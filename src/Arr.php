<?php
    
    namespace Xmgr;
    
    use Random\RandomException;
    use Xmgr\Collections\Collection;
    
    /**
     * Class Arr
     *
     * The Arr class provides utility methods for working with arrays.
     */
    class Arr {
        
        /**
         * Obtains a random value from an array.
         *
         * @param array $data The array from which to pick a random item.
         *
         * @return mixed The randomly selected value from the array.
         *
         * @throws \Exception If the provided array is empty or if the random generator fails.
         */
        public static function randomItem(array $data): mixed {
            return $data[random_int(0, count($data) - 1)];
        }
        
        /**
         * Gibt ein Array zurück das nur die in $keys übergebenen Felder aus dem $data array enthält.
         *
         * @param array $data
         * @param array $keys
         *
         * @return array
         */
        public static function pick(array $data, array $keys): array {
            $result = [];
            foreach ($keys as $key) {
                if (!is_scalar($key)) {
                    continue;
                }
                if (data_has($data, $key)) {
                    data_set($result, $key, data_get($data, $key));
                }
            }
            
            return $result;
        }
        
        /**
         * Determine if the given key exists in the provided array.
         *
         * @param \ArrayAccess|array $array
         * @param string|int         $key
         *
         * @return bool
         */
        public static function exists($array, $key) {
            if ($array instanceof \ArrayAccess) {
                return $array->offsetExists($key);
            }
            if (!is_array($array)) {
                return false;
            }
            
            return array_key_exists($key, $array);
        }
        
        /**
         * Cross join the given arrays, returning all possible permutations.
         *
         * @param iterable ...$arrays
         *
         * @return array
         */
        public static function crossJoin(...$arrays) {
            $results = [[]];
            
            foreach ($arrays as $index => $array) {
                $append = [];
                
                foreach ($results as $product) {
                    foreach ($array as $item) {
                        $product[$index] = $item;
                        
                        $append[] = $product;
                    }
                }
                
                $results = $append;
            }
            
            return $results;
        }
        
        /**
         * Remove one or many array items from a given array using "dot" notation.
         *
         * @param array                  $array
         * @param array|string|int|float $keys
         *
         * @return void
         */
        public static function forget(array &$array, mixed $keys) {
            $original = &$array;
            
            $keys = (array)$keys;
            
            if (count($keys) === 0) {
                return;
            }
            
            foreach ($keys as $key) {
                // if the exact key exists in the top-level, remove it
                if (static::exists($array, $key)) {
                    unset($array[$key]);
                    
                    continue;
                }
                
                $parts = explode('.', $key);
                
                // clean up before each pass
                $array = &$original;
                
                while (count($parts) > 1) {
                    $part = array_shift($parts);
                    
                    if (isset($array[$part]) && static::accessible($array[$part])) {
                        $array = &$array[$part];
                    } else {
                        continue 2;
                    }
                }
                
                unset($array[array_shift($parts)]);
            }
        }
        
        /**
         * Collapse an array of arrays into a single array.
         *
         * @param iterable $array
         *
         * @return array
         */
        public static function collapse($array) {
            $results = [];
            
            foreach ($array as $values) {
                if ($values instanceof Collection) {
                    $values = $values->all();
                } elseif (!is_array($values)) {
                    continue;
                }
                
                $results[] = $values;
            }
            
            return array_merge([], ...$results);
        }
        
        /**
         * Check if the given value is array accessible.
         *
         * @param mixed $value
         *
         * @return bool
         */
        public static function accessible(mixed $value): bool {
            return is_array($value) || $value instanceof \ArrayAccess;
        }
        
        /**
         * Gibt das erste array item zurück
         *
         * @param mixed      $array
         * @param mixed|null $default
         *
         * @return array|mixed|null
         */
        public static function firstValue(mixed $array, mixed $default = null): mixed {
            return array_index($array, 0, $default);
        }
        
        /**
         * Gibt das letzte array item zurück
         *
         * @param mixed      $array
         * @param mixed|null $default
         *
         * @return array|mixed|null
         */
        public static function lastValue(mixed $array, mixed $default = null): mixed {
            return array_index($array, -1, $default);
        }
        
        /**
         * Returns an array of randomly selected values from the given array
         *
         * @param array $array    The array to select values from
         * @param int   $quantity The number of values to select from the array. If negative, the absolute value will
         *                        be used.
         *
         * @return array The array of randomly selected values from the given array
         * @throws RandomException
         */
        public static function randomItems(array $array, int $quantity): array {
            if (!$array) {
                return [];
            }
            $array    = array_values($array);
            $quantity = max(1, abs($quantity));
            $count    = count($array);
            $result   = [];
            while ($quantity--) {
                $result[] = $array[random_int(0, $count - 1)];
            }
            
            return $result;
        }
        
        /**
         * Convert a newline-delimited JSON string into an array of PHP values.
         * ---------------------------------------------------------------------
         *
         * Usage:
         * $jsonString = '{"name": "John", "age": 30}\n{"name": "Jane", "age": 25}\n{"name": "Bob", "age": 40}';
         * $result = array_fromNdJson($jsonString);
         * print_r($result);
         *
         * Output:
         * Array
         * (
         *     [0] => Array
         *         (
         *             [name] => John
         *             [age] => 30
         *         )
         *
         *     [1] => Array
         *         (
         *             [name] => Jane
         *             [age] => 25
         *         )
         *
         *     [2] => Array
         *         (
         *             [name] => Bob
         *             [age] => 40
         *         )
         * )
         *
         *
         * @param string $string The newline-delimited JSON string to convert.
         * @param bool   $assoc  Convert JSON objects to associative arrays. Default is true.
         *
         * @return array The resulting array containing the converted JSON objects.
         *
         */
        public static function fromNdJson(string $string, bool $assoc = true) {
            $arr   = [];
            $lines = explode("\n", $string);
            foreach ($lines as $line) {
                $json = json_decode($line, $assoc);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $arr[] = $json;
                }
            }
            
            return $arr;
        }
        
        /**
         * Checks if a given string is present in an array.
         *
         * @param array  $array       The array to search in.
         * @param string $string      The string to search for.
         * @param bool   $ignore_case If true, comparison will be case-insensitive. Defaults to true.
         *
         * @return bool Returns true if the string is found in the array, false otherwise.
         */
        public static function containsString(array $array, string $string, bool $ignore_case = true): bool {
            foreach ($array as $value) {
                if ($ignore_case ? strtolower($value) === strtolower($string) : ($value === $string)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Updates the value of an element at the specified index in an array.
         *
         * @param array $array The input array
         * @param int   $index The index at which the value will be updated (negative index is allowed)
         * @param mixed $value The new value to be assigned at the specified index
         *
         * @return array The updated array
         */
        public static function updateAt(array $array, int $index, mixed $value): array {
            $len = count($array);
            if ($index < 0) {
                $index = $len - min(abs($index), $len);
            } else {
                $index = min($index, $len - 1, $index);
            }
            $array[$index] = $value;
            
            return $array;
        }
        
        /**
         * Retrieves and removes an element from an array by its index.
         *
         * This function retrieves the value of the element at the specified index in the array and removes it from the
         * array. If the array is not an array or the index does not exist in the array, it returns the default value
         * instead.
         *
         * Note: index -1 means last element (same effect as array_pop())
         *
         * @param mixed &$array   The array from which to pull the element.
         * @param int    $index   The index of the element to retrieve and remove.
         * @param mixed  $default (Optional) The default value to return if the element does not exist.
         *
         * @return mixed  The value of the pulled element, or the default value if the element does not exist.
         */
        public static function array_pull_at(mixed &$array, int $index, mixed $default = null): mixed {
            if (is_array($array)) {
                $key = static::keyAt($array, $index);
                if (array_key_exists($key, $array)) {
                    $value = $array[$key];
                    unset($array[$key]);
                    
                    return $value;
                }
            }
            
            return $default;
        }
        
        /**
         * Iterates through each element in the given array using a callback function.
         * This function uses a recursive iterator to visit each element in a multidimensional array.
         *
         * @param array    $array       The array to iterate through.
         * @param callable $callback    The callback function to be called for each element.
         *                              The callback function will receive two arguments:
         *                              - The key of the current element.
         *                              - The value of the current element.
         *                              The callback function should have the following signature:
         *                              function callback($key, $value): void {}
         *
         * @return void
         */
        public static function iterate(array $array, callable $callback): void {
            foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array), \RecursiveIteratorIterator::SELF_FIRST) as $key => $value) {
                $callback($key, $value);
            }
        }
        
        /**
         * Checks if all specified keys exist in an array.
         *
         * @param mixed $array The array to check for key existence.
         * @param array $keys  An array of keys to check.
         *
         * @return bool Returns true if all keys exist in the array, false otherwise.
         */
        public static function hasAllKeys(mixed $array, array $keys): bool {
            if (!is_array($array)) {
                return false;
            }
            foreach ($keys as $key) {
                if (!array_key_exists($key, $array)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Unsets array elements based on the provided action.
         * This method iterates over each element of the array and applies the provided action to determine if the
         * element should be unset. If the action returns true for a specific key-value pair, that pair will be removed
         * from the array using the unset() function.
         *
         * @param array    $data   The input array to be modified
         * @param \Closure $action The closure to be called for each key-value pair of the array.
         *                         The closure takes two parameters: the key and the value of the current element and
         *                         should return a boolean value. If the closure returns true, the corresponding
         *                         element
         *                         will be unset.
         *
         * @return array
         */
        public static function unsetWhere(array $data, \Closure $action): array {
            foreach ($data as $key => $value) {
                if ($action($key, $value) === true) {
                    unset($data[$key]);
                }
            }
            
            return $data;
        }
        
        /**
         * Checks if all values in an array match a given value or a callable function.
         *
         * What this function does:
         * 1) If the array is empty, it returns false immediately.
         * 2) It iterates through each value in the array.
         * 3) If the compare parameter is a callable function or an instance of \Closure,
         *    it calls the compare function with the value as the argument and checks if the returned value is true.
         * 4) If the compare parameter is not a callable function or an instance of \Closure,
         *    it simply checks if the value is equal to the compare parameter.
         * 5) If any value does not match the compare parameter, it returns false.
         * 6) If all values match the compare parameter, it returns true.
         *
         * Example:
         * Input.....: [1, 2, 3, 4, 5], 2
         * Output....: false
         * Explanation: The compare parameter is not equal to all values in the array.
         *
         * @param array $array   The array to check
         * @param mixed $compare The value or callable function to compare against
         *
         * @return bool          Returns true if all values in the array match the compare parameter, false otherwise
         */
        public static function allValuesMatch(array $array, mixed $compare): bool {
            if (!$array) {
                return false;
            }
            foreach ($array as $value) {
                if (is_callable($compare) || $compare instanceof \Closure) {
                    if ($compare($value) !== true) {
                        return false;
                    }
                } else {
                    if ($value !== $compare) {
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Removes and returns a value from an array based on the specified key.
         * If the array is not an array or the key does not exist, the default value is returned.
         * Once the value is pulled, it is also removed from the array.
         *
         * @param mixed      $array   The input array
         * @param mixed      $key     The key of the value to be pulled
         * @param mixed|null $default The default value to return if the array is not an array or the key does not exist
         *                            (optional, default: null)
         *
         * @return mixed  The pulled value if found, otherwise the default value
         */
        public static function array_pull(mixed &$array, mixed $key, mixed $default = null): mixed {
            if (!is_array($array)) {
                return $default;
            }
            $value = arr($array, $key, $default);
            unset($array[$key]);
            
            return $value;
        }
        
        /**
         * Plucks values from an array of arrays, based on a given key.
         * What this function does:
         * 1) It initializes an empty array to store the resulting values.
         * 2) It checks if the input array is actually an array.
         * 3) If it is, it iterates over each element of the input array.
         * 4) For each element, it checks if the element is an array and if the given key exists in that element.
         * 5) If the key exists, it adds the element to the resulting array.
         * 6) Finally, it returns the resulting array.
         *
         * @param mixed $array The input array to extract values from
         * @param mixed $key   The key to use for extracting values
         *
         * @return array The resulting array with values from the input array, based on the given key
         */
        public static function pluck(mixed $array, mixed $key): array {
            $result = [];
            if (is_array($array)) {
                foreach ($array as $value) {
                    if (is_array($value) && array_key_exists($key, $value)) {
                        $result[] = $value;
                    }
                }
            }
            
            return $result;
        }
        
        /**
         * @param mixed      $input   Input value
         * @param array      $allowed Array containing valid values
         * @param mixed|null $else    Default value. If this is NULL, the first array from $allowed is the default value
         * @param bool       $strict
         *
         * @return mixed
         */
        public static function array_allow(mixed $input, array $allowed, mixed $else = null, bool $strict = true): mixed {
            if (!$allowed) {
                return $input;
            }
            $else = $else ?? $allowed[0];
            foreach ($allowed as $a) {
                if ($strict ? $input === $a : $input == $a) {
                    return $input;
                }
            }
            
            return $else;
        }
        
        /**
         * Returns the mapped value for a given key in an associative array.
         *
         * @param mixed      $value    The key to be mapped.
         * @param array      $data     The associative array containing the mappings.
         * @param mixed|null $fallback (Optional) The fallback value to be returned if the key is not found in the
         *                             array. Default is null.
         *
         * @return mixed                  The mapped value if the key is found in the array, otherwise the fallback
         *                                value.
         */
        public static function valmap(mixed $value, array $data, mixed $fallback = null): mixed {
            foreach ($data as $k => $v) {
                if ($value === $k) {
                    return $v;
                }
            }
            
            return $fallback;
        }
        
        /**
         * Retrieves the key at a specific index in an array, or returns a default value if the index is out of bounds.
         *
         * This method takes an array and returns the key at the specified index. If the index is out of bounds, it
         * returns a default value instead. The array keys are sorted in ascending order before retrieving the key at
         * the index.
         *
         * Example:
         * Input array.....: ['a' => 1, 'b' => 2, 'c' => 3]
         * Index...........: 1
         * Default value...: 'N/A'
         * Output..........: 'b'
         *
         * @param array $array   The array from which to retrieve the key
         * @param int   $index   The index at which to retrieve the key
         * @param mixed $default Optional. The default value to return if the index is out of bounds. Default is null.
         *
         * @return mixed The key at the specified index, or the default value if the index is out of bounds
         */
        public static function keyAt(array $array, int $index, mixed $default = null): mixed {
            return arr(array_slice(array_keys($array), $index, 1), 0, $default);
        }
        
        /**
         * Flatten a multi-dimensional array into a dot notation array.
         * ----------------------------------------------------------------
         * Usage:
         * array_dot(['a' => ['b' => 'c']]);            // ['a.b' => 'c']
         * array_dot(['a' => ['b' => 'c']], false);     // ['a' => ['b' => 'c']]
         *
         * @param array $array The multi-dimensional array to be flattened.
         * @param bool  $full  Specify whether to include the full key path in the resulting dot notation array.
         *
         * @return array The dot notation array.
         */
        public static function array_dot(array $array, bool $full = true): array {
            $fn = function ($array, $prepend = '') use (&$fn, $full) {
                $results = [];
                
                foreach ($array as $key => $value) {
                    if ($full) {
                        $results[$prepend . $key] = $value;
                    }
                    if (is_array($value) && !empty($value)) {
                        $results = array_merge($results, $fn($value, $prepend . $key . '.'));
                    } else {
                        if (!$full) {
                            $results[$prepend . $key] = $value;
                        }
                    }
                }
                
                return $results;
            };
            
            return $fn($array);
        }
        
        /**
         * Inserts a value into an array at a specified index.
         *
         * This function inserts the given value into the specified array at the specified index. It uses the
         * `array_splice()` function to perform the insertion. The original array is modified by this function.
         *
         * @param mixed $array   The input array.
         * @param int   $index   The index at which to insert the value. If the index is negative, it is interpreted as
         *                       counting from the end of the array.
         * @param mixed $value   The value to insert into the array.
         * @param mixed $key     If the key is not null, the value will be inserted with the specified key.
         *                       If that key already exists in the array, it will be dropped before.
         *
         * @return array         The modified array with the value inserted at the specified index.
         */
        public static function insert(mixed $array, int $index, mixed $value, mixed $key = null): array {
            if ($key !== null) {
                unset($array[$key]);
            }
            $part1 = array_slice($array, 0, $index, true);
            $part2 = array_slice($array, $index, null, true);
            if ($key === null) {
                $part1[] = $value;
            } else {
                $part1[$key] = $value;
            }
            
            return array_merge($part1, $part2);
        }
        
        /**
         * Make CSV string from given array.
         * NOTE: >> If a cell value contains a non-scalar value, it will be json-encoded.
         *       >> NULL values are converted to blank string.
         *
         * @param array  $array     Input array
         * @param string $delimiter Column delimiter
         * @param string $enclosure Enclosure string for cell values that contain spaces
         * @param string $escape    Escape character for literal usage of delimiter- or enclosure-characters
         *
         * @return string The result CSV string
         */
        public static function toCsvString(array $array, string $delimiter = ',', string $enclosure = '"', string $escape = "\\"): string {
            $h = fopen('php://temp', 'r+');
            if ($h !== false) {
                foreach ($array as $row) {
                    if (is_array($row)) {
                        foreach ($row as &$r) {
                            $r = (is_array($r) ? json_encode($r) : ($r === null ? '' : $r));
                        }
                        fputcsv($h, $row, $delimiter, $enclosure, $escape);
                    }
                }
                $length = ftell($h) + 1;
                rewind($h);
                $data = fread($h, $length);
                fclose($h);
                
                return rtrim($data, "\n");
            }
            
            return '';
        }
        
        /**
         * Parse CSV to array
         *
         * @param string        $csv_string The CSV string
         * @param int           $skip_rows  Optional: skip first N rows
         * @param array         $only       Optional: specify which columns you wanna keep.
         *                                  E.g. [0,1,4] would add the first, second and 5th column to each row.
         * @param callable|null $callback   Optional: a callback to modify and validate. Make sure you receive the $row
         *                                  array by reference so you are able to modify it. NOTE: if this callable
         *                                  returns false, the corresponding row is skipped.
         * @param string        $separator  Column separator
         * @param string        $enclosure  Enclosure string, be default double quotes are used
         * @param string        $escape     For escaping characters
         *
         * @return array The result CSV array
         */
        public static function fromCsvString(string $csv_string, int $skip_rows = 0, array $only = [], callable $callback = null, string $separator = ',', string $enclosure = '"', string $escape = "\\"): array {
            $h = fopen('php://temp', 'r+');
            if ($h !== false) {
                $data = [];
                if (fwrite($h, $csv_string) !== false) {
                    rewind($h);
                    $skip_rows = abs($skip_rows);
                    $i         = 0;
                    while (($row = fgetcsv($h, null, $separator, $enclosure, $escape)) !== false) {
                        if (is_array($row) && (!$skip_rows || $i >= $skip_rows)) {
                            $csvrow = ($only ? array_pick($row, $only) : $row);
                            if ($callback === null || (is_callable($callback) && $callback($csvrow) !== false)) {
                                $data[] = $csvrow;
                            }
                        }
                        $i++;
                    }
                }
                fclose($h);
                
                return $data;
            }
            
            return [];
        }
        
    }
