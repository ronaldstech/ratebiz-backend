<?php
namespace App\Middleware;

use App\Helpers\Response;
use App\Helpers\JwtHelper;

class AuthMiddleware
{
    public static function handle(): object
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            return JwtHelper::verify($token);
        } catch (\Exception $e) {
            Response::json(['error' => 'Invalid token'], 401);
        }
    }
}
