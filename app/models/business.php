<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Business
{
    public static function create(array $data): void
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO businesses 
            (id, owner_id, name, category, description, location, phone, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['id'],
            $data['owner_id'],
            $data['name'],
            $data['category'],
            $data['description'],
            $data['location'],
            $data['phone'] ?? null,
            $data['image_url'] ?? null
        ]);
    }

    public static function all(): array
    {
        $db = Database::connect();
        return $db->query("SELECT * FROM businesses WHERE is_verified = 1")->fetchAll();
    }

    public static function find(string $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM businesses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByOwnerId(string $ownerId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM businesses WHERE owner_id = ?");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }
}
