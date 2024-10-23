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
__webpack_require__.d(__webpack_exports__, "getFormattedEvolution", function() { return /* reexport */ getFormattedEvolution; });
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
__webpack_require__.d(__webpack_exports__, "Passthrough", function() { return /* reexport */ Passthrough; });
__webpack_require__.d(__webpack_exports__, "DataTableActions", function() { return /* reexport */ DataTableActions; });
__webpack_require__.d(__webpack_exports__, "VersionInfoHeaderMessage", function() { return /* reexport */ VersionInfoHeaderMessage; });
__webpack_require__.d(__webpack_exports__, "MobileLeftMenu", function() { return /* reexport */ MobileLeftMenu; });
__webpack_require__.d(__webpack_exports__, "scrollToAnchorInUrl", function() { return /* reexport */ scrollToAnchorInUrl; });

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
/* harmony default export */ var Periods_Periods = (new Periods());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/utilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function format(date) {
  return $.datepicker.formatDate('yy-mm-dd', date);
}
function getToday() {
  const date = new Date(Date.now());
  // undo browser timezone
  date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000);
  // apply Matomo site timezone (if it exists)
  date.setHours(date.getHours() + (window.piwik.timezoneOffset || 0) / 3600);
  // get rid of hours/minutes/seconds/etc.
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
  if (strDate === '') {
    throw new Error('Invalid date, empty string.');
  }
  if (strDate === 'today' || strDate === 'now') {
    return getToday();
  }
  if (strDate === 'yesterday'
  // note: ignoring the 'same time' part since the frontend doesn't care about the time
  || strDate === 'yesterdaySameTime') {
    const yesterday = getToday();
    yesterday.setDate(yesterday.getDate() - 1);
    return yesterday;
  }
  if (strDate.match(/last[ -]?week/i)) {
    const lastWeek = getToday();
    lastWeek.setDate(lastWeek.getDate() - 7);
    return lastWeek;
  }
  if (strDate.match(/last[ -]?month/i)) {
    const lastMonth = getToday();
    lastMonth.setDate(1);
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    return lastMonth;
  }
  if (strDate.match(/last[ -]?year/i)) {
    const lastYear = getToday();
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
function getWeekNumber(date) {
  // Algorithm from https://www.w3resource.com/javascript-exercises/javascript-date-exercise-24.php
  // and updated based on http://www.java2s.com/example/nodejs/date/get-the-iso-week-date-week-number.html
  // for legibility
  // Create a copy of the date object
  const dt = new Date(date.valueOf());
  // ISO week date weeks start on Monday so correct the day number
  const dayNr = (date.getDay() + 6) % 7;
  // ISO 8601 states that week 1 is the week with the first thursday of that year.
  // Set the target date to the thursday in the target week
  dt.setDate(dt.getDate() - dayNr + 3);
  // Store the millisecond value of the target date
  const firstThursdayUTC = dt.valueOf();
  // Set the target to the first Thursday of the year
  // First set the target to january first
  dt.setMonth(0, 1);
  // Not a Thursday? Correct the date to the next Thursday
  if (dt.getDay() !== 4) {
    const daysToNextThursday = (4 - dt.getDay() + 7) % 7;
    dt.setMonth(0, 1 + daysToNextThursday);
  }
  // The week number is the number of weeks between the
  // first Thursday of the year and the Thursday in the target week
  return 1 + Math.ceil((firstThursdayUTC - dt.valueOf()) / (7 * 24 * 3600 * 1000 /* 1 week */));
}
// check whether two dates are in the same period, e.g. a week, a month or a year
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
    case 'day':
      return year1 === year2 && month1 === month2 && day1 === day2;
    case 'week':
      return year1 === year2 && week1 === week2;
    case 'month':
      return year1 === year2 && month1 === month2;
    case 'year':
      return year1 === year2;
    default:
      return false;
  }
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Day.ts
function Day_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class Day_DayPeriod {
  constructor(dateInPeriod) {
    Day_defineProperty(this, "dateInPeriod", void 0);
    this.dateInPeriod = dateInPeriod;
  }
  static parse(strDate) {
    return new Day_DayPeriod(parseDate(strDate));
  }
  static getDisplayText() {
    return translate('Intl_PeriodDay');
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
Periods_Periods.addCustomPeriod('day', Day_DayPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Week.ts
function Week_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class Week_WeekPeriod {
  constructor(dateInPeriod) {
    Week_defineProperty(this, "dateInPeriod", void 0);
    this.dateInPeriod = dateInPeriod;
  }
  static parse(strDate) {
    return new Week_WeekPeriod(parseDate(strDate));
  }
  static getDisplayText() {
    return translate('Intl_PeriodWeek');
  }
  getPrettyString() {
    const weekDates = this.getDateRange();
    const startWeek = format(weekDates[0]);
    const endWeek = format(weekDates[1]);
    return translate('General_DateRangeFromTo', [startWeek, endWeek]);
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
Periods_Periods.addCustomPeriod('week', Week_WeekPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Month.ts
function Month_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class Month_MonthPeriod {
  constructor(dateInPeriod) {
    Month_defineProperty(this, "dateInPeriod", void 0);
    this.dateInPeriod = dateInPeriod;
  }
  static parse(strDate) {
    return new Month_MonthPeriod(parseDate(strDate));
  }
  static getDisplayText() {
    return translate('Intl_PeriodMonth');
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
Periods_Periods.addCustomPeriod('month', Month_MonthPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Year.ts
function Year_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class Year_YearPeriod {
  constructor(dateInPeriod) {
    Year_defineProperty(this, "dateInPeriod", void 0);
    this.dateInPeriod = dateInPeriod;
  }
  static parse(strDate) {
    return new Year_YearPeriod(parseDate(strDate));
  }
  static getDisplayText() {
    return translate('Intl_PeriodYear');
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
Periods_Periods.addCustomPeriod('year', Year_YearPeriod);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Range.ts
function Range_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class Range_RangePeriod {
  constructor(startDate, endDate, childPeriodType) {
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
  static getLastNRange(childPeriodType, strAmount, strEndDate) {
    const nAmount = Math.max(parseInt(strAmount.toString(), 10) - 1, 0);
    if (Number.isNaN(nAmount)) {
      throw new Error('Invalid range strAmount');
    }
    let endDate = strEndDate ? parseDate(strEndDate) : getToday();
    let startDate = new Date(endDate.getTime());
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
      throw new Error(`Unknown period type '${childPeriodType}'.`);
    }
    if (childPeriodType !== 'day') {
      const startPeriod = Periods_Periods.periods[childPeriodType].parse(startDate);
      const endPeriod = Periods_Periods.periods[childPeriodType].parse(endDate);
      [startDate] = startPeriod.getDateRange();
      [, endDate] = endPeriod.getDateRange();
    }
    const firstWebsiteDate = new Date(1991, 7, 6);
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
    return new Range_RangePeriod(startDate, endDate, childPeriodType);
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
      throw new Error(`Unknown period type '${childPeriodType}'.`);
    }
    if (childPeriodType !== 'day') {
      const startPeriod = Periods_Periods.periods[childPeriodType].parse(startDate);
      const endPeriod = Periods_Periods.periods[childPeriodType].parse(endDate);
      [startDate] = startPeriod.getDateRange();
      [, endDate] = endPeriod.getDateRange();
    }
    const firstWebsiteDate = new Date(1991, 7, 6);
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
    return new Range_RangePeriod(startDate, endDate, childPeriodType);
  }
  static parse(strDate, childPeriodType = 'day') {
    if (/^previous/.test(strDate)) {
      const endDate = Range_RangePeriod.getLastNRange(childPeriodType, '2').startDate;
      return Range_RangePeriod.getLastNRange(childPeriodType, strDate.substring(8), endDate);
    }
    if (/^last/.test(strDate)) {
      return Range_RangePeriod.getLastNRange(childPeriodType, strDate.substring(4));
    }
    const parts = decodeURIComponent(strDate).split(',');
    return new Range_RangePeriod(parseDate(parts[0]), parseDate(parts[1]), childPeriodType);
  }
  static getDisplayText() {
    return translate('General_DateRangeInPeriodList');
  }
  getPrettyString() {
    const start = format(this.startDate);
    const end = format(this.endDate);
    return translate('General_DateRangeFromTo', [start, end]);
  }
  getDateRange() {
    return [this.startDate, this.endDate];
  }
  containsToday() {
    return todayIsInRange(this.getDateRange());
  }
  getDayCount() {
    return Math.ceil((this.endDate.getTime() - this.startDate.getTime()) / (1000 * 3600 * 24)) + 1;
  }
}
Periods_Periods.addCustomPeriod('range', Range_RangePeriod);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Matomo/Matomo.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

let originalTitle;
const {
  piwik,
  broadcast: Matomo_broadcast,
  piwikHelper: Matomo_piwikHelper
} = window;
piwik.helper = Matomo_piwikHelper;
piwik.broadcast = Matomo_broadcast;
piwik.updateDateInTitle = function updateDateInTitle(date, period) {
  if (!$('.top_controls #periodString').length) {
    return;
  }
  // Cache server-rendered page title
  originalTitle = originalTitle || document.title;
  if (originalTitle.indexOf(piwik.siteName) === 0) {
    const dateString = ` - ${Periods_Periods.parse(period, date).getPrettyString()} `;
    document.title = `${piwik.siteName}${dateString}${originalTitle.slice(piwik.siteName.length)}`;
  }
};
piwik.hasUserCapability = function hasUserCapability(capability) {
  return Array.isArray(piwik.userCapabilities) && piwik.userCapabilities.indexOf(capability) !== -1;
};
piwik.on = function addMatomoEventListener(eventName, listener) {
  function listenerWrapper(evt) {
    listener(...evt.detail); // eslint-disable-line
  }
  listener.wrapper = listenerWrapper;
  window.addEventListener(eventName, listenerWrapper);
};
piwik.off = function removeMatomoEventListener(eventName, listener) {
  if (listener.wrapper) {
    window.removeEventListener(eventName, listener.wrapper);
  }
};
piwik.postEvent = function postMatomoEvent(eventName, ...args // eslint-disable-line
) {
  const event = new CustomEvent(eventName, {
    detail: args
  });
  window.dispatchEvent(event);
};
const Matomo = piwik;
/* harmony default export */ var Matomo_Matomo = (Matomo);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */







// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoUrl/MatomoUrl.ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }
function MatomoUrl_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


 // important to load all periods here
const {
  piwik: MatomoUrl_piwik,
  broadcast: MatomoUrl_broadcast
} = window;
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
class MatomoUrl_MatomoUrl {
  constructor() {
    MatomoUrl_defineProperty(this, "url", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null));
    MatomoUrl_defineProperty(this, "urlQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.url.value ? this.url.value.search.replace(/^\?/, '') : ''));
    MatomoUrl_defineProperty(this, "hashQuery", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.url.value ? this.url.value.hash.replace(/^[#/?]+/, '') : ''));
    MatomoUrl_defineProperty(this, "urlParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.parse(this.urlQuery.value))));
    MatomoUrl_defineProperty(this, "hashParsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.parse(this.hashQuery.value))));
    MatomoUrl_defineProperty(this, "parsed", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_extends(_extends({}, this.urlParsed.value), this.hashParsed.value))));
    this.url.value = new URL(window.location.href);
    window.addEventListener('hashchange', event => {
      this.url.value = new URL(event.newURL);
      this.updatePeriodParamsFromUrl();
    });
    this.updatePeriodParamsFromUrl();
  }
  updateHashToUrl(urlWithoutLeadingHash) {
    const wholeHash = `#${urlWithoutLeadingHash}`;
    if (window.location.hash === wholeHash) {
      // trigger event manually since the url is the same
      window.dispatchEvent(new HashChangeEvent('hashchange', {
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
    const serializedParams = typeof params !== 'string' ? this.stringify(params) : params;
    const modifiedHashParams = Object.keys(hashParams).length ? this.getFinalHashParams(hashParams, params) : {};
    const serializedHashParams = this.stringify(modifiedHashParams);
    let url = `?${serializedParams}`;
    if (serializedHashParams.length) {
      url = `${url}#?${serializedHashParams}`;
    }
    window.broadcast.propagateNewPage('', undefined, undefined, undefined, url);
  }
  getFinalHashParams(params, urlParams = {}) {
    const paramsObj = typeof params !== 'string' ? params : this.parse(params);
    const urlParamsObj = typeof params !== 'string' ? urlParams : this.parse(urlParams);
    return _extends({
      // these params must always be present in the hash
      period: urlParamsObj.period || this.parsed.value.period,
      date: urlParamsObj.date || this.parsed.value.date,
      segment: urlParamsObj.segment || this.parsed.value.segment
    }, paramsObj);
  }
  // if we're in an embedded context, loads an entire new URL, otherwise updates the hash
  updateLocation(params) {
    if (Matomo_Matomo.helper.isReportingPage()) {
      this.updateHash(params);
      return;
    }
    this.updateUrl(params);
  }
  getSearchParam(paramName) {
    const hash = window.location.href.split('#');
    const regex = new RegExp(`${paramName}(\\[]|=)`);
    if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {
      const valueFromHash = window.broadcast.getValueFromHash(paramName, window.location.href);
      // for date, period and idsite fall back to parameter from url, if non in hash was provided
      if (valueFromHash || paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite') {
        return valueFromHash;
      }
    }
    return window.broadcast.getValueFromUrl(paramName, window.location.search);
  }
  parse(query) {
    return MatomoUrl_broadcast.getValuesFromUrl(`?${query}`, true);
  }
  stringify(search) {
    const searchWithoutEmpty = Object.fromEntries(Object.entries(search).filter(([, value]) => value !== '' && value !== null && value !== undefined));
    // using jQuery since URLSearchParams does not handle array params the way Matomo uses them
    return $.param(searchWithoutEmpty).replace(/%5B%5D/g, '[]')
    // some browsers treat URLs w/ date=a,b differently from date=a%2Cb, causing multiple
    // entries to show up in the browser history.
    .replace(/%2C/g, ',')
    // jquery seems to encode space characters as '+', but certain parts of matomo won't
    // decode it correctly, so we make sure to use %20 instead
    .replace(/\+/g, '%20');
  }
  updatePeriodParamsFromUrl() {
    let date = this.getSearchParam('date');
    const period = this.getSearchParam('period');
    if (!isValidPeriod(period, date)) {
      // invalid data in URL
      return;
    }
    if (MatomoUrl_piwik.period === period && MatomoUrl_piwik.currentDateString === date) {
      // this period / date is already loaded
      return;
    }
    MatomoUrl_piwik.period = period;
    const dateRange = Periods_Periods.parse(period, date).getDateRange();
    MatomoUrl_piwik.startDateString = format(dateRange[0]);
    MatomoUrl_piwik.endDateString = format(dateRange[1]);
    MatomoUrl_piwik.updateDateInTitle(date, period);
    // do not set anything to previousN/lastN, as it's more useful to plugins
    // to have the dates than previousN/lastN.
    if (MatomoUrl_piwik.period === 'range') {
      date = `${MatomoUrl_piwik.startDateString},${MatomoUrl_piwik.endDateString}`;
    }
    MatomoUrl_piwik.currentDateString = date;
  }
}
const instance = new MatomoUrl_MatomoUrl();
/* harmony default export */ var src_MatomoUrl_MatomoUrl = (instance);
MatomoUrl_piwik.updatePeriodParamsFromUrl = instance.updatePeriodParamsFromUrl.bind(instance);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts
function AjaxHelper_extends() { AjaxHelper_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return AjaxHelper_extends.apply(this, arguments); }
function AjaxHelper_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const {
  $: AjaxHelper_$
} = window;
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
  // cleanup ajax queue
  this.clean();
  // call original array push
  return Array.prototype.push.call(this, ...args);
};
window.globalAjaxQueue.abort = function globalAjaxQueueAbort() {
  // abort all queued requests if possible
  this.forEach(x => x && x.abort && x.abort());
  // remove all elements from array
  this.splice(0, this.length);
  this.active = 0;
};
/**
 * error callback to use by default
 */
function defaultErrorCallback(deferred, status) {
  // do not display error message if request was aborted
  if (status === 'abort' || !deferred || deferred.status === 0) {
    return;
  }
  if (typeof Piwik_Popover === 'undefined') {
    console.log(`Request failed: ${deferred.responseText}`); // mostly for tests
    return;
  }
  if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {
    AjaxHelper_$(document.body).html(piwikHelper.escape(deferred.responseText));
  } else {
    AjaxHelper_$('#loadingError').show();
  }
}
class ApiResponseError extends Error {}
/**
 * Global ajax helper to handle requests within Matomo
 */
class AjaxHelper_AjaxHelper {
  // helper method entry point
  static fetch(
  // eslint-disable-line
  params, options = {}) {
    const helper = new AjaxHelper_AjaxHelper();
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
      helper.setBulkRequests(...params);
    } else {
      Object.keys(params).forEach(key => {
        if (/password/i.test(key)) {
          throw new Error(`Password parameters are not allowed to be sent as GET parameter. Please send ${key} as POST parameter instead.`);
        }
      });
      helper.addParams(AjaxHelper_extends(AjaxHelper_extends({
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
      helper.headers = AjaxHelper_extends(AjaxHelper_extends({}, helper.headers), options.headers);
    }
    let createErrorNotification = true;
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
    return helper.send().then(result => {
      const data = result instanceof AjaxHelper_AjaxHelper ? result.requestHandle.responseJSON : result;
      // check for error if not using default notification behavior
      const results = helper.postParams.method === 'API.getBulkRequest' && Array.isArray(data) ? data : [data];
      const errors = results.filter(r => r.result === 'error').map(r => r.message);
      if (errors.length) {
        throw new ApiResponseError(errors.filter(e => e.length).join('\n'));
      }
      return result;
    }).catch(xhr => {
      if (createErrorNotification || xhr instanceof ApiResponseError) {
        throw xhr;
      }
      let message = 'Something went wrong';
      if (xhr.status === 504) {
        message = 'Request was possibly aborted';
      }
      if (xhr.status === 429) {
        message = 'Rate Limit was exceed';
      }
      throw new Error(message);
    });
  }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  static post(params,
  // eslint-disable-next-line
  postParams = {}, options = {}) {
    return AjaxHelper_AjaxHelper.fetch(params, AjaxHelper_extends(AjaxHelper_extends({}, options), {}, {
      postParams
    }));
  }
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  static oneAtATime(method, options) {
    let abortController = null;
    return (params, postParams) => {
      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();
      return AjaxHelper_AjaxHelper.post(AjaxHelper_extends(AjaxHelper_extends({}, params), {}, {
        method
      }), postParams, AjaxHelper_extends(AjaxHelper_extends({}, options), {}, {
        abortController
      })).finally(() => {
        abortController = null;
      });
    };
  }
  constructor() {
    /**
     * Format of response
     */
    AjaxHelper_defineProperty(this, "format", 'json');
    /**
     * A timeout for the request which will override any global timeout
     */
    AjaxHelper_defineProperty(this, "timeout", null);
    /**
     * Callback function to be executed on success
     */
    AjaxHelper_defineProperty(this, "callback", null);
    /**
     * Use this.callback if an error is returned
     */
    AjaxHelper_defineProperty(this, "useRegularCallbackInCaseOfError", false);
    /**
     * Callback function to be executed on error
     *
     * @deprecated use the jquery promise API
     */
    AjaxHelper_defineProperty(this, "errorCallback", void 0);
    AjaxHelper_defineProperty(this, "withToken", false);
    /**
     * Callback function to be executed on complete (after error or success)
     *
     * @deprecated use the jquery promise API
     */
    AjaxHelper_defineProperty(this, "completeCallback", void 0);
    /**
     * Params to be passed as GET params
     * @see ajaxHelper.mixinDefaultGetParams
     */
    AjaxHelper_defineProperty(this, "getParams", {});
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
    AjaxHelper_defineProperty(this, "getUrl", '?');
    /**
     * Params to be passed as GET params
     * @see ajaxHelper.mixinDefaultPostParams
     */
    AjaxHelper_defineProperty(this, "postParams", {});
    /**
     * Element to be displayed while loading
     */
    AjaxHelper_defineProperty(this, "loadingElement", null);
    /**
     * Element to be displayed on error
     */
    AjaxHelper_defineProperty(this, "errorElement", '#ajaxError');
    /**
     * Extra headers to add to the request.
     */
    AjaxHelper_defineProperty(this, "headers", {
      'X-Requested-With': 'XMLHttpRequest'
    });
    /**
     * Handle for current request
     */
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
  addParams(initialParams, type) {
    const params = typeof initialParams === 'string' ? window.broadcast.getValuesFromUrl(initialParams) : initialParams;
    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
    Object.keys(params).forEach(key => {
      let value = params[key];
      if (arrayParams.indexOf(key) !== -1 && !value) {
        return;
      }
      if (typeof value === 'boolean') {
        value = value ? 1 : 0;
      }
      if (type.toLowerCase() === 'get') {
        this.getParams[key] = value;
      } else if (type.toLowerCase() === 'post') {
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
    this.addParams(broadcast.getValuesFromUrl(url), 'GET');
  }
  /**
   * Gets this helper instance ready to send a bulk request. Each argument to this
   * function is a single request to use.
   */
  setBulkRequests(...urls) {
    const urlsProcessed = urls.map(u => typeof u === 'string' ? u : AjaxHelper_$.param(u));
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
  setFormat(format) {
    this.format = format;
  }
  /**
   * Set the div element to show while request is loading
   *
   * @param [element]  selector for the loading element
   */
  setLoadingElement(element) {
    this.loadingElement = element || '#ajaxLoadingDiv';
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
    if (AjaxHelper_$(this.errorElement).length) {
      AjaxHelper_$(this.errorElement).hide();
    }
    if (this.loadingElement) {
      AjaxHelper_$(this.loadingElement).fadeIn();
    }
    this.requestHandle = this.buildAjaxCall();
    window.globalAjaxQueue.push(this.requestHandle);
    if (this.abortController) {
      this.abortController.signal.addEventListener('abort', () => {
        if (this.requestHandle) {
          this.requestHandle.abort();
        }
      });
    }
    const result = new Promise((resolve, reject) => {
      this.requestHandle.then(data => {
        if (this.resolveWithHelper) {
          // NOTE: we can't resolve w/ the jquery xhr, because it's a promise, and will
          // just result in following the promise chain back to 'data'
          resolve(this); // casting hack here
        } else {
          resolve(data); // ignoring textStatus/jqXHR
        }
      }).fail(xhr => {
        if (xhr.status === 429) {
          console.log(`Warning: the '${AjaxHelper_$.param(this.getParams)}' request was rate limited!`);
          reject(xhr);
          return;
        }
        if (xhr.statusText === 'abort' || xhr.status === 0) {
          return;
        }
        console.log(`Warning: the ${AjaxHelper_$.param(this.getParams)} request failed!`);
        reject(xhr);
      });
    });
    return result;
  }
  /**
   * Aborts the current request if it is (still) running
   */
  abort() {
    if (this.requestHandle && typeof this.requestHandle.abort === 'function') {
      this.requestHandle.abort();
      this.requestHandle = null;
    }
  }
  /**
   * Builds and sends the ajax requests
   */
  buildAjaxCall() {
    const self = this;
    const parameters = this.mixinDefaultGetParams(this.getParams);
    let url = this.getUrl;
    if (url[url.length - 1] !== '?') {
      url += '&';
    }
    // we took care of encoding &segment properly already, so we don't use $.param for it ($.param
    // URL encodes the values)
    if (parameters.segment) {
      url = `${url}segment=${parameters.segment}&`;
      delete parameters.segment;
    }
    if (parameters.date) {
      url = `${url}date=${decodeURIComponent(parameters.date.toString())}&`;
      delete parameters.date;
    }
    url += AjaxHelper_$.param(parameters);
    const ajaxCall = {
      type: 'POST',
      async: true,
      url,
      dataType: this.format || 'json',
      complete: this.completeCallback,
      headers: this.headers ? this.headers : undefined,
      error: function errorCallback(...args) {
        window.globalAjaxQueue.active -= 1;
        if (self.errorCallback) {
          self.errorCallback.apply(this, args);
        }
      },
      success: (response, status, request) => {
        if (this.loadingElement) {
          AjaxHelper_$(this.loadingElement).hide();
        }
        const results = this.postParams.method === 'API.getBulkRequest' && Array.isArray(response) ? response : [response];
        const errors = results.filter(r => r.result === 'error').map(r => r.message).filter(e => e.length)
        // count occurrences of error messages
        .reduce((acc, e) => {
          acc[e] = (acc[e] || 0) + 1;
          return acc;
        }, {});
        if (errors && Object.keys(errors).length && !this.useRegularCallbackInCaseOfError) {
          let errorMessage = '';
          Object.keys(errors).forEach(error => {
            if (errorMessage.length) {
              errorMessage += '<br />';
            }
            // append error count if it occured more than once
            if (errors[error] > 1) {
              errorMessage += `${error} (${errors[error]}x)`;
            } else {
              errorMessage += error;
            }
          });
          let placeAt = null;
          let type = 'toast';
          if (AjaxHelper_$(this.errorElement).length && errorMessage.length) {
            AjaxHelper_$(this.errorElement).show();
            placeAt = this.errorElement;
            type = null;
          }
          const isLoggedIn = !document.querySelector('#login_form');
          if (errorMessage && isLoggedIn) {
            const UI = window['require']('piwik/UI'); // eslint-disable-line
            const notification = new UI.Notification();
            notification.show(errorMessage, {
              placeat: placeAt,
              context: 'error',
              type,
              id: 'ajaxHelper'
            });
            notification.scrollToNotification();
          }
        } else if (this.callback) {
          this.callback(response, status, request);
        }
        window.globalAjaxQueue.active -= 1;
        if (Matomo_Matomo.ajaxRequestFinished) {
          Matomo_Matomo.ajaxRequestFinished();
        }
      },
      data: this.mixinDefaultPostParams(this.postParams),
      timeout: this.timeout !== null ? this.timeout : undefined
    };
    return AjaxHelper_$.ajax(ajaxCall);
  }
  isRequestToApiMethod() {
    return this.getParams && this.getParams.module === 'API' && this.getParams.method || this.postParams && this.postParams.module === 'API' && this.postParams.method;
  }
  isWidgetizedRequest() {
    return broadcast.getValueFromUrl('module') === 'Widgetize';
  }
  getDefaultPostParams() {
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
  mixinDefaultPostParams(params) {
    const defaultParams = this.getDefaultPostParams();
    const mergedParams = AjaxHelper_extends(AjaxHelper_extends({}, defaultParams), params);
    return mergedParams;
  }
  /**
   * Mixin the default parameters to send as GET
   *
   * @param   params   parameter object
   */
  mixinDefaultGetParams(originalParams) {
    const segment = src_MatomoUrl_MatomoUrl.getSearchParam('segment');
    const defaultParams = {
      idSite: Matomo_Matomo.idSite ? Matomo_Matomo.idSite.toString() : broadcast.getValueFromUrl('idSite'),
      period: Matomo_Matomo.period || broadcast.getValueFromUrl('period'),
      segment
    };
    const params = originalParams;
    // never append token_auth to url
    if (params.token_auth) {
      params.token_auth = null;
      delete params.token_auth;
    }
    Object.keys(defaultParams).forEach(key => {
      if (this.useGETDefaultParameter(key) && (params[key] === null || typeof params[key] === 'undefined' || params[key] === '') && (this.postParams[key] === null || typeof this.postParams[key] === 'undefined' || this.postParams[key] === '') && defaultParams[key]) {
        params[key] = defaultParams[key];
      }
    });
    // handle default date & period if not already set
    if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
      params.date = Matomo_Matomo.currentDateString;
    }
    return params;
  }
  getRequestHandle() {
    return this.requestHandle;
  }
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts

window.ajaxHelper = AjaxHelper_AjaxHelper;
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PopoverHandler/PopoverHandler.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const {
  $: PopoverHandler_$
} = window;
class PopoverHandler_PopoverHandler {
  constructor() {
    this.setup();
  }
  setup() {
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.parsed.value.popover, () => this.onPopoverParamChanged());
    if (src_MatomoUrl_MatomoUrl.parsed.value.popover) {
      this.onPopoverParamChangedInitial();
    }
  }
  // don't initiate the handler until the page had a chance to render,
  // since some rowactions depend on what's been loaded.
  onPopoverParamChangedInitial() {
    PopoverHandler_$(() => {
      setTimeout(() => {
        this.openOrClose();
      });
    });
  }
  onPopoverParamChanged() {
    // make sure all popover handles were registered
    PopoverHandler_$(() => {
      this.openOrClose();
    });
  }
  openOrClose() {
    this.close();
    // should be rather done by routing
    const popoverParam = src_MatomoUrl_MatomoUrl.parsed.value.popover;
    if (popoverParam) {
      this.open(popoverParam);
    } else {
      // the URL should only be set to an empty popover if there are no popovers in the stack.
      // to avoid avoid any strange inconsistent states, we reset the popover stack here.
      window.broadcast.resetPopoverStack();
    }
  }
  close() {
    window.Piwik_Popover.close();
  }
  open(thePopoverParam) {
    // in case the $ was encoded (e.g. when using copy&paste on urls in some browsers)
    let popoverParam = decodeURIComponent(thePopoverParam);
    // revert special encoding from broadcast.propagateNewPopoverParameter()
    popoverParam = popoverParam.replace(/\$/g, '%');
    popoverParam = decodeURIComponent(popoverParam);
    const popoverParamParts = popoverParam.split(':');
    const handlerName = popoverParamParts[0];
    popoverParamParts.shift();
    const param = popoverParamParts.join(':');
    if (typeof window.broadcast.popoverHandlers[handlerName] !== 'undefined' && !window.broadcast.isLoginPage()) {
      window.broadcast.popoverHandlers[handlerName](param);
    }
  }
}
/* harmony default export */ var src_PopoverHandler_PopoverHandler = (new PopoverHandler_PopoverHandler());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/CookieHelper/CookieHelper.ts
/*
 * General utils for managing cookies in Typescript.
 */
// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
function setCookie(name, val, seconds) {
  const date = new Date();
  // set default day to 3 days
  if (!seconds) {
    // eslint-disable-next-line no-param-reassign
    seconds = 3 * 24 * 60 * 1000;
  }
  // Set it expire in n days
  date.setTime(date.getTime() + seconds);
  // Set it
  document.cookie = `${name}=${val}; expires=${date.toUTCString()}; path=/`;
}
// eslint-disable-next-line consistent-return,@typescript-eslint/explicit-module-boundary-types
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  // if cookie not exist return null
  // eslint-disable-next-line eqeqeq
  if (parts.length == 2) {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const data = parts.pop().split(';').shift();
    if (typeof data !== 'undefined') {
      return data;
    }
  }
  return null;
}
// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
function deleteCookie(name) {
  const date = new Date();
  // Set it expire in -1 days
  date.setTime(date.getTime() + -1 * 24 * 60 * 60 * 1000);
  // Set it
  document.cookie = `${name}=; expires=${date.toUTCString()}; path=/`;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/zenMode.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const {
  $: zenMode_$
} = window;
function handleZenMode() {
  let zenMode = !!parseInt(getCookie('zenMode'), 10);
  const iconSwitcher = zenMode_$('.top_controls .icon-arrowup');
  function updateZenMode() {
    if (zenMode) {
      zenMode_$('body').addClass('zenMode');
      iconSwitcher.addClass('icon-arrowdown').removeClass('icon-arrowup');
      iconSwitcher.prop('title', translate('CoreHome_ExitZenMode'));
    } else {
      zenMode_$('body').removeClass('zenMode');
      iconSwitcher.removeClass('icon-arrowdown').addClass('icon-arrowup');
      iconSwitcher.prop('title', translate('CoreHome_EnterZenMode'));
    }
  }
  Matomo_Matomo.helper.registerShortcut('z', translate('CoreHome_ShortcutZenMode'), event => {
    if (event.altKey) {
      return;
    }
    zenMode = !zenMode;
    setCookie('zenMode', zenMode ? '1' : '0');
    updateZenMode();
  });
  iconSwitcher.click(() => {
    window.Mousetrap.trigger('z');
  });
  updateZenMode();
}
Matomo_Matomo.on('Matomo.topControlsRendered', () => {
  handleZenMode();
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/externalLink.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Takes a raw URL and returns an HTML link tag for the URL, if the URL is for a matomo.org
 * domain then the URL will be modified to include campaign parameters
 *
 * @param url     URL to process
 * @param values  Optional [campaignOverride, sourceOverride, mediumOverride]
 * @return string
 */
function externalRawLink(url, ...values) {
  const pkArgs = values;
  if (!window._pk_externalRawLink) {
    // eslint-disable-line
    return url;
  }
  return window._pk_externalRawLink(url, pkArgs); // eslint-disable-line
}
/**
 * Takes a raw URL and returns an HTML link tag for the URL, if the URL is for a matomo.org
 * domain then the URL will be modified to include campaign parameters
 *
 * @param url              URL to process
 * @param values  Optional [campaignOverride, sourceOverride, mediumOverride]
 * @return string
 */
function externalLink(url, ...values) {
  if (!url) {
    return '';
  }
  const campaignOverride = values.length > 0 && values[0] ? values[0] : null;
  const sourceOverride = values.length > 1 && values[1] ? values[1] : null;
  const mediumOverride = values.length > 2 && values[2] ? values[2] : null;
  const returnUrl = externalRawLink(url, campaignOverride, sourceOverride, mediumOverride);
  /* eslint-disable prefer-template */
  return '<a target="_blank" rel="noreferrer noopener" href="' + returnUrl + '">';
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/createVueApp.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



function createVueApp(...args) {
  const app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])(...args);
  app.config.globalProperties.$sanitize = window.vueSanitize;
  app.config.globalProperties.translate = translate;
  app.config.globalProperties.translateOrDefault = translateOrDefault;
  app.config.globalProperties.externalLink = externalLink;
  app.config.globalProperties.externalRawLink = externalRawLink;
  return app;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/importPluginUmd.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/* eslint-disable @typescript-eslint/no-explicit-any */
const pluginLoadingPromises = {};
const PLUGIN_LOAD_TIMEOUT = 120;
const POLL_INTERVAL = 50;
const POLL_LIMIT = 1000;
// code based off webpack's generated code for import()
// currently does not load styles on demand
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
  const script = document.createElement('script');
  script.charset = 'utf-8';
  script.timeout = PLUGIN_LOAD_TIMEOUT;
  script.src = pluginUmdPath;
  let timeout;
  // create error before stack unwound to get useful stacktrace later
  const error = new Error();
  const onScriptComplete = event => {
    // avoid mem leaks in IE.
    script.onerror = null;
    script.onload = null;
    clearTimeout(timeout);
    // the script may not load entirely at the time onload is called, so we poll for a small
    // amount of time until the window.PluginName object appears
    let pollProgress = 0;
    function checkPluginInWindow() {
      pollProgress += POLL_INTERVAL;
      // promise was already handled
      if (!promiseReject || !promiseResolve) {
        return;
      }
      // promise was not resolved, and window object exists
      if (window[plugin] && promiseResolve) {
        try {
          promiseResolve(window[plugin]);
        } finally {
          promiseReject = undefined;
          promiseResolve = undefined;
        }
        return;
      }
      // script took too long to execute or failed to execute, and no plugin object appeared in
      // window, so we report an error
      if (pollProgress > POLL_LIMIT) {
        try {
          const errorType = event && (event.type === 'load' ? 'missing' : event.type);
          const realSrc = event && event.target && event.target.src;
          error.message = `Loading plugin ${plugin} on demand failed.\n(${errorType}: ${realSrc})`;
          error.name = 'PluginOnDemandLoadError';
          error.type = errorType;
          error.request = realSrc;
          promiseReject(error);
        } finally {
          promiseReject = undefined;
          promiseResolve = undefined;
        }
        return;
      }
      setTimeout(checkPluginInWindow, POLL_INTERVAL);
    }
    setTimeout(checkPluginInWindow, POLL_INTERVAL);
  };
  timeout = setTimeout(() => {
    onScriptComplete({
      type: 'timeout',
      target: script
    });
  }, PLUGIN_LOAD_TIMEOUT);
  script.onerror = onScriptComplete;
  script.onload = onScriptComplete;
  document.head.appendChild(script);
  return new Promise((resolve, reject) => {
    promiseResolve = resolve;
    promiseReject = reject;
  });
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/useExternalPluginComponent.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/ban-ts-comment */


function useExternalPluginComponent(plugin, component) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineAsyncComponent"])(() => importPluginUmd(plugin).then(module => {
    if (!module) {
      // @ts-ignore
      resolve(null); // plugin not loaded
    }
    return module[component];
  }));
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/directiveUtilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function getRef(expander, binding) {
  var _binding$instance;
  return expander instanceof HTMLElement ? expander : (_binding$instance = binding.instance) === null || _binding$instance === void 0 ? void 0 : _binding$instance.$refs[expander];
}
/* harmony default export */ var directiveUtilities = ({
  getRef
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/debounce.ts
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/getFormattedEvolution.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function calculateEvolution(currentValue, pastValue) {
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
  return evolution;
}
function formatEvolution(evolution) {
  return `${evolution > 0 ? Matomo_Matomo.numbers.symbolPlus : ''}${Math.round(evolution)}}%`;
}
function getFormattedEvolution(currentValue, pastValue) {
  const evolution = calculateEvolution(currentValue, pastValue);
  return formatEvolution(evolution);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/clone.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function clone(p) {
  if (typeof p === 'undefined') {
    return p;
  }
  return JSON.parse(JSON.stringify(p));
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/VueEntryContainer/VueEntryContainer.vue?vue&type=template&id=6cb9164b

const _hoisted_1 = {
  ref: "root"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_ctx.componentWrapper ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.componentWrapper), {
    key: 0
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VueEntryContainer/VueEntryContainer.vue?vue&type=template&id=6cb9164b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/VueEntryContainer/VueEntryContainer.vue?vue&type=script&lang=ts


/* harmony default export */ var VueEntryContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    html: String
  },
  mounted() {
    Matomo_Matomo.helper.compileVueEntryComponents(this.$refs.root);
  },
  beforeUnmount() {
    Matomo_Matomo.helper.destroyVueComponent(this.$refs.root);
  },
  computed: {
    componentWrapper() {
      if (!this.html) {
        return null;
      }
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])({
        template: this.html
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VueEntryContainer/VueEntryContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VueEntryContainer/VueEntryContainer.vue



VueEntryContainervue_type_script_lang_ts.render = render

/* harmony default export */ var VueEntryContainer = (VueEntryContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=3f6d8e16

const ActivityIndicatorvue_type_template_id_3f6d8e16_hoisted_1 = {
  class: "loadingPiwik"
};
function ActivityIndicatorvue_type_template_id_3f6d8e16_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ActivityIndicatorvue_type_template_id_3f6d8e16_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loading]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=3f6d8e16

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MatomoLoader/MatomoLoader.vue?vue&type=template&id=74f456c7

const MatomoLoadervue_type_template_id_74f456c7_hoisted_1 = {
  class: "matomo-loader"
};
const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const _hoisted_5 = [_hoisted_2, _hoisted_3, _hoisted_4];
function MatomoLoadervue_type_template_id_74f456c7_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", MatomoLoadervue_type_template_id_74f456c7_hoisted_1, _hoisted_5);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoLoader/MatomoLoader.vue?vue&type=template&id=74f456c7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MatomoLoader/MatomoLoader.vue?vue&type=script&lang=ts

/* harmony default export */ var MatomoLoadervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoLoader/MatomoLoader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoLoader/MatomoLoader.vue



MatomoLoadervue_type_script_lang_ts.render = MatomoLoadervue_type_template_id_74f456c7_render

/* harmony default export */ var MatomoLoader = (MatomoLoadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts



/* harmony default export */ var ActivityIndicatorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoLoader: MatomoLoader
  },
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



ActivityIndicatorvue_type_script_lang_ts.render = ActivityIndicatorvue_type_template_id_3f6d8e16_render

/* harmony default export */ var ActivityIndicator = (ActivityIndicatorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=32dc7f1c

function Alertvue_type_template_id_32dc7f1c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert", {
      [`alert-${_ctx.severity}`]: true
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=32dc7f1c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts

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



Alertvue_type_script_lang_ts.render = Alertvue_type_template_id_32dc7f1c_render

/* harmony default export */ var Alert = (Alertvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownMenu/DropdownMenu.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
  mounted(element, binding) {
    let options = {};
    $(element).addClass('matomo-dropdown-menu');
    const isSubmenu = !!$(element).parent().closest('.dropdown-content').length;
    if (isSubmenu) {
      var _binding$value;
      options = {
        hover: true
      };
      $(element).addClass('submenu');
      $(((_binding$value = binding.value) === null || _binding$value === void 0 ? void 0 : _binding$value.activates) || $(element).data('target')).addClass('submenu-dropdown-content');
      // if a submenu is used, the dropdown will never scroll
      $(element).parents('.dropdown-content').addClass('submenu-container');
    }
    $(element).dropdown(options);
  },
  updated(element) {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
      $(element).addClass('matomo-dropdown-menu');
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusAnywhereButHere/FocusAnywhereButHere.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function onClickOutsideElement(element, binding, event) {
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
function onScroll(element, binding) {
  binding.value.hasScrolled = true;
}
function onMouseDown(element, binding) {
  binding.value.isMouseDown = true;
  binding.value.hasScrolled = false;
}
function onEscapeHandler(element, binding, event) {
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
const doc = document.documentElement;
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
  mounted(el, binding) {
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
  unmounted(el, binding) {
    doc.removeEventListener('keyup', binding.value.onEscapeHandler);
    doc.removeEventListener('mousedown', binding.value.onMouseDown);
    doc.removeEventListener('mouseup', binding.value.onClickOutsideElement);
    doc.removeEventListener('scroll', binding.value.onScroll);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FocusIf/FocusIf.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function doFocusIf(el, binding) {
  var _binding$value, _binding$oldValue;
  if ((_binding$value = binding.value) !== null && _binding$value !== void 0 && _binding$value.focused && !((_binding$oldValue = binding.oldValue) !== null && _binding$oldValue !== void 0 && _binding$oldValue.focused)) {
    setTimeout(() => {
      el.focus();
      if (binding.value.afterFocus) {
        binding.value.afterFocus();
      }
    }, 5);
  }
}
/* harmony default export */ var FocusIf = ({
  mounted(el, binding) {
    doFocusIf(el, binding);
  },
  updated(el, binding) {
    doFocusIf(el, binding);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Tooltips/Tooltips.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $: Tooltips_$
} = window;
function defaultContentTransform() {
  const title = Tooltips_$(this).attr('title') || '';
  return window.vueSanitize(title.replace(/\n/g, '<br />'));
}
function setupTooltips(el, binding) {
  var _binding$value, _binding$value2, _binding$value3, _binding$value4, _binding$value5, _binding$value6;
  Tooltips_$(el).tooltip({
    track: true,
    content: ((_binding$value = binding.value) === null || _binding$value === void 0 ? void 0 : _binding$value.content) || defaultContentTransform,
    show: typeof ((_binding$value2 = binding.value) === null || _binding$value2 === void 0 ? void 0 : _binding$value2.show) !== 'undefined' ? (_binding$value3 = binding.value) === null || _binding$value3 === void 0 ? void 0 : _binding$value3.show : {
      delay: ((_binding$value4 = binding.value) === null || _binding$value4 === void 0 ? void 0 : _binding$value4.delay) || 700,
      duration: ((_binding$value5 = binding.value) === null || _binding$value5 === void 0 ? void 0 : _binding$value5.duration) || 200
    },
    hide: false,
    tooltipClass: (_binding$value6 = binding.value) === null || _binding$value6 === void 0 ? void 0 : _binding$value6.tooltipClass
  });
}
/* harmony default export */ var Tooltips = ({
  mounted(el, binding) {
    setTimeout(() => setupTooltips(el, binding));
  },
  updated(el, binding) {
    setTimeout(() => setupTooltips(el, binding));
  },
  beforeUnmount(el) {
    try {
      window.$(el).tooltip('destroy');
    } catch (e) {
      // ignore
    }
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=8c3d22f8

const MatomoDialogvue_type_template_id_8c3d22f8_hoisted_1 = {
  ref: "root"
};
function MatomoDialogvue_type_template_id_8c3d22f8_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MatomoDialogvue_type_template_id_8c3d22f8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.modelValue]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=8c3d22f8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=script&lang=ts


/* harmony default export */ var MatomoDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /**
     * Whether the modal is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true
    }
  },
  emits: ['yes', 'no', 'closeEnd', 'close', 'validation', 'update:modelValue'],
  activated() {
    this.$emit('update:modelValue', false);
  },
  watch: {
    modelValue(newValue, oldValue) {
      if (newValue) {
        const slotElement = this.$refs.root.firstElementChild;
        Matomo_Matomo.helper.modalConfirm(slotElement, {
          yes: () => {
            this.$emit('yes');
          },
          no: () => {
            this.$emit('no');
          },
          validation: () => {
            this.$emit('validation');
          }
        }, {
          onCloseEnd: () => {
            // materialize removes the child element, so we move it back to the slot
            this.$refs.root.appendChild(slotElement);
            this.$emit('update:modelValue', false);
            this.$emit('closeEnd');
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



MatomoDialogvue_type_script_lang_ts.render = MatomoDialogvue_type_template_id_8c3d22f8_render

/* harmony default export */ var MatomoDialog = (MatomoDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnClick/ExpandOnClick.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function onExpand(element) {
  element.classList.toggle('expanded');
  const positionElement = element.querySelector('.dropdown.positionInViewport');
  if (positionElement) {
    Matomo_Matomo.helper.setMarginLeftToBeInViewport(positionElement);
  }
}
function ExpandOnClick_onClickOutsideElement(element, binding, event) {
  const hadUsedScrollbar = binding.value.isMouseDown && binding.value.hasScrolled;
  binding.value.isMouseDown = false;
  binding.value.hasScrolled = false;
  if (hadUsedScrollbar) {
    return;
  }
  if (!element.contains(event.target)) {
    var _binding$value;
    element.classList.remove('expanded');
    if ((_binding$value = binding.value) !== null && _binding$value !== void 0 && _binding$value.onClosed) {
      binding.value.onClosed();
    }
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
const ExpandOnClick_doc = document.documentElement;
const {
  $: ExpandOnClick_$
} = window;
/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
/* harmony default export */ var ExpandOnClick = ({
  mounted(el, binding) {
    binding.value.isMouseDown = false;
    binding.value.hasScrolled = false;
    binding.value.onExpand = onExpand.bind(null, el);
    binding.value.onEscapeHandler = ExpandOnClick_onEscapeHandler.bind(null, el, binding);
    binding.value.onMouseDown = ExpandOnClick_onMouseDown.bind(null, binding);
    binding.value.onClickOutsideElement = ExpandOnClick_onClickOutsideElement.bind(null, el, binding);
    binding.value.onScroll = ExpandOnClick_onScroll.bind(null, binding);
    setTimeout(() => {
      const expander = directiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        ExpandOnClick_$(expander).on('click', binding.value.onExpand);
      }
    });
    ExpandOnClick_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnClick_doc.addEventListener('mousedown', binding.value.onMouseDown);
    ExpandOnClick_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
    ExpandOnClick_doc.addEventListener('scroll', binding.value.onScroll);
  },
  unmounted(el, binding) {
    const expander = directiveUtilities.getRef(binding.value.expander, binding);
    if (expander) {
      ExpandOnClick_$(expander).off('click', binding.value.onExpand);
    }
    ExpandOnClick_doc.removeEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnClick_doc.removeEventListener('mousedown', binding.value.onMouseDown);
    ExpandOnClick_doc.removeEventListener('mouseup', binding.value.onClickOutsideElement);
    ExpandOnClick_doc.removeEventListener('scroll', binding.value.onScroll);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ExpandOnHover/ExpandOnHover.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function onMouseEnter(element) {
  element.classList.add('expanded');
  const positionElement = element.querySelector('.dropdown.positionInViewport');
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
const ExpandOnHover_doc = document.documentElement;
/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnHover: ExpandOnHover(), // function call is important since we store state
 *                                   // in this directive
 * }
 */
/* harmony default export */ var ExpandOnHover = ({
  mounted(el, binding) {
    binding.value.onMouseEnter = onMouseEnter.bind(null, el);
    binding.value.onMouseLeave = onMouseLeave.bind(null, el);
    binding.value.onClickOutsideElement = ExpandOnHover_onClickOutsideElement.bind(null, el);
    binding.value.onEscapeHandler = ExpandOnHover_onEscapeHandler.bind(null, el);
    setTimeout(() => {
      const expander = directiveUtilities.getRef(binding.value.expander, binding);
      if (expander) {
        expander.addEventListener('mouseenter', binding.value.onMouseEnter);
      }
    });
    el.addEventListener('mouseleave', binding.value.onMouseLeave);
    ExpandOnHover_doc.addEventListener('keyup', binding.value.onEscapeHandler);
    ExpandOnHover_doc.addEventListener('mouseup', binding.value.onClickOutsideElement);
  },
  unmounted(el, binding) {
    const expander = directiveUtilities.getRef(binding.value.expander, binding);
    if (expander) {
      expander.removeEventListener('mouseenter', binding.value.onMouseEnter);
    }
    el.removeEventListener('mouseleave', binding.value.onMouseLeave);
    document.removeEventListener('keyup', binding.value.onEscapeHandler);
    document.removeEventListener('mouseup', binding.value.onClickOutsideElement);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowSensitiveData/ShowSensitiveData.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const {
  $: ShowSensitiveData_$
} = window;
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
  mounted(el, binding) {
    const element = ShowSensitiveData_$(el);
    const {
      sensitiveData
    } = binding.value;
    const showCharacters = binding.value.showCharacters || 6;
    const clickElement = binding.value.clickElementSelector || element;
    let protectedData = '';
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DropdownButton/DropdownButton.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $: DropdownButton_$
} = window;
/* harmony default export */ var DropdownButton = ({
  mounted(el) {
    const element = DropdownButton_$(el);
    // BC for materializecss 0.97 => 1.0
    if (!element.attr('data-target') && element.attr('data-activates')) {
      element.attr('data-target', element.attr('data-activates'));
    }
    const target = element.attr('data-target');
    if (target && DropdownButton_$(`#${target}`).length) {
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SelectOnFocus/SelectOnFocus.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $: SelectOnFocus_$
} = window;
function onFocusHandler(binding, event) {
  if (binding.value.focusedElement !== event.target) {
    binding.value.focusedElement = event.target;
    SelectOnFocus_$(event.target).select();
  }
}
function SelectOnFocus_onClickHandler(event) {
  // .select() + focus and blur seems to not work on pre elements
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
/* harmony default export */ var SelectOnFocus = ({
  mounted(el, binding) {
    const tagName = el.tagName.toLowerCase();
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
  unmounted(el, binding) {
    if (binding.value.elementSupportsSelect) {
      el.removeEventListener('focus', binding.value.onFocusHandler);
      el.removeEventListener('blur', binding.value.onBlurHandler);
    } else {
      el.removeEventListener('click', binding.value.onClickHandler);
    }
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/CopyToClipboard/CopyToClipboard.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function CopyToClipboard_onClickHandler(pre) {
  if (pre) {
    const textarea = document.createElement('textarea');
    textarea.value = pre.innerText;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.focus();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    const btn = pre.parentElement;
    if (btn) {
      const icon = btn.getElementsByTagName('i')[0];
      if (icon) {
        icon.classList.remove('copyToClipboardIcon');
        icon.classList.add('copyToClipboardIconCheck');
      }
      const copied = btn.getElementsByClassName('copyToClipboardCopiedDiv')[0];
      if (copied) {
        copied.style.display = 'inline-block';
        setTimeout(() => {
          copied.style.display = 'none';
        }, 2500);
      }
    }
  }
}
function onTransitionEndHandler(el, binding) {
  if (binding.value.transitionOpen) {
    const btn = el.parentElement;
    if (btn) {
      const icon = btn.getElementsByTagName('i')[0];
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
  mounted(el, binding) {
    const tagName = el.tagName.toLowerCase();
    if (tagName === 'pre') {
      const btn = document.createElement('button');
      btn.setAttribute('type', 'button');
      btn.className = 'copyToClipboardButton';
      const positionDiv = document.createElement('div');
      positionDiv.className = 'copyToClipboardPositionDiv';
      const icon = document.createElement('i');
      icon.className = 'copyToClipboardIcon';
      btn.appendChild(icon);
      const sp = document.createElement('span');
      sp.className = 'copyToClipboardSpan';
      sp.innerHTML = translate('General_Copy');
      btn.appendChild(sp);
      positionDiv.appendChild(btn);
      const cdiv = document.createElement('div');
      cdiv.className = 'copyToClipboardCopiedDiv';
      cdiv.innerHTML = translate('General_CopiedToClipboard');
      positionDiv.appendChild(cdiv);
      const pe = el.parentElement;
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
  unmounted(el, binding) {
    el.removeEventListener('click', binding.value.onClickHandler);
    el.removeEventListener('transitionend', binding.value.onTransitionEndHandler);
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SideNav/SideNav.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
  mounted(el, binding) {
    if (!binding.value.activator) {
      return;
    }
    setTimeout(() => {
      if (!binding.value.initialized) {
        binding.value.initialized = true;
        const sideNavActivator = directiveUtilities.getRef(binding.value.activator, binding);
        if (sideNavActivator) {
          window.$(sideNavActivator).show();
          const targetSelector = sideNavActivator.getAttribute('data-target');
          // @ts-ignore
          window.$(`#${targetSelector}`).sidenav({
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=5e16ac38

const EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_1 = {
  key: 0,
  class: "title",
  tabindex: "6"
};
const EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_2 = ["href", "title"];
const EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_3 = {
  class: "iconsBar"
};
const EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_4 = ["href", "title"];
const EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const _hoisted_6 = [EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_5];
const _hoisted_7 = ["title"];
const _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);
const _hoisted_9 = [_hoisted_8];
const _hoisted_10 = {
  key: 2,
  class: "ratingIcons"
};
const _hoisted_11 = {
  class: "inlineHelp"
};
const _hoisted_12 = ["innerHTML"];
const _hoisted_13 = ["innerHTML"];
const _hoisted_14 = ["href"];
function EnrichedHeadlinevue_type_template_id_5e16ac38_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "enrichedHeadline",
    onMouseenter: _cache[1] || (_cache[1] = $event => _ctx.showIcons = true),
    onMouseleave: _cache[2] || (_cache[2] = $event => _ctx.showIcons = false),
    ref: "root"
  }, [!_ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "title",
    href: _ctx.editUrl,
    title: _ctx.translate('CoreHome_ClickToEditX', _ctx.htmlEntities(_ctx.actualFeatureName))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 8, EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_3, [_ctx.helpUrl && !_ctx.actualInlineHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    target: "_blank",
    class: "helpIcon",
    href: _ctx.helpUrl,
    title: _ctx.translate('CoreHome_ExternalHelp')
  }, _hoisted_6, 8, EnrichedHeadlinevue_type_template_id_5e16ac38_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualInlineHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showInlineHelp = !_ctx.showInlineHelp),
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      'active': _ctx.showInlineHelp
    }]),
    title: _ctx.translate(_ctx.reportGenerated ? 'General_HelpReport' : 'General_Help')
  }, _hoisted_9, 10, _hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showRateFeature ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_10, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.rateFeature), {
    title: _ctx.actualFeatureName
  }, null, 8, ["title"]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showIcons || _ctx.showInlineHelp]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=5e16ac38

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts





/**
 * Usage:
 *
 * <h2><EnrichedHeadline>All Websites Dashboard</EnrichedHeadline></h2>
 * -> uses "All Websites Dashboard" as featurename
 *
 * <h2><EnrichedHeadline feature-name="All Websites Dashboard">All Websites Dashboard (Total:
 * 309 Visits)</EnrichedHeadline></h2>
 * -> custom featurename
 *
 * <h2><EnrichedHeadline help-url="http://piwik.org/guide">All Websites Dashboard</EnrichedHeadline></h2>
 * -> shows help icon and links to external url
 *
 * <h2><EnrichedHeadline edit-url="index.php?module=Foo&action=bar&id=4">All Websites
 * Dashboard</EnrichedHeadline></h2>
 * -> makes the headline clickable linking to the specified url
 *
 * <h2><EnrichedHeadline inline-help="inlineHelp">Pages report</EnrichedHeadline></h2>
 * -> inlineHelp specified via a attribute shows help icon on headline hover
 *
 * <h2><EnrichedHeadline>All Websites Dashboard
 *     <div class="inlineHelp">My <strong>inline help</strong></div>
 * </EnrichedHeadline></h2>
 * -> alternative definition for inline help
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 *
 * * <h2><EnrichedHeadline report-generated="generated time">Pages report</EnrichedHeadline></h2>
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
    const root = this.$refs.root;
    if (!this.actualInlineHelp) {
      var _root$parentElement;
      let helpNode = root.querySelector('.title .inlineHelp');
      if (!helpNode && (_root$parentElement = root.parentElement) !== null && _root$parentElement !== void 0 && _root$parentElement.nextElementSibling) {
        // hack for reports :(
        helpNode = root.parentElement.nextElementSibling.querySelector('.reportDocumentation');
      }
      if (helpNode) {
        var _helpNode$getAttribut;
        // hackish solution to get binded html of p tag within the help node
        // at this point the ng-bind-html is not yet converted into html when report is not
        // initially loaded. Using $compile doesn't work. So get and set it manually
        const helpDocs = (_helpNode$getAttribut = helpNode.getAttribute('data-content')) === null || _helpNode$getAttribut === void 0 ? void 0 : _helpNode$getAttribut.trim();
        if (helpDocs && helpDocs.length) {
          this.actualInlineHelp = `<p>${helpDocs}</p>`;
          setTimeout(() => helpNode.remove(), 0);
        }
      }
    }
    if (!this.actualFeatureName) {
      var _root$querySelector;
      this.actualFeatureName = (_root$querySelector = root.querySelector('.title')) === null || _root$querySelector === void 0 ? void 0 : _root$querySelector.textContent;
    }
    if (Matomo_Matomo.period && Matomo_Matomo.currentDateString) {
      const currentPeriod = Periods_Periods.parse(Matomo_Matomo.period, Matomo_Matomo.currentDateString);
      if (this.reportGenerated && currentPeriod.containsToday()) {
        window.$(root.querySelector('.report-generated')).tooltip({
          track: true,
          content: this.reportGenerated,
          items: 'div',
          show: false,
          hide: false
        });
      }
    }
  },
  methods: {
    htmlEntities(v) {
      return Matomo_Matomo.helper.htmlEntities(v);
    }
  },
  computed: {
    showRateFeature() {
      return translateOrDefault('Feedback_SendFeedback') !== 'Feedback_SendFeedback';
    },
    rateFeature() {
      if (this.showRateFeature) {
        return useExternalPluginComponent('Feedback', 'RateFeature');
      }
      return '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue



EnrichedHeadlinevue_type_script_lang_ts.render = EnrichedHeadlinevue_type_template_id_5e16ac38_render

/* harmony default export */ var EnrichedHeadline = (EnrichedHeadlinevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=3f63a484

const ContentBlockvue_type_template_id_3f63a484_hoisted_1 = {
  class: "card-content"
};
const ContentBlockvue_type_template_id_3f63a484_hoisted_2 = {
  key: 0,
  class: "card-title"
};
const ContentBlockvue_type_template_id_3f63a484_hoisted_3 = {
  key: 1,
  class: "card-title"
};
const ContentBlockvue_type_template_id_3f63a484_hoisted_4 = {
  ref: "content"
};
const ContentBlockvue_type_template_id_3f63a484_hoisted_5 = {
  key: 0,
  class: "card-image hide-on-med-and-down"
};
const ContentBlockvue_type_template_id_3f63a484_hoisted_6 = ["src", "alt"];
function ContentBlockvue_type_template_id_3f63a484_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      card: true,
      'card-with-image': !!this.imageUrl
    }),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_3f63a484_hoisted_1, [_ctx.contentTitle && !_ctx.actualFeature && !_ctx.helpUrl && !_ctx.actualHelpText ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_3f63a484_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(_ctx.contentTitle)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.contentTitle && (_ctx.actualFeature || _ctx.helpUrl || _ctx.actualHelpText) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_3f63a484_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.actualFeature,
    "help-url": _ctx.helpUrl,
    "inline-help": _ctx.actualHelpText
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(_ctx.contentTitle)), 1)]),
    _: 1
  }, 8, ["feature-name", "help-url", "inline-help"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_3f63a484_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)]), _ctx.imageUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ContentBlockvue_type_template_id_3f63a484_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: _ctx.imageUrl,
    alt: _ctx.actualImageAltText
  }, null, 8, ContentBlockvue_type_template_id_3f63a484_hoisted_6)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=3f63a484

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts



let adminContent = null;
const {
  $: ContentBlockvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var ContentBlockvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    contentTitle: String,
    feature: String,
    helpUrl: String,
    helpText: String,
    anchor: String,
    imageUrl: String,
    imageAltText: String
  },
  components: {
    EnrichedHeadline: EnrichedHeadline
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
      const anchorElement = document.createElement('a');
      anchorElement.id = this.anchor;
      ContentBlockvue_type_script_lang_ts_$(root.parentElement).prepend(anchorElement);
    }
    setTimeout(() => {
      const inlineHelp = content.querySelector('.contentHelp');
      if (inlineHelp) {
        this.actualHelpText = inlineHelp.innerHTML;
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
    let contentTopPosition = null;
    if (adminContent) {
      contentTopPosition = adminContent.offsetTop;
    }
    if (contentTopPosition || contentTopPosition === 0) {
      const parents = root.closest('.widgetLoader');
      // when shown within the widget loader, we need to get the offset of that element
      // as the widget loader might be still shown. Would otherwise not position correctly
      // the widgets on the admin home page
      const topThis = parents ? parents.offsetTop : root.offsetTop;
      if (topThis - contentTopPosition < 17) {
        // we make sure to display the first card with no margin-top to have it on same as line as
        // navigation
        root.style.marginTop = '0';
      }
    }
  },
  methods: {
    decode(s) {
      return Matomo_Matomo.helper.htmlDecode(s);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue



ContentBlockvue_type_script_lang_ts.render = ContentBlockvue_type_template_id_3f63a484_render

/* harmony default export */ var ContentBlock = (ContentBlockvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=0fddc0cc

const Comparisonsvue_type_template_id_0fddc0cc_hoisted_1 = {
  key: 0,
  ref: "root",
  class: "matomo-comparisons"
};
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_2 = {
  class: "comparison-type"
};
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_3 = ["title"];
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_4 = ["href"];
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_5 = ["title"];
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_6 = {
  class: "comparison-period-label"
};
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_7 = ["onClick"];
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_8 = ["title"];
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_9 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  }
};
const Comparisonsvue_type_template_id_0fddc0cc_hoisted_10 = ["alt"];
function Comparisonsvue_type_template_id_0fddc0cc_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return _ctx.isComparing ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Comparisonsvue_type_template_id_0fddc0cc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Comparisons')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.segmentComparisons, (comparison, $index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "comparison card",
      key: comparison.index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_0fddc0cc_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Segment')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "title",
      title: _ctx.getTitleTooltip(comparison)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      href: _ctx.getUrlToSegment(comparison.params.segment)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(comparison.title), 9, Comparisonsvue_type_template_id_0fddc0cc_hoisted_4)], 8, Comparisonsvue_type_template_id_0fddc0cc_hoisted_3), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periodComparisons, periodComparison => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "comparison-period",
        key: periodComparison.index,
        title: _ctx.getComparisonTooltip(comparison, periodComparison)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "comparison-dot",
        style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
          'background-color': _ctx.getSeriesColor(comparison, periodComparison)
        })
      }, null, 4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Comparisonsvue_type_template_id_0fddc0cc_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(periodComparison.title) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getComparisonPeriodType(periodComparison)) + ") ", 1)], 8, Comparisonsvue_type_template_id_0fddc0cc_hoisted_5);
    }), 128)), _ctx.segmentComparisons.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "remove-button",
      onClick: $event => _ctx.removeSegmentComparison($index)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon icon-close",
      title: _ctx.translate('General_ClickToRemoveComp')
    }, null, 8, Comparisonsvue_type_template_id_0fddc0cc_hoisted_8)], 8, Comparisonsvue_type_template_id_0fddc0cc_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_0fddc0cc_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, Comparisonsvue_type_template_id_0fddc0cc_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])])), [[_directive_tooltips, {
    duration: 200,
    delay: 200,
    content: _ctx.transformTooltipContent
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=0fddc0cc

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Segmentation/Segments.store.ts
function Segments_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class Segments_store_SegmentsStore {
  get state() {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.segmentState);
  }
  constructor() {
    Segments_store_defineProperty(this, "segmentState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      availableSegments: []
    }));
    Matomo_Matomo.on('piwikSegmentationInited', () => this.setSegmentState());
  }
  setSegmentState() {
    try {
      const uiControlObject = $('.segmentEditorPanel').data('uiControlObject');
      this.segmentState.availableSegments = uiControlObject.impl.availableSegments || [];
    } catch (e) {
      // segment editor is not initialized yet
    }
  }
}
/* harmony default export */ var Segments_store = (new Segments_store_SegmentsStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.store.ts
function Comparisons_store_extends() { Comparisons_store_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Comparisons_store_extends.apply(this, arguments); }
function Comparisons_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
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
class Comparisons_store_ComparisonsStore {
  constructor() {
    Comparisons_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      comparisonsDisabledFor: []
    }));
    Comparisons_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState));
    // for tests
    Comparisons_store_defineProperty(this, "colors", {});
    Comparisons_store_defineProperty(this, "segmentComparisons", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.parseSegmentComparisons()));
    Comparisons_store_defineProperty(this, "periodComparisons", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.parsePeriodComparisons()));
    Comparisons_store_defineProperty(this, "isEnabled", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.checkEnabledForCurrentPage()));
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
      this.loadComparisonsDisabledFor();
    } else {
      document.addEventListener('DOMContentLoaded', () => {
        this.loadComparisonsDisabledFor();
      });
    }
    $(() => {
      this.colors = this.getAllSeriesColors();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => this.getComparisons(), () => Matomo_Matomo.postEvent('piwikComparisonsChanged'), {
      deep: true
    });
  }
  getComparisons() {
    return this.getSegmentComparisons().concat(this.getPeriodComparisons());
  }
  isComparing() {
    return this.isComparisonEnabled()
    // first two in each array are for the currently selected segment/period
    && (this.segmentComparisons.value.length > 1 || this.periodComparisons.value.length > 1);
  }
  isComparingPeriods() {
    return this.getPeriodComparisons().length > 1; // first is currently selected period
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
    const seriesIndex = this.getComparisonSeriesIndex(periodComparison.index, segmentComparison.index) % SERIES_COLOR_COUNT;
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
    this.getPeriodComparisons().forEach(periodComp => {
      this.getSegmentComparisons().forEach(segmentComp => {
        seriesInfo.push({
          index: seriesIndex,
          params: Comparisons_store_extends(Comparisons_store_extends({}, segmentComp.params), periodComp.params),
          color: this.colors[`series${seriesIndex}`]
        });
        seriesIndex += 1;
      });
    });
    return seriesInfo;
  }
  removeSegmentComparison(index) {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }
    const newComparisons = [...this.segmentComparisons.value];
    newComparisons.splice(index, 1);
    const extraParams = {};
    if (index === 0) {
      extraParams.segment = newComparisons[0].params.segment;
    }
    this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value, extraParams);
  }
  addSegmentComparison(params) {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }
    const newComparisons = this.segmentComparisons.value.concat([{
      params,
      index: -1,
      title: ''
    }]);
    this.updateQueryParamsFromComparisons(newComparisons, this.periodComparisons.value);
  }
  updateQueryParamsFromComparisons(segmentComparisons, periodComparisons, extraParams = {}) {
    // get unique segments/periods/dates from new Comparisons
    const compareSegments = {};
    const comparePeriodDatePairs = {};
    let firstSegment = false;
    let firstPeriod = false;
    segmentComparisons.forEach(comparison => {
      if (firstSegment) {
        compareSegments[comparison.params.segment] = true;
      } else {
        firstSegment = true;
      }
    });
    periodComparisons.forEach(comparison => {
      if (firstPeriod) {
        comparePeriodDatePairs[`${comparison.params.period}|${comparison.params.date}`] = true;
      } else {
        firstPeriod = true;
      }
    });
    const comparePeriods = [];
    const compareDates = [];
    Object.keys(comparePeriodDatePairs).forEach(pair => {
      const parts = pair.split('|');
      comparePeriods.push(parts[0]);
      compareDates.push(parts[1]);
    });
    const compareParams = {
      compareSegments: Object.keys(compareSegments),
      comparePeriods,
      compareDates
    };
    // change the page w/ these new param values
    const baseParams = Matomo_Matomo.helper.isReportingPage() ? src_MatomoUrl_MatomoUrl.hashParsed.value : src_MatomoUrl_MatomoUrl.urlParsed.value;
    src_MatomoUrl_MatomoUrl.updateLocation(Comparisons_store_extends(Comparisons_store_extends(Comparisons_store_extends({}, baseParams), compareParams), extraParams));
  }
  getAllSeriesColors() {
    const {
      ColorManager
    } = Matomo_Matomo;
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
    return ColorManager.getColors('comparison-series-color', seriesColorNames);
  }
  loadComparisonsDisabledFor() {
    const matomoModule = src_MatomoUrl_MatomoUrl.parsed.value.module;
    // Skip while installing, updating or logging in
    if (matomoModule === 'CoreUpdater' || matomoModule === 'Installation' || matomoModule === 'Overlay' || window.piwik.isPagesComparisonApiDisabled || window.piwik.installation || window.broadcast.isLoginPage()) {
      this.privateState.comparisonsDisabledFor = [];
      return;
    }
    AjaxHelper_AjaxHelper.fetch({
      module: 'API',
      method: 'API.getPagesComparisonsDisabledFor'
    }).then(result => {
      this.privateState.comparisonsDisabledFor = result;
    });
  }
  parseSegmentComparisons() {
    const {
      availableSegments
    } = Segments_store.state;
    const compareSegments = [...wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.compareSegments)];
    // add base comparisons
    compareSegments.unshift(src_MatomoUrl_MatomoUrl.parsed.value.segment || '');
    const newSegmentComparisons = [];
    compareSegments.forEach((segment, idx) => {
      let storedSegment;
      availableSegments.forEach(s => {
        if (s.definition === segment || s.definition === decodeURIComponent(segment) || decodeURIComponent(s.definition) === segment) {
          storedSegment = s;
        }
      });
      let segmentTitle = storedSegment ? storedSegment.name : translate('General_Unknown');
      if (segment.trim() === '') {
        segmentTitle = translate('SegmentEditor_DefaultAllVisits');
      }
      newSegmentComparisons.push({
        params: {
          segment
        },
        title: Matomo_Matomo.helper.htmlDecode(segmentTitle),
        index: idx
      });
    });
    return newSegmentComparisons;
  }
  parsePeriodComparisons() {
    const comparePeriods = [...wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.comparePeriods)];
    const compareDates = [...wrapArray(src_MatomoUrl_MatomoUrl.parsed.value.compareDates)];
    comparePeriods.unshift(src_MatomoUrl_MatomoUrl.parsed.value.period);
    compareDates.unshift(src_MatomoUrl_MatomoUrl.parsed.value.date);
    const newPeriodComparisons = [];
    for (let i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
      let title;
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
        title,
        index: i
      });
    }
    return newPeriodComparisons;
  }
  checkEnabledForCurrentPage() {
    // category/subcategory is not included on top bar pages, so in that case we use module/action
    const category = src_MatomoUrl_MatomoUrl.parsed.value.category || src_MatomoUrl_MatomoUrl.parsed.value.module;
    const subcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory || src_MatomoUrl_MatomoUrl.parsed.value.action;
    const id = `${category}.${subcategory}`;
    const isEnabled = this.privateState.comparisonsDisabledFor.indexOf(id) === -1 && this.privateState.comparisonsDisabledFor.indexOf(`${category}.*`) === -1;
    document.documentElement.classList.toggle('comparisonsDisabled', !isEnabled);
    return isEnabled;
  }
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.store.instance.ts

/* harmony default export */ var Comparisons_store_instance = (new Comparisons_store_ComparisonsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
function Comparisonsvue_type_script_lang_ts_extends() { Comparisonsvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Comparisonsvue_type_script_lang_ts_extends.apply(this, arguments); }







/* harmony default export */ var Comparisonsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {},
  directives: {
    Tooltips: Tooltips
  },
  data() {
    return {
      comparisonTooltips: null
    };
  },
  setup() {
    // accessing has to be done through a computed property so we can use the computed
    // instance directly in the template. unfortunately, vue won't register to changes.
    const isComparing = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Comparisons_store_instance.isComparing() && !window.broadcast.isNoDataPage());
    const segmentComparisons = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Comparisons_store_instance.getSegmentComparisons());
    const periodComparisons = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Comparisons_store_instance.getPeriodComparisons());
    const getSeriesColor = Comparisons_store_instance.getSeriesColor.bind(Comparisons_store_instance);
    function transformTooltipContent() {
      const title = window.$(this).attr('title');
      if (!title) {
        return title;
      }
      return window.vueSanitize(title.replace(/\n/g, '<br />'));
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
      return typeof comparison.params.segment !== 'undefined';
    },
    removeSegmentComparison(index) {
      // otherwise the tooltip will be stuck on the screen
      window.$(this.$refs.root).tooltip('destroy');
      Comparisons_store_instance.removeSegmentComparison(index);
    },
    getComparisonPeriodType(comparison) {
      const {
        period
      } = comparison.params;
      if (period === 'range') {
        return translate('CoreHome_PeriodRange');
      }
      const periodStr = translate(`Intl_Period${period.substring(0, 1).toUpperCase()}${period.substring(1)}`);
      return periodStr.substring(0, 1).toUpperCase() + periodStr.substring(1);
    },
    getComparisonTooltip(segmentComparison, periodComparison) {
      if (!this.comparisonTooltips || !Object.keys(this.comparisonTooltips).length) {
        return undefined;
      }
      return (this.comparisonTooltips[periodComparison.index] || {})[segmentComparison.index];
    },
    getTitleTooltip(comparison) {
      return `${this.htmlentities(comparison.title)}<br/>` + `${this.htmlentities(decodeURIComponent(comparison.params.segment))}`;
    },
    getUrlToSegment(segment) {
      const hash = Comparisonsvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value);
      delete hash.comparePeriods;
      delete hash.compareDates;
      delete hash.compareSegments;
      hash.segment = segment;
      return `${window.location.search}#?${src_MatomoUrl_MatomoUrl.stringify(hash)}`;
    },
    onComparisonsChanged() {
      this.comparisonTooltips = null;
      if (!Comparisons_store_instance.isComparing()) {
        return;
      }
      const periodComparisons = Comparisons_store_instance.getPeriodComparisons();
      const segmentComparisons = Comparisons_store_instance.getSegmentComparisons();
      AjaxHelper_AjaxHelper.fetch({
        method: 'API.getProcessedReport',
        apiModule: 'VisitsSummary',
        apiAction: 'get',
        compare: '1',
        compareSegments: src_MatomoUrl_MatomoUrl.getSearchParam('compareSegments'),
        comparePeriods: src_MatomoUrl_MatomoUrl.getSearchParam('comparePeriods'),
        compareDates: src_MatomoUrl_MatomoUrl.getSearchParam('compareDates'),
        format_metrics: '1'
      }).then(report => {
        this.comparisonTooltips = {};
        periodComparisons.forEach(periodComp => {
          this.comparisonTooltips[periodComp.index] = {};
          segmentComparisons.forEach(segmentComp => {
            const tooltip = this.generateComparisonTooltip(report, periodComp, segmentComp);
            this.comparisonTooltips[periodComp.index][segmentComp.index] = tooltip;
          });
        });
      });
    },
    generateComparisonTooltip(visitsSummary, periodComp, segmentComp) {
      if (!visitsSummary.reportData.comparisons) {
        // sanity check
        return '';
      }
      const firstRowIndex = Comparisons_store_instance.getComparisonSeriesIndex(periodComp.index, 0);
      const firstRow = visitsSummary.reportData.comparisons[firstRowIndex];
      const comparisonRowIndex = Comparisons_store_instance.getComparisonSeriesIndex(periodComp.index, segmentComp.index);
      const comparisonRow = visitsSummary.reportData.comparisons[comparisonRowIndex];
      const firstPeriodRow = visitsSummary.reportData.comparisons[segmentComp.index];
      let tooltip = '<div class="comparison-card-tooltip">';
      let visitsPercent = (comparisonRow.nb_visits / firstRow.nb_visits * 100).toFixed(2);
      visitsPercent = `${visitsPercent}%`;
      tooltip += translate('General_ComparisonCardTooltip1', [`'${comparisonRow.compareSegmentPretty}'`, comparisonRow.comparePeriodPretty, visitsPercent, comparisonRow.nb_visits.toString(), firstRow.nb_visits.toString()]);
      if (periodComp.index > 0) {
        tooltip += '<br/><br/>';
        tooltip += translate('General_ComparisonCardTooltip2', [comparisonRow.nb_visits_change.toString(), firstPeriodRow.compareSegmentPretty, firstPeriodRow.comparePeriodPretty]);
      }
      tooltip += '</div>';
      return tooltip;
    },
    htmlentities(str) {
      return Matomo_Matomo.helper.htmlEntities(str);
    }
  },
  mounted() {
    Matomo_Matomo.on('piwikComparisonsChanged', () => {
      this.onComparisonsChanged();
    });
    this.onComparisonsChanged();
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue



Comparisonsvue_type_script_lang_ts.render = Comparisonsvue_type_template_id_0fddc0cc_render

/* harmony default export */ var Comparisons = (Comparisonsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=template&id=39121530

const MenuItemsDropdownvue_type_template_id_39121530_hoisted_1 = {
  ref: "root",
  class: "menuDropdown"
};
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_2 = ["title"];
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_3 = ["innerHTML"];
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down reporting-menu-sub-icon"
}, null, -1);
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_5 = {
  class: "items"
};
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_6 = {
  key: 0,
  class: "search"
};
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_7 = ["placeholder"];
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_8 = ["title"];
const MenuItemsDropdownvue_type_template_id_39121530_hoisted_9 = ["title"];
function MenuItemsDropdownvue_type_template_id_39121530_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");
  const _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MenuItemsDropdownvue_type_template_id_39121530_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "title",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showItems = !_ctx.showItems),
    title: _ctx.tooltip
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(this.actualMenuTitle)
  }, null, 8, MenuItemsDropdownvue_type_template_id_39121530_hoisted_3), MenuItemsDropdownvue_type_template_id_39121530_hoisted_4], 8, MenuItemsDropdownvue_type_template_id_39121530_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MenuItemsDropdownvue_type_template_id_39121530_hoisted_5, [_ctx.showSearch && _ctx.showItems ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MenuItemsDropdownvue_type_template_id_39121530_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.searchTerm = $event),
    onKeydown: _cache[2] || (_cache[2] = $event => _ctx.onSearchTermKeydown($event)),
    placeholder: _ctx.translate('General_Search')
  }, null, 40, MenuItemsDropdownvue_type_template_id_39121530_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.showItems
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "search_ico icon-search",
    title: _ctx.translate('General_Search')
  }, null, 8, MenuItemsDropdownvue_type_template_id_39121530_hoisted_8), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[3] || (_cache[3] = $event => {
      _ctx.searchTerm = '';
      _ctx.searchItems('');
    }),
    class: "reset icon-close",
    title: _ctx.translate('General_Clear')
  }, null, 8, MenuItemsDropdownvue_type_template_id_39121530_hoisted_9), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[4] || (_cache[4] = $event => _ctx.selectItem($event))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showItems]])])), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.lostFocus
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=template&id=39121530

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MenuItemsDropdown/MenuItemsDropdown.vue?vue&type=script&lang=ts



const {
  $: MenuItemsDropdownvue_type_script_lang_ts_$
} = window;
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
    menuTitle() {
      this.actualMenuTitle = this.menuTitle;
    }
  },
  data() {
    return {
      showItems: false,
      searchTerm: '',
      actualMenuTitle: this.menuTitle
    };
  },
  methods: {
    lostFocus() {
      this.showItems = false;
    },
    selectItem(event) {
      const targetClasses = event.target.classList;
      if (!targetClasses.contains('item') || targetClasses.contains('disabled') || targetClasses.contains('separator')) {
        return;
      }
      if (this.menuTitleChangeOnClick) {
        this.actualMenuTitle = (event.target.textContent || '').replace(/[\u0000-\u2666]/g, c => `&#${c.charCodeAt(0)};`); // eslint-disable-line
      }
      this.showItems = false;
      MenuItemsDropdownvue_type_script_lang_ts_$(this.$slots.default()[0].el).find('.item').removeClass('active');
      targetClasses.add('active');
      this.$emit('afterSelect', event.target);
    },
    onSearchTermKeydown() {
      setTimeout(() => {
        this.searchItems(this.searchTerm);
      });
    },
    searchItems(unprocessedSearchTerm) {
      const searchTerm = unprocessedSearchTerm.toLowerCase();
      MenuItemsDropdownvue_type_script_lang_ts_$(this.$refs.root).find('.item').each((index, node) => {
        const $node = MenuItemsDropdownvue_type_script_lang_ts_$(node);
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



MenuItemsDropdownvue_type_script_lang_ts.render = MenuItemsDropdownvue_type_template_id_39121530_render

/* harmony default export */ var MenuItemsDropdown = (MenuItemsDropdownvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=7b95c829

const DatePickervue_type_template_id_7b95c829_hoisted_1 = {
  ref: "root"
};
function DatePickervue_type_template_id_7b95c829_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DatePickervue_type_template_id_7b95c829_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=7b95c829

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts
function DatePickervue_type_script_lang_ts_extends() { DatePickervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return DatePickervue_type_script_lang_ts_extends.apply(this, arguments); }



const DEFAULT_STEP_MONTHS = 1;
const {
  $: DatePickervue_type_script_lang_ts_$
} = window;
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
  setup(props, context) {
    const root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    function setDateCellColor($dateCell, dateValue) {
      const $dateCellLink = $dateCell.children('a');
      if (props.selectedDateStart && props.selectedDateEnd && dateValue >= props.selectedDateStart && dateValue <= props.selectedDateEnd) {
        $dateCell.addClass('ui-datepicker-current-period');
      } else {
        $dateCell.removeClass('ui-datepicker-current-period');
      }
      if (props.highlightedDateStart && props.highlightedDateEnd && dateValue >= props.highlightedDateStart && dateValue <= props.highlightedDateEnd) {
        // other-month cells don't have links, so the <td> must have the ui-state-hover class
        const elementToAddClassTo = $dateCellLink.length ? $dateCellLink : $dateCell;
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
      const day = parseInt($dateCell.children('a,span').text(), 10);
      return new Date(year, month, day);
    }
    function getOtherMonthDate($dateCell, month, year) {
      let date;
      const $row = $dateCell.parent();
      const $rowCells = $row.children('td');
      // if in the first row, the date cell is before the current month
      if ($row.is(':first-child')) {
        const $firstDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').first();
        date = getCellDate($firstDateInMonth, month, year);
        date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
        return date;
      }
      // the date cell is after the current month
      const $lastDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').last();
      date = getCellDate($lastDateInMonth, month, year);
      date.setDate(date.getDate() + $rowCells.index($dateCell) - $rowCells.index($lastDateInMonth));
      return date;
    }
    function getMonthYearDisplayed() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const $firstCellWithMonth = element.find('td[data-month]');
      const month = parseInt($firstCellWithMonth.attr('data-month'), 10);
      const year = parseInt($firstCellWithMonth.attr('data-year'), 10);
      return [month, year];
    }
    function setDatePickerCellColors() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const $calendarTable = element.find('.ui-datepicker-calendar');
      const monthYear = getMonthYearDisplayed();
      // highlight the rest of the cells by first getting the date for the first cell
      // in the calendar, then just incrementing by one for the rest of the cells.
      const $cells = $calendarTable.find('td');
      const $firstDateCell = $cells.first();
      const currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);
      $cells.each(function setCellColor() {
        setDateCellColor(DatePickervue_type_script_lang_ts_$(this), currentDate);
        currentDate.setDate(currentDate.getDate() + 1);
      });
    }
    function viewDateChanged() {
      if (!props.viewDate) {
        return false;
      }
      let date;
      if (typeof props.viewDate === 'string') {
        try {
          date = parseDate(props.viewDate);
        } catch (e) {
          return false;
        }
      } else {
        date = props.viewDate;
      }
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      // only change the datepicker date if the date is outside of the current month/year.
      // this avoids a re-render in other cases.
      const monthYear = getMonthYearDisplayed();
      if (monthYear[0] !== date.getMonth() || monthYear[1] !== date.getFullYear()) {
        element.datepicker('setDate', date);
        return true;
      }
      return false;
    }
    // remove the ui-state-active class & click handlers for every cell. we bypass
    // the datepicker's date selection logic for smoother browser rendering.
    function onJqueryUiRenderedPicker() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      element.find('td[data-event]').off('click');
      element.find('.ui-state-active').removeClass('ui-state-active');
      element.find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day');
      // add href to left/right nav in calendar so they can be accessed via keyboard
      element.find('.ui-datepicker-prev,.ui-datepicker-next').attr('href', '');
    }
    function stepMonthsChanged() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const stepMonths = props.stepMonths || DEFAULT_STEP_MONTHS;
      if (element.datepicker('option', 'stepMonths') === stepMonths) {
        return false;
      }
      // setting stepMonths will change the month in view back to the selected date. to avoid
      // we set the selected date to the month in view.
      const currentMonth = DatePickervue_type_script_lang_ts_$('.ui-datepicker-month', element).val();
      const currentYear = DatePickervue_type_script_lang_ts_$('.ui-datepicker-year', element).val();
      element.datepicker('option', 'stepMonths', stepMonths).datepicker('setDate', new Date(currentYear, currentMonth));
      onJqueryUiRenderedPicker();
      return true;
    }
    function enableDisableMonthDropdown() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const monthPicker = element.find('.ui-datepicker-month')[0];
      if (monthPicker) {
        monthPicker.disabled = props.disableMonthDropdown;
      }
    }
    function handleOtherMonthClick() {
      if (!DatePickervue_type_script_lang_ts_$(this).hasClass('ui-state-hover')) {
        return;
      }
      const $row = DatePickervue_type_script_lang_ts_$(this).parent();
      const $tbody = $row.parent();
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
    }
    // on a prop change (NOTE: we can't watch just `props`, since then newProps and oldProps will
    // have the same values (since it is a proxy object). Using a copy doesn't quite work, the
    // object it returns will always be different, BUT, since we check what changes it works
    // for our purposes. The only downside is that it runs on every tick basically, but since
    // that is within the context of the date picker component, it's bearable.
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => DatePickervue_type_script_lang_ts_extends({}, props), (newProps, oldProps) => {
      let redraw = false;
      [x => x.selectedDateStart, x => x.selectedDateEnd, x => x.highlightedDateStart, x => x.highlightedDateEnd].forEach(selector => {
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
      // redraw when selected/highlighted dates change
      if (redraw) {
        setDatePickerCellColors();
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const customOptions = props.options || {};
      const datePickerOptions = DatePickervue_type_script_lang_ts_extends(DatePickervue_type_script_lang_ts_extends(DatePickervue_type_script_lang_ts_extends({}, Matomo_Matomo.getBaseDatePickerOptions()), customOptions), {}, {
        onChangeMonthYear: () => {
          // datepicker renders the HTML after this hook is called, so we use setTimeout
          // to run some code after the render.
          setTimeout(() => {
            onJqueryUiRenderedPicker();
          });
        }
      });
      element.datepicker(datePickerOptions);
      element.on('mouseover', 'tbody td a', event => {
        // this event is triggered when a user clicks a date as well. in that case,
        // the originalEvent is null. we don't need to redraw again for that, so
        // we ignore events like that.
        if (event.originalEvent) {
          setDatePickerCellColors();
        }
      });
      // on hover cell, execute scope.cellHover()
      element.on('mouseenter', 'tbody td', function onMouseEnter() {
        const monthYear = getMonthYearDisplayed();
        const $dateCell = DatePickervue_type_script_lang_ts_$(this);
        const dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
        context.emit('cellHover', {
          date: dateValue,
          $cell: $dateCell
        });
      });
      // overrides jquery UI handler that unhighlights a cell when the mouse leaves it
      element.on('mouseout', 'tbody td a', () => {
        setDatePickerCellColors();
      });
      // call scope.cellHoverLeave() when mouse leaves table body (can't do event on tbody, for
      // some reason that fails, so we do two events, one on the table & one on thead)
      element.on('mouseleave', 'table', () => context.emit('cellHoverLeave')).on('mouseenter', 'thead', () => context.emit('cellHoverLeave'));
      // make sure whitespace is clickable when the period makes it appropriate
      element.on('click', 'tbody td.ui-datepicker-other-month', handleOtherMonthClick);
      // NOTE: using a selector w/ .on() doesn't seem to work for some reason...
      element.on('click', e => {
        e.preventDefault();
        const $target = DatePickervue_type_script_lang_ts_$(e.target).closest('a');
        if (!$target.is('.ui-datepicker-next') && !$target.is('.ui-datepicker-prev')) {
          return;
        }
        onCalendarViewChange();
      });
      // when a cell is clicked, invoke the onDateSelected function. this, in conjunction
      // with onJqueryUiRenderedPicker(), overrides the date picker's click behavior.
      element.on('click', 'td[data-month]', event => {
        const $cell = DatePickervue_type_script_lang_ts_$(event.target).closest('td');
        const month = parseInt($cell.attr('data-month'), 10);
        const year = parseInt($cell.attr('data-year'), 10);
        const day = parseInt($cell.children('a,span').text(), 10);
        context.emit('dateSelect', {
          date: new Date(year, month, day)
        });
      });
      const renderPostProcessed = stepMonthsChanged();
      viewDateChanged();
      enableDisableMonthDropdown();
      if (!renderPostProcessed) {
        onJqueryUiRenderedPicker();
      }
      setDatePickerCellColors();
    });
    return {
      root
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue



DatePickervue_type_script_lang_ts.render = DatePickervue_type_template_id_7b95c829_render

/* harmony default export */ var DatePicker = (DatePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=7e956b79

const DateRangePickervue_type_template_id_7e956b79_hoisted_1 = {
  class: "dateRangePicker"
};
const DateRangePickervue_type_template_id_7e956b79_hoisted_2 = {
  id: "calendarRangeFrom"
};
const DateRangePickervue_type_template_id_7e956b79_hoisted_3 = {
  id: "calendarRangeTo"
};
function DateRangePickervue_type_template_id_7e956b79_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DateRangePickervue_type_template_id_7e956b79_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_7e956b79_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeFrom')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarFrom",
    name: "inputCalendarFrom",
    class: "browser-default",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.startDateText = $event),
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.onRangeInputChanged('from', $event)),
    onKeyup: _cache[2] || (_cache[2] = $event => _ctx.handleEnterPress($event))
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.startDateText]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DatePicker, {
    id: "calendarFrom",
    "view-date": _ctx.startDate,
    "selected-date-start": _ctx.fromPickerSelectedDates[0],
    "selected-date-end": _ctx.fromPickerSelectedDates[1],
    "highlighted-date-start": _ctx.fromPickerHighlightedDates[0],
    "highlighted-date-end": _ctx.fromPickerHighlightedDates[1],
    onDateSelect: _cache[3] || (_cache[3] = $event => _ctx.setStartRangeDate($event.date)),
    onCellHover: _cache[4] || (_cache[4] = $event => _ctx.fromPickerHighlightedDates = _ctx.getNewHighlightedDates($event.date, $event.$cell)),
    onCellHoverLeave: _cache[5] || (_cache[5] = $event => _ctx.fromPickerHighlightedDates = [null, null])
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_7e956b79_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeTo')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarTo",
    name: "inputCalendarTo",
    class: "browser-default",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.endDateText = $event),
    onKeydown: _cache[7] || (_cache[7] = $event => _ctx.onRangeInputChanged('to', $event)),
    onKeyup: _cache[8] || (_cache[8] = $event => _ctx.handleEnterPress($event))
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.endDateText]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DatePicker, {
    id: "calendarTo",
    "view-date": _ctx.endDate,
    "selected-date-start": _ctx.toPickerSelectedDates[0],
    "selected-date-end": _ctx.toPickerSelectedDates[1],
    "highlighted-date-start": _ctx.toPickerHighlightedDates[0],
    "highlighted-date-end": _ctx.toPickerHighlightedDates[1],
    onDateSelect: _cache[9] || (_cache[9] = $event => _ctx.setEndRangeDate($event.date)),
    onCellHover: _cache[10] || (_cache[10] = $event => _ctx.toPickerHighlightedDates = _ctx.getNewHighlightedDates($event.date, $event.$cell)),
    onCellHoverLeave: _cache[11] || (_cache[11] = $event => _ctx.toPickerHighlightedDates = [null, null])
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=7e956b79

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts



const DATE_FORMAT = 'YYYY-MM-DD';
/* harmony default export */ var DateRangePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    startDate: String,
    endDate: String
  },
  components: {
    DatePicker: DatePicker
  },
  data() {
    let startDate = null;
    try {
      if (this.startDate) {
        startDate = parseDate(this.startDate);
      }
    } catch (e) {
      // ignore
    }
    let endDate = null;
    try {
      if (this.endDate) {
        endDate = parseDate(this.endDate);
      }
    } catch (e) {
      // ignore
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
    startDate() {
      this.startDateText = this.startDate;
      this.setStartRangeDateFromStr(this.startDate);
    },
    endDate() {
      this.endDateText = this.endDate;
      this.setEndRangeDateFromStr(this.endDate);
    }
  },
  mounted() {
    this.rangeChanged(); // emit with initial range pair
  },
  methods: {
    setStartRangeDate(date) {
      this.fromPickerSelectedDates = [date, date];
      this.rangeChanged();
    },
    setEndRangeDate(date) {
      this.toPickerSelectedDates = [date, date];
      this.rangeChanged();
    },
    onRangeInputChanged(source, event) {
      setTimeout(() => {
        if (source === 'from') {
          this.setStartRangeDateFromStr(event.target.value);
        } else {
          this.setEndRangeDateFromStr(event.target.value);
        }
      });
    },
    getNewHighlightedDates(date, $cell) {
      if ($cell.hasClass('ui-datepicker-unselectable')) {
        return null;
      }
      return [date, date];
    },
    handleEnterPress($event) {
      if ($event.keyCode !== 13) {
        return;
      }
      this.$emit('submit', {
        start: this.startDate,
        end: this.endDate
      });
    },
    setStartRangeDateFromStr(dateStr) {
      this.startDateInvalid = true;
      let startDateParsed = null;
      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          startDateParsed = parseDate(dateStr);
        }
      } catch (e) {
        // ignore
      }
      if (startDateParsed) {
        this.fromPickerSelectedDates = [startDateParsed, startDateParsed];
        this.startDateInvalid = false;
        this.rangeChanged();
      }
    },
    setEndRangeDateFromStr(dateStr) {
      this.endDateInvalid = true;
      let endDateParsed = null;
      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          endDateParsed = parseDate(dateStr);
        }
      } catch (e) {
        // ignore
      }
      if (endDateParsed) {
        this.toPickerSelectedDates = [endDateParsed, endDateParsed];
        this.endDateInvalid = false;
        this.rangeChanged();
      }
    },
    rangeChanged() {
      this.$emit('rangeChange', {
        start: this.fromPickerSelectedDates[0] ? format(this.fromPickerSelectedDates[0]) : null,
        end: this.toPickerSelectedDates[0] ? format(this.toPickerSelectedDates[0]) : null
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue



DateRangePickervue_type_script_lang_ts.render = DateRangePickervue_type_template_id_7e956b79_render

/* harmony default export */ var DateRangePicker = (DateRangePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=32009d07

function PeriodDatePickervue_type_template_id_32009d07_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DatePicker, {
    "selected-date-start": _ctx.selectedDates[0],
    "selected-date-end": _ctx.selectedDates[1],
    "highlighted-date-start": _ctx.highlightedDates[0],
    "highlighted-date-end": _ctx.highlightedDates[1],
    "view-date": _ctx.viewDate,
    "step-months": _ctx.period === 'year' ? 12 : 1,
    "disable-month-dropdown": _ctx.period === 'year',
    onCellHover: _cache[0] || (_cache[0] = $event => _ctx.onHoverNormalCell($event.date, $event.$cell)),
    onCellHoverLeave: _cache[1] || (_cache[1] = $event => _ctx.onHoverLeaveNormalCells()),
    onDateSelect: _cache[2] || (_cache[2] = $event => _ctx.onDateSelected($event.date))
  }, null, 8, ["selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end", "view-date", "step-months", "disable-month-dropdown"]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=32009d07

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts




const PeriodDatePickervue_type_script_lang_ts_piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
const piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);
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
  setup(props, context) {
    const viewDate = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.date);
    const selectedDates = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([null, null]);
    const highlightedDates = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([null, null]);
    function getBoundedDateRange(date) {
      const dates = Periods_Periods.get(props.period).parse(date).getDateRange();
      // make sure highlighted date range is within min/max date range
      dates[0] = PeriodDatePickervue_type_script_lang_ts_piwikMinDate < dates[0] ? dates[0] : PeriodDatePickervue_type_script_lang_ts_piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;
      return dates;
    }
    function onHoverNormalCell(cellDate, $cell) {
      const isOutOfMinMaxDateRange = cellDate < PeriodDatePickervue_type_script_lang_ts_piwikMinDate || cellDate > piwikMaxDate;
      // don't highlight anything if the period is month or day, and we're hovering over calendar
      // whitespace. since there are no dates, it's doesn't make sense what you're selecting.
      const shouldNotHighlightFromWhitespace = $cell.hasClass('ui-datepicker-other-month') && (props.period === 'month' || props.period === 'day');
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
        date
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
      selectedDates,
      highlightedDates,
      viewDate,
      onHoverNormalCell,
      onHoverLeaveNormalCells,
      onDateSelected
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue



PeriodDatePickervue_type_script_lang_ts.render = PeriodDatePickervue_type_template_id_32009d07_render

/* harmony default export */ var PeriodDatePicker = (PeriodDatePickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=50cb1ca6

const Notificationvue_type_template_id_50cb1ca6_hoisted_1 = {
  key: 0
};
const Notificationvue_type_template_id_50cb1ca6_hoisted_2 = ["data-notification-instance-id"];
const Notificationvue_type_template_id_50cb1ca6_hoisted_3 = {
  key: 1
};
const Notificationvue_type_template_id_50cb1ca6_hoisted_4 = {
  class: "notification-body"
};
const Notificationvue_type_template_id_50cb1ca6_hoisted_5 = ["innerHTML"];
const Notificationvue_type_template_id_50cb1ca6_hoisted_6 = {
  key: 1
};
function Notificationvue_type_template_id_50cb1ca6_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
    name: _ctx.type === 'toast' ? 'slow-fade-out' : undefined,
    onAfterLeave: _cache[1] || (_cache[1] = $event => _ctx.toastClosed())
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [!_ctx.deleted ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_50cb1ca6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
      name: _ctx.type === 'toast' ? 'toast-slide-up' : undefined,
      appear: ""
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
        name: _ctx.animate ? 'fade-in' : undefined,
        appear: ""
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["notification system", _ctx.cssClasses]),
          style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.style),
          ref: "root",
          "data-notification-instance-id": _ctx.notificationInstanceId
        }, [_ctx.canClose ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
          key: 0,
          type: "button",
          class: "close",
          "data-dismiss": "alert",
          onClick: _cache[0] || (_cache[0] = $event => _ctx.closeNotification($event))
        }, " × ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", Notificationvue_type_template_id_50cb1ca6_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Notificationvue_type_template_id_50cb1ca6_hoisted_4, [_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          key: 0,
          innerHTML: _ctx.$sanitize(_ctx.message)
        }, null, 8, Notificationvue_type_template_id_50cb1ca6_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Notificationvue_type_template_id_50cb1ca6_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 14, Notificationvue_type_template_id_50cb1ca6_hoisted_2)]),
        _: 3
      }, 8, ["name"])])]),
      _: 3
    }, 8, ["name"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 3
  }, 8, ["name"]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=template&id=50cb1ca6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Notification/Notification.vue?vue&type=script&lang=ts


const {
  $: Notificationvue_type_script_lang_ts_$
} = window;
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
      if (this.type === 'persistent') {
        // otherwise it is never possible to dismiss the notification
        return true;
      }
      return !this.noclear;
    }
  },
  emits: ['closed'],
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
    if (this.type === 'toast') {
      addToastEvent();
    }
    if (this.style) {
      Notificationvue_type_script_lang_ts_$(this.$refs.root).css(this.style);
    }
  },
  methods: {
    toastClosed() {
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
        this.$emit('closed');
      });
    },
    closeNotification(event) {
      if (this.canClose && event && event.target) {
        this.deleted = true;
        Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
          this.$emit('closed');
        });
      }
      this.markNotificationAsRead();
    },
    markNotificationAsRead() {
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



Notificationvue_type_script_lang_ts.render = Notificationvue_type_template_id_50cb1ca6_render

/* harmony default export */ var Notification = (Notificationvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=template&id=148c8a5e

const NotificationGroupvue_type_template_id_148c8a5e_hoisted_1 = {
  class: "notification-group"
};
const NotificationGroupvue_type_template_id_148c8a5e_hoisted_2 = ["innerHTML"];
function NotificationGroupvue_type_template_id_148c8a5e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", NotificationGroupvue_type_template_id_148c8a5e_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.notifications, (notification, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
      key: notification.id || `no-id-${index}`,
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
      onClosed: $event => _ctx.removeNotification(notification.id)
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        innerHTML: _ctx.$sanitize(notification.message)
      }, null, 8, NotificationGroupvue_type_template_id_148c8a5e_hoisted_2)]),
      _: 2
    }, 1032, ["notification-id", "title", "context", "type", "noclear", "toast-length", "style", "animate", "message", "notification-instance-id", "css-class", "onClosed"]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=template&id=148c8a5e

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/Notifications.store.ts
function Notifications_store_extends() { Notifications_store_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Notifications_store_extends.apply(this, arguments); }
function Notifications_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




const {
  $: Notifications_store_$
} = window;
class Notifications_store_NotificationsStore {
  constructor() {
    Notifications_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      notifications: []
    }));
    Notifications_store_defineProperty(this, "nextNotificationId", 0);
  }
  get state() {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState);
  }
  appendNotification(notification) {
    this.checkMessage(notification.message);
    // remove existing notification before adding
    if (notification.id) {
      this.remove(notification.id);
    }
    this.privateState.notifications.push(notification);
  }
  prependNotification(notification) {
    this.checkMessage(notification.message);
    // remove existing notification before adding
    if (notification.id) {
      this.remove(notification.id);
    }
    this.privateState.notifications.unshift(notification);
  }
  /**
   * Removes a previously shown notification having the given notification id.
   */
  remove(id) {
    this.privateState.notifications = this.privateState.notifications.filter(n => n.id !== id);
  }
  parseNotificationDivs() {
    const $notificationNodes = Notifications_store_$('[data-role="notification"]');
    const notificationsToShow = [];
    $notificationNodes.each((index, notificationNode) => {
      const $notificationNode = Notifications_store_$(notificationNode);
      const attributes = $notificationNode.data();
      const message = $notificationNode.html();
      if (message) {
        notificationsToShow.push(Notifications_store_extends(Notifications_store_extends({}, attributes), {}, {
          message,
          animate: false
        }));
      }
      $notificationNodes.remove();
    });
    notificationsToShow.forEach(n => this.show(n));
  }
  clearTransientNotifications() {
    this.privateState.notifications = this.privateState.notifications.filter(n => n.type !== 'transient');
  }
  /**
   * Creates a notification and shows it to the user.
   */
  show(notification) {
    this.checkMessage(notification.message);
    let addMethod = notification.prepend ? this.prependNotification : this.appendNotification;
    let notificationPosition = '#notificationContainer';
    if (notification.placeat) {
      notificationPosition = notification.placeat;
    } else {
      // If a modal is open, we want to make sure the error message is visible and therefore
      // show it within the opened modal
      const modalSelector = '.modal.open .modal-content';
      const modal = document.querySelector(modalSelector);
      if (modal) {
        if (!modal.querySelector('#modalNotificationContainer')) {
          Notifications_store_$(modal).prepend('<div id="modalNotificationContainer"/>');
        }
        notificationPosition = `${modalSelector} #modalNotificationContainer`;
        addMethod = this.prependNotification;
      }
    }
    const group = notification.group || (notificationPosition ? notificationPosition.toString() : '');
    this.initializeNotificationContainer(notificationPosition, group);
    const notificationInstanceId = (this.nextNotificationId += 1).toString();
    addMethod.call(this, Notifications_store_extends(Notifications_store_extends({}, notification), {}, {
      noclear: !!notification.noclear,
      group,
      notificationId: notification.id,
      notificationInstanceId,
      type: notification.type || 'transient'
    }));
    return notificationInstanceId;
  }
  scrollToNotification(notificationInstanceId) {
    setTimeout(() => {
      const element = document.querySelector(`[data-notification-instance-id='${notificationInstanceId}']`);
      if (element) {
        Matomo_Matomo.helper.lazyScrollTo(element, 250);
      }
    });
  }
  /**
   * Shows a notification at a certain point with a quick upwards animation.
   */
  toast(notification) {
    this.checkMessage(notification.message);
    const $placeat = notification.placeat ? Notifications_store_$(notification.placeat) : undefined;
    if (!$placeat || !$placeat.length) {
      throw new Error('A valid selector is required for the placeat option when using Notification.toast().');
    }
    const toastElement = document.createElement('div');
    toastElement.style.position = 'absolute';
    toastElement.style.top = `${$placeat.offset().top}px`;
    toastElement.style.left = `${$placeat.offset().left}px`;
    toastElement.style.zIndex = '1000';
    document.body.appendChild(toastElement);
    const app = createVueApp({
      render: () => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Notification, Notifications_store_extends(Notifications_store_extends({}, notification), {}, {
        notificationId: notification.id,
        type: 'toast',
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
    const $container = Notifications_store_$(notificationPosition);
    if ($container.children('.notification-group').length) {
      return;
    }
    // avoiding a dependency cycle. won't need to do this when NotificationGroup's do not need
    // to be dynamically initialized.
    const NotificationGroup = window.CoreHome.NotificationGroup; // eslint-disable-line
    const app = createVueApp({
      template: '<NotificationGroup :group="group"></NotificationGroup>',
      data: () => ({
        group
      })
    });
    app.component('NotificationGroup', NotificationGroup);
    app.mount($container[0]);
  }
  checkMessage(message) {
    if (!message) {
      throw new Error('No message given, cannot display notification');
    }
  }
}
const Notifications_store_instance = new Notifications_store_NotificationsStore();
/* harmony default export */ var Notifications_store = (Notifications_store_instance);
// parse notifications on dom load
Notifications_store_$(() => Notifications_store_instance.parseNotificationDivs());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=script&lang=ts



/* harmony default export */ var NotificationGroupvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    group: String
  },
  components: {
    Notification: Notification
  },
  computed: {
    notifications() {
      return Notifications_store.state.notifications.filter(n => {
        if (this.group) {
          return this.group === n.group;
        }
        return !n.group;
      });
    }
  },
  methods: {
    removeNotification(id) {
      Notifications_store.remove(id);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/NotificationGroup.vue



NotificationGroupvue_type_script_lang_ts.render = NotificationGroupvue_type_template_id_148c8a5e_render

/* harmony default export */ var Notification_NotificationGroup = (NotificationGroupvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Notification/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=template&id=34147ede

const ShowHelpLinkvue_type_template_id_34147ede_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const ShowHelpLinkvue_type_template_id_34147ede_hoisted_2 = [ShowHelpLinkvue_type_template_id_34147ede_hoisted_1];
function ShowHelpLinkvue_type_template_id_34147ede_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    class: "item-help-icon",
    tabindex: "5",
    href: "javascript:",
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.showHelp && _ctx.showHelp(...args))
  }, ShowHelpLinkvue_type_template_id_34147ede_hoisted_2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=template&id=34147ede

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ShowHelpLink/ShowHelpLink.vue?vue&type=script&lang=ts


const REPORTING_HELP_NOTIFICATION_ID = 'reportingMenu-help';
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
  data() {
    return {
      currentName: ''
    };
  },
  methods: {
    showHelp() {
      if (this.currentName !== '') {
        Notifications_store.remove(REPORTING_HELP_NOTIFICATION_ID);
        this.currentName = '';
        return;
      }
      Notifications_store.show({
        context: 'info',
        id: REPORTING_HELP_NOTIFICATION_ID,
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



ShowHelpLinkvue_type_script_lang_ts.render = ShowHelpLinkvue_type_template_id_34147ede_render

/* harmony default export */ var ShowHelpLink = (ShowHelpLinkvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SitesStore.ts
function SitesStore_extends() { SitesStore_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SitesStore_extends.apply(this, arguments); }
function SitesStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class SitesStore_SitesStore {
  constructor() {
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
    SitesStore_defineProperty(this, "initialSites", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state.initialSites)));
    SitesStore_defineProperty(this, "initialSitesFiltered", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.stateFiltered.initialSites)));
  }
  loadInitialSites(onlySitesWithAdminAccess = false, sitesToExclude = []) {
    if (this.state.isInitialized && sitesToExclude.length === 0) {
      return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state.initialSites));
    }
    // If the filtered state has already been initialized with the same sites, return that.
    if (this.stateFiltered.isInitialized && sitesToExclude.length === this.stateFiltered.excludedSites.length && sitesToExclude.every((val, index) => val === this.stateFiltered.excludedSites[index])) {
      return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.stateFiltered.initialSites));
    }
    // If we want to exclude certain sites, perform the search for that.
    if (sitesToExclude.length > 0) {
      this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then(sites => {
        this.stateFiltered.isInitialized = true;
        this.stateFiltered.excludedSites = sitesToExclude;
        if (sites !== null) {
          this.stateFiltered.initialSites = sites;
        }
      });
    }
    // If the main state has already been initialized, no need to continue.
    if (this.state.isInitialized) {
      return Promise.resolve(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state.initialSites));
    }
    return this.searchSite('%', onlySitesWithAdminAccess, sitesToExclude).then(sites => {
      this.state.isInitialized = true;
      if (sites !== null) {
        this.state.initialSites = sites;
      }
      return sites;
    });
  }
  loadSite(idSite) {
    if (idSite === 'all') {
      src_MatomoUrl_MatomoUrl.updateUrl(SitesStore_extends(SitesStore_extends({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        module: 'MultiSites',
        action: 'index',
        date: src_MatomoUrl_MatomoUrl.parsed.value.date,
        period: src_MatomoUrl_MatomoUrl.parsed.value.period
      }));
    } else {
      src_MatomoUrl_MatomoUrl.updateUrl(SitesStore_extends(SitesStore_extends({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        segment: '',
        idSite
      }), SitesStore_extends(SitesStore_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
        segment: '',
        idSite
      }));
    }
  }
  searchSite(term, onlySitesWithAdminAccess = false, sitesToExclude = []) {
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
    return this.limitRequest.then(response => {
      const limit = response.value;
      let methodToCall = 'SitesManager.getPatternMatchSites';
      if (onlySitesWithAdminAccess) {
        methodToCall = 'SitesManager.getSitesWithAdminAccess';
      }
      this.currentRequestAbort = new AbortController();
      return AjaxHelper_AjaxHelper.fetch({
        method: methodToCall,
        limit,
        pattern: term,
        sitesToExclude
      }, {
        abortController: this.currentRequestAbort
      });
    }).then(response => {
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
    sites = sites.map(s => SitesStore_extends(SitesStore_extends({}, s), {}, {
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
/* harmony default export */ var SiteSelector_SitesStore = (new SitesStore_SitesStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=template&id=13051180
function SiteSelectorvue_type_template_id_13051180_extends() { SiteSelectorvue_type_template_id_13051180_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SiteSelectorvue_type_template_id_13051180_extends.apply(this, arguments); }

const SiteSelectorvue_type_template_id_13051180_hoisted_1 = ["value", "name"];
const SiteSelectorvue_type_template_id_13051180_hoisted_2 = ["title"];
const SiteSelectorvue_type_template_id_13051180_hoisted_3 = ["textContent"];
const SiteSelectorvue_type_template_id_13051180_hoisted_4 = {
  key: 1,
  class: "placeholder"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_5 = {
  class: "dropdown"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_6 = {
  class: "custom_select_search"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_7 = ["placeholder"];
const SiteSelectorvue_type_template_id_13051180_hoisted_8 = {
  key: 0
};
const SiteSelectorvue_type_template_id_13051180_hoisted_9 = {
  class: "custom_select_container"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_10 = ["onClick"];
const SiteSelectorvue_type_template_id_13051180_hoisted_11 = ["innerHTML", "href", "title"];
const SiteSelectorvue_type_template_id_13051180_hoisted_12 = {
  class: "custom_select_ul_list"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_13 = {
  class: "noresult"
};
const SiteSelectorvue_type_template_id_13051180_hoisted_14 = {
  key: 1
};
function SiteSelectorvue_type_template_id_13051180_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$displayedModelVa, _ctx$displayedModelVa2, _ctx$displayedModelVa3, _ctx$displayedModelVa4;
  const _component_AllSitesLink = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AllSitesLink");
  const _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  const _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["siteSelector piwikSelector borderedControl", {
      'expanded': _ctx.showSitesList,
      'disabled': !_ctx.hasMultipleSites
    }])
  }, [_ctx.name ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
    key: 0,
    type: "hidden",
    value: (_ctx$displayedModelVa = _ctx.displayedModelValue) === null || _ctx$displayedModelVa === void 0 ? void 0 : _ctx$displayedModelVa.id,
    name: _ctx.name
  }, null, 8, SiteSelectorvue_type_template_id_13051180_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    ref: "selectorLink",
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.onClickSelector && _ctx.onClickSelector(...args)),
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.onPressEnter($event)),
    href: "javascript:void(0)",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
      'loading': _ctx.isLoading
    }, "title"]),
    tabindex: "4",
    title: _ctx.selectorLinkTitle
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["icon icon-chevron-down", {
      'iconHidden': _ctx.isLoading,
      'collapsed': !_ctx.showSitesList
    }])
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [(_ctx$displayedModelVa2 = _ctx.displayedModelValue) !== null && _ctx$displayedModelVa2 !== void 0 && _ctx$displayedModelVa2.name || !_ctx.placeholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(((_ctx$displayedModelVa3 = _ctx.displayedModelValue) === null || _ctx$displayedModelVa3 === void 0 ? void 0 : _ctx$displayedModelVa3.name) || _ctx.firstSiteName)
  }, null, 8, SiteSelectorvue_type_template_id_13051180_hoisted_3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !((_ctx$displayedModelVa4 = _ctx.displayedModelValue) !== null && _ctx$displayedModelVa4 !== void 0 && _ctx$displayedModelVa4.name) && _ctx.placeholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SiteSelectorvue_type_template_id_13051180_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.placeholder), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 42, SiteSelectorvue_type_template_id_13051180_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    onClick: _cache[2] || (_cache[2] = $event => {
      _ctx.searchTerm = '';
      _ctx.loadInitialSites();
    }),
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.searchTerm = $event),
    tabindex: "4",
    class: "websiteSearch inp browser-default",
    placeholder: _ctx.translate('General_Search')
  }, null, 8, SiteSelectorvue_type_template_id_13051180_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.shouldFocusOnSearch
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    title: "Clear",
    onClick: _cache[4] || (_cache[4] = $event => {
      _ctx.searchTerm = '';
      _ctx.loadInitialSites();
    }),
    class: "reset",
    src: "plugins/CoreHome/images/reset_search.png"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.autocompleteMinSites <= _ctx.sites.length || _ctx.searchTerm]]), _ctx.allSitesLocation === 'top' && _ctx.showAllSitesItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AllSitesLink, {
    href: _ctx.urlAllSites,
    "all-sites-text": _ctx.allSitesText,
    onClick: _cache[5] || (_cache[5] = $event => _ctx.onAllSitesClick($event))
  }, null, 8, ["href", "all-sites-text"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
    class: "custom_select_ul_list",
    onClick: _cache[7] || (_cache[7] = $event => _ctx.showSitesList = false)
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, (site, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      onClick: $event => _ctx.switchSite(SiteSelectorvue_type_template_id_13051180_extends(SiteSelectorvue_type_template_id_13051180_extends({}, site), {}, {
        id: site.idsite
      }), $event),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: _cache[6] || (_cache[6] = $event => $event.preventDefault()),
      innerHTML: _ctx.$sanitize(_ctx.getMatchedSiteName(site.name)),
      tabindex: "4",
      href: _ctx.getUrlForSiteId(site.idsite),
      title: site.name
    }, null, 8, SiteSelectorvue_type_template_id_13051180_hoisted_11)], 8, SiteSelectorvue_type_template_id_13051180_hoisted_10)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(!_ctx.showSelectedSite && `${_ctx.activeSiteId}` === `${site.idsite}`)]]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", SiteSelectorvue_type_template_id_13051180_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_NotFound') + ' ' + _ctx.searchTerm), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.sites.length && _ctx.searchTerm]])])), [[_directive_tooltips, {
    content: _ctx.tooltipContent
  }]]), _ctx.allSitesLocation === 'bottom' && _ctx.showAllSitesItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteSelectorvue_type_template_id_13051180_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AllSitesLink, {
    href: _ctx.urlAllSites,
    "all-sites-text": _ctx.allSitesText,
    onClick: _cache[8] || (_cache[8] = $event => _ctx.onAllSitesClick($event))
  }, null, 8, ["href", "all-sites-text"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSitesList]])], 2)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=template&id=13051180

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=template&id=77d1c93d

const AllSitesLinkvue_type_template_id_77d1c93d_hoisted_1 = ["innerHTML", "href"];
function AllSitesLinkvue_type_template_id_77d1c93d_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    onClick: _cache[1] || (_cache[1] = $event => this.onClick($event)),
    class: "custom_select_all"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = $event => $event.preventDefault()),
    innerHTML: _ctx.$sanitize(_ctx.allSitesText),
    tabindex: "4",
    href: _ctx.href
  }, null, 8, AllSitesLinkvue_type_template_id_77d1c93d_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=template&id=77d1c93d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=script&lang=ts

/* harmony default export */ var AllSitesLinkvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    href: String,
    allSitesText: String
  },
  emits: ['click'],
  methods: {
    onClick(event) {
      this.$emit('click', event);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/AllSitesLink.vue



AllSitesLinkvue_type_script_lang_ts.render = AllSitesLinkvue_type_template_id_77d1c93d_render

/* harmony default export */ var AllSitesLink = (AllSitesLinkvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=script&lang=ts
function SiteSelectorvue_type_script_lang_ts_extends() { SiteSelectorvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SiteSelectorvue_type_script_lang_ts_extends.apply(this, arguments); }










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
      default: () => []
    }
  },
  emits: ['update:modelValue', 'blur'],
  components: {
    AllSitesLink: AllSitesLink
  },
  directives: {
    FocusAnywhereButHere: FocusAnywhereButHere,
    FocusIf: FocusIf,
    Tooltips: Tooltips
  },
  watch: {
    searchTerm() {
      this.onSearchTermChanged();
    }
  },
  data() {
    return {
      searchTerm: '',
      activeSiteId: `${Matomo_Matomo.idSite}`,
      showSitesList: false,
      isLoading: false,
      sites: [],
      autocompleteMinSites: parseInt(Matomo_Matomo.config.autocomplete_min_sites, 10)
    };
  },
  created() {
    this.searchSite = debounce(this.searchSite);
    if (!this.modelValue && Matomo_Matomo.idSite) {
      this.$emit('update:modelValue', {
        id: Matomo_Matomo.idSite,
        name: Matomo_Matomo.helper.htmlDecode(Matomo_Matomo.siteName)
      });
    }
  },
  mounted() {
    window.initTopControls();
    this.loadInitialSites().then(() => {
      if (this.shouldDefaultToFirstSite) {
        this.$emit('update:modelValue', {
          id: this.sites[0].idsite,
          name: this.sites[0].name
        });
      }
    });
    const shortcutTitle = translate('CoreHome_ShortcutWebsiteSelector');
    Matomo_Matomo.helper.registerShortcut('w', shortcutTitle, event => {
      if (event.altKey) {
        return;
      }
      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false; // IE
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
      var _this$modelValue;
      return this.hasMultipleSites ? translate('CoreHome_ChangeCurrentWebsite', ((_this$modelValue = this.modelValue) === null || _this$modelValue === void 0 ? void 0 : _this$modelValue.name) || this.firstSiteName) : '';
    },
    hasMultipleSites() {
      const initialSites = SiteSelector_SitesStore.initialSitesFiltered.value && SiteSelector_SitesStore.initialSitesFiltered.value.length ? SiteSelector_SitesStore.initialSitesFiltered.value : SiteSelector_SitesStore.initialSites.value;
      return initialSites && initialSites.length > 1;
    },
    firstSiteName() {
      const initialSites = SiteSelector_SitesStore.initialSitesFiltered.value && SiteSelector_SitesStore.initialSitesFiltered.value.length ? SiteSelector_SitesStore.initialSitesFiltered.value : SiteSelector_SitesStore.initialSites.value;
      return initialSites && initialSites.length > 0 ? initialSites[0].name : '';
    },
    urlAllSites() {
      const newQuery = src_MatomoUrl_MatomoUrl.stringify(SiteSelectorvue_type_script_lang_ts_extends(SiteSelectorvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        module: 'MultiSites',
        action: 'index',
        date: src_MatomoUrl_MatomoUrl.parsed.value.date,
        period: src_MatomoUrl_MatomoUrl.parsed.value.period
      }));
      return `?${newQuery}`;
    },
    shouldDefaultToFirstSite() {
      var _this$modelValue2;
      return !((_this$modelValue2 = this.modelValue) !== null && _this$modelValue2 !== void 0 && _this$modelValue2.id) && (!this.hasMultipleSites || this.defaultToFirstSite) && this.sites[0];
    },
    // using an extra computed property in case SiteSelector is used directly
    // in a vue-entry, and there is no parent component with state to respond
    // to update:modelValue events
    displayedModelValue() {
      if (this.modelValue) {
        return this.modelValue;
      }
      if (Matomo_Matomo.idSite) {
        return {
          id: Matomo_Matomo.idSite,
          name: Matomo_Matomo.helper.htmlDecode(Matomo_Matomo.siteName)
        };
      }
      if (this.shouldDefaultToFirstSite) {
        return {
          id: this.sites[0].idsite,
          name: this.sites[0].name
        };
      }
      return null;
    },
    tooltipContent() {
      return function tooltipContent() {
        const title = $(this).attr('title') || '';
        return Matomo_Matomo.helper.htmlEntities(title);
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
      this.switchSite({
        id: 'all',
        name: this.$props.allSitesText
      }, event);
      this.showSitesList = false;
    },
    switchSite(site, event) {
      // for Mac OS cmd key needs to be pressed, ctrl key on other systems
      const controlKey = navigator.userAgent.indexOf('Mac OS X') !== -1 ? event.metaKey : event.ctrlKey;
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
    onBlur() {
      this.showSitesList = false;
      this.$emit('blur');
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
      if (event.key !== 'Enter') {
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
      if (index === -1 || this.isLoading // only highlight when we know the displayed results are for a search
      ) {
        return this.htmlEntities(siteName);
      }
      const previousPart = this.htmlEntities(siteName.substring(0, index));
      const lastPart = this.htmlEntities(siteName.substring(index + this.searchTerm.length));
      return `${previousPart}<span class="autocompleteMatched">${this.searchTerm}</span>${lastPart}`;
    },
    loadInitialSites() {
      return SiteSelector_SitesStore.loadInitialSites(this.onlySitesWithAdminAccess, this.sitesToExclude ? this.sitesToExclude : []).then(sites => {
        this.sites = sites || [];
      });
    },
    searchSite(term) {
      this.isLoading = true;
      SiteSelector_SitesStore.searchSite(term, this.onlySitesWithAdminAccess, this.sitesToExclude ? this.sitesToExclude : []).then(sites => {
        if (term !== this.searchTerm) {
          return; // search term changed in the meantime
        }
        if (sites) {
          this.sites = sites;
        }
      }).finally(() => {
        this.isLoading = false;
      });
    },
    getUrlForSiteId(idSite) {
      const newQuery = src_MatomoUrl_MatomoUrl.stringify(SiteSelectorvue_type_script_lang_ts_extends(SiteSelectorvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.urlParsed.value), {}, {
        segment: '',
        idSite
      }));
      const newHash = src_MatomoUrl_MatomoUrl.stringify(SiteSelectorvue_type_script_lang_ts_extends(SiteSelectorvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
        segment: '',
        idSite
      }));
      return `?${newQuery}#?${newHash}`;
    },
    htmlEntities(v) {
      return Matomo_Matomo.helper.htmlEntities(v);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/SiteSelector/SiteSelector.vue



SiteSelectorvue_type_script_lang_ts.render = SiteSelectorvue_type_template_id_13051180_render

/* harmony default export */ var SiteSelector = (SiteSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=template&id=6c970683

const QuickAccessvue_type_template_id_6c970683_hoisted_1 = {
  ref: "root",
  class: "quickAccessInside"
};
const QuickAccessvue_type_template_id_6c970683_hoisted_2 = ["title", "placeholder"];
const QuickAccessvue_type_template_id_6c970683_hoisted_3 = {
  class: "dropdown"
};
const QuickAccessvue_type_template_id_6c970683_hoisted_4 = {
  class: "no-result"
};
const QuickAccessvue_type_template_id_6c970683_hoisted_5 = ["onClick"];
const QuickAccessvue_type_template_id_6c970683_hoisted_6 = ["onMouseenter", "onClick"];
const QuickAccessvue_type_template_id_6c970683_hoisted_7 = {
  class: "quickAccessMatomoSearch"
};
const QuickAccessvue_type_template_id_6c970683_hoisted_8 = ["onMouseenter", "onClick"];
const QuickAccessvue_type_template_id_6c970683_hoisted_9 = ["textContent"];
const QuickAccessvue_type_template_id_6c970683_hoisted_10 = {
  class: "quick-access-category helpCategory"
};
const QuickAccessvue_type_template_id_6c970683_hoisted_11 = ["href"];
function QuickAccessvue_type_template_id_6c970683_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");
  const _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", QuickAccessvue_type_template_id_6c970683_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search",
    onMouseenter: _cache[0] || (_cache[0] = $event => _ctx.searchActive = true)
  }, null, 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "s",
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.onKeypress($event)),
    onFocus: _cache[2] || (_cache[2] = $event => _ctx.searchActive = true),
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.searchTerm = $event),
    type: "text",
    tabindex: "2",
    title: _ctx.quickAccessTitle,
    placeholder: _ctx.translate('General_Search'),
    ref: "input"
  }, null, 40, QuickAccessvue_type_template_id_6c970683_hoisted_2), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.searchActive
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", QuickAccessvue_type_template_id_6c970683_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", QuickAccessvue_type_template_id_6c970683_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_SearchNoResults')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.numMenuItems > 0 || _ctx.sites.length)]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menuItems, subcategory => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
      key: subcategory.title
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      class: "quick-access-category",
      onClick: $event => {
        _ctx.searchTerm = subcategory.title;
        _ctx.searchMenu(_ctx.searchTerm);
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.title), 9, QuickAccessvue_type_template_id_6c970683_hoisted_5), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.items, submenuEntry => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["result", {
          selected: submenuEntry.menuIndex === _ctx.searchIndex
        }]),
        onMouseenter: $event => _ctx.searchIndex = submenuEntry.menuIndex,
        onClick: $event => _ctx.selectMenuItem(submenuEntry.index),
        key: submenuEntry.index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(submenuEntry.name.trim()), 1)], 42, QuickAccessvue_type_template_id_6c970683_hoisted_6);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", QuickAccessvue_type_template_id_6c970683_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: "quick-access-category websiteCategory"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Sites')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && _ctx.sites.length || _ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: "no-result"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_LoadingWebsites')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && _ctx.isLoading]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, (site, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["result", {
        selected: _ctx.numMenuItems + index === _ctx.searchIndex
      }]),
      onMouseenter: $event => _ctx.searchIndex = _ctx.numMenuItems + index,
      onClick: $event => _ctx.selectSite(site.idsite),
      key: site.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(site.name)
    }, null, 8, QuickAccessvue_type_template_id_6c970683_hoisted_9)], 42, QuickAccessvue_type_template_id_6c970683_hoisted_8)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSitesSelector && !_ctx.isLoading]]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", QuickAccessvue_type_template_id_6c970683_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_HelpResources')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
      selected: _ctx.searchIndex === 'help'
    }, "quick-access-help"]),
    onMouseenter: _cache[4] || (_cache[4] = $event => _ctx.searchIndex = 'help')
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: `https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=QuickSearch&s=${encodeURIComponent(_ctx.searchTerm)}`,
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_SearchOnMatomo', _ctx.searchTerm)), 9, QuickAccessvue_type_template_id_6c970683_hoisted_11)], 34)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm && _ctx.searchActive]])])), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=template&id=6c970683

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=script&lang=ts
function QuickAccessvue_type_script_lang_ts_extends() { QuickAccessvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return QuickAccessvue_type_script_lang_ts_extends.apply(this, arguments); }







const {
  ListingFormatter
} = window;
function isElementInViewport(element) {
  const rect = element.getBoundingClientRect();
  const $window = window.$(window);
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
    searchActive(newValue) {
      const root = this.$refs.root;
      if (!root || !root.parentElement) {
        return;
      }
      const classes = root.parentElement.classList;
      classes.toggle('active', newValue);
      classes.toggle('expanded', newValue);
    }
  },
  mounted() {
    const root = this.$refs.root;
    // this is currently needed since vue-entry code will render a div, then vue will render a div
    // within it, but the top controls and CSS expect to have certain CSS classes in the root
    // element.
    // same applies to above watch for searchActive()
    if (root && root.parentElement) {
      root.parentElement.classList.add('quick-access', 'piwikSelector');
    }
    if (typeof window.initTopControls !== 'undefined' && window.initTopControls) {
      window.initTopControls();
    }
    Matomo_Matomo.helper.registerShortcut('f', translate('CoreHome_ShortcutSearch'), event => {
      if (event.altKey) {
        return;
      }
      event.preventDefault();
      scrollFirstElementIntoView(this.$refs.root);
      this.activateSearch();
    });
  },
  data() {
    const hasSegmentSelector = !!document.querySelector('.segmentEditorPanel');
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
      hasSegmentSelector,
      sites: [],
      isLoading: false
    };
  },
  created() {
    this.searchMenu = debounce(this.searchMenu.bind(this));
  },
  computed: {
    hasSitesSelector() {
      return !!document.querySelector('.top_controls .siteSelector,.top_controls [vue-entry="CoreHome.SiteSelector"]');
    },
    quickAccessTitle() {
      const searchAreas = [translate('CoreHome_MenuEntries')];
      if (this.hasSegmentSelector) {
        searchAreas.push(translate('CoreHome_Segments'));
      }
      if (this.hasSitesSelector) {
        searchAreas.push(translate('SitesManager_Sites'));
      }
      return translate('CoreHome_QuickAccessTitle', ListingFormatter.formatAnd(searchAreas));
    }
  },
  emits: ['itemSelected', 'blur'],
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
      } else {
        setTimeout(() => {
          this.searchActive = true;
          this.searchMenu(this.searchTerm);
        });
      }
    },
    highlightPreviousItem() {
      if (this.searchIndex - 1 < 0) {
        this.searchIndex = 0;
      } else {
        this.searchIndex -= 1;
      }
      this.makeSureSelectedItemIsInViewport();
    },
    highlightNextItem() {
      const numTotal = this.$refs.root.querySelectorAll('li.result').length;
      if (numTotal <= this.searchIndex + 1) {
        this.searchIndex = numTotal - 1;
      } else {
        this.searchIndex += 1;
      }
      this.makeSureSelectedItemIsInViewport();
    },
    clickQuickAccessMenuItem() {
      const selectedMenuElement = this.getCurrentlySelectedElement();
      if (selectedMenuElement) {
        setTimeout(() => {
          selectedMenuElement.click();
          this.$emit('itemSelected', selectedMenuElement);
        }, 20);
      }
    },
    deactivateSearch() {
      this.searchTerm = '';
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
      const results = this.$refs.root.querySelectorAll('li.result');
      if (results && results.length && results.item(this.searchIndex)) {
        return results.item(this.searchIndex);
      }
      return undefined;
    },
    searchMenu(unprocessedSearchTerm) {
      const searchTerm = unprocessedSearchTerm.toLowerCase();
      let index = -1;
      const menuItemsIndex = {};
      const menuItems = [];
      const moveToCategory = theSubmenuItem => {
        // force rerender of element to prevent weird side effects
        const submenuItem = QuickAccessvue_type_script_lang_ts_extends({}, theSubmenuItem);
        // needed for proper highlighting with arrow keys
        index += 1;
        submenuItem.menuIndex = index;
        const {
          category
        } = submenuItem;
        if (!(category in menuItemsIndex)) {
          menuItems.push({
            title: category,
            items: []
          });
          menuItemsIndex[category] = menuItems.length - 1;
        }
        const indexOfCategory = menuItemsIndex[category];
        menuItems[indexOfCategory].items.push(submenuItem);
      };
      this.resetSearchIndex();
      if (this.hasSitesSelector) {
        this.isLoading = true;
        SiteSelector_SitesStore.searchSite(searchTerm).then(sites => {
          if (sites) {
            this.sites = sites;
          }
        }).finally(() => {
          this.isLoading = false;
        });
      }
      const menuItemMatches = i => i.name.toLowerCase().indexOf(searchTerm) !== -1 || i.category.toLowerCase().indexOf(searchTerm) !== -1;
      // get the menu items on first search since this component can be mounted
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
      const topMenuItems = this.topMenuItems.filter(menuItemMatches);
      const leftMenuItems = this.leftMenuItems.filter(menuItemMatches);
      const segmentItems = this.segmentItems.filter(menuItemMatches);
      topMenuItems.forEach(moveToCategory);
      leftMenuItems.forEach(moveToCategory);
      segmentItems.forEach(moveToCategory);
      this.numMenuItems = topMenuItems.length + leftMenuItems.length + segmentItems.length;
      this.menuItems = menuItems;
    },
    resetSearchIndex() {
      this.searchIndex = 0;
      this.makeSureSelectedItemIsInViewport();
    },
    selectSite(idSite) {
      SiteSelector_SitesStore.loadSite(idSite);
    },
    selectMenuItem(index) {
      const target = document.querySelector(`[quick_access='${index}']`);
      if (target) {
        this.deactivateSearch();
        const href = target.getAttribute('href');
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
    onBlur() {
      this.searchActive = false;
      this.$emit('blur');
    },
    activateSearch() {
      this.searchActive = true;
    },
    getTopMenuItems() {
      const category = translate('CoreHome_Menu');
      const topMenuItems = [];
      document.querySelectorAll('nav .sidenav li > a, nav .sidenav li > div > a').forEach(element => {
        var _element$textContent;
        let text = (_element$textContent = element.textContent) === null || _element$textContent === void 0 ? void 0 : _element$textContent.trim();
        if (!text || element.parentElement != null && element.parentElement.tagName != null && element.parentElement.tagName === 'DIV') {
          var _element$getAttribute;
          text = (_element$getAttribute = element.getAttribute('title')) === null || _element$getAttribute === void 0 ? void 0 : _element$getAttribute.trim(); // possibly a icon, use title instead
        }
        if (text) {
          topMenuItems.push({
            name: text,
            index: this.menuIndexCounter += 1,
            category
          });
          element.setAttribute('quick_access', `${this.menuIndexCounter}`);
        }
      });
      return topMenuItems;
    },
    getLeftMenuItems() {
      const leftMenuItems = [];
      document.querySelectorAll('#secondNavBar .menuTab').forEach(element => {
        var _categoryElement$;
        const categoryElement = window.$(element).find('> .item');
        let category = ((_categoryElement$ = categoryElement[0]) === null || _categoryElement$ === void 0 ? void 0 : _categoryElement$.innerText.trim()) || '';
        if (category && category.lastIndexOf('\n') !== -1) {
          // remove "\n\nMenu"
          category = category.slice(0, category.lastIndexOf('\n')).trim();
        }
        window.$(element).find('li .item').each((i, subElement) => {
          var _subElement$textConte;
          const text = (_subElement$textConte = subElement.textContent) === null || _subElement$textConte === void 0 ? void 0 : _subElement$textConte.trim();
          if (text) {
            leftMenuItems.push({
              name: text,
              category,
              index: this.menuIndexCounter += 1
            });
            subElement.setAttribute('quick_access', `${this.menuIndexCounter}`);
          }
        });
      });
      return leftMenuItems;
    },
    getSegmentItems() {
      if (!this.hasSegmentSelector) {
        return [];
      }
      const category = translate('CoreHome_Segments');
      const segmentItems = [];
      document.querySelectorAll('.segmentList [data-idsegment]').forEach(element => {
        var _element$querySelecto;
        const text = (_element$querySelecto = element.querySelector('.segname')) === null || _element$querySelecto === void 0 || (_element$querySelecto = _element$querySelecto.textContent) === null || _element$querySelecto === void 0 ? void 0 : _element$querySelecto.trim();
        if (text) {
          segmentItems.push({
            name: text,
            category,
            index: this.menuIndexCounter += 1
          });
          element.setAttribute('quick_access', `${this.menuIndexCounter}`);
        }
      });
      return segmentItems;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/QuickAccess/QuickAccess.vue



QuickAccessvue_type_script_lang_ts.render = QuickAccessvue_type_template_id_6c970683_render

/* harmony default export */ var QuickAccess = (QuickAccessvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=template&id=29bea072

const FieldArrayvue_type_template_id_29bea072_hoisted_1 = {
  class: "fieldArray form-group"
};
const FieldArrayvue_type_template_id_29bea072_hoisted_2 = {
  key: 0,
  class: "fieldUiControl"
};
const FieldArrayvue_type_template_id_29bea072_hoisted_3 = ["onClick", "title"];
function FieldArrayvue_type_template_id_29bea072_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldArrayvue_type_template_id_29bea072_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.modelValue, (item, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fieldArrayTable multiple valign-wrapper", {
        [`fieldArrayTable${index}`]: true
      }]),
      key: index
    }, [_ctx.field.uiControl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldArrayvue_type_template_id_29bea072_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      "model-value": item,
      options: _ctx.field.availableValues,
      "onUpdate:modelValue": $event => _ctx.onEntryChange($event, index),
      "model-modifiers": _ctx.field.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field.uiControl,
      title: _ctx.field.title,
      name: `${_ctx.name}-${index}`,
      "template-file": _ctx.field.templateFile,
      component: _ctx.field.component
    }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "title", "name", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      onClick: $event => _ctx.removeEntry(index),
      class: "icon-minus valign",
      title: _ctx.translate('General_Remove')
    }, null, 8, FieldArrayvue_type_template_id_29bea072_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.modelValue.length]])], 2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=template&id=29bea072

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=script&lang=ts


// async since this is a a recursive component
const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
/* harmony default export */ var FieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    name: String,
    field: Object,
    rows: String
  },
  components: {
    Field
  },
  emits: ['update:modelValue'],
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
      // make sure there is always an empty new value
      if ((!newValue || !newValue.length || newValue.slice(-1)[0] !== '') && (!this.rows || (this.modelValue || []).length < parseInt(this.rows, 10))) {
        this.$emit('update:modelValue', [...(newValue || []), '']);
      }
    },
    onEntryChange(newValue, index) {
      const newArrayValue = [...(this.modelValue || [])];
      newArrayValue[index] = newValue;
      this.$emit('update:modelValue', newArrayValue);
    },
    removeEntry(index) {
      if (index > -1 && this.modelValue) {
        const newValue = this.modelValue.filter((x, i) => i !== index);
        this.$emit('update:modelValue', newValue);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/FieldArray/FieldArray.vue



FieldArrayvue_type_script_lang_ts.render = FieldArrayvue_type_template_id_29bea072_render

/* harmony default export */ var FieldArray = (FieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=template&id=bfce275a

const MultiPairFieldvue_type_template_id_bfce275a_hoisted_1 = {
  class: "multiPairField form-group"
};
const MultiPairFieldvue_type_template_id_bfce275a_hoisted_2 = {
  key: 1,
  class: "fieldUiControl fieldUiControl2"
};
const MultiPairFieldvue_type_template_id_bfce275a_hoisted_3 = {
  key: 2,
  class: "fieldUiControl fieldUiControl3"
};
const MultiPairFieldvue_type_template_id_bfce275a_hoisted_4 = {
  key: 3,
  class: "fieldUiControl fieldUiControl4"
};
const MultiPairFieldvue_type_template_id_bfce275a_hoisted_5 = ["onClick", "title"];
function MultiPairFieldvue_type_template_id_bfce275a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_bfce275a_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.modelValue, (item, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multiPairFieldTable multiple valign-wrapper", {
        [`multiPairFieldTable${index}`]: true,
        [`has${_ctx.fieldCount}Fields`]: true
      }]),
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
      "onUpdate:modelValue": $event => _ctx.onEntryChange(index, _ctx.field1.key, $event),
      "model-modifiers": _ctx.field1.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field1.uiControl,
      name: `${_ctx.name}-p1-${index}`,
      title: _ctx.field1.title,
      "template-file": _ctx.field1.templateFile,
      component: _ctx.field1.component
    }, null, 8, ["model-value", "options", "onUpdate:modelValue", "model-modifiers", "uicontrol", "name", "title", "template-file", "component"])], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_bfce275a_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field2.availableValues,
      "onUpdate:modelValue": $event => _ctx.onEntryChange(index, _ctx.field2.key, $event),
      "model-value": item[_ctx.field2.key],
      "model-modifiers": _ctx.field2.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field2.uiControl,
      name: `${_ctx.name}-p2-${index}`,
      title: _ctx.field2.title,
      "template-file": _ctx.field2.templateFile,
      component: _ctx.field2.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "name", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field3 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_bfce275a_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field3.availableValues,
      "onUpdate:modelValue": $event => _ctx.onEntryChange(index, _ctx.field3.key, $event),
      "model-value": item[_ctx.field3.key],
      "model-modifiers": _ctx.field3.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field3.uiControl,
      title: _ctx.field3.title,
      "template-file": _ctx.field3.templateFile,
      component: _ctx.field3.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.field4 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultiPairFieldvue_type_template_id_bfce275a_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "full-width": true,
      options: _ctx.field4.availableValues,
      "onUpdate:modelValue": $event => _ctx.onEntryChange(index, _ctx.field4.key, $event),
      "model-value": item[_ctx.field4.key],
      "model-modifiers": _ctx.field4.modelModifiers,
      placeholder: ' ',
      uicontrol: _ctx.field4.uiControl,
      title: _ctx.field4.title,
      "template-file": _ctx.field4.templateFile,
      component: _ctx.field4.component
    }, null, 8, ["options", "onUpdate:modelValue", "model-value", "model-modifiers", "uicontrol", "title", "template-file", "component"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      onClick: $event => _ctx.removeEntry(index),
      class: "icon-minus valign",
      title: _ctx.translate('General_Remove')
    }, null, 8, MultiPairFieldvue_type_template_id_bfce275a_hoisted_5), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.modelValue.length]])], 2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=template&id=bfce275a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MultiPairField/MultiPairField.vue?vue&type=script&lang=ts
function MultiPairFieldvue_type_script_lang_ts_extends() { MultiPairFieldvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return MultiPairFieldvue_type_script_lang_ts_extends.apply(this, arguments); }


// async since this is a recursive component
const MultiPairFieldvue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
/* harmony default export */ var MultiPairFieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    name: String,
    field1: Object,
    field2: Object,
    field3: Object,
    field4: Object,
    rows: Number
  },
  components: {
    Field: MultiPairFieldvue_type_script_lang_ts_Field
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
  emits: ['update:modelValue'],
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
      // make sure there is always an empty new value
      if ((!newValue || !newValue.length || this.isEmptyValue(newValue.slice(-1)[0])) && (!this.rows || this.modelValue.length < this.rows)) {
        this.$emit('update:modelValue', [...(newValue || []), this.makeEmptyValue()]);
      }
    },
    onEntryChange(index, key, newValue) {
      const newWholeValue = [...this.modelValue];
      newWholeValue[index] = MultiPairFieldvue_type_script_lang_ts_extends(MultiPairFieldvue_type_script_lang_ts_extends({}, newWholeValue[index]), {}, {
        [key]: newValue
      });
      this.$emit('update:modelValue', newWholeValue);
    },
    removeEntry(index) {
      if (index > -1 && this.modelValue) {
        const newValue = this.modelValue.filter((x, i) => i !== index);
        this.$emit('update:modelValue', newValue);
      }
    },
    isEmptyValue(value) {
      const {
        fieldCount
      } = this;
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



MultiPairFieldvue_type_script_lang_ts.render = MultiPairFieldvue_type_template_id_bfce275a_render

/* harmony default export */ var MultiPairField = (MultiPairFieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=template&id=facf55b4

const PeriodSelectorvue_type_template_id_facf55b4_hoisted_1 = ["disabled"];
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-left"
}, null, -1);
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_3 = [PeriodSelectorvue_type_template_id_facf55b4_hoisted_2];
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_4 = ["title"];
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-calendar"
}, null, -1);
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_6 = {
  id: "periodMore",
  class: "dropdown"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_7 = {
  class: "flex"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_8 = {
  key: 0,
  class: "period-date"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_9 = {
  class: "period-type"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_10 = {
  id: "otherPeriods"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_11 = ["onDblclick", "title"];
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_12 = ["id", "checked", "onChange", "onDblclick"];
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_13 = {
  key: 0,
  class: "compare-checkbox"
};
const PeriodSelectorvue_type_template_id_facf55b4_hoisted_14 = {
  id: "comparePeriodToDropdown"
};
const _hoisted_15 = {
  key: 1,
  class: "compare-date-range"
};
const _hoisted_16 = {
  id: "comparePeriodStartDate"
};
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "compare-dates-separator"
}, null, -1);
const _hoisted_18 = {
  id: "comparePeriodEndDate"
};
const _hoisted_19 = {
  class: "apply-button-container"
};
const _hoisted_20 = ["disabled", "value"];
const _hoisted_21 = {
  key: 2,
  id: "ajaxLoadingCalendar"
};
const _hoisted_22 = {
  class: "loadingSegment"
};
const _hoisted_23 = ["disabled"];
const _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-right"
}, null, -1);
const _hoisted_25 = [_hoisted_24];
function PeriodSelectorvue_type_template_id_facf55b4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DateRangePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DateRangePicker");
  const _component_PeriodDatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PeriodDatePicker");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _directive_expand_on_click = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-click");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    ref: "root",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["periodSelector piwikSelector", {
      'periodSelector-withPrevNext': _ctx.canShowMovePeriod
    }])
  }, [_ctx.canShowMovePeriod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    class: "move-period move-period-prev",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.movePeriod(-1)),
    disabled: _ctx.isPeriodMoveDisabled(-1)
  }, PeriodSelectorvue_type_template_id_facf55b4_hoisted_3, 8, PeriodSelectorvue_type_template_id_facf55b4_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    ref: "title",
    id: "date",
    class: "title",
    tabindex: "-1",
    title: _ctx.translate('General_ChooseDate', _ctx.currentlyViewingText)
  }, [PeriodSelectorvue_type_template_id_facf55b4_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentlyViewingText), 1)], 8, PeriodSelectorvue_type_template_id_facf55b4_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DateRangePicker, {
    class: "period-range",
    "start-date": _ctx.startRangeDate,
    "end-date": _ctx.endRangeDate,
    onRangeChange: _cache[1] || (_cache[1] = $event => _ctx.onRangeChange($event.start, $event.end)),
    onSubmit: _cache[2] || (_cache[2] = $event => _ctx.onApplyClicked())
  }, null, 8, ["start-date", "end-date"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.selectedPeriod === 'range']]), _ctx.selectedPeriod !== 'range' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PeriodDatePicker, {
    id: "datepicker",
    period: _ctx.selectedPeriod,
    date: _ctx.periodValue === _ctx.selectedPeriod ? _ctx.dateValue : null,
    onSelect: _cache[3] || (_cache[3] = $event => _ctx.setPiwikPeriodAndDate(_ctx.selectedPeriod, $event.date))
  }, null, 8, ["period", "date"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Period')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_10, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periodsFiltered, period => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: period
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'selected-period-label': period === _ctx.selectedPeriod
      }),
      onDblclick: $event => _ctx.changeViewedPeriod(period),
      title: period === _ctx.periodValue ? '' : _ctx.translate('General_DoubleClickToChangePeriod')
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      name: "period",
      id: `period_id_${period}`,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.selectedPeriod = $event),
      checked: _ctx.selectedPeriod === period,
      onChange: $event => _ctx.selectedPeriod = period,
      onDblclick: $event => _ctx.changeViewedPeriod(period)
    }, null, 40, PeriodSelectorvue_type_template_id_facf55b4_hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.selectedPeriod]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getPeriodDisplayText(period)), 1)], 42, PeriodSelectorvue_type_template_id_facf55b4_hoisted_11)]);
  }), 128))])])]), _ctx.isComparisonEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "comparePeriodTo",
    type: "checkbox",
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.isComparing = $event)
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.isComparing]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_CompareTo')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodSelectorvue_type_template_id_facf55b4_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.comparePeriodType,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.comparePeriodType = $event),
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
      'visibility': _ctx.isComparing ? 'visible' : 'hidden'
    }),
    name: 'comparePeriodToDropdown',
    uicontrol: 'select',
    options: _ctx.comparePeriodDropdownOptions,
    "full-width": true,
    disabled: !_ctx.isComparing
  }, null, 8, ["modelValue", "style", "options", "disabled"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isComparing && _ctx.comparePeriodType === 'custom' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.compareStartDate,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.compareStartDate = $event),
    name: 'comparePeriodStartDate',
    uicontrol: 'text',
    "full-width": true,
    title: _ctx.translate('CoreHome_StartDate'),
    placeholder: 'YYYY-MM-DD'
  }, null, 8, ["modelValue", "title"])])]), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.compareEndDate,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.compareEndDate = $event),
    name: 'comparePeriodEndDate',
    uicontrol: 'text',
    "full-width": true,
    title: _ctx.translate('CoreHome_EndDate'),
    placeholder: 'YYYY-MM-DD'
  }, null, 8, ["modelValue", "title"])])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "submit",
    id: "calendarApply",
    class: "btn",
    onClick: _cache[9] || (_cache[9] = $event => _ctx.onApplyClicked()),
    disabled: !_ctx.isApplyEnabled(),
    value: _ctx.translate('General_Apply')
  }, null, 8, _hoisted_20)]), _ctx.isLoadingNewPage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.canShowMovePeriod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 1,
    class: "move-period move-period-next",
    onClick: _cache[10] || (_cache[10] = $event => _ctx.movePeriod(1)),
    disabled: _ctx.isPeriodMoveDisabled(1)
  }, _hoisted_25, 8, _hoisted_23)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)), [[_directive_expand_on_click, {
    expander: 'title'
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=template&id=facf55b4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=script&lang=ts
function PeriodSelectorvue_type_script_lang_ts_extends() { PeriodSelectorvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return PeriodSelectorvue_type_script_lang_ts_extends.apply(this, arguments); }











const PeriodSelectorvue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
const NBSP = Matomo_Matomo.helper.htmlDecode('&nbsp;');
const COMPARE_PERIOD_TYPES = ['custom', 'previousPeriod', 'previousYear'];
const COMPARE_PERIOD_OPTIONS = [{
  key: 'custom',
  value: translate('General_Custom')
}, {
  key: 'previousPeriod',
  value: translate('General_PreviousPeriod').replace(/\s+/, NBSP)
}, {
  key: 'previousYear',
  value: translate('General_PreviousYear').replace(/\s+/, NBSP)
}];
// the date when the site was created
const PeriodSelectorvue_type_script_lang_ts_piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
// today/now
const PeriodSelectorvue_type_script_lang_ts_piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);
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
  data() {
    const selectedPeriod = src_MatomoUrl_MatomoUrl.parsed.value.period;
    return {
      comparePeriodDropdownOptions: COMPARE_PERIOD_OPTIONS,
      periodValue: selectedPeriod,
      dateValue: null,
      selectedPeriod,
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
  mounted() {
    Matomo_Matomo.on('hidePeriodSelector', () => {
      window.$(this.$refs.root).parent('#periodString').hide();
    });
    // some widgets might hide the period selector using the event above, so ensure it's
    // shown again when switching the page
    Matomo_Matomo.on('matomoPageChange', () => {
      window.$(this.$refs.root).parent('#periodString').show();
    });
    this.isComparing = Comparisons_store_instance.isComparingPeriods();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => Comparisons_store_instance.isComparingPeriods(), newVal => {
      this.isComparing = newVal;
    });
    this.updateSelectedValuesFromHash();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.parsed.value, this.updateSelectedValuesFromHash);
    this.updateComparisonValuesFromStore();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => Comparisons_store_instance.getPeriodComparisons(), this.updateComparisonValuesFromStore);
    window.initTopControls(); // must be called when a top control changes width
    this.handleZIndexPositionRelativeCompareDropdownIssue();
  },
  computed: {
    currentlyViewingText() {
      let date;
      if (this.periodValue === 'range') {
        if (!this.startRangeDate || !this.endRangeDate) {
          return translate('General_Error');
        }
        date = `${this.startRangeDate},${this.endRangeDate}`;
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
    isComparisonEnabled() {
      return Comparisons_store_instance.isComparisonEnabled();
    },
    periodsFiltered() {
      return (this.periods || []).filter(periodLabel => Periods_Periods.isRecognizedPeriod(periodLabel));
    },
    selectedComparisonParams() {
      if (!this.isComparing) {
        return {};
      }
      if (this.comparePeriodType === 'custom') {
        return {
          comparePeriods: ['range'],
          comparePeriodType: 'custom',
          compareDates: [`${this.compareStartDate},${this.compareEndDate}`]
        };
      }
      if (this.comparePeriodType === 'previousPeriod') {
        return {
          comparePeriods: [this.selectedPeriod],
          comparePeriodType: 'previousPeriod',
          compareDates: [this.previousPeriodDateToSelectedPeriod]
        };
      }
      if (this.comparePeriodType === 'previousYear') {
        const dateStr = this.selectedPeriod === 'range' ? `${this.startRangeDate},${this.endRangeDate}` : format(this.dateValue);
        const currentDateRange = Periods_Periods.parse(this.selectedPeriod, dateStr).getDateRange();
        currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
        currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);
        if (this.selectedPeriod === 'range') {
          return {
            comparePeriods: ['range'],
            comparePeriodType: 'previousYear',
            compareDates: [`${format(currentDateRange[0])},${format(currentDateRange[1])}`]
          };
        }
        return {
          comparePeriods: [this.selectedPeriod],
          comparePeriodType: 'previousYear',
          compareDates: [format(currentDateRange[0])]
        };
      }
      console.warn(`Unknown compare period type: ${this.comparePeriodType}`);
      return {};
    },
    previousPeriodDateToSelectedPeriod() {
      if (this.selectedPeriod === 'range') {
        const currentStartRange = parseDate(this.startRangeDate);
        const currentEndRange = parseDate(this.endRangeDate);
        const newEndDate = Range_RangePeriod.getLastNRange('day', 2, currentStartRange).startDate;
        const rangeSize = Math.floor((currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000);
        const newRange = Range_RangePeriod.getLastNRange('day', 1 + rangeSize, newEndDate);
        return `${format(newRange.startDate)},${format(newRange.endDate)}`;
      }
      const newStartDate = Range_RangePeriod.getLastNRange(this.selectedPeriod, 2, this.dateValue).startDate;
      return format(newStartDate);
    },
    selectedDateString() {
      if (this.selectedPeriod === 'range') {
        const dateFrom = this.startRangeDate;
        const dateTo = this.endRangeDate;
        const oDateFrom = parseDate(dateFrom);
        const oDateTo = parseDate(dateTo);
        if (!isValidDate(oDateFrom) || !isValidDate(oDateTo) || oDateFrom > oDateTo) {
          // TODO: use a notification instead?
          window.$('#alert').find('h2').text(translate('General_InvalidDateRange'));
          Matomo_Matomo.helper.modalConfirm('#alert', {});
          return null;
        }
        return `${dateFrom},${dateTo}`;
      }
      return format(this.dateValue);
    },
    isErrorDisplayed() {
      return this.currentlyViewingText === translate('General_Error');
    },
    isRangeSelection() {
      return this.periodValue === 'range';
    },
    canShowMovePeriod() {
      return !this.isRangeSelection && !this.isErrorDisplayed;
    }
  },
  methods: {
    handleZIndexPositionRelativeCompareDropdownIssue() {
      const $element = window.$(this.$refs.root);
      $element.on('focus', '#comparePeriodToDropdown .select-dropdown', () => {
        $element.addClass('compare-dropdown-open');
      }).on('blur', '#comparePeriodToDropdown .select-dropdown', () => {
        $element.removeClass('compare-dropdown-open');
      });
    },
    changeViewedPeriod(period) {
      // only change period if it's different from what's being shown currently
      if (period === this.periodValue) {
        return;
      }
      // can't just change to a range period, w/o setting two new dates
      if (period === 'range') {
        return;
      }
      this.setPiwikPeriodAndDate(period, this.dateValue);
    },
    setPiwikPeriodAndDate(period, date) {
      this.periodValue = period;
      this.selectedPeriod = period;
      this.dateValue = date;
      const currentDateString = format(date);
      this.setRangeStartEndFromPeriod(period, currentDateString);
      this.propagateNewUrlParams(currentDateString, this.selectedPeriod);
      window.initTopControls();
    },
    propagateNewUrlParams(date, period) {
      const compareParams = this.selectedComparisonParams;
      let baseParams;
      if (Matomo_Matomo.helper.isReportingPage()) {
        this.closePeriodSelector();
        baseParams = src_MatomoUrl_MatomoUrl.hashParsed.value;
      } else {
        this.isLoadingNewPage = true;
        baseParams = src_MatomoUrl_MatomoUrl.parsed.value;
      }
      // get params without comparePeriods/compareSegments/compareDates
      const paramsWithoutCompare = PeriodSelectorvue_type_script_lang_ts_extends({}, baseParams);
      delete paramsWithoutCompare.comparePeriods;
      delete paramsWithoutCompare.comparePeriodType;
      delete paramsWithoutCompare.compareDates;
      src_MatomoUrl_MatomoUrl.updateLocation(PeriodSelectorvue_type_script_lang_ts_extends(PeriodSelectorvue_type_script_lang_ts_extends({}, paramsWithoutCompare), {}, {
        date,
        period
      }, compareParams));
    },
    onApplyClicked() {
      if (this.selectedPeriod === 'range') {
        const dateString = this.selectedDateString;
        if (!dateString) {
          return;
        }
        this.periodValue = 'range';
        this.propagateNewUrlParams(dateString, 'range');
        return;
      }
      this.setPiwikPeriodAndDate(this.selectedPeriod, this.dateValue);
    },
    updateComparisonValuesFromStore() {
      this.comparePeriodType = 'previousPeriod';
      this.compareStartDate = '';
      this.compareEndDate = '';
      // first is selected period, second is period to compare to
      const comparePeriods = Comparisons_store_instance.getPeriodComparisons();
      if (comparePeriods.length < 2) {
        return;
      }
      const comparePeriodType = src_MatomoUrl_MatomoUrl.parsed.value.comparePeriodType;
      if (!COMPARE_PERIOD_TYPES.includes(comparePeriodType)) {
        return;
      }
      this.comparePeriodType = comparePeriodType;
      if (this.comparePeriodType !== 'custom' || comparePeriods[1].params.period !== 'range') {
        return;
      }
      let periodObj;
      try {
        periodObj = Periods_Periods.parse(comparePeriods[1].params.period, comparePeriods[1].params.date);
      } catch (_unused) {
        return;
      }
      const [startDate, endDate] = periodObj.getDateRange();
      this.compareStartDate = format(startDate);
      this.compareEndDate = format(endDate);
    },
    updateSelectedValuesFromHash() {
      const date = src_MatomoUrl_MatomoUrl.parsed.value.date;
      const period = src_MatomoUrl_MatomoUrl.parsed.value.period;
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
        const periodObj = Periods_Periods.get(period).parse(date);
        const [startDate, endDate] = periodObj.getDateRange();
        this.dateValue = startDate;
        this.startRangeDate = format(startDate);
        this.endRangeDate = format(endDate);
      } else {
        this.dateValue = parseDate(date);
        this.setRangeStartEndFromPeriod(period, date);
      }
    },
    setRangeStartEndFromPeriod(period, dateStr) {
      const dateRange = Periods_Periods.parse(period, dateStr).getDateRange();
      this.startRangeDate = format(dateRange[0] < PeriodSelectorvue_type_script_lang_ts_piwikMinDate ? PeriodSelectorvue_type_script_lang_ts_piwikMinDate : dateRange[0]);
      this.endRangeDate = format(dateRange[1] > PeriodSelectorvue_type_script_lang_ts_piwikMaxDate ? PeriodSelectorvue_type_script_lang_ts_piwikMaxDate : dateRange[1]);
    },
    getPeriodDisplayText(periodLabel) {
      return Periods_Periods.get(periodLabel).getDisplayText();
    },
    onRangeChange(start, end) {
      if (!start || !end) {
        this.isRangeValid = false;
        return;
      }
      this.isRangeValid = true;
      this.startRangeDate = start;
      this.endRangeDate = end;
    },
    isApplyEnabled() {
      if (this.selectedPeriod === 'range' && !this.isRangeValid) {
        return false;
      }
      if (this.isComparing && this.comparePeriodType === 'custom' && !this.isCompareRangeValid()) {
        return false;
      }
      return true;
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
      let newDate = new Date();
      if (this.dateValue != null) {
        newDate = this.dateValue;
      }
      switch (this.periodValue) {
        case 'day':
          newDate.setDate(newDate.getDate() + direction);
          break;
        case 'week':
          newDate.setDate(newDate.getDate() + direction * 7);
          break;
        case 'month':
          newDate.setMonth(newDate.getMonth() + direction);
          break;
        case 'year':
          newDate.setFullYear(newDate.getFullYear() + direction);
          break;
        default:
          break;
      }
      // Ensure the date is not outside the min and max dates
      if (this.dateValue < PeriodSelectorvue_type_script_lang_ts_piwikMinDate) {
        this.dateValue = PeriodSelectorvue_type_script_lang_ts_piwikMinDate;
      }
      if (this.dateValue > PeriodSelectorvue_type_script_lang_ts_piwikMaxDate) {
        this.dateValue = PeriodSelectorvue_type_script_lang_ts_piwikMaxDate;
      }
      this.onApplyClicked();
    },
    isPeriodMoveDisabled(direction) {
      // disable period move when date range is used or when we would go out of the min/max dates
      if (this.dateValue === null) {
        return this.isRangeSelection;
      }
      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction) {
      if (this.dateValue === null) {
        return false;
      }
      const boundaryDate = direction === -1 ? PeriodSelectorvue_type_script_lang_ts_piwikMinDate : PeriodSelectorvue_type_script_lang_ts_piwikMaxDate;
      return !datesAreInTheSamePeriod(this.dateValue, boundaryDate, this.periodValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodSelector/PeriodSelector.vue



PeriodSelectorvue_type_script_lang_ts.render = PeriodSelectorvue_type_template_id_facf55b4_render

/* harmony default export */ var PeriodSelector = (PeriodSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=7af256f9

const ReportingMenuvue_type_template_id_7af256f9_hoisted_1 = {
  class: "reportingMenu"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_2 = ["aria-label"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_3 = ["data-category-id"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_4 = ["onClick"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_5 = {
  class: "hidden"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_6 = {
  key: 2,
  role: "menu"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_7 = ["href", "onClick", "title"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_8 = ["href", "onClick"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_9 = ["onClick"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const ReportingMenuvue_type_template_id_7af256f9_hoisted_11 = [ReportingMenuvue_type_template_id_7af256f9_hoisted_10];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_12 = {
  id: "mobile-left-menu",
  class: "sidenav sidenav--reporting-menu-mobile hide-on-large-only"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_13 = ["data-category-id"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_14 = {
  key: 1,
  class: "collapsible collapsible-accordion"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_15 = {
  class: "collapsible-header"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_16 = {
  class: "collapsible-body"
};
const ReportingMenuvue_type_template_id_7af256f9_hoisted_17 = ["onClick", "href"];
const ReportingMenuvue_type_template_id_7af256f9_hoisted_18 = ["onClick", "href"];
function ReportingMenuvue_type_template_id_7af256f9_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MenuItemsDropdown = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MenuItemsDropdown");
  const _directive_side_nav = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("side-nav");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingMenuvue_type_template_id_7af256f9_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
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
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ReportingMenuvue_type_template_id_7af256f9_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_Menu')), 1)], 8, ReportingMenuvue_type_template_id_7af256f9_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ReportingMenuvue_type_template_id_7af256f9_hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, subcategory => {
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
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 11, ReportingMenuvue_type_template_id_7af256f9_hoisted_7);
        }), 128))]),
        _: 2
      }, 1032, ["menu-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        href: `#?${_ctx.makeUrl(category, subcategory)}`,
        class: "item",
        onClick: $event => _ctx.loadSubcategory(category, subcategory, $event)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, ReportingMenuvue_type_template_id_7af256f9_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), subcategory.help ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 2,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["item-help-icon", {
          active: _ctx.helpShownCategory && _ctx.helpShownCategory.subcategory === subcategory.id && _ctx.helpShownCategory.category === category.id && subcategory.help
        }]),
        tabindex: "5",
        href: "javascript:",
        onClick: $event => _ctx.showHelp(category, subcategory, $event)
      }, ReportingMenuvue_type_template_id_7af256f9_hoisted_11, 10, ReportingMenuvue_type_template_id_7af256f9_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, ReportingMenuvue_type_template_id_7af256f9_hoisted_3);
  }), 128))], 8, ReportingMenuvue_type_template_id_7af256f9_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ReportingMenuvue_type_template_id_7af256f9_hoisted_12, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menu, category => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "no-padding",
      key: category.id,
      "data-category-id": category.id
    }, [category.component ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(category.component), {
      key: 0,
      onAction: $event => _ctx.loadCategory(category)
    }, null, 40, ["onAction"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !category.component ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ReportingMenuvue_type_template_id_7af256f9_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", ReportingMenuvue_type_template_id_7af256f9_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(category.icon ? category.icon : 'icon-chevron-down')
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportingMenuvue_type_template_id_7af256f9_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.subcategories, subcategory => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: subcategory.id
      }, [subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 0
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(subcategory.subcategories, subcat => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          onClick: $event => _ctx.loadSubcategory(category, subcat),
          href: `#?${_ctx.makeUrl(category, subcat)}`,
          key: subcat.id
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcat.name), 9, ReportingMenuvue_type_template_id_7af256f9_hoisted_17);
      }), 128)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !subcategory.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 1,
        onClick: $event => _ctx.loadSubcategory(category, subcategory),
        href: `#?${_ctx.makeUrl(category, subcategory)}`
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subcategory.name), 9, ReportingMenuvue_type_template_id_7af256f9_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))])])])])), [[_directive_side_nav, {
      activator: _ctx.sideNavActivator
    }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, ReportingMenuvue_type_template_id_7af256f9_hoisted_13);
  }), 128))])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.vue?vue&type=template&id=7af256f9

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPages/ReportingPages.store.ts
function ReportingPages_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class ReportingPages_store_ReportingPagesStore {
  constructor() {
    ReportingPages_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      pages: []
    }));
    ReportingPages_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    ReportingPages_store_defineProperty(this, "fetchAllPagesPromise", void 0);
    ReportingPages_store_defineProperty(this, "pages", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.pages));
  }
  findPageInCategory(categoryId) {
    // happens when user switches between sites, in this case check if the same category exists and
    // if so, select first entry from that category
    return this.pages.value.find(p => p && p.category && p.category.id === categoryId && p.subcategory && p.subcategory.id);
  }
  findPage(categoryId, subcategoryId) {
    return this.pages.value.find(p => p && p.category && p.subcategory && p.category.id === categoryId && `${p.subcategory.id}` === subcategoryId);
  }
  reloadAllPages() {
    delete this.fetchAllPagesPromise;
    return this.getAllPages();
  }
  getAllPages() {
    if (!this.fetchAllPagesPromise) {
      this.fetchAllPagesPromise = AjaxHelper_AjaxHelper.fetch({
        method: 'API.getReportPagesMetadata',
        filter_limit: '-1'
      }).then(response => {
        this.privateState.pages = response;
        return this.pages.value;
      });
    }
    return this.fetchAllPagesPromise.then(() => this.pages.value);
  }
}
/* harmony default export */ var ReportingPages_store = (new ReportingPages_store_ReportingPagesStore());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Orderable.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function sortOrderables(menu) {
  const result = [...(menu || [])];
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/Category.ts
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/Subcategory.ts
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingMenu/ReportingMenu.store.ts
function ReportingMenu_store_extends() { ReportingMenu_store_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportingMenu_store_extends.apply(this, arguments); }
function ReportingMenu_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
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
class ReportingMenu_store_ReportingMenuStore {
  constructor() {
    ReportingMenu_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      activeSubcategoryId: null,
      activeSubsubcategoryId: null
    }));
    ReportingMenu_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    ReportingMenu_store_defineProperty(this, "activeCategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => typeof this.state.value.activeCategoryId !== 'undefined' ? this.state.value.activeCategoryId : src_MatomoUrl_MatomoUrl.parsed.value.category));
    ReportingMenu_store_defineProperty(this, "activeSubcategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.activeSubcategoryId || src_MatomoUrl_MatomoUrl.parsed.value.subcategory));
    ReportingMenu_store_defineProperty(this, "activeSubsubcategory", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const manuallySetId = this.state.value.activeSubsubcategoryId;
      if (manuallySetId) {
        return manuallySetId;
      }
      // default to activeSubcategory if the activeSubcategory is part of a group
      const foundCategory = this.findSubcategory(this.activeCategory.value, this.activeSubcategory.value);
      if (foundCategory.subsubcategory && foundCategory.subsubcategory.id === this.activeSubcategory.value) {
        return foundCategory.subsubcategory.id;
      }
      return null;
    }));
    ReportingMenu_store_defineProperty(this, "menu", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.buildMenuFromPages()));
  }
  fetchMenuItems() {
    return ReportingPages_store.getAllPages().then(() => this.menu.value);
  }
  reloadMenuItems() {
    return ReportingPages_store.reloadAllPages().then(() => this.menu.value);
  }
  findSubcategory(categoryId, subcategoryId) {
    let foundCategory = undefined;
    let foundSubcategory = undefined;
    let foundSubSubcategory = undefined;
    this.menu.value.forEach(category => {
      if (category.id !== categoryId) {
        return;
      }
      (getCategoryChildren(category) || []).forEach(subcategory => {
        if (subcategory.id === subcategoryId) {
          foundCategory = category;
          foundSubcategory = subcategory;
        }
        if (subcategory.isGroup) {
          (getSubcategoryChildren(subcategory) || []).forEach(subcat => {
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
  buildMenuFromPages() {
    const menu = [];
    const displayedCategory = src_MatomoUrl_MatomoUrl.parsed.value.category;
    const displayedSubcategory = src_MatomoUrl_MatomoUrl.parsed.value.subcategory;
    const pages = ReportingPages_store.pages.value;
    const categoriesHandled = {};
    pages.forEach(page => {
      const category = ReportingMenu_store_extends({}, page.category);
      const categoryId = category.id;
      const isCategoryDisplayed = categoryId === displayedCategory;
      if (categoriesHandled[categoryId]) {
        return;
      }
      categoriesHandled[categoryId] = true;
      category.subcategories = [];
      let categoryGroups = null;
      const pagesWithCategory = pages.filter(p => p.category.id === categoryId);
      pagesWithCategory.forEach(p => {
        const subcategory = ReportingMenu_store_extends({}, p.subcategory);
        const isSubcategoryDisplayed = subcategory.id === displayedSubcategory && isCategoryDisplayed;
        if (p.widgets && p.widgets[0] && isNumeric(p.subcategory.id)) {
          // we handle a goal or something like it
          if (!categoryGroups) {
            categoryGroups = ReportingMenu_store_extends({}, subcategory);
            categoryGroups.name = translate('CoreHome_ChooseX', [category.name]);
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
        categoryGroups.subcategories.forEach(sub => category.subcategories.push(sub));
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
/* harmony default export */ var ReportingMenu_store = (new ReportingMenu_store_ReportingMenuStore());
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
function ReportingMenuvue_type_script_lang_ts_extends() { ReportingMenuvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportingMenuvue_type_script_lang_ts_extends.apply(this, arguments); }










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
    ReportingMenu_store.fetchMenuItems().then(menu => {
      if (!src_MatomoUrl_MatomoUrl.parsed.value.subcategory) {
        const categoryToLoad = menu[0];
        const subcategoryToLoad = categoryToLoad.subcategories[0];
        // load first, initial page if no subcategory is present
        ReportingMenu_store.enterSubcategory(categoryToLoad, subcategoryToLoad);
        this.propagateUrlChange(categoryToLoad, subcategoryToLoad);
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.parsed.value, query => {
      const found = ReportingMenu_store.findSubcategory(query.category, query.subcategory);
      ReportingMenu_store.enterSubcategory(found.category, found.subcategory, found.subsubcategory);
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
    propagateUrlChange(category, subcategory) {
      const queryParams = src_MatomoUrl_MatomoUrl.parsed.value;
      if (queryParams.category === category.id && queryParams.subcategory === subcategory.id) {
        // we need to manually trigger change as URL would not change and therefore page would not
        // be reloaded
        this.loadSubcategory(category, subcategory);
      } else {
        src_MatomoUrl_MatomoUrl.updateHash(ReportingMenuvue_type_script_lang_ts_extends(ReportingMenuvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
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
        compareSegments
      } = src_MatomoUrl_MatomoUrl.parsed.value;
      return src_MatomoUrl_MatomoUrl.stringify({
        idSite,
        period,
        date,
        segment,
        comparePeriods,
        compareDates,
        compareSegments,
        category: category.id,
        subcategory: subcategory.id
      });
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
        src_MatomoUrl_MatomoUrl.updateHash(ReportingMenuvue_type_script_lang_ts_extends(ReportingMenuvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
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



ReportingMenuvue_type_script_lang_ts.render = ReportingMenuvue_type_template_id_7af256f9_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=e256fdba

const WidgetLoadervue_type_template_id_e256fdba_hoisted_1 = {
  class: "widgetLoader"
};
const WidgetLoadervue_type_template_id_e256fdba_hoisted_2 = {
  key: 0
};
const WidgetLoadervue_type_template_id_e256fdba_hoisted_3 = {
  key: 1,
  class: "notification system notification-error"
};
const WidgetLoadervue_type_template_id_e256fdba_hoisted_4 = ["href"];
const WidgetLoadervue_type_template_id_e256fdba_hoisted_5 = {
  key: 2,
  class: "notification system notification-error"
};
const WidgetLoadervue_type_template_id_e256fdba_hoisted_6 = {
  class: "theWidgetContent",
  ref: "widgetContent"
};
function WidgetLoadervue_type_template_id_e256fdba_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_e256fdba_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": _ctx.finalLoadingMessage,
    loading: _ctx.loading
  }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.widgetName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", WidgetLoadervue_type_template_id_e256fdba_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.widgetName), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loadingFailedRateLimit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_e256fdba_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), _ctx.hasErrorFaqLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://matomo.org/faq/troubleshooting/faq_19489/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequestFaqLink')), 9, WidgetLoadervue_type_template_id_e256fdba_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetLoadervue_type_template_id_e256fdba_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRateLimit')), 1))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loadingFailed]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetLoadervue_type_template_id_e256fdba_hoisted_6, null, 512)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=template&id=e256fdba

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetLoader/WidgetLoader.vue?vue&type=script&lang=ts
function WidgetLoadervue_type_script_lang_ts_extends() { WidgetLoadervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return WidgetLoadervue_type_script_lang_ts_extends.apply(this, arguments); }








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
    loadingMessage: String
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
      let fullParameters = WidgetLoadervue_type_script_lang_ts_extends({}, parameters || {});
      const paramsToForward = Object.keys(WidgetLoadervue_type_script_lang_ts_extends(WidgetLoadervue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
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
        fullParameters = WidgetLoadervue_type_script_lang_ts_extends(WidgetLoadervue_type_script_lang_ts_extends({}, fullParameters), {}, {
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
      AjaxHelper_AjaxHelper.fetch(this.getWidgetUrl(parameters), {
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
        Notifications_store.parseNotificationDivs();
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



WidgetLoadervue_type_script_lang_ts.render = WidgetLoadervue_type_template_id_e256fdba_render

/* harmony default export */ var WidgetLoader = (WidgetLoadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=template&id=73071e47

const WidgetContainervue_type_template_id_73071e47_hoisted_1 = {
  class: "widget-container"
};
function WidgetContainervue_type_template_id_73071e47_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Widget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Widget");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetContainervue_type_template_id_73071e47_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.actualContainer, (widget, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Widget, {
      widget: widget,
      "prevent-recursion": true
    }, null, 8, ["widget"])])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=template&id=73071e47

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=script&lang=ts
function WidgetContainervue_type_script_lang_ts_extends() { WidgetContainervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return WidgetContainervue_type_script_lang_ts_extends.apply(this, arguments); }


// since we're recursing, don't import the plugin directly
const Widget = useExternalPluginComponent('CoreHome', 'Widget');
/* harmony default export */ var WidgetContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    container: {
      type: Array,
      required: true
    }
  },
  components: {
    Widget
  },
  computed: {
    actualContainer() {
      var _container$, _widget$parameters, _widget$parameters2;
      const container = this.container;
      if (!(container !== null && container !== void 0 && (_container$ = container[0]) !== null && _container$ !== void 0 && _container$.parameters)) {
        return container;
      }
      const [widget] = container;
      const isWidgetized = ((_widget$parameters = widget.parameters) === null || _widget$parameters === void 0 ? void 0 : _widget$parameters.widget) === '1' || ((_widget$parameters2 = widget.parameters) === null || _widget$parameters2 === void 0 ? void 0 : _widget$parameters2.widget) === 1;
      const isGraphEvolution = isWidgetized && widget.viewDataTable === 'graphEvolution';
      // we hide the first title for Visits Overview with Graph and Goal Overview
      const firstWidget = isGraphEvolution ? WidgetContainervue_type_script_lang_ts_extends(WidgetContainervue_type_script_lang_ts_extends({}, widget), {}, {
        parameters: WidgetContainervue_type_script_lang_ts_extends(WidgetContainervue_type_script_lang_ts_extends({}, widget.parameters), {}, {
          showtitle: '0'
        })
      }) : widget;
      return [firstWidget, ...container.slice(1)];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetContainer/WidgetContainer.vue



WidgetContainervue_type_script_lang_ts.render = WidgetContainervue_type_template_id_73071e47_render

/* harmony default export */ var WidgetContainer = (WidgetContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=template&id=ad0a8c4a

const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_1 = {
  class: "reportsByDimensionView"
};
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_2 = {
  class: "entityList"
};
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_3 = {
  class: "listCircle"
};
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_4 = ["onClick"];
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_5 = {
  class: "dimension"
};
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_6 = {
  class: "reportContainer"
};
const WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);
function WidgetByDimensionContainervue_type_template_id_ad0a8c4a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_WidgetLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetLoader");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.widgetsByCategory, category => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "dimensionCategory",
      key: category.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category.name) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_3, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(category.widgets, widget => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["reportDimension", {
          activeDimension: _ctx.selectedWidget.uniqueId === widget.uniqueId
        }]),
        key: widget.uniqueId,
        onClick: $event => _ctx.selectWidget(widget)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(widget.name), 1)], 10, WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_4);
    }), 128))])]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_6, [_ctx.selectedWidget.parameters ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_WidgetLoader, {
    key: 0,
    "widget-params": _ctx.selectedWidget.parameters,
    class: "dimensionReport"
  }, null, 8, ["widget-params"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), WidgetByDimensionContainervue_type_template_id_ad0a8c4a_hoisted_7]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=template&id=ad0a8c4a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=script&lang=ts
function WidgetByDimensionContainervue_type_script_lang_ts_extends() { WidgetByDimensionContainervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return WidgetByDimensionContainervue_type_script_lang_ts_extends.apply(this, arguments); }



/* harmony default export */ var WidgetByDimensionContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    widgets: Array
  },
  components: {
    WidgetLoader: WidgetLoader
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
      this.widgetsSorted.forEach(widget => {
        var _widget$subcategory;
        const category = (_widget$subcategory = widget.subcategory) === null || _widget$subcategory === void 0 ? void 0 : _widget$subcategory.name;
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
    selectWidget(widget) {
      // we copy to force rerender if selecting same widget
      this.selectedWidget = WidgetByDimensionContainervue_type_script_lang_ts_extends({}, widget);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/WidgetByDimensionContainer/WidgetByDimensionContainer.vue



WidgetByDimensionContainervue_type_script_lang_ts.render = WidgetByDimensionContainervue_type_template_id_ad0a8c4a_render

/* harmony default export */ var WidgetByDimensionContainer = (WidgetByDimensionContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=template&id=2ddfad58

const Widgetvue_type_template_id_2ddfad58_hoisted_1 = ["id"];
const Widgetvue_type_template_id_2ddfad58_hoisted_2 = {
  key: 1
};
const Widgetvue_type_template_id_2ddfad58_hoisted_3 = {
  key: 2
};
function Widgetvue_type_template_id_2ddfad58_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_WidgetLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetLoader");
  const _component_WidgetContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetContainer");
  const _component_WidgetByDimensionContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetByDimensionContainer");
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return _ctx.actualWidget && _ctx.showWidget ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["matomo-widget", {
      'isFirstWidgetInPage': _ctx.actualWidget.isFirstInPage
    }]),
    id: _ctx.actualWidget.uniqueId
  }, [!_ctx.actualWidget.isContainer && _ctx.actualWidget.parameters ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_WidgetLoader, {
    key: 0,
    "widget-params": _ctx.actualWidget.parameters,
    "widget-name": _ctx.actualWidget.name
  }, null, 8, ["widget-params", "widget-name"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualWidget.isContainer && _ctx.actualWidget.layout !== 'ByDimension' && !this.preventRecursion ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Widgetvue_type_template_id_2ddfad58_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_WidgetContainer, {
    container: _ctx.actualWidget.widgets
  }, null, 8, ["container"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.actualWidget.isContainer && _ctx.actualWidget.layout === 'ByDimension' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Widgetvue_type_template_id_2ddfad58_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_WidgetByDimensionContainer, {
    widgets: _ctx.actualWidget.widgets
  }, null, 8, ["widgets"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, Widgetvue_type_template_id_2ddfad58_hoisted_1)), [[_directive_tooltips, {
    content: _ctx.tooltipContent
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=template&id=2ddfad58

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=script&lang=ts
function Widgetvue_type_script_lang_ts_extends() { Widgetvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Widgetvue_type_script_lang_ts_extends.apply(this, arguments); }








function findContainer(widgetsByCategory, containerId) {
  let widget = undefined;
  Object.values(widgetsByCategory || {}).some(widgets => {
    widget = widgets.find(w => {
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
  data() {
    return {
      showWidget: false
    };
  },
  setup() {
    function tooltipContent() {
      const $this = window.$(this);
      if ($this.hasClass('matomo-form-field')) {
        // do not show it for form fields
        return '';
      }
      const title = window.$(this).attr('title') || '';
      return window.vueSanitize(title.replace(/\n/g, '<br />'));
    }
    return {
      tooltipContent
    };
  },
  created() {
    const {
      actualWidget
    } = this;
    if (actualWidget && actualWidget.middlewareParameters) {
      const params = actualWidget.middlewareParameters;
      AjaxHelper_AjaxHelper.fetch(params).then(response => {
        this.showWidget = !!response;
      });
    } else {
      this.showWidget = true;
    }
  },
  computed: {
    allWidgets() {
      return Widgets_store.widgets.value;
    },
    actualWidget() {
      const widget = this.widget;
      if (widget) {
        const result = Widgetvue_type_script_lang_ts_extends({}, widget);
        if (widget && widget.isReport && !widget.documentation) {
          const report = ReportMetadata_store.findReport(widget.module, widget.action);
          if (report && report.documentation) {
            result.documentation = report.documentation;
          }
        }
        return widget;
      }
      if (this.containerid) {
        const containerWidget = findContainer(this.allWidgets, this.containerid);
        if (containerWidget) {
          const result = Widgetvue_type_script_lang_ts_extends({}, containerWidget);
          if (this.widgetized) {
            result.isFirstInPage = true;
            result.parameters = Widgetvue_type_script_lang_ts_extends(Widgetvue_type_script_lang_ts_extends({}, result.parameters), {}, {
              widget: '1'
            });
            const widgets = getWidgetChildren(result);
            if (widgets) {
              result.widgets = widgets.map(w => Widgetvue_type_script_lang_ts_extends(Widgetvue_type_script_lang_ts_extends({}, w), {}, {
                parameters: Widgetvue_type_script_lang_ts_extends(Widgetvue_type_script_lang_ts_extends({}, w.parameters), {}, {
                  widget: '1',
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
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Widget/Widget.vue



Widgetvue_type_script_lang_ts.render = Widgetvue_type_template_id_2ddfad58_render

/* harmony default export */ var Widget_Widget = (Widgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=template&id=16afd136

const ReportingPagevue_type_template_id_16afd136_hoisted_1 = {
  class: "reporting-page"
};
const ReportingPagevue_type_template_id_16afd136_hoisted_2 = {
  key: 1,
  class: "col s12 l6 leftWidgetColumn"
};
const ReportingPagevue_type_template_id_16afd136_hoisted_3 = {
  key: 2,
  class: "col s12 l6 rightWidgetColumn"
};
function ReportingPagevue_type_template_id_16afd136_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Widget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Widget");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_16afd136_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading
  }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_NoSuchPage')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasNoPage]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.widgets, widget => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: widget.uniqueId
    }, [!widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
      key: 0,
      class: "col s12 fullWidgetColumn",
      widget: widget
    }, null, 8, ["widget"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_16afd136_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(widget.left, widgetInGroup => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
        widget: widgetInGroup,
        key: widgetInGroup.uniqueId
      }, null, 8, ["widget"]);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), widget.group ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportingPagevue_type_template_id_16afd136_hoisted_3, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(widget.right, widgetInGroup => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
        widget: widgetInGroup,
        key: widgetInGroup.uniqueId
      }, null, 8, ["widget"]);
    }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=template&id=16afd136

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.store.ts
function ReportingPage_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function ReportingPage_store_extends() { ReportingPage_store_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportingPage_store_extends.apply(this, arguments); }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    const newWidgets = [...widgets];
    const groupedWidgets = widgets[0];
    if (groupedWidgets.group) {
      newWidgets[0] = ReportingPage_store_extends(ReportingPage_store_extends({}, newWidgets[0]), {}, {
        left: markWidgetsInFirstRowOfPage(groupedWidgets.left || []),
        right: markWidgetsInFirstRowOfPage(groupedWidgets.right || [])
      });
    } else {
      newWidgets[0] = ReportingPage_store_extends(ReportingPage_store_extends({}, newWidgets[0]), {}, {
        isFirstInPage: true
      });
    }
    return newWidgets;
  }
  return widgets;
}
class ReportingPage_store_ReportingPageStore {
  constructor() {
    ReportingPage_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({}));
    ReportingPage_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    ReportingPage_store_defineProperty(this, "page", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.page));
    ReportingPage_store_defineProperty(this, "widgets", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const page = this.page.value;
      if (!page) {
        return [];
      }
      let widgets = [];
      const reportsToIgnore = {};
      const isIgnoredReport = widget => widget.isReport && reportsToIgnore[`${widget.module}.${widget.action}`];
      const getRelatedReports = widget => {
        if (!widget.isReport) {
          return [];
        }
        const report = ReportMetadata_store.findReport(widget.module, widget.action);
        if (!report || !report.relatedReports) {
          return [];
        }
        return report.relatedReports;
      };
      (page.widgets || []).forEach(widget => {
        if (isIgnoredReport(widget)) {
          return;
        }
        getRelatedReports(widget).forEach(report => {
          reportsToIgnore[`${report.module}.${report.action}`] = true;
        });
        widgets.push(widget);
      });
      widgets = sortOrderables(widgets);
      if (widgets.length === 1) {
        // if there is only one widget, we always display it full width
        return markWidgetsInFirstRowOfPage(widgets);
      }
      const groupedWidgets = [];
      for (let i = 0; i < widgets.length; i += 1) {
        const widget = widgets[i];
        if (shouldBeRenderedWithFullWidth(widget) || widgets[i + 1] && shouldBeRenderedWithFullWidth(widgets[i + 1])) {
          groupedWidgets.push(ReportingPage_store_extends(ReportingPage_store_extends({}, widget), {}, {
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
          groupedWidgets.push({
            group: true,
            left,
            right
          });
        }
      }
      const sortedWidgets = markWidgetsInFirstRowOfPage(groupedWidgets);
      return sortedWidgets;
    }));
  }
  fetchPage(category, subcategory) {
    this.resetPage();
    return Promise.all([ReportingPages_store.getAllPages(), ReportMetadata_store.fetchReportMetadata()]).then(() => {
      this.privateState.page = ReportingPages_store.findPage(category, subcategory);
      return this.page.value;
    });
  }
  resetPage() {
    this.privateState.page = undefined;
  }
}
/* harmony default export */ var ReportingPage_store = (new ReportingPage_store_ReportingPageStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=script&lang=ts
function ReportingPagevue_type_script_lang_ts_extends() { ReportingPagevue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportingPagevue_type_script_lang_ts_extends.apply(this, arguments); }











function showOnlyRawDataNotification() {
  const params = 'category=General_Visitors&subcategory=Live_VisitorLog';
  const url = window.broadcast.buildReportingUrl(params);
  let message = translate('CoreHome_PeriodHasOnlyRawData', `<a href="${url}">`, '</a>');
  if (!Matomo_Matomo.visitorLogEnabled) {
    message = translate('CoreHome_PeriodHasOnlyRawDataNoVisitsLog');
  }
  Notifications_store.show({
    id: 'onlyRawData',
    animate: false,
    context: 'info',
    message,
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
  data() {
    return {
      loading: false,
      hasRawData: false,
      hasNoVisits: false,
      dateLastChecked: null,
      hasNoPage: false
    };
  },
  created() {
    ReportingPage_store.resetPage();
    this.loading = true; // we only set loading on initial load
    this.renderInitialPage();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.parsed.value, (newValue, oldValue) => {
      if (newValue.category === oldValue.category && newValue.subcategory === oldValue.subcategory && newValue.period === oldValue.period && newValue.date === oldValue.date && newValue.segment === oldValue.segment && JSON.stringify(newValue.compareDates) === JSON.stringify(oldValue.compareDates) && JSON.stringify(newValue.comparePeriods) === JSON.stringify(oldValue.comparePeriods) && JSON.stringify(newValue.compareSegments) === JSON.stringify(oldValue.compareSegments) && JSON.stringify(newValue.columns || '') === JSON.stringify(oldValue.columns || '')) {
        // this page is already loaded
        return;
      }
      if (newValue.date !== oldValue.date || newValue.period !== oldValue.period) {
        hideOnlyRawDataNoticifation();
        this.dateLastChecked = null;
        this.hasRawData = false;
        this.hasNoVisits = false;
      }
      this.renderPage(newValue.category, newValue.subcategory, newValue.period, newValue.date, newValue.segment);
    });
    Matomo_Matomo.on('loadPage', (category, subcategory) => {
      const parsedUrl = src_MatomoUrl_MatomoUrl.parsed.value;
      this.renderPage(category, subcategory, parsedUrl.period, parsedUrl.date, parsedUrl.segment);
    });
  },
  computed: {
    widgets() {
      return ReportingPage_store.widgets.value;
    }
  },
  methods: {
    renderPage(category, subcategory, period, date, segment) {
      if (!category || !subcategory) {
        ReportingPage_store.resetPage();
        this.loading = false;
        return;
      }
      try {
        Periods_Periods.parse(period, date);
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
      Matomo_Matomo.postEvent('matomoPageChange', {});
      Notifications_store.clearTransientNotifications();
      if (Periods_Periods.parse(period, date).containsToday()) {
        this.showOnlyRawDataMessageIfRequired(category, subcategory, period, date, segment);
      }
      const params = {
        category,
        subcategory
      };
      Matomo_Matomo.postEvent('ReportingPage.loadPage', params);
      if (params.promise) {
        this.loading = true;
        Promise.resolve(params.promise).finally(() => {
          this.loading = false;
        });
        return;
      }
      ReportingPage_store.fetchPage(category, subcategory).then(() => {
        const hasNoPage = !ReportingPage_store.page.value;
        if (hasNoPage) {
          const page = ReportingPages_store.findPageInCategory(category);
          if (page && page.subcategory) {
            src_MatomoUrl_MatomoUrl.updateHash(ReportingPagevue_type_script_lang_ts_extends(ReportingPagevue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
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
      const parsed = src_MatomoUrl_MatomoUrl.parsed.value;
      this.renderPage(parsed.category, parsed.subcategory, parsed.period, parsed.date, parsed.segment);
    },
    showOnlyRawDataMessageIfRequired(category, subcategory, period, date, segment) {
      if (this.hasRawData && this.hasNoVisits) {
        showOnlyRawDataNotification();
      }
      if (segment) {
        hideOnlyRawDataNoticifation();
        return;
      }
      const subcategoryExceptions = ['Live_VisitorLog', 'General_RealTime', 'UserCountryMap_RealTimeMap', 'MediaAnalytics_TypeAudienceLog', 'MediaAnalytics_TypeRealTime', 'FormAnalytics_TypeRealTime', 'Goals_AddNewGoal'];
      const categoryExceptions = ['HeatmapSessionRecording_Heatmaps', 'HeatmapSessionRecording_SessionRecordings', 'Marketplace_Marketplace'];
      if (subcategoryExceptions.indexOf(subcategory) !== -1 || categoryExceptions.indexOf(category) !== -1 || subcategory.toLowerCase().indexOf('manage') !== -1) {
        hideOnlyRawDataNoticifation();
        return;
      }
      const minuteInMilliseconds = 60000;
      if (this.dateLastChecked && new Date().valueOf() - this.dateLastChecked.valueOf() < minuteInMilliseconds) {
        return;
      }
      AjaxHelper_AjaxHelper.fetch({
        method: 'VisitsSummary.getVisits',
        date,
        period,
        segment
      }).then(json => {
        this.dateLastChecked = new Date();
        if (json.value > 0) {
          this.hasNoVisits = false;
          hideOnlyRawDataNoticifation();
          return undefined;
        }
        this.hasNoVisits = true;
        if (this.hasRawData) {
          showOnlyRawDataNotification();
          return undefined;
        }
        return AjaxHelper_AjaxHelper.fetch({
          method: 'Live.getMostRecentVisitsDateTime',
          date,
          period
        }).then(lastVisits => {
          if (!lastVisits || lastVisits.value === '') {
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
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportingPage/ReportingPage.vue



ReportingPagevue_type_script_lang_ts.render = ReportingPagevue_type_template_id_16afd136_render

/* harmony default export */ var ReportingPage = (ReportingPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=template&id=85b6ba3e

const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_1 = {
  class: "report-export-popover row",
  id: "reportExport"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_2 = {
  class: "col l6"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_3 = {
  name: "format"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_4 = {
  name: "option_flat"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_5 = {
  name: "option_expanded"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_6 = {
  name: "option_format_metrics"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_7 = {
  class: "col l6"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_8 = {
  name: "filter_type"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_9 = {
  class: "filter_limit"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_10 = {
  name: "filter_limit_all"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_11 = {
  key: 0,
  name: "filter_limit"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_12 = {
  key: 1,
  name: "filter_limit"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_13 = {
  class: "col l12"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_14 = ["value"];
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_15 = ["innerHTML"];
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_16 = {
  class: "col l12"
};
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_17 = ["href", "title"];
const ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_18 = ["innerHTML"];
function ReportExportPopovervue_type_template_id_85b6ba3e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'format',
    title: _ctx.translate('CoreHome_ExportFormat'),
    modelValue: _ctx.reportFormat,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.reportFormat = $event),
    "full-width": true,
    options: _ctx.availableReportFormats[_ctx.reportType]
  }, null, 8, ["title", "modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_flat',
    title: _ctx.translate('CoreHome_FlattenReport'),
    modelValue: _ctx.optionFlat,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.optionFlat = $event)
  }, null, 8, ["title", "modelValue"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSubtables]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_expanded',
    title: _ctx.translate('CoreHome_ExpandSubtables'),
    modelValue: _ctx.optionExpanded,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.optionExpanded = $event)
  }, null, 8, ["title", "modelValue"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSubtables && !_ctx.optionFlat]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'checkbox',
    name: 'option_format_metrics',
    title: _ctx.translate('CoreHome_FormatMetrics'),
    modelValue: _ctx.optionFormatMetrics,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.optionFormatMetrics = $event)
  }, null, 8, ["title", "modelValue"])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'filter_type',
    title: _ctx.translate('CoreHome_ReportType'),
    modelValue: _ctx.reportType,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.reportType = $event),
    "full-width": true,
    options: _ctx.availableReportTypes
  }, null, 8, ["title", "modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'radio',
    name: 'filter_limit_all',
    title: _ctx.translate('CoreHome_RowLimit'),
    modelValue: _ctx.reportLimitAll,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.reportLimitAll = $event),
    "full-width": true,
    options: _ctx.limitAllOptions
  }, null, 8, ["title", "modelValue", "options"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.maxFilterLimit || _ctx.maxFilterLimit <= 0]]), _ctx.reportLimitAll === 'no' && _ctx.maxFilterLimit <= 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'number',
    name: "filter_limit",
    min: 1,
    modelValue: _ctx.reportLimit,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.reportLimit = $event),
    "full-width": true
  }, null, 8, ["modelValue"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.reportLimitAll === 'no' && _ctx.maxFilterLimit > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: 'number',
    name: 'filter_limit',
    min: 1,
    max: _ctx.maxFilterLimit,
    modelValue: _ctx.reportLimit,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.reportLimit = $event),
    value: _ctx.reportLimit,
    "full-width": true,
    title: _ctx.filterLimitTooltip
  }, null, 8, ["max", "modelValue", "value", "title"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("textarea", {
    readonly: "",
    class: "exportFullUrl",
    value: _ctx.exportLinkWithoutToken
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("\n      ")], 8, ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_14)), [[_directive_select_on_focus, {}]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "tooltip",
    innerHTML: _ctx.$sanitize(_ctx.translate('CoreHome_ExportTooltipWithLink', '<a target=_blank href=\'?module=UsersManager&action=userSecurity\'>', '</a>', 'ENTER_YOUR_TOKEN_AUTH_HERE'))
  }, null, 8, ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_15)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn",
    href: _ctx.exportLink,
    target: "_new",
    title: _ctx.translate('CoreHome_ExportTooltip')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Export')), 9, ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_17), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "javascript:",
    onClick: _cache[8] || (_cache[8] = $event => _ctx.showUrl = !_ctx.showUrl),
    class: "toggle-export-url"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ShowExportUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.showUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_HideExportUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showUrl]])])]), _ctx.additionalContent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "col l12 report-export-popover-footer",
    innerHTML: _ctx.$sanitize(_ctx.additionalContent)
  }, null, 8, ReportExportPopovervue_type_template_id_85b6ba3e_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=template&id=85b6ba3e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=script&lang=ts






const ReportExportPopovervue_type_script_lang_ts_Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');
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
  mounted() {
    // pass data as object, so it can be manipulated by subscribers
    const parameters = {
      content: this.additionalContent,
      dataTable: this.dataTable
    };
    Matomo_Matomo.postEvent('ReportExportPopover.additionalContent', parameters);
    this.additionalContent = parameters.content;
  },
  data() {
    return {
      showUrl: false,
      reportFormat: this.initialReportFormat,
      optionFlat: this.initialOptionFlat,
      optionExpanded: this.initialOptionExpanded,
      optionFormatMetrics: this.initialOptionFormatMetrics,
      reportType: this.initialReportType,
      reportLimitAll: this.initialReportLimitAll,
      reportLimit: typeof this.initialReportLimit === 'string' ? parseInt(this.initialReportLimit, 10) : this.initialReportLimit,
      additionalContent: ''
    };
  },
  watch: {
    reportType(newVal) {
      if (!this.availableReportFormats[newVal][this.reportFormat]) {
        this.reportFormat = 'XML';
      }
    },
    reportLimit(newVal, oldVal) {
      if (this.maxFilterLimit && this.maxFilterLimit > 0 && newVal > this.maxFilterLimit) {
        this.reportLimit = oldVal;
      }
    }
  },
  computed: {
    filterLimitTooltip() {
      const rowLimit = translate('CoreHome_RowLimit');
      const computedMetricMax = this.maxFilterLimit ? translate('General_ComputedMetricMax', this.maxFilterLimit.toString()) : '';
      return `${rowLimit} (${computedMetricMax})`;
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
        return undefined;
      }
      let requestParams = {};
      const limit = this.reportLimitAll === 'yes' ? -1 : this.reportLimit;
      if (this.requestParams && typeof this.requestParams === 'string') {
        requestParams = JSON.parse(this.requestParams);
      } else if (this.requestParams && typeof this.requestParams === 'object') {
        requestParams = this.requestParams;
      }
      const {
        segment,
        label,
        idGoal,
        idDimension,
        idSite
      } = dataTable.param;
      let {
        date,
        period
      } = dataTable.param;
      if (reportFormat === 'RSS') {
        date = 'last10';
      }
      if (typeof dataTable.param.dateUsedInGraph !== 'undefined') {
        date = dataTable.param.dateUsedInGraph;
      }
      const formatsUseDayNotRange = Matomo_Matomo.config.datatable_export_range_as_day.toLowerCase();
      if (formatsUseDayNotRange.indexOf(reportFormat.toLowerCase()) !== -1 && dataTable.param.period === 'range') {
        period = 'day';
      }
      // Below evolution graph, show daily exports
      if (dataTable.param.period === 'range' && dataTable.param.viewDataTable === 'graphEvolution') {
        period = 'day';
      }
      const exportUrlParams = {
        module: 'API',
        format: reportFormat,
        idSite,
        period,
        date
      };
      if (reportType === 'processed') {
        exportUrlParams.method = 'API.getProcessedReport';
        [exportUrlParams.apiModule, exportUrlParams.apiAction] = apiMethod.split('.');
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
      }
      // Export Goals specific reports
      if (typeof idGoal !== 'undefined' && idGoal !== '-1') {
        exportUrlParams.idGoal = idGoal;
      }
      // Export Dimension specific reports
      if (typeof idDimension !== 'undefined' && idDimension !== '-1') {
        exportUrlParams.idDimension = idDimension;
      }
      if (label) {
        const labelParts = label.split(',');
        if (labelParts.length > 1) {
          exportUrlParams.label = labelParts;
        } else {
          [exportUrlParams.label] = labelParts;
        }
      }
      exportUrlParams.token_auth = 'ENTER_YOUR_TOKEN_AUTH_HERE';
      if (withToken === true) {
        exportUrlParams.token_auth = Matomo_Matomo.token_auth;
        exportUrlParams.force_api_session = 1;
      }
      exportUrlParams.filter_limit = limit;
      const prefix = window.location.href.split('?')[0];
      return `${prefix}?${src_MatomoUrl_MatomoUrl.stringify(exportUrlParams)}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExportPopover.vue



ReportExportPopovervue_type_script_lang_ts.render = ReportExportPopovervue_type_template_id_85b6ba3e_render

/* harmony default export */ var ReportExportPopover = (ReportExportPopovervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ReportExport/ReportExport.ts
function ReportExport_extends() { ReportExport_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportExport_extends.apply(this, arguments); }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





const {
  $: ReportExport_$
} = window;
/* harmony default export */ var ReportExport = ({
  mounted(el, binding) {
    el.addEventListener('click', () => {
      const popoverParamBackup = src_MatomoUrl_MatomoUrl.hashParsed.value.popover;
      const dataTable = ReportExport_$(el).closest('[data-report]').data('uiControlObject');
      const popover = window.Piwik_Popover.showLoading('Export');
      const formats = binding.value.reportFormats;
      let reportLimit = dataTable.param.filter_limit;
      if (binding.value.maxFilterLimit > 0) {
        reportLimit = Math.min(reportLimit, binding.value.maxFilterLimit);
      }
      const optionFlat = dataTable.param.flat === true || dataTable.param.flat === 1 || dataTable.param.flat === '1';
      const props = {
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
      app.component('popover', ReportExportPopover);
      const mountPoint = document.createElement('div');
      app.mount(mountPoint);
      const {
        reportTitle
      } = binding.value;
      window.Piwik_Popover.setTitle(`${translate('General_Export')} ${Matomo_Matomo.helper.htmlEntities(reportTitle)}`);
      window.Piwik_Popover.setContent(mountPoint);
      window.Piwik_Popover.onClose(() => {
        app.unmount();
        if (popoverParamBackup !== '') {
          setTimeout(() => {
            src_MatomoUrl_MatomoUrl.updateHash(ReportExport_extends(ReportExport_extends({}, src_MatomoUrl_MatomoUrl.hashParsed.value), {}, {
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
        ReportExport_$('.exportFullUrl, .btn', popover).tooltip({
          track: true,
          show: false,
          hide: false
        });
      }, 100);
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=template&id=7dbf2b09

const Sparklinevue_type_template_id_7dbf2b09_hoisted_1 = ["src", "width", "height"];
function Sparklinevue_type_template_id_7dbf2b09_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    class: "sparklineImg",
    loading: "lazy",
    alt: "",
    src: _ctx.sparklineUrl,
    width: _ctx.width,
    height: _ctx.height
  }, null, 8, Sparklinevue_type_template_id_7dbf2b09_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=template&id=7dbf2b09

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=script&lang=ts
function Sparklinevue_type_script_lang_ts_extends() { Sparklinevue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Sparklinevue_type_script_lang_ts_extends.apply(this, arguments); }






/* harmony default export */ var Sparklinevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    seriesIndices: Array,
    params: [Object, String],
    width: Number,
    height: Number
  },
  data() {
    return {
      isWidget: false
    };
  },
  mounted() {
    this.isWidget = !!this.$el.closest('[widgetId]');
  },
  computed: {
    sparklineUrl() {
      const {
        seriesIndices,
        params
      } = this;
      const sparklineColors = Matomo_Matomo.getSparklineColors();
      if (seriesIndices) {
        sparklineColors.lineColor = sparklineColors.lineColor.filter((c, index) => seriesIndices.indexOf(index) !== -1);
      }
      const colors = JSON.stringify(sparklineColors);
      const defaultParams = {
        forceView: '1',
        viewDataTable: 'sparkline',
        widget: this.isWidget ? '1' : '0',
        showtitle: '1',
        colors,
        random: Date.now(),
        date: this.defaultDate,
        // mixinDefaultGetParams() will use the raw, encoded value from the URL (legacy behavior),
        // which means MatomoUrl.stringify() will end up double encoding it if we don't set it
        // ourselves here.
        segment: src_MatomoUrl_MatomoUrl.parsed.value.segment
      };
      const givenParams = typeof params === 'object' ? params : src_MatomoUrl_MatomoUrl.parse(params.substring(params.indexOf('?') + 1));
      const helper = new AjaxHelper_AjaxHelper();
      const urlParams = helper.mixinDefaultGetParams(Sparklinevue_type_script_lang_ts_extends(Sparklinevue_type_script_lang_ts_extends({}, defaultParams), givenParams));
      // Append the token_auth to the URL if it was set (eg. embed dashboard)
      const token_auth = src_MatomoUrl_MatomoUrl.parsed.value.token_auth;
      if (token_auth && token_auth.length && Matomo_Matomo.shouldPropagateTokenAuth) {
        urlParams.token_auth = token_auth;
      }
      return `?${src_MatomoUrl_MatomoUrl.stringify(urlParams)}`;
    },
    defaultDate() {
      if (Matomo_Matomo.period === 'range') {
        return `${Matomo_Matomo.startDateString},${Matomo_Matomo.endDateString}`;
      }
      const dateRange = Range_RangePeriod.getLastNRange(Matomo_Matomo.period, 30, Matomo_Matomo.currentDateString).getDateRange();
      const piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
      if (dateRange[0] < piwikMinDate) {
        dateRange[0] = piwikMinDate;
      }
      const startDateStr = format(dateRange[0]);
      const endDateStr = format(dateRange[1]);
      return `${startDateStr},${endDateStr}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Sparkline/Sparkline.vue



Sparklinevue_type_script_lang_ts.render = Sparklinevue_type_template_id_7dbf2b09_render

/* harmony default export */ var Sparkline = (Sparklinevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=template&id=08f50ba6

const Progressbarvue_type_template_id_08f50ba6_hoisted_1 = {
  class: "progressbar"
};
const Progressbarvue_type_template_id_08f50ba6_hoisted_2 = {
  class: "progress"
};
const Progressbarvue_type_template_id_08f50ba6_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);
const Progressbarvue_type_template_id_08f50ba6_hoisted_4 = ["innerHTML"];
function Progressbarvue_type_template_id_08f50ba6_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Progressbarvue_type_template_id_08f50ba6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Progressbarvue_type_template_id_08f50ba6_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "determinate",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])([{
      "width": "0"
    }, {
      width: `${_ctx.actualProgress}%`
    }])
  }, null, 4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Progressbarvue_type_template_id_08f50ba6_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "label",
    innerHTML: _ctx.$sanitize(_ctx.label)
  }, null, 8, Progressbarvue_type_template_id_08f50ba6_hoisted_4)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !!_ctx.label]])]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=template&id=08f50ba6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=script&lang=ts

/* harmony default export */ var Progressbarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Progressbar/Progressbar.vue



Progressbarvue_type_script_lang_ts.render = Progressbarvue_type_template_id_08f50ba6_render

/* harmony default export */ var Progressbar = (Progressbarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentIntro/ContentIntro.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* harmony default export */ var ContentIntro = ({
  mounted(el) {
    el.classList.add('piwik-content-intro');
  },
  updated(el) {
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
      el.classList.add('piwik-content-intro');
    });
  }
});
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentTable/ContentTable.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* harmony default export */ var ContentTable = ({
  mounted(el, binding) {
    var _binding$value;
    if (binding !== null && binding !== void 0 && (_binding$value = binding.value) !== null && _binding$value !== void 0 && _binding$value.off) {
      return;
    }
    el.classList.add('card', 'card-table', 'entityTable');
  },
  updated(el, binding) {
    var _binding$value2;
    if (binding !== null && binding !== void 0 && (_binding$value2 = binding.value) !== null && _binding$value2 !== void 0 && _binding$value2.off) {
      return;
    }
    // classes can be overwritten when elements bind to :class, nextTick + using
    // updated avoids this problem (and doing in both mounted and updated avoids a temporary
    // state where the classes aren't added)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
      el.classList.add('card', 'card-table', 'entityTable');
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=template&id=04849007

const AjaxFormvue_type_template_id_04849007_hoisted_1 = {
  ref: "root"
};
function AjaxFormvue_type_template_id_04849007_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AjaxFormvue_type_template_id_04849007_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default", {
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=template&id=04849007

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=script&lang=ts




const {
  $: AjaxFormvue_type_script_lang_ts_$
} = window;
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
  data() {
    return {
      isSubmitting: false,
      successfulPostResponse: null,
      errorPostResponse: null
    };
  },
  emits: ['update:modelValue'],
  mounted() {
    // on submit call controller submit method
    AjaxFormvue_type_script_lang_ts_$(this.$refs.root).on('click', 'input[type=submit]', () => {
      this.submitForm();
    });
  },
  methods: {
    submitForm() {
      this.successfulPostResponse = null;
      this.errorPostResponse = null;
      let postParams = this.formData;
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
      }).then(response => {
        this.successfulPostResponse = response;
        if (!this.noSuccessNotification) {
          const notificationInstanceId = Notifications_store.show({
            message: translate('General_YourChangesHaveBeenSaved'),
            context: 'success',
            type: 'toast',
            id: 'ajaxHelper'
          });
          Notifications_store.scrollToNotification(notificationInstanceId);
        }
      }).catch(error => {
        this.errorPostResponse = error.message;
      }).finally(() => {
        this.isSubmitting = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxForm/AjaxForm.vue



AjaxFormvue_type_script_lang_ts.render = AjaxFormvue_type_template_id_04849007_render

/* harmony default export */ var AjaxForm = (AjaxFormvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Passthrough/Passthrough.vue?vue&type=template&id=31c1d52c

function Passthroughvue_type_template_id_31c1d52c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default");
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Passthrough/Passthrough.vue?vue&type=template&id=31c1d52c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/Passthrough/Passthrough.vue?vue&type=script&lang=ts

/* harmony default export */ var Passthroughvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Passthrough/Passthrough.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Passthrough/Passthrough.vue



Passthroughvue_type_script_lang_ts.render = Passthroughvue_type_template_id_31c1d52c_render

/* harmony default export */ var Passthrough = (Passthroughvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DataTable/DataTableActions.vue?vue&type=template&id=72664d4c

const DataTableActionsvue_type_template_id_72664d4c_hoisted_1 = {
  key: 0
};
const DataTableActionsvue_type_template_id_72664d4c_hoisted_2 = ["data-target"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-configure"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_4 = [DataTableActionsvue_type_template_id_72664d4c_hoisted_3];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_5 = ["data-target"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_6 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_7 = ["title", "src"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_8 = ["id"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_9 = ["data-footer-icon-id"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_10 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_11 = ["title", "src"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_12 = {
  key: 2
};
const DataTableActionsvue_type_template_id_72664d4c_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
  class: "divider"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
  class: "divider"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_15 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_17 = [DataTableActionsvue_type_template_id_72664d4c_hoisted_16];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_18 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-image"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_20 = [DataTableActionsvue_type_template_id_72664d4c_hoisted_19];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_21 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-annotation"
}, null, -1);
const DataTableActionsvue_type_template_id_72664d4c_hoisted_23 = [DataTableActionsvue_type_template_id_72664d4c_hoisted_22];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_24 = ["title"];
const DataTableActionsvue_type_template_id_72664d4c_hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search",
  draggable: "false"
}, null, -1);
const _hoisted_26 = ["title"];
const _hoisted_27 = ["id", "title"];
const _hoisted_28 = ["title"];
const _hoisted_29 = ["title", "src"];
const _hoisted_30 = ["id"];
const _hoisted_31 = {
  key: 0
};
const _hoisted_32 = ["innerHTML"];
const _hoisted_33 = {
  key: 1
};
const _hoisted_34 = ["innerHTML"];
const _hoisted_35 = {
  key: 2
};
const _hoisted_36 = ["innerHTML"];
const _hoisted_37 = {
  key: 3
};
const _hoisted_38 = ["innerHTML"];
const _hoisted_39 = {
  key: 4
};
const _hoisted_40 = ["innerHTML"];
const _hoisted_41 = {
  key: 5
};
const _hoisted_42 = ["innerHTML"];
const _hoisted_43 = ["title", "data-target"];
const _hoisted_44 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-calendar"
}, null, -1);
const _hoisted_45 = {
  class: "periodName"
};
const _hoisted_46 = ["id"];
const _hoisted_47 = ["data-period"];
function DataTableActionsvue_type_template_id_72664d4c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Passthrough = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Passthrough");
  const _directive_dropdown_button = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("dropdown-button");
  const _directive_report_export = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("report-export");
  return _ctx.showFooter && _ctx.showFooterIcons ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DataTableActionsvue_type_template_id_72664d4c_hoisted_1, [_ctx.hasConfigItems && (_ctx.isAnyConfigureIconHighlighted || _ctx.isTableView) ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dropdown-button dropdownConfigureIcon dataTableAction", {
      highlighted: _ctx.isAnyConfigureIconHighlighted
    }]),
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"])),
    "data-target": `dropdownConfigure${_ctx.randomIdForDropdown}`,
    style: {
      "margin-right": "3.5px"
    }
  }, DataTableActionsvue_type_template_id_72664d4c_hoisted_4, 10, DataTableActionsvue_type_template_id_72664d4c_hoisted_2)), [[_directive_dropdown_button]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.hasFooterIconsToShow ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "dropdown-button dataTableAction activateVisualizationSelection",
    href: "",
    "data-target": `dropdownVisualizations${_ctx.randomIdForDropdown}`,
    style: {
      "margin-right": "3.5px"
    },
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"]))
  }, [/^icon-/.test(_ctx.activeFooterIcon || '') ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    title: _ctx.translate('CoreHome_ChangeVisualization'),
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.activeFooterIcon)
  }, null, 10, DataTableActionsvue_type_template_id_72664d4c_hoisted_6)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    key: 1,
    title: _ctx.translate('CoreHome_ChangeVisualization'),
    width: "16",
    height: "16",
    src: _ctx.activeFooterIcon
  }, null, 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_7))], 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_5)), [[_directive_dropdown_button]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showFooterIcons ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
    key: 2,
    id: `dropdownVisualizations${_ctx.randomIdForDropdown}`,
    class: "dropdown-content dataTableFooterIcons"
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.footerIcons, (footerIconGroup, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Passthrough, {
      key: index
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(footerIconGroup.buttons.filter(i => !!i.icon), footerIcon => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
          key: footerIcon.id
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`${footerIconGroup.class} tableIcon
              ${_ctx.activeFooterIconIds.indexOf(footerIcon.id) !== -1 ? 'activeIcon' : ''}`),
          "data-footer-icon-id": footerIcon.id
        }, [/^icon-/.test(footerIcon.icon || '') ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 0,
          title: footerIcon.title,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(footerIcon.icon),
          style: {
            "margin-right": "5.5px"
          }
        }, null, 10, DataTableActionsvue_type_template_id_72664d4c_hoisted_10)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
          key: 1,
          width: "16",
          height: "16",
          title: footerIcon.title,
          src: footerIcon.icon,
          style: {
            "margin-right": "5.5px"
          }
        }, null, 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_11)), footerIcon.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", DataTableActionsvue_type_template_id_72664d4c_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(footerIcon.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, DataTableActionsvue_type_template_id_72664d4c_hoisted_9)]);
      }), 128)), DataTableActionsvue_type_template_id_72664d4c_hoisted_13]),
      _: 2
    }, 1024);
  }), 128)), DataTableActionsvue_type_template_id_72664d4c_hoisted_14], 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showExport ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 3,
    class: "dataTableAction activateExportSelection",
    title: _ctx.translate('General_ExportThisReport'),
    href: "",
    style: {
      "margin-right": "3.5px"
    },
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"]))
  }, DataTableActionsvue_type_template_id_72664d4c_hoisted_17, 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_15)), [[_directive_report_export, {
    reportTitle: _ctx.reportTitle,
    requestParams: _ctx.requestParams,
    apiMethod: _ctx.apiMethodToRequestDataTable,
    reportFormats: _ctx.reportFormats,
    maxFilterLimit: _ctx.maxFilterLimit
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showExportAsImageIcon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 4,
    class: "dataTableAction tableIcon",
    href: "",
    id: "dataTableFooterExportAsImageIcon",
    onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showExportImage($event), ["prevent"])),
    title: _ctx.translate('General_ExportAsImage'),
    style: {
      "margin-right": "3.5px"
    }
  }, DataTableActionsvue_type_template_id_72664d4c_hoisted_20, 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showAnnotations ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 5,
    class: "dataTableAction annotationView",
    href: "",
    title: _ctx.translate('Annotations_Annotations'),
    onClick: _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, DataTableActionsvue_type_template_id_72664d4c_hoisted_23, 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_21)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showSearch ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 6,
    class: "dropdown-button dataTableAction searchAction",
    href: "",
    title: _ctx.translate('General_Search'),
    style: {
      "margin-right": "3.5px"
    },
    draggable: "false",
    onClick: _cache[5] || (_cache[5] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"]))
  }, [DataTableActionsvue_type_template_id_72664d4c_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-close",
    draggable: "false",
    title: _ctx.translate('CoreHome_CloseSearch')
  }, null, 8, _hoisted_26), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: `widgetSearch_${_ctx.reportId}`,
    title: _ctx.translate('CoreHome_DataTableHowToSearch'),
    type: "text",
    class: "dataTableSearchInput"
  }, null, 8, _hoisted_27)], 8, DataTableActionsvue_type_template_id_72664d4c_hoisted_24)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dataTableActions, action => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: action.id,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`dataTableAction ${action.id}`),
      href: "",
      onClick: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"])),
      title: action.title,
      style: {
        "margin-right": "3.5px"
      }
    }, [/^icon-/.test(action.icon || '') ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(action.icon)
    }, null, 2)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
      key: 1,
      width: "16",
      height: "16",
      title: action.title,
      src: action.icon
    }, null, 8, _hoisted_29))], 10, _hoisted_28);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
    id: `dropdownConfigure${_ctx.randomIdForDropdown}`,
    class: "dropdown-content tableConfiguration"
  }, [_ctx.showFlattenTable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTableFlatten",
    innerHTML: _ctx.$sanitize(_ctx.flattenItemText)
  }, null, 8, _hoisted_32)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showDimensionsConfigItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTableShowDimensions",
    innerHTML: _ctx.$sanitize(_ctx.showDimensionsText)
  }, null, 8, _hoisted_34)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showFlatConfigItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTableIncludeAggregateRows",
    innerHTML: _ctx.$sanitize(_ctx.includeAggregateRowsText)
  }, null, 8, _hoisted_36)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showTotalsConfigItem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTableShowTotalsRow",
    innerHTML: _ctx.$sanitize(_ctx.keepTotalsRowText)
  }, null, 8, _hoisted_38)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showExcludeLowPopulation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTableExcludeLowPopulation",
    innerHTML: _ctx.$sanitize(_ctx.excludeLowPopText)
  }, null, 8, _hoisted_40)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showPivotBySubtable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "configItem dataTablePivotBySubtable",
    innerHTML: _ctx.$sanitize(_ctx.pivotByText)
  }, null, 8, _hoisted_42)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, _hoisted_30), _ctx.showPeriods ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 7,
    class: "dropdown-button dataTableAction activatePeriodsSelection",
    href: "",
    onClick: _cache[7] || (_cache[7] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent"])),
    title: _ctx.translate('CoreHome_ChangePeriod'),
    "data-target": `dropdownPeriods${_ctx.randomIdForDropdown}`
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_44, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_45, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translations[_ctx.clientSideParameters.period] || _ctx.clientSideParameters.period), 1)])], 8, _hoisted_43)), [[_directive_dropdown_button]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showPeriods ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
    key: 8,
    id: `dropdownPeriods${_ctx.randomIdForDropdown}`,
    class: "dropdown-content dataTablePeriods"
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectablePeriods, selectablePeriod => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: selectablePeriod
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      "data-period": selectablePeriod,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`tableIcon ${_ctx.clientSideParameters.period === selectablePeriod ? 'activeIcon' : ''}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translations[selectablePeriod] || selectablePeriod), 1)], 10, _hoisted_47)]);
  }), 128))], 8, _hoisted_46)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DataTable/DataTableActions.vue?vue&type=template&id=72664d4c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/DataTable/DataTableActions.vue?vue&type=script&lang=ts





const {
  $: DataTableActionsvue_type_script_lang_ts_$
} = window;
function getSingleStateIconText(text, addDefault, replacement) {
  if (/(%(.\$)?s+)/g.test(translate(text))) {
    const values = ['<br /><span class="action">'];
    if (replacement) {
      values.push(replacement);
    }
    let result = translate(text, ...values);
    if (addDefault) {
      result += ` (${translate('CoreHome_Default')})`;
    }
    result += '</span>';
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
function isBooleanLikeSet(value) {
  return !!value && value !== '0';
}
/* harmony default export */ var DataTableActionsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    showPeriods: Boolean,
    showFooter: Boolean,
    showFooterIcons: Boolean,
    showSearch: Boolean,
    showFlattenTable: Boolean,
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
    pivotDimensionName: String
  },
  components: {
    Passthrough: Passthrough
  },
  directives: {
    DropdownButton: DropdownButton,
    ReportExport: ReportExport
  },
  methods: {
    showExportImage(event) {
      DataTableActionsvue_type_script_lang_ts_$(event.target).closest('.dataTable').find('div.jqplot-target').trigger('piwikExportAsImage');
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
      if (params.abandonedCarts === 0 || params.abandonedCarts === '0') {
        result.push('ecommerceOrder');
      } else if (params.abandonedCarts === 1 || params.abandonedCarts === '1') {
        result.push('ecommerceAbandonedCart');
      }
      return result.map(id => this.allFooterIcons.find(button => button.id === id)).filter(icon => !!icon);
    },
    activeFooterIcon() {
      var _this$activeFooterIco;
      return (_this$activeFooterIco = this.activeFooterIcons[0]) === null || _this$activeFooterIco === void 0 ? void 0 : _this$activeFooterIco.icon;
    },
    activeFooterIconIds() {
      return this.activeFooterIcons.map(icon => icon.id);
    },
    numIcons() {
      return this.allFooterIcons.length;
    },
    hasFooterIconsToShow() {
      return !!this.activeFooterIcons.length && this.numIcons > 1;
    },
    reportFormats() {
      const formats = {
        CSV: 'CSV',
        TSV: 'TSV (Excel)',
        XML: 'XML',
        JSON: 'Json',
        HTML: 'HTML'
      };
      formats.RSS = 'RSS';
      return formats;
    },
    showDimensionsConfigItem() {
      return this.showFlattenTable && `${this.clientSideParameters.flat}` === '1' && this.hasMultipleDimensions;
    },
    showFlatConfigItem() {
      return this.showFlattenTable && `${this.clientSideParameters.flat}` === '1';
    },
    showTotalsConfigItem() {
      return !this.isDataTableEmpty && this.showTotalsRow;
    },
    hasConfigItems() {
      return this.showFlattenTable || this.showDimensionsConfigItem || this.showFlatConfigItem || this.showTotalsConfigItem || this.showExcludeLowPopulation || this.showPivotBySubtable;
    },
    flattenItemText() {
      const params = this.clientSideParameters;
      return getToggledIconText(isBooleanLikeSet(params.flat), 'CoreHome_UnFlattenDataTable', 'CoreHome_FlattenDataTable');
    },
    keepTotalsRowText() {
      const params = this.clientSideParameters;
      return getToggledIconText(isBooleanLikeSet(params.keep_totals_row), 'CoreHome_RemoveTotalsRowDataTable', 'CoreHome_AddTotalsRowDataTable');
    },
    includeAggregateRowsText() {
      const params = this.clientSideParameters;
      return getToggledIconText(isBooleanLikeSet(params.include_aggregate_rows), 'CoreHome_DataTableExcludeAggregateRows', 'CoreHome_DataTableIncludeAggregateRows');
    },
    showDimensionsText() {
      const params = this.clientSideParameters;
      return getToggledIconText(isBooleanLikeSet(params.show_dimensions), 'CoreHome_DataTableCombineDimensions', 'CoreHome_DataTableShowDimensions');
    },
    pivotByText() {
      const params = this.clientSideParameters;
      if (isBooleanLikeSet(params.pivotBy)) {
        return getSingleStateIconText('CoreHome_UndoPivotBySubtable', true);
      }
      return getSingleStateIconText('CoreHome_PivotBySubtable', false, this.pivotDimensionName);
    },
    excludeLowPopText() {
      const params = this.clientSideParameters;
      return getToggledIconText(isBooleanLikeSet(params.enable_filter_excludelowpop), 'CoreHome_IncludeRowsWithLowPopulation', 'CoreHome_ExcludeRowsWithLowPopulation');
    },
    isAnyConfigureIconHighlighted() {
      const params = this.clientSideParameters;
      return isBooleanLikeSet(params.flat) || isBooleanLikeSet(params.keep_totals_row) || isBooleanLikeSet(params.include_aggregate_rows) || isBooleanLikeSet(params.show_dimensions) || isBooleanLikeSet(params.pivotBy) || isBooleanLikeSet(params.enable_filter_excludelowpop);
    },
    isTableView() {
      return this.viewDataTable === 'table' || this.viewDataTable === 'tableAllColumns' || this.viewDataTable === 'tableGoals';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DataTable/DataTableActions.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DataTable/DataTableActions.vue



DataTableActionsvue_type_script_lang_ts.render = DataTableActionsvue_type_template_id_72664d4c_render

/* harmony default export */ var DataTableActions = (DataTableActionsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/VersionInfoHeaderMessage/VersionInfoHeaderMessage.vue?vue&type=template&id=9eb6779c

const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_1 = {
  key: 0,
  class: "title",
  style: {
    "cursor": "pointer"
  },
  ref: "expander"
};
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_3 = {
  key: 1,
  class: "title",
  href: "?module=CoreUpdater&action=newVersionAvailable",
  style: {
    "cursor": "pointer"
  },
  ref: "expander"
};
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_5 = ["innerHTML"];
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_6 = ["href"];
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_7 = {
  id: "updateCheckLinkContainer"
};
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_8 = {
  class: "dropdown positionInViewport"
};
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_9 = ["innerHTML"];
const VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_10 = ["innerHTML"];
function VersionInfoHeaderMessagevue_type_template_id_9eb6779c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Passthrough = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Passthrough");
  const _directive_expand_on_hover = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-hover");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    id: "header_message",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["piwikSelector", {
      header_info: !_ctx.latestVersionAvailable || _ctx.lastUpdateCheckFailed,
      update_available: _ctx.latestVersionAvailable
    }])
  }, [_ctx.latestVersionAvailable && !_ctx.isPiwikDemo ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Passthrough, {
    key: 0
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NewUpdatePiwikX', _ctx.latestVersionAvailable)) + " ", 1), VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_2], 512)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NewUpdatePiwikX', _ctx.latestVersionAvailable)) + " ", 1), VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_4], 512))]),
    _: 1
  })) : _ctx.isSuperUser && (_ctx.isAdminArea || _ctx.lastUpdateCheckFailed) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Passthrough, {
    key: 1
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.isInternetEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "title",
      innerHTML: _ctx.$sanitize(_ctx.updateCheck)
    }, null, 8, VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_5)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      class: "title",
      href: _ctx.externalRawLink('https://matomo.org/changelog/'),
      target: "_blank",
      rel: "noreferrer noopener"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_SeeAvailableVersions')), 1)], 8, VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_6))]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_8, [_ctx.latestVersionAvailable && _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.updateNowText)
  }, null, 8, VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_9)) : _ctx.latestVersionAvailable && !_ctx.isPiwikDemo && _ctx.hasSomeViewAccess && !_ctx.isAnonymous ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.updateAvailableText)
  }, null, 8, VersionInfoHeaderMessagevue_type_template_id_9eb6779c_hoisted_10)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_YouAreCurrentlyUsing', _ctx.piwikVersion)), 1)])], 2)), [[_directive_expand_on_hover, {
    expander: 'expander'
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VersionInfoHeaderMessage/VersionInfoHeaderMessage.vue?vue&type=template&id=9eb6779c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/VersionInfoHeaderMessage/VersionInfoHeaderMessage.vue?vue&type=script&lang=ts





/* harmony default export */ var VersionInfoHeaderMessagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isMultiServerEnvironment: Boolean,
    lastUpdateCheckFailed: Boolean,
    latestVersionAvailable: String,
    isPiwikDemo: Boolean,
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
    Passthrough: Passthrough
  },
  directives: {
    ExpandOnHover: ExpandOnHover
  },
  computed: {
    updateNowText() {
      let text = '';
      if (this.isMultiServerEnvironment) {
        const link = externalRawLink(`https://builds.matomo.org/matomo-${this.latestVersionAvailable}.zip`);
        text = translate('CoreHome_OneClickUpdateNotPossibleAsMultiServerEnvironment', `<a rel="noreferrer noopener" href="${link}">builds.matomo.org</a>`);
      } else {
        text = translate('General_PiwikXIsAvailablePleaseUpdateNow', this.latestVersionAvailable || '', '<br /><a href="index.php?module=CoreUpdater&amp;action=newVersionAvailable">', '</a>', externalLink('https://matomo.org/changelog/'), '</a>');
      }
      return `${text}<br/>`;
    },
    updateAvailableText() {
      const updateSubject = translate('General_NewUpdatePiwikX', this.latestVersionAvailable || '');
      /* eslint-disable prefer-template */
      const matomoLink = externalLink('https://matomo.org/') + 'Matomo</a>';
      const changelogLinkStart = externalLink('https://matomo.org/changelog/');
      const text = translate('General_PiwikXIsAvailablePleaseNotifyPiwikAdmin', `${matomoLink} ${changelogLinkStart}${this.latestVersionAvailable}</a>`, `<a href="mailto:${this.contactEmail}?subject=${encodeURIComponent(updateSubject)}">`, '</a>');
      return `${text}<br />`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VersionInfoHeaderMessage/VersionInfoHeaderMessage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/VersionInfoHeaderMessage/VersionInfoHeaderMessage.vue



VersionInfoHeaderMessagevue_type_script_lang_ts.render = VersionInfoHeaderMessagevue_type_template_id_9eb6779c_render

/* harmony default export */ var VersionInfoHeaderMessage = (VersionInfoHeaderMessagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MobileLeftMenu/MobileLeftMenu.vue?vue&type=template&id=49f29e13

const MobileLeftMenuvue_type_template_id_49f29e13_hoisted_1 = {
  id: "mobile-left-menu",
  class: "sidenav hide-on-large-only"
};
const MobileLeftMenuvue_type_template_id_49f29e13_hoisted_2 = {
  class: "collapsible collapsible-accordion"
};
const MobileLeftMenuvue_type_template_id_49f29e13_hoisted_3 = {
  class: "collapsible-header"
};
const MobileLeftMenuvue_type_template_id_49f29e13_hoisted_4 = {
  class: "collapsible-body"
};
const MobileLeftMenuvue_type_template_id_49f29e13_hoisted_5 = ["title", "href"];
function MobileLeftMenuvue_type_template_id_49f29e13_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_side_nav = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("side-nav");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", MobileLeftMenuvue_type_template_id_49f29e13_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menuWithSubmenuItems, (level2, level1) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "no-padding",
      key: level1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", MobileLeftMenuvue_type_template_id_49f29e13_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", MobileLeftMenuvue_type_template_id_49f29e13_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translateOrDefault(level1)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(level2._icon || 'icon-chevron-down')
    }, null, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MobileLeftMenuvue_type_template_id_49f29e13_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(Object.entries(level2).filter(([n]) => n[0] !== '_'), ([name, params]) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: name
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        title: params._tooltip ? _ctx.translateIfNecessary(params._tooltip) : '',
        target: "_self",
        href: _ctx.getMenuUrl(params._url)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translateIfNecessary(name)), 9, MobileLeftMenuvue_type_template_id_49f29e13_hoisted_5)]);
    }), 128))])])])])), [[_directive_side_nav, {
      activator: _ctx.activateLeftMenu
    }]])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MobileLeftMenu/MobileLeftMenu.vue?vue&type=template&id=49f29e13

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreHome/vue/src/MobileLeftMenu/MobileLeftMenu.vue?vue&type=script&lang=ts
function MobileLeftMenuvue_type_script_lang_ts_extends() { MobileLeftMenuvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return MobileLeftMenuvue_type_script_lang_ts_extends.apply(this, arguments); }




const {
  $: MobileLeftMenuvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var MobileLeftMenuvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    menu: {
      type: Object,
      required: true
    }
  },
  directives: {
    SideNav: SideNav
  },
  methods: {
    getMenuUrl(params) {
      return `?${src_MatomoUrl_MatomoUrl.stringify(MobileLeftMenuvue_type_script_lang_ts_extends(MobileLeftMenuvue_type_script_lang_ts_extends({}, src_MatomoUrl_MatomoUrl.urlParsed.value), params))}`;
    },
    translateIfNecessary(name) {
      if (name.includes('_')) {
        return translate(name);
      }
      return name;
    }
  },
  computed: {
    menuWithSubmenuItems() {
      const menu = this.menu || {};
      return Object.fromEntries(Object.entries(menu)
      // remove submenus that have no items that do not start w/ '_'
      .filter(([, level2]) => {
        const itemsWithoutUnderscore = Object.entries(level2).filter(([name]) => name[0] !== '_');
        return Object.keys(itemsWithoutUnderscore).length;
      }));
    },
    activateLeftMenu() {
      return MobileLeftMenuvue_type_script_lang_ts_$('nav .activateLeftMenu')[0];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MobileLeftMenu/MobileLeftMenu.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MobileLeftMenu/MobileLeftMenu.vue



MobileLeftMenuvue_type_script_lang_ts.render = MobileLeftMenuvue_type_template_id_49f29e13_render

/* harmony default export */ var MobileLeftMenu = (MobileLeftMenuvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/scrollToAnchorInUrl.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const {
  $: scrollToAnchorInUrl_$
} = window;
function scrollToAnchorNode($node) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  scrollToAnchorInUrl_$.scrollTo($node, 20);
}
function preventDefaultIfEventExists(event) {
  if (event) {
    event.preventDefault();
  }
}
function scrollToAnchorIfPossible(hash, event) {
  var _$node, _$node2;
  if (!hash) {
    return;
  }
  if (hash.indexOf('&') !== -1) {
    return;
  }
  let $node = null;
  try {
    $node = scrollToAnchorInUrl_$(`#${hash}`);
  } catch (err) {
    // on jquery syntax error, ignore so nothing is logged to the console
    return;
  }
  if ((_$node = $node) !== null && _$node !== void 0 && _$node.length) {
    scrollToAnchorNode($node);
    preventDefaultIfEventExists(event);
    return;
  }
  $node = scrollToAnchorInUrl_$(`a[name=${hash}]`);
  if ((_$node2 = $node) !== null && _$node2 !== void 0 && _$node2.length) {
    scrollToAnchorNode($node);
    preventDefaultIfEventExists(event);
  }
}
function isLinkWithinSamePage(location, newUrl) {
  if (location && location.origin && newUrl.indexOf(location.origin) === -1) {
    // link to different domain
    return false;
  }
  if (location && location.pathname && newUrl.indexOf(location.pathname) === -1) {
    // link to different path
    return false;
  }
  if (location && location.search && newUrl.indexOf(location.search) === -1) {
    // link with different search
    return false;
  }
  return true;
}
function handleScrollToAnchorIfPresentOnPageLoad() {
  if (window.location.hash.slice(0, 2) === '#/') {
    const hash = window.location.hash.slice(2);
    scrollToAnchorIfPossible(hash, null);
  }
}
function handleScrollToAnchorAfterPageLoad() {
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_MatomoUrl_MatomoUrl.url.value, (newUrl, oldUrl) => {
    if (!newUrl) {
      return;
    }
    const hashPos = newUrl.href.indexOf('#/');
    if (hashPos === -1) {
      return;
    }
    if (oldUrl && !isLinkWithinSamePage(oldUrl, newUrl.href)) {
      return;
    }
    const hash = newUrl.href.slice(hashPos + 2);
    scrollToAnchorIfPossible(hash, null);
  });
}
handleScrollToAnchorAfterPageLoad();
scrollToAnchorInUrl_$(handleScrollToAnchorIfPresentOnPageLoad);
function scrollToAnchorInUrl() {
  // may be called when page is only fully loaded after some additional requests
  // timeout needed to ensure Vue rendered fully
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(handleScrollToAnchorIfPresentOnPageLoad);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */














































































// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=CoreHome.umd.js.map