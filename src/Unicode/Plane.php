<?php
    
    namespace Xmgr\Unicode;
    
    /**
     * Class Plane
     *
     * Represents a Unicode plane.
     */
    class Plane {
        
        private int $index;
        
        
        /**
         * Get the plane index associated with a given Unicode codepoint.
         *
         * @param int $codepoint The Unicode codepoint to find the plane index for.
         *
         * @return int|string Returns the plane index if the codepoint belongs to a valid plane.
         *                  Otherwise, returns 0.
         */
        public static function planeIndexByCodepoint(int $codepoint): int|string {
            foreach (Unicode::$planes as $index => $plane) {
                if ($codepoint >= $plane[1] && $codepoint <= $plane[2]) {
                    return $index;
                }
            }
            
            return 0;
        }
        
        /**
         * __construct method.
         *
         * @param int $index The index parameter represents the value to initialize the instance.
         *                   It must be an integer indicating the index of the Unicode::$planes array
         *                   that will be used to set the $index property.
         *
         * @return void
         */
        private function __construct(int $index) {
            $index       = minmax($index, 0, count(Unicode::$planes) - 1);
            $this->index = $index;
        }
        
        /**
         * Retrieves the plane index by the given character.
         *
         * @param string $char The character for which the plane index is to be retrieved.
         *
         * @return static The instantiated object with the plane index set.
         */
        public static function byChar(string $char): static {
            return new static(static::planeIndexByCodepoint(mb_ord($char)));
        }
        
        /**
         * Returns an instance of the current class based on the given codepoint.
         *
         * @param int $codepoint The codepoint to determine the plane index for.
         *
         * @return static An instance of the current class.
         */
        public static function byCodepoint(int $codepoint): static {
            return new static(static::planeIndexByCodepoint($codepoint));
        }
        
        /**
         * Retrieve all instances of the class.
         *
         * @return array The array containing all instances of the class.
         */
        public static function all(): array {
            $planes = [];
            foreach (Unicode::$planes as $key => $data) {
                $planes[] = new static($key);
            }
            
            return $planes;
        }
        
        /**
         * Get the value from the specified key in the Unicode planes array.
         *
         * @param mixed      $key     The key to search for in the Unicode planes array.
         * @param mixed|null $default The default value to return if the specified key is not found.
         *
         * @return mixed The value associated with the specified key in the Unicode planes array. If the key is not
         *               found, the default value is returned.
         */
        protected function get(mixed $key, mixed $default = null): mixed {
            return arr(Unicode::$planes[$this->index], $key, $default);
        }
        
        /**
         * Returns the value of the index property.
         *
         * @return mixed The value of the index property.
         */
        public function index(): mixed {
            return $this->index;
        }
        
        /**
         * Returns the name of the object.
         *
         * @return string The name of the object.
         */
        public function name(): string {
            return (string)$this->get(0, '?Unknown?');
        }
        
        /**
         * Returns the integer value returned by the 'get' method, using the parameters '1' and '0'.
         *
         * @return int The integer value returned by the 'get' method using the parameters '1' and '0'.
         */
        public function start(): int {
            return (int)$this->get(1, 0);
        }
        
        /**
         * Returns the last value in the array.
         *
         * @return int The last value in the array.
         */
        public function end(): int {
            return (int)$this->get(2, 0);
        }
        
        /**
         * Returns an array of blocks that fall within the range of the current block.
         *
         * @return array An array of Block objects.
         * @throws \Exception
         */
        public function blocks() {
            $blocks = [];
            foreach (Block::all() as $block) {
                if ($block->start() >= $this->start() && $block->end() <= $this->end()) {
                    $blocks[] = $block;
                }
            }
            
            return $blocks;
        }
        
    }
