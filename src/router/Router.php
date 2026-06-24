<?php
namespace CriterionRegisterLogin\Router;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Http\Validator;

class Router {
    private static $instance = null;
    private $routes = [];
    private $currentRouteIndex = null;

    private static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function add($method, $uri, $action): self {
        $router = self::getInstance();
        
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $uri);
        $pattern = "#^" . $pattern . "$#";

        $router->routes[] = [
            'method'     => strtoupper($method),
            'uri'        => $uri,
            'pattern'    => $pattern,
            'action'     => $action,
            'middleware' => [],
            'name'       => null
        ];

        $router->currentRouteIndex = count($router->routes) - 1;
        
        return $router;
    }

    public static function get($uri, $action): self {
        return self::add('GET', $uri, $action);
    }

    public static function post($uri, $action): self {
        return self::add('POST', $uri, $action);
    }

    public function middleware($middleware): self {
        if ($this->currentRouteIndex !== null) {
            if (is_array($middleware)) {
                $this->routes[$this->currentRouteIndex]['middleware'] = array_merge(
                    $this->routes[$this->currentRouteIndex]['middleware'], 
                    $middleware
                );
            } else {
                $this->routes[$this->currentRouteIndex]['middleware'][] = $middleware;
            }
        }
        return $this;
    }

    public static function dispatch(): void {
        $router = self::getInstance();
        
        $request = Request::capture();
        
        $requestUri = $request->path();
        $requestMethod = $request->method();

        if ($requestMethod === 'POST' && $request->has('_method')) {
            $requestMethod = strtoupper($request->input('_method'));
        }

        foreach ($router->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middleware) {
                    if (class_exists($middleware)) {
                        $mwInstance = new $middleware();
                        
                        $mwInstance->handle($request); 
                    }
                }

                $callbackArgs = array_merge([$request], $params);
                $response = null;

                if (is_callable($route['action'])) {
                    $response = call_user_func_array($route['action'], $callbackArgs);
                } 
                
                elseif (is_string($route['action']) && strpos($route['action'], '@') !== false) {
                    list($controller, $method) = explode('@', $route['action']);
                    
                    if (!class_exists($controller)) {
                        throw new \Exception("Hiba: A(z) '$controller' osztály nem található.");
                    }
                    
                    $controllerInstance = new $controller();
                    if (!method_exists($controllerInstance, $method)) {
                        throw new \Exception("Hiba: A(z) '$method' metódus nem létezik a(z) '$controller' osztályban.");
                    }
                    
                    $response = call_user_func_array([$controllerInstance, $method], $callbackArgs);
                } else {
                    throw new \Exception("Route action nem található vagy nem meghívható.");
                }

                if ($response instanceof Response) {
                    $response->send();
                } elseif (is_array($response)) {
                    Response::json($response)->send();
                } else {
                    Response::make((string)$response)->send();
                }
                
                return;
            }
        }

        Response::make("404 - Az oldal nem található.", 404)->send();
    }
}