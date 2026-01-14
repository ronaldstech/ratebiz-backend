<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    public static function generate(array $payload): string
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60 * 24); // 24 hours

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public static function verify(string $token): object
    {
        return JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
    }
}
