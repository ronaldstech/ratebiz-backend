<?php
namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Business;
use App\Middleware\AuthMiddleware;
use Ramsey\Uuid\Uuid;

class BusinessController
{
    public function index(): void
    {
        try {
            Response::json(Business::all());
        } catch (\Exception $e) {
            Response::json(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id): void
    {
        try {
            $business = Business::find($id);

            if (!$business) {
                Response::json(['error' => 'Business not found'], 404);
            }

            Response::json($business);
        } catch (\Exception $e) {
            Response::json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function store(): void
    {
        try {
            $user = AuthMiddleware::handle();

            if ($user->role !== 'owner' && $user->role !== 'admin') {
                Response::json(['error' => 'Forbidden'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            Business::create([
                'id' => Uuid::uuid4()->toString(),
                'owner_id' => $user->user_id,
                'name' => $data['name'],
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null
            ]);

            Response::json(['message' => 'Business created'], 201);
        } catch (\Exception $e) {
            Response::json(['error' => 'Error creating business: ' . $e->getMessage()], 500);
        }
    }
}
