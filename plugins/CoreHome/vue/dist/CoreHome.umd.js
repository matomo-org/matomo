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
__webpack_require__.d(__webpack_exports__, "MatomoUrl", function() { return /* reexport */ MatomoUrl_MatomoUrl; });
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
__webpack_require__.d(__webpack_exports__, "ExpandOnClick", function() { return /* reexport */ ExpandOnClickFactory; });
__webpack_require__.d(__webpack_exports__, "ExpandOnHover", function() { return /* reexport */ ExpandOnHoverFactory; });
__webpack_require__.d(__webpack_exports__, "EnrichedHeadline", function() { return /* reexport */ EnrichedHeadline; });
__webpack_require__.d(__webpack_exports__, "ContentBlock", function() { return /* reexport */ ContentBlock; });
__webpack_require__.d(__webpack_exports__, "Comparisons", function() { return /* reexport */ Comparisons; });
__webpack_require__.d(__webpack_exports__, "Menudropdown", function() { return /* reexport */ Menudropdown; });
__webpack_require__.d(__webpack_exports__, "DatePicker", function() { return /* reexport */ DatePicker; });
__webpack_require__.d(__webpack_exports__, "DateRangePicker", function() { return /* reexport */ DateRangePicker; });
__webpack_require__.d(__webpack_exports__, "PeriodDatePicker", function() { return /* reexport */ PeriodDatePicker; });

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

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoUrl/MatomoUrl.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Similar to angulars $location but works around some limitation. Use it if you need to access
 * search params
 */
const MatomoUrl = {
  getSearchParam(paramName) {
    const hash = window.location.href.split('#');
    const regex = new RegExp(`${paramName}(\\[]|=)`);

    if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {
      const valueFromHash = window.broadcast.getValueFromHash(paramName, window.location.href); // for date, period and idsite fall back to parameter from url, if non in hash was provided

      if (valueFromHash || paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite') {
        return valueFromHash;
      }
    }

    return window.broadcast.getValueFromUrl(paramName, window.location.search);
  },

  onLocationChange(callback) {
    window.addEventListener('hashchange', () => {
      const newLocation = new URLSearchParams(window.location.hash.replace(/^[#?/]+/, ''));
      callback(newLocation);
    });
  },

  parseHashQuery() {
    return this.parseQueryString(window.location.hash.replace(/^[#?/]+/, ''));
  },

  parseQueryString(query) {
    const params = new URLSearchParams(query);
    const result = {}; // TODO: doesn't handle object query params

    Array.from(params.keys()).forEach(name => {
      if (/[[\]]/.test(name) || name.indexOf('%5B%5D') !== -1) {
        result[name] = params.getAll(name);
      } else {
        result[name] = params.get(name);
      }
    });
    return result;
  },

  stringify(search) {
    // TODO: using $ since URLSearchParams does not handle array params the way Matomo uses them
    return $.param(search);
  }

};
/* harmony default export */ var MatomoUrl_MatomoUrl = (MatomoUrl);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoUrl/MatomoUrl.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikUrl() {
  const model = {
    getSearchParam: MatomoUrl_MatomoUrl.getSearchParam.bind(MatomoUrl_MatomoUrl)
  };
  return model;
}

piwikUrl.$inject = [];
angular.module('piwikApp.service').service('piwikUrl', piwikUrl);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.ts
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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function format(date) {
  return $.datepicker.formatDate('yy-mm-dd', date);
}
function getToday() {
  const date = new Date(Date.now()); // undo browser timezone

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

  const strDate = decodeURIComponent(date);

  if (strDate === 'today' || strDate === 'now') {
    return getToday();
  }

  if (strDate === 'yesterday' // note: ignoring the 'same time' part since the frontend doesn't care about the time
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

  try {
    return $.datepicker.parseDate('yy-mm-dd', strDate);
  } catch (err) {
    // angular swallows this error, so manual console log here
    console.error(err.message || err);
    throw err;
  }
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Matomo/Matomo.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



let originalTitle;
const {
  piwik: Matomo_piwik,
  broadcast: Matomo_broadcast,
  piwikHelper: Matomo_piwikHelper
} = window;
Matomo_piwik.helper = Matomo_piwikHelper;
Matomo_piwik.broadcast = Matomo_broadcast;

function isValidPeriod(periodStr, dateStr) {
  try {
    Periods_Periods.parse(periodStr, dateStr);
    return true;
  } catch (e) {
    return false;
  }
}

Matomo_piwik.updatePeriodParamsFromUrl = function updatePeriodParamsFromUrl() {
  let date = MatomoUrl_MatomoUrl.getSearchParam('date');
  const period = MatomoUrl_MatomoUrl.getSearchParam('period');

  if (!isValidPeriod(period, date)) {
    // invalid data in URL
    return;
  }

  if (Matomo_piwik.period === period && Matomo_piwik.currentDateString === date) {
    // this period / date is already loaded
    return;
  }

  Matomo_piwik.period = period;
  const dateRange = Periods_Periods.parse(period, date).getDateRange();
  Matomo_piwik.startDateString = format(dateRange[0]);
  Matomo_piwik.endDateString = format(dateRange[1]);
  Matomo_piwik.updateDateInTitle(date, period); // do not set anything to previousN/lastN, as it's more useful to plugins
  // to have the dates than previousN/lastN.

  if (Matomo_piwik.period === 'range') {
    date = `${Matomo_piwik.startDateString},${Matomo_piwik.endDateString}`;
  }

  Matomo_piwik.currentDateString = date;
};

Matomo_piwik.updateDateInTitle = function updateDateInTitle(date, period) {
  if (!$('.top_controls #periodString').length) {
    return;
  } // Cache server-rendered page title


  originalTitle = originalTitle || document.title;

  if (originalTitle.indexOf(Matomo_piwik.siteName) === 0) {
    const dateString = ` - ${Periods_Periods.parse(period, date).getPrettyString()} `;
    document.title = `${Matomo_piwik.siteName}${dateString}${originalTitle.substr(Matomo_piwik.siteName.length)}`;
  }
};

Matomo_piwik.hasUserCapability = function hasUserCapability(capability) {
  return window.angular.isArray(Matomo_piwik.userCapabilities) && Matomo_piwik.userCapabilities.indexOf(capability) !== -1;
};

Matomo_piwik.on = function addMatomoEventListener(eventName, listener) {
  function listenerWrapper(evt) {
    listener(...evt.detail); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;
  window.addEventListener(eventName, listener);
};

Matomo_piwik.off = function removeMatomoEventListener(eventName, listener) {
  window.removeEventListener(eventName, listener.wrapper);
};

Matomo_piwik.postEvent = function postMatomoEvent(eventName, ...args // eslint-disable-line
) {
  const event = new CustomEvent(eventName, {
    detail: args
  });
  window.dispatchEvent(event); // required until angularjs is removed

  return Matomo_piwik.helper.getAngularDependency('$rootScope') // eslint-disable-line
  .$oldEmit(eventName, ...args);
};

const Matomo = Matomo_piwik;
/* harmony default export */ var Matomo_Matomo = (Matomo);
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

  $rootScope.$emit = function emitWrapper(name, ...args) {
    return Matomo_Matomo.postEvent(name, ...args);
  };

  $rootScope.$on('$locationChangeSuccess', piwik.updatePeriodParamsFromUrl);
}

initPiwikService.$inject = ['piwik', '$rootScope'];
window.angular.module('piwikApp.service').run(initPiwikService);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/translate.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function translate(translationStringId, ...values) {
  let pkArgs = values; // handle variadic args AND single array of values (to match _pk_translate signature)

  if (values.length === 1 && values[0] && values[0] instanceof Array) {
    [pkArgs] = values;
  }

  return window._pk_translate(translationStringId, pkArgs); // eslint-disable-line
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Day.ts
function Day_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

}
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

angular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts
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
  for (let i = this.length; i >= 0; i -= 1) {
    if (!this[i] || this[i].readyState === 4) {
      this.splice(i, 1);
    }
  }
};

window.globalAjaxQueue.push = function globalAjaxQueuePush(...args) {
  this.active += args.length; // cleanup ajax queue

  this.clean(); // call original array push

  return Array.prototype.push.call(this, ...args);
};

window.globalAjaxQueue.abort = function globalAjaxQueueAbort() {
  // abort all queued requests if possible
  this.forEach(x => x && x.abort && x.abort()); // remove all elements from array

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
    console.log(`Request failed: ${deferred.responseText}`); // mostly for tests

    return;
  }

  const loadingError = $('#loadingError');

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


class AjaxHelper_AjaxHelper {
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
  static fetch(params) {
    const helper = new AjaxHelper_AjaxHelper();
    helper.setFormat('json');
    helper.addParams({
      module: 'API',
      format: 'json',
      ...params
    }, 'get');
    return helper.send();
  }

  constructor() {
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


  addParams(initialParams, type) {
    const params = typeof initialParams === 'string' ? window.broadcast.getValuesFromUrl(initialParams) : initialParams;
    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];
    Object.keys(params).forEach(key => {
      const value = params[key];

      if (arrayParams.indexOf(key) !== -1 && !value) {
        return;
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
    const urlsProcessed = urls.map(u => typeof u === 'string' ? u : $.param(u));
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
    if ($(this.errorElement).length) {
      $(this.errorElement).hide();
    }

    if (this.loadingElement) {
      $(this.loadingElement).fadeIn();
    }

    this.requestHandle = this.buildAjaxCall();
    window.globalAjaxQueue.push(this.requestHandle);
    return new Promise((resolve, reject) => {
      this.requestHandle.then(resolve).fail(xhr => {
        if (xhr.statusText !== 'abort') {
          console.log(`Warning: the ${$.param(this.getParams)} request failed!`);
          reject(xhr);
        }
      });
    });
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
    } // we took care of encoding &segment properly already, so we don't use $.param for it ($.param
    // URL encodes the values)


    if (parameters.segment) {
      url = `${url}segment=${parameters.segment}&`;
      delete parameters.segment;
    }

    if (parameters.date) {
      url = `${url}date=${decodeURIComponent(parameters.date.toString())}&`;
      delete parameters.date;
    }

    url += $.param(parameters);
    const ajaxCall = {
      type: 'POST',
      async: true,
      url,
      dataType: this.format || 'json',
      complete: this.completeCallback,
      error: function errorCallback(...args) {
        window.globalAjaxQueue.active -= 1;

        if (self.errorCallback) {
          self.errorCallback.apply(this, args);
        }
      },
      success: (response, status, request) => {
        if (this.loadingElement) {
          $(this.loadingElement).hide();
        }

        if (response && response.result === 'error' && !this.useRegularCallbackInCaseOfError) {
          let placeAt = null;
          let type = 'toast';

          if ($(this.errorElement).length && response.message) {
            $(this.errorElement).show();
            placeAt = this.errorElement;
            type = null;
          }

          if (response.message) {
            const UI = window['require']('piwik/UI'); // eslint-disable-line

            const notification = new UI.Notification();
            notification.show(response.message, {
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
    return $.ajax(ajaxCall);
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
    const mergedParams = { ...defaultParams,
      ...params
    };
    return mergedParams;
  }
  /**
   * Mixin the default parameters to send as GET
   *
   * @param   params   parameter object
   */


  mixinDefaultGetParams(originalParams) {
    const segment = MatomoUrl_MatomoUrl.getSearchParam('segment');
    const defaultParams = {
      idSite: Matomo_Matomo.idSite ? Matomo_Matomo.idSite.toString() : broadcast.getValueFromUrl('idSite'),
      period: Matomo_Matomo.period || broadcast.getValueFromUrl('period'),
      segment
    };
    const params = originalParams; // never append token_auth to url

    if (params.token_auth) {
      params.token_auth = null;
      delete params.token_auth;
    }

    Object.keys(defaultParams).forEach(key => {
      if (this.useGETDefaultParameter(key) && !params[key] && !this.postParams[key] && defaultParams[key]) {
        params[key] = defaultParams[key];
      }
    }); // handle default date & period if not already set

    if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {
      params.date = Matomo_Matomo.currentDateString;
    }

    return params;
  }

}
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
  mounted(element, binding) {
    let options = {};
    $(element).addClass('matomo-dropdown-menu');
    const isSubmenu = !!$(element).parent().closest('.dropdown-content');

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
      const binding = {
        instance: null,
        value: {
          activates: $(`#${attrs.activates}`)[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      $timeout(() => {
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
function FocusAnywhereButHere() {
  let element;
  let binding;
  let isMouseDown = false;
  let hasScrolled = false;

  function onClickOutsideElement(event) {
    const hadUsedScrollbar = isMouseDown && hasScrolled;
    isMouseDown = false;
    hasScrolled = false;

    if (hadUsedScrollbar) {
      return;
    }

    if (!element.contains(event.target)) {
      if (binding.value.blur) {
        binding.value.blur();
      }
    }
  }

  function onScroll() {
    hasScrolled = true;
  }

  function onMouseDown() {
    isMouseDown = true;
    hasScrolled = false;
  }

  function onEscapeHandler(event) {
    if (event.which === 27) {
      setTimeout(() => {
        isMouseDown = false;
        hasScrolled = false;

        if (binding.value.blur) {
          binding.value.blur();
        }
      }, 0);
    }
  }

  const doc = document.documentElement;
  return {
    mounted(el, b) {
      element = el;
      binding = b;
      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mousedown', onMouseDown);
      doc.addEventListener('mouseup', onClickOutsideElement);
      doc.addEventListener('scroll', onScroll);
    },

    unmounted() {
      doc.removeEventListener('keyup', onEscapeHandler);
      doc.removeEventListener('mousedown', onMouseDown);
      doc.removeEventListener('mouseup', onClickOutsideElement);
      doc.removeEventListener('scroll', onScroll);
    }

  };
}
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
      const binding = {
        instance: null,
        value: {
          blur: () => {
            setTimeout(() => {
              scope.$apply(attr.piwikFocusAnywhereButHere);
            }, 0);
          }
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      const wrapped = FocusAnywhereButHere();
      wrapped.mounted(element[0], binding, null, null);
      scope.$on('$destroy', () => wrapped.unmounted(element[0], binding, null, null));
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
/* harmony default export */ var FocusIf = ({
  updated(el, binding) {
    if (binding.value.focusIf) {
      setTimeout(() => {
        el.focus();
      }, 5);
    }
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
      scope.$watch(attrs.piwikFocusIf, newValue => {
        const binding = {
          instance: null,
          value: {
            focusIf: !!newValue,
            afterFocus: () => scope.$apply()
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

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnClick: ExpandOnClick(), // function call is important since we store state
 *                                   // in this directive
 * }
 */

function ExpandOnClickFactory() {
  let element;
  let isMouseDown = false;
  let hasScrolled = false;

  function onExpand() {
    element.classList.toggle('expanded');
    const positionElement = element.querySelector('.dropdown.positionInViewport');

    if (positionElement) {
      Matomo_Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }

  function onClickOutsideElement(event) {
    const hadUsedScrollbar = isMouseDown && hasScrolled;
    isMouseDown = false;
    hasScrolled = false;

    if (hadUsedScrollbar) {
      return;
    }

    if (!element.contains(event.target)) {
      element.classList.remove('expanded');
    }
  }

  function onScroll() {
    hasScrolled = true;
  }

  function onMouseDown() {
    isMouseDown = true;
    hasScrolled = false;
  }

  function onEscapeHandler(event) {
    if (event.which === 27) {
      isMouseDown = false;
      hasScrolled = false;
      element.classList.remove('expanded');
    }
  }

  const doc = document.documentElement;
  return {
    mounted(el, binding) {
      element = el;
      binding.value.expander.addEventListener('click', onExpand);
      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mousedown', onMouseDown);
      doc.addEventListener('mouseup', onClickOutsideElement);
      doc.addEventListener('scroll', onScroll);
    },

    unmounted(el, binding) {
      binding.value.expander.removeEventListener('click', onExpand);
      doc.removeEventListener('keyup', onEscapeHandler);
      doc.removeEventListener('mousedown', onMouseDown);
      doc.removeEventListener('mouseup', onClickOutsideElement);
      doc.removeEventListener('scroll', onScroll);
    }

  };
}
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
      const binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      const wrapped = ExpandOnClickFactory();
      wrapped.mounted(element[0], binding, null, null);
      scope.$on('$destroy', () => wrapped.unmounted(element[0], binding, null, null));
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

/**
 * Usage (in a component):
 *
 * directives: {
 *   ExpandOnHover: ExpandOnHover(), // function call is important since we store state
 *                                   // in this directive
 * }
 */

function ExpandOnHoverFactory() {
  let element;

  function onMouseEnter() {
    element.classList.add('expanded');
    const positionElement = element.querySelector('.dropdown.positionInViewport');

    if (positionElement) {
      Matomo_Matomo.helper.setMarginLeftToBeInViewport(positionElement);
    }
  }

  function onMouseLeave() {
    element.classList.remove('expanded');
  }

  function onClickOutsideElement(event) {
    if (!element.contains(event.target)) {
      element.classList.remove('expanded');
    }
  }

  function onEscapeHandler(event) {
    if (event.which === 27) {
      element.classList.remove('expanded');
    }
  }

  const doc = document.documentElement;
  return {
    mounted(el, binding) {
      element = el;
      binding.value.expander.addEventListener('mouseenter', onMouseEnter);
      element.addEventListener('mouseleave', onMouseLeave);
      doc.addEventListener('keyup', onEscapeHandler);
      doc.addEventListener('mouseup', onClickOutsideElement);
    },

    unmounted(el, binding) {
      binding.value.expander.removeEventListener('mouseenter', onMouseEnter);
      element.removeEventListener('mouseleave', onMouseLeave);
      document.removeEventListener('keyup', onEscapeHandler);
      document.removeEventListener('mouseup', onClickOutsideElement);
    }

  };
}
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
      const binding = {
        instance: null,
        value: {
          expander: element.find('.title').first()[0]
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      const wrapped = ExpandOnHoverFactory();
      wrapped.mounted(element[0], binding, null, null);
      scope.$on('$destroy', () => wrapped.unmounted(element[0], binding, null, null));
    }
  };
}

piwikExpandOnHover.$inject = [];
angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=15ad69b4

const _hoisted_1 = {
  ref: "root"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.modelValue]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/MatomoDialog/MatomoDialog.vue?vue&type=template&id=15ad69b4

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
  emits: ['yes', 'no', 'closeEnd', 'close', 'update:modelValue'],

  activated() {
    this.$emit('update:modelValue', false);
  },

  watch: {
    modelValue(newValue, oldValue) {
      if (newValue) {
        const slotElement = this.element || this.$refs.root.firstElementChild;
        Matomo_Matomo.helper.modalConfirm(slotElement, {
          yes: () => {
            this.$emit('yes');
          },
          no: () => {
            this.$emit('no');
          }
        }, {
          onCloseEnd: () => {
            // materialize removes the child element, so we move it back to the slot
            if (!this.element) {
              this.$refs.root.appendChild(slotElement);
            }

            this.$emit('update:modelValue', false);
            this.$emit('closeEnd');
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



MatomoDialogvue_type_script_lang_ts.render = render

/* harmony default export */ var MatomoDialog = (MatomoDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/createAngularJsAdapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


let transcludeCounter = 0;

function toKebabCase(arg) {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1).replace(/[A-Z]/g, s => `-${s.toLowerCase()}`);
}

function toAngularJsCamelCase(arg) {
  return arg.substring(0, 1).toLowerCase() + arg.substring(1).replace(/-([a-z])/g, (s, p) => p.toUpperCase());
}

function createAngularJsAdapter(options) {
  const {
    component,
    scope = {},
    events = {},
    $inject,
    directiveName,
    transclude,
    mountPointFactory,
    postCreate,
    noScope,
    restrict = 'A'
  } = options;
  const currentTranscludeCounter = transcludeCounter;

  if (transclude) {
    transcludeCounter += 1;
  }

  const angularJsScope = {};
  Object.entries(scope).forEach(([scopeVarName, info]) => {
    if (!info.vue) {
      info.vue = scopeVarName;
    }

    if (info.angularJsBind) {
      angularJsScope[scopeVarName] = info.angularJsBind;
    }
  });

  function angularJsAdapter(...injectedServices) {
    const adapter = {
      restrict,
      scope: noScope ? undefined : angularJsScope,
      compile: function angularJsAdapterCompile() {
        return {
          post: function angularJsAdapterLink(ngScope, ngElement, ngAttrs) {
            const clone = transclude ? ngElement.find(`[ng-transclude][counter=${currentTranscludeCounter}]`) : null; // build the root vue template

            let rootVueTemplate = '<root-component';
            Object.entries(events).forEach(info => {
              const [eventName] = info;
              rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}', $event)"`;
            });
            Object.entries(scope).forEach(([key, info]) => {
              if (info.angularJsBind === '&') {
                const eventName = toKebabCase(key);

                if (!events[eventName]) {
                  // pass through scope & w/o a custom event handler
                  rootVueTemplate += ` @${eventName}="onEventHandler('${eventName}', $event)"`;
                }
              } else {
                rootVueTemplate += ` :${info.vue}="${info.vue}"`;
              }
            });
            rootVueTemplate += '>';

            if (transclude) {
              rootVueTemplate += '<div ref="transcludeTarget"/>';
            }

            rootVueTemplate += '</root-component>'; // build the vue app

            const app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
              template: rootVueTemplate,

              data() {
                const initialData = {};
                Object.entries(scope).forEach(([scopeVarName, info]) => {
                  let value = ngScope[scopeVarName];

                  if (typeof value === 'undefined' && typeof info.default !== 'undefined') {
                    value = info.default instanceof Function ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices) : info.default;
                  }

                  initialData[info.vue] = value;
                });
                return initialData;
              },

              setup() {
                if (transclude) {
                  const transcludeTarget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
                  return {
                    transcludeTarget
                  };
                }

                return undefined;
              },

              methods: {
                onEventHandler(name, $event) {
                  const scopePropertyName = toAngularJsCamelCase(name);

                  if (ngScope[scopePropertyName]) {
                    ngScope[scopePropertyName]($event);
                  }

                  if (events[name]) {
                    events[name]($event, ngScope, ngElement, ngAttrs, ...injectedServices);
                  }
                }

              }
            });
            app.config.globalProperties.$sanitize = window.vueSanitize;
            app.config.globalProperties.translate = translate;
            app.component('root-component', component); // mount the app

            const mountPoint = mountPointFactory ? mountPointFactory(ngScope, ngElement, ngAttrs, ...injectedServices) : ngElement[0];
            const vm = app.mount(mountPoint); // setup watches to bind between angularjs + vue

            Object.entries(scope).forEach(([scopeVarName, info]) => {
              if (!info.angularJsBind || info.angularJsBind === '&') {
                return;
              }

              ngScope.$watch(scopeVarName, newValue => {
                if (typeof info.default !== 'undefined' && typeof newValue === 'undefined') {
                  vm[scopeVarName] = info.default instanceof Function ? info.default(ngScope, ngElement, ngAttrs, ...injectedServices) : info.default;
                } else {
                  vm[scopeVarName] = newValue;
                }
              });
            });

            if (transclude) {
              $(vm.transcludeTarget).append(clone);
            }

            if (postCreate) {
              postCreate(vm, ngScope, ngElement, ngAttrs, ...injectedServices);
            }
          }
        };
      }
    };

    if (transclude) {
      adapter.transclude = true;
      adapter.template = `<div ng-transclude counter="${currentTranscludeCounter}"/>`;
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
      default: (scope, element) => element[0]
    }
  },
  events: {
    yes: ($event, scope, element, attrs) => {
      if (attrs.yes) {
        scope.$eval(attrs.yes);
        setTimeout(() => {
          scope.$apply();
        }, 0);
      }
    },
    no: ($event, scope, element, attrs) => {
      if (attrs.no) {
        scope.$eval(attrs.no);
        setTimeout(() => {
          scope.$apply();
        }, 0);
      }
    },
    close: ($event, scope, element, attrs) => {
      if (attrs.close) {
        scope.$eval(attrs.close);
        setTimeout(() => {
          scope.$apply();
        }, 0);
      }
    },
    'update:modelValue': (newValue, scope, element, attrs, $parse) => {
      setTimeout(() => {
        scope.$apply($parse(attrs.piwikDialog).assign(scope, newValue));
      }, 0);
    }
  },
  $inject: ['$parse'],
  directiveName: 'piwikDialog',
  transclude: true,
  mountPointFactory: (scope, element) => {
    const vueRootPlaceholder = $('<div class="vue-placeholder"/>');
    vueRootPlaceholder.appendTo(element);
    return vueRootPlaceholder[0];
  },
  postCreate: (vm, scope, element, attrs) => {
    scope.$watch(attrs.piwikDialog, (newValue, oldValue) => {
      if (oldValue !== newValue) {
        vm.modelValue = newValue || false;
      }
    });
  },
  noScope: true
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=dac5e122

const EnrichedHeadlinevue_type_template_id_dac5e122_hoisted_1 = {
  key: 0,
  class: "title",
  tabindex: "6"
};
const _hoisted_2 = ["href", "title"];
const _hoisted_3 = {
  class: "iconsBar"
};
const _hoisted_4 = ["href", "title"];

const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

const _hoisted_6 = [_hoisted_5];
const _hoisted_7 = ["title"];

const _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);

const _hoisted_9 = [_hoisted_8];
const _hoisted_10 = {
  class: "ratingIcons"
};
const _hoisted_11 = {
  class: "inlineHelp"
};
const _hoisted_12 = ["innerHTML"];
const _hoisted_13 = ["href"];
function EnrichedHeadlinevue_type_template_id_dac5e122_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RateFeature = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RateFeature");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "enrichedHeadline",
    onMouseenter: _cache[1] || (_cache[1] = $event => _ctx.showIcons = true),
    onMouseleave: _cache[2] || (_cache[2] = $event => _ctx.showIcons = false),
    ref: "root"
  }, [!_ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", EnrichedHeadlinevue_type_template_id_dac5e122_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
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
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showInlineHelp = !_ctx.showInlineHelp),
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=template&id=dac5e122

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts


 // working around a cycle in dependencies (CoreHome depends on Feedback, Feedback depends on
// CoreHome)
// TODO: may need a generic solution at some point, but it's bad practice to have
// cyclic dependencies like this. it worked before because it was individual files
// dependening on each other, not whole plugins.

const RateFeature = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineAsyncComponent"])(() => new Promise(resolve => {
  window.$(document).ready(() => {
    resolve(window.Feedback.RateFeature); // eslint-disable-line
  });
}));
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
    RateFeature
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
    const {
      root
    } = this.$refs;

    if (!this.actualInlineHelp) {
      let helpNode = root.querySelector('.title .inlineHelp');

      if (!helpNode && root.parentElement.nextElementSibling) {
        // hack for reports :(
        helpNode = root.parentElement.nextElementSibling.querySelector('.reportDocumentation');
      }

      if (helpNode) {
        // hackish solution to get binded html of p tag within the help node
        // at this point the ng-bind-html is not yet converted into html when report is not
        // initially loaded. Using $compile doesn't work. So get and set it manually
        const helpDocs = helpNode.getAttribute('data-content').trim();

        if (helpDocs.length) {
          this.actualInlineHelp = `<p>${helpDocs}</p>`;
          setTimeout(() => helpNode.remove(), 0);
        }
      }
    }

    if (!this.actualFeatureName) {
      this.actualFeatureName = root.querySelector('.title').textContent;
    }

    if (this.reportGenerated && Periods_Periods.parse(Matomo_Matomo.period, Matomo_Matomo.currentDateString).containsToday()) {
      window.$(root.querySelector('.report-generated')).tooltip({
        track: true,
        content: this.reportGenerated,
        items: 'div',
        show: false,
        hide: false
      });
    }
  }

}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/EnrichedHeadline/EnrichedHeadline.vue



EnrichedHeadlinevue_type_script_lang_ts.render = EnrichedHeadlinevue_type_template_id_dac5e122_render

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

const ContentBlockvue_type_template_id_09ef9e02_hoisted_1 = {
  class: "card",
  ref: "root"
};
const ContentBlockvue_type_template_id_09ef9e02_hoisted_2 = {
  class: "card-content"
};
const ContentBlockvue_type_template_id_09ef9e02_hoisted_3 = {
  key: 0,
  class: "card-title"
};
const ContentBlockvue_type_template_id_09ef9e02_hoisted_4 = {
  key: 1,
  class: "card-title"
};
const ContentBlockvue_type_template_id_09ef9e02_hoisted_5 = {
  ref: "content"
};
function ContentBlockvue_type_template_id_09ef9e02_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_2, [_ctx.contentTitle && !_ctx.actualFeature && !_ctx.helpUrl && !_ctx.actualHelpText ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_09ef9e02_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.contentTitle), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.contentTitle && (_ctx.actualFeature || _ctx.helpUrl || _ctx.actualHelpText) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", ContentBlockvue_type_template_id_09ef9e02_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.actualFeature,
    "help-url": _ctx.helpUrl,
    "inline-help": _ctx.actualHelpText
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.contentTitle), 1)]),
    _: 1
  }, 8, ["feature-name", "help-url", "inline-help"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ContentBlockvue_type_template_id_09ef9e02_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 512)])], 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=template&id=09ef9e02

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ContentBlock/ContentBlock.vue?vue&type=script&lang=ts


let adminContent = null;
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

  data() {
    return {
      actualFeature: this.feature,
      actualHelpText: this.helpText
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
    const {
      root,
      content
    } = this.$refs;

    if (this.anchor) {
      const anchorElement = document.createElement('a');
      anchorElement.id = this.anchor;
      root.parentElement.prepend(anchorElement);
    }

    setTimeout(() => {
      const inlineHelp = content.querySelector('.contentHelp');

      if (inlineHelp) {
        this.actualHelpText = inlineHelp.innerHTML;
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

    let contentTopPosition;

    if (adminContent) {
      contentTopPosition = adminContent.offsetTop;
    }

    if (contentTopPosition || contentTopPosition === 0) {
      const parents = root.closest('[piwik-widget-loader]'); // when shown within the widget loader, we need to get the offset of that element
      // as the widget loader might be still shown. Would otherwise not position correctly
      // the widgets on the admin home page

      const topThis = parents ? parents.offsetTop : root.offsetTop;

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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.store.ts
function Comparisons_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */






const SERIES_COLOR_COUNT = 8;
const SERIES_SHADE_COUNT = 3;

function wrapArray(values) {
  if (!values) {
    return [];
  }

  return values instanceof Array ? values : [values];
}

class Comparisons_store_ComparisonsStore {
  get state() {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState);
  }

  constructor() {
    Comparisons_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      segmentComparisons: [],
      periodComparisons: [],
      comparisonsDisabledFor: []
    }));

    Comparisons_store_defineProperty(this, "colors", {});

    MatomoUrl_MatomoUrl.onLocationChange(() => this.updateComparisonsFromQueryParams());
    Matomo_Matomo.on('piwikSegmentationInited', () => this.updateComparisonsFromQueryParams());
    this.loadComparisonsDisabledFor();
    $(() => {
      this.updateComparisonsFromQueryParams();
      this.colors = this.getAllSeriesColors();
    });
  }

  getComparisons() {
    return this.getSegmentComparisons().concat(this.getPeriodComparisons());
  }

  isComparing() {
    return this.isComparisonEnabled() // first two in each array are for the currently selected segment/period
    && (this.privateState.segmentComparisons.length > 1 || this.privateState.periodComparisons.length > 1);
  }

  isComparingPeriods() {
    return this.getPeriodComparisons().length > 1; // first is currently selected period
  }

  getSegmentComparisons() {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.privateState.segmentComparisons;
  }

  getPeriodComparisons() {
    if (!this.isComparisonEnabled()) {
      return [];
    }

    return this.privateState.periodComparisons;
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
    return this.checkEnabledForCurrentPage();
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
          params: { ...segmentComp.params,
            ...periodComp.params
          },
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

    const newComparisons = Array().concat(this.privateState.segmentComparisons);
    newComparisons.splice(index, 1);
    const extraParams = {};

    if (index === 0) {
      extraParams.segment = newComparisons[0].params.segment;
    }

    this.updateQueryParamsFromComparisons(newComparisons, this.privateState.periodComparisons, extraParams);
  }

  addSegmentComparison(params) {
    if (!this.isComparisonEnabled()) {
      throw new Error('Comparison disabled.');
    }

    const newComparisons = this.privateState.segmentComparisons.concat([{
      params,
      index: -1,
      title: ''
    }]);
    this.updateQueryParamsFromComparisons(newComparisons, this.privateState.periodComparisons);
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
    }; // change the page w/ these new param values

    if (Matomo_Matomo.helper.isAngularRenderingThePage()) {
      const search = MatomoUrl_MatomoUrl.parseHashQuery();
      const newSearch = { ...search,
        ...compareParams,
        ...extraParams
      };
      delete newSearch['compareSegments[]'];
      delete newSearch['comparePeriods[]'];
      delete newSearch['compareDates[]'];

      if (JSON.stringify(newSearch) !== JSON.stringify(search)) {
        window.location.hash = `#?${MatomoUrl_MatomoUrl.stringify(newSearch)}`;
      }

      return;
    }

    const paramsToRemove = [];
    ['compareSegments', 'comparePeriods', 'compareDates'].forEach(name => {
      if (!compareParams[name].length) {
        paramsToRemove.push(name);
      }
    }); // angular is not rendering the page (ie, we are in the embedded dashboard) or we need to change
    // the segment
    // TODO: move this to URL service?

    const url = $.param({ ...extraParams
    }).replace(/%5B%5D/g, '[]');
    const strHash = $.param({ ...compareParams
    }).replace(/%5B%5D/g, '[]');
    window.broadcast.propagateNewPage(url, undefined, strHash, paramsToRemove);
  }

  getAllSeriesColors() {
    const {
      ColorManager
    } = Matomo_Matomo;
    const seriesColorNames = [];

    for (let i = 0; i < SERIES_COLOR_COUNT; i += 1) {
      seriesColorNames.push(`series${i}`);

      for (let j = 0; j < SERIES_SHADE_COUNT; j += 1) {
        seriesColorNames.push(`series${i}-shade${j}`);
      }
    }

    return ColorManager.getColors('comparison-series-color', seriesColorNames);
  }

  updateComparisonsFromQueryParams() {
    let title;
    let availableSegments = [];

    try {
      availableSegments = $('.segmentEditorPanel').data('uiControlObject').impl.availableSegments || [];
    } catch (e) {// segment editor is not initialized yet
    }

    let compareSegments = wrapArray(MatomoUrl_MatomoUrl.getSearchParam('compareSegments')) || [];
    compareSegments = compareSegments instanceof Array ? compareSegments : [compareSegments];
    let comparePeriods = wrapArray(MatomoUrl_MatomoUrl.getSearchParam('comparePeriods')) || [];
    comparePeriods = comparePeriods instanceof Array ? comparePeriods : [comparePeriods];
    let compareDates = wrapArray(MatomoUrl_MatomoUrl.getSearchParam('compareDates')) || [];
    compareDates = compareDates instanceof Array ? compareDates : [compareDates]; // add base comparisons

    compareSegments.unshift(MatomoUrl_MatomoUrl.getSearchParam('segment'));
    comparePeriods.unshift(MatomoUrl_MatomoUrl.getSearchParam('period'));
    compareDates.unshift(MatomoUrl_MatomoUrl.getSearchParam('date'));
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
    const newPeriodComparisons = [];

    for (let i = 0; i < Math.min(compareDates.length, comparePeriods.length); i += 1) {
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

    this.setComparisons(newSegmentComparisons, newPeriodComparisons);
  }

  checkEnabledForCurrentPage() {
    // category/subcategory is not included on top bar pages, so in that case we use module/action
    const category = MatomoUrl_MatomoUrl.getSearchParam('category') || MatomoUrl_MatomoUrl.getSearchParam('module');
    const subcategory = MatomoUrl_MatomoUrl.getSearchParam('subcategory') || MatomoUrl_MatomoUrl.getSearchParam('action');
    const id = `${category}.${subcategory}`;
    const isEnabled = this.privateState.comparisonsDisabledFor.indexOf(id) === -1 && this.privateState.comparisonsDisabledFor.indexOf(`${category}.*`) === -1;
    document.documentElement.classList.toggle('comparisonsDisabled', !isEnabled);
    return isEnabled;
  }

  setComparisons(newSegmentComparisons, newPeriodComparisons) {
    const oldSegmentComparisons = this.privateState.segmentComparisons;
    const oldPeriodComparisons = this.privateState.periodComparisons;
    this.privateState.segmentComparisons = newSegmentComparisons;
    this.privateState.periodComparisons = newPeriodComparisons;

    if (JSON.stringify(oldPeriodComparisons) !== JSON.stringify(newPeriodComparisons) || JSON.stringify(oldSegmentComparisons) !== JSON.stringify(newSegmentComparisons)) {
      Matomo_Matomo.postEvent('piwikComparisonsChanged');
    }
  }

  loadComparisonsDisabledFor() {
    AjaxHelper_AjaxHelper.fetch({
      module: 'API',
      method: 'API.getPagesComparisonsDisabledFor'
    }).then(result => {
      this.privateState.comparisonsDisabledFor = result;
    });
  }

}
/* harmony default export */ var Comparisons_store = (new Comparisons_store_ComparisonsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=4f8421ca

const Comparisonsvue_type_template_id_4f8421ca_hoisted_1 = {
  key: 0,
  ref: "root"
};
const Comparisonsvue_type_template_id_4f8421ca_hoisted_2 = {
  class: "comparison-type"
};
const Comparisonsvue_type_template_id_4f8421ca_hoisted_3 = ["title"];
const Comparisonsvue_type_template_id_4f8421ca_hoisted_4 = ["href"];
const Comparisonsvue_type_template_id_4f8421ca_hoisted_5 = ["title"];
const Comparisonsvue_type_template_id_4f8421ca_hoisted_6 = {
  class: "comparison-period-label"
};
const Comparisonsvue_type_template_id_4f8421ca_hoisted_7 = ["onClick"];
const Comparisonsvue_type_template_id_4f8421ca_hoisted_8 = ["title"];
const Comparisonsvue_type_template_id_4f8421ca_hoisted_9 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  }
};
const Comparisonsvue_type_template_id_4f8421ca_hoisted_10 = ["alt"];
function Comparisonsvue_type_template_id_4f8421ca_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.comparisonsService.isComparing() ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Comparisonsvue_type_template_id_4f8421ca_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Comparisons')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.comparisonsService.getSegmentComparisons(), (comparison, $index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "comparison card",
      key: comparison.index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_4f8421ca_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Segment')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "title",
      title: comparison.title + '<br/>' + decodeURIComponent(comparison.params.segment)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      href: _ctx.getUrlToSegment(comparison.params.segment)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(comparison.title), 9, Comparisonsvue_type_template_id_4f8421ca_hoisted_4)], 8, Comparisonsvue_type_template_id_4f8421ca_hoisted_3), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.comparisonsService.getPeriodComparisons(), periodComparison => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "comparison-period",
        key: periodComparison.index,
        title: _ctx.getComparisonTooltip(comparison, periodComparison)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "comparison-dot",
        style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
          'background-color': _ctx.comparisonsService.getSeriesColor(comparison, periodComparison)
        })
      }, null, 4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Comparisonsvue_type_template_id_4f8421ca_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(periodComparison.title) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getComparisonPeriodType(periodComparison)) + ") ", 1)], 8, Comparisonsvue_type_template_id_4f8421ca_hoisted_5);
    }), 128)), _ctx.comparisonsService.getSegmentComparisons().length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "remove-button",
      onClick: $event => _ctx.comparisonsService.removeSegmentComparison($index)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon icon-close",
      title: _ctx.translate('General_ClickToRemoveComp')
    }, null, 8, Comparisonsvue_type_template_id_4f8421ca_hoisted_8)], 8, Comparisonsvue_type_template_id_4f8421ca_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Comparisonsvue_type_template_id_4f8421ca_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, Comparisonsvue_type_template_id_4f8421ca_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=template&id=4f8421ca

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts






/* harmony default export */ var Comparisonsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {},

  data() {
    return {
      comparisonsService: Comparisons_store,
      comparisonTooltips: null
    };
  },

  methods: {
    comparisonHasSegment(comparison) {
      return typeof comparison.params.segment !== 'undefined';
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

      return this.comparisonTooltips[periodComparison.index][segmentComparison.index];
    },

    getUrlToSegment(segment) {
      let {
        hash
      } = window.location;
      hash = window.broadcast.updateParamValue('comparePeriods[]=', hash);
      hash = window.broadcast.updateParamValue('compareDates[]=', hash);
      hash = window.broadcast.updateParamValue('compareSegments[]=', hash);
      hash = window.broadcast.updateParamValue(`segment=${encodeURIComponent(segment)}`, hash);
      return window.location.search + hash;
    },

    setUpTooltips() {
      const {
        $
      } = window;
      $(this.$refs.root).tooltip({
        track: true,
        content: function transformTooltipContent() {
          const title = $(this).attr('title');
          return window.vueSanitize(title.replace(/\n/g, '<br />'));
        },
        show: {
          delay: 200,
          duration: 200
        },
        hide: false
      });
    },

    onComparisonsChanged() {
      this.comparisonTooltips = null;

      if (!this.comparisonsService.isComparing()) {
        return;
      }

      const periodComparisons = this.comparisonsService.getPeriodComparisons();
      const segmentComparisons = this.comparisonsService.getSegmentComparisons();
      AjaxHelper_AjaxHelper.fetch({
        method: 'API.getProcessedReport',
        apiModule: 'VisitsSummary',
        apiAction: 'get',
        compare: '1',
        compareSegments: MatomoUrl_MatomoUrl.getSearchParam('compareSegments'),
        comparePeriods: MatomoUrl_MatomoUrl.getSearchParam('comparePeriods'),
        compareDates: MatomoUrl_MatomoUrl.getSearchParam('compareDates'),
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

      const firstRowIndex = this.comparisonsService.getComparisonSeriesIndex(periodComp.index, 0);
      const firstRow = visitsSummary.reportData.comparisons[firstRowIndex];
      const comparisonRowIndex = this.comparisonsService.getComparisonSeriesIndex(periodComp.index, segmentComp.index);
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
    }

  },

  mounted() {
    Matomo_Matomo.on('piwikComparisonsChanged', () => this.onComparisonsChanged());
    this.onComparisonsChanged();
    setTimeout(() => this.setUpTooltips());
  },

  unmounted() {
    try {
      window.$(this.refs.root).tooltip('destroy');
    } catch (e) {
      // ignore
      console.log('does this always happen?'); // TODO: Remove
    }
  }

}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.vue



Comparisonsvue_type_script_lang_ts.render = Comparisonsvue_type_template_id_4f8421ca_render

/* harmony default export */ var Comparisons = (Comparisonsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Comparisons/Comparisons.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




function ComparisonFactory() {
  return Comparisons_store;
}

ComparisonFactory.$inject = [];
angular.module('piwikApp.service').factory('piwikComparisonsService', ComparisonFactory);
/* harmony default export */ var Comparisons_adapter = (createAngularJsAdapter({
  component: Comparisons,
  directiveName: 'piwikComparisons',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=template&id=58d3b5f8

const Menudropdownvue_type_template_id_58d3b5f8_hoisted_1 = {
  ref: "root",
  class: "menuDropdown"
};
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_2 = ["title"];
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_3 = ["innerHTML"];

const Menudropdownvue_type_template_id_58d3b5f8_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-arrow-bottom"
}, null, -1);

const Menudropdownvue_type_template_id_58d3b5f8_hoisted_5 = {
  class: "items"
};
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_6 = {
  key: 0,
  class: "search"
};
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_7 = ["placeholder"];
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_8 = ["title"];
const Menudropdownvue_type_template_id_58d3b5f8_hoisted_9 = ["title"];
function Menudropdownvue_type_template_id_58d3b5f8_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  const _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Menudropdownvue_type_template_id_58d3b5f8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "title",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showItems = !_ctx.showItems),
    title: _ctx.tooltip
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(this.actualMenuTitle)
  }, null, 8, Menudropdownvue_type_template_id_58d3b5f8_hoisted_3), Menudropdownvue_type_template_id_58d3b5f8_hoisted_4], 8, Menudropdownvue_type_template_id_58d3b5f8_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Menudropdownvue_type_template_id_58d3b5f8_hoisted_5, [_ctx.showSearch && _ctx.showItems ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Menudropdownvue_type_template_id_58d3b5f8_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.searchTerm = $event),
    onChange: _cache[2] || (_cache[2] = $event => _ctx.searchItems(_ctx.searchTerm)),
    placeholder: _ctx.translate('General_Search')
  }, null, 40, Menudropdownvue_type_template_id_58d3b5f8_hoisted_7), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, _ctx.showItems]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    class: "search_ico",
    src: "plugins/Morpheus/images/search_ico.png",
    title: _ctx.translate('General_Search')
  }, null, 8, Menudropdownvue_type_template_id_58d3b5f8_hoisted_8), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[3] || (_cache[3] = $event => {
      _ctx.searchTerm = '';

      _ctx.searchItems('');
    }),
    class: "reset",
    src: "plugins/CoreHome/images/reset_search.png",
    title: _ctx.translate('General_Clear')
  }, null, 8, Menudropdownvue_type_template_id_58d3b5f8_hoisted_9), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[4] || (_cache[4] = $event => _ctx.selectItem($event))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showItems]])], 512)), [[_directive_focus_anywhere_but_here, _ctx.showItems = false]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=template&id=58d3b5f8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Menudropdown/Menudropdown.vue?vue&type=script&lang=ts



const {
  $: Menudropdownvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var Menudropdownvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    menuTitle: String,
    tooltip: String,
    showSearch: String,
    menuTitleChangeOnClick: String
  },
  directives: {
    FocusAnywhereButHere: FocusAnywhereButHere(),
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
    selectItem(event) {
      const targetClasses = event.target.classList;

      if (targetClasses.contains('item') || targetClasses.contains('disabled') || targetClasses.contains('separator')) {
        return;
      }

      if (this.menuTitleChangeOnClick !== false) {
        this.actualMenuTitle = event.target.textContent.replace(/[\u0000-\u2666]/g, c => `&#${c.charCodeAt(0)};`); // eslint-disable-line
      }

      this.showItems = false;
      Menudropdownvue_type_script_lang_ts_$(this.$slots.default()).find('.item').removeClass('active');
      targetClasses.add('active');
      this.$emit('afterSelect');
    },

    searchItems(unprocessedSearchTerm) {
      const searchTerm = unprocessedSearchTerm.toLowerCase();
      Menudropdownvue_type_script_lang_ts_$(this.$refs.root).find('.item').each((index, node) => {
        const $node = Menudropdownvue_type_script_lang_ts_$(node);

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



Menudropdownvue_type_script_lang_ts.render = Menudropdownvue_type_template_id_58d3b5f8_render

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
    'after-select': scope => {
      setTimeout(() => {
        scope.$apply();
      }, 0);
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=735ab8c1

const DatePickervue_type_template_id_735ab8c1_hoisted_1 = {
  ref: "root"
};
function DatePickervue_type_template_id_735ab8c1_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DatePickervue_type_template_id_735ab8c1_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=template&id=735ab8c1

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DatePicker/DatePicker.vue?vue&type=script&lang=ts


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
      const $rowCells = $row.children('td'); // if in the first row, the date cell is before the current month

      if ($row.is(':first-child')) {
        const $firstDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').first();
        date = getCellDate($firstDateInMonth, month, year);
        date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
        return date;
      } // the date cell is after the current month


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
      const monthYear = getMonthYearDisplayed(); // highlight the rest of the cells by first getting the date for the first cell
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
      let date = props.viewDate;

      if (!date) {
        return false;
      }

      if (!(date instanceof Date)) {
        try {
          date = DatePickervue_type_script_lang_ts_$.datepicker.parseDate('yy-mm-dd', date);
        } catch (e) {
          return false;
        }
      }

      const element = DatePickervue_type_script_lang_ts_$(root.value); // only change the datepicker date if the date is outside of the current month/year.
      // this avoids a re-render in other cases.

      const monthYear = getMonthYearDisplayed();

      if (monthYear[0] !== date.getMonth() || monthYear[1] !== date.getFullYear()) {
        element.datepicker('setDate', date);
        return true;
      }

      return false;
    } // remove the ui-state-active class & click handlers for every cell. we bypass
    // the datepicker's date selection logic for smoother browser rendering.


    function onJqueryUiRenderedPicker() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      element.find('td[data-event]').off('click');
      element.find('.ui-state-active').removeClass('ui-state-active');
      element.find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day'); // add href to left/right nav in calendar so they can be accessed via keyboard

      element.find('.ui-datepicker-prev,.ui-datepicker-next').attr('href', '');
    }

    function stepMonthsChanged() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const stepMonths = props.stepMonths || DEFAULT_STEP_MONTHS;

      if (element.datepicker('option', 'stepMonths') === stepMonths) {
        return false;
      } // setting stepMonths will change the month in view back to the selected date. to avoid
      // we set the selected date to the month in view.


      const currentMonth = DatePickervue_type_script_lang_ts_$('.ui-datepicker-month', element).val();
      const currentYear = DatePickervue_type_script_lang_ts_$('.ui-datepicker-year', element).val();
      element.datepicker('option', 'stepMonths', stepMonths).datepicker('setDate', new Date(currentYear, currentMonth));
      onJqueryUiRenderedPicker();
      return true;
    }

    function enableDisableMonthDropdown() {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      element.find('.ui-datepicker-month').attr('disabled', props.disableMonthDropdown);
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
    } // on a prop change (NOTE: we can't watch just `props`, since then newProps and oldProps will
    // have the same values (since it is a proxy object). Using a copy doesn't quite work, the
    // object it returns will always be different, BUT, since we check what changes it works
    // for our purposes. The only downside is that it runs on every tick basically, but since
    // that is within the context of the date picker component, it's bearable.


    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => ({ ...props
    }), (newProps, oldProps) => {
      let redraw = false;
      ['selectedDateStart', 'selectedDateEnd', 'highlightedDateStart', 'highlightedDateEnd'].forEach(propName => {
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
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      const element = DatePickervue_type_script_lang_ts_$(root.value);
      const customOptions = props.options || {};
      const datePickerOptions = { ...Matomo_Matomo.getBaseDatePickerOptions(),
        ...customOptions,
        onChangeMonthYear: () => {
          // datepicker renders the HTML after this hook is called, so we use setTimeout
          // to run some code after the render.
          setTimeout(() => {
            onJqueryUiRenderedPicker();
          });
        }
      };
      element.datepicker(datePickerOptions);
      element.on('mouseover', 'tbody td a', event => {
        // this event is triggered when a user clicks a date as well. in that case,
        // the originalEvent is null. we don't need to redraw again for that, so
        // we ignore events like that.
        if (event.originalEvent) {
          setDatePickerCellColors();
        }
      }); // on hover cell, execute scope.cellHover()

      element.on('mouseenter', 'tbody td', function onMouseEnter() {
        const monthYear = getMonthYearDisplayed();
        const $dateCell = DatePickervue_type_script_lang_ts_$(this);
        const dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
        context.emit('cellHover', {
          date: dateValue,
          $cell: $dateCell
        });
      }); // overrides jquery UI handler that unhighlights a cell when the mouse leaves it

      element.on('mouseout', 'tbody td a', () => {
        setDatePickerCellColors();
      }); // call scope.cellHoverLeave() when mouse leaves table body (can't do event on tbody, for
      // some reason that fails, so we do two events, one on the table & one on thead)

      element.on('mouseleave', 'table', () => context.emit('cellHoverLeave')).on('mouseenter', 'thead', () => context.emit('cellHoverLeave')); // make sure whitespace is clickable when the period makes it appropriate

      element.on('click', 'tbody td.ui-datepicker-other-month', () => handleOtherMonthClick()); // NOTE: using a selector w/ .on() doesn't seem to work for some reason...

      element.on('click', e => {
        e.preventDefault();
        const $target = DatePickervue_type_script_lang_ts_$(e.target).closest('a');

        if (!$target.is('.ui-datepicker-next') && !$target.is('.ui-datepicker-prev')) {
          return;
        }

        onCalendarViewChange();
      }); // when a cell is clicked, invoke the onDateSelected function. this, in conjunction
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



DatePickervue_type_script_lang_ts.render = DatePickervue_type_template_id_735ab8c1_render

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
    'cell-hover': (event, scope, element, attrs, $timeout) => {
      $timeout(); // trigger new digest
    },
    'cell-hover-leave': (event, scope, element, attrs, $timeout) => {
      $timeout(); // trigger new digest
    },
    'date-select': (event, scope, element, attrs, $timeout) => {
      $timeout(); // trigger new digest
    }
  },
  $inject: ['$timeout']
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=5d5439c6

const DateRangePickervue_type_template_id_5d5439c6_hoisted_1 = {
  id: "calendarRangeFrom"
};
const DateRangePickervue_type_template_id_5d5439c6_hoisted_2 = {
  id: "calendarRangeTo"
};
function DateRangePickervue_type_template_id_5d5439c6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DatePicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DatePicker");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_5d5439c6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeFrom')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarFrom",
    name: "inputCalendarFrom",
    class: "browser-default",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.startDateText = $event),
    onChange: _cache[1] || (_cache[1] = $event => _ctx.onRangeInputChanged('from', $event)),
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
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DateRangePickervue_type_template_id_5d5439c6_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h6", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_DateRangeTo')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "inputCalendarTo",
    name: "inputCalendarTo",
    class: "browser-default",
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.endDateText = $event),
    onChange: _cache[7] || (_cache[7] = $event => _ctx.onRangeInputChanged('to', $event)),
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
  }, null, 8, ["view-date", "selected-date-start", "selected-date-end", "highlighted-date-start", "highlighted-date-end"])])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=template&id=5d5439c6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts



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
      startDate = parseDate(this.startDate);
    } catch (e) {// ignore
    }

    let endDate = null;

    try {
      endDate = parseDate(this.endDate);
    } catch (e) {// ignore
    }

    return {
      fromPickerSelectedDates: [startDate, startDate],
      toPickerSelectedDates: [endDate, endDate],
      fromPickerHighlightedDates: [null, null],
      toPickerHighlightedDates: [null, null],
      startDateText: '',
      endDateText: ''
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
      if (source === 'from') {
        this.setStartRangeDateFromStr(event.target.value);
      } else {
        this.setEndRangeDateFromStr(event.target.value);
      }
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
      let startDateParsed;

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

    setEndRangeDateFromStr(dateStr) {
      let endDateParsed;

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

    rangeChanged() {
      this.$emit('rangeChange', {
        start: format(this.fromPickerSelectedDates[0]),
        end: format(this.toPickerSelectedDates[0])
      });
    }

  }
}));
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/DateRangePicker/DateRangePicker.vue



DateRangePickervue_type_script_lang_ts.render = DateRangePickervue_type_template_id_5d5439c6_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=0fe3c4e7

function PeriodDatePickervue_type_template_id_0fe3c4e7_render(_ctx, _cache, $props, $setup, $data, $options) {
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=template&id=0fe3c4e7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/PeriodDatePicker/PeriodDatePicker.vue?vue&type=script&lang=ts




const piwikMinDate = new Date(Matomo_Matomo.minDateYear, Matomo_Matomo.minDateMonth - 1, Matomo_Matomo.minDateDay);
const piwikMaxDate = new Date(Matomo_Matomo.maxDateYear, Matomo_Matomo.maxDateMonth - 1, Matomo_Matomo.maxDateDay);
/* harmony default export */ var PeriodDatePickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    period: String,
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
      const dates = Periods_Periods.get(props.period).parse(date).getDateRange(); // make sure highlighted date range is within min/max date range

      dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;
      return dates;
    }

    function onHoverNormalCell(cellDate, $cell) {
      const isOutOfMinMaxDateRange = cellDate < piwikMinDate || cellDate > piwikMaxDate; // don't highlight anything if the period is month or day, and we're hovering over calendar
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
        return;
      }

      selectedDates.value = getBoundedDateRange(props.date);
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



PeriodDatePickervue_type_script_lang_ts.render = PeriodDatePickervue_type_template_id_0fe3c4e7_render

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

const ActivityIndicatorvue_type_template_id_6af4d064_hoisted_1 = {
  class: "loadingPiwik"
};

const ActivityIndicatorvue_type_template_id_6af4d064_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
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
      default: () => translate('General_LoadingData')
    }
  },
  $inject: [],
  directiveName: 'piwikActivityIndicator'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c3863ae2

function Alertvue_type_template_id_c3863ae2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert", {
      [`alert-${_ctx.severity}`]: true
    }])
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
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/index.ts
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