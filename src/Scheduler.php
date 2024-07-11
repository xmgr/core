<?php
    
    namespace Xmgr;
    
    /**
     * Class Scheduler
     *
     * The Scheduler class allows you to schedule tasks based on various conditions,
     * such as daily recurrence, weekdays, specific dates, and times.
     *
     * @package MyApp
     */
    class Scheduler {
        
        protected ?DateTime $datetime = null;
        
        /**
         * Class constructor.
         *
         * @param mixed $datetime The datetime value to be used. Defaults to 'now' if not provided.
         *
         * @throws \Exception
         */
        public function __construct(mixed $datetime = 'now') {
            $this->datetime = new DateTime($datetime);
        }
        
        /** @var array */
        protected array $schedulers = [];
        
        /**
         * Adds a condition to the schedulers array.
         *
         * @param mixed $condition The condition to be added.
         *
         * @return static Returns a reference to the current object.
         */
        protected function add(mixed $condition): static {
            if (!$this->schedulers) {
                $this->schedulers[] = [];
            }
            $this->schedulers[count($this->schedulers) - 1][] = $condition;
            
            return $this;
        }
        
        /**
         * Add a daily recurrence to the schedule.
         *
         * @return $this
         */
        public function daily(): static {
            $this->add(true);
            
            return $this;
        }
        
        /**
         * Sets the date to the nearest weekend.
         *
         * @return $this
         */
        public function onWeekend(): static {
            $this->add($this->datetime->isWeekend());
            
            return $this;
        }
        
        /**
         * Adds the current time to the date and time object for all weekdays (Monday to Friday).
         * Returns the updated date and time object.
         *
         * @return $this
         */
        public function onWeekdays(): static {
            $this->add($this->datetime->isWeekday());
            
            return $this;
        }
        
        /**
         * Sets the time of the current instance to the specified hour and minute.
         *
         * @param int $hour   The hour to set.
         * @param int $minute The minute to set. Defaults to 0 if not provided.
         *
         * @return $this Returns the modified instance of the class.
         */
        public function at(int $hour, int $minute = 0): static {
            $this->add($this->datetime->timeIs($hour, $minute));
            
            return $this;
        }
        
        /**
         * Sets the hours of the current instance to the specified array of hours.
         *
         * @param array $hours An array of hours to set.
         *
         * @return $this Returns the modified instance of the class.
         */
        public function atHours(array $hours) {
            $this->add($this->datetime->hoursIs($hours));
            
            return $this;
        }
        
        /**
         * Sets the date to the next Monday.
         *
         * @return $this
         */
        public function onMonday() {
            $this->add($this->datetime->isMonday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onTuesday() {
            $this->add($this->datetime->isTuesday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onWednesday() {
            $this->add($this->datetime->isWednesday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onThursday() {
            $this->add($this->datetime->isThursday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onFriday() {
            $this->add($this->datetime->isFriday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onSaturday() {
            $this->add($this->datetime->isSaturday());
            
            return $this;
        }
        
        /**
         * @return $this
         */
        public function onSunday() {
            $this->add($this->datetime->isSunday());
            
            return $this;
        }
        
        /**
         * @param int $dayOfWeek
         *
         * @return $this
         */
        public function onDay(int $dayOfWeek) {
            $this->add($this->datetime->dayOfWeekIs($dayOfWeek));
            
            return $this;
        }
        
        /**
         * @param array $daysOfWeek
         *
         * @return $this
         */
        public function onDaysOfWeek(array $daysOfWeek) {
            $this->add(in_array($this->datetime->dayOfWeekIs($daysOfWeek), $daysOfWeek));
            
            return $this;
        }
        
        /**
         * @param int $month
         *
         * @return $this
         */
        public function onMonth(int $month) {
            $this->add($this->datetime->monthIs($month));
            
            return $this;
        }
        
        /**
         * @param array $months
         *
         * @return $this
         */
        public function onMonths(array $months) {
            $this->add($this->datetime->monthIs($months));
            
            return $this;
        }
        
        /**
         * @return DateTime|null
         */
        public function customCondition() {
            return $this->datetime;
        }
        
        /**
         * Adds an empty scheduler to the list of schedulers.
         *
         * @return static
         */
        public function or(): static {
            $this->schedulers[] = [];
            
            return $this;
        }
        
    }
