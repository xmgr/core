<?php
    
    namespace Xmgr\Filesystem;
    
    /**
     * Sets the file position indicator for the given file handle.
     *
     * @param resource $handle The file handle to operate on.
     * @param int      $offset The offset.
     * @param int      $whence The optional whence value: SEEK_SET, SEEK_CUR, or SEEK_END.
     *
     * @return int 0 on success, -1 on failure.
     */
    class File {
        
        /**
         * Checks if a file exists
         *
         * @param string $file The file path to check
         *
         * @return bool True if the file exists, false otherwise
         */
        public static function exists(string $file): bool {
            return is_file($file);
        }
        
        /**
         * Touches a file if it does not exist
         *
         * @param string $file The file to be touched
         *
         * @return void
         */
        public static function touch(string $file) {
            if (!is_file($file)) {
                @touch($file);
            }
        }
        
        /**
         * Checks if a file is both existing and writable.
         *
         * @param string $file The absolute path of the file to be checked.
         *
         * @return bool Returns true if the file exists and is writable, false otherwise.
         */
        public static function writable(string $file) {
            return is_file($file) && is_writable($file);
        }
        
        /**
         * Read file contents
         *
         * @param string   $file     The full path to the file
         * @param int|null $offset   Offset (in bytes) to start reading the file's content.
         *                           Pass negative numbers to move the offset from the end of the file, e.g.
         *                           if you pass -5 as offset, it will read the last 5 bytes from the file.
         * @param int|null $length   Pass NULL to read till the end of the file or pass an integer
         *                           to specify the length of bytes you want to read (starting from the given offset)
         * @param bool     $create   Create the file if it doesn't exist
         *
         * @return string
         */
        public static function read(string $file, ?int $offset = 0, int $length = null, bool $create = false): string {
            if ($create && !is_file($file)) {
                touch($file);
                
                return '';
            }
            if (is_file($file) && is_readable($file)) {
                if ($length === null) {
                    $result = file_get_contents($file, false, null, (int)$offset);
                } else {
                    $result = file_get_contents($file, false, null, $offset, $length);
                }
                
                return ($result === false ? '' : $result);
            }
            
            return '';
        }
        
        /**
         * Writes data to a file
         *
         * @param string        $file     The file path where the data will be written.
         * @param string|array  $data     The data to be written to the file. If an array is provided,
         *                                it will be joined with a new line separator.
         * @param int|bool|null $offset   The offset where the data will be written in the file.
         *                                If null or false, the data will be written from the beginning of the file.
         *                                If true, the data will be appended to the file.
         *                                If an integer is provided, the data will be inserted at the specified offset.
         * @param bool          $replace  Determines whether to replace existing data at the offset
         *                                if the offset is specified and $replace is true.
         *                                If $replace is false, the original content moves to the right.
         *                                This parameter is ignored if $offset is null, false, or true.
         *
         * Example writing sequence
         * Command                           |     File content
         * ----------------------------------|------------------------------
         * File::write("file", "AB");                 |     "AB"             // Clear file and write new content
         * File::write("file", "ABC");                |     "ABC"            // Clear file and write new content
         * File::write("file", "123", true);          |     "ABC123"         // Append contents
         * File::write("file", "-", 2);               |     "AB-C123"        // Insert at offset 2
         * File::write("file", "*", -2);              |     "AB-C1*23"       // Insert at offset -2
         * File::write("file", "?", 1, true);         |     "A?-C1*23"       // Override from offset 1
         * File::write("file", "XZ", -1, true);       |     "A?-C1*2XY"      // Override from offset -1
         *
         *
         * @return int|bool   Returns the number of bytes written to the file on success,
         *                   or false on failure.
         */
        public static function write(string $file, string|array $data = '', int|bool|null $offset = null, bool $replace = false): int|bool {
            $data = (is_array($data) ? implode(PHP_EOL, $data) : $data);
            if (!is_file($file)) {
                @touch($file);
            }
            
            if (is_file($file) && is_writable($file)) {
                switch (true) {
                    # Write new data to file
                    case ($offset === null || $offset === false):
                        return file_put_contents($file, $data);
                    # Append data to file
                    case ($offset === true):
                        return file_put_contents($file, $data, FILE_APPEND);
                    # Insert data to file
                    default:
                        $result = false;
                        $offset = (int)$offset;
                        $handle = fopen($file, 'cb+');
                        if ($handle !== false) {
                            rewind($handle);
                            # Handle negative offset
                            if ($offset < 0) {
                                fseek($handle, 0, SEEK_END);
                                $offset = ftell($handle) - abs($offset);
                            }
                            fseek($handle, $offset);
                            if ($replace) {
                                # Override data at this offset
                                fwrite($handle, $data);
                            } else {
                                # Insert data at this offset (original content moves to the right)
                                $rest = fread($handle, (int)filesize($file));
                                fseek($handle, $offset);
                                $result = fwrite($handle, $data . $rest);
                            }
                            fclose($handle);
                            clearstatcache();
                            
                            return $result;
                        }
                        break;
                }
            }
            
            return false;
        }
        
        /**
         * Append data to a file
         *
         * @param string       $file The file to append the data to
         * @param string|array $data The data to be appended to the file. It can either be a string or an array of
         *                           strings
         *
         * @return bool|int
         */
        public static function append(string $file, string|array $data): bool|int {
            return static::write($file, $data, true);
        }
        
        /**
         * Modifies a file by writing data at a specific offset
         *
         * @param string       $file       The file path to modify
         * @param string|array $data       The data to write at the specified offset
         *                                 If a string is provided, it will be written as is.
         *                                 If an array is provided, the elements will be concatenated and written.
         * @param int          $offset     The byte offset to start writing the data
         * @param bool         $replace    If set to true, the existing contents at the specified offset will be
         *                                 replaced If set to false, the data will be inserted at the specified offset,
         *                                 pushing down the existing contents after the offset
         *
         * @return bool|int
         */
        public static function modify(string $file, string|array $data, int $offset, bool $replace = false) {
            return static::write($file, $data, $offset, $replace);
        }
        
        /**
         * Clears the content of a file by replacing it with an empty string
         *
         * @param string $file The path to the file that needs to be cleared
         *
         * @return bool|int The number of bytes written to the file, or false on failure
         */
        public static function clear(string $file): bool|int {
            return file_put_contents($file, '');
        }
        
        /**
         * Returns the position of the end of the file pointer.
         *
         * @param resource $handle The file handle to operate on.
         *
         * @return bool|int The current position of the file pointer from the beginning of the file, or false on
         *                  failure.
         */
        public static function fend($handle): bool|int {
            # Get current offset
            $offset = ftell($handle);
            # Jump to the end
            fseek($handle, 0, SEEK_END);
            # Get result
            $result = ftell($handle);
            # Reset back to original offset
            fseek($handle, $offset);
            
            return $result;
        }
        
        /**
         * Checks if a file exists and is readable.
         *
         * @param string $file The file path to check
         *
         * @return bool Returns true if the file exists and is readable, false otherwise.
         */
        public static function isReadable(string $file): bool {
            return is_file($file) && is_readable($file);
        }
        
        /**
         * Convert file size to a human-readable format.
         *
         * @param int $bytes The size of the file in bytes.
         * @param int $dec   The number of decimal places to round the result to. Default is 2.
         *
         * @return string            The human-readable file size.
         */
        public static function human_filesize(int $bytes, int $dec = 2): string {
            $size   = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor == 0) {
                $dec = 0;
            }
            
            
            return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
        }
        
        /**
         * Returns an array with all elements inside the given directory
         *
         * @param string|null $dir                 The directory you want to search in
         * @param string|null $regex_filter        Only collect results where the full absolute path matches against
         *                                         this regex pattern
         * @param int         $max_depth           Max depth for recursive calls, default is -1 which means no limit.
         *                                         Pass 0 to only list files in the specified folder but not in
         *                                         subfolders.
         * @param bool        $include_directories By default, only files will be collected. Set to true if you also
         *                                         want to catch the directory paths.
         *
         * @return array|string[]
         */
        public static function find(?string $dir, string $regex_filter = null, int $max_depth = -1, bool $include_directories = false): array {
            if (!is_dir($dir)) {
                return [];
            }
            $data     = [];
            $dir      = (string)$dir;
            $iterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS ^ \FilesystemIterator::CURRENT_AS_PATHNAME ^ \FilesystemIterator::FOLLOW_SYMLINKS);
            $files    = new \RecursiveIteratorIterator($iterator, $include_directories ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY);
            $files->setMaxDepth($max_depth);
            foreach ($files as $file) {
                if ($regex_filter !== null && $regex_filter !== '' && !preg_match($regex_filter, $file)) {
                    continue;
                }
                $data[] = $file;
            }
            
            return $data;
        }
        
    }
