<?php
    
    namespace Xmgr;
    
    /**
     *
     */
    class Console {
        
        public const string RESET = "\033[0m";
        
        # Text color
        public const string COLOR_BLACK      = "\033[30m";
        public const string COLOR_WHITE      = "\033[97m";
        public const string COLOR_RED        = "\033[31m";
        public const string COLOR_GREEN      = "\033[32m";
        public const string COLOR_YELLOW     = "\033[33m";
        public const string COLOR_BLUE       = "\033[34m";
        public const string COLOR_LIGHT_GREY = "\033[37m";
        public const string COLOR_VIOLET     = "\033[35m";
        public const string COLOR_CYAN       = "\033[36m";
        
        # Background color
        public const string BG_BLACK      = "\033[40m";
        public const string BG_WHITE      = "\033[107m";
        public const string BG_RED        = "\033[41m";
        public const string BG_GREEN      = "\033[42m";
        public const string BG_YELLOW     = "\033[43m";
        public const string BG_BLUE       = "\033[44m";
        public const string BG_LIGHT_GREY = "\033[47m";
        public const string BG_DARK_GREY  = "\033[100m";
        public const string BG_VIOLET     = "\033[45m";
        public const string BG_CYAN       = "\033[46m";
        
        # Formatierung
        public const string FORMAT_BOLD          = "\033[1m";
        public const string FORMAT_ITALIC        = "\033[3m";
        public const string FORMAT_UNDERLINE     = "\033[4m";
        public const string FORMAT_BLINK         = "\033[5m";
        public const string FORMAT_REVERSED      = "\033[7m";
        public const string FORMAT_CONCEALED     = "\033[8m";
        public const string FORMAT_STRIKETHROUGH = "\033[9m";
        
        public const string ICON_INFO  = 'ⓘ';
        public const string ICON_WARN  = '⚠';
        public const string ICON_OK    = '✓';
        public const string ICON_ERROR = '✗';
        
        /**
         * Named arguments, z.B. --foo oder --foo=bar
         *
         * @var array|null
         */
        protected static ?array $arguments = null;
        
        /**
         * Optionen bzw flags, z.B. -F oder -g oder -2
         *
         * @var array|null
         */
        protected static ?array $options = null;
        
        /**
         * Befehlsargumente die nicht mit Bindestrich beginnen (aufgerufener Skriptname ausgenommen)
         *
         * @var array|null
         */
        protected static ?array $values = null;
        
        public static int $verbose_messages_count = 0;
        
        public static bool $disableVerbosity = false;
        
        /**
         * Returns the $argv array or a specific argument if $index is given.
         *
         * @param int|null $index   The index for a specific argument (note: 0 means the current script, 1 is the first
         *                          argument and so on ...)
         * @param mixed    $default Default value if the given $index does not exist
         *
         * @return array|mixed
         */
        private static function argv(?int $index = null, mixed $default = ''): mixed {
            static $args = null;
            if ($args === null) {
                global $argv;
                if (isset($argv) && is_array($argv)) {
                    $args = $argv;
                }
            }
            
            return ($index === null ? $args : (isset($args[$index]) ? (string)$args[$index] : $default));
        }
        
        /**
         * Gibt ein Array von Argumenten zurück, die nicht mit Bindestrich beginnen.
         * Hinweis: das erste Item (Index 0) ist der Name des Befehls.
         *
         * @return array|null
         */
        public static function values(): ?array {
            if (static::$values === null) {
                static::$values = [];
                foreach (static::argv() as $k => $v) {
                    if ($k === 0) {
                        # Den Skriptnamen selbst brauchen wir hier nicht
                        continue;
                    }
                    if (!str_starts_with($v, '-')) {
                        static::$values[] = $v;
                    }
                }
            }
            
            return static::$values;
        }
        
        /**
         * Get the value at the given index of the values array, or return the default value if index is not found.
         *
         * @param int          $index   The index of the value to retrieve from the array.
         * @param mixed|string $default The default value to return if index is not found. Defaults to an empty string.
         *
         * @return mixed The value at the given index of the values array, or the default value if index is not found.
         */
        public static function value(int $index, mixed $default = ''): mixed {
            return arr(static::values(), $index, $default);
        }
        
        /**
         * Returns the first integer found in the array of values.
         * If no integer is found, it returns 0.
         *
         * @return int The first integer found in the array of values, or 0 if no integer is found.
         */
        public static function firstInt(): int {
            foreach (static::values() as $value) {
                if (ctype_digit($value)) {
                    return (int)$value;
                }
            }
            
            return 0;
        }
        
        /**
         * Returns a trimmed string value obtained from the static method call `value(0)`.
         *
         * @return string The trimmed string value.
         */
        public static function command(): string {
            return trim(static::value(0));
        }
        
        /**
         * Extracts the class name from the fully qualified command string.
         *
         * @return string The class name extracted from the command string.
         */
        public static function cmdClass(): string {
            return Str::before(static::command(), ':');
        }
        
        /**
         * Retrieves the command action from the command string.
         *
         * This method retrieves the command action from the command string by using the
         * `Str::after()` method to extract the portion of the command string that comes
         * after the first occurrence of the colon ':' character.
         *
         * @return string The command action.
         */
        public static function cmdAction(): string {
            return Str::after(static::command(), ':');
        }
        
        /**
         * Returns all CLI arguments as associative array.
         * NOTE: dashes in the keys are trimmed. So passing an argument like --foo=bar would be stored as ['foo' =>
         * 'bar']
         *
         * @return array|null
         */
        public static function args(): ?array {
            if (static::$arguments === null) {
                static::$arguments = [];
                foreach (static::argv() as $arg) {
                    if (!str_contains($arg, '=')) {
                        static::$arguments[trim($arg, '-')] = true;
                    } else {
                        $arr                                   = explode('=', $arg, 2);
                        static::$arguments[trim($arr[0], '-')] = ($arr[1] ?? null);
                    }
                }
            }
            
            return static::$arguments;
        }
        
        /**
         * Retrieves CLI options passed as command line arguments.
         *
         * This function parses the command line arguments passed to the script and extracts the options
         * and their corresponding values. It returns an associative array where the keys are the options
         * and the values are the respective values. The options are defined by a single dash followed by
         * a single character.
         *
         * Example:
         * Input.....: php script.php -a value1 -b value2 -c value3 -xyz
         * Output....: ['a' => 'value1', 'b' => 'value2', 'c' => 'value3', 'x' => '', 'y' => '', 'z' => '']
         *
         * Note that this function only supports single character options and does not handle long options
         * (options with two dashes).
         *
         * @return array An associative array containing the options and their values
         */
        public static function options(): array {
            if (static::$options === null) {
                static::$options = [];
                $args            = static::argv();
                foreach ($args as $i => $arg) {
                    if ($i === 0) {
                        continue;
                    }
                    $next = $args[$i + 1] ?? '';
                    if ($arg[0] === '-' && $arg[1] !== '-') {
                        $to = str_split(trim($arg, '-'));
                        foreach ($to as $o) {
                            static::$options[$o] = (str_starts_with($next, '-') ? '' : $next);
                        }
                    }
                }
            }
            
            return static::$options;
        }
        
        /**
         * Retrieves the value of a specific option by name.
         *
         * @param string $name The name of the option to retrieve.
         *
         * @return mixed The value of the option, or null if the option does not exist.
         */
        public static function option(string $name) {
            return arr(static::options(), $name);
        }
        
        /**
         * Checks if the given option exists in the list of options.
         *
         * @param string $name The name of the option to check.
         *
         * @return bool Returns true if the option exists, otherwise false.
         */
        public static function hasOption(string $name): bool {
            return arr(static::options(), $name) !== null;
            
        }
        
        /**
         * Return specific CLI argument. It does not matter if you call it with or without leading dashes.
         * Example: if --foo=bar has been passed, the call arg('foo') will return 'bar'.
         * -
         * Note: you can also use param() function to fetch an argument from either CLI or web
         *
         * @param string     $name
         * @param mixed|null $default
         *
         * @return mixed|null
         */
        public static function arg(string $name, mixed $default = ''): mixed {
            return data_get(static::args(), trim($name, '-'), $default);
        }
        
        /**
         * Determines if any element in the given array is not false
         *
         * @param array $arguments        The array of arguments to check
         * @param bool  $check_truthyness (Optional) Whether to check the truthiness of the argument values. Default is
         *                                false.
         *
         * @return bool True if any element is not false, false otherwise
         */
        public static function hasAnyArgument(array $arguments, bool $check_truthyness = false): bool {
            foreach ($arguments as $argument) {
                $value = static::arg($argument, false);
                if ($value !== false && (!$check_truthyness || $value)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Checks if the "help" argument is passed or if the "/?" option is used in Windows version.
         *
         * @return bool True if the "help" argument is passed or if the "/?" option is used in Windows version, false
         *              otherwise.
         */
        public static function help(): bool {
            return static::arg('help') || /* Windows-Version: */ in_array('/?', static::argv());
        }
        
        /**
         * Returns the value of the 'debug' argument.
         *
         * @return mixed The value of the 'debug' argument.
         */
        public static function debug() {
            return static::arg('debug');
        }
        
        /**
         * Determine if verbose output is enabled.
         *
         * @return bool Returns true if verbose output is enabled, false otherwise.
         */
        public static function verbose(): bool {
            return (!static::$disableVerbosity && ((static::arg('verbose') || static::hasOption('v') || static::debug())));
        }
        
        /**
         * Prints a verbose message with optional data.
         *
         * @param string $message The message to be printed.
         * @param mixed  ...$data Optional additional data to be dumped.
         *
         * @return void
         */
        public static function printVerboseMessage(string $message, ...$data) {
            static::$verbose_messages_count++;
            if (static::verbose()) {
                echo $message . "\n";
                dump(...$data);
            }
        }
        
        /**
         * Check if the script is running in a simulation mode.
         *
         * @return bool True if the script is running in a simulation mode, false otherwise.
         */
        public static function simulate(): bool {
            return static::hasAnyArgument(['test', 'dry', 'dryrun', 'dry-run', 'simulate']);
        }
        
        /**
         * Checks if the current object has any argument equal to 'confirm' or 'confirmed'.
         *
         * @return bool Returns true if any of the arguments is equal to 'confirm' or 'confirmed', otherwise returns
         *              false.
         */
        public static function confirmed(): bool {
            return static::hasAnyArgument(['confirm', 'confirmed'], true);
        }
        
        /**
         * Applies specified color and formatting to a given text.
         *
         * @param string $text       The text to be colored and formatted.
         * @param string $textcolor  (optional) The color of the text. Default is an empty string (no color).
         * @param string $background (optional) The background color of the text. Default is an empty string (no
         *                           background color).
         * @param string $format     (optional) Additional formatting to be applied to the text. Default is an empty
         *                           string (no formatting).
         * @param bool   $reset      (optional) Whether to reset the color and formatting after the text. Default is
         *                           true.
         *
         * @return string The colored and formatted text.
         */
        public static function colored(string $text, string $textcolor = '', string $background = '', string $format = '', bool $reset = true): string {
            if (Console::arg('plain')) {
                return $text;
            }
            
            return ($reset ? self::RESET : '') . $textcolor . $background . $format . $text . ($reset ? self::RESET : '');
        }
        
        /**
         * Formats a text with specified formatting options.
         *
         * @param string $text          The text to be formatted.
         * @param bool   $bold          (optional) Whether to apply bold formatting. Default is false.
         * @param bool   $italic        (optional) Whether to apply italic formatting. Default is false.
         * @param bool   $underline     (optional) Whether to apply underline formatting. Default is false.
         * @param bool   $blink         (optional) Whether to apply blink formatting. Default is false.
         * @param bool   $concealed     (optional) Whether to apply concealed formatting. Default is false.
         * @param bool   $strikethrough (optional) Whether to apply strikethrough formatting. Default is false.
         * @param string $textcolor     (optional) The text color in ANSI format. Default is empty.
         *
         * @return string The formatted text.
         */
        public static function formatted(string $text, bool $bold = false, bool $italic = false, bool $underline = false, bool $blink = false, bool $concealed = false, bool $strikethrough = false, string $textcolor = ''): string {
            $string = '';
            if (!Console::arg('plain')) {
                $string .= self::RESET;
                $string .= $bold ? self::FORMAT_BOLD : '';
                $string .= $italic ? self::FORMAT_ITALIC : '';
                $string .= $underline ? self::FORMAT_UNDERLINE : '';
                $string .= $blink ? self::FORMAT_BLINK : '';
                $string .= $concealed ? self::FORMAT_CONCEALED : '';
                $string .= $strikethrough ? self::FORMAT_STRIKETHROUGH : '';
                $string .= $textcolor;
                $string .= $text;
                $string .= self::RESET;
            }
            
            return $string;
        }
        
        /**
         * Rings the bell if the sound alert is enabled and not in silent or mute mode.
         *
         * @return void
         */
        public static function ringBell() {
            if (self::arg('sound-alert') && !self::arg('silent') && !self::arg('mute')) {
                echo "\x07";
            }
        }
        
        /**
         * Returns a formatted error message.
         *
         * @param mixed $message The error message to be formatted. It can be a string or an instance
         *                       of the \Exception class.
         *
         * @return string The formatted error message with colors and icons.
         */
        public static function error($message): string {
            $text = $message;
            if ($message instanceof \Exception) {
                $text = $message->getMessage();
            }
            
            return self::colored(self::ICON_ERROR . ' ' . $text, self::COLOR_WHITE, self::BG_RED) . "\n";
        }
        
        /**
         * Secondary text transformation.
         *
         * @param string $text The input text.
         *
         * @return string The transformed text.
         */
        public static function secondary(string $text): string {
            return self::colored($text, self::COLOR_LIGHT_GREY);
        }
        
        /**
         * Generates a note with optional bold formatting.
         *
         * @param string $text The text content of the note.
         * @param bool   $bold Indicates whether the note should be rendered in bold format.
         *
         * @return string The formatted note.
         */
        public static function note(string $text, bool $bold = false): string {
            return self::colored($text, self::COLOR_YELLOW, '', ($bold ? self::FORMAT_BOLD : '')) . "\n";
        }
        
        /**
         * Applies yellow color to the given text.
         *
         * @param string $text   The text to be colored.
         * @param string $format Additional formatting for the colored text (optional).
         *
         * @return string The colored text with yellow color.
         */
        public static function yellow(string $text, string $format = ''): string {
            return self::colored($text, self::COLOR_YELLOW, '', $format);
        }
        
        /**
         * Returns the given text formatted in green color.
         *
         * @param string $text The text to be formatted.
         *
         * @return string The formatted text in green color.
         */
        public static function green(string $text): string {
            return self::colored($text, self::COLOR_GREEN) . "\n";
        }
        
        /**
         * Returns success colored text (white text on green background)
         *
         * @param string $text The success message to be displayed
         *
         * @return string The formatted success message
         */
        public static function success(string $text): string {
            return self::colored(self::ICON_OK . ' ' . $text, self::COLOR_WHITE, self::BG_GREEN) . "\n";
        }
        
        /**
         * Returns a formatted information message.
         *
         * @param string $text The text message to be displayed.
         *
         * @return string The formatted information message.
         */
        public static function info(string $text): string {
            return self::colored(self::ICON_INFO . ' ' . $text, self::COLOR_BLUE) . "\n";
        }
        
        /**
         * Generates a warning message with colored text.
         *
         * @param string $text The warning message text.
         *
         * @return string The formatted warning message.
         */
        public static function warning(string $text): string {
            return self::colored(self::ICON_WARN . ' ' . $text, self::COLOR_BLACK, self::BG_YELLOW) . "\n";
        }
        
        /**
         * Retrieve the number of columns in the terminal window.
         *
         * @return int The number of columns in the terminal window.
         */
        public static function cols(): int {
            static $cols = null;
            if ($cols === null) {
                $cols = 20;
                if (!str_contains(strtolower(PHP_OS), 'win')) {
                    $cols = (int)(@exec('tput cols'));
                }
            }
            
            return $cols;
        }
        
        /**
         * Updates and displays a progress bar.
         *
         * @param int      $done  The number of completed units.
         * @param int      $total The total number of units.
         * @param int|null $size  The size of the progress bar.
         *
         * @return void
         */
        public static function progressbar(int $done, int $total, ?int $size = null) {
            static $start_time;
            $total = abs($total);
            if (!$total) {
                return;
            }
            if ($done === 0) {
                $done = 1;
            }
            
            if ($size === null) {
                $size = max((int)(self::cols() / 100 * 50), 20);
            }
            
            // if we go over our bound, just ignore it
            if ($done > $total) return;
            
            if (empty($start_time)) $start_time = time();
            $now = time();
            
            $perc = (double)($done / $total);
            
            $bar = floor($perc * $size);
            
            $status_bar = "\r[";
            $status_bar .= str_repeat('=', $bar);
            if ($bar < $size) {
                $status_bar .= '>';
                $status_bar .= str_repeat(' ', $size - $bar);
            } else {
                $status_bar .= '=';
            }
            
            $disp = number_format($perc * 100, 0);
            
            $status_bar .= "] $disp%  $done/$total";
            
            $rate = ($now - $start_time) / $done;
            $left = $total - $done;
            $eta  = round($rate * $left, 2);
            
            $elapsed = $now - $start_time;
            
            $status_bar .= ' remaining: ' . number_format($eta) . ' sec.  elapsed: ' . number_format($elapsed) . ' sec.';
            
            echo "$status_bar  ";
            
            flush();
            
            // when done, send a newline
            if ($done == $total) {
                echo "\n";
            }
            
        }
        
        /**
         * Countdown method.
         *
         * @param int $seconds The number of seconds to count down from. Default is 3.
         *
         * @return void
         */
        public static function countdown(int $seconds = 3) {
            $seconds = abs($seconds);
            echo 'Operation wird ausgeführt in ';
            while ($seconds) {
                echo $seconds . ' .. ';
                sleep(1);
                $seconds--;
            }
        }
        
    }
