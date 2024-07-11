<?php
    
    namespace Xmgr;
    
    /**
     * Class Job
     *
     * Represents a job that can be executed at a specific schedule.
     *
     * @package YourPackage
     */
    abstract class Job {
        
        protected DateTime  $time_start;
        protected DateTime  $time_end;
        protected int       $duration = 0;
        protected array     $log      = [];
        protected ?DateTime $datetime = null;
        
        /**
         * Handles the logic for the current class.
         *
         * This method should be implemented by a subclass to perform specific operations.
         *
         * @return mixed
         */
        abstract protected function handle();
        
        /**
         * Defines the schedule for running the execute method in the current class.
         *
         * This method needs to be implemented in the child classes to define the schedule for running the execute
         * method. The execute method will run according to the schedule defined in this method.
         *
         * @return bool
         */
        abstract protected function schedule(): bool;
        
        /**
         * Checks if the task can be executed.
         *
         * @return bool Returns true if the task can be executed, false otherwise.
         */
        final public function canBeExecuted(): bool {
            return $this->schedule() === true;
        }
        
        /**
         * Logs a message with associated data.
         *
         * @param string $message The message to be logged.
         * @param mixed  $data    The associated data to be logged.
         *
         * @return $this
         */
        final public function log(string $message, mixed $data): static {
            $this->log[] = new LogEntry($message, $data);
            
            return $this;
        }
        
        /**
         * Sets the DateTime value for the current object.
         *
         * @param mixed $datetime The value to set the DateTime to. This can be a string, a DateTime object, or any
         *                        other valid value that can be used to create a DateTime object.
         *
         * @return $this
         * @throws \Exception
         */
        final public function setDateTime(mixed $datetime): static {
            $this->datetime = new DateTime($datetime);
            
            return $this;
        }
        
        /**
         * Returns the datetime value.
         *
         * If the datetime value is set, it returns the stored datetime value.
         * If the datetime value is not set, it returns the current datetime.
         *
         * @return DateTime The datetime value.
         * @throws \Exception
         */
        final protected function datetime(): DateTime {
            if (!($this->datetime instanceof DateTime)) {
                $this->datetime = DateTime::now();
            }
            
            return $this->datetime;
        }
        
        /**
         * Executes the code in the execute method.
         *
         * @param bool $force
         *
         * @return void
         * @throws \Exception
         */
        final public function execute(bool $force = false): void {
            if ($this->canBeExecuted() || $force) {
                $this->time_start = DateTime::now();
                $this->handle();
                $this->time_end = DateTime::now();
                $this->duration = $this->time_end->timestamp(true) - $this->time_start->timestamp(true);
            }
            
        }
        
        /**
         * Runs the execute method of the current class.
         *
         * @param bool $force
         *
         * @return void
         * @throws \Exception
         */
        final public static function run(bool $force = false): void {
            $obj = new static();
            $obj->execute($force);
        }
        
    }
