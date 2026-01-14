<?php
// Simple debug script
echo "PHP Version: " . phpversion() . "\n";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoload loaded successfully\n";
} catch (Exception $e) {
    echo "Autoload error: " . $e->getMessage() . "\n";
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo ".env loaded successfully\n";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
} catch (Exception $e) {
    echo ".env error: " . $e->getMessage() . "\n";
}

try {
    $db = new PDO(
        "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Database connection successful\n";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

try {
    if (class_exists('App\Router')) {
        echo "App\Router class found!\n";
    } else {
        echo "App\Router class NOT found.\n";
    }
} catch (Exception $e) {
    echo "Router loading error: " . $e->getMessage() . "\n";
}
?>