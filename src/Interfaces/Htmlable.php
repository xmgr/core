<?php
    
    namespace Xmgr\Interfaces;
    
    /**
     * Interface Htmlable
     *
     * Represents an object that can be converted to HTML.
     *
     * @package App\Interfaces
     */
    interface Htmlable {
        
        /**
         * Converts the object to its HTML representation.
         *
         * This method takes the current object and generates its HTML representation.
         * The generated HTML can be used to display the object in a web page or within an HTML email.
         *
         * @return string The HTML representation of the object.
         */
        public function toHtml(): string;
        
    }
