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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

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
__webpack_require__.d(__webpack_exports__, "activityIndicatorAdapter", function() { return /* reexport */ activityIndicatorAdapter; });
__webpack_require__.d(__webpack_exports__, "ActivityIndicator", function() { return /* reexport */ ActivityIndicator; });
__webpack_require__.d(__webpack_exports__, "translate", function() { return /* reexport */ translate; });
__webpack_require__.d(__webpack_exports__, "alertAdapter", function() { return /* reexport */ alertAdapter; });
__webpack_require__.d(__webpack_exports__, "Periods", function() { return /* reexport */ Periods_Periods; });

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

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/translate.ts
function translate(translationStringId, values = []) {
  return window._pk_translate(translationStringId, values); // eslint-disable-line
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Piwik period management service for the frontend.
 *
 * Usage:
 *
 *     var DayPeriod = piwikPeriods.get('day');
 *     var day = new DayPeriod(new Date());
 *
 * or
 *
 *     var day = piwikPeriods.parse('day', '2013-04-05');
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
 * Then call piwik.addCustomPeriod w/ your period class:
 *
 *     piwik.addCustomPeriod('mycustomperiod', MyCustomPeriod);
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
    return [].concat(this.periodOrder);
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
function format(date) {
  return $.datepicker.formatDate('yy-mm-dd', date);
}
function getToday() {
  const date = new Date(Date.now()); // undo browser timezone

  date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000); // apply piwik site timezone (if it exists)

  date.setHours(date.getHours() + (piwik.timezoneOffset || 0) / 3600); // get rid of hours/minutes/seconds/etc.

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
  if (!dateRange['isArray'] && dateRange.length !== 2) {
    // eslint-disable-line
    return false;
  }

  if (getToday() >= dateRange[0] && getToday() <= dateRange[1]) {
    return true;
  }

  return false;
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Day.ts
function Day_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }




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
    return translate(`Intl_Month_Long_StandAlone_${this.dateInPeriod.getMonth() + 1} ${this.dateInPeriod.getFullYear()}`);
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




class Range_RangePeriod {
  constructor(startDate, endDate, childPeriodType) {
    Range_defineProperty(this, "startDate", void 0);

    Range_defineProperty(this, "endDate", void 0);

    Range_defineProperty(this, "childPeriodType", void 0);

    this.startDate = startDate;
    this.endDate = endDate;
    this.childPeriodType = childPeriodType;
  }

  static parse(strDate, childPeriodType = 'day') {
    /**
     * Returns a range representing the last N childPeriodType periods, including the current one.
     */
    function getLastNRange(strAmount, strEndDate) {
      const nAmount = Math.max(parseInt(strAmount, 10) - 1, 0);

      if (Number.isNaN(nAmount)) {
        throw new Error(`Invalid range date: ${strDate}`);
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

      if (startDate - firstWebsiteDate < 0) {
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

    if (/^previous/.test(strDate)) {
      const endDate = getLastNRange('2').startDate;
      return getLastNRange(strDate.substring(8), endDate);
    }

    if (/^last/.test(strDate)) {
      return getLastNRange(strDate.substring(4));
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



piwik.addCustomPeriod = Periods_Periods.addCustomPeriod.bind(Periods_Periods);

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
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=c109d738

const _hoisted_1 = {
  class: "loadingPiwik"
};

const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  alt: ""
}, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.loading]]);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=c109d738

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



ActivityIndicatorvue_type_script_lang_ts.render = render

/* harmony default export */ var ActivityIndicator = (ActivityIndicatorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts



function activityIndicatorAdapter() {
  return {
    restrict: 'A',
    scope: {
      loading: '<',
      loadingMessage: '<'
    },
    template: '',
    link: function activityIndicatorAdapterLink(scope, element) {
      const app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
        template: '<activity-indicator :loading="loading" :loadingMessage="loadingMessage"/>',

        data() {
          return {
            loading: scope.loading,
            loadingMessage: scope.loadingMessage
          };
        }

      });
      app.component('activity-indicator', ActivityIndicator);
      const vm = app.mount(element[0]);
      scope.$watch('loading', newValue => {
        vm.loading = newValue;
      });
      scope.$watch('loadingMessage', newValue => {
        vm.loadingMessage = newValue || translate('General_LoadingData');
      });
    }
  };
}
activityIndicatorAdapter.$inject = [];
angular.module('piwikApp').directive('piwikActivityIndicator', activityIndicatorAdapter);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c4d43d96

function Alertvue_type_template_id_c4d43d96_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert", {
      [`alert-${_ctx.severity}`]: true
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 2);
}
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=c4d43d96

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



Alertvue_type_script_lang_ts.render = Alertvue_type_template_id_c4d43d96_render

/* harmony default export */ var Alert = (Alertvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts


function alertAdapter() {
  return {
    restrict: 'A',
    transclude: true,
    scope: {
      severity: '@piwikAlert'
    },
    template: '<div ng-transclude/>',
    compile: function alertAdapterCompile() {
      return {
        post: function alertAdapterPostLink(scope, element) {
          const clone = element.find('[ng-transclude]');
          const app = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])({
            template: '<alert :severity="severity"><div ref="transcludeTarget"/></alert>',

            data() {
              return {
                severity: scope.severity
              };
            },

            setup() {
              const transcludeTarget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
              return {
                transcludeTarget
              };
            }

          });
          app.component('alert', Alert);
          const vm = app.mount(element[0]);
          scope.$watch('severity', newValue => {
            vm.severity = newValue;
          });
          $(vm.transcludeTarget).append(clone);
        }
      };
    }
  };
}
alertAdapter.$inject = [];
angular.module('piwikApp').directive('piwikAlert', alertAdapter);
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/index.ts











// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=CoreHome.umd.js.map