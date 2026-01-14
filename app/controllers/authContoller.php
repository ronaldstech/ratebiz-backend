<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Helpers\JwtHelper;
use Ramsey\Uuid\Uuid;

class AuthController
{
    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            Response::json(['error' => 'Email and password required'], 422);
        }

        $db = Database::connect();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);

        if ($stmt->fetch()) {
            Response::json(['error' => 'Email already registered'], 409);
        }

        $id = Uuid::uuid4()->toString();

        $stmt = $db->prepare("
            INSERT INTO users (id, email, password_hash)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $id,
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT)
        ]);

        $token = JwtHelper::generate(['user_id' => $id, 'role' => 'user']);

        Response::json([
            'message' => 'Registration successful',
            'token' => $token
        ], 201);
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT id, password_hash, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$data['email']]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            Response::json(['error' => 'Invalid credentials'], 401);
        }

        $token = JwtHelper::generate([
            'user_id' => $user['id'],
            'role' => $user['role']
        ]);

        Response::json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
}
