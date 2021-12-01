(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("vue"));
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CoreHome"] = factory(require("vue"));
	else
		root["CoreHome"] = factory(root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/CoreHome/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "2342":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
// see https://github.com/matomo-org/matomo/issues/5094 used to detect an ad blocker

window.hasBlockedContent = false;

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "createAngularJsAdapter", function() { return /* reexport */ createAngularJsAdapter; });
__webpack_require__.d(__webpack_exports__, "activityIndicatorAdapter", function() { return /* reexport */ ActivityIndicator_adapter; });
__webpack_require__.d(__webpack_exports__, "ActivityIndicator", function() { return /* reexport */ ActivityIndicator; });
__webpack_require__.d(__webpack_exports__, "translate", function() { return /* reexport */ translate; });
__webpack_require__.d(__webpack_exports__, "alertAdapter", function() { return /* reexport */ Alert_adapter; });
__webpack_require__.d(__webpack_exports__, "AjaxHelper", function() { return /* reexport */ AjaxHelper_AjaxHelper; });
__webpack_require__.d(__webpack_exports__, "setCookie", function() { return /* reexport */ setCookie; });
__webpack_require__.d(__webpack_exports__, "getCookie", function() { return /* reexport */ getCookie; });
__webpack_require__.d(__webpack_exports__, "deleteCookie", function() { return /* reexport */ deleteCookie; });
__webpack_require__.d(__webpack_exports__, "MatomoUrl", function() { return /* reexport */ src_MatomoUrl_MatomoUrl; });
__webpack_require__.d(__webpack_exports__, "Matomo", function() { return /* reexport */ Matomo_Matomo; });
__webpack_require__.d(__webpack_exports__, "Periods", function() { return /* reexport */ Periods_Periods; });
__webpack_require__.d(__webpack_exports__, "Day", function() { return /* reexport */ Day_DayPeriod; });
__webpack_require__.d(__webpack_exports__, "Week", function() { return /* reexport */ Week_WeekPeriod; });
__webpack_require__.d(__webpack_exports__, "Month", function() { return /* reexport */ Month_MonthPeriod; });
__webpack_require__.d(__webpack_exports__, "Year", function() { return /* reexport */ Year_YearPeriod; });
__webpack_require__.d(__webpack_exports__, "Range", function() { return /* reexport */ Range_RangePeriod; });
__webpack_require__.d(__webpack_exports__, "format", function() { return /* reexport */ format; });
__webpack_require__.d(__webpack_exports__, "getToday", function() { return /* reexport */ getToday; });
__webpack_require__.d(__webpack_exports__, "parseDate", function() { return /* reexport */ parseDate; });
__webpack_require__.d(__webpack_exports__, "todayIsInRange", function() { return /* reexport */ todayIsInRange; });
__webpack_require__.d(__webpack_exports__, "Dropdown", function() { return /* reexport */ DropdownMenu; });
__webpack_require__.d(__webpack_exports__, "FocusAnywhereButHere", function() { return /* reexport */ FocusAnywhereButHere; });
__webpack_require__.d(__webpack_exports__, "FocusIf", function() { return /* reexport */ FocusIf; });
__webpack_require__.d(__webpack_exports__, "MatomoDialog", function() { return /* reexport */ MatomoDialog; });
__webpack_require__.d(__webpack_exports__, "ExpandOnClick", function() { return /* reexport */ ExpandOnClick; });
__webpack_require__.d(__webpack_exports__, "ExpandOnHover", function() { return /* reexport */ ExpandOnHover; });
__webpack_require__.d(__webpack_exports__, "EnrichedHeadline", function() { return /* reexport */ EnrichedHeadline; });
__webpack_require__.d(__webpack_exports__, "ContentBlock", function() { return /* reexport */ ContentBlock; });
__webpack_require__.d(__webpack_exports__, "Comparisons", function() { return /* reexport */ Comparisons; });
__webpack_require__.d(__webpack_exports__, "Menudropdown", function() { return /* reexport */ Menudropdown; });
__webpack_require__.d(__webpack_exports__, "DatePicker", function() { return /* reexport */ DatePicker; });
__webpack_require__.d(__webpack_exports__, "DateRangePicker", function() { return /* reexport */ DateRangePicker; });
__webpack_require__.d(__webpack_exports__, "PeriodDatePicker", function() { return /* reexport */ PeriodDatePicker; });
__webpack_require__.d(__webpack_exports__, "Notification", function() { return /* reexport */ Notification; });
__webpack_require__.d(__webpack_exports__, "NotificationGroup", function() { return /* reexport */ Notification_NotificationGroup; });
__webpack_require__.d(__webpack_exports__, "NotificationsStore", function() { return /* reexport */ Notifications_store; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: ./plugins/CoreHome/vue/src/noAdblockFlag.ts
var noAdblockFlag = __webpack_require__("2342");

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Matomo period management service for the frontend.
 *
 * Usage:
 *
 *     var DayPeriod = matomoPeriods.get('day');
 *     var day = new DayPeriod(new Date());
 *
 * or
 *
 *     var day = matomoPeriods.parse('day', '2013-04-05');
 *
 * Adding custom periods:
 *
 * To add your own period to the frontend, create a period class for it
 * w/ the following methods:
 *
 * - **getPrettyString()**: returns a human readable display string for the period.
 * - **getDateRange()**: returns an array w/ two elements, the first being the start
 *                       Date of the period, the second being the end Date. The dates
 *                       must be Date objects, not strings, and are inclusive.
 * - **containsToday()**: returns true if the date period contains today. False if not.
 * - (_static_) **parse(strDate)**: creates a new instance of this period from the
 *                                  value of the 'date' query parameter.
 * - (_static_) **getDisplayText**: returns translated text for the period, eg, 'month',
 *                                  'week', etc.
 *
 * Then call Periods.addCustomPeriod w/ your period class:
 *
 *     Periods.addCustomPeriod('mycustomperiod', MyCustomPeriod);
 *
 * NOTE: currently only single date periods like day, week, month year can
 *       be extended. Other types of periods that require a special UI to
 *       view/edit aren't, since there is currently no way to use a
 *       custom UI for a custom period.
 */
var Periods = /*#__PURE__*/function () {
  function Periods() {
    _classCallCheck(this, Periods);

    _defineProperty(this, "periods", {});

    _defineProperty(this, "periodOrder", []);
  }

  _createClass(Periods, [{
    key: "addCustomPeriod",
    value: function addCustomPeriod(name, periodClass) {
      if (this.periods[name]) {
        throw new Error("The \"".concat(name, "\" period already exists! It cannot be overridden."));
      }

      this.periods[name] = periodClass;
      this.periodOrder.push(name);
    }
  }, {
    key: "getAllLabels",
    value: function getAllLabels() {
      return Array().concat(this.periodOrder);
    }
  }, {
    key: "get",
    value: function get(strPeriod) {
      var periodClass = this.periods[strPeriod];

      if (!periodClass) {
        throw new Error("Invalid period label: ".concat(strPeriod));
      }

      return periodClass;
    }
  }, {
    key: "parse",
    value: function parse(strPeriod, strDate) {
      return this.get(strPeriod).parse(strDate);
    }
  }, {
    key: "isRecognizedPeriod",
    value: function isRecognizedPeriod(strPeriod) {
      return !!this.periods[strPeriod];
    }
  }]);

  return Periods;
}();

/* harmony default export */ var Periods_Periods = (new Periods());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Matomo/Matomo.ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var originalTitle;
var _window = window,
    Matomo_piwik = _window.piwik,
    Matomo_broadcast = _window.broadcast,
    Matomo_piwikHelper = _window.piwikHelper;
Matomo_piwik.helper = Matomo_piwikHelper;
Matomo_piwik.broadcast = Matomo_broadcast;

Matomo_piwik.updateDateInTitle = function updateDateInTitle(date, period) {
  if (!$('.top_controls #periodString').length) {
    return;
  } // Cache server-rendered page title


  originalTitle = originalTitle || document.title;

  if (originalTitle.indexOf(Matomo_piwik.siteName) === 0) {
    var dateString = " - ".concat(Periods_Periods.parse(period, date).getPrettyString(), " ");
    document.title = "".concat(Matomo_piwik.siteName).concat(dateString).concat(originalTitle.substr(Matomo_piwik.siteName.length));
  }
};

Matomo_piwik.hasUserCapability = function hasUserCapability(capability) {
  return window.angular.isArray(Matomo_piwik.userCapabilities) && Matomo_piwik.userCapabilities.indexOf(capability) !== -1;
};

Matomo_piwik.on = function addMatomoEventListener(eventName, listener) {
  function listenerWrapper(evt) {
    listener.apply(void 0, _toConsumableArray(evt.detail)); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;
  window.addEventListener(eventName, listenerWrapper);
};

Matomo_piwik.off = function removeMatomoEventListener(eventName, listener) {
  if (listener.wrapper) {
    window.removeEventListener(eventName, listener.wrapper);
  }
};

Matomo_piwik.postEventNoEmit = function postEventNoEmit(eventName) {
  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }

  var event = new CustomEvent(eventName, {
    detail: args
  });
  window.dispatchEvent(event);
};

Matomo_piwik.postEvent = function postMatomoEvent(eventName) {
  for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
    args[_key2 - 1] = arguments[_key2];
  }

  Matomo_piwik.postEventNoEmit.apply(Matomo_piwik, [eventName].concat(args)); // required until angularjs is removed

  window.angular.element(function () {
    var $rootScope = Matomo_piwik.helper.getAngularDependency('$rootScope'); // eslint-disable-line

    $rootScope.$oldEmit.apply($rootScope, [eventName].concat(args));
  });
};

var Matomo = Matomo_piwik;
/* harmony default export */ var Matomo_Matomo = (Matomo);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/translate.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function translate(translationStringId) {
  for (var _len = arguments.length, values = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    values[_key - 1] = arguments[_key];
  }

  var pkArgs = values; // handle variadic args AND single array of values (to match _pk_translate signature)

  if (values.length === 1 && values[0] && values[0] instanceof Array) {
    pkArgs = values[0];
  }

  return window._pk_translate(translationStringId, pkArgs); // eslint-disable-line
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/utilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function format(date) {
  return $.datepicker.formatDate('yy-mm-dd', date);
}
function getToday() {
  var date = new Date(Date.now()); // undo browser timezone

  date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000); // apply Matomo site timezone (if it exists)

  date.setHours(date.getHours() + (window.piwik.timezoneOffset || 0) / 3600); // get rid of hours/minutes/seconds/etc.

  date.setHours(0);
  date.setMinutes(0);
  date.setSeconds(0);
  date.setMilliseconds(0);
  return date;
}
function parseDate(date) {
  if (date instanceof Date) {
    return date;
  }

  var strDate = decodeURIComponent(date).trim();

  if (strDate === '') {
    throw new Error('Invalid date, empty string.');
  }

  if (strDate === 'today' || strDate === 'now') {
    return getToday();
  }

  if (strDate === 'yesterday' // note: ignoring the 'same time' part since the frontend doesn't care about the time
  || strDate === 'yesterdaySameTime') {
    var yesterday = getToday();
    yesterday.setDate(yesterday.getDate() - 1);
    return yesterday;
  }

  if (strDate.match(/last[ -]?week/i)) {
    var lastWeek = getToday();
    lastWeek.setDate(lastWeek.getDate() - 7);
    return lastWeek;
  }

  if (strDate.match(/last[ -]?month/i)) {
    var lastMonth = getToday();
    lastMonth.setDate(1);
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    return lastMonth;
  }

  if (strDate.match(/last[ -]?year/i)) {
    var lastYear = getToday();
    lastYear.setFullYear(lastYear.getFullYear() - 1);
    return lastYear;
  }

  return $.datepicker.parseDate('yy-mm-dd', strDate);
}
function todayIsInRange(dateRange) {
  if (dateRange.length !== 2) {
    return false;
  }

  if (getToday() >= dateRange[0] && getToday() <= dateRange[1]) {
    return true;
  }

  return false;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Range.ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || Range_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function Range_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return Range_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return Range_arrayLikeToArray(o, minLen); }

function Range_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function Range_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Range_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Range_createClass(Constructor, protoProps, staticProps) { if (protoProps) Range_defineProperties(Constructor.prototype, protoProps); if (staticProps) Range_defineProperties(Constructor, staticProps); return Constructor; }

function Range_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var Range_RangePeriod = /*#__PURE__*/function () {
  function RangePeriod(startDate, endDate, childPeriodType) {
    Range_classCallCheck(this, RangePeriod);

    Range_defineProperty(this, "startDate", void 0);

    Range_defineProperty(this, "endDate", void 0);

    Range_defineProperty(this, "childPeriodType", void 0);

    this.startDate = startDate;
    this.endDate = endDate;
    this.childPeriodType = childPeriodType;
  }
  /**
   * Returns a range representing the last N childPeriodType periods, including the current one.
   */


  Range_createClass(RangePeriod, [{
    key: "getPrettyString",
    value: function getPrettyString() {
      var start = format(this.startDate);
      var end = format(this.endDate);
      return translate('General_DateRangeFromTo', [start, end]);
    }
  }, {
    key: "getDateRange",
    value: function getDateRange() {
      return [this.startDate, this.endDate];
    }
  }, {
    key: "containsToday",
    value: function containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }], [{
    key: "getLastNRange",
    value: function getLastNRange(childPeriodType, strAmount, strEndDate) {
      var nAmount = Math.max(parseInt(strAmount.toString(), 10) - 1, 0);

      if (Number.isNaN(nAmount)) {
        throw new Error('Invalid range strAmount');
      }

      var endDate = strEndDate ? parseDate(strEndDate) : getToday();
      var startDate = new Date(endDate.getTime());

      if (childPeriodType === 'day') {
        startDate.setDate(startDate.getDate() - nAmount);
      } else if (childPeriodType === 'week') {
        startDate.setDate(startDate.getDate() - nAmount * 7);
      } else if (childPeriodType === 'month') {
        startDate.setDate(1);
        startDate.setMonth(startDate.getMonth() - nAmount);
      } else if (childPeriodType === 'year') {
        startDate.setFullYear(startDate.getFullYear() - nAmount);
      } else {
        throw new Error("Unknown period type '".concat(childPeriodType, "'."));
      }

      if (childPeriodType !== 'day') {
        var startPeriod = Periods_Periods.periods[childPeriodType].parse(startDate);
        var endPeriod = Periods_Periods.periods[childPeriodType].parse(endDate);

        var _startPeriod$getDateR = startPeriod.getDateRange();

        var _startPeriod$getDateR2 = _slicedToArray(_startPeriod$getDateR, 1);

        startDate = _startPeriod$getDateR2[0];

        var _endPeriod$getDateRan = endPeriod.getDateRange();

        var _endPeriod$getDateRan2 = _slicedToArray(_endPeriod$getDateRan, 2);

        endDate = _endPeriod$getDateRan2[1];
      }

      var firstWebsiteDate = new Date(1991, 7, 6);

      if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
        switch (childPeriodType) {
          case 'year':
            startDate = new Date(1992, 0, 1);
            break;

          case 'month':
            startDate = new Date(1991, 8, 1);
            break;

          case 'week':
            startDate = new Date(1991, 8, 12);
            break;

          case 'day':
          default:
            startDate = firstWebsiteDate;
            break;
        }
      }

      return new RangePeriod(startDate, endDate, childPeriodType);
    }
    /**
     * Returns a range representing a specific child date range counted back from the end date
     *
     * @param childPeriodType Type of the period, eg. day, week, year
     * @param rangeEndDate
     * @param countBack Return only the child date range for this specific period number
     * @returns {RangePeriod}
     */

  }, {
    key: "getLastNRangeChild",
    value: function getLastNRangeChild(childPeriodType, rangeEndDate, countBack) {
      var ed = rangeEndDate ? parseDate(rangeEndDate) : getToday();
      var startDate = new Date(ed.getTime());
      var endDate = new Date(ed.getTime());

      if (childPeriodType === 'day') {
        startDate.setDate(startDate.getDate() - countBack);
        endDate.setDate(endDate.getDate() - countBack);
      } else if (childPeriodType === 'week') {
        startDate.setDate(startDate.getDate() - countBack * 7);
        endDate.setDate(endDate.getDate() - countBack * 7);
      } else if (childPeriodType === 'month') {
        startDate.setDate(1);
        startDate.setMonth(startDate.getMonth() - countBack);
        endDate.setDate(1);
        endDate.setMonth(endDate.getMonth() - countBack);
      } else if (childPeriodType === 'year') {
        startDate.setFullYear(startDate.getFullYear() - countBack);
        endDate.setFullYear(endDate.getFullYear() - countBack);
      } else {
        throw new Error("Unknown period type '".concat(childPeriodType, "'."));
      }

      if (childPeriodType !== 'day') {
        var startPeriod = Periods_Periods.periods[childPeriodType].parse(startDate);
        var endPeriod = Periods_Periods.periods[childPeriodType].parse(endDate);

        var _startPeriod$getDateR3 = startPeriod.getDateRange();

        var _startPeriod$getDateR4 = _slicedToArray(_startPeriod$getDateR3, 1);

        startDate = _startPeriod$getDateR4[0];

        var _endPeriod$getDateRan3 = endPeriod.getDateRange();

        var _endPeriod$getDateRan4 = _slicedToArray(_endPeriod$getDateRan3, 2);

        endDate = _endPeriod$getDateRan4[1];
      }

      var firstWebsiteDate = new Date(1991, 7, 6);

      if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
        switch (childPeriodType) {
          case 'year':
            startDate = new Date(1992, 0, 1);
            break;

          case 'month':
            startDate = new Date(1991, 8, 1);
            break;

          case 'week':
            startDate = new Date(1991, 8, 12);
            break;

          case 'day':
          default:
            startDate = firstWebsiteDate;
            break;
        }
      }

      return new RangePeriod(startDate, endDate, childPeriodType);
    }
  }, {
    key: "parse",
    value: function parse(strDate) {
      var childPeriodType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'day';

      if (/^previous/.test(strDate)) {
        var endDate = RangePeriod.getLastNRange(childPeriodType, '2').startDate;
        return RangePeriod.getLastNRange(childPeriodType, strDate.substring(8), endDate);
      }

      if (/^last/.test(strDate)) {
        return RangePeriod.getLastNRange(childPeriodType, strDate.substring(4));
      }

      var parts = decodeURIComponent(strDate).split(',');
      return new RangePeriod(parseDate(parts[0]), parseDate(parts[1]), childPeriodType);
    }
  }, {
    key: "getDisplayText",
    value: function getDisplayText() {
      return translate('General_DateRangeInPeriodList');
    }
  }]);

  return RangePeriod;
}();


Periods_Periods.addCustomPeriod('range', Range_RangePeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



window.piwik.addCustomPeriod = Periods_Periods.addCustomPeriod.bind(Periods_Periods);

function piwikPeriods() {
  return {
    getAllLabels: Periods_Periods.getAllLabels.bind(Periods_Periods),
    isRecognizedPeriod: Periods_Periods.isRecognizedPeriod.bind(Periods_Periods),
    get: Periods_Periods.get.bind(Periods_Periods),
    parse: Periods_Periods.parse.bind(Periods_Periods),
    parseDate: parseDate,
    format: format,
    RangePeriod: Range_RangePeriod,
    todayIsInRange: todayIsInRange
  };
}

window.angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Day.ts
function Day_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Day_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Day_createClass(Constructor, protoProps, staticProps) { if (protoProps) Day_defineProperties(Constructor.prototype, protoProps); if (staticProps) Day_defineProperties(Constructor, staticProps); return Constructor; }

function Day_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var Day_DayPeriod = /*#__PURE__*/function () {
  function DayPeriod(dateInPeriod) {
    Day_classCallCheck(this, DayPeriod);

    Day_defineProperty(this, "dateInPeriod", void 0);

    this.dateInPeriod = dateInPeriod;
  }

  Day_createClass(DayPeriod, [{
    key: "getPrettyString",
    value: function getPrettyString() {
      return format(this.dateInPeriod);
    }
  }, {
    key: "getDateRange",
    value: function getDateRange() {
      return [new Date(this.dateInPeriod.getTime()), new Date(this.dateInPeriod.getTime())];
    }
  }, {
    key: "containsToday",
    value: function containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }], [{
    key: "parse",
    value: function parse(strDate) {
      return new DayPeriod(parseDate(strDate));
    }
  }, {
    key: "getDisplayText",
    value: function getDisplayText() {
      return translate('Intl_PeriodDay');
    }
  }]);

  return DayPeriod;
}();


Periods_Periods.addCustomPeriod('day', Day_DayPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Week.ts
function Week_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Week_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Week_createClass(Constructor, protoProps, staticProps) { if (protoProps) Week_defineProperties(Constructor.prototype, protoProps); if (staticProps) Week_defineProperties(Constructor, staticProps); return Constructor; }

function Week_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var Week_WeekPeriod = /*#__PURE__*/function () {
  function WeekPeriod(dateInPeriod) {
    Week_classCallCheck(this, WeekPeriod);

    Week_defineProperty(this, "dateInPeriod", void 0);

    this.dateInPeriod = dateInPeriod;
  }

  Week_createClass(WeekPeriod, [{
    key: "getPrettyString",
    value: function getPrettyString() {
      var weekDates = this.getDateRange();
      var startWeek = format(weekDates[0]);
      var endWeek = format(weekDates[1]);
      return translate('General_DateRangeFromTo', [startWeek, endWeek]);
    }
  }, {
    key: "getDateRange",
    value: function getDateRange() {
      var daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;
      var startWeek = new Date(this.dateInPeriod.getTime());
      startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);
      var endWeek = new Date(startWeek.getTime());
      endWeek.setDate(startWeek.getDate() + 6);
      return [startWeek, endWeek];
    }
  }, {
    key: "containsToday",
    value: function containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }], [{
    key: "parse",
    value: function parse(strDate) {
      return new WeekPeriod(parseDate(strDate));
    }
  }, {
    key: "getDisplayText",
    value: function getDisplayText() {
      return translate('Intl_PeriodWeek');
    }
  }]);

  return WeekPeriod;
}();


Periods_Periods.addCustomPeriod('week', Week_WeekPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Month.ts
function Month_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Month_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Month_createClass(Constructor, protoProps, staticProps) { if (protoProps) Month_defineProperties(Constructor.prototype, protoProps); if (staticProps) Month_defineProperties(Constructor, staticProps); return Constructor; }

function Month_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var Month_MonthPeriod = /*#__PURE__*/function () {
  function MonthPeriod(dateInPeriod) {
    Month_classCallCheck(this, MonthPeriod);

    Month_defineProperty(this, "dateInPeriod", void 0);

    this.dateInPeriod = dateInPeriod;
  }

  Month_createClass(MonthPeriod, [{
    key: "getPrettyString",
    value: function getPrettyString() {
      var month = translate("Intl_Month_Long_StandAlone_".concat(this.dateInPeriod.getMonth() + 1));
      return "".concat(month, " ").concat(this.dateInPeriod.getFullYear());
    }
  }, {
    key: "getDateRange",
    value: function getDateRange() {
      var startMonth = new Date(this.dateInPeriod.getTime());
      startMonth.setDate(1);
      var endMonth = new Date(this.dateInPeriod.getTime());
      endMonth.setDate(1);
      endMonth.setMonth(endMonth.getMonth() + 1);
      endMonth.setDate(0);
      return [startMonth, endMonth];
    }
  }, {
    key: "containsToday",
    value: function containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }], [{
    key: "parse",
    value: function parse(strDate) {
      return new MonthPeriod(parseDate(strDate));
    }
  }, {
    key: "getDisplayText",
    value: function getDisplayText() {
      return translate('Intl_PeriodMonth');
    }
  }]);

  return MonthPeriod;
}();


Periods_Periods.addCustomPeriod('month', Month_MonthPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Year.ts
function Year_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Year_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Year_createClass(Constructor, protoProps, staticProps) { if (protoProps) Year_defineProperties(Constructor.prototype, protoProps); if (staticProps) Year_defineProperties(Constructor, staticProps); return Constructor; }

function Year_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var Year_YearPeriod = /*#__PURE__*/function () {
  function YearPeriod(dateInPeriod) {
    Year_classCallCheck(this, YearPeriod);

    Year_defineProperty(this, "dateInPeriod", void 0);

    this.dateInPeriod = dateInPeriod;
  }

  Year_createClass(YearPeriod, [{
    key: "getPrettyString",
    value: function getPrettyString() {
      return this.dateInPeriod.getFullYear().toString();
    }
  }, {
    key: "getDateRange",
    value: function getDateRange() {
      var startYear = new Date(this.dateInPeriod.getTime());
      startYear.setMonth(0);
      startYear.setDate(1);
      var endYear = new Date(this.dateInPeriod.getTime());
      endYear.setMonth(12);
      endYear.setDate(0);
      return [startYear, endYear];
    }
  }, {
    key: "containsToday",
    value: function containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }], [{
    key: "parse",
    value: function parse(strDate) {
      return new YearPeriod(parseDate(strDate));
    }
  }, {
    key: "getDisplayText",
    value: function getDisplayText() {
      return translate('Intl_PeriodYear');
    }
  }]);

  return YearPeriod;
}();


Periods_Periods.addCustomPeriod('year', Year_YearPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */








// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoUrl/MatomoUrl.ts
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { MatomoUrl_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function MatomoUrl_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function MatomoUrl_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function MatomoUrl_createClass(Constructor, protoProps, staticProps) { if (protoProps) MatomoUrl_defineProperties(Constructor.prototype, protoProps); if (staticProps) MatomoUrl_defineProperties(Constructor, staticProps); return Constructor; }

function MatomoUrl_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


 // important to load all periods here

var MatomoUrl_window = window,
    MatomoUrl_piwik = MatomoUrl_window.piwik,
    MatomoUrl_broadcast = MatomoUrl_window.broadcast;

function isValidPeriod(periodStr, dateStr) {
  try {
    Periods_Periods.parse(periodStr, dateStr);
    return true;
  } catch (e) {
    return false;
  }
}
/**
 * URL store and helper functions.
 */


var MatomoUrl_MatomoUrl = /*#__PURE__*/function () {
  function MatomoUrl() {
    var _this = this;

    MatomoUrl_classCallCheck(this, MatomoUrl);

    MatomoUrl_defineProperty(this, "urlQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(''));

    MatomoUrl_defineProperty(this, "hashQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(''));

    MatomoUrl_defineProperty(this, "urlParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(MatomoUrl_broadcast.getValuesFromUrl("?".concat(_this.urlQuery.value), true));
    }));

    MatomoUrl_defineProperty(this, "hashParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(MatomoUrl_broadcast.getValuesFromUrl("?".concat(_this.hashQuery.value), true));
    }));

    MatomoUrl_defineProperty(this, "parsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_objectSpread(_objectSpread({}, _this.urlParsed.value), _this.hashParsed.value));
    }));

    this.setUrlQuery(window.location.search);
    this.setHashQuery(window.location.hash); // $locationChangeSuccess is triggered before angularjs changes actual window the hash, so we
    // have to hook into this method if we want our event handlers to execute before other angularjs
    // handlers (like the reporting page one)

    Matomo_Matomo.on('$locationChangeSuccess', function (absUrl) {
      var url = new URL(absUrl);

      _this.setUrlQuery(url.search.replace(/^\?/, ''));

      _this.setHashQuery(url.hash.replace(/^#/, ''));
    });
    this.updatePeriodParamsFromUrl();
  }

  MatomoUrl_createClass(MatomoUrl, [{
    key: "updateHash",
    value: function updateHash(params) {
      var serializedParams = typeof params !== 'string' ? this.stringify(params) : params;
      var $location = Matomo_Matomo.helper.getAngularDependency('$location');
      $location.search(serializedParams);
    }
  }, {
    key: "getSearchParam",
    value: function getSearchParam(paramName) {
      var hash = window.location.href.split('#');
      var regex = new RegExp("".concat(paramName, "(\\[]|=)"));

      if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {
        var valueFromHash = window.broadcast.getValueFromHash(paramName, window.location.href); // for date, period and idsite fall back to parameter from url, if non in hash was provided

        if (valueFromHash || paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite') {
          return valueFromHash;
        }
      }

      return window.broadcast.getValueFromUrl(paramName, window.location.search);
    }
  }, {
    key: "stringify",
    value: function stringify(search) {
      // TODO: using $ since URLSearchParams does not handle array params the way Matomo uses them
      return $.param(search).replace(/%5B%5D/g, '[]');
    }
  }, {
    key: "updatePeriodParamsFromUrl",
    value: function updatePeriodParamsFromUrl() {
      var date = this.getSearchParam('date');
      var period = this.getSearchParam('period');

      if (!isValidPeriod(period, date)) {
        // invalid data in URL
        return;
      }

      if (MatomoUrl_piwik.period === period && MatomoUrl_piwik.currentDateString === date) {
        // this period / date is already loaded
        return;
      }

      MatomoUrl_piwik.period = period;
      var dateRange = Periods_Periods.parse(period, date).getDateRange();
      MatomoUrl_piwik.startDateString = format(dateRange[0]);
      MatomoUrl_piwik.endDateString = format(dateRange[1]);
      MatomoUrl_piwik.updateDateInTitle(date, period); // do not set anything to previousN/lastN, as it's more useful to plugins
      // to have the dates than previousN/lastN.

      if (MatomoUrl_piwik.period === 'range') {
        date = "".concat(MatomoUrl_piwik.startDateString, ",").concat(MatomoUrl_piwik.endDateString);
      }

      MatomoUrl_piwik.currentDateString = date;
    }
  }, {
    key: "setUrlQuery",
    value: function setUrlQuery(search) {
      this.urlQuery.value = search.replace(/^\?/, '');
    }
  }, {
    key: "setHashQuery",
    value: function setHashQuery(hash) {
      this.hashQuery.value = hash.replace(/^[#/?]+/, '');
    }
  }]);

  return MatomoUrl;
}();

var instance = new MatomoUrl_MatomoUrl();
/* harmony default export */ var src_MatomoUrl_MatomoUrl = (instance);
MatomoUrl_piwik.updatePeriodParamsFromUrl = instance.updatePeriodParamsFromUrl.bind(instance);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoUrl/MatomoUrl.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikUrl() {
  var model = {
    getSearchParam: src_MatomoUrl_MatomoUrl.getSearchParam.bind(src_MatomoUrl_MatomoUrl)
  };
  return model;
}

piwikUrl.$inject = [];
angular.module('piwikApp.service').service('piwikUrl', piwikUrl);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Matomo/Matomo.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikService() {
  return Matomo_Matomo;
}

window.angular.module('piwikApp.service').service('piwik', piwikService);

function initPiwikService(piwik, $rootScope) {
  // overwrite $rootScope so all events also go through Matomo.postEvent(...) too.
  $rootScope.$oldEmit = $rootScope.$emit; // eslint-disable-line

  $rootScope.$emit = function emitWrapper(name) {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    Matomo_Matomo.postEventNoEmit.apply(Matomo_Matomo, [name].concat(args));
    return this.$oldEmit.apply(this, [name].concat(args));
  };

  $rootScope.$oldBroadcast = $rootScope.$broadcast; // eslint-disable-line

  $rootScope.$broadcast = function broadcastWrapper(name) {
    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      args[_key2 - 1] = arguments[_key2];
    }

    Matomo_Matomo.postEventNoEmit.apply(Matomo_Matomo, [name].concat(args));
    return this.$oldBroadcast.apply(this, [name].concat(args)); // eslint-disable-line
  };

  $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
}

initPiwikService.$inject = ['piwik', '$rootScope'];
window.angular.module('piwikApp.service').run(initPiwikService);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts
function AjaxHelper_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function AjaxHelper_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { AjaxHelper_ownKeys(Object(source), true).forEach(function (key) { AjaxHelper_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { AjaxHelper_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function AjaxHelper_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function AjaxHelper_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function AjaxHelper_createClass(Constructor, protoProps, staticProps) { if (protoProps) AjaxHelper_defineProperties(Constructor.prototype, protoProps); if (staticProps) AjaxHelper_defineProperties(Constructor, staticProps); return Constructor; }

function AjaxHelper_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


window.globalAjaxQueue = [];
window.globalAjaxQueue.active = 0;

window.globalAjaxQueue.clean = function globalAjaxQueueClean() {
  for (var i = this.length; i >= 0; i -= 1) {
    if (!this[i] || this[i].readyState === 4) {
      this.splice(i, 1);
    }
  }
};

window.globalAjaxQueue.push = function globalAjaxQueuePush() {
  var _Array$prototype$push;

  for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
    args[_key] = arguments[_key];
  }

  this.active += args.length; // cleanup ajax queue

  this.clean(); // call original array push

  return (_Array$prototype$push = Array.prototype.push).call.apply(_Array$prototype$push, [this].concat(args));
};

window.globalAjaxQueue.abort = function globalAjaxQueueAbort() {
  // abort all queued requests if possible
  this.forEach(function (x) {
    return x && x.abort && x.abort();
  }); // remove all elements from array

  this.splice(0, this.length);
  this.active = 0;
};
/**
 * error callback to use by default
 */


function defaultErrorCallback(deferred, status) {
  // do not display error message if request was aborted
  if (status === 'abort') {
    return;
  }

  if (typeof Piwik_Popover === 'undefined') {
    console.log("Request failed: ".concat(deferred.responseText)); // mostly for tests

    return;
  }

  var loadingError = $('#loadingError');

  if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
    if (deferred && deferred.status === 500) {
      $(document.body).html(piwikHelper.escape(deferred.responseText));
    }
  } else {
    loadingError.show();
  }
}
/**
 * Global ajax helper to handle requests within Matomo
 */


var AjaxHelper_AjaxHelper = /*#__PURE__*/function () {
  function AjaxHelper() {
    AjaxHelper_classCallCheck(this, AjaxHelper);

    AjaxHelper_defineProperty(this, "format", 'json');

    AjaxHelper_defineProperty(this, "timeout", null);

    AjaxHelper_defineProperty(this, "callback", null);

    AjaxHelper_defineProperty(this, "useRegularCallbackInCaseOfError", false);

    AjaxHelper_defineProperty(this, "errorCallback", void 0);

    AjaxHelper_defineProperty(this, "withToken", false);

    AjaxHelper_defineProperty(this, "completeCallback", void 0);

    AjaxHelper_defineProperty(this, "getParams", {});

    AjaxHelper_defineProperty(this, "getUrl", '?');

    AjaxHelper_defineProperty(this, "postParams", {});

    AjaxHelper_defineProperty(this, "loadingElement", null);

    AjaxHelper_defineProperty(this, "errorElement", '#ajaxError');

    AjaxHelper_defineProperty(this, "requestHandle", null);

    AjaxHelper_defineProperty(this, "defaultParams", ['idSite', 'period', 'date', 'segment']);

    this.errorCallback = defaultErrorCallback;
  }
  /**
   * Adds params to the request.
   * If params are given more then once, the latest given value is used for the request
   *
   * @param  initialParams
   * @param  type  type of given parameters (POST or GET)
   * @return {void}
   */


  AjaxHelper_createClass(AjaxHelper, [{
    key: "addParams",
    value: function addParams(initialParams, type) {
      var _this = this;

      var params = typeof initialParams === 'string' ? window.broadcast.getValuesFromUrl(initialParams) : initialParams;
      var arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
      Object.keys(params).forEach(function (key) {
        var value = params[key];

        if (arrayParams.indexOf(key) !== -1 && !value) {
          return;
        }

        if (type.toLowerCase() === 'get') {
          _this.getParams[key] = value;
        } else if (type.toLowerCase() === 'post') {
          _this.postParams[key] = value;
        }
      });
    }
  }, {
    key: "withTokenInUrl",
    value: function withTokenInUrl() {
      this.withToken = true;
    }
    /**
     * Sets the base URL to use in the AJAX request.
     */

  }, {
    key: "setUrl",
    value: function setUrl(url) {
      this.addParams(broadcast.getValuesFromUrl(url), 'GET');
    }
    /**
     * Gets this helper instance ready to send a bulk request. Each argument to this
     * function is a single request to use.
     */

  }, {
    key: "setBulkRequests",
    value: function setBulkRequests() {
      for (var _len2 = arguments.length, urls = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        urls[_key2] = arguments[_key2];
      }

      var urlsProcessed = urls.map(function (u) {
        return typeof u === 'string' ? u : $.param(u);
      });
      this.addParams({
        module: 'API',
        method: 'API.getBulkRequest',
        urls: urlsProcessed,
        format: 'json'
      }, 'post');
    }
    /**
     * Set a timeout (in milliseconds) for the request. This will override any global timeout.
     *
     * @param timeout  Timeout in milliseconds
     */

  }, {
    key: "setTimeout",
    value: function setTimeout(timeout) {
      this.timeout = timeout;
    }
    /**
     * Sets the callback called after the request finishes
     *
     * @param callback  Callback function
     * @deprecated use the jquery promise API
     */

  }, {
    key: "setCallback",
    value: function setCallback(callback) {
      this.callback = callback;
    }
    /**
     * Set that the callback passed to setCallback() should be used if an application error (i.e. an
     * Exception in PHP) is returned.
     */

  }, {
    key: "useCallbackInCaseOfError",
    value: function useCallbackInCaseOfError() {
      this.useRegularCallbackInCaseOfError = true;
    }
    /**
     * Set callback to redirect on success handler
     * &update=1(+x) will be appended to the current url
     *
     * @param [params] to modify in redirect url
     * @return {void}
     */

  }, {
    key: "redirectOnSuccess",
    value: function redirectOnSuccess(params) {
      this.setCallback(function () {
        piwikHelper.redirect(params);
      });
    }
    /**
     * Sets the callback called in case of an error within the request
     *
     * @deprecated use the jquery promise API
     */

  }, {
    key: "setErrorCallback",
    value: function setErrorCallback(callback) {
      this.errorCallback = callback;
    }
    /**
     * Sets the complete callback which is called after an error or success callback.
     *
     * @deprecated use the jquery promise API
     */

  }, {
    key: "setCompleteCallback",
    value: function setCompleteCallback(callback) {
      this.completeCallback = callback;
    }
    /**
     * Sets the response format for the request
     *
     * @param format  response format (e.g. json, html, ...)
     */

  }, {
    key: "setFormat",
    value: function setFormat(format) {
      this.format = format;
    }
    /**
     * Set the div element to show while request is loading
     *
     * @param [element]  selector for the loading element
     */

  }, {
    key: "setLoadingElement",
    value: function setLoadingElement(element) {
      this.loadingElement = element || '#ajaxLoadingDiv';
    }
    /**
     * Set the div element to show on error
     *
     * @param element  selector for the error element
     */

  }, {
    key: "setErrorElement",
    value: function setErrorElement(element) {
      if (!element) {
        return;
      }

      this.errorElement = element;
    }
    /**
     * Detect whether are allowed to use the given default parameter or not
     */

  }, {
    key: "useGETDefaultParameter",
    value: function useGETDefaultParameter(parameter) {
      if (parameter && this.defaultParams) {
        for (var i = 0; i < this.defaultParams.length; i += 1) {
          if (this.defaultParams[i] === parameter) {
            return true;
          }
        }
      }

      return false;
    }
    /**
     * Removes a default parameter that is usually send automatically along the request.
     *
     * @param parameter  A name such as "period", "date", "segment".
     */

  }, {
    key: "removeDefaultParameter",
    value: function removeDefaultParameter(parameter) {
      if (parameter && this.defaultParams) {
        for (var i = 0; i < this.defaultParams.length; i += 1) {
          if (this.defaultParams[i] === parameter) {
            this.defaultParams.splice(i, 1);
          }
        }
      }
    }
    /**
     * Send the request
     */

  }, {
    key: "send",
    value: function send() {
      var _this2 = this;

      if ($(this.errorElement).length) {
        $(this.errorElement).hide();
      }

      if (this.loadingElement) {
        $(this.loadingElement).fadeIn();
      }

      this.requestHandle = this.buildAjaxCall();
      window.globalAjaxQueue.push(this.requestHandle);
      return new Promise(function (resolve, reject) {
        _this2.requestHandle.then(resolve).fail(function (xhr) {
          if (xhr.statusText !== 'abort') {
            console.log("Warning: the ".concat($.param(_this2.getParams), " request failed!"));
            reject(xhr);
          }
        });
      });
    }
    /**
     * Aborts the current request if it is (still) running
     */

  }, {
    key: "abort",
    value: function abort() {
      if (this.requestHandle && typeof this.requestHandle.abort === 'function') {
        this.requestHandle.abort();
        this.requestHandle = null;
      }
    }
    /**
     * Builds and sends the ajax requests
     */

  }, {
    key: "buildAjaxCall",
    value: function buildAjaxCall() {
      var _this3 = this;

      var self = this;
      var parameters = this.mixinDefaultGetParams(this.getParams);
      var url = this.getUrl;

      if (url[url.length - 1] !== '?') {
        url += '&';
      } // we took care of encoding &segment properly already, so we don't use $.param for it ($.param
      // URL encodes the values)


      if (parameters.segment) {
        url = "".concat(url, "segment=").concat(parameters.segment, "&");
        delete parameters.segment;
      }

      if (parameters.date) {
        url = "".concat(url, "date=").concat(decodeURIComponent(parameters.date.toString()), "&");
        delete parameters.date;
      }

      url += $.param(parameters);
      var ajaxCall = {
        type: 'POST',
        async: true,
        url: url,
        dataType: this.format || 'json',
        complete: this.completeCallback,
        error: function errorCallback() {
          window.globalAjaxQueue.active -= 1;

          if (self.errorCallback) {
            for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
              args[_key3] = arguments[_key3];
            }

            self.errorCallback.apply(this, args);
          }
        },
        success: function success(response, status, request) {
          if (_this3.loadingElement) {
            $(_this3.loadingElement).hide();
          }

          if (response && response.result === 'error' && !_this3.useRegularCallbackInCaseOfError) {
            var placeAt = null;
            var type = 'toast';

            if ($(_this3.errorElement).length && response.message) {
              $(_this3.errorElement).show();
              placeAt = _this3.errorElement;
              type = null;
            }

            if (response.message) {
              var UI = window['require']('piwik/UI'); // eslint-disable-line

              var notification = new UI.Notification();
              notification.show(response.message, {
                placeat: placeAt,
                context: 'error',
                type: type,
                id: 'ajaxHelper'
              });
              notification.scrollToNotification();
            }
          } else if (_this3.callback) {
            _this3.callback(response, status, request);
          }

          window.globalAjaxQueue.active -= 1;

          if (Matomo_Matomo.ajaxRequestFinished) {
            Matomo_Matomo.ajaxRequestFinished();
          }
        },
        data: this.mixinDefaultPostParams(this.postParams),
        timeout: this.timeout !== null ? this.timeout : undefined
      };
      return $.ajax(ajaxCall);
    }
  }, {
    key: "isRequestToApiMethod",
    value: function isRequestToApiMethod() {
      return this.getParams && this.getParams.module === 'API' && this.getParams.method || this.postParams && this.postParams.module === 'API' && this.postParams.method;
    }
  }, {
    key: "isWidgetizedRequest",
    value: function isWidgetizedRequest() {
      return broadcast.getValueFromUrl('module') === 'Widgetize';
    }
  }, {
    key: "getDefaultPostParams",
    value: function getDefaultPostParams() {
      if (this.withToken || this.isRequestToApiMethod() || Matomo_Matomo.shouldPropagateTokenAuth) {
        return {
          token_auth: Matomo_Matomo.token_auth,
          // When viewing a widgetized report there won't be any session that can be used, so don't
          // force session usage
          force_api_session: broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1
        };
      }

      return {};
    }
    /**
     * Mixin the default parameters to send as POST
     *
     * @param params   parameter object
     */

  }, {
    key: "mixinDefaultPostParams",
    value: function mixinDefaultPostParams(params) {
      var defaultParams = this.getDefaultPostParams();

      var mergedParams = AjaxHelper_objectSpread(AjaxHelper_objectSpread({}, defaultParams), params);

      return mergedParams;
    }
    /**
     * Mixin the default parameters to send as GET
     *
     * @param   params   parameter object
     */

  }, {
    key: "mixinDefaultGetParams",
    value: function mixinDefaultGetParams(originalParams) {
      var _this4 = this;

      var segment = src_MatomoUrl_MatomoUrl.getSearchParam('segment');
      var defaultParams = {
        idSite: Matomo_Matomo.idSite ? Matomo_Matomo.idSite.toString() : broadcast.getValueFromUrl('idSite'),
        period: Matomo_Matomo.period || broadcast.getValueFromUrl('period'),
        segment: segment
      };
      var params = originalParams; // never append token_auth to url

      if (params.token_auth) {
        params.token_auth = null;
        delete params.token_auth;
      }

      Object.keys(defaultParams).forEach(function (key) {
        if (_this4.useGETDefaultParameter(key) && !params[key] && !_this4.postParams[key] && defaultParams[key]) {
          params[key] = defaultParams[key];
        }
      }); // handle default date & period if not already set

      if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
        params.date = Matomo_Matomo.currentDateString;
      }

      return params;
    }
  }], [{
    key: "fetch",
    value:
    /**
     * Format of response
     */

    /**
     * A timeout for the request which will override any global timeout
     */

    /**
     * Callback function to be executed on success
     */

    /**
     * Use this.callback if an error is returned
     */

    /**
     * Callback function to be executed on error
     *
     * @deprecated use the jquery promise API
     */

    /**
     * Callback function to be executed on complete (after error or success)
     *
     * @deprecated use the jquery promise API
     */

    /**
     * Params to be passed as GET params
     * @see ajaxHelper.mixinDefaultGetParams
     */

    /**
     * Base URL used in the AJAX request. Can be set by setUrl.
     *
     * It is set to '?' rather than 'index.php?' to increase chances that it works
     * including for users who have an automatic 301 redirection from index.php? to ?
     * POST values are missing when there is such 301 redirection. So by by-passing
     * this 301 redirection, we avoid this issue.
     *
     * @see ajaxHelper.setUrl
     */

    /**
     * Params to be passed as GET params
     * @see ajaxHelper.mixinDefaultPostParams
     */

    /**
     * Element to be displayed while loading
     */

    /**
     * Element to be displayed on error
     */

    /**
     * Handle for current request
     */
    // helper method entry point
    function fetch(params) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var helper = new AjaxHelper();

      if (options.withTokenInUrl) {
        helper.withTokenInUrl();
      }

      helper.setFormat('json');
      helper.addParams(AjaxHelper_objectSpread({
        module: 'API',
        format: 'json'
      }, params), 'get');

      if (options.postParams) {
        helper.addParams(options.postParams, 'post');
      }

      return helper.send();
    }
  }]);

  return AjaxHelper;
}();


// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts

window.ajaxHelper = AjaxHelper_AjaxHelper;

function ajaxQueue() {
  return globalAjaxQueue;
}

angular.module('piwikApp.service').service('globalAjaxQueue', ajaxQueue);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownMenu/DropdownMenu.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * A materializecss dropdown menu that supports submenus.
 *
 * To use a submenu, just use this directive within another dropdown.
 *
 * Note: if submenus are used, then dropdowns will never scroll.
 *
 * Usage:
 * <a class='dropdown-trigger btn' href='' data-target='mymenu' v-dropdown-menu>Menu</a>
 * <ul id='mymenu' class='dropdown-content'>
 *     <li>
 *         <a class='dropdown-trigger' data-target="mysubmenu" v-dropdown-menu>Submenu</a>
 *         <ul id="mysubmenu" class="dropdown-content">
 *             <li>Submenu Item</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <a href="">Another item</a>
 *     </li>
 * </ul>
 */
/* harmony default export */ var DropdownMenu = ({
  mounted: function mounted(element, binding) {
    var options = {};
    $(element).addClass('matomo-dropdown-menu');
    var isSubmenu = !!$(element).parent().closest('.dropdown-content').length;

    if (isSubmenu) {
      options = {
        hover: true
      };
      $(element).addClass('submenu');
      $(binding.value.activates).addClass('submenu-dropdown-content'); // if a submenu is used, the dropdown will never scroll

      $(element).parents('.dropdown-content').addClass('submenu-container');
    }

    $(element).dropdown(options);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownMenu/DropdownMenu.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikDropdownMenu($timeout) {
  return {
    restrict: 'A',
    link: function piwikDropdownMenuLink(scope, element, attrs) {
      var binding = {
        instance: null,
        value: {
          activates: $("#".concat(attrs.activates))[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      $timeout(function () {
        DropdownMenu.mounted(element[0], binding);
      });
    }
  };
}

piwikDropdownMenu.$inject = ['$timeout'];
angular.module('piwikApp').directive('piwikDropdownMenu', piwikDropdownMenu);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusAnywhereButHere/FocusAnywhereButHere.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function onClickOutsideElement(element, binding, event) {
  var hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
  binding.value.isMouseDown = false;
  binding.value.hasScrolled = false;

  if (hadUsedScrollbar) {
    return;
  }

  if (!element.contains(event.target)) {
    if (binding.value) {
      binding.value.blur();
    }
  }
}

function onScroll(element, binding) {
  binding.value.hasScrolled = true;
}

function onMouseDown(element, binding) {
  binding.value.isMouseDown = true;
  binding.value.hasScrolled = false;
}

function onEscapeHandler(element, binding, event) {
  if (event.which === 27) {
    setTimeout(function () {
      binding.value.isMouseDown = false;
      binding.value.hasScrolled = false;

      if (binding.value.blur) {
        binding.value.blur();
      }
    }, 0);
  }
}

var doc = document.documentElement;
/**
 * Usage (in a component):
 *
 * directives: {
 *   // function call is important since we store state in this directive
 *   FocusAnywhereButHere: FocusAnywhereButHere(),
 * }
 *
 * Note: the binding data needs to be static, changes will not be handled.
 */

/* harmony default export */ var FocusAnywhereButHere = ({
  mounted: function mounted(el, binding) {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onEscapeHandler = onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = onMouseDown.bind(null, el, binding);
    binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el, binding);
    binding.value.onScroll = onScroll.bind(null, el, binding);
    doc.addEventListener('keyup', binding.value.onEscapeHandler);
    doc.addEventListener('mousedown', binding.value.onMouseDown);
    doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted: function unmounted(el, binding) {
    doc.removeEventListener('keyup', binding.value.onEscapeHandler);
    doc.removeEventListener('mousedown', binding.value.onMouseDown);
    doc.removeEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.removeEventListener('scroll', binding.value.onScroll);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusAnywhereButHere/FocusAnywhereButHere.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The given expression will be executed when the user presses either escape or presses something
 * outside of this element
 *
 * Example:
 * <div piwik-focus-anywhere-but-here="closeDialog()">my dialog</div>
 */

function piwikFocusAnywhereButHere() {
  return {
    restrict: 'A',
    link: function focusAnywhereButHereLink(scope, element, attr) {
      var binding = {
        instance: null,
        value: {
          blur: function blur() {
            setTimeout(function () {
              scope.$apply(attr.piwikFocusAnywhereButHere);
            }, 0);
          }
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      FocusAnywhereButHere.mounted(element[0], binding);
      element.on('$destroy', function () {
        return FocusAnywhereButHere.unmounted(element[0], binding);
      });
    }
  };
}

piwikFocusAnywhereButHere.$inject = [];
angular.module('piwikApp.directive').directive('piwikFocusAnywhereButHere', piwikFocusAnywhereButHere);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusIf/FocusIf.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function doFocusIf(el, binding) {
  if (binding.arg) {
    setTimeout(function () {
      el.focus();

      if (binding.value.afterFocus) {
        binding.value.afterFocus();
      }
    }, 5);
  }
}

/* harmony default export */ var FocusIf = ({
  mounted: function mounted(el, binding) {
    doFocusIf(el, binding);
  },
  updated: function updated(el, binding) {
    doFocusIf(el, binding);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusIf/FocusIf.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * If the given expression evaluates to true the element will be focused
 *
 * Example:
 * <input type="text" piwik-focus-if="view.editName">
 */

function piwikFocusIf() {
  return {
    restrict: 'A',
    link: function focusIfLink(scope, element, attrs) {
      scope.$watch(attrs.piwikFocusIf, function (newValue) {
        var binding = {
          instance: null,
          arg: newValue ? '1' : undefined,
          value: {
            afterFocus: function afterFocus() {
              return scope.$apply();
            }
          },
          oldValue: null,
          modifiers: {},
          dir: {}
        };
        FocusIf.updated(element[0], binding);
      });
    }
  };
}

angular.module('piwikApp.directive').directive('piwikFocusIf', piwikFocusIf);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnClick/ExpandOnClick.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function onExpand(element) {
  element.classList.toggle('expanded');
  var positionElement = element.querySelector('.dropdown.positionInViewport');

  if (positionElement) {
    Matomo_Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}

function ExpandOnClick_onClickOutsideElement(element, binding, event) {
  var hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
  binding.value.isMouseDown = false;
  binding.value.hasScrolled = false;

  if (hadUsedScrollbar) {
    return;
  }

  if (!element.contains(event.target)) {
    element.classList.remove('expanded');
  }
}

function ExpandOnClick_onScroll(binding) {
  binding.value.hasScrolled = true;
}

function ExpandOnClick_onMouseDown(binding) {
  binding.value.isMouseDown = true;
  binding.value.hasScrolled = false;
}

function ExpandOnClick_onEscapeHandler(element, binding, event) {
  if (event.which === 27) {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    element.classList.remove('expanded');
  }
}

var ExpandOnClick_doc = document.documentElement;
/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */

/* harmony default export */ var ExpandOnClick = ({
  mounted: function mounted(el, binding) {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onExpand = onExpand.bind(null, el);
    binding.value.onEscapeHandler = ExpandOnClick_onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = ExpandOnClick_onMouseDown.bind(null, binding);
    binding.value.onClickOutsideElement = ExpandOnClick_onClickOutsideElement.bind(null, el, binding);
    binding.value.onScroll = ExpandOnClick_onScroll.bind(null, binding); // have to use jquery here since existing code will do $(...).click(). which apparently
    // doesn't work when using addEventListener.

    window.$(binding.value.expander).click(binding.value.onExpand);
    ExpandOnClick_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnClick_doc.addEventListener('mousedown', binding.value.onMouseDown);
    ExpandOnClick_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    ExpandOnClick_doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted: function unmounted(el, binding) {
    binding.value.expander.removeEventListener('click', binding.value.onExpand);
    ExpandOnClick_doc.removeEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnClick_doc.removeEventListener('mousedown', binding.value.onMouseDown);
    ExpandOnClick_doc.removeEventListener('mouseup', binding.value.onClickOutsideElement);
    ExpandOnClick_doc.removeEventListener('scroll', binding.value.onScroll);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnClick/ExpandOnClick.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikExpandOnClick() {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope, element) {
      var binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      ExpandOnClick.mounted(element[0], binding);
      element.on('$destroy', function () {
        return ExpandOnClick.unmounted(element[0], binding);
      });
    }
  };
}
piwikExpandOnClick.$inject = [];
angular.module('piwikApp').directive('piwikExpandOnClick', piwikExpandOnClick);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnHover/ExpandOnHover.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function onMouseEnter(element) {
  element.classList.add('expanded');
  var positionElement = element.querySelector('.dropdown.positionInViewport');

  if (positionElement) {
    Matomo_Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}

function onMouseLeave(element) {
  element.classList.remove('expanded');
}

function ExpandOnHover_onClickOutsideElement(element, event) {
  if (!element.contains(event.target)) {
    element.classList.remove('expanded');
  }
}

function ExpandOnHover_onEscapeHandler(element, event) {
  if (event.which === 27) {
    element.classList.remove('expanded');
  }
}

var ExpandOnHover_doc = document.documentElement;
/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnHover: ExpandOnHover(), // function call is important since we store state
 *                                   // in this directive
 * }
 */

/* harmony default export */ var ExpandOnHover = ({
  mounted: function mounted(el, binding) {
    binding.value.onMouseEnter = onMouseEnter.bind(null, el);
    binding.value.onMouseLeave = onMouseLeave.bind(null, el);
    binding.value.onClickOutsideElement = ExpandOnHover_onClickOutsideElement.bind(null, el);
    binding.value.onEscapeHandler = ExpandOnHover_onEscapeHandler.bind(null, el);
    binding.value.expander.addEventListener('mouseenter', binding.value.onMouseEnter);
    el.addEventListener('mouseleave', binding.value.onMouseLeave);
    ExpandOnHover_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnHover_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
  },
  unmounted: function unmounted(el, binding) {
    binding.value.expander.removeEventListener('mouseenter', binding.value.onMouseEnter);
    el.removeEventListener('mouseleave', binding.value.onMouseLeave);
    document.removeEventListener('keyup', binding.value.onEscapeHandler);
    document.removeEventListener('mouseup', binding.value.onClickOutsideElement);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnHover/ExpandOnHover.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikExpandOnHover() {
  return {
    restrict: 'A',
    link: function expandOnHoverLink(scope, element) {
      var binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      ExpandOnHover.mounted(element[0], binding);
      element.on('$destroy', function () {
        return ExpandOnHover.unmounted(element[0], binding);
      });
    }
  };
}

piwikExpandOnHover.$inject = [];
angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=64e27324

var _hoisted_1 = {
  ref: "root"
};
function MatomoDialogvue_type_template_id_64e27324_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.modelValue]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=64e27324

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=script&lang=ts


/* harmony default export */ var MatomoDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /**
     * Whether the modal is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true
    },

    /**
     * Only here for backwards compatibility w/ AngularJS. If supplied, we use this
     * element to launch the modal instead of the element in the slot. This should not
     * be used for new Vue code.
     *
     * @deprecated
     */
    element: {
      type: HTMLElement,
      required: false
    }
  },
  emits: ['yes', 'no', 'closeEnd', 'close', 'validation', 'update:modelValue'],
  activated: function activated() {
    this.$emit('update:modelValue', false);
  },
  watch: {
    modelValue: function modelValue(newValue, oldValue) {
      var _this = this;

      if (newValue) {
        var slotElement = this.element || this.$refs.root.firstElementChild;
        Matomo_Matomo.helper.modalConfirm(slotElement, {
          yes: function yes() {
            _this.$emit('yes');
          },
          no: function no() {
            _this.$emit('no');
          },
          validation: function validation() {
            _this.$emit('validation');
          }
        }, {
          onCloseEnd: function onCloseEnd() {
            // materialize removes the child element, so we move it back to the slot
            if (!_this.element) {
              _this.$refs.root.appendChild(slotElement);
            }

            _this.$emit('update:modelValue', false);

            _this.$emit('closeEnd');
          }
        });
      } else if (newValue === false && oldValue === true) {
        // the user closed the dialog, e.g. by pressing Esc or clicking away from it
        this.$emit('close');
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue



MatomoDialogvue_type_script_lang_ts.render = MatomoDialogvue_type_template_id_64e27324_render

/* harmony default export */ var MatomoDialog = (MatomoDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/createAngularJsAdapter.ts
function createAngularJsAdapter_slicedToArray(arr, i) { return createAngularJsAdapter_arrayWithHoles(arr) || createAngularJsAdapter_iterableToArrayLimit(arr, i) || createAngularJsAdapter_unsupportedIterableToArray(arr, i) || createAngularJsAdapter_nonIterableRest(); }

function createAngularJsAdapter_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function createAngularJsAdapter_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return createAngularJsAdapter_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return createAngularJsAdapter_arrayLikeToArray(o, minLen); }

function createAngularJsAdapter_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function createAngularJsAdapter_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function createAngularJsAdapter_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


var transcludeCounter = 0;

function toKebabCase(arg) {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1).replace(/[A-Z]/g, function (s) {
    return "-".concat(s.toLowerCase());
  });
}

function toAngularJsCamelCase(arg) {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1).replace(/-([a-z])/g, function (s, p) {
    return p.toUpperCase();
  });
}

function createAngularJsAdapter(options) {
  var component = options.component,
      _options$scope = options.scope,
      scope = _options$scope === void 0 ? {} : _options$scope,
      _options$events = options.events,
      events = _options$events === void 0 ? {} : _options$events,
      $inject = options.$inject,
      directiveName = options.directiveName,
      transclude = options.transclude,
      mountPointFactory = options.mountPointFactory,
      postCreate = options.postCreate,
      noScope = options.noScope,
      _options$restrict = options.restrict,
      restrict = _options$restrict === void 0 ? 'A' : _options$restrict;
  var currentTranscludeCounter = transcludeCounter;

  if (transclude) {
    transcludeCounter += 1;
  }

  var angularJsScope = {};
  Object.entries(scope).forEach(function (_ref) {
    var _ref2 = createAngularJsAdapter_slicedToArray(_ref, 2),
        scopeVarName = _ref2[0],
        info = _ref2[1];

    if (!info.vue) {
      info.vue = scopeVarName;
    }

    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
  });

  function angularJsAdapter() {
    for (var _len = arguments.length, injectedServices = new Array(_len), _key = 0; _key < _len; _key++) {
      injectedServices[_key] = arguments[_key];
    }

    var adapter = {
      restrict: restrict,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(ngScope, ngElement, ngAttrs) {
            var clone = transclude ? ngElement.find("[ng-transclude][counter=".concat(currentTranscludeCounter, "]")) : null; // build the root vue template

            var rootVueTemplate = '<root-component';
            Object.entries(events).forEach(function (info) {
              var _info = createAngularJsAdapter_slicedToArray(info, 1),
                  eventName = _info[0];

              rootVueTemplate += " @".concat(eventName, "=\"onEventHandler('").concat(eventName, "', $event)\"");
            });
            Object.entries(scope).forEach(function (_ref3) {
              var _ref4 = createAngularJsAdapter_slicedToArray(_ref3, 2),
                  key = _ref4[0],
                  info = _ref4[1];

              if (info.angularJsBind === '&') {
                var eventName = toKebabCase(key);

                if (!events[eventName]) {
                  // pass through scope & w/o a custom event handler
                  rootVueTemplate += " @".concat(eventName, "=\"onEventHandler('").concat(eventName, "', $event)\"");
                }
              } else {
                rootVueTemplate += " :".concat(info.vue, "=\"").concat(info.vue, "\"");
              }
            });
            rootVueTemplate += '>';

            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }

            rootVueTemplate += '</root-component>'; // build the vue app

            var app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
              template: rootVueTemplate,
              data: function data() {
                var initialData = {};
                Object.entries(scope).forEach(function (_ref5) {
                  var _ref6 = createAngularJsAdapter_slicedToArray(_ref5, 2),
                      scopeVarName = _ref6[0],
                      info = _ref6[1];

                  var value = ngScope[scopeVarName];

                  if (typeof value === 'undefined' && typeof info.default !== 'undefined') {
                    value = info.default instanceof Function ? info.default.apply(info, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : info.default;
                  }

                  if (info.transform) {
                    value = info.transform(value);
                  }

                  initialData[info.vue] = value;
                });
                return initialData;
              },
              setup: function setup() {
                if (transclude) {
                  var transcludeTarget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
                  return {
                    transcludeTarget: transcludeTarget
                  };
                }

                return undefined;
              },
              methods: {
                onEventHandler: function onEventHandler(name, $event) {
                  var scopePropertyName = toAngularJsCamelCase(name);

                  if (ngScope[scopePropertyName]) {
                    ngScope[scopePropertyName]($event);
                  }

                  if (events[name]) {
                    events[name].apply(events, [$event, ngScope, ngElement, ngAttrs].concat(injectedServices));
                  }
                }
              }
            });
            app.config.globalProperties.$sanitize = window.vueSanitize;
            app.config.globalProperties.translate = translate;
            app.component('root-component', component); // mount the app

            var mountPoint = mountPointFactory ? mountPointFactory.apply(void 0, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : ngElement[0];
            var vm = app.mount(mountPoint); // setup watches to bind between angularjs + vue

            Object.entries(scope).forEach(function (_ref7) {
              var _ref8 = createAngularJsAdapter_slicedToArray(_ref7, 2),
                  scopeVarName = _ref8[0],
                  info = _ref8[1];

              if (!info.angularJsBind || info.angularJsBind === '&') {
                return;
              }

              ngScope.$watch(scopeVarName, function (newValue) {
                var newValueFinal = newValue;

                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  newValueFinal = info.default instanceof Function ? info.default.apply(info, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : info.default;
                }

                if (info.transform) {
                  newValueFinal = info.transform(newValueFinal);
                }

                vm[scopeVarName] = newValueFinal;
              });
            });

            if (transclude) {
              $(vm.transcludeTarget).append(clone);
            }

            if (postCreate) {
              postCreate.apply(void 0, [vm, ngScope, ngElement, ngAttrs].concat(injectedServices));
            }

            ngElement.on('$destroy', function () {
              app.unmount();
            });
          }
        };
      }
    };

    if (transclude) {
      adapter.transclude = true;
      adapter.template = "<div ng-transclude counter=\"".concat(currentTranscludeCounter, "\"/>");
    }

    return adapter;
  }

  angularJsAdapter.$inject = $inject || [];
  angular.module('piwikApp').directive(directiveName, angularJsAdapter);
  return angularJsAdapter;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var MatomoDialog_adapter = (createAngularJsAdapter({
  component: MatomoDialog,
  scope: {
    show: {
      vue: 'modelValue',
      default: false
    },
    element: {
      default: function _default(scope, element) {
        return element[0];
      }
    }
  },
  events: {
    yes: function yes($event, scope, element, attrs) {
      if (attrs.yes) {
        scope.$eval(attrs.yes);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    no: function no($event, scope, element, attrs) {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    validation: function validation($event, scope, element, attrs) {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    close: function close($event, scope, element, attrs) {
      if (attrs.close) {
        scope.$eval(attrs.close);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    'update:modelValue': function updateModelValue(newValue, scope, element, attrs, $parse) {
      setTimeout(function () {
        scope.$apply($parse(attrs.piwikDialog).assign(scope, newValue));
      }, 0);
    }
  },
  $inject: ['$parse'],
  directiveName: 'piwikDialog',
  transclude: true,
  mountPointFactory: function mountPointFactory(scope, element) {
    var vueRootPlaceholder = $('<div class="vue-placeholder"/>');
    vueRootPlaceholder.appendTo(element);
    return vueRootPlaceholder[0];
  },
  postCreate: function postCreate(vm, scope, element, attrs) {
    scope.$watch(attrs.piwikDialog, function (newValue, oldValue) {
      if (oldValue !== newValue) {
        vm.modelValue = newValue || false;
      }
    });
  },
  noScope: true
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=40f81493

var EnrichedHeadlinevue_type_template_id_40f81493_hoisted_1 = {
  key: 0,
  class: "title",
  tabindex: "6"
};
var _hoisted_2 = ["href", "title"];
var _hoisted_3 = {
  class: "iconsBar"
};
var _hoisted_4 = ["href", "title"];

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

var _hoisted_6 = [_hoisted_5];
var _hoisted_7 = ["title"];

var _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);

var _hoisted_9 = [_hoisted_8];
var _hoisted_10 = {
  class: "ratingIcons"
};
var _hoisted_11 = {
  class: "inlineHelp"
};
var _hoisted_12 = ["innerHTML"];
var _hoisted_13 = ["href"];
function EnrichedHeadlinevue_type_template_id_40f81493_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_RateFeature = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RateFeature");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "enrichedHeadline",
    onMouseenter: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.showIcons = true;
    }),
    onMouseleave: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.showIcons = false;
    }),
    ref: "root"
  }, [!_ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", EnrichedHeadlinevue_type_template_id_40f81493_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "title",
    href: _ctx.editUrl,
    title: _ctx.translate('CoreHome_ClickToEditX', _ctx.$sanitize(_ctx.actualFeatureName))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 8, _hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_3, [_ctx.helpUrl && !_ctx.actualInlineHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    target: "_blank",
    class: "helpIcon",
    href: _ctx.helpUrl,
    title: _ctx.translate('CoreHome_ExternalHelp')
  }, _hoisted_6, 8, _hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualInlineHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showInlineHelp = !_ctx.showInlineHelp;
    }),
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      'active': _ctx.showInlineHelp
    }]),
    title: _ctx.translate(_ctx.reportGenerated ? 'General_HelpReport' : 'General_Help')
  }, _hoisted_9, 10, _hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RateFeature, {
    title: _ctx.actualFeatureName
  }, null, 8, ["title"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showIcons || _ctx.showInlineHelp]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.actualInlineHelp)
  }, null, 8, _hoisted_12), _ctx.helpUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    target: "_blank",
    class: "readMore",
    href: _ctx.helpUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 9, _hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showInlineHelp]])], 544);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=40f81493

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts


 // working around a cycle in dependencies (CoreHome depends on Feedback, Feedback depends on
// CoreHome)
// TODO: may need a generic solution at some point, but it's bad practice to have
// cyclic dependencies like this. it worked before because it was individual files
// dependening on each other, not whole plugins.

var RateFeature = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineAsyncComponent"])(function () {
  return new Promise(function (resolve) {
    window.$(document).ready(function () {
      var _window = window,
          Feedback = _window.Feedback; // eslint-disable-line

      if (Feedback) {
        resolve(Feedback.RateFeature);
      } else {
        // feedback plugin not loaded
        resolve(null);
      }
    });
  });
});
/**
 * Usage:
 *
 * <h2 piwik-enriched-headline>All Websites Dashboard</h2>
 * -> uses "All Websites Dashboard" as featurename
 *
 * <h2 piwik-enriched-headline feature-name="All Websites Dashboard">All Websites Dashboard (Total:
 * 309 Visits)</h2>
 * -> custom featurename
 *
 * <h2 piwik-enriched-headline help-url="http://piwik.org/guide">All Websites Dashboard</h2>
 * -> shows help icon and links to external url
 *
 * <h2 piwik-enriched-headline edit-url="index.php?module=Foo&action=bar&id=4">All Websites
 * Dashboard</h2>
 * -> makes the headline clickable linking to the specified url
 *
 * <h2 piwik-enriched-headline inline-help="inlineHelp">Pages report</h2>
 * -> inlineHelp specified via a attribute shows help icon on headline hover
 *
 * <h2 piwik-enriched-headline>All Websites Dashboard
 *     <div class="inlineHelp">My <strong>inline help</strong></div>
 * </h2>
 * -> alternative definition for inline help
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 *
 * * <h2 piwik-enriched-headline report-generated="generated time">Pages report</h2>
 * -> reportGenerated specified via this attribute shows a clock icon with a tooltip which
 * activated by hover
 * -> the tooltip shows the value of the attribute
 */

/* harmony default export */ var EnrichedHeadlinevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    helpUrl: {
      type: String,
      default: ''
    },
    editUrl: {
      type: String,
      default: ''
    },
    reportGenerated: String,
    featureName: String,
    inlineHelp: String
  },
  components: {
    RateFeature: RateFeature
  },
  data: function data() {
    return {
      showIcons: false,
      showInlineHelp: false,
      actualFeatureName: this.featureName,
      actualInlineHelp: this.inlineHelp
    };
  },
  watch: {
    inlineHelp: function inlineHelp(newValue) {
      this.actualInlineHelp = newValue;
    },
    featureName: function featureName(newValue) {
      this.actualFeatureName = newValue;
    }
  },
  mounted: function mounted() {
    var _this = this;

    var root = this.$refs.root; // timeout used since angularjs does not fill out the transclude at this point

    setTimeout(function () {
      if (!_this.actualInlineHelp) {
        var helpNode = root.querySelector('.title .inlineHelp');

        if (!helpNode && root.parentElement.nextElementSibling) {
          // hack for reports :(
          helpNode = root.parentElement.nextElementSibling.querySelector('.reportDocumentation');
        }

        if (helpNode) {
          // hackish solution to get binded html of p tag within the help node
          // at this point the ng-bind-html is not yet converted into html when report is not
          // initially loaded. Using $compile doesn't work. So get and set it manually
          var helpDocs = helpNode.getAttribute('data-content').trim();

          if (helpDocs.length) {
            _this.actualInlineHelp = "<p>".concat(helpDocs, "</p>");
            setTimeout(function () {
              return helpNode.remove();
            }, 0);
          }
        }
      }

      if (!_this.actualFeatureName) {
        _this.actualFeatureName = root.querySelector('.title').textContent;
      }

      if (_this.reportGenerated && Periods_Periods.parse(Matomo_Matomo.period, Matomo_Matomo.currentDateString).containsToday()) {
        window.$(root.querySelector('.report-generated')).tooltip({
          track: true,
          content: _this.reportGenerated,
          items: 'div',
          show: false,
          hide: false
        });
      }
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue



EnrichedHeadlinevue_type_script_lang_ts.render = EnrichedHeadlinevue_type_template_id_40f81493_render

/* harmony default export */ var EnrichedHeadline = (EnrichedHeadlinevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var EnrichedHeadline_adapter = (createAngularJsAdapter({
  component: EnrichedHeadline,
  scope: {
    helpUrl: {
      angularJsBind: '@'
    },
    editUrl: {
      angularJsBind: '@'
    },
    reportGenerated: {
      angularJsBind: '@?'
    },
    featureName: {
      angularJsBind: '@'
    },
    inlineHelp: {
      angularJsBind: '@?'
    }
  },
  directiveName: 'piwikEnrichedHeadline',
  transclude: true
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=09ef9e02

var ContentBlockvue_type_template_id_09ef9e02_hoisted_1 = {
  class: "card",
  ref: "root"
};
var ContentBlockvue_type_template_id_09ef9e02_hoisted_2 = {
  class: "card-content"
};
var ContentBlockvue_type_template_id_09ef9e02_hoisted_3 = {
  key: 0,
  class: "card-title"
};
var ContentBlockvue_type_template_id_09ef9e02_hoisted_4 = {
  key: 1,
  class: "card-title"
};
var ContentBlockvue_type_template_id_09ef9e02_hoisted_5 = {
  ref: "content"
};
function ContentBlockvue_type_template_id_09ef9e02_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_2, [_ctx.contentTitle && !_ctx.actualFeature && !_ctx.helpUrl && !_ctx.actualHelpText ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_09ef9e02_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.contentTitle), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.contentTitle && (_ctx.actualFeature || _ctx.helpUrl || _ctx.actualHelpText) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_09ef9e02_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.actualFeature,
    "help-url": _ctx.helpUrl,
    "inline-help": _ctx.actualHelpText
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.contentTitle), 1)];
    }),
    _: 1
  }, 8, ["feature-name", "help-url", "inline-help"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)])], 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=09ef9e02

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts


var adminContent = null;
/* harmony default export */ var ContentBlockvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    contentTitle: String,
    feature: String,
    helpUrl: String,
    helpText: String,
    anchor: String
  },
  components: {
    EnrichedHeadline: EnrichedHeadline
  },
  data: function data() {
    return {
      actualFeature: this.feature,
      actualHelpText: this.helpText
    };
  },
  watch: {
    feature: function feature(newValue) {
      this.actualFeature = newValue;
    },
    helpText: function helpText(newValue) {
      this.actualHelpText = newValue;
    }
  },
  mounted: function mounted() {
    var _this = this;

    var _this$$refs = this.$refs,
        root = _this$$refs.root,
        content = _this$$refs.content;

    if (this.anchor) {
      var anchorElement = document.createElement('a');
      anchorElement.id = this.anchor;
      root.parentElement.prepend(anchorElement);
    }

    setTimeout(function () {
      var inlineHelp = content.querySelector('.contentHelp');

      if (inlineHelp) {
        _this.actualHelpText = inlineHelp.innerHTML;
        inlineHelp.remove();
      }
    }, 0);

    if (this.actualFeature && (this.actualFeature === true || this.actualFeature === 'true')) {
      this.actualFeature = this.contentTitle;
    }

    if (adminContent === null) {
      // cache admin node for further content blocks
      adminContent = document.querySelector('#content.admin');
    }

    var contentTopPosition;

    if (adminContent) {
      contentTopPosition = adminContent.offsetTop;
    }

    if (contentTopPosition || contentTopPosition === 0) {
      var parents = root.closest('[piwik-widget-loader]'); // when shown within the widget loader, we need to get the offset of that element
      // as the widget loader might be still shown. Would otherwise not position correctly
      // the widgets on the admin home page

      var topThis = parents ? parents.offsetTop : root.offsetTop;

      if (topThis - contentTopPosition < 17) {
        // we make sure to display the first card with no margin-top to have it on same as line as
        // navigation
        root.style.marginTop = 0;
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue



ContentBlockvue_type_script_lang_ts.render = ContentBlockvue_type_template_id_09ef9e02_render

/* harmony default export */ var ContentBlock = (ContentBlockvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ContentBlock_adapter = (createAngularJsAdapter({
  component: ContentBlock,
  scope: {
    contentTitle: {
      angularJsBind: '@'
    },
    feature: {
      angularJsBind: '@'
    },
    helpUrl: {
      angularJsBind: '@'
    },
    helpText: {
      angularJsBind: '@'
    },
    anchor: {
      angularJsBind: '@?'
    }
  },
  directiveName: 'piwikContentBlock',
  transclude: true
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Segmentation/Segments.store.ts
function Segments_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Segments_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Segments_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) Segments_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) Segments_store_defineProperties(Constructor, staticProps); return Constructor; }

function Segments_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var Segments_store_SegmentsStore = /*#__PURE__*/function () {
  function SegmentsStore() {
    var _this = this;

    Segments_store_classCallCheck(this, SegmentsStore);

    Segments_store_defineProperty(this, "segmentState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      availableSegments: []
    }));

    Matomo_Matomo.on('piwikSegmentationInited', function () {
      return _this.setSegmentState();
    });
  }

  Segments_store_createClass(SegmentsStore, [{
    key: "state",
    get: function get() {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.segmentState);
    }
  }, {
    key: "setSegmentState",
    value: function setSegmentState() {
      try {
        var uiControlObject = $('.segmentEditorPanel').data('uiControlObject');
        this.segmentState.availableSegments = uiControlObject.impl.availableSegments || [];
      } catch (e) {// segment editor is not initialized yet
      }
    }
  }]);

  return SegmentsStore;
}();

/* harmony default export */ var Segments_store = (new Segments_store_SegmentsStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.store.ts
function Comparisons_store_toConsumableArray(arr) { return Comparisons_store_arrayWithoutHoles(arr) || Comparisons_store_iterableToArray(arr) || Comparisons_store_unsupportedIterableToArray(arr) || Comparisons_store_nonIterableSpread(); }

function Comparisons_store_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function Comparisons_store_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return Comparisons_store_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return Comparisons_store_arrayLikeToArray(o, minLen); }

function Comparisons_store_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function Comparisons_store_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return Comparisons_store_arrayLikeToArray(arr); }

function Comparisons_store_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function Comparisons_store_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function Comparisons_store_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { Comparisons_store_ownKeys(Object(source), true).forEach(function (key) { Comparisons_store_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { Comparisons_store_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function Comparisons_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Comparisons_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Comparisons_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) Comparisons_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) Comparisons_store_defineProperties(Constructor, staticProps); return Constructor; }

function Comparisons_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */







var SERIES_COLOR_COUNT = 8;
var SERIES_SHADE_COUNT = 3;

function wrapArray(values) {
  if (!values) {
    return [];
  }

  return values instanceof Array ? values : [values];
}

var Comparisons_store_ComparisonsStore = /*#__PURE__*/function () {
  // for tests
  function ComparisonsStore() {
    var _this = this;

    Comparisons_store_classCallCheck(this, ComparisonsStore);

    Comparisons_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      comparisonsDisabledFor: []
    }));

    Comparisons_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState));

    Comparisons_store_defineProperty(this, "colors", {});

    Comparisons_store_defineProperty(this, "segmentComparisons", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.parseSegmentComparisons();
    }));

    Comparisons_store_defineProperty(this, "periodComparisons", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.parsePeriodComparisons();
    }));

    Comparisons_store_defineProperty(this, "isEnabled", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.checkEnabledForCurrentPage();
    }));

    this.loadComparisonsDisabledFor();
    $(function () {
      _this.colors = _this.getAllSeriesColors();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return _this.getComparisons();
    }, function () {
      return Matomo_Matomo.postEvent('piwikComparisonsChanged');
    }, {
      deep: true
    });
  }

  Comparisons_store_createClass(ComparisonsStore, [{
    key: "getComparisons",
    value: function getComparisons() {
      return this.getSegmentComparisons().concat(this.getPeriodComparisons());
    }
  }, {
    key: "isComparing",
    value: function isComparing() {
      return this.isComparisonEnabled() // first two in each array are for the currently selected segment/period
      && (this.segmentComparisons.value.length > 1 || this.periodComparisons.value.length > 1);
    }
  }, {
    key: "isComparingPeriods",
    value: function isComparingPeriods() {
      return this.getPeriodComparisons().length > 1; // first is currently selected period
    }
  }, {
    key: "getSegmentComparisons",
    value: function getSegmentComparisons() {
      if (!this.isComparisonEnabled()) {
        return [];
      }

      return this.segmentComparisons.value;
    }
  }, {
    key: "getPeriodComparisons",
    value: function getPeriodComparisons() {
      if (!this.isComparisonEnabled()) {
        return [];
      }

      return this.periodComparisons.value;
    }
  }, {
    key: "getSeriesColor",
    value: function getSeriesColor(segmentComparison, periodComparison) {
      var metricIndex = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
      var seriesIndex = this.getComparisonSeriesIndex(periodComparison.index, segmentComparison.index) % SERIES_COLOR_COUNT;

      if (metricIndex === 0) {
        return this.colors["series".concat(seriesIndex)];
      }

      var shadeIndex = metricIndex % SERIES_SHADE_COUNT;
      return this.colors["series".concat(seriesIndex, "-shade").concat(shadeIndex)];
    }
  }, {
    key: "getSeriesColorName",
    value: function getSeriesColorName(seriesIndex, metricIndex) {
      var colorName = "series".concat(seriesIndex % SERIES_COLOR_COUNT);

      if (metricIndex > 0) {
        colorName += "-shade".concat(metricIndex % SERIES_SHADE_COUNT);
      }

      return colorName;
    }
  }, {
    key: "isComparisonEnabled",
    value: function isComparisonEnabled() {
      return this.isEnabled.value;
    }
  }, {
    key: "getIndividualComparisonRowIndices",
    value: function getIndividualComparisonRowIndices(seriesIndex) {
      var segmentCount = this.getSegmentComparisons().length;
      var segmentIndex = seriesIndex % segmentCount;
      var periodIndex = Math.floor(seriesIndex / segmentCount);
      return {
        segmentIndex: segmentIndex,
        periodIndex: periodIndex
      };
    }
  }, {
    key: "getComparisonSeriesIndex",
    value: function getComparisonSeriesIndex(periodIndex, segmentIndex) {
      var segmentCount = this.getSegmentComparisons().length;
      return periodIndex * segmentCount + segmentIndex;
    }
  }, {
    key: "getAllComparisonSeries",
    value: function getAllComparisonSeries() {
      var _this2 = this;

      var seriesInfo = [];
      var seriesIndex = 0;
      this.getPeriodComparisons().forEach(function (periodComp) {
        _this2.getSegmentComparisons().forEach(function (segmentComp) {
          seriesInfo.push({
            index: seriesIndex,
            params: Comparisons_store_objectSpread(Comparisons_store_objectSpread({}, segmentComp.params), periodComp.params),
            color: _this2.colors["series".concat(seriesIndex)]
          });
          seriesIndex += 1;
        });
      });
      return seriesInfo;
    }
  }, {
    key: "removeSegmentComparison",
    value: function removeSegmentComparison(index) {
      if (!this.isComparisonEnabled()) {
        throw new Error('Comparison disabled.');
      }

      var newComparisons = Comparisons_store_toConsumableArray(this.segmentComparisons.value);

      newComparisons.splice(index, 1);
      var extraParams = {};

      if (index === 0) {
        extraParams.segment = newComparisons[0].params.segment;
      }

      this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value, extraParams);
    }
  }, {
    key: "addSegmentComparison",
    value: function addSegmentComparison(params) {
      if (!this.isComparisonEnabled()) {
        throw new Error('Comparison disabled.');
      }

      var newComparisons = this.segmentComparisons.value.concat([{
        params: params,
        index: -1,
        title: ''
      }]);
      this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value);
    }
  }, {
    key: "updateQueryParamsFromComparisons",
    value: function updateQueryParamsFromComparisons(segmentComparisons, periodComparisons) {
      var extraParams = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      // get unique segments/periods/dates from new Comparisons
      var compareSegments = {};
      var comparePeriodDatePairs = {};
      var firstSegment = false;
      var firstPeriod = false;
      segmentComparisons.forEach(function (comparison) {
        if (firstSegment) {
          compareSegments[comparison.params.segment] = true;
        } else {
          firstSegment = true;
        }
      });
      periodComparisons.forEach(function (comparison) {
        if (firstPeriod) {
          comparePeriodDatePairs["".concat(comparison.params.period, "|").concat(comparison.params.date)] = true;
        } else {
          firstPeriod = true;
        }
      });
      var comparePeriods = [];
      var compareDates = [];
      Object.keys(comparePeriodDatePairs).forEach(function (pair) {
        var parts = pair.split('|');
        comparePeriods.push(parts[0]);
        compareDates.push(parts[1]);
      });
      var compareParams = {
        compareSegments: Object.keys(compareSegments),
        comparePeriods: comparePeriods,
        compareDates: compareDates
      }; // change the page w/ these new param values

      if (Matomo_Matomo.helper.isAngularRenderingThePage()) {
        var search = src_MatomoUrl_MatomoUrl.hashParsed.value;

        var newSearch = Comparisons_store_objectSpread(Comparisons_store_objectSpread(Comparisons_store_objectSpread({}, search), compareParams), extraParams);

        delete newSearch['compareSegments[]'];
        delete newSearch['comparePeriods[]'];
        delete newSearch['compareDates[]'];

        if (JSON.stringify(newSearch) !== JSON.stringify(search)) {
          src_MatomoUrl_MatomoUrl.updateHash(newSearch);
        }

        return;
      }

      var paramsToRemove = [];
      ['compareSegments', 'comparePeriods', 'compareDates'].forEach(function (name) {
        if (!compareParams[name].length) {
          paramsToRemove.push(name);
        }
      }); // angular is not rendering the page (ie, we are in the embedded dashboard) or we need to change
      // the segment

      var url = src_MatomoUrl_MatomoUrl.stringify(extraParams);
      var strHash = src_MatomoUrl_MatomoUrl.stringify(compareParams);
      window.broadcast.propagateNewPage(url, undefined, strHash, paramsToRemove);
    }
  }, {
    key: "getAllSeriesColors",
    value: function getAllSeriesColors() {
      var ColorManager = Matomo_Matomo.ColorManager;

      if (!ColorManager) {
        return [];
      }

      var seriesColorNames = [];

      for (var i = 0; i < SERIES_COLOR_COUNT; i += 1) {
        seriesColorNames.push("series".concat(i));

        for (var j = 0; j < SERIES_SHADE_COUNT; j += 1) {
          seriesColorNames.push("series".concat(i, "-shade").concat(j));
        }
      }

      return ColorManager.getColors('comparison-series-color', seriesColorNames);
    }
  }, {
    key: "loadComparisonsDisabledFor",
    value: function loadComparisonsDisabledFor() {
      var _this3 = this;

      var matomoModule = src_MatomoUrl_MatomoUrl.parsed.value.module;

      if (matomoModule === 'CoreUpdater' || matomoModule === 'Installation') {
        this.privateState.comparisonsDisabledFor = [];
        return;
      }

      AjaxHelper_AjaxHelper.fetch({
        module: 'API',
        method: 'API.getPagesComparisonsDisabledFor'
      }).then(function (result) {
        _this3.privateState.comparisonsDisabledFor = result;
      });
    }
  }, {
    key: "parseSegmentComparisons",
    value: function parseSegmentComparisons() {
      var availableSegments = Segments_store.state.availableSegments;

      var compareSegments = Comparisons_store_toConsumableArray(wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.compareSegments)); // add base comparisons


      compareSegments.unshift(src_MatomoUrl_MatomoUrl.parsed.value.segment || '');
      var newSegmentComparisons = [];
      compareSegments.forEach(function (segment, idx) {
        var storedSegment;
        availableSegments.forEach(function (s) {
          if (s.definition === segment || s.definition === decodeURIComponent(segment) || decodeURIComponent(s.definition) === segment) {
            storedSegment = s;
          }
        });
        var segmentTitle = storedSegment ? storedSegment.name : translate('General_Unknown');

        if (segment.trim() === '') {
          segmentTitle = translate('SegmentEditor_DefaultAllVisits');
        }

        newSegmentComparisons.push({
          params: {
            segment: segment
          },
          title: Matomo_Matomo.helper.htmlDecode(segmentTitle),
          index: idx
        });
      });
      return newSegmentComparisons;
    }
  }, {
    key: "parsePeriodComparisons",
    value: function parsePeriodComparisons() {
      var comparePeriods = Comparisons_store_toConsumableArray(wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.comparePeriods));

      var compareDates = Comparisons_store_toConsumableArray(wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.compareDates));

      comparePeriods.unshift(src_MatomoUrl_MatomoUrl.parsed.value.period);
      compareDates.unshift(src_MatomoUrl_MatomoUrl.parsed.value.date);
      var newPeriodComparisons = [];

      for (var i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
        var title = void 0;

        try {
          title = Periods_Periods.parse(comparePeriods[i], compareDates[i]).getPrettyString();
        } catch (e) {
          title = translate('General_Error');
        }

        newPeriodComparisons.push({
          params: {
            date: compareDates[i],
            period: comparePeriods[i]
          },
          title: title,
          index: i
        });
      }

      return newPeriodComparisons;
    }
  }, {
    key: "checkEnabledForCurrentPage",
    value: function checkEnabledForCurrentPage() {
      // category/subcategory is not included on top bar pages, so in that case we use module/action
      var category = src_MatomoUrl_MatomoUrl.parsed.value.category || src_MatomoUrl_MatomoUrl.parsed.value.module;
      var subcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory || src_MatomoUrl_MatomoUrl.parsed.value.action;
      var id = "".concat(category, ".").concat(subcategory);
      var isEnabled = this.privateState.comparisonsDisabledFor.indexOf(id) === -1 && this.privateState.comparisonsDisabledFor.indexOf("".concat(category, ".*")) === -1;
      document.documentElement.classList.toggle('comparisonsDisabled', !isEnabled);
      return isEnabled;
    }
  }]);

  return ComparisonsStore;
}();


// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.store.instance.ts

/* harmony default export */ var Comparisons_store_instance = (new Comparisons_store_ComparisonsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=1b8ecdd2

var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_1 = {
  key: 0,
  ref: "root",
  class: "matomo-comparisons"
};
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_2 = {
  class: "comparison-type"
};
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_3 = ["title"];
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_4 = ["href"];
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_5 = ["title"];
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_6 = {
  class: "comparison-period-label"
};
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_7 = ["onClick"];
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_8 = ["title"];
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_9 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  }
};
var Comparisonsvue_type_template_id_1b8ecdd2_hoisted_10 = ["alt"];
function Comparisonsvue_type_template_id_1b8ecdd2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.isComparing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Comparisonsvue_type_template_id_1b8ecdd2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Comparisons')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.segmentComparisons, function (comparison, $index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "comparison card",
      key: comparison.index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_1b8ecdd2_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Segment')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "title",
      title: comparison.title + '<br/>' + decodeURIComponent(comparison.params.segment)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      href: _ctx.getUrlToSegment(comparison.params.segment)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(comparison.title), 9, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_4)], 8, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_3), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periodComparisons, function (periodComparison) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "comparison-period",
        key: periodComparison.index,
        title: _ctx.getComparisonTooltip(comparison, periodComparison)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "comparison-dot",
        style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
          'background-color': _ctx.getSeriesColor(comparison, periodComparison)
        })
      }, null, 4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Comparisonsvue_type_template_id_1b8ecdd2_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(periodComparison.title) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getComparisonPeriodType(periodComparison)) + ") ", 1)], 8, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_5);
    }), 128)), _ctx.segmentComparisons.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "remove-button",
      onClick: function onClick($event) {
        return _ctx.removeSegmentComparison($index);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon icon-close",
      title: _ctx.translate('General_ClickToRemoveComp')
    }, null, 8, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_8)], 8, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_1b8ecdd2_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, Comparisonsvue_type_template_id_1b8ecdd2_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=1b8ecdd2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
function Comparisonsvue_type_script_lang_ts_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function Comparisonsvue_type_script_lang_ts_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { Comparisonsvue_type_script_lang_ts_ownKeys(Object(source), true).forEach(function (key) { Comparisonsvue_type_script_lang_ts_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { Comparisonsvue_type_script_lang_ts_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function Comparisonsvue_type_script_lang_ts_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }







/* harmony default export */ var Comparisonsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {},
  data: function data() {
    return {
      comparisonTooltips: null
    };
  },
  setup: function setup() {
    // accessing has to be done through a computed property so we can use the computed
    // instance directly in the template. unfortunately, vue won't register to changes.
    var isComparing = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Comparisons_store_instance.isComparing();
    });
    var segmentComparisons = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Comparisons_store_instance.getSegmentComparisons();
    });
    var periodComparisons = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Comparisons_store_instance.getPeriodComparisons();
    });
    var getSeriesColor = Comparisons_store_instance.getSeriesColor.bind(Comparisons_store_instance);
    return {
      isComparing: isComparing,
      segmentComparisons: segmentComparisons,
      periodComparisons: periodComparisons,
      getSeriesColor: getSeriesColor
    };
  },
  methods: {
    comparisonHasSegment: function comparisonHasSegment(comparison) {
      return typeof comparison.params.segment !== 'undefined';
    },
    removeSegmentComparison: function removeSegmentComparison(index) {
      // otherwise the tooltip will be stuck on the screen
      window.$(this.$refs.root).tooltip('destroy');
      Comparisons_store_instance.removeSegmentComparison(index);
    },
    getComparisonPeriodType: function getComparisonPeriodType(comparison) {
      var period = comparison.params.period;

      if (period === 'range') {
        return translate('CoreHome_PeriodRange');
      }

      var periodStr = translate("Intl_Period".concat(period.substring(0, 1).toUpperCase()).concat(period.substring(1)));
      return periodStr.substring(0, 1).toUpperCase() + periodStr.substring(1);
    },
    getComparisonTooltip: function getComparisonTooltip(segmentComparison, periodComparison) {
      if (!this.comparisonTooltips || !Object.keys(this.comparisonTooltips).length) {
        return undefined;
      }

      return (this.comparisonTooltips[periodComparison.index] || {})[segmentComparison.index];
    },
    getUrlToSegment: function getUrlToSegment(segment) {
      var hash = Comparisonsvue_type_script_lang_ts_objectSpread({}, src_MatomoUrl_MatomoUrl.hashParsed.value);

      delete hash.comparePeriods;
      delete hash.compareDates;
      delete hash.compareSegments;
      hash.segment = segment;
      return "".concat(window.location.search, "#?").concat(src_MatomoUrl_MatomoUrl.stringify(hash));
    },
    setUpTooltips: function setUpTooltips() {
      var _window = window,
          $ = _window.$;
      $(this.$refs.root).tooltip({
        track: true,
        content: function transformTooltipContent() {
          var title = $(this).attr('title');
          return window.vueSanitize(title.replace(/\n/g, '<br />'));
        },
        show: {
          delay: 200,
          duration: 200
        },
        hide: false
      });
    },
    onComparisonsChanged: function onComparisonsChanged() {
      var _this = this;

      this.comparisonTooltips = null;

      if (!Comparisons_store_instance.isComparing()) {
        return;
      }

      var periodComparisons = Comparisons_store_instance.getPeriodComparisons();
      var segmentComparisons = Comparisons_store_instance.getSegmentComparisons();
      AjaxHelper_AjaxHelper.fetch({
        method: 'API.getProcessedReport',
        apiModule: 'VisitsSummary',
        apiAction: 'get',
        compare: '1',
        compareSegments: src_MatomoUrl_MatomoUrl.getSearchParam('compareSegments'),
        comparePeriods: src_MatomoUrl_MatomoUrl.getSearchParam('comparePeriods'),
        compareDates: src_MatomoUrl_MatomoUrl.getSearchParam('compareDates'),
        format_metrics: '1'
      }).then(function (report) {
        _this.comparisonTooltips = {};
        periodComparisons.forEach(function (periodComp) {
          _this.comparisonTooltips[periodComp.index] = {};
          segmentComparisons.forEach(function (segmentComp) {
            var tooltip = _this.generateComparisonTooltip(report, periodComp, segmentComp);

            _this.comparisonTooltips[periodComp.index][segmentComp.index] = tooltip;
          });
        });
      });
    },
    generateComparisonTooltip: function generateComparisonTooltip(visitsSummary, periodComp, segmentComp) {
      if (!visitsSummary.reportData.comparisons) {
        // sanity check
        return '';
      }

      var firstRowIndex = Comparisons_store_instance.getComparisonSeriesIndex(periodComp.index, 0);
      var firstRow = visitsSummary.reportData.comparisons[firstRowIndex];
      var comparisonRowIndex = Comparisons_store_instance.getComparisonSeriesIndex(periodComp.index, segmentComp.index);
      var comparisonRow = visitsSummary.reportData.comparisons[comparisonRowIndex];
      var firstPeriodRow = visitsSummary.reportData.comparisons[segmentComp.index];
      var tooltip = '<div class="comparison-card-tooltip">';
      var visitsPercent = (comparisonRow.nb_visits / firstRow.nb_visits * 100).toFixed(2);
      visitsPercent = "".concat(visitsPercent, "%");
      tooltip += translate('General_ComparisonCardTooltip1', ["'".concat(comparisonRow.compareSegmentPretty, "'"), comparisonRow.comparePeriodPretty, visitsPercent, comparisonRow.nb_visits.toString(), firstRow.nb_visits.toString()]);

      if (periodComp.index > 0) {
        tooltip += '<br/><br/>';
        tooltip += translate('General_ComparisonCardTooltip2', [comparisonRow.nb_visits_change.toString(), firstPeriodRow.compareSegmentPretty, firstPeriodRow.comparePeriodPretty]);
      }

      tooltip += '</div>';
      return tooltip;
    }
  },
  updated: function updated() {
    var _this2 = this;

    setTimeout(function () {
      return _this2.setUpTooltips();
    });
  },
  mounted: function mounted() {
    var _this3 = this;

    Matomo_Matomo.on('piwikComparisonsChanged', function () {
      _this3.onComparisonsChanged();
    });
    this.onComparisonsChanged();
    setTimeout(function () {
      return _this3.setUpTooltips();
    });
  },
  beforeUnmount: function beforeUnmount() {
    try {
      window.$(this.refs.root).tooltip('destroy');
    } catch (e) {// ignore
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue



Comparisonsvue_type_script_lang_ts.render = Comparisonsvue_type_template_id_1b8ecdd2_render

/* harmony default export */ var Comparisons = (Comparisonsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




function ComparisonFactory() {
  return Comparisons_store_instance;
}

ComparisonFactory.$inject = [];
angular.module('piwikApp.service').factory('piwikComparisonsService', ComparisonFactory);
/* harmony default export */ var Comparisons_adapter = (createAngularJsAdapter({
  component: Comparisons,
  directiveName: 'piwikComparisons',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=template&id=0349d645

var Menudropdownvue_type_template_id_0349d645_hoisted_1 = {
  ref: "root",
  class: "menuDropdown"
};
var Menudropdownvue_type_template_id_0349d645_hoisted_2 = ["title"];
var Menudropdownvue_type_template_id_0349d645_hoisted_3 = ["innerHTML"];

var Menudropdownvue_type_template_id_0349d645_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-arrow-bottom"
}, null, -1);

var Menudropdownvue_type_template_id_0349d645_hoisted_5 = {
  class: "items"
};
var Menudropdownvue_type_template_id_0349d645_hoisted_6 = {
  key: 0,
  class: "search"
};
var Menudropdownvue_type_template_id_0349d645_hoisted_7 = ["placeholder"];
var Menudropdownvue_type_template_id_0349d645_hoisted_8 = ["title"];
var Menudropdownvue_type_template_id_0349d645_hoisted_9 = ["title"];
function Menudropdownvue_type_template_id_0349d645_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Menudropdownvue_type_template_id_0349d645_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "title",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showItems = !_ctx.showItems;
    }),
    title: _ctx.tooltip
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(this.actualMenuTitle)
  }, null, 8, Menudropdownvue_type_template_id_0349d645_hoisted_3), Menudropdownvue_type_template_id_0349d645_hoisted_4], 8, Menudropdownvue_type_template_id_0349d645_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Menudropdownvue_type_template_id_0349d645_hoisted_5, [_ctx.showSearch && _ctx.showItems ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Menudropdownvue_type_template_id_0349d645_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    onKeydown: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onSearchTermKeydown($event);
    }),
    placeholder: _ctx.translate('General_Search')
  }, null, 40, Menudropdownvue_type_template_id_0349d645_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {}, _ctx.showItems]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    class: "search_ico",
    src: "plugins/Morpheus/images/search_ico.png",
    title: _ctx.translate('General_Search')
  }, null, 8, Menudropdownvue_type_template_id_0349d645_hoisted_8), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[3] || (_cache[3] = function ($event) {
      _ctx.searchTerm = '';

      _ctx.searchItems('');
    }),
    class: "reset",
    src: "plugins/CoreHome/images/reset_search.png",
    title: _ctx.translate('General_Clear')
  }, null, 8, Menudropdownvue_type_template_id_0349d645_hoisted_9), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.selectItem($event);
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showItems]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.lostFocus
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=template&id=0349d645

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=script&lang=ts



var Menudropdownvue_type_script_lang_ts_window = window,
    Menudropdownvue_type_script_lang_ts_$ = Menudropdownvue_type_script_lang_ts_window.$;
/* harmony default export */ var Menudropdownvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    menuTitle: String,
    tooltip: String,
    showSearch: Boolean,
    menuTitleChangeOnClick: String
  },
  directives: {
    FocusAnywhereButHere: FocusAnywhereButHere,
    FocusIf: FocusIf
  },
  emits: ['afterSelect'],
  watch: {
    menuTitle: function menuTitle() {
      this.actualMenuTitle = this.menuTitle;
    }
  },
  data: function data() {
    return {
      showItems: false,
      searchTerm: '',
      actualMenuTitle: this.menuTitle
    };
  },
  methods: {
    lostFocus: function lostFocus() {
      this.showItems = false;
    },
    selectItem: function selectItem(event) {
      var targetClasses = event.target.classList;

      if (!targetClasses.contains('item') || targetClasses.contains('disabled') || targetClasses.contains('separator')) {
        return;
      }

      if (this.menuTitleChangeOnClick !== false) {
        this.actualMenuTitle = event.target.textContent.replace(/[\u0000-\u2666]/g, function (c) {
          return "&#".concat(c.charCodeAt(0), ";");
        }); // eslint-disable-line
      }

      this.showItems = false;
      Menudropdownvue_type_script_lang_ts_$(this.$slots.default()).find('.item').removeClass('active');
      targetClasses.add('active');
      this.$emit('afterSelect');
    },
    onSearchTermKeydown: function onSearchTermKeydown() {
      var _this = this;

      setTimeout(function () {
        _this.searchItems(_this.searchTerm);
      });
    },
    searchItems: function searchItems(unprocessedSearchTerm) {
      var searchTerm = unprocessedSearchTerm.toLowerCase();
      Menudropdownvue_type_script_lang_ts_$(this.$refs.root).find('.item').each(function (index, node) {
        var $node = Menudropdownvue_type_script_lang_ts_$(node);

        if ($node.text().toLowerCase().indexOf(searchTerm) === -1) {
          $node.hide();
        } else {
          $node.show();
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue



Menudropdownvue_type_script_lang_ts.render = Menudropdownvue_type_template_id_0349d645_render

/* harmony default export */ var Menudropdown = (Menudropdownvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Menudropdown_adapter = (createAngularJsAdapter({
  component: Menudropdown,
  scope: {
    menuTitle: {
      angularJsBind: '@'
    },
    tooltip: {
      angularJsBind: '@'
    },
    showSearch: {
      angularJsBind: '='
    },
    menuTitleChangeOnClick: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikMenudropdown',
  transclude: true,
  events: {
    'after-select': function afterSelect($event, scope) {
      setTimeout(function () {
        scope.$apply();
      }, 0);
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=c8c462d2

var DatePickervue_type_template_id_c8c462d2_hoisted_1 = {
  ref: "root"
};
function DatePickervue_type_template_id_c8c462d2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DatePickervue_type_template_id_c8c462d2_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=c8c462d2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts
function DatePickervue_type_script_lang_ts_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function DatePickervue_type_script_lang_ts_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { DatePickervue_type_script_lang_ts_ownKeys(Object(source), true).forEach(function (key) { DatePickervue_type_script_lang_ts_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { DatePickervue_type_script_lang_ts_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function DatePickervue_type_script_lang_ts_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }




var DEFAULT_STEP_MONTHS = 1;
var DatePickervue_type_script_lang_ts_window = window,
    DatePickervue_type_script_lang_ts_$ = DatePickervue_type_script_lang_ts_window.$;
/* harmony default export */ var DatePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    selectedDateStart: Date,
    selectedDateEnd: Date,
    highlightedDateStart: Date,
    highlightedDateEnd: Date,
    viewDate: [String, Date],
    stepMonths: Number,
    disableMonthDropdown: Boolean,
    options: Object
  },
  emits: ['cellHover', 'cellHoverLeave', 'dateSelect'],
  setup: function setup(props, context) {
    var root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    function setDateCellColor($dateCell, dateValue) {
      var $dateCellLink = $dateCell.children('a');

      if (props.selectedDateStart && props.selectedDateEnd && dateValue >= props.selectedDateStart && dateValue <= props.selectedDateEnd) {
        $dateCell.addClass('ui-datepicker-current-period');
      } else {
        $dateCell.removeClass('ui-datepicker-current-period');
      }

      if (props.highlightedDateStart && props.highlightedDateEnd && dateValue >= props.highlightedDateStart && dateValue <= props.highlightedDateEnd) {
        // other-month cells don't have links, so the <td> must have the ui-state-hover class
        var elementToAddClassTo = $dateCellLink.length ? $dateCellLink : $dateCell;
        elementToAddClassTo.addClass('ui-state-hover');
      } else {
        $dateCell.removeClass('ui-state-hover');
        $dateCellLink.removeClass('ui-state-hover');
      }
    }

    function getCellDate($dateCell, month, year) {
      if ($dateCell.hasClass('ui-datepicker-other-month')) {
        return getOtherMonthDate($dateCell, month, year); // eslint-disable-line
      }

      var day = parseInt($dateCell.children('a,span').text(), 10);
      return new Date(year, month, day);
    }

    function getOtherMonthDate($dateCell, month, year) {
      var date;
      var $row = $dateCell.parent();
      var $rowCells = $row.children('td'); // if in the first row, the date cell is before the current month

      if ($row.is(':first-child')) {
        var $firstDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').first();
        date = getCellDate($firstDateInMonth, month, year);
        date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
        return date;
      } // the date cell is after the current month


      var $lastDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').last();
      date = getCellDate($lastDateInMonth, month, year);
      date.setDate(date.getDate() + $rowCells.index($dateCell) - $rowCells.index($lastDateInMonth));
      return date;
    }

    function getMonthYearDisplayed() {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      var $firstCellWithMonth = element.find('td[data-month]');
      var month = parseInt($firstCellWithMonth.attr('data-month'), 10);
      var year = parseInt($firstCellWithMonth.attr('data-year'), 10);
      return [month, year];
    }

    function setDatePickerCellColors() {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      var $calendarTable = element.find('.ui-datepicker-calendar');
      var monthYear = getMonthYearDisplayed(); // highlight the rest of the cells by first getting the date for the first cell
      // in the calendar, then just incrementing by one for the rest of the cells.

      var $cells = $calendarTable.find('td');
      var $firstDateCell = $cells.first();
      var currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);
      $cells.each(function setCellColor() {
        setDateCellColor(DatePickervue_type_script_lang_ts_$(this), currentDate);
        currentDate.setDate(currentDate.getDate() + 1);
      });
    }

    function viewDateChanged() {
      var date = props.viewDate;

      if (!date) {
        return false;
      }

      if (!(date instanceof Date)) {
        try {
          date = parseDate(date);
        } catch (e) {
          return false;
        }
      }

      var element = DatePickervue_type_script_lang_ts_$(root.value); // only change the datepicker date if the date is outside of the current month/year.
      // this avoids a re-render in other cases.

      var monthYear = getMonthYearDisplayed();

      if (monthYear[0] !== date.getMonth() || monthYear[1] !== date.getFullYear()) {
        element.datepicker('setDate', date);
        return true;
      }

      return false;
    } // remove the ui-state-active class & click handlers for every cell. we bypass
    // the datepicker's date selection logic for smoother browser rendering.


    function onJqueryUiRenderedPicker() {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      element.find('td[data-event]').off('click');
      element.find('.ui-state-active').removeClass('ui-state-active');
      element.find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day'); // add href to left/right nav in calendar so they can be accessed via keyboard

      element.find('.ui-datepicker-prev,.ui-datepicker-next').attr('href', '');
    }

    function stepMonthsChanged() {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      var stepMonths = props.stepMonths || DEFAULT_STEP_MONTHS;

      if (element.datepicker('option', 'stepMonths') === stepMonths) {
        return false;
      } // setting stepMonths will change the month in view back to the selected date. to avoid
      // we set the selected date to the month in view.


      var currentMonth = DatePickervue_type_script_lang_ts_$('.ui-datepicker-month', element).val();
      var currentYear = DatePickervue_type_script_lang_ts_$('.ui-datepicker-year', element).val();
      element.datepicker('option', 'stepMonths', stepMonths).datepicker('setDate', new Date(currentYear, currentMonth));
      onJqueryUiRenderedPicker();
      return true;
    }

    function enableDisableMonthDropdown() {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      element.find('.ui-datepicker-month').attr('disabled', props.disableMonthDropdown);
    }

    function handleOtherMonthClick() {
      if (!DatePickervue_type_script_lang_ts_$(this).hasClass('ui-state-hover')) {
        return;
      }

      var $row = DatePickervue_type_script_lang_ts_$(this).parent();
      var $tbody = $row.parent();

      if ($row.is(':first-child')) {
        // click on first of the month
        $tbody.find('a').first().click();
      } else {
        // click on last of month
        $tbody.find('a').last().click();
      }
    }

    function onCalendarViewChange() {
      // clicking left/right re-enables the month dropdown, so we disable it again
      enableDisableMonthDropdown();
      setDatePickerCellColors();
    } // on a prop change (NOTE: we can't watch just `props`, since then newProps and oldProps will
    // have the same values (since it is a proxy object). Using a copy doesn't quite work, the
    // object it returns will always be different, BUT, since we check what changes it works
    // for our purposes. The only downside is that it runs on every tick basically, but since
    // that is within the context of the date picker component, it's bearable.


    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return DatePickervue_type_script_lang_ts_objectSpread({}, props);
    }, function (newProps, oldProps) {
      var redraw = false;
      ['selectedDateStart', 'selectedDateEnd', 'highlightedDateStart', 'highlightedDateEnd'].forEach(function (propName) {
        if (redraw) {
          return;
        }

        if (!newProps[propName] && oldProps[propName]) {
          redraw = true;
        }

        if (newProps[propName] && !oldProps[propName]) {
          redraw = true;
        }

        if (newProps[propName] && oldProps[propName] && newProps[propName].getTime() !== oldProps[propName].getTime()) {
          redraw = true;
        }
      });

      if (newProps.viewDate !== oldProps.viewDate && viewDateChanged()) {
        redraw = true;
      }

      if (newProps.stepMonths !== oldProps.stepMonths) {
        stepMonthsChanged();
      }

      if (newProps.enableDisableMonthDropdown !== oldProps.enableDisableMonthDropdown) {
        enableDisableMonthDropdown();
      } // redraw when selected/highlighted dates change


      if (redraw) {
        setDatePickerCellColors();
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      var customOptions = props.options || {};

      var datePickerOptions = DatePickervue_type_script_lang_ts_objectSpread(DatePickervue_type_script_lang_ts_objectSpread(DatePickervue_type_script_lang_ts_objectSpread({}, Matomo_Matomo.getBaseDatePickerOptions()), customOptions), {}, {
        onChangeMonthYear: function onChangeMonthYear() {
          // datepicker renders the HTML after this hook is called, so we use setTimeout
          // to run some code after the render.
          setTimeout(function () {
            onJqueryUiRenderedPicker();
          });
        }
      });

      element.datepicker(datePickerOptions);
      element.on('mouseover', 'tbody td a', function (event) {
        // this event is triggered when a user clicks a date as well. in that case,
        // the originalEvent is null. we don't need to redraw again for that, so
        // we ignore events like that.
        if (event.originalEvent) {
          setDatePickerCellColors();
        }
      }); // on hover cell, execute scope.cellHover()

      element.on('mouseenter', 'tbody td', function onMouseEnter() {
        var monthYear = getMonthYearDisplayed();
        var $dateCell = DatePickervue_type_script_lang_ts_$(this);
        var dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
        context.emit('cellHover', {
          date: dateValue,
          $cell: $dateCell
        });
      }); // overrides jquery UI handler that unhighlights a cell when the mouse leaves it

      element.on('mouseout', 'tbody td a', function () {
        setDatePickerCellColors();
      }); // call scope.cellHoverLeave() when mouse leaves table body (can't do event on tbody, for
      // some reason that fails, so we do two events, one on the table & one on thead)

      element.on('mouseleave', 'table', function () {
        return context.emit('cellHoverLeave');
      }).on('mouseenter', 'thead', function () {
        return context.emit('cellHoverLeave');
      }); // make sure whitespace is clickable when the period makes it appropriate

      element.on('click', 'tbody td.ui-datepicker-other-month', function () {
        return handleOtherMonthClick();
      }); // NOTE: using a selector w/ .on() doesn't seem to work for some reason...

      element.on('click', function (e) {
        e.preventDefault();
        var $target = DatePickervue_type_script_lang_ts_$(e.target).closest('a');

        if (!$target.is('.ui-datepicker-next') && !$target.is('.ui-datepicker-prev')) {
          return;
        }

        onCalendarViewChange();
      }); // when a cell is clicked, invoke the onDateSelected function. this, in conjunction
      // with onJqueryUiRenderedPicker(), overrides the date picker's click behavior.

      element.on('click', 'td[data-month]', function (event) {
        var $cell = DatePickervue_type_script_lang_ts_$(event.target).closest('td');
        var month = parseInt($cell.attr('data-month'), 10);
        var year = parseInt($cell.attr('data-year'), 10);
        var day = parseInt($cell.children('a,span').text(), 10);
        context.emit('dateSelect', {
          date: new Date(year, month, day)
        });
      });
      var renderPostProcessed = stepMonthsChanged();
      viewDateChanged();
      enableDisableMonthDropdown();

      if (!renderPostProcessed) {
        onJqueryUiRenderedPicker();
      }

      setDatePickerCellColors();
    });
    return {
      root: root
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue



DatePickervue_type_script_lang_ts.render = DatePickervue_type_template_id_c8c462d2_render

/* harmony default export */ var DatePicker = (DatePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var DatePicker_adapter = (createAngularJsAdapter({
  component: DatePicker,
  scope: {
    selectedDateStart: {
      angularJsBind: '<'
    },
    selectedDateEnd: {
      angularJsBind: '<'
    },
    highlightedDateStart: {
      angularJsBind: '<'
    },
    highlightedDateEnd: {
      angularJsBind: '<'
    },
    viewDate: {
      angularJsBind: '<'
    },
    stepMonths: {
      angularJsBind: '<'
    },
    disableMonthDropdown: {
      angularJsBind: '<'
    },
    options: {
      angularJsBind: '<'
    },
    cellHover: {
      angularJsBind: '&'
    },
    cellHoverLeave: {
      angularJsBind: '&'
    },
    dateSelect: {
      angularJsBind: '&'
    }
  },
  directiveName: 'piwikDatePicker',
  events: {
    'cell-hover': function cellHover(event, scope, element, attrs, $timeout) {
      $timeout(); // trigger new digest
    },
    'cell-hover-leave': function cellHoverLeave(event, scope, element, attrs, $timeout) {
      $timeout(); // trigger new digest
    },
    'date-select': function dateSelect(event, scope, element, attrs, $timeout) {
      $timeout(); // trigger new digest
    }
  },
  $inject: ['$timeout']
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=d9f4b538

var DateRangePickervue_type_template_id_d9f4b538_hoisted_1 = {
  id: "calendarRangeFrom"
};
var DateRangePickervue_type_template_id_d9f4b538_hoisted_2 = {
  id: "calendarRangeTo"
};
function DateRangePickervue_type_template_id_d9f4b538_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_d9f4b538_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeFrom')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarFrom",
    name: "inputCalendarFrom",
    class: "browser-default",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.startDateText = $event;
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onRangeInputChanged('from', $event);
    }),
    onKeyup: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.handleEnterPress($event);
    })
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.startDateText]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DatePicker, {
    id: "calendarFrom",
    "view-date": _ctx.startDate,
    "selected-date-start": _ctx.fromPickerSelectedDates[0],
    "selected-date-end": _ctx.fromPickerSelectedDates[1],
    "highlighted-date-start": _ctx.fromPickerHighlightedDates[0],
    "highlighted-date-end": _ctx.fromPickerHighlightedDates[1],
    onDateSelect: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.setStartRangeDate($event.date);
    }),
    onCellHover: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.fromPickerHighlightedDates = _ctx.getNewHighlightedDates($event.date, $event.$cell);
    }),
    onCellHoverLeave: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.fromPickerHighlightedDates = [null, null];
    })
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_d9f4b538_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeTo')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarTo",
    name: "inputCalendarTo",
    class: "browser-default",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.endDateText = $event;
    }),
    onChange: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.onRangeInputChanged('to', $event);
    }),
    onKeyup: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.handleEnterPress($event);
    })
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.endDateText]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DatePicker, {
    id: "calendarTo",
    "view-date": _ctx.endDate,
    "selected-date-start": _ctx.toPickerSelectedDates[0],
    "selected-date-end": _ctx.toPickerSelectedDates[1],
    "highlighted-date-start": _ctx.toPickerHighlightedDates[0],
    "highlighted-date-end": _ctx.toPickerHighlightedDates[1],
    onDateSelect: _cache[9] || (_cache[9] = function ($event) {
      return _ctx.setEndRangeDate($event.date);
    }),
    onCellHover: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.toPickerHighlightedDates = _ctx.getNewHighlightedDates($event.date, $event.$cell);
    }),
    onCellHoverLeave: _cache[11] || (_cache[11] = function ($event) {
      return _ctx.toPickerHighlightedDates = [null, null];
    })
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=d9f4b538

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts



/* harmony default export */ var DateRangePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    startDate: String,
    endDate: String
  },
  components: {
    DatePicker: DatePicker
  },
  data: function data() {
    var startDate = null;

    try {
      startDate = parseDate(this.startDate);
    } catch (e) {// ignore
    }

    var endDate = null;

    try {
      endDate = parseDate(this.endDate);
    } catch (e) {// ignore
    }

    return {
      fromPickerSelectedDates: [startDate, startDate],
      toPickerSelectedDates: [endDate, endDate],
      fromPickerHighlightedDates: [null, null],
      toPickerHighlightedDates: [null, null],
      startDateText: this.startDate,
      endDateText: this.endDate
    };
  },
  emits: ['rangeChange', 'submit'],
  watch: {
    startDate: function startDate() {
      this.startDateText = this.startDate;
      this.setStartRangeDateFromStr(this.startDate);
    },
    endDate: function endDate() {
      this.endDateText = this.endDate;
      this.setEndRangeDateFromStr(this.endDate);
    }
  },
  mounted: function mounted() {
    this.rangeChanged(); // emit with initial range pair
  },
  methods: {
    setStartRangeDate: function setStartRangeDate(date) {
      this.fromPickerSelectedDates = [date, date];
      this.rangeChanged();
    },
    setEndRangeDate: function setEndRangeDate(date) {
      this.toPickerSelectedDates = [date, date];
      this.rangeChanged();
    },
    onRangeInputChanged: function onRangeInputChanged(source, event) {
      if (source === 'from') {
        this.setStartRangeDateFromStr(event.target.value);
      } else {
        this.setEndRangeDateFromStr(event.target.value);
      }
    },
    getNewHighlightedDates: function getNewHighlightedDates(date, $cell) {
      if ($cell.hasClass('ui-datepicker-unselectable')) {
        return null;
      }

      return [date, date];
    },
    handleEnterPress: function handleEnterPress($event) {
      if ($event.keyCode !== 13) {
        return;
      }

      this.$emit('submit', {
        start: this.startDate,
        end: this.endDate
      });
    },
    setStartRangeDateFromStr: function setStartRangeDateFromStr(dateStr) {
      var startDateParsed;

      try {
        startDateParsed = parseDate(dateStr);
      } catch (e) {
        this.startDateText = this.startDate;
      }

      if (startDateParsed) {
        this.fromPickerSelectedDates = [startDateParsed, startDateParsed];
      }

      this.rangeChanged();
    },
    setEndRangeDateFromStr: function setEndRangeDateFromStr(dateStr) {
      var endDateParsed;

      try {
        endDateParsed = parseDate(dateStr);
      } catch (e) {
        this.endDateText = this.endDate;
      }

      if (endDateParsed) {
        this.toPickerSelectedDates = [endDateParsed, endDateParsed];
      }

      this.rangeChanged();
    },
    rangeChanged: function rangeChanged() {
      this.$emit('rangeChange', {
        start: format(this.fromPickerSelectedDates[0]),
        end: format(this.toPickerSelectedDates[0])
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue



DateRangePickervue_type_script_lang_ts.render = DateRangePickervue_type_template_id_d9f4b538_render

/* harmony default export */ var DateRangePicker = (DateRangePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var DateRangePicker_adapter = (createAngularJsAdapter({
  component: DateRangePicker,
  scope: {
    startDate: {
      angularJsBind: '<'
    },
    endDate: {
      angularJsBind: '<'
    },
    rangeChange: {
      angularJsBind: '&'
    },
    submit: {
      angularJsBind: '&'
    }
  },
  directiveName: 'piwikDateRangePicker',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=effd17b0

function PeriodDatePickervue_type_template_id_effd17b0_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DatePicker, {
    "selected-date-start": _ctx.selectedDates[0],
    "selected-date-end": _ctx.selectedDates[1],
    "highlighted-date-start": _ctx.highlightedDates[0],
    "highlighted-date-end": _ctx.highlightedDates[1],
    "view-date": _ctx.viewDate,
    "step-months": _ctx.period === 'year' ? 12 : 1,
    "disable-month-dropdown": _ctx.period === 'year',
    onCellHover: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onHoverNormalCell($event.date, $event.$cell);
    }),
    onCellHoverLeave: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onHoverLeaveNormalCells();
    }),
    onDateSelect: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onDateSelected($event.date);
    })
  }, null, 8, ["selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end", "view-date", "step-months", "disable-month-dropdown"]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=effd17b0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts




var piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
var piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);
/* harmony default export */ var PeriodDatePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    period: String,
    date: [String, Date]
  },
  components: {
    DatePicker: DatePicker
  },
  emits: ['select'],
  setup: function setup(props, context) {
    var viewDate = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.date);
    var selectedDates = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([null, null]);
    var highlightedDates = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([null, null]);

    function getBoundedDateRange(date) {
      var dates = Periods_Periods.get(props.period).parse(date).getDateRange(); // make sure highlighted date range is within min/max date range

      dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;
      return dates;
    }

    function onHoverNormalCell(cellDate, $cell) {
      var isOutOfMinMaxDateRange = cellDate < piwikMinDate || cellDate > piwikMaxDate; // don't highlight anything if the period is month or day, and we're hovering over calendar
      // whitespace. since there are no dates, it's doesn't make sense what you're selecting.

      var shouldNotHighlightFromWhitespace = $cell.hasClass('ui-datepicker-other-month') && (props.period === 'month' || props.period === 'day');

      if (isOutOfMinMaxDateRange || shouldNotHighlightFromWhitespace) {
        highlightedDates.value = [null, null];
        return;
      }

      highlightedDates.value = getBoundedDateRange(cellDate);
    }

    function onHoverLeaveNormalCells() {
      highlightedDates.value = [null, null];
    }

    function onDateSelected(date) {
      context.emit('select', {
        date: date
      });
    }

    function onChanges() {
      if (!props.period || !props.date) {
        selectedDates.value = [null, null];
        viewDate.value = null;
        return;
      }

      selectedDates.value = getBoundedDateRange(props.date);
      viewDate.value = parseDate(props.date);
    }

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(props, onChanges);
    onChanges();
    return {
      selectedDates: selectedDates,
      highlightedDates: highlightedDates,
      viewDate: viewDate,
      onHoverNormalCell: onHoverNormalCell,
      onHoverLeaveNormalCells: onHoverLeaveNormalCells,
      onDateSelected: onDateSelected
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue



PeriodDatePickervue_type_script_lang_ts.render = PeriodDatePickervue_type_template_id_effd17b0_render

/* harmony default export */ var PeriodDatePicker = (PeriodDatePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var PeriodDatePicker_adapter = (createAngularJsAdapter({
  component: PeriodDatePicker,
  scope: {
    period: {
      angularJsBind: '<'
    },
    date: {
      angularJsBind: '<'
    },
    select: {
      angularJsBind: '&'
    }
  },
  directiveName: 'piwikPeriodDatePicker',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=6af4d064

var ActivityIndicatorvue_type_template_id_6af4d064_hoisted_1 = {
  class: "loadingPiwik"
};

var ActivityIndicatorvue_type_template_id_6af4d064_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  alt: ""
}, null, -1);

function ActivityIndicatorvue_type_template_id_6af4d064_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ActivityIndicatorvue_type_template_id_6af4d064_hoisted_1, [ActivityIndicatorvue_type_template_id_6af4d064_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loading]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=6af4d064

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts


/* harmony default export */ var ActivityIndicatorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    loading: {
      type: Boolean,
      required: true,
      default: false
    },
    loadingMessage: {
      type: String,
      required: false,
      default: translate('General_LoadingData')
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue



ActivityIndicatorvue_type_script_lang_ts.render = ActivityIndicatorvue_type_template_id_6af4d064_render

/* harmony default export */ var ActivityIndicator = (ActivityIndicatorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/* harmony default export */ var ActivityIndicator_adapter = (createAngularJsAdapter({
  component: ActivityIndicator,
  scope: {
    loading: {
      vue: 'loading',
      angularJsBind: '<'
    },
    loadingMessage: {
      vue: 'loadingMessage',
      angularJsBind: '<',
      default: function _default() {
        return translate('General_LoadingData');
      }
    }
  },
  $inject: [],
  directiveName: 'piwikActivityIndicator'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c3863ae2
function Alertvue_type_template_id_c3863ae2_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


function Alertvue_type_template_id_c3863ae2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert", Alertvue_type_template_id_c3863ae2_defineProperty({}, "alert-".concat(_ctx.severity), true)])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c3863ae2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts

/* harmony default export */ var Alertvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    severity: {
      type: String,
      required: true
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue



Alertvue_type_script_lang_ts.render = Alertvue_type_template_id_c3863ae2_render

/* harmony default export */ var Alert = (Alertvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Alert_adapter = (createAngularJsAdapter({
  component: Alert,
  scope: {
    severity: {
      vue: 'severity',
      angularJsBind: '@piwikAlert'
    }
  },
  directiveName: 'piwikAlert',
  transclude: true
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/CookieHelper/CookieHelper.ts
/*
 * General utils for managing cookies in Typescript.
 */
// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
function setCookie(name, val, seconds) {
  var date = new Date(); // set default day to 3 days

  if (!seconds) {
    // eslint-disable-next-line no-param-reassign
    seconds = 3 * 24 * 60 * 1000;
  } // Set it expire in n days


  date.setTime(date.getTime() + seconds); // Set it

  document.cookie = "".concat(name, "=").concat(val, "; expires=").concat(date.toUTCString(), "; path=/");
} // eslint-disable-next-line consistent-return,@typescript-eslint/explicit-module-boundary-types

function getCookie(name) {
  var value = "; ".concat(document.cookie);
  var parts = value.split("; ".concat(name, "=")); // if cookie not exist return null
  // eslint-disable-next-line eqeqeq

  if (parts.length == 2) {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    var data = parts.pop().split(';').shift();

    if (typeof data !== 'undefined') {
      return data;
    }
  }

  return null;
} // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types

function deleteCookie(name) {
  var date = new Date(); // Set it expire in -1 days

  date.setTime(date.getTime() + -1 * 24 * 60 * 60 * 1000); // Set it

  document.cookie = "".concat(name, "=; expires=").concat(date.toUTCString(), "; path=/");
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=e3d12348

var Notificationvue_type_template_id_e3d12348_hoisted_1 = {
  key: 0
};
var Notificationvue_type_template_id_e3d12348_hoisted_2 = ["data-notification-instance-id"];
var Notificationvue_type_template_id_e3d12348_hoisted_3 = {
  key: 1
};
var Notificationvue_type_template_id_e3d12348_hoisted_4 = {
  class: "notification-body"
};
var Notificationvue_type_template_id_e3d12348_hoisted_5 = ["innerHTML"];
var Notificationvue_type_template_id_e3d12348_hoisted_6 = {
  key: 1
};
function Notificationvue_type_template_id_e3d12348_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
    name: _ctx.type === 'toast' ? 'slow-fade-out' : undefined,
    onAfterLeave: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.toastClosed();
    })
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [!_ctx.deleted ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_e3d12348_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
        name: _ctx.type === 'toast' ? 'toast-slide-up' : undefined,
        appear: ""
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
            name: _ctx.animate ? 'fade-in' : undefined,
            appear: ""
          }, {
            default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
              return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
                class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["notification system", _ctx.cssClasses]),
                style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.style),
                ref: "root",
                "data-notification-instance-id": _ctx.notificationInstanceId
              }, [_ctx.canClose ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
                key: 0,
                type: "button",
                class: "close",
                "data-dismiss": "alert",
                onClick: _cache[0] || (_cache[0] = function ($event) {
                  return _ctx.closeNotification($event);
                })
              }, " × ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", Notificationvue_type_template_id_e3d12348_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Notificationvue_type_template_id_e3d12348_hoisted_4, [_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
                key: 0,
                innerHTML: _ctx.$sanitize(_ctx.message)
              }, null, 8, Notificationvue_type_template_id_e3d12348_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_e3d12348_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 14, Notificationvue_type_template_id_e3d12348_hoisted_2)];
            }),
            _: 3
          }, 8, ["name"])])];
        }),
        _: 3
      }, 8, ["name"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 3
  }, 8, ["name"]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=e3d12348

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=script&lang=ts


var Notificationvue_type_script_lang_ts_window = window,
    Notificationvue_type_script_lang_ts_$ = Notificationvue_type_script_lang_ts_window.$;
/* harmony default export */ var Notificationvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    notificationId: String,
    // NOTE: notificationId refers to server side ID for notifications stored in the session.
    // this ID is just so it can be selected outside of this component (just for scrolling).
    notificationInstanceId: String,
    title: String,
    context: String,
    type: String,
    noclear: Boolean,
    toastLength: {
      type: Number,
      default: 12 * 1000
    },
    style: [String, Object],
    animate: Boolean,
    message: String,
    cssClass: String
  },
  computed: {
    cssClasses: function cssClasses() {
      var result = {};

      if (this.context) {
        result["notification-".concat(this.context)] = true;
      }

      if (this.cssClass) {
        result[this.cssClass] = true;
      }

      return result;
    },
    canClose: function canClose() {
      if (this.type === 'persistent') {
        // otherwise it is never possible to dismiss the notification
        return true;
      }

      return !this.noclear;
    }
  },
  emits: ['closed'],
  data: function data() {
    return {
      deleted: false
    };
  },
  mounted: function mounted() {
    var _this = this;

    var addToastEvent = function addToastEvent() {
      setTimeout(function () {
        _this.deleted = true;
      }, _this.toastLength);
    };

    if (this.type === 'toast') {
      addToastEvent();
    }

    if (this.style) {
      Notificationvue_type_script_lang_ts_$(this.$refs.root).css(this.style);
    }
  },
  methods: {
    toastClosed: function toastClosed() {
      var _this2 = this;

      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        _this2.$emit('closed');
      });
    },
    closeNotification: function closeNotification(event) {
      var _this3 = this;

      if (this.canClose && event && event.target) {
        this.deleted = true;
        Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
          _this3.$emit('closed');
        });
      }

      this.markNotificationAsRead();
    },
    markNotificationAsRead: function markNotificationAsRead() {
      if (!this.notificationId) {
        return;
      }

      AjaxHelper_AjaxHelper.fetch({
        module: 'CoreHome',
        action: 'markNotificationAsRead'
      }, {
        postParams: {
          notificationId: this.notificationId
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue



Notificationvue_type_script_lang_ts.render = Notificationvue_type_template_id_e3d12348_render

/* harmony default export */ var Notification = (Notificationvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Notification_adapter = (createAngularJsAdapter({
  component: Notification,
  scope: {
    notificationId: {
      angularJsBind: '@?'
    },
    title: {
      angularJsBind: '@?notificationTitle'
    },
    context: {
      angularJsBind: '@?'
    },
    type: {
      angularJsBind: '@?'
    },
    noclear: {
      angularJsBind: '@?',
      transform: function transform(v) {
        return !!v;
      }
    },
    toastLength: {
      angularJsBind: '@?'
    }
  },
  directiveName: 'piwikNotification',
  transclude: true
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notifications.store.ts
function Notifications_store_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function Notifications_store_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { Notifications_store_ownKeys(Object(source), true).forEach(function (key) { Notifications_store_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { Notifications_store_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function Notifications_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Notifications_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Notifications_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) Notifications_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) Notifications_store_defineProperties(Constructor, staticProps); return Constructor; }

function Notifications_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





var Notifications_store_NotificationsStore = /*#__PURE__*/function () {
  function NotificationsStore() {
    Notifications_store_classCallCheck(this, NotificationsStore);

    Notifications_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      notifications: []
    }));

    Notifications_store_defineProperty(this, "nextNotificationId", 0);
  }

  Notifications_store_createClass(NotificationsStore, [{
    key: "state",
    get: function get() {
      return this.privateState;
    }
  }, {
    key: "appendNotification",
    value: function appendNotification(notification) {
      this.checkMessage(notification.message); // remove existing notification before adding

      if (notification.id) {
        this.remove(notification.id);
      }

      this.privateState.notifications.push(notification);
    }
  }, {
    key: "prependNotification",
    value: function prependNotification(notification) {
      this.checkMessage(notification.message); // remove existing notification before adding

      if (notification.id) {
        this.remove(notification.id);
      }

      this.privateState.notifications.unshift(notification);
    }
    /**
     * Removes a previously shown notification having the given notification id.
     */

  }, {
    key: "remove",
    value: function remove(id) {
      this.privateState.notifications = this.privateState.notifications.filter(function (n) {
        return n.id !== id;
      });
    }
  }, {
    key: "parseNotificationDivs",
    value: function parseNotificationDivs() {
      var _this = this;

      var $notificationNodes = $('[data-role="notification"]');
      var notificationsToShow = [];
      $notificationNodes.each(function (index, notificationNode) {
        var $notificationNode = $(notificationNode);
        var attributes = $notificationNode.data();
        var message = $notificationNode.html();

        if (message) {
          notificationsToShow.push(Notifications_store_objectSpread(Notifications_store_objectSpread({}, attributes), {}, {
            message: message,
            animate: false
          }));
        }

        $notificationNodes.remove();
      });
      notificationsToShow.forEach(function (n) {
        return _this.show(n);
      });
    }
  }, {
    key: "clearTransientNotifications",
    value: function clearTransientNotifications() {
      this.privateState.notifications = this.privateState.notifications.filter(function (n) {
        return n.type !== 'transient';
      });
    }
    /**
     * Creates a notification and shows it to the user.
     */

  }, {
    key: "show",
    value: function show(notification) {
      this.checkMessage(notification.message);
      var addMethod = this.appendNotification;
      var notificationPosition = '#notificationContainer';

      if (notification.placeat) {
        notificationPosition = notification.placeat;
      } else {
        // If a modal is open, we want to make sure the error message is visible and therefore
        // show it within the opened modal
        var modalSelector = '.modal.open .modal-content';
        var modal = document.querySelector(modalSelector);

        if (modal) {
          if (!modal.querySelector('#modalNotificationContainer')) {
            window.$(modal).prepend('<div id="modalNotificationContainer"/>');
          }

          notificationPosition = "".concat(modalSelector, " #modalNotificationContainer");
          addMethod = this.prependNotification;
        }
      }

      var group = notification.group || (notificationPosition ? notificationPosition.toString() : '');
      this.initializeNotificationContainer(notificationPosition, group);
      var notificationInstanceId = (this.nextNotificationId += 1).toString();
      addMethod.call(this, Notifications_store_objectSpread(Notifications_store_objectSpread({}, notification), {}, {
        noclear: !!notification.noclear,
        group: group,
        notificationId: notification.id,
        notificationInstanceId: notificationInstanceId,
        type: notification.type || 'transient'
      }));
      return notificationInstanceId;
    }
  }, {
    key: "scrollToNotification",
    value: function scrollToNotification(notificationInstanceId) {
      setTimeout(function () {
        var element = document.querySelector("[data-notification-instance-id='".concat(notificationInstanceId, "']"));

        if (element) {
          Matomo_Matomo.helper.lazyScrollTo(element, 250);
        }
      });
    }
    /**
     * Shows a notification at a certain point with a quick upwards animation.
     */

  }, {
    key: "toast",
    value: function toast(notification) {
      this.checkMessage(notification.message);
      var $placeat = $(notification.placeat);

      if (!$placeat.length) {
        throw new Error('A valid selector is required for the placeat option when using Notification.toast().');
      }

      var toastElement = document.createElement('div');
      toastElement.style.position = 'absolute';
      toastElement.style.top = "".concat($placeat.offset().top, "px");
      toastElement.style.left = "".concat($placeat.offset().left, "px");
      toastElement.style.zIndex = '1000';
      document.body.appendChild(toastElement);
      var app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
        render: function render() {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Notification, Notifications_store_objectSpread(Notifications_store_objectSpread({}, notification), {}, {
            notificationId: notification.id,
            type: 'toast',
            onClosed: function onClosed() {
              app.unmount();
            }
          }));
        }
      });
      app.config.globalProperties.$sanitize = window.vueSanitize;
      app.config.globalProperties.translate = translate;
      app.mount(toastElement);
    }
  }, {
    key: "initializeNotificationContainer",
    value: function initializeNotificationContainer(notificationPosition, group) {
      var $container = window.$(notificationPosition);

      if ($container.children('.notification-group').length) {
        return;
      } // avoiding a dependency cycle. won't need to do this when NotificationGroup's do not need
      // to be dynamically initialized.


      var NotificationGroup = window.CoreHome.NotificationGroup; // eslint-disable-line

      var app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
        template: '<NotificationGroup :group="group"></NotificationGroup>',
        data: function data() {
          return {
            group: group
          };
        }
      });
      app.config.globalProperties.$sanitize = window.vueSanitize;
      app.config.globalProperties.translate = translate;
      app.component('NotificationGroup', NotificationGroup);
      app.mount($container[0]);
    }
  }, {
    key: "checkMessage",
    value: function checkMessage(message) {
      if (!message) {
        throw new Error('No message given, cannot display notification');
      }
    }
  }]);

  return NotificationsStore;
}();

var Notifications_store_instance = new Notifications_store_NotificationsStore();
/* harmony default export */ var Notifications_store = (Notifications_store_instance); // parse notifications on dom load

$(function () {
  return Notifications_store_instance.parseNotificationDivs();
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notifications.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').factory('notifications', function () {
  return Notifications_store;
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=template&id=672051da

var NotificationGroupvue_type_template_id_672051da_hoisted_1 = {
  class: "notification-group"
};
var NotificationGroupvue_type_template_id_672051da_hoisted_2 = ["innerHTML"];
function NotificationGroupvue_type_template_id_672051da_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", NotificationGroupvue_type_template_id_672051da_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.notifications, function (notification, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
      key: notification.id || "no-id-".concat(index),
      "notification-id": notification.id,
      title: notification.title,
      context: notification.context,
      type: notification.type,
      noclear: notification.noclear,
      "toast-length": notification.toastLength,
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(notification.style),
      animate: notification.animate,
      message: notification.message,
      "notification-instance-id": notification.notificationInstanceId,
      "css-class": notification.class,
      onClosed: function onClosed($event) {
        return _ctx.removeNotification(notification.id);
      }
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
        return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
          innerHTML: _ctx.$sanitize(notification.message)
        }, null, 8, NotificationGroupvue_type_template_id_672051da_hoisted_2)];
      }),
      _: 2
    }, 1032, ["notification-id", "title", "context", "type", "noclear", "toast-length", "style", "animate", "message", "notification-instance-id", "css-class", "onClosed"]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=template&id=672051da

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=script&lang=ts



/* harmony default export */ var NotificationGroupvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    group: String
  },
  components: {
    Notification: Notification
  },
  computed: {
    notifications: function notifications() {
      var _this = this;

      return Notifications_store.state.notifications.filter(function (n) {
        if (_this.group) {
          return _this.group === n.group;
        }

        return !n.group;
      });
    }
  },
  methods: {
    removeNotification: function removeNotification(id) {
      Notifications_store.remove(id);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue



NotificationGroupvue_type_script_lang_ts.render = NotificationGroupvue_type_template_id_672051da_render

/* harmony default export */ var Notification_NotificationGroup = (NotificationGroupvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */















































// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=CoreHome.umd.js.map