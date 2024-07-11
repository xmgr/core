<?php
    
    namespace Xmgr\Collections;
    
    use Xmgr\Record;
    
    /**
     * Class ModelCollection
     *
     * This class extends the Collection class and represents a collection of models.
     * It provides methods to manipulate and iterate over the collection.
     */
    class ModelCollection extends Collection {
        
        /**
         * Retrieves the records from the object.
         *
         * @return Record[] The records stored in the object.
         */
        public function records() {
            return $this->items;
        }
        
        /**
         * Retrieves an array of rows from the items.
         *
         * @param bool $id_key Whether to use the item's ID as the array key.
         *
         * @return array The array of rows. Each row is represented as an associative array, where the keys are the
         *               field names and the values are the field values.
         */
        public function rows(bool $id_key = true): array {
            $result = [];
            foreach ($this->items as $item) {
                if ($item instanceof Record) {
                    if ($id_key) {
                        $result[$item->id()] = $item->toArray();
                    } else {
                        $result[] = $item->toArray();
                    }
                }
            }
            
            return $result;
        }
        
        /**
         * Pluck a single column from the given array of items.
         *
         * @param string|int $key The key or property name to pluck from each item.
         *
         * @return Collection A new collection containing the plucked values.
         */
        public function pluck(int|string $key): Collection {
            $result = new Collection();
            foreach ($this->items as $item) {
                if ($item instanceof Record) {
                    $result->push($item->get($key));
                }
            }
            
            return $result;
        }
        
    }
