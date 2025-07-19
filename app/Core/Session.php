<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function add($route, $controller, $action, $method = 'GET')
    {
        $this->routes[] = [
            'route' => $route,
            'controller' => $controller,
            'action' => $action,
            'method' => $method
        ];
    }

    public function dispatch($url, $method)
    {
        $url = parse_url($url, PHP_URL_PATH);
        $url = trim($url, '/');

        foreach ($this->routes as $route) {
            $pattern = '#^' . str_replace('{id}', '([^/]+)', $route['route']) . '$#';
            
            if (preg_match($pattern, $url, $matches) && $route['method'] === $method) {
                array_shift($matches);
                
                $controllerName = 'App\\Controllers\\' . $route['controller'];
                $controller = new $controllerName();
                
                call_user_func_array([$controller, $route['action']], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>No route found for: $method /$url</p>";
    }
}