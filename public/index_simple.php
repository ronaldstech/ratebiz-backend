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
if ($_SERVER['REQUEST_URI'] === '/ratebiz/public/api/test') {
    echo json_encode(['message' => 'API is working!', 'timestamp' => time()]);
    exit;
}

// For now, just return a simple message
echo json_encode(['status' => 'API server is running', 'path' => $_SERVER['REQUEST_URI']]);
?>