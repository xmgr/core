<?php
    
    namespace Xmgr;
    
    /**
     * Class JsonResource
     *
     * This class represents a JSON resource that can be used to store and manipulate JSON data.
     * It extends the Data class.
     *
     * @package YourPackage
     */
    class JsonResource extends Data {
        
        protected array|object $data = [];
        
        /**
         * Constructs a new instance.
         *
         * @param mixed $json  The JSON data to initialize the instance. This can be an instance of the current class,
         *                     an instance of `Net\Http\Response`, a string representing JSON data,
         *                     an object that can be converted to JSON, or an array.
         * @param bool  $assoc (optional) Whether to return associative arrays. Default is `true`.
         *
         * @return void
         */
        public function __construct(mixed $json, bool $assoc = true) {
            $data = [];
            switch (true) {
                case ($json instanceof self):
                    $data = $json->export();
                    break;
                case $json instanceof Net\Http\Response:
                    $data = json2array($json->body());
                    break;
                case is_string($json):
                    $data = json2array($json, $assoc);
                    break;
                case is_object($json):
                    $data = json_decode(json_encode($json), true);
                    break;
                case is_array($json):
                    $data = $json;
                    break;
                default:
                    break;
            }
            parent::__construct($data);
        }
        
    }
