<?php
    
    namespace Xmgr;
    
    use ReturnTypeWillChange;
    use Xmgr\Collections\Collection;
    use Xmgr\Interfaces\Arrayable;
    use Xmgr\Interfaces\Jsonable;
    
    /**
     * Represents an array value
     */
    class Data implements \ArrayAccess, \JsonSerializable, \Countable, Arrayable, Jsonable {
        
        /** @var array|object $data */
        protected array|object $data = [];
        protected array        $meta = [];
        
        protected string $mandatory = '';
        
        /**
         * @param mixed $data
         * @param array $meta
         */
        public function __construct(mixed $data = [], array $meta = []) {
            $this->setup($data);
            $this->meta = $meta;
            $this->init();
        }
        
        /**
         * Wird direkt nach dem construct aufgerufen und kann in der abgeleiteten Klasse konkret implementiert werden
         *
         * @return void
         */
        public function init() {
        
        }
        
        /**
         * Set up the data for the object.
         *
         * @param mixed $data The data to set up. Accepts an array, an object, or an instance of the same class.
         *
         * @return void
         */
        final protected function setup(mixed $data = []) {
            switch (true) {
                # Array oder Objekt können wir direkt setzen
                case is_array($data) || is_object($data):
                    $this->data = $data;
                    break;
                # Bei static holen wir die tatsächlichen Daten ab
                case $data instanceof static:
                    $this->data = $data->export();
                    break;
                # Ansonsten tun wir als ob es ein Array gäbe
                default:
                    $this->data = [$data];
                    break;
            }
        }
        
        /**
         * Create an array of objects based on the given data.
         *
         * @param array $data The data to create objects from.
         *
         * @return array An array of objects created from the data.
         */
        public static function make(array $data): array {
            $result = [];
            foreach ($data as $key => $d) {
                $obj          = new static($d, ['key' => $key]);
                $result[$key] = $obj;
            }
            
            return $result;
        }
        
        /**
         * Checks if the specified element exists in the mandatory list.
         *
         * @return bool True if the element exists, False otherwise.
         */
        public function exists(): bool {
            return $this->has($this->mandatory);
        }
        
        /**
         * Get the value of a meta key.
         *
         * @param mixed|null $key     The meta key.
         * @param mixed|null $default The default value if the key doesn't exist.
         *
         * @return mixed|null The value of the meta key, or the default value if the key doesn't exist.
         */
        public function meta(mixed $key = null, mixed $default = null): mixed {
            return $key === null ? $this->meta : data_get($this->meta, $key, $default);
        }
        
        /**
         * Sets the meta data.
         *
         * @param array $data The meta data to be set.
         *
         * @return $this
         */
        public function setMeta(array $data) {
            $this->meta = $data;
            
            return $this;
        }
        
        /**
         * Adds meta data to the object.
         *
         * @param array $data The meta data to be added.
         *
         * @return $this The current object instance.
         */
        public function addMeta(array $data) {
            $this->meta = array_replace_recursive($this->meta, $data);
            
            return $this;
        }
        
        /**
         * Checks if the given offset exists in the data array.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool True if the offset exists in the data array, false otherwise.
         */
        public function has(mixed $offset): bool {
            return data_has($this->data, $offset);
        }
        
        /**
         * Check if any of the keys exist in the data.
         *
         * @param array $keys The keys to check.
         *
         * @return bool Returns true if any of the keys exist, false otherwise.
         */
        public function hasAnyKey(array $keys): bool {
            if (!$this->data) {
                return false;
            }
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Check if the array has all the specified keys.
         *
         * @param array $keys The keys to check for.
         *
         * @return bool True if the array has all the keys, false otherwise.
         */
        public function hasAllKeys(array $keys): bool {
            if (!$this->data) {
                return false;
            }
            foreach ($keys as $key) {
                if (!$this->has($key)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Get the value for a given key from the data array, or return a default value if the key does not exist.
         *
         * @param mixed      $key     The key to retrieve the value for.
         * @param mixed|null $default The default value to return if the key does not exist (optional).
         *
         * @return mixed The value for the given key, or the default value if the key does not exist.
         */
        public function get(mixed $key, mixed $default = null): mixed {
            return data_get($this->data, $key, $default);
        }
        
        /**
         * Sets a value in the data array.
         *
         * @param mixed      $key   The key to set the value for.
         * @param mixed|null $value The value to set. Defaults to null.
         *
         * @return void
         */
        public function set(mixed $key, mixed $value = null): void {
            data_set($this->data, $key, $value);
        }
        
        /**
         * Forget a value from the data array.
         *
         * @param mixed $key
         *
         * @return $this
         */
        public function forget(mixed $key): static {
            data_forget($this->data, $key);
            
            return $this;
        }
        
        /**
         * Check if the given value exists in the internal data array.
         *
         * @param mixed $value  The value to check for.
         * @param bool  $strict (optional) Whether to use strict comparison. Default is true.
         *
         * @return bool Returns true if the value exists, false otherwise.
         */
        public function contains(mixed $value, bool $strict = true): bool {
            return in_array(value($value), $this->data, $strict);
        }
        
        /**
         * Checks if any of the specified values exist in the data.
         *
         * @param array $values The values to check.
         * @param bool  $strict (optional) Whether to perform a strict comparison. Default is true.
         *
         * @return bool Returns true if any of the values exist in the data, false otherwise.
         */
        public function containsOneOf(array $values, bool $strict = true): bool {
            if (!$this->data) {
                return false;
            }
            foreach ($values as $value) {
                if ($this->contains($value, $strict)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Checks if all of the specified values exist in the data.
         *
         * @param array $values The values to check.
         * @param bool  $strict (optional) Whether to perform a strict comparison. Default is true.
         *
         * @return bool Returns true if all of the values exist in the data, false otherwise.
         */
        public function containsAll(array $values, bool $strict = true): bool {
            if (!$this->data) {
                return false;
            }
            foreach ($values as $value) {
                if (!$this->contains($value, $strict)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Retrieves the value associated with the given key and returns an instance of the Expect class.
         *
         * @param mixed $key The key associated with the value to retrieve.
         *
         * @return Expectation An instance of the Expect class that contains the retrieved value.
         */
        public function expect(mixed $key) {
            return \expect($this->get($key));
        }
        
        /**
         * Checks if the given value is equal to the data.
         *
         * @param array $value The value to compare.
         *
         * @return bool Returns true if the value is equal to the data, false otherwise.
         */
        public function equals(array $value): bool {
            return $this->data === $value;
        }
        
        # -- Magic getter und setter
        
        /**
         * Magic method to get the value of a property.
         *
         * @param string $name The name of the property to get.
         *
         * @return mixed The value of the property.
         */
        public function __get(string $name) {
            return $this->get($name);
        }
        
        /**
         * Magic method for setting values.
         *
         * @param string $name  The name of the value to set.
         * @param mixed  $value The value to set.
         *
         * @return void
         */
        public function __set(string $name, mixed $value) {
            $this->set($name, $value);
        }
        
        # -- Getter by type
        
        /**
         * Finds the first value associated with a given key in the data.
         *
         * @param array      $keys    The keys to search for.
         * @param mixed|null $default (optional) The default value to return if no matching key is found. Default is
         *                            null.
         *
         * @return mixed The value associated with the first matching key, or the default value if no matching key is
         *               found.
         */
        public function find(array $keys, mixed $default = null): mixed {
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    return $this->get($key);
                }
            }
            
            return $default;
        }
        
        /**
         * Finds the first key in the array of keys that exists in the data.
         *
         * @param array      $keys    The keys to search for.
         * @param mixed|null $default (optional) The default value to return if none of the keys are found. Default is
         *                            null.
         *
         * @return mixed Returns the first key from the array of keys that exists in the data. If none of the keys are
         *               found, the default value is returned.
         */
        public function findKey(array $keys, mixed $default = null) {
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    return $key;
                }
            }
            
            return $default;
        }
        
        /**
         * Converts the value of the specified key to an integer.
         *
         * @param mixed $key The key of the value to convert to an integer.
         *
         * @return int Returns the value of the specified key as an integer.
         */
        public function int(mixed $key): int {
            return (int)$this->get($key);
        }
        
        /**
         * Returns the value associated with the specified key as a string.
         *
         * @param mixed $key The key to retrieve the value from.
         *
         * @return string The value associated with the specified key as a string.
         */
        public function string(mixed $key): string {
            return (string)$this->get($key);
        }
        
        /**
         * Retrieves the value associated with the given key and converts it to a float.
         *
         * @param mixed $key The key to retrieve the value for.
         *
         * @return float The value associated with the given key, converted to float.
         */
        public function float(mixed $key): float {
            return (float)$this->get($key);
        }
        
        /**
         * Retrieves the boolean value associated with the specified key.
         *
         * @param mixed $key The key to retrieve the boolean value for.
         *
         * @return bool Returns the boolean value associated with the specified key.
         */
        public function bool(mixed $key): bool {
            return (bool)$this->get($key);
        }
        
        /**
         * Casts the value retrieved from the specified key as an object.
         *
         * @param mixed $key The key to retrieve the value from.
         *
         * @return object Returns the value from the specified key casted as an object.
         */
        public function object(mixed $key): object {
            return (object)$this->get($key);
        }
        
        /**
         * Converts the values of the specified key into objects.
         *
         * @param mixed $key The key of the values to convert.
         *
         * @return array|object[] Returns an array of objects with the values of the specified key converted into
         *                        objects.
         */
        public function objects(mixed $key): array {
            $data = $this->array($key);
            foreach ($data as &$d) {
                $d = (object)$d;
            }
            
            return $data;
        }
        
        /**
         * Converts the value of the specified key to an array.
         *
         * @param mixed $key The key to retrieve the value from.
         *
         * @return array Returns the value of the specified key as an array.
         */
        public function array(mixed $key): array {
            return (array)$this->get($key);
        }
        
        /**
         * Returns a new instance of the specified class using the value of the given key in the data.
         *
         * @param mixed       $key   The key to retrieve the value from the data.
         * @param string|null $class (optional) The class to instantiate. Default is the current class.
         *
         * @return self An instance of the specified class with the value of the given key in the data.
         */
        public function model(mixed $key, ?string $class = null): self {
            $class = $class !== null && class_exists($class) ? $class : static::class;
            
            return new $class($this->get($key));
        }
        
        /**
         * Creates model instances for the specified key.
         *
         * @param mixed       $key   The key to create model instances for.
         * @param string|null $class (optional) The class name of the model. Default is the current class.
         *
         * @return array An array of model instances created for the specified key.
         */
        public function models(mixed $key, ?string $class = null): array {
            $class = $class !== null && class_exists($class) ? $class : static::class;
            
            return $class::make($this->array($key));
        }
        
        # -- Manipulatoren
        
        /**
         * Convert all scalar values and nulls to their string representations in the data.
         *
         * @return void
         */
        public function stringify(): void {
            array_walk_recursive($this->data, function (&$item, $key) {
                if (is_scalar($item) || $item === null) {
                    $item = (string)$item;
                }
            });
        }
        
        # -- Abfrage nach Typenreinheit
        
        /**
         * Checks if all values in the data match the specified callback function.
         *
         * @param \Closure $callback The callback function to match each value.
         *
         * @return bool Returns true if all values match the callback function, false otherwise.
         */
        public function allValuesMatch(\Closure $callback): bool {
            if (!$this->data) {
                return false;
            }
            foreach ($this->data as $item) {
                if (!$callback($item)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Checks if all values in the data are strings.
         *
         * @return bool Returns true if all values in the data are strings, false otherwise.
         */
        public function isStringArray(): bool {
            return $this->allValuesMatch(function ($item) {
                return is_string($item);
            });
        }
        
        /**
         * Checks if all values in the data are numeric.
         *
         * @return bool Returns true if all values in the data are numeric, false otherwise.
         */
        public function isNumericArray(): bool {
            return $this->allValuesMatch(function ($item) {
                return is_int($item) || is_float($item);
            });
        }
        
        /**
         * Checks if all values in the array are objects.
         *
         * @return bool Returns true if all values in the array are objects, false otherwise.
         */
        public function isObjectArray(): bool {
            return $this->allValuesMatch(function ($item) {
                return is_object($item);
            });
        }
        
        /**
         * Checks if the data is multidimensional.
         *
         * @return bool Returns true if the data is multidimensional, false otherwise.
         */
        public function isMultidimensional(): bool {
            return $this->allValuesMatch(function ($item) {
                return is_array($item);
            });
        }
        
        # -- Array Helper
        
        /**
         * Returns an array of keys from the data.
         *
         * @return array An array of keys from the data.
         */
        public function keys(): array {
            return array_keys($this->data);
        }
        
        /**
         * Returns an array of all values in the data.
         *
         * @return array An array containing all the values in the data.
         */
        public function values(): array {
            return array_values($this->data);
        }
        
        /**
         * Reindexes the elements of the data array.
         *
         * @return $this Returns the current object.
         */
        public function reindex() {
            $this->data = array_values($this->data);
            
            return $this;
        }
        
        /**
         * Removes the specified keys from the data.
         *
         * @param array $keys The keys to remove from the data.
         *
         * @return $this Returns an instance of the current object, after removing the keys from the data.
         */
        public function removeKeys(array $keys): self {
            foreach ($keys as $key) {
                unset($this->data[$key]);
            }
            
            return $this;
        }
        
        /**
         * Renames keys in the data array.
         *
         * @param array $keys An associative array where the keys represent the old keys and the values represent the
         *                    new keys.
         *
         * @return $this Returns the current object after renaming the keys.
         */
        public function rename(array $keys): self {
            foreach ($keys as $old_key => $new_key) {
                $value = $this->get($old_key);
                $this->forget($old_key);
                $this->set($new_key, $value);
            }
            
            return $this;
        }
        
        /**
         * Keeps only the specified keys in the data and discards the rest.
         *
         * @param array $keys The keys to keep in the data.
         *
         * @return self Returns an instance of the class with the updated data.
         */
        public function keep(array $keys): self {
            $data = [];
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    data_set($data, $key, $this->get($key));
                }
            }
            $this->data = $data;
            
            return $this;
        }
        
        # --
        
        /**
         * Iterates over an array recursively and applies a callback function to each key-value pair.
         *
         * @param array &  $data     The array to iterate over.
         * @param \Closure $callback The callback function to apply to each key-value pair. The function should accept
         *                           two parameters: the key and the value.
         *
         * @return void
         */
        final protected function iterate(&$data, \Closure $callback): void {
            foreach ($data as $key => &$value) {
                $callback($key, $value);
                if (is_array($value)) {
                    $this->iterate($value, $callback);
                }
            }
        }
        
        /**
         * Modifies the data using the specified callback function.
         *
         * @param \Closure $callback The callback function to modify the data.
         *
         * @return static Returns an instance of the modified data.
         */
        public function modify(\Closure $callback): static {
            $this->iterate($this->data, $callback);
            
            return $this;
        }
        
        /**
         * Create a new instance of the current class using the value associated with the specified key.
         *
         * @param mixed $key The key associated with the value to use.
         *
         * @return self Returns a new instance of the current class with the value associated with the specified key.
         */
        public function scope(mixed $key): self {
            return new self($this->get($key));
        }
        
        /**
         * Converts objects in the data to arrays.
         *
         * @return $this Returns the modified object with objects converted to arrays.
         */
        public function objectsToArray(): self {
            $this->modify(function ($key, &$value) {
                if (is_object($value)) {
                    $value = (array)$value;
                }
            });
            
            return $this;
        }
        
        /**
         * Converts arrays within the data to objects.
         *
         * @return $this Returns the modified object.
         */
        public function arraysToObject(): self {
            $this->modify(function ($key, &$value) {
                if (is_array($value)) {
                    $value = (object)$value;
                }
            });
            
            return $this;
        }
        
        # -- Konvertieren der Daten
        
        /**
         * Converts the object to a JSON string.
         *
         * @return string Returns the object converted to a JSON string.
         */
        public function toJson(): string {
            return (string)$this->jsonSerialize();
        }
        
        /**
         * Converts the data to an object.
         *
         * @return object Returns the data converted to an object.
         */
        public function toObject(): object {
            return (object)$this->data;
        }
        
        /**
         * Converts the data to an array.
         *
         * @param array $keys (optional) The keys to include in the resulting array. Default is an empty array.
         *
         * @return array Returns an array representation of the data.
         */
        public function toArray(array $keys = []): array {
            return ($keys ? Arr::pick($this->data, $keys) : $this->data);
        }
        
        /**
         * Maps the existing keys in the data to new keys and returns an array with the new keys mapped to their
         * corresponding values.
         *
         * @param array $keys An associative array where the keys are the existing keys to map and the values are the
         *                    new keys to map to.
         *
         * @return array An associative array with the new keys mapped to their corresponding values from the data.
         */
        public function toMappedArray(array $keys): array {
            $result = [];
            foreach ($keys as $old_key => $new_key) {
                if (is_int($old_key)) {
                    $old_key = $new_key;
                }
                if ($this->has($old_key)) {
                    data_set($result, $new_key, $this->get($old_key));
                }
            }
            
            return $result;
        }
        
        /**
         * Export the data.
         *
         * @return object|array The exported data.
         */
        public function export(): object|array {
            return $this->data;
        }
        
        /**
         * Magic method that allows setting data values dynamically based on the method called.
         *
         * @param string $method The name of the method called.
         * @param array  $args   The arguments passed to the method.
         *
         * @return $this Returns the modified instance of the class.
         */
        public function __call(string $method, array $args) {
            $this->data[$method] = count($args) > 0 ? reset($args) : true;
            
            return $this;
        }
        
        /**
         * Converts the data to a collection.
         *
         * @return Collection The data converted to a collection.
         */
        public function toCollection() {
            return \collect(...$this->data);
        }
        
        # -- ArrayAccess method stubs
        
        /**
         * Checks if the specified offset exists.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool Returns true if the offset exists, false otherwise.
         */
        #[ReturnTypeWillChange] public function offsetExists(mixed $offset): bool {
            return $this->has($offset);
        }
        
        /**
         * Retrieves the value at the specified offset.
         *
         * @param mixed $offset The offset to retrieve the value from.
         *
         * @return mixed Returns the value at the specified offset, or null if the offset does not exist.
         *
         * @deprecated This method may change its return type in the future.
         */
        #[ReturnTypeWillChange] public function offsetGet(mixed $offset): mixed {
            return $this->get($offset, null);
        }
        
        /**
         * Sets the value at the specified offset.
         *
         * @param mixed $offset The offset to set the value at.
         * @param mixed $value  The value to set.
         *
         * @return void
         *
         * @note This method uses the #[ReturnTypeWillChange] attribute indicating that the return type may change in
         *       future versions of PHP.
         */
        #[ReturnTypeWillChange] public function offsetSet(mixed $offset, mixed $value): void {
            $this->set($offset, $value);
        }
        
        /**
         * Removes the element at the specified offset.
         *
         * Note: This method uses the #[ReturnTypeWillChange] attribute, which indicates that the return type of the
         * method may change in future versions of PHP. Please refer to the PHP documentation for further information.
         *
         * @param mixed $offset The offset of the element to remove.
         *
         * @return void
         */
        #[ReturnTypeWillChange] public function offsetUnset(mixed $offset): void {
            unset($this->data[$offset]);
        }
        
        # -- JsonSerializable method stubs
        
        /**
         * Serializes the internal data to JSON.
         *
         * @return false|string Returns a JSON representation of the internal data.
         *                     If the serialization fails, false is returned.
         *
         * @deprecated This method is marked as deprecated and may be removed in a future version.
         */
        #[ReturnTypeWillChange] public function jsonSerialize(): false|string {
            return json_encode($this->data);
        }
        
        # -- Countable method stubs
        
        /**
         * Gets the number of elements in the data.
         *
         * @return int The number of elements in the data.
         */
        public function count(): int {
            return count($this->data);
        }
        
    }
