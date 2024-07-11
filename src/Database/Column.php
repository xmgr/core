<?php
    
    namespace Xmgr\Database;
    
    use Exception;
    
    /**
     * Class Column
     * Represents a column in a database query.
     */
    class Column {
        
        protected string $connect = 'AND';
        protected string $name    = '';
        
        protected array $conditions = [];
        
        protected array $functions = [];
        
        
        /**
         * Constructor for the class.
         *
         * @param string $name    The name of the object.
         * @param string $connect Optional. The connection type to be used. Defaults to 'AND'.
         *
         * @return void
         */
        public function __construct(string $name, string $connect = 'AND') {
            $this->name = $name;
            $this->setConnect($connect);
        }
        
        /**
         * Set the connect value.
         *
         * @param string $value The connect value to be set. Only accepts "AND" or "OR" (case-insensitive).
         *
         * @return void
         */
        protected function setConnect(string $value): void {
            $value         = trim(strtoupper($value));
            $this->connect = (in_array($value, ['AND', 'OR']) ? $value : $this->connect);
        }
        
        /**
         * Generates the alias for the given string.
         *
         * @param string $alias The alias to be applied to the string.
         *
         * @return string The modified string with the generated alias applied.
         */
        public function as(string $alias): string {
            return dbkey($this->name) . ' AS ' . ltrim(trim(str_keep($alias, '_')), '0123456789');
        }
        
        /**
         * Set the logical operator to "OR".
         *
         * @return $this Return the current instance of the object.
         */
        public function or(): static {
            $this->setConnect('OR');
            
            return $this;
        }
        
        /**
         * Add a condition to the query.
         *
         * @param string      $operator The operator used in the condition.
         * @param mixed       $value    The value to compare against.
         * @param string|null $operator2
         * @param string|null $value2
         *
         * @return void
         */
        public function add(string $operator, mixed $value, ?string $operator2 = null, ?string $value2 = null): void {
            $sqlValue     = dbvalue($value);
            $sqlValue2    = ($value2 === null ? null : dbvalue($value2));
            $columnString = $this->name();
            foreach ($this->functions as $function) {
                $columnString = strtoupper($function) . '(' . $columnString . ')';
            }
            $this->conditions[] = $columnString . ' ' . $operator . ' ' . $sqlValue . ($operator2 !== null && $value2 !== null ? ' ' . $operator2 . ' ' . $sqlValue2 : '');
        }
        
        /**
         * Returns a safe database key for the current name.
         *
         * @return string The safe database key.
         */
        public function name(): string {
            return dbkey($this->name);
        }
        
        /**
         * Add functions to the $functions array property.
         *
         * @param array $functions The array of functions to add.
         *
         * @return $this The current instance of the class.
         */
        public function addFunctions(array $functions): static {
            foreach ($functions as $function) {
                $this->functions[] = $function;
            }
            
            return $this;
        }
        
        /**
         * Add an IS condition to the query with a null value.
         *
         * @return $this The current instance of the class.
         * @throws Exception
         */
        public function isNull(): static {
            $this->is(null);
            
            return $this;
        }
        
        /**
         * Add an "IS NOT" condition to the query.
         *
         * @return $this The current instance for method chaining.
         */
        public function isNotNull(): static {
            $this->isNot(null);
            
            return $this;
        }
        
        /**
         * Add an equality condition to the query.
         *
         * @param mixed $value The value to compare with.
         *
         * @return $this The instance of the class to allow method chaining.
         */
        public function equals(mixed $value): static {
            $this->add('=', $value);
            
            return $this;
        }
        
        /**
         * Adds a value to the list of unequal comparisons.
         *
         * @param mixed $value The value to compare against.
         *
         * @return static The current instance of the object.
         */
        public function unequal(mixed $value): static {
            $this->add('<>', $value);
            
            return $this;
        }
        
        /**
         * Add a greater than condition to the query.
         *
         * @param mixed $value The value to compare.
         *
         * @return $this The current instance of the class.
         * @throws Exception
         */
        public function greaterThan(mixed $value): static {
            $this->add('>', $value);
            
            return $this;
        }
        
        /**
         * Add a greater than or equals condition to the query.
         *
         * @param mixed $value The value to compare against.
         *
         * @return $this
         * @throws Exception
         */
        public function greaterThanOrEquals(mixed $value): static {
            $this->add('>=', $value);
            
            return $this;
        }
        
        /**
         * Add a less than condition to the query.
         *
         * @param mixed $value The value to compare against.
         *
         * @return $this
         * @throws Exception
         */
        public function lessThan(mixed $value): static {
            $this->add('<', $value);
            
            return $this;
        }
        
        /**
         * Add a less than or equals condition to the query.
         *
         * @param mixed $value The value to compare against.
         *
         * @return $this The current instance of the object.
         * @throws Exception
         */
        public function lessThanOrEquals(mixed $value): static {
            $this->add('<=', $value);
            
            return $this;
        }
        
        /**
         * Add a condition to the object using the "=" operator.
         *
         * @param mixed $value The value to be compared with.
         *
         * @return $this The updated instance of the object.
         * @throws Exception
         */
        public function is(mixed $value): static {
            $this->add('IS', $value);
            
            return $this;
        }
        
        /**
         * Add an "!=" condition to the query.
         *
         * @param mixed $value The value to compare with.
         *
         * @return static The updated query object.
         * @throws Exception
         */
        public function not(mixed $value): static {
            $this->add('!=', $value);
            
            return $this;
        }
        
        /**
         * Adds a not-equals condition to the query.
         *
         * @
         *
         * /**
         * Add an "IS NOT" condition to the query.
         *
         * @param mixed $value The value to compare with.
         *
         * @return static The updated query object.
         */
        public function isNot(mixed $value): static {
            $this->add('IS NOT', $value);
            
            return $this;
        }
        
        /**
         * Adds a not-equals condition to the query.
         *
         * @param mixed $value The value to compare against.
         *
         * @return $this The current object instance.
         */
        public function notEquals(mixed $value): static {
            $this->add('IS BETWEEN', $value);
            
            return $this;
        }
        
        /**
         * Adds an "IS BETWEEN" condition to the query.
         *
         * @param int|float $min The minimum value for the condition.
         * @param int|float $max The maximum value for the condition.
         *
         * @return self Returns the updated instance of the object.
         */
        public function isBetween(int|float $min, int|float $max): static {
            $this->add('IS BETWEEN', min($min, $max), max($min, $max));
            
            return $this;
        }
        
        /**
         * Adds an "IS NOT BETWEEN" condition to the query.
         *
         * @param int|float $min The lower bound value of the range.
         * @param int|float $max The upper bound value of the range.
         *
         * @return static The current instance of the class with the "IS NOT BETWEEN" condition added.
         */
        public function isNotBetween(int|float $min, int|float $max): static {
            $this->add('IS NOT BETWEEN', min($min, $max), max($min, $max));
            
            return $this;
        }
        
        /**
         * Add a condition to check if the value is true.
         *
         * @return $this The updated object instance.
         * @throws Exception
         */
        public function isTrue(): static {
            $this->add('=', 1);
            
            return $this;
        }
        
        /**
         * Check if the object's value is considered false.
         *
         * @return $this Returns true if the object's value is considered false, false otherwise.
         * @throws Exception
         */
        public function isFalse(): static {
            return $this->isZero();
        }
        
        /**
         * Check if value is zero
         *
         * @return $this
         * @throws Exception
         */
        public function isZero(): static {
            $this->add('=', 0);
            
            return $this;
        }
        
        /**
         * Add an IN condition to the object.
         *
         * @param array $values The array of values to be used in the IN condition.
         *
         * @return $this The modified object.
         * @throws Exception
         */
        public function in(array $values): static {
            $this->add('IN', $values);
            
            return $this;
        }
        
        /**
         * Add "NOT IN" condition to the current query builder instance.
         *
         * @param array $values The values to be used in the "NOT IN" condition.
         *
         * @return static The current query builder instance.
         * @throws Exception
         */
        public function notIn(array $values): static {
            $this->add('NOT IN', $values);
            
            return $this;
        }
        
        /**
         * Get the string representation of the object.
         *
         * @return string The string representation of the object.
         */
        public function toString(): string {
            return '(' . implode(' ' . $this->connect . ' ', $this->conditions) . ')';
        }
        
        /**
         * Get the string representation of the object.
         *
         * @return string The string representation of the object.
         */
        public function __toString(): string {
            return $this->toString();
        }
        
        /**
         * Add a "LIKE" condition to the query.
         *
         * @param string $value The value to compare against.
         *
         * @return static Returns the current instance.
         * @throws Exception
         */
        public function like(string $value): static {
            $this->add('LIKE', $value);
            
            return $this;
        }
        
        /**
         * Add a "NOT LIKE" condition to the query.
         *
         * @param string $value The value to compare against in the "LIKE" condition.
         *
         * @return static Returns an instance of the class for method chaining.
         * @throws Exception
         */
        public function notLike(string $value): static {
            $this->add('NOT LIKE', $value);
            
            return $this;
        }
        
        /**
         * Set the BETWEEN condition for the query.
         *
         * @param mixed $value1 The first value to compare.
         * @param mixed $value2 The second value to compare.
         *
         * @return static The updated instance of the class.
         * @throws Exception
         */
        public function between(mixed $value1, mixed $value2): static {
            $this->add('BETWEEN', [$value1, $value2]);
            
            return $this;
        }
        
        # ---------------------------------------------------------------------------
        
        /**
         * Set a CAST operation for the specified column in the query.
         *
         * @param string $column The name of the column to cast.
         * @param string $as     The data type to cast the column as.
         *
         * @return string The updated instance of the class.
         * @throws Exception
         */
        public function cast(string $column, string $as) {
            return 'CAST ' . dbkey($column) . ' AS ' . $as;
        }
        
        /**
         * Applies a given function to a column and returns the result.
         *
         * @param string $function The function to apply.
         * @param string $column   The column to apply the function to.
         *
         * @return string The result of applying the function to the column.
         */
        public function func(string $function, string $column): string {
            return strtoupper($function) . '(' . $column . ')';
        }
        
        /**
         * Applies the lower() function to a column and returns the result.
         *
         * @param string $column The column to apply the lower() function to.
         *
         * @return string The result of applying the lower() function to the column.
         */
        public function lower(string $column): string {
            return $this->func('lower', $column);
        }
        
        /**
         * Applies the "upper" function to the given column name.
         *
         * @param string $column The name of the column to apply the "upper" function on.
         *
         * @return string The modified column name with the "upper" function applied.
         */
        public function upper(string $column): string {
            return $this->func('upper', $column);
        }
        
    }
