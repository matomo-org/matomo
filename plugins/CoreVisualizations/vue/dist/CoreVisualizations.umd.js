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
__webpack_require__.d(__webpack_exports__, "EvolutionBadge", function() { return /* reexport */ EvolutionBadge; });
__webpack_require__.d(__webpack_exports__, "MetricValue", function() { return /* reexport */ MetricValue; });
__webpack_require__.d(__webpack_exports__, "SeriesPicker", function() { return /* reexport */ SeriesPicker; });
__webpack_require__.d(__webpack_exports__, "MetricsPicker", function() { return /* reexport */ MetricsPicker; });
__webpack_require__.d(__webpack_exports__, "SingleMetricView", function() { return /* reexport */ SingleMetricView; });
__webpack_require__.d(__webpack_exports__, "SparklinesGrid", function() { return /* reexport */ SparklinesGrid; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.vue?vue&type=template&id=94a77dd2

const _hoisted_1 = ["title"];
const _hoisted_2 = {
  class: "evolutionBadge__icon",
  "aria-hidden": "true"
};
const _hoisted_3 = {
  class: "evolutionBadge__value"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EvolutionTrendIcon = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EvolutionTrendIcon");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["evolutionBadge", _ctx.directionClass]),
    title: _ctx.tooltip || undefined
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EvolutionTrendIcon, {
    class: "evolutionTrendIcon",
    direction: _ctx.direction
  }, null, 8, ["direction"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formattedPercent), 1)], 10, _hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.vue?vue&type=template&id=94a77dd2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionTrendIcon.vue?vue&type=template&id=14185072

const EvolutionTrendIconvue_type_template_id_14185072_hoisted_1 = {
  key: 0,
  viewBox: "0 0 16 16"
};
const EvolutionTrendIconvue_type_template_id_14185072_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M3.77344 11L8.27344 5L12.7734 11H3.77344Z",
  fill: "currentColor"
}, null, -1);
const EvolutionTrendIconvue_type_template_id_14185072_hoisted_3 = [EvolutionTrendIconvue_type_template_id_14185072_hoisted_2];
const _hoisted_4 = {
  key: 1,
  viewBox: "0 0 16 16"
};
const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M3.77344 6L8.27344 12L12.7734 6H3.77344Z",
  fill: "currentColor"
}, null, -1);
const _hoisted_6 = [_hoisted_5];
const _hoisted_7 = {
  key: 2,
  viewBox: "0 0 16 16"
};
const _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("rect", {
  x: "3",
  y: "7",
  width: "10",
  height: "2",
  fill: "currentColor"
}, null, -1);
const _hoisted_9 = [_hoisted_8];
function EvolutionTrendIconvue_type_template_id_14185072_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.direction === 'up' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", EvolutionTrendIconvue_type_template_id_14185072_hoisted_1, EvolutionTrendIconvue_type_template_id_14185072_hoisted_3)) : _ctx.direction === 'down' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", _hoisted_4, _hoisted_6)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", _hoisted_7, _hoisted_9));
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionTrendIcon.vue?vue&type=template&id=14185072

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionTrendIcon.vue?vue&type=script&lang=ts

/* harmony default export */ var EvolutionTrendIconvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'EvolutionTrendIcon',
  props: {
    direction: {
      type: String,
      required: true,
      validator: value => ['up', 'down', 'neutral'].indexOf(value) !== -1
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionTrendIcon.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionTrendIcon.vue



EvolutionTrendIconvue_type_script_lang_ts.render = EvolutionTrendIconvue_type_template_id_14185072_render

/* harmony default export */ var EvolutionTrendIcon = (EvolutionTrendIconvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.vue?vue&type=script&lang=ts


/* harmony default export */ var EvolutionBadgevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'EvolutionBadge',
  components: {
    EvolutionTrendIcon: EvolutionTrendIcon
  },
  props: {
    // the change to display, either a number (eg 4, -4) or a pre-formatted
    // string as emitted by Sparklines/Config.php (eg "4%", "-4%")
    percent: {
      type: [Number, String],
      required: true
    },
    // when true the colour is inverted, so a decrease reads as positive (eg bounce rate)
    isLowerValueBetter: {
      type: Boolean,
      default: false
    },
    // raw value difference (currentValue - pastValue); the authoritative source of the
    // arrow direction when available, falling back to the sign of percent otherwise
    trend: {
      type: Number,
      default: undefined
    },
    tooltip: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const changeValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (typeof props.trend === 'number' && !Number.isNaN(props.trend)) {
        return props.trend;
      }
      // only the sign matters here. Fold the localised minus (U+2212, eg fi/sv)
      // to ASCII so a coarse parse of the formatted percent gets the sign right.
      const numeric = parseFloat(String(props.percent).replace('\u2212', '-').replace(',', '.').replace(/[^0-9.+-]/g, ''));
      return Number.isNaN(numeric) ? 0 : numeric;
    });
    const direction = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (changeValue.value > 0) {
        return 'up';
      }
      if (changeValue.value < 0) {
        return 'down';
      }
      return 'neutral';
    });
    // the arrow direction always reflects the actual value change, while the colour
    // (positive/negative) reflects whether that change is good or bad for the metric
    const directionClass = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (direction.value === 'neutral') {
        return 'evolutionBadge--neutral';
      }
      const increased = direction.value === 'up';
      const isPositive = props.isLowerValueBetter ? !increased : increased;
      return isPositive ? 'evolutionBadge--positive' : 'evolutionBadge--negative';
    });
    const formattedPercent = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const label = typeof props.percent === 'number' ? `${props.percent}%` : String(props.percent).trim();
      const sign = label.charAt(0);
      if (changeValue.value > 0 && sign !== '+' && sign !== '-') {
        return `+${label}`;
      }
      return label;
    });
    return {
      direction,
      directionClass,
      formattedPercent
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/EvolutionBadge/EvolutionBadge.vue



EvolutionBadgevue_type_script_lang_ts.render = render

/* harmony default export */ var EvolutionBadge = (EvolutionBadgevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=template&id=04f47fe6

const MetricValuevue_type_template_id_04f47fe6_hoisted_1 = {
  class: "metricValue"
};
const MetricValuevue_type_template_id_04f47fe6_hoisted_2 = ["title"];
const MetricValuevue_type_template_id_04f47fe6_hoisted_3 = {
  class: "metricValue__primary"
};
const MetricValuevue_type_template_id_04f47fe6_hoisted_4 = ["title"];
const MetricValuevue_type_template_id_04f47fe6_hoisted_5 = {
  key: 1,
  class: "metricValue__secondary"
};
const MetricValuevue_type_template_id_04f47fe6_hoisted_6 = {
  class: "metricValue__secondaryLine"
};
function MetricValuevue_type_template_id_04f47fe6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$displayValue;
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricValuevue_type_template_id_04f47fe6_hoisted_1, [_ctx.displayTitle ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["metricValue__title", {
      'metricValue__title--documented': !!_ctx.documentation
    }]),
    title: _ctx.documentation || _ctx.displayTitle
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.displayTitle), 1)], 10, MetricValuevue_type_template_id_04f47fe6_hoisted_2)), [[_directive_tooltips, {
    duration: 200,
    delay: 200
  }]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MetricValuevue_type_template_id_04f47fe6_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    class: "metricValue__number",
    title: (_ctx$displayValue = _ctx.displayValue) === null || _ctx$displayValue === void 0 ? void 0 : _ctx$displayValue.toString()
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.displayValue), 1)], 8, MetricValuevue_type_template_id_04f47fe6_hoisted_4)), [[_directive_tooltips, {
    duration: 200,
    delay: 200
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "evolution")]), _ctx.hasSecondary ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricValuevue_type_template_id_04f47fe6_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricValuevue_type_template_id_04f47fe6_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.displaySecondaryLine), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=template&id=04f47fe6

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=script&lang=ts


/* harmony default export */ var MetricValuevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'MetricValue',
  directives: {
    Tooltips: external_CoreHome_["Tooltips"]
  },
  props: {
    // Optional: the date-comparison card reuses MetricValue for a value column with no title
    // (the date label is rendered separately by DateAtom). The title div is skipped when empty.
    title: {
      type: String,
      default: ''
    },
    // Metric value: a raw number is locale-formatted here; an already-formatted string
    // (e.g. "50%" or "4min 22s") is rendered as-is.
    value: {
      type: [String, Number],
      required: true
    },
    // Optional secondary line: value and label, combined into one string for display.
    // Matomo provides these separately as metric.value + metric.description.
    secondaryValue: [String, Number],
    secondaryLabel: String,
    // Optional metric documentation; when set it is shown as the title tooltip (otherwise the
    // tooltip falls back to the full title so a clipped title stays recoverable on hover).
    documentation: String
  },
  computed: {
    displayTitle() {
      return Object(external_CoreHome_["ucfirst"])(this.title, document.documentElement.lang);
    },
    displayValue() {
      return this.formatValue(this.value);
    },
    displaySecondaryValue() {
      return this.formatValue(this.secondaryValue);
    },
    displaySecondaryLine() {
      const value = this.displaySecondaryValue;
      const valueText = value === undefined || value === null ? '' : String(value);
      const label = this.secondaryLabel;
      if (!label) {
        return valueText;
      }
      // Merge the value into the label at its printf `%s` slot so word order holds across locales
      // (some put `%s` last), otherwise prepend it. The replace callback keeps a `$` in the value
      // (e.g. '$12') from acting as a regex backreference.
      if (/%(?:\d+\$)?s/.test(label)) {
        return label.replace(/%(?:\d+\$)?s/g, () => valueText);
      }
      return `${valueText} ${label}`;
    },
    hasSecondary() {
      return this.secondaryValue !== undefined && this.secondaryValue !== null && this.secondaryValue !== '';
    }
  },
  methods: {
    // Locale-format raw numbers (plain metrics); leave already-formatted strings untouched.
    formatValue(value) {
      return typeof value === 'number' ? external_CoreHome_["NumberFormatter"].formatNumber(value, 2) : value;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue



MetricValuevue_type_script_lang_ts.render = MetricValuevue_type_template_id_04f47fe6_render

/* harmony default export */ var MetricValue = (MetricValuevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=7c1adaf7

const SeriesPickervue_type_template_id_7c1adaf7_hoisted_1 = {
  key: 0,
  class: "jqplot-seriespicker-popover"
};
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_2 = {
  class: "headline"
};
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_3 = ["onClick"];
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_4 = ["type", "checked"];
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_5 = {
  key: 0,
  class: "headline recordsToPlot"
};
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_6 = ["onClick"];
const SeriesPickervue_type_template_id_7c1adaf7_hoisted_7 = ["type", "checked"];
function SeriesPickervue_type_template_id_7c1adaf7_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["jqplot-seriespicker", {
      open: _ctx.isPopupVisible
    }]),
    onMouseenter: _cache[1] || (_cache[1] = $event => _ctx.isPopupVisible = true),
    onMouseleave: _cache[2] || (_cache[2] = $event => _ctx.onLeavePopup())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(() => {}, ["prevent", "stop"]))
  }, " + "), _ctx.isPopupVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SeriesPickervue_type_template_id_7c1adaf7_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", SeriesPickervue_type_template_id_7c1adaf7_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.multiselect ? 'General_MetricsToPlot' : 'General_MetricToPlot')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableColumns, columnConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickColumn",
      onClick: $event => _ctx.optionSelected(columnConfig.column, _ctx.columnStates),
      key: columnConfig.column
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.columnStates[columnConfig.column]
    }, null, 8, SeriesPickervue_type_template_id_7c1adaf7_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(columnConfig.translation), 1)])], 8, SeriesPickervue_type_template_id_7c1adaf7_hoisted_3);
  }), 128)), _ctx.selectableRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SeriesPickervue_type_template_id_7c1adaf7_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RecordsToPlot')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableRows, rowConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickRow",
      onClick: $event => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates),
      key: rowConfig.matcher
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.rowStates[rowConfig.matcher]
    }, null, 8, SeriesPickervue_type_template_id_7c1adaf7_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rowConfig.label), 1)])], 8, SeriesPickervue_type_template_id_7c1adaf7_hoisted_6);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 34);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=7c1adaf7

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



SeriesPickervue_type_script_lang_ts.render = SeriesPickervue_type_template_id_7c1adaf7_render

/* harmony default export */ var SeriesPicker = (SeriesPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.vue?vue&type=template&id=5b298d15

const MetricsPickervue_type_template_id_5b298d15_hoisted_1 = {
  ref: "root",
  class: "metrics-picker"
};
const MetricsPickervue_type_template_id_5b298d15_hoisted_2 = {
  ref: "expander",
  type: "button",
  class: "metrics-picker__toggle"
};
const MetricsPickervue_type_template_id_5b298d15_hoisted_3 = {
  class: "metrics-picker__toggle-label"
};
const MetricsPickervue_type_template_id_5b298d15_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down metrics-picker__chevron"
}, null, -1);
const MetricsPickervue_type_template_id_5b298d15_hoisted_5 = {
  class: "metrics-picker__dropdown"
};
function MetricsPickervue_type_template_id_5b298d15_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MetricsPickerOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MetricsPickerOptions");
  const _directive_expand_on_click = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-click");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricsPickervue_type_template_id_5b298d15_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", MetricsPickervue_type_template_id_5b298d15_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricsPickervue_type_template_id_5b298d15_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ChooseMetrics')), 1), MetricsPickervue_type_template_id_5b298d15_hoisted_4], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MetricsPickervue_type_template_id_5b298d15_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MetricsPickerOptions, {
    multiselect: _ctx.multiselect,
    "selectable-columns": _ctx.selectableColumns,
    "selectable-rows": _ctx.selectableRows,
    "selected-columns": _ctx.selectedColumns,
    "selected-rows": _ctx.selectedRows,
    onSelect: _cache[0] || (_cache[0] = $event => _ctx.onSelect($event))
  }, null, 8, ["multiselect", "selectable-columns", "selectable-rows", "selected-columns", "selected-rows"])])])), [[_directive_expand_on_click, {
    expander: 'expander'
  }]]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.vue?vue&type=template&id=5b298d15

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPickerOptions.vue?vue&type=template&id=0bdfb6f4

const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_1 = ["role", "aria-label"];
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_2 = ["type", "checked", "onChange", "onKeydown"];
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true"
}, null, -1);
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_4 = {
  class: "metrics-picker__title"
};
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_5 = {
  key: 0,
  class: "metrics-picker__headline"
};
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_6 = ["type", "checked", "onChange", "onKeydown"];
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true"
}, null, -1);
const MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_8 = {
  class: "metrics-picker__title"
};
function MetricsPickerOptionsvue_type_template_id_0bdfb6f4_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "metrics-picker__options",
    role: _ctx.multiselect ? 'group' : 'radiogroup',
    "aria-label": _ctx.translate('General_ChooseMetrics')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableColumns, columnConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", {
      class: "metrics-picker__column metrics-picker__label",
      key: columnConfig.column
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "filled-in",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.columnStates[columnConfig.column],
      onChange: $event => _ctx.optionSelected(columnConfig.column, _ctx.columnStates),
      onKeydown: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.optionSelected(columnConfig.column, _ctx.columnStates), ["prevent"]), ["enter"])
    }, null, 40, MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_2), MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(columnConfig.translation), 1)]);
  }), 128)), _ctx.selectableRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RecordsToPlot')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableRows, rowConfig => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", {
      class: "metrics-picker__row metrics-picker__label",
      key: rowConfig.matcher
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "filled-in",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.rowStates[rowConfig.matcher],
      onChange: $event => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates),
      onKeydown: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates), ["prevent"]), ["enter"])
    }, null, 40, MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_6), MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rowConfig.label), 1)]);
  }), 128))], 8, MetricsPickerOptionsvue_type_template_id_0bdfb6f4_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPickerOptions.vue?vue&type=template&id=0bdfb6f4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPickerOptions.vue?vue&type=script&lang=ts

// Declared outside the component because it is needed inside data(), before the
// component's methods are available.
function MetricsPickerOptionsvue_type_script_lang_ts_getInitialOptionStates(allOptions, selectedOptions) {
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
/* harmony default export */ var MetricsPickerOptionsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
      columnStates: MetricsPickerOptionsvue_type_script_lang_ts_getInitialOptionStates(this.selectableColumns, this.selectedColumns),
      rowStates: MetricsPickerOptionsvue_type_script_lang_ts_getInitialOptionStates(this.selectableRows, this.selectedRows)
    };
  },
  emits: ['select'],
  methods: {
    unselectOptions(optionStates) {
      Object.keys(optionStates).forEach(optionName => {
        optionStates[optionName] = false;
      });
    },
    getSelected(optionStates) {
      return Object.keys(optionStates).filter(optionName => !!optionStates[optionName]);
    },
    optionSelected(optionValue, optionStates) {
      if (!this.multiselect) {
        this.unselectOptions(this.columnStates);
        this.unselectOptions(this.rowStates);
      }
      optionStates[optionValue] = !optionStates[optionValue];
      this.$emit('select', {
        columns: this.getSelected(this.columnStates),
        rows: this.getSelected(this.rowStates)
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPickerOptions.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPickerOptions.vue



MetricsPickerOptionsvue_type_script_lang_ts.render = MetricsPickerOptionsvue_type_template_id_0bdfb6f4_render

/* harmony default export */ var MetricsPickerOptions = (MetricsPickerOptionsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.vue?vue&type=script&lang=ts



/* harmony default export */ var MetricsPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
  components: {
    MetricsPickerOptions: MetricsPickerOptions
  },
  directives: {
    ExpandOnClick: external_CoreHome_["ExpandOnClick"]
  },
  emits: ['select'],
  methods: {
    onSelect(selected) {
      this.$emit('select', selected);
      // selecting a metric applies the change and closes the dropdown
      this.$refs.root.classList.remove('expanded');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricsPicker/MetricsPicker.vue



MetricsPickervue_type_script_lang_ts.render = MetricsPickervue_type_template_id_5b298d15_render

/* harmony default export */ var MetricsPicker = (MetricsPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=20c744f6

const SingleMetricViewvue_type_template_id_20c744f6_hoisted_1 = {
  class: "metric-sparkline"
};
const SingleMetricViewvue_type_template_id_20c744f6_hoisted_2 = {
  class: "metric-value"
};
const SingleMetricViewvue_type_template_id_20c744f6_hoisted_3 = ["title"];
const SingleMetricViewvue_type_template_id_20c744f6_hoisted_4 = ["title"];
function SingleMetricViewvue_type_template_id_20c744f6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["singleMetricView", {
      'loading': _ctx.isLoading
    }]),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_20c744f6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    params: _ctx.sparklineParams
  }, null, 8, ["params"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_20c744f6_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    title: _ctx.metricDocumentation
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricValue), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx.metricTranslation || '').toLowerCase()), 1)], 8, SingleMetricViewvue_type_template_id_20c744f6_hoisted_3), _ctx.pastValue !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "metricEvolution",
    title: _ctx.translate('General_EvolutionSummaryGeneric', _ctx.metricValue, _ctx.currentPeriod, _ctx.pastValue, _ctx.pastPeriod, _ctx.metricChangePercent)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.evolutionClass)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricChangePercent), 3)], 8, SingleMetricViewvue_type_template_id_20c744f6_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=20c744f6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=script&lang=ts



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
    goalMetrics: Array,
    lowerIsBetterMetrics: {
      type: Array,
      default: () => []
    }
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
      // a metric that is missing for the current period is treated as 0, just like
      // the past value below, so an evolution down to zero is still calculated
      return responses.value[1][actualMetric.value] || 0;
    });
    const pastValueUnformatted = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _responses$value2;
      if (!((_responses$value2 = responses.value) !== null && _responses$value2 !== void 0 && _responses$value2[2])) {
        return null;
      }
      return responses.value[2][actualMetric.value] || 0;
    });
    const isLowerValueBetter = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => props.lowerIsBetterMetrics.indexOf(actualMetric.value) !== -1);
    const evolutionClass = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (metricValueUnformatted.value === null || pastValueUnformatted.value === null || metricValueUnformatted.value === pastValueUnformatted.value) {
        return [];
      }
      // arrow direction always reflects the actual value change, while the colour
      // (positive/negative) reflects whether that change is good or bad for the metric
      const increased = metricValueUnformatted.value > pastValueUnformatted.value;
      const isPositive = isLowerValueBetter.value ? !increased : increased;
      return [increased ? 'evolution-up' : 'evolution-down', isPositive ? 'positive-evolution' : 'negative-evolution'];
    });
    const metricChangePercent = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (metricValueUnformatted.value === null || metricValueUnformatted.value === undefined || pastValueUnformatted.value === null || pastValueUnformatted.value === undefined) {
        return null;
      }
      const currentValue = typeof metricValueUnformatted.value === 'string' ? parseFloat(metricValueUnformatted.value) : metricValueUnformatted.value;
      const pastValue = typeof pastValueUnformatted.value === 'string' ? parseFloat(pastValueUnformatted.value) : pastValueUnformatted.value;
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
      $(root.value).closest('div.widget').find('.widgetName > span').text(title);
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
      promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
        method,
        format_metrics: 'all'
      }, extraParams)));
      if (external_CoreHome_["Matomo"].period !== 'range') {
        // second request for unformatted data so we can calculate evolution
        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
          method,
          format_metrics: '0'
        }, extraParams)));
        // third request for past data (unformatted)
        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
          method,
          date: getLastPeriodDate(),
          format_metrics: '0'
        }, extraParams)));
        // fourth request for past data (formatted for tooltip display)
        promises.push(external_CoreHome_["AjaxHelper"].fetch(Object.assign({
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
      const $widgetName = element.closest('div.widget').find('.widgetName');
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
      evolutionClass,
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



SingleMetricViewvue_type_script_lang_ts.render = SingleMetricViewvue_type_template_id_20c744f6_render

/* harmony default export */ var SingleMetricView = (SingleMetricViewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SparklinesGrid/SparklinesGrid.vue?vue&type=template&id=15709986

function SparklinesGridvue_type_template_id_15709986_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentComparisonCard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentComparisonCard");
  const _component_SparklineCard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SparklineCard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.gridClasses)
  }, [_ctx.isSegmentMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.segmentGroups, (segments, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index,
      class: "sparklinesGrid__item"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentComparisonCard, {
      segments: segments,
      "are-sparklines-linkable": _ctx.areSparklinesLinkable,
      "all-metrics-documentation": _ctx.allMetricsDocumentation
    }, null, 8, ["segments", "are-sparklines-linkable", "all-metrics-documentation"])]);
  }), 128)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.flatSparklines, (sparkline, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index,
      class: "sparklinesGrid__item"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SparklineCard, {
      sparkline: sparkline,
      "are-sparklines-linkable": _ctx.areSparklinesLinkable,
      "all-metrics-documentation": _ctx.allMetricsDocumentation
    }, null, 8, ["sparkline", "are-sparklines-linkable", "all-metrics-documentation"])]);
  }), 128))], 2);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SparklinesGrid/SparklinesGrid.vue?vue&type=template&id=15709986

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SparklineCard.vue?vue&type=template&id=4308735a

const SparklineCardvue_type_template_id_4308735a_hoisted_1 = ["data-graph-params", "data-series-indices"];
const SparklineCardvue_type_template_id_4308735a_hoisted_2 = {
  key: 0,
  class: "sparklineCard__title"
};
function SparklineCardvue_type_template_id_4308735a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DateComparison = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DateComparison");
  const _component_NoComparison = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("NoComparison");
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["sparkline sparklineCard", {
      notLinkable: !_ctx.areSparklinesLinkable
    }]),
    "data-graph-params": _ctx.graphParamsAttr,
    "data-series-indices": _ctx.seriesIndicesAttr
  }, [_ctx.sparkline.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SparklineCardvue_type_template_id_4308735a_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sparkline.title), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isComparison ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DateComparison, {
    key: 1,
    sparkline: _ctx.sparkline
  }, null, 8, ["sparkline"])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_NoComparison, {
    key: 2,
    sparkline: _ctx.sparkline,
    "all-metrics-documentation": _ctx.allMetricsDocumentation
  }, null, 8, ["sparkline", "all-metrics-documentation"])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["sparklineCard__sparkline", {
      'sparklineCard__sparkline--wide': _ctx.isComparison
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    width: _ctx.sparklineWidth,
    height: 40,
    params: _ctx.sparkline.url,
    "series-indices": _ctx.sparkline.seriesIndices
  }, null, 8, ["width", "params", "series-indices"])], 2)], 10, SparklineCardvue_type_template_id_4308735a_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SparklineCard.vue?vue&type=template&id=4308735a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/NoComparison.vue?vue&type=template&id=44e340e0

const NoComparisonvue_type_template_id_44e340e0_hoisted_1 = {
  class: "sparklineNoComparison"
};
function NoComparisonvue_type_template_id_44e340e0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EvolutionBadge = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EvolutionBadge");
  const _component_MetricValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MetricValue");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", NoComparisonvue_type_template_id_44e340e0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MetricValue, {
    class: "metricValue--fixedHeight",
    title: _ctx.title,
    value: _ctx.primaryValue,
    "secondary-value": _ctx.secondaryValue,
    "secondary-label": _ctx.secondaryLabel,
    documentation: _ctx.documentation
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createSlots"])({
    _: 2
  }, [_ctx.sparkline.evolution ? {
    name: "evolution",
    fn: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EvolutionBadge, {
      percent: _ctx.sparkline.evolution.percent,
      trend: _ctx.sparkline.evolution.trend,
      "is-lower-value-better": _ctx.sparkline.evolution.isLowerValueBetter,
      tooltip: _ctx.sparkline.evolution.tooltip || ''
    }, null, 8, ["percent", "trend", "is-lower-value-better", "tooltip"])]),
    key: "0"
  } : undefined]), 1032, ["title", "value", "secondary-value", "secondary-label", "documentation"])]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/NoComparison.vue?vue&type=template&id=44e340e0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/NoComparison.vue?vue&type=script&lang=ts



/**
 * No-comparison body for a sparkline card: the metric readout only (the shell renders the shared
 * sparkline below it). Composes the MetricValue + EvolutionBadge atoms. In no-comparison mode the
 * metrics live under the '' group key: the first is the primary value, an optional second is the
 * "unique" line.
 */
/* harmony default export */ var NoComparisonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'NoComparison',
  components: {
    MetricValue: MetricValue,
    EvolutionBadge: EvolutionBadge
  },
  props: {
    sparkline: {
      type: Object,
      required: true
    },
    // Backend map of metric column -> documentation string (from Sparklines.php).
    allMetricsDocumentation: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const primaryMetric = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$sparkline$metr;
      return (_props$sparkline$metr = props.sparkline.metrics) === null || _props$sparkline$metr === void 0 || (_props$sparkline$metr = _props$sparkline$metr['']) === null || _props$sparkline$metr === void 0 ? void 0 : _props$sparkline$metr[0];
    });
    // Optional secondary metric (eg "9,527 unique"), shown only when the report adds a
    // second metric to the same sparkline. MetricValue hides the line when it is absent.
    const secondaryMetric = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$sparkline$metr2;
      return (_props$sparkline$metr2 = props.sparkline.metrics) === null || _props$sparkline$metr2 === void 0 || (_props$sparkline$metr2 = _props$sparkline$metr2['']) === null || _props$sparkline$metr2 === void 0 ? void 0 : _props$sparkline$metr2[1];
    });
    // Prefer the metric's column name (eg "Bounce Rate"), falling back to the label.
    const title = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _primaryMetric$value, _primaryMetric$value2;
      return ((_primaryMetric$value = primaryMetric.value) === null || _primaryMetric$value === void 0 ? void 0 : _primaryMetric$value.title) || ((_primaryMetric$value2 = primaryMetric.value) === null || _primaryMetric$value2 === void 0 ? void 0 : _primaryMetric$value2.description) || '';
    });
    // Documentation for the primary metric, looked up by its column. Empty for sparklines added
    // without a column (column === '') or metrics with no registered documentation.
    const documentation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _primaryMetric$value$, _primaryMetric$value3;
      return props.allMetricsDocumentation[(_primaryMetric$value$ = (_primaryMetric$value3 = primaryMetric.value) === null || _primaryMetric$value3 === void 0 ? void 0 : _primaryMetric$value3.column) !== null && _primaryMetric$value$ !== void 0 ? _primaryMetric$value$ : ''] || undefined;
    });
    // Values are passed raw to MetricValue, which locale-formats numbers and renders
    // already-formatted strings verbatim.
    const primaryValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _primaryMetric$value$2, _primaryMetric$value4;
      return (_primaryMetric$value$2 = (_primaryMetric$value4 = primaryMetric.value) === null || _primaryMetric$value4 === void 0 ? void 0 : _primaryMetric$value4.value) !== null && _primaryMetric$value$2 !== void 0 ? _primaryMetric$value$2 : '';
    });
    const secondaryValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _secondaryMetric$valu;
      return (_secondaryMetric$valu = secondaryMetric.value) === null || _secondaryMetric$valu === void 0 ? void 0 : _secondaryMetric$valu.value;
    });
    const secondaryLabel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _secondaryMetric$valu2;
      return (_secondaryMetric$valu2 = secondaryMetric.value) === null || _secondaryMetric$valu2 === void 0 ? void 0 : _secondaryMetric$valu2.description;
    });
    return {
      title,
      documentation,
      primaryValue,
      secondaryValue,
      secondaryLabel
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/NoComparison.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/NoComparison.vue



NoComparisonvue_type_script_lang_ts.render = NoComparisonvue_type_template_id_44e340e0_render

/* harmony default export */ var NoComparison = (NoComparisonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/DateComparison.vue?vue&type=template&id=60a224a0

const DateComparisonvue_type_template_id_60a224a0_hoisted_1 = {
  class: "sparklineDateComparison"
};
const DateComparisonvue_type_template_id_60a224a0_hoisted_2 = ["title"];
function DateComparisonvue_type_template_id_60a224a0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_PeriodColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PeriodColumns");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DateComparisonvue_type_template_id_60a224a0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "sparklineDateComparison__title",
    title: _ctx.metricTitle
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricTitle), 9, DateComparisonvue_type_template_id_60a224a0_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PeriodColumns, {
    entry: _ctx.sparkline
  }, null, 8, ["entry"])]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateComparison.vue?vue&type=template&id=60a224a0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/PeriodColumns.vue?vue&type=template&id=eb849e72

const PeriodColumnsvue_type_template_id_eb849e72_hoisted_1 = {
  class: "periodColumns"
};
const PeriodColumnsvue_type_template_id_eb849e72_hoisted_2 = {
  key: 0,
  class: "periodColumns__separator"
};
const PeriodColumnsvue_type_template_id_eb849e72_hoisted_3 = {
  class: "periodColumns__column"
};
function PeriodColumnsvue_type_template_id_eb849e72_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DateAtom = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DateAtom");
  const _component_EvolutionBadge = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EvolutionBadge");
  const _component_MetricValue = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MetricValue");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodColumnsvue_type_template_id_eb849e72_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.periods, (period, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: period.label
    }, [index > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PeriodColumnsvue_type_template_id_eb849e72_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PeriodColumnsvue_type_template_id_eb849e72_hoisted_3, [_ctx.showLabels ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DateAtom, {
      key: 0,
      label: period.label
    }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MetricValue, {
      class: "metricValue--noTitle",
      value: period.primaryValue,
      "secondary-value": period.secondaryValue,
      "secondary-label": period.secondaryLabel
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createSlots"])({
      _: 2
    }, [period.evolution ? {
      name: "evolution",
      fn: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EvolutionBadge, {
        percent: period.evolution.percent,
        trend: period.evolution.trend,
        "is-lower-value-better": period.evolution.isLowerValueBetter,
        tooltip: period.evolution.tooltip || ''
      }, null, 8, ["percent", "trend", "is-lower-value-better", "tooltip"])]),
      key: "0"
    } : undefined]), 1032, ["value", "secondary-value", "secondary-label"])])], 64);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/PeriodColumns.vue?vue&type=template&id=eb849e72

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/DateAtom.vue?vue&type=template&id=c76a1e74

const DateAtomvue_type_template_id_c76a1e74_hoisted_1 = ["title"];
function DateAtomvue_type_template_id_c76a1e74_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "dateAtom",
    title: _ctx.label
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label), 9, DateAtomvue_type_template_id_c76a1e74_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateAtom.vue?vue&type=template&id=c76a1e74

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/DateAtom.vue?vue&type=script&lang=ts

/**
 * A compared date shown above its value column in a date-comparison sparkline card. The label is
 * the backend's already-localised pretty period string (eg "Monday, May 4, 2026"); this atom only
 * styles it. Kept a separate component so a series-colour indicator can be added later without
 * touching DateComparison.
 */
/* harmony default export */ var DateAtomvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'DateAtom',
  props: {
    label: {
      type: String,
      required: true
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateAtom.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateAtom.vue



DateAtomvue_type_script_lang_ts.render = DateAtomvue_type_template_id_c76a1e74_render

/* harmony default export */ var DateAtom = (DateAtomvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/PeriodColumns.vue?vue&type=script&lang=ts




/**
 * Shared compared-period columns for comparison cards: takes a SparklineEntry and renders one
 * column per compared period (a DateAtom label, the MetricValue readout with no title, and an
 * EvolutionBadge when the period has evolution), split by dividers. Used by DateComparison (date
 * comparison) and SegmentComparisonRow (segment + date). The host owns the outer spacing; this
 * derives the columns from the entry's per-period metric groups (metricsOrder order) and lays
 * them out. The date label shows only when comparing >1 period — a single column needs no label.
 */
/* harmony default export */ var PeriodColumnsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'PeriodColumns',
  components: {
    DateAtom: DateAtom,
    MetricValue: MetricValue,
    EvolutionBadge: EvolutionBadge
  },
  props: {
    entry: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    // One column per period, in backend order via `metricsOrder` (not Object.keys, which JS
    // re-sorts integer-like year labels). Primary = big value + evolution; optional second = the
    // "unique" sub-line. Values pass raw to MetricValue, which locale-formats numbers.
    const periods = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const metrics = props.entry.metrics || {};
      const order = props.entry.metricsOrder || [];
      return order.map(label => {
        var _primary$value;
        const groupMetrics = metrics[label] || [];
        const primary = groupMetrics[0];
        const secondary = groupMetrics[1];
        return {
          label,
          primaryValue: (_primary$value = primary === null || primary === void 0 ? void 0 : primary.value) !== null && _primary$value !== void 0 ? _primary$value : '',
          evolution: primary === null || primary === void 0 ? void 0 : primary.evolution,
          secondaryValue: secondary === null || secondary === void 0 ? void 0 : secondary.value,
          secondaryLabel: secondary === null || secondary === void 0 ? void 0 : secondary.description
        };
      });
    });
    // Label the columns only when comparing more than one period (segment-only has one column).
    const showLabels = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => periods.value.length > 1);
    return {
      periods,
      showLabels
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/PeriodColumns.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/PeriodColumns.vue



PeriodColumnsvue_type_script_lang_ts.render = PeriodColumnsvue_type_template_id_eb849e72_render

/* harmony default export */ var PeriodColumns = (PeriodColumnsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/DateComparison.vue?vue&type=script&lang=ts



/**
 * Date-comparison body for a sparkline card: metric name as title and one column per compared date
 * (the shell renders the shared sparkline below, which draws a coloured series per date). Metrics
 * arrive grouped by date label (one column each, in seriesIndices order). Only two-date comparison
 * reaches here. The columns themselves are rendered by the shared PeriodColumns component.
 */
/* harmony default export */ var DateComparisonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'DateComparison',
  components: {
    PeriodColumns: PeriodColumns
  },
  props: {
    sparkline: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    // Read the metric name from the first column via metricsOrder, not Object.values, whose order
    // JS shuffles for integer-like labels. The name is the same across columns, so this is for
    // consistency, not correctness.
    const metricTitle = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _, _metrics$firstLabel;
      const metrics = props.sparkline.metrics || {};
      const firstLabel = (_ = (props.sparkline.metricsOrder || [])[0]) !== null && _ !== void 0 ? _ : Object.keys(metrics)[0];
      const primary = firstLabel !== undefined ? (_metrics$firstLabel = metrics[firstLabel]) === null || _metrics$firstLabel === void 0 ? void 0 : _metrics$firstLabel[0] : undefined;
      return Object(external_CoreHome_["ucfirst"])((primary === null || primary === void 0 ? void 0 : primary.title) || (primary === null || primary === void 0 ? void 0 : primary.description), document.documentElement.lang);
    });
    return {
      metricTitle
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateComparison.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/DateComparison.vue



DateComparisonvue_type_script_lang_ts.render = DateComparisonvue_type_template_id_60a224a0_render

/* harmony default export */ var DateComparison = (DateComparisonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/sparklineDataAttrs.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The `data-graph-params` value for a sparkline's `.sparkline` wrapper, or null when none can be
 * derived. The legacy click-to-evolution wiring (window.initializeSparklines) reads it to open the
 * metric's evolution graph, so it must be set whenever the sparkline is linkable.
 *
 * Prefers the explicit backend `graphParams`; otherwise derives the reload params (columns/rows/
 * idGoal) from the url — the reused Sparkline renders the image with `src` (no `data-src`), so
 * sparkline.js can't read the columns off the img and we supply them here.
 *
 * Shared by SparklineCard and SegmentComparisonCard, where the whole card is one linkable
 * sparkline, so these attributes ride on the card root.
 */
function sparklineGraphParamsAttr(entry) {
  const {
    graphParams,
    url
  } = entry;
  if (graphParams && Object.keys(graphParams).length) {
    return JSON.stringify(graphParams);
  }
  if (url) {
    const parsed = external_CoreHome_["MatomoUrl"].parse(url.substring(url.indexOf('?') + 1));
    const derived = {};
    ['columns', 'rows', 'idGoal'].forEach(key => {
      if (parsed[key]) {
        derived[key] = parsed[key];
      }
    });
    if (Object.keys(derived).length) {
      return JSON.stringify(derived);
    }
  }
  return null;
}
/**
 * The `data-series-indices` value for a sparkline's `.sparkline` wrapper, or null when the entry
 * carries no series indices (no-comparison sparklines). Comparison entries set one index per series
 * so the evolution graph highlights the matching coloured line(s).
 */
function sparklineSeriesIndicesAttr(entry) {
  const {
    seriesIndices
  } = entry;
  return seriesIndices && seriesIndices.length ? JSON.stringify(seriesIndices) : null;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SparklineCard.vue?vue&type=script&lang=ts





/**
 * Card shell: the frame around a sparkline body. Owns the legacy `.sparkline` wrapper and
 * its evolution-graph data attributes; delegates the content to a body component. Comparison
 * entries carry seriesIndices (one per compared date), so they get the DateComparison body;
 * everything else gets the no-comparison body.
 */
/* harmony default export */ var SparklineCardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SparklineCard',
  components: {
    NoComparison: NoComparison,
    DateComparison: DateComparison,
    Sparkline: external_CoreHome_["Sparkline"]
  },
  props: {
    sparkline: {
      type: Object,
      required: true
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true
    },
    // Backend map of metric column -> documentation string, forwarded to the body component.
    allMetricsDocumentation: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    // Comparison entries set seriesIndices (one series per compared date); no-comparison entries
    // leave it null. This picks the card body — only two-date comparison reaches the Vue grid.
    const isComparison = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$sparkline$seri;
      return !!((_props$sparkline$seri = props.sparkline.seriesIndices) !== null && _props$sparkline$seri !== void 0 && _props$sparkline$seri.length);
    });
    // The legacy click-to-evolution wiring (window.initializeSparklines) reads these attributes off
    // the .sparkline wrapper. Shared with SegmentComparisonCard, which puts them on the card root.
    const graphParamsAttr = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => sparklineGraphParamsAttr(props.sparkline));
    const seriesIndicesAttr = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => sparklineSeriesIndicesAttr(props.sparkline));
    // Displayed sparkline width; comparison cards are wider so their sparkline is too. Kept in sync
    // with the .sparklineCard__sparkline max-width in the .less (Sparkline renders the PNG at 2x
    // this, and the CSS cap stops it scaling past that crisp source). Height stays 40 for both.
    const sparklineWidth = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => isComparison.value ? 760 : 380);
    return {
      isComparison,
      graphParamsAttr,
      seriesIndicesAttr,
      sparklineWidth
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SparklineCard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SparklineCard.vue



SparklineCardvue_type_script_lang_ts.render = SparklineCardvue_type_template_id_4308735a_render

/* harmony default export */ var SparklineCard = (SparklineCardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonCard.vue?vue&type=template&id=7f53d9a2

const SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_1 = ["data-graph-params", "data-series-indices"];
const SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_2 = ["title"];
const SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_3 = {
  class: "sparklineSegmentComparisonCard__rows"
};
function SegmentComparisonCardvue_type_template_id_7f53d9a2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentComparisonRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentComparisonRow");
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["sparkline sparklineSegmentComparisonCard", {
      notLinkable: !_ctx.areSparklinesLinkable
    }]),
    "data-graph-params": _ctx.graphParamsAttr,
    "data-series-indices": _ctx.seriesIndicesAttr
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["sparklineSegmentComparisonCard__title", {
      'sparklineSegmentComparisonCard__title--documented': !!_ctx.documentation
    }]),
    title: _ctx.documentation || _ctx.metricTitle
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricTitle), 1)], 10, SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_2)), [[_directive_tooltips, {
    duration: 200,
    delay: 200
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_3, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.segments, (segment, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SegmentComparisonRow, {
      key: index,
      segment: segment
    }, null, 8, ["segment"]);
  }), 128))])], 10, SegmentComparisonCardvue_type_template_id_7f53d9a2_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonCard.vue?vue&type=template&id=7f53d9a2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonRow.vue?vue&type=template&id=fd8e0dde

const SegmentComparisonRowvue_type_template_id_fd8e0dde_hoisted_1 = {
  class: "sparklineSegmentComparisonRow"
};
const SegmentComparisonRowvue_type_template_id_fd8e0dde_hoisted_2 = ["title"];
function SegmentComparisonRowvue_type_template_id_fd8e0dde_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_PeriodColumns = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PeriodColumns");
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentComparisonRowvue_type_template_id_fd8e0dde_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "sparklineSegmentComparisonRow__chip",
    title: _ctx.segmentLabel
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.segmentLabel), 9, SegmentComparisonRowvue_type_template_id_fd8e0dde_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PeriodColumns, {
    entry: _ctx.segment
  }, null, 8, ["entry"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["sparklineSegmentComparisonRow__sparkline", {
      'sparklineSegmentComparisonRow__sparkline--wide': _ctx.isMultiPeriod
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    width: _ctx.sparklineWidth,
    height: 40,
    params: _ctx.segment.url,
    "series-indices": _ctx.segment.seriesIndices
  }, null, 8, ["width", "params", "series-indices"])], 2)]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonRow.vue?vue&type=template&id=fd8e0dde

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonRow.vue?vue&type=script&lang=ts



/**
 * One compared segment inside a segment-comparison card: a presentational block with a segment-name
 * chip, one value column per compared date (a bare value for segment-only, or a labelled column
 * with an evolution badge per date for segment + date, rendered by the shared PeriodColumns), and
 * its own single- or multi-series sparkline. The row is not itself a link — the whole card is the
 * single `.sparkline` click-to-evolution unit (SegmentComparisonCard); every segment reloads the
 * same evolution graph.
 */
/* harmony default export */ var SegmentComparisonRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SegmentComparisonRow',
  components: {
    PeriodColumns: PeriodColumns,
    Sparkline: external_CoreHome_["Sparkline"]
  },
  props: {
    segment: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    // Segment name (compareSegmentPretty); always populated in segment comparison.
    const segmentLabel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => props.segment.title || '');
    // More than one compared date (segment + date) → widen the sparkline. The period columns
    // themselves are derived and rendered by PeriodColumns from the same entry.
    const isMultiPeriod = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => (props.segment.metricsOrder || []).length > 1);
    // Displayed sparkline width; segment + date rows draw one series per date so they are wider,
    // matching the date-comparison card. Kept in sync with the `--wide` max-width in the .less
    // (Sparkline renders the PNG at 2x this; the CSS cap stops it scaling past that crisp source).
    const sparklineWidth = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => isMultiPeriod.value ? 760 : 380);
    return {
      segmentLabel,
      isMultiPeriod,
      sparklineWidth
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonRow.vue



SegmentComparisonRowvue_type_script_lang_ts.render = SegmentComparisonRowvue_type_template_id_fd8e0dde_render

/* harmony default export */ var SegmentComparisonRow = (SegmentComparisonRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonCard.vue?vue&type=script&lang=ts




/**
 * Segment-comparison card: one card per metric, showing the metric name once and a stacked block
 * per compared segment (SegmentComparisonRow). The whole card is a single `.sparkline`
 * click-to-evolution link — every segment reloads the same evolution graph (same metric columns,
 * segments plotted as its series), so the card, not each row, is the clickable/hover unit. Rows are
 * presentational.
 */
/* harmony default export */ var SegmentComparisonCardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SegmentComparisonCard',
  directives: {
    Tooltips: external_CoreHome_["Tooltips"]
  },
  components: {
    SegmentComparisonRow: SegmentComparisonRow
  },
  props: {
    // One entry per compared segment for this metric (same metric, different segment).
    segments: {
      type: Array,
      required: true
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true
    },
    // Backend map of metric column -> documentation string, for the card-title tooltip.
    allMetricsDocumentation: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    // Same metric across segments; read its name + column from the first segment's first period
    // group. In segment-only comparison the column is populated so the doc tooltip resolves; in
    // segment + date it comes back empty (two periods, so Config::addSparkline can't map columns),
    // so no tooltip shows — correct parity with date comparison, not a regression.
    const primaryMetric = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _, _metrics$label;
      const first = props.segments[0];
      const metrics = (first === null || first === void 0 ? void 0 : first.metrics) || {};
      const label = (_ = ((first === null || first === void 0 ? void 0 : first.metricsOrder) || [])[0]) !== null && _ !== void 0 ? _ : Object.keys(metrics)[0];
      return label !== undefined ? (_metrics$label = metrics[label]) === null || _metrics$label === void 0 ? void 0 : _metrics$label[0] : undefined;
    });
    const metricTitle = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _primaryMetric$value, _primaryMetric$value2;
      const label = ((_primaryMetric$value = primaryMetric.value) === null || _primaryMetric$value === void 0 ? void 0 : _primaryMetric$value.title) || ((_primaryMetric$value2 = primaryMetric.value) === null || _primaryMetric$value2 === void 0 ? void 0 : _primaryMetric$value2.description);
      return Object(external_CoreHome_["ucfirst"])(label, document.documentElement.lang);
    });
    const documentation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _primaryMetric$value$, _primaryMetric$value3;
      return props.allMetricsDocumentation[(_primaryMetric$value$ = (_primaryMetric$value3 = primaryMetric.value) === null || _primaryMetric$value3 === void 0 ? void 0 : _primaryMetric$value3.column) !== null && _primaryMetric$value$ !== void 0 ? _primaryMetric$value$ : ''] || undefined;
    });
    // The whole card is one click-to-evolution link (window.initializeSparklines reads these off
    // the `.sparkline` root). Segments share the metric's columns, so derive reload params from the
    // first segment; series indices are the union of the card's segments.
    const graphParamsAttr = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const first = props.segments[0];
      return first ? sparklineGraphParamsAttr(first) : null;
    });
    const seriesIndicesAttr = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const indices = props.segments.flatMap(segment => segment.seriesIndices || []);
      return indices.length ? JSON.stringify(indices) : null;
    });
    return {
      metricTitle,
      documentation,
      graphParamsAttr,
      seriesIndicesAttr
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonCard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/Sparklines/SegmentComparisonCard.vue



SegmentComparisonCardvue_type_script_lang_ts.render = SegmentComparisonCardvue_type_template_id_7f53d9a2_render

/* harmony default export */ var SegmentComparisonCard = (SegmentComparisonCardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SparklinesGrid/SparklinesGrid.vue?vue&type=script&lang=ts



/* harmony default export */ var SparklinesGridvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SparklinesGrid',
  components: {
    SparklineCard: SparklineCard,
    SegmentComparisonCard: SegmentComparisonCard
  },
  props: {
    sparklines: {
      type: Object,
      required: true
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true
    },
    // Backend map of metric column -> documentation string, forwarded to each card for the
    // metric-title tooltip.
    allMetricsDocumentation: {
      type: Object,
      default: () => ({})
    },
    isWidget: {
      type: Boolean,
      default: false
    },
    // Comparison layout from the backend: 'none', 'date', 'segment' or 'segmentDate'. Date and
    // segment+date cards are wider (value columns + a full-width sparkline) so use a lower density;
    // segment / segment+date group a metric's per-segment entries into one taller card.
    comparisonMode: {
      type: String,
      default: 'none'
    }
  },
  setup(props) {
    // Both segment modes render one SegmentComparisonCard per metric group (a row per segment);
    // they differ only in how many date columns each row shows and in card width (isWideLayout).
    const isSegmentMode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => props.comparisonMode === 'segment' || props.comparisonMode === 'segmentDate');
    // `order` is the backend's source of truth for display order: a total order across
    // all cards (even comparison metrics/segments). Flatten every group and sort by it.
    // Drop placeholders (Config::addPlaceholder()): no url, they only padded the legacy
    // 2-column layout and would render as empty cards here. Used by 'none' and 'date'.
    const flatSparklines = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => [].concat(...Object.values(props.sparklines || {})).filter(sparkline => !!sparkline.url).sort((a, b) => a.order - b.order));
    // Segment (and segment + date) comparison emits one entry per (metric x segment), grouped by
    // metric in `sparklines`. One card per group (stacking per-segment rows); drop placeholders
    // (no url) and order groups by their lowest entry `order`.
    const segmentGroups = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object.values(props.sparklines || {}).map(group => group.filter(sparkline => !!sparkline.url)).filter(group => group.length > 0).sort((a, b) => Math.min(...a.map(s => s.order)) - Math.min(...b.map(s => s.order))));
    // date / segment+date cards are wider (value columns + a full-width sparkline), so the grid
    // gives them a lower density. (isSegmentMode = segment || segmentDate is a different split.)
    const isWideLayout = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => props.comparisonMode === 'date' || props.comparisonMode === 'segmentDate');
    // Container classes drive the CSS grid (see the .less). Every layout reflows via auto-fill.
    // Standard cards use --compact (a denser 200px minimum) in a widget; the wide comparison
    // layouts (date / segment+date) use --wide (350px) on reporting pages and widgets alike.
    // --framed layers the widget frame (tighter gutter, even padding) over the column modifier.
    const gridClasses = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => ({
      sparklinesGrid: true,
      'sparklinesGrid--wide': isWideLayout.value,
      'sparklinesGrid--framed': props.isWidget,
      'sparklinesGrid--compact': props.isWidget && !isWideLayout.value
    }));
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      // Wire each sparkline to its evolution graph once the cards are in the DOM (per-segment row
      // in segment mode, per card otherwise). Safe to re-run (it unbinds first); CoreHome's
      // sparkline.js is in the global JS bundle.
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
        window.initializeSparklines();
      });
    });
    return {
      isSegmentMode,
      flatSparklines,
      segmentGroups,
      gridClasses
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SparklinesGrid/SparklinesGrid.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SparklinesGrid/SparklinesGrid.vue



SparklinesGridvue_type_script_lang_ts.render = SparklinesGridvue_type_template_id_15709986_render

/* harmony default export */ var SparklinesGrid = (SparklinesGridvue_type_script_lang_ts);
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