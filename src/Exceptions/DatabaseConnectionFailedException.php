<?php
    
    namespace Xmgr\Exceptions;
    
    use Xmgr\BaseException;
    
    /**
     * Exception class for when a database connection fails.
     *
     * This exception is used when a connection to the database cannot be established or is lost.
     * It extends the BaseException class, which provides basic exception functionality.
     *
     * @package YourPackageName
     */
    class DatabaseConnectionFailedException extends BaseException {
    
    }
