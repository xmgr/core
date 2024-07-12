<?php
    
    namespace Xmgr\Net;
    
    use Xmgr\Net\Http\Response;
    
    /**
     * Class Curl
     *
     * The Curl class provides methods for making HTTP requests using cURL.
     */
    class Curl {
        
        public const string DEFAULT_CHARSET = 'UTF-8';
        public const string METHOD_GET      = 'GET';
        public const string METHOD_POST     = 'POST';
        public const string METHOD_PUT      = 'PUT';
        public const string METHOD_DELETE   = 'DELETE';
        public const string METHOD_HEAD     = 'HEAD';
        public const string METHOD_OPTIONS  = 'OPTIONS';
        public const string METHOD_TRACE    = 'TRACE';
        public const string METHOD_CONNECT  = 'CONNECT';
        
        protected string $url     = '';
        protected array  $headers = [];
        
        protected array $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => [],
            CURLOPT_FRESH_CONNECT  => true
        ];
        
        protected string $method = 'GET';
        
        protected string $body              = '';
        protected array  $postfields        = [];
        protected string $basicAuthUsername = '';
        protected string $basicAuthPassword = '';
        
        /**
         * Constructs a new instance of the class.
         *
         * @param string $url          The URL to request (default is an empty string).
         * @param array  $assocHeaders An associative array of HTTP headers (default is an empty array).
         * @param string $method       The HTTP method to use (default is 'GET').
         * @param array  $options      Additional options for the request (default is an empty array).
         *
         * @return void
         */
        public function __construct(string $url = '', array $assocHeaders = [], string $method = 'GET', array $options = []) {
            $this->setUrl($url);
            $this->addHeaders($assocHeaders);
            $this->method($method);
            $this->options($options);
        }
        
        /**
         * Sets the HTTP method for the request.
         *
         * @param string $method The HTTP method to set.
         *
         * @return static This instance of the class.
         */
        public function method(string $method): static {
            $this->method = strtoupper($method);
            switch ($this->method) {
                case self::METHOD_GET:
                    $this->option(CURLOPT_HTTPGET, true);
                    break;
                case self::METHOD_POST:
                    $this->option(CURLOPT_POST, true);
                    break;
                default:
                    $this->option(CURLOPT_CUSTOMREQUEST, $method);
                    break;
            }
            
            return $this;
        }
        
        /**
         * Sends a GET request.
         *
         * @return $this Returns the response from the request.
         */
        public function get(): self {
            return $this->method(self::METHOD_GET);
        }
        
        /**
         * Sends a POST request using the current cURL options.
         *
         * @return $this Returns the response from the POST request.
         */
        public function post(): self {
            return $this->method(self::METHOD_POST);
        }
        
        /**
         * Checks if the given method(s) match the current method.
         *
         * @param string|array $methods The method(s) to be compared.
         *
         * @return bool Returns true if the given method(s) match the current method, false otherwise.
         */
        public function methodIs(string|array $methods) {
            $methods = (is_string($methods) ? [$methods] : $methods);
            if ($methods) {
                foreach ($methods as $method) {
                    if (strtoupper($this->method) === strtoupper($method)) {
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        /**
         * Set or add a header to the array of headers.
         *
         * @param string $name  The name of the header.
         * @param string $value The value of the header.
         *
         * @return $this The current object instance.
         */
        public function addHeader(string $name, string $value): static {
            $this->headers[$name] = $value;
            
            return $this;
        }
        
        /**
         * Adds headers to the existing headers.
         *
         * @param array $headers An array of headers to be added.
         *
         * @return $this The updated object with added headers.
         */
        public function addHeaders(array $headers = []): static {
            $this->headers = array_replace($this->headers, $headers);
            
            return $this;
        }
        
        /**
         * Adds a raw header to the cURL options.
         *
         * @param string $header The header to be added.
         *
         * @return static Returns an instance of the class with the added header.
         */
        public function addRawHeader(string $header): static {
            $this->curlOptions[CURLOPT_HEADER][] = $header;
            
            return $this;
        }
        
        /**
         * Adds multiple raw headers to the cURL options.
         *
         * @param array $headers An array of headers to be added.
         *
         * @return static Returns an instance of the class with the added headers.
         */
        public function addRawHeaders(array $headers): static {
            foreach ($headers as $header) {
                $this->addRawHeader($header);
            }
            
            return $this;
        }
        
        /**
         * Sets multiple cURL options.
         *
         * @param array $options An associative array of cURL options.
         *
         * @return static Returns an instance of the class with the updated cURL options.
         */
        public function options(array $options): static {
            $this->curlOptions = array_replace($this->curlOptions, $options);
            
            return $this;
        }
        
        /**
         * Sets an option for the cURL request.
         *
         * @param string $option The option to be set.
         * @param mixed  $value  The value to set for the option.
         *
         * @return static Returns an instance of the class with the updated option.
         */
        public function option($option, $value): static {
            $this->options([$option => $value]);
            
            return $this;
        }
        
        /**
         * Sets the URL for the current cURL request.
         *
         * @param string $url The URL to set for the request.
         *
         * @return static Returns an instance of the class with the updated URL.
         */
        public function setUrl(string $url): static {
            $this->url = $url;
            
            return $this;
        }
        
        /**
         * Sets the post fields for the cURL request.
         *
         * @param array $data The data to be sent as the post fields.
         *
         * @return static Returns an instance of the class with the set post fields.
         */
        public function setPostFields(array $data) {
            $this->postfields = $data;
            
            return $this;
        }
        
        /**
         * Adds a post field to the class's postfields.
         *
         * @param mixed $key   The key of the post field.
         * @param mixed $value The value of the post field.
         *
         * @return static Returns an instance of the class with the added post field.
         */
        public function addPostField(mixed $key, mixed $value): static {
            $this->postfields[$key] = $value;
            
            return $this;
        }
        
        /**
         * Adds post fields to the request.
         *
         * @param array $fields The post fields to be added.
         *
         * @return static Returns an instance of the class with the added post fields.
         */
        public function addPostFields(array $fields): static {
            $this->postfields = array_replace($this->postfields, $fields);
            
            return $this;
        }
        
        /**
         * Sets SSL options for cURL requests.
         *
         * @param bool $verifyPeer   (optional) Whether to verify the peer's SSL certificate. Default is true.
         * @param bool $verifyHost   (optional) Whether to verify the SSL certificate's common name against the DNS
         *                           host name. Default is true.
         * @param bool $verifyStatus (optional) Whether to verify the certificate's status. Default is true.
         *
         * @return $this Returns an instance of the class with the SSL options set.
         */
        public function ssl(bool $verifyPeer = true, bool $verifyHost = true, bool $verifyStatus = true) {
            $this->option(CURLOPT_SSL_VERIFYPEER, $verifyPeer);
            $this->option(CURLOPT_SSL_VERIFYHOST, $verifyHost);
            $this->option(CURLOPT_SSL_VERIFYSTATUS, $verifyStatus);
            
            return $this;
        }
        
        /**
         * Disables verification of SSL certificates for the cURL options.
         *
         * @return static Returns an instance of the class with SSL verification disabled.
         */
        public function noVerify(): static {
            $this->option(CURLOPT_SSL_VERIFYPEER, false);
            $this->option(CURLOPT_SSL_VERIFYHOST, false);
            $this->option(CURLOPT_SSL_VERIFYSTATUS, false);
            
            return $this;
        }
        
        /**
         * Sets the verbose mode for cURL.
         *
         * @param bool $set [optional] Whether to enable or disable verbose mode. Defaults to true.
         *
         * @return static Returns an instance of the class with the verbose mode set.
         */
        public function verbose(bool $set = true): static {
            $this->option(CURLOPT_VERBOSE, $set);
            
            return $this;
        }
        
        /**
         * Sets the CURLOP_CRLF option to true in the cURL options.
         *
         * @return static Returns an instance of the class with the CURLOP_CRLF option set to true.
         */
        public function crlf(): static {
            $this->option(CURLOPT_CRLF, true);
            
            return $this;
        }
        
        /**
         * Sets the CURLOPT_FRESH_CONNECT option for the request.
         *
         * @param bool $fresh Whether to use fresh connection or not (default is true).
         *                    If this is false, a cached connection may be used.
         *
         * @return static The current instance of the class.
         */
        public function fresh(bool $fresh = true): static {
            $this->option(CURLOPT_FRESH_CONNECT, $fresh);
            
            return $this;
        }
        
        /**
         * Sets the body for the cURL request.
         *
         * @param string $body The body to be set for the request.
         *
         * @return static Returns an instance of the class with the body set.
         */
        public function body(string $body): static {
            $this->body = $body;
            
            return $this;
        }
        
        /**
         * Adds a "Content-Type" header to the current instance.
         *
         * @param string $contentType The content type value to be added (default is "text/plain").
         * @param string $charset     The charset value for the content type (default is self::DEFAULT_CHARSET).
         *
         * @return static The current instance of the class.
         */
        public function contentType(string $contentType = 'text/plain', string $charset = self::DEFAULT_CHARSET): static {
            $this->addRawHeader('Content-Type: ' . $contentType . ($charset ? '; charset=' . $charset : ''));
            
            return $this;
        }
        
        /**
         * Set the request body as JSON and set the Content-Type header to application/json.
         *
         * @param string $json    The JSON string to send as the request body.
         * @param string $charset The character set to use for the Content-Type header (default is 'UTF-8').
         *
         * @return static The current instance of the class with the JSON and Content-Type headers set.
         */
        public function sendJson(string $json, string $charset = self::DEFAULT_CHARSET): static {
            $this->body($json);
            $this->contentType('application/json', $charset);
            
            return $this;
        }
        
        /**
         * Sets the request body to the given JSON data and sets the content type header to "application/json".
         *
         * @param mixed  $data    The JSON data to send.
         * @param string $charset The character encoding to use (default is 'UTF-8').
         *
         * @return static This object instance for method chaining.
         */
        public function sendJsonData(mixed $data, string $charset = self::DEFAULT_CHARSET): static {
            $this->body(json_encode($data));
            $this->contentType('application/json', $charset);
            
            return $this;
        }
        
        /**
         * Sends a plain text HTTP request with the specified body and charset.
         *
         * @param string $body    The body of the HTTP request (default is an empty string).
         * @param string $charset The character encoding of the plain text (default is the class's default charset).
         *
         * @return $this The current instance of the class.
         */
        public function sendPlainText(string $body = '', string $charset = self::DEFAULT_CHARSET): static {
            $this->body($body);
            $this->contentType('text/plain', $charset);
            
            return $this;
        }
        
        /**
         * Sets the body and content type headers to HTML.
         *
         * @param string $body    The body of the HTML (default is an empty string).
         * @param string $charset The character set for the HTML (default is 'UTF-8').
         *
         * @return $this The current instance of the class.
         */
        public function sendHtml(string $body = '', string $charset = self::DEFAULT_CHARSET): static {
            $this->body($body);
            $this->contentType('text/html', $charset);
            
            return $this;
        }
        
        /**
         * Sets the Content-Length header value and returns the current instance of the class.
         *
         * @param true|int $length The length value to set for the Content-Length header.
         *                         If $length is true and the postfields property is a string, the length of the
         *                         postfields string will be used. Otherwise, $length will be used as the value for the
         *                         Content-Length header.
         *
         * @return static The current instance of the class.
         */
        public function contentLength(true|int $length = true): static {
            $this->addHeader('Content-Length', ($length === true ? strlen($this->body) : $length));
            
            return $this;
        }
        
        /**
         * Set the maximum number of redirections for the cURL request.
         *
         * @param int $amount The maximum number of redirections.
         *
         * @return static The current instance of the class with the updated maximum number of redirections.
         */
        public function maxRedirections(int $amount): static {
            $this->option(CURLOPT_MAXREDIRS, $amount);
            
            return $this;
        }
        
        /**
         * Sets the timeout for the request.
         *
         * @param int $seconds The timeout duration in seconds.
         *
         * @return static The current instance of the class.
         */
        public function timeout(int $seconds): static {
            $this->option(CURLOPT_TIMEOUT, $seconds);
            
            return $this;
        }
        
        /**
         * Sets the timeout in milliseconds for the cURL request.
         *
         * @param int $millis The timeout in milliseconds.
         *                    Note: 1,000ms = 1s
         *
         * @return static The current instance of the class with the timeout set.
         */
        public function timeoutMs(int $millis): static {
            $this->option(CURLOPT_TIMEOUT_MS, $millis);
            
            return $this;
        }
        
        /**
         * Sets the cookies to be included in the request.
         *
         * @param array $cookies An associative array of cookies, where the key is the cookie name and the value is the
         *                       cookie value.
         *
         * @return static The current instance of the class with the cookies set.
         */
        public function cookies(array $cookies): static {
            $this->option(CURLOPT_COOKIE, 'Cookie: ' . http_build_query($cookies, '', '; '));
            
            return $this;
        }
        
        /**
         * Sets the value of the 'Accept' HTTP header.
         *
         * @param string $value The value to set for the 'Accept' header (default is '
         */
        public function accept(string $value = '*/*'): static {
            $this->addHeader('Accept', $value);
            
            return $this;
        }
        
        /**
         * Sets the user agent for the HTTP request.
         *
         * @param string $value The user agent string.
         *
         * @return static The current instance of the class.
         */
        public function userAgent(string $value): static {
            $this->option(CURLOPT_USERAGENT, $value);
            
            return $this;
        }
        
        /**
         * Sets the credentials for HTTP authentication.
         *
         * @param string $username The username for authentication.
         * @param string $password The password for authentication.
         *
         * @return static The current instance of the class with the updated credentials.
         */
        public function credentials(string $username, string $password): static {
            $this->option(CURLOPT_USERNAME, $username);
            $this->option(CURLOPT_PASSWORD, $password);
            
            return $this;
        }
        
        /**
         * Sets the basic authentication credentials for the cURL request.
         *
         * @param string $username The username for basic authentication.
         * @param string $password The password for basic authentication.
         *
         * @return static Returns an instance of the class with the set basic authentication credentials.
         */
        public function basicAuth(string $username, string $password): static {
            $this->basicAuthUsername = $username;
            $this->basicAuthPassword = $password;
            
            return $this;
        }
        
        /**
         * Executes an HTTP request and returns the response.
         *
         * @param string|null $url     The URL to request (optional). If provided, it will update the instance URL.
         * @param array       $options An array of options to configure the CURL request (default is an empty array).
         *
         * @return Response The response object containing the request result.
         */
        public function exec(?string $url = null, array $options = []): Response {
            if ($url) {
                $this->url = $url;
            }
            $this->curlOptions = array_replace($this->curlOptions, $options);
            $ch                = curl_init($this->url);
            
            # Set headers
            $headers = [];
            if ($this->headers) {
                $headers = array_map(function ($name, $value) {
                    return $name . ': ' . $value;
                }, array_keys($this->headers), $this->headers);
            }
            $optionHeaders                         = $this->curlOptions[CURLOPT_HTTPHEADER] ?? [];
            $optionHeaders                         = array_replace($headers, $optionHeaders);
            $this->curlOptions[CURLOPT_HTTPHEADER] = $optionHeaders;
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
            
            # Manage post fields
            if ($this->method === 'POST' && $this->postfields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postfields);
            }
            
            # Basic auth
            if ($this->basicAuthUsername !== '' && $this->basicAuthPassword !== '') {
                $this->option(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $this->option(CURLOPT_USERPWD, $this->basicAuthUsername . ':' . $this->basicAuthPassword);
            }
            
            # Set options
            curl_setopt_array($ch, $this->curlOptions);
            
            $response = curl_exec($ch);
            $result   = new Response($this->url, $this->curlOptions, $response, curl_getinfo($ch), curl_errno($ch), curl_error($ch));
            curl_close($ch);
            
            return $result;
        }
        
    }
