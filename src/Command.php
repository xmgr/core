<?php
    
    namespace Xmgr;
    
    use Xmgr\Exceptions\CommandExecutionFailedException;
    
    /**
     * Class Command
     *
     * Represents a command execution.
     */
    class Command {
        
        /**
         * Executes a command based on the provided string.
         *
         * @throws \Exception If the provided command is invalid or unknown.
         */
        final public static function run(): void {
            $cmd = Console::command();
            if ($cmd) {
                $class  = str_pascal(Console::cmdClass());
                $action = str_snake(Console::cmdAction());
                if ($class && $action) {
                    $fqcn = "\\App\\Console\\Commands\\$class";
                    if (class_exists($fqcn)) {
                        $obj    = new $fqcn();
                        $method = $action;
                        try {
                            if (method_exists($obj, $method)) {
                                $obj->$method();
                            }
                        } catch (\Throwable $e) {
                            throw $e;
                        }
                    } else {
                        throw new CommandExecutionFailedException("Invalid command \"$cmd\" - command \"$class\" not found.");
                    }
                } else {
                    throw new CommandExecutionFailedException('Invalid or unknown command!');
                }
            } else {
                throw new CommandExecutionFailedException('No command provided!');
            }
        }
        
        /**
         * Prints "Test OK" along with the current date and time.
         *
         * @return void
         */
        public function test() {
            echo "\nTest OK (" . date('c') . ")\n\n";
        }
        
    }
