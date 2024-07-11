<?php
    
    namespace Xmgr\Net\Http;
    
    use Xmgr\JsonResource;
    
    /**
     * Class Response
     *
     * Represents a response from a request.
     */
    class Response implements \Stringable {
        
        protected string $requestUrl;
        protected array  $options = [];
        protected string $response;
        protected array  $info;
        protected int    $errno;
        protected string $error;
        
        /**
         * __construct method.
         *
         * Initializes the class instance.
         *
         * @param string $url      The request URL.
         * @param array  $options  The request options.
         * @param mixed  $response The response data.
         * @param mixed  $info     The information about the request.
         * @param int    $errno    The error number if an error occurred during request.
         * @param string $error    The error message if an error occurred during request.
         *
         * @return void
         */
        public function __construct(string $url, array $options, $response, $info, $errno, $error) {
            $this->requestUrl = $url;
            $this->options    = $options;
            $this->response   = is_string($response) ? $response : '';
            $this->info       = is_array($info) ? $info : [];
            $this->errno      = (int)$errno;
            $this->error      = (string)$error;
        }
        
        /**
         * Returns the value of the response property.
         *
         * @return string The value of the response property.
         */
        public function body(): string {
            return $this->response;
        }
        
        /**
         * Get the JSON representation of the response as a JsonResource instance.
         *
         * @return JsonResource The JSON representation of the response.
         */
        public function json(): JsonResource {
            return new JsonResource($this->body());
        }
        
        /**
         * Retrieves information based on the specified option.
         *
         * @param int|null $option The option to retrieve information for.
         *
         * @return mixed The information corresponding to the specified option.
         */
        public function info(?int $option = null) {
            return ($option === null ? $this->info : arr($this->info, $option, null));
        }
        
        /**
         * Returns the string representation of the object.
         *
         * @return string The string representation of the object.
         */
        public function __toString(): string {
            return $this->response;
        }
        
    }
