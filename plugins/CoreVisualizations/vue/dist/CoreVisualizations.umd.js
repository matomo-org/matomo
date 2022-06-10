(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["CoreVisualizations"] = factory(require("CoreHome"), require("vue"));
	else
		root["CoreVisualizations"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
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
/******/ 	__webpack_require__.p = "plugins/CoreVisualizations/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

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
__webpack_require__.d(__webpack_exports__, "SeriesPicker", function() { return /* reexport */ SeriesPicker; });
__webpack_require__.d(__webpack_exports__, "SingleMetricView", function() { return /* reexport */ SingleMetricView; });

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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=bd202f38

var _hoisted_1 = {
  key: 0,
  class: "jqplot-seriespicker-popover"
};
var _hoisted_2 = {
  class: "headline"
};
var _hoisted_3 = ["onClick"];
var _hoisted_4 = ["type", "checked"];
var _hoisted_5 = {
  key: 0,
  class: "headline recordsToPlot"
};
var _hoisted_6 = ["onClick"];
var _hoisted_7 = ["type", "checked"];
function SeriesPickervue_type_template_id_bd202f38_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["jqplot-seriespicker", {
      open: _ctx.isPopupVisible
    }]),
    onMouseenter: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.isPopupVisible = true;
    }),
    onMouseleave: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onLeavePopup();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function () {}, ["prevent", "stop"]))
  }, " + "), _ctx.isPopupVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.multiselect ? 'General_MetricsToPlot' : 'General_MetricToPlot')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableColumns, function (columnConfig) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickColumn",
      onClick: function onClick($event) {
        return _ctx.optionSelected(columnConfig.column, _ctx.columnStates);
      },
      key: columnConfig.column
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.columnStates[columnConfig.column]
    }, null, 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(columnConfig.translation), 1)])], 8, _hoisted_3);
  }), 128)), _ctx.selectableRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RecordsToPlot')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableRows, function (rowConfig) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickRow",
      onClick: function onClick($event) {
        return _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates);
      },
      key: rowConfig.matcher
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.rowStates[rowConfig.matcher]
    }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rowConfig.label), 1)])], 8, _hoisted_6);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 34);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=bd202f38

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts



function getInitialOptionStates(allOptions, selectedOptions) {
  var states = {};
  allOptions.forEach(function (columnConfig) {
    var name = columnConfig.column || columnConfig.matcher;
    states[name] = false;
  });
  selectedOptions.forEach(function (column) {
    states[column] = true;
  });
  return states;
}

function arrayEqual(lhs, rhs) {
  if (lhs.length !== rhs.length) {
    return false;
  }

  return lhs.filter(function (element) {
    return rhs.indexOf(element) === -1;
  }).length === 0;
}

function unselectOptions(optionStates) {
  Object.keys(optionStates).forEach(function (optionName) {
    optionStates[optionName] = false;
  });
}

function getSelected(optionStates) {
  return Object.keys(optionStates).filter(function (optionName) {
    return !!optionStates[optionName];
  });
}

/* harmony default export */ var SeriesPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    multiselect: Boolean,
    selectableColumns: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectableRows: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectedColumns: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectedRows: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    return {
      isPopupVisible: false,
      columnStates: getInitialOptionStates(this.selectableColumns, this.selectedColumns),
      rowStates: getInitialOptionStates(this.selectableRows, this.selectedRows)
    };
  },
  emits: ['select'],
  created: function created() {
    this.optionSelected = Object(external_CoreHome_["debounce"])(this.optionSelected, 0);
  },
  methods: {
    optionSelected: function optionSelected(optionValue, optionStates) {
      if (!this.multiselect) {
        unselectOptions(this.columnStates);
        unselectOptions(this.rowStates);
      }

      optionStates[optionValue] = !optionStates[optionValue];
      this.triggerOnSelectAndClose();
    },
    onLeavePopup: function onLeavePopup() {
      this.isPopupVisible = false;

      if (this.optionsChanged()) {
        this.triggerOnSelectAndClose();
      }
    },
    triggerOnSelectAndClose: function triggerOnSelectAndClose() {
      this.isPopupVisible = false;
      this.$emit('select', {
        columns: getSelected(this.columnStates),
        rows: getSelected(this.rowStates)
      });
    },
    optionsChanged: function optionsChanged() {
      return !arrayEqual(getSelected(this.columnStates), this.selectedColumns) || !arrayEqual(getSelected(this.rowStates), this.selectedRows);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue



SeriesPickervue_type_script_lang_ts.render = SeriesPickervue_type_template_id_bd202f38_render

/* harmony default export */ var SeriesPicker = (SeriesPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var SeriesPicker_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SeriesPicker,
  scope: {
    multiselect: {
      angularJsBind: '<'
    },
    selectableColumns: {
      angularJsBind: '<'
    },
    selectableRows: {
      angularJsBind: '<'
    },
    selectedColumns: {
      angularJsBind: '<'
    },
    selectedRows: {
      angularJsBind: '<'
    },
    onSelect: {
      angularJsBind: '&',
      vue: 'select'
    }
  },
  directiveName: 'piwikSeriesPicker',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=2e2e889f

var SingleMetricViewvue_type_template_id_2e2e889f_hoisted_1 = {
  class: "metric-sparkline"
};
var SingleMetricViewvue_type_template_id_2e2e889f_hoisted_2 = {
  class: "metric-value"
};
var SingleMetricViewvue_type_template_id_2e2e889f_hoisted_3 = ["title"];
var SingleMetricViewvue_type_template_id_2e2e889f_hoisted_4 = ["title"];
function SingleMetricViewvue_type_template_id_2e2e889f_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["singleMetricView", {
      'loading': _ctx.isLoading
    }]),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_2e2e889f_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    params: _ctx.sparklineParams
  }, null, 8, ["params"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_2e2e889f_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    title: _ctx.metricDocumentation
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricValue), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx.metricTranslation || '').toLowerCase()), 1)], 8, SingleMetricViewvue_type_template_id_2e2e889f_hoisted_3), _ctx.pastValue !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "metricEvolution",
    title: _ctx.translate('General_EvolutionSummaryGeneric', _ctx.metricValue, _ctx.currentPeriod, _ctx.pastValue, _ctx.pastPeriod, _ctx.metricChangePercent)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'positive-evolution': _ctx.metricValueUnformatted > _ctx.pastValueUnformatted,
      'negative-evolution': _ctx.metricValueUnformatted < _ctx.pastValueUnformatted
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricChangePercent), 3)], 8, SingleMetricViewvue_type_template_id_2e2e889f_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=2e2e889f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=script&lang=ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





function getPastPeriodStr() {
  var _Range$getLastNRange = external_CoreHome_["Range"].getLastNRange(external_CoreHome_["Matomo"].period, 2, external_CoreHome_["Matomo"].currentDateString),
      startDate = _Range$getLastNRange.startDate;

  var dateRange = external_CoreHome_["Periods"].get(external_CoreHome_["Matomo"].period).parse(startDate).getDateRange();
  return "".concat(Object(external_CoreHome_["format"])(dateRange[0]), ",").concat(Object(external_CoreHome_["format"])(dateRange[1]));
}

var _window = window,
    $ = _window.$;
/* harmony default export */ var SingleMetricViewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    metric: {
      type: String,
      required: true
    },
    idGoal: [String, Number],
    metricTranslations: {
      type: Object,
      required: true
    },
    metricDocumentations: Object,
    goals: {
      type: Object,
      required: true
    },
    goalMetrics: Array
  },
  components: {
    Sparkline: external_CoreHome_["Sparkline"]
  },
  setup: function setup(props) {
    var root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    var isLoading = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    var responses = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    var actualMetric = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.metric);
    var actualIdGoal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.idGoal);
    var selectedColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return [actualIdGoal.value ? "goal".concat(actualIdGoal.value, "_").concat(actualMetric.value) : actualMetric.value];
    });
    var metricValueUnformatted = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _responses$value;

      if (!((_responses$value = responses.value) !== null && _responses$value !== void 0 && _responses$value[1])) {
        return null;
      }

      return responses.value[1][actualMetric.value];
    });
    var pastValueUnformatted = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _responses$value2;

      if (!((_responses$value2 = responses.value) !== null && _responses$value2 !== void 0 && _responses$value2[2])) {
        return null;
      }

      return responses.value[2][actualMetric.value] || 0;
    });
    var metricChangePercent = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      if (!metricValueUnformatted.value) {
        return null;
      }

      var currentValue = typeof metricValueUnformatted.value === 'string' ? parseInt(metricValueUnformatted.value, 10) : metricValueUnformatted.value;
      var pastValue = typeof pastValueUnformatted.value === 'string' ? parseInt(pastValueUnformatted.value, 10) : pastValueUnformatted.value;
      var evolution = external_CoreHome_["Matomo"].helper.calculateEvolution(currentValue, pastValue);
      return "".concat((evolution * 100).toFixed(2), " %");
    });
    var pastValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _responses$value3;

      if (!((_responses$value3 = responses.value) !== null && _responses$value3 !== void 0 && _responses$value3[3])) {
        return null;
      }

      var pastDataFormatted = responses.value[3];
      return pastDataFormatted[actualMetric.value] || 0;
    });
    var metricValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _responses$value4;

      if (!((_responses$value4 = responses.value) !== null && _responses$value4 !== void 0 && _responses$value4[0])) {
        return null;
      }

      var currentData = responses.value[0];
      return currentData[actualMetric.value] || 0;
    });
    var metricTranslation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _props$metricTranslat;

      if (!((_props$metricTranslat = props.metricTranslations) !== null && _props$metricTranslat !== void 0 && _props$metricTranslat[actualMetric.value])) {
        return '';
      }

      return props.metricTranslations[actualMetric.value];
    });
    var metricDocumentation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var _props$metricDocument;

      if (!((_props$metricDocument = props.metricDocumentations) !== null && _props$metricDocument !== void 0 && _props$metricDocument[actualMetric.value])) {
        return '';
      }

      return props.metricDocumentations[actualMetric.value];
    });
    var currentPeriod = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      if (external_CoreHome_["Matomo"].startDateString === external_CoreHome_["Matomo"].endDateString) {
        return external_CoreHome_["Matomo"].endDateString;
      }

      return "".concat(external_CoreHome_["Matomo"].startDateString, ", ").concat(external_CoreHome_["Matomo"].endDateString);
    });

    function isIdGoalSet() {
      return actualIdGoal.value || actualIdGoal.value === 0;
    }

    var sparklineParams = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var params = {
        module: 'API',
        action: 'get',
        columns: actualMetric.value
      };

      if (isIdGoalSet()) {
        params.idGoal = actualIdGoal.value;
        params.module = 'Goals';
      }

      return params;
    });
    var pastPeriod = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      if (external_CoreHome_["Matomo"].period === 'range') {
        return undefined;
      }

      return getPastPeriodStr();
    });
    var selectableColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var result = [];
      Object.keys(props.metricTranslations).forEach(function (column) {
        result.push({
          column: column,
          translation: props.metricTranslations[column]
        });
      });
      Object.values(props.goals || {}).forEach(function (goal) {
        props.goalMetrics.forEach(function (column) {
          result.push({
            column: "goal".concat(goal.idgoal, "_").concat(column),
            translation: "".concat(goal.name, " - ").concat(props.metricTranslations[column])
          });
        });
      });
      return result;
    });

    function setWidgetTitle() {
      var title = metricTranslation.value;

      if (isIdGoalSet()) {
        var _props$goals$actualId;

        var goalName = ((_props$goals$actualId = props.goals[actualIdGoal.value]) === null || _props$goals$actualId === void 0 ? void 0 : _props$goals$actualId.name) || Object(external_CoreHome_["translate"])('General_Unknown');
        title = "".concat(goalName, " - ").concat(title);
      }

      $(root.value).closest('div.widget').find('.widgetTop > .widgetName > span').text(title);
    }

    function getLastPeriodDate() {
      var range = external_CoreHome_["Range"].getLastNRange(external_CoreHome_["Matomo"].period, 2, external_CoreHome_["Matomo"].currentDateString);
      return Object(external_CoreHome_["format"])(range.startDate);
    }

    function fetchData() {
      isLoading.value = true;
      var promises = [];
      var apiModule = 'API';
      var apiAction = 'get';
      var extraParams = {};

      if (isIdGoalSet()) {
        // the conversion rate added by the AddColumnsProcessedMetrics filter conflicts w/
        // the goals one, so don't run it
        extraParams.idGoal = actualIdGoal.value;
        extraParams.filter_add_columns_when_show_all_columns = 0;
        apiModule = 'Goals';
        apiAction = 'get';
      }

      var method = "".concat(apiModule, ".").concat(apiAction); // first request for formatted data

      promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
        method: method,
        format_metrics: 'all'
      }, extraParams)));

      if (external_CoreHome_["Matomo"].period !== 'range') {
        // second request for unformatted data so we can calculate evolution
        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
          method: method,
          format_metrics: '0'
        }, extraParams))); // third request for past data (unformatted)

        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
          method: method,
          date: getLastPeriodDate(),
          format_metrics: '0'
        }, extraParams))); // fourth request for past data (formatted for tooltip display)

        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
          method: method,
          date: getLastPeriodDate(),
          format_metrics: 'all'
        }, extraParams)));
      }

      return Promise.all(promises).then(function (r) {
        responses.value = r;
        isLoading.value = false;
      });
    }

    function onMetricChanged(newMetric) {
      actualMetric.value = newMetric;
      fetchData().then(setWidgetTitle); // notify widget of parameter change so it is replaced

      $(root.value).closest('[widgetId]').trigger('setParameters', {
        column: actualMetric.value,
        idGoal: actualIdGoal.value
      });
    }

    function setMetric(newColumn) {
      var idGoal = undefined;
      var actualColumn = newColumn;
      var m = newColumn.match(/^goal([0-9]+)_(.*)/);

      if (m) {
        idGoal = +m[1];

        var _m = _slicedToArray(m, 3);

        actualColumn = _m[2];
      }

      if (actualMetric.value !== actualColumn || idGoal !== actualIdGoal.value) {
        actualMetric.value = actualColumn;
        actualIdGoal.value = idGoal;
        onMetricChanged(actualColumn);
      }
    }

    function createSeriesPicker() {
      var element = $(root.value);
      var $widgetName = element.closest('div.widget').find('.widgetTop > .widgetName');
      var $seriesPickerElem = $('<div class="single-metric-view-picker"><div></div></div>');
      var app = Object(external_CoreHome_["createVueApp"])({
        render: function render() {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(SeriesPicker, {
            multiselect: false,
            selectableColumns: selectableColumns.value,
            selectableRows: [],
            selectedColumns: selectedColumns.value,
            selectedRows: [],
            onSelect: function onSelect(_ref) {
              var columns = _ref.columns;
              setMetric(columns[0]);
            }
          });
        }
      });
      $widgetName.append($seriesPickerElem);
      app.mount($seriesPickerElem.children()[0]);
      return app;
    }

    var seriesPickerApp;
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      seriesPickerApp = createSeriesPicker();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(function () {
      $(root.value).closest('.widgetContent').off('widget:destroy').off('widget:reload');
      $(root.value).closest('div.widget').find('.single-metric-view-picker').remove();
      seriesPickerApp.unmount();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.metric;
    }, function () {
      onMetricChanged(props.metric);
    });
    onMetricChanged(props.metric);
    return {
      root: root,
      metricValue: metricValue,
      isLoading: isLoading,
      selectedColumns: selectedColumns,
      responses: responses,
      metricValueUnformatted: metricValueUnformatted,
      pastValueUnformatted: pastValueUnformatted,
      metricChangePercent: metricChangePercent,
      pastValue: pastValue,
      metricTranslation: metricTranslation,
      metricDocumentation: metricDocumentation,
      sparklineParams: sparklineParams,
      pastPeriod: pastPeriod,
      selectableColumns: selectableColumns,
      currentPeriod: currentPeriod
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue



SingleMetricViewvue_type_script_lang_ts.render = SingleMetricViewvue_type_template_id_2e2e889f_render

/* harmony default export */ var SingleMetricView = (SingleMetricViewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var SingleMetricView_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SingleMetricView,
  scope: {
    metric: {
      angularJsBind: '<'
    },
    idGoal: {
      angularJsBind: '<'
    },
    metricTranslations: {
      angularJsBind: '<'
    },
    metricDocumentations: {
      angularJsBind: '<'
    },
    goals: {
      angularJsBind: '<'
    },
    goalMetrics: {
      angularJsBind: '<'
    }
  },
  directiveName: 'piwikSingleMetricView',
  restrict: 'E',
  postCreate: function postCreate(vm, scope, element) {
    element.closest('.widgetContent').on('widget:destroy', function () {
      scope.$parent.$destroy();
    }).on('widget:reload', function () {
      scope.$parent.$destroy();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/index.ts
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
//# sourceMappingURL=CoreVisualizations.umd.js.map