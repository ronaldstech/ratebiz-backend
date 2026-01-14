<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing basic setup...\n";

// Test autoloading
try {
    $router = new App\Router();
    echo "✓ Router class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Router class failed: " . $e->getMessage() . "\n";
}

// Test database connection
try {
    $db = App\Config\Database::connect();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

// Test JWT helper
try {
    $token = App\Helpers\JwtHelper::generate(['test' => 'data']);
    echo "✓ JWT generation successful\n";
} catch (Exception $e) {
    echo "✗ JWT generation failed: " . $e->getMessage() . "\n";
}

echo "Basic setup test completed.\n";
?>