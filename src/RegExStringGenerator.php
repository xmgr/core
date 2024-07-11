<?php
    
    namespace Xmgr;
    
    
    use Random\RandomException;
    
    /**
     * Class RegExStringGenerator
     *
     * @package WD\Core
     */
    class RegExStringGenerator {
        
        /**
         * Limits infinite-quantifiers to this max value (e.g. "*" or "+")
         */
        public const int INFINITE_QUANTIFIER_MAX = 10;
        
        /**
         * @var array Group results (used for backreference like "\1")
         */
        protected array $groups = [];
        
        /**
         * @var array Group patterns (used for backreference like "\g1")
         */
        protected array $group_patterns = [];
        
        /**
         * @var string Your RegEx pattern
         */
        protected string $string = '';
        
        /**
         * @var int The current position in the string (see tokenize())
         */
        protected int $offset = 0;
        
        /**
         * @var int Numeric index for groups (increments on "(" opening parenthesis)
         */
        protected int $group_num = -1;
        
        protected int $result_limit = 2000;
        protected int $q_limit      = 1000;
        
        protected static array $posix_character_classes = [
            'umlaut'     => 'aöüÄÖÜ',
            '-umlaut'    => 'aöü',
            '+umlaut'    => 'ÄÖÜ',
            'vowel'      => 'aeiouAEIOU',
            '-vowel'     => 'aeiou',
            '+vowel'     => 'AEIOU',
            'consonant'  => 'bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ',
            '-consonant' => 'bcdfghjklmnpqrstvwxyz',
            '+consonant' => 'BCDFGHJKLMNPQRSTVWXYZ',
            'alpha'      => 'a-zA-Z',
            'upper'      => 'A-Z',
            'lower'      => 'a-z',
            'digit'      => '0-9',
            'alnum'      => 'a-zA-Z0-9',
            'xdigit'     => '0-9A-Fa-f',
            'space'      => "\t\r\v\n ",
            'blank'      => " \t",
            'print'      => ' -~',
            'punct'      => "!'#S%&'()*+,-./:;<=>?@\[/\]^_{|}~",
            'word'       => 'a-zA-Z0-9_',
        ];
        
        /**
         * Get the parsed result of a string
         *
         * @param string $string The input string to be parsed
         *
         * @return string The parsed result string
         */
        public function get($string): string {
            $string = (string)$string;
            if ($string === '') {
                return '';
            }
            
            # Reset stuff
            $this->groups         = [];
            $this->group_patterns = [];
            $this->string         = "($string)";
            //$this->string = $string;
            $this->offset    = 0;
            $this->group_num = -1;
            
            # Tokenize string and build up a recursive stack
            $stack = $this->tokenize();
            if (false && dev()) {
                echo '<h3>Der Stack nach dem Parsen des Strings:</h3>';
                echo "<div style='background-color: #eeeeee;padding: 8px;'><pre>" . print_r($stack, true) . '</pre></div>';
                // echo pre($this->groups);
                // echo pre($this->group_patterns);
                // echo pre($this->string);
                // echo pre($this->offset);
                // echo pre($this->group_num);
            }
            
            # Parses the stack and generates the result string
            return $this->parse($stack);
        }
        
        /**
         * Parse the given stack and return a string output
         *
         * @param array $stack The stack to be parsed
         *
         * @return string The parsed result
         * @throws RandomException
         */
        protected function parse($stack): string {
            $result = '';
            if ($stack) {
                foreach ($stack as $k => $v) {
                    if (strlen($result) > $this->result_limit) {
                        break;
                    }
                    # If it's an empty string or an empty array, just skip
                    if ($v === '' || $v === []) {
                        continue;
                    }
                    if (is_array($v)) {
                        # If the current element consists of only 1 value and has a type, we
                        # save one additional redundant recursive call, so we can pick that value directly.
                        if (count($v) === 1 && isset($v[0]['type'])) {
                            $v = $v[0];
                        }
                        if (isset($v['type'])) {
                            # The type of current stack element
                            $type   = $v['type'];
                            $action = ($v['action'] ?? '');
                            $str    = '';
                            # Check quantifiers
                            if (isset($v['min']) && isset($v['max'])) {
                                $v['max'] = min((int)$v['max'], $this->q_limit);
                                $q        = random_int((int)$v['min'], (int)$v['max']);
                                if (!$q) {
                                    # Just skip this part if quantifier is zero
                                    continue;
                                }
                                
                                # Quantify element
                                while ($q--) {
                                    $this->parseValue($type, $v, $str, $action);
                                }
                            } else {
                                if (isset($v['q'])) {
                                    $q = (int)$v['q'];
                                    $q = min($q, $this->q_limit);
                                    if ($q) {
                                        while ($q--) {
                                            $this->parseValue($type, $v, $str, $action);
                                        }
                                    }
                                } else {
                                    # No quantifier here, just process this current step once
                                    $this->parseValue($type, $v, $str, $action);
                                }
                            }
                            
                            if ($action !== '') {
                                if ($action == 'shuffle') {
                                    $str = str_shuffle($str);
                                }
                                if (false && $action == 'random-case') {
                                    
                                    $chrArray = preg_split('//u', $v['value'], -1, PREG_SPLIT_NO_EMPTY);
                                    foreach ($chrArray as &$chr) {
                                        $r   = random_int(0, 2);
                                        $chr = ($r === 0 ? $chr : ($r === 1 ? mb_strtolower($chr) : mb_strtoupper($chr)));
                                        $str .= $chr;
                                    }
                                }
                            }
                            
                            $result .= $str;
                        } else {
                            # If this element doesn't hold a "type" key, it's a multi-dimensional (most likely numerically indexed) array
                            # So we'll step into that
                            $result .= $this->parse($v);
                        }
                    }
                }
            }
            
            return $result;
        }
        
        /**
         * Parse a value
         *
         * @param string        $type   The type of the value
         * @param array|string &$v      The reference to the value being parsed
         * @param string   &    $result The reference to the resulting string
         * @param string        $action (optional) Additional action to be performed
         *
         * @return void
         * @throws RandomException
         */
        protected function parseValue(string $type, array|string &$v, string &$result, string $action = ''): void {
            $capture = true;
            
            # Check for backreference and re-parse the pattern (like "\g1")
            if ($type === 'sr') {
                if (isset($this->group_patterns[$v['value']])) {
                    $v       = $this->group_patterns[$v['value']];
                    $type    = 'group';
                    $capture = false;
                } else {
                    # Group number doesn't exist yet
                    # Can happen with patterns like "\g2"
                    return;
                }
            }
            
            # Check for backreference like "\1"
            if ($type === 'ref') {
                if (isset($this->groups[$v['value']])) {
                    $result .= $this->groups[$v['value']];
                }
                
                return;
            }
            
            # Stop further processing if the value is empty. This also avoids mutually exclusive patterns.
            if ($v['value'] === '' || $v['value'] === []) {
                return;
            }
            
            switch ($type) {
                /*
                 * Append a single, plain character
                 */
                case 'char':
                    $result .= $v['value'];
                    break;
                
                /*
                 * Append a character class
                 */
                case 'class':
                    # Avoid error on mutually exclusive patterns like in [^\w\W]
                    $sl = mb_strlen($v['value']) - 1;
                    if ($sl === -1) {
                        break;
                    }
                    
                    # We pick one single, random character out of the string
                    // $result .= $v["value"][random_int(0, $sl)];
                    $result .= mb_substr($v['value'], random_int(0, $sl), 1);
                    unset($sl);
                    break;
                
                /*
                 * Processes a group
                 */
                case 'group':
                    # Don't capture backreferences as additional groups
                    if ($capture) {
                        # Add group
                        $this->group_patterns[] = $v;
                        if (isset($v['name']) && $v['name']) {
                            # Additionally, add reference to named group
                            $this->group_patterns[$v['name']] = $v;
                        }
                    }
                    
                    # Check if group contains multiple options or just one.
                    if (isset($v['value'][0][0])) {
                        # Group has multiple (>=2) options
                        $gv = $this->parse($v['value'][random_int(0, count($v['value']) - 1)]);
                    } else {
                        # Group contains one single option
                        $gv = $this->parse($v);
                    }
                    $this->groups[$v['num']] = $gv;
                    if (isset($v['name']) && $v['name']) {
                        $this->groups[$v['name']] = $gv;
                    }
                    $result .= $gv;
                    break;
                # We won't care about other stuff
                default:
                    exit('Dude, wtf did you do to reach that case?');
            }
        }
        
        /**
         * Tokenize the given string
         *
         * @return array The tokens generated from the string
         * @throws RandomException
         */
        protected function tokenize(): array {
            $stack  = [];
            $string = &$this->string;
            $length = mb_strlen($string);
            
            # Loop through pattern characters
            # Offset is the current position in the string
            while ($this->offset < $length) {
                # This is the current character
                $character = mb_substr($string, $this->offset, 1);
                $next      = mb_substr($string, $this->offset + 1, 1);
                // echo pre($character);
                
                $stackcount = count($stack);
                
                # Check what the current character is
                switch ($character) {
                    /*
                     * Escape sequence (\s, \w, \d, \S, \W, \D, \<num>, \g<num>)
                     */
                    case '\\':
                        # Process only if there exists a character after the current one
                        if ($next !== '') {
                            $this->push($stack, $this->resolve_escaped($next));
                        }
                        $this->offset++;
                        break;
                    
                    /*
                     * Character class like "[abc]" (so "a", "b" or "c")
                     */
                    case '[':
                        # If the next char is "]", skip that (so there's an empty character class like "[]")
                        if ($next === ']') {
                            $this->offset++;
                            # Yes, we push an empty string onto the stack because otherwise a pattern
                            # like "AB[]?" would make either "A" or "AB" (so basically the "?" would quantify
                            # the previous token which is the single letter "B").
                            $this->push($stack, 'char', '');
                            break;
                        }
                        
                        # Tokenize character class
                        $this->tokenize_class($stack);
                        break;
                    
                    /*
                     * Any character
                     */
                    case '.':
                        $this->push($stack, 'class', $this->any_character());
                        break;
                    
                    /*
                     * Starting a group like "(one|two|three)"
                     */
                    case '(':
                        $this->offset++;
                        $rest = mb_substr($string, $this->offset);
                        # Comment group
                        if (mb_strpos($rest, '?#') === 0) {
                            $end_offset   = mb_strpos($rest, ')');
                            $this->offset += $end_offset;
                            break;
                        }
                        $name = '';
                        if (mb_strpos($rest, '?<') === 0) {
                            $no           = mb_strpos($rest, '>');
                            $name         = mb_substr($rest, 2, $no - 2);
                            $this->offset += $no + 1;
                        }
                        $this->group_num++;
                        $stack[] = ['type' => 'group', 'num' => $this->group_num, 'name' => $name, 'value' => $this->tokenize()];
                        $this->offset--;
                        break;
                    
                    /*
                     * Closing group
                     */
                    case ')':
                        $this->offset++;
                        break 2;
                    
                    /*
                     * Group option
                     */
                    case '|':
                        # We split the whole thing later, see code after this switch statement
                        $stack[] = '|';
                        break;
                    
                    /*
                     * Random range or specified quantifier
                     */
                    case '{':
                        # Avoid stuff like "{}" - doesn't repeat anything so we skip this part
                        if ($next === '}') {
                            $this->offset++;
                            break;
                        }
                        if (!$stack) {
                            #break;
                        }
                        
                        # Fetch string between { and }
                        $qs = '';
                        for ($j = $this->offset + 1; $j < mb_strlen($string); $j++) {
                            # Move the overall offset along
                            $this->offset++;
                            if (mb_substr($string, $j, 1) === '}') {
                                break;
                            }
                            $qs .= mb_substr($string, $j, 1);
                        }
                        
                        # Continue if invalid content or there are no elements in stack
                        if ($qs === '' || !$stack) {
                            break;
                        }
                        
                        $ls = &$stack[count($stack) - 1];
                        
                        # Check what type of quantifier we do have here
                        if (is_numeric($qs)) {
                            # Quantifier is just a number ("2" or "123" or something)
                            $ls['q'] = abs((int)$qs);
                            break;
                        } else {
                            # We have multiple quantifiers, so something like {2|4} or {5|10|15} or {1|3|5}
                            $qs = trim($qs, '| ');
                            if (mb_strpos($qs, '|') !== false) {
                                $arr = explode('|', $qs);
                                if ($arr) {
                                    if (str_contains($qs, '-')) {
                                        # We have a range somewhere
                                        foreach ($arr as &$sq) {
                                            if (str_contains($sq, '-')) {
                                                # Current quantifier is a range, like "{1,2,5-10}
                                                $sarr = explode('-', $sq);
                                                if (count($sarr) >= 2) {
                                                    $sqmin = (int)$sarr[0];
                                                    $sqmax = (int)$sarr[1];
                                                    $sq    = random_int(min($sqmin, $sqmax), max($sqmin, $sqmax));
                                                } else {
                                                    $sq = (int)$sarr[0];
                                                }
                                            }
                                        }
                                    }
                                    $rq      = abs((int)$arr[random_int(0, count($arr) - 1)]);
                                    $ls['q'] = $rq;
                                    break;
                                }
                                break;
                            }
                            # We got a range like {2,4} or {,5} or {3,}
                            if (mb_strpos($qs, ',') !== false) {
                                $arr = explode(',', $qs);
                                if ($arr && isset($arr[0]) && isset($arr[1])) {
                                    $min = (int)$arr[0];
                                    $max = $arr[1];
                                    if ($max === '') { // so we have something like "{3,}"
                                        # In this case, we use the min value and increment it with limited max.
                                        $max = $min + static::INFINITE_QUANTIFIER_MAX;
                                    } else {
                                        $max = (int)$max;
                                        $max = max($max, $min);
                                    }
                                    
                                    $ls['min'] = $min;
                                    $ls['max'] = $max;
                                    break;
                                }
                            }
                        }
                        break;
                    
                    /*
                     * 0 or 1 quantifier
                     */
                    case '?':
                        if ($stack && is_array($stack[$stackcount - 1])) {
                            $stack[$stackcount - 1]['min'] = 0;
                            $stack[$stackcount - 1]['max'] = 1;
                        }
                        break;
                    
                    /*
                     * 1 or infinite quantifier
                     */
                    case '+':
                        if ($stack && is_array($stack[$stackcount - 1])) {
                            $stack[$stackcount - 1]['min'] = 1;
                            $stack[$stackcount - 1]['max'] = static::INFINITE_QUANTIFIER_MAX;
                        }
                        break;
                    
                    /*
                     * 0 or infinite quantifier
                     */
                    case '*':
                        if ($stack && is_array($stack[$stackcount - 1])) {
                            $stack[$stackcount - 1]['min'] = 0;
                            $stack[$stackcount - 1]['max'] = static::INFINITE_QUANTIFIER_MAX;
                        }
                        break;
                    
                    /**
                     * Just a plain character
                     */
                    default:
                        $this->push($stack, 'char', $character);
                        break;
                }
                $this->offset++;
            }
            
            # Check if the stack has options
            if (in_array('|', $stack, true)) {
                $options = [];
                while (($offset = array_search('|', $stack, true)) !== false) {
                    $curr = array_splice($stack, 0, $offset + 1);
                    array_pop($curr);
                    $options[] = $curr;
                }
                $options[] = $stack;
                
                return $options;
            } else {
                return $stack;
            }
        }
        
        /**
         * Push a value onto the stack
         *
         * @param $stack
         * @param $type
         * @param $value
         */
        protected function push(&$stack, $type, $value = null): void {
            if ($value === null) {
                $stack[] = $type;
            } else {
                $stack[] = ['type' => $type, 'value' => $value];
            }
        }
        
        /**
         * Process a character class
         *
         * @param array $stack
         *
         * @return string
         */
        protected function tokenize_class(array &$stack): string {
            $string = mb_substr($this->string, $this->offset + 1);
            $i      = 0;
            $result = '';
            while ($i < mb_strlen($string)) {
                # Current character
                $c = mb_substr($string, $i, 1);
                
                # The overall string offset moves along as well
                $this->offset++;
                switch ($c) {
                    /*
                     * Escaped character
                     */
                    case '\\':
                        $c  = mb_substr($string, $i + 1, 1); // (isset($string[$i + 1]) ? $string[$i + 1] : "");
                        $tr = $this->resolve_escaped($c, true, $i);
                        if (is_string($tr)) {
                            $result .= $tr;
                        } else {
                            if (is_array($tr) && isset($tr['value'])) {
                                $result .= $tr['value'];
                            }
                        }
                        $this->offset++;
                        $i++;
                        break;
                    # POSIX character classes
                    case ':':
                        $rest = mb_substr($string, $i);
                        // echo pre($rest);
                        foreach (static::$posix_character_classes as $k => $v) {
                            $find = "$k:";
                            if (mb_strpos($rest, ":$find") === 0) {
                                $this->offset += mb_strlen($find);
                                $i            += mb_strlen($find);
                                $result       .= $v;
                            }
                        }
                        break;
                    
                    /*
                     * End of character class detected
                     */
                    case ']':
                        # Step out of the switch and loop
                        break 2;
                    
                    /*
                     * Just add the current character
                     */
                    default:
                        $result .= $c;
                        break;
                }
                $i++;
            }
            
            # Replace ranges like "a-z", "A-Z", "0-9", "a-k", "4-7", ...
            # The strpos check saved a little bit of performance because if the string
            # doesn't even contain a "-", there can't be a range so we save a redundant preg_replace
            # @todo check with previous and next character instead of replacing all at once(?)
            if (mb_strpos($result, '-') !== false) {
                // $result = preg_replace_callback('/(([a-z])\-([a-z])|([A-Z])\-([A-Z])|([0-9])\-([0-9]))/', function ($matches) {
                $result = preg_replace_callback('/(.)-(.)/u', function ($matches) {
                    $arr = explode('-', $matches[0]);
                    if (isset($arr[1])) {
                        return implode('', $this->mb_range($arr[0], $arr[1]));
                    }
                    
                    return $matches[0];
                }, $result);
            }
            
            # Negate character class chars (so you've something like [^abc])
            if (mb_substr($string, 0, 1) === '^') {
                $result = mb_substr($result, 1); // We remove the leading "^"
                # Now we say that the character class consists of "all characters" and
                # We str_replace all current characters out of that
                $result = str_replace(str_split($result), '', $this->any_character());
            }
            
            # Pushing the character class onto the stack
            $this->push($stack, 'class', $result);
            
            return $result;
        }
        
        /**
         * multibyte string compatible range('A', 'Z')
         *
         * @param string $start Character to start from (included)
         * @param string $end   Character to end with (included)
         *
         * @return array list of characters in unicode alphabet from $start to $end
         * @author Rodney Rehm
         */
        protected function mb_range(string $start, string $end): array {
            $range = [];
            $a     = mb_ord($start, 'UTF-8');
            $b     = mb_ord($end, 'UTF-8');
            for ($code = min($a, $b); $code <= max($a, $b); $code++) {
                $range[] = mb_chr($code, 'UTF-8');
            }
            
            return $range;
        }
        
        /**
         * Resolve escaped characters in a string
         *
         * @param string $string         The input string
         * @param bool   $inClass        Whether the method is called from tokenize_class() (default: false)
         * @param int &  $current_offset The current offset in the string (default: 0)
         *
         * @return array|string The resolved characters as an array if $inClass is true, otherwise a string
         */
        protected function resolve_escaped(string $string, bool $inClass = false, int &$current_offset = 0): array|string {
            $chars = $string;
            $type  = 'char';
            switch (true) {
                # Nothing
                case ($string === '0'):
                    $chars = '';
                    break;
                # Non-alphanumeric char
                case ($string === 'W'):
                    $chars = $this->special_character();
                    $type  = 'class';
                    break;
                # Non-digit
                case ($string === 'D'):
                    $chars = $this->letters_characters() . $this->special_character();
                    $type  = 'class';
                    break;
                # Alphanumeric char
                case ($string === 'w'):
                    $chars = $this->word_characters();
                    $type  = 'class';
                    break;
                # Digit
                case ($string === 'd'):
                    $chars = $this->number_characters();
                    $type  = 'class';
                    break;
                # Whitespace
                case ($string === 's'):
                    $chars = $this->whitespace_characters();
                    $type  = 'class';
                    break;
                # Non-Whitespace
                case ($string === 'S'):
                    $chars = $this->any_character_except_whitespaces();
                    $type  = 'class';
                    break;
                # Subroutine
                case ($string === 'g' && !$inClass):
                    $next = $this->string[$this->offset + 2];
                    if (is_numeric($next)) {
                        $ret = ['type' => 'sr', 'value' => $this->string[$this->offset + 2]];
                        $this->offset++;
                        
                        return $ret;
                    }
                    if ($next === '{') {
                        $rest         = mb_substr($this->string, $this->offset + 3);
                        $ne           = mb_strpos($rest, '}');
                        $name         = mb_substr($rest, 0, $ne);
                        $ret          = ['type' => 'sr', 'value' => $name];
                        $this->offset += $ne + 2;
                        
                        return $ret;
                    }
                    break;
                # Named backreference
                case ($string === 'k' && !$inClass):
                    $next = $this->string[$this->offset + 2];
                    if ($next === '{') {
                        $rest         = mb_substr($this->string, $this->offset + 3);
                        $ne           = mb_strpos($rest, '}');
                        $name         = mb_substr($rest, 0, $ne);
                        $ret          = ['type' => 'ref', 'value' => $name];
                        $this->offset += $ne + 2;
                        
                        return $ret;
                    }
                    break;
                # Unicode char
                case ($string === 'u' || $string === 'U'):
                    $rest = mb_substr($this->string, $this->offset + 2);
                    
                    $e      = '';
                    $offset = $this->offset + 2;
                    if ($rest[0] === '+') {
                        // echo "k";
                        $this->offset++;
                        $current_offset++;
                        $offset++;
                        $rest = mb_substr($this->string, $offset);
                    }
                    if ($rest[0] === '{') {
                        $offset++;
                        $this->offset   += 2;
                        $current_offset += 2;
                    }
                    
                    $rest = mb_substr($this->string, $offset);
                    $rl   = mb_strlen($rest);
                    for ($j = 0; $j < $rl; $j++) {
                        $cuc = mb_substr($rest, $j, 1);
                        if (!ctype_xdigit($cuc) /*|| strlen($e) > 6*/) {
                            break;
                        }
                        $this->offset++;
                        $current_offset++;
                        $e .= $cuc;
                    }
                    
                    $chars = mb_chr(hexdec($e), 'UTF-8');
                    break;
                # Hex to char - use via "\x<HEXCODE>" (0-7F)
                case ($string === 'x'):
                    $hex = mb_substr($this->string, $this->offset + 2, 2);
                    if (!ctype_xdigit($hex)) {
                        break;
                    }
                    $chars          = hex2bin($hex);
                    $this->offset   += 2;
                    $current_offset += 2;
                    break;
                # Charcode to char - use with "\#<CHARCODE>" (0-127)
                case ($string === '#'):
                    $cc   = '';
                    $rest = mb_substr($this->string, $this->offset + 2);
                    if (mb_strpos($rest, '{') === 0) {
                        $dec            = (int)Str::between($rest, '{', '}');
                        $this->offset   += (mb_strlen($dec) + 2);
                        $current_offset += (mb_strlen($dec) + 2);
                        $cc             = $dec;
                    } else {
                        $rl = mb_strlen($rest);
                        for ($j = 0; $j < $rl; $j++) {
                            if (is_numeric($rest[$j])) {
                                $this->offset++;
                                $current_offset++;
                                $cc .= $rest[$j];
                            } else {
                                break;
                            }
                        }
                    }
                    
                    $chars = mb_chr($cc, 'utf-8');
                    break;
                # Vertical Tab
                case ($string === 'v'):
                    $chars = "\v";
                    break;
                # Horizintal Tab
                case ($string === 't'):
                    $chars = "\t";
                    break;
                # Line feed LF
                case ($string === 'n'):
                    $chars = "\n";
                    break;
                # Carriage return CR
                case ($string === 'r'):
                    $chars = "\r";
                    break;
                # Backslash
                case ($string === '\\'):
                    $chars = '\\';
                    break;
                # Backreference
                case is_numeric($string):
                    return ['type' => 'ref', 'value' => $string];
                # Plain string
                default:
                    break;
            }
            
            # If we called this method from tokenize_class(), we return the string...
            if ($inClass) {
                return $chars;
            }
            
            # ... otherwise, we return a stack element.
            
            return ['type' => $type, 'value' => $chars];
        }
        
        /**
         * Returns numbers
         *
         * @return string
         */
        protected function number_characters(): string {
            return '0123456789';
        }
        
        /**
         * Returns lowercase letters
         *
         * @return string
         */
        protected function lowercase_letters_characters(): string {
            return 'abcdefghijklmnopqrstuvwxyz';
        }
        
        /**
         * Returns uppercase letters
         *
         * @return string
         */
        protected function uppercase_letters_characters(): string {
            return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        
        /**
         * Returns both lower- and uppercase letters
         *
         * @return string
         */
        protected function letters_characters(): string {
            return ($this->lowercase_letters_characters() . $this->uppercase_letters_characters());
        }
        
        /**
         * Word-characters (so "A-Z", "a-z", "0-9" and "_")
         *
         * @return string
         */
        protected function word_characters(): string {
            return $this->lowercase_letters_characters() . $this->uppercase_letters_characters() . $this->number_characters();
        }
        
        /**
         * Whitespaces
         *
         * @return string
         */
        protected function whitespace_characters(): string {
            // return " \f\n\r\t\v\u00A0\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u2028\u2029\u2028\u2029\u202f\u205f\u3000";
            return " \r\n\t";
        }
        
        /**
         * All characters
         *
         * @return string
         */
        protected function any_character(): string {
            return $this->letters_characters() . $this->number_characters() . $this->special_character();
        }
        
        /**
         * Special characters
         *
         * @return string
         */
        protected function special_character(): string {
            return "/_%@$#+.-_,;|<>!?=(){}[]`&^~*:'\" \\";
        }
        
        /**
         * @param array $exclude_list
         *
         * @return string|array
         */
        protected function special_characters_except(array $exclude_list = []): string|array {
            return str_replace($exclude_list, '', $this->special_character());
        }
        
        /**
         * Special characters excluding whitespaces
         *
         * @return array|string|string[]
         */
        protected function special_characters_except_whitespace(): array|string {
            return str_replace(str_split($this->whitespace_characters()), '', $this->special_character());
        }
        
        /**
         * Any character except whitespaces
         *
         * @return string
         */
        protected function any_character_except_whitespaces(): string {
            return ($this->letters_characters() . $this->number_characters() . $this->special_characters_except_whitespace());
        }
        
        /**
         * @param string $pattern
         * @param int    $qty
         *
         * @return array
         * @throws RandomException
         */
        public function some(string $pattern, int $qty = 10): array {
            $qty   = max($qty, 1);
            $stack = [];
            while ($qty-- > 0) {
                $stack[] = $this->get($pattern);
            }
            
            return $stack;
        }
        
    }
    
