<?php
namespace RentCar\Core;

class Router {
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    private $globalMiddlewares = [];

    public function addMiddleware($middleware) {
        $this->globalMiddlewares[] = $middleware;
    }

    public function get($path, $callback, $middlewares = []) {
        $this->addRoute('GET', $path, $callback, $middlewares);
    }

    public function post($path, $callback, $middlewares = []) {
        $this->addRoute('POST', $path, $callback, $middlewares);
    }

    public function put($path, $callback, $middlewares = []) {
        $this->addRoute('PUT', $path, $callback, $middlewares);
    }

    public function delete($path, $callback, $middlewares = []) {
        $this->addRoute('DELETE', $path, $callback, $middlewares);
    }

    private function addRoute($method, $path, $callback, $middlewares) {
        $this->routes[$method][$path] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Chercher la route correspondante
        foreach ($this->routes[$method] as $route => $config) {
            $pattern = $this->buildPattern($route);
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                
                // Exécuter les middlewares globaux
                foreach ($this->globalMiddlewares as $middleware) {
                    if ($middleware->handle() === false) {
                        return;
                    }
                }
                
                // Exécuter les middlewares spécifiques à la route
                foreach ($config['middlewares'] as $middleware) {
                    if ($middleware->handle() === false) {
                        return;
                    }
                }
                
                // Exécuter le callback
                $this->executeCallback($config['callback'], $matches);
                return;
            }
        }

        // Route non trouvée
        http_response_code(404);
        echo json_encode(['error' => 'Route not found', 'path' => $path]);
    }

    private function buildPattern($route) {
        return '#^' . preg_replace('/\(\\\d\+\)/', '([0-9]+)', $route) . '$#';
    }

    private function executeCallback($callback, $params) {
        if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        } elseif (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];
            call_user_func_array([$controller, $method], $params);
        } else {
            throw new \Exception("Invalid callback");
        }
    }
}