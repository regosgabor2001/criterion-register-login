<?php
namespace CriterionRegisterLogin\Router;

class Router {
    private static $instance = null;
    private $routes = [];
    private $currentRouteIndex = null;

    // Singleton elérés, hogy a statikus hívások egy példányba gyűljenek
    private static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Route regisztrálása
    public static function add($method, $uri, $action) {
        $router = self::getInstance();
        
        // Laravel-stílusú {param} átalakítása Regex kifejezéssé
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

        // Elmentjük az aktuális indexet a láncolhatósághoz (name, middleware)
        $router->currentRouteIndex = count($router->routes) - 1;
        
        return $router;
    }

    public static function get($uri, $action) {
        return self::add('GET', $uri, $action);
    }

    public static function post($uri, $action) {
        return self::add('POST', $uri, $action);
    }

    // Laravel-stílusú név hozzárendelés: ->name('profile')
    public function name($name) {
        if ($this->currentRouteIndex !== null) {
            $this->routes[$this->currentRouteIndex]['name'] = $name;
        }
        return $this;
    }

    // Middleware hozzárendelés: ->middleware(AuthMiddleware::class)
    public function middleware($middleware) {
        if ($this->currentRouteIndex !== null) {
            $this->routes[$this->currentRouteIndex]['middleware'][] = $middleware;
        }
        return $this;
    }

    // Kérés feldolgozása (Dispatch)
    public static function dispatch() {
        $router = self::getInstance();
        
        // Aktuális URI és Method lekérése query stringek nélkül
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Ha POST kérésnél van _method (pl. PUT/DELETE emulációhoz)
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($router->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                
                // Csak a megnevezett (string kulcsú) paramétereket tartjuk meg a regex-ből
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Middleware-ek futtatása (ha vannak)
                foreach ($route['middleware'] as $middleware) {
                    // Itt példányosítjuk a middleware-t, és ha a handle() hamisat ad vissza, megállítjuk a futást
                    $mwInstance = new $middleware();
                    if (!$mwInstance->handle()) {
                        return; // A middleware kezeli a redirectet vagy die()-t
                    }
                }

                // Action végrehajtása (Closure vagy Controller)
                if (is_callable($route['action'])) {
                    return call_user_func_array($route['action'], $params);
                } 
                
                // Ha Controller@metódus string formátum: 'UserController@show'
                if (is_string($route['action']) && strpos($route['action'], '@') !== false) {
                    list($controller, $method) = explode('@', $route['action']);
                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $method)) {
                            return call_user_func_array([$controllerInstance, $method], $params);
                        }
                    }
                }

                throw new Exception("Route action nem található vagy nem meghívható.");
            }
        }

        // 404-es hiba ha nincs találat
        http_response_code(404);
        echo "404 - Az oldal nem található.";
    }
}