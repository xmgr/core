/**
 * Merges the properties of one or more objects with the current object.
 *
 * @param {...Object} objects - The objects to merge.
 * @returns {Object} - The merged object.
 */
Object.prototype.merge = function () {
    var result = {};
    if (is_object(this)) {
        result = this;
    }
    for (var i = 0; i < arguments.length; i++) {
        var obj = arguments[i];
        for (var prop in obj) {
            if (obj.hasOwnProperty(prop)) {
                if (is_object(obj[prop])) {
                    result[prop] = obj[prop];
                } else {
                    // Andernfalls den Wert aus dem aktuellen Objekt verwenden
                    result[prop] = obj[prop];
                }
            }
        }
    }
    return result;
}

/**
 * @description The `prop` property decalration for all objects.
 * @memberOf Object.prototype
 */
Object.prototype.prop = function (name, default_value) {
    return (Object.hasOwn(this, name) ? this[name] : default_value);
}