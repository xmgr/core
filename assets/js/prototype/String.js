String.prototype.collapseWS = function () {
    let tokens = value.match(regexp_consecutive_whitespaces) || [];
    return tokens.join(" ");
};

String.prototype.collapse = function (chars) {
    let tokens = Array.from(this);
    return tokens.join(" ");
};