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
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const {
  $
} = window;
class SiteTypesStore_SiteTypesStore {
  constructor() {
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      typesById: {}
    }));
    _defineProperty(this, "typesById", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state).typesById));
    _defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.state).isLoading));
    _defineProperty(this, "types", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object.values(this.typesById.value)));
    _defineProperty(this, "response", void 0);
  }
  init() {
    return this.fetchAvailableTypes();
  }
  fetchAvailableTypes() {
    if (this.response) {
      return Promise.resolve(this.response);
    }
    this.state.isLoading = true;
    this.response = external_CoreHome_["AjaxHelper"].fetch({
      method: 'API.getAvailableMeasurableTypes',
      filter_limit: '-1'
    }).then(types => {
      types.forEach(type => {
        this.state.typesById[type.id] = type;
      });
      return this.types.value;
    }).finally(() => {
      this.state.isLoading = false;
    });
    return this.response;
  }
  getEditSiteIdParameter() {
    // parse query directly because #/editsiteid=N was supported alongside #/?editsiteid=N
    const m = external_CoreHome_["MatomoUrl"].hashQuery.value.match(/editsiteid=([0-9]+)/);
    if (!m) {
      return undefined;
    }
    const isShowAddSite = external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite === '1' || external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite === 'true';
    const editsiteid = m[1];
    if (editsiteid && $.isNumeric(editsiteid) && !isShowAddSite) {
      return editsiteid;
    }
    return undefined;
  }
  removeEditSiteIdParameterFromHash() {
    const params = _extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value);
    delete params.editsiteid;
    external_CoreHome_["MatomoUrl"].updateHash(params);
  }
}
/* harmony default export */ var src_SiteTypesStore_SiteTypesStore = (new SiteTypesStore_SiteTypesStore());
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/CurrencyStore/CurrencyStore.ts
function CurrencyStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class CurrencyStore_CurrencyStore {
  constructor() {
    CurrencyStore_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      currencies: {}
    }));
    CurrencyStore_defineProperty(this, "currencies", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState).currencies));
    CurrencyStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState).isLoading));
    CurrencyStore_defineProperty(this, "initializePromise", null);
  }
  init() {
    if (!this.initializePromise) {
      this.initializePromise = this.fetchCurrencies();
    }
    return this.initializePromise;
  }
  fetchCurrencies() {
    this.privateState.isLoading = true;
    return external_CoreHome_["AjaxHelper"].fetch({
      method: 'SitesManager.getCurrencyList'
    }).then(currencies => {
      this.privateState.currencies = currencies;
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
}
/* harmony default export */ var src_CurrencyStore_CurrencyStore = (new CurrencyStore_CurrencyStore());
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/TimezoneStore/TimezoneStore.ts
function TimezoneStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class TimezoneStore_TimezoneStore {
  constructor() {
    TimezoneStore_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      timezones: [],
      timezoneSupportEnabled: false
    }));
    TimezoneStore_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    TimezoneStore_defineProperty(this, "timezones", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.timezones));
    TimezoneStore_defineProperty(this, "timezoneSupportEnabled", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.timezoneSupportEnabled));
    TimezoneStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.isLoading));
    TimezoneStore_defineProperty(this, "initializePromise", null);
  }
  init() {
    if (!this.initializePromise) {
      this.privateState.isLoading = true;
      this.initializePromise = Promise.all([this.checkTimezoneSupportEnabled(), this.fetchTimezones()]).finally(() => {
        this.privateState.isLoading = false;
      });
    }
    return this.initializePromise;
  }
  fetchTimezones() {
    return external_CoreHome_["AjaxHelper"].fetch({
      method: 'SitesManager.getTimezonesList'
    }).then(grouped => {
      const flattened = [];
      Object.entries(grouped).forEach(([group, timezonesGroup]) => {
        Object.entries(timezonesGroup).forEach(([label, code]) => {
          flattened.push({
            group,
            label,
            code
          });
        });
      });
      this.privateState.timezones = flattened;
    });
  }
  checkTimezoneSupportEnabled() {
    return external_CoreHome_["AjaxHelper"].fetch({
      method: 'SitesManager.isTimezoneSupportEnabled'
    }).then(response => {
      this.privateState.timezoneSupportEnabled = response.value;
    });
  }
}
/* harmony default export */ var src_TimezoneStore_TimezoneStore = (new TimezoneStore_TimezoneStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=template&id=35a6a1b6

const _hoisted_1 = {
  class: "SitesManager",
  ref: "root"
};
const _hoisted_2 = {
  class: "sites-manager-header"
};
const _hoisted_3 = ["innerHTML"];
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_5 = ["innerHTML"];
const _hoisted_6 = {
  class: "loadingPiwik"
};
const _hoisted_7 = ["alt"];
const _hoisted_8 = {
  class: "ui-confirm add-site-dialog"
};
const _hoisted_9 = {
  class: "center"
};
const _hoisted_10 = ["title", "onClick"];
const _hoisted_11 = {
  class: "ui-button-text"
};
const _hoisted_12 = {
  class: "sitesManagerList"
};
const _hoisted_13 = {
  key: 0
};
const _hoisted_14 = {
  class: "bottomButtonBar"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_ButtonBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ButtonBar");
  const _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");
  const _component_SiteFields = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteFields");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/manage-websites/'),
    "feature-name": _ctx.translate('SitesManager_WebsitesManagement')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.headlineText), 1)]),
    _: 1
  }, 8, ["help-url", "feature-name"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.availableTypes.length]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_MainDescription')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.mainDescription)
  }, null, 8, _hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.superUserAccessMessage)
  }, null, 8, _hoisted_5)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess]])])])), [[_directive_content_intro]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
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
    "onUpdate:searchTerm": _cache[0] || (_cache[0] = $event => _ctx.searchTerm = $event),
    onAdd: _cache[1] || (_cache[1] = $event => _ctx.addNewEntity()),
    onSearch: _cache[2] || (_cache[2] = $event => _ctx.searchSites($event)),
    onPrev: _cache[3] || (_cache[3] = $event => _ctx.previousPage()),
    onNext: _cache[4] || (_cache[4] = $event => _ctx.nextPage())
  }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showAddSiteDialog,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.showAddSiteDialog = $event)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChooseMeasurableTypeHeadline')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableTypes, type => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        type: "button",
        key: type.id,
        title: type.description,
        class: "modal-close btn",
        onClick: $event => {
          _ctx.addSite(type.id);
        },
        "aria-disabled": "false"
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(type.name), 1)], 8, _hoisted_10);
    }), 128))])])])])]),
    _: 1
  }, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [_ctx.activeSearchTerm && 0 === _ctx.sites.length && !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_NotFound')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.activeSearchTerm), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, (site, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: site.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteFields, {
      site: site,
      "timezone-support-enabled": _ctx.timezoneSupportEnabled,
      "utc-time": _ctx.utcTime,
      "global-settings": _ctx.globalSettings,
      onEditSite: _cache[6] || (_cache[6] = $event => this.isSiteBeingEdited = true),
      onCancelEditSite: _cache[7] || (_cache[7] = $event => _ctx.afterCancelEdit($event)),
      onDelete: _cache[8] || (_cache[8] = $event => _ctx.afterDelete($event)),
      onSave: $event => _ctx.afterSave($event.site, $event.settingValues, index, $event.isNew)
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
    "onUpdate:searchTerm": _cache[9] || (_cache[9] = $event => _ctx.searchTerm = $event),
    onAdd: _cache[10] || (_cache[10] = $event => _ctx.addNewEntity()),
    onSearch: _cache[11] || (_cache[11] = $event => _ctx.searchSites($event)),
    onPrev: _cache[12] || (_cache[12] = $event => _ctx.previousPage()),
    onNext: _cache[13] || (_cache[13] = $event => _ctx.nextPage())
  }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])])], 512);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=template&id=35a6a1b6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=template&id=d473a694

const ButtonBarvue_type_template_id_d473a694_hoisted_1 = {
  class: "sitesButtonBar clearfix"
};
const ButtonBarvue_type_template_id_d473a694_hoisted_2 = {
  class: "search"
};
const ButtonBarvue_type_template_id_d473a694_hoisted_3 = ["value", "placeholder", "disabled"];
const ButtonBarvue_type_template_id_d473a694_hoisted_4 = ["title"];
const ButtonBarvue_type_template_id_d473a694_hoisted_5 = {
  class: "paging"
};
const ButtonBarvue_type_template_id_d473a694_hoisted_6 = ["disabled"];
const ButtonBarvue_type_template_id_d473a694_hoisted_7 = {
  style: {
    "cursor": "pointer"
  }
};
const ButtonBarvue_type_template_id_d473a694_hoisted_8 = {
  class: "counter"
};
const ButtonBarvue_type_template_id_d473a694_hoisted_9 = ["disabled"];
const ButtonBarvue_type_template_id_d473a694_hoisted_10 = {
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
    onClick: _cache[0] || (_cache[0] = $event => _ctx.addNewEntity()),
    tabindex: "1"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.availableTypes.length > 1 ? _ctx.translate('SitesManager_AddMeasurable') : _ctx.translate('SitesManager_AddSite')), 3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess && _ctx.availableTypes]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ButtonBarvue_type_template_id_d473a694_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    value: _ctx.searchTerm,
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.onKeydown($event)),
    placeholder: _ctx.translate('Actions_SubmenuSitesearch'),
    type: "text",
    disabled: _ctx.siteIsBeingEdited
  }, null, 40, ButtonBarvue_type_template_id_d473a694_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[2] || (_cache[2] = $event => _ctx.searchSite()),
    title: _ctx.translate('General_ClickToSearch'),
    class: "search_ico icon-search"
  }, null, 8, ButtonBarvue_type_template_id_d473a694_hoisted_4)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext || _ctx.isSearching]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ButtonBarvue_type_template_id_d473a694_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn prev",
    disabled: _ctx.hasPrev && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? undefined : true,
    onClick: _cache[3] || (_cache[3] = $event => _ctx.previousPage())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_7, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 8, ButtonBarvue_type_template_id_d473a694_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.paginationText), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn next",
    disabled: _ctx.hasNext && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? undefined : true,
    onClick: _cache[4] || (_cache[4] = $event => _ctx.nextPage())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ButtonBarvue_type_template_id_d473a694_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 8, ButtonBarvue_type_template_id_d473a694_hoisted_9)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasPrev || _ctx.hasNext]])]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=template&id=d473a694

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=script&lang=ts



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
  created() {
    src_SiteTypesStore_SiteTypesStore.init();
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown, 50);
  },
  computed: {
    hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    paginationText() {
      let text;
      if (this.isSearching) {
        text = Object(external_CoreHome_["translate"])('General_PaginationWithoutTotal', `${this.offsetStart}`, `${this.offsetEnd}`);
      } else {
        text = Object(external_CoreHome_["translate"])('General_Pagination', `${this.offsetStart}`, `${this.offsetEnd}`, this.totalNumberOfSites === null ? '?' : `${this.totalNumberOfSites}`);
      }
      return ` ${text} `;
    }
  },
  methods: {
    addNewEntity() {
      this.$emit('add');
    },
    searchSite() {
      if (this.siteIsBeingEdited) {
        return;
      }
      this.$emit('search');
    },
    previousPage() {
      this.$emit('prev');
    },
    nextPage() {
      this.$emit('next');
    },
    onKeydown(event) {
      setTimeout(() => {
        if (event.key === 'Enter') {
          this.searchSiteOnEnter(event);
          return;
        }
        this.$emit('update:searchTerm', event.target.value);
      });
    },
    searchSiteOnEnter(event) {
      event.preventDefault();
      this.searchSite();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SitesManagement/ButtonBar.vue



ButtonBarvue_type_script_lang_ts.render = ButtonBarvue_type_template_id_d473a694_render

/* harmony default export */ var ButtonBar = (ButtonBarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=template&id=b900a09c

const SiteFieldsvue_type_template_id_b900a09c_hoisted_1 = ["idsite", "type"];
const SiteFieldsvue_type_template_id_b900a09c_hoisted_2 = {
  class: "card-content"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_3 = {
  key: 0,
  class: "row"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_4 = {
  class: "col m3"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_5 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_6 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_7 = ["target", "title", "href"];
const SiteFieldsvue_type_template_id_b900a09c_hoisted_8 = {
  class: "col m4"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_9 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_10 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_11 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_12 = {
  class: "title"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_13 = {
  class: "col m4"
};
const SiteFieldsvue_type_template_id_b900a09c_hoisted_14 = {
  class: "title"
};
const _hoisted_15 = ["href"];
const _hoisted_16 = {
  key: 0
};
const _hoisted_17 = {
  class: "title"
};
const _hoisted_18 = {
  key: 1
};
const _hoisted_19 = {
  class: "title"
};
const _hoisted_20 = {
  key: 2
};
const _hoisted_21 = {
  class: "title"
};
const _hoisted_22 = {
  class: "col m1 text-right"
};
const _hoisted_23 = ["title"];
const _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);
const _hoisted_25 = [_hoisted_24];
const _hoisted_26 = ["title"];
const _hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);
const _hoisted_28 = [_hoisted_27];
const _hoisted_29 = {
  key: 1
};
const _hoisted_30 = {
  class: "form-group row"
};
const _hoisted_31 = {
  class: "col s12 m6 input-field"
};
const _hoisted_32 = ["placeholder"];
const _hoisted_33 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "col s12 m6"
}, null, -1);
const _hoisted_34 = {
  id: "timezoneHelpText",
  class: "inline-help-node"
};
const _hoisted_35 = {
  key: 0
};
const _hoisted_36 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_37 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_38 = {
  class: "editingSiteFooter"
};
const _hoisted_39 = ["disabled", "value"];
const _hoisted_40 = ["disabled"];
const _hoisted_41 = ["innerHTML"];
function SiteFieldsvue_type_template_id_b900a09c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$theSite$excluded, _ctx$theSite$excluded2, _ctx$theSite$excluded3;
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_GroupedSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSettings");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
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
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ShowTrackingTag')), 9, SiteFieldsvue_type_template_id_b900a09c_hoisted_7)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.idsite && _ctx.howToSetupUrl]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Timezone')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.timezone_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Currency')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.currency_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Ecommerce')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.ecommerce === 1 || _ctx.theSite.ecommerce === '1']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuSitesearch')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.sitesearch === 1 || _ctx.theSite.sitesearch === '1']])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteFieldsvue_type_template_id_b900a09c_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SiteFieldsvue_type_template_id_b900a09c_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_Urls')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": "), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.theSite.alias_urls, (url, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: url
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      rel: "noreferrer noopener",
      href: url
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(url) + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(index === _ctx.theSite.alias_urls.length - 1 ? '' : ', '), 9, _hoisted_15)]);
  }), 128))]), (_ctx$theSite$excluded = _ctx.theSite.excluded_ips) !== null && _ctx$theSite$excluded !== void 0 && _ctx$theSite$excluded.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedIps')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_ips.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$theSite$excluded2 = _ctx.theSite.excluded_parameters) !== null && _ctx$theSite$excluded2 !== void 0 && _ctx$theSite$excluded2.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedParameters')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_parameters.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$theSite$excluded3 = _ctx.theSite.excluded_user_agents) !== null && _ctx$theSite$excluded3 !== void 0 && _ctx$theSite$excluded3.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedUserAgents')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.theSite.excluded_user_agents.split(/\s*,\s*/g).join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "table-action",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.editSite()),
    title: _ctx.translate('General_Edit')
  }, _hoisted_25, 8, _hoisted_23)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "table-action",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.getMessagesToWarnOnSiteRemoval()),
    title: _ctx.translate('General_Delete')
  }, _hoisted_28, 8, _hoisted_26), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.theSite.idsite]])])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.editMode ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.theSite.name = $event),
    maxlength: "90",
    placeholder: _ctx.translate('General_Name')
  }, null, 8, _hoisted_32), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.theSite.name]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1)]), _hoisted_33]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading
  }, null, 8, ["loading"]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.measurableSettings, settingsPerPlugin => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: settingsPerPlugin.pluginName
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSettings, {
      "group-name": settingsPerPlugin.pluginName,
      settings: settingsPerPlugin.settings,
      "all-setting-values": _ctx.settingValues,
      onChange: $event => _ctx.settingValues[`${settingsPerPlugin.pluginName}.${$event.name}`] = $event.value
    }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"])]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "currency",
    modelValue: _ctx.theSite.currency,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.theSite.currency = $event),
    title: _ctx.translate('SitesManager_Currency'),
    "inline-help": _ctx.translate('SitesManager_CurrencySymbolWillBeUsedForGoals'),
    options: _ctx.currencies
  }, null, 8, ["modelValue", "title", "inline-help", "options"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "timezone",
    modelValue: _ctx.theSite.timezone,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.theSite.timezone = $event),
    title: _ctx.translate('SitesManager_Timezone'),
    "inline-help": '#timezoneHelpText',
    options: _ctx.timezones
  }, null, 8, ["modelValue", "title", "options"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.timezoneSupportEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AdvancedTimezoneSupportNotFound')) + " ", 1), _hoisted_36])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.utcTimeIs) + " ", 1), _hoisted_37, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    disabled: _ctx.isSaving,
    type: "submit",
    class: "btn",
    value: _ctx.translate('General_Save'),
    onClick: _cache[5] || (_cache[5] = $event => _ctx.saveSite())
  }, null, 8, _hoisted_39), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn btn-link",
    disabled: _ctx.isSaving,
    onClick: _cache[6] || (_cache[6] = $event => _ctx.cancelEditSite(_ctx.site))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel', '', '')), 9, _hoisted_40)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showRemoveDialog,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.showRemoveDialog = $event),
    onConfirmed: _ctx.deleteSite
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.removeDialogTitle), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_DeleteSiteExplanation')), 1), _ctx.deleteSiteExplanation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.deleteSiteExplanation)
    }, null, 8, _hoisted_41)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])], 10, SiteFieldsvue_type_template_id_b900a09c_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=template&id=b900a09c

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=script&lang=ts
function SiteFieldsvue_type_script_lang_ts_extends() { SiteFieldsvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SiteFieldsvue_type_script_lang_ts_extends.apply(this, arguments); }






const timezoneOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => src_TimezoneStore_TimezoneStore.timezones.value.map(({
  group,
  label,
  code
}) => ({
  group,
  key: label,
  value: code
})));
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
  data() {
    return {
      isLoading: false,
      isSaving: false,
      editMode: false,
      theSite: SiteFieldsvue_type_script_lang_ts_extends({}, this.site),
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
  created() {
    src_CurrencyStore_CurrencyStore.init();
    src_TimezoneStore_TimezoneStore.init();
    src_SiteTypesStore_SiteTypesStore.init();
    this.onSiteChanged();
  },
  watch: {
    site() {
      this.onSiteChanged();
    },
    measurableSettings(settings) {
      if (!settings.length) {
        return;
      }
      const settingValues = {};
      settings.forEach(settingsForPlugin => {
        settingsForPlugin.settings.forEach(setting => {
          settingValues[`${settingsForPlugin.pluginName}.${setting.name}`] = setting.value;
        });
      });
      this.settingValues = settingValues;
    }
  },
  methods: {
    onSiteChanged() {
      const site = this.site;
      this.theSite = SiteFieldsvue_type_script_lang_ts_extends({}, site);
      const isNew = isSiteNew(site);
      if (isNew) {
        const globalSettings = this.globalSettings;
        this.theSite.timezone = globalSettings.defaultTimezone;
        this.theSite.currency = globalSettings.defaultCurrency;
      }
      const forcedEditSiteId = src_SiteTypesStore_SiteTypesStore.getEditSiteIdParameter();
      if (isNew || forcedEditSiteId && `${site.idsite}` === forcedEditSiteId) {
        this.editSite();
      }
    },
    editSite() {
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
      }).then(settings => {
        this.measurableSettings = settings;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    saveSite() {
      if (this.isSaving) {
        return; // saving already in progress
      }
      this.isSaving = true;
      const values = {
        siteName: this.theSite.name,
        timezone: this.theSite.timezone,
        currency: this.theSite.currency,
        type: this.theSite.type,
        settingValues: {}
      };
      const isNew = isSiteNew(this.theSite);
      let apiMethod = 'SitesManager.addSite';
      if (!isNew) {
        apiMethod = 'SitesManager.updateSite';
        values.idSite = this.theSite.idsite;
      }
      // process measurable settings
      Object.entries(this.settingValues).forEach(([fullName, fieldValue]) => {
        const [pluginName, name] = fullName.split('.');
        const settingValues = values.settingValues;
        if (!settingValues[pluginName]) {
          settingValues[pluginName] = [];
        }
        let value = fieldValue;
        if (fieldValue === false) {
          value = '0';
        } else if (fieldValue === true) {
          value = '1';
        } else if (Array.isArray(fieldValue)) {
          value = fieldValue.filter(x => !!x);
        }
        settingValues[pluginName].push({
          name,
          value
        });
      });
      external_CoreHome_["AjaxHelper"].post({
        method: apiMethod
      }, values).then(response => {
        this.editMode = false;
        if (!this.theSite.idsite && response && response.value) {
          this.theSite.idsite = `${response.value}`;
        }
        const timezoneInfo = src_TimezoneStore_TimezoneStore.timezones.value.find(t => t.code === this.theSite.timezone);
        this.theSite.timezone_name = (timezoneInfo === null || timezoneInfo === void 0 ? void 0 : timezoneInfo.label) || this.theSite.timezone;
        if (this.theSite.currency) {
          this.theSite.currency_name = src_CurrencyStore_CurrencyStore.currencies.value[this.theSite.currency];
        }
        const notificationId = external_CoreHome_["NotificationsStore"].show({
          message: isNew ? Object(external_CoreHome_["translate"])('SitesManager_WebsiteCreated') : Object(external_CoreHome_["translate"])('SitesManager_WebsiteUpdated'),
          context: 'success',
          id: 'websitecreated',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationId);
        src_SiteTypesStore_SiteTypesStore.removeEditSiteIdParameterFromHash();
        this.$emit('save', {
          site: this.theSite,
          settingValues: values.settingValues,
          isNew
        });
      }).finally(() => {
        this.isSaving = false;
      });
    },
    cancelEditSite(site) {
      this.editMode = false;
      src_SiteTypesStore_SiteTypesStore.removeEditSiteIdParameterFromHash();
      this.$emit('cancelEditSite', {
        site,
        element: this.$refs.root
      });
    },
    deleteSite(password) {
      external_CoreHome_["AjaxHelper"].post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.deleteSite'
      }, {
        passwordConfirmation: password
      }).then(() => {
        this.$emit('delete', this.theSite);
      });
    },
    getMessagesToWarnOnSiteRemoval() {
      external_CoreHome_["AjaxHelper"].post({
        idSite: this.theSite.idsite,
        module: 'API',
        format: 'json',
        method: 'SitesManager.getMessagesToWarnOnSiteRemoval'
      }).then(response => {
        this.deleteSiteExplanation = '';
        if (response.length) {
          this.deleteSiteExplanation += response.join('<br>');
        }
        this.showRemoveDialog = true;
      });
    }
  },
  computed: {
    availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    setupUrl() {
      const site = this.theSite;
      let suffix = '';
      let connector = '';
      if (this.isInternalSetupUrl) {
        suffix = external_CoreHome_["MatomoUrl"].stringify({
          idSite: site.idsite,
          period: external_CoreHome_["MatomoUrl"].parsed.value.period,
          date: external_CoreHome_["MatomoUrl"].parsed.value.date,
          updated: 'false'
        });
        connector = this.howToSetupUrl.indexOf('?') === -1 ? '?' : '&';
      }
      return `${this.howToSetupUrl}${connector}${suffix}`;
    },
    utcTimeIs() {
      const utcTime = this.utcTime;
      const formatTimePart = n => n.toString().padStart(2, '0');
      const hours = formatTimePart(utcTime.getHours());
      const minutes = formatTimePart(utcTime.getMinutes());
      const seconds = formatTimePart(utcTime.getSeconds());
      const date = `${Object(external_CoreHome_["format"])(this.utcTime)} ${hours}:${minutes}:${seconds}`;
      return Object(external_CoreHome_["translate"])('SitesManager_UTCTimeIs', date);
    },
    timezones() {
      return timezoneOptions.value;
    },
    currencies() {
      return src_CurrencyStore_CurrencyStore.currencies.value;
    },
    currentType() {
      const site = this.site;
      const type = src_SiteTypesStore_SiteTypesStore.typesById.value[site.type];
      if (!type) {
        return {
          name: site.type
        };
      }
      return type;
    },
    howToSetupUrl() {
      const type = this.currentType;
      if (!type) {
        return undefined;
      }
      return type.howToSetupUrl;
    },
    isInternalSetupUrl() {
      const {
        howToSetupUrl
      } = this;
      if (!howToSetupUrl) {
        return false;
      }
      return `${howToSetupUrl}`.substring(0, 1) === '?';
    },
    removeDialogTitle() {
      return Object(external_CoreHome_["translate"])('SitesManager_DeleteConfirm', `"${this.theSite.name}" (idSite = ${this.theSite.idsite})`);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteFields/SiteFields.vue



SiteFieldsvue_type_script_lang_ts.render = SiteFieldsvue_type_template_id_b900a09c_render

/* harmony default export */ var SiteFields = (SiteFieldsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/GlobalSettingsStore/GlobalSettingsStore.ts
function GlobalSettingsStore_extends() { GlobalSettingsStore_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return GlobalSettingsStore_extends.apply(this, arguments); }
function GlobalSettingsStore_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class GlobalSettingsStore_GlobalSettingsStore {
  constructor() {
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
        searchCategoryParametersGlobal: ''
      }
    }));
    GlobalSettingsStore_defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState).isLoading));
    GlobalSettingsStore_defineProperty(this, "globalSettings", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState).globalSettings));
  }
  init() {
    return this.fetchGlobalSettings();
  }
  saveGlobalSettings(settings) {
    this.privateState.isLoading = true;
    return external_CoreHome_["AjaxHelper"].post({
      module: 'SitesManager',
      format: 'json',
      action: 'setGlobalSettings'
    }, settings, {
      withTokenInUrl: true
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
  fetchGlobalSettings() {
    this.privateState.isLoading = true;
    external_CoreHome_["AjaxHelper"].fetch({
      module: 'SitesManager',
      action: 'getGlobalSettings'
    }).then(response => {
      this.privateState.globalSettings = GlobalSettingsStore_extends(GlobalSettingsStore_extends({}, response), {}, {
        // the API can return false for these
        excludedIpsGlobal: response.excludedIpsGlobal || '',
        excludedQueryParametersGlobal: response.excludedQueryParametersGlobal || '',
        excludedUserAgentsGlobal: response.excludedUserAgentsGlobal || '',
        excludedReferrersGlobal: response.excludedReferrersGlobal || '',
        searchKeywordParametersGlobal: response.searchKeywordParametersGlobal || '',
        searchCategoryParametersGlobal: response.searchCategoryParametersGlobal || ''
      });
    }).finally(() => {
      this.privateState.isLoading = false;
    });
  }
}
/* harmony default export */ var src_GlobalSettingsStore_GlobalSettingsStore = (new GlobalSettingsStore_GlobalSettingsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SitesManagement/SitesManagement.vue?vue&type=script&lang=ts
function SitesManagementvue_type_script_lang_ts_extends() { SitesManagementvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SitesManagementvue_type_script_lang_ts_extends.apply(this, arguments); }







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
  data() {
    const currentDate = new Date();
    const utcTime = new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), currentDate.getUTCDate(), currentDate.getUTCHours(), currentDate.getUTCMinutes(), currentDate.getUTCSeconds());
    return {
      pageSize: 10,
      currentPage: 0,
      showAddSiteDialog: false,
      searchTerm: '',
      activeSearchTerm: '',
      fetchedSites: [],
      isLoadingInitialEntities: false,
      utcTime,
      totalNumberOfSites: null,
      isSiteBeingEdited: false,
      fetchLimitedSitesAbortController: null
    };
  },
  created() {
    src_TimezoneStore_TimezoneStore.init();
    src_SiteTypesStore_SiteTypesStore.init();
    src_GlobalSettingsStore_GlobalSettingsStore.init();
    this.isLoadingInitialEntities = true;
    Promise.all([src_SiteTypesStore_SiteTypesStore.fetchAvailableTypes(), this.fetchLimitedSitesWithAdminAccess(), this.getTotalNumberOfSites()]).then(() => {
      this.triggerAddSiteIfRequested();
    }).finally(() => {
      this.isLoadingInitialEntities = false;
    });
    // if hash is #globalSettings, redirect to globalSettings action (we don't do it on
    // page load so the back button still works)
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashQuery.value, () => {
      this.checkGlobalSettingsHash();
    });
  },
  computed: {
    sites() {
      const emptyIdSiteRows = this.fetchedSites.filter(s => !s.idsite).length;
      return this.fetchedSites.slice(0, this.pageSize + emptyIdSiteRows);
    },
    isLoading() {
      return !!this.fetchLimitedSitesAbortController || this.isLoadingInitialEntities || this.totalNumberOfSites === null || src_SiteTypesStore_SiteTypesStore.isLoading.value || src_TimezoneStore_TimezoneStore.isLoading.value || src_GlobalSettingsStore_GlobalSettingsStore.isLoading.value;
    },
    availableTypes() {
      return src_SiteTypesStore_SiteTypesStore.types.value;
    },
    timezoneSupportEnabled() {
      return src_TimezoneStore_TimezoneStore.timezoneSupportEnabled.value;
    },
    globalSettings() {
      return src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value;
    },
    headlineText() {
      return Object(external_CoreHome_["translate"])('SitesManager_XManagement', this.availableTypes.length > 1 ? Object(external_CoreHome_["translate"])('General_Measurables') : Object(external_CoreHome_["translate"])('SitesManager_Sites'));
    },
    mainDescription() {
      return Object(external_CoreHome_["translate"])('SitesManager_YouCurrentlyHaveAccessToNWebsites', `<strong>${this.totalNumberOfSites}</strong>`);
    },
    hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    superUserAccessMessage() {
      return Object(external_CoreHome_["translate"])('SitesManager_SuperUserAccessCan', '<a href=\'#globalSettings\'>', '</a>');
    },
    hasPrev() {
      return this.currentPage >= 1;
    },
    hasNext() {
      return this.fetchedSites.filter(s => !!s.idsite).length >= this.pageSize + 1;
    },
    offsetStart() {
      return this.currentPage * this.pageSize + 1;
    },
    offsetEnd() {
      return this.offsetStart + this.sites.filter(s => !!s.idsite).length - 1;
    }
  },
  methods: {
    checkGlobalSettingsHash() {
      const newHash = external_CoreHome_["MatomoUrl"].hashQuery.value;
      if (external_CoreHome_["Matomo"].hasSuperUserAccess && (newHash === 'globalSettings' || newHash === '/globalSettings')) {
        external_CoreHome_["MatomoUrl"].updateLocation(SitesManagementvue_type_script_lang_ts_extends(SitesManagementvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
          action: 'globalSettings'
        }));
      }
    },
    addNewEntity() {
      if (this.availableTypes.length > 1) {
        this.showAddSiteDialog = true;
      } else if (this.availableTypes.length === 1) {
        this.addSite(this.availableTypes[0].id);
      }
    },
    addSite(typeId) {
      let type = typeId;
      const parameters = {
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
        type
      });
      this.isSiteBeingEdited = true;
    },
    afterCancelEdit({
      site,
      element
    }) {
      this.isSiteBeingEdited = false;
      if (!site.idsite) {
        this.fetchedSites = this.fetchedSites.filter(s => !!s.idsite);
        return;
      }
      element.scrollIntoView();
    },
    fetchLimitedSitesWithAdminAccess(searchTerm = '') {
      if (this.fetchLimitedSitesAbortController) {
        this.fetchLimitedSitesAbortController.abort();
      }
      this.fetchLimitedSitesAbortController = new AbortController();
      const limit = this.pageSize + 1;
      const offset = this.currentPage * this.pageSize;
      const params = {
        method: 'SitesManager.getSitesWithAdminAccess',
        fetchAliasUrls: 1,
        limit: limit + offset,
        filter_offset: offset,
        filter_limit: limit
      };
      if (searchTerm) {
        params.pattern = searchTerm;
      }
      return external_CoreHome_["AjaxHelper"].fetch(params).then(sites => {
        this.fetchedSites = sites || [];
      }).then(sites => {
        this.activeSearchTerm = searchTerm;
        return sites;
      }).finally(() => {
        this.fetchLimitedSitesAbortController = null;
      });
    },
    getTotalNumberOfSites() {
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getSitesIdWithAdminAccess',
        filter_limit: '-1'
      }).then(sites => {
        this.totalNumberOfSites = sites.length;
      });
    },
    triggerAddSiteIfRequested() {
      const forcedEditSiteId = src_SiteTypesStore_SiteTypesStore.getEditSiteIdParameter();
      const showaddsite = external_CoreHome_["MatomoUrl"].urlParsed.value.showaddsite;
      if (showaddsite === '1') {
        this.addNewEntity();
      } else if (forcedEditSiteId) {
        this.searchTerm = forcedEditSiteId;
        this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
      }
    },
    previousPage() {
      this.currentPage = Math.max(0, this.currentPage - 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    nextPage() {
      this.currentPage = Math.max(0, this.currentPage + 1);
      this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
    },
    searchSites() {
      this.currentPage = 0;
      this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
    },
    afterDelete(site) {
      let redirectParams = {
        showaddsite: 0
      };
      // if the current idSite in the URL is the site we're deleting, then we have to make to
      // change it. otherwise, if a user goes to another page, the invalid idSite may cause
      // a fatal error.
      if (external_CoreHome_["MatomoUrl"].urlParsed.value.idSite === `${site.idsite}`) {
        const otherSite = this.sites.find(s => s.idsite !== site.idsite);
        if (otherSite) {
          redirectParams = SitesManagementvue_type_script_lang_ts_extends(SitesManagementvue_type_script_lang_ts_extends({}, redirectParams), {}, {
            idSite: otherSite.idsite
          });
        }
      }
      external_CoreHome_["Matomo"].helper.redirect(redirectParams);
    },
    afterSave(site, settingValues, index, isNew) {
      const texttareaArrayParams = ['excluded_ips', 'excluded_parameters', 'excluded_user_agents', 'sitesearch_keyword_parameters', 'sitesearch_category_parameters'];
      const newSite = SitesManagementvue_type_script_lang_ts_extends({}, site);
      Object.values(settingValues).forEach(settings => {
        settings.forEach(setting => {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=template&id=c6989cd8

const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_1 = {
  class: "SitesManager"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "globalSettings",
  id: "globalSettings"
}, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_3 = {
  id: "excludedIpsGlobalHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_6 = ["innerHTML"];
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_7 = {
  id: "excludedQueryParametersGlobalHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_10 = {
  id: "excludedUserAgentsGlobalHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_13 = {
  id: "excludedReferrersGlobalHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_19 = {
  id: "timezoneHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_20 = {
  key: 0
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_23 = {
  id: "keepURLFragmentsHelp",
  class: "inline-help-node"
};
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_24 = ["innerHTML"];
const ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_25 = {
  class: "alert alert-info"
};
function ManageGlobalSettingsvue_type_template_id_c6989cd8_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('SitesManager_GlobalWebsitesSettings')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_HelpExcludedIpAddresses', '1.2.3.4/24', '1.2.3.*', '1.2.*.*')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_4, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.yourCurrentIpAddressIs)
    }, null, 8, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_6)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ListOfQueryParametersToExclude', '/^sess.*|.*[dD]ate$/')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_8, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters', 'phpsessid, sessionid, ...')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp1')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_11, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalListExcludedUserAgents_Desc')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp2')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_GlobalExcludedUserAgentHelp3', '/bot|spider|crawl|scanner/i')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelp')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_14, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpDetails')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpExamples', 'www.example.org', 'http://example.org/mypath', 'https://www.example.org/?param=1', 'https://sub.example.org/')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_17, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ExcludedReferrersHelpSubDomains', '.sub.example.org', 'http://sub.example.org/mypath', 'https://new.sub.example.org/')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.timezoneSupportEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AdvancedTimezoneSupportNotFound')) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_21])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_UTCTimeIs', _ctx.utcTimeDate)) + " ", 1), ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      innerHTML: _ctx.$sanitize(_ctx.keepUrlFragmentHelp)
    }, null, 8, ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_KeepURLFragmentsHelp2')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "excludedIpsGlobal",
      "var-type": "array",
      modelValue: _ctx.excludedIpsGlobal,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.excludedIpsGlobal = $event),
      title: _ctx.translate('SitesManager_ListOfIpsToBeExcludedOnAllWebsites'),
      introduction: _ctx.translate('SitesManager_GlobalListExcludedIps'),
      "inline-help": '#excludedIpsGlobalHelp',
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "excludedQueryParametersGlobal",
      "var-type": "array",
      modelValue: _ctx.excludedQueryParametersGlobal,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.excludedQueryParametersGlobal = $event),
      title: _ctx.translate('SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites'),
      introduction: _ctx.translate('SitesManager_GlobalListExcludedQueryParameters'),
      "inline-help": '#excludedQueryParametersGlobalHelp',
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "excludedUserAgentsGlobal",
      "var-type": "array",
      modelValue: _ctx.excludedUserAgentsGlobal,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.excludedUserAgentsGlobal = $event),
      title: _ctx.translate('SitesManager_GlobalListExcludedUserAgents_Desc'),
      introduction: _ctx.translate('SitesManager_GlobalListExcludedUserAgents'),
      "inline-help": '#excludedUserAgentsGlobalHelp',
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "excludedReferrersGlobal",
      "var-type": "array",
      modelValue: _ctx.excludedReferrersGlobal,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.excludedReferrersGlobal = $event),
      title: _ctx.translate('SitesManager_GlobalListExcludedReferrersDesc'),
      introduction: _ctx.translate('SitesManager_GlobalListExcludedReferrers'),
      "inline-help": '#excludedReferrersGlobalHelp',
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "keepURLFragmentsGlobal",
      modelValue: _ctx.keepURLFragmentsGlobal,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.keepURLFragmentsGlobal = $event),
      title: _ctx.translate('SitesManager_KeepURLFragmentsLong'),
      introduction: _ctx.translate('SitesManager_KeepURLFragments'),
      "inline-help": '#keepURLFragmentsHelp',
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "introduction", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_TrackingSiteSearch')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteSearchUse')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageGlobalSettingsvue_type_template_id_c6989cd8_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SearchParametersNote')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SearchParametersNote2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "searchKeywordParametersGlobal",
      "var-type": "array",
      modelValue: _ctx.searchKeywordParametersGlobal,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.searchKeywordParametersGlobal = $event),
      title: _ctx.translate('SitesManager_SearchKeywordLabel'),
      "inline-help": _ctx.translate('SitesManager_SearchKeywordParametersDesc'),
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "title", "inline-help", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "searchCategoryParametersGlobal",
      "var-type": "array",
      modelValue: _ctx.searchCategoryParametersGlobal,
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.searchCategoryParametersGlobal = $event),
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
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.defaultTimezone = $event)
    }, null, 8, ["options", "title", "introduction", "disabled", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "defaultCurrency",
      modelValue: _ctx.defaultCurrency,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.defaultCurrency = $event),
      options: _ctx.currencies,
      title: _ctx.translate('SitesManager_SelectDefaultCurrency'),
      introduction: _ctx.translate('SitesManager_DefaultCurrencyForNewWebsites'),
      "inline-help": _ctx.translate('SitesManager_CurrencySymbolWillBeUsedForGoals'),
      disabled: _ctx.isLoading
    }, null, 8, ["modelValue", "options", "title", "introduction", "inline-help", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.isSaving,
      onConfirm: _cache[9] || (_cache[9] = $event => _ctx.saveGlobalSettings())
    }, null, 8, ["saving"])]),
    _: 1
  }, 8, ["content-title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasSuperUserAccess]])]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=template&id=c6989cd8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=script&lang=ts






/* harmony default export */ var ManageGlobalSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    // TypeScript can't add state types if there are no properties (probably a bug in Vue)
    // so we add one dummy property to get the compile to work
    dummy: String
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data() {
    const currentDate = new Date();
    const utcTime = new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), currentDate.getUTCDate(), currentDate.getUTCHours(), currentDate.getUTCMinutes(), currentDate.getUTCSeconds());
    const settings = src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value;
    return {
      currentIpAddress: null,
      utcTime,
      keepURLFragmentsGlobal: settings.keepURLFragmentsGlobal,
      defaultTimezone: settings.defaultTimezone,
      defaultCurrency: settings.defaultCurrency,
      excludedIpsGlobal: (settings.excludedIpsGlobal || '').split(','),
      excludedQueryParametersGlobal: (settings.excludedQueryParametersGlobal || '').split(','),
      excludedUserAgentsGlobal: (settings.excludedUserAgentsGlobal || '').split(','),
      excludedReferrersGlobal: (settings.excludedReferrersGlobal || '').split(','),
      searchKeywordParametersGlobal: (settings.searchKeywordParametersGlobal || '').split(','),
      searchCategoryParametersGlobal: (settings.searchCategoryParametersGlobal || '').split(','),
      isSaving: false
    };
  },
  created() {
    src_CurrencyStore_CurrencyStore.init();
    src_TimezoneStore_TimezoneStore.init();
    src_GlobalSettingsStore_GlobalSettingsStore.init();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => src_GlobalSettingsStore_GlobalSettingsStore.globalSettings.value, settings => {
      this.keepURLFragmentsGlobal = settings.keepURLFragmentsGlobal;
      this.defaultTimezone = settings.defaultTimezone;
      this.defaultCurrency = settings.defaultCurrency;
      this.excludedIpsGlobal = (settings.excludedIpsGlobal || '').split(',');
      this.excludedQueryParametersGlobal = (settings.excludedQueryParametersGlobal || '').split(',');
      this.excludedUserAgentsGlobal = (settings.excludedUserAgentsGlobal || '').split(',');
      this.excludedReferrersGlobal = (settings.excludedReferrersGlobal || '').split(',');
      this.searchKeywordParametersGlobal = (settings.searchKeywordParametersGlobal || '').split(',');
      this.searchCategoryParametersGlobal = (settings.searchCategoryParametersGlobal || '').split(',');
    });
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'API.getIpFromHeader'
    }).then(response => {
      this.currentIpAddress = response.value;
    });
  },
  methods: {
    saveGlobalSettings() {
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
        searchCategoryParameters: this.searchCategoryParametersGlobal.join(',')
      }).then(() => {
        external_CoreHome_["Matomo"].helper.redirect({
          showaddsite: false
        });
      }).finally(() => {
        this.isSaving = false;
      });
    }
  },
  computed: {
    isLoading() {
      return src_GlobalSettingsStore_GlobalSettingsStore.isLoading.value || src_TimezoneStore_TimezoneStore.isLoading.value || src_CurrencyStore_CurrencyStore.isLoading.value;
    },
    timezones() {
      return src_TimezoneStore_TimezoneStore.timezones.value;
    },
    timezoneOptions() {
      return this.timezones.map(({
        group,
        label,
        code
      }) => ({
        group,
        key: label,
        value: code
      }));
    },
    currencies() {
      return src_CurrencyStore_CurrencyStore.currencies.value;
    },
    hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    yourCurrentIpAddressIs() {
      return Object(external_CoreHome_["translate"])('SitesManager_YourCurrentIpAddressIs', `<i>${this.currentIpAddress}</i>`);
    },
    timezoneSupportEnabled() {
      return src_TimezoneStore_TimezoneStore.timezoneSupportEnabled.value;
    },
    utcTimeDate() {
      const {
        utcTime
      } = this;
      const formatTimePart = n => n.toString().padStart(2, '0');
      const hours = formatTimePart(utcTime.getHours());
      const minutes = formatTimePart(utcTime.getMinutes());
      const seconds = formatTimePart(utcTime.getSeconds());
      return `${Object(external_CoreHome_["format"])(this.utcTime)} ${hours}:${minutes}:${seconds}`;
    },
    keepUrlFragmentHelp() {
      return Object(external_CoreHome_["translate"])('SitesManager_KeepURLFragmentsHelp', '<em>#</em>', '<em>example.org/index.html#first_section</em>', '<em>example.org/index.html</em>');
    },
    searchCategoryParamsInlineHelp() {
      const parts = [Object(external_CoreHome_["translate"])('Goals_Optional'), Object(external_CoreHome_["translate"])('SitesManager_SearchCategoryDesc'), Object(external_CoreHome_["translate"])('SitesManager_SearchCategoryParametersDesc')];
      return parts.join(' ');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/ManageGlobalSettings/ManageGlobalSettings.vue



ManageGlobalSettingsvue_type_script_lang_ts.render = ManageGlobalSettingsvue_type_template_id_c6989cd8_render

/* harmony default export */ var ManageGlobalSettings = (ManageGlobalSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=template&id=3425106e

const SiteWithoutDatavue_type_template_id_3425106e_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-left"
}, null, -1);
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_2 = {
  id: "start-tracking-data-header"
};
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_3 = {
  key: 0,
  class: "row",
  id: "start-tracking-detection"
};
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_4 = ["src", "alt"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_5 = ["href"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_6 = {
  class: "row",
  id: "start-tracking-method-list"
};
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search"
}, null, -1);
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_8 = ["href", "onClick"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_9 = ["src"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_10 = {
  class: "list-entry-text"
};
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_11 = {
  id: "start-tracking-skip"
};
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_12 = ["href"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_13 = ["data-method"];
const SiteWithoutDatavue_type_template_id_3425106e_hoisted_14 = ["src", "alt"];
function SiteWithoutDatavue_type_template_id_3425106e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_VueEntryContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("VueEntryContainer");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [_ctx.showMethodDetails ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    id: "start-tracking-back",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      _ctx.showOverview();
    }, ["prevent"]))
  }, [SiteWithoutDatavue_type_template_id_3425106e_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Mobile_NavigationBack')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h1", SiteWithoutDatavue_type_template_id_3425106e_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.headline), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
    id: "start-tracking-cta",
    html: _ctx.ctaContent
  }, null, 8, ["html"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": `${_ctx.translate('SitesManager_DetectingYourSite')}…`,
    loading: _ctx.loading
  }, null, 8, ["loading-message", "loading"]), !_ctx.loading && !_ctx.showMethodDetails ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.recommendedMethod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SiteWithoutDatavue_type_template_id_3425106e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: _ctx.recommendedMethod.icon,
    alt: `${_ctx.recommendedMethod.name} logo`
  }, null, 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationTitle), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationText), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: `#${_ctx.recommendedMethod.id.toLowerCase()}`,
    class: "btn",
    id: "showMethod",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showMethod(_ctx.recommendedMethod.id), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.recommendedMethod.recommendationButton), 9, SiteWithoutDatavue_type_template_id_3425106e_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SiteWithoutDatavue_type_template_id_3425106e_hoisted_6, [SiteWithoutDatavue_type_template_id_3425106e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataOtherInstallMethods')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_SiteWithoutDataOtherInstallMethodsIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.trackingMethods, method => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "list-entry",
      key: method.id
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: `#${method.id.toLowerCase()}`,
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showMethod(method.id), ["prevent"])
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
    alt: `${_ctx.showMethodDetails.name} logo`
  }, null, 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
    html: _ctx.showMethodDetails.content
  }, null, 8, ["html"])], 8, SiteWithoutDatavue_type_template_id_3425106e_hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=template&id=3425106e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/SitesManager/vue/src/SiteWithoutData/SiteWithoutData.vue?vue&type=script&lang=ts
function SiteWithoutDatavue_type_script_lang_ts_extends() { SiteWithoutDatavue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SiteWithoutDatavue_type_script_lang_ts_extends.apply(this, arguments); }


/* harmony default export */ var SiteWithoutDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    ctaContent: String
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    VueEntryContainer: external_CoreHome_["VueEntryContainer"]
  },
  data() {
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
  created() {
    const params = {
      module: 'SitesManager',
      action: 'getTrackingMethodsForSite'
    };
    external_CoreHome_["AjaxHelper"].fetch(params).then(response => {
      this.trackingMethods = response.trackingMethods;
      this.recommendedMethod = response.recommendedMethod;
      this.loading = false;
      // set up watch once all data was fetched, to ensure tracking methods are available
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab, activeTab => {
        this.showMethodDetails = this.findTrackingMethod(activeTab);
      });
      if (external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab) {
        this.showMethodDetails = this.findTrackingMethod(external_CoreHome_["MatomoUrl"].hashParsed.value.activeTab);
      }
      this.checkIfSiteHasData();
    });
  },
  methods: {
    findTrackingMethod(methodId) {
      if (this.recommendedMethod && methodId && this.recommendedMethod.id.toLowerCase() === methodId.toLowerCase()) {
        return this.recommendedMethod;
      }
      let trackingMethod = null;
      Object.entries(this.trackingMethods).forEach(([, method]) => {
        if (methodId && method.id.toLowerCase() === methodId.toLowerCase()) {
          trackingMethod = method;
        }
      });
      return trackingMethod;
    },
    showMethod(methodId) {
      external_CoreHome_["MatomoUrl"].updateHash(SiteWithoutDatavue_type_script_lang_ts_extends(SiteWithoutDatavue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        activeTab: methodId.toLowerCase()
      }));
    },
    showOverview() {
      external_CoreHome_["MatomoUrl"].updateHash(SiteWithoutDatavue_type_script_lang_ts_extends(SiteWithoutDatavue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        activeTab: null
      }));
    },
    checkIfSiteHasData() {
      const params = {
        method: 'Live.getMostRecentVisitsDateTime',
        date: 'today',
        period: 'day',
        idSite: external_CoreHome_["Matomo"].idSite
      };
      const options = {
        // don't show error messages returned from API as notification
        createErrorNotification: false
      };
      external_CoreHome_["AjaxHelper"].fetch(params, options).then(response => {
        if (response && response.value !== '') {
          window.broadcast.propagateNewPage('date=today');
          return;
        }
        window.setTimeout(this.checkIfSiteHasData, this.currentInterval);
        this.currentInterval = Math.min(this.currentInterval + this.updateCheckInterval, this.maxInterval);
      }).catch(() => {
        // ignore errors to no distract user with an error message
      });
    }
  },
  computed: {
    ignoreSitesWithoutDataLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(SiteWithoutDatavue_type_script_lang_ts_extends(SiteWithoutDatavue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'SitesManager',
        action: 'ignoreNoDataMessage'
      }))}`;
    },
    headline() {
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