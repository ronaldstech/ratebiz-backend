<?php
namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Review;
use App\Middleware\AuthMiddleware;
use Ramsey\Uuid\Uuid;

class ReviewController
{
    public function store(): void
    {
        try {
            // Optional auth for guest reviews
            $user_id = null;
            try {
                $user = AuthMiddleware::handle();
                $user_id = $user->user_id;
            } catch (\Exception $e) {
                // Not logged in, proceed as guest
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['business_id']) || !isset($data['rating']) || empty($data['comment'])) {
                Response::json(['error' => 'Business ID, rating and comment are required'], 422);
                return;
            }

            if ($data['rating'] < 0.5 || $data['rating'] > 5) {
                Response::json(['error' => 'Rating must be between 0.5 and 5'], 422);
                return;
            }

            $id = Uuid::uuid4()->toString();
            Review::create([
                'id' => $id,
                'business_id' => $data['business_id'],
                'user_id' => $user_id,
                'rating' => $data['rating'],
                'comment' => $data['comment']
            ]);

            Response::json([
                'message' => 'Review submitted successfully',
                'id' => $id
            ], 201);

        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to submit review: ' . $e->getMessage()], 500);
        }
    }

    public function update(string $id): void
    {
        try {
            $user = AuthMiddleware::handle();
            $review = Review::find($id);

            if (!$review) {
                Response::json(['error' => 'Review not found'], 404);
                return;
            }

            if ($review['user_id'] !== $user->user_id) {
                Response::json(['error' => 'Unauthorized to update this review'], 403);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            Review::update($id, [
                'rating' => $data['rating'] ?? $review['rating'],
                'comment' => $data['comment'] ?? $review['comment']
            ]);

            Response::json(['message' => 'Review updated successfully']);

        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to update review: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): void
    {
        try {
            $user = AuthMiddleware::handle();
            $review = Review::find($id);

            if (!$review) {
                Response::json(['error' => 'Review not found'], 404);
                return;
            }

            if ($review['user_id'] !== $user->user_id) {
                Response::json(['error' => 'Unauthorized to delete this review'], 403);
                return;
            }

            Review::delete($id);
            Response::json(['message' => 'Review deleted successfully']);

        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to delete review: ' . $e->getMessage()], 500);
        }
    }

    public function index(?string $businessId = null): void
    {
        try {
            // Support both /api/businesses/{id}/reviews and /api/reviews?business_id=...
            $id = $businessId ?? $_GET['business_id'] ?? null;
            
            if (!$id) {
                Response::json(['error' => 'Business ID is required'], 422);
                return;
            }

            $reviews = Review::getByBusinessId($id);
            
            // Format for frontend
            $formatted = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'userName' => $r['user_name'] ?? 'Anonymous Guest',
                    'rating' => (float)$r['rating'],
                    'comment' => $r['comment'],
                    'created_at' => $r['created_at']
                ];
            }, $reviews);

            Response::json($formatted);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to fetch reviews: ' . $e->getMessage()], 500);
        }
    }
}
