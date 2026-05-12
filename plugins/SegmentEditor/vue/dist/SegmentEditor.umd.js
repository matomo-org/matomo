(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["SegmentEditor"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["SegmentEditor"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__) {
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
/******/ 	__webpack_require__.p = "plugins/SegmentEditor/vue/dist/";
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

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "SegmentGeneratorStore", function() { return /* reexport */ SegmentGenerator_store; });
__webpack_require__.d(__webpack_exports__, "SegmentGenerator", function() { return /* reexport */ SegmentGenerator; });
__webpack_require__.d(__webpack_exports__, "getCanUserEditSegment", function() { return /* reexport */ getCanUserEditSegment; });
__webpack_require__.d(__webpack_exports__, "getDeleteSegmentTitle", function() { return /* reexport */ getDeleteSegmentTitle; });
__webpack_require__.d(__webpack_exports__, "getEditSegmentTitle", function() { return /* reexport */ getEditSegmentTitle; });
__webpack_require__.d(__webpack_exports__, "getStarSegmentTitle", function() { return /* reexport */ getStarSegmentTitle; });
__webpack_require__.d(__webpack_exports__, "SegmentSelectorStore", function() { return /* reexport */ SegmentSelector_store; });
__webpack_require__.d(__webpack_exports__, "SegmentSelector", function() { return /* reexport */ SegmentSelector; });
__webpack_require__.d(__webpack_exports__, "CompareIcon", function() { return /* reexport */ CompareIcon; });
__webpack_require__.d(__webpack_exports__, "StarIcon", function() { return /* reexport */ StarIcon; });

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

// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.store.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class SegmentGenerator_store_SegmentGeneratorStore {
  constructor() {
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      segments: []
    }));
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
  }
  loadSegments(siteId, visitSegmentsOnly) {
    // Do not cache the in-flight promise. AjaxHelper silently swallows
    // aborts (when globalAjaxQueue.abort() fires during navigation), which
    // means a cached promise can stay pending forever and any subsequent
    // call returns that stuck promise so queriedSegments never populates
    // and the segment editor form fails to render any condition rows.
    this.privateState.isLoading = true;
    let idSites = undefined;
    let idSite = undefined;
    if (siteId === 'all' || !siteId) {
      idSites = 'all';
      idSite = 'all';
    } else if (siteId) {
      idSites = siteId;
      idSite = siteId;
    }
    return external_CoreHome_["AjaxHelper"].fetch({
      method: 'API.getSegmentsMetadata',
      filter_limit: '-1',
      _hideImplementationData: 0,
      idSites,
      idSite
    }, {
      // Stay out of globalAjaxQueue so a navigation-triggered
      // globalAjaxQueue.abort() (e.g. when the panel close re-renders
      // hashchange listeners) cannot kill the metadata fetch.
      // AjaxHelper silently swallows aborts, which would leave the
      // promise pending forever and the segment editor form rendered
      // without dimension labels or condition rows.
      abortable: false
    }).then(response => {
      this.privateState.isLoading = false;
      if (response) {
        if (visitSegmentsOnly) {
          this.privateState.segments = response.filter(s => s.sqlSegment && s.sqlSegment.match(/log_visit\./));
        } else {
          this.privateState.segments = response;
        }
      }
      return this.state.value.segments;
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
}
/* harmony default export */ var SegmentGenerator_store = (new SegmentGenerator_store_SegmentGeneratorStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=template&id=2246e2ee

const _hoisted_1 = {
  class: "segment-generator",
  ref: "root"
};
const _hoisted_2 = {
  class: "segment-rows"
};
const _hoisted_3 = {
  class: "segment-row"
};
const _hoisted_4 = ["onClick"];
const _hoisted_5 = {
  class: "segment-loading"
};
const _hoisted_6 = {
  class: "segment-row-inputs valign-wrapper"
};
const _hoisted_7 = {
  class: "segment-input metricListBlock valign-wrapper"
};
const _hoisted_8 = {
  style: {
    "width": "100%"
  }
};
const _hoisted_9 = {
  class: "segment-input metricMatchBlock valign-wrapper"
};
const _hoisted_10 = {
  style: {
    "display": "inline-block"
  }
};
const _hoisted_11 = {
  class: "segment-input metricValueBlock valign-wrapper"
};
const _hoisted_12 = {
  class: "form-group row",
  style: {
    "width": "100%"
  }
};
const _hoisted_13 = {
  class: "input-field col s12"
};
const _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  role: "status",
  "aria-live": "polite",
  class: "ui-helper-hidden-accessible"
}, null, -1);
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);
const _hoisted_16 = {
  class: "segment-or"
};
const _hoisted_17 = ["onClick"];
const _hoisted_18 = ["innerHTML"];
const _hoisted_19 = {
  class: "segment-and"
};
const _hoisted_20 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ValueInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ValueInput");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading
  }, null, 8, ["loading"]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.conditions, (condition, conditionIndex) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`segmentRow${conditionIndex}`),
      key: conditionIndex
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(condition.orConditions, (orCondition, orConditionIndex) => {
      var _ctx$segments$orCondi, _ctx$segments$orCondi2;
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`orCondId${orCondition.id}`),
        key: orConditionIndex
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        class: "segment-close",
        onClick: $event => _ctx.removeOrCondition(condition, orCondition)
      }, null, 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader, null, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.conditionValuesLoading[orCondition.id]]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "expandable-select",
        name: "segments",
        "model-value": orCondition.segment,
        "onUpdate:modelValue": $event => _ctx.onSegmentSelection($event, orCondition),
        title: (_ctx$segments$orCondi = _ctx.segments[orCondition.segment]) === null || _ctx$segments$orCondi === void 0 ? void 0 : _ctx$segments$orCondi.name,
        "full-width": true,
        options: _ctx.segmentList
      }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "matchType",
        "model-value": orCondition.matches,
        "onUpdate:modelValue": $event => {
          orCondition.matches = $event;
          _ctx.computeSegmentDefinition();
        },
        "full-width": true,
        options: _ctx.matches[(_ctx$segments$orCondi2 = _ctx.segments[orCondition.segment]) === null || _ctx$segments$orCondi2 === void 0 ? void 0 : _ctx$segments$orCondi2.type]
      }, null, 8, ["model-value", "onUpdate:modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ValueInput, {
        value: orCondition.value,
        onUpdate: $event => {
          orCondition.value = $event;
          // deep watch doesn't catch this change
          this.computeSegmentDefinition();
        }
      }, null, 8, ["value", "onUpdate"])])])]), _hoisted_15])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_OperatorOR')), 1)], 2);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "segment-add-or",
      onClick: $event => _ctx.addNewOrCondition(condition)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      innerHTML: _ctx.$sanitize(_ctx.addNewOrConditionLinkText)
    }, null, 8, _hoisted_18)])], 8, _hoisted_17)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_OperatorAND')), 1)], 2);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "segment-add-row initial",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.addNewAndCondition())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    innerHTML: _ctx.$sanitize(_ctx.addNewAndConditionLinkText)
  }, null, 8, _hoisted_20)])])], 512);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=template&id=2246e2ee

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=template&id=8dceff9a

const ValueInputvue_type_template_id_8dceff9a_hoisted_1 = ["placeholder", "title", "value"];
function ValueInputvue_type_template_id_8dceff9a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
    placeholder: _ctx.translate('General_Value'),
    type: "text",
    class: "autocomplete",
    title: _ctx.translate('General_Value'),
    autocomplete: "off",
    value: _ctx.value,
    onKeydown: _cache[0] || (_cache[0] = $event => _ctx.onKeydownOrConditionValue($event)),
    onChange: _cache[1] || (_cache[1] = $event => _ctx.onKeydownOrConditionValue($event))
  }, null, 40, ValueInputvue_type_template_id_8dceff9a_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=template&id=8dceff9a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=script&lang=ts


/* harmony default export */ var ValueInputvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    value: null
  },
  created() {
    this.onKeydownOrConditionValue = Object(external_CoreHome_["debounce"])(this.onKeydownOrConditionValue, 50);
  },
  emits: ['update'],
  methods: {
    onKeydownOrConditionValue(event) {
      this.$emit('update', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue



ValueInputvue_type_script_lang_ts.render = ValueInputvue_type_template_id_8dceff9a_render

/* harmony default export */ var ValueInput = (ValueInputvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=script&lang=ts





function initialMatches() {
  return {
    metric: [{
      key: '==',
      value: Object(external_CoreHome_["translate"])('General_OperationEquals')
    }, {
      key: '!=',
      value: Object(external_CoreHome_["translate"])('General_OperationNotEquals')
    }, {
      key: '<=',
      value: Object(external_CoreHome_["translate"])('General_OperationAtMost')
    }, {
      key: '>=',
      value: Object(external_CoreHome_["translate"])('General_OperationAtLeast')
    }, {
      key: '<',
      value: Object(external_CoreHome_["translate"])('General_OperationLessThan')
    }, {
      key: '>',
      value: Object(external_CoreHome_["translate"])('General_OperationGreaterThan')
    }],
    dimension: [{
      key: '==',
      value: Object(external_CoreHome_["translate"])('General_OperationIs')
    }, {
      key: '!=',
      value: Object(external_CoreHome_["translate"])('General_OperationIsNot')
    }, {
      key: '=@',
      value: Object(external_CoreHome_["translate"])('General_OperationContains')
    }, {
      key: '!@',
      value: Object(external_CoreHome_["translate"])('General_OperationDoesNotContain')
    }, {
      key: '=^',
      value: Object(external_CoreHome_["translate"])('General_OperationStartsWith')
    }, {
      key: '=$',
      value: Object(external_CoreHome_["translate"])('General_OperationEndsWith')
    }]
  };
}
function generateUniqueId() {
  let id = '';
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  for (let i = 1; i <= 10; i += 1) {
    id += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return id;
}
function findAndExplodeByMatch(metric) {
  const matches = ['==', '!=', '<=', '>=', '=@', '!@', '<', '>', '=^', '=$'];
  const newMetric = {};
  let minPos = metric.length;
  let match;
  let index;
  let singleChar = false;
  for (let key = 0; key < matches.length; key += 1) {
    match = matches[key];
    index = metric.indexOf(match);
    if (index !== -1) {
      if (index < minPos) {
        minPos = index;
        if (match.length === 1) {
          singleChar = true;
        }
      }
    }
  }
  if (minPos < metric.length) {
    // sth found - explode
    if (singleChar === true) {
      newMetric.segment = metric.slice(0, minPos);
      newMetric.matches = metric.slice(minPos, minPos + 1);
      newMetric.value = decodeURIComponent(metric.slice(minPos + 1));
    } else {
      newMetric.segment = metric.slice(0, minPos);
      newMetric.matches = metric.slice(minPos, minPos + 2);
      newMetric.value = decodeURIComponent(metric.slice(minPos + 2));
    }
    // if value is only '' -> change to empty string
    if (newMetric.value === '""') {
      newMetric.value = '';
    }
  }
  try {
    // Decode again to deal with double-encoded segments in database
    newMetric.value = decodeURIComponent(newMetric.value);
  } catch (e) {
    // Expected if the segment was not double-encoded
  }
  return newMetric;
}
function stripTags(text) {
  return text ? `${text}`.replace(/(<([^>]+)>)/ig, '') : text;
}
const {
  $
} = window;
/* harmony default export */ var SegmentGeneratorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    addInitialCondition: Boolean,
    visitSegmentsOnly: Boolean,
    idsite: {
      type: [String, Number],
      default: () => external_CoreHome_["Matomo"].idSite
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    MatomoLoader: external_CoreHome_["MatomoLoader"],
    ValueInput: ValueInput
  },
  data() {
    return {
      conditions: [],
      queriedSegments: [],
      matches: initialMatches(),
      conditionValuesLoading: {},
      segmentDefinition: ''
    };
  },
  emits: ['update:modelValue'],
  watch: {
    modelValue(newVal) {
      if ((newVal || '') !== (this.segmentDefinition || '')) {
        this.setSegmentString(newVal);
      }
    },
    conditions: {
      deep: true,
      handler() {
        this.computeSegmentDefinition();
      }
    },
    segmentDefinition(newVal) {
      if ((newVal || '') !== (this.modelValue || '')) {
        this.$emit('update:modelValue', newVal);
      }
    },
    idsite(newVal) {
      this.reloadSegments(newVal, this.visitSegmentsOnly);
    }
  },
  created() {
    this.matches[''] = this.matches.dimension;
    this.setSegmentString(this.modelValue);
    this.segmentDefinition = this.modelValue;
    this.reloadSegments(this.idsite, this.visitSegmentsOnly);
  },
  methods: {
    reloadSegments(idsite, visitSegmentsOnly) {
      SegmentGenerator_store.loadSegments(idsite, visitSegmentsOnly).then(segments => {
        this.queriedSegments = segments.map(s => Object.assign(Object.assign({}, s), {}, {
          category: s.category || 'Others'
        }));
        if (this.addInitialCondition && this.conditions.length === 0) {
          this.addNewAndCondition();
        }
      });
    },
    addAndCondition(condition) {
      this.conditions.push(condition);
    },
    addNewOrCondition(condition) {
      if (!this.firstSegment) {
        return; // skip till list of segments is available
      }
      const orCondition = {
        segment: this.firstSegment,
        matches: this.firstMatch,
        value: ''
      };
      this.addOrCondition(condition, orCondition);
    },
    addOrCondition(condition, orCondition) {
      this.conditionValuesLoading[orCondition.id] = false;
      orCondition.id = generateUniqueId();
      condition.orConditions.push(orCondition);
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
        this.updateAutocomplete(orCondition);
      });
    },
    onSegmentSelection(event, orCondition) {
      orCondition.segment = event;
      this.updateAutocomplete(orCondition);
      this.computeSegmentDefinition();
      this.focusValueInput(orCondition);
    },
    updateAutocomplete(orCondition) {
      this.conditionValuesLoading[orCondition.id] = true;
      $(`.orCondId${orCondition.id} .metricValueBlock input`, this.$refs.root).autocomplete({
        source: [],
        minLength: 0
      });
      const abortController = new AbortController();
      let resolved = false;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'API',
        format: 'json',
        method: 'API.getSuggestedValuesForSegment',
        segmentName: orCondition.segment,
        segment: null,
        idSite: this.idsite
      }, {
        createErrorNotification: false // don't show errors returned from the API in UI
      }).then(response => {
        this.conditionValuesLoading[orCondition.id] = false;
        resolved = true;
        let autocompleteValues = response;
        if (Array.isArray(autocompleteValues)) {
          autocompleteValues = autocompleteValues.map(v => `${v}`);
        }
        const inputElement = $(`.orCondId${orCondition.id} .metricValueBlock input`).autocomplete({
          source: autocompleteValues,
          minLength: 0,
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          select: (event, ui) => {
            event.preventDefault();
            orCondition.value = ui.item.value;
            this.computeSegmentDefinition(); // deep watch doesn't catch this change
            this.$forceUpdate();
          }
        }).off('click').click(() => {
          $(inputElement).autocomplete('search', orCondition.value);
        });
      }).catch(() => {
        resolved = true;
        this.conditionValuesLoading[orCondition.id] = false;
        $(`.orCondId${orCondition.id} .metricValueBlock input`).autocomplete({
          source: [],
          minLength: 0
        }).autocomplete('search', orCondition.value);
      });
      setTimeout(() => {
        if (!resolved) {
          abortController.abort();
        }
      }, 20000);
    },
    removeOrCondition(condition, orCondition) {
      const index = condition.orConditions.indexOf(orCondition);
      if (index > -1) {
        condition.orConditions.splice(index, 1);
      }
      if (condition.orConditions.length === 0) {
        const andCondIndex = this.conditions.indexOf(condition);
        if (index > -1) {
          this.conditions.splice(andCondIndex, 1);
        }
      }
    },
    setSegmentString(segmentStr) {
      this.conditions = [];
      if (!segmentStr) {
        return;
      }
      const blocks = segmentStr.split(';').map(b => b.split(','));
      this.conditions = blocks.map(block => {
        const condition = {
          orConditions: []
        };
        block.forEach(innerBlock => {
          const orCondition = findAndExplodeByMatch(innerBlock);
          this.addOrCondition(condition, orCondition);
        });
        return condition;
      });
    },
    addNewAndCondition() {
      const condition = {
        orConditions: []
      };
      if (!this.firstSegment) {
        return; // skip till list of segments is available
      }
      this.addAndCondition(condition);
      this.addNewOrCondition(condition);
    },
    // NOTE: can't use a computed property since we need to recompute on changes inside the
    //       structure. don't have to if we don't do in-place changes, but with nested structures,
    //       that's complicated.
    computeSegmentDefinition() {
      let segmentStr = '';
      this.conditions.forEach(condition => {
        if (!condition.orConditions.length) {
          return;
        }
        let subSegmentStr = '';
        condition.orConditions.forEach(orCondition => {
          if (!orCondition.value && !orCondition.segment && !orCondition.matches) {
            return;
          }
          if (subSegmentStr !== '') {
            subSegmentStr += ','; // OR operator
          }
          // one encode for urldecode on value, one encode for urldecode on condition
          const value = encodeURIComponent(encodeURIComponent(orCondition.value));
          subSegmentStr += `${orCondition.segment}${orCondition.matches}${value}`;
        });
        if (segmentStr !== '') {
          segmentStr += ';'; // add AND operator between segment blocks
        }
        segmentStr += subSegmentStr;
      });
      this.segmentDefinition = segmentStr;
    },
    focusValueInput(orCondition) {
      const $input = $(`.orCondId${orCondition.id} .metricValueBlock input`);
      $input.focus();
      if ($input.val()) {
        $input.select();
      }
    }
  },
  computed: {
    firstSegment() {
      var _this$queriedSegments;
      return ((_this$queriedSegments = this.queriedSegments[0]) === null || _this$queriedSegments === void 0 ? void 0 : _this$queriedSegments.segment) || null;
    },
    firstMatch() {
      const segment = this.queriedSegments[0];
      if (!segment) {
        return null;
      }
      if (segment.type && this.matches[segment.type]) {
        return this.matches[segment.type][0].key;
      }
      return this.matches[''][0].key;
    },
    segments() {
      const result = {};
      this.queriedSegments.forEach(s => {
        result[s.segment] = s;
      });
      return result;
    },
    segmentList() {
      return this.queriedSegments.map(s => ({
        group: s.category,
        key: s.segment,
        value: s.name,
        tooltip: s.acceptedValues ? stripTags(s.acceptedValues) : undefined
      }));
    },
    addNewOrConditionLinkText() {
      return `+ ${Object(external_CoreHome_["translate"])('SegmentEditor_AddANDorORCondition', `<span>${Object(external_CoreHome_["translate"])('SegmentEditor_OperatorOR')}</span>`)}`;
    },
    andConditionLabel() {
      return this.conditions.length ? Object(external_CoreHome_["translate"])('SegmentEditor_OperatorAND') : '';
    },
    addNewAndConditionLinkText() {
      return `+ ${Object(external_CoreHome_["translate"])('SegmentEditor_AddANDorORCondition', `<span>${this.andConditionLabel}</span>`)}`;
    },
    isLoading() {
      return SegmentGenerator_store.state.value.isLoading;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue



SegmentGeneratorvue_type_script_lang_ts.render = render

/* harmony default export */ var SegmentGenerator = (SegmentGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.helpers.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function getStarredByTitlePart(segment, userContext, translations) {
  const login = segment.starred_by || '';
  if (login === userContext.login) {
    return ` (${translations.General_StarredByYou})`;
  }
  return ` (${translations.General_StarredBy} ${login})`;
}
function getCanUserEditSegment(segment, segmentAccess, userContext) {
  if (!segment || userContext.isAnonymous) {
    return false;
  }
  if (segmentAccess !== 'write') {
    return false;
  }
  if (userContext.hasSuperUserAccess) {
    return true;
  }
  return segment.login === userContext.login;
}
function getDeleteSegmentTitle(segment, canEdit, translations) {
  if (segment.enable_only_idsite) {
    return canEdit ? translations.General_CanDeleteSiteSegment : translations.General_CanNotDeleteSiteSegment;
  }
  return canEdit ? translations.General_CanDeleteGlobalSegment : translations.General_CanNotDeleteGlobalSegment;
}
function getEditSegmentTitle(segment, canEdit, translations) {
  if (segment.enable_only_idsite) {
    return canEdit ? translations.General_CanEditSiteSegment : translations.General_CanNotEditSiteSegment;
  }
  return canEdit ? translations.General_CanEditGlobalSegment : translations.General_CanNotEditGlobalSegment;
}
function getStarSegmentTitle(segment, canEdit, translations, userContext) {
  if (userContext.isAnonymous) {
    return '';
  }
  if (segment.enable_only_idsite) {
    if (canEdit) {
      if (segment.starred) {
        return `${translations.General_CanUnstarSiteSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
      }
      return translations.General_CanStarSiteSegment;
    }
    if (segment.starred) {
      return translations.General_CanNotUnstarSiteSegment;
    }
    return translations.General_CanNotStarSiteSegment;
  }
  if (canEdit) {
    if (segment.starred) {
      return `${translations.General_CanUnstarGlobalSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
    }
    return translations.General_CanStarGlobalSegment;
  }
  if (segment.starred) {
    return translations.General_CanNotUnstarGlobalSegment;
  }
  return translations.General_CanNotStarGlobalSegment;
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.store.ts
function SegmentSelector_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



class SegmentSelector_store_SegmentSelectorStore {
  constructor() {
    SegmentSelector_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      availableSegments: [],
      currentSegment: '',
      isUserAnonymous: false,
      isInitialized: false,
      loginUrl: '',
      manageSegmentsUrl: '',
      panelExpanded: false,
      renderVersion: 0,
      segmentAccess: 'read',
      translations: {},
      userContext: {
        isAnonymous: false,
        hasSuperUserAccess: false,
        login: ''
      }
    }));
    SegmentSelector_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    SegmentSelector_store_defineProperty(this, "starChangeCallbacks", []);
  }
  normalizeAvailableSegments(segments) {
    return segments.map(segment => Object.assign(Object.assign({}, segment), {}, {
      starred: this.normalizeStarredState(segment.starred)
    }));
  }
  init(config) {
    // Normalise the starred flag up-front so external consumers (e.g. the
    // page-table panel API getSegmentFromId) can rely on `segment.starred`
    // being a boolean. The backend sends "0"/"1" strings or 0/1 numbers,
    // and the screenshot tests strict-equality compare against true/false.
    this.privateState.availableSegments = this.normalizeAvailableSegments(config.availableSegments);
    this.privateState.currentSegment = config.currentSegment || '';
    this.privateState.isUserAnonymous = config.isUserAnonymous;
    this.privateState.isInitialized = true;
    this.privateState.loginUrl = config.loginUrl;
    this.privateState.manageSegmentsUrl = config.manageSegmentsUrl;
    this.privateState.segmentAccess = config.segmentAccess;
    this.privateState.translations = config.translations;
    this.privateState.userContext = config.userContext;
    this.privateState.renderVersion += 1;
  }
  onStarChange(callback) {
    this.starChangeCallbacks.push(callback);
    let isUnsubscribed = false;
    return () => {
      if (isUnsubscribed) {
        return;
      }
      isUnsubscribed = true;
      const index = this.starChangeCallbacks.indexOf(callback);
      if (index !== -1) {
        this.starChangeCallbacks.splice(index, 1);
      }
    };
  }
  notifyChange() {
    this.privateState.renderVersion += 1;
  }
  setAvailableSegments(segments) {
    this.privateState.availableSegments = this.normalizeAvailableSegments(segments);
    this.notifyChange();
  }
  setCurrentSegment(segment) {
    this.privateState.currentSegment = segment || '';
    this.notifyChange();
  }
  getCurrentSegment() {
    return this.privateState.currentSegment;
  }
  setPanelExpanded(isExpanded) {
    this.privateState.panelExpanded = isExpanded;
    this.notifyChange();
  }
  getPanelExpanded() {
    return this.privateState.panelExpanded;
  }
  getSegmentAccess() {
    return this.privateState.segmentAccess;
  }
  getTranslations() {
    return this.privateState.translations;
  }
  getUserContext() {
    return this.privateState.userContext;
  }
  normalizeStarredState(starred) {
    if (typeof starred === 'boolean') {
      return starred;
    }
    if (typeof starred === 'number') {
      return starred !== 0;
    }
    if (typeof starred === 'string') {
      return starred === '1' || starred.toLowerCase() === 'true';
    }
    return false;
  }
  getSegmentFromId(idSegment) {
    if (typeof idSegment === 'undefined' || idSegment === null || idSegment === '') {
      return null;
    }
    return this.privateState.availableSegments.find(segment => `${segment.idsegment}` === `${idSegment}`) || null;
  }
  decodeDefinition(definition) {
    const candidates = [definition];
    try {
      candidates.push(window.piwikHelper.htmlDecode(definition));
    } catch (e) {
      // Ignore decode failures and keep original value.
    }
    try {
      candidates.push(window.piwikHelper.htmlDecode(decodeURIComponent(definition)));
    } catch (e) {
      // Ignore decode failures and keep original value.
    }
    return candidates.filter((candidate, index, values) => typeof candidate !== 'undefined' && values.indexOf(candidate) === index);
  }
  getSegmentByDefinition(definition) {
    const candidates = this.decodeDefinition(definition);
    return this.privateState.availableSegments.find(segment => candidates.indexOf(segment.definition) !== -1) || null;
  }
  getPlainSegmentName(segment) {
    return window.piwikHelper.htmlDecode(segment.name);
  }
  getSegmentTooltipText(segment) {
    let segmentName = window.piwikHelper.htmlDecode(segment.name);
    const {
      userContext
    } = this.privateState;
    if (userContext.hasSuperUserAccess && segment.login !== userContext.login) {
      segmentName += ' (';
      segmentName += Object(external_CoreHome_["translate"])('General_CreatedByUser', [segment.login || '']);
      if (Number(segment.enable_all_users) === 0) {
        segmentName += `, ${Object(external_CoreHome_["translate"])('SegmentEditor_VisibleToSuperUser')}`;
      }
      segmentName += ')';
    }
    return segmentName;
  }
  isSegmentVisibleToSuperUserOnly(segment) {
    const {
      userContext
    } = this.privateState;
    return userContext.hasSuperUserAccess && segment.login !== userContext.login && Number(segment.enable_all_users) === 0;
  }
  isSegmentSharedWithMeBySuperUser(segment) {
    const {
      userContext
    } = this.privateState;
    if (userContext.hasSuperUserAccess) {
      return false;
    }
    return segment.login !== userContext.login && Number(segment.enable_all_users) === 1;
  }
  getCurrentSegmentTitle() {
    const current = this.getCurrentSegment();
    if (current !== '') {
      const segment = this.getSegmentByDefinition(current);
      if (segment) {
        return this.getPlainSegmentName(segment);
      }
      return Object(external_CoreHome_["translate"])('SegmentEditor_CustomSegment');
    }
    return this.privateState.translations.SegmentEditor_DefaultAllVisits;
  }
  getCurrentSegmentTooltip() {
    let title = `${Object(external_CoreHome_["translate"])('SegmentEditor_ChooseASegment')}.`;
    title += ` ${Object(external_CoreHome_["translate"])('SegmentEditor_CurrentlySelectedSegment', [this.getCurrentSegmentTitle()])}`;
    return title;
  }
  getComparedSegmentDefinitions() {
    return external_CoreHome_["ComparisonsStoreInstance"].getSegmentComparisons().map(comparison => comparison.params.segment);
  }
  getComparisonLimit() {
    return Number(window.piwik.config.data_comparison_segment_limit) + 1;
  }
  isComparisonAvailable() {
    const comparisonService = external_CoreHome_["ComparisonsStoreInstance"];
    const isEnabled = comparisonService.isComparisonEnabled();
    return isEnabled || isEnabled === null;
  }
  isSegmentSelected(definition) {
    return definition === this.privateState.currentSegment || definition === decodeURIComponent(this.privateState.currentSegment);
  }
  isSegmentCompared(definition, comparedSegments) {
    return comparedSegments.indexOf(definition) !== -1 || comparedSegments.indexOf(decodeURIComponent(definition)) !== -1;
  }
  buildCompareState(definition, comparedSegments) {
    if (this.isSegmentCompared(definition, comparedSegments)) {
      return {
        state: 'active',
        title: Object(external_CoreHome_["translate"])('SegmentEditor_CompareThisSegment')
      };
    }
    if (comparedSegments.length >= this.getComparisonLimit()) {
      return {
        state: 'disabled',
        title: Object(external_CoreHome_["translate"])('General_MaximumNumberOfSegmentsComparedIs', [this.getComparisonLimit()])
      };
    }
    return {
      state: '',
      title: Object(external_CoreHome_["translate"])('SegmentEditor_CompareThisSegment')
    };
  }
  getCanUserEditSegment(segment) {
    return getCanUserEditSegment(segment, this.privateState.segmentAccess, this.privateState.userContext);
  }
  getEditSegmentTitle(segment, canEdit) {
    return getEditSegmentTitle(segment, canEdit, this.privateState.translations);
  }
  getDeleteSegmentTitle(segment, canEdit) {
    return getDeleteSegmentTitle(segment, canEdit, this.privateState.translations);
  }
  getStarSegmentTitle(segment, canEdit) {
    return getStarSegmentTitle(segment, canEdit, this.privateState.translations, this.privateState.userContext);
  }
  toggleStarredSegment(segment, idSegment) {
    segment.starred = !this.normalizeStarredState(segment.starred);
    const method = segment.starred ? 'star' : 'unstar';
    this.notifyStarredSegment(segment);
    const LegacyAjaxHelper = window.ajaxHelper;
    const ajaxHandler = new LegacyAjaxHelper();
    ajaxHandler.addParams({
      module: 'API',
      format: 'json',
      method: `SegmentEditor.${method}`,
      userLogin: this.privateState.userContext.login,
      idSegment: idSegment || ''
    }, 'POST');
    ajaxHandler.useCallbackInCaseOfError();
    ajaxHandler.setCallback(response => {
      if (!response || response.result === 'error') {
        segment.starred = !this.normalizeStarredState(segment.starred);
        this.notifyStarredSegment(segment, true);
        return;
      }
      segment.starred = this.normalizeStarredState(response.starred);
      segment.starred_by = response.starred_by;
      this.notifyStarredSegment(segment);
    });
    ajaxHandler.send();
  }
  toggleStarredSegmentById(idSegment) {
    const segment = this.getSegmentFromId(idSegment);
    if (!segment) {
      return;
    }
    this.toggleStarredSegment(segment, idSegment);
  }
  notifyStarredSegment(segment, isError = false) {
    this.notifyChange();
    this.starChangeCallbacks.forEach(callback => {
      callback(segment, isError);
    });
  }
  buildSearchContext(searchValue) {
    const rawSearch = searchValue || '';
    const hasSearch = rawSearch.length >= 2;
    return {
      hasSearch,
      lowerSearch: rawSearch.toLowerCase(),
      normalizedSearch: hasSearch ? window.piwikHelper.normalize(rawSearch) : ''
    };
  }
  matchesSearch(text, search) {
    if (!search.hasSearch) {
      return true;
    }
    const normalizedText = window.piwikHelper.normalize(text);
    const lowerText = text.toLowerCase();
    return normalizedText.indexOf(search.normalizedSearch) !== -1 || lowerText.indexOf(search.lowerSearch) !== -1;
  }
  buildHeaderEntry(type) {
    if (type === 'shared') {
      return {
        key: 'header-shared-with-you',
        type: 'header',
        className: 'segmentsSharedWithMeBySuperUser',
        label: Object(external_CoreHome_["translate"])('SegmentEditor_SharedWithYou'),
        tooltip: ''
      };
    }
    return {
      key: 'header-visible-to-super-user',
      type: 'header',
      className: 'segmentsVisibleToSuperUser',
      label: Object(external_CoreHome_["translate"])('SegmentEditor_VisibleToSuperUser'),
      tooltip: ''
    };
  }
  buildAllVisitsEntry(context) {
    const allVisitsCompareState = this.buildCompareState('', context.comparedSegments);
    const label = [this.privateState.translations.SegmentEditor_DefaultAllVisits, this.privateState.translations.General_DefaultAppended].join(' ');
    return {
      key: 'segment-all-visits',
      type: 'segment',
      classes: [this.privateState.currentSegment === '' ? 'segmentSelected' : '', this.isSegmentCompared('', context.comparedSegments) ? 'comparedSegment' : ''].join(' ').trim(),
      idsegment: '',
      definition: '',
      label,
      tooltip: label,
      showStarButton: false,
      showStarPlaceholder: !this.privateState.isUserAnonymous,
      showEditButton: false,
      showEditPlaceholder: this.privateState.segmentAccess === 'write',
      showCompareButton: context.comparisonAvailable,
      compareButtonClass: 'segmentAction compareSegment allVisitsCompareSegment',
      compareTitle: allVisitsCompareState.title,
      compareState: allVisitsCompareState.state
    };
  }
  buildSegmentEntry(segment, tooltipText, labelText, context) {
    const canEdit = this.getCanUserEditSegment(segment);
    const compareState = this.buildCompareState(segment.definition, context.comparedSegments);
    const classes = [];
    if (this.isSegmentSelected(segment.definition)) {
      classes.push('segmentSelected');
    }
    if (segment.starred) {
      classes.push('segmentStarred');
    }
    if (this.isSegmentCompared(segment.definition, context.comparedSegments)) {
      classes.push('comparedSegment');
    }
    return {
      key: `segment-${segment.idsegment}`,
      type: 'segment',
      classes: classes.join(' '),
      idsegment: `${segment.idsegment || ''}`,
      definition: segment.definition,
      label: labelText,
      tooltip: tooltipText,
      // Intentionally hide the star control for anonymous users rather than
      // showing a disabled state; this is the agreed product behavior.
      showStarButton: !this.privateState.isUserAnonymous,
      isStarred: this.normalizeStarredState(segment.starred),
      starTitle: this.getStarSegmentTitle(segment, canEdit),
      starState: canEdit ? '' : 'disabled',
      showEditButton: this.privateState.segmentAccess === 'write',
      editTitle: this.getEditSegmentTitle(segment, canEdit),
      editState: canEdit ? '' : 'disabled',
      showCompareButton: context.comparisonAvailable,
      compareButtonClass: 'segmentAction compareSegment',
      compareTitle: compareState.title,
      compareState: compareState.state
    };
  }
  buildSegmentEntries(context) {
    const entries = [];
    let hasSharedHeader = false;
    let hasSuperUserHeader = false;
    this.privateState.availableSegments.forEach(segment => {
      const isStarred = this.normalizeStarredState(segment.starred);
      const labelText = this.getPlainSegmentName(segment);
      const tooltipText = this.getSegmentTooltipText(segment);
      if (!this.matchesSearch(tooltipText, context.search)) {
        return;
      }
      if (this.isSegmentSharedWithMeBySuperUser(segment) && !hasSharedHeader) {
        hasSharedHeader = true;
        entries.push(this.buildHeaderEntry('shared'));
      }
      if (this.isSegmentVisibleToSuperUserOnly(segment) && !hasSuperUserHeader) {
        hasSuperUserHeader = true;
        entries.push(this.buildHeaderEntry('superuser'));
      }
      entries.push(this.buildSegmentEntry(Object.assign(Object.assign({}, segment), {}, {
        starred: isStarred
      }), tooltipText, labelText, context));
    });
    return entries;
  }
  buildNoResultsEntry() {
    return {
      key: 'no-results',
      type: 'no-results',
      classes: 'filterNoResults grayed',
      idsegment: '',
      definition: '',
      label: this.privateState.translations.General_SearchNoResults,
      tooltip: this.privateState.translations.General_SearchNoResults,
      showStarButton: false,
      showEditButton: false,
      showCompareButton: false
    };
  }
  buildSelectorEntries(context) {
    const entries = [];
    const allVisitsEntry = this.buildAllVisitsEntry(context);
    if (this.matchesSearch(allVisitsEntry.label, context.search)) {
      entries.push(allVisitsEntry);
    }
    entries.push(...this.buildSegmentEntries(context));
    if (context.search.hasSearch && entries.filter(entry => entry.type === 'segment').length === 0) {
      entries.push(this.buildNoResultsEntry());
    }
    return entries;
  }
  buildViewModel(entries) {
    return {
      authorizedToCreateSegments: this.privateState.segmentAccess === 'write',
      currentSegmentTitle: this.getCurrentSegmentTitle(),
      currentSegmentTooltip: this.getCurrentSegmentTooltip(),
      currentSegmentValue: this.privateState.currentSegment,
      entries,
      isExpanded: this.privateState.panelExpanded,
      isUserAnonymous: !!this.privateState.isUserAnonymous,
      loginUrl: this.privateState.loginUrl,
      manageSegmentsUrl: this.privateState.manageSegmentsUrl
    };
  }
  getSelectorViewModel(searchValue) {
    const {
      renderVersion
    } = this.privateState;
    if (renderVersion < 0) {
      throw new Error('Segment selector render version must not be negative');
    }
    const context = {
      comparedSegments: this.getComparedSegmentDefinitions(),
      comparisonAvailable: this.isComparisonAvailable(),
      search: this.buildSearchContext(searchValue)
    };
    const entries = this.buildSelectorEntries(context);
    return this.buildViewModel(entries);
  }
}
/* harmony default export */ var SegmentSelector_store = (new SegmentSelector_store_SegmentSelectorStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=template&id=75417757

const SegmentSelectorvue_type_template_id_75417757_hoisted_1 = {
  ref: "root"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_2 = {
  key: 0,
  class: "segmentationContainer listHtml"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_3 = ["title"];
const SegmentSelectorvue_type_template_id_75417757_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-segment"
}, null, -1);
const SegmentSelectorvue_type_template_id_75417757_hoisted_5 = {
  class: "dropdown dropdown-body"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_6 = {
  class: "segmentFilterContainer"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_7 = {
  class: "submenu"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_8 = {
  class: "segment-visits-label"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_9 = {
  class: "segmentList"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const SegmentSelectorvue_type_template_id_75417757_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SegmentSelectorvue_type_template_id_75417757_hoisted_12 = ["data-idsegment", "data-definition", "onClick", "onAnimationend"];
const SegmentSelectorvue_type_template_id_75417757_hoisted_13 = ["title", "onKeyup"];
const SegmentSelectorvue_type_template_id_75417757_hoisted_14 = {
  key: 1,
  class: "segmentAction starSegment segmentAction--placeholder",
  "aria-hidden": "true"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_15 = {
  key: 4,
  class: "segmentAction editSegment segmentAction--placeholder",
  "aria-hidden": "true"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const SegmentSelectorvue_type_template_id_75417757_hoisted_17 = ["href"];
const SegmentSelectorvue_type_template_id_75417757_hoisted_18 = {
  key: 1
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_19 = {
  class: "youMustBeLoggedIn"
};
const SegmentSelectorvue_type_template_id_75417757_hoisted_20 = ["href"];
function SegmentSelectorvue_type_template_id_75417757_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchInput");
  const _component_star_button = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("star-button");
  const _component_compare_button = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("compare-button");
  const _component_edit_button = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("edit-button");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_1, [_ctx.viewModel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "title",
    tabindex: "4",
    title: _ctx.viewModel.currentSegmentTooltip,
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.togglePanel && _ctx.togglePanel(...args), ["prevent"]))
  }, [SegmentSelectorvue_type_template_id_75417757_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["segmentationTitle", {
      'segment-clicked': !!_ctx.viewModel.currentSegmentValue
    }])
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.viewModel.currentSegmentTitle), 3)], 8, SegmentSelectorvue_type_template_id_75417757_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchInput, {
    tabindex: "4",
    modelValue: _ctx.searchInput,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.searchInput = $event),
    "show-clear": true
  }, null, 8, ["modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", SegmentSelectorvue_type_template_id_75417757_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SegmentSelectorvue_type_template_id_75417757_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_SelectSegmentOfVisits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.viewModel.entries, entry => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: entry.key
    }, [entry.type === 'header' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(entry.className)
    }, [SegmentSelectorvue_type_template_id_75417757_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.label) + ": ", 1), SegmentSelectorvue_type_template_id_75417757_hoisted_11], 2)) : entry.type === 'no-results' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 1,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.getEntryClasses(entry))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.label), 3)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 2,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.getEntryClasses(entry)),
      "data-idsegment": entry.idsegment,
      "data-definition": entry.definition,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.selectSegment(entry), ["prevent"]),
      onAnimationend: $event => _ctx.clearStarAnimationClass(entry)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "segname",
      tabindex: "4",
      title: entry.tooltip,
      onKeyup: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.selectSegment(entry), ["prevent"]), ["enter"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.label), 41, SegmentSelectorvue_type_template_id_75417757_hoisted_13), entry.type === 'segment' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [entry.showStarButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_star_button, {
      key: 0,
      segment: entry
    }, null, 8, ["segment"])) : entry.showStarPlaceholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SegmentSelectorvue_type_template_id_75417757_hoisted_14)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.showCompareButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_compare_button, {
      key: 2,
      segment: entry,
      "is-anonymous": _ctx.viewModel.isUserAnonymous,
      onToggleCompareButton: _ctx.toggleComparison
    }, null, 8, ["segment", "is-anonymous", "onToggleCompareButton"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.showEditButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_edit_button, {
      key: 3,
      segment: entry,
      onOpenEditButton: _ctx.openEditSegment
    }, null, 8, ["segment", "onOpenEditButton"])) : entry.showEditPlaceholder ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SegmentSelectorvue_type_template_id_75417757_hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 42, SegmentSelectorvue_type_template_id_75417757_hoisted_12))], 64);
  }), 128))])])])]), _ctx.viewModel.authorizedToCreateSegments ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    tabindex: "4",
    class: "add_new_segment btn",
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.openAddSegment && _ctx.openAddSegment(...args), ["stop", "prevent"]))
  }, [SegmentSelectorvue_type_template_id_75417757_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_AddNewSegment')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.viewModel.manageSegmentsUrl,
    tabindex: "4",
    class: "btn btn-block btn-outline manage_segment_btn"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_ManageSegments')), 9, SegmentSelectorvue_type_template_id_75417757_hoisted_17)], 64)) : _ctx.viewModel.isUserAnonymous ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentSelectorvue_type_template_id_75417757_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SegmentSelectorvue_type_template_id_75417757_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_YouMustBeLoggedInToCreateSegments')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.viewModel.loginUrl,
    tabindex: "4",
    class: "sign_in_segment_btn btn"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Login_LogIn')), 9, SegmentSelectorvue_type_template_id_75417757_hoisted_20)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=template&id=75417757

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/StarButton.vue?vue&type=template&id=abb7f9e4

const StarButtonvue_type_template_id_abb7f9e4_hoisted_1 = ["data-star", "title", "data-state"];
function StarButtonvue_type_template_id_abb7f9e4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_StarIcon = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("StarIcon");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    "data-star": _ctx.segment.idsegment,
    class: "segmentAction starSegment",
    title: _ctx.segment.starTitle,
    "data-state": _ctx.segment.starState,
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleStar(_ctx.entry), ["stop", "prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_StarIcon, {
    filled: !!_ctx.segment.isStarred
  }, null, 8, ["filled"])], 8, StarButtonvue_type_template_id_abb7f9e4_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/StarButton.vue?vue&type=template&id=abb7f9e4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/StarIcon.vue?vue&type=template&id=6ae6b8eb

const StarIconvue_type_template_id_6ae6b8eb_hoisted_1 = {
  xmlns: "http://www.w3.org/2000/svg",
  width: "18",
  height: "18",
  viewBox: "0 0 18 18",
  "aria-hidden": "true"
};
const StarIconvue_type_template_id_6ae6b8eb_hoisted_2 = ["fill", "d"];
function StarIconvue_type_template_id_6ae6b8eb_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", StarIconvue_type_template_id_6ae6b8eb_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    "stroke-width": "1.5",
    "stroke-linecap": "round",
    "stroke-linejoin": "round",
    stroke: "currentColor",
    fill: _ctx.iconFill,
    d: _ctx.starPath
  }, null, 8, StarIconvue_type_template_id_6ae6b8eb_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/StarIcon.vue?vue&type=template&id=6ae6b8eb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/StarIcon.vue?vue&type=script&lang=ts

const starPath = 'M8.64315 1.72117C8.67601 1.65477 8.72679 1.59887 8.78974 1.55979C8.85268 1.52071 8.9253 1.5 8.9994 1.5C9.07349 1.5 9.14611 1.52071 9.20906 1.55979C9.27201 1.59887 9.32278 1.65477 9.35565 1.72117L11.0881 5.23042C11.2023 5.4614 11.3708 5.66123 11.5791 5.81276C11.7875 5.96429 12.0295 6.063 12.2844 6.10042L16.1589 6.66742C16.2323 6.67806 16.3013 6.70902 16.358 6.75682C16.4147 6.80461 16.457 6.86733 16.4799 6.93787C16.5029 7.00842 16.5056 7.08397 16.4878 7.156C16.4701 7.22802 16.4325 7.29363 16.3794 7.34542L13.5774 10.0739C13.3926 10.254 13.2544 10.4763 13.1745 10.7216C13.0947 10.967 13.0757 11.2281 13.1191 11.4824L13.7806 15.3374C13.7936 15.4108 13.7857 15.4863 13.7578 15.5554C13.7299 15.6245 13.6831 15.6844 13.6228 15.7282C13.5625 15.772 13.4911 15.7979 13.4168 15.8031C13.3425 15.8083 13.2682 15.7924 13.2024 15.7574L9.7389 13.9364C9.51068 13.8166 9.25678 13.754 8.99902 13.754C8.74126 13.754 8.48736 13.8166 8.25915 13.9364L4.7964 15.7574C4.73064 15.7922 4.65644 15.8079 4.58223 15.8026C4.50803 15.7973 4.43679 15.7713 4.37662 15.7276C4.31645 15.6838 4.26977 15.6241 4.24189 15.5551C4.21401 15.4861 4.20604 15.4107 4.2189 15.3374L4.87965 11.4832C4.92329 11.2287 4.90438 10.9675 4.82455 10.722C4.74472 10.4764 4.60635 10.254 4.4214 10.0739L1.6194 7.34617C1.56584 7.29444 1.52789 7.22872 1.50987 7.15648C1.49185 7.08423 1.49447 7.00838 1.51746 6.93756C1.54044 6.86674 1.58285 6.8038 1.63985 6.75591C1.69686 6.70801 1.76617 6.67709 1.8399 6.66667L5.71365 6.10042C5.96884 6.06329 6.21119 5.96471 6.41983 5.81316C6.62848 5.66161 6.79717 5.46162 6.9114 5.23042L8.64315 1.72117Z';
/* harmony default export */ var StarIconvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'StarIcon',
  props: {
    filled: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    iconFill() {
      return this.filled ? 'currentColor' : 'none';
    },
    starPath() {
      return starPath;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/StarIcon.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/StarIcon.vue



StarIconvue_type_script_lang_ts.render = StarIconvue_type_template_id_6ae6b8eb_render

/* harmony default export */ var StarIcon = (StarIconvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/StarButton.vue?vue&type=script&lang=ts



/* harmony default export */ var StarButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'StarButton',
  components: {
    StarIcon: StarIcon
  },
  props: {
    segment: {
      type: Object,
      required: true
    }
  },
  methods: {
    toggleStar() {
      if (this.segment.starState === 'disabled' || !this.segment.idsegment) {
        return;
      }
      SegmentSelector_store.toggleStarredSegmentById(this.segment.idsegment);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/StarButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/StarButton.vue



StarButtonvue_type_script_lang_ts.render = StarButtonvue_type_template_id_abb7f9e4_render

/* harmony default export */ var StarButton = (StarButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/EditButton.vue?vue&type=template&id=5e77ddf7

const EditButtonvue_type_template_id_5e77ddf7_hoisted_1 = ["title", "data-state"];
function EditButtonvue_type_template_id_5e77ddf7_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.segment.showEditButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    class: "segmentAction editSegment",
    title: _ctx.segment.editTitle,
    "data-state": _ctx.segment.editState,
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.dispatchOpenEvent(_ctx.segment), ["stop", "prevent"]))
  }, null, 8, EditButtonvue_type_template_id_5e77ddf7_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/EditButton.vue?vue&type=template&id=5e77ddf7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/EditButton.vue?vue&type=script&lang=ts

/* harmony default export */ var EditButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'EditButton',
  props: {
    segment: {
      type: Object,
      required: true
    }
  },
  emits: ['openEditButton'],
  methods: {
    // This should be replaced with a direct call to store to open edit modal once we have migrated
    // it to its own vue component. For now we need this to dispatch the event to Segmentation.js
    dispatchOpenEvent() {
      if (this.segment.editState === 'disabled' || !this.segment.idsegment) {
        return;
      }
      this.$emit('openEditButton', this.segment.idsegment);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/EditButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/EditButton.vue



EditButtonvue_type_script_lang_ts.render = EditButtonvue_type_template_id_5e77ddf7_render

/* harmony default export */ var EditButton = (EditButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/CompareButton.vue?vue&type=template&id=3c8b94d6

const CompareButtonvue_type_template_id_3c8b94d6_hoisted_1 = ["title", "data-state"];
function CompareButtonvue_type_template_id_3c8b94d6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_CompareIcon = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CompareIcon");
  return _ctx.segment.showCompareButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([_ctx.segment.compareButtonClass, {
      isAnonymous: _ctx.isAnonymous
    }]),
    title: _ctx.segment.compareTitle,
    "data-state": _ctx.segment.compareState,
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.dispatchToggleEvent(), ["stop", "prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CompareIcon, {
    state: _ctx.segment.compareState
  }, null, 8, ["state"])], 10, CompareButtonvue_type_template_id_3c8b94d6_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/CompareButton.vue?vue&type=template&id=3c8b94d6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/CompareIcon.vue?vue&type=template&id=30cdb480

const CompareIconvue_type_template_id_30cdb480_hoisted_1 = {
  xmlns: "http://www.w3.org/2000/svg",
  width: "18",
  height: "18",
  viewBox: "0 0 18 18",
  "aria-hidden": "true"
};
const CompareIconvue_type_template_id_30cdb480_hoisted_2 = ["fill"];
const CompareIconvue_type_template_id_30cdb480_hoisted_3 = ["fill"];
function CompareIconvue_type_template_id_30cdb480_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", CompareIconvue_type_template_id_30cdb480_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    d: "M0.78125 2H7.78125V16H0.78125L3.00852 9L0.78125 2Z",
    fill: _ctx.iconFill,
    stroke: "currentColor",
    "stroke-width": "1.5",
    "stroke-linecap": "round",
    "stroke-linejoin": "round"
  }, null, 8, CompareIconvue_type_template_id_30cdb480_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    d: "M10.2188 2H17.2188L14.9915 9L17.2188 16H10.2188V2Z",
    fill: _ctx.iconFill,
    stroke: "currentColor",
    "stroke-width": "1.5",
    "stroke-linecap": "round",
    "stroke-linejoin": "round"
  }, null, 8, CompareIconvue_type_template_id_30cdb480_hoisted_3)]);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/CompareIcon.vue?vue&type=template&id=30cdb480

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/CompareIcon.vue?vue&type=script&lang=ts

/* harmony default export */ var CompareIconvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'CompareIcon',
  props: {
    state: {
      type: String,
      required: true
    }
  },
  computed: {
    iconFill() {
      return this.state === 'active' ? 'currentColor' : 'transparent';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/CompareIcon.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/CompareIcon.vue



CompareIconvue_type_script_lang_ts.render = CompareIconvue_type_template_id_30cdb480_render

/* harmony default export */ var CompareIcon = (CompareIconvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/Buttons/CompareButton.vue?vue&type=script&lang=ts


/* harmony default export */ var CompareButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'CompareButton',
  components: {
    CompareIcon: CompareIcon
  },
  props: {
    segment: {
      type: Object,
      required: true
    },
    isAnonymous: {
      type: Boolean,
      default: false
    }
  },
  emits: ['toggleCompareButton'],
  methods: {
    // This should be replaced with a direct call to the store to toggle the comparison once the
    // add/edit segment modal is migrated to its own vue component, since that migration will
    // introduce the store-driven panel close mechanism this action depends on. For now we need
    // this to dispatch the event to Segmentation.js.
    dispatchToggleEvent() {
      if (this.segment.compareState === 'disabled' || typeof this.segment.definition === 'undefined') {
        return;
      }
      this.$emit('toggleCompareButton', this.segment.definition);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/CompareButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/Buttons/CompareButton.vue



CompareButtonvue_type_script_lang_ts.render = CompareButtonvue_type_template_id_3c8b94d6_render

/* harmony default export */ var CompareButton = (CompareButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=script&lang=ts






/* harmony default export */ var SegmentSelectorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SegmentSelector',
  components: {
    CompareButton: CompareButton,
    EditButton: EditButton,
    SearchInput: external_CoreHome_["SearchInput"],
    StarButton: StarButton
  },
  data() {
    return {
      filterTimer: null,
      panelContainer: null,
      searchInput: '',
      debouncedSearchInput: '',
      starAnimationClasses: {},
      unsubscribeStarChange: null
    };
  },
  computed: {
    viewModel() {
      if (!SegmentSelector_store.state.value.isInitialized) {
        return null;
      }
      const filterValue = this.debouncedSearchInput.length >= 2 ? this.debouncedSearchInput : '';
      return SegmentSelector_store.getSelectorViewModel(filterValue);
    }
  },
  mounted() {
    const root = this.$refs.root;
    this.panelContainer = root.closest('.segmentListContainer');
    if (this.panelContainer) {
      this.panelContainer.addEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }
    this.unsubscribeStarChange = SegmentSelector_store.onStarChange((segment, isError) => {
      const segmentId = `${segment.idsegment || ''}`;
      if (!segmentId) {
        return;
      }
      this.starAnimationClasses = Object.assign(Object.assign({}, this.starAnimationClasses), {}, {
        [segmentId]: isError ? 'segmentStarErrorAnimation' : 'segmentStarAnimation'
      });
    });
  },
  beforeUnmount() {
    if (this.panelContainer) {
      this.panelContainer.removeEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }
    if (this.unsubscribeStarChange) {
      this.unsubscribeStarChange();
      this.unsubscribeStarChange = null;
    }
    if (this.filterTimer) {
      window.clearTimeout(this.filterTimer);
      this.filterTimer = null;
    }
  },
  watch: {
    searchInput(newValue) {
      this.onSearchInput(newValue);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    dispatchPanelEvent(eventName, detail) {
      if (!this.panelContainer) {
        return;
      }
      this.panelContainer.dispatchEvent(new CustomEvent(eventName, {
        bubbles: true,
        detail
      }));
    },
    togglePanel() {
      this.dispatchPanelEvent('SegmentEditor:toggle-panel');
    },
    selectSegment(entry) {
      if (entry.type !== 'segment') {
        return;
      }
      if (!entry.definition && entry.definition !== '') {
        return;
      }
      this.dispatchPanelEvent('SegmentEditor:select-segment', {
        definition: entry.definition
      });
    },
    toggleComparison(definition) {
      this.dispatchPanelEvent('SegmentEditor:toggle-comparison', {
        definition
      });
    },
    openEditSegment(id) {
      this.dispatchPanelEvent('SegmentEditor:open-edit-segment', {
        idSegment: id
      });
    },
    openAddSegment() {
      this.dispatchPanelEvent('SegmentEditor:open-add-segment');
    },
    getEntryClasses(entry) {
      const baseClasses = Array.isArray(entry.classes) ? entry.classes.join(' ') : entry.classes || '';
      const animationClass = entry.idsegment ? this.starAnimationClasses[`${entry.idsegment}`] || '' : '';
      return [baseClasses, animationClass].filter(Boolean).join(' ');
    },
    clearStarAnimationClass(entry) {
      if (!entry.idsegment) {
        return;
      }
      const segmentId = `${entry.idsegment}`;
      if (!this.starAnimationClasses[segmentId]) {
        return;
      }
      const classes = Object.assign({}, this.starAnimationClasses);
      delete classes[segmentId];
      this.starAnimationClasses = classes;
    },
    onSearchInputUpdate(value) {
      if (!value) {
        this.clearSearch();
      }
    },
    onSearchInput(value) {
      this.onSearchInputUpdate(value);
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
      }
      this.filterTimer = window.setTimeout(() => {
        this.debouncedSearchInput = value;
        SegmentSelector_store.notifyChange();
      }, 500);
    },
    clearSearch() {
      this.searchInput = '';
      this.debouncedSearchInput = '';
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
        this.filterTimer = null;
      }
      SegmentSelector_store.notifyChange();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue



SegmentSelectorvue_type_script_lang_ts.render = SegmentSelectorvue_type_template_id_75417757_render

/* harmony default export */ var SegmentSelector = (SegmentSelectorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/index.ts
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
//# sourceMappingURL=SegmentEditor.umd.js.map