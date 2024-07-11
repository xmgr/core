<?php
    
    namespace Xmgr\Interfaces;
    
    /**
     * Interface Stringable
     *
     * The Stringable interface represents an object that can be converted to a string.
     * All classes implementing this interface must define a toString() method that returns a string representation of
     * the object.
     */
    interface Stringable {
        
        /**
         * Returns a string representation of the object.
         *
         * This method should be overridden by subclasses to provide a specific implementation.
         *
         * @return string A string representation of the object.
         */
        public function toString(): string;
        
    }
