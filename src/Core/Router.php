<?php

namespace App\Core;

/**
 * Router class.
 *
 * Allows registering GET/POST routes and dispatching requests
 * to controller methods. Meant for small apps, no regex or params.
 */
class Router
{
    /**
     * Array of registered routes in the form:
     * [
     *   'GET' => [ '/path' => [ControllerClass::class, 'method'] ],
     *   'POST' => [ '/path' => [ControllerClass::class, 'method'] ],
     * ]
     *
     * @var array<string, array<string, array{0:string,1:string}>>
     */
    private array $routes = [];

    /**
     * Register a GET route.
     *
     * @param string $path    The request path (e.g. "/login")
     * @param array  $handler [ControllerClass::class, 'methodName']
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Register a POST route.
     *
     * @param string $path    The request path (e.g. "/login")
     * @param array  $handler [ControllerClass::class, 'methodName']
     */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * Dispatch an incoming request to the correct handler.
     *
     * @param string $method HTTP method (e.g. "GET" or "POST")
     * @param string $uri    Request URI (from $_SERVER['REQUEST_URI'])
     */
    public function dispatch(string $method, string $uri): void
    {
        // Strip query string and normalize path
        $path = strtok($uri, '?') ?: '/';

        // Find matching handler
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo "Not Found";
            return;
        }

        // Instantiate the controller and call the method
        [$class, $methodName] = $handler;
        $controller = new $class();
        $controller->$methodName();
    }
}
