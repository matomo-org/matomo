/*!
 * Piwik - Web Analytics
 *
 * JavaScript tracking client
 *
 * @link http://piwik.org
 * @source http://dev.piwik.org/trac/browser/trunk/js/piwik.js
 * @license http://www.opensource.org/licenses/bsd-license.php Simplified BSD
 */

// Refer to README for build instructions when minifying this file for distribution.

/*jslint browser:true, forin:true, plusplus:false, onevar:false, strict:true, evil:true */
/*global window unescape ActiveXObject _paq:true */

/*
 * Browser [In]Compatibility
 *
 * This version of piwik.js is known to not work with:
 * - IE4 - try..catch and for..in introduced in IE5
 * - IE5 - named anonymous functions, array.push, encodeURIComponent, and decodeURIComponent introduced in IE5.5
 */

var
	// asynchronous tracker (or proxy)
	_paq = _paq || [],

	// Piwik singleton and namespace
	Piwik =	Piwik || (function () {
		"use strict";

		/************************************************************
		 * Private data
		 ************************************************************/

		var expireDateTime,

		/* plugins */
		plugins = {},

		/* alias frequently used globals for added minification */
		documentAlias = document,
		navigatorAlias = navigator,
		screenAlias = screen,
		windowAlias = window,

		/* DOM Ready */
		hasLoaded = false,
		registeredOnLoadHandlers = [],

		/*
		 * encode
		 */
		encodeWrapper = windowAlias.encodeURIComponent,

		/*
		 * decode
		 */
		decodeWrapper = windowAlias.decodeURIComponent,

		/* asynchronous tracker */
		asyncTracker,

		/* iterator */
		i;

		/************************************************************
		 * Private methods
		 ************************************************************/

		/*
		 * Is property defined?
		 */
		function isDefined(property) {
			return typeof property !== 'undefined';
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

		/*
		 * apply wrapper
		 *
		 * @param array parameterArray An array comprising either:
		 *      [ 'methodName', optional_parameters ]
		 * or:
		 *      [ functionObject, optional_parameters ] 
		 */
		function apply(parameterArray) {
			var f = parameterArray.shift();

			if (isString(f)) {
				asyncTracker[f].apply(asyncTracker, parameterArray);
			} else {
				f.apply(asyncTracker, parameterArray);
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

		/*
		 * Call plugin hook methods
		 */
		function executePluginMethod(methodName, callback) {
			var result = '', i, pluginMethod;

			for (i in plugins) {
				pluginMethod = plugins[i][methodName];
				if (isFunction(pluginMethod)) {
					result += pluginMethod(callback);
				}
			}

			return result;
		}

		/*
		 * Handle beforeunload event
		 */
		function beforeUnloadHandler() {
			executePluginMethod('unload');

			/*
			 * Delay/pause (blocks UI)
			 */
			if (expireDateTime) {
				// the things we do for backwards compatibility...
				// in ECMA-262 5th ed., we could simply use:  while (Date.now() < expireDateTime) { }
				var now;

				do {
					now = new Date();
				} while (now.getTime() < expireDateTime);
			}
		}

		/*
		 * Handler for onload event
		 */
		function loadHandler() {
			var i;
			if (!hasLoaded) {
				hasLoaded = true;
				executePluginMethod('load');
				for (i = 0; i < registeredOnLoadHandlers.length; i++) {
					registeredOnLoadHandlers[i]();
				}
			}
			return true;
		}

		/*
		 * Add onload or DOM ready handler
		 */
		function addReadyListener() {
			if (documentAlias.addEventListener) {
				addEventListener(documentAlias, 'DOMContentLoaded', function ready() {
                        documentAlias.removeEventListener('DOMContentLoaded', ready, false);
						loadHandler();
					});
			} else if (documentAlias.attachEvent) {
				documentAlias.attachEvent('onreadystatechange', function ready() {
					if (documentAlias.readyState === 'complete') {
						documentAlias.detachEvent('onreadystatechange', ready);
						loadHandler();
					}
				});

				if (documentAlias.documentElement.doScroll && windowAlias === top) {
					(function ready() {
						if (!hasLoaded) {
							try {
								documentAlias.documentElement.doScroll('left');
							} catch (error) {
								setTimeout(ready, 0);
								return;
							}
							loadHandler();
						}
					}());
				}
			}
			// fallback
			addEventListener(windowAlias, 'load', loadHandler, false);
		}

		/*
		 * Get page referrer
		 */
		function getReferrer() {
			var referrer = '';
			try {
				referrer = top.document.referrer;
			} catch (e) {
				if (parent) {
					try {
						referrer = parent.document.referrer;
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
		 * Extract hostname from URL
		 */
		function getHostName(url) {
			// scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
			var e = new RegExp('^(?:(?:https?|ftp):)/*(?:[^@]+@)?([^:/#]+)'),
				matches = e.exec(url);
			return matches ? matches[1] : url;
		}

		/*
		 * Extract parameter from URL
		 */
		function getParameter(url, varName) {
			// scheme : // [username [: password] @] hostame [: port] [/ [path] [? query] [# fragment]]
			var e = new RegExp('^(?:https?|ftp)(?::/*(?:[^?]+)[?])([^#]+)'),
				matches = e.exec(url),
				f = new RegExp('(?:^|&)' + varName + '=([^&]*)'),
				result = matches ? f.exec(matches[1]) : 0;
				return result ? decodeWrapper(result[1]) : '';
		}

		/*
		 * Set cookie value
		 */
		function setCookie(cookieName, value, msToExpire, path, domain, secure) {
			var expiryDate;

			// relative time to expire in milliseconds
			if (msToExpire) {
				expiryDate = new Date();
				expiryDate.setTime(expiryDate.getTime() + msToExpire);
			}

			documentAlias.cookie = cookieName + '=' + encodeWrapper(value) +
				(msToExpire ? ';expires=' + expiryDate.toGMTString() : '') +
				';path=' + (path ? path : '/') +
				(domain ? ';domain=' + domain : '') +
				(secure ? ';secure' : '');
		}

		/*
		 * Get cookie value
		 */
		function getCookie(cookieName) {
			var cookiePattern = new RegExp('(^|;)[ ]*' + cookieName + '=([^;]*)'),

				cookieMatch = cookiePattern.exec(documentAlias.cookie);

			return cookieMatch ? decodeWrapper(cookieMatch[2]) : 0;
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
					var str = "",
						i,
						v;

					for (i = 7; i >= 0; i--) {
						v = (val >>> (i * 4)) & 0x0f;
						str += v.toString(16);
					}
					return str;
				},

				blockstart,
				i, j,
				W = [],
				H0 = 0x67452301,
				H1 = 0xEFCDAB89,
				H2 = 0x98BADCFE,
				H3 = 0x10325476,
				H4 = 0xC3D2E1F0,
				A, B, C, D, E,
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

		/************************************************************
		 * stringify (json encode)
		 * - based on public domain JSON implementation at http://www.json.org/json2.js (2009-04-16)
		 ************************************************************/
		function stringify(value) {

			var escapable = new RegExp('[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]', 'g'),
				// table of character substitutions
				meta = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};

			// If the string contains no control characters, no quote characters, and no
			// backslash characters, then we can safely slap some quotes around it.
			// Otherwise we must also replace the offending characters with safe escape
			// sequences.
			function quote(string) {
				escapable.lastIndex = 0;
				return escapable.test(string) ?
					'"' + string.replace(escapable, function (a) {
						var c = meta[a];
						return isString(c) ? c :
							'\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
					}) + '"' :
					'"' + string + '"';
			}

			function f(n) {
				return n < 10 ? '0' + n : n;
			}

			// Produce a string from holder[key].
			function str(key, holder) {
				var i,          // The loop counter.
					k,          // The member key.
					v,          // The member value.
					partial,
					value = holder[key];

				if (value === null) {
					return 'null';
				}

				// If the value has a toJSON method, call it to obtain a replacement value.
				if (value && isObject(value) && isFunction(value.toJSON)) {
					value = value.toJSON(key);
				}

				// What happens next depends on the value's type.
				switch (typeof value) {
				case 'string':
					return quote(value);

				case 'number':
					// JSON numbers must be finite. Encode non-finite numbers as null.
					return isFinite(value) ? String(value) : 'null';

				case 'boolean':
				case 'null':
					// If the value is a boolean or null, convert it to a string. Note:
					// typeof null does not produce 'null'. The case is included here in
					// the remote chance that this gets fixed someday.
					return String(value);

				case 'object':
					// Make an array to hold the partial results of stringifying this object value.
					partial = [];

					// Is the value an array?
					// if (Object.prototype.toString.call(value)=="[object Array]") {	// call added in IE5.5
					if (value instanceof Array) {
						// The value is an array. Stringify every element. Use null as a placeholder
						// for non-JSON values.
						for (i = 0; i < value.length; i++) {
							partial[i] = str(i, value) || 'null';
						}

						// Join all of the elements together, separated with commas, and wrap them in
						// brackets.
						v = partial.length === 0 ? '[]' : '[' + partial.join(',') + ']';
						return v;
					}

					// if (Object.prototype.toString.call(value)=="[object Date]") {	// call added in IE5.5
					if (value instanceof Date) {
						return quote(value.getUTCFullYear()   + '-' +
							f(value.getUTCMonth() + 1) + '-' +
							f(value.getUTCDate())      + 'T' +
							f(value.getUTCHours())     + ':' +
							f(value.getUTCMinutes())   + ':' +
							f(value.getUTCSeconds())   + 'Z');
					}

					// Otherwise, iterate through all of the keys in the object.
					for (k in value) {
						v = str(k, value);
						if (v) {
							partial.push(quote(k) + ':' + v);
						}
					}

					// Join all of the member texts together, separated with commas,
					// and wrap them in braces.
					v = partial.length === 0 ? '{}' : '{' + partial.join(',') + '}';
					return v;
				}
			}

			return str('', {'': value});
		}
		/************************************************************
		 * end stringify
		 ************************************************************/
		
		/************************************************************
		 * json_parse (parse a string into a JS object)
		 * https://github.com/douglascrockford/JSON-js/blob/master/json_parse.js
		 ************************************************************/
		
		var json_parse = (function () {

			// This is a function that can parse a JSON text, producing a JavaScript
			// data structure. It is a simple, recursive descent parser. It does not use
			// eval or regular expressions, so it can be used as a model for implementing
			// a JSON parser in other languages.

			// We are defining the function inside of another function to avoid creating
			// global variables.

			    var at,     // The index of the current character
			        ch,     // The current character
			        escapee = {
			            '"':  '"',
			            '\\': '\\',
			            '/':  '/',
			            b:    '\b',
			            f:    '\f',
			            n:    '\n',
			            r:    '\r',
			            t:    '\t'
			        },
			        text,

			        error = function (m) {

			// Call error when something is wrong.

			            throw {
			                name:    'SyntaxError',
			                message: m,
			                at:      at,
			                text:    text
			            };
			        },

			        next = function (c) {

			// If a c parameter is provided, verify that it matches the current character.

			            if (c && c !== ch) {
			                error("Expected '" + c + "' instead of '" + ch + "'");
			            }

			// Get the next character. When there are no more characters,
			// return the empty string.

			            ch = text.charAt(at);
			            at += 1;
			            return ch;
			        },

			        number = function () {

			// Parse a number value.

			            var number,
			                string = '';

			            if (ch === '-') {
			                string = '-';
			                next('-');
			            }
			            while (ch >= '0' && ch <= '9') {
			                string += ch;
			                next();
			            }
			            if (ch === '.') {
			                string += '.';
			                while (next() && ch >= '0' && ch <= '9') {
			                    string += ch;
			                }
			            }
			            if (ch === 'e' || ch === 'E') {
			                string += ch;
			                next();
			                if (ch === '-' || ch === '+') {
			                    string += ch;
			                    next();
			                }
			                while (ch >= '0' && ch <= '9') {
			                    string += ch;
			                    next();
			                }
			            }
			            number = +string;
			            if (isNaN(number)) {
			                error("Bad number");
			            } else {
			                return number;
			            }
			        },

			        string = function () {

			// Parse a string value.

			            var hex,
			                i,
			                string = '',
			                uffff;

			// When parsing for string values, we must look for " and \ characters.

			            if (ch === '"') {
			                while (next()) {
			                    if (ch === '"') {
			                        next();
			                        return string;
			                    } else if (ch === '\\') {
			                        next();
			                        if (ch === 'u') {
			                            uffff = 0;
			                            for (i = 0; i < 4; i += 1) {
			                                hex = parseInt(next(), 16);
			                                if (!isFinite(hex)) {
			                                    break;
			                                }
			                                uffff = uffff * 16 + hex;
			                            }
			                            string += String.fromCharCode(uffff);
			                        } else if (typeof escapee[ch] === 'string') {
			                            string += escapee[ch];
			                        } else {
			                            break;
			                        }
			                    } else {
			                        string += ch;
			                    }
			                }
			            }
			            error("Bad string");
			        },

			        white = function () {

			// Skip whitespace.

			            while (ch && ch <= ' ') {
			                next();
			            }
			        },

			        word = function () {

			// true, false, or null.

			            switch (ch) {
			            case 't':
			                next('t');
			                next('r');
			                next('u');
			                next('e');
			                return true;
			            case 'f':
			                next('f');
			                next('a');
			                next('l');
			                next('s');
			                next('e');
			                return false;
			            case 'n':
			                next('n');
			                next('u');
			                next('l');
			                next('l');
			                return null;
			            }
			            error("Unexpected '" + ch + "'");
			        },

			        value,  // Place holder for the value function.

			        array = function () {

			// Parse an array value.

			            var array = [];

			            if (ch === '[') {
			                next('[');
			                white();
			                if (ch === ']') {
			                    next(']');
			                    return array;   // empty array
			                }
			                while (ch) {
			                    array.push(value());
			                    white();
			                    if (ch === ']') {
			                        next(']');
			                        return array;
			                    }
			                    next(',');
			                    white();
			                }
			            }
			            error("Bad array");
			        },

			        object = function () {

			// Parse an object value.

			            var key,
			                object = {};

			            if (ch === '{') {
			                next('{');
			                white();
			                if (ch === '}') {
			                    next('}');
			                    return object;   // empty object
			                }
			                while (ch) {
			                    key = string();
			                    white();
			                    next(':');
			                    if (Object.hasOwnProperty.call(object, key)) {
			                        error('Duplicate key "' + key + '"');
			                    }
			                    object[key] = value();
			                    white();
			                    if (ch === '}') {
			                        next('}');
			                        return object;
			                    }
			                    next(',');
			                    white();
			                }
			            }
			            error("Bad object");
			        };

			    value = function () {

			// Parse a JSON value. It could be an object, an array, a string, a number,
			// or a word.

			        white();
			        switch (ch) {
			        case '{':
			            return object();
			        case '[':
			            return array();
			        case '"':
			            return string();
			        case '-':
			            return number();
			        default:
			            return ch >= '0' && ch <= '9' ? number() : word();
			        }
			    };

			// Return the json_parse function. It will have access to all of the above
			// functions and variables.

			    return function (source, reviver) {
			        var result;

			        text = source;
			        at = 0;
			        ch = ' ';
			        result = value();
			        white();
			        if (ch) {
			            error("Syntax error");
			        }

			// If there is a reviver function, we recursively walk the new structure,
			// passing each name/value pair to the reviver function for possible
			// transformation, starting with a temporary root object that holds the result
			// in an empty key. If there is not a reviver function, we simply return the
			// result.

			        return typeof reviver === 'function' ? (function walk(holder, key) {
			            var k, v, value = holder[key];
			            if (value && typeof value === 'object') {
			                for (k in value) {
			                    if (Object.hasOwnProperty.call(value, k)) {
			                        v = walk(value, k);
			                        if (v !== undefined) {
			                            value[k] = v;
			                        } else {
			                            delete value[k];
			                        }
			                    }
			                }
			            }
			            return reviver.call(holder, key, value);
			        }({'': result}, '')) : result;
			    };
		}());


		/*
		 * Fix-up URL when page rendered from search engine cache or translated page
		 */
		function urlFixup(hostname, href, referrer) {
			if (hostname === 'webcache.googleusercontent.com' ||			// Google
					hostname === 'cc.bingj.com' ||							// Bing
					hostname.substring(0, 5) === '74.6.') {					// Yahoo (via Inktomi 74.6.0.0/16)
				href = documentAlias.links[0].href;
				hostname = getHostName(href);
			} else if (hostname === 'translate.googleusercontent.com') {	// Google
				if (referrer === '') {
					referrer = href;
				}
				href = getParameter(href, 'u');
				hostname = getHostName(href);
			}
			return [hostname, href, referrer];
		}

		/*
		 * Fix-up domain
		 */
		function domainFixup(domain) {
			var dl = domain.length;
			return (domain.charAt(--dl) === '.') ? domain.substring(0, dl) : domain;
		}

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

			// Current URL and Referrer URL
			locationArray = urlFixup(documentAlias.domain, windowAlias.location.href, getReferrer()),
			domainAlias = domainFixup(locationArray[0]),
			locationHrefAlias = locationArray[1],
			configReferrerUrl = locationArray[2],

			// Request method (GET or POST)
			configRequestMethod = 'GET',

			// Tracker URL
			configTrackerUrl = trackerUrl || '',

			// Site ID
			configTrackerSiteId = siteId || '',

			// Document URL
			configCustomUrl,

			// Document title
			configTitle = documentAlias.title,

			// Extensions to be treated as download links
			configDownloadExtensions = '7z|aac|ar[cj]|as[fx]|avi|bin|csv|deb|dmg|doc|exe|flv|gif|gz|gzip|hqx|jar|jpe?g|js|mp(2|3|4|e?g)|mov(ie)?|ms[ip]|od[bfgpst]|og[gv]|pdf|phps|png|ppt|qtm?|ra[mr]?|rpm|sea|sit|tar|t?bz2?|tgz|torrent|txt|wav|wm[av]|wpd||xls|xml|z|zip',

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

			// Heart beat after initial page view (in milliseconds)
			configHeartBeatTimer = 30000,

			// Disallow hash tags in URL
			configDiscardHashTag,

			// Custom data
			configCustomData,

			// Server cookies (first- or third-party, depending on where Piwik is hosted)
			configServerCookies,

			// First-party cookie name prefix
			configCookieNamePrefix = '_pk_',

			// First-party cookie domain
			// User agent defaults to origin hostname
			configCookieDomain,

			// First-party cookie path
			// Default is user agent defined.
			configCookiePath,

			// Do we attribute the conversion to the first referrer or the most recent referrer?
			configConversionAttributionFirstReferer,

			// Life of the visitor cookie (in milliseconds)
			configVisitorCookieTimeout = 63072000000, // 2 years

			// Life of the session cookie (in milliseconds)
			configSessionCookieTimeout = 1800000, // 30 minutes

			// Life of the referral cookie (in milliseconds)
			configReferralCookieTimeout = 15768000000, // 6 months

			// Custom Variables read from cookie
			customVariables = false,

			// Custom Variables names and values are each truncated before being sent in the request or recorded in the cookie
			customVariableMaximumLength = 100,
			
			// Client-side data collection
			browserHasCookies = '0',

			// Plugin, Parameter name, MIME type, detected
			pluginMap = {
				// document types
				pdf:         ['pdf',   'application/pdf',               '0'],
				// media players
				quicktime:   ['qt',    'video/quicktime',               '0'],
				realplayer:  ['realp', 'audio/x-pn-realaudio-plugin',   '0'],
				wma:         ['wma',   'application/x-mplayer2',        '0'],
				// interactive multimedia 
				director:    ['dir',   'application/x-director',        '0'],
				flash:       ['fla',   'application/x-shockwave-flash', '0'],
				// RIA
				java:        ['java',  'application/x-java-vm',         '0'],
				gears:       ['gears', 'application/x-googlegears',     '0'],
				silverlight: ['ag',    'application/x-silverlight',     '0']
			},

			// Guard against installing the link tracker more than once per Tracker instance
			linkTrackingInstalled = false,

			// Hash function
			hash = sha1,

			// Internal state of the pseudo click handler
			lastButton,
			lastTarget,

			// Visitor ID
			visitorId,

			// Domain hash value
			domainHash;

			/*
			 * Purify URL.
			 */
			function purify(str) {
				var targetPattern;

				if (configDiscardHashTag) {
					targetPattern = new RegExp('#.*');
					return str.replace(targetPattern, '');
				}
				return str;
			}

			/*
			 * Is the host local?  (i.e., not an outlink)
			 */
			function isSiteHostName(hostName) {
				var i, alias, offset;

				for (i = 0; i < configHostsAlias.length; i++) {
					alias = configHostsAlias[i].toLowerCase();

					if (hostName === alias) {
						return true;
					}

					if (alias.substring(0, 2) === '*.') {
						if (hostName === alias.substring(2)) {
							return true;
						}

						offset = hostName.length - alias.length + 1;
						if ((offset > 0) && (hostName.substring(offset) === alias.substring(1))) {
							return true;
						}
					}
				}
				return false;
			}

			/*
			 * Send image request to Piwik server using GET.
			 * The infamous web bug is a transparent, single pixel (1x1) image
			 */
			function getImage(request) {
				var image = new Image(1, 1);
				image.onLoad = function () { };
				image.src = configTrackerUrl + '?' + request;
			}

			/*
			 * POST request to Piwik server using XMLHttpRequest.
			 */
			function sendXmlHttpRequest(request) {
				try {
					// we use the progid Microsoft.XMLHTTP because
					// IE5.5 included MSXML 2.5; the progid MSXML2.XMLHTTP
					// is pinned to MSXML2.XMLHTTP.3.0
					var xhr = windowAlias.XMLHttpRequest ? new windowAlias.XMLHttpRequest() :
						windowAlias.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') :
						null;
					xhr.open('POST', configTrackerUrl, true);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
					xhr.setRequestHeader('Content-Length', request.length);
					xhr.setRequestHeader('Connection', 'close');
					xhr.send(request);
				} catch (e) {
					// fallback
					getImage(request);
				}
			}

			/*
			 * Send request
			 */
			function sendRequest(request, delay)
			{
				var now = new Date();

				if (configRequestMethod === 'POST') {
					sendXmlHttpRequest(request);
				} else {
					getImage(request);
				}

				expireDateTime = now.getTime() + delay;
			}

			/*
			 * Browser plugin tests
			 */
			function detectBrowserPlugins() {
				var i, mimeType;

				// Safari and Opera
				// IE6/IE7 navigator.javaEnabled can't be aliased, so test directly
				if (typeof navigator.javaEnabled !== 'unknown' &&
						isDefined(navigatorAlias.javaEnabled) &&
						navigatorAlias.javaEnabled()) {
					pluginMap.java[2] = '1';
				}

				// Firefox
				if (isFunction(windowAlias.GearsFactory)) {
					pluginMap.gears[2] = '1';
				}

				if (navigatorAlias.mimeTypes && navigatorAlias.mimeTypes.length) {
					for (i in pluginMap) {
						mimeType = navigatorAlias.mimeTypes[pluginMap[i][1]];
						if (mimeType && mimeType.enabledPlugin) {
							pluginMap[i][2] = '1';
						}
					}
				}
			}

			/*
			 * Inits the custom variables object
			 */
			function getCustomVariablesFromCookie()
			{
				name = getCookieName('cvar');
				cookie = getCookie(name);
				if(cookie.length)
				{
					cookie = json_parse(cookie);
					if(isObject(cookie)) {
						return cookie;
					}
				}
				return {};
			}
			
			/*
			 * Lazy loads the custom variables from the cookie, only once during this page view
			 */
			function loadCustomVariables()
			{
				if(customVariables == false)
				{
					customVariables = getCustomVariablesFromCookie();
				}
			}
			
			/*
			 * Get cookie name with prefix and domain hash
			 */
			function getCookieName(baseName) {
				return configCookieNamePrefix + baseName + '.' + domainHash;
			}

			/*
			 * Does browser have cookies enabled (for this site)?
			 */
			function hasCookies() {
				var testCookieName = getCookieName('testcookie');

				if (!isDefined(navigatorAlias.cookieEnabled)) {
					setCookie(testCookieName, '1');
					return getCookie(testCookieName) === '1' ? '1' : '0';
				}

				return navigatorAlias.cookieEnabled ? '1' : '0';
			}

			/*
			 * Update domain hash
			 */
			function updateDomainHash() {
				domainHash = hash((configCookieDomain || domainAlias) + (configCookiePath || '/')).substring(0, 8); // 8 hexits = 32 bits
			}

			/*
			 * Returns the URL to call piwik.php, 
			 * with the standard parameters (plugins, resolution, url, referer, etc.).
			 * Sends the pageview and browser settings with every request in case of race conditions.
			 */
			function getRequest(customData, pluginMethod) {
				var i,
				now = new Date(), nowTs = Math.round(now.getTime() / 1000),
				tmpContainer, newVisitor, uuid, visitCount, createTs, currentVisitTs, lastVisitTs, referralTs, referralUrl, currentRefererHostName, originalRefererHostName,
				idname = getCookieName('id'),
				sesname = getCookieName('ses'),
				refname = getCookieName('ref'),
				id = getCookie(idname),
				ses = getCookie(sesname),
				ref = getCookie(refname),
				request = '&res=' + screenAlias.width + 'x' + screenAlias.height + '&cookie=' + browserHasCookies;

				for (i in pluginMap) {
					request += '&' + pluginMap[i][0] + '=' + pluginMap[i][2];
				}

				if (id) {
					// returning visitor
					newVisitor = '0';
					tmpContainer = id.split('.');
					uuid = tmpContainer[0];
					createTs = tmpContainer[1];
					visitCount = tmpContainer[2];
					currentVisitTs = tmpContainer[3];
					lastVisitTs = tmpContainer[4];
				} else {
					// new visitor
					newVisitor = '1';

					// seconds since Unix epoch
					createTs = nowTs;

					// no previous visit
					lastVisitTs = '';

					// generate a pseudo-unique ID to fingerprint this user;
					// note: this isn't a RFC4122-compliant UUID
					uuid = hash(
							(isDefined(navigatorAlias.userAgent) ? navigatorAlias.userAgent : '') +
							(isDefined(navigatorAlias.platform) ? navigatorAlias.platform : '') +
							request + Math.round(now.getTime / 1000)
						).substring(0, 16); // 16 hexits = 64 bits

					visitCount = 0;
				}

				if (ref) {
					tmpContainer = ref.split(' ');
					referralTs = tmpContainer[0];
					referralUrl = tmpContainer[1];
				}

				if (!ses) {
					// new session (aka new visit)
					visitCount++;

					lastVisitTs = currentVisitTs;

					// the referral URL depends on the first or last referrer attribution
					currentRefererHostName = getHostName(configReferrerUrl);
					originalRefererHostName = ref ? getHostName(ref) : '';
					if (currentRefererHostName.length && // there is a referer
							!isSiteHostName(currentRefererHostName) && // domain is not the current domain
							(!configConversionAttributionFirstReferer || // attribute to last known referer
							!originalRefererHostName.length || // previously empty
							isSiteHostName(originalRefererHostName))) { // previously set but in current domain
						// record this referral
						referralTs = nowTs;
						referralUrl = configReferrerUrl;

						// set the referral cookie
						setCookie(refname, referralTs + ' ' + referralUrl, configReferralCookieTimeout, configCookiePath, configCookieDomain);
					}

					// send heart beat
					if (configHeartBeatTimer) {
						setTimeout(function () {
							this.logLink('1', 'ping', customData);
						}, configHeartBeatTimer);
					}
				}

				currentVisitTs = nowTs;
				customVariablesString = stringify(customVariables);
				
				// update other cookies
				setCookie(idname, uuid + '.' + createTs + '.' + visitCount + '.' + currentVisitTs + '.' + lastVisitTs, configVisitorCookieTimeout, configCookiePath, configCookieDomain);
				setCookie(sesname, '*', configSessionCookieTimeout, configCookiePath, configCookieDomain);
				setCookie(getCookieName('cvar'), customVariablesString, configSessionCookieTimeout, configCookiePath, configCookieDomain);
				
				// build out the rest of the request
				request = 'idsite=' + configTrackerSiteId +
					'&rec=1' + 
					'&rand=' + Math.random() +
					'&h=' + now.getHours() + '&m=' + now.getMinutes() + '&s=' + now.getSeconds() +
					'&url=' + encodeWrapper(purify(configCustomUrl || locationHrefAlias)) +
					'&urlref=' + encodeWrapper(purify(configReferrerUrl)) +
					'&_id=' + uuid + '&_idts=' + createTs + '&_idvc=' + visitCount + '&_idn=' + newVisitor +
					'&_ref=' + encodeWrapper(purify(referralUrl)) +
					'&_refts=' + referralTs +
					'&_viewts=' + lastVisitTs +
					'&_ses=' + (configServerCookies ? 0 : 1) +
					'&_cvar=' + customVariablesString
					request;

				// custom data
				if (customData) {
					request += '&data=' + encodeWrapper(stringify(customData));
				} else if (configCustomData) {
					request += '&data=' + encodeWrapper(stringify(configCustomData));
				}

				// tracker plugin hook
				request += executePluginMethod(pluginMethod);

				return request;
			}

			/*
			 * Log the page view / visit
			 */
			function logPageView(customTitle, customData) {
				var request = getRequest(customData, 'log') +
					'&action_name=' + encodeWrapper(customTitle || configTitle); // refs #530;

				sendRequest(request, configTrackerPause);
			}

			/*
			 * Log the goal with the server
			 */
			function logGoal(idGoal, customRevenue, customData) {
				var request = getRequest(customData, 'goal') +
					'&idgoal=' + idGoal;

				// custom revenue
				if (customRevenue) {
					request += '&revenue=' + customRevenue;
				}

				sendRequest(request, configTrackerPause);
			}
			
			/*
			 * Log the link or click  with the server
			 */
			function logLink(url, linkType, customData) {
				var request = getRequest(customData, 'click') +
					'&' + linkType + '=' + encodeWrapper(purify(url)) +
					'&redirect=0';

				sendRequest(request, configTrackerPause);
			}

			/*
			 * Construct regular expression of classes
			 */
			function getClassesRegExp(configClasses, defaultClass) {
				var i, classesRegExp = '(^| )(piwik[_-]' + defaultClass;

				if (configClasses) {
					for (i = 0; i < configClasses.length; i++) {
						classesRegExp += '|' + configClasses[i];
					}
				}
				classesRegExp += ')( |$)';

				return new RegExp(classesRegExp);
			}

			/*
			 * Link or Download?
			 */
			function getLinkType(className, href, isInLink) {
				// outlinks
				if (!isInLink) {
					return 'link';
				}

				// does class indicate whether it is an (explicit/forced) outlink or a download?
				var downloadPattern = getClassesRegExp(configDownloadClasses, 'download'),
				linkPattern = getClassesRegExp(configLinkClasses, 'link'),

				// does file extension indicate that it is a download?
				downloadExtensionsPattern = new RegExp('\\.(' + configDownloadExtensions + ')([?&#]|$)', 'i');

				// optimization of the if..elseif..else construct below
				return linkPattern.test(className) ? 'link' : (downloadPattern.test(className) || downloadExtensionsPattern.test(href) ? 'download' : 0);

/*
				var linkType;

				if (linkPattern.test(className)) {
					// class attribute contains 'piwik_link' (or user's override)
					linkType = 'link';
				} else if (downloadPattern.test(className)) {
					// class attribute contains 'piwik_download' (or user's override)
					linkType = 'download';
				} else if (downloadExtensionsPattern.test(sourceHref)) {
					// file extension matches a defined download extension
					linkType = 'download';
				} else {
					// otherwise none of the above
					linkType = 0;
				}

				return linkType;
 */
			}

			/*
			 * Process clicks
			 */
			function processClick(sourceElement) {
				var parentElement, tag, linkType;

				while ((parentElement = sourceElement.parentNode) &&
						((tag = sourceElement.tagName) !== 'A' && tag !== 'AREA')) {
					sourceElement = parentElement;
				}

				if (isDefined(sourceElement.href)) {
					// browsers, such as Safari, don't downcase hostname and href
					var originalSourceHostName = sourceElement.hostname,
						sourceHostName = originalSourceHostName.toLowerCase(),
						sourceHref = sourceElement.href.replace(originalSourceHostName, sourceHostName),
						scriptProtocol = new RegExp('^(javascript|vbscript|jscript|mocha|livescript|ecmascript): *', 'i');

					// ignore script pseudo-protocol links
					if (!scriptProtocol.test(sourceHref)) {
						// track outlinks and all downloads
						linkType = getLinkType(sourceElement.className, sourceHref, isSiteHostName(sourceHostName));
						if (linkType) {
							logLink(sourceHref, linkType);
						}
					}
				}
			}

			/*
			 * Handle click event
			 */
			function clickHandler(evt) {
				var button, target;

				evt = evt || windowAlias.event;
				button = evt.which || evt.button;
				target = evt.target || evt.srcElement;

				// Using evt.type (added in IE4), we avoid defining separate handlers for mouseup and mousedown.
				if (evt.type === 'click') {
					if (target) {
						processClick(target);
					}
				} else if (evt.type === 'mousedown') {
					if ((button === 1 || button === 2) && target) {
						lastButton = button;
						lastTarget = target;
					} else {
						lastButton = lastTarget = null;
					}
				} else if (evt.type === 'mouseup') {
					if (button === lastButton && target === lastTarget) {
						processClick(target);
					}
					lastButton = lastTarget = null;
				}
			}

			/*
			 * Add click listener to a DOM element
			 */
			function addClickListener(element, enable) {
				if (enable) {
					// for simplicity and performance, we ignore drag events
					addEventListener(element, 'mouseup', clickHandler, false);
					addEventListener(element, 'mousedown', clickHandler, false);
				} else {
					addEventListener(element, 'click', clickHandler, false);
				}
			}

			/*
			 * Add click handlers to anchor and AREA elements, except those to be ignored
			 */
			function addClickListeners(enable) {
				if (!linkTrackingInstalled) {
					linkTrackingInstalled = true;

					// iterate through anchor elements with href and AREA elements

					var i, ignorePattern = getClassesRegExp(configIgnoreClasses, 'ignore'), linkElements = documentAlias.links;

					if (linkElements) {
						for (i = 0; i < linkElements.length; i++) {
							if (!ignorePattern.test(linkElements[i].className)) {
								addClickListener(linkElements[i], enable);
							}
						}
					}
				}
			}

/*<DEBUG>*/
			/*
			 * Register a test hook.  Using eval() permits access to otherwise
			 * privileged membmers.
			 */
			function registerHook(hookName, userHook) {
				var hookObj = null;

				if (isString(hookName) && !isDefined(registeredHooks[hookName]) && userHook) {
					if (isObject(userHook)) {
						hookObj = userHook;
					} else if (isString(userHook)) {
						try {
							eval('hookObj =' + userHook);
						} catch (e) { }
					}

					registeredHooks[hookName] = hookObj;
				}
				return hookObj;
			}
/*</DEBUG>*/

			/************************************************************
			 * Constructor
			 ************************************************************/

			/*
			 * initialize tracker
			 */
			browserHasCookies = hasCookies();
			detectBrowserPlugins();
			updateDomainHash();

/*<DEBUG>*/
			/*
			 * initialize test plugin
			 */
			executePluginMethod('run', registerHook);
/*</DEBUG>*/

			/************************************************************
			 * Public data and methods
			 ************************************************************/

			return {
/*<DEBUG>*/
				/*
				 * Test hook accessors
				 */
				hook: registeredHooks,
				getHook: function (hookName) {
					return registeredHooks[hookName];
				},
/*</DEBUG>*/

				/**
				 * Get visitor ID (from first party cookie)
				 *
				 * @return string Visitor ID in hexits (or null, if not yet known)
				 */
				getVisitorId: function () {
					return visitorId;
				},

				/**
				 * Specify the Piwik server URL
				 *
				 * @param string trackerUrl
				 */
				setTrackerUrl: function (trackerUrl) {
					configTrackerUrl = trackerUrl;
				},

				/**
				 * Specify the site ID
				 *
				 * @param int|string siteId
				 */
				setSiteId: function (siteId) {
					configTrackerSiteId = siteId;
				},

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
				setCustomData: function (key_or_obj, opt_value) {
					if (isObject(key_or_obj)) {
						configCustomData = key_or_obj;
					} else {
						if (!configCustomData) {
							configCustomData = [];
						}
						configCustomData[key_or_obj] = opt_value;
					}
				},

				/**
				 * Get custom data
				 *
				 * @return mixed
				 */
				getCustomData: function () {
					return configCustomData;
				},

				/**
				 * Set custom variable to this visit
				 *
				 * @param int index
				 * @param string name
				 * @param string value
				 */
				setCustomVariable: function (index, name, value) {
					loadCustomVariables();
					if(index > 0 && index <= 5)
					{
						customVariables[index] = [name.substring(0, customVariableMaximumLength), value.substring(0, customVariableMaximumLength)];
					}
				},

				/**
				 * Get custom variable
				 *
				 * @param int slotId
				 */
				getCustomVariable: function (index) {
					loadCustomVariables();
					return customVariables[index];
				},
				
				/**
				 * Delete custom variable
				 *
				 * @param int slotId
				 */
				deleteCustomVariable: function (index) {
					setCustomVariable(index, "", "");
				},

				/**
				 * Set delay for link tracking (in milliseconds)
				 *
				 * @param int delay
				 */
				setLinkTrackingTimer: function (delay) {
					configTrackerPause = delay;
				},

				/**
				 * Set list of file extensions to be recognized as downloads
				 *
				 * @param string extensions
				 */
				setDownloadExtensions: function (extensions) {
					configDownloadExtensions = extensions;
				},

				/**
				 * Specify additional file extensions to be recognized as downloads
				 *
				 * @param string extensions
				 */
				addDownloadExtensions: function (extensions) {
					configDownloadExtensions += '|' + extensions;
				},

				/**
				 * Set array of domains to be treated as local
				 *
				 * @param string|array hostsAlias
				 */
				setDomains: function (hostsAlias) {
					configHostsAlias = isString(hostsAlias) ? [hostsAlias] : hostsAlias;
					configHostsAlias.push(domainAlias);
				},

				/**
				 * Set array of classes to be ignored if present in link
				 *
				 * @param string|array ignoreClasses
				 */
				setIgnoreClasses: function (ignoreClasses) {
					configIgnoreClasses = isString(ignoreClasses) ? [ignoreClasses] : ignoreClasses;
				},

				/**
				 * Set request method
				 *
				 * @param string method GET or POST; default is GET
				 */
				setRequestMethod: function (method) {
					configRequestMethod = method || 'GET';
				},

				/**
				 * Override referrer
				 *
				 * @param string url
				 */
				setReferrerUrl: function (url) {
					configReferrerUrl = url;
				},

				/**
				 * Override url
				 *
				 * @param string url
				 */
				setCustomUrl: function (url) {
					configCustomUrl = url;
				},

				/**
				 * Override document.title
				 *
				 * @param string title
				 */
				setDocumentTitle: function (title) {
					configTitle = title;
				},

				/**
				 * Set array of classes to be treated as downloads
				 *
				 * @param string|array downloadClasses
				 */
				setDownloadClasses: function (downloadClasses) {
					configDownloadClasses = isString(downloadClasses) ? [downloadClasses] : downloadClasses;
				},

				/**
				 * Set array of classes to be treated as outlinks
				 *
				 * @param string|array linkClasses
				 */
				setLinkClasses: function (linkClasses) {
					configLinkClasses = isString(linkClasses) ? [linkClasses] : linkClasses;
				},

				/**
				 * Strip hash tag (or anchor) from URL
				 *
				 * @param bool enableFilter
				 */
				discardHashTag: function (enableFilter) {
					configDiscardHashTag = enableFilter;
				},

				/**
				 * Set first-party cookie name prefix
				 *
				 * @param string cookieNamePrefix
				 */
				setCookieNamePrefix: function (cookieNamePrefix) {
					configCookieNamePrefix = cookieNamePrefix;
					// Re-init the Custom Variables cookie
					customVariables = getCustomVariablesFromCookie();
				},

				/**
				 * Set first-party cookie domain
				 *
				 * @param string domain
				 */
				setCookieDomain: function (domain) {
					configCookieDomain = domainFixup(domain);
					updateDomainHash();
				},

				/**
				 * Set first-party cookie path
				 *
				 * @param string domain
				 */
				setCookiePath: function (path) {
					configCookiePath = path;
					updateDomainHash();
				},

				/**
				 * Set visitor cookie timeout (in milliseconds)
				 *
				 * @param int timeout
				 */
				setVisitorCookieTimeout: function (timeout) {
					configVisitorCookieTimeout = timeout;
				},

				/**
				 * Set session cookie timeout (in milliseconds)
				 *
				 * @param int timeout
				 */
				setSessionCookieTimeout: function (timeout) {
					configSessionCookieTimeout = timeout;
				},

				/**
				 * Set referral cookie timeout (in milliseconds)
				 *
				 * @param int timeout
				 */
				setReferralCookieTimeout: function (timeout) {
					configReferralCookieTimeout = timeout;
				},

				/**
				 * Set conversion attribution to first referer
				 *
				 * @param bool enable If true, use first referer; if false, use the last referer
				 */
				setConversionAttributionFirstReferer: function (enable) {
					configConversionAttributionFirstReferer = enable;
				},

				/**
				 * Add click listener to a specific link element.
				 * When clicked, Piwik will log the click automatically.
				 *
				 * @param DOMElement element
				 * @param bool enable If true, use pseudo click-handler (mousedown+mouseup)
				 */
				addListener: function (element, enable) {
					addClickListener(element, enable);
				},

				/**
				 * Install link tracker
				 *
				 * The default behaviour is to use actual click events.  However, some browsers
				 * (e.g., Firefox, Opera, and Konqueror) don't generate click events for the middle mouse button.
				 *
				 * To capture more "clicks", the pseudo click-handler uses mousedown + mouseup events.
				 * This is not industry standard and is vulnerable to false positives (e.g., drag events).
				 *
				 * @param bool enable If true, use pseudo click-handler (mousedown+mouseup)
				 */
				enableLinkTracking: function (enable) {
					if (hasLoaded) {
						// the load event has already fired, add the click listeners now
						addClickListeners(enable);
					} else {
						// defer until page has loaded
						registeredOnLoadHandlers[registeredOnLoadHandlers.length] = function () {
							addClickListeners(enable);
						};
					}
				},

				/**
				 * Enable third-party server cookies
				 *
				 * @param bool enable
				 */
				enableServerCookies: function (enable) {
					configServerCookies = enable;
				},

				/**
				 * Set heartbeat (in milliseconds)
				 *
				 * @param int delay
				 */
				setHeartBeatTimer: function (delay) {
					configHeartBeatTimer = delay;
				},

				/**
				 * Frame buster
				 */
				killFrame: function () {
					if (windowAlias !== top) {
						top.location = windowAlias.location;
					}
				},

				/**
				 * Redirect if browsing offline (aka file: buster)
				 *
				 * @param string url Redirect to this URL
				 */
				redirectFile: function (url) {
					if (windowAlias.location.protocol === 'file:') {
						windowAlias.location = url;
					}
				},

				/**
				 * Trigger a goal
				 *
				 * @param int|string idGoal
				 * @param int|float customRevenue
				 * @param mixed customData
				 */
				trackGoal: function (idGoal, customRevenue, customData) {
					logGoal(idGoal, customRevenue, customData);
				},

				/**
				 * Manually log a click from your own code
				 *
				 * @param string sourceUrl
				 * @param string linkType
				 * @param mixed customData
				 */
				trackLink: function (sourceUrl, linkType, customData) {
					logLink(sourceUrl, linkType, customData);
				},

				/**
				 * Log visit to this page
				 *
				 * @param string customTitle
				 * @param mixed customData
				 */
				trackPageView: function (customTitle, customData) {
					logPageView(customTitle, customData);
				}
			};
		}
		
		/************************************************************
		 * Proxy object
		 * - this allows the caller to continue push()'ing to _paq
		 *   after the Tracker has been initialized and loaded
		 ************************************************************/

		function TrackerProxy() {
			return {
				push: apply
			};
		}

		/************************************************************
		 * Constructor
		 ************************************************************/

		// initialize the Piwik singleton
		addEventListener(windowAlias, 'beforeunload', beforeUnloadHandler, false);
		addReadyListener();

		asyncTracker = new Tracker();

		for (i = 0; i < _paq.length; i++) {
			apply(_paq[i]);
		}

		// replace initialization array with proxy object
		_paq = new TrackerProxy();

		/************************************************************
		 * Public data and methods
		 ************************************************************/

		return {
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
				return new Tracker(piwikUrl, siteId);
			}
		};
	}());
