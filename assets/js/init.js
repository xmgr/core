const regexp_consecutive_whitespaces = (/[^\x20\t\r\n\f]+/g);

/**
 * Selects one or multiple elements based on the given selector.
 *
 * @param {string|HTMLElement|NodeList} selector - The selector to select elements.
 * @return {array} - An array of selected elements.
 */
function $(selector) {
    
    let elements = [];
    if (typeof selector === "string") {
        elements = document.querySelectorAll(selector);
    } else if (selector instanceof HTMLElement) {
        elements = [selector];
    } else if (selector instanceof NodeList) {
        elements = selector;
    } else if (selector && is_object(selector) && selector instanceof $) {
        /* ... */
    } else if (selector && is_object(selector)) {
        elements = [selector];
    }
    
    /**
     * Checks if an element has a specific class.
     *
     * @returns {boolean} - True if the element has the class, otherwise false.
     * @param class_name
     */
    elements.hasClass = function (class_name) {
        let class_list = classlist(class_name);
        for (let i = 0; i < this.length; i++) {
            let el = this[i];
            for (current_class_name of class_list) {
                if (el.classList.contains(class_name)) {
                    return true;
                }
            }
        }
        return false;
    };
    
    /**
     * Adds one or more CSS classes to the elements in the collection.
     *
     * @returns {Object} - The elements collection after adding the CSS class(es).
     * @param class_name
     */
    elements.addClass = function (class_name) {
        let class_list = classlist(class_name);
        for (let i = 0; i < this.length; i++) {
            let el = this[i];
            for (current_class_name of class_list) {
                el.classList.add(current_class_name);
            }
        }
    }
    
    /**
     * Toggles the specified CSS class on each element in the given collection of elements.
     *
     * @param class_name
     */
    elements.toggleClass = function (class_name) {
        let class_list = classlist(class_name);
        for (let i = 0; i < this.length; i++) {
            let el = this[i];
            for (current_class_name of class_list) {
                el.classList.toggle(current_class_name);
            }
        }
        return this;
    }
    
    /**
     * Removes the specified CSS class(es) from all elements in the given collection.
     *
     * @returns {void}
     * @param class_name
     */
    elements.removeClass = function (class_name) {
        let class_list = classlist(class_name);
        for (let i = 0; i < this.length; i++) {
            let el = this[i];
            for (current_class_name of class_list) {
                el.classList.remove(current_class_name);
            }
        }
    }
    
    /**
     * Removes all occurrences of specified elements from the array.
     *
     * @returns {Array} - The modified array.
     *
     * @example
     * const array = [1, 2, 3, 2, 4];
     * elements.remove(array, 2, 3);
     * console.log(array); // [1, 4]
     */
    elements.remove = function () {
        for (let i = 0; i < this.length; i++) {
            if (this[i].parentNode) {
                this[i].parentNode.removeChild(this[i]);
            }
        }
        return this;
    };
    
    /**
     * Executes a function when the DOM is ready.
     *
     * @param {Function} callback - The function to be executed when the DOM is ready.
     */
    elements.ready = function (callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
        } else {
            // Wenn das DOM bereits geladen ist, Callback sofort ausführen
            callback();
        }
    };
    
    
    /**
     * Show the selected elements
     *
     * @returns {Object} - The elements collection after being shown
     */
    elements.show = function () {
        for (let i = 0; i < this.length; i++) {
            this[i].style.display = "";
        }
        return this;
    };
    
    /**
     * Hide the selected elements
     *
     * @returns {Object} - The elements collection after being hidden
     */
    elements.hide = function () {
        for (let i = 0; i < this.length; i++) {
            this[i].style.display = "none";
        }
        return this;
    };
    
    
    elements.toggle = function () {
        for (let i = 0; i < this.length; i++) {
            if (this[i].style.display === "none") {
                this[i].style.display = "";
            } else {
                this[i].style.display = "none";
            }
        }
        return this;
    };
    
    
    elements.text = function (content) {
        if (content !== undefined) {
            // Set the text of each element
            for (let i = 0; i < this.length; i++) {
                this[i].textContent = content;
            }
            return this;
        } else {
            // Return the text of the first element
            return this[0] ? this[0].textContent : '';
        }
    };
    
    elements.html = function (html) {
        if (html !== undefined) {
            // Set the HTML of each element
            for (let i = 0; i < this.length; i++) {
                this[i].innerHTML = html;
            }
            return this;
        } else {
            // Return the HTML of the first element
            return this[0] ? this[0].innerHTML : '';
        }
    };
    
    /**
     * Iterates over each element in the given array.
     *
     * @param {function} callback - The function to be executed for each element.
     */
    elements.each = function (callback) {
        for (let i = 0; i < this.length; i++) {
            callback.call(this[i], i, this[i]);
        }
        return this;
    };
    
    
    /**
     * Retrieves element value
     *
     * @return {Array} - An array containing the values of the elements.
     * @param value
     */
    elements.val = function (value) {
        if (value !== undefined) {
            // Set the value of each element
            for (let i = 0; i < this.length; i++) {
                if (this[i].value !== undefined) {
                    this[i].value = value;
                }
            }
            return this;
        } else {
            // Return the value of the first element
            return this[0] ? this[0].value : '';
        }
    };
    
    elements.attr = function (attr_name, attr_value) {
        if (attr_value !== undefined) {
            for (let i = 0; i < this.length; i++) {
                this[i].setAttribute(attr_name, attr_value);
            }
            return this;
        } else {
            return this[0] ? this[0].getAttribute(attr_name) : '';
        }
    };
    
    elements.toggleAttr = function (attr_name) {
        for (let i = 0; i < this.length; i++) {
            if (this[i].hasAttribute(attr_name)) {
                this[i].removeAttribute(attr_name);
            } else {
                this[i].setAttribute(attr_name, "");
            }
        }
        return this;
    };
    
    elements.prop = function (propName, propValue) {
        if (propValue !== undefined) {
            // Set the property value of each element
            for (let i = 0; i < this.length; i++) {
                if (this[i][propName] !== undefined) {
                    this[i][propName] = propValue;
                }
            }
            return this;
        } else {
            // Return the property value of the first element
            return this[0] ? this[0][propName] : undefined;
        }
    };
    
    elements.scrollTop = function (value) {
        if (value !== undefined) {
            // Set the scrollTop of each element
            for (let i = 0; i < this.length; i++) {
                this[i].scrollTop = value;
            }
        } else {
            // Return the scrollTop of the first element
            return this[0] ? this[0].scrollTop : undefined;
        }
    };
    
    // on-Funktion zum Erstellen von event listenern
    /**
     * Attaches an event handler to the specified elements.
     *
     * @param eventName
     * @param callback
     */
    elements.on = function (eventName, callback) {
        for (let i = 0; i < this.length; i++) {
            this[i].addEventListener(eventName, callback);
        }
        return this;
    };
    
    return elements;
}

$.ajax = async function (url, options = {}) {
    let default_options = {
        method: 'GET',
        redirect: 'follow',
        headers: {
            // 'Content-Type',
            'Accept': '*/*',
        }
    };
    console.log(default_options.merge(options));
    return fetch(url, default_options.merge(options));
}

$.get = async function (url, options = {}) {
    let default_options = {
        method: 'GET'
    };
    return await $.ajax(url, default_options.merge(options));
}

$.post = async function (url, body, options = {}) {
    let default_options = {
        method: 'POST',
        body: body
    };
    return await $.ajax(url, default_options.merge(options));
}

$.gettext = async function (url) {
    let response = await fetch(url);
    return await response.text();
}


$('form.ajax').on('submit', function (e) {
    e.preventDefault();
    let url = $(this).attr('action') || window.location.pathname;
    let body = new FormData(this);
    $.post(url, body)
        .then((response) => {
            return response.text();
        })
        .then((data) => {
                console.log(data);
            }
        );
});

$(document).ready(function () {
    console.log("- document ready -");
});
