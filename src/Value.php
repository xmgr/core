<?php
    
    namespace Xmgr;
    
    use Nothing;
    use stdClass;
    
    /**
     * Class Value
     *
     * Represents a value with various utility methods for checking and updating the value.
     */
    class Value {
        
        protected mixed $value;
        
        /**
         * Constructs a new instance of the class.
         *
         * @param mixed|null $value The initial value to assign to the object. Optional, defaults to null.
         *
         * @return void
         */
        public function __construct(mixed $value = null) {
            if (func_num_args()) {
                $this->set($value);
            } else {
                $this->set(new Nothing());
            }
        }
        
        /**
         * Creates a new instance of the class using the provided value.
         *
         * @param mixed|null $value The initial value to assign to the object. Optional, defaults to null.
         *
         * @return self The newly created instance of the class.
         */
        public static function make(mixed $value = null): static {
            return (func_num_args() ? new static($value) : new static());
        }
        
        /**
         * Retrieves the value of the property.
         *
         * @return mixed The value of the property.
         */
        public function get(): mixed {
            return $this->value;
        }
        
        /**
         * Sets the value of the property.
         *
         * @param mixed $value The value to be set.
         *
         * @return $this
         */
        public function set(mixed $value): static {
            $this->value = $value;
            
            return $this;
        }
        
        # -- IS ...
        
        /**
         * Checks if the property value is equal to any of the provided values.
         *
         * @param mixed ...$values The values to check against the property value.
         *
         * @return bool True if the property value is equal to any of the provided values, otherwise false.
         */
        public function is(...$values): bool {
            if (in_array($this->value, $values, true)) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Checks if the value of this object is in the given array.
         *
         * @param array $array  The array to check against.
         * @param bool  $strict [optional] Indicates whether strict comparison (===) should be used.
         *                      Default is true.
         *
         * @return bool Returns true if the value is found in the array, false otherwise.
         */
        public function isIn(array $array, bool $strict = true): bool {
            return in_array($this->value, $array, $strict);
        }
        
        /**
         * Checks if the value of the property is an instance of the "Nothing" class.
         *
         * @return bool True if the value is an instance of "Nothing", false otherwise.
         */
        public function isNothing(): bool {
            return $this->value instanceof Nothing;
        }
        
        /**
         * Checks if the value of the property is null.
         *
         * @return bool Returns true if the value of the property is null, false otherwise.
         */
        public function isNull(): bool {
            return $this->value === null;
        }
        
        /**
         * Checks if the value of the property is an integer.
         *
         * @return bool True if the value is an integer, false otherwise.
         */
        public function isInt(): bool {
            return is_int($this->value);
        }
        
        /**
         * Determines whether the value of the property is an unsigned integer.
         *
         * @return bool True if the value is an unsigned integer, false otherwise.
         */
        public function isUInt(): bool {
            return is_int($this->value) && $this->value >= 0;
        }
        
        /**
         * Checks if the value is a string.
         *
         * @return bool Returns true if the value is a string, false otherwise.
         */
        public function isString(): bool {
            return is_string($this->value);
        }
        
        /**
         * Checks if the value of the property is a float.
         *
         * @return bool Returns true if the value is a float, false otherwise.
         */
        public function isFloat(): bool {
            return is_float($this->value);
        }
        
        /**
         * Checks if the value of the property is a boolean.
         *
         * @return bool true if the value is a boolean, false otherwise.
         */
        public function isBool(): bool {
            return is_bool($this->value);
        }
        
        /**
         * Checks if the value is an array.
         *
         * @return bool Returns true if the value is an array, false otherwise.
         */
        public function isArray(): bool {
            return is_array($this->value);
        }
        
        /**
         * Checks if the value is a number.
         *
         * @return bool Returns true if the value is a number, false otherwise.
         */
        public function isNumber(): bool {
            return $this->isInt() || $this->isFloat();
        }
        
        /**
         * Checks if the value of the property is numeric.
         *
         * @return bool Returns true if the value is numeric, otherwise false.
         */
        public function isNumeric(): bool {
            return is_numeric($this->value);
        }
        
        /**
         * Checks if the value of the property is scalar.
         *
         * @return bool True if the value is scalar, false otherwise.
         */
        public function isScalar(): bool {
            return is_scalar($this->value);
        }
        
        /**
         * Checks if the value of the property is a resource.
         *
         * @return bool Returns true if the value is a resource, false otherwise.
         */
        public function isResource(): bool {
            return is_resource($this->value);
        }
        
        # -- If ... set
        
        /**
         * Updates the value of the property if it is an object.
         *
         * @param mixed $set The new value to set if the property is an object.
         *
         * @return $this The current instance of the object.
         */
        public function ifObject(mixed $set): static {
            if (is_object($this->value)) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Sets the value of the property to the given value if it is null.
         *
         * @param mixed $set The value to set if the property is null.
         *
         * @return static The updated object instance.
         */
        public function ifNull(mixed $set): static {
            if ($this->isNull()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Checks if the property has no value, and if so, sets a new value.
         *
         * @param mixed $set The value to set if the property is empty.
         *
         * @return static This method returns an instance of the class to allow method chaining.
         */
        public function ifNothing($set): static {
            if ($this->isNothing()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Sets the property with the provided value if it is null or nothing.
         *
         * @param mixed $set The value to set if the property is null or nothing.
         *
         * @return static Returns the current object instance.
         */
        public function ifNullOrNothing($set): static {
            if ($this->isNothing() || $this->isNull()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Checks if the current object holds a scalar value and sets a new value if so.
         *
         * @param mixed $set The value to set if the current object holds a scalar value.
         *
         * @return $this Returns the current object.
         */
        public function ifScalar(mixed $set): static {
            if ($this->isScalar()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Checks if the value of the object is an array and sets the value if it is.
         *
         * @param mixed $set The value to be set if the current value is an array.
         *
         * @return $this An instance of the current object.
         */
        public function ifArray(mixed $set): static {
            if ($this->isArray()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Checks if the value of the object is not a scalar and sets the value if it is not.
         *
         * @param mixed $set The value to be set if the current value is not a scalar.
         *
         * @return $this An instance of the current object.
         */
        public function ifNotScalar(mixed $set): static {
            if (!$this->isScalar()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        /**
         * Checks if the value of the object is a resource and sets the value if it is.
         *
         * @param mixed $set The value to be set if the current value is a resource.
         *
         * @return $this An instance of the current object.
         */
        public function ifResource(mixed $set): static {
            if ($this->isResource()) {
                $this->set($set);
            }
            
            return $this;
        }
        
        # Convert / Cast
        
        /**
         * Converts the value of the object to a number.
         *
         * If the current value is not a scalar, it is set to 0.
         * Then the value is typecasted to a number.
         *
         * @return $this An instance of the current object.
         */
        public function toNumber(): static {
            $this->ifNotScalar(0);
            $this->set($this->value + 0);
            
            return $this;
        }
        
        /**
         * Converts the value of the object to an integer.
         *
         * This method checks if the current value is not a scalar and assigns 0 to the value
         * if it is not. Then, it casts the value to an integer using the (int) casting operator.
         *
         * @return $this An instance of the current object.
         */
        public function toInt(): static {
            $this->ifNotScalar(0);
            $this->set((int)$this->value);
            
            return $this;
        }
        
        /**
         * Converts the value of the object to an unsigned integer.
         *
         * @return $this An instance of the current object.
         */
        public function toUInt(): static {
            $this->toInt();
            $this->set(abs((int)$this->value));
            
            return $this;
        }
        
        /**
         * Converts the value to a float.
         *
         * This method converts the current value to a float data type.
         * If the current value is not a scalar value, it will be set to 0.00.
         *
         * @return $this Returns the instance of the object.
         */
        public function toFloat(): static {
            $this->ifNotScalar(0.00);
            $this->set((float)$this->value);
            
            return $this;
        }
        
        /**
         * Converts the value to a string.
         *
         * This method converts the current value to a string data type.
         * If the current value is not a scalar value, it will be set to an empty string ('').
         *
         * @return $this Returns the instance of the object.
         */
        public function toString(): static {
            $this->ifNotScalar('');
            $this->set((string)$this->value);
            
            return $this;
        }
        
        /**
         * Trims whitespace from the value.
         *
         * This method trims leading and trailing whitespace from the current value.
         * It ensures that any blank spaces at the beginning or end of the value are removed.
         *
         * @return $this Returns the instance of the object.
         */
        public function trim(): static {
            $this->toString();
            $this->set(trim($this->value));
            
            return $this;
        }
        
        /**
         * Converts the value to an array.
         *
         * This method converts the current value to an array data type. If the current
         * value is not already an array, it will be converted using the arrval()
         * function. If the value needs to be recursively converted, you can pass
         * true to the $recursively parameter. In this case, the value is first
         * converted to a JSON string using json_encode() and then decoded into an array using json_decode().
         *
         * @param bool $recursively Optional. Whether to recursively convert the value to an array. Default is false.
         *
         * @return $this Returns the instance of the object.
         */
        public function toArray(bool $recursively = false): static {
            $this->set((array)$this->value);
            if ($recursively) {
                $this->set(arrval($this->value));
            }
            
            return $this;
        }
        
        /**
         * Converts the value to an object.
         *
         * This method converts the current value to an object data type.
         * If the current value is a scalar value, it will be converted to an empty stdClass object.
         * If the current value is null or undefined, it will be converted to an empty stdClass object.
         * If the current value is a resource, it will be converted to an empty stdClass object.
         * If the current value is an array, it will be converted to an object using type casting.
         *
         * @return $this Returns the instance of the object.
         */
        public function toObject(): static {
            $this->ifScalar(new stdClass());
            $this->ifNullOrNothing(new stdClass());
            $this->ifResource(new stdClass());
            $this->ifArray((object)$this->value);
            
            return $this;
        }
        
    }
