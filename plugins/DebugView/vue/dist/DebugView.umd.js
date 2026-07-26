(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["DebugView"] = factory(require("CoreHome"), require("vue"));
	else
		root["DebugView"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/DebugView/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "DebugViewPage", function() { return /* reexport */ DebugViewPage; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.vue?vue&type=template&id=1f678280

const _hoisted_1 = {
  class: "debugViewIntro"
};
const _hoisted_2 = {
  class: "debugViewTopBar"
};
const _hoisted_3 = {
  class: "debugViewLiveStatus"
};
const _hoisted_4 = {
  class: "debugViewLiveText"
};
const _hoisted_5 = ["aria-label"];
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = {
  key: 0,
  class: "debugViewErrorDetails"
};
const _hoisted_8 = {
  key: 1,
  class: "debugViewLayout"
};
const _hoisted_9 = {
  class: "debugViewStreamColumn"
};
const _hoisted_10 = {
  key: 0,
  class: "debugViewEmptyState"
};
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search debugViewEmptyIcon",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_12 = {
  class: "debugViewEmptyHeadline"
};
const _hoisted_13 = {
  class: "debugViewEmptyHint"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_MinutesRail = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MinutesRail");
  const _component_HitsStream = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("HitsStream");
  const _component_HitDetailsPane = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("HitDetailsPane");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "debugViewPage",
    ref: "root",
    onKeydown: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])((...args) => _ctx.onPageEscape && _ctx.onPageEscape(...args), ["esc"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('DebugView_DebugView')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_PageDescription')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewLiveDot", {
        'debugViewLiveDot--paused': _ctx.paused
      }]),
      "aria-hidden": "true"
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.liveStatusText), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "debugViewPauseButton",
      "aria-label": _ctx.paused ? _ctx.translate('DebugView_Resume') : _ctx.translate('DebugView_Pause'),
      onClick: _cache[0] || (_cache[0] = $event => _ctx.togglePaused())
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.paused ? 'icon-play' : 'icon-pause'),
      "aria-hidden": "true"
    }, null, 2)], 8, _hoisted_5)])]), _ctx.pollingError !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
      key: 0,
      severity: "warning"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_PollingErrorTitle')), 1), _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_PollingErrorMessage')) + " ", 1), _ctx.pollingError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pollingError), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isInitialLoading
    }, null, 8, ["loading"]), !_ctx.isInitialLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MinutesRail, {
      buckets: _ctx.minuteBuckets,
      "selected-minute": _ctx.selectedMinute,
      "pending-count": _ctx.buffer.length,
      paused: _ctx.paused,
      onSelectMinute: _ctx.onSelectMinute
    }, null, 8, ["buckets", "selected-minute", "pending-count", "paused", "onSelectMinute"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [!_ctx.hits.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_10, [_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewLiveDot", {
        'debugViewLiveDot--paused': _ctx.paused
      }]),
      "aria-hidden": "true"
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_WaitingForRequests')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_WaitingForRequestsHint')), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_HitsStream, {
      key: 1,
      ref: "stream",
      hits: _ctx.hits,
      "selected-hit-id": _ctx.selectedHit ? _ctx.selectedHit.idRawRequest : null,
      onOpenHit: _ctx.onOpenHit
    }, null, 8, ["hits", "selected-hit-id", "onOpenHit"]))]), _ctx.selectedHit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_HitDetailsPane, {
      key: 0,
      hit: _ctx.selectedHit,
      "id-site": _ctx.idSite,
      onClose: _ctx.onCloseDetails
    }, null, 8, ["hit", "id-site", "onClose"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["content-title"])], 544);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.vue?vue&type=template&id=1f678280

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/MinutesRail/MinutesRail.vue?vue&type=template&id=46ff404e

const MinutesRailvue_type_template_id_46ff404e_hoisted_1 = {
  class: "debugViewMinutesRail"
};
const MinutesRailvue_type_template_id_46ff404e_hoisted_2 = ["aria-label"];
const MinutesRailvue_type_template_id_46ff404e_hoisted_3 = ["disabled", "aria-label", "onClick"];
const MinutesRailvue_type_template_id_46ff404e_hoisted_4 = {
  key: 0,
  class: "debugViewMinuteCount"
};
const MinutesRailvue_type_template_id_46ff404e_hoisted_5 = {
  key: 0,
  class: "debugViewMinuteLabel",
  "aria-hidden": "true"
};
function MinutesRailvue_type_template_id_46ff404e_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MinutesRailvue_type_template_id_46ff404e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewPendingBadge", {
      'debugViewPendingBadge--paused': _ctx.paused
    }]),
    "aria-live": "polite"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_NewHitsSincePaused', `${_ctx.pendingCount}`)), 3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", {
    class: "debugViewMinutesList",
    "aria-label": _ctx.translate('DebugView_MinutesTimeline')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.buckets, (bucket, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: bucket.minuteStart,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewMinuteItem", {
        'debugViewMinuteItem--has-hits': bucket.count > 0
      }])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewMinuteDot", {
        'debugViewMinuteDot--has-hits': bucket.count > 0,
        'debugViewMinuteDot--current': index === 0,
        'debugViewMinuteDot--selected': bucket.minuteStart === _ctx.selectedMinute
      }]),
      disabled: bucket.count === 0,
      "aria-label": _ctx.getDotLabel(bucket, index),
      onClick: $event => _ctx.$emit('selectMinute', bucket.minuteStart)
    }, [bucket.count > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", MinutesRailvue_type_template_id_46ff404e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(bucket.count), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, MinutesRailvue_type_template_id_46ff404e_hoisted_3), bucket.showLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", MinutesRailvue_type_template_id_46ff404e_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(bucket.label), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
  }), 128))], 8, MinutesRailvue_type_template_id_46ff404e_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/MinutesRail/MinutesRail.vue?vue&type=template&id=46ff404e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/MinutesRail/MinutesRail.vue?vue&type=script&lang=ts


/* harmony default export */ var MinutesRailvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    buckets: {
      type: Array,
      required: true
    },
    selectedMinute: {
      type: Number,
      default: null
    },
    pendingCount: {
      type: Number,
      default: 0
    },
    paused: Boolean
  },
  emits: ['selectMinute'],
  methods: {
    getDotLabel(bucket, index) {
      const parts = [];
      if (bucket.count > 1) {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_HitsInMinute', `${bucket.count}`, bucket.label));
      } else if (bucket.count === 1) {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_OneHitInMinute', bucket.label));
      } else {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_NoHitsInMinute', bucket.label));
      }
      if (index === 0) {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_CurrentMinute'));
      }
      if (bucket.minuteStart === this.selectedMinute) {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_SelectedMinute'));
      }
      if (bucket.count > 0) {
        parts.push(Object(external_CoreHome_["translate"])('DebugView_JumpToMinute', bucket.label));
      }
      return parts.join('. ');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/MinutesRail/MinutesRail.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/MinutesRail/MinutesRail.vue



MinutesRailvue_type_script_lang_ts.render = MinutesRailvue_type_template_id_46ff404e_render

/* harmony default export */ var MinutesRail = (MinutesRailvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitsStream/HitsStream.vue?vue&type=template&id=52d79c7d

const HitsStreamvue_type_template_id_52d79c7d_hoisted_1 = {
  class: "debugViewStream",
  ref: "root"
};
const HitsStreamvue_type_template_id_52d79c7d_hoisted_2 = {
  key: 0,
  class: "debugViewHitGap",
  "aria-hidden": "true"
};
const HitsStreamvue_type_template_id_52d79c7d_hoisted_3 = {
  class: "debugViewHitGapLabel"
};
function HitsStreamvue_type_template_id_52d79c7d_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_HitRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("HitRow");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", HitsStreamvue_type_template_id_52d79c7d_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["TransitionGroup"], {
    tag: "ol",
    name: "debugViewHitAnim",
    class: "debugViewStreamList",
    "aria-label": _ctx.translate('DebugView_SecondsStream')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.items, item => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: item.hit.idRawRequest,
        class: "debugViewStreamItem"
      }, [item.gapLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", HitsStreamvue_type_template_id_52d79c7d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", HitsStreamvue_type_template_id_52d79c7d_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.gapLabel), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_HitRow, {
        hit: item.hit,
        "is-selected": item.hit.idRawRequest === _ctx.selectedHitId,
        onOpen: $event => _ctx.$emit('openHit', item.hit)
      }, null, 8, ["hit", "is-selected", "onOpen"])]);
    }), 128))]),
    _: 1
  }, 8, ["aria-label"])], 512);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitsStream.vue?vue&type=template&id=52d79c7d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitsStream/HitRow.vue?vue&type=template&id=1260ccf9

const HitRowvue_type_template_id_1260ccf9_hoisted_1 = ["data-hit-id", "aria-label", "title"];
const HitRowvue_type_template_id_1260ccf9_hoisted_2 = ["src"];
const HitRowvue_type_template_id_1260ccf9_hoisted_3 = {
  class: "debugViewHitTitle"
};
const HitRowvue_type_template_id_1260ccf9_hoisted_4 = ["title"];
const HitRowvue_type_template_id_1260ccf9_hoisted_5 = {
  class: "debugViewHitTime"
};
function HitRowvue_type_template_id_1260ccf9_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    type: "button",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewHitRow", {
      'debugViewHitRow--selected': _ctx.isSelected
    }]),
    "data-hit-id": _ctx.hit.idRawRequest,
    "aria-label": _ctx.ariaLabel,
    "aria-haspopup": "dialog",
    title: _ctx.hit.subtitle || undefined,
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('open'))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewHitIconCircle", _ctx.typeInfo.cssClass]),
    "aria-hidden": "true"
  }, [_ctx.typeInfo.iconSvg ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    key: 0,
    src: _ctx.typeInfo.iconSvg,
    alt: ""
  }, null, 8, HitRowvue_type_template_id_1260ccf9_hoisted_2)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 1,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.typeInfo.icon)
  }, null, 2))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", HitRowvue_type_template_id_1260ccf9_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.hit.title), 1), _ctx.hit.isBot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "debugViewBotBadge",
    title: _ctx.hit.botName || undefined
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_BotBadge')), 9, HitRowvue_type_template_id_1260ccf9_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", HitRowvue_type_template_id_1260ccf9_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.hit.timePretty), 1)], 10, HitRowvue_type_template_id_1260ccf9_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitRow.vue?vue&type=template&id=1260ccf9

// CONCATENATED MODULE: ./plugins/DebugView/vue/src/hitTypes.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
// the exact image assets the visits log shows for each action kind (see
// plugins/Actions/VisitorDetails.php and the other plugins' VisitorDetails),
// hardcoded on purpose so no API is needed. Types without a visits log
// counterpart (ping, session recordings, unknown) keep an icon-font class
// verified to exist in plugins/Morpheus/stylesheets/base/icons.css.
const HIT_TYPES = {
  pageview: {
    iconSvg: 'plugins/Morpheus/images/action.svg',
    labelKey: 'DebugView_TypePageview'
  },
  event: {
    iconSvg: 'plugins/Morpheus/images/event.svg',
    labelKey: 'DebugView_TypeEvent'
  },
  goal: {
    iconSvg: 'plugins/Morpheus/images/goal.svg',
    labelKey: 'DebugView_TypeGoal'
  },
  download: {
    iconSvg: 'plugins/Morpheus/images/download.svg',
    labelKey: 'DebugView_TypeDownload'
  },
  outlink: {
    iconSvg: 'plugins/Morpheus/images/link.svg',
    labelKey: 'DebugView_TypeOutlink'
  },
  search: {
    iconSvg: 'plugins/Morpheus/images/search.svg',
    labelKey: 'DebugView_TypeSearch'
  },
  ecommerceOrder: {
    iconSvg: 'plugins/Morpheus/images/ecommerceOrder.svg',
    labelKey: 'DebugView_TypeEcommerceOrder'
  },
  ecommerceAbandonedCart: {
    iconSvg: 'plugins/Morpheus/images/ecommerceAbandonedCart.svg',
    labelKey: 'DebugView_TypeEcommerceAbandonedCart'
  },
  content: {
    iconSvg: 'plugins/Morpheus/images/contentinteraction.svg',
    labelKey: 'DebugView_TypeContent'
  },
  ping: {
    icon: 'icon-heart',
    labelKey: 'DebugView_TypePing'
  },
  media: {
    iconSvg: 'plugins/MediaAnalytics/images/video.png',
    labelKey: 'DebugView_TypeMedia'
  },
  form: {
    iconSvg: 'plugins/FormAnalytics/images/form.png',
    labelKey: 'DebugView_TypeForm'
  },
  crash: {
    iconSvg: 'plugins/CrashAnalytics/images/crash.png',
    labelKey: 'DebugView_TypeCrash'
  },
  // the visits log links session recordings with the play icon
  sessionRecording: {
    icon: 'icon-play',
    labelKey: 'DebugView_TypeSessionRecording'
  },
  other: {
    icon: 'icon-help',
    labelKey: 'DebugView_TypeVendor'
  }
};
function getHitTypeInfo(type, trackingParams) {
  const knownType = Object.prototype.hasOwnProperty.call(HIT_TYPES, type) ? type : 'other';
  const entry = HIT_TYPES[knownType];
  let iconSvg = entry.iconSvg || null;
  if (knownType === 'media' && trackingParams && trackingParams.ma_mt === 'audio') {
    // like the visits log: audio plays get the audio icon, everything else video
    iconSvg = 'plugins/MediaAnalytics/images/audio.png';
  }
  if (knownType === 'content' && trackingParams && !trackingParams.c_i) {
    // like the visits log: interactions and impressions have distinct icons —
    // a content request without an interaction name (c_i) is an impression
    iconSvg = 'plugins/Morpheus/images/contentimpression.svg';
  }
  return {
    icon: entry.icon || '',
    iconSvg,
    labelKey: entry.labelKey,
    cssClass: `debugViewHitIconCircle--${knownType}`
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitsStream/HitRow.vue?vue&type=script&lang=ts



/* harmony default export */ var HitRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hit: {
      type: Object,
      required: true
    },
    isSelected: Boolean
  },
  emits: ['open'],
  computed: {
    typeInfo() {
      return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
    },
    ariaLabel() {
      let typeLabel = Object(external_CoreHome_["translate"])(this.typeInfo.labelKey);
      if (this.hit.isBot) {
        typeLabel = `${typeLabel} (${this.hit.botName || Object(external_CoreHome_["translate"])('DebugView_BotBadge')})`;
      }
      return `${typeLabel}: ${this.hit.title}, ${this.hit.timePretty}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitRow.vue



HitRowvue_type_script_lang_ts.render = HitRowvue_type_template_id_1260ccf9_render

/* harmony default export */ var HitRow = (HitRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitsStream/HitsStream.vue?vue&type=script&lang=ts



/* harmony default export */ var HitsStreamvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hits: {
      type: Array,
      required: true
    },
    selectedHitId: {
      type: String,
      default: null
    }
  },
  components: {
    HitRow: HitRow
  },
  emits: ['openHit'],
  computed: {
    // hits are ordered newest first; each item carries the elapsed-time gap to
    // the next newer hit shown directly above it
    items() {
      return this.hits.map((hit, index) => {
        let gapLabel = null;
        if (index > 0) {
          const gap = this.hits[index - 1].timestamp - hit.timestamp;
          if (gap > 0 && gap < 60) {
            gapLabel = Object(external_CoreHome_["translate"])('DebugView_SecondsAgoShort', `${gap}`);
          } else if (gap >= 60) {
            gapLabel = Object(external_CoreHome_["translate"])('DebugView_MinutesAgoShort', `${Math.floor(gap / 60)}`);
          }
        }
        return {
          hit,
          gapLabel
        };
      });
    }
  },
  methods: {
    findRowElement(hitId) {
      const root = this.$refs.root;
      if (!root) {
        return null;
      }
      const rows = root.querySelectorAll('.debugViewHitRow');
      for (let i = 0; i < rows.length; i += 1) {
        if (rows[i].getAttribute('data-hit-id') === hitId) {
          return rows[i];
        }
      }
      return null;
    },
    focusHit(hitId) {
      const row = this.findRowElement(hitId);
      if (row) {
        row.focus();
      }
    },
    scrollToMinute(minuteStart) {
      // hits are sorted newest first, so the first match is the topmost row of
      // the clicked minute's block
      const hit = this.hits.find(candidate => candidate.timestamp >= minuteStart && candidate.timestamp < minuteStart + 60);
      if (!hit) {
        return;
      }
      const row = this.findRowElement(hit.idRawRequest);
      if (row && typeof row.scrollIntoView === 'function') {
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        row.scrollIntoView({
          behavior: reducedMotion ? 'auto' : 'smooth',
          block: 'start'
        });
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitsStream.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitsStream/HitsStream.vue



HitsStreamvue_type_script_lang_ts.render = HitsStreamvue_type_template_id_52d79c7d_render

/* harmony default export */ var HitsStream = (HitsStreamvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.vue?vue&type=template&id=9ea3fcd0

const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_1 = ["aria-label"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_2 = {
  class: "debugViewDetailsHeader"
};
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_3 = ["src"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_4 = ["title"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_5 = ["title"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_6 = ["aria-label"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-close",
  "aria-hidden": "true"
}, null, -1);
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_8 = [HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_7];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_9 = ["aria-selected", "tabindex"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_10 = ["aria-selected", "tabindex"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_11 = ["aria-labelledby"];
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_12 = {
  class: "debugViewDetailsSection"
};
const HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_13 = {
  class: "debugViewDetailsSection"
};
const _hoisted_14 = {
  class: "debugViewDetailsSection"
};
const _hoisted_15 = {
  class: "debugViewDetailsSection"
};
const _hoisted_16 = {
  class: "debugViewVisitUnavailable"
};
const _hoisted_17 = {
  key: 1,
  class: "debugViewLazyLoading"
};
const _hoisted_18 = {
  class: "debugViewDetailsSection"
};
const _hoisted_19 = {
  class: "debugViewVisitUnavailable"
};
const _hoisted_20 = {
  class: "debugViewDetailsSection"
};
const _hoisted_21 = {
  key: 0,
  class: "debugViewProcessedHint"
};
const _hoisted_22 = {
  class: "debugViewDetailsSection"
};
const _hoisted_23 = {
  key: 3,
  class: "debugViewVisitUnavailable"
};
function HitDetailsPanevue_type_template_id_9ea3fcd0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DetailRows = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DetailRows");
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    ref: "pane",
    class: "debugViewDetailsPane",
    role: "dialog",
    "aria-label": _ctx.paneLabel,
    onKeydown: [_cache[5] || (_cache[5] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('close'), ["prevent"]), ["esc"])), _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])((...args) => _ctx.onTabKey && _ctx.onTabKey(...args), ["tab"]))]
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewHitIconCircle", _ctx.typeInfo.cssClass]),
    "aria-hidden": "true"
  }, [_ctx.typeInfo.iconSvg ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    key: 0,
    src: _ctx.typeInfo.iconSvg,
    alt: ""
  }, null, 8, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_3)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 1,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.typeInfo.icon)
  }, null, 2))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
    class: "debugViewDetailsTitle",
    title: _ctx.hit.title
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.hit.title), 9, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_4), _ctx.hit.isBot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: "debugViewBotBadge",
    title: _ctx.hit.botName || undefined
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_BotBadge')), 9, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    ref: "closeButton",
    type: "button",
    class: "debugViewDetailsClose",
    "aria-label": _ctx.translate('DebugView_CloseDetails'),
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('close'))
  }, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_8, 8, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_6)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "debugViewDetailsTabs",
    role: "tablist",
    onKeydown: [_cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleTab(), ["prevent"]), ["left"])), _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleTab(), ["prevent"]), ["right"]))]
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    role: "tab",
    id: "debugViewTabParams",
    "aria-selected": _ctx.activeTab === 'params' ? 'true' : 'false',
    "aria-controls": "debugViewTabPanel",
    tabindex: _ctx.activeTab === 'params' ? 0 : -1,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewDetailsTab", {
      'debugViewDetailsTab--active': _ctx.activeTab === 'params'
    }]),
    onClick: _cache[1] || (_cache[1] = $event => _ctx.activeTab = 'params')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ParametersTab')), 11, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    role: "tab",
    id: "debugViewTabProcessed",
    "aria-selected": _ctx.activeTab === 'processed' ? 'true' : 'false',
    "aria-controls": "debugViewTabPanel",
    tabindex: _ctx.activeTab === 'processed' ? 0 : -1,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewDetailsTab", {
      'debugViewDetailsTab--active': _ctx.activeTab === 'processed'
    }]),
    onClick: _cache[2] || (_cache[2] = $event => _ctx.activeTab = 'processed')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedTab')), 11, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_10)], 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "debugViewDetailsBody",
    role: "tabpanel",
    id: "debugViewTabPanel",
    "aria-labelledby": _ctx.activeTab === 'params' ? 'debugViewTabParams' : 'debugViewTabProcessed'
  }, [_ctx.activeTab === 'params' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_ctx.hasTrackingParams ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_TrackingParameters')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DetailRows, {
    entries: _ctx.trackingParamsEntries,
    depth: 0
  }, null, 8, ["entries"])], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.hasDefaultParams ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_DefaultParameters')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DetailRows, {
    entries: _ctx.defaultParamsEntries,
    depth: 0
  }, null, 8, ["entries"])], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.hasOtherParams ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_OtherParameters')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DetailRows, {
    entries: _ctx.otherParamsEntries,
    depth: 0
  }, null, 8, ["entries"])], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.activeTab === 'processed' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.visitLoadState === 'bot' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", _hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedDetails')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedNotAvailableBot')), 1)], 64)) : _ctx.visitLoadState === 'loading' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader)])) : _ctx.visitLoadState === 'loaded' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [_ctx.hit.type === 'sessionRecording' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedDetails')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedCannotBeShown')), 1)], 64)) : _ctx.matchedActions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedDetails')), 1), _ctx.hit.type === 'media' || _ctx.hit.type === 'form' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedAggregatedHint')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.matchedActions, (action, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DetailRows, {
      key: index,
      entries: action,
      depth: 0
    }, null, 8, ["entries"]);
  }), 128))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_ProcessedVisitDetails')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DetailRows, {
    entries: _ctx.visitEntries,
    depth: 0
  }, null, 8, ["entries"])], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('DebugView_VisitNotAvailable')), 1))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_11)], 40, HitDetailsPanevue_type_template_id_9ea3fcd0_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.vue?vue&type=template&id=9ea3fcd0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitDetailsPane/DetailRows.vue?vue&type=template&id=7c26bbe2

const DetailRowsvue_type_template_id_7c26bbe2_hoisted_1 = {
  key: 0,
  class: "debugViewDetailNested"
};
const DetailRowsvue_type_template_id_7c26bbe2_hoisted_2 = {
  class: "debugViewDetailKey"
};
const DetailRowsvue_type_template_id_7c26bbe2_hoisted_3 = ["aria-expanded", "title", "onClick"];
const DetailRowsvue_type_template_id_7c26bbe2_hoisted_4 = {
  class: "debugViewDetailKey"
};
const DetailRowsvue_type_template_id_7c26bbe2_hoisted_5 = {
  class: "debugViewDetailValue"
};
function DetailRowsvue_type_template_id_7c26bbe2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DetailRows = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DetailRows", true);
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewDetailRows", {
      'debugViewDetailRows--nested': _ctx.depth > 0
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.visibleEntries, entry => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: entry.key,
      class: "debugViewDetailRow"
    }, [entry.children ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DetailRowsvue_type_template_id_7c26bbe2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", DetailRowsvue_type_template_id_7c26bbe2_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.key), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DetailRows, {
      entries: entry.children,
      depth: _ctx.depth + 1
    }, null, 8, ["entries", "depth"])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 1,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["debugViewDetailValueRow", {
        'debugViewDetailValueRow--expanded': _ctx.isExpanded(entry.key)
      }]),
      "aria-expanded": _ctx.isExpanded(entry.key) ? 'true' : 'false',
      title: entry.text,
      onClick: $event => _ctx.toggle(entry.key)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", DetailRowsvue_type_template_id_7c26bbe2_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.key), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", DetailRowsvue_type_template_id_7c26bbe2_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.text), 1)], 10, DetailRowsvue_type_template_id_7c26bbe2_hoisted_3))]);
  }), 128))], 2);
}
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/DetailRows.vue?vue&type=template&id=7c26bbe2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitDetailsPane/DetailRows.vue?vue&type=script&lang=ts

const MAX_DEPTH = 4;
function isSkippedKey(key) {
  const lowerKey = key.toLowerCase();
  return lowerKey.endsWith('icon') || lowerKey.endsWith('iconsvg');
}
// booleans are kept: `false` is data ("all available details"), only truly
// empty values are dropped
function isSkippedValue(value) {
  return value === null || value === undefined || value === '';
}
function toText(value) {
  if (value !== null && typeof value === 'object') {
    try {
      return JSON.stringify(value);
    } catch (e) {
      return String(value);
    }
  }
  return String(value);
}
/* harmony default export */ var DetailRowsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'DetailRows',
  props: {
    entries: {
      type: Object,
      required: true
    },
    depth: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      expandedKeys: {}
    };
  },
  watch: {
    entries() {
      this.expandedKeys = {};
    }
  },
  computed: {
    visibleEntries() {
      const result = [];
      Object.keys(this.entries).forEach(key => {
        const value = this.entries[key];
        if (isSkippedValue(value) || isSkippedKey(key)) {
          return;
        }
        if (value && typeof value === 'object' && this.depth < MAX_DEPTH) {
          const children = value;
          if (Object.keys(children).length) {
            result.push({
              key,
              text: '',
              children
            });
          }
          return;
        }
        result.push({
          key,
          text: toText(value),
          children: null
        });
      });
      return result;
    }
  },
  methods: {
    toggle(key) {
      this.expandedKeys[key] = !this.expandedKeys[key];
    },
    isExpanded(key) {
      return !!this.expandedKeys[key];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/DetailRows.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/DetailRows.vue



DetailRowsvue_type_script_lang_ts.render = DetailRowsvue_type_template_id_7c26bbe2_render

/* harmony default export */ var DetailRows = (DetailRowsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.vue?vue&type=script&lang=ts




/* harmony default export */ var HitDetailsPanevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hit: {
      type: Object,
      required: true
    },
    idSite: {
      type: Number,
      required: true
    }
  },
  components: {
    DetailRows: DetailRows,
    MatomoLoader: external_CoreHome_["MatomoLoader"]
  },
  emits: ['close'],
  data() {
    return {
      activeTab: 'params',
      visitDetails: null,
      visitLoadState: 'loading'
    };
  },
  computed: {
    typeInfo() {
      return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
    },
    paneLabel() {
      return this.hit.title || Object(external_CoreHome_["translate"])('DebugView_HitDetails');
    },
    trackingParamsEntries() {
      return this.hit.trackingParams || {};
    },
    hasTrackingParams() {
      return !!this.hit.trackingParams && Object.keys(this.hit.trackingParams).length > 0;
    },
    defaultParamsEntries() {
      return this.hit.trackingParamsDefaults || {};
    },
    hasDefaultParams() {
      return !!this.hit.trackingParamsDefaults && Object.keys(this.hit.trackingParamsDefaults).length > 0;
    },
    otherParamsEntries() {
      return this.hit.trackingParamsOther || {};
    },
    hasOtherParams() {
      return !!this.hit.trackingParamsOther && Object.keys(this.hit.trackingParamsOther).length > 0;
    },
    // the processed Live actions belonging to this raw request. Core actions
    // are matched via the log_link_visit_action id (a pageview and the goal it
    // triggered share it); media, form and crash actions have no such row, so
    // they are matched via their own identifiers. Heatmap & Session Recording
    // requests are deliberately not matched (nothing sensible to show).
    matchedActions() {
      if (!this.visitDetails || this.hit.type === 'sessionRecording') {
        return [];
      }
      const rawActions = this.visitDetails.actionDetails;
      if (!Array.isArray(rawActions)) {
        return [];
      }
      const actions = rawActions;
      const params = this.hit.trackingParams || {};
      switch (this.hit.type) {
        case 'media':
          {
            // the media resource (the request's ma_re) is exposed as the media
            // action's url; the view id does not survive Live's pipeline
            const resource = params.ma_re;
            if (!resource) {
              return [];
            }
            return actions.filter(action => action.type === 'media' && String(action.url) === String(resource));
          }
        case 'crash':
          {
            // no shared id exists; the error message (prefix, as stored values
            // are truncated) identifies the crash
            const cra = typeof params.cra === 'string' ? params.cra : '';
            if (!cra) {
              return [];
            }
            const prefix = cra.replace(/\.\.\.$/, '');
            return actions.filter(action => action.type === 'crash' && typeof action.message === 'string' && (action.message === cra || action.message.indexOf(prefix) === 0));
          }
        case 'form':
          {
            // form entries carry the pageview id and the numeric form id
            const pvId = params.pv_id;
            const faId = params.fa_id;
            if (pvId) {
              return actions.filter(action => action.type === 'form' && String(action.idpageview) === String(pvId));
            }
            if (faId) {
              return actions.filter(action => action.type === 'form' && String(action.formId) === String(faId));
            }
            return [];
          }
        default:
          {
            if (!this.hit.idLinkVa) {
              return [];
            }
            const idLinkVa = Number(this.hit.idLinkVa);
            return actions.filter(action => Number(action.pageId) === idLinkVa || Number(action.goalPageId) === idLinkVa);
          }
      }
    },
    visitEntries() {
      if (!this.visitDetails) {
        return {};
      }
      const entries = Object.assign({}, this.visitDetails);
      delete entries.actionDetails;
      return entries;
    }
  },
  watch: {
    'hit.idRawRequest': function onHitChange() {
      this.activeTab = 'params';
      this.loadVisit();
      this.$nextTick(() => this.focusCloseButton());
    }
  },
  mounted() {
    this.focusCloseButton();
    this.loadVisit();
  },
  methods: {
    // lazily loads the visit this hit belongs to, directly from the browser via
    // the visitId segment; the raw parameters render without waiting for it
    loadVisit() {
      this.visitDetails = null;
      // bot requests record no visit at all — nothing to load, the Processed
      // tab explains why instead
      if (this.hit.isBot) {
        this.visitLoadState = 'bot';
        return;
      }
      if (!this.hit.idVisit) {
        this.visitLoadState = 'unavailable';
        return;
      }
      this.visitLoadState = 'loading';
      const requestedHitId = this.hit.idRawRequest;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Live.getLastVisitsDetails',
        idSite: this.idSite,
        segment: `visitId==${this.hit.idVisit}`,
        filter_limit: 1,
        // pin the date window explicitly: AjaxHelper would otherwise inject
        // the page URL's current period/date (e.g. a stale date=yesterday),
        // scoping the lookup so the just-tracked visit is never found.
        // last2 (yesterday + today) always covers the <= 60 min stream
        // window, including around the site-timezone midnight
        period: 'range',
        date: 'last2'
      }, {
        createErrorNotification: false
      }).then(visits => {
        if (requestedHitId !== this.hit.idRawRequest) {
          return; // a different hit was opened meanwhile
        }
        const visit = Array.isArray(visits) && visits.length ? visits[0] : null;
        if (visit) {
          this.visitDetails = visit;
          this.visitLoadState = 'loaded';
        } else {
          this.visitLoadState = 'unavailable';
        }
      }).catch(() => {
        if (requestedHitId === this.hit.idRawRequest) {
          this.visitLoadState = 'unavailable';
        }
      });
    },
    focusCloseButton() {
      const closeButton = this.$refs.closeButton;
      if (closeButton) {
        closeButton.focus();
      }
    },
    toggleTab() {
      this.activeTab = this.activeTab === 'params' ? 'processed' : 'params';
      this.$nextTick(() => {
        const pane = this.$refs.pane;
        const active = pane && pane.querySelector('[role="tab"][aria-selected="true"]');
        if (active) {
          active.focus();
        }
      });
    },
    // below the responsive breakpoint the pane overlays the stream, so keep
    // focus inside it while it is open (the content behind it is obscured)
    onTabKey(event) {
      if (!window.matchMedia || !window.matchMedia('(max-width: 960px)').matches) {
        return;
      }
      const pane = this.$refs.pane;
      if (!pane) {
        return;
      }
      const focusable = Array.from(pane.querySelectorAll('button:not([tabindex="-1"]), [href], input, select, textarea, [tabindex="0"]')).filter(el => el.offsetParent !== null);
      if (!focusable.length) {
        return;
      }
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/HitDetailsPane/HitDetailsPane.vue



HitDetailsPanevue_type_script_lang_ts.render = HitDetailsPanevue_type_template_id_9ea3fcd0_render

/* harmony default export */ var HitDetailsPane = (HitDetailsPanevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/AutoRefreshController/AutoRefreshController.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const DEFAULT_INTERVAL_MS = 3000;
const DEFAULT_MAX_INTERVAL_MS = 300000;
class AutoRefreshController {
  constructor(options) {
    _defineProperty(this, "options", void 0);
    _defineProperty(this, "currentInterval", void 0);
    _defineProperty(this, "updateInterval", null);
    _defineProperty(this, "visibilityListenerId", null);
    this.options = options;
    this.currentInterval = this.resolveBaseInterval();
    this.setupVisibilityHandling();
  }
  getUpdatedResult(result) {
    if (typeof result === 'boolean') {
      return result;
    }
    return result.updated;
  }
  resolveBaseInterval() {
    if (this.options.getBaseInterval) {
      const interval = Number(this.options.getBaseInterval());
      if (Number.isFinite(interval) && interval > 0) {
        return interval;
      }
    }
    return DEFAULT_INTERVAL_MS;
  }
  resolveMaxInterval() {
    if (this.options.getMaxInterval) {
      const interval = Number(this.options.getMaxInterval());
      if (Number.isFinite(interval) && interval > 0) {
        return interval;
      }
    }
    return DEFAULT_MAX_INTERVAL_MS;
  }
  clearUpdate() {
    if (this.updateInterval) {
      window.clearTimeout(this.updateInterval);
      this.updateInterval = null;
    }
  }
  getVisibility() {
    const {
      Visibility: visibility
    } = window;
    if (!visibility || !visibility.isSupported || !visibility.isSupported()) {
      return null;
    }
    return visibility;
  }
  isTabHidden() {
    const visibility = this.getVisibility();
    return Boolean(visibility && visibility.hidden());
  }
  setupVisibilityHandling() {
    const visibility = this.getVisibility();
    if (!visibility) {
      return;
    }
    this.visibilityListenerId = visibility.change(() => {
      if (visibility.hidden()) {
        this.clearUpdate();
      } else if (this.options.shouldRun()) {
        this.update();
      }
    });
  }
  teardownVisibilityHandling() {
    const visibility = this.getVisibility();
    if (!visibility || typeof this.visibilityListenerId !== 'number') {
      return;
    }
    visibility.unbind(this.visibilityListenerId);
    this.visibilityListenerId = null;
  }
  schedule(delayMs) {
    const nextDelay = Number.isFinite(delayMs) && delayMs > 0 ? delayMs : this.resolveBaseInterval();
    this.clearUpdate();
    if (!this.options.shouldRun()) {
      return;
    }
    this.updateInterval = window.setTimeout(() => {
      this.update();
    }, nextDelay);
  }
  update() {
    if (!this.options.shouldRun()) {
      return;
    }
    if (this.isTabHidden()) {
      return;
    }
    this.options.request().then(response => Promise.resolve(this.options.handleResponse(response))).then(result => {
      const baseInterval = this.resolveBaseInterval();
      const isUpdated = this.getUpdatedResult(result);
      if (isUpdated) {
        this.currentInterval = baseInterval;
      } else {
        this.currentInterval += baseInterval;
      }
      if (this.currentInterval > this.resolveMaxInterval()) {
        this.currentInterval = this.resolveMaxInterval();
      }
      this.schedule(this.currentInterval);
    }).catch(error => {
      if (this.options.onError) {
        this.options.onError(error);
      }
      this.schedule(this.resolveBaseInterval());
    });
  }
  start() {
    this.currentInterval = 0;
    this.update();
  }
  stop() {
    this.clearUpdate();
  }
  destroy() {
    this.stop();
    this.teardownVisibilityHandling();
  }
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.vue?vue&type=script&lang=ts






const MAX_RENDERED_HITS = 500;
const RAIL_TICK_MS = 15000;
// hit ids are BIGINT UNSIGNED decimal strings from the API and may exceed
// Number's safe integer range — compare them without ever converting: longer
// decimal means bigger, equal length falls back to a lexical comparison
function compareDecimalIds(a, b) {
  const left = /^\d+$/.test(a) ? a.replace(/^0+(?=\d)/, '') : '0';
  const right = /^\d+$/.test(b) ? b.replace(/^0+(?=\d)/, '') : '0';
  if (left.length !== right.length) {
    return left.length - right.length;
  }
  if (left === right) {
    return 0;
  }
  return left < right ? -1 : 1;
}
/* harmony default export */ var DebugViewPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    idSite: {
      type: Number,
      required: true
    },
    refreshInterval: {
      type: Number,
      default: 5
    },
    lastMinutes: {
      type: Number,
      default: 30
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    HitDetailsPane: HitDetailsPane,
    HitsStream: HitsStream,
    MinutesRail: MinutesRail
  },
  data() {
    return {
      hits: [],
      buffer: [],
      paused: false,
      selectedHit: null,
      selectedMinute: null,
      isInitialLoading: true,
      pollingError: null,
      timezone: '',
      serverTimeOffset: 0,
      nowTick: Math.floor(Date.now() / 1000),
      // incremental polling cursor; a decimal string, never a Number (see
      // compareDecimalIds)
      lastId: '0',
      seenHits: new Map(),
      // minute-rail counts, tracked separately from `hits` so the rail stays
      // accurate even when the rendered stream is capped at MAX_RENDERED_HITS
      minuteCounts: new Map(),
      streamGeneration: 0,
      refreshController: null,
      railTimer: null,
      isUnmounted: false
    };
  },
  computed: {
    liveStatusText() {
      return this.paused ? Object(external_CoreHome_["translate"])('DebugView_StreamPaused') : Object(external_CoreHome_["translate"])('DebugView_StreamLive');
    },
    lastMinutesCount() {
      const minutes = Number(this.lastMinutes);
      return Number.isFinite(minutes) && minutes > 0 ? Math.floor(minutes) : 30;
    },
    // "now" in server time: browser now corrected by the clock offset tracked
    // from every API response
    serverNow() {
      return this.nowTick + this.serverTimeOffset;
    },
    minuteBuckets() {
      const formatter = this.createMinuteFormatter();
      const counts = this.minuteCounts;
      const nowMinuteStart = Math.floor(this.serverNow / 60) * 60;
      const buckets = [];
      for (let i = 0; i < this.lastMinutesCount; i += 1) {
        const minuteStart = nowMinuteStart - i * 60;
        const count = counts.get(minuteStart) || 0;
        buckets.push({
          minuteStart,
          count,
          label: formatter.format(new Date(minuteStart * 1000)),
          showLabel: i % 5 === 0 || count > 0
        });
      }
      return buckets;
    }
  },
  mounted() {
    this.initRefreshController();
    if (this.refreshController) {
      this.refreshController.start();
    }
    // slide the minute buckets forward even when no new hits arrive
    this.railTimer = window.setInterval(() => {
      this.nowTick = Math.floor(Date.now() / 1000);
      this.pruneOldHits();
    }, RAIL_TICK_MS);
  },
  beforeUnmount() {
    this.isUnmounted = true;
    if (this.railTimer) {
      window.clearInterval(this.railTimer);
      this.railTimer = null;
    }
    if (this.refreshController) {
      this.refreshController.destroy();
      this.refreshController = null;
    }
  },
  methods: {
    createMinuteFormatter() {
      const options = {
        hour: 'numeric',
        minute: '2-digit'
      };
      try {
        return new Intl.DateTimeFormat(undefined, Object.assign(Object.assign({}, options), {}, {
          timeZone: this.timezone || undefined
        }));
      } catch (e) {
        return new Intl.DateTimeFormat(undefined, options);
      }
    },
    initRefreshController() {
      this.refreshController = new AutoRefreshController({
        getBaseInterval: () => {
          const seconds = Number(this.refreshInterval);
          return Number.isFinite(seconds) && seconds > 0 ? seconds * 1000 : 5000;
        },
        shouldRun: () => {
          if (this.isUnmounted) {
            return false;
          }
          const root = this.$refs.root;
          return Boolean(root && root.isConnected);
        },
        request: () => {
          const generation = this.streamGeneration;
          return this.fetchHits().then(response => ({
            generation,
            response
          }), error => {
            const tagged = new Error('DebugView poll failed');
            tagged.generation = generation;
            tagged.error = error;
            throw tagged;
          });
        },
        handleResponse: tagged => this.processResponse(tagged),
        onError: error => this.handlePollingError(error)
      });
    },
    fetchHits() {
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'DebugView.getRecentHits',
        idSite: this.idSite,
        lastMinutes: this.lastMinutesCount,
        // ids are monotonic, so a strict id cursor can never lose late or
        // same-second hits — no overlap window needed
        minId: this.lastId
      }, {
        createErrorNotification: false
      });
    },
    processResponse(tagged) {
      if (tagged.generation !== this.streamGeneration) {
        // stale response from before a reset — discard entirely
        return {
          updated: false
        };
      }
      const {
        response
      } = tagged;
      this.isInitialLoading = false;
      this.pollingError = null;
      this.nowTick = Math.floor(Date.now() / 1000);
      if (response && Number.isFinite(response.serverTime)) {
        this.serverTimeOffset = response.serverTime - this.nowTick;
      }
      if (response && response.timezone) {
        this.timezone = response.timezone;
      }
      const incoming = (response && response.hits || []).filter(hit => !!hit && !!hit.idRawRequest && !this.seenHits.has(hit.idRawRequest));
      incoming.forEach(hit => {
        this.seenHits.set(hit.idRawRequest, hit.timestamp);
        if (compareDecimalIds(hit.idRawRequest, this.lastId) > 0) {
          this.lastId = hit.idRawRequest;
        }
      });
      if (incoming.length) {
        if (this.paused) {
          this.buffer = this.buffer.concat(incoming);
        } else {
          this.hits = this.sortNewestFirst(this.hits.concat(incoming));
          this.addToRailCounts(incoming);
        }
      }
      this.pruneOldHits();
      return {
        updated: incoming.length > 0
      };
    },
    handlePollingError(taggedError) {
      let inner = taggedError;
      if (taggedError && typeof taggedError === 'object' && 'generation' in taggedError) {
        const tagged = taggedError;
        if (tagged.generation !== this.streamGeneration) {
          // failure of a request from before a reset — ignore
          return;
        }
        inner = tagged.error;
      }
      this.isInitialLoading = false;
      let message = '';
      if (inner && typeof inner === 'object' && 'message' in inner) {
        const errorMessage = inner.message;
        if (typeof errorMessage === 'string') {
          message = errorMessage;
        }
      }
      this.pollingError = message;
    },
    addToRailCounts(hits) {
      hits.forEach(hit => {
        const minuteStart = Math.floor(hit.timestamp / 60) * 60;
        this.minuteCounts.set(minuteStart, (this.minuteCounts.get(minuteStart) || 0) + 1);
      });
    },
    sortNewestFirst(hits) {
      return hits.slice().sort((lhs, rhs) => {
        if (rhs.timestamp !== lhs.timestamp) {
          return rhs.timestamp - lhs.timestamp;
        }
        return compareDecimalIds(rhs.idRawRequest, lhs.idRawRequest);
      });
    },
    pruneOldHits() {
      const windowStart = this.serverNow - this.lastMinutesCount * 60;
      this.hits = this.hits.filter(hit => hit.timestamp >= windowStart).slice(0, MAX_RENDERED_HITS);
      if (this.buffer.length) {
        this.buffer = this.buffer.filter(hit => hit.timestamp >= windowStart);
      }
      this.seenHits.forEach((timestamp, id) => {
        if (timestamp < windowStart) {
          this.seenHits.delete(id);
        }
      });
      this.minuteCounts.forEach((count, minuteStart) => {
        if (minuteStart + 60 <= windowStart) {
          this.minuteCounts.delete(minuteStart);
        }
      });
    },
    togglePaused() {
      if (this.paused) {
        this.resumeStream();
      } else {
        this.paused = true;
      }
    },
    resumeStream() {
      if (this.buffer.length) {
        this.hits = this.sortNewestFirst(this.hits.concat(this.buffer));
        this.addToRailCounts(this.buffer);
        this.buffer = [];
        this.pruneOldHits();
      }
      this.paused = false;
    },
    onPageEscape() {
      if (this.selectedHit) {
        this.onCloseDetails();
      }
    },
    onSelectMinute(minuteStart) {
      this.selectedMinute = minuteStart;
      const stream = this.$refs.stream;
      if (stream) {
        stream.scrollToMinute(minuteStart);
      }
    },
    onOpenHit(hit) {
      this.selectedHit = hit;
    },
    onCloseDetails() {
      const closedHit = this.selectedHit;
      this.selectedHit = null;
      if (!closedHit) {
        return;
      }
      this.$nextTick(() => {
        const stream = this.$refs.stream;
        if (stream) {
          stream.focusHit(closedHit.idRawRequest);
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/DebugViewPage/DebugViewPage.vue



DebugViewPagevue_type_script_lang_ts.render = render

/* harmony default export */ var DebugViewPage = (DebugViewPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/DebugView/vue/src/index.ts
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
//# sourceMappingURL=DebugView.umd.js.map