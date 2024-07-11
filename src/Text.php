<?php
    
    namespace Xmgr;
    
    use Xmgr\Interfaces\Stringable;
    
    /**
     * Class Text
     *
     * Represents a text object that can be manipulated.
     */
    class Text implements Stringable, \Stringable, \ArrayAccess {
        
        protected string $text = '';
        
        /**
         * Constructor method.
         *
         * @param mixed $text (Optional) The text to be set. Defaults to an empty string.
         */
        public function __construct(mixed $text = '') {
            $this->set($text);
        }
        
        /**
         * Retrieves the value of the text property.
         *
         * @return string The value of the text property.
         */
        public function get(): string {
            return $this->text;
        }
        
        /**
         * Update the value of the "text" property.
         *
         * @param mixed $text The new value for the "text" property.
         *
         * @return $this
         */
        public function set(mixed $text): static {
            $this->text = Str::from($text);
            
            return $this;
        }
        
        /**
         * Appends a given text to the existing text.
         *
         * @param string $text The text to be appended.
         *
         * @return $this
         */
        public function append(string $text): static {
            return $this->set($this->text . $text);
        }
        
        /**
         * Prepend method.
         *
         * @param string $text The text to be prepended.
         *
         * @return static The current instance of the class.
         */
        public function prepend(string $text): static {
            return $this->set($text . $this->text);
        }
        
        # --
        
        /**
         * Converts the object to its string representation.
         *
         * This method calls the `get()` method to retrieve the value of the text property and returns it.
         *
         * @return string The string representation of the object.
         */
        public function toString(): string {
            return $this->get();
        }
        
        /**
         * Returns a string representation of the current object.
         *
         * @return string The string representation of the object.
         */
        public function __toString(): string {
            return $this->toString();
        }
        
        # -- ArrayAccess stubs
        
        /**
         * Checks if a specific offset exists in the text property.
         *
         * @param mixed $offset The offset to check.
         *
         * @return bool Indicates whether the offset exists in the text property.
         */
        public function offsetExists(mixed $offset): bool {
            return isset($this->text[$offset]);
        }
        
        /**
         * Returns the value at the specified offset.
         *
         * @param mixed $offset The offset to retrieve the value from.
         *
         * @return string The value at the specified offset.
         */
        public function offsetGet(mixed $offset): string {
            return $this->text[$offset];
        }
        
        /**
         * Sets the value at the specified offset.
         *
         * @param mixed $offset The offset to set the value at.
         * @param mixed $value  The value to be set.
         *
         * @return void
         */
        public function offsetSet(mixed $offset, mixed $value): void {
            $this->text[$offset] = $value;
        }
        
        /**
         * Unsets the value at a specified offset in the 'text' array.
         *
         * @param mixed $offset The key or index of the element to unset.
         *
         * @return void
         */
        public function offsetUnset(mixed $offset): void {
            # @todo implement
        }
        
        
    }
