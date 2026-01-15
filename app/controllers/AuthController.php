<?php
namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Helpers\JwtHelper;
use Ramsey\Uuid\Uuid;
use App\Models\Business;

class AuthController
{
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                Response::json(['error' => 'Name, Email and Password are required'], 422);
                return;
            }

            $db = Database::connect();

            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);

            if ($stmt->fetch()) {
                Response::json(['error' => 'Email already registered'], 409);
                return;
            }

            $userId = Uuid::uuid4()->toString();
            // Default role is always 'user'
            $role = 'user';

            $stmt = $db->prepare("
                INSERT INTO users (id, email, password_hash, name, role, phone)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['name'],
                $role,
                $data['phone'] ?? null
            ]);

            $token = JwtHelper::generate(['user_id' => $userId, 'role' => $role]);

            Response::json([
                'message' => 'Registration successful',
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $role
                ]
            ], 201);

        } catch (\Exception $e) {
            Response::json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    public function login(): void
    {
        try {
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
        } catch (\Exception $e) {
            Response::json(['error' => 'Login failed: ' . $e->getMessage()], 500);
        }
    }
}
