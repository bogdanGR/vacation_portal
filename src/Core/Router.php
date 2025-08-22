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
    private array $routes = ['GET'=>[], 'POST'=>[]];

    /**
     * Register a GET route.
     *
     * @param string $path    The request path (e.g. "/login")
     * @param array  $handler [ControllerClass::class, 'methodName']
     */
    public function get(string $path, array $handler): void
    {
        $this->add('GET',  $path, $handler);
    }

    /**
     * Register a POST route.
     *
     * @param string $path    The request path (e.g. "/login")
     * @param array  $handler [ControllerClass::class, 'methodName']
     */
    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Register a route pattern and its handler.
     *
     * Converts paths with {placeholders} into regex patterns with named groups.
     * Example:
     *   "/users/{id}/edit"
     *   → "#^/users/(?P<id>[^/]+)/edit$#"
     *
     * @param string $method  HTTP method ("GET" or "POST")
     * @param string $path    Route path with optional {param} placeholders
     * @param array{0:string,1:string} $handler [ControllerClass::class, 'methodName']
     */
    private function add(string $method, string $path, array $handler): void
    {
        // Convert "/users/{id}/edit" -> "#^/users/(?P<id>[^/]+)/edit$#"
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        $this->routes[$method]["#^{$pattern}$#"] = $handler;
    }

    /**
     * Dispatch an incoming request to the correct handler.
     *
     * @param string $method HTTP method (e.g. "GET" or "POST")
     * @param string $uri    Request URI (from $_SERVER['REQUEST_URI'])
     */
    public function dispatch(string $method, string $uri): void
    {
        // Strip query string (everything after ?)
        $path = explode('?', $uri, 2)[0] ?: '/';

        // Loop over all registered routes for this HTTP method
        foreach ($this->routes[$method] ?? [] as $routePattern => $handler) {

            // If request path matches the route regex
            if (preg_match($routePattern, $path, $matches)) {

                // Keep only named parameters (ignore numeric indexes from preg_match)
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                // Handler = [ControllerClass, methodName]
                [$controllerClass, $controllerMethod] = $handler;
                $controller = new $controllerClass();

                // Reflection: check if controller method expects arguments
                $ref = new \ReflectionMethod($controllerClass, $controllerMethod);

                if ($ref->getNumberOfParameters() > 0) {
                    // Pass the params array (e.g., ['id' => '5'])
                    $controller->$controllerMethod($params);
                } else {
                    // Call method with no params
                    $controller->$controllerMethod();
                }
                return;
            }
        }

        // If no route matched, 404
        http_response_code(404);
        echo 'Not Found';
    }

}
