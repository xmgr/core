<?php
    
    namespace Xmgr;
    
    /**
     * Converts the DateTime object into a DateTimeImmutable object.
     *
     * This method converts the internal DateTime object into a DateTimeImmutable object. If the internal object is
     * already a DateTimeImmutable, the method returns it as is. Otherwise, it creates a new DateTimeImmutable object
     * from the internal DateTime object and returns it.
     *
     * @return \DateTimeImmutable The DateTimeImmutable object.
     */
    class DateTime {
        
        protected \DateTime|\DateTimeImmutable $datetime;
        
        protected string $defaultFormat = 'Y-m-d H:i:s';
        
        protected bool $immutable = false;
        
        /**
         * @var array|static[]
         */
        protected array $saves = [];
        
        /**
         * Constructor method for initializing the object.
         *
         * @param mixed      $datetime The initial datetime value.
         * @param mixed|null $timezone The optional timezone value. If not provided, it defaults to null.
         *
         * @return void
         * @throws \Exception
         */
        public function __construct(mixed $datetime = 'now', mixed $timezone = null, bool $immutable = false) {
            $this->setup($datetime, $timezone, $immutable);
        }
        
        /**
         * Returns a new instance of the current class representing the current time
         *
         * @return static A new instance of the current class representing the current time.
         * @throws \Exception
         */
        public static function now($timezone = null): static {
            return new static('now', $timezone);
        }
        
        /**
         * Gets the current date with the time reset to 00:00:00.
         * This function returns the current date using the DateTime class and resets the time to 00:00:00.
         * This can be useful when you only need the date without the time component.
         *
         * Example:
         * $date = today();
         * echo $date;  // Output: 2021-01-01
         *
         * @return DateTime The current date with the time reset to 00:00:00
         * @throws \Exception
         */
        public static function today(): DateTime {
            return static::now()->resetTime();
        }
        
        /**
         * Sets the date and time.
         *
         * The method sets the date and time based on the provided parameters. If the $datetime parameter is an integer,
         * it creates a new \DateTime object using the specified timestamp in the format Y-m-d H:i:s. If the $datetime
         * parameter is a string, it converts the string to a timestamp using strtotime, and then creates a new
         * \DateTime object using the resulting timestamp and the default format from the class. If the $datetime
         * parameter is an instance of \DateTime, it directly sets the date and time. If the $datetime parameter is an
         * instance of the current class, it converts it to a \DateTime object using the toDateTime method. If the
         * $datetime parameter is an instance of \DateTimeImmutable, it creates a new \DateTime object using the
         * DateTimeImmutable object. If none of the above conditions are met, it creates a new \DateTime object with
         * the current date and time.
         *
         * The method then calls the timezone method with the $timezone parameter to set the timezone.
         *
         * @param mixed $datetime  The date and time to set. It can be an integer representing a timestamp, a string
         *                         representing a valid date and time, an instance of \DateTime, an instance of the
         *                         current class, or an instance of \DateTimeImmutable.
         * @param mixed $timezone  The timezone to set. It can be an instance of \DateTimeZone or a string representing
         *                         a valid timezone. If null, no change will be made.
         *
         * @return $this
         * @throws \Exception
         */
        public function setup(mixed $datetime = 'now', mixed $timezone = null, bool $immutable = false): static {
            switch (true) {
                # Teimstamp
                case (is_int($datetime)):
                    if ($this->immutable) {
                        $this->datetime = (\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date($this->defaultFormat, $datetime)));
                    } else {
                        $this->datetime = (\DateTime::createFromFormat('Y-m-d H:i:s', date($this->defaultFormat, $datetime)));
                    }
                    break;
                # Microtime
                case (is_float($datetime)):
                    if ($immutable) {
                        $this->datetime = \DateTimeImmutable::createFromFormat('U.u', $datetime);
                    } else {
                        $this->datetime = \DateTime::createFromFormat('U.u', $datetime);
                    }
                    break;
                # NOW oder True
                case($datetime === true):
                case(is_string($datetime) && strtolower($datetime) === 'now'):
                    if ($immutable) {
                        $this->datetime = \DateTimeImmutable::createFromFormat('U.u', microtime(true));
                    } else {
                        $this->datetime = \DateTime::createFromFormat('U.u', microtime(true));
                    }
                    break;
                # String
                case (is_string($datetime)):
                    if ($immutable) {
                        $this->datetime = (\DateTimeImmutable::createFromFormat($this->defaultFormat, date($this->defaultFormat, strtotime($datetime))));
                    } else {
                        $this->datetime = (\DateTime::createFromFormat($this->defaultFormat, date($this->defaultFormat, strtotime($datetime))));
                    }
                    break;
                # DateTime
                case ($datetime instanceof \DateTime):
                    $this->datetime = ($immutable ? \DateTimeImmutable::createFromMutable($datetime) : $datetime);
                    break;
                # static
                case ($datetime instanceof static):
                    $this->datetime = $immutable ? $this->toDateTimeImmutable() : $datetime->toDateTime();
                    break;
                # DateTimeImmutable
                case ($datetime instanceof \DateTimeImmutable):
                    $this->datetime = ($immutable ? $datetime : \DateTime::createFromImmutable($datetime));
                    break;
                # Default
                default:
                    if ($immutable) {
                        $this->datetime = \DateTimeImmutable::createFromFormat('U.u', microtime(true));
                    } else {
                        $this->datetime = \DateTime::createFromFormat('U.u', microtime(true));
                    }
                    break;
            }
            $this->setTimezone($timezone);
            
            return $this;
        }
        
        /**
         * Determines whether the object is mutable or not.
         *
         * @return bool Returns true if the object is mutable (an instance of \DateTime), false otherwise.
         */
        public function isMutable() {
            return $this->datetime instanceof \DateTime;
        }
        
        /**
         * Checks if the current object's underlying datetime is of type \DateTimeImmutable.
         *
         * @return bool True if the datetime is of type \DateTimeImmutable, false otherwise.
         */
        public function isImmutable(): bool {
            return $this->datetime instanceof \DateTimeImmutable;
        }
        
        /**
         * Sets the timestamp of the DateTime object to the specified value.
         *
         * @param int $timestamp       The UNIX timestamp representing the date and time. This can also be a float with
         *                             microseconds.
         *
         * @return $this The current object instance with the updated timestamp.
         */
        public function setTimestamp(int $timestamp): static {
            $this->datetime->setTimestamp($timestamp);
            
            return $this;
        }
        
        /**
         * Converts the internal \DateTime object into an immutable \DateTimeImmutable object if it is currently a
         * \DateTime object.
         *
         * @return static Returns a new instance of the class with the \DateTime object transformed into a
         *                \DateTimeImmutable object if necessary. The original object remains unchanged.
         */
        public function immutable(): static {
            if ($this->datetime instanceof \DateTime) {
                $this->datetime = \DateTimeImmutable::createFromMutable($this->datetime);
            }
            
            return $this;
        }
        
        /**
         * Converts the current datetime object to a mutable DateTime object if it is currently an instance of
         * DateTimeImmutable.
         *
         * @return static The current DateTime object, now mutable.
         */
        public function mutable(bool $mutable = true): static {
            if (!$mutable) {
                return $this->immutable();
            }
            if ($this->datetime instanceof \DateTimeImmutable) {
                $this->datetime = \DateTime::createFromImmutable($this->datetime);
            }
            
            return $this;
        }
        
        /**
         * Retrieves the DateTime object.
         *
         * The method returns the DateTime object associated with the current instance.
         *
         * @return \DateTime The DateTime object associated with the current instance.
         */
        public function toDateTime(): \DateTime {
            return ($this->datetime instanceof \DateTime ? $this->datetime : \DateTime::createFromImmutable($this->datetime));
        }
        
        /**
         * Converts the internal datetime value to an instance of DateTimeImmutable.
         *
         * @return \DateTimeImmutable The converted DateTimeImmutable object.
         */
        public function toDateTimeImmutable(): \DateTimeImmutable {
            return ($this->datetime instanceof \DateTimeImmutable ? $this->datetime : \DateTimeImmutable::createFromMutable($this->datetime));
        }
        
        /**
         * Calculates the difference between the current datetime object and the given datetime object.
         *
         * @param mixed $datetime The datetime object to calculate the difference with.
         *
         * @return DateTimeDiff difference between the current datetime object and the given datetime object.
         * @throws \Exception If the $datetime parameter is not a valid datetime object.
         */
        public function diff(mixed $datetime = 'now'): DateTimeDiff {
            return new DateTimeDiff($this->toDateTime(), (new static($datetime))->toDateTime());
        }
        
        
        /**
         * Checks if the current datetime object is earlier than the given datetime.
         *
         * @param mixed $datetime The datetime object or string representation to compare against.
         *
         * @return bool True if the current datetime is earlier than the given datetime, otherwise false.
         * @throws \Exception
         */
        public function earlierThan(mixed $datetime): bool {
            return $this->toDateTime() < (new static($datetime))->toDateTime();
        }
        
        /**
         * Checks if the current time represented by this object is later than the specified datetime.
         *
         * @param mixed $datetime The datetime object or string to compare against.
         *
         * @return bool Returns true if the current time is later than the specified datetime, false otherwise.
         * @throws \Exception
         */
        public function laterThan(mixed $datetime): bool {
            return $this->toDateTime() > (new static($datetime))->toDateTime();
        }
        
        /**
         * Checks if the current datetime object is older than the given datetime.
         *
         * @param mixed $datetime The datetime to compare with.
         *
         * @return bool Returns true if the current datetime is older than the given datetime, false otherwise.
         * @throws \Exception
         */
        public function olderThan(mixed $datetime): bool {
            return $this->earlierThan($datetime);
        }
        
        /**
         * Checks if the current time is older than the specified number of seconds.
         *
         * @param int $seconds The number of seconds to compare with the current time.
         *
         * @return bool True if the current time is older than the specified number of seconds, false otherwise.
         * @throws \Exception
         */
        public function olderThanInSeconds(int $seconds): bool {
            return $this->diff(static::now())->secondsOlder($seconds);
        }
        
        /**
         * Formats the current datetime.
         *
         * The method uses the provided format string to format the current datetime. It delegates the formatting to
         * the format() method of the underlying DateTime object.
         *
         * @param string $format The format string to be used when formatting the datetime. This should follow the
         *                       format accepted by the format() method of the underlying DateTime object.
         *
         * @return string The formatted datetime string.
         */
        public function format(string $format): string {
            return $this->datetime->format($format);
        }
        
        /**
         * Converts the datetime value to ISO8601 format.
         *
         * @return string The datetime value in ISO8601 format.
         */
        public function toISO8601(bool $withT = true, bool $withOffset = false): string {
            return $this->format('Y-m-d' . ($withT ? '\\T' : '') . 'H:i:s' . ($withOffset ? 'P' : ''));
        }
        
        /**
         * Converts the datetime value represented by this object into a string format following the ISO 8601 standard
         * in extended format.
         *
         * @return string The datetime string representation in the format "YYYY-MM-DDTHH:MM:SS±hh:mm".
         */
        public function toISO8601Expanded(): string {
            return $this->format('X-m-d\\TH:i:sP');
        }
        
        /**
         * Converts the datetime represented by this object into a string format suitable for SQL database storage.
         *
         * @return string The datetime string representation in the format "YYYY-MM-DD HH:MM:SS".
         */
        public function toSqlFormat(): string {
            return $this->format('Y-m-d H:i:s');
        }
        
        /**
         * Converts the date and time represented by this object into a string format in "YYYY-MM-DD HH:MM:SS" format.
         *
         * @return string The date and time string representation in "YYYY-MM-DD HH:MM:SS" format.
         */
        public function toString(): string {
            return $this->format('Y-m-d H:i:s');
        }
        
        /**
         * Converts the date and time represented by this object into a full date and time string format by using a
         * specified delimiter.
         *
         * @param string $delimiter (optional) The delimiter to use between the date and time components. Default is an
         *                          empty string.
         *
         * @return string The full date and time string representation in the format "YYYY-MM-DD HH:MM:SS" or
         *                "YYYYMMDDHHMMSS" if $delimiter is an empty string.
         */
        public function toFullDateTimeString(string $delimiter = ''): string {
            return implode($delimiter, explode('-', $this->format('Y-m-d-H-i-s')));
        }
        
        /**
         * Converts the datetime represented by this object into a string format that follows the W3C datetime format.
         * The format used is "Y-m-d\TH:i:sP".
         *
         * @return string The datetime string representation in the W3C datetime format.
         */
        public function toW3CFormat(): string {
            return $this->format("Y-m-d\\TH:i:sP");
        }
        
        /**
         * Converts the time represented by this object into a string format with optional seconds and a specified
         * delimiter.
         *
         * @param bool   $withSeconds (optional) Whether to include the seconds in the time string. Default is false.
         * @param string $delimiter   (optional) The delimiter to use between hours, minutes, and seconds. Default is
         *                            ":".
         *
         * @return string The time string representation in the format "HH:MM" or "HH:MM:SS" if $withSeconds is true.
         */
        public function toTimeString(bool $withSeconds = false, string $delimiter = ':'): string {
            return $this->format('H' . $delimiter . 'i' . ($withSeconds ? $delimiter . 's' : ''));
        }
        
        /**
         * Converts the time represented by this object into a compact string format.
         * The format is "YmdHis", which stands for Year, Month, Day, Hour, Minute, Second.
         *
         * @return string The time string representation in the format "YmdHis".
         */
        public function toSimpleFormat(): string {
            return $this->format('YmdHis');
        }
        
        /**
         * Returns the offset of the current DateTime object from UTC time in seconds.
         *
         * @return int The offset in seconds.
         */
        public function offsetInSeconds(): int {
            return (int)$this->format('Z');
        }
        
        /**
         * Returns the UTC offset of the date and time represented by this object.
         *
         * @param bool $withColon (optional) Whether to include the colon in the offset. Default is true.
         *
         * @return string The UTC offset representation in the format "+HH:MM" or "+HHMM" if $withColon is false.
         */
        public function getUTCOffset(bool $withColon = true): string {
            return $this->format($withColon ? 'P' : 'O');
        }
        
        /**
         * Retrieves the timestamp of the current datetime object.
         *
         * @param bool $withMicroseconds Whether to include microsecond precision in the timestamp. Default is false.
         *
         * @return float|int The timestamp as a float if $withMicroseconds is true, otherwise as an integer.
         */
        public function timestamp(bool $withMicroseconds = false): float|int {
            return ($withMicroseconds ? (float)$this->format('U.u') : $this->datetime->getTimestamp());
        }
        
        /**
         * Retrieves the microseconds value of the timestamp represented by this object.
         *
         * @return float|int The number of microseconds of the timestamp.
         */
        public function microseconds(): float|int {
            return $this->timestamp(true);
        }
        
        /**
         * Adds the specified amount of time to this object.
         *
         * @param int|null $seconds (optional) The number of seconds to add. Default is null.
         * @param int|null $minutes (optional) The number of minutes to add. Default is null.
         * @param int|null $hours   (optional) The number of hours to add. Default is null.
         * @param int|null $days    (optional) The number of days to add. Default is null.
         * @param int|null $months  (optional) The number of months to add. Default is null.
         * @param int|null $years   (optional) The number of years to add. Default is null.
         *
         * @return $this The modified object with the added time.
         */
        public function add(?int $seconds = null, ?int $minutes = null, ?int $hours = null, ?int $days = null, ?int $months = null, ?int $years = null): static {
            if ($seconds !== null) {
                $this->modifySecond(abs($seconds));
            }
            if ($minutes !== null) {
                $this->modifyMinute(abs($minutes));
            }
            if ($hours !== null) {
                $this->modifyHour(abs($hours));
            }
            if ($days !== null) {
                $this->modifyDay(abs($days));
            }
            if ($months !== null) {
                $this->modifyMonth(abs($months));
            }
            if ($years !== null) {
                $this->modifyYear(abs($years));
            }
            
            return $this;
        }
        
        /**
         * Subtracts a specified number of seconds, minutes, hours, days, months, and/or years from the current time
         * represented by this object.
         *
         * @param int|null $seconds (optional) The number of seconds to subtract. Default is null.
         * @param int|null $minutes (optional) The number of minutes to subtract. Default is null.
         * @param int|null $hours   (optional) The number of hours to subtract. Default is null.
         * @param int|null $days    (optional) The number of days to subtract. Default is null.
         * @param int|null $months  (optional) The number of months to subtract. Default is null.
         * @param int|null $years   (optional) The number of years to subtract. Default is null.
         *
         * @return static This object after the subtraction operation.
         */
        public function sub(?int $seconds = null, ?int $minutes = null, ?int $hours = null, ?int $days = null, ?int $months = null, ?int $years = null): static {
            if ($seconds !== null) {
                $this->modifySecond(abs($seconds) * -1);
            }
            if ($minutes !== null) {
                $this->modifyMinute(abs($minutes) * -1);
            }
            if ($hours !== null) {
                $this->modifyHour(abs($hours) * -1);
            }
            if ($days !== null) {
                $this->modifyDay(abs($days) * -1);
            }
            if ($months !== null) {
                $this->modifyMonth(abs($months) * -1);
            }
            if ($years !== null) {
                $this->modifyYear(abs($years) * -1);
            }
            
            return $this;
        }
        
        /**
         * Resets the specified components of the time represented by this object.
         *
         * @param int|null $seconds      (optional) The value to set for the seconds component. If true, the seconds
         *                               component will be set to 0. Default is null.
         * @param int|null $minutes      (optional) The value to set for the minutes component. If true, the minutes
         *                               component will be set to 0. Default is null.
         * @param int|null $hours        (optional) The value to set for the hours component. If true, the hours
         *                               component will be set to 0. Default is null.
         * @param int|null $days         (optional) The value to set for the days component. If true, the days component
         *                               will be set to 1. Default is null.
         * @param int|null $months       (optional) The value to set for the months component. If true, the months
         *                               component will be set to 1. Default is null.
         * @param int|null $years        (optional) The value to set for the years component. If true, the years
         *                               component will be set to 1. Default is null.
         *
         * @return static This object after resetting the specified components of the time.
         */
        public function set(?int $seconds = null, ?int $minutes = null, ?int $hours = null, ?int $days = null, ?int $months = null, ?int $years = null): static {
            if ($seconds !== null) {
                $this->datetime->setTime($this->getHour(), $this->getMinute(), $seconds);
            }
            if ($minutes !== null) {
                $this->datetime->setTime($this->getHour(), $minutes, $this->getSecond());
            }
            if ($hours !== null) {
                $this->datetime->setTime($hours, $this->getMinute(), $this->getSecond());
            }
            if ($days !== null) {
                $this->datetime->setDate($this->getYear(), $this->getMonth(), $days);
            }
            if ($months !== null) {
                $this->datetime->setDate($this->getYear(), $months, $this->getDay());
            }
            if ($years !== null) {
                $this->datetime->setDate($years, $this->getMonth(), $this->getDay());
            }
            
            return $this;
        }
        
        /**
         * @return void
         */
        public function random() {
        
        }
        
        /**
         * Applies current date and/or time to this object based on the given options.
         *
         * @param bool $seconds (optional) Whether to update the seconds value. Default is false.
         * @param bool $minutes (optional) Whether to update the minutes value. Default is false.
         * @param bool $hours   (optional) Whether to update the hours value. Default is false.
         * @param bool $days    (optional) Whether to update the days value. Default is false.
         * @param bool $months  (optional) Whether to update the months value. Default is false.
         * @param bool $years   (optional) Whether to update the years value. Default is false.
         *
         * @return static Returns this object with updated date and/or time values.
         * @throws \Exception
         */
        public function applyFromNow(bool $seconds = false, bool $minutes = false, bool $hours = false, bool $days = false, bool $months = false, bool $years = false): static {
            static::applyFromDateTime(static::now(), $seconds, $minutes, $hours, $days, $months, $years);
            
            return $this;
        }
        
        /**
         * Applies the specified date and time values from the given DateTime object to this DateTime object.
         *
         * @param self $datetime The DateTime object from which to apply the date and time values.
         * @param bool $seconds  (optional) Whether to apply the seconds value. Default is false.
         * @param bool $minutes  (optional) Whether to apply the minutes value. Default is false.
         * @param bool $hours    (optional) Whether to apply the hours value. Default is false.
         * @param bool $days     (optional) Whether to apply the days value. Default is false.
         * @param bool $months   (optional) Whether to apply the months value. Default is false.
         * @param bool $years    (optional) Whether to apply the years value. Default is false.
         *
         * @return static This DateTime object with the specified date and time values applied.
         */
        public function applyFromDateTime(self $datetime, bool $seconds = false, bool $minutes = false, bool $hours = false, bool $days = false, bool $months = false, bool $years = false): static {
            if ($seconds) {
                $this->setSecond($datetime->getSecond());
            }
            if ($minutes) {
                $this->setMinute($datetime->getMinute());
            }
            if ($hours) {
                $this->setHour($datetime->getHour());
            }
            if ($days) {
                $this->setDay($datetime->getDay());
            }
            if ($months) {
                $this->setMonth($datetime->getMonth());
            }
            if ($years) {
                $this->setYear($datetime->getYear());
            }
            
            return $this;
        }
        
        /**
         * Adds the specified amount of time to the current time represented by this object.
         *
         * @param int|null $hours   (optional) The number of hours to add. Default is 0.
         * @param int|null $minutes (optional) The number of minutes to add. Default is null.
         * @param int|null $seconds (optional) The number of seconds to add. Default is null.
         *
         * @return $this The modified instance of the object, with the added time.
         */
        public function addTime(?int $hours = null, ?int $minutes = null, ?int $seconds = null): static {
            $this->add($seconds, $minutes, $hours);
            
            return $this;
        }
        
        /**
         * Subtracts the given hours, minutes, and seconds from the time represented by this object.
         *
         * @param int|null $hours   (optional) The number of hours to subtract. Default is 0.
         * @param int|null $minutes (optional) The number of minutes to subtract. Default is null.
         * @param int|null $seconds (optional) The number of seconds to subtract. Default is null.
         *
         * @return self Returns a new instance of the class with the updated time after subtracting the specified
         *              hours, minutes, and seconds.
         */
        public function subTime(?int $hours = 0, ?int $minutes = null, ?int $seconds = null): static {
            $this->sub($seconds, $minutes, $hours);
            
            return $this;
        }
        
        /**
         * Adds a specified number of years, months, and days to the current date.
         *
         * @param int|null $years  (optional) The number of years to add. Default is null.
         * @param int|null $months (optional) The number of months to add. Default is null.
         * @param int|null $days   (optional) The number of days to add. Default is null.
         *
         * @return $this
         */
        public function addDate(?int $years = null, ?int $months = null, ?int $days = null): static {
            $this->add(null, null, null, $days, $months, $years);
            
            return $this;
        }
        
        /**
         * Subtracts a specified number of years, months, and/or days from the date represented by this object.
         *
         * @param int|null $years  (optional) The number of years to subtract from the date. Default is null.
         * @param int|null $months (optional) The number of months to subtract from the date. Default is null.
         * @param int|null $days   (optional) The number of days to subtract from the date. Default is null.
         *
         * @return static The updated object with the date subtracted by the specified number of years, months, and/or
         *                days.
         */
        public function subDate(?int $years = null, ?int $months = null, ?int $days = null): static {
            $this->sub(null, null, null, $days, $months, $years);
            
            return $this;
        }
        
        /**
         * Sets the date to the previous day.
         *
         * @return $this The modified instance of the class.
         */
        public function yesterday(): static {
            $this->modifyDay(-1);
            
            return $this;
        }
        
        /**
         * Sets the year of the current object to the specified year value.
         *
         * @param int $year The year value to set.
         *
         * @return static The updated object with the year set to the specified value.
         */
        public function setYear(int $year): static {
            return $this->setDate($year, null, null);
        }
        
        /**
         * Adds the specified number of years to the current date and time.
         *
         * @param int|null $years (optional) The number of years to add. Default is null.
         *
         * @return $this The modified DateTime object.
         */
        public function addYears(int $years = null): static {
            $this->add(null, null, null, null, null, $years);
            
            return $this;
        }
        
        /**
         * Subtract a specified number of years from the date and time represented by this object.
         *
         * @param int|null $years (optional) The number of years to subtract. Default is null.
         *
         * @return $this The current object after subtracting the specified number of years.
         */
        public function subYears(int $years = null): static {
            $this->sub(null, null, null, null, null, $years);
            
            return $this;
        }
        
        /**
         * Sets the month of the current date object.
         *
         * @param int $month The month value to set. Must be a valid integer.
         *
         * @return static A new instance of the current date object with the month set to the specified value.
         */
        public function setMonth(int $month): static {
            return $this->setDate(null, $month, null);
        }
        
        /**
         * Adds the specified number of months to this object.
         *
         * @param int|null $months (optional) The number of months to add. Positive value adds months in the future,
         *                         negative value adds months in the past. Default is null.
         *
         * @return static This object with the added months.
         */
        public function addMonths(int $months = null): static {
            $this->add(null, null, null, null, $months, null);
            
            return $this;
        }
        
        /**
         * Subtract a specified number of months from the current date and time represented by this object.
         *
         * @param int|null $months (optional) The number of months to subtract. Default is null.
         *                         If null, no months will be subtracted.
         *
         * @return static The updated DateTime object after subtracting the specified number of months.
         */
        public function subMonths(int $months = null): static {
            $this->sub(null, null, null, null, $months, null);
            
            return $this;
        }
        
        /**
         * Sets the day of the month for this object and returns a new instance.
         *
         * @param int $day The day of the month to set.
         *
         * @return static A new instance with the day set to the specified value.
         */
        public function setDay(int $day): static {
            return $this->setDate(null, null, $day);
        }
        
        /**
         * Adds the specified number of days to the current date and time.
         *
         * @param int|null $days (optional) The number of days to add. Default is null.
         *
         * @return static The modified DateTime object.
         */
        public function addDays(int $days = null): static {
            $this->add(null, null, null, $days, null, null);
            
            return $this;
        }
        
        /**
         * Subtracts a specified number of days from the current datetime object.
         *
         * @param int|null $days (optional) Number of days to subtract. Default is null.
         *
         * @return static Returns a new datetime object with the specified number of days subtracted.
         */
        public function subDays(int $days = null): static {
            $this->sub(null, null, null, $days, null, null);
            
            return $this;
        }
        
        /**
         * Sets the hour of the time represented by this object.
         *
         * @param int $hour The hour value to set.
         *
         * @return static A new instance of the object with the hour value set.
         */
        public function setHour(int $hour): static {
            return $this->setTime($hour, null, null);
        }
        
        /**
         * Adds the specified number of hours to the time represented by this object.
         *
         * @param int|null $hours (optional) The number of hours to add. Default is null.
         *
         * @return static The modified object with the added hours.
         */
        public function addHours(int $hours = null): static {
            $this->add(null, null, $hours, null, null, null);
            
            return $this;
        }
        
        /**
         * Subtracts a specified number of hours from the current DateTime object.
         *
         * @param int|null $hours (optional) The number of hours to subtract. Default is null.
         *
         * @return static Returns a new DateTime object with the specified number of hours subtracted.
         */
        public function subHours(int $hours = null): static {
            $this->sub(null, null, $hours, null, null, null);
            
            return $this;
        }
        
        /**
         * Sets the minute of the time represented by this object.
         *
         * @param int $minute The minute value to be set.
         *
         * @return static The modified object with the minute value set.
         */
        public function setMinute(int $minute): static {
            return $this->setTime(null, $minute, null);
        }
        
        /**
         * Adds the specified number of minutes to the current time.
         *
         * @param int|null $minutes (optional) The number of minutes to add. Default is null, which adds zero minutes.
         *
         * @return static Returns a new instance of the class with the added minutes.
         */
        public function addMinutes(int $minutes = null): static {
            $this->add(null, $minutes, null, null, null, null);
            
            return $this;
        }
        
        /**
         * Subtracts the specified number of minutes from the time represented by this object.
         *
         * @param int|null $minutes (optional) The number of minutes to subtract. Default is null.
         *
         * @return static This object with the specified number of minutes subtracted.
         */
        public function subMinutes(int $minutes = null): static {
            $this->sub(null, $minutes, null, null, null, null);
            
            return $this;
        }
        
        /**
         * Sets the second component of the time represented by this object.
         *
         * @param int $second The value to set as the second component of the time.
         *
         * @return static A new instance of the class with the updated second component.
         */
        public function setSecond(int $second): static {
            return $this->setTime(null, null, $second);
        }
        
        /**
         * Adds the specified number of seconds to the time represented by this object.
         *
         * @param int|null $seconds (optional) The number of seconds to add. Default is null.
         *
         * @return static The updated object with the added seconds.
         */
        public function addSeconds(int $seconds = null): static {
            $this->add($seconds, null, null, null, null, null);
            
            return $this;
        }
        
        /**
         * Subtracts the specified number of seconds from this object's time.
         *
         * @param int|null $seconds (optional) The number of seconds to subtract. Default is null.
         *
         * @return static A new instance of the same class with the time decremented by the specified number of seconds.
         */
        public function subSeconds(int $seconds = null): static {
            $this->sub($seconds, null, null, null, null, null);
            
            return $this;
        }
        
        /**
         * Sets the day of the week for this object.
         *
         * @param int|null $dayOfWeek The day of the week to set (1-7, where 1 is Monday and 7 is Sunday).
         *
         * @return $this The modified object with the updated day of the week.
         */
        public function setDayOfWeek(?int $dayOfWeek): static {
            if ($dayOfWeek !== null) {
                $this->datetime->setISODate($this->format('Y'), $this->format('W'), $dayOfWeek);
            }
            
            return $this;
        }
        
        /**
         * Set date to the next week from the current date.
         *
         * @return $this The date object representing the next week from the current date.
         */
        public function nextWeek(?int $dayOfWeek = null): static {
            return $this->modifyDay(7)->setDayOfWeek($dayOfWeek);
        }
        
        /**
         * Get the datetime of the previous week.
         *
         * @return $this The datetime object representing the previous week.
         */
        public function previousWeek(?int $dayOfWeek = null): static {
            return $this->modifyDay(-7)->setDayOfWeek($dayOfWeek);
        }
        
        /**
         * Gets the day of the week represented by this object.
         *
         * @return int The day of the week as an integer. The value will be between 1 and 7, where 1 represents Monday,
         *             2 represents Tuesday, and so on.
         */
        public function dayOfWeek(): int {
            return (int)$this->format('N');
        }
        
        /**
         * Checks if the day of the week represented by this object is equal to the provided day of the week.
         *
         * @param int|array $dayOfWeek The day of the week to compare against. (1 for Monday, ..., 7 for Sunday)
         *
         * @return bool True if the day of the week matches the provided dayOfWeek, false otherwise.
         */
        public function dayOfWeekIs(int|array $dayOfWeek): bool {
            return (is_array($dayOfWeek) ? in_array($this->dayOfWeek(), $dayOfWeek) : $this->dayOfWeek() === $dayOfWeek);
        }
        
        /**
         * Checks if the given date falls on a weekend (Saturday or Sunday).
         *
         * @return bool Returns true if the date falls on a weekend, false otherwise.
         */
        public function isWeekend(): bool {
            return $this->isSaturday() || $this->isSunday();
        }
        
        /**
         * Checks if the current date is a weekday.
         *
         * @return bool Returns true if the current date is a weekday, false if it is a weekend.
         */
        public function isWeekday(): bool {
            return !$this->isWeekend();
        }
        
        /**
         * Determines if the current date represented by this object is a Monday.
         *
         * @return bool Returns true if the current date is a Monday, else returns false.
         */
        public function isMonday(): bool {
            return $this->dayOfWeekIs(1);
        }
        
        /**
         * Checks if the date represented by this object falls on a Tuesday.
         *
         * @return bool Returns true if the date falls on a Tuesday, false otherwise.
         */
        public function isTuesday(): bool {
            return $this->dayOfWeekIs(2);
        }
        
        /**
         * Checks if the current date is a Wednesday.
         *
         * @return bool True if the current date is a Wednesday, false otherwise.
         */
        public function isWednesday(): bool {
            return $this->dayOfWeekIs(3);
        }
        
        /**
         * Checks if the current date is a Thursday.
         *
         * @return bool Returns true if the current date is a Thursday, otherwise returns false.
         */
        public function isThursday(): bool {
            return $this->dayOfWeekIs(4);
        }
        
        /**
         * Determines if the current date is a Friday.
         *
         * @return bool Returns true if the current date is a Friday, otherwise false.
         */
        public function isFriday(): bool {
            return $this->dayOfWeekIs(5);
        }
        
        /**
         * Checks if the date represented by this object is a Saturday.
         *
         * @return bool Returns true if the date is a Saturday, otherwise returns false.
         */
        public function isSaturday(): bool {
            return $this->dayOfWeekIs(6);
        }
        
        /**
         * Checks if the current date is a Sunday.
         *
         * @return bool True if the current date is a Sunday, false otherwise.
         */
        public function isSunday(): bool {
            return $this->dayOfWeekIs(7);
        }
        
        # --------------------------------------------
        
        # --------------------------------------------
        
        /**
         * Returns the date of the last Monday relative to the current date.
         *
         * @return $this The date of the last Monday.
         */
        public function lastMonday(): static {
            return $this->previousWeek(1);
        }
        
        /**
         * Sets the date to the Monday of the current week.
         *
         * @return $this
         */
        public function thisMonday(): static {
            return $this->setDayOfWeek(1);
        }
        
        /**
         * Returns the date of the upcoming Monday from the current date.
         *
         * @return $this The date object representing the next Monday.
         */
        public function nextMonday(): static {
            return $this->nextWeek(1);
        }
        
        /**
         * Returns the date object of the previous Tuesday relative to this object.
         *
         * @return $this The date object representing the previous Tuesday relative to this object.
         */
        public function lastTuesday(): static {
            return $this->previousWeek(2);
        }
        
        /**
         * Sets the date to the Tuesday of the current week.
         *
         * @return $this
         */
        public function thisTuesday(): static {
            return $this->setDayOfWeek(2);
        }
        
        /**
         * Returns the date of the following Tuesday from the date represented by this object.
         *
         * @return $this A new instance of the class representing the date of the next Tuesday.
         */
        public function nextTuesday(): static {
            return $this->nextWeek(2);
        }
        
        /**
         * Returns the date of the last Wednesday relative to this date or DateTime object.
         *
         * @return $this A new instance of the same class representing the date of the last Wednesday.
         */
        public function lastWednesday(): static {
            return $this->previousWeek(3);
        }
        
        /**
         * Sets the date to the Wednesday of the current week.
         *
         * @return $this
         */
        public function thisWednesday(): static {
            return $this->setDayOfWeek(3);
        }
        
        /**
         * Calculates the date of the next Wednesday from the current date.
         *
         * @return static A new instance of the class representing the date of the next Wednesday.
         */
        public function nextWednesday(): static {
            return $this->nextWeek(3);
        }
        
        /**
         * Returns the date representing the last Thursday relative to the current date.
         *
         * @return $this A new instance of the class representing the last Thursday date.
         */
        public function lastThursday(): static {
            return $this->previousWeek(4);
        }
        
        /**
         * Sets the date to the Thursday of the current week.
         *
         * @return $this
         */
        public function thisThursday(): static {
            return $this->setDayOfWeek(4);
        }
        
        /**
         * Get the date object for the next Thursday.
         *
         * @return $this A new date object representing the date of the next Thursday.
         */
        public function nextThursday(): static {
            return $this->nextWeek(4);
        }
        
        /**
         * Returns the date that represents the last Friday from the current date.
         *
         * @return $this The date object representing the last Friday.
         */
        public function lastFriday(): static {
            return $this->previousWeek(5);
        }
        
        /**
         * Sets the date to the Friday of the current week.
         *
         * @return $this
         */
        public function thisFriday(): static {
            return $this->setDayOfWeek(5);
        }
        
        /**
         * Calculates the date of the next Friday relative to the current date represented by this object.
         *
         * @return $this A new instance of the class representing the date of the next Friday.
         */
        public function nextFriday(): static {
            return $this->nextWeek(5);
        }
        
        /**
         * Retrieves the date of the last Saturday based on the current date.
         *
         * @return $this The date object representing the last Saturday.
         */
        public function lastSaturday(): static {
            return $this->previousWeek(6);
        }
        
        /**
         * Sets the date to the Saturday of the current week.
         *
         * @return $this
         */
        public function thisSaturday(): static {
            return $this->setDayOfWeek(6);
        }
        
        /**
         * Calculates the date of the next Saturday relative to the current date represented by this object.
         *
         * @return $this A new instance of the class representing the date of the next Saturday.
         */
        public function nextSaturday(): static {
            return $this->nextWeek(6);
        }
        
        /**
         * Returns the date object representing the last Sunday relative to the current date.
         *
         * @return $this The date object representing the last Sunday.
         */
        public function lastSunday(): static {
            return $this->previousWeek(7);
        }
        
        /**
         * Sets the date to the Sunday of the current week.
         *
         * @return $this
         */
        public function thisSunday(): static {
            return $this->setDayOfWeek(7);
        }
        
        /**
         * Returns the DateTime object representing the next Sunday from the current DateTime object.
         *
         * @return static The DateTime object representing the next Sunday.
         */
        public function nextSunday(): static {
            return $this->nextWeek(7);
        }
        
        /**
         * Sets the date to the last day of the month.
         *
         * @return $this
         */
        public function setToLastDayOfMonth(): static {
            return $this->modify('last day of this month');
        }
        
        /**
         * Sets the DateTime object from a specified format string and datetime string.
         *
         * @param string $format   The format string used to parse the datetime string.
         * @param string $datetime The datetime string to be parsed.
         *
         * @return static This DateTime object, for method chaining.
         */
        public function setFromFormat(string $format, string $datetime): static {
            if ($this->isImmutable()) {
                $this->datetime = \DateTimeImmutable::createFromFormat($format, $datetime);
            } else {
                $this->datetime = \DateTime::createFromFormat($format, $datetime);
            }
            
            return $this;
        }
        
        /**
         * Creates a new instance of the class based on a given format and datetime string.
         *
         * @param string $format   The format of the datetime string.
         * @param string $datetime The datetime string to parse.
         *
         * @return static The newly created instance of the class, with the date and time set based on the given format
         *                and datetime string.
         */
        public static function createFromFormat(string $format, string $datetime): static {
            return (new static())->setFromFormat($format, $datetime);
        }
        
        /**
         * Create a new instance of the class with the given datetime and timezone.
         *
         * @param mixed $datetime The datetime value to create the instance from.
         * @param mixed $timezone (optional) The timezone to be used. If not provided, the default system timezone will
         *                        be used.
         *
         * @return static An instance of the class with the specified datetime and timezone.
         * @throws \Exception
         */
        public static function create(mixed $datetime, mixed $timezone = null): static {
            return new static($datetime, $timezone);
        }
        
        /**
         * Creates a new immutable instance of the class using the given datetime and timezone.
         *
         * @param mixed $datetime The datetime value to create the instance from.
         * @param mixed $timezone The timezone to be applied to the instance.
         *
         * @return static A new instance of the class with the given datetime and timezone.
         * @throws \Exception
         */
        public static function createImmutable(mixed $datetime, mixed $timezone): static {
            return new static($datetime, $timezone, true);
        }
        
        /**
         * Creates an array of instances of the class using the given datetimes, timezone, and immutability setting.
         *
         * @param array $datetimes The array of datetimes to create instances from.
         * @param mixed $timezone  The timezone to use for the instances.
         * @param bool  $immutable (optional) Whether the instances should be immutable. Default is false.
         *
         * @return array|static[] An array of instances of the class created from the given datetimes using the
         *                        provided timezone and immutability setting.
         * @throws \Exception
         */
        public static function factory(array $datetimes, mixed $timezone, bool $immutable = false): array {
            $result = [];
            foreach ($datetimes as $datetime) {
                $result[] = new static($datetime, $timezone, $immutable);
            }
            
            return $result;
        }
        
        /**
         * Sets the timezone.
         *
         * The method sets the timezone based on the provided parameter. If the parameter is an instance of
         * DateTimeZone, it directly sets the timezone. If the parameter is a string, it creates a new DateTimeZone
         * object using the provided timezone string. If the parameter is null, it does not change the current
         * timezone. If none of the above conditions are met, it sets the timezone to the default timezone obtained
         * from date_default_timezone_get().
         *
         * @param \DateTimeZone|string|null $timezone The timezone to set. An instance of DateTimeZone or a string
         *                                            representing a valid timezone. If null, no change will be made.
         *
         * @return \DateTimeZone|string The timezone that has been set. If a DateTimeZone object is set, it will be
         *                              returned. If a string representing a valid timezone is set, the string will be
         *                              returned.
         * @throws \Exception
         */
        public function setTimezone(mixed $timezone = null): \DateTimeZone|string {
            try {
                $tz = match (true) {
                    $timezone instanceof \DateTimeZone => $timezone,
                    is_string($timezone) => new \DateTimeZone($timezone),
                    default => new \DateTimeZone(date_default_timezone_get()),
                };
            } catch (\Exception $e) {
                $tz = new \DateTimeZone(date_default_timezone_get());
            }
            $this->datetime->setTimezone($tz);
            
            return $this->datetime->getTimezone();
        }
        
        /**
         * @return \DateTimeZone|false
         */
        /**
         * @return \DateTimeZone|false
         */
        public function timezone() {
            return $this->datetime->getTimezone();
        }
        
        # -- Modifications
        
        /**
         * @param string $modifier
         *
         * @return $this
         */
        public function modify(string $modifier): static {
            $this->datetime->modify($modifier);
            
            return $this;
        }
        
        /**
         * Set the date value of the object.
         *
         * @param scalar $year  The year of the date value.
         * @param scalar $month The month of the date value.
         * @param scalar $day   The day of the date value.
         *
         * @return $this
         */
        public function setDate(mixed $year = null, mixed $month = null, mixed $day = null): static {
            $this->set(null, null, null, $day, $month, $year);
            
            return $this;
        }
        
        /**
         * Retrieves the date represented by this object into a string format, with a specified delimiter.
         *
         * @param string $delimiter (optional) The delimiter to use between year, month, and day. Default is "-".
         *
         * @return string The date string representation in the format "YYYY-MM-DD".
         */
        public function getDateString(string $delimiter = '-'): string {
            return $this->format("Y{$delimiter}m{$delimiter}d");
        }
        
        /**
         * Set the time of the object.
         *
         * @param mixed $hour   The hour value.
         * @param mixed $minute The minute value.
         * @param mixed $second The second value.
         *
         * @return static The updated object.
         */
        public function setTime(mixed $hour = null, mixed $minute = null, mixed $second = null): static {
            $this->set($second, $minute, $hour);
            
            return $this;
        }
        
        /**
         * Retrieves the number of days in the month represented by this object.
         *
         * @return int The number of days in the month.
         */
        public function daysInMonth(): int {
            return (int)$this->format('t');
        }
        
        /**
         * Checks if the current day is the last day of the month.
         *
         * @return bool Returns true if the current day is the last day of the month, otherwise false.
         */
        public function isLastDayOfMonth(): bool {
            return $this->getDay() === $this->daysInMonth();
        }
        
        /**
         * Retrieves the year of the current date and time object.
         *
         * @param int|null $mod The number of years to modify the date (positive or negative). Default is null.
         *
         * @return $this The year of the current date and time object.
         */
        public function modifyYear(?int $mod = null): static {
            $this->mod($mod, null, null, null, null, null, null);
            
            return $this;
            
        }
        
        /**
         * Retrieves the year from the current date and time.
         *
         * @return int The year represented as a four-digit number.
         */
        public function getYear(): int {
            return (int)$this->format('Y');
        }
        
        /**
         * Retrieves the month value of the current DateTime object or modifies it by the specified number of months.
         *
         * @param int|null $mod The number of months to modify the current month (positive or negative). Default is
         *                      null.
         *
         * @return $this The month value of the current DateTime object or the modified month value.
         */
        public function modifyMonth(?int $mod = null): static {
            $this->mod(null, $mod, null, null, null, null, null);
            
            return $this;
        }
        
        /**
         * Gets the month of the current DateTime object.
         *
         * @return int The month of the current DateTime object as an integer.
         */
        public function getMonth(): int {
            return (int)$this->format('n');
        }
        
        /**
         * Retrieves the day of the month from the DateTime object. If a value is provided, it modifies the DateTime
         * object by the specified number of days.
         *
         * @param int|null $mod The number of days to modify (positive or negative). Default is null.
         *
         * @return $this The day of the month as an integer.
         */
        public function modifyDay(?int $mod = null): static {
            $this->mod(null, null, null, $mod, null, null, null);
            
            return $this;
        }
        
        /**
         * Returns the day of the month represented by the current DateTime object.
         *
         * @return int The day of the month as an integer.
         */
        public function getDay(): int {
            return (int)$this->format('j');
        }
        
        /**
         * Modifies the hour of the current DateTime object by the specified amount.
         *
         * @param int|null $mod The number of hours to modify (positive or negative). Default is null.
         *
         * @return $this The modified hour as an integer.
         */
        public function modifyHour(?int $mod = null): static {
            $this->mod(null, null, null, null, $mod, null, null);
            
            return $this;
        }
        
        /**
         * Checks if the year of this object is equal to the given year.
         *
         * @param int|array $year The year to compare against the year of this object.
         *
         * @return bool True if the year of this object is equal to the given year, false otherwise.
         */
        public function yearIs(int|array $year): bool {
            return (is_array($year) ? in_array($this->getYear(), $year) : $this->getYear() === $year);
        }
        
        /**
         * Checks if the month of the date represented by this object is equal to the given month.
         *
         * @param int|array $month The month to compare with the month of the date.
         *
         * @return bool Returns true if the month of the date is equal to the given month.
         *              Otherwise, returns false.
         */
        public function monthIs(int|array $month): bool {
            return (is_array($month) ? in_array($this->getMonth(), $month) : $this->getMonth() === $month);
        }
        
        /**
         * Checks if the current day matches the given day.
         *
         * @param int|array $day The day to check against. Should be a number representing the day of the month.
         *
         * @return bool Returns true if the current day matches the given day, false otherwise.
         */
        public function dayIs(int|array $day): bool {
            return (is_array($day) ? in_array($this->getDay(), $day) : $this->getDay() === $day);
        }
        
        /**
         * Determines if the time represented by this object has the specified hour.
         *
         * @param int|array $hour The hour to compare against the time.
         *
         * @return bool True if the time has the specified hour, false otherwise.
         */
        public function hoursIs(int|array $hour): bool {
            return (is_array($hour) ? in_array($this->getHour(), $hour) : $this->getHour() === $hour);
        }
        
        /**
         * Checks if the minute of the time represented by this object is equal to the specified minute.
         *
         * @param int|array $minute The minute to compare against.
         *
         * @return bool Returns true if the minute of the time represented by this object is equal to the specified
         *              minute, otherwise returns false.
         */
        public function minuteIs(int|array $minute): bool {
            return (is_array($minute) ? in_array($this->getMinute(), $minute) : $this->getMinute() === $minute);
        }
        
        /**
         * Checks if the second of this object matches the given second.
         *
         * @param int|array $second The second to compare with.
         *
         * @return bool True if the second matches the given second, false otherwise.
         */
        public function secondIs(int|array $second): bool {
            return (is_array($second) ? in_array($this->getSecond(), $second) : $this->getSecond() === $second);
        }
        
        /**
         * Retrieves the hour component of the current DateTime object.
         *
         * @return int The hour component of the current DateTime object.
         */
        public function getHour(): int {
            return (int)$this->format('G');
        }
        
        /**
         * Modifies the minute of the current DateTime object by the specified amount.
         *
         * @param int|null $mod The number of minutes to modify (positive or negative). Default is null.
         *
         * @return $this The modified minute value.
         */
        public function modifyMinute(?int $mod = null): static {
            $this->mod(null, null, null, null, null, $mod, null);
            
            return $this;
        }
        
        /**
         * Retrieves the minute value from the current datetime object.
         *
         * @return int The minute value as an integer.
         */
        public function getMinute(): int {
            return (int)$this->format('i');
        }
        
        /**
         * Returns the second component of the current DateTime object.
         *
         * @param int|null $mod The number of seconds to modify (positive or negative). Default is null.
         *
         * @return $this The second component of the modified DateTime object.
         */
        public function modifySecond(?int $mod = null): static {
            $this->mod(null, null, null, null, null, null, $mod);
            
            return $this;
        }
        
        /**
         * Retrieves and returns the second value from the datetime object.
         *
         * @return int The second value as an integer.
         */
        public function getSecond(): int {
            return (int)$this->format('s');
        }
        
        /**
         * Modifies the date and time of the current object by the specified years, months, days, hours, minutes, and
         * seconds.
         *
         * @param int|null $years   The number of years to modify (positive or negative). Default is null.
         * @param int|null $months  The number of months to modify (positive or negative). Default is null.
         * @param int|null $days    The number of days to modify (positive or negative). Default is null.
         * @param int|null $hours   The number of hours to modify (positive or negative). Default is null.
         * @param int|null $minutes The number of minutes to modify (positive or negative). Default is null.
         * @param int|null $seconds The number of seconds to modify (positive or negative). Default is null.
         *
         * @return $this The modified DateTime object.
         */
        public function mod(?int $years = null, ?int $months = null, ?int $weeks = null, ?int $days = null, ?int $hours = null, ?int $minutes = null, ?int $seconds = null): static {
            if ($years !== null && $years !== 0) {
                $this->modify(($years < 0 ? '-' : '+') . $years . ' years');
            }
            if ($months !== null && $months !== 0) {
                $this->modify(($months < 0 ? '-' : '+') . $months . ' months');
            }
            if ($weeks !== null && $weeks !== 0) {
                $this->modify(($weeks < 0 ? '-' : '+') . $weeks . ' weeks');
            }
            if ($days !== null && $days !== 0) {
                $this->modify(($days < 0 ? '-' : '+') . $days . ' days');
            }
            if ($hours !== null && $hours !== 0) {
                $this->modify(($hours < 0 ? '-' : '+') . $hours . ' hours');
            }
            if ($minutes !== null && $minutes !== 0) {
                $this->modify(($minutes < 0 ? '-' : '+') . $minutes . ' minutes');
            }
            if ($seconds !== null && $seconds !== 0) {
                $this->modify(($seconds < 0 ? '-' : '+') . $seconds . ' seconds');
            }
            
            return $this;
        }
        
        /**
         * Reset the units of the DateTime object to the current date and time.
         *
         * @return $this Returns the current object for method chaining.
         */
        public function setToNow(): static {
            $this->setTimestamp(time());
            
            return $this;
        }
        
        /**
         * Resets the time represented by this object to midnight (00:00:00).
         *
         * @return $this The current object after resetting the time.
         */
        public function resetTime(): static {
            $this->setTime(0, 0, 0);
            
            return $this;
        }
        
        /**
         * Sets the time of the current object to midnight (00:00:00).
         *
         * @return DateTime The DateTime object with the time set to midnight.
         */
        public function midnight(): static {
            return $this->setTime(0, 0, 0);
        }
        
        /**
         * Sets the time to the start of the day (00:00:00).
         *
         * @return static A new instance of the object with the time set to the start of the day.
         */
        public function startTime(): static {
            return $this->setTime(0, 0, 0);
        }
        
        /**
         * Sets the time of this object to the end of the day (23:59:59).
         *
         * @return static A new instance of the object with the time set to the end of the day.
         */
        public function endTime(): static {
            return $this->setTime(23, 59, 59);
        }
        
        /**
         * Updates the current date to the next day and returns the start time of the updated date.
         *
         * @return static Returns the updated date with the start time.
         */
        public function toNextDay(): static {
            return $this->modifyDay(1)->startTime();
        }
        
        /**
         * Sets the date and time to the start of the current week.
         * The start of the week is considered to be Monday.
         *
         * @return static The updated instance of the current object with the date and time set to the start of the
         *                week.
         */
        public function toStartOfThisWeek(): static {
            return $this->thisMonday()->resetTime();
        }
        
        /**
         * Returns the start time of the next week from the current time represented by this object.
         *
         * @return static The instance of the class representing the start time of the next week.
         */
        public function toStartOfNextWeek(): static {
            return $this->nextMonday()->startTime();
        }
        
        /**
         * Returns the start time of the next week from the current time represented by this object.
         *
         * @return static The instance of the class representing the start time of the next week.
         */
        public function toStartOfNextMonth(): static {
            return $this->modifyMonth(1)->setDay(1)->startTime();
        }
        
        /**
         * Advances the date represented by this object to the start of the next year.
         *
         * @return static A new instance representing the start of the next year.
         */
        public function toStartOfNextYear(): static {
            return $this->modifyYear(1)->setMonth(1)->setDay(1)->startTime();
        }
        
        /**
         * Determines if the date represented by this object is the current date.
         *
         * @return bool Returns true if the date is the current date, false otherwise.
         * @throws \Exception
         */
        public function isToday(): bool {
            return $this->getDateString() === static::now($this->timezone())->getDateString();
        }
        
        /**
         * Checks if the date represented by this object is tomorrow.
         *
         * @return bool Returns true if the date is tomorrow, otherwise returns false.
         * @throws \Exception
         */
        public function isTomorrow(): bool {
            return $this->getDateString() === static::now()->addDays(1)->getDateString();
        }
        
        /**
         * @return bool
         * @throws \Exception
         */
        public function isYesterday(): bool {
            return $this->getDateString() === static::now()->subDays(1)->getDateString();
        }
        
        /**
         * Determines if the current time represented by this object is the current time.
         *
         * @return bool True if the current time is the same as the time represented by this object, false otherwise.
         * @throws \Exception
         */
        public function isNow(): bool {
            return $this->timestamp() === static::now($this->timestamp())->timestamp();
        }
        
        /**
         * Creates a copy of the current object.
         *
         * @return static A new instance of the current object with the same values as the current object.
         * @throws \Exception
         */
        public function copy(): static {
            return clone $this;
        }
        
        /**
         * Checks if the provided hour, minute, and/or second values match the respective values of the time
         * represented by this object. Returns true if all provided values match or if no values are provided. Returns
         * false otherwise.
         *
         * @param int|null $hour   (optional) The hour value to check. Default is null.
         * @param int|null $minute (optional) The minute value to check. Default is null.
         * @param int|null $second (optional) The second value to check. Default is null.
         *
         * @return bool True if all provided values match or if no values are provided. False otherwise.
         */
        public function timeIs(?int $hour = null, ?int $minute = null, ?int $second = null): bool {
            $result = [];
            if ($hour !== null) {
                $result[] = $this->hoursIs($hour);
            }
            if ($minute !== null) {
                $result[] = $this->minuteIs($minute);
            }
            if ($second !== null) {
                $result[] = $this->secondIs($second);
            }
            
            return ($result && !in_array(false, $result, true));
        }
        
        /**
         * Determines if the date represented by this object matches the specified year, month, and/or day.
         *
         * @param int|null $year  (optional) The year to compare. If not provided, the year will not be checked.
         * @param int|null $month (optional) The month to compare. If not provided, the month will not be checked.
         * @param int|null $day   (optional) The day to compare. If not provided, the day will not be checked.
         *
         * @return bool Returns true if the date matches the specified year, month, and/or day. Returns false otherwise.
         */
        public function dateIs(?int $year = null, ?int $month = null, ?int $day = null): bool {
            $result = [];
            if ($year !== null) {
                $result[] = $this->yearIs($year);
            }
            if ($month !== null) {
                $result[] = $this->monthIs($month);
            }
            if ($day !== null) {
                $result[] = $this->dayIs($day);
            }
            
            return ($result && !in_array(false, $result, true));
        }
        
        /**
         * Checks if the date and time represented by this object is in the past.
         *
         * @return bool Returns true if the date and time is in the past, false otherwise.
         */
        public function isPast(): bool {
            return $this->datetime < new \DateTime();
        }
        
        /**
         * Checks if the datetime represented by this object is in the future.
         *
         * @return bool true if the datetime is in the future, false otherwise.
         */
        public function isFuture(): bool {
            return $this->datetime > new \DateTime();
        }
        
        /**
         * Checks if the current datetime is expired.
         *
         * @return bool True if the current datetime is expired, false otherwise.
         */
        public function expired(): bool {
            return $this->isPast();
        }
        
        /**
         * Saves the current instance of the object with the specified name.
         *
         * @param string $name The name to associate with the saved instance.
         *
         * @return $this The current instance of the object.
         */
        public function save(string $name): static {
            $this->saves[$name] = clone $this->datetime;
            
            return $this;
        }
        
        /**
         * @param $key
         *
         * @return $this|\DateTime|\DateTimeImmutable|mixed
         */
        /**
         * @param $key
         *
         * @return $this|\DateTime|\DateTimeImmutable|mixed
         */
        public function saved($key) {
            return array_key_exists($key, $this->saves) ? $this->saves[$key] : $this->datetime;
        }
        
        /**
         * Restores the timestamp using a saved timestamp with the specified name.
         *
         * @param string $name The name of the saved timestamp to restore.
         *
         * @return static|DateTime The DateTime object with the restored timestamp.
         * @throws \Exception
         */
        public function restore(string $name): static {
            $this->datetime = $this->saved($name);
            
            return clone $this;
        }
        
        # --
        
        /**
         * Returns relative time in human-readable format
         *
         * The function calculates the difference between the given time and the current time
         * and returns the result in a human-readable format. By default, the function returns
         * all time units (e.g., "5 years 1 month 2 days 3 hours 10 minutes 40 seconds ago").
         * If the $only_biggest_unit parameter is set to true, it will return only the biggest
         * unit (e.g., "5 years ago").
         *
         * @param int  $time                  The timestamp representing the time to compare with the current time.
         * @param bool $only_biggest_unit     Whether to return only the biggest unit or all units.
         *                                    Default is false.
         *
         * @return string                     The relative time in human-readable format.
         */
        public static function relativeTime(int $time, bool $only_biggest_unit = false): string {
            $d[0] = [1, 'second'];
            $d[1] = [60, 'minute'];
            $d[2] = [3600, 'hour'];
            $d[3] = [86400, 'day'];
            $d[4] = [604800, 'week'];
            $d[5] = [2592000, 'month'];
            $d[6] = [31104000, 'year'];
            
            $w = [];
            
            $return      = [];
            $now         = time();
            $diff        = ($now - $time);
            $secondsLeft = $diff;
            
            for ($i = 6; $i > -1; $i--) {
                $w[$i]       = intval($secondsLeft / $d[$i][0]);
                $secondsLeft -= ($w[$i] * $d[$i][0]);
                if ($w[$i] != 0) {
                    $return[] = abs($w[$i]) . $d[$i][1] . (($w[$i] > 1) ? 's' : '');
                }
                
            }
            
            $return = ($only_biggest_unit ? [array_shift($return)] : $return);
            $str    = implode(' ', $return);
            
            return ($diff > 0 ? $str . ' ago' : 'in ' . $str);
        }
        
        /**
         * Convert seconds to a readable time format.
         *
         * @param int|float $inputSeconds The number of seconds.
         * @param string    $lang         The language for formatting the time (default is "en").
         *
         * @return string The formatted time.
         */
        public static function secondsToTime(int|float $inputSeconds, string $lang = 'en'): string {
            $secondsInAMinute = 60;
            $secondsInAnHour  = 60 * $secondsInAMinute;
            $secondsInADay    = 24 * $secondsInAnHour;
            
            // Extract days
            $days = floor($inputSeconds / $secondsInADay);
            
            // Extract hours
            $hourSeconds = bcmod($inputSeconds, $secondsInADay);
            $hours       = floor($hourSeconds / $secondsInAnHour);
            
            // Extract minutes
            $minuteSeconds = $hourSeconds % $secondsInAnHour;
            $minutes       = floor($minuteSeconds / $secondsInAMinute);
            
            // Extract the remaining seconds
            $remainingSeconds = $minuteSeconds % $secondsInAMinute;
            $seconds          = ceil($remainingSeconds);
            
            // Format and return
            $timeParts = [];
            $sections  = match ($lang) {
                'de' => [
                    'Tage'     => (int)$days,
                    'Stunden'  => (int)$hours,
                    'Minuten'  => (int)$minutes,
                    'Sekunden' => (int)$seconds,
                ],
                default => [
                    'day'    => (int)$days,
                    'hour'   => (int)$hours,
                    'minute' => (int)$minutes,
                    'second' => (int)$seconds,
                ],
            };
            
            foreach ($sections as $name => $value) {
                if ($value > 0) {
                    if ($lang == 'en') {
                        $timeParts[] = $value . ' ' . $name . ($value == 1 ? '' : 's');
                    } else {
                        $timeParts[] = $value . ' ' . ($value === 1 ? substr($name, 0, -1) : $name);
                    }
                }
            }
            if (count($timeParts) >= 2) {
                $last        = array_pop($timeParts);
                $timeParts[] = __('and');
                $timeParts[] = $last;
            }
            
            return implode(' ', $timeParts);
        }
        
    }
