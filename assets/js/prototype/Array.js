Array.prototype.get = function (key, default_value) {
    return key in this ? this[key] : default_value;
}