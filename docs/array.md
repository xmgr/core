# Array helpers

# Arr class

```php
# Get random item from array
Xmgr\Arr::randomItem($array);
# Get multiple items randomly
Xmgr\Arr::randomItems($array);
```

# Helper functions

```php
# This array is a sample for all of the following calls
$array = ["foo" => ["bar" => "baz"], "key" => "value", "xyz"];

# Get item from array by given key, or the default value if the key does not exist
arr($array, $key, $default);

# Get first or last array item
Xmgr\Arr::firstValue($array, $default);
Xmgr\Arr::lastValue($array, $default);

# Makes CSV string from array
Arr::toCsvString($array, $delimiter = ',', $enclosure = '"', $escape = "\\");

# Get or set array value via dot notation
array_get(array: $array, key: "foo.bar");       // "baz"
array_get(array: $array, key: "foo");           // ["bar" => "baz"]
array_get(array: $array, key: "key");           // "value"
array_get(array: $array, key: "hello", default: null);   // NULL
# Set:
data_set($array, "x", "y");        // Adds: ["x" => "y"]
data_set($array, "a.b", "c");      // Adds: ["a" => ["b" => "c"]]

# Get array item by index
array_index($array, 0)              // ["bar" => "baz"]
array_index($array, 1)              // "value"
array_index($array, 2)              // "xyz"
array_index($array, -1)             // "xyz"

# Check if all of the given keys are set in the array
Arr::hasAllKeys($array, ["key", "foo"]);             // TRUE
Arr::hasAllKeys($array, ["key", "foo", "hello"]);    // FALSE

# Returns an array containing only the picked items from the given array by key
array_pick($array, ["key", 0]);       // ["key" => "value", 0 => "xyz"]

# Get random item from array
array_random_value(array: $array);
# Get multiple items randomly
array_random_values(array: $array);

# Returns the item from the first key that exists in the array
array_find($array, ["abc", "xyz", "key", "hello"]); // Returns "value"

# Check if array contains the given string
Arr::containsString(array: $array, string: "ValuE", ignore_case: false); // FALSE
Arr::containsString(array: $array, string: "ValuE", ignore_case: true);  // TRUE

# Check if all array items match the given value or closure
Arr::allValuesMatch(array: $array, "test"); // FALSE
Arr::allValuesMatch(array: $array, function($value){
    return (is_array($value) || is_string($value));
}); // TRUE
```