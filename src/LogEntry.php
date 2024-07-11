<?php
    
    namespace Xmgr;
    
    /**
     * Class LogEntry
     *
     * Represents a log entry with a message, data and timestamp.
     */
    class LogEntry {
        
        protected string $message = '';
        protected mixed  $data    = null;
        protected float  $time;
        
        /**
         * Class constructor.
         *
         * Initializes the object with the provided message and data.
         *
         * @param string $message The message to be assigned to the object.
         * @param mixed  $data    The data to be assigned to the object.
         *
         * @return void
         */
        public function __construct(string $message, mixed $data) {
            $this->time    = microtime(true);
            $this->message = $message;
            $this->data    = $data;
        }
        
        /**
         * Convert the object to its string representation.
         *
         * This method returns an empty string.
         *
         * @return string The empty string.
         */
        public function toString() {
            return '';
        }
        
    }
    
