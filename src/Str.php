<?php
    
    namespace Xmgr;
    
    /**
     * Str class represents a utility class for string manipulation.
     *
     * @package     YourPackageName
     */
    class Str {
        
        public const string TRIM_DEFAULT = " \n\r\t\v\0";
        
        /**
         * Returns the length of the longest string in an array of strings.
         * ----------------------------------------------------------------
         * Usage:
         * longest_string(["abc", "abcd", "abcde"]);                  // 5
         * longest_string(["This", "is", "a", "test"]);               // 4
         *
         * @param array $strings An array of strings to find the longest string from.
         *
         * @return int The length of the longest string in the array.
         */
        public static function longest(array $strings) {
            $max = 0;
            foreach ($strings as $string) {
                $max = max($max, strlen($string));
            }
            
            return $max;
        }
        
        /**
         * Converts the given value to a string and removes leading/trailing whitespace.
         * ----------------------------------------------------------------
         * Usage:
         * from(" example ");              // "example"
         * from(123);                      // "123"
         * from(true);                     // "1"
         *
         * @param mixed  $value The value to be converted to string and trimmed.
         * @param string $trim  The characters to trim from the value. Default is self::TRIM_DEFAULT.
         *
         * @return string The converted string with leading/trailing whitespace removed.
         */
        public static function from(mixed $value, string $trim = self::TRIM_DEFAULT): string {
            return trim((is_scalar($value) ? (string)$value : ''), $trim);
        }
        
        /**
         * Only keeps alphanumeric characters in the string.
         * What this function does:
         * 1) Removes all characters that are not alphanumeric characters from the string.
         * 2) Optionally, it can keep additional characters specified in the $whitelist parameter.
         * 3) Optionally, it can keep only ASCII characters if $keep_ascii parameter is set to true.
         * 4) The resulting string will contain only alphanumeric characters and characters specified in $whitelist (if
         * provided).
         * -
         * The resulting string can be, for example, used for data validation or sanitization purposes.
         * -
         * Example:
         * Input.....: "Hello World!123"
         * Output....: "HelloWorld123"
         *
         * @param string       $string     Input string
         * @param string|array $whitelist  Additional characters you want to keep (default: "")
         * @param bool         $keep_ascii Whether to keep only ASCII characters (default: false)
         *
         * @return string
         */
        public static function str_keep_alnum(string $string, string|array $whitelist = '', bool $keep_ascii = false): string {
            return str_keep($string, $whitelist, '', $keep_ascii, true, true);
        }
        
        /**
         * Checks if a given value is a valid email address.
         * ----------------------------------------------------------------
         * Usage:
         * isEmail("john@example.com");      // true
         * isEmail("notanemailaddress");     // false
         *
         * @param string $value The value to be checked.
         *
         * @return bool True if the value is a valid email address, false otherwise.
         */
        public static function isEmail(string $value): bool {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        /**
         * Trims whitespace from the beginning and end of every line in a string using regular expressions.
         *
         * @param string $string The string to be trimmed
         *
         * @return string The trimmed string
         */
        public static function trimLines(string $string) {
            return trim(preg_replace('/^\s+|\s+?$/uim', '', $string));
        }
        
        /**
         * Check if a string contains another string using multi-byte safe functions.
         *
         * @param string $haystack The string to search in.
         * @param string $needle   The string to search for.
         *
         * @return bool True if the needle is found in the haystack, false otherwise.
         */
        public static function mb_str_contains(string $haystack, string $needle): bool {
            return !(mb_strlen($needle) === 0) && mb_strpos($haystack, $needle) !== false;
        }
        
        /**
         * Returns the matched group from a regular expression pattern in a string.
         * If the pattern matches and the specified group exists, returns the matched group.
         * If the pattern does not match or the specified group does not exist, returns the default value.
         *
         * @param string     $string  The input string to search in
         * @param string     $pattern The regular expression pattern to match
         * @param int|string $group   The index of the matched group to return (defaults to 1)
         * @param mixed      $default The default value to return if the pattern does not match or the specified group
         *                            does not exist (defaults to an empty string)
         *
         * @return mixed Returns the matched group if it exists, otherwise returns the default value
         */
        public static function preg_first(string $string, string $pattern, int|string $group = 1, mixed $default = '') {
            if (preg_match($pattern, $string, $matches)) {
                if (isset($matches[$group])) {
                    $value = $matches[$group];
                    
                    return (is_array($value) ? $value[0] : $value);
                }
            }
            
            return $default;
        }
        
        /**
         * @param          $string
         * @param          $offset
         * @param \Closure $callback
         *
         * @return void
         */
        /**
         * @param          $string
         * @param          $offset
         * @param \Closure $callback
         *
         * @return void
         */
        public static function str_move_offset($string, &$offset, \Closure $callback) {
            while ($offset < mb_strlen($string)) {
                $result = $callback($string, $offset);
                if ($result === true) {
                    $offset++;
                }
                if ($result === false) {
                    return;
                }
            }
        }
        
        /**
         * Converts a given text in markdown format to HTML.
         *
         * This function parses the markdown text and converts it to HTML representation.
         * It supports various markdown syntaxes including headings, blockquotes, lists, bold, italic, underline,
         * strikethrough, inline code, block code, and marked text.
         *
         * @param string $text The input markdown text to be converted to HTML.
         *
         * @return string The HTML representation of the input markdown text.
         */
        public static function markdown(string $text): string {
            $output = '';
            # Zeilenumbrüche vereinheitlichen
            $text       = str_replace(["\r\n", "\n"], "\n", trim($text));
            $iterations = 0;
            try {
                $len = mb_strlen($text);
                
                # Legt fest, in welcher Formatierung man sich momentan befindet
                $bold        = false;
                $italic      = false;
                $strike      = false;
                $underline   = false;
                $cite        = false;
                $inline_code = false;
                $block_code  = false;
                $list        = false;
                $heading     = 0;
                $break       = 0;
                $cut         = false;
                $mark        = false;
                
                $i = 0;
                while ($i < $len) {
                    $iterations++;
                    # Zusätzlicher Mechanismus um Endlosschleife zu verhindern (falls $i irgendwo nicht
                    # korrekt weitergezählt wird).
                    if ($iterations > $len) {
                        break;
                    }
                    
                    # Falls es das erste Zeichen ist
                    $first = $i === 0;
                    # Falls es das letzte Zeichen ist
                    $last = (($i + 1) === $len);
                    
                    # Aktuelles, vorheriges und nächstes Zeichen sowie der verbleibende Rest des Strings
                    $char      = mb_substr($text, $i, 1);
                    $prev_char = mb_substr($text, $i - 1, 1);
                    $next_char = mb_substr($text, $i + 1, 1);
                    $rest      = mb_substr($text, $i);
                    
                    # Zeilenumbruch-Zähler zurücksetzen, wenn das aktuelle Zeichen kein Zeilenumbruch ist.
                    if ($char !== "\n") {
                        $break = 0;
                    }
                    
                    # Überschrift-Tag validieren (h1-h6, denn h7 gibt's schließlich nicht)
                    $heading = min($heading, 6);
                    
                    switch (true) {
                        # String-Literal verwenden (Escaping) - das muss an erster Stelle sein.
                        # Damit kann man ggf. die Markdown-Formatierung deaktiveren, das heißt dass
                        # dann z.B. "\*\*Text\*\*" eben nicht als fetter Text geparsed wird.
                        case $char === "\\":
                            $output .= $next_char;
                            $i      += 2;
                            break;
                        
                        # Code-Block
                        # Sobald ein Codeblock mit "```" gestartet wurde, wird ab dann auch
                        # immer dieser Case ausgeführt (außer wenn Escaping erkannt wird,
                        # dann natürlich der Case darüber).
                        # ----------------------------------------------------------------
                        # ```
                        # echo "Hello World!";
                        # ```
                        case (str_starts_with($rest, '```') || $block_code):
                            if (str_starts_with($rest, '```')) {
                                $block_code = !$block_code;
                                $output     .= ($block_code ? '<pre>' : '</pre>');
                                $i          += 3;
                            } else {
                                $output .= $char;
                                $i++;
                            }
                            break;
                        
                        # Inline-Code
                        # Sobald Inline-Code mit "`" eingeleitet wird, wird ab dann auch
                        # immer dieser Case ausgeführt (außer wenn Escaping zuvor erkannt
                        # wurde, s. erster Case).
                        # ----------------------------------------------------------------
                        # Beispiel mit `inline-code`
                        case ($char === '`' || $inline_code):
                            if ($char === '`') {
                                $inline_code = !$inline_code;
                                $output      .= ($inline_code ? '<code>' : '</code>');
                            } else {
                                $output .= $char;
                            }
                            $i += 1;
                            break;
                        
                        # Blockzitat beginnt
                        # (Endet automatisch mit dem nächsten Zeilenumbruch auf den kein ">" folgt)
                        # ----------------------------------------------------------------
                        # > Das ist ein
                        # > Blockzitat über
                        # > mehrere Zeilen
                        case (!$cite && ($first || $prev_char === "\n") && str_starts_with($rest, '>')):
                            $i++;
                            $cite   = true;
                            $output .= '<blockquote>';
                            break;
                        
                        # Die beginnenden ">" im Blockzitat nicht anzeigen
                        case ($cite && $prev_char === "\n" && $char === '>'):
                            $i++;
                            break;
                        
                        # Text löschen
                        # Für den Fall dass man im Markdown source Text haben will, der in der fertig
                        # geparsten HTML-Version gar nicht zu sehen sein soll - kann ja sein
                        # dass es dafür einen usecase gibt.
                        # ----------------------------------------------------------------
                        # Foo /* das alles wird nicht zu sehen sein, nur das "Bar" am Ende */ Bar
                        case ($cut || str_starts_with($rest, '/*')):
                            if ($cut) {
                                $i++;
                                if (str_starts_with($rest, '*/')) {
                                    $cut = false;
                                    $i   += 2;
                                }
                            } else {
                                $cut = true;
                                $i   += 2;
                            }
                            break;
                        
                        # Markierter text (ein <mark> tag wird hinzugefügt)
                        # ----------------------------------------------------------------
                        # Dies ist ||markierter|| Text. Ein Browser styled das üblicherweise
                        # so dass der Text mit gelber Hintergrundfarbe hervorgehoben wird.
                        case (str_starts_with($rest, '||')):
                            $mark   = !$mark;
                            $i      += 2;
                            $output .= ($mark ? '<mark>' : '</mark>');
                            break;
                        
                        # Überschriften h1-h6
                        # Heading startet
                        # ----------------------------------------------------------------
                        # # Überschrift 1
                        # ## Überschrift 2
                        # ### Überschrift 3 etc ...
                        case ($heading && $char === '#'):
                        case (!$heading && $char === '#' && ($prev_char === "\n" || $first)):
                            $i++;
                            $heading++;
                            break;
                        
                        # Text einer Überschrift beginnt (also das nach dem letzten "#" und einem Leerzeichen)
                        case ($heading && $prev_char === '#' && $char === ' '):
                            $output .= "<h$heading>";
                            $i++;
                            break;
                        
                        # Überschrift endet wenn ein Zeilenumbruch erkannt wird
                        case ($heading && ($char === "\n" || $last)):
                            $i++;
                            $output  .= "</h$heading>";
                            $heading = 0;
                            break;
                        
                        # Zeilenumbruch
                        case mb_substr($text, $i, 1) === "\n":
                            # Wenn ein Zeilenumbruch kommt, schließen wir mit zuvor geöffneten Formatierungen ab
                            if ($bold) {
                                $bold   = false;
                                $output .= '</strong>';
                                $break++;
                            }
                            if ($italic) {
                                $italic = false;
                                $output .= '</em>';
                                $break++;
                            }
                            if ($strike) {
                                $strike = false;
                                $output .= '</s>';
                                $break++;
                            }
                            if ($mark) {
                                $mark   = false;
                                $output .= '</mark>';
                                $break++;
                            }
                            if ($underline) {
                                $underline = false;
                                $output    .= '</u>';
                                $break++;
                            }
                            
                            # Laufendes Blockzitat schließen, wenn nach diesem Zeilenumbruch kein ">" mehr kommt
                            if ($cite) {
                                $output .= '<br>';
                                if ($next_char !== '>') {
                                    $cite   = false;
                                    $output .= '</blockquote>';
                                }
                            }
                            
                            # Liste
                            if ($list && mb_substr($text, $i + 1, 2) !== '- ') {
                                $list   = false;
                                $output .= '</li></ul>';
                                $i++;
                                break;
                            }
                            if (mb_substr($text, $i + 1, 2) === '- ') {
                                $i      += 2;
                                $output .= ($list ? '<li>' : '<ul><li>');
                                $list   = true;
                                break;
                            }
                            
                            # Linie
                            if (mb_substr($text, $i + 1, 3) === '---' || mb_substr($text, $i + 1, 3) === '___') {
                                $i++;
                                $i      += 3;
                                $output .= '<hr>';
                                $break  = 0;
                                break;
                            }
                            
                            if ($break != 0) {
                                $output .= '<br>';
                            }
                            $break++;
                            $i++;
                            break;
                        
                        # Hyperlink
                        # ----------------------------------------------------------------
                        # [Klick hier](https://example.com)
                        # Wird zu:
                        # <a href='https://example.com'>Klick hier</a>
                        # @todo check ob man den text fett darstellen kann
                        case (str_starts_with($rest, '[')):
                            $i++;
                            $link_url  = '';
                            $link_text = mb_substr($text, $i, mb_strpos($text, ']', $i) - $i);
                            $i         += mb_strlen($link_text) + 1;
                            if (mb_substr($text, $i, 1) === '(') {
                                $i++;
                                $link_url = mb_substr($text, $i, mb_strpos($text, ')', $i) - $i);
                                $i        += mb_strlen($link_url) + 1;
                            }
                            if ($link_text && $link_url) {
                                $output .= "<a href='$link_url'>$link_text</a>";
                                break;
                            }
                            $i++;
                            $output .= mb_substr($text, $i, 1);
                            break;
                        
                        # Fetter text
                        # **Foo**       -> <strong>Foo</strong>
                        case str_starts_with($rest, '**'):
                            $bold   = !$bold;
                            $output .= ($bold ? '<strong>' : '</strong>');
                            $i      += 2;
                            break;
                        
                        # Kursiver text
                        # __Foo__       -> <em>Foo</em>
                        case str_starts_with($rest, '__'):
                            $italic = !$italic;
                            $output .= ($italic ? '<em>' : '</em>');
                            $i      += 2;
                            break;
                        
                        # Durchgestrichener text
                        # ~~Foo~~       -> <s>Foo</s>
                        case str_starts_with($rest, '~~'):
                            $strike = !$strike;
                            $output .= ($strike ? '<s>' : '</s>');
                            $i      += 2;
                            break;
                        
                        # Unterstrichener Text
                        # ==Foo== -> <u>Foo</u>
                        case str_starts_with($rest, '=='):
                            $underline = !$underline;
                            $output    .= ($underline ? '<u>' : '</u>');
                            $i         += 2;
                            break;
                        
                        # Standardmäßig einfach das aktuelle Zeichen an den output dran hängen
                        default:
                            $output .= $char;
                            $i++;
                            break;
                    }
                }
                
                # Am Ende etwaige offene Tags sauber schließen
                $output .= ($bold ? '</strong>' : '');
                $output .= ($italic ? '</em>' : '');
                $output .= ($strike ? '</s>' : '');
                $output .= ($underline ? '</u>' : '');
                $output .= ($cite ? '</blockquote>' : '');
                $output .= ($inline_code ? '</code>' : '');
                $output .= ($block_code ? '</pre>' : '');
                $output .= ($mark ? '</mark>' : '');
                $output .= ($heading ? "</h$heading>" : '');
                
            } catch (\Exception $e) {
                /* Irgendwas ist mit dem Offset schiefgelaufen (index out of bound) */
            }
            
            return "<div class='markdown-container'>\n" . $output . "\n</div>";
        }
        
        /**
         * Replaces specified characters in a given string with a specified replacement.
         *
         * This function replaces all occurrences of characters specified in the $chars parameter with the $replace
         * parameter in the given $string. If the $chars parameter is an array, it will be used directly in the
         * str_replace function. If the $chars parameter is a string, it will be split into an array using the
         * mb_str_split function before being passed to str_replace.
         *
         * Example 1:
         * Input.....: $string = "Hello world!";
         *             $chars = ["l", "o"];
         *             $replace = "-";
         * Output....: "He--- w-r-d!"
         *
         * @param string       $string  Input string
         * @param array|string $chars   Characters to replace
         * @param string       $replace Replacement value
         *
         * @return string The resulting string after replacing the specified characters with the given replacement
         */
        public static function replaceCharacters(string $string, array|string $chars, string $replace): string {
            return str_replace(is_array($chars) ? $chars : mb_str_split($chars), $replace, $string);
        }
        
        /**
         * Returns the substring of a given string before the last occurrence of a specified search string.
         * -----------------------------------------------------------------------------------------------
         * Usage:
         * beforeLast("Hello World", " ");                           // "Hello"
         * beforeLast("Lorem ipsum dolor sit amet", " ", "Unknown"); // "Lorem ipsum dolor sit"
         *
         * @param string       $string  The input string.
         * @param string       $search  The search string to find the last occurrence before.
         * @param mixed|string $default (optional) The default value to return if the search string is not found.
         *                              Defaults to an empty string.
         *
         * @return string The substring of the input string before the last occurrence of the search string. If the
         *                search string is not found, the default value is returned.
         */
        public static function beforeLast(string $string, string $search, mixed $default = '') {
            $pos = mb_strrpos($string, $search);
            if ($pos !== false) {
                return mb_substr($string, 0, $pos);
            }
            
            return $default;
        }
        
        /**
         * Returns the portion of a string before the first occurrence of a specified substring.
         * ---------------------------------------------------------------------------------------------
         * Usage:
         * before("hello world", "world");            // "hello "
         * before("hello world", "foo");              // ""
         * before("hello world", "world", "default"); // "hello "
         *
         * @param string       $string  The string to search within.
         * @param string       $search  The substring to search for.
         * @param mixed|string $default (optional) The default value to return if the substring is not found. Default
         *                              is an empty string.
         *
         * @return mixed The portion of the string before the first occurrence of the search substring.
         * If the substring is not found, the default value is returned.
         */
        public static function before(string $string, string $search, mixed $default = ''): mixed {
            $pos = mb_strpos($string, $search);
            if ($pos !== false) {
                return mb_substr($string, 0, $pos);
            }
            
            return $default;
        }
        
        /**
         * Returns the substring of a given string after the first occurrence of a specified search string.
         * ----------------------------------------------------------------
         * Usage:
         * after("Hello World", "o");                               // " World"
         * after("apple,banana,cherry", ",");                       // "banana,cherry"
         * after("This is a test", "is");                           // " is a test"
         * after("Hello World", "z");                               // ''
         *
         * @param string $string  The input string to search within.
         * @param string $search  The string to search for in the input string.
         * @param mixed  $default The value to return if the search string is not found.
         *
         * @return string|mixed The substring of the input string after the first occurrence of the search string,
         *                      or the default value if the search string is not found.
         */
        public static function after(string $string, string $search, mixed $default = ''): mixed {
            $pos = mb_strpos($string, $search);
            if ($pos !== false) {
                return mb_substr($string, $pos + 1);
            }
            
            return $default;
        }
        
        /**
         * Returns the portion of a string that occurs after the last occurrence of a specified substring.
         * ----------------------------------------------------------------
         * Usage:
         * afterLast("Hello World", "o");                // "rld"
         * afterLast("abcde", "c");                      // "de"
         * afterLast("This is a test", "is");            // "t"
         *
         * @param string $string  The input string to search.
         * @param string $search  The substring to search for in the input string.
         * @param mixed  $default (optional) The default value to return if the substring is not found. Defaults to an
         *                        empty string ('').
         *
         * @return string|mixed The portion of the input string that occurs after the last occurrence of the substring.
         *                      If the substring is not found, the default value is returned.
         */
        public static function afterLast(string $string, string $search, mixed $default = '') {
            $pos = mb_strrpos($string, $search);
            if ($pos !== false) {
                return mb_substr($string, $pos + 1);
            }
            
            return $default;
        }
        
        /**
         * Cleans up hexadecimal input by removing whitespace and non-hexadecimal characters.
         * Optionally, autofixes the input by removing non-hexadecimal characters and appending '0' if necessary.
         *
         * @param string $input   The input string to be cleaned up.
         * @param bool   $autofix Determines whether to autofix the input. Defaults to false.
         *
         * @return array|string|null The cleaned up input string as an array if $autofix is true and the input is valid
         *                           hexadecimal, otherwise, the cleaned up input string as a string if $autofix is
         *                           false and the input is valid hexadecimal, or null if the input is empty or
         *                           consists only of non-hexadecimal characters.
         */
        public static function hex_cleanup(string $input, bool $autofix = false): array|string|null {
            $input = str_replace([' ', "\n", "\r", "\t", "\0", "\v"], '', trim($input));
            $input = preg_replace('/^0x/ui', '', $input);
            $input = preg_replace('/(\\\\x|\\\\u)([a-f0-9][a-f0-9])/ui', '$2', $input);
            
            if ($autofix) {
                $input = preg_replace('/[^a-f0-9]/ui', '', $input);
                if (strlen($input) % 2 !== 0) {
                    $input = $input . '0';
                }
            }
            
            return $input;
        }
        
        /**
         * Returns the nth part of an exploded string
         *
         * @param string $string    Input string
         * @param string $separator Separator for explode
         * @param int    $index     Numeric index. 0 means first part, -1 means last part etc
         * @param mixed  $default
         *
         * @return mixed|null
         */
        public static function str_part(string $string, string $separator, int $index = 0, mixed $default = ''): mixed {
            return array_index(explode($separator, $string), $index, $default);
        }
        
        /**
         * Joins chunks of a string with a specified insert and provides options for prepending and appending the
         * insert.
         * ----------------------------------------------------------------
         * Usage:
         * chunk_join("abcdefg", 2, "-");                                 // "ab-cd-ef-g"
         * chunk_join("123456789", 3, " ", true, true);                   // "- 1 2 3 4 5 6 7 8 9 -"
         *
         * @param string $string  The string to be chunked and joined.
         * @param int    $length  The length of each chunk.
         * @param string $insert  The string to insert between each chunk.
         * @param bool   $prepend [Optional] Whether to prepend the insert to the result. Default is false.
         * @param bool   $append  [Optional] Whether to append the insert to the result. Default is false.
         *
         * @return string The resulting string after joining the chunks with the insert.
         */
        public static function chunk_join(string $string, int $length, string $insert, bool $prepend = false, bool $append = false): string {
            $arr    = str_split($string, $length);
            $result = implode($insert, $arr);
            
            return ($prepend ? $insert : '') . $result . ($append ? $insert : '');
        }
        
        /**
         * Wraps a string into chunks of a specific length, with optional prefix and suffix.
         * ----------------------------------------------------------------
         * Usage:
         * chunk_wrap("Hello, world!", 5, "<", ">");                  // <Hello><, wo><rld!>
         * chunk_wrap("Lorem ipsum dolor sit amet", 10, "[", "]");    // [Lorem ipsu][m dolor s][it amet]
         *
         * @param string $string The string to be chunk wrapped.
         * @param int    $length The desired length of each chunk.
         * @param string $before Optional prefix to be added before each chunk.
         * @param string $after  Optional suffix to be added after each chunk.
         *
         * @return string The string wrapped into chunks of the specified length, with the optional prefix and suffix.
         */
        public static function chunk_wrap(string $string, int $length, string $before = '', string $after = ''): string {
            return $before . implode($after . $before, str_split($string, $length)) . $after;
        }
        
        /**
         * Converts a character to the specified encoding and returns its corresponding code point.
         * ---------------------------------------------------------------------------------------------
         * Usage:
         * cpmap('A', 'UTF-8');                        // 65
         * cpmap('あ', 'UTF-8');                       // 12354
         * cpmap('⚽', 'UTF-8');                       // 9917
         *
         * @param string $char        The character to convert.
         * @param string $to_encoding The target encoding to convert to.
         *
         * @return int|null The code point of the converted character, or null on failure or empty input.
         */
        public static function cpmap($char, $to_encoding): ?int {
            try {
                $t = iconv('UTF-8', $to_encoding, $char);
            } catch (\Exception) {
                return null;
            }
            
            return ($t === false || $t === '' ? null : (strlen($t) === 1 ? ord($t) : mb_ord($t)));
        }
        
        /**
         * Converts a code point to its corresponding Unicode character.
         * This function takes a code point and converts it to its corresponding Unicode character using the specified
         * encoding. It returns the Unicode character as an integer.
         *
         * @param int    $cp            The code point to be converted.
         * @param string $from_encoding The encoding of the code point.
         *
         * @return int|null The corresponding Unicode character as an integer, or null if conversion fails.
         */
        public static function cp2unicode(int $cp, string $from_encoding): ?int {
            $byte = hex2bin(dechex($cp));
            try {
                $utf8_char = iconv($from_encoding, 'UTF-8', $byte);
                
                return mb_ord($utf8_char, 'UTF-8');
            } catch (\Exception) {
                return null;
            }
        }
        
        /**
         * Converts a Unicode code point to its corresponding bytes representation.
         * -------------------------------------------------------------------------
         * Usage:
         * cp2bytes("U+0041");                 // Returns "\x00\x00\x00\x41"
         * cp2bytes("0x4E2D");                // Returns "\x00\x00\x4E\x2D"
         * cp2bytes("42");                    // Returns ""
         *
         * @param string $cp The Unicode code point to convert.
         *
         * @return string The bytes representation of the Unicode code point.
         */
        public static function cp2bytes(string $cp): string {
            $cp = strtolower($cp);
            if (self::startsWithOneOf($cp, ['u+', '0x'])) {
                $cp = substr($cp, 2);
            }
            if (ctype_xdigit($cp)) {
                return pack('N', hexdec($cp));
            }
            
            return '';
        }
        
        /**
         * Converts a string into an array of binary representation for each character.
         *
         * @param string $string The input string
         *
         * @return array|string[] An array of binary representation for each character in the input string.
         */
        public static function string_to_bin_array(string $string): array {
            $bin   = [];
            $chars = str_split($string, 1);
            foreach ($chars as $c) {
                $bin[] = str_pad(decbin(hexdec(bin2hex($c))), 8, '0', STR_PAD_LEFT);
            }
            
            return $bin;
        }
        
        /**
         * Converts string to UTF-8 if possible
         * Example: Euro-Sign "€" (\x80) in windows-1252 would then be
         * converted to the corresponding UTF-8 bytes (so \xE2\x82\xAC then in hex representation).
         * -
         * If the conversion fails for whatever reason, the original input string will be returned.
         *
         * @param string      $string          Input string
         * @param string|null $source_encoding Optional: input encoding. Pass NULL to auto-detect the encoding
         *
         * @return string
         */
        public static function str2utf8(string $string, string $source_encoding = null): string {
            static $encoding_list = null;
            if ($encoding_list === null) {
                $encoding_list = array_map('strtolower', mb_list_encodings());
            }
            $encoding = ($source_encoding && in_array(strtolower($source_encoding), $encoding_list) ? $source_encoding : mb_detect_encoding($string, mb_list_encodings()));
            if ($encoding && $encoding != 'UTF-8') {
                $tmp    = mb_convert_encoding($string, 'UTF-8', $encoding);
                $string = (is_string($tmp) ? $tmp : $string);
            }
            
            return $string;
        }
        
        /**
         * Retrieves the matching group from the subject string based on the provided regular expression pattern.
         *
         * This function takes a regular expression pattern, a subject string, and an optional index value.
         * It searches for a match in the subject string using the pattern and returns the corresponding group
         * based on the index value. If no match is found, it returns the default value.
         *
         * Example:
         * $pattern = "/(\d{2}-\d{2}-\d{4})/";
         * $subject = "Today's date is 12-01-2022";
         * $index = 1;
         * $default = "N/A";
         *
         * preg_group($pattern, $subject, $index, $default);
         * The output will be "12-01-2022"
         *
         * @param string $pattern The regular expression pattern to search for
         * @param string $subject The subject string to search within
         * @param int    $index   The index of the group to retrieve (default is 1)
         * @param string $default The default value to return if no match is found (default is an empty string)
         *
         * @return mixed            The matching group from the subject string or the default value
         */
        public static function preg_group(string $pattern, string $subject, int $index = 1, string $default = ''): mixed {
            $matches = [];
            if (preg_match($pattern, $subject, $matches)) {
                return arr($matches, $index, $default);
            }
            
            return $default;
        }
        
        /**
         * Encodes the given data using Base32 encoding.
         *
         * @param string $data The data to be encoded
         *
         * @return string The encoded data
         */
        public static function base32_encode(string $data): string {
            static $chars = 'abcdefghijklmnopqrstuvwxyz234567';
            $dataSize      = strlen($data);
            $res           = '';
            $remainder     = 0;
            $remainderSize = 0;
            
            for ($i = 0; $i < $dataSize; $i++) {
                $b             = ord($data[$i]);
                $remainder     = ($remainder << 8) | $b;
                $remainderSize += 8;
                while ($remainderSize > 4) {
                    $remainderSize -= 5;
                    $c             = $remainder & (31 << $remainderSize);
                    $c             >>= $remainderSize;
                    $res           .= $chars[$c];
                }
            }
            if ($remainderSize > 0) {
                // remainderSize < 5:
                $remainder <<= (5 - $remainderSize);
                $c         = $remainder & 31;
                $res       .= $chars[$c];
            }
            
            return $res;
        }
        
        /**
         * Decodes a string encoded using the base32 encoding scheme.
         * This method converts the encoded string back to its original form.
         * It throws an exception if the encoded string contains any unknown characters.
         *
         * @param string $data The encoded string to be decoded
         *
         * @return string The decoded string
         *
         * @throws \Exception If the encoded string contains unknown characters
         */
        public static function base32_decode(string $data): string {
            static $chars = 'abcdefghijklmnopqrstuvwxyz234567';
            $data     = strtolower($data);
            $dataSize = strlen($data);
            $buf      = 0;
            $bufSize  = 0;
            $res      = '';
            
            for ($i = 0; $i < $dataSize; $i++) {
                $c = $data[$i];
                $b = strpos($chars, $c);
                if ($b === false) {
                    throw new BaseException('Encoded string is invalid, it contains unknown char #' . ord($c));
                }
                $buf     = ($buf << 5) | $b;
                $bufSize += 5;
                if ($bufSize > 7) {
                    $bufSize -= 8;
                    $b       = ($buf & (0xff << $bufSize)) >> $bufSize;
                    $res     .= chr($b);
                }
            }
            
            return $res;
        }
        
        /**
         * Replaces line breaks in a string with newline characters.
         * ---------------------------------------------------------
         * Usage:
         * newlineLF("Hello\r\nWorld");            // "Hello\nWorld"
         * newlineLF("This is a\rtest");           // "This is a\ntest"
         *
         * @param string $string The input string to replace line breaks.
         *
         * @return array|string The modified string with line breaks replaced by newline characters.
         */
        public static function newlineLF(string $string): array|string {
            return str_replace(["\r\n", "\r"], ["\n", "\n"], $string);
        }
        
        /**
         * Replaces newline characters with carriage return and newline characters in a given string.
         * ----------------------------------------------------------------
         * Usage:
         * newlineCRLF("Hello\nWorld");             // "Hello\r\nWorld"
         * newlineCRLF("This is\na test");          // "This is\r\na test"
         *
         * @param string $string The string to replace newline characters in.
         *
         * @return array|string The modified string with newline characters replaced with carriage return and newline
         *                      characters.
         */
        public static function newlineCRLF(string $string): array|string {
            return str_replace("\n", "\r\n", static::newlineLF($string));
        }
        
        /**
         * Replace newline characters '\n' with carriage return '\r' in a string.
         * --------------------------------------------------------------------
         * Usage:
         * newlineCR("Hello\nWorld");             // "Hello\rWorld"
         * newlineCR("Hello\r\nWorld");           // "Hello\rWorld"
         *
         * @param string $string The string in which newline characters need to be replaced.
         *
         * @return array|string The modified string with newline characters replaced with carriage return.
         */
        public static function newlineCR(string $string): array|string {
            return str_replace("\n", "\r", static::newlineCRLF($string));
        }
        
        /**
         * Shortens a given string to a specified length.
         * ----------------------------------------------------------------
         * Usage:
         * shorten("This is a long sentence.", 10); // "This is a"
         * shorten("Hello, world!", 5);            // "Hello"
         *
         * @param string $string The string to be shortened.
         * @param int    $length The length to which the string should be shortened.
         *
         * @return string The shortened string.
         */
        public static function shorten(string $string, int $length): string {
            return mb_substr($string, 0, $length);
        }
        
        /**
         * Concatenates all given values into a single string, using a specified joiner.
         * ------------------------------------------------------------------------
         * Usage:
         * concat("-", "Hello", "World");             // "Hello-World"
         * concat(" ", "Lorem", "ipsum", "dolor");     // "Lorem ipsum dolor"
         *
         * @param string $joiner    (optional) The joiner to be used between values. Default is a space.
         * @param mixed  ...$values The values to be concatenated into a string.
         *
         * @return string The concatenated string with trimmed leading and trailing joiners.
         */
        public static function concat(string $joiner = ' ', ...$values) {
            self::cast(...$values);
            
            return trim(implode($joiner, $values), self::TRIM_DEFAULT . $joiner);
        }
        
        /**
         * Retrieves the substring between two specified strings.
         *
         * This function searches for the first occurrence of the $from string and the first occurrence of the $to
         * string in the $string. It returns the substring between these two strings, excluding the $from and $to
         * strings themselves. If either the $from or $to string is not found in the $string, an empty string is
         * returned.
         *
         * Example:
         * Input.....: $string = "Hello World", $from = "e", $to = "o"
         * Output....: "ll"
         *
         * @param string $string The input string to search within.
         * @param string $from   The starting string.
         * @param string $to     The ending string.
         *
         * @return string The substring between the $from and $to strings, excluding the $from and $to strings.
         */
        public static function between(string $string, string $from, string $to): string {
            $start = mb_strpos($string, $from);
            $end   = mb_strpos($string, $to, $start);
            if ($start === false || $end === false) {
                return '';
            }
            $start += 1;
            
            return mb_substr($string, $start, ($end - $start));
        }
        
        /**
         * Checks if a string starts with a specific substring and ends with another specific substring.
         *
         * Example:
         * Input.....: "Hello World", "Hello", "World"
         * Output....: true
         *
         * @param string $string Input string
         * @param string $before Substring to check if it starts with
         * @param string $end    Substring to check if it ends with
         *
         * @return bool Returns true if the string starts with $before and ends with $end, otherwise false.
         */
        public static function enclosed(string $string, string $before = '', string $end = ''): bool {
            return (str_starts_with($string, $before) && str_ends_with($string, $end));
        }
        
        /**
         * Checks if a string starts with any of the specified strings.
         *
         * It iterates through each string in the array and checks if the given string starts with it.
         * If a match is found, it returns true. Otherwise, it returns false.
         *
         * @param string $string  The input string to check
         * @param array  $strings An array of strings to compare against
         *
         * @return bool Returns true if the input string starts with any of the specified strings; false otherwise.
         */
        public static function startsWithOneOf(string $string, array $strings): bool {
            foreach ($strings as $s) {
                if (str_starts_with($string, $s)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Casts the given values to string.
         *
         * @param mixed &...$values The values to be casted. Pass the values by reference.
         */
        public static function cast(&...$values): void {
            foreach ($values as &$value) {
                $value = self::from($value);
            }
        }
        
        /**
         * Exploded nur, wenn der String nicht leer ist.
         *
         * @param string $separator
         * @param string $string
         *
         * @return array|string[]
         */
        public static function explode(string $separator, string $string): array {
            return ($string === '' ? [] : explode($separator, $string));
        }
        
        /**
         * Removes specified characters from the string.
         *
         * This function removes the characters specified in the $chars parameter from the input string.
         * It uses the str_replace_chars() function with an empty replacement string to achieve this.
         *
         * Example:
         * Input.....: "Hello World"
         * $chars....: ['l', 'o']
         * Output....: "He Wrld"
         *
         * @param string       $string Input string
         * @param array|string $chars  Characters to remove from the string
         *
         * @return string The modified string with specified characters removed
         */
        public static function removeChars(string $string, array|string $chars): string {
            return str_replace(is_string($chars) ? mb_str_split($chars) : $chars, '', $string);
        }
        
        /**
         * Prüft, ob ein String mindestens einen der übergebenen strings enthält
         *
         * @param string $haystack
         * @param array  $strings
         * @param bool   $ignore_case
         *
         * @return bool
         */
        public static function containsAny(string $haystack, array $strings, bool $ignore_case = true): bool {
            foreach ($strings as $string) {
                if (($ignore_case && str_contains(strtolower($haystack), strtolower($string))) || str_contains($haystack, $string)) {
                    return true;
                }
            }
            
            return false;
        }
        
        /**
         * Generates a version 4 UUID.
         *
         * This function generates a version 4 UUID using the random_string method to generate
         * the necessary sections. The resulting UUID is returned as a string in the format
         * xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.
         * Brashly C&P'd from stackoverflow
         *
         *
         * @return string The generated version 4 UUID.
         * @throws \Exception
         */
        public static function uuid4() {
            $data = random_bytes(16);
            
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        
        /**
         * Collapses consecutive occurrences of specified characters in a string.
         * --------------------------------------------------------------------
         * Usage:
         * collapse("Hello   World", " ");                      // "Hello World"
         * collapse("aabbccdd", ["a", "b" , "c"]);              // "abcd"
         * collapse("A...B...C", ['.', '-']);                   // "A.B.C"
         *
         * @param string       $string              The input string to collapse.
         * @param array|string $characters          (Optional) The characters to collapse. Defaults to ' '
         *                                          (whitespace).
         *                                          Can be provided as an array or string.
         * @param bool         $auto_trim           (Optional) Whether to automatically trim collapsed characters from
         *                                          the beginning and end of the string. Defaults to false.
         *
         * @return string The collapsed string with consecutive occurrences of specified characters replaced by a
         *                single instance.
         */
        public static function collapse(string $string, array|string $characters = ' ', bool $auto_trim = false): string {
            $characters = is_array($characters) ? implode('', $characters) : $characters;
            $result     = ($characters === '' ? $string : ((string)preg_replace('~([' . preg_quote($characters, '~') . '])\1+~', '$1', $string)));
            
            return ($auto_trim ? trim($result, $characters) : $result);
        }
        
        /**
         * Checks if a string has a Byte Order Mark (BOM) at the beginning.
         * ----------------------------------------------------------------
         * Usage:
         * $string = "\xef\xbb\xbfHello World";
         * $hasBom = hasBom($string);            // true
         *
         * @param string $string The string to check for BOM.
         *
         * @return bool Returns true if the string has a BOM at the beginning, false otherwise.
         */
        public static function hasBom(string $string): bool {
            return self::startsWithOneOf($string, [
                "\xef\xbb\xbf",
                "\xfe\xff",
                "\xff\xfe",
                "\x00\x00\xfe\xff",
                "\xff\xfe\x00\x00",
                "\x2b\x2f\x76\x38",
                "\x2b\x2f\x76\x39",
                "\x2b\x2f\x76\x2b",
                "\x2b\x2f\x76\x2f",
                "\xf7\x64\x4c",
                "\xdd\x73\x66\x73",
                "\x0e\xfe\xff",
                "\xfb\xee\x28",
                "\x84\x31\x95\x33"
            ]);
        }
        
        /**
         * Checks if a given character is a printable ASCII character.
         *
         * @param string $char The character to check.
         *
         * @return bool Returns true if the character is a printable ASCII character, false otherwise.
         */
        public static function isPrintableAsciiChar(string $char): bool {
            $cp = mb_ord($char);
            
            return $cp >= 32 && $cp <= 126;
        }
        
    }
