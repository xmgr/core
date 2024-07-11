<?php
    
    namespace Xmgr;
    
    use Xmgr\Controllers\Request\CliController;
    use Xmgr\Controllers\Request\HttpController;
    
    /**
     * The System class is responsible for handling the incoming request and routing it to the appropriate controller.
     */
    class System {
        
        /**
         * Handles the incoming request and routes it to the appropriate controller.
         *
         * @return object The controller instance that handles the request.
         */
        public static function handleRequest() {
            switch (true) {
                # Console / CLI
                case self::isCliRequest():
                    $handler = new CliController();
                    break;
                # HTTP Request
                case isset($_SERVER['HTTP_HOST']):
                    if (!session_id()) {
                        session_start();
                    }
                    $route_handlers = (array)config('app.route.handlers');
                    foreach ($route_handlers as $handler) {
                        $router_file = path((string)config('path.routes', 'routes'), $handler . '.php');
                        if (is_file($router_file)) {
                            require_once $router_file;
                        }
                    }
                    $handler = new HttpController();
                    break;
                # Other stuff is going on
                default:
                    exit('Invalid request!');
            }
            
            $handler->handle();
            
            return $handler;
        }
        
        /**
         * Determines if the current request is a CLI request.
         *
         * @return bool|null Returns true if the request is a CLI request, false if it is not a CLI request,
         *                  or null if the determination cannot be made.
         */
        public static function isCliRequest(): ?bool {
            return \is_cli();
        }
        
        /**
         * Determines if the current request is a web request.
         *
         * @return bool Returns true if the request is a web request, false otherwise.
         */
        public static function isWebRequest(): bool {
            return isset($_SERVER['HTTP_HOST']);
        }
        
    }
