(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Live"] = factory(require("CoreHome"), require("vue"));
	else
		root["Live"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Live/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "LiveWidgetRefresh", function() { return /* reexport */ LiveWidgetRefresh; });
__webpack_require__.d(__webpack_exports__, "TotalVisitors", function() { return /* reexport */ TotalVisitors; });
__webpack_require__.d(__webpack_exports__, "LivePage", function() { return /* reexport */ LivePage; });
__webpack_require__.d(__webpack_exports__, "IndexHeader", function() { return /* reexport */ IndexHeader; });
__webpack_require__.d(__webpack_exports__, "LastVisits", function() { return /* reexport */ LastVisits; });

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

// CONCATENATED MODULE: ./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const {
  $
} = window;
/* harmony default export */ var LiveWidgetRefresh = ({
  mounted(el, binding) {
    setTimeout(() => {
      const segment = external_CoreHome_["MatomoUrl"].parsed.value.segment;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      $(el).find('#visitsLive').liveWidget({
        interval: binding.value.liveRefreshAfterMs,
        onUpdate: () => {
          // updates the numbers of total visits in startbox
          external_CoreHome_["AjaxHelper"].fetch({
            module: 'Live',
            action: 'ajaxTotalVisitors',
            segment
          }, {
            format: 'html'
          }).then(r => {
            external_CoreHome_["Matomo"].helper.destroyVueComponent(el);
            $(el).find('#visitsTotal').replaceWith(r);
            external_CoreHome_["Matomo"].helper.compileVueEntryComponents(el);
          });
        },
        maxRows: 10,
        fadeInSpeed: 600,
        dataUrlParams: {
          module: 'Live',
          action: 'getLastVisitsStart',
          segment
        }
      });
    });
  }
});
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=template&id=e054d62c

const _hoisted_1 = {
  class: "dataTable",
  cellspacing: "0"
};
const _hoisted_2 = {
  id: "label",
  class: "sortable label first",
  style: {
    "cursor": "auto"
  }
};
const _hoisted_3 = {
  class: "thDIV"
};
const _hoisted_4 = {
  class: "sortable",
  style: {
    "cursor": "auto"
  }
};
const _hoisted_5 = {
  class: "thDIV"
};
const _hoisted_6 = {
  class: "sortable",
  style: {
    "cursor": "auto"
  }
};
const _hoisted_7 = {
  class: "thDIV"
};
const _hoisted_8 = {
  class: ""
};
const _hoisted_9 = {
  class: "label column"
};
const _hoisted_10 = ["title"];
const _hoisted_11 = ["title"];
const _hoisted_12 = {
  class: ""
};
const _hoisted_13 = {
  class: "label column"
};
const _hoisted_14 = ["title"];
const _hoisted_15 = ["title"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Date')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Actions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LastHours', 24)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorToday
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.visitorsCountToday || 0), 9, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorToday
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pisToday || 0), 9, _hoisted_11)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LastMinutes', 30)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorHalfHour
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.visitorsCountHalfHour || 0), 9, _hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorHalfHour
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pisHalfhour || 0), 9, _hoisted_15)])])])]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=template&id=e054d62c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=script&lang=ts

/* harmony default export */ var TotalVisitorsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    countErrorToday: Number,
    visitorsCountToday: Number,
    pisToday: Number,
    countErrorHalfHour: Number,
    visitorsCountHalfHour: Number,
    pisHalfhour: Number
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue



TotalVisitorsvue_type_script_lang_ts.render = render

/* harmony default export */ var TotalVisitors = (TotalVisitorsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=template&id=12df3591

const LivePagevue_type_template_id_12df3591_hoisted_1 = {
  class: "visitsLiveFooter"
};
const LivePagevue_type_template_id_12df3591_hoisted_2 = ["title"];
const LivePagevue_type_template_id_12df3591_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  id: "pauseImage",
  border: "0",
  src: "plugins/Live/images/pause.png"
}, null, -1);
const LivePagevue_type_template_id_12df3591_hoisted_4 = [LivePagevue_type_template_id_12df3591_hoisted_3];
const LivePagevue_type_template_id_12df3591_hoisted_5 = ["title"];
const LivePagevue_type_template_id_12df3591_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  id: "playImage",
  style: {
    "display": "none"
  },
  border: "0",
  src: "plugins/Live/images/play.png"
}, null, -1);
const LivePagevue_type_template_id_12df3591_hoisted_7 = [LivePagevue_type_template_id_12df3591_hoisted_6];
const LivePagevue_type_template_id_12df3591_hoisted_8 = {
  key: 0
};
const LivePagevue_type_template_id_12df3591_hoisted_9 = ["href"];
function LivePagevue_type_template_id_12df3591_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_VueEntryContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("VueEntryContainer");
  const _directive_live_widget_refresh = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("live-widget-refresh");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(!_ctx.isWidgetized ? 'ContentBlock' : 'Passthrough'), {
    "content-title": !_ctx.isWidgetized ? _ctx.translate('Live_VisitorsInRealTime') : undefined
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
      html: _ctx.initialTotalVisitors
    }, null, 8, ["html"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
      html: _ctx.visitors
    }, null, 8, ["html"])])), [[_directive_live_widget_refresh, {
      liveRefreshAfterMs: _ctx.liveRefreshAfterMs
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LivePagevue_type_template_id_12df3591_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('Live_OnClickPause', _ctx.translate('Live_VisitorsInRealTime')),
      onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onClickPause(), ["prevent"]))
    }, LivePagevue_type_template_id_12df3591_hoisted_4, 8, LivePagevue_type_template_id_12df3591_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('Live_OnClickStart', _ctx.translate('Live_VisitorsInRealTime')),
      onClick: _cache[1] || (_cache[1] = $event => {
        _ctx.onClickPlay();
      })
    }, LivePagevue_type_template_id_12df3591_hoisted_7, 8, LivePagevue_type_template_id_12df3591_hoisted_5), !_ctx.disableLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", LivePagevue_type_template_id_12df3591_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "rightLink",
      href: _ctx.visitorLogUrl
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LinkVisitorLog')), 9, LivePagevue_type_template_id_12df3591_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]),
    _: 1
  }, 8, ["content-title"]))]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=template&id=12df3591

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }




/* harmony default export */ var LivePagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    disableLink: Boolean,
    visitors: String,
    initialTotalVisitors: String,
    liveRefreshAfterMs: Number,
    isWidgetized: Boolean
  },
  components: {
    TotalVisitors: TotalVisitors,
    VueEntryContainer: external_CoreHome_["VueEntryContainer"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    Passthrough: external_CoreHome_["Passthrough"]
  },
  directives: {
    LiveWidgetRefresh: LiveWidgetRefresh
  },
  computed: {
    visitorLogUrl() {
      return `#?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        category: 'General_Visitors',
        subcategory: 'Live_VisitorLog'
      }))}`;
    }
  },
  methods: {
    onClickPause() {
      window.onClickPause();
    },
    onClickPlay() {
      window.onClickPlay();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue



LivePagevue_type_script_lang_ts.render = LivePagevue_type_template_id_12df3591_render

/* harmony default export */ var LivePage = (LivePagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=template&id=e270701e

function IndexHeadervue_type_template_id_e270701e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_VisitorLog')), 1)]),
    _: 1
  })])])), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=template&id=e270701e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=script&lang=ts


/* harmony default export */ var IndexHeadervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue



IndexHeadervue_type_script_lang_ts.render = IndexHeadervue_type_template_id_e270701e_render

/* harmony default export */ var IndexHeader = (IndexHeadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Live/vue/src/LastVisits/LastVisits.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $: LastVisits_$
} = window;
/* harmony default export */ var LastVisits = ({
  mounted(el) {
    LastVisits_$(el).off('click').on('click', '.visits-live-launch-visitor-profile', function onClickLaunchProfile(e) {
      e.preventDefault();
      window.broadcast.propagateNewPopoverParameter('visitorProfile', LastVisits_$(this).attr('data-visitor-id'));
      return false;
    }).tooltip({
      track: true,
      content() {
        const title = LastVisits_$(this).attr('title') || '';
        return window.vueSanitize(title.replace(/\n/g, '<br />'));
      },
      show: {
        delay: 100,
        duration: 0
      },
      hide: false
    });
  }
});
// CONCATENATED MODULE: ./plugins/Live/vue/src/index.ts
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
//# sourceMappingURL=Live.umd.js.map