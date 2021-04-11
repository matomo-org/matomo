/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/animations'), require('@angular/core')) :
    typeof define === 'function' && define.amd ? define('@angular/animations/browser', ['exports', '@angular/animations', '@angular/core'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.animations = global.ng.animations || {}, global.ng.animations.browser = {}), global.ng.animations, global.ng.core));
}(this, (function (exports, animations, core) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function isBrowser() {
        return (typeof window !== 'undefined' && typeof window.document !== 'undefined');
    }
    function isNode() {
        // Checking only for `process` isn't enough to identify whether or not we're in a Node
        // environment, because Webpack by default will polyfill the `process`. While we can discern
        // that Webpack polyfilled it by looking at `process.browser`, it's very Webpack-specific and
        // might not be future-proof. Instead we look at the stringified version of `process` which
        // is `[object process]` in Node and `[object Object]` when polyfilled.
        return typeof process !== 'undefined' && {}.toString.call(process) === '[object process]';
    }
    function optimizeGroupPlayer(players) {
        switch (players.length) {
            case 0:
                return new animations.NoopAnimationPlayer();
            case 1:
                return players[0];
            default:
                return new animations.ɵAnimationGroupPlayer(players);
        }
    }
    function normalizeKeyframes(driver, normalizer, element, keyframes, preStyles, postStyles) {
        if (preStyles === void 0) { preStyles = {}; }
        if (postStyles === void 0) { postStyles = {}; }
        var errors = [];
        var normalizedKeyframes = [];
        var previousOffset = -1;
        var previousKeyframe = null;
        keyframes.forEach(function (kf) {
            var offset = kf['offset'];
            var isSameOffset = offset == previousOffset;
            var normalizedKeyframe = (isSameOffset && previousKeyframe) || {};
            Object.keys(kf).forEach(function (prop) {
                var normalizedProp = prop;
                var normalizedValue = kf[prop];
                if (prop !== 'offset') {
                    normalizedProp = normalizer.normalizePropertyName(normalizedProp, errors);
                    switch (normalizedValue) {
                        case animations.ɵPRE_STYLE:
                            normalizedValue = preStyles[prop];
                            break;
                        case animations.AUTO_STYLE:
                            normalizedValue = postStyles[prop];
                            break;
                        default:
                            normalizedValue =
                                normalizer.normalizeStyleValue(prop, normalizedProp, normalizedValue, errors);
                            break;
                    }
                }
                normalizedKeyframe[normalizedProp] = normalizedValue;
            });
            if (!isSameOffset) {
                normalizedKeyframes.push(normalizedKeyframe);
            }
            previousKeyframe = normalizedKeyframe;
            previousOffset = offset;
        });
        if (errors.length) {
            var LINE_START = '\n - ';
            throw new Error("Unable to animate due to the following errors:" + LINE_START + errors.join(LINE_START));
        }
        return normalizedKeyframes;
    }
    function listenOnPlayer(player, eventName, event, callback) {
        switch (eventName) {
            case 'start':
                player.onStart(function () { return callback(event && copyAnimationEvent(event, 'start', player)); });
                break;
            case 'done':
                player.onDone(function () { return callback(event && copyAnimationEvent(event, 'done', player)); });
                break;
            case 'destroy':
                player.onDestroy(function () { return callback(event && copyAnimationEvent(event, 'destroy', player)); });
                break;
        }
    }
    function copyAnimationEvent(e, phaseName, player) {
        var totalTime = player.totalTime;
        var disabled = player.disabled ? true : false;
        var event = makeAnimationEvent(e.element, e.triggerName, e.fromState, e.toState, phaseName || e.phaseName, totalTime == undefined ? e.totalTime : totalTime, disabled);
        var data = e['_data'];
        if (data != null) {
            event['_data'] = data;
        }
        return event;
    }
    function makeAnimationEvent(element, triggerName, fromState, toState, phaseName, totalTime, disabled) {
        if (phaseName === void 0) { phaseName = ''; }
        if (totalTime === void 0) { totalTime = 0; }
        return { element: element, triggerName: triggerName, fromState: fromState, toState: toState, phaseName: phaseName, totalTime: totalTime, disabled: !!disabled };
    }
    function getOrSetAsInMap(map, key, defaultValue) {
        var value;
        if (map instanceof Map) {
            value = map.get(key);
            if (!value) {
                map.set(key, value = defaultValue);
            }
        }
        else {
            value = map[key];
            if (!value) {
                value = map[key] = defaultValue;
            }
        }
        return value;
    }
    function parseTimelineCommand(command) {
        var separatorPos = command.indexOf(':');
        var id = command.substring(1, separatorPos);
        var action = command.substr(separatorPos + 1);
        return [id, action];
    }
    var _contains = function (elm1, elm2) { return false; };
    var ɵ0 = _contains;
    var _matches = function (element, selector) { return false; };
    var ɵ1 = _matches;
    var _query = function (element, selector, multi) {
        return [];
    };
    var ɵ2 = _query;
    // Define utility methods for browsers and platform-server(domino) where Element
    // and utility methods exist.
    var _isNode = isNode();
    if (_isNode || typeof Element !== 'undefined') {
        // this is well supported in all browsers
        _contains = function (elm1, elm2) {
            return elm1.contains(elm2);
        };
        _matches = (function () {
            if (_isNode || Element.prototype.matches) {
                return function (element, selector) { return element.matches(selector); };
            }
            else {
                var proto = Element.prototype;
                var fn_1 = proto.matchesSelector || proto.mozMatchesSelector || proto.msMatchesSelector ||
                    proto.oMatchesSelector || proto.webkitMatchesSelector;
                if (fn_1) {
                    return function (element, selector) { return fn_1.apply(element, [selector]); };
                }
                else {
                    return _matches;
                }
            }
        })();
        _query = function (element, selector, multi) {
            var results = [];
            if (multi) {
                // DO NOT REFACTOR TO USE SPREAD SYNTAX.
                // For element queries that return sufficiently large NodeList objects,
                // using spread syntax to populate the results array causes a RangeError
                // due to the call stack limit being reached. `Array.from` can not be used
                // as well, since NodeList is not iterable in IE 11, see
                // https://developer.mozilla.org/en-US/docs/Web/API/NodeList
                // More info is available in #38551.
                var elems = element.querySelectorAll(selector);
                for (var i = 0; i < elems.length; i++) {
                    results.push(elems[i]);
                }
            }
            else {
                var elm = element.querySelector(selector);
                if (elm) {
                    results.push(elm);
                }
            }
            return results;
        };
    }
    function containsVendorPrefix(prop) {
        // Webkit is the only real popular vendor prefix nowadays
        // cc: http://shouldiprefix.com/
        return prop.substring(1, 6) == 'ebkit'; // webkit or Webkit
    }
    var _CACHED_BODY = null;
    var _IS_WEBKIT = false;
    function validateStyleProperty(prop) {
        if (!_CACHED_BODY) {
            _CACHED_BODY = getBodyNode() || {};
            _IS_WEBKIT = _CACHED_BODY.style ? ('WebkitAppearance' in _CACHED_BODY.style) : false;
        }
        var result = true;
        if (_CACHED_BODY.style && !containsVendorPrefix(prop)) {
            result = prop in _CACHED_BODY.style;
            if (!result && _IS_WEBKIT) {
                var camelProp = 'Webkit' + prop.charAt(0).toUpperCase() + prop.substr(1);
                result = camelProp in _CACHED_BODY.style;
            }
        }
        return result;
    }
    function getBodyNode() {
        if (typeof document != 'undefined') {
            return document.body;
        }
        return null;
    }
    var matchesElement = _matches;
    var containsElement = _contains;
    var invokeQuery = _query;
    function hypenatePropsObject(object) {
        var newObj = {};
        Object.keys(object).forEach(function (prop) {
            var newProp = prop.replace(/([a-z])([A-Z])/g, '$1-$2');
            newObj[newProp] = object[prop];
        });
        return newObj;
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @publicApi
     */
    var NoopAnimationDriver = /** @class */ (function () {
        function NoopAnimationDriver() {
        }
        NoopAnimationDriver.prototype.validateStyleProperty = function (prop) {
            return validateStyleProperty(prop);
        };
        NoopAnimationDriver.prototype.matchesElement = function (element, selector) {
            return matchesElement(element, selector);
        };
        NoopAnimationDriver.prototype.containsElement = function (elm1, elm2) {
            return containsElement(elm1, elm2);
        };
        NoopAnimationDriver.prototype.query = function (element, selector, multi) {
            return invokeQuery(element, selector, multi);
        };
        NoopAnimationDriver.prototype.computeStyle = function (element, prop, defaultValue) {
            return defaultValue || '';
        };
        NoopAnimationDriver.prototype.animate = function (element, keyframes, duration, delay, easing, previousPlayers, scrubberAccessRequested) {
            if (previousPlayers === void 0) { previousPlayers = []; }
            return new animations.NoopAnimationPlayer(duration, delay);
        };
        return NoopAnimationDriver;
    }());
    NoopAnimationDriver.decorators = [
        { type: core.Injectable }
    ];
    /**
     * @publicApi
     */
    var AnimationDriver = /** @class */ (function () {
        function AnimationDriver() {
        }
        return AnimationDriver;
    }());
    AnimationDriver.NOOP = new NoopAnimationDriver();

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var ONE_SECOND = 1000;
    var SUBSTITUTION_EXPR_START = '{{';
    var SUBSTITUTION_EXPR_END = '}}';
    var ENTER_CLASSNAME = 'ng-enter';
    var LEAVE_CLASSNAME = 'ng-leave';
    var ENTER_SELECTOR = '.ng-enter';
    var LEAVE_SELECTOR = '.ng-leave';
    var NG_TRIGGER_CLASSNAME = 'ng-trigger';
    var NG_TRIGGER_SELECTOR = '.ng-trigger';
    var NG_ANIMATING_CLASSNAME = 'ng-animating';
    var NG_ANIMATING_SELECTOR = '.ng-animating';
    function resolveTimingValue(value) {
        if (typeof value == 'number')
            return value;
        var matches = value.match(/^(-?[\.\d]+)(m?s)/);
        if (!matches || matches.length < 2)
            return 0;
        return _convertTimeValueToMS(parseFloat(matches[1]), matches[2]);
    }
    function _convertTimeValueToMS(value, unit) {
        switch (unit) {
            case 's':
                return value * ONE_SECOND;
            default: // ms or something else
                return value;
        }
    }
    function resolveTiming(timings, errors, allowNegativeValues) {
        return timings.hasOwnProperty('duration') ?
            timings :
            parseTimeExpression(timings, errors, allowNegativeValues);
    }
    function parseTimeExpression(exp, errors, allowNegativeValues) {
        var regex = /^(-?[\.\d]+)(m?s)(?:\s+(-?[\.\d]+)(m?s))?(?:\s+([-a-z]+(?:\(.+?\))?))?$/i;
        var duration;
        var delay = 0;
        var easing = '';
        if (typeof exp === 'string') {
            var matches = exp.match(regex);
            if (matches === null) {
                errors.push("The provided timing value \"" + exp + "\" is invalid.");
                return { duration: 0, delay: 0, easing: '' };
            }
            duration = _convertTimeValueToMS(parseFloat(matches[1]), matches[2]);
            var delayMatch = matches[3];
            if (delayMatch != null) {
                delay = _convertTimeValueToMS(parseFloat(delayMatch), matches[4]);
            }
            var easingVal = matches[5];
            if (easingVal) {
                easing = easingVal;
            }
        }
        else {
            duration = exp;
        }
        if (!allowNegativeValues) {
            var containsErrors = false;
            var startIndex = errors.length;
            if (duration < 0) {
                errors.push("Duration values below 0 are not allowed for this animation step.");
                containsErrors = true;
            }
            if (delay < 0) {
                errors.push("Delay values below 0 are not allowed for this animation step.");
                containsErrors = true;
            }
            if (containsErrors) {
                errors.splice(startIndex, 0, "The provided timing value \"" + exp + "\" is invalid.");
            }
        }
        return { duration: duration, delay: delay, easing: easing };
    }
    function copyObj(obj, destination) {
        if (destination === void 0) { destination = {}; }
        Object.keys(obj).forEach(function (prop) {
            destination[prop] = obj[prop];
        });
        return destination;
    }
    function normalizeStyles(styles) {
        var normalizedStyles = {};
        if (Array.isArray(styles)) {
            styles.forEach(function (data) { return copyStyles(data, false, normalizedStyles); });
        }
        else {
            copyStyles(styles, false, normalizedStyles);
        }
        return normalizedStyles;
    }
    function copyStyles(styles, readPrototype, destination) {
        if (destination === void 0) { destination = {}; }
        if (readPrototype) {
            // we make use of a for-in loop so that the
            // prototypically inherited properties are
            // revealed from the backFill map
            for (var prop in styles) {
                destination[prop] = styles[prop];
            }
        }
        else {
            copyObj(styles, destination);
        }
        return destination;
    }
    function getStyleAttributeString(element, key, value) {
        // Return the key-value pair string to be added to the style attribute for the
        // given CSS style key.
        if (value) {
            return key + ':' + value + ';';
        }
        else {
            return '';
        }
    }
    function writeStyleAttribute(element) {
        // Read the style property of the element and manually reflect it to the
        // style attribute. This is needed because Domino on platform-server doesn't
        // understand the full set of allowed CSS properties and doesn't reflect some
        // of them automatically.
        var styleAttrValue = '';
        for (var i = 0; i < element.style.length; i++) {
            var key = element.style.item(i);
            styleAttrValue += getStyleAttributeString(element, key, element.style.getPropertyValue(key));
        }
        for (var key in element.style) {
            // Skip internal Domino properties that don't need to be reflected.
            if (!element.style.hasOwnProperty(key) || key.startsWith('_')) {
                continue;
            }
            var dashKey = camelCaseToDashCase(key);
            styleAttrValue += getStyleAttributeString(element, dashKey, element.style[key]);
        }
        element.setAttribute('style', styleAttrValue);
    }
    function setStyles(element, styles, formerStyles) {
        if (element['style']) {
            Object.keys(styles).forEach(function (prop) {
                var camelProp = dashCaseToCamelCase(prop);
                if (formerStyles && !formerStyles.hasOwnProperty(prop)) {
                    formerStyles[prop] = element.style[camelProp];
                }
                element.style[camelProp] = styles[prop];
            });
            // On the server set the 'style' attribute since it's not automatically reflected.
            if (isNode()) {
                writeStyleAttribute(element);
            }
        }
    }
    function eraseStyles(element, styles) {
        if (element['style']) {
            Object.keys(styles).forEach(function (prop) {
                var camelProp = dashCaseToCamelCase(prop);
                element.style[camelProp] = '';
            });
            // On the server set the 'style' attribute since it's not automatically reflected.
            if (isNode()) {
                writeStyleAttribute(element);
            }
        }
    }
    function normalizeAnimationEntry(steps) {
        if (Array.isArray(steps)) {
            if (steps.length == 1)
                return steps[0];
            return animations.sequence(steps);
        }
        return steps;
    }
    function validateStyleParams(value, options, errors) {
        var params = options.params || {};
        var matches = extractStyleParams(value);
        if (matches.length) {
            matches.forEach(function (varName) {
                if (!params.hasOwnProperty(varName)) {
                    errors.push("Unable to resolve the local animation param " + varName + " in the given list of values");
                }
            });
        }
    }
    var PARAM_REGEX = new RegExp(SUBSTITUTION_EXPR_START + "\\s*(.+?)\\s*" + SUBSTITUTION_EXPR_END, 'g');
    function extractStyleParams(value) {
        var params = [];
        if (typeof value === 'string') {
            var match = void 0;
            while (match = PARAM_REGEX.exec(value)) {
                params.push(match[1]);
            }
            PARAM_REGEX.lastIndex = 0;
        }
        return params;
    }
    function interpolateParams(value, params, errors) {
        var original = value.toString();
        var str = original.replace(PARAM_REGEX, function (_, varName) {
            var localVal = params[varName];
            // this means that the value was never overridden by the data passed in by the user
            if (!params.hasOwnProperty(varName)) {
                errors.push("Please provide a value for the animation param " + varName);
                localVal = '';
            }
            return localVal.toString();
        });
        // we do this to assert that numeric values stay as they are
        return str == original ? value : str;
    }
    function iteratorToArray(iterator) {
        var arr = [];
        var item = iterator.next();
        while (!item.done) {
            arr.push(item.value);
            item = iterator.next();
        }
        return arr;
    }
    var DASH_CASE_REGEXP = /-+([a-z0-9])/g;
    function dashCaseToCamelCase(input) {
        return input.replace(DASH_CASE_REGEXP, function () {
            var m = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                m[_i] = arguments[_i];
            }
            return m[1].toUpperCase();
        });
    }
    function camelCaseToDashCase(input) {
        return input.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    }
    function allowPreviousPlayerStylesMerge(duration, delay) {
        return duration === 0 || delay === 0;
    }
    function balancePreviousStylesIntoKeyframes(element, keyframes, previousStyles) {
        var previousStyleProps = Object.keys(previousStyles);
        if (previousStyleProps.length && keyframes.length) {
            var startingKeyframe_1 = keyframes[0];
            var missingStyleProps_1 = [];
            previousStyleProps.forEach(function (prop) {
                if (!startingKeyframe_1.hasOwnProperty(prop)) {
                    missingStyleProps_1.push(prop);
                }
                startingKeyframe_1[prop] = previousStyles[prop];
            });
            if (missingStyleProps_1.length) {
                var _loop_1 = function () {
                    var kf = keyframes[i];
                    missingStyleProps_1.forEach(function (prop) {
                        kf[prop] = computeStyle(element, prop);
                    });
                };
                // tslint:disable-next-line
                for (var i = 1; i < keyframes.length; i++) {
                    _loop_1();
                }
            }
        }
        return keyframes;
    }
    function visitDslNode(visitor, node, context) {
        switch (node.type) {
            case 7 /* Trigger */:
                return visitor.visitTrigger(node, context);
            case 0 /* State */:
                return visitor.visitState(node, context);
            case 1 /* Transition */:
                return visitor.visitTransition(node, context);
            case 2 /* Sequence */:
                return visitor.visitSequence(node, context);
            case 3 /* Group */:
                return visitor.visitGroup(node, context);
            case 4 /* Animate */:
                return visitor.visitAnimate(node, context);
            case 5 /* Keyframes */:
                return visitor.visitKeyframes(node, context);
            case 6 /* Style */:
                return visitor.visitStyle(node, context);
            case 8 /* Reference */:
                return visitor.visitReference(node, context);
            case 9 /* AnimateChild */:
                return visitor.visitAnimateChild(node, context);
            case 10 /* AnimateRef */:
                return visitor.visitAnimateRef(node, context);
            case 11 /* Query */:
                return visitor.visitQuery(node, context);
            case 12 /* Stagger */:
                return visitor.visitStagger(node, context);
            default:
                throw new Error("Unable to resolve animation metadata node #" + node.type);
        }
    }
    function computeStyle(element, prop) {
        return window.getComputedStyle(element)[prop];
    }

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b)
                if (b.hasOwnProperty(p))
                    d[p] = b[p]; };
        return extendStatics(d, b);
    };
    function __extends(d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }
    var __assign = function () {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s)
                    if (Object.prototype.hasOwnProperty.call(s, p))
                        t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };
    function __rest(s, e) {
        var t = {};
        for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
                t[p] = s[p];
        if (s != null && typeof Object.getOwnPropertySymbols === "function")
            for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
                if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                    t[p[i]] = s[p[i]];
            }
        return t;
    }
    function __decorate(decorators, target, key, desc) {
        var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
        if (typeof Reflect === "object" && typeof Reflect.decorate === "function")
            r = Reflect.decorate(decorators, target, key, desc);
        else
            for (var i = decorators.length - 1; i >= 0; i--)
                if (d = decorators[i])
                    r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
        return c > 3 && r && Object.defineProperty(target, key, r), r;
    }
    function __param(paramIndex, decorator) {
        return function (target, key) { decorator(target, key, paramIndex); };
    }
    function __metadata(metadataKey, metadataValue) {
        if (typeof Reflect === "object" && typeof Reflect.metadata === "function")
            return Reflect.metadata(metadataKey, metadataValue);
    }
    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try {
                step(generator.next(value));
            }
            catch (e) {
                reject(e);
            } }
            function rejected(value) { try {
                step(generator["throw"](value));
            }
            catch (e) {
                reject(e);
            } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }
    function __generator(thisArg, body) {
        var _ = { label: 0, sent: function () { if (t[0] & 1)
                throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function () { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f)
                throw new TypeError("Generator is already executing.");
            while (_)
                try {
                    if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done)
                        return t;
                    if (y = 0, t)
                        op = [op[0] & 2, t.value];
                    switch (op[0]) {
                        case 0:
                        case 1:
                            t = op;
                            break;
                        case 4:
                            _.label++;
                            return { value: op[1], done: false };
                        case 5:
                            _.label++;
                            y = op[1];
                            op = [0];
                            continue;
                        case 7:
                            op = _.ops.pop();
                            _.trys.pop();
                            continue;
                        default:
                            if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) {
                                _ = 0;
                                continue;
                            }
                            if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) {
                                _.label = op[1];
                                break;
                            }
                            if (op[0] === 6 && _.label < t[1]) {
                                _.label = t[1];
                                t = op;
                                break;
                            }
                            if (t && _.label < t[2]) {
                                _.label = t[2];
                                _.ops.push(op);
                                break;
                            }
                            if (t[2])
                                _.ops.pop();
                            _.trys.pop();
                            continue;
                    }
                    op = body.call(thisArg, _);
                }
                catch (e) {
                    op = [6, e];
                    y = 0;
                }
                finally {
                    f = t = 0;
                }
            if (op[0] & 5)
                throw op[1];
            return { value: op[0] ? op[1] : void 0, done: true };
        }
    }
    var __createBinding = Object.create ? (function (o, m, k, k2) {
        if (k2 === undefined)
            k2 = k;
        Object.defineProperty(o, k2, { enumerable: true, get: function () { return m[k]; } });
    }) : (function (o, m, k, k2) {
        if (k2 === undefined)
            k2 = k;
        o[k2] = m[k];
    });
    function __exportStar(m, exports) {
        for (var p in m)
            if (p !== "default" && !exports.hasOwnProperty(p))
                __createBinding(exports, m, p);
    }
    function __values(o) {
        var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
        if (m)
            return m.call(o);
        if (o && typeof o.length === "number")
            return {
                next: function () {
                    if (o && i >= o.length)
                        o = void 0;
                    return { value: o && o[i++], done: !o };
                }
            };
        throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
    }
    function __read(o, n) {
        var m = typeof Symbol === "function" && o[Symbol.iterator];
        if (!m)
            return o;
        var i = m.call(o), r, ar = [], e;
        try {
            while ((n === void 0 || n-- > 0) && !(r = i.next()).done)
                ar.push(r.value);
        }
        catch (error) {
            e = { error: error };
        }
        finally {
            try {
                if (r && !r.done && (m = i["return"]))
                    m.call(i);
            }
            finally {
                if (e)
                    throw e.error;
            }
        }
        return ar;
    }
    function __spread() {
        for (var ar = [], i = 0; i < arguments.length; i++)
            ar = ar.concat(__read(arguments[i]));
        return ar;
    }
    function __spreadArrays() {
        for (var s = 0, i = 0, il = arguments.length; i < il; i++)
            s += arguments[i].length;
        for (var r = Array(s), k = 0, i = 0; i < il; i++)
            for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
                r[k] = a[j];
        return r;
    }
    ;
    function __await(v) {
        return this instanceof __await ? (this.v = v, this) : new __await(v);
    }
    function __asyncGenerator(thisArg, _arguments, generator) {
        if (!Symbol.asyncIterator)
            throw new TypeError("Symbol.asyncIterator is not defined.");
        var g = generator.apply(thisArg, _arguments || []), i, q = [];
        return i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i;
        function verb(n) { if (g[n])
            i[n] = function (v) { return new Promise(function (a, b) { q.push([n, v, a, b]) > 1 || resume(n, v); }); }; }
        function resume(n, v) { try {
            step(g[n](v));
        }
        catch (e) {
            settle(q[0][3], e);
        } }
        function step(r) { r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r); }
        function fulfill(value) { resume("next", value); }
        function reject(value) { resume("throw", value); }
        function settle(f, v) { if (f(v), q.shift(), q.length)
            resume(q[0][0], q[0][1]); }
    }
    function __asyncDelegator(o) {
        var i, p;
        return i = {}, verb("next"), verb("throw", function (e) { throw e; }), verb("return"), i[Symbol.iterator] = function () { return this; }, i;
        function verb(n, f) { i[n] = o[n] ? function (v) { return (p = !p) ? { value: __await(o[n](v)), done: n === "return" } : f ? f(v) : v; } : f; }
    }
    function __asyncValues(o) {
        if (!Symbol.asyncIterator)
            throw new TypeError("Symbol.asyncIterator is not defined.");
        var m = o[Symbol.asyncIterator], i;
        return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i);
        function verb(n) { i[n] = o[n] && function (v) { return new Promise(function (resolve, reject) { v = o[n](v), settle(resolve, reject, v.done, v.value); }); }; }
        function settle(resolve, reject, d, v) { Promise.resolve(v).then(function (v) { resolve({ value: v, done: d }); }, reject); }
    }
    function __makeTemplateObject(cooked, raw) {
        if (Object.defineProperty) {
            Object.defineProperty(cooked, "raw", { value: raw });
        }
        else {
            cooked.raw = raw;
        }
        return cooked;
    }
    ;
    var __setModuleDefault = Object.create ? (function (o, v) {
        Object.defineProperty(o, "default", { enumerable: true, value: v });
    }) : function (o, v) {
        o["default"] = v;
    };
    function __importStar(mod) {
        if (mod && mod.__esModule)
            return mod;
        var result = {};
        if (mod != null)
            for (var k in mod)
                if (Object.hasOwnProperty.call(mod, k))
                    __createBinding(result, mod, k);
        __setModuleDefault(result, mod);
        return result;
    }
    function __importDefault(mod) {
        return (mod && mod.__esModule) ? mod : { default: mod };
    }
    function __classPrivateFieldGet(receiver, privateMap) {
        if (!privateMap.has(receiver)) {
            throw new TypeError("attempted to get private field on non-instance");
        }
        return privateMap.get(receiver);
    }
    function __classPrivateFieldSet(receiver, privateMap, value) {
        if (!privateMap.has(receiver)) {
            throw new TypeError("attempted to set private field on non-instance");
        }
        privateMap.set(receiver, value);
        return value;
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var ANY_STATE = '*';
    function parseTransitionExpr(transitionValue, errors) {
        var expressions = [];
        if (typeof transitionValue == 'string') {
            transitionValue.split(/\s*,\s*/).forEach(function (str) { return parseInnerTransitionStr(str, expressions, errors); });
        }
        else {
            expressions.push(transitionValue);
        }
        return expressions;
    }
    function parseInnerTransitionStr(eventStr, expressions, errors) {
        if (eventStr[0] == ':') {
            var result = parseAnimationAlias(eventStr, errors);
            if (typeof result == 'function') {
                expressions.push(result);
                return;
            }
            eventStr = result;
        }
        var match = eventStr.match(/^(\*|[-\w]+)\s*(<?[=-]>)\s*(\*|[-\w]+)$/);
        if (match == null || match.length < 4) {
            errors.push("The provided transition expression \"" + eventStr + "\" is not supported");
            return expressions;
        }
        var fromState = match[1];
        var separator = match[2];
        var toState = match[3];
        expressions.push(makeLambdaFromStates(fromState, toState));
        var isFullAnyStateExpr = fromState == ANY_STATE && toState == ANY_STATE;
        if (separator[0] == '<' && !isFullAnyStateExpr) {
            expressions.push(makeLambdaFromStates(toState, fromState));
        }
    }
    function parseAnimationAlias(alias, errors) {
        switch (alias) {
            case ':enter':
                return 'void => *';
            case ':leave':
                return '* => void';
            case ':increment':
                return function (fromState, toState) { return parseFloat(toState) > parseFloat(fromState); };
            case ':decrement':
                return function (fromState, toState) { return parseFloat(toState) < parseFloat(fromState); };
            default:
                errors.push("The transition alias value \"" + alias + "\" is not supported");
                return '* => *';
        }
    }
    // DO NOT REFACTOR ... keep the follow set instantiations
    // with the values intact (closure compiler for some reason
    // removes follow-up lines that add the values outside of
    // the constructor...
    var TRUE_BOOLEAN_VALUES = new Set(['true', '1']);
    var FALSE_BOOLEAN_VALUES = new Set(['false', '0']);
    function makeLambdaFromStates(lhs, rhs) {
        var LHS_MATCH_BOOLEAN = TRUE_BOOLEAN_VALUES.has(lhs) || FALSE_BOOLEAN_VALUES.has(lhs);
        var RHS_MATCH_BOOLEAN = TRUE_BOOLEAN_VALUES.has(rhs) || FALSE_BOOLEAN_VALUES.has(rhs);
        return function (fromState, toState) {
            var lhsMatch = lhs == ANY_STATE || lhs == fromState;
            var rhsMatch = rhs == ANY_STATE || rhs == toState;
            if (!lhsMatch && LHS_MATCH_BOOLEAN && typeof fromState === 'boolean') {
                lhsMatch = fromState ? TRUE_BOOLEAN_VALUES.has(lhs) : FALSE_BOOLEAN_VALUES.has(lhs);
            }
            if (!rhsMatch && RHS_MATCH_BOOLEAN && typeof toState === 'boolean') {
                rhsMatch = toState ? TRUE_BOOLEAN_VALUES.has(rhs) : FALSE_BOOLEAN_VALUES.has(rhs);
            }
            return lhsMatch && rhsMatch;
        };
    }

    var SELF_TOKEN = ':self';
    var SELF_TOKEN_REGEX = new RegExp("s*" + SELF_TOKEN + "s*,?", 'g');
    /*
     * [Validation]
     * The visitor code below will traverse the animation AST generated by the animation verb functions
     * (the output is a tree of objects) and attempt to perform a series of validations on the data. The
     * following corner-cases will be validated:
     *
     * 1. Overlap of animations
     * Given that a CSS property cannot be animated in more than one place at the same time, it's
     * important that this behavior is detected and validated. The way in which this occurs is that
     * each time a style property is examined, a string-map containing the property will be updated with
     * the start and end times for when the property is used within an animation step.
     *
     * If there are two or more parallel animations that are currently running (these are invoked by the
     * group()) on the same element then the validator will throw an error. Since the start/end timing
     * values are collected for each property then if the current animation step is animating the same
     * property and its timing values fall anywhere into the window of time that the property is
     * currently being animated within then this is what causes an error.
     *
     * 2. Timing values
     * The validator will validate to see if a timing value of `duration delay easing` or
     * `durationNumber` is valid or not.
     *
     * (note that upon validation the code below will replace the timing data with an object containing
     * {duration,delay,easing}.
     *
     * 3. Offset Validation
     * Each of the style() calls are allowed to have an offset value when placed inside of keyframes().
     * Offsets within keyframes() are considered valid when:
     *
     *   - No offsets are used at all
     *   - Each style() entry contains an offset value
     *   - Each offset is between 0 and 1
     *   - Each offset is greater to or equal than the previous one
     *
     * Otherwise an error will be thrown.
     */
    function buildAnimationAst(driver, metadata, errors) {
        return new AnimationAstBuilderVisitor(driver).build(metadata, errors);
    }
    var ROOT_SELECTOR = '';
    var AnimationAstBuilderVisitor = /** @class */ (function () {
        function AnimationAstBuilderVisitor(_driver) {
            this._driver = _driver;
        }
        AnimationAstBuilderVisitor.prototype.build = function (metadata, errors) {
            var context = new AnimationAstBuilderContext(errors);
            this._resetContextStyleTimingState(context);
            return visitDslNode(this, normalizeAnimationEntry(metadata), context);
        };
        AnimationAstBuilderVisitor.prototype._resetContextStyleTimingState = function (context) {
            context.currentQuerySelector = ROOT_SELECTOR;
            context.collectedStyles = {};
            context.collectedStyles[ROOT_SELECTOR] = {};
            context.currentTime = 0;
        };
        AnimationAstBuilderVisitor.prototype.visitTrigger = function (metadata, context) {
            var _this = this;
            var queryCount = context.queryCount = 0;
            var depCount = context.depCount = 0;
            var states = [];
            var transitions = [];
            if (metadata.name.charAt(0) == '@') {
                context.errors.push('animation triggers cannot be prefixed with an `@` sign (e.g. trigger(\'@foo\', [...]))');
            }
            metadata.definitions.forEach(function (def) {
                _this._resetContextStyleTimingState(context);
                if (def.type == 0 /* State */) {
                    var stateDef_1 = def;
                    var name = stateDef_1.name;
                    name.toString().split(/\s*,\s*/).forEach(function (n) {
                        stateDef_1.name = n;
                        states.push(_this.visitState(stateDef_1, context));
                    });
                    stateDef_1.name = name;
                }
                else if (def.type == 1 /* Transition */) {
                    var transition = _this.visitTransition(def, context);
                    queryCount += transition.queryCount;
                    depCount += transition.depCount;
                    transitions.push(transition);
                }
                else {
                    context.errors.push('only state() and transition() definitions can sit inside of a trigger()');
                }
            });
            return {
                type: 7 /* Trigger */,
                name: metadata.name,
                states: states,
                transitions: transitions,
                queryCount: queryCount,
                depCount: depCount,
                options: null
            };
        };
        AnimationAstBuilderVisitor.prototype.visitState = function (metadata, context) {
            var styleAst = this.visitStyle(metadata.styles, context);
            var astParams = (metadata.options && metadata.options.params) || null;
            if (styleAst.containsDynamicStyles) {
                var missingSubs_1 = new Set();
                var params_1 = astParams || {};
                styleAst.styles.forEach(function (value) {
                    if (isObject(value)) {
                        var stylesObj_1 = value;
                        Object.keys(stylesObj_1).forEach(function (prop) {
                            extractStyleParams(stylesObj_1[prop]).forEach(function (sub) {
                                if (!params_1.hasOwnProperty(sub)) {
                                    missingSubs_1.add(sub);
                                }
                            });
                        });
                    }
                });
                if (missingSubs_1.size) {
                    var missingSubsArr = iteratorToArray(missingSubs_1.values());
                    context.errors.push("state(\"" + metadata
                        .name + "\", ...) must define default values for all the following style substitutions: " + missingSubsArr.join(', '));
                }
            }
            return {
                type: 0 /* State */,
                name: metadata.name,
                style: styleAst,
                options: astParams ? { params: astParams } : null
            };
        };
        AnimationAstBuilderVisitor.prototype.visitTransition = function (metadata, context) {
            context.queryCount = 0;
            context.depCount = 0;
            var animation = visitDslNode(this, normalizeAnimationEntry(metadata.animation), context);
            var matchers = parseTransitionExpr(metadata.expr, context.errors);
            return {
                type: 1 /* Transition */,
                matchers: matchers,
                animation: animation,
                queryCount: context.queryCount,
                depCount: context.depCount,
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitSequence = function (metadata, context) {
            var _this = this;
            return {
                type: 2 /* Sequence */,
                steps: metadata.steps.map(function (s) { return visitDslNode(_this, s, context); }),
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitGroup = function (metadata, context) {
            var _this = this;
            var currentTime = context.currentTime;
            var furthestTime = 0;
            var steps = metadata.steps.map(function (step) {
                context.currentTime = currentTime;
                var innerAst = visitDslNode(_this, step, context);
                furthestTime = Math.max(furthestTime, context.currentTime);
                return innerAst;
            });
            context.currentTime = furthestTime;
            return {
                type: 3 /* Group */,
                steps: steps,
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitAnimate = function (metadata, context) {
            var timingAst = constructTimingAst(metadata.timings, context.errors);
            context.currentAnimateTimings = timingAst;
            var styleAst;
            var styleMetadata = metadata.styles ? metadata.styles : animations.style({});
            if (styleMetadata.type == 5 /* Keyframes */) {
                styleAst = this.visitKeyframes(styleMetadata, context);
            }
            else {
                var styleMetadata_1 = metadata.styles;
                var isEmpty = false;
                if (!styleMetadata_1) {
                    isEmpty = true;
                    var newStyleData = {};
                    if (timingAst.easing) {
                        newStyleData['easing'] = timingAst.easing;
                    }
                    styleMetadata_1 = animations.style(newStyleData);
                }
                context.currentTime += timingAst.duration + timingAst.delay;
                var _styleAst = this.visitStyle(styleMetadata_1, context);
                _styleAst.isEmptyStep = isEmpty;
                styleAst = _styleAst;
            }
            context.currentAnimateTimings = null;
            return {
                type: 4 /* Animate */,
                timings: timingAst,
                style: styleAst,
                options: null
            };
        };
        AnimationAstBuilderVisitor.prototype.visitStyle = function (metadata, context) {
            var ast = this._makeStyleAst(metadata, context);
            this._validateStyleAst(ast, context);
            return ast;
        };
        AnimationAstBuilderVisitor.prototype._makeStyleAst = function (metadata, context) {
            var styles = [];
            if (Array.isArray(metadata.styles)) {
                metadata.styles.forEach(function (styleTuple) {
                    if (typeof styleTuple == 'string') {
                        if (styleTuple == animations.AUTO_STYLE) {
                            styles.push(styleTuple);
                        }
                        else {
                            context.errors.push("The provided style string value " + styleTuple + " is not allowed.");
                        }
                    }
                    else {
                        styles.push(styleTuple);
                    }
                });
            }
            else {
                styles.push(metadata.styles);
            }
            var containsDynamicStyles = false;
            var collectedEasing = null;
            styles.forEach(function (styleData) {
                if (isObject(styleData)) {
                    var styleMap = styleData;
                    var easing = styleMap['easing'];
                    if (easing) {
                        collectedEasing = easing;
                        delete styleMap['easing'];
                    }
                    if (!containsDynamicStyles) {
                        for (var prop in styleMap) {
                            var value = styleMap[prop];
                            if (value.toString().indexOf(SUBSTITUTION_EXPR_START) >= 0) {
                                containsDynamicStyles = true;
                                break;
                            }
                        }
                    }
                }
            });
            return {
                type: 6 /* Style */,
                styles: styles,
                easing: collectedEasing,
                offset: metadata.offset,
                containsDynamicStyles: containsDynamicStyles,
                options: null
            };
        };
        AnimationAstBuilderVisitor.prototype._validateStyleAst = function (ast, context) {
            var _this = this;
            var timings = context.currentAnimateTimings;
            var endTime = context.currentTime;
            var startTime = context.currentTime;
            if (timings && startTime > 0) {
                startTime -= timings.duration + timings.delay;
            }
            ast.styles.forEach(function (tuple) {
                if (typeof tuple == 'string')
                    return;
                Object.keys(tuple).forEach(function (prop) {
                    if (!_this._driver.validateStyleProperty(prop)) {
                        context.errors.push("The provided animation property \"" + prop + "\" is not a supported CSS property for animations");
                        return;
                    }
                    var collectedStyles = context.collectedStyles[context.currentQuerySelector];
                    var collectedEntry = collectedStyles[prop];
                    var updateCollectedStyle = true;
                    if (collectedEntry) {
                        if (startTime != endTime && startTime >= collectedEntry.startTime &&
                            endTime <= collectedEntry.endTime) {
                            context.errors.push("The CSS property \"" + prop + "\" that exists between the times of \"" + collectedEntry.startTime + "ms\" and \"" + collectedEntry
                                .endTime + "ms\" is also being animated in a parallel animation between the times of \"" + startTime + "ms\" and \"" + endTime + "ms\"");
                            updateCollectedStyle = false;
                        }
                        // we always choose the smaller start time value since we
                        // want to have a record of the entire animation window where
                        // the style property is being animated in between
                        startTime = collectedEntry.startTime;
                    }
                    if (updateCollectedStyle) {
                        collectedStyles[prop] = { startTime: startTime, endTime: endTime };
                    }
                    if (context.options) {
                        validateStyleParams(tuple[prop], context.options, context.errors);
                    }
                });
            });
        };
        AnimationAstBuilderVisitor.prototype.visitKeyframes = function (metadata, context) {
            var _this = this;
            var ast = { type: 5 /* Keyframes */, styles: [], options: null };
            if (!context.currentAnimateTimings) {
                context.errors.push("keyframes() must be placed inside of a call to animate()");
                return ast;
            }
            var MAX_KEYFRAME_OFFSET = 1;
            var totalKeyframesWithOffsets = 0;
            var offsets = [];
            var offsetsOutOfOrder = false;
            var keyframesOutOfRange = false;
            var previousOffset = 0;
            var keyframes = metadata.steps.map(function (styles) {
                var style = _this._makeStyleAst(styles, context);
                var offsetVal = style.offset != null ? style.offset : consumeOffset(style.styles);
                var offset = 0;
                if (offsetVal != null) {
                    totalKeyframesWithOffsets++;
                    offset = style.offset = offsetVal;
                }
                keyframesOutOfRange = keyframesOutOfRange || offset < 0 || offset > 1;
                offsetsOutOfOrder = offsetsOutOfOrder || offset < previousOffset;
                previousOffset = offset;
                offsets.push(offset);
                return style;
            });
            if (keyframesOutOfRange) {
                context.errors.push("Please ensure that all keyframe offsets are between 0 and 1");
            }
            if (offsetsOutOfOrder) {
                context.errors.push("Please ensure that all keyframe offsets are in order");
            }
            var length = metadata.steps.length;
            var generatedOffset = 0;
            if (totalKeyframesWithOffsets > 0 && totalKeyframesWithOffsets < length) {
                context.errors.push("Not all style() steps within the declared keyframes() contain offsets");
            }
            else if (totalKeyframesWithOffsets == 0) {
                generatedOffset = MAX_KEYFRAME_OFFSET / (length - 1);
            }
            var limit = length - 1;
            var currentTime = context.currentTime;
            var currentAnimateTimings = context.currentAnimateTimings;
            var animateDuration = currentAnimateTimings.duration;
            keyframes.forEach(function (kf, i) {
                var offset = generatedOffset > 0 ? (i == limit ? 1 : (generatedOffset * i)) : offsets[i];
                var durationUpToThisFrame = offset * animateDuration;
                context.currentTime = currentTime + currentAnimateTimings.delay + durationUpToThisFrame;
                currentAnimateTimings.duration = durationUpToThisFrame;
                _this._validateStyleAst(kf, context);
                kf.offset = offset;
                ast.styles.push(kf);
            });
            return ast;
        };
        AnimationAstBuilderVisitor.prototype.visitReference = function (metadata, context) {
            return {
                type: 8 /* Reference */,
                animation: visitDslNode(this, normalizeAnimationEntry(metadata.animation), context),
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitAnimateChild = function (metadata, context) {
            context.depCount++;
            return {
                type: 9 /* AnimateChild */,
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitAnimateRef = function (metadata, context) {
            return {
                type: 10 /* AnimateRef */,
                animation: this.visitReference(metadata.animation, context),
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitQuery = function (metadata, context) {
            var parentSelector = context.currentQuerySelector;
            var options = (metadata.options || {});
            context.queryCount++;
            context.currentQuery = metadata;
            var _a = __read(normalizeSelector(metadata.selector), 2), selector = _a[0], includeSelf = _a[1];
            context.currentQuerySelector =
                parentSelector.length ? (parentSelector + ' ' + selector) : selector;
            getOrSetAsInMap(context.collectedStyles, context.currentQuerySelector, {});
            var animation = visitDslNode(this, normalizeAnimationEntry(metadata.animation), context);
            context.currentQuery = null;
            context.currentQuerySelector = parentSelector;
            return {
                type: 11 /* Query */,
                selector: selector,
                limit: options.limit || 0,
                optional: !!options.optional,
                includeSelf: includeSelf,
                animation: animation,
                originalSelector: metadata.selector,
                options: normalizeAnimationOptions(metadata.options)
            };
        };
        AnimationAstBuilderVisitor.prototype.visitStagger = function (metadata, context) {
            if (!context.currentQuery) {
                context.errors.push("stagger() can only be used inside of query()");
            }
            var timings = metadata.timings === 'full' ?
                { duration: 0, delay: 0, easing: 'full' } :
                resolveTiming(metadata.timings, context.errors, true);
            return {
                type: 12 /* Stagger */,
                animation: visitDslNode(this, normalizeAnimationEntry(metadata.animation), context),
                timings: timings,
                options: null
            };
        };
        return AnimationAstBuilderVisitor;
    }());
    function normalizeSelector(selector) {
        var hasAmpersand = selector.split(/\s*,\s*/).find(function (token) { return token == SELF_TOKEN; }) ? true : false;
        if (hasAmpersand) {
            selector = selector.replace(SELF_TOKEN_REGEX, '');
        }
        // the :enter and :leave selectors are filled in at runtime during timeline building
        selector = selector.replace(/@\*/g, NG_TRIGGER_SELECTOR)
            .replace(/@\w+/g, function (match) { return NG_TRIGGER_SELECTOR + '-' + match.substr(1); })
            .replace(/:animating/g, NG_ANIMATING_SELECTOR);
        return [selector, hasAmpersand];
    }
    function normalizeParams(obj) {
        return obj ? copyObj(obj) : null;
    }
    var AnimationAstBuilderContext = /** @class */ (function () {
        function AnimationAstBuilderContext(errors) {
            this.errors = errors;
            this.queryCount = 0;
            this.depCount = 0;
            this.currentTransition = null;
            this.currentQuery = null;
            this.currentQuerySelector = null;
            this.currentAnimateTimings = null;
            this.currentTime = 0;
            this.collectedStyles = {};
            this.options = null;
        }
        return AnimationAstBuilderContext;
    }());
    function consumeOffset(styles) {
        if (typeof styles == 'string')
            return null;
        var offset = null;
        if (Array.isArray(styles)) {
            styles.forEach(function (styleTuple) {
                if (isObject(styleTuple) && styleTuple.hasOwnProperty('offset')) {
                    var obj = styleTuple;
                    offset = parseFloat(obj['offset']);
                    delete obj['offset'];
                }
            });
        }
        else if (isObject(styles) && styles.hasOwnProperty('offset')) {
            var obj = styles;
            offset = parseFloat(obj['offset']);
            delete obj['offset'];
        }
        return offset;
    }
    function isObject(value) {
        return !Array.isArray(value) && typeof value == 'object';
    }
    function constructTimingAst(value, errors) {
        var timings = null;
        if (value.hasOwnProperty('duration')) {
            timings = value;
        }
        else if (typeof value == 'number') {
            var duration = resolveTiming(value, errors).duration;
            return makeTimingAst(duration, 0, '');
        }
        var strValue = value;
        var isDynamic = strValue.split(/\s+/).some(function (v) { return v.charAt(0) == '{' && v.charAt(1) == '{'; });
        if (isDynamic) {
            var ast = makeTimingAst(0, 0, '');
            ast.dynamic = true;
            ast.strValue = strValue;
            return ast;
        }
        timings = timings || resolveTiming(strValue, errors);
        return makeTimingAst(timings.duration, timings.delay, timings.easing);
    }
    function normalizeAnimationOptions(options) {
        if (options) {
            options = copyObj(options);
            if (options['params']) {
                options['params'] = normalizeParams(options['params']);
            }
        }
        else {
            options = {};
        }
        return options;
    }
    function makeTimingAst(duration, delay, easing) {
        return { duration: duration, delay: delay, easing: easing };
    }

    function createTimelineInstruction(element, keyframes, preStyleProps, postStyleProps, duration, delay, easing, subTimeline) {
        if (easing === void 0) { easing = null; }
        if (subTimeline === void 0) { subTimeline = false; }
        return {
            type: 1 /* TimelineAnimation */,
            element: element,
            keyframes: keyframes,
            preStyleProps: preStyleProps,
            postStyleProps: postStyleProps,
            duration: duration,
            delay: delay,
            totalTime: duration + delay,
            easing: easing,
            subTimeline: subTimeline
        };
    }

    var ElementInstructionMap = /** @class */ (function () {
        function ElementInstructionMap() {
            this._map = new Map();
        }
        ElementInstructionMap.prototype.consume = function (element) {
            var instructions = this._map.get(element);
            if (instructions) {
                this._map.delete(element);
            }
            else {
                instructions = [];
            }
            return instructions;
        };
        ElementInstructionMap.prototype.append = function (element, instructions) {
            var existingInstructions = this._map.get(element);
            if (!existingInstructions) {
                this._map.set(element, existingInstructions = []);
            }
            existingInstructions.push.apply(existingInstructions, __spread(instructions));
        };
        ElementInstructionMap.prototype.has = function (element) {
            return this._map.has(element);
        };
        ElementInstructionMap.prototype.clear = function () {
            this._map.clear();
        };
        return ElementInstructionMap;
    }());

    var ONE_FRAME_IN_MILLISECONDS = 1;
    var ENTER_TOKEN = ':enter';
    var ENTER_TOKEN_REGEX = new RegExp(ENTER_TOKEN, 'g');
    var LEAVE_TOKEN = ':leave';
    var LEAVE_TOKEN_REGEX = new RegExp(LEAVE_TOKEN, 'g');
    /*
     * The code within this file aims to generate web-animations-compatible keyframes from Angular's
     * animation DSL code.
     *
     * The code below will be converted from:
     *
     * ```
     * sequence([
     *   style({ opacity: 0 }),
     *   animate(1000, style({ opacity: 0 }))
     * ])
     * ```
     *
     * To:
     * ```
     * keyframes = [{ opacity: 0, offset: 0 }, { opacity: 1, offset: 1 }]
     * duration = 1000
     * delay = 0
     * easing = ''
     * ```
     *
     * For this operation to cover the combination of animation verbs (style, animate, group, etc...) a
     * combination of prototypical inheritance, AST traversal and merge-sort-like algorithms are used.
     *
     * [AST Traversal]
     * Each of the animation verbs, when executed, will return an string-map object representing what
     * type of action it is (style, animate, group, etc...) and the data associated with it. This means
     * that when functional composition mix of these functions is evaluated (like in the example above)
     * then it will end up producing a tree of objects representing the animation itself.
     *
     * When this animation object tree is processed by the visitor code below it will visit each of the
     * verb statements within the visitor. And during each visit it will build the context of the
     * animation keyframes by interacting with the `TimelineBuilder`.
     *
     * [TimelineBuilder]
     * This class is responsible for tracking the styles and building a series of keyframe objects for a
     * timeline between a start and end time. The builder starts off with an initial timeline and each
     * time the AST comes across a `group()`, `keyframes()` or a combination of the two wihtin a
     * `sequence()` then it will generate a sub timeline for each step as well as a new one after
     * they are complete.
     *
     * As the AST is traversed, the timing state on each of the timelines will be incremented. If a sub
     * timeline was created (based on one of the cases above) then the parent timeline will attempt to
     * merge the styles used within the sub timelines into itself (only with group() this will happen).
     * This happens with a merge operation (much like how the merge works in mergesort) and it will only
     * copy the most recently used styles from the sub timelines into the parent timeline. This ensures
     * that if the styles are used later on in another phase of the animation then they will be the most
     * up-to-date values.
     *
     * [How Missing Styles Are Updated]
     * Each timeline has a `backFill` property which is responsible for filling in new styles into
     * already processed keyframes if a new style shows up later within the animation sequence.
     *
     * ```
     * sequence([
     *   style({ width: 0 }),
     *   animate(1000, style({ width: 100 })),
     *   animate(1000, style({ width: 200 })),
     *   animate(1000, style({ width: 300 }))
     *   animate(1000, style({ width: 400, height: 400 })) // notice how `height` doesn't exist anywhere
     * else
     * ])
     * ```
     *
     * What is happening here is that the `height` value is added later in the sequence, but is missing
     * from all previous animation steps. Therefore when a keyframe is created it would also be missing
     * from all previous keyframes up until where it is first used. For the timeline keyframe generation
     * to properly fill in the style it will place the previous value (the value from the parent
     * timeline) or a default value of `*` into the backFill object. Given that each of the keyframe
     * styles are objects that prototypically inhert from the backFill object, this means that if a
     * value is added into the backFill then it will automatically propagate any missing values to all
     * keyframes. Therefore the missing `height` value will be properly filled into the already
     * processed keyframes.
     *
     * When a sub-timeline is created it will have its own backFill property. This is done so that
     * styles present within the sub-timeline do not accidentally seep into the previous/future timeline
     * keyframes
     *
     * (For prototypically-inherited contents to be detected a `for(i in obj)` loop must be used.)
     *
     * [Validation]
     * The code in this file is not responsible for validation. That functionality happens with within
     * the `AnimationValidatorVisitor` code.
     */
    function buildAnimationTimelines(driver, rootElement, ast, enterClassName, leaveClassName, startingStyles, finalStyles, options, subInstructions, errors) {
        if (startingStyles === void 0) { startingStyles = {}; }
        if (finalStyles === void 0) { finalStyles = {}; }
        if (errors === void 0) { errors = []; }
        return new AnimationTimelineBuilderVisitor().buildKeyframes(driver, rootElement, ast, enterClassName, leaveClassName, startingStyles, finalStyles, options, subInstructions, errors);
    }
    var AnimationTimelineBuilderVisitor = /** @class */ (function () {
        function AnimationTimelineBuilderVisitor() {
        }
        AnimationTimelineBuilderVisitor.prototype.buildKeyframes = function (driver, rootElement, ast, enterClassName, leaveClassName, startingStyles, finalStyles, options, subInstructions, errors) {
            if (errors === void 0) { errors = []; }
            subInstructions = subInstructions || new ElementInstructionMap();
            var context = new AnimationTimelineContext(driver, rootElement, subInstructions, enterClassName, leaveClassName, errors, []);
            context.options = options;
            context.currentTimeline.setStyles([startingStyles], null, context.errors, options);
            visitDslNode(this, ast, context);
            // this checks to see if an actual animation happened
            var timelines = context.timelines.filter(function (timeline) { return timeline.containsAnimation(); });
            if (timelines.length && Object.keys(finalStyles).length) {
                var tl = timelines[timelines.length - 1];
                if (!tl.allowOnlyTimelineStyles()) {
                    tl.setStyles([finalStyles], null, context.errors, options);
                }
            }
            return timelines.length ? timelines.map(function (timeline) { return timeline.buildKeyframes(); }) :
                [createTimelineInstruction(rootElement, [], [], [], 0, 0, '', false)];
        };
        AnimationTimelineBuilderVisitor.prototype.visitTrigger = function (ast, context) {
            // these values are not visited in this AST
        };
        AnimationTimelineBuilderVisitor.prototype.visitState = function (ast, context) {
            // these values are not visited in this AST
        };
        AnimationTimelineBuilderVisitor.prototype.visitTransition = function (ast, context) {
            // these values are not visited in this AST
        };
        AnimationTimelineBuilderVisitor.prototype.visitAnimateChild = function (ast, context) {
            var elementInstructions = context.subInstructions.consume(context.element);
            if (elementInstructions) {
                var innerContext = context.createSubContext(ast.options);
                var startTime = context.currentTimeline.currentTime;
                var endTime = this._visitSubInstructions(elementInstructions, innerContext, innerContext.options);
                if (startTime != endTime) {
                    // we do this on the upper context because we created a sub context for
                    // the sub child animations
                    context.transformIntoNewTimeline(endTime);
                }
            }
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitAnimateRef = function (ast, context) {
            var innerContext = context.createSubContext(ast.options);
            innerContext.transformIntoNewTimeline();
            this.visitReference(ast.animation, innerContext);
            context.transformIntoNewTimeline(innerContext.currentTimeline.currentTime);
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype._visitSubInstructions = function (instructions, context, options) {
            var startTime = context.currentTimeline.currentTime;
            var furthestTime = startTime;
            // this is a special-case for when a user wants to skip a sub
            // animation from being fired entirely.
            var duration = options.duration != null ? resolveTimingValue(options.duration) : null;
            var delay = options.delay != null ? resolveTimingValue(options.delay) : null;
            if (duration !== 0) {
                instructions.forEach(function (instruction) {
                    var instructionTimings = context.appendInstructionToTimeline(instruction, duration, delay);
                    furthestTime =
                        Math.max(furthestTime, instructionTimings.duration + instructionTimings.delay);
                });
            }
            return furthestTime;
        };
        AnimationTimelineBuilderVisitor.prototype.visitReference = function (ast, context) {
            context.updateOptions(ast.options, true);
            visitDslNode(this, ast.animation, context);
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitSequence = function (ast, context) {
            var _this = this;
            var subContextCount = context.subContextCount;
            var ctx = context;
            var options = ast.options;
            if (options && (options.params || options.delay)) {
                ctx = context.createSubContext(options);
                ctx.transformIntoNewTimeline();
                if (options.delay != null) {
                    if (ctx.previousNode.type == 6 /* Style */) {
                        ctx.currentTimeline.snapshotCurrentStyles();
                        ctx.previousNode = DEFAULT_NOOP_PREVIOUS_NODE;
                    }
                    var delay = resolveTimingValue(options.delay);
                    ctx.delayNextStep(delay);
                }
            }
            if (ast.steps.length) {
                ast.steps.forEach(function (s) { return visitDslNode(_this, s, ctx); });
                // this is here just incase the inner steps only contain or end with a style() call
                ctx.currentTimeline.applyStylesToKeyframe();
                // this means that some animation function within the sequence
                // ended up creating a sub timeline (which means the current
                // timeline cannot overlap with the contents of the sequence)
                if (ctx.subContextCount > subContextCount) {
                    ctx.transformIntoNewTimeline();
                }
            }
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitGroup = function (ast, context) {
            var _this = this;
            var innerTimelines = [];
            var furthestTime = context.currentTimeline.currentTime;
            var delay = ast.options && ast.options.delay ? resolveTimingValue(ast.options.delay) : 0;
            ast.steps.forEach(function (s) {
                var innerContext = context.createSubContext(ast.options);
                if (delay) {
                    innerContext.delayNextStep(delay);
                }
                visitDslNode(_this, s, innerContext);
                furthestTime = Math.max(furthestTime, innerContext.currentTimeline.currentTime);
                innerTimelines.push(innerContext.currentTimeline);
            });
            // this operation is run after the AST loop because otherwise
            // if the parent timeline's collected styles were updated then
            // it would pass in invalid data into the new-to-be forked items
            innerTimelines.forEach(function (timeline) { return context.currentTimeline.mergeTimelineCollectedStyles(timeline); });
            context.transformIntoNewTimeline(furthestTime);
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype._visitTiming = function (ast, context) {
            if (ast.dynamic) {
                var strValue = ast.strValue;
                var timingValue = context.params ? interpolateParams(strValue, context.params, context.errors) : strValue;
                return resolveTiming(timingValue, context.errors);
            }
            else {
                return { duration: ast.duration, delay: ast.delay, easing: ast.easing };
            }
        };
        AnimationTimelineBuilderVisitor.prototype.visitAnimate = function (ast, context) {
            var timings = context.currentAnimateTimings = this._visitTiming(ast.timings, context);
            var timeline = context.currentTimeline;
            if (timings.delay) {
                context.incrementTime(timings.delay);
                timeline.snapshotCurrentStyles();
            }
            var style = ast.style;
            if (style.type == 5 /* Keyframes */) {
                this.visitKeyframes(style, context);
            }
            else {
                context.incrementTime(timings.duration);
                this.visitStyle(style, context);
                timeline.applyStylesToKeyframe();
            }
            context.currentAnimateTimings = null;
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitStyle = function (ast, context) {
            var timeline = context.currentTimeline;
            var timings = context.currentAnimateTimings;
            // this is a special case for when a style() call
            // directly follows  an animate() call (but not inside of an animate() call)
            if (!timings && timeline.getCurrentStyleProperties().length) {
                timeline.forwardFrame();
            }
            var easing = (timings && timings.easing) || ast.easing;
            if (ast.isEmptyStep) {
                timeline.applyEmptyStep(easing);
            }
            else {
                timeline.setStyles(ast.styles, easing, context.errors, context.options);
            }
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitKeyframes = function (ast, context) {
            var currentAnimateTimings = context.currentAnimateTimings;
            var startTime = (context.currentTimeline).duration;
            var duration = currentAnimateTimings.duration;
            var innerContext = context.createSubContext();
            var innerTimeline = innerContext.currentTimeline;
            innerTimeline.easing = currentAnimateTimings.easing;
            ast.styles.forEach(function (step) {
                var offset = step.offset || 0;
                innerTimeline.forwardTime(offset * duration);
                innerTimeline.setStyles(step.styles, step.easing, context.errors, context.options);
                innerTimeline.applyStylesToKeyframe();
            });
            // this will ensure that the parent timeline gets all the styles from
            // the child even if the new timeline below is not used
            context.currentTimeline.mergeTimelineCollectedStyles(innerTimeline);
            // we do this because the window between this timeline and the sub timeline
            // should ensure that the styles within are exactly the same as they were before
            context.transformIntoNewTimeline(startTime + duration);
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitQuery = function (ast, context) {
            var _this = this;
            // in the event that the first step before this is a style step we need
            // to ensure the styles are applied before the children are animated
            var startTime = context.currentTimeline.currentTime;
            var options = (ast.options || {});
            var delay = options.delay ? resolveTimingValue(options.delay) : 0;
            if (delay &&
                (context.previousNode.type === 6 /* Style */ ||
                    (startTime == 0 && context.currentTimeline.getCurrentStyleProperties().length))) {
                context.currentTimeline.snapshotCurrentStyles();
                context.previousNode = DEFAULT_NOOP_PREVIOUS_NODE;
            }
            var furthestTime = startTime;
            var elms = context.invokeQuery(ast.selector, ast.originalSelector, ast.limit, ast.includeSelf, options.optional ? true : false, context.errors);
            context.currentQueryTotal = elms.length;
            var sameElementTimeline = null;
            elms.forEach(function (element, i) {
                context.currentQueryIndex = i;
                var innerContext = context.createSubContext(ast.options, element);
                if (delay) {
                    innerContext.delayNextStep(delay);
                }
                if (element === context.element) {
                    sameElementTimeline = innerContext.currentTimeline;
                }
                visitDslNode(_this, ast.animation, innerContext);
                // this is here just incase the inner steps only contain or end
                // with a style() call (which is here to signal that this is a preparatory
                // call to style an element before it is animated again)
                innerContext.currentTimeline.applyStylesToKeyframe();
                var endTime = innerContext.currentTimeline.currentTime;
                furthestTime = Math.max(furthestTime, endTime);
            });
            context.currentQueryIndex = 0;
            context.currentQueryTotal = 0;
            context.transformIntoNewTimeline(furthestTime);
            if (sameElementTimeline) {
                context.currentTimeline.mergeTimelineCollectedStyles(sameElementTimeline);
                context.currentTimeline.snapshotCurrentStyles();
            }
            context.previousNode = ast;
        };
        AnimationTimelineBuilderVisitor.prototype.visitStagger = function (ast, context) {
            var parentContext = context.parentContext;
            var tl = context.currentTimeline;
            var timings = ast.timings;
            var duration = Math.abs(timings.duration);
            var maxTime = duration * (context.currentQueryTotal - 1);
            var delay = duration * context.currentQueryIndex;
            var staggerTransformer = timings.duration < 0 ? 'reverse' : timings.easing;
            switch (staggerTransformer) {
                case 'reverse':
                    delay = maxTime - delay;
                    break;
                case 'full':
                    delay = parentContext.currentStaggerTime;
                    break;
            }
            var timeline = context.currentTimeline;
            if (delay) {
                timeline.delayNextStep(delay);
            }
            var startingTime = timeline.currentTime;
            visitDslNode(this, ast.animation, context);
            context.previousNode = ast;
            // time = duration + delay
            // the reason why this computation is so complex is because
            // the inner timeline may either have a delay value or a stretched
            // keyframe depending on if a subtimeline is not used or is used.
            parentContext.currentStaggerTime =
                (tl.currentTime - startingTime) + (tl.startTime - parentContext.currentTimeline.startTime);
        };
        return AnimationTimelineBuilderVisitor;
    }());
    var DEFAULT_NOOP_PREVIOUS_NODE = {};
    var AnimationTimelineContext = /** @class */ (function () {
        function AnimationTimelineContext(_driver, element, subInstructions, _enterClassName, _leaveClassName, errors, timelines, initialTimeline) {
            this._driver = _driver;
            this.element = element;
            this.subInstructions = subInstructions;
            this._enterClassName = _enterClassName;
            this._leaveClassName = _leaveClassName;
            this.errors = errors;
            this.timelines = timelines;
            this.parentContext = null;
            this.currentAnimateTimings = null;
            this.previousNode = DEFAULT_NOOP_PREVIOUS_NODE;
            this.subContextCount = 0;
            this.options = {};
            this.currentQueryIndex = 0;
            this.currentQueryTotal = 0;
            this.currentStaggerTime = 0;
            this.currentTimeline = initialTimeline || new TimelineBuilder(this._driver, element, 0);
            timelines.push(this.currentTimeline);
        }
        Object.defineProperty(AnimationTimelineContext.prototype, "params", {
            get: function () {
                return this.options.params;
            },
            enumerable: false,
            configurable: true
        });
        AnimationTimelineContext.prototype.updateOptions = function (options, skipIfExists) {
            var _this = this;
            if (!options)
                return;
            var newOptions = options;
            var optionsToUpdate = this.options;
            // NOTE: this will get patched up when other animation methods support duration overrides
            if (newOptions.duration != null) {
                optionsToUpdate.duration = resolveTimingValue(newOptions.duration);
            }
            if (newOptions.delay != null) {
                optionsToUpdate.delay = resolveTimingValue(newOptions.delay);
            }
            var newParams = newOptions.params;
            if (newParams) {
                var paramsToUpdate_1 = optionsToUpdate.params;
                if (!paramsToUpdate_1) {
                    paramsToUpdate_1 = this.options.params = {};
                }
                Object.keys(newParams).forEach(function (name) {
                    if (!skipIfExists || !paramsToUpdate_1.hasOwnProperty(name)) {
                        paramsToUpdate_1[name] = interpolateParams(newParams[name], paramsToUpdate_1, _this.errors);
                    }
                });
            }
        };
        AnimationTimelineContext.prototype._copyOptions = function () {
            var options = {};
            if (this.options) {
                var oldParams_1 = this.options.params;
                if (oldParams_1) {
                    var params_1 = options['params'] = {};
                    Object.keys(oldParams_1).forEach(function (name) {
                        params_1[name] = oldParams_1[name];
                    });
                }
            }
            return options;
        };
        AnimationTimelineContext.prototype.createSubContext = function (options, element, newTime) {
            if (options === void 0) { options = null; }
            var target = element || this.element;
            var context = new AnimationTimelineContext(this._driver, target, this.subInstructions, this._enterClassName, this._leaveClassName, this.errors, this.timelines, this.currentTimeline.fork(target, newTime || 0));
            context.previousNode = this.previousNode;
            context.currentAnimateTimings = this.currentAnimateTimings;
            context.options = this._copyOptions();
            context.updateOptions(options);
            context.currentQueryIndex = this.currentQueryIndex;
            context.currentQueryTotal = this.currentQueryTotal;
            context.parentContext = this;
            this.subContextCount++;
            return context;
        };
        AnimationTimelineContext.prototype.transformIntoNewTimeline = function (newTime) {
            this.previousNode = DEFAULT_NOOP_PREVIOUS_NODE;
            this.currentTimeline = this.currentTimeline.fork(this.element, newTime);
            this.timelines.push(this.currentTimeline);
            return this.currentTimeline;
        };
        AnimationTimelineContext.prototype.appendInstructionToTimeline = function (instruction, duration, delay) {
            var updatedTimings = {
                duration: duration != null ? duration : instruction.duration,
                delay: this.currentTimeline.currentTime + (delay != null ? delay : 0) + instruction.delay,
                easing: ''
            };
            var builder = new SubTimelineBuilder(this._driver, instruction.element, instruction.keyframes, instruction.preStyleProps, instruction.postStyleProps, updatedTimings, instruction.stretchStartingKeyframe);
            this.timelines.push(builder);
            return updatedTimings;
        };
        AnimationTimelineContext.prototype.incrementTime = function (time) {
            this.currentTimeline.forwardTime(this.currentTimeline.duration + time);
        };
        AnimationTimelineContext.prototype.delayNextStep = function (delay) {
            // negative delays are not yet supported
            if (delay > 0) {
                this.currentTimeline.delayNextStep(delay);
            }
        };
        AnimationTimelineContext.prototype.invokeQuery = function (selector, originalSelector, limit, includeSelf, optional, errors) {
            var results = [];
            if (includeSelf) {
                results.push(this.element);
            }
            if (selector.length > 0) { // if :self is only used then the selector is empty
                selector = selector.replace(ENTER_TOKEN_REGEX, '.' + this._enterClassName);
                selector = selector.replace(LEAVE_TOKEN_REGEX, '.' + this._leaveClassName);
                var multi = limit != 1;
                var elements = this._driver.query(this.element, selector, multi);
                if (limit !== 0) {
                    elements = limit < 0 ? elements.slice(elements.length + limit, elements.length) :
                        elements.slice(0, limit);
                }
                results.push.apply(results, __spread(elements));
            }
            if (!optional && results.length == 0) {
                errors.push("`query(\"" + originalSelector + "\")` returned zero elements. (Use `query(\"" + originalSelector + "\", { optional: true })` if you wish to allow this.)");
            }
            return results;
        };
        return AnimationTimelineContext;
    }());
    var TimelineBuilder = /** @class */ (function () {
        function TimelineBuilder(_driver, element, startTime, _elementTimelineStylesLookup) {
            this._driver = _driver;
            this.element = element;
            this.startTime = startTime;
            this._elementTimelineStylesLookup = _elementTimelineStylesLookup;
            this.duration = 0;
            this._previousKeyframe = {};
            this._currentKeyframe = {};
            this._keyframes = new Map();
            this._styleSummary = {};
            this._pendingStyles = {};
            this._backFill = {};
            this._currentEmptyStepKeyframe = null;
            if (!this._elementTimelineStylesLookup) {
                this._elementTimelineStylesLookup = new Map();
            }
            this._localTimelineStyles = Object.create(this._backFill, {});
            this._globalTimelineStyles = this._elementTimelineStylesLookup.get(element);
            if (!this._globalTimelineStyles) {
                this._globalTimelineStyles = this._localTimelineStyles;
                this._elementTimelineStylesLookup.set(element, this._localTimelineStyles);
            }
            this._loadKeyframe();
        }
        TimelineBuilder.prototype.containsAnimation = function () {
            switch (this._keyframes.size) {
                case 0:
                    return false;
                case 1:
                    return this.getCurrentStyleProperties().length > 0;
                default:
                    return true;
            }
        };
        TimelineBuilder.prototype.getCurrentStyleProperties = function () {
            return Object.keys(this._currentKeyframe);
        };
        Object.defineProperty(TimelineBuilder.prototype, "currentTime", {
            get: function () {
                return this.startTime + this.duration;
            },
            enumerable: false,
            configurable: true
        });
        TimelineBuilder.prototype.delayNextStep = function (delay) {
            // in the event that a style() step is placed right before a stagger()
            // and that style() step is the very first style() value in the animation
            // then we need to make a copy of the keyframe [0, copy, 1] so that the delay
            // properly applies the style() values to work with the stagger...
            var hasPreStyleStep = this._keyframes.size == 1 && Object.keys(this._pendingStyles).length;
            if (this.duration || hasPreStyleStep) {
                this.forwardTime(this.currentTime + delay);
                if (hasPreStyleStep) {
                    this.snapshotCurrentStyles();
                }
            }
            else {
                this.startTime += delay;
            }
        };
        TimelineBuilder.prototype.fork = function (element, currentTime) {
            this.applyStylesToKeyframe();
            return new TimelineBuilder(this._driver, element, currentTime || this.currentTime, this._elementTimelineStylesLookup);
        };
        TimelineBuilder.prototype._loadKeyframe = function () {
            if (this._currentKeyframe) {
                this._previousKeyframe = this._currentKeyframe;
            }
            this._currentKeyframe = this._keyframes.get(this.duration);
            if (!this._currentKeyframe) {
                this._currentKeyframe = Object.create(this._backFill, {});
                this._keyframes.set(this.duration, this._currentKeyframe);
            }
        };
        TimelineBuilder.prototype.forwardFrame = function () {
            this.duration += ONE_FRAME_IN_MILLISECONDS;
            this._loadKeyframe();
        };
        TimelineBuilder.prototype.forwardTime = function (time) {
            this.applyStylesToKeyframe();
            this.duration = time;
            this._loadKeyframe();
        };
        TimelineBuilder.prototype._updateStyle = function (prop, value) {
            this._localTimelineStyles[prop] = value;
            this._globalTimelineStyles[prop] = value;
            this._styleSummary[prop] = { time: this.currentTime, value: value };
        };
        TimelineBuilder.prototype.allowOnlyTimelineStyles = function () {
            return this._currentEmptyStepKeyframe !== this._currentKeyframe;
        };
        TimelineBuilder.prototype.applyEmptyStep = function (easing) {
            var _this = this;
            if (easing) {
                this._previousKeyframe['easing'] = easing;
            }
            // special case for animate(duration):
            // all missing styles are filled with a `*` value then
            // if any destination styles are filled in later on the same
            // keyframe then they will override the overridden styles
            // We use `_globalTimelineStyles` here because there may be
            // styles in previous keyframes that are not present in this timeline
            Object.keys(this._globalTimelineStyles).forEach(function (prop) {
                _this._backFill[prop] = _this._globalTimelineStyles[prop] || animations.AUTO_STYLE;
                _this._currentKeyframe[prop] = animations.AUTO_STYLE;
            });
            this._currentEmptyStepKeyframe = this._currentKeyframe;
        };
        TimelineBuilder.prototype.setStyles = function (input, easing, errors, options) {
            var _this = this;
            if (easing) {
                this._previousKeyframe['easing'] = easing;
            }
            var params = (options && options.params) || {};
            var styles = flattenStyles(input, this._globalTimelineStyles);
            Object.keys(styles).forEach(function (prop) {
                var val = interpolateParams(styles[prop], params, errors);
                _this._pendingStyles[prop] = val;
                if (!_this._localTimelineStyles.hasOwnProperty(prop)) {
                    _this._backFill[prop] = _this._globalTimelineStyles.hasOwnProperty(prop) ?
                        _this._globalTimelineStyles[prop] :
                        animations.AUTO_STYLE;
                }
                _this._updateStyle(prop, val);
            });
        };
        TimelineBuilder.prototype.applyStylesToKeyframe = function () {
            var _this = this;
            var styles = this._pendingStyles;
            var props = Object.keys(styles);
            if (props.length == 0)
                return;
            this._pendingStyles = {};
            props.forEach(function (prop) {
                var val = styles[prop];
                _this._currentKeyframe[prop] = val;
            });
            Object.keys(this._localTimelineStyles).forEach(function (prop) {
                if (!_this._currentKeyframe.hasOwnProperty(prop)) {
                    _this._currentKeyframe[prop] = _this._localTimelineStyles[prop];
                }
            });
        };
        TimelineBuilder.prototype.snapshotCurrentStyles = function () {
            var _this = this;
            Object.keys(this._localTimelineStyles).forEach(function (prop) {
                var val = _this._localTimelineStyles[prop];
                _this._pendingStyles[prop] = val;
                _this._updateStyle(prop, val);
            });
        };
        TimelineBuilder.prototype.getFinalKeyframe = function () {
            return this._keyframes.get(this.duration);
        };
        Object.defineProperty(TimelineBuilder.prototype, "properties", {
            get: function () {
                var properties = [];
                for (var prop in this._currentKeyframe) {
                    properties.push(prop);
                }
                return properties;
            },
            enumerable: false,
            configurable: true
        });
        TimelineBuilder.prototype.mergeTimelineCollectedStyles = function (timeline) {
            var _this = this;
            Object.keys(timeline._styleSummary).forEach(function (prop) {
                var details0 = _this._styleSummary[prop];
                var details1 = timeline._styleSummary[prop];
                if (!details0 || details1.time > details0.time) {
                    _this._updateStyle(prop, details1.value);
                }
            });
        };
        TimelineBuilder.prototype.buildKeyframes = function () {
            var _this = this;
            this.applyStylesToKeyframe();
            var preStyleProps = new Set();
            var postStyleProps = new Set();
            var isEmpty = this._keyframes.size === 1 && this.duration === 0;
            var finalKeyframes = [];
            this._keyframes.forEach(function (keyframe, time) {
                var finalKeyframe = copyStyles(keyframe, true);
                Object.keys(finalKeyframe).forEach(function (prop) {
                    var value = finalKeyframe[prop];
                    if (value == animations.ɵPRE_STYLE) {
                        preStyleProps.add(prop);
                    }
                    else if (value == animations.AUTO_STYLE) {
                        postStyleProps.add(prop);
                    }
                });
                if (!isEmpty) {
                    finalKeyframe['offset'] = time / _this.duration;
                }
                finalKeyframes.push(finalKeyframe);
            });
            var preProps = preStyleProps.size ? iteratorToArray(preStyleProps.values()) : [];
            var postProps = postStyleProps.size ? iteratorToArray(postStyleProps.values()) : [];
            // special case for a 0-second animation (which is designed just to place styles onscreen)
            if (isEmpty) {
                var kf0 = finalKeyframes[0];
                var kf1 = copyObj(kf0);
                kf0['offset'] = 0;
                kf1['offset'] = 1;
                finalKeyframes = [kf0, kf1];
            }
            return createTimelineInstruction(this.element, finalKeyframes, preProps, postProps, this.duration, this.startTime, this.easing, false);
        };
        return TimelineBuilder;
    }());
    var SubTimelineBuilder = /** @class */ (function (_super) {
        __extends(SubTimelineBuilder, _super);
        function SubTimelineBuilder(driver, element, keyframes, preStyleProps, postStyleProps, timings, _stretchStartingKeyframe) {
            if (_stretchStartingKeyframe === void 0) { _stretchStartingKeyframe = false; }
            var _this = _super.call(this, driver, element, timings.delay) || this;
            _this.element = element;
            _this.keyframes = keyframes;
            _this.preStyleProps = preStyleProps;
            _this.postStyleProps = postStyleProps;
            _this._stretchStartingKeyframe = _stretchStartingKeyframe;
            _this.timings = { duration: timings.duration, delay: timings.delay, easing: timings.easing };
            return _this;
        }
        SubTimelineBuilder.prototype.containsAnimation = function () {
            return this.keyframes.length > 1;
        };
        SubTimelineBuilder.prototype.buildKeyframes = function () {
            var keyframes = this.keyframes;
            var _a = this.timings, delay = _a.delay, duration = _a.duration, easing = _a.easing;
            if (this._stretchStartingKeyframe && delay) {
                var newKeyframes = [];
                var totalTime = duration + delay;
                var startingGap = delay / totalTime;
                // the original starting keyframe now starts once the delay is done
                var newFirstKeyframe = copyStyles(keyframes[0], false);
                newFirstKeyframe['offset'] = 0;
                newKeyframes.push(newFirstKeyframe);
                var oldFirstKeyframe = copyStyles(keyframes[0], false);
                oldFirstKeyframe['offset'] = roundOffset(startingGap);
                newKeyframes.push(oldFirstKeyframe);
                /*
                  When the keyframe is stretched then it means that the delay before the animation
                  starts is gone. Instead the first keyframe is placed at the start of the animation
                  and it is then copied to where it starts when the original delay is over. This basically
                  means nothing animates during that delay, but the styles are still renderered. For this
                  to work the original offset values that exist in the original keyframes must be "warped"
                  so that they can take the new keyframe + delay into account.
          
                  delay=1000, duration=1000, keyframes = 0 .5 1
          
                  turns into
          
                  delay=0, duration=2000, keyframes = 0 .33 .66 1
                 */
                // offsets between 1 ... n -1 are all warped by the keyframe stretch
                var limit = keyframes.length - 1;
                for (var i = 1; i <= limit; i++) {
                    var kf = copyStyles(keyframes[i], false);
                    var oldOffset = kf['offset'];
                    var timeAtKeyframe = delay + oldOffset * duration;
                    kf['offset'] = roundOffset(timeAtKeyframe / totalTime);
                    newKeyframes.push(kf);
                }
                // the new starting keyframe should be added at the start
                duration = totalTime;
                delay = 0;
                easing = '';
                keyframes = newKeyframes;
            }
            return createTimelineInstruction(this.element, keyframes, this.preStyleProps, this.postStyleProps, duration, delay, easing, true);
        };
        return SubTimelineBuilder;
    }(TimelineBuilder));
    function roundOffset(offset, decimalPoints) {
        if (decimalPoints === void 0) { decimalPoints = 3; }
        var mult = Math.pow(10, decimalPoints - 1);
        return Math.round(offset * mult) / mult;
    }
    function flattenStyles(input, allStyles) {
        var styles = {};
        var allProperties;
        input.forEach(function (token) {
            if (token === '*') {
                allProperties = allProperties || Object.keys(allStyles);
                allProperties.forEach(function (prop) {
                    styles[prop] = animations.AUTO_STYLE;
                });
            }
            else {
                copyStyles(token, false, styles);
            }
        });
        return styles;
    }

    var Animation = /** @class */ (function () {
        function Animation(_driver, input) {
            this._driver = _driver;
            var errors = [];
            var ast = buildAnimationAst(_driver, input, errors);
            if (errors.length) {
                var errorMessage = "animation validation failed:\n" + errors.join('\n');
                throw new Error(errorMessage);
            }
            this._animationAst = ast;
        }
        Animation.prototype.buildTimelines = function (element, startingStyles, destinationStyles, options, subInstructions) {
            var start = Array.isArray(startingStyles) ? normalizeStyles(startingStyles) :
                startingStyles;
            var dest = Array.isArray(destinationStyles) ? normalizeStyles(destinationStyles) :
                destinationStyles;
            var errors = [];
            subInstructions = subInstructions || new ElementInstructionMap();
            var result = buildAnimationTimelines(this._driver, element, this._animationAst, ENTER_CLASSNAME, LEAVE_CLASSNAME, start, dest, options, subInstructions, errors);
            if (errors.length) {
                var errorMessage = "animation building failed:\n" + errors.join('\n');
                throw new Error(errorMessage);
            }
            return result;
        };
        return Animation;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @publicApi
     */
    var AnimationStyleNormalizer = /** @class */ (function () {
        function AnimationStyleNormalizer() {
        }
        return AnimationStyleNormalizer;
    }());
    /**
     * @publicApi
     */
    var NoopAnimationStyleNormalizer = /** @class */ (function () {
        function NoopAnimationStyleNormalizer() {
        }
        NoopAnimationStyleNormalizer.prototype.normalizePropertyName = function (propertyName, errors) {
            return propertyName;
        };
        NoopAnimationStyleNormalizer.prototype.normalizeStyleValue = function (userProvidedProperty, normalizedProperty, value, errors) {
            return value;
        };
        return NoopAnimationStyleNormalizer;
    }());

    var WebAnimationsStyleNormalizer = /** @class */ (function (_super) {
        __extends(WebAnimationsStyleNormalizer, _super);
        function WebAnimationsStyleNormalizer() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        WebAnimationsStyleNormalizer.prototype.normalizePropertyName = function (propertyName, errors) {
            return dashCaseToCamelCase(propertyName);
        };
        WebAnimationsStyleNormalizer.prototype.normalizeStyleValue = function (userProvidedProperty, normalizedProperty, value, errors) {
            var unit = '';
            var strVal = value.toString().trim();
            if (DIMENSIONAL_PROP_MAP[normalizedProperty] && value !== 0 && value !== '0') {
                if (typeof value === 'number') {
                    unit = 'px';
                }
                else {
                    var valAndSuffixMatch = value.match(/^[+-]?[\d\.]+([a-z]*)$/);
                    if (valAndSuffixMatch && valAndSuffixMatch[1].length == 0) {
                        errors.push("Please provide a CSS unit value for " + userProvidedProperty + ":" + value);
                    }
                }
            }
            return strVal + unit;
        };
        return WebAnimationsStyleNormalizer;
    }(AnimationStyleNormalizer));
    var ɵ0$1 = function () { return makeBooleanMap('width,height,minWidth,minHeight,maxWidth,maxHeight,left,top,bottom,right,fontSize,outlineWidth,outlineOffset,paddingTop,paddingLeft,paddingBottom,paddingRight,marginTop,marginLeft,marginBottom,marginRight,borderRadius,borderWidth,borderTopWidth,borderLeftWidth,borderRightWidth,borderBottomWidth,textIndent,perspective'
        .split(',')); };
    var DIMENSIONAL_PROP_MAP = (ɵ0$1)();
    function makeBooleanMap(keys) {
        var map = {};
        keys.forEach(function (key) { return map[key] = true; });
        return map;
    }

    function createTransitionInstruction(element, triggerName, fromState, toState, isRemovalTransition, fromStyles, toStyles, timelines, queriedElements, preStyleProps, postStyleProps, totalTime, errors) {
        return {
            type: 0 /* TransitionAnimation */,
            element: element,
            triggerName: triggerName,
            isRemovalTransition: isRemovalTransition,
            fromState: fromState,
            fromStyles: fromStyles,
            toState: toState,
            toStyles: toStyles,
            timelines: timelines,
            queriedElements: queriedElements,
            preStyleProps: preStyleProps,
            postStyleProps: postStyleProps,
            totalTime: totalTime,
            errors: errors
        };
    }

    var EMPTY_OBJECT = {};
    var AnimationTransitionFactory = /** @class */ (function () {
        function AnimationTransitionFactory(_triggerName, ast, _stateStyles) {
            this._triggerName = _triggerName;
            this.ast = ast;
            this._stateStyles = _stateStyles;
        }
        AnimationTransitionFactory.prototype.match = function (currentState, nextState, element, params) {
            return oneOrMoreTransitionsMatch(this.ast.matchers, currentState, nextState, element, params);
        };
        AnimationTransitionFactory.prototype.buildStyles = function (stateName, params, errors) {
            var backupStateStyler = this._stateStyles['*'];
            var stateStyler = this._stateStyles[stateName];
            var backupStyles = backupStateStyler ? backupStateStyler.buildStyles(params, errors) : {};
            return stateStyler ? stateStyler.buildStyles(params, errors) : backupStyles;
        };
        AnimationTransitionFactory.prototype.build = function (driver, element, currentState, nextState, enterClassName, leaveClassName, currentOptions, nextOptions, subInstructions, skipAstBuild) {
            var errors = [];
            var transitionAnimationParams = this.ast.options && this.ast.options.params || EMPTY_OBJECT;
            var currentAnimationParams = currentOptions && currentOptions.params || EMPTY_OBJECT;
            var currentStateStyles = this.buildStyles(currentState, currentAnimationParams, errors);
            var nextAnimationParams = nextOptions && nextOptions.params || EMPTY_OBJECT;
            var nextStateStyles = this.buildStyles(nextState, nextAnimationParams, errors);
            var queriedElements = new Set();
            var preStyleMap = new Map();
            var postStyleMap = new Map();
            var isRemoval = nextState === 'void';
            var animationOptions = { params: Object.assign(Object.assign({}, transitionAnimationParams), nextAnimationParams) };
            var timelines = skipAstBuild ?
                [] :
                buildAnimationTimelines(driver, element, this.ast.animation, enterClassName, leaveClassName, currentStateStyles, nextStateStyles, animationOptions, subInstructions, errors);
            var totalTime = 0;
            timelines.forEach(function (tl) {
                totalTime = Math.max(tl.duration + tl.delay, totalTime);
            });
            if (errors.length) {
                return createTransitionInstruction(element, this._triggerName, currentState, nextState, isRemoval, currentStateStyles, nextStateStyles, [], [], preStyleMap, postStyleMap, totalTime, errors);
            }
            timelines.forEach(function (tl) {
                var elm = tl.element;
                var preProps = getOrSetAsInMap(preStyleMap, elm, {});
                tl.preStyleProps.forEach(function (prop) { return preProps[prop] = true; });
                var postProps = getOrSetAsInMap(postStyleMap, elm, {});
                tl.postStyleProps.forEach(function (prop) { return postProps[prop] = true; });
                if (elm !== element) {
                    queriedElements.add(elm);
                }
            });
            var queriedElementsList = iteratorToArray(queriedElements.values());
            return createTransitionInstruction(element, this._triggerName, currentState, nextState, isRemoval, currentStateStyles, nextStateStyles, timelines, queriedElementsList, preStyleMap, postStyleMap, totalTime);
        };
        return AnimationTransitionFactory;
    }());
    function oneOrMoreTransitionsMatch(matchFns, currentState, nextState, element, params) {
        return matchFns.some(function (fn) { return fn(currentState, nextState, element, params); });
    }
    var AnimationStateStyles = /** @class */ (function () {
        function AnimationStateStyles(styles, defaultParams) {
            this.styles = styles;
            this.defaultParams = defaultParams;
        }
        AnimationStateStyles.prototype.buildStyles = function (params, errors) {
            var finalStyles = {};
            var combinedParams = copyObj(this.defaultParams);
            Object.keys(params).forEach(function (key) {
                var value = params[key];
                if (value != null) {
                    combinedParams[key] = value;
                }
            });
            this.styles.styles.forEach(function (value) {
                if (typeof value !== 'string') {
                    var styleObj_1 = value;
                    Object.keys(styleObj_1).forEach(function (prop) {
                        var val = styleObj_1[prop];
                        if (val.length > 1) {
                            val = interpolateParams(val, combinedParams, errors);
                        }
                        finalStyles[prop] = val;
                    });
                }
            });
            return finalStyles;
        };
        return AnimationStateStyles;
    }());

    /**
     * @publicApi
     */
    function buildTrigger(name, ast) {
        return new AnimationTrigger(name, ast);
    }
    /**
     * @publicApi
     */
    var AnimationTrigger = /** @class */ (function () {
        function AnimationTrigger(name, ast) {
            var _this = this;
            this.name = name;
            this.ast = ast;
            this.transitionFactories = [];
            this.states = {};
            ast.states.forEach(function (ast) {
                var defaultParams = (ast.options && ast.options.params) || {};
                _this.states[ast.name] = new AnimationStateStyles(ast.style, defaultParams);
            });
            balanceProperties(this.states, 'true', '1');
            balanceProperties(this.states, 'false', '0');
            ast.transitions.forEach(function (ast) {
                _this.transitionFactories.push(new AnimationTransitionFactory(name, ast, _this.states));
            });
            this.fallbackTransition = createFallbackTransition(name, this.states);
        }
        Object.defineProperty(AnimationTrigger.prototype, "containsQueries", {
            get: function () {
                return this.ast.queryCount > 0;
            },
            enumerable: false,
            configurable: true
        });
        AnimationTrigger.prototype.matchTransition = function (currentState, nextState, element, params) {
            var entry = this.transitionFactories.find(function (f) { return f.match(currentState, nextState, element, params); });
            return entry || null;
        };
        AnimationTrigger.prototype.matchStyles = function (currentState, params, errors) {
            return this.fallbackTransition.buildStyles(currentState, params, errors);
        };
        return AnimationTrigger;
    }());
    function createFallbackTransition(triggerName, states) {
        var matchers = [function (fromState, toState) { return true; }];
        var animation = { type: 2 /* Sequence */, steps: [], options: null };
        var transition = {
            type: 1 /* Transition */,
            animation: animation,
            matchers: matchers,
            options: null,
            queryCount: 0,
            depCount: 0
        };
        return new AnimationTransitionFactory(triggerName, transition, states);
    }
    function balanceProperties(obj, key1, key2) {
        if (obj.hasOwnProperty(key1)) {
            if (!obj.hasOwnProperty(key2)) {
                obj[key2] = obj[key1];
            }
        }
        else if (obj.hasOwnProperty(key2)) {
            obj[key1] = obj[key2];
        }
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var EMPTY_INSTRUCTION_MAP = new ElementInstructionMap();
    var TimelineAnimationEngine = /** @class */ (function () {
        function TimelineAnimationEngine(bodyNode, _driver, _normalizer) {
            this.bodyNode = bodyNode;
            this._driver = _driver;
            this._normalizer = _normalizer;
            this._animations = {};
            this._playersById = {};
            this.players = [];
        }
        TimelineAnimationEngine.prototype.register = function (id, metadata) {
            var errors = [];
            var ast = buildAnimationAst(this._driver, metadata, errors);
            if (errors.length) {
                throw new Error("Unable to build the animation due to the following errors: " + errors.join('\n'));
            }
            else {
                this._animations[id] = ast;
            }
        };
        TimelineAnimationEngine.prototype._buildPlayer = function (i, preStyles, postStyles) {
            var element = i.element;
            var keyframes = normalizeKeyframes(this._driver, this._normalizer, element, i.keyframes, preStyles, postStyles);
            return this._driver.animate(element, keyframes, i.duration, i.delay, i.easing, [], true);
        };
        TimelineAnimationEngine.prototype.create = function (id, element, options) {
            var _this = this;
            if (options === void 0) { options = {}; }
            var errors = [];
            var ast = this._animations[id];
            var instructions;
            var autoStylesMap = new Map();
            if (ast) {
                instructions = buildAnimationTimelines(this._driver, element, ast, ENTER_CLASSNAME, LEAVE_CLASSNAME, {}, {}, options, EMPTY_INSTRUCTION_MAP, errors);
                instructions.forEach(function (inst) {
                    var styles = getOrSetAsInMap(autoStylesMap, inst.element, {});
                    inst.postStyleProps.forEach(function (prop) { return styles[prop] = null; });
                });
            }
            else {
                errors.push('The requested animation doesn\'t exist or has already been destroyed');
                instructions = [];
            }
            if (errors.length) {
                throw new Error("Unable to create the animation due to the following errors: " + errors.join('\n'));
            }
            autoStylesMap.forEach(function (styles, element) {
                Object.keys(styles).forEach(function (prop) {
                    styles[prop] = _this._driver.computeStyle(element, prop, animations.AUTO_STYLE);
                });
            });
            var players = instructions.map(function (i) {
                var styles = autoStylesMap.get(i.element);
                return _this._buildPlayer(i, {}, styles);
            });
            var player = optimizeGroupPlayer(players);
            this._playersById[id] = player;
            player.onDestroy(function () { return _this.destroy(id); });
            this.players.push(player);
            return player;
        };
        TimelineAnimationEngine.prototype.destroy = function (id) {
            var player = this._getPlayer(id);
            player.destroy();
            delete this._playersById[id];
            var index = this.players.indexOf(player);
            if (index >= 0) {
                this.players.splice(index, 1);
            }
        };
        TimelineAnimationEngine.prototype._getPlayer = function (id) {
            var player = this._playersById[id];
            if (!player) {
                throw new Error("Unable to find the timeline player referenced by " + id);
            }
            return player;
        };
        TimelineAnimationEngine.prototype.listen = function (id, element, eventName, callback) {
            // triggerName, fromState, toState are all ignored for timeline animations
            var baseEvent = makeAnimationEvent(element, '', '', '');
            listenOnPlayer(this._getPlayer(id), eventName, baseEvent, callback);
            return function () { };
        };
        TimelineAnimationEngine.prototype.command = function (id, element, command, args) {
            if (command == 'register') {
                this.register(id, args[0]);
                return;
            }
            if (command == 'create') {
                var options = (args[0] || {});
                this.create(id, element, options);
                return;
            }
            var player = this._getPlayer(id);
            switch (command) {
                case 'play':
                    player.play();
                    break;
                case 'pause':
                    player.pause();
                    break;
                case 'reset':
                    player.reset();
                    break;
                case 'restart':
                    player.restart();
                    break;
                case 'finish':
                    player.finish();
                    break;
                case 'init':
                    player.init();
                    break;
                case 'setPosition':
                    player.setPosition(parseFloat(args[0]));
                    break;
                case 'destroy':
                    this.destroy(id);
                    break;
            }
        };
        return TimelineAnimationEngine;
    }());

    var QUEUED_CLASSNAME = 'ng-animate-queued';
    var QUEUED_SELECTOR = '.ng-animate-queued';
    var DISABLED_CLASSNAME = 'ng-animate-disabled';
    var DISABLED_SELECTOR = '.ng-animate-disabled';
    var STAR_CLASSNAME = 'ng-star-inserted';
    var STAR_SELECTOR = '.ng-star-inserted';
    var EMPTY_PLAYER_ARRAY = [];
    var NULL_REMOVAL_STATE = {
        namespaceId: '',
        setForRemoval: false,
        setForMove: false,
        hasAnimation: false,
        removedBeforeQueried: false
    };
    var NULL_REMOVED_QUERIED_STATE = {
        namespaceId: '',
        setForMove: false,
        setForRemoval: false,
        hasAnimation: false,
        removedBeforeQueried: true
    };
    var REMOVAL_FLAG = '__ng_removed';
    var StateValue = /** @class */ (function () {
        function StateValue(input, namespaceId) {
            if (namespaceId === void 0) { namespaceId = ''; }
            this.namespaceId = namespaceId;
            var isObj = input && input.hasOwnProperty('value');
            var value = isObj ? input['value'] : input;
            this.value = normalizeTriggerValue(value);
            if (isObj) {
                var options = copyObj(input);
                delete options['value'];
                this.options = options;
            }
            else {
                this.options = {};
            }
            if (!this.options.params) {
                this.options.params = {};
            }
        }
        Object.defineProperty(StateValue.prototype, "params", {
            get: function () {
                return this.options.params;
            },
            enumerable: false,
            configurable: true
        });
        StateValue.prototype.absorbOptions = function (options) {
            var newParams = options.params;
            if (newParams) {
                var oldParams_1 = this.options.params;
                Object.keys(newParams).forEach(function (prop) {
                    if (oldParams_1[prop] == null) {
                        oldParams_1[prop] = newParams[prop];
                    }
                });
            }
        };
        return StateValue;
    }());
    var VOID_VALUE = 'void';
    var DEFAULT_STATE_VALUE = new StateValue(VOID_VALUE);
    var AnimationTransitionNamespace = /** @class */ (function () {
        function AnimationTransitionNamespace(id, hostElement, _engine) {
            this.id = id;
            this.hostElement = hostElement;
            this._engine = _engine;
            this.players = [];
            this._triggers = {};
            this._queue = [];
            this._elementListeners = new Map();
            this._hostClassName = 'ng-tns-' + id;
            addClass(hostElement, this._hostClassName);
        }
        AnimationTransitionNamespace.prototype.listen = function (element, name, phase, callback) {
            var _this = this;
            if (!this._triggers.hasOwnProperty(name)) {
                throw new Error("Unable to listen on the animation trigger event \"" + phase + "\" because the animation trigger \"" + name + "\" doesn't exist!");
            }
            if (phase == null || phase.length == 0) {
                throw new Error("Unable to listen on the animation trigger \"" + name + "\" because the provided event is undefined!");
            }
            if (!isTriggerEventValid(phase)) {
                throw new Error("The provided animation trigger event \"" + phase + "\" for the animation trigger \"" + name + "\" is not supported!");
            }
            var listeners = getOrSetAsInMap(this._elementListeners, element, []);
            var data = { name: name, phase: phase, callback: callback };
            listeners.push(data);
            var triggersWithStates = getOrSetAsInMap(this._engine.statesByElement, element, {});
            if (!triggersWithStates.hasOwnProperty(name)) {
                addClass(element, NG_TRIGGER_CLASSNAME);
                addClass(element, NG_TRIGGER_CLASSNAME + '-' + name);
                triggersWithStates[name] = DEFAULT_STATE_VALUE;
            }
            return function () {
                // the event listener is removed AFTER the flush has occurred such
                // that leave animations callbacks can fire (otherwise if the node
                // is removed in between then the listeners would be deregistered)
                _this._engine.afterFlush(function () {
                    var index = listeners.indexOf(data);
                    if (index >= 0) {
                        listeners.splice(index, 1);
                    }
                    if (!_this._triggers[name]) {
                        delete triggersWithStates[name];
                    }
                });
            };
        };
        AnimationTransitionNamespace.prototype.register = function (name, ast) {
            if (this._triggers[name]) {
                // throw
                return false;
            }
            else {
                this._triggers[name] = ast;
                return true;
            }
        };
        AnimationTransitionNamespace.prototype._getTrigger = function (name) {
            var trigger = this._triggers[name];
            if (!trigger) {
                throw new Error("The provided animation trigger \"" + name + "\" has not been registered!");
            }
            return trigger;
        };
        AnimationTransitionNamespace.prototype.trigger = function (element, triggerName, value, defaultToFallback) {
            var _this = this;
            if (defaultToFallback === void 0) { defaultToFallback = true; }
            var trigger = this._getTrigger(triggerName);
            var player = new TransitionAnimationPlayer(this.id, triggerName, element);
            var triggersWithStates = this._engine.statesByElement.get(element);
            if (!triggersWithStates) {
                addClass(element, NG_TRIGGER_CLASSNAME);
                addClass(element, NG_TRIGGER_CLASSNAME + '-' + triggerName);
                this._engine.statesByElement.set(element, triggersWithStates = {});
            }
            var fromState = triggersWithStates[triggerName];
            var toState = new StateValue(value, this.id);
            var isObj = value && value.hasOwnProperty('value');
            if (!isObj && fromState) {
                toState.absorbOptions(fromState.options);
            }
            triggersWithStates[triggerName] = toState;
            if (!fromState) {
                fromState = DEFAULT_STATE_VALUE;
            }
            var isRemoval = toState.value === VOID_VALUE;
            // normally this isn't reached by here, however, if an object expression
            // is passed in then it may be a new object each time. Comparing the value
            // is important since that will stay the same despite there being a new object.
            // The removal arc here is special cased because the same element is triggered
            // twice in the event that it contains animations on the outer/inner portions
            // of the host container
            if (!isRemoval && fromState.value === toState.value) {
                // this means that despite the value not changing, some inner params
                // have changed which means that the animation final styles need to be applied
                if (!objEquals(fromState.params, toState.params)) {
                    var errors = [];
                    var fromStyles_1 = trigger.matchStyles(fromState.value, fromState.params, errors);
                    var toStyles_1 = trigger.matchStyles(toState.value, toState.params, errors);
                    if (errors.length) {
                        this._engine.reportError(errors);
                    }
                    else {
                        this._engine.afterFlush(function () {
                            eraseStyles(element, fromStyles_1);
                            setStyles(element, toStyles_1);
                        });
                    }
                }
                return;
            }
            var playersOnElement = getOrSetAsInMap(this._engine.playersByElement, element, []);
            playersOnElement.forEach(function (player) {
                // only remove the player if it is queued on the EXACT same trigger/namespace
                // we only also deal with queued players here because if the animation has
                // started then we want to keep the player alive until the flush happens
                // (which is where the previousPlayers are passed into the new palyer)
                if (player.namespaceId == _this.id && player.triggerName == triggerName && player.queued) {
                    player.destroy();
                }
            });
            var transition = trigger.matchTransition(fromState.value, toState.value, element, toState.params);
            var isFallbackTransition = false;
            if (!transition) {
                if (!defaultToFallback)
                    return;
                transition = trigger.fallbackTransition;
                isFallbackTransition = true;
            }
            this._engine.totalQueuedPlayers++;
            this._queue.push({ element: element, triggerName: triggerName, transition: transition, fromState: fromState, toState: toState, player: player, isFallbackTransition: isFallbackTransition });
            if (!isFallbackTransition) {
                addClass(element, QUEUED_CLASSNAME);
                player.onStart(function () {
                    removeClass(element, QUEUED_CLASSNAME);
                });
            }
            player.onDone(function () {
                var index = _this.players.indexOf(player);
                if (index >= 0) {
                    _this.players.splice(index, 1);
                }
                var players = _this._engine.playersByElement.get(element);
                if (players) {
                    var index_1 = players.indexOf(player);
                    if (index_1 >= 0) {
                        players.splice(index_1, 1);
                    }
                }
            });
            this.players.push(player);
            playersOnElement.push(player);
            return player;
        };
        AnimationTransitionNamespace.prototype.deregister = function (name) {
            var _this = this;
            delete this._triggers[name];
            this._engine.statesByElement.forEach(function (stateMap, element) {
                delete stateMap[name];
            });
            this._elementListeners.forEach(function (listeners, element) {
                _this._elementListeners.set(element, listeners.filter(function (entry) {
                    return entry.name != name;
                }));
            });
        };
        AnimationTransitionNamespace.prototype.clearElementCache = function (element) {
            this._engine.statesByElement.delete(element);
            this._elementListeners.delete(element);
            var elementPlayers = this._engine.playersByElement.get(element);
            if (elementPlayers) {
                elementPlayers.forEach(function (player) { return player.destroy(); });
                this._engine.playersByElement.delete(element);
            }
        };
        AnimationTransitionNamespace.prototype._signalRemovalForInnerTriggers = function (rootElement, context) {
            var _this = this;
            var elements = this._engine.driver.query(rootElement, NG_TRIGGER_SELECTOR, true);
            // emulate a leave animation for all inner nodes within this node.
            // If there are no animations found for any of the nodes then clear the cache
            // for the element.
            elements.forEach(function (elm) {
                // this means that an inner remove() operation has already kicked off
                // the animation on this element...
                if (elm[REMOVAL_FLAG])
                    return;
                var namespaces = _this._engine.fetchNamespacesByElement(elm);
                if (namespaces.size) {
                    namespaces.forEach(function (ns) { return ns.triggerLeaveAnimation(elm, context, false, true); });
                }
                else {
                    _this.clearElementCache(elm);
                }
            });
            // If the child elements were removed along with the parent, their animations might not
            // have completed. Clear all the elements from the cache so we don't end up with a memory leak.
            this._engine.afterFlushAnimationsDone(function () { return elements.forEach(function (elm) { return _this.clearElementCache(elm); }); });
        };
        AnimationTransitionNamespace.prototype.triggerLeaveAnimation = function (element, context, destroyAfterComplete, defaultToFallback) {
            var _this = this;
            var triggerStates = this._engine.statesByElement.get(element);
            if (triggerStates) {
                var players_1 = [];
                Object.keys(triggerStates).forEach(function (triggerName) {
                    // this check is here in the event that an element is removed
                    // twice (both on the host level and the component level)
                    if (_this._triggers[triggerName]) {
                        var player = _this.trigger(element, triggerName, VOID_VALUE, defaultToFallback);
                        if (player) {
                            players_1.push(player);
                        }
                    }
                });
                if (players_1.length) {
                    this._engine.markElementAsRemoved(this.id, element, true, context);
                    if (destroyAfterComplete) {
                        optimizeGroupPlayer(players_1).onDone(function () { return _this._engine.processLeaveNode(element); });
                    }
                    return true;
                }
            }
            return false;
        };
        AnimationTransitionNamespace.prototype.prepareLeaveAnimationListeners = function (element) {
            var _this = this;
            var listeners = this._elementListeners.get(element);
            var elementStates = this._engine.statesByElement.get(element);
            // if this statement fails then it means that the element was picked up
            // by an earlier flush (or there are no listeners at all to track the leave).
            if (listeners && elementStates) {
                var visitedTriggers_1 = new Set();
                listeners.forEach(function (listener) {
                    var triggerName = listener.name;
                    if (visitedTriggers_1.has(triggerName))
                        return;
                    visitedTriggers_1.add(triggerName);
                    var trigger = _this._triggers[triggerName];
                    var transition = trigger.fallbackTransition;
                    var fromState = elementStates[triggerName] || DEFAULT_STATE_VALUE;
                    var toState = new StateValue(VOID_VALUE);
                    var player = new TransitionAnimationPlayer(_this.id, triggerName, element);
                    _this._engine.totalQueuedPlayers++;
                    _this._queue.push({
                        element: element,
                        triggerName: triggerName,
                        transition: transition,
                        fromState: fromState,
                        toState: toState,
                        player: player,
                        isFallbackTransition: true
                    });
                });
            }
        };
        AnimationTransitionNamespace.prototype.removeNode = function (element, context) {
            var _this = this;
            var engine = this._engine;
            if (element.childElementCount) {
                this._signalRemovalForInnerTriggers(element, context);
            }
            // this means that a * => VOID animation was detected and kicked off
            if (this.triggerLeaveAnimation(element, context, true))
                return;
            // find the player that is animating and make sure that the
            // removal is delayed until that player has completed
            var containsPotentialParentTransition = false;
            if (engine.totalAnimations) {
                var currentPlayers = engine.players.length ? engine.playersByQueriedElement.get(element) : [];
                // when this `if statement` does not continue forward it means that
                // a previous animation query has selected the current element and
                // is animating it. In this situation want to continue forwards and
                // allow the element to be queued up for animation later.
                if (currentPlayers && currentPlayers.length) {
                    containsPotentialParentTransition = true;
                }
                else {
                    var parent = element;
                    while (parent = parent.parentNode) {
                        var triggers = engine.statesByElement.get(parent);
                        if (triggers) {
                            containsPotentialParentTransition = true;
                            break;
                        }
                    }
                }
            }
            // at this stage we know that the element will either get removed
            // during flush or will be picked up by a parent query. Either way
            // we need to fire the listeners for this element when it DOES get
            // removed (once the query parent animation is done or after flush)
            this.prepareLeaveAnimationListeners(element);
            // whether or not a parent has an animation we need to delay the deferral of the leave
            // operation until we have more information (which we do after flush() has been called)
            if (containsPotentialParentTransition) {
                engine.markElementAsRemoved(this.id, element, false, context);
            }
            else {
                var removalFlag = element[REMOVAL_FLAG];
                if (!removalFlag || removalFlag === NULL_REMOVAL_STATE) {
                    // we do this after the flush has occurred such
                    // that the callbacks can be fired
                    engine.afterFlush(function () { return _this.clearElementCache(element); });
                    engine.destroyInnerAnimations(element);
                    engine._onRemovalComplete(element, context);
                }
            }
        };
        AnimationTransitionNamespace.prototype.insertNode = function (element, parent) {
            addClass(element, this._hostClassName);
        };
        AnimationTransitionNamespace.prototype.drainQueuedTransitions = function (microtaskId) {
            var _this = this;
            var instructions = [];
            this._queue.forEach(function (entry) {
                var player = entry.player;
                if (player.destroyed)
                    return;
                var element = entry.element;
                var listeners = _this._elementListeners.get(element);
                if (listeners) {
                    listeners.forEach(function (listener) {
                        if (listener.name == entry.triggerName) {
                            var baseEvent = makeAnimationEvent(element, entry.triggerName, entry.fromState.value, entry.toState.value);
                            baseEvent['_data'] = microtaskId;
                            listenOnPlayer(entry.player, listener.phase, baseEvent, listener.callback);
                        }
                    });
                }
                if (player.markedForDestroy) {
                    _this._engine.afterFlush(function () {
                        // now we can destroy the element properly since the event listeners have
                        // been bound to the player
                        player.destroy();
                    });
                }
                else {
                    instructions.push(entry);
                }
            });
            this._queue = [];
            return instructions.sort(function (a, b) {
                // if depCount == 0 them move to front
                // otherwise if a contains b then move back
                var d0 = a.transition.ast.depCount;
                var d1 = b.transition.ast.depCount;
                if (d0 == 0 || d1 == 0) {
                    return d0 - d1;
                }
                return _this._engine.driver.containsElement(a.element, b.element) ? 1 : -1;
            });
        };
        AnimationTransitionNamespace.prototype.destroy = function (context) {
            this.players.forEach(function (p) { return p.destroy(); });
            this._signalRemovalForInnerTriggers(this.hostElement, context);
        };
        AnimationTransitionNamespace.prototype.elementContainsData = function (element) {
            var containsData = false;
            if (this._elementListeners.has(element))
                containsData = true;
            containsData =
                (this._queue.find(function (entry) { return entry.element === element; }) ? true : false) || containsData;
            return containsData;
        };
        return AnimationTransitionNamespace;
    }());
    var TransitionAnimationEngine = /** @class */ (function () {
        function TransitionAnimationEngine(bodyNode, driver, _normalizer) {
            this.bodyNode = bodyNode;
            this.driver = driver;
            this._normalizer = _normalizer;
            this.players = [];
            this.newHostElements = new Map();
            this.playersByElement = new Map();
            this.playersByQueriedElement = new Map();
            this.statesByElement = new Map();
            this.disabledNodes = new Set();
            this.totalAnimations = 0;
            this.totalQueuedPlayers = 0;
            this._namespaceLookup = {};
            this._namespaceList = [];
            this._flushFns = [];
            this._whenQuietFns = [];
            this.namespacesByHostElement = new Map();
            this.collectedEnterElements = [];
            this.collectedLeaveElements = [];
            // this method is designed to be overridden by the code that uses this engine
            this.onRemovalComplete = function (element, context) { };
        }
        /** @internal */
        TransitionAnimationEngine.prototype._onRemovalComplete = function (element, context) {
            this.onRemovalComplete(element, context);
        };
        Object.defineProperty(TransitionAnimationEngine.prototype, "queuedPlayers", {
            get: function () {
                var players = [];
                this._namespaceList.forEach(function (ns) {
                    ns.players.forEach(function (player) {
                        if (player.queued) {
                            players.push(player);
                        }
                    });
                });
                return players;
            },
            enumerable: false,
            configurable: true
        });
        TransitionAnimationEngine.prototype.createNamespace = function (namespaceId, hostElement) {
            var ns = new AnimationTransitionNamespace(namespaceId, hostElement, this);
            if (hostElement.parentNode) {
                this._balanceNamespaceList(ns, hostElement);
            }
            else {
                // defer this later until flush during when the host element has
                // been inserted so that we know exactly where to place it in
                // the namespace list
                this.newHostElements.set(hostElement, ns);
                // given that this host element is apart of the animation code, it
                // may or may not be inserted by a parent node that is an of an
                // animation renderer type. If this happens then we can still have
                // access to this item when we query for :enter nodes. If the parent
                // is a renderer then the set data-structure will normalize the entry
                this.collectEnterElement(hostElement);
            }
            return this._namespaceLookup[namespaceId] = ns;
        };
        TransitionAnimationEngine.prototype._balanceNamespaceList = function (ns, hostElement) {
            var limit = this._namespaceList.length - 1;
            if (limit >= 0) {
                var found = false;
                for (var i = limit; i >= 0; i--) {
                    var nextNamespace = this._namespaceList[i];
                    if (this.driver.containsElement(nextNamespace.hostElement, hostElement)) {
                        this._namespaceList.splice(i + 1, 0, ns);
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    this._namespaceList.splice(0, 0, ns);
                }
            }
            else {
                this._namespaceList.push(ns);
            }
            this.namespacesByHostElement.set(hostElement, ns);
            return ns;
        };
        TransitionAnimationEngine.prototype.register = function (namespaceId, hostElement) {
            var ns = this._namespaceLookup[namespaceId];
            if (!ns) {
                ns = this.createNamespace(namespaceId, hostElement);
            }
            return ns;
        };
        TransitionAnimationEngine.prototype.registerTrigger = function (namespaceId, name, trigger) {
            var ns = this._namespaceLookup[namespaceId];
            if (ns && ns.register(name, trigger)) {
                this.totalAnimations++;
            }
        };
        TransitionAnimationEngine.prototype.destroy = function (namespaceId, context) {
            var _this = this;
            if (!namespaceId)
                return;
            var ns = this._fetchNamespace(namespaceId);
            this.afterFlush(function () {
                _this.namespacesByHostElement.delete(ns.hostElement);
                delete _this._namespaceLookup[namespaceId];
                var index = _this._namespaceList.indexOf(ns);
                if (index >= 0) {
                    _this._namespaceList.splice(index, 1);
                }
            });
            this.afterFlushAnimationsDone(function () { return ns.destroy(context); });
        };
        TransitionAnimationEngine.prototype._fetchNamespace = function (id) {
            return this._namespaceLookup[id];
        };
        TransitionAnimationEngine.prototype.fetchNamespacesByElement = function (element) {
            // normally there should only be one namespace per element, however
            // if @triggers are placed on both the component element and then
            // its host element (within the component code) then there will be
            // two namespaces returned. We use a set here to simply the dedupe
            // of namespaces incase there are multiple triggers both the elm and host
            var namespaces = new Set();
            var elementStates = this.statesByElement.get(element);
            if (elementStates) {
                var keys = Object.keys(elementStates);
                for (var i = 0; i < keys.length; i++) {
                    var nsId = elementStates[keys[i]].namespaceId;
                    if (nsId) {
                        var ns = this._fetchNamespace(nsId);
                        if (ns) {
                            namespaces.add(ns);
                        }
                    }
                }
            }
            return namespaces;
        };
        TransitionAnimationEngine.prototype.trigger = function (namespaceId, element, name, value) {
            if (isElementNode(element)) {
                var ns = this._fetchNamespace(namespaceId);
                if (ns) {
                    ns.trigger(element, name, value);
                    return true;
                }
            }
            return false;
        };
        TransitionAnimationEngine.prototype.insertNode = function (namespaceId, element, parent, insertBefore) {
            if (!isElementNode(element))
                return;
            // special case for when an element is removed and reinserted (move operation)
            // when this occurs we do not want to use the element for deletion later
            var details = element[REMOVAL_FLAG];
            if (details && details.setForRemoval) {
                details.setForRemoval = false;
                details.setForMove = true;
                var index = this.collectedLeaveElements.indexOf(element);
                if (index >= 0) {
                    this.collectedLeaveElements.splice(index, 1);
                }
            }
            // in the event that the namespaceId is blank then the caller
            // code does not contain any animation code in it, but it is
            // just being called so that the node is marked as being inserted
            if (namespaceId) {
                var ns = this._fetchNamespace(namespaceId);
                // This if-statement is a workaround for router issue #21947.
                // The router sometimes hits a race condition where while a route
                // is being instantiated a new navigation arrives, triggering leave
                // animation of DOM that has not been fully initialized, until this
                // is resolved, we need to handle the scenario when DOM is not in a
                // consistent state during the animation.
                if (ns) {
                    ns.insertNode(element, parent);
                }
            }
            // only *directives and host elements are inserted before
            if (insertBefore) {
                this.collectEnterElement(element);
            }
        };
        TransitionAnimationEngine.prototype.collectEnterElement = function (element) {
            this.collectedEnterElements.push(element);
        };
        TransitionAnimationEngine.prototype.markElementAsDisabled = function (element, value) {
            if (value) {
                if (!this.disabledNodes.has(element)) {
                    this.disabledNodes.add(element);
                    addClass(element, DISABLED_CLASSNAME);
                }
            }
            else if (this.disabledNodes.has(element)) {
                this.disabledNodes.delete(element);
                removeClass(element, DISABLED_CLASSNAME);
            }
        };
        TransitionAnimationEngine.prototype.removeNode = function (namespaceId, element, isHostElement, context) {
            if (isElementNode(element)) {
                var ns = namespaceId ? this._fetchNamespace(namespaceId) : null;
                if (ns) {
                    ns.removeNode(element, context);
                }
                else {
                    this.markElementAsRemoved(namespaceId, element, false, context);
                }
                if (isHostElement) {
                    var hostNS = this.namespacesByHostElement.get(element);
                    if (hostNS && hostNS.id !== namespaceId) {
                        hostNS.removeNode(element, context);
                    }
                }
            }
            else {
                this._onRemovalComplete(element, context);
            }
        };
        TransitionAnimationEngine.prototype.markElementAsRemoved = function (namespaceId, element, hasAnimation, context) {
            this.collectedLeaveElements.push(element);
            element[REMOVAL_FLAG] =
                { namespaceId: namespaceId, setForRemoval: context, hasAnimation: hasAnimation, removedBeforeQueried: false };
        };
        TransitionAnimationEngine.prototype.listen = function (namespaceId, element, name, phase, callback) {
            if (isElementNode(element)) {
                return this._fetchNamespace(namespaceId).listen(element, name, phase, callback);
            }
            return function () { };
        };
        TransitionAnimationEngine.prototype._buildInstruction = function (entry, subTimelines, enterClassName, leaveClassName, skipBuildAst) {
            return entry.transition.build(this.driver, entry.element, entry.fromState.value, entry.toState.value, enterClassName, leaveClassName, entry.fromState.options, entry.toState.options, subTimelines, skipBuildAst);
        };
        TransitionAnimationEngine.prototype.destroyInnerAnimations = function (containerElement) {
            var _this = this;
            var elements = this.driver.query(containerElement, NG_TRIGGER_SELECTOR, true);
            elements.forEach(function (element) { return _this.destroyActiveAnimationsForElement(element); });
            if (this.playersByQueriedElement.size == 0)
                return;
            elements = this.driver.query(containerElement, NG_ANIMATING_SELECTOR, true);
            elements.forEach(function (element) { return _this.finishActiveQueriedAnimationOnElement(element); });
        };
        TransitionAnimationEngine.prototype.destroyActiveAnimationsForElement = function (element) {
            var players = this.playersByElement.get(element);
            if (players) {
                players.forEach(function (player) {
                    // special case for when an element is set for destruction, but hasn't started.
                    // in this situation we want to delay the destruction until the flush occurs
                    // so that any event listeners attached to the player are triggered.
                    if (player.queued) {
                        player.markedForDestroy = true;
                    }
                    else {
                        player.destroy();
                    }
                });
            }
        };
        TransitionAnimationEngine.prototype.finishActiveQueriedAnimationOnElement = function (element) {
            var players = this.playersByQueriedElement.get(element);
            if (players) {
                players.forEach(function (player) { return player.finish(); });
            }
        };
        TransitionAnimationEngine.prototype.whenRenderingDone = function () {
            var _this = this;
            return new Promise(function (resolve) {
                if (_this.players.length) {
                    return optimizeGroupPlayer(_this.players).onDone(function () { return resolve(); });
                }
                else {
                    resolve();
                }
            });
        };
        TransitionAnimationEngine.prototype.processLeaveNode = function (element) {
            var _this = this;
            var details = element[REMOVAL_FLAG];
            if (details && details.setForRemoval) {
                // this will prevent it from removing it twice
                element[REMOVAL_FLAG] = NULL_REMOVAL_STATE;
                if (details.namespaceId) {
                    this.destroyInnerAnimations(element);
                    var ns = this._fetchNamespace(details.namespaceId);
                    if (ns) {
                        ns.clearElementCache(element);
                    }
                }
                this._onRemovalComplete(element, details.setForRemoval);
            }
            if (this.driver.matchesElement(element, DISABLED_SELECTOR)) {
                this.markElementAsDisabled(element, false);
            }
            this.driver.query(element, DISABLED_SELECTOR, true).forEach(function (node) {
                _this.markElementAsDisabled(node, false);
            });
        };
        TransitionAnimationEngine.prototype.flush = function (microtaskId) {
            var _this = this;
            if (microtaskId === void 0) { microtaskId = -1; }
            var players = [];
            if (this.newHostElements.size) {
                this.newHostElements.forEach(function (ns, element) { return _this._balanceNamespaceList(ns, element); });
                this.newHostElements.clear();
            }
            if (this.totalAnimations && this.collectedEnterElements.length) {
                for (var i = 0; i < this.collectedEnterElements.length; i++) {
                    var elm = this.collectedEnterElements[i];
                    addClass(elm, STAR_CLASSNAME);
                }
            }
            if (this._namespaceList.length &&
                (this.totalQueuedPlayers || this.collectedLeaveElements.length)) {
                var cleanupFns = [];
                try {
                    players = this._flushAnimations(cleanupFns, microtaskId);
                }
                finally {
                    for (var i = 0; i < cleanupFns.length; i++) {
                        cleanupFns[i]();
                    }
                }
            }
            else {
                for (var i = 0; i < this.collectedLeaveElements.length; i++) {
                    var element = this.collectedLeaveElements[i];
                    this.processLeaveNode(element);
                }
            }
            this.totalQueuedPlayers = 0;
            this.collectedEnterElements.length = 0;
            this.collectedLeaveElements.length = 0;
            this._flushFns.forEach(function (fn) { return fn(); });
            this._flushFns = [];
            if (this._whenQuietFns.length) {
                // we move these over to a variable so that
                // if any new callbacks are registered in another
                // flush they do not populate the existing set
                var quietFns_1 = this._whenQuietFns;
                this._whenQuietFns = [];
                if (players.length) {
                    optimizeGroupPlayer(players).onDone(function () {
                        quietFns_1.forEach(function (fn) { return fn(); });
                    });
                }
                else {
                    quietFns_1.forEach(function (fn) { return fn(); });
                }
            }
        };
        TransitionAnimationEngine.prototype.reportError = function (errors) {
            throw new Error("Unable to process animations due to the following failed trigger transitions\n " + errors.join('\n'));
        };
        TransitionAnimationEngine.prototype._flushAnimations = function (cleanupFns, microtaskId) {
            var _this = this;
            var subTimelines = new ElementInstructionMap();
            var skippedPlayers = [];
            var skippedPlayersMap = new Map();
            var queuedInstructions = [];
            var queriedElements = new Map();
            var allPreStyleElements = new Map();
            var allPostStyleElements = new Map();
            var disabledElementsSet = new Set();
            this.disabledNodes.forEach(function (node) {
                disabledElementsSet.add(node);
                var nodesThatAreDisabled = _this.driver.query(node, QUEUED_SELECTOR, true);
                for (var i_1 = 0; i_1 < nodesThatAreDisabled.length; i_1++) {
                    disabledElementsSet.add(nodesThatAreDisabled[i_1]);
                }
            });
            var bodyNode = this.bodyNode;
            var allTriggerElements = Array.from(this.statesByElement.keys());
            var enterNodeMap = buildRootMap(allTriggerElements, this.collectedEnterElements);
            // this must occur before the instructions are built below such that
            // the :enter queries match the elements (since the timeline queries
            // are fired during instruction building).
            var enterNodeMapIds = new Map();
            var i = 0;
            enterNodeMap.forEach(function (nodes, root) {
                var className = ENTER_CLASSNAME + i++;
                enterNodeMapIds.set(root, className);
                nodes.forEach(function (node) { return addClass(node, className); });
            });
            var allLeaveNodes = [];
            var mergedLeaveNodes = new Set();
            var leaveNodesWithoutAnimations = new Set();
            for (var i_2 = 0; i_2 < this.collectedLeaveElements.length; i_2++) {
                var element = this.collectedLeaveElements[i_2];
                var details = element[REMOVAL_FLAG];
                if (details && details.setForRemoval) {
                    allLeaveNodes.push(element);
                    mergedLeaveNodes.add(element);
                    if (details.hasAnimation) {
                        this.driver.query(element, STAR_SELECTOR, true).forEach(function (elm) { return mergedLeaveNodes.add(elm); });
                    }
                    else {
                        leaveNodesWithoutAnimations.add(element);
                    }
                }
            }
            var leaveNodeMapIds = new Map();
            var leaveNodeMap = buildRootMap(allTriggerElements, Array.from(mergedLeaveNodes));
            leaveNodeMap.forEach(function (nodes, root) {
                var className = LEAVE_CLASSNAME + i++;
                leaveNodeMapIds.set(root, className);
                nodes.forEach(function (node) { return addClass(node, className); });
            });
            cleanupFns.push(function () {
                enterNodeMap.forEach(function (nodes, root) {
                    var className = enterNodeMapIds.get(root);
                    nodes.forEach(function (node) { return removeClass(node, className); });
                });
                leaveNodeMap.forEach(function (nodes, root) {
                    var className = leaveNodeMapIds.get(root);
                    nodes.forEach(function (node) { return removeClass(node, className); });
                });
                allLeaveNodes.forEach(function (element) {
                    _this.processLeaveNode(element);
                });
            });
            var allPlayers = [];
            var erroneousTransitions = [];
            for (var i_3 = this._namespaceList.length - 1; i_3 >= 0; i_3--) {
                var ns = this._namespaceList[i_3];
                ns.drainQueuedTransitions(microtaskId).forEach(function (entry) {
                    var player = entry.player;
                    var element = entry.element;
                    allPlayers.push(player);
                    if (_this.collectedEnterElements.length) {
                        var details = element[REMOVAL_FLAG];
                        // move animations are currently not supported...
                        if (details && details.setForMove) {
                            player.destroy();
                            return;
                        }
                    }
                    var nodeIsOrphaned = !bodyNode || !_this.driver.containsElement(bodyNode, element);
                    var leaveClassName = leaveNodeMapIds.get(element);
                    var enterClassName = enterNodeMapIds.get(element);
                    var instruction = _this._buildInstruction(entry, subTimelines, enterClassName, leaveClassName, nodeIsOrphaned);
                    if (instruction.errors && instruction.errors.length) {
                        erroneousTransitions.push(instruction);
                        return;
                    }
                    // even though the element may not be apart of the DOM, it may
                    // still be added at a later point (due to the mechanics of content
                    // projection and/or dynamic component insertion) therefore it's
                    // important we still style the element.
                    if (nodeIsOrphaned) {
                        player.onStart(function () { return eraseStyles(element, instruction.fromStyles); });
                        player.onDestroy(function () { return setStyles(element, instruction.toStyles); });
                        skippedPlayers.push(player);
                        return;
                    }
                    // if a unmatched transition is queued to go then it SHOULD NOT render
                    // an animation and cancel the previously running animations.
                    if (entry.isFallbackTransition) {
                        player.onStart(function () { return eraseStyles(element, instruction.fromStyles); });
                        player.onDestroy(function () { return setStyles(element, instruction.toStyles); });
                        skippedPlayers.push(player);
                        return;
                    }
                    // this means that if a parent animation uses this animation as a sub trigger
                    // then it will instruct the timeline builder to not add a player delay, but
                    // instead stretch the first keyframe gap up until the animation starts. The
                    // reason this is important is to prevent extra initialization styles from being
                    // required by the user in the animation.
                    instruction.timelines.forEach(function (tl) { return tl.stretchStartingKeyframe = true; });
                    subTimelines.append(element, instruction.timelines);
                    var tuple = { instruction: instruction, player: player, element: element };
                    queuedInstructions.push(tuple);
                    instruction.queriedElements.forEach(function (element) { return getOrSetAsInMap(queriedElements, element, []).push(player); });
                    instruction.preStyleProps.forEach(function (stringMap, element) {
                        var props = Object.keys(stringMap);
                        if (props.length) {
                            var setVal_1 = allPreStyleElements.get(element);
                            if (!setVal_1) {
                                allPreStyleElements.set(element, setVal_1 = new Set());
                            }
                            props.forEach(function (prop) { return setVal_1.add(prop); });
                        }
                    });
                    instruction.postStyleProps.forEach(function (stringMap, element) {
                        var props = Object.keys(stringMap);
                        var setVal = allPostStyleElements.get(element);
                        if (!setVal) {
                            allPostStyleElements.set(element, setVal = new Set());
                        }
                        props.forEach(function (prop) { return setVal.add(prop); });
                    });
                });
            }
            if (erroneousTransitions.length) {
                var errors_1 = [];
                erroneousTransitions.forEach(function (instruction) {
                    errors_1.push("@" + instruction.triggerName + " has failed due to:\n");
                    instruction.errors.forEach(function (error) { return errors_1.push("- " + error + "\n"); });
                });
                allPlayers.forEach(function (player) { return player.destroy(); });
                this.reportError(errors_1);
            }
            var allPreviousPlayersMap = new Map();
            // this map works to tell which element in the DOM tree is contained by
            // which animation. Further down below this map will get populated once
            // the players are built and in doing so it can efficiently figure out
            // if a sub player is skipped due to a parent player having priority.
            var animationElementMap = new Map();
            queuedInstructions.forEach(function (entry) {
                var element = entry.element;
                if (subTimelines.has(element)) {
                    animationElementMap.set(element, element);
                    _this._beforeAnimationBuild(entry.player.namespaceId, entry.instruction, allPreviousPlayersMap);
                }
            });
            skippedPlayers.forEach(function (player) {
                var element = player.element;
                var previousPlayers = _this._getPreviousPlayers(element, false, player.namespaceId, player.triggerName, null);
                previousPlayers.forEach(function (prevPlayer) {
                    getOrSetAsInMap(allPreviousPlayersMap, element, []).push(prevPlayer);
                    prevPlayer.destroy();
                });
            });
            // this is a special case for nodes that will be removed (either by)
            // having their own leave animations or by being queried in a container
            // that will be removed once a parent animation is complete. The idea
            // here is that * styles must be identical to ! styles because of
            // backwards compatibility (* is also filled in by default in many places).
            // Otherwise * styles will return an empty value or auto since the element
            // that is being getComputedStyle'd will not be visible (since * = destination)
            var replaceNodes = allLeaveNodes.filter(function (node) {
                return replacePostStylesAsPre(node, allPreStyleElements, allPostStyleElements);
            });
            // POST STAGE: fill the * styles
            var postStylesMap = new Map();
            var allLeaveQueriedNodes = cloakAndComputeStyles(postStylesMap, this.driver, leaveNodesWithoutAnimations, allPostStyleElements, animations.AUTO_STYLE);
            allLeaveQueriedNodes.forEach(function (node) {
                if (replacePostStylesAsPre(node, allPreStyleElements, allPostStyleElements)) {
                    replaceNodes.push(node);
                }
            });
            // PRE STAGE: fill the ! styles
            var preStylesMap = new Map();
            enterNodeMap.forEach(function (nodes, root) {
                cloakAndComputeStyles(preStylesMap, _this.driver, new Set(nodes), allPreStyleElements, animations.ɵPRE_STYLE);
            });
            replaceNodes.forEach(function (node) {
                var post = postStylesMap.get(node);
                var pre = preStylesMap.get(node);
                postStylesMap.set(node, Object.assign(Object.assign({}, post), pre));
            });
            var rootPlayers = [];
            var subPlayers = [];
            var NO_PARENT_ANIMATION_ELEMENT_DETECTED = {};
            queuedInstructions.forEach(function (entry) {
                var element = entry.element, player = entry.player, instruction = entry.instruction;
                // this means that it was never consumed by a parent animation which
                // means that it is independent and therefore should be set for animation
                if (subTimelines.has(element)) {
                    if (disabledElementsSet.has(element)) {
                        player.onDestroy(function () { return setStyles(element, instruction.toStyles); });
                        player.disabled = true;
                        player.overrideTotalTime(instruction.totalTime);
                        skippedPlayers.push(player);
                        return;
                    }
                    // this will flow up the DOM and query the map to figure out
                    // if a parent animation has priority over it. In the situation
                    // that a parent is detected then it will cancel the loop. If
                    // nothing is detected, or it takes a few hops to find a parent,
                    // then it will fill in the missing nodes and signal them as having
                    // a detected parent (or a NO_PARENT value via a special constant).
                    var parentWithAnimation_1 = NO_PARENT_ANIMATION_ELEMENT_DETECTED;
                    if (animationElementMap.size > 1) {
                        var elm = element;
                        var parentsToAdd = [];
                        while (elm = elm.parentNode) {
                            var detectedParent = animationElementMap.get(elm);
                            if (detectedParent) {
                                parentWithAnimation_1 = detectedParent;
                                break;
                            }
                            parentsToAdd.push(elm);
                        }
                        parentsToAdd.forEach(function (parent) { return animationElementMap.set(parent, parentWithAnimation_1); });
                    }
                    var innerPlayer = _this._buildAnimation(player.namespaceId, instruction, allPreviousPlayersMap, skippedPlayersMap, preStylesMap, postStylesMap);
                    player.setRealPlayer(innerPlayer);
                    if (parentWithAnimation_1 === NO_PARENT_ANIMATION_ELEMENT_DETECTED) {
                        rootPlayers.push(player);
                    }
                    else {
                        var parentPlayers = _this.playersByElement.get(parentWithAnimation_1);
                        if (parentPlayers && parentPlayers.length) {
                            player.parentPlayer = optimizeGroupPlayer(parentPlayers);
                        }
                        skippedPlayers.push(player);
                    }
                }
                else {
                    eraseStyles(element, instruction.fromStyles);
                    player.onDestroy(function () { return setStyles(element, instruction.toStyles); });
                    // there still might be a ancestor player animating this
                    // element therefore we will still add it as a sub player
                    // even if its animation may be disabled
                    subPlayers.push(player);
                    if (disabledElementsSet.has(element)) {
                        skippedPlayers.push(player);
                    }
                }
            });
            // find all of the sub players' corresponding inner animation player
            subPlayers.forEach(function (player) {
                // even if any players are not found for a sub animation then it
                // will still complete itself after the next tick since it's Noop
                var playersForElement = skippedPlayersMap.get(player.element);
                if (playersForElement && playersForElement.length) {
                    var innerPlayer = optimizeGroupPlayer(playersForElement);
                    player.setRealPlayer(innerPlayer);
                }
            });
            // the reason why we don't actually play the animation is
            // because all that a skipped player is designed to do is to
            // fire the start/done transition callback events
            skippedPlayers.forEach(function (player) {
                if (player.parentPlayer) {
                    player.syncPlayerEvents(player.parentPlayer);
                }
                else {
                    player.destroy();
                }
            });
            // run through all of the queued removals and see if they
            // were picked up by a query. If not then perform the removal
            // operation right away unless a parent animation is ongoing.
            for (var i_4 = 0; i_4 < allLeaveNodes.length; i_4++) {
                var element = allLeaveNodes[i_4];
                var details = element[REMOVAL_FLAG];
                removeClass(element, LEAVE_CLASSNAME);
                // this means the element has a removal animation that is being
                // taken care of and therefore the inner elements will hang around
                // until that animation is over (or the parent queried animation)
                if (details && details.hasAnimation)
                    continue;
                var players = [];
                // if this element is queried or if it contains queried children
                // then we want for the element not to be removed from the page
                // until the queried animations have finished
                if (queriedElements.size) {
                    var queriedPlayerResults = queriedElements.get(element);
                    if (queriedPlayerResults && queriedPlayerResults.length) {
                        players.push.apply(players, __spread(queriedPlayerResults));
                    }
                    var queriedInnerElements = this.driver.query(element, NG_ANIMATING_SELECTOR, true);
                    for (var j = 0; j < queriedInnerElements.length; j++) {
                        var queriedPlayers = queriedElements.get(queriedInnerElements[j]);
                        if (queriedPlayers && queriedPlayers.length) {
                            players.push.apply(players, __spread(queriedPlayers));
                        }
                    }
                }
                var activePlayers = players.filter(function (p) { return !p.destroyed; });
                if (activePlayers.length) {
                    removeNodesAfterAnimationDone(this, element, activePlayers);
                }
                else {
                    this.processLeaveNode(element);
                }
            }
            // this is required so the cleanup method doesn't remove them
            allLeaveNodes.length = 0;
            rootPlayers.forEach(function (player) {
                _this.players.push(player);
                player.onDone(function () {
                    player.destroy();
                    var index = _this.players.indexOf(player);
                    _this.players.splice(index, 1);
                });
                player.play();
            });
            return rootPlayers;
        };
        TransitionAnimationEngine.prototype.elementContainsData = function (namespaceId, element) {
            var containsData = false;
            var details = element[REMOVAL_FLAG];
            if (details && details.setForRemoval)
                containsData = true;
            if (this.playersByElement.has(element))
                containsData = true;
            if (this.playersByQueriedElement.has(element))
                containsData = true;
            if (this.statesByElement.has(element))
                containsData = true;
            return this._fetchNamespace(namespaceId).elementContainsData(element) || containsData;
        };
        TransitionAnimationEngine.prototype.afterFlush = function (callback) {
            this._flushFns.push(callback);
        };
        TransitionAnimationEngine.prototype.afterFlushAnimationsDone = function (callback) {
            this._whenQuietFns.push(callback);
        };
        TransitionAnimationEngine.prototype._getPreviousPlayers = function (element, isQueriedElement, namespaceId, triggerName, toStateValue) {
            var players = [];
            if (isQueriedElement) {
                var queriedElementPlayers = this.playersByQueriedElement.get(element);
                if (queriedElementPlayers) {
                    players = queriedElementPlayers;
                }
            }
            else {
                var elementPlayers = this.playersByElement.get(element);
                if (elementPlayers) {
                    var isRemovalAnimation_1 = !toStateValue || toStateValue == VOID_VALUE;
                    elementPlayers.forEach(function (player) {
                        if (player.queued)
                            return;
                        if (!isRemovalAnimation_1 && player.triggerName != triggerName)
                            return;
                        players.push(player);
                    });
                }
            }
            if (namespaceId || triggerName) {
                players = players.filter(function (player) {
                    if (namespaceId && namespaceId != player.namespaceId)
                        return false;
                    if (triggerName && triggerName != player.triggerName)
                        return false;
                    return true;
                });
            }
            return players;
        };
        TransitionAnimationEngine.prototype._beforeAnimationBuild = function (namespaceId, instruction, allPreviousPlayersMap) {
            var e_1, _a;
            var triggerName = instruction.triggerName;
            var rootElement = instruction.element;
            // when a removal animation occurs, ALL previous players are collected
            // and destroyed (even if they are outside of the current namespace)
            var targetNameSpaceId = instruction.isRemovalTransition ? undefined : namespaceId;
            var targetTriggerName = instruction.isRemovalTransition ? undefined : triggerName;
            var _loop_1 = function (timelineInstruction) {
                var element = timelineInstruction.element;
                var isQueriedElement = element !== rootElement;
                var players = getOrSetAsInMap(allPreviousPlayersMap, element, []);
                var previousPlayers = this_1._getPreviousPlayers(element, isQueriedElement, targetNameSpaceId, targetTriggerName, instruction.toState);
                previousPlayers.forEach(function (player) {
                    var realPlayer = player.getRealPlayer();
                    if (realPlayer.beforeDestroy) {
                        realPlayer.beforeDestroy();
                    }
                    player.destroy();
                    players.push(player);
                });
            };
            var this_1 = this;
            try {
                for (var _b = __values(instruction.timelines), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var timelineInstruction = _c.value;
                    _loop_1(timelineInstruction);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            // this needs to be done so that the PRE/POST styles can be
            // computed properly without interfering with the previous animation
            eraseStyles(rootElement, instruction.fromStyles);
        };
        TransitionAnimationEngine.prototype._buildAnimation = function (namespaceId, instruction, allPreviousPlayersMap, skippedPlayersMap, preStylesMap, postStylesMap) {
            var _this = this;
            var triggerName = instruction.triggerName;
            var rootElement = instruction.element;
            // we first run this so that the previous animation player
            // data can be passed into the successive animation players
            var allQueriedPlayers = [];
            var allConsumedElements = new Set();
            var allSubElements = new Set();
            var allNewPlayers = instruction.timelines.map(function (timelineInstruction) {
                var element = timelineInstruction.element;
                allConsumedElements.add(element);
                // FIXME (matsko): make sure to-be-removed animations are removed properly
                var details = element[REMOVAL_FLAG];
                if (details && details.removedBeforeQueried)
                    return new animations.NoopAnimationPlayer(timelineInstruction.duration, timelineInstruction.delay);
                var isQueriedElement = element !== rootElement;
                var previousPlayers = flattenGroupPlayers((allPreviousPlayersMap.get(element) || EMPTY_PLAYER_ARRAY)
                    .map(function (p) { return p.getRealPlayer(); }))
                    .filter(function (p) {
                    // the `element` is not apart of the AnimationPlayer definition, but
                    // Mock/WebAnimations
                    // use the element within their implementation. This will be added in Angular5 to
                    // AnimationPlayer
                    var pp = p;
                    return pp.element ? pp.element === element : false;
                });
                var preStyles = preStylesMap.get(element);
                var postStyles = postStylesMap.get(element);
                var keyframes = normalizeKeyframes(_this.driver, _this._normalizer, element, timelineInstruction.keyframes, preStyles, postStyles);
                var player = _this._buildPlayer(timelineInstruction, keyframes, previousPlayers);
                // this means that this particular player belongs to a sub trigger. It is
                // important that we match this player up with the corresponding (@trigger.listener)
                if (timelineInstruction.subTimeline && skippedPlayersMap) {
                    allSubElements.add(element);
                }
                if (isQueriedElement) {
                    var wrappedPlayer = new TransitionAnimationPlayer(namespaceId, triggerName, element);
                    wrappedPlayer.setRealPlayer(player);
                    allQueriedPlayers.push(wrappedPlayer);
                }
                return player;
            });
            allQueriedPlayers.forEach(function (player) {
                getOrSetAsInMap(_this.playersByQueriedElement, player.element, []).push(player);
                player.onDone(function () { return deleteOrUnsetInMap(_this.playersByQueriedElement, player.element, player); });
            });
            allConsumedElements.forEach(function (element) { return addClass(element, NG_ANIMATING_CLASSNAME); });
            var player = optimizeGroupPlayer(allNewPlayers);
            player.onDestroy(function () {
                allConsumedElements.forEach(function (element) { return removeClass(element, NG_ANIMATING_CLASSNAME); });
                setStyles(rootElement, instruction.toStyles);
            });
            // this basically makes all of the callbacks for sub element animations
            // be dependent on the upper players for when they finish
            allSubElements.forEach(function (element) {
                getOrSetAsInMap(skippedPlayersMap, element, []).push(player);
            });
            return player;
        };
        TransitionAnimationEngine.prototype._buildPlayer = function (instruction, keyframes, previousPlayers) {
            if (keyframes.length > 0) {
                return this.driver.animate(instruction.element, keyframes, instruction.duration, instruction.delay, instruction.easing, previousPlayers);
            }
            // special case for when an empty transition|definition is provided
            // ... there is no point in rendering an empty animation
            return new animations.NoopAnimationPlayer(instruction.duration, instruction.delay);
        };
        return TransitionAnimationEngine;
    }());
    var TransitionAnimationPlayer = /** @class */ (function () {
        function TransitionAnimationPlayer(namespaceId, triggerName, element) {
            this.namespaceId = namespaceId;
            this.triggerName = triggerName;
            this.element = element;
            this._player = new animations.NoopAnimationPlayer();
            this._containsRealPlayer = false;
            this._queuedCallbacks = {};
            this.destroyed = false;
            this.markedForDestroy = false;
            this.disabled = false;
            this.queued = true;
            this.totalTime = 0;
        }
        TransitionAnimationPlayer.prototype.setRealPlayer = function (player) {
            var _this = this;
            if (this._containsRealPlayer)
                return;
            this._player = player;
            Object.keys(this._queuedCallbacks).forEach(function (phase) {
                _this._queuedCallbacks[phase].forEach(function (callback) { return listenOnPlayer(player, phase, undefined, callback); });
            });
            this._queuedCallbacks = {};
            this._containsRealPlayer = true;
            this.overrideTotalTime(player.totalTime);
            this.queued = false;
        };
        TransitionAnimationPlayer.prototype.getRealPlayer = function () {
            return this._player;
        };
        TransitionAnimationPlayer.prototype.overrideTotalTime = function (totalTime) {
            this.totalTime = totalTime;
        };
        TransitionAnimationPlayer.prototype.syncPlayerEvents = function (player) {
            var _this = this;
            var p = this._player;
            if (p.triggerCallback) {
                player.onStart(function () { return p.triggerCallback('start'); });
            }
            player.onDone(function () { return _this.finish(); });
            player.onDestroy(function () { return _this.destroy(); });
        };
        TransitionAnimationPlayer.prototype._queueEvent = function (name, callback) {
            getOrSetAsInMap(this._queuedCallbacks, name, []).push(callback);
        };
        TransitionAnimationPlayer.prototype.onDone = function (fn) {
            if (this.queued) {
                this._queueEvent('done', fn);
            }
            this._player.onDone(fn);
        };
        TransitionAnimationPlayer.prototype.onStart = function (fn) {
            if (this.queued) {
                this._queueEvent('start', fn);
            }
            this._player.onStart(fn);
        };
        TransitionAnimationPlayer.prototype.onDestroy = function (fn) {
            if (this.queued) {
                this._queueEvent('destroy', fn);
            }
            this._player.onDestroy(fn);
        };
        TransitionAnimationPlayer.prototype.init = function () {
            this._player.init();
        };
        TransitionAnimationPlayer.prototype.hasStarted = function () {
            return this.queued ? false : this._player.hasStarted();
        };
        TransitionAnimationPlayer.prototype.play = function () {
            !this.queued && this._player.play();
        };
        TransitionAnimationPlayer.prototype.pause = function () {
            !this.queued && this._player.pause();
        };
        TransitionAnimationPlayer.prototype.restart = function () {
            !this.queued && this._player.restart();
        };
        TransitionAnimationPlayer.prototype.finish = function () {
            this._player.finish();
        };
        TransitionAnimationPlayer.prototype.destroy = function () {
            this.destroyed = true;
            this._player.destroy();
        };
        TransitionAnimationPlayer.prototype.reset = function () {
            !this.queued && this._player.reset();
        };
        TransitionAnimationPlayer.prototype.setPosition = function (p) {
            if (!this.queued) {
                this._player.setPosition(p);
            }
        };
        TransitionAnimationPlayer.prototype.getPosition = function () {
            return this.queued ? 0 : this._player.getPosition();
        };
        /** @internal */
        TransitionAnimationPlayer.prototype.triggerCallback = function (phaseName) {
            var p = this._player;
            if (p.triggerCallback) {
                p.triggerCallback(phaseName);
            }
        };
        return TransitionAnimationPlayer;
    }());
    function deleteOrUnsetInMap(map, key, value) {
        var currentValues;
        if (map instanceof Map) {
            currentValues = map.get(key);
            if (currentValues) {
                if (currentValues.length) {
                    var index = currentValues.indexOf(value);
                    currentValues.splice(index, 1);
                }
                if (currentValues.length == 0) {
                    map.delete(key);
                }
            }
        }
        else {
            currentValues = map[key];
            if (currentValues) {
                if (currentValues.length) {
                    var index = currentValues.indexOf(value);
                    currentValues.splice(index, 1);
                }
                if (currentValues.length == 0) {
                    delete map[key];
                }
            }
        }
        return currentValues;
    }
    function normalizeTriggerValue(value) {
        // we use `!= null` here because it's the most simple
        // way to test against a "falsy" value without mixing
        // in empty strings or a zero value. DO NOT OPTIMIZE.
        return value != null ? value : null;
    }
    function isElementNode(node) {
        return node && node['nodeType'] === 1;
    }
    function isTriggerEventValid(eventName) {
        return eventName == 'start' || eventName == 'done';
    }
    function cloakElement(element, value) {
        var oldValue = element.style.display;
        element.style.display = value != null ? value : 'none';
        return oldValue;
    }
    function cloakAndComputeStyles(valuesMap, driver, elements, elementPropsMap, defaultStyle) {
        var cloakVals = [];
        elements.forEach(function (element) { return cloakVals.push(cloakElement(element)); });
        var failedElements = [];
        elementPropsMap.forEach(function (props, element) {
            var styles = {};
            props.forEach(function (prop) {
                var value = styles[prop] = driver.computeStyle(element, prop, defaultStyle);
                // there is no easy way to detect this because a sub element could be removed
                // by a parent animation element being detached.
                if (!value || value.length == 0) {
                    element[REMOVAL_FLAG] = NULL_REMOVED_QUERIED_STATE;
                    failedElements.push(element);
                }
            });
            valuesMap.set(element, styles);
        });
        // we use a index variable here since Set.forEach(a, i) does not return
        // an index value for the closure (but instead just the value)
        var i = 0;
        elements.forEach(function (element) { return cloakElement(element, cloakVals[i++]); });
        return failedElements;
    }
    /*
    Since the Angular renderer code will return a collection of inserted
    nodes in all areas of a DOM tree, it's up to this algorithm to figure
    out which nodes are roots for each animation @trigger.

    By placing each inserted node into a Set and traversing upwards, it
    is possible to find the @trigger elements and well any direct *star
    insertion nodes, if a @trigger root is found then the enter element
    is placed into the Map[@trigger] spot.
     */
    function buildRootMap(roots, nodes) {
        var rootMap = new Map();
        roots.forEach(function (root) { return rootMap.set(root, []); });
        if (nodes.length == 0)
            return rootMap;
        var NULL_NODE = 1;
        var nodeSet = new Set(nodes);
        var localRootMap = new Map();
        function getRoot(node) {
            if (!node)
                return NULL_NODE;
            var root = localRootMap.get(node);
            if (root)
                return root;
            var parent = node.parentNode;
            if (rootMap.has(parent)) { // ngIf inside @trigger
                root = parent;
            }
            else if (nodeSet.has(parent)) { // ngIf inside ngIf
                root = NULL_NODE;
            }
            else { // recurse upwards
                root = getRoot(parent);
            }
            localRootMap.set(node, root);
            return root;
        }
        nodes.forEach(function (node) {
            var root = getRoot(node);
            if (root !== NULL_NODE) {
                rootMap.get(root).push(node);
            }
        });
        return rootMap;
    }
    var CLASSES_CACHE_KEY = '$$classes';
    function containsClass(element, className) {
        if (element.classList) {
            return element.classList.contains(className);
        }
        else {
            var classes = element[CLASSES_CACHE_KEY];
            return classes && classes[className];
        }
    }
    function addClass(element, className) {
        if (element.classList) {
            element.classList.add(className);
        }
        else {
            var classes = element[CLASSES_CACHE_KEY];
            if (!classes) {
                classes = element[CLASSES_CACHE_KEY] = {};
            }
            classes[className] = true;
        }
    }
    function removeClass(element, className) {
        if (element.classList) {
            element.classList.remove(className);
        }
        else {
            var classes = element[CLASSES_CACHE_KEY];
            if (classes) {
                delete classes[className];
            }
        }
    }
    function removeNodesAfterAnimationDone(engine, element, players) {
        optimizeGroupPlayer(players).onDone(function () { return engine.processLeaveNode(element); });
    }
    function flattenGroupPlayers(players) {
        var finalPlayers = [];
        _flattenGroupPlayersRecur(players, finalPlayers);
        return finalPlayers;
    }
    function _flattenGroupPlayersRecur(players, finalPlayers) {
        for (var i = 0; i < players.length; i++) {
            var player = players[i];
            if (player instanceof animations.ɵAnimationGroupPlayer) {
                _flattenGroupPlayersRecur(player.players, finalPlayers);
            }
            else {
                finalPlayers.push(player);
            }
        }
    }
    function objEquals(a, b) {
        var k1 = Object.keys(a);
        var k2 = Object.keys(b);
        if (k1.length != k2.length)
            return false;
        for (var i = 0; i < k1.length; i++) {
            var prop = k1[i];
            if (!b.hasOwnProperty(prop) || a[prop] !== b[prop])
                return false;
        }
        return true;
    }
    function replacePostStylesAsPre(element, allPreStyleElements, allPostStyleElements) {
        var postEntry = allPostStyleElements.get(element);
        if (!postEntry)
            return false;
        var preEntry = allPreStyleElements.get(element);
        if (preEntry) {
            postEntry.forEach(function (data) { return preEntry.add(data); });
        }
        else {
            allPreStyleElements.set(element, postEntry);
        }
        allPostStyleElements.delete(element);
        return true;
    }

    var AnimationEngine = /** @class */ (function () {
        function AnimationEngine(bodyNode, _driver, normalizer) {
            var _this = this;
            this.bodyNode = bodyNode;
            this._driver = _driver;
            this._triggerCache = {};
            // this method is designed to be overridden by the code that uses this engine
            this.onRemovalComplete = function (element, context) { };
            this._transitionEngine = new TransitionAnimationEngine(bodyNode, _driver, normalizer);
            this._timelineEngine = new TimelineAnimationEngine(bodyNode, _driver, normalizer);
            this._transitionEngine.onRemovalComplete = function (element, context) { return _this.onRemovalComplete(element, context); };
        }
        AnimationEngine.prototype.registerTrigger = function (componentId, namespaceId, hostElement, name, metadata) {
            var cacheKey = componentId + '-' + name;
            var trigger = this._triggerCache[cacheKey];
            if (!trigger) {
                var errors = [];
                var ast = buildAnimationAst(this._driver, metadata, errors);
                if (errors.length) {
                    throw new Error("The animation trigger \"" + name + "\" has failed to build due to the following errors:\n - " + errors.join('\n - '));
                }
                trigger = buildTrigger(name, ast);
                this._triggerCache[cacheKey] = trigger;
            }
            this._transitionEngine.registerTrigger(namespaceId, name, trigger);
        };
        AnimationEngine.prototype.register = function (namespaceId, hostElement) {
            this._transitionEngine.register(namespaceId, hostElement);
        };
        AnimationEngine.prototype.destroy = function (namespaceId, context) {
            this._transitionEngine.destroy(namespaceId, context);
        };
        AnimationEngine.prototype.onInsert = function (namespaceId, element, parent, insertBefore) {
            this._transitionEngine.insertNode(namespaceId, element, parent, insertBefore);
        };
        AnimationEngine.prototype.onRemove = function (namespaceId, element, context, isHostElement) {
            this._transitionEngine.removeNode(namespaceId, element, isHostElement || false, context);
        };
        AnimationEngine.prototype.disableAnimations = function (element, disable) {
            this._transitionEngine.markElementAsDisabled(element, disable);
        };
        AnimationEngine.prototype.process = function (namespaceId, element, property, value) {
            if (property.charAt(0) == '@') {
                var _a = __read(parseTimelineCommand(property), 2), id = _a[0], action = _a[1];
                var args = value;
                this._timelineEngine.command(id, element, action, args);
            }
            else {
                this._transitionEngine.trigger(namespaceId, element, property, value);
            }
        };
        AnimationEngine.prototype.listen = function (namespaceId, element, eventName, eventPhase, callback) {
            // @@listen
            if (eventName.charAt(0) == '@') {
                var _a = __read(parseTimelineCommand(eventName), 2), id = _a[0], action = _a[1];
                return this._timelineEngine.listen(id, element, action, callback);
            }
            return this._transitionEngine.listen(namespaceId, element, eventName, eventPhase, callback);
        };
        AnimationEngine.prototype.flush = function (microtaskId) {
            if (microtaskId === void 0) { microtaskId = -1; }
            this._transitionEngine.flush(microtaskId);
        };
        Object.defineProperty(AnimationEngine.prototype, "players", {
            get: function () {
                return this._transitionEngine.players
                    .concat(this._timelineEngine.players);
            },
            enumerable: false,
            configurable: true
        });
        AnimationEngine.prototype.whenRenderingDone = function () {
            return this._transitionEngine.whenRenderingDone();
        };
        return AnimationEngine;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Returns an instance of `SpecialCasedStyles` if and when any special (non animateable) styles are
     * detected.
     *
     * In CSS there exist properties that cannot be animated within a keyframe animation
     * (whether it be via CSS keyframes or web-animations) and the animation implementation
     * will ignore them. This function is designed to detect those special cased styles and
     * return a container that will be executed at the start and end of the animation.
     *
     * @returns an instance of `SpecialCasedStyles` if any special styles are detected otherwise `null`
     */
    function packageNonAnimatableStyles(element, styles) {
        var startStyles = null;
        var endStyles = null;
        if (Array.isArray(styles) && styles.length) {
            startStyles = filterNonAnimatableStyles(styles[0]);
            if (styles.length > 1) {
                endStyles = filterNonAnimatableStyles(styles[styles.length - 1]);
            }
        }
        else if (styles) {
            startStyles = filterNonAnimatableStyles(styles);
        }
        return (startStyles || endStyles) ? new SpecialCasedStyles(element, startStyles, endStyles) :
            null;
    }
    /**
     * Designed to be executed during a keyframe-based animation to apply any special-cased styles.
     *
     * When started (when the `start()` method is run) then the provided `startStyles`
     * will be applied. When finished (when the `finish()` method is called) the
     * `endStyles` will be applied as well any any starting styles. Finally when
     * `destroy()` is called then all styles will be removed.
     */
    var SpecialCasedStyles = /** @class */ (function () {
        function SpecialCasedStyles(_element, _startStyles, _endStyles) {
            this._element = _element;
            this._startStyles = _startStyles;
            this._endStyles = _endStyles;
            this._state = 0 /* Pending */;
            var initialStyles = SpecialCasedStyles.initialStylesByElement.get(_element);
            if (!initialStyles) {
                SpecialCasedStyles.initialStylesByElement.set(_element, initialStyles = {});
            }
            this._initialStyles = initialStyles;
        }
        SpecialCasedStyles.prototype.start = function () {
            if (this._state < 1 /* Started */) {
                if (this._startStyles) {
                    setStyles(this._element, this._startStyles, this._initialStyles);
                }
                this._state = 1 /* Started */;
            }
        };
        SpecialCasedStyles.prototype.finish = function () {
            this.start();
            if (this._state < 2 /* Finished */) {
                setStyles(this._element, this._initialStyles);
                if (this._endStyles) {
                    setStyles(this._element, this._endStyles);
                    this._endStyles = null;
                }
                this._state = 1 /* Started */;
            }
        };
        SpecialCasedStyles.prototype.destroy = function () {
            this.finish();
            if (this._state < 3 /* Destroyed */) {
                SpecialCasedStyles.initialStylesByElement.delete(this._element);
                if (this._startStyles) {
                    eraseStyles(this._element, this._startStyles);
                    this._endStyles = null;
                }
                if (this._endStyles) {
                    eraseStyles(this._element, this._endStyles);
                    this._endStyles = null;
                }
                setStyles(this._element, this._initialStyles);
                this._state = 3 /* Destroyed */;
            }
        };
        return SpecialCasedStyles;
    }());
    SpecialCasedStyles.initialStylesByElement = new WeakMap();
    function filterNonAnimatableStyles(styles) {
        var result = null;
        var props = Object.keys(styles);
        for (var i = 0; i < props.length; i++) {
            var prop = props[i];
            if (isNonAnimatableStyle(prop)) {
                result = result || {};
                result[prop] = styles[prop];
            }
        }
        return result;
    }
    function isNonAnimatableStyle(prop) {
        return prop === 'display' || prop === 'position';
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var ELAPSED_TIME_MAX_DECIMAL_PLACES = 3;
    var ANIMATION_PROP = 'animation';
    var ANIMATIONEND_EVENT = 'animationend';
    var ONE_SECOND$1 = 1000;
    var ElementAnimationStyleHandler = /** @class */ (function () {
        function ElementAnimationStyleHandler(_element, _name, _duration, _delay, _easing, _fillMode, _onDoneFn) {
            var _this = this;
            this._element = _element;
            this._name = _name;
            this._duration = _duration;
            this._delay = _delay;
            this._easing = _easing;
            this._fillMode = _fillMode;
            this._onDoneFn = _onDoneFn;
            this._finished = false;
            this._destroyed = false;
            this._startTime = 0;
            this._position = 0;
            this._eventFn = function (e) { return _this._handleCallback(e); };
        }
        ElementAnimationStyleHandler.prototype.apply = function () {
            applyKeyframeAnimation(this._element, this._duration + "ms " + this._easing + " " + this._delay + "ms 1 normal " + this._fillMode + " " + this._name);
            addRemoveAnimationEvent(this._element, this._eventFn, false);
            this._startTime = Date.now();
        };
        ElementAnimationStyleHandler.prototype.pause = function () {
            playPauseAnimation(this._element, this._name, 'paused');
        };
        ElementAnimationStyleHandler.prototype.resume = function () {
            playPauseAnimation(this._element, this._name, 'running');
        };
        ElementAnimationStyleHandler.prototype.setPosition = function (position) {
            var index = findIndexForAnimation(this._element, this._name);
            this._position = position * this._duration;
            setAnimationStyle(this._element, 'Delay', "-" + this._position + "ms", index);
        };
        ElementAnimationStyleHandler.prototype.getPosition = function () {
            return this._position;
        };
        ElementAnimationStyleHandler.prototype._handleCallback = function (event) {
            var timestamp = event._ngTestManualTimestamp || Date.now();
            var elapsedTime = parseFloat(event.elapsedTime.toFixed(ELAPSED_TIME_MAX_DECIMAL_PLACES)) * ONE_SECOND$1;
            if (event.animationName == this._name &&
                Math.max(timestamp - this._startTime, 0) >= this._delay && elapsedTime >= this._duration) {
                this.finish();
            }
        };
        ElementAnimationStyleHandler.prototype.finish = function () {
            if (this._finished)
                return;
            this._finished = true;
            this._onDoneFn();
            addRemoveAnimationEvent(this._element, this._eventFn, true);
        };
        ElementAnimationStyleHandler.prototype.destroy = function () {
            if (this._destroyed)
                return;
            this._destroyed = true;
            this.finish();
            removeKeyframeAnimation(this._element, this._name);
        };
        return ElementAnimationStyleHandler;
    }());
    function playPauseAnimation(element, name, status) {
        var index = findIndexForAnimation(element, name);
        setAnimationStyle(element, 'PlayState', status, index);
    }
    function applyKeyframeAnimation(element, value) {
        var anim = getAnimationStyle(element, '').trim();
        var index = 0;
        if (anim.length) {
            index = countChars(anim, ',') + 1;
            value = anim + ", " + value;
        }
        setAnimationStyle(element, '', value);
        return index;
    }
    function removeKeyframeAnimation(element, name) {
        var anim = getAnimationStyle(element, '');
        var tokens = anim.split(',');
        var index = findMatchingTokenIndex(tokens, name);
        if (index >= 0) {
            tokens.splice(index, 1);
            var newValue = tokens.join(',');
            setAnimationStyle(element, '', newValue);
        }
    }
    function findIndexForAnimation(element, value) {
        var anim = getAnimationStyle(element, '');
        if (anim.indexOf(',') > 0) {
            var tokens = anim.split(',');
            return findMatchingTokenIndex(tokens, value);
        }
        return findMatchingTokenIndex([anim], value);
    }
    function findMatchingTokenIndex(tokens, searchToken) {
        for (var i = 0; i < tokens.length; i++) {
            if (tokens[i].indexOf(searchToken) >= 0) {
                return i;
            }
        }
        return -1;
    }
    function addRemoveAnimationEvent(element, fn, doRemove) {
        doRemove ? element.removeEventListener(ANIMATIONEND_EVENT, fn) :
            element.addEventListener(ANIMATIONEND_EVENT, fn);
    }
    function setAnimationStyle(element, name, value, index) {
        var prop = ANIMATION_PROP + name;
        if (index != null) {
            var oldValue = element.style[prop];
            if (oldValue.length) {
                var tokens = oldValue.split(',');
                tokens[index] = value;
                value = tokens.join(',');
            }
        }
        element.style[prop] = value;
    }
    function getAnimationStyle(element, name) {
        return element.style[ANIMATION_PROP + name] || '';
    }
    function countChars(value, char) {
        var count = 0;
        for (var i = 0; i < value.length; i++) {
            var c = value.charAt(i);
            if (c === char)
                count++;
        }
        return count;
    }

    var DEFAULT_FILL_MODE = 'forwards';
    var DEFAULT_EASING = 'linear';
    var CssKeyframesPlayer = /** @class */ (function () {
        function CssKeyframesPlayer(element, keyframes, animationName, _duration, _delay, easing, _finalStyles, _specialStyles) {
            this.element = element;
            this.keyframes = keyframes;
            this.animationName = animationName;
            this._duration = _duration;
            this._delay = _delay;
            this._finalStyles = _finalStyles;
            this._specialStyles = _specialStyles;
            this._onDoneFns = [];
            this._onStartFns = [];
            this._onDestroyFns = [];
            this._started = false;
            this.currentSnapshot = {};
            this._state = 0;
            this.easing = easing || DEFAULT_EASING;
            this.totalTime = _duration + _delay;
            this._buildStyler();
        }
        CssKeyframesPlayer.prototype.onStart = function (fn) {
            this._onStartFns.push(fn);
        };
        CssKeyframesPlayer.prototype.onDone = function (fn) {
            this._onDoneFns.push(fn);
        };
        CssKeyframesPlayer.prototype.onDestroy = function (fn) {
            this._onDestroyFns.push(fn);
        };
        CssKeyframesPlayer.prototype.destroy = function () {
            this.init();
            if (this._state >= 4 /* DESTROYED */)
                return;
            this._state = 4 /* DESTROYED */;
            this._styler.destroy();
            this._flushStartFns();
            this._flushDoneFns();
            if (this._specialStyles) {
                this._specialStyles.destroy();
            }
            this._onDestroyFns.forEach(function (fn) { return fn(); });
            this._onDestroyFns = [];
        };
        CssKeyframesPlayer.prototype._flushDoneFns = function () {
            this._onDoneFns.forEach(function (fn) { return fn(); });
            this._onDoneFns = [];
        };
        CssKeyframesPlayer.prototype._flushStartFns = function () {
            this._onStartFns.forEach(function (fn) { return fn(); });
            this._onStartFns = [];
        };
        CssKeyframesPlayer.prototype.finish = function () {
            this.init();
            if (this._state >= 3 /* FINISHED */)
                return;
            this._state = 3 /* FINISHED */;
            this._styler.finish();
            this._flushStartFns();
            if (this._specialStyles) {
                this._specialStyles.finish();
            }
            this._flushDoneFns();
        };
        CssKeyframesPlayer.prototype.setPosition = function (value) {
            this._styler.setPosition(value);
        };
        CssKeyframesPlayer.prototype.getPosition = function () {
            return this._styler.getPosition();
        };
        CssKeyframesPlayer.prototype.hasStarted = function () {
            return this._state >= 2 /* STARTED */;
        };
        CssKeyframesPlayer.prototype.init = function () {
            if (this._state >= 1 /* INITIALIZED */)
                return;
            this._state = 1 /* INITIALIZED */;
            var elm = this.element;
            this._styler.apply();
            if (this._delay) {
                this._styler.pause();
            }
        };
        CssKeyframesPlayer.prototype.play = function () {
            this.init();
            if (!this.hasStarted()) {
                this._flushStartFns();
                this._state = 2 /* STARTED */;
                if (this._specialStyles) {
                    this._specialStyles.start();
                }
            }
            this._styler.resume();
        };
        CssKeyframesPlayer.prototype.pause = function () {
            this.init();
            this._styler.pause();
        };
        CssKeyframesPlayer.prototype.restart = function () {
            this.reset();
            this.play();
        };
        CssKeyframesPlayer.prototype.reset = function () {
            this._styler.destroy();
            this._buildStyler();
            this._styler.apply();
        };
        CssKeyframesPlayer.prototype._buildStyler = function () {
            var _this = this;
            this._styler = new ElementAnimationStyleHandler(this.element, this.animationName, this._duration, this._delay, this.easing, DEFAULT_FILL_MODE, function () { return _this.finish(); });
        };
        /** @internal */
        CssKeyframesPlayer.prototype.triggerCallback = function (phaseName) {
            var methods = phaseName == 'start' ? this._onStartFns : this._onDoneFns;
            methods.forEach(function (fn) { return fn(); });
            methods.length = 0;
        };
        CssKeyframesPlayer.prototype.beforeDestroy = function () {
            var _this = this;
            this.init();
            var styles = {};
            if (this.hasStarted()) {
                var finished_1 = this._state >= 3 /* FINISHED */;
                Object.keys(this._finalStyles).forEach(function (prop) {
                    if (prop != 'offset') {
                        styles[prop] = finished_1 ? _this._finalStyles[prop] : computeStyle(_this.element, prop);
                    }
                });
            }
            this.currentSnapshot = styles;
        };
        return CssKeyframesPlayer;
    }());

    var DirectStylePlayer = /** @class */ (function (_super) {
        __extends(DirectStylePlayer, _super);
        function DirectStylePlayer(element, styles) {
            var _this = _super.call(this) || this;
            _this.element = element;
            _this._startingStyles = {};
            _this.__initialized = false;
            _this._styles = hypenatePropsObject(styles);
            return _this;
        }
        DirectStylePlayer.prototype.init = function () {
            var _this = this;
            if (this.__initialized || !this._startingStyles)
                return;
            this.__initialized = true;
            Object.keys(this._styles).forEach(function (prop) {
                _this._startingStyles[prop] = _this.element.style[prop];
            });
            _super.prototype.init.call(this);
        };
        DirectStylePlayer.prototype.play = function () {
            var _this = this;
            if (!this._startingStyles)
                return;
            this.init();
            Object.keys(this._styles)
                .forEach(function (prop) { return _this.element.style.setProperty(prop, _this._styles[prop]); });
            _super.prototype.play.call(this);
        };
        DirectStylePlayer.prototype.destroy = function () {
            var _this = this;
            if (!this._startingStyles)
                return;
            Object.keys(this._startingStyles).forEach(function (prop) {
                var value = _this._startingStyles[prop];
                if (value) {
                    _this.element.style.setProperty(prop, value);
                }
                else {
                    _this.element.style.removeProperty(prop);
                }
            });
            this._startingStyles = null;
            _super.prototype.destroy.call(this);
        };
        return DirectStylePlayer;
    }(animations.NoopAnimationPlayer));

    var KEYFRAMES_NAME_PREFIX = 'gen_css_kf_';
    var TAB_SPACE = ' ';
    var CssKeyframesDriver = /** @class */ (function () {
        function CssKeyframesDriver() {
            this._count = 0;
            this._head = document.querySelector('head');
        }
        CssKeyframesDriver.prototype.validateStyleProperty = function (prop) {
            return validateStyleProperty(prop);
        };
        CssKeyframesDriver.prototype.matchesElement = function (element, selector) {
            return matchesElement(element, selector);
        };
        CssKeyframesDriver.prototype.containsElement = function (elm1, elm2) {
            return containsElement(elm1, elm2);
        };
        CssKeyframesDriver.prototype.query = function (element, selector, multi) {
            return invokeQuery(element, selector, multi);
        };
        CssKeyframesDriver.prototype.computeStyle = function (element, prop, defaultValue) {
            return window.getComputedStyle(element)[prop];
        };
        CssKeyframesDriver.prototype.buildKeyframeElement = function (element, name, keyframes) {
            keyframes = keyframes.map(function (kf) { return hypenatePropsObject(kf); });
            var keyframeStr = "@keyframes " + name + " {\n";
            var tab = '';
            keyframes.forEach(function (kf) {
                tab = TAB_SPACE;
                var offset = parseFloat(kf['offset']);
                keyframeStr += "" + tab + offset * 100 + "% {\n";
                tab += TAB_SPACE;
                Object.keys(kf).forEach(function (prop) {
                    var value = kf[prop];
                    switch (prop) {
                        case 'offset':
                            return;
                        case 'easing':
                            if (value) {
                                keyframeStr += tab + "animation-timing-function: " + value + ";\n";
                            }
                            return;
                        default:
                            keyframeStr += "" + tab + prop + ": " + value + ";\n";
                            return;
                    }
                });
                keyframeStr += tab + "}\n";
            });
            keyframeStr += "}\n";
            var kfElm = document.createElement('style');
            kfElm.textContent = keyframeStr;
            return kfElm;
        };
        CssKeyframesDriver.prototype.animate = function (element, keyframes, duration, delay, easing, previousPlayers, scrubberAccessRequested) {
            if (previousPlayers === void 0) { previousPlayers = []; }
            if ((typeof ngDevMode === 'undefined' || ngDevMode) && scrubberAccessRequested) {
                notifyFaultyScrubber();
            }
            var previousCssKeyframePlayers = previousPlayers.filter(function (player) { return player instanceof CssKeyframesPlayer; });
            var previousStyles = {};
            if (allowPreviousPlayerStylesMerge(duration, delay)) {
                previousCssKeyframePlayers.forEach(function (player) {
                    var styles = player.currentSnapshot;
                    Object.keys(styles).forEach(function (prop) { return previousStyles[prop] = styles[prop]; });
                });
            }
            keyframes = balancePreviousStylesIntoKeyframes(element, keyframes, previousStyles);
            var finalStyles = flattenKeyframesIntoStyles(keyframes);
            // if there is no animation then there is no point in applying
            // styles and waiting for an event to get fired. This causes lag.
            // It's better to just directly apply the styles to the element
            // via the direct styling animation player.
            if (duration == 0) {
                return new DirectStylePlayer(element, finalStyles);
            }
            var animationName = "" + KEYFRAMES_NAME_PREFIX + this._count++;
            var kfElm = this.buildKeyframeElement(element, animationName, keyframes);
            document.querySelector('head').appendChild(kfElm);
            var specialStyles = packageNonAnimatableStyles(element, keyframes);
            var player = new CssKeyframesPlayer(element, keyframes, animationName, duration, delay, easing, finalStyles, specialStyles);
            player.onDestroy(function () { return removeElement(kfElm); });
            return player;
        };
        return CssKeyframesDriver;
    }());
    function flattenKeyframesIntoStyles(keyframes) {
        var flatKeyframes = {};
        if (keyframes) {
            var kfs = Array.isArray(keyframes) ? keyframes : [keyframes];
            kfs.forEach(function (kf) {
                Object.keys(kf).forEach(function (prop) {
                    if (prop == 'offset' || prop == 'easing')
                        return;
                    flatKeyframes[prop] = kf[prop];
                });
            });
        }
        return flatKeyframes;
    }
    function removeElement(node) {
        node.parentNode.removeChild(node);
    }
    var warningIssued = false;
    function notifyFaultyScrubber() {
        if (warningIssued)
            return;
        console.warn('@angular/animations: please load the web-animations.js polyfill to allow programmatic access...\n', '  visit https://bit.ly/IWukam to learn more about using the web-animation-js polyfill.');
        warningIssued = true;
    }

    var WebAnimationsPlayer = /** @class */ (function () {
        function WebAnimationsPlayer(element, keyframes, options, _specialStyles) {
            this.element = element;
            this.keyframes = keyframes;
            this.options = options;
            this._specialStyles = _specialStyles;
            this._onDoneFns = [];
            this._onStartFns = [];
            this._onDestroyFns = [];
            this._initialized = false;
            this._finished = false;
            this._started = false;
            this._destroyed = false;
            this.time = 0;
            this.parentPlayer = null;
            this.currentSnapshot = {};
            this._duration = options['duration'];
            this._delay = options['delay'] || 0;
            this.time = this._duration + this._delay;
        }
        WebAnimationsPlayer.prototype._onFinish = function () {
            if (!this._finished) {
                this._finished = true;
                this._onDoneFns.forEach(function (fn) { return fn(); });
                this._onDoneFns = [];
            }
        };
        WebAnimationsPlayer.prototype.init = function () {
            this._buildPlayer();
            this._preparePlayerBeforeStart();
        };
        WebAnimationsPlayer.prototype._buildPlayer = function () {
            var _this = this;
            if (this._initialized)
                return;
            this._initialized = true;
            var keyframes = this.keyframes;
            this.domPlayer =
                this._triggerWebAnimation(this.element, keyframes, this.options);
            this._finalKeyframe = keyframes.length ? keyframes[keyframes.length - 1] : {};
            this.domPlayer.addEventListener('finish', function () { return _this._onFinish(); });
        };
        WebAnimationsPlayer.prototype._preparePlayerBeforeStart = function () {
            // this is required so that the player doesn't start to animate right away
            if (this._delay) {
                this._resetDomPlayerState();
            }
            else {
                this.domPlayer.pause();
            }
        };
        /** @internal */
        WebAnimationsPlayer.prototype._triggerWebAnimation = function (element, keyframes, options) {
            // jscompiler doesn't seem to know animate is a native property because it's not fully
            // supported yet across common browsers (we polyfill it for Edge/Safari) [CL #143630929]
            return element['animate'](keyframes, options);
        };
        WebAnimationsPlayer.prototype.onStart = function (fn) {
            this._onStartFns.push(fn);
        };
        WebAnimationsPlayer.prototype.onDone = function (fn) {
            this._onDoneFns.push(fn);
        };
        WebAnimationsPlayer.prototype.onDestroy = function (fn) {
            this._onDestroyFns.push(fn);
        };
        WebAnimationsPlayer.prototype.play = function () {
            this._buildPlayer();
            if (!this.hasStarted()) {
                this._onStartFns.forEach(function (fn) { return fn(); });
                this._onStartFns = [];
                this._started = true;
                if (this._specialStyles) {
                    this._specialStyles.start();
                }
            }
            this.domPlayer.play();
        };
        WebAnimationsPlayer.prototype.pause = function () {
            this.init();
            this.domPlayer.pause();
        };
        WebAnimationsPlayer.prototype.finish = function () {
            this.init();
            if (this._specialStyles) {
                this._specialStyles.finish();
            }
            this._onFinish();
            this.domPlayer.finish();
        };
        WebAnimationsPlayer.prototype.reset = function () {
            this._resetDomPlayerState();
            this._destroyed = false;
            this._finished = false;
            this._started = false;
        };
        WebAnimationsPlayer.prototype._resetDomPlayerState = function () {
            if (this.domPlayer) {
                this.domPlayer.cancel();
            }
        };
        WebAnimationsPlayer.prototype.restart = function () {
            this.reset();
            this.play();
        };
        WebAnimationsPlayer.prototype.hasStarted = function () {
            return this._started;
        };
        WebAnimationsPlayer.prototype.destroy = function () {
            if (!this._destroyed) {
                this._destroyed = true;
                this._resetDomPlayerState();
                this._onFinish();
                if (this._specialStyles) {
                    this._specialStyles.destroy();
                }
                this._onDestroyFns.forEach(function (fn) { return fn(); });
                this._onDestroyFns = [];
            }
        };
        WebAnimationsPlayer.prototype.setPosition = function (p) {
            if (this.domPlayer === undefined) {
                this.init();
            }
            this.domPlayer.currentTime = p * this.time;
        };
        WebAnimationsPlayer.prototype.getPosition = function () {
            return this.domPlayer.currentTime / this.time;
        };
        Object.defineProperty(WebAnimationsPlayer.prototype, "totalTime", {
            get: function () {
                return this._delay + this._duration;
            },
            enumerable: false,
            configurable: true
        });
        WebAnimationsPlayer.prototype.beforeDestroy = function () {
            var _this = this;
            var styles = {};
            if (this.hasStarted()) {
                Object.keys(this._finalKeyframe).forEach(function (prop) {
                    if (prop != 'offset') {
                        styles[prop] =
                            _this._finished ? _this._finalKeyframe[prop] : computeStyle(_this.element, prop);
                    }
                });
            }
            this.currentSnapshot = styles;
        };
        /** @internal */
        WebAnimationsPlayer.prototype.triggerCallback = function (phaseName) {
            var methods = phaseName == 'start' ? this._onStartFns : this._onDoneFns;
            methods.forEach(function (fn) { return fn(); });
            methods.length = 0;
        };
        return WebAnimationsPlayer;
    }());

    var WebAnimationsDriver = /** @class */ (function () {
        function WebAnimationsDriver() {
            this._isNativeImpl = /\{\s*\[native\s+code\]\s*\}/.test(getElementAnimateFn().toString());
            this._cssKeyframesDriver = new CssKeyframesDriver();
        }
        WebAnimationsDriver.prototype.validateStyleProperty = function (prop) {
            return validateStyleProperty(prop);
        };
        WebAnimationsDriver.prototype.matchesElement = function (element, selector) {
            return matchesElement(element, selector);
        };
        WebAnimationsDriver.prototype.containsElement = function (elm1, elm2) {
            return containsElement(elm1, elm2);
        };
        WebAnimationsDriver.prototype.query = function (element, selector, multi) {
            return invokeQuery(element, selector, multi);
        };
        WebAnimationsDriver.prototype.computeStyle = function (element, prop, defaultValue) {
            return window.getComputedStyle(element)[prop];
        };
        WebAnimationsDriver.prototype.overrideWebAnimationsSupport = function (supported) {
            this._isNativeImpl = supported;
        };
        WebAnimationsDriver.prototype.animate = function (element, keyframes, duration, delay, easing, previousPlayers, scrubberAccessRequested) {
            if (previousPlayers === void 0) { previousPlayers = []; }
            var useKeyframes = !scrubberAccessRequested && !this._isNativeImpl;
            if (useKeyframes) {
                return this._cssKeyframesDriver.animate(element, keyframes, duration, delay, easing, previousPlayers);
            }
            var fill = delay == 0 ? 'both' : 'forwards';
            var playerOptions = { duration: duration, delay: delay, fill: fill };
            // we check for this to avoid having a null|undefined value be present
            // for the easing (which results in an error for certain browsers #9752)
            if (easing) {
                playerOptions['easing'] = easing;
            }
            var previousStyles = {};
            var previousWebAnimationPlayers = previousPlayers.filter(function (player) { return player instanceof WebAnimationsPlayer; });
            if (allowPreviousPlayerStylesMerge(duration, delay)) {
                previousWebAnimationPlayers.forEach(function (player) {
                    var styles = player.currentSnapshot;
                    Object.keys(styles).forEach(function (prop) { return previousStyles[prop] = styles[prop]; });
                });
            }
            keyframes = keyframes.map(function (styles) { return copyStyles(styles, false); });
            keyframes = balancePreviousStylesIntoKeyframes(element, keyframes, previousStyles);
            var specialStyles = packageNonAnimatableStyles(element, keyframes);
            return new WebAnimationsPlayer(element, keyframes, playerOptions, specialStyles);
        };
        return WebAnimationsDriver;
    }());
    function supportsWebAnimations() {
        return typeof getElementAnimateFn() === 'function';
    }
    function getElementAnimateFn() {
        return (isBrowser() && Element.prototype['animate']) || {};
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.AnimationDriver = AnimationDriver;
    exports.ɵAnimation = Animation;
    exports.ɵAnimationEngine = AnimationEngine;
    exports.ɵAnimationStyleNormalizer = AnimationStyleNormalizer;
    exports.ɵCssKeyframesDriver = CssKeyframesDriver;
    exports.ɵCssKeyframesPlayer = CssKeyframesPlayer;
    exports.ɵNoopAnimationDriver = NoopAnimationDriver;
    exports.ɵNoopAnimationStyleNormalizer = NoopAnimationStyleNormalizer;
    exports.ɵWebAnimationsDriver = WebAnimationsDriver;
    exports.ɵWebAnimationsPlayer = WebAnimationsPlayer;
    exports.ɵWebAnimationsStyleNormalizer = WebAnimationsStyleNormalizer;
    exports.ɵallowPreviousPlayerStylesMerge = allowPreviousPlayerStylesMerge;
    exports.ɵangular_packages_animations_browser_browser_a = SpecialCasedStyles;
    exports.ɵcontainsElement = containsElement;
    exports.ɵinvokeQuery = invokeQuery;
    exports.ɵmatchesElement = matchesElement;
    exports.ɵsupportsWebAnimations = supportsWebAnimations;
    exports.ɵvalidateStyleProperty = validateStyleProperty;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=animations-browser.umd.js.map
