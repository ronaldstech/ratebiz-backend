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
            $user = AuthMiddleware::handle();
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['business_id']) || !isset($data['rating']) || empty($data['comment'])) {
                Response::json(['error' => 'Business ID, rating and comment are required'], 422);
                return;
            }

            if ($data['rating'] < 0.5 || $data['rating'] > 5) {
                Response::json(['error' => 'Rating must be between 0.5 and 5'], 422);
                return;
            }

            Review::create([
                'id' => Uuid::uuid4()->toString(),
                'business_id' => $data['business_id'],
                'user_id' => $user->user_id,
                'rating' => $data['rating'],
                'comment' => $data['comment']
            ]);

            Response::json(['message' => 'Review submitted successfully'], 201);

        } catch (\Exception $e) {
            Response::json(['error' => 'Failed to submit review: ' . $e->getMessage()], 500);
        }
    }

    public function index(string $businessId): void
    {
        try {
            $reviews = Review::getByBusinessId($businessId);
            Response::json($reviews);
        } catch (\Exception $e) {
            Response::json(['error' => 'Failed to fetch reviews: ' . $e->getMessage()], 500);
        }
    }
}
