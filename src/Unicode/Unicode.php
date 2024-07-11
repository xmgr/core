<?php
    
    namespace Xmgr\Unicode;
    
    use Xmgr\DateTime;
    use Xmgr\Filesystem\File;
    use Xmgr\Net\Http;
    
    /**
     * Class Unicode
     *
     * This class provides functionalities related to Unicode.
     */
    class Unicode {
        
        /**
         * Returns the Unicode character code point (CP) as a formatted string.
         * ----------------------------------------------------------------
         * Usage:
         * ucp(65);       // "U+0041"
         * ucp(8364);     // "U+20AC"
         *
         * @param int $cp The Unicode character code point.
         *
         * @return string    The formatted string representing the Unicode character code point.
         */
        public static function ucp(int $cp): string {
            return 'U+' . str_pad(strtoupper(dechex($cp)), 4, '0', STR_PAD_LEFT);
        }
        
        /**
         * Array of unicode planes.
         *
         * Each element in the array represents a Unicode plane and consists of the plane
         * name and the start and end code points of the plane.
         *
         * @var array
         */
        public static array $planes = [
            ['Basic Multilingual Plane (BMP)', 0x00, 0xFFFF],
            ['Supplementary Multilingual Plane (SMP)', 0x10000, 0x1FFFF],
            ['Supplementary Ideographic Plane (SIP)', 0x20000, 0x2FFFF],
            ['Tertiary Ideographic Plane (TIP)', 0x30000, 0x3FFFF],
            ['Supplementary Special-purpose Plane (SSP)', 0x40000, 0x4FFFF],
            ['Unassigned', 0x50000, 0x5FFFF],
            ['Unassigned', 0x60000, 0x6FFFF],
            ['Unassigned', 0x70000, 0x7FFFF],
            ['Unassigned', 0x80000, 0x8FFFF],
            ['Unassigned', 0x90000, 0x9FFFF],
            ['Unassigned', 0xA0000, 0xAFFFF],
            ['Unassigned', 0xB0000, 0xBFFFF],
            ['Unassigned', 0xC0000, 0xCFFFF],
            ['Unassigned', 0xD0000, 0xDFFFF],
            ['Supplementary Private Use Area-J (SSP)', 0xE0000, 0xEFFFF],
            ['Supplementary Private Use Area-K (SPUA-A)', 0xF0000, 0xFFFFF],
            ['Supplementary Private Use Area-K (SPUA-B)', 0x100000, 0x10FFFF],
        ];
        
        public static array $blocks = [];
        
        /**
         * Retrieves the Unicode blocks as an array.
         *
         * @return array The array of Unicode blocks.
         * @throws \Exception
         */
        public static function blocks(): array {
            if (!static::$blocks) {
                static::updateUnicodeBlocks();
                static::$blocks = json2array(File::read(static::unicodeBlockFile()));
            }
            
            return static::$blocks;
        }
        
        /**
         * Returns the file path for the Unicode block file.
         *
         * @return string The file path for the Unicode block file.
         */
        protected static function unicodeBlockFile(): string {
            return path('storage/app/unicode-blocks.json');
        }
        
        /**
         * Update the Unicode blocks data.
         *
         * @param bool $force_update Whether to force the update or not. Default is false.
         *
         * @return void
         * @throws \Exception
         */
        public static function updateUnicodeBlocks(bool $force_update = false): void {
            $unicode_blocks_file = static::unicodeBlockFile();
            if (File::isReadable($unicode_blocks_file)) {
                $filemtime = filemtime($unicode_blocks_file);
                if ($force_update || ($filemtime && DateTime::create($filemtime)->olderThanInSeconds((60 * 60 * 24)))) {
                    $blocks     = Http::get('https://www.unicode.org/Public/UCD/latest/ucd/Blocks.txt')->body();
                    $blocks     = preg_replace('/\r\n?/', '\n', $blocks);
                    $blocks_arr = explode("\n", $blocks);
                    $block_data = [];
                    foreach ($blocks_arr as $sb) {
                        if (preg_match('/^[[:xdigit:]]+?\.\.[[:xdigit:]]+?;\s+.+?$/ui', $sb)) {
                            $tmparr = explode(';', $sb, 2);
                            if (count($tmparr) >= 2) {
                                $blockname = trim($tmparr[1]);
                                $range     = explode('..', $tmparr[0]);
                                if (count($range) === 2) {
                                    $block_data[] = [hexdec($range[0]), hexdec($range[1]), $blockname];
                                }
                            }
                        }
                    }
                    if ($block_data) {
                        File::write(static::unicodeBlockFile(), json_encode($block_data, JSON_PRETTY_PRINT));
                    }
                }
            }
        }
        
    }
    