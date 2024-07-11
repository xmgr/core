<?php
    
    namespace Xmgr;
    
    use Xmgr\Collections\Collection;
    use Xmgr\Database\AttributeCast;
    use Xmgr\Database\JsonCast;
    use Xmgr\Database\QueryBuilder;
    use Xmgr\Interfaces\Arrayable;
    use Xmgr\Trait\Jsonable;
    
    /**
     * Class Record
     *
     * The Record class provides an interface for interacting with database tables.
     *
     * @property int id Primary id column
     */
    class Record implements \ArrayAccess, Arrayable {
        
        use Jsonable;
        
        protected static string $table   = '';
        protected static string $primary = 'id';
        
        protected array $initialData = [];
        protected array $data        = [];
        protected array $modified    = [];
        
        public array $meta = [];
        
        protected static string $collection = Collection::class;
        
        protected mixed $lastExecResult = null;
        
        protected static ?string $connection = null;
        
        /**
         * @var array|string[]|AttributeCast[]
         */
        protected array $casts = [
            'custom_field' => JsonCast::class
        ];
        
        /**
         * Constructor for the class.
         *
         * @param int|array $data The data used to initialize the class. Optional.
         */
        public function __construct(int|array $data = []) {
            if ($this->tableExists()) {
                if ($data) {
                    if (is_int($data)) {
                        $this->setup(static::where($data)->firstToArray());
                    }
                    if (is_array($data)) {
                        $this->setup($data);
                    }
                }
            }
            dispatch(new Event('db.record.init', $this->data, $this));
            $this->init();
        }
        
        /**
         * Initialize the object.
         * This method should be implemented in the child class.
         *
         * @return void
         *
         */
        protected function init() {
            /* Implement in child class */
        }
        
        /**
         * Get the database instance.
         *
         * @return Database The database instance.
         */
        private static function db(): Database {
            return db(static::$connection);
        }
        
        /**
         * Touches the data by adding it to the modified data based on the given keys.
         *
         * This may be useful if you pass an array to the constructor and want to mark the data as modified.
         *
         * @param bool|array $keys The keys to determine which data to touch. If true, touch all data. If array, touch
         *                         only the data with matching keys.
         *
         * @return void
         */
        public function touch(bool|array $keys): void {
            foreach ($this->data as $key => $value) {
                if ($keys === true || (is_array($keys) && in_array($key, $keys))) {
                    $this->modified[$key] = $value;
                }
            }
        }
        
        /**
         * Check if the table exists in the database.
         *
         * @return bool Returns true if the table exists, false otherwise.
         */
        private function tableExists(): bool {
            return static::db()->hasTable(static::table());
        }
        
        /**
         * This method returns the ID of the record.
         *
         * @return int The ID of the record.
         */
        public function id(): int {
            return (int)$this->get('id', 0);
        }
        
        /**
         * This method checks if the record exists in the database.
         *
         * @return bool Returns true if the record exists, and false otherwise.
         */
        public function exists(): bool {
            return $this->id() > 0;
        }
        
        /**
         * Set up the initial data and internal properties.
         *
         * @param mixed $data The initial data to be set up.
         *
         * @return void
         */
        protected function setup(mixed $data): void {
            $this->initialData = $data;
            $this->data        = $this->initialData;
            $this->modified    = [];
        }
        
        /**
         * Reload the object data from the database.
         *
         * @param ?int $id The identifier of the object in the database.
         *
         * @return void
         */
        private function reload(?int $id = null): void {
            $id = abs($id ?? $this->id());
            if ($id) {
                $this->__construct($id);
            }
        }
        
        /**
         * Reverts the current data to its initial state.
         *
         * @param array|null $keys
         *
         * @return void
         */
        public function revert(?array $keys = null): void {
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $this->initialData)) {
                        $this->data[$key] = $this->original($key);
                    }
                }
            } else {
                $this->data = $this->initialData;
            }
        }
        
        /**
         * Get the table name for the current class.
         *
         * @return string The table name for the current class.
         */
        public static function table(): string {
            return static::$table;
        }
        
        /**
         * Get the table name for the current class.
         *
         * @return string The table name for the current class.
         */
        public static function safeTable(): string {
            return dbkey(static::$table);
        }
        
        /**
         * Find a record by its value.
         *
         * @param int $id
         *
         * @return static The found record as an instance of the current class.
         * @throws \Exception
         */
        public static function find(int $id): static {
            return static::sql($id)->first();
        }
        
        /**
         * Get the first record from the database table.
         *
         * @param mixed ...$where The conditions to filter the records.
         *
         * @return static The first record from the database table, or null if no record found.
         * @throws \Exception
         */
        public static function first(...$where): static {
            return static::sql(...$where)->first();
        }
        
        /**
         * Retrieve data from the database based on a given condition.
         *
         * @param mixed ...$where The condition(s) to filter the database query.
         *
         * @return QueryBuilder The fetched data from the database, loaded into instances of the current
         *                                     class.
         */
        public static function where(...$where): QueryBuilder {
            return static::sql(...$where);
        }
        
        /**
         * This method returns all the records from the database.
         *
         * @param mixed ...$where
         *
         * @return QueryBuilder An array of records from the database.
         */
        public static function all(...$where): QueryBuilder {
            return static::sql(...$where);
        }
        
        /**
         * This method collects and returns records from the database based on the given conditions.
         *
         * @param mixed $where The conditions to apply while collecting records.
         *
         * @return Collection A collection of records from the database.
         */
        public static function collect(...$where): Collection {
            return static::sql(...$where)->collect(static::$collection);
        }
        
        /**
         * This method returns an instance of the Database\QueryBuilder class.
         *
         * @return QueryBuilder The instance of the Database\QueryBuilder class.
         */
        public static function sql(...$where): QueryBuilder {
            return sql(static::table(), ...$where)
                ->useClass(static::class)
                ->useCollection(static::$collection)
                ->withConnection(self::$connection);
        }
        
        /**
         * Check if the given key exists in the data array.
         *
         * @param string $key The key to search for in the data array.
         *
         * @return bool True if the key exists in the data array, false otherwise.
         */
        public function has(string $key): bool {
            return array_key_exists($key, $this->data);
        }
        
        /**
         * Get the value of a property from the data array.
         *
         * @param string $key The name of the property to retrieve.
         *
         * @return mixed|null The value of the property if found, or null if not found.
         */
        public function get(string $key, $default = null): mixed {
            $getter = 'get__' . $key;
            if (method_exists($this, $getter)) {
                return $this->{$getter}($key, $default);
            } else {
                return data_get($this->data, $key, $default);
            }
        }
        
        /**
         * Magic getter method.
         *
         * @param mixed $property The name of the property to retrieve.
         *
         * @return mixed The value of the specified property.
         */
        public function __get(mixed $property): mixed {
            return $this->get($property);
        }
        
        /**
         * Get the raw value of a key from the data array.
         *
         * @param string     $key     The key to get the value for.
         * @param mixed|null $default The default value to return if the key is not found. Optional.
         *
         * @return mixed The raw value of the key if found, otherwise the default value or null if no default value is
         *               specified.
         */
        public function get_raw(string $key, mixed $default = null) {
            return arr($this->data, $key, $default);
        }
        
        /**
         * Convert the value associated with the given key to integer type and return it.
         *
         * @param string $key     The key to retrieve the value from.
         * @param int    $default The default value to return if the key is not found. Optional.
         *
         * @return int The value associated with the given key, converted to integer.
         */
        public function int(string $key, int $default): int {
            return (int)$this->get($key, $default);
        }
        
        /**
         * Get the value of the specified key as a float.
         *
         * @param string $key     The key of the value to retrieve.
         * @param float  $default The default value to return if the key does not exist. Optional.
         *
         * @return float The value of the specified key as a float.
         */
        public function float(string $key, float $default): float {
            return (float)$this->get($key, $default);
        }
        
        /**
         * Retrieve a string value from the configuration using a specified key.
         *
         * @param string $key     The key to retrieve the string value.
         * @param string $default The default value to return if the key is not found. Optional. Default is an empty
         *                        string.
         *
         * @return string The string value associated with the key, or the default value if the key is not found.
         */
        public function string(string $key, string $default): string {
            return (string)$this->get($key, $default);
        }
        
        /**
         * This method sets the value of a specific key in the data record.
         *
         * @param string $key   The key to set the value for.
         * @param mixed  $value The value to set for the key.
         *
         * @return void
         */
        public function set(string $key, mixed $value): void {
            if ($key === static::$primary) {
                # It's not allowed to set a primary key's value
                return;
            }
            $setter = 'set__' . $key;
            if (method_exists($this, $setter)) {
                $value = $this->{$setter}($value, $key);
            }
            $this->modified[$key] = $value;
            $this->data[$key]     = $value;
        }
        
        /**
         * Set the value of a specific property.
         *
         * @param string $property The name of the property to set the value for.
         * @param mixed  $value    The value to set for the property.
         *
         * @return void
         */
        public function __set(string $property, mixed $value) {
            $this->set($property, $value);
        }
        
        /**
         * Set a raw value for a given key.
         *
         * @param string $key   The key for the value to be set.
         * @param mixed  $value The value to be set.
         *
         * @return void
         */
        public function set_raw(string $key, mixed $value): void {
            $this->data[$key]     = $value;
            $this->modified[$key] = $value;
        }
        
        /**
         * Get the original value of a property from the initial data.
         *
         * @param string     $property The name of the property.
         * @param mixed|null $default  The value to return if the property is not found. Optional.
         *
         * @return mixed The original value of the property, or the default value if the property is not found.
         */
        public function original(string $property, mixed $default = null): mixed {
            return data_get($this->initialData, $property, $default);
        }
        
        /**
         * Fill the model with an array of attributes.
         *
         * @param array $data The data array containing the attributes to fill the model with.
         *
         * @return self The filled model instance.
         */
        public function fill(array $data): static {
            foreach ($data as $key => $value) {
                $this->set($key, $value);
            }
            
            return $this;
        }
        
        # Hooks
        
        /**
         * Called before creating a new object.
         *
         * This method should be implemented in the child class to perform any necessary actions before a new object is
         * created. It is a hook that is triggered automatically before the create process is started.
         *
         * @return void
         */
        protected function beforeCreate() {
            /* Implement in child class */
        }
        
        /**
         * Called after the object is created.
         *
         * This method should be implemented in the child class to perform any necessary actions after the object is
         * created. It is a hook that is triggered automatically after the create process is completed.
         *
         * @return void
         */
        protected function afterCreate() {
            /* Implement in child class */
        }
        
        /**
         * Called before the object is updated.
         *
         * This method should be implemented in the child class to perform any necessary actions before the object is
         * updated. It is a hook that is triggered automatically before the update process begins.
         *
         * @return void
         */
        protected function beforeUpdate() {
            /* Implement in child class */
        }
        
        /**
         * Called after the object is updated.
         *
         * This method should be implemented in the child class to perform any necessary actions after the object is
         * updated. It is a hook that is triggered automatically after the update process is completed.
         *
         * @return void
         */
        protected function afterUpdate() {
            /* Implement in child class */
        }
        
        /**
         * Called before the object is saved.
         *
         * This method should be implemented in the child class to perform any necessary actions before the object is
         * saved. It is a hook that is triggered automatically before the save process is initiated.
         *
         * @return void
         */
        protected function beforeSave() {
            /* Implement in child class */
        }
        
        /**
         * Called after the object is saved.
         *
         * This method should be implemented in the child class to perform any necessary actions after the object is
         * saved. It is a hook that is triggered automatically after the save process is completed.
         *
         * @return void
         */
        protected function afterSave() {
            /* Implement in child class */
        }
        
        /**
         * This method is called before the deletion of an object and should be implemented in the child class.
         *
         * @return void
         */
        protected function beforeDelete() {
            /* Implement in child class */
        }
        
        /**
         * Implement in child class to perform actions after a record is deleted.
         *
         * @return void
         */
        protected function afterDelete() {
            /* Implement in child class */
        }
        
        # CRUD
        
        /**
         * Prepare the data for saving the model.
         *
         * @param array $data The additional data to merge with the model data. Optional.
         *
         * @return array The prepared data for saving the model.
         */
        protected function prepareData(array $data = []): array {
            $this->fill($data);
            unset($this->modified[static::$primary]);
            
            return $this->modified;
        }
        
        /**
         * Apply casts to the given data array.
         *
         * @param array  $data The data array to apply casts to.
         * @param string $do   The operation to perform ('set' or 'get').
         *
         * @return array The modified data array with applied casts.
         */
        protected function applyCasts(array $data, string $do): array {
            return $data; // Disable for now
            /*
            foreach ($data as $key => &$value) {
                if (array_key_exists($key, $this->casts)) {
                    $castClass  = $this->casts[$key];
                    $castObject = new $castClass();
                    if ($do === 'set') {
                        $value = $castObject->set($value);
                    }
                    if ($do === 'get') {
                        $value = $castObject->get($value);
                    }
                }
            }
            
            return $data;
            */
        }
        
        /**
         * Save the model to the database.
         *
         * This method saves the current instance of the model to the database. It checks whether the table exists and
         * then determines if the model should be created or updated based on whether it already exists in the
         * database.
         *
         * @return bool|int Returns true on successful save, otherwise false.
         * @throws \Exception
         */
        public function save(array $data = []): bool|int {
            return $this->exists() ? $this->update($data) : $this->create($data);
        }
        
        /**
         * Create a new record in the database.
         *
         * @param array $data The data to be inserted into the database. Optional.
         *
         * @return int|false The number of affected rows, or 0 if no data was provided.
         * @throws \Exception
         */
        public function create(array $data = []): int|false {
            if (!$this->tableExists()) {
                return false;
            }
            $data = $this->prepareData($data);
            
            $this->beforeSave();
            $this->beforeCreate();
            dispatch(new Event('db.record.save.before', $data, $this));
            dispatch(new Event('db.record.create.before', $data, $this));
            $this->lastExecResult = static::sql()->insert($data)->exec();
            $lid                  = 0;
            if ($this->lastExecResult !== false && $this->lastExecResult !== 0) {
                $lid = static::db()->lastInsertId();
            }
            $this->afterCreate();
            $this->afterSave();
            dispatch(new Event('db.record.create.after', $this, $this, $this->lastExecResult, $lid));
            dispatch(new Event('db.record.save.after', $this, $this, $this->lastExecResult, $lid));
            
            if ($lid) {
                $this->reload($lid);
            }
            
            return $this->lastExecResult;
        }
        
        /**
         * Create a new record in the database.
         *
         * @param array $data The data to be inserted into the database. Optional.
         *
         * @return int|false The number of affected rows, or 0 if no data was provided.
         * @throws \Exception
         */
        public function update(array $data = []): int|false {
            if (!$this->tableExists() || !$this->exists()) {
                return false;
            }
            $id   = $this->id();
            $data = $this->prepareData($data);
            
            $this->beforeSave();
            $this->beforeUpdate();
            dispatch(new Event('db.record.save.before', $data, $this, null, $this->id()));
            dispatch(new Event('db.record.update.before', $data, $this, null, $this->id()));
            $this->lastExecResult = ($data ? static::sql()->update($data, $id)->exec() : 0);
            $this->afterUpdate();
            $this->afterSave();
            dispatch(new Event('db.record.update.after', $data, $this, $this->lastExecResult, $this->id()));
            dispatch(new Event('db.record.save.after', $data, $this, $this->lastExecResult, $this->id()));
            
            if ($this->lastExecResult !== false && $this->lastExecResult !== 0) {
                $this->reload($id);
            }
            
            return $this->lastExecResult;
        }
        
        /**
         * Delete the current record from the database table.
         *
         * @return bool True if the record was deleted successfully, otherwise false.
         * @throws \Exception
         */
        public function delete() {
            $this->beforeDelete();
            dispatch(new Event('db.record.delete.before', $this, null, $this->id()));
            $result = ($this->exists() ? static::sql()->delete($this->id()) : 0);
            dispatch(new Event('db.record.delete.after', $this, null, $this->id()));
            $this->afterDelete();
            
            return $result ? $result->exec() : $result;
        }
        
        /**
         * Check if the last operation failed.
         *
         * @return bool True if the last operation failed, otherwise false.
         */
        public function lastOperationFailed(): bool {
            return $this->lastExecResult === false;
        }
        
        /**
         * Check if the last operation succeeded.
         *
         * @return bool Returns true if the last operation succeeded, otherwise false.
         */
        public function lastOperationSucceeded(): bool {
            return $this->lastExecResult !== false;
        }
        
        /**
         * Checks if the last operation had no changes.
         *
         * @return bool Returns true if the last operation had no changes, false otherwise.
         */
        public function lastOperationHadNoChanges(): bool {
            return $this->lastExecResult === 0;
        }
        
        /**
         * Check if the model has been saved to the database.
         *
         * @return bool True if the model has been saved, false otherwise.
         */
        public function hasBeenSaved(): bool {
            return is_int($this->lastExecResult) && $this->lastExecResult;
        }
        
        /**
         * Get the primary value for the current instance.
         *
         * @return mixed The primary value for the current instance.
         */
        public function primaryValue($default = null): mixed {
            return $this->get(static::$primary, $default);
        }
        
        /**
         * This method returns an instance of the SQL class for the given table.
         *
         * @param string $table The name of the database table.
         *
         * @return QueryBuilder An instance of the SQL class for performing queries on the specified table.
         */
        public static function factory(string $table): QueryBuilder {
            return static::sql($table)->useClass(static::class)->useCollection(static::$collection);
        }
        
        /**
         * Construct an array of instances of the current class using the given data array.
         *
         * @param array|mixed $data The data array containing instances of the current class.
         *
         * @return array An array of instances of the current class.
         */
        public static function construct(mixed $data): array {
            if (!$data || !is_array($data)) {
                return [];
            }
            $result = [];
            foreach ($data as $d) {
                if (is_array($d)) {
                    $result[] = new static($d);
                }
            }
            
            return $result;
        }
        
        /**
         * Define a one-to-many relationship with another model.
         *
         * @param string|self $model  The class name of the related model.
         * @param string      $column The foreign key column name to use for the relationship. Optional.
         *
         * @return Collection The collection of related models.
         * @throws \Exception
         * @todo implement
         *
         */
        public function hasMany(string|self $model, string $column = ''): Collection {
            return self::where([$column => $this->id])->collect();
        }
        
        /**
         * Define a "belongs to" relationship between the current model and another model.
         *
         * @param string|self $model  The class name or instance of the model to which the current model belongs.
         * @param string      $column The column name to use for the relationship (default: "id").
         *
         * @return static An instance of the related model that matches the given column value.
         * @throws \Exception
         * @todo implement
         *
         */
        public function belongsTo(string|self $model, string $column = 'id'): Record {
            return self::where(['id' => $this->get($column)])->useClass(static::class)->first();
        }
        
        /**
         * Get a random value from a specific attribute in the table for the current class.
         *
         * @param string $attribute The attribute from which to retrieve a random value.
         *
         * @return mixed The random value from the specified attribute in the table for the current class.
         * @throws \Exception
         */
        public static function randomAttribute(string $attribute): mixed {
            return static::sql(static::table())->randomly(1)->fetchColumn($attribute);
        }
        
        /**
         * This method returns a randomly selected record from the database that matches the given conditions.
         *
         * @param mixed ...$where The conditions for selecting the record(s).
         *
         * @return static The randomly selected record.
         */
        public static function random(...$where): static {
            return static::where(...$where)->randomly()->first();
        }
        
        /**
         * This method returns randomly selected records from the database based on the given conditions.
         *
         * @param int|null $limit
         * @param mixed    ...$where Optional parameters to filter the records.
         *
         * @return Collection|array|Record An array of randomly selected records from the database.
         */
        public static function some(?int $limit, ...$where): Collection|array|static {
            return static::where(...$where)->limit($limit)->randomly()->get();
        }
        
        /**
         * Truncate the table associated with the current class.
         *
         * @return false|int
         * @throws \Exception
         */
        public static function truncate(): false|int {
            return static::db()->exec('TRUNCATE TABLE ' . static::safeTable());
        }
        
        /**
         * Add meta data to the current object.
         *
         * @param string     $label The label for the meta data.
         * @param mixed|null $data  The data to be associated with the meta label. Optional.
         *
         * @return void
         */
        public function addMeta(string $label, mixed $data = null): void {
            $this->meta[] = ['label' => $label, 'data' => $data];
        }
        
        /**
         * Convert the current object to an array representation.
         *
         * @return array The array representation of the object.
         */
        public function toArray(): array {
            return $this->data;
        }
        
        /**
         * @param Collection|null $collection
         *
         * @return mixed|Collection
         */
        /**
         * @param Collection|null $collection
         *
         * @return mixed|Collection
         */
        public function toCollection(Collection $collection = null) {
            $collection = $collection ?? static::$collection;
            
            return new $collection($this);
        }
        
        /**
         * Creates a clone of the object.
         *
         * @return static A clone of the object.
         */
        public function clone(): static {
            return clone $this;
        }
        
        # ArrayAccess stubs
        
        /**
         * Checks if a given offset exists in the data array.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool Returns true if the offset exists, false otherwise.
         */
        public function offsetExists(mixed $offset): bool {
            return array_key_exists($offset, $this->data);
        }
        
        /**
         * This method retrieves the value at a specific offset.
         *
         * @param mixed $offset The offset to retrieve the value from.
         *
         * @return mixed The value at the specified offset.
         */
        public function offsetGet(mixed $offset): mixed {
            return $this->get($offset . null);
        }
        
        /**
         * Set the value at the specified offset.
         *
         * @param mixed $offset The offset at which to set the value.
         * @param mixed $value  The value to set.
         *
         * @return void
         */
        public function offsetSet(mixed $offset, mixed $value): void {
            $this->set($offset, $value);
        }
        
        /**
         * This method unsets a value at the specified offset in the data array.
         *
         * @param mixed $offset The offset to unset.
         *
         * @return void
         */
        public function offsetUnset(mixed $offset): void {
            unset($this->data[$offset]);
        }
        
    }
    
