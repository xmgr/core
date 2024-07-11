<?php
    
    namespace Xmgr\Collections;
    
    use Traversable;
    
    /**
     * Class Collection
     *
     * The Collection class represents a collection of items.
     */
    class Collection implements \Countable, \IteratorAggregate, \ArrayAccess, \JsonSerializable {
        
        protected array $items = [];
        
        /**
         * Constructor for the class.
         *
         * @param mixed ...$items The items that will be assigned to the object.
         *
         * @return void
         */
        public function __construct(...$items) {
            $this->items = $items;
        }
        
        /**
         * Create a new instance from an array of data.
         *
         * @param array $data The array of data to be used for creating the instance.
         *
         * @return static The new instance created from the provided array data.
         */
        public static function fromArray(array $data): static {
            return new static(...array_values($data));
        }
        
        /**
         * Adds a value to the collection.
         *
         * @param mixed $value The value to be added.
         *
         * @return static The updated collection.
         */
        public function add(mixed $value): static {
            return $this->push($value);
        }
        
        /**
         * Pushes an item onto the end of the items array and returns the instance.
         *
         * @param mixed $data The item to be pushed onto the array.
         *
         * @return $this The instance with the new item pushed onto the array.
         */
        public function push(mixed $data): static {
            $this->items[] = $data;
            
            return $this;
        }
        
        /**
         * Checks if the given value exists in the array of items.
         *
         * @param mixed $value The value to check for existence.
         *
         * @return bool Returns true if the value exists in the array, false otherwise.
         */
        public function contains(mixed $value): bool {
            if (in_array($value, $this->items, true)) {
                return true;
            }
            
            return false;
        }
        
        /**
         * Get the item at the given index.
         *
         * @param int $index The index of the item to retrieve.
         *
         * @return mixed|null The item at the given index, or null if the index is out of bounds.
         */
        public function itemAt(int $index): mixed {
            return array_index($this->items, $index, null);
        }
        
        /**
         * Get the first item from the collection.
         *
         * @return mixed|null The first item from the collection, or null if the collection is empty.
         */
        public function first(): mixed {
            return ($this->items ? $this->items[array_key_first($this->items)] : null);
        }
        
        /**
         * Get the last item from the collection.
         *
         * @return mixed|null The last item from the collection, or null if the collection is empty.
         */
        public function last(): mixed {
            return ($this->items ? $this->items[array_key_last($this->items)] : null);
        }
        
        /**
         * Pluck a single column from the given array of items.
         *
         * @param string|int $key The key or property name to pluck from each item.
         *
         * @return Collection A new collection containing the plucked values.
         */
        public function pluck(string|int $key): self {
            $plucked = [];
            foreach ($this->items as $item) {
                if (is_array($item) || $item instanceof \ArrayAccess) {
                    if (arr_has($item, $key)) {
                        $plucked[] = $item[$key];
                    }
                } elseif (is_object($item)) {
                    if (isset($item->$key)) {
                        $plucked[] = $item->$key;
                    }
                }
            }
            
            return new static($plucked);
        }
        
        /**
         * Creates a new collection by applying a closure to each item in the current collection.
         *
         * @param \Closure $function The closure to be applied to each item.
         *                           The closure should accept two parameters - $item and $key, and return a boolean
         *                           value. If the closure returns true, the item will be included in the new
         *                           collection.
         *
         * @return static The new collection that contains the items for which the closure returned true.
         */
        public function collect(\Closure $function) {
            $collection = new static();
            foreach ($this->items as $key => $item) {
                if ($function($item, $key) === true) {
                    $collection->push($item);
                }
            }
            
            return $collection;
        }
        
        /**
         * Convert the items of the object to an array.
         *
         * @return array The items of the object converted to an array.
         */
        public function toArray(): array {
            return $this->items;
        }
        
        /**
         * Get all the items in the array.
         *
         * @return array Returns an array containing all the items in the array.
         */
        public function all(): array {
            return $this->items;
        }
        
        /**
         * Returns the number of items in the object.
         *
         * @return int The number of items in the object.
         */
        public function count(): int {
            return count($this->items);
        }
        
        /**
         * Get the iterator for the object.
         *
         * @return Traversable The iterator for the object.
         */
        public function getIterator(): Traversable {
            yield from $this->items;
        }
        
        /**
         * Checks if a given offset exists in the items array.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool Returns true if the offset exists, false otherwise.
         */
        public function offsetExists(mixed $offset): bool {
            return array_key_exists($offset, $this->items);
        }
        
        /**
         * Retrieves the value at the specified offset.
         *
         * @param mixed $offset The offset key. Must be a valid key for the $items array.
         *
         * @return mixed The value at the specified offset.
         */
        public function offsetGet(mixed $offset): mixed {
            return $this->items[$offset];
        }
        
        /**
         * Sets the value at a specified offset.
         *
         * @param mixed $offset The offset at which to set the value.
         * @param mixed $value  The value to set at the specified offset.
         *
         * @return void
         */
        public function offsetSet(mixed $offset, mixed $value): void {
            $this->items[$offset] = $value;
        }
        
        /**
         * Removes the value at the specified offset from the internal items array.
         *
         * @param mixed $offset The offset to be unset.
         *
         * @return void
         */
        public function offsetUnset(mixed $offset): void {
            unset($this->items[$offset]);
        }
        
        /**
         * Serializes the current object to JSON format.
         *
         * @return string|false The JSON representation of the object.
         */
        public function jsonSerialize(): string|false {
            return json_encode($this->items);
        }
        
    }
