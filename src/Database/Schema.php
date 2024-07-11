<?php
    
    namespace Xmgr\Database;
    
    
    use Xmgr\Database\Schema\Blueprint;
    
    /**
     * Class Schema
     *
     * The Schema class is used to create a new table in the database.
     */
    class Schema {
        
        /**
         * Create a new table in the database.
         *
         * @param string   $table_name The name of the table to create.
         * @param \Closure $function   The Closure function that defines the table structure using the Blueprint class.
         *
         * @return void
         */
        public static function create(string $table_name, \Closure $function): void {
            
            $table = new Blueprint($table_name, true);
            $function($table);
            
            $sql     = 'CREATE TABLE ' . dbkey($table_name) . ' (';
            $columns = [];
            foreach ($table->columns as $column) {
                $stack   = [];
                $stack[] = dbkey($column->name());
                $stack[] = $column->typeToString();
                if ($column->unsigned) {
                    $stack[] = 'UNSIGNED';
                }
                if ($column->zerofill) {
                    $stack[] = 'ZEROFILL';
                }
                $stack[] = $column->nullable ? 'NOT NULL' : 'NULL';
                if ($column->default !== false) {
                    $stack[] = ' DEFAULT ' . $column->default;
                }
                if ($column->autoIncrement) {
                    $stack[] = 'AUTO_INCREMENT';
                }
                if ($column->collation) {
                    $stack[] = 'COLLATE ' . dbvalue($column->collation);
                }
                $stack[]   = 'COMMENT ' . dbvalue($column->comment);
                $columns[] = implode(' ', $stack);
            }
            
            $sql .= implode(', ', $columns);
            
            $indexes = [];
            if ($table->primary) {
                $primaries = array_unique($table->primary);
                foreach ($primaries as $p) {
                    $indexes[] = 'PRIMARY KEY (' . $p . ')';
                }
            }
            if ($table->uniques) {
                foreach ($table->uniques as $uname => $p) {
                    $indexes[] = 'UNIQUE KEY ' . dbkey($uname) . ' (' . $p . ')';
                }
            }
            if ($table->indexes) {
                foreach ($table->indexes as $uname => $p) {
                    $indexes[] = 'INDEX ' . dbkey($uname) . ' (' . $p . ')';
                }
            }
            
            $sql .= ($indexes ? ' ' . implode(', ', $indexes) : '');
            
            $sql .= ($table->foreigns ? ' ' . implode(', ', $table->foreigns) : '');
            
            $sql                    .= ')';
            $data                   = [];
            $data['COMMENT']        = dbvalue($table->comment);
            $data['COLLATE']        = dbvalue($table->collation);
            $data['ENGINE']         = dbvalue($table->engine);
            $data['CHECKSUM']       = ($table->checksum ? 1 : 0);
            $data['AUTO_INCREMENT'] = $table->auto_increment;
            foreach ($table->adddata as $key => $value) {
                $data[$key] = $value;
            }
            $lines = [];
            foreach ($data as $key => $value) {
                $lines[] = $key . '=' . $value;
            }
            
            $sql .= ($lines ? ' ' . implode(' ', $lines) : '') . ';';
            
            dump($sql);
            
        }
        
    }
