<?php
    
    namespace Xmgr\Net;
    
    use Xmgr\Net\Http\Response;
    
    /**
     * Represents a class for making HTTP requests using different HTTP methods.
     */
    class Http {
        
        /**
         * Send a GET request to the specified URL with optional headers.
         *
         * @param string $url     The URL to send the GET request to.
         * @param array  $headers Optional headers to include in the request.
         *
         * @return Response The response data received from the GET request, or an empty string if no data
         *                             is received.
         */
        public static function get(string $url, array $headers = []): Response {
            $request = new Curl($url, $headers);
            
            return $request->exec();
        }
        
        /**
         * Sends a POST request to the specified URL.
         *
         * @param string $url     The URL to send the request to.
         * @param string $body    The body of the request (default is an empty string).
         * @param array  $headers An associative array of HTTP headers (default is an empty array).
         *
         * @return Response The response of the POST request.
         */
        public static function post(string $url, string $body = '', array $headers = []): Response {
            $request = new Curl($url, $headers, 'POST');
            $request->body($body);
            
            return $request->exec();
        }
        
        /**
         * Creates and returns a new instance of the class.
         *
         * @param string $url     The URL to request.
         * @param array  $headers An associative array of HTTP headers.
         * @param string $method  The HTTP method to use (default is 'GET').
         *
         * @return Curl The newly created instance of the class.
         */
        public static function request(string $url, array $headers = [], string $method = 'GET'): Curl {
            return new Curl($url, $headers, $method);
        }
        
    }
