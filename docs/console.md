# Console

Example console command (used for all example on this page):

```shell
xmcli -PrNf --no-cache --user=john -m 3 -T -S 'none' ./example.txt -aG
```

## Terminology

- "input" means the entire command that has been entered in the console
- "named argument" means arguments that start with two hypens `--` and that may or may not has a value assigned. Example: value for "--no-cache" is `true`, value for `--user=john` is "john".
- "option" (or "flag") means an argument that starts with one hypen `-`. Each option can be a single argument (`-P -r -N -f`) or combined (`-PrNf`).
- a "value" means an argument that does not start with a hyphen. The values for the sample command would be "3", "none" and "./example.txt"

```php
# Entire console command
cli()->input();                   // "xmcli -PrNf --no-cache --user=john -m 3 -T -S 'none' ./example.txt -aG"

# Check if an option is set
cli()->hasOption('x');            // false
cli()->hasOption('r');            // true
cli()->hasOption('G');            // true
cli()->hasOption('g');            // false

# Get the value for a specific option
# This finds the next value after the option has been set
cli()->optionValue('m');          // "3"
cli()->optionValue('T');          // ""
cli()->optionValue('S');          // "none"

# Fetches the last argument that does not start with "-"
cli()->lastValue();               // "./example.txt"

# Check named arguments - the hyphen can be omitted
cli()->arg('--no-cache');         // true
cli()->arg('no-cache');           // true
cli()->arg('--user');             // "john"
cli()->arg('user');               // "john"
```

## Related core functions

### argv()

Returns the plain arguments array (like `$argv` does).

### args()

Returns named arguments.

```php
# @todo write docs
```

### arg(...)

Returns value for named argument.

```php
arg('--no-cache');          // true
arg('no-cache');            // true
arg('--user');              // "john"
arg('user');                // "john"
```

### cli_options()

Returns cli options. Options are arguments that start with a single hypen, you can use 
a single argument (`-S -p -a -j`) for one option or specify multiple options in one
argument (`-Spaj`). The "value" for an option usually is an empty string.
But if the next argument after the option is a value (so, an argument that
does not begin with a hyphen), that value is assigned for this option(s).

```
[
    "P" => "",
    "r" => "",
    "N" => "",
    "f" => "",
    "m" => "3",
    "T" => "",
    "S" => "none",
    "a" => "",
    "G" => ""
]
```









