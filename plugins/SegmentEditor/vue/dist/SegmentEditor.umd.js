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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=template&id=d27e660c

var _hoisted_1 = {
  class: "segment-generator",
  ref: "root"
};
var _hoisted_2 = {
  class: "segment-rows"
};
var _hoisted_3 = {
  class: "segment-row"
};
var _hoisted_4 = ["onClick"];
var _hoisted_5 = {
  href: "#",
  class: "segment-loading"
};
var _hoisted_6 = {
  class: "segment-row-inputs valign-wrapper"
};
var _hoisted_7 = {
  class: "segment-input metricListBlock valign-wrapper"
};
var _hoisted_8 = {
  style: {
    "width": "100%"
  }
};
var _hoisted_9 = {
  class: "segment-input metricMatchBlock valign-wrapper"
};
var _hoisted_10 = {
  style: {
    "display": "inline-block"
  }
};
var _hoisted_11 = {
  class: "segment-input metricValueBlock valign-wrapper"
};
var _hoisted_12 = {
  class: "form-group row",
  style: {
    "width": "100%"
  }
};
var _hoisted_13 = {
  class: "input-field col s12"
};

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  role: "status",
  "aria-live": "polite",
  class: "ui-helper-hidden-accessible"
}, null, -1);

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);

var _hoisted_16 = {
  class: "segment-or"
};
var _hoisted_17 = ["onClick"];
var _hoisted_18 = ["innerHTML"];
var _hoisted_19 = {
  class: "segment-and"
};
var _hoisted_20 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ValueInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ValueInput");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading
  }, null, 8, ["loading"]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.conditions, function (condition, conditionIndex) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("segmentRow".concat(conditionIndex)),
      key: conditionIndex
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(condition.orConditions, function (orCondition, orConditionIndex) {
      var _ctx$segments$orCondi, _ctx$segments$orCondi2;

      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("orCondId".concat(orCondition.id)),
        key: orConditionIndex
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        class: "segment-close",
        onClick: function onClick($event) {
          return _ctx.removeOrCondition(condition, orCondition);
        }
      }, null, 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_5, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.conditionValuesLoading[orCondition.id]]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "expandable-select",
        name: "segments",
        "model-value": orCondition.segment,
        "onUpdate:modelValue": function onUpdateModelValue($event) {
          orCondition.segment = $event;

          _ctx.updateAutocomplete(orCondition);

          _ctx.computeSegmentDefinition();
        },
        title: (_ctx$segments$orCondi = _ctx.segments[orCondition.segment]) === null || _ctx$segments$orCondi === void 0 ? void 0 : _ctx$segments$orCondi.name,
        "full-width": true,
        options: _ctx.segmentList
      }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "matchType",
        "model-value": orCondition.matches,
        "onUpdate:modelValue": function onUpdateModelValue($event) {
          orCondition.matches = $event;

          _ctx.computeSegmentDefinition();
        },
        "full-width": true,
        options: _ctx.matches[(_ctx$segments$orCondi2 = _ctx.segments[orCondition.segment]) === null || _ctx$segments$orCondi2 === void 0 ? void 0 : _ctx$segments$orCondi2.type]
      }, null, 8, ["model-value", "onUpdate:modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ValueInput, {
        or: orCondition,
        onUpdate: function onUpdate($event) {
          orCondition.value = $event; // deep watch doesn't catch this change

          _this.computeSegmentDefinition();
        }
      }, null, 8, ["or", "onUpdate"])])])]), _hoisted_15])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_OperatorOR')), 1)], 2);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "segment-add-or",
      onClick: function onClick($event) {
        return _ctx.addNewOrCondition(condition);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      innerHTML: _ctx.$sanitize(_ctx.addNewOrConditionLinkText)
    }, null, 8, _hoisted_18)])], 8, _hoisted_17)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_OperatorAND')), 1)], 2);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "segment-add-row initial",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.addNewAndCondition();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    innerHTML: _ctx.$sanitize(_ctx.addNewAndConditionLinkText)
  }, null, 8, _hoisted_20)])])], 512);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=template&id=d27e660c

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.store.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var SegmentGenerator_store_SegmentGeneratorStore = /*#__PURE__*/function () {
  function SegmentGeneratorStore() {
    var _this = this;

    _classCallCheck(this, SegmentGeneratorStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      segments: []
    }));

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "loadSegmentsAbort", void 0);

    _defineProperty(this, "loadSegmentsPromise", void 0);

    _defineProperty(this, "fetchedSiteId", void 0);
  }

  _createClass(SegmentGeneratorStore, [{
    key: "loadSegments",
    value: function loadSegments(siteId, visitSegmentsOnly) {
      var _this2 = this;

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
        var idSites = undefined;
        var idSite = undefined;

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
          idSites: idSites,
          idSite: idSite
        });
      }

      return this.loadSegmentsPromise.then(function (response) {
        _this2.privateState.isLoading = false;

        if (response) {
          if (visitSegmentsOnly) {
            _this2.privateState.segments = response.filter(function (s) {
              return s.sqlSegment && s.sqlSegment.match(/log_visit\./);
            });
          } else {
            _this2.privateState.segments = response;
          }
        }

        return _this2.state.value.segments;
      }).finally(function () {
        _this2.privateState.isLoading = false;
      });
    }
  }]);

  return SegmentGeneratorStore;
}();

/* harmony default export */ var SegmentGenerator_store = (new SegmentGenerator_store_SegmentGeneratorStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=template&id=17b64c0b

var ValueInputvue_type_template_id_17b64c0b_hoisted_1 = ["placeholder", "title", "value"];
function ValueInputvue_type_template_id_17b64c0b_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
    placeholder: _ctx.translate('General_Value'),
    type: "text",
    class: "autocomplete",
    title: _ctx.translate('General_Value'),
    autocomplete: "off",
    value: _ctx.or.value,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydownOrConditionValue($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydownOrConditionValue($event);
    })
  }, null, 40, ValueInputvue_type_template_id_17b64c0b_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=template&id=17b64c0b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=script&lang=ts


/* harmony default export */ var ValueInputvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    or: Object
  },
  created: function created() {
    this.onKeydownOrConditionValue = Object(external_CoreHome_["debounce"])(this.onKeydownOrConditionValue, 50);
  },
  emits: ['update'],
  methods: {
    onKeydownOrConditionValue: function onKeydownOrConditionValue(event) {
      this.$emit('update', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/ValueInput.vue



ValueInputvue_type_script_lang_ts.render = ValueInputvue_type_template_id_17b64c0b_render

/* harmony default export */ var ValueInput = (ValueInputvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=script&lang=ts






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
  var id = '';
  var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

  for (var i = 1; i <= 10; i += 1) {
    id += chars.charAt(Math.floor(Math.random() * chars.length));
  }

  return id;
}

function findAndExplodeByMatch(metric) {
  var matches = ['==', '!=', '<=', '>=', '=@', '!@', '<', '>', '=^', '=$'];
  var newMetric = {};
  var minPos = metric.length;
  var match;
  var index;
  var singleChar = false;

  for (var key = 0; key < matches.length; key += 1) {
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
    } // if value is only '' -> change to empty string


    if (newMetric.value === '""') {
      newMetric.value = '';
    }
  }

  try {
    // Decode again to deal with double-encoded segments in database
    newMetric.value = decodeURIComponent(newMetric.value);
  } catch (e) {// Expected if the segment was not double-encoded
  }

  return newMetric;
}

function stripTags(text) {
  return text ? "".concat(text).replace(/(<([^>]+)>)/ig, '') : text;
}

var _window = window,
    $ = _window.$;
/* harmony default export */ var SegmentGeneratorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    addInitialCondition: Boolean,
    visitSegmentsOnly: Boolean,
    idsite: {
      type: [String, Number],
      default: function _default() {
        return external_CoreHome_["Matomo"].idSite;
      }
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    ValueInput: ValueInput
  },
  data: function data() {
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
    modelValue: function modelValue(newVal) {
      if ((newVal || '') !== (this.segmentDefinition || '')) {
        this.setSegmentString(newVal);
      }
    },
    conditions: {
      deep: true,
      handler: function handler() {
        this.computeSegmentDefinition();
      }
    },
    segmentDefinition: function segmentDefinition(newVal) {
      if ((newVal || '') !== (this.modelValue || '')) {
        this.$emit('update:modelValue', newVal);
      }
    },
    idsite: function idsite(newVal) {
      this.reloadSegments(newVal, this.visitSegmentsOnly);
    }
  },
  created: function created() {
    this.matches[''] = this.matches.dimension;
    this.setSegmentString(this.modelValue);
    this.segmentDefinition = this.modelValue;
    this.reloadSegments(this.idsite, this.visitSegmentsOnly);
  },
  methods: {
    reloadSegments: function reloadSegments(idsite, visitSegmentsOnly) {
      var _this = this;

      SegmentGenerator_store.loadSegments(idsite, visitSegmentsOnly).then(function (segments) {
        _this.queriedSegments = segments.map(function (s) {
          return Object.assign(Object.assign({}, s), {}, {
            category: s.category || 'Others'
          });
        });

        if (_this.addInitialCondition && _this.conditions.length === 0) {
          _this.addNewAndCondition();
        }
      });
    },
    addAndCondition: function addAndCondition(condition) {
      this.conditions.push(condition);
    },
    addNewOrCondition: function addNewOrCondition(condition) {
      var orCondition = {
        segment: this.firstSegment,
        matches: this.firstMatch,
        value: ''
      };
      this.addOrCondition(condition, orCondition);
    },
    addOrCondition: function addOrCondition(condition, orCondition) {
      var _this2 = this;

      this.conditionValuesLoading[orCondition.id] = false;
      orCondition.id = generateUniqueId();
      condition.orConditions.push(orCondition);
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        _this2.updateAutocomplete(orCondition);
      });
    },
    updateAutocomplete: function updateAutocomplete(orCondition) {
      var _this3 = this;

      this.conditionValuesLoading[orCondition.id] = true;
      $(".orCondId".concat(orCondition.id, " .metricValueBlock input"), this.$refs.root).autocomplete({
        source: [],
        minLength: 0
      });
      var abortController = new AbortController();
      var resolved = false;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'API',
        format: 'json',
        method: 'API.getSuggestedValuesForSegment',
        segmentName: orCondition.segment
      }).then(function (response) {
        _this3.conditionValuesLoading[orCondition.id] = false;
        resolved = true;
        var inputElement = $(".orCondId".concat(orCondition.id, " .metricValueBlock input")).autocomplete({
          source: response,
          minLength: 0,
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          select: function select(event, ui) {
            event.preventDefault();
            orCondition.value = ui.item.value;

            _this3.computeSegmentDefinition(); // deep watch doesn't catch this change


            _this3.$forceUpdate();
          }
        }).off('click').click(function () {
          $(inputElement).autocomplete('search', orCondition.value);
        });
      }).catch(function () {
        resolved = true;
        _this3.conditionValuesLoading[orCondition.id] = false;
        $(".orCondId".concat(orCondition.id, " .metricValueBlock input")).autocomplete({
          source: [],
          minLength: 0
        }).autocomplete('search', orCondition.value);
      });
      setTimeout(function () {
        if (!resolved) {
          abortController.abort();
        }
      }, 20000);
    },
    removeOrCondition: function removeOrCondition(condition, orCondition) {
      var index = condition.orConditions.indexOf(orCondition);

      if (index > -1) {
        condition.orConditions.splice(index, 1);
      }

      if (condition.orConditions.length === 0) {
        var andCondIndex = this.conditions.indexOf(condition);

        if (index > -1) {
          this.conditions.splice(andCondIndex, 1);
        }
      }
    },
    setSegmentString: function setSegmentString(segmentStr) {
      var _this4 = this;

      this.conditions = [];

      if (!segmentStr) {
        return;
      }

      var blocks = segmentStr.split(';').map(function (b) {
        return b.split(',');
      });
      this.conditions = blocks.map(function (block) {
        var condition = {
          orConditions: []
        };
        block.forEach(function (innerBlock) {
          var orCondition = findAndExplodeByMatch(innerBlock);

          _this4.addOrCondition(condition, orCondition);
        });
        return condition;
      });
    },
    addNewAndCondition: function addNewAndCondition() {
      var condition = {
        orConditions: []
      };
      this.addAndCondition(condition);
      this.addNewOrCondition(condition);
      return condition;
    },
    // NOTE: can't use a computed property since we need to recompute on changes inside the
    //       structure. don't have to if we don't do in-place changes, but with nested structures,
    //       that's complicated.
    computeSegmentDefinition: function computeSegmentDefinition() {
      var segmentStr = '';
      this.conditions.forEach(function (condition) {
        if (!condition.orConditions.length) {
          return;
        }

        var subSegmentStr = '';
        condition.orConditions.forEach(function (orCondition) {
          if (!orCondition.value && !orCondition.segment && !orCondition.matches) {
            return;
          }

          if (subSegmentStr !== '') {
            subSegmentStr += ','; // OR operator
          } // one encode for urldecode on value, one encode for urldecode on condition


          var value = encodeURIComponent(encodeURIComponent(orCondition.value));
          subSegmentStr += "".concat(orCondition.segment).concat(orCondition.matches).concat(value);
        });

        if (segmentStr !== '') {
          segmentStr += ';'; // add AND operator between segment blocks
        }

        segmentStr += subSegmentStr;
      });
      this.segmentDefinition = segmentStr;
    }
  },
  computed: {
    firstSegment: function firstSegment() {
      return this.queriedSegments[0].segment;
    },
    firstMatch: function firstMatch() {
      var segment = this.queriedSegments[0];

      if (!segment) {
        return null;
      }

      if (segment.type && this.matches[segment.type]) {
        return this.matches[segment.type][0].key;
      }

      return this.matches[''][0].key;
    },
    segments: function segments() {
      var result = {};
      this.queriedSegments.forEach(function (s) {
        result[s.segment] = s;
      });
      return result;
    },
    segmentList: function segmentList() {
      return this.queriedSegments.map(function (s) {
        return {
          group: s.category,
          key: s.segment,
          value: s.name,
          tooltip: s.acceptedValues ? stripTags(s.acceptedValues) : undefined
        };
      });
    },
    addNewOrConditionLinkText: function addNewOrConditionLinkText() {
      return "+".concat(Object(external_CoreHome_["translate"])('SegmentEditor_AddANDorORCondition', "<span>".concat(Object(external_CoreHome_["translate"])('SegmentEditor_OperatorOR'), "</span>")));
    },
    andConditionLabel: function andConditionLabel() {
      return this.conditions.length ? Object(external_CoreHome_["translate"])('SegmentEditor_OperatorAND') : '';
    },
    addNewAndConditionLinkText: function addNewAndConditionLinkText() {
      return "+".concat(Object(external_CoreHome_["translate"])('SegmentEditor_AddANDorORCondition', "<span>".concat(this.andConditionLabel, "</span>")));
    },
    isLoading: function isLoading() {
      return SegmentGenerator_store.state.value.isLoading;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.vue



SegmentGeneratorvue_type_script_lang_ts.render = render

/* harmony default export */ var SegmentGenerator = (SegmentGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/SegmentGenerator/SegmentGenerator.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/* harmony default export */ var SegmentGenerator_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SegmentGenerator,
  require: '?ngModel',
  scope: {
    segmentDefinition: {
      angularJsBind: '@',
      vue: 'modelValue'
    },
    addInitialCondition: {
      angularJsBind: '=',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    visitSegmentsOnly: {
      angularJsBind: '=',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    idsite: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikSegmentGenerator',
  $inject: ['$timeout'],
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel, $timeout) {
      var currentValue = ngModel ? ngModel.$viewValue : scope.segmentDefinition;

      if (newValue !== currentValue) {
        $timeout(function () {
          if (!ngModel) {
            scope.segmentDefinition = newValue;
            return;
          } // ngModel being used


          ngModel.$setViewValue(newValue);
          ngModel.$render(); // not detected by the watch for some reason
        });
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    // methods to forward for BC
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    element.scope().segmentGenerator = {
      getSegmentString: function getSegmentString() {
        return vm.modelValue;
      }
    };
    var ngModel = controller;

    if (!ngModel) {
      scope.$watch('segmentDefinition', function (newVal) {
        if (newVal !== vm.modelValue) {
          Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
            vm.modelValue = newVal;
          });
        }
      });
      return;
    } // ngModel being used


    ngModel.$render = function () {
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        vm.modelValue = Object(external_CoreHome_["removeAngularJsSpecificProperties"])(ngModel.$viewValue);
      });
    };

    if (typeof scope.segmentDefinition !== 'undefined') {
      ngModel.$setViewValue(scope.segmentDefinition);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./plugins/SegmentEditor/vue/src/index.ts
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
//# sourceMappingURL=SegmentEditor.umd.js.map