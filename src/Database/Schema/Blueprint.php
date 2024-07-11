<?php
    
    namespace Xmgr\Database\Schema;
    
    /**
     * The Table class represents a database table.
     */
    class Blueprint {
        
        protected string $name = '';
        
        protected bool $create = true;
        
        public string $engine   = 'InnoDB';
        public bool   $checksum = false;
        /**
         * @var array|Column[]
         */
        public array $columns = [];
        
        public string $comment        = '';
        public int    $auto_increment = 1;
        public string $collation      = 'utf8mb4_unicode_ci';
        public array  $primary        = [];
        public array  $uniques        = [];
        public array  $indexes        = [];
        public array  $adddata        = [];
        public array  $foreigns       = [];
        
        /**
         * Determines the MySQL string data type based on the length of the given string.
         *
         * @param string $string The string for which the data type is determined.
         *
         * @return string|array The MySQL string data type. If the length is less than or equal to 255, it returns an
         *                      array with the data type 'VARCHAR' and maximum length of 255. If the length is between
         *                      256 and 65535, it returns 'TEXT'. If the length is between 65536 and 16777215, it
         *                      returns 'MEDIUMTEXT'. If the length is between 16777216 and 4294967295, it returns
         *                      'LONGTEXT'. If the length is greater than 4294967295, it returns 'UNKNOWN'.
         */
        public function getMySQLStringDataType(string $string) {
            $length = strlen($string);
            if ($length <= 255) {
                return ['VARCHAR', 255];
            } elseif ($length <= 65535) {
                return 'TEXT';
            } elseif ($length <= 16777215) {
                return 'MEDIUMTEXT';
            } elseif ($length <= 4294967295) {
                return 'LONGTEXT';
            } else {
                return 'UNKNOWN';
            }
        }
        
        /**
         * Class Constructor.
         *
         * This method is called when a new instance of the class is created.
         *
         * @param string $name The name parameter for the object.
         *
         * @return void
         */
        public function __construct(string $name, bool $create = true) {
            $this->name   = $name;
            $this->create = $create;
        }
        
        /**
         * Add a column to the table.
         *
         * @param mixed $column The column to add. Can be a Column object or a string representing the column name.
         *
         * @return Column The added column.
         */
        protected function add(mixed $column): Column {
            $obj                         = ($column instanceof Column ? $column : new Column($column));
            $obj->table                  = $this;
            $this->columns[$obj->name()] = $obj;
            
            return $obj;
        }
        
        /**
         * Sets the database engine for the table.
         *
         * @param string $name The name of the database engine.
         *
         * @return $this
         */
        public function engine(string $name) {
            $this->engine = $name;
            
            return $this;
        }
        
        /**
         * Enable the checksum feature for the method instance.
         *
         * @return $this
         */
        public function checksum() {
            $this->checksum = true;
            
            return $this;
        }
        
        /**
         * Set the auto increment value for the column.
         *
         * @param int $value The auto increment value to set. (default: 1)
         *
         * @return $this Returns the current instance of the column for method chaining.
         */
        public function autoIncrement(int $value = 1) {
            $this->auto_increment = max(1, abs($value));
            
            return $this;
        }
        
        /**
         * Creates a new instance of the Column class with the specified name.
         *
         * @param string $name The name of the column.
         *
         * @return Column Returns a new instance of the Column class with the specified name.
         */
        public function column(string $name): Column {
            return $this->add($name);
        }
        
        /**
         * Returns a new instance of the Column object with the name "id".
         *
         * @return Column
         */
        public function id(): Column {
            return $this->add('id')->id();
        }
        
        /**
         * Adds a new INT column to the table with the specified name and length.
         *
         * @param string   $name   The name of the INT column.
         * @param int|null $length The length of the INT column. If not provided, it defaults to null.
         *
         * @return Column Returns the newly created INT column object.
         */
        public function int(string $name, ?int $length = null): Column {
            return $this->add($name)->int($length);
        }
        
        /**
         * Adds a boolean field with the given name and returns the current object after setting the field's value to
         * bool.
         *
         * @param string $name The name of the boolean field.
         *
         * @return Column Returns the current object for method chaining.
         */
        public function bool(string $name): Column {
            return $this->add($name)->bool();
        }
        
        /**
         * Creates a new tinyint column in the database table.
         *
         * @param string $name   The name of the column.
         * @param mixed  $length The length of the column. Default is 1.
         *
         * @return Column  The created column object.
         */
        public function tinyint(string $name, int $length = 1) {
            return $this->add($name)->tinyint($length);
        }
        
        /**
         * Specifies a column with a string data type.
         *
         * @param string $name   The name of the column.
         * @param mixed  $length The maximum length of the string. Default is 255.
         *
         * @return Column The instance of the Column class.
         */
        public function string(string $name, mixed $length = 255) {
            $type = 'VARCHAR';
            if ($length <= 255) {
            
            } elseif ($length <= 65535) {
                $length = null;
                $type   = 'TEXT';
            } elseif ($length <= 16777215) {
                $length = null;
                $type   = 'MEDIUMTEXT';
            } elseif ($length <= 4294967295) {
                $length = null;
                $type   = 'LONGTEXT';
            } else {
                $type = 'TEXT';
            }
            
            return $this->add($name)->customType($type, $length);
        }
        
        /**
         * Creates a new varchar column in the database.
         *
         * @param string $name   The name of the column.
         * @param mixed  $length The maximum number of characters allowed in the column (default is 255).
         *
         */
        public function varchar(string $name, mixed $length = 255) {
            return $this->add($name)->varchar($length);
        }
        
        /**
         * Creates a new float column in the database.
         *
         * @param string   $name     The name of the column.
         * @param int|null $length   The precision of the column (default is null).
         * @param int|null $decimals The number of decimal places allowed in the column (default is null).
         *
         */
        public function float(string $name, ?int $length = null, ?int $decimals = null) {
            return $this->add($name)->float($length, $decimals);
        }
        
        /**
         * @param string   $name
         * @param int|null $length
         * @param int|null $decimals
         *
         * @return Column
         */
        public function double(string $name, ?int $length = null, ?int $decimals = null) {
            return $this->add($name)->double($length, $decimals);
        }
        
        /**
         * @param string   $name
         * @param int|null $length
         * @param int|null $decimals
         *
         * @return Column
         */
        public function real(string $name, ?int $length = null, ?int $decimals = null) {
            return $this->add($name)->double($length, $decimals);
        }
        
        /**
         * @param string   $name
         * @param int|null $length
         * @param int|null $decimals
         *
         * @return Column
         */
        public function decimal(string $name, ?int $length = null, ?int $decimals = null) {
            return $this->add($name)->double($length, $decimals);
        }
        
        /**
         * @param string $name
         *
         * @return Column
         */
        public function text(string $name) {
            return $this->add($name)->text();
        }
        
        /**
         * @param string $name
         *
         * @return Column
         */
        public function mediumtext(string $name) {
            return $this->add($name)->mediumtext();
        }
        
        /**
         * Adds a longtext column with the given name to the current object.
         *
         * @param string $name The name of the column to be added.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function longtext(string $name): static {
            return $this->add((new Column($name))->longtext());
        }
        
        /**
         * Sets the comment for the current object.
         *
         * @param string $message The comment message to be set.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function comment(string $message): static {
            $this->comment = $message;
            
            return $this;
        }
        
        /**
         * Adds the primary key 'id' to the list of primary keys for the current object.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function primaryId(): static {
            $this->primary[] = dbkey('id');
            
            return $this;
        }
        
        /**
         * Sets the given columns as primary key.
         *
         * @param string|array $columns The column(s) to be set as primary key.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function primary(string|array $columns): static {
            $this->primary[] = dbkey($columns);
            
            return $this;
        }
        
        /**
         * Adds uniqueness constraints for the specified columns.
         *
         * This method allows you to add uniqueness constraints for one or more columns.
         * The uniqueness constraints help in ensuring that the values in the specified columns are unique,
         * preventing duplicate entries in the database.
         *
         * @param string|array $columns The column(s) for which uniqueness constraints are to be added.
         *                              In case of multiple columns, you can pass them as an array or a string.
         *                              If passing a string, the columns should be separated by commas.
         *
         * @param string|null  $name    (Optional) The name of the uniqueness constraint.
         *                              If not provided, a default name will be generated based on the columns being
         *                              constrained. The name is converted to a database-friendly format using the
         *                              dbkey function.
         *
         * @return $this Returns the current instance of the class, allowing for method chaining.
         */
        public function unique(string|array $columns, ?string $name = null): static {
            $name                 = static::combinedColumnName($name, $columns);
            $this->uniques[$name] = dbkey($columns);
            
            return $this;
        }
        
        /**
         * Combine column names.
         *
         * This function combines column names to form a new column name.
         *
         * @param mixed $columns The columns to be combined. It can be an array or an object.
         *
         * @return string The combined column name.
         */
        public static function combinedColumnName($name, mixed $columns): string {
            if ($name === null) {
                $names = [];
                foreach (dbkeys($columns) as $colname) {
                    $names[] = str_replace(['` *'], '', $colname);
                }
                
                return trim(str_collapse(dbkey(implode('_', $names)), '_'), '_');
            } else {
                return str_replace('`', '', dbkey($name));
            }
        }
        
        /**
         * Creates an index on the specified columns.
         *
         * This method creates an index on the specified columns. If a name is provided, it will be used
         * as the index name. Otherwise, a combined column name will be generated using the provided columns.
         *
         * @param string|array $columns The column(s) on which the index should be created.
         * @param string|null  $name    (optional) The name for the index. If not provided, a combined column name will
         *                              be used.
         *
         * @return $this Returns the current instance of the class for method chaining.
         */
        public function index(string|array $columns, ?string $name = null): static {
            $name                 = static::combinedColumnName($name, $columns);
            $this->indexes[$name] = dbkey($columns);
            
            return $this;
        }
        
        /**
         * Generates foreign key constraint SQL statement and adds it to the foreigns array.
         *
         * This method generates a foreign key constraint SQL statement based on the provided parameters.
         * The generated SQL statement is then added to the foreigns array for further processing.
         *
         * @param string $name       The name of the foreign key constraint.
         * @param string $column     The column name of the table.
         * @param string $ref_table  The referenced table name.
         * @param string $ref_column The referenced column name.
         * @param string $on_update  The action to be taken on update (default: 'NO ACTION').
         * @param string $on_delete  The action to be taken on delete (default: 'NO ACTION').
         *
         * @return $this Returns the current object instance for method chaining.
         */
        public function foreign(string $name, string $column, string $ref_table, string $ref_column, string $on_update = 'NO ACTION', string $on_delete = 'NO ACTION'): static {
            $on_update = trim(str_collapse(str_replace(['-', '_'], ' ', strtoupper($on_update))));
            $on_delete = trim(str_collapse(str_replace(['-', '_'], ' ', strtoupper($on_delete))));
            
            $this->foreigns[] = 'CONSTRAINT ' . dbkey($name) . ' FOREIGN KEY (' . dbkey($column) . ') REFERENCES ' . dbkey($ref_table) . ' (' . dbkey($ref_column) . ') ON UPDATE ' . $on_update . ' ON DELETE ' . $on_delete;
            
            return $this;
        }
        
        /**
         * Sets the collation for the current object. If no argument is provided, it defaults to 'utf8mb4_unicode_ci'.
         *
         * @param string $collation The collation to be set.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function collate(string $collation = 'utf8mb4_unicode_ci'): static {
            $this->collation = $collation;
            
            return $this;
        }
        
        /**
         * Adds a new data entry to the adddata array.
         *
         * @param string $name  The name of the data entry.
         * @param mixed  $value The value of the data entry.
         *
         * @return $this;
         */
        public function addData(string $name, mixed $value): static {
            $this->adddata[strtoupper(trim($name))] = $value;
            
            return $this;
        }
        
    }
