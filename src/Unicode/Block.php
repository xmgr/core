<?php
    
    namespace Xmgr\Unicode;
    
    use Exception;
    
    /**
     * Get the block index by code point.
     *
     * @param int $codepoint The code point to find the block index for.
     *
     * @return int The block index of the given code point or 0 if not found.
     * @throws Exception if there is an error while retrieving the block index.
     */
    class Block {
        
        private int $index;
        
        /**
         * Get the block index by code point.
         *
         * @param int $codepoint The code point to find the block index for.
         *
         * @return int The block index of the given code point or 0 if not found.
         * @throws Exception
         */
        public static function blockIndexByCodepoint(int $codepoint): int {
            foreach (Unicode::blocks() as $index => $block) {
                if ($codepoint >= $block[0] && $codepoint <= $block[1]) {
                    return $index;
                }
            }
            
            return 0;
        }
        
        /**
         * Constructor method.
         *
         * @param int $index The index value.
         *
         * @throws Exception
         */
        private function __construct(int $index) {
            $index       = minmax($index, 0, count(Unicode::blocks()) - 1);
            $this->index = $index;
        }
        
        /**
         * Returns a new instance of the called class by the given char.
         *
         * @param string $char The character to create the instance with.
         *
         * @return static The new instance of the called class.
         * @throws Exception If an error occurs while creating the instance.
         */
        public static function byChar(string $char): static {
            return new static(static::blockIndexByCodepoint(mb_ord($char)));
        }
        
        /**
         * Returns an instance of the class, representing a block index,
         * for the given Unicode codepoint.
         *
         * @param int $codepoint The Unicode codepoint.
         *
         * @return static An instance of the class representing the block index.
         * @throws Exception If there is an error while creating the instance.
         */
        public static function byCodepoint(int $codepoint): static {
            return new static(static::blockIndexByCodepoint($codepoint));
        }
        
        /**
         * Retrieve all blocks.
         *
         * This method returns an array of all blocks.
         *
         * @return array|static[]  An array containing all blocks.
         * @throws Exception
         */
        public static function all(): array {
            $blocks = [];
            foreach (Unicode::blocks() as $key => $data) {
                $blocks[] = new static($key);
            }
            
            return $blocks;
        }
        
        /**
         * Retrieves the value associated with the given key from the Unicode blocks array.
         *
         * @param mixed      $key     The key whose associated value is to be retrieved.
         * @param mixed|null $default OPTIONAL. The default value to return if the key does not exist. Default is null.
         *
         * @return mixed The value associated with the given key, or the default value if the key does not exist.
         * @throws Exception
         */
        protected function get(mixed $key, mixed $default = null): mixed {
            return arr(Unicode::blocks()[$this->index], $key, $default);
        }
        
        /**
         * Returns the index.
         *
         * @return mixed The index value.
         */
        public function index(): mixed {
            return $this->index;
        }
        
        /**
         * Retrieves the name.
         *
         * @return string The name.
         * @throws Exception
         */
        public function name(): string {
            return (string)$this->get(2, '?Unknown?');
        }
        
        /**
         * Returns the starting value.
         *
         * This method retrieves the starting value from the data source.
         *
         * @return int The starting value.
         * @throws Exception if the starting value cannot be retrieved.
         */
        public function start(): int {
            return (int)$this->get(0, 0);
        }
        
        /**
         * Returns the end value.
         *
         * @return int The end value.
         * @throws Exception if the value cannot be retrieved.
         */
        public function end(): int {
            return (int)$this->get(1, 0);
        }
        
        /**
         * Calculates the length of a given range.
         *
         * @return int The length of the range.
         * @throws Exception
         */
        public function length(): int {
            return $this->end() - $this->start() + 1;
        }
        
        /**
         * Retrieve the plane of a given codepoint.
         *
         * This method returns the plane of the codepoint starting from the current instance.
         *
         * @return Plane  The plane of the codepoint.
         * @throws Exception
         */
        public function plane(): Plane {
            return Plane::byCodepoint($this->start());
        }
        
    }
