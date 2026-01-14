<?php
namespace App;

use App\Helpers\Response;

class Router
{
    private array $routes = [];

    public function post(string $path, string $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!isset($this->routes[$method][$path])) {
            Response::json(['error' => 'Route not found'], 404);
        }

        [$controller, $action] = explode('@', $this->routes[$method][$path]);
        $controller = "App\\Controllers\\$controller";

        (new $controller)->$action();
    }
}
