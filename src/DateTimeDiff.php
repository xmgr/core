<?php
    
    namespace Xmgr;
    
    /**
     * Class DateTimeDiff
     *
     * Represents the difference between two DateTime objects and provides various methods to retrieve and manipulate
     * the difference.
     */
    class DateTimeDiff {
        
        protected DateTime $datetime1;
        protected DateTime $datetime2;
        
        protected \DateInterval $interval;
        
        /**
         * Constructor for creating a new instance of the class.
         *
         * @param mixed $datetime1 The first datetime parameter.
         * @param mixed $datetime2 The second datetime parameter.
         *
         * @return void
         * @throws \Exception
         */
        public function __construct(mixed $datetime1, mixed $datetime2) {
            $this->datetime1 = (new DateTime($datetime1));
            $this->datetime2 = (new DateTime($datetime2));
            $this->interval  = $this->datetime1->toDateTime()->diff($this->datetime2->toDateTime());
        }
        
        /**
         * Retrieves the stored DateInterval object.
         *
         * @return \DateInterval|false The stored DateInterval object if it exists, or false if no interval is stored.
         */
        public function getInterval(): \DateInterval|false {
            return $this->interval;
        }
        
        /**
         * Converts the time duration to a human-readable string format.
         *
         * @return string The time duration represented as a string.
         */
        public function asString(): string {
            $stack   = [];
            $years   = $this->years();
            $monts   = $this->months();
            $days    = $this->days();
            $hours   = $this->hours();
            $minutes = $this->minutes();
            $seconds = $this->seconds();
            if ($years) {
                $stack[] = $years . ' ' . __('year' . ($years > 1 ? 's' : ''));
            }
            if ($monts) {
                $stack[] = $monts . ' ' . __('month' . ($monts > 1 ? 's' : ''));
            }
            if ($days) {
                $stack[] = $days . ' ' . __('day' . ($days > 1 ? 's' : ''));
            }
            if ($hours) {
                $stack[] = $hours . ' ' . __('hour' . ($hours > 1 ? 's' : ''));
            }
            if ($minutes) {
                $stack[] = $minutes . ' ' . __('minute' . ($minutes > 1 ? 's' : ''));
            }
            if ($seconds) {
                $stack[] = $seconds . ' ' . __('second' . ($seconds > 1 ? 's' : ''));
            }
            
            if (count($stack) > 1) {
                $last                     = array_pop($stack);
                $stack[count($stack) - 1] = $stack[count($stack) - 1] . ' ' . __('and') . ' ' . $last;
            }
            
            return implode(', ', $stack);
        }
        
        /**
         * Check if there is a difference between datetime1 and datetime2.
         *
         * @return bool True if datetime1 is different from datetime2, false otherwise.
         *
         */
        public function isDifferent(): bool {
            return $this->datetime1->timestamp() !== $this->datetime2->timestamp();
        }
        
        /**
         * Checks if the format of the two given DateTime objects are the same.
         *
         * @param string $format The format to compare the DateTime objects. The format should be a valid format
         *                       accepted by the DateTime::format() method.
         *
         * @return bool Returns true if the format of the two DateTime objects are the same, false otherwise.
         */
        public function sameFormat(string $format): bool {
            return $this->datetime1->format($format) === $this->datetime2->format($format);
        }
        
        /**
         * Checks if the day of the week of the first datetime is the same as the day of the week of the second
         * datetime.
         *
         * @return bool Returns true if the day of the week is the same, false otherwise.
         */
        public function isSameDayOfWeek(): bool {
            return $this->sameFormat('N');
        }
        
        /**
         * Checks if two datetimes represent the same day.
         *
         * @return bool Returns true if the two datetimes represent the same day, otherwise returns false.
         */
        public function isTheSameDay(): bool {
            return $this->sameFormat('Y-m-d');
        }
        
        /**
         * Checks if the time of the current instance is the same as the time of another instance.
         *
         * @param bool $withSeconds Whether to compare the seconds as well. Defaults to true.
         *
         * @return bool Returns true if the time is the same, false otherwise.
         */
        public function isTheSameTime(bool $withSeconds = true): bool {
            return $this->sameFormat('H:i' . ($withSeconds ? ':s' : ''));
        }
        
        /**
         * Checks if the current date is in the same year as the specified date.
         *
         * @return bool Returns true if the current date is in the same year as the specified date, false otherwise.
         */
        public function isTheSameYear(): bool {
            return $this->sameFormat('Y');
        }
        
        /**
         * Get the number of years in the interval.
         *
         * @return int The number of years in the interval.
         */
        public function years(): int {
            return $this->interval->y;
        }
        
        /**
         * Get the number of months in the interval.
         *
         * @return int The number of months in the interval.
         */
        public function months(): int {
            return $this->interval->m;
        }
        
        
        /**
         * Get the total number of months in the interval.
         *
         * @return int The total number of months in the interval.
         */
        public function totalMonths(): int {
            return ($this->years() * 12) + $this->months();
        }
        
        /**
         * Get the number of days in the interval.
         *
         * @return int The number of days in the interval.
         */
        public function days(): int {
            return $this->interval->d;
        }
        
        /**
         * Get the number of days between the two datetime parameters.
         *
         * @return int The number of days between the two datetime parameters.
         */
        public function totalDays(): int {
            return $this->interval->days;
        }
        
        /**
         * Get the total number of hours from the interval.
         *
         * @return int The total number of hours from the interval.
         */
        public function hours(): int {
            return $this->interval->h;
        }
        
        /**
         * Get the number of minutes in the interval.
         *
         * @return int The number of minutes in the interval.
         *
         */
        public function minutes(): int {
            return $this->interval->i;
        }
        
        /**
         * Get the total seconds from the interval.
         *
         * @return int The total seconds from the interval.
         *
         */
        public function seconds(): int {
            return $this->interval->s;
        }
        
        /**
         * Get the total number of seconds between two given datetime objects.
         *
         * @return int The total number of seconds between the two datetime objects.
         */
        public function totalSeconds(): int {
            return $this->datetime2->timestamp() - $this->datetime1->timestamp();
        }
        
        /**
         * Calculates the total number of minutes based on the total number of seconds.
         *
         * @param bool $floor Whether to floor or ceil the result. Defaults to true (floor).
         *
         * @return int The total number of minutes.
         */
        public function totalMinutes(bool $floor = true): int {
            $minutes = $this->totalSeconds() / 60;
            
            return ($floor ? floor($minutes) : ceil($minutes));
        }
        
        /**
         * Calculate the total minutes as a float based on the total seconds.
         *
         * @param bool $floor If true, the function will round down the result to the nearest whole number. Defaults to
         *                    true.
         *
         * @return int|float The total minutes calculated as a float from the total seconds. If $floor is true, the
         *                   result will be rounded down to the nearest whole number.
         *
         */
        public function totalMinutesAsFloat(bool $floor = true): int|float {
            return $this->totalSeconds() / 60;
        }
        
        /**
         * Calculate the total hours based on the total minutes.
         *
         * @param bool $floor If true, the function will round down the result to the nearest whole number. Defaults to
         *                    true.
         *
         * @return int The total hours calculated from the total minutes. If $floor is true, the result will be rounded
         *             down.
         *
         */
        public function totalHours(bool $floor = true): int {
            $hours = $this->totalMinutes() / 60;
            
            return ($floor ? floor($hours) : ceil($hours));
        }
        
        /**
         * Calculate the total hours as a float based on the total seconds.
         *
         * @return int|float The total hours calculated as a float from the total seconds.
         */
        public function totalHoursAsFloat(): int|float {
            return $this->totalSeconds() / 60 / 60;
        }
        
        /**
         * Determines if datetime1 is older than datetime2 by a certain number of seconds.
         *
         * @param int $seconds The number of seconds by which to compare the datetime objects.
         *
         * @return bool True if datetime1 is older than datetime2 by the specified number of seconds, false otherwise.
         */
        public function secondsOlder(int $seconds): bool {
            return ($this->datetime1->timestamp() < $this->datetime2->timestamp()) && ($this->totalSeconds() > abs($seconds));
        }
        
        
    }
