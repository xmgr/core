<?php
    
    namespace Xmgr;
    
    /**
     * Class Route
     *
     * The Route class provides methods for handling routes and dispatching requests to the appropriate route handler.
     */
    class Route {
        
        /** @var array|RouteHandler[] */
        public static array $routes = [];
        
        public static string $append = '';
        
        /** @var null|mixed|RouteHandler */
        protected static mixed $handler = null;
        
        /**
         * Appends the given route to the existing route.
         *
         * @param string $route The route pattern to be appended.
         *
         * @return void
         */
        public static function append(string $route): void {
            self::$append = '/' . trim($route, '/');
        }
        
        /**
         * Get the handler for the route.
         *
         * @return mixed The handler for the route.
         */
        public static function handler(): mixed {
            return static::$handler;
        }
        
        /**
         * Determines if a given route matches the current URI.
         *
         * @param string $route The route to match against.
         *
         * @return bool Returns true if the route matches the URI, false otherwise.
         */
        public static function matches(string $route): bool {
            $uri   = '/' . trim(uri(), '/');
            $route = '/' . trim($route, '/');
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
         * Joins the given paths into a single string.
         *
         * @param mixed ...$paths The paths to be joined.
         *
         * @return string The joined path string.
         */
        public static function join(...$paths): string {
            return trim(str_collapse(implode('/', $paths), '/'));
        }
        
        /**
         * Adds a route with the given route, methods, and action.
         *
         * @param string     $route   The route pattern to match against.
         * @param array      $methods The HTTP methods allowed for the route. Default: []
         * @param mixed|null $action  The action to be performed when the route is matched. Default: null
         *
         * @return RouteHandler The newly added RouteHandler object.
         */
        protected static function add(string $route, array $methods = [], mixed $action = null): RouteHandler {
            $route          = '/' . self::join(self::$append, $route);
            $handler        = (new RouteHandler($route))->allowMethods($methods)->action($action);
            self::$routes[] = $handler;
            
            return $handler;
        }
        
        /**
         * Retrieves data from a specified route using the GET method.
         *
         * @param string $route  The route to retrieve data from.
         * @param mixed  $action A callback function to execute when data is retrieved.
         *
         * @return RouteHandler
         */
        public static function get(string $route, mixed $action = null): RouteHandler {
            return self::add($route, ['GET'], $action);
        }
        
        /**
         * Process POST requests
         *
         * @param string $route  The route pattern
         * @param mixed  $action A callback function to execute.
         *
         * @return RouteHandler
         */
        public static function post(string $route, mixed $action): RouteHandler {
            return self::add($route, ['POST'], $action);
        }
        
        /**
         * Matches the given methods, route, and action.
         *
         * @param array  $methods The HTTP methods allowed for the route.
         * @param string $route   The route pattern to match against.
         * @param mixed  $action  The action to be performed when the route is matched.
         *
         * @return RouteHandler
         */
        public static function match(array $methods, string $route, mixed $action): RouteHandler {
            return self::add($route, $methods, $action);
        }
        
        /**
         * Process POST requests
         *
         * @param string $route  The route pattern
         * @param mixed  $action A callback function to execute.
         *
         * @return RouteHandler
         */
        public static function any(string $route, mixed $action): RouteHandler {
            return self::add($route, [], $action);
        }
        
        /**
         * Renders the specified view with the given data and layout.
         *
         * @param string      $route  The route pattern to match against.
         * @param string|null $view   The name of the view file to render. If null, the view file name will be derived
         *                            from the route.
         * @param array       $data   The data to be passed to the view.
         * @param string|null $layout The layout file to be used. Defaults to 'layouts.default'.
         *
         * @return RouteHandler
         */
        public static function view(string $route, ?string $view = null, array $data = [], ?string $layout = 'default') {
            return self::add($route)->loadView($view ?? $route, $layout, $data);
        }
        
        /**
         * Loads a view file with optional data for the given route and returns the RouteHandler.
         *
         * @param string      $route The route pattern to match against.
         * @param string|null $view  The path to the view file, or null to use the route as the view path.
         * @param array       $data  Optional data to pass to the view file.
         *
         * @return RouteHandler
         */
        public static function file(string $route, ?string $view = null, array $data = []): RouteHandler {
            return self::add($route)->loadView($view ?? $route, '', $data);
        }
        
        /**
         * @param string $name
         *
         * @return void
         */
        public static function controller(string $name) {
            if (class_exists($name)) {
                #uri_part();
            }
        }
        
        /**
         * Adds the views matching the given paths to the route handler.
         * If the view does not exist, it tries to find an alternative view with 'index' appended to the path.
         *
         * @param array|string $paths  The paths of the views to be added.
         * @param bool         $layout If set to true, the view will include a layout.
         *
         * @return void
         */
        public static function autoview(array|string $paths, bool|string $layout = true): void {
            $paths     = (array)$paths;
            $uri_parts = Request::uriParts();
            foreach ($paths as $path) {
                if (str_starts_with(uri(), $path)) {
                    while ($uri_parts) {
                        $uri  = implode('/', $uri_parts);
                        $view = view($uri, $layout);
                        if ($view->exists()) {
                            self::add($uri, [], $view);
                        } else {
                            $view = view(build_uri($uri, 'index'), $layout);
                            if ($view->exists()) {
                                self::add($uri, [], $view);
                            }
                        }
                        array_pop($uri_parts);
                    }
                }
            }
        }
        
        /**
         * Automatically includes and executes PHP files based on the given paths.
         *
         * @param array|string $paths The paths to the PHP files to be included and executed.
         *
         * @return void
         */
        public static function autofile(array|string $paths): void {
            self::autoview($paths, false);
        }
        
        /**
         * Tries to match the given directories with the current URI.
         *
         * @param mixed $directories The directories to be matched against.
         *
         * @return void
         */
        public static function try(mixed $directories): void {
            $directories = (array)$directories;
            $uri         = Str::explode('/', trim(uri(), '/'));
            foreach ($directories as $directory) {
                $max = 64;
                while ($uri) {
                    if (!$max--) {
                        break;
                    }
                    
                    $route     = build_uri(...$uri);
                    $view_name = $directory . '.' . trim(str_replace('/', '.', implode('.', $uri)), '.');
                    //dump($view_name);
                    $view = view(($directory === '' ? '' : $directory . '.') . implode('.', $uri), '');
                    //dump($route);
                    if ($view->exists()) {
                        //self::process('*', '*', $view);
                        self::add($route, [], $view);
                    }
                    /*else {
                        $view = view(($directory === '' ? '' : $directory . '.') . implode('.', $uri) . '.index', '');
                        if ($view->exists()) {
                            self::add($route, [], $view);
                        }
                    }
                    */
                    array_pop($uri);
                }
            }
        }
        
        /**
         * Adds a base route to each route in the given array.
         *
         * @param string $base_route The base route to be added to each route.
         * @param array  $routes     The array of routes to be modified.
         */
        public static function group(string $base_route, array $routes) {
            foreach ($routes as $route) {
                /** @var RouteHandler $route */
                $route->prepend($base_route);
            }
        }
        
        /**
         * Dispatches the request to the appropriate route handler.
         *
         * @return void
         */
        public static function dispatch(): void {
            foreach (self::$routes as $handler) {
                if ($handler->matches()) {
                    self::$handler = $handler;
                    break;
                }
            }
        }
        
    }
