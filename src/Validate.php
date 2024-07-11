<?php
    
    namespace Xmgr;
    
    use Throwable;
    
    /**
     * Enthält Methoden um Werte auf Gültigkeit zu prüfen.
     * Eine Exception wird geworfen, wenn die Prüfung fehlschlägt.
     */
    class Validate {
        
        /**
         * Ensures that a given condition is true. If the condition is false,
         * it throws a BaseException with an optional error message.
         *
         * @param bool   $condition The condition to be checked.
         * @param string $message   Optional error message to be shown in case the condition is false.
         *
         * @throws BaseException|Throwable If the condition is false.
         */
        public static function ensure(bool $condition, string $message = ''): void {
            throwIfNot($condition, new BaseException($message));
        }
        
        /**
         * Fails if a given condition is true. If the condition is true,
         * it throws a BaseException with an optional error message.
         *
         * @param bool   $condition The condition to be checked.
         * @param string $message   Optional error message to be shown in case the condition is true.
         *
         * @throws BaseException|Throwable If the condition is true.
         */
        public static function failIf(bool $condition, string $message = ''): void {
            throwIf($condition, new BaseException($message));
        }
        
        /**
         * Validates if the given value is a valid ID. If the value is not an integer
         * greater than or equal to 1, it throws an InvalidIdException with an optional
         * error message.
         *
         * @param mixed  $value   The value to be validated as ID.
         * @param string $message Optional error message to be shown in case the value is not a valid ID.
         *
         * @throws BaseException|Throwable If the value is not a valid ID.
         */
        public static function id(mixed $value, string $message = ''): void {
            throwIfNot(is_int($value) && $value >= 1, new BaseException($message ?: 'Ungültige ID'));
        }
        
        /**
         * Records a given value or Record object.
         *
         * If the given record is an integer and non-zero, or if it is an instance of the Record class
         * and has a non-zero ID, this method will record the value. Otherwise, it throws a BaseException
         * with an optional error message.
         *
         * @param mixed  $record  The value or Record object to be recorded.
         * @param string $message Optional error message to be shown in case the record is not valid.
         *                        If no message is provided, a default message will be used.
         *
         * @throws BaseException|Throwable If the record is not valid.
         */
        public static function record(mixed $record, string $message = ''): void {
            throwIfNot((is_int($record) && $record) || ($record instanceof Record && $record->id()), new BaseException($message ?: 'Datensatz nicht existent oder ungültig', 500));
        }
        
        /**
         * Validates if the given value is a valid email address.
         * Throws a BaseException if the value is not a valid email.
         *
         * @param mixed  $value   The value to be validated.
         * @param string $message Optional error message to be shown when the value is not a valid email.
         *
         * @throws BaseException|Throwable If the value is not a valid email address.
         */
        public static function email(mixed $value, string $message = ''): void {
            \throwIfNot(Str::isEmail(Str::from($value)), new BaseException($message ?: 'Ungültiges Format für eine E-Mail Adresse'));
        }
        
    }
