<?php
    
    namespace Xmgr\Database\Schema;
    
    use Xmgr\Str;
    
    /**
     * Class Column
     *
     * Represents a column in a database table.
     */
    class Column {
        
        public Blueprint|null $table = null;
        
        protected array  $data      = [];
        protected string $name      = '';
        protected string $type      = '';
        protected mixed  $length    = null;
        public bool      $unsigned  = false;
        public bool      $zerofill  = false;
        public bool      $nullable  = false;
        public string    $on_update = '';
        
        protected array $indexes = [];
        
        public bool      $autoIncrement = false;
        protected bool   $primary       = false;
        public mixed     $default       = false;
        protected string $extra         = '';
        public string    $comment       = '';
        public string    $collation     = '';
        
        /**
         * Constructor for the class.
         *
         * @param string|array $data The data to be used for initialization. Can be either a string or an array.
         *                           If it is a string, it will be used as the name property of the object.
         *                           If it is an array, it will be passed to the parse() method for further
         *                           initialization.
         */
        public function __construct(string|array $data) {
            if (is_string($data)) {
                $this->name = $data;
            }
            if (is_array($data)) {
                $this->parse($data);
            }
        }
        
        /**
         * Retrieves the name associated with the current object.
         *
         * @return string The name of the object.
         */
        public function name(): string {
            return $this->name;
        }
        
        /**
         * Converts the type property to uppercase.
         *
         * @return string The type property in uppercase.
         */
        public function type(): string {
            return strtoupper($this->type);
        }
        
        /**
         * Converts the type property to a string representation.
         *
         * If the length property is null, the converted type is returned without length,
         * otherwise the length is appended in parentheses.
         *
         * @return string The converted type with optional length in parentheses.
         */
        public function typeToString(): string {
            return strtoupper($this->type) . ($this->length === null ? '' : '(' . (int)$this->length . ')');
        }
        
        /**
         * Parses the given data array and sets the corresponding properties of the object.
         *
         * @param array $data The data array to be parsed.
         *
         * @return void
         */
        protected function parse(array $data): void {
            $this->name = trim((string)arr($data, 'Field', ''));
            $this->parseType((string)arr($data, 'Type', ''));
            $nullvalue      = trim(strtolower(arr($data, 'Null', 'no')));
            $this->nullable = ($nullvalue === 'yes');
            $this->parseKeys((string)arr($data, 'Key', ''));
            # Default geht nur mit nullable enabled
            $this->default       = (string)arr($data, 'Default', '');
            $this->extra         = trim(strtolower((string)arr($data, 'Extra', '')));
            $this->autoIncrement = str_contains($this->extra, 'auto_increment');
        }
        
        /**
         * Parses a given type and sets the appropriate properties.
         *
         * @param string $type The type to parse.
         *
         * @return void
         */
        protected function parseType(string $type): void {
            $type = trim(strtolower($type));
            $this->setType(Str::before($type, '('));
            # @todo improve
            $this->setLength((int)Str::preg_group('/^.+?\((\d+)\)/i', $type, 1, '0'));
            $this->unsigned = str_contains($type, 'unsigned');
        }
        
        /**
         * Sets the unsigned flag to true.
         *
         * @return $this The current instance with the unsigned flag set to true.
         */
        public function unsigned(): static {
            $this->unsigned = true;
            
            return $this;
        }
        
        /**
         * Sets the unsigned property to false and returns the current object.
         *
         * @return $this The current object.
         */
        public function signed(): static {
            $this->unsigned = false;
            
            return $this;
        }
        
        /**
         * Parses the given key and sets the primary flag accordingly.
         *
         * @param string $key The key to be parsed.
         *
         * @return void
         */
        protected function parseKeys(string $key): void {
            $key           = trim(strtolower($key));
            $this->primary = str_contains($key, 'pri');
        }
        
        /**
         * @param string     $type
         * @param mixed|null $length
         *
         * @return $this
         */
        protected function setType(string $type, mixed $length = null): static {
            $this->type = $type;
            $this->setLength($length);
            
            return $this;
        }
        
        /**
         * Sets the length property of the object.
         *
         * @param mixed $length The new value for the length property. Accepts any data type.
         *
         * @return static The object itself after setting the length property.
         */
        protected function setLength(mixed $length): static {
            if ($length !== null) {
                $this->length = $length;
            }
            
            return $this;
        }
        
        /**
         * Sets the type property to uppercase and sets the length property.
         *
         * @param string $type   The type to be set. Should be a string.
         * @param mixed  $length The length to be set. Default is null.
         *
         * @return static Returns the instance of the class.
         */
        public function customType(string $type, mixed $length = null): static {
            $this->type = strtoupper($type);
            $this->setLength($length);
            
            return $this;
        }
        
        /**
         * Adds an index to the indexes array.
         *
         * @param string $index The value of the index.
         * @param string $name  (optional) The name associated with the index.
         *
         * @return $this The current object instance after adding the index.
         */
        public function addIndex(string $index, string $name = ''): static {
            $this->indexes[] = [$index, $name];
            
            return $this;
        }
        
        /**
         * Sets the ID type to "BIGINT", unsigned flag to true, and auto-increment flag to true.
         *
         * @return $this  Returns the current instance of the class for method chaining.
         */
        public function id(): static {
            $this->setType('BIGINT');
            $this->unsigned      = true;
            $this->autoIncrement = true;
            
            return $this;
        }
        
        /**
         * Sets the type of the property to "INT" and assigns a length to it.
         *
         * @param int|null $length The length of the INT property.
         *
         * @return $this The current instance of the class.
         */
        public function int(?int $length = null): static {
            $this->setType('INT', $length);
            
            return $this;
        }
        
        /**
         * Sets the type property to "TINYINT" with size 1.
         *
         * @return $this The current instance of the class.
         */
        public function bool(): static {
            $this->setType('TINYINT', 1);
            
            return $this;
        }
        
        /**
         * Sets the length for the `tinyint` column type.
         *
         * @param int|null $length The length to be set.
         *
         * @return $this
         */
        public function tinyint(?int $length = null): static {
            $this->setLength($length);
            
            return $this;
        }
        
        /**
         * Sets the type of the column to VARCHAR with a specified length.
         *
         * @param mixed $length The length of the VARCHAR column. Default is 256.
         *
         * @return $this
         */
        public function varchar(mixed $length = 256): static {
            $this->setType('VARCHAR', $length);
            
            return $this;
        }
        
        /**
         * Sets the data type of the column to FLOAT.
         *
         * @param int|null $length   The length of the floating-point number. Default is null.
         * @param int|null $decimals The number of decimal places. Default is null.
         *
         * @return $this
         */
        public function float(?int $length = null, ?int $decimals = null): static {
            $this->setType('FLOAT');
            
            return $this;
        }
        
        /**
         * Sets the type of the column to DOUBLE.
         *
         * @param int|null $length   The length of the double value. Default is null.
         * @param int|null $decimals The number of decimal places. Default is null.
         *
         * @return $this
         */
        public function double(?int $length = null, ?int $decimals = null): static {
            $this->setType('DOUBLE');
            
            return $this;
        }
        
        /**
         * Sets the column type to 'DOUBLE' with optional length and decimals.
         *
         * @param int|null $length   The length of the decimal column. If null, no length is set.
         * @param int|null $decimals The number of decimal places. If null, no decimals are set.
         *
         * @return $this
         */
        public function decimal(?int $length = null, ?int $decimals = null): static {
            $this->setType('DOUBLE');
            
            return $this;
        }
        
        /**
         * Sets the type of the column to TEXT.
         *
         * @return $this
         */
        public function text(): static {
            $this->setType('TEXT');
            
            return $this;
        }
        
        /**
         * Sets the data type for the column as MEDIUMTEXT.
         *
         * @return $this
         */
        public function mediumtext(): static {
            $this->setType('MEDIUMTEXT');
            
            return $this;
        }
        
        /**
         * Sets the column type as LONGTEXT for the database connection.
         *
         * @return $this
         */
        public function longtext(): static {
            $this->setType('LONGTEXT');
            
            return $this;
        }
        
        /**
         * Sets the column type to TIMESTAMP and configures the default and on update values.
         *
         * @param bool $default_current_timestamp   Whether to set the default value to current timestamp. Default is
         *                                          true.
         * @param bool $on_update_current_timestamp Whether to set the on update value to current timestamp. Default is
         *                                          false.
         *
         * @return $this
         */
        public function timestamp(bool $default_current_timestamp = true, bool $on_update_current_timestamp = false): static {
            $this->setType('TIMESTAMP');
            if ($default_current_timestamp) {
                $this->defaultCurrentTimestamp();
            }
            if ($on_update_current_timestamp) {
                $this->onUpdateCurrentTimestamp();
            }
            
            return $this;
        }
        
        /**
         * Sets the default value for the column to the current timestamp.
         *
         * @return $this
         */
        public function defaultCurrentTimestamp(): static {
            $this->default = 'CURRENT_TIMESTAMP()';
            
            return $this;
        }
        
        /**
         * Sets the column to be updated with the current timestamp on update.
         *
         * @return $this
         */
        public function onUpdateCurrentTimestamp(): static {
            $this->on_update = 'CURRENT_TIMESTAMP()';
            
            return $this;
        }
        
        /**
         * Sets the default value for the property.
         *
         * @param mixed $value The default value to set.
         *
         * @return $this Returns the current object instance.
         */
        public function setDefault(mixed $value): static {
            $this->default = dbvalue($value);
            
            return $this;
        }
        
        
        /**
         * Sets the default value of the property to null and makes it nullable.
         *
         * @return static Returns the current instance for method chaining.
         */
        public function defaultNull(): static {
            $this->nullable = true;
            $this->default  = null;
            
            return $this;
        }
        
        /**
         * Sets the comment property to the given message.
         *
         * @param string $message The comment message.
         *
         * @return static Returns the current instance for method chaining.
         */
        public function comment(string $message): static {
            $this->comment = $message;
            
            return $this;
        }
        
        
        /**
         * Sets the collation for the database connection.
         *
         * @param string $collation The collation to be set. Default is 'utf8mb4_unicode_ci'.
         *
         * @return $this
         */
        public function collate(string $collation = 'utf8mb4_unicode_ci'): static {
            $this->collation = $collation;
            
            return $this;
        }
        
        /**
         * Sets the primary key for the table.
         *
         * @return static Returns the instance of the current class.
         */
        public function primary(): static {
            $this->table->primary($this->name);
            
            return $this;
        }
        
        
        /**
         * Adds a unique constraint to the specified column in the table.
         *
         * @param ?string $name The name for the index. If you pass null, the name is the same as the column name.
         *
         * @return static Returns the current instance of the class.
         */
        public function unique(?string $name = null): static {
            $this->table->unique($this->name);
            
            return $this;
        }
        
    }
