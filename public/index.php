<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Custom autoloader fallback for App namespace if composer mapping is missing
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $parts = explode('\\', $class);
        array_shift($parts); // Remove App
        
        // Handle App\Config separately if it maps to config/
        if ($parts[0] === 'Config') {
            $path = __DIR__ . '/../config/' . $parts[1] . '.php';
        } else {
            // Lowercase directory parts for Linux compatibility
            $filename = array_pop($parts);
            $dirs = array_map('strtolower', $parts);
            $path = __DIR__ . '/../app/' . (empty($dirs) ? '' : implode('/', $dirs) . '/') . $filename . '.php';
        }
        
        if (file_exists($path)) {
            require_once $path;
        } else {
            $dir = dirname($path);
            $target = strtolower(basename($path));
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $f) {
                    if (strtolower($f) === $target) {
                        require_once $dir . '/' . $f;
                        return;
                    }
                }
            }
        }
    }
});

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

// Test controller loading
if (strpos($_SERVER['REQUEST_URI'], '/api/controllers') !== false) {
    try {
        $controller = new App\Controllers\BusinessController();
        echo json_encode(['message' => 'Controller loaded successfully']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Controller loading failed: ' . $e->getMessage()]);
    }
    exit;
}

// Initialize router
$router = new App\Router();

// Define routes
$router->post('/api/auth/login', 'AuthController@login');
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/upload', 'UploadController@upload');

$router->get('/api/businesses', 'BusinessController@index');
$router->get('/api/my-businesses', 'BusinessController@myBusinesses');
$router->get('/api/businesses/{id}', 'BusinessController@show');
$router->post('/api/businesses', 'BusinessController@store');

$router->get('/api/businesses/{id}/reviews', 'ReviewController@index');
$router->get('/api/reviews', 'ReviewController@index');
$router->post('/api/reviews', 'ReviewController@store');
$router->put('/api/reviews/{id}', 'ReviewController@update');
$router->delete('/api/reviews/{id}', 'ReviewController@destroy');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
?>