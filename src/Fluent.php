<?php
    
    namespace Xmgr;
    
    use Xmgr\Collections\Collection;
    use Xmgr\Interfaces\Arrayable;
    use Xmgr\Interfaces\Collectable;
    use Xmgr\Interfaces\Jsonable;
    
    /**
     * Class Fluent
     *
     * Represents an object for handling and manipulating attributes in a fluent manner.
     *
     * @implements \ArrayAccess
     * @implements Arrayable
     * @implements Jsonable
     * @implements \JsonSerializable
     * @implements Collectable
     */
    class Fluent implements \ArrayAccess, Arrayable, Jsonable, \JsonSerializable, Collectable {
        
        protected array $attributes = [];
        
        /**
         * __construct method.
         *
         * This method initializes a new instance of the class.
         *
         * @param array|object $attributes (optional) The attributes to assign to the object. Defaults to an empty
         *                                 array.
         *
         * @return void
         */
        public function __construct(array|object $attributes = []) {
            $this->attributes = (array)$attributes;
        }
        
        /**
         * Get the value of a given key from the attributes array.
         *
         * @param string     $key     The key to retrieve the value for.
         * @param mixed|null $default The default value to return if the key does not exist.
         *
         * @return mixed The value of the given key from the attributes array, or the default value if the key does not
         *               exist.
         */
        public function get(mixed $key, mixed $default = null): mixed {
            return data_get($this->attributes, $key, $default);
        }
        
        /**
         * __call method.
         *
         * This method allows dynamic method calls on the object.
         * It assigns the argument(s) to the object's attributes under the specified method name.
         *
         * @param string $method The name of the method being called.
         * @param array  $args   The arguments passed to the method.
         *
         * @return $this The current object instance.
         */
        public function __call(string $method, ...$args) {
            $this->attributes[$method] = count($args) > 0 ? reset($args) : true;
            
            return $this;
        }
        
        /**
         * Create a new Collection instance.
         *
         * @param string|null $key The key of the value to be collected. If null, the entire collection is returned.
         *
         * @return Collection A new Collection instance that contains the collected value(s) or the entire collection.
         */
        public function collect(string $key = null): Collection {
            return new Collection($this->get($key));
        }
        
        /**
         * Retrieves the value associated with the given key from the attributes array. If the key does not exist, the
         * $default value is returned instead.
         *
         * @param mixed      $key     The key to retrieve the value for.
         * @param mixed|null $default The default value to return if the key does not exist in the attributes array.
         *                            Default is null.
         *
         * @return mixed The value associated with the given key if it exists in the attributes array, else the
         *               $default value.
         */
        public function value(mixed $key, mixed $default = null): mixed {
            if (array_key_exists($key, $this->attributes)) {
                return $this->attributes[$key];
            }
            
            return value($default);
        }
        
        /**
         * Checks if a specified offset exists in the attributes array.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool Returns true if the offset exists, false otherwise.
         */
        public function offsetExists(mixed $offset): bool {
            return isset($this->attributes[$offset]);
        }
        
        /**
         * Retrieves the value at the specified offset.
         *
         * @param mixed $offset The offset of the value to retrieve.
         *
         * @return mixed The value at the specified offset.
         */
        public function offsetGet(mixed $offset): mixed {
            return $this->value($offset);
        }
        
        /**
         * Sets the value at the specified offset.
         *
         * @param mixed $offset The offset to set the value at.
         * @param mixed $value  The value to set at the specified offset.
         *
         * @return void
         */
        public function offsetSet(mixed $offset, mixed $value): void {
            $this->attributes[$offset] = $value;
        }
        
        /**
         * Unsets the value at the specified offset.
         *
         * @param mixed $offset The offset of the value to unset.
         *
         * @return void
         */
        public function offsetUnset(mixed $offset): void {
            unset($this->attributes[$offset]);
        }
        
        /**
         * toJson method.
         *
         * This method converts the object to a JSON string.
         *
         * @param int $flags (optional) The JSON encode flags. Defaults to 0.
         *
         * @return string The JSON representation of the object.
         */
        public function toJson(int $flags = 0): string {
            return json_encode($this->jsonSerialize(), $flags);
        }
        
        /**
         * Converts the object to a JSON serializable value.
         *
         * @return array The JSON serializable value representing the object.
         */
        public function jsonSerialize(): array {
            return $this->toArray();
        }
        
        /**
         * toArray method.
         *
         * This method converts the object into an array representation.
         *
         * @return array The object converted into an array.
         */
        public function toArray(): array {
            return $this->attributes;
        }
        
        /**
         * Checks if a specific property or key exists.
         *
         * @param string $key The name of the property or key to check.
         *
         * @return bool True if the property or key exists, false otherwise.
         */
        public function __isset(mixed $key) {
            return $this->offsetExists($key);
        }
        
        /**
         * Unsets a specific key in the object.
         *
         * @param string $key The key to unset.
         *
         * @return void
         */
        public function __unset(mixed $key) {
            $this->offsetUnset($key);
        }
        
    }
