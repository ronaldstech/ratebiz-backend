<?php
// Simple debug script
echo "PHP Version: " . phpversion() . "\n";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoload loaded successfully\n";
} catch (Exception $e) {
    echo "Autoload error: " . $e->getMessage() . "\n";
}

spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $parts = explode('\\', $class);
        array_shift($parts);
        if ($parts[0] === 'Config') {
            $path = __DIR__ . '/../config/' . $parts[1] . '.php';
        } else {
            $filename = array_pop($parts);
            $dirs = array_map('strtolower', $parts);
            $path = __DIR__ . '/../app/' . (empty($dirs) ? '' : implode('/', $dirs) . '/') . $filename . '.php';
        }
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

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

echo "Listing 'app' directory:\n";
function listDir($dir) {
    if (!is_dir($dir)) {
        echo "$dir is not a directory\n";
        return;
    }
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        echo is_dir($path) ? "[DIR] $file\n" : "[FILE] $file\n";
    }
}
listDir(__DIR__ . '/../app');
echo "\nListing 'app/controllers':\n";
listDir(__DIR__ . '/../app/controllers');

?>