<?php
    
    namespace Xmgr;
    
    /**
     * Class Event
     *
     * Represents an event that can be triggered and listened to.
     */
    class Event {
        
        public int    $id     = 0;
        public mixed  $result = 0;
        public array  $array  = [];
        public string $name   = '';
        public mixed  $data;
        public mixed  $obj;
        
        /**
         * @var array|\Closure[]
         *
         * Holds an array of listeners.
         * Each listener can be either an instance of \Closure or a class implementing a listener interface.
         * Listeners can be added to this array using the `addListener` method.
         */
        protected static array $listeners = [];
        
        /**
         * Constructor for the class.
         *
         * @param mixed        $name   The name of the object.
         * @param mixed|null  &$data   The data to be assigned to the object. By default, it is set to null.
         * @param mixed|null   $obj    An optional object to be assigned to the object. By default, it is set to null.
         * @param mixed|null   $result An optional result value to be assigned to the object. By default, it is set to
         *                             null.
         * @param int          $id     An optional ID to be assigned to the object. By default, it is set to 0.
         * @param array        $array  An optional array to be assigned to the object. By default, it is an empty
         *                             array.
         */
        public function __construct($name, &$data = null, mixed $obj = null, mixed $result = null, int $id = 0, array $array = []) {
            $this->name   = $name;
            $this->data   = &$data;
            $this->array  = $array;
            $this->id     = $id;
            $this->result = $result;
            $this->obj    = $obj;
        }
        
        /**
         * Registers multiple listeners for various events.
         *
         * This method iterates through the provided array of listeners and registers each listener to its
         * corresponding event by calling the {@link addListener()} method.
         *
         * @param array $listeners An associative array where the keys are event names and the values are listener
         *                         functions.
         *
         * @return void
         */
        final public static function registerListeners(array $listeners): void {
            foreach ($listeners as $event => $listener) {
                static::addListener($event, $listener);
            }
        }
        
        /**
         * Adds a listener to the specified event.
         *
         * @param string   $event    The name of the event to add the listener to.
         * @param callable $listener The listener function to be added.
         *
         * @return void
         */
        public static function addListener(string $event, mixed $listener): void {
            static::$listeners[$event][] = $listener;
        }
        
        /**
         * Check if there are listeners registered for a given event name.
         *
         * @param string $name The name of the event to check for listeners.
         *
         * @return bool Returns true if listeners exist for the given event name, otherwise false.
         */
        public static function hasListenersFor(string $name): bool {
            return array_key_exists($name, static::$listeners);
        }
        
        /**
         * Triggers the given event by calling all the registered listeners for that event name.
         *
         * @param Event $event The event object to be triggered.
         *
         * @return void
         */
        final public static function trigger(Event $event): void {
            if (static::hasListenersFor($event->name)) {
                foreach (static::$listeners[$event->name] as $listener) {
                    if (is_callable($listener)) {
                        $listener($event);
                    }
                    if (is_string($listener) && class_exists($listener)) {
                        $listener = new $listener();
                    }
                    if ($listener instanceof Listener && method_exists($listener, 'handle')) {
                        $listener->handle();
                    }
                }
            }
        }
        
    }
