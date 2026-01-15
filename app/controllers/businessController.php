<?php
namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Business;
use App\Middleware\AuthMiddleware;
use App\Config\Database;
use Ramsey\Uuid\Uuid;

class BusinessController
{
    public function index(): void
    {
        try {
            $businesses = Business::allWithStats();

            // Format response to match frontend expectations
            $formatted = array_map(function($biz) {
                return [
                    'id' => $biz['id'],
                    'businessName' => $biz['name'],
                    'category' => $biz['category'],
                    'location' => $biz['location'],
                    'description' => $biz['description'],
                    'imageUrl' => $biz['image_url'] ?? null,
                    'phone' => $biz['phone'] ?? null,
                    'rating' => number_format((float)$biz['avg_rating'], 1),
                    'reviewCount' => $biz['review_count']
                ];
            }, $businesses);

            Response::json($formatted);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id): void
    {
        try {
            $business = Business::findWithStats($id);

            if (!$business) {
                Response::json(['error' => 'Business not found'], 404);
            }

            // Format for frontend
            $formatted = [
                'id' => $business['id'],
                'businessName' => $business['name'],
                'category' => $business['category'],
                'location' => $business['location'],
                'description' => $business['description'],
                'imageUrl' => $business['image_url'] ?? null,
                'phone' => $business['phone'] ?? null,
                'rating' => number_format((float)$business['avg_rating'], 1),
                'reviewCount' => $business['review_count']
            ];

            Response::json($formatted);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function store(): void
    {
        try {
            $user = AuthMiddleware::handle();

            // Allow 'user', 'business' (owner), or 'admin' to create businesses
            // But we specifically want to allow 'user' to upgrade
            if (!in_array($user->role, ['user', 'business', 'admin'])) {
                Response::json(['error' => 'Forbidden'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Map frontend 'businessName' to 'name' if necessary
            $name = $data['name'] ?? $data['businessName'] ?? null;

            if (empty($name)) {
                 Response::json(['error' => 'Business Name is required'], 422);
            }

            $db = Database::connect();
            $db->beginTransaction();

            try {
                // Create Business
                Business::create([
                    'id' => Uuid::uuid4()->toString(),
                    'owner_id' => $user->user_id,
                    'name' => $name,
                    'category' => $data['category'] ?? null,
                    'description' => $data['description'] ?? null,
                    'location' => $data['location'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'image_url' => $data['imageUrl'] ?? null
                ]);

                // Upgrade User Role to 'business' if they are currently 'user'
                if ($user->role === 'user') {
                     $stmt = $db->prepare("UPDATE users SET role = 'business' WHERE id = ?");
                     $stmt->execute([$user->user_id]);
                }

                $db->commit();
                
                Response::json(['message' => 'Business created successfully. You are now a Business account.'], 201);

            } catch (\Throwable $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $e) {
            Response::json(['error' => 'Error creating business: ' . $e->getMessage()], 500);
        }
    }
    public function myBusinesses(): void
    {
        try {
            $user = AuthMiddleware::handle();
            $businesses = Business::getByOwnerId($user->user_id);

            // Format response to match frontend expectations
            $formatted = array_map(function($biz) {
                return [
                    'id' => $biz['id'],
                    'businessName' => $biz['name'], // Map name to businessName
                    'category' => $biz['category'],
                    'location' => $biz['location'],
                    'description' => $biz['description'],
                    'imageUrl' => $biz['image_url'] ?? null,
                    'phone' => $biz['phone'] ?? null
                ];
            }, $businesses);

            Response::json($formatted);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Error fetching businesses: ' . $e->getMessage()], 500);
        }
    }
}
