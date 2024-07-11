<?php
    
    namespace Xmgr\Filesystem;
    
    /**
     * The Directory class provides various utilities for working with directories and files.
     */
    class Directory {
        
        /**
         * Checks if a file exists
         *
         * @param string $file The file path to check
         *
         * @return bool True if the file exists, false otherwise
         */
        public static function exists(string $file): bool {
            return is_dir($file);
        }
        
        /**
         * Touches a file if it does not exist
         *
         * @param string $file The file to be touched
         *
         * @return void
         */
        public static function create(string $file): void {
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
            return is_dir($file) && is_writable($file);
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
        public static function files(?string $dir, string $regex_filter = null, int $max_depth = -1, bool $include_directories = false): array {
            return File::find($dir, $regex_filter, $max_depth, $include_directories);
        }
        
    }
