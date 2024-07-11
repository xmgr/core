<?php
    
    namespace Xmgr;
    
    /**
     * Represents a Validator object for validating values.
     */
    class Validator {
        
        protected mixed $value = null;
        
        /**
         * @param $value
         */
        public function __construct($value) {
            $this->value = $value;
        }
        
        /**
         * Creates a new instance of the class.
         *
         * @param mixed $value The value to be passed to the constructor.
         *
         * @return self The new instance of the class.
         */
        public static function create(mixed $value): self {
            return new self($value);
        }
        
        /**
         * Converts the current value to an unsigned integer.
         *
         * Modifies the current object's value by converting it to an integer, taking its absolute value,
         * and returning a reference to the modified object.
         *
         * @return $this
         */
        public function toUInt(): static {
            $this->toInt();
            $this->value = abs($this->value);
            
            return $this;
        }
        
        /**
         * Converts the value to an integer.
         *
         * @return $this
         */
        public function toInt(): static {
            $this->value = (int)$this->value;
            
            return $this;
        }
        
        /**
         * Converts the current value to a float.
         *
         * @return self Returns an instance of the current object after converting the value to a float.
         */
        public function toFloat(): static {
            $this->value = (float)$this->value;
            
            return $this;
        }
        
        /**
         * Converts the current value to a numeric type.
         *
         * @return $this
         */
        public function numeric(): static {
            $this->value = $this->value + 0;
            
            return $this;
        }
        
        /**
         * Updates the value to be within the specified minimum and maximum limits.
         *
         * @param mixed|null $min The minimum limit. If set to null, the minimum limit will not be applied.
         * @param mixed|null $max The maximum limit. If set to null, the maximum limit will not be applied.
         *
         * @return $this Returns the object instance to allow method chaining.
         */
        public function minmax(mixed $min = null, mixed $max = null) {
            $this->value = minmax($this->value, $min, $max);
            
            return $this;
        }
        
    }
