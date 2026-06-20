(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["UserCountryMap"] = factory(require("CoreHome"), require("vue"));
	else
		root["UserCountryMap"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/UserCountryMap/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "VisitorMapWidget", function() { return /* reexport */ VisitorMapWidget; });
__webpack_require__.d(__webpack_exports__, "RealtimeMapWidget", function() { return /* reexport */ RealtimeMapWidget; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountryMap/vue/src/VisitorMap/VisitorMapWidget.vue?vue&type=template&id=471095cf

const _hoisted_1 = {
  class: "card"
};
const _hoisted_2 = {
  class: "UserCountryMap card-content",
  style: {
    "position": "relative",
    "overflow": "hidden"
  }
};
const _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<div class=\"UserCountryMap_container\"><div class=\"UserCountryMap_map\" style=\"overflow:hidden;\"></div><div class=\"UserCountryMap-overlay UserCountryMap-title\"><div class=\"content\"><div class=\"map-stats\"></div></div></div><div class=\"UserCountryMap-overlay UserCountryMap-legend\"><div class=\"content\"></div></div><div class=\"UserCountryMap-tooltip UserCountryMap-info\"><div class=\"content unlocated-stats\"></div></div><div class=\"UserCountryMap-info-btn\" data-tooltip-target=\".UserCountryMap-tooltip\"></div></div>", 1);
const _hoisted_4 = {
  class: "mapWidgetStatus"
};
const _hoisted_5 = {
  key: 0
};
const _hoisted_6 = {
  class: "pk-emptyDataTable"
};
const _hoisted_7 = {
  key: 1,
  class: "loadingPiwik"
};
const _hoisted_8 = {
  key: 0,
  class: "dataTableFeatures"
};
const _hoisted_9 = {
  class: "dataTableFooterIcons"
};
const _hoisted_10 = {
  class: "dataTableFooterWrap",
  var: "graphVerticalBar"
};
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "UserCountryMap-activeItem dataTableFooterActiveItem",
  src: "plugins/Morpheus/images/data_table_footer_active_item.png",
  style: {
    "left": "25px"
  }
}, null, -1);
const _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "tableIconsGroup"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "tableAllColumnsSwitch"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  class: "UserCountryMap-btn-zoom tableIcon",
  format: "table"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/zoom-out.png",
  title: "Zoom to world"
})])])], -1);
const _hoisted_13 = {
  class: "tableIconsGroup UserCountryMap-view-mode-buttons"
};
const _hoisted_14 = {
  class: "tableAllColumnsSwitch"
};
const _hoisted_15 = ["data-region", "data-country"];
const _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/UserCountryMap/images/regions.png",
  title: "Show visitors per region/country"
}, null, -1);
const _hoisted_17 = {
  style: {
    "margin": "0"
  }
};
const _hoisted_18 = {
  class: "UserCountryMap-btn-city tableIcon inactiveIcon",
  style: {
    "display": "none"
  }
};
const _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/UserCountryMap/images/cities.png",
  title: "Show visitors per city"
}, null, -1);
const _hoisted_20 = {
  style: {
    "margin": "0"
  }
};
const _hoisted_21 = ["value", "selected"];
const _hoisted_22 = {
  class: "userCountryMapSelectCountry browser-default",
  style: {
    "height": "auto"
  }
};
const _hoisted_23 = {
  value: "world"
};
const _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", {
  disabled: "disabled"
}, " –––––– ", -1);
const _hoisted_25 = ["value"];
const _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", {
  disabled: "disabled"
}, " –––––– ", -1);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [_ctx.noData ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [!_ctx.isWidget ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_VisitorMap')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ThereIsNoDataForThisReport')), 1)], 64)) : _ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')) + "... ", 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), !_ctx.noData && !_ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [_hoisted_11, _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "UserCountryMap-btn-region tableIcon activeIcon",
    "data-region": _ctx.translate('UserCountryMap_Regions'),
    "data-country": _ctx.translate('UserCountryMap_Countries')
  }, [_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_Countries')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("  ")], 8, _hoisted_15), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_18, [_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_Cities')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("  ")])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "userCountryMapSelectMetrics browser-default",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.metricSelectStyle)
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metrics, metric => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: metric[0],
      value: metric[0],
      selected: metric[0] === _ctx.defaultMetric
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric[1]), 9, _hoisted_21);
  }), 128))], 4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_WorldWide')), 1), _hoisted_24, (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.continents, (name, code) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: code,
      value: code
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(name), 9, _hoisted_25);
  }), 128)), _hoisted_26])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/VisitorMap/VisitorMapWidget.vue?vue&type=template&id=471095cf

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountryMap/vue/src/VisitorMap/VisitorMapWidget.vue?vue&type=script&lang=ts


/* harmony default export */ var VisitorMapWidgetvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  props: {
    uniqueId: String,
    widgetName: String,
    widgetized: Boolean,
    isWidget: Boolean,
    isWide: Boolean
  },
  data() {
    return {
      loading: true,
      noData: false,
      metrics: [],
      defaultMetric: 'nb_visits',
      continents: {},
      metricSelectStyle: {
        float: 'right',
        marginRight: '25px',
        marginBottom: '10px',
        maxWidth: '10em',
        fontSize: '10px',
        height: 'auto'
      }
    };
  },
  mounted() {
    this.loadConfig();
  },
  beforeUnmount() {
    this.stopResizeObserver();
    if (window.visitorMap) {
      window.visitorMap.destroy();
      window.visitorMap = undefined;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    async loadConfig() {
      this.loading = true;
      this.noData = false;
      try {
        const config = await external_CoreHome_["AjaxHelper"].fetch({
          module: 'UserCountryMap',
          action: 'getVisitorMapConfig'
        });
        if (!config.visitsSummary || !config.visitsSummary.nb_visits) {
          this.noData = true;
          this.loading = false;
          return;
        }
        this.metrics = config.metrics;
        this.defaultMetric = config.defaultMetric;
        this.continents = config.continents;
        this.loading = false;
        await Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])();
        // Scope jQuery selectors to this component's DOM
        // so the legacy JS finds only elements within this
        // widget instance. The VisitorMap constructor uses
        // theWidget.element as the jQuery context.
        const scopeEl = this.$el;
        const theWidget = {
          element: scopeEl
        };
        window.visitorMap = new UserCountryMapLegacy.VisitorMap(config, theWidget);
        this.startResizeObserver();
      } catch (_unused) {
        this.noData = true;
        this.loading = false;
      }
    },
    startResizeObserver() {
      var _this$$el;
      const container = (_this$$el = this.$el) === null || _this$$el === void 0 ? void 0 : _this$$el.querySelector('.UserCountryMap_container');
      if (!container || !window.ResizeObserver) {
        return;
      }
      let lastWidth = container.clientWidth;
      let lastHeight = container.clientHeight;
      this.resizeObserver = new ResizeObserver(() => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        if (w !== lastWidth || h !== lastHeight) {
          lastWidth = w;
          lastHeight = h;
          if (window.visitorMap) {
            window.visitorMap.resize();
          }
        }
      });
      this.resizeObserver.observe(container);
    },
    stopResizeObserver() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
        this.resizeObserver = undefined;
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/VisitorMap/VisitorMapWidget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/VisitorMap/VisitorMapWidget.vue



VisitorMapWidgetvue_type_script_lang_ts.render = render

/* harmony default export */ var VisitorMapWidget = (VisitorMapWidgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountryMap/vue/src/RealtimeMap/RealtimeMapWidget.vue?vue&type=template&id=4680af44

const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_1 = {
  class: "card"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_2 = ["data-config", "data-standalone"];
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_3 = {
  class: "RealTimeMap_container"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "RealTimeMap_map",
  style: {
    "overflow": "hidden"
  }
}, null, -1);
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_5 = {
  class: "realTimeMap_overlay"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_6 = {
  key: 0,
  class: "showing_visits_of",
  style: {
    "display": "none"
  }
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "realTimeMap_timeSpan",
  style: {
    "font-weight": "bold"
  }
}, null, -1);
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_8 = {
  class: "no_data",
  style: {
    "display": "none"
  }
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_9 = {
  class: "loading_data"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/UserCountryMap/images/realtimemap-loading.gif",
  style: {
    "vertical-align": "baseline",
    "position": "relative",
    "left": "-2px"
  }
}, null, -1);
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_11 = {
  key: 0,
  class: "realTimeMap_overlay realTimeMap_datetime"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_12 = {
  class: "RealTimeMap_meta"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_13 = {
  key: 0,
  class: "loadingPiwik"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_14 = {
  key: 1,
  class: "pk-emptyDataTable"
};
const RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_15 = {
  key: 0,
  id: "realTimeMapNoVisitsInfo",
  class: "alert alert-info",
  style: {
    "display": "none",
    "margin-top": "20px",
    "margin-bottom": "0"
  }
};
function RealtimeMapWidgetvue_type_template_id_4680af44_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    ref: "mapRoot",
    class: "RealTimeMap card-content",
    style: {
      "position": "relative",
      "overflow": "hidden"
    },
    "data-config": _ctx.configJson,
    "data-standalone": _ctx.isStandalone ? 1 : 0,
    tabindex: "0"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_3, [RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_5, [_ctx.showFooterMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_ShowingVisits')) + " ", 1), RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_7])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ThereIsNoDataForThisReport')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')) + "... ", 1), RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_10]), _ctx.showDateTime ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_11)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_12, [!_ctx.loadFailed ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: true
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')) + "... ", 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ThereIsNoDataForThisReport')), 1))]), _ctx.hasSuperUserAccess ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_NoVisitsInfo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountryMap_NoVisitsInfo2')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, RealtimeMapWidgetvue_type_template_id_4680af44_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/RealtimeMap/RealtimeMapWidget.vue?vue&type=template&id=4680af44

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountryMap/vue/src/RealtimeMap/RealtimeMapWidget.vue?vue&type=script&lang=ts


/* harmony default export */ var RealtimeMapWidgetvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  props: {
    uniqueId: String,
    widgetName: String,
    widgetized: Boolean,
    isWidget: Boolean,
    isWide: Boolean
  },
  data() {
    return {
      configJson: '',
      showFooterMessage: true,
      showDateTime: true,
      loadFailed: false
    };
  },
  computed: {
    isStandalone() {
      return !this.widgetized && !this.isWidget;
    },
    hasSuperUserAccess() {
      return !!external_CoreHome_["Matomo"].hasSuperUserAccess;
    }
  },
  mounted() {
    this.loadConfig();
  },
  beforeUnmount() {
    this.stopResizeObserver();
    // UIControl instances register themselves; find and
    // destroy the one attached to our element
    const el = this.$refs.mapRoot;
    if (el && typeof $ === 'function') {
      const ctrl = $(el).data('uiControlObject');
      // eslint-disable-next-line no-underscore-dangle
      if (ctrl && typeof ctrl._destroy === 'function') {
        ctrl._destroy(); // eslint-disable-line no-underscore-dangle
      }
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    async loadConfig() {
      try {
        const config = await external_CoreHome_["AjaxHelper"].fetch({
          module: 'UserCountryMap',
          action: 'getRealtimeMapConfig'
        });
        this.showFooterMessage = !!config.showFooterMessage;
        this.showDateTime = !!config.showDateTime;
        this.configJson = JSON.stringify(config);
        await Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])();
        UserCountryMapLegacy.RealtimeMap.initElements();
        this.startResizeObserver();
      } catch (_unused) {
        this.loadFailed = true;
      }
    },
    startResizeObserver() {
      var _this$$refs$mapRoot;
      const container = (_this$$refs$mapRoot = this.$refs.mapRoot) === null || _this$$refs$mapRoot === void 0 ? void 0 : _this$$refs$mapRoot.querySelector('.RealTimeMap_container');
      if (!container || !window.ResizeObserver) {
        return;
      }
      let lastW = container.clientWidth;
      let lastH = container.clientHeight;
      this.resizeObserver = new ResizeObserver(() => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        if (w !== lastW || h !== lastH) {
          lastW = w;
          lastH = h;
          if (typeof $ === 'function') {
            const el = this.$refs.mapRoot;
            const ctrl = $(el).data('uiControlObject');
            if (ctrl && typeof ctrl.resize === 'function') {
              ctrl.resize();
            }
          }
        }
      });
      this.resizeObserver.observe(container);
    },
    stopResizeObserver() {
      if (this.resizeObserver) {
        this.resizeObserver.disconnect();
        this.resizeObserver = undefined;
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/RealtimeMap/RealtimeMapWidget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/RealtimeMap/RealtimeMapWidget.vue



RealtimeMapWidgetvue_type_script_lang_ts.render = RealtimeMapWidgetvue_type_template_id_4680af44_render

/* harmony default export */ var RealtimeMapWidget = (RealtimeMapWidgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/UserCountryMap/vue/src/index.ts
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=UserCountryMap.umd.js.map