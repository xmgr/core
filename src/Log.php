<?php
    
    namespace Xmgr;
    
    use Xmgr\Filesystem\File;
    
    /**
     * Class Log
     *
     * This class provides functionality to write messages and optional data.
     */
    class Log {
        
        public static array $runtimeMessages = [];
        
        /**
         * Writes given message and optional data to a file.
         *
         * @param string     $file    The path to the file where the message and data will be written.
         * @param string     $message The message to be written to the file (optional).
         * @param mixed|null $data    The data to be written to the file (optional).
         *
         * @return void
         */
        public static function toFile(string $file, string $message = '', mixed $data = null) {
            $content = '[' . date('c') . '] ' . $message . ($data === null ? '' : "\n" . print_r($data, true)) . "\n";
            File::append($file, $content);
        }
        
        /**
         * Adds a runtime message to the static runtimeMessages array.
         *
         * @param string $message (optional) The message to add to the runtimeMessages array. Default is an empty
         *                        string.
         *
         * @return void
         */
        public static function runtime(string $message = ''): void {
            static::$runtimeMessages[] = $message;
        }
        
    }
    
