<?php
namespace CriterionRegisterLogin\Router;

use CriterionRegisterLogin\Http\Request;
use CriterionRegisterLogin\Http\Response;
use CriterionRegisterLogin\Http\Validator;

class Router {
    private static $instance = null;
    private $routes = [];
    private $currentRouteIndex = null;

    // Singleton elérés, hogy a statikus hívások egy példányba gyűljenek
    private static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Route regisztrálása
    public static function add($method, $uri, $action): self {
        $router = self::getInstance();
        
        // Laravel-stílusú {param} átalakítása Regex kifejezéssé
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $uri);
        $pattern = "#^" . $pattern . "$#";

        $router->routes[] = [
            'method'     => strtoupper($method),
            'uri'        => $uri,
            'pattern'    => $pattern,
            'action'     => $action,
            'middleware' => [], // Ez egy üres tömbként indul, ide gyűlnek a szűrők
            'name'       => null
        ];

        // Elmentjük az aktuális indexet a láncolhatósághoz (name, middleware)
        $router->currentRouteIndex = count($router->routes) - 1;
        
        return $router;
    }

    public static function get($uri, $action): self {
        return self::add('GET', $uri, $action);
    }

    public static function post($uri, $action): self {
        return self::add('POST', $uri, $action);
    }

    // Laravel-stílusú név hozzárendelés: ->name('profile')
    public function name($name): self {
        if ($this->currentRouteIndex !== null) {
            $this->routes[$this->currentRouteIndex]['name'] = $name;
        }
        return $this;
    }

    // ==========================================================================
    // MODOSÍTOTT MIDDLEWARE METÓDUS (Tömböt és stringet is fogad)
    // ==========================================================================
    public function middleware($middleware): self {
        if ($this->currentRouteIndex !== null) {
            if (is_array($middleware)) {
                // Ha tömböt kapunk (pl. [Csrf::class, Auth::class]), összefésüljük a meglévővel
                $this->routes[$this->currentRouteIndex]['middleware'] = array_merge(
                    $this->routes[$this->currentRouteIndex]['middleware'], 
                    $middleware
                );
            } else {
                // Ha egy sima stringet kapunk, csak simán betoljuk a tömb végére
                $this->routes[$this->currentRouteIndex]['middleware'][] = $middleware;
            }
        }
        return $this;
    }

    // Kérés feldolgozása (Dispatch)
    public static function dispatch(): void {
        $router = self::getInstance();
        
        // 1. A Laravel-stílusú Request objektum példányosítása az aktuális kérésből
        $request = Request::capture();
        
        $requestUri = $request->path();
        $requestMethod = $request->method();

        // Ha POST kérésnél van _method (pl. PUT/DELETE emulációhoz a request-ből)
        if ($requestMethod === 'POST' && $request->has('_method')) {
            $requestMethod = strtoupper($request->input('_method'));
        }

        foreach ($router->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // ==========================================================================
                // BIZTONSÁGOSABB MIDDLEWARE FUTTATÁS
                // ==========================================================================
                foreach ($route['middleware'] as $middleware) {
                    if (class_exists($middleware)) {
                        $mwInstance = new $middleware();
                        
                        // Csak meghívjuk a handle-t. Ha hiba van, a middleware-ben lévő exit; leállítja a rendszert.
                        // Nem kell if(!handle()), mert ha sikeres, a middleware nem ad vissza semmit (void), 
                        // így a korábbi if megállította volna a futást.
                        $mwInstance->handle($request); 
                    }
                }

                $callbackArgs = array_merge([$request], $params);
                $response = null;

                if (is_callable($route['action'])) {
                    $response = call_user_func_array($route['action'], $callbackArgs);
                } 
                
                // Action végrehajtása (Controller@metódus)
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