<?php
    
    namespace Xmgr\Database;
    
    /**
     * This abstract class represents an attribute cast.
     * Attribute casts are used to set and retrieve values for variables.
     */
    abstract class AttributeCast {
        
        /**
         * Retrieves the value of a given variable.
         *
         * @param mixed $value The variable to get the value of.
         */
        abstract public function get(mixed $value): mixed;
        
        /**
         * Set the value for the given variable.
         *
         * @param mixed $value The value to be set for the variable.
         */
        abstract public function set(mixed $value): mixed;
        
    }
