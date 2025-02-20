(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["SitesManager"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["SitesManager"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/SitesManager/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "SiteTypesStore", function() { return /* reexport */ src_SiteTypesStore_SiteTypesStore; });
__webpack_require__.d(__webpack_exports__, "CurrencyStore", function() { return /* reexport */ src_CurrencyStore_CurrencyStore; });
__webpack_require__.d(__webpack_exports__, "TimezoneStore", function() { return /* reexport */ src_TimezoneStore_TimezoneStore; });
__webpack_require__.d(__webpack_exports__, "SitesManagement", function() { return /* reexport */ SitesManagement; });
__webpack_require__.d(__webpack_exports__, "ManageGlobalSettings", function() { return /* reexport */ ManageGlobalSettings; });
__webpack_require__.d(__webpack_exports__, "ExcludeQueryParameterSettings", function() { return /* reexport */ ExcludeQueryParameterSettings; });
__webpack_require__.d(__webpack_exports__, "SiteWithoutData", function() { return /* reexport */ SiteWithoutData; });

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

// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteTypesStore/SiteTypesStore.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


var _window = window,
    $ = _window.$;

var SiteTypesStore_SiteTypesStore = /*#__PURE__*/function () {
  function SiteTypesStore() {
    var _this = this;

    _classCallCheck(this, SiteTypesStore);

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      typesById: {}
    }));

    _defineProperty(this, "typesById", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.state).typesById;
    }));

    _defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.state).isLoading;
    }));

    _defineProperty(this, "types", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object.values(_this.typesById.value);
    }));

    _defineProperty(this, "response", void 0);
  }

  _createClass(SiteTypesStore, [{
    key: "init",
    value: function init() {
      return this.fetchAvailableTypes();
    }
  }, {
    key: "fetchAvailableTypes",
    value: function fetchAvailableTypes() {
      var _this2 = this;

      if (this.response) {
        return Promise.resolve(this.response);
      }

      this.state.isLoading = true;
      this.response = external_CoreHome_["AjaxHelper"].fetch({
        method: 'API.getAvailableMeasurableTypes',
        filter_limit: '-1'
      }).then(function (types) {
        types.forEach(function (type) {
          _this2.state.typesById[type.id] = type;
        });
        return _this2.types.value;
      }).finally(function () {
        _this2.state.isLoading = false;
      });
      return this.response;
    }
  }, {
    key: "getEditSiteIdParameter",
    value: function getEditSiteIdParameter() {
      // parse query directly because #/editsiteid=N was supported alongside #/?editsiteid=N
      var m = external_CoreHome_["MatomoUrl"].hashQuery.value.match(/editsiteid=([0-9]+)/);

      if (!m) {
        return undefined;
      }

      var isShowAddSite = external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite === '1' || external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite === 'true';
      var editsiteid = m[1];

      if (editsiteid && $.isNumeric(editsiteid) && !isShowAddSite) {
        return editsiteid;
      }

      return undefined;
    }
  }, {
    key: "removeEditSiteIdParameterFromHash",
    value: function removeEditSiteIdParameterFromHash() {
      var params = Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value);
      delete params.editsiteid;
      external_CoreHome_["MatomoUrl"].updateHash(params);
    }
  }]);

  return SiteTypesStore;
}();

/* harmony default export */ var src_SiteTypesStore_SiteTypesStore = (new SiteTypesStore_SiteTypesStore());
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/CurrencyStore/CurrencyStore.ts
function CurrencyStore_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function CurrencyStore_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function CurrencyStore_createClass(Constructor, protoProps, staticProps) { if (protoProps) CurrencyStore_defineProperties(Constructor.prototype, protoProps); if (staticProps) CurrencyStore_defineProperties(Constructor, staticProps); return Constructor; }

function CurrencyStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var CurrencyStore_CurrencyStore = /*#__PURE__*/function () {
  function CurrencyStore() {
    var _this = this;

    CurrencyStore_classCallCheck(this, CurrencyStore);

    CurrencyStore_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      currencies: {}
    }));

    CurrencyStore_defineProperty(this, "currencies", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState).currencies;
    }));

    CurrencyStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState).isLoading;
    }));

    CurrencyStore_defineProperty(this, "initializePromise", null);
  }

  CurrencyStore_createClass(CurrencyStore, [{
    key: "init",
    value: function init() {
      if (!this.initializePromise) {
        this.initializePromise = this.fetchCurrencies();
      }

      return this.initializePromise;
    }
  }, {
    key: "fetchCurrencies",
    value: function fetchCurrencies() {
      var _this2 = this;

      this.privateState.isLoading = true;
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getCurrencyList'
      }).then(function (currencies) {
        _this2.privateState.currencies = currencies;
      }).finally(function () {
        _this2.privateState.isLoading = false;
      });
    }
  }]);

  return CurrencyStore;
}();

/* harmony default export */ var src_CurrencyStore_CurrencyStore = (new CurrencyStore_CurrencyStore());
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/TimezoneStore/TimezoneStore.ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function TimezoneStore_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function TimezoneStore_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function TimezoneStore_createClass(Constructor, protoProps, staticProps) { if (protoProps) TimezoneStore_defineProperties(Constructor.prototype, protoProps); if (staticProps) TimezoneStore_defineProperties(Constructor, staticProps); return Constructor; }

function TimezoneStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var TimezoneStore_TimezoneStore = /*#__PURE__*/function () {
  function TimezoneStore() {
    var _this = this;

    TimezoneStore_classCallCheck(this, TimezoneStore);

    TimezoneStore_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      timezones: [],
      timezoneSupportEnabled: false
    }));

    TimezoneStore_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    TimezoneStore_defineProperty(this, "timezones", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.timezones;
    }));

    TimezoneStore_defineProperty(this, "timezoneSupportEnabled", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.timezoneSupportEnabled;
    }));

    TimezoneStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.isLoading;
    }));

    TimezoneStore_defineProperty(this, "initializePromise", null);
  }

  TimezoneStore_createClass(TimezoneStore, [{
    key: "init",
    value: function init() {
      var _this2 = this;

      if (!this.initializePromise) {
        this.privateState.isLoading = true;
        this.initializePromise = Promise.all([this.checkTimezoneSupportEnabled(), this.fetchTimezones()]).finally(function () {
          _this2.privateState.isLoading = false;
        });
      }

      return this.initializePromise;
    }
  }, {
    key: "fetchTimezones",
    value: function fetchTimezones() {
      var _this3 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getTimezonesList'
      }).then(function (grouped) {
        var flattened = [];
        Object.entries(grouped).forEach(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
              group = _ref2[0],
              timezonesGroup = _ref2[1];

          Object.entries(timezonesGroup).forEach(function (_ref3) {
            var _ref4 = _slicedToArray(_ref3, 2),
                label = _ref4[0],
                code = _ref4[1];

            flattened.push({
              group: group,
              label: label,
              code: code
            });
          });
        });
        _this3.privateState.timezones = flattened;
      });
    }
  }, {
    key: "checkTimezoneSupportEnabled",
    value: function checkTimezoneSupportEnabled() {
      var _this4 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.isTimezoneSupportEnabled'
      }).then(function (response) {
        _this4.privateState.timezoneSupportEnabled = response.value;
      });
    }
  }]);

  return TimezoneStore;
}();

/* harmony default export */ var src_TimezoneStore_TimezoneStore = (new TimezoneStore_TimezoneStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=template&id=35a6a1b6

var _hoisted_1 = {
  class: "SitesManager",
  ref: "root"
};
var _hoisted_2 = {
  class: "sites-manager-header"
};
var _hoisted_3 = ["innerHTML"];

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_5 = ["innerHTML"];
var _hoisted_6 = {
  class: "loadingPiwik"
};
var _hoisted_7 = ["alt"];
var _hoisted_8 = {
  class: "ui-confirm add-site-dialog"
};
var _hoisted_9 = {
  class: "center"
};
var _hoisted_10 = ["title", "onClick"];
var _hoisted_11 = {
  class: "ui-button-text"
};
var _hoisted_12 = {
  class: "sitesManagerList"
};
var _hoisted_13 = {
  key: 0
};
var _hoisted_14 = {
  class: "bottomButtonBar"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_ButtonBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ButtonBar");

  var _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");

  var _component_SiteFields = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteFields");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/manage-websites/'),
    "feature-name": _ctx.translate('SitesManager_WebsitesManagement')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.headlineText), 1)];
    }),
    _: 1
  }, 8, ["help-url", "feature-name"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.availableTypes.length]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_MainDescription')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.mainDescription)
  }, null, 8, _hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.superUserAccessMessage)
  }, null, 8, _hoisted_5)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess]])])], 512), [[_directive_content_intro]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      hide_only: !_ctx.isLoading
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)])], 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ButtonBar, {
    "site-is-being-edited": _ctx.isSiteBeingEdited,
    "has-prev": _ctx.hasPrev,
    hasNext: _ctx.hasNext,
    "offset-start": _ctx.offsetStart,
    "offset-end": _ctx.offsetEnd,
    "total-number-of-sites": _ctx.totalNumberOfSites,
    "is-loading": _ctx.isLoading,
    "search-term": _ctx.searchTerm,
    "is-searching": !!_ctx.activeSearchTerm,
    "onUpdate:searchTerm": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    onAdd: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.addNewEntity();
    }),
    onSearch: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.searchSites($event);
    }),
    onPrev: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.previousPage();
    }),
    onNext: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.nextPage();
    })
  }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showAddSiteDialog,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.showAddSiteDialog = $event;
    })
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChooseMeasurableTypeHeadline')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableTypes, function (type) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
          type: "button",
          key: type.id,
          title: type.description,
          class: "modal-close btn",
          onClick: function onClick($event) {
            _ctx.addSite(type.id);
          },
          "aria-disabled": "false"
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(type.name), 1)], 8, _hoisted_10);
      }), 128))])])])])];
    }),
    _: 1
  }, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [_ctx.activeSearchTerm && 0 === _ctx.sites.length && !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_NotFound')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.activeSearchTerm), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, function (site, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: site.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteFields, {
      site: site,
      "timezone-support-enabled": _ctx.timezoneSupportEnabled,
      "utc-time": _ctx.utcTime,
      "global-settings": _ctx.globalSettings,
      onEditSite: _cache[6] || (_cache[6] = function ($event) {
        return _this.isSiteBeingEdited = true;
      }),
      onCancelEditSite: _cache[7] || (_cache[7] = function ($event) {
        return _ctx.afterCancelEdit($event);
      }),
      onDelete: _cache[8] || (_cache[8] = function ($event) {
        return _ctx.afterDelete($event);
      }),
      onSave: function onSave($event) {
        return _ctx.afterSave($event.site, $event.settingValues, index, $event.isNew);
      }
    }, null, 8, ["site", "timezone-support-enabled", "utc-time", "global-settings", "onSave"])]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ButtonBar, {
    "site-is-being-edited": _ctx.isSiteBeingEdited,
    "has-prev": _ctx.hasPrev,
    hasNext: _ctx.hasNext,
    "offset-start": _ctx.offsetStart,
    "offset-end": _ctx.offsetEnd,
    "total-number-of-sites": _ctx.totalNumberOfSites,
    "is-loading": _ctx.isLoading,
    "search-term": _ctx.searchTerm,
    "is-searching": !!_ctx.activeSearchTerm,
    "onUpdate:searchTerm": _cache[9] || (_cache[9] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    onAdd: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.addNewEntity();
    }),
    onSearch: _cache[11] || (_cache[11] = function ($event) {
      return _ctx.searchSites($event);
    }),
    onPrev: _cache[12] || (_cache[12] = function ($event) {
      return _ctx.previousPage();
    }),
    onNext: _cache[13] || (_cache[13] = function ($event) {
      return _ctx.nextPage();
    })
  }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])])], 512);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=template&id=35a6a1b6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=template&id=d473a694

var ButtonBarvue_type_template_id_d473a694_hoisted_1 = {
  class: "sitesButtonBar clearfix"
};
var ButtonBarvue_type_template_id_d473a694_hoisted_2 = {
  class: "search"
};
var ButtonBarvue_type_template_id_d473a694_hoisted_3 = ["value", "placeholder", "disabled"];
var ButtonBarvue_type_template_id_d473a694_hoisted_4 = ["title"];
var ButtonBarvue_type_template_id_d473a694_hoisted_5 = {
  class: "paging"
};
var ButtonBarvue_type_template_id_d473a694_hoisted_6 = ["disabled"];
var ButtonBarvue_type_template_id_d473a694_hoisted_7 = {
  style: {
    "cursor": "pointer"
  }
};
var ButtonBarvue_type_template_id_d473a694_hoisted_8 = {
  class: "counter"
};
var ButtonBarvue_type_template_id_d473a694_hoisted_9 = ["disabled"];
var ButtonBarvue_type_template_id_d473a694_hoisted_10 = {
  style: {
    "cursor": "pointer"
  },
  class: "pointer"
};
function ButtonBarvue_type_template_id_d473a694_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ButtonBarvue_type_template_id_d473a694_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn addSite", {
      disabled: _ctx.siteIsBeingEdited
    }]),
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.addNewEntity();
    }),
    tabindex: "1"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.availableTypes.length > 1 ? _ctx.translate('SitesManager_AddMeasurable') : _ctx.translate('SitesManager_AddSite')), 3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess && _ctx.availableTypes]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ButtonBarvue_type_template_id_d473a694_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    value: _ctx.searchTerm,
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    placeholder: _ctx.translate('Actions_SubmenuSitesearch'),
    type: "text",
    disabled: _ctx.siteIsBeingEdited
  }, null, 40, ButtonBarvue_type_template_id_d473a694_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.searchSite();
    }),
    title: _ctx.translate('General_ClickToSearch'),
    class: "search_ico icon-search"
  }, null, 8, ButtonBarvue_type_template_id_d473a694_hoisted_4)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext || _ctx.isSearching]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ButtonBarvue_type_template_id_d473a694_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn prev",
    disabled: _ctx.hasPrev && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? undefined : true,
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.previousPage();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_7, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 8, ButtonBarvue_type_template_id_d473a694_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.paginationText), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn next",
    disabled: _ctx.hasNext && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? undefined : true,
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.nextPage();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 8, ButtonBarvue_type_template_id_d473a694_hoisted_9)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext]])]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=template&id=d473a694

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=script&lang=ts



/* harmony default export */ var ButtonBarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    siteIsBeingEdited: {
      type: Boolean,
      required: true
    },
    hasPrev: {
      type: Boolean,
      required: true
    },
    hasNext: {
      type: Boolean,
      required: true
    },
    offsetStart: {
      type: Number,
      required: true
    },
    offsetEnd: {
      type: Number,
      required: true
    },
    totalNumberOfSites: {
      type: Number
    },
    isLoading: {
      type: Boolean,
      required: true
    },
    searchTerm: {
      type: String,
      required: true
    },
    isSearching: {
      type: Boolean,
      required: true
    }
  },
  emits: ['add', 'search', 'prev', 'next', 'update:searchTerm'],
  created: function created() {
    src_SiteTypesStore_SiteTypesStore.init();
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown, 50);
  },
  computed: {
    hasSuperUserAccess: function hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    availableTypes: function availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    paginationText: function paginationText() {
      var text;

      if (this.isSearching) {
        text = Object(external_CoreHome_["translate"])('General_PaginationWithoutTotal', "".concat(this.offsetStart), "".concat(this.offsetEnd));
      } else {
        text = Object(external_CoreHome_["translate"])('General_Pagination', "".concat(this.offsetStart), "".concat(this.offsetEnd), this.totalNumberOfSites === null ? '?' : "".concat(this.totalNumberOfSites));
      }

      return " ".concat(text, " ");
    }
  },
  methods: {
    addNewEntity: function addNewEntity() {
      this.$emit('add');
    },
    searchSite: function searchSite() {
      if (this.siteIsBeingEdited) {
        return;
      }

      this.$emit('search');
    },
    previousPage: function previousPage() {
      this.$emit('prev');
    },
    nextPage: function nextPage() {
      this.$emit('next');
    },
    onKeydown: function onKeydown(event) {
      var _this = this;

      setTimeout(function () {
        if (event.key === 'Enter') {
          _this.searchSiteOnEnter(event);

          return;
        }

        _this.$emit('update:searchTerm', event.target.value);
      });
    },
    searchSiteOnEnter: function searchSiteOnEnter(event) {
      event.preventDefault();
      this.searchSite();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue



ButtonBarvue_type_script_lang_ts.render = ButtonBarvue_type_template_id_d473a694_render

/* harmony default export */ var ButtonBar = (ButtonBarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=template&id=b900a09c

var SiteFieldsvue_type_template_id_b900a09c_hoisted_1 = ["idsite", "type"];
var SiteFieldsvue_type_template_id_b900a09c_hoisted_2 = {
  class: "card-content"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_3 = {
  key: 0,
  class: "row"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_4 = {
  class: "col m3"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_5 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_6 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_7 = ["target", "title", "href"];
var SiteFieldsvue_type_template_id_b900a09c_hoisted_8 = {
  class: "col m4"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_9 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_10 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_11 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_12 = {
  class: "title"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_13 = {
  class: "col m4"
};
var SiteFieldsvue_type_template_id_b900a09c_hoisted_14 = {
  class: "title"
};

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": ");

var _hoisted_16 = ["href"];
var _hoisted_17 = {
  key: 0
};
var _hoisted_18 = {
  class: "title"
};
var _hoisted_19 = {
  key: 1
};
var _hoisted_20 = {
  class: "title"
};
var _hoisted_21 = {
  key: 2
};
var _hoisted_22 = {
  class: "title"
};
var _hoisted_23 = {
  class: "col m1 text-right"
};
var _hoisted_24 = ["title"];

var _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);

var _hoisted_26 = [_hoisted_25];
var _hoisted_27 = ["title"];

var _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);

var _hoisted_29 = [_hoisted_28];
var _hoisted_30 = {
  key: 1
};
var _hoisted_31 = {
  class: "form-group row"
};
var _hoisted_32 = {
  class: "col s12 m6 input-field"
};
var _hoisted_33 = ["placeholder"];

var _hoisted_34 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "col s12 m6"
}, null, -1);

var _hoisted_35 = {
  id: "timezoneHelpText",
  class: "inline-help-node"
};
var _hoisted_36 = {
  key: 0
};

var _hoisted_37 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_38 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_39 = {
  class: "editingSiteFooter"
};
var _hoisted_40 = ["disabled", "value"];
var _hoisted_41 = ["disabled"];
var _hoisted_42 = ["innerHTML"];
function SiteFieldsvue_type_template_id_b900a09c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$theSite$excluded, _ctx$theSite$excluded2, _ctx$theSite$excluded3;

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_GroupedSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSettings");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["site card hoverable", {
      'editingSite': !!_ctx.editMode
    }]),
    idsite: _ctx.theSite.idsite,
    type: _ctx.theSite.type,
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_2, [!_ctx.editMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.idsite), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Type')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentType.name), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.availableTypes.length > 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: _ctx.isInternalSetupUrl ? '_self' : '_blank',
    title: _ctx.translate('SitesManager_ShowTrackingTag'),
    href: _ctx.setupUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ShowTrackingTag')), 9, SiteFieldsvue_type_template_id_b900a09c_hoisted_7)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.idsite && _ctx.howToSetupUrl]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Timezone')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.timezone_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Currency')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.currency_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Ecommerce')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.ecommerce === 1 || _ctx.theSite.ecommerce === '1']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuSitesearch')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.sitesearch === 1 || _ctx.theSite.sitesearch === '1']])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Urls')), 1), _hoisted_15, (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.theSite.alias_urls, function (url, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: url
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      rel: "noreferrer noopener",
      href: url
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(url) + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(index === _ctx.theSite.alias_urls.length - 1 ? '' : ', '), 9, _hoisted_16)]);
  }), 128))]), (_ctx$theSite$excluded = _ctx.theSite.excluded_ips) !== null && _ctx$theSite$excluded !== void 0 && _ctx$theSite$excluded.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedIps')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_ips.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$theSite$excluded2 = _ctx.theSite.excluded_parameters) !== null && _ctx$theSite$excluded2 !== void 0 && _ctx$theSite$excluded2.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedParameters')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_parameters.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$theSite$excluded3 = _ctx.theSite.excluded_user_agents) !== null && _ctx$theSite$excluded3 !== void 0 && _ctx$theSite$excluded3.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedUserAgents')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_user_agents.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "table-action",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.editSite();
    }),
    title: _ctx.translate('General_Edit')
  }, _hoisted_26, 8, _hoisted_24)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "table-action",
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.getMessagesToWarnOnSiteRemoval();
    }),
    title: _ctx.translate('General_Delete')
  }, _hoisted_29, 8, _hoisted_27), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.idsite]])])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.theSite.name = $event;
    }),
    maxlength: "90",
    placeholder: _ctx.translate('General_Name')
  }, null, 8, _hoisted_33), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.theSite.name]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1)]), _hoisted_34]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading
  }, null, 8, ["loading"]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.measurableSettings, function (settingsPerPlugin) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: settingsPerPlugin.pluginName
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSettings, {
      "group-name": settingsPerPlugin.pluginName,
      settings: settingsPerPlugin.settings,
      "all-setting-values": _ctx.settingValues,
      onChange: function onChange($event) {
        return _ctx.settingValues["".concat(settingsPerPlugin.pluginName, ".").concat($event.name)] = $event.value;
      }
    }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"])]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "currency",
    modelValue: _ctx.theSite.currency,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.theSite.currency = $event;
    }),
    title: _ctx.translate('SitesManager_Currency'),
    "inline-help": _ctx.translate('SitesManager_CurrencySymbolWillBeUsedForGoals'),
    options: _ctx.currencies
  }, null, 8, ["modelValue", "title", "inline-help", "options"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "timezone",
    modelValue: _ctx.theSite.timezone,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return _ctx.theSite.timezone = $event;
    }),
    title: _ctx.translate('SitesManager_Timezone'),
    "inline-help": '#timezoneHelpText',
    options: _ctx.timezones
  }, null, 8, ["modelValue", "title", "options"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.timezoneSupportEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_36, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AdvancedTimezoneSupportNotFound')) + " ", 1), _hoisted_37])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.utcTimeIs) + " ", 1), _hoisted_38, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    disabled: _ctx.isSaving,
    type: "submit",
    class: "btn",
    value: _ctx.translate('General_Save'),
    onClick: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.saveSite();
    })
  }, null, 8, _hoisted_40), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn btn-link",
    disabled: _ctx.isSaving,
    onClick: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.cancelEditSite(_ctx.site);
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel', '', '')), 9, _hoisted_41)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showRemoveDialog,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.showRemoveDialog = $event;
    }),
    onConfirmed: _ctx.deleteSite
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.removeDialogTitle), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_DeleteSiteExplanation')), 1), _ctx.deleteSiteExplanation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
        key: 0,
        innerHTML: _ctx.$sanitize(_ctx.deleteSiteExplanation)
      }, null, 8, _hoisted_42)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])], 10, SiteFieldsvue_type_template_id_b900a09c_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=template&id=b900a09c

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=script&lang=ts
function SiteFieldsvue_type_script_lang_ts_slicedToArray(arr, i) { return SiteFieldsvue_type_script_lang_ts_arrayWithHoles(arr) || SiteFieldsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || SiteFieldsvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || SiteFieldsvue_type_script_lang_ts_nonIterableRest(); }

function SiteFieldsvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function SiteFieldsvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return SiteFieldsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return SiteFieldsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function SiteFieldsvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function SiteFieldsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function SiteFieldsvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }







var timezoneOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
  return src_TimezoneStore_TimezoneStore.timezones.value.map(function (_ref) {
    var group = _ref.group,
        label = _ref.label,
        code = _ref.code;
    return {
      group: group,
      key: label,
      value: code
    };
  });
});

function isSiteNew(site) {
  return typeof site.idsite === 'undefined';
}

/* harmony default export */ var SiteFieldsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    site: {
      type: Object,
      required: true
    },
    timezoneSupportEnabled: {
      type: Boolean
    },
    utcTime: {
      type: Date,
      required: true
    },
    globalSettings: {
      type: Object,
      required: true
    }
  },
  data: function data() {
    return {
      isLoading: false,
      isSaving: false,
      editMode: false,
      theSite: Object.assign({}, this.site),
      measurableSettings: [],
      settingValues: {},
      showRemoveDialog: false,
      deleteSiteExplanation: ''
    };
  },
  components: {
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"],
    Field: external_CorePluginsAdmin_["Field"],
    GroupedSettings: external_CorePluginsAdmin_["GroupedSettings"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  emits: ['delete', 'editSite', 'cancelEditSite', 'save'],
  created: function created() {
    src_CurrencyStore_CurrencyStore.init();
    src_TimezoneStore_TimezoneStore.init();
    src_SiteTypesStore_SiteTypesStore.init();
    this.onSiteChanged();
  },
  watch: {
    site: function site() {
      this.onSiteChanged();
    },
    measurableSettings: function measurableSettings(settings) {
      if (!settings.length) {
        return;
      }

      var settingValues = {};
      settings.forEach(function (settingsForPlugin) {
        settingsForPlugin.settings.forEach(function (setting) {
          settingValues["".concat(settingsForPlugin.pluginName, ".").concat(setting.name)] = setting.value;
        });
      });
      this.settingValues = settingValues;
    }
  },
  methods: {
    onSiteChanged: function onSiteChanged() {
      var site = this.site;
      this.theSite = Object.assign({}, site);
      var isNew = isSiteNew(site);

      if (isNew) {
        var globalSettings = this.globalSettings;
        this.theSite.timezone = globalSettings.defaultTimezone;
        this.theSite.currency = globalSettings.defaultCurrency;
      }

      var forcedEditSiteId = src_SiteTypesStore_SiteTypesStore.getEditSiteIdParameter();

      if (isNew || forcedEditSiteId && "".concat(site.idsite) === forcedEditSiteId) {
        this.editSite();
      }
    },
    editSite: function editSite() {
      var _this = this;

      this.editMode = true;
      this.$emit('editSite', {
        idSite: this.theSite.idsite
      });
      this.measurableSettings = [];

      if (isSiteNew(this.theSite)) {
        if (!this.currentType) {
          return;
        }

        this.measurableSettings = this.currentType.settings || [];
        return;
      }

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getSiteSettings',
        idSite: this.theSite.idsite
      }).then(function (settings) {
        _this.measurableSettings = settings;
      }).finally(function () {
        _this.isLoading = false;
      });
    },
    saveSite: function saveSite() {
      var _this2 = this;

      if (this.isSaving) {
        return; // saving already in progress
      }

      this.isSaving = true;
      var values = {
        siteName: this.theSite.name,
        timezone: this.theSite.timezone,
        currency: this.theSite.currency,
        type: this.theSite.type,
        settingValues: {}
      };
      var isNew = isSiteNew(this.theSite);
      var apiMethod = 'SitesManager.addSite';

      if (!isNew) {
        apiMethod = 'SitesManager.updateSite';
        values.idSite = this.theSite.idsite;
      } // process measurable settings


      Object.entries(this.settingValues).forEach(function (_ref2) {
        var _ref3 = SiteFieldsvue_type_script_lang_ts_slicedToArray(_ref2, 2),
            fullName = _ref3[0],
            fieldValue = _ref3[1];

        var _fullName$split = fullName.split('.'),
            _fullName$split2 = SiteFieldsvue_type_script_lang_ts_slicedToArray(_fullName$split, 2),
            pluginName = _fullName$split2[0],
            name = _fullName$split2[1];

        var settingValues = values.settingValues;

        if (!settingValues[pluginName]) {
          settingValues[pluginName] = [];
        }

        var value = fieldValue;

        if (fieldValue === false) {
          value = '0';
        } else if (fieldValue === true) {
          value = '1';
        } else if (Array.isArray(fieldValue)) {
          value = fieldValue.filter(function (x) {
            return !!x;
          });
        }

        settingValues[pluginName].push({
          name: name,
          value: value
        });
      });
      external_CoreHome_["AjaxHelper"].post({
        method: apiMethod
      }, values).then(function (response) {
        _this2.editMode = false;

        if (!_this2.theSite.idsite && response && response.value) {
          _this2.theSite.idsite = "".concat(response.value);
        }

        var timezoneInfo = src_TimezoneStore_TimezoneStore.timezones.value.find(function (t) {
          return t.code === _this2.theSite.timezone;
        });
        _this2.theSite.timezone_name = (timezoneInfo === null || timezoneInfo === void 0 ? void 0 : timezoneInfo.label) || _this2.theSite.timezone;

        if (_this2.theSite.currency) {
          _this2.theSite.currency_name = src_CurrencyStore_CurrencyStore.currencies.value[_this2.theSite.currency];
        }

        var notificationId = external_CoreHome_["NotificationsStore"].show({
          message: isNew ? Object(external_CoreHome_["translate"])('SitesManager_WebsiteCreated') : Object(external_CoreHome_["translate"])('SitesManager_WebsiteUpdated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationId);
        src_SiteTypesStore_SiteTypesStore.removeEditSiteIdParameterFromHash();

        _this2.$emit('save', {
          site: _this2.theSite,
          settingValues: values.settingValues,
          isNew: isNew
        });
      }).finally(function () {
        _this2.isSaving = false;
      });
    },
    cancelEditSite: function cancelEditSite(site) {
      this.editMode = false;
      src_SiteTypesStore_SiteTypesStore.removeEditSiteIdParameterFromHash();
      this.$emit('cancelEditSite', {
        site: site,
        element: this.$refs.root
      });
    },
    deleteSite: function deleteSite(password) {
      var _this3 = this;

      external_CoreHome_["AjaxHelper"].post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.deleteSite'
      }, {
        passwordConfirmation: password
      }).then(function () {
        _this3.$emit('delete', _this3.theSite);
      });
    },
    getMessagesToWarnOnSiteRemoval: function getMessagesToWarnOnSiteRemoval() {
      var _this4 = this;

      external_CoreHome_["AjaxHelper"].post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.getMessagesToWarnOnSiteRemoval'
      }).then(function (response) {
        _this4.deleteSiteExplanation = '';

        if (response.length) {
          _this4.deleteSiteExplanation += response.join('<br>');
        }

        _this4.showRemoveDialog = true;
      });
    }
  },
  computed: {
    availableTypes: function availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    setupUrl: function setupUrl() {
      var site = this.theSite;
      var suffix = '';
      var connector = '';

      if (this.isInternalSetupUrl) {
        suffix = external_CoreHome_["MatomoUrl"].stringify({
          idSite: site.idsite,
          period: external_CoreHome_["MatomoUrl"].parsed.value.period,
          date: external_CoreHome_["MatomoUrl"].parsed.value.date,
          updated: 'false'
        });
        connector = this.howToSetupUrl.indexOf('?') === -1 ? '?' : '&';
      }

      return "".concat(this.howToSetupUrl).concat(connector).concat(suffix);
    },
    utcTimeIs: function utcTimeIs() {
      var utcTime = this.utcTime;

      var formatTimePart = function formatTimePart(n) {
        return n.toString().padStart(2, '0');
      };

      var hours = formatTimePart(utcTime.getHours());
      var minutes = formatTimePart(utcTime.getMinutes());
      var seconds = formatTimePart(utcTime.getSeconds());
      var date = "".concat(Object(external_CoreHome_["format"])(this.utcTime), " ").concat(hours, ":").concat(minutes, ":").concat(seconds);
      return Object(external_CoreHome_["translate"])('SitesManager_UTCTimeIs', date);
    },
    timezones: function timezones() {
      return timezoneOptions.value;
    },
    currencies: function currencies() {
      return src_CurrencyStore_CurrencyStore.currencies.value;
    },
    currentType: function currentType() {
      var site = this.site;
      var type = src_SiteTypesStore_SiteTypesStore.typesById.value[site.type];

      if (!type) {
        return {
          name: site.type
        };
      }

      return type;
    },
    howToSetupUrl: function howToSetupUrl() {
      var type = this.currentType;

      if (!type) {
        return undefined;
      }

      return type.howToSetupUrl;
    },
    isInternalSetupUrl: function isInternalSetupUrl() {
      var howToSetupUrl = this.howToSetupUrl;

      if (!howToSetupUrl) {
        return false;
      }

      return "".concat(howToSetupUrl).substring(0, 1) === '?';
    },
    removeDialogTitle: function removeDialogTitle() {
      return Object(external_CoreHome_["translate"])('SitesManager_DeleteConfirm', "\"".concat(this.theSite.name, "\" (idSite = ").concat(this.theSite.idsite, ")"));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue



SiteFieldsvue_type_script_lang_ts.render = SiteFieldsvue_type_template_id_b900a09c_render

/* harmony default export */ var SiteFields = (SiteFieldsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/GlobalSettingsStore/GlobalSettingsStore.ts
function GlobalSettingsStore_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function GlobalSettingsStore_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function GlobalSettingsStore_createClass(Constructor, protoProps, staticProps) { if (protoProps) GlobalSettingsStore_defineProperties(Constructor.prototype, protoProps); if (staticProps) GlobalSettingsStore_defineProperties(Constructor, staticProps); return Constructor; }

function GlobalSettingsStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var GlobalSettingsStore_GlobalSettingsStore = /*#__PURE__*/function () {
  function GlobalSettingsStore() {
    var _this = this;

    GlobalSettingsStore_classCallCheck(this, GlobalSettingsStore);

    GlobalSettingsStore_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      globalSettings: {
        keepURLFragmentsGlobal: false,
        defaultCurrency: '',
        defaultTimezone: '',
        excludedIpsGlobal: '',
        excludedQueryParametersGlobal: '',
        excludedUserAgentsGlobal: '',
        excludedReferrersGlobal: '',
        searchKeywordParametersGlobal: '',
        searchCategoryParametersGlobal: '',
        exclusionTypeForQueryParams: ''
      }
    }));

    GlobalSettingsStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState).isLoading;
    }));

    GlobalSettingsStore_defineProperty(this, "globalSettings", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState).globalSettings;
    }));
  }

  GlobalSettingsStore_createClass(GlobalSettingsStore, [{
    key: "init",
    value: function init() {
      return this.fetchGlobalSettings();
    }
  }, {
    key: "saveGlobalSettings",
    value: function saveGlobalSettings(settings) {
      var _this2 = this;

      this.privateState.isLoading = true;
      return external_CoreHome_["AjaxHelper"].post({
        module: 'SitesManager',
        format: 'json',
        action: 'setGlobalSettings'
      }, settings, {
        withTokenInUrl: true
      }).finally(function () {
        _this2.privateState.isLoading = false;
      });
    }
  }, {
    key: "fetchGlobalSettings",
    value: function fetchGlobalSettings() {
      var _this3 = this;

      this.privateState.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'SitesManager',
        action: 'getGlobalSettings'
      }).then(function (response) {
        _this3.privateState.globalSettings = Object.assign(Object.assign({}, response), {}, {
          // the API can return false for these
          excludedIpsGlobal: response.excludedIpsGlobal || '',
          excludedQueryParametersGlobal: response.excludedQueryParametersGlobal || '',
          excludedUserAgentsGlobal: response.excludedUserAgentsGlobal || '',
          excludedReferrersGlobal: response.excludedReferrersGlobal || '',
          searchKeywordParametersGlobal: response.searchKeywordParametersGlobal || '',
          searchCategoryParametersGlobal: response.searchCategoryParametersGlobal || '',
          exclusionTypeForQueryParams: response.exclusionTypeForQueryParams || ''
        });
      }).finally(function () {
        _this3.privateState.isLoading = false;
      });
    }
  }]);

  return GlobalSettingsStore;
}();

/* harmony default export */ var src_GlobalSettingsStore_GlobalSettingsStore = (new GlobalSettingsStore_GlobalSettingsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=script&lang=ts







/* harmony default export */ var SitesManagementvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    // TypeScript can't add state types if there are no properties (probably a bug in Vue)
    // so we add one dummy property to get the compile to work
    dummy: String
  },
  components: {
    MatomoDialog: external_CoreHome_["MatomoDialog"],
    ButtonBar: ButtonBar,
    SiteFields: SiteFields,
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  data: function data() {
    var currentDate = new Date();
    var utcTime = new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), currentDate.getUTCDate(), currentDate.getUTCHours(), currentDate.getUTCMinutes(), currentDate.getUTCSeconds());
    return {
      pageSize: 10,
      currentPage: 0,
      showAddSiteDialog: false,
      searchTerm: '',
      activeSearchTerm: '',
      fetchedSites: [],
      isLoadingInitialEntities: false,
      utcTime: utcTime,
      totalNumberOfSites: null,
      isSiteBeingEdited: false,
      fetchLimitedSitesAbortController: null
    };
  },
  created: function created() {
    var _this = this;

    src_TimezoneStore_TimezoneStore.init();
    src_SiteTypesStore_SiteTypesStore.init();
    src_GlobalSettingsStore_GlobalSettingsStore.init();
    this.isLoadingInitialEntities = true;
    Promise.all([src_SiteTypesStore_SiteTypesStore.fetchAvailableTypes(), this.fetchLimitedSitesWithAdminAccess(), this.getTotalNumberOfSites()]).then(function () {
      _this.triggerAddSiteIfRequested();
    }).finally(function () {
      _this.isLoadingInitialEntities = false;
    }); // if hash is #globalSettings, redirect to globalSettings action (we don't do it on
    // page load so the back button still works)

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return external_CoreHome_["MatomoUrl"].hashQuery.value;
    }, function () {
      _this.checkGlobalSettingsHash();
    });
  },
  computed: {
    sites: function sites() {
      var emptyIdSiteRows = this.fetchedSites.filter(function (s) {
        return !s.idsite;
      }).length;
      return this.fetchedSites.slice(0, this.pageSize + emptyIdSiteRows);
    },
    isLoading: function isLoading() {
      return !!this.fetchLimitedSitesAbortController || this.isLoadingInitialEntities || this.totalNumberOfSites === null || src_SiteTypesStore_SiteTypesStore.isLoading.value || src_TimezoneStore_TimezoneStore.isLoading.value || src_GlobalSettingsStore_GlobalSettingsStore.isLoading.value;
    },
    availableTypes: function availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    timezoneSupportEnabled: function timezoneSupportEnabled() {
      return src_TimezoneStore_TimezoneStore.timezoneSupportEnabled.value;
    },
    globalSettings: function globalSettings() {
      return src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value;
    },
    headlineText: function headlineText() {
      return Object(external_CoreHome_["translate"])('SitesManager_XManagement', this.availableTypes.length > 1 ? Object(external_CoreHome_["translate"])('General_Measurables') : Object(external_CoreHome_["translate"])('SitesManager_Sites'));
    },
    mainDescription: function mainDescription() {
      return Object(external_CoreHome_["translate"])('SitesManager_YouCurrentlyHaveAccessToNWebsites', "<strong>".concat(this.totalNumberOfSites, "</strong>"));
    },
    hasSuperUserAccess: function hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    superUserAccessMessage: function superUserAccessMessage() {
      return Object(external_CoreHome_["translate"])('SitesManager_SuperUserAccessCan', '<a href=\'#globalSettings\'>', '</a>');
    },
    hasPrev: function hasPrev() {
      return this.currentPage >= 1;
    },
    hasNext: function hasNext() {
      return this.fetchedSites.filter(function (s) {
        return !!s.idsite;
      }).length >= this.pageSize + 1;
    },
    offsetStart: function offsetStart() {
      return this.currentPage * this.pageSize + 1;
    },
    offsetEnd: function offsetEnd() {
      return this.offsetStart + this.sites.filter(function (s) {
        return !!s.idsite;
      }).length - 1;
    }
  },
  methods: {
    checkGlobalSettingsHash: function checkGlobalSettingsHash() {
      var newHash = external_CoreHome_["MatomoUrl"].hashQuery.value;

      if (external_CoreHome_["Matomo"].hasSuperUserAccess && (newHash === 'globalSettings' || newHash === '/globalSettings')) {
        external_CoreHome_["MatomoUrl"].updateLocation(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
          action: 'globalSettings'
        }));
      }
    },
    addNewEntity: function addNewEntity() {
      if (this.availableTypes.length > 1) {
        this.showAddSiteDialog = true;
      } else if (this.availableTypes.length === 1) {
        this.addSite(this.availableTypes[0].id);
      }
    },
    addSite: function addSite(typeId) {
      var type = typeId;
      var parameters = {
        isAllowed: true,
        measurableType: type
      };
      external_CoreHome_["Matomo"].postEvent('SitesManager.initAddSite', parameters);

      if (parameters && !parameters.isAllowed) {
        return;
      }

      if (!type) {
        type = 'website'; // todo shall we really hard code this or trigger an exception or so?
      }

      this.fetchedSites.unshift({
        type: type
      });
      this.isSiteBeingEdited = true;
    },
    afterCancelEdit: function afterCancelEdit(_ref) {
      var site = _ref.site,
          element = _ref.element;
      this.isSiteBeingEdited = false;

      if (!site.idsite) {
        this.fetchedSites = this.fetchedSites.filter(function (s) {
          return !!s.idsite;
        });
        return;
      }

      element.scrollIntoView();
    },
    fetchLimitedSitesWithAdminAccess: function fetchLimitedSitesWithAdminAccess() {
      var _this2 = this;

      var searchTerm = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

      if (this.fetchLimitedSitesAbortController) {
        this.fetchLimitedSitesAbortController.abort();
      }

      this.fetchLimitedSitesAbortController = new AbortController();
      var limit = this.pageSize + 1;
      var offset = this.currentPage * this.pageSize;
      var params = {
        method: 'SitesManager.getSitesWithAdminAccess',
        fetchAliasUrls: 1,
        limit: limit + offset,
        filter_offset: offset,
        filter_limit: limit
      };

      if (searchTerm) {
        params.pattern = searchTerm;
      }

      return external_CoreHome_["AjaxHelper"].fetch(params).then(function (sites) {
        _this2.fetchedSites = sites || [];
      }).then(function (sites) {
        _this2.activeSearchTerm = searchTerm;
        return sites;
      }).finally(function () {
        _this2.fetchLimitedSitesAbortController = null;
      });
    },
    getTotalNumberOfSites: function getTotalNumberOfSites() {
      var _this3 = this;

      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getSitesIdWithAdminAccess',
        filter_limit: '-1'
      }).then(function (sites) {
        _this3.totalNumberOfSites = sites.length;
      });
    },
    triggerAddSiteIfRequested: function triggerAddSiteIfRequested() {
      var forcedEditSiteId = src_SiteTypesStore_SiteTypesStore.getEditSiteIdParameter();
      var showaddsite = external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite;

      if (showaddsite === '1') {
        this.addNewEntity();
      } else if (forcedEditSiteId) {
        this.searchTerm = forcedEditSiteId;
        this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
      }
    },
    previousPage: function previousPage() {
      this.currentPage = Math.max(0, this.currentPage - 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    nextPage: function nextPage() {
      this.currentPage = Math.max(0, this.currentPage + 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    searchSites: function searchSites() {
      this.currentPage = 0;
      this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
    },
    afterDelete: function afterDelete(site) {
      var redirectParams = {
        showaddsite: 0
      }; // if the current idSite in the URL is the site we're deleting, then we have to make to
      // change it. otherwise, if a user goes to another page, the invalid idSite may cause
      // a fatal error.

      if (external_CoreHome_["MatomoUrl"].urlParsed.value.idSite === "".concat(site.idsite)) {
        var otherSite = this.sites.find(function (s) {
          return s.idsite !== site.idsite;
        });

        if (otherSite) {
          redirectParams = Object.assign(Object.assign({}, redirectParams), {}, {
            idSite: otherSite.idsite
          });
        }
      }

      external_CoreHome_["Matomo"].helper.redirect(redirectParams);
    },
    afterSave: function afterSave(site, settingValues, index, isNew) {
      var texttareaArrayParams = ['excluded_ips', 'excluded_parameters', 'excluded_user_agents', 'sitesearch_keyword_parameters', 'sitesearch_category_parameters'];
      var newSite = Object.assign({}, site);
      Object.values(settingValues).forEach(function (settings) {
        settings.forEach(function (setting) {
          if (setting.name === 'urls') {
            newSite.alias_urls = setting.value;
          } else if (texttareaArrayParams.indexOf(setting.name) !== -1) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            newSite[setting.name] = setting.value.join(', ');
          } else {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            newSite[setting.name] = setting.value;
          }
        });
      });
      this.fetchedSites[index] = newSite;

      if (isNew && this.totalNumberOfSites !== null) {
        this.totalNumberOfSites += 1;
      }

      this.isSiteBeingEdited = false;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue



SitesManagementvue_type_script_lang_ts.render = render

/* harmony default export */ var SitesManagement = (SitesManagementvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=template&id=26f983c2

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_1 = {
  class: "SitesManager"
};

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "globalSettings",
  id: "globalSettings"
}, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_3 = {
  id: "excludedIpsGlobalHelp",
  class: "inline-help-node"
};

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_6 = ["innerHTML"];
var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_7 = {
  id: "excludedUserAgentsGlobalHelp",
  class: "inline-help-node"
};

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_10 = {
  id: "excludedReferrersGlobalHelp",
  class: "inline-help-node"
};

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_16 = {
  id: "timezoneHelp",
  class: "inline-help-node"
};
var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_17 = {
  key: 0
};

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_20 = {
  id: "keepURLFragmentsHelp",
  class: "inline-help-node"
};
var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_21 = ["innerHTML"];
var ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_22 = {
  class: "alert alert-info"
};
function ManageGlobalSettingsvue_type_template_id_26f983c2_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ExcludeQueryParameterSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ExcludeQueryParameterSettings");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('SitesManager_GlobalWebsitesSettings')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_HelpExcludedIpAddresses', '1.2.3.4/24', '1.2.3.*', '1.2.*.*')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_4, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.yourCurrentIpAddressIs)
      }, null, 8, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_6)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp1')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_8, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalListExcludedUserAgents_Desc')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp2')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp3', '/bot|spider|crawl|scanner/i')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelp')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_11, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpDetails')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpExamples', 'www.example.org', 'http://example.org/mypath', 'https://www.example.org/?param=1', 'https://sub.example.org/')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_14, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpSubDomains', '.sub.example.org', 'http://sub.example.org/mypath', 'https://new.sub.example.org/')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.timezoneSupportEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AdvancedTimezoneSupportNotFound')) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_18])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_UTCTimeIs', _ctx.utcTimeDate)) + " ", 1), ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        innerHTML: _ctx.$sanitize(_ctx.keepUrlFragmentHelp)
      }, null, 8, ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_21), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_KeepURLFragmentsHelp2')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "textarea",
        name: "excludedIpsGlobal",
        "var-type": "array",
        modelValue: _ctx.excludedIpsGlobal,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.excludedIpsGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_ListOfIpsToBeExcludedOnAllWebsites'),
        introduction: _ctx.translate('SitesManager_GlobalListExcludedIps'),
        "inline-help": '#excludedIpsGlobalHelp',
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ExcludeQueryParameterSettings, {
        exclusionTypeForQueryParams: _ctx.exclusionTypeForQueryParams,
        "onUpdate:exclusionTypeForQueryParams": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.exclusionTypeForQueryParams = $event;
        }),
        excludedQueryParametersGlobal: _ctx.excludedQueryParametersGlobal,
        "onUpdate:excludedQueryParametersGlobal": _cache[2] || (_cache[2] = function ($event) {
          return _ctx.excludedQueryParametersGlobal = $event;
        }),
        commonSensitiveQueryParams: _ctx.commonSensitiveQueryParams
      }, null, 8, ["exclusionTypeForQueryParams", "excludedQueryParametersGlobal", "commonSensitiveQueryParams"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "textarea",
        name: "excludedUserAgentsGlobal",
        "var-type": "array",
        modelValue: _ctx.excludedUserAgentsGlobal,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          return _ctx.excludedUserAgentsGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_GlobalListExcludedUserAgents_Desc'),
        introduction: _ctx.translate('SitesManager_GlobalListExcludedUserAgents'),
        "inline-help": '#excludedUserAgentsGlobalHelp',
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "textarea",
        name: "excludedReferrersGlobal",
        "var-type": "array",
        modelValue: _ctx.excludedReferrersGlobal,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          return _ctx.excludedReferrersGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_GlobalListExcludedReferrersDesc'),
        introduction: _ctx.translate('SitesManager_GlobalListExcludedReferrers'),
        "inline-help": '#excludedReferrersGlobalHelp',
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "keepURLFragmentsGlobal",
        modelValue: _ctx.keepURLFragmentsGlobal,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
          return _ctx.keepURLFragmentsGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_KeepURLFragmentsLong'),
        introduction: _ctx.translate('SitesManager_KeepURLFragments'),
        "inline-help": '#keepURLFragmentsHelp',
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_TrackingSiteSearch')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteSearchUse')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_26f983c2_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SearchParametersNote')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SearchParametersNote2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "searchKeywordParametersGlobal",
        "var-type": "array",
        modelValue: _ctx.searchKeywordParametersGlobal,
        "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
          return _ctx.searchKeywordParametersGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_SearchKeywordLabel'),
        "inline-help": _ctx.translate('SitesManager_SearchKeywordParametersDesc'),
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "inline-help", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "searchCategoryParametersGlobal",
        "var-type": "array",
        modelValue: _ctx.searchCategoryParametersGlobal,
        "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
          return _ctx.searchCategoryParametersGlobal = $event;
        }),
        title: _ctx.translate('SitesManager_SearchCategoryLabel'),
        "inline-help": _ctx.searchCategoryParamsInlineHelp,
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "title", "inline-help", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "defaultTimezone",
        options: _ctx.timezoneOptions,
        title: _ctx.translate('SitesManager_SelectDefaultTimezone'),
        introduction: _ctx.translate('SitesManager_DefaultTimezoneForNewWebsites'),
        "inline-help": '#timezoneHelp',
        disabled: _ctx.isLoading,
        modelValue: _ctx.defaultTimezone,
        "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
          return _ctx.defaultTimezone = $event;
        })
      }, null, 8, ["options", "title", "introduction", "disabled", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "defaultCurrency",
        modelValue: _ctx.defaultCurrency,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
          return _ctx.defaultCurrency = $event;
        }),
        options: _ctx.currencies,
        title: _ctx.translate('SitesManager_SelectDefaultCurrency'),
        introduction: _ctx.translate('SitesManager_DefaultCurrencyForNewWebsites'),
        "inline-help": _ctx.translate('SitesManager_CurrencySymbolWillBeUsedForGoals'),
        disabled: _ctx.isLoading
      }, null, 8, ["modelValue", "options", "title", "introduction", "inline-help", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        saving: _ctx.isSaving,
        onConfirm: _cache[10] || (_cache[10] = function ($event) {
          return _ctx.saveGlobalSettings();
        })
      }, null, 8, ["saving"])];
    }),
    _: 1
  }, 8, ["content-title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess]])]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=template&id=26f983c2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ExcludeQueryParameterSettings.vue?vue&type=template&id=4fb96f9e

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_1 = {
  class: "siteManagerGlobalExcludedUrlParameters"
};
var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_2 = {
  id: "excludedQueryParametersGlobalHelp",
  class: "inline-help-node"
};
var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_3 = {
  id: "excludedQueryParametersGlobalExclusionTypeHelp",
  class: "inline-help-node"
};

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down"
}, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-up"
}, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_6 = {
  key: 0
};

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_11 = ["value"];
function ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ListOfQueryParametersToExclude', '/^sess.*|.*[dD]ate$/')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExclusionTypeDescriptionCommonSessionParameters')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.localExclusionTypeForQueryParams === 'common_session_parameters']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExclusionTypeDescriptionMatomoRecommendedPII')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.showListOfCommonExclusions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    href: "javascript:;",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showListOfCommonExclusions = true;
    }, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExclusionViewListLink')) + " ", 1), ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showListOfCommonExclusions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    href: "javascript:;",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showListOfCommonExclusions = false;
    }, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExclusionViewListLink')) + " ", 1), ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_5])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.showListOfCommonExclusions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.commonSensitiveQueryParams.join(', ')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_7, ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition', 'phpsessid, sessionid, ...')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.localExclusionTypeForQueryParams === 'matomo_recommended_pii']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExclusionTypeDescriptionCustom')) + " ", 1), ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_9, ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition', 'phpsessid, sessionid, ...')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.localExclusionTypeForQueryParams === 'custom']])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "exclusionType",
    introduction: _ctx.translate('SitesManager_GlobalListExcludedQueryParameters'),
    options: _ctx.exclusionTypeOptions,
    modelValue: _ctx.localExclusionTypeForQueryParams,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.localExclusionTypeForQueryParams = $event;
    }),
    "inline-help": '#excludedQueryParametersGlobalExclusionTypeHelp'
  }, null, 8, ["introduction", "options", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "textarea",
    name: "excludedQueryParametersGlobal",
    "var-type": "array",
    class: "limited-height-scrolling-textarea",
    modelValue: _ctx.localExcludedQueryParametersGlobal,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.localExcludedQueryParametersGlobal = $event;
    }),
    "model-value": _ctx.localExcludedQueryParametersGlobal.join('\n'),
    onInput: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.onInputExcludedQueryParametersGlobal($event.target.value);
    }),
    title: _ctx.translate('SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites'),
    "inline-help": '#excludedQueryParametersGlobalHelp'
  }, null, 8, ["modelValue", "model-value", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    onClick: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.addCommonPIIQueryParams();
    }),
    class: "btn",
    value: _ctx.translate('SitesManager_AddSensibleExclusionsToMyCustomListButtonText')
  }, null, 8, ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_hoisted_11)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.localExclusionTypeForQueryParams === 'custom']])]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ExcludeQueryParameterSettings.vue?vue&type=template&id=4fb96f9e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ExcludeQueryParameterSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var ExcludeQueryParameterSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  props: {
    exclusionTypeForQueryParams: {
      type: String,
      default: 'common_session_parameters'
    },
    excludedQueryParametersGlobal: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    commonSensitiveQueryParams: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    return {
      localExclusionTypeForQueryParams: this.exclusionTypeForQueryParams,
      localExcludedQueryParametersGlobal: this.excludedQueryParametersGlobal,
      exclusionTypeOptions: [{
        value: Object(external_CoreHome_["translate"])('SitesManager_ExclusionTypeOptionCommonSessionParameters'),
        key: 'common_session_parameters'
      }, {
        value: Object(external_CoreHome_["translate"])('SitesManager_ExclusionTypeOptionMatomoRecommendedPII'),
        key: 'matomo_recommended_pii'
      }, {
        value: Object(external_CoreHome_["translate"])('SitesManager_ExclusionTypeOptionCustom'),
        key: 'custom'
      }],
      showListOfCommonExclusions: false
    };
  },
  watch: {
    exclusionTypeForQueryParams: {
      handler: function handler(newExclusionType) {
        this.localExclusionTypeForQueryParams = newExclusionType;
      }
    },
    localExclusionTypeForQueryParams: {
      handler: function handler(newExclusionType) {
        this.updateExclusionType(newExclusionType);
      },
      immediate: true
    },
    excludedQueryParametersGlobal: {
      handler: function handler(excludedQueryParametersGlobal) {
        this.localExcludedQueryParametersGlobal = excludedQueryParametersGlobal;
      }
    }
  },
  methods: {
    updateExclusionType: function updateExclusionType(value) {
      if (value !== 'custom') {
        this.localExcludedQueryParametersGlobal = [];
        this.onInputExcludedQueryParametersGlobal('');
      }

      this.$emit('update:exclusionTypeForQueryParams', value);
    },
    onInputExcludedQueryParametersGlobal: function onInputExcludedQueryParametersGlobal(value) {
      var valueArray = value.split('\n');
      this.$emit('update:excludedQueryParametersGlobal', valueArray);
    },
    addCommonPIIQueryParams: function addCommonPIIQueryParams() {
      var _this = this;

      var updatedParams = this.localExcludedQueryParametersGlobal.filter(function (param) {
        return !_this.commonSensitiveQueryParams.includes(param);
      });
      updatedParams = updatedParams.concat(this.commonSensitiveQueryParams);
      this.localExcludedQueryParametersGlobal = updatedParams;
      this.$emit('update:excludedQueryParametersGlobal', updatedParams);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ExcludeQueryParameterSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ExcludeQueryParameterSettings.vue



ExcludeQueryParameterSettingsvue_type_script_lang_ts.render = ExcludeQueryParameterSettingsvue_type_template_id_4fb96f9e_render

/* harmony default export */ var ExcludeQueryParameterSettings = (ExcludeQueryParameterSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=script&lang=ts







/* harmony default export */ var ManageGlobalSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ExcludeQueryParameterSettings: ExcludeQueryParameterSettings,
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  props: {
    commonSensitiveQueryParams: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    var currentDate = new Date();
    var utcTime = new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), currentDate.getUTCDate(), currentDate.getUTCHours(), currentDate.getUTCMinutes(), currentDate.getUTCSeconds());
    var settings = src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value;
    return {
      currentIpAddress: null,
      utcTime: utcTime,
      keepURLFragmentsGlobal: settings.keepURLFragmentsGlobal,
      defaultTimezone: settings.defaultTimezone,
      defaultCurrency: settings.defaultCurrency,
      excludedIpsGlobal: (settings.excludedIpsGlobal || '').split(','),
      excludedQueryParametersGlobal: (settings.excludedQueryParametersGlobal || '').split(','),
      excludedUserAgentsGlobal: (settings.excludedUserAgentsGlobal || '').split(','),
      excludedReferrersGlobal: (settings.excludedReferrersGlobal || '').split(','),
      searchKeywordParametersGlobal: (settings.searchKeywordParametersGlobal || '').split(','),
      searchCategoryParametersGlobal: (settings.searchCategoryParametersGlobal || '').split(','),
      isSaving: false,
      exclusionTypeForQueryParams: settings.exclusionTypeForQueryParams
    };
  },
  created: function created() {
    var _this = this;

    src_CurrencyStore_CurrencyStore.init();
    src_TimezoneStore_TimezoneStore.init();
    src_GlobalSettingsStore_GlobalSettingsStore.init();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value;
    }, function (settings) {
      _this.keepURLFragmentsGlobal = settings.keepURLFragmentsGlobal;
      _this.defaultTimezone = settings.defaultTimezone;
      _this.defaultCurrency = settings.defaultCurrency;
      _this.excludedIpsGlobal = (settings.excludedIpsGlobal || '').split(',');
      _this.excludedQueryParametersGlobal = (settings.excludedQueryParametersGlobal || '').split(',');
      _this.excludedUserAgentsGlobal = (settings.excludedUserAgentsGlobal || '').split(',');
      _this.excludedReferrersGlobal = (settings.excludedReferrersGlobal || '').split(',');
      _this.searchKeywordParametersGlobal = (settings.searchKeywordParametersGlobal || '').split(',');
      _this.searchCategoryParametersGlobal = (settings.searchCategoryParametersGlobal || '').split(',');
      _this.exclusionTypeForQueryParams = settings.exclusionTypeForQueryParams;
    });
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'API.getIpFromHeader'
    }).then(function (response) {
      _this.currentIpAddress = response.value;
    });
  },
  methods: {
    saveGlobalSettings: function saveGlobalSettings() {
      var _this2 = this;

      this.isSaving = true;
      src_GlobalSettingsStore_GlobalSettingsStore.saveGlobalSettings({
        keepURLFragments: this.keepURLFragmentsGlobal,
        currency: this.defaultCurrency,
        timezone: this.defaultTimezone,
        excludedIps: this.excludedIpsGlobal.join(','),
        excludedQueryParameters: this.excludedQueryParametersGlobal.join(','),
        excludedUserAgents: this.excludedUserAgentsGlobal.join(','),
        excludedReferrers: this.excludedReferrersGlobal.join(','),
        searchKeywordParameters: this.searchKeywordParametersGlobal.join(','),
        searchCategoryParameters: this.searchCategoryParametersGlobal.join(','),
        exclusionTypeForQueryParams: this.exclusionTypeForQueryParams
      }).then(function () {
        external_CoreHome_["Matomo"].helper.redirect({
          showaddsite: false
        });
      }).finally(function () {
        _this2.isSaving = false;
      });
    }
  },
  computed: {
    isLoading: function isLoading() {
      return src_GlobalSettingsStore_GlobalSettingsStore.isLoading.value || src_TimezoneStore_TimezoneStore.isLoading.value || src_CurrencyStore_CurrencyStore.isLoading.value;
    },
    timezones: function timezones() {
      return src_TimezoneStore_TimezoneStore.timezones.value;
    },
    timezoneOptions: function timezoneOptions() {
      return this.timezones.map(function (_ref) {
        var group = _ref.group,
            label = _ref.label,
            code = _ref.code;
        return {
          group: group,
          key: label,
          value: code
        };
      });
    },
    currencies: function currencies() {
      return src_CurrencyStore_CurrencyStore.currencies.value;
    },
    hasSuperUserAccess: function hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    yourCurrentIpAddressIs: function yourCurrentIpAddressIs() {
      return Object(external_CoreHome_["translate"])('SitesManager_YourCurrentIpAddressIs', "<i>".concat(this.currentIpAddress, "</i>"));
    },
    timezoneSupportEnabled: function timezoneSupportEnabled() {
      return src_TimezoneStore_TimezoneStore.timezoneSupportEnabled.value;
    },
    utcTimeDate: function utcTimeDate() {
      var utcTime = this.utcTime;

      var formatTimePart = function formatTimePart(n) {
        return n.toString().padStart(2, '0');
      };

      var hours = formatTimePart(utcTime.getHours());
      var minutes = formatTimePart(utcTime.getMinutes());
      var seconds = formatTimePart(utcTime.getSeconds());
      return "".concat(Object(external_CoreHome_["format"])(this.utcTime), " ").concat(hours, ":").concat(minutes, ":").concat(seconds);
    },
    keepUrlFragmentHelp: function keepUrlFragmentHelp() {
      return Object(external_CoreHome_["translate"])('SitesManager_KeepURLFragmentsHelp', '<em>#</em>', '<em>example.org/index.html#first_section</em>', '<em>example.org/index.html</em>');
    },
    searchCategoryParamsInlineHelp: function searchCategoryParamsInlineHelp() {
      var parts = [Object(external_CoreHome_["translate"])('Goals_Optional'), Object(external_CoreHome_["translate"])('SitesManager_SearchCategoryDesc'), Object(external_CoreHome_["translate"])('SitesManager_SearchCategoryParametersDesc')];
      return parts.join(' ');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue



ManageGlobalSettingsvue_type_script_lang_ts.render = ManageGlobalSettingsvue_type_template_id_26f983c2_render

/* harmony default export */ var ManageGlobalSettings = (ManageGlobalSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=template&id=3425106e


var SiteWithoutDatavue_type_template_id_3425106e_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-left"
}, null, -1);

var SiteWithoutDatavue_type_template_id_3425106e_hoisted_2 = {
  id: "start-tracking-data-header"
};
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_3 = {
  key: 0,
  class: "row",
  id: "start-tracking-detection"
};
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_4 = ["src", "alt"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_5 = ["href"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_6 = {
  class: "row",
  id: "start-tracking-method-list"
};

var SiteWithoutDatavue_type_template_id_3425106e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search"
}, null, -1);

var SiteWithoutDatavue_type_template_id_3425106e_hoisted_8 = ["href", "onClick"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_9 = ["src"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_10 = {
  class: "list-entry-text"
};
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_11 = {
  id: "start-tracking-skip"
};
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_12 = ["href"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_13 = ["data-method"];
var SiteWithoutDatavue_type_template_id_3425106e_hoisted_14 = ["src", "alt"];
function SiteWithoutDatavue_type_template_id_3425106e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_VueEntryContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("VueEntryContainer");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [_ctx.showMethodDetails ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    id: "start-tracking-back",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.showOverview();
    }, ["prevent"]))
  }, [SiteWithoutDatavue_type_template_id_3425106e_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Mobile_NavigationBack')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h1", SiteWithoutDatavue_type_template_id_3425106e_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.headline), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
    id: "start-tracking-cta",
    html: _ctx.ctaContent
  }, null, 8, ["html"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": "".concat(_ctx.translate('SitesManager_DetectingYourSite'), "\u2026"),
    loading: _ctx.loading
  }, null, 8, ["loading-message", "loading"]), !_ctx.loading && !_ctx.showMethodDetails ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.recommendedMethod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteWithoutDatavue_type_template_id_3425106e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: _ctx.recommendedMethod.icon,
    alt: "".concat(_ctx.recommendedMethod.name, " logo")
  }, null, 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationTitle), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationText), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#".concat(_ctx.recommendedMethod.id.toLowerCase()),
    class: "btn",
    id: "showMethod",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showMethod(_ctx.recommendedMethod.id);
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationButton), 9, SiteWithoutDatavue_type_template_id_3425106e_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteWithoutDatavue_type_template_id_3425106e_hoisted_6, [SiteWithoutDatavue_type_template_id_3425106e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataOtherInstallMethods')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataOtherInstallMethodsIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.trackingMethods, function (method) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "list-entry",
      key: method.id
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "#".concat(method.id.toLowerCase()),
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
        return _ctx.showMethod(method.id);
      }, ["prevent"])
    }, [method.icon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
      key: 0,
      src: method.icon,
      class: "list-entry-icon"
    }, null, 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteWithoutDatavue_type_template_id_3425106e_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(method.name), 1)], 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_8)]);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteWithoutDatavue_type_template_id_3425106e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataNotYetReady')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataTemporarilyHidePage')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.ignoreSitesWithoutDataLink,
    class: "ignoreSitesWithoutData"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataHidePageForHour')), 9, SiteWithoutDatavue_type_template_id_3425106e_hoisted_12)])], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showMethodDetails ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 2,
    id: "start-tracking-details",
    "data-method": _ctx.showMethodDetails.id
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: _ctx.showMethodDetails.icon,
    alt: "".concat(_ctx.showMethodDetails.name, " logo")
  }, null, 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
    html: _ctx.showMethodDetails.content
  }, null, 8, ["html"])], 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=template&id=3425106e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=script&lang=ts
function SiteWithoutDatavue_type_script_lang_ts_slicedToArray(arr, i) { return SiteWithoutDatavue_type_script_lang_ts_arrayWithHoles(arr) || SiteWithoutDatavue_type_script_lang_ts_iterableToArrayLimit(arr, i) || SiteWithoutDatavue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || SiteWithoutDatavue_type_script_lang_ts_nonIterableRest(); }

function SiteWithoutDatavue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function SiteWithoutDatavue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return SiteWithoutDatavue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return SiteWithoutDatavue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function SiteWithoutDatavue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function SiteWithoutDatavue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function SiteWithoutDatavue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }



/* harmony default export */ var SiteWithoutDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    ctaContent: String
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    VueEntryContainer: external_CoreHome_["VueEntryContainer"]
  },
  data: function data() {
    return {
      loading: true,
      updateCheckInterval: 1000,
      currentInterval: 1000,
      maxInterval: 30000,
      showMethodDetails: null,
      recommendedMethod: null,
      trackingMethods: []
    };
  },
  created: function created() {
    var _this = this;

    var params = {
      module: 'SitesManager',
      action: 'getTrackingMethodsForSite'
    };
    external_CoreHome_["AjaxHelper"].fetch(params).then(function (response) {
      _this.trackingMethods = response.trackingMethods;
      _this.recommendedMethod = response.recommendedMethod;
      _this.loading = false; // set up watch once all data was fetched, to ensure tracking methods are available

      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
        return external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab;
      }, function (activeTab) {
        _this.showMethodDetails = _this.findTrackingMethod(activeTab);
      });

      if (external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab) {
        _this.showMethodDetails = _this.findTrackingMethod(external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab);
      }

      _this.checkIfSiteHasData();
    });
  },
  methods: {
    findTrackingMethod: function findTrackingMethod(methodId) {
      if (this.recommendedMethod && methodId && this.recommendedMethod.id.toLowerCase() === methodId.toLowerCase()) {
        return this.recommendedMethod;
      }

      var trackingMethod = null;
      Object.entries(this.trackingMethods).forEach(function (_ref) {
        var _ref2 = SiteWithoutDatavue_type_script_lang_ts_slicedToArray(_ref, 2),
            method = _ref2[1];

        if (methodId && method.id.toLowerCase() === methodId.toLowerCase()) {
          trackingMethod = method;
        }
      });
      return trackingMethod;
    },
    showMethod: function showMethod(methodId) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        activeTab: methodId.toLowerCase()
      }));
    },
    showOverview: function showOverview() {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        activeTab: null
      }));
    },
    checkIfSiteHasData: function checkIfSiteHasData() {
      var _this2 = this;

      var params = {
        method: 'Live.getMostRecentVisitsDateTime',
        date: 'today',
        period: 'day',
        idSite: external_CoreHome_["Matomo"].idSite
      };
      var options = {
        // don't show error messages returned from API as notification
        createErrorNotification: false
      };
      external_CoreHome_["AjaxHelper"].fetch(params, options).then(function (response) {
        if (response && response.value !== '') {
          window.broadcast.propagateNewPage('date=today');
          return;
        }

        window.setTimeout(_this2.checkIfSiteHasData, _this2.currentInterval);
        _this2.currentInterval = Math.min(_this2.currentInterval + _this2.updateCheckInterval, _this2.maxInterval);
      }).catch(function () {// ignore errors to no distract user with an error message
      });
    }
  },
  computed: {
    ignoreSitesWithoutDataLink: function ignoreSitesWithoutDataLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'SitesManager',
        action: 'ignoreNoDataMessage'
      })));
    },
    headline: function headline() {
      if (this.showMethodDetails && this.showMethodDetails.name) {
        if (this.showMethodDetails.type === 99) {
          return this.showMethodDetails.name;
        }

        return Object(external_CoreHome_["translate"])('SitesManager_SiteWithoutDataInstallWithX', this.showMethodDetails.name);
      }

      return Object(external_CoreHome_["translate"])('SitesManager_SiteWithoutDataChooseTrackingMethod');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue



SiteWithoutDatavue_type_script_lang_ts.render = SiteWithoutDatavue_type_template_id_3425106e_render

/* harmony default export */ var SiteWithoutData = (SiteWithoutDatavue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/index.ts
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
//# sourceMappingURL=SitesManager.umd.js.map