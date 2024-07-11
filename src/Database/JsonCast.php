<?php
    
    namespace Xmgr\Database;
    
    /**
     * Class JsonCast
     *
     * This class extends the AttributeCast class and provides methods to encode and decode values as JSON.
     */
    class JsonCast extends AttributeCast {
        
        /**
         * Set the value and encode it as JSON.
         *
         * @param mixed $value The value to be set.
         *
         * @return string The JSON-encoded value.
         */
        public function set(mixed $value): string {
            return json_encode($value);
        }
        
        /**
         * Retrieves the json data from the given value
         *
         * @param mixed $value The value to retrieve json data from
         *
         * @return mixed The decoded json data or an empty array if value is not a valid json
         */
        public function get(mixed $value): mixed {
            return json_validate($value) ? json_decode($value, true) : [];
        }
        
    }
