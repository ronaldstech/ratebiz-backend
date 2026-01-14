<?php
// Simple debug script
echo "PHP Version: " . phpversion() . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

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

echo "\nContent of 'app/router.php':\n";
if (file_exists(__DIR__ . '/../app/router.php')) {
    echo file_get_contents(__DIR__ . '/../app/router.php');
} else if (file_exists(__DIR__ . '/../app/Router.php')) {
    echo file_get_contents(__DIR__ . '/../app/Router.php');
} else {
    echo "router.php not found\n";
}
?>