<?php
namespace App;

use App\Helpers\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[a-z]+\}#', '([^/]+)', $route);

            if (preg_match("#^$pattern$#", $path, $matches)) {
                array_shift($matches);

                [$controller, $action] = explode('@', $handler);
                $controller = "App\\Controllers\\$controller";

                (new $controller)->$action(...$matches);
                return;
            }
        }

        Response::json(['error' => 'Route not found'], 404);
    }
}
