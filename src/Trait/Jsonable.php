<?php
    
    namespace Xmgr\Trait;
    
    /**
     * Jsonable trait.
     *
     * This trait provides a method to convert the data object to a JSON string.
     *
     * @package MyApp
     */
    trait Jsonable {
        
        /**
         * Converts the data object to a JSON string.
         *
         * @param int   $flags    (optional) The encoding options. Default is 0.
         * @param mixed $fallback (optional) The fallback value to use if encoding fails. Default is an empty string.
         *
         * @return string The JSON encoded string on successful encoding, or null if encoding fails and no
         *                     fallback is provided.
         */
        public function toJson(int $flags = 0, string $fallback = ''): string {
            if (property_exists($this, 'data')) {
                return json_encode($this->data, $flags) ?? $fallback;
            }
            
            return $fallback;
        }
        
    }
