<?php
    
    namespace Xmgr\Interfaces;
    
    /**
     * Represents an entity that can be dispatched.
     *
     * Dispatchable objects can be processed or executed by a specific mechanism.
     */
    interface Dispatchable {
        
        /**
         * Dispatches the specified request to the appropriate controller and action.
         *
         * This method is responsible for resolving the incoming request and determining
         * which controller and action should be executed to handle the request.
         * It determines the appropriate controller and action based on the request
         * parameters and routes defined in the application configuration.
         *
         * @return void
         * @throws \Throwable if the request is invalid or cannot be handled
         *
         */
        public function dispatch();
        
    }
