<?php
    
    namespace Xmgr\Interfaces;
    
    /**
     * Interface Jsonable
     *
     * This interface defines a contract for objects that can be serialized to JSON format.
     */
    interface Jsonable {
        
        /**
         * Converts the data of this object to a JSON string representation.
         *
         * @return string The JSON string representation of the object's data.
         */
        public function toJson(): string;
        
    }
