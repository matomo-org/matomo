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

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=7c1adaf7

const _hoisted_1 = {
  key: 0,
  class: "jqplot-seriespicker-popover"
};
const _hoisted_2 = {
  class: "headline"
};
const _hoisted_3 = ["onClick"];
const _hoisted_4 = ["type", "checked"];
const _hoisted_5 = {
  key: 0,
  class: "headline recordsToPlot"
};
const _hoisted_6 = ["onClick"];
const _hoisted_7 = ["type", "checked"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["jqplot-seriespicker", {
      open: _ctx.isPopupVisible
    }]),
    onMouseenter: _cache[1] || (_cache[1] = $event => _ctx.isPopupVisible = true),
    onMouseleave: _cache[2] || (_cache[2] = $event => _ctx.onLeavePopup())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent", "stop"]))
  }, " + "), _ctx.isPopupVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.multiselect ? 'General_MetricsToPlot' : 'General_MetricToPlot')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableColumns, columnConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickColumn",
      onClick: $event => _ctx.optionSelected(columnConfig.column, _ctx.columnStates),
      key: columnConfig.column
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.columnStates[columnConfig.column]
    }, null, 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(columnConfig.translation), 1)])], 8, _hoisted_3);
  }), 128)), _ctx.selectableRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RecordsToPlot')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableRows, rowConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickRow",
      onClick: $event => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates),
      key: rowConfig.matcher
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.rowStates[rowConfig.matcher]
    }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rowConfig.label), 1)])], 8, _hoisted_6);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 34);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=7c1adaf7

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts


function getInitialOptionStates(allOptions, selectedOptions) {
  const states = {};
  allOptions.forEach(columnConfig => {
    const name = columnConfig.column || columnConfig.matcher;
    states[name] = false;
  });
  selectedOptions.forEach(column => {
    states[column] = true;
  });
  return states;
}
function arrayEqual(lhs, rhs) {
  if (lhs.length !== rhs.length) {
    return false;
  }
  return lhs.filter(element => rhs.indexOf(element) === -1).length === 0;
}
function unselectOptions(optionStates) {
  Object.keys(optionStates).forEach(optionName => {
    optionStates[optionName] = false;
  });
}
function getSelected(optionStates) {
  return Object.keys(optionStates).filter(optionName => !!optionStates[optionName]);
}
/* harmony default export */ var SeriesPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    multiselect: Boolean,
    selectableColumns: {
      type: Array,
      default: () => []
    },
    selectableRows: {
      type: Array,
      default: () => []
    },
    selectedColumns: {
      type: Array,
      default: () => []
    },
    selectedRows: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isPopupVisible: false,
      columnStates: getInitialOptionStates(this.selectableColumns, this.selectedColumns),
      rowStates: getInitialOptionStates(this.selectableRows, this.selectedRows)
    };
  },
  emits: ['select'],
  created() {
    this.optionSelected = Object(external_CoreHome_["debounce"])(this.optionSelected, 0);
  },
  methods: {
    optionSelected(optionValue, optionStates) {
      if (!this.multiselect) {
        unselectOptions(this.columnStates);
        unselectOptions(this.rowStates);
      }
      optionStates[optionValue] = !optionStates[optionValue];
      this.triggerOnSelectAndClose();
    },
    onLeavePopup() {
      this.isPopupVisible = false;
      if (this.optionsChanged()) {
        this.triggerOnSelectAndClose();
      }
    },
    triggerOnSelectAndClose() {
      this.isPopupVisible = false;
      this.$emit('select', {
        columns: getSelected(this.columnStates),
        rows: getSelected(this.rowStates)
      });
    },
    optionsChanged() {
      return !arrayEqual(getSelected(this.columnStates), this.selectedColumns) || !arrayEqual(getSelected(this.rowStates), this.selectedRows);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue



SeriesPickervue_type_script_lang_ts.render = render

/* harmony default export */ var SeriesPicker = (SeriesPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=2757a028

const SingleMetricViewvue_type_template_id_2757a028_hoisted_1 = {
  class: "metric-sparkline"
};
const SingleMetricViewvue_type_template_id_2757a028_hoisted_2 = {
  class: "metric-value"
};
const SingleMetricViewvue_type_template_id_2757a028_hoisted_3 = ["title"];
const SingleMetricViewvue_type_template_id_2757a028_hoisted_4 = ["title"];
function SingleMetricViewvue_type_template_id_2757a028_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["singleMetricView", {
      'loading': _ctx.isLoading
    }]),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_2757a028_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    params: _ctx.sparklineParams
  }, null, 8, ["params"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_2757a028_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    title: _ctx.metricDocumentation
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricValue), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx.metricTranslation || '').toLowerCase()), 1)], 8, SingleMetricViewvue_type_template_id_2757a028_hoisted_3), _ctx.pastValue !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "metricEvolution",
    title: _ctx.translate('General_EvolutionSummaryGeneric', _ctx.metricValue, _ctx.currentPeriod, _ctx.pastValue, _ctx.pastPeriod, _ctx.metricChangePercent)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'positive-evolution': _ctx.metricValueUnformatted > _ctx.pastValueUnformatted,
      'negative-evolution': _ctx.metricValueUnformatted < _ctx.pastValueUnformatted
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricChangePercent), 3)], 8, SingleMetricViewvue_type_template_id_2757a028_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=2757a028

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



function getPastPeriodStr() {
  const {
    startDate
  } = external_CoreHome_["Range"].getLastNRange(external_CoreHome_["Matomo"].period, 2, external_CoreHome_["Matomo"].currentDateString);
  const dateRange = external_CoreHome_["Periods"].get(external_CoreHome_["Matomo"].period).parse(startDate).getDateRange();
  return `${Object(external_CoreHome_["format"])(dateRange[0])},${Object(external_CoreHome_["format"])(dateRange[1])}`;
}
const {
  $
} = window;
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
  setup(props) {
    const root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const isLoading = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const responses = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const actualMetric = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.metric);
    const actualIdGoal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(props.idGoal);
    const selectedColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => [actualIdGoal.value ? `goal${actualIdGoal.value}_${actualMetric.value}` : actualMetric.value]);
    const metricValueUnformatted = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _responses$value;
      if (!((_responses$value = responses.value) !== null && _responses$value !== void 0 && _responses$value[1])) {
        return null;
      }
      return responses.value[1][actualMetric.value];
    });
    const pastValueUnformatted = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _responses$value2;
      if (!((_responses$value2 = responses.value) !== null && _responses$value2 !== void 0 && _responses$value2[2])) {
        return null;
      }
      return responses.value[2][actualMetric.value] || 0;
    });
    const metricChangePercent = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (!metricValueUnformatted.value) {
        return null;
      }
      const currentValue = typeof metricValueUnformatted.value === 'string' ? parseInt(metricValueUnformatted.value, 10) : metricValueUnformatted.value;
      const pastValue = typeof pastValueUnformatted.value === 'string' ? parseInt(pastValueUnformatted.value, 10) : pastValueUnformatted.value;
      const evolution = external_CoreHome_["Matomo"].helper.calculateEvolution(currentValue, pastValue);
      return `${(evolution * 100).toFixed(2)} %`;
    });
    const pastValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _responses$value3;
      if (!((_responses$value3 = responses.value) !== null && _responses$value3 !== void 0 && _responses$value3[3])) {
        return null;
      }
      const pastDataFormatted = responses.value[3];
      return pastDataFormatted[actualMetric.value] || 0;
    });
    const metricValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _responses$value4;
      if (!((_responses$value4 = responses.value) !== null && _responses$value4 !== void 0 && _responses$value4[0])) {
        return null;
      }
      const currentData = responses.value[0];
      return currentData[actualMetric.value] || 0;
    });
    const metricTranslation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$metricTranslat;
      if (!((_props$metricTranslat = props.metricTranslations) !== null && _props$metricTranslat !== void 0 && _props$metricTranslat[actualMetric.value])) {
        return '';
      }
      return props.metricTranslations[actualMetric.value];
    });
    const metricDocumentation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$metricDocument;
      if (!((_props$metricDocument = props.metricDocumentations) !== null && _props$metricDocument !== void 0 && _props$metricDocument[actualMetric.value])) {
        return '';
      }
      return props.metricDocumentations[actualMetric.value];
    });
    const currentPeriod = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (external_CoreHome_["Matomo"].startDateString === external_CoreHome_["Matomo"].endDateString) {
        return external_CoreHome_["Matomo"].endDateString;
      }
      return `${external_CoreHome_["Matomo"].startDateString}, ${external_CoreHome_["Matomo"].endDateString}`;
    });
    function isIdGoalSet() {
      return actualIdGoal.value || actualIdGoal.value === 0;
    }
    const sparklineParams = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const params = {
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
    const pastPeriod = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (external_CoreHome_["Matomo"].period === 'range') {
        return undefined;
      }
      return getPastPeriodStr();
    });
    const selectableColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const result = [];
      Object.keys(props.metricTranslations).forEach(column => {
        result.push({
          column,
          translation: props.metricTranslations[column]
        });
      });
      Object.values(props.goals || {}).forEach(goal => {
        props.goalMetrics.forEach(column => {
          result.push({
            column: `goal${goal.idgoal}_${column}`,
            translation: `${goal.name} - ${props.metricTranslations[column]}`
          });
        });
      });
      return result;
    });
    function setWidgetTitle() {
      let title = metricTranslation.value;
      if (isIdGoalSet()) {
        var _props$goals$actualId;
        const goalName = ((_props$goals$actualId = props.goals[actualIdGoal.value]) === null || _props$goals$actualId === void 0 ? void 0 : _props$goals$actualId.name) || Object(external_CoreHome_["translate"])('General_Unknown');
        title = `${goalName} - ${title}`;
      }
      $(root.value).closest('div.widget').find('.widgetTop > .widgetName > span').text(title);
    }
    function getLastPeriodDate() {
      const range = external_CoreHome_["Range"].getLastNRange(external_CoreHome_["Matomo"].period, 2, external_CoreHome_["Matomo"].currentDateString);
      return Object(external_CoreHome_["format"])(range.startDate);
    }
    function fetchData() {
      isLoading.value = true;
      const promises = [];
      let apiModule = 'API';
      let apiAction = 'get';
      const extraParams = {};
      if (isIdGoalSet()) {
        // the conversion rate added by the AddColumnsProcessedMetrics filter conflicts w/
        // the goals one, so don't run it
        extraParams.idGoal = actualIdGoal.value;
        extraParams.filter_add_columns_when_show_all_columns = 0;
        apiModule = 'Goals';
        apiAction = 'get';
      }
      const method = `${apiModule}.${apiAction}`;
      // first request for formatted data
      promises.push(external_CoreHome_["AjaxHelper"].fetch(_extends({
        method,
        format_metrics: 'all'
      }, extraParams)));
      if (external_CoreHome_["Matomo"].period !== 'range') {
        // second request for unformatted data so we can calculate evolution
        promises.push(external_CoreHome_["AjaxHelper"].fetch(_extends({
          method,
          format_metrics: '0'
        }, extraParams)));
        // third request for past data (unformatted)
        promises.push(external_CoreHome_["AjaxHelper"].fetch(_extends({
          method,
          date: getLastPeriodDate(),
          format_metrics: '0'
        }, extraParams)));
        // fourth request for past data (formatted for tooltip display)
        promises.push(external_CoreHome_["AjaxHelper"].fetch(_extends({
          method,
          date: getLastPeriodDate(),
          format_metrics: 'all'
        }, extraParams)));
      }
      return Promise.all(promises).then(r => {
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
      let idGoal = undefined;
      let actualColumn = newColumn;
      const m = newColumn.match(/^goal([0-9]+)_(.*)/);
      if (m) {
        idGoal = +m[1];
        [,, actualColumn] = m;
      }
      if (actualMetric.value !== actualColumn || idGoal !== actualIdGoal.value) {
        actualMetric.value = actualColumn;
        actualIdGoal.value = idGoal;
        onMetricChanged(actualColumn);
      }
    }
    function createSeriesPicker() {
      const element = $(root.value);
      const $widgetName = element.closest('div.widget').find('.widgetTop > .widgetName');
      const $seriesPickerElem = $('<div class="single-metric-view-picker"><div></div></div>');
      const app = Object(external_CoreHome_["createVueApp"])({
        render: () => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(SeriesPicker, {
          multiselect: false,
          selectableColumns: selectableColumns.value,
          selectableRows: [],
          selectedColumns: selectedColumns.value,
          selectedRows: [],
          onSelect: ({
            columns
          }) => {
            setMetric(columns[0]);
          }
        })
      });
      $widgetName.append($seriesPickerElem);
      app.mount($seriesPickerElem.children()[0]);
      return app;
    }
    let seriesPickerApp;
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      seriesPickerApp = createSeriesPicker();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(() => {
      $(root.value).closest('.widgetContent').off('widget:destroy').off('widget:reload');
      $(root.value).closest('div.widget').find('.single-metric-view-picker').remove();
      seriesPickerApp.unmount();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => props.metric, () => {
      onMetricChanged(props.metric);
    });
    onMetricChanged(props.metric);
    return {
      root,
      metricValue,
      isLoading,
      selectedColumns,
      responses,
      metricValueUnformatted,
      pastValueUnformatted,
      metricChangePercent,
      pastValue,
      metricTranslation,
      metricDocumentation,
      sparklineParams,
      pastPeriod,
      selectableColumns,
      currentPeriod
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue



SingleMetricViewvue_type_script_lang_ts.render = SingleMetricViewvue_type_template_id_2757a028_render

/* harmony default export */ var SingleMetricView = (SingleMetricViewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/index.ts
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
//# sourceMappingURL=CoreVisualizations.umd.js.map