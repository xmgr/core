<?php
    
    namespace Xmgr\Interfaces;
    
    /**
     * Interface Arrayable represents an object that can be converted into an array.
     */
    interface Arrayable {
        
        /**
         * Converts the object to an array representation.
         *
         * @return array The array representation of the object.
         */
        public function toArray(): array;
        
    }
