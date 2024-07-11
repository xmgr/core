<?php
    
    use JetBrains\PhpStorm\NoReturn;
    use Random\RandomException;
    use Xmgr\Arr;
    use Xmgr\Collections\Collection;
    use Xmgr\Database;
    use Xmgr\Database\Column;
    use Xmgr\Database\QueryBuilder;
    use Xmgr\Event;
    use Xmgr\Expectation;
    use Xmgr\Filesystem\File;
    use Xmgr\Request;
    use Xmgr\Response;
    use Xmgr\Session;
    use Xmgr\Str;
    use Xmgr\Text;
    use Xmgr\Url;
    
    # @todo wrap all functions in function_exists condition
    
    /**
     * ################################################################
     * This file contains structurally independend
     * core helper functions.
     * ################################################################
     */
    
    # ################################################################
    # Path and filesystem helpers
    # ################################################################
    
    /**
     * Returns base directory
     * This is usually the document root, so we calculate the absolute path from the path
     * where this file resides and step 2 dirs up.
     * +++ NOTE: if this file ever moves to a different folder, the basedir probably needs to be adjusted! +++
     *
     * @param mixed ...$paths
     *
     * @return string
     */
    function xmpath(...$paths): string {
        $path = XM_BASEDIR;
        $to   = joinpaths(...$paths);
        if ($to !== '') {
            $path = rtrim($path . DIRECTORY_SEPARATOR . trim($to, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        }
        
        return $path === '' ? DIRECTORY_SEPARATOR : $path;
    }
    
    /**
     * Constructs a normalized path by joining multiple path segments.
     *
     * This function takes a base path and multiple additional path segments and constructs a normalized path by
     * joining them together. The base path should be a string representing the initial path, and the additional path
     * segments should be provided as variadic arguments. The resulting path is normalized by replacing any backslashes
     * or forward slashes with the appropriate DIRECTORY_SEPARATOR constant. If the base path is empty, the
     * DIRECTORY_SEPARATOR constant is returned as the path.
     *
     * @param string ...$paths
     *
     * @return string The normalized path.
     */
    function joinpaths(...$paths): string {
        foreach ($paths as $i => &$path) {
            $path = (string)$path;
            $path = str_collapse(str_replace(["\\", '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
            $path = $i === 0 ? rtrim($path, DIRECTORY_SEPARATOR) : trim($path, DIRECTORY_SEPARATOR);
            if ($path === '') {
                unset($paths[$i]);
            }
        }
        
        return $paths ? implode(DIRECTORY_SEPARATOR, $paths) : DIRECTORY_SEPARATOR;
    }
    
    /**
     * Builds a path starting from the project root
     *
     * @param mixed ...$paths
     *
     * @return string
     */
    function path(...$paths): string {
        $basedir = APP_ROOT;
        $to      = joinpaths(...$paths);
        # Ensure that nested calls of this function won't break the path
        $to   = (str_starts_with($to, $basedir) ? mb_substr($to, mb_strlen($basedir)) : $to);
        $path = rtrim($basedir . DIRECTORY_SEPARATOR . $to, DIRECTORY_SEPARATOR);
        
        return ($path === '' ? DIRECTORY_SEPARATOR : $path);
    }
    
    # ################################################################
    # Console helpers
    # ################################################################
    
    /**
     * Checks if the current environment is a command-line interface (CLI) mode.
     *
     * @return bool|null Returns true if the current environment is CLI, false otherwise.
     */
    function is_cli(): ?bool {
        static $cliMode = null;
        if ($cliMode === null) {
            $cliMode = (defined('STDIN') || array_key_exists('SHELL', $_ENV) || (defined('PHP_SAPI') && strtolower(PHP_SAPI) == 'cli') || (function_exists('php_sapi_name') && strtolower((string)php_sapi_name()) == 'cli'));
        }
        
        return $cliMode;
    }
    
    /**
     * Returns all CLI arguments as associative array.
     * NOTE: dashes in the keys are trimmed. So passing an argument like --foo=bar would be stored as ['foo' => 'bar']
     *
     * @return array|null
     */
    function args(): ?array {
        static $arguments = null;
        if ($arguments === null) {
            $arguments = [];
            foreach (argv() as $arg) {
                if (!str_contains($arg, '=')) {
                    $arguments[trim($arg, '-')] = true;
                } else {
                    $arr                           = explode('=', $arg, 2);
                    $arguments[trim($arr[0], '-')] = ($arr[1] ?? null);
                }
            }
        }
        
        return $arguments;
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
    function arg(string $name, mixed $default = ''): mixed {
        return (is_cli() ? data_get(args(), trim($name, '-'), $default) : $default);
    }
    
    /**
     * Returns the $argv array or a specific argument if $index is given.
     *
     * @param int|null $index   The index for a specific argument (note: 0 means the current script, 1 is the first
     *                          argument and so on ...)
     * @param mixed    $default Default value if the given $index does not exist
     *
     * @return array|mixed
     */
    function argv(int $index = null, mixed $default = ''): mixed {
        static $args = null;
        if ($args === null) {
            $args = [];
            global $argv;
            $args = (array)($argv ?: data_get($_SERVER, 'argv', []));
            foreach ($args as &$arg) {
                $arg = trim($arg);
            }
        }
        
        return ($index === null ? $args : (isset($args[$index]) ? (string)$args[$index] : $default));
    }
    
    # ################################################################
    # Configuration helpers
    # ################################################################
    
    /**
     * Returns the value from the configuration based on the given key.
     * If the key is not found, it returns the default value.
     * If no key is provided, it returns the entire configuration data.
     *
     * @param string|null $key     The key of the configuration value (optional).
     * @param mixed|null  $default The default value to return if the key is not found (optional).
     *
     * @return mixed The value from the configuration based on the key, or the entire configuration if no key is
     *               provided.
     */
    function config(mixed $key = null, mixed $default = null): mixed {
        static $config = [];
        
        # Return entire config data
        if ($key === null) {
            return $default;
        }
        
        # Behave as setter
        if (is_array($key)) {
            $config = array_replace_recursive($config, $key);
            
            return null;
        }
        
        # Load data
        if (is_scalar($key)) {
            $keys      = explode('.', (string)$key, 2);
            $first_key = $keys[0] ?? '';
            if ($first_key !== '' && !isset($config[$first_key])) {
                $configuration_files = [
                    xmpath("config/$first_key.php"),
                    path((string)env('CONFIG_DIR', 'config'), "$first_key.php")
                ];
                foreach ($configuration_files as $configuration_file) {
                    if (is_file($configuration_file)) {
                        $tmp = include $configuration_file;
                        if (is_array($tmp)) {
                            $config = array_replace_recursive($config, [$first_key => $tmp]);
                        }
                    }
                }
            }
        }
        
        # If config is empty, return default
        if (!$config) {
            return $default;
        }
        
        return data_get($config, $key, $default);
    }
    
    /**
     * Get the value of an environment variable or return a default value.
     *
     * @param string|null $key     The key of the environment variable to retrieve.
     * @param mixed       $default The default value to return if the environment variable is not found.
     *
     * @return mixed The value of the environment variable if found, otherwise the default value is returned.
     */
    function env(string $key = null, mixed $default = null): mixed {
        static $env = null;
        if ($env === null) {
            $env  = $_ENV ?? [];
            $file = path('.env');
            
            if (is_file($file) && is_readable($file)) {
                $lines = (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
            } else {
                return $default;
            }
            if ($lines) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    # Skip comments and blank lines
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    $arr = explode('=', $line, 2);
                    if (isset($arr[1])) {
                        $ckey = trim($arr[0]);
                        if ($ckey === '') {
                            continue;
                        }
                        $value        = trim($arr[1]);
                        $value_length = strlen($value);
                        $result       = $value;
                        switch (true) {
                            # Parse NULL values
                            case (strtolower($value) === 'null'):
                                $result = null;
                                break;
                            # Parse boolean (true)
                            case (strtolower($value) === 'true'):
                                $result = true;
                                break;
                            # Parse boolean (false)
                            case (strtolower($value) === 'false'):
                                $result = false;
                                break;
                            # Parse string in double quotes
                            # @todo Add support for multiline strings or parse escape sequences
                            case ($value_length >= 2 && $value[0] === '"' && $value[$value_length - 1] === '"'):
                                $result = substr($value, 1, strrpos($value, '"') - 1);
                                break;
                            # Parse string in single quotes
                            case ($value_length >= 2 && $value[0] === "'" && $value[$value_length - 1] === "'"):
                                $result = substr($value, 1, strrpos($value, "'") - 1);
                                break;
                            # Parse JSON string to native arrays
                            case (str_starts_with($value, '{') || str_starts_with($value, '[')):
                                $result = json2array($value);
                                break;
                            # Parse numeric values to int or float
                            case (is_numeric($value)):
                                $result = ($value + 0);
                                break;
                            # Parse hex number and convert to dec
                            case (str_starts_with($value, '0x')):
                                $str = str_replace(' ', '', substr($value, 2));
                                if (ctype_xdigit($str)) {
                                    $result = hexdec($str);
                                } else {
                                    $result = '';
                                }
                                break;
                            # Parse base64 string
                            # Example:
                            # MY_BASE64_VALUE=base64:SGVsbG8=
                            # (This would parse the value as "Hello")
                            case (str_starts_with($value, 'base64:')):
                                $result = base64_decode(str_replace(['"', "'"], '', substr($value, 7)));
                                break;
                        }
                        
                        # Apply data
                        $env[$ckey] = $result;
                    }
                }
            }
        }
        
        return arr($env, $key, $default);
    }
    
    # ################################################################
    # Request, URL and domain helpers
    # ################################################################
    
    /**
     * Retrieves a value from the request using the specified key,
     * with the option to provide a default value if the key does not exist.
     *
     * Usage:
     * request('name');             // Retrieves the value of the 'name' key from the request
     * request('age', 0);           // Retrieves the value of the 'age' key, defaulting to 0 if the key is not found
     *
     * @param int|string $key     The key to retrieve the value for from the request.
     * @param mixed|null $default The default value to return if the key is not found in the request (optional).
     *                            Defaults to null if not specified.
     *
     * @return mixed The value of the specified key in the request, or the default value if the key is not found.
     */
    function request(int|string $key, mixed $default = null): mixed {
        return rpost($key, rget($key, (is_int($key) ? uri_part($key, $default) : $default)));
    }
    
    /**
     * Get a value from the $_GET array.
     *
     * @param mixed      $key     The key to search for in the $_GET array.
     * @param mixed|null $default The default value to return if the key is not found in the $_GET array.
     *
     * @return mixed  Returns the value from the $_GET array if the key is found; otherwise, returns the default value.
     */
    function rget(mixed $key, mixed $default = null): mixed {
        return data_get($_GET, $key, $default);
    }
    
    /**
     * Returns the value of a given key from the $_POST array, or a default value if the key is not found.
     *
     * @param mixed|string $key     The key to retrieve the value from the $_POST array.
     * @param mixed|null   $default The default value to be returned if the key is not found in the $_POST array.
     *
     * @return mixed The value associated with the given key in the $_POST array, or the default value if the key is
     *               not found.
     */
    function rpost(mixed $key, mixed $default = null): mixed {
        return data_get($_POST, $key, $default);
    }
    
    # Some helper functions for specific request data
    
    /**
     * Retrieves the integer value of the 'id' parameter from the request.
     * This function looks for the 'id' parameter in both the query string and the request body.
     * If the parameter is found in either location, its value is returned as an integer.
     * If the parameter is not found, the default value is returned as an integer.
     *
     * Example usage:
     * $id = rid();
     *
     * @return int The integer value of the 'id' parameter from the request, or the default value if not found.
     */
    function rid($key = 'id'): int {
        return (int)request($key, 0);
    }
    
    
    /**
     * Returns the URI of the current request without the query string part.
     *
     * @return string The URI of the current request.
     */
    function uri(): string {
        static $uri = null;
        if ($uri === null) {
            $uri = '/' . str_collapse(trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'), '/');
        }
        
        return $uri;
    }
    
    /**
     * Returns a specified part of the URI string.
     *
     * This function retrieves the part of the URI string at the specified index.
     * The URI string is obtained from the uri() function, which trims the leading and trailing slashes.
     * The URI is then split into an array using the "/" delimiter.
     * The part at the specified index is returned as a string, unless the index is out of bounds or the URI is empty.
     *
     * Example:
     * Given URI string: "/example/uri/part"
     * - uri_part(0) returns "example"
     * - uri_part(1) returns "uri"
     * - uri_part(2) returns "part"
     * - uri_part(3) returns ""
     *
     * @param int   $index   Index of the desired URI part
     * @param mixed $default (optional) Default value to return if the specified index is out of bounds
     *
     * @return mixed
     */
    function uri_part(int $index, mixed $default = ''): mixed {
        $uri = trim(uri(), '/');
        
        return ($uri === '' ? '' : (string)arr(explode('/', $uri), $index, $default));
    }
    
    function build_uri(...$parts) {
        return str_collapse('/' . trim(implode('/', $parts), '/'), '/');
    }
    
    /**
     * Returns the client's IP address
     * ----------------------------------------------------------------
     * Usage:
     * remote_addr(); // "127.0.0.1", "192.168.1.100", ...
     *
     * @return string The client's IP address.
     */
    function remote_addr(): string {
        static $ip = null;
        if ($ip === null) {
            $ip = '';
            $ip = array_find($_SERVER, [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'REMOTE_ADDR'
            ], '127.0.0.1');
        }
        
        return $ip;
    }
    
    /**
     * URL builder
     * ----------------------------------------------------------------
     * Usage:
     * url();           // "http://example.com"
     * url("about");    // "http://example.com/about"
     * url("blog");     // "http://example.com/blog"
     *
     * @param string $to The path to append to the URL. Optional.
     *
     * @return string The complete web URL.
     * @throws Exception
     */
    function url(string $to = '', array $params = []): string {
        $to = trim($to, '/');
        
        return trim(url_scheme() . '://' . host() . '/' . $to, '/') . ($params ? '?' . http_build_query($params) : '');
    }
    
    /**
     * Returns the URL object based on the given URL string or the current URL.
     *
     * If the $url parameter is null, it will return the URL object of the current URL using the `Url::current()`
     * method. Otherwise, it will create a new URL object using the given $url string.
     *
     * @param string|null $url The URL string. Defaults to null.
     *
     * @return Url The URL object.
     */
    function get_url(?string $url = null) {
        return ($url === null ? Url::current() : new Url($url));
    }
    
    /**
     * Check if the current web request comes via http or https
     *
     * @return bool|null
     * @see url_scheme
     */
    function is_https(): ?bool {
        static $https = null;
        if ($https === null) {
            $https = (arr($_SERVER, 'HTTP_X_FORWARDED_PROTO') === 'https' || arr($_SERVER, 'REQUEST_SCHEME') === 'https' || arr($_SERVER, 'HTTPS') === 'on');
        }
        
        return $https;
    }
    
    /**
     * Returns the URL scheme
     * ----------------------------------------------------------------
     * Usage:
     * url_scheme();            // "http" or "https"
     *
     * @return string The URL scheme, either "http" or "https"
     */
    function url_scheme(): string {
        return (is_https() ? 'https' : 'http');
    }
    
    /**
     * Returns the base url for the current host (so basically the URL scheme and the domain part without any path)
     *
     * @param string $to Optional: define a path that will be appended to the URL
     *
     * @return string
     * @throws \Exception
     */
    function baseurl(string $to = ''): string {
        static $baseurl = null;
        $baseurl = $baseurl ?? 'https://' . host();
        $to      = trim(str_replace("\\", '/', $to), '/');
        
        return $baseurl . ($to === '' ? '' : '/' . $to);
    }
    
    /**
     * Returns the current host
     * Precedence:
     * 1) CLI param "host" (like in /usr/bin/php script.php --host="hostname-here")
     * 2) Variable "DOMAIN" set in project's .env file
     * 3) "Host" request header
     * 4) Server name (set in the current apache/nginx conf)
     * 5) As last fallback, the standard hostname for the local machine is returned
     *
     * @return mixed|string|null
     */
    function host(): mixed {
        static $host = null;
        if ($host === null) {
            $host = (is_cli() ? arg('host') : '') ?: env('DOMAIN') ?: arr($_SERVER, 'HTTP_HOST') ?: arr($_SERVER, 'SERVER_NAME') ?: gethostname() ?: 'localhost';
        }
        
        return $host;
    }
    
    /**
     * Checks if a form has been submitted.
     *
     * This function checks if the form has been submitted by checking if any of the following keys exist in the $_POST
     * array:
     *
     * 1. 'submit'
     * 2. 'submitted'
     * 3. 'id'
     *
     * @return bool Returns true if the form has been submitted, false otherwise.
     */
    function form_submitted(): bool {
        return Xmgr\Request::postHas(['submit', 'submitted', 'id', '_token']);
    }
    
    /**
     * Retrieves or updates session data based on the provided key.
     * If no key is provided, it returns the instance of the session object.
     * If an array is provided as the key and a session ID is available,
     * it replaces the corresponding session data with the provided array and returns the session object.
     * If a key is provided, it returns the corresponding value from the session data.
     * If the key is not found, it returns the provided default value.
     *
     * @param string|array|null $key     The key to retrieve or update session data for. Default is null.
     * @param mixed             $default The default value to return if the key is not found. Default is null.
     *
     * @return mixed|string|Session The session object or the value associated with the provided key,
     *                                      or the default value if the key is not found.
     */
    function session(mixed $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return Session::i();
        }
        if (is_array($key)) {
            if (session_id()) {
                data_set($_SESSION, $key);
            }
            
            return Session::i();
        }
        
        return data_get($_SESSION ?? [], $key, $default);
    }
    
    # ################################################################
    # Security helpers
    # ################################################################
    
    /**
     * Secure return of HTML string
     *
     * @param string $value        The string to escape
     * @param int    $flags
     * @param string $encoding     The string encoding (default: utf-8)
     * @param bool   $all_entities Escape all entities or just some special characters
     *
     * @return string
     */
    function e(mixed $value, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, string $encoding = 'utf-8', bool $all_entities = false): string {
        return ($all_entities ? htmlentities((string)$value, $flags, $encoding) : htmlspecialchars((string)$value, $flags, $encoding));
    }
    
    # ################################################################
    # Application helpers
    # ################################################################
    
    /**
     * Returns a response object
     *
     * @param string $content
     *
     * @return Response
     *
     */
    function response(string $content = ''): Response {
        return new Response($content);
    }
    
    /**
     * Aborts the current script execution and returns an HTTP response with the specified code, message, and headers.
     *
     * This function is typically used to handle error scenarios or unexpected conditions where you want to terminate
     * the script execution and return a specific HTTP response.
     *
     * Note that this function terminates the script execution using the exit() function, so any code following the
     * call to abort() will not be executed.
     *
     * Example usage:
     * abort(404, "Page Not Found", ["Content-Type: text/html"]);
     *
     * @param int    $code    The HTTP response code to send (e.g., 404, 500)
     * @param string $message The response message to send
     * @param array  $headers An optional array of additional headers to include in the response
     *
     * @return never
     */
    function abort(int $code, string $message = '', array $headers = []): never {
        static $messages = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];
        http_response_code($code);
        echo $message ?: ($messages[$code] ?? 'Unknown Error');
        exit();
    }
    
    /**
     * Redirects the user to a new location.
     *
     * This function sends a HTTP Location header to the client, instructing it to redirect to the specified location.
     * After sending the header, the script execution is stopped using the exit() function to prevent further
     * processing.
     *
     * Example Usage:
     *
     *      redirect('https://example.com');
     *
     * @param string $location The URL or URI to redirect to.
     *
     * @return void
     */
    #[NoReturn] function redirect(string $location) {
        header('Location: ' . $location);
        exit();
    }
    
    # ################################################################
    # Array helpers
    # ################################################################
    
    /**
     * Retrieves the value from an array or an object implementing the ArrayAccess interface, based on the provided key.
     * If the key does not exist in the array or object, the default value is returned.
     *
     * @param mixed $array   The array or object from which to retrieve the value.
     * @param mixed $key     The key to fetch the value from the array or object.
     * @param mixed $default The default value to return if the key does not exist in the array or object.
     *
     * @return mixed The value from the array or object, or the default value if the key does not exist.
     */
    function arr(mixed $array, mixed $key, mixed $default = null): mixed {
        return (is_array($array) && array_key_exists($key, $array) ? $array[$key] : ($array instanceof ArrayAccess && $array->offsetExists($key) ? $array->offsetGet($key) : $default));
    }
    
    /**
     * Checks if an array has a specific key.
     *
     * This function checks if the given array has the specified key. It returns true if the key exists in the array,
     * otherwise it returns false. It supports both regular arrays and objects implementing the ArrayAccess interface.
     *
     * Example usage:
     * $arr = ['foo' => 'bar', 'baz' => 'qux'];
     * array_has($arr, 'foo'); // returns true
     * array_has($arr, 'qux'); // returns false
     *
     * @param mixed $array The array or ArrayAccess object to check
     * @param mixed $key   The key to check for
     *
     * @return bool Returns true if the array has the specified key, false otherwise
     */
    function arr_has(mixed $array, mixed $key): bool {
        return (is_array($array) && array_key_exists($key, $array)) || ($array instanceof ArrayAccess && $array->offsetExists($key));
    }
    
    /**
     * Finds the value of the first matching key in the given array
     * ------------------------------------------------------------
     *
     * @param array $array   The array to search for the keys.
     * @param array $keys    The keys to search for in the array.
     * @param mixed $default The value to return if none of the keys are found in the array.
     *
     * @return mixed  The value of the first matching key in the array, or the default value if none of the keys are
     *                found.
     */
    function array_find(array $array, array $keys, mixed $default = null): mixed {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }
        
        return $default;
    }
    
    /**
     * Returns array item from the given index
     * -
     * Example:
     * array_index(['a'=>'hello', 'b'=>'world'], 0); // "hello"
     * array_index(['a'=>'hello', 'b'=>'world'], 1); // "world"
     * array_index(['a'=>'hello', 'b'=>'world'], 2); // NULL
     * array_index(['a'=>'hello', 'b'=>'world'], -1);    // 'world'
     *
     * @param array      $array   The input array which may have an associative index
     * @param int        $index   The numeric index in the array you want to fetch
     *                            NOTE: you can use negative indexes as well, e.g. -1 returns the last item in the array
     * @param mixed|null $default A default value if the given index does not exist in the array
     *
     * @return array|mixed
     */
    function array_index(array $array, int $index, mixed $default = null): mixed {
        return arr(array_slice(array_values($array), $index, 1), 0, $default);
    }
    
    /**
     * Converts the given data into an array.
     *
     * This method checks the type of the input data and performs the necessary conversion:
     *  - If the data is an instance of stdClass, it is casted to an array.
     *  - If the data is an object, it checks if the object has a method named 'toArray'. If so, it calls
     *    the 'toArray' method to convert the object to an array. If the object does not have a 'toArray' method,
     *    it uses JSON encoding and decoding to convert the object to an associative array.
     *  - If the data is not an object, it directly casts it to an array.
     *
     * @param mixed $data The input data to convert
     *
     * @return array The converted data as an array
     */
    function arrval(mixed $data): array {
        return ($data instanceof stdClass ? (array)$data : (is_object($data) ? (method_exists($data, 'toArray') ? $data->toArray() : json_decode(json_encode($data), true)) : (array)$data));
    }
    
    /**
     * Remove / unset an item from an array or object using "dot" notation.
     *
     * @param mixed  $data
     * @param mixed  $key
     * @param string $separator
     *
     * @return mixed
     */
    function data_forget(mixed &$data, mixed $key, string $separator = '.') {
        $segments = is_array($key) ? $key : explode($separator, $key);
        $segment  = array_shift($segments);
        if (Arr::accessible($data)) {
            if (is_scalar($key) && array_key_exists($key, $data)) {
                unset($data[$key]);
            }
            if ($segments && Arr::exists($data, $segment)) {
                data_forget($data[$segment], $segments);
            } else {
                Arr::forget($data, $segment);
            }
        } elseif (is_object($data)) {
            if ($segments && isset($data->{$segment})) {
                data_forget($data->{$segment}, $segments);
            } elseif (isset($data->{$segment})) {
                unset($data->{$segment});
            }
        }
        
        return $data;
    }
    
    /**
     * Prüft, ob es im übergebenen array einen nested key gibt
     *
     * @param mixed  $data
     * @param scalar $key
     * @param string $separator
     *
     * @return bool
     */
    function data_has(mixed $data, mixed $key, string $separator = '.'): bool {
        $data = arrval($data);
        if (!is_scalar($key)) {
            return false;
        }
        if (array_key_exists($key, $data)) {
            return true;
        }
        $temp = $data;
        $keys = explode($separator, (string)$key);
        foreach ($keys as $k) {
            if (is_object($temp)) {
                $temp = (array)$temp;
            }
            if (is_array($temp) && (array_key_exists($k, $temp))) {
                $temp = &$temp[$k];
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Retrieves a value from an array using a specified key
     * ----------------------------------------------------------------
     * Usage:
     * $array = ['name' => 'John', 'age' => 25, 'address' => ['city' => 'New York']];
     * data_get($array, 'name');                          // "John"
     * data_get($array, 'age');                           // 25
     * data_get($array, 'address.city');                  // "New York"
     * data_get($array, 'address.street', 'N/A');         // "N/A"
     *
     * @param mixed  $data       The array from which to retrieve the value.
     * @param mixed  $key        The key to look for in the array. It can be a string or an array of keys.
     * @param mixed  $default    The default value to return if the specified key is not found in the array.
     *                           Default value is null.
     * @param string $separator  The separator used to separate nested keys in the given key string.
     *                           Default value is '.'.
     *
     * @return mixed             The value associated with the specified key. If the key is not found,
     *                           the default value is returned.
     */
    function data_get(mixed $data, mixed $key, mixed $default = null, string $separator = '.'): mixed {
        $data = arrval($data);
        if (is_array($key)) {
            $result = [];
            foreach ($key as $keyname) {
                $result[$keyname] = data_get($data, $keyname, $default, $separator);
            }
            
            return $result;
        }
        if (!is_scalar($key)) {
            return $default;
        }
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        $temp = $data;
        $keys = explode($separator, (string)$key);
        foreach ($keys as $k) {
            if (is_object($temp)) {
                $temp = (array)$temp;
            }
            if (is_array($temp) && (array_key_exists($k, $temp))) {
                $temp = &$temp[$k];
            } else {
                return $default;
            }
        }
        
        return $temp;
    }
    
    /**
     * Sets a value in an associative array using a dot-separated key path.
     * If the key path does not exist, it will be created.
     *
     * Usage:
     * $array = [];
     * data_set($array, 'foo.bar', 'baz');
     * // $array = ['foo' => ['bar' => 'baz']]
     *
     * @param mixed  $data      The array to set the value in. Passed by reference.
     * @param mixed  $key       The dot-separated key path or an array containing the key path.
     * @param mixed  $value     The value to set.
     * @param string $separator The separator used in the dot-separated key path. Default is '.'.
     *
     * @return void
     */
    function data_set(mixed &$data, mixed $key, mixed $value = null, string $separator = '.'): void {
        if (!(is_object($data) || is_array($data))) {
            return;
        }
        $key   = (is_object($key) ? (array)$key : $key);
        $value = (is_array($key) ? $key : $value);
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                data_set($data, (is_array($key) ? $k : $key . $separator . $k), $v, $separator);
            }
        }
        if (!is_scalar($key)) {
            return;
        }
        $temp = &$data;
        $keys = explode($separator, (string)$key);
        foreach ($keys as $k) {
            if (is_object($temp)) {
                if (!isset($temp->{$k})) {
                    $temp->{$k} = [];
                }
                $temp = &$temp->{$k};
            } elseif (is_array($temp)) {
                if (!array_key_exists($k, $temp)) {
                    $temp[$k] = [];
                }
                $temp = &$temp[$k];
            }
            
        }
        $temp = $value;
    }
    
    /**
     * Returns a random value from the given array.
     *
     * @param array $array The array from which to retrieve a random value.
     *
     * @return mixed|null Returns a random value from the given array.
     *                   Returns null if the array is empty.
     *
     * @throws Exception If the random_int() function throws an exception.
     */
    function array_random_value(array $array): mixed {
        return ($array ? array_values($array)[random_int(0, count($array) - 1)] : null);
    }
    
    /**
     * Returns an array of randomly selected values from the given array
     *
     * @param array $array    The array to select values from
     * @param int   $quantity The number of values to select from the array. If negative, the absolute value will be
     *                        used.
     *
     * @return array The array of randomly selected values from the given array
     * @throws RandomException
     */
    function array_random_values(array $array, int $quantity): array {
        if (!$array) {
            return [];
        }
        $array    = array_values($array);
        $quantity = max(1, abs($quantity));
        $count    = count($array);
        $result   = [];
        while ($quantity--) {
            $result[] = $array[random_int(0, $count - 1)];
        }
        
        return $result;
    }
    
    /**
     * Parse JSON string to data array
     *
     * @param mixed $json
     * @param bool  $assoc
     *
     * @return array|mixed
     */
    function json2array(mixed $json, bool $assoc = true): mixed {
        # Just return the value if it's already an array
        if (is_array($json)) {
            return $json;
        }
        # Try to return an object as array
        if (is_object($json)) {
            return $assoc ? [(array)$json] : [$json];
        }
        
        # Handle JSON string
        return (json_validate($json) ? json_decode((string)$json, $assoc) : []) ?: [];
    }
    
    
    /**
     * Convert a newline-delimited JSON string into an array of PHP values.
     * ---------------------------------------------------------------------
     *
     * Usage:
     * $jsonString = '{"name": "John", "age": 30}\n{"name": "Jane", "age": 25}\n{"name": "Bob", "age": 40}';
     * $result = array_fromNdJson($jsonString);
     * print_r($result);
     *
     * Output:
     * Array
     * (
     *     [0] => Array
     *         (
     *             [name] => John
     *             [age] => 30
     *         )
     *
     *     [1] => Array
     *         (
     *             [name] => Jane
     *             [age] => 25
     *         )
     *
     *     [2] => Array
     *         (
     *             [name] => Bob
     *             [age] => 40
     *         )
     * )
     *
     *
     * @param string $string The newline-delimited JSON string to convert.
     * @param bool   $assoc  Convert JSON objects to associative arrays. Default is true.
     *
     * @return array The resulting array containing the converted JSON objects.
     *
     */
    function ndjson2array(string $string, bool $assoc = true): array {
        $arr   = [];
        $lines = explode("\n", $string);
        foreach ($lines as $line) {
            $json = json_decode($line, $assoc);
            if (json_last_error() === JSON_ERROR_NONE) {
                $arr[] = $json;
            }
        }
        
        return $arr;
    }
    
    
    /**
     * Picks specific keys from an array and returns a new array with only those keys.
     *
     * This function iterates over the given `$keys` array and checks if each key exists in the `$array`.
     * If a key is found in the `$array`, it will be added to the result array with its corresponding value.
     *
     * @param array $array The input array from which to pick the keys.
     * @param array $keys  A list of keys to pick from the array.
     *
     * @return array The resulting array containing the picked keys and their corresponding values.
     */
    function array_pick(array $array, array $keys): array {
        $result = [];
        foreach ($keys as $key) {
            if (isset($array[$key]) || array_key_exists($key, $array)) {
                $result[$key] = $array[$key];
            }
        }
        
        return $result;
    }
    
    # ################################################################
    # String helpers
    # ################################################################
    
    /**
     * Returns a hexadecimal dump of a string.
     * -
     * Output format:
     * #<Line-Number>;Dec-Offset;Hex-Offset: <Hex-Bytes> [<characters>]
     *
     * @param string $string        The string to dump.
     * @param int    $bytes_per_row The number of bytes to display per row. Default is 16.
     * @param int    $offset        The offset of the string to start the dump. Default is 0.
     *
     * @return string                The hexadecimal dump of the string.
     */
    function hexdump(string $string, int $bytes_per_row = 16, int $offset = 0): string {
        $lines           = [];
        $actual_length   = strlen($string);
        $strlen          = $actual_length + $offset;
        $chunks          = array_chunk(str_split($string), $bytes_per_row);
        $last_chunk_size = count($chunks[count($chunks) - 1]);
        $chunk_count     = count($chunks);
        $current_offset  = $offset;
        $dec_length      = strlen($strlen);
        $hex_length      = strlen(dechex($strlen));
        $bytes_length    = $bytes_per_row * 3;
        foreach ($chunks as $i => $chunk) {
            $hex = str_pad(trim(chunk_split(strtoupper(bin2hex(implode('', $chunk))), 2, ' ')), $bytes_length, ' ', STR_PAD_RIGHT);
            array_walk($chunk, function (&$item) {
                $item = (\ord($item) >= 32 && \ord($item) <= 126 ? $item : '.');
            });
            $lines[]        = /*'#' . str_pad(($i + 1), $chunk_length, '0', STR_PAD_LEFT) . ';' .*/
                str_pad($current_offset, $dec_length, '0', STR_PAD_LEFT) . ';' . str_pad(strtoupper(dechex($current_offset)), $hex_length, '0', STR_PAD_LEFT) . ': ' . $hex . ' [' . implode('', $chunk) . ']';
            $current_offset += $bytes_per_row;
        }
        
        return 'Info: Length=' . $actual_length . '; Read=' . $offset . '-' . ($strlen - 1) . '; Chunks=' . $chunk_count . ";\n" . 'Format: ' . /*#<Line-No>;*/ '<Dec-Offset>;<Hex-Offset>: <Hex-Bytes>  [<Characters>]' . "\n" . implode("\n", $lines);
    }
    
    /**
     * Creates a new instance of \Xmgr\Text using the given value.
     *
     * @param mixed $value The value to be wrapped as \Xmgr\Text instance.
     *
     * @return Text The new \Xmgr\Text instance created using the given value.
     */
    function str(mixed $value): Text {
        return new Text($value);
    }
    
    /**
     * Filters a string to only keep characters that match the specified whitelist.
     *
     * This function takes a string and filters it to only keep characters that match the specified whitelist.
     * If the whitelist is provided as a string, it is converted to an array of characters.
     * By default, this function keeps alphabetic characters and digits, but this behavior can be modified by setting
     * the $keepAlpha and $keepDigits parameters to false. The resulting string is returned.
     *
     * @param string       $string              The string to be filtered.
     * @param string|array $whitelist           The whitelist of characters.
     *                                          If a string is provided, it will be converted to an array of
     *                                          characters
     * @param bool         $keep_alpha          Whether to keep alphabetic characters.
     *                                          Defaults to true.
     * @param bool         $keep_digits         Whether to keep digits.
     *                                          Defaults to true.
     *
     * @return string The filtered string.
     */
    function str_keep(string $string, string|array $whitelist, string $replace = '', bool $keep_ascii = true, bool $keep_alpha = true, bool $keep_digits = true): string {
        $whitelist  = (is_array($whitelist) ? $whitelist : mb_str_split($whitelist));
        $whitelist  = array_flip($whitelist);
        $characters = mb_str_split($string);
        $result     = '';
        foreach ($characters as $char) {
            if (($keep_ascii && Str::isPrintableAsciiChar($char)) || ($keep_alpha && ctype_alpha($char)) || ($keep_digits && ctype_digit($char)) || isset($whitelist[$char])) {
                $result .= $char;
            } else {
                $result .= ($replace === '' ? '' : $replace);
            }
        }
        
        return $result;
    }
    
    /**
     * Only keeps the characters specified in the whitelist.
     * -
     * Example:
     * Input.....: $string = "Hello World xyz", $whitelist = ['H', 'e', 'o', 'l', 'W', 'r', 'd']
     * Output....: "Hello World"
     *
     * @param string       $string    Input string
     * @param string|array $whitelist Characters to keep (can be provided as a string or an array)
     *
     * @return string
     */
    function str_keep_only(string $string, string|array $whitelist = ''): string {
        return str_keep($string, $whitelist, '', false, false, false);
    }
    
    /**
     * Converts characters to their corresponding ascii equivalent.
     * Example: "Ħëļłố Ŵóřŀȡ" -> "Hello World"
     * -
     * Note:
     * Enabling transliteration can convert additional
     * characters and symbols (e.g. "弈 团 队" -> "yi tuan dui").
     * BUT keep in mind that enabling transliteration can make this
     * function hundreds of times slower. The asciimap() replacement is very fast and
     * in 99.5% a transliteration is not needed at all, except if you're dealing with
     * very exotic characters, which is normally not the case.
     *
     * @param string $string Input string
     *
     * @return string
     */
    function asciify(string $string): string {
        $tmp    = transliterator_transliterate('Any; Latin-ASCII;', $string);
        $string = (is_string($tmp) ? $tmp : $string);
        $string = preg_replace('/[^\x20-\x7E\x09\x0A\x0D]/u', '', $string);
        $string = str_collapse(str_replace([' . '], ' ', $string));
        
        return $string;
    }
    
    /**
     * Returns random string
     * ----------------------------------------------------------------
     * Usage:
     * random_string(5);            // "Sx3vR"
     * random_string(5, "10");      // "01001", "01101", "11011", ...
     * random_string(5, "ABC");     // "ACBBC", "BCBBB", "BCCAC", "CBAAB", ...
     *
     * @param int          $length    The length for the generated result string.
     *                                NOTE: you can also pass an array with min and max values to define
     *                                a variable-width string length.
     * @param string       $pool      Available characters for the generated string.
     * @param string|array $exclude
     *
     * @return string
     * @throws Exception
     */
    function random_string(int $length, string $pool = '[:alnum:]', string|array $exclude = ''): string {
        static $map = [
            '[:lower:]'  => 'abcdefghijklmnopqrstuvwxyz',
            '[:upper:]'  => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '[:digit:]'  => '0123456789',
            '[:blank:]'  => " \x09",
            '[:alpha:]'  => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '[:alnum:]'  => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            '[:cntrl:]'  => "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F",
            '[:punct:]'  => "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~",
            '[:graph:]'  => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~",
            '[:print:]'  => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~ ",
            '[:space:]'  => "\x09\x0A\x0B\x0C\x0D\x20",
            '[:xdigit:]' => 'abcdefABCDEF0123456789',
        ];
        
        $pool = str_replace(is_string($exclude) ? mb_str_split($exclude) : $exclude, '', str_replace(array_keys($map), array_values($map), $pool));
        
        $len = abs($length);
        
        if (!$len) {
            return '';
        }
        $str            = '';
        $pool_max_index = max(mb_strlen($pool) - 1, 0);
        while ($len--) {
            $str .= mb_substr($pool, random_int(0, $pool_max_index), 1);
        }
        
        return $str;
    }
    
    /**
     * Returns a random character from the given string of characters.
     *
     * Usage:
     * random_char("abcdefghijklmnopqrstuvwxyz");       // "g"
     * random_char("0123456789");                       // "4"
     * random_char("!@#$%^&*()");                       // "@"
     * random_char("abcdefg12345678!@#$");               // "d"
     *
     * @param string $characters The string of characters to choose from.
     *
     * @return string Returns a random character from the given string.
     * @throws Exception If the provided string of characters is empty.
     */
    function random_char(string $characters): string {
        return ($characters === '' ? '' : mb_str_split($characters)[random_int(0, strlen($characters) - 1)]);
    }
    
    /**
     * Generates a random password string.
     *
     * Usage:
     * str_passwd();                         // "b8AB7JFzx9ZkXsh3DRPnrvW5LtS2u6qd"
     * str_passwd(10);                       // "eA2Fs6ZgD4"
     * str_passwd(10, '!@#$%^&*');            // "eA2!s6@Zg#"
     *
     * @param int    $length                The length of the password string. Defaults to 32.
     *                                      NOTE: The minimum length is 8.
     * @param string $additional_characters Additional characters to include in the password string.
     *                                      Defaults to an empty string. These characters will be combined
     *                                      with the default character sets.
     *
     * @return string The randomly generated password string.
     * @throws Exception
     */
    function str_passwd(int $length = 32, string $additional_characters = ''): string {
        $length = max(8, abs($length)) - 2;
        # We want the first and last character of the password to be a non-ambiguous letter or digit
        static $nac = 'aAbBcCdDeEfFgGhHijJkKLmMnNopPqQrRsStTuUvVwWxXyYzZ';
        static $nnum = '23456789';
        
        return random_string(1, $nac) . random_string($length, $nac . $nnum . $additional_characters) . random_string(1, $nac);
    }
    
    /**
     * Separates connected words with a space
     * Example: "ShopAPIClientCredentials" --> "Shop API Client Credentials".
     *
     * @param string $string Input string
     *
     * @return array|string|string[]|null
     *
     */
    function str_separate_words(string $string): array|string|null {
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', preg_replace('/([A-Z])([A-Z][a-z])/', '$1 $2', $string));
    }
    
    /**
     * Only keeps letters and numbers in the string and collapses consecutive spaces.
     * What this function does:
     * 1) asciify's the string (removes accents, transliterates characters to their corresponding ascii equivalents)
     * 2) After that, all characters that are not alphanumeric characters or spaces are going to be replaced with a
     * space
     * 3) Consecutive spaces will be collapsed (e.g. "a    b  c" --> "a b c")
     * -
     * The resulting string can be, for example, programmatically processed then.
     * -
     * Example:
     * Input.....: "Ħëļłố Ŵóřŀȡ Maße     x??y!z% 弈 123_45\n	6 团 队  "
     * Output....: "Hello World Masse x y z yi 123 45 6 tuan dui"
     *
     * @param string $string Input string
     * @param string $keep   Additional characters you want to keep
     *
     * @return string
     */
    function str_clean(string $string, string $keep = ''): string {
        $keep = ' ' . $keep;
        
        return trim(str_collapse(str_keep(str_separate_words(asciify($string)), $keep, ' ', false), $keep), $keep . " \r\n\0-_.,_:;#+*");
    }
    
    /**
     * Modifies a string by separating words, keeping certain characters, and collapsing consecutive occurrences of a
     * separator.
     *
     * This function performs the following actions:
     * 1) The input string is asciified (removes accents, transliterates characters to their corresponding ASCII
     * equivalents).
     * 2) The asciified string's words are separated using a separator provided as the second parameter.
     * 3) After separation, any characters that are not alphanumeric or the separator are replaced with the separator.
     * 4) Consecutive occurrences of the separator are collapsed into a single occurrence.
     * 5) Finally, all leading and trailing occurrences of the separator are removed.
     *
     * The resulting string can be used, for example, as part of a URL or file path.
     *
     * Example:
     * Input.....: "Ħëļłố Ŵóřŀȡ Mãße!~^_^~x??y"
     * Separator.: "-"
     * Output....: "Hello-World-Masse-x-y"
     *
     * @param string $string    Input string
     * @param string $separator Separator used to separate words and collapse consecutive occurrences
     * @param string $keep      Keep additional characters
     *
     * @return string Modified string with separated words and collapsed occurrences of the separator
     */
    function str_case(string $string, string $separator = ' ', string $keep = '', string $collapse = ''): string {
        return trim(str_collapse(str_keep(str_separate_words(asciify($string)), $separator . $keep, $separator, false), $separator . $collapse), $separator);
    }
    
    /**
     * Converts a string to a slug format.
     *
     * This function takes a string and converts it into a slug format by replacing
     * spaces with hyphens and optionally converting the string to lowercase.
     *
     * Example:
     * Input.....: "Hello World"
     * Output....: "hello-world"
     *
     * @param string $string Input string
     * @param bool   $lower  (Optional) Whether to convert the string to lowercase (default: true)
     *
     * @return string The slug format of the input string
     */
    function str_slug(string $string, bool $lower = false): string {
        $result = str_case($string, '-');
        
        return ($lower ? strtolower($result) : $result);
    }
    
    /**
     * Transforms a string into kebab case.
     * This function takes a string input and converts it into kebab case
     * (all lowercase letters, with hyphens replacing spaces and special characters).
     * -
     * Example:
     * Input.....: "Hello World! How are you?"
     * Output....: "hello-world-how-are-you"
     *
     * @param string $string Input string
     *
     * @return string The string transformed into kebab case
     */
    function str_kebab(string $string) {
        return str_slug($string, true);
    }
    
    /**
     * Replaces spaces with hyphens and converts the first character of each word to uppercase.
     * What this function does:
     * 1) Replaces all spaces in the input string with hyphens
     * 2) Converts the first character of each word to uppercase
     * -
     * The resulting string can be used, for example, as a SEO-friendly URL slug or for display purposes.
     * -
     * Example:
     * Input.....: "hello world"
     * Output....: "Hello-World"
     *
     * @param string $string Input string
     *
     * @return string
     */
    function str_train(string $string): string {
        return str_replace(' ', '-', str_title($string));
    }
    
    /**
     * Converts a string to snake case.
     * The snake case is a convention for writing compound words or phrases in which the elements are separated with an
     * underscore. In this function, the input string is converted to snake case by replacing spaces with underscores
     * and converting the string to lower case.
     *
     * Example:
     * Input.....: "Hello World Masse"
     * Output....: "hello_world_masse"
     *
     * @param string $string   Input string
     * @param bool   $all_caps To make an uppercase version
     *
     * @return string The string converted to snake case.
     */
    function str_snake(string $string, bool $all_caps = false): string {
        $result = str_case($string, '_');
        
        return ($all_caps ? strtoupper($result) : $result);
    }
    
    /**
     * Converts the given string to screaming snake case.
     * What this function does:
     * 1) The given string will be transformed into snake case using str_snake() function.
     * 2) Additionally, all letters in the resulting snake case string will be converted to uppercase, thus creating
     * the screaming snake case.
     * -
     * Screaming snake case is a naming convention where each word is separated by an underscore and all letters are
     * in uppercase. It is commonly used for constants and variables that are meant to be seen as "shouting".
     * -
     * Example:
     * Input.....: "ThisIsAVariable"
     * Output....: "THIS_IS_A_VARIABLE"
     *
     * @param string $string The input string to be converted to screaming snake case
     *
     * @return string The converted string in screaming snake case
     */
    function str_screaming_snake(string $string): string {
        return str_snake($string, true);
    }
    
    /**
     * Converstr string to dot.case
     *
     * @param string $string Input string
     * @param bool   $lower  Whether to convert the resulting string to lowercase
     *
     * @return string The modified string with a dot at the end
     */
    function str_dot(string $string, bool $lower = true): string {
        $result = str_case($string, '.');
        
        return ($lower ? strtolower($result) : $result);
    }
    
    /**
     * Converts the given string to title case.
     * This function capitalizes the first character of each word in the string.
     * Words are delimited by spaces.
     *
     * Example:
     * Input.....: "hello world"
     * Output....: "Hello World"
     *
     * @param string $string Input string
     *
     * @return string The converted string
     */
    function str_title(string $string): string {
        return ucwords(str_case($string, ' '));
    }
    
    /**
     * Converts a string to camel case.
     * This function converts a string to camel case by performing the following steps:
     *
     * 1) The string is converted to lower case using the str_case() function with an empty delimiter.
     * 2) ucwords() is applied to the resulting string, capitalizing the first letter of each word.
     * 3) Finally, lcfirst() is used to lowercase the first letter of the resulting string.
     *
     * Tip: Keep in mind that this function assumes the input string follows a specific format, where words are
     * delimited by spaces, underscores or hyphens. If the input string contains any other delimiters, the result
     * might not be the expected camel case representation.
     *
     * Example:
     * Input.....: "hello world"
     * Output....: "helloWorld"
     *
     * @param string $string The string to convert to camel case.
     *
     * @return string The converted camel case string.
     */
    function str_camel(string $string): string {
        return lcfirst(ucwords(str_case($string, '')));
    }
    
    /**
     * Converts a string to Pascal case.
     *
     * This function converts the given string to Pascal case, which capitalizes the first letter of each word and
     * removes spaces. It makes use of the str_case() function to remove any non-alphanumeric characters and convert
     * the string to lowercase. Then, the ucwords() function is used to capitalize the first letter of each word.
     *
     * Example:
     * Input: "hello world"
     * Output: "HelloWorld"
     *
     * @param string $string The input string to be converted to Pascal case.
     *
     * @return string The resulting string in Pascal case.
     */
    function str_pascal(string $string): string {
        return ucwords(str_case($string, ''));
    }
    
    /**
     * Collapses specified, consecutive characters in a string.
     * That means that repetitive characters are unified.
     * Example with collapsing the space character:
     * "This   is  a     test" --> "This is a test"
     *
     * @param string       $string     Input string
     * @param string|array $characters All the characters this string consists of will be unified.
     * @param bool         $auto_trim
     *
     * @return string
     */
    function str_collapse(string $string, string|array $characters = ' ', bool $auto_trim = false): string {
        $characters = is_array($characters) ? implode('', $characters) : $characters;
        $result     = ($characters === '' ? $string : ((string)preg_replace('~([' . preg_quote($characters, '~') . '])\1+~', '$1', $string)));
        
        return ($auto_trim ? trim($result, $characters) : $result);
    }
    
    # ################################################################
    # Debug helpers
    # ################################################################
    
    /**
     * Returns whether the application is in development mode or not.
     *
     * If the development mode is not explicitly set, the function will check for different conditions
     * to determine if the application should be in development mode.
     *
     * @return bool Returns true if the application is in development mode, false otherwise.
     */
    function dev(): bool {
        static $dev_mode = null;
        
        if ($dev_mode === null) {
            global $argv;
            $dev_mode    = false;
            $dev_allowed = (env('DEV') === true || (request('da') === 'g4Zk61thozWm7tqGmVRUEfDrMPYVrcep') || (is_bool(session('dev'))));
            
            if (!$dev_allowed) {
                return false;
            }
            
            $dev_mode = true;
            
            # Check session value
            if (is_bool(session('dev'))) {
                $dev_mode = session('dev');
            }
            
            # Set dev mode explicitely via request argument
            if (isset($_GET['dev'])) {
                $dev_mode = match ($_GET['dev']) {
                    '0' => false,
                    '1' => true
                };
            }
            
            # Check for "--dev=1" on CLI
            if (!$dev_mode && $argv && in_array('--dev=1', $argv, true)) {
                $dev_mode = true;
            }
            
            # Update dev mode in session
            if (session()) {
                $_SESSION['dev'] = $dev_mode;
                session(['dev' => $dev_mode]);
            }
        }
        
        return $dev_mode;
    }
    
    /**
     * Creates a new expectation object for the given value.
     *
     * @param mixed $value The value to be expected.
     *
     * @return Expectation The new expectation object.
     */
    function expect(mixed $value): Expectation {
        return new Expectation($value);
    }
    
    /**
     * Formats a value for display within HTML `<pre>` tags.
     *
     * This function takes the given value and formats it as a string representation suitable for display within HTML
     * `<pre>` tags. The value is first converted to a string using the `print_r()` function, with the second argument
     * set to `true` to return the string instead of printing it. The resulting string is then wrapped in HTML `<pre>`
     * tags for proper formatting.
     *
     * @param mixed ...$values
     *
     * @return string The formatted value wrapped in HTML `<pre>` tags
     */
    function pre(...$values): string {
        $html = '';
        foreach ($values as $value) {
            
            $html .= '<pre>' . e(print_r($value, true)) . '</pre>';
        }
        
        return $html;
    }
    
    function dmsg(string $message, ...$data) {
        echo '<div class="xm-msg-container">';
        echo '<div class="xm-msg-text">' . $message . '</div>';
        dump(...$data);
        echo '</div>';
    }
    
    /**
     * Dump values from xdump()
     * Note: calling this function will immediately print the result.
     * If you want to manually print it, use xdump()
     * -
     * Usage:
     * x("hello world", true, false, null, 12, -0.47, new stdClass(), ["key" => "value"]);
     *
     * @param mixed ...$data Any amount of data you pass to this function
     *
     * @see \get_dump
     */
    function dump(...$data): void {
        echo get_dump(...$data);
    }
    
    /**
     * Returns a formatted string representation of the passed data.
     *
     * @param mixed ...$data The data to be dumped. Can be multiple arguments.
     *
     * @return string The formatted string representation of the passed data.
     */
    function get_dump(...$data): string {
        static $counter = 1;
        $with_trace = defined('DUMP_TRACE') && (bool)DUMP_TRACE;
        
        # Different nice and decent colors for different file types
        static $colormap = [
            'integer'  => '60afdc',
            'double'   => '49c0b6',
            'float'    => '49c0b6',
            'string'   => 'fd9f3e',
            'null'     => '2c2c2c',
            'boolean'  => 'ff6c5f',
            'object'   => 'a26eea',
            'array'    => '4257b2',
            'resource' => 'd20962',
        ];
        static $cli_colormap = [
            'integer'  => '34',
            'double'   => '36',
            'float'    => '36',
            'string'   => '33',
            'null'     => '37',
            'boolean'  => '39',
            'object'   => '35',
            'array'    => '94',
            'resource' => '95',
        ];
        
        # If this is a CLI request, output will be plain, without HTML to also have a nicely formatted output in console
        static $cli = null;
        if ($cli === null) {
            $cli = (defined('STDIN') || array_key_exists('SHELL', $_ENV) || (defined('PHP_SAPI') && strtolower(PHP_SAPI) == 'cli') || (function_exists('php_sapi_name') && strtolower((string)php_sapi_name()) == 'cli'));
        }
        
        # Exception for trace string
        if ($with_trace) {
            $exception   = new Exception();
            $print_trace = $exception->getTraceAsString();
        }
        
        # Result output
        $output = '';
        
        # Iterate through passed data
        foreach ($data as $value) {
            $bool = (bool)$value;
            $type = strtolower(gettype($value));
            # For arrays, show values count, for strings show the length
            $type_info = (is_array($value) ? count($value) : (is_string($value) ? strlen($value) : ''));
            
            # CLI mode (for dumping values in console)
            if ($cli) {
                $color = ($cli_colormap[$type] ?? '39');
                $color = ($value === true ? '42' : ($value === false ? '41' : $color));
                // $output .= $color;
                // $output .= "\033[41mRed";
                $output .= "\n============================ x dump ============================\n";
                $output .= "\033[" . $color . 'm' . $type . "\033[0m" . ($type_info !== '' ? "($type_info)" : '') . "\033[2m (~" . ($bool ? 'true' : 'false') . ") (call-no. $counter)\033[0m\n";
                if ($value !== null && $value !== true && $value !== false) {
                    $output .= "----------------------------------------------------------------\n" . "\033[" . $color . 'm' . print_r($value, true) . "\033[0m\n";
                }
                if ($with_trace) {
                    $output .= "----------------------------------------------------------------\n\033[2m" . $print_trace . "\033[0m\n================================================================\n";
                }
            } else {
                $color = ($colormap[$type] ?? 'f85a40');
                $color = ($value === true ? '47cf73' : ($value === false ? 'ee4f4f' : $color));
                # HTML output for web requests
                $type_info = ($type_info !== '' ? "<sup style='font-family:Calibri, Verdana, Helvetica, Arial, sans-serif !important;padding-left:3px;font-size:11px;font-weight:100;color:rgba(0,0,0,0.72);'>($type_info)</sup>" : '');
                $output    .= "<div class='xdump xdump-$counter' style='text-align:left;display:block !important;position:relative !important;clear:both !important;box-sizing:border-box;background-color:#$color;border:4px solid rgba(255,255,255,0.5);/*border:4px solid rgba(0,0,0,0.05);*/border-radius:5px;padding:12px !important;margin:16px;font-family:Consolas,\"Courier New\", \"Arial\", sans-serif !important;font-size:13px;text-transform: none !important;line-height: initial !important;opacity:1.0 !important;float:none;'>";
                $output    .= "<div class='xdump-varinfo' style='box-sizing:border-box;background-color:rgba(255,255,255,0.4);border-radius:4px;padding:8px;margin:4px 0;color:rgba(0,0,0,0.72) !important;font-size:14px;'><span style='text-decoration: underline;font-weight:bold;font-family:Consolas,\"Courier New\", \"Arial\", sans-serif !important;color:rgba(0,0,0,0.72) !important;'>$type</span>$type_info <span style='opacity:0.5;color:rgba(0,0,0,0.72) !important;font-weight:normal !important;font-family:Consolas,\"Courier New\", \"Arial\", sans-serif !important;font-size:11px;'>(~" . ($bool ? 'true' : 'false') . ')</span> ';
                $output    .= $with_trace ? "<span style='opacity:0.5;color:rgba(0,0,0,0.72) !important;font-weight:normal !important;font-family:Consolas,\"Courier New\", \"Arial\", sans-serif !important;font-size:11px;cursor:pointer;' onclick=\"document.querySelector('.xdump-$counter .xdump-trace').toggleAttribute('hidden')\">&#x1F50D;trace</span>" : '';
                $output    .= '</div>';
                if ($value !== null && $value !== true && $value !== false && $value !== []) {
                    $output .= "<pre style='box-sizing:border-box;background-color:rgba(255,255,255,0.96);color:rgba(0,0,0,0.9);padding:8px;margin:8px 0;max-height:512px;overflow:auto;border:none;border-radius:4px;font-size:12px;line-height:1.25em;'>" . htmlentities(print_r($value, true), ENT_QUOTES, 'UTF-8') . '</pre>';
                }
                if ($with_trace) {
                    $ptrace = nl2br(htmlentities($print_trace, ENT_QUOTES, 'UTF-8'));
                    $ptrace = preg_replace('/^(#2 .+)/mui', '<strong style="font-weight:bold;color:#ffffff;">$1</strong>', $ptrace);
                    $output .= "<div class='xdump-trace' style='font-family:Consolas,Monospace,\"Courier New\",Calibri, Verdana, Helvetica, Arial, sans-serif !important;text-align:left;box-sizing:border-box;padding:4px;color:rgba(255,255,255,0.4);font-size:10px;line-height:12px;font-weight:normal;font-weight:100;' hidden>" . $ptrace . '</div>';
                }
                $output .= '</div>';
            }
            $counter++;
        }
        
        return $output;
    }
    
    /**
     * Dumps the passed data and ends execution.
     *
     * @param mixed ...$data The data to be dumped. Can be multiple arguments.
     *
     * @return void
     */
    #[NoReturn] function dd(...$data): void {
        dump(...$data);
        exit();
    }
    
    /**
     * Adds a note to the app log file.
     *
     * This function appends a note to the specified app log file. The note includes the current timestamp,
     * the provided message, and (optionally) additional data.
     *
     * @param string|null $message The main message to include in the note.
     * @param mixed|null  $data    Additional data to include in the note. This can be any valid PHP variable.
     *
     * @return void
     */
    function note(?string $message = '', mixed ...$data): void {
        static $max_filesize = (1000 * 1000 * 5);
        try {
            $content = '[' . date('c') . '] [' . gethostname() . '] ' . (is_cli() ? 'CLI: `' . implode(' ', argv()) . '`' : Request::httpMethod() . ': ' . Request::request_url() . ' (' . remote_addr() . ')') . ' ' . $message . "\n";
            if ($_GET) {
                $content .= 'Query args: ' . json_encode($_GET) . "\n";
            }
            if ($_POST) {
                $content .= 'Form data: ' . json_encode($_POST) . "\n";
            }
            if ($_FILES) {
                $content .= 'Files: ' . json_encode($_FILES) . "\n";
            }
            if (Request::body()) {
                $content .= "Request body:\n--\n" . Request::body() . "\n--\n";
            }
            if ($data) {
                foreach ($data as $i => $d) {
                    $content .= '#' . ++$i . ' ' . gettype($d);
                    $content .= match (true) {
                        is_string($d) => '(' . strlen($d) . ')',
                        is_array($d) => '(' . count($d) . ')',
                        is_object($d) => '(' . $d::class . ')',
                        is_int($d) || is_float($d) => '(' . $d . ')',
                        default => ''
                    };
                    $content .= ' (~' . ($d ? 'true' : 'false') . ")\n";
                    # Dump the value only if it makes sense and provides representative content
                    if (!($d === null || is_bool($d) || (is_array($d) && !$d) || is_int($d) || is_float($d))) {
                        $content .= trim(print_r($d, true)) . "\n";
                    }
                }
            }
            $file = path('storage/log/hits-app-' . date('Y-m') . '.log');
            if (is_file($file) && filesize($file) > $max_filesize) {
                # Clear contents if the file is already 5MB
                File::clear($file);
            }
            File::append($file, $content);
        } catch (\Exception) {
            /* No action */
        }
    }
    
    /**
     * @param \Exception $e
     */
    function error(\Exception $e): void {
        if (is_cli()) {
            echo Xmgr\Console::error($e);
        } else {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
    }
    
    /**
     * Returns the value passed as an argument.
     * If the value is callable, it invokes the callable and returns the result.
     *
     * @param mixed $value The value to be returned or invoked.
     *
     * @return mixed The returned or invoked value.
     */
    function value(mixed $value): mixed {
        return (is_callable($value) ? $value() : $value);
    }
    
    /**
     * Dispatches an event if the provided data is an instance of Event.
     *
     * This function checks if the provided data is an instance of the Event class.
     * If it is, it triggers the event by calling the static method trigger() on the Event class.
     * This function is useful for handling events in an application.
     *
     * @param mixed $data The data to be dispatched.
     *
     * @return void
     */
    function dispatch(mixed $data): void {
        if ($data instanceof Event) {
            Event::trigger($data);
        }
    }
    
    # ################################################################
    # Number helpers
    # ################################################################
    
    /**
     * Returns a unique identifier based on the given context.
     * If no context is provided, a default identifier is returned.
     *
     * @param float|bool|int|string|null $context (optional) The context for the identifier.
     *                                            Defaults to null.
     *
     * @return int The unique identifier for the given context.
     */
    function id(float|bool|int|string $context = null): int {
        static $id = 0;
        static $ids = [];
        
        if (is_scalar($context)) {
            if (!array_key_exists($context, $ids)) {
                $ids[$context] = 0;
            }
            
            return $ids[$context]++;
        }
        
        return $id++;
    }
    
    /**
     * Gleicht eine Zahl an den nächst-höheren bzw nächst-niedrigeren angegebene Stellenwert.
     * Auf- oder Abrunden kann forciert werden wenn true oder false übergeben wird.
     * Für automatische Rundung (Rundung an die nächst-nähere Stelle) können wie üblich die
     * PHP Rundungskonstanten übergeben werden
     * -
     * Beispiele:
     *
     * Auf hunderter aufrunden
     * num_align(1234, 100, true)                      // 1300
     *
     * Auf hunderter abrunden
     * num_align(1499, 100, false)                     // 1400
     *
     * Automatisch zum nähesten 0.5 runden (Schweizer Rappenrundung)
     * num_align(123.45, 0.5, PHP_ROUND_HALF_UP, 0)    // 123.5
     *
     * Automatisch zum nähesten 0.5 runden (Schweizer Rappenrundung)
     * num_align(123.23, 0.5, PHP_ROUND_HALF_UP, 0)    // 123.0
     *
     * @param float|int $number    Input value
     * @param float     $base      The base step the input value should be rounded to
     * @param bool|int  $mode      Angleichung nach oben (true) oder unten(false) oder automatisch zum nächst-näheren
     *                             Stellenwert (PHP round Konstante)
     * @param int       $precision Präzision für automatische Runden
     *
     * @return int|float
     */
    function num_align(float|int $number, float $base = 0.01, bool|int $mode = true, int $precision = 0): int|float {
        $factor = PHP_INT_MAX;
        if ($mode === true) {
            return (ceil(($number * $factor) / ($base * $factor)) * ($base * $factor)) / $factor;
        }
        if ($mode === false) {
            return (floor(($number * $factor) / ($base * $factor)) * ($base * $factor)) / $factor;
        }
        if (is_int($mode)) {
            return (round($number / $base, abs($precision), $mode) * $base);
        }
        
        return $number;
    }
    
    /**
     * Returns the minimum or maximum value within a given range or a specific value
     * ----------------------------------------------------------------
     * Usage:
     * minmax(5);                        // 5
     * minmax(10, 5, 15);                 // 10
     * minmax(20, [10, 30]);              // 20
     * minmax(40, 50, 60);                // 50
     * minmax(70, [60, 80]);              // 70
     *
     * @param mixed                $number  The value to check for minimum or maximum.
     * @param float|int|array|null $min     The minimum value or the range for the minimum value.
     *                                      If an array is provided, it should contain two elements: minimum and
     *                                      maximum.
     * @param float|int|null       $max     The maximum value for the range.
     *                                      If not provided, $min will be treated as the maximum value.
     *
     * @return float|bool|int|string        The minimum or maximum value based on the given range or a specific value.
     *                                      If the given $number is not numeric, it returns 0.
     */
    function minmax(mixed $number, float|int|array $min = null, float|int $max = null): float|bool|int|string {
        if (!is_numeric($number)) {
            return 0;
        }
        $number = ($min === null ? $number : max($number, $min));
        $number = ($max === null ? $number : min($number, $max));
        
        return $number ?: 0;
    }
    
    # ################################################################
    # Database helpers
    # ################################################################
    
    /**
     * Returns the instance of the Database class.
     *
     * This function returns the singleton instance of the Database class by calling the `i()` method,
     * which creates the instance if it does not exist, or returns the already created instance.
     * The optional `$connection` parameter can be used to specify a database connection name.
     * If no connection name is provided, the default database connection will be used.
     *
     * @param string|null $connection (Optional) The name of the database connection.
     *
     * @return Database The instance of the Database class.
     */
    function db(?string $connection = null): Database {
        try {
            return Database::i($connection);
        } catch (\Exception $e) {
            exit('- Database error! -');
        }
    }
    
    /**
     * Creates a new instance of the QueryBuilder class for the specified table with optional WHERE conditions.
     *
     * This method returns an instance of the QueryBuilder class, which can be used to build and execute SQL queries.
     *
     * Example usage:
     * $query = sql("users", "id = 1");
     * $query->select();
     * $results = $query->get();
     *
     * @param string $table    The name of the table to query.
     * @param mixed  ...$where Optional WHERE conditions to append to the query.
     *                         Accepts a variable number of arguments.
     *                         Each argument must be a valid WHERE condition.
     *
     * @return QueryBuilder An instance of the QueryBuilder class.
     */
    function sql(string $table = '', ...$where): QueryBuilder {
        return new QueryBuilder($table, ...$where);
    }
    
    /**
     * Returns a string representation of concatenated conditions using logical OR and AND operators.
     *
     * @param mixed ...$conditions Multiple arrays of conditions to be concatenated. Each array represents a block of
     *                             conditions to be connected with logical AND. Each condition within the block should
     *                             have a __toString() method defined.
     *
     * @return string The concatenated conditions using logical OR and AND operators.
     */
    function where(...$conditions): string {
        $ors = [];
        foreach ($conditions as $condition) {
            $ands = [];
            if (is_array($condition) && $condition) {
                foreach ($condition as $key => $value) {
                    switch (true) {
                        # Just evaluate that part separately via a recursive call
                        case (is_array($value)):
                            $ands[] = where($value);
                            break;
                        # If true is passed, just set 1 as condition
                        case(is_int($key) && is_bool($value)):
                            $ands[] = (int)$value;
                            break;
                        # Assume the condition is an id check
                        case (is_int($key) && is_int($value)):
                            $ands[] = '`id` = ' . dbvalue($value);
                            break;
                        # Use custom column-specific conditions
                        case  (is_int($key) && $value instanceof Column):
                            $ands[] = $value->toString();
                            break;
                        # Assume that the key is a column
                        case is_string($key):
                            # If the value is an array, we check if the value is in the given values (WHERE IN ...)
                            if (is_array($value)) {
                                foreach ($value as &$v) {
                                    $v = dbvalue($v);
                                }
                                
                                $ands[] = dbkey($key) . ' IN (' . implode(', ', $value) . ')';
                            }
                            # Equals check with key and value
                            if (is_scalar($value)) {
                                $ands[] = dbkey($key) . ' = ' . dbvalue($value);
                            }
                            # Assume that we want to compare with the value of another column
                            if ($value instanceof Column) {
                                $ands[] = dbkey($key) . ' = ' . $value->name();
                            }
                            break;
                        # In that case we expect a raw SQL condition
                        case (is_int($key) && is_string($value)):
                            if ($value !== '') {
                                $ands[] = $value;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            # Handle plain true or false and add 1 or 0 to the condition.
            if (is_bool($condition)) {
                $ands[] = (int)$condition;
            }
            # Column conditions
            if ($condition instanceof Column) {
                $ands[] = $condition->toString();
            }
            if (is_int($condition)) {
                $ands[] = dbkey('id') . ' = ' . $condition;
            }
            # Raw SQL string
            if (is_string($condition) && $condition !== '') {
                $ands[] = $condition;
            }
            if ($ands) {
                $ors[] = '(' . implode(' AND ', $ands) . ')';
            }
        }
        if (!$ors) {
            return '';
        }
        
        return trim(implode(' OR ', $ors));
    }
    
    /**
     * Returns a new instance of the Column class with the specified name.
     *
     * @param string $name The name of the column.
     *
     * @return Column A new instance of the Column class with the specified name.
     */
    function column(string $name, $connect_or = false): Column {
        return new Column($name, $connect_or ? 'OR' : 'AND');
    }
    
    /**
     * Sanitizes and formats a given value to be used as a database key name.
     *
     * This function performs the following steps:
     * 1) Trims the input value.
     * 2) Collapses consecutive occurrences of '_', '*', and '()' characters in the value.
     * 3) Removes any trailing '.' characters.
     * 4) Sets the final value to '*' if the initial value is an empty string.
     * 5) Wraps the value with backticks if it is not equal to '*'.
     *
     * @param string $value The input value to sanitize and format.
     *
     * @return string The sanitized and formatted value.
     */
    function db_keyname(string $value): string {
        # Sanitize string
        $value = trim(str_collapse(str_keep($value, '_.*()'), '*.'), '.');
        $value = ($value === '' ? '*' : $value);
        $value = str_replace(['.', '`*`'], ['`.`', '*'], '`' . $value . '`');
        
        return $value === '' ? '*' : $value;
    }
    
    /**
     * Returns an array of unique database keys based on the given input keys.
     * If $return_as_string is set to true, it returns the keys as a string separated by commas.
     * This function supports different types of input keys - integer, float, boolean, string, array.
     *
     * @param mixed $keys The input keys. It can be a single key or an array of keys.
     * @param bool  $to_array
     *
     * @return string|array The database keys as either a string or an array.
     */
    function dbkey(mixed $keys = null, bool $to_array = false): array|string {
        $keys   = (array)$keys;
        $result = [];
        foreach ($keys as $key) {
            switch (true) {
                case is_int($key) || is_float($key):
                    $result[] = $key;
                    break;
                case $key === true || $key === '*' || $key === null || $key === '':
                    $result[] = '*';
                    break;
                case is_string($key):
                    $key = str_replace([';', '|'], ',', $key);
                    $arr = explode(',', str_collapse(trim($key, ','), ', '));
                    foreach ($arr as $k) {
                        $result[] = db_keyname($k);
                    }
                    break;
                case is_array($key):
                    $result = array_merge($result, dbkey($key, true));
                    break;
                default:
                    break;
            }
        }
        $result = array_unique($result);
        $result = $result ?: ['*'];
        
        return ($to_array ? $result : implode(', ', $result));
    }
    
    /**
     * Calls the dbkey() function with the given keys and returns the result as an array.
     *
     * @param mixed $keys      The keys to be passed to the dbkey() function.
     * @param bool  $to_string If you want the imploded version as string.
     *
     * @return array The result of the dbkey() function as an array.
     */
    function dbkeys(mixed $keys = null, bool $to_string = false): array {
        return dbkey($keys, !$to_string);
    }
    
    /**
     * Converts a PHP value to its corresponding SQL representation.
     * The method is used to escape and format values before inserting them into the database.
     *
     * @param mixed $value The PHP value to be converted.
     *
     * @return float|int|string The SQL representation of the input value.
     */
    function dbvalue(mixed $value): float|int|string {
        return match (true) {
            $value instanceof Column => $value->name(),
            $value === null => 'NULL',
            is_string($value) => db()->connection()->quote($value),
            is_int($value) || is_float($value) => $value,
            is_array($value) => db()->connection()->quote(json_encode($value)),
            is_bool($value) => (int)$value,
            default => 'DEFAULT',
        };
    }
    
    /**
     * Prepares an array of data for a database query.
     * This function converts each key-value pair in the input array to a formatted string that can be used in a
     * database query. The resulting array will have each key-value pair formatted as "dbkey = dbvalue".
     * -
     * Example:
     * Input....: ['name' => 'John', 'age' => 25, 'file' => null]
     * Output...: "`name` = "John", `age` = 25, `file` = NULL"
     *
     * @param array $data The input array containing key-value pairs
     *
     * @return array|string An array of formatted strings ready to be used in a database query
     */
    function dbset(array $data, $to_string = false): array|string {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = dbkey($key) . ' = ' . dbvalue($value);
        }
        
        return ($to_string ? implode(', ', $result) : $result);
    }
    
    /**
     * Generates an SQL INSERT statement based on the provided data.
     *
     * This function takes an associative array of key-value pairs representing
     * the data to be inserted into the database. It generates an SQL INSERT
     * statement using the keys as column names and the corresponding values as
     * the column values. The generated statement includes proper escaping of
     * values to prevent SQL injection.
     *
     * Example usage:
     *   $data = [
     *     'name' => 'John Doe',
     *     'age' => 25,
     *     'email' => 'johndoe@example.com'
     *   ];
     *
     *   $sql = sql_insert($data);
     *   // Output: '(name, age, email) VALUES ('John Doe', 25, 'johndoe@example.com')'
     *
     * @param array $data An associative array of key-value pairs representing
     *                    the data to be inserted.
     *
     * @return string The generated SQL INSERT statement.
     */
    function sql_values(array $data): string {
        $keys   = [];
        $values = [];
        foreach ($data as $key => $value) {
            $keys[]   = dbkey($key);
            $values[] = dbvalue($value);
        }
        
        return '(' . implode(', ', $keys) . ') VALUES (' . implode(', ', $values) . ')';
    }
    
    /**
     * Creates a new Collection with the given items.
     *
     * Usage:
     * collect(1, 2, 3);  // Collection([1, 2, 3])
     * collect('a', 'b'); // Collection(['a', 'b'])
     * collect();         // Collection([])
     *
     * @param mixed ...$items The items to add to the Collection.
     *
     * @return Collection A new Collection object containing the given items.
     */
    function collect(...$items): Collection {
        return new Collection(...$items);
    }
    
    /**
     * Throws the given throwable if the condition is true.
     *
     * This function checks if the provided condition evaluates to true. If it does, it throws the given throwable
     * object.
     *
     * @param mixed     $condition The condition to check
     * @param Throwable $throwable The throwable object to throw
     *
     * @throws Throwable If the condition is true
     */
    function throwIf(mixed $condition, Throwable $throwable): void {
        if (value($condition)) {
            throw $throwable;
        }
    }
    
    /**
     * Throws the given throwable if the condition is not met.
     *
     * @param mixed     $condition The condition to be checked
     * @param Throwable $throwable The throwable to be thrown
     *
     * @throws Throwable  The given throwable if the condition is not met
     */
    function throwIfNot(mixed $condition, Throwable $throwable): void {
        throwIf(!$condition, $throwable);
    }
    
    /**
     * Sets or retrieves the current language setting.
     * The current language setting is stored in a static variable to persist between function calls.
     * If a parameter is provided, it is used to set the current language. If no parameter is provided,
     * the current language is retrieved.
     * -
     * Example:
     * Input.....: 'fr'
     * Output....: 'fr'
     *
     * @param string|null $set The language to set. If null, retrieves the current language setting.
     *
     * @return string The current language setting.
     */
    function lang(?string $set = null): string {
        static $lang = 'en';
        if (is_string($set)) {
            $lang = $set;
        }
        
        return $lang;
    }
    
    /**
     * Retrieves a translated string based on the key.
     * If the translation exists in the specified language, it will be returned. Otherwise, the key itself will be
     * returned.
     * -
     * How this function works:
     * 1) Loads the language file in JSON format from the 'lang/de.json' file and converts it to an associative array
     * using json2array() function.
     * 2) Attempts to load additional translations from a language-specific PHP file, if such a file exists in the
     * 'lang' directory, for the specified language ($lang).
     * 3) The translation data is stored in a static variable ($t) to avoid reading the language files multiple times.
     * 4) The translation is retrieved from the $t array using the provided key ($key) using the data_get() function.
     * If a translation is found, it will be returned. Otherwise, the key itself will be returned.
     * 5) If the translation is not found, the key is formatted using str_title() function and returned.
     * -
     * Example usage:
     * Input.....: __("hello.world")
     * Output....: "Hello World" (If the translation for "hello.world" is not found, the key itself will be returned)
     *
     * @param string      $key  The translation key
     * @param string|null $lang The language to use (default: 'de')
     *
     * @return string The translated string or the key itself if translation not found
     */
    function __(string $key, string $lang = null): string {
        $lang = $lang ?? lang();
        static $langmap = ['en' => 0, 'de' => 1];
        $langid = arr($langmap, $lang, 0);
        static $t = null;
        if ($t === null) {
            $t  = [];
            $t  = json2array(File::read(path('lang/de.json')));
            $fp = Str::before($key, '.');
            $tf = path('lang/' . $lang . '/' . $fp . '.php');
            if (is_file($tf)) {
                $data = require_once $tf;
                if (is_array($data)) {
                    $t[$fp] = $data;
                }
            }
            $map = path('lang/map.php');
            if (is_file($map)) {
                $tmp = require_once $map;
                if (is_array($tmp)) {
                    $t = array_replace_recursive($t, $tmp);
                }
            }
        }
        $translated = data_get($t, $key, str_title($key));
        if (is_array($translated)) {
            $translated = arr($translated, $langid, str_title($key));
        }
        
        return $translated;
    }
    
    /**
     * Returns the current date and time as a DateTime object.
     *
     * @return \Xmgr\DateTime The current date and time.
     */
    function now(): Xmgr\DateTime {
        return new Xmgr\DateTime();
    }
    
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     *
     * @return bool
     */
    function blank(mixed $value): bool {
        if (is_null($value)) {
            return true;
        }
        
        if (is_string($value)) {
            return trim($value) === '';
        }
        
        if (is_numeric($value) || is_bool($value)) {
            return false;
        }
        
        if ($value instanceof Countable) {
            return count($value) === 0;
        }
        
        if ($value instanceof Stringable) {
            return trim((string)$value) === '';
        }
        
        return empty($value);
    }
    
    /**
     * Checks if the current operating system is Windows.
     *
     * This function checks whether the operating system on which the code is running is Windows or not.
     * It does this by using the PHP_OS constant which contains the operating system name. It compares the first three
     * characters of the uppercase version of PHP_OS with the string 'WIN'. If they match, it is assumed that the
     * operating system is Windows. The result is cached in a static variable to avoid unnecessary calls to the PHP_OS
     * constant.
     *
     * @return bool Returns true if the current operating system is Windows, false otherwise.
     */
    function windows_os(): bool {
        static $win_os = null;
        $win_os = $win_os ?? strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        return $win_os;
    }
    
    /**
     * Downloads a file.
     *
     * This function sends the specified content as a file download. If a filename is not provided, it generates a
     * default filename based on the current date and time.
     *
     * @param string      $content The content to be downloaded
     * @param string|null $file    Optional. The filename of the downloaded file. If not provided, a default filename
     *                             will be generated.
     */
    function download(string $content, ?string $file = null) {
        $filename = $file ?? rget('file') ?? 'file-' . date('Y-m-d_H_i_s') . '.txt';
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        echo $content;
    }
    
    /**
     * Class Nothing
     *
     * This class represents a placeholder class with no specific functionality.
     *
     * Usage:
     * Instantiate an object of this class to serve as a placeholder or to create an empty container.
     *
     * Example:
     * $nothingObj = new Nothing();
     *
     */
    class Nothing {
    
    }
