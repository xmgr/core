<?php
    
    namespace Xmgr;
    
    /**
     * Class Url
     *
     * Represents a URL and provides methods to manipulate and retrieve its components.
     */
    class Url {
        
        protected string $scheme   = 'https';
        protected string $host     = 'example.com';
        protected int    $port     = 0;
        protected string $user     = '';
        protected string $pass     = '';
        protected string $path     = '/';
        protected array  $args     = [];
        protected string $fragment = 'https';
        
        /**
         * Constructs a new instance of the class.
         *
         * @param string $url The URL to parse. Optional, defaults to an empty string.
         */
        public function __construct(string $url = '') {
            $this->parse($url);
        }
        
        /**
         * Creates a new instance of the current class based on the current request URL.
         *
         * @return static Returns a new instance of the current class based on the current request URL.
         */
        public static function current(): static {
            return new static(Request::request_url());
        }
        
        /**
         * Updates the instance properties with the different components parsed from the given URL.
         *
         * @param string $url The URL to be parsed.
         *
         * @return $this
         */
        public function parse(string $url): static {
            $this->scheme((string)parse_url($url, PHP_URL_SCHEME));
            $this->host(parse_url($url, PHP_URL_HOST) ?? 'localhost', parse_url($url, PHP_URL_PORT));
            $this->auth((string)parse_url($url, PHP_URL_USER), (string)parse_url($url, PHP_URL_PASS));
            $this->path(parse_url($url, PHP_URL_PATH) ?? '/');
            $this->args(static::querystring2array(parse_url($url, PHP_URL_QUERY) ?? ''));
            $this->fragment(parse_url($url, PHP_URL_FRAGMENT) ?? '');
            
            return $this;
        }
        
        /**
         * Sets the username and password for authentication.
         *
         * @param string $username The username to authenticate with.
         * @param string $password The password to authenticate with.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function auth(string $username, string $password): static {
            $this->user = $username;
            $this->pass = $password;
            
            return $this;
        }
        
        /**
         * Disables authentication for the current instance.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function noAuth(): static {
            $this->user = '';
            $this->pass = '';
            
            return $this;
        }
        
        /**
         * Generates the authentication string based on the user and password properties.
         *
         * @return string Returns the authentication string in the format "user:pass@" if both user and password are
         *                set. If either user or password is not set, returns an empty string
         *.
         */
        protected function authString(): string {
            if ($this->user && $this->pass) {
                return $this->user . ':' . $this->pass . '@';
            }
            
            return '';
        }
        
        /**
         * Checks if the provided scheme matches the current scheme of the object.
         *
         * @param string $scheme The scheme to compare with the current scheme.
         *
         * @return bool Returns true if the provided scheme matches the current scheme, otherwise returns false.
         */
        public function schemeIs(string $scheme): bool {
            return mb_strtolower($scheme) === $this->scheme;
        }
        
        /**
         * Returns the scheme value.
         *
         * @return string The scheme value.
         */
        public function getScheme(): string {
            return $this->scheme;
        }
        
        /**
         * Sets the scheme value.
         *
         * @param string|null $value The scheme value to be set. If null, the scheme will not be changed.
         *
         * @return $this The current instance of the class.
         */
        public function scheme(?string $value = null): static {
            if ($value !== null) {
                $this->scheme = mb_strtolower(str_replace(' ', '', trim($value, '/:')));
                $this->scheme = $this->scheme === '' ? 'https' : $this->scheme;
            }
            
            return $this;
        }
        
        /**
         * Calculates the depth of the path.
         *
         * @return int The depth of the path.
         */
        public function pathDepth() {
            return count(explode('/', trim($this->path, '/')));
        }
        
        /**
         * Checks if the path exists.
         *
         * @return bool Returns true if the path is not equal to '/' and is not an empty string, otherwise returns
         *              false.
         */
        public function hasPath(): bool {
            return ($this->path !== '/' && $this->path !== '');
        }
        
        /**
         * Gets the path value.
         *
         * @return string The path value.
         */
        public function getPath(): string {
            return $this->path;
        }
        
        /**
         * Sets the path for the current instance.
         *
         * @param string|null $value The value of the path. If null, the method will not set a new path.
         *
         * @return $this The current instance of the class.
         */
        public function path(?string $value = null): static {
            if ($value !== null) {
                $this->path = '/' . trim($value, '/');
            }
            
            return $this;
        }
        
        /**
         * Retrieves the fragment of the URL.
         *
         * @return string The fragment of the URL.
         */
        public function getFragment(): string {
            return $this->fragment;
        }
        
        /**
         * Sets the fragment value.
         *
         * @param string|null $value The value of the fragment. Defaults to null.
         *
         * @return $this The current instance of the class.
         */
        public function fragment(?string $value = null): static {
            if ($value !== null) {
                $this->fragment = ltrim($value, '#');
            }
            
            return $this;
        }
        
        /**
         * Retrieves the port value of the current instance.
         *
         * @return int The port value of the current instance, or null if not set.
         */
        public function getPort(): int {
            return $this->port;
        }
        
        /**
         * Sets the port for the connection.
         *
         * @param int|null $value The value of the port. If null, the port will not be modified.
         *
         * @return $this The current instance of the class.
         */
        public function port(?int $value = null): static {
            if ($value !== null) {
                $this->port = $value;
            }
            
            return $this;
        }
        
        /**
         * Sets the "port" property to 0, effectively disabling the use of a specific port.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function noPort() {
            $this->port = 0;
            
            return $this;
        }
        
        /**
         * Retrieves the host value.
         *
         * @return string The host value.
         */
        public function getHost(): string {
            return $this->host;
        }
        
        /**
         * Creates an array of components from the host.
         *
         * The host string is split into an array using the dot (.) as the delimiter. Each
         * component of the host is stored as an element in the resulting array.
         *
         * @return array Returns an array containing the components of the host.
         */
        public function hostParts(): array {
            return explode('.', $this->host);
        }
        
        /**
         * Retrieves a specific part of the host.
         *
         * @param int $index The index of the host part to retrieve.
         *
         * @return string The value of the specified host part. If the index is out of range, an empty string is
         *                returned.
         */
        public function hostPart(int $index): string {
            return array_index($this->hostParts(), $index, '');
        }
        
        /**
         * Sets a specific part of the host value.
         *
         * @param int    $index The index of the host part to be updated.
         * @param string $value The new value for the specified host part.
         *
         * @return $this The current instance of the class.
         */
        public function setHostPart(int $index, string $value): static {
            $this->host(implode('.', Arr::updateAt($this->hostParts(), $index, $value)));
            
            return $this;
        }
        
        /**
         * Sets the host value.
         *
         * @param string|null $value The host value to be set. If null, the host will not be changed.
         *
         * @return $this The current instance of the class.
         */
        public function host(?string $value = null, ?int $port = null): static {
            if ($value !== null) {
                $tmp = trim(str_replace(' ', '', $value), '.');
                if (str_contains($tmp, '.')) {
                    $this->host = $tmp;
                }
            }
            $this->port($port);
            
            return $this;
        }
        
        /**
         * Retrieves the subdomain from the host.
         *
         * The subdomain is extracted from the "host" property by splitting it into an array
         * using the dot (.) as the delimiter. If the number of array elements is greater than 2,
         * the first element is considered as the subdomain. Otherwise, an empty string is returned.
         *
         * @return string Returns the subdomain extracted from the "host" property, or an empty string if no subdomain
         *                is found.
         */
        public function getSubdomain(): string {
            $tmp = explode('.', $this->host);
            
            return (count($tmp) > 2 ? $tmp[0] : '');
        }
        
        /**
         * Appends or replaces the subdomain in the host.
         *
         * The subdomain is extracted from the "host" property by splitting it into an array
         * using the dot (.) as the delimiter. If the number of array elements is greater than 2,
         * the first element is replaced with the given subdomain. Otherwise, the subdomain is
         * appended to the host.
         *
         * @param string $name The subdomain to be appended or replaced in the host.
         *
         * @return $this Returns the instance of the class with the updated host.
         */
        public function subdomain(string $name): static {
            $tmp = explode('.', $this->host);
            switch (true) {
                case count($tmp) > 2:
                    $tmp[0] = $name;
                    break;
                case count($tmp) === 2:
                    array_unshift($tmp, $name);
                    break;
            }
            $this->host = implode('.', $tmp);
            
            return $this;
        }
        
        /**
         * Retrieves the domain value.
         *
         * @return string The domain value.
         */
        public function getDomain(): string {
            return $this->hostPart(-2);
        }
        
        /**
         * Retrieves the top-level domain from the host value.
         *
         * @return string The top-level domain extracted from the host value.
         */
        public function getTopLevelDomain(): string {
            return $this->hostPart(-1);
        }
        
        /**
         * Sets the domain value.
         *
         * @param string $name The domain name to be set.
         *
         * @return $this The current instance of the class.
         */
        public function domain(string $name): static {
            $this->setHostPart(-2, str_replace(' ', '', $name));
            
            return $this;
        }
        
        /**
         * Prepends a domain part to the host value.
         *
         * @param string $value The domain part value to be prepended to the host.
         *
         * @return $this The current instance of the class.
         */
        public function prependDomainPart(string $value): static {
            $this->host = trim($value, '.') . $this->host;
            
            return $this;
        }
        
        /**
         * Sets the top-level domain (TLD) value.
         *
         * @param string $name The TLD value to be set.
         *
         * @return $this The current instance of the class.
         */
        public function tld(string $name): static {
            $this->setHostPart(-1, str_replace(' ', '', $name));
            
            return $this;
        }
        
        /**
         * Generates a query string from the arguments stored in the object.
         *
         * @param bool $with_querytionmark (optional) Determines whether to include a question mark before the query
         *                                 string. Defaults to true.
         *
         * @return string The generated query string, optionally including a question mark.
         */
        public function queryString(bool $with_querytionmark = true): string {
            return static::array2querystring($this->args, $with_querytionmark);
        }
        
        /**
         * Builds the complete URL using the configured components.
         *
         * @return string The complete URL generated using the configured components.
         */
        public function build(): string {
            $url = $this->scheme . '://' . $this->authString() . $this->host;
            if ($this->port) {
                $url .= ':' . $this->port;
            }
            $url .= $this->getPath();
            $url .= $this->queryString();
            if ($this->fragment !== '') {
                $url .= '#' . $this->fragment;
            }
            
            return $url;
        }
        
        /**
         * Clears the parameters of the current object and returns the updated object.
         *
         * @param array  $new_args                An optional array of new arguments.
         * @param string $additional_query_string An optional additional query string.
         *
         * @return $this                           The updated object after clearing the parameters.
         */
        public function clearParams(array $new_args = [], string $additional_query_string = ''): static {
            $this->args = [];
            $this->args($new_args);
            $this->addQueryString($additional_query_string);
            
            return $this;
        }
        
        /**
         * Updates the "args" property with the given data.
         *
         * @param array $data The new data to merge with the existing "args" property.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function args(array $data): static {
            $this->args = array_replace_recursive($this->args, $data);
            
            return $this;
        }
        
        /**
         * Adds an argument to the list of arguments.
         *
         * @param string $key   The key of the argument.
         * @param mixed  $value The value of the argument.
         *
         * @return $this The current instance of the class.
         */
        public function arg(string $key, mixed $value): static {
            $this->args([$key => $value]);
            
            return $this;
        }
        
        /**
         * Converts a query string into an associative array.
         *
         * @param string $string The query string to be converted.
         *
         * @return array The associative array representation of the query string.
         */
        public static function querystring2array(string $string): array {
            parse_str(trim($string, '?&'), $tmp);
            
            return $tmp ?: [];
        }
        
        /**
         * Converts an array into a query string.
         *
         * @param array $data The array to be converted into a query string.
         *
         * @return string The resulting query string.
         */
        public static function array2querystring(array $data, $with_questionmark = false): string {
            return ($data && $with_questionmark ? '?' : '') . http_build_query($data);
        }
        
        /**
         * Adds query parameters to the list of arguments.
         *
         * @param string $query_string The query string containing the parameters.
         *
         * @return $this The current instance of the class.
         */
        public function addQueryString(string $query_string): static {
            $this->args(static::querystring2array($query_string));
            
            return $this;
        }
        
        /**
         * Filters the "args" property, keeping only the specified keys.
         *
         * @param array $keys The keys to keep in the "args" property.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function keep(array $keys): static {
            $this->args = array_pick($this->args, $keys);
            
            return $this;
        }
        
        /**
         * Applies the stored "args" property to the global $_GET array.
         *
         * @return $this Returns the instance of the current object for method chaining.
         */
        public function applyArguments(): static {
            $_GET = array_replace_recursive($_GET, $this->args);
            
            return $this;
        }
        
        /**
         * Generates a string representation of the object.
         *
         * @return string Returns a string representation of the object.
         */
        public function toString(): string {
            return $this->build();
        }
        
        /**
         * Converts the current object to a string representation.
         *
         * @return string Returns the string representation of the current object.
         */
        public function __toString() {
            return $this->toString();
        }
        
    }