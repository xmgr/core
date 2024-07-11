<?php
    
    namespace Xmgr;
    
    /**
     * Class HttpResponse
     *
     * The HttpResponse class represents an HTTP response.
     */
    class Response {
        
        protected string $contentType = 'text/html';
        protected string $content     = '';
        protected int    $status      = 200;
        protected array  $headers     = [];
        
        /**
         * Constructs a new instance of the class.
         *
         * @param string $content The content of the object. Default is an empty string.
         *
         * @return void
         */
        public function __construct(mixed $content = '') {
            if (is_string($content)) {
                $this->setContent($content);
            }
            if ($content instanceof View) {
                $this->setContent($content->render());
            }
            if ($content instanceof static) {
                $this->setContent($content->content());
                $this->setStatus($content->status());
                $this->addHeaders($content->headers());
                $this->contentType = $content->type();
            }
        }
        
        /**
         * Retrieves the content of this object.
         *
         * @return string The content of this object as a string.
         */
        public function content(): string {
            return $this->content;
        }
        
        /**
         * Retrieves the status value of this object.
         *
         * @return int The status value of this object.
         */
        public function status(): int {
            return $this->status;
        }
        
        /**
         * Gets the headers associated with the object.
         *
         * @return array The headers array.
         */
        public function headers(): array {
            return $this->headers;
        }
        
        /**
         * Get the type of the object.
         *
         * @return string The type of the object.
         */
        public function type(): string {
            return $this->contentType;
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
         * Sets the content of the object.
         *
         * @param mixed $content The content to be set.
         *                       Can be of any type, it will be converted to a string representation.
         */
        protected function setContent(mixed $content): void {
            $this->content = (string)$content;
        }
        
        /**
         * Sets the response content to be in JSON format.
         *
         * @param mixed $data The data to be encoded as JSON.
         *
         * @return $this The current object instance.
         */
        public function json(mixed $data, int $status = 200): static {
            $this->setStatus($status);
            $this->contentType = 'application/json';
            $this->setContent(json_encode($data));
            
            return $this;
        }
        
        
        /**
         * Sets the HTTP response status code.
         *
         * @param int $status The HTTP status code to set. Default is 200.
         *
         * @return $this
         */
        public function setStatus(int $status = 200): static {
            $this->status = $status;
            
            return $this;
        }
        
        /**
         * Sends the specified headers to the client.
         *
         * @param array $headers An associative array with the header names as keys and the header values as values.
         *
         * @return void
         */
        protected function sendHeaders(array $headers = []): void {
            $this->addHeaders($headers);
            foreach ($headers as $key => $value) {
                header("$key: $value");
            }
        }
        
        /**
         * Sends the HTTP response with the specified content type and content.
         *
         * This method sets the "Content-Type" header with the specified content type
         * and then outputs the content to the browser.
         *
         * @param null $data
         *
         * @return void
         */
        public function send($data = null): void {
            if ($data !== null) {
                $this->setContent($data);
            }
            header("Content-Type: $this->contentType");
            $this->sendHeaders();
            http_response_code($this->status);
            
            echo $this->content;
        }
        
        /**
         * Sets the content type to "text/html" and the content to the provided HTML.
         *
         * This method is used to set the content type to "text/html" and assign the
         * provided HTML to the content variable. It returns the current object instance.
         *
         * @param string $html The HTML content to be assigned.
         *
         * @return self The current object instance.
         */
        public function html(string $html): static {
            $this->contentType = 'text/html';
            $this->content     = $html;
            
            return $this;
        }
        
        /**
         * Sets the content type to "text/plain" and assigns the specified text as the content.
         *
         * This method is used to set the content type header to "text/plain" and assign the specified text
         * as the content of the response object.
         *
         * @param string $text The text content to be set.
         *
         * @return static Returns the current instance of the response object.
         */
        public function text(string $text): static {
            $this->contentType = 'text/plain';
            $this->content     = $text;
            
            return $this;
        }
        
        /**
         * Converts the object to a string representation.
         *
         * @return string The string representation of the object.
         */
        public function __toString() {
            return $this->content;
        }
        
        /**
         * Downloads a file.
         *
         * @param string $file The path to the file to be downloaded.
         *
         * @return string
         */
        public function downloadFile(string $file): string {
            if (is_file($file)) {
                header('Content-Description: File Transfer');
                #header('Content-Type: ' . $type);
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                ob_clean();
                flush();
                
                return file_get_contents($file);
            }
            
            abort(404, 'Not found!');
        }
        
        /**
         * Downloads a file.
         *
         * @param string $content  The content of the file to be downloaded.
         * @param string $filename The name of the file to be downloaded.
         *
         * @return void
         */
        public function download(string $content, string $filename) {
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
            
        }
        
    }
