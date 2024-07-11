<?php
    
    namespace Xmgr\Controllers\Request;
    
    use Xmgr\Response;
    use Xmgr\Route;
    use Xmgr\RouteHandler;
    use Xmgr\View;
    
    /**
     * Class BaseRequestController
     *
     * The BaseRequestController is responsible for handling the incoming request and sending the response.
     */
    class BaseRequestController extends AbstractRequestController {
        
        public function __construct() {
        
        }
        
        /**
         * Handles the current route request and generates the output.
         *
         * @return void
         */
        public function handle() {
            Route::dispatch();
            $this->handler = Route::handler();
            
            ob_start();
            
            $handler = $this->handler;
            if ($handler instanceof RouteHandler) {
                echo $handler->handle();
            } else {
                $response = value($this->handler);
                
                switch (true) {
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
            }
            
            $this->output = ob_get_contents();
            ob_end_clean();
        }
        
        /**
         * Sends the output.
         *
         * @return void
         */
        public function send() {
            echo $this->output;
        }
        
    }
