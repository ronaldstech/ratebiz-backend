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
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.business_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$businessId]);
        return $stmt->fetchAll();
    }

    public static function find(string $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function update(string $id, array $data): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE reviews 
            SET rating = ?, comment = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['rating'],
            $data['comment'],
            $id
        ]);
    }

    public static function delete(string $id): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
    }
}
