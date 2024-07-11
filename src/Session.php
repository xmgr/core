<?php
    
    namespace Xmgr;
    
    /**
     * Class Session
     *
     * The Session class handles the management of user sessions.
     */
    class Session {
        
        /** @var ?self */
        protected static ?self $i = null;
        
        /**
         * Returns an instance of the current class.
         *
         * If an instance of the current class exists, it will be returned. Otherwise, a new instance will be created
         * and stored for future use.
         *
         * @return self An instance of the current class.
         */
        public static function i(): self {
            static::$i = static::$i ?? new static();
            
            return static::$i;
        }
        
    }
