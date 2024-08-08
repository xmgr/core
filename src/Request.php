<?php
    
    namespace Xmgr;
    
    /**
     * Class Request
     *
     * The Request class provides methods to retrieve values from the $_GET and $_POST superglobal arrays.
     * It also provides methods to retrieve values from either the GET or POST parameters.
     * Additionally, it provides methods to retrieve the request body, HTTP method, and check the HTTP method against a
     * specified value. The class also has a method to retrieve the value of the 'id' parameter as an integer. Finally,
     * it has a method to retrieve values from the $_SERVER superglobal array.
     */
    class Request {
        
        /**
         * Retrieves a value from the $_GET superglobal array using the provided key.
         *
         * @param mixed $key     The key to retrieve the value for.
         * @param mixed $default Optional. The default value to return if the key is not found. Default is null.
         *
         * @return mixed The value associated with the given key, or the default value if the key is not found.
         */
        public static function get(mixed $key, mixed $default = null): mixed {
            return data_get($_GET, $key, $default);
        }
        
        /**
         * Retrieves a value from the $_POST superglobal array using the provided key.
         *
         * @param mixed $key     The key to retrieve the value for.
         * @param mixed $default Optional. The default value to return if the key is not found. Default is null.
         *
         * @return mixed The value associated with the given key, or the default value if the key is not found.
         */
        public static function post(mixed $key, mixed $default = null): mixed {
            return data_get($_POST, $key, $default);
        }
        
        /**
         * Description: This method is responsible for retrieving the value of a given key from either the GET or POST
         * parameters.
         *
         * @param mixed $key     The key to retrieve the value for from either the GET or POST parameters.
         * @param mixed $default The default value to return if the key does not exist in either the GET or POST
         *                       parameters.
         *
         * @return mixed The value of the given key from either the GET or POST parameters, or the default value if the
         *               key does not exist in either parameter.
         *
         * @throws \RuntimeException If an error occurs while retrieving the value from the GET or POST parameters.
         */
        public static function getOrPost(mixed $key, mixed $default): mixed {
            return static::get($key, static::post($key, $default));
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the specified key from the POST request.
         * If the value is not found in the POST request, then it retrieves the value from the GET request.
         * If the value is not found in both requests, it returns the default value.
         *
         * @param mixed $key     The key to retrieve the value for.
         * @param mixed $default The default value to return if the key is not found in the POST or GET request.
         *
         * @return mixed The value of the key from the POST or GET request, or the default value.
         * @throws \RuntimeException If an error occurs while retrieving the value from the POST or GET request.
         */
        public static function postOrGet(mixed $key, mixed $default): mixed {
            return static::post($key, static::get($key, $default));
        }
        
        /**
         * Checks if any of the given keys exist in the $_GET superglobal array.
         *
         * @param mixed $keys An array of keys to check in the $_GET array
         *
         * @return bool
         */
        public static function getHas(mixed $keys): bool {
            return (bool)array_intersect($keys, array_keys($_GET));
        }
        
        /**
         * Checks if any of the given keys exist in the $_POST superglobal array.
         *
         * @param mixed $keys An array of keys to check in the $_POST array.
         *
         * @return bool
         */
        public static function postHas(mixed $keys): bool {
            return (bool)array_intersect($keys, array_keys($_POST));
        }
        
        /**
         * Get the value of the given key from both the request's POST data and GET data.
         * If the key is not found in either data, the default value will be returned.
         * This is an alias for the postOrGet() method.)
         *
         * @param mixed $key     The key to retrieve the value for.
         * @param mixed $default The default value to return if the key is not found in the data.
         *
         * @return mixed The value of the key from the POST data if found, otherwise the value from the GET data
         * or the default value if not found in either data.
         */
        public static function find(mixed $key, mixed $default): mixed {
            return static::postOrGet($key, $default);
        }
        
        /**
         * Get the request body.
         *
         * @return string The request body.
         */
        public static function body(): string {
            static $body = null;
            $body = $body ?? (string)file_get_contents('php://input');
            
            return $body;
        }
        
        /**
         * Description: This method is responsible for retrieving all form data submitted through the POST method.
         *
         * @return array An associative array containing all form data submitted through the POST method.
         *
         * @throws \RuntimeException If an error occurs while retrieving the form data.
         */
        public static function formData(): array {
            return $_POST;
        }
        
        /**
         * Description: This method is responsible for converting the JSON data from the request body into an
         * associative array.
         *
         * @return array The JSON data converted into an associative array.
         *
         * @throws \RuntimeException If an error occurs while converting the JSON data.
         *
         * @see body()
         * @see json2array()
         */
        public static function jsonData() {
            return json2array(static::body());
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the '_method' parameter using the HTTP
         * method of the current request.
         *
         * @return string The value of the '_method' parameter or the actual http request method
         *
         * @throws \RuntimeException If an error occurs while retrieving the '_method' parameter.
         */
        public static function method(): string {
            return strtoupper(static::post('_method', static::httpMethod()));
        }
        
        /**
         * Description: This method is responsible for checking if the value of the '_method' parameter retrieved using
         * the HTTP method of the current request is equal to the specified string
         *.
         *
         * @param string|array $method The string to compare with the value of the '_method' parameter.
         *
         * @return bool Returns true if the value of the '_method' parameter is equal to the specified string, false
         *              otherwise.
         *
         * @throws \RuntimeException If an error occurs while retrieving the '_method' parameter.
         */
        public static function methodIs(string|array $method): bool {
            if (is_string($method)) {
                return (static::method() === strtoupper($method));
            }
            if (is_array($method)) {
                foreach ($method as $m) {
                    if (static::method() === strtoupper($m)) {
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        /**
         * Checks if the current request is a GET request.
         *
         * @return bool Returns true if the current request is a GET request, false otherwise.
         */
        public static function isGetRequest(): bool {
            return self::methodIs('GET');
        }
        
        /**
         * Checks if the current request is a POST request.
         *
         * @return bool Returns true if the current request is a POST request, false otherwise.
         */
        public static function isPostRequest(): bool {
            return self::methodIs('POST');
        }
        
        /**
         * Get the HTTP method of the current request.
         *
         * @return string The HTTP method of the current request. It will return 'GET' if the 'REQUEST_METHOD' key is
         *                not found in the $_SERVER array.
         */
        public static function httpMethod(): string {
            return data_get($_SERVER, 'REQUEST_METHOD', 'GET');
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the 'id' parameter from either the POST
         * or GET request variables as an integer.
         *
         * @return int The value of the 'id' parameter as an integer.
         *
         * @throws \RuntimeException If an error occurs while retrieving the 'id' parameter.
         */
        public static function id(): int {
            return (int)static::postOrGet('id', 0);
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the 'id' parameter using the GET
         * method of the current request and casting it to an integer. It returns the value of the 'id' parameter
         * or 0 if the parameter is not present.
         *
         * @return int The value of the 'id' parameter or 0 if the parameter is not present.
         *
         * @throws \RuntimeException If an error occurs while retrieving the 'id' parameter.
         */
        public static function rid(): int {
            return (int)static::postOrGet('id', 0);
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the specified key from the $_SERVER
         * superglobal array.
         *
         * @param string $key     The key to search for in the $_SERVER array.
         * @param mixed  $default (optional) The default value to return if the key is not found in the $_SERVER array.
         *                        Default is an empty string.
         *
         * @return mixed The value of the specified key in the $_SERVER array, or the default value if the key is not
         *               found.
         */
        public static function server(string $key, mixed $default = '') {
            return arr($_SERVER, $key, $default);
        }
        
        /**
         * This method is responsible for retrieving the headers of the current request.
         *
         * @return array The headers of the current request.
         *
         * @throws \RuntimeException If an error occurs while retrieving the headers.
         */
        public static function headers() {
            static $headers = null;
            if ($headers === null) {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (str_starts_with($name, 'HTTP_')) {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
            }
            
            
            return $headers;
        }
        
        /**
         * Description: This method is responsible for retrieving the value of the specified header from the $_SERVER
         * array.
         *
         * @param string     $name    The name of the header to retrieve.
         * @param mixed|null $default Optional. The default value to return if the specified header is not found.
         *                            Defaults to null.
         *
         * @return string The value of the specified header as a string. If the header is not found, the default value
         *                is returned.
         *
         * @throws \RuntimeException If an error occurs while retrieving the header.
         */
        public static function header(string $name, mixed $default = null): string {
            return (string)arr($_SERVER, 'HTTP_' . strtoupper(str_replace('-', '_', $name)), $default);
        }
        
        /**
         * Description: This method is responsible for checking if the current URI starts with a given path.
         *
         * @param string $path The path to compare the current URI with.
         *
         * @return bool Returns true if the current URI starts with the given path, false otherwise.
         *
         * @throws \RuntimeException If an error occurs while checking if the current URI starts with the given path.
         */
        public static function uriStartsWith(string $path) {
            return str_starts_with(uri(), $path);
        }
        
        /**
         * Returns the URI of the current request without the query string part.
         *
         * @param ?int $part Holt nur einen einzelnen Part aus dem URL Pfad
         *                   Beispiel: /foo/bar/baz
         *                   Request::uri(1); // "bar"
         *
         * @return string The URI of the current request.
         */
        public static function uri(?int $part = null) {
            static $uri = null;
            if ($uri === null) {
                $uri = '/' . trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
            }
            if ($part !== null) {
                $arr = explode('/', trim($part, '/'));
                
                return (string)arr($arr, $part);
            }
            
            return $uri;
        }
        
        /**
         * Breaks down the URI into its parts by removing any leading or trailing slashes.
         *
         * @return array
         */
        public static function uriParts(): array {
            return Str::explode('/', trim(self::uri(), '/'));
        }
        
        /**
         * Retrieves the specific part of the URI using the provided index.
         *
         * @param int $index The index of the desired URI part, starting from 0.
         *
         * @return string The URI part at the specified index.
         */
        public static function uriPart(int $index) {
            return (string)arr(self::uriParts(), $index);
        }
        
        /**
         * Returns the current request URL
         * ----------------------------------------------------------------
         *
         * @return string|null The current request URL, or null if it cannot be determined.
         */
        public static function request_url(): ?string {
            static $url = null;
            if ($url === null) {
                $url = url_scheme() . '://' . host() . arr($_SERVER, 'REQUEST_URI');
            }
            
            return $url;
        }
        
    }
