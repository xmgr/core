<?php
    
    namespace Xmgr;
    
    /**
     * Class Expectation
     *
     * The Expectation class is used to perform various checks and assertions on a given value.
     */
    class Expectation {
        
        protected mixed  $value = null;
        protected string $label = '';
        
        protected array $checks = [];
        
        /**
         * Constructs a new instance of the class.
         *
         * @param mixed  $value The value to be assigned to the object's property.
         * @param string $label A readable label
         *
         * @return $this
         */
        public function __construct(mixed $value, string $label = '') {
            $this->value = $value;
            $this->label = $label;
            
            return $this;
        }
        
        /**
         * Ruft den Wert auf falls er callable ist
         *
         * @param bool $safe_mode Falls die Ausführung des callable eine Exception wirft, kann diese mit safe_mode
         *                        unterdrückt werden.
         *
         * @return $this
         * @throws \Exception
         */
        public function resolve(bool $safe_mode = false): self {
            if (is_callable($this->value)) {
                try {
                    $this->value = value($this->value);
                } catch (\Exception $e) {
                    if ($safe_mode) {
                        $this->value = null;
                    } else {
                        throw $e;
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Resets the value and label of the object.
         *
         * @param mixed       $value The new value to be set.
         * @param string|null $label The new label to be set. Default is null.
         *
         * @return self The modified object.
         */
        public function reset(mixed $value, ?string $label = null): self {
            $this->value = $value;
            if (is_string($label)) {
                $this->label = $label;
            }
            
            return $this;
        }
        
        /**
         * Retrieves the value stored in the object.
         *
         * @return mixed The value stored in the object.
         */
        public function value(): mixed {
            return $this->value;
        }
        
        /**
         * Returns the label associated with the current object.
         *
         * @return string The label of the current object.
         */
        public function label(): string {
            return $this->label;
        }
        
        /**
         * Retrieves the result of the checks.
         *
         * @return array The array containing the result of the checks.
         */
        public function result(): array {
            return $this->checks;
        }
        
        /**
         * Adds a check result to the list of checks.
         *
         * @param bool   $result The check result.
         * @param string $text_true
         * @param string $text_false
         * @param mixed  $compare
         *
         * @return $this
         */
        protected function add(bool $result, string $text_true = '', string $text_false = '', mixed $compare = null): self {
            $this->checks[] = [
                'result'     => $result,
                'text_true'  => $text_true,
                'text_false' => $text_false,
                'compare'    => $compare,
            ];
            
            return $this;
        }
        
        /**
         * Checks if the value of this object matches any of the given values.
         *
         * @param mixed ...$values The values to compare against.
         *
         * @return $this
         */
        public function toBeAnyOf(...$values): self {
            $result = $this->isAnyOf(...$values);
            
            return $this->add($result, 'in any of', 'is not any of', $values);
        }
        
        /**
         * Checks if the given value is equal to the object's property value.
         *
         * @param mixed $value The value to be compared with the object's property value.
         *
         * @return $this
         */
        public function toBe(mixed $value): self {
            return $this->add($this->is($value), 'is the same as', 'is not the same as', $value);
        }
        
        /**
         * Checks if executing the value throws an exception
         *
         * @return bool
         */
        public function toFail() {
            if ($this->isCallable()) {
                try {
                    $this->resolve();
                } catch (\Exception $e) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Determines if the current value is a boolean.
         *
         * @return $this
         */
        public function toBeBoolean(): self {
            return $this->add($this->isBool(), 'is boolean', 'is not boolean');
        }
        
        /**
         * Checks if the value of the object is falsy.
         *
         * @return $this
         */
        public function toBeFalsy(): self {
            return $this->add($this->isFalsy(), 'is falsy', 'is not falsy');
        }
        
        /**
         * Checks if the value stored in the object is false.
         *
         * @return $this
         */
        public function toBeFalse(): self {
            return $this->add($this->is(false), 'is false', 'is not false');
        }
        
        /**
         * Checks if the value of this object is truthy.
         *
         * @return $this
         */
        public function toBeTruthy(): self {
            return $this->add($this->isTruthy(), 'is truthy', 'is not truthy');
        }
        
        /**
         * Checks if the value of this object is true.
         *
         * @return $this
         */
        public function toBeTrue(): self {
            return $this->add($this->is(true), 'is true', 'is not true');
        }
        
        /**
         * Checks if the value of this object is in the given array.
         *
         * @param array $values An array of values to check against.
         * @param bool  $strict (optional) Whether to perform strict comparison. Defaults to true.
         *
         * @return $this
         */
        public function toBeIn(array $values, bool $strict = true): self {
            return $this->add(in_array($this->value(), $values, $strict), 'is in array', 'is not in array', $values);
        }
        
        /**
         * Checks if the value of this object is null.
         *
         * @return $this
         */
        public function toBeNull(): self {
            return $this->add($this->is(null), 'is null', 'is not null');
        }
        
        /**
         * Checks if the value of this object is a number.
         *
         * @return $this
         */
        public function toBeNumber(): self {
            return $this->add($this->isNumber(), 'is a number', 'is not a number');
        }
        
        /**
         * Checks if the value of this object is an integer.
         *
         * @return $this
         */
        public function toBeInteger(): self {
            return $this->add(is_int($this->value()), 'is an integer', 'is not an integer');
        }
        
        /**
         * Checks if the value of this object is a float.
         *
         * @return $this
         */
        public function toBeFloat(): self {
            return $this->add(is_float($this->value()), 'is a float', 'is not a float');
        }
        
        /**
         * Checks if the value of this object is a string.
         *
         * @return $this
         */
        public function toBeString(): self {
            return $this->add(is_string($this->value()), 'is a string', 'is not a string');
        }
        
        /**
         * Checks if the value of this object is an array.
         *
         * @return $this
         */
        public function toBeArray(): self {
            return $this->add($this->isArray(), 'is an array', 'is not an array');
        }
        
        /**
         * Checks if the value of this object is an object.
         *
         * @return $this
         */
        public function toBeObject(): self {
            return $this->add(is_object($this->value()), 'is an object', 'is not an object');
        }
        
        /**
         * Checks if the value of this object is greater than the given value.
         *
         * @param int|float $value The value to compare against.
         *
         * @return $this
         */
        public function toBeGreaterThan(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() > $value, 'is greater than', 'is not greater than', $value);
        }
        
        /**
         * Checks if the value of this object is greater than or equal to the specified value.
         *
         * @param int|float $value The value to compare with.
         *
         * @return $this
         */
        public function toBeGreaterThanOrEqual(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() >= $value, 'is greater than or equal', 'is not greater than or equal', $value);
        }
        
        /**
         * Checks if the value of this object is less than the specified value.
         *
         * @param int|float $value The value to compare against.
         *
         * @return $this
         */
        public function toBeLessThan(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() < $value, 'is less than', 'is not less than', $value);
        }
        
        /**
         * Checks if the value of this object is less than or equal to the specified value.
         *
         * @param int|float $value The value to compare against.
         *
         * @return $this
         */
        public function toBeLessThanOrEqual(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() <= $value, 'is less than or equal', 'is not less than or equal', $value);
        }
        
        /**
         * Checks if the value of this object is greater or less than the given value.
         *
         * @param int|float $value The value to compare against.
         *
         * @return $this
         */
        public function toBeGreaterOrLessThan(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() <> $value, 'is greater or less than', 'is not greater or less than', $value);
        }
        
        /**
         * Checks if the given value is positive.
         *
         * @param int|float $value The value to be checked.
         *
         * @return $this
         */
        public function toBePositive(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() > 0, 'is positive', 'is not positive', $value);
        }
        
        /**
         * Checks if the given value is negative.
         *
         * @param int|float $value The value to check.
         *
         * @return $this
         */
        public function toBeNegative(int|float $value): self {
            return $this->add($this->isNumber() && $this->value() < 0, 'is negative', 'is not negative', $value);
        }
        
        /**
         * Checks if the value of this object is callable.
         *
         * @return $this
         */
        public function toBeCallable(): self {
            return $this->add(is_callable($this->value()), 'is callable', 'is not a callable');
        }
        
        /**
         * Checks if the value of this object is iterable.
         *
         * @return $this
         */
        public function toBeIterable(): self {
            return $this->add(is_iterable($this->value()), 'is iterable', 'is not iterable');
        }
        
        /**
         * Checks if the value of this object is an array with only one item.
         *
         * @return $this
         */
        public function toHaveOnlyOneItem(): self {
            return $this->add($this->isArray() && count($this->value()) === 1, 'has only one item', 'does not have only one item');
        }
        
        /**
         * Checks if the given value has the specified item count.
         *
         * @param int $count The item count to be checked.
         *
         * @return $this
         */
        public function toHaveItemCount(int $count): self {
            return $this->add($this->isArray() && count($this->value()) === $count, 'has item count', 'does not have item count', $count);
        }
        
        /**
         * Checks if the value is instance of the specified class
         *
         * @param string $class
         *
         * @return bool
         */
        public function toBeInstanceOf(string $class) {
            return $this->value() instanceof $class;
        }
        
        /**
         * Checks if the value is an instance of a model.
         *
         * @return $this
         */
        public function toBeRecord(): self {
            return $this->add($this->value() instanceof Record, 'is a record', 'is not a record');
        }
        
        /**
         * Checks if all values in the array are identical.
         *
         * @return $this
         */
        public function arrayValuesToBeIdentical(): self {
            return $this->add($this->isArray() && ($this->value() === array_unique($this->value())), 'has identical array values', 'does not have identical array values');
        }
        
        /**
         * Checks if the current value contains any digits.
         *
         * @return $this
         */
        public function toContainDigits(): self {
            return $this->add($this->isString() && Str::containsAny($this->value(), range(0, 9)), 'contains digits', 'does not contain digits');
        }
        
        /**
         * Checks if the value of this object contains the specified string.
         *
         * @param string $string The string to check for.
         *
         * @return $this
         */
        public function toContainString(string $string): self {
            return $this->add($this->isString() && str_contains($this->value(), $string), 'does contain string', 'does not contain string', $string);
        }
        
        /**
         * Checks if the value of this object is contained within the given string.
         *
         * @param string $string The string to check against.
         *
         * @return $this
         */
        public function toBeContainedIn(string $string): self {
            return $this->add($this->isString() && str_contains($string, $this->value()), 'is contained in', 'is not contained in', $string);
        }
        
        /**
         * Checks if the value consists only of capital letters.
         *
         * @return $this
         */
        public function toConsistOnlyOfCapitalLetters(): self {
            return $this->add(ctype_upper($this->value()), 'consists of only capital letters', 'does not consist of only capital letters');
        }
        
        /**
         * Checks if the current value consists only of lowercase letters.
         *
         * @return $this
         */
        public function toConsistOnlyOfLowercaseLetters(): self {
            return $this->add(ctype_lower($this->value()));
        }
        
        /**
         * Checks if the given value matches the provided string.
         *
         * @param string $string         The string to be compared with the value.
         * @param bool   $case_sensitive (optional) Whether the comparison is case sensitive or not. Defaults to false.
         *
         * @return $this
         */
        public function toMatchString(string $string, bool $case_sensitive = false): self {
            return $this->add($this->isString() && ($case_sensitive ? ($this->value() === $string) : (mb_strtolower($this->value()) === mb_strtolower($string))), 'matches string', 'does not match string', $string);
        }
        
        /**
         * Checks if the value of this object is zero.
         *
         * @return $this
         */
        public function toBeZero(): self {
            return $this->add($this->isAnyOf(0, '0'), 'is zero', 'is not zero');
        }
        
        /**
         * Checks if the value of this object is a digit.
         *
         * @param string|int $digit The digit to check.
         *
         * @return $this
         */
        public function toBeDigit(string|int $digit): self {
            return $this->add($this->isAnyOf((string)$digit, (int)$digit), 'is digit ', 'is not digit', $digit);
        }
        
        /**
         * Checks if the value of this object is present in the given array.
         *
         * @param array $array  The array to search in.
         * @param bool  $strict (optional) Whether strict comparison should be used. Default is true.
         *
         * @return $this
         */
        public function toBeInArray(array $array, bool $strict = true): self {
            return $this->add(in_array($this->value(), $array, $strict), 'is in array', 'is not in array', $array);
        }
        
        /**
         * Checks if the value of this object is a key in the given array.
         *
         * @param array $array The array to check against.
         *
         * @return $this
         */
        public function toBeArrayKeyIn(array $array): self {
            return $this->add(array_key_exists($this->value(), $array), 'is array key in', 'is not array key in', $array);
        }
        
        # ---------------------------------------------------
        
        /**
         * Checks if the given value is equal to the internal value.
         *
         * @param mixed $value The value to compare against the internal value.
         *
         * @return bool Returns true if the given value is equal to the internal value, false otherwise.
         */
        protected function is(mixed $value): bool {
            return $this->value() === $value;
        }
        
        /**
         * Checks if the value is callable
         *
         * @return bool
         */
        protected function isCallable(): bool {
            return is_callable($this->value());
        }
        
        /**
         * Checks if the value is a closure
         *
         * @return bool
         */
        protected function isCLosure(): bool {
            return $this->value() instanceof \Closure;
        }
        
        /**
         * Checks if the value is a string.
         *
         * @return bool Returns true if the value is a string, false otherwise.
         */
        protected function isString(): bool {
            return is_string($this->value());
        }
        
        /**
         * Checks if the current value is a number.
         *
         * @return bool Returns true if the current value is a number, false otherwise.
         */
        protected function isNumber(): bool {
            return (is_int($this->value()) || is_float($this->value()));
        }
        
        /**
         * Checks if the value stored in this object is an array.
         *
         * @return bool
         */
        protected function isArray(): bool {
            return is_array($this->value());
        }
        
        /**
         * Checks if the current instance is falsy.
         *
         * @return bool Returns true if the current instance is falsy, false otherwise.
         */
        protected function isFalsy(): bool {
            return (bool)$this->value() === false;
        }
        
        /**
         * Checks if the value is truthy.
         *
         * @return bool Returns true if the value is truthy, false otherwise.
         */
        protected function isTruthy(): bool {
            return (bool)$this->value() === true;
        }
        
        /**
         * Checks if the value is a boolean type and optionally matches a specific boolean value.
         *
         * @param bool|null $and The boolean value to compare with the value. Defaults to null, which means no
         *                       comparison is done.
         *
         * @return bool Returns true if the value is a boolean and matches the provided value (if any), otherwise
         *              returns false.
         */
        protected function isBool(?bool $and = null): bool {
            return is_bool($this->value()) && (!is_bool($and) || $this->value() === $and);
        }
        
        /**
         * Checks if the value of this object matches any of the given values.
         *
         * @param mixed ...$values The values to check against.
         *
         * @return bool True if the value matches any of the given values, false otherwise.
         */
        protected function isAnyOf(...$values): bool {
            if (in_array($this->value(), $values, true)) {
                return true;
            }
            
            return false;
        }
        
        # --
        
        /**
         * This method is used for testing purposes.
         *
         * @return void
         */
        public static function test() {
        
        }
        
    }
