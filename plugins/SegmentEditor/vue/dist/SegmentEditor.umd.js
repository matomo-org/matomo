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
__webpack_require__.d(__webpack_exports__, "SegmentSelector", function() { return /* reexport */ SegmentSelector; });

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
    _defineProperty(this, "loadSegmentsAbort", void 0);
    _defineProperty(this, "loadSegmentsPromise", void 0);
    _defineProperty(this, "fetchedSiteId", void 0);
  }
  loadSegments(siteId, visitSegmentsOnly) {
    if (this.loadSegmentsAbort) {
      this.loadSegmentsAbort.abort();
      this.loadSegmentsAbort = undefined;
    }
    this.privateState.isLoading = true;
    if (this.fetchedSiteId !== siteId) {
      this.loadSegmentsAbort = undefined;
      this.fetchedSiteId = siteId;
    }
    if (!this.loadSegmentsPromise) {
      let idSites = undefined;
      let idSite = undefined;
      if (siteId === 'all' || !siteId) {
        idSites = 'all';
        idSite = 'all';
      } else if (siteId) {
        idSites = siteId;
        idSite = siteId;
      }
      this.loadSegmentsAbort = new AbortController();
      this.loadSegmentsPromise = external_CoreHome_["AjaxHelper"].fetch({
        method: 'API.getSegmentsMetadata',
        filter_limit: '-1',
        _hideImplementationData: 0,
        idSites,
        idSite
      });
    }
    return this.loadSegmentsPromise.then(response => {
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
      delete this.loadSegmentsPromise;
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=template&id=109ce624

const SegmentSelectorvue_type_template_id_109ce624_hoisted_1 = {
  ref: "root"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_2 = {
  key: 0,
  class: "segmentationContainer listHtml"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_3 = ["title"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-segment"
}, null, -1);
const SegmentSelectorvue_type_template_id_109ce624_hoisted_5 = {
  class: "dropdown dropdown-body"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_6 = {
  class: "segmentFilterContainer"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_7 = ["value", "placeholder"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_8 = {
  class: "submenu"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_9 = {
  class: "segmentList"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const SegmentSelectorvue_type_template_id_109ce624_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SegmentSelectorvue_type_template_id_109ce624_hoisted_12 = ["data-idsegment", "data-definition"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_13 = ["title", "onClick", "onKeyup"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_14 = ["data-star", "title", "data-state", "onClick"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_15 = {
  xmlns: "http://www.w3.org/2000/svg",
  width: "16",
  height: "16",
  viewBox: "0 0 24 24"
};
const SegmentSelectorvue_type_template_id_109ce624_hoisted_16 = ["d"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_17 = ["title", "data-state", "onClick"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_18 = ["title", "data-state", "onClick"];
const SegmentSelectorvue_type_template_id_109ce624_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const SegmentSelectorvue_type_template_id_109ce624_hoisted_20 = ["href"];
const _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_22 = {
  class: "submenu"
};
const _hoisted_23 = {
  key: 0,
  class: "youMustBeLoggedIn"
};
const _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_25 = ["href"];
const _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function SegmentSelectorvue_type_template_id_109ce624_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentSelectorvue_type_template_id_109ce624_hoisted_1, [_ctx.viewModel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SegmentSelectorvue_type_template_id_109ce624_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "title",
    tabindex: "4",
    title: _ctx.viewModel.currentSegmentTooltip,
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.togglePanel && _ctx.togglePanel(...args), ["prevent"]))
  }, [SegmentSelectorvue_type_template_id_109ce624_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["segmentationTitle", {
      'segment-clicked': !!_ctx.viewModel.currentSegmentValue
    }])
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.viewModel.currentSegmentTitle), 3)], 8, SegmentSelectorvue_type_template_id_109ce624_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_109ce624_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_109ce624_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "segmentFilter browser-default",
    type: "text",
    tabindex: "4",
    value: _ctx.searchInput,
    placeholder: _ctx.translate('General_Search'),
    onInput: _cache[1] || (_cache[1] = (...args) => _ctx.onSearchInput && _ctx.onSearchInput(...args))
  }, null, 40, SegmentSelectorvue_type_template_id_109ce624_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.clearSearch && _ctx.clearSearch(...args), ["prevent"]))
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", SegmentSelectorvue_type_template_id_109ce624_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_SelectSegmentOfVisits')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SegmentSelectorvue_type_template_id_109ce624_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.viewModel.entries, entry => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: entry.key
    }, [entry.type === 'header' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(entry.className)
    }, [SegmentSelectorvue_type_template_id_109ce624_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.label) + ": ", 1), SegmentSelectorvue_type_template_id_109ce624_hoisted_11], 2)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 1,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(entry.classes),
      "data-idsegment": entry.idsegment,
      "data-definition": entry.definition
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "segname",
      tabindex: "4",
      title: entry.tooltip,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.selectSegment(entry), ["prevent"]),
      onKeyup: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.selectSegment(entry), ["prevent"]), ["enter"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.label), 41, SegmentSelectorvue_type_template_id_109ce624_hoisted_13), entry.type === 'segment' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [entry.showStarButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      "data-star": entry.idsegment,
      class: "segmentAction starSegment",
      title: entry.starTitle,
      "data-state": entry.starState,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleStar(entry), ["stop", "prevent"])
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", SegmentSelectorvue_type_template_id_109ce624_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      stroke: "black",
      "stroke-width": "3",
      fill: "none",
      d: _ctx.starPath
    }, null, 8, SegmentSelectorvue_type_template_id_109ce624_hoisted_16)]))], 8, SegmentSelectorvue_type_template_id_109ce624_hoisted_14)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.showEditButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 1,
      class: "segmentAction editSegment",
      title: entry.editTitle,
      "data-state": entry.editState,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.openEditSegment(entry), ["stop", "prevent"])
    }, null, 8, SegmentSelectorvue_type_template_id_109ce624_hoisted_17)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.showCompareButton ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 2,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(entry.compareButtonClass),
      title: entry.compareTitle,
      "data-state": entry.compareState,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleComparison(entry), ["stop", "prevent"])
    }, null, 10, SegmentSelectorvue_type_template_id_109ce624_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, SegmentSelectorvue_type_template_id_109ce624_hoisted_12))], 64);
  }), 128))])])])]), _ctx.viewModel.authorizedToCreateSegments ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    tabindex: "4",
    class: "add_new_segment btn",
    onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.openAddSegment && _ctx.openAddSegment(...args), ["stop", "prevent"]))
  }, [SegmentSelectorvue_type_template_id_109ce624_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_AddNewSegment')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.viewModel.manageSegmentsUrl,
    tabindex: "4",
    class: "btn btn-block btn-outline"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_ManageSegments')), 9, SegmentSelectorvue_type_template_id_109ce624_hoisted_20)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [_ctx.viewModel.isUserAnonymous ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_YouMustBeLoggedInToCreateSegments')) + " ", 1), _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" › "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.viewModel.loginUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Login_LogIn')), 9, _hoisted_25)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _hoisted_26, _hoisted_27], 64))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=template&id=109ce624

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=script&lang=ts


const starPath = 'M9.153 5.408C10.42 3.136 11.053 2 12 2c.947 0 1.58 1.136 2.847 3.408l.328.588c.36.646.54.969.82 1.182.28.213.63.292 1.33.45l.636.144c2.46.557 3.689.835 3.982 1.776.292.94-.546 1.921-2.223 3.882l-.434.507c-.476.557-.715.836-.822 1.18-.107.345-.071.717.001 1.46l.066.677c.253 2.617.38 3.925-.386 4.506-.766.582-1.918.051-4.22-1.009l-.597-.274c-.654-.302-.981-.452-1.328-.452-.347 0-.674.15-1.329.452l-.595.274c-2.303 1.06-3.455 1.59-4.22 1.01-.767-.582-.64-1.89-.387-4.507l.066-.676c.072-.744.108-1.116 0-1.46-.106-.345-.345-.624-.821-1.18l-.434-.508c-1.677-1.96-2.515-2.941-2.223-3.882.293-.941 1.523-1.22 3.983-1.776l.636-.144c.699-.158 1.048-.237 1.329-.45.28-.213.46-.536.82-1.182l.328-.588Z';
/* harmony default export */ var SegmentSelectorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'SegmentSelector',
  data() {
    return {
      bridge: null,
      bridgeContainer: null,
      bridgePanel: null,
      filterTimer: null,
      searchInput: '',
      starPath,
      unsubscribe: null,
      viewModel: null
    };
  },
  mounted() {
    const root = this.$refs.root;
    this.bridgeContainer = root.closest('.segmentListContainer');
    this.bridgePanel = root.closest('.segmentEditorPanel');
    if (this.bridgeContainer) {
      this.bridgeContainer.addEventListener('SegmentEditor.bridgeReady', this.attachBridge);
      this.bridgeContainer.addEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }
    this.attachBridge();
  },
  beforeUnmount() {
    if (this.bridgeContainer) {
      this.bridgeContainer.removeEventListener('SegmentEditor.bridgeReady', this.attachBridge);
      this.bridgeContainer.removeEventListener('SegmentEditor.resetFilter', this.clearSearch);
    }
    if (this.unsubscribe) {
      this.unsubscribe();
      this.unsubscribe = null;
    }
    if (this.filterTimer) {
      window.clearTimeout(this.filterTimer);
      this.filterTimer = null;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    attachBridge() {
      var _window$matomoPluginS;
      if (!this.bridgeContainer) {
        return;
      }
      const bridgeId = this.bridgeContainer.getAttribute('data-segment-bridge-id') || '';
      const bridges = ((_window$matomoPluginS = window.matomoPluginSegmentEditor) === null || _window$matomoPluginS === void 0 ? void 0 : _window$matomoPluginS.bridges) || {};
      const bridge = bridgeId ? bridges[bridgeId] : null;
      if (!bridge || bridge === this.bridge) {
        if (bridge) {
          this.refreshViewModel();
        }
        return;
      }
      if (this.unsubscribe) {
        this.unsubscribe();
      }
      this.bridge = bridge;
      this.unsubscribe = bridge.onStateChange(() => {
        this.refreshViewModel();
      });
      this.refreshViewModel();
    },
    refreshViewModel() {
      if (!this.bridge) {
        return;
      }
      const filterValue = this.searchInput.length >= 2 ? this.searchInput : '';
      this.viewModel = this.bridge.getViewModel(filterValue);
    },
    togglePanel() {
      if (this.bridge) {
        this.bridge.togglePanel();
      }
    },
    selectSegment(entry) {
      if (entry.type !== 'segment') {
        return;
      }
      if (!entry.definition && entry.definition !== '') {
        return;
      }
      if (this.bridge) {
        this.bridge.selectSegment(entry.definition);
      }
    },
    toggleStar(entry) {
      if (entry.starState === 'disabled' || !entry.idsegment) {
        return;
      }
      if (this.bridge) {
        this.bridge.toggleStarredSegmentById(entry.idsegment);
      }
    },
    toggleComparison(entry) {
      if (entry.compareState === 'disabled' || typeof entry.definition === 'undefined') {
        return;
      }
      if (this.bridge) {
        this.bridge.toggleComparisonByDefinition(entry.definition);
      }
    },
    openEditSegment(entry) {
      if (entry.editState === 'disabled' || !entry.idsegment) {
        return;
      }
      if (this.bridge) {
        this.bridge.openEditSegment(entry.idsegment);
      }
    },
    openAddSegment() {
      if (this.bridge) {
        this.bridge.openAddSegment();
      }
    },
    onSearchInput(event) {
      const target = event.target;
      this.searchInput = (target === null || target === void 0 ? void 0 : target.value) || '';
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
      }
      this.filterTimer = window.setTimeout(() => {
        this.refreshViewModel();
      }, 500);
    },
    clearSearch() {
      this.searchInput = '';
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
        this.filterTimer = null;
      }
      this.refreshViewModel();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentSelector/SegmentSelector.vue



SegmentSelectorvue_type_script_lang_ts.render = SegmentSelectorvue_type_template_id_109ce624_render

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