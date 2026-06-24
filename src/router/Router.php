<?php
namespace CriterionRegisterLogin\Router;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Http\Validator;

/**
 * Class Router
 * The central routing core of the application. Responsible for tracking definitions,
 * compiling dynamic URI patterns, executing middleware queues, and dispatching controllers.
 */
class Router {
    /**
     * Stores the single unified instance of the router.
     * @var self|null
     */
    private static $instance = null;
    
    // The internal registry storing all declared routes and their metadata
    private $routes = [];
    
    // Tracks the most recently added route index to support fluent middleware chaining
    private $currentRouteIndex = null;

    /**
     * Enforces the Singleton pattern. Returns the unique shared Router entity.
     * * @return self
     */
    private static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registers a new route blueprint into the internal tracking array.
     * Dynamically compiles standard route variables (e.g., {id}) into explicit Regular Expressions.
     * * @param string $method HTTP Verb (GET, POST, etc.)
     * @param string $uri The target application route mask
     * @param mixed $action Closure or 'Controller@method' string reference
     * @return self
     */
    public static function add($method, $uri, $action): self {
        $router = self::getInstance();
        
        // Convert framework-style route parameters into named RegEx capture groups
        // Example: "/user/{id}" transforms into "#^/user/(?P<id>[a-zA-Z0-9_\-]+)$#"
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

        // Capture the index state to enable post-declaration chain manipulation (.e.g., ->middleware())
        $router->currentRouteIndex = count($router->routes) - 1;
        
        return $router;
    }

    /**
     * Shortcut factory to append a GET validation rule to the registry.
     */
    public static function get($uri, $action): self {
        return self::add('GET', $uri, $action);
    }

    /**
     * Shortcut factory to append a POST validation rule to the registry.
     */
    public static function post($uri, $action): self {
        return self::add('POST', $uri, $action);
    }

    /**
     * Fluent interface method to bind a single middleware or a stack to the current route index context.
     * * @param mixed $middleware Class name string or array of strings
     * @return self
     */
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

    /**
     * Dispatches the application pipeline. Evaluates the captured request state,
     * matches the corresponding routing token, executes mid-tier logic, and flushes output.
     * * @return void
     * @throws \Exception When handlers are structurally malformed or missing
     */
    public static function dispatch(): void {
        $router = self::getInstance();
        
        // Capture raw environment variables under the unified Request entity abstraction
        $request = Request::capture();
        
        $requestUri = $request->path();
        $requestMethod = $request->method();

        // Method Spoofing: Overwrite standard POST actions with RESTful verbs (PUT/DELETE) via hidden _method values
        if ($requestMethod === 'POST' && $request->has('_method')) {
            $requestMethod = strtoupper($request->input('_method'));
        }

        // Iterate through the internal registry to discover structural matches
        foreach ($router->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                
                // Extract clean named parameters from RegEx match outputs, omitting positional integer indexes
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Sequential Middleware execution pipeline loop
                foreach ($route['middleware'] as $middleware) {
                    if (class_exists($middleware)) {
                        $mwInstance = new $middleware();
                        
                        // Execute the middleware interceptor handler rule.
                        // Note: If a middleware calls exit/send internally, propagation halts immediately.
                        $mwInstance->handle($request); 
                    }
                }

                // Prepare variable arguments array: prepend the main Request object before URL path tokens
                $callbackArgs = array_merge([$request], $params);
                $response = null;

                // Resolution Strategy A: Direct execution if the endpoint actions are defined as Closures/Callables
                if (is_callable($route['action'])) {
                    $response = call_user_func_array($route['action'], $callbackArgs);
                } 
                
                // Resolution Strategy B: Reflective string instantiation for formal 'Controller@method' setups
                elseif (is_string($route['action']) && strpos($route['action'], '@') !== false) {
                    list($controller, $method) = explode('@', $route['action']);
                    
                    if (!class_exists($controller)) {
                        throw new \Exception("Hiba: A(z) '$controller' osztály nem található.");
                    }
                    
                    $controllerInstance = new $controller();
                    if (!method_exists($controllerInstance, $method)) {
                        throw new \Exception("Hiba: A(z) '$method' metódus nem létezik a(z) '$controller' osztályban.");
                    }
                    
                    // Dispatch the execution straight to the targeted controller action
                    $response = call_user_func_array([$controllerInstance, $method], $callbackArgs);
                } else {
                    throw new \Exception("Route action nem található vagy nem meghívható.");
                }

                // Uniform Type Transformation Layer: Cast arbitrary returned results into standard Response objects
                if ($response instanceof Response) {
                    $response->send();
                } elseif (is_array($response)) {
                    Response::json($response)->send();
                } else {
                    Response::make((string)$response)->send();
                }
                
                // Complete application flow immediately following a successful match sequence
                return;
            }
        }

        // Standard global fallback when no registered patterns align with the request URI token context
        Response::make("404 - Az oldal nem található.", 404)->send();
    }
}