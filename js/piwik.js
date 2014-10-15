/*!
 * Piwik - free/libre analytics platform
 *
 * JavaScript tracking client
 *
 * @link http://piwik.org
 * @source https://github.com/piwik/piwik/blob/master/js/piwik.js
 * @license http://piwik.org/free-software/bsd/ BSD-3 Clause (also in js/LICENSE.txt)
 * @license magnet:?xt=urn:btih:c80d50af7d3db9be66a4d0a86db0286e4fd33292&dn=bsd-3-clause.txt BSD-3-Clause
 */
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

/************************************************************
 * JSON - public domain reference implementation by Douglas Crockford
 * @version 2012-10-08
 * @link http://www.JSON.org/js.html
 ************************************************************/
/*jslint evil: true, regexp: false, bitwise: true, white: true */
/*global JSON2:true */
/*members "", "\b", "\t", "\n", "\f", "\r", "\"", "\\", apply,
    call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
    getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
    lastIndex, length, parse, prototype, push, replace, sort, slice, stringify,
    test, toJSON, toString, valueOf,
    objectToJSON
*/

// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

if (typeof JSON2 !== 'object') {
    JSON2 = {};
}

(function () {
    'use strict';

    function f(n) {
        // Format integers to have at least two digits.
        return n < 10 ? '0' + n : n;
    }

    function objectToJSON(value, key) {
        var objectType = Object.prototype.toString.apply(value);

        if (objectType === '[object Date]') {
            return isFinite(value.valueOf())
                ?  value.getUTCFullYear()     + '-' +
                    f(value.getUTCMonth() + 1) + '-' +
                    f(value.getUTCDate())      + 'T' +
                    f(value.getUTCHours())     + ':' +
                    f(value.getUTCMinutes())   + ':' +
                    f(value.getUTCSeconds())   + 'Z'
                : null;
        }

        if (objectType === '[object String]' ||
                objectType === '[object Number]' ||
                objectType === '[object Boolean]') {
            return value.valueOf();
        }

        if (objectType !== '[object Array]' &&
                typeof value.toJSON === 'function') {
            return value.toJSON(key);
        }

        return value;
    }

    var cx = new RegExp('[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]', 'g'),
    // hack: workaround Snort false positive (sid 8443)
        pattern = '\\\\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]',
        escapable = new RegExp('[' + pattern, 'g'),
        gap,
        indent,
        meta = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;

    function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

        escapable.lastIndex = 0;

        return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
            var c = meta[a];

            return typeof c === 'string'
                ? c
                : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
        }) + '"' : '"' + string + '"';
    }

    function str(key, holder) {

// Produce a string from holder[key].

        var i,          // The loop counter.
            k,          // The member key.
            v,          // The member value.
            length,
            mind = gap,
            partial,
            value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

        if (value && typeof value === 'object') {
            value = objectToJSON(value, key);
        }

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
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

// If the type is 'object', we might be dealing with an object or an array or
// null.

        case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

            if (!value) {
                return 'null';
            }

// Make an array to hold the partial results of stringifying this object value.

            gap += indent;
            partial = [];

// Is the value an array?

            if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

                v = partial.length === 0
                    ? '[]'
                    : gap
                    ?  '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']'
                    : '[' + partial.join(',') + ']';
                gap = mind;

                return v;
            }

// If the replacer is an array, use it to select the members to be stringified.

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    if (typeof rep[i] === 'string') {
                        k = rep[i];
                        v = str(k, value);

                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {

// Otherwise, iterate through all of the keys in the object.

                for (k in value) {
                    if (Object.prototype.hasOwnProperty.call(value, k)) {
                        v = str(k, value);

                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

            v = partial.length === 0
                ? '{}'
                : gap
                ?  '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}'
                : '{' + partial.join(',') + '}';
            gap = mind;

            return v;
        }
    }

// If the JSON object does not yet have a stringify method, give it one.

    if (typeof JSON2.stringify !== 'function') {
        JSON2.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

            var i;
            gap = '';
            indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

// If the space parameter is a string, it will be used as the indent string.

            } else if (typeof space === 'string') {
                indent = space;
            }

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

            rep = replacer;

            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                    typeof replacer.length !== 'number')) {
                throw new Error('JSON2.stringify');
            }

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

            return str('', {'': value});
        };
    }

// If the JSON object does not yet have a parse method, give it one.

    if (typeof JSON2.parse !== 'function') {
        JSON2.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

            var j;

            function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

                var k, v, value = holder[key];

                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.prototype.hasOwnProperty.call(value, k)) {
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
            }

// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

            text = String(text);
            cx.lastIndex = 0;

            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

            if ((new RegExp('^[\\],:{}\\s]*$'))
                    .test(text.replace(new RegExp('\\\\(?:["\\\\/bfnrt]|u[0-9a-fA-F]{4})', 'g'), '@')
                        .replace(new RegExp('"[^"\\\\\n\r]*"|true|false|null|-?\\d+(?:\\.\\d*)?(?:[eE][+\\-]?\\d+)?', 'g'), ']')
                        .replace(new RegExp('(?:^|:|,)(?:\\s*\\[)+', 'g'), ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

                return typeof reviver === 'function'
                    ?  walk({'': j}, '')
                    : j;
            }

// If the text is not JSON parseable, then a SyntaxError is thrown.

            throw new SyntaxError('JSON2.parse');
        };
    }
}());
/************************************************************
 * end JSON
 ************************************************************/

/*jslint browser:true, plusplus:true, vars:true, nomen:true, evil:true */
/*global window */
/*global unescape */
/*global ActiveXObject */
/*members encodeURIComponent, decodeURIComponent, getElementsByTagName,
    shift, unshift, piwikAsyncInit,
    createElement, appendChild, characterSet, charset,
    addEventListener, attachEvent, removeEventListener, detachEvent, disableCookies,
    cookie, domain, readyState, documentElement, doScroll, title, text,
    location, top, onerror, document, referrer, parent, links, href, protocol, name, GearsFactory,
    performance, mozPerformance, msPerformance, webkitPerformance, timing, requestStart,
    responseEnd, event, which, button, srcElement, type, target,
    parentNode, tagName, hostname, className,
    userAgent, cookieEnabled, platform, mimeTypes, enabledPlugin, javaEnabled,
    XMLHttpRequest, ActiveXObject, open, setRequestHeader, onreadystatechange, send, readyState, status,
    getTime, getTimeAlias, setTime, toGMTString, getHours, getMinutes, getSeconds,
    toLowerCase, toUpperCase, charAt, indexOf, lastIndexOf, split, slice,
    onload, src,
    round, random,
    exec,
    res, width, height, devicePixelRatio,
    pdf, qt, realp, wma, dir, fla, java, gears, ag,
    hook, getHook, getVisitorId, getVisitorInfo, setUserId, getUserId, setSiteId, setTrackerUrl, appendToTrackingUrl, getRequest, addPlugin,
    getAttributionInfo, getAttributionCampaignName, getAttributionCampaignKeyword,
    getAttributionReferrerTimestamp, getAttributionReferrerUrl,
    setCustomData, getCustomData,
    setCustomRequestProcessing,
    setCustomVariable, getCustomVariable, deleteCustomVariable, storeCustomVariablesInCookie,
    setDownloadExtensions, addDownloadExtensions,
    setDomains, setIgnoreClasses, setRequestMethod, setRequestContentType,
    setReferrerUrl, setCustomUrl, setAPIUrl, setDocumentTitle,
    setDownloadClasses, setLinkClasses,
    setCampaignNameKey, setCampaignKeywordKey,
    discardHashTag,
    setCookieNamePrefix, setCookieDomain, setCookiePath, setVisitorIdCookie,
    setVisitorCookieTimeout, setSessionCookieTimeout, setReferralCookieTimeout,
    setConversionAttributionFirstReferrer,
    disablePerformanceTracking, setGenerationTimeMs,
    doNotTrack, setDoNotTrack, msDoNotTrack,
    addListener, enableLinkTracking, enableJSErrorTracking, setLinkTrackingTimer,
    setHeartBeatTimer, killFrame, redirectFile, setCountPreRendered,
    trackGoal, trackLink, trackPageView, trackSiteSearch, trackEvent,
    setEcommerceView, addEcommerceItem, trackEcommerceOrder, trackEcommerceCartUpdate,
    deleteCookies, offsetTop, offsetLeft, offsetHeight, offsetWidth, nodeType, defaultView,
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
    getQuery, getContent, getContentImpressionsRequestsFromNodes, buildContentInteractionTrackingRedirectUrl,
    buildContentInteractionRequestNode, buildContentInteractionRequest, buildContentImpressionRequest,
    appendContentInteractionToRequestIfPossible, setupInteractionsTracking, trackContentImpressionClickInteraction,
    internalIsNodeVisible, clearTrackedContentImpressions, getTrackerUrl, trackAllContentImpressions,
    getTrackedContentImpressions, getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet,
    contentInteractionTrackingSetupDone, contains, match, pathname, piece, trackContentInteractionNode,
    trackContentInteractionNode, trackContentImpressionsWithinNode, trackContentImpression,
    enableTrackOnlyVisibleContent, trackContentInteraction, clearEnableTrackOnlyVisibleContent,
    trackVisibleContentImpressions, isTrackOnlyVisibleContentEnabled, port, isUrlToCurrentDomain,
    isNodeAuthorizedToTriggerInteraction, replaceHrefIfInternalLink, getConfigDownloadExtensions, disableLinkTracking,
    substr, setAnyAttribute, wasContentTargetAttrReplaced, max, abs, childNodes, compareDocumentPosition, body,
    getConfigVisitorCookieTimeout, getRemainingVisitorCookieTimeout
 */
/*global _paq:true */
/*members push */
/*global Piwik:true */
/*members addPlugin, getTracker, getAsyncTracker */
/*global Piwik_Overlay_Client */
/*global AnalyticsTracker:true */
/*members initialize */
/*global define */
/*members amd */
/*global console:true */
/*members error */

// asynchronous tracker (or proxy)
if (typeof _paq !== 'object') {
    _paq = [];
}

// Piwik singleton and namespace
if (typeof Piwik !== 'object') {
    Piwik = (function () {
        'use strict';

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

            /* performance timing */
            performanceAlias = windowAlias.performance || windowAlias.mozPerformance || windowAlias.msPerformance || windowAlias.webkitPerformance,

            /* DOM Ready */
            hasLoaded = false,
            registeredOnLoadHandlers = [],

            /* encode */
            encodeWrapper = windowAlias.encodeURIComponent,

            /* decode */
            decodeWrapper = windowAlias.decodeURIComponent,

            /* urldecode */
            urldecode = unescape,

            /* asynchronous tracker */
            asyncTracker,

            /* iterator */
            iterator,

            /* local Piwik */
            Piwik;

        /************************************************************
         * Private methods
         ************************************************************/

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

        /*
         * apply wrapper
         *
         * @param array parameterArray An array comprising either:
         *      [ 'methodName', optional_parameters ]
         * or:
         *      [ functionObject, optional_parameters ]
         */
        function apply() {
            var i, f, parameterArray;

            for (i = 0; i < arguments.length; i += 1) {
                parameterArray = arguments[i];
                f = parameterArray.shift();

                if (isString(f)) {
                    asyncTracker[f].apply(asyncTracker, parameterArray);
                } else {
                    f.apply(asyncTracker, parameterArray);
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

        /*
         * Call plugin hook methods
         */
        function executePluginMethod(methodName, callback) {
            var result = '',
                i,
                pluginMethod;

            for (i in plugins) {
                if (Object.prototype.hasOwnProperty.call(plugins, i)) {
                    pluginMethod = plugins[i][methodName];

                    if (isFunction(pluginMethod)) {
                        result += pluginMethod(callback);
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
            var _timer;

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

                if (documentAlias.documentElement.doScroll && windowAlias === windowAlias.top) {
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

            // sniff for older WebKit versions
            if ((new RegExp('WebKit')).test(navigatorAlias.userAgent)) {
                _timer = setInterval(function () {
                    if (hasLoaded || /loaded|complete/.test(documentAlias.readyState)) {
                        clearInterval(_timer);
                        loadHandler();
                    }
                }, 10);
            }

            // fallback
            addEventListener(windowAlias, 'load', loadHandler, false);
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

        /*
         * Extract parameter from URL
         */
        function getParameter(url, name) {
            var regexSearch = "[\\?&#]" + name + "=([^&#]*)";
            var regex = new RegExp(regexSearch);
            var results = regex.exec(url);
            return results ? decodeWrapper(results[1]) : '';
        }

        /*
         * UTF-8 encoding
         */
        function utf8_encode(argString) {
            return urldecode(encodeWrapper(argString));
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
            if (hostName === 'translate.googleusercontent.com') {       // Google
                if (referrer === '') {
                    referrer = href;
                }

                href = getParameter(href, 'u');
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
            hasNodeCssClass: function (node, className)
            {
                if (node && className && node.className) {
                    var classes = node.className.split(' ');
                    if (-1 !== indexOfArray(classes, className)) {
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
                if (text && String(text) === text) {
                    return text.replace(/^\s+|\s+$/g, '');
                }

                return text;
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

            if (trackerUrl.slice(-9) === 'piwik.php') {
                trackerUrl = trackerUrl.slice(0, trackerUrl.length - 9);
            }

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
                               + '&idSite=([0-9]+)&period=([^&]+)&date=([^&]+)$');

            var match = referrerRegExp.exec(documentAlias.referrer);

            if (match) {
                // check idsite
                var idsite = match[1];

                if (idsite !== String(configTrackerSiteId)) {
                    return false;
                }

                // store overlay session info in window name
                var period = match[2],
                    date = match[3];

                windowAlias.name = windowName + '###' + period + '###' + date;
            }

            // retrieve and check data from window name
            var windowNameParts = windowAlias.name.split('###');

            return windowNameParts.length === 3 && windowNameParts[0] === windowName;
        }

        /*
         * Inject the script needed for page overlay
         */
        function injectOverlayScripts(configTrackerUrl, configApiUrl, configTrackerSiteId) {
            var windowNameParts = windowAlias.name.split('###'),
                period = windowNameParts[1],
                date = windowNameParts[2],
                piwikUrl = getPiwikUrlForOverlay(configTrackerUrl, configApiUrl);

            loadScript(
                piwikUrl + 'plugins/Overlay/client/client.js?v=1',
                function () {
                    Piwik_Overlay_Client.initialize(piwikUrl, configTrackerSiteId, period, date);
                }
            );
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
        function Tracker(trackerUrl, siteId, uuid) {

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

                // Document URL
                configCustomUrl,

                // Document title
                configTitle = documentAlias.title,

                // Extensions to be treated as download links
                configDownloadExtensions = '7z|aac|apk|ar[cj]|as[fx]|avi|azw3|bin|csv|deb|dmg|docx?|epub|exe|flv|gif|gz|gzip|hqx|jar|jpe?g|js|mobi|mp(2|3|4|e?g)|mov(ie)?|ms[ip]|od[bfgpst]|og[gv]|pdf|phps|png|pptx?|qtm?|ra[mr]?|rpm|sea|sit|tar|t?bz2?|tgz|torrent|txt|wav|wm[av]|wpd||xlsx?|xml|z|zip',

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

                // Minimum visit time after initial page view (in milliseconds)
                configMinimumVisitTime,

                // Recurring heart beat after initial ping (in milliseconds)
                configHeartBeatTimer,

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

                // First-party cookie domain
                // User agent defaults to origin hostname
                configCookieDomain,

                // First-party cookie path
                // Default is user agent defined.
                configCookiePath,

                // Cookies are disabled
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

                // Guard against installing the activity tracker more than once per Tracker instance
                activityTrackingInstalled = false,

                // Last activity timestamp
                lastActivityTime,

                // Internal state of the pseudo click handler
                lastButton,
                lastTarget,

                // Hash function
                hash = sha1,

                // Domain hash value
                domainHash,

                // Visitor UUID
                visitorUUID = uuid;

            /*
             * Set cookie value
             */
            function setCookie(cookieName, value, msToExpire, path, domain, secure) {
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
                    (secure ? ';secure' : '');
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

            /*
             * Removes hash tag from the URL
             *
             * URLs are purified before being recorded in the cookie,
             * or before being sent as GET parameters
             */
            function purify(url) {
                var targetPattern;

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
                var image = new Image(1, 1);

                image.onload = function () {
                    iterator = 0; // To avoid JSLint warning of empty block
                    if (typeof callback === 'function') { callback(); }
                };
                image.src = configTrackerUrl + (configTrackerUrl.indexOf('?') < 0 ? '?' : '&') + request;
            }

            /*
             * POST request to Piwik server using XMLHttpRequest.
             */
            function sendXmlHttpRequest(request, callback, fallbackToGet) {
                if (!isDefined(fallbackToGet) || null === fallbackToGet) {
                    fallbackToGet = true;
                }

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
                        if (this.readyState === 4 && !(this.status >= 200 && this.status < 300) && fallbackToGet) {
                            getImage(request, callback);
                        } else {
                            if (typeof callback === 'function') { callback(); }
                        }
                    };

                    xhr.setRequestHeader('Content-Type', configRequestContentType);

                    xhr.send(request);
                } catch (e) {
                    if (fallbackToGet) {
                        // fallback
                        getImage(request, callback);
                    }
                }
            }

            function setExpireDateTime(delay) {

                var now  = new Date();
                var time = now.getTime() + delay;

                if (!expireDateTime || time > expireDateTime) {
                    expireDateTime = time;
                }
            }

            function makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(callback)
            {
                var now     = new Date();
                var timeNow = now.getTime();

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

                if (!configDoNotTrack && request) {
                    makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(function () {
                        if (configRequestMethod === 'POST') {
                            sendXmlHttpRequest(request, callback);
                        } else {
                            getImage(request, callback);
                        }

                        setExpireDateTime(delay);
                    });
                }
            }

            function canSendBulkRequest(requests)
            {
                if (configDoNotTrack) {
                    return false;
                }

                return (requests && requests.length);
            }

            /*
             * Send requests using bulk
             */
            function sendBulkRequest(requests, delay)
            {
                if (!canSendBulkRequest(requests)) {
                    return;
                }

                var bulk = '{"requests":["?' + requests.join('","?') + '"]}';

                makeSureThereIsAGapAfterFirstTrackingRequestToPreventMultipleVisitorCreation(function () {
                    sendXmlHttpRequest(bulk, null, false);
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
                    cookie = JSON2.parse(cookie);

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
             * Process all "activity" events.
             * For performance, this function must have low overhead.
             */
            function activityHandler() {
                var now = new Date();

                lastActivityTime = now.getTime();
            }

            /*
             * Load visitor ID cookie
             */
            function loadVisitorIdCookie() {
                var now = new Date(),
                    nowTs = Math.round(now.getTime() / 1000),
                    id = getCookie(getCookieName('id')),
                    tmpContainer;

                if (id) {
                    tmpContainer = id.split('.');

                    // returning visitor flag
                    tmpContainer.unshift('0');

                } else {
                    // uuid - generate a pseudo-unique ID to fingerprint this user;
                    // note: this isn't a RFC4122-compliant UUID
                    if (!visitorUUID) {
                        visitorUUID = hash(
                            (navigatorAlias.userAgent || '') +
                            (navigatorAlias.platform || '') +
                            JSON2.stringify(browserFeatures) +
                            now.getTime() +
                            Math.random()
                        ).slice(0, 16); // 16 hexits = 64 bits
                    }

                    tmpContainer = [
                        // new visitor
                        '1',

                        // uuid
                        visitorUUID,

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
                }

                return tmpContainer;
            }

            function getRemainingVisitorCookieTimeout() {
                var now = new Date(),
                    nowTs = now.getTime(),
                    visitorInfo = loadVisitorIdCookie();

                var createTs = parseInt(visitorInfo[2], 10);
                var originalTimeout = (createTs * 1000) + configVisitorCookieTimeout - nowTs;
                return originalTimeout;
            }

            /*
             * Sets the Visitor ID cookie: either the first time loadVisitorIdCookie is called
             * or when there is a new visit or a new page view
             */
            function setVisitorIdCookie(uuid, createTs, visitCount, nowTs, lastVisitTs, lastEcommerceOrderTs) {
                var timeout = getRemainingVisitorCookieTimeout();

                setCookie(getCookieName('id'), uuid + '.' + createTs + '.' + visitCount + '.' + nowTs + '.' + lastVisitTs + '.' + lastEcommerceOrderTs, timeout, configCookiePath, configCookieDomain);
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
                        cookie = JSON2.parse(cookie);
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

            function deleteCookies() {
                var savedConfigCookiesDisabled = configCookiesDisabled;

                // Temporarily allow cookies just to delete the existing ones
                configCookiesDisabled = false;
                setCookie(getCookieName('id'), '', -86400, configCookiePath, configCookieDomain);
                setCookie(getCookieName('ses'), '', -86400, configCookiePath, configCookieDomain);
                setCookie(getCookieName('cvar'), '', -86400, configCookiePath, configCookieDomain);
                setCookie(getCookieName('ref'), '', -86400, configCookiePath, configCookieDomain);

                configCookiesDisabled = savedConfigCookiesDisabled;
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
             * Returns the URL to call piwik.php,
             * with the standard parameters (plugins, resolution, url, referrer, etc.).
             * Sends the pageview and browser settings with every request in case of race conditions.
             */
            function getRequest(request, customData, pluginMethod, currentEcommerceOrderTs) {
                var i,
                    now = new Date(),
                    nowTs = Math.round(now.getTime() / 1000),
                    newVisitor,
                    uuid,
                    visitCount,
                    createTs,
                    currentVisitTs,
                    lastVisitTs,
                    lastEcommerceOrderTs,
                    referralTs,
                    referralUrl,
                    referralUrlMaxLength = 1024,
                    currentReferrerHostName,
                    originalReferrerHostName,
                    customVariablesCopy = customVariables,
                    sesname = getCookieName('ses'),
                    refname = getCookieName('ref'),
                    cvarname = getCookieName('cvar'),
                    id = loadVisitorIdCookie(),
                    ses = getCookie(sesname),
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

                newVisitor = id[0];
                uuid = id[1];
                createTs = id[2];
                visitCount = id[3];
                currentVisitTs = id[4];
                lastVisitTs = id[5];
                // case migrating from pre-1.5 cookies
                if (!isDefined(id[6])) {
                    id[6] = "";
                }

                lastEcommerceOrderTs = id[6];

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

                if (!ses) {
                    // cookie 'ses' was not found: we consider this the start of a 'session'

                    // here we make sure that if 'ses' cookie is deleted few times within the visit
                    // and so this code path is triggered many times for one visit,
                    // we only increase visitCount once per Visit window (default 30min)
                    var visitDuration = configSessionCookieTimeout / 1000;
                    if (!lastVisitTs
                            || (nowTs - lastVisitTs) > visitDuration) {
                        visitCount++;
                        lastVisitTs = currentVisitTs;
                    }


                    // Detect the campaign information from the current URL
                    // Only if campaign wasn't previously set
                    // Or if it was set but we must attribute to the most recent one
                    // Note: we are working on the currentUrl before purify() since we can parse the campaign parameters in the hash tag
                    if (!configConversionAttributionFirstReferrer
                            || !campaignNameDetected.length) {
                        for (i in configCampaignNameParameters) {
                            if (Object.prototype.hasOwnProperty.call(configCampaignNameParameters, i)) {
                                campaignNameDetected = getParameter(currentUrl, configCampaignNameParameters[i]);

                                if (campaignNameDetected.length) {
                                    break;
                                }
                            }
                        }

                        for (i in configCampaignKeywordParameters) {
                            if (Object.prototype.hasOwnProperty.call(configCampaignKeywordParameters, i)) {
                                campaignKeywordDetected = getParameter(currentUrl, configCampaignKeywordParameters[i]);

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

                        setCookie(refname, JSON2.stringify(attributionCookie), configReferralCookieTimeout, configCookiePath, configCookieDomain);
                    }
                }
                // build out the rest of the request
                request += '&idsite=' + configTrackerSiteId +
                    '&rec=1' +
                    '&r=' + String(Math.random()).slice(2, 8) + // keep the string to a minimum
                    '&h=' + now.getHours() + '&m=' + now.getMinutes() + '&s=' + now.getSeconds() +
                    '&url=' + encodeWrapper(purify(currentUrl)) +
                    (configReferrerUrl.length ? '&urlref=' + encodeWrapper(purify(configReferrerUrl)) : '') +
                    (configUserId.length ? '&uid=' + encodeWrapper(configUserId) : '') +
                    '&_id=' + uuid + '&_idts=' + createTs + '&_idvc=' + visitCount +
                    '&_idn=' + newVisitor + // currently unused
                    (campaignNameDetected.length ? '&_rcn=' + encodeWrapper(campaignNameDetected) : '') +
                    (campaignKeywordDetected.length ? '&_rck=' + encodeWrapper(campaignKeywordDetected) : '') +
                    '&_refts=' + referralTs +
                    '&_viewts=' + lastVisitTs +
                    (String(lastEcommerceOrderTs).length ? '&_ects=' + lastEcommerceOrderTs : '') +
                    (String(referralUrl).length ? '&_ref=' + encodeWrapper(purify(referralUrl.slice(0, referralUrlMaxLength))) : '') +
                    (charSet ? '&cs=' + encodeWrapper(charSet) : '');

                // browser features
                for (i in browserFeatures) {
                    if (Object.prototype.hasOwnProperty.call(browserFeatures, i)) {
                        request += '&' + i + '=' + browserFeatures[i];
                    }
                }

                // custom data
                if (customData) {
                    request += '&data=' + encodeWrapper(JSON2.stringify(customData));
                } else if (configCustomData) {
                    request += '&data=' + encodeWrapper(JSON2.stringify(configCustomData));
                }

                // Custom Variables, scope "page"
                function appendCustomVariablesToRequest(customVariables, parameterName) {
                    var customVariablesStringified = JSON2.stringify(customVariables);
                    if (customVariablesStringified.length > 2) {
                        return '&' + parameterName + '=' + encodeWrapper(customVariablesStringified);
                    }
                    return '';
                }

                var sortedCustomVarPage  = sortObjectByKeys(customVariablesPage);
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
                        setCookie(cvarname, JSON2.stringify(customVariables), configSessionCookieTimeout, configCookiePath, configCookieDomain);
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

                // update cookies
                setVisitorIdCookie(uuid, createTs, visitCount, nowTs, lastVisitTs, isDefined(currentEcommerceOrderTs) && String(currentEcommerceOrderTs).length ? currentEcommerceOrderTs : lastEcommerceOrderTs);
                setCookie(sesname, '*', configSessionCookieTimeout, configCookiePath, configCookieDomain);

                // tracker plugin hook
                request += executePluginMethod(pluginMethod);

                if (configAppendToTrackingUrl.length) {
                    request += '&' + configAppendToTrackingUrl;
                }

                if (isFunction(configCustomRequestContentProcessing)) {
                    request = configCustomRequestContentProcessing(request);
                }

                return request;
            }

            function logEcommerce(orderId, grandTotal, subTotal, tax, shipping, discount) {
                var request = 'idgoal=0',
                    lastEcommerceOrderTs,
                    now = new Date(),
                    items = [],
                    sku;

                if (String(orderId).length) {
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
                    request += '&ec_items=' + encodeWrapper(JSON2.stringify(items));
                }
                request = getRequest(request, configCustomData, 'ecommerce', lastEcommerceOrderTs);
                sendRequest(request, configTrackerPause);
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
            function logPageView(customTitle, customData) {
                var now = new Date(),
                    request = getRequest('action_name=' + encodeWrapper(titleFixup(customTitle || configTitle)), customData, 'log');

                sendRequest(request, configTrackerPause);

                // send ping
                if (configMinimumVisitTime && configHeartBeatTimer && !activityTrackingInstalled) {
                    activityTrackingInstalled = true;

                    // add event handlers; cross-browser compatibility here varies significantly
                    // @see http://quirksmode.org/dom/events
                    addEventListener(documentAlias, 'click', activityHandler);
                    addEventListener(documentAlias, 'mouseup', activityHandler);
                    addEventListener(documentAlias, 'mousedown', activityHandler);
                    addEventListener(documentAlias, 'mousemove', activityHandler);
                    addEventListener(documentAlias, 'mousewheel', activityHandler);
                    addEventListener(windowAlias, 'DOMMouseScroll', activityHandler);
                    addEventListener(windowAlias, 'scroll', activityHandler);
                    addEventListener(documentAlias, 'keypress', activityHandler);
                    addEventListener(documentAlias, 'keydown', activityHandler);
                    addEventListener(documentAlias, 'keyup', activityHandler);
                    addEventListener(windowAlias, 'resize', activityHandler);
                    addEventListener(windowAlias, 'focus', activityHandler);
                    addEventListener(windowAlias, 'blur', activityHandler);

                    // periodic check for activity
                    lastActivityTime = now.getTime();
                    setTimeout(function heartBeat() {
                        var requestPing;
                        now = new Date();

                        // there was activity during the heart beat period;
                        // on average, this is going to overstate the visitDuration by configHeartBeatTimer/2
                        if ((lastActivityTime + configHeartBeatTimer) > now.getTime()) {
                            // send ping if minimum visit time has elapsed
                            if (configMinimumVisitTime < now.getTime()) {
                                requestPing = getRequest('ping=1', customData, 'ping');

                                sendRequest(requestPing, configTrackerPause);
                            }

                            // resume heart beat
                            setTimeout(heartBeat, configHeartBeatTimer);
                        }
                        // else heart beat cancelled due to inactivity
                    }, configHeartBeatTimer);
                }
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
            function getLinkType(className, href, isInLink) {
                if (startsUrlWithTrackerUrl(href)) {
                    return 0;
                }

                // does class indicate whether it is an (explicit/forced) outlink or a download?
                var downloadPattern = getClassesRegExp(configDownloadClasses, 'download'),
                    linkPattern = getClassesRegExp(configLinkClasses, 'link'),

                // does file extension indicate that it is a download?
                    downloadExtensionsPattern = new RegExp('\\.(' + configDownloadExtensions + ')([?&#]|$)', 'i');

                if (linkPattern.test(className)) {
                    return 'link';
                }

                if (downloadPattern.test(className) || downloadExtensionsPattern.test(href)) {
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

                // browsers, such as Safari, don't downcase hostname and href
                var originalSourceHostName = sourceElement.hostname || getHostName(sourceElement.href);
                var sourceHostName = originalSourceHostName.toLowerCase();
                var sourceHref = sourceElement.href.replace(originalSourceHostName, sourceHostName);

                // browsers, such as Safari, don't downcase hostname and href
                var scriptProtocol = new RegExp('^(javascript|vbscript|jscript|mocha|livescript|ecmascript|mailto):', 'i');

                if (!scriptProtocol.test(sourceHref)) {
                    // track outlinks and all downloads
                    var linkType = getLinkType(sourceElement.className, sourceHref, isSiteHostName(sourceHostName));

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

                    requests.push(request);
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
            function logEvent(category, action, name, value, customData)
            {
                // Category and Action are required parameters
                if (String(category).length === 0 || String(action).length === 0) {
                    return false;
                }
                var request = getRequest(
                        buildEventRequest(category, action, name, value),
                        customData,
                        'event'
                    );

                sendRequest(request, configTrackerPause);
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
            function logGoal(idGoal, customRevenue, customData) {
                var request = getRequest('idgoal=' + idGoal + (customRevenue ? '&revenue=' + customRevenue : ''), customData, 'goal');

                sendRequest(request, configTrackerPause);
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

                sendRequest(request, (callback ? 0 : configTrackerPause), callback);
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

            function trackCallbackOnLoad(callback)
            {
                if (documentAlias.readyState === 'complete') {
                    callback();
                } else if (windowAlias.addEventListener) {
                    windowAlias.addEventListener('load', callback);
                } else if (windowAlias.attachEvent) {
                    windowAlias.attachEvent('onLoad', callback);
                }
            }

            function trackCallbackOnReady(callback)
            {
                var loaded = false;

                if (documentAlias.attachEvent) {
                    loaded = documentAlias.readyState === "complete";
                } else {
                    loaded = documentAlias.readyState !== "loading";
                }

                if (loaded) {
                    callback();
                } else if (documentAlias.addEventListener) {
                    documentAlias.addEventListener('DOMContentLoaded', callback);
                } else if (documentAlias.attachEvent) {
                    documentAlias.attachEvent('onreadystatechange', callback);
                }
            }

            /*
             * Process clicks
             */
            function processClick(sourceElement) {
                var link = getLinkIfShouldBeProcessed(sourceElement);

                if (link && link.type) {
                    // urldecode %xx
                    link.href = urldecode(link.href);
                    logLink(link.href, link.type, undefined, null, sourceElement);
                }
            }

            /*
             * Handle click event
             */
            function clickHandler(evt) {
                var button,
                    target;

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

                    var i,
                        ignorePattern = getClassesRegExp(configIgnoreClasses, 'ignore'),
                        linkElements = documentAlias.links;

                    if (linkElements) {
                        for (i = 0; i < linkElements.length; i++) {
                            if (!ignorePattern.test(linkElements[i].className)) {
                                addClickListener(linkElements[i], enable);
                            }
                        }
                    }
                }
            }
            function enableTrackOnlyVisibleContent (checkOnSroll, timeIntervalInMs, tracker) {

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

                    if (checkOnSroll) {

                        // scroll event is executed after each pixel, so we make sure not to
                        // execute event too often. otherwise FPS goes down a lot!
                        events = ['scroll', 'resize'];
                        for (index = 0; index < events.length; index++) {
                            if (documentAlias.addEventListener) {
                                documentAlias.addEventListener(events[index], setDidScroll);
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
                    },
                    devicePixelRatio = (new RegExp('Mac OS X.*Safari/')).test(navigatorAlias.userAgent) ? windowAlias.devicePixelRatio || 1 : 1;

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
                    if (typeof navigator.javaEnabled !== 'unknown' &&
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

                // screen resolution
                // - only Apple reports screen.* in device-independent-pixels (dips)
                // - devicePixelRatio is always 2 on MacOSX+Retina regardless of resolution set in Display Preferences
                browserFeatures.res = screenAlias.width * devicePixelRatio + 'x' + screenAlias.height * devicePixelRatio;
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
/*</DEBUG>*/

            /************************************************************
             * Constructor
             ************************************************************/

            /*
             * initialize tracker
             */
            detectBrowserFeatures();
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
                getQuery: function () {
                    return query;
                },
                getContent: function () {
                    return content;
                },

                buildContentImpressionRequest: buildContentImpressionRequest,
                buildContentInteractionRequest: buildContentInteractionRequest,
                buildContentInteractionRequestNode: buildContentInteractionRequestNode,
                buildContentInteractionTrackingRedirectUrl: buildContentInteractionTrackingRedirectUrl,
                getContentImpressionsRequestsFromNodes: getContentImpressionsRequestsFromNodes,
                getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet: getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet,
                trackCallbackOnLoad: trackCallbackOnLoad,
                trackCallbackOnReady: trackCallbackOnReady,
                buildContentImpressionsRequests: buildContentImpressionsRequests,
                wasContentImpressionAlreadyTracked: wasContentImpressionAlreadyTracked,
                appendContentInteractionToRequestIfPossible: getContentInteractionToRequestIfPossible,
                setupInteractionsTracking: setupInteractionsTracking,
                trackContentImpressionClickInteraction: trackContentImpressionClickInteraction,
                internalIsNodeVisible: isVisible,
                isNodeAuthorizedToTriggerInteraction: isNodeAuthorizedToTriggerInteraction,
                replaceHrefIfInternalLink: replaceHrefIfInternalLink,
                getConfigDownloadExtensions: function () {
                    return configDownloadExtensions;
                },
                enableTrackOnlyVisibleContent: function (checkOnScroll, timeIntervalInMs) {
                    return enableTrackOnlyVisibleContent(checkOnScroll, timeIntervalInMs, this);
                },
                clearTrackedContentImpressions: function () {
                    trackedContentImpressions = [];
                },
                getTrackedContentImpressions: function () {
                    return trackedContentImpressions;
                },
                getTrackerUrl: function () {
                    return configTrackerUrl;
                },
                clearEnableTrackOnlyVisibleContent: function () {
                    isTrackOnlyVisibleContentEnabled = false;
                },
                disableLinkTracking: function () {
                    linkTrackingInstalled = false;
                    linkTrackingEnabled   = false;
                },
                getConfigVisitorCookieTimeout: function () {
                    return configVisitorCookieTimeout;
                },
                getRemainingVisitorCookieTimeout: getRemainingVisitorCookieTimeout,
/*</DEBUG>*/

                /**
                 * Get visitor ID (from first party cookie)
                 *
                 * @return string Visitor ID in hexits (or null, if not yet known)
                 */
                getVisitorId: function () {
                    return (loadVisitorIdCookie())[1];
                },

                /**
                 * Get the visitor information (from first party cookie)
                 *
                 * @return array
                 */
                getVisitorInfo: function () {
                    return loadVisitorIdCookie();
                },

                /**
                 * Get the Attribution information, which is an array that contains
                 * the Referrer used to reach the site as well as the campaign name and keyword
                 * It is useful only when used in conjunction with Tracker API function setAttributionInfo()
                 * To access specific data point, you should use the other functions getAttributionReferrer* and getAttributionCampaign*
                 *
                 * @return array Attribution array, Example use:
                 *   1) Call JSON2.stringify(piwikTracker.getAttributionInfo())
                 *   2) Pass this json encoded string to the Tracking API (php or java client): setAttributionInfo()
                 */
                getAttributionInfo: function () {
                    return loadReferrerAttributionCookie();
                },

                /**
                 * Get the Campaign name that was parsed from the landing page URL when the visitor
                 * landed on the site originally
                 *
                 * @return string
                 */
                getAttributionCampaignName: function () {
                    return loadReferrerAttributionCookie()[0];
                },

                /**
                 * Get the Campaign keyword that was parsed from the landing page URL when the visitor
                 * landed on the site originally
                 *
                 * @return string
                 */
                getAttributionCampaignKeyword: function () {
                    return loadReferrerAttributionCookie()[1];
                },

                /**
                 * Get the time at which the referrer (used for Goal Attribution) was detected
                 *
                 * @return int Timestamp or 0 if no referrer currently set
                 */
                getAttributionReferrerTimestamp: function () {
                    return loadReferrerAttributionCookie()[2];
                },

                /**
                 * Get the full referrer URL that will be used for Goal Attribution
                 *
                 * @return string Raw URL, or empty string '' if no referrer currently set
                 */
                getAttributionReferrerUrl: function () {
                    return loadReferrerAttributionCookie()[3];
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
                 * Sets a User ID to this user (such as an email address or a username)
                 *
                 * @param string User ID
                 */
                setUserId: function (userId) {
                    configUserId = userId;
                },

                /**
                 * Gets the User ID if set.
                 *
                 * @returns string User ID
                 */
                getUserId: function() {
                    return configUserId;
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
                            configCustomData = {};
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
                setCustomRequestProcessing: function (customRequestContentProcessingLogic) {
                    configCustomRequestContentProcessing = customRequestContentProcessingLogic;
                },

                /**
                 * Appends the specified query string to the piwik.php?... Tracking API URL
                 *
                 * @param string queryString eg. 'lat=140&long=100'
                 */
                appendToTrackingUrl: function (queryString) {
                    configAppendToTrackingUrl = queryString;
                },

                /**
                 * Returns the query string for the current HTTP Tracking API request.
                 * Piwik would prepend the hostname and path to Piwik: http://example.org/piwik/piwik.php?
                 * prior to sending the request.
                 *
                 * @param request eg. "param=value&param2=value2"
                 */
                getRequest: function (request) {
                    return getRequest(request);
                },

                /**
                 * Add plugin defined by a name and a callback function.
                 * The callback function will be called whenever a tracking request is sent.
                 * This can be used to append data to the tracking request, or execute other custom logic.
                 *
                 * @param string pluginName
                 * @param Object pluginObj
                 */
                addPlugin: function (pluginName, pluginObj) {
                    plugins[pluginName] = pluginObj;
                },

                /**
                 * Set custom variable within this visit
                 *
                 * @param int index
                 * @param string name
                 * @param string value
                 * @param string scope Scope of Custom Variable:
                 *                     - "visit" will store the name/value in the visit and will persist it in the cookie for the duration of the visit,
                 *                     - "page" will store the name/value in the next page view tracked.
                 *                     - "event" will store the name/value in the next event tracked.
                 */
                setCustomVariable: function (index, name, value, scope) {
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
                },

                /**
                 * Get custom variable
                 *
                 * @param int index
                 * @param string scope Scope of Custom Variable: "visit" or "page" or "event"
                 */
                getCustomVariable: function (index, scope) {
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
                },

                /**
                 * Delete custom variable
                 *
                 * @param int index
                 */
                deleteCustomVariable: function (index, scope) {
                    // Only delete if it was there already
                    if (this.getCustomVariable(index, scope)) {
                        this.setCustomVariable(index, '', '', scope);
                    }
                },

                /**
                 * When called then the Custom Variables of scope "visit" will be stored (persisted) in a first party cookie
                 * for the duration of the visit. This is useful if you want to call getCustomVariable later in the visit.
                 *
                 * By default, Custom Variables of scope "visit" are not stored on the visitor's computer.
                 */
                storeCustomVariablesInCookie: function () {
                    configStoreCustomVariablesInCookie = true;
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
                    configRequestMethod = method || defaultRequestMethod;
                },

                /**
                 * Set request Content-Type header value, applicable when POST request method is used for submitting tracking events.
                 * See XMLHttpRequest Level 2 spec, section 4.7.2 for invalid headers
                 * @link http://dvcs.w3.org/hg/xhr/raw-file/tip/Overview.html
                 *
                 * @param string requestContentType; default is 'application/x-www-form-urlencoded; charset=UTF-8'
                 */
                setRequestContentType: function (requestContentType) {
                    configRequestContentType = requestContentType || defaultRequestContentType;
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
                    configCustomUrl = resolveRelativeReference(locationHrefAlias, url);
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
                 * Set the URL of the Piwik API. It is used for Page Overlay.
                 * This method should only be called when the API URL differs from the tracker URL.
                 *
                 * @param string apiUrl
                 */
                setAPIUrl: function (apiUrl) {
                    configApiUrl = apiUrl;
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
                 * Set array of campaign name parameters
                 *
                 * @see http://piwik.org/faq/how-to/#faq_120
                 * @param string|array campaignNames
                 */
                setCampaignNameKey: function (campaignNames) {
                    configCampaignNameParameters = isString(campaignNames) ? [campaignNames] : campaignNames;
                },

                /**
                 * Set array of campaign keyword parameters
                 *
                 * @see http://piwik.org/faq/how-to/#faq_120
                 * @param string|array campaignKeywords
                 */
                setCampaignKeywordKey: function (campaignKeywords) {
                    configCampaignKeywordParameters = isString(campaignKeywords) ? [campaignKeywords] : campaignKeywords;
                },

                /**
                 * Strip hash tag (or anchor) from URL
                 * Note: this can be done in the Piwik>Settings>Websites on a per-website basis
                 *
                 * @deprecated
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
                 * Set visitor cookie timeout (in seconds)
                 * Defaults to 13 months (timeout=33955200)
                 *
                 * @param int timeout
                 */
                setVisitorCookieTimeout: function (timeout) {
                    configVisitorCookieTimeout = timeout * 1000;
                },

                /**
                 * Set session cookie timeout (in seconds).
                 * Defaults to 30 minutes (timeout=1800000)
                 *
                 * @param int timeout
                 */
                setSessionCookieTimeout: function (timeout) {
                    configSessionCookieTimeout = timeout * 1000;
                },

                /**
                 * Set referral cookie timeout (in seconds).
                 * Defaults to 6 months (15768000000)
                 *
                 * @param int timeout
                 */
                setReferralCookieTimeout: function (timeout) {
                    configReferralCookieTimeout = timeout * 1000;
                },

                /**
                 * Set conversion attribution to first referrer and campaign
                 *
                 * @param bool if true, use first referrer (and first campaign)
                 *             if false, use the last referrer (or campaign)
                 */
                setConversionAttributionFirstReferrer: function (enable) {
                    configConversionAttributionFirstReferrer = enable;
                },

                /**
                 * Disables all cookies from being set
                 *
                 * Existing cookies will be deleted on the next call to track
                 */
                disableCookies: function () {
                    configCookiesDisabled = true;
                    browserFeatures.cookie = '0';
                },

                /**
                 * One off cookies clearing. Useful to call this when you know for sure a new visitor is using the same browser,
                 * it maybe helps to "reset" tracking cookies to prevent data reuse for different users.
                 */
                deleteCookies: function () {
                    deleteCookies();
                },

                /**
                 * Handle do-not-track requests
                 *
                 * @param bool enable If true, don't track if user agent sends 'do-not-track' header
                 */
                setDoNotTrack: function (enable) {
                    var dnt = navigatorAlias.doNotTrack || navigatorAlias.msDoNotTrack;
                    configDoNotTrack = enable && (dnt === 'yes' || dnt === '1');

                    // do not track also disables cookies and deletes existing cookies
                    if (configDoNotTrack) {
                        this.disableCookies();
                    }
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
                 * @param bool enable If true, use pseudo click-handler (mousedown+mouseup)
                 */
                enableLinkTracking: function (enable) {
                    linkTrackingEnabled = true;

                    if (hasLoaded) {
                        // the load event has already fired, add the click listeners now
                        addClickListeners(enable);
                    } else {
                        // defer until page has loaded
                        registeredOnLoadHandlers.push(function () {
                            addClickListeners(enable);
                        });
                    }
                },

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
                enableJSErrorTracking: function () {
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
                },

                /**
                 * Disable automatic performance tracking
                 */
                disablePerformanceTracking: function () {
                    configPerformanceTrackingEnabled = false;
                },

                /**
                 * Set the server generation time.
                 * If set, the browser's performance.timing API in not used anymore to determine the time.
                 *
                 * @param int generationTime
                 */
                setGenerationTimeMs: function (generationTime) {
                    configPerformanceGenerationTime = parseInt(generationTime, 10);
                },

                /**
                 * Set heartbeat (in seconds)
                 *
                 * @param int minimumVisitLength
                 * @param int heartBeatDelay
                 */
                setHeartBeatTimer: function (minimumVisitLength, heartBeatDelay) {
                    var now = new Date();

                    configMinimumVisitTime = now.getTime() + minimumVisitLength * 1000;
                    configHeartBeatTimer = heartBeatDelay * 1000;
                },

                /**
                 * Frame buster
                 */
                killFrame: function () {
                    if (windowAlias.location !== windowAlias.top.location) {
                        windowAlias.top.location = windowAlias.location;
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
                 * Count sites in pre-rendered state
                 *
                 * @param bool enable If true, track when in pre-rendered state
                 */
                setCountPreRendered: function (enable) {
                    configCountPreRendered = enable;
                },

                /**
                 * Trigger a goal
                 *
                 * @param int|string idGoal
                 * @param int|float customRevenue
                 * @param mixed customData
                 */
                trackGoal: function (idGoal, customRevenue, customData) {
                    trackCallback(function () {
                        logGoal(idGoal, customRevenue, customData);
                    });
                },

                /**
                 * Manually log a click from your own code
                 *
                 * @param string sourceUrl
                 * @param string linkType
                 * @param mixed customData
                 * @param function callback
                 */
                trackLink: function (sourceUrl, linkType, customData, callback) {
                    trackCallback(function () {
                        logLink(sourceUrl, linkType, customData, callback);
                    });
                },

                /**
                 * Log visit to this page
                 *
                 * @param string customTitle
                 * @param mixed customData
                 */
                trackPageView: function (customTitle, customData) {
                    trackedContentImpressions = [];

                    if (isOverlaySession(configTrackerSiteId)) {
                        trackCallback(function () {
                            injectOverlayScripts(configTrackerUrl, configApiUrl, configTrackerSiteId);
                        });
                    } else {
                        trackCallback(function () {
                            logPageView(customTitle, customData);
                        });
                    }
                },

                /**
                 * Scans the entire DOM for all content blocks and tracks all impressions once the DOM ready event has
                 * been triggered.
                 *
                 * If you only want to track visible content impressions have a look at `trackVisibleContentImpressions()`.
                 * We do not track an impression of the same content block twice if you call this method multiple times
                 * unless `trackPageView()` is called meanwhile. This is useful for single page applications.
                 */
                trackAllContentImpressions: function () {
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
                },

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
                trackVisibleContentImpressions: function (checkOnSroll, timeIntervalInMs) {
                    if (isOverlaySession(configTrackerSiteId)) {
                        return;
                    }

                    if (!isDefined(checkOnSroll)) {
                        checkOnSroll = true;
                    }

                    if (!isDefined(timeIntervalInMs)) {
                        timeIntervalInMs = 750;
                    }

                    enableTrackOnlyVisibleContent(checkOnSroll, timeIntervalInMs, this);

                    trackCallback(function () {
                        trackCallbackOnLoad(function () {
                            // we have to wait till CSS parsed and applied
                            var contentNodes = content.findContentNodes();
                            var requests     = getCurrentlyVisibleContentImpressionsRequestsIfNotTrackedYet(contentNodes);

                            sendBulkRequest(requests, configTrackerPause);
                        });
                    });
                },

                /**
                 * Tracks a content impression using the specified values. You should not call this method too often
                 * as each call causes an XHR tracking request and can slow down your site or your server.
                 *
                 * @param string contentName  For instance "Ad Sale".
                 * @param string [contentPiece='Unknown'] For instance a path to an image or the text of a text ad.
                 * @param string [contentTarget] For instance the URL of a landing page.
                 */
                trackContentImpression: function (contentName, contentPiece, contentTarget) {
                    if (isOverlaySession(configTrackerSiteId)) {
                        return;
                    }

                    if (!contentName) {
                        return;
                    }

                    contentPiece = contentPiece || 'Unknown';

                    trackCallback(function () {
                        var request = buildContentImpressionRequest(contentName, contentPiece, contentTarget);
                        sendRequest(request, configTrackerPause);
                    });
                },

                /**
                 * Scans the given DOM node and its children for content blocks and tracks an impression for them if
                 * no impression was already tracked for it. If you have called `trackVisibleContentImpressions()`
                 * upfront only visible content blocks will be tracked. You can use this method if you, for instance,
                 * dynamically add an element using JavaScript to your DOM after we have tracked the initial impressions.
                 *
                 * @param Element domNode
                 */
                trackContentImpressionsWithinNode: function (domNode) {
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
                },

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
                trackContentInteraction: function (contentInteraction, contentName, contentPiece, contentTarget) {
                    if (isOverlaySession(configTrackerSiteId)) {
                        return;
                    }

                    if (!contentInteraction || !contentName) {
                        return;
                    }

                    contentPiece = contentPiece || 'Unknown';

                    trackCallback(function () {
                        var request = buildContentInteractionRequest(contentInteraction, contentName, contentPiece, contentTarget);
                        sendRequest(request, configTrackerPause);
                    });
                },

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
                trackContentInteractionNode: function (domNode, contentInteraction) {
                    if (isOverlaySession(configTrackerSiteId) || !domNode) {
                        return;
                    }

                    trackCallback(function () {
                        var request = buildContentInteractionRequestNode(domNode, contentInteraction);
                        sendRequest(request, configTrackerPause);
                    });
                },

                /**
                 * Records an event
                 *
                 * @param string category The Event Category (Videos, Music, Games...)
                 * @param string action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
                 * @param string name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
                 * @param float value (optional) The Event's value
                 */
                trackEvent: function (category, action, name, value) {
                    trackCallback(function () {
                        logEvent(category, action, name, value);
                    });
                },

                /**
                 * Log special pageview: Internal search
                 *
                 * @param string keyword
                 * @param string category
                 * @param int resultsCount
                 */
                trackSiteSearch: function (keyword, category, resultsCount) {
                    trackCallback(function () {
                        logSiteSearch(keyword, category, resultsCount);
                    });
                },

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
                setEcommerceView: function (sku, name, category, price) {
                    if (!isDefined(category) || !category.length) {
                        category = "";
                    } else if (category instanceof Array) {
                        category = JSON2.stringify(category);
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
                },

                /**
                 * Adds an item (product) that is in the current Cart or in the Ecommerce order.
                 * This function is called for every item (product) in the Cart or the Order.
                 * The only required parameter is sku.
                 *
                 * @param string sku (required) Item's SKU Code. This is the unique identifier for the product.
                 * @param string name (optional) Item's name
                 * @param string name (optional) Item's category, or array of up to 5 categories
                 * @param float price (optional) Item's price. If not specified, will default to 0
                 * @param float quantity (optional) Item's quantity. If not specified, will default to 1
                 */
                addEcommerceItem: function (sku, name, category, price, quantity) {
                    if (sku.length) {
                        ecommerceItems[sku] = [ sku, name, category, price, quantity ];
                    }
                },

                /**
                 * Tracks an Ecommerce order.
                 * If the Ecommerce order contains items (products), you must call first the addEcommerceItem() for each item in the order.
                 * All revenues (grandTotal, subTotal, tax, shipping, discount) will be individually summed and reported in Piwik reports.
                 * Parameters orderId and grandTotal are required. For others, you can set to false if you don't need to specify them.
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
                trackEcommerceOrder: function (orderId, grandTotal, subTotal, tax, shipping, discount) {
                    logEcommerceOrder(orderId, grandTotal, subTotal, tax, shipping, discount);
                },

                /**
                 * Tracks a Cart Update (add item, remove item, update item).
                 * On every Cart update, you must call addEcommerceItem() for each item (product) in the cart, including the items that haven't been updated since the last cart update.
                 * Then you can call this function with the Cart grandTotal (typically the sum of all items' prices)
                 *
                 * @param float grandTotal (required) Items (products) amount in the Cart
                 */
                trackEcommerceCartUpdate: function (grandTotal) {
                    logEcommerceCartUpdate(grandTotal);
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

        Date.prototype.getTimeAlias = Date.prototype.getTime;

        asyncTracker = new Tracker();

        var applyFirst = {setTrackerUrl: 1, setAPIUrl: 1, setSiteId: 1, disableCookies: 1, enableLinkTracking: 1};
        var methodName;

        // find the call to setTrackerUrl or setSiteid (if any) and call them first
        for (iterator = 0; iterator < _paq.length; iterator++) {
            methodName = _paq[iterator][0];

            if (applyFirst[methodName]) {
                apply(_paq[iterator]);
                delete _paq[iterator];

                if (applyFirst[methodName] > 1) {
                    if (console !== undefined && console && console.error) {
                        console.error('The method ' + methodName + ' is registered more than once in "_paq" variable. Only the last call has an effect. Please have a look at the multiple Piwik trackers documentation: http://developer.piwik.org/api-reference/tracking-javascript#multiple-piwik-trackers');
                    }
                }

                applyFirst[methodName]++;
            }
        }

        // apply the queue of actions
        for (iterator = 0; iterator < _paq.length; iterator++) {
            if (_paq[iterator]) {
                apply(_paq[iterator]);
            }
        }

        // replace initialization array with proxy object
        _paq = new TrackerProxy();

        /************************************************************
         * Public data and methods
         ************************************************************/

        Piwik = {
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
                return new Tracker(piwikUrl, siteId, asyncTracker.getVisitorId());
            },

            /**
             * Get internal asynchronous tracker object
             *
             * @return Tracker
             */
            getAsyncTracker: function () {
                return asyncTracker;
            }
        };

        // Expose Piwik as an AMD module
        if (typeof define === 'function' && define.amd) {
            define('piwik', [], function () { return Piwik; });
        }

        return Piwik;
    }());
}

if (window && window.piwikAsyncInit) {
    window.piwikAsyncInit();
}

/*jslint sloppy: true */
(function () {
    var jsTrackerType = (typeof AnalyticsTracker);
    if (jsTrackerType === 'undefined') {
        AnalyticsTracker = Piwik;
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
                return eval('piwik_' + optionName);
            } catch (ignore) { }

            return; // undefined
        }

        // instantiate the tracker
        var option,
            piwikTracker = Piwik.getTracker(piwikUrl, siteId);

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
