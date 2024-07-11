<?php
    
    namespace Xmgr;
    
    use PDO;
    use Xmgr\Database\Table;
    use Xmgr\Exceptions\DatabaseConnectionFailedException;
    use Xmgr\Exceptions\MissingOrUnknownDatabaseConnectionName;
    
    /**
     * Class Database
     *
     * The Database class represents a connection to a database using PDO.
     */
    class Database {
        
        /**
         * @var array|static[]
         */
        private static array   $instances        = [];
        private static ?string $activeConnection = null;
        protected ?PDO         $pdo              = null;
        
        public array $history = [];
        
        /**
         * @var array|Table[]
         */
        protected array $tables = [];
        
        /**
         * Constructor for the class.
         *
         * Initializes the database connection using the configuration settings.
         *
         * @throws DatabaseConnectionFailedException
         */
        protected function __construct() {
            $connection     = self::$activeConnection;
            $connection_key = "database.connections.$connection";
            $driver         = config("$connection_key.driver");
            $name           = (string)config("$connection_key.name");
            $timezone       = (string)config("$connection_key.timezone", config("database.defaults.$driver.timezone", ''));
            $host           = (string)config("$connection_key.host", config("database.defaults.$driver.host", ''));
            $username       = (string)config("$connection_key.username", config("database.defaults.$driver.username", ''));
            $password       = (string)config("$connection_key.password", '');
            $port           = (string)config("$connection_key.port", config("database.defaults.$driver.port"));
            $charset        = (string)config("$connection_key.pdo.charset", config('database.defaults.pdo.charset'));
            $options        = array_replace_recursive((array)config('database.defaults.pdo.options', []), (array)config("$connection_key.pdo.options", []));
            
            try {
                $this->pdo = new PDO("mysql:host=$host;dbname=$name;port=$port;charset=$charset", $username, $password, $options);
                if ($timezone) {
                    $this->pdo->exec("SET time_zone = '$timezone';");
                }
            } catch (\Throwable $e) {
                throw new DatabaseConnectionFailedException($e);
            }
        }
        
        /**
         * Retrieves all the tables.
         *
         * @return array Returns an array containing all the tables.
         */
        public function tables() {
            return $this->tables;
        }
        
        /**
         * Retrieves the specified table from the tables array.
         *
         * @param string $name The name of the table to retrieve.
         *
         * @return Table Returns the specified table from the tables array if it exists.
         *                   If the table does not exist, null is returned.
         */
        public function table(string $name): Table {
            return array_key_exists($name, $this->tables) ? $this->tables[$name] : new Table('');
        }
        
        /**
         * Get the database connection.
         *
         * @return \PDO|null Returns the \PDO database connection object, or null if the connection is not established.
         */
        public function connection(): ?PDO {
            return $this->pdo;
        }
        
        /**
         * Logs the given query.
         *
         * @param string $query The query to log.
         *
         * @return void
         */
        private function log(string $query): void {
            $this->history[] = $query;
        }
        
        /**
         * Magic method to prevent cloning of the object.
         *
         * @return void
         */
        protected function __clone() {
        }
        
        /**
         * Sets the active connection name and returns the instance of the connection.
         *
         * @param string|null $connectionName The name of the connection.
         *
         * @return self The instance of the connection.
         * @throws \Exception
         */
        public static function i(string $connectionName = null): self {
            $connectionName = $connectionName ?? static::$activeConnection ?? config('database.connection', 'default');
            if (!$connectionName) {
                throw new MissingOrUnknownDatabaseConnectionName();
            }
            static::$activeConnection = $connectionName;
            if (!isset(static::$instances[$connectionName])) {
                static::$instances[$connectionName] = new static();
                static::$instances[$connectionName]->loadSchema();
            }
            
            return static::$instances[$connectionName];
        }
        
        /**
         * Loads the database schema.
         * Fetches table names from the database using "SHOW TABLES;" query,
         * and creates a new Table object for each table name.
         *
         * @return void
         * @throws \Exception
         */
        protected function loadSchema(): void {
            $tablenames = $this->query('SHOW TABLES;')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tablenames as $tablename) {
                $this->tables[$tablename] = new Table($tablename);
            }
        }
        
        /**
         * Checks if a table exists.
         *
         * @param string $name The name of the table to check.
         *
         * @return bool Returns true if the table exists, false otherwise.
         */
        public function hasTable(string $name): bool {
            return array_key_exists($name, $this->tables);
        }
        
        /**
         * Checks if a table has a specific column.
         *
         * @param string $column The name of the column to check.
         * @param string $table  The name of the table to check.
         *
         * @return bool Returns true if the table has the specified column, false otherwise.
         */
        public function hasColumn(string $column, string $table) {
            return $this->table($table)->hasColumn($column);
        }
        
        # ################################################################
        #
        # ################################################################
        
        /**
         * Executes the given query.
         *
         * @param string $query The SQL query to execute.
         *
         * @return \PDOStatement|null Returns the resulting \PDOStatement on success, or null on failure.
         */
        public function query(string $query): ?\PDOStatement {
            $this->log($query);
            
            return $this->pdo->query($query);
        }
        
        /**
         * Executes a query on the database.
         *
         * @param string $query The SQL query to execute.
         *
         * @return int|false Returns the number of affected rows by the query execution, or false on failure.
         */
        public function exec(string $query): false|int {
            $this->log($query);
            
            return $this->pdo->exec($query);
        }
        
        /**
         * Retrieves the ID of the last inserted row or sequence value.
         *
         * @return int Returns the ID of the last inserted row or sequence value as a string.
         *                     If the ID is not available, false is returned.
         */
        public function lastInsertId(): int {
            return (int)$this->pdo->lastInsertId();
        }
        
    }
