<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Review
{
    public static function create(array $data): void
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO reviews (id, business_id, user_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['id'],
            $data['business_id'],
            $data['user_id'],
            $data['rating'],
            $data['comment']
        ]);
    }

    public static function getByBusinessId(string $businessId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT r.*, u.name as user_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.business_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$businessId]);
        return $stmt->fetchAll();
    }
}
