<?php
    
    namespace Xmgr\Database;
    
    /**
     * The Table class represents a database table.
     */
    class Table {
        
        protected string $name    = '';
        protected array  $columns = [];
        
        /**
         * Constructor for the class.
         *
         * @param string $name The name of the database table.
         *
         * @return void
         * @throws \Exception
         */
        public function __construct(string $name) {
            if ($name) {
                $this->name = $name;
                $columns    = db()->query('SHOW COLUMNS FROM ' . dbkey($this->name))->fetchAll();
                foreach ($columns as $data) {
                    if (isset($data['Field'])) {
                        $this->columns[$data['Field']] = new Schema\Column($data);
                    }
                }
            }
        }
        
        /**
         * Check if the database table exists.
         *
         * @return bool True if the database table exists, false otherwise.
         */
        public function exists(): bool {
            return (bool)$this->name;
        }
        
        /**
         * Check if the database table has a column named 'id'.
         *
         * @return bool Returns true if the table has a column named 'id', otherwise returns false.
         */
        public function hasId(): bool {
            return $this->hasColumn('id');
        }
        
        /**
         * Check if a column exists in the database table.
         *
         * @param string $name The name of the column to check.
         *
         * @return bool Returns TRUE if the column exists, FALSE otherwise.
         */
        public function hasColumn(string $name): bool {
            return array_key_exists($name, $this->columns);
        }
        
    }
