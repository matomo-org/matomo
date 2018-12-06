/*!
 * Piwik - free/libre analytics platform
 *
 * JavaScript tracking client
 *
 * @link https://piwik.org
 * @source https://github.com/matomo-org/matomo/blob/master/js/piwik.js
 * @license https://piwik.org/free-software/bsd/ BSD-3 Clause (also in js/LICENSE.txt)
 * @license magnet:?xt=urn:btih:c80d50af7d3db9be66a4d0a86db0286e4fd33292&dn=bsd-3-clause.txt BSD-3-Clause
 */
// NOTE: if you change this above Piwik comment block, you must also change `$byteStart` in js/tracker.php

// Refer to README.md for build instructions when minifying this file for distribution.

/*
 * Browser [In]Compatibility
 * - minimum required ECMAScript: ECMA-262, edition 3
 *
 * Incompatible with these (and earlier) versions of:
 * - IE4 - try..catch and for..in introduced in IE5
 * - IE5 - named anonymous functions, array.push, encodeURIComponent, decodeURIComponent, and getElementsByTagName introduced in IE5.5
 * - Firefox 1.0 and Netscape 8.x - FF1.5 adds array.indexOf, among other things
 * - Mozilla 1.7 and Netscape 6.x-7.x
 * - Netscape 4.8
 * - Opera 6 - Error object (and Presto) introduced in Opera 7
 * - Opera 7
 */

/*global JSON_PIWIK:true */

if (typeof JSON_PIWIK !== 'object' && typeof window.JSON === 'object' && window.JSON.stringify && window.JSON.parse) {
    JSON_PIWIK = window.JSON;
} else {
    (function () {
        // we make sure to not break any site that uses JSON3 as well as we do not know if they run it in conflict mode
        // or not.
        var exports = {};

        // Create a JSON object only if one does not already exist. We create the
        // methods in a closure to avoid creating global variables.

        /*! JSON v3.3.2 | http://bestiejs.github.io/json3 | Copyright 2012-2014, Kit Cambridge | http://kit.mit-license.org */
        (function () {
            // Detect the `define` function exposed by asynchronous module loaders. The
            // strict `define` check is necessary for compatibility with `r.js`.
            var isLoader = typeof define === "function" && define.amd;

            // A set of types used to distinguish objects from primitives.
            var objectTypes = {
                "function": true,
                "object": true
            };

            // Detect the `exports` object exposed by CommonJS implementations.
            var freeExports = objectTypes[typeof exports] && exports && !exports.nodeType && exports;

            // Use the `global` object exposed by Node (including Browserify via
            // `insert-module-globals`), Narwhal, and Ringo as the default context,
            // and the `window` object in browsers. Rhino exports a `global` function
            // instead.
            var root = objectTypes[typeof window] && window || this,
                freeGlobal = freeExports && objectTypes[typeof module] && module && !module.nodeType && typeof global == "object" && global;

            if (freeGlobal && (freeGlobal["global"] === freeGlobal || freeGlobal["window"] === freeGlobal || freeGlobal["self"] === freeGlobal)) {
                root = freeGlobal;
            }

            // Public: Initializes JSON 3 using the given `context` object, attaching the
            // `stringify` and `parse` functions to the specified `exports` object.
            function runInContext(context, exports) {
                context || (context = root["Object"]());
                exports || (exports = root["Object"]());

                // Native constructor aliases.
                var Number = context["Number"] || root["Number"],
                    String = context["String"] || root["String"],
                    Object = context["Object"] || root["Object"],
                    Date = context["Date"] || root["Date"],
                    SyntaxError = context["SyntaxError"] || root["SyntaxError"],
                    TypeError = context["TypeError"] || root["TypeError"],
                    Math = context["Math"] || root["Math"],
                    nativeJSON = context["JSON"] || root["JSON"];

                // Delegate to the native `stringify` and `parse` implementations.
                if (typeof nativeJSON == "object" && nativeJSON) {
                    exports.stringify = nativeJSON.stringify;
                    exports.parse = nativeJSON.parse;
                }

                // Convenience aliases.
                var objectProto = Object.prototype,
                    getClass = objectProto.toString,
                    isProperty, forEach, undef;

                // Test the `Date#getUTC*` methods. Based on work by @Yaffle.
                var isExtended = new Date(-3509827334573292);
                try {
                    // The `getUTCFullYear`, `Month`, and `Date` methods return nonsensical
                    // results for certain dates in Opera >= 10.53.
                    isExtended = isExtended.getUTCFullYear() == -109252 && isExtended.getUTCMonth() === 0 && isExtended.getUTCDate() === 1 &&
                        // Safari < 2.0.2 stores the internal millisecond time value correctly,
                        // but clips the values returned by the date methods to the range of
                        // signed 32-bit integers ([-2 ** 31, 2 ** 31 - 1]).
                        isExtended.getUTCHours() == 10 && isExtended.getUTCMinutes() == 37 && isExtended.getUTCSeconds() == 6 && isExtended.getUTCMilliseconds() == 708;
                } catch (exception) {}

                // Internal: Determines whether the native `JSON.stringify` and `parse`
                // implementations are spec-compliant. Based on work by Ken Snyder.
                function has(name) {
                    if (has[name] !== undef) {
                        // Return cached feature test result.
                        return has[name];
                    }
                    var isSupported;
                    if (name == "bug-string-char-index") {
                        // IE <= 7 doesn't support accessing string characters using square
                        // bracket notation. IE 8 only supports this for primitives.
                        isSupported = "a"[0] != "a";
                    } else if (name == "json") {
                        // Indicates whether both `JSON.stringify` and `JSON.parse` are
                        // supported.
                        isSupported = has("json-stringify") && has("json-parse");
                    } else {
                        var value, serialized = '{"a":[1,true,false,null,"\\u0000\\b\\n\\f\\r\\t"]}';
                        // Test `JSON.stringify`.
                        if (name == "json-stringify") {
                            var stringify = exports.stringify, stringifySupported = typeof stringify == "function" && isExtended;
                            if (stringifySupported) {
                                // A test function object with a custom `toJSON` method.
                                (value = function () {
                                    return 1;
                                }).toJSON = value;
                                try {
                                    stringifySupported =
                                        // Firefox 3.1b1 and b2 serialize string, number, and boolean
                                        // primitives as object literals.
                                        stringify(0) === "0" &&
                                        // FF 3.1b1, b2, and JSON 2 serialize wrapped primitives as object
                                        // literals.
                                        stringify(new Number()) === "0" &&
                                        stringify(new String()) == '""' &&
                                        // FF 3.1b1, 2 throw an error if the value is `null`, `undefined`, or
                                        // does not define a canonical JSON representation (this applies to
                                        // objects with `toJSON` properties as well, *unless* they are nested
                                        // within an object or array).
                                        stringify(getClass) === undef &&
                                        // IE 8 serializes `undefined` as `"undefined"`. Safari <= 5.1.7 and
                                        // FF 3.1b3 pass this test.
                                        stringify(undef) === undef &&
                                        // Safari <= 5.1.7 and FF 3.1b3 throw `Error`s and `TypeError`s,
                                        // respectively, if the value is omitted entirely.
                                        stringify() === undef &&
                                        // FF 3.1b1, 2 throw an error if the given value is not a number,
                                        // string, array, object, Boolean, or `null` literal. This applies to
                                        // objects with custom `toJSON` methods as well, unless they are nested
                                        // inside object or array literals. YUI 3.0.0b1 ignores custom `toJSON`
                                        // methods entirely.
                                        stringify(value) === "1" &&
                                        stringify([value]) == "[1]" &&
                                        // Prototype <= 1.6.1 serializes `[undefined]` as `"[]"` instead of
                                        // `"[null]"`.
                                        stringify([undef]) == "[null]" &&
                                        // YUI 3.0.0b1 fails to serialize `null` literals.
                                        stringify(null) == "null" &&
                                        // FF 3.1b1, 2 halts serialization if an array contains a function:
                                        // `[1, true, getClass, 1]` serializes as "[1,true,],". FF 3.1b3
                                        // elides non-JSON values from objects and arrays, unless they
                                        // define custom `toJSON` methods.
                                        stringify([undef, getClass, null]) == "[null,null,null]" &&
                                        // Simple serialization test. FF 3.1b1 uses Unicode escape sequences
                                        // where character escape codes are expected (e.g., `\b` => `\u0008`).
                                        stringify({ "a": [value, true, false, null, "\x00\b\n\f\r\t"] }) == serialized &&
                                        // FF 3.1b1 and b2 ignore the `filter` and `width` arguments.
                                        stringify(null, value) === "1" &&
                                        stringify([1, 2], null, 1) == "[\n 1,\n 2\n]" &&
                                        // JSON 2, Prototype <= 1.7, and older WebKit builds incorrectly
                                        // serialize extended years.
                                        stringify(new Date(-8.64e15)) == '"-271821-04-20T00:00:00.000Z"' &&
                                        // The milliseconds are optional in ES 5, but required in 5.1.
                                        stringify(new Date(8.64e15)) == '"+275760-09-13T00:00:00.000Z"' &&
                                        // Firefox <= 11.0 incorrectly serializes years prior to 0 as negative
                                        // four-digit years instead of six-digit years. Credits: @Yaffle.
                                        stringify(new Date(-621987552e5)) == '"-000001-01-01T00:00:00.000Z"' &&
                                        // Safari <= 5.1.5 and Opera >= 10.53 incorrectly serialize millisecond
                                        // values less than 1000. Credits: @Yaffle.
                                        stringify(new Date(-1)) == '"1969-12-31T23:59:59.999Z"';
                                } catch (exception) {
                                    stringifySupported = false;
                                }
                            }
                            isSupported = stringifySupported;
                        }
                        // Test `JSON.parse`.
                        if (name == "json-parse") {
                            var parse = exports.parse;
                            if (typeof parse == "function") {
                                try {
                                    // FF 3.1b1, b2 will throw an exception if a bare literal is provided.
                                    // Conforming implementations should also coerce the initial argument to
                                    // a string prior to parsing.
                                    if (parse("0") === 0 && !parse(false)) {
                                        // Simple parsing test.
                                        value = parse(serialized);
                                        var parseSupported = value["a"].length == 5 && value["a"][0] === 1;
                                        if (parseSupported) {
                                            try {
                                                // Safari <= 5.1.2 and FF 3.1b1 allow unescaped tabs in strings.
                                                parseSupported = !parse('"\t"');
                                            } catch (exception) {}
                                            if (parseSupported) {
                                                try {
                                                    // FF 4.0 and 4.0.1 allow leading `+` signs and leading
                                                    // decimal points. FF 4.0, 4.0.1, and IE 9-10 also allow
                                                    // certain octal literals.
                                                    parseSupported = parse("01") !== 1;
                                                } catch (exception) {}
                                            }
                                            if (parseSupported) {
                                                try {
                                                    // FF 4.0, 4.0.1, and Rhino 1.7R3-R4 allow trailing decimal
                                                    // points. These environments, along with FF 3.1b1 and 2,
                                                    // also allow trailing commas in JSON objects and arrays.
                                                    parseSupported = parse("1.") !== 1;
                                                } catch (exception) {}
                                            }
                                        }
                                    }
                                } catch (exception) {
                                    parseSupported = false;
                                }
                            }
                            isSupported = parseSupported;
                        }
                    }
                    return has[name] = !!isSupported;
                }

                if (!has("json")) {
                    // Common `[[Class]]` name aliases.
                    var functionClass = "[object Function]",
                        dateClass = "[object Date]",
                        numberClass = "[object Number]",
                        stringClass = "[object String]",
                        arrayClass = "[object Array]",
                        booleanClass = "[object Boolean]";

                    // Detect incomplete support for accessing string characters by index.
                    var charIndexBuggy = has("bug-string-char-index");

                    // Define additional utility methods if the `Date` methods are buggy.
                    if (!isExtended) {
                        var floor = Math.floor;
                        // A mapping between the months of the year and the number of days between
                        // January 1st and the first of the respective month.
                        var Months = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
                        // Internal: Calculates the number of days between the Unix epoch and the
                        // first day of the given month.
                        var getDay = function (year, month) {
                            return Months[month] + 365 * (year - 1970) + floor((year - 1969 + (month = +(month > 1))) / 4) - floor((year - 1901 + month) / 100) + floor((year - 1601 + month) / 400);
                        };
                    }

                    // Internal: Determines if a property is a direct property of the given
                    // object. Delegates to the native `Object#hasOwnProperty` method.
                    if (!(isProperty = objectProto.hasOwnProperty)) {
                        isProperty = function (property) {
                            var members = {}, constructor;
                            if ((members.__proto__ = null, members.__proto__ = {
                                    // The *proto* property cannot be set multiple times in recent
                                    // versions of Firefox and SeaMonkey.
                                    "toString": 1
                                }, members).toString != getClass) {
                                // Safari <= 2.0.3 doesn't implement `Object#hasOwnProperty`, but
                                // supports the mutable *proto* property.
                                isProperty = function (property) {
                                    // Capture and break the object's prototype chain (see section 8.6.2
                                    // of the ES 5.1 spec). The parenthesized expression prevents an
                                    // unsafe transformation by the Closure Compiler.
                                    var original = this.__proto__, result = property in (this.__proto__ = null, this);
                                    // Restore the original prototype chain.
                                    this.__proto__ = original;
                                    return result;
                                };
                            } else {
                                // Capture a reference to the top-level `Object` constructor.
                                constructor = members.constructor;
                                // Use the `constructor` property to simulate `Object#hasOwnProperty` in
                                // other environments.
                                isProperty = function (property) {
                                    var parent = (this.constructor || constructor).prototype;
                                    return property in this && !(property in parent && this[property] === parent[property]);
                                };
                            }
                            members = null;
                            return isProperty.call(this, property);
                        };
                    }

                    // Internal: Normalizes the `for...in` iteration algorithm across
                    // environments. Each enumerated key is yielded to a `callback` function.
                    forEach = function (object, callback) {
                        var size = 0, Properties, members, property;

                        // Tests for bugs in the current environment's `for...in` algorithm. The
                        // `valueOf` property inherits the non-enumerable flag from
                        // `Object.prototype` in older versions of IE, Netscape, and Mozilla.
                        (Properties = function () {
                            this.valueOf = 0;
                        }).prototype.valueOf = 0;

                        // Iterate over a new instance of the `Properties` class.
                        members = new Properties();
                        for (property in members) {
                            // Ignore all properties inherited from `Object.prototype`.
                            if (isProperty.call(members, property)) {
                                size++;
                            }
                        }
                        Properties = members = null;

                        // Normalize the iteration algorithm.
                        if (!size) {
                            // A list of non-enumerable properties inherited from `Object.prototype`.
                            members = ["valueOf", "toString", "toLocaleString", "propertyIsEnumerable", "isPrototypeOf", "hasOwnProperty", "constructor"];
                            // IE <= 8, Mozilla 1.0, and Netscape 6.2 ignore shadowed non-enumerable
                            // properties.
                            forEach = function (object, callback) {
                                var isFunction = getClass.call(object) == functionClass, property, length;
                                var hasProperty = !isFunction && typeof object.constructor != "function" && objectTypes[typeof object.hasOwnProperty] && object.hasOwnProperty || isProperty;
                                for (property in object) {
                                    // Gecko <= 1.0 enumerates the `prototype` property of functions under
                                    // certain conditions; IE does not.
                                    if (!(isFunction && property == "prototype") && hasProperty.call(object, property)) {
                                        callback(property);
                                    }
                                }
                                // Manually invoke the callback for each non-enumerable property.
                                for (length = members.length; property = members[--length]; hasProperty.call(object, property) && callback(property));
                            };
                        } else if (size == 2) {
                            // Safari <= 2.0.4 enumerates shadowed properties twice.
                            forEach = function (object, callback) {
                                // Create a set of iterated properties.
                                var members = {}, isFunction = getClass.call(object) == functionClass, property;
                                for (property in object) {
                                    // Store each property name to prevent double enumeration. The
                                    // `prototype` property of functions is not enumerated due to cross-
                                    // environment inconsistencies.
                                    if (!(isFunction && property == "prototype") && !isProperty.call(members, property) && (members[property] = 1) && isProperty.call(object, property)) {
                                        callback(property);
                                    }
                                }
                            };
                        } else {
                            // No bugs detected; use the standard `for...in` algorithm.
                            forEach = function (object, callback) {
                                var isFunction = getClass.call(object) == functionClass, property, isConstructor;
                                for (property in object) {
                                    if (!(isFunction && property == "prototype") && isProperty.call(object, property) && !(isConstructor = property === "constructor")) {
                                        callback(property);
                                    }
                                }
                                // Manually invoke the callback for the `constructor` property due to
                                // cross-environment inconsistencies.
                                if (isConstructor || isProperty.call(object, (property = "constructor"))) {
                                    callback(property);
                                }
                            };
                        }
                        return forEach(object, callback);
                    };

                    // Public: Serializes a JavaScript `value` as a JSON string. The optional
                    // `filter` argument may specify either a function that alters how object and
                    // array members are serialized, or an array of strings and numbers that
                    // indicates which properties should be serialized. The optional `width`
                    // argument may be either a string or number that specifies the indentation
                    // level of the output.
                    if (!has("json-stringify")) {
                        // Internal: A map of control characters and their escaped equivalents.
                        var Escapes = {
                            92: "\\\\",
                            34: '\\"',
                            8: "\\b",
                            12: "\\f",
                            10: "\\n",
                            13: "\\r",
                            9: "\\t"
                        };

                        // Internal: Converts `value` into a zero-padded string such that its
                        // length is at least equal to `width`. The `width` must be <= 6.
                        var leadingZeroes = "000000";
                        var toPaddedString = function (width, value) {
                            // The `|| 0` expression is necessary to work around a bug in
                            // Opera <= 7.54u2 where `0 == -0`, but `String(-0) !== "0"`.
                            return (leadingZeroes + (value || 0)).slice(-width);
                        };

                        // Internal: Double-quotes a string `value`, replacing all ASCII control
                        // characters (characters with code unit values between 0 and 31) with
                        // their escaped equivalents. This is an implementation of the
                        // `Quote(value)` operation defined in ES 5.1 section 15.12.3.
                        var unicodePrefix = "\\u00";
                        var quote = function (value) {
                            var result = '"', index = 0, length = value.length, useCharIndex = !charIndexBuggy || length > 10;
                            var symbols = useCharIndex && (charIndexBuggy ? value.split("") : value);
                            for (; index < length; index++) {
                                var charCode = value.charCodeAt(index);
                                // If the character is a control character, append its Unicode or
                                // shorthand escape sequence; otherwise, append the character as-is.
                                switch (charCode) {
                                    case 8: case 9: case 10: case 12: case 13: case 34: case 92:
                                    result += Escapes[charCode];
                                    break;
                                    default:
                                        if (charCode < 32) {
                                            result += unicodePrefix + toPaddedString(2, charCode.toString(16));
                                            break;
                                        }
                                        result += useCharIndex ? symbols[index] : value.charAt(index);
                                }
                            }
                            return result + '"';
                        };

                        // Internal: Recursively serializes an object. Implements the
                        // `Str(key, holder)`, `JO(value)`, and `JA(value)` operations.
                        var serialize = function (property, object, callback, properties, whitespace, indentation, stack) {
                            var value, className, year, month, date, time, hours, minutes, seconds, milliseconds, results, element, index, length, prefix, result;
                            try {
                                // Necessary for host object support.
                                value = object[property];
                            } catch (exception) {}
                            if (typeof value == "object" && value) {
                                className = getClass.call(value);
                                if (className == dateClass && !isProperty.call(value, "toJSON")) {
                                    if (value > -1 / 0 && value < 1 / 0) {
                                        // Dates are serialized according to the `Date#toJSON` method
                                        // specified in ES 5.1 section 15.9.5.44. See section 15.9.1.15
                                        // for the ISO 8601 date time string format.
                                        if (getDay) {
                                            // Manually compute the year, month, date, hours, minutes,
                                            // seconds, and milliseconds if the `getUTC*` methods are
                                            // buggy. Adapted from @Yaffle's `date-shim` project.
                                            date = floor(value / 864e5);
                                            for (year = floor(date / 365.2425) + 1970 - 1; getDay(year + 1, 0) <= date; year++);
                                            for (month = floor((date - getDay(year, 0)) / 30.42); getDay(year, month + 1) <= date; month++);
                                            date = 1 + date - getDay(year, month);
                                            // The `time` value specifies the time within the day (see ES
                                            // 5.1 section 15.9.1.2). The formula `(A % B + B) % B` is used
                                            // to compute `A modulo B`, as the `%` operator does not
                                            // correspond to the `modulo` operation for negative numbers.
                                            time = (value % 864e5 + 864e5) % 864e5;
                                            // The hours, minutes, seconds, and milliseconds are obtained by
                                            // decomposing the time within the day. See section 15.9.1.10.
                                            hours = floor(time / 36e5) % 24;
                                            minutes = floor(time / 6e4) % 60;
                                            seconds = floor(time / 1e3) % 60;
                                            milliseconds = time % 1e3;
                                        } else {
                                            year = value.getUTCFullYear();
                                            month = value.getUTCMonth();
                                            date = value.getUTCDate();
                                            hours = value.getUTCHours();
                                            minutes = value.getUTCMinutes();
                                            seconds = value.getUTCSeconds();
                                            milliseconds = value.getUTCMilliseconds();
                                        }
                                        // Serialize extended years correctly.
                                        value = (year <= 0 || year >= 1e4 ? (year < 0 ? "-" : "+") + toPaddedString(6, year < 0 ? -year : year) : toPaddedString(4, year)) +
                                            "-" + toPaddedString(2, month + 1) + "-" + toPaddedString(2, date) +
                                            // Months, dates, hours, minutes, and seconds should have two
                                            // digits; milliseconds should have three.
                                            "T" + toPaddedString(2, hours) + ":" + toPaddedString(2, minutes) + ":" + toPaddedString(2, seconds) +
                                            // Milliseconds are optional in ES 5.0, but required in 5.1.
                                            "." + toPaddedString(3, milliseconds) + "Z";
                                    } else {
                                        value = null;
                                    }
                                } else if (typeof value.toJSON == "function" && ((className != numberClass && className != stringClass && className != arrayClass) || isProperty.call(value, "toJSON"))) {
                                    // Prototype <= 1.6.1 adds non-standard `toJSON` methods to the
                                    // `Number`, `String`, `Date`, and `Array` prototypes. JSON 3
                                    // ignores all `toJSON` methods on these objects unless they are
                                    // defined directly on an instance.
                                    value = value.toJSON(property);
                                }
                            }
                            if (callback) {
                                // If a replacement function was provided, call it to obtain the value
                                // for serialization.
                                value = callback.call(object, property, value);
                            }
                            if (value === null) {
                                return "null";
                            }
                            className = getClass.call(value);
                            if (className == booleanClass) {
                                // Booleans are represented literally.
                                return "" + value;
                            } else if (className == numberClass) {
                                // JSON numbers must be finite. `Infinity` and `NaN` are serialized as
                                // `"null"`.
                                return value > -1 / 0 && value < 1 / 0 ? "" + value : "null";
                            } else if (className == stringClass) {
                                // Strings are double-quoted and escaped.
                                return quote("" + value);
                            }
                            // Recursively serialize objects and arrays.
                            if (typeof value == "object") {
                                // Check for cyclic structures. This is a linear search; performance
                                // is inversely proportional to the number of unique nested objects.
                                for (length = stack.length; length--;) {
                                    if (stack[length] === value) {
                                        // Cyclic structures cannot be serialized by `JSON.stringify`.
                                        throw TypeError();
                                    }
                                }
                                // Add the object to the stack of traversed objects.
                                stack.push(value);
                                results = [];
                                // Save the current indentation level and indent one additional level.
                                prefix = indentation;
                                indentation += whitespace;
                                if (className == arrayClass) {
                                    // Recursively serialize array elements.
                                    for (index = 0, length = value.length; index < length; index++) {
                                        element = serialize(index, value, callback, properties, whitespace, indentation, stack);
                                        results.push(element === undef ? "null" : element);
                                    }
                                    result = results.length ? (whitespace ? "[\n" + indentation + results.join(",\n" + indentation) + "\n" + prefix + "]" : ("[" + results.join(",") + "]")) : "[]";
                                } else {
                                    // Recursively serialize object members. Members are selected from
                                    // either a user-specified list of property names, or the object
                                    // itself.
                                    forEach(properties || value, function (property) {
                                        var element = serialize(property, value, callback, properties, whitespace, indentation, stack);
                                        if (element !== undef) {
                                            // According to ES 5.1 section 15.12.3: "If `gap` {whitespace}
                                            // is not the empty string, let `member` {quote(property) + ":"}
                                            // be the concatenation of `member` and the `space` character."
                                            // The "`space` character" refers to the literal space
                                            // character, not the `space` {width} argument provided to
                                            // `JSON.stringify`.
                                            results.push(quote(property) + ":" + (whitespace ? " " : "") + element);
                                        }
                                    });
                                    result = results.length ? (whitespace ? "{\n" + indentation + results.join(",\n" + indentation) + "\n" + prefix + "}" : ("{" + results.join(",") + "}")) : "{}";
                                }
                                // Remove the object from the traversed object stack.
                                stack.pop();
                                return result;
                            }
                        };

                        // Public: `JSON.stringify`. See ES 5.1 section 15.12.3.
                        exports.stringify = function (source, filter, width) {
                            var whitespace, callback, properties, className;
                            if (objectTypes[typeof filter] && filter) {
                                if ((className = getClass.call(filter)) == functionClass) {
                                    callback = filter;
                                } else if (className == arrayClass) {
                                    // Convert the property names array into a makeshift set.
                                    properties = {};
                                    for (var index = 0, length = filter.length, value; index < length; value = filter[index++], ((className = getClass.call(value)), className == stringClass || className == numberClass) && (properties[value] = 1));
                                }
                            }
                            if (width) {
                                if ((className = getClass.call(width)) == numberClass) {
                                    // Convert the `width` to an integer and create a string containing
                                    // `width` number of space characters.
                                    if ((width -= width % 1) > 0) {
                                        for (whitespace = "", width > 10 && (width = 10); whitespace.length < width; whitespace += " ");
                                    }
                                } else if (className == stringClass) {
                                    whitespace = width.length <= 10 ? width : width.slice(0, 10);
                                }
                            }
                            // Opera <= 7.54u2 discards the values associated with empty string keys
                            // (`""`) only if they are used directly within an object member list
                            // (e.g., `!("" in { "": 1})`).
                            return serialize("", (value = {}, value[""] = source, value), callback, properties, whitespace, "", []);
                        };
                    }

                    // Public: Parses a JSON source string.
                    if (!has("json-parse")) {
                        var fromCharCode = String.fromCharCode;

                        // Internal: A map of escaped control characters and their unescaped
                        // equivalents.
                        var Unescapes = {
                            92: "\\",
                            34: '"',
                            47: "/",
                            98: "\b",
                            116: "\t",
                            110: "\n",
                            102: "\f",
                            114: "\r"
                        };

                        // Internal: Stores the parser state.
                        var Index, Source;

                        // Internal: Resets the parser state and throws a `SyntaxError`.
                        var abort = function () {
                            Index = Source = null;
                            throw SyntaxError();
                        };

                        // Internal: Returns the next token, or `"$"` if the parser has reached
                        // the end of the source string. A token may be a string, number, `null`
                        // literal, or Boolean literal.
                        var lex = function () {
                            var source = Source, length = source.length, value, begin, position, isSigned, charCode;
                            while (Index < length) {
                                charCode = source.charCodeAt(Index);
                                switch (charCode) {
                                    case 9: case 10: case 13: case 32:
                                    // Skip whitespace tokens, including tabs, carriage returns, line
                                    // feeds, and space characters.
                                    Index++;
                                    break;
                                    case 123: case 125: case 91: case 93: case 58: case 44:
                                    // Parse a punctuator token (`{`, `}`, `[`, `]`, `:`, or `,`) at
                                    // the current position.
                                    value = charIndexBuggy ? source.charAt(Index) : source[Index];
                                    Index++;
                                    return value;
                                    case 34:
                                        // `"` delimits a JSON string; advance to the next character and
                                        // begin parsing the string. String tokens are prefixed with the
                                        // sentinel `@` character to distinguish them from punctuators and
                                        // end-of-string tokens.
                                        for (value = "@", Index++; Index < length;) {
                                            charCode = source.charCodeAt(Index);
                                            if (charCode < 32) {
                                                // Unescaped ASCII control characters (those with a code unit
                                                // less than the space character) are not permitted.
                                                abort();
                                            } else if (charCode == 92) {
                                                // A reverse solidus (`\`) marks the beginning of an escaped
                                                // control character (including `"`, `\`, and `/`) or Unicode
                                                // escape sequence.
                                                charCode = source.charCodeAt(++Index);
                                                switch (charCode) {
                                                    case 92: case 34: case 47: case 98: case 116: case 110: case 102: case 114:
                                                    // Revive escaped control characters.
                                                    value += Unescapes[charCode];
                                                    Index++;
                                                    break;
                                                    case 117:
                                                        // `\u` marks the beginning of a Unicode escape sequence.
                                                        // Advance to the first character and validate the
                                                        // four-digit code point.
                                                        begin = ++Index;
                                                        for (position = Index + 4; Index < position; Index++) {
                                                            charCode = source.charCodeAt(Index);
                                                            // A valid sequence comprises four hexdigits (case-
                                                            // insensitive) that form a single hexadecimal value.
                                                            if (!(charCode >= 48 && charCode <= 57 || charCode >= 97 && charCode <= 102 || charCode >= 65 && charCode <= 70)) {
                                                                // Invalid Unicode escape sequence.
                                                                abort();
                                                            }
                                                        }
                                                        // Revive the escaped character.
                                                        value += fromCharCode("0x" + source.slice(begin, Index));
                                                        break;
                                                    default:
                                                        // Invalid escape sequence.
                                                        abort();
                                                }
                                            } else {
                                                if (charCode == 34) {
                                                    // An unescaped double-quote character marks the end of the
                                                    // string.
                                                    break;
                                                }
                                                charCode = source.charCodeAt(Index);
                                                begin = Index;
                                                // Optimize for the common case where a string is valid.
                                                while (charCode >= 32 && charCode != 92 && charCode != 34) {
                                                    charCode = source.charCodeAt(++Index);
                                                }
                                                // Append the string as-is.
                                                value += source.slice(begin, Index);
                                            }
                                        }
                                        if (source.charCodeAt(Index) == 34) {
                                            // Advance to the next character and return the revived string.
                                            Index++;
                                            return value;
                                        }
                                        // Unterminated string.
                                        abort();
                                    default:
                                        // Parse numbers and literals.
                                        begin = Index;
                                        // Advance past the negative sign, if one is specified.
                                        if (charCode == 45) {
                                            isSigned = true;
                                            charCode = source.charCodeAt(++Index);
                                        }
                                        // Parse an integer or floating-point value.
                                        if (charCode >= 48 && charCode <= 57) {
                                            // Leading zeroes are interpreted as octal literals.
                                            if (charCode == 48 && ((charCode = source.charCodeAt(Index + 1)), charCode >= 48 && charCode <= 57)) {
                                                // Illegal octal literal.
                                                abort();
                                            }
                                            isSigned = false;
                                            // Parse the integer component.
                                            for (; Index < length && ((charCode = source.charCodeAt(Index)), charCode >= 48 && charCode <= 57); Index++);
                                            // Floats cannot contain a leading decimal point; however, this
                                            // case is already accounted for by the parser.
                                            if (source.charCodeAt(Index) == 46) {
                                                position = ++Index;
                                                // Parse the decimal component.
                                                for (; position < length && ((charCode = source.charCodeAt(position)), charCode >= 48 && charCode <= 57); position++);
                                                if (position == Index) {
                                                    // Illegal trailing decimal.
                                                    abort();
                                                }
                                                Index = position;
                                            }
                                            // Parse exponents. The `e` denoting the exponent is
                                            // case-insensitive.
                                            charCode = source.charCodeAt(Index);
                                            if (charCode == 101 || charCode == 69) {
                                                charCode = source.charCodeAt(++Index);
                                                // Skip past the sign following the exponent, if one is
                                                // specified.
                                                if (charCode == 43 || charCode == 45) {
                                                    Index++;
                                                }
                                                // Parse the exponential component.
                                                for (position = Index; position < length && ((charCode = source.charCodeAt(position)), charCode >= 48 && charCode <= 57); position++);
                                                if (position == Index) {
                                                    // Illegal empty exponent.
                                                    abort();
                                                }
                                                Index = position;
                                            }
                                            // Coerce the parsed value to a JavaScript number.
                                            return +source.slice(begin, Index);
                                        }
                                        // A negative sign may only precede numbers.
                                        if (isSigned) {
                                            abort();
                                        }
                                        // `true`, `false`, and `null` literals.
                                        if (source.slice(Index, Index + 4) == "true") {
                                            Index += 4;
                                            return true;
                                        } else if (source.slice(Index, Index + 5) == "false") {
                                            Index += 5;
                                            return false;
                                        } else if (source.slice(Index, Index + 4) == "null") {
                                            Index += 4;
                                            return null;
                                        }
                                        // Unrecognized token.
                                        abort();
                                }
                            }
                            // Return the sentinel `$` character if the parser has reached the end
                            // of the source string.
                            return "$";
                        };

                        // Internal: Parses a JSON `value` token.
                        var get = function (value) {
                            var results, hasMembers;
                            if (value == "$") {
                                // Unexpected end of input.
                                abort();
                            }
                            if (typeof value == "string") {
                                if ((charIndexBuggy ? value.charAt(0) : value[0]) == "@") {
                                    // Remove the sentinel `@` character.
                                    return value.slice(1);
                                }
                                // Parse object and array literals.
                                if (value == "[") {
                                    // Parses a JSON array, returning a new JavaScript array.
                                    results = [];
                                    for (;; hasMembers || (hasMembers = true)) {
                                        value = lex();
                                        // A closing square bracket marks the end of the array literal.
                                        if (value == "]") {
                                            break;
                                        }
                                        // If the array literal contains elements, the current token
                                        // should be a comma separating the previous element from the
                                        // next.
                                        if (hasMembers) {
                                            if (value == ",") {
                                                value = lex();
                                                if (value == "]") {
                                                    // Unexpected trailing `,` in array literal.
                                                    abort();
                                                }
                                            } else {
                                                // A `,` must separate each array element.
                                                abort();
                                            }
                                        }
                                        // Elisions and leading commas are not permitted.
                                        if (value == ",") {
                                            abort();
                                        }
                                        results.push(get(value));
                                    }
                                    return results;
                                } else if (value == "{") {
                                    // Parses a JSON object, returning a new JavaScript object.
                                    results = {};
                                    for (;; hasMembers || (hasMembers = true)) {
                                        value = lex();
                                        // A closing curly brace marks the end of the object literal.
                                        if (value == "}") {
                                            break;
                                        }
                                        // If the object literal contains members, the current token
                                        // should be a comma separator.
                                        if (hasMembers) {
                                            if (value == ",") {
                                                value = lex();
                                                if (value == "}") {
                                                    // Unexpected trailing `,` in object literal.
                                                    abort();
                                                }
                                            } else {
                                                // A `,` must separate each object member.
                                                abort();
                                            }
                                        }
                                        // Leading commas are not permitted, object property names must be
                                        // double-quoted strings, and a `:` must separate each property
                                        // name and value.
                                        if (value == "," || typeof value != "string" || (charIndexBuggy ? value.charAt(0) : value[0]) != "@" || lex() != ":") {
                                            abort();
                                        }
                                        results[value.slice(1)] = get(lex());
                                    }
                                    return results;
                                }
                                // Unexpected token encountered.
                                abort();
                            }
                            return value;
                        };

                        // Internal: Updates a traversed object member.
                        var update = function (source, property, callback) {
                            var element = walk(source, property, callback);
                            if (element === undef) {
                                delete source[property];
                            } else {
                                source[property] = element;
                            }
                        };

                        // Internal: Recursively traverses a parsed JSON object, invoking the
                        // `callback` function for each value. This is an implementation of the
                        // `Walk(holder, name)` operation defined in ES 5.1 section 15.12.2.
                        var walk = function (source, property, callback) {
                            var value = source[property], length;
                            if (typeof value == "object" && value) {
                                // `forEach` can't be used to traverse an array in Opera <= 8.54
                                // because its `Object#hasOwnProperty` implementation returns `false`
                                // for array indices (e.g., `![1, 2, 3].hasOwnProperty("0")`).
                                if (getClass.call(value) == arrayClass) {
                                    for (length = value.length; length--;) {
                                        update(value, length, callback);
                                    }
                                } else {
                                    forEach(value, function (property) {
                                        update(value, property, callback);
                                    });
                                }
                            }
                            return callback.call(source, property, value);
                        };

                        // Public: `JSON.parse`. See ES 5.1 section 15.12.2.
                        exports.parse = function (source, callback) {
                            var result, value;
                            Index = 0;
                            Source = "" + source;
                            result = get(lex());
                            // If a JSON string contains multiple tokens, it is invalid.
                            if (lex() != "$") {
                                abort();
                            }
                            // Reset the parser state.
                            Index = Source = null;
                            return callback && getClass.call(callback) == functionClass ? walk((value = {}, value[""] = result, value), "", callback) : result;
                        };
                    }
                }

                exports["runInContext"] = runInContext;
                return exports;
            }

            if (freeExports && !isLoader) {
                // Export for CommonJS environments.
                runInContext(root, freeExports);
            } else {
                // Export for web browsers and JavaScript engines.
                var nativeJSON = root.JSON,
                    previousJSON = root["JSON3"],
                    isRestored = false;

                var JSON3 = runInContext(root, (root["JSON3"] = {
                    // Public: Restores the original value of the global `JSON` object and
                    // returns a reference to the `JSON3` object.
                    "noConflict": function () {
                        if (!isRestored) {
                            isRestored = true;
                            root.JSON = nativeJSON;
                            root["JSON3"] = previousJSON;
                            nativeJSON = previousJSON = null;
                        }
                        return JSON3;
                    }
                }));

                root.JSON = {
                    "parse": JSON3.parse,
                    "stringify": JSON3.stringify
                };
            }

            // Export for asynchronous module loaders.
            if (isLoader) {
                define(function () {
                    return JSON3;
                });
            }
        }).call(this);
        /************************************************************
         * end JSON
         ************************************************************/

        JSON_PIWIK = exports;

    })();
}

/* startjslint */
/*jslint browser:true, plusplus:true, vars:true, nomen:true, evil:true, regexp: false, bitwise: true, white: true */
/*global JSON_PIWIK */
/*global window */
/*global unescape */
/*global ActiveXObject */
/*global Blob */
/*members Piwik, Matomo, encodeURIComponent, decodeURIComponent, getElementsByTagName,
    shift, unshift, piwikAsyncInit, piwikPluginAsyncInit, frameElement, self, hasFocus,
    createElement, appendChild, characterSet, charset, all,
    addEventListener, attachEvent, removeEventListener, detachEvent, disableCookies,
    cookie, domain, readyState, documentElement, doScroll, title, text,
    location, top, onerror, document, referrer, parent, links, href, protocol, name, GearsFactory,
    performance, mozPerformance, msPerformance, webkitPerformance, timing, requestStart,
    responseEnd, event, which, button, srcElement, type, target,
    parentNode, tagName, hostname, className,
    userAgent, cookieEnabled, sendBeacon, platform, mimeTypes, enabledPlugin, javaEnabled,
    XMLHttpRequest, ActiveXObject, open, setRequestHeader, onreadystatechange, send, readyState, status,
    getTime, getTimeAlias, setTime, toGMTString, getHours, getMinutes, getSeconds,
    toLowerCase, toUpperCase, charAt, indexOf, lastIndexOf, split, slice,
    onload, src,
    min, round, random, floor,
    exec,
    res, width, height,
    pdf, qt, realp, wma, dir, fla, java, gears, ag,
    initialized, hook, getHook, resetUserId, getVisitorId, getVisitorInfo, setUserId, getUserId, setSiteId, getSiteId, setTrackerUrl, getTrackerUrl, appendToTrackingUrl, getRequest, addPlugin,
    getAttributionInfo, getAttributionCampaignName, getAttributionCampaignKeyword,
    getAttributionReferrerTimestamp, getAttributionReferrerUrl,
    setCustomData, getCustomData,
    setCustomRequestProcessing,
    setCustomVariable, getCustomVariable, deleteCustomVariable, storeCustomVariablesInCookie, setCustomDimension, getCustomDimension,
    deleteCustomVariables, deleteCustomDimension, setDownloadExtensions, addDownloadExtensions, removeDownloadExtensions,
    setDomains, setIgnoreClasses, setRequestMethod, setRequestContentType,
    setReferrerUrl, setCustomUrl, setAPIUrl, setDocumentTitle, getPiwikUrl, getCurrentUrl,
    setDownloadClasses, setLinkClasses,
    setCampaignNameKey, setCampaignKeywordKey,
    getConsentRequestsQueue, requireConsent, getRememberedConsent, hasRememberedConsent, setConsentGiven,
    rememberConsentGiven, forgetConsentGiven, unload, hasConsent,
    discardHashTag, alwaysUseSendBeacon,
    setCookieNamePrefix, setCookieDomain, setCookiePath, setSecureCookie, setVisitorIdCookie, getCookieDomain, hasCookies, setSessionCookie,
    setVisitorCookieTimeout, setSessionCookieTimeout, setReferralCookieTimeout, getCookie, getCookiePath, getSessionCookieTimeout,
    setConversionAttributionFirstReferrer, tracker, request,
    disablePerformanceTracking, setGenerationTimeMs,
    doNotTrack, setDoNotTrack, msDoNotTrack, getValuesFromVisitorIdCookie,
    enableCrossDomainLinking, disableCrossDomainLinking, isCrossDomainLinkingEnabled, setCrossDomainLinkingTimeout, getCrossDomainLinkingUrlParameter,
    addListener, enableLinkTracking, enableJSErrorTracking, setLinkTrackingTimer, getLinkTrackingTimer,
    enableHeartBeatTimer, disableHeartBeatTimer, killFrame, redirectFile, setCountPreRendered,
    trackGoal, trackLink, trackPageView, getNumTrackedPageViews, trackRequest, queueRequest, trackSiteSearch, trackEvent,
    requests, timeout, sendRequests, queueRequest,
    setEcommerceView, addEcommerceItem, removeEcommerceItem, clearEcommerceCart, trackEcommerceOrder, trackEcommerceCartUpdate,
    deleteCookie, deleteCookies, offsetTop, offsetLeft, offsetHeight, offsetWidth, nodeType, defaultView,
    innerHTML, scrollLeft, scrollTop, currentStyle, getComputedStyle, querySelectorAll, splice,
    getAttribute, hasAttribute, attributes, nodeName, findContentNodes, findContentNodes, findContentNodesWithinNode,
    findPieceNode, findTargetNodeNoDefault, findTargetNode, findContentPiece, children, hasNodeCssClass,
    getAttributeValueFromNode, hasNodeAttributeWithValue, hasNodeAttribute, findNodesByTagName, findMultiple,
    makeNodesUnique, concat, find, htmlCollectionToArray, offsetParent, value, nodeValue, findNodesHavingAttribute,
    findFirstNodeHavingAttribute, findFirstNodeHavingAttributeWithValue, getElementsByClassName,
    findNodesHavingCssClass, findFirstNodeHavingClass, isLinkElement, findParentContentNode, removeDomainIfIsInLink,
    findContentName, findMediaUrlInNode, toAbsoluteUrl, findContentTarget, getLocation, origin, host, isSameDomain,
    search, trim, getBoundingClientRect, bottom, right, left, innerWidth, innerHeight, clientWidth, clientHeight,
    isOrWasNodeInViewport, isNodeVisible, buildInteractionRequestParams, buildImpressionRequestParams,
    shouldIgnoreInteraction, setHrefAttribute, setAttribute, buildContentBlock, collectContent, setLocation,
    CONTENT_ATTR, CONTENT_CLASS, CONTENT_NAME_ATTR, CONTENT_PIECE_ATTR, CONTENT_PIECE_CLASS,
    CONTENT_TARGET_ATTR, CONTENT_TARGET_CLASS, CONTENT_IGNOREINTERACTION_ATTR, CONTENT_IGNOREINTERACTION_CLASS,
    trackCallbackOnLoad, trackCallbackOnReady, buildContentImpressionsRequests, wasContentImpressionAlreadyTracked,
    getQuery, getContent, setVisitorId, getContentImpressionsRequestsFromNodes, buildContentInteractionTrackingRedirectUrl,
    buildContentInteractionRequestNode, buildContentInteractionRequest, buildContentImpressionRequest,
    appendContentInteractionToRequestIfPossible, setupInteractionsTracking, trackContentImpressionClickInteraction,
    internalIsNodeVisible, clearTrackedContentImpressions, getTrackerUrl, trackAllContentImpressions,
    getTrackedContentImpressions, getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet,
    contentInteractionTrackingSetupDone, contains, match, pathname, piece, trackContentInteractionNode,
    trackContentInteractionNode, trackContentImpressionsWithinNode, trackContentImpression,
    enableTrackOnlyVisibleContent, trackContentInteraction, clearEnableTrackOnlyVisibleContent, logAllContentBlocksOnPage,
    trackVisibleContentImpressions, isTrackOnlyVisibleContentEnabled, port, isUrlToCurrentDomain, piwikTrackers,
    isNodeAuthorizedToTriggerInteraction, replaceHrefIfInternalLink, getConfigDownloadExtensions, disableLinkTracking,
    substr, setAnyAttribute, wasContentTargetAttrReplaced, max, abs, childNodes, compareDocumentPosition, body,
    getConfigVisitorCookieTimeout, getRemainingVisitorCookieTimeout, getDomains, getConfigCookiePath,
    getConfigIdPageView, newVisitor, uuid, createTs, visitCount, currentVisitTs, lastVisitTs, lastEcommerceOrderTs,
     "", "\b", "\t", "\n", "\f", "\r", "\"", "\\", apply, call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
    getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join, lastIndex, length, parse, prototype, push, replace,
    sort, slice, stringify, test, toJSON, toString, valueOf, objectToJSON, addTracker, removeAllAsyncTrackersButFirst,
    optUserOut, forgetUserOptOut, isUserOptedOut
 */
/*global _paq:true */
/*members push */
/*global Piwik:true */
/*members addPlugin, getTracker, getAsyncTracker, getAsyncTrackers, addTracker, trigger, on, off, retryMissedPluginCalls,
          DOM, onLoad, onReady, isNodeVisible, isOrWasNodeVisible, JSON */
/*global Piwik_Overlay_Client */
/*global AnalyticsTracker:true */
/*members initialize */
/*global define */
/*global console */
/*members amd */
/*members error */
/*members log */

// asynchronous tracker (or proxy)
if (typeof _paq !== 'object') {
    _paq = [];
}

// Piwik singleton and namespace
if (typeof window.Piwik !== 'object') {
    window.Matomo = window.Piwik = (function () {
        'use strict';

        /************************************************************
         * Private data
         ************************************************************/

        var expireDateTime,

            /* plugins */
            plugins = {},

            eventHandlers = {},

            /* alias frequently used globals for added minification */
            documentAlias = document,
            navigatorAlias = navigator,
            screenAlias = screen,
            windowAlias = window,

            /* performance timing */
            performanceAlias = windowAlias.performance || windowAlias.mozPerformance || windowAlias.msPerformance || windowAlias.webkitPerformance,

            /* encode */
            encodeWrapper = windowAlias.encodeURIComponent,

            /* decode */
            decodeWrapper = windowAlias.decodeURIComponent,

            /* urldecode */
            urldecode = unescape,

            /* asynchronous tracker */
            asyncTrackers = [],

            /* iterator */
            iterator,

            /* local Piwik */
            Piwik,

            missedPluginTrackerCalls = [],

            coreConsentCounter = 0,

            trackerIdCounter = 0,

            isPageUnloading = false;

        /************************************************************
         * Private methods
         ************************************************************/

        /**
         * See https://github.com/piwik/piwik/issues/8413
         * To prevent Javascript Error: Uncaught URIError: URI malformed when encoding is not UTF-8. Use this method
         * instead of decodeWrapper if a text could contain any non UTF-8 encoded characters eg
         * a URL like http://apache.piwik/test.html?%F6%E4%FC or a link like
         * <a href="test-with-%F6%E4%FC/story/0">(encoded iso-8859-1 URL)</a>
         */
        function safeDecodeWrapper(url)
        {
            try {
                return decodeWrapper(url);
            } catch (e) {
                return unescape(url);
            }
        }

        /*
         * Is property defined?
         */
        function isDefined(property) {
            // workaround https://github.com/douglascrockford/JSLint/commit/24f63ada2f9d7ad65afc90e6d949f631935c2480
            var propertyType = typeof property;

            return propertyType !== 'undefined';
        }

        /*
         * Is property a function?
         */
        function isFunction(property) {
            return typeof property === 'function';
        }

        /*
         * Is property an object?
         *
         * @return bool Returns true if property is null, an Object, or subclass of Object (i.e., an instanceof String, Date, etc.)
         */
        function isObject(property) {
            return typeof property === 'object';
        }

        /*
         * Is property a string?
         */
        function isString(property) {
            return typeof property === 'string' || property instanceof String;
        }

        function isObjectEmpty(property)
        {
            if (!property) {
                return true;
            }

            var i;
            var isEmpty = true;
            for (i in property) {
                if (Object.prototype.hasOwnProperty.call(property, i)) {
                    isEmpty = false;
                }
            }

            return isEmpty;
        }

        /**
         * Logs an error in the console.
         *  Note: it does not generate a JavaScript error, so make sure to also generate an error if needed.
         * @param message
         */
        function logConsoleError(message) {
            // needed to write it this way for jslint
            var consoleType = typeof console;
            if (consoleType !== 'undefined' && console && console.error) {
                console.error(message);
            }
        }

        /*
         * apply wrapper
         *
         * @param array parameterArray An array comprising either:
         *      [ 'methodName', optional_parameters ]
         * or:
         *      [ functionObject, optional_parameters ]
         */
        function apply() {
            var i, j, f, parameterArray, trackerCall;

            for (i = 0; i < arguments.length; i += 1) {
                trackerCall = null;
                if (arguments[i] && arguments[i].slice) {
                    trackerCall = arguments[i].slice();
                }
                parameterArray = arguments[i];
                f = parameterArray.shift();

                var fParts, context;

                var isStaticPluginCall = isString(f) && f.indexOf('::') > 0;
                if (isStaticPluginCall) {
                    // a static method will not be called on a tracker and is not dependent on the existence of a
                    // tracker etc
                    fParts = f.split('::');
                    context = fParts[0];
                    f = fParts[1];

                    if ('object' === typeof Piwik[context] && 'function' === typeof Piwik[context][f]) {
                        Piwik[context][f].apply(Piwik[context], parameterArray);
                    } else if (trackerCall) {
                        // we try to call that method again later as the plugin might not be loaded yet
                        // a plugin can call "Piwik.retryMissedPluginCalls();" once it has been loaded and then the
                        // method call to "Piwik[context][f]" may be executed
                        missedPluginTrackerCalls.push(trackerCall);
                    }

                } else {
                    for (j = 0; j < asyncTrackers.length; j++) {
                        if (isString(f)) {
                            context = asyncTrackers[j];

                            var isPluginTrackerCall = f.indexOf('.') > 0;

                            if (isPluginTrackerCall) {
                                fParts = f.split('.');
                                if (context && 'object' === typeof context[fParts[0]]) {
                                    context = context[fParts[0]];
                                    f = fParts[1];
                                } else if (trackerCall) {
                                    // we try to call that method again later as the plugin might not be loaded yet
                                    missedPluginTrackerCalls.push(trackerCall);
                                    break;
                                }
                            }

                            if (context[f]) {
                                context[f].apply(context, parameterArray);
                            } else {
                                var message = 'The method \'' + f + '\' was not found in "_paq" variable.  Please have a look at the Piwik tracker documentation: https://developer.piwik.org/api-reference/tracking-javascript';
                                logConsoleError(message);

                                if (!isPluginTrackerCall) {
                                    // do not trigger an error if it is a call to a plugin as the plugin may just not be
                                    // loaded yet etc
                                    throw new TypeError(message);
                                }
                            }

                            if (f === 'addTracker') {
                                // addTracker adds an entry to asyncTrackers and would otherwise result in an endless loop
                                break;
                            }

                            if (f === 'setTrackerUrl' || f === 'setSiteId') {
                                // these two methods should be only executed on the first tracker
                                break;
                            }
                        } else {
                            f.apply(asyncTrackers[j], parameterArray);
                        }
                    }
                }
            }
        }

        /*
         * Cross-browser helper function to add event handler
         */
        function addEventListener(element, eventType, eventHandler, useCapture) {
            if (element.addEventListener) {
                element.addEventListener(eventType, eventHandler, useCapture);

                return true;
            }

            if (element.attachEvent) {
                return element.attachEvent('on' + eventType, eventHandler);
            }

            element['on' + eventType] = eventHandler;
        }

        function trackCallbackOnLoad(callback)
        {
            if (documentAlias.readyState === 'complete') {
                callback();
            } else if (windowAlias.addEventListener) {
                windowAlias.addEventListener('load', callback, false);
            } else if (windowAlias.attachEvent) {
                windowAlias.attachEvent('onload', callback);
            }
        }

        function trackCallbackOnReady(callback)
        {
            var loaded = false;

            if (documentAlias.attachEvent) {
                loaded = documentAlias.readyState === 'complete';
            } else {
                loaded = documentAlias.readyState !== 'loading';
            }

            if (loaded) {
                callback();
                return;
            }

            var _timer;

            if (documentAlias.addEventListener) {
                addEventListener(documentAlias, 'DOMContentLoaded', function ready() {
                    documentAlias.removeEventListener('DOMContentLoaded', ready, false);
                    if (!loaded) {
                        loaded = true;
                        callback();
                    }
                });
            } else if (documentAlias.attachEvent) {
                documentAlias.attachEvent('onreadystatechange', function ready() {
                    if (documentAlias.readyState === 'complete') {
                        documentAlias.detachEvent('onreadystatechange', ready);
                        if (!loaded) {
                            loaded = true;
                            callback();
                        }
                    }
                });

                if (documentAlias.documentElement.doScroll && windowAlias === windowAlias.top) {
                    (function ready() {
                        if (!loaded) {
                            try {
                                documentAlias.documentElement.doScroll('left');
                            } catch (error) {
                                setTimeout(ready, 0);

                                return;
                            }
                            loaded = true;
                            callback();
                        }
                    }());
                }
            }

            // fallback
            addEventListener(windowAlias, 'load', function () {
                if (!loaded) {
                    loaded = true;
                    callback();
                }
            }, false);
        }

        /*
         * Call plugin hook methods
         */
        function executePluginMethod(methodName, params, callback) {
            if (!methodName) {
                return '';
            }

            var result = '',
                i,
                pluginMethod, value, isFunction;

            for (i in plugins) {
                if (Object.prototype.hasOwnProperty.call(plugins, i)) {
                    isFunction = plugins[i] && 'function' === typeof plugins[i][methodName];

                    if (isFunction) {
                        pluginMethod = plugins[i][methodName];
                        value = pluginMethod(params || {}, callback);

                        if (value) {
                            result += value;
                        }
                    }
                }
            }

            return result;
        }

        /*
         * Handle beforeunload event
         *
         * Subject to Safari's "Runaway JavaScript Timer" and
         * Chrome V8 extension that terminates JS that exhibits
         * "slow unload", i.e., calling getTime() > 1000 times
         */
        function beforeUnloadHandler() {
            var now;
            isPageUnloading = true;

            executePluginMethod('unload');
            /*
             * Delay/pause (blocks UI)
             */
            if (expireDateTime) {
                // the things we do for backwards compatibility...
                // in ECMA-262 5th ed., we could simply use:
                //     while (Date.now() < expireDateTime) { }
                do {
                    now = new Date();
                } while (now.getTimeAlias() < expireDateTime);
            }
        }

        /*
         * Load JavaScript file (asynchronously)
         */
        function loadScript(src, onLoad) {
            var script = documentAlias.createElement('script');

            script.type = 'text/javascript';
            script.src = src;

            if (script.readyState) {
                script.onreadystatechange = function () {
                    var state = this.readyState;

                    if (state === 'loaded' || state === 'complete') {
                        script.onreadystatechange = null;
                        onLoad();
                    }
                };
            } else {
                script.onload = onLoad;
            }

            documentAlias.getElementsByTagName('head')[0].appendChild(script);
        }

        /*
         * Get page referrer
         */
        function getReferrer() {
            var referrer = '';

            try {
                referrer = windowAlias.top.document.referrer;
            } catch (e) {
                if (windowAlias.parent) {
                    try {
                        referrer = windowAlias.parent.document.referrer;
                    } catch (e2) {
                        referrer = '';
                    }
                }
            }

            if (referrer === '') {
                referrer = documentAlias.referrer;
            }

            return referrer;
        }

        /*
         * Extract scheme/protocol from URL
         */
        function getProtocolScheme(url) {
            var e = new RegExp('^([a-z]+):'),
                matches = e.exec(url);

            return matches ? matches[1] : null;
        }

        /*
         * Extract hostname from URL
         */
        function getHostName(url) {
            // scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
            var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
                matches = e.exec(url);

            return matches ? matches[1] : url;
        }

        function stringStartsWith(str, prefix) {
            str = String(str);
            return str.lastIndexOf(prefix, 0) === 0;
        }

        function stringEndsWith(str, suffix) {
            str = String(str);
            return str.indexOf(suffix, str.length - suffix.length) !== -1;
        }

        function stringContains(str, needle) {
            str = String(str);
            return str.indexOf(needle) !== -1;
        }

        function removeCharactersFromEndOfString(str, numCharactersToRemove) {
            str = String(str);
            return str.substr(0, str.length - numCharactersToRemove);
        }

        /**
         * We do not check whether URL contains already url parameter, please use removeUrlParameter() if needed
         * before calling this method.
         * This method makes sure to append URL parameters before a possible hash. Will escape (encode URI component)
         * the set name and value
         */
        function addUrlParameter(url, name, value) {
            url = String(url);

            if (!value) {
                value = '';
            }

            var hashPos = url.indexOf('#');
            var urlLength = url.length;

            if (hashPos === -1) {
                hashPos = urlLength;
            }

            var baseUrl = url.substr(0, hashPos);
            var urlHash = url.substr(hashPos, urlLength - hashPos);

            if (baseUrl.indexOf('?') === -1) {
                baseUrl += '?';
            } else if (!stringEndsWith(baseUrl, '?')) {
                baseUrl += '&';
            }
            // nothing to if ends with ?

            return baseUrl + encodeWrapper(name) + '=' + encodeWrapper(value) + urlHash;
        }

        function removeUrlParameter(url, name) {
            url = String(url);

            if (url.indexOf('?' + name + '=') === -1 && url.indexOf('&' + name + '=') === -1) {
                // nothing to remove, url does not contain this parameter
                return url;
            }

            var searchPos = url.indexOf('?');
            if (searchPos === -1) {
                // nothing to remove, no query parameters
                return url;
            }

            var queryString = url.substr(searchPos + 1);
            var baseUrl = url.substr(0, searchPos);

            if (queryString) {
                var urlHash = '';
                var hashPos = queryString.indexOf('#');
                if (hashPos !== -1) {
                    urlHash = queryString.substr(hashPos + 1);
                    queryString = queryString.substr(0, hashPos);
                }

                var param;
                var paramsArr = queryString.split('&');
                var i = paramsArr.length - 1;

                for (i; i >= 0; i--) {
                    param = paramsArr[i].split('=')[0];
                    if (param === name) {
                        paramsArr.splice(i, 1);
                    }
                }

                var newQueryString = paramsArr.join('&');

                if (newQueryString) {
                    baseUrl = baseUrl + '?' + newQueryString;
                }

                if (urlHash) {
                    baseUrl += '#' + urlHash;
                }
            }

            return baseUrl;
        }

        /*
         * Extract parameter from URL
         */
        function getUrlParameter(url, name) {
            var regexSearch = "[\\?&#]" + name + "=([^&#]*)";
            var regex = new RegExp(regexSearch);
            var results = regex.exec(url);
            return results ? decodeWrapper(results[1]) : '';
        }

        function trim(text)
        {
            if (text && String(text) === text) {
                return text.replace(/^\s+|\s+$/g, '');
            }

            return text;
        }

        /*
         * UTF-8 encoding
         */
        function utf8_encode(argString) {
            return unescape(encodeWrapper(argString));
        }

        /************************************************************
         * sha1
         * - based on sha1 from http://phpjs.org/functions/sha1:512 (MIT / GPL v2)
         ************************************************************/

        function sha1(str) {
            // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
            // + namespaced by: Michael White (http://getsprink.com)
            // +      input by: Brett Zamir (http://brett-zamir.me)
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   jslinted by: Anthon Pang (http://piwik.org)

            var
                rotate_left = function (n, s) {
                    return (n << s) | (n >>> (32 - s));
                },

                cvt_hex = function (val) {
                    var strout = '',
                        i,
                        v;

                    for (i = 7; i >= 0; i--) {
                        v = (val >>> (i * 4)) & 0x0f;
                        strout += v.toString(16);
                    }

                    return strout;
                },

                blockstart,
                i,
                j,
                W = [],
                H0 = 0x67452301,
                H1 = 0xEFCDAB89,
                H2 = 0x98BADCFE,
                H3 = 0x10325476,
                H4 = 0xC3D2E1F0,
                A,
                B,
                C,
                D,
                E,
                temp,
                str_len,
                word_array = [];

            str = utf8_encode(str);
            str_len = str.length;

            for (i = 0; i < str_len - 3; i += 4) {
                j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 |
                    str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
                word_array.push(j);
            }

            switch (str_len & 3) {
                case 0:
                    i = 0x080000000;
                    break;
                case 1:
                    i = str.charCodeAt(str_len - 1) << 24 | 0x0800000;
                    break;
                case 2:
                    i = str.charCodeAt(str_len - 2) << 24 | str.charCodeAt(str_len - 1) << 16 | 0x08000;
                    break;
                case 3:
                    i = str.charCodeAt(str_len - 3) << 24 | str.charCodeAt(str_len - 2) << 16 | str.charCodeAt(str_len - 1) << 8 | 0x80;
                    break;
            }

            word_array.push(i);

            while ((word_array.length & 15) !== 14) {
                word_array.push(0);
            }

            word_array.push(str_len >>> 29);
            word_array.push((str_len << 3) & 0x0ffffffff);

            for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
                for (i = 0; i < 16; i++) {
                    W[i] = word_array[blockstart + i];
                }

                for (i = 16; i <= 79; i++) {
                    W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
                }

                A = H0;
                B = H1;
                C = H2;
                D = H3;
                E = H4;

                for (i = 0; i <= 19; i++) {
                    temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B, 30);
                    B = A;
                    A = temp;
                }

                for (i = 20; i <= 39; i++) {
                    temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B, 30);
                    B = A;
                    A = temp;
                }

                for (i = 40; i <= 59; i++) {
                    temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B, 30);
                    B = A;
                    A = temp;
                }

                for (i = 60; i <= 79; i++) {
                    temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
                    E = D;
                    D = C;
                    C = rotate_left(B, 30);
                    B = A;
                    A = temp;
                }

                H0 = (H0 + A) & 0x0ffffffff;
                H1 = (H1 + B) & 0x0ffffffff;
                H2 = (H2 + C) & 0x0ffffffff;
                H3 = (H3 + D) & 0x0ffffffff;
                H4 = (H4 + E) & 0x0ffffffff;
            }

            temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);

            return temp.toLowerCase();
        }

        /************************************************************
         * end sha1
         ************************************************************/

        /*
         * Fix-up URL when page rendered from search engine cache or translated page
         */
        function urlFixup(hostName, href, referrer) {
            if (!hostName) {
                hostName = '';
            }

            if (!href) {
                href = '';
            }

            if (hostName === 'translate.googleusercontent.com') {       // Google
                if (referrer === '') {
                    referrer = href;
                }

                href = getUrlParameter(href, 'u');
                hostName = getHostName(href);
            } else if (hostName === 'cc.bingj.com' ||                   // Bing
                hostName === 'webcache.googleusercontent.com' ||    // Google
                hostName.slice(0, 5) === '74.6.') {                 // Yahoo (via Inktomi 74.6.0.0/16)
                href = documentAlias.links[0].href;
                hostName = getHostName(href);
            }

            return [hostName, href, referrer];
        }

        /*
         * Fix-up domain
         */
        function domainFixup(domain) {
            var dl = domain.length;

            // remove trailing '.'
            if (domain.charAt(--dl) === '.') {
                domain = domain.slice(0, dl);
            }

            // remove leading '*'
            if (domain.slice(0, 2) === '*.') {
                domain = domain.slice(1);
            }

            if (domain.indexOf('/') !== -1) {
                domain = domain.substr(0, domain.indexOf('/'));
            }

            return domain;
        }

        /*
         * Title fixup
         */
        function titleFixup(title) {
            title = title && title.text ? title.text : title;

            if (!isString(title)) {
                var tmp = documentAlias.getElementsByTagName('title');

                if (tmp && isDefined(tmp[0])) {
                    title = tmp[0].text;
                }
            }

            return title;
        }

        function getChildrenFromNode(node)
        {
            if (!node) {
                return [];
            }

            if (!isDefined(node.children) && isDefined(node.childNodes)) {
                return node.children;
            }

            if (isDefined(node.children)) {
                return node.children;
            }

            return [];
        }

        function containsNodeElement(node, containedNode)
        {
            if (!node || !containedNode) {
                return false;
            }

            if (node.contains) {
                return node.contains(containedNode);
            }

            if (node === containedNode) {
                return true;
            }

            if (node.compareDocumentPosition) {
                return !!(node.compareDocumentPosition(containedNode) & 16);
            }

            return false;
        }

        // Polyfill for IndexOf for IE6-IE8
        function indexOfArray(theArray, searchElement)
        {
            if (theArray && theArray.indexOf) {
                return theArray.indexOf(searchElement);
            }

            // 1. Let O be the result of calling ToObject passing
            //    the this value as the argument.
            if (!isDefined(theArray) || theArray === null) {
                return -1;
            }

            if (!theArray.length) {
                return -1;
            }

            var len = theArray.length;

            if (len === 0) {
                return -1;
            }

            var k = 0;

            // 9. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ToString(k).
                //   This is implicit for LHS operands of the in operator
                // b. Let kPresent be the result of calling the
                //    HasProperty internal method of O with argument Pk.
                //   This step can be combined with c
                // c. If kPresent is true, then
                //    i.  Let elementK be the result of calling the Get
                //        internal method of O with the argument ToString(k).
                //   ii.  Let same be the result of applying the
                //        Strict Equality Comparison Algorithm to
                //        searchElement and elementK.
                //  iii.  If same is true, return k.
                if (theArray[k] === searchElement) {
                    return k;
                }
                k++;
            }
            return -1;
        }

        /************************************************************
         * Element Visiblility
         ************************************************************/

        /**
         * Author: Jason Farrell
         * Author URI: http://useallfive.com/
         *
         * Description: Checks if a DOM element is truly visible.
         * Package URL: https://github.com/UseAllFive/true-visibility
         * License: MIT (https://github.com/UseAllFive/true-visibility/blob/master/LICENSE.txt)
         */
        function isVisible(node) {

            if (!node) {
                return false;
            }

            //-- Cross browser method to get style properties:
            function _getStyle(el, property) {
                if (windowAlias.getComputedStyle) {
                    return documentAlias.defaultView.getComputedStyle(el,null)[property];
                }
                if (el.currentStyle) {
                    return el.currentStyle[property];
                }
            }

            function _elementInDocument(element) {
                element = element.parentNode;

                while (element) {
                    if (element === documentAlias) {
                        return true;
                    }
                    element = element.parentNode;
                }
                return false;
            }

            /**
             * Checks if a DOM element is visible. Takes into
             * consideration its parents and overflow.
             *
             * @param (el)      the DOM element to check if is visible
             *
             * These params are optional that are sent in recursively,
             * you typically won't use these:
             *
             * @param (t)       Top corner position number
             * @param (r)       Right corner position number
             * @param (b)       Bottom corner position number
             * @param (l)       Left corner position number
             * @param (w)       Element width number
             * @param (h)       Element height number
             */
            function _isVisible(el, t, r, b, l, w, h) {
                var p = el.parentNode,
                    VISIBLE_PADDING = 1; // has to be visible at least one px of the element

                if (!_elementInDocument(el)) {
                    return false;
                }

                //-- Return true for document node
                if (9 === p.nodeType) {
                    return true;
                }

                //-- Return false if our element is invisible
                if (
                    '0' === _getStyle(el, 'opacity') ||
                    'none' === _getStyle(el, 'display') ||
                    'hidden' === _getStyle(el, 'visibility')
                ) {
                    return false;
                }

                if (!isDefined(t) ||
                    !isDefined(r) ||
                    !isDefined(b) ||
                    !isDefined(l) ||
                    !isDefined(w) ||
                    !isDefined(h)) {
                    t = el.offsetTop;
                    l = el.offsetLeft;
                    b = t + el.offsetHeight;
                    r = l + el.offsetWidth;
                    w = el.offsetWidth;
                    h = el.offsetHeight;
                }

                if (node === el && (0 === h || 0 === w) && 'hidden' === _getStyle(el, 'overflow')) {
                    return false;
                }

                //-- If we have a parent, let's continue:
                if (p) {
                    //-- Check if the parent can hide its children.
                    if (('hidden' === _getStyle(p, 'overflow') || 'scroll' === _getStyle(p, 'overflow'))) {
                        //-- Only check if the offset is different for the parent
                        if (
                            //-- If the target element is to the right of the parent elm
                        l + VISIBLE_PADDING > p.offsetWidth + p.scrollLeft ||
                        //-- If the target element is to the left of the parent elm
                        l + w - VISIBLE_PADDING < p.scrollLeft ||
                        //-- If the target element is under the parent elm
                        t + VISIBLE_PADDING > p.offsetHeight + p.scrollTop ||
                        //-- If the target element is above the parent elm
                        t + h - VISIBLE_PADDING < p.scrollTop
                        ) {
                            //-- Our target element is out of bounds:
                            return false;
                        }
                    }
                    //-- Add the offset parent's left/top coords to our element's offset:
                    if (el.offsetParent === p) {
                        l += p.offsetLeft;
                        t += p.offsetTop;
                    }
                    //-- Let's recursively check upwards:
                    return _isVisible(p, t, r, b, l, w, h);
                }
                return true;
            }

            return _isVisible(node);
        }

        /************************************************************
         * Query
         ************************************************************/

        var query = {
            htmlCollectionToArray: function (foundNodes)
            {
                var nodes = [], index;

                if (!foundNodes || !foundNodes.length) {
                    return nodes;
                }

                for (index = 0; index < foundNodes.length; index++) {
                    nodes.push(foundNodes[index]);
                }

                return nodes;
            },
            find: function (selector)
            {
                // we use querySelectorAll only on document, not on nodes because of its unexpected behavior. See for
                // instance http://stackoverflow.com/questions/11503534/jquery-vs-document-queryselectorall and
                // http://jsfiddle.net/QdMc5/ and http://ejohn.org/blog/thoughts-on-queryselectorall
                if (!document.querySelectorAll || !selector) {
                    return []; // we do not support all browsers
                }

                var foundNodes = document.querySelectorAll(selector);

                return this.htmlCollectionToArray(foundNodes);
            },
            findMultiple: function (selectors)
            {
                if (!selectors || !selectors.length) {
                    return [];
                }

                var index, foundNodes;
                var nodes = [];
                for (index = 0; index < selectors.length; index++) {
                    foundNodes = this.find(selectors[index]);
                    nodes = nodes.concat(foundNodes);
                }

                nodes = this.makeNodesUnique(nodes);

                return nodes;
            },
            findNodesByTagName: function (node, tagName)
            {
                if (!node || !tagName || !node.getElementsByTagName) {
                    return [];
                }

                var foundNodes = node.getElementsByTagName(tagName);

                return this.htmlCollectionToArray(foundNodes);
            },
            makeNodesUnique: function (nodes)
            {
                var copy = [].concat(nodes);
                nodes.sort(function(n1, n2){
                    if (n1 === n2) {
                        return 0;
                    }

                    var index1 = indexOfArray(copy, n1);
                    var index2 = indexOfArray(copy, n2);

                    if (index1 === index2) {
                        return 0;
                    }

                    return index1 > index2 ? -1 : 1;
                });

                if (nodes.length <= 1) {
                    return nodes;
                }

                var index = 0;
                var numDuplicates = 0;
                var duplicates = [];
                var node;

                node = nodes[index++];

                while (node) {
                    if (node === nodes[index]) {
                        numDuplicates = duplicates.push(index);
                    }

                    node = nodes[index++] || null;
                }

                while (numDuplicates--) {
                    nodes.splice(duplicates[numDuplicates], 1);
                }

                return nodes;
            },
            getAttributeValueFromNode: function (node, attributeName)
            {
                if (!this.hasNodeAttribute(node, attributeName)) {
                    return;
                }

                if (node && node.getAttribute) {
                    return node.getAttribute(attributeName);
                }

                if (!node || !node.attributes) {
                    return;
                }

                var typeOfAttr = (typeof node.attributes[attributeName]);
                if ('undefined' === typeOfAttr) {
                    return;
                }

                if (node.attributes[attributeName].value) {
                    return node.attributes[attributeName].value; // nodeValue is deprecated ie Chrome
                }

                if (node.attributes[attributeName].nodeValue) {
                    return node.attributes[attributeName].nodeValue;
                }

                var index;
                var attrs = node.attributes;

                if (!attrs) {
                    return;
                }

                for (index = 0; index < attrs.length; index++) {
                    if (attrs[index].nodeName === attributeName) {
                        return attrs[index].nodeValue;
                    }
                }

                return null;
            },
            hasNodeAttributeWithValue: function (node, attributeName)
            {
                var value = this.getAttributeValueFromNode(node, attributeName);

                return !!value;
            },
            hasNodeAttribute: function (node, attributeName)
            {
                if (node && node.hasAttribute) {
                    return node.hasAttribute(attributeName);
                }

                if (node && node.attributes) {
                    var typeOfAttr = (typeof node.attributes[attributeName]);
                    return 'undefined' !== typeOfAttr;
                }

                return false;
            },
            hasNodeCssClass: function (node, klassName)
            {
                if (node && klassName && node.className) {
                    var classes = typeof node.className === "string" ? node.className.split(' ') : [];
                    if (-1 !== indexOfArray(classes, klassName)) {
                        return true;
                    }
                }

                return false;
            },
            findNodesHavingAttribute: function (nodeToSearch, attributeName, nodes)
            {
                if (!nodes) {
                    nodes = [];
                }

                if (!nodeToSearch || !attributeName) {
                    return nodes;
                }

                var children = getChildrenFromNode(nodeToSearch);

                if (!children || !children.length) {
                    return nodes;
                }

                var index, child;
                for (index = 0; index < children.length; index++) {
                    child = children[index];
                    if (this.hasNodeAttribute(child, attributeName)) {
                        nodes.push(child);
                    }

                    nodes = this.findNodesHavingAttribute(child, attributeName, nodes);
                }

                return nodes;
            },
            findFirstNodeHavingAttribute: function (node, attributeName)
            {
                if (!node || !attributeName) {
                    return;
                }

                if (this.hasNodeAttribute(node, attributeName)) {
                    return node;
                }

                var nodes = this.findNodesHavingAttribute(node, attributeName);

                if (nodes && nodes.length) {
                    return nodes[0];
                }
            },
            findFirstNodeHavingAttributeWithValue: function (node, attributeName)
            {
                if (!node || !attributeName) {
                    return;
                }

                if (this.hasNodeAttributeWithValue(node, attributeName)) {
                    return node;
                }

                var nodes = this.findNodesHavingAttribute(node, attributeName);

                if (!nodes || !nodes.length) {
                    return;
                }

                var index;
                for (index = 0; index < nodes.length; index++) {
                    if (this.getAttributeValueFromNode(nodes[index], attributeName)) {
                        return nodes[index];
                    }
                }
            },
            findNodesHavingCssClass: function (nodeToSearch, className, nodes)
            {
                if (!nodes) {
                    nodes = [];
                }

                if (!nodeToSearch || !className) {
                    return nodes;
                }

                if (nodeToSearch.getElementsByClassName) {
                    var foundNodes = nodeToSearch.getElementsByClassName(className);
                    return this.htmlCollectionToArray(foundNodes);
                }

                var children = getChildrenFromNode(nodeToSearch);

                if (!children || !children.length) {
                    return [];
                }

                var index, child;
                for (index = 0; index < children.length; index++) {
                    child = children[index];
                    if (this.hasNodeCssClass(child, className)) {
                        nodes.push(child);
                    }

                    nodes = this.findNodesHavingCssClass(child, className, nodes);
                }

                return nodes;
            },
            findFirstNodeHavingClass: function (node, className)
            {
                if (!node || !className) {
                    return;
                }

                if (this.hasNodeCssClass(node, className)) {
                    return node;
                }

                var nodes = this.findNodesHavingCssClass(node, className);

                if (nodes && nodes.length) {
                    return nodes[0];
                }
            },
            isLinkElement: function (node)
            {
                if (!node) {
                    return false;
                }

                var elementName      = String(node.nodeName).toLowerCase();
                var linkElementNames = ['a', 'area'];
                var pos = indexOfArray(linkElementNames, elementName);

                return pos !== -1;
            },
            setAnyAttribute: function (node, attrName, attrValue)
            {
                if (!node || !attrName) {
                    return;
                }

                if (node.setAttribute) {
                    node.setAttribute(attrName, attrValue);
                } else {
                    node[attrName] = attrValue;
                }
            }
        };

        /************************************************************
         * Content Tracking
         ************************************************************/

        var content = {
            CONTENT_ATTR: 'data-track-content',
            CONTENT_CLASS: 'piwikTrackContent',
            CONTENT_NAME_ATTR: 'data-content-name',
            CONTENT_PIECE_ATTR: 'data-content-piece',
            CONTENT_PIECE_CLASS: 'piwikContentPiece',
            CONTENT_TARGET_ATTR: 'data-content-target',
            CONTENT_TARGET_CLASS: 'piwikContentTarget',
            CONTENT_IGNOREINTERACTION_ATTR: 'data-content-ignoreinteraction',
            CONTENT_IGNOREINTERACTION_CLASS: 'piwikContentIgnoreInteraction',
            location: undefined,

            findContentNodes: function ()
            {

                var cssSelector  = '.' + this.CONTENT_CLASS;
                var attrSelector = '[' + this.CONTENT_ATTR + ']';
                var contentNodes = query.findMultiple([cssSelector, attrSelector]);

                return contentNodes;
            },
            findContentNodesWithinNode: function (node)
            {
                if (!node) {
                    return [];
                }

                // NOTE: we do not use query.findMultiple here as querySelectorAll would most likely not deliver the result we want

                var nodes1 = query.findNodesHavingCssClass(node, this.CONTENT_CLASS);
                var nodes2 = query.findNodesHavingAttribute(node, this.CONTENT_ATTR);

                if (nodes2 && nodes2.length) {
                    var index;
                    for (index = 0; index < nodes2.length; index++) {
                        nodes1.push(nodes2[index]);
                    }
                }

                if (query.hasNodeAttribute(node, this.CONTENT_ATTR)) {
                    nodes1.push(node);
                } else if (query.hasNodeCssClass(node, this.CONTENT_CLASS)) {
                    nodes1.push(node);
                }

                nodes1 = query.makeNodesUnique(nodes1);

                return nodes1;
            },
            findParentContentNode: function (anyNode)
            {
                if (!anyNode) {
                    return;
                }

                var node    = anyNode;
                var counter = 0;

                while (node && node !== documentAlias && node.parentNode) {
                    if (query.hasNodeAttribute(node, this.CONTENT_ATTR)) {
                        return node;
                    }
                    if (query.hasNodeCssClass(node, this.CONTENT_CLASS)) {
                        return node;
                    }

                    node = node.parentNode;

                    if (counter > 1000) {
                        break; // prevent loop, should not happen anyway but better we do this
                    }
                    counter++;
                }
            },
            findPieceNode: function (node)
            {
                var contentPiece;

                contentPiece = query.findFirstNodeHavingAttribute(node, this.CONTENT_PIECE_ATTR);

                if (!contentPiece) {
                    contentPiece = query.findFirstNodeHavingClass(node, this.CONTENT_PIECE_CLASS);
                }

                if (contentPiece) {
                    return contentPiece;
                }

                return node;
            },
            findTargetNodeNoDefault: function (node)
            {
                if (!node) {
                    return;
                }

                var target = query.findFirstNodeHavingAttributeWithValue(node, this.CONTENT_TARGET_ATTR);
                if (target) {
                    return target;
                }

                target = query.findFirstNodeHavingAttribute(node, this.CONTENT_TARGET_ATTR);
                if (target) {
                    return target;
                }

                target = query.findFirstNodeHavingClass(node, this.CONTENT_TARGET_CLASS);
                if (target) {
                    return target;
                }
            },
            findTargetNode: function (node)
            {
                var target = this.findTargetNodeNoDefault(node);
                if (target) {
                    return target;
                }

                return node;
            },
            findContentName: function (node)
            {
                if (!node) {
                    return;
                }

                var nameNode = query.findFirstNodeHavingAttributeWithValue(node, this.CONTENT_NAME_ATTR);

                if (nameNode) {
                    return query.getAttributeValueFromNode(nameNode, this.CONTENT_NAME_ATTR);
                }

                var contentPiece = this.findContentPiece(node);
                if (contentPiece) {
                    return this.removeDomainIfIsInLink(contentPiece);
                }

                if (query.hasNodeAttributeWithValue(node, 'title')) {
                    return query.getAttributeValueFromNode(node, 'title');
                }

                var clickUrlNode = this.findPieceNode(node);

                if (query.hasNodeAttributeWithValue(clickUrlNode, 'title')) {
                    return query.getAttributeValueFromNode(clickUrlNode, 'title');
                }

                var targetNode = this.findTargetNode(node);

                if (query.hasNodeAttributeWithValue(targetNode, 'title')) {
                    return query.getAttributeValueFromNode(targetNode, 'title');
                }
            },
            findContentPiece: function (node)
            {
                if (!node) {
                    return;
                }

                var nameNode = query.findFirstNodeHavingAttributeWithValue(node, this.CONTENT_PIECE_ATTR);

                if (nameNode) {
                    return query.getAttributeValueFromNode(nameNode, this.CONTENT_PIECE_ATTR);
                }

                var contentNode = this.findPieceNode(node);

                var media = this.findMediaUrlInNode(contentNode);
                if (media) {
                    return this.toAbsoluteUrl(media);
                }
            },
            findContentTarget: function (node)
            {
                if (!node) {
                    return;
                }

                var targetNode = this.findTargetNode(node);

                if (query.hasNodeAttributeWithValue(targetNode, this.CONTENT_TARGET_ATTR)) {
                    return query.getAttributeValueFromNode(targetNode, this.CONTENT_TARGET_ATTR);
                }

                var href;
                if (query.hasNodeAttributeWithValue(targetNode, 'href')) {
                    href = query.getAttributeValueFromNode(targetNode, 'href');
                    return this.toAbsoluteUrl(href);
                }

                var contentNode = this.findPieceNode(node);

                if (query.hasNodeAttributeWithValue(contentNode, 'href')) {
                    href = query.getAttributeValueFromNode(contentNode, 'href');
                    return this.toAbsoluteUrl(href);
                }
            },
            isSameDomain: function (url)
            {
                if (!url || !url.indexOf) {
                    return false;
                }

                if (0 === url.indexOf(this.getLocation().origin)) {
                    return true;
                }

                var posHost = url.indexOf(this.getLocation().host);
                if (8 >= posHost && 0 <= posHost) {
                    return true;
                }

                return false;
            },
            removeDomainIfIsInLink: function (text)
            {
                // we will only remove if domain === location.origin meaning is not an outlink
                var regexContainsProtocol = '^https?:\/\/[^\/]+';
                var regexReplaceDomain = '^.*\/\/[^\/]+';

                if (text &&
                    text.search &&
                    -1 !== text.search(new RegExp(regexContainsProtocol))
                    && this.isSameDomain(text)) {

                    text = text.replace(new RegExp(regexReplaceDomain), '');
                    if (!text) {
                        text = '/';
                    }
                }

                return text;
            },
            findMediaUrlInNode: function (node)
            {
                if (!node) {
                    return;
                }

                var mediaElements = ['img', 'embed', 'video', 'audio'];
                var elementName   = node.nodeName.toLowerCase();

                if (-1 !== indexOfArray(mediaElements, elementName) &&
                    query.findFirstNodeHavingAttributeWithValue(node, 'src')) {

                    var sourceNode = query.findFirstNodeHavingAttributeWithValue(node, 'src');

                    return query.getAttributeValueFromNode(sourceNode, 'src');
                }

                if (elementName === 'object' &&
                    query.hasNodeAttributeWithValue(node, 'data')) {

                    return query.getAttributeValueFromNode(node, 'data');
                }

                if (elementName === 'object') {
                    var params = query.findNodesByTagName(node, 'param');
                    if (params && params.length) {
                        var index;
                        for (index = 0; index < params.length; index++) {
                            if ('movie' === query.getAttributeValueFromNode(params[index], 'name') &&
                                query.hasNodeAttributeWithValue(params[index], 'value')) {

                                return query.getAttributeValueFromNode(params[index], 'value');
                            }
                        }
                    }

                    var embed = query.findNodesByTagName(node, 'embed');
                    if (embed && embed.length) {
                        return this.findMediaUrlInNode(embed[0]);
                    }
                }
            },
            trim: function (text)
            {
                return trim(text);
            },
            isOrWasNodeInViewport: function (node)
            {
                if (!node || !node.getBoundingClientRect || node.nodeType !== 1) {
                    return true;
                }

                var rect = node.getBoundingClientRect();
                var html = documentAlias.documentElement || {};

                var wasVisible = rect.top < 0;
                if (wasVisible && node.offsetTop) {
                    wasVisible = (node.offsetTop + rect.height) > 0;
                }

                var docWidth = html.clientWidth; // The clientWidth attribute returns the viewport width excluding the size of a rendered scroll bar

                if (windowAlias.innerWidth && docWidth > windowAlias.innerWidth) {
                    docWidth = windowAlias.innerWidth; // The innerWidth attribute must return the viewport width including the size of a rendered scroll bar
                }

                var docHeight = html.clientHeight; // The clientWidth attribute returns the viewport width excluding the size of a rendered scroll bar

                if (windowAlias.innerHeight && docHeight > windowAlias.innerHeight) {
                    docHeight = windowAlias.innerHeight; // The innerWidth attribute must return the viewport width including the size of a rendered scroll bar
                }

                return (
                    (rect.bottom > 0 || wasVisible) &&
                    rect.right  > 0 &&
                    rect.left   < docWidth &&
                    ((rect.top  < docHeight) || wasVisible) // rect.top < 0 we assume user has seen all the ones that are above the current viewport
                );
            },
            isNodeVisible: function (node)
            {
                var isItVisible  = isVisible(node);
                var isInViewport = this.isOrWasNodeInViewport(node);
                return isItVisible && isInViewport;
            },
            buildInteractionRequestParams: function (interaction, name, piece, target)
            {
                var params = '';

                if (interaction) {
                    params += 'c_i='+ encodeWrapper(interaction);
                }
                if (name) {
                    if (params) {
                        params += '&';
                    }
                    params += 'c_n='+ encodeWrapper(name);
                }
                if (piece) {
                    if (params) {
                        params += '&';
                    }
                    params += 'c_p='+ encodeWrapper(piece);
                }
                if (target) {
                    if (params) {
                        params += '&';
                    }
                    params += 'c_t='+ encodeWrapper(target);
                }

                return params;
            },
            buildImpressionRequestParams: function (name, piece, target)
            {
                var params = 'c_n=' + encodeWrapper(name) +
                    '&c_p=' + encodeWrapper(piece);

                if (target) {
                    params += '&c_t=' + encodeWrapper(target);
                }

                return params;
            },
            buildContentBlock: function (node)
            {
                if (!node) {
                    return;
                }

                var name   = this.findContentName(node);
                var piece  = this.findContentPiece(node);
                var target = this.findContentTarget(node);

                name   = this.trim(name);
                piece  = this.trim(piece);
                target = this.trim(target);

                return {
                    name: name || 'Unknown',
                    piece: piece || 'Unknown',
                    target: target || ''
                };
            },
            collectContent: function (contentNodes)
            {
                if (!contentNodes || !contentNodes.length) {
                    return [];
                }

                var contents = [];

                var index, contentBlock;
                for (index = 0; index < contentNodes.length; index++) {
                    contentBlock = this.buildContentBlock(contentNodes[index]);
                    if (isDefined(contentBlock)) {
                        contents.push(contentBlock);
                    }
                }

                return contents;
            },
            setLocation: function (location)
            {
                this.location = location;
            },
            getLocation: function ()
            {
                var locationAlias = this.location || windowAlias.location;

                if (!locationAlias.origin) {
                    locationAlias.origin = locationAlias.protocol + "//" + locationAlias.hostname + (locationAlias.port ? ':' + locationAlias.port: '');
                }

                return locationAlias;
            },
            toAbsoluteUrl: function (url)
            {
                if ((!url || String(url) !== url) && url !== '') {
                    // we only handle strings
                    return url;
                }

                if ('' === url) {
                    return this.getLocation().href;
                }

                // Eg //example.com/test.jpg
                if (url.search(/^\/\//) !== -1) {
                    return this.getLocation().protocol + url;
                }

                // Eg http://example.com/test.jpg
                if (url.search(/:\/\//) !== -1) {
                    return url;
                }

                // Eg #test.jpg
                if (0 === url.indexOf('#')) {
                    return this.getLocation().origin + this.getLocation().pathname + url;
                }

                // Eg ?x=5
                if (0 === url.indexOf('?')) {
                    return this.getLocation().origin + this.getLocation().pathname + url;
                }

                // Eg mailto:x@y.z tel:012345, ... market:... sms:..., javasript:... ecmascript: ... and many more
                if (0 === url.search('^[a-zA-Z]{2,11}:')) {
                    return url;
                }

                // Eg /test.jpg
                if (url.search(/^\//) !== -1) {
                    return this.getLocation().origin + url;
                }

                // Eg test.jpg
                var regexMatchDir = '(.*\/)';
                var base = this.getLocation().origin + this.getLocation().pathname.match(new RegExp(regexMatchDir))[0];
                return base + url;
            },
            isUrlToCurrentDomain: function (url) {

                var absoluteUrl = this.toAbsoluteUrl(url);

                if (!absoluteUrl) {
                    return false;
                }

                var origin = this.getLocation().origin;
                if (origin === absoluteUrl) {
                    return true;
                }

                if (0 === String(absoluteUrl).indexOf(origin)) {
                    if (':' === String(absoluteUrl).substr(origin.length, 1)) {
                        return false; // url has port whereas origin has not => different URL
                    }

                    return true;
                }

                return false;
            },
            setHrefAttribute: function (node, url)
            {
                if (!node || !url) {
                    return;
                }

                query.setAnyAttribute(node, 'href', url);
            },
            shouldIgnoreInteraction: function (targetNode)
            {
                var hasAttr  = query.hasNodeAttribute(targetNode, this.CONTENT_IGNOREINTERACTION_ATTR);
                var hasClass = query.hasNodeCssClass(targetNode, this.CONTENT_IGNOREINTERACTION_CLASS);
                return hasAttr || hasClass;
            }
        };

        /************************************************************
         * Page Overlay
         ************************************************************/

        function getPiwikUrlForOverlay(trackerUrl, apiUrl) {
            if (apiUrl) {
                return apiUrl;
            }

            trackerUrl = content.toAbsoluteUrl(trackerUrl);

            // if eg http://www.example.com/js/tracker.php?version=232323 => http://www.example.com/js/tracker.php
            if (stringContains(trackerUrl, '?')) {
                var posQuery = trackerUrl.indexOf('?');
                trackerUrl   = trackerUrl.slice(0, posQuery);
            }

            if (stringEndsWith(trackerUrl, 'matomo.php')) {
                // if eg without domain or path "matomo.php" => ''
                trackerUrl = removeCharactersFromEndOfString(trackerUrl, 'matomo.php'.length);
            } else if (stringEndsWith(trackerUrl, 'piwik.php')) {
                // if eg without domain or path "piwik.php" => ''
                trackerUrl = removeCharactersFromEndOfString(trackerUrl, 'piwik.php'.length);
            } else if (stringEndsWith(trackerUrl, '.php')) {
                // if eg http://www.example.com/js/piwik.php => http://www.example.com/js/
                // or if eg http://www.example.com/tracker.php => http://www.example.com/
                var lastSlash = trackerUrl.lastIndexOf('/');
                var includeLastSlash = 1;
                trackerUrl = trackerUrl.slice(0, lastSlash + includeLastSlash);
            }

            // if eg http://www.example.com/js/ => http://www.example.com/ (when not minified Piwik JS loaded)
            if (stringEndsWith(trackerUrl, '/js/')) {
                trackerUrl = removeCharactersFromEndOfString(trackerUrl, 'js/'.length);
            }

            // http://www.example.com/
            return trackerUrl;
        }

        /*
         * Check whether this is a page overlay session
         *
         * @return boolean
         *
         * {@internal side-effect: modifies window.name }}
         */
        function isOverlaySession(configTrackerSiteId) {
            var windowName = 'Piwik_Overlay';

            // check whether we were redirected from the piwik overlay plugin
            var referrerRegExp = new RegExp('index\\.php\\?module=Overlay&action=startOverlaySession'
                + '&idSite=([0-9]+)&period=([^&]+)&date=([^&]+)(&segment=.*)?$');

            var match = referrerRegExp.exec(documentAlias.referrer);

            if (match) {
                // check idsite
                var idsite = match[1];

                if (idsite !== String(configTrackerSiteId)) {
                    return false;
                }

                // store overlay session info in window name
                var period = match[2],
                    date = match[3],
                    segment = match[4];

                if (!segment) {
                    segment = '';
                } else if (segment.indexOf('&segment=') === 0) {
                    segment = segment.substr('&segment='.length);
                }

                windowAlias.name = windowName + '###' + period + '###' + date + '###' + segment;
            }

            // retrieve and check data from window name
            var windowNameParts = windowAlias.name.split('###');

            return windowNameParts.length === 4 && windowNameParts[0] === windowName;
        }

        /*
         * Inject the script needed for page overlay
         */
        function injectOverlayScripts(configTrackerUrl, configApiUrl, configTrackerSiteId) {
            var windowNameParts = windowAlias.name.split('###'),
                period = windowNameParts[1],
                date = windowNameParts[2],
                segment = windowNameParts[3],
                piwikUrl = getPiwikUrlForOverlay(configTrackerUrl, configApiUrl);

            loadScript(
                piwikUrl + 'plugins/Overlay/client/client.js?v=1',
                function () {
                    Piwik_Overlay_Client.initialize(piwikUrl, configTrackerSiteId, period, date, segment);
                }
            );
        }

        function isInsideAnIframe () {
            var frameElement;

            try {
                // If the parent window has another origin, then accessing frameElement
                // throws an Error in IE. see issue #10105.
                frameElement = windowAlias.frameElement;
            } catch(e) {
                // When there was an Error, then we know we are inside an iframe.
                return true;
            }

            if (isDefined(frameElement)) {
                return (frameElement && String(frameElement.nodeName).toLowerCase() === 'iframe') ? true : false;
            }

            try {
                return windowAlias.self !== windowAlias.top;
            } catch (e2) {
                return true;
            }
        }

        /************************************************************
         * End Page Overlay
         ************************************************************/

        /*
         * Piwik Tracker class
         *
         * trackerUrl and trackerSiteId are optional arguments to the constructor
         *
         * See: Tracker.setTrackerUrl() and Tracker.setSiteId()
         */
        function Tracker(trackerUrl, siteId) {

            /************************************************************
             * Private members
             ************************************************************/

            var
                /*<DEBUG>*/
                /*
                 * registered test hooks
                 */
                registeredHooks = {},
                /*</DEBUG>*/

                trackerInstance = this,

                // constants
                CONSENT_COOKIE_NAME = 'mtm_consent',
                CONSENT_REMOVED_COOKIE_NAME = 'mtm_consent_removed',

                // Current URL and Referrer URL
                locationArray = urlFixup(documentAlias.domain, windowAlias.location.href, getReferrer()),
                domainAlias = domainFixup(locationArray[0]),
                locationHrefAlias = safeDecodeWrapper(locationArray[1]),
                configReferrerUrl = safeDecodeWrapper(locationArray[2]),

                enableJSErrorTracking = false,

                defaultRequestMethod = 'GET',

                // Request method (GET or POST)
                configRequestMethod = defaultRequestMethod,

                defaultRequestContentType = 'application/x-www-form-urlencoded; charset=UTF-8',

                // Request Content-Type header value; applicable when POST request method is used for submitting tracking events
                configRequestContentType = defaultRequestContentType,

                // Tracker URL
                configTrackerUrl = trackerUrl || '',

                // API URL (only set if it differs from the Tracker URL)
                configApiUrl = '',

                // This string is appended to the Tracker URL Request (eg. to send data that is not handled by the existing setters/getters)
                configAppendToTrackingUrl = '',

                // Site ID
                configTrackerSiteId = siteId || '',

                // User ID
                configUserId = '',

                // Visitor UUID
                visitorUUID = '',

                // Document URL
                configCustomUrl,

                // Document title
                configTitle = '',

                // Extensions to be treated as download links
                configDownloadExtensions = ['7z','aac','apk','arc','arj','asf','asx','avi','azw3','bin','csv','deb','dmg','doc','docx','epub','exe','flv','gif','gz','gzip','hqx','ibooks','jar','jpg','jpeg','js','mobi','mp2','mp3','mp4','mpg','mpeg','mov','movie','msi','msp','odb','odf','odg','ods','odt','ogg','ogv','pdf','phps','png','ppt','pptx','qt','qtm','ra','ram','rar','rpm','sea','sit','tar','tbz','tbz2','bz','bz2','tgz','torrent','txt','wav','wma','wmv','wpd','xls','xlsx','xml','z','zip'],

                // Hosts or alias(es) to not treat as outlinks
                configHostsAlias = [domainAlias],

                // HTML anchor element classes to not track
                configIgnoreClasses = [],

                // HTML anchor element classes to treat as downloads
                configDownloadClasses = [],

                // HTML anchor element classes to treat at outlinks
                configLinkClasses = [],

                // Maximum delay to wait for web bug image to be fetched (in milliseconds)
                configTrackerPause = 500,

                // If enabled, always use sendBeacon if the browser supports it
                configAlwaysUseSendBeacon = false,

                // Minimum visit time after initial page view (in milliseconds)
                configMinimumVisitTime,

                // Recurring heart beat after initial ping (in milliseconds)
                configHeartBeatDelay,

                // alias to circumvent circular function dependency (JSLint requires this)
                heartBeatPingIfActivityAlias,

                // Disallow hash tags in URL
                configDiscardHashTag,

                // Custom data
                configCustomData,

                // Campaign names
                configCampaignNameParameters = [ 'pk_campaign', 'piwik_campaign', 'utm_campaign', 'utm_source', 'utm_medium' ],

                // Campaign keywords
                configCampaignKeywordParameters = [ 'pk_kwd', 'piwik_kwd', 'utm_term' ],

                // First-party cookie name prefix
                configCookieNamePrefix = '_pk_',

                // the URL parameter that will store the visitorId if cross domain linking is enabled
                // pk_vid = visitor ID
                // first part of this URL parameter will be 16 char visitor Id.
                // The second part is the 10 char current timestamp and the third and last part will be a 6 characters deviceId
                // timestamp is needed to prevent reusing the visitorId when the URL is shared. The visitorId will be
                // only reused if the timestamp is less than 45 seconds old.
                // deviceId parameter is needed to prevent reusing the visitorId when the URL is shared. The visitorId
                // will be only reused if the device is still the same when opening the link.
                // VDI = visitor device identifier
                configVisitorIdUrlParameter = 'pk_vid',

                // Cross domain linking, the visitor ID is transmitted only in the 180 seconds following the click.
                configVisitorIdUrlParameterTimeoutInSeconds = 180,

                // First-party cookie domain
                // User agent defaults to origin hostname
                configCookieDomain,

                // First-party cookie path
                // Default is user agent defined.
                configCookiePath,

                // Whether to use "Secure" cookies that only work over SSL
                configCookieIsSecure = false,

                // First-party cookies are disabled
                configCookiesDisabled = false,

                // Do Not Track
                configDoNotTrack,

                // Count sites which are pre-rendered
                configCountPreRendered,

                // Do we attribute the conversion to the first referrer or the most recent referrer?
                configConversionAttributionFirstReferrer,

                // Life of the visitor cookie (in milliseconds)
                configVisitorCookieTimeout = 33955200000, // 13 months (365 days + 28days)

                // Life of the session cookie (in milliseconds)
                configSessionCookieTimeout = 1800000, // 30 minutes

                // Life of the referral cookie (in milliseconds)
                configReferralCookieTimeout = 15768000000, // 6 months

                // Is performance tracking enabled
                configPerformanceTrackingEnabled = true,

                // Generation time set from the server
                configPerformanceGenerationTime = 0,

                // Whether Custom Variables scope "visit" should be stored in a cookie during the time of the visit
                configStoreCustomVariablesInCookie = false,

                // Custom Variables read from cookie, scope "visit"
                customVariables = false,

                configCustomRequestContentProcessing,

                // Custom Variables, scope "page"
                customVariablesPage = {},

                // Custom Variables, scope "event"
                customVariablesEvent = {},

                // Custom Dimensions (can be any scope)
                customDimensions = {},

                // Custom Variables names and values are each truncated before being sent in the request or recorded in the cookie
                customVariableMaximumLength = 200,

                // Ecommerce items
                ecommerceItems = {},

                // Browser features via client-side data collection
                browserFeatures = {},

                // Keeps track of previously tracked content impressions
                trackedContentImpressions = [],
                isTrackOnlyVisibleContentEnabled = false,

                // Guard to prevent empty visits see #6415. If there is a new visitor and the first 2 (or 3 or 4)
                // tracking requests are at nearly same time (eg trackPageView and trackContentImpression) 2 or more
                // visits will be created
                timeNextTrackingRequestCanBeExecutedImmediately = false,

                // Guard against installing the link tracker more than once per Tracker instance
                linkTrackingInstalled = false,
                linkTrackingEnabled = false,
                crossDomainTrackingEnabled = false,

                // Guard against installing the activity tracker more than once per Tracker instance
                heartBeatSetUp = false,

                // bool used to detect whether this browser window had focus at least once. So far we cannot really
                // detect this 100% correct for an iframe so whenever Piwik is loaded inside an iframe we presume
                // the window had focus at least once.
                hadWindowFocusAtLeastOnce = isInsideAnIframe(),

                // Timestamp of last tracker request sent to Piwik
                lastTrackerRequestTime = null,

                // Handle to the current heart beat timeout
                heartBeatTimeout,

                // Internal state of the pseudo click handler
                lastButton,
                lastTarget,

                // Hash function
                hash = sha1,

                // Domain hash value
                domainHash,

                configIdPageView,

                // we measure how many pageviews have been tracked so plugins can use it to eg detect if a
                // pageview was already tracked or not
                numTrackedPageviews = 0,

                configCookiesToDelete = ['id', 'ses', 'cvar', 'ref'],

                // whether requireConsent() was called or not
                configConsentRequired = false,

                // we always have the concept of consent. by default consent is assumed unless the end user removes it,
                // or unless a matomo user explicitly requires consent (via requireConsent())
                configHasConsent = null, // initialized below

                // holds all pending tracking requests that have not been tracked because we need consent
                consentRequestsQueue = [],

                // a unique ID for this tracker during this request
                uniqueTrackerId = trackerIdCounter++;

            // Document title
            try {
                configTitle = documentAlias.title;
            } catch(e) {
                configTitle = '';
            }

            /*
             * Set cookie value
             */
            function setCookie(cookieName, value, msToExpire, path, domain, isSecure) {
                if (configCookiesDisabled) {
                    return;
                }

                var expiryDate;

                // relative time to expire in milliseconds
                if (msToExpire) {
                    expiryDate = new Date();
                    expiryDate.setTime(expiryDate.getTime() + msToExpire);
                }

                documentAlias.cookie = cookieName + '=' + encodeWrapper(value) +
                    (msToExpire ? ';expires=' + expiryDate.toGMTString() : '') +
                    ';path=' + (path || '/') +
                    (domain ? ';domain=' + domain : '') +
                    (isSecure ? ';secure' : '');
            }

            /*
             * Get cookie value
             */
            function getCookie(cookieName) {
                if (configCookiesDisabled) {
                    return 0;
                }

                var cookiePattern = new RegExp('(^|;)[ ]*' + cookieName + '=([^;]*)'),
                    cookieMatch = cookiePattern.exec(documentAlias.cookie);

                return cookieMatch ? decodeWrapper(cookieMatch[2]) : 0;
            }

            configHasConsent = !getCookie(CONSENT_REMOVED_COOKIE_NAME);

            /*
             * Removes hash tag from the URL
             *
             * URLs are purified before being recorded in the cookie,
             * or before being sent as GET parameters
             */
            function purify(url) {
                var targetPattern;

                // we need to remove this parameter here, they wouldn't be removed in Piwik tracker otherwise eg
                // for outlinks or referrers
                url = removeUrlParameter(url, configVisitorIdUrlParameter);

                if (configDiscardHashTag) {
                    targetPattern = new RegExp('#.*');

                    return url.replace(targetPattern, '');
                }

                return url;
            }

            /*
             * Resolve relative reference
             *
             * Note: not as described in rfc3986 section 5.2
             */
            function resolveRelativeReference(baseUrl, url) {
                var protocol = getProtocolScheme(url),
                    i;

                if (protocol) {
                    return url;
                }

                if (url.slice(0, 1) === '/') {
                    return getProtocolScheme(baseUrl) + '://' + getHostName(baseUrl) + url;
                }

                baseUrl = purify(baseUrl);

                i = baseUrl.indexOf('?');
                if (i >= 0) {
                    baseUrl = baseUrl.slice(0, i);
                }

                i = baseUrl.lastIndexOf('/');
                if (i !== baseUrl.length - 1) {
                    baseUrl = baseUrl.slice(0, i + 1);
                }

                return baseUrl + url;
            }

            function isSameHost (hostName, alias) {
                var offset;

                hostName = String(hostName).toLowerCase();
                alias = String(alias).toLowerCase();

                if (hostName === alias) {
                    return true;
                }

                if (alias.slice(0, 1) === '.') {
                    if (hostName === alias.slice(1)) {
                        return true;
                    }

                    offset = hostName.length - alias.length;

                    if ((offset > 0) && (hostName.slice(offset) === alias)) {
                        return true;
                    }
                }

                return false;
            }

            /*
             * Extract pathname from URL. element.pathname is actually supported by pretty much all browsers including
             * IE6 apart from some rare very old ones
             */
            function getPathName(url) {
                var parser = document.createElement('a');
                if (url.indexOf('//') !== 0 && url.indexOf('http') !== 0) {
                    if (url.indexOf('*') === 0) {
                        url = url.substr(1);
                    }
                    if (url.indexOf('.') === 0) {
                        url = url.substr(1);
                    }
                    url = 'http://' + url;
                }

                parser.href = content.toAbsoluteUrl(url);

                if (parser.pathname) {
                    return parser.pathname;
                }

                return '';
            }

            function isSitePath (path, pathAlias)
            {
                if(!stringStartsWith(pathAlias, '/')) {
                    pathAlias = '/' + pathAlias;
                }

                if(!stringStartsWith(path, '/')) {
                    path = '/' + path;
                }

                var matchesAnyPath = (pathAlias === '/' || pathAlias === '/*');

                if (matchesAnyPath) {
                    return true;
                }

                if (path === pathAlias) {
                    return true;
                }

                pathAlias = String(pathAlias).toLowerCase();
                path = String(path).toLowerCase();

                // wildcard path support
                if(stringEndsWith(pathAlias, '*')) {
                    // remove the final '*' before comparing
                    pathAlias = pathAlias.slice(0, -1);

                    // Note: this is almost duplicated from just few lines above
                    matchesAnyPath = (!pathAlias || pathAlias === '/');

                    if (matchesAnyPath) {
                        return true;
                    }

                    if (path === pathAlias) {
                        return true;
                    }

                    // wildcard match
                    return path.indexOf(pathAlias) === 0;
                }

                // we need to append slashes so /foobarbaz won't match a site /foobar
                if (!stringEndsWith(path, '/')) {
                    path += '/';
                }

                if (!stringEndsWith(pathAlias, '/')) {
                    pathAlias += '/';
                }

                return path.indexOf(pathAlias) === 0;
            }

            /**
             * Whether the specified domain name and path belong to any of the alias domains (eg. set via setDomains).
             *
             * Note: this function is used to determine whether a click on a URL will be considered an "Outlink".
             *
             * @param host
             * @param path
             * @returns {boolean}
             */
            function isSiteHostPath(host, path)
            {
                var i,
                    alias,
                    configAlias,
                    aliasHost,
                    aliasPath;

                for (i = 0; i < configHostsAlias.length; i++) {
                    aliasHost = domainFixup(configHostsAlias[i]);
                    aliasPath = getPathName(configHostsAlias[i]);

                    if (isSameHost(host, aliasHost) && isSitePath(path, aliasPath)) {
                        return true;
                    }
                }

                return false;
            }

            /*
             * Is the host local? (i.e., not an outlink)
             */
            function isSiteHostName(hostName) {

                var i,
                    alias,
                    offset;

                for (i = 0; i < configHostsAlias.length; i++) {
                    alias = domainFixup(configHostsAlias[i].toLowerCase());

                    if (hostName === alias) {
                        return true;
                    }

                    if (alias.slice(0, 1) === '.') {
                        if (hostName === alias.slice(1)) {
                            return true;
                        }

                        offset = hostName.length - alias.length;

                        if ((offset > 0) && (hostName.slice(offset) === alias)) {
                            return true;
                        }
                    }
                }

                return false;
            }

            /*
             * Send image request to Piwik server using GET.
             * The infamous web bug (or beacon) is a transparent, single pixel (1x1) image
             */
            function getImage(request, callback) {
                // make sure to actually load an image so callback gets invoked
                request = request.replace("send_image=0","send_image=1");

                var image = new Image(1, 1);
                image.onload = function () {
                    iterator = 0; // To avoid JSLint warning of empty block
                    if (typeof callback === 'function') { callback(); }
                };
                image.src = configTrackerUrl + (configTrackerUrl.indexOf('?') < 0 ? '?' : '&') + request;
            }

            function supportsSendBeacon()
            {
                return 'object' === typeof navigatorAlias
                    && 'function' === typeof navigatorAlias.sendBeacon
                    && 'function' === typeof Blob;
            }

            function sendPostRequestViaSendBeacon(request)
            {
                var isSupported = supportsSendBeacon();

                if (!isSupported) {
                    return false;
                }

                var headers = {type: 'application/x-www-form-urlencoded; charset=UTF-8'};
                var success = false;

                var url = configTrackerUrl;

                try {
                    var blob = new Blob([request], headers);

                    if (request.length <= 2000) {
                        blob = new Blob([], headers);
                        url = url + (url.indexOf('?') < 0 ? '?' : '&') + request;
                    }

                    success = navigatorAlias.sendBeacon(url, blob);
                    // returns true if the user agent is able to successfully queue the data for transfer,
                    // Otherwise it returns false and we need to try the regular way

                } catch (e) {
                    return false;
                }

                return success;
            }

            /*
             * POST request to Piwik server using XMLHttpRequest.
             */
            function sendXmlHttpRequest(request, callback, fallbackToGet) {
                if (!isDefined(fallbackToGet) || null === fallbackToGet) {
                    fallbackToGet = true;
                }

                if (isPageUnloading && sendPostRequestViaSendBeacon(request)) {
                    return;
                }

                setTimeout(function () {
                    // we execute it with a little delay in case the unload event occurred just after sending this request
                    // this is to avoid the following behaviour: Eg on form submit a tracking request is sent via POST
                    // in this method. Then a few ms later the browser wants to navigate to the new page and the unload
                    // event occurrs and the browser cancels the just triggered POST request. This causes or fallback
                    // method to be triggered and we execute the same request again (either as fallbackGet or sendBeacon).
                    // The problem is that we do not know whether the inital POST request was already fully transferred
                    // to the server or not when the onreadystatechange callback is executed and we might execute the
                    // same request a second time. To avoid this, we delay the actual execution of this POST request just
                    // by 50ms which gives it usually enough time to detect the unload event in most cases.

                    if (isPageUnloading && sendPostRequestViaSendBeacon(request)) {
                        return;
                    }
                    var sentViaBeacon;

                    try {
                        // we use the progid Microsoft.XMLHTTP because
                        // IE5.5 included MSXML 2.5; the progid MSXML2.XMLHTTP
                        // is pinned to MSXML2.XMLHTTP.3.0
                        var xhr = windowAlias.XMLHttpRequest
                            ? new windowAlias.XMLHttpRequest()
                            : windowAlias.ActiveXObject
                                ? new ActiveXObject('Microsoft.XMLHTTP')
                                : null;

                        xhr.open('POST', configTrackerUrl, true);

                        // fallback on error
                        xhr.onreadystatechange = function () {
                            if (this.readyState === 4 && !(this.status >= 200 && this.status < 300)) {
                                var sentViaBeacon = isPageUnloading && sendPostRequestViaSendBeacon(request);

                                if (!sentViaBeacon && fallbackToGet) {
                                    getImage(request, callback);
                                }
                            } else {
                                if (this.readyState === 4 && (typeof callback === 'function')) { callback(); }
                            }
                        };

                        xhr.setRequestHeader('Content-Type', configRequestContentType);

                        xhr.send(request);
                    } catch (e) {
                        sentViaBeacon = isPageUnloading && sendPostRequestViaSendBeacon(request);
                        if (!sentViaBeacon && fallbackToGet) {
                            getImage(request, callback);
                        }
                    }
                }, 50);

            }

            function setExpireDateTime(delay) {

                var now  = new Date();
                var time = now.getTime() + delay;

                if (!expireDateTime || time > expireDateTime) {
                    expireDateTime = time;
                }
            }

            /*
             * Sets up the heart beat timeout.
             */
            function heartBeatUp(delay) {
                if (heartBeatTimeout
                    || !configHeartBeatDelay
                    || !configHasConsent
                ) {
                    return;
                }

                heartBeatTimeout = setTimeout(function heartBeat() {
                    heartBeatTimeout = null;

                    if (!hadWindowFocusAtLeastOnce) {
                        // if browser does not support .hasFocus (eg IE5), we assume that the window has focus.
                        hadWindowFocusAtLeastOnce = (!documentAlias.hasFocus || documentAlias.hasFocus());
                    }

                    if (!hadWindowFocusAtLeastOnce) {
                        // only send a ping if the tab actually had focus at least once. For example do not send a ping
                        // if window was opened via "right click => open in new window" and never had focus see #9504
                        heartBeatUp(configHeartBeatDelay);
                        return;
                    }

                    if (heartBeatPingIfActivityAlias()) {
                        return;
                    }

                    var now = new Date(),
                        heartBeatDelay = configHeartBeatDelay - (now.getTime() - lastTrackerRequestTime);
                    // sanity check
                    heartBeatDelay = Math.min(configHeartBeatDelay, heartBeatDelay);
                    heartBeatUp(heartBeatDelay);
                }, delay || configHeartBeatDelay);
            }

            /*
             * Removes the heart beat timeout.
             */
            function heartBeatDown() {
                if (!heartBeatTimeout) {
                    return;
                }

                clearTimeout(heartBeatTimeout);
                heartBeatTimeout = null;
            }

            function heartBeatOnFocus() {
                hadWindowFocusAtLeastOnce = true;

                // since it's possible for a user to come back to a tab after several hours or more, we try to send
                // a ping if the page is active. (after the ping is sent, the heart beat timeout will be set)
                if (heartBeatPingIfActivityAlias()) {
                    return;
                }

                heartBeatUp();
            }

            function heartBeatOnBlur() {
                heartBeatDown();
            }

            /*
             * Setup event handlers and timeout for initial heart beat.
             */
            function setUpHeartBeat() {
                if (heartBeatSetUp
                    || !configHeartBeatDelay
                ) {
                    return;
                }

                heartBeatSetUp = true;

                addEventListener(windowAlias, 'focus', heartBeatOnFocus);
                addEventListener(windowAlias, 'blur', heartBeatOnBlur);

                heartBeatUp();
            }

            function makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(callback)
            {
                var now     = new Date();
                var timeNow = now.getTime();

                lastTrackerRequestTime = timeNow;

                if (timeNextTrackingRequestCanBeExecutedImmediately && timeNow < timeNextTrackingRequestCanBeExecutedImmediately) {
                    // we are in the time frame shortly after the first request. we have to delay this request a bit to make sure
                    // a visitor has been created meanwhile.

                    var timeToWait = timeNextTrackingRequestCanBeExecutedImmediately - timeNow;

                    setTimeout(callback, timeToWait);
                    setExpireDateTime(timeToWait + 50); // set timeout is not necessarily executed at timeToWait so delay a bit more
                    timeNextTrackingRequestCanBeExecutedImmediately += 50; // delay next tracking request by further 50ms to next execute them at same time

                    return;
                }

                if (timeNextTrackingRequestCanBeExecutedImmediately === false) {
                    // it is the first request, we want to execute this one directly and delay all the next one(s) within a delay.
                    // All requests after this delay can be executed as usual again
                    var delayInMs = 800;
                    timeNextTrackingRequestCanBeExecutedImmediately = timeNow + delayInMs;
                }

                callback();
            }

            /*
             * Send request
             */
            function sendRequest(request, delay, callback) {
                if (!configHasConsent) {
                    consentRequestsQueue.push(request);
                    return;
                }
                if (!configDoNotTrack && request) {
                    if (configConsentRequired && configHasConsent) { // send a consent=1 when explicit consent is given for the apache logs
                        request += '&consent=1';
                    }

                    makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(function () {

                        if (configAlwaysUseSendBeacon && sendPostRequestViaSendBeacon(request)) {
                            setExpireDateTime(100);
                            if (typeof callback === 'function') {
                                callback();
                            }
                            return;
                        }

                        if (configRequestMethod === 'POST' || String(request).length > 2000) {
                            sendXmlHttpRequest(request, callback);
                        } else {
                            getImage(request, callback);
                        }

                        setExpireDateTime(delay);
                    });
                }
                if (!heartBeatSetUp) {
                    setUpHeartBeat(); // setup window events too, but only once
                } else {
                    heartBeatUp();
                }
            }

            function canSendBulkRequest(requests)
            {
                if (configDoNotTrack) {
                    return false;
                }

                return (requests && requests.length);
            }

            function arrayChunk(theArray, chunkSize)
            {
                if (!chunkSize || chunkSize >= theArray.length) {
                    return [theArray];
                }

                var index = 0;
                var arrLength = theArray.length;
                var chunks = [];

                for (index; index < arrLength; index += chunkSize) {
                    chunks.push(theArray.slice(index, index + chunkSize));
                }

                return chunks;
            }

            /*
             * Send requests using bulk
             */
            function sendBulkRequest(requests, delay)
            {
                if (!canSendBulkRequest(requests)) {
                    return;
                }

                if (!configHasConsent) {
                    consentRequestsQueue.push(requests);
                    return;
                }

                makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(function () {
                    var chunks = arrayChunk(requests, 50);

                    var i = 0, bulk;
                    for (i; i < chunks.length; i++) {
                        bulk = '{"requests":["?' + chunks[i].join('","?') + '"]}';
                        sendXmlHttpRequest(bulk, null, false);
                    }

                    setExpireDateTime(delay);
                });
            }

            /*
             * Get cookie name with prefix and domain hash
             */
            function getCookieName(baseName) {
                // NOTE: If the cookie name is changed, we must also update the PiwikTracker.php which
                // will attempt to discover first party cookies. eg. See the PHP Client method getVisitorId()
                return configCookieNamePrefix + baseName + '.' + configTrackerSiteId + '.' + domainHash;
            }

            /*
             * Does browser have cookies enabled (for this site)?
             */
            function hasCookies() {
                if (configCookiesDisabled) {
                    return '0';
                }

                if (!isDefined(navigatorAlias.cookieEnabled)) {
                    var testCookieName = getCookieName('testcookie');
                    setCookie(testCookieName, '1');

                    return getCookie(testCookieName) === '1' ? '1' : '0';
                }

                return navigatorAlias.cookieEnabled ? '1' : '0';
            }

            /*
             * Update domain hash
             */
            function updateDomainHash() {
                domainHash = hash((configCookieDomain || domainAlias) + (configCookiePath || '/')).slice(0, 4); // 4 hexits = 16 bits
            }

            /*
             * Inits the custom variables object
             */
            function getCustomVariablesFromCookie() {
                var cookieName = getCookieName('cvar'),
                    cookie = getCookie(cookieName);

                if (cookie.length) {
                    cookie = JSON_PIWIK.parse(cookie);

                    if (isObject(cookie)) {
                        return cookie;
                    }
                }

                return {};
            }

            /*
             * Lazy loads the custom variables from the cookie, only once during this page view
             */
            function loadCustomVariables() {
                if (customVariables === false) {
                    customVariables = getCustomVariablesFromCookie();
                }
            }

            /*
             * Generate a pseudo-unique ID to fingerprint this user
             * 16 hexits = 64 bits
             * note: this isn't a RFC4122-compliant UUID
             */
            function generateRandomUuid() {
                return hash(
                    (navigatorAlias.userAgent || '') +
                    (navigatorAlias.platform || '') +
                    JSON_PIWIK.stringify(browserFeatures) +
                    (new Date()).getTime() +
                    Math.random()
                ).slice(0, 16);
            }

            function generateBrowserSpecificId() {
                return hash(
                    (navigatorAlias.userAgent || '') +
                    (navigatorAlias.platform || '') +
                    JSON_PIWIK.stringify(browserFeatures)).slice(0, 6);
            }

            function getCurrentTimestampInSeconds()
            {
                return Math.floor((new Date()).getTime() / 1000);
            }

            function makeCrossDomainDeviceId()
            {
                var timestamp = getCurrentTimestampInSeconds();
                var browserId = generateBrowserSpecificId();
                var deviceId = String(timestamp) + browserId;

                return deviceId;
            }

            function isSameCrossDomainDevice(deviceIdFromUrl)
            {
                deviceIdFromUrl = String(deviceIdFromUrl);

                var thisBrowserId = generateBrowserSpecificId();
                var lengthBrowserId = thisBrowserId.length;

                var browserIdInUrl = deviceIdFromUrl.substr(-1 * lengthBrowserId, lengthBrowserId);
                var timestampInUrl = parseInt(deviceIdFromUrl.substr(0, deviceIdFromUrl.length - lengthBrowserId), 10);

                if (timestampInUrl && browserIdInUrl && browserIdInUrl === thisBrowserId) {
                    // we only reuse visitorId when used on same device / browser

                    var currentTimestampInSeconds = getCurrentTimestampInSeconds();

                    if (configVisitorIdUrlParameterTimeoutInSeconds <= 0) {
                        return true;
                    }
                    if (currentTimestampInSeconds >= timestampInUrl
                        && currentTimestampInSeconds <= (timestampInUrl + configVisitorIdUrlParameterTimeoutInSeconds)) {
                        // we only use visitorId if it was generated max 180 seconds ago
                        return true;
                    }
                }

                return false;
            }

            function getVisitorIdFromUrl(url) {
                if (!crossDomainTrackingEnabled) {
                    return '';
                }

                // problem different timezone or when the time on the computer is not set correctly it may re-use
                // the same visitorId again. therefore we also have a factor like hashed user agent to reduce possible
                // activation of a visitorId on other device
                var visitorIdParam = getUrlParameter(url, configVisitorIdUrlParameter);

                if (!visitorIdParam) {
                    return '';
                }

                visitorIdParam = String(visitorIdParam);

                var pattern = new RegExp("^[a-zA-Z0-9]+$");

                if (visitorIdParam.length === 32 && pattern.test(visitorIdParam)) {
                    var visitorDevice = visitorIdParam.substr(16, 32);

                    if (isSameCrossDomainDevice(visitorDevice)) {
                        var visitorId = visitorIdParam.substr(0, 16);
                        return visitorId;
                    }
                }

                return '';
            }

            /*
             * Load visitor ID cookie
             */
            function loadVisitorIdCookie() {

                if (!visitorUUID) {
                    // we are using locationHrefAlias and not currentUrl on purpose to for sure get the passed URL parameters
                    // from original URL
                    visitorUUID = getVisitorIdFromUrl(locationHrefAlias);
                }

                var now = new Date(),
                    nowTs = Math.round(now.getTime() / 1000),
                    visitorIdCookieName = getCookieName('id'),
                    id = getCookie(visitorIdCookieName),
                    cookieValue,
                    uuid;

                // Visitor ID cookie found
                if (id) {
                    cookieValue = id.split('.');

                    // returning visitor flag
                    cookieValue.unshift('0');

                    if(visitorUUID.length) {
                        cookieValue[1] = visitorUUID;
                    }
                    return cookieValue;
                }

                if(visitorUUID.length) {
                    uuid = visitorUUID;
                } else if ('0' === hasCookies()){
                    uuid = '';
                } else {
                    uuid = generateRandomUuid();
                }

                // No visitor ID cookie, let's create a new one
                cookieValue = [
                    // new visitor
                    '1',

                    // uuid
                    uuid,

                    // creation timestamp - seconds since Unix epoch
                    nowTs,

                    // visitCount - 0 = no previous visit
                    0,

                    // current visit timestamp
                    nowTs,

                    // last visit timestamp - blank = no previous visit
                    '',

                    // last ecommerce order timestamp
                    ''
                ];

                return cookieValue;
            }


            /**
             * Loads the Visitor ID cookie and returns a named array of values
             */
            function getValuesFromVisitorIdCookie() {
                var cookieVisitorIdValue = loadVisitorIdCookie(),
                    newVisitor = cookieVisitorIdValue[0],
                    uuid = cookieVisitorIdValue[1],
                    createTs = cookieVisitorIdValue[2],
                    visitCount = cookieVisitorIdValue[3],
                    currentVisitTs = cookieVisitorIdValue[4],
                    lastVisitTs = cookieVisitorIdValue[5];

                // case migrating from pre-1.5 cookies
                if (!isDefined(cookieVisitorIdValue[6])) {
                    cookieVisitorIdValue[6] = "";
                }

                var lastEcommerceOrderTs = cookieVisitorIdValue[6];

                return {
                    newVisitor: newVisitor,
                    uuid: uuid,
                    createTs: createTs,
                    visitCount: visitCount,
                    currentVisitTs: currentVisitTs,
                    lastVisitTs: lastVisitTs,
                    lastEcommerceOrderTs: lastEcommerceOrderTs
                };
            }

            function getRemainingVisitorCookieTimeout() {
                var now = new Date(),
                    nowTs = now.getTime(),
                    cookieCreatedTs = getValuesFromVisitorIdCookie().createTs;

                var createTs = parseInt(cookieCreatedTs, 10);
                var originalTimeout = (createTs * 1000) + configVisitorCookieTimeout - nowTs;
                return originalTimeout;
            }

            /*
             * Sets the Visitor ID cookie
             */
            function setVisitorIdCookie(visitorIdCookieValues) {

                if(!configTrackerSiteId) {
                    // when called before Site ID was set
                    return;
                }

                var now = new Date(),
                    nowTs = Math.round(now.getTime() / 1000);

                if(!isDefined(visitorIdCookieValues)) {
                    visitorIdCookieValues = getValuesFromVisitorIdCookie();
                }

                var cookieValue = visitorIdCookieValues.uuid + '.' +
                    visitorIdCookieValues.createTs + '.' +
                    visitorIdCookieValues.visitCount + '.' +
                    nowTs + '.' +
                    visitorIdCookieValues.lastVisitTs + '.' +
                    visitorIdCookieValues.lastEcommerceOrderTs;

                setCookie(getCookieName('id'), cookieValue, getRemainingVisitorCookieTimeout(), configCookiePath, configCookieDomain, configCookieIsSecure);
            }

            /*
             * Loads the referrer attribution information
             *
             * @returns array
             *  0: campaign name
             *  1: campaign keyword
             *  2: timestamp
             *  3: raw URL
             */
            function loadReferrerAttributionCookie() {
                // NOTE: if the format of the cookie changes,
                // we must also update JS tests, PHP tracker, System tests,
                // and notify other tracking clients (eg. Java) of the changes
                var cookie = getCookie(getCookieName('ref'));

                if (cookie.length) {
                    try {
                        cookie = JSON_PIWIK.parse(cookie);
                        if (isObject(cookie)) {
                            return cookie;
                        }
                    } catch (ignore) {
                        // Pre 1.3, this cookie was not JSON encoded
                    }
                }

                return [
                    '',
                    '',
                    0,
                    ''
                ];
            }

            function deleteCookie(cookieName, path, domain) {
                setCookie(cookieName, '', -86400, path, domain);
            }

            function isPossibleToSetCookieOnDomain(domainToTest)
            {
                var valueToSet = 'testvalue';
                setCookie('test', valueToSet, 10000, null, domainToTest);

                if (getCookie('test') === valueToSet) {
                    deleteCookie('test', null, domainToTest);

                    return true;
                }

                return false;
            }

            function deleteCookies() {
                var savedConfigCookiesDisabled = configCookiesDisabled;

                // Temporarily allow cookies just to delete the existing ones
                configCookiesDisabled = false;

                var index, cookieName;

                for (index = 0; index < configCookiesToDelete.length; index++) {
                    cookieName = getCookieName(configCookiesToDelete[index]);
                    if (cookieName !== CONSENT_REMOVED_COOKIE_NAME && cookieName !== CONSENT_COOKIE_NAME && 0 !== getCookie(cookieName)) {
                        deleteCookie(cookieName, configCookiePath, configCookieDomain);
                    }
                }

                configCookiesDisabled = savedConfigCookiesDisabled;
            }

            function setSiteId(siteId) {
                configTrackerSiteId = siteId;
                setVisitorIdCookie();
            }

            function sortObjectByKeys(value) {
                if (!value || !isObject(value)) {
                    return;
                }

                // Object.keys(value) is not supported by all browsers, we get the keys manually
                var keys = [];
                var key;

                for (key in value) {
                    if (Object.prototype.hasOwnProperty.call(value, key)) {
                        keys.push(key);
                    }
                }

                var normalized = {};
                keys.sort();
                var len = keys.length;
                var i;

                for (i = 0; i < len; i++) {
                    normalized[keys[i]] = value[keys[i]];
                }

                return normalized;
            }

            /**
             * Creates the session cookie
             */
            function setSessionCookie() {
                setCookie(getCookieName('ses'), '*', configSessionCookieTimeout, configCookiePath, configCookieDomain, configCookieIsSecure);
            }

            function generateUniqueId() {
                var id = '';
                var chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var charLen = chars.length;
                var i;

                for (i = 0; i < 6; i++) {
                    id += chars.charAt(Math.floor(Math.random() * charLen));
                }

                return id;
            }

            /**
             * Returns the URL to call piwik.php,
             * with the standard parameters (plugins, resolution, url, referrer, etc.).
             * Sends the pageview and browser settings with every request in case of race conditions.
             */
            function getRequest(request, customData, pluginMethod, currentEcommerceOrderTs) {
                var i,
                    now = new Date(),
                    nowTs = Math.round(now.getTime() / 1000),
                    referralTs,
                    referralUrl,
                    referralUrlMaxLength = 1024,
                    currentReferrerHostName,
                    originalReferrerHostName,
                    customVariablesCopy = customVariables,
                    cookieSessionName = getCookieName('ses'),
                    cookieReferrerName = getCookieName('ref'),
                    cookieCustomVariablesName = getCookieName('cvar'),
                    cookieSessionValue = getCookie(cookieSessionName),
                    attributionCookie = loadReferrerAttributionCookie(),
                    currentUrl = configCustomUrl || locationHrefAlias,
                    campaignNameDetected,
                    campaignKeywordDetected;

                if (configCookiesDisabled) {
                    deleteCookies();
                }

                if (configDoNotTrack) {
                    return '';
                }

                var cookieVisitorIdValues = getValuesFromVisitorIdCookie();
                if (!isDefined(currentEcommerceOrderTs)) {
                    currentEcommerceOrderTs = "";
                }

                // send charset if document charset is not utf-8. sometimes encoding
                // of urls will be the same as this and not utf-8, which will cause problems
                // do not send charset if it is utf8 since it's assumed by default in Piwik
                var charSet = documentAlias.characterSet || documentAlias.charset;

                if (!charSet || charSet.toLowerCase() === 'utf-8') {
                    charSet = null;
                }

                campaignNameDetected = attributionCookie[0];
                campaignKeywordDetected = attributionCookie[1];
                referralTs = attributionCookie[2];
                referralUrl = attributionCookie[3];

                if (!cookieSessionValue) {
                    // cookie 'ses' was not found: we consider this the start of a 'session'

                    // here we make sure that if 'ses' cookie is deleted few times within the visit
                    // and so this code path is triggered many times for one visit,
                    // we only increase visitCount once per Visit window (default 30min)
                    var visitDuration = configSessionCookieTimeout / 1000;
                    if (!cookieVisitorIdValues.lastVisitTs
                        || (nowTs - cookieVisitorIdValues.lastVisitTs) > visitDuration) {
                        cookieVisitorIdValues.visitCount++;
                        cookieVisitorIdValues.lastVisitTs = cookieVisitorIdValues.currentVisitTs;
                    }


                    // Detect the campaign information from the current URL
                    // Only if campaign wasn't previously set
                    // Or if it was set but we must attribute to the most recent one
                    // Note: we are working on the currentUrl before purify() since we can parse the campaign parameters in the hash tag
                    if (!configConversionAttributionFirstReferrer
                        || !campaignNameDetected.length) {
                        for (i in configCampaignNameParameters) {
                            if (Object.prototype.hasOwnProperty.call(configCampaignNameParameters, i)) {
                                campaignNameDetected = getUrlParameter(currentUrl, configCampaignNameParameters[i]);

                                if (campaignNameDetected.length) {
                                    break;
                                }
                            }
                        }

                        for (i in configCampaignKeywordParameters) {
                            if (Object.prototype.hasOwnProperty.call(configCampaignKeywordParameters, i)) {
                                campaignKeywordDetected = getUrlParameter(currentUrl, configCampaignKeywordParameters[i]);

                                if (campaignKeywordDetected.length) {
                                    break;
                                }
                            }
                        }
                    }

                    // Store the referrer URL and time in the cookie;
                    // referral URL depends on the first or last referrer attribution
                    currentReferrerHostName = getHostName(configReferrerUrl);
                    originalReferrerHostName = referralUrl.length ? getHostName(referralUrl) : '';

                    if (currentReferrerHostName.length && // there is a referrer
                        !isSiteHostName(currentReferrerHostName) && // domain is not the current domain
                        (!configConversionAttributionFirstReferrer || // attribute to last known referrer
                            !originalReferrerHostName.length || // previously empty
                            isSiteHostName(originalReferrerHostName))) { // previously set but in current domain
                        referralUrl = configReferrerUrl;
                    }

                    // Set the referral cookie if we have either a Referrer URL, or detected a Campaign (or both)
                    if (referralUrl.length
                        || campaignNameDetected.length) {
                        referralTs = nowTs;
                        attributionCookie = [
                            campaignNameDetected,
                            campaignKeywordDetected,
                            referralTs,
                            purify(referralUrl.slice(0, referralUrlMaxLength))
                        ];

                        setCookie(cookieReferrerName, JSON_PIWIK.stringify(attributionCookie), configReferralCookieTimeout, configCookiePath, configCookieDomain);
                    }
                }

                // build out the rest of the request
                request += '&idsite=' + configTrackerSiteId +
                    '&rec=1' +
                    '&r=' + String(Math.random()).slice(2, 8) + // keep the string to a minimum
                    '&h=' + now.getHours() + '&m=' + now.getMinutes() + '&s=' + now.getSeconds() +
                    '&url=' + encodeWrapper(purify(currentUrl)) +
                    (configReferrerUrl.length ? '&urlref=' + encodeWrapper(purify(configReferrerUrl)) : '') +
                    ((configUserId && configUserId.length) ? '&uid=' + encodeWrapper(configUserId) : '') +
                    '&_id=' + cookieVisitorIdValues.uuid + '&_idts=' + cookieVisitorIdValues.createTs + '&_idvc=' + cookieVisitorIdValues.visitCount +
                    '&_idn=' + cookieVisitorIdValues.newVisitor + // currently unused
                    (campaignNameDetected.length ? '&_rcn=' + encodeWrapper(campaignNameDetected) : '') +
                    (campaignKeywordDetected.length ? '&_rck=' + encodeWrapper(campaignKeywordDetected) : '') +
                    '&_refts=' + referralTs +
                    '&_viewts=' + cookieVisitorIdValues.lastVisitTs +
                    (String(cookieVisitorIdValues.lastEcommerceOrderTs).length ? '&_ects=' + cookieVisitorIdValues.lastEcommerceOrderTs : '') +
                    (String(referralUrl).length ? '&_ref=' + encodeWrapper(purify(referralUrl.slice(0, referralUrlMaxLength))) : '') +
                    (charSet ? '&cs=' + encodeWrapper(charSet) : '') +
                    '&send_image=0';

                // browser features
                for (i in browserFeatures) {
                    if (Object.prototype.hasOwnProperty.call(browserFeatures, i)) {
                        request += '&' + i + '=' + browserFeatures[i];
                    }
                }

                var customDimensionIdsAlreadyHandled = [];
                if (customData) {
                    for (i in customData) {
                        if (Object.prototype.hasOwnProperty.call(customData, i) && /^dimension\d+$/.test(i)) {
                            var index = i.replace('dimension', '');
                            customDimensionIdsAlreadyHandled.push(parseInt(index, 10));
                            customDimensionIdsAlreadyHandled.push(String(index));
                            request += '&' + i + '=' + customData[i];
                            delete customData[i];
                        }
                    }
                }

                if (customData && isObjectEmpty(customData)) {
                    customData = null;
                    // we deleted all keys from custom data
                }

                // custom dimensions
                for (i in customDimensions) {
                    if (Object.prototype.hasOwnProperty.call(customDimensions, i)) {
                        var isNotSetYet = (-1 === indexOfArray(customDimensionIdsAlreadyHandled, i));
                        if (isNotSetYet) {
                            request += '&dimension' + i + '=' + customDimensions[i];
                        }
                    }
                }

                // custom data
                if (customData) {
                    request += '&data=' + encodeWrapper(JSON_PIWIK.stringify(customData));
                } else if (configCustomData) {
                    request += '&data=' + encodeWrapper(JSON_PIWIK.stringify(configCustomData));
                }

                // Custom Variables, scope "page"
                function appendCustomVariablesToRequest(customVariables, parameterName) {
                    var customVariablesStringified = JSON_PIWIK.stringify(customVariables);
                    if (customVariablesStringified.length > 2) {
                        return '&' + parameterName + '=' + encodeWrapper(customVariablesStringified);
                    }
                    return '';
                }

                var sortedCustomVarPage = sortObjectByKeys(customVariablesPage);
                var sortedCustomVarEvent = sortObjectByKeys(customVariablesEvent);

                request += appendCustomVariablesToRequest(sortedCustomVarPage, 'cvar');
                request += appendCustomVariablesToRequest(sortedCustomVarEvent, 'e_cvar');

                // Custom Variables, scope "visit"
                if (customVariables) {
                    request += appendCustomVariablesToRequest(customVariables, '_cvar');

                    // Don't save deleted custom variables in the cookie
                    for (i in customVariablesCopy) {
                        if (Object.prototype.hasOwnProperty.call(customVariablesCopy, i)) {
                            if (customVariables[i][0] === '' || customVariables[i][1] === '') {
                                delete customVariables[i];
                            }
                        }
                    }

                    if (configStoreCustomVariablesInCookie) {
                        setCookie(cookieCustomVariablesName, JSON_PIWIK.stringify(customVariables), configSessionCookieTimeout, configCookiePath, configCookieDomain);
                    }
                }

                // performance tracking
                if (configPerformanceTrackingEnabled) {
                    if (configPerformanceGenerationTime) {
                        request += '&gt_ms=' + configPerformanceGenerationTime;
                    } else if (performanceAlias && performanceAlias.timing
                        && performanceAlias.timing.requestStart && performanceAlias.timing.responseEnd) {
                        request += '&gt_ms=' + (performanceAlias.timing.responseEnd - performanceAlias.timing.requestStart);
                    }
                }

                if (configIdPageView) {
                    request += '&pv_id=' + configIdPageView;
                }

                // update cookies
                cookieVisitorIdValues.lastEcommerceOrderTs = isDefined(currentEcommerceOrderTs) && String(currentEcommerceOrderTs).length ? currentEcommerceOrderTs : cookieVisitorIdValues.lastEcommerceOrderTs;
                setVisitorIdCookie(cookieVisitorIdValues);
                setSessionCookie();

                // tracker plugin hook
                request += executePluginMethod(pluginMethod, {tracker: trackerInstance, request: request});

                if (configAppendToTrackingUrl.length) {
                    request += '&' + configAppendToTrackingUrl;
                }

                if (isFunction(configCustomRequestContentProcessing)) {
                    request = configCustomRequestContentProcessing(request);
                }

                return request;
            }

            /*
             * If there was user activity since the last check, and it's been configHeartBeatDelay seconds
             * since the last tracker, send a ping request (the heartbeat timeout will be reset by sendRequest).
             */
            heartBeatPingIfActivityAlias = function heartBeatPingIfActivity() {
                var now = new Date();
                if (lastTrackerRequestTime + configHeartBeatDelay <= now.getTime()) {
                    var requestPing = getRequest('ping=1', null, 'ping');
                    sendRequest(requestPing, configTrackerPause);

                    return true;
                }

                return false;
            };

            function logEcommerce(orderId, grandTotal, subTotal, tax, shipping, discount) {
                var request = 'idgoal=0',
                    lastEcommerceOrderTs,
                    now = new Date(),
                    items = [],
                    sku,
                    isEcommerceOrder = String(orderId).length;

                if (isEcommerceOrder) {
                    request += '&ec_id=' + encodeWrapper(orderId);
                    // Record date of order in the visitor cookie
                    lastEcommerceOrderTs = Math.round(now.getTime() / 1000);
                }

                request += '&revenue=' + grandTotal;

                if (String(subTotal).length) {
                    request += '&ec_st=' + subTotal;
                }

                if (String(tax).length) {
                    request += '&ec_tx=' + tax;
                }

                if (String(shipping).length) {
                    request += '&ec_sh=' + shipping;
                }

                if (String(discount).length) {
                    request += '&ec_dt=' + discount;
                }

                if (ecommerceItems) {
                    // Removing the SKU index in the array before JSON encoding
                    for (sku in ecommerceItems) {
                        if (Object.prototype.hasOwnProperty.call(ecommerceItems, sku)) {
                            // Ensure name and category default to healthy value
                            if (!isDefined(ecommerceItems[sku][1])) {
                                ecommerceItems[sku][1] = "";
                            }

                            if (!isDefined(ecommerceItems[sku][2])) {
                                ecommerceItems[sku][2] = "";
                            }

                            // Set price to zero
                            if (!isDefined(ecommerceItems[sku][3])
                                || String(ecommerceItems[sku][3]).length === 0) {
                                ecommerceItems[sku][3] = 0;
                            }

                            // Set quantity to 1
                            if (!isDefined(ecommerceItems[sku][4])
                                || String(ecommerceItems[sku][4]).length === 0) {
                                ecommerceItems[sku][4] = 1;
                            }

                            items.push(ecommerceItems[sku]);
                        }
                    }
                    request += '&ec_items=' + encodeWrapper(JSON_PIWIK.stringify(items));
                }
                request = getRequest(request, configCustomData, 'ecommerce', lastEcommerceOrderTs);
                sendRequest(request, configTrackerPause);

                if (isEcommerceOrder) {
                    ecommerceItems = {};
                }
            }

            function logEcommerceOrder(orderId, grandTotal, subTotal, tax, shipping, discount) {
                if (String(orderId).length
                    && isDefined(grandTotal)) {
                    logEcommerce(orderId, grandTotal, subTotal, tax, shipping, discount);
                }
            }

            function logEcommerceCartUpdate(grandTotal) {
                if (isDefined(grandTotal)) {
                    logEcommerce("", grandTotal, "", "", "", "");
                }
            }

            /*
             * Log the page view / visit
             */
            function logPageView(customTitle, customData, callback) {
                configIdPageView = generateUniqueId();

                var request = getRequest('action_name=' + encodeWrapper(titleFixup(customTitle || configTitle)), customData, 'log');

                sendRequest(request, configTrackerPause, callback);
            }

            /*
             * Construct regular expression of classes
             */
            function getClassesRegExp(configClasses, defaultClass) {
                var i,
                    classesRegExp = '(^| )(piwik[_-]' + defaultClass;

                if (configClasses) {
                    for (i = 0; i < configClasses.length; i++) {
                        classesRegExp += '|' + configClasses[i];
                    }
                }

                classesRegExp += ')( |$)';

                return new RegExp(classesRegExp);
            }

            function startsUrlWithTrackerUrl(url) {
                return (configTrackerUrl && url && 0 === String(url).indexOf(configTrackerUrl));
            }

            /*
             * Link or Download?
             */
            function getLinkType(className, href, isInLink, hasDownloadAttribute) {
                if (startsUrlWithTrackerUrl(href)) {
                    return 0;
                }

                // does class indicate whether it is an (explicit/forced) outlink or a download?
                var downloadPattern = getClassesRegExp(configDownloadClasses, 'download'),
                    linkPattern = getClassesRegExp(configLinkClasses, 'link'),

                    // does file extension indicate that it is a download?
                    downloadExtensionsPattern = new RegExp('\\.(' + configDownloadExtensions.join('|') + ')([?&#]|$)', 'i');

                if (linkPattern.test(className)) {
                    return 'link';
                }

                if (hasDownloadAttribute || downloadPattern.test(className) || downloadExtensionsPattern.test(href)) {
                    return 'download';
                }

                if (isInLink) {
                    return 0;
                }

                return 'link';
            }

            function getSourceElement(sourceElement)
            {
                var parentElement;

                parentElement = sourceElement.parentNode;
                while (parentElement !== null &&
                /* buggy IE5.5 */
                isDefined(parentElement)) {

                    if (query.isLinkElement(sourceElement)) {
                        break;
                    }
                    sourceElement = parentElement;
                    parentElement = sourceElement.parentNode;
                }

                return sourceElement;
            }

            function getLinkIfShouldBeProcessed(sourceElement)
            {
                sourceElement = getSourceElement(sourceElement);

                if (!query.hasNodeAttribute(sourceElement, 'href')) {
                    return;
                }

                if (!isDefined(sourceElement.href)) {
                    return;
                }

                var href = query.getAttributeValueFromNode(sourceElement, 'href');

                if (startsUrlWithTrackerUrl(href)) {
                    return;
                }

                var originalSourcePath = sourceElement.pathname || getPathName(sourceElement.href);

                // browsers, such as Safari, don't downcase hostname and href
                var originalSourceHostName = sourceElement.hostname || getHostName(sourceElement.href);
                var sourceHostName = originalSourceHostName.toLowerCase();
                var sourceHref = sourceElement.href.replace(originalSourceHostName, sourceHostName);

                // browsers, such as Safari, don't downcase hostname and href
                var scriptProtocol = new RegExp('^(javascript|vbscript|jscript|mocha|livescript|ecmascript|mailto|tel):', 'i');

                if (!scriptProtocol.test(sourceHref)) {
                    // track outlinks and all downloads
                    var linkType = getLinkType(sourceElement.className, sourceHref, isSiteHostPath(sourceHostName, originalSourcePath), query.hasNodeAttribute(sourceElement, 'download'));

                    if (linkType) {
                        return {
                            type: linkType,
                            href: sourceHref
                        };
                    }
                }
            }

            function buildContentInteractionRequest(interaction, name, piece, target)
            {
                var params = content.buildInteractionRequestParams(interaction, name, piece, target);

                if (!params) {
                    return;
                }

                return getRequest(params, null, 'contentInteraction');
            }

            function buildContentInteractionTrackingRedirectUrl(url, contentInteraction, contentName, contentPiece, contentTarget)
            {
                if (!isDefined(url)) {
                    return;
                }

                if (startsUrlWithTrackerUrl(url)) {
                    return url;
                }

                var redirectUrl = content.toAbsoluteUrl(url);
                var request  = 'redirecturl=' + encodeWrapper(redirectUrl) + '&';
                request     += buildContentInteractionRequest(contentInteraction, contentName, contentPiece, (contentTarget || url));

                var separator = '&';
                if (configTrackerUrl.indexOf('?') < 0) {
                    separator = '?';
                }

                return configTrackerUrl + separator + request;
            }

            function isNodeAuthorizedToTriggerInteraction(contentNode, interactedNode)
            {
                if (!contentNode || !interactedNode) {
                    return false;
                }

                var targetNode = content.findTargetNode(contentNode);

                if (content.shouldIgnoreInteraction(targetNode)) {
                    // interaction should be ignored
                    return false;
                }

                targetNode = content.findTargetNodeNoDefault(contentNode);
                if (targetNode && !containsNodeElement(targetNode, interactedNode)) {
                    /**
                     * There is a target node defined but the clicked element is not within the target node. example:
                     * <div data-track-content><a href="Y" data-content-target>Y</a><img src=""/><a href="Z">Z</a></div>
                     *
                     * The user clicked in this case on link Z and not on target Y
                     */
                    return false;
                }

                return true;
            }

            function getContentInteractionToRequestIfPossible (anyNode, interaction, fallbackTarget)
            {
                if (!anyNode) {
                    return;
                }

                var contentNode = content.findParentContentNode(anyNode);

                if (!contentNode) {
                    // we are not within a content block
                    return;
                }

                if (!isNodeAuthorizedToTriggerInteraction(contentNode, anyNode)) {
                    return;
                }

                var contentBlock = content.buildContentBlock(contentNode);

                if (!contentBlock) {
                    return;
                }

                if (!contentBlock.target && fallbackTarget) {
                    contentBlock.target = fallbackTarget;
                }

                return content.buildInteractionRequestParams(interaction, contentBlock.name, contentBlock.piece, contentBlock.target);
            }

            function wasContentImpressionAlreadyTracked(contentBlock)
            {
                if (!trackedContentImpressions || !trackedContentImpressions.length) {
                    return false;
                }

                var index, trackedContent;

                for (index = 0; index < trackedContentImpressions.length; index++) {
                    trackedContent = trackedContentImpressions[index];

                    if (trackedContent &&
                        trackedContent.name === contentBlock.name &&
                        trackedContent.piece === contentBlock.piece &&
                        trackedContent.target === contentBlock.target) {
                        return true;
                    }
                }

                return false;
            }

            function replaceHrefIfInternalLink(contentBlock)
            {
                if (!contentBlock) {
                    return false;
                }

                var targetNode = content.findTargetNode(contentBlock);

                if (!targetNode || content.shouldIgnoreInteraction(targetNode)) {
                    return false;
                }

                var link = getLinkIfShouldBeProcessed(targetNode);

                if (linkTrackingEnabled && link && link.type) {

                    return false; // will be handled via outlink or download.
                }

                if (query.isLinkElement(targetNode) &&
                    query.hasNodeAttributeWithValue(targetNode, 'href')) {
                    var url = String(query.getAttributeValueFromNode(targetNode, 'href'));

                    if (0 === url.indexOf('#')) {
                        return false;
                    }

                    if (startsUrlWithTrackerUrl(url)) {
                        return true;
                    }

                    if (!content.isUrlToCurrentDomain(url)) {
                        return false;
                    }

                    var block = content.buildContentBlock(contentBlock);

                    if (!block) {
                        return;
                    }

                    var contentName   = block.name;
                    var contentPiece  = block.piece;
                    var contentTarget = block.target;

                    if (!query.hasNodeAttributeWithValue(targetNode, content.CONTENT_TARGET_ATTR) || targetNode.wasContentTargetAttrReplaced) {
                        // make sure we still track the correct content target when an interaction is happening
                        targetNode.wasContentTargetAttrReplaced = true;
                        contentTarget = content.toAbsoluteUrl(url);
                        query.setAnyAttribute(targetNode, content.CONTENT_TARGET_ATTR, contentTarget);
                    }

                    var targetUrl = buildContentInteractionTrackingRedirectUrl(url, 'click', contentName, contentPiece, contentTarget);

                    // location.href does not respect target=_blank so we prefer to use this
                    content.setHrefAttribute(targetNode, targetUrl);

                    return true;
                }

                return false;
            }

            function replaceHrefsIfInternalLink(contentNodes)
            {
                if (!contentNodes || !contentNodes.length) {
                    return;
                }

                var index;
                for (index = 0; index < contentNodes.length; index++) {
                    replaceHrefIfInternalLink(contentNodes[index]);
                }
            }

            function trackContentImpressionClickInteraction (targetNode)
            {
                return function (event) {

                    if (!targetNode) {
                        return;
                    }

                    var contentBlock = content.findParentContentNode(targetNode);

                    var interactedElement;
                    if (event) {
                        interactedElement = event.target || event.srcElement;
                    }
                    if (!interactedElement) {
                        interactedElement = targetNode;
                    }

                    if (!isNodeAuthorizedToTriggerInteraction(contentBlock, interactedElement)) {
                        return;
                    }

                    setExpireDateTime(configTrackerPause);

                    if (query.isLinkElement(targetNode) &&
                        query.hasNodeAttributeWithValue(targetNode, 'href') &&
                        query.hasNodeAttributeWithValue(targetNode, content.CONTENT_TARGET_ATTR)) {
                        // there is a href attribute, the link was replaced with piwik.php but later the href was changed again by the application.
                        var href = query.getAttributeValueFromNode(targetNode, 'href');
                        if (!startsUrlWithTrackerUrl(href) && targetNode.wasContentTargetAttrReplaced) {
                            query.setAnyAttribute(targetNode, content.CONTENT_TARGET_ATTR, '');
                        }
                    }

                    var link = getLinkIfShouldBeProcessed(targetNode);

                    if (linkTrackingInstalled && link && link.type) {
                        // click ignore, will be tracked via processClick, we do not want to track it twice

                        return link.type;
                    }

                    if (replaceHrefIfInternalLink(contentBlock)) {
                        return 'href';
                    }

                    var block = content.buildContentBlock(contentBlock);

                    if (!block) {
                        return;
                    }

                    var contentName   = block.name;
                    var contentPiece  = block.piece;
                    var contentTarget = block.target;

                    // click on any non link element, or on a link element that has not an href attribute or on an anchor
                    var request = buildContentInteractionRequest('click', contentName, contentPiece, contentTarget);
                    sendRequest(request, configTrackerPause);

                    return request;
                };
            }

            function setupInteractionsTracking(contentNodes)
            {
                if (!contentNodes || !contentNodes.length) {
                    return;
                }

                var index, targetNode;
                for (index = 0; index < contentNodes.length; index++) {
                    targetNode = content.findTargetNode(contentNodes[index]);

                    if (targetNode && !targetNode.contentInteractionTrackingSetupDone) {
                        targetNode.contentInteractionTrackingSetupDone = true;

                        addEventListener(targetNode, 'click', trackContentImpressionClickInteraction(targetNode));
                    }
                }
            }

            /*
             * Log all content pieces
             */
            function buildContentImpressionsRequests(contents, contentNodes)
            {
                if (!contents || !contents.length) {
                    return [];
                }

                var index, request;

                for (index = 0; index < contents.length; index++) {

                    if (wasContentImpressionAlreadyTracked(contents[index])) {
                        contents.splice(index, 1);
                        index--;
                    } else {
                        trackedContentImpressions.push(contents[index]);
                    }
                }

                if (!contents || !contents.length) {
                    return [];
                }

                replaceHrefsIfInternalLink(contentNodes);
                setupInteractionsTracking(contentNodes);

                var requests = [];

                for (index = 0; index < contents.length; index++) {

                    request = getRequest(
                        content.buildImpressionRequestParams(contents[index].name, contents[index].piece, contents[index].target),
                        undefined,
                        'contentImpressions'
                    );

                    if (request) {
                        requests.push(request);
                    }
                }

                return requests;
            }

            /*
             * Log all content pieces
             */
            function getContentImpressionsRequestsFromNodes(contentNodes)
            {
                var contents = content.collectContent(contentNodes);

                return buildContentImpressionsRequests(contents, contentNodes);
            }

            /*
             * Log currently visible content pieces
             */
            function getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet(contentNodes)
            {
                if (!contentNodes || !contentNodes.length) {
                    return [];
                }

                var index;

                for (index = 0; index < contentNodes.length; index++) {
                    if (!content.isNodeVisible(contentNodes[index])) {
                        contentNodes.splice(index, 1);
                        index--;
                    }
                }

                if (!contentNodes || !contentNodes.length) {
                    return [];
                }

                return getContentImpressionsRequestsFromNodes(contentNodes);
            }

            function buildContentImpressionRequest(contentName, contentPiece, contentTarget)
            {
                var params = content.buildImpressionRequestParams(contentName, contentPiece, contentTarget);

                return getRequest(params, null, 'contentImpression');
            }

            function buildContentInteractionRequestNode(node, contentInteraction)
            {
                if (!node) {
                    return;
                }

                var contentNode  = content.findParentContentNode(node);
                var contentBlock = content.buildContentBlock(contentNode);

                if (!contentBlock) {
                    return;
                }

                if (!contentInteraction) {
                    contentInteraction = 'Unknown';
                }

                return buildContentInteractionRequest(contentInteraction, contentBlock.name, contentBlock.piece, contentBlock.target);
            }

            function buildEventRequest(category, action, name, value)
            {
                return 'e_c=' + encodeWrapper(category)
                    + '&e_a=' + encodeWrapper(action)
                    + (isDefined(name) ? '&e_n=' + encodeWrapper(name) : '')
                    + (isDefined(value) ? '&e_v=' + encodeWrapper(value) : '');
            }

            /*
             * Log the event
             */
            function logEvent(category, action, name, value, customData, callback)
            {
                // Category and Action are required parameters
                if (trim(String(category)).length === 0 || trim(String(action)).length === 0) {
                    logConsoleError('Error while logging event: Parameters `category` and `action` must not be empty or filled with whitespaces');
                    return false;
                }
                var request = getRequest(
                    buildEventRequest(category, action, name, value),
                    customData,
                    'event'
                );

                sendRequest(request, configTrackerPause, callback);
            }

            /*
             * Log the site search request
             */
            function logSiteSearch(keyword, category, resultsCount, customData) {
                var request = getRequest('search=' + encodeWrapper(keyword)
                    + (category ? '&search_cat=' + encodeWrapper(category) : '')
                    + (isDefined(resultsCount) ? '&search_count=' + resultsCount : ''), customData, 'sitesearch');

                sendRequest(request, configTrackerPause);
            }

            /*
             * Log the goal with the server
             */
            function logGoal(idGoal, customRevenue, customData, callback) {
                var request = getRequest('idgoal=' + idGoal + (customRevenue ? '&revenue=' + customRevenue : ''), customData, 'goal');

                sendRequest(request, configTrackerPause, callback);
            }

            /*
             * Log the link or click with the server
             */
            function logLink(url, linkType, customData, callback, sourceElement) {

                var linkParams = linkType + '=' + encodeWrapper(purify(url));

                var interaction = getContentInteractionToRequestIfPossible(sourceElement, 'click', url);

                if (interaction) {
                    linkParams += '&' + interaction;
                }

                var request = getRequest(linkParams, customData, 'link');

                sendRequest(request, configTrackerPause, callback);
            }

            /*
             * Browser prefix
             */
            function prefixPropertyName(prefix, propertyName) {
                if (prefix !== '') {
                    return prefix + propertyName.charAt(0).toUpperCase() + propertyName.slice(1);
                }

                return propertyName;
            }

            /*
             * Check for pre-rendered web pages, and log the page view/link/goal
             * according to the configuration and/or visibility
             *
             * @see http://dvcs.w3.org/hg/webperf/raw-file/tip/specs/PageVisibility/Overview.html
             */
            function trackCallback(callback) {
                var isPreRendered,
                    i,
                    // Chrome 13, IE10, FF10
                    prefixes = ['', 'webkit', 'ms', 'moz'],
                    prefix;

                if (!configCountPreRendered) {
                    for (i = 0; i < prefixes.length; i++) {
                        prefix = prefixes[i];

                        // does this browser support the page visibility API?
                        if (Object.prototype.hasOwnProperty.call(documentAlias, prefixPropertyName(prefix, 'hidden'))) {
                            // if pre-rendered, then defer callback until page visibility changes
                            if (documentAlias[prefixPropertyName(prefix, 'visibilityState')] === 'prerender') {
                                isPreRendered = true;
                            }
                            break;
                        }
                    }
                }

                if (isPreRendered) {
                    // note: the event name doesn't follow the same naming convention as vendor properties
                    addEventListener(documentAlias, prefix + 'visibilitychange', function ready() {
                        documentAlias.removeEventListener(prefix + 'visibilitychange', ready, false);
                        callback();
                    });

                    return;
                }

                // configCountPreRendered === true || isPreRendered === false
                callback();
            }

            function getCrossDomainVisitorId()
            {
                var visitorId = getValuesFromVisitorIdCookie().uuid;
                var deviceId = makeCrossDomainDeviceId();
                return visitorId + deviceId;
            }

            function replaceHrefForCrossDomainLink(element)
            {
                if (!element) {
                    return;
                }

                if (!query.hasNodeAttribute(element, 'href')) {
                    return;
                }

                var link = query.getAttributeValueFromNode(element, 'href');

                if (!link || startsUrlWithTrackerUrl(link)) {
                    return;
                }

                // we need to remove the parameter and add it again if needed to make sure we have latest timestamp
                // and visitorId (eg userId might be set etc)
                link = removeUrlParameter(link, configVisitorIdUrlParameter);

                if (link.indexOf('?') > 0) {
                    link += '&';
                } else {
                    link += '?';
                }

                var crossDomainVisitorId = getCrossDomainVisitorId();

                link = addUrlParameter(link, configVisitorIdUrlParameter, crossDomainVisitorId);

                query.setAnyAttribute(element, 'href', link);
            }

            function isLinkToDifferentDomainButSamePiwikWebsite(element)
            {
                var targetLink = query.getAttributeValueFromNode(element, 'href');

                if (!targetLink) {
                    return false;
                }

                targetLink = String(targetLink);

                var isOutlink = targetLink.indexOf('//') === 0
                    || targetLink.indexOf('http://') === 0
                    || targetLink.indexOf('https://') === 0;

                if (!isOutlink) {
                    return false;
                }

                var originalSourcePath = element.pathname || getPathName(element.href);
                var originalSourceHostName = (element.hostname || getHostName(element.href)).toLowerCase();

                if (isSiteHostPath(originalSourceHostName, originalSourcePath)) {
                    // we could also check against config cookie domain but this would require that other website
                    // sets actually same cookie domain and we cannot rely on it.
                    if (!isSameHost(domainAlias, domainFixup(originalSourceHostName))) {
                        return true;
                    }

                    return false;
                }

                return false;
            }

            /*
             * Process clicks
             */
            function processClick(sourceElement) {
                var link = getLinkIfShouldBeProcessed(sourceElement);

                // not a link to same domain or the same website (as set in setDomains())
                if (link && link.type) {
                    link.href = safeDecodeWrapper(link.href);
                    logLink(link.href, link.type, undefined, null, sourceElement);
                    return;
                }


                // a link to same domain or the same website (as set in setDomains())
                if (crossDomainTrackingEnabled) {
                    // in case the clicked element is within the <a> (for example there is a <div> within the <a>) this will get the actual <a> link element
                    sourceElement = getSourceElement(sourceElement);

                    if(isLinkToDifferentDomainButSamePiwikWebsite(sourceElement)) {
                        replaceHrefForCrossDomainLink(sourceElement);
                    }

                }
            }

            function isIE8orOlder()
            {
                return documentAlias.all && !documentAlias.addEventListener;
            }

            function getKeyCodeFromEvent(event)
            {
                // event.which is deprecated https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/which
                var which = event.which;

                /**
                 1 : Left mouse button
                 2 : Wheel button or middle button
                 3 : Right mouse button
                 */

                var typeOfEventButton = (typeof event.button);

                if (!which && typeOfEventButton !== 'undefined' ) {
                    /**
                     -1: No button pressed
                     0 : Main button pressed, usually the left button
                     1 : Auxiliary button pressed, usually the wheel button or themiddle button (if present)
                     2 : Secondary button pressed, usually the right button
                     3 : Fourth button, typically the Browser Back button
                     4 : Fifth button, typically the Browser Forward button

                     IE8 and earlier has different values:
                     1 : Left mouse button
                     2 : Right mouse button
                     4 : Wheel button or middle button

                     For a left-hand configured mouse, the return values are reversed. We do not take care of that.
                     */

                    if (isIE8orOlder()) {
                        if (event.button & 1) {
                            which = 1;
                        } else if (event.button & 2) {
                            which = 3;
                        } else if (event.button & 4) {
                            which = 2;
                        }
                    } else {
                        if (event.button === 0 || event.button === '0') {
                            which = 1;
                        } else if (event.button & 1) {
                            which = 2;
                        } else if (event.button & 2) {
                            which = 3;
                        }
                    }
                }

                return which;
            }

            function getNameOfClickedButton(event)
            {
                switch (getKeyCodeFromEvent(event)) {
                    case 1:
                        return 'left';
                    case 2:
                        return 'middle';
                    case 3:
                        return 'right';
                }
            }

            function getTargetElementFromEvent(event)
            {
                return event.target || event.srcElement;
            }

            /*
             * Handle click event
             */
            function clickHandler(enable) {

                return function (event) {

                    event = event || windowAlias.event;

                    var button = getNameOfClickedButton(event);
                    var target = getTargetElementFromEvent(event);

                    if (event.type === 'click') {

                        var ignoreClick = false;
                        if (enable && button === 'middle') {
                            // if enabled, we track middle clicks via mouseup
                            // some browsers (eg chrome) trigger click and mousedown/up events when middle is clicked,
                            // whereas some do not. This way we make "sure" to track them only once, either in click
                            // (default) or in mouseup (if enable == true)
                            ignoreClick = true;
                        }

                        if (target && !ignoreClick) {
                            processClick(target);
                        }
                    } else if (event.type === 'mousedown') {
                        if (button === 'middle' && target) {
                            lastButton = button;
                            lastTarget = target;
                        } else {
                            lastButton = lastTarget = null;
                        }
                    } else if (event.type === 'mouseup') {
                        if (button === lastButton && target === lastTarget) {
                            processClick(target);
                        }
                        lastButton = lastTarget = null;
                    } else if (event.type === 'contextmenu') {
                        processClick(target);
                    }
                };
            }

            /*
             * Add click listener to a DOM element
             */
            function addClickListener(element, enable) {
                var enableType = typeof enable;
                if (enableType === 'undefined') {
                    enable = true;
                }

                addEventListener(element, 'click', clickHandler(enable), false);

                if (enable) {
                    addEventListener(element, 'mouseup', clickHandler(enable), false);
                    addEventListener(element, 'mousedown', clickHandler(enable), false);
                    addEventListener(element, 'contextmenu', clickHandler(enable), false);
                }
            }

            /*
             * Add click handlers to anchor and AREA elements, except those to be ignored
             */
            function addClickListeners(enable, trackerInstance) {
                linkTrackingInstalled = true;

                // iterate through anchor elements with href and AREA elements
                var i,
                    ignorePattern = getClassesRegExp(configIgnoreClasses, 'ignore'),
                    linkElements = documentAlias.links,
                    linkElement = null, trackerType = null;

                if (linkElements) {
                    for (i = 0; i < linkElements.length; i++) {
                        linkElement = linkElements[i];
                        if (!ignorePattern.test(linkElement.className)) {
                            trackerType = typeof linkElement.piwikTrackers;

                            if ('undefined' === trackerType) {
                                linkElement.piwikTrackers = [];
                            }

                            if (-1 === indexOfArray(linkElement.piwikTrackers, trackerInstance)) {
                                // we make sure to setup link only once for each tracker
                                linkElement.piwikTrackers.push(trackerInstance);
                                addClickListener(linkElement, enable);
                            }
                        }
                    }
                }
            }


            function enableTrackOnlyVisibleContent (checkOnScroll, timeIntervalInMs, tracker) {

                if (isTrackOnlyVisibleContentEnabled) {
                    // already enabled, do not register intervals again
                    return true;
                }

                isTrackOnlyVisibleContentEnabled = true;

                var didScroll = false;
                var events, index;

                function setDidScroll() { didScroll = true; }

                trackCallbackOnLoad(function () {

                    function checkContent(intervalInMs) {
                        setTimeout(function () {
                            if (!isTrackOnlyVisibleContentEnabled) {
                                return; // the tests stopped tracking only visible content
                            }
                            didScroll = false;
                            tracker.trackVisibleContentImpressions();
                            checkContent(intervalInMs);
                        }, intervalInMs);
                    }

                    function checkContentIfDidScroll(intervalInMs) {

                        setTimeout(function () {
                            if (!isTrackOnlyVisibleContentEnabled) {
                                return; // the tests stopped tracking only visible content
                            }

                            if (didScroll) {
                                didScroll = false;
                                tracker.trackVisibleContentImpressions();
                            }

                            checkContentIfDidScroll(intervalInMs);
                        }, intervalInMs);
                    }

                    if (checkOnScroll) {

                        // scroll event is executed after each pixel, so we make sure not to
                        // execute event too often. otherwise FPS goes down a lot!
                        events = ['scroll', 'resize'];
                        for (index = 0; index < events.length; index++) {
                            if (documentAlias.addEventListener) {
                                documentAlias.addEventListener(events[index], setDidScroll, false);
                            } else {
                                windowAlias.attachEvent('on' + events[index], setDidScroll);
                            }
                        }

                        checkContentIfDidScroll(100);
                    }

                    if (timeIntervalInMs && timeIntervalInMs > 0) {
                        timeIntervalInMs = parseInt(timeIntervalInMs, 10);
                        checkContent(timeIntervalInMs);
                    }

                });
            }

            /*
             * Browser features (plugins, resolution, cookies)
             */
            function detectBrowserFeatures() {
                var i,
                    mimeType,
                    pluginMap = {
                        // document types
                        pdf: 'application/pdf',

                        // media players
                        qt: 'video/quicktime',
                        realp: 'audio/x-pn-realaudio-plugin',
                        wma: 'application/x-mplayer2',

                        // interactive multimedia
                        dir: 'application/x-director',
                        fla: 'application/x-shockwave-flash',

                        // RIA
                        java: 'application/x-java-vm',
                        gears: 'application/x-googlegears',
                        ag: 'application/x-silverlight'
                    };

                // detect browser features except IE < 11 (IE 11 user agent is no longer MSIE)
                if (!((new RegExp('MSIE')).test(navigatorAlias.userAgent))) {
                    // general plugin detection
                    if (navigatorAlias.mimeTypes && navigatorAlias.mimeTypes.length) {
                        for (i in pluginMap) {
                            if (Object.prototype.hasOwnProperty.call(pluginMap, i)) {
                                mimeType = navigatorAlias.mimeTypes[pluginMap[i]];
                                browserFeatures[i] = (mimeType && mimeType.enabledPlugin) ? '1' : '0';
                            }
                        }
                    }

                    // Safari and Opera
                    // IE6/IE7 navigator.javaEnabled can't be aliased, so test directly
                    // on Edge navigator.javaEnabled() always returns `true`, so ignore it
                    if (!((new RegExp('Edge[ /](\\d+[\\.\\d]+)')).test(navigatorAlias.userAgent)) &&
                        typeof navigator.javaEnabled !== 'unknown' &&
                        isDefined(navigatorAlias.javaEnabled) &&
                        navigatorAlias.javaEnabled()) {
                        browserFeatures.java = '1';
                    }

                    // Firefox
                    if (isFunction(windowAlias.GearsFactory)) {
                        browserFeatures.gears = '1';
                    }

                    // other browser features
                    browserFeatures.cookie = hasCookies();
                }

                var width = parseInt(screenAlias.width, 10);
                var height = parseInt(screenAlias.height, 10);
                browserFeatures.res = parseInt(width, 10) + 'x' + parseInt(height, 10);
            }

            /*<DEBUG>*/
            /*
             * Register a test hook. Using eval() permits access to otherwise
             * privileged members.
             */
            function registerHook(hookName, userHook) {
                var hookObj = null;

                if (isString(hookName) && !isDefined(registeredHooks[hookName]) && userHook) {
                    if (isObject(userHook)) {
                        hookObj = userHook;
                    } else if (isString(userHook)) {
                        try {
                            eval('hookObj =' + userHook);
                        } catch (ignore) { }
                    }

                    registeredHooks[hookName] = hookObj;
                }

                return hookObj;
            }

            var requestQueue = {
                requests: [],
                timeout: null,
                sendRequests: function () {
                    var requestsToTrack = this.requests;
                    this.requests = [];
                    if (requestsToTrack.length === 1) {
                        sendRequest(requestsToTrack[0]);
                    } else {
                        sendBulkRequest(requestsToTrack);
                    }
                },
                push: function (requestUrl) {
                    if (!requestUrl) {
                        return;
                    }
                    if (isPageUnloading) {
                        // we don't queue as we need to ensure the request will be sent when the page is unloading...
                        trackerInstance.trackRequest(requestUrl);
                        return;
                    }

                    this.requests.push(requestUrl);

                    if (this.timeout) {
                        clearTimeout(this.timeout);
                        this.timeout = null;
                    }
                    // we always extend by another 1.75 seconds after receiving a tracking request
                    this.timeout = setTimeout(function () {
                        requestQueue.timeout = null;
                        requestQueue.sendRequests();
                    }, 1750);

                    var trackerQueueId = 'RequestQueue' + uniqueTrackerId;
                    if (!Object.prototype.hasOwnProperty.call(plugins, trackerQueueId)) {
                        // we setup one unload handler per tracker...
                        // Piwik.addPlugin might not be defined at this point, we add the plugin directly also to make
                        // JSLint happy.
                        plugins[trackerQueueId] = {
                            unload: function () {
                                if (requestQueue.timeout) {
                                    clearTimeout(requestQueue.timeout);
                                }
                                requestQueue.sendRequests();
                            }
                        };
                    }
                }
            };

            /*</DEBUG>*/

            /************************************************************
             * Constructor
             ************************************************************/

            /*
             * initialize tracker
             */
            detectBrowserFeatures();
            updateDomainHash();
            setVisitorIdCookie();

            /*<DEBUG>*/
            /*
             * initialize test plugin
             */
            executePluginMethod('run', null, registerHook);
            /*</DEBUG>*/

            /************************************************************
             * Public data and methods
             ************************************************************/


            /*<DEBUG>*/
            /*
             * Test hook accessors
             */
            this.hook = registeredHooks;
            this.getHook = function (hookName) {
                return registeredHooks[hookName];
            };
            this.getQuery = function () {
                return query;
            };
            this.getContent = function () {
                return content;
            };
            this.setVisitorId = function (visitorId) {
                visitorUUID = visitorId;
            };

            this.buildContentImpressionRequest = buildContentImpressionRequest;
            this.buildContentInteractionRequest = buildContentInteractionRequest;
            this.buildContentInteractionRequestNode = buildContentInteractionRequestNode;
            this.buildContentInteractionTrackingRedirectUrl = buildContentInteractionTrackingRedirectUrl;
            this.getContentImpressionsRequestsFromNodes = getContentImpressionsRequestsFromNodes;
            this.getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet = getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet;
            this.trackCallbackOnLoad = trackCallbackOnLoad;
            this.trackCallbackOnReady = trackCallbackOnReady;
            this.buildContentImpressionsRequests = buildContentImpressionsRequests;
            this.wasContentImpressionAlreadyTracked = wasContentImpressionAlreadyTracked;
            this.appendContentInteractionToRequestIfPossible = getContentInteractionToRequestIfPossible;
            this.setupInteractionsTracking = setupInteractionsTracking;
            this.trackContentImpressionClickInteraction = trackContentImpressionClickInteraction;
            this.internalIsNodeVisible = isVisible;
            this.isNodeAuthorizedToTriggerInteraction = isNodeAuthorizedToTriggerInteraction;
            this.replaceHrefIfInternalLink = replaceHrefIfInternalLink;
            this.getDomains = function () {
                return configHostsAlias;
            };
            this.getConfigIdPageView = function () {
                return configIdPageView;
            };
            this.getConfigDownloadExtensions = function () {
                return configDownloadExtensions;
            };
            this.enableTrackOnlyVisibleContent = function (checkOnScroll, timeIntervalInMs) {
                return enableTrackOnlyVisibleContent(checkOnScroll, timeIntervalInMs, this);
            };
            this.clearTrackedContentImpressions = function () {
                trackedContentImpressions = [];
            };
            this.getTrackedContentImpressions = function () {
                return trackedContentImpressions;
            };
            this.clearEnableTrackOnlyVisibleContent = function () {
                isTrackOnlyVisibleContentEnabled = false;
            };
            this.disableLinkTracking = function () {
                linkTrackingInstalled = false;
                linkTrackingEnabled   = false;
            };
            this.getConfigVisitorCookieTimeout = function () {
                return configVisitorCookieTimeout;
            };
            this.removeAllAsyncTrackersButFirst = function () {
                var firstTracker = asyncTrackers[0];
                asyncTrackers = [firstTracker];
            };
            this.getConsentRequestsQueue = function () {
                return consentRequestsQueue;
            };
            this.hasConsent = function () {
                return configHasConsent;
            };
            this.getRemainingVisitorCookieTimeout = getRemainingVisitorCookieTimeout;
            /*</DEBUG>*/

            /**
             * Get visitor ID (from first party cookie)
             *
             * @return string Visitor ID in hexits (or empty string, if not yet known)
             */
            this.getVisitorId = function () {
                return getValuesFromVisitorIdCookie().uuid;
            };

            /**
             * Get the visitor information (from first party cookie)
             *
             * @return array
             */
            this.getVisitorInfo = function () {
                // Note: in a new method, we could return also return getValuesFromVisitorIdCookie()
                //       which returns named parameters rather than returning integer indexed array
                return loadVisitorIdCookie();
            };

            /**
             * Get the Attribution information, which is an array that contains
             * the Referrer used to reach the site as well as the campaign name and keyword
             * It is useful only when used in conjunction with Tracker API function setAttributionInfo()
             * To access specific data point, you should use the other functions getAttributionReferrer* and getAttributionCampaign*
             *
             * @return array Attribution array, Example use:
             *   1) Call JSON_PIWIK.stringify(piwikTracker.getAttributionInfo())
             *   2) Pass this json encoded string to the Tracking API (php or java client): setAttributionInfo()
             */
            this.getAttributionInfo = function () {
                return loadReferrerAttributionCookie();
            };

            /**
             * Get the Campaign name that was parsed from the landing page URL when the visitor
             * landed on the site originally
             *
             * @return string
             */
            this.getAttributionCampaignName = function () {
                return loadReferrerAttributionCookie()[0];
            };

            /**
             * Get the Campaign keyword that was parsed from the landing page URL when the visitor
             * landed on the site originally
             *
             * @return string
             */
            this.getAttributionCampaignKeyword = function () {
                return loadReferrerAttributionCookie()[1];
            };

            /**
             * Get the time at which the referrer (used for Goal Attribution) was detected
             *
             * @return int Timestamp or 0 if no referrer currently set
             */
            this.getAttributionReferrerTimestamp = function () {
                return loadReferrerAttributionCookie()[2];
            };

            /**
             * Get the full referrer URL that will be used for Goal Attribution
             *
             * @return string Raw URL, or empty string '' if no referrer currently set
             */
            this.getAttributionReferrerUrl = function () {
                return loadReferrerAttributionCookie()[3];
            };

            /**
             * Specify the Piwik tracking URL
             *
             * @param string trackerUrl
             */
            this.setTrackerUrl = function (trackerUrl) {
                configTrackerUrl = trackerUrl;
            };

            /**
             * Returns the Piwik tracking URL
             * @returns string
             */
            this.getTrackerUrl = function () {
                return configTrackerUrl;
            };

            /**
             * Returns the Piwik server URL.
             *
             * @returns string
             */
            this.getPiwikUrl = function () {
                return getPiwikUrlForOverlay(this.getTrackerUrl(), configApiUrl);
            };

            /**
             * Adds a new tracker. All sent requests will be also sent to the given siteId and piwikUrl.
             *
             * @param string piwikUrl  The tracker URL of the current tracker instance
             * @param int|string siteId
             * @return Tracker
             */
            this.addTracker = function (piwikUrl, siteId) {
                if (!siteId) {
                    throw new Error('A siteId must be given to add a new tracker');
                }

                if (!isDefined(piwikUrl) || null === piwikUrl) {
                    piwikUrl = this.getTrackerUrl();
                }

                var tracker = new Tracker(piwikUrl, siteId);

                asyncTrackers.push(tracker);

                return tracker;
            };

            /**
             * Returns the site ID
             *
             * @returns int
             */
            this.getSiteId = function() {
                return configTrackerSiteId;
            };

            /**
             * Specify the site ID
             *
             * @param int|string siteId
             */
            this.setSiteId = function (siteId) {
                setSiteId(siteId);
            };

            /**
             * Clears the User ID and generates a new visitor id.
             */
            this.resetUserId = function() {
                configUserId = '';
            };

            /**
             * Sets a User ID to this user (such as an email address or a username)
             *
             * @param string User ID
             */
            this.setUserId = function (userId) {
                if(!isDefined(userId) || !userId.length) {
                    return;
                }
                configUserId = userId;
            };

            /**
             * Gets the User ID if set.
             *
             * @returns string User ID
             */
            this.getUserId = function() {
                return configUserId;
            };

            /**
             * Pass custom data to the server
             *
             * Examples:
             *   tracker.setCustomData(object);
             *   tracker.setCustomData(key, value);
             *
             * @param mixed key_or_obj
             * @param mixed opt_value
             */
            this.setCustomData = function (key_or_obj, opt_value) {
                if (isObject(key_or_obj)) {
                    configCustomData = key_or_obj;
                } else {
                    if (!configCustomData) {
                        configCustomData = {};
                    }
                    configCustomData[key_or_obj] = opt_value;
                }
            };

            /**
             * Get custom data
             *
             * @return mixed
             */
            this.getCustomData = function () {
                return configCustomData;
            };

            /**
             * Configure function with custom request content processing logic.
             * It gets called after request content in form of query parameters string has been prepared and before request content gets sent.
             *
             * Examples:
             *   tracker.setCustomRequestProcessing(function(request){
             *     var pairs = request.split('&');
             *     var result = {};
             *     pairs.forEach(function(pair) {
             *       pair = pair.split('=');
             *       result[pair[0]] = decodeURIComponent(pair[1] || '');
             *     });
             *     return JSON.stringify(result);
             *   });
             *
             * @param function customRequestContentProcessingLogic
             */
            this.setCustomRequestProcessing = function (customRequestContentProcessingLogic) {
                configCustomRequestContentProcessing = customRequestContentProcessingLogic;
            };

            /**
             * Appends the specified query string to the piwik.php?... Tracking API URL
             *
             * @param string queryString eg. 'lat=140&long=100'
             */
            this.appendToTrackingUrl = function (queryString) {
                configAppendToTrackingUrl = queryString;
            };

            /**
             * Returns the query string for the current HTTP Tracking API request.
             * Piwik would prepend the hostname and path to Piwik: http://example.org/piwik/piwik.php?
             * prior to sending the request.
             *
             * @param request eg. "param=value&param2=value2"
             */
            this.getRequest = function (request) {
                return getRequest(request);
            };

            /**
             * Add plugin defined by a name and a callback function.
             * The callback function will be called whenever a tracking request is sent.
             * This can be used to append data to the tracking request, or execute other custom logic.
             *
             * @param string pluginName
             * @param Object pluginObj
             */
            this.addPlugin = function (pluginName, pluginObj) {
                plugins[pluginName] = pluginObj;
            };

            /**
             * Set Custom Dimensions. Set Custom Dimensions will not be cleared after a tracked pageview and will
             * be sent along all following tracking requests. It is possible to remove/clear a value via `deleteCustomDimension`.
             *
             * @param int index A Custom Dimension index
             * @param string value
             */
            this.setCustomDimension = function (customDimensionId, value) {
                customDimensionId = parseInt(customDimensionId, 10);
                if (customDimensionId > 0) {
                    if (!isDefined(value)) {
                        value = '';
                    }
                    if (!isString(value)) {
                        value = String(value);
                    }
                    customDimensions[customDimensionId] = value;
                }
            };

            /**
             * Get a stored value for a specific Custom Dimension index.
             *
             * @param int index A Custom Dimension index
             */
            this.getCustomDimension = function (customDimensionId) {
                customDimensionId = parseInt(customDimensionId, 10);
                if (customDimensionId > 0 && Object.prototype.hasOwnProperty.call(customDimensions, customDimensionId)) {
                    return customDimensions[customDimensionId];
                }
            };

            /**
             * Delete a custom dimension.
             *
             * @param int index Custom dimension Id
             */
            this.deleteCustomDimension = function (customDimensionId) {
                customDimensionId = parseInt(customDimensionId, 10);
                if (customDimensionId > 0) {
                    delete customDimensions[customDimensionId];
                }
            };

            /**
             * Set custom variable within this visit
             *
             * @param int index Custom variable slot ID from 1-5
             * @param string name
             * @param string value
             * @param string scope Scope of Custom Variable:
             *                     - "visit" will store the name/value in the visit and will persist it in the cookie for the duration of the visit,
             *                     - "page" will store the name/value in the next page view tracked.
             *                     - "event" will store the name/value in the next event tracked.
             */
            this.setCustomVariable = function (index, name, value, scope) {
                var toRecord;

                if (!isDefined(scope)) {
                    scope = 'visit';
                }
                if (!isDefined(name)) {
                    return;
                }
                if (!isDefined(value)) {
                    value = "";
                }
                if (index > 0) {
                    name = !isString(name) ? String(name) : name;
                    value = !isString(value) ? String(value) : value;
                    toRecord = [name.slice(0, customVariableMaximumLength), value.slice(0, customVariableMaximumLength)];
                    // numeric scope is there for GA compatibility
                    if (scope === 'visit' || scope === 2) {
                        loadCustomVariables();
                        customVariables[index] = toRecord;
                    } else if (scope === 'page' || scope === 3) {
                        customVariablesPage[index] = toRecord;
                    } else if (scope === 'event') { /* GA does not have 'event' scope but we do */
                        customVariablesEvent[index] = toRecord;
                    }
                }
            };

            /**
             * Get custom variable
             *
             * @param int index Custom variable slot ID from 1-5
             * @param string scope Scope of Custom Variable: "visit" or "page" or "event"
             */
            this.getCustomVariable = function (index, scope) {
                var cvar;

                if (!isDefined(scope)) {
                    scope = "visit";
                }

                if (scope === "page" || scope === 3) {
                    cvar = customVariablesPage[index];
                } else if (scope === "event") {
                    cvar = customVariablesEvent[index];
                } else if (scope === "visit" || scope === 2) {
                    loadCustomVariables();
                    cvar = customVariables[index];
                }

                if (!isDefined(cvar)
                    || (cvar && cvar[0] === '')) {
                    return false;
                }

                return cvar;
            };

            /**
             * Delete custom variable
             *
             * @param int index Custom variable slot ID from 1-5
             * @param string scope
             */
            this.deleteCustomVariable = function (index, scope) {
                // Only delete if it was there already
                if (this.getCustomVariable(index, scope)) {
                    this.setCustomVariable(index, '', '', scope);
                }
            };

            /**
             * Deletes all custom variables for a certain scope.
             *
             * @param string scope
             */
            this.deleteCustomVariables = function (scope) {
                if (scope === "page" || scope === 3) {
                    customVariablesPage = {};
                } else if (scope === "event") {
                    customVariablesEvent = {};
                } else if (scope === "visit" || scope === 2) {
                    customVariables = {};
                }
            };

            /**
             * When called then the Custom Variables of scope "visit" will be stored (persisted) in a first party cookie
             * for the duration of the visit. This is useful if you want to call getCustomVariable later in the visit.
             *
             * By default, Custom Variables of scope "visit" are not stored on the visitor's computer.
             */
            this.storeCustomVariablesInCookie = function () {
                configStoreCustomVariablesInCookie = true;
            };

            /**
             * Set delay for link tracking (in milliseconds)
             *
             * @param int delay
             */
            this.setLinkTrackingTimer = function (delay) {
                configTrackerPause = delay;
            };

            /**
             * Get delay for link tracking (in milliseconds)
             *
             * @param int delay
             */
            this.getLinkTrackingTimer = function () {
                return configTrackerPause;
            };

            /**
             * Set list of file extensions to be recognized as downloads
             *
             * @param string|array extensions
             */
            this.setDownloadExtensions = function (extensions) {
                if(isString(extensions)) {
                    extensions = extensions.split('|');
                }
                configDownloadExtensions = extensions;
            };

            /**
             * Specify additional file extensions to be recognized as downloads
             *
             * @param string|array extensions  for example 'custom' or ['custom1','custom2','custom3']
             */
            this.addDownloadExtensions = function (extensions) {
                var i;
                if(isString(extensions)) {
                    extensions = extensions.split('|');
                }
                for (i=0; i < extensions.length; i++) {
                    configDownloadExtensions.push(extensions[i]);
                }
            };

            /**
             * Removes specified file extensions from the list of recognized downloads
             *
             * @param string|array extensions  for example 'custom' or ['custom1','custom2','custom3']
             */
            this.removeDownloadExtensions = function (extensions) {
                var i, newExtensions = [];
                if(isString(extensions)) {
                    extensions = extensions.split('|');
                }
                for (i=0; i < configDownloadExtensions.length; i++) {
                    if (indexOfArray(extensions, configDownloadExtensions[i]) === -1) {
                        newExtensions.push(configDownloadExtensions[i]);
                    }
                }
                configDownloadExtensions = newExtensions;
            };

            /**
             * Set array of domains to be treated as local. Also supports path, eg '.piwik.org/subsite1'. In this
             * case all links that don't go to '*.piwik.org/subsite1/ *' would be treated as outlinks.
             * For example a link to 'piwik.org/' or 'piwik.org/subsite2' both would be treated as outlinks.
             *
             * Also supports page wildcard, eg 'piwik.org/index*'. In this case all links
             * that don't go to piwik.org/index* would be treated as outlinks.
             *
             * The current domain will be added automatically if no given host alias contains a path and if no host
             * alias is already given for the current host alias. Say you are on "example.org" and set
             * "hostAlias = ['example.com', 'example.org/test']" then the current "example.org" domain will not be
             * added as there is already a more restrictive hostAlias 'example.org/test' given. We also do not add
             * it automatically if there was any other host specifying any path like
             * "['example.com', 'example2.com/test']". In this case we would also not add the current
             * domain "example.org" automatically as the "path" feature is used. As soon as someone uses the path
             * feature, for Piwik JS Tracker to work correctly in all cases, one needs to specify all hosts
             * manually.
             *
             * @param string|array hostsAlias
             */
            this.setDomains = function (hostsAlias) {
                configHostsAlias = isString(hostsAlias) ? [hostsAlias] : hostsAlias;

                var hasDomainAliasAlready = false, i = 0, alias;
                for (i; i < configHostsAlias.length; i++) {
                    alias = String(configHostsAlias[i]);

                    if (isSameHost(domainAlias, domainFixup(alias))) {
                        hasDomainAliasAlready = true;
                        break;
                    }

                    var pathName = getPathName(alias);
                    if (pathName && pathName !== '/' && pathName !== '/*') {
                        hasDomainAliasAlready = true;
                        break;
                    }
                }

                // The current domain will be added automatically if no given host alias contains a path
                // and if no host alias is already given for the current host alias.
                if (!hasDomainAliasAlready) {
                    /**
                     * eg if domainAlias = 'piwik.org' and someone set hostsAlias = ['piwik.org/foo'] then we should
                     * not add piwik.org as it would increase the allowed scope.
                     */
                    configHostsAlias.push(domainAlias);
                }
            };

            /**
             * Enables cross domain linking. By default, the visitor ID that identifies a unique visitor is stored in
             * the browser's first party cookies. This means the cookie can only be accessed by pages on the same domain.
             * If you own multiple domains and would like to track all the actions and pageviews of a specific visitor
             * into the same visit, you may enable cross domain linking. Whenever a user clicks on a link it will append
             * a URL parameter pk_vid to the clicked URL which consists of these parts: 16 char visitorId, a 10 character
             * current timestamp and the last 6 characters are an id based on the userAgent to identify the users device).
             * This way the current visitorId is forwarded to the page of the different domain.
             *
             * On the different domain, the Piwik tracker will recognize the set visitorId from the URL parameter and
             * reuse this parameter if the page was loaded within 45 seconds. If cross domain linking was not enabled,
             * it would create a new visit on that page because we wouldn't be able to access the previously created
             * cookie. By enabling cross domain linking you can track several different domains into one website and
             * won't lose for example the original referrer.
             *
             * To make cross domain linking work you need to set which domains should be considered as your domains by
             * calling the method "setDomains()" first. We will add the URL parameter to links that go to a
             * different domain but only if the domain was previously set with "setDomains()" to make sure not to append
             * the URL parameters when a link actually goes to a third-party URL.
             */
            this.enableCrossDomainLinking = function () {
                crossDomainTrackingEnabled = true;
            };

            /**
             * Disable cross domain linking if it was previously enabled. See enableCrossDomainLinking();
             */
            this.disableCrossDomainLinking = function () {
                crossDomainTrackingEnabled = false;
            };

            /**
             * Detect whether cross domain linking is enabled or not. See enableCrossDomainLinking();
             * @returns bool
             */
            this.isCrossDomainLinkingEnabled = function () {
                return crossDomainTrackingEnabled;
            };


            /**
             * By default, the two visits across domains will be linked together
             * when the link is click and the page is loaded within 180 seconds.
             * @param timeout in seconds
             */
            this.setCrossDomainLinkingTimeout = function (timeout) {
                configVisitorIdUrlParameterTimeoutInSeconds = timeout;
            };

            /**
             * Returns the query parameter appended to link URLs so cross domain visits
             * can be detected.
             *
             * If your application creates links dynamically, then you'll have to add this
             * query parameter manually to those links (since the JavaScript tracker cannot
             * detect when those links are added).
             *
             * Eg:
             *
             * var url = 'http://myotherdomain.com/?' + piwikTracker.getCrossDomainLinkingUrlParameter();
             * $element.append('<a href="' + url + '"/>');
             */
            this.getCrossDomainLinkingUrlParameter = function () {
                return encodeWrapper(configVisitorIdUrlParameter) + '=' + encodeWrapper(getCrossDomainVisitorId());
            };

            /**
             * Set array of classes to be ignored if present in link
             *
             * @param string|array ignoreClasses
             */
            this.setIgnoreClasses = function (ignoreClasses) {
                configIgnoreClasses = isString(ignoreClasses) ? [ignoreClasses] : ignoreClasses;
            };

            /**
             * Set request method
             *
             * @param string method GET or POST; default is GET
             */
            this.setRequestMethod = function (method) {
                configRequestMethod = method || defaultRequestMethod;
            };

            /**
             * Set request Content-Type header value, applicable when POST request method is used for submitting tracking events.
             * See XMLHttpRequest Level 2 spec, section 4.7.2 for invalid headers
             * @link http://dvcs.w3.org/hg/xhr/raw-file/tip/Overview.html
             *
             * @param string requestContentType; default is 'application/x-www-form-urlencoded; charset=UTF-8'
             */
            this.setRequestContentType = function (requestContentType) {
                configRequestContentType = requestContentType || defaultRequestContentType;
            };

            /**
             * Override referrer
             *
             * @param string url
             */
            this.setReferrerUrl = function (url) {
                configReferrerUrl = url;
            };

            /**
             * Override url
             *
             * @param string url
             */
            this.setCustomUrl = function (url) {
                configCustomUrl = resolveRelativeReference(locationHrefAlias, url);
            };

            /**
             * Returns the current url of the page that is currently being visited. If a custom URL was set, the
             * previously defined custom URL will be returned.
             */
            this.getCurrentUrl = function () {
                return configCustomUrl || locationHrefAlias;
            };

            /**
             * Override document.title
             *
             * @param string title
             */
            this.setDocumentTitle = function (title) {
                configTitle = title;
            };

            /**
             * Set the URL of the Piwik API. It is used for Page Overlay.
             * This method should only be called when the API URL differs from the tracker URL.
             *
             * @param string apiUrl
             */
            this.setAPIUrl = function (apiUrl) {
                configApiUrl = apiUrl;
            };

            /**
             * Set array of classes to be treated as downloads
             *
             * @param string|array downloadClasses
             */
            this.setDownloadClasses = function (downloadClasses) {
                configDownloadClasses = isString(downloadClasses) ? [downloadClasses] : downloadClasses;
            };

            /**
             * Set array of classes to be treated as outlinks
             *
             * @param string|array linkClasses
             */
            this.setLinkClasses = function (linkClasses) {
                configLinkClasses = isString(linkClasses) ? [linkClasses] : linkClasses;
            };

            /**
             * Set array of campaign name parameters
             *
             * @see http://piwik.org/faq/how-to/#faq_120
             * @param string|array campaignNames
             */
            this.setCampaignNameKey = function (campaignNames) {
                configCampaignNameParameters = isString(campaignNames) ? [campaignNames] : campaignNames;
            };

            /**
             * Set array of campaign keyword parameters
             *
             * @see http://piwik.org/faq/how-to/#faq_120
             * @param string|array campaignKeywords
             */
            this.setCampaignKeywordKey = function (campaignKeywords) {
                configCampaignKeywordParameters = isString(campaignKeywords) ? [campaignKeywords] : campaignKeywords;
            };

            /**
             * Strip hash tag (or anchor) from URL
             * Note: this can be done in the Piwik>Settings>Websites on a per-website basis
             *
             * @deprecated
             * @param bool enableFilter
             */
            this.discardHashTag = function (enableFilter) {
                configDiscardHashTag = enableFilter;
            };

            /**
             * Set first-party cookie name prefix
             *
             * @param string cookieNamePrefix
             */
            this.setCookieNamePrefix = function (cookieNamePrefix) {
                configCookieNamePrefix = cookieNamePrefix;
                // Re-init the Custom Variables cookie
                customVariables = getCustomVariablesFromCookie();
            };

            /**
             * Set first-party cookie domain
             *
             * @param string domain
             */
            this.setCookieDomain = function (domain) {
                var domainFixed = domainFixup(domain);

                if (isPossibleToSetCookieOnDomain(domainFixed)) {
                    configCookieDomain = domainFixed;
                    updateDomainHash();
                }
            };

            /**
             * Get first-party cookie domain
             */
            this.getCookieDomain = function () {
                return configCookieDomain;
            };

            /**
             * Detect if cookies are enabled and supported by browser.
             */
            this.hasCookies = function () {
                return '1' === hasCookies();
            };

            /**
             * Set a first-party cookie for the duration of the session.
             *
             * @param string cookieName
             * @param string cookieValue
             * @param int msToExpire Defaults to session cookie timeout
             */
            this.setSessionCookie = function (cookieName, cookieValue, msToExpire) {
                if (!cookieName) {
                    throw new Error('Missing cookie name');
                }

                if (!isDefined(msToExpire)) {
                    msToExpire = configSessionCookieTimeout;
                }

                configCookiesToDelete.push(cookieName);

                setCookie(getCookieName(cookieName), cookieValue, msToExpire, configCookiePath, configCookieDomain);
            };

            /**
             * Get first-party cookie value.
             *
             * Returns null if cookies are disabled or if no cookie could be found for this name.
             *
             * @param string cookieName
             */
            this.getCookie = function (cookieName) {
                var cookieValue = getCookie(getCookieName(cookieName));

                if (cookieValue === 0) {
                    return null;
                }

                return cookieValue;
            };

            /**
             * Set first-party cookie path.
             *
             * @param string domain
             */
            this.setCookiePath = function (path) {
                configCookiePath = path;
                updateDomainHash();
            };

            /**
             * Get first-party cookie path.
             *
             * @param string domain
             */
            this.getCookiePath = function (path) {
                return configCookiePath;
            };

            /**
             * Set visitor cookie timeout (in seconds)
             * Defaults to 13 months (timeout=33955200)
             *
             * @param int timeout
             */
            this.setVisitorCookieTimeout = function (timeout) {
                configVisitorCookieTimeout = timeout * 1000;
            };

            /**
             * Set session cookie timeout (in seconds).
             * Defaults to 30 minutes (timeout=1800)
             *
             * @param int timeout
             */
            this.setSessionCookieTimeout = function (timeout) {
                configSessionCookieTimeout = timeout * 1000;
            };

            /**
             * Get session cookie timeout (in seconds).
             */
            this.getSessionCookieTimeout = function () {
                return configSessionCookieTimeout;
            };

            /**
             * Set referral cookie timeout (in seconds).
             * Defaults to 6 months (15768000000)
             *
             * @param int timeout
             */
            this.setReferralCookieTimeout = function (timeout) {
                configReferralCookieTimeout = timeout * 1000;
            };

            /**
             * Set conversion attribution to first referrer and campaign
             *
             * @param bool if true, use first referrer (and first campaign)
             *             if false, use the last referrer (or campaign)
             */
            this.setConversionAttributionFirstReferrer = function (enable) {
                configConversionAttributionFirstReferrer = enable;
            };

            /**
             * Enable the Secure cookie flag on all first party cookies.
             * This should be used when your website is only available under HTTPS
             * so that all tracking cookies are always sent over secure connection.
             *
             * @param bool
             */
            this.setSecureCookie = function (enable) {
                configCookieIsSecure = enable;
            };

            /**
             * Disables all cookies from being set
             *
             * Existing cookies will be deleted on the next call to track
             */
            this.disableCookies = function () {
                configCookiesDisabled = true;
                browserFeatures.cookie = '0';

                if (configTrackerSiteId) {
                    deleteCookies();
                }
            };

            /**
             * One off cookies clearing. Useful to call this when you know for sure a new visitor is using the same browser,
             * it maybe helps to "reset" tracking cookies to prevent data reuse for different users.
             */
            this.deleteCookies = function () {
                deleteCookies();
            };

            /**
             * Handle do-not-track requests
             *
             * @param bool enable If true, don't track if user agent sends 'do-not-track' header
             */
            this.setDoNotTrack = function (enable) {
                var dnt = navigatorAlias.doNotTrack || navigatorAlias.msDoNotTrack;
                configDoNotTrack = enable && (dnt === 'yes' || dnt === '1');

                // do not track also disables cookies and deletes existing cookies
                if (configDoNotTrack) {
                    this.disableCookies();
                }
            };

            /**
             * Enables send beacon usage instead of regular XHR which reduces the link tracking time to a minimum
             * of 100ms instead of 500ms (default). This means when a user clicks for example on an outlink, the
             * navigation to this page will happen 400ms faster.
             * In case you are setting a callback method when issuing a tracking request, the callback method will
             *  be executed as soon as the tracking request was sent through "sendBeacon" and not after the tracking
             *  request finished as it is not possible to find out when the request finished.
             * Send beacon will only be used if the browser actually supports it.
             */
            this.alwaysUseSendBeacon = function () {
                configAlwaysUseSendBeacon = true;
            };

            /**
             * Add click listener to a specific link element.
             * When clicked, Piwik will log the click automatically.
             *
             * @param DOMElement element
             * @param bool enable If false, do not use pseudo click-handler (middle click + context menu)
             */
            this.addListener = function (element, enable) {
                addClickListener(element, enable);
            };

            /**
             * Install link tracker.
             *
             * If you change the DOM of your website or web application you need to make sure to call this method
             * again so Piwik can detect links that were added newly.
             *
             * The default behaviour is to use actual click events. However, some browsers
             * (e.g., Firefox, Opera, and Konqueror) don't generate click events for the middle mouse button.
             *
             * To capture more "clicks", the pseudo click-handler uses mousedown + mouseup events.
             * This is not industry standard and is vulnerable to false positives (e.g., drag events).
             *
             * There is a Safari/Chrome/Webkit bug that prevents tracking requests from being sent
             * by either click handler.  The workaround is to set a target attribute (which can't
             * be "_self", "_top", or "_parent").
             *
             * @see https://bugs.webkit.org/show_bug.cgi?id=54783
             *
             * @param bool enable Defaults to true.
             *                    * If "true", use pseudo click-handler (treat middle click and open contextmenu as
             *                    left click). A right click (or any click that opens the context menu) on a link
             *                    will be tracked as clicked even if "Open in new tab" is not selected.
             *                    * If "false" (default), nothing will be tracked on open context menu or middle click.
             *                    The context menu is usually opened to open a link / download in a new tab
             *                    therefore you can get more accurate results by treat it as a click but it can lead
             *                    to wrong click numbers.
             */
            this.enableLinkTracking = function (enable) {
                linkTrackingEnabled = true;

                var self = this;
                trackCallback(function () {
                    trackCallbackOnReady(function () {
                        addClickListeners(enable, self);
                    });
                });
            };

            /**
             * Enable tracking of uncatched JavaScript errors
             *
             * If enabled, uncaught JavaScript Errors will be tracked as an event by defining a
             * window.onerror handler. If a window.onerror handler is already defined we will make
             * sure to call this previously registered error handler after tracking the error.
             *
             * By default we return false in the window.onerror handler to make sure the error still
             * appears in the browser's console etc. Note: Some older browsers might behave differently
             * so it could happen that an actual JavaScript error will be suppressed.
             * If a window.onerror handler was registered we will return the result of this handler.
             *
             * Make sure not to overwrite the window.onerror handler after enabling the JS error
             * tracking as the error tracking won't work otherwise. To capture all JS errors we
             * recommend to include the Piwik JavaScript tracker in the HTML as early as possible.
             * If possible directly in <head></head> before loading any other JavaScript.
             */
            this.enableJSErrorTracking = function () {
                if (enableJSErrorTracking) {
                    return;
                }

                enableJSErrorTracking = true;
                var onError = windowAlias.onerror;

                windowAlias.onerror = function (message, url, linenumber, column, error) {
                    trackCallback(function () {
                        var category = 'JavaScript Errors';

                        var action = url + ':' + linenumber;
                        if (column) {
                            action += ':' + column;
                        }

                        logEvent(category, action, message);
                    });

                    if (onError) {
                        return onError(message, url, linenumber, column, error);
                    }

                    return false;
                };
            };

            /**
             * Disable automatic performance tracking
             */
            this.disablePerformanceTracking = function () {
                configPerformanceTrackingEnabled = false;
            };

            /**
             * Set the server generation time.
             * If set, the browser's performance.timing API in not used anymore to determine the time.
             *
             * @param int generationTime
             */
            this.setGenerationTimeMs = function (generationTime) {
                configPerformanceGenerationTime = parseInt(generationTime, 10);
            };

            /**
             * Set heartbeat (in seconds)
             *
             * @param int heartBeatDelayInSeconds Defaults to 15. Cannot be lower than 1.
             */
            this.enableHeartBeatTimer = function (heartBeatDelayInSeconds) {
                heartBeatDelayInSeconds = Math.max(heartBeatDelayInSeconds, 1);
                configHeartBeatDelay = (heartBeatDelayInSeconds || 15) * 1000;

                // if a tracking request has already been sent, start the heart beat timeout
                if (lastTrackerRequestTime !== null) {
                    setUpHeartBeat();
                }
            };

            /**
             * Disable heartbeat if it was previously activated.
             */
            this.disableHeartBeatTimer = function () {
                heartBeatDown();

                if (configHeartBeatDelay || heartBeatSetUp) {
                    if (windowAlias.removeEventListener) {
                        windowAlias.removeEventListener('focus', heartBeatOnFocus, true);
                        windowAlias.removeEventListener('blur', heartBeatOnBlur, true);
                    } else if  (windowAlias.detachEvent) {
                        windowAlias.detachEvent('onfocus', heartBeatOnFocus);
                        windowAlias.detachEvent('onblur', heartBeatOnBlur);
                    }
                }

                configHeartBeatDelay = null;
                heartBeatSetUp = false;
            };

            /**
             * Frame buster
             */
            this.killFrame = function () {
                if (windowAlias.location !== windowAlias.top.location) {
                    windowAlias.top.location = windowAlias.location;
                }
            };

            /**
             * Redirect if browsing offline (aka file: buster)
             *
             * @param string url Redirect to this URL
             */
            this.redirectFile = function (url) {
                if (windowAlias.location.protocol === 'file:') {
                    windowAlias.location = url;
                }
            };

            /**
             * Count sites in pre-rendered state
             *
             * @param bool enable If true, track when in pre-rendered state
             */
            this.setCountPreRendered = function (enable) {
                configCountPreRendered = enable;
            };

            /**
             * Trigger a goal
             *
             * @param int|string idGoal
             * @param int|float customRevenue
             * @param mixed customData
             * @param function callback
             */
            this.trackGoal = function (idGoal, customRevenue, customData, callback) {
                trackCallback(function () {
                    logGoal(idGoal, customRevenue, customData, callback);
                });
            };

            /**
             * Manually log a click from your own code
             *
             * @param string sourceUrl
             * @param string linkType
             * @param mixed customData
             * @param function callback
             */
            this.trackLink = function (sourceUrl, linkType, customData, callback) {
                trackCallback(function () {
                    logLink(sourceUrl, linkType, customData, callback);
                });
            };

            /**
             * Get the number of page views that have been tracked so far within the currently loaded page.
             */
            this.getNumTrackedPageViews = function () {
                return numTrackedPageviews;
            };

            /**
             * Log visit to this page
             *
             * @param string customTitle
             * @param mixed customData
             * @param function callback
             */
            this.trackPageView = function (customTitle, customData, callback) {
                trackedContentImpressions = [];
                consentRequestsQueue = [];

                if (isOverlaySession(configTrackerSiteId)) {
                    trackCallback(function () {
                        injectOverlayScripts(configTrackerUrl, configApiUrl, configTrackerSiteId);
                    });
                } else {
                    trackCallback(function () {
                        numTrackedPageviews++;
                        logPageView(customTitle, customData, callback);
                    });
                }
            };

            /**
             * Scans the entire DOM for all content blocks and tracks all impressions once the DOM ready event has
             * been triggered.
             *
             * If you only want to track visible content impressions have a look at `trackVisibleContentImpressions()`.
             * We do not track an impression of the same content block twice if you call this method multiple times
             * unless `trackPageView()` is called meanwhile. This is useful for single page applications.
             */
            this.trackAllContentImpressions = function () {
                if (isOverlaySession(configTrackerSiteId)) {
                    return;
                }

                trackCallback(function () {
                    trackCallbackOnReady(function () {
                        // we have to wait till DOM ready
                        var contentNodes = content.findContentNodes();
                        var requests     = getContentImpressionsRequestsFromNodes(contentNodes);

                        sendBulkRequest(requests, configTrackerPause);
                    });
                });
            };

            /**
             * Scans the entire DOM for all content blocks as soon as the page is loaded. It tracks an impression
             * only if a content block is actually visible. Meaning it is not hidden and the content is or was at
             * some point in the viewport.
             *
             * If you want to track all content blocks have a look at `trackAllContentImpressions()`.
             * We do not track an impression of the same content block twice if you call this method multiple times
             * unless `trackPageView()` is called meanwhile. This is useful for single page applications.
             *
             * Once you have called this method you can no longer change `checkOnScroll` or `timeIntervalInMs`.
             *
             * If you do want to only track visible content blocks but not want us to perform any automatic checks
             * as they can slow down your frames per second you can call `trackVisibleContentImpressions()` or
             * `trackContentImpressionsWithinNode()` manually at  any time to rescan the entire DOM for newly
             * visible content blocks.
             * o Call `trackVisibleContentImpressions(false, 0)` to initially track only visible content impressions
             * o Call `trackVisibleContentImpressions()` at any time again to rescan the entire DOM for newly visible content blocks or
             * o Call `trackContentImpressionsWithinNode(node)` at any time to rescan only a part of the DOM for newly visible content blocks
             *
             * @param boolean [checkOnScroll=true] Optional, you can disable rescanning the entire DOM automatically
             *                                     after each scroll event by passing the value `false`. If enabled,
             *                                     we check whether a previously hidden content blocks became visible
             *                                     after a scroll and if so track the impression.
             *                                     Note: If a content block is placed within a scrollable element
             *                                     (`overflow: scroll`), we can currently not detect when this block
             *                                     becomes visible.
             * @param integer [timeIntervalInMs=750] Optional, you can define an interval to rescan the entire DOM
             *                                     for new impressions every X milliseconds by passing
             *                                     for instance `timeIntervalInMs=500` (rescan DOM every 500ms).
             *                                     Rescanning the entire DOM and detecting the visible state of content
             *                                     blocks can take a while depending on the browser and amount of content.
             *                                     In case your frames per second goes down you might want to increase
             *                                     this value or disable it by passing the value `0`.
             */
            this.trackVisibleContentImpressions = function (checkOnScroll, timeIntervalInMs) {
                if (isOverlaySession(configTrackerSiteId)) {
                    return;
                }

                if (!isDefined(checkOnScroll)) {
                    checkOnScroll = true;
                }

                if (!isDefined(timeIntervalInMs)) {
                    timeIntervalInMs = 750;
                }

                enableTrackOnlyVisibleContent(checkOnScroll, timeIntervalInMs, this);

                trackCallback(function () {
                    trackCallbackOnLoad(function () {
                        // we have to wait till CSS parsed and applied
                        var contentNodes = content.findContentNodes();
                        var requests     = getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet(contentNodes);

                        sendBulkRequest(requests, configTrackerPause);
                    });
                });
            };

            /**
             * Tracks a content impression using the specified values. You should not call this method too often
             * as each call causes an XHR tracking request and can slow down your site or your server.
             *
             * @param string contentName  For instance "Ad Sale".
             * @param string [contentPiece='Unknown'] For instance a path to an image or the text of a text ad.
             * @param string [contentTarget] For instance the URL of a landing page.
             */
            this.trackContentImpression = function (contentName, contentPiece, contentTarget) {
                if (isOverlaySession(configTrackerSiteId)) {
                    return;
                }

                contentName = trim(contentName);
                contentPiece = trim(contentPiece);
                contentTarget = trim(contentTarget);

                if (!contentName) {
                    return;
                }

                contentPiece = contentPiece || 'Unknown';

                trackCallback(function () {
                    var request = buildContentImpressionRequest(contentName, contentPiece, contentTarget);
                    sendRequest(request, configTrackerPause);
                });
            };

            /**
             * Scans the given DOM node and its children for content blocks and tracks an impression for them if
             * no impression was already tracked for it. If you have called `trackVisibleContentImpressions()`
             * upfront only visible content blocks will be tracked. You can use this method if you, for instance,
             * dynamically add an element using JavaScript to your DOM after we have tracked the initial impressions.
             *
             * @param Element domNode
             */
            this.trackContentImpressionsWithinNode = function (domNode) {
                if (isOverlaySession(configTrackerSiteId) || !domNode) {
                    return;
                }

                trackCallback(function () {
                    if (isTrackOnlyVisibleContentEnabled) {
                        trackCallbackOnLoad(function () {
                            // we have to wait till CSS parsed and applied
                            var contentNodes = content.findContentNodesWithinNode(domNode);

                            var requests = getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet(contentNodes);
                            sendBulkRequest(requests, configTrackerPause);
                        });
                    } else {
                        trackCallbackOnReady(function () {
                            // we have to wait till DOM ready
                            var contentNodes = content.findContentNodesWithinNode(domNode);

                            var requests = getContentImpressionsRequestsFromNodes(contentNodes);
                            sendBulkRequest(requests, configTrackerPause);
                        });
                    }
                });
            };

            /**
             * Tracks a content interaction using the specified values. You should use this method only in conjunction
             * with `trackContentImpression()`. The specified `contentName` and `contentPiece` has to be exactly the
             * same as the ones that were used in `trackContentImpression()`. Otherwise the interaction will not count.
             *
             * @param string contentInteraction The type of interaction that happened. For instance 'click' or 'submit'.
             * @param string contentName  The name of the content. For instance "Ad Sale".
             * @param string [contentPiece='Unknown'] The actual content. For instance a path to an image or the text of a text ad.
             * @param string [contentTarget] For instance the URL of a landing page.
             */
            this.trackContentInteraction = function (contentInteraction, contentName, contentPiece, contentTarget) {
                if (isOverlaySession(configTrackerSiteId)) {
                    return;
                }

                contentInteraction = trim(contentInteraction);
                contentName = trim(contentName);
                contentPiece = trim(contentPiece);
                contentTarget = trim(contentTarget);

                if (!contentInteraction || !contentName) {
                    return;
                }

                contentPiece = contentPiece || 'Unknown';

                trackCallback(function () {
                    var request = buildContentInteractionRequest(contentInteraction, contentName, contentPiece, contentTarget);
                    sendRequest(request, configTrackerPause);
                });
            };

            /**
             * Tracks an interaction with the given DOM node / content block.
             *
             * By default we track interactions on click but sometimes you might want to track interactions yourself.
             * For instance you might want to track an interaction manually on a double click or a form submit.
             * Make sure to disable the automatic interaction tracking in this case by specifying either the CSS
             * class `piwikContentIgnoreInteraction` or the attribute `data-content-ignoreinteraction`.
             *
             * @param Element domNode  This element itself or any of its parent elements has to be a content block
             *                         element. Meaning one of those has to have a `piwikTrackContent` CSS class or
             *                         a `data-track-content` attribute.
             * @param string [contentInteraction='Unknown] The name of the interaction that happened. For instance
             *                                             'click', 'formSubmit', 'DblClick', ...
             */
            this.trackContentInteractionNode = function (domNode, contentInteraction) {
                if (isOverlaySession(configTrackerSiteId) || !domNode) {
                    return;
                }

                trackCallback(function () {
                    var request = buildContentInteractionRequestNode(domNode, contentInteraction);
                    sendRequest(request, configTrackerPause);
                });
            };

            /**
             * Useful to debug content tracking. This method will log all detected content blocks to console
             * (if the browser supports the console). It will list the detected name, piece, and target of each
             * content block.
             */
            this.logAllContentBlocksOnPage = function () {
                var contentNodes = content.findContentNodes();
                var contents = content.collectContent(contentNodes);

                // needed to write it this way for jslint
                var consoleType = typeof console;
                if (consoleType !== 'undefined' && console && console.log) {
                    console.log(contents);
                }
            };

            /**
             * Records an event
             *
             * @param string category The Event Category (Videos, Music, Games...)
             * @param string action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
             * @param string name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
             * @param float value (optional) The Event's value
             * @param function callback
             * @param mixed customData
             */
            this.trackEvent = function (category, action, name, value, customData, callback) {
                trackCallback(function () {
                    logEvent(category, action, name, value, customData, callback);
                });
            };

            /**
             * Log special pageview: Internal search
             *
             * @param string keyword
             * @param string category
             * @param int resultsCount
             * @param mixed customData
             */
            this.trackSiteSearch = function (keyword, category, resultsCount, customData) {
                trackCallback(function () {
                    logSiteSearch(keyword, category, resultsCount, customData);
                });
            };

            /**
             * Used to record that the current page view is an item (product) page view, or a Ecommerce Category page view.
             * This must be called before trackPageView() on the product/category page.
             * It will set 3 custom variables of scope "page" with the SKU, Name and Category for this page view.
             * Note: Custom Variables of scope "page" slots 3, 4 and 5 will be used.
             *
             * On a category page, you can set the parameter category, and set the other parameters to empty string or false
             *
             * Tracking Product/Category page views will allow Piwik to report on Product & Categories
             * conversion rates (Conversion rate = Ecommerce orders containing this product or category / Visits to the product or category)
             *
             * @param string sku Item's SKU code being viewed
             * @param string name Item's Name being viewed
             * @param string category Category page being viewed. On an Item's page, this is the item's category
             * @param float price Item's display price, not use in standard Piwik reports, but output in API product reports.
             */
            this.setEcommerceView = function (sku, name, category, price) {
                if (!isDefined(category) || !category.length) {
                    category = "";
                } else if (category instanceof Array) {
                    category = JSON_PIWIK.stringify(category);
                }

                customVariablesPage[5] = ['_pkc', category];

                if (isDefined(price) && String(price).length) {
                    customVariablesPage[2] = ['_pkp', price];
                }

                // On a category page, do not track Product name not defined
                if ((!isDefined(sku) || !sku.length)
                    && (!isDefined(name) || !name.length)) {
                    return;
                }

                if (isDefined(sku) && sku.length) {
                    customVariablesPage[3] = ['_pks', sku];
                }

                if (!isDefined(name) || !name.length) {
                    name = "";
                }

                customVariablesPage[4] = ['_pkn', name];
            };

            /**
             * Adds an item (product) that is in the current Cart or in the Ecommerce order.
             * This function is called for every item (product) in the Cart or the Order.
             * The only required parameter is sku.
             * The items are deleted from this JavaScript object when the Ecommerce order is tracked via the method trackEcommerceOrder.
             *
             * If there is already a saved item for the given sku, it will be updated with the
             * new information.
             *
             * @param string sku (required) Item's SKU Code. This is the unique identifier for the product.
             * @param string name (optional) Item's name
             * @param string name (optional) Item's category, or array of up to 5 categories
             * @param float price (optional) Item's price. If not specified, will default to 0
             * @param float quantity (optional) Item's quantity. If not specified, will default to 1
             */
            this.addEcommerceItem = function (sku, name, category, price, quantity) {
                if (sku.length) {
                    ecommerceItems[sku] = [ sku, name, category, price, quantity ];
                }
            };

            /**
             * Removes a single ecommerce item by SKU from the current cart.
             *
             * @param string sku (required) Item's SKU Code. This is the unique identifier for the product.
             */
            this.removeEcommerceItem = function (sku) {
                if (sku.length) {
                    delete ecommerceItems[sku];
                }
            };

            /**
             * Clears the current cart, removing all saved ecommerce items. Call this method to manually clear
             * the cart before sending an ecommerce order.
             */
            this.clearEcommerceCart = function () {
                ecommerceItems = {};
            };

            /**
             * Tracks an Ecommerce order.
             * If the Ecommerce order contains items (products), you must call first the addEcommerceItem() for each item in the order.
             * All revenues (grandTotal, subTotal, tax, shipping, discount) will be individually summed and reported in Piwik reports.
             * Parameters orderId and grandTotal are required. For others, you can set to false if you don't need to specify them.
             * After calling this method, items added to the cart will be removed from this JavaScript object.
             *
             * @param string|int orderId (required) Unique Order ID.
             *                   This will be used to count this order only once in the event the order page is reloaded several times.
             *                   orderId must be unique for each transaction, even on different days, or the transaction will not be recorded by Piwik.
             * @param float grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
             * @param float subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied)
             * @param float tax (optional) Tax amount for this order
             * @param float shipping (optional) Shipping amount for this order
             * @param float discount (optional) Discounted amount in this order
             */
            this.trackEcommerceOrder = function (orderId, grandTotal, subTotal, tax, shipping, discount) {
                logEcommerceOrder(orderId, grandTotal, subTotal, tax, shipping, discount);
            };

            /**
             * Tracks a Cart Update (add item, remove item, update item).
             * On every Cart update, you must call addEcommerceItem() for each item (product) in the cart, including the items that haven't been updated since the last cart update.
             * Then you can call this function with the Cart grandTotal (typically the sum of all items' prices)
             * Calling this method does not remove from this JavaScript object the items that were added to the cart via addEcommerceItem
             *
             * @param float grandTotal (required) Items (products) amount in the Cart
             */
            this.trackEcommerceCartUpdate = function (grandTotal) {
                logEcommerceCartUpdate(grandTotal);
            };

            /**
             * Sends a tracking request with custom request parameters.
             * Piwik will prepend the hostname and path to Piwik, as well as all other needed tracking request
             * parameters prior to sending the request. Useful eg if you track custom dimensions via a plugin.
             *
             * @param request eg. "param=value&param2=value2"
             * @param customData
             * @param callback
             * @param pluginMethod
             */
            this.trackRequest = function (request, customData, callback, pluginMethod) {
                trackCallback(function () {
                    var fullRequest = getRequest(request, customData, pluginMethod);
                    sendRequest(fullRequest, configTrackerPause, callback);
                });
            };

            /**
             * Won't send the tracking request directly but wait for a short time to possibly send this tracking request
             * along with other tracking requests in one go. This can reduce the number of requests send to your server.
             * If the page unloads (user navigates to another page or closes the browser), then all remaining queued
             * requests will be sent immediately so that no tracking request gets lost.
             * Note: Any queued request may not be possible to be replayed in case a POST request is sent. Only queue
             * requests that don't have to be replayed.
             *
             * @param request eg. "param=value&param2=value2"
             */
            this.queueRequest = function (request) {
                trackCallback(function () {
                    var fullRequest = getRequest(request);
                    requestQueue.push(fullRequest);
                });
            };

            /**
             * If the user has given consent previously and this consent was remembered, it will return the number
             * in milliseconds since 1970/01/01 which is the date when the user has given consent. Please note that
             * the returned time depends on the users local time which may not always be correct.
             *
             * @returns number|string
             */
            this.getRememberedConsent = function () {
                var value = getCookie(CONSENT_COOKIE_NAME);
                if (getCookie(CONSENT_REMOVED_COOKIE_NAME)) {
                    // if for some reason the consent_removed cookie is also set with the consent cookie, the
                    // consent_removed cookie overrides the consent one, and we make sure to delete the consent
                    // cookie.
                    if (value) {
                        deleteCookie(CONSENT_COOKIE_NAME, configCookiePath, configCookieDomain);
                    }
                    return null;
                }

                if (!value || value === 0) {
                    return null;
                }
                return value;
            };

            /**
             * Detects whether the user has given consent previously.
             *
             * @returns bool
             */
            this.hasRememberedConsent = function () {
                return !!this.getRememberedConsent();
            };

            /**
             * When called, no tracking request will be sent to the Matomo server until you have called `setConsentGiven()`
             * unless consent was given previously AND you called {@link rememberConsentGiven()} when the user gave her
             * or his consent.
             *
             * This may be useful when you want to implement for example a popup to ask for consent before tracking the user.
             * Once the user has given consent, you should call {@link setConsentGiven()} or {@link rememberConsentGiven()}.
             *
             * Please note that when consent is required, we will temporarily set cookies but not track any data. Those
             * cookies will only exist during this page view and deleted as soon as the user navigates to a different page
             * or closes the browser.
             *
             * If you require consent for tracking personal data for example, you should first call
             * `_paq.push(['requireConsent'])`.
             *
             * If the user has already given consent in the past, you can either decide to not call `requireConsent` at all
             * or call `_paq.push(['setConsentGiven'])` on each page view at any time after calling `requireConsent`.
             *
             * When the user gives you the consent to track data, you can also call `_paq.push(['rememberConsentGiven', optionalTimeoutInHours])`
             * and for the duration while the consent is remembered, any call to `requireConsent` will be automatically ignored until you call `forgetConsentGiven`.
             * `forgetConsentGiven` needs to be called when the user removes consent for tracking. This means if you call `rememberConsentGiven` at the
             * time the user gives you consent, you do not need to ever call `_paq.push(['setConsentGiven'])`.
             */
            this.requireConsent = function () {
                configConsentRequired = true;
                configHasConsent = this.hasRememberedConsent();
                // Piwik.addPlugin might not be defined at this point, we add the plugin directly also to make JSLint happy
                // We also want to make sure to define an unload listener for each tracker, not only one tracker.
                coreConsentCounter++;
                plugins['CoreConsent' + coreConsentCounter] = {
                    unload: function () {
                        if (!configHasConsent) {
                            // we want to make sure to remove all previously set cookies again
                            deleteCookies();
                        }
                    }
                };
            };

            /**
             * Call this method once the user has given consent. This will cause all tracking requests from this
             * page view to be sent. Please note that the given consent won't be remembered across page views. If you
             * want to remember consent across page views, call {@link rememberConsentGiven()} instead.
             */
            this.setConsentGiven = function () {
                configHasConsent = true;
                deleteCookie(CONSENT_REMOVED_COOKIE_NAME, configCookiePath, configCookieDomain);
                var i, requestType;
                for (i = 0; i < consentRequestsQueue.length; i++) {
                    requestType = typeof consentRequestsQueue[i];
                    if (requestType === 'string') {
                        sendRequest(consentRequestsQueue[i], configTrackerPause);
                    } else if (requestType === 'object') {
                        sendBulkRequest(consentRequestsQueue[i], configTrackerPause);
                    }
                }
                consentRequestsQueue = [];
            };

            /**
             * Calling this method will remember that the user has given consent across multiple requests by setting
             * a cookie. You can optionally define the lifetime of that cookie in milliseconds using a parameter.
             *
             * When you call this method, we imply that the user has given consent for this page view, and will also
             * imply consent for all future page views unless the cookie expires (if timeout defined) or the user
             * deletes all her or his cookies. This means even if you call {@link requireConsent()}, then all requests
             * will still be tracked.
             *
             * Please note that this feature requires you to set the `cookieDomain` and `cookiePath` correctly and requires
             * that you do not disable cookies. Please also note that when you call this method, consent will be implied
             * for all sites that match the configured cookieDomain and cookiePath. Depending on your website structure,
             * you may need to restrict or widen the scope of the cookie domain/path to ensure the consent is applied
             * to the sites you want.
             */
            this.rememberConsentGiven = function (hoursToExpire) {
                if (configCookiesDisabled) {
                    logConsoleError('rememberConsentGiven is called but cookies are disabled, consent will not be remembered');
                    return;
                }
                if (hoursToExpire) {
                    hoursToExpire = hoursToExpire * 60 * 60 * 1000;
                }
                this.setConsentGiven();
                var now = new Date().getTime();
                setCookie(CONSENT_COOKIE_NAME, now, hoursToExpire, configCookiePath, configCookieDomain, configCookieIsSecure);
            };

            /**
             * Calling this method will remove any previously given consent and during this page view no request
             * will be sent anymore ({@link requireConsent()}) will be called automatically to ensure the removed
             * consent will be enforced. You may call this method if the user removes consent manually, or if you
             * want to re-ask for consent after a specific time period.
             */
            this.forgetConsentGiven = function () {
                if (configCookiesDisabled) {
                    logConsoleError('forgetConsentGiven is called but cookies are disabled, consent will not be forgotten');
                    return;
                }

                deleteCookie(CONSENT_COOKIE_NAME, configCookiePath, configCookieDomain);
                setCookie(CONSENT_REMOVED_COOKIE_NAME, new Date().getTime(), 0, configCookiePath, configCookieDomain, configCookieIsSecure);
                this.requireConsent();
            };

            /**
             * Returns true if user is opted out, false if otherwise.
             *
             * @returns {boolean}
             */
            this.isUserOptedOut = function () {
                return !configHasConsent;
            };

            /**
             * Alias for forgetConsentGiven(). After calling this function, the user will no longer be tracked,
             * (even if they come back to the site).
             */
            this.optUserOut = this.forgetConsentGiven;

            /**
             * Alias for rememberConsentGiven(). After calling this function, the current user will be tracked.
             */
            this.forgetUserOptOut = this.rememberConsentGiven;

            Piwik.trigger('TrackerSetup', [this]);
        }

        function TrackerProxy() {
            return {
                push: apply
            };
        }

        /**
         * Applies the given methods in the given order if they are present in paq.
         *
         * @param {Array} paq
         * @param {Array} methodsToApply an array containing method names in the order that they should be applied
         *                 eg ['setSiteId', 'setTrackerUrl']
         * @returns {Array} the modified paq array with the methods that were already applied set to undefined
         */
        function applyMethodsInOrder(paq, methodsToApply)
        {
            var appliedMethods = {};
            var index, iterator;

            for (index = 0; index < methodsToApply.length; index++) {
                var methodNameToApply = methodsToApply[index];
                appliedMethods[methodNameToApply] = 1;

                for (iterator = 0; iterator < paq.length; iterator++) {
                    if (paq[iterator] && paq[iterator][0]) {
                        var methodName = paq[iterator][0];

                        if (methodNameToApply === methodName) {
                            apply(paq[iterator]);
                            delete paq[iterator];

                            if (appliedMethods[methodName] > 1
                                && methodName !== "addTracker") {
                                logConsoleError('The method ' + methodName + ' is registered more than once in "_paq" variable. Only the last call has an effect. Please have a look at the multiple Piwik trackers documentation: https://developer.piwik.org/guides/tracking-javascript-guide#multiple-piwik-trackers');
                            }

                            appliedMethods[methodName]++;
                        }
                    }
                }
            }

            return paq;
        }

        /************************************************************
         * Constructor
         ************************************************************/

        var applyFirst = ['addTracker', 'disableCookies', 'setTrackerUrl', 'setAPIUrl', 'enableCrossDomainLinking', 'setCrossDomainLinkingTimeout', 'setSecureCookie', 'setCookiePath', 'setCookieDomain', 'setDomains', 'setUserId', 'setSiteId', 'alwaysUseSendBeacon', 'enableLinkTracking', 'requireConsent', 'setConsentGiven'];

        function createFirstTracker(piwikUrl, siteId)
        {
            var tracker = new Tracker(piwikUrl, siteId);
            asyncTrackers.push(tracker);

            _paq = applyMethodsInOrder(_paq, applyFirst);

            // apply the queue of actions
            for (iterator = 0; iterator < _paq.length; iterator++) {
                if (_paq[iterator]) {
                    apply(_paq[iterator]);
                }
            }

            // replace initialization array with proxy object
            _paq = new TrackerProxy();

            return tracker;
        }

        /************************************************************
         * Proxy object
         * - this allows the caller to continue push()'ing to _paq
         *   after the Tracker has been initialized and loaded
         ************************************************************/

        // initialize the Piwik singleton
        addEventListener(windowAlias, 'beforeunload', beforeUnloadHandler, false);

        Date.prototype.getTimeAlias = Date.prototype.getTime;

        /************************************************************
         * Public data and methods
         ************************************************************/

        Piwik = {
            initialized: false,

            JSON: JSON_PIWIK,

            /**
             * DOM Document related methods
             */
            DOM: {
                /**
                 * Adds an event listener to the given element.
                 * @param element
                 * @param eventType
                 * @param eventHandler
                 * @param useCapture  Optional
                 */
                addEventListener: function (element, eventType, eventHandler, useCapture) {
                    var captureType = typeof useCapture;
                    if (captureType === 'undefined') {
                        useCapture = false;
                    }

                    addEventListener(element, eventType, eventHandler, useCapture);
                },
                /**
                 * Specify a function to execute when the DOM is fully loaded.
                 *
                 * If the DOM is already loaded, the function will be executed immediately.
                 *
                 * @param function callback
                 */
                onLoad: trackCallbackOnLoad,

                /**
                 * Specify a function to execute when the DOM is ready.
                 *
                 * If the DOM is already ready, the function will be executed immediately.
                 *
                 * @param function callback
                 */
                onReady: trackCallbackOnReady,

                /**
                 * Detect whether a node is visible right now.
                 */
                isNodeVisible: isVisible,

                /**
                 * Detect whether a node has been visible at some point
                 */
                isOrWasNodeVisible: content.isNodeVisible
            },

            /**
             * Listen to an event and invoke the handler when a the event is triggered.
             *
             * @param string event
             * @param function handler
             */
            on: function (event, handler) {
                if (!eventHandlers[event]) {
                    eventHandlers[event] = [];
                }

                eventHandlers[event].push(handler);
            },

            /**
             * Remove a handler to no longer listen to the event. Must pass the same handler that was used when
             * attaching the event via ".on".
             * @param string event
             * @param function handler
             */
            off: function (event, handler) {
                if (!eventHandlers[event]) {
                    return;
                }

                var i = 0;
                for (i; i < eventHandlers[event].length; i++) {
                    if (eventHandlers[event][i] === handler) {
                        eventHandlers[event].splice(i, 1);
                    }
                }
            },

            /**
             * Triggers the given event and passes the parameters to all handlers.
             *
             * @param string event
             * @param Array extraParameters
             * @param Object context  If given the handler will be executed in this context
             */
            trigger: function (event, extraParameters, context) {
                if (!eventHandlers[event]) {
                    return;
                }

                var i = 0;
                for (i; i < eventHandlers[event].length; i++) {
                    eventHandlers[event][i].apply(context || windowAlias, extraParameters);
                }
            },

            /**
             * Add plugin
             *
             * @param string pluginName
             * @param Object pluginObj
             */
            addPlugin: function (pluginName, pluginObj) {
                plugins[pluginName] = pluginObj;
            },

            /**
             * Get Tracker (factory method)
             *
             * @param string piwikUrl
             * @param int|string siteId
             * @return Tracker
             */
            getTracker: function (piwikUrl, siteId) {
                if (!isDefined(siteId)) {
                    siteId = this.getAsyncTracker().getSiteId();
                }
                if (!isDefined(piwikUrl)) {
                    piwikUrl = this.getAsyncTracker().getTrackerUrl();
                }

                return new Tracker(piwikUrl, siteId);
            },

            /**
             * Get all created async trackers
             *
             * @return Tracker[]
             */
            getAsyncTrackers: function () {
                return asyncTrackers;
            },

            /**
             * Adds a new tracker. All sent requests will be also sent to the given siteId and piwikUrl.
             * If piwikUrl is not set, current url will be used.
             *
             * @param null|string piwikUrl  If null, will reuse the same tracker URL of the current tracker instance
             * @param int|string siteId
             * @return Tracker
             */
            addTracker: function (piwikUrl, siteId) {
                var tracker;
                if (!asyncTrackers.length) {
                    tracker = createFirstTracker(piwikUrl, siteId);
                } else {
                    tracker = asyncTrackers[0].addTracker(piwikUrl, siteId);
                }
                return tracker;
            },

            /**
             * Get internal asynchronous tracker object.
             *
             * If no parameters are given, it returns the internal asynchronous tracker object. If a piwikUrl and idSite
             * is given, it will try to find an optional
             *
             * @param string piwikUrl
             * @param int|string siteId
             * @return Tracker
             */
            getAsyncTracker: function (piwikUrl, siteId) {

                var firstTracker;
                if (asyncTrackers && asyncTrackers.length && asyncTrackers[0]) {
                    firstTracker = asyncTrackers[0];
                } else {
                    return createFirstTracker(piwikUrl, siteId);
                }

                if (!siteId && !piwikUrl) {
                    // for BC and by default we just return the initially created tracker
                    return firstTracker;
                }

                // we look for another tracker created via `addTracker` method
                if ((!isDefined(siteId) || null === siteId) && firstTracker) {
                    siteId = firstTracker.getSiteId();
                }

                if ((!isDefined(piwikUrl) || null === piwikUrl) && firstTracker) {
                    piwikUrl = firstTracker.getTrackerUrl();
                }

                var tracker, i = 0;
                for (i; i < asyncTrackers.length; i++) {
                    tracker = asyncTrackers[i];
                    if (tracker
                        && String(tracker.getSiteId()) === String(siteId)
                        && tracker.getTrackerUrl() === piwikUrl) {

                        return tracker;
                    }
                }
            },

            /**
             * When calling plugin methods via "_paq.push(['...'])" and the plugin is loaded separately because
             * matomo.js is not writable then there is a chance that first matomo.js is loaded and later the plugin.
             * In this case we would have already executed all "_paq.push" methods and they would not have succeeded
             * because the plugin will be loaded only later. In this case, once a plugin is loaded, it should call
             * "Piwik.retryMissedPluginCalls()" so they will be executed after all.
             *
             * @param string piwikUrl
             * @param int|string siteId
             * @return Tracker
             */
            retryMissedPluginCalls: function () {
                var missedCalls = missedPluginTrackerCalls;
                missedPluginTrackerCalls = [];
                var i = 0;
                for (i; i < missedCalls.length; i++) {
                    apply(missedCalls[i]);
                }
            }
        };

        // Expose Piwik as an AMD module
        if (typeof define === 'function' && define.amd) {
            define('piwik', [], function () { return Piwik; });
            define('matomo', [], function () { return Piwik; });
        }

        return Piwik;
    }());
}

/*!!! pluginTrackerHook */

/* GENERATED: tracker.js */
/*!
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * All information contained herein is, and remains the property of InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

/**
 * To minify this version call
 * cat tracker.js | java -jar ../../js/yuicompressor-2.4.8.jar --type js --line-break 1000 | sed 's/^[/][*]/\/*!/' > tracker.min.js
 */

(function () {
    var timeWhenScriptLoaded = new Date().getTime();
    var timeFirstTrackingRequest = null;
    var debugMode = false;
    var pingIntervalInSeconds = 10;
    var usesCustomInterval = false;
    var isMediaTrackingEnabled = true;
    var customPiwikTrackers = null;
    var stopTrackingAfterXMs = 1000 * 60 * 60 * 3; // we stop after 3 hours
    var documentAlias = document;
    var windowAlias = window;
    var numMediaPlaysTotal = 0;
    var numMediaPlaysTotalOffScreen = 0;

    var mediaTitleFallback = function (){
        return '';
    };
    
    var mediaTrackerInstances = [];

    function getJson()
    {
        if (typeof Piwik === 'object' && typeof Piwik.JSON === 'object') {
            return Piwik.JSON;
        } else if (windowAlias.JSON && windowAlias.JSON.parse && windowAlias.JSON.stringify) {
            return windowAlias.JSON;
        } else if (typeof windowAlias.JSON2 === 'object' && windowAlias.JSON2.parse && windowAlias.JSON2.stringify) {
            return windowAlias.JSON2;
        } else {
            return {parse: function () { return {}; }, stringify: function () { return ''; }}
        }
    }

    var isFirstPlay = true;

    function logConsoleMessage() {
        if (debugMode && 'undefined' !== typeof console && console && console.debug) {
            console.debug.apply(console, arguments);
        }
    }

    function isArray(variable)
    {
        return typeof variable === 'object' && typeof variable.length === 'number';
    }

    function isOpenCast()
    {
        return documentAlias.getElementById('engage_video') && documentAlias.getElementById('videoDisplay1_wrapper');
    }

    function hasJwPlayer()
    {
        return 'function' === typeof jwplayer;
    }

    function hasFlowPlayer()
    {
        return 'function' === typeof flowplayer;
    }

    function setDefaultFallbackTitle(node, tracker)
    {
        if (!tracker.getMediaTitle() && 'function' === typeof mediaTitleFallback) {
            var fallbackTitle = mediaTitleFallback(node);
            if (fallbackTitle) {
                tracker.setMediaTitle(fallbackTitle);
            }
        }
    }

    // first letter is upper as we use it for event tracking as in MediaAudio, MediaVideo
    var mediaType = {AUDIO: 'Audio', VIDEO: 'Video'};

    var urlHelper = {
        getLocation: function ()
        {
            var location = this.location || windowAlias.location;

            if (!location.origin) {
                location.origin = location.protocol + "//" + location.hostname + (location.port ? ':' + location.port: '');
            }

            return location;
        },
        setLocation: function (location)
        {
            this.location = location;
        },
        makeUrlAbsolute: function (url)
        {
            if ((!url || String(url) !== url) && url !== '') {
                // it has to be a string
                return url;
            }

            if (url.indexOf('//') === 0) {
                // eg url without protocol //innocraft.com/movie.mp4
                return this.getLocation().protocol + url;
            }

            if (url.indexOf('://') !== -1) {
                // eg absolute url http://innocraft.com/movie.mp4
                return url;
            }

            if (url.indexOf('/') === 0) {
                // eg url without domain /movie.mp4
                return this.getLocation().origin + url;
            }

            if (url.indexOf('#') === 0 || url.indexOf('?') === 0) {
                // eg only query or hash ?movie=movie.mp4 or #movie
                return this.getLocation().origin + this.getLocation().pathname + url;
            }

            if ('' === url) {
                return this.getLocation().href;
            }

            // eg relative path movie.mp4
            var regexToMatchDir = '(.*\/)';
            var basePath = this.getLocation().origin + this.getLocation().pathname.match(new RegExp(regexToMatchDir))[0];
            return basePath + url;
        }
    };

    var utils = {
        getCurrentTime: function () {
            return new Date().getTime();
        },
        roundTimeToSeconds: function (timeInMs) {
            return Math.round(timeInMs / 1000);
        },
        isNumber: function (text) {
            return !isNaN(text);
        },
        getTimeScriptLoaded: function (text) {
            return timeWhenScriptLoaded;
        },
        generateUniqueId: function () {
            var id = '';
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charLen = chars.length;

            for (var i = 0; i < 16; i++) {
                id += chars.charAt(Math.floor(Math.random() * charLen));
            }

            return id;
        },
        trim: function (text)
        {
            if (text && String(text) === text) {
                return text.replace(/^\s+|\s+$/g, '');
            }

            return text;
        },
        getQueryParameter: function (url, parameter) {
            var regexp = new RegExp('[?&]' + parameter + '(=([^&#]*)|&|#|$)');
            var matches = regexp.exec(url);

            if (!matches) {
                return null;
            }

            if (!matches[2]) {
                return '';
            }

            var value = matches[2].replace(/\+/g, " ");

            return decodeURIComponent(value);
        },
        isDocumentOffScreen: function () {
            return documentAlias && 'undefined' !== documentAlias.hidden && documentAlias.hidden;
        }
    };

    var element = {
        getAttribute: function (node, attributeName) {
            if (node && node.getAttribute && attributeName) {
                return node.getAttribute(attributeName);
            }

            return null;
        },
        setAttribute: function (node, attributeName, attributeValue) {
            if (node && node.setAttribute) {
                node.setAttribute(attributeName, attributeValue);
            }
        },
        isMediaIgnored: function (node) {
            var ignore = element.getAttribute(node, 'data-piwik-ignore');
            if (!!ignore || ignore === '') {
                return true;
            }
            ignore = element.getAttribute(node, 'data-matomo-ignore');
            if (!!ignore || ignore === '') {
                return true;
            }
            return false;
        },
        getMediaResource: function (node, defaultResource) {
            var src = element.getAttribute(node, 'data-matomo-resource');

            if (src) {
                return src;
            }

            src = element.getAttribute(node, 'data-piwik-resource');

            if (src) {
                return src;
            }

            src = element.getAttribute(node, 'src');

            if (src) {
                return src;
            }

            return defaultResource;
        },
        getMediaTitle: function (node) {
            var title = element.getAttribute(node, 'data-matomo-title');

            if (!title) {
                title = element.getAttribute(node, 'data-piwik-title');
            }

            if (!title) {
                title = element.getAttribute(node, 'title');
            }

            if (!title) {
                title = element.getAttribute(node, 'alt');
            }

            return title;
        },
        hasCssClass: function (node, theClass)
        {
            if (node && node.className) {
                var classes = ('' + node.className).split(' ');
                for (var i = 0; i < classes.length; i++) {
                    if (classes[i] === theClass) {
                        return true;
                    }
                }
            }

            return false;
        },
        getFirstParentWithClass: function (node, theClass, maxLevels) {
            if (maxLevels <= 0 || !node || !node.parentNode) {
                return null;
            }

            var parent = node.parentNode;

            if (this.hasCssClass(parent, theClass)) {
                return parent;
            } else {
                return this.getFirstParentWithClass(parent, theClass, --maxLevels);
            }
        },
        isFullscreen: function (node) {
            if (node && documentAlias.fullScreenElement === node
                || documentAlias.mozFullScreenElement === node
                || documentAlias.webkitFullscreenElement === node
                || documentAlias.msFullscreenElement === node) {
                // msFullscreenElement is only ie11
                return true;
            }

            return false;
        }
    };

    function getPiwikTrackers()
    {
        if (null === customPiwikTrackers) {
            if ('object' === typeof Piwik && Piwik.getAsyncTrackers) {
                return Piwik.getAsyncTrackers();
            }
        }

        if (isArray(customPiwikTrackers)) {
            return customPiwikTrackers;
        }

        return [];
    }

    function MediaTracker(playerName, type, resource) {
        this.playerName = playerName;
        this.type = type;
        this.resource = resource;
        this.disabled = false;
        this.reset();
    }

    MediaTracker.piwikTrackers = [];

    MediaTracker.prototype.disable = function () {
        this.disabled = true;
    };

    MediaTracker.prototype.reset = function () {
        this.id = utils.generateUniqueId();
        this.mediaTitle = null;
        this.timeToInitialPlay = null;
        this.width = null;
        this.height = null;
        this.fullscreen = false;
        this.timeout = null;
        this.watchedTime = 0;
        this.lastTimeCheck = null;
        this.isPlaying = false;
        this.isPaused = false;
        this.mediaProgressInSeconds = 0;
        this.mediaLengthInSeconds = 0;
        this.disabled = false;
        this.numPlaysSameMedia = 0;
        this.numPlaysSameMediaOffScreen = 0;
    };

    MediaTracker.prototype.setResource = function (resource) {
        this.resource = resource;
    };

    MediaTracker.prototype.getResource = function () {
        return this.resource;
    };

    MediaTracker.prototype.trackEvent = function (action)
    {
        if (this.disabled) {
            return;
        }

        if (!timeFirstTrackingRequest) {
            timeFirstTrackingRequest = utils.getCurrentTime();
        } else if ((utils.getCurrentTime() - timeFirstTrackingRequest) > stopTrackingAfterXMs) {
            this.disable();
            return;
        }

        var asyncTrackers = getPiwikTrackers();

        var mediaType = 'Media' + this.type;
        var mediaResource = this.mediaTitle || this.resource;

        var args = [mediaType, action, mediaResource];
        args.push(parseInt(Math.round(this.mediaProgressInSeconds), 10));

        if (asyncTrackers && asyncTrackers.length) {
            var i = 0, tracker;

            for (i; i < asyncTrackers.length; i++) {
                tracker = asyncTrackers[i];
                if (tracker && tracker.MediaAnalytics && tracker.MediaAnalytics.isTrackEventsEnabled()) {
                    tracker.trackEvent.apply(tracker, args);
                }
            }
        } else {

            if (typeof _paq === 'undefined') {
                _paq = [];
            }

            args.unshift('trackEvent');
            _paq.push(args);

            logConsoleMessage('piwikWasNotYetInitialized. This means players were scanning too early for media or there are no async trackers');
        }

        logConsoleMessage('trackEvent', mediaType, mediaResource, action);
    };

    MediaTracker.prototype.trackProgress = function (idView, mediaTitle, playerName, mediaType, mediaResource, watchedTimeInSeconds, progressInSeconds, mediaLength, timeToInitialPlay, width, height, fullscreen) {

        if (this.disabled) {
            return;
        }

        if (!timeFirstTrackingRequest) {
            timeFirstTrackingRequest = utils.getCurrentTime();
        } else if ((utils.getCurrentTime() - timeFirstTrackingRequest) > stopTrackingAfterXMs) {
            this.disable();
            return;
        }

        var params = {
            ma_id: idView,
            ma_ti: mediaTitle !== null ? mediaTitle : '',
            ma_pn: playerName,
            ma_mt: mediaType,
            ma_re: mediaResource,
            ma_st: parseInt(watchedTimeInSeconds, 10),
            ma_ps: parseInt(progressInSeconds, 10),
            ma_le: mediaLength,
            ma_ttp: timeToInitialPlay !== null ? timeToInitialPlay : '',
            ma_w: width ? width : '',
            ma_h: height ? height : '',
            ma_fs: fullscreen ? '1' : '0'
        };

        var requestUrl = '';
        for (var index in params) {
            if (Object.prototype.hasOwnProperty.call(params, index)) {
                requestUrl += index + '=' + encodeURIComponent(params[index]) + '&';
            }
        }

        var asyncTrackers = getPiwikTrackers();

        if (asyncTrackers && asyncTrackers.length) {
            var i = 0, tracker;

            for (i; i < asyncTrackers.length; i++) {
                tracker = asyncTrackers[i];
                if (tracker && tracker.MediaAnalytics && tracker.MediaAnalytics.isTrackProgressEnabled()) {
                    tracker.trackRequest(requestUrl);
                }
            }
        } else {

            if (typeof _paq === 'undefined') {
                _paq = [];
            }

            _paq.push(['trackRequest', requestUrl]);

            logConsoleMessage('piwikWasNotYetInitialized. This means players were scanning too early for media or there are no async trackers');
        }

        if (debugMode) {
            // check for debug mode is not really needed but better only to stringify when needed
            logConsoleMessage('trackProgress', getJson().stringify(params));
        }
    };

    MediaTracker.prototype.setFullscreen = function (isFullscreen) {
        if (!this.fullscreen) {
            this.fullscreen = !!isFullscreen;
        }
    };

    MediaTracker.prototype.setWidth = function (width) {
        if (utils.isNumber(width)) {
            this.width = parseInt(width, 10);
        }
    };

    MediaTracker.prototype.setHeight = function (height) {
        if (utils.isNumber(height)) {
            this.height = parseInt(height, 10);
        }
    };

    MediaTracker.prototype.setMediaTitle = function (title) {
        this.mediaTitle = title;
    };

    MediaTracker.prototype.getMediaTitle = function () {
        return this.mediaTitle;
    };

    MediaTracker.prototype.setMediaProgressInSeconds = function (mediaProgressInSeconds) {
        this.mediaProgressInSeconds = mediaProgressInSeconds;
    };

    MediaTracker.prototype.getMediaProgressInSeconds = function () {
        return this.mediaProgressInSeconds;
    };

    MediaTracker.prototype.setMediaTotalLengthInSeconds = function (mediaLengthInSeconds) {
        this.mediaLengthInSeconds = mediaLengthInSeconds;
    };

    MediaTracker.prototype.getMediaTotalLengthInSeconds = function () {
        return this.mediaLengthInSeconds;
    };

    MediaTracker.prototype.play = function () {
        if (this.isPlaying) {
            return; // already playing
        }

        this.isPlaying = true;
        this.startWatchedTime();

        if (isFirstPlay && this.timeToInitialPlay === null) {
            // we want to track time to initial play only once for the first play
            this.timeToInitialPlay = utils.roundTimeToSeconds(utils.getCurrentTime() - utils.getTimeScriptLoaded());
        }

        isFirstPlay = false;

        if (this.isPaused) {
            this.isPaused = false;
            this.trackEvent('resume');
        } else {
            this.trackEvent('play');

            var isOffScreen = utils.isDocumentOffScreen();
            this.numPlaysSameMedia++;
            numMediaPlaysTotal++;

            if (isOffScreen) {
                this.numPlaysSameMediaOffScreen++;
                numMediaPlaysTotalOffScreen++;
            }

            if (this.numPlaysSameMedia > 25 || numMediaPlaysTotal > 50) {
                this.disable();
            } else if (this.numPlaysSameMediaOffScreen > 10 || numMediaPlaysTotalOffScreen > 15) {
                this.disable();
            }
        }

        this.trackUpdate();
    };

    MediaTracker.prototype.startWatchedTime = function () {
        this.lastTimeCheck = utils.getCurrentTime();
    };

    MediaTracker.prototype.stopWatchedTime = function () {
        if (this.lastTimeCheck) {
            this.watchedTime += utils.getCurrentTime() - this.lastTimeCheck;
            this.lastTimeCheck = null;
        }
    };

    // also when buffer start
    MediaTracker.prototype.seekStart = function () {
        if (this.isPlaying) {
            // if the media player is currently not playing, we can easily ignore the seek as it has no effect. Makes
            // sure we do not accidentally start tracking or set video to playing when the video is seeking/buffering
            // initally before the video has even played or when it is not playing
            this.stopWatchedTime();
        }
    };

    // also when buffer finish and media continues playing
    MediaTracker.prototype.seekFinish = function () {
        if (this.isPlaying) {
            // if the media player is currently not playing, we can easily ignore the seek as it has no effect. Makes
            // sure we do not accidentally start tracking or set video to playing when the video is seeking/buffering
            // initally before the video has even played or when it is not playing
            this.startWatchedTime();
        }
    };

    MediaTracker.prototype.pause = function () {
        if (this.isPlaying) {
            this.isPaused = true;
            this.isPlaying = false;

            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }

            this.stopWatchedTime();

            this.trackUpdate();

            this.trackEvent('pause');
        }
    };

    MediaTracker.prototype.finish = function () {
        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }

        this.stopWatchedTime();
        this.trackUpdate();

        this.trackEvent('finish');

        // we generate a new id from now on all events will be counted towards a new "media session".
        // we do not call .reset as it would result in changed media title etc. but only because a media is finished
        // it does not mean the media actually changed so we should also not change the media title
        this.id = utils.generateUniqueId();
        this.timeToInitialPlay = null;
        this.lastTimeCheck = null;
        this.isPlaying = false;
        this.isPaused = false;
        this.watchedTime = 0;
        this.mediaProgressInSeconds = 0;
    };

    MediaTracker.prototype.trackUpdate = function () {
        if (this.timeout) {
            // we are just tracking an update below... if there was an update scheduled... cancel it... otherwise
            // may send eg 2 updates within few seconds
            clearTimeout(this.timeout);
            this.timeout = null;
        }

        var crtTime = utils.getCurrentTime();

        if (this.lastTimeCheck) {
            this.watchedTime += (crtTime - this.lastTimeCheck);
            this.lastTimeCheck = crtTime;
        }

        var mediaLength = this.mediaLengthInSeconds;
        if (!mediaLength || !utils.isNumber(mediaLength)) {
            mediaLength = '';
        } else {
            mediaLength = parseInt(this.mediaLengthInSeconds, 10);
        }

        var watchedTimeInSeconds = utils.roundTimeToSeconds(this.watchedTime);
        var progressInSeconds = this.mediaProgressInSeconds;

        if (progressInSeconds > mediaLength && mediaLength) {
            progressInSeconds = mediaLength;
        }

        this.trackProgress(this.id, this.mediaTitle, this.playerName, this.type, this.resource, watchedTimeInSeconds, progressInSeconds, mediaLength, this.timeToInitialPlay, this.width, this.height, this.fullscreen);
    };

    MediaTracker.prototype.update = function () {
        if (this.timeout) {
            return;
        }

        var watchedTimeInSeconds = utils.roundTimeToSeconds(this.watchedTime);

        var interval = pingIntervalInSeconds;

        if (!usesCustomInterval && (watchedTimeInSeconds >= 3600 || numMediaPlaysTotal > 15)) {
            interval = 120;
        } else if (!usesCustomInterval && (watchedTimeInSeconds >= 1800 || numMediaPlaysTotal > 10)) {
            interval = 90;
        } else if (!usesCustomInterval && (watchedTimeInSeconds >= 600 || numMediaPlaysTotal > 4)) {
            interval = 60;
        } else if (!usesCustomInterval && (watchedTimeInSeconds >= 300 || numMediaPlaysTotal > 2)) {
            interval = 40;
        } else if (!usesCustomInterval && watchedTimeInSeconds >= 60) {
            interval = 20;
        }

        interval = interval * 1000;

        var self = this;
        this.timeout = setTimeout(function () {
            self.trackUpdate();
            self.timeout = null;
        }, interval);
    };

    var players = {
        players: {},
        // when registering we also will directly search for media
        registerPlayer: function (name, player) {
            if (!player || !player.scanForMedia || 'function' !==  typeof player.scanForMedia) {
                throw new Error('A registered player does not implement the scanForMedia function');
            }
            name = name.toLowerCase();
            this.players[name] = player;
        },
        removePlayer: function (name) {
            name = name.toLowerCase();

            delete this.players[name];
        },
        getPlayer: function (name) {
            name = name.toLowerCase();

            if (name in this.players) {
                return this.players[name];
            }

            return null;
        },
        getPlayers: function () {
            return this.players;
        },
        // can be used to re-scan the dom or a particular part of the page for new medias
        scanForMedia: function (documentOrElement) {
            if (!isMediaTrackingEnabled) {
                return;
            }

            if ('undefined' === typeof documentOrElement || !documentOrElement) {
                documentOrElement = document;
            }

            var i;
            for (i in this.players) {
                if (Object.prototype.hasOwnProperty.call(this.players, i)) {
                    this.players[i].scanForMedia(documentOrElement);
                }
            }
        }
    };

    var Html5Player = function (node, type) {
        if (!node) {
            return;
        }

        if (!windowAlias.addEventListener) {
            // html5 audio / video is not supported in this browser
            return;
        }

        if (node.hasPlayerInstance) {
            // when scanning for media multiple times prevent from creating multiple trackers for the same video
            return;
        }

        node.hasPlayerInstance = true;

        var isVideo = mediaType.VIDEO === type;
        var absoluteResource = urlHelper.makeUrlAbsolute(node.currentSrc);
        var resource = element.getMediaResource(node, absoluteResource);

        var playerName = 'html5' + type.toLowerCase();
        if (typeof paella === 'object' && typeof paella.opencast === 'object') {
            playerName = 'paella-opencast';
        } else if (element.getFirstParentWithClass(node, 'video-js', 1)) {
            playerName = 'video.js';
        } else if (element.hasCssClass(node, 'jw-video')) {
            playerName = 'jwplayer';
        } else if (element.getFirstParentWithClass(node, 'flowplayer', 3)) {
            playerName = 'flowplayer';
        }

        var tracker = new MediaTracker(playerName, type, resource);
        mediaTrackerInstances.push(tracker);

        function updateDuration()
        {
            if (node.duration) {
                // duration might be only available now, likely it will be going into the if below and track then the
                // media duration
                tracker.setMediaTotalLengthInSeconds(node.duration);
            }
        }

        function updateDimensions() {
            if (isVideo) {
                if ('undefined' !== typeof node.videoWidth && node.videoWidth) {
                    tracker.setWidth(node.videoWidth);
                } else if ('undefined' !== typeof node.clientWidth && node.clientWidth) {
                    tracker.setWidth(node.clientWidth);
                }

                if ('undefined' !== typeof node.videoHeight && node.videoHeight) {
                    tracker.setHeight(node.videoHeight);
                } else if ('undefined' !== typeof node.clientHeight && node.clientHeight) {
                    tracker.setHeight(node.clientHeight);
                }
                tracker.setFullscreen(element.isFullscreen(node));
            }
        }

        function updateCurrentTime() {
            tracker.setMediaProgressInSeconds(node.currentTime);
        }

        function updateMediaTitle() {
            var title = element.getMediaTitle(node);
            if (title) {
                tracker.setMediaTitle(title);
            } else {
                findCustomPlayerTitleIfNeeded(node, tracker);
            }
        }

        // eg jwplayer or flowplayer may provide custom resource information
        findCustomPlayerResource(node, tracker);

        updateDimensions();
        updateMediaTitle();
        updateDuration();
        updateCurrentTime();

        var isPlaying = false;
        var hasTrackedMediaView = false;
        var currentSource = null;

        if (node.currentSrc) {
            currentSource = node.currentSrc;
        }

        function findCustomPlayerTitleIfNeeded(node, tracker)
        {
            // jwplayer does not let users set an html attribute like title or data-piwik-title so we retrieve it
            // from the player directly if it is loaded. We can get the player Instance which is 2 levels further up
            // in a div.jwplayer element

            if (hasJwPlayer() && !tracker.getMediaTitle()) {
                var jwPlayerDiv = element.getFirstParentWithClass(node, 'jwplayer', 2);

                if (!jwPlayerDiv) {
                    // jwplayer 5 support
                    jwPlayerDiv = element.getFirstParentWithClass(node, 'jwplayer-video', 3);
                    if (jwPlayerDiv && 'undefined' !== typeof jwPlayerDiv.children && jwPlayerDiv.children && jwPlayerDiv.children.length && jwPlayerDiv.children[0]) {
                        // better be to use firstElementChild but not supported in eg IE8/9 afaik
                        jwPlayerDiv = jwPlayerDiv.children[0];
                    }
                }
                if (jwPlayerDiv) {
                    try {
                        var player = jwplayer(jwPlayerDiv);
                        if (player && player.getPlaylistItem) {
                            var item = player.getPlaylistItem();
                            if (item && item.matomoTitle) {
                                tracker.setMediaTitle(item.matomoTitle)
                            } else if (item && item.piwikTitle) {
                                tracker.setMediaTitle(item.piwikTitle)
                            } else if (item && item.title) {
                                tracker.setMediaTitle(item.title)
                            }
                        }
                    } catch (e) {
                        logConsoleMessage(e);
                    }
                }
            }

            if (hasFlowPlayer() && !tracker.getMediaTitle()) {
                var flowPlayerDiv = element.getFirstParentWithClass(node, 'flowplayer', 4);
                if (flowPlayerDiv) {
                    var player = flowplayer(flowPlayerDiv);

                    if (player && player.video && player.video.matomoTitle) {
                        tracker.setMediaTitle(player.video.matomoTitle);
                    } else if (player && player.video && player.video.piwikTitle) {
                        tracker.setMediaTitle(player.video.piwikTitle);
                    } else if (player && player.video && player.video.title) {
                        tracker.setMediaTitle(player.video.title);
                    }
                }
            }

            if (!tracker.getMediaTitle()) {
                var openCastTitle = documentAlias.getElementById('engage_basic_description_title');
                if (openCastTitle && openCastTitle.innerText) {
                    var title = utils.trim(openCastTitle.innerText);
                    if (title) {
                        tracker.setMediaTitle(title);
                    }
                } else if (typeof paella === 'object'
                    && typeof paella.opencast === 'object'
                    && typeof paella.opencast._episode === 'object'
                    && paella.opencast._episode.dcTitle) {
                    var title = utils.trim(paella.opencast._episode.dcTitle);
                    if (title) {
                        tracker.setMediaTitle(title);
                    }
                }
            }

            setDefaultFallbackTitle(node, tracker);
        }

        function findCustomPlayerResource(node, tracker)
        {
            // jwplayer does not let users set an html attribute like title or data-piwik-title so we retrieve it
            // from the player directly if it is loaded. We can get the player Instance which is 2 levels further up
            // in a div.jwplayer element

            if (hasJwPlayer()) {
                var jwPlayerDiv = element.getFirstParentWithClass(node, 'jwplayer', 2);

                if (!jwPlayerDiv) {
                    // jwplayer 5 support
                    jwPlayerDiv = element.getFirstParentWithClass(node, 'jwplayer-video', 3);
                    if (jwPlayerDiv && 'undefined' !== typeof jwPlayerDiv.children && jwPlayerDiv.children && jwPlayerDiv.children.length && jwPlayerDiv.children[0]) {
                        // better be to use firstElementChild but not supported in eg IE8/9 afaik
                        jwPlayerDiv = jwPlayerDiv.children[0];
                    }
                }

                if (jwPlayerDiv) {
                    try {
                        var player = jwplayer(jwPlayerDiv);
                        if (player && player.getPlaylistItem) {
                            // lets overwrite resource by possible playlist item. Useful when item changes after a while
                            var item = player.getPlaylistItem();

                            if (item && 'undefined' !== typeof item.matomoResource && item.matomoResource) {
                                tracker.setResource(item.matomoResource)
                            } else if (item && 'undefined' !== typeof item.piwikResource && item.piwikResource) {
                                tracker.setResource(item.piwikResource)
                            }
                        }
                    } catch (e) {
                        logConsoleMessage(e);
                    }
                }
            }

            if (hasFlowPlayer()) {
                var flowPlayerDiv = element.getFirstParentWithClass(node, 'flowplayer', 4);
                if (flowPlayerDiv) {
                    var player = flowplayer(flowPlayerDiv);
                    if (player && player.video && 'undefined' !== typeof player.video.matomoResource && player.video.matomoResource) {
                        tracker.setResource(player.video.matomoResource);
                    } else if (player && player.video && 'undefined' !== typeof player.video.piwikResource && player.video.piwikResource) {
                        tracker.setResource(player.video.piwikResource);
                    }
                }
            }
        }

        function checkVideoChanged()
        {
            if (!currentSource && node.currentSrc) {
                currentSource = node.currentSrc;
            } else if (currentSource && node.currentSrc && currentSource != node.currentSrc) {
                currentSource = node.currentSrc;
                var absoluteUrl = urlHelper.makeUrlAbsolute(currentSource);
                var previousTitle = tracker.getMediaTitle();
                // the URL has changed and we need to start tracking a new video play
                isPlaying = false;
                tracker.reset();
                tracker.setResource(absoluteUrl);
                tracker.setMediaTitle('');

                var title = element.getMediaTitle(node)
                if (title && title !== previousTitle) {
                    // we make sure the title actually changed and otherwise rather set it to an empty title
                    tracker.setMediaTitle(title);
                } else {
                    findCustomPlayerTitleIfNeeded(node, tracker);
                }

                findCustomPlayerResource(node, tracker);
                updateDuration();
            }
        }

        function trackMediaViewIfPossible()
        {
            if (!hasTrackedMediaView && (tracker.getResource() || tracker.getMediaTitle())) {
                hasTrackedMediaView = true;
                // by now we might also have updated video title information which might not be available initially
                updateMediaTitle(node, tracker);
                findCustomPlayerResource(node, tracker);
                tracker.trackUpdate();
                // we make sure to now track it with video width and height as initially the "has media viewed request"
                // did not have the width/height
            }
        }

        function onResizeOrMetadataUpdate() {
            checkVideoChanged();
            updateDimensions();
            updateDuration();
            updateCurrentTime();
            trackMediaViewIfPossible();
        }

        var seekLastTime = null;
        if (node.loop) {
            seekLastTime = 0; // we set the seek last time to zero as it would otherwise trigger a seek event for the first repeat.
        }
        var numSeekEvents = 0;

        var isHeaderVideo = false;
        if (node.loop && node.autoplay && node.muted) {
            // likely a header video embedded in the top of the website continuously playing...
            // we don't really want to track such videos very often and want to delay sending the updates
            isHeaderVideo = true;
        }

        node.addEventListener('playing', function() {
            checkVideoChanged();

            if ('undefined' !== typeof node.paused && node.paused) {
                return;
            }

            if ('undefined' !== typeof node.ended && node.ended) {
                return;
            }

            if (!isPlaying) {
                updateCurrentTime();
                isPlaying = true;
                tracker.play();
            }

        }, true);
        node.addEventListener('durationchange', updateDuration, true);
        node.addEventListener('loadedmetadata', onResizeOrMetadataUpdate, true);
        node.addEventListener('loadeddata', onResizeOrMetadataUpdate, true);
        node.addEventListener('pause', function() {
            if (node.currentTime && node.duration && node.currentTime === node.duration) {
                // html5 triggers a pause event followed by a finish event when the video is over. We should not
                // track a pause in such a case
                return;
            }
            if (node.seeking) {
                // we are actually seeking and not pausing. Some players still trigger pause event in this case
                return;
            }

            updateCurrentTime();
            isPlaying = false;
            tracker.pause();
        }, true);
        node.addEventListener('seeking', function() {
            if (node.seeking) {
                updateCurrentTime();
                var progress = parseInt(tracker.getMediaProgressInSeconds(), 10);
                if ((seekLastTime === null || seekLastTime !== progress) && numSeekEvents < 25) {
                    // do not trigger event for the same second twice!
                    // also we track max 20 seek events
                    seekLastTime = progress;
                    tracker.trackEvent('seek');
                    numSeekEvents++;
                }
            }
        }, true);
        node.addEventListener('ended', function() {
            isPlaying = false;
            tracker.finish();
        }, true);
        node.addEventListener('timeupdate', function() {
            updateCurrentTime();
            updateDuration();

            if (isVideo && !tracker.width) {
                // sometimes html5 video player does not get a width right away
                updateDimensions();
            }

            if ('undefined' !== typeof node.paused && node.paused) {
                return;
            }

            if ('undefined' !== typeof node.ended && node.ended) {
                return;
            }

            if (isHeaderVideo) {
                var watched = utils.roundTimeToSeconds(tracker.watchedTime);
                var duration = tracker.getMediaTotalLengthInSeconds();
                if (watched >= 30 && duration >= 1 && duration < 30 && (watched / duration) >= 3) {
                    // we stop tracking this after 3 repeats but only if at least played for 30 seconds...
                    tracker.disable();
                }
            }

            // we track below, so it will be counted as viewed for sure
            hasTrackedMediaView = true;

            if (!isPlaying) {
                // in case it is already playing when being loaded
                isPlaying = true;
                tracker.play();
            } else {
                tracker.update();
            }
        }, true);
        node.addEventListener('seeking', function() {
            tracker.seekStart();
        }, true);
        node.addEventListener('seeked', function() {
            updateCurrentTime();
            updateDuration();

            tracker.seekFinish();
        }, true);

        if (isVideo) {
            node.addEventListener('resize', onResizeOrMetadataUpdate, true);

            windowAlias.addEventListener('resize', function () {
                updateDimensions(); // in this case user resized only the browser, no need to check for new media title etc
            }, false);
        }

        // we track the view a little delayed hoping more information becomes available by then, for example a late loaded
        // jwplayer title, etc.
        //  tracker.timeout so if page is unloaded before timeout, we make sure the video view will be tracked onunload
        tracker.timeout = setTimeout(function () {
            // we wait for another second and then track even if resource OR title exists, total length not needed
            onResizeOrMetadataUpdate();
            tracker.timeout = null;
        }, 1500);
    };
    Html5Player.scanForMedia = function (theDocumentOrNode) {
        if (!windowAlias.addEventListener) {
            // html5 audio / video is not supported in this browser
            return;
        }

        var is_open_cast = isOpenCast();

        var html5VideoPlayers = theDocumentOrNode.getElementsByTagName('video');
        for (var i = 0; i < html5VideoPlayers.length; i++) {
            if (!element.isMediaIgnored(html5VideoPlayers[i])) {
                if (is_open_cast) {
                    var wrapper1 = theDocumentOrNode.getElementById('videoDisplay1_wrapper');
                    if (wrapper1 && ('function' === typeof wrapper1.contains) && !wrapper1.contains(html5VideoPlayers[i])) {
                        // for opencast, we only track the first video. It can eg show a presenter and the presentation
                        // in this case we only track the presenter / main video.
                        continue;
                    }
                }
                if (element.getAttribute(html5VideoPlayers[i], 'id') === 'playerContainer_videoContainer_slave_1'
                    && theDocumentOrNode.getElementById('playerContainer_videoContainer_master')) {
                    // for opencast with paella player, we only track the first video.
                    // It can eg show a presenter and the presentation in this case we only track the presenter / main video.
                    continue;
                }

                new Html5Player(html5VideoPlayers[i], mediaType.VIDEO);
            }
        }
        html5VideoPlayers = null;

        var html5AudioPlayers = theDocumentOrNode.getElementsByTagName('audio');
        for (var i = 0; i < html5AudioPlayers.length; i++) {
            if (!element.isMediaIgnored(html5AudioPlayers[i])) {
                new Html5Player(html5AudioPlayers[i], mediaType.AUDIO);
            }
        }
        html5AudioPlayers = null;

        if ('undefined' !== typeof soundManager && soundManager && 'undefined' !== typeof soundManager.sounds) {
            for (var i in soundManager.sounds) {
                if (Object.prototype.hasOwnProperty.call(soundManager.sounds, i)) {
                    var sound = soundManager.sounds[i];
                    if (sound && sound.isHTML5 && sound._a) {
                        if (!element.isMediaIgnored(sound._a)) {
                            new Html5Player(sound._a, mediaType.AUDIO);
                        }
                    }
                }
            }
        }
    };

    var JwPlayerInt = function (node, type) {
        if (!node || !windowAlias.addEventListener) {
            // html5 audio / video is not supported in this browser
            return;
        }

        if (node.hasPlayerInstance || !hasJwPlayer()) {
            // when scanning for media multiple times prevent from creating multiple trackers for the same video
            return;
        }

        var jwPlayerDiv = element.getFirstParentWithClass(node, 'jwplayer', 2);
        if (!jwPlayerDiv) {
            return;
        }

        var player = jwplayer(jwPlayerDiv);

        if (!player || !player.getItem || 'undefined' === (typeof player.getItem())) {
            return;
        }

        node.hasPlayerInstance = true;

        function getResoure(player)
        {
            var item = player.getPlaylistItem();

            if (item && item.matomoResource) {
                return item.matomoResource;
            }

            if (item && item.piwikResource) {
                return item.piwikResource;
            }

            if (item && item.file) {
                return item.file;
            }

            return '';
        }

        function getMediaTitle(player)
        {
            var item = player.getPlaylistItem();

            if (item && item.matomoTitle) {
                return item.matomoTitle;
            }

            if (item && item.piwikTitle) {
                return item.piwikTitle;
            }

            if (item && item.title) {
                return item.title;
            }

            if ('function' === typeof mediaTitleFallback) {
                var fallbackTitle = mediaTitleFallback(node);
                if (fallbackTitle) {
                    return fallbackTitle;
                }
            }

            return null;
        }

        function maybeResetPlayer(player, tracker, currentSource)
        {
            var resource = getResoure(player);
            if (currentSource && resource && currentSource != resource) {
                currentSource = resource;
                // the URL has changed and we need to start tracking a new video play
                tracker.reset();
                tracker.setResource(urlHelper.makeUrlAbsolute(currentSource));
                tracker.setMediaTitle(getMediaTitle(player));
                tracker.setWidth(player.getWidth());
                tracker.setHeight(player.getHeight());
                tracker.setFullscreen(player.getFullscreen());
                return true;
            }

            return false;
        }

        var playerResource = getResoure(player);
        var absoluteResource = urlHelper.makeUrlAbsolute(playerResource);
        var resource = element.getMediaResource(node, absoluteResource);

        var tracker = new MediaTracker('jwplayer', type, resource);
        tracker.setMediaTitle(getMediaTitle(player));
        tracker.setWidth(player.getWidth());
        tracker.setHeight(player.getHeight());
        tracker.setFullscreen(player.getFullscreen());
        mediaTrackerInstances.push(tracker);

        var duration = player.getDuration();
        if (duration) {
            tracker.setMediaTotalLengthInSeconds(duration);
        }

        var isPlaying = false, currentSource = playerResource;
        var seekLastTime = null, numSeekEvents = 0;

        player.on('play', function() {
            maybeResetPlayer(player, tracker, currentSource);

            isPlaying = true;
            tracker.play();
        }, true);

        player.on('playlistItem', function() {
            maybeResetPlayer(player, tracker, currentSource);
            if (player.getState() !== 'playing') {
                isPlaying = false;
            }
        }, true);

        player.on('pause', function() {
            if (player.getPosition() && player.getDuration() && player.getPosition() === player.getDuration()) {
                // it may trigger a pause event followed by a finish event when the video is over. We should not
                // track a pause in such a case
                return;
            }

            tracker.pause();
        }, true);
        player.on('complete', function() { tracker.finish(); }, true);
        player.on('time', function() {
            var position = player.getPosition();
            if (position) {
                tracker.setMediaProgressInSeconds(position);
            }

            var duration = player.getDuration();
            if (duration) {
                tracker.setMediaTotalLengthInSeconds(duration);
            }

            if (isPlaying) {
                tracker.update();
            } else {
                // in case it is already playing when being loaded
                isPlaying = true;
                tracker.play();
            }
        }, true);
        player.on('seek', function() { tracker.seekStart(); }, true);
        player.on('seeked', function() {
            var position = player.getPosition();
            if (position) {
                tracker.setMediaProgressInSeconds(position);
            }

            var duration = player.getDuration();
            if (duration) {
                tracker.setMediaTotalLengthInSeconds(duration);
            }
            tracker.seekFinish();

            var progress = parseInt(tracker.getMediaProgressInSeconds(), 10);
            if ((seekLastTime === null || seekLastTime !== progress) && numSeekEvents < 25) {
                // do not trigger event for the same second twice!
                // also we track max 20 seek events
                seekLastTime = progress;
                tracker.trackEvent('seek');
                numSeekEvents++;
            }
        }, true);

        player.on('resize', function() {
            tracker.setWidth(player.getWidth());
            tracker.setHeight(player.getHeight());
            tracker.setFullscreen(player.getFullscreen());
        }, true);

        player.on('fullscreen', function () {
            tracker.setWidth(player.getWidth());
            tracker.setHeight(player.getHeight());
            tracker.setFullscreen(player.getFullscreen());
        }, false);

        tracker.trackUpdate();
    };
    JwPlayerInt.scanForMedia = function (theDocumentOrNode) {
        if (!windowAlias.addEventListener || !hasJwPlayer()) {
            // this browser is not supported
            return;
        }

        var objects = theDocumentOrNode.getElementsByTagName('object');
        for (var i = 0; i < objects.length; i++) {
            if (!element.isMediaIgnored(objects[i]) && element.hasCssClass(objects[i], 'jw-swf')) {
                new JwPlayerInt(objects[i], mediaType.VIDEO);
            }
        }
        objects = null;
    };

    var VimeoPlayer = function (node, type) {
        // detect universally embedded videos
        if (!node) {
            return;
        }

        if (!windowAlias.addEventListener) {
            // html5 audio / video is not supported in this browser
            return;
        }

        if (node.playerInstance) {
            // when scanning for media multiple times. Prevent creating multiple trackers for the same video
            return;
        }

        node.playerInstance = true;

        var src = element.getAttribute(node, 'src');

        var resourceToTrack = element.getMediaResource(node, null)

        var tracker = new MediaTracker('vimeo', type, resourceToTrack);
        tracker.setWidth(node.clientWidth);
        tracker.setHeight(node.clientHeight);
        tracker.setFullscreen(element.isFullscreen(node));
        mediaTrackerInstances.push(tracker);

        windowAlias.addEventListener('resize', function () {
            tracker.setWidth(node.clientWidth);
            tracker.setHeight(node.clientHeight);
            tracker.setFullscreen(element.isFullscreen(node));
        }, false);

        var title = element.getMediaTitle(node);

        if (title) {
            tracker.setMediaTitle(title);
        }

        node.matomoNumSeekEvents = 0;
        node.matomoSeekLastTime = null;

        var onMessageReceived = function (event) {
            if (!(/^(https?:)?\/\/(player.)?vimeo.com(?=$|\/)/).test(event.origin)) {
                return false;
            }

            if (!event || !event.data) {
                return;
            }

            if (node.contentWindow && event.source && node.contentWindow !== event.source) {
                return;
            }

            var data = event.data;

            if ('string' === typeof data) {
                data = getJson().parse(event.data);
            }

            if (('event' in data && data.event === 'ready') || ('method' in data && data.method === 'ping')) {
                if (playerOrigin === '*') {
                    playerOrigin = event.origin;
                }

                if (!node.isVimeoReady) {
                    node.isVimeoReady = true;
                    postAction('addEventListener', 'play');
                    postAction('addEventListener', 'pause');
                    postAction('addEventListener', 'finish');
                    postAction('addEventListener', 'seek');
                    postAction('addEventListener', 'seeked');
                    postAction('addEventListener', 'playProgress');
                    postAction('getVideoTitle');
                }

                return;
            }

            if ('method' in data) {

                logConsoleMessage('vimeoMethod', data.method);

                switch (data.method) {
                    case 'getVideoTitle':
                        if (data.value) {
                            tracker.setMediaTitle(data.value);
                        } else {
                            setDefaultFallbackTitle(node, tracker);
                        }
                        tracker.trackUpdate();
                        break;

                    case 'getPaused':
                        if (data.value) {
                            tracker.pause();
                        }
                }

                return;
            }

            if ('event' in data) {

                var eventName = data.event;

                logConsoleMessage('vimeoEvent', eventName);

                if (data && data.data) {
                    data = data.data;
                }

                if (tracker && data && data.seconds) {
                    if (tracker.getMediaProgressInSeconds() === data.seconds
                        && (eventName === 'playProgress' || eventName === 'timeupdate')) {
                        // vimeo does eg send a playProgress event every 2 hours, even when it is inactive. To prevent
                        // this bug we do not track anything unless it is updated.
                        // this way we also make it a little faster as we do not have to track an update for the
                        // very same second 4 or 5 times per second.
                        return;
                    }

                    tracker.setMediaProgressInSeconds(data.seconds);
                }

                if (tracker && data && data.duration) {
                    tracker.setMediaTotalLengthInSeconds(data.duration);
                }

                switch (eventName) {
                    case 'play':
                        tracker.play();
                        break;

                    case 'timeupdate':
                    case 'playProgress':

                        if (tracker._isSeeking) {
                            tracker._isSeeking = false;
                            tracker.seekFinish();
                        }

                        tracker.update();
                        break;

                    case 'seek':
                        tracker.seekStart();
                        tracker._isSeeking = true;
                        break;
                    case 'seeked':
                        var progress = parseInt(tracker.getMediaProgressInSeconds(), 10);
                        if ((node.matomoSeekLastTime === null || node.matomoSeekLastTime !== progress) && node.matomoNumSeekEvents < 25) {
                            // do not trigger event for the same second twice!
                            // also we track max 20 seek events
                            node.matomoSeekLastTime = progress;
                            tracker.trackEvent('seek');
                            node.matomoNumSeekEvents++;
                        }
                        break;

                    case 'pause':
                        if (data && data.seconds && data && data.duration && data.seconds === data.duration) {
                            // vimeo triggers a pause event followed by a finish event when the video is over. We should not
                            // track a pause in such a case
                            logConsoleMessage('ignoring pause event because video is finished');
                            break;
                        }

                        setTimeout(function () {
                            // we only track a pause event, if it is still paused in like a second. otherwise it is likely a seek
                            postAction('getPaused');
                        }, 700);

                        break;

                    case 'finish':
                        tracker.finish();
                        break;
                }
            }
        }

        windowAlias.addEventListener('message', onMessageReceived, true);

        var playerOrigin = '*';
        tracker._isSeeking = false;

        function postAction(method, value) {
            var data = {method: method};

            if (value !== undefined) {
                data.value = value;
            }

            if (node && node.contentWindow) {
                if (navigator && navigator.userAgent) {
                    var ieVersion = parseFloat(navigator.userAgent.toLowerCase().replace(/^.*msie (\d+).*$/, '$1'));
                    if (ieVersion >= 8 && ieVersion < 10) {
                        data = getJson().stringify(data);
                    }
                }

                node.contentWindow.postMessage(data, playerOrigin);
            }
        }

        postAction('ping');
    };
    VimeoPlayer.scanForMedia = function (theDocumentOrNode) {

        if (!windowAlias.addEventListener) {
            // vimeo iframe api is not supported in this browser
            return;
        }

        var videos = theDocumentOrNode.getElementsByTagName('iframe');
        for (var i = 0; i < videos.length; i++) {
            if (element.isMediaIgnored(videos[i])) {
                continue;
            }

            var src = element.getAttribute(videos[i], 'src');
            if (src && src.indexOf('player.vimeo.com') > 0) {
                new VimeoPlayer(videos[i], mediaType.VIDEO);
            }
        }
        videos = null;
    };

    var YoutubePlayer = function (node, type) {
        if (!node) {
            return;
        }

        if (!windowAlias.addEventListener) {
            // youtube does not support this browser
            return;
        }

        if (node.playerInstance) {
            // when scanning for media multiple times prevent from creating multiple trackers for the same video
            return;
        }

        var resourceToTrack = element.getMediaResource(node, null);

        var tracker = new MediaTracker('youtube', type, resourceToTrack);
        tracker.setWidth(node.clientWidth);
        tracker.setHeight(node.clientHeight);
        tracker.setFullscreen(element.isFullscreen(node));
        mediaTrackerInstances.push(tracker);

        windowAlias.addEventListener('resize', function () {
            tracker.setWidth(node.clientWidth);
            tracker.setHeight(node.clientHeight);
            tracker.setFullscreen(element.isFullscreen(node));
        }, false);

        var title = element.getMediaTitle(node);

        if (title) {
            tracker.setMediaTitle(title);
        }

        var isSeeking = false;
        var updateInterval = null;

        // we may overwrite the title if no data-piwik-title is set. We can get from the YT API a much better
        // name than from the attributes title or alt
        var canOverwriteTitle = !element.getAttribute(node, 'data-piwik-title') && !element.getAttribute(node, 'data-matomo-title');

        var hasPlayingInitialized = false;
        var isPaused = false;

        var currentVideoId = null;

        node.playerInstance = new YT.Player(node, {
            events: {
                'onReady': function (event) {
                    if (!event || !event.target) {
                        return;
                    }

                    if (canOverwriteTitle && event.target && event.target.getVideoData) {
                        var videoData = event.target.getVideoData();
                        if (videoData && videoData.title) {
                            tracker.setMediaTitle(videoData.title);
                        } else {
                            setDefaultFallbackTitle(node, tracker);
                        }
                    }

                    tracker.trackUpdate();
                },
                'onStateChange': function(event) {
                    if (!event || !event.target) {
                        return;
                    }

                    var target = event.target;

                    var playerState;
                    if (event && 'undefined' !== typeof event.data) {
                        playerState = event.data;
                    } else {
                        if (!target.getPlayerState) {
                            logConsoleMessage('youtubeMissingPlayerState');
                            return;
                        }

                        playerState = target.getPlayerState();
                    }

                    logConsoleMessage('youtubeStateChange', playerState);

                    switch (playerState) {

                        case YT.PlayerState.ENDED:

                            if (target.getCurrentTime) {
                                tracker.setMediaProgressInSeconds(target.getCurrentTime());
                            }

                            if (target.getDuration) {
                                tracker.setMediaTotalLengthInSeconds(target.getDuration());
                            }

                            tracker.finish();
                            if (updateInterval) {
                                clearInterval(updateInterval);
                                updateInterval = null;
                            }
                            break;

                        case YT.PlayerState.PLAYING: // playing

                            var videoData = null;
                            if (target.getVideoData) {
                                videoData = target.getVideoData();
                            }

                            if (!currentVideoId && videoData && videoData.video_id) {
                                currentVideoId = videoData.video_id;
                            } else if (currentVideoId && videoData && videoData.video_id && currentVideoId != videoData.video_id) {
                                currentVideoId = videoData.video_id;
                                // the URL has changed and we need to start playing another video (playlist)
                                tracker.reset();
                                if (target.getVideoUrl) {
                                    tracker.setResource(target.getVideoUrl());
                                }
                                canOverwriteTitle = true;
                                hasPlayingInitialized = false;
                                isSeeking = false;
                                logConsoleMessage('currentVideoId has changed to ' + currentVideoId);
                            }

                            if (target.getCurrentTime) {
                                tracker.setMediaProgressInSeconds(target.getCurrentTime());
                            }

                            if (target.getDuration) {
                                tracker.setMediaTotalLengthInSeconds(target.getDuration());
                            }

                            if (canOverwriteTitle) {
                                if (videoData && videoData.title) {
                                    tracker.setMediaTitle(videoData.title);
                                }

                                canOverwriteTitle = false; // no need from now on to set it again
                            }

                            if (!hasPlayingInitialized || isPaused) {
                                hasPlayingInitialized = true;
                                isPaused = false;
                                isSeeking = false;
                                tracker.play();
                            } else if (isSeeking) {
                                isSeeking = false;
                                tracker.seekFinish();
                            }

                            tracker.update();

                            if (!updateInterval) {
                                updateInterval = setInterval(function () {
                                    if (tracker.isPlaying) {
                                        if (target && target.getCurrentTime) {
                                            tracker.setMediaProgressInSeconds(target.getCurrentTime());
                                        }
                                        tracker.update();
                                    }
                                }, 1 * 1000);
                                // try to send ping every second
                            }
                            break;

                        case -1:
                        case YT.PlayerState.PAUSED:
                            setTimeout(function() {
                                // we need to track pauses with a second delay to differentiate seeks from pauses
                                if (target && target.getPlayerState && target.getPlayerState() == YT.PlayerState.PAUSED) {

                                    if (target && target.getCurrentTime) {
                                        tracker.setMediaProgressInSeconds(target.getCurrentTime());
                                    }

                                    // if still paused after one second, we assume it was actually paused and not soomed
                                    tracker.pause();
                                    isPaused = true;
                                    if (updateInterval) {
                                        clearInterval(updateInterval);
                                        updateInterval = null;
                                    }
                                }
                            }, 1000);

                            break;

                        case YT.PlayerState.BUFFERING:
                            tracker.seekStart();
                            isSeeking = true;
                            if (updateInterval) {
                                clearInterval(updateInterval);
                                updateInterval = null;
                            }
                            break;

                    }
                }
            }
        });
    }

    YoutubePlayer.scanForMedia = function (theDocumentOrNode) {

        if (!windowAlias.addEventListener) {
            // youtube is not supported in this browser
            return;
        }

        var youtubeVideos = [];
        var iframePlayers = theDocumentOrNode.getElementsByTagName('iframe');
        for (var i = 0; i < iframePlayers.length; i++) {
            if (element.isMediaIgnored(iframePlayers[i])) {
                continue;
            }

            var src = element.getAttribute(iframePlayers[i], 'src');
            if (src && (src.indexOf('youtube.com') > 0 || src.indexOf('youtube-nocookie.com') > 0)) {
                element.setAttribute(iframePlayers[i], 'enablejsapi', 'true');
                youtubeVideos.push(iframePlayers[i]);
            }
        }
        iframePlayers = null;

        function replaceMethod(methodNameToReplace, theFunction)
        {
            if (!(methodNameToReplace in window)) {
                return;
            }

            var oldMethodBackup = window[methodNameToReplace];

            if ('function' !== typeof oldMethodBackup) {
                return;
            }

            try {
                if (oldMethodBackup.toString && oldMethodBackup.toString().indexOf('function replaceMe') === 0) {
                    // the method is already replaced, to not replace it again and again and again
                    return;
                }
            } catch (e) {}

            function replaceMe() {
                try {
                    oldMethodBackup.apply(window, [].slice.call(arguments, 0));
                    theFunction();
                } catch (error) {
                    // in case the users method has an error we ignore it.
                    theFunction();
                    throw error;
                }
            };

            window[methodNameToReplace] = replaceMe;
        }

        function isYoutubeLoaded()
        {
            return 'object' === typeof YT && YT && YT.Player;
        }

        function onYoutubeReady() {
            if (!isYoutubeLoaded()) {
                return;
            }
            var iframePlayers = theDocumentOrNode.getElementsByTagName('iframe');
            for (var i = 0; i < iframePlayers.length; i++) {
                if (element.isMediaIgnored(iframePlayers[i])) {
                    continue;
                }

                var src = element.getAttribute(iframePlayers[i], 'src');
                if (src && (src.indexOf('youtube.com') > 0 || src.indexOf('youtube-nocookie.com') > 0)) {
                    if (iframePlayers[i].setAttribute) {
                        iframePlayers[i].setAttribute('enablejsapi', '1');
                    }
                    new YoutubePlayer(iframePlayers[i], mediaType.VIDEO);
                }
            }
        }

        if (youtubeVideos && youtubeVideos.length) {
            if (isYoutubeLoaded()) {
                onYoutubeReady();
            } else {
                if (windowAlias.onYouTubeIframeAPIReady) {
                    // we need to replace each time this method is called if not loaded yet as eg a user could have
                    // overwritten our onYouTubeIframeAPIReady with their custom callback eg between "onReady" and "onLoad"
                    replaceMethod('onYouTubeIframeAPIReady', onYoutubeReady);

                    // we make sure to not load the API again as we assume either we loaded it initially in the else,
                    // or the user loaded it if the user defined the callback
                } else if (windowAlias.onYouTubePlayerAPIReady) {
                    // we need to replace each time this method is called if not loaded yet as eg a user could have
                    // overwritten our onYouTubePlayerAPIReady with their custom callback eg between "onReady" and "onLoad"
                    replaceMethod('onYouTubePlayerAPIReady', onYoutubeReady);

                    // we make sure to not load the API again as we assume either we loaded it initially in the else,
                    // or the user loaded it if the user defined the callback
                } else {
                    windowAlias.onYouTubeIframeAPIReady = onYoutubeReady;

                    var tag = documentAlias.createElement('script');
                    tag.src = "https://www.youtube.com/iframe_api";
                    var scripts = documentAlias.getElementsByTagName('script');
                    if (scripts && scripts.length) {
                        var scriptTag = scripts[0];
                        scriptTag.parentNode.insertBefore(tag, scriptTag);
                    } else if (documentAlias.body) {
                        documentAlias.body.appendChild(tag);
                    }
                }
            }
        }

        youtubeVideos = null;
    };

    players.registerPlayer('html5', Html5Player);
    players.registerPlayer('vimeo', VimeoPlayer);
    players.registerPlayer('youtube', YoutubePlayer);
    players.registerPlayer('jwplayer', JwPlayerInt);

    function enrichTracker(tracker)
    {
        if ('undefined' !== typeof tracker.MediaAnalytics) {
            return;
        }

        tracker.MediaAnalytics = {
            enableEvents: true,
            enableProgress: true,

            disableTrackEvents: function () {
                this.enableEvents = false;
            },
            enableTrackEvents: function () {
                this.enableEvents = true;
            },
            isTrackEventsEnabled: function () {
                return isMediaTrackingEnabled && this.enableEvents;
            },
            disableTrackProgress: function () {
                this.enableProgress = false;
            },
            enableTrackProgress: function () {
                this.enableProgress = true;
            },
            isTrackProgressEnabled: function () {
                return isMediaTrackingEnabled && this.enableProgress;
            }
        };

        Piwik.trigger('MediaAnalytics.TrackerInitialized', [tracker]);
    }

    function callAsyncReadyMethod()
    {
        if (typeof window === 'object' && 'function' === typeof windowAlias.piwikMediaAnalyticsAsyncInit) {
            windowAlias.piwikMediaAnalyticsAsyncInit();
        }
    }

    var jwPlayerFound = false;
    var flowPlayerFound = false;
    function setUpPlayerReadyEvents()
    {
        if (!jwPlayerFound && hasJwPlayer()) {
            jwPlayerFound = true;
            // jw player might be ready only later and eg video element might be there only later. Works only if jwPlayer is loaded
            // before piwik
            var jwPlayerInstance = jwplayer();
            if ('object' === typeof jwPlayerInstance && 'function' === typeof jwPlayerInstance.on) {
                jwPlayerInstance.on('ready', function (event) {
                    players.scanForMedia(document);
                });
            }
        }

        if (!flowPlayerFound && hasFlowPlayer()) {
            flowPlayerFound = true;
            // flowplayer might be ready only later and eg video element might be there only later. Works only if flowplayer is loaded
            // before flowplayer
            flowplayer(function (api, root) {
                if (api) {
                    api.on('ready', function () {
                        players.scanForMedia(document);
                    });
                    api.on('load', function () {
                        players.scanForMedia(document);
                    });
                }
            });

            var flowplayerApi = flowplayer();
            if ('object' === typeof flowplayerApi && 'function' === typeof flowplayerApi.on) {
                flowplayerApi.on('ready', function () {
                    players.scanForMedia(document);
                });
                flowplayerApi.on('load', function () {
                    players.scanForMedia(document);
                });
            }
        }
    }

    function startScanningForMedia()
    {
        // we test for tracker instance only in onReady and onLoad in case tracker instances were created between
        // init Matomo and the onReady or onLoad event

        Piwik.DOM.onReady(function () {
            var trackers = getPiwikTrackers();

            if (!trackers || !isArray(trackers) || !trackers.length) {
                // no single tracker has been created yet. We do not automatically scan for media as a user might only
                // later create a tracker
                return;
            }

            players.scanForMedia(document);
            setUpPlayerReadyEvents();
        });
        Piwik.DOM.onLoad(function () {
            var trackers = getPiwikTrackers();

            if (!trackers || !isArray(trackers) || !trackers.length) {
                // no single tracker has been created yet. We do not automatically scan for media as a user might only
                // later create a tracker
                return;
            }

            players.scanForMedia(document);
            setUpPlayerReadyEvents();
        });
    }

    function init() {
        if ('object' === typeof windowAlias && 'object' === typeof windowAlias.Piwik && 'object' === typeof windowAlias.Piwik.MediaAnalytics) {
            // do not initialize media analytics twice
            return;
        }

        if ('object' === typeof windowAlias && !windowAlias.Piwik) {
            // piwik is not defined yet
            return;
        }

        Piwik.MediaAnalytics = {
            utils: utils,
            url: urlHelper,
            element: element,
            players: players,
            MediaTracker: MediaTracker,
            mediaType: mediaType,
            scanForMedia: function (node) {
                players.scanForMedia(node || document);
            },
            setPingInterval: function (globalMediaPingIntervalInSeconds) {
                if (1 > globalMediaPingIntervalInSeconds) {
                    throw new Error('Ping interval needs to be at least one second');
                }
                usesCustomInterval = true;
                pingIntervalInSeconds = parseInt(globalMediaPingIntervalInSeconds, 10);
            },
            removePlayer: function (playerName) {
                players.removePlayer(playerName);
            },
            addPlayer: function (playerName, player) {
                players.registerPlayer(playerName, player);
            },
            disableMediaAnalytics: function () {
                isMediaTrackingEnabled = false;
            },
            enableMediaAnalytics: function () {
                isMediaTrackingEnabled = true;
            },
            setPiwikTrackers: function (trackers) {
                if (trackers === null) {
                    customPiwikTrackers = null;
                    return;
                }

                if (!isArray(trackers)) {
                    trackers = [trackers];
                }

                customPiwikTrackers = trackers;
            },
            setMediaTitleFallback: function (fallbackCallback) {
                if ('function' !== typeof  fallbackCallback) {
                    throw new Error('The mediaTitleFallback needs to be callback function');
                }
                mediaTitleFallback = fallbackCallback;
            },
            getPiwikTrackers: function () {
                return getPiwikTrackers();
            },
            isMediaAnalyticsEnabled: function () {
                return isMediaTrackingEnabled;
            },
            setMaxTrackingTime: function (stopAfterSeconds) {
                stopTrackingAfterXMs = parseInt(stopAfterSeconds, 10) * 1000;
            },
            enableDebugMode: function () {
                debugMode = true
            }
        };

        Piwik.addPlugin('MediaAnalytics', {
            unload: function () {
                var tracker;
                logConsoleMessage('tracker intances mediaTrackerInstances');
                for (var i = 0; i < mediaTrackerInstances.length; i++) {
                    tracker = mediaTrackerInstances[i];

                    if (tracker && tracker.timeout) {
                        logConsoleMessage('before unload');
                        tracker.trackUpdate();
                    }
                }
            }
        });

        if (windowAlias.Piwik.initialized) {
            // tracker was separately loaded via separate include. we need to enrich already created trackers
            var asyncTrackers = Piwik.getAsyncTrackers();
            var i = 0;
            for (i; i < asyncTrackers.length; i++) {
                enrichTracker(asyncTrackers[i]);
            }

            Piwik.on('TrackerSetup', enrichTracker);

            // now that the methods are set on the tracker instance we check if there were calls that couldn't be executed
            // the first time because the media analytics plugin was not loaded yet (but it is now)
            Piwik.retryMissedPluginCalls();

            callAsyncReadyMethod();
            startScanningForMedia();

        } else {

            Piwik.on('TrackerSetup', enrichTracker);

            Piwik.on('PiwikInitialized', function () {
                callAsyncReadyMethod();

                // at this point the first tracker was created, and all methods called by a user on _paq applied.
                // this means now we can start looking for media because if someone has disabled eg tracking events
                // or tracking progress or enabled debug etc we can be sure the media tracker has been configured
                startScanningForMedia();
            });
        }
    }

    if ('object' === typeof windowAlias.Piwik) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof windowAlias.piwikPluginAsyncInit) {
            windowAlias.piwikPluginAsyncInit = [];
        }

        windowAlias.piwikPluginAsyncInit.push(init);
    }

})();

/* END GENERATED: tracker.js */


/* GENERATED: tracker.js */

/**
 * To minify this version call
 * cat tracker.js | java -jar ../../js/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar --type js --line-break 1000 | sed 's/^[/][*]/\/*!/' > tracker.min.js
 */

(function () {
    // some libraries overwrite the builtin Node class so we have to define them ourselves. If we update libraries below
    // we will need to update the constants and replace eg Node.ELEMENT_NODE with Node_ELEMENT_NODE
    var Node_ELEMENT_NODE = 1;
    var Node_DOCUMENT_NODE = 9;
    var Node_DOCUMENT_TYPE_NODE = 10;
    var Node_COMMENT_NODE = 8;
    var Node_TEXT_NODE = 3;

    // from https://gist.github.com/asfaltboy/8aea7435b888164e8563
    // manual fixes:
    // * isCSSIdentifier() && isCSSIdentChar() escaped dash to \-
    // * fixed looping over prefixedOwnClassNamesArray
    // * fixed / improved ownClassNameCount
    // * if (siblings.children)

    /*!
     * Copyright (C) 2015 Pavel Savshenko
     * Copyright (C) 2011 Google Inc.  All rights reserved.
     * Copyright (C) 2007, 2008 Apple Inc.  All rights reserved.
     * Copyright (C) 2008 Matt Lilek <webkit@mattlilek.com>
     * Copyright (C) 2009 Joseph Pecoraro
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1.  Redistributions of source code must retain the above copyright
     *     notice, this list of conditions and the following disclaimer.
     * 2.  Redistributions in binary form must reproduce the above copyright
     *     notice, this list of conditions and the following disclaimer in the
     *     documentation and/or other materials provided with the distribution.
     * 3.  Neither the name of Apple Computer, Inc. ("Apple") nor the names of
     *     its contributors may be used to endorse or promote products derived
     *     from this software without specific prior written permission.
     *
     * THIS SOFTWARE IS PROVIDED BY APPLE AND ITS CONTRIBUTORS "AS IS" AND ANY
     * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
     * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
     * DISCLAIMED. IN NO EVENT SHALL APPLE OR ITS CONTRIBUTORS BE LIABLE FOR ANY
     * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
     * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
     * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
     * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
     * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     */

    var UTILS = {};
    UTILS.cssPath = function(node, optimized)
    {
        if (node.nodeType !== Node_ELEMENT_NODE)
            return "";
        var steps = [];
        var contextNode = node;
        while (contextNode) {
            var step = UTILS._cssPathStep(contextNode, !!optimized, contextNode === node);
            if (!step)
                break; // Error - bail out early.
            steps.push(step);
            if (step.optimized)
                break;
            contextNode = contextNode.parentNode;
        }
        steps.reverse();
        return steps.join(" > ");
    }
    UTILS._cssPathStep = function(node, optimized, isTargetNode)
    {
        if (node.nodeType !== Node_ELEMENT_NODE)
            return null;

        var id = node.getAttribute("id");
        if (optimized) {
            if (id)
                return new UTILS.DOMNodePathStep(idSelector(id), true);
            var nodeNameLower = node.nodeName.toLowerCase();
            if (nodeNameLower === "body" || nodeNameLower === "head" || nodeNameLower === "html")
                return new UTILS.DOMNodePathStep(node.nodeName.toLowerCase(), true);
        }
        var nodeName = node.nodeName.toLowerCase();

        if (id)
            return new UTILS.DOMNodePathStep(nodeName.toLowerCase() + idSelector(id), true);
        var parent = node.parentNode;
        if (!parent || parent.nodeType === Node_DOCUMENT_NODE)
            return new UTILS.DOMNodePathStep(nodeName.toLowerCase(), true);

        /**
         * @param {UTILS.DOMNode} node
         * @return {Array.<string>}
         */
        function prefixedElementClassNames(node)
        {
            var classAttribute = node.getAttribute("class");
            if (!classAttribute)
                return [];



            return classAttribute.split(/\s+/g).filter(Boolean).map(function(name) {
                // The prefix is required to store "__proto__" in a object-based map.
                return "$" + name;
            });
        }

        /**
         * @param {string} id
         * @return {string}
         */
        function idSelector(id)
        {
            return "#" + escapeIdentifierIfNeeded(id);
        }

        /**
         * @param {string} ident
         * @return {string}
         */
        function escapeIdentifierIfNeeded(ident)
        {
            if (isCSSIdentifier(ident))
                return ident;
            var shouldEscapeFirst = /^(?:[0-9]|-[0-9-]?)/.test(ident);
            var lastIndex = ident.length - 1;
            return ident.replace(/./g, function(c, i) {
                return ((shouldEscapeFirst && i === 0) || !isCSSIdentChar(c)) ? escapeAsciiChar(c, i === lastIndex) : c;
            });
        }

        /**
         * @param {string} c
         * @param {boolean} isLast
         * @return {string}
         */
        function escapeAsciiChar(c, isLast)
        {
            return "\\" + toHexByte(c) + (isLast ? "" : " ");
        }

        /**
         * @param {string} c
         */
        function toHexByte(c)
        {
            var hexByte = c.charCodeAt(0).toString(16);
            if (hexByte.length === 1)
                hexByte = "0" + hexByte;
            return hexByte;
        }

        /**
         * @param {string} c
         * @return {boolean}
         */
        function isCSSIdentChar(c)
        {
            if (/[a-zA-Z0-9_\-]/.test(c))
                return true;
            return c.charCodeAt(0) >= 0xA0;
        }

        /**
         * @param {string} value
         * @return {boolean}
         */
        function isCSSIdentifier(value)
        {
            return /^-?[a-zA-Z_][a-zA-Z0-9_\-]*$/.test(value);
        }

        function arrayFlip(theArray) {
            var flipped = {}, index;

            for (index = 0; index < theArray.length; index++) {
                flipped[theArray[index]] = true
            }

            return flipped;
        }

        var prefixedOwnClassNamesArray = prefixedElementClassNames(node);
        var needsClassNames = false;
        var needsNthChild = false;
        var ownIndex = -1;
        var siblings = parent.children;
        if (siblings && siblings.length) {
            for (var i = 0; (ownIndex === -1 || !needsNthChild) && i < siblings.length; ++i) {
                var sibling = siblings[i];
                if (sibling === node) {
                    ownIndex = i;
                    continue;
                }
                if (needsNthChild) {
                    continue;
                }
                if (sibling.nodeName.toLowerCase() !== nodeName.toLowerCase()) {
                    continue;
                }

                needsClassNames = true;
                // fixed by innocraft, otherwise when counting it would include .length when counting entries

                var ownClassNames = arrayFlip(prefixedOwnClassNamesArray);
                var ownClassNameCount = prefixedOwnClassNamesArray.length;

                if (ownClassNameCount === 0) {
                    needsNthChild = true;
                    continue;
                }
                var siblingClassNamesArray = prefixedElementClassNames(sibling);
                for (var j = 0; j < siblingClassNamesArray.length; ++j) {
                    var siblingClass = siblingClassNamesArray[j];
                    // FIXED BY INNOCRAFT, it may return 0 which is a valid value and considered found
                    if (!ownClassNames.hasOwnProperty(siblingClass)) {
                        continue;
                    }

                    delete ownClassNames[siblingClass];
                    ownClassNameCount--;

                    if (!ownClassNameCount) {
                        needsNthChild = true;
                        break;
                    }
                }
            }
        }

        var result = nodeName.toLowerCase();
        if (isTargetNode && nodeName.toLowerCase() === "input" && node.getAttribute("type") && !node.getAttribute("id") && !node.getAttribute("class"))
            result += "[type=\"" + node.getAttribute("type") + "\"]";
        if (needsNthChild) {
            result += ":nth-child(" + (ownIndex + 1) + ")";
        } else if (needsClassNames) {
            // FIXED THIS LOOP BY INNOCRAFT otherwise would include .length when iterating
            for (var prefixedName = 0; prefixedName < prefixedOwnClassNamesArray.length; prefixedName++)
                // for (var prefixedName in prefixedOwnClassNamesArray.keySet())
                result += "." + escapeIdentifierIfNeeded(prefixedOwnClassNamesArray[prefixedName].substr(1));
        }

        return new UTILS.DOMNodePathStep(result, false);
    }

    UTILS.DOMNodePathStep = function(value, optimized)
    {
        this.value = value;
        this.optimized = optimized || false;
    }

    UTILS.DOMNodePathStep.prototype = {
        toString: function()
        {
            return this.value;
        }
    }

/*!
 * Copyright 2011 Google Inc.
 *
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
var __extends = function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
        function __() { this.constructor = d; }
        __.prototype = b.prototype;
        d.prototype = new __();
    };
var MutationObserverCtor;
if (typeof WebKitMutationObserver !== 'undefined') {
    MutationObserverCtor = WebKitMutationObserver;
} else if (typeof MutationObserver !== 'undefined') {
    MutationObserverCtor = MutationObserver;
}

if (typeof MutationObserverCtor !== 'undefined' && MutationObserverCtor) {

    var NodeMap = (function () {
        function NodeMap() {
            this.nodes = [];
            this.values = [];
        }
        NodeMap.prototype.isIndex = function (s) {
            return +s === s >>> 0;
        };
        NodeMap.prototype.nodeId = function (node) {
            var id = node[NodeMap.ID_PROP];
            if (!id) {
                // note: we may falsely assign an id that has already been used... and we cannot fix it really.
                // it is the case when for debugging purposes we "delete window.Piwik/Matomo" and load the tracker
                // again. The problem is that we cannot know the currently highest ID in use by any element.
                // this.values and this.nodes will be empty, Nodemap.nextId_ will be set to 0, but there will be
                // already elements having IDs like 826... when we then add new elements randomly, they will get an ID
                // like 1 which points actually to a different node... it is not an issue only when debugging

                id = node[NodeMap.ID_PROP] = NodeMap.nextId_++;
            }
            return id;
        };
        NodeMap.prototype.set = function (node, value) {
            var id = this.nodeId(node);
            this.nodes[id] = node;
            this.values[id] = value;
        };
        NodeMap.prototype.get = function (node) {
            var id = this.nodeId(node);
            return this.values[id];
        };
        NodeMap.prototype.has = function (node) {
            return this.nodeId(node) in this.nodes;
        };
        NodeMap.prototype['delete'] = function (node) {
            var id = this.nodeId(node);
            delete this.nodes[id];
            this.values[id] = undefined;
        };
        NodeMap.prototype.keys = function () {
            var nodes = [];
            for (var id in this.nodes) {
                if (!this.isIndex(id))
                    continue;
                nodes.push(this.nodes[id]);
            }
            return nodes;
        };
        NodeMap.ID_PROP = '__mutation_summary_node_map_id__';
        NodeMap.nextId_ = 1;
        return NodeMap;
    })();
    /**
     *  var reachableMatchableProduct = [
     *  //  STAYED_OUT,  ENTERED,     STAYED_IN,   EXITED
     *    [ STAYED_OUT,  STAYED_OUT,  STAYED_OUT,  STAYED_OUT ], // STAYED_OUT
     *    [ STAYED_OUT,  ENTERED,     ENTERED,     STAYED_OUT ], // ENTERED
     *    [ STAYED_OUT,  ENTERED,     STAYED_IN,   EXITED     ], // STAYED_IN
     *    [ STAYED_OUT,  STAYED_OUT,  EXITED,      EXITED     ]  // EXITED
     *  ];
     */
    var Movement;
    (function (Movement) {
        Movement[Movement["STAYED_OUT"] = 0] = "STAYED_OUT";
        Movement[Movement["ENTERED"] = 1] = "ENTERED";
        Movement[Movement["STAYED_IN"] = 2] = "STAYED_IN";
        Movement[Movement["REPARENTED"] = 3] = "REPARENTED";
        Movement[Movement["REORDERED"] = 4] = "REORDERED";
        Movement[Movement["EXITED"] = 5] = "EXITED";
    })(Movement || (Movement = {}));
    function enteredOrExited(changeType) {
        return changeType === Movement.ENTERED || changeType === Movement.EXITED;
    }
    var NodeChange = (function () {
        function NodeChange(node, childList, attributes, characterData, oldParentNode, added, attributeOldValues, characterDataOldValue) {
            if (childList === void 0) { childList = false; }
            if (attributes === void 0) { attributes = false; }
            if (characterData === void 0) { characterData = false; }
            if (oldParentNode === void 0) { oldParentNode = null; }
            if (added === void 0) { added = false; }
            if (attributeOldValues === void 0) { attributeOldValues = null; }
            if (characterDataOldValue === void 0) { characterDataOldValue = null; }
            this.node = node;
            this.childList = childList;
            this.attributes = attributes;
            this.characterData = characterData;
            this.oldParentNode = oldParentNode;
            this.added = added;
            this.attributeOldValues = attributeOldValues;
            this.characterDataOldValue = characterDataOldValue;
            this.isCaseInsensitive =
                this.node.nodeType === Node_ELEMENT_NODE &&
                this.node instanceof HTMLElement &&
                this.node.ownerDocument instanceof HTMLDocument;
        }
        NodeChange.prototype.getAttributeOldValue = function (name) {
            if (!this.attributeOldValues)
                return undefined;
            if (this.isCaseInsensitive)
                name = name.toLowerCase();
            return this.attributeOldValues[name];
        };
        NodeChange.prototype.getAttributeNamesMutated = function () {
            var names = [];
            if (!this.attributeOldValues)
                return names;
            for (var name in this.attributeOldValues) {
                names.push(name);
            }
            return names;
        };
        NodeChange.prototype.attributeMutated = function (name, oldValue) {
            this.attributes = true;
            this.attributeOldValues = this.attributeOldValues || {};
            if (name in this.attributeOldValues)
                return;
            this.attributeOldValues[name] = oldValue;
        };
        NodeChange.prototype.characterDataMutated = function (oldValue) {
            if (this.characterData)
                return;
            this.characterData = true;
            this.characterDataOldValue = oldValue;
        };
        // Note: is it possible to receive a removal followed by a removal. This
        // can occur if the removed node is added to an non-observed node, that
        // node is added to the observed area, and then the node removed from
        // it.
        NodeChange.prototype.removedFromParent = function (parent) {
            this.childList = true;
            if (this.added || this.oldParentNode)
                this.added = false;
            else
                this.oldParentNode = parent;
        };
        NodeChange.prototype.insertedIntoParent = function () {
            this.childList = true;
            this.added = true;
        };
        // An node's oldParent is
        //   -its present parent, if its parentNode was not changed.
        //   -null if the first thing that happened to it was an add.
        //   -the node it was removed from if the first thing that happened to it
        //      was a remove.
        NodeChange.prototype.getOldParent = function () {
            if (this.childList) {
                if (this.oldParentNode)
                    return this.oldParentNode;
                if (this.added)
                    return null;
            }
            return this.node.parentNode;
        };
        return NodeChange;
    })();
    var ChildListChange = (function () {
        function ChildListChange() {
            this.added = new NodeMap();
            this.removed = new NodeMap();
            this.maybeMoved = new NodeMap();
            this.oldPrevious = new NodeMap();
            this.moved = undefined;
        }
        return ChildListChange;
    })();
    var TreeChanges = (function (_super) {
        __extends(TreeChanges, _super);
        function TreeChanges(rootNode, mutations) {
            _super.call(this);
            this.rootNode = rootNode;
            this.reachableCache = undefined;
            this.wasReachableCache = undefined;
            this.anyParentsChanged = false;
            this.anyAttributesChanged = false;
            this.anyCharacterDataChanged = false;
            for (var m = 0; m < mutations.length; m++) {
                var mutation = mutations[m];
                switch (mutation.type) {
                    case 'childList':
                        this.anyParentsChanged = true;
                        for (var i = 0; i < mutation.removedNodes.length; i++) {
                            var node = mutation.removedNodes[i];
                            this.getChange(node).removedFromParent(mutation.target);
                        }
                        for (var i = 0; i < mutation.addedNodes.length; i++) {
                            var node = mutation.addedNodes[i];
                            this.getChange(node).insertedIntoParent();
                        }
                        break;
                    case 'attributes':
                        this.anyAttributesChanged = true;
                        var change = this.getChange(mutation.target);
                        change.attributeMutated(mutation.attributeName, mutation.oldValue);
                        break;
                    case 'characterData':
                        this.anyCharacterDataChanged = true;
                        var change = this.getChange(mutation.target);
                        change.characterDataMutated(mutation.oldValue);
                        break;
                }
            }
        }
        TreeChanges.prototype.getChange = function (node) {
            var change = this.get(node);
            if (!change) {
                change = new NodeChange(node);
                this.set(node, change);
            }
            return change;
        };
        TreeChanges.prototype.getOldParent = function (node) {
            var change = this.get(node);
            return change ? change.getOldParent() : node.parentNode;
        };
        TreeChanges.prototype.getIsReachable = function (node) {
            if (node === this.rootNode)
                return true;
            if (!node)
                return false;
            this.reachableCache = this.reachableCache || new NodeMap();
            var isReachable = this.reachableCache.get(node);
            if (isReachable === undefined) {
                isReachable = this.getIsReachable(node.parentNode);
                this.reachableCache.set(node, isReachable);
            }
            return isReachable;
        };
        // A node wasReachable if its oldParent wasReachable.
        TreeChanges.prototype.getWasReachable = function (node) {
            if (node === this.rootNode)
                return true;
            if (!node)
                return false;
            this.wasReachableCache = this.wasReachableCache || new NodeMap();
            var wasReachable = this.wasReachableCache.get(node);
            if (wasReachable === undefined) {
                wasReachable = this.getWasReachable(this.getOldParent(node));
                this.wasReachableCache.set(node, wasReachable);
            }
            return wasReachable;
        };
        TreeChanges.prototype.reachabilityChange = function (node) {
            if (this.getIsReachable(node)) {
                return this.getWasReachable(node) ?
                    Movement.STAYED_IN : Movement.ENTERED;
            }
            return this.getWasReachable(node) ?
                Movement.EXITED : Movement.STAYED_OUT;
        };
        return TreeChanges;
    })(NodeMap);
    var MutationProjection = (function () {
        // TOOD(any)
        function MutationProjection(rootNode, mutations, selectors, calcReordered, calcOldPreviousSibling) {
            this.rootNode = rootNode;
            this.mutations = mutations;
            this.selectors = selectors;
            this.calcReordered = calcReordered;
            this.calcOldPreviousSibling = calcOldPreviousSibling;
            this.treeChanges = new TreeChanges(rootNode, mutations);
            this.entered = [];
            this.exited = [];
            this.stayedIn = new NodeMap();
            this.visited = new NodeMap();
            this.childListChangeMap = undefined;
            this.characterDataOnly = undefined;
            this.matchCache = undefined;
            this.processMutations();
        }
        MutationProjection.prototype.processMutations = function () {
            if (!this.treeChanges.anyParentsChanged &&
                !this.treeChanges.anyAttributesChanged)
                return;
            var changedNodes = this.treeChanges.keys();
            for (var i = 0; i < changedNodes.length; i++) {
                this.visitNode(changedNodes[i], undefined);
            }
        };
        MutationProjection.prototype.visitNode = function (node, parentReachable) {
            if (this.visited.has(node))
                return;
            this.visited.set(node, true);
            var change = this.treeChanges.get(node);
            var reachable = parentReachable;
            // node inherits its parent's reachability change unless
            // its parentNode was mutated.
            if ((change && change.childList) || reachable == undefined)
                reachable = this.treeChanges.reachabilityChange(node);
            if (reachable === Movement.STAYED_OUT)
                return;
            // Cache match results for sub-patterns.
            this.matchabilityChange(node);
            if (reachable === Movement.ENTERED) {
                this.entered.push(node);
            }
            else if (reachable === Movement.EXITED) {
                this.exited.push(node);
                this.ensureHasOldPreviousSiblingIfNeeded(node);
            }
            else if (reachable === Movement.STAYED_IN) {
                var movement = Movement.STAYED_IN;
                if (change && change.childList) {
                    if (change.oldParentNode !== node.parentNode) {
                        movement = Movement.REPARENTED;
                        this.ensureHasOldPreviousSiblingIfNeeded(node);
                    }
                    else if (this.calcReordered && this.wasReordered(node)) {
                        movement = Movement.REORDERED;
                    }
                }
                this.stayedIn.set(node, movement);
            }
            if (reachable === Movement.STAYED_IN)
                return;
            // reachable === ENTERED || reachable === EXITED.
            for (var child = node.firstChild; child; child = child.nextSibling) {
                this.visitNode(child, reachable);
            }
        };
        MutationProjection.prototype.ensureHasOldPreviousSiblingIfNeeded = function (node) {
            if (!this.calcOldPreviousSibling)
                return;
            this.processChildlistChanges();
            var parentNode = node.parentNode;
            var nodeChange = this.treeChanges.get(node);
            if (nodeChange && nodeChange.oldParentNode)
                parentNode = nodeChange.oldParentNode;
            var change = this.childListChangeMap.get(parentNode);
            if (!change) {
                change = new ChildListChange();
                this.childListChangeMap.set(parentNode, change);
            }
            if (!change.oldPrevious.has(node)) {
                change.oldPrevious.set(node, node.previousSibling);
            }
        };
        MutationProjection.prototype.getChanged = function (summary, selectors, characterDataOnly) {
            this.selectors = selectors;
            this.characterDataOnly = characterDataOnly;
            for (var i = 0; i < this.entered.length; i++) {
                var node = this.entered[i];
                var matchable = this.matchabilityChange(node);
                if (matchable === Movement.ENTERED || matchable === Movement.STAYED_IN)
                    summary.added.push(node);
            }
            var stayedInNodes = this.stayedIn.keys();
            for (var i = 0; i < stayedInNodes.length; i++) {
                var node = stayedInNodes[i];
                var matchable = this.matchabilityChange(node);
                if (matchable === Movement.ENTERED) {
                    summary.added.push(node);
                }
                else if (matchable === Movement.EXITED) {
                    summary.removed.push(node);
                }
                else if (matchable === Movement.STAYED_IN && (summary.reparented || summary.reordered)) {
                    var movement = this.stayedIn.get(node);
                    if (summary.reparented && movement === Movement.REPARENTED)
                        summary.reparented.push(node);
                    else if (summary.reordered && movement === Movement.REORDERED)
                        summary.reordered.push(node);
                }
            }
            for (var i = 0; i < this.exited.length; i++) {
                var node = this.exited[i];
                var matchable = this.matchabilityChange(node);
                if (matchable === Movement.EXITED || matchable === Movement.STAYED_IN)
                    summary.removed.push(node);
            }
        };
        MutationProjection.prototype.getOldParentNode = function (node) {
            var change = this.treeChanges.get(node);
            if (change && change.childList)
                return change.oldParentNode ? change.oldParentNode : null;
            var reachabilityChange = this.treeChanges.reachabilityChange(node);
            if (reachabilityChange === Movement.STAYED_OUT || reachabilityChange === Movement.ENTERED)
                throw Error('getOldParentNode requested on invalid node.');
            return node.parentNode;
        };
        MutationProjection.prototype.getOldPreviousSibling = function (node) {
            var parentNode = node.parentNode;
            var nodeChange = this.treeChanges.get(node);
            if (nodeChange && nodeChange.oldParentNode)
                parentNode = nodeChange.oldParentNode;
            var change = this.childListChangeMap.get(parentNode);
            if (!change)
                throw Error('getOldPreviousSibling requested on invalid node.');
            return change.oldPrevious.get(node);
        };
        MutationProjection.prototype.getOldAttribute = function (element, attrName) {
            var change = this.treeChanges.get(element);
            if (!change || !change.attributes)
                throw Error('getOldAttribute requested on invalid node.');
            var value = change.getAttributeOldValue(attrName);
            if (value === undefined)
                throw Error('getOldAttribute requested for unchanged attribute name.');
            return value;
        };
        MutationProjection.prototype.attributeChangedNodes = function (includeAttributes) {
            if (!this.treeChanges.anyAttributesChanged)
                return {}; // No attributes mutations occurred.
            var attributeFilter;
            var caseInsensitiveFilter;
            if (includeAttributes) {
                attributeFilter = {};
                caseInsensitiveFilter = {};
                for (var i = 0; i < includeAttributes.length; i++) {
                    var attrName = includeAttributes[i];
                    attributeFilter[attrName] = true;
                    caseInsensitiveFilter[attrName.toLowerCase()] = attrName;
                }
            }
            var result = {};
            var nodes = this.treeChanges.keys();
            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                var change = this.treeChanges.get(node);
                if (!change.attributes)
                    continue;
                if (Movement.STAYED_IN !== this.treeChanges.reachabilityChange(node) ||
                    Movement.STAYED_IN !== this.matchabilityChange(node)) {
                    continue;
                }
                var element = node;
                var changedAttrNames = change.getAttributeNamesMutated();
                for (var j = 0; j < changedAttrNames.length; j++) {
                    var attrName = changedAttrNames[j];
                    if (attributeFilter &&
                        !attributeFilter[attrName] &&
                        !(change.isCaseInsensitive && caseInsensitiveFilter[attrName])) {
                        continue;
                    }
                    var oldValue = change.getAttributeOldValue(attrName);
                    if (oldValue === element.getAttribute(attrName))
                        continue;
                    if (caseInsensitiveFilter && change.isCaseInsensitive)
                        attrName = caseInsensitiveFilter[attrName];
                    result[attrName] = result[attrName] || [];
                    result[attrName].push(element);
                }
            }
            return result;
        };
        MutationProjection.prototype.getOldCharacterData = function (node) {
            var change = this.treeChanges.get(node);
            if (!change || !change.characterData)
                throw Error('getOldCharacterData requested on invalid node.');
            return change.characterDataOldValue;
        };
        MutationProjection.prototype.getCharacterDataChanged = function () {
            if (!this.treeChanges.anyCharacterDataChanged)
                return []; // No characterData mutations occurred.
            var nodes = this.treeChanges.keys();
            var result = [];
            for (var i = 0; i < nodes.length; i++) {
                var target = nodes[i];
                if (Movement.STAYED_IN !== this.treeChanges.reachabilityChange(target))
                    continue;
                var change = this.treeChanges.get(target);
                if (!change.characterData ||
                    target.textContent == change.characterDataOldValue)
                    continue;
                result.push(target);
            }
            return result;
        };
        MutationProjection.prototype.computeMatchabilityChange = function (selector, el) {
            if (!this.matchCache)
                this.matchCache = [];
            if (!this.matchCache[selector.uid])
                this.matchCache[selector.uid] = new NodeMap();
            var cache = this.matchCache[selector.uid];
            var result = cache.get(el);
            if (result === undefined) {
                result = selector.matchabilityChange(el, this.treeChanges.get(el));
                cache.set(el, result);
            }
            return result;
        };
        MutationProjection.prototype.matchabilityChange = function (node) {
            var _this = this;
            // TODO(rafaelw): Include PI, CDATA?
            // Only include text nodes.
            if (this.characterDataOnly) {
                switch (node.nodeType) {
                    case Node_COMMENT_NODE:
                    case Node_TEXT_NODE:
                        return Movement.STAYED_IN;
                    default:
                        return Movement.STAYED_OUT;
                }
            }
            // No element filter. Include all nodes.
            if (!this.selectors)
                return Movement.STAYED_IN;
            // Element filter. Exclude non-elements.
            if (node.nodeType !== Node_ELEMENT_NODE)
                return Movement.STAYED_OUT;
            var el = node;
            var matchChanges = this.selectors.map(function (selector) {
                return _this.computeMatchabilityChange(selector, el);
            });
            var accum = Movement.STAYED_OUT;
            var i = 0;
            while (accum !== Movement.STAYED_IN && i < matchChanges.length) {
                switch (matchChanges[i]) {
                    case Movement.STAYED_IN:
                        accum = Movement.STAYED_IN;
                        break;
                    case Movement.ENTERED:
                        if (accum === Movement.EXITED)
                            accum = Movement.STAYED_IN;
                        else
                            accum = Movement.ENTERED;
                        break;
                    case Movement.EXITED:
                        if (accum === Movement.ENTERED)
                            accum = Movement.STAYED_IN;
                        else
                            accum = Movement.EXITED;
                        break;
                }
                i++;
            }
            return accum;
        };
        MutationProjection.prototype.getChildlistChange = function (el) {
            var change = this.childListChangeMap.get(el);
            if (!change) {
                change = new ChildListChange();
                this.childListChangeMap.set(el, change);
            }
            return change;
        };
        MutationProjection.prototype.processChildlistChanges = function () {
            if (this.childListChangeMap)
                return;
            this.childListChangeMap = new NodeMap();
            for (var i = 0; i < this.mutations.length; i++) {
                var mutation = this.mutations[i];
                if (mutation.type != 'childList')
                    continue;
                if (this.treeChanges.reachabilityChange(mutation.target) !== Movement.STAYED_IN &&
                    !this.calcOldPreviousSibling)
                    continue;
                var change = this.getChildlistChange(mutation.target);
                var oldPrevious = mutation.previousSibling;
                function recordOldPrevious(node, previous) {
                    if (!node ||
                        change.oldPrevious.has(node) ||
                        change.added.has(node) ||
                        change.maybeMoved.has(node))
                        return;
                    if (previous &&
                        (change.added.has(previous) ||
                        change.maybeMoved.has(previous)))
                        return;
                    change.oldPrevious.set(node, previous);
                }
                for (var j = 0; j < mutation.removedNodes.length; j++) {
                    var node = mutation.removedNodes[j];
                    recordOldPrevious(node, oldPrevious);
                    if (change.added.has(node)) {
                        change.added['delete'](node);
                    }
                    else {
                        change.removed.set(node, true);
                        change.maybeMoved['delete'](node);
                    }
                    oldPrevious = node;
                }
                recordOldPrevious(mutation.nextSibling, oldPrevious);
                for (var j = 0; j < mutation.addedNodes.length; j++) {
                    var node = mutation.addedNodes[j];
                    if (change.removed.has(node)) {
                        change.removed['delete'](node);
                        change.maybeMoved.set(node, true);
                    }
                    else {
                        change.added.set(node, true);
                    }
                }
            }
        };
        MutationProjection.prototype.wasReordered = function (node) {
            if (!this.treeChanges.anyParentsChanged)
                return false;
            this.processChildlistChanges();
            var parentNode = node.parentNode;
            var nodeChange = this.treeChanges.get(node);
            if (nodeChange && nodeChange.oldParentNode)
                parentNode = nodeChange.oldParentNode;
            var change = this.childListChangeMap.get(parentNode);
            if (!change)
                return false;
            if (change.moved)
                return change.moved.get(node);
            change.moved = new NodeMap();
            var pendingMoveDecision = new NodeMap();
            function isMoved(node) {
                if (!node)
                    return false;
                if (!change.maybeMoved.has(node))
                    return false;
                var didMove = change.moved.get(node);
                if (didMove !== undefined)
                    return didMove;
                if (pendingMoveDecision.has(node)) {
                    didMove = true;
                }
                else {
                    pendingMoveDecision.set(node, true);
                    didMove = getPrevious(node) !== getOldPrevious(node);
                }
                if (pendingMoveDecision.has(node)) {
                    pendingMoveDecision['delete'](node);
                    change.moved.set(node, didMove);
                }
                else {
                    didMove = change.moved.get(node);
                }
                return didMove;
            }
            var oldPreviousCache = new NodeMap();
            function getOldPrevious(node) {
                var oldPrevious = oldPreviousCache.get(node);
                if (oldPrevious !== undefined)
                    return oldPrevious;
                oldPrevious = change.oldPrevious.get(node);
                while (oldPrevious &&
                (change.removed.has(oldPrevious) || isMoved(oldPrevious))) {
                    oldPrevious = getOldPrevious(oldPrevious);
                }
                if (oldPrevious === undefined)
                    oldPrevious = node.previousSibling;
                oldPreviousCache.set(node, oldPrevious);
                return oldPrevious;
            }
            var previousCache = new NodeMap();
            function getPrevious(node) {
                if (previousCache.has(node))
                    return previousCache.get(node);
                var previous = node.previousSibling;
                while (previous && (change.added.has(previous) || isMoved(previous)))
                    previous = previous.previousSibling;
                previousCache.set(node, previous);
                return previous;
            }
            change.maybeMoved.keys().forEach(isMoved);
            return change.moved.get(node);
        };
        return MutationProjection;
    })();
    var Summary = (function () {
        function Summary(projection, query) {
            var _this = this;
            this.projection = projection;
            this.added = [];
            this.removed = [];
            this.reparented = query.all || query.element || query.characterData ? [] : undefined;
            this.reordered = query.all ? [] : undefined;
            projection.getChanged(this, query.elementFilter, query.characterData);
            if (query.all || query.attribute || query.attributeList) {
                var filter = query.attribute ? [query.attribute] : query.attributeList;
                var attributeChanged = projection.attributeChangedNodes(filter);
                if (query.attribute) {
                    this.valueChanged = attributeChanged[query.attribute] || [];
                }
                else {
                    this.attributeChanged = attributeChanged;
                    if (query.attributeList) {
                        query.attributeList.forEach(function (attrName) {
                            if (!_this.attributeChanged.hasOwnProperty(attrName))
                                _this.attributeChanged[attrName] = [];
                        });
                    }
                }
            }
            if (query.all || query.characterData) {
                var characterDataChanged = projection.getCharacterDataChanged();
                if (query.characterData)
                    this.valueChanged = characterDataChanged;
                else
                    this.characterDataChanged = characterDataChanged;
            }
            if (this.reordered)
                this.getOldPreviousSibling = projection.getOldPreviousSibling.bind(projection);
        }
        Summary.prototype.getOldParentNode = function (node) {
            return this.projection.getOldParentNode(node);
        };
        Summary.prototype.getOldAttribute = function (node, name) {
            return this.projection.getOldAttribute(node, name);
        };
        Summary.prototype.getOldCharacterData = function (node) {
            return this.projection.getOldCharacterData(node);
        };
        Summary.prototype.getOldPreviousSibling = function (node) {
            return this.projection.getOldPreviousSibling(node);
        };
        return Summary;
    })();
// TODO(rafaelw): Allow ':' and '.' as valid name characters.
    var validNameInitialChar = /[a-zA-Z_]+/;
    var validNameNonInitialChar = /[a-zA-Z0-9_\-]+/;
// TODO(rafaelw): Consider allowing backslash in the attrValue.
// TODO(rafaelw): There's got a to be way to represent this state machine
// more compactly???
    function escapeQuotes(value) {
        return '"' + value.replace(/"/, '\\\"') + '"';
    }
    var Qualifier = (function () {
        function Qualifier() {
        }
        Qualifier.prototype.matches = function (oldValue) {
            if (oldValue === null)
                return false;
            if (this.attrValue === undefined)
                return true;
            if (!this.contains)
                return this.attrValue == oldValue;
            var tokens = oldValue.split(' ');
            for (var i = 0; i < tokens.length; i++) {
                if (this.attrValue === tokens[i])
                    return true;
            }
            return false;
        };
        Qualifier.prototype.toString = function () {
            if (this.attrName === 'class' && this.contains)
                return '.' + this.attrValue;
            if (this.attrName === 'id' && !this.contains)
                return '#' + this.attrValue;
            if (this.contains)
                return '[' + this.attrName + '~=' + escapeQuotes(this.attrValue) + ']';
            if ('attrValue' in this)
                return '[' + this.attrName + '=' + escapeQuotes(this.attrValue) + ']';
            return '[' + this.attrName + ']';
        };
        return Qualifier;
    })();
    var Selector = (function () {
        function Selector() {
            this.uid = Selector.nextUid++;
            this.qualifiers = [];
        }
        Object.defineProperty(Selector.prototype, "caseInsensitiveTagName", {
            get: function () {
                return this.tagName.toUpperCase();
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(Selector.prototype, "selectorString", {
            get: function () {
                return this.tagName + this.qualifiers.join('');
            },
            enumerable: true,
            configurable: true
        });
        Selector.prototype.isMatching = function (el) {
            return el[Selector.matchesSelector](this.selectorString);
        };
        Selector.prototype.wasMatching = function (el, change, isMatching) {
            if (!change || !change.attributes)
                return isMatching;
            var tagName = change.isCaseInsensitive ? this.caseInsensitiveTagName : this.tagName;
            if (tagName !== '*' && tagName !== el.tagName)
                return false;
            var attributeOldValues = [];
            var anyChanged = false;
            for (var i = 0; i < this.qualifiers.length; i++) {
                var qualifier = this.qualifiers[i];
                var oldValue = change.getAttributeOldValue(qualifier.attrName);
                attributeOldValues.push(oldValue);
                anyChanged = anyChanged || (oldValue !== undefined);
            }
            if (!anyChanged)
                return isMatching;
            for (var i = 0; i < this.qualifiers.length; i++) {
                var qualifier = this.qualifiers[i];
                var oldValue = attributeOldValues[i];
                if (oldValue === undefined)
                    oldValue = el.getAttribute(qualifier.attrName);
                if (!qualifier.matches(oldValue))
                    return false;
            }
            return true;
        };
        Selector.prototype.matchabilityChange = function (el, change) {
            var isMatching = this.isMatching(el);
            if (isMatching)
                return this.wasMatching(el, change, isMatching) ? Movement.STAYED_IN : Movement.ENTERED;
            else
                return this.wasMatching(el, change, isMatching) ? Movement.EXITED : Movement.STAYED_OUT;
        };
        Selector.parseSelectors = function (input) {
            var selectors = [];
            var currentSelector;
            var currentQualifier;
            function newSelector() {
                if (currentSelector) {
                    if (currentQualifier) {
                        currentSelector.qualifiers.push(currentQualifier);
                        currentQualifier = undefined;
                    }
                    selectors.push(currentSelector);
                }
                currentSelector = new Selector();
            }
            function newQualifier() {
                if (currentQualifier)
                    currentSelector.qualifiers.push(currentQualifier);
                currentQualifier = new Qualifier();
            }
            var WHITESPACE = /\s/;
            var valueQuoteChar;
            var SYNTAX_ERROR = 'Invalid or unsupported selector syntax.';
            var SELECTOR = 1;
            var TAG_NAME = 2;
            var QUALIFIER = 3;
            var QUALIFIER_NAME_FIRST_CHAR = 4;
            var QUALIFIER_NAME = 5;
            var ATTR_NAME_FIRST_CHAR = 6;
            var ATTR_NAME = 7;
            var EQUIV_OR_ATTR_QUAL_END = 8;
            var EQUAL = 9;
            var ATTR_QUAL_END = 10;
            var VALUE_FIRST_CHAR = 11;
            var VALUE = 12;
            var QUOTED_VALUE = 13;
            var SELECTOR_SEPARATOR = 14;
            var state = SELECTOR;
            var i = 0;
            while (i < input.length) {
                var c = input[i++];
                switch (state) {
                    case SELECTOR:
                        if (c.match(validNameInitialChar)) {
                            newSelector();
                            currentSelector.tagName = c;
                            state = TAG_NAME;
                            break;
                        }
                        if (c == '*') {
                            newSelector();
                            currentSelector.tagName = '*';
                            state = QUALIFIER;
                            break;
                        }
                        if (c == '.') {
                            newSelector();
                            newQualifier();
                            currentSelector.tagName = '*';
                            currentQualifier.attrName = 'class';
                            currentQualifier.contains = true;
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '#') {
                            newSelector();
                            newQualifier();
                            currentSelector.tagName = '*';
                            currentQualifier.attrName = 'id';
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '[') {
                            newSelector();
                            newQualifier();
                            currentSelector.tagName = '*';
                            currentQualifier.attrName = '';
                            state = ATTR_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c.match(WHITESPACE))
                            break;
                        throw Error(SYNTAX_ERROR);
                    case TAG_NAME:
                        if (c.match(validNameNonInitialChar)) {
                            currentSelector.tagName += c;
                            break;
                        }
                        if (c == '.') {
                            newQualifier();
                            currentQualifier.attrName = 'class';
                            currentQualifier.contains = true;
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '#') {
                            newQualifier();
                            currentQualifier.attrName = 'id';
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '[') {
                            newQualifier();
                            currentQualifier.attrName = '';
                            state = ATTR_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c.match(WHITESPACE)) {
                            state = SELECTOR_SEPARATOR;
                            break;
                        }
                        if (c == ',') {
                            state = SELECTOR;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case QUALIFIER:
                        if (c == '.') {
                            newQualifier();
                            currentQualifier.attrName = 'class';
                            currentQualifier.contains = true;
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '#') {
                            newQualifier();
                            currentQualifier.attrName = 'id';
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '[') {
                            newQualifier();
                            currentQualifier.attrName = '';
                            state = ATTR_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c.match(WHITESPACE)) {
                            state = SELECTOR_SEPARATOR;
                            break;
                        }
                        if (c == ',') {
                            state = SELECTOR;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case QUALIFIER_NAME_FIRST_CHAR:
                        if (c.match(validNameInitialChar)) {
                            currentQualifier.attrValue = c;
                            state = QUALIFIER_NAME;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case QUALIFIER_NAME:
                        if (c.match(validNameNonInitialChar)) {
                            currentQualifier.attrValue += c;
                            break;
                        }
                        if (c == '.') {
                            newQualifier();
                            currentQualifier.attrName = 'class';
                            currentQualifier.contains = true;
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '#') {
                            newQualifier();
                            currentQualifier.attrName = 'id';
                            state = QUALIFIER_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c == '[') {
                            newQualifier();
                            state = ATTR_NAME_FIRST_CHAR;
                            break;
                        }
                        if (c.match(WHITESPACE)) {
                            state = SELECTOR_SEPARATOR;
                            break;
                        }
                        if (c == ',') {
                            state = SELECTOR;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case ATTR_NAME_FIRST_CHAR:
                        if (c.match(validNameInitialChar)) {
                            currentQualifier.attrName = c;
                            state = ATTR_NAME;
                            break;
                        }
                        if (c.match(WHITESPACE))
                            break;
                        throw Error(SYNTAX_ERROR);
                    case ATTR_NAME:
                        if (c.match(validNameNonInitialChar)) {
                            currentQualifier.attrName += c;
                            break;
                        }
                        if (c.match(WHITESPACE)) {
                            state = EQUIV_OR_ATTR_QUAL_END;
                            break;
                        }
                        if (c == '~') {
                            currentQualifier.contains = true;
                            state = EQUAL;
                            break;
                        }
                        if (c == '=') {
                            currentQualifier.attrValue = '';
                            state = VALUE_FIRST_CHAR;
                            break;
                        }
                        if (c == ']') {
                            state = QUALIFIER;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case EQUIV_OR_ATTR_QUAL_END:
                        if (c == '~') {
                            currentQualifier.contains = true;
                            state = EQUAL;
                            break;
                        }
                        if (c == '=') {
                            currentQualifier.attrValue = '';
                            state = VALUE_FIRST_CHAR;
                            break;
                        }
                        if (c == ']') {
                            state = QUALIFIER;
                            break;
                        }
                        if (c.match(WHITESPACE))
                            break;
                        throw Error(SYNTAX_ERROR);
                    case EQUAL:
                        if (c == '=') {
                            currentQualifier.attrValue = '';
                            state = VALUE_FIRST_CHAR;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                    case ATTR_QUAL_END:
                        if (c == ']') {
                            state = QUALIFIER;
                            break;
                        }
                        if (c.match(WHITESPACE))
                            break;
                        throw Error(SYNTAX_ERROR);
                    case VALUE_FIRST_CHAR:
                        if (c.match(WHITESPACE))
                            break;
                        if (c == '"' || c == "'") {
                            valueQuoteChar = c;
                            state = QUOTED_VALUE;
                            break;
                        }
                        currentQualifier.attrValue += c;
                        state = VALUE;
                        break;
                    case VALUE:
                        if (c.match(WHITESPACE)) {
                            state = ATTR_QUAL_END;
                            break;
                        }
                        if (c == ']') {
                            state = QUALIFIER;
                            break;
                        }
                        if (c == "'" || c == '"')
                            throw Error(SYNTAX_ERROR);
                        currentQualifier.attrValue += c;
                        break;
                    case QUOTED_VALUE:
                        if (c == valueQuoteChar) {
                            state = ATTR_QUAL_END;
                            break;
                        }
                        currentQualifier.attrValue += c;
                        break;
                    case SELECTOR_SEPARATOR:
                        if (c.match(WHITESPACE))
                            break;
                        if (c == ',') {
                            state = SELECTOR;
                            break;
                        }
                        throw Error(SYNTAX_ERROR);
                }
            }
            switch (state) {
                case SELECTOR:
                case TAG_NAME:
                case QUALIFIER:
                case QUALIFIER_NAME:
                case SELECTOR_SEPARATOR:
                    // Valid end states.
                    newSelector();
                    break;
                default:
                    throw Error(SYNTAX_ERROR);
            }
            if (!selectors.length)
                throw Error(SYNTAX_ERROR);
            return selectors;
        };
        Selector.nextUid = 1;
        Selector.matchesSelector = (function () {
            var element = document.createElement('div');
            if (typeof element['webkitMatchesSelector'] === 'function')
                return 'webkitMatchesSelector';
            if (typeof element['mozMatchesSelector'] === 'function')
                return 'mozMatchesSelector';
            if (typeof element['msMatchesSelector'] === 'function')
                return 'msMatchesSelector';
            return 'matchesSelector';
        })();
        return Selector;
    })();
    var attributeFilterPattern = /^([a-zA-Z:_]+[a-zA-Z0-9_\-:\.]*)$/;
    function validateAttribute(attribute) {
        if (typeof attribute != 'string')
            throw Error('Invalid request opion. attribute must be a non-zero length string.');
        attribute = attribute.trim();
        if (!attribute)
            throw Error('Invalid request opion. attribute must be a non-zero length string.');
        if (!attribute.match(attributeFilterPattern))
            throw Error('Invalid request option. invalid attribute name: ' + attribute);
        return attribute;
    }
    function validateElementAttributes(attribs) {
        if (!attribs.trim().length)
            throw Error('Invalid request option: elementAttributes must contain at least one attribute.');
        var lowerAttributes = {};
        var attributes = {};
        var tokens = attribs.split(/\s+/);
        for (var i = 0; i < tokens.length; i++) {
            var name = tokens[i];
            if (!name)
                continue;
            var name = validateAttribute(name);
            var nameLower = name.toLowerCase();
            if (lowerAttributes[nameLower])
                throw Error('Invalid request option: observing multiple case variations of the same attribute is not supported.');
            attributes[name] = true;
            lowerAttributes[nameLower] = true;
        }
        return Object.keys(attributes);
    }
    function elementFilterAttributes(selectors) {
        var attributes = {};
        selectors.forEach(function (selector) {
            selector.qualifiers.forEach(function (qualifier) {
                attributes[qualifier.attrName] = true;
            });
        });
        return Object.keys(attributes);
    }
    var MutationSummary = (function () {
        function MutationSummary(opts) {
            var _this = this;
            this.connected = false;
            this.options = MutationSummary.validateOptions(opts);
            this.observerOptions = MutationSummary.createObserverOptions(this.options.queries);
            this.root = this.options.rootNode;
            this.callback = this.options.callback;
            this.elementFilter = Array.prototype.concat.apply([], this.options.queries.map(function (query) {
                return query.elementFilter ? query.elementFilter : [];
            }));
            if (!this.elementFilter.length)
                this.elementFilter = undefined;
            this.calcReordered = this.options.queries.some(function (query) {
                return query.all;
            });
            this.queryValidators = []; // TODO(rafaelw): Shouldn't always define this.
            if (MutationSummary.createQueryValidator) {
                this.queryValidators = this.options.queries.map(function (query) {
                    return MutationSummary.createQueryValidator(_this.root, query);
                });
            }
            this.observer = new MutationObserverCtor(function (mutations) {
                _this.observerCallback(mutations);
            });
            this.reconnect();
        }
        MutationSummary.createObserverOptions = function (queries) {
            var observerOptions = {
                childList: true,
                subtree: true
            };
            var attributeFilter;
            function observeAttributes(attributes) {
                if (observerOptions.attributes && !attributeFilter)
                    return; // already observing all.
                observerOptions.attributes = true;
                observerOptions.attributeOldValue = true;
                if (!attributes) {
                    // observe all.
                    attributeFilter = undefined;
                    return;
                }
                // add to observed.
                attributeFilter = attributeFilter || {};
                attributes.forEach(function (attribute) {
                    attributeFilter[attribute] = true;
                    attributeFilter[attribute.toLowerCase()] = true;
                });
            }
            queries.forEach(function (query) {
                if (query.characterData) {
                    observerOptions.characterData = true;
                    observerOptions.characterDataOldValue = true;
                    return;
                }
                if (query.all) {
                    observeAttributes();
                    observerOptions.characterData = true;
                    observerOptions.characterDataOldValue = true;
                    return;
                }
                if (query.attribute) {
                    observeAttributes([query.attribute.trim()]);
                    return;
                }
                var attributes = elementFilterAttributes(query.elementFilter).concat(query.attributeList || []);
                if (attributes.length)
                    observeAttributes(attributes);
            });
            if (attributeFilter)
                observerOptions.attributeFilter = Object.keys(attributeFilter);
            return observerOptions;
        };
        MutationSummary.validateOptions = function (options) {
            for (var prop in options) {
                if (!(prop in MutationSummary.optionKeys))
                    throw Error('Invalid option: ' + prop);
            }
            if (typeof options.callback !== 'function')
                throw Error('Invalid options: callback is required and must be a function');
            if (!options.queries || !options.queries.length)
                throw Error('Invalid options: queries must contain at least one query request object.');
            var opts = {
                callback: options.callback,
                rootNode: options.rootNode || document,
                observeOwnChanges: !!options.observeOwnChanges,
                oldPreviousSibling: !!options.oldPreviousSibling,
                queries: []
            };
            for (var i = 0; i < options.queries.length; i++) {
                var request = options.queries[i];
                // all
                if (request.all) {
                    if (Object.keys(request).length > 1)
                        throw Error('Invalid request option. all has no options.');
                    opts.queries.push({ all: true });
                    continue;
                }
                // attribute
                if ('attribute' in request) {
                    var query = {
                        attribute: validateAttribute(request.attribute)
                    };
                    query.elementFilter = Selector.parseSelectors('*[' + query.attribute + ']');
                    if (Object.keys(request).length > 1)
                        throw Error('Invalid request option. attribute has no options.');
                    opts.queries.push(query);
                    continue;
                }
                // element
                if ('element' in request) {
                    var requestOptionCount = Object.keys(request).length;
                    var query = {
                        element: request.element,
                        elementFilter: Selector.parseSelectors(request.element)
                    };
                    if (request.hasOwnProperty('elementAttributes')) {
                        query.attributeList = validateElementAttributes(request.elementAttributes);
                        requestOptionCount--;
                    }
                    if (requestOptionCount > 1)
                        throw Error('Invalid request option. element only allows elementAttributes option.');
                    opts.queries.push(query);
                    continue;
                }
                // characterData
                if (request.characterData) {
                    if (Object.keys(request).length > 1)
                        throw Error('Invalid request option. characterData has no options.');
                    opts.queries.push({ characterData: true });
                    continue;
                }
                throw Error('Invalid request option. Unknown query request.');
            }
            return opts;
        };
        MutationSummary.prototype.createSummaries = function (mutations) {
            if (!mutations || !mutations.length)
                return [];
            var projection = new MutationProjection(this.root, mutations, this.elementFilter, this.calcReordered, this.options.oldPreviousSibling);
            var summaries = [];
            for (var i = 0; i < this.options.queries.length; i++) {
                summaries.push(new Summary(projection, this.options.queries[i]));
            }
            return summaries;
        };
        MutationSummary.prototype.checkpointQueryValidators = function () {
            this.queryValidators.forEach(function (validator) {
                if (validator)
                    validator.recordPreviousState();
            });
        };
        MutationSummary.prototype.runQueryValidators = function (summaries) {
            this.queryValidators.forEach(function (validator, index) {
                if (validator)
                    validator.validate(summaries[index]);
            });
        };
        MutationSummary.prototype.changesToReport = function (summaries) {
            return summaries.some(function (summary) {
                var summaryProps = ['added', 'removed', 'reordered', 'reparented',
                    'valueChanged', 'characterDataChanged'];
                if (summaryProps.some(function (prop) { return summary[prop] && summary[prop].length; }))
                    return true;
                if (summary.attributeChanged) {
                    var attrNames = Object.keys(summary.attributeChanged);
                    var attrsChanged = attrNames.some(function (attrName) {
                        return !!summary.attributeChanged[attrName].length;
                    });
                    if (attrsChanged)
                        return true;
                }
                return false;
            });
        };
        MutationSummary.prototype.observerCallback = function (mutations) {
            if (!this.options.observeOwnChanges)
                this.observer.disconnect();
            var summaries = this.createSummaries(mutations);
            this.runQueryValidators(summaries);
            if (this.options.observeOwnChanges)
                this.checkpointQueryValidators();
            if (this.changesToReport(summaries))
                this.callback(summaries);
            // disconnect() may have been called during the callback.
            if (!this.options.observeOwnChanges && this.connected) {
                this.checkpointQueryValidators();
                this.observer.observe(this.root, this.observerOptions);
            }
        };
        MutationSummary.prototype.reconnect = function () {
            if (this.connected)
                throw Error('Already connected');
            this.observer.observe(this.root, this.observerOptions);
            this.connected = true;
            this.checkpointQueryValidators();
        };
        MutationSummary.prototype.takeSummaries = function () {
            if (!this.connected)
                throw Error('Not connected');
            var summaries = this.createSummaries(this.observer.takeRecords());
            return this.changesToReport(summaries) ? summaries : undefined;
        };
        MutationSummary.prototype.disconnect = function () {
            var summaries = this.takeSummaries();
            this.observer.disconnect();
            this.connected = false;
            return summaries;
        };
        MutationSummary.NodeMap = NodeMap; // exposed for use in TreeMirror.
        MutationSummary.parseElementFilter = Selector.parseSelectors; // exposed for testing.
        MutationSummary.optionKeys = {
            'callback': true,
            'queries': true,
            'rootNode': true,
            'oldPreviousSibling': true,
            'observeOwnChanges': true
        };
        return MutationSummary;
    })();

    /**
     * TREEMIRROR
     * SEE https://github.com/rafaelw/mutation-summary/blob/master/util/tree-mirror.js
     * Custom modifications from InnoCraft especially in serializeNode()
     */
    var TreeMirrorClient = (function () {
        function TreeMirrorClient(target, mirror, testingQueries) {
            var _this = this;
            this.target = target;
            this.mirror = mirror;
            this.nextId = 1;
            this.knownNodes = new MutationSummary.NodeMap();
            var rootId = this.serializeNode(target).id;
            var children = [];
            for (var child = target.firstChild; child; child = child.nextSibling)
                children.push(this.serializeNode(child, true));
            this.mirror.initialize(rootId, children);
            var self = this;
            var queries = [{ all: true }];
            if (testingQueries)
                queries = queries.concat(testingQueries);
            this.mutationSummary = new MutationSummary({
                rootNode: target,
                callback: function (summaries) {
                    _this.applyChanged(summaries);
                },
                queries: queries
            });
        }
        TreeMirrorClient.prototype.disconnect = function () {
            if (this.mutationSummary) {
                this.mutationSummary.disconnect();
                this.mutationSummary = undefined;
            }
        };
        TreeMirrorClient.prototype.rememberNode = function (node) {
            var id = this.nextId++;
            this.knownNodes.set(node, id);
            return id;
        };
        TreeMirrorClient.prototype.forgetNode = function (node) {
            this.knownNodes['delete'](node);
        };
        TreeMirrorClient.prototype.serializeNode = function (node, recursive, isIgnoredField, isIgnoredContent) {
            if (node === null)
                return null;

            var id = this.knownNodes.get(node);
            if (id !== undefined) {
                return { id: id };
            }
            var parent = (id && id.parentNode) ? id.parentNode : null;
            var data = {
                nodeType: node.nodeType,
                id: this.rememberNode(node)
            };

            if (!isIgnoredField && element.shouldMaskField(node, false)) {
                isIgnoredField = true;
            }
            if (!isIgnoredContent && element.shouldMaskContent(node, false)) {
                isIgnoredContent = true;
            }

            while (parent && !isIgnoredField && !isIgnoredContent) {
                if (!isIgnoredField && element.shouldMaskField(parent, false)) {
                    isIgnoredField = true;
                }
                if (!isIgnoredContent && element.shouldMaskContent(parent, false)) {
                    isIgnoredContent = true;
                }

                parent = parent.parentNode ? parent.parentNode : null;
            }

            switch (data.nodeType) {
                case Node_DOCUMENT_TYPE_NODE:
                    var docType = node;
                    data.name = docType.name;
                    data.publicId = docType.publicId;
                    data.systemId = docType.systemId;
                    break;
                case Node_COMMENT_NODE:
                    // to save data etc we do not track comment nodes. they are not needed to render page
                    data.textContent = ' ';
                    break;
                case Node_TEXT_NODE:

                    if ('undefined' !== typeof node.parentNode && node.parentNode && node.parentNode.tagName === 'TEXTAREA' && (!recordKeystrokes || isIgnoredField || element.shouldMaskField(node, false))) {
                        data.textContent = element.maskFormField(trackerUtils.trim(node.textContent));
                    } else if (isIgnoredContent || element.shouldMaskContent(node, false)) {
                        data.textContent = element.maskFormField(trackerUtils.trim(node.textContent));
                    } else {
                        data.textContent = node.textContent;
                    }

                    break;
                case Node_ELEMENT_NODE:

                    data.tagName = node.tagName;
                    data.attributes = {};

                    if ('SCRIPT' === data.tagName || 'NOSCRIPT' === data.tagName) {
                        // we ignore any details about script elements. We would replace them anyway when rendering the page
                        // and they often contain random parameters which would prevent re-using existing tracked DOM
                        // mutations in blob tables
                        break;
                    }

                    if ('STYLE' === data.tagName
                        && (('string' === typeof node.innerText && node.innerText.trim() === '') ||
                            ('string' === typeof node.innerHTML && node.innerHTML.trim() === ''))
                        && documentAlias.styleSheets
                        && documentAlias.styleSheets.length) {
                        var styleContent;
                        for (var k = 0; k < documentAlias.styleSheets.length; k++) {
                            if (documentAlias.styleSheets[k]) {
                                var sheet = documentAlias.styleSheets[k];
                                if (sheet && sheet.ownerNode && !sheet.href && sheet.ownerNode === node && sheet.cssRules && sheet.cssRules.length) {
                                    var content = '';
                                    for (var i = 0; i < sheet.cssRules.length; i++) {
                                        if (sheet.cssRules[i].cssText) {
                                            content += sheet.cssRules[i].cssText + " ";
                                        }
                                    }
                                    styleContent = documentAlias.createTextNode(content);
                                    data.childNodes = [
                                        this.serializeNode(styleContent, false, isIgnoredField, isIgnoredContent)
                                    ];
                                    break;
                                }
                            }

                        }
                        if (styleContent) {
                            break;
                        }
                    }

                    var attrValue;
                    for (var i = 0; i < node.attributes.length; i++) {
                        var attr = node.attributes[i];
                        if (attr && 'value' in attr) {
                            attrValue = attr.value;
                        } else {
                            attrValue = '';
                        }

                        var randomLength = false;

                        if (attr.name === 'value'
                            && data.tagName === 'INPUT'
                            && node.value
                            && (!node.type || String(node.type).toLowerCase() === 'text' || String(node.type).toLowerCase() === 'number')) {
                            attrValue = node.value;
                        }

                        if (attr.name === 'src'
                            && data.tagName === 'IMG'
                            && element.shouldMaskContent(node, false)) {
                            attrValue = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==';
                        }

                        if (attr.name === 'value'
                            && data.tagName === 'INPUT'
                            && node.value
                            && String(node.type).toLowerCase() === 'password') {
                            randomLength = true;
                        }

                        if ('INPUT' === data.tagName && node.type && node.type === 'hidden' && 'value' === attr.name) {
                            data.attributes[attr.name] = ''; // we make sure to not track any hidden tokens etc
                        } else if ('INPUT' === data.tagName && 'value' === attr.name && (!recordKeystrokes || isIgnoredField || element.shouldMaskField(node, false))) {
                            data.attributes[attr.name] = element.maskFormField(attrValue, randomLength);
                        } else if (('title' === attr.name || 'alt' === attr.name || 'label' === attr.name || 'placeholder' === attr.name) && (isIgnoredContent || element.shouldMaskContent(node, false))) {
                            data.attributes[attr.name] = element.maskFormField(attrValue);
                        } else {
                            data.attributes[attr.name] = attrValue;
                        }
                    }

                    if ('IFRAME' === data.tagName && (node.scrollWidth <= 1 || node.scrollHeight <= 1)) {
                        // they would not be displayed, we want to prevent recording such iframes as they may contain
                        // random parameters or other content that should not be recorded
                        data.attributes.src = 'about:blank';
                    } else if ('META' === data.tagName) {
                        if (data.attributes.property && String(data.attributes.property).indexOf('og:') >= 0) {
                            data.attributes = {};
                        } else if (data.attributes.name) {
                            var metaName = String(data.attributes.name).toLowerCase();
                            if (metaName.indexOf('twitter:') >= 0 || metaName.indexOf('description') >= 0 || metaName.indexOf('keywords') >= 0) {
                                data.attributes = {};
                            }
                        }

                    } else if ('LINK' === data.tagName) {
                        if (data.attributes.rel) {
                            var link = String(data.attributes.rel).toLowerCase();
                            var blockedLinks = ['icon', 'preload', 'preconnect', 'dns-prefetch', 'next', 'prev', 'alternate', 'search']
                            if (blockedLinks.indexOf(link) >= 0) {
                                data.attributes = {};
                            }
                        }
                        if (data.attributes.href) {
                            // this is a local url that cannot be resolved when viewing the recording
                            var linkPos = String(data.attributes.href).toLowerCase().indexOf('.scr.kaspersky-labs.com');
                            if (linkPos > 5 && linkPos <= 20) {
                                data.attributes = {};
                            }
                        }
                    }

                    if (recursive && node.childNodes.length) {
                        data.childNodes = [];
                        for (var child = node.firstChild; child; child = child.nextSibling) {
                            data.childNodes.push(this.serializeNode(child, true, isIgnoredField, isIgnoredContent));
                        }
                    }
                    break;
            }
            return data;
        };
        TreeMirrorClient.prototype.serializeAddedAndMoved = function (added, reparented, reordered) {
            var _this = this;
            var all = added.concat(reparented).concat(reordered);
            var parentMap = new MutationSummary.NodeMap();
            all.forEach(function (node) {
                var parent = node.parentNode;
                var children = parentMap.get(parent);
                if (!children) {
                    children = new MutationSummary.NodeMap();
                    parentMap.set(parent, children);
                }
                children.set(node, true);
            });
            var moved = [];
            parentMap.keys().forEach(function (parent) {
                var children = parentMap.get(parent);
                var keys = children.keys();
                while (keys.length) {
                    var node = keys[0];
                    while (node.previousSibling && children.has(node.previousSibling))
                        node = node.previousSibling;
                    while (node && children.has(node)) {
                        var data = _this.serializeNode(node);
                        data.previousSibling = _this.serializeNode(node.previousSibling);
                        data.parentNode = _this.serializeNode(node.parentNode);
                        moved.push(data);
                        children['delete'](node);
                        node = node.nextSibling;
                    }
                    var keys = children.keys();
                }
            });
            return moved;
        };
        TreeMirrorClient.prototype.serializeAttributeChanges = function (attributeChanged) {
            var _this = this;
            var map = new MutationSummary.NodeMap();
            Object.keys(attributeChanged).forEach(function (attrName) {
                attributeChanged[attrName].forEach(function (element) {
                    var record = map.get(element);
                    if (!record) {
                        record = _this.serializeNode(element);
                        record.attributes = {};
                        map.set(element, record);
                    }
                    record.attributes[attrName] = element.getAttribute(attrName);
                });
            });
            return map.keys().map(function (node) {
                return map.get(node);
            });
        };
        TreeMirrorClient.prototype.applyChanged = function (summaries) {
            var _this = this;
            var summary = summaries[0];
            var removed = summary.removed.map(function (node) {
                return _this.serializeNode(node);
            });
            var moved = this.serializeAddedAndMoved(summary.added, summary.reparented, summary.reordered);
            var attributes = this.serializeAttributeChanges(summary.attributeChanged);
            var text = summary.characterDataChanged.map(function (node) {
                var data = _this.serializeNode(node);
                data.textContent = node.textContent;
                return data;
            });
            this.mirror.applyChanged(removed, moved, attributes, text);
            summary.removed.forEach(function (node) {
                _this.forgetNode(node);
            });
        };
        return TreeMirrorClient;
    })();
}
    /*!
     * Copyright (C) InnoCraft Ltd - All rights reserved.
     *
     * All information contained herein is, and remains the property of InnoCraft Ltd.
     *
     * @link https://www.innocraft.com/
     * @license For license details see https://www.innocraft.com/license
     */

    var documentAlias = document;
    var windowAlias = window;

    // we initialize it with zero by default but overwrite it as soon as page is ready. It is not really being used before that
    var timeWhenPageReady = 0;
    var debugMode = false;
    var isHsrEnabled = !isExcluded();
    var enableRecordMovements = true;
    var customPiwikTrackers = null;
    var isDOMloaded = false;
    var hsrIdView = '';
    var matchTrackerUrl = false;

    var maxCapturingTimeStart = 15 * 60 * 1000; // 15 minutes
    var maxCapturingTimeEnd = 30 * 60 * 1000; // 30 minutes we stop recording after 30 minutes in any case. This is to prevent cases were eg DOM is manipulated constantly on a page and session recording would keep sending requests.

    var increaseMaxCaptureTimeWhenLessThanXRequests = 10; // when less than 10 requests so far, we record for another 5min
    var maxCaptureTimeIncreaseWhenLessThanXRequests = (5 * 60 * 1000); // we give it another 5 minutes when there are less than 10 requests
    var pixelOffsetAccuracy = 2000; // HAS TO MATCH SERVER SIDE VALUE AS WE CALCULATE PERCENTAGE OFFSET BASED ON THIS
    var maxScrollAccuracy = 1000; // HAS TO MATCH SERVER SIDE VALUE AS WE CALCULATE SCROLL PERCENTAGE BASED ON THIS
    var maxSampleRate = 100; // HAS TO MATCH SERVER SIDE VALUE MAX SAMPLE RATE
    var maxLenTextInput = 500; // we limit for now to max 500 characters

    // when a page is loaded, we cannot detect the mouse position until a user actually moves the mouse. This is a problem
    // because in the replay the first mouse move is basically not shown. therefore, the first time we receive a mouse move
    // event we will use this as initial mouse position. So instead of waiting for 100 or 200ms for the first mouse move
    // we capture it directly without any delay. This way we can draw the first mouse move.
    var isFirstMouseMoveEvent = false;

    function isExcluded()
    {
        // we check whether it is a supported browser and not a bot, in most methods we can use more "modern" features that wouldn't work eg on IE6 or IE9
        // some inital methods still need to work on all browsers though!
        if ('object' !== typeof JSON) {
            // not supported browser
            return true;
        }

        if ('function' !== typeof Array.prototype.map || 'function' !== typeof Array.prototype.filter || 'function' !== typeof Array.prototype.indexOf) {
            // needed by CSS selector and mutation summary
            return true;
        }

        if ('function' !== typeof Element.prototype.getBoundingClientRect) {
            // needed by our tracker
            return true;
        }

        var blockedSites = ['cc.bingj.com'];
        if (blockedSites.indexOf(documentAlias.domain) !== -1
            || String(documentAlias.domain).indexOf('.googleusercontent.com') !== -1) {
            return true;
        }

        var bot = /alexa|baidu|bing|bot|crawler|curl|crawling|duckduckgo|facebookexternalhit|feedburner|googlebot|google web preview|linkdex|nagios|postrank|pingdom|robot|slurp|spider|yahoo!|yandex|wget/i.test(navigator.userAgent);
        if (bot) {
            return true;
        }

        var topUrl = String(documentAlias.referrer);
        if (topUrl && topUrl.indexOf('module=Overlay&action=startOverlaySession') >= 0) {
            // do not record when shown in Piwik overlay
            return true;
        }

        return false;
    }

    function logConsoleMessage() {
        if (debugMode && 'object' === typeof console) {
            if (typeof console.debug === 'function') {
                console.debug.apply(console, arguments);
            } else if (typeof console.log === 'function') {
                console.log.apply(console, arguments);
            }
        }
    }

    var shouldTriggerRecording = function () {
        // allows user to customize whether a user should be included in tracking or not
        return true;
    };

    var EVENT_TYPE_MOVEMENT = 1;
    var EVENT_TYPE_CLICK = 2;
    var EVENT_TYPE_SCROLL = 3;
    var EVENT_TYPE_RESIZE = 4;
    var EVENT_TYPE_INITIAL_DOM = 5;
    var EVENT_TYPE_MUTATION = 6;
    var EVENT_TYPE_LINK_HSR = 7;
    var EVENT_TYPE_PAGE_TREEMIRROR = 8;
    var EVENT_TYPE_FORM_TEXT = 9;
    var EVENT_TYPE_FORM_VALUE = 10;
    var EVENT_TYPE_STOP_RECORDING = 11;
    var EVENT_TYPE_SCROLL_ELEMENT = 12;

    var RECORD_TYPE_BOTH = 0;
    var RECORD_TYPE_HEATMAP = 1;
    var RECORD_TYPE_SESSION = 2;

    var recordKeystrokes = true;
    var hasClicked = false;
    var hasScrolled = false;
    var autoDetectNewPageviews = true;
    var timeLastEvent = null;

    var pJson = {};
    if ('object' === typeof JSON) {
        // we need to initialize this by default because we need to use JSON before Matomo is loaded
        pJson = JSON;
    }

    var isPluginInitialized = false;
    var recordedDataBeforeTrackerSetup = [];

    var mutation = {
        hasObserver: function () {
            if (typeof WebKitMutationObserver !== 'undefined') {
                return true;
            } else if (typeof MutationObserver !== 'undefined') {
                return true;
            }
            // no need to check for MozMutationObserver as outdated

            return false;
        }
    };

    var canTrackSessionRecording = mutation.hasObserver();

    var dom = {
        getScrollLeft: function () {
            return windowAlias.document.body.scrollLeft || windowAlias.document.documentElement.scrollLeft;
        },
        getScrollTop: function () {
            return windowAlias.document.body.scrollTop || windowAlias.document.documentElement.scrollTop;
        },
        getDocumentHeight: function () {
            // we use at least one px to prevent divisions by zero etc
            return Math.max(documentAlias.body.offsetHeight, documentAlias.body.scrollHeight, documentAlias.documentElement.offsetHeight, documentAlias.documentElement.clientHeight, documentAlias.documentElement.scrollHeight, 1);
        },
        getDocumentWidth: function () {
            // we use at least one px to prevent divisions by zero etc
            return Math.max(documentAlias.body.offsetWidth, documentAlias.body.scrollWidth, documentAlias.documentElement.offsetWidth, documentAlias.documentElement.clientWidth, documentAlias.documentElement.scrollWidth, 1);
        },
        getWindowSize: function () {
            var height = windowAlias.innerHeight || documentAlias.documentElement.clientHeight || documentAlias.body.clientHeight;
            var width = windowAlias.innerWidth || documentAlias.documentElement.clientWidth || documentAlias.body.clientWidth;

            return {width: width, height: height};
        }
    };

    var storage = {
        namespace: 'hsr', // has to match server side!
        set: function (tracker, configId, value) {
            configId = parseInt(configId, 10);
            value = parseInt(value, 10);

            var cookieValue = '';
            var keys = storage.getHsrConfigs(tracker);
            var found = false;
            for (var i = 0; i < keys.length; i++) {
                if (keys[i] && keys[i].id === configId) {
                    found = true;
                    keys[i].value = value;
                }
                cookieValue += keys[i].id + '.' + keys[i].value + '_';
            }
            if (!found) {
                cookieValue += configId + '.' + value;
            }
            tracker.setSessionCookie(this.namespace, cookieValue);
        },
        get: function (tracker, configId) {
            configId = parseInt(configId, 10);

            var keys = storage.getHsrConfigs(tracker);
            for (var i = 0; i < keys.length; i++) {
                if (keys[i] && keys[i].id === configId) {
                    return keys[i].value;
                }
            }
            return null;
        },
        getHsrConfigs: function (tracker) {
            var value = tracker.getCookie(this.namespace);
            if (!value) {
                return [];
            }

            var keys = [];
            var parts = String(value).split('_'), innerParts;
            for (var i = 0; i < parts.length; i++) {
                innerParts = parts[i].split('.');
                if (innerParts && innerParts.length === 2) {
                    keys.push({id: parseInt(innerParts[0], 10), value: parseInt(innerParts[1], 10)});
                }
            }
            return keys;
        }
    };

    var element = {
        getAttribute: function (node, attributeName) {
            if (node && node.getAttribute && attributeName) {
                return node.getAttribute(attributeName);
            }

            return null;
        },
        hasAttribute: function (node, attributeName) {
            if (node && node.hasAttribute) {
                return node.hasAttribute(attributeName);
            }

            if (node && node.attributes) {
                var theType = (typeof node.attributes[attributeName]);
                return theType !== 'undefined';
            }

            return false;
        },
        getTagName: function (node) {
            if (node && node.tagName) {
                return ('' + node.tagName).toLowerCase();
            }

            return null;
        },
        getCssClasses: function (node)
        {
            if (node && node.className) {
                var classes = typeof node.className === "string" ? trackerUtils.trim(node.className).split(/\s+/) : [];
                return classes;
            }

            return [];
        },
        getHeight: function(node) {
            if (node && (node.nodeType === 9 || node.tagName === 'HTML')) {
                return dom.getDocumentHeight();
            }

            if (node === window) {
                return documentAlias.documentElement.clientHeight;
            }

            return Math.max(node.scrollHeight, node.offsetHeight, 0);
        },
        getWidth: function(node) {
            if (node && (node.nodeType === 9 || node.tagName === 'HTML')) {
                // In some browsers when the viewport is larger than the html element we need to use doc width / height.
                // Eg in firefox, when viewport is larger than HTML element, we may run into this issue
                return dom.getDocumentWidth();
            }

            if (node === window) {
                return documentAlias.documentElement.clientWidth;
            }

            return Math.max(node.scrollWidth, node.offsetWidth, 0);
        },
        getOffset: function (node) {
            if (!node.getBoundingClientRect) {
                return {top: 0, left: 0, width: 0, height: 0};
            }

            var theDoc = (node && node.ownerDocument).documentElement;
            var clientRect = node.getBoundingClientRect();

            // we use Math.floor so when calculating like event.pageX - x.top then we more likely don't get a negative value
            return {
                top: Math.floor(clientRect.top) + (windowAlias.pageYOffset || documentAlias.scrollTop || 0) - (theDoc.clientTop || 0),
                left: Math.floor(clientRect.left) + (windowAlias.pageXOffset || documentAlias.scrollLeft || 0) - (theDoc.clientLeft || 0),
                width: Math.max(clientRect.width, element.getWidth(node)),
                height: Math.max(clientRect.height, element.getHeight(node))
            };
        },
        getSelector: function (node, selector) {
            return UTILS.cssPath(node, false);
        },
        maskFormField: function (text, variableLength) {
            if (!text) {
                return text;
            }
            text = String(text).replace(/./g, '*');
            if (variableLength) {
                // useful to eg better anonymize password length
                var rand = Math.floor(Math.random() * 10) + 1;
                text = text + (new Array(rand + 1).join('*'));
            }
            return text;
        },
        shouldMaskField: function (node, shouldCheckParents) {
            if (!node) {
                return false;
            }

            var type = element.getAttribute(node, 'type');
            if (!type) {
                type = 'text';
            } else {
                type = String(type).toLowerCase();
            }
            var isSelectField = type === 'radio' || type === 'checkbox' || (node.nodeName && node.nodeName === 'SELECT');

            if (!recordKeystrokes) {
                if (isSelectField) {
                    return false; // still record select fields
                }
                return true;
            }

            var attrName = element.getAttribute(node, 'name');
            var attrId = element.getAttribute(node, 'id');
            var attrAutoCo = element.getAttribute(node, 'autocomplete');

            attrName = trackerUtils.trim(String(attrName)).toLowerCase().replace(/[\s_-]+/g, '');
            attrId = trackerUtils.trim(String(attrId)).toLowerCase().replace(/[\s_-]+/g, '');
            attrAutoCo = trackerUtils.trim(String(attrAutoCo)).toLowerCase().replace(/[\s_-]+/g, '');

            var blockedFields = ['creditcardnumber', 'off', 'kreditkarte', 'debitcard', 'kreditkort', 'kredietkaart', ' kartakredytowa', 'cvv', 'cc', 'ccc', 'cccsc', 'cccvc', 'ccexpiry', 'ccexpyear', 'ccexpmonth', 'cccvv', 'cctype', 'cvc', 'exp', 'ccname', 'cardnumber', 'ccnumber', 'username', 'creditcard', 'name', 'fullname', 'familyname', 'firstname', 'vorname', 'nachname', 'lastname', 'nickname', 'surname', 'login', 'formlogin', 'konto', 'user', 'website', 'domain', 'gender', 'company', 'firma', 'geschlecht', 'email', 'emailaddress', 'emailadresse', 'mail', 'epos', 'ebost', 'epost', 'eposta', 'authpw', 'token_auth', 'tokenauth', 'token', 'pin', 'ibanaccountnum', 'ibanaccountnumber', 'account', 'accountnum', 'auth', 'age', 'alter', 'tel', 'city', 'cell', 'cellphone', 'bic', 'iban', 'swift', 'kontonummer', 'konto', 'kontonr', 'phone', 'mobile', 'mobiili', 'mobilne', 'handynummer', 'téléphone', 'telefono', 'ssn', 'socialsecuritynumber', 'socialsec', 'socsec', 'address', 'addressline1', 'addressline2','billingaddress', 'billingaddress1', 'billingaddress2','shippingaddress', 'shippingaddress1', 'shippingaddress2', 'vat', 'vatnumber', 'gst', 'gstnumber', 'tax', 'taxnumber', 'steuernummer', 'adresse', 'indirizzo', 'adres', 'dirección', 'osoite', 'address1', 'address2', 'address3', 'street', 'strasse', 'rue', 'via', 'ulica', 'calle', 'sokak', 'zip', 'zipcode', 'plz', 'postleitzahl', 'postalcode', 'postcode', 'dateofbirth', 'dob', 'telephone', 'telefon', 'telefonnr', 'telefonnummer', 'password', 'passwort', 'kennwort', 'wachtwoord', 'contraseña', 'passord', 'hasło', 'heslo', 'wagwoord', 'parole', 'contrasenya', 'heslo', 'clientid', 'identifier', 'id', 'consumersecret', 'webhooksecret', 'consumerkey', 'keyconsumersecret', 'keyconsumerkey', 'clientsecret', 'secret', 'secretq', 'secretquestion', 'privatekey', 'publickey', 'pw', 'pwd', 'pwrd', 'pword', 'paword', 'pasword', 'paswort', 'pass'];

            if (type === 'password'
                || type === 'email'
                || type === 'tel'
                || type === 'hidden'
                || blockedFields.indexOf(attrName) !== -1
                || blockedFields.indexOf(attrId) !== -1
                || blockedFields.indexOf(attrAutoCo) !== -1
                || element.hasAttribute(node, 'data-piwik-mask')
                || element.hasAttribute(node, 'data-matomo-mask')) {
                return true;
            }

            if (!isSelectField && node && node.value) {
                if (!type || type === 'text' || type === 'number' || (node && node.nodeName === 'TEXTAREA')) {
                    if (/^\d{7,24}$/.test(String(node.value))) {
                        // when entering 12-21 digits, we assume it is credit card. Longest credit card has 19 characters
                        // but users may enter too many characters. we also want to avoid some phone numbers etc
                        return true;
                    }

                    if (String(node.value).indexOf('@') !== -1 && String(node.value).length > 2) {
                        // user might be entering an email address, we force the masking of it
                        return true;
                    }
                }
            }

            if (shouldCheckParents) {
                var parent = node.parentNode ? node.parentNode : null;
                var hasUnmask = false;

                // check if any parent node has data-piwik-mask. We only do this when requested for performance reasons
                while (parent) {
                    if (element.hasAttribute(parent, 'data-piwik-mask')
                        || element.hasAttribute(parent, 'data-matomo-mask')) {
                        return true;
                    } else {
                        if (!hasUnmask && parent && element.hasAttribute(parent, 'data-matomo-unmask')) {
                            // if any mask is set on any parent, this unmask is supposed to be ignored!
                            hasUnmask = true;
                        }
                        parent = parent.parentNode ? parent.parentNode : null;
                    }
                }

                if (hasUnmask) {
                    return false;
                }
            }

            if (element.hasAttribute(node, 'data-matomo-unmask')) {
                return false;
            }

            if (isSelectField) {
                return false; // it is fine to record this select field
            }

            return true;
        },
        shouldMaskContent: function (node, shouldCheckParents) {
            if (!node) {
                return false;
            }

            if (node.tagName && node.tagName !== 'FORM' && element.hasAttribute(node, 'data-matomo-mask')) {
                return true;
            }

            if (shouldCheckParents) {
                var parent = node.parentNode ? node.parentNode : null;

                // check if any parent node has data-matomo-mask. We only do this when requested for performance reasons
                // when defined on a form element, it will apply to all form elements within that container
                while (parent) {
                    if (parent.tagName !== 'FORM' && element.hasAttribute(parent, 'data-matomo-mask')) {
                        return true;
                    } else {
                        parent = parent.parentNode ? parent.parentNode : null;
                    }
                }
            }

            return false;
        }
    };

    var trackerUtils = {
        isArray: function (variable) {
            return typeof variable === 'object' && variable !== null && typeof variable.length === 'number';
        },
        getCurrentTime: function () {
            return new Date().getTime();
        },
        getTimeSincePageReady: function () {
            if (!timeWhenPageReady) {
                return 0;
            }
            return (new Date().getTime()) - timeWhenPageReady;
        },
        roundTimeToSeconds: function (timeInMs) {
            return Math.round(timeInMs / 1000);
        },
        getRandomInt: function (min, max) {
            return Math.round(Math.random() * (max - min) + min);
        },
        isNumber: function (text) {
            return !isNaN(text);
        },
        trim: function (text)
        {
            if (text && String(text) === text) {
                return text.replace(/^\s+|\s+$/g, '');
            }

            return text;
        },
        generateUniqueId: function () {
            var id = '';
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charLen = chars.length;

            for (var i = 0; i < 6; i++) {
                id += chars.charAt(Math.floor(Math.random() * charLen));
            }

            return id;
        }
    };

    function hasParameterInUrl(parameter)
    {
        return location.href && location.href.indexOf(parameter) > 0;
    }

    function shouldForceBeInSample()
    {
        return hasParameterInUrl('pk_hsr_forcesample=1') || hasParameterInUrl('pk_hsr_capturescreen=1');
    }

    function shouldForceNotBeInSample()
    {
        return hasParameterInUrl('pk_hsr_forcesample=0');
    }

    function isInTestGroup(sampleRate)
    {
        if (shouldForceBeInSample()) {
            // when we take a screenshot, the user also needs to be included otherwise it won't be possible to capture
            // the screen.
            return true;
        }

        if (shouldForceNotBeInSample()) {
            return false;
        }

        if (sampleRate >= 100) {
            return true;
        }

        if (sampleRate <= 0) {
            return false;
        }

        if (sampleRate >= 1) {
            return sampleRate >= trackerUtils.getRandomInt(1, maxSampleRate);
        }

        // eg when 0.1
        return (sampleRate * 10) >= trackerUtils.getRandomInt(1, maxSampleRate * 10);
    }

    function enrichTracker(tracker)
    {
        if ('undefined' !== typeof tracker.HeatmapSessionRecording) {
            return;
        }

        tracker.HeatmapSessionRecording = {
            myId: trackerUtils.generateUniqueId(),
            hasReceivedConfig: false,
            hasTrackedData: false,
            hasSentStopTrackingEvent: false,
            enabled: true,
            hsrIdsToGetDOM: [],
            disable: function () {
                this.enabled = false;
            },
            enable: function () {
                this.enabled = true;
            },
            isEnabled: function () {
                return isHsrEnabled && this.enabled;
            },
            numSentTrackingRequests: 0,
            Heatmap: {
                data: [], // holds all data that needs to be tracked
                hsrids: [], // as soon as one of those configuration is "active / condition met", the hsrid of the config is added here
                configs: [], // here we hold all configurations
                addConfig: function (config) {
                    if ('object' !== typeof config || !config.id) {
                        return;
                    }

                    config.id = parseInt(config.id, 10);

                    this.configs.push(config);

                    if ('undefined' === typeof config.sample_rate) {
                        config.sample_rate = maxSampleRate;
                    } else {
                        // we need to make sure to limit to max value
                        config.sample_rate = Math.min(parseFloat(config.sample_rate), maxSampleRate);
                    }

                    // a heatmap is so far always immediately enabled
                    if (config.id && isInTestGroup(config.sample_rate) && shouldTriggerRecording(config)) {
                        this.addHsrId(config.id);

                        if (config.getdom || hasParameterInUrl('pk_hsr_capturescreen=1')) {
                            tracker.HeatmapSessionRecording.hsrIdsToGetDOM.push(config.id);
                        }
                    }
                },
                addHsrId: function (idSiteHsr) {
                    this.hsrids.push(idSiteHsr);

                    if (tracker.HeatmapSessionRecording.hasTrackedData) {
                        // we record it as an event so it makes sure to be only tracked if we also send other data
                        tracking.recordData(RECORD_TYPE_HEATMAP, {ty: EVENT_TYPE_LINK_HSR, id: idSiteHsr});
                    }
                }
            },
            Both: {
                data: []
            },
            Session: {
                data: [], // holds all data that needs to be tracked
                hsrids: [], // as soon as one of those configuration is "active / condition met", the hsrid of the config is added here
                configs: [], // here we hold all configurations
                addConfig: function (config) {
                    if ('object' !== typeof config || !config.id) {
                        return;
                    }

                    config.id = parseInt(config.id, 10);

                    if ('undefined' === typeof config.sample_rate) {
                        config.sample_rate = maxSampleRate;
                    } else {
                        // we need to make sure to limit to max value
                        config.sample_rate = Math.min(parseFloat(config.sample_rate), maxSampleRate);
                    }

                    config.conditionsMet = false;

                    this.configs.push(config);

                    var idSite = parseInt(tracker.getSiteId(), 10);
                    var storageValue = storage.get(tracker, config.id);

                    if (1 === storageValue && !shouldForceNotBeInSample()) {
                        // we make sure user will be directly recorded, but only if user did not force to be excluded manually via url
                        config.sample_rate = maxSampleRate;
                        config.activity = false;
                        config.min_time = 0;
                    } else if (shouldForceBeInSample()) {
                        // user forced to be recorded, we ignore any stored value or test group
                    } else if (0 === storageValue || !isInTestGroup(config.sample_rate)) {
                        // visitor is not in sample group, we always set cookie again to extend value
                        // unless user forced to be in test group
                        storage.set(tracker, config.id, 0);
                        return;
                    }

                    this.checkConditionsMet();

                    if (config.min_time) {
                        // there might be a little race conditions if this is called when page is not ready yet,
                        // and later timeSincePageReady is "changed / resetted" then this won't become "true".

                        var self = this;
                        Piwik.DOM.onReady(function () {
                            // we add 120ms extra just to make sure we are over the session time
                            var timeoutMs = (config.min_time * 1000) - trackerUtils.getTimeSincePageReady() + 120;
                            if (timeoutMs >= 0) {
                                setTimeout(function () {
                                    self.checkConditionsMet();
                                }, timeoutMs);
                            } else {
                                self.checkConditionsMet();
                            }
                        });
                    }
                },
                checkConditionsMet: function () {
                    var config;
                    for (var i = 0; i < this.configs.length; i++) {
                        config = this.configs[i];
                        if (config && !config.conditionsMet) {
                            var met = true;

                            if (config.min_time && config.min_time >= trackerUtils.roundTimeToSeconds(trackerUtils.getTimeSincePageReady())) {
                                met = false;
                            }

                            if (config.activity && !hasScrolled) {
                                // check if scrollbars are shown at all, if not we set it as scrolled
                                hasScrolled = dom.getDocumentHeight() <= dom.getWindowSize().height;
                            }

                            if (config.activity && (!hasClicked || !hasScrolled)) {
                                met = false;
                            }

                            if (met) {
                                config.conditionsMet = true;

                                if (shouldTriggerRecording(config)) {
                                    if ('undefined' === typeof config.keystrokes || !config.keystrokes || config.keystrokes === '0') {
                                        // as soon as one disables it, we need to disable them all, not possible to do it differently
                                        // as they are all linked with each other and also affect already recorded / tracked mutations
                                        // etc.
                                        recordKeystrokes = false;
                                    }

                                    this.addHsrId(config.id);
                                }
                            }
                        }
                    }
                },
                addHsrId: function (idSiteHsr) {
                    this.hsrids.push(idSiteHsr);

                    if (tracker.HeatmapSessionRecording.hasTrackedData) {
                        // we record it as an event so it makes sure to be only tracked if we also send other data
                        // if we sent this as a separate request, we risk to link this ID even though we never sent
                        // any initial dom
                        tracking.recordData(RECORD_TYPE_SESSION, {ty: EVENT_TYPE_LINK_HSR, id: idSiteHsr});
                    }

                    var idSite = parseInt(tracker.getSiteId(), 10);

                    // remember recording of this visitor to also record next page view
                    storage.set(tracker, idSiteHsr, 1);
                }
            },
            addConfig: function (config) {
                this.hasReceivedConfig = true;

                if ('undefined' === typeof config || !config) {
                    configuration.checkAllConfigsReceived();
                    return;
                }

                if ('object' === typeof config.heatmap) {
                    this.Heatmap.addConfig(config.heatmap);
                }

                var i;

                if (config.heatmaps && trackerUtils.isArray(config.heatmaps) && config.heatmaps.length) {
                    for (i = 0; i < config.heatmaps.length; i++) {
                        this.Heatmap.addConfig(config.heatmaps[i]);
                    }
                }
                if (canTrackSessionRecording) {
                    if (config.sessions && trackerUtils.isArray(config.sessions) && config.sessions.length) {
                        for (i = 0; i < config.sessions.length; i++) {
                            this.Session.addConfig(config.sessions[i]);
                        }
                    }

                    if ('object' === typeof config.session) {
                        this.Session.addConfig(config.session);
                    }
                }

                configuration.checkAllConfigsReceived();
            }
        };
    }

    var initialWindowSize = dom.getWindowSize();

    var tracking = {
        getPiwikTrackers: function ()
        {
            if (null === customPiwikTrackers) {
                if ('object' === typeof Piwik && Piwik.getAsyncTrackers) {
                    var trackers = Piwik.getAsyncTrackers();

                    if (!trackers || !trackers.length) {
                        return [];
                    }

                    return trackers;
                }
            }

            if (trackerUtils.isArray(customPiwikTrackers)) {
                return customPiwikTrackers;
            }

            return [];
        },
        sendQueuedData: function (tracker, shouldEndRecording) {
            if (!isDOMloaded || !hsrIdView) {
                // we start tracking only after onload event so we know everything is loaded and rendered and we
                // know full document size and can calculate scroll max percentage, fold percentage etc correctly.
                return;
            }

            if (!tracker || !tracker.HeatmapSessionRecording) {
                return;
            }

            var hsr = tracker.HeatmapSessionRecording;

            if (!hsr.isEnabled()) {
                return;
            }

            var hsrIds = [];
            var queuedData = [];

            if (hsr.Heatmap.hsrids && hsr.Heatmap.hsrids.length) {
                // we always need to add all active hsrids, otherwise, on first request, there might not be any heatmap
                // data yet and then we would never assign these hsrids so we always send those ids no matter if there
                // is any data or not
                hsrIds = hsr.Heatmap.hsrids;

                if (hsr.Heatmap.data.length) {
                    queuedData = hsr.Heatmap.data;
                    hsr.Heatmap.data = [];
                }
            }

            var isUsingSessionRecording = hsr.Session.hsrids && hsr.Session.hsrids.length && recording.initialDOM;
            if (isUsingSessionRecording) {
                // we always need to add all active hsrids, otherwise, on first request, there might not be any session
                // data yet and then we would never assign these hsrids so we always send those ids no matter if there
                // is any data or not

                // we also need to make sure we have the initial DOM, otherwise, if this is not tracked, replaying
                // the session won't work. even better would be to have a separate flag whether we had it at least
                // once in session.data.length or so
                hsrIds = hsrIds.concat(hsr.Session.hsrids);

                if (hsr.Session.data.length) {
                    queuedData = queuedData.concat(hsr.Session.data);
                    hsr.Session.data = [];

                    if (!recordKeystrokes) {
                        // we again make sure to not send any keystrokes if disabled. This happens when eg
                        // we record some keystrokes before any of the tracker is initialized or has received a config
                        for (var i = (queuedData.length - 1); i >= 0; i--) {
                            if (queuedData[i] && queuedData[i].ty && queuedData[i].ty === EVENT_TYPE_FORM_TEXT) {
                                queuedData.splice(i, 1);
                            }
                        }
                    }
                }
            }

            if (hsrIds.length && hsr.Both.data.length) {
                // we track Both only if there is any heatmap or session recording to be tracked
                queuedData = queuedData.concat(hsr.Both.data);
                hsr.Both.data = [];
            }

            if ('undefined' === typeof shouldEndRecording) {
                shouldEndRecording = this.shouldEndRecording(tracker);
            }

            if (shouldEndRecording && hsr.hasTrackedData && !hsr.hasSentStopTrackingEvent && isUsingSessionRecording) {
                // we do not send a stop event when user was not active before that (no tracking request)
                queuedData.push({ty: EVENT_TYPE_STOP_RECORDING});

                // will be set to true if the tracking of a session or heatmap finishes "early". Set to make sure we do not track this event twice on unload again
                hsr.hasSentStopTrackingEvent = true;
            }

            if (!hsrIds || !hsrIds.length || !queuedData || !queuedData.length) {
                return;
            }

            // we only add initial dom once when needing to get dom, but only if we send data anyway
            if (tracker.HeatmapSessionRecording.hsrIdsToGetDOM && tracker.HeatmapSessionRecording.hsrIdsToGetDOM.length) {
                if (!recording.initialDOM && canTrackSessionRecording) {
                    var mirror = new TreeMirrorClient(documentAlias, {
                        initialize: function(rootId, children) {
                            // happens when no session recording is active at the same time
                            recording.initialDOM = pJson.stringify({
                                rootId: rootId,
                                children: children
                            });
                        }
                    });
                    mirror.disconnect();
                }

                if (recording.initialDOM && canTrackSessionRecording) {
                    for (var index = 0; index < tracker.HeatmapSessionRecording.hsrIdsToGetDOM.length; index++) {
                        queuedData.push({
                            ty: EVENT_TYPE_PAGE_TREEMIRROR,
                            dom: recording.initialDOM,
                            id: tracker.HeatmapSessionRecording.hsrIdsToGetDOM[index]
                        });
                    }

                    tracker.HeatmapSessionRecording.hsrIdsToGetDOM = [];
                }
            }

            hsr.hasTrackedData = true;

            this.sendQueuedDataRequestNow(tracker, hsrIds, queuedData);

            if (shouldEndRecording) {
                // we stop the recording at the end to make sure all other queued events will be still tracked
                // this is useful if there were eg a few events just before the maxCaptureTime. In this case we make
                // sure to still send them

                // this is not 100% multi tracker safe and will also directly disable it for multiple trackers meaning
                // for them likely the recording end date won't be sent
                Piwik.HeatmapSessionRecording.disable();
            }
        },
        shouldEndRecording: function (tracker) {
            var timeSinceLoad = trackerUtils.getTimeSincePageReady();

            if (maxCapturingTimeEnd < timeSinceLoad) {
                // we force stop recording eg after 30 minutes
                return true;
            }

            if (maxCapturingTimeStart < timeSinceLoad) {
                var hsr = tracker.HeatmapSessionRecording;
                var hasSentFewRequestsSoFar = !hsr.numSentTrackingRequests || hsr.numSentTrackingRequests <= increaseMaxCaptureTimeWhenLessThanXRequests;
                var msOneMinute = 60 * 1000;
                var wasActiveInLastMinute = timeLastEvent && (timeSinceLoad < (msOneMinute + timeLastEvent));

                if (hasSentFewRequestsSoFar || wasActiveInLastMinute) {
                    // we increase max capture time eg by another 5 minutes
                    maxCapturingTimeStart = timeSinceLoad + maxCaptureTimeIncreaseWhenLessThanXRequests;
                } else {
                    return true;
                }
            }

            return false;
        },
        sendQueuedDataRequestNow: function (tracker, hsrIds, events) {

            var data = '';
            for (var i = 0; i < events.length; i++) {
                for (var name in events[i]) {
                    if (Object.prototype.hasOwnProperty.call(events[i], name)) {
                        data += '&hsr_ev[' + i + '][' + name + ']=' + encodeURIComponent(events[i][name]);
                    }
                }
            }

            for (var index = 0; index < hsrIds.length; index++) {
                data += '&hsr_ids[]=' + encodeURIComponent(hsrIds[index]);
            }

            var requestUrl = 'hsr_vid=' + hsrIdView + data;
            var windowSize = dom.getWindowSize();
            var docHeight = dom.getDocumentHeight();

            if (!recording.scrollMaxPercentage) {
                // recording.scrollMaxPercentage is only set after first scroll so before we have to detect ourselves
                var lastScrollTop = dom.getScrollTop();
                recording.scrollMaxPercentage = parseInt(((windowSize.height + lastScrollTop) / docHeight) * maxScrollAccuracy, 10);
            }

            requestUrl += '&hsr_vw=' + encodeURIComponent(windowSize.width);
            requestUrl += '&hsr_vh=' + encodeURIComponent(windowSize.height);
            requestUrl += '&hsr_ti=' + trackerUtils.getTimeSincePageReady();
            requestUrl += '&hsr_smp=' + recording.scrollMaxPercentage;
            requestUrl += '&hsr_fyp=' + parseInt((initialWindowSize.height / docHeight) * maxScrollAccuracy, 10);

            tracker.HeatmapSessionRecording.numSentTrackingRequests++;
            tracker.trackRequest(requestUrl, null, null, 'HeatmapSessionRecording');

            logConsoleMessage('track: ' + requestUrl);
        },
        recordData: function (dataType, data) {
            if (!isPluginInitialized) {
                recordedDataBeforeTrackerSetup.push({type: dataType, data: data});
                return;
            }

            var trackers = tracking.getPiwikTrackers();
            trackers.forEach(function (tracker) {
                if (tracker.HeatmapSessionRecording && tracker.HeatmapSessionRecording.isEnabled()) {
                    if ('object' === typeof data && 'undefined' !== typeof data.ti
                        && data.ti && (!timeLastEvent || data.ti > timeLastEvent)
                        && data.ty && data.ty !== EVENT_TYPE_MUTATION) {
                        // we ignore mutation events, these are not active events where the user did something
                        // prevents extending the maxCapturingTime in case the website keeps updating the page
                        timeLastEvent = data.ti;
                    }

                    if (RECORD_TYPE_BOTH === dataType) {
                        tracker.HeatmapSessionRecording.Both.data.push(data);
                    } else if (RECORD_TYPE_HEATMAP === dataType) {
                        tracker.HeatmapSessionRecording.Heatmap.data.push(data);
                    } else if (RECORD_TYPE_SESSION === dataType) {
                        tracker.HeatmapSessionRecording.Session.data.push(data);
                    }
                }
            });

            if (debugMode) {
                logConsoleMessage('recorddata', pJson.stringify(data));
            }
        },
        stopSendingData: function () {
            var trackers = tracking.getPiwikTrackers();
            trackers.forEach(function (tracker) {
                if (tracker.HeatmapSessionRecording) {
                    var hsr = tracker.HeatmapSessionRecording;
                    if ('undefined' !== typeof hsr.trackingInterval) {
                        // cleanup resources to not run interval every second forever
                        clearInterval(hsr.trackingInterval);
                        delete hsr.trackingInterval;
                    }
                }
            });
        },
        startSendingData: function () {
            var trackers = tracking.getPiwikTrackers();
            trackers.forEach(function (tracker) {
                if (tracker.HeatmapSessionRecording
                    && 'undefined' === typeof tracker.HeatmapSessionRecording.trackingInterval) {
                    // we make sure it has not been set up yet and is not currently sending data already

                    // if there are multiple trackers, we do not want to fire them at the same time
                    var intervalMs = trackerUtils.getRandomInt(1250, 1450);
                    tracker.HeatmapSessionRecording.trackingInterval = setInterval(function () {
                        tracking.sendQueuedData(tracker);
                    }, intervalMs);

                    // don't wait for another second to send first data, start sending possibly queued data now
                    tracking.sendQueuedData(tracker);
                }
            });
        }
    };

    function callAsyncReadyMethod()
    {
        if (typeof window === 'object' && 'function' === typeof windowAlias.piwikHeatmapSessionRecordingAsyncInit) {
            windowAlias.piwikHeatmapSessionRecordingAsyncInit();
        }

        var eventsQueued = recordedDataBeforeTrackerSetup;
        recordedDataBeforeTrackerSetup = [];
        isPluginInitialized = true;

        // replay previously recorded events
        for (var i = 0; i < eventsQueued.length; i++) {
            tracking.recordData(eventsQueued[i].type, eventsQueued[i].data);
        }

        // we can only add this code after async init to make sure we recognize all parameters and configurations
        // and to make sure piwik trackers have been configured
        Piwik.DOM.onLoad(function () {
            isDOMloaded = true;

            if (isHsrEnabled) {
                var trackers = tracking.getPiwikTrackers();
                if (trackers && trackers.length) {
                    // only if it has not been disabled meanwhile by a user and if there is actually a tracker configured
                    // we call the method only to start the recording, not to actually enable it
                    Piwik.HeatmapSessionRecording.enable();
                }
            }
        });
    }

    var recording = {
        moveEvents: ['mousemove', 'touchmove'],
        clickEvents: ['mousedown'],
        scrollEvents: ['scroll', 'resize'],
        lastScroll: null,
        lastElementScroll: null,
        lastMove: null,
        lastResize: null,
        scrollMaxPercentage: 0,
        lastResizeInterval: null,
        lastScrollInterval: null,
        lastMoveInterval: null,
        isRecording: false,
        isRecordingMutations: false,
        startRecording: function()
        {
            if (!isHsrEnabled || this.isRecording) {
                return;
            }

            this.isRecording = true;

            // append each recorded data individually to each tracker so they can individually start sending data
            // once data has arrived for a tracker. also depending on which tracker heatmap or session is enabled
            // we need to track different data per tracker

            this.lastScrollInterval = setInterval(function () {
                if (recording.lastScroll) {
                    var scroll = recording.lastScroll;
                    recording.lastScroll = null;

                    var data = {
                        ti: scroll.time,
                        ty: EVENT_TYPE_SCROLL,
                        x: scroll.scrollX,
                        y: scroll.scrollY
                    };

                    tracking.recordData(RECORD_TYPE_SESSION, data);
                }
                if (recording.lastElementScroll) {
                    var scroll = recording.lastElementScroll;
                    recording.lastElementScroll = null;

                    var data = {
                        ti: scroll.time,
                        ty: EVENT_TYPE_SCROLL_ELEMENT,
                        s: scroll.selector,
                        x: scroll.scrollX,
                        y: scroll.scrollY
                    };

                    tracking.recordData(RECORD_TYPE_SESSION, data);
                }
            }, 200);

            this.lastResizeInterval = setInterval(function () {
                if (recording.lastResize) {
                    var resize = recording.lastResize;
                    recording.lastResize = null;
                    var data = {
                        ti: resize.ti,
                        ty: EVENT_TYPE_RESIZE,
                        x: resize.width,
                        y: resize.height
                    };

                    tracking.recordData(RECORD_TYPE_SESSION, data);
                }
            }, 200);

            // also listen to double click?

            this.lastMoveInterval = setInterval(function () {
                if (recording.lastMove) {
                    var move = recording.lastMove;
                    recording.lastMove = null;
                    var data = {
                        ti: move.time,
                        ty: EVENT_TYPE_MOVEMENT,
                        s: move.selector,
                        x: move.offsetx,
                        y: move.offsety
                    };

                    tracking.recordData(RECORD_TYPE_BOTH, data);
                }
            }, 200);

            this.scrollEvents.forEach(function (eventName) {
                windowAlias.addEventListener(eventName, recording.onScroll, true);
            });

            this.clickEvents.forEach(function (eventName) {
                windowAlias.addEventListener(eventName, recording.onClick, true);
            });

            this.moveEvents.forEach(function (eventName) {
                windowAlias.addEventListener(eventName, recording.onMove, true);
            });
        },
        mirror: null,
        initialDOM: null,
        startRecordingMutations: function () {
            if (!isHsrEnabled || !canTrackSessionRecording || this.isRecordingMutations) {
                // either already set up or does not support observer
                return;
            }

            this.isRecordingMutations = true;

            windowAlias.addEventListener('resize', recording.onResize, true);
            windowAlias.addEventListener('change', recording.onFormChange, true);

            try {
                this.mirror = new TreeMirrorClient(document, {
                    initialize: function(rootId, children) {
                        // track initial dom mutations
                        var data = {
                            ty: EVENT_TYPE_INITIAL_DOM,
                            ti: 0,
                            te: pJson.stringify({rootId: rootId, children: children})
                        };

                        if (!recording.initialDOM) {
                            recording.initialDOM = data.te;
                        }

                        tracking.recordData(RECORD_TYPE_SESSION, data);
                    },
                    applyChanged: function(removed, addedOrMoved, attributes, text) {
                        if (removed.length || addedOrMoved.length || attributes.length || text.length)  {
                            var data = {
                                ti: trackerUtils.getTimeSincePageReady(),
                                ty: EVENT_TYPE_MUTATION,
                                te: {}
                            };
                            if (removed.length) {
                                data.te.rem = removed;
                            }
                            if (addedOrMoved.length) {
                                data.te.adOrMo = addedOrMoved;
                            }
                            if (attributes.length) {
                                data.te.att = attributes;
                            }
                            if (text.length) {
                                data.te.text = text;
                            }

                            data.te = pJson.stringify(data.te);

                            tracking.recordData(RECORD_TYPE_SESSION, data);
                        }
                    }
                });

            } catch (e) {
                logConsoleMessage(e);
            }
        },
        onResize: function () {
            // todo check if e.target === window?

            var size = dom.getWindowSize();

            recording.lastResize = {
                ti: trackerUtils.getTimeSincePageReady(),
                width: size.width,
                height: size.height
            };
        },
        onFormChange: function (e) {
            if (!('target' in e) || !e.target) {
                return;
            }

            var node = e.target;
            var tagName = element.getTagName(node);

            if (!tagName) {
                return;
            }

            var time = trackerUtils.getTimeSincePageReady();
            var eventType = EVENT_TYPE_FORM_VALUE;
            var isCheckField = false;

            if (tagName === 'input') {
                var fieldType = element.getAttribute(node, 'type');

                if (String(fieldType).toLowerCase() === 'radio' || String(fieldType).toLowerCase() === 'checkbox') {
                    isCheckField = true;
                } else {
                    // we ignore this as we handle this as in onFormChange
                    eventType = EVENT_TYPE_FORM_TEXT;
                }
            } else if (tagName === 'textarea') {
                eventType = EVENT_TYPE_FORM_TEXT;
            } else if (tagName !== 'select') {
                return;
            }

            if (!recordKeystrokes && eventType === EVENT_TYPE_FORM_TEXT) {
                // recording of any text is disabled
                return;
            }

            var selector = element.getSelector(node);

            var text = '';
            if (isCheckField) {
                text = node.checked ? '1' : '0';
            } else if (eventType === EVENT_TYPE_FORM_TEXT && 'undefined' !== typeof node.value) {
                text = String(node.value);
                if (text > maxLenTextInput) {
                    text = text.substr(0, maxLenTextInput);
                }

                if (element.shouldMaskField(node, true)) {
                    text = element.maskFormField(text, element.getAttribute(node, 'type') === 'password');
                }
            } else if (eventType === EVENT_TYPE_FORM_VALUE && 'undefined' !== typeof node.value) {
                text = String(node.value);
            }

            var data = {
                ti: time,
                ty: eventType,
                s: selector,
                te: text
            };

            if (selector) {
                tracking.recordData(RECORD_TYPE_SESSION, data);
            } else {
                logConsoleMessage('No selector found for text input ', e);
            }
        },
        onScroll: function(event) {
            if (!hasScrolled) {
                // eg when a session requires a scroll and a click in order to actually track a recording
                hasScrolled = true;
                recording.checkTrackersIfConditionsMet();
            }

            var time = trackerUtils.getTimeSincePageReady();

            if (event && event.type && event.type === 'scroll' && event.target && event.target !== documentAlias) {
                // scroll on an element
                var target = event.target;

                if ('undefined' === typeof target.scrollTop) {
                    return;// not supported
                }

                var scrollTop = target.scrollTop;
                var scrollLeft = target.scrollLeft;

                var eleWidth = element.getWidth(target);
                var eleHeight = element.getHeight(target);

                if (eleWidth <= 0 || eleHeight <= 0 || !eleWidth || !eleHeight) {
                    return; // element is not visible and prevent division by zero.
                }

                var selector = element.getSelector(target);

                recording.lastElementScroll = {
                    time: time,
                    selector: selector,
                    scrollY: parseInt((maxScrollAccuracy * scrollTop) / eleHeight, 10),
                    scrollX: parseInt((maxScrollAccuracy * scrollLeft) / eleWidth, 10)
                };
                return;
            }

            // scroll on the document itself
            var lastScrollTop = parseInt(dom.getScrollTop(), 10);
            var lastScrollLeft = parseInt(dom.getScrollLeft(), 10);
            var docHeight = dom.getDocumentHeight();
            var docWidth = dom.getDocumentWidth();

            recording.lastScroll = {
                time: time,
                scrollY: parseInt((maxScrollAccuracy * lastScrollTop) / docHeight, 10),
                scrollX: parseInt((maxScrollAccuracy * lastScrollLeft) / docWidth, 10)
            };

            var lastScrollPercentage = parseInt((maxScrollAccuracy * (lastScrollTop + dom.getWindowSize().height)) / docHeight, 10);

            if (lastScrollPercentage > recording.scrollMaxPercentage) {
                // track new scroll reach position and percentage
                recording.scrollMaxPercentage = lastScrollPercentage;
            }
        },
        checkTrackersIfConditionsMet: function () {
            var trackers = tracking.getPiwikTrackers();
            for (var i = 0; i < trackers.length; i++) {
                if (trackers[i]
                    && trackers[i].HeatmapSessionRecording
                    && trackers[i].HeatmapSessionRecording.Session) {
                    trackers[i].HeatmapSessionRecording.Session.checkConditionsMet();
                }
            }
        },
        onClick: function(e) {
            logConsoleMessage('click');

            if (!hasClicked) {
                hasClicked = true;
                recording.checkTrackersIfConditionsMet();
            }

            if (!('target' in e) || !('pageY' in e) || !('pageX' in e) || !e.target) {
                return;
            }

            var time = trackerUtils.getTimeSincePageReady();

            // we unset a poosibly set mouse move for now as we can move the mouse there automatically
            // saves a wee bit of data but makes maybe replaying more complicated? not sure..
            recording.lastMove = null;

            var offset = element.getOffset(e.target);
            var offsetx = parseInt(((e.pageX - offset.left) / offset.width) * pixelOffsetAccuracy, 10);
            var offsety = parseInt(((e.pageY - offset.top) / offset.height) * pixelOffsetAccuracy, 10);
            var selector = element.getSelector(e.target);

            if (offsetx % 2 === 1) {
                offsetx++; // we only record "even" pixel number to slightly reduce the number of rows when grouping / archiving
            }
            if (offsety % 2 === 1) {
                offsety++; // we only record "even" pixel number to slightly reduce the number of rows when grouping / archiving
            }

            if (debugMode && (isNaN(offsetx) || isNaN(offsety))) {
                logConsoleMessage('could not detect x or y coordinate for selector ' + selector, e);
            }

            var data = {
                ti: time,
                ty: EVENT_TYPE_CLICK,
                s: selector,
                x: offsetx,
                y: offsety
            };

            if (selector) {
                tracking.recordData(RECORD_TYPE_BOTH, data);
            } else {
                logConsoleMessage('No selector found for click ', e);
            }
        },
        onMove: function (e) {
            if (!enableRecordMovements) {
                return;
            }
            if (!('clientY' in e) || !('clientX' in e) || !('pageX' in e) || !('pageY' in e)) {
                return;
            }

            var x = e.clientX;
            var y = e.clientY;
            var node = documentAlias.elementFromPoint(x, y);

            if (node) {
                var time = trackerUtils.getTimeSincePageReady();

                var offset = element.getOffset(node);
                var offsetx = parseInt(((e.pageX - offset.left) / offset.width) * pixelOffsetAccuracy, 10);
                var offsety = parseInt(((e.pageY - offset.top) / offset.height) * pixelOffsetAccuracy, 10);
                if (offsetx % 2 === 1) {
                    offsetx++; // we only record "even" pixel number to slightly reduce the number of rows when grouping / archiving
                }
                if (offsety % 2 === 1) {
                    offsety++; // we only record "even" pixel number to slightly reduce the number of rows when grouping / archiving
                }

                var selector = element.getSelector(node);

                if (debugMode && (isNaN(offsetx) || isNaN(offsety))) {
                    logConsoleMessage('could not detect x or y coordinate for selector ' + selector, e);
                }

                if (selector) {
                    if (!isFirstMouseMoveEvent) {
                        // we record this one directly to "make sure" to detect initial mouse position as the initial
                        // mouse position can be only detected when this event occurs.
                        isFirstMouseMoveEvent = true;
                        var data = {ti: 0, ty: EVENT_TYPE_MOVEMENT, s: selector, x: offsetx, y: offsety};
                        tracking.recordData(RECORD_TYPE_BOTH, data);
                    } else {
                        recording.lastMove = {selector: selector, offsetx: offsetx, offsety: offsety, time: time};
                    }
                } else {
                    logConsoleMessage('No selector found for click ', e);
                }
            }
        },
        stopRecording: function () {
            this.isRecording = false;

            if (this.lastResizeInterval !== null) {
                clearInterval(this.lastResizeInterval);
                this.lastResizeInterval = null;
            }
            if (this.lastScrollInterval !== null) {
                clearInterval(this.lastScrollInterval);
                this.lastScrollInterval = null;
            }
            if (this.lastMoveInterval !== null) {
                clearInterval(this.lastMoveInterval);
                this.lastMoveInterval = null;
            }

            this.scrollMaxPercentage = 0;

            // make sure they won't be tracked if tracking is started again later
            this.lastScroll = null;
            this.lastElementScroll = null;
            this.lastMove = null;
            this.lastResize = null;

            this.scrollEvents.forEach(function (eventName) {
                windowAlias.removeEventListener(eventName, recording.onScroll, true);
            });
            this.moveEvents.forEach(function (eventName) {
                windowAlias.removeEventListener(eventName, recording.onMove, true);
            });
            this.clickEvents.forEach(function (eventName) {
                windowAlias.removeEventListener(eventName, recording.onClick, true);
            });
        },
        stopRecordingMutations: function () {
            this.isRecordingMutations = false;

            windowAlias.removeEventListener('resize', recording.onResize, true);
            windowAlias.removeEventListener('change', recording.onFormChange, true);

            this.initialDOM = null;

            if (this.mirror) {
                this.mirror.disconnect();
                this.mirror = null;
            }
        }
    };

    var configuration = {
        fetch: function() {
            // THIS FUNCTION NEEDS TO WORK IN ALL BROWSERS

            var numRequestsSent = 0;

            var trackers = tracking.getPiwikTrackers();

            if (!trackers || !trackers.length) {
                // no tracker configured yet or user won't take part because browser not supported
                return;
            }

            for (var i = 0; i < trackers.length; i++) {
                var tracker = trackers[i];
                if (tracker
                    && tracker.HeatmapSessionRecording
                    && !tracker.HeatmapSessionRecording.hasReceivedConfig
                    && tracker.HeatmapSessionRecording.isEnabled()) {
                    // we check for !hasReceivedConfig so a user can set a config manually for a tracker and prevent
                    // sending this request to the Matomo instance for even faster performance
                    // if a user for sure has this plugin not available on a piwik instance, the user may also disable
                    // the feature for that tracker so we for sure do not send this request

                    var trackerUrl = tracker.getPiwikUrl();
                    var trackerIdSite = tracker.getSiteId();

                    if (!trackerUrl || !trackerIdSite) {
                        logConsoleMessage('cannot find piwik url for tracker or site, disabling heatmap & session recording');
                        tracker.HeatmapSessionRecording.disable();
                        tracker.HeatmapSessionRecording.hasReceivedConfig = true;
                        continue;
                    }

                    if (trackerUrl.substr(-1, 1) !== '/') {
                        trackerUrl += '/';
                    }

                    numRequestsSent++;

                    var url;

                    if (matchTrackerUrl) {
                        url = tracker.getCurrentUrl();
                    } else {
                        url = windowAlias.location.href;
                        try {
                            url = decodeURIComponent(url);
                        } catch (e) {
                            url = unescape(url);
                        }
                    }

                    trackerUrl += 'plugins/HeatmapSessionRecording/configs.php?idsite=' + encodeURIComponent(trackerIdSite) + '&trackerid=' + tracker.HeatmapSessionRecording.myId + '&url=' + encodeURIComponent(url);

                    var hsrIds = storage.getHsrConfigs(tracker);
                    for (var k = 0; k < hsrIds.length; k++) {
                        trackerUrl += '&hsr'+ encodeURIComponent(hsrIds[k].id) + '=' + encodeURIComponent(hsrIds[k].value);
                    }

                    (function (tracker) {
                        var script = documentAlias.createElement('script');
                        script.src = trackerUrl;
                        script.async = true;
                        script.defer = true;
                        script.onerror = function () {
                            // eg when using multiple trackers with different piwik instances but plugin is only installed on one piwik instance
                            tracker.HeatmapSessionRecording.disable();
                            tracker.HeatmapSessionRecording.hasReceivedConfig = true;
                            configuration.checkAllConfigsReceived();
                        };
                        // timeout may reduce the risk of delaying onload event
                        setTimeout(function () {
                            var head = documentAlias.getElementsByTagName('head');
                            if (head && head.length && head[0]) {
                                head[0].appendChild(script);
                            } else {
                                var body = documentAlias.getElementsByTagName('body');
                                if (body && body.length && body[0]) {
                                    body[0].appendChild(script);
                                }
                            }
                        }, 10);
                    })(tracker);
                }
            }

            if (numRequestsSent === 0) {
                // no tracking request was sent, check if we can maybe disable the recording
                this.checkAllConfigsReceived();
            }
        },
        assign: function (config) {
            var trackers = tracking.getPiwikTrackers();

            // we need to find to which tracker the config belongs and assign it to that tracker
            for (var i = 0; i < trackers.length; i++) {
                var tracker = trackers[i];
                if (tracker && tracker.HeatmapSessionRecording) {
                    if (tracker.getSiteId() == config.idsite
                        && tracker.HeatmapSessionRecording.myId === config.trackerid) {

                        tracker.HeatmapSessionRecording.addConfig(config);
                        break;
                    }
                }
            }

            this.checkAllConfigsReceived();
        },
        checkAllConfigsReceived: function () {
            // this is to detect if we can stop recording any actions as it will never start tracking initially
            // this way we save resources and increase website performance etc.

            var trackers = tracking.getPiwikTrackers();
            // we know we are not waiting for any particular config anymore and for better performance can
            // disable the recording
            var anyHeatmapConfigured = false;
            var anySessionConfigured = false;

            var hsr;
            for (var i = 0; i < trackers.length; i++) {
                if (trackers[i].HeatmapSessionRecording) {
                    hsr = trackers[i].HeatmapSessionRecording;

                    if (!hsr.hasReceivedConfig) {
                        // not all trackers have received their config yet so cannot decide yet if we can stop recording or not
                        // returning here is not a mistake but essential to not execute check below
                        return;
                    }

                    if (hsr.Heatmap.configs && hsr.Heatmap.configs.length) {
                        anyHeatmapConfigured = true;
                    }
                    if (hsr.Session.configs && hsr.Session.configs.length) {
                        anySessionConfigured = true;
                    }
                }
            }

            // there are none configured, so we stop the tracking so the website gets better performance and we need
            // less resources etc. less cpu usage etc. it doesn't mean we can track anything yet because they are only
            // configured but there might be a requirement like "requires_activity" or "min_session_time" that prevents
            // them from tracking in the end but we still need to record as those requirements might be met later
            if (!anyHeatmapConfigured && !anySessionConfigured) {
                Piwik.HeatmapSessionRecording.disable();
            } else if (!anySessionConfigured) {
                Piwik.DOM.onLoad(function () {
                    // we need to make sure this is executed after mutations were started. Otherwise we might stop now,
                    // and then onLoad we start recording (as this is happening by default)
                    recording.stopRecordingMutations();
                });
            }
        }
    };

    function init() {
        if ('object' === typeof window && 'object' === typeof windowAlias.Piwik && 'object' === typeof windowAlias.Piwik.HeatmapSessionRecording) {
            // do not initialize twice
            return;
        }

        if ('object' === typeof window && !windowAlias.Piwik) {
            // piwik is not defined yet
            return;
        }

        pJson = Piwik.JSON;

        Piwik.HeatmapSessionRecording = {
            utils: trackerUtils,
            element: element,
            storage: storage,
            dom: dom,
            tracking: tracking,
            recording: recording,
            RECORD_TYPE_BOTH: RECORD_TYPE_BOTH,
            RECORD_TYPE_HEATMAP: RECORD_TYPE_HEATMAP,
            RECORD_TYPE_SESSION: RECORD_TYPE_SESSION,
            configuration: configuration,
            getIdView: function () {
                return hsrIdView;
            },
            disableRecordMovements: function () {
                enableRecordMovements = false;
            },
            enableRecordMovements: function () {
                enableRecordMovements = true;
            },
            isRecordingMovements: function () {
                return enableRecordMovements;
            },
            disableAutoDetectNewPageView: function () {
                autoDetectNewPageviews = false;
            },
            enableAutoDetectNewPageView: function () {
                autoDetectNewPageviews = true;
            },
            isAutoDetectingNewPageViews: function () {
                return autoDetectNewPageviews;
            },
            matchTrackerUrl: function () {
                return matchTrackerUrl = true;
            },
            setTrigger: function (triggerMethod) {
                if (typeof triggerMethod === 'function') {
                    shouldTriggerRecording = triggerMethod;
                } else {
                    throw Error('trigger needs to be a method');
                }
            },
            setNewPageView: function (fetchConfig) {
                if (isExcluded()) {
                    // those methods are not compatible with the browser so we only execute it when browser is supported
                    return;
                }

                logConsoleMessage('new pageview');

                var isHsrEnabled = this.isEnabled();

                // now we stop any potential recording. This method will make sure to track remaining queued data
                if (isHsrEnabled) {
                    this.disable();
                }

                // we set a new view to make sure a new heatmap or recording will be logged.
                // We need to make sure this method is called after disable so the new hsrIdView won't be used when
                // sending the remaining tracking requests
                hsrIdView = trackerUtils.generateUniqueId();
                timeWhenPageReady = new Date().getTime();
                recordedDataBeforeTrackerSetup = []; // need to reset / clear this
                hasClicked = false;
                hasScrolled = false;

                // we need to re-initialize the tracker and unset all previously set configs, hsrids, data etc.
                // problem: there may be timeouts waiting...
                var trackers = tracking.getPiwikTrackers();
                trackers.forEach(function (tracker) {
                    var isEnabled = true;

                    if ('undefined' !== typeof tracker.HeatmapSessionRecording) {
                        // we remember isEnabled state for each tracker. This way eg the config wont be fetched again
                        // for a not supported Matomo etc and user won't have to call it again.
                        // note: we cannot use isEnabled() because we disabled tracking before and it would always
                        // return false. so we need to remember the state itself directly
                        isEnabled = tracker.HeatmapSessionRecording.enabled;
                        delete tracker.HeatmapSessionRecording;
                    }

                    enrichTracker(tracker);

                    if (!isEnabled) {
                        tracker.HeatmapSessionRecording.disable();
                    }
                });

                if (isHsrEnabled) {
                    // we enable the tracking again if it was enabled before
                    this.enable();

                    if ('undefined' === typeof fetchConfig || fetchConfig === true) {
                        // user might want to disable this if the user wants to avoid these requests and instead wants
                        // to configure it manually on the tracker
                        configuration.fetch();
                    } else if ('object' === typeof fetchConfig) {
                        trackers.forEach(function (tracker) {
                            tracker.HeatmapSessionRecording.addConfig(fetchConfig);
                        });
                    } else {
                        logConsoleMessage('manual tracker config required');
                    }
                }
            },
            disable: function () {
                if (isExcluded()) {
                    // those methods are not compatible with the browser so we only execute it when browser is supported
                    return;
                }

                isHsrEnabled = false;
                recording.stopRecording();
                recording.stopRecordingMutations();
                tracking.stopSendingData();
            },
            enable: function () {
                if (isExcluded()) {
                    // those methods are not compatible with the browser so we only execute it when browser is supported
                    return;
                }

                isHsrEnabled = true;
                recording.startRecording();
                recording.startRecordingMutations();
                // we always start sending data but that method will make sure to actually only start it once
                // and it will only send data if a tracker is actually supposed to send data
                // (there might be a gap between start sending data and actually tracking data)
                tracking.startSendingData();
            },
            isEnabled: function () {
                return isHsrEnabled;
            },
            setMaxCaptureTime: function (maxTimeInSeconds) {
                maxCapturingTimeStart = parseInt(maxTimeInSeconds, 10) * 1000;

                if (maxCapturingTimeStart > maxCapturingTimeEnd) {
                    // if user sets a custom value that is very long, we need to make sure to apply this also to the end max capture time
                    maxCapturingTimeStart = maxCapturingTimeEnd;
                }
            },
            setMaxTextInputLength: function (maxLengthCharacters) {
                maxLenTextInput = maxLengthCharacters;
            },
            disableCaptureKeystrokes: function () {
                recordKeystrokes = false;
            },
            enableCaptureKeystrokes: function () {
                recordKeystrokes = true;
            },
            setPiwikTrackers: function (trackers) {
                if (trackers === null) {
                    customPiwikTrackers = null;
                    return;
                }

                if (!trackerUtils.isArray(trackers)) {
                    trackers = [trackers];
                }

                customPiwikTrackers = trackers;
                // we make sure all trackers are enriched
                customPiwikTrackers.forEach(enrichTracker);
            },
            enableDebugMode: function () {
                debugMode = true;
            }
        };

        Piwik.DOM.onReady(function () {
            // we do not measure time since "script load" but since DOM ready, we should ignore any time past
            // before dom ready as user is not actually doing anything on the website as it is still "loading the dom".
            timeWhenPageReady = new Date().getTime();
        });

        Piwik.addPlugin('HeatmapSessionRecording', {
            log: function (eventParams) {
                if (autoDetectNewPageviews) {
                    if (eventParams.tracker && eventParams.tracker.getNumTrackedPageViews && eventParams.tracker.getNumTrackedPageViews() > 1) {
                        // we only recognize a pageview after the first tracking pageview as the first tracking pageview
                        // is already handled indirectly without needing to wait for this.
                        setTimeout(function () {
                            // we don't execute it directly and rather want the initial request to finish
                            Piwik.HeatmapSessionRecording.setNewPageView(true);
                        }, 10);
                    }
                }

                return '';
            },
            unload: function () {
                if (!isExcluded()) {
                    // We make sure it is a supported browser and can use the needed methods

                    // sendQueuedData will make sure to only send data when actually enabled
                    var trackers = tracking.getPiwikTrackers();

                    // prevent possible race conditions of starting 2 queued data at same time
                    tracking.stopSendingData();

                    trackers.forEach(function (tracker) {
                        var shouldEndRecording = true;
                        tracking.sendQueuedData(tracker, shouldEndRecording);
                    });
                }
            }
        });

        if (windowAlias.Piwik.initialized) {
            // tracker was separately loaded via separate include. we need to enrich already created trackers
            var asyncTrackers = Piwik.getAsyncTrackers();
            asyncTrackers.forEach(enrichTracker);

            Piwik.on('TrackerSetup', enrichTracker);

            // now that the methods are set on the tracker instance we check if there were calls that couldn't be executed
            // the first time because the form analytics plugin was not loaded yet (but it is now)
            Piwik.retryMissedPluginCalls();

            callAsyncReadyMethod();
            configuration.fetch();

        } else {
            Piwik.on('TrackerSetup', enrichTracker);

            Piwik.on('PiwikInitialized', function () {
                callAsyncReadyMethod();

                // at this point the first tracker was created, and all methods called by a user on _paq applied.
                // this means now we can start looking for form because if someone has disabled eg tracking events
                // or tracking progress or enabled debug etc we can be sure the form tracker has been configured

                configuration.fetch();
            });
        }
    }

    hsrIdView = trackerUtils.generateUniqueId();

    if ('object' === typeof windowAlias.Piwik) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof windowAlias.piwikPluginAsyncInit) {
            windowAlias.piwikPluginAsyncInit = [];
        }

        windowAlias.piwikPluginAsyncInit.push(init);
    }

})();
/* END GENERATED: tracker.js */


/* GENERATED: tracker.js */
/*!
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * All information contained herein is, and remains the property of InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

/**
 * To minify this version call
 * cat tracker.js | java -jar ../../js/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar --type js --line-break 1000 | sed 's/^[/][*]/\/*!/' > tracker.min.js
 */

(function () {
    var debugMode = false;
    var isFormAnalyticsEnabled = true;
    var customPiwikTrackers = null;

    var FIELD_CATEGORY_CHECK = 'FIELD_CHECKABLE';
    var FIELD_CATEGORY_SELECT = 'FIELD_SELECTABLE';
    var FIELD_CATEGORY_TEXT = 'FIELD_TEXT';

    var textFieldTypes = ['password', 'text', 'url', 'tel', 'email' , 'search', '', null];
    var selectFieldTypes = ['color', 'date', 'datetime', 'datetime-local', 'month' , 'number', 'range', 'time', 'week'];
    var checkFieldTypes = ['radio', 'checkbox'];
    var ignoreFieldTypes = ['button', 'submit', 'hidden', 'reset'];

    // when sending a tracking request, we wait that many ms before actually sending the tracking request so we can send several requests at once
    var trackRequestAfterMs = 2250;

    var formTrackerInstances = [];

    function logConsoleMessage() {
        if (debugMode && 'undefined' !== typeof console && console && console.debug) {
            console.debug.apply(console, arguments);
        }
    }

    var element = {
        getAttribute: function (node, attributeName) {
            if (node && node.getAttribute && attributeName) {
                return node.getAttribute(attributeName);
            }

            return null;
        },
        hasClass: function (node, className) {
            if (!node || !node.className) {
                return false;
            }

            return (' ' + node.className + ' ').indexOf(' ' + className + ' ') > -1;
        },
        hasNodeAttribute: function (node, attributeName) {
            if (node && node.hasAttribute) {
                return node.hasAttribute(attributeName);
            }

            if (node && node.attributes) {
                var theType = (typeof node.attributes[attributeName]);
                return theType !== 'undefined';
            }

            return false;
        },
        isIgnored: function (node) {
            if (this.hasNodeAttribute(node, 'data-matomo-ignore')) {
                return true;
            }
            if (this.hasNodeAttribute(node, 'data-piwik-ignore')) {
                return true;
            }
            return false;
        },
        getTagName: function (node) {
            if (node && node.tagName) {
                return ('' + node.tagName).toLowerCase();
            }

            return null;
        },
        findAllFormElements: function (element) {
            if (element && element.querySelectorAll) {
                return element.querySelectorAll('form, [data-piwik-form], [data-matomo-form]');
            }
            return [];
        },
        findAllFieldElements: function (element) {
            if (element && element.querySelectorAll) {
                return element.querySelectorAll('input,select,textarea,button,textarea');
            }
            return [];
        },
        findFormTrackerInstance: function (node, maxLevels) {
            if ('undefined' === typeof maxLevels) {
                maxLevels = 100;
            }

            if (maxLevels <= 0 || !node) {
                return null;
            }

            if (node.formTrackerInstance) {
                return node.formTrackerInstance;
            }

            if (node.parentNode) {
                return this.findFormTrackerInstance(node.parentNode, --maxLevels);
            }
        }
    };

    var utils = {
        isArray: function (variable) {
            return typeof variable === 'object' && variable !== null && typeof variable.length === 'number';
        },
        indexOfArray: function (anArray, element) {
            if (!anArray) {
                return -1;
            }

            if (anArray.indexOf) {
                return anArray.indexOf(element);
            }

            if (!this.isArray(anArray)) {
                return -1;
            }

            for (var i = 0; i < anArray.length; i++) {
                if (anArray[i] === element) {
                    return i;
                }
            }

            return -1;
        },
        getCurrentTime: function () {
            return new Date().getTime();
        },
        isNumber: function (text) {
            return !isNaN(text);
        },
        generateUniqueId: function () {
            var id = '';
            var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charLen = chars.length;

            for (var i = 0; i < 6; i++) {
                id += chars.charAt(Math.floor(Math.random() * charLen));
            }

            return id;
        },
        paramsToQueryString: function (params) {
            if (!params) {
                params = {};
            }

            var requestUrl = '';
            for (var index in params) {
                if (Object.prototype.hasOwnProperty.call(params, index)) {
                    if (params[index] === null) {
                        continue;
                    }

                    requestUrl += index + '=' + encodeURIComponent(params[index]) + '&';
                }
            }

            return requestUrl;
        }
    };

    var tracking = {
        getPiwikTrackers: function ()
        {
            if (null === customPiwikTrackers) {
                if ('object' === typeof Piwik && Piwik.getAsyncTrackers) {
                    return Piwik.getAsyncTrackers();
                }
            }

            if (utils.isArray(customPiwikTrackers)) {
                return customPiwikTrackers;
            }

            return [];
        },
        trackParams: function (params, increaseTimeout) {
            if (!isFormAnalyticsEnabled) {
                return;
            }

            var requestUrl = utils.paramsToQueryString(params);

            if (!requestUrl || requestUrl === '') {
                return;
            }

            var asyncTrackers = this.getPiwikTrackers();

            if (asyncTrackers && asyncTrackers.length) {
                var i = 0, tracker;

                for (i; i < asyncTrackers.length; i++) {
                    tracker = asyncTrackers[i];
                    if (increaseTimeout && 500 === tracker.getLinkTrackingTimer() && tracker.setLinkTrackingTimer) {
                        // if it still has the default value, slightly increase timeout to make sure we can handle eg form submit
                        // request
                        tracker.setLinkTrackingTimer(650);
                    }

                    if (tracker && (!tracker.FormAnalytics || tracker.FormAnalytics.isEnabled())) {
                        tracker.trackRequest(requestUrl, null, null, 'FormAnalytics');
                    }
                }
            }

            if (debugMode) {
                // check for debug mode is not needed here but we do want to perform the stringify only when needed
                logConsoleMessage('trackProgress: ' + Piwik.JSON.stringify(params));
            }
        }
    };

    function callAsyncReadyMethod()
    {
        if (typeof window === 'object' && 'function' === typeof window.piwikFormAnalyticsAsyncInit) {
            window.piwikFormAnalyticsAsyncInit();
        }
    }

    function FormTracker(node) {
        this.reset();

        this.fields = [];
        this.firstFieldEngagementDate = null;
        this.lastFieldEngagementDate = null;
        this.hesitationTimeTracked = false;
        this.formStartTracked = false;
        this.node = node;
        this.formId = element.getAttribute(node, 'id');
        this.formName = element.getAttribute(node, 'data-matomo-name'); // name can be overwritten by data-matomo-name
        if (!this.formName) {
            this.formName = element.getAttribute(node, 'data-piwik-name'); // name can be overwritten by data-piwik-name
        }
        if (!this.formName) {
            this.formName = element.getAttribute(node, 'name');
        }

        this.entryFieldName = '';
        this.exitFieldName = '';
        this.lastFocusedFieldName = ''; // we update this one always on focus
        this.fieldsWithUpdates = [];
        this.fieldNodes = []; // we use this to know which form fields have been added to this form already
        this.initialFormViewLoggedWithTrackers = [];
        this.trackingTimeout = null;
        this.timeLastTrackingRequest = 0; // used to calculate time spent on form, we are ignoring any time while form was active
        this.timeOffWindowBeforeEngagement = 0; // we do not count the time into account when window wasn't active
        // we never send the total time spent, we only send the amount of ms that was spent on the form since the last
        // tracking event. So we calculate always the total time spent, and remove the amount of already tracked spent time.
        this.timeOffWindowSinceEngagement = 0; // we do not count the time into account when window wasn't active

        Piwik.DOM.addEventListener(window, 'focus', (function (that) {
            return function () {
                if (!that.timeWindowBlur) {
                    return;
                }

                var timeWindowOff = utils.getCurrentTime() - that.timeWindowBlur;
                that.timeWindowBlur = null;
                // we unset it again to make sure next time it works only when we also captured blur

                if (timeWindowOff < 0) {
                    timeWindowOff = 0;
                }

                if (that.timeLastTrackingRequest) {
                    // we need to make sure to ignore if window was off after last tracking request
                    that.timeLastTrackingRequest = that.timeLastTrackingRequest + timeWindowOff;
                }

                if (that.firstFieldEngagementDate) {
                    that.timeOffWindowSinceEngagement += timeWindowOff;
                    logConsoleMessage('time off engaged ' + that.timeOffWindowSinceEngagement);
                } else {
                    that.timeOffWindowBeforeEngagement += timeWindowOff;
                    logConsoleMessage('time off not engaged ' + that.timeOffWindowBeforeEngagement);
                }
            };
        })(this));
        Piwik.DOM.addEventListener(window, 'blur', (function (that) {
            return function () {
                that.timeWindowBlur = utils.getCurrentTime();
                logConsoleMessage('window blur');
            };
        })(this));

        Piwik.DOM.addEventListener(node, 'submit', (function (that) {
            return function () {
                logConsoleMessage('form submit');
                that.trackFormSubmit();
            };
        })(this));
    }

    FormTracker.prototype.reset = function () {
        this.detectionDate = utils.getCurrentTime();
        this.formViewId = utils.generateUniqueId();
        this.fieldsWithUpdates = [];
        this.firstFieldEngagementDate = null;
        this.lastFieldEngagementDate = null;
        this.timeOffWindowSinceEngagement = 0;
        this.timeOffWindowBeforeEngagement = 0;
        this.formStartTracked = false; // if user engages again, we will count a new form start...

        if (this.fields && this.fields.length) {
            for (var i = 0; i < this.fields.length; i++) {
                this.fields[i].resetOnFormSubmit();
            }
        }
    };

    FormTracker.prototype.trackFormSubmit = function () {

        this.setEngagedWithForm();

        var timeToSubmit = this.lastFieldEngagementDate - this.firstFieldEngagementDate - this.timeOffWindowSinceEngagement;
        if (timeToSubmit < 0) {
            timeToSubmit = 0;
        }

        var params = {
            fa_su: 1, // marks it as form submission
            fa_tts: timeToSubmit // time to submit form
        };

        this.sendUpdate(this.fields, params, true);

        this.reset();
    };

    FormTracker.prototype.trackFormConversion = function () {
        if (!this.timeLastTrackingRequest) {
            // no timeout needed, we can send the request directly as nothing else has been tracked yet
            this.sendUpdate([], {fa_co: 1});
            return;
        }

        var secondsSinceLastTrackingRequest = (utils.getCurrentTime() - this.timeLastTrackingRequest) / 1000;

        if (secondsSinceLastTrackingRequest < 2) {
            // we need to give the previous request a bit of time to process in case the submit contains information
            // about form_start and time_spent on form. otherwise there could be a race condition when filling out
            // the form very quick and then calling submit and conversion at the same time. the conversion would not
            // find a form with time spent on because the other request that contains this information is only just
            // being processed at the same time
            var self = this;
            setTimeout(function () {
                self.sendUpdate([], {fa_co: 1});
            }, 800);
        } else {
            // if there was no tracking request in the last 2 seconds we want to send this request as quick as possible
            // in case the user leaves the page again
            this.sendUpdate([], {fa_co: 1});
        }
    };

    FormTracker.prototype.shouldBeTracked = function () {
        return !!this.fields && !!this.fields.length;
    };

    FormTracker.prototype.trackInitialFormView = function () {
        if (!this.initialFormViewLoggedWithTrackers || !this.initialFormViewLoggedWithTrackers.length) {
            this.initialFormViewLoggedWithTrackers = tracking.getPiwikTrackers();
            this.sendUpdate([], {fa_fv: '1'});
            // fa_fv => 1 = form view. we do currently not track a new form view after a form submit
            // (eg if page does not reload). Possibly we should though in the future as when someone does a page reload
            // after a form submit we would track a form view and a new form start under circumstances and it would be
            // good to track this consistent no matter if it is an ajax form or not. Something to see later.
        }
    };

    FormTracker.prototype.setEngagedWithForm = function (hasChangedForm) {
        this.lastFieldEngagementDate = utils.getCurrentTime();

        if (!this.firstFieldEngagementDate) {
            this.firstFieldEngagementDate = this.lastFieldEngagementDate;
        }
    };

    FormTracker.prototype.trackFieldUpdate = function (field) {
        if (utils.indexOfArray(this.fieldsWithUpdates, field) === -1) {
            this.fieldsWithUpdates.push(field);
        }

        this.scheduleSendUpdate();
    };

    FormTracker.prototype.scheduleSendUpdate = function () {
        if (this.trackingTimeout) {
            clearTimeout(this.trackingTimeout);
            this.trackingTimeout = null;
        }

        var self = this;
        this.trackingTimeout = setTimeout(function () {
            var fields = self.fieldsWithUpdates;
            self.fieldsWithUpdates = [];
            self.sendUpdate(fields);
        }, trackRequestAfterMs);
    };

    FormTracker.prototype.sendUpdate = function (fieldsToTrack, extraParams, increaseTimeout)
    {
        if (!this.shouldBeTracked()) {
            // we make sure to not track anything if there are no visible fields registered
            return;
        }

        if (this.trackingTimeout) {
            clearTimeout(this.trackingTimeout);
            this.trackingTimeout = null;
        }

        if (!fieldsToTrack) {
            fieldsToTrack = [];
        }

        var fields = [];
        for (var i = 0; i < fieldsToTrack.length; i++) {
            fields.push(fieldsToTrack[i].getTrackingParams());
        }

        var params = {
            fa_vid: this.formViewId,
            fa_id: this.formId,
            fa_name: this.formName
        };

        if (this.entryFieldName) {
            params.fa_ef = this.entryFieldName;
        }

        if (this.exitFieldName) {
            params.fa_lf = this.exitFieldName;
        }

        if (fields.length) {
            params.fa_fields = Piwik.JSON.stringify(fields);
        }

        if (this.firstFieldEngagementDate) {

            // we only want to track any time spent or hesitation time as soon as a user has engaged with it
            if (!this.formStartTracked) {
                // we do currently not track a new form view and not a new form start after a form submit
                // (eg if page does not reload). Possibly we should though in the future as when someone does a page reload
                // after a form submit we would track a form view and a new form start under circumstances and it would be
                // good to track this consistent no matter if it is an ajax form or not.
                params.fa_st = '1';
                this.formStartTracked = true;
            }

            if (!this.hesitationTimeTracked) {
                // we only want to track this once as soon as
                params.fa_ht = this.firstFieldEngagementDate - this.detectionDate - this.timeOffWindowBeforeEngagement;
                this.hesitationTimeTracked = true;
            }

            if (this.lastFieldEngagementDate && this.timeLastTrackingRequest) {
                // we only send time spent once user has actually engaged with it
                params.fa_ts = this.lastFieldEngagementDate - this.timeLastTrackingRequest;
                if (params.fa_ts < 0) {
                    params.fa_ts = 0; // eg if form was not engaged with since last tracking request
                }
            } else if (this.lastFieldEngagementDate && !this.timeLastTrackingRequest) {
                // on first tracking request since engagement
                params.fa_ts = this.lastFieldEngagementDate - this.firstFieldEngagementDate - this.timeOffWindowSinceEngagement;
                if (params.fa_ts < 0) {
                    params.fa_ts = 0; // eg if window was off / blurred for a long time after the last engagement
                }
            }

            // it is important we set this only once engaged so far
            this.timeLastTrackingRequest = utils.getCurrentTime();
        }

        if (extraParams) {
            for (var j in extraParams) {
                if (Object.prototype.hasOwnProperty.call(extraParams, j)) {
                    params[j] = extraParams[j];
                }
            }
        }

        if ('undefined' === typeof increaseTimeout) {
            increaseTimeout = false;
        }

        tracking.trackParams(params, increaseTimeout);
    };

    FormTracker.prototype.scanForFields = function() {
        var i, j = 0, field, fields, node;

        fields = element.findAllFieldElements(this.node);

        for (i = 0; i < fields.length; i++) {
            if (!fields[i]) {
                continue;
            }

            node = fields[i];

            if (element.isIgnored(node) || utils.indexOfArray(this.fieldNodes, node) > -1) {
                continue;
            }

            var tagName = element.getTagName(node);
            var fieldType = element.getAttribute(node, 'type');

            if (utils.indexOfArray(ignoreFieldTypes, fieldType) !== -1) {
                continue;
            } else if ('button' === tagName) {
                continue;
            }

            if (tagName === 'input' && !fieldType) {
                fieldType = 'text';
            }

            var fieldName = element.getAttribute(node, 'data-matomo-name');
            if (!fieldName) {
                fieldName = element.getAttribute(node, 'data-piwik-name');
                if (!fieldName) {
                    fieldName = element.getAttribute(node, 'name');

                    if (!fieldName) {
                        fieldName = element.getAttribute(node, 'id');

                        if (!fieldName) {
                            continue; // no name found, we ignore it
                        }
                    }
                }
            }

            this.fieldNodes.push(node);

            var found = false;
            for (j = 0; j < this.fields.length; j++) {
                if (this.fields[j] && this.fields[j].fieldName === fieldName) {
                    found = true;
                    // for radio and checkboxes we need to group some fields together
                    this.fields[j].addNode(node);
                    break;
                }
            }

            if (!found) {
                field = new FormField(this, fields[i], tagName, fieldType, fieldName);
                this.addFormField(field);
            }
        }
    };

    FormTracker.prototype.addFormField = function (field)
    {
        this.fields.push(field);
    };

    function FormField(tracker, node, tagName, fieldType, fieldName) {
        this.discoveredDate = utils.getCurrentTime();
        this.tracker = tracker;
        this.timespent = 0;
        this.hesitationtime = 0;
        this.nodes = [];
        this.tagName = tagName;
        this.fieldName = fieldName;
        this.fieldType = fieldType;
        this.startFocus = null; // timestamp last focused
        this.timeLastChange = null; // timestamp last changed which is set when field has focus and it is being changed
        this.numChanges = 0; // number of total changes after a different field had the focus
        this.numFocus = 0; // number of total focuses after a different field had the focus
        this.numDeletes = 0; // number of times a user pressed back or delete key
        this.numCursor = 0; // number of times a user used arrow keys
        this.canCountChange = true; // to make sure we can count a change only once per focus
        this.isFocusedCausedAuto = element.hasNodeAttribute(node, 'autofocus');

        if (this.tagName === 'select') {
            this.category = FIELD_CATEGORY_SELECT;
        } else if (this.tagName === 'textarea') {
            this.category = FIELD_CATEGORY_TEXT;
        } else if (utils.indexOfArray(checkFieldTypes, this.fieldType) !== -1) {
            this.category = FIELD_CATEGORY_CHECK;
        } else if (utils.indexOfArray(selectFieldTypes, this.fieldType) !== -1) {
            this.category = FIELD_CATEGORY_SELECT;
        } else {
            this.category = FIELD_CATEGORY_TEXT;
        }

        this.addNode(node);

        var isFocusedInitially = (node === document.activeElement);

        if (isFocusedInitially) {
            this.onFocus();
        }
    }

    FormField.prototype.addNode = function (node) {
        this.nodes.push(node);

        function addEvent(node, eventName, callback) {
            if (node
                && 'object' === typeof tinymce
                && 'function' === typeof tinymce.get
                && element.getTagName(node) === 'textarea'
                && element.getAttribute(node, 'id')) {
                var id = element.getAttribute(node, 'id');
                var editor = tinymce.get(id);
                if (editor) {
                    editor.on(eventName, callback);
                    return;
                }
            } else if (node
                && 'function' === typeof jQuery
                && element.getTagName(node) === 'select'
                && element.hasClass(node, 'select2-hidden-accessible')
                && node.nextSibling
            ) {
                if (eventName === 'focus') {
                    eventName = 'select2:open';
                } else if (eventName === 'blur') {
                    eventName = 'select2:close';
                }
                jQuery(node).on(eventName, callback);
                return;
            }

            Piwik.DOM.addEventListener(node, eventName, callback);
        }

        addEvent(node, 'focus', (function (that) {
            return function (event) {
                // if focus happens after page was loaded, we can be quite sure it wasn't autofocused.
                // when another field had the focus before, it cannot be an autofocus
                if (that.isAutoFocus()) {
                    logConsoleMessage('field autofocus ' + that.fieldName);
                } else {
                    logConsoleMessage('field focus ' + that.fieldName);
                }

                that.onFocus();
            };
        })(this));

        addEvent(node, 'blur', (function (that) {
            return function () {
                logConsoleMessage('field blur ' + that.fieldName);
                that.onBlur();
            };
        })(this));

        if (this.category === FIELD_CATEGORY_TEXT) {
            addEvent(node, 'keyup', (function (that) {
                return function (event) {
                    var key = event.which || event.keyCode;
                    var metaKeysIgnore = [9, 16, 17, 18, 20, 27, 91];
                    if ((key && utils.indexOfArray(metaKeysIgnore, key) !== -1) || event.isCtrlKey) {
                        return;
                    }
                    if (key >= 37 && key <= 40) {
                        if (!that.isBlank()) {
                            // we count cursors only once there is actually a value in the field
                            that.numCursor++;
                            that.tracker.trackFieldUpdate(that);
                        }
                        return;
                    }
                    if (key == 8 || key == 46) {
                        // used backspace or delete key
                        if (!that.isBlank()) {
                            // we count deletes only once there is actually a value in the field
                            that.numDeletes++;
                            that.tracker.trackFieldUpdate(that);
                        }
                        return;
                    }

                    logConsoleMessage('field text keyup ' + that.fieldName);
                    that.onChange();
                };
            })(this));

            addEvent(node, 'paste', (function (that) {
                return function () {
                    logConsoleMessage('field text paste ' + that.fieldName);
                    that.onChange();
                };
            })(this));
        } else {
            addEvent(node, 'change', (function (that) {
                // it would be great to listen to change events for text fields as well, however, the change event
                // may be triggered on blur, which means we cannot differentiate between blur / change and eg you type
                // something, then wait for 10 seconds to leave the field, we would not want to count those 10seconds
                // into time spent but the change event would be triggered after those 10 seconds and we would count it
                return function () {
                    logConsoleMessage('field change ' + that.fieldName);
                    that.onChange();
                };
            })(this));
        }
    };

    FormField.prototype.resetOnFormSubmit = function () {
        this.hesitationtime = 0;
        this.timespent = 0;
        this.numFocus = 0;
        this.numDeletes = 0;
        this.numCursor = 0;
        this.numChanges = 0;
        this.startFocus = null;
        this.timeLastChange = null;
        this.canCountChange = true;
        this.hasChangedValueSinceFocus = false;
        this.isFocusedCausedAuto = false; // after a submit it can be no longer caused by auto focus
    };

    FormField.prototype.isAutoFocus = function () {

        if (!this.isFocusedCausedAuto) {
            return false;
        }

        // as soon as another field that a focus, it cannot be caused by autofocus anymore
        if (this.tracker.entryFieldName && this.tracker.entryFieldName !== this.fieldName) {
            this.isFocusedCausedAuto = false;
        }

        // a different field had focus before, it cannot be caused by auto focus
        if (this.tracker.exitFieldName && this.tracker.exitFieldName !== this.fieldName) {
            this.isFocusedCausedAuto = false;
        }

        return this.isFocusedCausedAuto;
    };

    FormField.prototype.getTrackingParams = function () {
        return {
            fa_fts: this.getTimeSpent(),
            fa_fht: this.getHesitationTime(),
            fa_fb: this.isBlank(),
            fa_fn: this.fieldName,
            fa_fch: this.numChanges,
            fa_ff: this.numFocus,
            fa_fd: this.numDeletes,
            fa_fcu: this.numCursor,
            fa_ft: this.fieldType || this.tagName,
            fa_fs: this.getFieldSize()
        };
    };

    FormField.prototype.isBlank = function () {
        if (this.category === FIELD_CATEGORY_CHECK) {
            for (var i = 0; i < this.nodes.length; i++) {
                if (this.nodes[i] && this.nodes[i].checked) {
                    return true;
                }
            }
            return false;
        }

        if (!this.nodes[0]) {
            return false;
        }

        var node = this.nodes[0];

        if ('undefined' === typeof node.value) {
            return true;
        }

        var value = node.value;

        if (null === value || false === value || '' === value) {
            return true;
        }

        return String(value).length === 0;
    };

    FormField.prototype.getFieldSize = function () {
        if (this.category === FIELD_CATEGORY_TEXT) {
            if (this.nodes[0] && this.nodes[0].value) {
                return String(this.nodes[0].value).length;
            } else {
                return 0;
            }
        } else {
            return -1;
        }
    };

    FormField.prototype.getTimeSpent = function () {
        if (this.numChanges && !this.timeSpent) {
            // we make sure to log at least 1ms if there was a change but no actual time was spent
            // as it is kind of required behaviour for logaggregator which assumes when there was a change,
            // there was also time spent on it
            this.timeSpent = 1;
        }

        if (!this.startFocus || this.isAutoFocus()) {
            // the field does currently had not have focus, so we use that value
            return this.timespent;
        }

        // field has currently focus and likely the time spent is not yet updated, we need to calculate it dynamically

        if (this.timeLastChange) {
            // the field currently has focus and it was changed at least once. When the field was changed we only want
            // to calculate the time to the last change, not the time to the blur since the user might just not "unfocus"
            // the field
            var diff = this.timeLastChange - this.startFocus;
            if (diff < 0) {
                diff = 0;
            }
            return this.timespent + diff;
        }

        // the field has currently focus but not has been changed yet
        return this.timespent + utils.getCurrentTime() - this.startFocus;
    };

    FormField.prototype.getHesitationTime = function () {

        if (this.numChanges || !this.startFocus || this.isAutoFocus()) {
            // only if there were no changes to it so far. Then user has already interacted with it
            return this.hesitationtime;
        }

        // field has currently focus and likely the hesitation time is not yet updated, we need to calculate it dynamically
        var now = utils.getCurrentTime();

        return this.hesitationtime + (now - this.startFocus);
    };

    FormField.prototype.onFocus = function () {
        this.startFocus = utils.getCurrentTime();

        var hadDifferentFieldFocusBefore = this.fieldName !== this.tracker.lastFocusedFieldName;

        if (hadDifferentFieldFocusBefore && this.tracker.lastFocusedFieldName) {
            // cannot be caused by auto focus since different field was focused before
            // (on first focus lastFocusedFieldName) may be empty so we need to make sure a value was set
            this.isFocusedCausedAuto = false;
        }

        this.timeLastChange = null;
        this.hasChangedValueSinceFocus = false;
        this.tracker.lastFocusedFieldName = this.fieldName;

        if (hadDifferentFieldFocusBefore) {
            this.canCountChange = true;
        }
        
        if (hadDifferentFieldFocusBefore && !this.isAutoFocus()) {
            this.numFocus++;

            this.tracker.setEngagedWithForm();
            this.tracker.trackFieldUpdate(this);

            // we track it only as exit field if the user actually engaged with a field, but not if field gets
            // autofocus
            this.tracker.exitFieldName = this.fieldName;

            // we only schedule an update if something actually changed
            this.tracker.scheduleSendUpdate();
        }
    };

    FormField.prototype.onBlur = function () {

        if (!this.startFocus) {
            return;
        }

        if (this.hasChangedValueSinceFocus) {
            // timespent and hesitation time was already measured on change event. we do not want do track any time
            // after a select change to the select blur event as it is not relevant here.
            if (this.timeLastChange && this.startFocus) {
                this.timespent += (this.timeLastChange - this.startFocus);
            }

            this.timeLastChange = null;
            this.startFocus = null;
            return;
        }

        if (!this.isAutoFocus()) {
            var now = utils.getCurrentTime();
            this.timespent += now - this.startFocus;

            if (!this.numChanges) {
                this.hesitationtime += now - this.startFocus;
            }

            // user has not updated a value, but kept focus for a while
            this.tracker.setEngagedWithForm();
            this.tracker.trackFieldUpdate(this);
        }

        this.startFocus = null;
    };

    FormField.prototype.onChange = function () {

        this.timeLastChange = utils.getCurrentTime();

        if (this.isAutoFocus()) {
            // we need to update the start focus time since it was automatically focussed and time spent wouldn't be
            // 100% accurate when using the auto focus start date. Instead we update it manually once on the first key
            // press
            this.startFocus = this.timeLastChange;
        } else if (!this.startFocus) {
            return; // currently not focussed!
        }

        // after a text change we ignore future focus events as being auto since they will be from then triggered by user
        this.isFocusedCausedAuto = false;
        this.hasChangedValueSinceFocus = true;

       if (!this.numChanges) {
            // when we go in here the first change has been made. we need to set the hestiation time and it will keep
            // always having this hesitation time afterwards since it is time from first focus to first change
            this.hesitationtime += this.timeLastChange - this.startFocus;
        }

        if (this.canCountChange) {
            // we only count a change once during a focus
            this.numChanges++;
            this.canCountChange = false;
        }

        if (!this.tracker.entryFieldName) {
            // we could an entry field only if a value was changed
            this.tracker.entryFieldName = this.fieldName;
        }

        this.tracker.setEngagedWithForm();
        this.tracker.trackFieldUpdate(this);
    };

    function addForm(node, sendInitialTrackingRequest)
    {
        if (!isFormAnalyticsEnabled) {
            return;
        }

        if (!document.querySelectorAll) {
            // this browser is not supported
            return;
        }

        var tracker;
        if (node && node.formTrackerInstance) {
            tracker = node.formTrackerInstance;
            tracker.scanForFields();
        } else if (!element.isIgnored(node)) {
            tracker = new FormTracker(node);
            tracker.scanForFields();
            formTrackerInstances.push(tracker); // we need to keep a list for unload event
            node.formTrackerInstance = tracker;
        }

        if (sendInitialTrackingRequest && tracker && tracker.shouldBeTracked()) {
            tracker.trackInitialFormView();
        }

        return tracker;
    }

    function scanForForms(elementOrDocument)
    {
        if ('undefined' === typeof elementOrDocument) {
            elementOrDocument = document;
        }

        var forms = element.findAllFormElements(elementOrDocument);

        for (var i = 0; i < forms.length; i++) {
            addForm(forms[i], true);
        }
    }

    function startScanningForForms()
    {
        var trackers = tracking.getPiwikTrackers();

        if (!trackers || !utils.isArray(trackers) || !trackers.length) {
            // no single tracker has been created yet. We do not automatically scan for forms as a user might only
            // later create a tracker
            return;
        }

        Piwik.DOM.onReady(function () {
            scanForForms(document);
        });
        Piwik.DOM.onLoad(function () {
            scanForForms(document);
        });
    }

    function enrichTracker(tracker)
    {
        if ('undefined' !== typeof tracker.FormAnalytics) {
            return;
        }

        tracker.FormAnalytics = {
            enabled: true,
            enable: function () {
                this.enabled = true;
            },
            disable: function () {
                this.enabled = false;
            },
            isEnabled: function () {
                return isFormAnalyticsEnabled && this.enabled;
            }
        };
    }

    function init() {
        if ('object' === typeof window && 'object' === typeof window.Piwik && 'object' === typeof window.Piwik.FormAnalytics) {
            // do not initialize form analytics twice
            return;
        }

        if ('object' === typeof window && !window.Piwik) {
            // piwik is not defined yet
            return;
        }

        Piwik.FormAnalytics = {
            element: element,
            utils: utils,
            tracking: tracking,
            FormField: FormField,
            FormTracker: FormTracker,
            disableFormAnalytics: function () {
                isFormAnalyticsEnabled = false;
            },
            enableFormAnalytics: function () {
                isFormAnalyticsEnabled = true;
            },
            isFormAnalyticsEnabled: function () {
                return isFormAnalyticsEnabled;
            },
            setPiwikTrackers: function (trackers) {
                if (trackers === null) {
                    customPiwikTrackers = null;
                    return;
                }

                if (!utils.isArray(trackers)) {
                    trackers = [trackers];
                }

                customPiwikTrackers = trackers;
            },
            setTrackingTimer: function (delay) {
                if (delay < 0) {
                    throw new Error('Delay needs to be at least zero');
                }
                trackRequestAfterMs = parseInt(delay, 10);
            },
            enableDebugMode: function () {
                debugMode = true;
            },
            scanForForms: scanForForms,
            trackFormSubmit: function (node) {
                var tracker = element.findFormTrackerInstance(node);

                if (tracker) {
                    tracker.trackFormSubmit();
                }
            },
            trackFormConversion: function (nodeOrFormName, formId) {
                if ('string' === typeof nodeOrFormName || 'string' === typeof formId) {
                    // TODO: later we need to optionally also add a pageURL parameter as they might configure their
                    // form to only be matched by page URL
                    tracking.trackParams({fa_vid: utils.generateUniqueId(),
                        fa_id: formId,
                        fa_name: nodeOrFormName, fa_co: 1});

                    return;
                }

                var tracker = element.findFormTrackerInstance(nodeOrFormName);

                if (tracker) {
                    tracker.trackFormConversion();
                }
            },
            trackForm: function (node) {
                return addForm(node, true);
            }
        };

        Piwik.addPlugin('FormAnalytics', {
            log: function (eventParams) {
                if (!isFormAnalyticsEnabled || !eventParams || !eventParams.tracker) {
                    return '';
                }

                var trackerInstance = eventParams.tracker;

                if (trackerInstance.FormAnalytics && !trackerInstance.FormAnalytics.isEnabled()) {
                    return '';
                }

                var forms = element.findAllFormElements(document);

                var requestParams = '';
                for (var i = 0; i < forms.length; i++) {
                    var formTracker = addForm(forms[i], false);

                    if (formTracker
                        && formTracker.shouldBeTracked()
                        && utils.indexOfArray(formTracker.initialFormViewLoggedWithTrackers, trackerInstance) === -1) {
                        formTracker.initialFormViewLoggedWithTrackers.push(trackerInstance);

                        if (formTracker.formViewId !== null) {
                            requestParams += '&fa_fp[' + i + '][fa_vid]=' + encodeURIComponent(formTracker.formViewId);
                        }
                        if (formTracker.formId !== null) {
                            requestParams += '&fa_fp[' + i + '][fa_id]=' + encodeURIComponent(formTracker.formId);
                        }
                        if (formTracker.formName !== null) {
                            requestParams += '&fa_fp[' + i + '][fa_name]=' + encodeURIComponent(formTracker.formName);
                        }
                        requestParams += '&fa_fp[' + i + '][fa_fv]=1';
                    }
                }

                if (requestParams) {
                    logConsoleMessage('sending request with pageview' + requestParams);
                    return '&fa_pv=1' + requestParams;
                }

                return '';
            },
            unload: function () {
                var tracker;
                for (var i = 0; i < formTrackerInstances.length; i++) {
                    tracker = formTrackerInstances[i];

                    if (tracker && tracker.trackingTimeout) {
                        logConsoleMessage('before unload');
                        clearTimeout(tracker.trackingTimeout);
                        tracker.sendUpdate(tracker.fieldsWithUpdates, {}, true);
                    }
                }
            }
        });

        if (window.Piwik.initialized) {
            Piwik.on('TrackerSetup', enrichTracker);

            // now that the methods are set on the tracker instance we check if there were calls that couldn't be executed
            // the first time because the form analytics plugin was not loaded yet (but it is now)
            Piwik.retryMissedPluginCalls();

            callAsyncReadyMethod();
            startScanningForForms();

        } else {
            Piwik.on('TrackerSetup', enrichTracker);

            Piwik.on('PiwikInitialized', function () {
                callAsyncReadyMethod();

                // at this point the first tracker was created, and all methods called by a user on _paq applied.
                // this means now we can start looking for form because if someone has disabled eg tracking events
                // or tracking progress or enabled debug etc we can be sure the form tracker has been configured
                startScanningForForms();
            });
        }
    }

    if ('object' === typeof window.Piwik) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof window.piwikPluginAsyncInit) {
            window.piwikPluginAsyncInit = [];
        }

        window.piwikPluginAsyncInit.push(init);
    }

})();
/* END GENERATED: tracker.js */


/* GENERATED: tracker.js */
/*!
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * All information contained herein is, and remains the property of InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

/**
 * To minify this version call
 * cat tracker.js | java -jar ../../js/yuicompressor-2.4.7/build/yuicompressor-2.4.7.jar --type js --line-break 1000 | sed 's/^[/][*]/\/*!/' > tracker.min.js
 */

(function () {

    var NAME_ORIGINAL_VARIATION = 'original';
    var debugMode = false;

    var storageNamespace = 'PiwikAbTesting';

    function logConsoleMessage() {
        if (debugMode && 'undefined' !== typeof console && console && console.debug) {
            console.debug.apply(console, arguments);
        }
    }

    function throwError(message) {
        logConsoleMessage(message);

        if (typeof Experiment !== 'undefined' && Experiment && Experiment.THROW_ERRORS) {
            throw new Error(message);
        }
    }

    var utils = {
        getRandomNumber: function (min, max) {
            return parseInt(Math.round(Math.random() * (max - min) + min, 10));
        },
        hasLocalStorage: function() {
            if (typeof localStorage === 'undefined') {
                return false;
            }

            var uid = new Date();
            var result;
            try {
                localStorage.setItem(uid, uid);
                result = localStorage.getItem(uid) == uid;
                localStorage.removeItem(uid);
                return result && localStorage && typeof JSON === 'object' && typeof JSON.parse === 'function';
            } catch (e) {
                return false;
            }
        },
        decodeSafe: function (text) {
            try {
                return window.decodeURIComponent(text);
            } catch (e) {
                return window.unescape(text);
            }
        },
        getQueryParameter: function (search, parameter) {
            search = ('' + search).toLowerCase();
            parameter = ('' + parameter).toLowerCase();

            var regexp = new RegExp('[?&]' + parameter + '(=([^&#]*)|&|#|$)', 'i');
            var matches = regexp.exec(search);

            if (!matches) {
                return null;
            }

            if (!matches[2]) {
                return '';
            }

            var value = matches[2].replace(/\+/g, " ");

            return this.decodeSafe(value);
        },
        removeQueryAndHashFromUrl: function (url) {
            var posHash = url.indexOf('#');
            if (posHash !== -1) {
                url = url.substr(0, posHash);
            }

            var posQuery = url.indexOf('?');
            if (posQuery !== -1) {
                url = url.substr(0, posQuery);
            }

            return url;
        },
        removeProtocol: function (url) {
            var posHash = ('' + url).indexOf('://');
            if (posHash !== -1 && posHash < 9) {
                return url.substr(posHash);
            }

            return url;
        },
        removeWwwSubdomain: function (url) {
            return ('' + url).replace('://www.', '://');
        },
        getVariationTest: function (location) {
            if (location && location.search) {
                var testVariation = utils.getQueryParameter(location.search, 'pk_ab_test');
                if (testVariation) {
                    logConsoleMessage('requested variation test ' + testVariation);
                    return String(testVariation).split(',');
                }
            }

            return [];
        }
    };

    var storage = {
        local: function () {
            var rawData = localStorage.getItem(storageNamespace) || '{}';
            var data = JSON.parse(rawData) || {};
            this.set = function (group, key, value) {
                key = group + ':' + key;
                data[key] = value;
                localStorage.setItem(storageNamespace, JSON.stringify(data));
            };
            this.get = function (group, key) {
                key = group + ':' + key;
                if (data && key in data) {
                    return data[key];
                }
            };
            this.clearAll = function (){
                data = {};
                localStorage.setItem(storageNamespace, JSON.stringify({}));
            }
        },
        cookies: function () {
            this.set = function (group, key, value) {
                key = storageNamespace + ':' + group + ':' + key;
                var days = 365;
                var date = new Date();
                date.setTime(date.getTime()+(days*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();

                document.cookie = key + '=' + encodeURIComponent(value) +'; expires=' + expires + '; path=/';
            };
            this.get = function (group, key) {
                key = storageNamespace + ':' + group + ':' + key;
                var param = key + '=';
                var cookieParts = document.cookie.split(';');

                for (var i=0; i < cookieParts.length; i++) {
                    var cookiePart = cookieParts[i];
                    cookiePart = ('' + cookiePart).replace(/^\s+/, '');

                    if (cookiePart.indexOf(param) == 0) {
                        return decodeURIComponent(cookiePart.substring(param.length, cookiePart.length));
                    }
                }
            };
            this.clearAll = function (){ }
        }
    };

    var target = {
        location: window.location,
        matchesTarget: function (targetObj) {
            if (!targetObj || !targetObj.type || !targetObj.attribute) {
                return true;
            }

            var attributeValue = target._getValueForAttribute(targetObj);

            return target._matchesAttribute(targetObj, attributeValue);
        },
        matchesTargets: function (includedTargets, excludedTargets) {
            if (excludedTargets && excludedTargets.length) {
                var excludedTarget;
                for (var i = 0; i < excludedTargets.length; i++) {
                    excludedTarget = excludedTargets[i];
                    if (this.matchesTarget(excludedTarget)) {
                        return false;
                    }
                }
            }

            if (includedTargets && includedTargets.length) {
                var includedTarget;
                for (var i = 0; i < includedTargets.length; i++) {
                    includedTarget = includedTargets[i];
                    if (this.matchesTarget(includedTarget)) {
                        return true;
                    }
                }

                return false;
            }

            return true;
        },
        matchesDate: function(now, startDateTime, endDateTime) {
            var currentTimestampUTC = now.getTime() + (now.getTimezoneOffset() * 60000);

            try {
                var start = new Date(startDateTime);
            } catch (e) {
                if (startDateTime) {
                    throwError('Invalid startDateTime given');
                }
            }

            try {
                var end = new Date(endDateTime);
            } catch (e) {
                if (endDateTime) {
                    throwError('Invalid startDateTime given');
                }
            }

            if (startDateTime && isNaN && isNaN(start.getTime())) {
                throwError('Invalid startDateTime given');
            }

            if (endDateTime && isNaN && isNaN(end.getTime())) {
                throwError('Invalid endDateTime given');
            }

            if (startDateTime && currentTimestampUTC < (start.getTime() + (start.getTimezoneOffset() * 60000))) {
                return false;
            }

            if (endDateTime && currentTimestampUTC > (end.getTime() + (end.getTimezoneOffset() * 60000))) {
                return false;
            }

            return true;
        },
        _getValueForAttribute: function (target) {
            var attribute = ('' + target.attribute).toLowerCase();

            switch (attribute) {
                case Experiment.TARGET_ATTRIBUTE_URL:
                    return utils.decodeSafe(this.location.href);
                case Experiment.TARGET_ATTRIBUTE_PATH:
                    return utils.decodeSafe(this.location.pathname);
                case Experiment.TARGET_ATTRIBUTE_URLPARAM:
                    return utils.getQueryParameter(this.location.search, target.value);
            }
        },
        _matchesAttribute: function (target, attributeValue) {
            var attribute = ('' + target.attribute).toLowerCase();

            switch (attribute) {
                case Experiment.TARGET_ATTRIBUTE_URL:
                case Experiment.TARGET_ATTRIBUTE_PATH:
                    return this._matchesTargetValue(attributeValue, target.type, target.inverted, target.value);
                case Experiment.TARGET_ATTRIBUTE_URLPARAM:
                    return this._matchesTargetValue(attributeValue, target.type, target.inverted, target.value2);
                default:
                    throwError('Invalid target attribute');
            }

            return false;
        },
        _matchesTargetValue: function (attributeValue, type, invert, valueToMatch) {
            var matches = false;
            var invert = !!invert && invert !== '0';

            if ('string' === typeof attributeValue) {
                attributeValue = attributeValue.toLowerCase();
            }

            if ('string' === typeof valueToMatch && type !== 'regexp') {
                valueToMatch = valueToMatch.toLowerCase();
            }

            switch (type) {
                case Experiment.TARGET_TYPE_ANY:
                    matches = true;
                    break;
                case Experiment.TARGET_TYPE_EXISTS:
                    if (typeof attributeValue !== 'undefined' && attributeValue !== null) {
                        matches = true;
                    }
                    break;
                case Experiment.TARGET_TYPE_EQUALS_SIMPLE:
                    if (attributeValue && attributeValue === String(valueToMatch)) {
                        matches = true;
                    }

                    attributeValue = utils.removeQueryAndHashFromUrl(attributeValue);
                    attributeValue = utils.removeProtocol(attributeValue);
                    valueToMatch = utils.removeProtocol(valueToMatch);

                    attributeValue = utils.removeWwwSubdomain(attributeValue);
                    valueToMatch = utils.removeWwwSubdomain(valueToMatch);

                    if (attributeValue && (attributeValue === String(valueToMatch) ||
                        attributeValue + '/' === String(valueToMatch) ||
                        attributeValue === '/' + valueToMatch ||
                        attributeValue === valueToMatch + '/' ||
                        attributeValue === '/' + valueToMatch + '/')) {
                        matches = true;
                    }
                    break;
                case Experiment.TARGET_TYPE_EQUALS_EXACTLY:

                    if (attributeValue && attributeValue === String(valueToMatch)) {
                        matches = true;
                    }

                    if (attributeValue && attributeValue.indexOf('://') > 0
                        && attributeValue.charAt(attributeValue.length - 1) === '/'
                        && 3 === (attributeValue.split('/').length - 1)
                        && attributeValue === (valueToMatch + '/')) {
                        // when url like https://⁄innocraft.com/ => match https://innocraft.com
                        matches = true;
                    }

                    if (valueToMatch && valueToMatch.indexOf('://') > 0
                        && valueToMatch.charAt(valueToMatch.length - 1) === '/'
                        && 3 === (valueToMatch.split('/').length - 1)
                        && valueToMatch === (attributeValue + '/')) {
                        // when url like https://⁄innocraft.com => match https://innocraft.com/
                        matches = true;
                    }
                    break;
                case Experiment.TARGET_TYPE_CONTAINS:
                    if (attributeValue && attributeValue.indexOf(String(valueToMatch)) !== -1) {
                        matches = true;
                    }
                    break;
                case Experiment.TARGET_TYPE_STARTS_WITH:
                    if (attributeValue && attributeValue.indexOf(String(valueToMatch)) === 0) {
                        matches = true;
                    }
                    break;
                case Experiment.TARGET_TYPE_REGEXP:
                    if (new RegExp(valueToMatch).test(attributeValue)) {
                        matches = true;
                    }
                    break;
                default:
                    throwError('Invalid target type given');
            }

            if (invert) {
                return !matches;
            }

            return matches;
        }
    };

    var Experiment = function (options) {

        this.options = options ? options : {};

        logConsoleMessage('creating experiment with options', options);

        if (!this.options.name) {
            throwError('Missing experiment name in options. Use eg: new PiwikAbTesting.Experiment({name: "MyName"})');
        }

        if (!this.options.variations) {
            throwError('Missing "variations" option. Use eg: new PiwikAbTesting.Experiment({variations: [{...}, {...}]})');
        }

        if (typeof this.options.variations !== 'object' || !this.options.variations.length) {
            throwError('"variations" has to be an array');
        }

        var i;
        for (i = 0; i < this.options.variations.length; i++) {
            if (typeof this.options.variations[i] !== 'object') {
                throwError('Each variation has to be an object');
            }

            if (!this.options.variations[i].name) {
                throwError('Missing variation name');
            }

            if (typeof this.options.variations[i].activate !== 'function') {
                throwError('A variation does not implement the "activate" method' + JSON.stringify(options));
            }
        }

        if (this.options.trigger && typeof this.options.trigger !== 'function') {
            throwError('The "trigger" option is not a function');
        }

        if (this.options.piwikTracker) {
            if (typeof this.options.piwikTracker !== 'object') {
                throwError('The Matomo tracker must be an instance of Piwik');
            }

            if (!this.options.piwikTracker['trackEvent']) {
                throwError('The Matomo instance does not implement the trackEvent method. Maybe a wrong Matomo instance is based as option?');
            }

            if (!this.options.piwikTracker['trackGoal']) {
                throwError('The Matomo instance does not implement the trackGoal method. Maybe a wrong Matomo instance is based as option?');
            }
        }

        if (this.options.percentage && this.options.percentage < 0 || this.options.percentage > 100) {
            throwError('percentage has to be between 0 and 100');
        }

        this.name = null;
        this.variations = null;
        this.includedTargets = null;
        this.excludedTargets = null;
        this.startDateTime = null;
        this.endDateTime = null;
        this.percentage = 100;
        this.piwikTracker = null;
        this.trigger = function () {
            return true;
        };

        // we cache this name once a name has been forced as it might be useful if localstorage does not work
        // and cookies are disabled. In this case the method getActivatedVariationName() will behave as expected
        this._cacheForcedVariationName = null;

        if (utils.hasLocalStorage()) {
            logConsoleMessage('using local storage');
            this.storage = new storage.local();
        } else {
            logConsoleMessage('using cookies storage');
            this.storage = new storage.cookies();
        }

        var j;
        for (j in this.options) {
            if (Object.prototype.hasOwnProperty.call(this.options, j)) {
                this[j] = this.options[j];
            }
        }

        this._track = function (method, args) {
            if (this.piwikTracker) {
                this.piwikTracker[method].apply(this.piwikTracker, args);
            } else {
                if (typeof _paq === 'undefined') {
                    _paq = [];
                }

                args.unshift(method);
                _paq.push(args);
            }

            logConsoleMessage('sent tracking request', method, args);
        };

        this.trackUsedVariation = function (variationName) {
            this._track('trackEvent', ['abtesting', this.name, variationName]);
        }

        this.trackGoal = function (idGoal) {
            if (idGoal) {
                this._track('trackGoal', [idGoal]);
            }
        };
        
        this._getVariationByName = function (variationName) {
           variationName = ('' + variationName).toLowerCase();

           for (var i = 0; i < this.variations.length; i++) {
               if (('' + this.variations[i].name).toLowerCase() === variationName) {
                   return this.variations[i];
               }
           }
        };

        this._makeEvent = function (variation) {
            var experiment = this;

            var onReady = function (callback) {
                // minimal onready implementation in case matomo does not have DOM ready for some reason
                callback();
            };

            if ('undefined' !== typeof Piwik && 'undefined' !== typeof Piwik.DOM && Piwik.DOM.onReady) {
                onReady = Piwik.DOM.onReady;
            }

            return {
                type: 'activate',
                experiment: this,
                onReady: onReady,
                redirect: function (url) {
                    var part = 'pk_abe=' + encodeURIComponent(experiment.name) + '&pk_abv=' + encodeURIComponent(variation.name);
                    if (url && (url.indexOf('?') !== -1)) {
                        url += '&' + part;
                    } else {
                        url += '?' + part;
                    }

                    var trackers = Piwik.getAsyncTrackers();
                    for (var i = 0; i < trackers.length; i++) {
                        trackers[i].trackPageView = function () {};
                        trackers[i].trackEvent = function () {};
                        trackers[i].trackGoal = function () {};
                    }

                    if (window.location.href === url) {
                        return;
                    }

                    window.location.replace(url);
                }
            };
        };

        this.forceVariation = function (variationName) {
            this._cacheForcedVariationName = variationName;

            logConsoleMessage(this.name, 'forcing variation', variationName);

            var variation = this._getVariationByName(variationName);

            var result = this.storage.set('variation', this.name, variationName);

            if (variation && variation.activate) {
                var event = this._makeEvent(variation);
                variation.activate.apply(variation, [event]);
            }

            this.trackUsedVariation(variationName);

            return result;
        };

        // returns undefined if no variation has been activated so far
        this.getActivatedVariationName = function () {
            var variationName;
            if (this._cacheForcedVariationName) {
                variationName = this._cacheForcedVariationName;
            } else {
                variationName = this.storage.get('variation', this.name);
            }

            if (this._getVariationByName(variationName)) {
                return variationName;
            }
        };

        this._doVariationsIncludeOriginal = function () {
            for (var i = 0; i < this.variations.length; i++) {
                var variation = this.variations[i];
                if (variation && variation.name && variation.name === NAME_ORIGINAL_VARIATION) {
                    return true;
                }
            }

            return false;
        }
        
        this._getVariationDefaultPercentage = function () {
            var percentageUsed = 100;

            var numVariations = this.variations.length;

            for (var i = 0; i < this.variations.length; i++) {
                var variation = this.variations[i];
                if (variation && (variation.percentage || variation.percentage === 0 || variation.percentage === '0')) {
                    percentageUsed = percentageUsed - parseInt(variation.percentage, 10);
                    numVariations--;
                }
            };

            var result = Math.round(percentageUsed / numVariations);

            if (result > 100) {
                result = 100;
            }

            if (result < 0) {
                result = 0;
            }

            return result;
        }

        this.getRandomVariationName = function () {
            var defaultPercentage = this._getVariationDefaultPercentage();
            var indexes = [];

            for (var i = 0; i < this.variations.length; i++) {
                var percentage = defaultPercentage;
                if (this.variations[i].percentage || this.variations[i].percentage === 0 || this.variations[i].percentage === '0') {
                    percentage = this.variations[i].percentage;
                }

                for (var j = 0; j < percentage; j++) {
                    indexes.push(i);
                }
            }

            var index = utils.getRandomNumber(0, indexes.length - 1);
            var variationIndex = indexes[index];

            return this.variations[variationIndex].name;
        };

        this._isInTestGroup = function () {
            var active = this.storage.get('isInTestGroup', this.name);

            if (typeof active !== 'undefined' && active !== null) {
                return active === '1' ? true : false;
            }

            active = utils.getRandomNumber(1, 100) <= this.percentage;

            this.storage.set('isInTestGroup', this.name, active ? '1' : '0');

            return active;
        };

        this.selectRandomVariation = function () {
            logConsoleMessage(this.name, 'select random variation');

            var variationName = this.getRandomVariationName();

            this.forceVariation(variationName);

            return variationName;
        };

        this.shouldTrigger = function () {
            if (!target.matchesDate(new Date(), this.startDateTime, this.endDateTime)) {
                logConsoleMessage(this.name, 'wont run, scheduled date does not match');
                return false;
            }

            if (!target.matchesTargets(this.includedTargets, this.excludedTargets)) {
                logConsoleMessage(this.name, 'wont run, targets do not match');
                return false;
            }

            if (!this.trigger()) {
                logConsoleMessage(this.name, 'wont run, disabled by trigger method');
                // trigger is optional user defined function passed via options
                return false;
            }

            if (!this._isInTestGroup()) {
                logConsoleMessage(this.name, 'wont run, not in test group');
                return false;
            }

            return true;
        };

        if (!this._doVariationsIncludeOriginal()) {
            this.variations.push({name: NAME_ORIGINAL_VARIATION, activate: function () {}});
        }

        var testVariations = utils.getVariationTest(window.location || null);
        if (testVariations && testVariations.length) {
            for (var i = 0; i < testVariations.length; i++) {
                if (this._getVariationByName(testVariations[i])) {

                    logConsoleMessage('going to test variation and disable tracking ' + testVariations[i]);
                    // we ignore shouldTrigger and always execute it if the variation actually exists
                    this.trackUsedVariation = function () { };
                    // we make sure to not track anything when testing it so the experiment does not yet get activated in Piwik
                    this.forceVariation(testVariations[i]);
                    return;
                }
            }
        }

        if (!this.shouldTrigger()) {
            logConsoleMessage(this.name, 'experiment should not trigger');
            return;
        }

        logConsoleMessage(this.name, 'should trigger');

        var variationName = this.getActivatedVariationName();

        if (variationName) {
            this.forceVariation(variationName);
        } else {
            logConsoleMessage(this.name, 'no existing variation found');
            this.selectRandomVariation();
        }
    }

    Experiment.NAME_ORIGINAL_VARIATION = NAME_ORIGINAL_VARIATION;
    Experiment.TARGET_ATTRIBUTE_URL = 'url';
    Experiment.TARGET_ATTRIBUTE_PATH = 'path';
    Experiment.TARGET_ATTRIBUTE_URLPARAM = 'urlparam';
    Experiment.TARGET_TYPE_ANY = 'any';
    Experiment.TARGET_TYPE_EXISTS = 'exists';
    Experiment.TARGET_TYPE_EQUALS_SIMPLE = 'equals_simple';
    Experiment.TARGET_TYPE_EQUALS_EXACTLY = 'equals_exactly';
    Experiment.TARGET_TYPE_CONTAINS = 'contains';
    Experiment.TARGET_TYPE_STARTS_WITH = 'starts_with';
    Experiment.TARGET_TYPE_REGEXP = 'regexp';
    Experiment.THROW_ERRORS = true;

    function callAsyncReadyMethod()
    {
        if (typeof window === 'object' && 'function' === typeof window.piwikAbTestingAsyncInit) {
            window.piwikAbTestingAsyncInit();
        }
    }

    function enterUserFromUrl()
    {
        if (window.location && utils.getQueryParameter(window.location.search, 'pk_abe')) {
            var e = utils.getQueryParameter(window.location.search, 'pk_abe');
            var v = utils.getQueryParameter(window.location.search, 'pk_abv');
            Piwik.AbTesting.enter({experiment: e, variation: v});
            logConsoleMessage('entered experiment from url parameters');
        }
    }

    function init() {
        if ('object' === typeof window && 'object' === typeof window.Piwik && 'object' === typeof window.Piwik.AbTesting) {
            logConsoleMessage('wont initialize, AbTesting already loaded');
            // do not initialize abtesting twice
            return;
        }

        if ('object' === typeof window && 'object' !== typeof window.Piwik) {
            logConsoleMessage('wont initialize, Matomo is not yet loaded');
            // piwik is not defined yet
            return;
        }

        Piwik.AbTesting = {
            utils: utils, target: target, storage: storage, Experiment: Experiment,
            enter: function (args) {
                if (args && args.experiment) {
                    _paq = _paq || [];
                    _paq.push(['trackEvent', 'abtesting', args.experiment, args.variation || NAME_ORIGINAL_VARIATION]);
                    logConsoleMessage('entering user into an experiment', args);
                } else {
                    logConsoleMessage('not entering user into an experiment, missing parameter experiment');
                }
            },
            create: function (args) {
                return new Experiment(args);
            },
            enableDebugMode: function () {
                debugMode = true
            }
        };

        if (window.Piwik.initialized) {
            // tracker was separately loaded via separate include. we need to enrich already created tracker
            // now that the methods are set on the tracker instance we check if there were calls that couldn't be executed
            // the first time because the abtesting plugin was not loaded yet (but it is now)
            Piwik.retryMissedPluginCalls();
            callAsyncReadyMethod();
            enterUserFromUrl();
        } else {
            Piwik.on('PiwikInitialized', function () {
                callAsyncReadyMethod();
                enterUserFromUrl();
            });
        }

    }

    if (typeof piwikExposeAbTestingTarget !== 'undefined' && piwikExposeAbTestingTarget) {
        // needed for piwik itself
        window.piwikAbTestingTarget = target;
    }

    if ('object' === typeof window.Piwik) {
        logConsoleMessage('matomo was already loaded, initializing abTesting now');
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof window.piwikPluginAsyncInit) {
            window.piwikPluginAsyncInit = [];
        }

        window.piwikPluginAsyncInit.push(init);

        logConsoleMessage('matomo not loaded yet, waiting for it to be loaded');
    }

})();
/* END GENERATED: tracker.js */


(function () {
    'use strict';

    function hasPaqConfiguration()
    {
        if ('object' !== typeof _paq) {
            return false;
        }
        // needed to write it this way for jslint
        var lengthType = typeof _paq.length;
        if ('undefined' === lengthType) {
            return false;
        }

        return !!_paq.length;
    }

    if (window
        && 'object' === typeof window.piwikPluginAsyncInit
        && window.piwikPluginAsyncInit.length) {
        var i = 0;
        for (i; i < window.piwikPluginAsyncInit.length; i++) {
            if (typeof window.piwikPluginAsyncInit[i] === 'function') {
                window.piwikPluginAsyncInit[i]();
            }
        }
    }

    if (window && window.piwikAsyncInit) {
        window.piwikAsyncInit();
    }

    if (!window.Piwik.getAsyncTrackers().length) {
        // we only create an initial tracker when no other async tracker has been created yet in piwikAsyncInit()
        if (hasPaqConfiguration()) {
            // we only create an initial tracker if there is a configuration for it via _paq. Otherwise
            // Piwik.getAsyncTrackers() would return unconfigured trackers
            window.Piwik.addTracker();
        } else {
            _paq = {push: function (args) {
                    // needed to write it this way for jslint
                    var consoleType = typeof console;
                    if (consoleType !== 'undefined' && console && console.error) {
                        console.error('_paq.push() was used but Matomo tracker was not initialized before the matomo.js file was loaded. Make sure to configure the tracker via _paq.push before loading matomo.js. Alternatively, you can create a tracker via Matomo.addTracker() manually and then use _paq.push but it may not fully work as tracker methods may not be executed in the correct order.', args);
                    }
                }};
        }
    }

    window.Piwik.trigger('PiwikInitialized', []);
    window.Piwik.initialized = true;
}());


/*jslint sloppy: true */
(function () {
    var jsTrackerType = (typeof AnalyticsTracker);
    if (jsTrackerType === 'undefined') {
        AnalyticsTracker = window.Piwik;
    }
}());
/*jslint sloppy: false */

/************************************************************
 * Deprecated functionality below
 * Legacy piwik.js compatibility ftw
 ************************************************************/

/*
 * Piwik globals
 *
 *   var piwik_install_tracker, piwik_tracker_pause, piwik_download_extensions, piwik_hosts_alias, piwik_ignore_classes;
 */
/*global piwik_log:true */
/*global piwik_track:true */

/**
 * Track page visit
 *
 * @param string documentTitle
 * @param int|string siteId
 * @param string piwikUrl
 * @param mixed customData
 */
if (typeof piwik_log !== 'function') {
    piwik_log = function (documentTitle, siteId, piwikUrl, customData) {
        'use strict';

        function getOption(optionName) {
            try {
                if (window['piwik_' + optionName]) {
                    return window['piwik_' + optionName];
                }
            } catch (ignore) { }

            return; // undefined
        }

        // instantiate the tracker
        var option,
            piwikTracker = window.Piwik.getTracker(piwikUrl, siteId);

        // initialize tracker
        piwikTracker.setDocumentTitle(documentTitle);
        piwikTracker.setCustomData(customData);

        // handle Piwik globals
        option = getOption('tracker_pause');

        if (option) {
            piwikTracker.setLinkTrackingTimer(option);
        }

        option = getOption('download_extensions');

        if (option) {
            piwikTracker.setDownloadExtensions(option);
        }

        option = getOption('hosts_alias');

        if (option) {
            piwikTracker.setDomains(option);
        }

        option = getOption('ignore_classes');

        if (option) {
            piwikTracker.setIgnoreClasses(option);
        }

        // track this page view
        piwikTracker.trackPageView();

        // default is to install the link tracker
        if (getOption('install_tracker')) {

            /**
             * Track click manually (function is defined below)
             *
             * @param string sourceUrl
             * @param int|string siteId
             * @param string piwikUrl
             * @param string linkType
             */
            piwik_track = function (sourceUrl, siteId, piwikUrl, linkType) {
                piwikTracker.setSiteId(siteId);
                piwikTracker.setTrackerUrl(piwikUrl);
                piwikTracker.trackLink(sourceUrl, linkType);
            };

            // set-up link tracking
            piwikTracker.enableLinkTracking();
        }
    };
}

/*! @license-end */
