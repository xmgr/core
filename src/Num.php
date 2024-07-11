<?php
    
    namespace Xmgr;
    
    /**
     * Class Num
     *
     * The Num class provides a set of methods for converting values, checking range, and calculating closest divisible
     * numbers.
     */
    class Num {
        
        /**
         * Converts a value to an integer.
         *
         * @param mixed $value The value to be converted.
         *
         * @return int Returns the converted integer value. If the input value is not scalar, returns 0.
         */
        public static function int(mixed $value): int {
            return is_scalar($value) ? (int)$value : 0;
        }
        
        /**
         * Converts a value to a float.
         *
         * @param mixed $value The value to be converted.
         *
         * @return float Returns the converted float value. If the input value is not scalar, returns 0.0.
         */
        public static function float(mixed $value): float {
            return is_scalar($value) ? (float)$value : 0.0;
        }
        
        /**
         * Converts a value to a float.
         *
         * @param mixed $value The value to be converted.
         *
         * @return int|float Returns the converted float value. If the input value is not scalar, returns 0.0.
         */
        public static function from(mixed $value): int|float {
            return (is_scalar($value) ? ($value + 0) : 0.0);
        }
        
        /**
         * Checks if a given value is within a specified range.
         *
         * @param mixed      $value The value to check if it is within the range.
         * @param mixed|null $min   The minimum value of the range (optional).
         * @param mixed      $max   The maximum value of the range (optional).
         *
         * @return bool Returns true if the value is within the range, false otherwise.
         */
        public static function isInRange(mixed $value, null|int|float $min = null, null|int|float $max = null): bool {
            $value = self::from($value);
            
            return (($min === null || $value >= $min) && ($max === null || $value <= $max));
        }
        
        /**
         * Calculates the closest number to the given number that is divisible by the given divisor.
         * -
         * The function first divides the given number by the given divisor and then rounds up the result to the
         * nearest
         * integer. The rounded up value is then multiplied by the divisor to obtain the closest number that is
         * divisible by the divisor.
         * -
         * Example:
         * Input.....: 13, 5
         * Output....: 15 (5 * ceil(13 / 5))
         * -
         *
         * @param float|int $number  The number for which the closest divisible number is to be calculated
         * @param float|int $divisor The divisor to check for divisibility
         *
         * @return int|float The closest number to the given number that is divisible by the given divisor
         */
        public static function closest_number_for_divisor(float|int $number, float|int $divisor, $if_equal_round_up = true): float|int {
            $up        = ceil($number / $divisor) * $divisor;
            $down      = floor($number / $divisor) * $divisor;
            $diff_up   = abs($number - $up);
            $diff_down = abs($number - $down);
            
            return ($diff_up === $diff_down ? ($if_equal_round_up ? $up : $down) : ($diff_up < $diff_down ? $up : $down));
        }
        
    }
