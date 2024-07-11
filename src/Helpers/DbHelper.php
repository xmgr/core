<?php
    
    namespace Xmgr\Helpers;
    
    /**
     * Class DbHelper
     *
     * The DbHelper class provides methods to map key-value pairs in an input array to an associative array.
     *
     * @deprecated
     */
    class DbHelper {
        
        /**
         * Maps each key-value pair in the input array to a new associative array using the functions dbkey() and
         * dbvalue(). This is useful for mapping database records to associative arrays that use different keys or
         * values.
         * -
         * The resulting associative array will have the same number of elements as the input array, with each key
         * mapped to its corresponding value.
         *
         * @param array $data Input array to map
         *
         * @return array The resulting associative array with mapped keys and values.
         * @deprecated
         */
        public static function dbassoc(array $data) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[dbkey($key)] = dbvalue($value);
            }
            
            return $result;
        }
        
    }
