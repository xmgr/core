<?php
    
    namespace Xmgr\Controllers\Request;
    
    use Xmgr\Command;
    use Xmgr\Console;
    
    /**
     * Class CliController
     *
     * @package Your\Namespace
     *
     * This class is responsible for sending the command using the Command class.
     * It extends the BaseRequestController class.
     */
    class CliController extends BaseRequestController {
        
        /**
         * Sends a command using the Command::run() method and handles any exceptions that occur.
         *
         * This method executes the Command::run() method to send a command. If an exception is thrown,
         * it catches the exception and displays an error message using the Console::error() method,
         * followed by the trace of the exception using the Console::secondary() method.
         *
         * @return void
         */
        public function send(): void {
            try {
                Command::run();
            } catch (\Exception $e) {
                echo Console::error($e);
                echo Console::secondary($e->getTraceAsString());
            }
        }
        
    }
