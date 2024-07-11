<?php
    
    namespace Xmgr;
    
    use Xmgr\Controllers\Request\ControllerHandler;
    
    /**
     * Class RouteHandler
     *
     * This class handles routing and processing of HTTP requests.
     *
     * @package YourPackage
     */
    class RouteHandler {
        
        protected array  $methods = [];
        protected string $route   = '';
        protected mixed  $action  = null;
        protected bool   $last    = true;
        
        /**
         * Constructor for the object.
         *
         * @param string $route The route to set for the object.
         *
         * @return void
         */
        public function __construct(string $route) {
            $this->route($route);
        }
        
        /**
         * Clears all the methods registered in the object.
         *
         * @return $this
         */
        public function clearMethods(): self {
            $this->methods = [];
            
            return $this;
        }
        
        /**
         * Allow specified HTTP methods for the route.
         *
         * @param array $methods An array of HTTP methods to allow for the route.
         *
         * @return $this Returns an instance of the current class.
         */
        public function allowMethods(array $methods): self {
            foreach ($methods as $method) {
                $this->methods[] = strtoupper($method);
            }
            
            return $this;
        }
        
        /**
         * Set the action for the current request.
         *
         * @param mixed $action The action to be set for the request.
         *
         * @return $this
         */
        public function action(mixed $action): self {
            $this->action = $action;
            
            return $this;
        }
        
        /**
         * Load a view.
         *
         * @param string      $view   The path to the view file.
         * @param string|null $layout The layout file to use (optional).
         * @param array       $data   The data to pass to the view file (optional).
         *
         * @return $this
         */
        public function loadView(string $view, string $layout = null, array $data = []): self {
            $this->action = view($view, $layout, $data);
            
            return $this;
        }
        
        /**
         * Prepends a route to the existing route string.
         *
         * @param string $route The route to prepend.
         *
         * @return $this
         */
        public function prepend(string $route): self {
            $this->route($route . '/' . $this->route);
            
            return $this;
        }
        
        /**
         * Append a route to the existing route.
         *
         * @param string $route The route to append.
         *
         * @return $this The current instance of the class.
         */
        public function append(string $route): self {
            $this->route($this->route . '/' . $route);
            
            return $this;
        }
        
        /**
         * Sets the route for the object.
         *
         * @param string $route The route to set for the object.
         *
         * @return self Returns the modified object.
         */
        public function route(string $route): self {
            $this->route = str_collapse('/' . trim($route, '/'), '/');
            
            return $this;
        }
        
        /**
         * Retrieves the route from the object.
         *
         * @return string The route of the object.
         */
        protected function getRoute(): string {
            return str_collapse('/' . trim($this->route, '/'), '/');
        }
        
        /**
         * Checks if the current request matches the route defined for the object.
         *
         * @return bool Returns true if the request matches the route, false otherwise.
         */
        public function matches(): bool {
            if ($this->action === null || !(!$this->methods || Request::methodIs($this->methods))) {
                return false;
            }
            $uri   = '/' . trim(uri(), '/');
            $route = $this->getRoute();
            
            if ($route === '*' || ($uri === '/' && $route === '')) {
                return true;
            }
            
            $arr_uri   = explode('/', trim($uri, '/'));
            $arr_route = explode('/', trim($route, '/'));
            
            foreach ($arr_route as $k => $a) {
                if ($a === '*') {
                    continue;
                }
                $b = arr($arr_uri, $k);
                if (Str::enclosed($a, '{', '}')) {
                    $a = trim($a, '{}');
                    $a = trim($a, '?');
                    if (!array_key_exists($a, $_GET)) {
                        $_GET[$a] = arr($arr_uri, $k);
                    }
                } else {
                    if ($a !== $b) {
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Handles the response based on the action set for the object.
         *
         * @return mixed The output generated by handling the response.
         */
        public function handle(): mixed {
            ob_start();
            $response = value($this->action);
            
            switch (true) {
                # Assume you want to let a controller object handle the response
                case is_array($response):
                    if (count($response) === 1) {
                        $response[] = 'index';
                    }
                    if (count($response) === 2) {
                        $class  = $response[0];
                        $method = $response[1];
                        if (class_exists($class)) {
                            $object = new $class();
                            if (method_exists($object, $method)) {
                                $response = new ControllerHandler($object, $method);
                            }
                        }
                    }
                case ($response instanceof ControllerHandler):
                    $response->run();
                    break;
                case (is_string($response)):
                    echo $response;
                    break;
                case $response instanceof View:
                    echo $response->render();
                    break;
                case $response instanceof Response:
                    $response->send();
                    break;
                default:
                    break;
            }
            $output = ob_get_contents();
            ob_end_clean();
            
            return $output;
        }
        
    }
