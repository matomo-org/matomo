(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["CustomDimensions"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["CustomDimensions"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/CustomDimensions/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "CustomDimensionsStore", function() { return /* reexport */ CustomDimensions_store; });
__webpack_require__.d(__webpack_exports__, "Edit", function() { return /* reexport */ Edit; });
__webpack_require__.d(__webpack_exports__, "List", function() { return /* reexport */ List; });
__webpack_require__.d(__webpack_exports__, "Manage", function() { return /* reexport */ Manage; });

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

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/Edit/Edit.vue?vue&type=template&id=2a08f82a

var _hoisted_1 = {
  class: "editCustomDimension"
};
var _hoisted_2 = {
  class: "loadingPiwik"
};

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif"
}, null, -1);

var _hoisted_4 = {
  class: "row form-group"
};
var _hoisted_5 = {
  class: "col s12"
};
var _hoisted_6 = {
  class: "col s12 m6"
};
var _hoisted_7 = {
  class: "row"
};
var _hoisted_8 = {
  class: "col s12 m6"
};
var _hoisted_9 = {
  class: "col s12 m6"
};
var _hoisted_10 = {
  class: "col s12"
};
var _hoisted_11 = ["onClick"];
var _hoisted_12 = {
  class: "row"
};
var _hoisted_13 = {
  class: "col s12"
};
var _hoisted_14 = {
  class: "col s12 m6 form-help"
};
var _hoisted_15 = ["value", "disabled"];
var _hoisted_16 = ["value", "disabled"];
var _hoisted_17 = {
  class: "btn cancel",
  type: "button",
  href: "#list"
};
var _hoisted_18 = {
  class: "alert alert-info howToTrackInfo"
};

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("              ");

var _hoisted_20 = ["innerHTML"];

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("\n            ");

var _hoisted_22 = ["innerHTML"];

var _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("              ");

var _hoisted_24 = ["innerHTML"];

var _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("\n            ");

var _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("              ");

var _hoisted_27 = ["innerHTML"];

var _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("\n            ");

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitleText
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      var _ctx$dimension$extrac;

      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_2, [_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isLoading || _ctx.isUpdating]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
        onSubmit: _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
          return _ctx.edit ? _ctx.updateCustomDimension() : _ctx.createCustomDimension();
        }, ["prevent"]))
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "name",
        modelValue: _ctx.dimension.name,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.dimension.name = $event;
        }),
        maxlength: 255,
        required: true,
        title: _ctx.translate('General_Name'),
        "inline-help": _ctx.translate('CustomDimensions_NameAllowedCharacters')
      }, null, 8, ["modelValue", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "active",
        modelValue: _ctx.dimension.active,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.dimension.active = $event;
        }),
        title: _ctx.translate('CorePluginsAdmin_Active'),
        "inline-help": _ctx.translate('CustomDimensions_CannotBeDeleted')
      }, null, 8, ["modelValue", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_ExtractValue')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimension.extractions, function (extraction, index) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("".concat(index, "extraction ")),
          key: index
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
          uicontrol: "select",
          name: "".concat(index, "dimension"),
          modelValue: extraction.dimension,
          "onUpdate:modelValue": function onUpdateModelValue($event) {
            return extraction.dimension = $event;
          },
          "full-width": true,
          options: _ctx.extractionDimensionsOptions
        }, null, 8, ["name", "modelValue", "onUpdate:modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
          uicontrol: "text",
          name: "".concat(index, "pattern"),
          modelValue: extraction.pattern,
          "onUpdate:modelValue": function onUpdateModelValue($event) {
            return extraction.pattern = $event;
          },
          "full-width": true,
          title: extraction.dimension === 'urlparam' ? _ctx.translate('CustomDimensions_UrlQueryStringParameter') : 'eg. /blog/(.*)/'
        }, null, 8, ["name", "modelValue", "onUpdate:modelValue", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: "icon-plus",
          onClick: _cache[2] || (_cache[2] = function ($event) {
            return _ctx.addExtraction();
          })
        }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], extraction.pattern]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: "icon-minus",
          onClick: function onClick($event) {
            return _ctx.removeExtraction(index);
          }
        }, null, 8, _hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dimension.extractions.length > 1]])])])], 2);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "casesensitive",
        modelValue: _ctx.dimension.case_sensitive,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          return _ctx.dimension.case_sensitive = $event;
        }),
        title: _ctx.translate('Goals_CaseSensitive')
      }, null, 8, ["modelValue", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], (_ctx$dimension$extrac = _ctx.dimension.extractions[0]) === null || _ctx$dimension$extrac === void 0 ? void 0 : _ctx$dimension$extrac.pattern]])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_ExtractionsHelp')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.doesScopeSupportExtraction]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        class: "btn update",
        type: "submit",
        value: _ctx.translate('General_Update'),
        disabled: _ctx.isUpdating,
        style: {
          "margin-right": "3.5px"
        }
      }, null, 8, _hoisted_15), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.edit]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        class: "btn create",
        type: "submit",
        value: _ctx.translate('General_Create'),
        disabled: _ctx.isUpdating,
        style: {
          "margin-right": "3.5px"
        }
      }, null, 8, _hoisted_16), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.create]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)], 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToTrackManuallyTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToTrackManuallyViaJs')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
        innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCodeViaJs(_ctx.dimension))
      }, null, 8, _hoisted_20), _hoisted_21], 512), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.howToTrackManuallyText)
      }, null, 8, _hoisted_22), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToTrackManuallyViaPhp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
        innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCodeViaPhp(_ctx.dimension))
      }, null, 8, _hoisted_24), _hoisted_25], 512), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToTrackManuallyViaHttp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [_hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
        innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCode)
      }, null, 8, _hoisted_27), _hoisted_28], 512), [[_directive_copy_to_clipboard, {}]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.edit]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]])];
    }),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Edit/Edit.vue?vue&type=template&id=2a08f82a

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/CustomDimensions.store.ts
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



var CustomDimensions_store_CustomDimensionsStore = /*#__PURE__*/function () {
  function CustomDimensionsStore() {
    var _this = this;

    _classCallCheck(this, CustomDimensionsStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      customDimensions: [],
      availableScopes: [],
      extractionDimensions: [],
      isLoading: false,
      isUpdating: false
    }));

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.isLoading;
    }));

    _defineProperty(this, "isUpdating", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.isUpdating;
    }));

    _defineProperty(this, "extractionDimensions", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.extractionDimensions;
    }));

    _defineProperty(this, "extractionDimensionsOptions", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.extractionDimensions.value.map(function (e) {
        return {
          key: e.value,
          value: e.name
        };
      });
    }));

    _defineProperty(this, "availableScopes", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.availableScopes;
    }));

    _defineProperty(this, "customDimensions", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.customDimensions;
    }));

    _defineProperty(this, "customDimensionsById", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var dimensionsById = {};

      _this.customDimensions.value.forEach(function (c) {
        dimensionsById["".concat(c.idcustomdimension)] = c;
      });

      return dimensionsById;
    }));

    _defineProperty(this, "reloadPromise", null);
  }

  _createClass(CustomDimensionsStore, [{
    key: "reload",
    value: function reload() {
      this.privateState.customDimensions = [];
      this.privateState.availableScopes = [];
      this.privateState.extractionDimensions = [];
      this.reloadPromise = null;
      return this.fetch();
    }
  }, {
    key: "fetch",
    value: function fetch() {
      var _this2 = this;

      if (this.reloadPromise) {
        return this.reloadPromise;
      }

      this.privateState.isLoading = true;
      this.reloadPromise = Promise.all([this.fetchConfiguredCustomDimensions(), this.fetchAvailableExtractionDimensions(), this.fetchAvailableScopes()]).finally(function () {
        _this2.privateState.isLoading = false;
      });
      return this.reloadPromise;
    }
  }, {
    key: "fetchConfiguredCustomDimensions",
    value: function fetchConfiguredCustomDimensions() {
      var _this3 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'CustomDimensions.getConfiguredCustomDimensions',
        filter_limit: '-1'
      }).then(function (r) {
        _this3.privateState.customDimensions = r;
      });
    }
  }, {
    key: "fetchAvailableExtractionDimensions",
    value: function fetchAvailableExtractionDimensions() {
      var _this4 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'CustomDimensions.getAvailableExtractionDimensions',
        filter_limit: '-1'
      }).then(function (r) {
        _this4.privateState.extractionDimensions = r;
      });
    }
  }, {
    key: "fetchAvailableScopes",
    value: function fetchAvailableScopes() {
      var _this5 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'CustomDimensions.getAvailableScopes',
        filter_limit: '-1'
      }).then(function (r) {
        _this5.privateState.availableScopes = r;
      });
    }
  }, {
    key: "createOrUpdateDimension",
    value: function createOrUpdateDimension(dimension, method) {
      var _this6 = this;

      this.privateState.isUpdating = true;
      return external_CoreHome_["AjaxHelper"].post({
        method: method,
        scope: dimension.scope,
        idDimension: dimension.idcustomdimension,
        idSite: dimension.idsite,
        name: dimension.name,
        active: dimension.active ? '1' : '0',
        caseSensitive: dimension.case_sensitive ? '1' : '0'
      }, {
        extractions: dimension.extractions
      }).finally(function () {
        _this6.privateState.isUpdating = false;
      });
    }
  }]);

  return CustomDimensionsStore;
}();

/* harmony default export */ var CustomDimensions_store = (new CustomDimensions_store_CustomDimensionsStore());
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/utilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function utilities_ucfirst(s) {
  return "".concat(s[0].toUpperCase()).concat(s.slice(1));
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/Edit/Edit.vue?vue&type=script&lang=ts





var notificationId = 'customdimensions';
/* harmony default export */ var Editvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    dimensionId: Number,
    dimensionScope: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      dimension: {
        extractions: []
      },
      isUpdatingDim: false
    };
  },
  created: function created() {
    this.init();
  },
  watch: {
    dimensionId: function dimensionId() {
      this.init();
    }
  },
  methods: {
    removeAnyCustomDimensionNotification: function removeAnyCustomDimensionNotification() {
      external_CoreHome_["NotificationsStore"].remove(notificationId);
    },
    showNotification: function showNotification(message, context) {
      external_CoreHome_["NotificationsStore"].show({
        message: message,
        context: context,
        id: notificationId,
        type: 'transient'
      });
    },
    init: function init() {
      var _this = this;

      if (this.dimensionId !== null) {
        this.removeAnyCustomDimensionNotification();
      }

      CustomDimensions_store.fetch().then(function () {
        if (_this.edit && _this.dimensionId) {
          _this.dimension = Object(external_CoreHome_["clone"])(CustomDimensions_store.customDimensionsById.value[_this.dimensionId]);

          if (_this.dimension && !_this.dimension.extractions.length) {
            _this.addExtraction();
          }
        } else if (_this.create) {
          _this.dimension = {
            idsite: external_CoreHome_["Matomo"].idSite,
            name: '',
            active: false,
            extractions: [],
            scope: _this.dimensionScope,
            case_sensitive: true
          };

          _this.addExtraction();
        }
      });
    },
    removeExtraction: function removeExtraction(index) {
      if (index > -1) {
        this.dimension.extractions.splice(index, 1);
      }
    },
    addExtraction: function addExtraction() {
      if (this.doesScopeSupportExtraction) {
        this.dimension.extractions.push({
          dimension: 'url',
          pattern: ''
        });
      }
    },
    createCustomDimension: function createCustomDimension() {
      var _this2 = this;

      this.isUpdatingDim = true;
      CustomDimensions_store.createOrUpdateDimension(this.dimension, 'CustomDimensions.configureNewCustomDimension').then(function () {
        _this2.showNotification(Object(external_CoreHome_["translate"])('CustomDimensions_DimensionCreated'), 'success');

        CustomDimensions_store.reload();
        external_CoreHome_["MatomoUrl"].updateHashToUrl('/list');
      }).finally(function () {
        _this2.isUpdatingDim = false;
      });
    },
    updateCustomDimension: function updateCustomDimension() {
      var _this3 = this;

      this.isUpdatingDim = true;
      CustomDimensions_store.createOrUpdateDimension(this.dimension, 'CustomDimensions.configureExistingCustomDimension').then(function () {
        _this3.showNotification(Object(external_CoreHome_["translate"])('CustomDimensions_DimensionUpdated'), 'success');

        CustomDimensions_store.reload();
        external_CoreHome_["MatomoUrl"].updateHashToUrl('/list');
      }).finally(function () {
        _this3.isUpdatingDim = false;
      });
    },
    manuallyTrackCodeViaJs: function manuallyTrackCodeViaJs(dimension) {
      return "_paq.push(['setCustomDimension', ".concat(dimension.idcustomdimension, ", ") + "'".concat(Object(external_CoreHome_["translate"])('CustomDimensions_ExampleValue'), "']);");
    },
    manuallyTrackCodeViaPhp: function manuallyTrackCodeViaPhp(dimension) {
      return "$tracker->setCustomDimension('".concat(dimension.idcustomdimension, "', ") + "'".concat(Object(external_CoreHome_["translate"])('CustomDimensions_ExampleValue'), "');");
    }
  },
  computed: {
    isLoading: function isLoading() {
      return CustomDimensions_store.isLoading.value;
    },
    isUpdating: function isUpdating() {
      return CustomDimensions_store.isUpdating.value || this.isUpdatingDim;
    },
    create: function create() {
      return this.dimensionId === 0;
    },
    edit: function edit() {
      return !this.create;
    },
    extractionDimensionsOptions: function extractionDimensionsOptions() {
      return CustomDimensions_store.extractionDimensionsOptions.value;
    },
    availableScopes: function availableScopes() {
      return CustomDimensions_store.availableScopes.value;
    },
    doesScopeSupportExtraction: function doesScopeSupportExtraction() {
      var _this$dimension,
          _this4 = this;

      if (!((_this$dimension = this.dimension) !== null && _this$dimension !== void 0 && _this$dimension.scope) || !this.availableScopes) {
        return false;
      }

      var dimensionScope = this.availableScopes.find(function (scope) {
        return scope.value === _this4.dimension.scope;
      });
      return dimensionScope === null || dimensionScope === void 0 ? void 0 : dimensionScope.supportsExtractions;
    },
    contentTitleText: function contentTitleText() {
      var _this$dimension2;

      return Object(external_CoreHome_["translate"])('CustomDimensions_ConfigureDimension', utilities_ucfirst(this.dimensionScope), "".concat(((_this$dimension2 = this.dimension) === null || _this$dimension2 === void 0 ? void 0 : _this$dimension2.index) || ''));
    },
    howToTrackManuallyText: function howToTrackManuallyText() {
      var link = 'https://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions';
      return Object(external_CoreHome_["translate"])('CustomDimensions_HowToTrackManuallyViaJsDetails', "<a target=_blank href=\"".concat(link, "\" rel=\"noreferrer noopener\">"), '</a>');
    },
    manuallyTrackCode: function manuallyTrackCode() {
      var exampleValue = Object(external_CoreHome_["translate"])('CustomDimensions_ExampleValue');
      return "&dimension".concat(this.dimension.idcustomdimension, "=").concat(exampleValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Edit/Edit.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Edit/Edit.vue



Editvue_type_script_lang_ts.render = render

/* harmony default export */ var Edit = (Editvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Edit/Edit.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Edit_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: Edit,
  scope: {
    dimensionId: {
      angularJsBind: '=',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    dimensionScope: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikCustomDimensionsEdit'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/List/List.vue?vue&type=template&id=0c3a05ca

var Listvue_type_template_id_0c3a05ca_hoisted_1 = ["innerHTML"];
var Listvue_type_template_id_0c3a05ca_hoisted_2 = {
  class: "loadingPiwik"
};

var Listvue_type_template_id_0c3a05ca_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif"
}, null, -1);

var Listvue_type_template_id_0c3a05ca_hoisted_4 = {
  class: "index"
};
var Listvue_type_template_id_0c3a05ca_hoisted_5 = {
  class: "name"
};
var Listvue_type_template_id_0c3a05ca_hoisted_6 = {
  class: "active"
};
var Listvue_type_template_id_0c3a05ca_hoisted_7 = {
  class: "action"
};
var Listvue_type_template_id_0c3a05ca_hoisted_8 = {
  colspan: "5"
};
var Listvue_type_template_id_0c3a05ca_hoisted_9 = {
  class: "index"
};
var Listvue_type_template_id_0c3a05ca_hoisted_10 = {
  class: "name"
};
var Listvue_type_template_id_0c3a05ca_hoisted_11 = {
  class: "extractions"
};
var Listvue_type_template_id_0c3a05ca_hoisted_12 = {
  class: "active"
};
var Listvue_type_template_id_0c3a05ca_hoisted_13 = {
  class: "action"
};
var Listvue_type_template_id_0c3a05ca_hoisted_14 = ["href"];
var Listvue_type_template_id_0c3a05ca_hoisted_15 = {
  class: "tableActionBar"
};
var Listvue_type_template_id_0c3a05ca_hoisted_16 = ["disabled", "onClick"];

var Listvue_type_template_id_0c3a05ca_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var Listvue_type_template_id_0c3a05ca_hoisted_18 = {
  class: "info"
};
function Listvue_type_template_id_0c3a05ca_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_CustomDimensions')), 1)];
    }),
    _: 1
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.contentIntroText)
  }, null, 8, Listvue_type_template_id_0c3a05ca_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Listvue_type_template_id_0c3a05ca_hoisted_2, [Listvue_type_template_id_0c3a05ca_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isLoading]])], 512), [[_directive_content_intro]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableScopes, function (scope) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: scope.value
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
      "content-title": _ctx.translate("CustomDimensions_ScopeTitle".concat(_ctx.ucfirst(scope.value)))
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
        return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate("CustomDimensions_ScopeDescription".concat(_ctx.ucfirst(scope.value)))) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate("CustomDimensions_ScopeDescription".concat(_ctx.ucfirst(scope.value), "MoreInfo"))), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", Listvue_type_template_id_0c3a05ca_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", Listvue_type_template_id_0c3a05ca_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
          class: "extractions"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_Extractions')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], scope.supportsExtractions]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", Listvue_type_template_id_0c3a05ca_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Active')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", Listvue_type_template_id_0c3a05ca_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_NoCustomDimensionConfigured')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], scope.numSlotsUsed === 0 && !_ctx.isLoading]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedCustomDimensionsByScope[scope.value], function (customDimension) {
          var _customDimension$extr;

          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["customdimension", customDimension.idcustomdimension]),
            key: customDimension.idcustomdimension
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(customDimension.idcustomdimension), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(customDimension.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
              'icon-ok': (_customDimension$extr = customDimension.extractions[0]) === null || _customDimension$extr === void 0 ? void 0 : _customDimension$extr.pattern
            })
          }, null, 2)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], scope.supportsExtractions]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
              'icon-ok': customDimension.active
            })
          }, null, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Listvue_type_template_id_0c3a05ca_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
            class: "table-action icon-edit",
            href: "#?idDimension=".concat(customDimension.idcustomdimension, "&scope=").concat(scope.value)
          }, null, 8, Listvue_type_template_id_0c3a05ca_hoisted_14)])], 2);
        }), 128))])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Listvue_type_template_id_0c3a05ca_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "btn",
          disabled: !scope.numSlotsLeft,
          onClick: function onClick($event) {
            return _ctx.addDimension(scope.value);
          }
        }, [Listvue_type_template_id_0c3a05ca_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_ConfigureNewDimension')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Listvue_type_template_id_0c3a05ca_hoisted_18, "(" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_XofYLeft', scope.numSlotsLeft, scope.numSlotsAvailable)) + ")", 1)], 8, Listvue_type_template_id_0c3a05ca_hoisted_16), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]])])];
      }),
      _: 2
    }, 1032, ["content-title"])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/List/List.vue?vue&type=template&id=0c3a05ca

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/List/List.vue?vue&type=script&lang=ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }





/* harmony default export */ var Listvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'listcustomdimensions',
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    ContentTable: external_CoreHome_["ContentTable"]
  },
  created: function created() {
    CustomDimensions_store.fetch();
  },
  methods: {
    ucfirst: function ucfirst(s) {
      return utilities_ucfirst(s);
    },
    addDimension: function addDimension(scope) {
      external_CoreHome_["MatomoUrl"].updateHashToUrl("/?idDimension=0&scope=".concat(scope));
    }
  },
  computed: {
    isLoading: function isLoading() {
      return CustomDimensions_store.isLoading.value;
    },
    availableScopes: function availableScopes() {
      return CustomDimensions_store.availableScopes.value;
    },
    contentIntroText: function contentIntroText() {
      var firstPart = Object(external_CoreHome_["translate"])('CustomDimensions_CustomDimensionsIntroNext', '<a target=_blank href="https://piwik.org/docs/custom-variables">', '</a>', '<a target=_blank href="https://piwik.org/faq/general/faq_21117">', '</a>');
      var secondPart = Object(external_CoreHome_["translate"])('CustomDimensions_CustomDimensionsIntro', '<a target=_blank href="https://piwik.org/docs/custom-dimensions">', '</a>', this.siteName);
      return "".concat(firstPart).concat(secondPart);
    },
    customDimensions: function customDimensions() {
      return CustomDimensions_store.customDimensions.value;
    },
    sortedCustomDimensions: function sortedCustomDimensions() {
      var result = _toConsumableArray(this.customDimensions);

      result.sort(function (lhs, rhs) {
        var lhsId = parseInt("".concat(lhs.idcustomdimension), 10);
        var rhsId = parseInt("".concat(rhs.idcustomdimension), 10);
        return lhsId - rhsId;
      });
      return result;
    },
    sortedCustomDimensionsByScope: function sortedCustomDimensionsByScope() {
      var result = {};
      this.sortedCustomDimensions.reduce(function (acc, dim) {
        acc[dim.scope] = acc[dim.scope] || [];
        acc[dim.scope].push(dim);
        return acc;
      }, result);
      return result;
    },
    siteName: function siteName() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(external_CoreHome_["Matomo"].siteName);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/List/List.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/List/List.vue



Listvue_type_script_lang_ts.render = Listvue_type_template_id_0c3a05ca_render

/* harmony default export */ var List = (Listvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/List/List.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var List_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: List,
  directiveName: 'piwikCustomDimensionsList'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/Manage/Manage.vue?vue&type=template&id=13846b18

var Managevue_type_template_id_13846b18_hoisted_1 = {
  class: "manageCustomDimensions"
};
var Managevue_type_template_id_13846b18_hoisted_2 = {
  key: 0
};

var Managevue_type_template_id_13846b18_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Managevue_type_template_id_13846b18_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Managevue_type_template_id_13846b18_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Managevue_type_template_id_13846b18_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Managevue_type_template_id_13846b18_hoisted_7 = ["textContent"];
var Managevue_type_template_id_13846b18_hoisted_8 = ["textContent"];
var Managevue_type_template_id_13846b18_hoisted_9 = {
  key: 1
};
function Managevue_type_template_id_13846b18_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_CustomDimensionsList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CustomDimensionsList");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _component_CustomDimensionsEdit = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CustomDimensionsEdit");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Managevue_type_template_id_13846b18_hoisted_1, [!_ctx.editMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Managevue_type_template_id_13846b18_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CustomDimensionsList)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "customDimensionsCreateMoreDimensions",
    "content-title": _ctx.translate('CustomDimensions_IncreaseAvailableCustomDimensionsTitle')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_IncreaseAvailableCustomDimensionsTakesLong')) + " ", 1), Managevue_type_template_id_13846b18_hoisted_3, Managevue_type_template_id_13846b18_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToCreateCustomDimension')) + " ", 1), Managevue_type_template_id_13846b18_hoisted_5, Managevue_type_template_id_13846b18_hoisted_6]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.addCustomDimCode)
      }, null, 8, Managevue_type_template_id_13846b18_hoisted_7)], 512), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_HowToManyCreateCustomDimensions')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CustomDimensions_ExampleCreateCustomDimensions', 5)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.addMultipleCustomDimCode)
      }, null, 8, Managevue_type_template_id_13846b18_hoisted_8)], 512), [[_directive_copy_to_clipboard, {}]])])];
    }),
    _: 1
  }, 8, ["content-title"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Managevue_type_template_id_13846b18_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CustomDimensionsEdit, {
    "dimension-id": _ctx.dimensionId,
    "dimension-scope": _ctx.dimensionScope
  }, null, 8, ["dimension-id", "dimension-scope"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Manage/Manage.vue?vue&type=template&id=13846b18

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CustomDimensions/vue/src/Manage/Manage.vue?vue&type=script&lang=ts




/* harmony default export */ var Managevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    CustomDimensionsList: List,
    ContentBlock: external_CoreHome_["ContentBlock"],
    CustomDimensionsEdit: Edit
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      editMode: false,
      dimensionId: null,
      dimensionScope: ''
    };
  },
  created: function created() {
    var _this = this;

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return external_CoreHome_["MatomoUrl"].hashParsed.value;
    }, function () {
      _this.initState();
    });
    this.initState();
  },
  methods: {
    getValidDimensionScope: function getValidDimensionScope(scope) {
      if (['action', 'visit'].indexOf(scope) !== -1) {
        return scope;
      }

      return '';
    },
    initState: function initState() {
      // as we're not using angular router we have to handle it manually here
      var idDimension = external_CoreHome_["MatomoUrl"].hashParsed.value.idDimension;

      if (idDimension) {
        var scope = this.getValidDimensionScope(external_CoreHome_["MatomoUrl"].hashParsed.value.scope);

        if (idDimension === '0') {
          var parameters = {
            isAllowed: true,
            scope: scope
          };
          external_CoreHome_["Matomo"].postEvent('CustomDimensions.initAddDimension', parameters);

          if (parameters && !parameters.isAllowed) {
            this.editMode = false;
            this.dimensionId = null;
            this.dimensionScope = '';
            return;
          }
        }

        this.editMode = true;
        this.dimensionId = parseInt(idDimension, 10);
        this.dimensionScope = scope;
      } else {
        this.editMode = false;
        this.dimensionId = null;
        this.dimensionScope = '';
      }

      external_CoreHome_["Matomo"].helper.lazyScrollToContent();
    }
  },
  computed: {
    addCustomDimCode: function addCustomDimCode() {
      return './console customdimensions:add-custom-dimension --scope=action\n' + './console customdimensions:add-custom-dimension --scope=visit';
    },
    addMultipleCustomDimCode: function addMultipleCustomDimCode() {
      return './console customdimensions:add-custom-dimension --scope=action --count=5';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Manage/Manage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Manage/Manage.vue



Managevue_type_script_lang_ts.render = Managevue_type_template_id_13846b18_render

/* harmony default export */ var Manage = (Managevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/Manage/Manage.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Manage_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: Manage,
  directiveName: 'piwikCustomDimensionsManage'
}));
// CONCATENATED MODULE: ./plugins/CustomDimensions/vue/src/index.ts
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
//# sourceMappingURL=CustomDimensions.umd.js.map