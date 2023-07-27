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
__webpack_require__.d(__webpack_exports__, "createVueApp", function() { return /* reexport */ createVueApp; });
__webpack_require__.d(__webpack_exports__, "useExternalPluginComponent", function() { return /* reexport */ useExternalPluginComponent; });
__webpack_require__.d(__webpack_exports__, "DirectiveUtilities", function() { return /* reexport */ directiveUtilities; });
__webpack_require__.d(__webpack_exports__, "debounce", function() { return /* reexport */ debounce; });
__webpack_require__.d(__webpack_exports__, "getFormattedEvolution", function() { return /* reexport */ getFormattedEvolution; });
__webpack_require__.d(__webpack_exports__, "createAngularJsAdapter", function() { return /* reexport */ createAngularJsAdapter; });
__webpack_require__.d(__webpack_exports__, "transformAngularJsBoolAttr", function() { return /* reexport */ transformAngularJsBoolAttr; });
__webpack_require__.d(__webpack_exports__, "transformAngularJsIntAttr", function() { return /* reexport */ transformAngularJsIntAttr; });
__webpack_require__.d(__webpack_exports__, "removeAngularJsSpecificProperties", function() { return /* reexport */ removeAngularJsSpecificProperties; });
__webpack_require__.d(__webpack_exports__, "clone", function() { return /* reexport */ clone; });
__webpack_require__.d(__webpack_exports__, "cloneThenApply", function() { return /* reexport */ cloneThenApply; });
__webpack_require__.d(__webpack_exports__, "activityIndicatorAdapter", function() { return /* reexport */ ActivityIndicator_adapter; });
__webpack_require__.d(__webpack_exports__, "ActivityIndicator", function() { return /* reexport */ ActivityIndicator; });
__webpack_require__.d(__webpack_exports__, "translate", function() { return /* reexport */ translate; });
__webpack_require__.d(__webpack_exports__, "translateOrDefault", function() { return /* reexport */ translateOrDefault; });
__webpack_require__.d(__webpack_exports__, "Alert", function() { return /* reexport */ Alert; });
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
__webpack_require__.d(__webpack_exports__, "DropdownMenu", function() { return /* reexport */ DropdownMenu; });
__webpack_require__.d(__webpack_exports__, "FocusAnywhereButHere", function() { return /* reexport */ FocusAnywhereButHere; });
__webpack_require__.d(__webpack_exports__, "FocusIf", function() { return /* reexport */ FocusIf; });
__webpack_require__.d(__webpack_exports__, "Tooltips", function() { return /* reexport */ Tooltips; });
__webpack_require__.d(__webpack_exports__, "MatomoDialog", function() { return /* reexport */ MatomoDialog; });
__webpack_require__.d(__webpack_exports__, "ExpandOnClick", function() { return /* reexport */ ExpandOnClick; });
__webpack_require__.d(__webpack_exports__, "ExpandOnHover", function() { return /* reexport */ ExpandOnHover; });
__webpack_require__.d(__webpack_exports__, "ShowSensitiveData", function() { return /* reexport */ ShowSensitiveData; });
__webpack_require__.d(__webpack_exports__, "DropdownButton", function() { return /* reexport */ DropdownButton; });
__webpack_require__.d(__webpack_exports__, "SelectOnFocus", function() { return /* reexport */ SelectOnFocus; });
__webpack_require__.d(__webpack_exports__, "CopyToClipboard", function() { return /* reexport */ CopyToClipboard; });
__webpack_require__.d(__webpack_exports__, "SideNav", function() { return /* reexport */ SideNav; });
__webpack_require__.d(__webpack_exports__, "EnrichedHeadline", function() { return /* reexport */ EnrichedHeadline; });
__webpack_require__.d(__webpack_exports__, "ContentBlock", function() { return /* reexport */ ContentBlock; });
__webpack_require__.d(__webpack_exports__, "Comparisons", function() { return /* reexport */ Comparisons; });
__webpack_require__.d(__webpack_exports__, "MenuItemsDropdown", function() { return /* reexport */ MenuItemsDropdown; });
__webpack_require__.d(__webpack_exports__, "DatePicker", function() { return /* reexport */ DatePicker; });
__webpack_require__.d(__webpack_exports__, "DateRangePicker", function() { return /* reexport */ DateRangePicker; });
__webpack_require__.d(__webpack_exports__, "PeriodDatePicker", function() { return /* reexport */ PeriodDatePicker; });
__webpack_require__.d(__webpack_exports__, "Notification", function() { return /* reexport */ Notification; });
__webpack_require__.d(__webpack_exports__, "NotificationGroup", function() { return /* reexport */ Notification_NotificationGroup; });
__webpack_require__.d(__webpack_exports__, "NotificationsStore", function() { return /* reexport */ Notifications_store; });
__webpack_require__.d(__webpack_exports__, "SitesStore", function() { return /* reexport */ SiteSelector_SitesStore; });
__webpack_require__.d(__webpack_exports__, "SiteSelector", function() { return /* reexport */ SiteSelector; });
__webpack_require__.d(__webpack_exports__, "QuickAccess", function() { return /* reexport */ QuickAccess; });
__webpack_require__.d(__webpack_exports__, "FieldArray", function() { return /* reexport */ FieldArray; });
__webpack_require__.d(__webpack_exports__, "MultiPairField", function() { return /* reexport */ MultiPairField; });
__webpack_require__.d(__webpack_exports__, "PeriodSelector", function() { return /* reexport */ PeriodSelector; });
__webpack_require__.d(__webpack_exports__, "ReportingMenu", function() { return /* reexport */ ReportingMenu; });
__webpack_require__.d(__webpack_exports__, "ReportingMenuStore", function() { return /* reexport */ ReportingMenu_store; });
__webpack_require__.d(__webpack_exports__, "ReportingPagesStore", function() { return /* reexport */ ReportingPages_store; });
__webpack_require__.d(__webpack_exports__, "ReportMetadataStore", function() { return /* reexport */ ReportMetadata_store; });
__webpack_require__.d(__webpack_exports__, "WidgetsStore", function() { return /* reexport */ Widgets_store; });
__webpack_require__.d(__webpack_exports__, "WidgetLoader", function() { return /* reexport */ WidgetLoader; });
__webpack_require__.d(__webpack_exports__, "WidgetContainer", function() { return /* reexport */ WidgetContainer; });
__webpack_require__.d(__webpack_exports__, "WidgetByDimensionContainer", function() { return /* reexport */ WidgetByDimensionContainer; });
__webpack_require__.d(__webpack_exports__, "Widget", function() { return /* reexport */ Widget_Widget; });
__webpack_require__.d(__webpack_exports__, "ReportingPage", function() { return /* reexport */ ReportingPage; });
__webpack_require__.d(__webpack_exports__, "ReportExport", function() { return /* reexport */ ReportExport; });
__webpack_require__.d(__webpack_exports__, "Sparkline", function() { return /* reexport */ Sparkline; });
__webpack_require__.d(__webpack_exports__, "Progressbar", function() { return /* reexport */ Progressbar; });
__webpack_require__.d(__webpack_exports__, "ContentIntro", function() { return /* reexport */ ContentIntro; });
__webpack_require__.d(__webpack_exports__, "ContentTable", function() { return /* reexport */ ContentTable; });
__webpack_require__.d(__webpack_exports__, "AjaxForm", function() { return /* reexport */ AjaxForm; });

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
    document.title = "".concat(Matomo_piwik.siteName).concat(dateString).concat(originalTitle.slice(Matomo_piwik.siteName.length));
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
  if (!translationStringId) {
    return '';
  }

  for (var _len = arguments.length, values = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    values[_key - 1] = arguments[_key];
  }

  var pkArgs = values; // handle variadic args AND single array of values (to match _pk_translate signature)

  if (values.length === 1 && values[0] && Array.isArray(values[0])) {
    pkArgs = values[0];
  }

  return window._pk_translate(translationStringId, pkArgs); // eslint-disable-line
}
function translateOrDefault(translationStringIdOrText) {
  if (!translationStringIdOrText || !window.piwik_translations[translationStringIdOrText]) {
    return translationStringIdOrText;
  }

  for (var _len2 = arguments.length, values = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
    values[_key2 - 1] = arguments[_key2];
  }

  return translate.apply(void 0, [translationStringIdOrText].concat(values));
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
  }, {
    key: "getDayCount",
    value: function getDayCount() {
      return Math.ceil((this.endDate.getTime() - this.startDate.getTime()) / (1000 * 3600 * 24)) + 1;
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
function MatomoUrl_slicedToArray(arr, i) { return MatomoUrl_arrayWithHoles(arr) || MatomoUrl_iterableToArrayLimit(arr, i) || MatomoUrl_unsupportedIterableToArray(arr, i) || MatomoUrl_nonIterableRest(); }

function MatomoUrl_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function MatomoUrl_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return MatomoUrl_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return MatomoUrl_arrayLikeToArray(o, minLen); }

function MatomoUrl_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function MatomoUrl_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function MatomoUrl_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

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

    MatomoUrl_defineProperty(this, "url", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null));

    MatomoUrl_defineProperty(this, "urlQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.url.value ? _this.url.value.search.replace(/^\?/, '') : '';
    }));

    MatomoUrl_defineProperty(this, "hashQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.url.value ? _this.url.value.hash.replace(/^[#/?]+/, '') : '';
    }));

    MatomoUrl_defineProperty(this, "urlParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.parse(_this.urlQuery.value));
    }));

    MatomoUrl_defineProperty(this, "hashParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.parse(_this.hashQuery.value));
    }));

    MatomoUrl_defineProperty(this, "parsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(Object.assign(Object.assign({}, _this.urlParsed.value), _this.hashParsed.value));
    }));

    this.url.value = new URL(window.location.href); // $locationChangeSuccess is triggered before angularjs changes actual window the hash, so we
    // have to hook into this method if we want our event handlers to execute before other angularjs
    // handlers (like the reporting page one)

    Matomo_Matomo.on('$locationChangeSuccess', function (absUrl) {
      _this.url.value = new URL(absUrl);
    });
    this.updatePeriodParamsFromUrl();
  }

  MatomoUrl_createClass(MatomoUrl, [{
    key: "updateHashToUrl",
    value: function updateHashToUrl(url) {
      var $location = Matomo_Matomo.helper.getAngularDependency('$location');
      $location.url(url);
    }
  }, {
    key: "updateHash",
    value: function updateHash(params) {
      var modifiedParams = this.getFinalHashParams(params);
      var serializedParams = this.stringify(modifiedParams);
      var $location = Matomo_Matomo.helper.getAngularDependency('$location');
      $location.search(serializedParams);
      var $timeout = Matomo_Matomo.helper.getAngularDependency('$timeout');
      $timeout();
    }
  }, {
    key: "updateUrl",
    value: function updateUrl(params) {
      var hashParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var serializedParams = typeof params !== 'string' ? this.stringify(params) : params;
      var modifiedHashParams = Object.keys(hashParams).length ? this.getFinalHashParams(hashParams, params) : {};
      var serializedHashParams = this.stringify(modifiedHashParams);
      var url = "?".concat(serializedParams);

      if (serializedHashParams.length) {
        url = "".concat(url, "#?").concat(serializedHashParams);
      }

      window.broadcast.propagateNewPage('', undefined, undefined, undefined, url);
    }
  }, {
    key: "getFinalHashParams",
    value: function getFinalHashParams(params) {
      var urlParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var paramsObj = typeof params !== 'string' ? params : this.parse(params);
      var urlParamsObj = typeof params !== 'string' ? urlParams : this.parse(urlParams);
      return Object.assign({
        // these params must always be present in the hash
        period: urlParamsObj.period || this.parsed.value.period,
        date: urlParamsObj.date || this.parsed.value.date,
        segment: urlParamsObj.segment || this.parsed.value.segment
      }, paramsObj);
    } // if we're in an embedded context, loads an entire new URL, otherwise updates the hash

  }, {
    key: "updateLocation",
    value: function updateLocation(params) {
      if (Matomo_Matomo.helper.isAngularRenderingThePage()) {
        this.updateHash(params);
        return;
      }

      this.updateUrl(params);
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
    key: "parse",
    value: function parse(query) {
      return MatomoUrl_broadcast.getValuesFromUrl("?".concat(query), true);
    }
  }, {
    key: "stringify",
    value: function stringify(search) {
      var searchWithoutEmpty = Object.fromEntries(Object.entries(search).filter(function (_ref) {
        var _ref2 = MatomoUrl_slicedToArray(_ref, 2),
            value = _ref2[1];

        return value !== '' && value !== null && value !== undefined;
      })); // TODO: using $ since URLSearchParams does not handle array params the way Matomo uses them

      return $.param(searchWithoutEmpty).replace(/%5B%5D/g, '[]') // some browsers treat URLs w/ date=a,b differently from date=a%2Cb, causing multiple
      // entries to show up in the browser history. this has a compounding effect w/ angular.js,
      // which when the back button is pressed to effectively abort the back navigation.
      .replace(/%2C/g, ',') // jquery seems to encode space characters as '+', but certain parts of matomo won't
      // decode it correctly, so we make sure to use %20 instead
      .replace(/\+/g, '%20');
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

window.angular.module('piwikApp.service').service('piwikUrl', piwikUrl); // make sure $location is initialized early

window.angular.module('piwikApp.service').run(['$location', function () {
  return null;
}]);
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
    return this.$oldEmit.apply(this, [name].concat(args)); // eslint-disable-line
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
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function AjaxHelper_toConsumableArray(arr) { return AjaxHelper_arrayWithoutHoles(arr) || AjaxHelper_iterableToArray(arr) || AjaxHelper_unsupportedIterableToArray(arr) || AjaxHelper_nonIterableSpread(); }

function AjaxHelper_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function AjaxHelper_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return AjaxHelper_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return AjaxHelper_arrayLikeToArray(o, minLen); }

function AjaxHelper_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function AjaxHelper_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return AjaxHelper_arrayLikeToArray(arr); }

function AjaxHelper_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function AjaxHelper_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function AjaxHelper_createClass(Constructor, protoProps, staticProps) { if (protoProps) AjaxHelper_defineProperties(Constructor.prototype, protoProps); if (staticProps) AjaxHelper_defineProperties(Constructor, staticProps); return Constructor; }

function AjaxHelper_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function AjaxHelper_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _wrapNativeSuper(Class) { var _cache = typeof Map === "function" ? new Map() : undefined; _wrapNativeSuper = function _wrapNativeSuper(Class) { if (Class === null || !_isNativeFunction(Class)) return Class; if (typeof Class !== "function") { throw new TypeError("Super expression must either be null or a function"); } if (typeof _cache !== "undefined") { if (_cache.has(Class)) return _cache.get(Class); _cache.set(Class, Wrapper); } function Wrapper() { return _construct(Class, arguments, _getPrototypeOf(this).constructor); } Wrapper.prototype = Object.create(Class.prototype, { constructor: { value: Wrapper, enumerable: false, writable: true, configurable: true } }); return _setPrototypeOf(Wrapper, Class); }; return _wrapNativeSuper(Class); }

function _construct(Parent, args, Class) { if (_isNativeReflectConstruct()) { _construct = Reflect.construct; } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _isNativeFunction(fn) { return Function.toString.call(fn).indexOf("[native code]") !== -1; }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

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

var ApiResponseError = /*#__PURE__*/function (_Error) {
  _inherits(ApiResponseError, _Error);

  var _super = _createSuper(ApiResponseError);

  function ApiResponseError() {
    AjaxHelper_classCallCheck(this, ApiResponseError);

    return _super.apply(this, arguments);
  }

  return ApiResponseError;
}( /*#__PURE__*/_wrapNativeSuper(Error));
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

    AjaxHelper_defineProperty(this, "headers", {
      'X-Requested-With': 'XMLHttpRequest'
    });

    AjaxHelper_defineProperty(this, "requestHandle", null);

    AjaxHelper_defineProperty(this, "abortController", null);

    AjaxHelper_defineProperty(this, "defaultParams", ['idSite', 'period', 'date', 'segment']);

    AjaxHelper_defineProperty(this, "resolveWithHelper", false);

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

        if (typeof value === 'boolean') {
          value = value ? 1 : 0;
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
      var $timeout = null;

      try {
        $timeout = Matomo_Matomo.helper.getAngularDependency('$timeout');
      } catch (e) {// ignore
      }

      if (this.abortController) {
        this.abortController.signal.addEventListener('abort', function () {
          if (_this2.requestHandle) {
            _this2.requestHandle.abort();
          }
        });
      }

      var result = new Promise(function (resolve, reject) {
        _this2.requestHandle.then(function (data) {
          if (_this2.resolveWithHelper) {
            // NOTE: we can't resolve w/ the jquery xhr, because it's a promise, and will
            // just result in following the promise chain back to 'data'
            resolve(_this2); // casting hack here
          } else {
            resolve(data); // ignoring textStatus/jqXHR
          }
        }).fail(function (xhr) {
          if (xhr.status === 429) {
            console.log("Warning: the '".concat($.param(_this2.getParams), "' request was rate limited!"));
            reject(xhr);
            return;
          }

          if (xhr.statusText === 'abort') {
            return;
          }

          console.log("Warning: the ".concat($.param(_this2.getParams), " request failed!"));
          reject(xhr);
        }).done(function () {
          if ($timeout) {
            $timeout(); // trigger digest
          }
        });
      });
      return result;
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
        headers: this.headers ? this.headers : undefined,
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

          var results = _this3.postParams.method === 'API.getBulkRequest' && Array.isArray(response) ? response : [response];
          var errors = results.filter(function (r) {
            return r.result === 'error';
          }).map(function (r) {
            return r.message;
          }).filter(function (e) {
            return e.length;
          }) // count occurrences of error messages
          .reduce(function (acc, e) {
            acc[e] = (acc[e] || 0) + 1;
            return acc;
          }, {});

          if (errors && Object.keys(errors).length && !_this3.useRegularCallbackInCaseOfError) {
            var errorMessage = '';
            Object.keys(errors).forEach(function (error) {
              if (errorMessage.length) {
                errorMessage += '<br />';
              } // append error count if it occured more than once


              if (errors[error] > 1) {
                errorMessage += "".concat(error, " (").concat(errors[error], "x)");
              } else {
                errorMessage += error;
              }
            });
            var placeAt = null;
            var type = 'toast';

            if ($(_this3.errorElement).length && errorMessage.length) {
              $(_this3.errorElement).show();
              placeAt = _this3.errorElement;
              type = null;
            }

            var isLoggedIn = !document.querySelector('#login_form');

            if (errorMessage && isLoggedIn) {
              var UI = window['require']('piwik/UI'); // eslint-disable-line

              var notification = new UI.Notification();
              notification.show(errorMessage, {
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
      var mergedParams = Object.assign(Object.assign({}, defaultParams), params);
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
        if (_this4.useGETDefaultParameter(key) && (params[key] === null || typeof params[key] === 'undefined' || params[key] === '') && (_this4.postParams[key] === null || typeof _this4.postParams[key] === 'undefined' || _this4.postParams[key] === '') && defaultParams[key]) {
          params[key] = defaultParams[key];
        }
      }); // handle default date & period if not already set

      if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
        params.date = Matomo_Matomo.currentDateString;
      }

      return params;
    }
  }, {
    key: "getRequestHandle",
    value: function getRequestHandle() {
      return this.requestHandle;
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
     * Extra headers to add to the request.
     */

    /**
     * Handle for current request
     */
    // helper method entry point
    function fetch( // eslint-disable-line
    params) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var helper = new AjaxHelper();

      if (options.withTokenInUrl) {
        helper.withTokenInUrl();
      }

      if (options.errorElement) {
        helper.setErrorElement(options.errorElement);
      }

      if (options.redirectOnSuccess) {
        helper.redirectOnSuccess(options.redirectOnSuccess !== true ? options.redirectOnSuccess : undefined);
      }

      helper.setFormat(options.format || 'json');

      if (Array.isArray(params)) {
        helper.setBulkRequests.apply(helper, AjaxHelper_toConsumableArray(params));
      } else {
        Object.keys(params).forEach(function (key) {
          if (/password/i.test(key)) {
            throw new Error("Password parameters are not allowed to be sent as GET parameter. Please send ".concat(key, " as POST parameter instead."));
          }
        });
        helper.addParams(Object.assign(Object.assign({
          module: 'API',
          format: options.format || 'json'
        }, params), {}, {
          // ajax helper does not encode the segment parameter assuming it is already encoded. this is
          // probably for pre-angularjs code, so we don't want to do this now, but just treat segment
          // as a normal query parameter input (so it will have double encoded values in input params
          // object, then naturally triple encoded in the URL after a $.param call), however we need
          // to support any existing uses of the old code, so instead we do a manual encode here. new
          // code that uses .fetch() will not need to pre-encode the parameter, while old code
          // can pre-encode it.
          segment: params.segment ? encodeURIComponent(params.segment) : undefined
        }), 'get');
      }

      if (options.postParams) {
        helper.addParams(options.postParams, 'post');
      }

      if (options.headers) {
        helper.headers = Object.assign(Object.assign({}, helper.headers), options.headers);
      }

      var createErrorNotification = true;

      if (typeof options.createErrorNotification !== 'undefined' && !options.createErrorNotification) {
        helper.useCallbackInCaseOfError();
        helper.setErrorCallback(null);
        createErrorNotification = false;
      }

      if (options.abortController) {
        helper.abortController = options.abortController;
      }

      if (options.returnResponseObject) {
        helper.resolveWithHelper = true;
      }

      return helper.send().then(function (result) {
        var data = result instanceof AjaxHelper ? result.requestHandle.responseJSON : result; // check for error if not using default notification behavior

        var results = helper.postParams.method === 'API.getBulkRequest' && Array.isArray(data) ? data : [data];
        var errors = results.filter(function (r) {
          return r.result === 'error';
        }).map(function (r) {
          return r.message;
        });

        if (errors.length) {
          throw new ApiResponseError(errors.filter(function (e) {
            return e.length;
          }).join('\n'));
        }

        return result;
      }).catch(function (xhr) {
        if (createErrorNotification || xhr instanceof ApiResponseError) {
          throw xhr;
        }

        var message = 'Something went wrong';

        if (xhr.status === 504) {
          message = 'Request was possibly aborted';
        }

        if (xhr.status === 429) {
          message = 'Rate Limit was exceed';
        }

        throw new Error(message);
      });
    } // eslint-disable-next-line @typescript-eslint/no-explicit-any

  }, {
    key: "post",
    value: function post(params) {
      var postParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      return AjaxHelper.fetch(params, Object.assign(Object.assign({}, options), {}, {
        postParams: postParams
      }));
    } // eslint-disable-next-line @typescript-eslint/no-explicit-any

  }, {
    key: "oneAtATime",
    value: function oneAtATime(method, options) {
      var abortController = null;
      return function (params, postParams) {
        if (abortController) {
          abortController.abort();
        }

        abortController = new AbortController();
        return AjaxHelper.post(Object.assign(Object.assign({}, params), {}, {
          method: method
        }), postParams, Object.assign(Object.assign({}, options), {}, {
          abortController: abortController
        })).finally(function () {
          abortController = null;
        });
      };
    }
  }]);

  return AjaxHelper;
}();


// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts

window.ajaxHelper = AjaxHelper_AjaxHelper;

function ajaxQueue() {
  return window.globalAjaxQueue;
}

window.angular.module('piwikApp.service').service('globalAjaxQueue', ajaxQueue);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PopoverHandler/PopoverHandler.ts
function PopoverHandler_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function PopoverHandler_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function PopoverHandler_createClass(Constructor, protoProps, staticProps) { if (protoProps) PopoverHandler_defineProperties(Constructor.prototype, protoProps); if (staticProps) PopoverHandler_defineProperties(Constructor, staticProps); return Constructor; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


var PopoverHandler_window = window,
    PopoverHandler_$ = PopoverHandler_window.$;

var PopoverHandler_PopoverHandler = /*#__PURE__*/function () {
  function PopoverHandler() {
    PopoverHandler_classCallCheck(this, PopoverHandler);

    this.setup();
  }

  PopoverHandler_createClass(PopoverHandler, [{
    key: "setup",
    value: function setup() {
      var _this = this;

      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
        return src_MatomoUrl_MatomoUrl.parsed.value.popover;
      }, function () {
        return _this.onPopoverParamChanged();
      });

      if (src_MatomoUrl_MatomoUrl.parsed.value.popover) {
        this.onPopoverParamChangedInitial();
      }
    } // don't initiate the handler until the page had a chance to render,
    // since some rowactions depend on what's been loaded.

  }, {
    key: "onPopoverParamChangedInitial",
    value: function onPopoverParamChangedInitial() {
      var _this2 = this;

      PopoverHandler_$(function () {
        setTimeout(function () {
          _this2.openOrClose();
        });
      });
    }
  }, {
    key: "onPopoverParamChanged",
    value: function onPopoverParamChanged() {
      var _this3 = this;

      // make sure all popover handles were registered
      PopoverHandler_$(function () {
        _this3.openOrClose();
      });
    }
  }, {
    key: "openOrClose",
    value: function openOrClose() {
      this.close(); // should be rather done by routing

      var popoverParam = src_MatomoUrl_MatomoUrl.parsed.value.popover;

      if (popoverParam) {
        this.open(popoverParam);
      } else {
        // the URL should only be set to an empty popover if there are no popovers in the stack.
        // to avoid avoid any strange inconsistent states, we reset the popover stack here.
        window.broadcast.resetPopoverStack();
      }
    }
  }, {
    key: "close",
    value: function close() {
      window.Piwik_Popover.close();
    }
  }, {
    key: "open",
    value: function open(thePopoverParam) {
      // in case the $ was encoded (e.g. when using copy&paste on urls in some browsers)
      var popoverParam = decodeURIComponent(thePopoverParam); // revert special encoding from broadcast.propagateNewPopoverParameter()

      popoverParam = popoverParam.replace(/\$/g, '%');
      popoverParam = decodeURIComponent(popoverParam);
      var popoverParamParts = popoverParam.split(':');
      var handlerName = popoverParamParts[0];
      popoverParamParts.shift();
      var param = popoverParamParts.join(':');

      if (typeof window.broadcast.popoverHandlers[handlerName] !== 'undefined' && !window.broadcast.isLoginPage()) {
        window.broadcast.popoverHandlers[handlerName](param);
      }
    }
  }]);

  return PopoverHandler;
}();

/* harmony default export */ var src_PopoverHandler_PopoverHandler = (new PopoverHandler_PopoverHandler());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c3863ae2
function Alertvue_type_template_id_c3863ae2_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


function Alertvue_type_template_id_c3863ae2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert", Alertvue_type_template_id_c3863ae2_defineProperty({}, "alert-".concat(_ctx.severity), true)])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c3863ae2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts

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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/createVueApp.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function createVueApp() {
  var app = external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"].apply(void 0, arguments);
  app.config.globalProperties.$sanitize = window.vueSanitize;
  app.config.globalProperties.translate = translate;
  app.config.globalProperties.translateOrDefault = translateOrDefault;
  return app;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/createAngularJsAdapter.ts
function createAngularJsAdapter_slicedToArray(arr, i) { return createAngularJsAdapter_arrayWithHoles(arr) || createAngularJsAdapter_iterableToArrayLimit(arr, i) || createAngularJsAdapter_unsupportedIterableToArray(arr, i) || createAngularJsAdapter_nonIterableRest(); }

function createAngularJsAdapter_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function createAngularJsAdapter_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return createAngularJsAdapter_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return createAngularJsAdapter_arrayLikeToArray(o, minLen); }

function createAngularJsAdapter_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function createAngularJsAdapter_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function createAngularJsAdapter_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function createAngularJsAdapter_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { createAngularJsAdapter_typeof = function _typeof(obj) { return typeof obj; }; } else { createAngularJsAdapter_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return createAngularJsAdapter_typeof(obj); }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */



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

function removeAngularJsSpecificProperties(newValue) {
  if (createAngularJsAdapter_typeof(newValue) === 'object' && newValue !== null && Object.getPrototypeOf(newValue) === Object.prototype) {
    return Object.fromEntries(Object.entries(newValue).filter(function (pair) {
      return !/^\$/.test(pair[0]);
    }));
  }

  return newValue;
}
function createAngularJsAdapter(options) {
  var component = options.component,
      require = options.require,
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
      restrict = _options$restrict === void 0 ? 'A' : _options$restrict,
      priority = options.priority,
      replace = options.replace;
  var currentTranscludeCounter = transcludeCounter;

  if (transclude) {
    transcludeCounter += 1;
  }

  var vueToAngular = {};
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

    vueToAngular[info.vue] = scopeVarName;
  });

  function angularJsAdapter() {
    for (var _len = arguments.length, injectedServices = new Array(_len), _key = 0; _key < _len; _key++) {
      injectedServices[_key] = arguments[_key];
    }

    var adapter = {
      restrict: restrict,
      require: require,
      priority: priority,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(ngScope, ngElement, ngAttrs, ngController) {
            var transcludeClone = transclude ? ngElement.find("[ng-transclude][counter=".concat(currentTranscludeCounter, "]")) : null; // build the root vue template

            var rootVueTemplate = '<root-component';
            Object.entries(events).forEach(function (info) {
              var _info = createAngularJsAdapter_slicedToArray(info, 1),
                  eventName = _info[0];

              rootVueTemplate += " @".concat(toKebabCase(eventName), "=\"onEventHandler('").concat(eventName, "', $event)\"");
            });
            Object.entries(scope).forEach(function (_ref3) {
              var _ref4 = createAngularJsAdapter_slicedToArray(_ref3, 2),
                  info = _ref4[1];

              if (info.angularJsBind === '&' || info.angularJsBind === '&?') {
                var eventName = toKebabCase(info.vue);

                if (!events[info.vue]) {
                  // pass through scope & w/o a custom event handler
                  rootVueTemplate += " @".concat(eventName, "=\"onEventHandler('").concat(info.vue, "', $event)\"");
                }
              } else {
                rootVueTemplate += " :".concat(toKebabCase(info.vue), "=\"").concat(info.vue, "\"");
              }
            });
            rootVueTemplate += '>';

            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }

            rootVueTemplate += '</root-component>'; // build the vue app

            var app = createVueApp({
              template: rootVueTemplate,
              data: function data() {
                var _this = this;

                var initialData = {};
                Object.entries(scope).forEach(function (_ref5) {
                  var _ref6 = createAngularJsAdapter_slicedToArray(_ref5, 2),
                      scopeVarName = _ref6[0],
                      info = _ref6[1];

                  var value = removeAngularJsSpecificProperties(ngScope[scopeVarName]);

                  if (typeof value === 'undefined' && typeof info.default !== 'undefined') {
                    value = info.default instanceof Function ? info.default.apply(info, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : info.default;
                  }

                  if (info.transform) {
                    value = info.transform.apply(info, [value, _this, ngScope, ngElement, ngAttrs, ngController].concat(injectedServices));
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
                  scopePropertyName = vueToAngular[scopePropertyName] || scopePropertyName;

                  if (ngScope[scopePropertyName]) {
                    ngScope[scopePropertyName]($event);
                  }

                  if (events[name]) {
                    events[name].apply(events, [$event, this, ngScope, ngElement, ngAttrs, ngController].concat(injectedServices));
                  }
                }
              }
            });
            app.component('root-component', component); // mount the app

            var mountPoint = mountPointFactory ? mountPointFactory.apply(void 0, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : ngElement[0];
            var vm = app.mount(mountPoint); // setup watches to bind between angularjs + vue

            Object.entries(scope).forEach(function (_ref7) {
              var _ref8 = createAngularJsAdapter_slicedToArray(_ref7, 2),
                  scopeVarName = _ref8[0],
                  info = _ref8[1];

              if (!info.angularJsBind || info.angularJsBind === '&' || info.angularJsBind === '&?') {
                return;
              }

              ngScope.$watch(scopeVarName, function (newValue, oldValue) {
                if (newValue === oldValue && JSON.stringify(vm[info.vue]) === JSON.stringify(newValue)) {
                  return; // initial
                }

                var newValueFinal = removeAngularJsSpecificProperties(newValue);

                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  newValueFinal = info.default instanceof Function ? info.default.apply(info, [ngScope, ngElement, ngAttrs].concat(injectedServices)) : info.default;
                }

                if (info.transform) {
                  newValueFinal = info.transform.apply(info, [newValueFinal, vm, ngScope, ngElement, ngAttrs, ngController].concat(injectedServices));
                }

                vm[info.vue] = newValueFinal;
              }, info.deepWatch);
            });

            if (transclude && transcludeClone) {
              $(vm.transcludeTarget).append(transcludeClone);
            }

            if (postCreate) {
              postCreate.apply(void 0, [vm, ngScope, ngElement, ngAttrs, ngController].concat(injectedServices));
            } // specifying replace: true on the directive does nothing w/ vue inside, so
            // handle it here.


            if (replace) {
              // transfer attributes from angularjs element that are not in scope to
              // mount point element
              Array.from(ngElement[0].attributes).forEach(function (attr) {
                if (scope[attr.nodeName]) {
                  return;
                }

                if (mountPoint.firstElementChild) {
                  mountPoint.firstElementChild.setAttribute(attr.nodeName, attr.nodeValue);
                }
              });
              ngElement.replaceWith(window.$(mountPoint).children());
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
  window.angular.module('piwikApp').directive(directiveName, angularJsAdapter);
  return angularJsAdapter;
}
function transformAngularJsBoolAttr(v) {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === 'true') {
    return true;
  }

  return !!v && v > 0 && v !== '0';
}
function transformAngularJsIntAttr(v) {
  if (typeof v === 'undefined') {
    return undefined;
  }

  if (v === null) {
    return null;
  }

  return parseInt(v, 10);
} // utility function for service adapters

function clone(p) {
  if (typeof p === 'undefined') {
    return p;
  }

  return JSON.parse(JSON.stringify(p));
}
function cloneThenApply(p) {
  var result = clone(p);
  Matomo_Matomo.helper.getAngularDependency('$rootScope').$applyAsync();
  return result;
}
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
      var _binding$value;

      options = {
        hover: true
      };
      $(element).addClass('submenu');
      $(((_binding$value = binding.value) === null || _binding$value === void 0 ? void 0 : _binding$value.activates) || $(element).data('target')).addClass('submenu-dropdown-content'); // if a submenu is used, the dropdown will never scroll

      $(element).parents('.dropdown-content').addClass('submenu-container');
    }

    $(element).dropdown(options);
  },
  updated: function updated(element) {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      $(element).addClass('matomo-dropdown-menu');
    });
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
window.angular.module('piwikApp').directive('piwikDropdownMenu', piwikDropdownMenu);
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

window.angular.module('piwikApp.directive').directive('piwikFocusAnywhereButHere', piwikFocusAnywhereButHere);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusIf/FocusIf.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function doFocusIf(el, binding) {
  var _binding$value, _binding$oldValue;

  if ((_binding$value = binding.value) !== null && _binding$value !== void 0 && _binding$value.focused && !((_binding$oldValue = binding.oldValue) !== null && _binding$oldValue !== void 0 && _binding$oldValue.focused)) {
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
          value: {
            focused: newValue ? true : undefined,
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

window.angular.module('piwikApp.directive').directive('piwikFocusIf', piwikFocusIf);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/directiveUtilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function getRef(expander, binding) {
  var _binding$instance;

  return expander instanceof HTMLElement ? expander : (_binding$instance = binding.instance) === null || _binding$instance === void 0 ? void 0 : _binding$instance.$refs[expander];
}

/* harmony default export */ var directiveUtilities = ({
  getRef: getRef
});
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
var ExpandOnClick_window = window,
    ExpandOnClick_$ = ExpandOnClick_window.$;
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
    binding.value.onScroll = ExpandOnClick_onScroll.bind(null, binding);
    setTimeout(function () {
      var expander = directiveUtilities.getRef(binding.value.expander, binding);

      if (expander) {
        ExpandOnClick_$(expander).on('click', binding.value.onExpand);
      }
    });
    ExpandOnClick_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnClick_doc.addEventListener('mousedown', binding.value.onMouseDown);
    ExpandOnClick_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    ExpandOnClick_doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted: function unmounted(el, binding) {
    var expander = directiveUtilities.getRef(binding.value.expander, binding);

    if (expander) {
      ExpandOnClick_$(expander).off('click', binding.value.onExpand);
    }

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
window.angular.module('piwikApp').directive('piwikExpandOnClick', piwikExpandOnClick);
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
    setTimeout(function () {
      var expander = directiveUtilities.getRef(binding.value.expander, binding);

      if (expander) {
        expander.addEventListener('mouseenter', binding.value.onMouseEnter);
      }
    });
    el.addEventListener('mouseleave', binding.value.onMouseLeave);
    ExpandOnHover_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnHover_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
  },
  unmounted: function unmounted(el, binding) {
    var expander = directiveUtilities.getRef(binding.value.expander, binding);

    if (expander) {
      expander.removeEventListener('mouseenter', binding.value.onMouseEnter);
    }

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

window.angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowSensitiveData/ShowSensitiveData.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var ShowSensitiveData_window = window,
    ShowSensitiveData_$ = ShowSensitiveData_window.$;
/**
 * Handles visibility of sensitive data. By default data will be shown replaced with stars (*)
 * On click on the element the full data will be shown
 *
 * Configuration attributes:
 * data-show-characters          number of characters to show in clear text (defaults to 6)
 * data-click-element-selector   selector for element that will show the full data on click
 *                               (defaults to element)
 *
 * Example:
 * <div v-show-sensitive-date="some text"></div>
 */

/* harmony default export */ var ShowSensitiveData = ({
  mounted: function mounted(el, binding) {
    var element = ShowSensitiveData_$(el);
    var sensitiveData = binding.value.sensitiveData;
    var showCharacters = binding.value.showCharacters || 6;
    var clickElement = binding.value.clickElementSelector || element;
    var protectedData = '';

    if (showCharacters > 0) {
      protectedData += sensitiveData.slice(0, showCharacters);
    }

    protectedData += sensitiveData.slice(showCharacters).replace(/./g, '*');
    element.html(protectedData);

    function onClickHandler() {
      element.html(sensitiveData);
      ShowSensitiveData_$(clickElement).css({
        cursor: ''
      });
      ShowSensitiveData_$(clickElement).tooltip('destroy');
    }

    ShowSensitiveData_$(clickElement).tooltip({
      content: translate('CoreHome_ClickToSeeFullInformation'),
      items: '*',
      track: true
    });
    ShowSensitiveData_$(clickElement).one('click', onClickHandler);
    ShowSensitiveData_$(clickElement).css({
      cursor: 'pointer'
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowSensitiveData/ShowSensitiveData.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikShowSensitiveData() {
  return {
    restrict: 'A',
    link: function piwikShowSensitiveDataLink(scope, element, attr) {
      var binding = {
        instance: null,
        value: {
          sensitiveData: attr.piwikShowSensitiveData || (attr.text ? attr.text() : ''),
          showCharacters: attr.showCharacters ? parseInt(attr.showCharacters, 10) : undefined,
          clickElementSelector: attr.clickElementSelector
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      ShowSensitiveData.mounted(element[0], binding);
    }
  };
}
window.angular.module('piwikApp').directive('piwikShowSensitiveData', piwikShowSensitiveData);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownButton/DropdownButton.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var DropdownButton_window = window,
    DropdownButton_$ = DropdownButton_window.$;
/* harmony default export */ var DropdownButton = ({
  mounted: function mounted(el) {
    var element = DropdownButton_$(el); // BC for materializecss 0.97 => 1.0

    if (!element.attr('data-target') && element.attr('data-activates')) {
      element.attr('data-target', element.attr('data-activates'));
    }

    var target = element.attr('data-target');

    if (target && DropdownButton_$("#".concat(target)).length) {
      element.dropdown({
        inDuration: 300,
        outDuration: 225,
        constrainWidth: false,
        //  hover: true, // Activate on hover
        belowOrigin: true // Displays dropdown below the button

      });
    }
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownButton/DropdownButton.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikDropdownButton() {
  return {
    restrict: 'C',
    link: function piwikDropdownButtonLink(scope, element) {
      DropdownButton.mounted(element[0]);
    }
  };
}
window.angular.module('piwikApp').directive('dropdownButton', piwikDropdownButton);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SelectOnFocus/SelectOnFocus.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function onFocusHandler(binding, event) {
  if (binding.value.focusedElement !== event.target) {
    binding.value.focusedElement = event.target;
    window.angular.element(event.target).select();
  }
}

function SelectOnFocus_onClickHandler(event) {
  // .select() + focus and blur seems to not work on pre elements
  var range = document.createRange();
  range.selectNode(event.target);
  var selection = window.getSelection();

  if (selection && selection.rangeCount > 0) {
    selection.removeAllRanges();
  }

  if (selection) {
    selection.addRange(range);
  }
}

function onBlurHandler(binding) {
  delete binding.value.focusedElement;
}

/* harmony default export */ var SelectOnFocus = ({
  mounted: function mounted(el, binding) {
    var tagName = el.tagName.toLowerCase();
    binding.value.elementSupportsSelect = tagName === 'textarea';

    if (binding.value.elementSupportsSelect) {
      binding.value.onFocusHandler = onFocusHandler.bind(null, binding);
      binding.value.onBlurHandler = onBlurHandler.bind(null, binding);
      el.addEventListener('focus', binding.value.onFocusHandler);
      el.addEventListener('blur', binding.value.onBlurHandler);
    } else {
      binding.value.onClickHandler = SelectOnFocus_onClickHandler;
      el.addEventListener('click', binding.value.onClickHandler);
    }
  },
  unmounted: function unmounted(el, binding) {
    if (binding.value.elementSupportsSelect) {
      el.removeEventListener('focus', binding.value.onFocusHandler);
      el.removeEventListener('blur', binding.value.onBlurHandler);
    } else {
      el.removeEventListener('click', binding.value.onClickHandler);
    }
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SelectOnFocus/SelectOnFocus.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikSelectOnFocus() {
  return {
    restrict: 'A',
    link: function piwikSelectOnFocusLink(scope, element) {
      var binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      SelectOnFocus.mounted(element[0], binding);
      element.on('$destroy', function () {
        return SelectOnFocus.unmounted(element[0], binding);
      });
    }
  };
}
window.angular.module('piwikApp').directive('piwikSelectOnFocus', piwikSelectOnFocus);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/CopyToClipboard/CopyToClipboard.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function CopyToClipboard_onClickHandler(pre) {
  if (pre) {
    var textarea = document.createElement('textarea');
    textarea.value = pre.innerText;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    var btn = pre.parentElement;

    if (btn) {
      var icon = btn.getElementsByTagName('i')[0];

      if (icon) {
        icon.classList.remove('copyToClipboardIcon');
        icon.classList.add('copyToClipboardIconCheck');
      }

      var copied = btn.getElementsByClassName('copyToClipboardCopiedDiv')[0];

      if (copied) {
        copied.style.display = 'inline-block';
        setTimeout(function () {
          copied.style.display = 'none';
        }, 2500);
      }
    }
  }
}

function onTransitionEndHandler(el, binding) {
  if (binding.value.transitionOpen) {
    var btn = el.parentElement;

    if (btn) {
      var icon = btn.getElementsByTagName('i')[0];

      if (icon) {
        icon.classList.remove('copyToClipboardIconCheck');
        icon.classList.add('copyToClipboardIcon');
      }
    }

    binding.value.transitionOpen = false;
  } else {
    binding.value.transitionOpen = true;
  }
}

/* harmony default export */ var CopyToClipboard = ({
  mounted: function mounted(el, binding) {
    var tagName = el.tagName.toLowerCase();

    if (tagName === 'pre') {
      var btn = document.createElement('button');
      btn.setAttribute('type', 'button');
      btn.className = 'copyToClipboardButton';
      var positionDiv = document.createElement('div');
      positionDiv.className = 'copyToClipboardPositionDiv';
      var icon = document.createElement('i');
      icon.className = 'copyToClipboardIcon';
      btn.appendChild(icon);
      var sp = document.createElement('span');
      sp.className = 'copyToClipboardSpan';
      sp.innerHTML = translate('General_Copy');
      btn.appendChild(sp);
      positionDiv.appendChild(btn);
      var cdiv = document.createElement('div');
      cdiv.className = 'copyToClipboardCopiedDiv';
      cdiv.innerHTML = translate('General_CopiedToClipboard');
      positionDiv.appendChild(cdiv);
      var pe = el.parentElement;

      if (pe) {
        pe.classList.add('copyToClipboardWrapper');
        pe.appendChild(positionDiv);
      }

      binding.value.onClickHandler = CopyToClipboard_onClickHandler.bind(null, el);
      btn.addEventListener('click', binding.value.onClickHandler);
      binding.value.onTransitionEndHandler = onTransitionEndHandler.bind(null, el, binding);
      btn.addEventListener('transitionend', binding.value.onTransitionEndHandler);
    }
  },
  unmounted: function unmounted(el, binding) {
    el.removeEventListener('click', binding.value.onClickHandler);
    el.removeEventListener('transitionend', binding.value.onTransitionEndHandler);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/CopyToClipboard/CopyToClipboard.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function matomoCopyToClipboard() {
  return {
    restrict: 'A',
    link: function matomoCopyToClipboardLink(scope, element) {
      var binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      CopyToClipboard.mounted(element[0], binding);
      element.on('$destroy', function () {
        return CopyToClipboard.unmounted(element[0], binding);
      });
    }
  };
}
window.angular.module('piwikApp').directive('matomoCopyToClipboard', matomoCopyToClipboard);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SideNav/SideNav.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var initialized = false;
/**
 * Will activate the materialize side nav feature once rendered. We use this directive as
 * it makes sure the actual left menu is rendered at the time we init the side nav.
 *
 * Has to be set on a collaapsible element
 *
 * Example:
 * <div class="collapsible" v-side-nav="nav .activateLeftMenu">...</div>
 */

/* harmony default export */ var SideNav = ({
  mounted: function mounted(el, binding) {
    if (!binding.value.activator) {
      return;
    }

    setTimeout(function () {
      if (!initialized) {
        initialized = true;
        var sideNavActivator = directiveUtilities.getRef(binding.value.activator, binding);

        if (sideNavActivator) {
          window.$(sideNavActivator).show();
          var targetSelector = sideNavActivator.getAttribute('data-target'); // @ts-ignore

          window.$("#".concat(targetSelector)).sidenav({
            closeOnClick: true
          });
        }
      }

      if (el.classList.contains('collapsible')) {
        window.$(el).collapsible();
      }
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SideNav/SideNav.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikSideNav($timeout) {
  return {
    restrict: 'A',
    priority: 10,
    link: function linkPiwikSideNav(scope, element, attr) {
      var binding = {
        instance: null,
        value: {
          activator: $(attr.piwikSideNav)[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      $timeout(function () {
        SideNav.mounted(element[0], binding);
      });
    }
  };
}
piwikSideNav.$inject = ['$timeout'];
window.angular.module('piwikApp.directive').directive('piwikSideNav', piwikSideNav);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=a14c48d8

var _hoisted_1 = {
  ref: "root"
};
function MatomoDialogvue_type_template_id_a14c48d8_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.modelValue]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=a14c48d8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=script&lang=ts


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
        $('.modal.open').modal('close');
        this.$emit('close');
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue



MatomoDialogvue_type_script_lang_ts.render = MatomoDialogvue_type_template_id_a14c48d8_render

/* harmony default export */ var MatomoDialog = (MatomoDialogvue_type_script_lang_ts);
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
    yes: function yes($event, vm, scope, element, attrs) {
      if (attrs.yes) {
        scope.$eval(attrs.yes);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    no: function no($event, vm, scope, element, attrs) {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    validation: function validation($event, vm, scope, element, attrs) {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    close: function close($event, vm, scope, element, attrs) {
      if (attrs.close) {
        scope.$eval(attrs.close);
        setTimeout(function () {
          scope.$apply();
        }, 0);
      }
    },
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, controller, $parse) {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=744f4bf7

var EnrichedHeadlinevue_type_template_id_744f4bf7_hoisted_1 = {
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
var _hoisted_13 = ["innerHTML"];
var _hoisted_14 = ["href"];
function EnrichedHeadlinevue_type_template_id_744f4bf7_render(_ctx, _cache, $props, $setup, $data, $options) {
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
  }, [!_ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", EnrichedHeadlinevue_type_template_id_744f4bf7_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "title",
    href: _ctx.editUrl,
    title: _ctx.translate('CoreHome_ClickToEditX', _ctx.htmlEntities(_ctx.actualFeatureName))
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
  }, null, 8, _hoisted_12), _ctx.reportGenerated != '' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "helpDate",
    innerHTML: _ctx.$sanitize(_ctx.reportGenerated)
  }, null, 8, _hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.helpUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    rel: "noreferrer noopener",
    target: "_blank",
    class: "readMore",
    href: _ctx.helpUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 9, _hoisted_14)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showInlineHelp]])], 544);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=744f4bf7

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/useExternalPluginComponent.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

/* eslint-disable @typescript-eslint/ban-ts-comment */

function useExternalPluginComponent(plugin, component) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineAsyncComponent"])(function () {
    return new Promise(function (resolve) {
      window.$(document).ready(function () {
        if (window[plugin]) {
          resolve(window[plugin][component]);
        } else {
          // @ts-ignore
          resolve(null); // plugin not loaded
        }
      });
    });
  });
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts



 // working around a cycle in dependencies (CoreHome depends on Feedback, Feedback depends on
// CoreHome)

var RateFeature = useExternalPluginComponent('Feedback', 'RateFeature');
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
        var _root$parentElement;

        var helpNode = root.querySelector('.title .inlineHelp');

        if (!helpNode && (_root$parentElement = root.parentElement) !== null && _root$parentElement !== void 0 && _root$parentElement.nextElementSibling) {
          // hack for reports :(
          helpNode = root.parentElement.nextElementSibling.querySelector('.reportDocumentation');
        }

        if (helpNode) {
          var _helpNode$getAttribut;

          // hackish solution to get binded html of p tag within the help node
          // at this point the ng-bind-html is not yet converted into html when report is not
          // initially loaded. Using $compile doesn't work. So get and set it manually
          var helpDocs = (_helpNode$getAttribut = helpNode.getAttribute('data-content')) === null || _helpNode$getAttribut === void 0 ? void 0 : _helpNode$getAttribut.trim();

          if (helpDocs && helpDocs.length) {
            _this.actualInlineHelp = "<p>".concat(helpDocs, "</p>");
            setTimeout(function () {
              return helpNode.remove();
            }, 0);
          }
        }
      }

      if (!_this.actualFeatureName) {
        var _root$querySelector;

        _this.actualFeatureName = (_root$querySelector = root.querySelector('.title')) === null || _root$querySelector === void 0 ? void 0 : _root$querySelector.textContent;
      }

      if (Matomo_Matomo.period && Matomo_Matomo.currentDateString) {
        var currentPeriod = Periods_Periods.parse(Matomo_Matomo.period, Matomo_Matomo.currentDateString);

        if (_this.reportGenerated && currentPeriod.containsToday()) {
          window.$(root.querySelector('.report-generated')).tooltip({
            track: true,
            content: _this.reportGenerated,
            items: 'div',
            show: false,
            hide: false
          });
        }
      }
    });
  },
  methods: {
    htmlEntities: function htmlEntities(v) {
      return Matomo_Matomo.helper.htmlEntities(v);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue



EnrichedHeadlinevue_type_script_lang_ts.render = EnrichedHeadlinevue_type_template_id_744f4bf7_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=bb18e396

var ContentBlockvue_type_template_id_bb18e396_hoisted_1 = {
  class: "card",
  ref: "root"
};
var ContentBlockvue_type_template_id_bb18e396_hoisted_2 = {
  class: "card-content"
};
var ContentBlockvue_type_template_id_bb18e396_hoisted_3 = {
  key: 0,
  class: "card-title"
};
var ContentBlockvue_type_template_id_bb18e396_hoisted_4 = {
  key: 1,
  class: "card-title"
};
var ContentBlockvue_type_template_id_bb18e396_hoisted_5 = {
  ref: "content"
};
function ContentBlockvue_type_template_id_bb18e396_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ContentBlockvue_type_template_id_bb18e396_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_bb18e396_hoisted_2, [_ctx.contentTitle && !_ctx.actualFeature && !_ctx.helpUrl && !_ctx.actualHelpText ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_bb18e396_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(_ctx.contentTitle)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.contentTitle && (_ctx.actualFeature || _ctx.helpUrl || _ctx.actualHelpText) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_bb18e396_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.actualFeature,
    "help-url": _ctx.helpUrl,
    "inline-help": _ctx.actualHelpText
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(_ctx.contentTitle)), 1)];
    }),
    _: 1
  }, 8, ["feature-name", "help-url", "inline-help"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_bb18e396_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)])], 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=bb18e396

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts



var adminContent = null;
var ContentBlockvue_type_script_lang_ts_window = window,
    ContentBlockvue_type_script_lang_ts_$ = ContentBlockvue_type_script_lang_ts_window.$;
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

    var root = this.$refs.root;
    var content = this.$refs.content;

    if (this.anchor && root && root.parentElement) {
      var anchorElement = document.createElement('a');
      anchorElement.id = this.anchor;
      ContentBlockvue_type_script_lang_ts_$(root.parentElement).prepend(anchorElement);
    }

    setTimeout(function () {
      var inlineHelp = content.querySelector('.contentHelp');

      if (inlineHelp) {
        _this.actualHelpText = inlineHelp.innerHTML;
        inlineHelp.remove();
      }
    }, 0);

    if (this.actualFeature && this.actualFeature === 'true') {
      this.actualFeature = this.contentTitle;
    }

    if (adminContent === null) {
      // cache admin node for further content blocks
      adminContent = document.querySelector('#content.admin');
    }

    var contentTopPosition = null;

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
        root.style.marginTop = '0';
      }
    }
  },
  methods: {
    decode: function decode(s) {
      return Matomo_Matomo.helper.htmlDecode(s);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue



ContentBlockvue_type_script_lang_ts.render = ContentBlockvue_type_template_id_bb18e396_render

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

  return Array.isArray(values) ? values : [values];
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
            params: Object.assign(Object.assign({}, segmentComp.params), periodComp.params),
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

      var baseParams = Matomo_Matomo.helper.isAngularRenderingThePage() ? src_MatomoUrl_MatomoUrl.hashParsed.value : src_MatomoUrl_MatomoUrl.urlParsed.value;
      src_MatomoUrl_MatomoUrl.updateLocation(Object.assign(Object.assign(Object.assign({}, baseParams), compareParams), extraParams));
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

      var matomoModule = src_MatomoUrl_MatomoUrl.parsed.value.module; // check if body id #installation exist

      if (window.piwik.installation) {
        this.privateState.comparisonsDisabledFor = [];
        return;
      }

      if (matomoModule === 'CoreUpdater' || matomoModule === 'Installation' || matomoModule === 'Overlay' || window.piwik.isPagesComparisonApiDisabled) {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=d1a096d8

var Comparisonsvue_type_template_id_d1a096d8_hoisted_1 = {
  key: 0,
  ref: "root",
  class: "matomo-comparisons"
};
var Comparisonsvue_type_template_id_d1a096d8_hoisted_2 = {
  class: "comparison-type"
};
var Comparisonsvue_type_template_id_d1a096d8_hoisted_3 = ["title"];
var Comparisonsvue_type_template_id_d1a096d8_hoisted_4 = ["href"];
var Comparisonsvue_type_template_id_d1a096d8_hoisted_5 = ["title"];
var Comparisonsvue_type_template_id_d1a096d8_hoisted_6 = {
  class: "comparison-period-label"
};
var Comparisonsvue_type_template_id_d1a096d8_hoisted_7 = ["onClick"];
var Comparisonsvue_type_template_id_d1a096d8_hoisted_8 = ["title"];
var Comparisonsvue_type_template_id_d1a096d8_hoisted_9 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  }
};
var Comparisonsvue_type_template_id_d1a096d8_hoisted_10 = ["alt"];
function Comparisonsvue_type_template_id_d1a096d8_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");

  return _ctx.isComparing ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Comparisonsvue_type_template_id_d1a096d8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Comparisons')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.segmentComparisons, function (comparison, $index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "comparison card",
      key: comparison.index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_d1a096d8_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Segment')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "title",
      title: comparison.title + '<br/>' + decodeURIComponent(comparison.params.segment)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      href: _ctx.getUrlToSegment(comparison.params.segment)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(comparison.title), 9, Comparisonsvue_type_template_id_d1a096d8_hoisted_4)], 8, Comparisonsvue_type_template_id_d1a096d8_hoisted_3), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periodComparisons, function (periodComparison) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "comparison-period",
        key: periodComparison.index,
        title: _ctx.getComparisonTooltip(comparison, periodComparison)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "comparison-dot",
        style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
          'background-color': _ctx.getSeriesColor(comparison, periodComparison)
        })
      }, null, 4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Comparisonsvue_type_template_id_d1a096d8_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(periodComparison.title) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getComparisonPeriodType(periodComparison)) + ") ", 1)], 8, Comparisonsvue_type_template_id_d1a096d8_hoisted_5);
    }), 128)), _ctx.segmentComparisons.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "remove-button",
      onClick: function onClick($event) {
        return _ctx.removeSegmentComparison($index);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon icon-close",
      title: _ctx.translate('General_ClickToRemoveComp')
    }, null, 8, Comparisonsvue_type_template_id_d1a096d8_hoisted_8)], 8, Comparisonsvue_type_template_id_d1a096d8_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_d1a096d8_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, Comparisonsvue_type_template_id_d1a096d8_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 512)), [[_directive_tooltips, {
    duration: 200,
    delay: 200,
    content: _ctx.transformTooltipContent
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=d1a096d8

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Tooltips/Tooltips.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var Tooltips_window = window,
    Tooltips_$ = Tooltips_window.$;

function defaultContentTransform() {
  var title = Tooltips_$(this).attr('title') || '';
  return window.vueSanitize(title.replace(/\n/g, '<br />'));
}

function setupTooltips(el, binding) {
  var _binding$value, _binding$value2, _binding$value3;

  Tooltips_$(el).tooltip({
    track: true,
    content: ((_binding$value = binding.value) === null || _binding$value === void 0 ? void 0 : _binding$value.content) || defaultContentTransform,
    show: {
      delay: ((_binding$value2 = binding.value) === null || _binding$value2 === void 0 ? void 0 : _binding$value2.delay) || 700,
      duration: ((_binding$value3 = binding.value) === null || _binding$value3 === void 0 ? void 0 : _binding$value3.duration) || 200
    },
    hide: false
  });
}

/* harmony default export */ var Tooltips = ({
  mounted: function mounted(el, binding) {
    setTimeout(function () {
      return setupTooltips(el, binding);
    });
  },
  updated: function updated(el, binding) {
    setTimeout(function () {
      return setupTooltips(el, binding);
    });
  },
  beforeUnmount: function beforeUnmount(el) {
    try {
      window.$(el).tooltip('destroy');
    } catch (e) {// ignore
    }
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts







/* harmony default export */ var Comparisonsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {},
  directives: {
    Tooltips: Tooltips
  },
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

    function transformTooltipContent() {
      var title = window.$(this).attr('title');

      if (!title) {
        return title;
      }

      return window.vueSanitize(title.replace(/\n/g, '<br />'));
    }

    return {
      isComparing: isComparing,
      segmentComparisons: segmentComparisons,
      periodComparisons: periodComparisons,
      getSeriesColor: getSeriesColor,
      transformTooltipContent: transformTooltipContent
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
      var hash = Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value);
      delete hash.comparePeriods;
      delete hash.compareDates;
      delete hash.compareSegments;
      hash.segment = segment;
      return "".concat(window.location.search, "#?").concat(src_MatomoUrl_MatomoUrl.stringify(hash));
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
  mounted: function mounted() {
    var _this2 = this;

    Matomo_Matomo.on('piwikComparisonsChanged', function () {
      _this2.onComparisonsChanged();
    });
    this.onComparisonsChanged();
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue



Comparisonsvue_type_script_lang_ts.render = Comparisonsvue_type_template_id_d1a096d8_render

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

window.angular.module('piwikApp.service').factory('piwikComparisonsService', ComparisonFactory);
/* harmony default export */ var Comparisons_adapter = (createAngularJsAdapter({
  component: Comparisons,
  directiveName: 'piwikComparisons',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=template&id=3deff5e2

var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_1 = {
  ref: "root",
  class: "menuDropdown"
};
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_2 = ["title"];
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_3 = ["innerHTML"];

var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-arrow-bottom"
}, null, -1);

var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_5 = {
  class: "items"
};
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_6 = {
  key: 0,
  class: "search"
};
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_7 = ["placeholder"];
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_8 = ["title"];
var MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_9 = ["title"];
function MenuItemsDropdownvue_type_template_id_3deff5e2_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "title",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showItems = !_ctx.showItems;
    }),
    title: _ctx.tooltip
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(this.actualMenuTitle)
  }, null, 8, MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_3), MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_4], 8, MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_5, [_ctx.showSearch && _ctx.showItems ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    onKeydown: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onSearchTermKeydown($event);
    }),
    placeholder: _ctx.translate('General_Search')
  }, null, 40, MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.showItems
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    class: "search_ico",
    src: "plugins/Morpheus/images/search_ico.png",
    title: _ctx.translate('General_Search')
  }, null, 8, MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_8), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[3] || (_cache[3] = function ($event) {
      _ctx.searchTerm = '';

      _ctx.searchItems('');
    }),
    class: "reset",
    src: "plugins/CoreHome/images/reset_search.png",
    title: _ctx.translate('General_Clear')
  }, null, 8, MenuItemsDropdownvue_type_template_id_3deff5e2_hoisted_9), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.selectItem($event);
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showItems]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.lostFocus
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=template&id=3deff5e2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=script&lang=ts



var MenuItemsDropdownvue_type_script_lang_ts_window = window,
    MenuItemsDropdownvue_type_script_lang_ts_$ = MenuItemsDropdownvue_type_script_lang_ts_window.$;
/* harmony default export */ var MenuItemsDropdownvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    menuTitle: String,
    tooltip: String,
    showSearch: Boolean,
    menuTitleChangeOnClick: Boolean
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

      if (this.menuTitleChangeOnClick) {
        this.actualMenuTitle = (event.target.textContent || '').replace(/[\u0000-\u2666]/g, function (c) {
          return "&#".concat(c.charCodeAt(0), ";");
        }); // eslint-disable-line
      }

      this.showItems = false;
      MenuItemsDropdownvue_type_script_lang_ts_$(this.$slots.default()[0].el).find('.item').removeClass('active');
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
      MenuItemsDropdownvue_type_script_lang_ts_$(this.$refs.root).find('.item').each(function (index, node) {
        var $node = MenuItemsDropdownvue_type_script_lang_ts_$(node);

        if ($node.text().toLowerCase().indexOf(searchTerm) === -1) {
          $node.hide();
        } else {
          $node.show();
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue



MenuItemsDropdownvue_type_script_lang_ts.render = MenuItemsDropdownvue_type_template_id_3deff5e2_render

/* harmony default export */ var MenuItemsDropdown = (MenuItemsDropdownvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var MenuItemsDropdown_adapter = (createAngularJsAdapter({
  component: MenuItemsDropdown,
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
    'after-select': function afterSelect($event, vm, scope) {
      setTimeout(function () {
        scope.$apply();
      }, 0);
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=589729fc

var DatePickervue_type_template_id_589729fc_hoisted_1 = {
  ref: "root"
};
function DatePickervue_type_template_id_589729fc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DatePickervue_type_template_id_589729fc_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=589729fc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts



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
      if (!props.viewDate) {
        return false;
      }

      var date;

      if (typeof props.viewDate === 'string') {
        try {
          date = parseDate(props.viewDate);
        } catch (e) {
          return false;
        }
      } else {
        date = props.viewDate;
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
      var monthPicker = element.find('.ui-datepicker-month')[0];

      if (monthPicker) {
        monthPicker.disabled = props.disableMonthDropdown;
      }
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
      return Object.assign({}, props);
    }, function (newProps, oldProps) {
      var redraw = false;
      [function (x) {
        return x.selectedDateStart;
      }, function (x) {
        return x.selectedDateEnd;
      }, function (x) {
        return x.highlightedDateStart;
      }, function (x) {
        return x.highlightedDateEnd;
      }].forEach(function (selector) {
        if (redraw) {
          return;
        }

        var newProp = selector(newProps);
        var oldProp = selector(oldProps);

        if (!newProp && oldProp) {
          redraw = true;
        }

        if (newProp && !oldProp) {
          redraw = true;
        }

        if (newProp && oldProp && newProp.getTime() !== oldProp.getTime()) {
          redraw = true;
        }
      });

      if (newProps.viewDate !== oldProps.viewDate && viewDateChanged()) {
        redraw = true;
      }

      if (newProps.stepMonths !== oldProps.stepMonths) {
        stepMonthsChanged();
      }

      if (newProps.disableMonthDropdown !== oldProps.disableMonthDropdown) {
        enableDisableMonthDropdown();
      } // redraw when selected/highlighted dates change


      if (redraw) {
        setDatePickerCellColors();
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      var element = DatePickervue_type_script_lang_ts_$(root.value);
      var customOptions = props.options || {};
      var datePickerOptions = Object.assign(Object.assign(Object.assign({}, Matomo_Matomo.getBaseDatePickerOptions()), customOptions), {}, {
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

      element.on('click', 'tbody td.ui-datepicker-other-month', handleOtherMonthClick); // NOTE: using a selector w/ .on() doesn't seem to work for some reason...

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



DatePickervue_type_script_lang_ts.render = DatePickervue_type_template_id_589729fc_render

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
    'cell-hover': function cellHover(event, vm, scope, element, attrs, controller, $timeout) {
      $timeout(); // trigger new digest
    },
    'cell-hover-leave': function cellHoverLeave(event, vm, scope, element, attrs, controller, $timeout) {
      $timeout(); // trigger new digest
    },
    'date-select': function dateSelect(event, vm, scope, element, attrs, controller, $timeout) {
      $timeout(); // trigger new digest
    }
  },
  $inject: ['$timeout']
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=7bf842c2

var DateRangePickervue_type_template_id_7bf842c2_hoisted_1 = {
  id: "calendarRangeFrom"
};
var DateRangePickervue_type_template_id_7bf842c2_hoisted_2 = {
  id: "calendarRangeTo"
};
function DateRangePickervue_type_template_id_7bf842c2_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_7bf842c2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeFrom')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarFrom",
    name: "inputCalendarFrom",
    class: "browser-default",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.startDateText = $event;
    }),
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
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
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_7bf842c2_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeTo')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarTo",
    name: "inputCalendarTo",
    class: "browser-default",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.endDateText = $event;
    }),
    onKeydown: _cache[7] || (_cache[7] = function ($event) {
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
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=7bf842c2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts



var DATE_FORMAT = 'YYYY-MM-DD';
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
      if (this.startDate) {
        startDate = parseDate(this.startDate);
      }
    } catch (e) {// ignore
    }

    var endDate = null;

    try {
      if (this.endDate) {
        endDate = parseDate(this.endDate);
      }
    } catch (e) {// ignore
    }

    return {
      fromPickerSelectedDates: [startDate, startDate],
      toPickerSelectedDates: [endDate, endDate],
      fromPickerHighlightedDates: [null, null],
      toPickerHighlightedDates: [null, null],
      startDateText: this.startDate,
      endDateText: this.endDate,
      startDateInvalid: false,
      endDateInvalid: false
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
      var _this = this;

      setTimeout(function () {
        if (source === 'from') {
          _this.setStartRangeDateFromStr(event.target.value);
        } else {
          _this.setEndRangeDateFromStr(event.target.value);
        }
      });
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
      this.startDateInvalid = true;
      var startDateParsed = null;

      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          startDateParsed = parseDate(dateStr);
        }
      } catch (e) {// ignore
      }

      if (startDateParsed) {
        this.fromPickerSelectedDates = [startDateParsed, startDateParsed];
        this.startDateInvalid = false;
        this.rangeChanged();
      }
    },
    setEndRangeDateFromStr: function setEndRangeDateFromStr(dateStr) {
      this.endDateInvalid = true;
      var endDateParsed = null;

      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          endDateParsed = parseDate(dateStr);
        }
      } catch (e) {// ignore
      }

      if (endDateParsed) {
        this.toPickerSelectedDates = [endDateParsed, endDateParsed];
        this.endDateInvalid = false;
        this.rangeChanged();
      }
    },
    rangeChanged: function rangeChanged() {
      this.$emit('rangeChange', {
        start: this.fromPickerSelectedDates[0] ? format(this.fromPickerSelectedDates[0]) : null,
        end: this.toPickerSelectedDates[0] ? format(this.toPickerSelectedDates[0]) : null
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue



DateRangePickervue_type_script_lang_ts.render = DateRangePickervue_type_template_id_7bf842c2_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=6d1fa14c

function PeriodDatePickervue_type_template_id_6d1fa14c_render(_ctx, _cache, $props, $setup, $data, $options) {
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=6d1fa14c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts




var PeriodDatePickervue_type_script_lang_ts_piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
var piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);
/* harmony default export */ var PeriodDatePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    period: {
      type: String,
      required: true
    },
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

      dates[0] = PeriodDatePickervue_type_script_lang_ts_piwikMinDate < dates[0] ? dates[0] : PeriodDatePickervue_type_script_lang_ts_piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;
      return dates;
    }

    function onHoverNormalCell(cellDate, $cell) {
      var isOutOfMinMaxDateRange = cellDate < PeriodDatePickervue_type_script_lang_ts_piwikMinDate || cellDate > piwikMaxDate; // don't highlight anything if the period is month or day, and we're hovering over calendar
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



PeriodDatePickervue_type_script_lang_ts.render = PeriodDatePickervue_type_template_id_6d1fa14c_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=template&id=157b316e

var SiteSelectorvue_type_template_id_157b316e_hoisted_1 = ["value", "name"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_2 = ["title"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_3 = ["textContent"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_4 = {
  key: 1,
  class: "placeholder"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_5 = {
  class: "dropdown"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_6 = {
  class: "custom_select_search"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_7 = ["placeholder"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_8 = {
  key: 0
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_9 = {
  class: "custom_select_container"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_10 = ["onClick"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_11 = ["innerHTML", "href", "title"];
var SiteSelectorvue_type_template_id_157b316e_hoisted_12 = {
  class: "ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all\n                 siteSelect"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_13 = {
  class: "ui-menu-item"
};
var SiteSelectorvue_type_template_id_157b316e_hoisted_14 = {
  class: "ui-corner-all",
  tabindex: "-1"
};
var _hoisted_15 = {
  key: 1
};
function SiteSelectorvue_type_template_id_157b316e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$modelValue, _ctx$modelValue2, _ctx$modelValue3, _ctx$modelValue4;

  var _component_AllSitesLink = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AllSitesLink");

  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["siteSelector piwikSelector borderedControl", {
      'expanded': _ctx.showSitesList,
      'disabled': !_ctx.hasMultipleSites
    }])
  }, [_ctx.name ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
    key: 0,
    type: "hidden",
    value: (_ctx$modelValue = _ctx.modelValue) === null || _ctx$modelValue === void 0 ? void 0 : _ctx$modelValue.id,
    name: _ctx.name
  }, null, 8, SiteSelectorvue_type_template_id_157b316e_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    ref: "selectorLink",
    onClick: _cache[0] || (_cache[0] = function () {
      return _ctx.onClickSelector && _ctx.onClickSelector.apply(_ctx, arguments);
    }),
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onPressEnter($event);
    }),
    href: "javascript:void(0)",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
      'loading': _ctx.isLoading
    }, "title"]),
    tabindex: "4",
    title: _ctx.selectorLinkTitle
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["icon icon-arrow-bottom", {
      'iconHidden': _ctx.isLoading,
      'collapsed': !_ctx.showSitesList
    }])
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [(_ctx$modelValue2 = _ctx.modelValue) !== null && _ctx$modelValue2 !== void 0 && _ctx$modelValue2.name || !_ctx.placeholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(((_ctx$modelValue3 = _ctx.modelValue) === null || _ctx$modelValue3 === void 0 ? void 0 : _ctx$modelValue3.name) || _ctx.firstSiteName)
  }, null, 8, SiteSelectorvue_type_template_id_157b316e_hoisted_3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !((_ctx$modelValue4 = _ctx.modelValue) !== null && _ctx$modelValue4 !== void 0 && _ctx$modelValue4.name) && _ctx.placeholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SiteSelectorvue_type_template_id_157b316e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.placeholder), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 42, SiteSelectorvue_type_template_id_157b316e_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_157b316e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_157b316e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      _ctx.searchTerm = '';

      _ctx.loadInitialSites();
    }),
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    tabindex: "4",
    class: "websiteSearch inp browser-default",
    placeholder: _ctx.translate('General_Search')
  }, null, 8, SiteSelectorvue_type_template_id_157b316e_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.shouldFocusOnSearch
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    title: "Clear",
    onClick: _cache[4] || (_cache[4] = function ($event) {
      _ctx.searchTerm = '';

      _ctx.loadInitialSites();
    }),
    class: "reset",
    src: "plugins/CoreHome/images/reset_search.png"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.autocompleteMinSites <= _ctx.sites.length || _ctx.searchTerm]]), _ctx.allSitesLocation === 'top' && _ctx.showAllSitesItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteSelectorvue_type_template_id_157b316e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AllSitesLink, {
    href: _ctx.urlAllSites,
    "all-sites-text": _ctx.allSitesText,
    onClick: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.onAllSitesClick($event);
    })
  }, null, 8, ["href", "all-sites-text"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_157b316e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
    class: "custom_select_ul_list",
    onClick: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.showSitesList = false;
    })
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, function (site, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      onClick: function onClick($event) {
        return _ctx.switchSite(Object.assign(Object.assign({}, site), {}, {
          id: site.idsite
        }), $event);
      },
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: _cache[6] || (_cache[6] = function ($event) {
        return $event.preventDefault();
      }),
      innerHTML: _ctx.$sanitize(_ctx.getMatchedSiteName(site.name)),
      tabindex: "4",
      href: _ctx.getUrlForSiteId(site.idsite),
      title: site.name
    }, null, 8, SiteSelectorvue_type_template_id_157b316e_hoisted_11)], 8, SiteSelectorvue_type_template_id_157b316e_hoisted_10)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(!_ctx.showSelectedSite && "".concat(_ctx.activeSiteId) === "".concat(site.idsite))]]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", SiteSelectorvue_type_template_id_157b316e_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", SiteSelectorvue_type_template_id_157b316e_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", SiteSelectorvue_type_template_id_157b316e_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_NotFound') + ' ' + _ctx.searchTerm), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.sites.length && _ctx.searchTerm]])]), _ctx.allSitesLocation === 'bottom' && _ctx.showAllSitesItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AllSitesLink, {
    href: _ctx.urlAllSites,
    "all-sites-text": _ctx.allSitesText,
    onClick: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.onAllSitesClick($event);
    })
  }, null, 8, ["href", "all-sites-text"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSitesList]])], 2)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=template&id=157b316e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=template&id=45607d28

var AllSitesLinkvue_type_template_id_45607d28_hoisted_1 = ["innerHTML", "href"];
function AllSitesLinkvue_type_template_id_45607d28_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _this.onClick($event);
    }),
    class: "custom_select_all"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return $event.preventDefault();
    }),
    innerHTML: _ctx.$sanitize(_ctx.allSitesText),
    tabindex: "4",
    href: _ctx.href
  }, null, 8, AllSitesLinkvue_type_template_id_45607d28_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=template&id=45607d28

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=script&lang=ts

/* harmony default export */ var AllSitesLinkvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    href: String,
    allSitesText: String
  },
  emits: ['click'],
  methods: {
    onClick: function onClick(event) {
      this.$emit('click', event);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue



AllSitesLinkvue_type_script_lang_ts.render = AllSitesLinkvue_type_template_id_45607d28_render

/* harmony default export */ var AllSitesLink = (AllSitesLinkvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SitesStore.ts
function SitesStore_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function SitesStore_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function SitesStore_createClass(Constructor, protoProps, staticProps) { if (protoProps) SitesStore_defineProperties(Constructor.prototype, protoProps); if (staticProps) SitesStore_defineProperties(Constructor, staticProps); return Constructor; }

function SitesStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var SitesStore_SitesStore = /*#__PURE__*/function () {
  function SitesStore() {
    var _this = this;

    SitesStore_classCallCheck(this, SitesStore);

    SitesStore_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      initialSites: [],
      isInitialized: false
    }));

    SitesStore_defineProperty(this, "stateFiltered", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      initialSites: [],
      isInitialized: false,
      excludedSites: []
    }));

    SitesStore_defineProperty(this, "currentRequestAbort", null);

    SitesStore_defineProperty(this, "limitRequest", void 0);

    SitesStore_defineProperty(this, "initialSites", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.state.initialSites);
    }));

    SitesStore_defineProperty(this, "initialSitesFiltered", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.stateFiltered.initialSites);
    }));
  }

  SitesStore_createClass(SitesStore, [{
    key: "loadInitialSites",
    value: function loadInitialSites() {
      var _this2 = this;

      var onlySitesWithAdminAccess = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var sitesToExclude = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];

      if (this.state.isInitialized && sitesToExclude.length === 0) {
        return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state.initialSites));
      } // If the filtered state has already been initialized with the same sites, return that.


      if (this.stateFiltered.isInitialized && sitesToExclude.length === this.stateFiltered.excludedSites.length && sitesToExclude.every(function (val, index) {
        return val === _this2.stateFiltered.excludedSites[index];
      })) {
        return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.stateFiltered.initialSites));
      } // If we want to exclude certain sites, perform the search for that.


      if (sitesToExclude.length > 0) {
        this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then(function (sites) {
          _this2.stateFiltered.isInitialized = true;
          _this2.stateFiltered.excludedSites = sitesToExclude;

          if (sites !== null) {
            _this2.stateFiltered.initialSites = sites;
          }
        });
      } // If the main state has already been initialized, no need to continue.


      if (this.state.isInitialized) {
        return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state.initialSites));
      }

      return this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then(function (sites) {
        _this2.state.isInitialized = true;

        if (sites !== null) {
          _this2.state.initialSites = sites;
        }

        return sites;
      });
    }
  }, {
    key: "loadSite",
    value: function loadSite(idSite) {
      if (idSite === 'all') {
        src_MatomoUrl_MatomoUrl.updateUrl(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
          module: 'MultiSites',
          action: 'index',
          date: src_MatomoUrl_MatomoUrl.parsed.value.date,
          period: src_MatomoUrl_MatomoUrl.parsed.value.period
        }));
      } else {
        src_MatomoUrl_MatomoUrl.updateUrl(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
          segment: '',
          idSite: idSite
        }), Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
          segment: '',
          idSite: idSite
        }));
      }
    }
  }, {
    key: "searchSite",
    value: function searchSite(term) {
      var _this3 = this;

      var onlySitesWithAdminAccess = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      var sitesToExclude = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];

      if (!term) {
        return this.loadInitialSites(onlySitesWithAdminAccess, sitesToExclude);
      }

      if (this.currentRequestAbort) {
        this.currentRequestAbort.abort();
      }

      if (!this.limitRequest) {
        this.limitRequest = AjaxHelper_AjaxHelper.fetch({
          method: 'SitesManager.getNumWebsitesToDisplayPerPage'
        });
      }

      return this.limitRequest.then(function (response) {
        var limit = response.value;
        var methodToCall = 'SitesManager.getPatternMatchSites';

        if (onlySitesWithAdminAccess) {
          methodToCall = 'SitesManager.getSitesWithAdminAccess';
        }

        _this3.currentRequestAbort = new AbortController();
        return AjaxHelper_AjaxHelper.fetch({
          method: methodToCall,
          limit: limit,
          pattern: term,
          sitesToExclude: sitesToExclude
        }, {
          abortController: _this3.currentRequestAbort
        });
      }).then(function (response) {
        if (response) {
          return _this3.processWebsitesList(response);
        }

        return null;
      }).finally(function () {
        _this3.currentRequestAbort = null;
      });
    }
  }, {
    key: "processWebsitesList",
    value: function processWebsitesList(response) {
      var sites = response;

      if (!sites || !sites.length) {
        return [];
      }

      sites = sites.map(function (s) {
        return Object.assign(Object.assign({}, s), {}, {
          name: s.group ? "[".concat(s.group, "] ").concat(s.name) : s.name
        });
      });
      sites.sort(function (lhs, rhs) {
        if (lhs.name.toLowerCase() < rhs.name.toLowerCase()) {
          return -1;
        }

        return lhs.name.toLowerCase() > rhs.name.toLowerCase() ? 1 : 0;
      });
      return sites;
    }
  }]);

  return SitesStore;
}();

/* harmony default export */ var SiteSelector_SitesStore = (new SitesStore_SitesStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/debounce.ts
var DEFAULT_DEBOUNCE_DELAY = 300;
function debounce(fn) {
  var delayInMs = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : DEFAULT_DEBOUNCE_DELAY;
  var timeout;
  return function wrapper() {
    var _this = this;

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    if (timeout) {
      clearTimeout(timeout);
    }

    timeout = setTimeout(function () {
      fn.call.apply(fn, [_this].concat(args));
    }, delayInMs);
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=script&lang=ts









/* harmony default export */ var SiteSelectorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Object,
    showSelectedSite: {
      type: Boolean,
      default: false
    },
    showAllSitesItem: {
      type: Boolean,
      default: true
    },
    switchSiteOnSelect: {
      type: Boolean,
      default: true
    },
    onlySitesWithAdminAccess: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      default: ''
    },
    allSitesText: {
      type: String,
      default: translate('General_MultiSitesSummary')
    },
    allSitesLocation: {
      type: String,
      default: 'bottom'
    },
    placeholder: String,
    defaultToFirstSite: Boolean,
    sitesToExclude: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  emits: ['update:modelValue', 'blur'],
  components: {
    AllSitesLink: AllSitesLink
  },
  directives: {
    FocusAnywhereButHere: FocusAnywhereButHere,
    FocusIf: FocusIf
  },
  watch: {
    searchTerm: function searchTerm() {
      this.onSearchTermChanged();
    }
  },
  data: function data() {
    return {
      searchTerm: '',
      activeSiteId: "".concat(Matomo_Matomo.idSite),
      showSitesList: false,
      isLoading: false,
      sites: [],
      autocompleteMinSites: parseInt(Matomo_Matomo.config.autocomplete_min_sites, 10)
    };
  },
  created: function created() {
    this.searchSite = debounce(this.searchSite);

    if (!this.modelValue && Matomo_Matomo.idSite) {
      this.$emit('update:modelValue', {
        id: Matomo_Matomo.idSite,
        name: Matomo_Matomo.helper.htmlDecode(Matomo_Matomo.siteName)
      });
    }
  },
  mounted: function mounted() {
    var _this = this;

    window.initTopControls();
    this.loadInitialSites().then(function () {
      if ((!_this.modelValue || !_this.modelValue.id) && (!_this.hasMultipleSites || _this.defaultToFirstSite) && _this.sites[0]) {
        _this.$emit('update:modelValue', {
          id: _this.sites[0].idsite,
          name: _this.sites[0].name
        });
      }
    });
    var shortcutTitle = translate('CoreHome_ShortcutWebsiteSelector');
    Matomo_Matomo.helper.registerShortcut('w', shortcutTitle, function (event) {
      if (event.altKey) {
        return;
      }

      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false; // IE
      }

      var selectorLink = _this.$refs.selectorLink;

      if (selectorLink) {
        selectorLink.click();
        selectorLink.focus();
      }
    });
  },
  computed: {
    shouldFocusOnSearch: function shouldFocusOnSearch() {
      return this.showSitesList && this.autocompleteMinSites <= this.sites.length || this.searchTerm;
    },
    selectorLinkTitle: function selectorLinkTitle() {
      var _this$modelValue;

      return this.hasMultipleSites ? translate('CoreHome_ChangeCurrentWebsite', ((_this$modelValue = this.modelValue) === null || _this$modelValue === void 0 ? void 0 : _this$modelValue.name) || this.firstSiteName) : '';
    },
    hasMultipleSites: function hasMultipleSites() {
      var initialSites = SiteSelector_SitesStore.initialSitesFiltered.value && SiteSelector_SitesStore.initialSitesFiltered.value.length ? SiteSelector_SitesStore.initialSitesFiltered.value : SiteSelector_SitesStore.initialSites.value;
      return initialSites && initialSites.length > 1;
    },
    firstSiteName: function firstSiteName() {
      var initialSites = SiteSelector_SitesStore.initialSitesFiltered.value && SiteSelector_SitesStore.initialSitesFiltered.value.length ? SiteSelector_SitesStore.initialSitesFiltered.value : SiteSelector_SitesStore.initialSites.value;
      return initialSites && initialSites.length > 0 ? initialSites[0].name : '';
    },
    urlAllSites: function urlAllSites() {
      var newQuery = src_MatomoUrl_MatomoUrl.stringify(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        module: 'MultiSites',
        action: 'index',
        date: src_MatomoUrl_MatomoUrl.parsed.value.date,
        period: src_MatomoUrl_MatomoUrl.parsed.value.period
      }));
      return "?".concat(newQuery);
    }
  },
  methods: {
    onSearchTermChanged: function onSearchTermChanged() {
      if (!this.searchTerm) {
        this.isLoading = false;
        this.loadInitialSites();
      } else {
        this.isLoading = true;
        this.searchSite(this.searchTerm);
      }
    },
    onAllSitesClick: function onAllSitesClick(event) {
      this.switchSite({
        id: 'all',
        name: this.$props.allSitesText
      }, event);
      this.showSitesList = false;
    },
    switchSite: function switchSite(site, event) {
      // for Mac OS cmd key needs to be pressed, ctrl key on other systems
      var controlKey = navigator.userAgent.indexOf('Mac OS X') !== -1 ? event.metaKey : event.ctrlKey;

      if (event && controlKey && event.target && event.target.href) {
        window.open(event.target.href, '_blank');
        return;
      }

      this.$emit('update:modelValue', {
        id: site.id,
        name: site.name
      });

      if (!this.switchSiteOnSelect || this.activeSiteId === site.id) {
        return;
      }

      SiteSelector_SitesStore.loadSite(site.id);
    },
    onBlur: function onBlur() {
      this.showSitesList = false;
      this.$emit('blur');
    },
    onClickSelector: function onClickSelector() {
      if (this.hasMultipleSites) {
        this.showSitesList = !this.showSitesList;

        if (!this.isLoading && !this.searchTerm) {
          this.loadInitialSites();
        }
      }
    },
    onPressEnter: function onPressEnter(event) {
      if (event.key !== 'Enter') {
        return;
      }

      event.preventDefault();
      this.showSitesList = !this.showSitesList;

      if (this.showSitesList && !this.isLoading) {
        this.loadInitialSites();
      }
    },
    getMatchedSiteName: function getMatchedSiteName(siteName) {
      var index = siteName.toUpperCase().indexOf(this.searchTerm.toUpperCase());

      if (index === -1 || this.isLoading // only highlight when we know the displayed results are for a search
      ) {
        return Matomo_Matomo.helper.htmlEntities(siteName);
      }

      var previousPart = Matomo_Matomo.helper.htmlEntities(siteName.substring(0, index));
      var lastPart = Matomo_Matomo.helper.htmlEntities(siteName.substring(index + this.searchTerm.length));
      return "".concat(previousPart, "<span class=\"autocompleteMatched\">").concat(this.searchTerm, "</span>").concat(lastPart);
    },
    loadInitialSites: function loadInitialSites() {
      var _this2 = this;

      return SiteSelector_SitesStore.loadInitialSites(this.onlySitesWithAdminAccess, this.sitesToExclude ? this.sitesToExclude : []).then(function (sites) {
        _this2.sites = sites || [];
      });
    },
    searchSite: function searchSite(term) {
      var _this3 = this;

      this.isLoading = true;
      SiteSelector_SitesStore.searchSite(term, this.onlySitesWithAdminAccess, this.sitesToExclude ? this.sitesToExclude : []).then(function (sites) {
        if (term !== _this3.searchTerm) {
          return; // search term changed in the meantime
        }

        if (sites) {
          _this3.sites = sites;
        }
      }).finally(function () {
        _this3.isLoading = false;
      });
    },
    getUrlForSiteId: function getUrlForSiteId(idSite) {
      var newQuery = src_MatomoUrl_MatomoUrl.stringify(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        segment: '',
        idSite: idSite
      }));
      var newHash = src_MatomoUrl_MatomoUrl.stringify(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
        segment: '',
        idSite: idSite
      }));
      return "?".concat(newQuery, "#?").concat(newHash);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue



SiteSelectorvue_type_script_lang_ts.render = SiteSelectorvue_type_template_id_157b316e_render

/* harmony default export */ var SiteSelector = (SiteSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




/* harmony default export */ var SiteSelector_adapter = (createAngularJsAdapter({
  component: SiteSelector,
  require: '?ngModel',
  scope: {
    showSelectedSite: {
      angularJsBind: '='
    },
    showAllSitesItem: {
      angularJsBind: '='
    },
    switchSiteOnSelect: {
      angularJsBind: '='
    },
    onlySitesWithAdminAccess: {
      angularJsBind: '='
    },
    name: {
      angularJsBind: '@'
    },
    allSitesText: {
      angularJsBind: '@'
    },
    allSitesLocation: {
      angularJsBind: '@'
    },
    placeholder: {
      angularJsBind: '@'
    },
    modelValue: {
      default: function _default(scope, element, attrs) {
        if (attrs.siteid && attrs.sitename) {
          return {
            id: attrs.siteid,
            name: Matomo_Matomo.helper.htmlDecode(attrs.sitename)
          };
        }

        if (Matomo_Matomo.idSite) {
          return {
            id: Matomo_Matomo.idSite,
            name: Matomo_Matomo.helper.htmlDecode(Matomo_Matomo.siteName)
          };
        }

        return undefined;
      }
    }
  },
  $inject: ['$timeout'],
  directiveName: 'piwikSiteselector',
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel, $timeout) {
      if (newValue && !vm.modelValue || !newValue && vm.modelValue || newValue.id !== vm.modelValue.id) {
        $timeout(function () {
          scope.value = newValue;
          element.attr('siteid', newValue.id);
          element.trigger('change', newValue);

          if (ngModel) {
            ngModel.$setViewValue(newValue);
            ngModel.$render(); // not called automatically by the digest
          }
        });
      }
    },
    blur: function blur(event, vm, scope) {
      setTimeout(function () {
        return scope.$apply();
      });
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller;
    scope.$watch('value', function (newVal) {
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        if (newVal !== vm.modelValue) {
          vm.modelValue = newVal;
        }
      });
    });

    if (attrs.siteid && attrs.sitename) {
      scope.value = {
        id: attrs.siteid,
        name: Matomo_Matomo.helper.htmlDecode(attrs.sitename)
      };
      vm.modelValue = scope.value;
    } else if (Matomo_Matomo.idSite) {
      scope.value = {
        id: Matomo_Matomo.idSite,
        name: Matomo_Matomo.helper.htmlDecode(Matomo_Matomo.siteName)
      };
      vm.modelValue = scope.value;
    } // setup ng-model mapping


    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = function () {
        Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
          Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
            if (window.angular.isString(ngModel.$viewValue)) {
              vm.modelValue = JSON.parse(ngModel.$viewValue);
            } else {
              vm.modelValue = ngModel.$viewValue;
            }
          });
        });
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SitesStore.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



function siteSelectorModelAdapter() {
  return {
    get initialSites() {
      return SiteSelector_SitesStore.initialSites.value;
    },

    loadSite: SiteSelector_SitesStore.loadSite.bind(SiteSelector_SitesStore),
    loadInitialSites: function loadInitialSites() {
      return cloneThenApply(SiteSelector_SitesStore.loadInitialSites());
    },
    searchSite: function searchSite() {
      return cloneThenApply(SiteSelector_SitesStore.searchSite.apply(SiteSelector_SitesStore, arguments));
    }
  };
}

window.angular.module('piwikApp.service').factory('siteSelectorModel', siteSelectorModelAdapter);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=template&id=6bbc907a

var QuickAccessvue_type_template_id_6bbc907a_hoisted_1 = {
  ref: "root",
  class: "quickAccessInside"
};
var QuickAccessvue_type_template_id_6bbc907a_hoisted_2 = ["title", "placeholder"];
var QuickAccessvue_type_template_id_6bbc907a_hoisted_3 = {
  class: "dropdown"
};
var QuickAccessvue_type_template_id_6bbc907a_hoisted_4 = {
  class: "no-result"
};
var QuickAccessvue_type_template_id_6bbc907a_hoisted_5 = ["onClick"];
var QuickAccessvue_type_template_id_6bbc907a_hoisted_6 = ["onMouseenter", "onClick"];
var QuickAccessvue_type_template_id_6bbc907a_hoisted_7 = {
  class: "quickAccessMatomoSearch"
};
var QuickAccessvue_type_template_id_6bbc907a_hoisted_8 = ["onMouseenter", "onClick"];
var QuickAccessvue_type_template_id_6bbc907a_hoisted_9 = ["textContent"];
var QuickAccessvue_type_template_id_6bbc907a_hoisted_10 = {
  class: "quick-access-category helpCategory"
};
var QuickAccessvue_type_template_id_6bbc907a_hoisted_11 = ["href"];
function QuickAccessvue_type_template_id_6bbc907a_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", QuickAccessvue_type_template_id_6bbc907a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search",
    onMouseenter: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.searchActive = true;
    })
  }, null, 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "s",
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeypress($event);
    }),
    onFocus: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.searchActive = true;
    }),
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    type: "text",
    tabindex: "2",
    title: _ctx.quickAccessTitle,
    placeholder: _ctx.translate('General_Search'),
    ref: "input"
  }, null, 40, QuickAccessvue_type_template_id_6bbc907a_hoisted_2), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.searchActive
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", QuickAccessvue_type_template_id_6bbc907a_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", QuickAccessvue_type_template_id_6bbc907a_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_SearchNoResults')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.numMenuItems > 0 || _ctx.sites.length)]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menuItems, function (subcategory) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
      key: subcategory.title
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      class: "quick-access-category",
      onClick: function onClick($event) {
        _ctx.searchTerm = subcategory.title;

        _ctx.searchMenu(_ctx.searchTerm);
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.title), 9, QuickAccessvue_type_template_id_6bbc907a_hoisted_5), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.items, function (submenuEntry) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["result", {
          selected: submenuEntry.menuIndex === _ctx.searchIndex
        }]),
        onMouseenter: function onMouseenter($event) {
          return _ctx.searchIndex = submenuEntry.menuIndex;
        },
        onClick: function onClick($event) {
          return _ctx.selectMenuItem(submenuEntry.index);
        },
        key: submenuEntry.index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(submenuEntry.name.trim()), 1)], 42, QuickAccessvue_type_template_id_6bbc907a_hoisted_6);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", QuickAccessvue_type_template_id_6bbc907a_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: "quick-access-category websiteCategory"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Sites')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && _ctx.sites.length || _ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: "no-result"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_LoadingWebsites')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && _ctx.isLoading]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, function (site, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["result", {
        selected: _ctx.numMenuItems + index === _ctx.searchIndex
      }]),
      onMouseenter: function onMouseenter($event) {
        return _ctx.searchIndex = _ctx.numMenuItems + index;
      },
      onClick: function onClick($event) {
        return _ctx.selectSite(site.idsite);
      },
      key: site.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(site.name)
    }, null, 8, QuickAccessvue_type_template_id_6bbc907a_hoisted_9)], 42, QuickAccessvue_type_template_id_6bbc907a_hoisted_8)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && !_ctx.isLoading]]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", QuickAccessvue_type_template_id_6bbc907a_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_HelpResources')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
      selected: _ctx.searchIndex === 'help'
    }, "quick-access-help"]),
    onMouseenter: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.searchIndex = 'help';
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=QuickSearch&s=".concat(encodeURIComponent(_ctx.searchTerm)),
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_SearchOnMatomo', _ctx.searchTerm)), 9, QuickAccessvue_type_template_id_6bbc907a_hoisted_11)], 34)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm && _ctx.searchActive]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=template&id=6bbc907a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=script&lang=ts








function isElementInViewport(element) {
  var rect = element.getBoundingClientRect();
  var $window = window.$(window);
  return rect.top >= 0 && rect.left >= 0 && rect.bottom <= $window.height() && rect.right <= $window.width();
}

function scrollFirstElementIntoView(element) {
  if (element && element.scrollIntoView) {
    // make sure search is visible
    element.scrollIntoView();
  }
}

/* harmony default export */ var QuickAccessvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    FocusAnywhereButHere: FocusAnywhereButHere,
    FocusIf: FocusIf
  },
  watch: {
    searchActive: function searchActive(newValue) {
      var root = this.$refs.root;

      if (!root || !root.parentElement) {
        return;
      }

      var classes = root.parentElement.classList;
      classes.toggle('active', newValue);
      classes.toggle('expanded', newValue);
    }
  },
  mounted: function mounted() {
    var _this = this;

    var root = this.$refs.root; // TODO: temporary, remove after angularjs is removed.
    // this is currently needed since angularjs will render a div, then vue will render a div
    // within it, but the top controls and CSS expect to have certain CSS classes in the root
    // element.
    // same applies to above watch for searchActive()

    if (root && root.parentElement) {
      root.parentElement.classList.add('quick-access', 'piwikSelector');
    }

    if (typeof window.initTopControls !== 'undefined' && window.initTopControls) {
      window.initTopControls();
    }

    Matomo_Matomo.helper.registerShortcut('f', translate('CoreHome_ShortcutSearch'), function (event) {
      if (event.altKey) {
        return;
      }

      event.preventDefault();
      scrollFirstElementIntoView(_this.$refs.root);

      _this.activateSearch();
    });
  },
  data: function data() {
    var hasSegmentSelector = !!document.querySelector('.segmentEditorPanel');
    return {
      menuItems: [],
      numMenuItems: 0,
      searchActive: false,
      searchTerm: '',
      searchIndex: 0,
      menuIndexCounter: -1,
      topMenuItems: null,
      leftMenuItems: null,
      segmentItems: null,
      hasSegmentSelector: hasSegmentSelector,
      sites: [],
      isLoading: false
    };
  },
  created: function created() {
    this.searchMenu = debounce(this.searchMenu.bind(this));
  },
  computed: {
    hasSitesSelector: function hasSitesSelector() {
      return !!document.querySelector('.top_controls [piwik-siteselector]');
    },
    quickAccessTitle: function quickAccessTitle() {
      var searchAreasTitle = '';
      var searchAreas = [translate('CoreHome_MenuEntries')];

      if (this.hasSegmentSelector) {
        searchAreas.push(translate('CoreHome_Segments'));
      }

      if (this.hasSitesSelector) {
        searchAreas.push(translate('SitesManager_Sites'));
      }

      while (searchAreas.length) {
        searchAreasTitle += searchAreas.shift();

        if (searchAreas.length >= 2) {
          searchAreasTitle += ', ';
        } else if (searchAreas.length === 1) {
          searchAreasTitle += " ".concat(translate('General_And'), " ");
        }
      }

      return translate('CoreHome_QuickAccessTitle', searchAreasTitle);
    }
  },
  emits: ['itemSelected', 'blur'],
  methods: {
    onKeypress: function onKeypress(event) {
      var _this2 = this;

      var areSearchResultsDisplayed = this.searchTerm && this.searchActive;
      var isTabKey = event.which === 9;
      var isEscKey = event.which === 27;

      if (event.which === 38) {
        this.highlightPreviousItem();
        event.preventDefault();
      } else if (event.which === 40) {
        this.highlightNextItem();
        event.preventDefault();
      } else if (event.which === 13) {
        this.clickQuickAccessMenuItem();
      } else if (isTabKey && areSearchResultsDisplayed) {
        this.deactivateSearch();
      } else if (isEscKey && areSearchResultsDisplayed) {
        this.deactivateSearch();
      } else {
        setTimeout(function () {
          _this2.searchActive = true;

          _this2.searchMenu(_this2.searchTerm);
        });
      }
    },
    highlightPreviousItem: function highlightPreviousItem() {
      if (this.searchIndex - 1 < 0) {
        this.searchIndex = 0;
      } else {
        this.searchIndex -= 1;
      }

      this.makeSureSelectedItemIsInViewport();
    },
    highlightNextItem: function highlightNextItem() {
      var numTotal = this.$refs.root.querySelectorAll('li.result').length;

      if (numTotal <= this.searchIndex + 1) {
        this.searchIndex = numTotal - 1;
      } else {
        this.searchIndex += 1;
      }

      this.makeSureSelectedItemIsInViewport();
    },
    clickQuickAccessMenuItem: function clickQuickAccessMenuItem() {
      var _this3 = this;

      var selectedMenuElement = this.getCurrentlySelectedElement();

      if (selectedMenuElement) {
        setTimeout(function () {
          selectedMenuElement.click();

          _this3.$emit('itemSelected', selectedMenuElement);
        }, 20);
      }
    },
    deactivateSearch: function deactivateSearch() {
      this.searchTerm = '';
      this.searchActive = false;

      if (this.$refs.input) {
        this.$refs.input.blur();
      }
    },
    makeSureSelectedItemIsInViewport: function makeSureSelectedItemIsInViewport() {
      var element = this.getCurrentlySelectedElement();

      if (element && !isElementInViewport(element)) {
        scrollFirstElementIntoView(element);
      }
    },
    getCurrentlySelectedElement: function getCurrentlySelectedElement() {
      var results = this.$refs.root.querySelectorAll('li.result');

      if (results && results.length && results.item(this.searchIndex)) {
        return results.item(this.searchIndex);
      }

      return undefined;
    },
    searchMenu: function searchMenu(unprocessedSearchTerm) {
      var _this4 = this;

      var searchTerm = unprocessedSearchTerm.toLowerCase();
      var index = -1;
      var menuItemsIndex = {};
      var menuItems = [];

      var moveToCategory = function moveToCategory(theSubmenuItem) {
        // force rerender of element to prevent weird side effects
        var submenuItem = Object.assign({}, theSubmenuItem); // needed for proper highlighting with arrow keys

        index += 1;
        submenuItem.menuIndex = index;
        var category = submenuItem.category;

        if (!(category in menuItemsIndex)) {
          menuItems.push({
            title: category,
            items: []
          });
          menuItemsIndex[category] = menuItems.length - 1;
        }

        var indexOfCategory = menuItemsIndex[category];
        menuItems[indexOfCategory].items.push(submenuItem);
      };

      this.resetSearchIndex();

      if (this.hasSitesSelector) {
        this.isLoading = true;
        SiteSelector_SitesStore.searchSite(searchTerm).then(function (sites) {
          if (sites) {
            _this4.sites = sites;
          }
        }).finally(function () {
          _this4.isLoading = false;
        });
      }

      var menuItemMatches = function menuItemMatches(i) {
        return i.name.toLowerCase().indexOf(searchTerm) !== -1 || i.category.toLowerCase().indexOf(searchTerm) !== -1;
      }; // get the menu items on first search since this component can be mounted
      // before the menus are


      if (this.topMenuItems === null) {
        this.topMenuItems = this.getTopMenuItems();
      }

      if (this.leftMenuItems === null) {
        this.leftMenuItems = this.getLeftMenuItems();
      }

      if (this.segmentItems === null) {
        this.segmentItems = this.getSegmentItems();
      }

      var topMenuItems = this.topMenuItems.filter(menuItemMatches);
      var leftMenuItems = this.leftMenuItems.filter(menuItemMatches);
      var segmentItems = this.segmentItems.filter(menuItemMatches);
      topMenuItems.forEach(moveToCategory);
      leftMenuItems.forEach(moveToCategory);
      segmentItems.forEach(moveToCategory);
      this.numMenuItems = topMenuItems.length + leftMenuItems.length + segmentItems.length;
      this.menuItems = menuItems;
    },
    resetSearchIndex: function resetSearchIndex() {
      this.searchIndex = 0;
      this.makeSureSelectedItemIsInViewport();
    },
    selectSite: function selectSite(idSite) {
      SiteSelector_SitesStore.loadSite(idSite);
    },
    selectMenuItem: function selectMenuItem(index) {
      var target = document.querySelector("[quick_access='".concat(index, "']"));

      if (target) {
        this.deactivateSearch();
        var href = target.getAttribute('href');

        if (href && href.length > 10 && target && target.click) {
          try {
            target.click();
          } catch (e) {
            window.$(target).click();
          }
        } else {
          // not sure why jquery is used here and above, but only sometimes. keeping for BC.
          window.$(target).click();
        }
      }
    },
    onBlur: function onBlur() {
      this.searchActive = false;
      this.$emit('blur');
    },
    activateSearch: function activateSearch() {
      this.searchActive = true;
    },
    getTopMenuItems: function getTopMenuItems() {
      var _this5 = this;

      var category = translate('CoreHome_Menu');
      var topMenuItems = [];
      document.querySelectorAll('nav .sidenav li > a').forEach(function (element) {
        var _element$textContent;

        var text = (_element$textContent = element.textContent) === null || _element$textContent === void 0 ? void 0 : _element$textContent.trim();

        if (!text) {
          var _element$getAttribute;

          text = (_element$getAttribute = element.getAttribute('title')) === null || _element$getAttribute === void 0 ? void 0 : _element$getAttribute.trim(); // possibly a icon, use title instead
        }

        if (text) {
          topMenuItems.push({
            name: text,
            index: _this5.menuIndexCounter += 1,
            category: category
          });
          element.setAttribute('quick_access', "".concat(_this5.menuIndexCounter));
        }
      });
      return topMenuItems;
    },
    getLeftMenuItems: function getLeftMenuItems() {
      var _this6 = this;

      var leftMenuItems = [];
      document.querySelectorAll('#secondNavBar .menuTab').forEach(function (element) {
        var _categoryElement$;

        var categoryElement = window.$(element).find('> .item');
        var category = ((_categoryElement$ = categoryElement[0]) === null || _categoryElement$ === void 0 ? void 0 : _categoryElement$.innerText.trim()) || '';

        if (category && category.lastIndexOf('\n') !== -1) {
          // remove "\n\nMenu"
          category = category.slice(0, category.lastIndexOf('\n')).trim();
        }

        window.$(element).find('li .item').each(function (i, subElement) {
          var _subElement$textConte;

          var text = (_subElement$textConte = subElement.textContent) === null || _subElement$textConte === void 0 ? void 0 : _subElement$textConte.trim();

          if (text) {
            leftMenuItems.push({
              name: text,
              category: category,
              index: _this6.menuIndexCounter += 1
            });
            subElement.setAttribute('quick_access', "".concat(_this6.menuIndexCounter));
          }
        });
      });
      return leftMenuItems;
    },
    getSegmentItems: function getSegmentItems() {
      var _this7 = this;

      if (!this.hasSegmentSelector) {
        return [];
      }

      var category = translate('CoreHome_Segments');
      var segmentItems = [];
      document.querySelectorAll('.segmentList [data-idsegment]').forEach(function (element) {
        var _element$querySelecto, _element$querySelecto2;

        var text = (_element$querySelecto = element.querySelector('.segname')) === null || _element$querySelecto === void 0 ? void 0 : (_element$querySelecto2 = _element$querySelecto.textContent) === null || _element$querySelecto2 === void 0 ? void 0 : _element$querySelecto2.trim();

        if (text) {
          segmentItems.push({
            name: text,
            category: category,
            index: _this7.menuIndexCounter += 1
          });
          element.setAttribute('quick_access', "".concat(_this7.menuIndexCounter));
        }
      });
      return segmentItems;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue



QuickAccessvue_type_script_lang_ts.render = QuickAccessvue_type_template_id_6bbc907a_render

/* harmony default export */ var QuickAccess = (QuickAccessvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var QuickAccess_adapter = (createAngularJsAdapter({
  component: QuickAccess,
  directiveName: 'piwikQuickAccess',
  events: {
    itemSelected: function itemSelected(event, vm, scope, elem, attrs, controller, $timeout) {
      $timeout();
    },
    blur: function blur(event, vm, scope) {
      setTimeout(function () {
        return scope.$apply();
      });
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=template&id=02c82ed4
function FieldArrayvue_type_template_id_02c82ed4_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


var FieldArrayvue_type_template_id_02c82ed4_hoisted_1 = {
  class: "fieldArray form-group"
};
var FieldArrayvue_type_template_id_02c82ed4_hoisted_2 = {
  key: 0,
  class: "fieldUiControl"
};
var FieldArrayvue_type_template_id_02c82ed4_hoisted_3 = ["onClick", "title"];
function FieldArrayvue_type_template_id_02c82ed4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldArrayvue_type_template_id_02c82ed4_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.modelValue, function (item, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fieldArrayTable multiple valign-wrapper", FieldArrayvue_type_template_id_02c82ed4_defineProperty({}, "fieldArrayTable".concat(index), true)]),
      key: index
    }, [_ctx.field.uiControl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldArrayvue_type_template_id_02c82ed4_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      "model-value": item,
      options: _ctx.field.availableValues,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.onEntryChange($event, index);
      },
      "model-modifiers": _ctx.field.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field.uiControl,
      title: _ctx.field.title,
      name: "".concat(_ctx.name, "-").concat(index),
      "template-file": _ctx.field.templateFile,
      component: _ctx.field.component
    }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "title", "name", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      onClick: function onClick($event) {
        return _ctx.removeEntry(index);
      },
      class: "icon-minus valign",
      title: _ctx.translate('General_Remove')
    }, null, 8, FieldArrayvue_type_template_id_02c82ed4_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.modelValue.length]])], 2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=template&id=02c82ed4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=script&lang=ts
function FieldArrayvue_type_script_lang_ts_toConsumableArray(arr) { return FieldArrayvue_type_script_lang_ts_arrayWithoutHoles(arr) || FieldArrayvue_type_script_lang_ts_iterableToArray(arr) || FieldArrayvue_type_script_lang_ts_unsupportedIterableToArray(arr) || FieldArrayvue_type_script_lang_ts_nonIterableSpread(); }

function FieldArrayvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldArrayvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldArrayvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldArrayvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function FieldArrayvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function FieldArrayvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return FieldArrayvue_type_script_lang_ts_arrayLikeToArray(arr); }

function FieldArrayvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }


 // async since this is a a recursive component

var Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
/* harmony default export */ var FieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    name: String,
    field: Object
  },
  components: {
    Field: Field
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue: function modelValue(newValue) {
      this.checkEmptyModelValue(newValue);
    }
  },
  mounted: function mounted() {
    this.checkEmptyModelValue(this.modelValue);
  },
  methods: {
    checkEmptyModelValue: function checkEmptyModelValue(newValue) {
      // make sure there is always an empty new value
      if (!newValue || !newValue.length || newValue.slice(-1)[0] !== '') {
        this.$emit('update:modelValue', [].concat(FieldArrayvue_type_script_lang_ts_toConsumableArray(newValue || []), ['']));
      }
    },
    onEntryChange: function onEntryChange(newValue, index) {
      var newArrayValue = FieldArrayvue_type_script_lang_ts_toConsumableArray(this.modelValue || []);

      newArrayValue[index] = newValue;
      this.$emit('update:modelValue', newArrayValue);
    },
    removeEntry: function removeEntry(index) {
      if (index > -1 && this.modelValue) {
        var newValue = this.modelValue.filter(function (x, i) {
          return i !== index;
        });
        this.$emit('update:modelValue', newValue);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue



FieldArrayvue_type_script_lang_ts.render = FieldArrayvue_type_template_id_02c82ed4_render

/* harmony default export */ var FieldArray = (FieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var FieldArray_adapter = (createAngularJsAdapter({
  component: FieldArray,
  require: '?ngModel',
  scope: {
    name: {
      angularJsBind: '='
    },
    field: {
      angularJsBind: '='
    }
  },
  directiveName: 'matomoFieldArray',
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel) {
      if (newValue !== vm.modelValue) {
        element.trigger('change', newValue);

        if (ngModel) {
          ngModel.$setViewValue(newValue);
        }
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller; // setup ng-model mapping

    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = function () {
        if (window.angular.isString(ngModel.$viewValue)) {
          vm.modelValue = JSON.parse(ngModel.$viewValue);
        } else {
          vm.modelValue = ngModel.$viewValue;
        }
      };
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=template&id=340e896b
function MultiPairFieldvue_type_template_id_340e896b_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


var MultiPairFieldvue_type_template_id_340e896b_hoisted_1 = {
  class: "multiPairField form-group"
};
var MultiPairFieldvue_type_template_id_340e896b_hoisted_2 = {
  key: 1,
  class: "fieldUiControl fieldUiControl2"
};
var MultiPairFieldvue_type_template_id_340e896b_hoisted_3 = {
  key: 2,
  class: "fieldUiControl fieldUiControl3"
};
var MultiPairFieldvue_type_template_id_340e896b_hoisted_4 = {
  key: 3,
  class: "fieldUiControl fieldUiControl4"
};
var MultiPairFieldvue_type_template_id_340e896b_hoisted_5 = ["onClick", "title"];
function MultiPairFieldvue_type_template_id_340e896b_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_340e896b_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.modelValue, function (item, index) {
    var _ref;

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multiPairFieldTable multiple valign-wrapper", (_ref = {}, MultiPairFieldvue_type_template_id_340e896b_defineProperty(_ref, "multiPairFieldTable".concat(index), true), MultiPairFieldvue_type_template_id_340e896b_defineProperty(_ref, "has".concat(_ctx.fieldCount, "Fields"), true), _ref)]),
      key: index
    }, [_ctx.field1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fieldUiControl fieldUiControl1", {
        hasMultiFields: _ctx.field1.type && _ctx.field2.type
      }])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      "model-value": item[_ctx.field1.key],
      options: _ctx.field1.availableValues,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.onEntryChange(index, _ctx.field1.key, $event);
      },
      "model-modifiers": _ctx.field1.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field1.uiControl,
      name: "".concat(_ctx.name, "-p1-").concat(index),
      title: _ctx.field1.title,
      "template-file": _ctx.field1.templateFile,
      component: _ctx.field1.component
    }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "name", "title", "template-file", "component"])], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_340e896b_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field2.availableValues,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.onEntryChange(index, _ctx.field2.key, $event);
      },
      "model-value": item[_ctx.field2.key],
      "model-modifiers": _ctx.field2.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field2.uiControl,
      name: "".concat(_ctx.name, "-p2-").concat(index),
      title: _ctx.field2.title,
      "template-file": _ctx.field2.templateFile,
      component: _ctx.field2.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "name", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field3 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_340e896b_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field3.availableValues,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.onEntryChange(index, _ctx.field3.key, $event);
      },
      "model-value": item[_ctx.field3.key],
      "model-modifiers": _ctx.field3.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field3.uiControl,
      title: _ctx.field3.title,
      "template-file": _ctx.field3.templateFile,
      component: _ctx.field3.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field4 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_340e896b_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field4.availableValues,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.onEntryChange(index, _ctx.field4.key, $event);
      },
      "model-value": item[_ctx.field4.key],
      "model-modifiers": _ctx.field4.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field4.uiControl,
      title: _ctx.field4.title,
      "template-file": _ctx.field4.templateFile,
      component: _ctx.field4.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      onClick: function onClick($event) {
        return _ctx.removeEntry(index);
      },
      class: "icon-minus valign",
      title: _ctx.translate('General_Remove')
    }, null, 8, MultiPairFieldvue_type_template_id_340e896b_hoisted_5), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.modelValue.length]])], 2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=template&id=340e896b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=script&lang=ts
function MultiPairFieldvue_type_script_lang_ts_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function MultiPairFieldvue_type_script_lang_ts_toConsumableArray(arr) { return MultiPairFieldvue_type_script_lang_ts_arrayWithoutHoles(arr) || MultiPairFieldvue_type_script_lang_ts_iterableToArray(arr) || MultiPairFieldvue_type_script_lang_ts_unsupportedIterableToArray(arr) || MultiPairFieldvue_type_script_lang_ts_nonIterableSpread(); }

function MultiPairFieldvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function MultiPairFieldvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return MultiPairFieldvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return MultiPairFieldvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function MultiPairFieldvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function MultiPairFieldvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return MultiPairFieldvue_type_script_lang_ts_arrayLikeToArray(arr); }

function MultiPairFieldvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }


 // async since this is a a recursive component

var MultiPairFieldvue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
/* harmony default export */ var MultiPairFieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    name: String,
    field1: Object,
    field2: Object,
    field3: Object,
    field4: Object
  },
  components: {
    Field: MultiPairFieldvue_type_script_lang_ts_Field
  },
  computed: {
    fieldCount: function fieldCount() {
      if (this.field1 && this.field2 && this.field3 && this.field4) {
        return 4;
      }

      if (this.field1 && this.field2 && this.field3) {
        return 3;
      }

      if (this.field1 && this.field2) {
        return 2;
      }

      if (this.field1) {
        return 1;
      }

      return 0;
    }
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue: function modelValue(newValue) {
      this.checkEmptyModelValue(newValue);
    }
  },
  mounted: function mounted() {
    this.checkEmptyModelValue(this.modelValue);
  },
  methods: {
    checkEmptyModelValue: function checkEmptyModelValue(newValue) {
      // make sure there is always an empty new value
      if (!newValue || !newValue.length || this.isEmptyValue(newValue.slice(-1)[0])) {
        this.$emit('update:modelValue', [].concat(MultiPairFieldvue_type_script_lang_ts_toConsumableArray(newValue || []), [this.makeEmptyValue()]));
      }
    },
    onEntryChange: function onEntryChange(index, key, newValue) {
      var newWholeValue = MultiPairFieldvue_type_script_lang_ts_toConsumableArray(this.modelValue);

      newWholeValue[index] = Object.assign(Object.assign({}, newWholeValue[index]), {}, MultiPairFieldvue_type_script_lang_ts_defineProperty({}, key, newValue));
      this.$emit('update:modelValue', newWholeValue);
    },
    removeEntry: function removeEntry(index) {
      if (index > -1 && this.modelValue) {
        var newValue = this.modelValue.filter(function (x, i) {
          return i !== index;
        });
        this.$emit('update:modelValue', newValue);
      }
    },
    isEmptyValue: function isEmptyValue(value) {
      var fieldCount = this.fieldCount;

      if (fieldCount === 4) {
        if (!value[this.field1.key] && !value[this.field2.key] && !value[this.field3.key] && !value[this.field4.key]) {
          return false;
        }
      } else if (fieldCount === 3) {
        if (!value[this.field1.key] && !value[this.field2.key] && !value[this.field3.key]) {
          return false;
        }
      } else if (fieldCount === 2) {
        if (!value[this.field1.key] && !value[this.field2.key]) {
          return false;
        }
      } else if (fieldCount === 1) {
        if (!value[this.field1.key]) {
          return false;
        }
      }

      return true;
    },
    makeEmptyValue: function makeEmptyValue() {
      var result = {};

      if (this.field1 && this.field1.key) {
        result[this.field1.key] = '';
      }

      if (this.field2 && this.field2.key) {
        result[this.field2.key] = '';
      }

      if (this.field3 && this.field3.key) {
        result[this.field3.key] = '';
      }

      if (this.field4 && this.field4.key) {
        result[this.field4.key] = '';
      }

      return result;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue



MultiPairFieldvue_type_script_lang_ts.render = MultiPairFieldvue_type_template_id_340e896b_render

/* harmony default export */ var MultiPairField = (MultiPairFieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var MultiPairField_adapter = (createAngularJsAdapter({
  component: MultiPairField,
  require: '?ngModel',
  scope: {
    name: {
      angularJsBind: '='
    },
    field1: {
      angularJsBind: '='
    },
    field2: {
      angularJsBind: '='
    },
    field3: {
      angularJsBind: '='
    },
    field4: {
      angularJsBind: '='
    }
  },
  directiveName: 'matomoMultiPairField',
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel) {
      if (newValue !== vm.modelValue) {
        element.trigger('change', newValue);

        if (ngModel) {
          ngModel.$setViewValue(newValue);
        }
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller; // setup ng-model mapping

    if (ngModel) {
      ngModel.$setViewValue(vm.modelValue);

      ngModel.$render = function () {
        if (window.angular.isString(ngModel.$viewValue)) {
          vm.modelValue = JSON.parse(ngModel.$viewValue);
        } else {
          vm.modelValue = ngModel.$viewValue;
        }
      };
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=template&id=155889f6

var PeriodSelectorvue_type_template_id_155889f6_hoisted_1 = {
  ref: "root",
  class: "periodSelector piwikSelector"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_2 = ["title"];

var PeriodSelectorvue_type_template_id_155889f6_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-calendar"
}, null, -1);

var PeriodSelectorvue_type_template_id_155889f6_hoisted_4 = {
  id: "periodMore",
  class: "dropdown"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_5 = {
  class: "flex"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_6 = {
  key: 0,
  class: "period-date"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_7 = {
  class: "period-type"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_8 = {
  id: "otherPeriods"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_9 = ["onDblclick", "title"];
var PeriodSelectorvue_type_template_id_155889f6_hoisted_10 = ["id", "checked", "onChange", "onDblclick"];
var PeriodSelectorvue_type_template_id_155889f6_hoisted_11 = {
  key: 0,
  class: "compare-checkbox"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_12 = {
  id: "comparePeriodToDropdown"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_13 = {
  key: 1,
  class: "compare-date-range"
};
var PeriodSelectorvue_type_template_id_155889f6_hoisted_14 = {
  id: "comparePeriodStartDate"
};

var PeriodSelectorvue_type_template_id_155889f6_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "compare-dates-separator"
}, null, -1);

var _hoisted_16 = {
  id: "comparePeriodEndDate"
};
var _hoisted_17 = {
  class: "apply-button-container"
};
var _hoisted_18 = ["disabled", "value"];
var _hoisted_19 = {
  key: 2,
  id: "ajaxLoadingCalendar"
};
var _hoisted_20 = {
  class: "loadingSegment"
};
function PeriodSelectorvue_type_template_id_155889f6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DateRangePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DateRangePicker");

  var _component_PeriodDatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PeriodDatePicker");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _directive_expand_on_click = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-click");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    ref: "title",
    id: "date",
    class: "title",
    tabindex: "-1",
    title: _ctx.translate('General_ChooseDate', _ctx.currentlyViewingText)
  }, [PeriodSelectorvue_type_template_id_155889f6_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentlyViewingText), 1)], 8, PeriodSelectorvue_type_template_id_155889f6_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DateRangePicker, {
    class: "period-range",
    "start-date": _ctx.startRangeDate,
    "end-date": _ctx.endRangeDate,
    onRangeChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onRangeChange($event.start, $event.end);
    }),
    onSubmit: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onApplyClicked();
    })
  }, null, 8, ["start-date", "end-date"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.selectedPeriod === 'range']]), _ctx.selectedPeriod !== 'range' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PeriodDatePicker, {
    id: "datepicker",
    period: _ctx.selectedPeriod,
    date: _ctx.periodValue === _ctx.selectedPeriod ? _ctx.dateValue : null,
    onSelect: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.setPiwikPeriodAndDate(_ctx.selectedPeriod, $event.date);
    })
  }, null, 8, ["period", "date"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Period')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periodsFiltered, function (period) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: period
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'selected-period-label': period === _ctx.selectedPeriod
      }),
      onDblclick: function onDblclick($event) {
        return _ctx.changeViewedPeriod(period);
      },
      title: period === _ctx.periodValue ? '' : _ctx.translate('General_DoubleClickToChangePeriod')
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      name: "period",
      id: "period_id_".concat(period),
      "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
        return _ctx.selectedPeriod = $event;
      }),
      checked: _ctx.selectedPeriod === period,
      onChange: function onChange($event) {
        return _ctx.selectedPeriod = period;
      },
      onDblclick: function onDblclick($event) {
        return _ctx.changeViewedPeriod(period);
      }
    }, null, 40, PeriodSelectorvue_type_template_id_155889f6_hoisted_10), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.selectedPeriod]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getPeriodDisplayText(period)), 1)], 42, PeriodSelectorvue_type_template_id_155889f6_hoisted_9)]);
  }), 128))])])]), _ctx.isComparisonEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "comparePeriodTo",
    type: "checkbox",
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return _ctx.isComparing = $event;
    })
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.isComparing]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_CompareTo')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.comparePeriodType,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.comparePeriodType = $event;
    }),
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
      'visibility': _ctx.isComparing ? 'visible' : 'hidden'
    }),
    name: 'comparePeriodToDropdown',
    uicontrol: 'select',
    options: _ctx.comparePeriodDropdownOptions,
    "full-width": true,
    disabled: !_ctx.isComparing
  }, null, 8, ["modelValue", "style", "options", "disabled"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isComparing && _ctx.comparePeriodType === 'custom' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_155889f6_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.compareStartDate,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.compareStartDate = $event;
    }),
    name: 'comparePeriodStartDate',
    uicontrol: 'text',
    "full-width": true,
    title: _ctx.translate('CoreHome_StartDate'),
    placeholder: 'YYYY-MM-DD'
  }, null, 8, ["modelValue", "title"])])]), PeriodSelectorvue_type_template_id_155889f6_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.compareEndDate,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.compareEndDate = $event;
    }),
    name: 'comparePeriodEndDate',
    uicontrol: 'text',
    "full-width": true,
    title: _ctx.translate('CoreHome_EndDate'),
    placeholder: 'YYYY-MM-DD'
  }, null, 8, ["modelValue", "title"])])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "submit",
    id: "calendarApply",
    class: "btn",
    onClick: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.onApplyClicked();
    }),
    disabled: !_ctx.isApplyEnabled(),
    value: _ctx.translate('General_Apply')
  }, null, 8, _hoisted_18)]), _ctx.isLoadingNewPage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 512)), [[_directive_expand_on_click, {
    expander: 'title'
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=template&id=155889f6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=7c5fe406

var ActivityIndicatorvue_type_template_id_7c5fe406_hoisted_1 = {
  class: "loadingPiwik"
};

var ActivityIndicatorvue_type_template_id_7c5fe406_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  alt: ""
}, null, -1);

function ActivityIndicatorvue_type_template_id_7c5fe406_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ActivityIndicatorvue_type_template_id_7c5fe406_hoisted_1, [ActivityIndicatorvue_type_template_id_7c5fe406_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loading]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=7c5fe406

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts


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



ActivityIndicatorvue_type_script_lang_ts.render = ActivityIndicatorvue_type_template_id_7c5fe406_render

/* harmony default export */ var ActivityIndicator = (ActivityIndicatorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=script&lang=ts
function PeriodSelectorvue_type_script_lang_ts_slicedToArray(arr, i) { return PeriodSelectorvue_type_script_lang_ts_arrayWithHoles(arr) || PeriodSelectorvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || PeriodSelectorvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || PeriodSelectorvue_type_script_lang_ts_nonIterableRest(); }

function PeriodSelectorvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function PeriodSelectorvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return PeriodSelectorvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return PeriodSelectorvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function PeriodSelectorvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function PeriodSelectorvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function PeriodSelectorvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }












var PeriodSelectorvue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
var NBSP = Matomo_Matomo.helper.htmlDecode('&nbsp;');
var COMPARE_PERIOD_OPTIONS = [{
  key: 'custom',
  value: translate('General_Custom')
}, {
  key: 'previousPeriod',
  value: translate('General_PreviousPeriod').replace(/\s+/, NBSP)
}, {
  key: 'previousYear',
  value: translate('General_PreviousYear').replace(/\s+/, NBSP)
}];
var PeriodSelectorvue_type_script_lang_ts_piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
var PeriodSelectorvue_type_script_lang_ts_piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);

function isValidDate(d) {
  if (Object.prototype.toString.call(d) !== '[object Date]') {
    return false;
  }

  return !Number.isNaN(d.getTime());
}

/* harmony default export */ var PeriodSelectorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    periods: Array
  },
  components: {
    DateRangePicker: DateRangePicker,
    PeriodDatePicker: PeriodDatePicker,
    Field: PeriodSelectorvue_type_script_lang_ts_Field,
    ActivityIndicator: ActivityIndicator
  },
  directives: {
    ExpandOnClick: ExpandOnClick
  },
  data: function data() {
    var selectedPeriod = src_MatomoUrl_MatomoUrl.parsed.value.period;
    return {
      comparePeriodDropdownOptions: COMPARE_PERIOD_OPTIONS,
      periodValue: selectedPeriod,
      dateValue: null,
      selectedPeriod: selectedPeriod,
      startRangeDate: null,
      endRangeDate: null,
      isRangeValid: null,
      isLoadingNewPage: false,
      isComparing: null,
      comparePeriodType: 'previousPeriod',
      compareStartDate: '',
      compareEndDate: ''
    };
  },
  mounted: function mounted() {
    var _this = this;

    Matomo_Matomo.on('hidePeriodSelector', function () {
      window.$(_this.$refs.root).parent('#periodString').hide();
    }); // some widgets might hide the period selector using the event above, so ensure it's
    // shown again when switching the page

    Matomo_Matomo.on('piwikPageChange', function () {
      window.$(_this.$refs.root).parent('#periodString').show();
    });
    this.updateSelectedValuesFromHash();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return src_MatomoUrl_MatomoUrl.parsed.value;
    }, this.updateSelectedValuesFromHash);
    this.isComparing = Comparisons_store_instance.isComparingPeriods();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return Comparisons_store_instance.isComparingPeriods();
    }, function (newVal) {
      _this.isComparing = newVal;
    });
    window.initTopControls(); // must be called when a top control changes width

    this.handleZIndexPositionRelativeCompareDropdownIssue();
  },
  computed: {
    currentlyViewingText: function currentlyViewingText() {
      var date;

      if (this.periodValue === 'range') {
        if (!this.startRangeDate || !this.endRangeDate) {
          return translate('General_Error');
        }

        date = "".concat(this.startRangeDate, ",").concat(this.endRangeDate);
      } else {
        if (!this.dateValue) {
          return translate('General_Error');
        }

        date = format(this.dateValue);
      }

      try {
        return Periods_Periods.parse(this.periodValue, date).getPrettyString();
      } catch (e) {
        return translate('General_Error');
      }
    },
    isComparisonEnabled: function isComparisonEnabled() {
      return Comparisons_store_instance.isComparisonEnabled();
    },
    periodsFiltered: function periodsFiltered() {
      return (this.periods || []).filter(function (periodLabel) {
        return Periods_Periods.isRecognizedPeriod(periodLabel);
      });
    },
    selectedComparisonParams: function selectedComparisonParams() {
      if (!this.isComparing) {
        return {};
      }

      if (this.comparePeriodType === 'custom') {
        return {
          comparePeriods: ['range'],
          compareDates: ["".concat(this.compareStartDate, ",").concat(this.compareEndDate)]
        };
      }

      if (this.comparePeriodType === 'previousPeriod') {
        return {
          comparePeriods: [this.selectedPeriod],
          compareDates: [this.previousPeriodDateToSelectedPeriod]
        };
      }

      if (this.comparePeriodType === 'previousYear') {
        var dateStr = this.selectedPeriod === 'range' ? "".concat(this.startRangeDate, ",").concat(this.endRangeDate) : format(this.dateValue);
        var currentDateRange = Periods_Periods.parse(this.selectedPeriod, dateStr).getDateRange();
        currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
        currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);

        if (this.selectedPeriod === 'range') {
          return {
            comparePeriods: ['range'],
            compareDates: ["".concat(format(currentDateRange[0]), ",").concat(format(currentDateRange[1]))]
          };
        }

        return {
          comparePeriods: [this.selectedPeriod],
          compareDates: [format(currentDateRange[0])]
        };
      }

      console.warn("Unknown compare period type: ".concat(this.comparePeriodType));
      return {};
    },
    previousPeriodDateToSelectedPeriod: function previousPeriodDateToSelectedPeriod() {
      if (this.selectedPeriod === 'range') {
        var currentStartRange = parseDate(this.startRangeDate);
        var currentEndRange = parseDate(this.endRangeDate);
        var newEndDate = Range_RangePeriod.getLastNRange('day', 2, currentStartRange).startDate;
        var rangeSize = Math.floor((currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000);
        var newRange = Range_RangePeriod.getLastNRange('day', 1 + rangeSize, newEndDate);
        return "".concat(format(newRange.startDate), ",").concat(format(newRange.endDate));
      }

      var newStartDate = Range_RangePeriod.getLastNRange(this.selectedPeriod, 2, this.dateValue).startDate;
      return format(newStartDate);
    },
    selectedDateString: function selectedDateString() {
      if (this.selectedPeriod === 'range') {
        var dateFrom = this.startRangeDate;
        var dateTo = this.endRangeDate;
        var oDateFrom = parseDate(dateFrom);
        var oDateTo = parseDate(dateTo);

        if (!isValidDate(oDateFrom) || !isValidDate(oDateTo) || oDateFrom > oDateTo) {
          // TODO: use a notification instead?
          window.$('#alert').find('h2').text(translate('General_InvalidDateRange'));
          Matomo_Matomo.helper.modalConfirm('#alert', {});
          return null;
        }

        return "".concat(dateFrom, ",").concat(dateTo);
      }

      return format(this.dateValue);
    }
  },
  methods: {
    handleZIndexPositionRelativeCompareDropdownIssue: function handleZIndexPositionRelativeCompareDropdownIssue() {
      var $element = window.$(this.$refs.root);
      $element.on('focus', '#comparePeriodToDropdown .select-dropdown', function () {
        $element.addClass('compare-dropdown-open');
      }).on('blur', '#comparePeriodToDropdown .select-dropdown', function () {
        $element.removeClass('compare-dropdown-open');
      });
    },
    changeViewedPeriod: function changeViewedPeriod(period) {
      // only change period if it's different from what's being shown currently
      if (period === this.periodValue) {
        return;
      } // can't just change to a range period, w/o setting two new dates


      if (period === 'range') {
        return;
      }

      this.setPiwikPeriodAndDate(period, this.dateValue);
    },
    setPiwikPeriodAndDate: function setPiwikPeriodAndDate(period, date) {
      this.periodValue = period;
      this.selectedPeriod = period;
      this.dateValue = date;
      var currentDateString = format(date);
      this.setRangeStartEndFromPeriod(period, currentDateString);
      this.propagateNewUrlParams(currentDateString, this.selectedPeriod);
      window.initTopControls();
    },
    propagateNewUrlParams: function propagateNewUrlParams(date, period) {
      var compareParams = this.selectedComparisonParams;
      var baseParams;

      if (Matomo_Matomo.helper.isAngularRenderingThePage()) {
        this.closePeriodSelector();
        baseParams = src_MatomoUrl_MatomoUrl.hashParsed.value;
      } else {
        this.isLoadingNewPage = true;
        baseParams = src_MatomoUrl_MatomoUrl.parsed.value;
      } // get params without comparePeriods/compareSegments/compareDates


      var paramsWithoutCompare = Object.assign({}, baseParams);
      delete paramsWithoutCompare.comparePeriods;
      delete paramsWithoutCompare.compareDates;
      src_MatomoUrl_MatomoUrl.updateLocation(Object.assign(Object.assign({}, paramsWithoutCompare), {}, {
        date: date,
        period: period
      }, compareParams));
    },
    onApplyClicked: function onApplyClicked() {
      if (this.selectedPeriod === 'range') {
        var dateString = this.selectedDateString;

        if (!dateString) {
          return;
        }

        this.periodValue = 'range';
        this.propagateNewUrlParams(dateString, 'range');
        return;
      }

      this.setPiwikPeriodAndDate(this.selectedPeriod, this.dateValue);
    },
    updateSelectedValuesFromHash: function updateSelectedValuesFromHash() {
      var date = src_MatomoUrl_MatomoUrl.parsed.value.date;
      var period = src_MatomoUrl_MatomoUrl.parsed.value.period;
      this.periodValue = period;
      this.selectedPeriod = period;
      this.dateValue = null;
      this.startRangeDate = null;
      this.endRangeDate = null;

      try {
        Periods_Periods.parse(period, date);
      } catch (e) {
        return;
      }

      if (period === 'range') {
        var periodObj = Periods_Periods.get(period).parse(date);

        var _periodObj$getDateRan = periodObj.getDateRange(),
            _periodObj$getDateRan2 = PeriodSelectorvue_type_script_lang_ts_slicedToArray(_periodObj$getDateRan, 2),
            startDate = _periodObj$getDateRan2[0],
            endDate = _periodObj$getDateRan2[1];

        this.dateValue = startDate;
        this.startRangeDate = format(startDate);
        this.endRangeDate = format(endDate);
      } else {
        this.dateValue = parseDate(date);
        this.setRangeStartEndFromPeriod(period, date);
      }
    },
    setRangeStartEndFromPeriod: function setRangeStartEndFromPeriod(period, dateStr) {
      var dateRange = Periods_Periods.parse(period, dateStr).getDateRange();
      this.startRangeDate = format(dateRange[0] < PeriodSelectorvue_type_script_lang_ts_piwikMinDate ? PeriodSelectorvue_type_script_lang_ts_piwikMinDate : dateRange[0]);
      this.endRangeDate = format(dateRange[1] > PeriodSelectorvue_type_script_lang_ts_piwikMaxDate ? PeriodSelectorvue_type_script_lang_ts_piwikMaxDate : dateRange[1]);
    },
    getPeriodDisplayText: function getPeriodDisplayText(periodLabel) {
      return Periods_Periods.get(periodLabel).getDisplayText();
    },
    onRangeChange: function onRangeChange(start, end) {
      if (!start || !end) {
        this.isRangeValid = false;
        return;
      }

      this.isRangeValid = true;
      this.startRangeDate = start;
      this.endRangeDate = end;
    },
    isApplyEnabled: function isApplyEnabled() {
      if (this.selectedPeriod === 'range' && !this.isRangeValid) {
        return false;
      }

      if (this.isComparing && this.comparePeriodType === 'custom' && !this.isCompareRangeValid()) {
        return false;
      }

      return true;
    },
    closePeriodSelector: function closePeriodSelector() {
      this.$refs.root.classList.remove('expanded');
    },
    isCompareRangeValid: function isCompareRangeValid() {
      try {
        parseDate(this.compareStartDate);
      } catch (e) {
        return false;
      }

      try {
        parseDate(this.compareEndDate);
      } catch (e) {
        return false;
      }

      return true;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue



PeriodSelectorvue_type_script_lang_ts.render = PeriodSelectorvue_type_template_id_155889f6_render

/* harmony default export */ var PeriodSelector = (PeriodSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var PeriodSelector_adapter = (createAngularJsAdapter({
  component: PeriodSelector,
  scope: {
    periods: {
      angularJsBind: '<'
    }
  },
  directiveName: 'piwikPeriodSelector'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=5cfc36bc

var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_1 = {
  class: "reportingMenu"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_2 = ["aria-label"];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_3 = ["onClick"];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_4 = {
  class: "hidden"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_5 = {
  role: "menu"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_6 = ["href", "onClick", "title"];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_7 = ["href", "onClick"];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_8 = ["onClick"];

var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_10 = [ReportingMenuvue_type_template_id_5cfc36bc_hoisted_9];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_11 = {
  id: "mobile-left-menu",
  class: "sidenav hide-on-large-only"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_12 = {
  class: "collapsible collapsible-accordion"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_13 = {
  class: "collapsible-header"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_14 = {
  class: "collapsible-body"
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_15 = {
  key: 0
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_16 = ["onClick", "href"];
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_17 = {
  key: 1
};
var ReportingMenuvue_type_template_id_5cfc36bc_hoisted_18 = ["onClick", "href"];
function ReportingMenuvue_type_template_id_5cfc36bc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MenuItemsDropdown = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MenuItemsDropdown");

  var _directive_side_nav = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("side-nav");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
    class: "navbar hide-on-med-and-down",
    role: "menu",
    "aria-label": _ctx.translate('CoreHome_MainNavigation')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menu, function (category) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["menuTab", {
        'active': category.id === _ctx.activeCategory
      }]),
      role: "menuitem",
      key: category.id
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "item",
      tabindex: "5",
      href: "",
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
        return _ctx.loadCategory(category);
      }, ["prevent"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("menu-icon ".concat(category.icon ? category.icon : 'icon-arrow-right'))
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_Menu')), 1)], 8, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_5, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, function (subcategory) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        role: "menuitem",
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          'active': (subcategory.id === _ctx.displayedSubcategory || subcategory.isGroup && _ctx.activeSubsubcategory === _ctx.displayedSubcategory) && category.id === _ctx.displayedCategory
        }),
        key: subcategory.id
      }, [subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MenuItemsDropdown, {
        key: 0,
        "show-search": true,
        "menu-title": _ctx.htmlEntities(subcategory.name)
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.subcategories, function (subcat) {
            return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
              class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["item", {
                active: subcat.id === _ctx.activeSubsubcategory && subcategory.id === _ctx.displayedSubcategory && category.id === _ctx.displayedCategory
              }]),
              tabindex: "5",
              href: "#?".concat(_ctx.makeUrl(category, subcat)),
              onClick: function onClick($event) {
                return _ctx.loadSubcategory(category, subcat, $event);
              },
              title: subcat.tooltip,
              key: subcat.id
            }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 11, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_6);
          }), 128))];
        }),
        _: 2
      }, 1032, ["menu-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        href: "#?".concat(_ctx.makeUrl(category, subcategory)),
        class: "item",
        onClick: function onClick($event) {
          return _ctx.loadSubcategory(category, subcategory, $event);
        }
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), subcategory.help ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 2,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["item-help-icon", {
          active: _ctx.helpShownCategory && _ctx.helpShownCategory.subcategory === subcategory.id && _ctx.helpShownCategory.category === category.id && subcategory.help
        }]),
        tabindex: "5",
        href: "javascript:",
        onClick: function onClick($event) {
          return _ctx.showHelp(category, subcategory, $event);
        }
      }, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_10, 10, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
    }), 128))])], 2);
  }), 128))], 8, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_11, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menu, function (category) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "no-padding",
      key: category.id
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(category.icon ? category.icon : 'icon-arrow-bottom')
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, function (subcategory) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: subcategory.id
      }, [subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_15, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.subcategories, function (subcat) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          onClick: function onClick($event) {
            return _ctx.loadSubcategory(category, subcat);
          },
          href: "#?".concat(_ctx.makeUrl(category, subcat)),
          key: subcat.id
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 9, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_16);
      }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ReportingMenuvue_type_template_id_5cfc36bc_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        onClick: function onClick($event) {
          return _ctx.loadSubcategory(category, subcategory);
        },
        href: "#?".concat(_ctx.makeUrl(category, subcategory))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, ReportingMenuvue_type_template_id_5cfc36bc_hoisted_18)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))])])])], 512), [[_directive_side_nav, {
      activator: _ctx.sideNavActivator
    }]])]);
  }), 128))])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=5cfc36bc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=52166f8a

var Notificationvue_type_template_id_52166f8a_hoisted_1 = {
  key: 0
};
var Notificationvue_type_template_id_52166f8a_hoisted_2 = ["data-notification-instance-id"];
var Notificationvue_type_template_id_52166f8a_hoisted_3 = {
  key: 1
};
var Notificationvue_type_template_id_52166f8a_hoisted_4 = {
  class: "notification-body"
};
var Notificationvue_type_template_id_52166f8a_hoisted_5 = ["innerHTML"];
var Notificationvue_type_template_id_52166f8a_hoisted_6 = {
  key: 1
};
function Notificationvue_type_template_id_52166f8a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
    name: _ctx.type === 'toast' ? 'slow-fade-out' : undefined,
    onAfterLeave: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.toastClosed();
    })
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [!_ctx.deleted ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_52166f8a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
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
              }, " × ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", Notificationvue_type_template_id_52166f8a_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Notificationvue_type_template_id_52166f8a_hoisted_4, [_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
                key: 0,
                innerHTML: _ctx.$sanitize(_ctx.message)
              }, null, 8, Notificationvue_type_template_id_52166f8a_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_52166f8a_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 14, Notificationvue_type_template_id_52166f8a_hoisted_2)];
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=52166f8a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=script&lang=ts


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

      AjaxHelper_AjaxHelper.post({
        module: 'CoreHome',
        action: 'markNotificationAsRead'
      }, {
        notificationId: this.notificationId
      }, {
        withTokenInUrl: true
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue



Notificationvue_type_script_lang_ts.render = Notificationvue_type_template_id_52166f8a_render

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
      transform: transformAngularJsBoolAttr
    },
    toastLength: {
      angularJsBind: '@?'
    }
  },
  directiveName: 'piwikNotification',
  transclude: true
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notifications.store.ts
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




var Notifications_store_window = window,
    Notifications_store_$ = Notifications_store_window.$;

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
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState);
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

      var $notificationNodes = Notifications_store_$('[data-role="notification"]');
      var notificationsToShow = [];
      $notificationNodes.each(function (index, notificationNode) {
        var $notificationNode = Notifications_store_$(notificationNode);
        var attributes = $notificationNode.data();
        var message = $notificationNode.html();

        if (message) {
          notificationsToShow.push(Object.assign(Object.assign({}, attributes), {}, {
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
      var addMethod = notification.prepend ? this.prependNotification : this.appendNotification;
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
            Notifications_store_$(modal).prepend('<div id="modalNotificationContainer"/>');
          }

          notificationPosition = "".concat(modalSelector, " #modalNotificationContainer");
          addMethod = this.prependNotification;
        }
      }

      var group = notification.group || (notificationPosition ? notificationPosition.toString() : '');
      this.initializeNotificationContainer(notificationPosition, group);
      var notificationInstanceId = (this.nextNotificationId += 1).toString();
      addMethod.call(this, Object.assign(Object.assign({}, notification), {}, {
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
      var $placeat = notification.placeat ? Notifications_store_$(notification.placeat) : undefined;

      if (!$placeat || !$placeat.length) {
        throw new Error('A valid selector is required for the placeat option when using Notification.toast().');
      }

      var toastElement = document.createElement('div');
      toastElement.style.position = 'absolute';
      toastElement.style.top = "".concat($placeat.offset().top, "px");
      toastElement.style.left = "".concat($placeat.offset().left, "px");
      toastElement.style.zIndex = '1000';
      document.body.appendChild(toastElement);
      var app = createVueApp({
        render: function render() {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Notification, Object.assign(Object.assign({}, notification), {}, {
            notificationId: notification.id,
            type: 'toast',
            onClosed: function onClosed() {
              app.unmount();
            }
          }));
        }
      });
      app.mount(toastElement);
    }
  }, {
    key: "initializeNotificationContainer",
    value: function initializeNotificationContainer(notificationPosition, group) {
      if (!notificationPosition) {
        return;
      }

      var $container = Notifications_store_$(notificationPosition);

      if ($container.children('.notification-group').length) {
        return;
      } // avoiding a dependency cycle. won't need to do this when NotificationGroup's do not need
      // to be dynamically initialized.


      var NotificationGroup = window.CoreHome.NotificationGroup; // eslint-disable-line

      var app = createVueApp({
        template: '<NotificationGroup :group="group"></NotificationGroup>',
        data: function data() {
          return {
            group: group
          };
        }
      });
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

Notifications_store_$(function () {
  return Notifications_store_instance.parseNotificationDivs();
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notifications.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

window.angular.module('piwikApp').factory('notifications', function () {
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=script&lang=ts



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





// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPages/ReportingPages.store.ts
function ReportingPages_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function ReportingPages_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function ReportingPages_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) ReportingPages_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) ReportingPages_store_defineProperties(Constructor, staticProps); return Constructor; }

function ReportingPages_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


var ReportingPages_store_ReportingPagesStore = /*#__PURE__*/function () {
  function ReportingPagesStore() {
    var _this = this;

    ReportingPages_store_classCallCheck(this, ReportingPagesStore);

    ReportingPages_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      pages: []
    }));

    ReportingPages_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    ReportingPages_store_defineProperty(this, "fetchAllPagesPromise", void 0);

    ReportingPages_store_defineProperty(this, "pages", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.pages;
    }));
  }

  ReportingPages_store_createClass(ReportingPagesStore, [{
    key: "findPageInCategory",
    value: function findPageInCategory(categoryId) {
      // happens when user switches between sites, in this case check if the same category exists and
      // if so, select first entry from that category
      return this.pages.value.find(function (p) {
        return p && p.category && p.category.id === categoryId && p.subcategory && p.subcategory.id;
      });
    }
  }, {
    key: "findPage",
    value: function findPage(categoryId, subcategoryId) {
      return this.pages.value.find(function (p) {
        return p && p.category && p.subcategory && p.category.id === categoryId && "".concat(p.subcategory.id) === subcategoryId;
      });
    }
  }, {
    key: "reloadAllPages",
    value: function reloadAllPages() {
      delete this.fetchAllPagesPromise;
      return this.getAllPages();
    }
  }, {
    key: "getAllPages",
    value: function getAllPages() {
      var _this2 = this;

      if (!this.fetchAllPagesPromise) {
        this.fetchAllPagesPromise = AjaxHelper_AjaxHelper.fetch({
          method: 'API.getReportPagesMetadata',
          filter_limit: '-1'
        }).then(function (response) {
          _this2.privateState.pages = response;
          return _this2.pages.value;
        });
      }

      return this.fetchAllPagesPromise.then(function () {
        return _this2.pages.value;
      });
    }
  }]);

  return ReportingPagesStore;
}();
/* harmony default export */ var ReportingPages_store = (new ReportingPages_store_ReportingPagesStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Orderable.ts
function Orderable_toConsumableArray(arr) { return Orderable_arrayWithoutHoles(arr) || Orderable_iterableToArray(arr) || Orderable_unsupportedIterableToArray(arr) || Orderable_nonIterableSpread(); }

function Orderable_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function Orderable_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return Orderable_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return Orderable_arrayLikeToArray(o, minLen); }

function Orderable_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function Orderable_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return Orderable_arrayLikeToArray(arr); }

function Orderable_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function sortOrderables(menu) {
  var result = Orderable_toConsumableArray(menu || []);

  result.sort(function (lhs, rhs) {
    if (lhs.order < rhs.order) {
      return -1;
    }

    if (lhs.order > rhs.order) {
      return 1;
    }

    return 0;
  });
  return result;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/Category.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function getCategoryChildren(category) {
  var container = category;

  if (container.subcategories) {
    return container.subcategories;
  }

  return [];
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/Subcategory.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function getSubcategoryChildren(subcategory) {
  var container = subcategory;

  if (container.subcategories) {
    return container.subcategories;
  }

  return [];
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.store.ts
function ReportingMenu_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function ReportingMenu_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function ReportingMenu_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) ReportingMenu_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) ReportingMenu_store_defineProperties(Constructor, staticProps); return Constructor; }

function ReportingMenu_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */








function isNumeric(text) {
  var n = parseFloat(text);
  return !Number.isNaN(n) && Number.isFinite(n);
}

var ReportingMenu_store_ReportingMenuStore = /*#__PURE__*/function () {
  function ReportingMenuStore() {
    var _this = this;

    ReportingMenu_store_classCallCheck(this, ReportingMenuStore);

    ReportingMenu_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      activeSubcategoryId: null,
      activeSubsubcategoryId: null
    }));

    ReportingMenu_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    ReportingMenu_store_defineProperty(this, "activeCategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return typeof _this.state.value.activeCategoryId !== 'undefined' ? _this.state.value.activeCategoryId : src_MatomoUrl_MatomoUrl.parsed.value.category;
    }));

    ReportingMenu_store_defineProperty(this, "activeSubcategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.activeSubcategoryId || src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
    }));

    ReportingMenu_store_defineProperty(this, "activeSubsubcategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var manuallySetId = _this.state.value.activeSubsubcategoryId;

      if (manuallySetId) {
        return manuallySetId;
      } // default to activeSubcategory if the activeSubcategory is part of a group


      var foundCategory = _this.findSubcategory(_this.activeCategory.value, _this.activeSubcategory.value);

      if (foundCategory.subsubcategory && foundCategory.subsubcategory.id === _this.activeSubcategory.value) {
        return foundCategory.subsubcategory.id;
      }

      return null;
    }));

    ReportingMenu_store_defineProperty(this, "menu", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.buildMenuFromPages();
    }));
  }

  ReportingMenu_store_createClass(ReportingMenuStore, [{
    key: "fetchMenuItems",
    value: function fetchMenuItems() {
      var _this2 = this;

      return ReportingPages_store.getAllPages().then(function () {
        return _this2.menu.value;
      });
    }
  }, {
    key: "reloadMenuItems",
    value: function reloadMenuItems() {
      var _this3 = this;

      return ReportingPages_store.reloadAllPages().then(function () {
        return _this3.menu.value;
      });
    }
  }, {
    key: "findSubcategory",
    value: function findSubcategory(categoryId, subcategoryId) {
      var foundCategory = undefined;
      var foundSubcategory = undefined;
      var foundSubSubcategory = undefined;
      this.menu.value.forEach(function (category) {
        if (category.id !== categoryId) {
          return;
        }

        (getCategoryChildren(category) || []).forEach(function (subcategory) {
          if (subcategory.id === subcategoryId) {
            foundCategory = category;
            foundSubcategory = subcategory;
          }

          if (subcategory.isGroup) {
            (getSubcategoryChildren(subcategory) || []).forEach(function (subcat) {
              if (subcat.id === subcategoryId) {
                foundCategory = category;
                foundSubcategory = subcategory;
                foundSubSubcategory = subcat;
              }
            });
          }
        });
      });
      return {
        category: foundCategory,
        subcategory: foundSubcategory,
        subsubcategory: foundSubSubcategory
      };
    }
  }, {
    key: "buildMenuFromPages",
    value: function buildMenuFromPages() {
      var menu = [];
      var displayedCategory = src_MatomoUrl_MatomoUrl.parsed.value.category;
      var displayedSubcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
      var pages = ReportingPages_store.pages.value;
      var categoriesHandled = {};
      pages.forEach(function (page) {
        var category = Object.assign({}, page.category);
        var categoryId = category.id;
        var isCategoryDisplayed = categoryId === displayedCategory;

        if (categoriesHandled[categoryId]) {
          return;
        }

        categoriesHandled[categoryId] = true;
        category.subcategories = [];
        var categoryGroups = null;
        var pagesWithCategory = pages.filter(function (p) {
          return p.category.id === categoryId;
        });
        pagesWithCategory.forEach(function (p) {
          var subcategory = Object.assign({}, p.subcategory);
          var isSubcategoryDisplayed = subcategory.id === displayedSubcategory && isCategoryDisplayed;

          if (p.widgets && p.widgets[0] && isNumeric(p.subcategory.id)) {
            // we handle a goal or something like it
            if (!categoryGroups) {
              categoryGroups = Object.assign({}, subcategory);
              categoryGroups.name = translate('CoreHome_ChooseX', [category.name]);
              categoryGroups.isGroup = true;
              categoryGroups.subcategories = [];
              categoryGroups.order = 10;
            }

            if (isSubcategoryDisplayed) {
              categoryGroups.name = subcategory.name;
            }

            var entityId = page.subcategory.id;
            subcategory.tooltip = "".concat(subcategory.name, " (id = ").concat(entityId, ")");
            categoryGroups.subcategories.push(subcategory);
            return;
          }

          category.subcategories.push(subcategory);
        });

        if (categoryGroups && categoryGroups.subcategories && categoryGroups.subcategories.length <= 5) {
          categoryGroups.subcategories.forEach(function (sub) {
            return category.subcategories.push(sub);
          });
        } else if (categoryGroups) {
          category.subcategories.push(categoryGroups);
        }

        category.subcategories = sortOrderables(getCategoryChildren(category));
        menu.push(category);
      });
      return sortOrderables(menu);
    }
  }, {
    key: "toggleCategory",
    value: function toggleCategory(category) {
      this.privateState.activeSubcategoryId = null;
      this.privateState.activeSubsubcategoryId = null;

      if (this.activeCategory.value === category.id) {
        this.privateState.activeCategoryId = null;
        return false;
      }

      this.privateState.activeCategoryId = category.id;
      return true;
    }
  }, {
    key: "enterSubcategory",
    value: function enterSubcategory(category, subcategory, subsubcategory) {
      if (!category || !subcategory) {
        return;
      }

      this.privateState.activeCategoryId = category.id;
      this.privateState.activeSubcategoryId = subcategory.id;

      if (subsubcategory) {
        this.privateState.activeSubsubcategoryId = subsubcategory.id;
      }
    }
  }]);

  return ReportingMenuStore;
}();
/* harmony default export */ var ReportingMenu_store = (new ReportingMenu_store_ReportingMenuStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widgets.store.ts
function Widgets_store_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { Widgets_store_typeof = function _typeof(obj) { return typeof obj; }; } else { Widgets_store_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return Widgets_store_typeof(obj); }

function Widgets_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function Widgets_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function Widgets_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) Widgets_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) Widgets_store_defineProperties(Constructor, staticProps); return Constructor; }

function Widgets_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function getWidgetChildren(widget) {
  var container = widget;

  if (container.widgets) {
    return container.widgets;
  }

  return [];
}

var Widgets_store_WidgetsStore = /*#__PURE__*/function () {
  function WidgetsStore() {
    var _this = this;

    Widgets_store_classCallCheck(this, WidgetsStore);

    Widgets_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isFetchedFirstTime: false,
      categorizedWidgets: {}
    }));

    Widgets_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      if (!_this.privateState.isFetchedFirstTime) {
        // initiating a side effect in a computed property seems wrong, but it needs to be
        // executed after knowing a user's logged in and it will succeed.
        _this.fetchAvailableWidgets();
      }

      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    Widgets_store_defineProperty(this, "widgets", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.categorizedWidgets;
    }));
  }

  Widgets_store_createClass(WidgetsStore, [{
    key: "fetchAvailableWidgets",
    value: function fetchAvailableWidgets() {
      var _this2 = this;

      // if there's no idSite, don't make the request since it will just fail
      if (!src_MatomoUrl_MatomoUrl.parsed.value.idSite) {
        return Promise.resolve(this.widgets.value);
      }

      this.privateState.isFetchedFirstTime = true;
      return new Promise(function (resolve, reject) {
        try {
          window.widgetsHelper.getAvailableWidgets(function (widgets) {
            var casted = widgets;
            _this2.privateState.categorizedWidgets = casted;
            resolve(_this2.widgets.value);
          });
        } catch (e) {
          reject(e);
        }
      });
    }
  }, {
    key: "reloadAvailableWidgets",
    value: function reloadAvailableWidgets() {
      if (Widgets_store_typeof(window.widgetsHelper) === 'object' && window.widgetsHelper.availableWidgets) {
        // lets also update widgetslist so will be easier to update list of available widgets in
        // dashboard selector immediately
        delete window.widgetsHelper.availableWidgets;
      }

      return this.fetchAvailableWidgets();
    }
  }]);

  return WidgetsStore;
}();

/* harmony default export */ var Widgets_store = (new Widgets_store_WidgetsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=script&lang=ts









var REPORTING_HELP_NOTIFICATION_ID = 'reportingmenu-help';
/* harmony default export */ var ReportingMenuvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MenuItemsDropdown: MenuItemsDropdown
  },
  directives: {
    SideNav: SideNav
  },
  props: {},
  data: function data() {
    return {
      showSubcategoryHelpOnLoad: null,
      initialLoad: true,
      helpShownCategory: null
    };
  },
  computed: {
    sideNavActivator: function sideNavActivator() {
      return document.querySelector('nav .activateLeftMenu');
    },
    menu: function menu() {
      return ReportingMenu_store.menu.value;
    },
    activeCategory: function activeCategory() {
      return ReportingMenu_store.activeCategory.value;
    },
    activeSubcategory: function activeSubcategory() {
      return ReportingMenu_store.activeSubcategory.value;
    },
    activeSubsubcategory: function activeSubsubcategory() {
      return ReportingMenu_store.activeSubsubcategory.value;
    },
    displayedCategory: function displayedCategory() {
      return src_MatomoUrl_MatomoUrl.parsed.value.category;
    },
    displayedSubcategory: function displayedSubcategory() {
      return src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
    }
  },
  created: function created() {
    var _this = this;

    ReportingMenu_store.fetchMenuItems().then(function (menu) {
      if (!src_MatomoUrl_MatomoUrl.parsed.value.subcategory) {
        var categoryToLoad = menu[0];
        var subcategoryToLoad = categoryToLoad.subcategories[0]; // load first, initial page if no subcategory is present

        ReportingMenu_store.enterSubcategory(categoryToLoad, subcategoryToLoad);

        _this.propagateUrlChange(categoryToLoad, subcategoryToLoad);
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return src_MatomoUrl_MatomoUrl.parsed.value;
    }, function (query) {
      var found = ReportingMenu_store.findSubcategory(query.category, query.subcategory);
      ReportingMenu_store.enterSubcategory(found.category, found.subcategory, found.subsubcategory);
    });
    Matomo_Matomo.on('piwikPageChange', function () {
      if (!_this.initialLoad) {
        window.globalAjaxQueue.abort();
      }

      _this.helpShownCategory = null;

      if (_this.showSubcategoryHelpOnLoad) {
        _this.showHelp(_this.showSubcategoryHelpOnLoad.category, _this.showSubcategoryHelpOnLoad.subcategory);

        _this.showSubcategoryHelpOnLoad = null;
      }

      window.$('#loadingError,#loadingRateLimitError').hide();
      _this.initialLoad = false;
    });
    Matomo_Matomo.on('updateReportingMenu', function () {
      ReportingMenu_store.reloadMenuItems().then(function () {
        var category = src_MatomoUrl_MatomoUrl.parsed.value.category;
        var subcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory; // we need to make sure to select same categories again

        if (category && subcategory) {
          var found = ReportingMenu_store.findSubcategory(category, subcategory);

          if (found.category) {
            ReportingMenu_store.enterSubcategory(found.category, found.subcategory, found.subsubcategory);
          }
        }
      });
      Widgets_store.reloadAvailableWidgets();
    });
  },
  methods: {
    propagateUrlChange: function propagateUrlChange(category, subcategory) {
      var queryParams = src_MatomoUrl_MatomoUrl.parsed.value;

      if (queryParams.category === category.id && queryParams.subcategory === subcategory.id) {
        // we need to manually trigger change as URL would not change and therefore page would not
        // be reloaded
        this.loadSubcategory(category, subcategory);
      } else {
        src_MatomoUrl_MatomoUrl.updateHash(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
          category: category.id,
          subcategory: subcategory.id
        }));
      }
    },
    loadCategory: function loadCategory(category) {
      Notifications_store.remove(REPORTING_HELP_NOTIFICATION_ID);
      var isActive = ReportingMenu_store.toggleCategory(category);

      if (isActive && category.subcategories && category.subcategories.length === 1) {
        this.helpShownCategory = null;
        var subcategory = category.subcategories[0];
        this.propagateUrlChange(category, subcategory);
      }
    },
    loadSubcategory: function loadSubcategory(category, subcategory, event) {
      if (event && (event.shiftKey || event.ctrlKey || event.metaKey)) {
        return;
      }

      Notifications_store.remove(REPORTING_HELP_NOTIFICATION_ID);

      if (subcategory && subcategory.id === this.activeSubcategory) {
        this.helpShownCategory = null; // this menu item is already active, a location change success would not be triggered,
        // instead trigger an event (after the URL changes)

        setTimeout(function () {
          Matomo_Matomo.postEvent('loadPage', category.id, subcategory.id);
        });
      }
    },
    makeUrl: function makeUrl(category, subcategory) {
      var _MatomoUrl$parsed$val = src_MatomoUrl_MatomoUrl.parsed.value,
          idSite = _MatomoUrl$parsed$val.idSite,
          period = _MatomoUrl$parsed$val.period,
          date = _MatomoUrl$parsed$val.date,
          segment = _MatomoUrl$parsed$val.segment,
          comparePeriods = _MatomoUrl$parsed$val.comparePeriods,
          compareDates = _MatomoUrl$parsed$val.compareDates,
          compareSegments = _MatomoUrl$parsed$val.compareSegments;
      return src_MatomoUrl_MatomoUrl.stringify({
        idSite: idSite,
        period: period,
        date: date,
        segment: segment,
        comparePeriods: comparePeriods,
        compareDates: compareDates,
        compareSegments: compareSegments,
        category: category.id,
        subcategory: subcategory.id
      });
    },
    htmlEntities: function htmlEntities(v) {
      return Matomo_Matomo.helper.htmlEntities(v);
    },
    showHelp: function showHelp(category, subcategory, event) {
      var parsedUrl = src_MatomoUrl_MatomoUrl.parsed.value;
      var currentCategory = parsedUrl.category;
      var currentSubcategory = parsedUrl.subcategory;

      if ((currentCategory !== category.id || currentSubcategory !== subcategory.id) && event) {
        this.showSubcategoryHelpOnLoad = {
          category: category,
          subcategory: subcategory
        };
        src_MatomoUrl_MatomoUrl.updateHash(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
          category: category.id,
          subcategory: subcategory.id
        }));
        return;
      }

      if (this.helpShownCategory && category.id === this.helpShownCategory.category && subcategory.id === this.helpShownCategory.subcategory) {
        Notifications_store.remove(REPORTING_HELP_NOTIFICATION_ID);
        this.helpShownCategory = null;
        return;
      }

      var prefixText = translate('CoreHome_ReportingCategoryHelpPrefix', category.name, subcategory.name);
      var prefix = "<strong>".concat(prefixText, "</strong><br/>");
      Notifications_store.show({
        context: 'info',
        id: REPORTING_HELP_NOTIFICATION_ID,
        type: 'help',
        noclear: true,
        class: 'help-notification',
        message: prefix + subcategory.help,
        placeat: '#notificationContainer',
        prepend: true
      });
      this.helpShownCategory = {
        category: category.id,
        subcategory: subcategory.id
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue



ReportingMenuvue_type_script_lang_ts.render = ReportingMenuvue_type_template_id_5cfc36bc_render

/* harmony default export */ var ReportingMenu = (ReportingMenuvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ReportingMenu_adapter = (createAngularJsAdapter({
  component: ReportingMenu,
  directiveName: 'piwikReportingMenu'
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

 // removed boolean active property from objects in vue so we can keep the store immutable, but,
// angularjs version should still have them

function addActiveMenuItems(menu) {
  menu.forEach(function (category) {
    if (category.id === ReportingMenu_store.activeCategory.value) {
      category.active = true;
      (category.subcategories || []).forEach(function (subcat) {
        if (subcat.id === ReportingMenu_store.activeSubcategory.value) {
          subcat.active = true;
          (subcat.subcategories || []).forEach(function (subsubcat) {
            if (subsubcat.id === ReportingMenu_store.activeSubsubcategory.value) {
              subsubcat.active = true;
            }
          });
        }
      });
    }
  });
  return menu;
}

function reportingMenuModelAdapter() {
  return {
    get menu() {
      return ReportingMenu_store.menu.value;
    },

    findSubcategory: ReportingMenu_store.findSubcategory.bind(ReportingMenu_store),
    reloadMenuItems: function reloadMenuItems() {
      return ReportingMenu_store.reloadMenuItems().then(function (p) {
        return addActiveMenuItems(cloneThenApply(p));
      });
    },
    fetchMenuItems: function fetchMenuItems() {
      return ReportingMenu_store.fetchMenuItems().then(function (p) {
        return addActiveMenuItems(cloneThenApply(p));
      });
    }
  };
}

window.angular.module('piwikApp.service').factory('reportingMenuModel', reportingMenuModelAdapter);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPages/ReportingPages.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



function reportingPagesModelAdapter() {
  return {
    get pages() {
      return ReportingPages_store.pages.value;
    },

    findPageInCategory: function findPageInCategory() {
      return clone(ReportingPages_store.findPageInCategory.apply(ReportingPages_store, arguments));
    },
    findPage: function findPage() {
      return clone(ReportingPages_store.findPage.apply(ReportingPages_store, arguments));
    },
    reloadAllPages: function reloadAllPages() {
      return ReportingPages_store.reloadAllPages().then(function (p) {
        return cloneThenApply(p);
      });
    },
    getAllPages: function getAllPages() {
      return ReportingPages_store.getAllPages().then(function (p) {
        return cloneThenApply(p);
      });
    }
  };
}

window.angular.module('piwikApp.service').factory('reportingPagesModel', reportingPagesModelAdapter);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportMetadata/ReportMetadata.store.ts
function ReportMetadata_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function ReportMetadata_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function ReportMetadata_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) ReportMetadata_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) ReportMetadata_store_defineProperties(Constructor, staticProps); return Constructor; }

function ReportMetadata_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




var ReportMetadata_store_ReportMetadataStore = /*#__PURE__*/function () {
  function ReportMetadataStore() {
    var _this = this;

    ReportMetadata_store_classCallCheck(this, ReportMetadataStore);

    ReportMetadata_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      reports: []
    }));

    ReportMetadata_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState));

    ReportMetadata_store_defineProperty(this, "reports", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.reports;
    }));

    ReportMetadata_store_defineProperty(this, "reportsPromise", void 0);
  }

  ReportMetadata_store_createClass(ReportMetadataStore, [{
    key: "findReport",
    value: // TODO: it used to return an empty array when nothing was found, will that be an issue?
    function findReport(reportModule, reportAction) {
      return this.reports.value.find(function (r) {
        return r.module === reportModule && r.action === reportAction;
      });
    }
  }, {
    key: "fetchReportMetadata",
    value: function fetchReportMetadata() {
      var _this2 = this;

      if (!this.reportsPromise) {
        this.reportsPromise = AjaxHelper_AjaxHelper.fetch({
          method: 'API.getReportMetadata',
          filter_limit: '-1',
          idSite: Matomo_Matomo.idSite || src_MatomoUrl_MatomoUrl.parsed.value.idSite
        }).then(function (response) {
          _this2.privateState.reports = response;
          return response;
        });
      }

      return this.reportsPromise.then(function () {
        return _this2.reports.value;
      });
    }
  }]);

  return ReportMetadataStore;
}();
/* harmony default export */ var ReportMetadata_store = (new ReportMetadata_store_ReportMetadataStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportMetadata/ReportMetadata.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


window.angular.module('piwikApp.service').factory('reportMetadataModel', function () {
  return {
    get reports() {
      return ReportMetadata_store.reports.value;
    },

    findReport: ReportMetadata_store.findReport.bind(ReportMetadata_store),
    fetchReportMetadata: function fetchReportMetadata() {
      return ReportMetadata_store.fetchReportMetadata().then(function (m) {
        return cloneThenApply(m);
      });
    }
  };
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=43c0d326

var WidgetLoadervue_type_template_id_43c0d326_hoisted_1 = {
  key: 0
};
var WidgetLoadervue_type_template_id_43c0d326_hoisted_2 = {
  key: 1,
  class: "notification system notification-error"
};
var WidgetLoadervue_type_template_id_43c0d326_hoisted_3 = {
  key: 0,
  rel: "noreferrer noopener",
  target: "_blank",
  href: "https://matomo.org/faq/troubleshooting/faq_19489/"
};
var WidgetLoadervue_type_template_id_43c0d326_hoisted_4 = {
  key: 2,
  class: "notification system notification-error"
};
var WidgetLoadervue_type_template_id_43c0d326_hoisted_5 = {
  class: "theWidgetContent",
  ref: "widgetContent"
};
function WidgetLoadervue_type_template_id_43c0d326_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": _ctx.loadingMessage,
    loading: _ctx.loading
  }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.widgetName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", WidgetLoadervue_type_template_id_43c0d326_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.widgetName), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loadingFailedRateLimit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_43c0d326_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), _ctx.hasErrorFaqLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", WidgetLoadervue_type_template_id_43c0d326_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequestFaqLink')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_43c0d326_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRateLimit')), 1))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loadingFailed]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetLoadervue_type_template_id_43c0d326_hoisted_5, null, 512)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=43c0d326

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=script&lang=ts








/**
 * Loads any custom widget or URL based on the given parameters.
 *
 * The currently active idSite, period, date and segment (if needed) is automatically
 * appended to the parameters. If this widget is removed from the DOM and requests are in
 * progress, these requests will be aborted. A loading message or an error message on failure
 * is shown as well. It's kinda similar to ng-include but there it is not possible to
 * listen to HTTP errors etc.
 *
 * Example:
 * <WidgetLoader :widget-params="{module: '', action: '', ...}"/>
 */

/* harmony default export */ var WidgetLoadervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    widgetParams: Object,
    widgetName: String
  },
  components: {
    ActivityIndicator: ActivityIndicator
  },
  data: function data() {
    return {
      loading: false,
      loadingFailed: false,
      loadingFailedRateLimit: false,
      changeCounter: 0,
      currentScope: null,
      lastWidgetAbortController: null
    };
  },
  watch: {
    widgetParams: function widgetParams(parameters) {
      if (parameters) {
        this.loadWidgetUrl(parameters, this.changeCounter += 1);
      }
    }
  },
  computed: {
    loadingMessage: function loadingMessage() {
      if (!this.widgetName) {
        return translate('General_LoadingData');
      }

      return translate('General_LoadingPopover', this.widgetName);
    },
    hasErrorFaqLink: function hasErrorFaqLink() {
      var isGeneralSettingsAdminEnabled = Matomo_Matomo.config.enable_general_settings_admin;
      var isPluginsAdminEnabled = Matomo_Matomo.config.enable_plugins_admin;
      return Matomo_Matomo.hasSuperUserAccess && (isGeneralSettingsAdminEnabled || isPluginsAdminEnabled);
    }
  },
  mounted: function mounted() {
    if (this.widgetParams) {
      this.loadWidgetUrl(this.widgetParams, this.changeCounter += 1);
    }
  },
  beforeUnmount: function beforeUnmount() {
    this.cleanupLastWidgetContent();
  },
  methods: {
    abortHttpRequestIfNeeded: function abortHttpRequestIfNeeded() {
      if (this.lastWidgetAbortController) {
        this.lastWidgetAbortController.abort();
        this.lastWidgetAbortController = null;
      }
    },
    cleanupLastWidgetContent: function cleanupLastWidgetContent() {
      var widgetContent = this.$refs.widgetContent;
      Matomo_Matomo.helper.destroyVueComponent(widgetContent);

      if (this.currentScope) {
        this.currentScope.$destroy();
      }

      if (widgetContent) {
        widgetContent.innerHTML = '';
      }
    },
    getWidgetUrl: function getWidgetUrl(parameters) {
      var urlParams = src_MatomoUrl_MatomoUrl.parsed.value;
      var fullParameters = Object.assign({}, parameters || {});
      var paramsToForward = Object.keys(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
        idSite: '',
        period: '',
        date: '',
        segment: '',
        widget: ''
      }));
      paramsToForward.forEach(function (key) {
        if (key === 'category' || key === 'subcategory') {
          return;
        }

        if (!(key in fullParameters)) {
          fullParameters[key] = urlParams[key];
        }
      });

      if (Comparisons_store_instance.isComparisonEnabled()) {
        fullParameters = Object.assign(Object.assign({}, fullParameters), {}, {
          comparePeriods: urlParams.comparePeriods,
          compareDates: urlParams.compareDates,
          compareSegments: urlParams.compareSegments
        });
      }

      if (!parameters || !('showtitle' in parameters)) {
        fullParameters.showtitle = '1';
      }

      if (Matomo_Matomo.shouldPropagateTokenAuth && urlParams.token_auth) {
        if (!Matomo_Matomo.broadcast.isWidgetizeRequestWithoutSession()) {
          fullParameters.force_api_session = '1';
        }

        fullParameters.token_auth = urlParams.token_auth;
      }

      fullParameters.random = Math.floor(Math.random() * 10000);
      return fullParameters;
    },
    loadWidgetUrl: function loadWidgetUrl(parameters, thisChangeId) {
      var _this = this;

      this.loading = true;
      this.abortHttpRequestIfNeeded();
      this.cleanupLastWidgetContent();
      this.lastWidgetAbortController = new AbortController();
      AjaxHelper_AjaxHelper.fetch(this.getWidgetUrl(parameters), {
        format: 'html',
        abortController: this.lastWidgetAbortController
      }).then(function (response) {
        if (thisChangeId !== _this.changeCounter || typeof response !== 'string') {
          // another widget was requested meanwhile, ignore this response
          return;
        }

        _this.lastWidgetAbortController = null;
        _this.loading = false;
        _this.loadingFailed = false;
        var widgetContent = _this.$refs.widgetContent;
        window.$(widgetContent).html(response);
        var $content = window.$(widgetContent).children();

        if (_this.widgetName) {
          // we need to respect the widget title, which overwrites a possibly set report title
          var $title = $content.find('> .card-content .card-title');

          if (!$title.length) {
            $title = $content.find('> h2');
          }

          if ($title.length) {
            // required to use htmlEntities since it also escapes '{{' format items
            $title.html(Matomo_Matomo.helper.htmlEntities(_this.widgetName));
          }
        }

        var $rootScope = Matomo_Matomo.helper.getAngularDependency('$rootScope');
        var scope = $rootScope.$new();
        _this.currentScope = scope; // compile angularjs first since it will modify all dom nodes, breaking vue bindings
        // if they are present

        Matomo_Matomo.helper.compileAngularComponents($content, {
          scope: scope
        });
        Matomo_Matomo.helper.compileVueEntryComponents($content);
        Notifications_store.parseNotificationDivs();
        setTimeout(function () {
          Matomo_Matomo.postEvent('widget:loaded', {
            parameters: parameters,
            element: $content
          });
        });
      }).catch(function (response) {
        if (thisChangeId !== _this.changeCounter) {
          // another widget was requested meanwhile, ignore this response
          return;
        }

        _this.lastWidgetAbortController = null;

        _this.cleanupLastWidgetContent();

        _this.loading = false;

        if (response.xhrStatus === 'abort') {
          return;
        }

        if (response.status === 429) {
          _this.loadingFailedRateLimit = true;
        }

        _this.loadingFailed = true;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue



WidgetLoadervue_type_script_lang_ts.render = WidgetLoadervue_type_template_id_43c0d326_render

/* harmony default export */ var WidgetLoader = (WidgetLoadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var WidgetLoader_adapter = (createAngularJsAdapter({
  component: WidgetLoader,
  scope: {
    piwikWidgetLoader: {
      vue: 'widgetParams',
      angularJsBind: '='
    },
    widgetName: {
      angularJsBind: '@'
    }
  },
  directiveName: 'piwikWidgetLoader'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=template&id=41745e0f

function WidgetContainervue_type_template_id_41745e0f_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Widget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Widget");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.actualContainer, function (widget, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Widget, {
      widget: widget,
      "prevent-recursion": true
    }, null, 8, ["widget"])])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=template&id=41745e0f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=script&lang=ts
function WidgetContainervue_type_script_lang_ts_toConsumableArray(arr) { return WidgetContainervue_type_script_lang_ts_arrayWithoutHoles(arr) || WidgetContainervue_type_script_lang_ts_iterableToArray(arr) || WidgetContainervue_type_script_lang_ts_unsupportedIterableToArray(arr) || WidgetContainervue_type_script_lang_ts_nonIterableSpread(); }

function WidgetContainervue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function WidgetContainervue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function WidgetContainervue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return WidgetContainervue_type_script_lang_ts_arrayLikeToArray(arr); }

function WidgetContainervue_type_script_lang_ts_slicedToArray(arr, i) { return WidgetContainervue_type_script_lang_ts_arrayWithHoles(arr) || WidgetContainervue_type_script_lang_ts_iterableToArrayLimit(arr, i) || WidgetContainervue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || WidgetContainervue_type_script_lang_ts_nonIterableRest(); }

function WidgetContainervue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function WidgetContainervue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return WidgetContainervue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return WidgetContainervue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function WidgetContainervue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function WidgetContainervue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function WidgetContainervue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }


 // since we're recursing, don't import the plugin directly

var Widget = useExternalPluginComponent('CoreHome', 'Widget');
/* harmony default export */ var WidgetContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    container: {
      type: Array,
      required: true
    }
  },
  components: {
    Widget: Widget
  },
  computed: {
    actualContainer: function actualContainer() {
      var _container$, _widget$parameters, _widget$parameters2;

      var container = this.container;

      if (!(container !== null && container !== void 0 && (_container$ = container[0]) !== null && _container$ !== void 0 && _container$.parameters)) {
        return container;
      }

      var _container = WidgetContainervue_type_script_lang_ts_slicedToArray(container, 1),
          widget = _container[0];

      var isWidgetized = ((_widget$parameters = widget.parameters) === null || _widget$parameters === void 0 ? void 0 : _widget$parameters.widget) === '1' || ((_widget$parameters2 = widget.parameters) === null || _widget$parameters2 === void 0 ? void 0 : _widget$parameters2.widget) === 1;
      var isGraphEvolution = isWidgetized && widget.viewDataTable === 'graphEvolution'; // we hide the first title for Visits Overview with Graph and Goal Overview

      var firstWidget = isGraphEvolution ? Object.assign(Object.assign({}, widget), {}, {
        parameters: Object.assign(Object.assign({}, widget.parameters), {}, {
          showtitle: '0'
        })
      }) : widget;
      return [firstWidget].concat(WidgetContainervue_type_script_lang_ts_toConsumableArray(container.slice(1)));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue



WidgetContainervue_type_script_lang_ts.render = WidgetContainervue_type_template_id_41745e0f_render

/* harmony default export */ var WidgetContainer = (WidgetContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var WidgetContainer_adapter = (createAngularJsAdapter({
  component: WidgetContainer,
  scope: {
    container: {
      angularJsBind: '=piwikWidgetContainer'
    }
  },
  directiveName: 'piwikWidgetContainer'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=template&id=3681f928

var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_1 = {
  class: "reportsByDimensionView"
};
var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_2 = {
  class: "entityList"
};
var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_3 = {
  class: "listCircle"
};
var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_4 = ["onClick"];
var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_5 = {
  class: "dimension"
};
var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_6 = {
  class: "reportContainer"
};

var WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);

function WidgetByDimensionContainervue_type_template_id_3681f928_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_WidgetLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetLoader");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.widgetsByCategory, function (category) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "dimensionCategory",
      key: category.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_3, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.widgets, function (widget) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["reportDimension", {
          activeDimension: _ctx.selectedWidget.uniqueId === widget.uniqueId
        }]),
        key: widget.uniqueId,
        onClick: function onClick($event) {
          return _ctx.selectWidget(widget);
        }
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(widget.name), 1)], 10, WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_4);
    }), 128))])]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_6, [_ctx.selectedWidget.parameters ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_WidgetLoader, {
    key: 0,
    "widget-params": _ctx.selectedWidget.parameters,
    class: "dimensionReport"
  }, null, 8, ["widget-params"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), WidgetByDimensionContainervue_type_template_id_3681f928_hoisted_7]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=template&id=3681f928

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=script&lang=ts
function WidgetByDimensionContainervue_type_script_lang_ts_slicedToArray(arr, i) { return WidgetByDimensionContainervue_type_script_lang_ts_arrayWithHoles(arr) || WidgetByDimensionContainervue_type_script_lang_ts_iterableToArrayLimit(arr, i) || WidgetByDimensionContainervue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || WidgetByDimensionContainervue_type_script_lang_ts_nonIterableRest(); }

function WidgetByDimensionContainervue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function WidgetByDimensionContainervue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return WidgetByDimensionContainervue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return WidgetByDimensionContainervue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function WidgetByDimensionContainervue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function WidgetByDimensionContainervue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function WidgetByDimensionContainervue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }




/* harmony default export */ var WidgetByDimensionContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    widgets: Array
  },
  components: {
    WidgetLoader: WidgetLoader
  },
  data: function data() {
    return {
      selectedWidget: null
    };
  },
  created: function created() {
    var _this$widgetsSorted = WidgetByDimensionContainervue_type_script_lang_ts_slicedToArray(this.widgetsSorted, 1);

    this.selectedWidget = _this$widgetsSorted[0];
  },
  computed: {
    widgetsSorted: function widgetsSorted() {
      return sortOrderables(this.widgets);
    },
    widgetsByCategory: function widgetsByCategory() {
      var byCategory = {};
      this.widgetsSorted.forEach(function (widget) {
        var _widget$subcategory;

        var category = (_widget$subcategory = widget.subcategory) === null || _widget$subcategory === void 0 ? void 0 : _widget$subcategory.name;

        if (!category) {
          return;
        }

        if (!byCategory[category]) {
          byCategory[category] = {
            name: category,
            order: widget.order,
            widgets: []
          };
        }

        byCategory[category].widgets.push(widget);
      });
      return sortOrderables(Object.values(byCategory));
    }
  },
  methods: {
    selectWidget: function selectWidget(widget) {
      // we copy to force rerender if selecting same widget
      this.selectedWidget = Object.assign({}, widget);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue



WidgetByDimensionContainervue_type_script_lang_ts.render = WidgetByDimensionContainervue_type_template_id_3681f928_render

/* harmony default export */ var WidgetByDimensionContainer = (WidgetByDimensionContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var WidgetByDimensionContainer_adapter = (createAngularJsAdapter({
  component: WidgetByDimensionContainer,
  scope: {
    widgets: {
      angularJsBind: '=piwikWidgetByDimensionContainer',
      transform: function transform(v) {
        return v.widgets;
      }
    }
  },
  directiveName: 'piwikWidgetByDimensionContainer'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=template&id=23f53472

var Widgetvue_type_template_id_23f53472_hoisted_1 = ["id"];
var Widgetvue_type_template_id_23f53472_hoisted_2 = {
  key: 1
};
var Widgetvue_type_template_id_23f53472_hoisted_3 = {
  key: 2
};
function Widgetvue_type_template_id_23f53472_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_WidgetLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetLoader");

  var _component_WidgetContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetContainer");

  var _component_WidgetByDimensionContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetByDimensionContainer");

  var _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");

  return _ctx.actualWidget ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["matomo-widget", {
      'isFirstWidgetInPage': _ctx.actualWidget.isFirstInPage
    }]),
    id: _ctx.actualWidget.uniqueId
  }, [!_ctx.actualWidget.isContainer && _ctx.actualWidget.parameters ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_WidgetLoader, {
    key: 0,
    "widget-params": _ctx.actualWidget.parameters,
    "widget-name": _ctx.actualWidget.name
  }, null, 8, ["widget-params", "widget-name"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualWidget.isContainer && _ctx.actualWidget.layout !== 'ByDimension' && !this.preventRecursion ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Widgetvue_type_template_id_23f53472_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_WidgetContainer, {
    container: _ctx.actualWidget.widgets
  }, null, 8, ["container"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualWidget.isContainer && _ctx.actualWidget.layout === 'ByDimension' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Widgetvue_type_template_id_23f53472_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_WidgetByDimensionContainer, {
    widgets: _ctx.actualWidget.widgets
  }, null, 8, ["widgets"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, Widgetvue_type_template_id_23f53472_hoisted_1)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showWidget], [_directive_tooltips, {
    content: _ctx.tooltipContent
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=template&id=23f53472

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=script&lang=ts









function findContainer(widgetsByCategory, containerId) {
  var widget = undefined;
  Object.values(widgetsByCategory || {}).some(function (widgets) {
    widget = widgets.find(function (w) {
      var _w$parameters;

      return w && w.isContainer && ((_w$parameters = w.parameters) === null || _w$parameters === void 0 ? void 0 : _w$parameters.containerId) === containerId;
    });
    return widget;
  });
  return widget;
}
/**
 * Renders any kind of widget. If you have a widget and you want to have it rendered, use this
 * directive. It will display a name on top and the actual widget below. It can handle any kind
 * of widget, no matter whether it is a regular widget or a container.
 *
 * @param {Object} piwikWidget  A widget object as returned by the WidgetMetadata API.
 * @param {Object} piwikWidget.middlewareParameters   If present, we will request a URL using the
 *                                                    given parameters and only if this URL
 *                                                    returns a JSON `true` the widget will be
 *                                                    shown. Otherwise the widget won't be shown.
 * @param {String} containerId  If you do not have a widget object but a containerId we will find
 *                              the correct widget object based on the given containerId. Be aware
 *                              that we might not find the widget if it is for example not
 *                              available for the current user or period/date.
 * @param {Boolean} widgetized  true if the widget is widgetized (eg in Dashboard or exported).
 *                              In this case we will add a URL parameter widget=1 to all widgets.
 *                              Eg sparklines will be then displayed one after another
 *                              (vertically aligned) instead of two next to each other.
 *
 * Example:
 * <Widget :widget="widget"></Widget>
 * // in this case we will find the correct widget automatically
 * <Widget :containerid="widgetGoalsOverview"></Widget>
 * // disables rating feature, no initial headline
 * <Widget :widget="widget" :widetized="true"></Widget>
 */


/* harmony default export */ var Widgetvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    widget: Object,
    widgetized: Boolean,
    containerid: String,
    preventRecursion: Boolean
  },
  components: {
    WidgetLoader: WidgetLoader,
    WidgetContainer: WidgetContainer,
    WidgetByDimensionContainer: WidgetByDimensionContainer
  },
  directives: {
    Tooltips: Tooltips
  },
  data: function data() {
    return {
      showWidget: false
    };
  },
  setup: function setup() {
    function tooltipContent() {
      var $this = window.$(this);

      if ($this.attr('piwik-field') === '' || $this.hasClass('matomo-form-field')) {
        // do not show it for form fields
        return '';
      }

      var title = window.$(this).attr('title') || '';
      return window.vueSanitize(title.replace(/\n/g, '<br />'));
    }

    return {
      tooltipContent: tooltipContent
    };
  },
  created: function created() {
    var _this = this;

    var actualWidget = this.actualWidget;

    if (actualWidget && actualWidget.middlewareParameters) {
      var params = actualWidget.middlewareParameters;
      AjaxHelper_AjaxHelper.fetch(params).then(function (response) {
        _this.showWidget = !!response;
      });
    } else {
      this.showWidget = true;
    }
  },
  computed: {
    allWidgets: function allWidgets() {
      return Widgets_store.widgets.value;
    },
    actualWidget: function actualWidget() {
      var _this2 = this;

      var widget = this.widget;

      if (widget) {
        var result = Object.assign({}, widget);

        if (widget && widget.isReport && !widget.documentation) {
          var report = ReportMetadata_store.findReport(widget.module, widget.action);

          if (report && report.documentation) {
            result.documentation = report.documentation;
          }
        }

        return widget;
      }

      if (this.containerid) {
        var containerWidget = findContainer(this.allWidgets, this.containerid);

        if (containerWidget) {
          var _result = Object.assign({}, containerWidget);

          if (this.widgetized) {
            _result.isFirstInPage = true;
            _result.parameters = Object.assign(Object.assign({}, _result.parameters), {}, {
              widget: '1'
            });
            var widgets = getWidgetChildren(_result);

            if (widgets) {
              _result.widgets = widgets.map(function (w) {
                return Object.assign(Object.assign({}, w), {}, {
                  parameters: Object.assign(Object.assign({}, w.parameters), {}, {
                    widget: '1',
                    containerId: _this2.containerid
                  })
                });
              });
            }
          }

          return _result;
        }
      }

      return null;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue



Widgetvue_type_script_lang_ts.render = Widgetvue_type_template_id_23f53472_render

/* harmony default export */ var Widget_Widget = (Widgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Widget_adapter = (createAngularJsAdapter({
  component: Widget_Widget,
  scope: {
    widget: {
      angularJsBind: '=?piwikWidget'
    },
    widgetized: {
      angularJsBind: '=?'
    },
    containerid: {
      angularJsBind: '@'
    }
  },
  directiveName: 'piwikWidget'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=template&id=f0afbf38

var ReportingPagevue_type_template_id_f0afbf38_hoisted_1 = {
  class: "reporting-page"
};
var ReportingPagevue_type_template_id_f0afbf38_hoisted_2 = {
  key: 1,
  class: "col s12 l6 leftWidgetColumn"
};
var ReportingPagevue_type_template_id_f0afbf38_hoisted_3 = {
  key: 2,
  class: "col s12 l6 rightWidgetColumn"
};
function ReportingPagevue_type_template_id_f0afbf38_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Widget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Widget");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_f0afbf38_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading
  }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_NoSuchPage')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasNoPage]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.widgets, function (widget) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: widget.uniqueId
    }, [!widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
      key: 0,
      class: "col s12 fullWidgetColumn",
      widget: widget
    }, null, 8, ["widget"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_f0afbf38_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(widget.left, function (widgetInGroup) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
        widget: widgetInGroup,
        key: widgetInGroup.uniqueId
      }, null, 8, ["widget"]);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_f0afbf38_hoisted_3, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(widget.right, function (widgetInGroup) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
        widget: widgetInGroup,
        key: widgetInGroup.uniqueId
      }, null, 8, ["widget"]);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=template&id=f0afbf38

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.store.ts
function ReportingPage_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function ReportingPage_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function ReportingPage_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) ReportingPage_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) ReportingPage_store_defineProperties(Constructor, staticProps); return Constructor; }

function ReportingPage_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function ReportingPage_store_toConsumableArray(arr) { return ReportingPage_store_arrayWithoutHoles(arr) || ReportingPage_store_iterableToArray(arr) || ReportingPage_store_unsupportedIterableToArray(arr) || ReportingPage_store_nonIterableSpread(); }

function ReportingPage_store_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function ReportingPage_store_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return ReportingPage_store_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return ReportingPage_store_arrayLikeToArray(o, minLen); }

function ReportingPage_store_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function ReportingPage_store_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return ReportingPage_store_arrayLikeToArray(arr); }

function ReportingPage_store_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */






function shouldBeRenderedWithFullWidth(widget) {
  // rather controller logic
  if (widget.isContainer && widget.layout && widget.layout === 'ByDimension' || widget.viewDataTable === 'bydimension') {
    return true;
  }

  if (widget.isWide) {
    return true;
  }

  return widget.viewDataTable && (widget.viewDataTable === 'tableAllColumns' || widget.viewDataTable === 'sparklines' || widget.viewDataTable === 'graphEvolution');
}

function markWidgetsInFirstRowOfPage(widgets) {
  if (widgets && widgets[0]) {
    var newWidgets = ReportingPage_store_toConsumableArray(widgets);

    var groupedWidgets = widgets[0];

    if (groupedWidgets.group) {
      newWidgets[0] = Object.assign(Object.assign({}, newWidgets[0]), {}, {
        left: markWidgetsInFirstRowOfPage(groupedWidgets.left || []),
        right: markWidgetsInFirstRowOfPage(groupedWidgets.right || [])
      });
    } else {
      newWidgets[0] = Object.assign(Object.assign({}, newWidgets[0]), {}, {
        isFirstInPage: true
      });
    }

    return newWidgets;
  }

  return widgets;
}

var ReportingPage_store_ReportingPageStore = /*#__PURE__*/function () {
  function ReportingPageStore() {
    var _this = this;

    ReportingPage_store_classCallCheck(this, ReportingPageStore);

    ReportingPage_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({}));

    ReportingPage_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    ReportingPage_store_defineProperty(this, "page", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.page;
    }));

    ReportingPage_store_defineProperty(this, "widgets", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var page = _this.page.value;

      if (!page) {
        return [];
      }

      var widgets = [];
      var reportsToIgnore = {};

      var isIgnoredReport = function isIgnoredReport(widget) {
        return widget.isReport && reportsToIgnore["".concat(widget.module, ".").concat(widget.action)];
      };

      var getRelatedReports = function getRelatedReports(widget) {
        if (!widget.isReport) {
          return [];
        }

        var report = ReportMetadata_store.findReport(widget.module, widget.action);

        if (!report || !report.relatedReports) {
          return [];
        }

        return report.relatedReports;
      };

      (page.widgets || []).forEach(function (widget) {
        if (isIgnoredReport(widget)) {
          return;
        }

        getRelatedReports(widget).forEach(function (report) {
          reportsToIgnore["".concat(report.module, ".").concat(report.action)] = true;
        });
        widgets.push(widget);
      });
      widgets = sortOrderables(widgets);

      if (widgets.length === 1) {
        // if there is only one widget, we always display it full width
        return markWidgetsInFirstRowOfPage(widgets);
      }

      var groupedWidgets = [];

      for (var i = 0; i < widgets.length; i += 1) {
        var widget = widgets[i];

        if (shouldBeRenderedWithFullWidth(widget) || widgets[i + 1] && shouldBeRenderedWithFullWidth(widgets[i + 1])) {
          groupedWidgets.push(Object.assign(Object.assign({}, widget), {}, {
            widgets: sortOrderables(getWidgetChildren(widget))
          }));
        } else {
          var counter = 0;
          var left = [widget];
          var right = [];

          while (widgets[i + 1] && !shouldBeRenderedWithFullWidth(widgets[i + 1])) {
            i += 1;
            counter += 1;

            if (counter % 2 === 0) {
              left.push(widgets[i]);
            } else {
              right.push(widgets[i]);
            }
          }

          groupedWidgets.push({
            group: true,
            left: left,
            right: right
          });
        }
      }

      var sortedWidgets = markWidgetsInFirstRowOfPage(groupedWidgets);
      return sortedWidgets;
    }));
  }

  ReportingPage_store_createClass(ReportingPageStore, [{
    key: "fetchPage",
    value: function fetchPage(category, subcategory) {
      var _this2 = this;

      this.resetPage();
      return Promise.all([ReportingPages_store.getAllPages(), ReportMetadata_store.fetchReportMetadata()]).then(function () {
        _this2.privateState.page = ReportingPages_store.findPage(category, subcategory);
        return _this2.page.value;
      });
    }
  }, {
    key: "resetPage",
    value: function resetPage() {
      this.privateState.page = undefined;
    }
  }]);

  return ReportingPageStore;
}();
/* harmony default export */ var ReportingPage_store = (new ReportingPage_store_ReportingPageStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=script&lang=ts












function showOnlyRawDataNotification() {
  var params = 'category=General_Visitors&subcategory=Live_VisitorLog';
  var url = window.broadcast.buildReportingUrl(params);
  Notifications_store.show({
    id: 'onlyRawData',
    animate: false,
    context: 'info',
    message: translate('CoreHome_PeriodHasOnlyRawData', "<a href=\"".concat(url, "\">"), '</a>'),
    type: 'transient'
  });
}

function hideOnlyRawDataNoticifation() {
  Notifications_store.remove('onlyRawData');
}

/* harmony default export */ var ReportingPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: ActivityIndicator,
    Widget: Widget_Widget
  },
  data: function data() {
    return {
      loading: false,
      hasRawData: false,
      hasNoVisits: false,
      dateLastChecked: null,
      hasNoPage: false
    };
  },
  created: function created() {
    var _this = this;

    ReportingPage_store.resetPage();
    this.loading = true; // we only set loading on initial load

    this.renderInitialPage();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return src_MatomoUrl_MatomoUrl.parsed.value;
    }, function (newValue, oldValue) {
      if (newValue.category === oldValue.category && newValue.subcategory === oldValue.subcategory && newValue.period === oldValue.period && newValue.date === oldValue.date && newValue.segment === oldValue.segment && JSON.stringify(newValue.compareDates) === JSON.stringify(oldValue.compareDates) && JSON.stringify(newValue.comparePeriods) === JSON.stringify(oldValue.comparePeriods) && JSON.stringify(newValue.compareSegments) === JSON.stringify(oldValue.compareSegments) && JSON.stringify(newValue.columns || '') === JSON.stringify(oldValue.columns || '')) {
        // this page is already loaded
        return;
      }

      if (newValue.date !== oldValue.date || newValue.period !== oldValue.period) {
        hideOnlyRawDataNoticifation();
        _this.dateLastChecked = null;
        _this.hasRawData = false;
        _this.hasNoVisits = false;
      }

      _this.renderPage(newValue.category, newValue.subcategory);
    });
    Matomo_Matomo.on('loadPage', function (category, subcategory) {
      _this.renderPage(category, subcategory);
    });
  },
  computed: {
    widgets: function widgets() {
      return ReportingPage_store.widgets.value;
    }
  },
  methods: {
    renderPage: function renderPage(category, subcategory) {
      var _this2 = this;

      if (!category || !subcategory) {
        ReportingPage_store.resetPage();
        this.loading = false;
        return;
      }

      var parsedUrl = src_MatomoUrl_MatomoUrl.parsed.value;
      var currentPeriod = parsedUrl.period;
      var currentDate = parsedUrl.date;

      try {
        Periods_Periods.parse(currentPeriod, currentDate);
      } catch (e) {
        Notifications_store.show({
          id: 'invalidDate',
          animate: false,
          context: 'error',
          message: translate('CoreHome_DateInvalid'),
          type: 'transient'
        });
        ReportingPage_store.resetPage();
        this.loading = false;
        return;
      }

      Notifications_store.remove('invalidDate');
      Matomo_Matomo.postEvent('piwikPageChange', {});
      Notifications_store.clearTransientNotifications();

      if (Periods_Periods.parse(currentPeriod, currentDate).containsToday()) {
        this.showOnlyRawDataMessageIfRequired();
      }

      var params = {
        category: category,
        subcategory: subcategory
      };
      Matomo_Matomo.postEvent('ReportingPage.loadPage', params);

      if (params.promise) {
        this.loading = true;
        Promise.resolve(params.promise).finally(function () {
          _this2.loading = false;
        });
        return;
      }

      ReportingPage_store.fetchPage(category, subcategory).then(function () {
        var hasNoPage = !ReportingPage_store.page.value;

        if (hasNoPage) {
          var page = ReportingPages_store.findPageInCategory(category);

          if (page && page.subcategory) {
            src_MatomoUrl_MatomoUrl.updateHash(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
              subcategory: page.subcategory.id
            }));
            return;
          }
        }

        _this2.hasNoPage = hasNoPage;
        _this2.loading = false;
      });
    },
    renderInitialPage: function renderInitialPage() {
      var parsed = src_MatomoUrl_MatomoUrl.parsed.value;
      this.renderPage(parsed.category, parsed.subcategory);
    },
    showOnlyRawDataMessageIfRequired: function showOnlyRawDataMessageIfRequired() {
      var _this3 = this;

      if (this.hasRawData && this.hasNoVisits) {
        showOnlyRawDataNotification();
      }

      var parsedUrl = src_MatomoUrl_MatomoUrl.parsed.value;
      var segment = parsedUrl.segment;

      if (segment) {
        hideOnlyRawDataNoticifation();
        return;
      }

      var subcategoryExceptions = ['Live_VisitorLog', 'General_RealTime', 'UserCountryMap_RealTimeMap', 'MediaAnalytics_TypeAudienceLog', 'MediaAnalytics_TypeRealTime', 'FormAnalytics_TypeRealTime', 'Goals_AddNewGoal'];
      var categoryExceptions = ['HeatmapSessionRecording_Heatmaps', 'HeatmapSessionRecording_SessionRecordings', 'Marketplace_Marketplace'];
      var subcategory = parsedUrl.subcategory;
      var category = parsedUrl.category;

      if (subcategoryExceptions.indexOf(subcategory) !== -1 || categoryExceptions.indexOf(category) !== -1 || subcategory.toLowerCase().indexOf('manage') !== -1) {
        hideOnlyRawDataNoticifation();
        return;
      }

      var minuteInMilliseconds = 60000;

      if (this.dateLastChecked && new Date().valueOf() - this.dateLastChecked.valueOf() < minuteInMilliseconds) {
        return;
      }

      AjaxHelper_AjaxHelper.fetch({
        method: 'VisitsSummary.getVisits'
      }).then(function (json) {
        _this3.dateLastChecked = new Date();

        if (json.value > 0) {
          _this3.hasNoVisits = false;
          hideOnlyRawDataNoticifation();
          return undefined;
        }

        _this3.hasNoVisits = true;

        if (_this3.hasRawData) {
          showOnlyRawDataNotification();
          return undefined;
        }

        return AjaxHelper_AjaxHelper.fetch({
          method: 'Live.getLastVisitsDetails',
          filter_limit: 1,
          doNotFetchActions: 1
        }).then(function (lastVisits) {
          if (!lastVisits || lastVisits.length === 0) {
            _this3.hasRawData = false;
            hideOnlyRawDataNoticifation();
            return;
          }

          _this3.hasRawData = true;
          showOnlyRawDataNotification();
        });
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue



ReportingPagevue_type_script_lang_ts.render = ReportingPagevue_type_template_id_f0afbf38_render

/* harmony default export */ var ReportingPage = (ReportingPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ReportingPage_adapter = (createAngularJsAdapter({
  component: ReportingPage,
  directiveName: 'piwikReportingPage'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=template&id=3d203950

var ReportExportPopovervue_type_template_id_3d203950_hoisted_1 = {
  class: "report-export-popover row",
  id: "reportExport"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_2 = {
  class: "col l6"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_3 = {
  name: "format"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_4 = {
  name: "option_flat"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_5 = {
  name: "option_expanded"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_6 = {
  name: "option_format_metrics"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_7 = {
  class: "col l6"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_8 = {
  name: "filter_type"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_9 = {
  class: "filter_limit"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_10 = {
  name: "filter_limit_all"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_11 = {
  key: 0,
  name: "filter_limit"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_12 = {
  key: 1,
  name: "filter_limit"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_13 = {
  class: "col l12"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_14 = ["value"];

var ReportExportPopovervue_type_template_id_3d203950_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("\n      ");

var ReportExportPopovervue_type_template_id_3d203950_hoisted_16 = [ReportExportPopovervue_type_template_id_3d203950_hoisted_15];
var ReportExportPopovervue_type_template_id_3d203950_hoisted_17 = ["innerHTML"];
var ReportExportPopovervue_type_template_id_3d203950_hoisted_18 = {
  class: "col l12"
};
var ReportExportPopovervue_type_template_id_3d203950_hoisted_19 = ["href", "title"];
function ReportExportPopovervue_type_template_id_3d203950_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'format',
    title: _ctx.translate('CoreHome_ExportFormat'),
    modelValue: _ctx.reportFormat,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.reportFormat = $event;
    }),
    "full-width": true,
    options: _ctx.availableReportFormats[_ctx.reportType]
  }, null, 8, ["title", "modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_flat',
    title: _ctx.translate('CoreHome_FlattenReport'),
    modelValue: _ctx.optionFlat,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.optionFlat = $event;
    })
  }, null, 8, ["title", "modelValue"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSubtables]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_expanded',
    title: _ctx.translate('CoreHome_ExpandSubtables'),
    modelValue: _ctx.optionExpanded,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.optionExpanded = $event;
    })
  }, null, 8, ["title", "modelValue"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSubtables && !_ctx.optionFlat]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_format_metrics',
    title: _ctx.translate('CoreHome_FormatMetrics'),
    modelValue: _ctx.optionFormatMetrics,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.optionFormatMetrics = $event;
    })
  }, null, 8, ["title", "modelValue"])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'filter_type',
    title: _ctx.translate('CoreHome_ReportType'),
    modelValue: _ctx.reportType,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return _ctx.reportType = $event;
    }),
    "full-width": true,
    options: _ctx.availableReportTypes
  }, null, 8, ["title", "modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'filter_limit_all',
    title: _ctx.translate('CoreHome_RowLimit'),
    modelValue: _ctx.reportLimitAll,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.reportLimitAll = $event;
    }),
    "full-width": true,
    options: _ctx.limitAllOptions
  }, null, 8, ["title", "modelValue", "options"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.maxFilterLimit || _ctx.maxFilterLimit <= 0]]), _ctx.reportLimitAll === 'no' && _ctx.maxFilterLimit <= 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'number',
    name: "filter_limit",
    min: 1,
    modelValue: _ctx.reportLimit,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.reportLimit = $event;
    }),
    "full-width": true
  }, null, 8, ["modelValue"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.reportLimitAll === 'no' && _ctx.maxFilterLimit > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'number',
    name: 'filter_limit',
    min: 1,
    max: _ctx.maxFilterLimit,
    modelValue: _ctx.reportLimit,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.reportLimit = $event;
    }),
    value: _ctx.reportLimit,
    "full-width": true,
    title: _ctx.filterLimitTooltip
  }, null, 8, ["max", "modelValue", "value", "title"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", {
    readonly: "",
    class: "exportFullUrl",
    value: _ctx.exportLinkWithoutToken
  }, ReportExportPopovervue_type_template_id_3d203950_hoisted_16, 8, ReportExportPopovervue_type_template_id_3d203950_hoisted_14), [[_directive_select_on_focus, {}]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "tooltip",
    innerHTML: _ctx.$sanitize(_ctx.translate('CoreHome_ExportTooltipWithLink', '<a target=_blank href=\'?module=UsersManager&action=userSecurity\'>', '</a>', 'ENTER_YOUR_TOKEN_AUTH_HERE'))
  }, null, 8, ReportExportPopovervue_type_template_id_3d203950_hoisted_17)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_3d203950_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn",
    href: _ctx.exportLink,
    target: "_new",
    title: _ctx.translate('CoreHome_ExportTooltip')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Export')), 9, ReportExportPopovervue_type_template_id_3d203950_hoisted_19), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "javascript:",
    onClick: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.showUrl = !_ctx.showUrl;
    }),
    class: "toggle-export-url"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ShowExportUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.showUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_HideExportUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showUrl]])])])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=template&id=3d203950

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=script&lang=ts
function ReportExportPopovervue_type_script_lang_ts_slicedToArray(arr, i) { return ReportExportPopovervue_type_script_lang_ts_arrayWithHoles(arr) || ReportExportPopovervue_type_script_lang_ts_iterableToArrayLimit(arr, i) || ReportExportPopovervue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || ReportExportPopovervue_type_script_lang_ts_nonIterableRest(); }

function ReportExportPopovervue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function ReportExportPopovervue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return ReportExportPopovervue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return ReportExportPopovervue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function ReportExportPopovervue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function ReportExportPopovervue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function ReportExportPopovervue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }







var ReportExportPopovervue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
/* harmony default export */ var ReportExportPopovervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: ReportExportPopovervue_type_script_lang_ts_Field
  },
  directives: {
    SelectOnFocus: SelectOnFocus
  },
  props: {
    hasSubtables: Boolean,
    availableReportTypes: Object,
    availableReportFormats: {
      type: Object,
      required: true
    },
    maxFilterLimit: Number,
    limitAllOptions: Object,
    dataTable: {
      type: Object,
      required: true
    },
    requestParams: [Object, String],
    apiMethod: {
      type: String,
      required: true
    },
    initialReportType: {
      type: String,
      default: 'default'
    },
    initialReportLimit: {
      type: [String, Number],
      default: 100
    },
    initialReportLimitAll: {
      type: String,
      default: 'yes'
    },
    initialOptionFlat: {
      type: Boolean,
      default: false
    },
    initialOptionExpanded: {
      type: Boolean,
      default: true
    },
    initialOptionFormatMetrics: {
      type: Boolean,
      default: false
    },
    initialReportFormat: {
      type: String,
      default: 'XML'
    }
  },
  data: function data() {
    return {
      showUrl: false,
      reportFormat: this.initialReportFormat,
      optionFlat: this.initialOptionFlat,
      optionExpanded: this.initialOptionExpanded,
      optionFormatMetrics: this.initialOptionFormatMetrics,
      reportType: this.initialReportType,
      reportLimitAll: this.initialReportLimitAll,
      reportLimit: typeof this.initialReportLimit === 'string' ? parseInt(this.initialReportLimit, 10) : this.initialReportLimit
    };
  },
  watch: {
    reportType: function reportType(newVal) {
      if (!this.availableReportFormats[newVal][this.reportFormat]) {
        this.reportFormat = 'XML';
      }
    },
    reportLimit: function reportLimit(newVal, oldVal) {
      if (this.maxFilterLimit && this.maxFilterLimit > 0 && newVal > this.maxFilterLimit) {
        this.reportLimit = oldVal;
      }
    }
  },
  computed: {
    filterLimitTooltip: function filterLimitTooltip() {
      var rowLimit = translate('CoreHome_RowLimit');
      var computedMetricMax = this.maxFilterLimit ? translate('General_ComputedMetricMax', this.maxFilterLimit.toString()) : '';
      return "".concat(rowLimit, " (").concat(computedMetricMax, ")");
    },
    exportLink: function exportLink() {
      return this.getExportLink(true);
    },
    exportLinkWithoutToken: function exportLinkWithoutToken() {
      return this.getExportLink(false);
    }
  },
  methods: {
    getExportLink: function getExportLink() {
      var withToken = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      var reportFormat = this.reportFormat,
          apiMethod = this.apiMethod,
          reportType = this.reportType;
      var dataTable = this.dataTable;

      if (!reportFormat) {
        return undefined;
      }

      var requestParams = {};
      var limit = this.reportLimitAll === 'yes' ? -1 : this.reportLimit;

      if (this.requestParams && typeof this.requestParams === 'string') {
        requestParams = JSON.parse(this.requestParams);
      }

      var _dataTable$param = dataTable.param,
          segment = _dataTable$param.segment,
          label = _dataTable$param.label,
          idGoal = _dataTable$param.idGoal,
          idDimension = _dataTable$param.idDimension,
          idSite = _dataTable$param.idSite;
      var _dataTable$param2 = dataTable.param,
          date = _dataTable$param2.date,
          period = _dataTable$param2.period;

      if (reportFormat === 'RSS') {
        date = 'last10';
      }

      if (typeof dataTable.param.dateUsedInGraph !== 'undefined') {
        date = dataTable.param.dateUsedInGraph;
      }

      var formatsUseDayNotRange = Matomo_Matomo.config.datatable_export_range_as_day.toLowerCase();

      if (formatsUseDayNotRange.indexOf(reportFormat.toLowerCase()) !== -1 && dataTable.param.period === 'range') {
        period = 'day';
      } // Below evolution graph, show daily exports


      if (dataTable.param.period === 'range' && dataTable.param.viewDataTable === 'graphEvolution') {
        period = 'day';
      }

      var exportUrlParams = {
        module: 'API',
        format: reportFormat,
        idSite: idSite,
        period: period,
        date: date
      };

      if (reportType === 'processed') {
        exportUrlParams.method = 'API.getProcessedReport';

        var _apiMethod$split = apiMethod.split('.');

        var _apiMethod$split2 = ReportExportPopovervue_type_script_lang_ts_slicedToArray(_apiMethod$split, 2);

        exportUrlParams.apiModule = _apiMethod$split2[0];
        exportUrlParams.apiAction = _apiMethod$split2[1];
      } else {
        exportUrlParams.method = apiMethod;
      }

      if (dataTable.param.compareDates && dataTable.param.compareDates.length) {
        exportUrlParams.compareDates = dataTable.param.compareDates;
        exportUrlParams.compare = '1';
      }

      if (dataTable.param.comparePeriods && dataTable.param.comparePeriods.length) {
        exportUrlParams.comparePeriods = dataTable.param.comparePeriods;
        exportUrlParams.compare = '1';
      }

      if (dataTable.param.compareSegments && dataTable.param.compareSegments.length) {
        exportUrlParams.compareSegments = dataTable.param.compareSegments;
        exportUrlParams.compare = '1';
      }

      if (typeof dataTable.param.filter_pattern !== 'undefined') {
        exportUrlParams.filter_pattern = dataTable.param.filter_pattern;
      }

      if (typeof dataTable.param.filter_pattern_recursive !== 'undefined') {
        exportUrlParams.filter_pattern_recursive = dataTable.param.filter_pattern_recursive;
      }

      if (window.$.isPlainObject(requestParams)) {
        Object.entries(requestParams).forEach(function (_ref) {
          var _ref2 = ReportExportPopovervue_type_script_lang_ts_slicedToArray(_ref, 2),
              index = _ref2[0],
              param = _ref2[1];

          var value = param;

          if (value === true) {
            value = 1;
          } else if (value === false) {
            value = 0;
          }

          exportUrlParams[index] = value;
        });
      }

      if (this.optionFlat) {
        exportUrlParams.flat = 1;

        if (typeof dataTable.param.include_aggregate_rows !== 'undefined' && dataTable.param.include_aggregate_rows === '1') {
          exportUrlParams.include_aggregate_rows = 1;
        }
      }

      if (!this.optionFlat && this.optionExpanded) {
        exportUrlParams.expanded = 1;
      }

      if (this.optionFormatMetrics) {
        exportUrlParams.format_metrics = 1;
      }

      if (dataTable.param.pivotBy) {
        exportUrlParams.pivotBy = dataTable.param.pivotBy;
        exportUrlParams.pivotByColumnLimit = 20;

        if (dataTable.props.pivot_by_column) {
          exportUrlParams.pivotByColumn = dataTable.props.pivot_by_column;
        }
      }

      if (reportFormat === 'CSV' || reportFormat === 'TSV' || reportFormat === 'RSS') {
        exportUrlParams.translateColumnNames = 1;
        exportUrlParams.language = Matomo_Matomo.language;
      }

      if (typeof segment !== 'undefined') {
        exportUrlParams.segment = decodeURIComponent(segment);
      } // Export Goals specific reports


      if (typeof idGoal !== 'undefined' && idGoal !== '-1') {
        exportUrlParams.idGoal = idGoal;
      } // Export Dimension specific reports


      if (typeof idDimension !== 'undefined' && idDimension !== '-1') {
        exportUrlParams.idDimension = idDimension;
      }

      if (label) {
        var labelParts = label.split(',');

        if (labelParts.length > 1) {
          exportUrlParams.label = labelParts;
        } else {
          var _labelParts = ReportExportPopovervue_type_script_lang_ts_slicedToArray(labelParts, 1);

          exportUrlParams.label = _labelParts[0];
        }
      }

      exportUrlParams.token_auth = 'ENTER_YOUR_TOKEN_AUTH_HERE';

      if (withToken === true) {
        exportUrlParams.token_auth = Matomo_Matomo.token_auth;
        exportUrlParams.force_api_session = 1;
      }

      exportUrlParams.filter_limit = limit;
      var prefix = window.location.href.split('?')[0];
      return "".concat(prefix, "?").concat(src_MatomoUrl_MatomoUrl.stringify(exportUrlParams));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue



ReportExportPopovervue_type_script_lang_ts.render = ReportExportPopovervue_type_template_id_3d203950_render

/* harmony default export */ var ReportExportPopover = (ReportExportPopovervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExport.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





var ReportExport_window = window,
    ReportExport_$ = ReportExport_window.$;
/* harmony default export */ var ReportExport = ({
  mounted: function mounted(el, binding) {
    el.addEventListener('click', function () {
      var popoverParamBackup = src_MatomoUrl_MatomoUrl.hashParsed.value.popover;
      var dataTable = ReportExport_$(el).closest('[data-report]').data('uiControlObject');
      var popover = window.Piwik_Popover.showLoading('Export');
      var formats = binding.value.reportFormats;
      var reportLimit = dataTable.param.filter_limit;

      if (binding.value.maxFilterLimit > 0) {
        reportLimit = Math.min(reportLimit, binding.value.maxFilterLimit);
      }

      var optionFlat = dataTable.param.flat === true || dataTable.param.flat === 1 || dataTable.param.flat === '1';
      var props = {
        initialReportType: 'default',
        initialReportLimit: reportLimit > 0 ? reportLimit : 100,
        initialReportLimitAll: reportLimit === -1 ? 'yes' : 'no',
        initialOptionFlat: optionFlat,
        initialOptionExpanded: true,
        initialOptionFormatMetrics: false,
        hasSubtables: optionFlat || dataTable.numberOfSubtables > 0,
        availableReportFormats: {
          default: formats,
          processed: {
            XML: formats.XML,
            JSON: formats.JSON
          }
        },
        availableReportTypes: {
          default: translate('CoreHome_StandardReport'),
          processed: translate('CoreHome_ReportWithMetadata')
        },
        limitAllOptions: {
          yes: translate('General_All'),
          no: translate('CoreHome_CustomLimit')
        },
        maxFilterLimit: binding.value.maxFilterLimit,
        dataTable: dataTable,
        requestParams: binding.value.requestParams,
        apiMethod: binding.value.apiMethod
      };
      var app = createVueApp({
        template: "\n          <popover v-bind=\"bind\"/>",
        data: function data() {
          return {
            bind: props
          };
        }
      });
      app.component('popover', ReportExportPopover);
      var mountPoint = document.createElement('div');
      app.mount(mountPoint);
      var reportTitle = binding.value.reportTitle;
      window.Piwik_Popover.setTitle("".concat(translate('General_Export'), " ").concat(Matomo_Matomo.helper.htmlEntities(reportTitle)));
      window.Piwik_Popover.setContent(mountPoint);
      window.Piwik_Popover.onClose(function () {
        app.unmount();

        if (popoverParamBackup !== '') {
          setTimeout(function () {
            src_MatomoUrl_MatomoUrl.updateHash(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
              popover: popoverParamBackup
            }));

            if (binding.value.onClose) {
              binding.value.onClose();
            }
          }, 100);
        }
      });
      setTimeout(function () {
        popover.dialog();
        ReportExport_$('.exportFullUrl, .btn', popover).tooltip({
          track: true,
          show: false,
          hide: false
        });
      }, 100);
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExport.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikReportExport($timeout) {
  return {
    restrict: 'A',
    scope: {
      reportTitle: '@',
      requestParams: '@',
      reportFormats: '@',
      apiMethod: '@',
      maxFilterLimit: '@'
    },
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    link: function piwikReportExportLink(scope, element) {
      var binding = {
        instance: null,
        value: {
          reportTitle: scope.reportTitle,
          requestParams: scope.requestParams,
          reportFormats: typeof scope.reportFormats === 'string' ? JSON.parse(scope.reportFormats) : scope.reportFormats,
          apiMethod: scope.apiMethod,
          maxFilterLimit: parseInt(scope.maxFilterLimit, 10),
          onClose: function onClose() {
            $timeout(function () {
              window.angular.element(document).injector().get('$rootScope').$apply();
            }, 10);
          }
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      ReportExport.mounted(element[0], binding);
    }
  };
}
piwikReportExport.$inject = ['$timeout'];
window.angular.module('piwikApp').directive('piwikReportExport', piwikReportExport);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=template&id=25e2c9f0

var Sparklinevue_type_template_id_25e2c9f0_hoisted_1 = ["src"];
function Sparklinevue_type_template_id_25e2c9f0_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    src: _ctx.sparklineUrl
  }, null, 8, Sparklinevue_type_template_id_25e2c9f0_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=template&id=25e2c9f0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=script&lang=ts






/* harmony default export */ var Sparklinevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    seriesIndices: Array,
    params: Object
  },
  data: function data() {
    return {
      isWidget: false
    };
  },
  mounted: function mounted() {
    this.isWidget = !!this.$el.closest('[widgetId]');
  },
  computed: {
    sparklineUrl: function sparklineUrl() {
      var seriesIndices = this.seriesIndices,
          params = this.params;
      var sparklineColors = Matomo_Matomo.getSparklineColors();

      if (seriesIndices) {
        sparklineColors.lineColor = sparklineColors.lineColor.filter(function (c, index) {
          return seriesIndices.indexOf(index) !== -1;
        });
      }

      var colors = JSON.stringify(sparklineColors);
      var defaultParams = {
        forceView: '1',
        viewDataTable: 'sparkline',
        widget: this.isWidget ? '1' : '0',
        showtitle: '1',
        colors: colors,
        random: Date.now(),
        date: this.defaultDate,
        // mixinDefaultGetParams() will use the raw, encoded value from the URL (legacy behavior),
        // which means MatomoUrl.stringify() will end up double encoding it if we don't set it
        // ourselves here.
        segment: src_MatomoUrl_MatomoUrl.parsed.value.segment
      };
      var helper = new AjaxHelper_AjaxHelper();
      var urlParams = helper.mixinDefaultGetParams(Object.assign(Object.assign({}, defaultParams), params)); // Append the token_auth to the URL if it was set (eg. embed dashboard)

      var token_auth = src_MatomoUrl_MatomoUrl.parsed.value.token_auth;

      if (token_auth && token_auth.length && Matomo_Matomo.shouldPropagateTokenAuth) {
        urlParams.token_auth = token_auth;
      }

      return "?".concat(src_MatomoUrl_MatomoUrl.stringify(urlParams));
    },
    defaultDate: function defaultDate() {
      if (Matomo_Matomo.period === 'range') {
        return "".concat(Matomo_Matomo.startDateString, ",").concat(Matomo_Matomo.endDateString);
      }

      var dateRange = Range_RangePeriod.getLastNRange(Matomo_Matomo.period, 30, Matomo_Matomo.currentDateString).getDateRange();
      var piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);

      if (dateRange[0] < piwikMinDate) {
        dateRange[0] = piwikMinDate;
      }

      var startDateStr = format(dateRange[0]);
      var endDateStr = format(dateRange[1]);
      return "".concat(startDateStr, ",").concat(endDateStr);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue



Sparklinevue_type_script_lang_ts.render = Sparklinevue_type_template_id_25e2c9f0_render

/* harmony default export */ var Sparkline = (Sparklinevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Sparkline_adapter = (createAngularJsAdapter({
  component: Sparkline,
  scope: {
    seriesIndices: {
      angularJsBind: '<'
    },
    params: {
      angularJsBind: '<'
    }
  },
  directiveName: 'piwikSparkline',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=template&id=0048ddd7

var Progressbarvue_type_template_id_0048ddd7_hoisted_1 = {
  class: "progressbar"
};
var Progressbarvue_type_template_id_0048ddd7_hoisted_2 = {
  class: "progress"
};

var Progressbarvue_type_template_id_0048ddd7_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);

var Progressbarvue_type_template_id_0048ddd7_hoisted_4 = ["innerHTML"];
function Progressbarvue_type_template_id_0048ddd7_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Progressbarvue_type_template_id_0048ddd7_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Progressbarvue_type_template_id_0048ddd7_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "determinate",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])([{
      "width": "0"
    }, {
      width: "".concat(_ctx.actualProgress, "%")
    }])
  }, null, 4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Progressbarvue_type_template_id_0048ddd7_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "label",
    innerHTML: _ctx.$sanitize(_ctx.label)
  }, null, 8, Progressbarvue_type_template_id_0048ddd7_hoisted_4)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !!_ctx.label]])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=template&id=0048ddd7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=script&lang=ts

/* harmony default export */ var Progressbarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    progress: {
      type: Number,
      required: true
    },
    label: String
  },
  computed: {
    actualProgress: function actualProgress() {
      if (this.progress > 100) {
        return 100;
      }

      if (this.progress < 0) {
        return 0;
      }

      return this.progress;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue



Progressbarvue_type_script_lang_ts.render = Progressbarvue_type_template_id_0048ddd7_render

/* harmony default export */ var Progressbar = (Progressbarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Progressbar_adapter = (createAngularJsAdapter({
  component: Progressbar,
  scope: {
    progress: {
      angularJsBind: '='
    },
    label: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikProgressbar'
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentIntro/ContentIntro.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* harmony default export */ var ContentIntro = ({
  mounted: function mounted(el) {
    el.classList.add('piwik-content-intro');
  },
  updated: function updated(el) {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      el.classList.add('piwik-content-intro');
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentIntro/ContentIntro.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikContentIntro() {
  return {
    restrict: 'A',
    link: function piwikContentIntroLink(scope, element) {
      ContentIntro.mounted(element[0]);
    }
  };
}
window.angular.module('piwikApp').directive('piwikContentIntro', piwikContentIntro);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentTable/ContentTable.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* harmony default export */ var ContentTable = ({
  mounted: function mounted(el) {
    el.classList.add('card', 'card-table', 'entityTable');
  },
  updated: function updated(el) {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      el.classList.add('card', 'card-table', 'entityTable');
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentTable/ContentTable.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikContentTable() {
  return {
    restrict: 'A',
    link: function piwikContentTableLink(scope, element) {
      ContentTable.mounted(element[0]);
    }
  };
}
window.angular.module('piwikApp').directive('piwikContentTable', piwikContentTable);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=template&id=00d5220c

var AjaxFormvue_type_template_id_00d5220c_hoisted_1 = {
  ref: "root"
};
function AjaxFormvue_type_template_id_00d5220c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AjaxFormvue_type_template_id_00d5220c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default", {
    formData: _ctx.formData,
    submitApiMethod: _ctx.submitApiMethod,
    sendJsonPayload: _ctx.sendJsonPayload,
    noErrorNotification: _ctx.noErrorNotification,
    noSuccessNotification: _ctx.noSuccessNotification,
    submitForm: _ctx.submitForm,
    isSubmitting: _ctx.isSubmitting,
    successfulPostResponse: _ctx.successfulPostResponse,
    errorPostResponse: _ctx.errorPostResponse
  })], 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=template&id=00d5220c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=script&lang=ts




var AjaxFormvue_type_script_lang_ts_window = window,
    AjaxFormvue_type_script_lang_ts_$ = AjaxFormvue_type_script_lang_ts_window.$;
/**
 * Example usage:
 *
 * <AjaxForm :form-data="myData" ...>
 *   <template v-slot:default="ajaxForm">
 *     <Field v-model="myData.something" .../>
 *     <SaveButton @confirm="ajaxForm.submitForm()" :saving="ajaxForm.isSubmitting"/>
 *   </template>
 * </AjaxForm>
 *
 * Data does not flow upwards in any way. :form-data is used for submitForm(), and the
 * containing component binds to properties of the object in controls to fill the object.
 */

/* harmony default export */ var AjaxFormvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    formData: {
      type: Object,
      required: true
    },
    submitApiMethod: {
      type: String,
      required: true
    },
    sendJsonPayload: Boolean,
    noErrorNotification: Boolean,
    noSuccessNotification: Boolean
  },
  data: function data() {
    return {
      isSubmitting: false,
      successfulPostResponse: null,
      errorPostResponse: null
    };
  },
  emits: ['update:modelValue'],
  mounted: function mounted() {
    var _this = this;

    // on submit call controller submit method
    AjaxFormvue_type_script_lang_ts_$(this.$refs.root).on('click', 'input[type=submit]', function () {
      _this.submitForm();
    });
  },
  methods: {
    submitForm: function submitForm() {
      var _this2 = this;

      this.successfulPostResponse = null;
      this.errorPostResponse = null;
      var postParams = this.formData;

      if (this.sendJsonPayload) {
        postParams = {
          data: JSON.stringify(this.formData)
        };
      }

      this.isSubmitting = true;
      AjaxHelper_AjaxHelper.post({
        module: 'API',
        method: this.submitApiMethod
      }, postParams, {
        createErrorNotification: !this.noErrorNotification
      }).then(function (response) {
        _this2.successfulPostResponse = response;

        if (!_this2.noSuccessNotification) {
          var notificationInstanceId = Notifications_store.show({
            message: translate('General_YourChangesHaveBeenSaved'),
            context: 'success',
            type: 'toast',
            id: 'ajaxHelper'
          });
          Notifications_store.scrollToNotification(notificationInstanceId);
        }
      }).catch(function (error) {
        _this2.errorPostResponse = error.message;
      }).finally(function () {
        _this2.isSubmitting = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue



AjaxFormvue_type_script_lang_ts.render = AjaxFormvue_type_template_id_00d5220c_render

/* harmony default export */ var AjaxForm = (AjaxFormvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var AjaxForm_adapter_window = window,
    AjaxForm_adapter_$ = AjaxForm_adapter_window.$;
/**
 * AngularJS directive that manages an AJAX form.
 *
 * This directive will detect inputs & selects defined within an element and when a
 * submit button is clicked, will post data from the inputs & selects to a Piwik API method.
 *
 * When the POST request is finished the result will, by default, be displayed as a
 * notification.
 *
 * This directive accepts the following attributes:
 *
 * - **submit-api-method**: **required** The Piwik API method that handles the POST request.
 * - **send-json-payload**: Whether to send the data as a form encoded URL or to send it as JSON.
 *                          If sending as JSON, the payload will still be a form encoded value,
 *                          but will contain a JSON object like `{data: {...form data...}}`.
 *
 *                          This is for forms with lots of fields where having the same number
 *                          of parameters in an API method would not be desired.
 * - **no-error-notification**: If true, does not display an error notification if the AJAX post
 *                              fails.
 * - **no-success-notification**: If true, does not display an error notification if the AJAX
 *                                results in success.
 *
 * **Custom Success/Error Handling**
 *
 * On success/failure, the response will be stored in controller scope. Child elements of a
 * piwik-ajax-form element can access this data, and thus, can customize what happens when
 * a form submit succeeds/fails.
 *
 * See the ajax-form.controller.js file for more info.
 *
 * Usage:
 *
 *     <div piwik-ajax-form
 *          submit-api-method="'MyPlugin.myFormSaveMethod'"
 *          send-json-payload="true"
 *          ng-model="myFormData">
 *
 *          <h2>My Form</h2>
 *          <input name="myOption" value="myDefaultValue" type="text" />
 *          <input name="myOtherOption" type="checkbox" checked="checked" />
 *          <input type="submit" value="Submit" ng-disabled="ajaxForm.isSubmitting" />
 *
 *          <div piwik-notification context='error' ng-show="errorPostResponse">ERROR!</div>
 *     </div>
 * @deprecated
 */

function piwikAjaxForm($parse) {
  return {
    restrict: 'A',
    scope: {
      submitApiMethod: '=',
      sendJsonPayload: '=',
      noErrorNotification: '=',
      noSuccessNotification: '=',
      useCustomDataBinding: '='
    },
    require: '?ngModel',
    transclude: true,
    compile: function piwikAjaxFormCompile(compileElement, compileAttrs) {
      compileAttrs.noErrorNotification = !!compileAttrs.noErrorNotification; // eslint-disable-next-line @typescript-eslint/no-explicit-any

      return function piwikAjaxFormLink(scope, element, attrs, ngModel, transclude) {
        if (!scope.submitApiMethod) {
          throw new Error('submitApiMethod is required');
        }

        scope.ajaxForm = {};
        scope.ajaxForm.submitApiMethod = scope.submitApiMethod;
        scope.ajaxForm.sendJsonPayload = scope.sendJsonPayload;
        scope.ajaxForm.noErrorNotification = scope.noErrorNotification;
        scope.ajaxForm.noSuccessNotification = scope.noSuccessNotification;
        scope.ajaxForm.data = {}; // if a model is supplied, initiate form data w/ model value

        if (ngModel) {
          // probably redundant, but I cannot find another way to get the ng model value here
          var ngModelGetter = $parse(attrs.ngModel);
          scope.ajaxForm.data = ngModelGetter(scope.$parent);
        }

        var specialBindDirective = {
          mounted: function mounted(el, binding) {
            scope.ajaxForm.submitForm = binding.value.submitForm;
          }
        };
        var rootTemplate = "\n        <AjaxForm\n          :form-data=\"data\"\n          :submit-api-method=\"submitApiMethod\"\n          :send-json-payload=\"sendJsonPayload\"\n          :no-error-notification=\"noErrorNotification\"\n          :no-success-notification=\"noSuccessNotification\"\n        >\n          <template v-slot:default=\"ajaxFormVue\">\n            <div\n              ref=\"transcludeTarget\"\n              v-special-bind-directive=\"{ submitForm: ajaxFormVue.submitForm }\"\n            />\n          </template>\n        </AjaxForm>";
        var app = createVueApp({
          template: rootTemplate,
          data: function data() {
            return scope.ajaxForm;
          },
          setup: function setup() {
            var transcludeTarget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
            return {
              transcludeTarget: transcludeTarget
            };
          }
        });
        app.component('AjaxForm', AjaxForm);
        app.directive('SpecialBindDirective', specialBindDirective);
        var vm = app.mount(element[0]);
        element.on('$destroy', function () {
          app.unmount();
        });

        function setFormValueFromInput(inputElement, skipScopeApply) {
          var name = AjaxForm_adapter_$(inputElement).attr('name');
          var val;

          if (AjaxForm_adapter_$(inputElement).attr('type') === 'checkbox') {
            val = AjaxForm_adapter_$(inputElement).is(':checked');
          } else {
            val = AjaxForm_adapter_$(inputElement).val();
          }

          scope.ajaxForm.data[name] = val;

          if (!skipScopeApply) {
            setTimeout(function () {
              scope.$apply();
            }, 0);
          }
        } // on change of any input, change appropriate value in model, but only if requested


        if (!scope.useCustomDataBinding) {
          element.on('change', 'input,select', function (event) {
            setFormValueFromInput(event.target);
          });
        } // make sure child elements can access this directive's scope
        // eslint-disable-next-line @typescript-eslint/no-explicit-any


        transclude(scope, function (clone, transcludeScope) {
          if (!transcludeScope.useCustomDataBinding) {
            var $inputs = clone.find('input,select').not('[type=submit]'); // initialize form data to input values (include <select>s

            $inputs.each(function inputEach() {
              setFormValueFromInput(this, true);
            });
          } // eslint-disable-next-line @typescript-eslint/no-explicit-any


          AjaxForm_adapter_$(vm.transcludeTarget).append(clone);
        });
      };
    }
  };
}

piwikAjaxForm.$inject = ['$parse'];
window.angular.module('piwikApp').directive('piwikAjaxForm', piwikAjaxForm);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=template&id=19288664


var ShowHelpLinkvue_type_template_id_19288664_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

var ShowHelpLinkvue_type_template_id_19288664_hoisted_2 = [ShowHelpLinkvue_type_template_id_19288664_hoisted_1];
function ShowHelpLinkvue_type_template_id_19288664_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    class: "item-help-icon",
    tabindex: "5",
    href: "javascript:",
    onClick: _cache[0] || (_cache[0] = function () {
      return _ctx.showHelp && _ctx.showHelp.apply(_ctx, arguments);
    })
  }, ShowHelpLinkvue_type_template_id_19288664_hoisted_2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=template&id=19288664

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=script&lang=ts


var ShowHelpLinkvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID = 'reportingMenu-help';
/* harmony default export */ var ShowHelpLinkvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    message: {
      type: String,
      required: true
    },
    name: {
      type: String,
      required: true
    }
  },
  data: function data() {
    return {
      currentName: ''
    };
  },
  methods: {
    showHelp: function showHelp() {
      if (this.currentName !== '') {
        Notifications_store.remove(ShowHelpLinkvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID);
        this.currentName = '';
        return;
      }

      Notifications_store.show({
        context: 'info',
        id: ShowHelpLinkvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID,
        type: 'help',
        noclear: true,
        class: 'help-notification',
        message: this.message,
        placeat: '#notificationContainer',
        prepend: true
      });

      if (this.name !== '') {
        this.currentName = this.name;
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue



ShowHelpLinkvue_type_script_lang_ts.render = ShowHelpLinkvue_type_template_id_19288664_render

/* harmony default export */ var ShowHelpLink = (ShowHelpLinkvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ShowHelpLink_adapter = (createAngularJsAdapter({
  component: ShowHelpLink,
  scope: {
    message: {
      angularJsBind: '@'
    },
    name: {
      angularJsBind: '@'
    }
  },
  directiveName: 'piwikShowHelpLink'
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/getFormattedEvolution.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function calculateEvolution(currentValue, pastValue) {
  var pastValueParsed = parseInt(pastValue, 10);
  var currentValueParsed = parseInt(currentValue, 10) - pastValueParsed;
  var evolution;

  if (currentValueParsed === 0 || Number.isNaN(currentValueParsed)) {
    evolution = 0;
  } else if (pastValueParsed === 0 || Number.isNaN(pastValueParsed)) {
    evolution = 100;
  } else {
    evolution = currentValueParsed / pastValueParsed * 100;
  }

  return evolution;
}

function formatEvolution(evolution) {
  return "".concat(evolution > 0 ? Matomo_Matomo.numbers.symbolPlus : '').concat(Math.round(evolution), "}%");
}

function getFormattedEvolution(currentValue, pastValue) {
  var evolution = calculateEvolution(currentValue, pastValue);
  return formatEvolution(evolution);
}
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