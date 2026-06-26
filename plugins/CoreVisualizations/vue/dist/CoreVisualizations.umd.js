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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=template&id=bdedef58

const MetricValuevue_type_template_id_bdedef58_hoisted_1 = {
  class: "metricValue"
};
const MetricValuevue_type_template_id_bdedef58_hoisted_2 = ["title"];
const MetricValuevue_type_template_id_bdedef58_hoisted_3 = {
  class: "metricValue__primary"
};
const MetricValuevue_type_template_id_bdedef58_hoisted_4 = {
  class: "metricValue__number"
};
const MetricValuevue_type_template_id_bdedef58_hoisted_5 = {
  key: 0,
  class: "metricValue__secondary"
};
const MetricValuevue_type_template_id_bdedef58_hoisted_6 = {
  class: "metricValue__secondaryValue"
};
const MetricValuevue_type_template_id_bdedef58_hoisted_7 = {
  key: 0,
  class: "metricValue__secondaryLabel"
};
function MetricValuevue_type_template_id_bdedef58_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricValuevue_type_template_id_bdedef58_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["metricValue__title", {
      'metricValue__title--documented': !!_ctx.documentation
    }]),
    title: _ctx.documentation || null
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)], 10, MetricValuevue_type_template_id_bdedef58_hoisted_2)), [[_directive_tooltips, {
    duration: 200,
    delay: 200
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MetricValuevue_type_template_id_bdedef58_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricValuevue_type_template_id_bdedef58_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "evolution")]), _ctx.hasSecondary ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricValuevue_type_template_id_bdedef58_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MetricValuevue_type_template_id_bdedef58_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.secondaryValue), 1), _ctx.secondaryLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", MetricValuevue_type_template_id_bdedef58_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.secondaryLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=template&id=bdedef58

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=script&lang=ts


/* harmony default export */ var MetricValuevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'MetricValue',
  directives: {
    Tooltips: external_CoreHome_["Tooltips"]
  },
  props: {
    title: {
      type: String,
      required: true
    },
    // Pre-formatted value (e.g. "9,527" or "4min 22s"); rendered verbatim, no formatting here.
    value: {
      type: [String, Number],
      required: true
    },
    // Optional secondary line. Value and label are kept separate so they can be
    // styled independently (e.g. "9,527" darker, "unique visitors" grey). Matomo
    // hands these out separately as metric.value + metric.description.
    secondaryValue: [String, Number],
    secondaryLabel: String,
    // Optional documentation shown as a tooltip on the title.
    documentation: String
  },
  computed: {
    hasSecondary() {
      return this.secondaryValue !== undefined && this.secondaryValue !== null && this.secondaryValue !== '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/MetricValue/MetricValue.vue



MetricValuevue_type_script_lang_ts.render = MetricValuevue_type_template_id_bdedef58_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=21624034

const SingleMetricViewvue_type_template_id_21624034_hoisted_1 = {
  class: "metric-sparkline"
};
const SingleMetricViewvue_type_template_id_21624034_hoisted_2 = {
  class: "metric-value"
};
const SingleMetricViewvue_type_template_id_21624034_hoisted_3 = ["title"];
const SingleMetricViewvue_type_template_id_21624034_hoisted_4 = ["title"];
function SingleMetricViewvue_type_template_id_21624034_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["singleMetricView", {
      'loading': _ctx.isLoading
    }]),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_21624034_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
    params: _ctx.sparklineParams
  }, null, 8, ["params"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SingleMetricViewvue_type_template_id_21624034_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    title: _ctx.metricDocumentation
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricValue), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx.metricTranslation || '').toLowerCase()), 1)], 8, SingleMetricViewvue_type_template_id_21624034_hoisted_3), _ctx.pastValue !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "metricEvolution",
    title: _ctx.translate('General_EvolutionSummaryGeneric', _ctx.metricValue, _ctx.currentPeriod, _ctx.pastValue, _ctx.pastPeriod, _ctx.metricChangePercent)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.evolutionClass)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricChangePercent), 3)], 8, SingleMetricViewvue_type_template_id_21624034_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SingleMetricView/SingleMetricView.vue?vue&type=template&id=21624034

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



SingleMetricViewvue_type_script_lang_ts.render = SingleMetricViewvue_type_template_id_21624034_render

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