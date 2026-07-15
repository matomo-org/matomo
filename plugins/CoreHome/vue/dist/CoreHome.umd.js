<<<<<<< HEAD
(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue")) : typeof define === "function" && define.amd ? define(["exports", "vue"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.CoreHome = {}, global.Vue));
})(this, (function(exports2, vue) {
  "use strict";var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __pow = Math.pow;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
=======
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
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
__webpack_require__.d(__webpack_exports__, "importPluginUmd", function() { return /* reexport */ importPluginUmd; });
__webpack_require__.d(__webpack_exports__, "useExternalPluginComponent", function() { return /* reexport */ useExternalPluginComponent; });
__webpack_require__.d(__webpack_exports__, "DirectiveUtilities", function() { return /* reexport */ directiveUtilities; });
__webpack_require__.d(__webpack_exports__, "debounce", function() { return /* reexport */ debounce; });
__webpack_require__.d(__webpack_exports__, "clone", function() { return /* reexport */ clone; });
__webpack_require__.d(__webpack_exports__, "VueEntryContainer", function() { return /* reexport */ VueEntryContainer; });
__webpack_require__.d(__webpack_exports__, "ActivityIndicator", function() { return /* reexport */ ActivityIndicator; });
__webpack_require__.d(__webpack_exports__, "MatomoLoader", function() { return /* reexport */ MatomoLoader; });
__webpack_require__.d(__webpack_exports__, "translate", function() { return /* reexport */ translate; });
__webpack_require__.d(__webpack_exports__, "translateOrDefault", function() { return /* reexport */ translateOrDefault; });
__webpack_require__.d(__webpack_exports__, "externalRawLink", function() { return /* reexport */ externalRawLink; });
__webpack_require__.d(__webpack_exports__, "externalLink", function() { return /* reexport */ externalLink; });
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
__webpack_require__.d(__webpack_exports__, "getWeekNumber", function() { return /* reexport */ getWeekNumber; });
__webpack_require__.d(__webpack_exports__, "datesAreInTheSamePeriod", function() { return /* reexport */ datesAreInTheSamePeriod; });
__webpack_require__.d(__webpack_exports__, "NumberFormatter", function() { return /* reexport */ src_NumberFormatter_NumberFormatter; });
__webpack_require__.d(__webpack_exports__, "formatNumber", function() { return /* reexport */ formatNumber; });
__webpack_require__.d(__webpack_exports__, "formatPercent", function() { return /* reexport */ formatPercent; });
__webpack_require__.d(__webpack_exports__, "formatCurrency", function() { return /* reexport */ formatCurrency; });
__webpack_require__.d(__webpack_exports__, "formatEvolution", function() { return /* reexport */ formatEvolution; });
__webpack_require__.d(__webpack_exports__, "calculateAndFormatEvolution", function() { return /* reexport */ calculateAndFormatEvolution; });
__webpack_require__.d(__webpack_exports__, "DropdownMenu", function() { return /* reexport */ DropdownMenu; });
__webpack_require__.d(__webpack_exports__, "FocusAnywhereButHere", function() { return /* reexport */ FocusAnywhereButHere; });
__webpack_require__.d(__webpack_exports__, "FocusIf", function() { return /* reexport */ FocusIf; });
__webpack_require__.d(__webpack_exports__, "Tooltips", function() { return /* reexport */ Tooltips; });
__webpack_require__.d(__webpack_exports__, "MatomoDialog", function() { return /* reexport */ MatomoDialog; });
__webpack_require__.d(__webpack_exports__, "MatomoModal", function() { return /* reexport */ MatomoModal; });
__webpack_require__.d(__webpack_exports__, "ExpandOnClick", function() { return /* reexport */ ExpandOnClick; });
__webpack_require__.d(__webpack_exports__, "ExpandOnHover", function() { return /* reexport */ ExpandOnHover; });
__webpack_require__.d(__webpack_exports__, "ShowSensitiveData", function() { return /* reexport */ ShowSensitiveData; });
__webpack_require__.d(__webpack_exports__, "DropdownButton", function() { return /* reexport */ DropdownButton; });
__webpack_require__.d(__webpack_exports__, "DraggableList", function() { return /* reexport */ DraggableList; });
__webpack_require__.d(__webpack_exports__, "SelectOnFocus", function() { return /* reexport */ SelectOnFocus; });
__webpack_require__.d(__webpack_exports__, "CopyToClipboard", function() { return /* reexport */ CopyToClipboard; });
__webpack_require__.d(__webpack_exports__, "SideNav", function() { return /* reexport */ SideNav; });
__webpack_require__.d(__webpack_exports__, "EnrichedHeadline", function() { return /* reexport */ EnrichedHeadline; });
__webpack_require__.d(__webpack_exports__, "ContentBlock", function() { return /* reexport */ ContentBlock; });
__webpack_require__.d(__webpack_exports__, "Comparisons", function() { return /* reexport */ Comparisons; });
__webpack_require__.d(__webpack_exports__, "ComparisonsStore", function() { return /* reexport */ Comparisons_store_ComparisonsStore; });
__webpack_require__.d(__webpack_exports__, "ComparisonsStoreInstance", function() { return /* reexport */ Comparisons_store_instance; });
__webpack_require__.d(__webpack_exports__, "MenuItemsDropdown", function() { return /* reexport */ MenuItemsDropdown; });
__webpack_require__.d(__webpack_exports__, "DatePicker", function() { return /* reexport */ DatePicker; });
__webpack_require__.d(__webpack_exports__, "DateRangePicker", function() { return /* reexport */ DateRangePicker; });
__webpack_require__.d(__webpack_exports__, "PeriodDatePicker", function() { return /* reexport */ PeriodDatePicker; });
__webpack_require__.d(__webpack_exports__, "Notification", function() { return /* reexport */ Notification; });
__webpack_require__.d(__webpack_exports__, "NotificationGroup", function() { return /* reexport */ Notification_NotificationGroup; });
__webpack_require__.d(__webpack_exports__, "NotificationsStore", function() { return /* reexport */ Notifications_store; });
__webpack_require__.d(__webpack_exports__, "ShowHelpLink", function() { return /* reexport */ ShowHelpLink; });
__webpack_require__.d(__webpack_exports__, "SitesStore", function() { return /* reexport */ SiteSelector_SitesStore; });
__webpack_require__.d(__webpack_exports__, "SiteSelector", function() { return /* reexport */ SiteSelector; });
__webpack_require__.d(__webpack_exports__, "QuickAccess", function() { return /* reexport */ QuickAccess; });
__webpack_require__.d(__webpack_exports__, "SearchInput", function() { return /* reexport */ SearchInput; });
__webpack_require__.d(__webpack_exports__, "FieldArray", function() { return /* reexport */ FieldArray; });
__webpack_require__.d(__webpack_exports__, "MultiPairField", function() { return /* reexport */ MultiPairField; });
__webpack_require__.d(__webpack_exports__, "PeriodSelector", function() { return /* reexport */ PeriodSelector; });
__webpack_require__.d(__webpack_exports__, "ReportingMenu", function() { return /* reexport */ ReportingMenu; });
__webpack_require__.d(__webpack_exports__, "ReportingMenuStore", function() { return /* reexport */ ReportingMenu_store; });
__webpack_require__.d(__webpack_exports__, "ReportingPagesStore", function() { return /* reexport */ ReportingPages_store; });
__webpack_require__.d(__webpack_exports__, "ReportMetadataStore", function() { return /* reexport */ ReportMetadata_store; });
__webpack_require__.d(__webpack_exports__, "WidgetsStore", function() { return /* reexport */ Widgets_store; });
__webpack_require__.d(__webpack_exports__, "ReportHeader", function() { return /* reexport */ ReportHeader; });
__webpack_require__.d(__webpack_exports__, "WidgetControls", function() { return /* reexport */ WidgetControls; });
__webpack_require__.d(__webpack_exports__, "WidgetLoader", function() { return /* reexport */ WidgetLoader; });
__webpack_require__.d(__webpack_exports__, "ClientWidgetRenderer", function() { return /* reexport */ ClientWidgetRenderer; });
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
__webpack_require__.d(__webpack_exports__, "Passthrough", function() { return /* reexport */ Passthrough; });
__webpack_require__.d(__webpack_exports__, "DataTableActions", function() { return /* reexport */ DataTableActions; });
__webpack_require__.d(__webpack_exports__, "VersionInfoHeaderMessage", function() { return /* reexport */ VersionInfoHeaderMessage; });
__webpack_require__.d(__webpack_exports__, "MobileLeftMenu", function() { return /* reexport */ MobileLeftMenu; });
__webpack_require__.d(__webpack_exports__, "scrollToAnchorInUrl", function() { return /* reexport */ scrollToAnchorInUrl; });
__webpack_require__.d(__webpack_exports__, "SearchFiltersPersistenceStore", function() { return /* reexport */ SearchFiltersPersistence_store; });
__webpack_require__.d(__webpack_exports__, "AutoClearPassword", function() { return /* reexport */ AutoClearPassword; });
__webpack_require__.d(__webpack_exports__, "PasswordStrength", function() { return /* reexport */ PasswordStrength; });
__webpack_require__.d(__webpack_exports__, "EntityDuplicatorModal", function() { return /* reexport */ EntityDuplicatorModal; });
__webpack_require__.d(__webpack_exports__, "EntityDuplicatorAction", function() { return /* reexport */ EntityDuplicatorAction; });
__webpack_require__.d(__webpack_exports__, "EntityDuplicatorStore", function() { return /* reexport */ EntityDuplicatorStore_EntityDuplicatorStore; });
__webpack_require__.d(__webpack_exports__, "BaseDuplicatorAdapter", function() { return /* reexport */ EntityDuplicatorAdapter_BaseDuplicatorAdapter; });

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

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/translate.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function translate(translationStringId, ...values) {
  if (!translationStringId) {
    return '';
  }
  let pkArgs = values;
  // handle variadic args AND single array of values (to match _pk_translate signature)
  if (values.length === 1 && values[0] && Array.isArray(values[0])) {
    [pkArgs] = values;
  }
  return window._pk_translate(translationStringId, pkArgs); // eslint-disable-line
}
function translateOrDefault(translationStringIdOrText, ...values) {
  if (!translationStringIdOrText || !window.piwik_translations[translationStringIdOrText]) {
    return translationStringIdOrText;
  }
  return translate(translationStringIdOrText, ...values);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
class Periods {
  constructor() {
    _defineProperty(this, "periods", {});
    _defineProperty(this, "periodOrder", []);
  }
  addCustomPeriod(name, periodClass) {
    if (this.periods[name]) {
      throw new Error(`The "${name}" period already exists! It cannot be overridden.`);
>>>>>>> 5eb7b67532 (Remove 3-dots dropdown menu)
    }
  return a;
};
var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
var __async = (__this, __arguments, generator) => {
  return new Promise((resolve2, reject) => {
    var fulfilled = (value) => {
      try {
        step(generator.next(value));
      } catch (e) {
        reject(e);
      }
    };
    var rejected = (value) => {
      try {
        step(generator.throw(value));
      } catch (e) {
        reject(e);
      }
    };
    var step = (x) => x.done ? resolve2(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
    step((generator = generator.apply(__this, __arguments)).next());
  });
};

  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  window.hasBlockedContent = false;
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function translate(translationStringId, ...values) {
    if (!translationStringId) {
      return "";
    }
    let pkArgs = values;
    if (values.length === 1 && values[0] && Array.isArray(values[0])) {
      [pkArgs] = values;
    }
    return window._pk_translate(translationStringId, pkArgs);
  }
  function translateOrDefault(translationStringIdOrText, ...values) {
    if (!translationStringIdOrText || !window.piwik_translations[translationStringIdOrText]) {
      return translationStringIdOrText;
    }
    return translate(translationStringIdOrText, ...values);
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class Periods {
    constructor() {
      __publicField(this, "periods", {});
      __publicField(this, "periodOrder", []);
    }
    addCustomPeriod(name, periodClass) {
      if (this.periods[name]) {
        throw new Error(`The "${name}" period already exists! It cannot be overridden.`);
      }
      this.periods[name] = periodClass;
      this.periodOrder.push(name);
    }
    getAllLabels() {
      return Array().concat(this.periodOrder);
    }
    get(strPeriod) {
      const periodClass = this.periods[strPeriod];
      if (!periodClass) {
        throw new Error(`Invalid period label: ${strPeriod}`);
      }
      return periodClass;
    }
    parse(strPeriod, strDate) {
      return this.get(strPeriod).parse(strDate);
    }
    isRecognizedPeriod(strPeriod) {
      return !!this.periods[strPeriod];
    }
  }
  const Periods$1 = new Periods();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function format(date) {
    return $.datepicker.formatDate("yy-mm-dd", date);
  }
  function getToday() {
    const date = new Date(Date.now());
    date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1e3);
    date.setHours(date.getHours() + (window.piwik.timezoneOffset || 0) / 3600);
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
    const strDate = decodeURIComponent(date).trim();
    if (strDate === "") {
      throw new Error("Invalid date, empty string.");
    }
    if (strDate === "today" || strDate === "now") {
      return getToday();
    }
    if (strDate === "yesterday" || strDate === "yesterdaySameTime") {
      const yesterday = getToday();
      yesterday.setDate(yesterday.getDate() - 1);
      return yesterday;
    }
    if (strDate.match(/^last[ -]?week$/i)) {
      const lastWeek = getToday();
      lastWeek.setDate(lastWeek.getDate() - 7);
      return lastWeek;
    }
    if (strDate.match(/^last[ -]?month$/i)) {
      const lastMonth = getToday();
      lastMonth.setDate(1);
      lastMonth.setMonth(lastMonth.getMonth() - 1);
      return lastMonth;
    }
    if (strDate.match(/^last[ -]?year$/i)) {
      const lastYear = getToday();
      lastYear.setFullYear(lastYear.getFullYear() - 1);
      return lastYear;
    }
    return $.datepicker.parseDate("yy-mm-dd", strDate);
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
  function getWeekNumber(date) {
    const dt = new Date(date.valueOf());
    const dayNr = (date.getDay() + 6) % 7;
    dt.setDate(dt.getDate() - dayNr + 3);
    const firstThursdayUTC = dt.valueOf();
    dt.setMonth(0, 1);
    if (dt.getDay() !== 4) {
      const daysToNextThursday = (4 - dt.getDay() + 7) % 7;
      dt.setMonth(0, 1 + daysToNextThursday);
    }
    return 1 + Math.ceil((firstThursdayUTC - dt.valueOf()) / (7 * 24 * 3600 * 1e3));
  }
  function datesAreInTheSamePeriod(date1, date2, period) {
    const year1 = date1.getFullYear();
    const month1 = date1.getMonth();
    const day1 = date1.getDate();
    const week1 = getWeekNumber(date1);
    const year2 = date2.getFullYear();
    const month2 = date2.getMonth();
    const day2 = date2.getDate();
    const week2 = getWeekNumber(date2);
    switch (period) {
      case "day":
        return year1 === year2 && month1 === month2 && day1 === day2;
      case "week":
        return year1 === year2 && week1 === week2;
      case "month":
        return year1 === year2 && month1 === month2;
      case "year":
        return year1 === year2;
      default:
        return false;
    }
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class DayPeriod {
    constructor(dateInPeriod) {
      this.dateInPeriod = dateInPeriod;
    }
    static parse(strDate) {
      return new DayPeriod(parseDate(strDate));
    }
    static getDisplayText() {
      return translate("Intl_PeriodDay");
    }
    getPrettyString() {
      return format(this.dateInPeriod);
    }
    getDateRange() {
      return [new Date(this.dateInPeriod.getTime()), new Date(this.dateInPeriod.getTime())];
    }
    containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }
  Periods$1.addCustomPeriod("day", DayPeriod);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class WeekPeriod {
    constructor(dateInPeriod) {
      this.dateInPeriod = dateInPeriod;
    }
    static parse(strDate) {
      return new WeekPeriod(parseDate(strDate));
    }
    static getDisplayText() {
      return translate("Intl_PeriodWeek");
    }
    getPrettyString() {
      const weekDates = this.getDateRange();
      const startWeek = format(weekDates[0]);
      const endWeek = format(weekDates[1]);
      return translate("General_DateRangeFromTo", [startWeek, endWeek]);
    }
    getDateRange() {
      const daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;
      const startWeek = new Date(this.dateInPeriod.getTime());
      startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);
      const endWeek = new Date(startWeek.getTime());
      endWeek.setDate(startWeek.getDate() + 6);
      return [startWeek, endWeek];
    }
    containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }
  Periods$1.addCustomPeriod("week", WeekPeriod);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class MonthPeriod {
    constructor(dateInPeriod) {
      this.dateInPeriod = dateInPeriod;
    }
    static parse(strDate) {
      return new MonthPeriod(parseDate(strDate));
    }
    static getDisplayText() {
      return translate("Intl_PeriodMonth");
    }
    getPrettyString() {
      const month = translate(`Intl_Month_Long_StandAlone_${this.dateInPeriod.getMonth() + 1}`);
      return `${month} ${this.dateInPeriod.getFullYear()}`;
    }
    getDateRange() {
      const startMonth = new Date(this.dateInPeriod.getTime());
      startMonth.setDate(1);
      const endMonth = new Date(this.dateInPeriod.getTime());
      endMonth.setDate(1);
      endMonth.setMonth(endMonth.getMonth() + 1);
      endMonth.setDate(0);
      return [startMonth, endMonth];
    }
    containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }
  Periods$1.addCustomPeriod("month", MonthPeriod);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class YearPeriod {
    constructor(dateInPeriod) {
      this.dateInPeriod = dateInPeriod;
    }
    static parse(strDate) {
      return new YearPeriod(parseDate(strDate));
    }
    static getDisplayText() {
      return translate("Intl_PeriodYear");
    }
    getPrettyString() {
      return this.dateInPeriod.getFullYear().toString();
    }
    getDateRange() {
      const startYear = new Date(this.dateInPeriod.getTime());
      startYear.setMonth(0);
      startYear.setDate(1);
      const endYear = new Date(this.dateInPeriod.getTime());
      endYear.setMonth(12);
      endYear.setDate(0);
      return [startYear, endYear];
    }
    containsToday() {
      return todayIsInRange(this.getDateRange());
    }
  }
  Periods$1.addCustomPeriod("year", YearPeriod);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class RangePeriod {
    constructor(startDate, endDate, childPeriodType) {
      this.startDate = startDate;
      this.endDate = endDate;
      this.childPeriodType = childPeriodType;
    }
    /**
     * Returns a range representing the last N childPeriodType periods, including the current one.
     */
    static getLastNRange(childPeriodType, strAmount, strEndDate) {
      const nAmount = Math.max(parseInt(strAmount.toString(), 10) - 1, 0);
      if (Number.isNaN(nAmount)) {
        throw new Error("Invalid range strAmount");
      }
      let endDate = strEndDate ? parseDate(strEndDate) : getToday();
      let startDate = new Date(endDate.getTime());
      if (childPeriodType === "day") {
        startDate.setDate(startDate.getDate() - nAmount);
      } else if (childPeriodType === "week") {
        startDate.setDate(startDate.getDate() - nAmount * 7);
      } else if (childPeriodType === "month") {
        startDate.setDate(1);
        startDate.setMonth(startDate.getMonth() - nAmount);
      } else if (childPeriodType === "year") {
        startDate.setFullYear(startDate.getFullYear() - nAmount);
      } else {
        throw new Error(`Unknown period type '${childPeriodType}'.`);
      }
      if (childPeriodType !== "day") {
        const startPeriod = Periods$1.periods[childPeriodType].parse(startDate);
        const endPeriod = Periods$1.periods[childPeriodType].parse(endDate);
        [startDate] = startPeriod.getDateRange();
        [, endDate] = endPeriod.getDateRange();
      }
      const firstWebsiteDate = new Date(1991, 7, 6);
      if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
        switch (childPeriodType) {
          case "year":
            startDate = new Date(1992, 0, 1);
            break;
          case "month":
            startDate = new Date(1991, 8, 1);
            break;
          case "week":
            startDate = new Date(1991, 8, 12);
            break;
          case "day":
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
    static getLastNRangeChild(childPeriodType, rangeEndDate, countBack) {
      const ed = rangeEndDate ? parseDate(rangeEndDate) : getToday();
      let startDate = new Date(ed.getTime());
      let endDate = new Date(ed.getTime());
      if (childPeriodType === "day") {
        startDate.setDate(startDate.getDate() - countBack);
        endDate.setDate(endDate.getDate() - countBack);
      } else if (childPeriodType === "week") {
        startDate.setDate(startDate.getDate() - countBack * 7);
        endDate.setDate(endDate.getDate() - countBack * 7);
      } else if (childPeriodType === "month") {
        startDate.setDate(1);
        startDate.setMonth(startDate.getMonth() - countBack);
        endDate.setDate(1);
        endDate.setMonth(endDate.getMonth() - countBack);
      } else if (childPeriodType === "year") {
        startDate.setFullYear(startDate.getFullYear() - countBack);
        endDate.setFullYear(endDate.getFullYear() - countBack);
      } else {
        throw new Error(`Unknown period type '${childPeriodType}'.`);
      }
      if (childPeriodType !== "day") {
        const startPeriod = Periods$1.periods[childPeriodType].parse(startDate);
        const endPeriod = Periods$1.periods[childPeriodType].parse(endDate);
        [startDate] = startPeriod.getDateRange();
        [, endDate] = endPeriod.getDateRange();
      }
      const firstWebsiteDate = new Date(1991, 7, 6);
      if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {
        switch (childPeriodType) {
          case "year":
            startDate = new Date(1992, 0, 1);
            break;
          case "month":
            startDate = new Date(1991, 8, 1);
            break;
          case "week":
            startDate = new Date(1991, 8, 12);
            break;
          case "day":
          default:
            startDate = firstWebsiteDate;
            break;
        }
      }
      return new RangePeriod(startDate, endDate, childPeriodType);
    }
    static parse(strDate, childPeriodType = "day") {
      if (/^previous/.test(strDate)) {
        const endDate = RangePeriod.getLastNRange(childPeriodType, "2").startDate;
        return RangePeriod.getLastNRange(childPeriodType, strDate.substring(8), endDate);
      }
      if (/^last/.test(strDate)) {
        return RangePeriod.getLastNRange(childPeriodType, strDate.substring(4));
      }
      const parts = decodeURIComponent(strDate).split(",");
      return new RangePeriod(parseDate(parts[0]), parseDate(parts[1]), childPeriodType);
    }
    static getDisplayText() {
      return translate("General_DateRangeInPeriodList");
    }
    getPrettyString() {
      const start = format(this.startDate);
      const end = format(this.endDate);
      return translate("General_DateRangeFromTo", [start, end]);
    }
    getDateRange() {
      return [this.startDate, this.endDate];
    }
    containsToday() {
      return todayIsInRange(this.getDateRange());
    }
    getDayCount() {
      return Math.ceil((this.endDate.getTime() - this.startDate.getTime()) / (1e3 * 3600 * 24)) + 1;
    }
  }
  Periods$1.addCustomPeriod("range", RangePeriod);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { piwik: piwik$1, broadcast: broadcast$2, piwikHelper: piwikHelper$1 } = window;
  function normalizeLoginModule(value) {
    if (typeof value !== "string") {
      return void 0;
    }
    const trimmed = value.trim();
    if (!trimmed) {
      return void 0;
    }
    if (!/^[A-Za-z0-9_]+$/.test(trimmed)) {
      return void 0;
    }
    return trimmed;
  }
  piwik$1.helper = piwikHelper$1;
  piwik$1.broadcast = broadcast$2;
  function getReportingMenuStore() {
    const { CoreHome } = window;
    return CoreHome == null ? void 0 : CoreHome.ReportingMenuStore;
  }
  function getComparisonsStore() {
    const { CoreHome } = window;
    return CoreHome == null ? void 0 : CoreHome.ComparisonStoreInstance;
  }
  function getActiveSegmentLabel(segment) {
    var _a2;
    if (typeof segment !== "string") {
      return void 0;
    }
    const trimmedSegment = segment.trim();
    const comparisonsStore = getComparisonsStore();
    if (comparisonsStore) {
      const comparisons = comparisonsStore.getSegmentComparisons();
      if (!trimmedSegment && comparisons.length) {
        return comparisons[0].title;
      }
      const found = comparisons.find(
        (comparison) => comparison.params.segment === segment
      );
      if (found) {
        return found.title;
      }
    }
    if (!trimmedSegment) {
      return translate("SegmentEditor_DefaultAllVisits");
    }
    const segmentationTitle = document.querySelector(".segmentEditorPanel .segmentationTitle");
    const fallbackName = (_a2 = segmentationTitle == null ? void 0 : segmentationTitle.textContent) == null ? void 0 : _a2.trim();
    if (fallbackName) {
      return fallbackName;
    }
    return translate("SegmentEditor_CustomSegment");
  }
  piwik$1.updateTitle = function updateTitle(date, period, category, subcategory, segment) {
    return __async(this, null, function* () {
      var _a2, _b, _c, _d;
      let categoryName = "";
      let subcategoryName = "";
      let dateString = "";
      if (period !== "" && date !== "") {
        dateString = Periods$1.parse(period, date).getPrettyString();
      }
      const titleSuffix = `${translate("CoreHome_WebAnalyticsReports")} - Matomo`;
      const store = getReportingMenuStore();
      if (store && category && subcategory) {
        let found = store.findSubcategory(category, subcategory);
        if (!found.category) {
          yield store.fetchMenuItems();
          found = store.findSubcategory(category, subcategory);
        }
        categoryName = (_b = (_a2 = found == null ? void 0 : found.category) == null ? void 0 : _a2.name) != null ? _b : "";
        subcategoryName = (_d = (_c = found == null ? void 0 : found.subcategory) == null ? void 0 : _c.name) != null ? _d : "";
        if (categoryName === subcategoryName) {
          subcategoryName = "";
        }
        categoryName = piwikHelper$1.htmlEntities(categoryName);
        subcategoryName = piwikHelper$1.htmlEntities(subcategoryName);
        const categorySubcategoryString = categoryName ? `${categoryName}  ${subcategoryName ? `> ${subcategoryName}` : ""}` : "";
        const segmentLabel = getActiveSegmentLabel(segment);
        const segmentString = segmentLabel ? piwikHelper$1.htmlEntities(segmentLabel) : "";
        document.title = [
          piwik$1.siteName,
          dateString,
          categorySubcategoryString,
          segmentString,
          titleSuffix
        ].filter(Boolean).join(" - ");
      }
    });
  };
  piwik$1.hasUserCapability = function hasUserCapability(capability) {
    return Array.isArray(piwik$1.userCapabilities) && piwik$1.userCapabilities.indexOf(capability) !== -1;
  };
  piwik$1.on = function addMatomoEventListener(eventName, listener) {
    function listenerWrapper(evt) {
      listener(...evt.detail);
    }
    listener.wrapper = listenerWrapper;
    window.addEventListener(eventName, listenerWrapper);
  };
  piwik$1.off = function removeMatomoEventListener(eventName, listener) {
    if (listener.wrapper) {
      window.removeEventListener(eventName, listener.wrapper);
    }
  };
  piwik$1.postEvent = function postMatomoEvent(eventName, ...args) {
    const event = new CustomEvent(eventName, { detail: args });
    window.dispatchEvent(event);
  };
  piwik$1.getLoginModule = function getLoginModule() {
    const fromPiwikConfig = normalizeLoginModule(piwik$1.loginModule);
    if (fromPiwikConfig) {
      return fromPiwikConfig;
    }
    const fromWindow = normalizeLoginModule(
      window.loginModule
    );
    if (fromWindow) {
      return fromWindow;
    }
    return "Login";
  };
  const Matomo = piwik$1;
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { piwik, broadcast: broadcast$1 } = window;
  function isValidPeriod(periodStr, dateStr) {
    try {
      Periods$1.parse(periodStr, dateStr);
      return true;
    } catch (e) {
      return false;
    }
  }
  class MatomoUrl {
    constructor() {
      __publicField(this, "url", vue.ref(null));
      __publicField(this, "urlQuery", vue.computed(
        () => this.url.value ? this.url.value.search.replace(/^\?/, "") : ""
      ));
      __publicField(this, "hashQuery", vue.computed(
        () => this.url.value ? this.url.value.hash.replace(/^[#/?]+/, "") : ""
      ));
      __publicField(this, "urlParsed", vue.computed(() => vue.readonly(
        this.parse(this.urlQuery.value)
      )));
      __publicField(this, "hashParsed", vue.computed(() => vue.readonly(
        this.parse(this.hashQuery.value)
      )));
      __publicField(this, "parsed", vue.computed(() => vue.readonly(__spreadValues(__spreadValues({}, this.urlParsed.value), this.hashParsed.value))));
      this.url.value = new URL(window.location.href);
      window.addEventListener("hashchange", (event) => {
        this.url.value = new URL(event.newURL);
        this.updatePeriodParamsFromUrl();
        this.updatePageTitle();
      });
      this.updatePeriodParamsFromUrl();
      this.updatePageTitle();
    }
    updateHashToUrl(urlWithoutLeadingHash) {
      const wholeHash = `#${urlWithoutLeadingHash}`;
      if (window.location.hash === wholeHash) {
        window.dispatchEvent(new HashChangeEvent("hashchange", {
          newURL: window.location.href,
          oldURL: window.location.href
        }));
      } else {
        window.location.hash = wholeHash;
      }
    }
    updateHash(params) {
      const modifiedParams = this.getFinalHashParams(params);
      const serializedParams = this.stringify(modifiedParams);
      this.updateHashToUrl(`?${serializedParams}`);
    }
    updateUrl(params, hashParams = {}) {
      const serializedParams = typeof params !== "string" ? this.stringify(params) : params;
      const modifiedHashParams = Object.keys(hashParams).length ? this.getFinalHashParams(hashParams, params) : {};
      const serializedHashParams = this.stringify(modifiedHashParams);
      let url = `?${serializedParams}`;
      if (serializedHashParams.length) {
        url = `${url}#?${serializedHashParams}`;
      }
      window.broadcast.propagateNewPage("", void 0, void 0, void 0, url);
    }
    getFinalHashParams(params, urlParams = {}) {
      const paramsObj = typeof params !== "string" ? params : this.parse(params);
      const urlParamsObj = typeof params !== "string" ? urlParams : this.parse(urlParams);
      return __spreadValues({
        // these params must always be present in the hash
        period: urlParamsObj.period || this.parsed.value.period,
        date: urlParamsObj.date || this.parsed.value.date,
        segment: urlParamsObj.segment || this.parsed.value.segment
      }, paramsObj);
    }
    // if we're in an embedded context, loads an entire new URL, otherwise updates the hash
    updateLocation(params) {
      if (Matomo.helper.isReportingPage()) {
        this.updateHash(params);
        return;
      }
      this.updateUrl(params);
    }
    getSearchParam(paramName) {
      const hash = window.location.href.split("#");
      const regex = new RegExp(`${paramName}(\\[]|=)`);
      if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {
        const valueFromHash = window.broadcast.getValueFromHash(paramName, window.location.href);
        if (valueFromHash || paramName !== "date" && paramName !== "period" && paramName !== "idSite") {
          return valueFromHash;
        }
      }
      return window.broadcast.getValueFromUrl(paramName, window.location.search);
    }
    parse(query) {
      return broadcast$1.getValuesFromUrl(`?${query}`, true);
    }
    stringify(search) {
      const searchWithoutEmpty = Object.fromEntries(
        Object.entries(search).filter(([, value]) => value !== "" && value !== null && value !== void 0)
      );
      return $.param(searchWithoutEmpty).replace(/%5B%5D/g, "[]").replace(/%2C/g, ",").replace(/\+/g, "%20");
    }
    getMenuPathSuffix() {
      const category = this.getSearchParam("category");
      const subcategory = this.getSearchParam("subcategory");
      return { category: decodeURIComponent(category), subcategory: decodeURIComponent(subcategory) };
    }
    getDateAndPeriodFromUrl() {
      return {
        date: this.getSearchParam("date") || "",
        period: this.getSearchParam("period") || ""
      };
    }
    updatePageTitle() {
      const { period, date } = this.getDateAndPeriodFromUrl();
      const { category, subcategory } = this.getMenuPathSuffix();
      const segment = this.getSearchParam("segment") || "";
      piwik.updateTitle(date, period, category, subcategory, segment);
    }
    updatePeriodParamsFromUrl() {
      const { period, date: initialDate } = this.getDateAndPeriodFromUrl();
      let date = initialDate;
      if (!isValidPeriod(period, date)) {
        return;
      }
      if (piwik.period === period && piwik.currentDateString === date) {
        return;
      }
      piwik.period = period;
      const dateRange = Periods$1.parse(period, date).getDateRange();
      piwik.startDateString = format(dateRange[0]);
      piwik.endDateString = format(dateRange[1]);
      if (piwik.period === "range") {
        date = `${piwik.startDateString},${piwik.endDateString}`;
      }
      piwik.currentDateString = date;
    }
  }
  const instance$1 = new MatomoUrl();
  piwik.updatePeriodParamsFromUrl = instance$1.updatePeriodParamsFromUrl.bind(instance$1);
  function setCookie(name, val, seconds) {
    const date = /* @__PURE__ */ new Date();
    if (!seconds) {
      seconds = 3 * 24 * 60 * 1e3;
    }
    date.setTime(date.getTime() + seconds);
    document.cookie = `${name}=${val}; expires=${date.toUTCString()}; path=/`;
  }
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length == 2) {
      const data = parts.pop().split(";").shift();
      if (typeof data !== "undefined") {
        return data;
      }
    }
    return null;
  }
  function deleteCookie(name) {
    const date = /* @__PURE__ */ new Date();
    date.setTime(date.getTime() + -1 * 24 * 60 * 60 * 1e3);
    document.cookie = `${name}=; expires=${date.toUTCString()}; path=/`;
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$j } = window;
  window.globalAjaxQueue = [];
  window.globalAjaxQueue.active = 0;
  window.globalAjaxQueue.clean = function globalAjaxQueueClean() {
    for (let i = this.length; i >= 0; i -= 1) {
      if (!this[i] || this[i].readyState === 4) {
        this.splice(i, 1);
      }
    }
  };
  window.globalAjaxQueue.push = function globalAjaxQueuePush(...args) {
    this.active += args.length;
    this.clean();
    return Array.prototype.push.call(this, ...args);
  };
  window.globalAjaxQueue.abort = function globalAjaxQueueAbort() {
    this.forEach((x) => x && x.abort && x.abort());
    this.splice(0, this.length);
    this.active = 0;
  };
  function defaultErrorCallback(deferred, status) {
    if (status === "abort" || !deferred || deferred.status === 0) {
      return;
    }
    if (typeof Piwik_Popover === "undefined") {
      console.log(`Request failed: ${deferred.responseText}`);
      return;
    }
    if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
      $$j(document.body).html(piwikHelper.escape(deferred.responseText));
    } else {
      $$j("#loadingError").show();
    }
  }
  function hasExplicitSegmentParam(params) {
    return Object.prototype.hasOwnProperty.call(params, "segment") && typeof params.segment !== "undefined";
  }
  class ApiResponseError extends Error {
  }
  class ChunkedBulkRequestError extends Error {
    constructor(xhr, status, errorThrown) {
      super("Chunked bulk request failed.");
      __publicField(this, "xhr");
      __publicField(this, "status");
      __publicField(this, "errorThrown");
      this.xhr = xhr;
      this.status = status;
      this.errorThrown = errorThrown;
    }
  }
  class ChunkedBulkAbortError extends Error {
    constructor() {
      super("Chunked bulk request was aborted.");
    }
  }
  class ChunkedBulkSessionTimeoutError extends Error {
    constructor() {
      super("Chunked bulk request timed out due to session expiration.");
    }
  }
  const _AjaxHelper = class _AjaxHelper {
    constructor() {
      /**
       * Format of response
       */
      __publicField(this, "format", "json");
      /**
       * A timeout for the request which will override any global timeout
       */
      __publicField(this, "timeout", null);
      /**
       * Callback function to be executed on success
       */
      __publicField(this, "callback", null);
      /**
       * Use this.callback if an error is returned
       */
      __publicField(this, "useRegularCallbackInCaseOfError", false);
      /**
       * Callback function to be executed on error
       *
       * @deprecated use the jquery promise API
       */
      __publicField(this, "errorCallback");
      __publicField(this, "withToken", false);
      /**
       * Callback function to be executed on complete (after error or success)
       *
       * @deprecated use the jquery promise API
       */
      __publicField(this, "completeCallback");
      /**
       * Params to be passed as GET params
       * @see ajaxHelper.mixinDefaultGetParams
       */
      __publicField(this, "getParams", {});
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
      __publicField(this, "getUrl", "?");
      /**
       * Params to be passed as GET params
       * @see ajaxHelper.mixinDefaultPostParams
       */
      __publicField(this, "postParams", {});
      /**
       * Element to be displayed while loading
       */
      __publicField(this, "loadingElement", null);
      /**
       * Element to be displayed on error
       */
      __publicField(this, "errorElement", "#ajaxError");
      /**
       * Extra headers to add to the request.
       */
      __publicField(this, "headers", {
        "X-Requested-With": "XMLHttpRequest"
      });
      /**
       * Handle for current request
       */
      __publicField(this, "requestHandle", null);
      __publicField(this, "abortController", null);
      __publicField(this, "abortable", true);
      __publicField(this, "defaultParams", ["idSite", "period", "date", "segment"]);
      __publicField(this, "resolveWithHelper", false);
      this.errorCallback = defaultErrorCallback;
    }
    // helper method entry point
    static fetch(params, options = {}) {
      if (Array.isArray(params)) {
        if (options.returnResponseObject) {
          throw new Error(this.UNSUPPORTED_BULK_RESPONSE_OBJECT_ERROR);
        }
      }
      const helper = new _AjaxHelper();
      if (options.withTokenInUrl) {
        helper.withTokenInUrl();
      }
      if (options.errorElement) {
        helper.setErrorElement(options.errorElement);
      }
      if (options.redirectOnSuccess) {
        helper.redirectOnSuccess(
          options.redirectOnSuccess !== true ? options.redirectOnSuccess : void 0
        );
      }
      helper.setFormat(options.format || "json");
      if (Array.isArray(params)) {
        helper.setBulkRequests(...params);
      } else {
        Object.keys(params).forEach((key) => {
          if (/password/i.test(key)) {
            throw new Error(`Password parameters are not allowed to be sent as GET parameter. Please send ${key} as POST parameter instead.`);
          }
        });
        const hasExplicitSegment = hasExplicitSegmentParam(params);
        let segmentParam = {};
        if (hasExplicitSegment) {
          let segmentVal = null;
          if (params.segment !== null) {
            segmentVal = encodeURIComponent(params.segment);
          }
          segmentParam = {
            segment: segmentVal
          };
        }
        helper.addParams(__spreadValues(__spreadValues({
          module: "API",
          format: options.format || "json"
        }, params), segmentParam), "get");
      }
      if (options.postParams) {
        helper.addParams(options.postParams, "post");
      }
      if (options.headers) {
        helper.headers = __spreadValues(__spreadValues({}, helper.headers), options.headers);
      }
      let createErrorNotification = true;
      if (typeof options.createErrorNotification !== "undefined" && !options.createErrorNotification) {
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
      if (options.abortable === false) {
        helper.abortable = false;
      }
      return helper.send().then((result) => {
        const data = result instanceof _AjaxHelper ? result.requestHandle.responseJSON : result;
        const results = helper.postParams.method === "API.getBulkRequest" && Array.isArray(data) ? data : [data];
        const errors = results.filter((r) => r.result === "error").map((r) => r.message);
        if (errors.length) {
          throw new ApiResponseError(errors.filter((e) => e.length).join("\n"));
        }
        return result;
      }).catch((error) => {
        if (createErrorNotification || error instanceof ApiResponseError) {
          throw error;
        }
        let message = "Something went wrong";
        if (error instanceof ChunkedBulkAbortError) {
          message = "Request was possibly aborted";
        }
        if (error instanceof ChunkedBulkSessionTimeoutError) {
          message = "Session timed out";
        }
        const status = typeof error === "object" && error !== null && "status" in error ? error.status : null;
        if (status === 504) {
          message = "Request was possibly aborted";
        }
        if (status === 429) {
          message = "Rate Limit was exceed";
        }
        throw new Error(message);
      });
    }
    static getBulkRequestLimit() {
      const bulkRequestLimit = parseInt(`${Matomo.apiBulkRequestLimit}`, 10);
      if (Number.isNaN(bulkRequestLimit)) {
        return -1;
      }
      return bulkRequestLimit;
    }
    static splitIntoChunks(elements, chunkSize) {
      const chunks = [];
      for (let i = 0; i < elements.length; i += chunkSize) {
        chunks.push(elements.slice(i, i + chunkSize));
      }
      return chunks;
    }
    hideLoadingElement() {
      if (this.loadingElement) {
        $$j(this.loadingElement).hide();
      }
    }
    handleApiErrorResponseOrCallback(response, status, request) {
      this.hideLoadingElement();
      const results = this.postParams.method === "API.getBulkRequest" && Array.isArray(response) ? response : [response];
      const errors = results.filter((r) => r.result === "error").map((r) => r.message).filter((e) => e.length).reduce((acc, e) => {
        acc[e] = (acc[e] || 0) + 1;
        return acc;
      }, {});
      if (errors && Object.keys(errors).length && !this.useRegularCallbackInCaseOfError) {
        let errorMessage = "";
        Object.keys(errors).forEach((error) => {
          if (errorMessage.length) {
            errorMessage += "<br />";
          }
          if (errors[error] > 1) {
            errorMessage += `${error} (${errors[error]}x)`;
          } else {
            errorMessage += error;
          }
        });
        let placeAt = null;
        let type = "toast";
        if ($$j(this.errorElement).length && errorMessage.length) {
          $$j(this.errorElement).show();
          placeAt = this.errorElement;
          type = null;
        }
        const isLoggedIn = !document.querySelector("#login_form");
        if (errorMessage && isLoggedIn) {
          const UI = window["require"]("piwik/UI");
          const notification = new UI.Notification();
          notification.show(errorMessage, {
            placeat: placeAt,
            context: "error",
            type,
            id: "ajaxHelper"
          });
          notification.scrollToNotification();
        }
      } else if (this.callback) {
        this.callback(response, status, request);
      }
    }
    buildRequestUrl(getParameters) {
      const parameters = this.mixinDefaultGetParams(getParameters);
      let url = this.getUrl;
      if (url[url.length - 1] !== "?") {
        url += "&";
      }
      if (Object.prototype.hasOwnProperty.call(parameters, "segment")) {
        const segmentValue = parameters.segment;
        delete parameters.segment;
        if (segmentValue !== null && typeof segmentValue !== "undefined") {
          const safeSegmentValue = `${segmentValue}`.replace(/&/g, "%26").replace(/#/g, "%23").replace(/\?/g, "%3F");
          url = `${url}segment=${safeSegmentValue}&`;
        }
      }
      if (parameters.date) {
        const dateStr = parameters.date.toString();
        const period = parameters.period;
        if (!/^[a-z0-9, -]+$/i.test(dateStr)) {
          throw new Error(`Invalid date '${dateStr}'.`);
        }
        if (period && Periods$1.isRecognizedPeriod(period)) {
          const isMultiplePeriod = /^(last|previous)\d/i.test(dateStr) || dateStr.indexOf(",") !== -1;
          try {
            if (isMultiplePeriod && period !== "range") {
              RangePeriod.parse(dateStr, period);
            } else {
              Periods$1.parse(period, dateStr);
            }
          } catch (e) {
            throw new Error(`Invalid date '${dateStr}' for period '${period}'.`);
          }
        }
        url = `${url}date=${encodeURIComponent(dateStr).replace(/%2C/g, ",")}&`;
        delete parameters.date;
      }
      url += $$j.param(parameters);
      return url;
    }
    buildChunkedBulkAjaxCall(urls) {
      const url = this.buildRequestUrl(__spreadValues({}, this.getParams));
      const urlsProcessed = urls.map((bulkUrl) => typeof bulkUrl === "string" ? bulkUrl : $$j.param(bulkUrl));
      return $$j.ajax({
        type: "POST",
        async: true,
        url,
        dataType: this.format || "json",
        headers: this.headers ? this.headers : void 0,
        data: this.mixinDefaultPostParams(__spreadProps(__spreadValues({}, this.postParams), {
          urls: urlsProcessed
        })),
        timeout: this.timeout !== null ? this.timeout : void 0
      });
    }
    getBulkRequestUrls() {
      if (this.postParams.method !== "API.getBulkRequest" || !Array.isArray(this.postParams.urls)) {
        return null;
      }
      return this.postParams.urls;
    }
    shouldSendBulkRequestInChunks() {
      const bulkRequestUrls = this.getBulkRequestUrls();
      if (!bulkRequestUrls) {
        return false;
      }
      const bulkRequestLimit = _AjaxHelper.getBulkRequestLimit();
      return bulkRequestLimit > 0 && bulkRequestUrls.length > bulkRequestLimit;
    }
    shouldRejectBulkResponseObjectRequest() {
      return !!this.getBulkRequestUrls() && this.resolveWithHelper;
    }
    sendBulkRequestInChunks() {
      const bulkRequestUrls = this.getBulkRequestUrls();
      if (!bulkRequestUrls) {
        return Promise.resolve([]);
      }
      const bulkRequestLimit = _AjaxHelper.getBulkRequestLimit();
      if (bulkRequestLimit <= 0) {
        return Promise.resolve([]);
      }
      try {
        this.buildRequestUrl(__spreadValues({}, this.getParams));
      } catch (e) {
        this.hideLoadingElement();
        return Promise.reject(e);
      }
      const chunkedAbortController = this.abortController || new AbortController();
      this.abortController = chunkedAbortController;
      let activeChunkRequest = null;
      let isQueueFinalized = false;
      let hasCompleteCallbackRun = false;
      const finalizeQueue = () => {
        if (isQueueFinalized || !this.abortable) {
          return;
        }
        window.globalAjaxQueue.active -= 1;
        isQueueFinalized = true;
      };
      const runCompleteCallback = (request, status) => {
        if (hasCompleteCallbackRun || !this.completeCallback) {
          return;
        }
        hasCompleteCallbackRun = true;
        this.completeCallback(request, status);
      };
      const requestHandle = {
        readyState: 1,
        status: 0,
        statusText: "",
        responseJSON: [],
        abort: () => {
          chunkedAbortController.abort();
        }
      };
      const requestHandleAsJqXHR = requestHandle;
      let callbackRequest = requestHandleAsJqXHR;
      this.requestHandle = requestHandleAsJqXHR;
      if (this.abortable) {
        window.globalAjaxQueue.push(requestHandleAsJqXHR);
      }
      chunkedAbortController.signal.addEventListener("abort", () => {
        if (activeChunkRequest) {
          activeChunkRequest.abort();
        }
      });
      const chunks = _AjaxHelper.splitIntoChunks(bulkRequestUrls, bulkRequestLimit);
      const results = [];
      const sendChunk = (chunkIndex) => {
        if (chunkIndex >= chunks.length) {
          return Promise.resolve(results);
        }
        activeChunkRequest = this.buildChunkedBulkAjaxCall(chunks[chunkIndex]);
        return new Promise((resolve2, reject) => {
          activeChunkRequest.then((chunkResult, status, xhr) => {
            callbackRequest = xhr;
            requestHandle.readyState = xhr.readyState;
            requestHandle.status = xhr.status;
            requestHandle.statusText = xhr.statusText || status;
            if (Array.isArray(chunkResult)) {
              results.push(...chunkResult);
            } else {
              results.push(chunkResult);
            }
            resolve2(results);
          }).fail((xhr, status, errorThrown) => {
            requestHandle.readyState = xhr.readyState;
            requestHandle.status = xhr.status;
            requestHandle.statusText = xhr.statusText || status;
            reject(new ChunkedBulkRequestError(xhr, status, errorThrown));
          });
        }).then(() => sendChunk(chunkIndex + 1));
      };
      return sendChunk(0).then((chunkResults) => {
        requestHandle.readyState = 4;
        requestHandle.responseJSON = chunkResults;
        this.handleApiErrorResponseOrCallback(chunkResults, "success", callbackRequest);
        finalizeQueue();
        runCompleteCallback(callbackRequest, "success");
        if (Matomo.ajaxRequestFinished) {
          Matomo.ajaxRequestFinished();
        }
        return chunkResults;
      }).catch((error) => {
        if (!(error instanceof ChunkedBulkRequestError)) {
          throw error;
        }
        const { xhr, status, errorThrown } = error;
        finalizeQueue();
        if (this.errorCallback) {
          this.errorCallback.apply(this, [xhr, status, errorThrown]);
        }
        runCompleteCallback(xhr, status);
        if (xhr.status === 429) {
          console.log(`Warning: the '${$$j.param(this.getParams)}' request was rate limited!`);
          throw xhr;
        }
        if (xhr.statusText === "abort" || xhr.status === 0) {
          throw new ChunkedBulkAbortError();
        }
        const isInApp = !document.querySelector("#login_form");
        const sessionTimedOut = xhr.getResponseHeader("X-Matomo-Session-Timed-Out") === "1";
        if (sessionTimedOut && isInApp) {
          setCookie("matomo_session_timed_out", "1", 60 * 1e3);
          Matomo.helper.refreshAfter(0);
          throw new ChunkedBulkSessionTimeoutError();
        }
        console.log(`Warning: the ${$$j.param(this.getParams)} request failed!`);
        throw xhr;
      });
    }
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    static post(params, postParams = {}, options = {}) {
      return _AjaxHelper.fetch(params, __spreadProps(__spreadValues({}, options), { postParams }));
    }
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    static oneAtATime(method, options) {
      let abortController = null;
      return (params, postParams) => {
        if (abortController) {
          abortController.abort();
        }
        abortController = new AbortController();
        return _AjaxHelper.post(
          __spreadProps(__spreadValues({}, params), {
            method
          }),
          postParams,
          __spreadProps(__spreadValues({}, options), {
            abortController
          })
        ).finally(() => {
          abortController = null;
        });
      };
    }
    /**
     * Adds params to the request.
     * If params are given more then once, the latest given value is used for the request
     *
     * @param  initialParams
     * @param  type  type of given parameters (POST or GET)
     * @return {void}
     */
    addParams(initialParams, type) {
      const params = typeof initialParams === "string" ? window.broadcast.getValuesFromUrl(initialParams) : initialParams;
      const arrayParams = ["compareSegments", "comparePeriods", "compareDates"];
      Object.keys(params).forEach((key) => {
        let value = params[key];
        if (arrayParams.indexOf(key) !== -1 && !value) {
          return;
        }
        if (typeof value === "boolean") {
          value = value ? 1 : 0;
        }
        if (type.toLowerCase() === "get") {
          this.getParams[key] = value;
        } else if (type.toLowerCase() === "post") {
          this.postParams[key] = value;
        }
      });
    }
    withTokenInUrl() {
      this.withToken = true;
    }
    /**
     * Sets the base URL to use in the AJAX request.
     */
    setUrl(url) {
      this.addParams(broadcast.getValuesFromUrl(url), "GET");
    }
    /**
     * Gets this helper instance ready to send a bulk request. Each argument to this
     * function is a single request to use.
     */
    setBulkRequests(...urls) {
      const urlsProcessed = urls.map((u) => typeof u === "string" ? u : $$j.param(u));
      this.addParams({
        module: "API",
        method: "API.getBulkRequest",
        urls: urlsProcessed,
        format: "json"
      }, "post");
    }
    /**
     * Set a timeout (in milliseconds) for the request. This will override any global timeout.
     *
     * @param timeout  Timeout in milliseconds
     */
    setTimeout(timeout) {
      this.timeout = timeout;
    }
    /**
     * Sets the callback called after the request finishes
     *
     * @param callback  Callback function
     * @deprecated use the jquery promise API
     */
    setCallback(callback) {
      this.callback = callback;
    }
    /**
     * Set that the callback passed to setCallback() should be used if an application error (i.e. an
     * Exception in PHP) is returned.
     */
    useCallbackInCaseOfError() {
      this.useRegularCallbackInCaseOfError = true;
    }
    /**
     * Set callback to redirect on success handler
     * &update=1(+x) will be appended to the current url
     *
     * @param [params] to modify in redirect url
     * @return {void}
     */
    redirectOnSuccess(params) {
      this.setCallback(() => {
        piwikHelper.redirect(params);
      });
    }
    /**
     * Sets the callback called in case of an error within the request
     *
     * @deprecated use the jquery promise API
     */
    setErrorCallback(callback) {
      this.errorCallback = callback;
    }
    /**
     * Sets the complete callback which is called after an error or success callback.
     *
     * @deprecated use the jquery promise API
     */
    setCompleteCallback(callback) {
      this.completeCallback = callback;
    }
    /**
     * Sets the response format for the request
     *
     * @param format  response format (e.g. json, html, ...)
     */
    setFormat(format2) {
      this.format = format2;
    }
    /**
     * Set the div element to show while request is loading
     *
     * @param [element]  selector for the loading element
     */
    setLoadingElement(element) {
      this.loadingElement = element || "#ajaxLoadingDiv";
    }
    /**
     * Set the div element to show on error
     *
     * @param element  selector for the error element
     */
    setErrorElement(element) {
      if (!element) {
        return;
      }
      this.errorElement = element;
    }
    /**
     * Detect whether are allowed to use the given default parameter or not
     */
    useGETDefaultParameter(parameter) {
      if (parameter && this.defaultParams) {
        for (let i = 0; i < this.defaultParams.length; i += 1) {
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
    removeDefaultParameter(parameter) {
      if (parameter && this.defaultParams) {
        for (let i = 0; i < this.defaultParams.length; i += 1) {
          if (this.defaultParams[i] === parameter) {
            this.defaultParams.splice(i, 1);
          }
        }
      }
    }
    /**
     * Send the request
     */
    send() {
      if ($$j(this.errorElement).length) {
        $$j(this.errorElement).hide();
      }
      if (this.shouldRejectBulkResponseObjectRequest()) {
        throw new Error(_AjaxHelper.UNSUPPORTED_BULK_RESPONSE_OBJECT_ERROR);
      }
      if (this.loadingElement) {
        $$j(this.loadingElement).fadeIn();
      }
      if (this.shouldSendBulkRequestInChunks()) {
        return this.sendBulkRequestInChunks();
      }
      try {
        this.requestHandle = this.buildAjaxCall();
      } catch (e) {
        this.hideLoadingElement();
        return Promise.reject(e);
      }
      if (this.abortable) {
        window.globalAjaxQueue.push(this.requestHandle);
      }
      if (this.abortController) {
        this.abortController.signal.addEventListener("abort", () => {
          if (this.requestHandle) {
            this.requestHandle.abort();
          }
        });
      }
      const result = new Promise((resolve2, reject) => {
        this.requestHandle.then((data) => {
          if (this.resolveWithHelper) {
            resolve2(this);
          } else {
            resolve2(data);
          }
        }).fail((xhr) => {
          if (xhr.status === 429) {
            console.log(`Warning: the '${$$j.param(this.getParams)}' request was rate limited!`);
            reject(xhr);
            return;
          }
          if (xhr.statusText === "abort" || xhr.status === 0) {
            return;
          }
          const isInApp = !document.querySelector("#login_form");
          const sessionTimedOut = xhr.getResponseHeader("X-Matomo-Session-Timed-Out") === "1";
          if (sessionTimedOut && isInApp) {
            setCookie("matomo_session_timed_out", "1", 60 * 1e3);
            Matomo.helper.refreshAfter(0);
            return;
          }
          console.log(`Warning: the ${$$j.param(this.getParams)} request failed!`);
          reject(xhr);
        });
      });
      return result;
    }
    /**
     * Aborts the current request if it is (still) running
     */
    abort() {
      if (this.requestHandle && typeof this.requestHandle.abort === "function") {
        this.requestHandle.abort();
        this.requestHandle = null;
      }
    }
    /**
     * Builds and sends the ajax requests
     */
    buildAjaxCall() {
      const self2 = this;
      const url = this.buildRequestUrl(this.getParams);
      const ajaxCall = {
        type: "POST",
        async: true,
        url,
        dataType: this.format || "json",
        complete: this.completeCallback,
        headers: this.headers ? this.headers : void 0,
        error: function errorCallback(...args) {
          if (self2.abortable) {
            window.globalAjaxQueue.active -= 1;
          }
          if (self2.errorCallback) {
            self2.errorCallback.apply(this, args);
          }
        },
        success: (response, status, request) => {
          this.handleApiErrorResponseOrCallback(response, status, request);
          if (self2.abortable) {
            window.globalAjaxQueue.active -= 1;
          }
          if (Matomo.ajaxRequestFinished) {
            Matomo.ajaxRequestFinished();
          }
        },
        data: this.mixinDefaultPostParams(this.postParams),
        timeout: this.timeout !== null ? this.timeout : void 0
      };
      return $$j.ajax(ajaxCall);
    }
    isRequestToApiMethod() {
      return this.getParams && this.getParams.module === "API" && this.getParams.method || this.postParams && this.postParams.module === "API" && this.postParams.method;
    }
    isWidgetizedRequest() {
      return broadcast.getValueFromUrl("module") === "Widgetize";
    }
    getDefaultPostParams() {
      if (this.withToken || this.isRequestToApiMethod() || Matomo.shouldPropagateTokenAuth) {
        return {
          token_auth: Matomo.token_auth,
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
    mixinDefaultPostParams(params) {
      const defaultParams = this.getDefaultPostParams();
      const mergedParams = __spreadValues(__spreadValues({}, defaultParams), params);
      return mergedParams;
    }
    /**
     * Mixin the default parameters to send as GET
     *
     * @param   params   parameter object
     */
    mixinDefaultGetParams(originalParams) {
      const segment = instance$1.getSearchParam("segment");
      const defaultParams = {
        idSite: Matomo.idSite ? Matomo.idSite.toString() : broadcast.getValueFromUrl("idSite"),
        period: Matomo.period || broadcast.getValueFromUrl("period"),
        segment
      };
      const params = originalParams;
      const hasExplicitSegment = hasExplicitSegmentParam(params) || hasExplicitSegmentParam(this.postParams);
      if (params.token_auth) {
        params.token_auth = null;
        delete params.token_auth;
      }
      Object.keys(defaultParams).forEach((key) => {
        if (this.useGETDefaultParameter(key) && !(key === "segment" && hasExplicitSegment) && (params[key] === null || typeof params[key] === "undefined" || params[key] === "") && (this.postParams[key] === null || typeof this.postParams[key] === "undefined" || this.postParams[key] === "") && defaultParams[key]) {
          params[key] = defaultParams[key];
        }
      });
      if (this.useGETDefaultParameter("date") && !params.date && !this.postParams.date) {
        params.date = Matomo.currentDateString;
      }
      return params;
    }
    getRequestHandle() {
      return this.requestHandle;
    }
  };
  // eslint-disable-line
  __publicField(_AjaxHelper, "UNSUPPORTED_BULK_RESPONSE_OBJECT_ERROR", "AjaxHelper returnResponseObject is not supported for bulk requests.");
  let AjaxHelper = _AjaxHelper;
  window.ajaxHelper = AjaxHelper;
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$i } = window;
  class NumberFormatter {
    constructor() {
      __publicField(this, "defaultMinFractionDigits", 0);
      __publicField(this, "defaultMaxFractionDigits", 2);
    }
    format(val, formatPattern, maxFractionDigits, minFractionDigits) {
      if (!$$i.isNumeric(val)) {
        return String(val);
      }
      let value = val;
      let pattern = formatPattern || Matomo.numbers.patternNumber;
      const patterns = pattern.split(";");
      if (patterns.length === 1) {
        patterns.push(`-${patterns[0]}`);
      }
      const negative = value < 0;
      pattern = negative ? patterns[1] : patterns[0];
      value = Math.abs(value);
      if (maxFractionDigits >= 0) {
        const factionFactor = __pow(10, maxFractionDigits);
        value = Math.round(value * factionFactor) / factionFactor;
      }
      const valueParts = value.toString().split(".");
      let majorDigits = valueParts[0];
      let minorDigits = valueParts[1] || "";
      const usesGrouping = pattern.indexOf(",") !== -1;
      if (usesGrouping) {
        const primaryGroupMatches = pattern.match(/#+0/);
        const primaryGroupSize = (primaryGroupMatches == null ? void 0 : primaryGroupMatches[0].length) || 0;
        let secondaryGroupSize = (primaryGroupMatches == null ? void 0 : primaryGroupMatches[0].length) || 0;
        const numberGroups = pattern.split(",");
        if (numberGroups.length > 2) {
          secondaryGroupSize = numberGroups[1].length;
        }
        const digits = majorDigits.split("").reverse();
        let groups = [];
        groups.push(digits.splice(0, primaryGroupSize).reverse().join(""));
        while (digits.length) {
          groups.push(digits.splice(0, secondaryGroupSize).reverse().join(""));
        }
        groups = groups.reverse();
        majorDigits = groups.join(",");
      }
      if (minFractionDigits > 0) {
        minorDigits = minorDigits.replace(/0+$/, "");
        if (minorDigits.length < minFractionDigits && minorDigits.length < maxFractionDigits) {
          const neededZeroes = minFractionDigits - minorDigits.length;
          minorDigits += new Array(neededZeroes + 1).join("0");
        }
      }
      let result = minorDigits ? `${majorDigits}.${minorDigits}` : majorDigits;
      result = pattern.replace(/#(?:[.,]#+)*0(?:[,.][0#]+)*/, result);
      return this.replaceSymbols(result);
    }
    replaceSymbols(value) {
      const replacements = {
        ".": Matomo.numbers.symbolDecimal,
        ",": Matomo.numbers.symbolGroup,
        "+": Matomo.numbers.symbolPlus,
        "-": Matomo.numbers.symbolMinus,
        "%": Matomo.numbers.symbolPercent
      };
      let newValue = "";
      const valueParts = value.split("");
      valueParts.forEach((val) => {
        let valueReplaced = val;
        Object.entries(replacements).some(([char, replacement]) => {
          if (valueReplaced.indexOf(char) !== -1) {
            valueReplaced = valueReplaced.replace(char, replacement);
            return true;
          }
          return false;
        });
        newValue += valueReplaced;
      });
      return newValue;
    }
    valOrDefault(val, def) {
      if (typeof val === "undefined") {
        return def;
      }
      return val;
    }
    getMaxFractionDigitsForCompactFormat(valueLength) {
      return valueLength === 1 ? 1 : 0;
    }
    determineCorrectCompactPattern(patterns, value) {
      var _a2;
      let factor = 0;
      let finalFactor = 0;
      let patternId = "";
      if (Math.round(value) < 1e3) {
        return ["0", 1];
      }
      for (factor = 1e3; factor <= 1e19; factor *= 10) {
        const patternOne = `${factor}One`;
        const patternOther = `${factor}Other`;
        if (Math.round(value / factor) === 1 && (patterns == null ? void 0 : patterns[patternOne]) !== "") {
          finalFactor = factor;
          patternId = patternOne;
        } else if (Math.round(value / factor) >= 1 && (patterns == null ? void 0 : patterns[patternOther]) !== "") {
          finalFactor = factor;
          patternId = patternOther;
        }
        if (patterns == null ? void 0 : patterns[patternId]) {
          const charCount = ((_a2 = patterns == null ? void 0 : patterns[patternId].match(/0/g)) == null ? void 0 : _a2.length) || 1;
          if (Math.round(value * __pow(10, charCount) / (factor * 10)) < __pow(10, charCount)) {
            break;
          }
        }
      }
      return [(patterns == null ? void 0 : patterns[patternId]) || "0", finalFactor];
    }
    formatCompact(pattern, factor, value) {
      var _a2;
      const charCount = ((_a2 = pattern.match(/0/g)) == null ? void 0 : _a2.length) || 0;
      let finalFactor = factor;
      if (charCount > 1) {
        finalFactor /= __pow(10, charCount - 1);
      }
      const maximumFractionDigits = this.getMaxFractionDigitsForCompactFormat(charCount);
      const digitCountFactor = __pow(10, maximumFractionDigits);
      const finalValue = Math.round(value / finalFactor * digitCountFactor) / digitCountFactor;
      const formattedNumber = this.formatNumber(finalValue, maximumFractionDigits, 0);
      return pattern.replace(/(0+)/, formattedNumber).replace(/('\.')/, ".");
    }
    parseFormattedNumber(value) {
      const isNegative = value.indexOf(Matomo.numbers.symbolMinus) > -1 || value.startsWith("-");
      const numberParts = value.split(Matomo.numbers.symbolDecimal);
      numberParts.forEach((val, index) => {
        numberParts[index] = val.replace(/[^0-9]/g, "");
      });
      return (isNegative ? -1 : 1) * parseFloat(numberParts.join("."));
    }
    formatNumber(value, maxFractionDigits, minFractionDigits) {
      return this.format(
        value,
        Matomo.numbers.patternNumber,
        this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
        this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits)
      );
    }
    formatPercent(value, maxFractionDigits, minFractionDigits) {
      return this.format(
        value,
        Matomo.numbers.patternPercent,
        this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
        this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits)
      );
    }
    formatCurrency(value, currency, maxFractionDigits, minFractionDigits) {
      const formatted = this.format(
        value,
        Matomo.numbers.patternCurrency,
        this.valOrDefault(maxFractionDigits, this.defaultMaxFractionDigits),
        this.valOrDefault(minFractionDigits, this.defaultMinFractionDigits)
      );
      return formatted.replace("¤", currency);
    }
    formatNumberCompact(value) {
      const val = value;
      const [compactPattern, factor] = this.determineCorrectCompactPattern(
        Matomo.numbers.patternsCompactNumber || [],
        val
      );
      if (Math.round(val) < 1e3 || compactPattern === "0") {
        return this.formatNumber(
          val,
          this.getMaxFractionDigitsForCompactFormat(Math.round(val)),
          0
        );
      }
      return this.formatCompact(compactPattern, factor, val);
    }
    formatCurrencyCompact(value, currency) {
      const val = value;
      const [compactPattern, factor] = this.determineCorrectCompactPattern(
        Matomo.numbers.patternsCompactCurrency || [],
        val
      );
      if (Math.round(val) < 1e3 || compactPattern === "0") {
        return this.formatCurrency(
          val,
          currency,
          this.getMaxFractionDigitsForCompactFormat(Math.round(val)),
          0
        );
      }
      return this.formatCompact(compactPattern, factor, val).replace("¤", currency);
    }
    formatEvolution(evolution, maxFractionDigits, minFractionDigits, noSign) {
      if (noSign) {
        return this.formatPercent(
          Math.abs(evolution),
          maxFractionDigits,
          minFractionDigits
        );
      }
      const formattedEvolution = this.formatPercent(evolution, maxFractionDigits, minFractionDigits);
      return `${evolution > 0 ? Matomo.numbers.symbolPlus : ""}${formattedEvolution}`;
    }
    calculateAndFormatEvolution(currentValue, pastValue, noSign) {
      const pastValueParsed = parseInt(pastValue, 10);
      const currentValueParsed = parseInt(currentValue, 10) - pastValueParsed;
      let evolution;
      if (currentValueParsed === 0 || Number.isNaN(currentValueParsed)) {
        evolution = 0;
      } else if (pastValueParsed === 0 || Number.isNaN(pastValueParsed)) {
        evolution = 100;
      } else {
        evolution = currentValueParsed / pastValueParsed * 100;
      }
      let maxFractionDigits = 3;
      if (Math.abs(evolution) > 100) {
        maxFractionDigits = 0;
      } else if (Math.abs(evolution) > 10) {
        maxFractionDigits = 1;
      } else if (Math.abs(evolution) > 1) {
        maxFractionDigits = 2;
      }
      return this.formatEvolution(evolution, maxFractionDigits, 0, noSign);
    }
  }
  const NumberFormatter$1 = new NumberFormatter();
  window.NumberFormatter = NumberFormatter$1;
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$h } = window;
  class PopoverHandler {
    constructor() {
      this.setup();
    }
    setup() {
      vue.watch(() => instance$1.parsed.value.popover, () => this.onPopoverParamChanged());
      if (instance$1.parsed.value.popover) {
        this.onPopoverParamChangedInitial();
      }
    }
    // don't initiate the handler until the page had a chance to render,
    // since some rowactions depend on what's been loaded.
    onPopoverParamChangedInitial() {
      $$h(() => {
        setTimeout(() => {
          this.openOrClose();
        });
      });
    }
    onPopoverParamChanged() {
      $$h(() => {
        this.openOrClose();
      });
    }
    openOrClose() {
      this.close();
      const popoverParam = instance$1.parsed.value.popover;
      if (popoverParam) {
        this.open(popoverParam);
      } else {
        window.broadcast.resetPopoverStack();
      }
    }
    close() {
      window.Piwik_Popover.close();
    }
    open(thePopoverParam) {
      let popoverParam = decodeURIComponent(thePopoverParam);
      popoverParam = popoverParam.replace(/\$/g, "%");
      popoverParam = decodeURIComponent(popoverParam);
      const popoverParamParts = popoverParam.split(":");
      const handlerName = popoverParamParts[0];
      popoverParamParts.shift();
      const param = popoverParamParts.join(":");
      if (typeof window.broadcast.popoverHandlers[handlerName] !== "undefined" && !window.broadcast.isLoginPage()) {
        window.broadcast.popoverHandlers[handlerName](param);
      }
    }
  }
  new PopoverHandler();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$g } = window;
  let zenModeShortcutRegistered = false;
  function handleZenMode() {
    let zenMode = !!parseInt(getCookie("zenMode"), 10);
    const iconSwitcher = $$g(".top_controls .zenModeToggle");
    function updateZenMode() {
      if (zenMode) {
        $$g("body").addClass("zenMode");
        iconSwitcher.addClass("icon-arrowdown").removeClass("icon-arrowup");
        iconSwitcher.prop("title", translate("CoreHome_ExitZenMode"));
      } else {
        $$g("body").removeClass("zenMode");
        iconSwitcher.removeClass("icon-arrowdown").addClass("icon-arrowup");
        iconSwitcher.prop("title", translate("CoreHome_EnterZenMode"));
      }
    }
    if (!zenModeShortcutRegistered) {
      Matomo.helper.registerShortcut("z", translate("CoreHome_ShortcutZenMode"), (event) => {
        if (event.altKey) {
          return;
        }
        zenMode = !zenMode;
        setCookie("zenMode", zenMode ? "1" : "0");
        updateZenMode();
      });
      zenModeShortcutRegistered = true;
    }
    iconSwitcher.off("click.matomoZenMode").on("click.matomoZenMode", () => {
      window.Mousetrap.trigger("z");
    });
    updateZenMode();
  }
  $$g(handleZenMode);
  Matomo.on("Matomo.topControlsRendered", () => {
    handleZenMode();
  });
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function externalRawLink(url, ...values) {
    const pkArgs = values;
    if (!window._pk_externalRawLink) {
      return url;
    }
    return window._pk_externalRawLink(url, pkArgs);
  }
  function externalLink(url, ...values) {
    if (!url) {
      return "";
    }
    const campaignOverride = values.length > 0 && values[0] ? values[0] : null;
    const sourceOverride = values.length > 1 && values[1] ? values[1] : null;
    const mediumOverride = values.length > 2 && values[2] ? values[2] : null;
    const returnUrl = externalRawLink(url, campaignOverride, sourceOverride, mediumOverride);
    return '<a target="_blank" rel="noreferrer noopener" href="' + returnUrl + '">';
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function formatNumber(val, maxFractionDigits, minFractionDigits) {
    return NumberFormatter$1.formatNumber(val, maxFractionDigits, minFractionDigits);
  }
  function formatPercent(val, maxFractionDigits, minFractionDigits) {
    return NumberFormatter$1.formatPercent(val, maxFractionDigits, minFractionDigits);
  }
  function formatCurrency(val, cur, maxFractionDigits, minFractionDigits) {
    return NumberFormatter$1.formatCurrency(val, cur, maxFractionDigits, minFractionDigits);
  }
  function formatEvolution(val, maxFractionDigits, minFractionDigits, noSign) {
    return NumberFormatter$1.formatEvolution(val, maxFractionDigits, minFractionDigits, noSign);
  }
  function calculateAndFormatEvolution(valCur, valPrev, noSign) {
    return NumberFormatter$1.calculateAndFormatEvolution(valCur, valPrev, noSign);
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function createVueApp(...args) {
    const app = vue.createApp(...args);
    app.config.globalProperties.$sanitize = window.vueSanitize;
    app.config.globalProperties.$sanitizeUrl = window.vueSanitizeUrl;
    app.config.globalProperties.translate = translate;
    app.config.globalProperties.translateOrDefault = translateOrDefault;
    app.config.globalProperties.externalLink = externalLink;
    app.config.globalProperties.externalRawLink = externalRawLink;
    app.config.globalProperties.formatNumber = formatNumber;
    app.config.globalProperties.formatPercent = formatPercent;
    app.config.globalProperties.formatCurrency = formatCurrency;
    app.config.globalProperties.formatEvolution = formatEvolution;
    app.config.globalProperties.calculateAndFormatEvolution = calculateAndFormatEvolution;
    return app;
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const pluginLoadingPromises = {};
  const PLUGIN_LOAD_TIMEOUT = 120;
  const POLL_INTERVAL = 50;
  const POLL_LIMIT = 1e3;
  function importPluginUmd(plugin) {
    if (pluginLoadingPromises[plugin]) {
      return pluginLoadingPromises[plugin];
    }
    if (window[plugin]) {
      return Promise.resolve(window[plugin]);
    }
    const pluginUmdPath = `?module=Proxy&action=getPluginUmdJs&plugin=${plugin}`;
    let promiseReject;
    let promiseResolve;
    const script = document.createElement("script");
    script.charset = "utf-8";
    script.timeout = PLUGIN_LOAD_TIMEOUT;
    script.src = pluginUmdPath;
    let timeout;
    const error = new Error();
    const onScriptComplete = (event) => {
      script.onerror = null;
      script.onload = null;
      clearTimeout(timeout);
      let pollProgress = 0;
      function checkPluginInWindow() {
        pollProgress += POLL_INTERVAL;
        if (!promiseReject || !promiseResolve) {
          return;
        }
        if (window[plugin] && promiseResolve) {
          try {
            promiseResolve(window[plugin]);
          } finally {
            promiseReject = void 0;
            promiseResolve = void 0;
          }
          return;
        }
        if (pollProgress > POLL_LIMIT) {
          try {
            const errorType = event && (event.type === "load" ? "missing" : event.type);
            const realSrc = event && event.target && event.target.src;
            error.message = `Loading plugin ${plugin} on demand failed.
(${errorType}: ${realSrc})`;
            error.name = "PluginOnDemandLoadError";
            error.type = errorType;
            error.request = realSrc;
            promiseReject(error);
          } finally {
            promiseReject = void 0;
            promiseResolve = void 0;
          }
          return;
        }
        setTimeout(checkPluginInWindow, POLL_INTERVAL);
      }
      setTimeout(checkPluginInWindow, POLL_INTERVAL);
    };
    timeout = setTimeout(() => {
      onScriptComplete({ type: "timeout", target: script });
    }, PLUGIN_LOAD_TIMEOUT);
    script.onerror = onScriptComplete;
    script.onload = onScriptComplete;
    document.head.appendChild(script);
    return new Promise((resolve2, reject) => {
      promiseResolve = resolve2;
      promiseReject = reject;
    });
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function useExternalPluginComponent(plugin, component) {
    return vue.defineAsyncComponent(() => importPluginUmd(plugin).then((module2) => {
      if (!module2) {
        resolve(null);
      }
      return module2[component];
    }));
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function getRef(expander, binding) {
    var _a2;
    return expander instanceof HTMLElement ? expander : (_a2 = binding.instance) == null ? void 0 : _a2.$refs[expander];
  }
  const DirectiveUtilities = {
    getRef
  };
  const DEFAULT_DEBOUNCE_DELAY = 300;
  function debounce(fn, delayInMs = DEFAULT_DEBOUNCE_DELAY) {
    let timeout;
    return function wrapper(...args) {
      if (timeout) {
        clearTimeout(timeout);
      }
      timeout = setTimeout(() => {
        fn.call(this, ...args);
      }, delayInMs);
    };
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function clone(p) {
    if (typeof p === "undefined") {
      return p;
    }
    return JSON.parse(JSON.stringify(p));
  }
  const _sfc_main$M = vue.defineComponent({
    props: {
      html: String
    },
    mounted() {
      Matomo.helper.compileVueEntryComponents(this.$refs.root);
    },
    beforeUnmount() {
      Matomo.helper.destroyVueComponent(this.$refs.root);
    },
    computed: {
      componentWrapper() {
        if (!this.html) {
          return null;
        }
        return vue.markRaw({
          template: this.html
        });
      }
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$H = { ref: "root" };
  function _sfc_render$L(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$H, [
      _ctx.componentWrapper ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.componentWrapper), { key: 0 })) : vue.createCommentVNode("", true)
    ], 512);
  }
  const VueEntryContainer = /* @__PURE__ */ _export_sfc(_sfc_main$M, [["render", _sfc_render$L]]);
  const _sfc_main$L = vue.defineComponent({});
  const _hoisted_1$G = { class: "matomo-loader" };
  function _sfc_render$K(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("span", _hoisted_1$G, [..._cache[0] || (_cache[0] = [
      vue.createElementVNode("span", null, null, -1),
      vue.createElementVNode("span", null, null, -1),
      vue.createElementVNode("span", null, null, -1)
    ])]);
  }
  const MatomoLoader = /* @__PURE__ */ _export_sfc(_sfc_main$L, [["render", _sfc_render$K]]);
  const _sfc_main$K = vue.defineComponent({
    components: { MatomoLoader },
    props: {
      loading: {
        type: Boolean,
        required: true,
        default: false
      },
      loadingMessage: {
        type: String,
        required: false,
        default: translate("General_LoadingData")
      }
    }
  });
  const _hoisted_1$F = { class: "loadingPiwik" };
  function _sfc_render$J(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$F, [
      vue.createVNode(_component_MatomoLoader),
      vue.createElementVNode("span", null, vue.toDisplayString(_ctx.loadingMessage), 1)
    ], 512)), [
      [vue.vShow, _ctx.loading]
    ]);
  }
  const ActivityIndicator = /* @__PURE__ */ _export_sfc(_sfc_main$K, [["render", _sfc_render$J]]);
  const _sfc_main$J = vue.defineComponent({
    props: {
      severity: {
        type: String,
        required: true
      }
    }
  });
  function _sfc_render$I(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["alert", { [`alert-${_ctx.severity}`]: true }])
    }, [
      vue.renderSlot(_ctx.$slots, "default")
    ], 2);
  }
  const Alert = /* @__PURE__ */ _export_sfc(_sfc_main$J, [["render", _sfc_render$I]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const DropdownMenu = {
    mounted(element, binding) {
      var _a2;
      let options = {};
      $(element).addClass("matomo-dropdown-menu");
      const isSubmenu = !!$(element).parent().closest(".dropdown-content").length;
      if (isSubmenu) {
        options = { hover: true };
        $(element).addClass("submenu");
        $(((_a2 = binding.value) == null ? void 0 : _a2.activates) || $(element).data("target")).addClass("submenu-dropdown-content");
        $(element).parents(".dropdown-content").addClass("submenu-container");
      }
      $(element).dropdown(options);
    },
    updated(element) {
      vue.nextTick(() => {
        $(element).addClass("matomo-dropdown-menu");
      });
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function onClickOutsideElement$2(element, binding, event) {
    const hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
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
  function onScroll$1(element, binding) {
    binding.value.hasScrolled = true;
  }
  function onMouseDown$1(element, binding) {
    binding.value.isMouseDown = true;
    binding.value.hasScrolled = false;
  }
  function onEscapeHandler$2(element, binding, event) {
    if (event.which === 27) {
      setTimeout(() => {
        binding.value.isMouseDown = false;
        binding.value.hasScrolled = false;
        if (binding.value.blur) {
          binding.value.blur();
        }
      }, 0);
    }
  }
  const doc$2 = document.documentElement;
  const FocusAnywhereButHere = {
    mounted(el, binding) {
      binding.value.isMouseDown = false;
      binding.value.hasScrolled = false;
      binding.value.onEscapeHandler = onEscapeHandler$2.bind(null, el, binding);
      binding.value.onMouseDown = onMouseDown$1.bind(null, el, binding);
      binding.value.onClickOutsideElement = onClickOutsideElement$2.bind(null, el, binding);
      binding.value.onScroll = onScroll$1.bind(null, el, binding);
      doc$2.addEventListener("keyup", binding.value.onEscapeHandler);
      doc$2.addEventListener("mousedown", binding.value.onMouseDown);
      doc$2.addEventListener("mouseup", binding.value.onClickOutsideElement);
      doc$2.addEventListener("scroll", binding.value.onScroll);
    },
    unmounted(el, binding) {
      doc$2.removeEventListener("keyup", binding.value.onEscapeHandler);
      doc$2.removeEventListener("mousedown", binding.value.onMouseDown);
      doc$2.removeEventListener("mouseup", binding.value.onClickOutsideElement);
      doc$2.removeEventListener("scroll", binding.value.onScroll);
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function doFocusIf(el, binding) {
    var _a2, _b;
    if (((_a2 = binding.value) == null ? void 0 : _a2.focused) && !((_b = binding.oldValue) == null ? void 0 : _b.focused)) {
      setTimeout(() => {
        el.focus();
        if (binding.value.afterFocus) {
          binding.value.afterFocus();
        }
      }, 5);
    }
  }
  const FocusIf = {
    mounted(el, binding) {
      doFocusIf(el, binding);
    },
    updated(el, binding) {
      doFocusIf(el, binding);
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$f } = window;
  const observers = /* @__PURE__ */ new WeakMap();
  function defaultContentTransform() {
    const title = $$f(this).attr("title") || "";
    return window.vueSanitize(title.replace(/\n/g, "<br />"));
  }
  function closeOrphanedTooltips(el) {
    if (!document.querySelector(".ui-tooltip")) {
      return;
    }
    let instance2;
    try {
      instance2 = $$f(el).tooltip("instance");
    } catch (e) {
      return;
    }
    if (!instance2 || !instance2.tooltips) {
      return;
    }
    Object.keys(instance2.tooltips).forEach((id) => {
      var _a2, _b;
      const target = (_b = (_a2 = instance2 == null ? void 0 : instance2.tooltips[id]) == null ? void 0 : _a2.element) == null ? void 0 : _b[0];
      if (target && !target.isConnected) {
        $$f(target).trigger("mouseleave").trigger("focusout");
      }
    });
  }
  function setupTooltips(el, binding) {
    var _a2, _b, _c, _d, _e, _f;
    if (!el.isConnected) {
      return;
    }
    $$f(el).tooltip({
      track: true,
      content: ((_a2 = binding.value) == null ? void 0 : _a2.content) || defaultContentTransform,
      show: typeof ((_b = binding.value) == null ? void 0 : _b.show) !== "undefined" ? (_c = binding.value) == null ? void 0 : _c.show : {
        delay: ((_d = binding.value) == null ? void 0 : _d.delay) || 700,
        duration: ((_e = binding.value) == null ? void 0 : _e.duration) || 200
      },
      hide: false,
      tooltipClass: (_f = binding.value) == null ? void 0 : _f.tooltipClass
    });
    if (!observers.has(el)) {
      const observer = new MutationObserver((mutations) => {
        if (mutations.some((mutation) => mutation.removedNodes.length > 0)) {
          closeOrphanedTooltips(el);
        }
      });
      observer.observe(el, { childList: true, subtree: true });
      observers.set(el, observer);
    }
  }
  const Tooltips = {
    mounted(el, binding) {
      setTimeout(() => setupTooltips(el, binding));
    },
    updated(el, binding) {
      setTimeout(() => setupTooltips(el, binding));
    },
    beforeUnmount(el) {
      const observer = observers.get(el);
      if (observer) {
        observer.disconnect();
        observers.delete(el);
      }
      try {
        window.$(el).tooltip("destroy");
      } catch (e) {
      }
    }
  };
  const _sfc_main$I = vue.defineComponent({
    props: {
      /**
       * Whether the modal is displayed or not;
       */
      modelValue: {
        type: Boolean,
        required: true
      },
      options: {
        type: Object,
        required: false,
        default: () => ({})
      }
    },
    emits: ["yes", "no", "closeEnd", "close", "validation", "update:modelValue"],
    activated() {
      this.$emit("update:modelValue", false);
    },
    watch: {
      modelValue(newValue, oldValue) {
        if (newValue) {
          const slotElement = this.$refs.root.firstElementChild;
          Matomo.helper.modalConfirm(slotElement, {
            yes: () => {
              this.$emit("yes");
            },
            no: () => {
              this.$emit("no");
            },
            validation: () => {
              this.$emit("validation");
            }
          }, __spreadValues({
            onCloseEnd: () => {
              this.$refs.root.appendChild(slotElement);
              this.$emit("update:modelValue", false);
              this.$emit("closeEnd");
            }
          }, this.options));
        } else if (newValue === false && oldValue === true) {
          $(".modal.open").modal("close");
          this.$emit("close");
        }
      }
    }
  });
  const _hoisted_1$E = { ref: "root" };
  function _sfc_render$H(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$E, [
      vue.renderSlot(_ctx.$slots, "default")
    ], 512)), [
      [vue.vShow, _ctx.modelValue]
    ]);
  }
  const MatomoDialog = /* @__PURE__ */ _export_sfc(_sfc_main$I, [["render", _sfc_render$H]]);
  const _sfc_main$H = vue.defineComponent({
    name: "MatomoModal",
    props: {
      modelValue: {
        type: Boolean,
        required: true
      },
      // Extra classes applied to the modal root, in the same shape Vue accepts
      // for `:class`. Use this to opt into modal-specific styling.
      classes: {
        type: [String, Array, Object],
        default: ""
      },
      // Extra classes applied to the inner `.modal-content` wrapper.
      contentClass: {
        type: [String, Array, Object],
        default: ""
      },
      ariaLabel: {
        type: String
      }
    },
    emits: ["update:modelValue", "opened", "closed"],
    data() {
      return {
        previousBodyOverflow: "",
        previousFocus: null
      };
    },
    computed: {
      modalClasses() {
        return [{ open: this.modelValue }, this.classes];
      }
    },
    methods: {
      close() {
        if (!this.modelValue) {
          return;
        }
        this.$emit("update:modelValue", false);
      },
      onKeydown(event) {
        if (event.key !== "Escape") {
          return;
        }
        this.close();
      },
      activate() {
        const rootElement = this.$refs.root;
        this.previousBodyOverflow = document.body.style.overflow;
        this.previousFocus = document.activeElement;
        document.body.style.overflow = "hidden";
        document.addEventListener("keydown", this.onKeydown);
        this.$nextTick(() => rootElement.focus());
        this.$emit("opened", rootElement);
      },
      deactivate() {
        document.body.style.overflow = this.previousBodyOverflow;
        this.previousBodyOverflow = "";
        document.removeEventListener("keydown", this.onKeydown);
        if (this.previousFocus) {
          this.previousFocus.focus();
        }
        this.previousFocus = null;
        this.$emit("closed");
      }
    },
    watch: {
      modelValue(open, wasOpen) {
        if (open && !wasOpen) {
          this.activate();
        } else if (!open && wasOpen) {
          this.deactivate();
        }
      }
    },
    mounted() {
      if (this.modelValue) {
        this.activate();
      }
    },
    unmounted() {
      if (this.modelValue) {
        this.deactivate();
      }
    }
  });
  const _hoisted_1$D = ["aria-label"];
  const _hoisted_2$v = {
    key: 0,
    class: "modal-footer matomo-modal-footer"
  };
  function _sfc_render$G(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createBlock(vue.Teleport, { to: "body" }, [
      _ctx.modelValue ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        class: "modal-overlay matomo-modal-overlay open",
        onClick: _cache[0] || (_cache[0] = (...args) => _ctx.close && _ctx.close(...args))
      })) : vue.createCommentVNode("", true),
      vue.withDirectives(vue.createElementVNode("div", {
        ref: "root",
        class: vue.normalizeClass(["modal matomo-modal", _ctx.modalClasses]),
        role: "dialog",
        "aria-modal": "true",
        "aria-label": _ctx.ariaLabel,
        tabindex: "-1"
      }, [
        vue.createElementVNode("div", {
          class: vue.normalizeClass(["modal-content matomo-modal-content", _ctx.contentClass])
        }, [
          vue.renderSlot(_ctx.$slots, "default")
        ], 2),
        _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$v, [
          vue.renderSlot(_ctx.$slots, "footer")
        ])) : vue.createCommentVNode("", true)
      ], 10, _hoisted_1$D), [
        [vue.vShow, _ctx.modelValue]
      ])
    ]);
  }
  const MatomoModal = /* @__PURE__ */ _export_sfc(_sfc_main$H, [["render", _sfc_render$G]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function expand(element, binding, event) {
    var _a2;
    element.classList.add("expanded");
    if ((_a2 = binding.value) == null ? void 0 : _a2.onExpand) {
      binding.value.onExpand(event);
    }
    const positionElement = element.querySelector(".dropdown.positionInViewport");
    if (positionElement) {
      Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }
  function close(element, binding, event) {
    var _a2;
    if (!element.classList.contains("expanded")) {
      return;
    }
    element.classList.remove("expanded");
    if ((_a2 = binding.value) == null ? void 0 : _a2.onClosed) {
      binding.value.onClosed(event);
    }
  }
  function onClickOnExpander(element, binding, event) {
    if (element.classList.contains("expanded")) {
      close(element, binding, event);
    } else {
      expand(element, binding, event);
    }
  }
  function onClickOutsideElement$1(element, binding, event) {
    const hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    if (hadUsedScrollbar) {
      return;
    }
    if (!element.contains(event.target)) {
      close(element, binding, event);
    }
  }
  function onScroll(binding) {
    binding.value.hasScrolled = true;
  }
  function onMouseDown(binding) {
    binding.value.isMouseDown = true;
    binding.value.hasScrolled = false;
  }
  function onEscapeHandler$1(element, binding, event) {
    if (event.key === "Escape") {
      binding.value.isMouseDown = false;
      binding.value.hasScrolled = false;
      close(element, binding, event);
    }
  }
  const doc$1 = document.documentElement;
  const ExpandOnClick = {
    mounted(el, binding) {
      binding.value.isMouseDown = false;
      binding.value.hasScrolled = false;
      binding.value.onClickOnExpander = onClickOnExpander.bind(null, el, binding);
      binding.value.onEscapeHandler = onEscapeHandler$1.bind(null, el, binding);
      binding.value.onMouseDown = onMouseDown.bind(null, binding);
      binding.value.onClickOutsideElement = onClickOutsideElement$1.bind(null, el, binding);
      binding.value.onScroll = onScroll.bind(null, binding);
      setTimeout(() => {
        const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
        if (expander) {
          expander.addEventListener("click", binding.value.onClickOnExpander);
        }
      });
      doc$1.addEventListener("keyup", binding.value.onEscapeHandler);
      doc$1.addEventListener("mousedown", binding.value.onMouseDown);
      doc$1.addEventListener("mouseup", binding.value.onClickOutsideElement);
      doc$1.addEventListener("scroll", binding.value.onScroll);
    },
    unmounted(el, binding) {
      const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        doc$1.removeEventListener("click", binding.value.onClickOnExpander);
      }
      doc$1.removeEventListener("keyup", binding.value.onEscapeHandler);
      doc$1.removeEventListener("mousedown", binding.value.onMouseDown);
      doc$1.removeEventListener("mouseup", binding.value.onClickOutsideElement);
      doc$1.removeEventListener("scroll", binding.value.onScroll);
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function onMouseEnter(element) {
    element.classList.add("expanded");
    const positionElement = element.querySelector(".dropdown.positionInViewport");
    if (positionElement) {
      Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }
  function onMouseLeave(element) {
    element.classList.remove("expanded");
  }
  function onClickOutsideElement(element, event) {
    if (!element.contains(event.target)) {
      element.classList.remove("expanded");
    }
  }
  function onEscapeHandler(element, event) {
    if (event.which === 27) {
      element.classList.remove("expanded");
    }
  }
  const doc = document.documentElement;
  const ExpandOnHover = {
    mounted(el, binding) {
      binding.value.onMouseEnter = onMouseEnter.bind(null, el);
      binding.value.onMouseLeave = onMouseLeave.bind(null, el);
      binding.value.onClickOutsideElement = onClickOutsideElement.bind(null, el);
      binding.value.onEscapeHandler = onEscapeHandler.bind(null, el);
      setTimeout(() => {
        const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
        if (expander) {
          expander.addEventListener("mouseenter", binding.value.onMouseEnter);
        }
      });
      el.addEventListener("mouseleave", binding.value.onMouseLeave);
      doc.addEventListener("keyup", binding.value.onEscapeHandler);
      doc.addEventListener("mouseup", binding.value.onClickOutsideElement);
    },
    unmounted(el, binding) {
      const expander = DirectiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        expander.removeEventListener("mouseenter", binding.value.onMouseEnter);
      }
      el.removeEventListener("mouseleave", binding.value.onMouseLeave);
      document.removeEventListener("keyup", binding.value.onEscapeHandler);
      document.removeEventListener("mouseup", binding.value.onClickOutsideElement);
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$e } = window;
  const ShowSensitiveData = {
    mounted(el, binding) {
      const element = $$e(el);
      const { sensitiveData } = binding.value;
      const showCharacters = binding.value.showCharacters || 6;
      const clickElement = binding.value.clickElementSelector || element;
      let protectedData = "";
      if (showCharacters > 0) {
        protectedData += sensitiveData.slice(0, showCharacters);
      }
      protectedData += sensitiveData.slice(showCharacters).replace(/./g, "*");
      element.html(protectedData);
      function onClickHandler2() {
        element.html(sensitiveData);
        $$e(clickElement).css({
          cursor: ""
        });
        $$e(clickElement).tooltip("destroy");
      }
      $$e(clickElement).tooltip({
        content: translate("CoreHome_ClickToSeeFullInformation"),
        items: "*",
        track: true
      });
      $$e(clickElement).one("click", onClickHandler2);
      $$e(clickElement).css({
        cursor: "pointer"
      });
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$d } = window;
  const DropdownButton = {
    mounted(el) {
      const element = $$d(el);
      if (!element.attr("data-target") && element.attr("data-activates")) {
        element.attr("data-target", element.attr("data-activates"));
      }
      const target = element.attr("data-target");
      if (target && $$d(`#${target}`).length) {
        element.dropdown({
          // eslint-disable-line
          inDuration: 300,
          outDuration: 225,
          constrainWidth: false,
          // Does not change width of dropdown to that of the activator
          //  hover: true, // Activate on hover
          belowOrigin: true
          // Displays dropdown below the button
        });
      }
    }
  };
  const _hoisted_1$C = ["data-item-id", "draggable", "aria-grabbed", "onDragstart", "onDragover"];
  const SORT_TRIGGER_OFFSET = 0.1;
  const _sfc_main$G = /* @__PURE__ */ vue.defineComponent({
    __name: "DraggableList",
    props: {
      items: {},
      itemKey: {},
      disabled: { type: Boolean, default: false },
      handle: { default: "" },
      axis: { default: "y" }
    },
    emits: ["reorder"],
    setup(__props, { emit: __emit }) {
      const props = __props;
      const emit = __emit;
      const orderedItems = vue.ref([]);
      const draggedId = vue.ref(null);
      const dragTargetId = vue.ref(null);
      const placeholderId = vue.ref(null);
      const dropSucceeded = vue.ref(false);
      const canDrag = vue.computed(() => !props.disabled && props.items.length > 1);
      function getItemKey(item, index) {
        if (typeof props.itemKey === "function") return props.itemKey(item, index);
        if (!item || typeof item !== "object") return index;
        const value = item[props.itemKey];
        if (typeof value === "string" || typeof value === "number") return value;
        return index;
      }
      const sourceItems = vue.computed(() => props.items.map((item, index) => ({
        id: String(getItemKey(item, index)),
        item,
        sourceIndex: index
      })));
      const itemKeySignature = vue.computed(() => sourceItems.value.map((entry) => entry.id).join("\0"));
      function syncOrderedItems() {
        orderedItems.value = sourceItems.value.slice();
      }
      function clearDragVisualState() {
        draggedId.value = null;
        dragTargetId.value = null;
        placeholderId.value = null;
      }
      function resetDragState(shouldSync = false) {
        clearDragVisualState();
        dropSucceeded.value = false;
        if (shouldSync) syncOrderedItems();
      }
      function matchesHandle(target, currentTarget) {
        if (!props.handle) return true;
        if (!(target instanceof Element)) return false;
        const handleElement = target.closest(props.handle);
        return !!handleElement && currentTarget.contains(handleElement);
      }
      function getOrderedIndex(itemId) {
        return orderedItems.value.findIndex((entry) => entry.id === itemId);
      }
      function getOrderedItemAt(index) {
        return orderedItems.value[typeof index === "number" ? index : Number(index)];
      }
      function getDropPosition(event, element) {
        const rect = element.getBoundingClientRect();
        const draggedIndex = draggedId.value ? getOrderedIndex(draggedId.value) : -1;
        const hoveredIndex = dragTargetId.value ? getOrderedIndex(dragTargetId.value) : -1;
        const isMovingForward = draggedIndex !== -1 && hoveredIndex !== -1 && draggedIndex < hoveredIndex;
        const triggerOffset = isMovingForward ? SORT_TRIGGER_OFFSET : 1 - SORT_TRIGGER_OFFSET;
        if (props.axis === "x") {
          return event.clientX < rect.left + rect.width * triggerOffset ? "before" : "after";
        }
        return event.clientY < rect.top + rect.height * triggerOffset ? "before" : "after";
      }
      function moveDraggedItem(targetId, position) {
        if (!draggedId.value || draggedId.value === targetId) return;
        const currentIndex = getOrderedIndex(draggedId.value);
        const targetIndex = getOrderedIndex(targetId);
        if (currentIndex === -1 || targetIndex === -1) return;
        let insertionIndex = targetIndex + (position === "after" ? 1 : 0);
        if (currentIndex < insertionIndex) {
          insertionIndex -= 1;
        }
        if (insertionIndex === currentIndex) return;
        const nextItems = orderedItems.value.slice();
        const [movedItem] = nextItems.splice(currentIndex, 1);
        nextItems.splice(insertionIndex, 0, movedItem);
        orderedItems.value = nextItems;
      }
      function onDragStart(event, itemId) {
        const itemElement = event.currentTarget;
        if (!itemElement || !canDrag.value || !matchesHandle(event.target, itemElement)) {
          event.preventDefault();
          return;
        }
        draggedId.value = itemId;
        dragTargetId.value = itemId;
        dropSucceeded.value = false;
        placeholderId.value = null;
        if (event.dataTransfer) {
          event.dataTransfer.effectAllowed = "move";
          event.dataTransfer.setData("text/plain", itemId);
        }
        window.setTimeout(() => {
          if (draggedId.value === itemId) placeholderId.value = itemId;
        }, 0);
      }
      function onDragStartForIndex(event, index) {
        const orderedItem = getOrderedItemAt(index);
        if (!orderedItem) {
          event.preventDefault();
          return;
        }
        onDragStart(event, orderedItem.id);
      }
      function onDragOver(event, itemId) {
        if (!draggedId.value || !canDrag.value) return;
        event.preventDefault();
        const itemElement = event.currentTarget;
        if (!itemElement) return;
        dragTargetId.value = itemId;
        moveDraggedItem(itemId, getDropPosition(event, itemElement));
        if (event.dataTransfer) event.dataTransfer.dropEffect = "move";
      }
      function onDragOverForIndex(event, index) {
        const orderedItem = getOrderedItemAt(index);
        if (!orderedItem) return;
        onDragOver(event, orderedItem.id);
      }
      function onDrop(event) {
        if (!draggedId.value) return;
        event.preventDefault();
        const reorderedIds = orderedItems.value.map((entry) => entry.id);
        if (reorderedIds && reorderedIds.join("\0") !== itemKeySignature.value) {
          dropSucceeded.value = true;
          emit("reorder", reorderedIds);
        }
        clearDragVisualState();
      }
      function onDragEnd() {
        if (dropSucceeded.value) {
          dropSucceeded.value = false;
          return;
        }
        resetDragState(true);
      }
      vue.watch([sourceItems, () => props.disabled], () => resetDragState(true), { immediate: true });
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createElementBlock("ul", {
          class: vue.normalizeClass(["draggableList", {
            isDragging: draggedId.value !== null,
            isDisabled: __props.disabled
          }])
        }, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(orderedItems.value, (orderedItem, index) => {
            return vue.openBlock(), vue.createElementBlock("li", {
              key: orderedItem.id,
              class: vue.normalizeClass(["draggableListItem", { isDragged: orderedItem.id === placeholderId.value }]),
              "data-item-id": orderedItem.id,
              draggable: canDrag.value,
              "aria-grabbed": orderedItem.id === draggedId.value,
              onDragstart: ($event) => onDragStartForIndex($event, index),
              onDragover: ($event) => onDragOverForIndex($event, index),
              onDrop,
              onDragend: onDragEnd
            }, [
              vue.renderSlot(_ctx.$slots, "default", {
                item: orderedItem.item,
                index: orderedItem.sourceIndex
              })
            ], 42, _hoisted_1$C);
          }), 128))
        ], 2);
      };
    }
  });
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$c } = window;
  function onFocusHandler(binding, event) {
    if (binding.value.focusedElement !== event.target) {
      binding.value.focusedElement = event.target;
      $$c(event.target).select();
    }
  }
  function onClickHandler$1(event) {
    const range = document.createRange();
    range.selectNode(event.target);
    const selection = window.getSelection();
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
  const SelectOnFocus = {
    mounted(el, binding) {
      const tagName = el.tagName.toLowerCase();
      binding.value.elementSupportsSelect = tagName === "textarea";
      if (binding.value.elementSupportsSelect) {
        binding.value.onFocusHandler = onFocusHandler.bind(null, binding);
        binding.value.onBlurHandler = onBlurHandler.bind(null, binding);
        el.addEventListener("focus", binding.value.onFocusHandler);
        el.addEventListener("blur", binding.value.onBlurHandler);
      } else {
        binding.value.onClickHandler = onClickHandler$1;
        el.addEventListener("click", binding.value.onClickHandler);
      }
    },
    unmounted(el, binding) {
      if (binding.value.elementSupportsSelect) {
        el.removeEventListener("focus", binding.value.onFocusHandler);
        el.removeEventListener("blur", binding.value.onBlurHandler);
      } else {
        el.removeEventListener("click", binding.value.onClickHandler);
      }
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function onClickHandler(pre) {
    if (pre) {
      const textarea = document.createElement("textarea");
      textarea.value = pre.innerText;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "absolute";
      textarea.style.left = "-9999px";
      document.body.appendChild(textarea);
      textarea.select();
      textarea.focus();
      document.execCommand("copy");
      document.body.removeChild(textarea);
      const btn = pre.parentElement;
      if (btn) {
        const icon = btn.getElementsByTagName("i")[0];
        if (icon) {
          icon.classList.remove("copyToClipboardIcon");
          icon.classList.add("copyToClipboardIconCheck");
        }
        const copied = btn.getElementsByClassName("copyToClipboardCopiedDiv")[0];
        if (copied) {
          copied.style.display = "inline-block";
          setTimeout(() => {
            copied.style.display = "none";
          }, 2500);
        }
      }
    }
  }
  function onTransitionEndHandler(el, binding) {
    if (binding.value.transitionOpen) {
      const btn = el.parentElement;
      if (btn) {
        const icon = btn.getElementsByTagName("i")[0];
        if (icon) {
          icon.classList.remove("copyToClipboardIconCheck");
          icon.classList.add("copyToClipboardIcon");
        }
      }
      binding.value.transitionOpen = false;
    } else {
      binding.value.transitionOpen = true;
    }
  }
  const CopyToClipboard = {
    mounted(el, binding) {
      const tagName = el.tagName.toLowerCase();
      if (tagName === "pre") {
        const btn = document.createElement("button");
        btn.setAttribute("type", "button");
        btn.className = "copyToClipboardButton";
        const positionDiv = document.createElement("div");
        positionDiv.className = "copyToClipboardPositionDiv";
        const icon = document.createElement("i");
        icon.className = "copyToClipboardIcon";
        btn.appendChild(icon);
        const sp = document.createElement("span");
        sp.className = "copyToClipboardSpan";
        sp.innerHTML = translate("General_Copy");
        btn.appendChild(sp);
        positionDiv.appendChild(btn);
        const cdiv = document.createElement("div");
        cdiv.className = "copyToClipboardCopiedDiv";
        cdiv.innerHTML = translate("General_CopiedToClipboard");
        positionDiv.appendChild(cdiv);
        const pe = el.parentElement;
        if (pe) {
          pe.classList.add("copyToClipboardWrapper");
          pe.appendChild(positionDiv);
        }
        binding.value.onClickHandler = onClickHandler.bind(null, el);
        btn.addEventListener("click", binding.value.onClickHandler);
        binding.value.onTransitionEndHandler = onTransitionEndHandler.bind(null, el, binding);
        btn.addEventListener("transitionend", binding.value.onTransitionEndHandler);
      }
    },
    unmounted(el, binding) {
      el.removeEventListener("click", binding.value.onClickHandler);
      el.removeEventListener("transitionend", binding.value.onTransitionEndHandler);
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function openMobileLeftMenu() {
    const mobileLeftMenu = document.getElementById("mobile-left-menu");
    if (!mobileLeftMenu) {
      return;
    }
    try {
      window.$(mobileLeftMenu).sidenav("open");
    } catch (e) {
    }
  }
  function closeMobileLeftMenu() {
    const secondNavBar = document.getElementById("secondNavBar");
    if (!(secondNavBar == null ? void 0 : secondNavBar.classList.contains("mobileLeftMenuOpen"))) {
      return;
    }
    const mobileLeftMenu = document.getElementById("mobile-left-menu");
    if (!mobileLeftMenu) {
      return;
    }
    try {
      window.$(mobileLeftMenu).sidenav("close");
    } catch (e) {
    }
  }
  const SideNav = {
    mounted(el, binding) {
      if (!binding.value.activator) {
        return;
      }
      const secondNavBar = document.getElementById("secondNavBar");
      const setSecondNavBarMenuState = (isOpen) => {
        if (secondNavBar) {
          secondNavBar.classList.toggle("mobileLeftMenuOpen", isOpen);
        }
      };
      setTimeout(() => {
        if (!binding.value.initialized) {
          binding.value.initialized = true;
          const sideNavActivator = DirectiveUtilities.getRef(binding.value.activator, binding);
          if (sideNavActivator) {
            window.$(sideNavActivator).show();
            const targetSelector = sideNavActivator.getAttribute("data-target");
            window.$(`#${targetSelector}`).sidenav({
              closeOnClick: true,
              onOpenStart: () => {
                setSecondNavBarMenuState(true);
              },
              onCloseStart: () => {
                setSecondNavBarMenuState(false);
              }
            });
          }
        }
        if (el.classList.contains("collapsible")) {
          window.$(el).collapsible();
        }
      });
    }
  };
  const _sfc_main$F = vue.defineComponent({
    props: {
      helpUrl: {
        type: String,
        default: ""
      },
      editUrl: {
        type: String,
        default: ""
      },
      reportGenerated: String,
      featureName: String,
      inlineHelp: String
    },
    data() {
      return {
        showIcons: false,
        showInlineHelp: false,
        actualFeatureName: this.featureName,
        actualInlineHelp: this.inlineHelp
      };
    },
    watch: {
      inlineHelp(newValue) {
        this.actualInlineHelp = newValue;
      },
      featureName(newValue) {
        this.actualFeatureName = newValue;
      }
    },
    mounted() {
      var _a2, _b;
      const root = this.$refs.root;
      if (!this.actualInlineHelp) {
        const inlineHelpNode = root.querySelector(".title .inlineHelp");
        if (inlineHelpNode) {
          const helpDocs = (_a2 = inlineHelpNode.getAttribute("data-content")) == null ? void 0 : _a2.trim();
          if (helpDocs && helpDocs.length) {
            this.actualInlineHelp = `<p>${helpDocs}</p>`;
            setTimeout(() => inlineHelpNode.remove(), 0);
          }
        } else {
          this.actualInlineHelp = this.readReportDocumentation();
        }
      }
      (_b = root.parentElement) == null ? void 0 : _b.addEventListener("piwik:reportChanged", this.onReportChanged);
      if (!this.actualFeatureName) {
        this.actualFeatureName = this.readReportFeatureName();
      }
      if (Matomo.period && Matomo.currentDateString) {
        const currentPeriod = Periods$1.parse(
          Matomo.period,
          Matomo.currentDateString
        );
        if (this.reportGenerated && currentPeriod.containsToday()) {
          window.$(root.querySelector(".report-generated")).tooltip({
            track: true,
            content: this.reportGenerated,
            items: "div",
            show: false,
            hide: false
          });
        }
      }
    },
    beforeUnmount() {
      var _a2;
      const root = this.$refs.root;
      (_a2 = root == null ? void 0 : root.parentElement) == null ? void 0 : _a2.removeEventListener("piwik:reportChanged", this.onReportChanged);
    },
    methods: {
      // Expose the plugin component to `<component :is>` as a plain Component.
      asComponent(component) {
        return component;
      },
      htmlEntities(v) {
        return Matomo.helper.htmlEntities(v);
      },
      onReportChanged() {
        this.actualInlineHelp = this.readReportDocumentation();
        const featureName = this.readReportFeatureName();
        if (featureName) {
          this.actualFeatureName = featureName;
        }
        if (!this.actualInlineHelp) {
          this.showInlineHelp = false;
        }
      },
      readReportFeatureName() {
        var _a2, _b;
        const root = this.$refs.root;
        return ((_b = (_a2 = root == null ? void 0 : root.querySelector(".title")) == null ? void 0 : _a2.textContent) == null ? void 0 : _b.trim()) || "";
      },
      readReportDocumentation() {
        var _a2, _b, _c, _d;
        const root = this.$refs.root;
        const helpDocs = (_d = (_c = (_b = (_a2 = root == null ? void 0 : root.parentElement) == null ? void 0 : _a2.nextElementSibling) == null ? void 0 : _b.querySelector(".reportDocumentation")) == null ? void 0 : _c.getAttribute("data-content")) == null ? void 0 : _d.trim();
        return helpDocs && helpDocs.length ? `<p>${helpDocs}</p>` : "";
      }
    },
    computed: {
      showRateFeature() {
        return translateOrDefault("Feedback_SendFeedback") !== "Feedback_SendFeedback";
      },
      rateFeature() {
        if (this.showRateFeature) {
          return useExternalPluginComponent("Feedback", "RateFeature");
        }
        return "";
      }
    }
  });
  const _hoisted_1$B = {
    key: 0,
    class: "title",
    tabindex: "6"
  };
  const _hoisted_2$u = ["href", "title"];
  const _hoisted_3$r = { class: "iconsBar" };
  const _hoisted_4$m = ["href", "title"];
  const _hoisted_5$k = ["title"];
  const _hoisted_6$h = {
    key: 2,
    class: "ratingIcons"
  };
  const _hoisted_7$c = { class: "inlineHelp" };
  const _hoisted_8$a = ["innerHTML"];
  const _hoisted_9$8 = ["innerHTML"];
  const _hoisted_10$6 = ["href"];
  function _sfc_render$F(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "enrichedHeadline",
      onMouseenter: _cache[1] || (_cache[1] = ($event) => _ctx.showIcons = true),
      onMouseleave: _cache[2] || (_cache[2] = ($event) => _ctx.showIcons = false),
      ref: "root"
    }, [
      !_ctx.editUrl ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$B, [
        vue.renderSlot(_ctx.$slots, "default")
      ])) : vue.createCommentVNode("", true),
      _ctx.editUrl ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 1,
        class: "title",
        href: _ctx.editUrl,
        title: _ctx.translate("CoreHome_ClickToEditX", _ctx.htmlEntities(_ctx.actualFeatureName || ""))
      }, [
        vue.renderSlot(_ctx.$slots, "default")
      ], 8, _hoisted_2$u)) : vue.createCommentVNode("", true),
      vue.withDirectives(vue.createElementVNode("span", _hoisted_3$r, [
        _ctx.helpUrl && !_ctx.actualInlineHelp ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          rel: "noreferrer noopener",
          target: "_blank",
          class: "helpIcon",
          href: _ctx.helpUrl,
          title: _ctx.translate("CoreHome_ExternalHelp")
        }, [..._cache[3] || (_cache[3] = [
          vue.createElementVNode("span", { class: "icon-help" }, null, -1)
        ])], 8, _hoisted_4$m)) : vue.createCommentVNode("", true),
        _ctx.actualInlineHelp ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 1,
          onClick: _cache[0] || (_cache[0] = ($event) => _ctx.showInlineHelp = !_ctx.showInlineHelp),
          class: vue.normalizeClass(["helpIcon", { "active": _ctx.showInlineHelp }]),
          title: _ctx.translate(_ctx.reportGenerated ? "General_HelpReport" : "General_Help")
        }, [..._cache[4] || (_cache[4] = [
          vue.createElementVNode("span", { class: "icon-info" }, null, -1)
        ])], 10, _hoisted_5$k)) : vue.createCommentVNode("", true),
        _ctx.showRateFeature ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$h, [
          (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.asComponent(_ctx.rateFeature)), { title: _ctx.actualFeatureName }, null, 8, ["title"]))
        ])) : vue.createCommentVNode("", true)
      ], 512), [
        [vue.vShow, _ctx.showIcons || _ctx.showInlineHelp]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_7$c, [
        vue.createElementVNode("div", {
          innerHTML: _ctx.$sanitize(_ctx.actualInlineHelp)
        }, null, 8, _hoisted_8$a),
        _ctx.reportGenerated != "" ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "helpDate",
          innerHTML: _ctx.$sanitize(_ctx.reportGenerated)
        }, null, 8, _hoisted_9$8)) : vue.createCommentVNode("", true),
        _ctx.helpUrl ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 1,
          rel: "noreferrer noopener",
          target: "_blank",
          class: "readMore",
          href: _ctx.helpUrl
        }, vue.toDisplayString(_ctx.translate("General_MoreDetails")), 9, _hoisted_10$6)) : vue.createCommentVNode("", true)
      ], 512), [
        [vue.vShow, _ctx.showInlineHelp]
      ])
    ], 544);
  }
  const EnrichedHeadline = /* @__PURE__ */ _export_sfc(_sfc_main$F, [["render", _sfc_render$F]]);
  let adminContent = null;
  const { $: $$b } = window;
  const _sfc_main$E = vue.defineComponent({
    props: {
      contentTitle: String,
      feature: String,
      helpUrl: String,
      editUrl: String,
      helpText: String,
      anchor: String,
      imageUrl: String,
      imageAltText: String
    },
    components: {
      EnrichedHeadline
    },
    data() {
      return {
        actualFeature: this.feature,
        actualHelpText: this.helpText,
        actualImageAltText: this.imageAltText ? this.imageAltText : this.contentTitle
      };
    },
    watch: {
      feature(newValue) {
        this.actualFeature = newValue;
      },
      helpText(newValue) {
        this.actualHelpText = newValue;
      }
    },
    mounted() {
      const root = this.$refs.root;
      const content = this.$refs.content;
      if (this.anchor && root && root.parentElement) {
        const anchorElement = document.createElement("a");
        anchorElement.id = this.anchor;
        $$b(root.parentElement).prepend(anchorElement);
      }
      setTimeout(() => {
        const inlineHelp = content.querySelector(".contentHelp");
        if (inlineHelp) {
          this.actualHelpText = inlineHelp.innerHTML;
          inlineHelp.remove();
        }
      }, 0);
      if (this.actualFeature && this.actualFeature === "true") {
        this.actualFeature = this.contentTitle;
      }
      if (adminContent === null) {
        adminContent = document.querySelector("#content.admin");
      }
      let contentTopPosition = null;
      if (adminContent) {
        contentTopPosition = adminContent.offsetTop;
      }
      if (contentTopPosition || contentTopPosition === 0) {
        const parents = root.closest(".widgetLoader");
        const topThis = parents ? parents.offsetTop : root.offsetTop;
        if (topThis - contentTopPosition < 17) {
          root.style.marginTop = "0";
        }
      }
    },
    methods: {
      decode(s) {
        return Matomo.helper.htmlDecode(s);
      }
    }
  });
  const _hoisted_1$A = { class: "card-content" };
  const _hoisted_2$t = {
    key: 0,
    class: "card-title"
  };
  const _hoisted_3$q = {
    key: 1,
    class: "card-title"
  };
  const _hoisted_4$l = { ref: "content" };
  const _hoisted_5$j = {
    key: 0,
    class: "card-image hide-on-med-and-down"
  };
  const _hoisted_6$g = ["src", "alt"];
  function _sfc_render$E(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass({ card: true, "card-with-image": !!_ctx.imageUrl }),
      ref: "root"
    }, [
      vue.createElementVNode("div", _hoisted_1$A, [
        _ctx.contentTitle && !_ctx.actualFeature && !_ctx.helpUrl && !_ctx.actualHelpText && !_ctx.editUrl ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_2$t, vue.toDisplayString(_ctx.decode(_ctx.contentTitle)), 1)) : vue.createCommentVNode("", true),
        _ctx.contentTitle && (_ctx.actualFeature || _ctx.helpUrl || _ctx.actualHelpText || _ctx.editUrl) ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_3$q, [
          vue.createVNode(_component_EnrichedHeadline, {
            "feature-name": _ctx.actualFeature,
            "help-url": _ctx.helpUrl,
            "edit-url": _ctx.editUrl,
            "inline-help": _ctx.actualHelpText
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.decode(_ctx.contentTitle)), 1)
            ]),
            _: 1
          }, 8, ["feature-name", "help-url", "edit-url", "inline-help"])
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_4$l, [
          vue.renderSlot(_ctx.$slots, "default")
        ], 512)
      ]),
      _ctx.imageUrl ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$j, [
        vue.createElementVNode("img", {
          src: _ctx.imageUrl,
          alt: _ctx.actualImageAltText
        }, null, 8, _hoisted_6$g)
      ])) : vue.createCommentVNode("", true)
    ], 2);
  }
  const ContentBlock = /* @__PURE__ */ _export_sfc(_sfc_main$E, [["render", _sfc_render$E]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class SegmentsStore {
    constructor() {
      __publicField(this, "segmentState", vue.reactive({
        availableSegments: []
      }));
      Matomo.on("piwikSegmentationInited", () => this.setSegmentState());
    }
    get state() {
      return vue.readonly(this.segmentState);
    }
    setSegmentState() {
      try {
        const uiControlObject = $(".segmentEditorPanel").data("uiControlObject");
        this.segmentState.availableSegments = uiControlObject.impl.availableSegments || [];
      } catch (e) {
      }
    }
  }
  const SegmentsStore$1 = new SegmentsStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const SERIES_COLOR_COUNT = 8;
  const SERIES_SHADE_COUNT = 3;
  function wrapArray(values) {
    if (!values) {
      return [];
    }
    return Array.isArray(values) ? values : [values];
  }
  function normalizeUrlState(value) {
    if (Array.isArray(value)) {
      return value.map(normalizeUrlState);
    }
    if (value && typeof value === "object") {
      return Object.fromEntries(
        Object.entries(value).sort(([a], [b]) => a.localeCompare(b)).map(([key, nestedValue]) => [key, normalizeUrlState(nestedValue)])
      );
    }
    return value;
  }
  class ComparisonsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        comparisonsDisabledFor: []
      }));
      __publicField(this, "state", vue.readonly(this.privateState));
      // for tests
      __publicField(this, "colors", {});
      __publicField(this, "segmentComparisons", vue.computed(() => this.parseSegmentComparisons()));
      __publicField(this, "periodComparisons", vue.computed(() => this.parsePeriodComparisons()));
      __publicField(this, "isEnabled", vue.computed(() => this.checkEnabledForCurrentPage()));
      if (document.readyState === "complete" || document.readyState === "interactive") {
        this.loadComparisonsDisabledFor();
      } else {
        document.addEventListener("DOMContentLoaded", () => {
          this.loadComparisonsDisabledFor();
        });
      }
      $(() => {
        this.colors = this.getAllSeriesColors();
      });
      vue.watch(
        () => this.getUrlStateWithoutPopoverKey(),
        () => Matomo.postEvent("piwikComparisonsChanged")
      );
    }
    getUrlStateWithoutPopoverKey() {
      const parsedWithoutPopover = Object.fromEntries(
        Object.entries(instance$1.parsed.value).filter(([key]) => key !== "popover")
      );
      return JSON.stringify(normalizeUrlState(parsedWithoutPopover));
    }
    getComparisons() {
      return this.getSegmentComparisons().concat(this.getPeriodComparisons());
    }
    isComparing() {
      return this.isComparisonEnabled() && (this.segmentComparisons.value.length > 1 || this.periodComparisons.value.length > 1);
    }
    isComparingPeriods() {
      return this.getPeriodComparisons().length > 1;
    }
    getSegmentComparisons() {
      if (!this.isComparisonEnabled()) {
        return [];
      }
      return this.segmentComparisons.value;
    }
    getPeriodComparisons() {
      if (!this.isComparisonEnabled()) {
        return [];
      }
      return this.periodComparisons.value;
    }
    getSeriesColor(segmentComparison, periodComparison, metricIndex = 0) {
      const seriesIndex = this.getComparisonSeriesIndex(
        periodComparison.index,
        segmentComparison.index
      ) % SERIES_COLOR_COUNT;
      if (metricIndex === 0) {
        return this.colors[`series${seriesIndex}`];
      }
      const shadeIndex = metricIndex % SERIES_SHADE_COUNT;
      return this.colors[`series${seriesIndex}-shade${shadeIndex}`];
    }
    getSeriesColorName(seriesIndex, metricIndex) {
      let colorName = `series${seriesIndex % SERIES_COLOR_COUNT}`;
      if (metricIndex > 0) {
        colorName += `-shade${metricIndex % SERIES_SHADE_COUNT}`;
      }
      return colorName;
    }
    isComparisonEnabled() {
      return this.isEnabled.value;
    }
    getIndividualComparisonRowIndices(seriesIndex) {
      const segmentCount = this.getSegmentComparisons().length;
      const segmentIndex = seriesIndex % segmentCount;
      const periodIndex = Math.floor(seriesIndex / segmentCount);
      return {
        segmentIndex,
        periodIndex
      };
    }
    getComparisonSeriesIndex(periodIndex, segmentIndex) {
      const segmentCount = this.getSegmentComparisons().length;
      return periodIndex * segmentCount + segmentIndex;
    }
    getAllComparisonSeries() {
      const seriesInfo = [];
      let seriesIndex = 0;
      this.getPeriodComparisons().forEach((periodComp) => {
        this.getSegmentComparisons().forEach((segmentComp) => {
          seriesInfo.push({
            index: seriesIndex,
            params: __spreadValues(__spreadValues({}, segmentComp.params), periodComp.params),
            color: this.colors[`series${seriesIndex}`]
          });
          seriesIndex += 1;
        });
      });
      return seriesInfo;
    }
    removeSegmentComparison(index) {
      if (!this.isComparisonEnabled()) {
        throw new Error("Comparison disabled.");
      }
      const newComparisons = [...this.segmentComparisons.value];
      newComparisons.splice(index, 1);
      const extraParams = {};
      if (index === 0) {
        extraParams.segment = newComparisons[0].params.segment;
      }
      this.updateQueryParamsFromComparisons(
        newComparisons,
        this.periodComparisons.value,
        extraParams
      );
    }
    removeSegmentComparisonByDefinition(segmentDefinition) {
      if (!this.isComparisonEnabled()) {
        throw new Error("Comparison disabled.");
      }
      let segmentIndex = null;
      this.getSegmentComparisons().forEach((segment, index) => {
        if (segment && segment.params && segment.params.segment === segmentDefinition) {
          segmentIndex = index;
        }
      });
      if (segmentIndex !== null) {
        this.removeSegmentComparison(segmentIndex);
      }
    }
    addSegmentComparison(params) {
      if (!this.isComparisonEnabled()) {
        throw new Error("Comparison disabled.");
      }
      const newComparisons = this.segmentComparisons.value.concat([{ params, index: -1, title: "" }]);
      this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value);
    }
    updateQueryParamsFromComparisons(segmentComparisons, periodComparisons, extraParams = {}) {
      const compareSegments = {};
      const comparePeriodDatePairs = {};
      let firstSegment = false;
      let firstPeriod = false;
      segmentComparisons.forEach((comparison) => {
        if (firstSegment) {
          compareSegments[comparison.params.segment] = true;
        } else {
          firstSegment = true;
        }
      });
      periodComparisons.forEach((comparison) => {
        if (firstPeriod) {
          comparePeriodDatePairs[`${comparison.params.period}|${comparison.params.date}`] = true;
        } else {
          firstPeriod = true;
        }
      });
      const comparePeriods = [];
      const compareDates = [];
      Object.keys(comparePeriodDatePairs).forEach((pair) => {
        const parts = pair.split("|");
        comparePeriods.push(parts[0]);
        compareDates.push(parts[1]);
      });
      const compareParams = {
        compareSegments: Object.keys(compareSegments),
        comparePeriods,
        compareDates
      };
      const baseParams = Matomo.helper.isReportingPage() ? instance$1.hashParsed.value : instance$1.urlParsed.value;
      instance$1.updateLocation(__spreadValues(__spreadValues(__spreadValues({}, baseParams), compareParams), extraParams));
    }
    getAllSeriesColors() {
      const { ColorManager } = Matomo;
      if (!ColorManager) {
        return [];
      }
      const seriesColorNames = [];
      for (let i = 0; i < SERIES_COLOR_COUNT; i += 1) {
        seriesColorNames.push(`series${i}`);
        for (let j = 0; j < SERIES_SHADE_COUNT; j += 1) {
          seriesColorNames.push(`series${i}-shade${j}`);
        }
      }
      return ColorManager.getColors("comparison-series-color", seriesColorNames);
    }
    loadComparisonsDisabledFor() {
      const matomoModule = instance$1.parsed.value.module;
      if (matomoModule === "CoreUpdater" || matomoModule === "Installation" || matomoModule === "Overlay" || window.piwik.isPagesComparisonApiDisabled || window.piwik.installation || window.broadcast.isLoginPage()) {
        this.privateState.comparisonsDisabledFor = [];
        return;
      }
      AjaxHelper.fetch({
        module: "API",
        method: "API.getPagesComparisonsDisabledFor"
      }).then((result) => {
        this.privateState.comparisonsDisabledFor = result;
      });
    }
    parseSegmentComparisons() {
      const { availableSegments } = SegmentsStore$1.state;
      const compareSegments = [
        ...wrapArray(instance$1.parsed.value.compareSegments)
      ];
      compareSegments.unshift(instance$1.parsed.value.segment || "");
      const newSegmentComparisons = [];
      compareSegments.forEach((segment, idx) => {
        let storedSegment;
        availableSegments.forEach((s) => {
          if (s.definition === segment || s.definition === decodeURIComponent(segment) || decodeURIComponent(s.definition) === segment) {
            storedSegment = s;
          }
        });
        let segmentTitle = storedSegment ? storedSegment.name : translate("General_Unknown");
        if (segment.trim() === "") {
          segmentTitle = translate("SegmentEditor_DefaultAllVisits");
        }
        newSegmentComparisons.push({
          params: {
            segment
          },
          title: Matomo.helper.htmlDecode(segmentTitle),
          index: idx
        });
      });
      return newSegmentComparisons;
    }
    parsePeriodComparisons() {
      const comparePeriods = [
        ...wrapArray(instance$1.parsed.value.comparePeriods)
      ];
      const compareDates = [
        ...wrapArray(instance$1.parsed.value.compareDates)
      ];
      comparePeriods.unshift(instance$1.parsed.value.period);
      compareDates.unshift(instance$1.parsed.value.date);
      const newPeriodComparisons = [];
      for (let i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
        let title;
        try {
          title = Periods$1.parse(comparePeriods[i], compareDates[i]).getPrettyString();
        } catch (e) {
          title = translate("General_Error");
        }
        newPeriodComparisons.push({
          params: {
            date: compareDates[i],
            period: comparePeriods[i]
          },
          title,
          index: i
        });
      }
      return newPeriodComparisons;
    }
    checkEnabledForCurrentPage() {
      const category = instance$1.parsed.value.category || instance$1.parsed.value.module;
      const subcategory = instance$1.parsed.value.subcategory || instance$1.parsed.value.action;
      const id = `${category}.${subcategory}`;
      const isEnabled = this.privateState.comparisonsDisabledFor.indexOf(id) === -1 && this.privateState.comparisonsDisabledFor.indexOf(`${category}.*`) === -1;
      document.documentElement.classList.toggle("comparisonsDisabled", !isEnabled);
      return isEnabled;
    }
  }
  const ComparisonsStoreInstance = new ComparisonsStore();
  const _sfc_main$D = vue.defineComponent({
    props: {},
    components: {
      MatomoLoader
    },
    directives: {
      Tooltips
    },
    data() {
      return {
        comparisonTooltips: null
      };
    },
    setup() {
      const isComparing = vue.computed(
        () => ComparisonsStoreInstance.isComparing() && !window.broadcast.isNoDataPage()
      );
      const segmentComparisons = vue.computed(() => ComparisonsStoreInstance.getSegmentComparisons());
      const periodComparisons = vue.computed(() => ComparisonsStoreInstance.getPeriodComparisons());
      const getSeriesColor = ComparisonsStoreInstance.getSeriesColor.bind(ComparisonsStoreInstance);
      function transformTooltipContent() {
        const title = window.$(this).attr("title");
        if (!title) {
          return title;
        }
        return window.vueSanitize(title.replace(/\n/g, "<br />"));
      }
      return {
        isComparing,
        segmentComparisons,
        periodComparisons,
        getSeriesColor,
        transformTooltipContent
      };
    },
    methods: {
      comparisonHasSegment(comparison) {
        return typeof comparison.params.segment !== "undefined";
      },
      removeSegmentComparison(index) {
        window.$(this.$refs.root).tooltip("destroy");
        ComparisonsStoreInstance.removeSegmentComparison(index);
      },
      getComparisonPeriodType(comparison) {
        const { period } = comparison.params;
        if (period === "range") {
          return translate("CoreHome_PeriodRange");
        }
        const periodStr = translate(
          `Intl_Period${period.substring(0, 1).toUpperCase()}${period.substring(1)}`
        );
        return periodStr.substring(0, 1).toUpperCase() + periodStr.substring(1);
      },
      getComparisonTooltip(segmentComparison, periodComparison) {
        if (!this.comparisonTooltips || !Object.keys(this.comparisonTooltips).length) {
          return void 0;
        }
        return (this.comparisonTooltips[periodComparison.index] || {})[segmentComparison.index];
      },
      getTitleTooltip(comparison) {
        return `${this.htmlentities(comparison.title)}<br/>${this.htmlentities(decodeURIComponent(comparison.params.segment))}`;
      },
      getUrlToSegment(segment) {
        const hash = __spreadValues({}, instance$1.hashParsed.value);
        delete hash.comparePeriods;
        delete hash.compareDates;
        delete hash.compareSegments;
        hash.segment = segment;
        return `${window.location.search}#?${instance$1.stringify(hash)}`;
      },
      onComparisonsChanged() {
        this.comparisonTooltips = null;
        if (!ComparisonsStoreInstance.isComparing()) {
          return;
        }
        const periodComparisons = ComparisonsStoreInstance.getPeriodComparisons();
        const segmentComparisons = ComparisonsStoreInstance.getSegmentComparisons();
        AjaxHelper.fetch({
          method: "API.getProcessedReport",
          apiModule: "VisitsSummary",
          apiAction: "get",
          compare: "1",
          compareSegments: instance$1.getSearchParam("compareSegments"),
          comparePeriods: instance$1.getSearchParam("comparePeriods"),
          compareDates: instance$1.getSearchParam("compareDates"),
          format_metrics: "1"
        }).then((report) => {
          this.comparisonTooltips = {};
          periodComparisons.forEach((periodComp) => {
            this.comparisonTooltips[periodComp.index] = {};
            segmentComparisons.forEach((segmentComp) => {
              const tooltip = this.generateComparisonTooltip(report, periodComp, segmentComp);
              this.comparisonTooltips[periodComp.index][segmentComp.index] = tooltip;
            });
          });
        });
      },
      generateComparisonTooltip(visitsSummary, periodComp, segmentComp) {
        if (!visitsSummary.reportData.comparisons) {
          return "";
        }
        const firstRowIndex = ComparisonsStoreInstance.getComparisonSeriesIndex(
          periodComp.index,
          0
        );
        const firstRow = visitsSummary.reportData.comparisons[firstRowIndex];
        const comparisonRowIndex = ComparisonsStoreInstance.getComparisonSeriesIndex(
          periodComp.index,
          segmentComp.index
        );
        const comparisonRow = visitsSummary.reportData.comparisons[comparisonRowIndex];
        const firstPeriodRow = visitsSummary.reportData.comparisons[segmentComp.index];
        let tooltip = '<div class="comparison-card-tooltip">';
        let visitsPercent = (comparisonRow.nb_visits / firstRow.nb_visits * 100).toFixed(2);
        visitsPercent = `${visitsPercent}%`;
        tooltip += translate("General_ComparisonCardTooltip1", [
          `'${this.htmlentities(comparisonRow.compareSegmentPretty)}'`,
          comparisonRow.comparePeriodPretty,
          visitsPercent,
          comparisonRow.nb_visits.toString(),
          firstRow.nb_visits.toString()
        ]);
        if (periodComp.index > 0) {
          tooltip += "<br/><br/>";
          tooltip += translate("General_ComparisonCardTooltip2", [
            comparisonRow.nb_visits_change.toString(),
            this.htmlentities(firstPeriodRow.compareSegmentPretty),
            firstPeriodRow.comparePeriodPretty
          ]);
        }
        tooltip += "</div>";
        return tooltip;
      },
      htmlentities(str) {
        return Matomo.helper.htmlEntities(str);
      }
    },
    mounted() {
      Matomo.on("piwikComparisonsChanged", () => {
        this.onComparisonsChanged();
      });
      this.onComparisonsChanged();
    }
  });
  const _hoisted_1$z = {
    key: 0,
    ref: "root",
    class: "matomo-comparisons"
  };
  const _hoisted_2$s = { class: "comparison-type" };
  const _hoisted_3$p = ["title"];
  const _hoisted_4$k = ["href"];
  const _hoisted_5$i = ["title"];
  const _hoisted_6$f = { class: "comparison-period-label" };
  const _hoisted_7$b = ["onClick"];
  const _hoisted_8$9 = ["title"];
  const _hoisted_9$7 = {
    class: "loadingPiwik",
    style: { "display": "none" }
  };
  function _sfc_render$D(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return _ctx.isComparing ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$z, [
      vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("General_Comparisons")), 1),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.segmentComparisons, (comparison, $index) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          class: "comparison card",
          key: comparison.index
        }, [
          vue.createElementVNode("div", _hoisted_2$s, vue.toDisplayString(_ctx.translate("General_Segment")), 1),
          vue.createElementVNode("div", {
            class: "title",
            title: _ctx.getTitleTooltip(comparison)
          }, [
            vue.createElementVNode("a", {
              target: "_blank",
              href: _ctx.getUrlToSegment(comparison.params.segment)
            }, vue.toDisplayString(comparison.title), 9, _hoisted_4$k)
          ], 8, _hoisted_3$p),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.periodComparisons, (periodComparison) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              class: "comparison-period",
              key: periodComparison.index,
              title: _ctx.getComparisonTooltip(comparison, periodComparison)
            }, [
              vue.createElementVNode("span", {
                class: "comparison-dot",
                style: vue.normalizeStyle({
                  "background-color": _ctx.getSeriesColor(comparison, periodComparison)
                })
              }, null, 4),
              vue.createElementVNode("span", _hoisted_6$f, vue.toDisplayString(periodComparison.title) + " (" + vue.toDisplayString(_ctx.getComparisonPeriodType(periodComparison)) + ") ", 1)
            ], 8, _hoisted_5$i);
          }), 128)),
          _ctx.segmentComparisons.length > 1 ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            class: "remove-button",
            onClick: ($event) => _ctx.removeSegmentComparison($index)
          }, [
            vue.createElementVNode("span", {
              class: "icon icon-close",
              title: _ctx.translate("General_ClickToRemoveComp")
            }, null, 8, _hoisted_8$9)
          ], 8, _hoisted_7$b)) : vue.createCommentVNode("", true)
        ]);
      }), 128)),
      vue.createElementVNode("div", _hoisted_9$7, [
        vue.createVNode(_component_MatomoLoader),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
      ])
    ])), [
      [_directive_tooltips, { duration: 200, delay: 200, content: _ctx.transformTooltipContent }]
    ]) : vue.createCommentVNode("", true);
  }
  const Comparisons = /* @__PURE__ */ _export_sfc(_sfc_main$D, [["render", _sfc_render$D]]);
  const { $: $$a } = window;
  const _sfc_main$C = vue.defineComponent({
    props: {
      menuTitle: String,
      tooltip: String,
      showSearch: Boolean,
      menuTitleChangeOnClick: Boolean
    },
    directives: {
      FocusAnywhereButHere,
      FocusIf
    },
    emits: ["afterSelect"],
    watch: {
      menuTitle() {
        this.actualMenuTitle = this.menuTitle;
      }
    },
    data() {
      return {
        showItems: false,
        searchTerm: "",
        actualMenuTitle: this.menuTitle
      };
    },
    methods: {
      lostFocus() {
        this.showItems = false;
      },
      selectItem(event) {
        const targetClasses = event.target.classList;
        if (!targetClasses.contains("item") || targetClasses.contains("disabled") || targetClasses.contains("separator")) {
          return;
        }
        if (this.menuTitleChangeOnClick) {
          this.actualMenuTitle = (event.target.textContent || "").replace(/[\u0000-\u2666]/g, (c) => `&#${c.charCodeAt(0)};`);
        }
        this.showItems = false;
        $$a(this.$slots.default()[0].el).find(".item").removeClass("active");
        targetClasses.add("active");
        this.$emit("afterSelect", event.target);
      },
      onSearchTermKeydown() {
        setTimeout(() => {
          this.searchItems(this.searchTerm);
        });
      },
      searchItems(unprocessedSearchTerm) {
        const searchTerm = unprocessedSearchTerm.toLowerCase();
        $$a(this.$refs.root).find(".item").each((index, node) => {
          const $node = $$a(node);
          if ($node.text().toLowerCase().indexOf(searchTerm) === -1) {
            $node.hide();
          } else {
            $node.show();
          }
        });
      }
    }
  });
  const _hoisted_1$y = {
    ref: "root",
    class: "menuDropdown"
  };
  const _hoisted_2$r = ["title"];
  const _hoisted_3$o = ["innerHTML"];
  const _hoisted_4$j = { class: "items" };
  const _hoisted_5$h = {
    key: 0,
    class: "search"
  };
  const _hoisted_6$e = ["placeholder"];
  const _hoisted_7$a = ["title"];
  const _hoisted_8$8 = ["title"];
  function _sfc_render$C(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_focus_if = vue.resolveDirective("focus-if");
    const _directive_focus_anywhere_but_here = vue.resolveDirective("focus-anywhere-but-here");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$y, [
      vue.createElementVNode("span", {
        class: "title",
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.showItems = !_ctx.showItems),
        title: _ctx.tooltip
      }, [
        vue.createElementVNode("span", {
          class: "title-label",
          innerHTML: _ctx.$sanitize(_ctx.actualMenuTitle)
        }, null, 8, _hoisted_3$o),
        _cache[5] || (_cache[5] = vue.createElementVNode("span", { class: "icon-chevron-down reporting-menu-sub-icon" }, null, -1))
      ], 8, _hoisted_2$r),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_4$j, [
        _ctx.showSearch && _ctx.showItems ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$h, [
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.searchTerm = $event),
            onKeydown: _cache[2] || (_cache[2] = ($event) => _ctx.onSearchTermKeydown()),
            placeholder: _ctx.translate("General_Search")
          }, null, 40, _hoisted_6$e), [
            [vue.vModelText, _ctx.searchTerm],
            [_directive_focus_if, { focused: _ctx.showItems }]
          ]),
          vue.withDirectives(vue.createElementVNode("div", {
            class: "search_ico icon-search",
            title: _ctx.translate("General_Search")
          }, null, 8, _hoisted_7$a), [
            [vue.vShow, !_ctx.searchTerm]
          ]),
          vue.withDirectives(vue.createElementVNode("div", {
            onClick: _cache[3] || (_cache[3] = ($event) => {
              _ctx.searchTerm = "";
              _ctx.searchItems("");
            }),
            class: "reset icon-close",
            title: _ctx.translate("General_Clear")
          }, null, 8, _hoisted_8$8), [
            [vue.vShow, _ctx.searchTerm]
          ])
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", {
          onClick: _cache[4] || (_cache[4] = ($event) => _ctx.selectItem($event))
        }, [
          vue.renderSlot(_ctx.$slots, "default")
        ])
      ], 512), [
        [vue.vShow, _ctx.showItems]
      ])
    ])), [
      [_directive_focus_anywhere_but_here, { blur: _ctx.lostFocus }]
    ]);
  }
  const MenuItemsDropdown = /* @__PURE__ */ _export_sfc(_sfc_main$C, [["render", _sfc_render$C]]);
  const DEFAULT_STEP_MONTHS = 1;
  const { $: $$9 } = window;
  const _sfc_main$B = vue.defineComponent({
    props: {
      selectedDateStart: Date,
      selectedDateEnd: Date,
      persistentHighlightedDateStart: Date,
      persistentHighlightedDateEnd: Date,
      highlightedDateStart: Date,
      highlightedDateEnd: Date,
      viewDate: [String, Date],
      stepMonths: Number,
      disableMonthDropdown: Boolean,
      disabled: Boolean,
      options: Object
    },
    emits: ["cellHover", "cellHoverLeave", "dateSelect"],
    setup(props, context) {
      const root = vue.ref(null);
      function setDateCellColor($dateCell, dateValue) {
        const $dateCellLink = $dateCell.children("a");
        const { selectedDateStart, selectedDateEnd } = props;
        const dateValueTime = dateValue.getTime();
        const isPersistentlyHighlightedDate = !!(props.persistentHighlightedDateStart && props.persistentHighlightedDateEnd && dateValue >= props.persistentHighlightedDateStart && dateValue <= props.persistentHighlightedDateEnd);
        const isBoundarySelectedDate = !!(selectedDateStart && selectedDateEnd && (dateValueTime === selectedDateStart.getTime() || dateValueTime === selectedDateEnd.getTime()));
        if (isBoundarySelectedDate) {
          $dateCell.addClass("ui-datepicker-current-period");
        } else {
          $dateCell.removeClass("ui-datepicker-current-period");
        }
        if (props.highlightedDateStart && props.highlightedDateEnd && dateValue >= props.highlightedDateStart && dateValue <= props.highlightedDateEnd) {
          $dateCell.addClass("ui-state-hover");
          if ($dateCellLink.length) {
            $dateCellLink.addClass("ui-state-hover");
          }
        } else {
          $dateCell.removeClass("ui-state-hover");
          $dateCellLink.removeClass("ui-state-hover");
        }
        if (isPersistentlyHighlightedDate) {
          $dateCell.addClass("ui-datepicker-persistent-highlight");
          if ($dateCellLink.length) {
            $dateCellLink.addClass("ui-datepicker-persistent-highlight");
          }
        } else {
          $dateCell.removeClass("ui-datepicker-persistent-highlight");
          $dateCellLink.removeClass("ui-datepicker-persistent-highlight");
        }
      }
      function getCellDate($dateCell, month, year) {
        if ($dateCell.hasClass("ui-datepicker-other-month")) {
          return getOtherMonthDate($dateCell, month, year);
        }
        const day = parseInt($dateCell.children("a,span").text(), 10);
        return new Date(year, month, day);
      }
      function getOtherMonthDate($dateCell, month, year) {
        let date;
        const $row = $dateCell.parent();
        const $rowCells = $row.children("td");
        if ($row.is(":first-child")) {
          const $firstDateInMonth = $row.children("td:not(.ui-datepicker-other-month)").first();
          date = getCellDate($firstDateInMonth, month, year);
          date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
          return date;
        }
        const $lastDateInMonth = $row.children("td:not(.ui-datepicker-other-month)").last();
        date = getCellDate($lastDateInMonth, month, year);
        date.setDate(date.getDate() + $rowCells.index($dateCell) - $rowCells.index($lastDateInMonth));
        return date;
      }
      function getMonthYearDisplayed() {
        const element = $$9(root.value);
        const $firstCellWithMonth = element.find("td[data-month]");
        const month = parseInt($firstCellWithMonth.attr("data-month"), 10);
        const year = parseInt($firstCellWithMonth.attr("data-year"), 10);
        return [month, year];
      }
      function setDatePickerCellColors() {
        const element = $$9(root.value);
        const $calendarTable = element.find(".ui-datepicker-calendar");
        const monthYear = getMonthYearDisplayed();
        const $cells = $calendarTable.find("td");
        const $firstDateCell = $cells.first();
        const currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);
        $cells.each(function setCellColor() {
          setDateCellColor($$9(this), currentDate);
          currentDate.setDate(currentDate.getDate() + 1);
        });
      }
      function viewDateChanged() {
        if (!props.viewDate) {
          return false;
        }
        let date;
        if (typeof props.viewDate === "string") {
          try {
            date = parseDate(props.viewDate);
          } catch (e) {
            return false;
          }
        } else {
          date = props.viewDate;
        }
        const element = $$9(root.value);
        const monthYear = getMonthYearDisplayed();
        if (monthYear[0] !== date.getMonth() || monthYear[1] !== date.getFullYear()) {
          element.datepicker("setDate", date);
          return true;
        }
        return false;
      }
      function enableDisableMonthDropdown() {
        const element = $$9(root.value);
        const monthPicker = element.find(".ui-datepicker-month")[0];
        if (monthPicker) {
          monthPicker.disabled = props.disableMonthDropdown || !!props.disabled;
        }
        const yearPicker = element.find(".ui-datepicker-year")[0];
        if (yearPicker) {
          yearPicker.disabled = !!props.disabled;
        }
      }
      function updateKeyboardAccessibility() {
        const element = $$9(root.value);
        const tabIndex = props.disabled ? -1 : 0;
        element.find("a, select").attr("tabindex", tabIndex);
        element.attr("aria-disabled", props.disabled ? "true" : "false");
        if (props.disabled) {
          element.find("a").attr("aria-disabled", "true");
        } else {
          element.find("a").removeAttr("aria-disabled");
        }
      }
      function onJqueryUiRenderedPicker() {
        const element = $$9(root.value);
        element.find("td[data-event]").off("click");
        element.find(".ui-state-active").removeClass("ui-state-active");
        element.find(".ui-datepicker-current-day").removeClass("ui-datepicker-current-day");
        element.find(".ui-datepicker-prev,.ui-datepicker-next").attr("href", "");
        element.find(".ui-datepicker-prev .ui-icon").removeClass("ui-icon-circle-triangle-w").addClass("icon-chevron-left");
        element.find(".ui-datepicker-next .ui-icon").removeClass("ui-icon-circle-triangle-e").addClass("icon-chevron-right");
        updateKeyboardAccessibility();
      }
      function stepMonthsChanged() {
        const element = $$9(root.value);
        const stepMonths = props.stepMonths || DEFAULT_STEP_MONTHS;
        if (element.datepicker("option", "stepMonths") === stepMonths) {
          return false;
        }
        const currentMonth = $$9(".ui-datepicker-month", element).val();
        const currentYear = $$9(".ui-datepicker-year", element).val();
        element.datepicker("option", "stepMonths", stepMonths).datepicker("setDate", new Date(currentYear, currentMonth));
        onJqueryUiRenderedPicker();
        return true;
      }
      function handleOtherMonthClick() {
        if (!$$9(this).hasClass("ui-state-hover")) {
          return;
        }
        const $row = $$9(this).parent();
        const $tbody = $row.parent();
        if ($row.is(":first-child")) {
          $tbody.find("a").first().click();
        } else {
          $tbody.find("a").last().click();
        }
      }
      function onCalendarViewChange() {
        enableDisableMonthDropdown();
        updateKeyboardAccessibility();
        setDatePickerCellColors();
      }
      vue.watch(() => __spreadValues({}, props), (newProps, oldProps) => {
        let redraw = false;
        [
          (x) => x.selectedDateStart,
          (x) => x.selectedDateEnd,
          (x) => x.persistentHighlightedDateStart,
          (x) => x.persistentHighlightedDateEnd,
          (x) => x.highlightedDateStart,
          (x) => x.highlightedDateEnd
        ].forEach((selector) => {
          if (redraw) {
            return;
          }
          const newProp = selector(newProps);
          const oldProp = selector(oldProps);
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
        }
        if (newProps.disabled !== oldProps.disabled) {
          enableDisableMonthDropdown();
          updateKeyboardAccessibility();
        }
        if (redraw) {
          setDatePickerCellColors();
        }
      });
      vue.onMounted(() => {
        const element = $$9(root.value);
        const customOptions = props.options || {};
        const datePickerOptions = __spreadProps(__spreadValues(__spreadValues({}, Matomo.getBaseDatePickerOptions()), customOptions), {
          onChangeMonthYear: () => {
            setTimeout(() => {
              onJqueryUiRenderedPicker();
            });
          }
        });
        element.datepicker(datePickerOptions);
        element.on("mouseover", "tbody td a", (event) => {
          if (event.originalEvent) {
            setDatePickerCellColors();
          }
        });
        element.on("mouseenter", "tbody td", function onMouseEnter2() {
          const monthYear = getMonthYearDisplayed();
          const $dateCell = $$9(this);
          const dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
          context.emit("cellHover", { date: dateValue, $cell: $dateCell });
        });
        element.on("mouseout", "tbody td a", () => {
          setDatePickerCellColors();
        });
        element.on("mouseleave", "table", () => context.emit("cellHoverLeave")).on("mouseenter", "thead", () => context.emit("cellHoverLeave"));
        element.on("click", "tbody td.ui-datepicker-other-month", handleOtherMonthClick);
        element.on("click", (e) => {
          e.preventDefault();
          const $target = $$9(e.target).closest("a");
          if (!$target.is(".ui-datepicker-next") && !$target.is(".ui-datepicker-prev")) {
            return;
          }
          onCalendarViewChange();
        });
        element.on("click", "td[data-month]", (event) => {
          const $cell = $$9(event.target).closest("td");
          const month = parseInt($cell.attr("data-month"), 10);
          const year = parseInt($cell.attr("data-year"), 10);
          const day = parseInt($cell.children("a,span").text(), 10);
          context.emit("dateSelect", { date: new Date(year, month, day) });
        });
        const renderPostProcessed = stepMonthsChanged();
        viewDateChanged();
        enableDisableMonthDropdown();
        if (!renderPostProcessed) {
          onJqueryUiRenderedPicker();
        }
        updateKeyboardAccessibility();
        setDatePickerCellColors();
      });
      return {
        root
      };
    }
  });
  const _hoisted_1$x = { ref: "root" };
  function _sfc_render$B(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$x, null, 512);
  }
  const DatePicker = /* @__PURE__ */ _export_sfc(_sfc_main$B, [["render", _sfc_render$B]]);
  const DATE_FORMAT = "YYYY-MM-DD";
  const _sfc_main$A = vue.defineComponent({
    name: "DateRangePicker",
    props: {
      startDate: String,
      endDate: String,
      disabled: Boolean
    },
    components: {
      DatePicker
    },
    data() {
      let startDate = null;
      try {
        if (this.startDate) {
          startDate = parseDate(this.startDate);
        }
      } catch (e) {
      }
      let endDate = null;
      try {
        if (this.endDate) {
          endDate = parseDate(this.endDate);
        }
      } catch (e) {
      }
      return {
        fromPickerSelectedDate: startDate,
        toPickerSelectedDate: endDate,
        fromPickerHoveredDate: null,
        toPickerHoveredDate: null,
        startDateText: this.startDate,
        endDateText: this.endDate,
        startDateInvalid: false,
        endDateInvalid: false
      };
    },
    emits: ["rangeChange", "submit"],
    watch: {
      startDate() {
        this.startDateText = this.startDate;
        this.syncStartRangeDateFromProp(this.startDate);
      },
      endDate() {
        this.endDateText = this.endDate;
        this.syncEndRangeDateFromProp(this.endDate);
      }
    },
    methods: {
      setStartRangeDate(date) {
        this.fromPickerSelectedDate = date;
        this.rangeChanged();
      },
      setEndRangeDate(date) {
        this.toPickerSelectedDate = date;
        this.rangeChanged();
      },
      onRangeInputChanged(source, event) {
        const input = event.target;
        setTimeout(() => {
          if (source === "from") {
            this.setStartRangeDateFromStr(input.value);
          } else {
            this.setEndRangeDateFromStr(input.value);
          }
        });
      },
      getNewHoveredDate(date, $cell) {
        if ($cell.hasClass("ui-datepicker-unselectable")) {
          return null;
        }
        return date;
      },
      handleEnterPress($event) {
        if ($event.keyCode !== 13) {
          return;
        }
        this.$emit("submit", {
          start: this.startDate,
          end: this.endDate
        });
      },
      syncStartRangeDateFromProp(dateStr) {
        this.startDateInvalid = true;
        let startDateParsed = null;
        try {
          if (dateStr && dateStr.length === DATE_FORMAT.length) {
            startDateParsed = parseDate(dateStr);
          }
        } catch (e) {
        }
        if (startDateParsed) {
          this.fromPickerSelectedDate = startDateParsed;
          this.startDateInvalid = false;
        }
      },
      setStartRangeDateFromStr(dateStr) {
        this.syncStartRangeDateFromProp(dateStr);
        if (!this.startDateInvalid) {
          this.rangeChanged();
        }
      },
      syncEndRangeDateFromProp(dateStr) {
        this.endDateInvalid = true;
        let endDateParsed = null;
        try {
          if (dateStr && dateStr.length === DATE_FORMAT.length) {
            endDateParsed = parseDate(dateStr);
          }
        } catch (e) {
        }
        if (endDateParsed) {
          this.toPickerSelectedDate = endDateParsed;
          this.endDateInvalid = false;
        }
      },
      setEndRangeDateFromStr(dateStr) {
        this.syncEndRangeDateFromProp(dateStr);
        if (!this.endDateInvalid) {
          this.rangeChanged();
        }
      },
      rangeChanged() {
        this.$emit("rangeChange", {
          start: this.fromPickerSelectedDate ? format(this.fromPickerSelectedDate) : null,
          end: this.toPickerSelectedDate ? format(this.toPickerSelectedDate) : null
        });
      }
    }
  });
  const _hoisted_1$w = { class: "dateRangePicker" };
  const _hoisted_2$q = { id: "calendarRangeFrom" };
  const _hoisted_3$n = { class: "dateRangePicker-label" };
  const _hoisted_4$i = ["disabled"];
  const _hoisted_5$g = { id: "calendarRangeTo" };
  const _hoisted_6$d = { class: "dateRangePicker-label" };
  const _hoisted_7$9 = ["disabled"];
  function _sfc_render$A(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DatePicker = vue.resolveComponent("DatePicker");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$w, [
      vue.createElementVNode("div", _hoisted_2$q, [
        vue.createElementVNode("h6", _hoisted_3$n, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_DateRangeFrom")) + " ", 1),
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            id: "inputCalendarFrom",
            name: "inputCalendarFrom",
            class: "browser-default dateRangePicker-field",
            disabled: _ctx.disabled,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.startDateText = $event),
            onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.onRangeInputChanged("from", $event)),
            onKeyup: _cache[2] || (_cache[2] = ($event) => _ctx.handleEnterPress($event))
          }, null, 40, _hoisted_4$i), [
            [vue.vModelText, _ctx.startDateText]
          ])
        ]),
        vue.createVNode(_component_DatePicker, {
          id: "calendarFrom",
          "view-date": _ctx.startDate,
          "selected-date-start": _ctx.fromPickerSelectedDate,
          "selected-date-end": _ctx.fromPickerSelectedDate,
          "highlighted-date-start": _ctx.fromPickerHoveredDate,
          "highlighted-date-end": _ctx.fromPickerHoveredDate,
          disabled: _ctx.disabled,
          onDateSelect: _cache[3] || (_cache[3] = ($event) => _ctx.setStartRangeDate($event.date)),
          onCellHover: _cache[4] || (_cache[4] = ($event) => _ctx.fromPickerHoveredDate = _ctx.getNewHoveredDate($event.date, $event.$cell)),
          onCellHoverLeave: _cache[5] || (_cache[5] = ($event) => _ctx.fromPickerHoveredDate = null)
        }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end", "disabled"])
      ]),
      vue.createElementVNode("div", _hoisted_5$g, [
        vue.createElementVNode("h6", _hoisted_6$d, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_DateRangeTo")) + " ", 1),
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            id: "inputCalendarTo",
            name: "inputCalendarTo",
            class: "browser-default dateRangePicker-field",
            disabled: _ctx.disabled,
            "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.endDateText = $event),
            onKeydown: _cache[7] || (_cache[7] = ($event) => _ctx.onRangeInputChanged("to", $event)),
            onKeyup: _cache[8] || (_cache[8] = ($event) => _ctx.handleEnterPress($event))
          }, null, 40, _hoisted_7$9), [
            [vue.vModelText, _ctx.endDateText]
          ])
        ]),
        vue.createVNode(_component_DatePicker, {
          id: "calendarTo",
          "view-date": _ctx.endDate,
          "selected-date-start": _ctx.toPickerSelectedDate,
          "selected-date-end": _ctx.toPickerSelectedDate,
          "highlighted-date-start": _ctx.toPickerHoveredDate,
          "highlighted-date-end": _ctx.toPickerHoveredDate,
          disabled: _ctx.disabled,
          onDateSelect: _cache[9] || (_cache[9] = ($event) => _ctx.setEndRangeDate($event.date)),
          onCellHover: _cache[10] || (_cache[10] = ($event) => _ctx.toPickerHoveredDate = _ctx.getNewHoveredDate($event.date, $event.$cell)),
          onCellHoverLeave: _cache[11] || (_cache[11] = ($event) => _ctx.toPickerHoveredDate = null)
        }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end", "disabled"])
      ])
    ]);
  }
  const DateRangePicker = /* @__PURE__ */ _export_sfc(_sfc_main$A, [["render", _sfc_render$A]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const NBSP = " ";
  const COMPARE_PERIOD_TYPES = ["custom", "previousPeriod", "previousYear"];
  const COMPARE_PERIOD_OPTIONS = [
    { key: "custom", value: translate("General_Custom") },
    {
      key: "previousPeriod",
      value: translate("General_PreviousPeriod").replace(/\s+/, NBSP)
    },
    {
      key: "previousYear",
      value: translate("General_PreviousYear").replace(/\s+/, NBSP)
    }
  ];
  function getSiteMinAllowedDate() {
    return new Date(window.piwik.minDateYear, window.piwik.minDateMonth - 1, window.piwik.minDateDay);
  }
  function getSiteMaxAllowedDate() {
    return new Date(window.piwik.maxDateYear, window.piwik.maxDateMonth - 1, window.piwik.maxDateDay);
  }
  const RANGE_PERIOD = "range";
  function isValidDate(candidateDate) {
    if (Object.prototype.toString.call(candidateDate) !== "[object Date]") {
      return false;
    }
    return !Number.isNaN(candidateDate.getTime());
  }
  function isSingleCalendarPeriod(period) {
    return period === "day" || period === "week" || period === "month" || period === "year";
  }
  const _sfc_main$z = vue.defineComponent({
    props: {
      period: {
        type: String,
        required: true
      },
      date: [String, Date],
      disabled: Boolean
    },
    components: {
      DatePicker
    },
    emits: ["select"],
    setup(props, context) {
      const viewDate = vue.ref(props.date);
      const selectedDates = vue.ref([null, null]);
      const committedBetweenHighlightDates = vue.ref([null, null]);
      const highlightedDates = vue.ref(null);
      const piwikMinDate = getSiteMinAllowedDate();
      const piwikMaxDate = getSiteMaxAllowedDate();
      function getBoundedDateRange(date) {
        const dates = Periods$1.get(props.period).parse(date).getDateRange();
        dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
        dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;
        return dates;
      }
      function getExclusiveBetweenRange(startDate, endDate) {
        if (!startDate || !endDate || startDate.getTime() >= endDate.getTime()) {
          return [null, null];
        }
        const betweenStart = new Date(startDate);
        betweenStart.setDate(betweenStart.getDate() + 1);
        const betweenEnd = new Date(endDate);
        betweenEnd.setDate(betweenEnd.getDate() - 1);
        if (betweenStart.getTime() > betweenEnd.getTime()) {
          return [null, null];
        }
        return [betweenStart, betweenEnd];
      }
      function refreshCommittedBetweenHighlightFromDate(date) {
        if (!date) {
          committedBetweenHighlightDates.value = [null, null];
          return;
        }
        const boundedDateRange = getBoundedDateRange(date);
        committedBetweenHighlightDates.value = getExclusiveBetweenRange(
          boundedDateRange[0],
          boundedDateRange[1]
        );
      }
      function onHoverNormalCell(cellDate, $cell) {
        const isOutOfMinMaxDateRange = cellDate < piwikMinDate || cellDate > piwikMaxDate;
        const shouldNotHighlightFromWhitespace = $cell.hasClass("ui-datepicker-other-month") && (props.period === "month" || props.period === "day");
        if (isOutOfMinMaxDateRange || shouldNotHighlightFromWhitespace) {
          highlightedDates.value = [null, null];
          return;
        }
        highlightedDates.value = getBoundedDateRange(cellDate);
      }
      function onHoverLeaveNormalCells() {
        highlightedDates.value = null;
      }
      function onDateSelected(date) {
        context.emit("select", { date });
      }
      function onChanges() {
        if (!props.period || !props.date) {
          selectedDates.value = [null, null];
          committedBetweenHighlightDates.value = [null, null];
          highlightedDates.value = null;
          viewDate.value = null;
          return;
        }
        selectedDates.value = getBoundedDateRange(props.date);
        refreshCommittedBetweenHighlightFromDate(props.date);
        highlightedDates.value = null;
        viewDate.value = parseDate(props.date);
      }
      vue.watch(props, onChanges);
      onChanges();
      return {
        selectedDates,
        committedBetweenHighlightDates,
        highlightedDates,
        viewDate,
        onHoverNormalCell,
        onHoverLeaveNormalCells,
        onDateSelected
      };
    }
  });
  function _sfc_render$z(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DatePicker = vue.resolveComponent("DatePicker");
    return vue.openBlock(), vue.createBlock(_component_DatePicker, {
      "selected-date-start": _ctx.selectedDates[0],
      "selected-date-end": _ctx.selectedDates[1],
      "persistent-highlighted-date-start": _ctx.committedBetweenHighlightDates[0],
      "persistent-highlighted-date-end": _ctx.committedBetweenHighlightDates[1],
      "highlighted-date-start": _ctx.highlightedDates ? _ctx.highlightedDates[0] : null,
      "highlighted-date-end": _ctx.highlightedDates ? _ctx.highlightedDates[1] : null,
      "view-date": _ctx.viewDate,
      "step-months": _ctx.period === "year" ? 12 : 1,
      "disable-month-dropdown": _ctx.period === "year",
      disabled: _ctx.disabled,
      onCellHover: _cache[0] || (_cache[0] = ($event) => _ctx.onHoverNormalCell($event.date, $event.$cell)),
      onCellHoverLeave: _cache[1] || (_cache[1] = ($event) => _ctx.onHoverLeaveNormalCells()),
      onDateSelect: _cache[2] || (_cache[2] = ($event) => _ctx.onDateSelected($event.date))
    }, null, 8, ["selected-date-start", "selected-date-end", "persistent-highlighted-date-start", "persistent-highlighted-date-end", "highlighted-date-start", "highlighted-date-end", "view-date", "step-months", "disable-month-dropdown", "disabled"]);
  }
  const PeriodDatePicker = /* @__PURE__ */ _export_sfc(_sfc_main$z, [["render", _sfc_render$z]]);
  const { $: $$8 } = window;
  const _sfc_main$y = vue.defineComponent({
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
        default: 12 * 1e3
      },
      style: [String, Object],
      animate: Boolean,
      message: String,
      cssClass: String
    },
    computed: {
      cssClasses() {
        const result = {};
        if (this.context) {
          result[`notification-${this.context}`] = true;
        }
        if (this.cssClass) {
          result[this.cssClass] = true;
        }
        return result;
      },
      canClose() {
        if (this.type === "persistent") {
          return true;
        }
        return !this.noclear;
      }
    },
    emits: ["closed"],
    data() {
      return {
        deleted: false
      };
    },
    mounted() {
      const addToastEvent = () => {
        setTimeout(() => {
          this.deleted = true;
        }, this.toastLength);
      };
      if (this.type === "toast") {
        addToastEvent();
      }
      if (this.style) {
        $$8(this.$refs.root).css(this.style);
      }
    },
    methods: {
      toastClosed() {
        vue.nextTick(() => {
          this.$emit("closed");
        });
      },
      closeNotification(event) {
        if (this.canClose && event && event.target) {
          this.deleted = true;
          vue.nextTick(() => {
            this.$emit("closed");
          });
        }
        this.markNotificationAsRead();
      },
      markNotificationAsRead() {
        if (!this.notificationId) {
          return;
        }
        AjaxHelper.post(
          {
            // GET params
            module: "CoreHome",
            action: "markNotificationAsRead"
          },
          {
            // POST params
            notificationId: this.notificationId
          },
          { withTokenInUrl: true }
        );
      }
    }
  });
  const _hoisted_1$v = { key: 0 };
  const _hoisted_2$p = ["data-notification-instance-id"];
  const _hoisted_3$m = { key: 1 };
  const _hoisted_4$h = { class: "notification-body" };
  const _hoisted_5$f = ["innerHTML"];
  const _hoisted_6$c = { key: 1 };
  function _sfc_render$y(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createBlock(vue.Transition, {
      name: _ctx.type === "toast" ? "slow-fade-out" : void 0,
      onAfterLeave: _cache[1] || (_cache[1] = ($event) => _ctx.toastClosed())
    }, {
      default: vue.withCtx(() => [
        !_ctx.deleted ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$v, [
          vue.createVNode(vue.Transition, {
            name: _ctx.type === "toast" ? "toast-slide-up" : void 0,
            appear: ""
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("div", null, [
                vue.createVNode(vue.Transition, {
                  name: _ctx.animate ? "fade-in" : void 0,
                  appear: ""
                }, {
                  default: vue.withCtx(() => [
                    vue.createElementVNode("div", {
                      class: vue.normalizeClass(["notification system", _ctx.cssClasses]),
                      style: vue.normalizeStyle(_ctx.style),
                      ref: "root",
                      "data-notification-instance-id": _ctx.notificationInstanceId
                    }, [
                      _ctx.canClose ? (vue.openBlock(), vue.createElementBlock("button", {
                        key: 0,
                        type: "button",
                        class: "close",
                        "data-dismiss": "alert",
                        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.closeNotification($event))
                      }, " × ")) : vue.createCommentVNode("", true),
                      _ctx.title ? (vue.openBlock(), vue.createElementBlock("strong", _hoisted_3$m, vue.toDisplayString(_ctx.title), 1)) : vue.createCommentVNode("", true),
                      vue.createElementVNode("div", _hoisted_4$h, [
                        _ctx.message ? (vue.openBlock(), vue.createElementBlock("div", {
                          key: 0,
                          innerHTML: _ctx.$sanitize(_ctx.message)
                        }, null, 8, _hoisted_5$f)) : vue.createCommentVNode("", true),
                        !_ctx.message ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$c, [
                          vue.renderSlot(_ctx.$slots, "default")
                        ])) : vue.createCommentVNode("", true)
                      ])
                    ], 14, _hoisted_2$p)
                  ]),
                  _: 3
                }, 8, ["name"])
              ])
            ]),
            _: 3
          }, 8, ["name"])
        ])) : vue.createCommentVNode("", true)
      ]),
      _: 3
    }, 8, ["name"]);
  }
  const Notification = /* @__PURE__ */ _export_sfc(_sfc_main$y, [["render", _sfc_render$y]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$7 } = window;
  class NotificationsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        notifications: []
      }));
      __publicField(this, "nextNotificationId", 0);
    }
    get state() {
      return vue.readonly(this.privateState);
    }
    appendNotification(notification) {
      this.checkMessage(notification.message);
      if (notification.id) {
        this.remove(notification.id);
      }
      this.privateState.notifications.push(notification);
    }
    prependNotification(notification) {
      this.checkMessage(notification.message);
      if (notification.id) {
        this.remove(notification.id);
      }
      this.privateState.notifications.unshift(notification);
    }
    /**
     * Removes a previously shown notification having the given notification id.
     */
    remove(id) {
      this.privateState.notifications = this.privateState.notifications.filter(
        (n) => n.id !== id
      );
    }
    parseNotificationDivs() {
      const $notificationNodes = $$7('[data-role="notification"]');
      const notificationsToShow = [];
      $notificationNodes.each((index, notificationNode) => {
        const $notificationNode = $$7(notificationNode);
        const attributes = $notificationNode.data();
        const message = $notificationNode.html();
        if (message) {
          notificationsToShow.push(__spreadProps(__spreadValues({}, attributes), { message, animate: false }));
        }
        $notificationNodes.remove();
      });
      notificationsToShow.forEach((n) => this.show(n));
    }
    clearTransientNotifications() {
      this.privateState.notifications = this.privateState.notifications.filter(
        (n) => n.type !== "transient"
      );
    }
    /**
     * Creates a notification and shows it to the user.
     */
    show(notification) {
      this.checkMessage(notification.message);
      let addMethod = notification.prepend ? this.prependNotification : this.appendNotification;
      let notificationPosition = "#notificationContainer";
      if (notification.placeat) {
        notificationPosition = notification.placeat;
      } else {
        const modalSelector = ".modal.open .modal-content";
        const modal = document.querySelector(modalSelector);
        if (modal) {
          if (!modal.querySelector("#modalNotificationContainer")) {
            $$7(modal).prepend('<div id="modalNotificationContainer"/>');
          }
          notificationPosition = `${modalSelector} #modalNotificationContainer`;
          addMethod = this.prependNotification;
        }
      }
      const group = notification.group || (notificationPosition ? notificationPosition.toString() : "");
      this.initializeNotificationContainer(notificationPosition, group);
      const notificationInstanceId = (this.nextNotificationId += 1).toString();
      addMethod.call(this, __spreadProps(__spreadValues({}, notification), {
        noclear: !!notification.noclear,
        group,
        notificationId: notification.id,
        notificationInstanceId,
        type: notification.type || "transient"
      }));
      return notificationInstanceId;
    }
    scrollToNotification(notificationInstanceId) {
      setTimeout(() => {
        const element = document.querySelector(
          `[data-notification-instance-id='${notificationInstanceId}']`
        );
        if (element) {
          Matomo.helper.lazyScrollTo(element, 250);
        }
      });
    }
    /**
     * Shows a notification at a certain point with a quick upwards animation.
     */
    toast(notification) {
      this.checkMessage(notification.message);
      const $placeat = notification.placeat ? $$7(notification.placeat) : void 0;
      if (!$placeat || !$placeat.length) {
        throw new Error("A valid selector is required for the placeat option when using Notification.toast().");
      }
      const toastElement = document.createElement("div");
      toastElement.style.position = "absolute";
      toastElement.style.top = `${$placeat.offset().top}px`;
      toastElement.style.left = `${$placeat.offset().left}px`;
      toastElement.style.zIndex = "1000";
      document.body.appendChild(toastElement);
      const app = createVueApp({
        render: () => vue.createVNode(Notification, __spreadProps(__spreadValues({}, notification), {
          notificationId: notification.id,
          type: "toast",
          onClosed: () => {
            app.unmount();
          }
        }))
      });
      app.mount(toastElement);
    }
    initializeNotificationContainer(notificationPosition, group) {
      if (!notificationPosition) {
        return;
      }
      const $container = $$7(notificationPosition);
      if ($container.children(".notification-group").length) {
        return;
      }
      const NotificationGroup2 = window.CoreHome.NotificationGroup;
      const app = createVueApp({
        template: '<NotificationGroup :group="group"></NotificationGroup>',
        data: () => ({ group })
      });
      app.component("NotificationGroup", NotificationGroup2);
      app.mount($container[0]);
    }
    checkMessage(message) {
      if (!message) {
        throw new Error("No message given, cannot display notification");
      }
    }
  }
  const instance = new NotificationsStore();
  $$7(() => instance.parseNotificationDivs());
  const _sfc_main$x = vue.defineComponent({
    props: {
      group: String
    },
    components: {
      Notification
    },
    computed: {
      notifications() {
        return instance.state.notifications.filter((n) => {
          if (this.group) {
            return this.group === n.group;
          }
          return !n.group;
        });
      }
    },
    methods: {
      removeNotification(id) {
        if (id) {
          instance.remove(id);
        }
      }
    }
  });
  const _hoisted_1$u = { class: "notification-group" };
  const _hoisted_2$o = ["innerHTML"];
  function _sfc_render$x(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Notification = vue.resolveComponent("Notification");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$u, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.notifications, (notification, index) => {
        return vue.openBlock(), vue.createBlock(_component_Notification, {
          key: notification.id || `no-id-${index}`,
          "notification-id": notification.id,
          title: notification.title,
          context: notification.context,
          type: notification.type,
          noclear: notification.noclear,
          "toast-length": notification.toastLength,
          style: vue.normalizeStyle(notification.style),
          animate: notification.animate,
          message: notification.message,
          "notification-instance-id": notification.notificationInstanceId,
          "css-class": notification.class,
          onClosed: ($event) => _ctx.removeNotification(notification.id)
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("div", {
              innerHTML: _ctx.$sanitize(notification.message)
            }, null, 8, _hoisted_2$o)
          ]),
          _: 2
        }, 1032, ["notification-id", "title", "context", "type", "noclear", "toast-length", "style", "animate", "message", "notification-instance-id", "css-class", "onClosed"]);
      }), 128))
    ]);
  }
  const NotificationGroup = /* @__PURE__ */ _export_sfc(_sfc_main$x, [["render", _sfc_render$x]]);
  const REPORTING_HELP_NOTIFICATION_ID$1 = "reportingMenu-help";
  const _sfc_main$w = vue.defineComponent({
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
    data() {
      return {
        currentName: ""
      };
    },
    methods: {
      showHelp() {
        if (this.currentName !== "") {
          instance.remove(REPORTING_HELP_NOTIFICATION_ID$1);
          this.currentName = "";
          return;
        }
        instance.show({
          context: "info",
          id: REPORTING_HELP_NOTIFICATION_ID$1,
          type: "help",
          noclear: true,
          class: "help-notification",
          message: this.message,
          placeat: "#notificationContainer",
          prepend: true
        });
        if (this.name !== "") {
          this.currentName = this.name;
        }
      }
    }
  });
  function _sfc_render$w(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("a", {
      class: "item-help-icon",
      tabindex: "5",
      href: "javascript:",
      onClick: _cache[0] || (_cache[0] = (...args) => _ctx.showHelp && _ctx.showHelp(...args))
    }, [..._cache[1] || (_cache[1] = [
      vue.createElementVNode("span", { class: "icon-help" }, null, -1)
    ])]);
  }
  const ShowHelpLink = /* @__PURE__ */ _export_sfc(_sfc_main$w, [["render", _sfc_render$w]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class SitesStore {
    constructor() {
      __publicField(this, "state", vue.reactive({
        initialSites: [],
        isInitialized: false
      }));
      __publicField(this, "stateFiltered", vue.reactive({
        initialSites: [],
        isInitialized: false,
        excludedSites: [],
        onlySitesWithAdminAccess: false,
        onlySitesWithAtLeastWriteAccess: false,
        siteTypesToExclude: []
      }));
      __publicField(this, "currentRequestAbort", null);
      __publicField(this, "limitRequest");
      __publicField(this, "initialSites", vue.computed(() => vue.readonly(this.state.initialSites)));
      __publicField(this, "initialSitesFiltered", vue.computed(() => vue.readonly(this.stateFiltered.initialSites)));
    }
    isFiltered(onlySitesWithAdminAccess = false, sitesToExclude = [], onlySitesWithAtLeastWriteAccess = false, siteTypesToExclude = []) {
      return sitesToExclude.length > 0 || onlySitesWithAdminAccess || onlySitesWithAtLeastWriteAccess || siteTypesToExclude.length > 0;
    }
    matchesCurrentFilteredState(onlySitesWithAdminAccess = false, sitesToExclude = [], onlySitesWithAtLeastWriteAccess = false, siteTypesToExclude = []) {
      if (!this.stateFiltered.isInitialized && !this.isFiltered(
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude
      )) {
        return true;
      }
      return this.stateFiltered.isInitialized && sitesToExclude.length === this.stateFiltered.excludedSites.length && sitesToExclude.every((val, index) => val === this.stateFiltered.excludedSites[index]) && onlySitesWithAdminAccess === this.stateFiltered.onlySitesWithAdminAccess && onlySitesWithAtLeastWriteAccess === this.stateFiltered.onlySitesWithAtLeastWriteAccess && siteTypesToExclude.length === this.stateFiltered.siteTypesToExclude.length && siteTypesToExclude.every(
        (val, index) => val === this.stateFiltered.siteTypesToExclude[index]
      );
    }
    loadInitialSites(onlySitesWithAdminAccess = false, sitesToExclude = [], onlySitesWithAtLeastWriteAccess = false, siteTypesToExclude = []) {
      if (this.state.isInitialized && !this.isFiltered(
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude
      )) {
        return Promise.resolve(vue.readonly(this.state.initialSites));
      }
      if (this.stateFiltered.isInitialized && this.matchesCurrentFilteredState(
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude
      )) {
        return Promise.resolve(vue.readonly(this.stateFiltered.initialSites));
      }
      if (this.isFiltered(
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude
      )) {
        return this.searchSite(
          "%",
          onlySitesWithAdminAccess,
          sitesToExclude,
          onlySitesWithAtLeastWriteAccess,
          siteTypesToExclude
        ).then((sites) => {
          this.stateFiltered.isInitialized = true;
          this.stateFiltered.excludedSites = sitesToExclude;
          this.stateFiltered.onlySitesWithAdminAccess = onlySitesWithAdminAccess;
          this.stateFiltered.onlySitesWithAtLeastWriteAccess = onlySitesWithAtLeastWriteAccess;
          this.stateFiltered.siteTypesToExclude = siteTypesToExclude;
          if (sites !== null) {
            this.stateFiltered.initialSites = sites;
          }
          return sites;
        });
      }
      if (this.state.isInitialized) {
        return Promise.resolve(vue.readonly(this.state.initialSites));
      }
      return this.searchSite(
        "%",
        onlySitesWithAdminAccess,
        sitesToExclude,
        onlySitesWithAtLeastWriteAccess,
        siteTypesToExclude
      ).then((sites) => {
        this.state.isInitialized = true;
        if (sites !== null) {
          this.state.initialSites = sites;
        }
        return sites;
      });
    }
    loadSite(idSite) {
      if (idSite === "all") {
        instance$1.updateUrl(__spreadProps(__spreadValues({}, instance$1.urlParsed.value), {
          module: "MultiSites",
          action: "index",
          date: instance$1.parsed.value.date,
          period: instance$1.parsed.value.period
        }));
      } else {
        instance$1.updateUrl(__spreadProps(__spreadValues({}, instance$1.urlParsed.value), {
          segment: "",
          idSite
        }), __spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
          segment: "",
          idSite
        }));
      }
    }
    searchSite(term, onlySitesWithAdminAccess = false, sitesToExclude = [], onlySitesWithAtLeastWriteAccess = false, siteTypesToExclude = []) {
      if (!term) {
        return this.loadInitialSites(
          onlySitesWithAdminAccess,
          sitesToExclude,
          onlySitesWithAtLeastWriteAccess,
          siteTypesToExclude
        );
      }
      if (this.currentRequestAbort) {
        this.currentRequestAbort.abort();
      }
      if (!this.limitRequest) {
        this.limitRequest = AjaxHelper.fetch({ method: "SitesManager.getNumWebsitesToDisplayPerPage" });
      }
      return this.limitRequest.then((response) => {
        const limit = response.value;
        let permission = "view";
        if (onlySitesWithAdminAccess) {
          permission = "admin";
        } else if (onlySitesWithAtLeastWriteAccess) {
          permission = "write";
        }
        this.currentRequestAbort = new AbortController();
        return AjaxHelper.fetch({
          method: "SitesManager.getSitesWithMinimumAccess",
          permission,
          limit,
          pattern: term,
          sitesToExclude,
          siteTypesToExclude
        }, {
          abortController: this.currentRequestAbort,
          abortable: false
        });
      }).then((response) => {
        if (response) {
          return this.processWebsitesList(response);
        }
        return null;
      }).finally(() => {
        this.currentRequestAbort = null;
      });
    }
    processWebsitesList(response) {
      let sites = response;
      if (!sites || !sites.length) {
        return [];
      }
      sites = sites.map((s) => __spreadProps(__spreadValues({}, s), {
        name: s.group ? `[${s.group}] ${s.name}` : s.name
      }));
      sites.sort((lhs, rhs) => {
        if (lhs.name.toLowerCase() < rhs.name.toLowerCase()) {
          return -1;
        }
        return lhs.name.toLowerCase() > rhs.name.toLowerCase() ? 1 : 0;
      });
      return sites;
    }
  }
  const SitesStore$1 = new SitesStore();
  const _sfc_main$v = vue.defineComponent({
    props: {
      href: String,
      allSitesText: String
    },
    emits: ["click"],
    methods: {
      onClick(event) {
        this.$emit("click", event);
      }
    }
  });
  const _hoisted_1$t = ["innerHTML", "href"];
  function _sfc_render$v(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      onClick: _cache[1] || (_cache[1] = ($event) => _ctx.onClick($event)),
      class: "custom_select_all"
    }, [
      vue.createElementVNode("a", {
        onClick: _cache[0] || (_cache[0] = ($event) => $event.preventDefault()),
        innerHTML: _ctx.$sanitize(_ctx.allSitesText),
        tabindex: "4",
        href: _ctx.href
      }, null, 8, _hoisted_1$t)
    ]);
  }
  const AllSitesLink = /* @__PURE__ */ _export_sfc(_sfc_main$v, [["render", _sfc_render$v]]);
  const _sfc_main$u = vue.defineComponent({
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
        default: ""
      },
      allSitesText: {
        type: String,
        default: translate("General_MultiSitesSummary")
      },
      allSitesLocation: {
        type: String,
        default: "bottom"
      },
      placeholder: String,
      defaultToFirstSite: Boolean,
      sitesToExclude: {
        type: Array,
        default: () => []
      },
      onlySitesWithAtLeastWriteAccess: {
        type: Boolean,
        default: false
      },
      siteTypesToExclude: {
        type: Array,
        default: () => []
      }
    },
    emits: ["update:modelValue", "blur"],
    components: {
      AllSitesLink
    },
    directives: {
      FocusAnywhereButHere,
      FocusIf,
      Tooltips
    },
    watch: {
      searchTerm() {
        this.onSearchTermChanged();
      }
    },
    data() {
      return {
        searchTerm: "",
        activeSiteId: `${Matomo.idSite}`,
        showSitesList: false,
        isLoading: false,
        sites: [],
        autocompleteMinSites: parseInt(Matomo.config.autocomplete_min_sites, 10)
      };
    },
    created() {
      this.searchSite = debounce(this.searchSite);
      if (!this.modelValue && Matomo.idSite) {
        this.$emit("update:modelValue", {
          id: Matomo.idSite,
          name: Matomo.helper.htmlDecode(Matomo.siteName)
        });
      }
    },
    mounted() {
      window.initTopControls();
      this.loadInitialSites().then(() => {
        if (this.shouldDefaultToFirstSite) {
          this.$emit("update:modelValue", { id: this.sites[0].idsite, name: this.sites[0].name });
        }
      });
      const shortcutTitle = translate("CoreHome_ShortcutWebsiteSelector");
      Matomo.helper.registerShortcut("w", shortcutTitle, (event) => {
        if (event.altKey) {
          return;
        }
        if (event.preventDefault) {
          event.preventDefault();
        } else {
          event.returnValue = false;
        }
        const selectorLink = this.$refs.selectorLink;
        if (selectorLink) {
          selectorLink.click();
          selectorLink.focus();
        }
      });
    },
    computed: {
      shouldFocusOnSearch() {
        return this.showSitesList && this.autocompleteMinSites <= this.sites.length || this.searchTerm;
      },
      selectorLinkTitle() {
        return this.hasMultipleSites && this.displayedModelValue ? translate("CoreHome_ChangeCurrentWebsite", this.htmlEntities(this.displayedModelValue.name)) : "";
      },
      hasMultipleSites() {
        const initialSites = SitesStore$1.matchesCurrentFilteredState(
          this.onlySitesWithAdminAccess,
          this.sitesToExclude ? this.sitesToExclude : [],
          this.onlySitesWithAtLeastWriteAccess,
          this.siteTypesToExclude ? this.siteTypesToExclude : []
        ) && SitesStore$1.initialSitesFiltered.value && SitesStore$1.initialSitesFiltered.value.length ? SitesStore$1.initialSitesFiltered.value : SitesStore$1.initialSites.value;
        return initialSites && initialSites.length > 1;
      },
      firstSiteName() {
        const initialSites = SitesStore$1.initialSitesFiltered.value && SitesStore$1.initialSitesFiltered.value.length ? SitesStore$1.initialSitesFiltered.value : SitesStore$1.initialSites.value;
        return initialSites && initialSites.length > 0 ? initialSites[0].name : "";
      },
      urlAllSites() {
        const newQuery = instance$1.stringify(__spreadProps(__spreadValues({}, instance$1.urlParsed.value), {
          module: "MultiSites",
          action: "index",
          date: instance$1.parsed.value.date,
          period: instance$1.parsed.value.period
        }));
        return `?${newQuery}`;
      },
      shouldDefaultToFirstSite() {
        var _a2;
        return !((_a2 = this.modelValue) == null ? void 0 : _a2.id) && (!this.hasMultipleSites || this.defaultToFirstSite) && this.sites[0];
      },
      // using an extra computed property in case SiteSelector is used directly
      // in a vue-entry, and there is no parent component with state to respond
      // to update:modelValue events
      displayedModelValue() {
        if (this.modelValue) {
          return this.modelValue;
        }
        if (Matomo.idSite) {
          return {
            id: Matomo.idSite,
            name: Matomo.helper.htmlDecode(Matomo.siteName)
          };
        }
        if (this.shouldDefaultToFirstSite) {
          return { id: this.sites[0].idsite, name: this.sites[0].name };
        }
        return null;
      },
      tooltipContent() {
        return function tooltipContent() {
          const title = $(this).attr("title") || "";
          return Matomo.helper.htmlEntities(title);
        };
      }
    },
    methods: {
      onSearchTermChanged() {
        if (!this.searchTerm) {
          this.isLoading = false;
          this.loadInitialSites();
        } else {
          this.isLoading = true;
          this.searchSite(this.searchTerm);
        }
      },
      onAllSitesClick(event) {
        this.switchSite({ id: "all", name: this.$props.allSitesText }, event);
        this.showSitesList = false;
      },
      switchSite(site, event) {
        const controlKey = navigator.userAgent.indexOf("Mac OS X") !== -1 ? event.metaKey : event.ctrlKey;
        if (event && controlKey && event.target && event.target.href) {
          window.open(event.target.href, "_blank");
          return;
        }
        this.$emit("update:modelValue", { id: site.id, name: site.name });
        if (!this.switchSiteOnSelect || this.activeSiteId === site.id) {
          return;
        }
        SitesStore$1.loadSite(site.id);
      },
      onBlur() {
        this.showSitesList = false;
        this.$emit("blur");
      },
      onClickSelector() {
        if (this.hasMultipleSites) {
          this.showSitesList = !this.showSitesList;
          if (!this.isLoading && !this.searchTerm) {
            this.loadInitialSites();
          }
        }
      },
      onPressEnter(event) {
        if (event.key !== "Enter") {
          return;
        }
        event.preventDefault();
        this.showSitesList = !this.showSitesList;
        if (this.showSitesList && !this.isLoading) {
          this.loadInitialSites();
        }
      },
      getMatchedSiteName(siteName) {
        const index = siteName.toUpperCase().indexOf(this.searchTerm.toUpperCase());
        if (index === -1 || this.isLoading) {
          return this.htmlEntities(siteName);
        }
        const previousPart = this.htmlEntities(siteName.substring(0, index));
        const lastPart = this.htmlEntities(
          siteName.substring(index + this.searchTerm.length)
        );
        return `${previousPart}<span class="autocompleteMatched">${this.searchTerm}</span>${lastPart}`;
      },
      loadInitialSites() {
        return SitesStore$1.loadInitialSites(
          this.onlySitesWithAdminAccess,
          this.sitesToExclude ? this.sitesToExclude : [],
          this.onlySitesWithAtLeastWriteAccess,
          this.siteTypesToExclude ? this.siteTypesToExclude : []
        ).then((sites) => {
          this.sites = sites || [];
        });
      },
      searchSite(term) {
        this.isLoading = true;
        SitesStore$1.searchSite(
          term,
          this.onlySitesWithAdminAccess,
          this.sitesToExclude ? this.sitesToExclude : [],
          this.onlySitesWithAtLeastWriteAccess,
          this.siteTypesToExclude ? this.siteTypesToExclude : []
        ).then((sites) => {
          if (term !== this.searchTerm) {
            return;
          }
          if (sites) {
            this.sites = sites;
          }
        }).finally(() => {
          this.isLoading = false;
        });
      },
      getUrlForSiteId(idSite) {
        const newQuery = instance$1.stringify(__spreadProps(__spreadValues({}, instance$1.urlParsed.value), {
          segment: "",
          idSite
        }));
        const newHash = instance$1.stringify(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
          segment: "",
          idSite
        }));
        return `?${newQuery}#?${newHash}`;
      },
      htmlEntities(v) {
        return Matomo.helper.htmlEntities(v);
      }
    }
  });
  const _hoisted_1$s = ["value", "name"];
  const _hoisted_2$n = ["title"];
  const _hoisted_3$l = ["textContent"];
  const _hoisted_4$g = {
    key: 1,
    class: "placeholder"
  };
  const _hoisted_5$e = { class: "dropdown" };
  const _hoisted_6$b = { class: "custom_select_search" };
  const _hoisted_7$8 = ["placeholder"];
  const _hoisted_8$7 = { key: 0 };
  const _hoisted_9$6 = { class: "custom_select_container" };
  const _hoisted_10$5 = ["onClick"];
  const _hoisted_11$5 = ["innerHTML", "href", "title"];
  const _hoisted_12$4 = { class: "custom_select_ul_list" };
  const _hoisted_13$4 = { class: "noresult" };
  const _hoisted_14$3 = { key: 1 };
  function _sfc_render$u(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2, _b, _c, _d;
    const _component_AllSitesLink = vue.resolveComponent("AllSitesLink");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    const _directive_focus_if = vue.resolveDirective("focus-if");
    const _directive_focus_anywhere_but_here = vue.resolveDirective("focus-anywhere-but-here");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["siteSelector piwikSelector borderedControl", { "expanded": _ctx.showSitesList, "disabled": !_ctx.hasMultipleSites }])
    }, [
      _ctx.name ? (vue.openBlock(), vue.createElementBlock("input", {
        key: 0,
        type: "hidden",
        value: (_a2 = _ctx.displayedModelValue) == null ? void 0 : _a2.id,
        name: _ctx.name
      }, null, 8, _hoisted_1$s)) : vue.createCommentVNode("", true),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
        ref: "selectorLink",
        onClick: _cache[0] || (_cache[0] = (...args) => _ctx.onClickSelector && _ctx.onClickSelector(...args)),
        onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.onPressEnter($event)),
        href: "javascript:void(0)",
        class: vue.normalizeClass([{ "loading": _ctx.isLoading }, "title"]),
        tabindex: "4",
        title: _ctx.selectorLinkTitle
      }, [
        vue.createElementVNode("span", null, [
          ((_b = _ctx.displayedModelValue) == null ? void 0 : _b.name) || !_ctx.placeholder ? (vue.openBlock(), vue.createElementBlock("span", {
            key: 0,
            textContent: vue.toDisplayString(((_c = _ctx.displayedModelValue) == null ? void 0 : _c.name) || _ctx.firstSiteName)
          }, null, 8, _hoisted_3$l)) : vue.createCommentVNode("", true),
          !((_d = _ctx.displayedModelValue) == null ? void 0 : _d.name) && _ctx.placeholder ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$g, vue.toDisplayString(_ctx.placeholder), 1)) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("span", {
          class: vue.normalizeClass(["icon icon-chevron-down", { "iconHidden": _ctx.isLoading, "collapsed": !_ctx.showSitesList }])
        }, null, 2)
      ], 42, _hoisted_2$n)), [
        [_directive_tooltips]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_5$e, [
        vue.withDirectives(vue.createElementVNode("div", _hoisted_6$b, [
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            onClick: _cache[2] || (_cache[2] = ($event) => {
              _ctx.searchTerm = "";
              _ctx.loadInitialSites();
            }),
            "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.searchTerm = $event),
            tabindex: "4",
            class: "websiteSearch inp browser-default",
            placeholder: _ctx.translate("General_Search")
          }, null, 8, _hoisted_7$8), [
            [vue.vModelText, _ctx.searchTerm],
            [_directive_focus_if, { focused: _ctx.shouldFocusOnSearch }]
          ]),
          vue.withDirectives(vue.createElementVNode("img", {
            title: "Clear",
            onClick: _cache[4] || (_cache[4] = ($event) => {
              _ctx.searchTerm = "";
              _ctx.loadInitialSites();
            }),
            class: "reset",
            src: "plugins/CoreHome/images/reset_search.png"
          }, null, 512), [
            [vue.vShow, _ctx.searchTerm]
          ])
        ], 512), [
          [vue.vShow, _ctx.autocompleteMinSites <= _ctx.sites.length || _ctx.searchTerm]
        ]),
        _ctx.allSitesLocation === "top" && _ctx.showAllSitesItem ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$7, [
          vue.createVNode(_component_AllSitesLink, {
            href: _ctx.urlAllSites,
            "all-sites-text": _ctx.allSitesText,
            onClick: _cache[5] || (_cache[5] = ($event) => _ctx.onAllSitesClick($event))
          }, null, 8, ["href", "all-sites-text"])
        ])) : vue.createCommentVNode("", true),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_9$6, [
          vue.createElementVNode("ul", {
            class: "custom_select_ul_list",
            onClick: _cache[7] || (_cache[7] = ($event) => _ctx.showSitesList = false)
          }, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.sites, (site, index) => {
              return vue.withDirectives((vue.openBlock(), vue.createElementBlock("li", {
                onClick: ($event) => _ctx.switchSite(__spreadProps(__spreadValues({}, site), { id: site.idsite }), $event),
                key: index
              }, [
                vue.createElementVNode("a", {
                  onClick: _cache[6] || (_cache[6] = ($event) => $event.preventDefault()),
                  innerHTML: _ctx.$sanitize(_ctx.getMatchedSiteName(site.name)),
                  tabindex: "4",
                  href: _ctx.getUrlForSiteId(site.idsite),
                  title: site.name
                }, null, 8, _hoisted_11$5)
              ], 8, _hoisted_10$5)), [
                [vue.vShow, !(!_ctx.showSelectedSite && `${_ctx.activeSiteId}` === `${site.idsite}`)]
              ]);
            }), 128))
          ]),
          vue.withDirectives(vue.createElementVNode("ul", _hoisted_12$4, [
            vue.createElementVNode("li", null, [
              vue.createElementVNode("div", _hoisted_13$4, vue.toDisplayString(_ctx.translate("SitesManager_NotFound") + " " + _ctx.searchTerm), 1)
            ])
          ], 512), [
            [vue.vShow, !_ctx.sites.length && _ctx.searchTerm]
          ])
        ])), [
          [_directive_tooltips, { content: _ctx.tooltipContent }]
        ]),
        _ctx.allSitesLocation === "bottom" && _ctx.showAllSitesItem ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14$3, [
          vue.createVNode(_component_AllSitesLink, {
            href: _ctx.urlAllSites,
            "all-sites-text": _ctx.allSitesText,
            onClick: _cache[8] || (_cache[8] = ($event) => _ctx.onAllSitesClick($event))
          }, null, 8, ["href", "all-sites-text"])
        ])) : vue.createCommentVNode("", true)
      ], 512), [
        [vue.vShow, _ctx.showSitesList]
      ])
    ], 2)), [
      [_directive_focus_anywhere_but_here, { blur: _ctx.onBlur }]
    ]);
  }
  const SiteSelector = /* @__PURE__ */ _export_sfc(_sfc_main$u, [["render", _sfc_render$u]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class ReportingPagesStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        pages: []
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "fetchAllPagesPromise");
      __publicField(this, "pages", vue.computed(() => this.state.value.pages));
    }
    findPageInCategory(categoryId) {
      return this.pages.value.find((p) => p && p.category && p.category.id === categoryId && p.subcategory && p.subcategory.id);
    }
    findPage(categoryId, subcategoryId) {
      return this.pages.value.find((p) => p && p.category && p.subcategory && p.category.id === categoryId && `${p.subcategory.id}` === subcategoryId);
    }
    reloadAllPages() {
      delete this.fetchAllPagesPromise;
      return this.getAllPages();
    }
    getAllPages() {
      if (!this.fetchAllPagesPromise) {
        this.fetchAllPagesPromise = AjaxHelper.fetch({
          method: "API.getReportPagesMetadata",
          filter_limit: "-1"
        }).then((response) => {
          this.privateState.pages = response;
          return this.pages.value;
        });
      }
      return this.fetchAllPagesPromise.then(() => this.pages.value);
    }
  }
  const ReportingPagesStoreInstance = new ReportingPagesStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function sortOrderables(menu) {
    const result = [...menu || []];
    result.sort((lhs, rhs) => {
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
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function getCategoryChildren(category) {
    const container = category;
    if (container.subcategories) {
      return container.subcategories;
    }
    return [];
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function getSubcategoryChildren(subcategory) {
    const container = subcategory;
    if (container.subcategories) {
      return container.subcategories;
    }
    return [];
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function isNumeric(text) {
    const n = parseFloat(text);
    return !Number.isNaN(n) && Number.isFinite(n);
  }
  const DEFAULT_GROUP = "";
  function getCategoryGroupIds(category) {
    const { groups } = category;
    return groups && groups.length ? groups : [DEFAULT_GROUP];
  }
  class ReportingMenuStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        activeSubcategoryId: null,
        activeSubsubcategoryId: null
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "activeCategory", vue.computed(
        () => typeof this.state.value.activeCategoryId !== "undefined" ? this.state.value.activeCategoryId : instance$1.parsed.value.category
      ));
      __publicField(this, "activeSubcategory", vue.computed(
        () => this.state.value.activeSubcategoryId || instance$1.parsed.value.subcategory
      ));
      __publicField(this, "activeSubsubcategory", vue.computed(() => {
        const manuallySetId = this.state.value.activeSubsubcategoryId;
        if (manuallySetId) {
          return manuallySetId;
        }
        const foundCategory = this.findSubcategory(
          this.activeCategory.value,
          this.activeSubcategory.value
        );
        if (foundCategory.subsubcategory && foundCategory.subsubcategory.id === this.activeSubcategory.value) {
          return foundCategory.subsubcategory.id;
        }
        return null;
      }));
      __publicField(this, "menu", vue.computed(() => this.buildMenuFromPages(
        instance$1.parsed.value.group || DEFAULT_GROUP
      )));
      /**
       * The full reporting menu across all top-level sections (groups), ignoring the active group
       * filter. Used by quick search so users can find any reporting page regardless of the section
       * they are currently in.
       */
      __publicField(this, "fullMenu", vue.computed(() => this.buildMenuFromPages(null)));
    }
    fetchMenuItems() {
      return ReportingPagesStoreInstance.getAllPages().then(() => this.menu.value);
    }
    reloadMenuItems() {
      return ReportingPagesStoreInstance.reloadAllPages().then(() => this.menu.value);
    }
    findSubcategory(categoryId, subcategoryId) {
      let foundCategory = void 0;
      let foundSubcategory = void 0;
      let foundSubSubcategory = void 0;
      this.menu.value.forEach((category) => {
        if (category.id !== categoryId) {
          return;
        }
        (getCategoryChildren(category) || []).forEach((subcategory) => {
          if (subcategory.id === subcategoryId) {
            foundCategory = category;
            foundSubcategory = subcategory;
          }
          if (subcategory.isGroup) {
            (getSubcategoryChildren(subcategory) || []).forEach((subcat) => {
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
    /**
     * Builds the reporting menu from the available pages. When `activeGroup` is a string, only
     * categories belonging to that top-level section ("Analytics", "AI Insights", ...) are included;
     * passing `null` returns the full menu across all sections (used by quick search).
     */
    buildMenuFromPages(activeGroup) {
      const menu = [];
      const displayedCategory = instance$1.parsed.value.category;
      const displayedSubcategory = instance$1.parsed.value.subcategory;
      const pages = ReportingPagesStoreInstance.pages.value;
      const categoriesHandled = {};
      pages.forEach((page) => {
        const category = __spreadValues({}, page.category);
        const categoryId = category.id;
        const isCategoryDisplayed = categoryId === displayedCategory;
        if (categoriesHandled[categoryId]) {
          return;
        }
        if (activeGroup !== null && !getCategoryGroupIds(category).includes(activeGroup)) {
          return;
        }
        categoriesHandled[categoryId] = true;
        category.subcategories = [];
        let categoryGroups = null;
        const pagesWithCategory = pages.filter((p) => p.category.id === categoryId);
        pagesWithCategory.forEach((p) => {
          const subcategory = __spreadValues({}, p.subcategory);
          const isSubcategoryDisplayed = subcategory.id === displayedSubcategory && isCategoryDisplayed;
          if (p.widgets && p.widgets[0] && isNumeric(p.subcategory.id)) {
            if (!categoryGroups) {
              categoryGroups = __spreadValues({}, subcategory);
              categoryGroups.name = translate("CoreHome_ChooseX", [category.name]);
              categoryGroups.isGroup = true;
              categoryGroups.subcategories = [];
              categoryGroups.order = 10;
            }
            if (isSubcategoryDisplayed) {
              categoryGroups.name = subcategory.name;
            }
            const entityId = subcategory.id;
            subcategory.tooltip = `${subcategory.name} (id = ${entityId})`;
            categoryGroups.subcategories.push(subcategory);
            return;
          }
          category.subcategories.push(subcategory);
        });
        if (categoryGroups && categoryGroups.subcategories && categoryGroups.subcategories.length <= 5) {
          categoryGroups.subcategories.forEach(
            (sub) => category.subcategories.push(sub)
          );
        } else if (categoryGroups) {
          category.subcategories.push(categoryGroups);
        }
        category.subcategories = sortOrderables(getCategoryChildren(category));
        menu.push(category);
      });
      return sortOrderables(menu);
    }
    toggleCategory(category) {
      this.privateState.activeSubcategoryId = null;
      this.privateState.activeSubsubcategoryId = null;
      if (this.activeCategory.value === category.id) {
        this.privateState.activeCategoryId = null;
        return false;
      }
      this.privateState.activeCategoryId = category.id;
      return true;
    }
    enterSubcategory(category, subcategory, subsubcategory) {
      if (!category || !subcategory) {
        return;
      }
      this.privateState.activeCategoryId = category.id;
      this.privateState.activeSubcategoryId = subcategory.id;
      if (subsubcategory) {
        this.privateState.activeSubsubcategoryId = subsubcategory.id;
      }
    }
  }
  const ReportingMenuStoreInstance = new ReportingMenuStore();
  const { ListingFormatter } = window;
  function isElementInViewport(element) {
    const rect = element.getBoundingClientRect();
    const $window = window.$(window);
    return rect.top >= 0 && rect.left >= 0 && rect.bottom <= $window.height() && rect.right <= $window.width();
  }
  function scrollFirstElementIntoView(element) {
    if (element && element.scrollIntoView) {
      element.scrollIntoView();
    }
  }
  const _sfc_main$t = vue.defineComponent({
    name: "QuickAccess",
    directives: {
      FocusAnywhereButHere,
      FocusIf,
      Tooltips
    },
    watch: {
      searchActive(newValue) {
        const root = this.$refs.root;
        if (!root || !root.parentElement) {
          return;
        }
        const classes = root.parentElement.classList;
        classes.toggle("active", newValue);
        classes.toggle("expanded", newValue);
      },
      reportingGroup() {
        this.topMenuItems = null;
        this.leftMenuItems = null;
        this.segmentItems = null;
        this.deactivateSearch();
      }
    },
    mounted() {
      const root = this.$refs.root;
      if (root && root.parentElement) {
        root.parentElement.classList.add("quick-access", "piwikSelector");
      }
      Matomo.helper.registerShortcut("f", translate("CoreHome_ShortcutSearch"), (event) => {
        if (event.altKey) {
          return;
        }
        event.preventDefault();
        const mobileMenuTrigger = document.querySelector("nav .activateLeftMenu");
        if (mobileMenuTrigger && window.$(mobileMenuTrigger).is(":visible")) {
          openMobileLeftMenu();
        }
        scrollFirstElementIntoView(this.$refs.root);
        this.activateSearch();
      });
    },
    data() {
      const hasSegmentSelector = !!document.querySelector(".segmentEditorPanel");
      return {
        menuItems: [],
        numMenuItems: 0,
        searchActive: false,
        searchTerm: "",
        searchIndex: 0,
        menuIndexCounter: -1,
        topMenuItems: null,
        leftMenuItems: null,
        segmentItems: null,
        hasSegmentSelector,
        sites: [],
        isLoading: false
      };
    },
    created() {
      this.searchMenu = debounce(this.searchMenu.bind(this));
    },
    computed: {
      reportingGroup() {
        return instance$1.parsed.value.group || DEFAULT_GROUP;
      },
      hasSitesSelector() {
        return !!document.querySelector(
          '.top_controls .siteSelector,.top_controls [vue-entry="CoreHome.SiteSelector"]'
        );
      },
      quickAccessTitle() {
        const searchAreas = [translate("CoreHome_MenuEntries")];
        if (this.hasSegmentSelector) {
          searchAreas.push(translate("CoreHome_Segments"));
        }
        if (this.hasSitesSelector) {
          searchAreas.push(translate("SitesManager_Sites"));
        }
        return translate("CoreHome_QuickAccessTitle", ListingFormatter.formatAnd(searchAreas));
      }
    },
    emits: ["itemSelected", "blur"],
    methods: {
      onKeypress(event) {
        const areSearchResultsDisplayed = this.searchTerm && this.searchActive;
        const isTabKey = event.which === 9;
        const isEscKey = event.which === 27;
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
        } else if (isTabKey) {
          this.searchActive = false;
        } else {
          setTimeout(() => {
            this.searchActive = true;
            this.searchMenu(this.searchTerm);
          });
        }
      },
      highlightPreviousItem() {
        const currentIndex = Number(this.searchIndex);
        if (currentIndex - 1 < 0) {
          this.searchIndex = 0;
        } else {
          this.searchIndex = currentIndex - 1;
        }
        this.makeSureSelectedItemIsInViewport();
      },
      highlightNextItem() {
        const numTotal = this.$refs.root.querySelectorAll("li.result").length;
        const currentIndex = Number(this.searchIndex);
        if (numTotal <= currentIndex + 1) {
          this.searchIndex = numTotal - 1;
        } else {
          this.searchIndex = currentIndex + 1;
        }
        this.makeSureSelectedItemIsInViewport();
      },
      clickQuickAccessMenuItem() {
        const selectedMenuElement = this.getCurrentlySelectedElement();
        if (selectedMenuElement) {
          setTimeout(() => {
            selectedMenuElement.click();
            this.$emit("itemSelected", selectedMenuElement);
          }, 20);
        }
      },
      deactivateSearch() {
        this.searchTerm = "";
        this.searchActive = false;
        if (this.$refs.input) {
          this.$refs.input.blur();
        }
      },
      makeSureSelectedItemIsInViewport() {
        const element = this.getCurrentlySelectedElement();
        if (element && !isElementInViewport(element)) {
          scrollFirstElementIntoView(element);
        }
      },
      getCurrentlySelectedElement() {
        const results = this.$refs.root.querySelectorAll("li.result");
        if (results && results.length && results.item(Number(this.searchIndex))) {
          return results.item(Number(this.searchIndex));
        }
        return void 0;
      },
      searchMenu(unprocessedSearchTerm) {
        const searchTerm = unprocessedSearchTerm.toLowerCase();
        let index = -1;
        const menuItemsIndex = {};
        const menuItems = [];
        const moveToCategory = (theSubmenuItem) => {
          const submenuItem = __spreadValues({}, theSubmenuItem);
          index += 1;
          submenuItem.menuIndex = index;
          const { category } = submenuItem;
          if (!(category in menuItemsIndex)) {
            menuItems.push({ title: category, items: [] });
            menuItemsIndex[category] = menuItems.length - 1;
          }
          const indexOfCategory = menuItemsIndex[category];
          menuItems[indexOfCategory].items.push(submenuItem);
        };
        this.resetSearchIndex();
        if (this.hasSitesSelector) {
          this.isLoading = true;
          SitesStore$1.searchSite(searchTerm).then((sites) => {
            if (sites) {
              this.sites = sites;
            }
          }).finally(() => {
            this.isLoading = false;
          });
        }
        const menuItemMatches = (i) => i.name.toLowerCase().indexOf(searchTerm) !== -1 || i.category.toLowerCase().indexOf(searchTerm) !== -1;
        if (this.topMenuItems === null) {
          this.topMenuItems = this.getTopMenuItems();
        }
        if (this.leftMenuItems === null) {
          this.leftMenuItems = this.getLeftMenuItems();
        }
        if (this.segmentItems === null) {
          this.segmentItems = this.getSegmentItems();
        }
        const topMenuItems = this.topMenuItems.filter(menuItemMatches);
        const leftMenuItems = this.leftMenuItems.filter(menuItemMatches);
        const segmentItems = this.segmentItems.filter(menuItemMatches);
        const otherGroupItems = this.getReportingMenuItemsFromOtherGroups().filter(menuItemMatches);
        topMenuItems.forEach(moveToCategory);
        leftMenuItems.forEach(moveToCategory);
        segmentItems.forEach(moveToCategory);
        otherGroupItems.forEach(moveToCategory);
        this.numMenuItems = topMenuItems.length + leftMenuItems.length + segmentItems.length + otherGroupItems.length;
        this.menuItems = menuItems;
      },
      resetSearchIndex() {
        this.searchIndex = 0;
        this.makeSureSelectedItemIsInViewport();
      },
      selectSite(idSite) {
        this.deactivateSearch();
        closeMobileLeftMenu();
        SitesStore$1.loadSite(idSite);
      },
      selectMenuItem(submenuEntry) {
        if (submenuEntry.page) {
          this.navigateToReportingPage(submenuEntry.page);
          return;
        }
        const target = document.querySelector(`[quick_access='${submenuEntry.index}']`);
        if (target) {
          this.deactivateSearch();
          closeMobileLeftMenu();
          const href = target.getAttribute("href");
          if (href && href.length > 10 && target && target.click) {
            try {
              target.click();
            } catch (e) {
              window.$(target).click();
            }
          } else {
            window.$(target).click();
          }
        }
      },
      onBlur() {
        this.searchActive = false;
        this.$emit("blur");
      },
      activateSearch() {
        this.searchActive = true;
      },
      getTopMenuItems() {
        const category = translate("CoreHome_Menu");
        const topMenuItems = [];
        document.querySelectorAll("nav .sidenav li > a, nav .sidenav li > div > a").forEach((element) => {
          var _a2, _b;
          let text = (_a2 = element.textContent) == null ? void 0 : _a2.trim();
          if (!text || element.parentElement != null && element.parentElement.tagName != null && element.parentElement.tagName === "DIV") {
            text = ((_b = element.getAttribute("title")) == null ? void 0 : _b.trim()) || "";
          }
          if (text) {
            topMenuItems.push({ name: text, index: this.menuIndexCounter += 1, category });
            element.setAttribute("quick_access", `${this.menuIndexCounter}`);
          }
        });
        return topMenuItems;
      },
      getLeftMenuItems() {
        const leftMenuItems = [];
        document.querySelectorAll("#secondNavBar .menuTab").forEach((element) => {
          var _a2;
          const categoryElement = window.$(element).find("> .item");
          let category = ((_a2 = categoryElement[0]) == null ? void 0 : _a2.innerText.trim()) || "";
          if (category && category.lastIndexOf("\n") !== -1) {
            category = category.slice(0, category.lastIndexOf("\n")).trim();
          }
          window.$(element).find("li .item").each((i, subElement) => {
            var _a3;
            const text = (_a3 = subElement.textContent) == null ? void 0 : _a3.trim();
            if (text) {
              leftMenuItems.push({ name: text, category, index: this.menuIndexCounter += 1 });
              subElement.setAttribute("quick_access", `${this.menuIndexCounter}`);
            }
          });
        });
        return leftMenuItems;
      },
      getSegmentItems() {
        if (!this.hasSegmentSelector) {
          return [];
        }
        const category = translate("CoreHome_Segments");
        const segmentItems = [];
        document.querySelectorAll(".segmentList [data-idsegment]").forEach((element) => {
          var _a2, _b;
          const text = (_b = (_a2 = element.querySelector(".segname")) == null ? void 0 : _a2.textContent) == null ? void 0 : _b.trim();
          if (text) {
            segmentItems.push({ name: text, category, index: this.menuIndexCounter += 1 });
            element.setAttribute("quick_access", `${this.menuIndexCounter}`);
          }
        });
        return segmentItems;
      },
      getReportingMenuItemsFromOtherGroups() {
        if (!ReportingPagesStoreInstance.pages.value.length) {
          return [];
        }
        const activeGroup = instance$1.parsed.value.group || DEFAULT_GROUP;
        const items = [];
        ReportingMenuStoreInstance.fullMenu.value.forEach((category) => {
          const groups = getCategoryGroupIds(category);
          if (groups.includes(activeGroup)) {
            return;
          }
          const navGroup = groups[0];
          const addItem = (subcategory) => {
            var _a2;
            const name = (_a2 = subcategory.name) == null ? void 0 : _a2.trim();
            if (!name) {
              return;
            }
            items.push({
              name,
              category: category.name,
              index: this.menuIndexCounter += 1,
              page: { category: category.id, subcategory: subcategory.id, group: navGroup }
            });
          };
          getCategoryChildren(category).forEach((subcategory) => {
            if (subcategory.isGroup) {
              getSubcategoryChildren(subcategory).forEach(addItem);
            } else {
              addItem(subcategory);
            }
          });
        });
        return items;
      },
      navigateToReportingPage(page) {
        this.deactivateSearch();
        closeMobileLeftMenu();
        const {
          idSite,
          period,
          date,
          segment,
          comparePeriods,
          compareDates,
          compareSegments
        } = instance$1.parsed.value;
        const params = {
          idSite,
          period,
          date,
          segment,
          comparePeriods,
          compareDates,
          compareSegments,
          category: page.category,
          subcategory: page.subcategory
        };
        if (page.group) {
          params.group = page.group;
        }
        instance$1.updateHash(params);
      }
    }
  });
  const _hoisted_1$r = {
    ref: "root",
    class: "quickAccessInside"
  };
  const _hoisted_2$m = ["title", "placeholder"];
  const _hoisted_3$k = { class: "dropdown quickAccessDropdown" };
  const _hoisted_4$f = { class: "no-result" };
  const _hoisted_5$d = ["onClick"];
  const _hoisted_6$a = ["onMouseenter", "onClick"];
  const _hoisted_7$7 = { class: "quickAccessMatomoSearch" };
  const _hoisted_8$6 = ["onMouseenter", "onClick"];
  const _hoisted_9$5 = ["textContent"];
  const _hoisted_10$4 = { class: "quick-access-category helpCategory" };
  const _hoisted_11$4 = ["href"];
  function _sfc_render$t(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_focus_if = vue.resolveDirective("focus-if");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    const _directive_focus_anywhere_but_here = vue.resolveDirective("focus-anywhere-but-here");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$r, [
      vue.createElementVNode("span", {
        class: "icon-search",
        onMouseenter: _cache[0] || (_cache[0] = ($event) => _ctx.searchActive = true)
      }, null, 32),
      vue.withDirectives(vue.createElementVNode("input", {
        class: "quickAccessInput browser-default",
        onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.onKeypress($event)),
        onFocus: _cache[2] || (_cache[2] = ($event) => _ctx.searchActive = true),
        "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.searchTerm = $event),
        type: "text",
        tabindex: "5",
        title: _ctx.quickAccessTitle,
        placeholder: _ctx.translate("General_Search"),
        ref: "input"
      }, null, 40, _hoisted_2$m), [
        [vue.vModelText, _ctx.searchTerm],
        [_directive_focus_if, { focused: _ctx.searchActive }],
        [_directive_tooltips]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_3$k, [
        vue.withDirectives(vue.createElementVNode("ul", null, [
          vue.createElementVNode("li", _hoisted_4$f, vue.toDisplayString(_ctx.translate("General_SearchNoResults")), 1)
        ], 512), [
          [vue.vShow, !(_ctx.numMenuItems > 0 || _ctx.sites.length)]
        ]),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.menuItems, (subcategory) => {
          return vue.openBlock(), vue.createElementBlock("ul", {
            key: subcategory.title
          }, [
            vue.createElementVNode("li", {
              class: "quick-access-category",
              onClick: ($event) => {
                _ctx.searchTerm = subcategory.title;
                _ctx.searchMenu(_ctx.searchTerm);
              }
            }, vue.toDisplayString(subcategory.title), 9, _hoisted_5$d),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(subcategory.items, (submenuEntry) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                class: vue.normalizeClass(["result", { selected: submenuEntry.menuIndex === _ctx.searchIndex }]),
                onMouseenter: ($event) => {
                  var _a2;
                  return _ctx.searchIndex = (_a2 = submenuEntry.menuIndex) != null ? _a2 : 0;
                },
                onClick: ($event) => _ctx.selectMenuItem(submenuEntry),
                key: submenuEntry.index
              }, [
                vue.createElementVNode("a", null, vue.toDisplayString(submenuEntry.name.trim()), 1)
              ], 42, _hoisted_6$a);
            }), 128))
          ]);
        }), 128)),
        vue.createElementVNode("ul", _hoisted_7$7, [
          vue.withDirectives(vue.createElementVNode("li", { class: "quick-access-category websiteCategory" }, vue.toDisplayString(_ctx.translate("SitesManager_Sites")), 513), [
            [vue.vShow, _ctx.hasSitesSelector && _ctx.sites.length || _ctx.isLoading]
          ]),
          vue.withDirectives(vue.createElementVNode("li", { class: "no-result" }, vue.toDisplayString(_ctx.translate("MultiSites_LoadingWebsites")), 513), [
            [vue.vShow, _ctx.hasSitesSelector && _ctx.isLoading]
          ]),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.sites, (site, index) => {
            return vue.withDirectives((vue.openBlock(), vue.createElementBlock("li", {
              class: vue.normalizeClass(["result", { selected: _ctx.numMenuItems + index === _ctx.searchIndex }]),
              onMouseenter: ($event) => _ctx.searchIndex = _ctx.numMenuItems + index,
              onClick: ($event) => _ctx.selectSite(site.idsite),
              key: site.idsite
            }, [
              vue.createElementVNode("a", {
                textContent: vue.toDisplayString(site.name)
              }, null, 8, _hoisted_9$5)
            ], 42, _hoisted_8$6)), [
              [vue.vShow, _ctx.hasSitesSelector && !_ctx.isLoading]
            ]);
          }), 128))
        ]),
        vue.createElementVNode("ul", null, [
          vue.createElementVNode("li", _hoisted_10$4, vue.toDisplayString(_ctx.translate("General_HelpResources")), 1),
          vue.createElementVNode("li", {
            class: vue.normalizeClass([{ selected: _ctx.searchIndex === "help" }, "quick-access-help"]),
            onMouseenter: _cache[4] || (_cache[4] = ($event) => _ctx.searchIndex = "help")
          }, [
            vue.createElementVNode("a", {
              href: `https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=QuickSearch&s=${encodeURIComponent(_ctx.searchTerm)}`,
              target: "_blank"
            }, vue.toDisplayString(_ctx.translate("CoreHome_SearchOnMatomo", _ctx.searchTerm)), 9, _hoisted_11$4)
          ], 34)
        ])
      ], 512), [
        [vue.vShow, _ctx.searchTerm && _ctx.searchActive]
      ])
    ])), [
      [_directive_focus_anywhere_but_here, { blur: _ctx.onBlur }]
    ]);
  }
  const QuickAccess = /* @__PURE__ */ _export_sfc(_sfc_main$t, [["render", _sfc_render$t]]);
  const _sfc_main$s = vue.defineComponent({
    name: "SearchInput",
    inheritAttrs: false,
    props: {
      modelValue: {
        type: String,
        required: true
      },
      placeholder: {
        type: String,
        default: ""
      },
      showClear: {
        type: Boolean,
        default: false
      }
    },
    emits: ["update:modelValue"],
    computed: {
      resolvedPlaceholder() {
        return this.placeholder || translate("General_Search");
      }
    },
    methods: {
      translate,
      onInput(event) {
        this.$emit("update:modelValue", event.target.value);
      },
      onClear() {
        this.$emit("update:modelValue", "");
      }
    }
  });
  const _hoisted_1$q = { class: "searchInputContainer" };
  const _hoisted_2$l = ["value", "placeholder"];
  function _sfc_render$s(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$q, [
      _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-search" }, null, -1)),
      vue.createElementVNode("input", vue.mergeProps({
        class: "searchInputField browser-default",
        type: "text",
        value: _ctx.modelValue,
        placeholder: _ctx.resolvedPlaceholder
      }, _ctx.$attrs, {
        onInput: _cache[0] || (_cache[0] = ($event) => _ctx.onInput($event))
      }), null, 16, _hoisted_2$l),
      _ctx.showClear && _ctx.modelValue ? (vue.openBlock(), vue.createElementBlock("button", {
        key: 0,
        type: "button",
        class: "searchInputClear",
        onClick: _cache[1] || (_cache[1] = ($event) => _ctx.onClear())
      })) : vue.createCommentVNode("", true)
    ]);
  }
  const SearchInput = /* @__PURE__ */ _export_sfc(_sfc_main$s, [["render", _sfc_render$s]]);
  const Field$4 = useExternalPluginComponent("CorePluginsAdmin", "Field");
  const _sfc_main$r = vue.defineComponent({
    props: {
      modelValue: Array,
      name: String,
      id: String,
      field: {
        type: Object,
        required: true
      },
      rows: String
    },
    components: {
      Field: Field$4
    },
    emits: ["update:modelValue"],
    watch: {
      modelValue(newValue) {
        this.checkEmptyModelValue(newValue);
      }
    },
    mounted() {
      this.checkEmptyModelValue(this.modelValue);
    },
    methods: {
      checkEmptyModelValue(newValue) {
        if ((!newValue || !newValue.length || newValue.slice(-1)[0] !== "") && (!this.rows || (this.modelValue || []).length < parseInt(this.rows, 10))) {
          this.$emit("update:modelValue", [...newValue || [], ""]);
        }
      },
      onEntryChange(newValue, index) {
        const newArrayValue = [...this.modelValue || []];
        newArrayValue[index] = newValue;
        this.$emit("update:modelValue", newArrayValue);
      },
      removeEntry(index) {
        if (index > -1 && this.modelValue) {
          const newValue = this.modelValue.filter((x, i) => i !== index);
          this.$emit("update:modelValue", newValue);
        }
      }
    }
  });
  const _hoisted_1$p = { class: "fieldArray form-group" };
  const _hoisted_2$k = {
    key: 0,
    class: "fieldUiControl"
  };
  const _hoisted_3$j = ["onClick", "title"];
  function _sfc_render$r(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$p, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.modelValue, (item, index) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          class: vue.normalizeClass(["fieldArrayTable multiple valign-wrapper", { [`fieldArrayTable${index}`]: true }]),
          key: index
        }, [
          _ctx.field.uiControl ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$k, [
            vue.createVNode(_component_Field, {
              "full-width": true,
              "model-value": item,
              options: _ctx.field.availableValues,
              "onUpdate:modelValue": ($event) => _ctx.onEntryChange($event, index),
              "model-modifiers": _ctx.field.modelModifiers,
              placeholder: " ",
              uicontrol: _ctx.field.uiControl,
              title: _ctx.field.title,
              name: `${_ctx.name}-${index}`,
              id: `${_ctx.id}-${index}`,
              "template-file": _ctx.field.templateFile,
              component: _ctx.field.component
            }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "title", "name", "id", "template-file", "component"])
          ])) : vue.createCommentVNode("", true),
          vue.withDirectives(vue.createElementVNode("span", {
            onClick: ($event) => _ctx.removeEntry(index),
            class: "icon-minus valign",
            title: _ctx.translate("General_Remove")
          }, null, 8, _hoisted_3$j), [
            [vue.vShow, index + 1 !== (_ctx.modelValue || []).length]
          ])
        ], 2);
      }), 128))
    ]);
  }
  const FieldArray = /* @__PURE__ */ _export_sfc(_sfc_main$r, [["render", _sfc_render$r]]);
  const Field$3 = useExternalPluginComponent("CorePluginsAdmin", "Field");
  const _sfc_main$q = vue.defineComponent({
    props: {
      modelValue: Array,
      name: String,
      id: String,
      field1: Object,
      field2: Object,
      field3: Object,
      field4: Object,
      rows: Number
    },
    components: {
      Field: Field$3
    },
    computed: {
      fieldCount() {
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
    emits: ["update:modelValue"],
    watch: {
      modelValue(newValue) {
        this.checkEmptyModelValue(newValue);
      }
    },
    mounted() {
      this.checkEmptyModelValue(this.modelValue);
    },
    methods: {
      checkEmptyModelValue(newValue) {
        if ((!newValue || !newValue.length || this.isEmptyValue(newValue.slice(-1)[0])) && (!this.rows || this.modelValue.length < this.rows)) {
          this.$emit("update:modelValue", [...newValue || [], this.makeEmptyValue()]);
        }
      },
      onEntryChange(index, key, newValue) {
        const newWholeValue = [...this.modelValue];
        newWholeValue[index] = __spreadProps(__spreadValues({}, newWholeValue[index]), { [key]: newValue });
        this.$emit("update:modelValue", newWholeValue);
      },
      removeEntry(index) {
        if (index > -1 && this.modelValue) {
          const newValue = this.modelValue.filter((x, i) => i !== index);
          this.$emit("update:modelValue", newValue);
        }
      },
      isEmptyValue(value) {
        const { fieldCount } = this;
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
      makeEmptyValue() {
        const result = {};
        if (this.field1 && this.field1.key) {
          result[this.field1.key] = "";
        }
        if (this.field2 && this.field2.key) {
          result[this.field2.key] = "";
        }
        if (this.field3 && this.field3.key) {
          result[this.field3.key] = "";
        }
        if (this.field4 && this.field4.key) {
          result[this.field4.key] = "";
        }
        return result;
      }
    }
  });
  const _hoisted_1$o = { class: "multiPairField form-group" };
  const _hoisted_2$j = {
    key: 1,
    class: "fieldUiControl fieldUiControl2"
  };
  const _hoisted_3$i = {
    key: 2,
    class: "fieldUiControl fieldUiControl3"
  };
  const _hoisted_4$e = {
    key: 3,
    class: "fieldUiControl fieldUiControl4"
  };
  const _hoisted_5$c = ["onClick", "title"];
  function _sfc_render$q(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$o, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.modelValue, (item, index) => {
        var _a2;
        return vue.openBlock(), vue.createElementBlock("div", {
          class: vue.normalizeClass(["multiPairFieldTable multiple valign-wrapper", { [`multiPairFieldTable${index}`]: true, [`has${_ctx.fieldCount}Fields`]: true }]),
          key: index
        }, [
          _ctx.field1 ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 0,
            class: vue.normalizeClass(["fieldUiControl fieldUiControl1", { hasMultiFields: _ctx.field1.type && ((_a2 = _ctx.field2) == null ? void 0 : _a2.type) }])
          }, [
            vue.createVNode(_component_Field, {
              "full-width": true,
              "model-value": item[_ctx.field1.key],
              options: _ctx.field1.availableValues,
              "onUpdate:modelValue": ($event) => _ctx.onEntryChange(index, _ctx.field1.key, $event),
              "model-modifiers": _ctx.field1.modelModifiers,
              placeholder: " ",
              uicontrol: _ctx.field1.uiControl,
              name: `${_ctx.name}-p1-${index}`,
              id: `${_ctx.id}-p1-${index}`,
              title: _ctx.field1.title,
              "template-file": _ctx.field1.templateFile,
              component: _ctx.field1.component
            }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "name", "id", "title", "template-file", "component"])
          ], 2)) : vue.createCommentVNode("", true),
          _ctx.field2 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$j, [
            vue.createVNode(_component_Field, {
              "full-width": true,
              options: _ctx.field2.availableValues,
              "onUpdate:modelValue": ($event) => _ctx.onEntryChange(index, _ctx.field2.key, $event),
              "model-value": item[_ctx.field2.key],
              "model-modifiers": _ctx.field2.modelModifiers,
              placeholder: " ",
              uicontrol: _ctx.field2.uiControl,
              name: `${_ctx.name}-p2-${index}`,
              id: `${_ctx.id}-p2-${index}`,
              title: _ctx.field2.title,
              "template-file": _ctx.field2.templateFile,
              component: _ctx.field2.component
            }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "name", "id", "title", "template-file", "component"])
          ])) : vue.createCommentVNode("", true),
          _ctx.field3 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$i, [
            vue.createVNode(_component_Field, {
              "full-width": true,
              options: _ctx.field3.availableValues,
              "onUpdate:modelValue": ($event) => _ctx.onEntryChange(index, _ctx.field3.key, $event),
              "model-value": item[_ctx.field3.key],
              "model-modifiers": _ctx.field3.modelModifiers,
              placeholder: " ",
              uicontrol: _ctx.field3.uiControl,
              name: `${_ctx.name}-p3-${index}`,
              id: `${_ctx.id}-p3-${index}`,
              title: _ctx.field3.title,
              "template-file": _ctx.field3.templateFile,
              component: _ctx.field3.component
            }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "name", "id", "title", "template-file", "component"])
          ])) : vue.createCommentVNode("", true),
          _ctx.field4 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$e, [
            vue.createVNode(_component_Field, {
              "full-width": true,
              options: _ctx.field4.availableValues,
              "onUpdate:modelValue": ($event) => _ctx.onEntryChange(index, _ctx.field4.key, $event),
              "model-value": item[_ctx.field4.key],
              "model-modifiers": _ctx.field4.modelModifiers,
              placeholder: " ",
              uicontrol: _ctx.field4.uiControl,
              name: `${_ctx.name}-p4-${index}`,
              id: `${_ctx.id}-p4-${index}`,
              title: _ctx.field4.title,
              "template-file": _ctx.field4.templateFile,
              component: _ctx.field4.component
            }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "name", "id", "title", "template-file", "component"])
          ])) : vue.createCommentVNode("", true),
          vue.withDirectives(vue.createElementVNode("span", {
            onClick: ($event) => _ctx.removeEntry(index),
            class: "icon-minus valign",
            title: _ctx.translate("General_Remove")
          }, null, 8, _hoisted_5$c), [
            [vue.vShow, index + 1 !== (_ctx.modelValue || []).length]
          ])
        ], 2);
      }), 128))
    ]);
  }
  const MultiPairField = /* @__PURE__ */ _export_sfc(_sfc_main$q, [["render", _sfc_render$q]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function isApplyButtonEnabled(state) {
    if (state.hasPendingNonRangePeriodChange) {
      return false;
    }
    if (state.uiSelectionType === "period" && state.uiSelectedPeriod !== RANGE_PERIOD && !state.isCompareDirty) {
      return true;
    }
    if (state.uiSelectedPeriod === RANGE_PERIOD && !state.hasPendingPresetSelection && !state.isRangeValid) {
      return false;
    }
    if (state.isComparing && state.comparePeriodType === "custom" && !state.isCompareRangeValid) {
      return false;
    }
    return true;
  }
  function getApplyButtonAction(state) {
    if (state.hasPendingNonRangePeriodChange) {
      return { type: "stop" };
    }
    if (!state.isCompareDirty) {
      return state.shouldCloseSelectorWithoutApplying ? { type: "close" } : { type: "stop" };
    }
    if (state.appliedPeriod === RANGE_PERIOD) {
      if (!state.hasCommittedRangeBounds) {
        return { type: "stop" };
      }
      const rangeDateValue = `${state.appliedRangeStartDate},${state.appliedRangeEndDate}`;
      return {
        type: "commit",
        date: state.rollingDateParam || rangeDateValue,
        period: RANGE_PERIOD
      };
    }
    if (!state.formattedAppliedAnchorDate) {
      return { type: "stop" };
    }
    return {
      type: "commit",
      date: state.rollingDateParam || state.formattedAppliedAnchorDate,
      period: state.appliedPeriod
    };
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function isKeyboardExpandEvent(event) {
    return event.detail === 0;
  }
  function stripCompareDateParams(baseUrlParams) {
    const paramsWithoutCompare = __spreadValues({}, baseUrlParams);
    delete paramsWithoutCompare.comparePeriods;
    delete paramsWithoutCompare.comparePeriodType;
    delete paramsWithoutCompare.compareDates;
    return paramsWithoutCompare;
  }
  function shiftDateByPeriod(sourceDate, period, direction) {
    const shiftedDate = new Date(sourceDate.getTime());
    switch (period) {
      case "day":
        shiftedDate.setDate(shiftedDate.getDate() + direction);
        break;
      case "week":
        shiftedDate.setDate(shiftedDate.getDate() + direction * 7);
        break;
      case "month":
        shiftedDate.setMonth(shiftedDate.getMonth() + direction);
        break;
      case "year":
        shiftedDate.setFullYear(shiftedDate.getFullYear() + direction);
        break;
    }
    return shiftedDate;
  }
  function clampDateToBounds$1(date, minDate, maxDate) {
    const clampedDate = new Date(date.getTime());
    if (clampedDate < minDate) {
      clampedDate.setTime(minDate.getTime());
    }
    if (clampedDate > maxDate) {
      clampedDate.setTime(maxDate.getTime());
    }
    return clampedDate;
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const PRESET_DATE_RANGE_PERIODS = {
    today: "day",
    yesterday: "day",
    last7days: "range",
    last30days: "range",
    last90days: "range",
    lastWeekMonSun: "week",
    lastMonth: "month",
    lastQuarter: "range",
    lastYear: "year",
    thisWeekMonToday: "week",
    thisMonth: "month",
    thisQuarter: "range",
    thisYear: "year"
  };
  const PRESET_DATE_RANGES = [
    { id: "today", labelKey: "CoreHome_PresetDateToday" },
    { id: "yesterday", labelKey: "CoreHome_PresetDateYesterday" },
    { id: "last7days", labelKey: "CoreHome_PresetDateLast7Days" },
    { id: "last30days", labelKey: "CoreHome_PresetDateLast30Days" },
    { id: "last90days", labelKey: "CoreHome_PresetDateLast90Days" },
    { id: "lastWeekMonSun", labelKey: "CoreHome_PresetDateLastWeekMonSun" },
    { id: "lastMonth", labelKey: "CoreHome_PresetDateLastMonth" },
    { id: "lastQuarter", labelKey: "CoreHome_PresetDateLastQuarter" },
    { id: "lastYear", labelKey: "CoreHome_PresetDateLastYear" },
    { id: "thisWeekMonToday", labelKey: "CoreHome_PresetDateThisWeekMonToday" },
    { id: "thisMonth", labelKey: "CoreHome_PresetDateThisMonth" },
    { id: "thisQuarter", labelKey: "CoreHome_PresetDateThisQuarter" },
    { id: "thisYear", labelKey: "CoreHome_PresetDateThisYear" }
  ];
  const PRESET_TOKEN_TO_ID_MAP = {
    "day|today": "today",
    "day|yesterday": "yesterday",
    "range|last7": "last7days",
    "range|last30": "last30days",
    "range|last90": "last90days",
    "week|lastweek": "lastWeekMonSun",
    "month|lastmonth": "lastMonth",
    "year|lastyear": "lastYear",
    "week|today": "thisWeekMonToday",
    "month|today": "thisMonth",
    "year|today": "thisYear"
  };
  const PRESET_ID_TO_URL_DATE_MAP = {
    today: "today",
    yesterday: "yesterday",
    last7days: "last7",
    last30days: "last30",
    last90days: "last90",
    lastWeekMonSun: "lastweek",
    lastMonth: "lastmonth",
    lastYear: "lastyear",
    thisWeekMonToday: "today",
    thisMonth: "today",
    thisYear: "today"
  };
  function getTokenPresetIdFromPeriodAndDate(period, date) {
    return PRESET_TOKEN_TO_ID_MAP[`${period}|${date}`] || null;
  }
  function cloneDate(date) {
    return new Date(date.getTime());
  }
  function addDays(date, days) {
    const nextDate = cloneDate(date);
    nextDate.setDate(nextDate.getDate() + days);
    return nextDate;
  }
  function startOfMonth(date) {
    return new Date(date.getFullYear(), date.getMonth(), 1);
  }
  function endOfMonth(date) {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0);
  }
  function startOfWeekMonday(date) {
    const daysToMonday = (date.getDay() + 6) % 7;
    return addDays(date, -daysToMonday);
  }
  function startOfQuarter(date) {
    const month = date.getMonth();
    const quarterStartMonth = month - month % 3;
    return new Date(date.getFullYear(), quarterStartMonth, 1);
  }
  function makeRangeDateParam(startDate, endDate) {
    return `${format(startDate)},${format(endDate)}`;
  }
  function clampDateToBounds(date, minDate, maxDate) {
    if (date < minDate) {
      return new Date(minDate.getTime());
    }
    if (date > maxDate) {
      return new Date(maxDate.getTime());
    }
    return date;
  }
  function resolvePresetDateRange(presetId, todayInput) {
    const today = cloneDate(todayInput);
    const withUrlDate = (selection) => __spreadProps(__spreadValues({}, selection), {
      urlDate: PRESET_ID_TO_URL_DATE_MAP[presetId] || selection.date
    });
    switch (presetId) {
      case "today":
        return withUrlDate({
          id: presetId,
          period: "day",
          date: format(today),
          selectedDate: today,
          startDate: today,
          endDate: today
        });
      case "yesterday": {
        const yesterday = addDays(today, -1);
        return withUrlDate({
          id: presetId,
          period: "day",
          date: format(yesterday),
          selectedDate: yesterday,
          startDate: yesterday,
          endDate: yesterday
        });
      }
      case "last7days": {
        const startDate = addDays(today, -6);
        return withUrlDate({
          id: presetId,
          period: "range",
          date: makeRangeDateParam(startDate, today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "last30days": {
        const startDate = addDays(today, -29);
        return withUrlDate({
          id: presetId,
          period: "range",
          date: makeRangeDateParam(startDate, today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "last90days": {
        const startDate = addDays(today, -89);
        return withUrlDate({
          id: presetId,
          period: "range",
          date: makeRangeDateParam(startDate, today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "lastWeekMonSun": {
        const thisWeekStart = startOfWeekMonday(today);
        const startDate = addDays(thisWeekStart, -7);
        const endDate = addDays(startDate, 6);
        return withUrlDate({
          id: presetId,
          period: "week",
          date: format(startDate),
          selectedDate: startDate,
          startDate,
          endDate
        });
      }
      case "lastMonth": {
        const lastMonthDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const startDate = startOfMonth(lastMonthDate);
        const endDate = endOfMonth(lastMonthDate);
        return withUrlDate({
          id: presetId,
          period: "month",
          date: format(startDate),
          selectedDate: startDate,
          startDate,
          endDate
        });
      }
      case "lastQuarter": {
        const thisQuarterStart = startOfQuarter(today);
        const endDate = addDays(thisQuarterStart, -1);
        const startDate = startOfQuarter(endDate);
        return withUrlDate({
          id: presetId,
          period: "range",
          date: makeRangeDateParam(startDate, endDate),
          selectedDate: endDate,
          startDate,
          endDate
        });
      }
      case "lastYear": {
        const year = today.getFullYear() - 1;
        const startDate = new Date(year, 0, 1);
        const endDate = new Date(year, 11, 31);
        return withUrlDate({
          id: presetId,
          period: "year",
          date: format(startDate),
          selectedDate: startDate,
          startDate,
          endDate
        });
      }
      case "thisWeekMonToday": {
        const startDate = startOfWeekMonday(today);
        return withUrlDate({
          id: presetId,
          period: "week",
          date: format(today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "thisMonth": {
        const startDate = startOfMonth(today);
        return withUrlDate({
          id: presetId,
          period: "month",
          date: format(today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "thisQuarter": {
        const startDate = startOfQuarter(today);
        return withUrlDate({
          id: presetId,
          period: "range",
          date: makeRangeDateParam(startDate, today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      case "thisYear": {
        const startDate = new Date(today.getFullYear(), 0, 1);
        return withUrlDate({
          id: presetId,
          period: "year",
          date: format(today),
          selectedDate: today,
          startDate,
          endDate: today
        });
      }
      default:
        throw new Error(`Unknown preset date range: ${presetId}`);
    }
  }
  function getPresetIdFromPeriodAndDate(period, date, todayInput = getToday()) {
    try {
      let selectedDate = null;
      let selectedDateRange = null;
      const matchingPreset = PRESET_DATE_RANGES.find((preset) => {
        const resolvedPreset = resolvePresetDateRange(preset.id, todayInput);
        if (resolvedPreset.period !== period) {
          return false;
        }
        if (resolvedPreset.date === date) {
          return true;
        }
        if (period !== "range") {
          selectedDate = selectedDate || parseDate(date);
          return datesAreInTheSamePeriod(selectedDate, resolvedPreset.selectedDate, period);
        }
        selectedDateRange = selectedDateRange || Periods$1.parse(period, date).getDateRange();
        const presetDateRange = [resolvedPreset.startDate, resolvedPreset.endDate];
        return selectedDateRange[0].getTime() === presetDateRange[0].getTime() && selectedDateRange[1].getTime() === presetDateRange[1].getTime();
      });
      return (matchingPreset == null ? void 0 : matchingPreset.id) || getTokenPresetIdFromPeriodAndDate(period, date);
    } catch (e) {
      return getTokenPresetIdFromPeriodAndDate(period, date);
    }
  }
  const PRESET_DATE_RANGE_GROUPS = [
    ["today", "yesterday"],
    ["last7days", "last30days", "last90days"],
    ["lastWeekMonSun", "lastMonth", "lastQuarter", "lastYear"],
    ["thisWeekMonToday", "thisMonth", "thisQuarter", "thisYear"]
  ];
  let nextPresetDateRangeGroupId = 0;
  const _sfc_main$p = vue.defineComponent({
    props: {
      checkedPresetId: {
        type: String,
        default: null
      },
      minDate: {
        type: Date,
        required: true
      },
      maxDate: {
        type: Date,
        required: true
      },
      today: {
        type: Date,
        default: () => getToday()
      },
      allowedPeriods: {
        type: Array,
        required: true
      }
    },
    data() {
      const presetInputName = `preset-date-range-${nextPresetDateRangeGroupId}`;
      nextPresetDateRangeGroupId += 1;
      return {
        presetInputName
      };
    },
    emits: ["select", "dblclick"],
    computed: {
      presetDateRanges() {
        return PRESET_DATE_RANGES.filter(
          (preset) => this.allowedPeriods.includes(PRESET_DATE_RANGE_PERIODS[preset.id])
        );
      },
      groupedPresetDateRanges() {
        const presetDateRangeById = new Map(
          this.presetDateRanges.map((preset) => [preset.id, preset])
        );
        return PRESET_DATE_RANGE_GROUPS.map((group) => group.map((presetId) => presetDateRangeById.get(presetId)).filter((preset) => !!preset)).filter((group) => group.length);
      }
    },
    methods: {
      translate,
      handlePresetClick(presetId) {
        if (this.checkedPresetId !== presetId) {
          return;
        }
        this.handlePresetSelected(presetId);
      },
      handlePresetSelected(presetId) {
        const resolvedPreset = resolvePresetDateRange(presetId, this.today);
        this.$emit("select", __spreadProps(__spreadValues({}, resolvedPreset), {
          startDate: clampDateToBounds(resolvedPreset.startDate, this.minDate, this.maxDate),
          endDate: clampDateToBounds(resolvedPreset.endDate, this.minDate, this.maxDate)
        }));
      },
      handlePresetDoubleClick(presetId) {
        const resolvedPreset = resolvePresetDateRange(presetId, this.today);
        this.$emit("dblclick", __spreadProps(__spreadValues({}, resolvedPreset), {
          startDate: clampDateToBounds(resolvedPreset.startDate, this.minDate, this.maxDate),
          endDate: clampDateToBounds(resolvedPreset.endDate, this.minDate, this.maxDate)
        }));
      }
    }
  });
  const _hoisted_1$n = { class: "presetDateRanges" };
  const _hoisted_2$i = {
    key: 0,
    class: "preset-date-range-group-separator"
  };
  const _hoisted_3$h = ["title", "onDblclick"];
  const _hoisted_4$d = ["name", "id", "checked", "onClick", "onChange"];
  const _hoisted_5$b = { class: "preset-option-text" };
  function _sfc_render$p(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$n, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.groupedPresetDateRanges, (group, index) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          key: index,
          class: "preset-date-range-group"
        }, [
          index > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$i)) : vue.createCommentVNode("", true),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(group, (preset) => {
            return vue.openBlock(), vue.createElementBlock("p", {
              key: preset.id
            }, [
              vue.createElementVNode("label", {
                class: vue.normalizeClass({ "selected-period-label": _ctx.checkedPresetId === preset.id }),
                title: _ctx.checkedPresetId === preset.id ? "" : _ctx.translate("General_DoubleClickToChangePeriod"),
                onDblclick: ($event) => _ctx.handlePresetDoubleClick(preset.id)
              }, [
                vue.createElementVNode("input", {
                  type: "radio",
                  class: "preset-option-input",
                  name: _ctx.presetInputName,
                  id: `preset_date_${preset.id}`,
                  checked: _ctx.checkedPresetId === preset.id,
                  onClick: ($event) => _ctx.handlePresetClick(preset.id),
                  onChange: ($event) => _ctx.handlePresetSelected(preset.id)
                }, null, 40, _hoisted_4$d),
                vue.createElementVNode("span", _hoisted_5$b, vue.toDisplayString(_ctx.translate(preset.labelKey)), 1)
              ], 42, _hoisted_3$h)
            ]);
          }), 128))
        ]);
      }), 128))
    ]);
  }
  const PresetDateRanges = /* @__PURE__ */ _export_sfc(_sfc_main$p, [["render", _sfc_render$p]]);
  let nextPeriodOptionsGroupId = 0;
  const _sfc_main$o = vue.defineComponent({
    name: "PeriodOptions",
    props: {
      modelValue: {
        type: String,
        default: null
      },
      periods: {
        type: Array,
        required: true
      },
      checkedPeriodId: {
        type: String,
        default: null
      },
      activeDatePeriod: {
        type: String,
        required: true
      }
    },
    data() {
      const periodInputName = `period-${nextPeriodOptionsGroupId}`;
      nextPeriodOptionsGroupId += 1;
      return {
        periodInputName
      };
    },
    emits: ["update:modelValue", "select", "dblclick"],
    computed: {
      displayPeriods() {
        if (!this.periods.includes("range")) {
          return this.periods;
        }
        return ["range"].concat(this.periods.filter((period) => period !== "range"));
      }
    },
    methods: {
      translate,
      getPeriodDisplayText(periodLabel) {
        const displayText = periodLabel === "range" ? `${translate("General_Custom")} ${translate("General_DateRangeInPeriodList")}` : Periods$1.get(periodLabel).getDisplayText();
        return displayText.charAt(0).toUpperCase() + displayText.slice(1);
      },
      handlePeriodSelected(period) {
        const payload = { period };
        this.$emit("update:modelValue", period);
        this.$emit("select", payload);
      },
      handlePeriodEnter(period) {
        this.handlePeriodSelected(period);
      },
      handlePeriodDoubleClick(period) {
        const payload = { period };
        this.$emit("dblclick", payload);
      }
    }
  });
  const _hoisted_1$m = ["aria-label"];
  const _hoisted_2$h = ["title", "onDblclick"];
  const _hoisted_3$g = ["name", "id", "checked", "onChange", "onKeydown"];
  const _hoisted_4$c = { class: "period-option-text" };
  function _sfc_render$o(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "periodOptions",
      role: "radiogroup",
      "aria-label": _ctx.translate("General_ChoosePeriod")
    }, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.displayPeriods, (period) => {
        return vue.openBlock(), vue.createElementBlock("p", { key: period }, [
          vue.createElementVNode("label", {
            class: vue.normalizeClass(["period-option-label", { "selected-period-label": _ctx.checkedPeriodId === period }]),
            title: period === _ctx.activeDatePeriod ? "" : _ctx.translate("General_DoubleClickToChangePeriod"),
            onDblclick: ($event) => _ctx.handlePeriodDoubleClick(period)
          }, [
            vue.createElementVNode("input", {
              class: "period-option-input",
              type: "radio",
              name: _ctx.periodInputName,
              id: `period_id_${period}`,
              checked: _ctx.checkedPeriodId === period,
              onChange: ($event) => _ctx.handlePeriodSelected(period),
              onKeydown: vue.withKeys(vue.withModifiers(($event) => _ctx.handlePeriodEnter(period), ["prevent"]), ["enter"])
            }, null, 40, _hoisted_3$g),
            vue.createElementVNode("span", _hoisted_4$c, vue.toDisplayString(_ctx.getPeriodDisplayText(period)), 1)
          ], 42, _hoisted_2$h)
        ]);
      }), 128))
    ], 8, _hoisted_1$m);
  }
  const PeriodOptions = /* @__PURE__ */ _export_sfc(_sfc_main$o, [["render", _sfc_render$o]]);
  const _sfc_main$n = vue.defineComponent({
    name: "PeriodSelectorOptionsColumn",
    components: {
      PresetDateRanges,
      PeriodOptions
    },
    props: {
      uiSelectedPeriod: {
        type: String,
        required: true
      },
      periodsFiltered: {
        type: Array,
        required: true
      },
      appliedPeriod: {
        type: String,
        required: true
      },
      activePresetId: {
        type: String,
        default: null
      },
      minAllowedDate: {
        type: Date,
        required: true
      },
      maxAllowedDate: {
        type: Date,
        required: true
      }
    },
    emits: [
      "update:uiSelectedPeriod",
      "period-select",
      "period-dblclick",
      "preset-select",
      "preset-dblclick"
    ],
    methods: {
      translate
    }
  });
  const _hoisted_1$l = { class: "period-type period-selector-options-column" };
  const _hoisted_2$g = { id: "otherPeriods" };
  function _sfc_render$n(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PeriodOptions = vue.resolveComponent("PeriodOptions");
    const _component_PresetDateRanges = vue.resolveComponent("PresetDateRanges");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$l, [
      vue.createElementVNode("h6", null, [
        vue.createElementVNode("b", null, vue.toDisplayString(_ctx.translate("General_ChoosePeriod")), 1)
      ]),
      vue.createElementVNode("div", _hoisted_2$g, [
        vue.createVNode(_component_PeriodOptions, {
          "model-value": _ctx.uiSelectedPeriod,
          periods: _ctx.periodsFiltered,
          "checked-period-id": _ctx.uiSelectedPeriod,
          "active-date-period": _ctx.appliedPeriod,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:uiSelectedPeriod", $event)),
          onSelect: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("period-select", $event)),
          onDblclick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("period-dblclick", $event))
        }, null, 8, ["model-value", "periods", "checked-period-id", "active-date-period"]),
        vue.createVNode(_component_PresetDateRanges, {
          "checked-preset-id": _ctx.activePresetId,
          "allowed-periods": _ctx.periodsFiltered,
          "min-date": _ctx.minAllowedDate,
          "max-date": _ctx.maxAllowedDate,
          onSelect: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("preset-select", $event)),
          onDblclick: _cache[4] || (_cache[4] = ($event) => _ctx.$emit("preset-dblclick", $event))
        }, null, 8, ["checked-preset-id", "allowed-periods", "min-date", "max-date"])
      ])
    ]);
  }
  const PeriodSelectorOptionsColumn = /* @__PURE__ */ _export_sfc(_sfc_main$n, [["render", _sfc_render$n]]);
  const Field$2 = useExternalPluginComponent("CorePluginsAdmin", "Field");
  const _sfc_main$m = vue.defineComponent({
    name: "PeriodSelectorCompareControls",
    components: {
      Field: Field$2
    },
    props: {
      isComparisonEnabled: {
        type: Boolean,
        required: true
      },
      isComparing: {
        type: Boolean,
        default: null
      },
      comparePeriodType: {
        type: String,
        required: true
      },
      compareStartDate: {
        type: String,
        required: true
      },
      compareEndDate: {
        type: String,
        required: true
      },
      comparePeriodDropdownOptions: {
        type: Array,
        required: true
      },
      showInvalidComparisonMessage: {
        type: Boolean,
        default: false
      }
    },
    emits: [
      "update:isComparing",
      "update:comparePeriodType",
      "update:compareStartDate",
      "update:compareEndDate"
    ],
    methods: {
      translate,
      onCompareToggle(event) {
        this.$emit("update:isComparing", event.target.checked);
      }
    }
  });
  const _hoisted_1$k = {
    key: 0,
    class: "compare-checkbox"
  };
  const _hoisted_2$f = { class: "compare-checkbox-label" };
  const _hoisted_3$f = ["checked"];
  const _hoisted_4$b = { class: "compare-checkbox-text" };
  const _hoisted_5$a = { id: "comparePeriodToDropdown" };
  const _hoisted_6$9 = {
    key: 1,
    class: "compare-date-range"
  };
  const _hoisted_7$6 = { id: "comparePeriodStartDate" };
  const _hoisted_8$5 = { id: "comparePeriodEndDate" };
  const _hoisted_9$4 = {
    key: 0,
    class: "compare-validation-message"
  };
  function _sfc_render$m(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      _ctx.isComparisonEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$k, [
        vue.createElementVNode("label", _hoisted_2$f, [
          vue.createElementVNode("input", {
            class: "compare-checkbox-input",
            id: "comparePeriodTo",
            type: "checkbox",
            checked: !!_ctx.isComparing,
            onChange: _cache[0] || (_cache[0] = ($event) => _ctx.onCompareToggle($event))
          }, null, 40, _hoisted_3$f),
          vue.createElementVNode("span", _hoisted_4$b, vue.toDisplayString(_ctx.translate("General_CompareTo")), 1)
        ]),
        vue.createElementVNode("div", _hoisted_5$a, [
          vue.createVNode(_component_Field, {
            "model-value": _ctx.comparePeriodType,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.$emit("update:comparePeriodType", $event)),
            style: vue.normalizeStyle({ "visibility": _ctx.isComparing ? "visible" : "hidden" }),
            name: "comparePeriodToDropdown",
            uicontrol: "select",
            options: _ctx.comparePeriodDropdownOptions,
            "full-width": true,
            disabled: !_ctx.isComparing
          }, null, 8, ["model-value", "style", "options", "disabled"])
        ])
      ])) : vue.createCommentVNode("", true),
      _ctx.isComparing && _ctx.comparePeriodType === "custom" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$9, [
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_7$6, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                "model-value": _ctx.compareStartDate,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.$emit("update:compareStartDate", $event)),
                name: "comparePeriodStartDate",
                uicontrol: "text",
                "full-width": true,
                title: _ctx.translate("CoreHome_StartDate"),
                placeholder: "YYYY-MM-DD"
              }, null, 8, ["model-value", "title"])
            ])
          ]),
          _cache[4] || (_cache[4] = vue.createElementVNode("span", { class: "compare-dates-separator" }, null, -1)),
          vue.createElementVNode("div", _hoisted_8$5, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                "model-value": _ctx.compareEndDate,
                "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("update:compareEndDate", $event)),
                name: "comparePeriodEndDate",
                uicontrol: "text",
                "full-width": true,
                title: _ctx.translate("CoreHome_EndDate"),
                placeholder: "YYYY-MM-DD"
              }, null, 8, ["model-value", "title"])
            ])
          ])
        ]),
        _ctx.showInvalidComparisonMessage ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_9$4, vue.toDisplayString(_ctx.translate("CoreHome_InvalidComparisonDateRange")), 1)) : vue.createCommentVNode("", true)
      ])) : vue.createCommentVNode("", true)
    ], 64);
  }
  const PeriodSelectorCompareControls = /* @__PURE__ */ _export_sfc(_sfc_main$m, [["render", _sfc_render$m]]);
  const _sfc_main$l = vue.defineComponent({
    name: "PeriodSelectorCalendarColumn",
    components: {
      DateRangePicker,
      PeriodDatePicker,
      PeriodSelectorCompareControls
    },
    props: {
      uiSelection: {
        type: Object,
        required: true
      },
      calendarViewport: {
        type: String,
        required: true
      },
      displayRangeStartDate: {
        type: String,
        default: null
      },
      displayRangeEndDate: {
        type: String,
        default: null
      },
      singleCalendarPeriod: {
        type: String,
        required: true
      },
      singleCalendarSelectedDate: {
        type: Date,
        default: null
      },
      isComparisonEnabled: {
        type: Boolean,
        required: true
      },
      isComparing: {
        type: Boolean,
        default: null
      },
      comparePeriodType: {
        type: String,
        required: true
      },
      compareStartDate: {
        type: String,
        required: true
      },
      compareEndDate: {
        type: String,
        required: true
      },
      comparePeriodDropdownOptions: {
        type: Array,
        required: true
      },
      showInvalidComparisonMessage: {
        type: Boolean,
        default: false
      },
      isApplyEnabled: {
        type: Boolean,
        required: true
      }
    },
    emits: [
      "range-change",
      "single-date-select",
      "apply-click",
      "disabled-apply-interaction",
      "update:isComparing",
      "update:comparePeriodType",
      "update:compareStartDate",
      "update:compareEndDate"
    ],
    methods: {
      translate,
      onApplyButtonInteraction() {
        if (!this.isApplyEnabled) {
          this.$emit("disabled-apply-interaction");
        }
      }
    }
  });
  const _hoisted_1$j = { class: "period-selector-calendar-column" };
  const _hoisted_2$e = { class: "period-date" };
  const _hoisted_3$e = ["disabled", "value"];
  function _sfc_render$l(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2, _b, _c;
    const _component_DateRangePicker = vue.resolveComponent("DateRangePicker");
    const _component_PeriodDatePicker = vue.resolveComponent("PeriodDatePicker");
    const _component_PeriodSelectorCompareControls = vue.resolveComponent("PeriodSelectorCompareControls");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$j, [
      vue.createElementVNode("div", null, [
        vue.withDirectives(vue.createVNode(_component_DateRangePicker, {
          class: "period-range",
          "start-date": (_a2 = _ctx.displayRangeStartDate) != null ? _a2 : void 0,
          "end-date": (_b = _ctx.displayRangeEndDate) != null ? _b : void 0,
          onRangeChange: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("range-change", $event)),
          onSubmit: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("apply-click"))
        }, null, 8, ["start-date", "end-date"]), [
          [vue.vShow, _ctx.calendarViewport === "range"]
        ])
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_2$e, [
        vue.createVNode(_component_PeriodDatePicker, {
          id: "datepicker",
          period: _ctx.singleCalendarPeriod,
          date: (_c = _ctx.singleCalendarSelectedDate) != null ? _c : void 0,
          onSelect: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("single-date-select", $event.date))
        }, null, 8, ["period", "date"])
      ], 512), [
        [vue.vShow, _ctx.calendarViewport === "single"]
      ]),
      vue.createVNode(_component_PeriodSelectorCompareControls, {
        "is-comparison-enabled": _ctx.isComparisonEnabled,
        "is-comparing": _ctx.isComparing,
        "compare-period-type": _ctx.comparePeriodType,
        "compare-start-date": _ctx.compareStartDate,
        "compare-end-date": _ctx.compareEndDate,
        "compare-period-dropdown-options": _ctx.comparePeriodDropdownOptions,
        "show-invalid-comparison-message": _ctx.showInvalidComparisonMessage,
        "onUpdate:isComparing": _cache[3] || (_cache[3] = ($event) => _ctx.$emit("update:isComparing", $event)),
        "onUpdate:comparePeriodType": _cache[4] || (_cache[4] = ($event) => _ctx.$emit("update:comparePeriodType", $event)),
        "onUpdate:compareStartDate": _cache[5] || (_cache[5] = ($event) => _ctx.$emit("update:compareStartDate", $event)),
        "onUpdate:compareEndDate": _cache[6] || (_cache[6] = ($event) => _ctx.$emit("update:compareEndDate", $event))
      }, null, 8, ["is-comparison-enabled", "is-comparing", "compare-period-type", "compare-start-date", "compare-end-date", "compare-period-dropdown-options", "show-invalid-comparison-message"]),
      vue.createElementVNode("div", {
        class: "apply-button-container",
        onMousedownCapture: _cache[8] || (_cache[8] = (...args) => _ctx.onApplyButtonInteraction && _ctx.onApplyButtonInteraction(...args))
      }, [
        vue.createElementVNode("input", {
          type: "submit",
          id: "calendarApply",
          class: "btn",
          onClick: _cache[7] || (_cache[7] = ($event) => _ctx.$emit("apply-click")),
          disabled: !_ctx.isApplyEnabled,
          value: _ctx.translate("General_Apply")
        }, null, 8, _hoisted_3$e)
      ], 32)
    ]);
  }
  const PeriodSelectorCalendarColumn = /* @__PURE__ */ _export_sfc(_sfc_main$l, [["render", _sfc_render$l]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const CONTEXT_KEY_IGNORED_PARAMS = [
    "date",
    "period",
    "comparePeriods",
    "comparePeriodType",
    "compareDates",
    "compareSegments"
  ];
  function getSelectionKey(period, date) {
    return `${period}|${date}`;
  }
  function getContextKeyFromParsed(parsed) {
    const normalizedContext = {};
    Object.keys(parsed).filter((key) => !CONTEXT_KEY_IGNORED_PARAMS.includes(key)).sort().forEach((key) => {
      normalizedContext[key] = parsed[key];
    });
    return JSON.stringify(normalizedContext);
  }
  function shouldSkipHashSync(currentSelectionKey, currentContextKey, nextHashUiSelection, lastKnownHashSelectionKey, lastKnownHashContextKey) {
    return !nextHashUiSelection && currentSelectionKey === lastKnownHashSelectionKey && currentContextKey === lastKnownHashContextKey;
  }
  function resolveSyncedUiSelection(currentSelectionKey, currentContextKey, nextHashUiSelection, nextHashSelectionKey) {
    const isExpectedHashUpdate = !!nextHashUiSelection && nextHashSelectionKey === currentSelectionKey;
    const syncedUiSelection = isExpectedHashUpdate && nextHashUiSelection ? __spreadValues({}, nextHashUiSelection) : null;
    return {
      syncedUiSelection,
      lastKnownHashSelectionKey: currentSelectionKey,
      lastKnownHashContextKey: currentContextKey,
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastInteractionSource: null
    };
  }
  function resolveActivePresetIdFromSelection(state) {
    if (state.pendingPresetSelection) {
      return state.pendingPresetSelection.id;
    }
    let currentDate = null;
    if (state.selectedPeriod === RANGE_PERIOD) {
      if (state.appliedRangeStartDate && state.appliedRangeEndDate) {
        currentDate = `${state.appliedRangeStartDate},${state.appliedRangeEndDate}`;
      }
    } else if (state.committedAnchorDate) {
      currentDate = format(state.committedAnchorDate);
    }
    if (!currentDate) {
      return null;
    }
    return getPresetIdFromPeriodAndDate(
      state.selectedPeriod,
      currentDate,
      getToday()
    );
  }
  function syncSelectionDisplayState(state) {
    state.calendarViewport = state.selectedPeriod === RANGE_PERIOD ? "range" : "single";
    if (isSingleCalendarPeriod(state.selectedPeriod)) {
      state.singleCalendarPeriod = state.selectedPeriod;
    } else if (!isSingleCalendarPeriod(state.singleCalendarPeriod)) {
      state.singleCalendarPeriod = "day";
    }
    if (state.selectedPeriod === RANGE_PERIOD) {
      state.singleCalendarSelectedDate = null;
      return;
    }
    if (state.pendingPresetSelection && isSingleCalendarPeriod(state.pendingPresetSelection.period)) {
      state.singleCalendarSelectedDate = state.pendingPresetSelection.selectedDate;
      return;
    }
    state.singleCalendarSelectedDate = state.committedPeriod === state.selectedPeriod ? state.committedAnchorDate : null;
  }
  const _sfc_main$k = vue.defineComponent({
    name: "PeriodSelector",
    props: {
      periods: Array
    },
    components: {
      PeriodSelectorOptionsColumn,
      PeriodSelectorCalendarColumn,
      ActivityIndicator
    },
    directives: {
      ExpandOnClick,
      Tooltips
    },
    data() {
      const selectedPeriod = instance$1.parsed.value.period;
      const initialSinglePeriod = isSingleCalendarPeriod(selectedPeriod) ? selectedPeriod : "day";
      const siteMinAllowedDate = getSiteMinAllowedDate();
      const siteMaxAllowedDate = getSiteMaxAllowedDate();
      return {
        uiSelection: { type: "period", id: selectedPeriod },
        lastInteractionSource: null,
        nextHashUiSelection: null,
        nextHashSelectionKey: null,
        lastKnownHashSelectionKey: null,
        lastKnownHashContextKey: null,
        minAllowedDate: siteMinAllowedDate,
        maxAllowedDate: siteMaxAllowedDate,
        pendingPresetSelection: null,
        committedPeriod: selectedPeriod,
        committedAnchorDate: null,
        selectedPeriod,
        calendarViewport: selectedPeriod === RANGE_PERIOD ? "range" : "single",
        singleCalendarPeriod: initialSinglePeriod,
        singleCalendarSelectedDate: null,
        appliedRangeStartDate: null,
        appliedRangeEndDate: null,
        isRangeValid: null,
        isLoadingNewPage: false,
        isComparing: null,
        comparePeriodType: "previousPeriod",
        compareStartDate: "",
        compareEndDate: "",
        compareAppliedSignature: "",
        shouldShowInvalidComparisonMessage: false
      };
    },
    mounted() {
      Matomo.on("hidePeriodSelector", () => {
        window.$(this.$refs.root).parent("#periodString").hide();
      });
      Matomo.on("matomoPageChange", () => {
        window.$(this.$refs.root).parent("#periodString").show();
      });
      window.initTopControls();
      this.handleZIndexPositionRelativeCompareDropdownIssue();
    },
    computed: {
      activePresetId() {
        return resolveActivePresetIdFromSelection(this);
      },
      matomoParsed() {
        return instance$1.parsed.value;
      },
      isComparingStoreValue() {
        return ComparisonsStoreInstance.isComparingPeriods();
      },
      periodComparisonsStoreValue() {
        return ComparisonsStoreInstance.getPeriodComparisons();
      },
      comparePeriodDropdownOptions() {
        return COMPARE_PERIOD_OPTIONS;
      },
      currentlyViewingText() {
        let date;
        if (this.committedPeriod === "range") {
          if (!this.appliedRangeStartDate || !this.appliedRangeEndDate) {
            return translate("General_Error");
          }
          date = `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`;
        } else {
          if (!this.committedAnchorDate) {
            return translate("General_Error");
          }
          date = format(this.committedAnchorDate);
        }
        try {
          return Periods$1.parse(this.committedPeriod, date).getPrettyString();
        } catch (e) {
          return translate("General_Error");
        }
      },
      isComparisonEnabled() {
        return ComparisonsStoreInstance.isComparisonEnabled();
      },
      periodsFiltered() {
        return (this.periods || []).filter(
          (periodLabel) => Periods$1.isRecognizedPeriod(periodLabel)
        );
      },
      selectedComparisonParams() {
        if (!this.isComparing) {
          return {};
        }
        if (this.comparePeriodType === "custom") {
          return {
            comparePeriods: ["range"],
            comparePeriodType: "custom",
            compareDates: [`${this.compareStartDate},${this.compareEndDate}`]
          };
        }
        if (this.comparePeriodType === "previousPeriod") {
          return {
            comparePeriods: [this.selectedPeriod],
            comparePeriodType: "previousPeriod",
            compareDates: [this.previousPeriodDateToSelectedPeriod]
          };
        }
        if (this.comparePeriodType === "previousYear") {
          const dateStr = this.selectedPeriod === "range" ? `${this.appliedRangeStartDate},${this.appliedRangeEndDate}` : format(this.committedAnchorDate);
          const currentDateRange = Periods$1.parse(
            this.selectedPeriod,
            dateStr
          ).getDateRange();
          currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
          currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);
          if (this.selectedPeriod === "range") {
            return {
              comparePeriods: ["range"],
              comparePeriodType: "previousYear",
              compareDates: [`${format(currentDateRange[0])},${format(currentDateRange[1])}`]
            };
          }
          return {
            comparePeriods: [this.selectedPeriod],
            comparePeriodType: "previousYear",
            compareDates: [format(currentDateRange[0])]
          };
        }
        console.warn(`Unknown compare period type: ${this.comparePeriodType}`);
        return {};
      },
      previousPeriodDateToSelectedPeriod() {
        if (this.selectedPeriod === "range") {
          const currentStartRange = parseDate(this.appliedRangeStartDate);
          const currentEndRange = parseDate(this.appliedRangeEndDate);
          const newEndDate = RangePeriod.getLastNRange("day", 2, currentStartRange).startDate;
          const rangeSize = Math.floor(
            (currentEndRange.valueOf() - currentStartRange.valueOf()) / 864e5
          );
          const newRange = RangePeriod.getLastNRange("day", 1 + rangeSize, newEndDate);
          return `${format(newRange.startDate)},${format(newRange.endDate)}`;
        }
        const newStartDate = RangePeriod.getLastNRange(
          this.selectedPeriod,
          2,
          this.committedAnchorDate
        ).startDate;
        return format(newStartDate);
      },
      selectedDateString() {
        if (this.selectedPeriod === "range") {
          const dateFrom = this.appliedRangeStartDate;
          const dateTo = this.appliedRangeEndDate;
          const oDateFrom = parseDate(dateFrom);
          const oDateTo = parseDate(dateTo);
          if (!isValidDate(oDateFrom) || !isValidDate(oDateTo) || oDateFrom > oDateTo) {
            window.$("#alert").find("h2").text(translate("General_InvalidDateRange"));
            Matomo.helper.modalConfirm("#alert", {});
            return null;
          }
          return `${dateFrom},${dateTo}`;
        }
        return format(this.committedAnchorDate);
      },
      isErrorDisplayed() {
        return this.currentlyViewingText === translate("General_Error");
      },
      isRangeSelection() {
        return this.committedPeriod === "range";
      },
      canShowMovePeriod() {
        return !this.isRangeSelection && !this.isErrorDisplayed;
      },
      compareCurrentSignature() {
        return JSON.stringify({
          isComparing: !!this.isComparing,
          comparePeriodType: this.comparePeriodType || "",
          compareStartDate: this.compareStartDate || "",
          compareEndDate: this.compareEndDate || ""
        });
      },
      isCompareDirty() {
        return this.compareCurrentSignature !== this.compareAppliedSignature;
      },
      hasPendingNonRangePeriodChange() {
        return this.uiSelection.type === "period" && this.lastInteractionSource === "period" && this.selectedPeriod !== RANGE_PERIOD && this.selectedPeriod !== this.committedPeriod;
      },
      isRangePresetSelection() {
        return this.uiSelection.type === "preset" && this.selectedPeriod === RANGE_PERIOD;
      },
      displayRangeStartDate() {
        if (this.isRangePresetSelection && this.pendingPresetSelection) {
          return format(this.pendingPresetSelection.startDate);
        }
        return this.appliedRangeStartDate;
      },
      displayRangeEndDate() {
        if (this.isRangePresetSelection && this.pendingPresetSelection) {
          return format(this.pendingPresetSelection.endDate);
        }
        return this.appliedRangeEndDate;
      }
    },
    watch: {
      isComparingStoreValue: {
        immediate: true,
        handler(newVal) {
          this.isComparing = newVal;
        }
      },
      matomoParsed: {
        immediate: true,
        handler() {
          this.updateSelectedValuesFromHash();
        }
      },
      periodComparisonsStoreValue: {
        immediate: true,
        handler() {
          this.updateComparisonValuesFromStore();
          this.compareAppliedSignature = this.compareCurrentSignature;
        }
      }
    },
    methods: {
      onExpand(event) {
        if (isKeyboardExpandEvent(event)) {
          const root = this.$refs.root;
          const selector = this.uiSelection.type === "preset" ? `#preset_date_${this.uiSelection.id}` : `#period_id_${this.uiSelection.id}`;
          const focusTarget = root.querySelector(selector) || root.querySelector("#preset_date_today");
          if (focusTarget instanceof HTMLElement) {
            focusTarget.focus();
          }
        }
      },
      onClosed(event) {
        if (isKeyboardExpandEvent(event)) {
          window.$(this.$refs.title).focus();
        }
      },
      handleZIndexPositionRelativeCompareDropdownIssue() {
        const $element = window.$(this.$refs.root);
        $element.on("focus", "#comparePeriodToDropdown .select-dropdown", () => {
          $element.addClass("compare-dropdown-open");
        }).on("blur", "#comparePeriodToDropdown .select-dropdown", () => {
          $element.removeClass("compare-dropdown-open");
        });
      },
      setUiSelection(selection, source) {
        this.uiSelection = selection;
        this.lastInteractionSource = source;
      },
      clearPresetSelection() {
        this.pendingPresetSelection = null;
      },
      setPendingPeriodAndDate(period, date) {
        this.committedPeriod = period;
        this.selectedPeriod = period;
        this.committedAnchorDate = date;
        this.setRangeStartEndFromPeriod(period, format(date));
        syncSelectionDisplayState(this);
      },
      setPiwikPeriodAndDate(period, date) {
        this.setPendingPeriodAndDate(period, date);
        this.setUiSelection({ type: "period", id: period }, "period");
        const currentDateString = format(date);
        this.clearPresetSelection();
        this.commitSelectionToUrl(currentDateString, this.selectedPeriod);
      },
      commitSelectionToUrl(date, period) {
        this.nextHashUiSelection = __spreadValues({}, this.uiSelection);
        this.nextHashSelectionKey = getSelectionKey(period, date);
        this.compareAppliedSignature = this.compareCurrentSignature;
        this.propagateNewUrlParams(date, period);
        window.initTopControls();
      },
      onPeriodOptionSelected(payload) {
        this.setUiSelection({ type: "period", id: payload.period }, "period");
        this.selectedPeriod = payload.period;
        this.clearPresetSelection();
        syncSelectionDisplayState(this);
        if (payload.period === RANGE_PERIOD) {
          this.isRangeValid = true;
        }
      },
      onPeriodOptionDblClick(payload) {
        this.onPeriodOptionSelected(payload);
        if (this.hasInvalidCustomComparison()) {
          this.showInvalidComparisonMessage();
          return;
        }
        if (payload.period === RANGE_PERIOD || payload.period === this.committedPeriod || !this.committedAnchorDate) {
          return;
        }
        this.setPiwikPeriodAndDate(payload.period, this.committedAnchorDate);
      },
      canInteractWithSingleCalendar() {
        return this.calendarViewport === "single" && this.selectedPeriod !== RANGE_PERIOD;
      },
      onDatePickerSelected(date) {
        if (!this.canInteractWithSingleCalendar()) {
          return;
        }
        this.setUiSelection({ type: "period", id: this.selectedPeriod }, "calendar");
        this.setPendingPeriodAndDate(this.selectedPeriod, date);
        this.clearPresetSelection();
        syncSelectionDisplayState(this);
        this.commitSelectionToUrl(format(date), this.selectedPeriod);
      },
      onPresetDateRangeSelected(selection) {
        if (!this.periodsFiltered.includes(selection.period)) {
          return;
        }
        this.selectedPeriod = selection.period;
        this.pendingPresetSelection = selection;
        this.isRangeValid = selection.period === RANGE_PERIOD ? true : this.isRangeValid;
        this.setUiSelection({ type: "preset", id: selection.id }, "preset");
        syncSelectionDisplayState(this);
      },
      onPresetDateRangeDblClick(selection) {
        this.onPresetDateRangeSelected(selection);
        if (this.hasInvalidCustomComparison()) {
          this.showInvalidComparisonMessage();
          return;
        }
        this.onApplyClicked();
      },
      propagateNewUrlParams(date, period) {
        const compareParams = this.selectedComparisonParams;
        let baseParams;
        if (Matomo.helper.isReportingPage()) {
          this.closePeriodSelector();
          baseParams = instance$1.hashParsed.value;
        } else {
          this.isLoadingNewPage = true;
          baseParams = instance$1.parsed.value;
        }
        instance$1.updateLocation(__spreadValues(__spreadProps(__spreadValues({}, stripCompareDateParams(baseParams)), {
          date,
          period
        }), compareParams));
      },
      hasPendingPresetSelectionOwnedByUi() {
        return !!this.pendingPresetSelection && this.uiSelection.type === "preset" && this.pendingPresetSelection.id === this.uiSelection.id;
      },
      shouldCloseSelectorWithoutApplying() {
        return this.selectedPeriod !== RANGE_PERIOD && !this.hasPendingNonRangePeriodChange;
      },
      hasCommittedRangeBounds() {
        return !!this.appliedRangeStartDate && !!this.appliedRangeEndDate;
      },
      applyPendingPresetSelection() {
        if (!this.hasPendingPresetSelectionOwnedByUi()) {
          return false;
        }
        const pendingPreset = this.pendingPresetSelection;
        this.committedPeriod = pendingPreset.period;
        this.committedAnchorDate = pendingPreset.selectedDate;
        this.appliedRangeStartDate = format(pendingPreset.startDate);
        this.appliedRangeEndDate = format(pendingPreset.endDate);
        this.setUiSelection({ type: "period", id: pendingPreset.period }, "preset");
        this.pendingPresetSelection = null;
        syncSelectionDisplayState(this);
        this.commitSelectionToUrl(
          pendingPreset.urlDate,
          pendingPreset.period
        );
        return true;
      },
      applyRangeSelection() {
        if (this.selectedPeriod !== RANGE_PERIOD) {
          return false;
        }
        const dateString = this.selectedDateString;
        if (!dateString) {
          return true;
        }
        this.committedPeriod = RANGE_PERIOD;
        this.commitSelectionToUrl(
          this.getCurrentRollingDateParamIfOwnedByPreset() || dateString,
          RANGE_PERIOD
        );
        return true;
      },
      applyNonRangeOrCompareChanges() {
        const action = getApplyButtonAction({
          hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
          isCompareDirty: this.isCompareDirty,
          shouldCloseSelectorWithoutApplying: this.shouldCloseSelectorWithoutApplying(),
          appliedPeriod: this.committedPeriod,
          hasCommittedRangeBounds: this.hasCommittedRangeBounds(),
          rollingDateParam: this.getCurrentRollingDateParamIfOwnedByPreset(),
          appliedRangeStartDate: this.appliedRangeStartDate,
          appliedRangeEndDate: this.appliedRangeEndDate,
          formattedAppliedAnchorDate: this.committedAnchorDate ? format(this.committedAnchorDate) : null
        });
        if (action.type === "stop") {
          return;
        }
        if (action.type === "close") {
          this.closePeriodSelector();
          return;
        }
        this.commitSelectionToUrl(action.date, action.period);
      },
      // Non-range period mode keeps the concrete selected date as the commit target.
      // Reopening the selector should let Apply close unchanged state, or commit compare-only
      // edits against that existing date, without forcing another calendar click.
      onApplyClicked() {
        if (this.applyPendingPresetSelection()) {
          return;
        }
        if (this.applyRangeSelection()) {
          return;
        }
        this.applyNonRangeOrCompareChanges();
      },
      updateComparisonValuesFromStore() {
        this.comparePeriodType = "previousPeriod";
        this.compareStartDate = "";
        this.compareEndDate = "";
        const comparePeriods = ComparisonsStoreInstance.getPeriodComparisons();
        if (comparePeriods.length < 2) {
          return;
        }
        const comparePeriodType = instance$1.parsed.value.comparePeriodType;
        if (!COMPARE_PERIOD_TYPES.includes(comparePeriodType)) {
          return;
        }
        this.comparePeriodType = comparePeriodType;
        if (this.comparePeriodType !== "custom" || comparePeriods[1].params.period !== "range") {
          return;
        }
        let periodObj;
        try {
          periodObj = Periods$1.parse(
            comparePeriods[1].params.period,
            comparePeriods[1].params.date
          );
        } catch (e) {
          return;
        }
        const [startDate, endDate] = periodObj.getDateRange();
        this.compareStartDate = format(startDate);
        this.compareEndDate = format(endDate);
      },
      getCurrentContextKey() {
        return getContextKeyFromParsed(instance$1.parsed.value);
      },
      applyUiSelectionFromHash(period, date, syncedUiSelection) {
        if (syncedUiSelection) {
          if (syncedUiSelection.type === "preset") {
            this.uiSelection = syncedUiSelection;
            return;
          }
          const presetId2 = getTokenPresetIdFromPeriodAndDate(period, date);
          if (presetId2 && this.periodsFiltered.includes(period)) {
            this.uiSelection = { type: "preset", id: presetId2 };
            return;
          }
          this.uiSelection = syncedUiSelection;
          return;
        }
        const presetId = getTokenPresetIdFromPeriodAndDate(period, date);
        if (presetId && this.periodsFiltered.includes(period)) {
          this.uiSelection = { type: "preset", id: presetId };
          this.pendingPresetSelection = null;
          return;
        }
<<<<<<< HEAD
        this.setUiSelection({ type: "period", id: period }, null);
        this.clearPresetSelection();
=======
        this.uiSelection = syncedUiSelection;
        return;
      }
      const presetId = getTokenPresetIdFromPeriodAndDate(period, date);
      if (presetId && this.periodsFiltered.includes(period)) {
        this.uiSelection = {
          type: 'preset',
          id: presetId
        };
        this.pendingPresetSelection = null;
        return;
      }
      this.setUiSelection({
        type: 'period',
        id: period
      }, null);
      this.clearPresetSelection();
    },
    getCurrentRollingDateParamIfOwnedByPreset() {
      if (this.uiSelection.type !== 'preset') {
        return null;
      }
      const parsedPeriod = src_MatomoUrl_MatomoUrl.parsed.value.period || '';
      const parsedDate = src_MatomoUrl_MatomoUrl.parsed.value.date || '';
      if (parsedPeriod !== this.committedPeriod || !parsedDate) {
        return null;
      }
      const presetId = getTokenPresetIdFromPeriodAndDate(parsedPeriod, parsedDate);
      if (presetId !== this.uiSelection.id) {
        return null;
      }
      return parsedDate;
    },
    resetSelectedDateValues() {
      this.committedAnchorDate = null;
      this.appliedRangeStartDate = null;
      this.appliedRangeEndDate = null;
    },
    applyDateValuesFromHash(period, date) {
      if (period === RANGE_PERIOD) {
        const periodObj = Periods_Periods.get(period).parse(date);
        const [startDate, endDate] = periodObj.getDateRange();
        this.committedAnchorDate = startDate;
        this.appliedRangeStartDate = format(startDate);
        this.appliedRangeEndDate = format(endDate);
        return;
      }
      this.committedAnchorDate = parseDate(date);
      this.setRangeStartEndFromPeriod(period, date);
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
      }
      this.singleCalendarSelectedDate = this.committedAnchorDate;
    },
    updateSelectedValuesFromHash() {
      const date = src_MatomoUrl_MatomoUrl.parsed.value.date || '';
      const period = src_MatomoUrl_MatomoUrl.parsed.value.period || '';
      const currentSelectionKey = getSelectionKey(period, date);
      const currentContextKey = this.getCurrentContextKey();
      if (shouldSkipHashSync(currentSelectionKey, currentContextKey, this.nextHashUiSelection, this.lastKnownHashSelectionKey, this.lastKnownHashContextKey)) {
        return;
      }
      const hashSyncState = resolveSyncedUiSelection(currentSelectionKey, currentContextKey, this.nextHashUiSelection, this.nextHashSelectionKey);
      this.nextHashUiSelection = hashSyncState.nextHashUiSelection;
      this.nextHashSelectionKey = hashSyncState.nextHashSelectionKey;
      this.lastInteractionSource = hashSyncState.lastInteractionSource;
      this.lastKnownHashSelectionKey = hashSyncState.lastKnownHashSelectionKey;
      this.lastKnownHashContextKey = hashSyncState.lastKnownHashContextKey;
      this.applyUiSelectionFromHash(period, date, hashSyncState.syncedUiSelection);
      this.committedPeriod = period;
      this.selectedPeriod = period;
      this.resetSelectedDateValues();
      try {
        Periods_Periods.parse(period, date);
      } catch (e) {
        if (period === RANGE_PERIOD) {
          this.isRangeValid = false;
        } else {
          this.isRangeValid = null;
        }
        return;
      }
      this.applyDateValuesFromHash(period, date);
      this.isRangeValid = period === RANGE_PERIOD ? true : null;
      this.pendingPresetSelection = null;
      syncSelectionDisplayState(this);
      this.compareAppliedSignature = this.compareCurrentSignature;
    },
    setRangeStartEndFromPeriod(period, dateStr) {
      const dateRange = Periods_Periods.parse(period, dateStr).getDateRange();
      this.appliedRangeStartDate = format(dateRange[0] < this.minAllowedDate ? this.minAllowedDate : dateRange[0]);
      this.appliedRangeEndDate = format(dateRange[1] > this.maxAllowedDate ? this.maxAllowedDate : dateRange[1]);
    },
    canInteractWithRangeCalendar() {
      return this.calendarViewport === 'range' && this.selectedPeriod === RANGE_PERIOD;
    },
    onRangeChange(start, end) {
      if (!this.canInteractWithRangeCalendar()) {
        return;
      }
      if (!start || !end) {
        this.isRangeValid = false;
        return;
      }
      this.isRangeValid = true;
      this.appliedRangeStartDate = start;
      this.appliedRangeEndDate = end;
      this.setUiSelection({
        type: 'period',
        id: RANGE_PERIOD
      }, 'range');
      this.clearPresetSelection();
    },
    isApplyEnabled() {
      return isApplyButtonEnabled({
        uiSelectionType: this.uiSelection.type,
        uiSelectedPeriod: this.selectedPeriod,
        hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
        hasPendingPresetSelection: !!this.pendingPresetSelection,
        isRangeValid: this.isRangeValid,
        isCompareDirty: this.isCompareDirty,
        isComparing: this.isComparing,
        comparePeriodType: this.comparePeriodType,
        isCompareRangeValid: this.isCompareRangeValid()
      });
    },
    shouldDisplayInvalidComparisonMessage() {
      return this.shouldShowInvalidComparisonMessage && this.hasInvalidCustomComparison();
    },
    hasInvalidCustomComparison() {
      return !!this.isComparing && this.comparePeriodType === 'custom' && !this.isCompareRangeValid();
    },
    showInvalidComparisonMessage() {
      if (!this.hasInvalidCustomComparison()) {
        return;
      }
      this.shouldShowInvalidComparisonMessage = true;
    },
    dismissInvalidComparisonMessage() {
      this.shouldShowInvalidComparisonMessage = false;
    },
    onDisabledApplyInteraction() {
      this.showInvalidComparisonMessage();
    },
    onCompareToggleUpdated(value) {
      this.isComparing = value;
      this.dismissInvalidComparisonMessage();
    },
    onComparePeriodTypeUpdated(value) {
      this.comparePeriodType = value;
      this.dismissInvalidComparisonMessage();
    },
    onCompareStartDateUpdated(value) {
      this.compareStartDate = value;
      this.dismissInvalidComparisonMessage();
    },
    onCompareEndDateUpdated(value) {
      this.compareEndDate = value;
      this.dismissInvalidComparisonMessage();
    },
    closePeriodSelector() {
      this.$refs.root.classList.remove('expanded');
    },
    isCompareRangeValid() {
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
    },
    movePeriod(direction) {
      if (!this.canMovePeriod(direction)) {
        return;
      }
      const baseDate = this.committedAnchorDate || new Date();
      const shiftedDate = shiftDateByPeriod(baseDate, this.committedPeriod, direction);
      const clampedDate = clampDateToBounds(shiftedDate, this.minAllowedDate, this.maxAllowedDate);
      this.setPiwikPeriodAndDate(this.committedPeriod, clampedDate);
    },
    isPeriodMoveDisabled(direction) {
      // disable period move when date range is used or when we would go out of the min/max dates
      if (this.committedAnchorDate === null) {
        return this.isRangeSelection;
      }
      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction) {
      if (this.committedAnchorDate === null) {
        return false;
      }
      const boundaryDate = direction === -1 ? this.minAllowedDate : this.maxAllowedDate;
      return !datesAreInTheSamePeriod(this.committedAnchorDate, boundaryDate, this.committedPeriod);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue



PeriodSelectorvue_type_script_lang_ts.render = PeriodSelectorvue_type_template_id_90748800_render

/* harmony default export */ var PeriodSelector = (PeriodSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=544d8f10

const ReportingMenuvue_type_template_id_544d8f10_hoisted_1 = {
  class: "reportingMenu"
};
const ReportingMenuvue_type_template_id_544d8f10_hoisted_2 = ["aria-label"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_3 = ["data-category-id"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_4 = ["onClick"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_5 = {
  class: "hidden"
};
const ReportingMenuvue_type_template_id_544d8f10_hoisted_6 = {
  key: 2,
  role: "menu"
};
const ReportingMenuvue_type_template_id_544d8f10_hoisted_7 = ["href", "onClick", "title"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_8 = ["href", "onClick"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_9 = ["onClick"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const ReportingMenuvue_type_template_id_544d8f10_hoisted_11 = [ReportingMenuvue_type_template_id_544d8f10_hoisted_10];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_12 = {
  id: "mobile-left-menu",
  class: "sidenav sidenav--reporting-menu-mobile hide-on-large-only"
};
const ReportingMenuvue_type_template_id_544d8f10_hoisted_13 = ["data-category-id"];
const ReportingMenuvue_type_template_id_544d8f10_hoisted_14 = {
  key: 1,
  class: "collapsible collapsible-accordion"
};
const _hoisted_15 = {
  class: "collapsible-header"
};
const _hoisted_16 = {
  class: "collapsible-body"
};
const _hoisted_17 = ["onClick", "href"];
const _hoisted_18 = ["onClick", "href"];
function ReportingMenuvue_type_template_id_544d8f10_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MenuItemsDropdown = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MenuItemsDropdown");
  const _directive_side_nav = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("side-nav");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingMenuvue_type_template_id_544d8f10_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
    class: "navbar hide-on-med-and-down collapsible",
    role: "menu",
    "aria-label": _ctx.translate('CoreHome_MainNavigation')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menu, category => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["menuTab", {
        'active': category.id === _ctx.activeCategory
      }]),
      role: "menuitem",
      key: category.id,
      "data-category-id": category.id
    }, [category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(category.component), {
      key: 0,
      onAction: $event => _ctx.loadCategory(category)
    }, null, 40, ["onAction"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      class: "item",
      tabindex: "5",
      href: "",
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.loadCategory(category), ["prevent"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`menu-icon ${category.icon ? category.icon : category.subcategories && category.id === _ctx.activeCategory ? 'icon-chevron-down' : 'icon-chevron-right'}`)
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ReportingMenuvue_type_template_id_544d8f10_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_Menu')), 1)], 8, ReportingMenuvue_type_template_id_544d8f10_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ReportingMenuvue_type_template_id_544d8f10_hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, subcategory => {
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
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.subcategories, subcat => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["item", {
              active: subcat.id === _ctx.activeSubsubcategory && subcategory.id === _ctx.displayedSubcategory && category.id === _ctx.displayedCategory
            }]),
            tabindex: "5",
            href: `#?${_ctx.makeUrl(category, subcat)}`,
            onClick: $event => _ctx.loadSubcategory(category, subcat, $event),
            title: subcat.tooltip,
            key: subcat.id
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 11, ReportingMenuvue_type_template_id_544d8f10_hoisted_7);
        }), 128))]),
        _: 2
      }, 1032, ["menu-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        href: `#?${_ctx.makeUrl(category, subcategory)}`,
        class: "item",
        onClick: $event => _ctx.loadSubcategory(category, subcategory, $event),
        tabindex: "5"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, ReportingMenuvue_type_template_id_544d8f10_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), subcategory.help ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 2,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["item-help-icon", {
          active: _ctx.helpShownCategory && _ctx.helpShownCategory.subcategory === subcategory.id && _ctx.helpShownCategory.category === category.id && subcategory.help
        }]),
        tabindex: "5",
        href: "javascript:",
        onClick: $event => _ctx.showHelp(category, subcategory, $event)
      }, ReportingMenuvue_type_template_id_544d8f10_hoisted_11, 10, ReportingMenuvue_type_template_id_544d8f10_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, ReportingMenuvue_type_template_id_544d8f10_hoisted_3);
  }), 128))], 8, ReportingMenuvue_type_template_id_544d8f10_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ReportingMenuvue_type_template_id_544d8f10_hoisted_12, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menu, category => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "no-padding",
      key: category.id,
      "data-category-id": category.id
    }, [category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(category.component), {
      key: 0,
      onAction: $event => _ctx.loadCategory(category)
    }, null, 40, ["onAction"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !category.component ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ReportingMenuvue_type_template_id_544d8f10_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(category.icon ? category.icon : 'icon-chevron-down')
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, subcategory => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: subcategory.id
      }, [subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 0
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.subcategories, subcat => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          onClick: $event => _ctx.loadSubcategory(category, subcat),
          href: `#?${_ctx.makeUrl(category, subcat)}`,
          key: subcat.id
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 9, _hoisted_17);
      }), 128)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        onClick: $event => _ctx.loadSubcategory(category, subcategory),
        href: `#?${_ctx.makeUrl(category, subcategory)}`
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, _hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))])])])])), [[_directive_side_nav, {
      activator: _ctx.sideNavActivator
    }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, ReportingMenuvue_type_template_id_544d8f10_hoisted_13);
  }), 128))])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=544d8f10

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widgets.store.ts
function Widgets_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



function getWidgetChildren(widget) {
  const container = widget;
  if (container.widgets) {
    return container.widgets;
  }
  return [];
}
class Widgets_store_WidgetsStore {
  constructor() {
    Widgets_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isFetchedFirstTime: false,
      categorizedWidgets: {}
    }));
    Widgets_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (!this.privateState.isFetchedFirstTime) {
        // initiating a side effect in a computed property seems wrong, but it needs to be
        // executed after knowing a user's logged in and it will succeed.
        this.fetchAvailableWidgets();
      }
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState);
    }));
    Widgets_store_defineProperty(this, "widgets", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.categorizedWidgets));
  }
  fetchAvailableWidgets() {
    // if there's no idSite, don't make the request since it will just fail
    if (!src_MatomoUrl_MatomoUrl.parsed.value.idSite) {
      return Promise.resolve(this.widgets.value);
    }
    this.privateState.isFetchedFirstTime = true;
    return new Promise((resolve, reject) => {
      try {
        window.widgetsHelper.getAvailableWidgets(widgets => {
          const casted = widgets;
          this.privateState.categorizedWidgets = casted;
          resolve(this.widgets.value);
        });
      } catch (e) {
        reject(e);
      }
    });
  }
  reloadAvailableWidgets() {
    // Let's also update widgetslist so will be easier to update list of available widgets in
    // dashboard selector immediately
    window.widgetsHelper.clearAvailableWidgets();
    const fetchPromise = this.fetchAvailableWidgets();
    fetchPromise.then(() => {
      Matomo_Matomo.postEvent('WidgetsStore.reloaded');
    });
    return fetchPromise;
  }
}
/* harmony default export */ var Widgets_store = (new Widgets_store_WidgetsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=script&lang=ts










const ReportingMenuvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID = 'reportingmenu-help';
/* harmony default export */ var ReportingMenuvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MenuItemsDropdown: MenuItemsDropdown
  },
  directives: {
    SideNav: SideNav
  },
  props: {},
  data() {
    return {
      showSubcategoryHelpOnLoad: null,
      initialLoad: true,
      helpShownCategory: null
    };
  },
  computed: {
    sideNavActivator() {
      return document.querySelector('nav .activateLeftMenu');
    },
    menu() {
      const categories = ReportingMenu_store.menu.value;
      categories.forEach(category => {
        if (category.widget && category.widget.indexOf('.') > 0) {
          const [widgetPlugin, widgetComponent] = category.widget.split('.');
          category.component = useExternalPluginComponent(widgetPlugin, widgetComponent);
        }
      });
      return categories;
    },
    activeCategory() {
      return ReportingMenu_store.activeCategory.value;
    },
    activeSubcategory() {
      return ReportingMenu_store.activeSubcategory.value;
    },
    activeSubsubcategory() {
      return ReportingMenu_store.activeSubsubcategory.value;
    },
    displayedCategory() {
      return src_MatomoUrl_MatomoUrl.parsed.value.category;
    },
    displayedSubcategory() {
      return src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
    }
  },
  created() {
    ReportingMenu_store.fetchMenuItems().then(() => {
      // load first, initial page if no subcategory is present
      if (!src_MatomoUrl_MatomoUrl.parsed.value.subcategory) {
        this.loadFirstPageOfActiveSection();
      }
    });
    // Keep the active top-menu section highlighted in sync with the active group. The group lives
    // in the URL hash (to avoid leaking into other links), so the server cannot set this active
    // state; we do it here, which only runs within the reporting SPA.
    this.updateTopMenuActiveState();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.parsed.value, query => {
      // When no subcategory is in the URL - e.g. right after switching section via the top menu,
      // which only changes the URL hash - load the active section's first page so the displayed
      // report switches too, not just the menu.
      if (!query.subcategory) {
        this.loadFirstPageOfActiveSection();
        this.updateTopMenuActiveState();
        return;
      }
      const found = ReportingMenu_store.findSubcategory(query.category, query.subcategory);
      ReportingMenu_store.enterSubcategory(found.category, found.subcategory, found.subsubcategory);
      this.updateTopMenuActiveState();
    });
    Matomo_Matomo.on('matomoPageChange', () => {
      if (!this.initialLoad) {
        window.globalAjaxQueue.abort();
      }
      this.helpShownCategory = null;
      if (this.showSubcategoryHelpOnLoad) {
        this.showHelp(this.showSubcategoryHelpOnLoad.category, this.showSubcategoryHelpOnLoad.subcategory);
        this.showSubcategoryHelpOnLoad = null;
      }
      window.$('#loadingError,#loadingRateLimitError').hide();
      this.initialLoad = false;
    });
    Matomo_Matomo.on('updateReportingMenu', () => {
      ReportingMenu_store.reloadMenuItems().then(() => {
        const category = src_MatomoUrl_MatomoUrl.parsed.value.category;
        const subcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
        // we need to make sure to select same categories again
        if (category && subcategory) {
          const found = ReportingMenu_store.findSubcategory(category, subcategory);
          if (found.category) {
            ReportingMenu_store.enterSubcategory(found.category, found.subcategory, found.subsubcategory);
          }
        }
      });
      Widgets_store.reloadAvailableWidgets();
    });
  },
  methods: {
    loadFirstPageOfActiveSection() {
      const menu = ReportingMenu_store.menu.value;
      const categoryToLoad = menu[0];
      if (!categoryToLoad) {
        return;
      }
      const subcategoryToLoad = categoryToLoad.subcategories[0];
      if (!subcategoryToLoad) {
        return;
      }
      ReportingMenu_store.enterSubcategory(categoryToLoad, subcategoryToLoad);
      this.propagateUrlChange(categoryToLoad, subcategoryToLoad);
    },
    updateTopMenuActiveState() {
      const activeGroup = src_MatomoUrl_MatomoUrl.parsed.value.group || '';
      // Top-menu entries for reporting sections carry their group as a data attribute (empty for
      // the default "Analytics" section). Toggle the active state of the matching entry.
      document.querySelectorAll('[data-reporting-group]').forEach(link => {
        const listItem = link.closest('li');
        if (!listItem) {
          return;
        }
        const group = link.getAttribute('data-reporting-group') || '';
        listItem.classList.toggle('active', group === activeGroup);
      });
    },
    propagateUrlChange(category, subcategory) {
      const queryParams = src_MatomoUrl_MatomoUrl.parsed.value;
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
    loadCategory(category) {
      Notifications_store.remove(ReportingMenuvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID);
      const isActive = ReportingMenu_store.toggleCategory(category);
      // one subcategory or a widget and some subcategories to allow to load the category
      const {
        subcategories
      } = category;
      const categoryCanLoad = subcategories && subcategories.length === 1 || category.widget && subcategories && subcategories.length;
      if (isActive && categoryCanLoad) {
        this.helpShownCategory = null;
        const subcategory = category.subcategories[0];
        this.propagateUrlChange(category, subcategory);
      }
    },
    loadSubcategory(category, subcategory, event) {
      if (event && (event.shiftKey || event.ctrlKey || event.metaKey)) {
        return;
      }
      Notifications_store.remove(ReportingMenuvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID);
      if (subcategory && subcategory.id === src_MatomoUrl_MatomoUrl.parsed.value.subcategory && category.id === src_MatomoUrl_MatomoUrl.parsed.value.category) {
        this.helpShownCategory = null;
        // this menu item is already active, a location change success would not be triggered,
        // instead trigger an event (after the URL changes)
        setTimeout(() => {
          Matomo_Matomo.postEvent('loadPage', category.id, subcategory.id);
        });
      }
    },
    makeUrl(category, subcategory) {
      const {
        idSite,
        period,
        date,
        segment,
        comparePeriods,
        compareDates,
        compareSegments,
        group
      } = src_MatomoUrl_MatomoUrl.parsed.value;
      const params = {
        idSite,
        period,
        date,
        segment,
        comparePeriods,
        compareDates,
        compareSegments,
        category: category.id,
        subcategory: subcategory.id
      };
      // keep the active reporting section (e.g. "AI Insights") while navigating within it
      if (group) {
        params.group = group;
      }
      return src_MatomoUrl_MatomoUrl.stringify(params);
    },
    htmlEntities(v) {
      return Matomo_Matomo.helper.htmlEntities(v);
    },
    showHelp(category, subcategory, event) {
      const parsedUrl = src_MatomoUrl_MatomoUrl.parsed.value;
      const currentCategory = parsedUrl.category;
      const currentSubcategory = parsedUrl.subcategory;
      if ((currentCategory !== category.id || currentSubcategory !== subcategory.id) && event) {
        this.showSubcategoryHelpOnLoad = {
          category,
          subcategory
        };
        src_MatomoUrl_MatomoUrl.updateHash(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
          category: category.id,
          subcategory: subcategory.id
        }));
        return;
      }
      if (this.helpShownCategory && category.id === this.helpShownCategory.category && subcategory.id === this.helpShownCategory.subcategory) {
        Notifications_store.remove(ReportingMenuvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID);
        this.helpShownCategory = null;
        return;
      }
      const prefixText = translate('CoreHome_ReportingCategoryHelpPrefix', category.name, subcategory.name);
      const prefix = `<strong>${prefixText}</strong><br/>`;
      Notifications_store.show({
        context: 'info',
        id: ReportingMenuvue_type_script_lang_ts_REPORTING_HELP_NOTIFICATION_ID,
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



ReportingMenuvue_type_script_lang_ts.render = ReportingMenuvue_type_template_id_544d8f10_render

/* harmony default export */ var ReportingMenu = (ReportingMenuvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportMetadata/ReportMetadata.store.ts
function ReportMetadata_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




class ReportMetadata_store_ReportMetadataStore {
  constructor() {
    ReportMetadata_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      reports: []
    }));
    ReportMetadata_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState));
    ReportMetadata_store_defineProperty(this, "reports", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.reports));
    ReportMetadata_store_defineProperty(this, "reportsPromise", void 0);
  }
  // TODO: it used to return an empty array when nothing was found, will that be an issue?
  findReport(reportModule, reportAction) {
    return this.reports.value.find(r => r.module === reportModule && r.action === reportAction);
  }
  fetchReportMetadata() {
    if (!this.reportsPromise) {
      this.reportsPromise = AjaxHelper_AjaxHelper.fetch({
        method: 'API.getReportMetadata',
        filter_limit: '-1',
        idSite: Matomo_Matomo.idSite || src_MatomoUrl_MatomoUrl.parsed.value.idSite
      }).then(response => {
        this.privateState.reports = response;
        return response;
      });
    }
    return this.reportsPromise.then(() => this.reports.value);
  }
}
/* harmony default export */ var ReportMetadata_store = (new ReportMetadata_store_ReportMetadataStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportHeader/ReportHeader.vue?vue&type=template&id=f802816a

const ReportHeadervue_type_template_id_f802816a_hoisted_1 = {
  class: "reportHeader"
};
const ReportHeadervue_type_template_id_f802816a_hoisted_2 = {
  class: "reportHeader__main"
};
const ReportHeadervue_type_template_id_f802816a_hoisted_3 = ["role", "tabindex", "title"];
const ReportHeadervue_type_template_id_f802816a_hoisted_4 = {
  class: "u-visuallyHidden"
};
const ReportHeadervue_type_template_id_f802816a_hoisted_5 = {
  class: "reportHeader__widgetControls"
};
const ReportHeadervue_type_template_id_f802816a_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "reportHeader__actions"
}, null, -1);
function ReportHeadervue_type_template_id_f802816a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_WidgetControls = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetControls");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportHeadervue_type_template_id_f802816a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportHeadervue_type_template_id_f802816a_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["reportHeader__title widgetName", {
      'reportHeader__title--clickable': _ctx.titleClickable
    }]),
    role: _ctx.titleClickable ? 'button' : null,
    tabindex: _ctx.titleClickable ? 0 : null,
    title: _ctx.titleClickable ? _ctx.titleClickHint : null,
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args)),
    onKeydown: [_cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args), ["prevent"]), ["enter"])), _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args), ["prevent"]), ["space"]))]
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)], 42, ReportHeadervue_type_template_id_f802816a_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ReportHeadervue_type_template_id_f802816a_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Widget')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportHeadervue_type_template_id_f802816a_hoisted_5, [_ctx.hasControls ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_WidgetControls, {
    key: 0,
    "can-minimise": _ctx.controls.minimise,
    "can-maximise": _ctx.controls.maximise,
    "can-refresh": _ctx.controls.refresh,
    "can-close": _ctx.controls.close,
    onMinimise: _cache[3] || (_cache[3] = $event => _ctx.onControl('minimise')),
    onMaximise: _cache[4] || (_cache[4] = $event => _ctx.onControl('maximise')),
    onRefresh: _cache[5] || (_cache[5] = $event => _ctx.onControl('refresh')),
    onClose: _cache[6] || (_cache[6] = $event => _ctx.onControl('close'))
  }, null, 8, ["can-minimise", "can-maximise", "can-refresh", "can-close"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), ReportHeadervue_type_template_id_f802816a_hoisted_6]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportHeader/ReportHeader.vue?vue&type=template&id=f802816a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetControls/WidgetControls.vue?vue&type=template&id=03428934

const WidgetControlsvue_type_template_id_03428934_hoisted_1 = {
  class: "widgetControls"
};
const WidgetControlsvue_type_template_id_03428934_hoisted_2 = ["title", "aria-label", "onClick"];
function WidgetControlsvue_type_template_id_03428934_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetControlsvue_type_template_id_03428934_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.visibleControls, control => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: control.id,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["widgetControls__action", `widgetControl-${control.id}`]),
      title: control.label,
      "aria-label": control.label,
      onClick: $event => _ctx.$emit(control.id)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["widgetControls__icon", control.icon])
    }, null, 2)], 10, WidgetControlsvue_type_template_id_03428934_hoisted_2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetControls/WidgetControls.vue?vue&type=template&id=03428934

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetControls/WidgetControls.vue?vue&type=script&lang=ts


/* harmony default export */ var WidgetControlsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    canMinimise: Boolean,
    canMaximise: Boolean,
    canRefresh: Boolean,
    canClose: Boolean
  },
  emits: ['minimise', 'maximise', 'refresh', 'close'],
  computed: {
    visibleControls() {
      const controls = [{
        id: 'refresh',
        icon: 'icon-reload',
        label: translate('General_Refresh'),
        visible: this.canRefresh
      }, {
        id: 'minimise',
        icon: 'icon-minimise',
        label: translate('Dashboard_Minimise'),
        visible: this.canMinimise
      }, {
        id: 'maximise',
        icon: 'icon-fullscreen',
        label: translate('Dashboard_Maximise'),
        visible: this.canMaximise
      }, {
        id: 'close',
        icon: 'icon-close',
        label: translate('General_Close'),
        visible: this.canClose
      }];
      return controls.filter(control => control.visible);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetControls/WidgetControls.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetControls/WidgetControls.vue



WidgetControlsvue_type_script_lang_ts.render = WidgetControlsvue_type_template_id_03428934_render

/* harmony default export */ var WidgetControls = (WidgetControlsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportHeader/ReportHeader.vue?vue&type=script&lang=ts



// Which widget controls each context exposes. Kept here so every surface that renders
// the header stays consistent with the redesign spec. `dashboard` is the normal widget state
// (all controls only make sense on a dashboard); `maximised`/`collapsed` are its state
// variants; `widgetized`/`preview` render no controls. Consumers outside a widget (e.g.
// full-page reports) pass a no-control context.
const CONTROLS_BY_CONTEXT = {
  dashboard: {
    minimise: true,
    maximise: true,
    refresh: true,
    close: true
  },
  maximised: {
    minimise: true,
    maximise: false,
    refresh: true,
    close: false
  },
  collapsed: {
    minimise: false,
    maximise: true,
    refresh: false,
    close: true
  },
  widgetized: {
    minimise: false,
    maximise: false,
    refresh: false,
    close: false
  },
  preview: {
    minimise: false,
    maximise: false,
    refresh: false,
    close: false
  }
};
/* harmony default export */ var ReportHeadervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    context: {
      type: String,
      default: 'dashboard'
    },
    title: String,
    titleClickable: Boolean,
    titleClickHint: String
  },
  components: {
    WidgetControls: WidgetControls
  },
  emits: ['minimise', 'maximise', 'refresh', 'close', 'titleClick'],
  computed: {
    controls() {
      return CONTROLS_BY_CONTEXT[this.context] || CONTROLS_BY_CONTEXT.widgetized;
    },
    hasControls() {
      const c = this.controls;
      return c.minimise || c.maximise || c.refresh || c.close;
    }
  },
  methods: {
    translate: translate,
    onTitleClick() {
      if (this.titleClickable) {
        this.$emit('titleClick');
      }
    },
    onControl(intent) {
      // Re-emit for Vue-native consumers...
      this.$emit(intent);
      // ...and dispatch a bubbling native event so non-Vue owners (the jQuery dashboard
      // widget) can bridge control intents back to their existing handlers.
      this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, {
        bubbles: true
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportHeader/ReportHeader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportHeader/ReportHeader.vue



ReportHeadervue_type_script_lang_ts.render = ReportHeadervue_type_template_id_f802816a_render

/* harmony default export */ var ReportHeader = (ReportHeadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=24b8f926

const WidgetLoadervue_type_template_id_24b8f926_hoisted_1 = {
  class: "widgetLoader"
};
const WidgetLoadervue_type_template_id_24b8f926_hoisted_2 = {
  key: 0
};
const WidgetLoadervue_type_template_id_24b8f926_hoisted_3 = {
  key: 1,
  class: "notification system notification-error"
};
const WidgetLoadervue_type_template_id_24b8f926_hoisted_4 = ["href"];
const WidgetLoadervue_type_template_id_24b8f926_hoisted_5 = {
  key: 2,
  class: "notification system notification-error"
};
const WidgetLoadervue_type_template_id_24b8f926_hoisted_6 = {
  class: "theWidgetContent",
  ref: "widgetContent"
};
function WidgetLoadervue_type_template_id_24b8f926_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_24b8f926_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": _ctx.finalLoadingMessage,
    loading: _ctx.loading
  }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.widgetName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", WidgetLoadervue_type_template_id_24b8f926_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.widgetName), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loadingFailedRateLimit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_24b8f926_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), _ctx.hasErrorFaqLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://matomo.org/faq/troubleshooting/faq_19489/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequestFaqLink')), 9, WidgetLoadervue_type_template_id_24b8f926_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_24b8f926_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRateLimit')), 1))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loadingFailed]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetLoadervue_type_template_id_24b8f926_hoisted_6, null, 512)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=24b8f926

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SearchFiltersPersistence/SearchFiltersPersistence.store.ts
function SearchFiltersPersistence_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class SearchFiltersPersistence_store_SearchFiltersPersistenceStore {
  constructor() {
    SearchFiltersPersistence_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      module: '',
      action: '',
      category: '',
      subcategory: '',
      idSite: '',
      widgetSearchFilters: {}
    }));
    SearchFiltersPersistence_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    Matomo_Matomo.on('matomoPageChange', () => {
      if (!this.isCurrentPage()) {
        this.resetSearchFilters();
      }
      this.updateCurrentRoutingFromUrl();
    });
  }
  resetSearchFilters() {
    this.privateState.widgetSearchFilters = {};
  }
  getSearchFilters(widgetId) {
    return this.state.value.widgetSearchFilters[widgetId] || {};
  }
  setSearchFilters(widgetId, filters) {
    if (widgetId) {
      this.privateState.widgetSearchFilters[widgetId] = filters;
    }
  }
  updateCurrentRoutingFromUrl() {
    const url = src_MatomoUrl_MatomoUrl.parsed.value;
    this.privateState.module = url.module;
    this.privateState.action = url.action;
    this.privateState.category = url.category;
    this.privateState.subcategory = url.subcategory;
    this.privateState.idSite = url.idSite;
  }
  isCurrentPage() {
    const url = src_MatomoUrl_MatomoUrl.parsed.value;
    return this.state.value.module === url.module && this.state.value.action === url.action && this.state.value.category === url.category && this.state.value.subcategory === url.subcategory && this.state.value.idSite === url.idSite;
  }
}
/* harmony default export */ var SearchFiltersPersistence_store = (new SearchFiltersPersistence_store_SearchFiltersPersistenceStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=script&lang=ts









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
    widgetName: String,
    loadingMessage: String,
    suppressNotifications: Boolean
  },
  components: {
    ActivityIndicator: ActivityIndicator
  },
  data() {
    return {
      loading: false,
      loadingFailed: false,
      loadingFailedRateLimit: false,
      changeCounter: 0,
      lastWidgetAbortController: null
    };
  },
  watch: {
    widgetParams(parameters) {
      if (parameters) {
        this.loadWidgetUrl(parameters, this.changeCounter += 1);
      }
    }
  },
  computed: {
    finalLoadingMessage() {
      if (this.loadingMessage) {
        return this.loadingMessage;
      }
      if (!this.widgetName) {
        return translate('General_LoadingData');
      }
      return translate('General_LoadingPopover', this.widgetName);
    },
    hasErrorFaqLink() {
      const isGeneralSettingsAdminEnabled = Matomo_Matomo.config.enable_general_settings_admin;
      const isPluginsAdminEnabled = Matomo_Matomo.config.enable_plugins_admin;
      return Matomo_Matomo.hasSuperUserAccess && (isGeneralSettingsAdminEnabled || isPluginsAdminEnabled);
    }
  },
  mounted() {
    if (this.widgetParams) {
      this.loadWidgetUrl(this.widgetParams, this.changeCounter += 1);
    }
  },
  beforeUnmount() {
    this.cleanupLastWidgetContent();
  },
  methods: {
    abortHttpRequestIfNeeded() {
      if (this.lastWidgetAbortController) {
        this.lastWidgetAbortController.abort();
        this.lastWidgetAbortController = null;
      }
    },
    cleanupLastWidgetContent() {
      const widgetContent = this.$refs.widgetContent;
      Matomo_Matomo.helper.destroyVueComponent(widgetContent);
      if (widgetContent) {
        widgetContent.innerHTML = '';
      }
    },
    getWidgetUrl(parameters) {
      const urlParams = src_MatomoUrl_MatomoUrl.parsed.value;
      let fullParameters = Object.assign({}, parameters || {});
      const paramsToForward = Object.keys(Object.assign(Object.assign({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
        idSite: '',
        period: '',
        date: '',
        segment: '',
        widget: ''
      }));
      paramsToForward.forEach(key => {
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
    loadWidgetUrl(parameters, thisChangeId) {
      this.loading = true;
      this.abortHttpRequestIfNeeded();
      this.cleanupLastWidgetContent();
      this.lastWidgetAbortController = new AbortController();
      let searchFilters = {};
      if (parameters.uniqueId) {
        searchFilters = SearchFiltersPersistence_store.getSearchFilters(parameters.uniqueId);
      }
      AjaxHelper_AjaxHelper.fetch(this.getWidgetUrl(Object.assign(parameters, searchFilters)), {
        format: 'html',
        abortController: this.lastWidgetAbortController
      }).then(response => {
        if (thisChangeId !== this.changeCounter || typeof response !== 'string') {
          // another widget was requested meanwhile, ignore this response
          return;
        }
        this.lastWidgetAbortController = null;
        this.loading = false;
        this.loadingFailed = false;
        const widgetContent = this.$refs.widgetContent;
        window.$(widgetContent).html(response);
        const $content = window.$(widgetContent).children();
        if (this.widgetName) {
          // we need to respect the widget title, which overwrites a possibly set report title
          let $title = $content.find('> .card-content .card-title');
          if (!$title.length) {
            $title = $content.find('> h2');
          }
          if ($title.length) {
            // required to use htmlEntities since it also escapes '{{' format items
            $title.html(Matomo_Matomo.helper.htmlEntities(this.widgetName));
          }
        }
        Matomo_Matomo.helper.compileVueEntryComponents($content);
        if (!this.suppressNotifications) {
          Notifications_store.parseNotificationDivs();
        }
        setTimeout(() => {
          Matomo_Matomo.postEvent('widget:loaded', {
            parameters,
            element: $content
          });
        });
      }).catch(response => {
        if (thisChangeId !== this.changeCounter) {
          // another widget was requested meanwhile, ignore this response
          return;
        }
        this.lastWidgetAbortController = null;
        this.cleanupLastWidgetContent();
        this.loading = false;
        if (response.xhrStatus === 'abort') {
          return;
        }
        if (response.status === 429) {
          this.loadingFailedRateLimit = true;
        }
        this.loadingFailed = true;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue



WidgetLoadervue_type_script_lang_ts.render = WidgetLoadervue_type_template_id_24b8f926_render

/* harmony default export */ var WidgetLoader = (WidgetLoadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Widget/ClientWidgetRenderer.vue?vue&type=template&id=d4ca1a74

function ClientWidgetRenderervue_type_template_id_d4ca1a74_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  return _ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ActivityIndicator, {
    key: 0,
    loading: true,
    "loading-message": _ctx.translate('General_LoadingData')
  }, null, 8, ["loading-message"])) : _ctx.loadingFailed ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')), 1)]),
    _: 1
  })) : _ctx.componentToRender ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.componentToRender), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeProps"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    key: 2
  }, _ctx.componentProps)), null, 16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/ClientWidgetRenderer.vue?vue&type=template&id=d4ca1a74

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Widget/ClientWidgetRenderer.vue?vue&type=script&lang=ts




/* harmony default export */ var ClientWidgetRenderervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    widget: {
      type: Object,
      required: true
    },
    widgetized: Boolean
  },
  components: {
    ActivityIndicator: ActivityIndicator,
    Alert: Alert
  },
  data() {
    return {
      componentToRender: null,
      loading: false,
      loadingFailed: false
    };
  },
  watch: {
    widget: {
      handler() {
        this.loadComponent();
>>>>>>> a9dc5eca3f (Move the dropdown to the reportHeader component)
      },
      getCurrentRollingDateParamIfOwnedByPreset() {
        if (this.uiSelection.type !== "preset") {
          return null;
        }
        const parsedPeriod = instance$1.parsed.value.period || "";
        const parsedDate = instance$1.parsed.value.date || "";
        if (parsedPeriod !== this.committedPeriod || !parsedDate) {
          return null;
        }
        const presetId = getTokenPresetIdFromPeriodAndDate(parsedPeriod, parsedDate);
        if (presetId !== this.uiSelection.id) {
          return null;
        }
        return parsedDate;
      },
      resetSelectedDateValues() {
        this.committedAnchorDate = null;
        this.appliedRangeStartDate = null;
        this.appliedRangeEndDate = null;
      },
      applyDateValuesFromHash(period, date) {
        if (period === RANGE_PERIOD) {
          const periodObj = Periods$1.get(period).parse(date);
          const [startDate, endDate] = periodObj.getDateRange();
          this.committedAnchorDate = startDate;
          this.appliedRangeStartDate = format(startDate);
          this.appliedRangeEndDate = format(endDate);
          return;
        }
        this.committedAnchorDate = parseDate(date);
        this.setRangeStartEndFromPeriod(period, date);
        if (isSingleCalendarPeriod(period)) {
          this.singleCalendarPeriod = period;
        }
        this.singleCalendarSelectedDate = this.committedAnchorDate;
      },
      updateSelectedValuesFromHash() {
        const date = instance$1.parsed.value.date || "";
        const period = instance$1.parsed.value.period || "";
        const currentSelectionKey = getSelectionKey(period, date);
        const currentContextKey = this.getCurrentContextKey();
        if (shouldSkipHashSync(
          currentSelectionKey,
          currentContextKey,
          this.nextHashUiSelection,
          this.lastKnownHashSelectionKey,
          this.lastKnownHashContextKey
        )) {
          return;
        }
        const hashSyncState = resolveSyncedUiSelection(
          currentSelectionKey,
          currentContextKey,
          this.nextHashUiSelection,
          this.nextHashSelectionKey
        );
        this.nextHashUiSelection = hashSyncState.nextHashUiSelection;
        this.nextHashSelectionKey = hashSyncState.nextHashSelectionKey;
        this.lastInteractionSource = hashSyncState.lastInteractionSource;
        this.lastKnownHashSelectionKey = hashSyncState.lastKnownHashSelectionKey;
        this.lastKnownHashContextKey = hashSyncState.lastKnownHashContextKey;
        this.applyUiSelectionFromHash(
          period,
          date,
          hashSyncState.syncedUiSelection
        );
        this.committedPeriod = period;
        this.selectedPeriod = period;
        this.resetSelectedDateValues();
        try {
          Periods$1.parse(period, date);
        } catch (e) {
          if (period === RANGE_PERIOD) {
            this.isRangeValid = false;
          } else {
            this.isRangeValid = null;
          }
          return;
        }
        this.applyDateValuesFromHash(period, date);
        this.isRangeValid = period === RANGE_PERIOD ? true : null;
        this.pendingPresetSelection = null;
        syncSelectionDisplayState(this);
        this.compareAppliedSignature = this.compareCurrentSignature;
      },
      setRangeStartEndFromPeriod(period, dateStr) {
        const dateRange = Periods$1.parse(period, dateStr).getDateRange();
        this.appliedRangeStartDate = format(
          dateRange[0] < this.minAllowedDate ? this.minAllowedDate : dateRange[0]
        );
        this.appliedRangeEndDate = format(
          dateRange[1] > this.maxAllowedDate ? this.maxAllowedDate : dateRange[1]
        );
      },
      canInteractWithRangeCalendar() {
        return this.calendarViewport === "range" && this.selectedPeriod === RANGE_PERIOD;
      },
      onRangeChange(start, end) {
        if (!this.canInteractWithRangeCalendar()) {
          return;
        }
        if (!start || !end) {
          this.isRangeValid = false;
          return;
        }
        this.isRangeValid = true;
        this.appliedRangeStartDate = start;
        this.appliedRangeEndDate = end;
        this.setUiSelection({ type: "period", id: RANGE_PERIOD }, "range");
        this.clearPresetSelection();
      },
      isApplyEnabled() {
        return isApplyButtonEnabled({
          uiSelectionType: this.uiSelection.type,
          uiSelectedPeriod: this.selectedPeriod,
          hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
          hasPendingPresetSelection: !!this.pendingPresetSelection,
          isRangeValid: this.isRangeValid,
          isCompareDirty: this.isCompareDirty,
          isComparing: this.isComparing,
          comparePeriodType: this.comparePeriodType,
          isCompareRangeValid: this.isCompareRangeValid()
        });
      },
      shouldDisplayInvalidComparisonMessage() {
        return this.shouldShowInvalidComparisonMessage && this.hasInvalidCustomComparison();
      },
      hasInvalidCustomComparison() {
        return !!this.isComparing && this.comparePeriodType === "custom" && !this.isCompareRangeValid();
      },
      showInvalidComparisonMessage() {
        if (!this.hasInvalidCustomComparison()) {
          return;
        }
        this.shouldShowInvalidComparisonMessage = true;
      },
      dismissInvalidComparisonMessage() {
        this.shouldShowInvalidComparisonMessage = false;
      },
      onDisabledApplyInteraction() {
        this.showInvalidComparisonMessage();
      },
      onCompareToggleUpdated(value) {
        this.isComparing = value;
        this.dismissInvalidComparisonMessage();
      },
      onComparePeriodTypeUpdated(value) {
        this.comparePeriodType = value;
        this.dismissInvalidComparisonMessage();
      },
      onCompareStartDateUpdated(value) {
        this.compareStartDate = value;
        this.dismissInvalidComparisonMessage();
      },
      onCompareEndDateUpdated(value) {
        this.compareEndDate = value;
        this.dismissInvalidComparisonMessage();
      },
      closePeriodSelector() {
        this.$refs.root.classList.remove("expanded");
      },
      isCompareRangeValid() {
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
      },
      movePeriod(direction) {
        if (!this.canMovePeriod(direction)) {
          return;
        }
        const baseDate = this.committedAnchorDate || /* @__PURE__ */ new Date();
        const shiftedDate = shiftDateByPeriod(baseDate, this.committedPeriod, direction);
        const clampedDate = clampDateToBounds$1(shiftedDate, this.minAllowedDate, this.maxAllowedDate);
        this.setPiwikPeriodAndDate(this.committedPeriod, clampedDate);
      },
      isPeriodMoveDisabled(direction) {
        if (this.committedAnchorDate === null) {
          return this.isRangeSelection;
        }
        return this.isRangeSelection || !this.canMovePeriod(direction);
      },
      canMovePeriod(direction) {
        if (this.committedAnchorDate === null) {
          return false;
        }
        const boundaryDate = direction === -1 ? this.minAllowedDate : this.maxAllowedDate;
        return !datesAreInTheSamePeriod(
          this.committedAnchorDate,
          boundaryDate,
          this.committedPeriod
        );
      }
    }
  });
  const _hoisted_1$i = ["disabled"];
  const _hoisted_2$d = ["title"];
  const _hoisted_3$d = { class: "flex" };
  const _hoisted_4$a = {
    key: 0,
    id: "ajaxLoadingCalendar"
  };
  const _hoisted_5$9 = { class: "loadingSegment" };
  const _hoisted_6$8 = ["disabled"];
  function _sfc_render$k(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PeriodSelectorOptionsColumn = vue.resolveComponent("PeriodSelectorOptionsColumn");
    const _component_PeriodSelectorCalendarColumn = vue.resolveComponent("PeriodSelectorCalendarColumn");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    const _directive_expand_on_click = vue.resolveDirective("expand-on-click");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      ref: "root",
      class: vue.normalizeClass(["periodSelector piwikSelector", { "periodSelector-withPrevNext": _ctx.canShowMovePeriod }])
    }, [
      _ctx.canShowMovePeriod ? (vue.openBlock(), vue.createElementBlock("button", {
        key: 0,
        class: "move-period move-period-prev",
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.movePeriod(-1)),
        disabled: _ctx.isPeriodMoveDisabled(-1)
      }, [..._cache[15] || (_cache[15] = [
        vue.createElementVNode("span", { class: "icon-chevron-left" }, null, -1)
      ])], 8, _hoisted_1$i)) : vue.createCommentVNode("", true),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("button", {
        ref: "title",
        id: "date",
        class: "title",
        tabindex: "4",
        title: _ctx.translate("General_ChooseDate", _ctx.currentlyViewingText)
      }, [
        _cache[16] || (_cache[16] = vue.createElementVNode("span", { class: "icon icon-calendar" }, null, -1)),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.currentlyViewingText), 1)
      ], 8, _hoisted_2$d)), [
        [_directive_tooltips]
      ]),
      vue.createElementVNode("div", {
        id: "periodMore",
        class: vue.normalizeClass(["dropdown", _ctx.selectedPeriod === "range" ? "dual-calendar" : "single-calendar"])
      }, [
        vue.createElementVNode("div", _hoisted_3$d, [
          vue.createVNode(_component_PeriodSelectorOptionsColumn, {
            "ui-selected-period": _ctx.selectedPeriod,
            "periods-filtered": _ctx.periodsFiltered,
            "applied-period": _ctx.committedPeriod,
            "active-preset-id": _ctx.activePresetId,
            "min-allowed-date": _ctx.minAllowedDate,
            "max-allowed-date": _ctx.maxAllowedDate,
            "onUpdate:uiSelectedPeriod": _cache[1] || (_cache[1] = ($event) => _ctx.selectedPeriod = $event),
            onPeriodSelect: _cache[2] || (_cache[2] = ($event) => _ctx.onPeriodOptionSelected($event)),
            onPeriodDblclick: _cache[3] || (_cache[3] = ($event) => _ctx.onPeriodOptionDblClick($event)),
            onPresetSelect: _cache[4] || (_cache[4] = ($event) => _ctx.onPresetDateRangeSelected($event)),
            onPresetDblclick: _cache[5] || (_cache[5] = ($event) => _ctx.onPresetDateRangeDblClick($event))
          }, null, 8, ["ui-selected-period", "periods-filtered", "applied-period", "active-preset-id", "min-allowed-date", "max-allowed-date"]),
          vue.createVNode(_component_PeriodSelectorCalendarColumn, {
            "ui-selection": _ctx.uiSelection,
            "calendar-viewport": _ctx.calendarViewport,
            "display-range-start-date": _ctx.displayRangeStartDate,
            "display-range-end-date": _ctx.displayRangeEndDate,
            "single-calendar-period": _ctx.singleCalendarPeriod,
            "single-calendar-selected-date": _ctx.singleCalendarSelectedDate,
            "is-comparison-enabled": _ctx.isComparisonEnabled,
            "is-comparing": _ctx.isComparing,
            "compare-period-type": _ctx.comparePeriodType,
            "compare-start-date": _ctx.compareStartDate,
            "compare-end-date": _ctx.compareEndDate,
            "compare-period-dropdown-options": _ctx.comparePeriodDropdownOptions,
            "show-invalid-comparison-message": _ctx.shouldDisplayInvalidComparisonMessage(),
            "is-apply-enabled": _ctx.isApplyEnabled(),
            onRangeChange: _cache[6] || (_cache[6] = ($event) => _ctx.onRangeChange($event.start, $event.end)),
            onSingleDateSelect: _cache[7] || (_cache[7] = ($event) => _ctx.onDatePickerSelected($event)),
            onApplyClick: _cache[8] || (_cache[8] = ($event) => _ctx.onApplyClicked()),
            onDisabledApplyInteraction: _cache[9] || (_cache[9] = ($event) => _ctx.onDisabledApplyInteraction()),
            "onUpdate:isComparing": _cache[10] || (_cache[10] = ($event) => _ctx.onCompareToggleUpdated($event)),
            "onUpdate:comparePeriodType": _cache[11] || (_cache[11] = ($event) => _ctx.onComparePeriodTypeUpdated($event)),
            "onUpdate:compareStartDate": _cache[12] || (_cache[12] = ($event) => _ctx.onCompareStartDateUpdated($event)),
            "onUpdate:compareEndDate": _cache[13] || (_cache[13] = ($event) => _ctx.onCompareEndDateUpdated($event))
          }, null, 8, ["ui-selection", "calendar-viewport", "display-range-start-date", "display-range-end-date", "single-calendar-period", "single-calendar-selected-date", "is-comparison-enabled", "is-comparing", "compare-period-type", "compare-start-date", "compare-end-date", "compare-period-dropdown-options", "show-invalid-comparison-message", "is-apply-enabled"])
        ]),
        _ctx.isLoadingNewPage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$a, [
          vue.createVNode(_component_ActivityIndicator, { loading: true }),
          vue.createElementVNode("div", _hoisted_5$9, vue.toDisplayString(_ctx.translate("SegmentEditor_LoadingSegmentedDataMayTakeSomeTime")), 1)
        ])) : vue.createCommentVNode("", true)
      ], 2),
      _ctx.canShowMovePeriod ? (vue.openBlock(), vue.createElementBlock("button", {
        key: 1,
        class: "move-period move-period-next",
        onClick: _cache[14] || (_cache[14] = ($event) => _ctx.movePeriod(1)),
        disabled: _ctx.isPeriodMoveDisabled(1)
      }, [..._cache[17] || (_cache[17] = [
        vue.createElementVNode("span", { class: "icon-chevron-right" }, null, -1)
      ])], 8, _hoisted_6$8)) : vue.createCommentVNode("", true)
    ], 2)), [
      [_directive_expand_on_click, {
        expander: "title",
        onExpand: _ctx.onExpand,
        onClosed: _ctx.onClosed
      }]
    ]);
  }
  const PeriodSelector = /* @__PURE__ */ _export_sfc(_sfc_main$k, [["render", _sfc_render$k]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function getWidgetChildren(widget) {
    const container = widget;
    if (container.widgets) {
      return container.widgets;
    }
    return [];
  }
  class WidgetsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isFetchedFirstTime: false,
        categorizedWidgets: {}
      }));
      __publicField(this, "state", vue.computed(() => {
        if (!this.privateState.isFetchedFirstTime) {
          this.fetchAvailableWidgets();
        }
        return vue.readonly(this.privateState);
      }));
      __publicField(this, "widgets", vue.computed(() => this.state.value.categorizedWidgets));
    }
    fetchAvailableWidgets() {
      if (!instance$1.parsed.value.idSite) {
        return Promise.resolve(this.widgets.value);
      }
      this.privateState.isFetchedFirstTime = true;
      return new Promise((resolve2, reject) => {
        try {
          window.widgetsHelper.getAvailableWidgets((widgets) => {
            const casted = widgets;
            this.privateState.categorizedWidgets = casted;
            resolve2(this.widgets.value);
          });
        } catch (e) {
          reject(e);
        }
      });
    }
    reloadAvailableWidgets() {
      window.widgetsHelper.clearAvailableWidgets();
      const fetchPromise = this.fetchAvailableWidgets();
      fetchPromise.then(() => {
        Matomo.postEvent("WidgetsStore.reloaded");
      });
      return fetchPromise;
    }
  }
  const WidgetsStoreInstance = new WidgetsStore();
  const REPORTING_HELP_NOTIFICATION_ID = "reportingmenu-help";
  const _sfc_main$j = vue.defineComponent({
    components: {
      MenuItemsDropdown
    },
    directives: {
      SideNav
    },
    props: {},
    data() {
      return {
        showSubcategoryHelpOnLoad: null,
        initialLoad: true,
        helpShownCategory: null
      };
    },
    computed: {
      sideNavActivator() {
        return document.querySelector("nav .activateLeftMenu");
      },
      menu() {
        const categories = ReportingMenuStoreInstance.menu.value;
        categories.forEach((category) => {
          if (category.widget && category.widget.indexOf(".") > 0) {
            const [widgetPlugin, widgetComponent] = category.widget.split(".");
            category.component = useExternalPluginComponent(widgetPlugin, widgetComponent);
          }
        });
        return categories;
      },
      activeCategory() {
        return ReportingMenuStoreInstance.activeCategory.value;
      },
      activeSubcategory() {
        return ReportingMenuStoreInstance.activeSubcategory.value;
      },
      activeSubsubcategory() {
        return ReportingMenuStoreInstance.activeSubsubcategory.value;
      },
      displayedCategory() {
        return instance$1.parsed.value.category;
      },
      displayedSubcategory() {
        return instance$1.parsed.value.subcategory;
      }
    },
    created() {
      ReportingMenuStoreInstance.fetchMenuItems().then(() => {
        if (!instance$1.parsed.value.subcategory) {
          this.loadFirstPageOfActiveSection();
        }
      });
      this.updateTopMenuActiveState();
      vue.watch(() => instance$1.parsed.value, (query) => {
        if (!query.subcategory) {
          this.loadFirstPageOfActiveSection();
          this.updateTopMenuActiveState();
          return;
        }
        const found = ReportingMenuStoreInstance.findSubcategory(
          query.category,
          query.subcategory
        );
        ReportingMenuStoreInstance.enterSubcategory(
          found.category,
          found.subcategory,
          found.subsubcategory
        );
        this.updateTopMenuActiveState();
      });
      Matomo.on("matomoPageChange", () => {
        if (!this.initialLoad) {
          window.globalAjaxQueue.abort();
        }
        this.helpShownCategory = null;
        if (this.showSubcategoryHelpOnLoad) {
          this.showHelp(
            this.showSubcategoryHelpOnLoad.category,
            this.showSubcategoryHelpOnLoad.subcategory
          );
          this.showSubcategoryHelpOnLoad = null;
        }
        window.$("#loadingError,#loadingRateLimitError").hide();
        this.initialLoad = false;
      });
      Matomo.on("updateReportingMenu", () => {
        ReportingMenuStoreInstance.reloadMenuItems().then(() => {
          const category = instance$1.parsed.value.category;
          const subcategory = instance$1.parsed.value.subcategory;
          if (category && subcategory) {
            const found = ReportingMenuStoreInstance.findSubcategory(category, subcategory);
            if (found.category) {
              ReportingMenuStoreInstance.enterSubcategory(
                found.category,
                found.subcategory,
                found.subsubcategory
              );
            }
          }
        });
        WidgetsStoreInstance.reloadAvailableWidgets();
      });
    },
    methods: {
      // Expose the plugin component to `<component :is>` as a plain Component.
      asComponent(component) {
        return component;
      },
      loadFirstPageOfActiveSection() {
        const menu = ReportingMenuStoreInstance.menu.value;
        const categoryToLoad = menu[0];
        if (!categoryToLoad) {
          return;
        }
        const subcategoryToLoad = categoryToLoad.subcategories[0];
        if (!subcategoryToLoad) {
          return;
        }
        ReportingMenuStoreInstance.enterSubcategory(categoryToLoad, subcategoryToLoad);
        this.propagateUrlChange(categoryToLoad, subcategoryToLoad);
      },
      updateTopMenuActiveState() {
        const activeGroup = instance$1.parsed.value.group || "";
        document.querySelectorAll("[data-reporting-group]").forEach((link) => {
          const listItem = link.closest("li");
          if (!listItem) {
            return;
          }
          const group = link.getAttribute("data-reporting-group") || "";
          listItem.classList.toggle("active", group === activeGroup);
        });
      },
      propagateUrlChange(category, subcategory) {
        const queryParams = instance$1.parsed.value;
        if (queryParams.category === category.id && queryParams.subcategory === subcategory.id) {
          this.loadSubcategory(category, subcategory);
        } else {
          instance$1.updateHash(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
            category: category.id,
            subcategory: subcategory.id
          }));
        }
      },
      loadCategory(category) {
        instance.remove(REPORTING_HELP_NOTIFICATION_ID);
        const isActive = ReportingMenuStoreInstance.toggleCategory(category);
        const { subcategories } = category;
        const categoryCanLoad = subcategories && subcategories.length === 1 || category.widget && subcategories && subcategories.length;
        if (isActive && categoryCanLoad) {
          this.helpShownCategory = null;
          const subcategory = category.subcategories[0];
          this.propagateUrlChange(category, subcategory);
        }
      },
      loadSubcategory(category, subcategory, event) {
        if (event && (event.shiftKey || event.ctrlKey || event.metaKey)) {
          return;
        }
        instance.remove(REPORTING_HELP_NOTIFICATION_ID);
        if (subcategory && subcategory.id === instance$1.parsed.value.subcategory && category.id === instance$1.parsed.value.category) {
          this.helpShownCategory = null;
          setTimeout(() => {
            Matomo.postEvent("loadPage", category.id, subcategory.id);
          });
        }
      },
      makeUrl(category, subcategory) {
        const {
          idSite,
          period,
          date,
          segment,
          comparePeriods,
          compareDates,
          compareSegments,
          group
        } = instance$1.parsed.value;
        const params = {
          idSite,
          period,
          date,
          segment,
          comparePeriods,
          compareDates,
          compareSegments,
          category: category.id,
          subcategory: subcategory.id
        };
        if (group) {
          params.group = group;
        }
        return instance$1.stringify(params);
      },
      htmlEntities(v) {
        return Matomo.helper.htmlEntities(v);
      },
      showHelp(category, subcategory, event) {
        const parsedUrl = instance$1.parsed.value;
        const currentCategory = parsedUrl.category;
        const currentSubcategory = parsedUrl.subcategory;
        if ((currentCategory !== category.id || currentSubcategory !== subcategory.id) && event) {
          this.showSubcategoryHelpOnLoad = { category, subcategory };
          instance$1.updateHash(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
            category: category.id,
            subcategory: subcategory.id
          }));
          return;
        }
        if (this.helpShownCategory && category.id === this.helpShownCategory.category && subcategory.id === this.helpShownCategory.subcategory) {
          instance.remove(REPORTING_HELP_NOTIFICATION_ID);
          this.helpShownCategory = null;
          return;
        }
        const prefixText = translate(
          "CoreHome_ReportingCategoryHelpPrefix",
          category.name,
          subcategory.name
        );
        const prefix = `<strong>${prefixText}</strong><br/>`;
        instance.show({
          context: "info",
          id: REPORTING_HELP_NOTIFICATION_ID,
          type: "help",
          noclear: true,
          class: "help-notification",
          message: prefix + subcategory.help,
          placeat: "#notificationContainer",
          prepend: true
        });
        this.helpShownCategory = {
          category: category.id,
          subcategory: subcategory.id
        };
      }
    }
  });
  const _hoisted_1$h = { class: "reportingMenu" };
  const _hoisted_2$c = ["aria-label"];
  const _hoisted_3$c = ["data-category-id"];
  const _hoisted_4$9 = ["onClick"];
  const _hoisted_5$8 = { class: "hidden" };
  const _hoisted_6$7 = {
    key: 2,
    role: "menu"
  };
  const _hoisted_7$5 = ["href", "onClick", "title"];
  const _hoisted_8$4 = ["href", "onClick"];
  const _hoisted_9$3 = ["onClick"];
  const _hoisted_10$3 = {
    id: "mobile-left-menu",
    class: "sidenav sidenav--reporting-menu-mobile hide-on-large-only"
  };
  const _hoisted_11$3 = ["data-category-id"];
  const _hoisted_12$3 = {
    key: 1,
    class: "collapsible collapsible-accordion"
  };
  const _hoisted_13$3 = { class: "collapsible-header" };
  const _hoisted_14$2 = { class: "collapsible-body" };
  const _hoisted_15$2 = ["onClick", "href"];
  const _hoisted_16$2 = ["onClick", "href"];
  function _sfc_render$j(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MenuItemsDropdown = vue.resolveComponent("MenuItemsDropdown");
    const _directive_side_nav = vue.resolveDirective("side-nav");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$h, [
      vue.createElementVNode("ul", {
        class: "navbar hide-on-med-and-down collapsible",
        role: "menu",
        "aria-label": _ctx.translate("CoreHome_MainNavigation")
      }, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.menu, (category) => {
          return vue.openBlock(), vue.createElementBlock("li", {
            class: vue.normalizeClass(["menuTab", { "active": category.id === _ctx.activeCategory }]),
            role: "menuitem",
            key: category.id,
            "data-category-id": category.id
          }, [
            category.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.asComponent(category.component)), {
              key: 0,
              onAction: ($event) => _ctx.loadCategory(category)
            }, null, 40, ["onAction"])) : vue.createCommentVNode("", true),
            !category.component ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 1,
              class: "item",
              tabindex: "5",
              href: "",
              onClick: vue.withModifiers(($event) => _ctx.loadCategory(category), ["prevent"])
            }, [
              vue.createElementVNode("span", {
                class: vue.normalizeClass(`menu-icon ${category.icon ? category.icon : category.subcategories && category.id === _ctx.activeCategory ? "icon-chevron-down" : "icon-chevron-right"}`)
              }, null, 2),
              vue.createTextVNode(vue.toDisplayString(category.name) + " ", 1),
              vue.createElementVNode("span", _hoisted_5$8, vue.toDisplayString(_ctx.translate("CoreHome_Menu")), 1)
            ], 8, _hoisted_4$9)) : vue.createCommentVNode("", true),
            !category.component ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_6$7, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(category.subcategories, (subcategory) => {
                return vue.openBlock(), vue.createElementBlock("li", {
                  role: "menuitem",
                  class: vue.normalizeClass({
                    "active": (subcategory.id === _ctx.displayedSubcategory || subcategory.isGroup && _ctx.activeSubsubcategory === _ctx.displayedSubcategory) && category.id === _ctx.displayedCategory
                  }),
                  key: subcategory.id
                }, [
                  subcategory.isGroup ? (vue.openBlock(), vue.createBlock(_component_MenuItemsDropdown, {
                    key: 0,
                    "show-search": true,
                    "menu-title": _ctx.htmlEntities(subcategory.name)
                  }, {
                    default: vue.withCtx(() => [
                      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(subcategory.subcategories, (subcat) => {
                        return vue.openBlock(), vue.createElementBlock("a", {
                          class: vue.normalizeClass(["item", {
                            active: subcat.id === _ctx.activeSubsubcategory && subcategory.id === _ctx.displayedSubcategory && category.id === _ctx.displayedCategory
                          }]),
                          tabindex: "5",
                          href: `#?${_ctx.makeUrl(category, subcat)}`,
                          onClick: ($event) => _ctx.loadSubcategory(category, subcat, $event),
                          title: subcat.tooltip,
                          key: subcat.id
                        }, vue.toDisplayString(subcat.name), 11, _hoisted_7$5);
                      }), 128))
                    ]),
                    _: 2
                  }, 1032, ["menu-title"])) : vue.createCommentVNode("", true),
                  !subcategory.isGroup ? (vue.openBlock(), vue.createElementBlock("a", {
                    key: 1,
                    href: `#?${_ctx.makeUrl(category, subcategory)}`,
                    class: "item",
                    onClick: ($event) => _ctx.loadSubcategory(category, subcategory, $event),
                    tabindex: "5"
                  }, vue.toDisplayString(subcategory.name), 9, _hoisted_8$4)) : vue.createCommentVNode("", true),
                  subcategory.help ? (vue.openBlock(), vue.createElementBlock("a", {
                    key: 2,
                    class: vue.normalizeClass(["item-help-icon", { active: _ctx.helpShownCategory && _ctx.helpShownCategory.subcategory === subcategory.id && _ctx.helpShownCategory.category === category.id && subcategory.help }]),
                    tabindex: "5",
                    href: "javascript:",
                    onClick: ($event) => _ctx.showHelp(category, subcategory, $event)
                  }, [..._cache[0] || (_cache[0] = [
                    vue.createElementVNode("span", { class: "icon-help" }, null, -1)
                  ])], 10, _hoisted_9$3)) : vue.createCommentVNode("", true)
                ], 2);
              }), 128))
            ])) : vue.createCommentVNode("", true)
          ], 10, _hoisted_3$c);
        }), 128))
      ], 8, _hoisted_2$c),
      vue.createElementVNode("ul", _hoisted_10$3, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.menu, (category) => {
          return vue.openBlock(), vue.createElementBlock("li", {
            class: "no-padding",
            key: category.id,
            "data-category-id": category.id
          }, [
            category.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.asComponent(category.component)), {
              key: 0,
              onAction: ($event) => _ctx.loadCategory(category)
            }, null, 40, ["onAction"])) : vue.createCommentVNode("", true),
            !category.component ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("ul", _hoisted_12$3, [
              vue.createElementVNode("li", null, [
                vue.createElementVNode("a", _hoisted_13$3, [
                  vue.createElementVNode("i", {
                    class: vue.normalizeClass(category.icon ? category.icon : "icon-chevron-down")
                  }, null, 2),
                  vue.createTextVNode(vue.toDisplayString(category.name), 1)
                ]),
                vue.createElementVNode("div", _hoisted_14$2, [
                  vue.createElementVNode("ul", null, [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(category.subcategories, (subcategory) => {
                      return vue.openBlock(), vue.createElementBlock("li", {
                        key: subcategory.id
                      }, [
                        subcategory.isGroup ? (vue.openBlock(true), vue.createElementBlock(vue.Fragment, { key: 0 }, vue.renderList(subcategory.subcategories, (subcat) => {
                          return vue.openBlock(), vue.createElementBlock("a", {
                            onClick: ($event) => _ctx.loadSubcategory(category, subcat),
                            href: `#?${_ctx.makeUrl(category, subcat)}`,
                            key: subcat.id
                          }, vue.toDisplayString(subcat.name), 9, _hoisted_15$2);
                        }), 128)) : vue.createCommentVNode("", true),
                        !subcategory.isGroup ? (vue.openBlock(), vue.createElementBlock("a", {
                          key: 1,
                          onClick: ($event) => _ctx.loadSubcategory(category, subcategory),
                          href: `#?${_ctx.makeUrl(category, subcategory)}`
                        }, vue.toDisplayString(subcategory.name), 9, _hoisted_16$2)) : vue.createCommentVNode("", true)
                      ]);
                    }), 128))
                  ])
                ])
              ])
            ])), [
              [_directive_side_nav, { activator: _ctx.sideNavActivator }]
            ]) : vue.createCommentVNode("", true)
          ], 8, _hoisted_11$3);
        }), 128))
      ])
    ]);
  }
  const ReportingMenu = /* @__PURE__ */ _export_sfc(_sfc_main$j, [["render", _sfc_render$j]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class ReportMetadataStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        reports: []
      }));
      __publicField(this, "state", vue.readonly(this.privateState));
      __publicField(this, "reports", vue.computed(() => this.state.reports));
      __publicField(this, "reportsPromise");
    }
    // TODO: it used to return an empty array when nothing was found, will that be an issue?
    findReport(reportModule, reportAction) {
      return this.reports.value.find((r) => r.module === reportModule && r.action === reportAction);
    }
    fetchReportMetadata() {
      if (!this.reportsPromise) {
        this.reportsPromise = AjaxHelper.fetch({
          method: "API.getReportMetadata",
          filter_limit: "-1",
          idSite: Matomo.idSite || instance$1.parsed.value.idSite
        }).then((response) => {
          this.privateState.reports = response;
          return response;
        });
      }
      return this.reportsPromise.then(() => this.reports.value);
    }
  }
  const ReportMetadataStoreInstance = new ReportMetadataStore();
  const _sfc_main$i = vue.defineComponent({
    props: {
      canMinimise: Boolean,
      canMaximise: Boolean,
      canRefresh: Boolean,
      canClose: Boolean
    },
    directives: {
      ExpandOnClick
    },
    emits: ["minimise", "maximise", "refresh", "close"],
    computed: {
      visibleControls() {
        const controls = [
          {
            id: "minimise",
            icon: "icon-minimise",
            label: translate("Dashboard_Minimise"),
            visible: this.canMinimise
          },
          {
            id: "maximise",
            icon: "icon-fullscreen",
            label: translate("Dashboard_Maximise"),
            visible: this.canMaximise
          },
          {
            id: "refresh",
            icon: "icon-reload",
            label: translate("General_Refresh"),
            visible: this.canRefresh
          },
          {
            id: "close",
            icon: "icon-close",
            label: translate("General_Close"),
            visible: this.canClose
          }
        ];
        return controls.filter((control) => control.visible);
      }
    },
    methods: {
      translate,
      onControl(intent) {
        this.$emit(intent);
        this.$el.dispatchEvent(new CustomEvent(`widgetcontrol:${intent}`, { bubbles: true }));
        this.$el.classList.remove("expanded");
      }
    }
  });
  const _hoisted_1$g = { class: "widgetControls" };
  const _hoisted_2$b = ["title", "aria-label"];
  const _hoisted_3$b = { class: "widgetControls__menu" };
  const _hoisted_4$8 = { class: "mtm-dropdownPanel" };
  const _hoisted_5$7 = { class: "mtm-dropdownPanel__menu" };
  const _hoisted_6$6 = ["onClick"];
  const _hoisted_7$4 = { class: "mtm-dropdownPanel__menuLabel" };
  function _sfc_render$i(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_expand_on_click = vue.resolveDirective("expand-on-click");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$g, [
      vue.createElementVNode("button", {
        ref: "trigger",
        type: "button",
        class: "widgetControls__trigger",
        title: _ctx.translate("CoreHome_WidgetControls"),
        "aria-label": _ctx.translate("CoreHome_WidgetControls")
      }, [..._cache[0] || (_cache[0] = [
        vue.createElementVNode("svg", {
          class: "widgetControls__icon",
          width: "20",
          height: "20",
          viewBox: "0 0 20 20",
          fill: "none",
          xmlns: "http://www.w3.org/2000/svg",
          "aria-hidden": "true",
          focusable: "false"
        }, [
          vue.createElementVNode("path", {
            d: "M9.99935 10.834C10.4596 10.834 10.8327 10.4609 10.8327 10.0007C10.8327 9.54041\n            10.4596 9.16732 9.99935 9.16732C9.53911 9.16732 9.16602 9.54041 9.16602\n            10.0007C9.16602 10.4609 9.53911 10.834 9.99935 10.834Z",
            fill: "currentColor",
            stroke: "currentColor",
            "stroke-width": "2",
            "stroke-linecap": "round",
            "stroke-linejoin": "round"
          }),
          vue.createElementVNode("path", {
            d: "M9.99935 5.00065C10.4596 5.00065 10.8327 4.62755 10.8327 4.16732C10.8327\n            3.70708 10.4596 3.33398 9.99935 3.33398C9.53911 3.33398 9.16602 3.70708 9.16602\n            4.16732C9.16602 4.62755 9.53911 5.00065 9.99935 5.00065Z",
            fill: "currentColor",
            stroke: "currentColor",
            "stroke-width": "2",
            "stroke-linecap": "round",
            "stroke-linejoin": "round"
          }),
          vue.createElementVNode("path", {
            d: "M9.99935 16.6673C10.4596 16.6673 10.8327 16.2942 10.8327 15.834C10.8327\n            15.3737 10.4596 15.0007 9.99935 15.0007C9.53911 15.0007 9.16602 15.3737 9.16602\n            15.834C9.16602 16.2942 9.53911 16.6673 9.99935 16.6673Z",
            fill: "currentColor",
            stroke: "currentColor",
            "stroke-width": "2",
            "stroke-linecap": "round",
            "stroke-linejoin": "round"
          })
        ], -1)
      ])], 8, _hoisted_2$b),
      vue.createElementVNode("div", _hoisted_3$b, [
        vue.createElementVNode("div", _hoisted_4$8, [
          vue.createElementVNode("ul", _hoisted_5$7, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.visibleControls, (control) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: control.id,
                class: "mtm-dropdownPanel__menuItem"
              }, [
                vue.createElementVNode("button", {
                  type: "button",
                  class: vue.normalizeClass(["mtm-dropdownPanel__menuLink", `widgetControl-${control.id}`]),
                  onClick: ($event) => _ctx.onControl(control.id)
                }, [
                  vue.createElementVNode("span", {
                    class: vue.normalizeClass(["mtm-dropdownPanel__menuIcon", control.icon])
                  }, null, 2),
                  vue.createElementVNode("span", _hoisted_7$4, vue.toDisplayString(control.label), 1)
                ], 10, _hoisted_6$6)
              ]);
            }), 128))
          ])
        ])
      ])
    ])), [
      [_directive_expand_on_click, { expander: "trigger" }]
    ]);
  }
  const WidgetControlsDropdown = /* @__PURE__ */ _export_sfc(_sfc_main$i, [["render", _sfc_render$i]]);
  const CONTROLS_BY_CONTEXT = {
    dashboard: {
      minimise: true,
      maximise: true,
      refresh: true,
      close: true
    },
    maximised: {
      minimise: true,
      maximise: false,
      refresh: true,
      close: false
    },
    widgetized: {
      minimise: false,
      maximise: false,
      refresh: false,
      close: false
    },
    preview: {
      minimise: false,
      maximise: false,
      refresh: false,
      close: false
    }
  };
  const _sfc_main$h = vue.defineComponent({
    props: {
      context: {
        type: String,
        default: "dashboard"
      },
      title: String,
      titleClickable: Boolean,
      titleClickHint: String
    },
    components: {
      WidgetControlsDropdown
    },
    emits: ["minimise", "maximise", "refresh", "close", "titleClick"],
    computed: {
      controls() {
        return CONTROLS_BY_CONTEXT[this.context] || CONTROLS_BY_CONTEXT.widgetized;
      },
      hasControls() {
        const c = this.controls;
        return c.minimise || c.maximise || c.refresh || c.close;
      }
    },
    methods: {
      translate,
      onTitleClick() {
        if (this.titleClickable) {
          this.$emit("titleClick");
        }
      }
    }
  });
  const _hoisted_1$f = { class: "reportHeader" };
  const _hoisted_2$a = { class: "reportHeader__main" };
  const _hoisted_3$a = ["role", "tabindex", "title"];
  const _hoisted_4$7 = { class: "reportHeader__widgetControls" };
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_WidgetControlsDropdown = vue.resolveComponent("WidgetControlsDropdown");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$f, [
      vue.createElementVNode("div", _hoisted_2$a, [
        vue.createElementVNode("h3", {
          class: vue.normalizeClass(["reportHeader__title widgetName", { "reportHeader__title--clickable": _ctx.titleClickable }]),
          role: _ctx.titleClickable ? "button" : null,
          tabindex: _ctx.titleClickable ? 0 : null,
          title: _ctx.titleClickable ? _ctx.titleClickHint : null,
          onClick: _cache[0] || (_cache[0] = (...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args)),
          onKeydown: [
            _cache[1] || (_cache[1] = vue.withKeys(vue.withModifiers((...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args), ["prevent"]), ["enter"])),
            _cache[2] || (_cache[2] = vue.withKeys(vue.withModifiers((...args) => _ctx.onTitleClick && _ctx.onTitleClick(...args), ["prevent"]), ["space"]))
          ]
        }, [
          vue.createElementVNode("span", null, vue.toDisplayString(_ctx.title), 1)
        ], 42, _hoisted_3$a)
      ]),
      vue.createElementVNode("div", _hoisted_4$7, [
        _ctx.hasControls ? (vue.openBlock(), vue.createBlock(_component_WidgetControlsDropdown, {
          key: 0,
          "can-minimise": _ctx.controls.minimise,
          "can-maximise": _ctx.controls.maximise,
          "can-refresh": _ctx.controls.refresh,
          "can-close": _ctx.controls.close,
          onMinimise: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("minimise")),
          onMaximise: _cache[4] || (_cache[4] = ($event) => _ctx.$emit("maximise")),
          onRefresh: _cache[5] || (_cache[5] = ($event) => _ctx.$emit("refresh")),
          onClose: _cache[6] || (_cache[6] = ($event) => _ctx.$emit("close"))
        }, null, 8, ["can-minimise", "can-maximise", "can-refresh", "can-close"])) : vue.createCommentVNode("", true)
      ]),
      _cache[7] || (_cache[7] = vue.createElementVNode("div", { class: "reportHeader__actions" }, null, -1))
    ]);
  }
  const ReportHeader = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$h]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class SearchFiltersPersistenceStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        module: "",
        action: "",
        category: "",
        subcategory: "",
        idSite: "",
        widgetSearchFilters: {}
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      Matomo.on("matomoPageChange", () => {
        if (!this.isCurrentPage()) {
          this.resetSearchFilters();
        }
        this.updateCurrentRoutingFromUrl();
      });
    }
    resetSearchFilters() {
      this.privateState.widgetSearchFilters = {};
    }
    getSearchFilters(widgetId) {
      return this.state.value.widgetSearchFilters[widgetId] || {};
    }
    setSearchFilters(widgetId, filters) {
      if (widgetId) {
        this.privateState.widgetSearchFilters[widgetId] = filters;
      }
    }
    updateCurrentRoutingFromUrl() {
      const url = instance$1.parsed.value;
      this.privateState.module = url.module;
      this.privateState.action = url.action;
      this.privateState.category = url.category;
      this.privateState.subcategory = url.subcategory;
      this.privateState.idSite = url.idSite;
    }
    isCurrentPage() {
      const url = instance$1.parsed.value;
      return this.state.value.module === url.module && this.state.value.action === url.action && this.state.value.category === url.category && this.state.value.subcategory === url.subcategory && this.state.value.idSite === url.idSite;
    }
  }
  const SearchFiltersPersistenceStoreInstance = new SearchFiltersPersistenceStore();
  const _sfc_main$g = vue.defineComponent({
    props: {
      widgetParams: Object,
      widgetName: String,
      loadingMessage: String,
      suppressNotifications: Boolean
    },
    components: {
      ActivityIndicator
    },
    data() {
      return {
        loading: false,
        loadingFailed: false,
        loadingFailedRateLimit: false,
        changeCounter: 0,
        lastWidgetAbortController: null
      };
    },
    watch: {
      widgetParams(parameters) {
        if (parameters) {
          this.loadWidgetUrl(parameters, this.changeCounter += 1);
        }
      }
    },
    computed: {
      finalLoadingMessage() {
        if (this.loadingMessage) {
          return this.loadingMessage;
        }
        if (!this.widgetName) {
          return translate("General_LoadingData");
        }
        return translate("General_LoadingPopover", this.widgetName);
      },
      hasErrorFaqLink() {
        const isGeneralSettingsAdminEnabled = Matomo.config.enable_general_settings_admin;
        const isPluginsAdminEnabled = Matomo.config.enable_plugins_admin;
        return Matomo.hasSuperUserAccess && (isGeneralSettingsAdminEnabled || isPluginsAdminEnabled);
      }
    },
    mounted() {
      if (this.widgetParams) {
        this.loadWidgetUrl(this.widgetParams, this.changeCounter += 1);
      }
    },
    beforeUnmount() {
      this.cleanupLastWidgetContent();
    },
    methods: {
      abortHttpRequestIfNeeded() {
        if (this.lastWidgetAbortController) {
          this.lastWidgetAbortController.abort();
          this.lastWidgetAbortController = null;
        }
      },
      cleanupLastWidgetContent() {
        const widgetContent = this.$refs.widgetContent;
        Matomo.helper.destroyVueComponent(widgetContent);
        if (widgetContent) {
          widgetContent.innerHTML = "";
        }
      },
      getWidgetUrl(parameters) {
        const urlParams = instance$1.parsed.value;
        let fullParameters = __spreadValues({}, parameters || {});
        const paramsToForward = Object.keys(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
          idSite: "",
          period: "",
          date: "",
          segment: "",
          widget: ""
        }));
        paramsToForward.forEach((key) => {
          if (key === "category" || key === "subcategory") {
            return;
          }
          if (!(key in fullParameters)) {
            fullParameters[key] = urlParams[key];
          }
        });
        if (ComparisonsStoreInstance.isComparisonEnabled()) {
          fullParameters = __spreadProps(__spreadValues({}, fullParameters), {
            comparePeriods: urlParams.comparePeriods,
            compareDates: urlParams.compareDates,
            compareSegments: urlParams.compareSegments
          });
        }
        if (!parameters || !("showtitle" in parameters)) {
          fullParameters.showtitle = "1";
        }
        if (Matomo.shouldPropagateTokenAuth && urlParams.token_auth) {
          if (!Matomo.broadcast.isWidgetizeRequestWithoutSession()) {
            fullParameters.force_api_session = "1";
          }
          fullParameters.token_auth = urlParams.token_auth;
        }
        fullParameters.random = Math.floor(Math.random() * 1e4);
        return fullParameters;
      },
      loadWidgetUrl(parameters, thisChangeId) {
        this.loading = true;
        this.abortHttpRequestIfNeeded();
        this.cleanupLastWidgetContent();
        this.lastWidgetAbortController = new AbortController();
        let searchFilters = {};
        if (parameters.uniqueId) {
          searchFilters = SearchFiltersPersistenceStoreInstance.getSearchFilters(parameters.uniqueId);
        }
        AjaxHelper.fetch(this.getWidgetUrl(Object.assign(parameters, searchFilters)), {
          format: "html",
          abortController: this.lastWidgetAbortController
        }).then((response) => {
          if (thisChangeId !== this.changeCounter || typeof response !== "string") {
            return;
          }
          this.lastWidgetAbortController = null;
          this.loading = false;
          this.loadingFailed = false;
          const widgetContent = this.$refs.widgetContent;
          window.$(widgetContent).html(response);
          const $content = window.$(widgetContent).children();
          if (this.widgetName) {
            let $title = $content.find("> .card-content .card-title");
            if (!$title.length) {
              $title = $content.find("> h2");
            }
            if ($title.length) {
              $title.html(Matomo.helper.htmlEntities(this.widgetName));
            }
          }
          Matomo.helper.compileVueEntryComponents($content);
          if (!this.suppressNotifications) {
            instance.parseNotificationDivs();
          }
          setTimeout(() => {
            Matomo.postEvent("widget:loaded", {
              parameters,
              element: $content
            });
          });
        }).catch((response) => {
          if (thisChangeId !== this.changeCounter) {
            return;
          }
          this.lastWidgetAbortController = null;
          this.cleanupLastWidgetContent();
          this.loading = false;
          if (response.xhrStatus === "abort") {
            return;
          }
          if (response.status === 429) {
            this.loadingFailedRateLimit = true;
          }
          this.loadingFailed = true;
        });
      }
    }
  });
  const _hoisted_1$e = { class: "widgetLoader" };
  const _hoisted_2$9 = { key: 0 };
  const _hoisted_3$9 = {
    key: 1,
    class: "notification system notification-error"
  };
  const _hoisted_4$6 = ["href"];
  const _hoisted_5$6 = {
    key: 2,
    class: "notification system notification-error"
  };
  const _hoisted_6$5 = {
    class: "theWidgetContent",
    ref: "widgetContent"
  };
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$e, [
      vue.createVNode(_component_ActivityIndicator, {
        "loading-message": _ctx.finalLoadingMessage,
        loading: _ctx.loading
      }, null, 8, ["loading-message", "loading"]),
      vue.withDirectives(vue.createElementVNode("div", null, [
        _ctx.widgetName ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_2$9, vue.toDisplayString(_ctx.widgetName), 1)) : vue.createCommentVNode("", true),
        !_ctx.loadingFailedRateLimit ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$9, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ErrorRequest", "", "")) + " ", 1),
          _ctx.hasErrorFaqLink ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            rel: "noreferrer noopener",
            target: "_blank",
            href: _ctx.externalRawLink("https://matomo.org/faq/troubleshooting/faq_19489/")
          }, vue.toDisplayString(_ctx.translate("General_ErrorRequestFaqLink")), 9, _hoisted_4$6)) : vue.createCommentVNode("", true)
        ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$6, vue.toDisplayString(_ctx.translate("General_ErrorRateLimit")), 1))
      ], 512), [
        [vue.vShow, _ctx.loadingFailed]
      ]),
      vue.createElementVNode("div", _hoisted_6$5, null, 512)
    ]);
  }
  const WidgetLoader = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$g]]);
  const _sfc_main$f = vue.defineComponent({
    props: {
      widget: {
        type: Object,
        required: true
      },
      widgetized: Boolean
    },
    components: {
      ActivityIndicator,
      Alert
    },
    data() {
      return {
        componentToRender: null,
        loading: false,
        loadingFailed: false
      };
    },
    watch: {
      widget: {
        handler() {
          this.loadComponent();
        },
        immediate: true
      }
    },
    computed: {
      componentProps() {
        var _a2;
        const widget = this.widget;
        return __spreadProps(__spreadValues({}, ((_a2 = widget.clientComponent) == null ? void 0 : _a2.props) || {}), {
          uniqueId: widget.uniqueId,
          widgetName: widget.name,
          widgetized: this.widgetized,
          isWidget: this.widgetized,
          isWide: widget.isWide
        });
      }
    },
    methods: {
      loadComponent() {
        return __async(this, null, function* () {
          const widget = this.widget;
          const { clientComponent } = widget;
          this.loading = true;
          this.loadingFailed = false;
          this.componentToRender = null;
          try {
            if (!clientComponent) {
              throw new Error("Missing client-rendered widget metadata");
            }
            const pluginModule = yield importPluginUmd(
              clientComponent.plugin
            );
            const component = pluginModule == null ? void 0 : pluginModule[clientComponent.name];
            if (!component) {
              throw new Error(
                `Unknown widget component ${clientComponent.plugin}.${clientComponent.name}`
              );
            }
            this.componentToRender = vue.markRaw(component);
          } catch (e) {
            console.error(e);
            this.loadingFailed = true;
          } finally {
            this.loading = false;
          }
        });
      }
    }
  });
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Alert = vue.resolveComponent("Alert");
    return _ctx.loading ? (vue.openBlock(), vue.createBlock(_component_ActivityIndicator, {
      key: 0,
      loading: true,
      "loading-message": _ctx.translate("General_LoadingData")
    }, null, 8, ["loading-message"])) : _ctx.loadingFailed ? (vue.openBlock(), vue.createBlock(_component_Alert, {
      key: 1,
      severity: "danger"
    }, {
      default: vue.withCtx(() => [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ErrorRequest", "", "")), 1)
      ]),
      _: 1
    })) : _ctx.componentToRender ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.componentToRender), vue.normalizeProps(vue.mergeProps({ key: 2 }, _ctx.componentProps)), null, 16)) : vue.createCommentVNode("", true);
  }
  const ClientWidgetRenderer = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  const Widget$1 = useExternalPluginComponent("CoreHome", "Widget");
  const _sfc_main$e = vue.defineComponent({
    props: {
      container: {
        type: Array,
        required: true
      }
    },
    components: {
      Widget: Widget$1
    },
    computed: {
      actualContainer() {
        var _a2, _b, _c;
        const container = this.container;
        if (!((_a2 = container == null ? void 0 : container[0]) == null ? void 0 : _a2.parameters)) {
          return container;
        }
        const [widget] = container;
        const isWidgetized = ((_b = widget.parameters) == null ? void 0 : _b.widget) === "1" || ((_c = widget.parameters) == null ? void 0 : _c.widget) === 1;
        const isGraphEvolution = isWidgetized && widget.viewDataTable === "graphEvolution";
        const firstWidget = isGraphEvolution ? __spreadProps(__spreadValues({}, widget), { parameters: __spreadProps(__spreadValues({}, widget.parameters), { showtitle: "0" }) }) : widget;
        return [
          firstWidget,
          ...container.slice(1)
        ];
      }
    }
  });
  const _hoisted_1$d = { class: "widget-container" };
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Widget = vue.resolveComponent("Widget");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$d, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.actualContainer, (widget, index) => {
        return vue.openBlock(), vue.createElementBlock("div", { key: index }, [
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Widget, {
              widget,
              "prevent-recursion": true
            }, null, 8, ["widget"])
          ])
        ]);
      }), 128))
    ]);
  }
  const WidgetContainer = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
  const _sfc_main$d = vue.defineComponent({
    props: {
      widgets: Array
    },
    components: {
      WidgetLoader
    },
    data() {
      return {
        selectedWidget: null
      };
    },
    created() {
      [this.selectedWidget] = this.widgetsSorted;
    },
    computed: {
      widgetsSorted() {
        return sortOrderables(this.widgets);
      },
      widgetsByCategory() {
        const byCategory = {};
        this.widgetsSorted.forEach((widget) => {
          var _a2;
          const category = (_a2 = widget.subcategory) == null ? void 0 : _a2.name;
          if (!category) {
            return;
          }
          if (!byCategory[category]) {
            byCategory[category] = { name: category, order: widget.order, widgets: [] };
          }
          byCategory[category].widgets.push(widget);
        });
        return sortOrderables(Object.values(byCategory));
      }
    },
    methods: {
      selectWidget(widget) {
        this.selectedWidget = __spreadValues({}, widget);
      }
    }
  });
  const _hoisted_1$c = { class: "reportsByDimensionView" };
  const _hoisted_2$8 = { class: "entityList" };
  const _hoisted_3$8 = { class: "listCircle" };
  const _hoisted_4$5 = ["onClick"];
  const _hoisted_5$5 = { class: "dimension" };
  const _hoisted_6$4 = { class: "reportContainer" };
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2;
    const _component_WidgetLoader = vue.resolveComponent("WidgetLoader");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$c, [
      vue.createElementVNode("div", _hoisted_2$8, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.widgetsByCategory, (category) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "dimensionCategory",
            key: category.name
          }, [
            vue.createTextVNode(vue.toDisplayString(category.name) + " ", 1),
            vue.createElementVNode("ul", _hoisted_3$8, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(category.widgets, (widget) => {
                var _a3;
                return vue.openBlock(), vue.createElementBlock("li", {
                  class: vue.normalizeClass(["reportDimension", { activeDimension: ((_a3 = _ctx.selectedWidget) == null ? void 0 : _a3.uniqueId) === widget.uniqueId }]),
                  key: widget.uniqueId,
                  onClick: ($event) => _ctx.selectWidget(widget)
                }, [
                  vue.createElementVNode("span", _hoisted_5$5, vue.toDisplayString(widget.name), 1)
                ], 10, _hoisted_4$5);
              }), 128))
            ])
          ]);
        }), 128))
      ]),
      vue.createElementVNode("div", _hoisted_6$4, [
        ((_a2 = _ctx.selectedWidget) == null ? void 0 : _a2.parameters) ? (vue.openBlock(), vue.createBlock(_component_WidgetLoader, {
          key: 0,
          "widget-params": _ctx.selectedWidget.parameters,
          class: "dimensionReport"
        }, null, 8, ["widget-params"])) : vue.createCommentVNode("", true)
      ]),
      _cache[0] || (_cache[0] = vue.createElementVNode("div", { class: "clear" }, null, -1))
    ]);
  }
  const WidgetByDimensionContainer = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  function findContainer(widgetsByCategory, containerId) {
    let widget = void 0;
    Object.values(widgetsByCategory || {}).some((widgets) => {
      widget = widgets.find((w) => {
        var _a2;
        return w && w.isContainer && ((_a2 = w.parameters) == null ? void 0 : _a2.containerId) === containerId;
      });
      return widget;
    });
    return widget;
  }
  const _sfc_main$c = vue.defineComponent({
    props: {
      widget: Object,
      widgetized: Boolean,
      containerid: String,
      preventRecursion: Boolean,
      suppressNotifications: Boolean
    },
    components: {
      WidgetLoader,
      WidgetContainer,
      WidgetByDimensionContainer,
      ClientWidgetRenderer
    },
    directives: {
      Tooltips
    },
    data() {
      return {
        showWidget: false
      };
    },
    setup() {
      function tooltipContent() {
        const $this = window.$(this);
        if ($this.hasClass("matomo-form-field")) {
          return "";
        }
        const title = window.$(this).attr("title") || "";
        return window.vueSanitize(title.replace(/\n/g, "<br />"));
      }
      return {
        tooltipContent
      };
    },
    created() {
      const { actualWidget } = this;
      if (actualWidget && actualWidget.middlewareParameters) {
        const params = actualWidget.middlewareParameters;
        AjaxHelper.fetch(params).then((response) => {
          this.showWidget = !!response;
        });
      } else {
        this.showWidget = true;
      }
    },
    computed: {
      allWidgets() {
        return WidgetsStoreInstance.widgets.value;
      },
      actualWidget() {
        const widget = this.widget;
        if (widget) {
          const result = __spreadValues({}, widget);
          if (widget && widget.isReport && !widget.documentation) {
            const report = ReportMetadataStoreInstance.findReport(widget.module, widget.action);
            if (report && report.documentation) {
              result.documentation = report.documentation;
            }
          }
          if (widget.uniqueId) {
            result.parameters = __spreadProps(__spreadValues({}, result.parameters), { uniqueId: widget.uniqueId });
          }
          return result;
        }
        if (this.containerid) {
          const containerWidget = findContainer(this.allWidgets, this.containerid);
          if (containerWidget) {
            const result = __spreadValues({}, containerWidget);
            if (this.widgetized) {
              result.isFirstInPage = true;
              result.parameters = __spreadProps(__spreadValues({}, result.parameters), { widget: "1" });
              const widgets = getWidgetChildren(result);
              if (widgets) {
                result.widgets = widgets.map((w) => __spreadProps(__spreadValues({}, w), {
                  parameters: __spreadProps(__spreadValues({}, w.parameters), {
                    widget: "1",
                    containerId: this.containerid
                  })
                }));
              }
            }
            return result;
          }
        }
        return null;
      }
    }
  });
  const _hoisted_1$b = ["id"];
  const _hoisted_2$7 = { key: 2 };
  const _hoisted_3$7 = { key: 3 };
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_WidgetLoader = vue.resolveComponent("WidgetLoader");
    const _component_ClientWidgetRenderer = vue.resolveComponent("ClientWidgetRenderer");
    const _component_WidgetContainer = vue.resolveComponent("WidgetContainer");
    const _component_WidgetByDimensionContainer = vue.resolveComponent("WidgetByDimensionContainer");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return _ctx.actualWidget && _ctx.showWidget ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      key: 0,
      class: vue.normalizeClass(["matomo-widget", { "isFirstWidgetInPage": _ctx.actualWidget.isFirstInPage }]),
      id: _ctx.actualWidget.uniqueId
    }, [
      !_ctx.actualWidget.isContainer && _ctx.actualWidget.parameters && !_ctx.actualWidget.clientComponent ? (vue.openBlock(), vue.createBlock(_component_WidgetLoader, {
        key: 0,
        "widget-params": _ctx.actualWidget.parameters,
        "widget-name": _ctx.actualWidget.name,
        "suppress-notifications": _ctx.suppressNotifications
      }, null, 8, ["widget-params", "widget-name", "suppress-notifications"])) : vue.createCommentVNode("", true),
      !_ctx.actualWidget.isContainer && _ctx.actualWidget.clientComponent ? (vue.openBlock(), vue.createBlock(_component_ClientWidgetRenderer, {
        key: 1,
        widget: _ctx.actualWidget,
        widgetized: _ctx.widgetized
      }, null, 8, ["widget", "widgetized"])) : vue.createCommentVNode("", true),
      _ctx.actualWidget.isContainer && _ctx.actualWidget.layout !== "ByDimension" && !_ctx.preventRecursion ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$7, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_WidgetContainer, {
            container: _ctx.actualWidget.widgets || []
          }, null, 8, ["container"])
        ])
      ])) : vue.createCommentVNode("", true),
      _ctx.actualWidget.isContainer && _ctx.actualWidget.layout === "ByDimension" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$7, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_WidgetByDimensionContainer, {
            widgets: _ctx.actualWidget.widgets
          }, null, 8, ["widgets"])
        ])
      ])) : vue.createCommentVNode("", true)
    ], 10, _hoisted_1$b)), [
      [_directive_tooltips, { content: _ctx.tooltipContent }]
    ]) : vue.createCommentVNode("", true);
  }
  const Widget = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function shouldBeRenderedWithFullWidth(widget) {
    if (widget.isContainer && widget.layout && widget.layout === "ByDimension" || widget.viewDataTable === "bydimension") {
      return true;
    }
    if (widget.isWide) {
      return true;
    }
    return widget.viewDataTable && (widget.viewDataTable === "tableAllColumns" || widget.viewDataTable === "sparklines" || widget.viewDataTable === "graphEvolution");
  }
  function markWidgetsInFirstRowOfPage(widgets) {
    if (widgets && widgets[0]) {
      const newWidgets = [...widgets];
      const groupedWidgets = widgets[0];
      if (groupedWidgets.group) {
        newWidgets[0] = __spreadProps(__spreadValues({}, newWidgets[0]), {
          left: markWidgetsInFirstRowOfPage(groupedWidgets.left || []),
          right: markWidgetsInFirstRowOfPage(groupedWidgets.right || [])
        });
      } else {
        newWidgets[0] = __spreadProps(__spreadValues({}, newWidgets[0]), { isFirstInPage: true });
      }
      return newWidgets;
    }
    return widgets;
  }
  class ReportingPageStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({}));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "page", vue.computed(() => this.state.value.page));
      __publicField(this, "widgets", vue.computed(() => {
        const page = this.page.value;
        if (!page) {
          return [];
        }
        let widgets = [];
        const reportsToIgnore = {};
        const isIgnoredReport = (widget) => widget.isReport && reportsToIgnore[`${widget.module}.${widget.action}`];
        const getRelatedReports = (widget) => {
          if (!widget.isReport) {
            return [];
          }
          const report = ReportMetadataStoreInstance.findReport(widget.module, widget.action);
          if (!report || !report.relatedReports) {
            return [];
          }
          return report.relatedReports;
        };
        const pageWidgets = page.widgets || [];
        pageWidgets.forEach((widget) => {
          if (isIgnoredReport(widget)) {
            return;
          }
          getRelatedReports(widget).forEach((report) => {
            reportsToIgnore[`${report.module}.${report.action}`] = true;
          });
          widgets.push(widget);
        });
        widgets = sortOrderables(widgets);
        if (widgets.length === 1) {
          return markWidgetsInFirstRowOfPage(widgets);
        }
        const groupedWidgets = [];
        for (let i = 0; i < widgets.length; i += 1) {
          const widget = widgets[i];
          if (shouldBeRenderedWithFullWidth(widget) || widgets[i + 1] && shouldBeRenderedWithFullWidth(widgets[i + 1])) {
            groupedWidgets.push(__spreadProps(__spreadValues({}, widget), {
              widgets: sortOrderables(getWidgetChildren(widget))
            }));
          } else {
            let counter = 0;
            const left = [widget];
            const right = [];
            while (widgets[i + 1] && !shouldBeRenderedWithFullWidth(widgets[i + 1])) {
              i += 1;
              counter += 1;
              if (counter % 2 === 0) {
                left.push(widgets[i]);
              } else {
                right.push(widgets[i]);
              }
            }
            groupedWidgets.push({ group: true, left, right });
          }
        }
        const sortedWidgets = markWidgetsInFirstRowOfPage(groupedWidgets);
        return sortedWidgets;
      }));
    }
    fetchPage(category, subcategory) {
      this.resetPage();
      return Promise.all([
        ReportingPagesStoreInstance.getAllPages(),
        ReportMetadataStoreInstance.fetchReportMetadata()
      ]).then(() => {
        this.privateState.page = ReportingPagesStoreInstance.findPage(category, subcategory);
        return this.page.value;
      });
    }
    resetPage() {
      this.privateState.page = void 0;
    }
  }
  const ReportingPageStoreInstance = new ReportingPageStore();
  const SiteWithoutData = useExternalPluginComponent("SitesManager", "SiteWithoutData");
  const SITE_WITHOUT_DATA_BODY_ID = "site-without-data";
  function showOnlyRawDataNotification() {
    const params = "category=General_Visitors&subcategory=Live_VisitorLog";
    const url = window.broadcast.buildReportingUrl(params);
    let message = translate("CoreHome_PeriodHasOnlyRawData", `<a href="${url}">`, "</a>");
    if (!Matomo.visitorLogEnabled) {
      message = translate("CoreHome_PeriodHasOnlyRawDataNoVisitsLog");
    }
    instance.show({
      id: "onlyRawData",
      animate: false,
      context: "info",
      message,
      type: "transient"
    });
  }
  function hideOnlyRawDataNoticifation() {
    instance.remove("onlyRawData");
  }
  const _sfc_main$b = vue.defineComponent({
    components: {
      ActivityIndicator,
      Widget,
      SiteWithoutData
    },
    props: {
      // groups the empty-site gate is skipped for (e.g. AI Insights), resolved server-side
      groupsWithoutTrackingRequirement: { type: Array, default: () => [] }
    },
    data() {
      return {
        loading: false,
        hasRawData: false,
        hasNoVisits: false,
        dateLastChecked: null,
        hasNoPage: false,
        siteHasNoData: false,
        noDataDismissed: false
      };
    },
    created() {
      ReportingPageStoreInstance.resetPage();
      this.loading = true;
      this.renderInitialPage();
      this.fetchSiteEmptyState();
      vue.watch(() => this.showEmptySiteScreen, (active) => {
        this.updateSiteWithoutDataBodyId(active);
      });
      vue.watch(() => instance$1.parsed.value, (newValue, oldValue) => {
        if (newValue.category === oldValue.category && newValue.subcategory === oldValue.subcategory && newValue.period === oldValue.period && newValue.date === oldValue.date && newValue.segment === oldValue.segment && JSON.stringify(newValue.compareDates) === JSON.stringify(oldValue.compareDates) && JSON.stringify(newValue.comparePeriods) === JSON.stringify(oldValue.comparePeriods) && JSON.stringify(newValue.compareSegments) === JSON.stringify(oldValue.compareSegments) && JSON.stringify(newValue.columns || "") === JSON.stringify(oldValue.columns || "")) {
          return;
        }
        if (newValue.date !== oldValue.date || newValue.period !== oldValue.period) {
          hideOnlyRawDataNoticifation();
          this.dateLastChecked = null;
          this.hasRawData = false;
          this.hasNoVisits = false;
        }
        this.renderPage(
          newValue.category,
          newValue.subcategory,
          newValue.period,
          newValue.date,
          newValue.segment
        );
      });
      Matomo.on("loadPage", (category, subcategory) => {
        const parsedUrl = instance$1.parsed.value;
        this.renderPage(
          category,
          subcategory,
          parsedUrl.period,
          parsedUrl.date,
          parsedUrl.segment
        );
      });
    },
    unmounted() {
      this.updateSiteWithoutDataBodyId(false);
    },
    computed: {
      widgets() {
        return ReportingPageStoreInstance.widgets.value;
      },
      showEmptySiteScreen() {
        if (!this.siteHasNoData || this.noDataDismissed) {
          return false;
        }
        const activeGroup = instance$1.parsed.value.group || DEFAULT_GROUP;
        return !this.groupsWithoutTrackingRequirement.includes(activeGroup);
      }
    },
    methods: {
      fetchSiteEmptyState() {
        AjaxHelper.fetch(
          { module: "SitesManager", action: "getSiteEmptyState", idSite: Matomo.idSite },
          { createErrorNotification: false }
        ).then((response) => {
          this.siteHasNoData = response === true;
        }).catch(() => {
          this.siteHasNoData = false;
        });
      },
      onNoDataDismissed() {
        this.noDataDismissed = true;
        this.renderInitialPage();
      },
      updateSiteWithoutDataBodyId(active) {
        if (active) {
          document.body.id = SITE_WITHOUT_DATA_BODY_ID;
        } else if (document.body.id === SITE_WITHOUT_DATA_BODY_ID) {
          document.body.id = "";
        }
      },
      renderPage(category, subcategory, period, date, segment) {
        if (this.showEmptySiteScreen) {
          instance.clearTransientNotifications();
          this.loading = false;
          return;
        }
        if (!category || !subcategory) {
          ReportingPageStoreInstance.resetPage();
          this.loading = false;
          return;
        }
        try {
          Periods$1.parse(period, date);
        } catch (e) {
          instance.show({
            id: "invalidDate",
            animate: false,
            context: "error",
            message: translate("CoreHome_DateInvalid"),
            type: "transient"
          });
          ReportingPageStoreInstance.resetPage();
          this.loading = false;
          return;
        }
        instance.remove("invalidDate");
        Matomo.postEvent("matomoPageChange", {});
        instance.clearTransientNotifications();
        if (Periods$1.parse(period, date).containsToday()) {
          this.showOnlyRawDataMessageIfRequired(category, subcategory, period, date, segment);
        }
        const params = { category, subcategory };
        Matomo.postEvent("ReportingPage.loadPage", params);
        if (params.promise) {
          this.loading = true;
          Promise.resolve(params.promise).finally(() => {
            this.loading = false;
          });
          return;
        }
        ReportingPageStoreInstance.fetchPage(category, subcategory).then(() => {
          const hasNoPage = !ReportingPageStoreInstance.page.value;
          if (hasNoPage) {
            const page = ReportingPagesStoreInstance.findPageInCategory(category);
            if (page && page.subcategory) {
              instance$1.updateHash(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
                subcategory: page.subcategory.id
              }));
              return;
            }
          }
          this.hasNoPage = hasNoPage;
          this.loading = false;
        });
      },
      renderInitialPage() {
        const parsed = instance$1.parsed.value;
        this.renderPage(
          parsed.category,
          parsed.subcategory,
          parsed.period,
          parsed.date,
          parsed.segment
        );
      },
      showOnlyRawDataMessageIfRequired(category, subcategory, period, date, segment) {
        if (this.hasRawData && this.hasNoVisits) {
          showOnlyRawDataNotification();
        }
        if (segment) {
          hideOnlyRawDataNoticifation();
          return;
        }
        const subcategoryExceptions = [
          "Live_VisitorLog",
          "General_RealTime",
          "UserCountryMap_RealTimeMap",
          "MediaAnalytics_TypeAudienceLog",
          "MediaAnalytics_TypeRealTime",
          "FormAnalytics_TypeRealTime",
          "Goals_AddNewGoal"
        ];
        const categoryExceptions = [
          "HeatmapSessionRecording_Heatmaps",
          "HeatmapSessionRecording_SessionRecordings",
          "Marketplace_Marketplace"
        ];
        if (subcategoryExceptions.indexOf(subcategory) !== -1 || categoryExceptions.indexOf(category) !== -1 || subcategory.toLowerCase().indexOf("manage") !== -1) {
          hideOnlyRawDataNoticifation();
          return;
        }
        const minuteInMilliseconds = 6e4;
        if (this.dateLastChecked && (/* @__PURE__ */ new Date()).valueOf() - this.dateLastChecked.valueOf() < minuteInMilliseconds) {
          return;
        }
        AjaxHelper.fetch({
          method: "VisitsSummary.getVisits",
          date,
          period,
          segment
        }).then((json) => {
          this.dateLastChecked = /* @__PURE__ */ new Date();
          if (json.value > 0) {
            this.hasNoVisits = false;
            hideOnlyRawDataNoticifation();
            return void 0;
          }
          this.hasNoVisits = true;
          if (this.hasRawData) {
            showOnlyRawDataNotification();
            return void 0;
          }
          return AjaxHelper.fetch({
            method: "Live.getMostRecentVisitsDateTime",
            date,
            period
          }).then((lastVisits) => {
            if (!lastVisits || lastVisits.value === "") {
              this.hasRawData = false;
              hideOnlyRawDataNoticifation();
              return;
            }
            this.hasRawData = true;
            showOnlyRawDataNotification();
          });
        });
      }
    }
  });
  const _hoisted_1$a = { class: "reporting-page" };
  const _hoisted_2$6 = {
    key: 1,
    class: "col s12 l6 leftWidgetColumn"
  };
  const _hoisted_3$6 = {
    key: 2,
    class: "col s12 l6 rightWidgetColumn"
  };
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SiteWithoutData = vue.resolveComponent("SiteWithoutData");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Widget = vue.resolveComponent("Widget");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$a, [
      _ctx.showEmptySiteScreen ? (vue.openBlock(), vue.createBlock(_component_SiteWithoutData, {
        key: 0,
        "embedded-in-reporting": true,
        onDismissed: _ctx.onNoDataDismissed
      }, null, 8, ["onDismissed"])) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
        vue.createVNode(_component_ActivityIndicator, { loading: _ctx.loading }, null, 8, ["loading"]),
        vue.withDirectives(vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("CoreHome_NoSuchPage")), 513), [
          [vue.vShow, _ctx.hasNoPage]
        ]),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.widgets, (widget) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "row",
            key: widget.uniqueId
          }, [
            !widget.group ? (vue.openBlock(), vue.createBlock(_component_Widget, {
              key: 0,
              class: "col s12 fullWidgetColumn",
              widget
            }, null, 8, ["widget"])) : vue.createCommentVNode("", true),
            widget.group ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$6, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(widget.left, (widgetInGroup) => {
                return vue.openBlock(), vue.createBlock(_component_Widget, {
                  widget: widgetInGroup,
                  key: widgetInGroup.uniqueId
                }, null, 8, ["widget"]);
              }), 128))
            ])) : vue.createCommentVNode("", true),
            widget.group ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$6, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(widget.right, (widgetInGroup) => {
                return vue.openBlock(), vue.createBlock(_component_Widget, {
                  widget: widgetInGroup,
                  key: widgetInGroup.uniqueId
                }, null, 8, ["widget"]);
              }), 128))
            ])) : vue.createCommentVNode("", true)
          ]);
        }), 128))
      ], 64))
    ]);
  }
  const ReportingPage = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const FORMATS_WITHOUT_EXPANDED = ["CSV", "TSV", "HTML"];
  function isFormatWithoutExpanded(format2) {
    return FORMATS_WITHOUT_EXPANDED.includes(format2);
  }
  function resolveInitialSubtablePreference(initialOptionFlat, initialOptionExpanded, initialReportFormat) {
    if (initialOptionFlat) {
      if (isFormatWithoutExpanded(initialReportFormat)) {
        return {
          hasUserPreference: false,
          preferredMode: null
        };
      }
      return {
        hasUserPreference: true,
        preferredMode: "flat"
      };
    }
    if (initialOptionExpanded) {
      return {
        hasUserPreference: true,
        preferredMode: "expanded"
      };
    }
    return {
      hasUserPreference: true,
      preferredMode: null
    };
  }
  function resolveEffectiveSubtableOptions(hasSubtables, canExportFlat, reportFormat, subtablePreference) {
    const { hasUserPreference, preferredMode } = subtablePreference;
    if (!hasSubtables && !canExportFlat) {
      return {
        optionFlat: false,
        optionExpanded: false
      };
    }
    if (isFormatWithoutExpanded(reportFormat)) {
      if (!canExportFlat) {
        return {
          optionFlat: false,
          optionExpanded: false
        };
      }
      return {
        optionFlat: !hasUserPreference || preferredMode === "flat",
        optionExpanded: false
      };
    }
    if (!hasSubtables) {
      return canExportFlat ? { optionFlat: preferredMode === "flat", optionExpanded: false } : { optionFlat: false, optionExpanded: false };
    }
    if (!hasUserPreference) {
      return {
        optionFlat: false,
        optionExpanded: true
      };
    }
    if (preferredMode === "flat") {
      return canExportFlat ? { optionFlat: true, optionExpanded: false } : { optionFlat: false, optionExpanded: true };
    }
    if (preferredMode === "expanded") {
      return {
        optionFlat: false,
        optionExpanded: true
      };
    }
    return {
      optionFlat: false,
      optionExpanded: false
    };
  }
  const Field$1 = useExternalPluginComponent("CorePluginsAdmin", "Field");
  const _sfc_main$a = vue.defineComponent({
    components: {
      Field: Field$1
    },
    directives: {
      SelectOnFocus
    },
    props: {
      hasSubtables: Boolean,
      canExportFlat: {
        type: Boolean,
        default: false
      },
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
        default: "default"
      },
      initialReportLimit: {
        type: [String, Number],
        default: 100
      },
      initialReportLimitAll: {
        type: String,
        default: "yes"
      },
      initialOptionFlat: {
        type: Boolean,
        default: false
      },
      initialOptionShowDimensions: {
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
        default: "TSV"
      }
    },
    mounted() {
      const parameters = { content: this.additionalContent, dataTable: this.dataTable };
      Matomo.postEvent("ReportExportPopover.additionalContent", parameters);
      this.additionalContent = parameters.content;
    },
    data() {
      return {
        showUrl: false,
        reportFormat: this.initialReportFormat,
        optionShowDimensions: this.initialOptionShowDimensions,
        // Keep explicit preference separate from default behavior:
        // default means CSV/TSV/HTML flat and other formats expanded.
        subtablePreference: resolveInitialSubtablePreference(
          this.initialOptionFlat,
          this.initialOptionExpanded,
          this.initialReportFormat
        ),
        optionFormatMetrics: this.initialOptionFormatMetrics,
        reportType: this.initialReportType,
        reportLimitAll: this.initialReportLimitAll,
        reportLimit: typeof this.initialReportLimit === "string" ? parseInt(this.initialReportLimit, 10) : this.initialReportLimit,
        additionalContent: ""
      };
    },
    watch: {
      reportType(newVal) {
        if (!this.availableReportFormats[newVal][this.reportFormat]) {
          this.reportFormat = "JSON";
        }
      },
      reportLimit(newVal, oldVal) {
        if (this.maxFilterLimit && this.maxFilterLimit > 0 && newVal > this.maxFilterLimit) {
          this.reportLimit = oldVal;
        }
      }
    },
    computed: {
      hasMultipleDimensions() {
        var _a2, _b;
        if (typeof ((_a2 = this.dataTable) == null ? void 0 : _a2.getReportMetadata) !== "function") {
          return false;
        }
        return Object.keys(((_b = this.dataTable) == null ? void 0 : _b.getReportMetadata().dimensions) || {}).length > 1;
      },
      filterLimitTooltip() {
        const rowLimit = translate("CoreHome_RowLimit");
        const computedMetricMax = this.maxFilterLimit ? translate(
          "General_ComputedMetricMax",
          this.maxFilterLimit.toString()
        ) : "";
        return `${rowLimit} (${computedMetricMax})`;
      },
      canExpand() {
        return !isFormatWithoutExpanded(this.reportFormat);
      },
      effectiveSubtableOptions() {
        return resolveEffectiveSubtableOptions(
          this.hasSubtables,
          this.canExportFlat,
          this.reportFormat,
          this.subtablePreference
        );
      },
      optionFlatModel: {
        get() {
          return this.effectiveSubtableOptions.optionFlat;
        },
        set(newVal) {
          if (!this.canExportFlat) {
            return;
          }
          if (newVal) {
            this.subtablePreference = {
              hasUserPreference: true,
              preferredMode: "flat"
            };
          } else if (!this.optionExpandedModel) {
            this.subtablePreference = {
              hasUserPreference: true,
              preferredMode: null
            };
          }
        }
      },
      optionExpandedModel: {
        get() {
          return this.effectiveSubtableOptions.optionExpanded;
        },
        set(newVal) {
          if (!this.hasSubtables || isFormatWithoutExpanded(this.reportFormat)) {
            return;
          }
          if (newVal) {
            this.subtablePreference = {
              hasUserPreference: true,
              preferredMode: "expanded"
            };
          } else if (!this.optionFlatModel) {
            this.subtablePreference = {
              hasUserPreference: true,
              preferredMode: null
            };
          }
        }
      },
      exportLink() {
        return this.getExportLink(true);
      },
      exportLinkWithoutToken() {
        return this.getExportLink(false);
      }
    },
    methods: {
      getExportLink(withToken = true) {
        const {
          reportFormat,
          apiMethod,
          reportType
        } = this;
        const dataTable = this.dataTable;
        if (!reportFormat) {
          return void 0;
        }
        let requestParams = {};
        const limit = this.reportLimitAll === "yes" ? -1 : this.reportLimit;
        if (this.requestParams && typeof this.requestParams === "string") {
          requestParams = JSON.parse(this.requestParams);
        } else if (this.requestParams && typeof this.requestParams === "object") {
          requestParams = this.requestParams;
        }
        const {
          segment,
          label,
          idGoal,
          idDimension,
          idSite
        } = dataTable.param;
        let { date, period } = dataTable.param;
        if (reportFormat === "RSS") {
          date = "last10";
        }
        if (typeof dataTable.param.dateUsedInGraph !== "undefined") {
          date = dataTable.param.dateUsedInGraph;
        }
        const formatsUseDayNotRange = Matomo.config.datatable_export_range_as_day.toLowerCase();
        if (formatsUseDayNotRange.indexOf(reportFormat.toLowerCase()) !== -1 && dataTable.param.period === "range") {
          period = "day";
        }
        if (dataTable.param.period === "range" && dataTable.param.viewDataTable === "graphEvolution") {
          period = "day";
        }
        const exportUrlParams = {
          module: "API",
          format: reportFormat,
          idSite,
          period,
          date
        };
        if (reportType === "processed") {
          exportUrlParams.method = "API.getProcessedReport";
          [exportUrlParams.apiModule, exportUrlParams.apiAction] = apiMethod.split(".");
        } else {
          exportUrlParams.method = apiMethod;
        }
        if (dataTable.param.compareDates && dataTable.param.compareDates.length) {
          exportUrlParams.compareDates = dataTable.param.compareDates;
          exportUrlParams.compare = "1";
        }
        if (dataTable.param.comparePeriods && dataTable.param.comparePeriods.length) {
          exportUrlParams.comparePeriods = dataTable.param.comparePeriods;
          exportUrlParams.compare = "1";
        }
        if (dataTable.param.compareSegments && dataTable.param.compareSegments.length) {
          exportUrlParams.compareSegments = dataTable.param.compareSegments;
          exportUrlParams.compare = "1";
        }
        if (typeof dataTable.param.filter_pattern !== "undefined") {
          exportUrlParams.filter_pattern = dataTable.param.filter_pattern;
        }
        if (typeof dataTable.param.filter_pattern_recursive !== "undefined") {
          exportUrlParams.filter_pattern_recursive = dataTable.param.filter_pattern_recursive;
        }
        if (window.$.isPlainObject(requestParams)) {
          Object.entries(requestParams).forEach(([index, param]) => {
            let value = param;
            if (value === true) {
              value = 1;
            } else if (value === false) {
              value = 0;
            }
            exportUrlParams[index] = value;
          });
        }
        const {
          optionFlat: effectiveOptionFlat,
          optionExpanded: effectiveOptionExpanded
        } = this.effectiveSubtableOptions;
        if (effectiveOptionFlat) {
          exportUrlParams.flat = 1;
          if (this.optionShowDimensions) {
            exportUrlParams.show_dimensions = 1;
          }
          if (typeof dataTable.param.include_aggregate_rows !== "undefined" && dataTable.param.include_aggregate_rows === "1") {
            exportUrlParams.include_aggregate_rows = 1;
          }
        }
        if (this.hasSubtables && !effectiveOptionFlat && effectiveOptionExpanded) {
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
        if (reportFormat === "CSV" || reportFormat === "TSV" || reportFormat === "RSS") {
          exportUrlParams.translateColumnNames = 1;
          exportUrlParams.language = Matomo.language;
        }
        if (typeof segment !== "undefined") {
          exportUrlParams.segment = decodeURIComponent(segment);
        }
        if (typeof idGoal !== "undefined" && idGoal !== "-1") {
          exportUrlParams.idGoal = idGoal;
        }
        if (typeof idDimension !== "undefined" && idDimension !== "-1") {
          exportUrlParams.idDimension = idDimension;
        }
        if (label) {
          const labelParts = label.split(",");
          if (labelParts.length > 1) {
            exportUrlParams.label = labelParts;
          } else {
            [exportUrlParams.label] = labelParts;
          }
        }
        exportUrlParams.showMetadata = 0;
        exportUrlParams.token_auth = "ENTER_YOUR_TOKEN_AUTH_HERE";
        if (withToken === true) {
          exportUrlParams.token_auth = Matomo.token_auth;
          exportUrlParams.force_api_session = 1;
        }
        exportUrlParams.filter_limit = limit;
        const prefix = window.location.href.split("?")[0];
        return `${prefix}?${instance$1.stringify(exportUrlParams)}`;
      }
    }
  });
  const _hoisted_1$9 = {
    class: "report-export-popover row",
    id: "reportExport"
  };
  const _hoisted_2$5 = { class: "col l6" };
  const _hoisted_3$5 = { name: "format" };
  const _hoisted_4$4 = { name: "option_flat" };
  const _hoisted_5$4 = { name: "option_show_dimensions" };
  const _hoisted_6$3 = { name: "option_expanded" };
  const _hoisted_7$3 = { name: "option_format_metrics" };
  const _hoisted_8$3 = { class: "col l6" };
  const _hoisted_9$2 = { name: "filter_type" };
  const _hoisted_10$2 = { class: "filter_limit" };
  const _hoisted_11$2 = { name: "filter_limit_all" };
  const _hoisted_12$2 = {
    key: 0,
    name: "filter_limit"
  };
  const _hoisted_13$2 = {
    key: 1,
    name: "filter_limit"
  };
  const _hoisted_14$1 = { class: "col l12" };
  const _hoisted_15$1 = ["value"];
  const _hoisted_16$1 = ["innerHTML"];
  const _hoisted_17$1 = { class: "col l12" };
  const _hoisted_18$1 = ["href", "title"];
  const _hoisted_19$1 = ["innerHTML"];
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2, _b;
    const _component_Field = vue.resolveComponent("Field");
    const _directive_select_on_focus = vue.resolveDirective("select-on-focus");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$9, [
      vue.createElementVNode("div", _hoisted_2$5, [
        vue.createElementVNode("div", _hoisted_3$5, [
          vue.createVNode(_component_Field, {
            uicontrol: "radio",
            name: "format",
            title: _ctx.translate("CoreHome_ExportFormat"),
            modelValue: _ctx.reportFormat,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.reportFormat = $event),
            "full-width": true,
            options: _ctx.availableReportFormats[_ctx.reportType]
          }, null, 8, ["title", "modelValue", "options"])
        ]),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_4$4, [
            vue.withDirectives(vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "option_flat",
              title: _ctx.translate("CoreHome_FlattenReport"),
              modelValue: _ctx.optionFlatModel,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.optionFlatModel = $event)
            }, null, 8, ["title", "modelValue"]), [
              [vue.vShow, _ctx.canExportFlat]
            ])
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_5$4, [
            vue.withDirectives(vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "option_show_dimensions",
              title: _ctx.translate("CoreHome_IncludeDimensionsSeparately"),
              modelValue: _ctx.optionShowDimensions,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.optionShowDimensions = $event)
            }, null, 8, ["title", "modelValue"]), [
              [vue.vShow, _ctx.canExportFlat && _ctx.hasMultipleDimensions && _ctx.optionFlatModel]
            ])
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_6$3, [
            vue.withDirectives(vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "option_expanded",
              title: _ctx.translate("CoreHome_ExpandSubtables"),
              modelValue: _ctx.optionExpandedModel,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.optionExpandedModel = $event)
            }, null, 8, ["title", "modelValue"]), [
              [vue.vShow, _ctx.hasSubtables && _ctx.canExpand]
            ])
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_7$3, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "option_format_metrics",
              title: _ctx.translate("CoreHome_FormatMetrics"),
              modelValue: _ctx.optionFormatMetrics,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.optionFormatMetrics = $event)
            }, null, 8, ["title", "modelValue"])
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_8$3, [
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_9$2, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: "filter_type",
              title: _ctx.translate("CoreHome_ReportType"),
              modelValue: _ctx.reportType,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.reportType = $event),
              "full-width": true,
              options: _ctx.availableReportTypes
            }, null, 8, ["title", "modelValue", "options"])
          ])
        ]),
        vue.createElementVNode("div", _hoisted_10$2, [
          vue.withDirectives(vue.createElementVNode("div", _hoisted_11$2, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: "filter_limit_all",
              title: _ctx.translate("CoreHome_RowLimit"),
              modelValue: _ctx.reportLimitAll,
              "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.reportLimitAll = $event),
              "full-width": true,
              options: _ctx.limitAllOptions
            }, null, 8, ["title", "modelValue", "options"])
          ], 512), [
            [vue.vShow, !_ctx.maxFilterLimit || _ctx.maxFilterLimit <= 0]
          ]),
          _ctx.reportLimitAll === "no" && ((_a2 = _ctx.maxFilterLimit) != null ? _a2 : 0) <= 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_12$2, [
            vue.createVNode(_component_Field, {
              uicontrol: "number",
              name: "filter_limit",
              min: 1,
              modelValue: _ctx.reportLimit,
              "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.reportLimit = $event),
              "full-width": true
            }, null, 8, ["modelValue"])
          ])) : vue.createCommentVNode("", true),
          _ctx.reportLimitAll === "no" && ((_b = _ctx.maxFilterLimit) != null ? _b : 0) > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_13$2, [
            vue.createVNode(_component_Field, {
              uicontrol: "number",
              name: "filter_limit",
              min: 1,
              max: _ctx.maxFilterLimit,
              modelValue: _ctx.reportLimit,
              "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.reportLimit = $event),
              value: _ctx.reportLimit,
              "full-width": true,
              title: _ctx.filterLimitTooltip
            }, null, 8, ["max", "modelValue", "value", "title"])
          ])) : vue.createCommentVNode("", true)
        ])
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_14$1, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("textarea", {
          readonly: "",
          class: "exportFullUrl",
          value: _ctx.exportLinkWithoutToken
        }, [..._cache[10] || (_cache[10] = [
          vue.createTextVNode("      ", -1)
        ])], 8, _hoisted_15$1)), [
          [_directive_select_on_focus, {}]
        ]),
        vue.createElementVNode("div", {
          class: "tooltip",
          innerHTML: _ctx.$sanitize(_ctx.translate(
            "CoreHome_ExportTooltipWithLink",
            "<a target=_blank href='?module=UsersManager&action=userSecurity'>",
            "</a>",
            "ENTER_YOUR_TOKEN_AUTH_HERE"
          ))
        }, null, 8, _hoisted_16$1)
      ], 512), [
        [vue.vShow, _ctx.showUrl]
      ]),
      vue.createElementVNode("div", _hoisted_17$1, [
        vue.createElementVNode("a", {
          class: "btn",
          href: _ctx.exportLink,
          target: "_new",
          title: _ctx.translate("CoreHome_ExportTooltip")
        }, vue.toDisplayString(_ctx.translate("General_Export")), 9, _hoisted_18$1),
        vue.createElementVNode("a", {
          href: "javascript:",
          onClick: _cache[9] || (_cache[9] = ($event) => _ctx.showUrl = !_ctx.showUrl),
          class: "toggle-export-url"
        }, [
          vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("CoreHome_ShowExportUrl")), 513), [
            [vue.vShow, !_ctx.showUrl]
          ]),
          vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("CoreHome_HideExportUrl")), 513), [
            [vue.vShow, _ctx.showUrl]
          ])
        ])
      ]),
      _ctx.additionalContent ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        class: "col l12 report-export-popover-footer",
        innerHTML: _ctx.$sanitize(_ctx.additionalContent)
      }, null, 8, _hoisted_19$1)) : vue.createCommentVNode("", true)
    ]);
  }
  const ReportExportPopover = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$6 } = window;
  const ReportExport = {
    mounted(el, binding) {
      el.addEventListener("click", () => {
        var _a2;
        const popoverParamBackup = instance$1.hashParsed.value.popover;
        const dataTable = $$6(el).closest("[data-report]").data("uiControlObject");
        const popover = window.Piwik_Popover.showLoading("Export");
        const formats = binding.value.reportFormats;
        let reportLimit = dataTable.param.filter_limit;
        if (binding.value.maxFilterLimit > 0) {
          reportLimit = Math.min(reportLimit, binding.value.maxFilterLimit);
        }
        const isDataTableFlat = dataTable.param.flat === true || dataTable.param.flat === 1 || dataTable.param.flat === "1";
        const optionShowDimensions = dataTable.param.show_dimensions === true || dataTable.param.show_dimensions === 1 || dataTable.param.show_dimensions === "1";
        const hasSubtables = isDataTableFlat || dataTable.numberOfSubtables > 0;
        const canExportFlat = (_a2 = binding.value.canExportFlat) != null ? _a2 : hasSubtables;
        const defaultFlatOnOpen = canExportFlat;
        const defaultExpandedOnOpen = false;
        const props = {
          initialReportType: "default",
          initialReportFormat: "TSV",
          initialReportLimit: reportLimit > 0 ? reportLimit : 100,
          initialReportLimitAll: reportLimit === -1 ? "yes" : "no",
          initialOptionFlat: defaultFlatOnOpen,
          initialOptionShowDimensions: optionShowDimensions,
          initialOptionExpanded: defaultExpandedOnOpen,
          initialOptionFormatMetrics: false,
          hasSubtables,
          canExportFlat,
          availableReportFormats: {
            default: formats,
            processed: {
              JSON: formats.JSON,
              XML: formats.XML
            }
          },
          availableReportTypes: {
            default: translate("CoreHome_StandardReport"),
            processed: translate("CoreHome_ReportWithMetadata")
          },
          limitAllOptions: {
            yes: translate("General_All"),
            no: translate("CoreHome_CustomLimit")
          },
          maxFilterLimit: binding.value.maxFilterLimit,
          dataTable,
          requestParams: binding.value.requestParams,
          apiMethod: binding.value.apiMethod
        };
        const app = createVueApp({
          template: `
          <popover v-bind="bind"/>`,
          data() {
            return {
              bind: props
            };
          }
        });
        app.component("popover", ReportExportPopover);
        const mountPoint = document.createElement("div");
        app.mount(mountPoint);
        const { reportTitle } = binding.value;
        window.Piwik_Popover.setTitle(
          `${translate("General_Export")} ${Matomo.helper.htmlEntities(reportTitle)}`
        );
        window.Piwik_Popover.setContent(mountPoint);
        window.Piwik_Popover.onClose(() => {
          app.unmount();
          if (popoverParamBackup !== "") {
            setTimeout(() => {
              instance$1.updateHash(__spreadProps(__spreadValues({}, instance$1.hashParsed.value), {
                popover: popoverParamBackup
              }));
              if (binding.value.onClose) {
                binding.value.onClose();
              }
            }, 100);
          }
        });
        setTimeout(() => {
          popover.dialog();
          $$6(".exportFullUrl, .btn", popover).tooltip({
            track: true,
            show: false,
            hide: false
          });
        }, 100);
      });
    }
  };
  const _sfc_main$9 = vue.defineComponent({
    name: "Sparkline",
    props: {
      seriesIndices: Array,
      params: [Object, String],
      width: Number,
      height: Number
    },
    data() {
      return {
        isWidget: false,
        themeMode: Matomo.getThemeMode()
      };
    },
    mounted() {
      this.isWidget = !!this.$el.closest("[widgetId]");
      window.addEventListener("themeModeChange", this.onThemeModeChange);
    },
    beforeUnmount() {
      window.removeEventListener("themeModeChange", this.onThemeModeChange);
    },
    computed: {
      sparklineUrl() {
        const { seriesIndices, params, themeMode } = this;
        const sparklineColors = Matomo.getSparklineColors();
        if (seriesIndices) {
          sparklineColors.lineColor = sparklineColors.lineColor.filter(
            (c, index) => seriesIndices.indexOf(index) !== -1
          );
        }
        const colors = JSON.stringify(sparklineColors);
        const redesignEnabled = document.body.classList.contains("sparklines-redesign-enabled");
        const sizeParams = redesignEnabled ? __spreadValues(__spreadValues({}, typeof this.width === "number" ? { width: this.width * 2 } : {}), typeof this.height === "number" ? { height: this.height * 2 } : {}) : {};
        const defaultParams = __spreadProps(__spreadValues({
          forceView: "1",
          viewDataTable: "sparkline",
          widget: this.isWidget ? "1" : "0",
          showtitle: "1",
          colors,
          random: Date.now(),
          date: this.defaultDate
        }, sizeParams), {
          // mixinDefaultGetParams() will use the raw, encoded value from the URL (legacy behavior),
          // which means MatomoUrl.stringify() will end up double encoding it if we don't set it
          // ourselves here.
          segment: instance$1.parsed.value.segment
        });
        const givenParams = typeof params === "object" ? params : instance$1.parse(params.substring(params.indexOf("?") + 1));
        const helper = new AjaxHelper();
        const urlParams = helper.mixinDefaultGetParams(__spreadValues(__spreadValues({}, defaultParams), givenParams));
        const token_auth = instance$1.parsed.value.token_auth;
        if (token_auth && token_auth.length && Matomo.shouldPropagateTokenAuth) {
          urlParams.token_auth = token_auth;
        }
        urlParams.themeMode = themeMode;
        return `?${instance$1.stringify(urlParams)}`;
      },
      defaultDate() {
        if (Matomo.period === "range") {
          return `${Matomo.startDateString},${Matomo.endDateString}`;
        }
        const dateRange = RangePeriod.getLastNRange(
          Matomo.period,
          30,
          Matomo.currentDateString
        ).getDateRange();
        const piwikMinDate = new Date(Matomo.minDateYear, Matomo.minDateMonth - 1, Matomo.minDateDay);
        if (dateRange[0] < piwikMinDate) {
          dateRange[0] = piwikMinDate;
        }
        const startDateStr = format(dateRange[0]);
        const endDateStr = format(dateRange[1]);
        return `${startDateStr},${endDateStr}`;
      }
    },
    methods: {
      onThemeModeChange() {
        this.themeMode = Matomo.getThemeMode();
      }
    }
  });
  const _hoisted_1$8 = ["src", "width", "height"];
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("img", {
      class: "sparklineImg",
      loading: "lazy",
      alt: "",
      src: _ctx.sparklineUrl,
      width: _ctx.width,
      height: _ctx.height
    }, null, 8, _hoisted_1$8);
  }
  const Sparkline = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const _sfc_main$8 = vue.defineComponent({
    components: { MatomoLoader },
    props: {
      progress: {
        type: Number,
        required: true
      },
      label: String
    },
    computed: {
      actualProgress() {
        if (this.progress > 100) {
          return 100;
        }
        if (this.progress < 0) {
          return 0;
        }
        return this.progress;
      }
    }
  });
  const _hoisted_1$7 = { class: "progressbar" };
  const _hoisted_2$4 = { class: "progress" };
  const _hoisted_3$4 = ["innerHTML"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      vue.createElementVNode("div", _hoisted_2$4, [
        vue.createElementVNode("div", {
          class: "determinate",
          style: vue.normalizeStyle([{ "width": "0" }, { width: `${_ctx.actualProgress}%` }])
        }, null, 4)
      ]),
      vue.withDirectives(vue.createElementVNode("span", null, [
        vue.createVNode(_component_MatomoLoader),
        vue.createElementVNode("span", {
          class: "label",
          innerHTML: _ctx.$sanitize(_ctx.label)
        }, null, 8, _hoisted_3$4)
      ], 512), [
        [vue.vShow, !!_ctx.label]
      ])
    ]);
  }
  const Progressbar = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const ContentIntro = {
    mounted(el) {
      el.classList.add("piwik-content-intro");
    },
    updated(el) {
      vue.nextTick(() => {
        el.classList.add("piwik-content-intro");
      });
    }
  };
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const MOBILE_BREAKPOINT = "(max-width: 767px)";
  const registrations = /* @__PURE__ */ new WeakMap();
  function ensureOverflowWrapper(el) {
    const parent = el.parentElement;
    if (!parent || parent.classList.contains("contentTableWrapper")) {
      return;
    }
    const wrapper = document.createElement("div");
    wrapper.className = "contentTableWrapper";
    parent.insertBefore(wrapper, el);
    wrapper.appendChild(el);
  }
  function removeOverflowWrapper(el) {
    const parent = el.parentElement;
    if (!parent || !parent.classList.contains("contentTableWrapper")) {
      return;
    }
    const wrapperParent = parent.parentElement;
    if (!wrapperParent) {
      return;
    }
    wrapperParent.insertBefore(el, parent);
    parent.remove();
  }
  function shouldWrapTable(mediaQuery) {
    return (mediaQuery || window.matchMedia(MOBILE_BREAKPOINT)).matches;
  }
  function addMediaQueryListener(mediaQuery, listener) {
    mediaQuery.addEventListener("change", listener);
  }
  function removeMediaQueryListener(mediaQuery, listener) {
    mediaQuery.removeEventListener("change", listener);
  }
  function applyResponsiveContentTable(el, mediaQuery) {
    el.classList.add("card", "card-table", "entityTable");
    if (shouldWrapTable(mediaQuery)) {
      ensureOverflowWrapper(el);
    } else {
      removeOverflowWrapper(el);
    }
  }
  function unregisterResponsiveContentTable(el) {
    const registration = registrations.get(el);
    if (registration) {
      removeMediaQueryListener(registration.mediaQuery, registration.listener);
      registrations.delete(el);
    }
    removeOverflowWrapper(el);
  }
  function registerResponsiveContentTable(el) {
    const existingRegistration = registrations.get(el);
    if (existingRegistration) {
      applyResponsiveContentTable(el, existingRegistration.mediaQuery);
      return;
    }
    const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
    const listener = () => {
      if (!el.isConnected) {
        unregisterResponsiveContentTable(el);
        return;
      }
      applyResponsiveContentTable(el, mediaQuery);
    };
    addMediaQueryListener(mediaQuery, listener);
    registrations.set(el, { mediaQuery, listener });
    applyResponsiveContentTable(el, mediaQuery);
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const ContentTable = {
    mounted(el, binding) {
      var _a2;
      if ((_a2 = binding == null ? void 0 : binding.value) == null ? void 0 : _a2.off) {
        return;
      }
      registerResponsiveContentTable(el);
    },
    updated(el, binding) {
      var _a2;
      if ((_a2 = binding == null ? void 0 : binding.value) == null ? void 0 : _a2.off) {
        unregisterResponsiveContentTable(el);
        return;
      }
      vue.nextTick(() => {
        registerResponsiveContentTable(el);
      });
    },
    beforeUnmount(el) {
      unregisterResponsiveContentTable(el);
    }
  };
  const { $: $$5 } = window;
  const _sfc_main$7 = vue.defineComponent({
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
    data() {
      return {
        isSubmitting: false,
        successfulPostResponse: null,
        errorPostResponse: null
      };
    },
    emits: ["update:modelValue"],
    mounted() {
      $$5(this.$refs.root).on("click", "input[type=submit]", () => {
        this.submitForm();
      });
    },
    methods: {
      submitForm() {
        this.successfulPostResponse = null;
        this.errorPostResponse = null;
        let postParams = this.formData;
        if (this.sendJsonPayload) {
          postParams = { data: JSON.stringify(this.formData) };
        }
        this.isSubmitting = true;
        AjaxHelper.post(
          {
            module: "API",
            method: this.submitApiMethod
          },
          postParams,
          {
            createErrorNotification: !this.noErrorNotification
          }
        ).then((response) => {
          this.successfulPostResponse = response;
          if (!this.noSuccessNotification) {
            const notificationInstanceId = instance.show({
              message: translate("General_YourChangesHaveBeenSaved"),
              context: "success",
              type: "toast",
              id: "ajaxHelper"
            });
            instance.scrollToNotification(notificationInstanceId);
          }
        }).catch((error) => {
          this.errorPostResponse = error.message;
        }).finally(() => {
          this.isSubmitting = false;
        });
      }
    }
  });
  const _hoisted_1$6 = { ref: "root" };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, [
      vue.renderSlot(_ctx.$slots, "default", {
        formData: _ctx.formData,
        submitApiMethod: _ctx.submitApiMethod,
        sendJsonPayload: _ctx.sendJsonPayload,
        noErrorNotification: _ctx.noErrorNotification,
        noSuccessNotification: _ctx.noSuccessNotification,
        submitForm: _ctx.submitForm,
        isSubmitting: _ctx.isSubmitting,
        successfulPostResponse: _ctx.successfulPostResponse,
        errorPostResponse: _ctx.errorPostResponse
      })
    ], 512);
  }
  const AjaxForm = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = vue.defineComponent({});
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.renderSlot(_ctx.$slots, "default");
  }
  const Passthrough = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function isBooleanLikeSet(value) {
    return !!value && value !== "0";
  }
  function resolveExportSupportsFlat(reportSupportsFlatten, flatParam) {
    return reportSupportsFlatten || isBooleanLikeSet(flatParam);
  }
  const { $: $$4 } = window;
  function getSingleStateIconText(text, addDefault, replacement) {
    if (/(%(.\$)?s+)/g.test(translate(text))) {
      const values = ['<br /><span class="action">'];
      if (replacement) {
        values.push(replacement);
      }
      let result = translate(text, ...values);
      if (addDefault) {
        result += ` (${translate("CoreHome_Default")})`;
      }
      result += "</span>";
      return result;
    }
    return translate(text);
  }
  function getToggledIconText(toggled, textToggled, textUntoggled) {
    if (toggled) {
      return getSingleStateIconText(textToggled, true);
    }
    return getSingleStateIconText(textUntoggled);
  }
  const _sfc_main$5 = vue.defineComponent({
    props: {
      showPeriods: Boolean,
      showFooter: Boolean,
      showFooterIcons: Boolean,
      showSearch: Boolean,
      showFlattenTable: Boolean,
      reportSupportsFlatten: Boolean,
      exportSupportsFlatten: Boolean,
      footerIcons: {
        type: Array,
        required: true
      },
      viewDataTable: {
        type: String,
        required: true
      },
      reportTitle: String,
      requestParams: {
        type: Object,
        required: true
      },
      apiMethodToRequestDataTable: {
        type: String,
        required: true
      },
      maxFilterLimit: {
        type: Number,
        required: true
      },
      showExport: Boolean,
      showExportAsImageIcon: Boolean,
      showAnnotations: Boolean,
      reportId: {
        type: String,
        required: true
      },
      dataTableActions: {
        type: Array,
        required: true
      },
      clientSideParameters: {
        type: Object,
        required: true
      },
      hasMultipleDimensions: Boolean,
      isDataTableEmpty: Boolean,
      showTotalsRow: Boolean,
      showExcludeLowPopulation: Boolean,
      showPivotBySubtable: Boolean,
      selectablePeriods: Array,
      translations: {
        type: Object,
        required: true
      },
      pivotDimensionName: String,
      placement: {
        type: String,
        default: "footer"
      }
    },
    components: {
      Passthrough
    },
    directives: {
      DropdownButton,
      ReportExport
    },
    methods: {
      showExportImage(event) {
        $$4(event.target).closest(".dataTable").find("div.jqplot-target").trigger("piwikExportAsImage");
      }
    },
    computed: {
      randomIdForDropdown() {
        return Math.floor(Math.random() * 999999);
      },
      allFooterIcons() {
        return this.footerIcons.reduce((icons, footerIcon) => {
          icons.push(...footerIcon.buttons);
          return icons;
        }, []);
      },
      activeFooterIcons() {
        const params = this.clientSideParameters;
        const result = [this.viewDataTable];
        if (params.abandonedCarts === 0 || params.abandonedCarts === "0") {
          result.push("ecommerceOrder");
        } else if (params.abandonedCarts === 1 || params.abandonedCarts === "1") {
          result.push("ecommerceAbandonedCart");
        }
        return result.map((id) => this.allFooterIcons.find((button) => button.id === id)).filter((icon) => !!icon);
      },
      activeFooterIcon() {
        var _a2;
        return (_a2 = this.activeFooterIcons[0]) == null ? void 0 : _a2.icon;
      },
      activeFooterIconIds() {
        return this.activeFooterIcons.map((icon) => icon.id);
      },
      numIcons() {
        return this.allFooterIcons.length;
      },
      hasFooterIconsToShow() {
        return !!this.activeFooterIcons.length && this.numIcons > 1;
      },
      reportFormats() {
        const formats = {
          TSV: "TSV (Excel)",
          HTML: "HTML",
          JSON: "JSON",
          XML: "XML",
          CSV: "CSV",
          RSS: "RSS"
        };
        return formats;
      },
      exportSupportsFlat() {
        return resolveExportSupportsFlat(
          !!this.exportSupportsFlatten,
          this.clientSideParameters.flat
        );
      },
      showDimensionsConfigItem() {
        return this.showFlattenTable && `${this.clientSideParameters.flat}` === "1" && this.hasMultipleDimensions;
      },
      showFlatConfigItem() {
        return this.showFlattenTable && `${this.clientSideParameters.flat}` === "1";
      },
      showTotalsConfigItem() {
        return !this.isDataTableEmpty && this.showTotalsRow;
      },
      hasConfigItems() {
        return this.showFlattenTable || this.showDimensionsConfigItem || this.showFlatConfigItem || this.showTotalsConfigItem || this.showExcludeLowPopulation || this.showPivotBySubtable;
      },
      flattenItemText() {
        const params = this.clientSideParameters;
        return getToggledIconText(
          isBooleanLikeSet(params.flat),
          "CoreHome_UnFlattenDataTable",
          "CoreHome_FlattenDataTable"
        );
      },
      keepTotalsRowText() {
        const params = this.clientSideParameters;
        return getToggledIconText(
          isBooleanLikeSet(params.keep_totals_row),
          "CoreHome_RemoveTotalsRowDataTable",
          "CoreHome_AddTotalsRowDataTable"
        );
      },
      includeAggregateRowsText() {
        const params = this.clientSideParameters;
        return getToggledIconText(
          isBooleanLikeSet(params.include_aggregate_rows),
          "CoreHome_DataTableExcludeAggregateRows",
          "CoreHome_DataTableIncludeAggregateRows"
        );
      },
      showDimensionsText() {
        const params = this.clientSideParameters;
        return getToggledIconText(
          isBooleanLikeSet(params.show_dimensions),
          "CoreHome_DataTableCombineDimensions",
          "CoreHome_DataTableShowDimensions"
        );
      },
      pivotByText() {
        const params = this.clientSideParameters;
        if (isBooleanLikeSet(params.pivotBy)) {
          return getSingleStateIconText("CoreHome_UndoPivotBySubtable", true);
        }
        return getSingleStateIconText("CoreHome_PivotBySubtable", false, this.pivotDimensionName);
      },
      excludeLowPopText() {
        const params = this.clientSideParameters;
        return getToggledIconText(
          isBooleanLikeSet(params.enable_filter_excludelowpop),
          "CoreHome_IncludeRowsWithLowPopulation",
          "CoreHome_ExcludeRowsWithLowPopulation"
        );
      },
      isAnyConfigureIconHighlighted() {
        const params = this.clientSideParameters;
        return isBooleanLikeSet(params.flat) || isBooleanLikeSet(params.keep_totals_row) || isBooleanLikeSet(params.include_aggregate_rows) || isBooleanLikeSet(params.show_dimensions) || isBooleanLikeSet(params.pivotBy) || isBooleanLikeSet(params.enable_filter_excludelowpop);
      },
      isTableView() {
        return this.viewDataTable === "table" || this.viewDataTable === "tableAllColumns" || this.viewDataTable === "tableGoals";
      }
    }
  });
  const _hoisted_1$5 = { key: 0 };
  const _hoisted_2$3 = ["data-target", "title"];
  const _hoisted_3$3 = ["data-target"];
  const _hoisted_4$3 = ["title"];
  const _hoisted_5$3 = ["title", "src"];
  const _hoisted_6$2 = ["id"];
  const _hoisted_7$2 = ["data-footer-icon-id"];
  const _hoisted_8$2 = ["title"];
  const _hoisted_9$1 = ["title", "src"];
  const _hoisted_10$1 = { key: 2 };
  const _hoisted_11$1 = ["title"];
  const _hoisted_12$1 = ["title"];
  const _hoisted_13$1 = ["title"];
  const _hoisted_14 = ["title"];
  const _hoisted_15 = ["title"];
  const _hoisted_16 = ["id", "title"];
  const _hoisted_17 = ["title"];
  const _hoisted_18 = ["title", "src"];
  const _hoisted_19 = ["id"];
  const _hoisted_20 = { key: 0 };
  const _hoisted_21 = ["innerHTML"];
  const _hoisted_22 = { key: 1 };
  const _hoisted_23 = ["innerHTML"];
  const _hoisted_24 = { key: 2 };
  const _hoisted_25 = ["innerHTML"];
  const _hoisted_26 = { key: 3 };
  const _hoisted_27 = ["innerHTML"];
  const _hoisted_28 = { key: 4 };
  const _hoisted_29 = ["innerHTML"];
  const _hoisted_30 = { key: 5 };
  const _hoisted_31 = ["innerHTML"];
  const _hoisted_32 = ["title", "data-target"];
  const _hoisted_33 = { class: "periodName" };
  const _hoisted_34 = ["id"];
  const _hoisted_35 = ["data-period"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Passthrough = vue.resolveComponent("Passthrough");
    const _directive_dropdown_button = vue.resolveDirective("dropdown-button");
    const _directive_report_export = vue.resolveDirective("report-export");
    return _ctx.showFooter && _ctx.showFooterIcons ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      _ctx.hasConfigItems && (_ctx.isAnyConfigureIconHighlighted || _ctx.isTableView) ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
        key: 0,
        class: vue.normalizeClass(["dropdown-button dropdownConfigureIcon dataTableAction", { highlighted: _ctx.isAnyConfigureIconHighlighted }]),
        href: "",
        onClick: _cache[0] || (_cache[0] = vue.withModifiers(() => {
        }, ["prevent"])),
        "data-target": `dropdownConfigure${_ctx.randomIdForDropdown}`,
        title: _ctx.translate("CoreHome_ReportConfigure"),
        style: { "margin-right": "3.5px" }
      }, [..._cache[8] || (_cache[8] = [
        vue.createElementVNode("span", { class: "icon-configure" }, null, -1)
      ])], 10, _hoisted_2$3)), [
        [_directive_dropdown_button]
      ]) : vue.createCommentVNode("", true),
      _ctx.hasFooterIconsToShow ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
        key: 1,
        class: "dropdown-button dataTableAction activateVisualizationSelection",
        href: "",
        "data-target": `dropdownVisualizations${_ctx.randomIdForDropdown}`,
        style: { "margin-right": "3.5px" },
        onClick: _cache[1] || (_cache[1] = vue.withModifiers(() => {
        }, ["prevent"]))
      }, [
        /^icon-/.test(_ctx.activeFooterIcon || "") ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          title: _ctx.translate("CoreHome_ChangeVisualization"),
          class: vue.normalizeClass(_ctx.activeFooterIcon)
        }, null, 10, _hoisted_4$3)) : (vue.openBlock(), vue.createElementBlock("img", {
          key: 1,
          title: _ctx.translate("CoreHome_ChangeVisualization"),
          width: "16",
          height: "16",
          src: _ctx.activeFooterIcon
        }, null, 8, _hoisted_5$3))
      ], 8, _hoisted_3$3)), [
        [_directive_dropdown_button]
      ]) : vue.createCommentVNode("", true),
      _ctx.showFooterIcons ? (vue.openBlock(), vue.createElementBlock("ul", {
        key: 2,
        id: `dropdownVisualizations${_ctx.randomIdForDropdown}`,
        class: "dropdown-content dataTableFooterIcons"
      }, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.footerIcons, (footerIconGroup, index) => {
          return vue.openBlock(), vue.createBlock(_component_Passthrough, { key: index }, {
            default: vue.withCtx(() => [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(footerIconGroup.buttons.filter((i) => !!i.icon), (footerIcon) => {
                return vue.openBlock(), vue.createElementBlock("li", {
                  key: footerIcon.id
                }, [
                  vue.createElementVNode("a", {
                    class: vue.normalizeClass(`${footerIconGroup.class} tableIcon
              ${_ctx.activeFooterIconIds.indexOf(footerIcon.id) !== -1 ? "activeIcon" : ""}`),
                    "data-footer-icon-id": footerIcon.id
                  }, [
                    /^icon-/.test(footerIcon.icon || "") ? (vue.openBlock(), vue.createElementBlock("span", {
                      key: 0,
                      title: footerIcon.title,
                      class: vue.normalizeClass(footerIcon.icon),
                      style: { "margin-right": "5.5px" }
                    }, null, 10, _hoisted_8$2)) : (vue.openBlock(), vue.createElementBlock("img", {
                      key: 1,
                      width: "16",
                      height: "16",
                      title: footerIcon.title,
                      src: footerIcon.icon,
                      style: { "margin-right": "5.5px" }
                    }, null, 8, _hoisted_9$1)),
                    footerIcon.title ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_10$1, vue.toDisplayString(footerIcon.title), 1)) : vue.createCommentVNode("", true)
                  ], 10, _hoisted_7$2)
                ]);
              }), 128)),
              _cache[9] || (_cache[9] = vue.createElementVNode("li", { class: "divider" }, null, -1))
            ]),
            _: 2
          }, 1024);
        }), 128)),
        _cache[10] || (_cache[10] = vue.createElementVNode("li", { class: "divider" }, null, -1))
      ], 8, _hoisted_6$2)) : vue.createCommentVNode("", true),
      _ctx.showExport ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
        key: 3,
        class: "dataTableAction activateExportSelection",
        title: _ctx.translate("General_ExportThisReport"),
        href: "",
        style: { "margin-right": "3.5px" },
        onClick: _cache[2] || (_cache[2] = vue.withModifiers(() => {
        }, ["prevent"]))
      }, [..._cache[11] || (_cache[11] = [
        vue.createElementVNode("span", { class: "icon-export" }, null, -1)
      ])], 8, _hoisted_11$1)), [
        [_directive_report_export, {
          reportTitle: _ctx.reportTitle,
          requestParams: _ctx.requestParams,
          apiMethod: _ctx.apiMethodToRequestDataTable,
          reportFormats: _ctx.reportFormats,
          maxFilterLimit: _ctx.maxFilterLimit,
          canExportFlat: _ctx.exportSupportsFlat
        }]
      ]) : vue.createCommentVNode("", true),
      _ctx.showExportAsImageIcon ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 4,
        class: "dataTableAction tableIcon",
        href: "",
        id: "dataTableFooterExportAsImageIcon",
        onClick: _cache[3] || (_cache[3] = vue.withModifiers(($event) => _ctx.showExportImage($event), ["prevent"])),
        title: _ctx.translate("General_ExportAsImage"),
        style: { "margin-right": "3.5px" }
      }, [..._cache[12] || (_cache[12] = [
        vue.createElementVNode("span", { class: "icon-image" }, null, -1)
      ])], 8, _hoisted_12$1)) : vue.createCommentVNode("", true),
      _ctx.showAnnotations ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 5,
        class: "dataTableAction annotationView",
        href: "",
        title: _ctx.translate("Annotations_Annotations"),
        onClick: _cache[4] || (_cache[4] = vue.withModifiers(() => {
        }, ["prevent"])),
        style: { "margin-right": "3.5px" }
      }, [..._cache[13] || (_cache[13] = [
        vue.createElementVNode("span", { class: "icon-annotation" }, null, -1)
      ])], 8, _hoisted_13$1)) : vue.createCommentVNode("", true),
      _ctx.showSearch ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 6,
        class: "dropdown-button dataTableAction searchAction",
        href: "",
        title: _ctx.translate("General_Search"),
        style: { "margin-right": "3.5px" },
        draggable: "false",
        onClick: _cache[5] || (_cache[5] = vue.withModifiers(() => {
        }, ["prevent"]))
      }, [
        _cache[14] || (_cache[14] = vue.createElementVNode("span", {
          class: "icon-search",
          draggable: "false"
        }, null, -1)),
        vue.createElementVNode("span", {
          class: "icon-close",
          draggable: "false",
          title: _ctx.translate("CoreHome_CloseSearch")
        }, null, 8, _hoisted_15),
        vue.createElementVNode("input", {
          id: `widgetSearch_${_ctx.reportId}_${_ctx.placement}`,
          title: _ctx.translate("CoreHome_DataTableHowToSearch"),
          type: "text",
          class: "dataTableSearchInput"
        }, null, 8, _hoisted_16)
      ], 8, _hoisted_14)) : vue.createCommentVNode("", true),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.dataTableActions, (action) => {
        return vue.openBlock(), vue.createElementBlock("a", {
          key: action.id,
          class: vue.normalizeClass(`dataTableAction ${action.id}`),
          href: "",
          onClick: _cache[6] || (_cache[6] = vue.withModifiers(() => {
          }, ["prevent"])),
          title: action.title,
          style: { "margin-right": "3.5px" }
        }, [
          /^icon-/.test(action.icon || "") ? (vue.openBlock(), vue.createElementBlock("span", {
            key: 0,
            class: vue.normalizeClass(action.icon)
          }, null, 2)) : (vue.openBlock(), vue.createElementBlock("img", {
            key: 1,
            width: "16",
            height: "16",
            title: action.title,
            src: action.icon
          }, null, 8, _hoisted_18))
        ], 10, _hoisted_17);
      }), 128)),
      vue.createElementVNode("ul", {
        id: `dropdownConfigure${_ctx.randomIdForDropdown}`,
        class: "dropdown-content tableConfiguration"
      }, [
        _ctx.showFlattenTable ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_20, [
          vue.createElementVNode("div", {
            class: "configItem dataTableFlatten",
            innerHTML: _ctx.$sanitize(_ctx.flattenItemText)
          }, null, 8, _hoisted_21)
        ])) : vue.createCommentVNode("", true),
        _ctx.showDimensionsConfigItem ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_22, [
          vue.createElementVNode("div", {
            class: "configItem dataTableShowDimensions",
            innerHTML: _ctx.$sanitize(_ctx.showDimensionsText)
          }, null, 8, _hoisted_23)
        ])) : vue.createCommentVNode("", true),
        _ctx.showFlatConfigItem ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_24, [
          vue.createElementVNode("div", {
            class: "configItem dataTableIncludeAggregateRows",
            innerHTML: _ctx.$sanitize(_ctx.includeAggregateRowsText)
          }, null, 8, _hoisted_25)
        ])) : vue.createCommentVNode("", true),
        _ctx.showTotalsConfigItem ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_26, [
          vue.createElementVNode("div", {
            class: "configItem dataTableShowTotalsRow",
            innerHTML: _ctx.$sanitize(_ctx.keepTotalsRowText)
          }, null, 8, _hoisted_27)
        ])) : vue.createCommentVNode("", true),
        _ctx.showExcludeLowPopulation ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_28, [
          vue.createElementVNode("div", {
            class: "configItem dataTableExcludeLowPopulation",
            innerHTML: _ctx.$sanitize(_ctx.excludeLowPopText)
          }, null, 8, _hoisted_29)
        ])) : vue.createCommentVNode("", true),
        _ctx.showPivotBySubtable ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_30, [
          vue.createElementVNode("div", {
            class: "configItem dataTablePivotBySubtable",
            innerHTML: _ctx.$sanitize(_ctx.pivotByText)
          }, null, 8, _hoisted_31)
        ])) : vue.createCommentVNode("", true)
      ], 8, _hoisted_19),
      _ctx.showPeriods ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
        key: 7,
        class: "dropdown-button dataTableAction activatePeriodsSelection",
        href: "",
        onClick: _cache[7] || (_cache[7] = vue.withModifiers(() => {
        }, ["prevent"])),
        title: _ctx.translate("CoreHome_ChangePeriod"),
        "data-target": `dropdownPeriods${_ctx.randomIdForDropdown}`
      }, [
        vue.createElementVNode("div", null, [
          _cache[15] || (_cache[15] = vue.createElementVNode("span", { class: "icon-calendar" }, null, -1)),
          vue.createElementVNode("span", _hoisted_33, vue.toDisplayString(_ctx.translations[_ctx.clientSideParameters.period] || _ctx.clientSideParameters.period), 1)
        ])
      ], 8, _hoisted_32)), [
        [_directive_dropdown_button]
      ]) : vue.createCommentVNode("", true),
      _ctx.showPeriods ? (vue.openBlock(), vue.createElementBlock("ul", {
        key: 8,
        id: `dropdownPeriods${_ctx.randomIdForDropdown}`,
        class: "dropdown-content dataTablePeriods"
      }, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectablePeriods, (selectablePeriod) => {
          return vue.openBlock(), vue.createElementBlock("li", { key: selectablePeriod }, [
            vue.createElementVNode("a", {
              "data-period": selectablePeriod,
              class: vue.normalizeClass(`tableIcon ${_ctx.clientSideParameters.period === selectablePeriod ? "activeIcon" : ""}`)
            }, [
              vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translations[selectablePeriod] || selectablePeriod), 1)
            ], 10, _hoisted_35)
          ]);
        }), 128))
      ], 8, _hoisted_34)) : vue.createCommentVNode("", true)
    ])) : vue.createCommentVNode("", true);
  }
  const DataTableActions = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    props: {
      isMultiServerEnvironment: Boolean,
      lastUpdateCheckFailed: Boolean,
      latestVersionAvailable: String,
      isSuperUser: Boolean,
      isAdminArea: Boolean,
      isInternetEnabled: Boolean,
      updateCheck: String,
      isAnonymous: Boolean,
      hasSomeViewAccess: Boolean,
      contactEmail: String,
      piwikVersion: String
    },
    components: {
      Passthrough
    },
    directives: {
      ExpandOnHover
    },
    computed: {
      updateNowText() {
        let text = "";
        if (this.isMultiServerEnvironment) {
          const link = externalRawLink(`https://builds.matomo.org/matomo-${this.latestVersionAvailable}.zip`);
          text = translate(
            "CoreHome_OneClickUpdateNotPossibleAsMultiServerEnvironment",
            `<a rel="noreferrer noopener" href="${link}">builds.matomo.org</a>`
          );
        } else {
          text = translate(
            "General_PiwikXIsAvailablePleaseUpdateNow",
            this.latestVersionAvailable || "",
            '<br /><a href="index.php?module=CoreUpdater&amp;action=newVersionAvailable">',
            "</a>",
            externalLink("https://matomo.org/changelog/"),
            "</a>"
          );
        }
        return `${text}<br/>`;
      },
      updateAvailableText() {
        const updateSubject = translate(
          "General_NewUpdatePiwikX",
          this.latestVersionAvailable || ""
        );
        const matomoLink = externalLink("https://matomo.org/") + "Matomo</a>";
        const changelogLinkStart = externalLink("https://matomo.org/changelog/");
        const text = translate(
          "General_PiwikXIsAvailablePleaseNotifyPiwikAdmin",
          `${matomoLink} ${changelogLinkStart}${this.latestVersionAvailable}</a>`,
          `<a href="mailto:${this.contactEmail}?subject=${encodeURIComponent(updateSubject)}">`,
          "</a>"
        );
        return `${text}<br />`;
      }
    }
  });
  const _hoisted_1$4 = {
    key: 0,
    class: "title",
    style: { "cursor": "pointer" },
    ref: "expander"
  };
  const _hoisted_2$2 = {
    key: 1,
    class: "title",
    href: "?module=CoreUpdater&action=newVersionAvailable",
    style: { "cursor": "pointer" },
    ref: "expander"
  };
  const _hoisted_3$2 = ["innerHTML"];
  const _hoisted_4$2 = ["href"];
  const _hoisted_5$2 = { id: "updateCheckLinkContainer" };
  const _hoisted_6$1 = { class: "dropdown positionInViewport" };
  const _hoisted_7$1 = ["innerHTML"];
  const _hoisted_8$1 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Passthrough = vue.resolveComponent("Passthrough");
    const _directive_expand_on_hover = vue.resolveDirective("expand-on-hover");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      id: "header_message",
      class: vue.normalizeClass(["piwikSelector", {
        header_info: !_ctx.latestVersionAvailable || _ctx.lastUpdateCheckFailed,
        update_available: _ctx.latestVersionAvailable
      }])
    }, [
      _ctx.latestVersionAvailable ? (vue.openBlock(), vue.createBlock(_component_Passthrough, { key: 0 }, {
        default: vue.withCtx(() => [
          _ctx.isMultiServerEnvironment ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$4, [
            _cache[0] || (_cache[0] = vue.createElementVNode("span", { class: "icon-update" }, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_NewUpdatePiwikX", _ctx.latestVersionAvailable)), 1)
          ], 512)) : (vue.openBlock(), vue.createElementBlock("a", _hoisted_2$2, [
            _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-update" }, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_NewUpdatePiwikX", _ctx.latestVersionAvailable)), 1)
          ], 512))
        ]),
        _: 1
      })) : _ctx.isSuperUser && (_ctx.isAdminArea || _ctx.lastUpdateCheckFailed) ? (vue.openBlock(), vue.createBlock(_component_Passthrough, { key: 1 }, {
        default: vue.withCtx(() => [
          _ctx.isInternetEnabled ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            class: "title",
            innerHTML: _ctx.$sanitize(_ctx.updateCheck)
          }, null, 8, _hoisted_3$2)) : (vue.openBlock(), vue.createElementBlock("a", {
            key: 1,
            class: "title",
            href: _ctx.externalRawLink("https://matomo.org/changelog/"),
            target: "_blank",
            rel: "noreferrer noopener"
          }, [
            vue.createElementVNode("span", _hoisted_5$2, vue.toDisplayString(_ctx.translate("CoreHome_SeeAvailableVersions")), 1)
          ], 8, _hoisted_4$2))
        ]),
        _: 1
      })) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_6$1, [
        _ctx.latestVersionAvailable && _ctx.isSuperUser ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          innerHTML: _ctx.$sanitize(_ctx.updateNowText)
        }, null, 8, _hoisted_7$1)) : _ctx.latestVersionAvailable && _ctx.hasSomeViewAccess && !_ctx.isAnonymous ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 1,
          innerHTML: _ctx.$sanitize(_ctx.updateAvailableText)
        }, null, 8, _hoisted_8$1)) : vue.createCommentVNode("", true),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_YouAreCurrentlyUsing", _ctx.piwikVersion || "")), 1)
      ])
    ], 2)), [
      [_directive_expand_on_hover, { expander: "expander" }]
    ]);
  }
  const VersionInfoHeaderMessage = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const { $: $$3 } = window;
  const _sfc_main$3 = vue.defineComponent({
    props: {
      menu: {
        type: Object,
        required: true
      }
    },
    directives: {
      SideNav
    },
    methods: {
      getMenuUrl(params) {
        return `?${instance$1.stringify(__spreadValues(__spreadValues({}, instance$1.urlParsed.value), params))}`;
      },
      translateIfNecessary(name) {
        if (name.includes("_")) {
          return translate(name);
        }
        return name;
      }
    },
    computed: {
      menuWithSubmenuItems() {
        const menu = this.menu || {};
        return Object.fromEntries(
          Object.entries(menu).filter(([, level2]) => {
            const itemsWithoutUnderscore = Object.entries(level2).filter(([name]) => name[0] !== "_");
            return Object.keys(itemsWithoutUnderscore).length;
          })
        );
      },
      activateLeftMenu() {
        return $$3("nav .activateLeftMenu")[0];
      }
    }
  });
  const _hoisted_1$3 = {
    id: "mobile-left-menu",
    class: "sidenav hide-on-large-only"
  };
  const _hoisted_2$1 = { class: "collapsible collapsible-accordion" };
  const _hoisted_3$1 = { class: "collapsible-header" };
  const _hoisted_4$1 = { class: "collapsible-body" };
  const _hoisted_5$1 = ["title", "href"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_side_nav = vue.resolveDirective("side-nav");
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$3, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.menuWithSubmenuItems, (level2, level1) => {
        return vue.openBlock(), vue.createElementBlock("li", {
          class: "no-padding",
          key: level1
        }, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("ul", _hoisted_2$1, [
            vue.createElementVNode("li", null, [
              vue.createElementVNode("a", _hoisted_3$1, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translateOrDefault(String(level1))), 1),
                vue.createElementVNode("i", {
                  class: vue.normalizeClass(level2._icon || "icon-chevron-down")
                }, null, 2)
              ]),
              vue.createElementVNode("div", _hoisted_4$1, [
                vue.createElementVNode("ul", null, [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(Object.entries(level2).filter(([n]) => n[0] !== "_"), ([name, params]) => {
                    return vue.openBlock(), vue.createElementBlock("li", { key: name }, [
                      vue.createElementVNode("a", {
                        title: params._tooltip ? _ctx.translateIfNecessary(params._tooltip) : "",
                        target: "_self",
                        href: _ctx.getMenuUrl(params._url)
                      }, vue.toDisplayString(_ctx.translateIfNecessary(name)), 9, _hoisted_5$1)
                    ]);
                  }), 128))
                ])
              ])
            ])
          ])), [
            [_directive_side_nav, { activator: _ctx.activateLeftMenu }]
          ])
        ]);
      }), 128))
    ]);
  }
  const MobileLeftMenu = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$2 } = window;
  function scrollToAnchorNode($node) {
    $$2.scrollTo($node, 20);
  }
  function scrollToAnchorIfPossible(hash, event) {
    if (!hash) {
      return;
    }
    if (hash.indexOf("&") !== -1) {
      return;
    }
    let $node = null;
    try {
      $node = $$2(`#${hash}`);
    } catch (err) {
      return;
    }
    if ($node == null ? void 0 : $node.length) {
      scrollToAnchorNode($node);
      return;
    }
    $node = $$2(`a[name=${hash}]`);
    if ($node == null ? void 0 : $node.length) {
      scrollToAnchorNode($node);
    }
  }
  function isLinkWithinSamePage(location, newUrl) {
    if (location && location.origin && newUrl.indexOf(location.origin) === -1) {
      return false;
    }
    if (location && location.pathname && newUrl.indexOf(location.pathname) === -1) {
      return false;
    }
    if (location && location.search && newUrl.indexOf(location.search) === -1) {
      return false;
    }
    return true;
  }
  function handleScrollToAnchorIfPresentOnPageLoad() {
    if (window.location.hash.slice(0, 2) === "#/") {
      const hash = window.location.hash.slice(2);
      scrollToAnchorIfPossible(hash);
    }
  }
  function handleScrollToAnchorAfterPageLoad() {
    vue.watch(() => instance$1.url.value, (newUrl, oldUrl) => {
      if (!newUrl) {
        return;
      }
      const hashPos = newUrl.href.indexOf("#/");
      if (hashPos === -1) {
        return;
      }
      if (oldUrl && !isLinkWithinSamePage(oldUrl, newUrl.href)) {
        return;
      }
      const hash = newUrl.href.slice(hashPos + 2);
      scrollToAnchorIfPossible(hash);
    });
  }
  handleScrollToAnchorAfterPageLoad();
  $$2(handleScrollToAnchorIfPresentOnPageLoad);
  function scrollToAnchorInUrl() {
    vue.nextTick(handleScrollToAnchorIfPresentOnPageLoad);
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function collectPasswordInputs(el) {
    const targets = [];
    if (el.tagName === "INPUT" && el.type === "password") {
      targets.push(el);
    } else {
      const nested = el.querySelectorAll('input[type="password"]');
      nested.forEach((nestedEl) => targets.push(nestedEl));
    }
    return targets;
  }
  function setupAutoClear(el, delay) {
    let timeoutId;
    let lastValue = el.value;
    const clearValue = () => {
      el.value = "";
      el.dispatchEvent(new Event("input"));
    };
    const resetTimer = () => {
      if (timeoutId) clearTimeout(timeoutId);
      timeoutId = setTimeout(clearValue, delay * 1e3);
    };
    const inputListener = () => resetTimer();
    const changeListener = () => resetTimer();
    el.addEventListener("input", inputListener);
    el.addEventListener("change", changeListener);
    el.dataset.autoClearEnabled = "true";
    const intervalId = setInterval(() => {
      if (el.value !== lastValue) {
        lastValue = el.value;
        resetTimer();
      }
    }, 300);
    el.onUmounted = {
      cleanup() {
        clearTimeout(timeoutId);
        clearInterval(intervalId);
        el.removeEventListener("input", inputListener);
        el.removeEventListener("change", changeListener);
        delete el.dataset.autoClearEnabled;
      }
    };
  }
  const AutoClearPassword = {
    mounted(el, binding) {
      const delay = binding.value && binding.value.delay || 600;
      const targets = collectPasswordInputs(el);
      targets.forEach((input) => setupAutoClear(input, delay));
    },
    unmounted(el) {
      const targets = collectPasswordInputs(el);
      targets.forEach((e) => {
        if (e.onUmounted && typeof e.onUmounted.cleanup === "function") {
          e.onUmounted.cleanup();
          delete e.onUmounted;
        }
      });
    }
  };
  const _sfc_main$2 = vue.defineComponent({
    props: {
      validationRules: {
        type: Array,
        required: true
      },
      password: {
        type: String,
        default: ""
      },
      externalInputSelector: {
        type: String,
        default: ""
      }
    },
    data() {
      return {
        pwd: "",
        rules: []
      };
    },
    emits: ["check:isValid"],
    watch: {
      pwdValue: {
        immediate: true,
        handler(pwd) {
          const rulesValidity = [];
          this.rules.forEach((rule) => {
            if (!pwd.length && typeof rule.passed !== "undefined") {
              delete rule.passed;
              return;
            }
            try {
              const regex = new RegExp(rule.validationRegex.replace(/^\/|\/$/g, ""));
              if (regex.test(pwd)) {
                rule.passed = true;
                rulesValidity.push(true);
              } else {
                rule.passed = false;
              }
            } catch (e) {
              console.log("Invalid password validation pattern:", e);
            }
          });
          if (this.rules.length > 0 && rulesValidity.length === this.rules.length) {
            this.$emit("check:isValid", true);
          }
        }
      }
    },
    computed: {
      pwdValue() {
        var _a2;
        if ((_a2 = this.externalInputSelector) == null ? void 0 : _a2.length) {
          return this.pwd;
        }
        return this.password;
      }
    },
    mounted() {
      var _a2;
      this.rules = this.validationRules.length ? this.validationRules.map((rule) => __spreadValues({}, rule)) : [];
      if ((_a2 = this.externalInputSelector) == null ? void 0 : _a2.length) {
        const input = document.querySelector(this.externalInputSelector);
        if (input) {
          input.addEventListener("input", this.handleExternalInput);
          this.pwd = input.value;
        }
      }
    },
    unmounted() {
      var _a2;
      if ((_a2 = this.externalInputSelector) == null ? void 0 : _a2.length) {
        const input = document.querySelector(this.externalInputSelector);
        if (input) {
          input.removeEventListener("input", this.handleExternalInput);
        }
      }
    },
    methods: {
      ruleStatus(rule) {
        if (typeof rule.passed === "undefined") {
          return "undefined";
        }
        return rule.passed ? "valid" : "invalid";
      },
      handleExternalInput(event) {
        const target = event.target;
        this.pwd = target.value;
      }
    }
  });
  const _hoisted_1$2 = {
    key: 0,
    class: "password-strength row"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return _ctx.rules.length ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$2, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.rules, (rule) => {
        return vue.openBlock(), vue.createElementBlock("li", {
          key: rule.ruleText,
          class: vue.normalizeClass(`col s12 xl6 rule rule-${_ctx.ruleStatus(rule)}`)
        }, [
          vue.createElementVNode("span", {
            class: vue.normalizeClass({
              "icon": true,
              "icon-ok": _ctx.ruleStatus(rule) === "valid",
              "icon-close": _ctx.ruleStatus(rule) === "invalid",
              "icon-circle": _ctx.ruleStatus(rule) === "undefined"
            })
          }, null, 2),
          vue.createTextVNode(" " + vue.toDisplayString(rule.ruleText), 1)
        ], 2);
      }), 128))
    ])) : vue.createCommentVNode("", true);
  }
  const PasswordStrength = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const Field = useExternalPluginComponent("CorePluginsAdmin", "Field");
  const Form = useExternalPluginComponent("CorePluginsAdmin", "Form");
  const { $: $$1 } = window;
  const _sfc_main$1 = vue.defineComponent({
    directives: {
      Form
    },
    components: {
      Field,
      MatomoLoader
    },
    props: {
      /**
       * The reactive class for controlling the settings of the modal from multiple components.
       */
      modalStore: {
        type: Object,
        required: true
      },
      /**
       * Option to hide the site selector when it's not needed.
       */
      hideSiteSelector: {
        type: Boolean,
        default: false
      },
      /**
       * Optional "Learn more." link to append to the end of the description text if provided.
       */
      descriptionLearnMoreLink: {
        type: String,
        default: ""
      }
    },
    data() {
      return {
        isLoading: true,
        isValidated: false,
        duplicationErrors: [],
        destinationSite: null,
        hasBeenSubmitted: false
      };
    },
    watch: {
      isModalVisible(newValue) {
        if (!newValue) {
          return;
        }
        let beforeShowModal = void 0;
        if (this.modalStore.adapter.beforeShowModal) {
          beforeShowModal = this.modalStore.adapter.beforeShowModal();
        }
        if (!beforeShowModal || typeof beforeShowModal === "undefined") {
          beforeShowModal = new Promise((resolve2) => resolve2());
        }
        this.showModal();
        beforeShowModal.then(() => {
          this.isLoading = false;
        });
      },
      destinationSite() {
        this.isValidated = false;
      }
    },
    methods: {
      closeModal() {
        const root = this.$refs.root;
        const $root = $$1(root);
        $root.modal("close");
      },
      resetModal() {
        this.modalStore.hideModal();
        this.destinationSite = null;
        this.isLoading = true;
        this.isValidated = false;
        this.duplicationErrors = [];
        this.hasBeenSubmitted = false;
      },
      showModal() {
        const root = this.$refs.root;
        const $root = $$1(root);
        $root.modal({
          dismissible: true,
          onCloseEnd: () => {
            this.resetModal();
          }
        }).modal("open");
      },
      submitRequest() {
        this.hasBeenSubmitted = true;
        this.getValidationResultPromise().then((validationResult) => {
          var _a2;
          if (!validationResult.isValid && validationResult.errorMessages.length > 0) {
            this.isValidated = true;
            this.hasBeenSubmitted = false;
            this.duplicationErrors = validationResult.errorMessages;
            return;
          }
          const params = this.modalStore.adapter.prepareApiParams(
            this.modalStore.getFormValues((_a2 = this.destinationSite) == null ? void 0 : _a2.id)
          );
          this.modalStore.adapter.submitRequest(params).then((response) => {
            if (!response || !response.success) {
              this.setErrorMessages(response);
              return;
            }
            if (this.modalStore.adapter.onSuccess) {
              this.modalStore.adapter.onSuccess(response);
            }
            this.closeModal();
          }).catch((error) => {
            this.setErrorMessages();
            if (this.modalStore.adapter.onFailure) {
              this.modalStore.adapter.onFailure(error);
            }
            console.log("Unexpected server error during request.", error);
          }).finally(() => {
            this.hasBeenSubmitted = false;
          });
        });
      },
      getValidationResultPromise() {
        var _a2;
        this.duplicationErrors = [];
        const validationResultPromise = this.modalStore.adapter.validateFormFields(
          this.modalStore.getFormValues((_a2 = this.destinationSite) == null ? void 0 : _a2.id)
        );
        return "isValid" in validationResultPromise ? new Promise((resolve2) => resolve2(validationResultPromise)) : validationResultPromise;
      },
      setErrorMessages(response = null) {
        let message = (response == null ? void 0 : response.message) || "";
        if (!message || message.length === 0) {
          message = translate("General_ErrorRequest", "", "");
        }
        this.duplicationErrors = [];
        this.duplicationErrors.push(message);
      }
    },
    mounted() {
      vue.watch(
        () => this.modalStore.state.entityFormData,
        () => {
          this.isValidated = false;
        },
        { deep: true }
      );
    },
    computed: {
      isModalVisible() {
        var _a2;
        return (_a2 = this.modalStore.state.isModalVisible) != null ? _a2 : false;
      },
      getModalTitle() {
        return translate("CoreHome_CopyX", this.modalStore.getEntityTypeTranslation);
      },
      getNoteText() {
        const noteText = translate(
          "CoreHome_CopyModalNote",
          "<strong>",
          "</strong>",
          this.modalStore.getEntityTypeTranslation
        );
        return `${noteText}`;
      },
      getDuplicateDescription() {
        return translate("CoreHome_CopyXDescription", this.modalStore.getEntityTypeTranslation);
      },
      getLearnMoreLink() {
        if (!this.descriptionLearnMoreLink) {
          return "";
        }
        const linkString = externalLink(this.descriptionLearnMoreLink);
        return translate("CoreHome_LearnMoreFullStop", linkString, "</a>");
      },
      getIsValid() {
        if (!this.isValidated) {
          return true;
        }
        return Array.isArray(this.duplicationErrors) && this.duplicationErrors.length === 0;
      }
    }
  });
  const _hoisted_1$1 = { class: "main-duplicator-modal-content" };
  const _hoisted_2 = { class: "modal-header" };
  const _hoisted_3 = {
    key: 0,
    class: "modal-sub-header"
  };
  const _hoisted_4 = { class: "loading-message" };
  const _hoisted_5 = {
    key: 0,
    class: "modal-sub-header"
  };
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = { class: "modal-content" };
  const _hoisted_8 = { class: "modal-inputs" };
  const _hoisted_9 = { class: "modal-sub-footer" };
  const _hoisted_10 = ["innerHTML"];
  const _hoisted_11 = ["innerHTML"];
  const _hoisted_12 = { class: "modal-footer" };
  const _hoisted_13 = ["disabled"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_Field = vue.resolveComponent("Field");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass({
        "modal": true,
        "entity-duplicator-modal": true,
        "slot-configured": _ctx.$slots.default
      }),
      ref: "root"
    }, [
      vue.withDirectives(vue.createElementVNode("div", _hoisted_1$1, [
        vue.createElementVNode("div", _hoisted_2, [
          _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "btn-close modal-close" }, [
            vue.createElementVNode("i", { class: "icon-close" })
          ], -1)),
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.getModalTitle), 1)
        ]),
        _ctx.isLoading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
          vue.createVNode(_component_MatomoLoader),
          vue.createElementVNode("span", _hoisted_4, vue.toDisplayString(_ctx.translate("General_Loading")), 1)
        ])) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
          !_ctx.hideSiteSelector ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5, [
            vue.createElementVNode("p", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.getDuplicateDescription) + " ", 1),
              _ctx.descriptionLearnMoreLink ? (vue.openBlock(), vue.createElementBlock("span", {
                key: 0,
                innerHTML: _ctx.$sanitize(_ctx.getLearnMoreLink)
              }, null, 8, _hoisted_6)) : vue.createCommentVNode("", true)
            ]),
            vue.createVNode(_component_Field, {
              uicontrol: "site",
              name: "siteSelector",
              title: _ctx.translate("CoreHome_ChooseWebsite"),
              modelValue: _ctx.destinationSite,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.destinationSite = $event),
              "ui-control-attributes": {
                onlySitesWithAtLeastWriteAccess: true,
                siteTypesToExclude: ["rollup"]
              }
            }, null, 8, ["title", "modelValue"])
          ])) : vue.createCommentVNode("", true),
          vue.createElementVNode("div", _hoisted_7, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_8, [
              vue.renderSlot(_ctx.$slots, "default")
            ])), [
              [_directive_form]
            ])
          ]),
          vue.createElementVNode("div", _hoisted_9, [
            _ctx.duplicationErrors.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", {
              key: 0,
              class: vue.normalizeClass({
                "alert": true,
                "alert-danger": true,
                "error-list": _ctx.duplicationErrors.length > 1
              })
            }, [
              vue.createElementVNode("ul", null, [
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.duplicationErrors, (duplicationError, index) => {
                  return vue.openBlock(), vue.createElementBlock("li", {
                    key: index,
                    innerHTML: _ctx.$sanitize(duplicationError)
                  }, null, 8, _hoisted_10);
                }), 128))
              ])
            ], 2)) : vue.createCommentVNode("", true),
            vue.createElementVNode("p", {
              class: "note-text",
              innerHTML: _ctx.$sanitize(_ctx.getNoteText)
            }, null, 8, _hoisted_11)
          ]),
          vue.createElementVNode("div", _hoisted_12, [
            vue.withDirectives(vue.createVNode(_component_MatomoLoader, null, null, 512), [
              [vue.vShow, _ctx.hasBeenSubmitted]
            ]),
            vue.createElementVNode("button", {
              class: "btn",
              disabled: !_ctx.getIsValid || _ctx.hasBeenSubmitted,
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.submitRequest())
            }, vue.toDisplayString(_ctx.translate("General_Copy")), 9, _hoisted_13)
          ])
        ], 64))
      ], 512), [
        [vue.vShow, _ctx.isModalVisible]
      ])
    ], 2);
  }
  const EntityDuplicatorModal = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      /**
       * Useful data to pass to the modal, such as the ID for which entity this action triggers a copy
       */
      actionFormData: {
        type: Object,
        required: true
      },
      /**
       * The reactive class for controlling the settings of the modal from multiple components.
       */
      modalStore: {
        type: Object,
        required: true
      },
      /**
       * Indicates whether the action should be shown.
       */
      isActionVisible: {
        type: Boolean,
        required: true
      },
      /**
       * Allows disabling the action (if you want it visible, but not active).
       */
      isActionEnabled: {
        type: Boolean,
        default: false
      },
      /**
       * Allows setting custom tooltip text. The default is 'Copy {entityTypeTranslation}'.
       */
      tooltipTextOverride: {
        type: String,
        default: ""
      },
      /**
       * Custom tooltip text used when the action is disabled, great for explaining why it's disabled.
       */
      tooltipTextOverrideDisabled: {
        type: String,
        default: ""
      },
      /**
       * Optional property to provide any custom classes to the root of the action's anchor element
       */
      extraClasses: {
        type: [String, Array, Object],
        default: ""
      }
    },
    directives: {
      Tooltips
    },
    methods: {
      handleClick() {
        this.modalStore.showModal(this.actionFormData);
      }
    },
    computed: {
      getActionTooltip() {
        if (this.isActionEnabled && this.tooltipTextOverride.length) {
          return translateOrDefault(this.tooltipTextOverride);
        }
        if (!this.isActionEnabled && this.tooltipTextOverrideDisabled.length) {
          return translateOrDefault(this.tooltipTextOverrideDisabled);
        }
        return translate("CoreHome_CopyX", this.modalStore.getEntityTypeTranslation);
      }
    }
  });
  const _hoisted_1 = ["title", "aria-disabled"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
      class: vue.normalizeClass([
        {
          "entity-duplicator-action": true,
          "table-action": true,
          "icon-content-copy": true,
          "is-disabled": !_ctx.isActionEnabled
        },
        _ctx.extraClasses
      ]),
      title: _ctx.getActionTooltip,
      "aria-disabled": !_ctx.isActionEnabled,
      onClick: _cache[0] || (_cache[0] = ($event) => !_ctx.isActionEnabled || _ctx.handleClick())
    }, null, 10, _hoisted_1)), [
      [_directive_tooltips],
      [vue.vShow, _ctx.isActionVisible]
    ]);
  }
  const EntityDuplicatorAction = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class BaseDuplicatorAdapter {
    constructor(properties) {
      __publicField(this, "module");
      __publicField(this, "method");
      __publicField(this, "format");
      __publicField(this, "requiredFields");
      this.module = properties.module || "API";
      this.method = properties.method;
      this.format = properties.format || "json";
      this.requiredFields = properties.requiredFields || ["idSite", "idDestinationSites"];
    }
    validateFormFields(formValues) {
      return __async(this, null, function* () {
        const errorMessages = [];
        this.requiredFields.forEach((fieldName) => {
          if (!(fieldName in formValues) || !formValues[fieldName]) {
            errorMessages.push(translate("General_Required", fieldName));
          }
        });
        return new Promise((resolve2) => resolve2({
          errorMessages,
          isValid: errorMessages.length === 0
        }));
      });
    }
    prepareApiParams(formValues) {
      return __spreadValues({
        idSite: Matomo.idSite || instance$1.parsed.value.idSite,
        idDestinationSites: [formValues.idDestinationSite]
      }, formValues);
    }
    submitRequest(params) {
      return __async(this, null, function* () {
        this.module = params.module || this.module;
        this.method = params.method || this.method;
        this.format = params.format || this.format;
        const postParams = params;
        if (!this.method || this.method.length < 1) {
          throw new Error("The POST method cannot be empty!");
        }
        const ajax = new AjaxHelper();
        ajax.useCallbackInCaseOfError();
        ajax.setErrorCallback(null);
        ajax.removeDefaultParameter("date");
        ajax.removeDefaultParameter("period");
        ajax.removeDefaultParameter("segment");
        ajax.addParams({
          module: this.module,
          method: this.method,
          format: this.format
        }, "GET");
        ajax.addParams(postParams, "POST");
        ajax.setFormat(this.format);
        return ajax.send();
      });
    }
    onSuccess(response) {
      let onSuccessCallbackPromise = new Promise((resolve2) => resolve2());
      if (this.onSuccessCallback) {
        onSuccessCallbackPromise = this.onSuccessCallback(response);
      }
      onSuccessCallbackPromise.then(() => {
        setTimeout(() => {
          const notificationInstanceId = instance.show({
            message: response.message,
            context: response.success ? "success" : "error",
            type: "toast",
            id: "entityDuplicationResult"
          });
          instance.scrollToNotification(notificationInstanceId);
        });
      });
    }
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class EntityDuplicatorStore {
    /**
     * Protected so that the buildStoreInstance has to be used. This ensures that the modal store is
     * instantiated as a reactive object. See buildStoreInstance for more documentation.
     *
     * @param duplicateEntityTypeTranslation
     * @param adapterDefinition
     * @param commonFormData
     * @protected
     */
    constructor(duplicateEntityTypeTranslation, adapterDefinition, commonFormData) {
      __publicField(this, "state", vue.reactive({
        isModalVisible: false,
        commonFormData: {},
        entityFormData: {},
        entityTypeTranslation: ""
      }));
      /**
       * The adapter class defines the implementation/behaviour of common part of the duplication
       * process such as validation, gathering parameters, posting to the API, and handling success.
       */
      __publicField(this, "adapter");
      this.state.entityTypeTranslation = duplicateEntityTypeTranslation;
      this.adapter = "validateFormFields" in adapterDefinition ? adapterDefinition : new BaseDuplicatorAdapter(adapterDefinition);
      this.state.commonFormData = commonFormData != null ? commonFormData : {};
    }
    /**
     * Returns a reactive store object for the specific type of entity being copied so that it can be
     * used to maintain the state of the modal across all the actions which trigger showing the modal.
     * See the property descriptions of the EntityDuplicatorState interface for more information.
     *
     * @param duplicateEntityTypeTranslation Translation string or translated string of the item being
     * duplicated. E.g. goal, funnel, heatmap,...
     * @param adapterDefinition Either an instance of EntityDuplicatorAdapter or an object containing
     * the properties necessary to instantiate an instance of the default BaseDuplicatorAdapter. This
     * allows encapsulating the desired implementation of how the modal behaves such as validation
     * and posting the API request.
     * @param commonFormData Optional form data that's common to the type of entity being duplicated.
     * E.g. status to set for the new copies or something similar.
     */
    static buildStoreInstance(duplicateEntityTypeTranslation, adapterDefinition, commonFormData) {
      return vue.reactive(new EntityDuplicatorStore(
        duplicateEntityTypeTranslation,
        adapterDefinition,
        commonFormData
      ));
    }
    showModal(entityFormData) {
      this.resetFormData();
      Object.entries(entityFormData != null ? entityFormData : {}).forEach(([key, value]) => {
        this.state.entityFormData[key] = value;
      });
      this.state.isModalVisible = true;
    }
    hideModal() {
      this.state.isModalVisible = false;
      this.resetFormData();
    }
    resetFormData() {
      Object.keys(this.state.entityFormData).forEach((key) => {
        delete this.state.entityFormData[key];
      });
    }
    getFormValues(idDestinationSites) {
      const idDestinationSitesArray = Array.isArray(idDestinationSites) ? idDestinationSites : [];
      if (idDestinationSites && !Array.isArray(idDestinationSites)) {
        idDestinationSitesArray.push(idDestinationSites);
      }
      return __spreadValues(__spreadValues({
        idSite: Matomo.idSite || instance$1.parsed.value.idSite,
        idDestinationSites: idDestinationSitesArray
      }, this.state.commonFormData), this.state.entityFormData);
    }
    /**
     * Uses the entityTypeTranslation property to return the translated entity type (e.g.
     * goal, funnel, segment, ...), which can be a translated string or translation key. If the value
     * is a translation key, the translated value will be returned. If no value is set, the default is
     * the translation of 'report'.
     */
    get getEntityTypeTranslation() {
      let translationKey = "CoreHome_ReportLowercase";
      if (this.state.entityTypeTranslation) {
        translationKey = this.state.entityTypeTranslation;
      }
      return translateOrDefault(translationKey);
    }
  }
  exports2.ActivityIndicator = ActivityIndicator;
  exports2.AjaxForm = AjaxForm;
  exports2.AjaxHelper = AjaxHelper;
  exports2.Alert = Alert;
  exports2.AutoClearPassword = AutoClearPassword;
  exports2.BaseDuplicatorAdapter = BaseDuplicatorAdapter;
  exports2.ClientWidgetRenderer = ClientWidgetRenderer;
  exports2.Comparisons = Comparisons;
  exports2.ComparisonsStore = ComparisonsStore;
  exports2.ComparisonsStoreInstance = ComparisonsStoreInstance;
  exports2.ContentBlock = ContentBlock;
  exports2.ContentIntro = ContentIntro;
  exports2.ContentTable = ContentTable;
  exports2.CopyToClipboard = CopyToClipboard;
  exports2.DataTableActions = DataTableActions;
  exports2.DatePicker = DatePicker;
  exports2.DateRangePicker = DateRangePicker;
  exports2.Day = DayPeriod;
  exports2.DirectiveUtilities = DirectiveUtilities;
  exports2.DraggableList = _sfc_main$G;
  exports2.DropdownButton = DropdownButton;
  exports2.DropdownMenu = DropdownMenu;
  exports2.EnrichedHeadline = EnrichedHeadline;
  exports2.EntityDuplicatorAction = EntityDuplicatorAction;
  exports2.EntityDuplicatorModal = EntityDuplicatorModal;
  exports2.EntityDuplicatorStore = EntityDuplicatorStore;
  exports2.ExpandOnClick = ExpandOnClick;
  exports2.ExpandOnHover = ExpandOnHover;
  exports2.FieldArray = FieldArray;
  exports2.FocusAnywhereButHere = FocusAnywhereButHere;
  exports2.FocusIf = FocusIf;
  exports2.Matomo = Matomo;
  exports2.MatomoDialog = MatomoDialog;
  exports2.MatomoLoader = MatomoLoader;
  exports2.MatomoModal = MatomoModal;
  exports2.MatomoUrl = instance$1;
  exports2.MenuItemsDropdown = MenuItemsDropdown;
  exports2.MobileLeftMenu = MobileLeftMenu;
  exports2.Month = MonthPeriod;
  exports2.MultiPairField = MultiPairField;
  exports2.Notification = Notification;
  exports2.NotificationGroup = NotificationGroup;
  exports2.NotificationsStore = instance;
  exports2.NumberFormatter = NumberFormatter$1;
  exports2.Passthrough = Passthrough;
  exports2.PasswordStrength = PasswordStrength;
  exports2.PeriodDatePicker = PeriodDatePicker;
  exports2.PeriodSelector = PeriodSelector;
  exports2.Periods = Periods$1;
  exports2.Progressbar = Progressbar;
  exports2.QuickAccess = QuickAccess;
  exports2.Range = RangePeriod;
  exports2.ReportExport = ReportExport;
  exports2.ReportHeader = ReportHeader;
  exports2.ReportMetadataStore = ReportMetadataStoreInstance;
  exports2.ReportingMenu = ReportingMenu;
  exports2.ReportingMenuStore = ReportingMenuStoreInstance;
  exports2.ReportingPage = ReportingPage;
  exports2.ReportingPagesStore = ReportingPagesStoreInstance;
  exports2.SearchFiltersPersistenceStore = SearchFiltersPersistenceStoreInstance;
  exports2.SearchInput = SearchInput;
  exports2.SelectOnFocus = SelectOnFocus;
  exports2.ShowHelpLink = ShowHelpLink;
  exports2.ShowSensitiveData = ShowSensitiveData;
  exports2.SideNav = SideNav;
  exports2.SiteSelector = SiteSelector;
  exports2.SitesStore = SitesStore$1;
  exports2.Sparkline = Sparkline;
  exports2.Tooltips = Tooltips;
  exports2.VersionInfoHeaderMessage = VersionInfoHeaderMessage;
  exports2.VueEntryContainer = VueEntryContainer;
  exports2.Week = WeekPeriod;
  exports2.Widget = Widget;
  exports2.WidgetByDimensionContainer = WidgetByDimensionContainer;
  exports2.WidgetContainer = WidgetContainer;
  exports2.WidgetControlsDropdown = WidgetControlsDropdown;
  exports2.WidgetLoader = WidgetLoader;
  exports2.WidgetsStore = WidgetsStoreInstance;
  exports2.Year = YearPeriod;
  exports2.calculateAndFormatEvolution = calculateAndFormatEvolution;
  exports2.clone = clone;
  exports2.createVueApp = createVueApp;
  exports2.datesAreInTheSamePeriod = datesAreInTheSamePeriod;
  exports2.debounce = debounce;
  exports2.deleteCookie = deleteCookie;
  exports2.externalLink = externalLink;
  exports2.externalRawLink = externalRawLink;
  exports2.format = format;
  exports2.formatCurrency = formatCurrency;
  exports2.formatEvolution = formatEvolution;
  exports2.formatNumber = formatNumber;
  exports2.formatPercent = formatPercent;
  exports2.getCookie = getCookie;
  exports2.getToday = getToday;
  exports2.getWeekNumber = getWeekNumber;
  exports2.importPluginUmd = importPluginUmd;
  exports2.parseDate = parseDate;
  exports2.scrollToAnchorInUrl = scrollToAnchorInUrl;
  exports2.setCookie = setCookie;
  exports2.todayIsInRange = todayIsInRange;
  exports2.translate = translate;
  exports2.translateOrDefault = translateOrDefault;
  exports2.useExternalPluginComponent = useExternalPluginComponent;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
