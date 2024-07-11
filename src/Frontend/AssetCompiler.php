<?php
    
    namespace Xmgr\Frontend;
    
    use Xmgr\Filesystem\File;
    use Xmgr\Log;
    
    /**
     * Class AssetCompiler
     *
     * This class is responsible for compiling CSS and JS files into minified versions.
     */
    class AssetCompiler {
        
        /**
         * Default CSS file names.
         * Array containing the file names of default CSS files.
         *
         * @var array
         */
        public const array DEFAULT_CSS = [
            'variables',
            'general',
            'global',
            'layout',
            'spacing',
            'colors',
            'typography',
            'table',
            'grid',
            'form-elements',
            'boxes',
            'defaults',
            /* Responsive */
            'responsive/responsive',
            'responsive/grid',
            'responsive/layout',
        ];
        
        /**
         * The default JavaScript files to be loaded.
         *
         * This constant variable contains an array of JavaScript files that are
         * necessary for the application to function properly. The files are loaded
         * in the order specified in the array.
         *
         * @var array
         */
        public const array DEFAULT_JS = [
            'functions',
            'prototype/Object',
            'prototype/Array',
            'prototype/String',
            'prototype/Element',
            'prototype/HTMLElement',
            'init',
        ];
        
        /**
         * Compile the given CSS files into a minified CSS file.
         *
         * @param array  $names An array of CSS file names to be compiled.
         * @param string $compiled_filename
         *
         * @return void
         */
        public static function compileCss(array $names, string $compiled_filename): void {
            $compiled_file = css_dir($compiled_filename . '.css');
            if (!is_dir(dirname($compiled_file))) {
                Log::runtime('Path "' . dirname($compiled_file) . '" does not exist!');
                
                return;
            }
            $handle = fopen($compiled_file, 'w');
            if ($handle !== false) {
                foreach ($names as $filename) {
                    $file = xmpath('assets/css', $filename . '.css');
                    if (File::exists($file)) {
                        fwrite($handle, "/*\n * ================================\n * $filename.css\n * ================================\n */\n\n" . File::read($file) . "\n\n");
                    }
                }
                fclose($handle);
            }
        }
        
        /**
         * Compile the given JS files into a minified JS file.
         *
         * @param array  $names An array of CSS file names to be compiled.
         * @param string $compiled_filename
         *
         * @return void
         */
        public static function compileJs(array $names, string $compiled_filename): void {
            $compiled_file = js_dir($compiled_filename . '.js');
            if (!is_dir(dirname($compiled_file))) {
                Log::runtime('Path "' . dirname($compiled_file) . '" does not exist!');
                
                return;
            }
            $handle = fopen($compiled_file, 'w');
            if ($handle !== false) {
                foreach ($names as $filename) {
                    $file = xmpath('assets/js', $filename . '.js');
                    if (File::exists($file)) {
                        fwrite($handle, "/*\n * ================================\n * $filename.js\n * ================================\n */\n\n" . File::read($file) . "\n\n");
                    }
                }
                fclose($handle);
            }
        }
        
    }
