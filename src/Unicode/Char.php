<?php
    
    namespace Xmgr\Unicode;
    
    use Xmgr\Str;
    
    /**
     * The Char class represents a single character. It provides various methods to retrieve information and perform
     * operations on the character.
     *
     * @package YourPackageName
     */
    class Char {
        
        protected string $char      = '';
        protected int    $codepoint = -1;
        
        /**
         * @param int|string $char
         */
        public function __construct(int|string $char) {
            if (is_string($char)) {
                $this->char      = $char;
                $this->codepoint = mb_ord($this->char);
            }
            if (is_int($char)) {
                $this->char      = mb_chr($char);
                $this->codepoint = $char;
            }
            
        }
        
        /**
         * Returns the codepoint value.
         *
         * @return false|int The codepoint value.
         */
        public function codepoint(): false|int {
            return $this->codepoint;
        }
        
        /**
         * Returns the codepoint as a string representation.
         *
         * @return string The codepoint as a string.
         */
        public function codepointString(): string {
            return Unicode::ucp($this->codepoint());
        }
        
        /**
         * Returns the number of bytes in the given string.
         *
         * @return int The number of bytes in the string.
         */
        public function byteCount(): int {
            return strlen($this->char);
        }
        
        /**
         * Determines if the given character is a multibyte character.
         *
         * @return bool True if the character is multibyte, false otherwise.
         */
        public function isMultibyte(): bool {
            return strlen($this->char) > 1;
        }
        
        /**
         * Checks if the current character is a null byte.
         *
         * @return bool True if the current character is a null byte, otherwise false.
         */
        public function isNullbyte(): bool {
            return $this->char === "\0";
        }
        
        /**
         * Determines if the codepoint is an alphabetical character.
         *
         * @return bool|null Returns true if the codepoint is an alphabetical character,
         *                  false if it is not an alphabetical character,
         *                  or null if the codepoint is outside the valid range.
         */
        public function isAlphabetical(): ?bool {
            return \IntlChar::isalpha($this->codepoint());
        }
        
        /**
         * Checks if the given codepoint represents an uppercase character.
         *
         * @return bool|null Returns true if the codepoint is uppercase,
         *                   false if it is not uppercase, and null if the codepoint is not valid.
         * @throws \Exception If an error occurs while checking the codepoint.
         */
        public function isUpper(): ?bool {
            return \IntlChar::isupper($this->codepoint());
        }
        
        /**
         * Checks if the given codepoint is in lower case.
         *
         * @return bool|null Returns `true` if the codepoint is in lower case, `false` if it is not in lower case, and
         *                   `null` if the codepoint is not a valid Unicode character.
         * @throws \Exception
         */
        public function isLower(): ?bool {
            return \IntlChar::islower($this->codepoint());
        }
        
        /**
         * Checks if the codepoint has a mirrored property.
         *
         * @return bool|null True if the codepoint is mirrored, false if it is not mirrored, null if the codepoint does
         *                   not have a mirrored property.
         */
        public function isMirrored(): ?bool {
            return \IntlChar::isMirrored($this->codepoint());
        }
        
        /**
         * Returns the mirrored character as a string.
         *
         * @return string The mirrored character as a string.
         */
        public function mirrorCharAsString() {
            return mb_chr(\IntlChar::charMirror($this->codepoint()));
        }
        
        /**
         * Returns the codepoint of the mirrored character.
         *
         * @return int The codepoint of the mirrored character.
         * @throws \Exception If the codepoint is invalid or an error occurred during the mirroring process.
         */
        public function mirrorCharAsCodepoint() {
            return \IntlChar::charMirror($this->codepoint());
        }
        
        /**
         * Checks if the codepoint is alphanumeric.
         *
         * @return bool|null Returns true if the codepoint is alphanumeric, false if it is not,
         *                   or null if the codepoint is not valid.
         */
        public function isAlphanumeric(): ?bool {
            return \IntlChar::isalnum($this->codepoint());
        }
        
        /**
         * Determines if the given codepoint is considered blank.
         *
         * @return bool|null True if the codepoint is considered blank, false if it is not blank, or null if an error
         *                   occurs.
         * @throws \Exception If an error occurs while checking if the codepoint is blank.
         */
        public function isBlank(): ?bool {
            return \IntlChar::isblank($this->codepoint());
        }
        
        /**
         * Returns whether the codepoint is a control character or not.
         *
         * @return bool|null True if the codepoint is a control character, false if it is not, null if the function
         *                   call fails.
         */
        public function isCntrl(): ?bool {
            return \IntlChar::iscntrl($this->codepoint());
        }
        
        /**
         * Determines if the codepoint is a digit.
         *
         * @return bool|null True if the codepoint is a digit, false if it is not a digit, null if the codepoint is not
         *                   valid.
         * @throws \Exception if an error occurs while calling the \IntlChar::isdigit() function.
         */
        public function isDigit(): ?bool {
            return \IntlChar::isdigit($this->codepoint());
        }
        
        /**
         * Checks if the given codepoint is a graph character.
         *
         * @return bool|null True if the codepoint is a graph character, false otherwise. Null if the codepoint is
         *                   invalid.
         */
        public function isGraph(): ?bool {
            return \IntlChar::isgraph($this->codepoint());
        }
        
        /**
         * Returns whether the codepoint is printable or not.
         *
         * @return bool|null True if the codepoint is printable, false if not printable, or null if the codepoint is
         *                   invalid.
         *
         * @throws \Exception If there is an error in determining the printability of the codepoint.
         */
        public function isPrintable(): ?bool {
            return \IntlChar::isprint($this->codepoint());
        }
        
        /**
         * Determines whether the codepoint is a punctuation character.
         *
         * @return bool|null True if the codepoint is a punctuation character, false otherwise. Null if the codepoint
         *                   is invalid.
         * @throws \Exception If an error occurs during the check.
         */
        public function isPunct(): ?bool {
            return \IntlChar::ispunct($this->codepoint());
        }
        
        /**
         * Determines if the given codepoint is a whitespace character.
         *
         * @return bool|null True if the codepoint is a whitespace character, null if the codepoint is not a valid
         *                   character.
         */
        public function isSpace(): ?bool {
            return \IntlChar::isspace($this->codepoint());
        }
        
        /**
         * Checks whether the character is whitespace.
         *
         * @return bool|int Returns false if the character is not whitespace,
         *                 and returns 1 if the character is whitespace.
         */
        public function isWhitespace(): false|int {
            return preg_match('/^\s$/u', $this->char);
        }
        
        /**
         * Checks if the character is a linebreak.
         *
         * @return bool Returns true if the character is a linebreak, false otherwise.
         */
        public function isLinebreak(): bool {
            return $this->char === "\r" || $this->char === "\n";
        }
        
        /**
         * Checks if the given codepoint matches the codepoint of the object.
         *
         * @param int $cp The codepoint to be checked.
         *
         * @return bool Returns true if the codepoint matches, false otherwise.
         */
        public function codepointIs(int $cp): bool {
            return $this->codepoint() === $cp;
        }
        
        /**
         * Checks if the given character is equal to the instance character.
         *
         * @param string $char The character to compare.
         *
         * @return bool True if the given character is equal to the instance character, false otherwise.
         */
        public function charIs(string $char): bool {
            return $this->char === $char;
        }
        
        /**
         * Returns the previous codepoint.
         *
         * @return static The previous codepoint.
         */
        public function previous(): static {
            return new static(max($this->codepoint() - 1, 0));
        }
        
        /**
         * Returns the next codepoint.
         *
         * @return static The next codepoint.
         */
        public function next(): static {
            return new static($this->codepoint() + 1);
        }
        
        /**
         * Converts a codepoint to its corresponding bytes representation.
         *
         * @param scalar $cp The codepoint to convert.
         *
         * @return string The bytes representation of the codepoint.
         */
        public function cp2bytes(float|bool|int|string $cp): string {
            $cp = strtolower($cp);
            if (Str::startsWithOneOf($cp, ['u+', '0x'])) {
                $cp = substr($cp, 2);
            }
            if (ctype_xdigit($cp)) {
                return pack('N', hexdec($cp));
            }
            
            return '';
        }
        
        /**
         * Retrieve the plane of a given codepoint.
         *
         * This method returns the plane of the codepoint starting from the current instance.
         *
         * @return Plane  The plane of the codepoint.
         */
        public function plane(): Plane {
            return Plane::byCodepoint($this->codepoint());
        }
        
        /**
         * Returns the block of the given codepoint.
         *
         * @return Block The block corresponding to the codepoint.
         * @throws \Exception
         */
        public function block(): Block {
            return Block::byCodepoint($this->codepoint());
        }
        
    }
