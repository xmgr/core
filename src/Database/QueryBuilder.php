<?php
    
    namespace Xmgr\Database;
    
    use Xmgr\Collections\Collection;
    use Xmgr\Collections\ModelCollection;
    use Xmgr\Interfaces\Arrayable;
    use Xmgr\Interfaces\Collectable;
    use Xmgr\Record;
    
    /**
     * Replaces data into a table.
     *
     * @param string|null $table    The name of the table.
     * @param array       $data     An array of data to be inserted.
     * @param mixed       ...$where Additional parameters for the WHERE clause (optional).
     *
     * @return $this The current instance of the class.
     * @throws \Exception
     */
    class QueryBuilder implements Arrayable, Collectable {
        
        public const int STMT_SELECT  = 1;
        public const int STMT_INSERT  = 2;
        public const int STMT_UPDATE  = 4;
        public const int STMT_DELETE  = 8;
        public const int STMT_REPLACE = 16;
        
        protected string $table    = '';
        protected string $priority = '';
        protected array  $select   = [];
        protected bool   $count    = false;
        protected array  $data     = [];
        protected string $where    = '';
        protected int    $action   = 0;
        protected array  $orderby  = [];
        protected ?int   $limit    = null;
        protected array  $joins    = [];
        protected string $having   = '';
        protected array  $groupby  = [];
        protected bool   $ignore   = false;
        
        /** @var string|Record $class */
        protected string|Record $class = Record::class;
        /** @var string|Collection $collection */
        protected string|Collection $collection = ModelCollection::class;
        
        protected ?string $connection = null;
        
        /**
         * Constructs a new instance of the class.
         *
         * @param string $table    The table name to be used for database operations.
         * @param mixed  ...$where The conditions used to filter the database query.
         */
        public function __construct(string $table = '', ...$where) {
            $this->setTable($table);
            $this->setWhere(...$where);
        }
        
        /**
         * Set the database connection to be used.
         *
         * @param string|null $connection The name of the database connection to be used.
         *
         * @return self The modified instance of the class.
         */
        public function withConnection(?string $connection) {
            $this->connection = $connection;
            
            return $this;
        }
        
        /**
         * Sets the class to be used by the current instance.
         *
         * @param string $name The name of the class to be used.
         *
         * @return $this The current instance for method chaining.
         */
        public function useClass(string $name): static {
            if (class_exists($name)) {
                $this->class = $name;
            }
            
            return $this;
        }
        
        /**
         * Set the priority of the action to high.
         *
         * @return $this The current instance of the class.
         */
        public function highPriority(): static {
            $this->priority = 'HIGH_PRIORITY';
            
            return $this;
        }
        
        /**
         * Set the priority of the current action to "LOW_PRIORITY".
         *
         * @return $this The current object with the priority set to "LOW_PRIORITY".
         */
        public function lowPriority(): static {
            $this->priority = 'LOW_PRIORITY ';
            
            return $this;
        }
        
        /**
         * Sets the name of the collection to be used.
         *
         * @param string $name The name of the collection.
         *
         * @return $this The current instance for method chaining.
         */
        public function useCollection(string $name): static {
            if ($name && class_exists($name)) {
                $this->collection = $name;
            }
            
            return $this;
        }
        
        /**
         * Selects specified columns from the database.
         *
         * @param string|array|bool $columns The columns to select. This can be a string representing a single column
         *                                   name, an array of column names, or a boolean indicating whether to select
         *                                   all columns
         *                                   (default is "*").
         *
         * @return $this The instance of the current class.
         * @throws \Exception
         */
        public function select(string|array|bool $columns = '*', string $table = ''): static {
            $this->setAction(self::STMT_SELECT);
            $this->select = array_replace($this->select, dbkeys($columns));
            $this->setTable($table);
            
            return $this;
        }
        
        /**
         * Add a column to the select statement.
         *
         * @param string      $column The name of the column to be selected.
         * @param string|null $alias  The optional alias for the column.
         *
         * @return $this Returns the current instance of the class.
         */
        public function column(string $column, ?string $alias = null): static {
            if ($alias === null) {
                $this->select[] = db_keyname($column);
            } else {
                $this->select[] = [db_keyname($column), db_keyname($alias)];
            }
            
            return $this;
        }
        
        /**
         * Set the count flag to true and fetch the count of rows from the database.
         *
         * @return int The count of rows from the database as an integer.
         * @throws \Exception
         */
        public function count(): int {
            $this->count = true;
            
            return (int)$this->fetchColumn('COUNT(*)');
        }
        
        /**
         * Adds a JOIN clause to the query.
         *
         * @param string $table    The table to join.
         * @param mixed  ...$where The condition for the join clause. It can be in various formats depending on the
         *                         implementation.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function join(string $table, ...$where): static {
            $this->joins[] = 'JOIN ' . dbkey($table) . ' ON ' . where(...$where);
            
            return $this;
        }
        
        /**
         * Performs an inner join on the given table with the specified conditions.
         *
         * @param string $table    The name of the table to join.
         * @param mixed  ...$where The conditions for the join clause.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function innerJoin(string $table, ...$where): static {
            $this->joins[] = 'INNER JOIN ' . dbkey($table) . ' ON ' . where(...$where);
            
            return $this;
        }
        
        /**
         * Adds a LEFT JOIN clause to the database query.
         *
         * @param string $table    The name of the table to join.
         * @param mixed  ...$where The conditions for the join clause.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function leftJoin(string $table, ...$where): static {
            $this->joins[] = 'LEFT JOIN ' . dbkey($table) . ' ON ' . where(...$where);
            
            return $this;
        }
        
        /**
         * Performs a right join operation with the specified table and join conditions.
         *
         * @param string $table The name of the table to perform the right join on.
         * @param mixed  $where The conditions for the right join operation. Accepts multiple parameters based on your
         *                      specific requirements.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function rightJoin(string $table, ...$where): static {
            $this->joins[] = 'JOIN ' . dbkey($table) . ' ON ' . where(...$where);
            
            return $this;
        }
        
        /**
         * Checks if the given action is equal to the current action.
         *
         * @param int $action The action to compare against the current action.
         *
         * @return bool Returns true if the given action is equal to the current action, false otherwise.
         */
        protected function actionIs(int $action): bool {
            return $this->action === $action;
        }
        
        /**
         * Set the action to be performed by the method.
         *
         * @param int $action The action to be performed.
         *
         * @return $this The instance of the class with the updated action.
         */
        protected function setAction(int $action): static {
            $this->action = $action;
            
            return $this;
        }
        
        /**
         * Sets the data for the query.
         *
         * @param array $data The array of data to be set.
         *
         * @return $this
         * @throws \Exception
         */
        public function setData(array $data): static {
            $this->data = $data;
            
            return $this;
        }
        
        /**
         * Adds data for the query.
         *
         * @param array $data The array of data to be set.
         *
         * @return $this
         * @throws \Exception
         */
        public function addData(array $data): static {
            $this->data = array_replace($this->data, $data);
            
            return $this;
        }
        
        /**
         * Inserts data into a table.
         *
         * @param array $data     An array of data to be inserted.
         * @param mixed ...$where Additional parameters for the WHERE clause (optional).
         *
         * @return $this The current instance of the class.
         * @throws \Exception
         */
        public function insert(array $data = [], ...$where): static {
            $this->setAction(self::STMT_INSERT);
            $this->addData($data ?? []);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Replaces data into a table.
         *
         * @param string|null $table    The name of the table.
         * @param array       $data     An array of data to be inserted.
         * @param mixed       ...$where Additional parameters for the WHERE clause (optional).
         *
         * @return $this The current instance of the class.
         * @throws \Exception
         */
        public function replace(?string $table = null, array $data = [], ...$where): static {
            $this->setAction(self::STMT_REPLACE);
            $this->setTable($table);
            $this->addData($data ?? []);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Updates data in a table.
         *
         * @param array $data     An array of data to be updated.
         * @param mixed ...$where Additional parameters for the WHERE clause (optional).
         *
         * @return $this The current instance of the class.
         * @throws \Exception
         */
        public function update(array $data, ...$where): static {
            $this->setAction(self::STMT_UPDATE);
            $this->setData($data);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Sets a value for a specific key in the data array.
         *
         * @param string $key   The key of the value to set.
         * @param mixed  $value The value to set.
         *
         * @return $this
         */
        public function set(string $key, mixed $value): static {
            $this->data[$key] = $value;
            
            return $this;
        }
        
        /**
         * Sets the table for the query.
         *
         * @param string $table The name of the table.
         *
         * @return $this
         * @throws \Exception
         */
        public function from(string $table, ...$where): static {
            $this->setTable($table);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Sets the table for the query.
         *
         * @param string $table The name of the table.
         *
         * @return $this
         * @throws \Exception
         */
        public function into(string $table, ...$where): static {
            $this->setTable($table);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Deletes data from a table.
         *
         * @param mixed ...$where Additional parameters for the WHERE clause (optional).
         *
         * @return $this The current instance of the class.
         */
        public function delete(...$where): static {
            $this->setAction(self::STMT_DELETE);
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Sets the "WHERE" clause for the query.
         *
         * @param mixed ...$where The conditions for the "WHERE" clause.
         *
         * @return void
         */
        protected function setWhere(...$where): void {
            $this->where = where(...$where);
        }
        
        /**
         * Sets the table for the query.
         *
         * @param string $table The name of the table to set.
         *
         * @return $this
         */
        public function setTable(string $table): static {
            if ($table) {
                $this->table = $table;
            }
            
            return $this;
        }
        
        /**
         * Sets the "WHERE" clause for the query and returns the current object.
         *
         * @param mixed ...$where The conditions for the "WHERE" clause.
         *
         * @return $this
         */
        public function where(...$where): static {
            $this->setWhere(...$where);
            
            return $this;
        }
        
        /**
         * Sets the HAVING clause of the query based on the given conditions.
         *
         * @param mixed ...$where The conditions to be applied in the HAVING clause.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function having(...$where): static {
            $this->having = ' HAVING ' . where(...$where);
            
            return $this;
        }
        
        /**
         * Set the order by clause for the query.
         *
         * @param string $column The column to order by.
         * @param string $order  The order direction (ASC or DESC). Default is ASC.
         *
         * @return $this Returns the current instance of the class.
         */
        public function orderBy(string $column, string $order = 'ASC'): static {
            $order           = trim(strtoupper($order));
            $order           = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';
            $this->orderby[] = dbkey($column) . ' ' . $order;
            
            return $this;
        }
        
        /**
         * Randomize the order of the retrieved data.
         *
         * This method adds a random order clause to the query, causing the
         * fetched data to be returned in a random order each time this
         * method is called.
         *
         * @return $this The current instance of the object for method chaining.
         */
        public function randomly(?int $limit = null): static {
            $this->orderby[] = 'RAND()';
            $this->limit($limit);
            
            return $this;
        }
        
        /**
         * Add ASC sorting to the query.
         *
         * @param string $column The column to sort in ascending order.
         *
         * @return $this The current instance of the class.
         */
        public function asc(string $column): static {
            $this->orderby[] = dbkey($column) . ' ASC';
            
            return $this;
        }
        
        /**
         * Set the column to be used for ordering the data in descending order.
         *
         * @param string $column The column name to be used for ordering. Default value is "id".
         *
         * @return $this Returns the current instance for method chaining.
         */
        public function latest(int|bool|null $limit = false, string $column = 'id'): static {
            if (is_int($limit)) {
                $this->limit($limit);
            }
            $this->orderby[] = dbkey($column) . ' DESC';
            
            return $this;
        }
        
        /**
         * Set the sort order for the specified column in descending order.
         *
         * @param string $column The column name to set the sort order for.
         *
         * @return $this The current instance of the class.
         */
        public function desc(string $column): static {
            $this->orderby[] = dbkey($column) . ' DESC';
            
            return $this;
        }
        
        /**
         * Set the limit for the query results.
         *
         * @param int|null $limit The maximum number of rows to be returned. Leave null if no limit is required.
         *
         * @return static Returns the instance of the class for method chaining.
         */
        public function limit(?int $limit): static {
            if ($limit) {
                $this->limit = $limit;
            }
            
            return $this;
        }
        
        /**
         * Execute the selected action.
         *
         * @return int|false|null The result of executing the action.
         * @throws \Exception
         */
        public function run(): int|null|false {
            return $this->exec();
        }
        
        /**
         * Finds a record in the database based on the given ID.
         *
         * @param int $id The ID of the record to find.
         *
         * @return $this The current instance for method chaining.
         * @throws \Exception
         */
        public function find(int $id) {
            $this->setWhere($id);
            
            return $this;
        }
        
        /**
         * Execute the specified action in the database.
         *
         * @return int|null|false  The number of affected rows by the executed query.
         * @throws \Exception If an error occurs during the execution of the query.
         */
        public function exec(): false|int|null {
            $query = '';
            switch ($this->action) {
                case self::STMT_INSERT:
                    $query = $this->buildInsert();
                    break;
                case self::STMT_REPLACE:
                    $query = $this->buildReplace();
                    break;
                case self::STMT_UPDATE:
                    $query = $this->buildUpdate();
                    break;
                case self::STMT_DELETE:
                    $query = $this->buildDelete();
                    break;
                default:
                    break;
            }
            if (!$query) {
                return null;
            }
            
            return db($this->connection)->exec($query);
        }
        
        /**
         * Fetches a single row from the database based on the current query.
         *
         * @return array|false The fetched row as an associative array, or false if no row is found.
         */
        public function fetch(): false|array {
            return db($this->connection)->query($this->buildSelect())->fetch() ?: [];
        }
        
        /**
         * Fetches a single column from the database based on the selected action.
         *
         * @param string     $column  The name of the column to fetch.
         * @param mixed|null $default (optional) The default value to return if the column is not found.
         *
         * @return mixed The fetched column value.
         * @throws \Exception
         */
        public function fetchColumn(string $column, mixed $default = null): mixed {
            $this->select($column);
            
            return db($this->connection)->query($this->buildSelect())->fetchColumn($column);
        }
        
        /**
         * Executes the appropriate action based on the current action and returns the result.
         *
         * @return array|Record Returns the result of the executed action. If the current action is "SELECT", it queries
         *                     the database using the built SELECT statement and returns the fetched row. Otherwise, it
         *                     returns an empty array.
         */
        public function first(): array|Record {
            return $this->asRecordOrArray($this->fetch());
        }
        
        /**
         * Convert given data into a record object.
         *
         * @param mixed $data The data to be converted into a record object.
         *
         * @return Record The record object created from the given data.
         */
        public function asRecord(mixed $data): Record {
            $classname = $this->class;
            
            return new $classname($data);
        }
        
        /**
         * Convert the given data to a Record or an array based on the selected action.
         *
         * @param mixed $data The data to be converted.
         *
         * @return Record|array The converted data.
         */
        public function asRecordOrArray(mixed $data): Record|array {
            return $this->class ? $this->asRecord($data) : $data;
        }
        
        /**
         * Retrieves the first record from the database result set and converts it to an array.
         *
         * @return array The first record in the result set as an array.
         */
        public function firstToArray(): array {
            return $this->fetch();
        }
        
        /**
         * Fetches all records from the database.
         *
         * @return array[] The fetched records from the database.
         */
        public function fetchAll(): array {
            return db($this->connection)->query($this->buildSelect())->fetchAll() ?: [];
        }
        
        /**
         * Retrieve data from the database based on the selected action.
         *
         * @return array|Collection The fetched data from the database.
         */
        public function get(): array|Collection {
            return $this->makeCollection($this->asRecordsOrArrays($this->fetchAll()));
        }
        
        
        /**
         * Returns the result of the query as an array.
         *
         * @return array[] The result of the query as an array.
         * @throws \Exception If an error occurs during the query execution.
         */
        public function toArray(): array {
            return $this->fetchAll();
        }
        
        
        /**
         * Convert the given data into an array of records based on the selected action.
         *
         * @param mixed $data The data that needs to be converted into records.
         *
         * @return array|Record[] The converted data as an array of records.
         */
        public function asRecords(mixed $data): array {
            $classname = $this->class;
            
            return $classname::construct($data);
        }
        
        /**
         * Convert the given data into records or arrays based on the selected action.
         *
         * @param array $data The data to be converted.
         *
         * @return array[]|Collection The converted data.
         */
        public function asRecordsOrArrays(mixed $data): array|Collection {
            return $this->class ? $this->asRecords($data) : $data;
        }
        
        /**
         * Collects data from the current instance and returns a new Collection based on the given class.
         *
         * @param Collection|string|null $collection The class name of the Collection to create.
         *
         * @return Collection The new Collection instance.
         */
        public function collect(Collection|string $collection = null): Collection {
            return $this->makeCollection($this->asRecordsOrArrays($this->fetchAll()), $collection);
        }
        
        /**
         * Create a collection from the given data.
         *
         * @param array       $data       The data to create the collection from.
         * @param string|null $collection The class name of the collection to create. If null, use the default
         *                                collection defined in the class or use the Collection class.
         *
         * @return object The created collection object.
         */
        protected function makeCollection(array $data, ?string $collection = null): object {
            $collection = $collection ?: $this->collection ?: Collection::class;
            $collection = class_exists($collection) ? $collection : Collection::class;
            
            return $collection::fromArray($data);
        }
        
        /**
         * Builds the JOIN part of the SQL query.
         *
         * Only returns a JOIN clause if the current action is 'SELECT'.
         *
         * @return string The JOIN clause or an empty string if the action is not 'SELECT'.
         */
        protected function buildJoin(): string {
            if ($this->actionIs(self::STMT_SELECT)) {
                return ' ' . implode(' ', $this->joins);
            }
            
            return '';
        }
        
        /**
         * Build the priority string.
         *
         * @return string The built priority string.
         */
        protected function buildPriority(): string {
            return ($this->priority ? $this->priority . ' ' : '');
        }
        
        /**
         * Builds the WHERE clause for the database query.
         *
         * @return string The WHERE clause string for the query, or an empty string if no WHERE condition is set.
         */
        protected function buildWhere(): string {
            return ($this->where ? ' WHERE ' . $this->where : '');
        }
        
        /**
         * Builds the GROUP BY clause for the SQL query.
         *
         * @return string The GROUP BY clause as a string. Returns an empty string if no GROUP BY clause is set.
         */
        protected function buildGroupBy(): string {
            return ($this->groupby ? ' GROUP BY ' . implode(', ', $this->groupby) : '');
        }
        
        /**
         * Retrieves the HAVING clause used in the query.
         *
         * @return string The HAVING clause used in the query.
         */
        protected function buildHaving(): string {
            return $this->having;
        }
        
        /**
         * Builds the ORDER BY clause for the database query.
         *
         * @return string The generated ORDER BY clause.
         */
        protected function buildOrderBy(): string {
            return ($this->orderby ? ' ORDER BY ' . implode(', ', $this->orderby) : '');
        }
        
        /**
         * Builds the LIMIT clause for a SQL query.
         *
         * @return string The LIMIT clause for the SQL query, if a limit has been set. Otherwise, an empty string.
         */
        protected function buildLimit(): string {
            return ($this->limit ? ' LIMIT ' . $this->limit : '');
        }
        
        /**
         * Generate the string representing the columns in a SQL query.
         *
         * @return string The string representing the columns in the SQL query.
         */
        protected function sqlColumnsString(): string {
            $result = [];
            foreach ($this->select as $s) {
                if (is_array($s)) {
                    $result[] = $s[0] . ' AS ' . $s[1];
                } else {
                    $result[] = $s;
                }
            }
            $result = array_unique($result);
            
            return $result ? implode(', ', $result) : '*';
        }
        
        /**
         * Builds a SELECT query string.
         *
         * @return string The SELECT query string.
         */
        protected function buildSelect(): string {
            return 'SELECT ' . $this->buildPriority() . ($this->count ? 'COUNT(*)' : $this->sqlColumnsString()) . ' FROM ' . dbkey($this->table) . $this->buildJoin() . $this->buildWhere() . $this->buildGroupBy() . $this->buildHaving() . $this->buildOrderBy() . $this->buildLimit();
        }
        
        /**
         * Builds the SQL query for inserting data into a table.
         *
         * @return string The SQL query for the insert operation.
         */
        protected function buildInsert(): string {
            return 'INSERT ' . ($this->ignore ? 'IGNORE ' : '') . 'INTO ' . $this->buildPriority() . dbkey($this->table) . ' ' . sql_values($this->data);
        }
        
        /**
         * Builds the SQL query for replacing data into a table.
         *
         * @return string The SQL query for the insert operation.
         */
        protected function buildReplace(): string {
            return 'REPLACE ' . $this->buildPriority() . 'INTO ' . dbkey($this->table) . ' SET ' . dbset($this->data, true);
        }
        
        /**
         * Builds the SQL update statement based on the table, data, and where clause.
         *
         * @return string The SQL update statement.
         */
        protected function buildUpdate(): string {
            return 'UPDATE ' . $this->buildPriority() . dbkey($this->table) . ' SET ' . dbset($this->data, true) . $this->buildWhere();
        }
        
        /**
         * Builds the SQL delete statement based on the table and where clause.
         *
         * @return string The SQL delete statement.
         */
        protected function buildDelete(): string {
            return 'DELETE ' . $this->buildPriority() . 'FROM ' . dbkey($this->table) . $this->buildWhere();
        }
        
        # #############
        
        /**
         * Gets the SELECT statement to fetch records from the database.
         *
         * @return string The SELECT statement.
         * @throws \Exception If an error occurs while building the SELECT statement.
         */
        public function getSqlSelectStatement(): string {
            return $this->buildSelect();
        }
        
        /**
         * Gets the SQL insert statement for the current instance.
         *
         * @return string The SQL insert statement.
         */
        public function getSqlInsertStatement(): string {
            return $this->buildInsert();
        }
        
        /**
         * Gets the SQL replace statement for the current instance.
         *
         * @return string The SQL insert statement.
         */
        public function getSqlReplaceStatement(): string {
            return $this->buildReplace();
        }
        
        /**
         * Gets the SQL update statement based on the current instance.
         *
         * @return string The SQL update statement.
         */
        public function getSqlUpdateStatement(): string {
            return $this->buildUpdate();
        }
        
        /**
         * Retrieves the delete statement for the current instance.
         *
         * @return string The generated delete statement.
         */
        public function getSqlDeleteStatement(): string {
            return $this->buildDelete();
        }
        
    }
