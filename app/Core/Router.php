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
            $pattern = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $route['route']) . '$#';
            
            if (preg_match($pattern, $url, $matches) && $route['method'] === $method) {
                array_shift($matches);
                
                $controllerName = 'App\\Controllers\\' . $route['controller'];
                $controller = new $controllerName();
                
                call_user_func_array([$controller, $route['action']], $matches);
                return;
            }
        }

        // 404 Not Found
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
}