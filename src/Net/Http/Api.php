<?php
    
    namespace Xmgr\Net\Http;
    
    use Xmgr\Net\Http;
    
    /**
     * Class Api
     *
     * This class is responsible for providing an interface to interact with an API.
     */
    class Api {
        
        protected string $url = '';
        
        public function __construct() {
        }
        
        /**
         * @param $path
         *
         * @return void
         */
        public function request($path) {
            #return $this
        }
        
        /**
         * @param string $path
         *
         * @return Response
         */
        public function get(string $path): Response {
            return Http::get($this->url($path));
        }
        
        /**
         * @param string $path
         *
         * @return Response
         */
        public function post(string $path): Response {
            return Http::post($this->url($path));
        }
        
        /**
         * @param string $to
         *
         * @return string
         */
        public function url(string $to = ''): string {
            $url = $this->url;
            $to  = trim($to, '/');
            if ($to !== '') {
                $url .= '/' . $to;
            }
            
            return $url;
        }
        
        /**
         * @param mixed  $data
         * @param string $model
         *
         * @return mixed
         */
        public function model(mixed $data, string $model) {
            return new $model($data);
        }
        
    }
