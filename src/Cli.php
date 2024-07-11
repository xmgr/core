<?php
    
    namespace Xmgr;
    
    /**
     * Retrieves the value of the specified option from the command line interface options array,
     * or returns the default value if the option is not found.
     *
     * @param string $name    The name of the option to retrieve.
     * @param mixed  $default The default value to return if the option is not found. Defaults to null.
     *
     * @return mixed The value of the option, or the default value if the option is not found.
     */
    class Cli {
        
        /**
         * @var $cli_instance null Represents the Command Line Interface (CLI) instance.
         */
        public static ?self $cli_instance = null;
        
        protected string $input   = '';
        protected array  $args    = [];
        protected array  $options = [];
        protected array  $named   = [];
        protected array  $values  = [];
        
        /**
         * Constructs a new instance of the class.
         *
         * @param string|null $input The input string. Defaults to null.
         *
         * @return void
         */
        public function __construct(?string $input = null) {
            $this->input = $input ?? implode(' ', argv());
            $this->args  = static::parse_cmd($this->input);
            $this->parseOptions();
            $this->parseNamedArguments();
            $this->parseValues();
        }
        
        /**
         * Parses a command string into an array of command parts.
         *
         * This function takes a command string as input and splits it into individual command parts.
         * The command string is parsed based on the following rules:
         * - The string is trimmed to remove leading and trailing whitespace.
         * - Double quotes (") and single quotes (') are used to enclose sections of the command string.
         *   - The enclosed sections are treated as separate parts of the command.
         *   - Quotes can be escaped with a backslash (\) to be treated as literal characters.
         *   - Nested quotes are not supported.
         * - Spaces are used to separate individual parts of the command string.
         * - All parts are returned in an array in the order they appear in the command string.
         *
         * Example:
         * Input.....: 'echo "Hello, World!"'
         * Output....: ['echo', 'Hello, World!']
         *
         * @param string $input The command string to parse.
         *
         * @return array An array of command parts.
         */
        protected static function parse_cmd(string $input): array {
            $input   = trim($input);
            $len     = mb_strlen($input);
            $i       = 0;
            $dquotes = false;
            $squotes = false;
            $parts   = [];
            $last    = '';
            while ($i < $len) {
                $c = mb_substr($input, $i, 1);
                $p = mb_substr($input, $i - 1, 1);
                $n = mb_substr($input, $i + 1, 1);
                switch (true) {
                    # String literal
                    case $c === "\\":
                        $i    += 2;
                        $last .= $n;
                        break;
                    # Begin reading inside double quotes
                    case $dquotes || $c === '"':
                        $i++;
                        if ($c === '"') {
                            if ($dquotes) {
                                $parts[] = $last;
                                $last    = '';
                            }
                            $dquotes = !$dquotes;
                        } else {
                            $last .= $c;
                        }
                        break;
                    # Begin reading inside single quotes
                    case $squotes || $c === "'":
                        $i++;
                        if ($c === "'") {
                            if ($squotes) {
                                $parts[] = $last;
                                $last    = '';
                            }
                            $squotes = !$squotes;
                        } else {
                            $last .= $c;
                        }
                        break;
                    # Separates arguments
                    case ($c === ' '):
                        $i++;
                        if ($last !== '') {
                            $parts[] = $last;
                            $last    = '';
                        }
                        break;
                    # Append the current character
                    default:
                        $last .= $c;
                        $i++;
                        break;
                }
                
            }
            if ($last !== '') {
                $parts[] = $last;
            }
            
            return $parts;
        }
        
        /**
         * Returns the array of arguments.
         *
         * @return array The array of arguments.
         */
        public function args(): array {
            return $this->args;
        }
        
        /**
         * Parses the values from the arguments and assigns them to the values property.
         *
         * Iterates over the given arguments and assigns the values to the values property, if the argument does
         * not start with a hyphen and is not the first argument.
         *
         * @return void
         */
        protected function parseValues(): void {
            foreach ($this->args as $i => $arg) {
                if ($arg[0] !== '-' && $i !== 0) {
                    $this->values[] = $arg;
                }
            }
        }
        
        /**
         * Parses the command line options and stores them in the class property `$options`.
         *
         * @return void
         */
        protected function parseOptions(): void {
            foreach ($this->args as $i => $arg) {
                if ($i === 0) {
                    continue;
                }
                $next = $this->args[$i + 1] ?? '';
                if ($arg[0] === '-' && $arg[1] !== '-') {
                    $to = str_split(trim($arg, '-'));
                    foreach ($to as $o) {
                        $this->options[$o] = (str_starts_with($next, '-') ? '' : $next);
                    }
                }
            }
        }
        
        /**
         * Parses named arguments from the command line and stores them in the object.
         *
         * @return void
         */
        protected function parseNamedArguments(): void {
            foreach ($this->args as $i => $arg) {
                if ($i === 0) {
                    continue;
                }
                if (mb_strpos($arg, '--') === 0) {
                    $arr                              = explode('=', $arg, 2);
                    $v                                = (string)arr($arr, 1, '');
                    $this->named[ltrim($arr[0], '-')] = ($v === '' ? true : $v);
                }
            }
        }
        
        /**
         * Returns a string that contains all the command line arguments separated by a space.
         *
         * @return string The concatenated command line arguments.
         */
        public function input(): string {
            return $this->input;
        }
        
        /**
         * Checks if a specific option is present in the command line options.
         *
         * @param string $name The name of the option to check.
         *
         * @return bool True if the option is present, false otherwise.
         */
        public function hasOption(string $name): bool {
            return array_key_exists($name, $this->options);
        }
        
        /**
         * Executes the script and returns the result of the firstArgument method.
         *
         * This method executes the script logic and returns the result of the firstArgument method.
         * The firstArgument method should already be implemented in the current class and return the desired result.
         *
         * @return mixed The result of the firstArgument method.
         */
        public function script() {
            return $this->firstArgument();
        }
        
        /**
         * Returns the command from the array of arguments.
         *
         * The method iterates through each argument in the array and checks if it matches the pattern /^\w+?:\w+?$/ui.
         * If a matching argument is found, the method returns that argument as the command.
         * If no matching argument is found, an empty string is returned.
         *
         * @return string The command from the arguments, or an empty string if no command is found.
         */
        public function command(): string {
            foreach ($this->args as $arg) {
                if (preg_match('/^\w+?:\w+?$/ui', $arg)) {
                    return $arg;
                }
            }
            
            return '';
        }
        
        /**
         * Returns the first value from the result of `explode(':', $this->command())`.
         *
         * @return string The first value from the result of `explode(':', $this->command())`.
         */
        public function commandName(): string {
            return Str::before($this->command(), ':');
        }
        
        /**
         * Returns the substring after the colon in the command string.
         *
         * @return string The substring after the colon.
         */
        public function commandAction(): string {
            return Str::after($this->command(), ':');
        }
        
        /**
         * Retrieves the value of the specified option from the command line interface options array,
         * or returns the default value if the option is not found.
         *
         * @param string $name    The name of the option to retrieve.
         * @param mixed  $default (optional) The default value to return if the option is not found. Defaults to an
         *                        empty string.
         *
         * @return mixed The value of the option if found, otherwise the default value.
         */
        public function optionValue(string $name, string $default = ''): mixed {
            return arr($this->options, $name, $default);
        }
        
        /**
         * Retrieves an array of all the keys in the command line interface options array.
         *
         * @return array An array containing all the keys from the command line interface options array.
         */
        public function options(): array {
            return $this->options;
        }
        
        /**
         * Retrieves an array of values from the command line arguments, excluding any options.
         *
         * @return array An array containing the values from the command line arguments.
         */
        public function values(): array {
            return $this->values;
        }
        
        /**
         * Returns the last value from the array of values.
         *
         * @return mixed The last value from the array.
         */
        public function firstArgument(): mixed {
            return Arr::firstValue($this->args);
        }
        
        /**
         * Returns the last value from the array of values.
         *
         * @return mixed The last value from the array.
         */
        public function firstValue(): mixed {
            return Arr::firstValue($this->values);
        }
        
        /**
         * Returns the last value from the array of values.
         *
         * @return mixed The last value from the array.
         */
        public function lastValue(): mixed {
            return Arr::lastValue($this->values);
        }
        
        /**
         * Returns the value of the specified named argument.
         *
         * @param string $name The name of the argument.
         *
         * @return mixed The value of the argument.
         */
        public function arg(string $name): mixed {
            return $this->named($name);
        }
        
        /**
         * Returns the value of the specified named argument.
         *
         * @param string $name The name of the argument.
         *
         * @return mixed The value of the argument.
         */
        public function named(string $name): mixed {
            return arr($this->named, ltrim($name, '-'), false);
        }
        
        /**
         * @param string $name
         *
         * @return bool
         */
        public function hasNamed(string $name): bool {
            return array_key_exists(ltrim($name, '-'), $this->named);
        }
        
        /**
         * Returns the value of the '--verbose' argument as a boolean.
         *
         * @return bool The value of the '--verbose' argument.
         */
        public function verbose(): bool {
            return (bool)static::arg('--verbose');
        }
        
        /**
         * @param string $name
         *
         * @return bool
         */
        public function hasFlag(string $name) {
            return $this->hasNamed($name) || $this->hasOption($name);
        }
        
    }
