(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Installation"] = factory(require("CoreHome"), require("vue"));
	else
		root["Installation"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Installation/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "SystemCheckPage", function() { return /* reexport */ SystemCheckPage; });
__webpack_require__.d(__webpack_exports__, "SystemCheck", function() { return /* reexport */ SystemCheck; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckPage.vue?vue&type=template&id=b24bc244

var _hoisted_1 = {
  key: 0,
  class: "alert alert-danger"
};
var _hoisted_2 = ["innerHTML"];
var _hoisted_3 = {
  key: 1,
  class: "alert alert-warning"
};
var _hoisted_4 = {
  key: 2,
  class: "alert alert-success"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SystemCheckSection = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SystemCheckSection");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Installation_SystemCheck'),
    feature: "true"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.hasErrors ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.thereWereErrorsText)
      }, null, 8, _hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SeeBelowForMoreInfo')), 1)])) : _ctx.hasWarnings ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SystemCheckSummaryThereWereWarnings')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SeeBelowForMoreInfo')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SystemCheckSummaryNoProblems')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SystemCheckSection, {
        "error-type": _ctx.errorType,
        "warning-type": _ctx.warningType,
        "informational-type": _ctx.informationalType,
        "system-check-info": _ctx.systemCheckInfo,
        "mandatory-results": _ctx.mandatoryResults,
        "optional-results": _ctx.optionalResults,
        "informational-results": _ctx.informationalResults,
        "is-installation": _ctx.isInstallation
      }, null, 8, ["error-type", "warning-type", "informational-type", "system-check-info", "mandatory-results", "optional-results", "informational-results", "is-installation"])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckPage.vue?vue&type=template&id=b24bc244

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckSection.vue?vue&type=template&id=5d6873c0


var SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])();

var SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_4 = ["innerHTML"];
var _hoisted_5 = {
  class: "entityTable system-check",
  id: "systemCheckRequired"
};
var _hoisted_6 = {
  class: "entityTable system-check",
  id: "systemCheckOptional"
};
var _hoisted_7 = {
  class: "entityTable system-check",
  id: "systemCheckInformational"
};
function SystemCheckSectionvue_type_template_id_5d6873c0_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DiagnosticTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DiagnosticTable");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_CopyBelowInfoForSupport')) + " ", 1), SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_1, SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_2, SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.copyInfo();
    }, ["prevent"])),
    class: "btn",
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_CopySystemCheck')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.downloadInfo();
    }, ["prevent"])),
    class: "btn"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_DownloadSystemCheck')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", {
    style: {
      "width": "100%",
      "height": "200px"
    },
    readonly: "",
    id: "matomo_system_check_info",
    ref: "systemCheckInfo",
    innerHTML: _ctx.$sanitize(_ctx.systemCheckInfo)
  }, null, 8, SystemCheckSectionvue_type_template_id_5d6873c0_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DiagnosticTable, {
    results: _ctx.mandatoryResults,
    "informational-type": _ctx.informationalType,
    "warning-type": _ctx.warningType,
    "error-type": _ctx.errorType
  }, null, 8, ["results", "informational-type", "warning-type", "error-type"])])], 512), [[_directive_content_table, {
    off: _ctx.isInstallation
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_Optional')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DiagnosticTable, {
    results: _ctx.optionalResults,
    "informational-type": _ctx.informationalType,
    "warning-type": _ctx.warningType,
    "error-type": _ctx.errorType
  }, null, 8, ["results", "informational-type", "warning-type", "error-type"])])], 512), [[_directive_content_table, {
    off: _ctx.isInstallation
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_InformationalResults')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DiagnosticTable, {
    results: _ctx.informationalResults,
    "informational-type": _ctx.informationalType,
    "warning-type": _ctx.warningType,
    "error-type": _ctx.errorType
  }, null, 8, ["results", "informational-type", "warning-type", "error-type"])])], 512), [[_directive_content_table, {
    off: _ctx.isInstallation
  }]])])], 64);
}
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckSection.vue?vue&type=template&id=5d6873c0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/DiagnosticTable.vue?vue&type=template&id=04e619d1

var DiagnosticTablevue_type_template_id_04e619d1_hoisted_1 = ["innerHTML"];
var DiagnosticTablevue_type_template_id_04e619d1_hoisted_2 = {
  key: 0
};

var DiagnosticTablevue_type_template_id_04e619d1_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);

var DiagnosticTablevue_type_template_id_04e619d1_hoisted_4 = ["innerHTML"];
var DiagnosticTablevue_type_template_id_04e619d1_hoisted_5 = {
  key: 1
};

var DiagnosticTablevue_type_template_id_04e619d1_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);

var DiagnosticTablevue_type_template_id_04e619d1_hoisted_7 = ["innerHTML"];
var _hoisted_8 = {
  key: 2
};

var _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);

var _hoisted_10 = ["innerHTML"];
var _hoisted_11 = {
  key: 3
};

var _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);

var _hoisted_13 = ["innerHTML"];

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_15 = {
  key: 0
};
var _hoisted_16 = ["innerHTML"];
function DiagnosticTablevue_type_template_id_04e619d1_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Passthrough = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Passthrough");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.results, function (result, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Passthrough, {
      key: index
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
        return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          innerHTML: _ctx.$sanitize(result.label)
        }, null, 8, DiagnosticTablevue_type_template_id_04e619d1_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(result.items, function (item, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
            key: index
          }, [item.status === 'error' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", DiagnosticTablevue_type_template_id_04e619d1_hoisted_2, [DiagnosticTablevue_type_template_id_04e619d1_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            class: "err",
            innerHTML: _ctx.$sanitize(typeof item.comment !== 'string' ? '' : item.comment)
          }, null, 8, DiagnosticTablevue_type_template_id_04e619d1_hoisted_4)])) : item.status === 'warning' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", DiagnosticTablevue_type_template_id_04e619d1_hoisted_5, [DiagnosticTablevue_type_template_id_04e619d1_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(typeof item.comment !== 'string' ? '' : item.comment)
          }, null, 8, DiagnosticTablevue_type_template_id_04e619d1_hoisted_7)])) : item.status === 'informational' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_8, [_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(typeof item.comment !== 'string' ? '' : item.comment)
          }, null, 8, _hoisted_10)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_11, [_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(typeof item.comment !== 'string' ? '' : item.comment)
          }, null, 8, _hoisted_13)])), _hoisted_14]);
        }), 128))])]), result.longErrorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          colspan: "2",
          class: "error",
          style: {
            "font-size": "small"
          },
          innerHTML: _ctx.$sanitize(result.longErrorMessage)
        }, null, 8, _hoisted_16)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
      }),
      _: 2
    }, 1024);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/DiagnosticTable.vue?vue&type=template&id=04e619d1

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/DiagnosticTable.vue?vue&type=script&lang=ts


/* harmony default export */ var DiagnosticTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    errorType: {
      type: String,
      required: true
    },
    warningType: {
      type: String,
      required: true
    },
    informationalType: {
      type: String,
      required: true
    },
    results: {
      type: Array,
      required: true
    }
  },
  components: {
    Passthrough: external_CoreHome_["Passthrough"]
  }
}));
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/DiagnosticTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/DiagnosticTable.vue



DiagnosticTablevue_type_script_lang_ts.render = DiagnosticTablevue_type_template_id_04e619d1_render

/* harmony default export */ var DiagnosticTable = (DiagnosticTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckSection.vue?vue&type=script&lang=ts



var _window = window,
    $ = _window.$;
/* harmony default export */ var SystemCheckSectionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    errorType: {
      type: String,
      required: true
    },
    warningType: {
      type: String,
      required: true
    },
    informationalType: {
      type: String,
      required: true
    },
    systemCheckInfo: {
      type: String,
      required: true
    },
    mandatoryResults: {
      type: Array,
      required: true
    },
    optionalResults: {
      type: Array,
      required: true
    },
    informationalResults: {
      type: Array,
      required: true
    },
    isInstallation: Boolean
  },
  components: {
    DiagnosticTable: DiagnosticTable
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  methods: {
    copyInfo: function copyInfo() {
      var textarea = this.$refs.systemCheckInfo;
      textarea.select();
      document.execCommand('copy');
      $(textarea).effect('highlight', {}, 600);
    },
    downloadInfo: function downloadInfo() {
      var textarea = this.$refs.systemCheckInfo;
      external_CoreHome_["Matomo"].helper.sendContentAsDownload('matomo_system_check.txt', textarea.innerHTML);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckSection.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckSection.vue



SystemCheckSectionvue_type_script_lang_ts.render = SystemCheckSectionvue_type_template_id_5d6873c0_render

/* harmony default export */ var SystemCheckSection = (SystemCheckSectionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckPage.vue?vue&type=script&lang=ts



/* harmony default export */ var SystemCheckPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    errorType: {
      type: String,
      required: true
    },
    warningType: {
      type: String,
      required: true
    },
    informationalType: {
      type: String,
      required: true
    },
    systemCheckInfo: {
      type: String,
      required: true
    },
    mandatoryResults: {
      type: Array,
      required: true
    },
    optionalResults: {
      type: Array,
      required: true
    },
    informationalResults: {
      type: Array,
      required: true
    },
    isInstallation: Boolean,
    hasErrors: Boolean,
    hasWarnings: Boolean
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SystemCheckSection: SystemCheckSection
  },
  computed: {
    thereWereErrorsText: function thereWereErrorsText() {
      return Object(external_CoreHome_["translate"])('Installation_SystemCheckSummaryThereWereErrors', '<strong>', '</strong>', '<strong>', '</strong>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckPage.vue



SystemCheckPagevue_type_script_lang_ts.render = render

/* harmony default export */ var SystemCheckPage = (SystemCheckPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheck.vue?vue&type=template&id=7dd80659

var SystemCheckvue_type_template_id_7dd80659_hoisted_1 = {
  key: 0
};

var SystemCheckvue_type_template_id_7dd80659_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", {
  style: {
    "clear": "both"
  }
}, null, -1);

var SystemCheckvue_type_template_id_7dd80659_hoisted_3 = {
  key: 1
};
var SystemCheckvue_type_template_id_7dd80659_hoisted_4 = {
  key: 0
};

var SystemCheckvue_type_template_id_7dd80659_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export"
}, null, -1);

var SystemCheckvue_type_template_id_7dd80659_hoisted_6 = ["href"];
function SystemCheckvue_type_template_id_7dd80659_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SystemCheckLegend = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SystemCheckLegend");

  var _component_SystemCheckSection = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SystemCheckSection");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [!_ctx.showNextStep ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SystemCheckvue_type_template_id_7dd80659_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SystemCheckLegend, {
    url: _ctx.systemCheckLegendUrl
  }, null, 8, ["url"]), SystemCheckvue_type_template_id_7dd80659_hoisted_2])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SystemCheck')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SystemCheckSection, {
    "error-type": _ctx.errorType,
    "warning-type": _ctx.warningType,
    "informational-type": _ctx.informationalType,
    "system-check-info": _ctx.systemCheckInfo,
    "mandatory-results": _ctx.mandatoryResults,
    "optional-results": _ctx.optionalResults,
    "informational-results": _ctx.informationalResults,
    "is-installation": _ctx.isInstallation
  }, null, 8, ["error-type", "warning-type", "informational-type", "system-check-info", "mandatory-results", "optional-results", "informational-results", "is-installation"]), !_ctx.showNextStep ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SystemCheckvue_type_template_id_7dd80659_hoisted_3, [!_ctx.showNextStep ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SystemCheckvue_type_template_id_7dd80659_hoisted_4, [SystemCheckvue_type_template_id_7dd80659_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink('https://matomo.org/docs/requirements/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_Requirements')), 9, SystemCheckvue_type_template_id_7dd80659_hoisted_6)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SystemCheckLegend, {
    url: _ctx.systemCheckLegendUrl
  }, null, 8, ["url"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheck.vue?vue&type=template&id=7dd80659

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckLegend.vue?vue&type=template&id=77aad6d6

var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_1 = {
  class: "system-check-legend"
};

var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);

var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);

var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);

var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_5 = {
  class: "next-step"
};
var SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_6 = ["href"];
function SystemCheckLegendvue_type_template_id_77aad6d6_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_Legend')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Ok')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Warning')) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SystemCheckWarning')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Installation_SystemCheckError')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.url
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RefreshPage')) + " »", 9, SystemCheckLegendvue_type_template_id_77aad6d6_hoisted_6)])], 64);
}
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckLegend.vue?vue&type=template&id=77aad6d6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheckLegend.vue?vue&type=script&lang=ts

/* harmony default export */ var SystemCheckLegendvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    url: {
      type: String,
      required: true
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckLegend.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheckLegend.vue



SystemCheckLegendvue_type_script_lang_ts.render = SystemCheckLegendvue_type_template_id_77aad6d6_render

/* harmony default export */ var SystemCheckLegend = (SystemCheckLegendvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Installation/vue/src/SystemCheck/SystemCheck.vue?vue&type=script&lang=ts



var SystemCheckvue_type_script_lang_ts_window = window,
    SystemCheckvue_type_script_lang_ts_$ = SystemCheckvue_type_script_lang_ts_window.$;
/* harmony default export */ var SystemCheckvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    showNextStep: Boolean,
    systemCheckLegendUrl: {
      type: String,
      required: true
    },
    errorType: {
      type: String,
      required: true
    },
    warningType: {
      type: String,
      required: true
    },
    informationalType: {
      type: String,
      required: true
    },
    systemCheckInfo: {
      type: String,
      required: true
    },
    mandatoryResults: {
      type: Array,
      required: true
    },
    optionalResults: {
      type: Array,
      required: true
    },
    informationalResults: {
      type: Array,
      required: true
    },
    isInstallation: Boolean
  },
  components: {
    SystemCheckSection: SystemCheckSection,
    SystemCheckLegend: SystemCheckLegend
  },
  mounted: function mounted() {
    // client-side test for https to handle the case where the server is behind a reverse proxy
    if (document.location.protocol === 'https:') {
      var link = SystemCheckvue_type_script_lang_ts_$('p.next-step a');
      link.attr('href', "".concat(link.attr('href'), "&clientProtocol=https"));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheck.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Installation/vue/src/SystemCheck/SystemCheck.vue



SystemCheckvue_type_script_lang_ts.render = SystemCheckvue_type_template_id_7dd80659_render

/* harmony default export */ var SystemCheck = (SystemCheckvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Installation/vue/src/index.ts
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
//# sourceMappingURL=Installation.umd.js.map