<?php
    
    namespace Xmgr\Controllers\Request;
    
    /**
     * The ControllerHandler class is responsible for handling controller actions.
     */
    class ControllerHandler {
        
        protected mixed  $object;
        protected string $action;
        
        /**
         * Construct a new instance of the class.
         *
         * @param object $object The object to be assigned.
         * @param string $action The action to be assigned.
         *
         * @return void
         */
        public function __construct(object $object, string $action) {
            $this->object = $object;
            $this->action = $action;
        }
        
        /**
         * Executes the specified action on the given object.
         *
         * @return void
         */
        public function run(): void {
            echo $this->object->{$this->action}();
        }
        
    }
