<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple test endpoint
if (strpos($_SERVER['REQUEST_URI'], '/api/test') !== false) {
    echo json_encode(['message' => 'API is working!', 'timestamp' => time()]);
    exit;
}

// Initialize router
$router = new App\Router();

// Define routes
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/register', 'AuthController@register');

$router->get('/api/businesses', 'BusinessController@index');
$router->get('/api/businesses/{id}', 'BusinessController@show');
$router->post('/api/businesses', 'BusinessController@store');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
?>