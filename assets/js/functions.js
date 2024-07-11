/**
 * Check if a variable is an object.
 *
 * @param {*} v - The variable to be checked.
 * @return {boolean} - Returns true if the variable is an object, false otherwise.
 */
const is_object = function (v) {
    return typeof v === 'object' && !Array.isArray(v) && v !== null;
}

/**
 * Function to check whether a given value is a string or not.
 *
 * @param {*} v - The value to be checked.
 * @returns {boolean} - True if the value is a string, false otherwise.
 */
const is_string = function (v) {
    return (typeof v === 'string' || v instanceof String);
}

/**
 * Checks if a variable is an array.
 *
 * @param {*} v - The variable to be checked.
 * @returns {boolean} - Returns true if the variable is an array, otherwise returns false.
 */
const is_array = function (v) {
    return Array.isArray(v);
}

/**
 * Determines whether a given value is a function or not.
 *
 * @param {*} v - The value to be checked.
 * @return {boolean} - Returns true if the given value is a function, otherwise returns false.
 */
const is_function = function (v) {
    return typeof v === 'function';
}

function stripAndCollapse(value) {
    var tokens = value.match(regexp_consecutive_whitespaces) || [];
    return tokens.join(" ");
}

/**
 * Converts the given value to a string. If the value is already a string, it is returned as is.
 * Otherwise, an empty string is returned.
 *
 * @param {*} value - The value to convert to a string.
 * @return {string} - The converted string value or an empty string.
 */
function str(value) {
    return is_string(value) ? value : "";
}

/**
 * Checks if a value is defined and not null.
 *
 * @param {*} value - The value to be checked.
 * @return {boolean} - Returns true if the value is defined and not null, otherwise returns false.
 */
function defined(value) {
    return value !== undefined && value !== null;
}

function get(value, key, default_value) {
    return is_object(value) ? value.prop(key, default_value) : (is_array(value) ? value.get(key, default_value) : default_value);
}

function classlist(string) {
    return stripAndCollapse(string.replaceAll(',', ' ').replaceAll('.', ' ').trim()).split(" ");
}

function strlist(string) {
    return stripAndCollapse(string.trim()).split(" ");
}