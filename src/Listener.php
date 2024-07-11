<?php
    
    namespace Xmgr;
    
    /**
     * The Listener class is an abstract class that serves as the base for creating event listeners.
     *
     * Usage example:
     *
     * ```
     * class MyListener extends Listener {
     *     public function handle() {
     *         // Handle the event
     *     }
     * }
     *
     * $listener = new MyListener();
     * $listener->handle();
     * ```
     */
    abstract class Listener {
        
        /**
         * Handle method to be implemented by subclasses.
         *
         * @return void
         */
        abstract public function handle();
        
        /**
         * Subscribes to events.
         *
         * @param array $events The events to subscribe to.
         *
         * @return void
         */
        final protected function subscribe(array $events) {
        
        }
        
    }
