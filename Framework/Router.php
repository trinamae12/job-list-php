<?php
namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorize;

class Router {
    protected $routes = [];

    /**
     * Add a new route
     * 
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param array $middleware
     * @return void
     */
    public function registerRoute($method, $uri, $action, $middleware = []) {
        // equivalent to destructuring in javascript
        list($controller, $controllerMethod) = explode('@', $action);

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'controllerMethod' => $controllerMethod,
            'middleware' => $middleware
        ];
    }

    /**
     * Add a GET route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function get($uri, $controller, $middleware = []) {
        $this->registerRoute('GET', $uri, $controller, $middleware);
    }

    /**
     * Add a POST route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function post($uri, $controller, $middleware = []) {
        $this->registerRoute('POST', $uri, $controller, $middleware);
    }

    /**
     * Add a PUT route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function put($uri, $controller, $middleware = []) {
        $this->registerRoute('PUT', $uri, $controller, $middleware);
    }

    /**
     * Add a DELETE route
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function delete($uri, $controller, $middleware = []) {
        $this->registerRoute('DELETE', $uri, $controller, $middleware);
    }

    // /**
    //  * Load error page
    //  * 
    //  * @param int $httpCode
    //  * @return void
    //  */
    // public function error($httpCode = 404) {
    //     http_response_code($httpCode);
    //     loadView("error/{$httpCode}");
    //     exit;
    // }

    /**
     * Route the request
     * 
     * @param string $uri
     * @param string $method
     * @return void
     */
    public function route($uri) {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Check for _method input
        if($requestMethod === "POST" && isset($_POST['_method'])) {
            // Override the request method with the value of _method
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach($this->routes as $route) {

            // Split the current uri into segments
            $uriSegments = explode('/', trim($uri, '/'));

            // Split the route URI into segments
            $routeSegments = explode('/', trim($route['uri'], '/'));
            
            $match = true;

            // check if number of segments matches and if method is equal to requestMethod
            if(count($uriSegments) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)) {
                $params = [];

                $match = true;

                for($i = 0; $i < count($uriSegments); $i++) {
                    // If the uri's do not match and there is no parameter
                    if($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])) {
                        $match = false;
                        break;
                    }
                    
                    // Check for the param and add to params array
                    if(preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)) {
                        // Assign paramter name (key) to respective value
                        $params[$matches[1]] = $uriSegments[$i];
                    }
                }

                // Test if match is still true
                if($match) {
                    foreach($route['middleware'] as $role) {
                        //$auth = new Authorize;
                        //inspectAndExit($auth);
                        (new Authorize())->handle($role);
                        //$auth->handle($role);
                    }

                    //Extract controller and controller method
                    $controller = 'App\\Controllers\\' . $route['controller'];
                    $controllerMethod = $route['controllerMethod'];

                    // Instantiate the controller class and call the method
                    $controllerInstance = new $controller();
                    $controllerInstance->$controllerMethod($params);
                    
                    return;
                }
            }

            // if($route['uri'] === $uri && $route['method'] === $method) {
            //     // require basePath('App/' . $route['controller']);
            //     //Extract controller and controller method
            //     $controller = 'App\\Controllers\\' . $route['controller'];
            //     $controllerMethod = $route['controllerMethod'];

            //     // Instantiate the controller class and call the method
            //     $controllerInstance = new $controller();
            //     $controllerInstance->$controllerMethod();

            //     return;
            // }
        }
        //$this->error();
        ErrorController::notFound();
    }   

}