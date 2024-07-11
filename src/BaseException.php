<?php
    
    namespace Xmgr;
    
    /**
     * Class BaseException
     *
     * Represents a base exception that can be thrown in an application.
     */
    class BaseException extends \Exception {
        
        protected ?\Throwable $previous;
        
        /**
         * Constructs a new instance of the class.
         *
         * @param mixed           $message  The error message or an instance of Throwable.
         * @param int             $code     The error code (default is 0).
         * @param \Throwable|null $previous The previous throwable used for chaining exceptions (default is null).
         *
         */
        public function __construct($message, int $code = 0, \Throwable $previous = null) {
            if ($message instanceof \Throwable) {
                if (!$previous) {
                    $previous = $message;
                }
                $message = $message->getMessage();
            }
            if ($previous) {
                $this->previous = $previous;
            }
            parent::__construct($message, $code, $previous);
        }
        
    }
