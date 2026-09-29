(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Marketplace"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Marketplace"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/Marketplace/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "Marketplace", function() { return /* reexport */ Marketplace; });
__webpack_require__.d(__webpack_exports__, "ManageLicenseKey", function() { return /* reexport */ ManageLicenseKey; });
__webpack_require__.d(__webpack_exports__, "GetNewPlugins", function() { return /* reexport */ GetNewPlugins; });
__webpack_require__.d(__webpack_exports__, "GetNewPluginsAdmin", function() { return /* reexport */ GetNewPluginsAdmin; });
__webpack_require__.d(__webpack_exports__, "GetPremiumFeatures", function() { return /* reexport */ GetPremiumFeatures; });
__webpack_require__.d(__webpack_exports__, "MissingReqsNotice", function() { return /* reexport */ MissingReqsNotice; });
__webpack_require__.d(__webpack_exports__, "OverviewIntro", function() { return /* reexport */ OverviewIntro; });
__webpack_require__.d(__webpack_exports__, "SubscriptionOverview", function() { return /* reexport */ SubscriptionOverview; });
__webpack_require__.d(__webpack_exports__, "RichMenuButton", function() { return /* reexport */ RichMenuButton; });
__webpack_require__.d(__webpack_exports__, "PluginGrid", function() { return /* reexport */ PluginGrid; });
__webpack_require__.d(__webpack_exports__, "PluginSection", function() { return /* reexport */ PluginSection; });
__webpack_require__.d(__webpack_exports__, "PluginCard", function() { return /* reexport */ PluginCard; });
__webpack_require__.d(__webpack_exports__, "CategoryTabs", function() { return /* reexport */ CategoryTabs; });
__webpack_require__.d(__webpack_exports__, "MarketplaceHero", function() { return /* reexport */ MarketplaceHero; });
__webpack_require__.d(__webpack_exports__, "SortMenu", function() { return /* reexport */ SortMenu; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=020f7942

const _hoisted_1 = {
  key: 0,
  class: "marketplacePage__detailsView"
};
const _hoisted_2 = {
  class: "marketplacePage__catalogue"
};
const _hoisted_3 = {
  key: 0,
  class: "marketplacePage__installAction"
};
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-left marketplacePage__backIcon",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_5 = {
  class: "marketplacePage__results",
  ref: "results"
};
const _hoisted_6 = {
  class: "marketplacePage__resultsCount",
  "aria-live": "polite"
};
const _hoisted_7 = {
  key: 0,
  class: "marketplacePage__resultsHeading"
};
const _hoisted_8 = {
  key: 0,
  class: "marketplacePage__sections"
};
const _hoisted_9 = {
  key: 3,
  class: "marketplacePage__loadError"
};
const _hoisted_10 = {
  class: "alert alert-danger"
};
const _hoisted_11 = {
  class: "marketplacePage__sentinel",
  ref: "sentinel"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RequestTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RequestTrial");
  const _component_PluginDetails = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginDetails");
  const _component_MarketplaceHero = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MarketplaceHero");
  const _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");
  const _component_CategoryTabs = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CategoryTabs");
  const _component_SortMenu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SortMenu");
  const _component_PluginSection = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginSection");
  const _component_PluginGrid = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginGrid");
  const _component_EmptyState = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EmptyState");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["marketplacePage", {
      'marketplacePage--details': !!_ctx.viewPluginName
    }]),
    ref: "root"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RequestTrial, {
    modelValue: _ctx.showRequestTrialForPlugin,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.showRequestTrialForPlugin = $event),
    onTrialRequested: _cache[1] || (_cache[1] = $event => _ctx.refresh())
  }, null, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["marketplacePage__views", {
      'marketplacePage__views--switching': _ctx.switching
    }]),
    ref: "views"
  }, [_ctx.viewPluginName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginDetails, {
    "plugin-card": _ctx.detailsCard,
    "is-super-user": _ctx.isSuperUser,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "is-multi-server-environment": _ctx.isMultiServerEnvironment,
    "is-valid-consumer": _ctx.isValidConsumer,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "has-some-admin-access": _ctx.hasSomeAdminAccess,
    "deactivate-nonce": _ctx.deactivateNonce,
    "activate-nonce": _ctx.activateNonce,
    "install-nonce": _ctx.installNonce,
    "update-nonce": _ctx.updateNonce,
    "num-users": _ctx.numUsers,
    onBack: _cache[2] || (_cache[2] = $event => _ctx.closeDetails()),
    onRequestTrial: _cache[3] || (_cache[3] = $event => _ctx.showRequestTrialForPlugin = $event)
  }, null, 8, ["plugin-card", "is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "has-some-admin-access", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MarketplaceHero, {
    "model-value": _ctx.searchQuery,
    "plugin-count": _ctx.allPlugins.length,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.updateQuery($event))
  }, null, 8, ["model-value", "plugin-count"]), _ctx.installAllPaidPluginsVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton, {
    disabled: _ctx.installDisabled
  }, null, 8, ["disabled"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.tabs.length > 1 && !_ctx.searchQuery.trim() && !_ctx.activePromotion ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CategoryTabs, {
    key: 1,
    ref: "categoryTabs",
    tabs: _ctx.tabs,
    "model-value": _ctx.activeTab,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.updateTab($event))
  }, null, 8, ["tabs", "model-value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showBackLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 2,
    type: "button",
    class: "marketplacePage__backLink",
    ref: "backLink",
    onClick: _cache[6] || (_cache[6] = $event => _ctx.closePromotion())
  }, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BackToMarketplace')), 1)], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["marketplacePage__resultsBar", {
      'marketplacePage__resultsBar--empty': !_ctx.resultsHeading && !_ctx.showSort,
      'marketplacePage__resultsBar--underBackLink': _ctx.showBackLink
    }]),
    ref: "resultsBar"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [_ctx.resultsHeading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.resultsHeading), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.showSort ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SortMenu, {
    key: 0,
    "model-value": _ctx.pluginSort,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.updateSort($event))
  }, null, 8, ["model-value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), _ctx.showSections ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sections, section => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginSection, {
      key: section.id,
      "section-id": section.id,
      "is-category": section.isCategory,
      plugins: section.plugins,
      context: _ctx.cardContext,
      onSeeAll: _cache[8] || (_cache[8] = $event => _ctx.seeAllInSection($event)),
      onOpenDetails: _cache[9] || (_cache[9] = $event => _ctx.openDetails($event)),
      onRequestTrial: _cache[10] || (_cache[10] = $event => _ctx.showRequestTrialForPlugin = $event)
    }, null, 8, ["section-id", "is-category", "plugins", "context"]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.showSections && (_ctx.loading || _ctx.filteredPlugins.length > 0) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginGrid, {
    key: 1,
    plugins: _ctx.pagedPlugins,
    "skeleton-count": _ctx.skeletonCount,
    context: _ctx.cardContext,
    onOpenDetails: _cache[11] || (_cache[11] = $event => _ctx.openDetails($event)),
    onRequestTrial: _cache[12] || (_cache[12] = $event => _ctx.showRequestTrialForPlugin = $event)
  }, null, 8, ["plugins", "skeleton-count", "context"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loading && !_ctx.loadFailed && _ctx.filteredPlugins.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_EmptyState, {
    key: 2,
    "has-query": !!_ctx.searchQuery.trim(),
    "can-reset": _ctx.hasActiveFilters,
    onReset: _cache[13] || (_cache[13] = $event => _ctx.resetFilters())
  }, null, 8, ["has-query", "can-reset"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loadFailed && !_ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginsNotAvailable')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, null, 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.viewPluginName]])], 2)], 2);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=020f7942

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/MarketplaceHero/MarketplaceHero.vue?vue&type=template&id=e85bdab6

const MarketplaceHerovue_type_template_id_e85bdab6_hoisted_1 = {
  class: "marketplaceHero"
};
const MarketplaceHerovue_type_template_id_e85bdab6_hoisted_2 = {
  class: "marketplaceHero__title"
};
const MarketplaceHerovue_type_template_id_e85bdab6_hoisted_3 = {
  class: "marketplaceHero__subtitle"
};
const MarketplaceHerovue_type_template_id_e85bdab6_hoisted_4 = {
  class: "marketplaceHero__search"
};
function MarketplaceHerovue_type_template_id_e85bdab6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchInput");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", MarketplaceHerovue_type_template_id_e85bdab6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h1", MarketplaceHerovue_type_template_id_e85bdab6_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_MatomoMarketplace')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", MarketplaceHerovue_type_template_id_e85bdab6_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_IntroShort')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", MarketplaceHerovue_type_template_id_e85bdab6_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchInput, {
    "model-value": _ctx.modelValue,
    "show-clear": true,
    placeholder: _ctx.placeholder,
    "aria-label": _ctx.placeholder,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('update:modelValue', $event))
  }, null, 8, ["model-value", "placeholder", "aria-label"])])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MarketplaceHero/MarketplaceHero.vue?vue&type=template&id=e85bdab6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/MarketplaceHero/MarketplaceHero.vue?vue&type=script&lang=ts


/* harmony default export */ var MarketplaceHerovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: {
      type: String,
      required: true
    },
    /** How many plugins the search covers. Zero while the catalogue is still on its way. */
    pluginCount: {
      type: Number,
      default: 0
    }
  },
  components: {
    SearchInput: external_CoreHome_["SearchInput"]
  },
  emits: ['update:modelValue'],
  computed: {
    /**
     * Names the real size of the catalogue rather than a number written into the translation, and
     * leaves it out entirely until the catalogue is here - "Search 0 plugins and themes" would
     * otherwise be what the reader sees for the length of the request.
     */
    placeholder() {
      return this.pluginCount > 0 ? Object(external_CoreHome_["translate"])('Marketplace_SearchPlaceholderWithCount', String(this.pluginCount)) : Object(external_CoreHome_["translate"])('Marketplace_SearchPlaceholder');
    }
  },
  methods: {
    translate: external_CoreHome_["translate"]
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MarketplaceHero/MarketplaceHero.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MarketplaceHero/MarketplaceHero.vue



MarketplaceHerovue_type_script_lang_ts.render = MarketplaceHerovue_type_template_id_e85bdab6_render

/* harmony default export */ var MarketplaceHero = (MarketplaceHerovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/CategoryTabs/CategoryTabs.vue?vue&type=template&id=63662302

const CategoryTabsvue_type_template_id_63662302_hoisted_1 = {
  class: "categoryTabs"
};
const CategoryTabsvue_type_template_id_63662302_hoisted_2 = {
  class: "categoryTabs__select"
};
const CategoryTabsvue_type_template_id_63662302_hoisted_3 = ["value", "aria-label"];
const CategoryTabsvue_type_template_id_63662302_hoisted_4 = ["value"];
const CategoryTabsvue_type_template_id_63662302_hoisted_5 = {
  class: "categoryTabs__bar",
  ref: "bar"
};
const CategoryTabsvue_type_template_id_63662302_hoisted_6 = {
  class: "categoryTabs__list"
};
const CategoryTabsvue_type_template_id_63662302_hoisted_7 = ["aria-current", "onClick"];
const CategoryTabsvue_type_template_id_63662302_hoisted_8 = {
  key: 0,
  class: "categoryTabs__more"
};
const CategoryTabsvue_type_template_id_63662302_hoisted_9 = ["aria-expanded"];
const CategoryTabsvue_type_template_id_63662302_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const CategoryTabsvue_type_template_id_63662302_hoisted_11 = {
  key: 0,
  class: "categoryTabs__menu"
};
const _hoisted_12 = ["aria-current", "onClick"];
function CategoryTabsvue_type_template_id_63662302_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default categoryTabs__selectInput",
    value: _ctx.modelValue,
    "aria-label": _ctx.translate('Marketplace_Categories'),
    onChange: _cache[0] || (_cache[0] = $event => _ctx.selectFromEvent($event))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.tabs, tab => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: tab.id,
      value: tab.id
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tabLabel(tab)), 9, CategoryTabsvue_type_template_id_63662302_hoisted_4);
  }), 128))], 40, CategoryTabsvue_type_template_id_63662302_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.tabs, (tab, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: tab.id,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["categoryTabs__tab", {
        'categoryTabs__tab--active': tab.id === _ctx.modelValue,
        'categoryTabs__tab--overflow': index >= _ctx.alwaysVisibleCount
      }]),
      "aria-current": tab.id === _ctx.modelValue ? 'true' : undefined,
      onClick: $event => _ctx.select(tab.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tabLabel(tab)), 11, CategoryTabsvue_type_template_id_63662302_hoisted_7);
  }), 128))]), _ctx.hasOverflowTabs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    ref: "moreButton",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["categoryTabs__tab categoryTabs__tab--more", {
      'categoryTabs__tab--active': _ctx.activeIsInOverflow
    }]),
    "aria-expanded": _ctx.expanded,
    onClick: _cache[1] || (_cache[1] = $event => _ctx.expanded = !_ctx.expanded)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.activeIsInOverflow ? _ctx.activeLabel : _ctx.translate('Marketplace_Categories')), 1), CategoryTabsvue_type_template_id_63662302_hoisted_10], 10, CategoryTabsvue_type_template_id_63662302_hoisted_9), _ctx.expanded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CategoryTabsvue_type_template_id_63662302_hoisted_11, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.overflowTabs, tab => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: tab.id,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["categoryTabs__menuItem", {
        'categoryTabs__menuItem--active': tab.id === _ctx.modelValue
      }]),
      "aria-current": tab.id === _ctx.modelValue ? 'true' : undefined,
      onClick: $event => _ctx.select(tab.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tabLabel(tab)), 11, _hoisted_12);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/CategoryTabs/CategoryTabs.vue?vue&type=template&id=63662302

// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/pluginGrouping.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Filtering, sorting and tab construction for the overview, on the client by design.
 *
 * Only the three queries in `Api\Client::getWarmedOverviewLists()` get the 90 minute cache, and
 * varying query, sort or category misses all three. Moving any of this behind a request parameter
 * turns every tab click into a cold catalogue download, silently.
 */
const TAB_ALL = 'all';
const TAB_BUNDLES = 'bundles';
const TAB_THEMES = 'themes';
/**
 * The two promoted rows at the top of the overview. Not tabs and not categories: the Marketplace
 * chooses what is in them and in what order, and "See all" opens a list of its own rather than a
 * tab - see {@link buildPromoSections} and {@link promotedPlugins}.
 */
const SECTION_FEATURED = 'featured';
const SECTION_BESTSELLING = 'bestselling';
/** The promoted sections, in display order. Featured leads, Best selling follows. */
const PROMO_SECTIONS = [SECTION_FEATURED, SECTION_BESTSELLING];
/**
 * Featured is the first thing on the page, so a row of one or two reads as an empty Marketplace
 * rather than as a short list. Below this many it is left out entirely; every other section shows
 * from one plugin up.
 */
const FEATURED_MIN_PLUGINS = 4;
/**
 * Everything no category claims. Invented by {@link buildTabs}, not sent by the Marketplace, so
 * that unclassified plugins - most of the catalogue - get a tab of their own.
 */
const TAB_OTHER = 'other';
/**
 * The legacy singular `category` field's word for unclassified. Dropped if it ever appears in
 * `categories`, or the bar grows an "Uncategorised" tab beside "Other" for the same idea.
 */
const CATEGORY_UNCATEGORISED = 'uncategorised';
/** Sort methods. The first three match `Marketplace\Input\Sort`; developer is client-side only. */
const SORT_LAST_UPDATED = 'lastupdated';
const SORT_NEWEST = 'newest';
const SORT_ALPHA = 'alpha';
const SORT_DEVELOPER = 'developer';
/**
 * The tabs that are not categories. Themes is one of them but does not lead the bar with the
 * others: {@link buildTabs} files it after the category run, beside Other.
 */
const TYPE_TABS = [TAB_ALL, TAB_BUNDLES, TAB_THEMES];
/** The type tabs that lead the bar, in display order. Category tabs follow them. */
const LEADING_TYPE_TABS = [TAB_ALL, TAB_BUNDLES];
/**
 * The tabs that close the bar, in display order, after the categories. Neither is a category a
 * plugin can carry: Themes is a kind of plugin and Other is what no category claimed, so both
 * would break the alphabetical run they now follow.
 */
const TRAILING_TABS = [TAB_THEMES, TAB_OTHER];
const MATOMO_OWNERS = ['piwik', 'matomo-org'];
/**
 * The Marketplace sends `2015-11-20 19:16:03`; `Date.parse()` only specifies the ISO form, so
 * parse it here. Unparseable values return null, which callers sort last.
 */
function parseMarketplaceDate(value) {
  var _parts$, _parts$2, _parts$3;
  if (typeof value !== 'string') {
    return null;
  }
  const parts = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?/.exec(value.trim());
  if (!parts) {
    return null;
  }
  const timestamp = Date.UTC(Number(parts[1]), Number(parts[2]) - 1, Number(parts[3]), Number((_parts$ = parts[4]) !== null && _parts$ !== void 0 ? _parts$ : 0), Number((_parts$2 = parts[5]) !== null && _parts$2 !== void 0 ? _parts$2 : 0), Number((_parts$3 = parts[6]) !== null && _parts$3 !== void 0 ? _parts$3 : 0));
  return Number.isNaN(timestamp) ? null : timestamp;
}
/** The name a card credits, so that sorting and display agree on who owns a Matomo plugin. */
function ownerLabel(plugin) {
  return MATOMO_OWNERS.includes(plugin.owner) ? 'Matomo' : plugin.owner || '';
}
/**
 * Whether a plugin is credited to Matomo, on its card and on its page. A bundle always is, whoever
 * the Marketplace names as its owner: a bundle is Matomo's own packaging of Matomo's plugins, and
 * it is sold as such.
 */
function isByMatomo(plugin) {
  return !!plugin.isBundle || ownerLabel(plugin) === 'Matomo';
}
/**
 * The same fields the Marketplace's own `plugins?query=` search covers, plus the owner as the card
 * credits it - "Matomo" has to find a piwik-owned plugin, since that is the only name on screen.
 */
function matchesQuery(plugin, query) {
  const needle = (query || '').trim().toLowerCase();
  if (!needle) {
    return true;
  }
  const fields = [plugin.displayName, plugin.name, plugin.description, plugin.owner, ownerLabel(plugin), ...(Array.isArray(plugin.keywords) ? plugin.keywords : [])];
  return fields.some(field => (typeof field === 'string' ? field : '').toLowerCase().includes(needle));
}
/**
 * The category slugs a plugin is filed under. An array by design, so a plugin can appear in more
 * than one section; anything malformed reads as unclassified rather than throwing.
 */
function pluginCategories(plugin) {
  const {
    categories
  } = plugin;
  if (!Array.isArray(categories)) {
    return [];
  }
  return categories.filter(slug => typeof slug === 'string' && !!slug && slug !== CATEGORY_UNCATEGORISED);
}
/**
 * The promotion lists this plugin appears in, as slug -> position. Anything malformed reads as no
 * promotion rather than throwing: a bad position would otherwise decide where a card sits.
 */
function pluginPromotions(plugin) {
  const {
    promotions
  } = plugin;
  if (!promotions || typeof promotions !== 'object' || Array.isArray(promotions)) {
    return {};
  }
  const positions = {};
  Object.keys(promotions).forEach(slug => {
    const position = promotions[slug];
    if (slug && typeof position === 'number' && Number.isFinite(position)) {
      positions[slug] = position;
    }
  });
  return positions;
}
/**
 * Whether the reader already has this plugin, by any route: installed, or covered by a license
 * whatever its state. A cancelled or expired license still means they have seen the plugin and
 * decided, so Featured - which is there to introduce plugins - passes over it. A requested trial
 * is not a licence and does not count.
 *
 * Only Featured filters on this. Best selling deliberately shows what the reader owns, so that
 * owning the popular ones is visible rather than inferred from an absence.
 */
function isOwned(plugin) {
  return !!plugin.isInstalled || !!plugin.licenseStatus;
}
/** Whether no category claims this plugin. */
function isUnclassified(plugin) {
  return pluginCategories(plugin).length === 0;
}
function matchesTab(plugin, tabId) {
  switch (tabId) {
    case TAB_ALL:
      return true;
    case TAB_BUNDLES:
      return !!plugin.isBundle;
    case TAB_THEMES:
      return !!plugin.isTheme;
    case TAB_OTHER:
      return !plugin.isTheme && !plugin.isBundle && (isUnclassified(plugin) || pluginCategories(plugin).includes(TAB_OTHER));
    default:
      return pluginCategories(plugin).includes(tabId);
  }
}
/**
 * Sorts a copy, never the argument. Every comparison falls back to the display name, so ties and
 * missing values keep a predictable order rather than the fetch's.
 */
function sortPlugins(plugins, sort) {
  const byName = (a, b) => (a.displayName || '').localeCompare(b.displayName || '');
  const descending = (a, b) => {
    if (a === b) {
      return 0;
    }
    if (a === null) {
      return 1;
    }
    if (b === null) {
      return -1;
    }
    return b - a;
  };
  const byDate = field => (a, b) => descending(parseMarketplaceDate(a[field]), parseMarketplaceDate(b[field])) || byName(a, b);
  const sorted = [...plugins];
  switch (sort) {
    case SORT_NEWEST:
      return sorted.sort(byDate('createdDateTime'));
    case SORT_ALPHA:
      return sorted.sort(byName);
    case SORT_DEVELOPER:
      return sorted.sort((a, b) => ownerLabel(a).localeCompare(ownerLabel(b)) || byName(a, b));
    case SORT_LAST_UPDATED:
    default:
      return sorted.sort(byDate('lastUpdatedRaw'));
  }
}
/**
 * Bundles by seat tier, smallest first, so the Team, Business and Enterprise ladder reads in the
 * order it is priced in rather than alphabetically.
 *
 * A bundle whose tier carries no number - `bundleSeats` is unset, see `Plugins::addBundleSeats()` -
 * sorts last: "Unlimited users" is the top of the ladder, and a bundle whose variations disagree
 * on a tier has no place on it. Ties fall back to the display name, as every other sort does.
 */
function sortBundles(plugins) {
  return [...plugins].sort((a, b) => {
    const seatsA = typeof a.bundleSeats === 'number' ? a.bundleSeats : Infinity;
    const seatsB = typeof b.bundleSeats === 'number' ? b.bundleSeats : Infinity;
    return (seatsA === seatsB ? 0 : seatsA - seatsB) || (a.displayName || '').localeCompare(b.displayName || '');
  });
}
/**
 * How one tab's list is ordered. Bundles have an order of their own - see {@link sortBundles} -
 * and ignore the sort control; every other tab is {@link sortPlugins}.
 */
function sortTabPlugins(plugins, sort, tabId) {
  return tabId === TAB_BUNDLES ? sortBundles(plugins) : sortPlugins(plugins, sort);
}
function filterPlugins(plugins, tabId, query) {
  return plugins.filter(plugin => matchesTab(plugin, tabId) && matchesQuery(plugin, query));
}
/** Falls back to the slug, so a caller with no translations still gets a stable order. */
const slugAsLabel = tab => tab.id;
/**
 * The tab list, built from the data rather than declared, so the category half follows whatever
 * slugs arrive in `plugin.categories`. An empty tab is left out: some categories hold as few as
 * five plugins, so one delisting can empty one.
 *
 * `labelFor` is a parameter rather than an import so that this module stays free of `CoreHome` -
 * see the note in `categoryLabels.ts`.
 */
function buildTabs(plugins, labelFor = slugAsLabel) {
  const tabs = [];
  LEADING_TYPE_TABS.forEach(id => {
    const count = id === TAB_ALL ? plugins.length : plugins.filter(plugin => matchesTab(plugin, id)).length;
    if (count > 0) {
      tabs.push({
        id,
        count,
        isCategory: false
      });
    }
  });
  const categoryCounts = new Map();
  plugins.forEach(plugin => {
    pluginCategories(plugin).forEach(slug => {
      var _categoryCounts$get;
      if (slug === TAB_OTHER) {
        return;
      }
      categoryCounts.set(slug, ((_categoryCounts$get = categoryCounts.get(slug)) !== null && _categoryCounts$get !== void 0 ? _categoryCounts$get : 0) + 1);
    });
  });
  // Ordered by the label rather than the slug: the slug is always English, so any other locale
  // would otherwise get a bar sorted by words its reader never sees.
  Array.from(categoryCounts.keys()).sort((a, b) => labelFor({
    id: a,
    isCategory: true
  }).localeCompare(labelFor({
    id: b,
    isCategory: true
  }))).forEach(id => {
    tabs.push({
      id,
      count: categoryCounts.get(id),
      isCategory: true
    });
  });
  TRAILING_TABS.forEach(id => {
    const count = plugins.filter(plugin => matchesTab(plugin, id)).length;
    if (count > 0) {
      tabs.push({
        id,
        count,
        isCategory: !TYPE_TABS.includes(id)
      });
    }
  });
  return tabs;
}
/**
 * The section stack, derived from the tab list so a section and its tab are the same set by
 * construction: "See all" lands on exactly what the row counted. Sections overlap on purpose.
 * Ordering within a section is the caller's, so a row and its category can sort alike.
 */
function buildSections(plugins, labelFor = slugAsLabel) {
  return buildTabs(plugins, labelFor).filter(tab => tab.id !== TAB_ALL).map(tab => ({
    id: tab.id,
    isCategory: tab.isCategory,
    plugins: plugins.filter(plugin => matchesTab(plugin, tab.id))
  }));
}
/** Whether a section is one of the promoted rows rather than a tab's contents. */
function isPromoSection(sectionId) {
  return PROMO_SECTIONS.includes(sectionId);
}
/**
 * Everything one promotion holds, in the Marketplace's order: the row shows the first cards of
 * this and its "See all" view shows all of them, so both are the same list cut in two places.
 *
 * Ordering is the Marketplace's position rather than the page's sort - promoting a plugin is
 * pointless if the reader's sort can move it to the bottom of the row - and ties fall back to the
 * display name so a duplicated position cannot reorder itself between renders.
 */
function promotedPlugins(plugins, sectionId) {
  return plugins.filter(plugin => sectionId in pluginPromotions(plugin)).filter(plugin => sectionId !== SECTION_FEATURED || !isOwned(plugin)).sort((a, b) => pluginPromotions(a)[sectionId] - pluginPromotions(b)[sectionId] || (a.displayName || '').localeCompare(b.displayName || ''));
}
/**
 * The promoted rows, in front of the stack {@link buildSections} derives from the tab bar.
 *
 * Kept apart from that one on purpose: those sections are a tab's contents by construction, and
 * these have no tab, so their "See all" opens the promotion's own list - see
 * {@link promotedPlugins}, which is what both the row and that list are cut from.
 *
 * A row the reader has nothing to gain from is left out: Featured hides what they already own, and
 * then hides itself unless {@link FEATURED_MIN_PLUGINS} plugins are left to show.
 */
function buildPromoSections(plugins) {
  const sections = [];
  PROMO_SECTIONS.forEach(id => {
    const promoted = promotedPlugins(plugins, id);
    const minimum = id === SECTION_FEATURED ? FEATURED_MIN_PLUGINS : 1;
    if (promoted.length >= minimum) {
      sections.push({
        id,
        isCategory: false,
        plugins: promoted
      });
    }
  });
  return sections;
}
/**
 * Maps the legacy `pluginType` hash parameter onto a tab. Nothing writes it any more, but
 * CorePluginsAdmin's ThemesIntro.vue and PluginsTable.vue still link in with it.
 */
function tabFromLegacyPluginType(pluginType) {
  switch (pluginType) {
    case 'themes':
      return TAB_THEMES;
    case 'plugins':
      return TAB_ALL;
    default:
      return null;
  }
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/categoryLabels.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/**
 * Labels for the tab bar, the card chips and the section headings, which all name the same things.
 *
 * Beside `pluginGrouping.ts` rather than inside it: that module imports nothing from `CoreHome`,
 * and its spec relies on loading it without mocking one.
 */
/**
 * The sections that name a plugin type or a promotion rather than a category slug, and so have a
 * fixed label. The promoted two have no tab, but their headings are resolved the same way.
 */
const TYPE_TAB_KEYS = {
  [TAB_ALL]: 'Marketplace_Home',
  [TAB_BUNDLES]: 'Marketplace_Bundles',
  [TAB_THEMES]: 'CorePluginsAdmin_Themes',
  [SECTION_FEATURED]: 'Marketplace_Featured',
  [SECTION_BESTSELLING]: 'Marketplace_BestSelling'
};
/**
 * The display name for a category slug, or '' for no category. Falls back to the slug itself, so a
 * category with no key yet still reads. translateOrDefault, not translate: an unknown key makes
 * translate() return "The string ... was not loaded in javascript".
 *
 * Capitalised as English, not in the reader's locale: the slug is an ASCII identifier and half of
 * what it builds is a translation key. Left to the browser's locale, 'insights' capitalises to
 * 'Insights' everywhere except Turkish and Azerbaijani, where the i takes a dot - so those two
 * would look up Marketplace_Categoryİnsights, a key nobody wrote, miss, and fall back to a
 * spelling of the slug no other reader sees.
 */
function categoryLabel(slug) {
  if (!slug) {
    return '';
  }
  const name = Object(external_CoreHome_["ucfirst"])(slug, 'en');
  const key = `Marketplace_Category${name}`;
  const label = Object(external_CoreHome_["translateOrDefault"])(key);
  return label === key ? name : label;
}
function tabLabel(tab) {
  if (!tab.isCategory) {
    var _TYPE_TAB_KEYS$tab$id;
    return Object(external_CoreHome_["translate"])((_TYPE_TAB_KEYS$tab$id = TYPE_TAB_KEYS[tab.id]) !== null && _TYPE_TAB_KEYS$tab$id !== void 0 ? _TYPE_TAB_KEYS$tab$id : tab.id);
  }
  return categoryLabel(tab.id);
}
/**
 * The one category chip a plugin carries, on its card and on its page. Always a label: a plugin no
 * category claims falls back to Other, the same tab it is listed under, so that every card in a row
 * carries a chip and the titles and descriptions below line up across the row.
 */
function chipLabel(plugin) {
  var _pluginCategories$;
  if (plugin.isBundle) {
    return Object(external_CoreHome_["translate"])(TYPE_TAB_KEYS[TAB_BUNDLES]);
  }
  return categoryLabel((_pluginCategories$ = pluginCategories(plugin)[0]) !== null && _pluginCategories$ !== void 0 ? _pluginCategories$ : TAB_OTHER);
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/CategoryTabs/CategoryTabs.vue?vue&type=script&lang=ts



/**
 * At or below this width the bar keeps the first few tabs and moves the rest into a menu. Written
 * as the same max-width test `CategoryTabs.less` uses: the min-width inverse disagrees with the
 * stylesheet at exactly 1400px, leaving the tabs past the fifth unreachable.
 */
const NARROW_BREAKPOINT = '(max-width: 1400px)';
/** How many tabs stay on the bar when there is not room for all of them. */
const ALWAYS_VISIBLE = 5;
/* harmony default export */ var CategoryTabsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    tabs: {
      type: Array,
      required: true
    },
    modelValue: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      expanded: false,
      isWide: true,
      narrowQuery: null,
      onNarrowChange: null,
      onDocumentClick: null,
      onKeydown: null
    };
  },
  mounted() {
    const narrowQuery = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(window.matchMedia(NARROW_BREAKPOINT));
    this.narrowQuery = narrowQuery;
    this.onNarrowChange = () => {
      this.isWide = !narrowQuery.matches;
      if (this.isWide) {
        this.expanded = false;
      }
    };
    narrowQuery.addEventListener('change', this.onNarrowChange);
    this.onNarrowChange();
    this.onDocumentClick = event => {
      const bar = this.$refs.bar;
      if (this.expanded && bar && !bar.contains(event.target)) {
        this.expanded = false;
      }
    };
    this.onKeydown = event => {
      if (event.key === 'Escape' && this.expanded) {
        this.expanded = false;
        const moreButton = this.$refs.moreButton;
        if (moreButton) {
          moreButton.focus();
        }
      }
    };
    document.addEventListener('mousedown', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  unmounted() {
    if (this.narrowQuery && this.onNarrowChange) {
      this.narrowQuery.removeEventListener('change', this.onNarrowChange);
    }
    if (this.onDocumentClick) {
      document.removeEventListener('mousedown', this.onDocumentClick);
    }
    if (this.onKeydown) {
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
  computed: {
    alwaysVisibleCount() {
      return this.isWide ? this.tabs.length : ALWAYS_VISIBLE;
    },
    overflowTabs() {
      return this.tabs.slice(this.alwaysVisibleCount);
    },
    hasOverflowTabs() {
      return this.overflowTabs.length > 0;
    },
    activeIsInOverflow() {
      return this.overflowTabs.some(tab => tab.id === this.modelValue);
    },
    activeLabel() {
      const active = this.tabs.find(tab => tab.id === this.modelValue);
      return active ? this.tabLabel(active) : '';
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    /** The cast lives here rather than in the template, which is compiled without TypeScript. */
    selectFromEvent(event) {
      this.select(event.target.value);
    },
    select(tabId) {
      this.expanded = false;
      this.$emit('update:modelValue', tabId);
    },
    tabLabel: tabLabel,
    /**
     * Moves focus onto the selected tab, for a caller that changed the selection from elsewhere on
     * the page - a section's "See all" - whose own control the re-render removes.
     *
     * An overflow tab is display:none until there is room for it, and focus() does nothing on one,
     * so the More button stands in; it already renders as active in that case.
     */
    focusActiveTab() {
      const bar = this.$refs.bar;
      const active = bar === null || bar === void 0 ? void 0 : bar.querySelector('.categoryTabs__tab--active');
      const moreButton = this.$refs.moreButton;
      if (active && active.offsetParent !== null) {
        active.focus();
        return;
      }
      const fallback = moreButton !== null && moreButton !== void 0 ? moreButton : active;
      if (fallback) {
        fallback.focus();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/CategoryTabs/CategoryTabs.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/CategoryTabs/CategoryTabs.vue



CategoryTabsvue_type_script_lang_ts.render = CategoryTabsvue_type_template_id_63662302_render

/* harmony default export */ var CategoryTabs = (CategoryTabsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/SortMenu/SortMenu.vue?vue&type=template&id=299ef286

const SortMenuvue_type_template_id_299ef286_hoisted_1 = {
  class: "sortMenu",
  ref: "root"
};
const SortMenuvue_type_template_id_299ef286_hoisted_2 = ["aria-expanded"];
const SortMenuvue_type_template_id_299ef286_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const SortMenuvue_type_template_id_299ef286_hoisted_4 = {
  key: 0,
  class: "sortMenu__menu"
};
const SortMenuvue_type_template_id_299ef286_hoisted_5 = ["aria-current", "onClick"];
const SortMenuvue_type_template_id_299ef286_hoisted_6 = {
  key: 0,
  class: "icon-ok sortMenu__check",
  "aria-hidden": "true"
};
function SortMenuvue_type_template_id_299ef286_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SortMenuvue_type_template_id_299ef286_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    ref: "trigger",
    class: "sortMenu__trigger",
    "aria-expanded": _ctx.expanded,
    onClick: _cache[0] || (_cache[0] = $event => _ctx.expanded = !_ctx.expanded)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SortBy')) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.activeLabel), 1), SortMenuvue_type_template_id_299ef286_hoisted_3], 8, SortMenuvue_type_template_id_299ef286_hoisted_2), _ctx.expanded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SortMenuvue_type_template_id_299ef286_hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: option.id,
      type: "button",
      class: "sortMenu__item",
      "aria-current": option.id === _ctx.modelValue ? 'true' : undefined,
      onClick: $event => _ctx.select(option.id)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(option.labelKey)), 1), option.id === _ctx.modelValue ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SortMenuvue_type_template_id_299ef286_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SortMenuvue_type_template_id_299ef286_hoisted_5);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SortMenu/SortMenu.vue?vue&type=template&id=299ef286

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/SortMenu/SortMenu.vue?vue&type=script&lang=ts



const OPTIONS = [{
  id: SORT_LAST_UPDATED,
  labelKey: 'Marketplace_SortByLastUpdated'
}, {
  id: SORT_NEWEST,
  labelKey: 'Marketplace_SortByNewest'
}, {
  id: SORT_ALPHA,
  labelKey: 'Marketplace_SortByAlpha'
}, {
  id: SORT_DEVELOPER,
  labelKey: 'Marketplace_Developer'
}];
/* harmony default export */ var SortMenuvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      expanded: false,
      onDocumentClick: null,
      onKeydown: null
    };
  },
  mounted() {
    this.onDocumentClick = event => {
      const root = this.$refs.root;
      if (this.expanded && root && !root.contains(event.target)) {
        this.expanded = false;
      }
    };
    this.onKeydown = event => {
      if (event.key === 'Escape' && this.expanded) {
        this.expanded = false;
        const trigger = this.$refs.trigger;
        if (trigger) {
          trigger.focus();
        }
      }
    };
    document.addEventListener('mousedown', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  unmounted() {
    if (this.onDocumentClick) {
      document.removeEventListener('mousedown', this.onDocumentClick);
    }
    if (this.onKeydown) {
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
  computed: {
    options() {
      return OPTIONS;
    },
    activeLabel() {
      var _OPTIONS$find;
      const active = (_OPTIONS$find = OPTIONS.find(option => option.id === this.modelValue)) !== null && _OPTIONS$find !== void 0 ? _OPTIONS$find : OPTIONS[0];
      return Object(external_CoreHome_["translate"])(active.labelKey);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    select(sort) {
      this.expanded = false;
      this.$emit('update:modelValue', sort);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SortMenu/SortMenu.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SortMenu/SortMenu.vue



SortMenuvue_type_script_lang_ts.render = SortMenuvue_type_template_id_299ef286_render

/* harmony default export */ var SortMenu = (SortMenuvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginGrid/PluginGrid.vue?vue&type=template&id=b4834194

const PluginGridvue_type_template_id_b4834194_hoisted_1 = {
  class: "pluginGrid"
};
function PluginGridvue_type_template_id_b4834194_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_PluginCard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginCard");
  const _component_PluginCardSkeleton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginCardSkeleton");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginGridvue_type_template_id_b4834194_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.visiblePlugins, plugin => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginCard, {
      key: plugin.name,
      plugin: plugin,
      context: _ctx.context,
      onOpenDetails: _cache[0] || (_cache[0] = $event => _ctx.$emit('openDetails', $event)),
      onRequestTrial: _cache[1] || (_cache[1] = $event => _ctx.$emit('requestTrial', $event))
    }, null, 8, ["plugin", "context"]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.skeletonCount, index => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginCardSkeleton, {
      key: `skeleton-${index}`
    });
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/PluginGrid.vue?vue&type=template&id=b4834194

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/PluginCard.vue?vue&type=template&id=797ec852

const PluginCardvue_type_template_id_797ec852_hoisted_1 = ["data-plugin"];
const PluginCardvue_type_template_id_797ec852_hoisted_2 = {
  class: "pluginCard__plate"
};
const PluginCardvue_type_template_id_797ec852_hoisted_3 = {
  class: "pluginCard__shot"
};
const PluginCardvue_type_template_id_797ec852_hoisted_4 = ["src", "srcset"];
const PluginCardvue_type_template_id_797ec852_hoisted_5 = {
  class: "pluginCard__chipList"
};
const PluginCardvue_type_template_id_797ec852_hoisted_6 = {
  key: 0,
  class: "pluginCard__chipItem pluginCard__chipItem--matomo"
};
const PluginCardvue_type_template_id_797ec852_hoisted_7 = {
  class: "pluginCard__badge"
};
const PluginCardvue_type_template_id_797ec852_hoisted_8 = {
  key: 1,
  class: "pluginCard__chipItem"
};
const PluginCardvue_type_template_id_797ec852_hoisted_9 = {
  class: "pluginCard__title"
};
const PluginCardvue_type_template_id_797ec852_hoisted_10 = ["href", "title"];
const PluginCardvue_type_template_id_797ec852_hoisted_11 = {
  class: "pluginCard__description"
};
const PluginCardvue_type_template_id_797ec852_hoisted_12 = {
  key: 0,
  class: "pluginCard__meta"
};
const _hoisted_13 = {
  class: "pluginCard__seats"
};
const _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "pluginCard__seatsIcon icon-ok",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_15 = {
  key: 1,
  class: "pluginCard__meta"
};
const _hoisted_16 = {
  key: 0,
  class: "pluginCard__updated"
};
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "pluginCard__updatedIcon icon-clock",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_18 = ["title"];
const _hoisted_19 = {
  class: "pluginCard__actions"
};
function PluginCardvue_type_template_id_797ec852_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoGlyph = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoGlyph");
  const _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("article", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["pluginCard", {
      'pluginCard--bundle': _ctx.plugin.isBundle
    }]),
    "data-plugin": _ctx.plugin.name
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginCardvue_type_template_id_797ec852_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginCardvue_type_template_id_797ec852_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["pluginCard__shotImage", {
      'pluginCard__shotImage--placeholder': _ctx.isPlaceholderCover
    }]),
    src: _ctx.coverImageUrl(440, 240),
    srcset: _ctx.coverImageSrcset,
    sizes: "(max-width: 767px) 100vw, (max-width: 1279px) 50vw, 320px",
    alt: "",
    width: "440",
    height: "240",
    loading: "lazy",
    decoding: "async",
    onError: _cache[0] || (_cache[0] = $event => _ctx.coverImageFailed = true)
  }, null, 42, PluginCardvue_type_template_id_797ec852_hoisted_4)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginCardvue_type_template_id_797ec852_hoisted_5, [_ctx.isByMatomo ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginCardvue_type_template_id_797ec852_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginCardvue_type_template_id_797ec852_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoGlyph)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CategoryMatomo')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.categoryLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginCardvue_type_template_id_797ec852_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.categoryLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", PluginCardvue_type_template_id_797ec852_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "pluginCard__titleLink",
    href: _ctx.detailsHref,
    title: _ctx.plugin.displayName,
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('openDetails', _ctx.plugin), ["exact", "prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.displayName), 9, PluginCardvue_type_template_id_797ec852_hoisted_10)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", PluginCardvue_type_template_id_797ec852_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.description), 1), _ctx.bundleSeatsLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginCardvue_type_template_id_797ec852_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_13, [_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.bundleSeatsLabel), 1)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [_ctx.plugin.lastUpdated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_16, [_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UpdatedOn', _ctx.plugin.lastUpdated)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isByMatomo ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 1,
    class: "pluginCard__owner",
    title: _ctx.ownerName
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.ownerName), 9, _hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(_ctx.context, {
    plugin: _ctx.plugin,
    "in-modal": false,
    onOpenDetailsModal: _cache[2] || (_cache[2] = $event => _ctx.$emit('openDetails', _ctx.plugin)),
    onRequestTrial: _cache[3] || (_cache[3] = $event => _ctx.$emit('requestTrial', _ctx.plugin))
  }), null, 16, ["plugin"])])], 10, PluginCardvue_type_template_id_797ec852_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCard.vue?vue&type=template&id=797ec852

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=4cc60a53

const CTAContainervue_type_template_id_4cc60a53_hoisted_1 = ["href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_2 = ["href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_3 = ["href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_4 = ["href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_5 = ["title"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_6 = ["title", "href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_7 = ["href"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_8 = ["title"];
const CTAContainervue_type_template_id_4cc60a53_hoisted_9 = ["title"];
function CTAContainervue_type_template_id_4cc60a53_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MoreDetailsAction = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MoreDetailsAction");
  const _component_CTAStatus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAStatus");
  const _component_DownloadButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DownloadButton");
  return _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CTAStatus, {
    key: 0,
    tone: "danger",
    label: _ctx.translate('Marketplace_LicenseMissing'),
    "in-modal": _ctx.inModal,
    "has-action": !_ctx.inModal
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
      "show-as-button": true,
      label: _ctx.translate('General_MoreDetails'),
      onAction: _cache[0] || (_cache[0] = $event => _ctx.$emit('openDetailsModal'))
    }, null, 8, ["label"])]),
    _: 1
  }, 8, ["label", "in-modal", "has-action"])) : _ctx.inModal && _ctx.plugin.hasExceededLicense && _ctx.plugin.consumer.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "btn btn-block",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.plugin.consumer.loginUrl)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UpgradeSubscription')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_1)) : _ctx.plugin.hasExceededLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CTAStatus, {
    key: 2,
    tone: "danger",
    label: _ctx.translate('Marketplace_LicenseExceeded'),
    "in-modal": _ctx.inModal,
    "has-action": !_ctx.inModal
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
      "show-as-button": true,
      label: _ctx.translate('General_MoreDetails'),
      onAction: _cache[1] || (_cache[1] = $event => _ctx.$emit('openDetailsModal'))
    }, null, 8, ["label"])]),
    _: 1
  }, 8, ["label", "in-modal", "has-action"])) : _ctx.plugin.canBeUpdated && 0 == _ctx.plugin.missingRequirements.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [_ctx.isAutoUpdatePossible && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "btn btn-block",
    href: _ctx.linkToUpdate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreUpdater_UpdateTitle')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_2)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CTAStatus, {
    key: 1,
    tone: "warning",
    label: _ctx.translate('Marketplace_CannotUpdate'),
    "in-modal": _ctx.inModal,
    "has-action": !_ctx.inModal || _ctx.isDownloadableWithoutAutoUpdate
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
      key: 0,
      "show-as-button": true,
      label: _ctx.translate('General_MoreDetails'),
      onAction: _cache[2] || (_cache[2] = $event => _ctx.$emit('openDetailsModal'))
    }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
      plugin: _ctx.plugin,
      "show-as-button": !_ctx.inModal,
      "is-auto-update-possible": _ctx.isAutoUpdatePossible
    }, null, 8, ["plugin", "show-as-button", "is-auto-update-possible"])]),
    _: 1
  }, 8, ["label", "in-modal", "has-action"]))], 64)) : _ctx.plugin.isInstalled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CTAStatus, {
    key: 4,
    tone: "success",
    label: _ctx.translate('General_Installed'),
    "in-modal": _ctx.inModal,
    "has-action": _ctx.hasInstalledAction
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_DownloadButton, {
      key: 0,
      plugin: _ctx.plugin,
      "show-as-button": !_ctx.inModal,
      "is-auto-update-possible": _ctx.isAutoUpdatePossible
    }, null, 8, ["plugin", "show-as-button", "is-auto-update-possible"])) : !_ctx.plugin.isInvalid && !_ctx.isMultiServerEnvironment && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [_ctx.plugin.isActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'btn btn-block': !_ctx.inModal
      }),
      href: _ctx.linkToDeactivate(_ctx.plugin.name)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Deactivate')), 11, CTAContainervue_type_template_id_4cc60a53_hoisted_3)) : _ctx.plugin.missingRequirements.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ")], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 2,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'btn btn-block': !_ctx.inModal
      }),
      href: _ctx.linkToActivate(_ctx.plugin.name)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activate')), 11, CTAContainervue_type_template_id_4cc60a53_hoisted_4))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["label", "in-modal", "has-action"])) : _ctx.plugin.isEligibleForFreeTrial && !_ctx.inModal && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 5,
    type: "button",
    class: "btn btn-block purchaseable",
    title: _ctx.translate('Marketplace_StartFreeTrial'),
    onClick: _cache[3] || (_cache[3] = $event => _ctx.$emit('openDetailsModal'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_StartFreeTrial')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_5)) : _ctx.plugin.isEligibleForFreeTrial && _ctx.inModal && _ctx.shopVariationUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 6,
    class: "btn btn-block addToCartLink",
    target: "_blank",
    title: _ctx.translate('Marketplace_ClickToCompletePurchase'),
    rel: "noreferrer noopener",
    href: _ctx.shopVariationUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AddToCart')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_6)) : !_ctx.inModal && !_ctx.plugin.isDownloadable && (_ctx.plugin.isPaid || _ctx.plugin.isNewBundle || _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 7,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[4] || (_cache[4] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CTAStatus, {
    key: 8,
    tone: "warning",
    label: _ctx.translate('Marketplace_CannotInstall'),
    "in-modal": _ctx.inModal,
    "has-action": !_ctx.inModal || _ctx.isDownloadableWithoutAutoUpdate
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
      key: 0,
      "show-as-button": true,
      label: _ctx.translate('General_MoreDetails'),
      onAction: _cache[5] || (_cache[5] = $event => _ctx.$emit('openDetailsModal'))
    }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
      plugin: _ctx.plugin,
      "show-as-button": !_ctx.inModal,
      "is-auto-update-possible": _ctx.isAutoUpdatePossible
    }, null, 8, ["plugin", "show-as-button", "is-auto-update-possible"])]),
    _: 1
  }, 8, ["label", "in-modal", "has-action"])) : _ctx.isPluginsAdminEnabled && _ctx.plugin.hasDownloadLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 9,
    href: _ctx.linkToInstall(_ctx.plugin.name),
    class: "btn btn-block"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ActionInstall')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_7)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 10
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[6] || (_cache[6] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64))], 64)) : _ctx.plugin.isTrialRequested ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "btn btn-block purchaseable disabled",
    href: "",
    title: _ctx.translate('Marketplace_TrialRequested')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialRequested')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_8)) : _ctx.plugin.canTrialBeRequested && !_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    class: "btn btn-block purchaseable",
    href: "",
    onClick: _cache[7] || (_cache[7] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      this.$emit('requestTrial');
    }, ["prevent"])),
    title: _ctx.translate('Marketplace_RequestTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrial')), 9, CTAContainervue_type_template_id_4cc60a53_hoisted_9)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[8] || (_cache[8] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64));
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=4cc60a53

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAStatus.vue?vue&type=template&id=5cd56404

const CTAStatusvue_type_template_id_5cd56404_hoisted_1 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};
const CTAStatusvue_type_template_id_5cd56404_hoisted_2 = {
  class: "ctaStatus__label"
};
const CTAStatusvue_type_template_id_5cd56404_hoisted_3 = {
  key: 0,
  class: "ctaStatus__action"
};
function CTAStatusvue_type_template_id_5cd56404_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["alert alert-no-background", _ctx.alertClass])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label) + " ", 1), _ctx.hasAction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAStatusvue_type_template_id_5cd56404_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("("), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default"), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 1,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["ctaStatus", _ctx.toneClass])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CTAStatusvue_type_template_id_5cd56404_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["ctaStatus__icon", _ctx.iconClass]),
    "aria-hidden": "true"
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label), 1)]), _ctx.hasAction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAStatusvue_type_template_id_5cd56404_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2));
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAStatus.vue?vue&type=template&id=5cd56404

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAStatus.vue?vue&type=script&lang=ts

/**
 * A plugin's non-actionable state - installed, cannot install, license missing - with whatever
 * action remains beside it.
 *
 * Two renderings: the modal keeps the alert banner it has always had, with the action in brackets;
 * a card draws one row, state then action, since the alert's padding and border will not fit.
 */
const ALERT_CLASSES = {
  success: 'alert-success',
  warning: 'alert-warning',
  danger: 'alert-danger'
};
const ICON_CLASSES = {
  success: 'icon-ok',
  warning: 'icon-warning',
  danger: 'icon-error'
};
/* harmony default export */ var CTAStatusvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /** One of success, warning or danger. */
    tone: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    inModal: {
      type: Boolean,
      required: true
    },
    /** The slot is a function either way, so the caller says, or the brackets render empty. */
    hasAction: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    alertClass() {
      var _ALERT_CLASSES$this$t;
      return (_ALERT_CLASSES$this$t = ALERT_CLASSES[this.tone]) !== null && _ALERT_CLASSES$this$t !== void 0 ? _ALERT_CLASSES$this$t : ALERT_CLASSES.warning;
    },
    iconClass() {
      var _ICON_CLASSES$this$to;
      return (_ICON_CLASSES$this$to = ICON_CLASSES[this.tone]) !== null && _ICON_CLASSES$this$to !== void 0 ? _ICON_CLASSES$this$to : ICON_CLASSES.warning;
    },
    toneClass() {
      return `ctaStatus--${this.tone}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAStatus.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAStatus.vue



CTAStatusvue_type_script_lang_ts.render = CTAStatusvue_type_template_id_5cd56404_render

/* harmony default export */ var CTAStatus = (CTAStatusvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=template&id=def25d5a

const DownloadButtonvue_type_template_id_def25d5a_hoisted_1 = {
  key: 0,
  onclick: "$(this).css('display', 'none')"
};
const DownloadButtonvue_type_template_id_def25d5a_hoisted_2 = ["href"];
function DownloadButtonvue_type_template_id_def25d5a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", DownloadButtonvue_type_template_id_def25d5a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['plugin-details', 'download', {
      'btn btn-block': _ctx.showAsButton
    }]),
    href: _ctx.linkTo({
      module: 'Marketplace',
      action: 'download',
      pluginName: _ctx.plugin.name,
      nonce: _ctx.plugin.downloadNonce
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 11, DownloadButtonvue_type_template_id_def25d5a_hoisted_2)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=template&id=def25d5a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts


/* harmony default export */ var DownloadButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    },
    /** Render as a button, for the card's action row, where a bare link reads as body text. */
    showAsButton: {
      type: Boolean,
      default: false
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true
    }
  },
  methods: {
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite
      }, params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue



DownloadButtonvue_type_script_lang_ts.render = DownloadButtonvue_type_template_id_def25d5a_render

/* harmony default export */ var DownloadButton = (DownloadButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=df89921e

const MoreDetailsActionvue_type_template_id_df89921e_hoisted_1 = ["title"];
function MoreDetailsActionvue_type_template_id_df89921e_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'btn btn-block': _ctx.showAsButton
    }),
    href: "",
    title: _ctx.translate('General_MoreDetails'),
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('action'), ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])($event => _ctx.$emit('action'), ["enter"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label ? _ctx.label : _ctx.translate('General_Help')), 43, MoreDetailsActionvue_type_template_id_df89921e_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=df89921e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=script&lang=ts

/* harmony default export */ var MoreDetailsActionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    showAsButton: {
      type: Boolean,
      required: false,
      default: false
    },
    label: {
      type: String,
      required: false
    }
  },
  emits: ['action']
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue



MoreDetailsActionvue_type_script_lang_ts.render = MoreDetailsActionvue_type_template_id_df89921e_render

/* harmony default export */ var MoreDetailsAction = (MoreDetailsActionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts





/* harmony default export */ var CTAContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    },
    activateNonce: {
      type: String,
      required: true
    },
    deactivateNonce: {
      type: String,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    },
    updateNonce: {
      type: String,
      required: true
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true
    },
    isValidConsumer: {
      type: Boolean,
      required: true
    },
    isMultiServerEnvironment: {
      type: Boolean,
      required: true
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true
    },
    isSuperUser: {
      type: Boolean,
      required: true
    },
    inModal: {
      type: Boolean,
      required: true
    },
    shopVariationUrl: {
      type: String,
      required: false,
      default: ''
    }
  },
  emits: ['openDetailsModal', 'requestTrial'],
  components: {
    CTAStatus: CTAStatus,
    DownloadButton: DownloadButton,
    MoreDetailsAction: MoreDetailsAction
  },
  computed: {
    /** The one case DownloadButton renders anything: an update it cannot apply for you. */
    isDownloadableWithoutAutoUpdate() {
      return this.plugin.missingRequirements.length === 0 && this.plugin.isDownloadable && !this.isAutoUpdatePossible;
    },
    /**
     * Whether the installed state offers anything beside the word "Installed". Both template
     * branches in one expression, because CTAStatus draws the brackets before rendering them.
     */
    hasInstalledAction() {
      return this.plugin.missingRequirements.length > 0 || !this.isAutoUpdatePossible || !this.plugin.isInvalid && !this.isMultiServerEnvironment && this.isPluginsAdminEnabled;
    }
  },
  methods: {
    linkToActivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'activate',
        redirectTo: 'referrer',
        referrer: this.marketplaceUrl(),
        nonce: this.activateNonce,
        pluginName
      });
    },
    linkToDeactivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        redirectTo: 'referrer',
        referrer: this.marketplaceUrl(),
        nonce: this.deactivateNonce,
        pluginName
      });
    },
    linkToInstall(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'installPlugin',
        referrer: this.marketplaceUrl(),
        nonce: this.installNonce,
        pluginName
      });
    },
    linkToUpdate(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'updatePlugin',
        referrer: this.marketplaceUrl(),
        nonce: this.updateNonce,
        pluginName
      });
    },
    /**
     * This page as the one to come back to, fragment and all: the Marketplace keeps its tab, search
     * and open plugin in the hash, which the Referer header these actions would otherwise return
     * to never carries. Built from MatomoUrl rather than read off window.location so the links
     * re-render as the hash changes, instead of keeping the one they were first drawn with.
     */
    marketplaceUrl() {
      const {
        origin,
        pathname
      } = window.location;
      const query = external_CoreHome_["MatomoUrl"].stringify(external_CoreHome_["MatomoUrl"].urlParsed.value);
      const hash = external_CoreHome_["MatomoUrl"].stringify(external_CoreHome_["MatomoUrl"].hashParsed.value);
      return `${origin}${pathname}?${query}${hash ? `#?${hash}` : ''}`;
    },
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite
      }, params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue



CTAContainervue_type_script_lang_ts.render = CTAContainervue_type_template_id_4cc60a53_render

/* harmony default export */ var CTAContainer = (CTAContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/MatomoGlyph.vue?vue&type=template&id=3fb0461a

const MatomoGlyphvue_type_template_id_3fb0461a_hoisted_1 = {
  xmlns: "http://www.w3.org/2000/svg",
  width: "18",
  height: "11",
  viewBox: "0 0 18 11",
  fill: "none",
  "aria-hidden": "true"
};
const MatomoGlyphvue_type_template_id_3fb0461a_hoisted_2 = ["d"];
function MatomoGlyphvue_type_template_id_3fb0461a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", MatomoGlyphvue_type_template_id_3fb0461a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    fill: "currentColor",
    d: _ctx.glyphPath
  }, null, 8, MatomoGlyphvue_type_template_id_3fb0461a_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/MatomoGlyph.vue?vue&type=template&id=3fb0461a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/MatomoGlyph.vue?vue&type=script&lang=ts

const glyphPath = 'M17.5957 6.39691L14.792 1.53157C13.557 -0.775318 9.9147 -0.348785 9.20844 2.14521C7.2643 -2.2726 1.18592 0.8693 3.73558 4.94346C1.85651 4.49998 -0.0243933 6.005 0.000239335 7.94318C-0.0346876 11.4162 5.01795 12.1463 5.99517 8.86439C7.17202 11.6685 10.8978 11.7602 11.9232 8.84855C13.7629 13.0583 19.5711 10.467 17.5957 6.39691ZM3.06829 10.0224C0.340685 9.97345 0.341053 5.91218 3.06829 5.86393C5.7959 5.91292 5.79553 9.97418 3.06829 10.0224ZM10.6044 6.57739C10.7121 6.76561 10.807 6.96157 10.882 7.16526C11.2265 8.10046 11.1614 9.0283 10.0357 9.72887C9.08087 10.3075 7.7382 9.9473 7.20033 8.96789L4.39662 4.10255C4.11941 3.6215 4.04588 3.06126 4.18926 2.5246C4.63191 0.756958 7.10841 0.430981 7.99151 2.02329C7.99151 2.02329 9.46175 4.5766 9.47756 4.60606L10.6044 6.57739ZM10.0603 3.0701C10.107 0.337424 14.1644 0.337792 14.2103 3.0701C14.1636 5.80279 10.1062 5.80242 10.0603 3.0701ZM16.9435 8.47211C16.6769 9.5576 15.4732 10.2541 14.4015 9.94251C13.8662 9.79886 13.4184 9.45483 13.1412 8.97378C12.9997 8.73105 11.5629 6.23337 11.4699 6.06983C13.0114 6.43669 14.6284 5.45544 15.0622 3.98984L16.7361 6.89453C17.0133 7.37558 17.0868 7.93545 16.9435 8.47211Z';
/* harmony default export */ var MatomoGlyphvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'MatomoGlyph',
  computed: {
    glyphPath() {
      return glyphPath;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/MatomoGlyph.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/MatomoGlyph.vue



MatomoGlyphvue_type_script_lang_ts.render = MatomoGlyphvue_type_template_id_3fb0461a_render

/* harmony default export */ var MatomoGlyph = (MatomoGlyphvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/constants.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * How long a Marketplace request may hang before the page calls it a failure.
 *
 * `AjaxHelper.send()` neither resolves nor rejects when the request never reaches the server
 * (`xhr.status === 0`), so without this a skeleton would sit there for good.
 */
const FETCH_TIMEOUT_MS = 30000;
/**
 * The one stand-in `Plugins::addPluginCoverImage()` falls back to for a plugin with no screenshot,
 * and the one a cover that fails to load is swapped for. It is line art on a white ground, so on a
 * dark page it needs the same inversion every other Matomo illustration gets - a real screenshot
 * must not be touched.
 */
const PLACEHOLDER_COVER = 'plugins/Marketplace/images/categories/uncategorised.png';
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/PluginCard.vue?vue&type=script&lang=ts







/* harmony default export */ var PluginCardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    },
    context: {
      type: Object,
      required: true
    }
  },
  components: {
    CTAContainer: CTAContainer,
    MatomoGlyph: MatomoGlyph
  },
  emits: ['openDetails', 'requestTrial'],
  data() {
    return {
      coverImageFailed: false
    };
  },
  watch: {
    'plugin.coverImage': function onCoverImageChange() {
      this.coverImageFailed = false;
    }
  },
  computed: {
    isByMatomo() {
      return isByMatomo(this.plugin);
    },
    ownerName() {
      return Object(external_CoreHome_["translate"])('Marketplace_ByAuthor', ownerLabel(this.plugin));
    },
    categoryLabel() {
      return chipLabel(this.plugin);
    },
    bundleSeatsLabel() {
      if (!this.plugin.isBundle || !this.plugin.bundleSeats) {
        return '';
      }
      return Object(external_CoreHome_["translate"])('Marketplace_BundleUpToXUsers', String(this.plugin.bundleSeats));
    },
    detailsHref() {
      return `#?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        showPlugin: this.plugin.name
      }))}`;
    },
    /** The plugin's own cover, or the stand-in once that has failed to load. */
    coverImage() {
      return this.coverImageFailed ? PLACEHOLDER_COVER : this.plugin.coverImage || '';
    },
    isPlaceholderCover() {
      return this.coverImage.endsWith(PLACEHOLDER_COVER);
    },
    coverImageSrcset() {
      return `${this.coverImageUrl(440, 240)} 440w, ${this.coverImageUrl(880, 480)} 880w`;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    coverImageUrl(width, height) {
      return `${this.coverImage}?w=${width}&h=${height}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCard.vue



PluginCardvue_type_script_lang_ts.render = PluginCardvue_type_template_id_797ec852_render

/* harmony default export */ var PluginCard = (PluginCardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/PluginCardSkeleton.vue?vue&type=template&id=21268c34

const PluginCardSkeletonvue_type_template_id_21268c34_hoisted_1 = {
  class: "pluginCardSkeleton",
  "aria-hidden": "true"
};
const PluginCardSkeletonvue_type_template_id_21268c34_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<div class=\"pluginCardSkeleton__plate\"></div><div class=\"pluginCardSkeleton__chipList\"><span class=\"pluginCardSkeleton__chipItem\"></span><span class=\"pluginCardSkeleton__chipItem pluginCardSkeleton__chipItem--short\"></span></div><div class=\"pluginCardSkeleton__title\"></div><div class=\"pluginCardSkeleton__textBlock\"><span class=\"pluginCardSkeleton__textLine\"></span><span class=\"pluginCardSkeleton__textLine\"></span><span class=\"pluginCardSkeleton__textLine pluginCardSkeleton__textLine--short\"></span></div><div class=\"pluginCardSkeleton__meta\"></div><div class=\"pluginCardSkeleton__action\"></div>", 6);
const PluginCardSkeletonvue_type_template_id_21268c34_hoisted_8 = [PluginCardSkeletonvue_type_template_id_21268c34_hoisted_2];
function PluginCardSkeletonvue_type_template_id_21268c34_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("article", PluginCardSkeletonvue_type_template_id_21268c34_hoisted_1, PluginCardSkeletonvue_type_template_id_21268c34_hoisted_8);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCardSkeleton.vue?vue&type=template&id=21268c34

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginCard/PluginCardSkeleton.vue?vue&type=script&lang=ts

/**
 * Holds a card's footprint while the catalogue loads. Its geometry has to match PluginCard, which
 * is why both read the `@marketplace-card-*` variables rather than one styling the other's DOM.
 */
/* harmony default export */ var PluginCardSkeletonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCardSkeleton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginCard/PluginCardSkeleton.vue



PluginCardSkeletonvue_type_script_lang_ts.render = PluginCardSkeletonvue_type_template_id_21268c34_render

/* harmony default export */ var PluginCardSkeleton = (PluginCardSkeletonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginGrid/PluginGrid.vue?vue&type=script&lang=ts



/* harmony default export */ var PluginGridvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    },
    /** At most this many cards, for a section showing one row; `null` renders every plugin. */
    maxCards: {
      type: Number,
      default: null
    },
    skeletonCount: {
      type: Number,
      default: 0
    },
    context: {
      type: Object,
      required: true
    }
  },
  components: {
    PluginCard: PluginCard,
    PluginCardSkeleton: PluginCardSkeleton
  },
  emits: ['openDetails', 'requestTrial'],
  computed: {
    visiblePlugins() {
      return this.maxCards === null ? this.plugins : this.plugins.slice(0, this.maxCards);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/PluginGrid.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/PluginGrid.vue



PluginGridvue_type_script_lang_ts.render = PluginGridvue_type_template_id_b4834194_render

/* harmony default export */ var PluginGrid = (PluginGridvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginSection/PluginSection.vue?vue&type=template&id=34e2078e

const PluginSectionvue_type_template_id_34e2078e_hoisted_1 = {
  class: "pluginSection"
};
const PluginSectionvue_type_template_id_34e2078e_hoisted_2 = {
  class: "pluginSection__header"
};
const PluginSectionvue_type_template_id_34e2078e_hoisted_3 = {
  class: "pluginSection__heading"
};
const PluginSectionvue_type_template_id_34e2078e_hoisted_4 = ["aria-label"];
const PluginSectionvue_type_template_id_34e2078e_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-right",
  "aria-hidden": "true"
}, null, -1);
function PluginSectionvue_type_template_id_34e2078e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_PluginGrid = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginGrid");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", PluginSectionvue_type_template_id_34e2078e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginSectionvue_type_template_id_34e2078e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", PluginSectionvue_type_template_id_34e2078e_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.heading), 1), _ctx.showSeeAll ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "pluginSection__seeAll",
    "aria-label": _ctx.seeAllAriaLabel,
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('seeAll', _ctx.sectionId))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.seeAllLabel), 1), PluginSectionvue_type_template_id_34e2078e_hoisted_5], 8, PluginSectionvue_type_template_id_34e2078e_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginGrid, {
    "max-cards": _ctx.maxCards,
    plugins: _ctx.plugins,
    context: _ctx.context,
    onOpenDetails: _cache[1] || (_cache[1] = $event => _ctx.$emit('openDetails', $event)),
    onRequestTrial: _cache[2] || (_cache[2] = $event => _ctx.$emit('requestTrial', $event))
  }, null, 8, ["max-cards", "plugins", "context"])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginSection/PluginSection.vue?vue&type=template&id=34e2078e

// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/visibleCardCount.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * How many cards a single-row section shows at the current width. One number decides both what is
 * rendered and whether "See all" is there, which a stylesheet hiding the overflow could not.
 *
 * Keep the counts in step with `.pluginGrid`'s columns; `visibleCardCount.spec.ts` pins them.
 */
/**
 * Narrowest first, first match wins: every max-width below the current width matches at once.
 *
 * Below three columns a section deliberately runs to two rows, or it would come down to a single
 * card, which is why 1280px reports four rather than two.
 */
const SINGLE_ROW_BREAKPOINTS = [{
  query: '(max-width: 760px)',
  cards: 2
}, {
  query: '(max-width: 1280px)',
  cards: 4
}, {
  query: '(max-width: 1520px)',
  cards: 3
}, {
  query: '(max-width: 1800px)',
  cards: 4
}];
/** The widest breakpoint's count, and so the most cards one row ever shows. */
const SINGLE_ROW_MAX_CARDS = 5;
function supportsMatchMedia() {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function';
}
/**
 * How many cards a single-row grid is showing right now. Answers with the widest count where
 * `matchMedia` is unavailable, so a caller renders a full row rather than nothing.
 */
function visibleCardCount() {
  if (!supportsMatchMedia()) {
    return SINGLE_ROW_MAX_CARDS;
  }
  const breakpoint = SINGLE_ROW_BREAKPOINTS.find(candidate => window.matchMedia(candidate.query).matches);
  return breakpoint ? breakpoint.cards : SINGLE_ROW_MAX_CARDS;
}
/**
 * Calls back whenever the answer changes, and returns the unsubscribe. Each subscriber opens its
 * own queries: `change` fires only on a crossing, and no module state outlives its section.
 */
function observeVisibleCardCount(onChange) {
  if (!supportsMatchMedia()) {
    return () => undefined;
  }
  const notify = () => onChange(visibleCardCount());
  const queries = SINGLE_ROW_BREAKPOINTS.map(breakpoint => {
    const query = window.matchMedia(breakpoint.query);
    query.addEventListener('change', notify);
    return query;
  });
  return () => queries.forEach(query => query.removeEventListener('change', notify));
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginSection/PluginSection.vue?vue&type=script&lang=ts





/* harmony default export */ var PluginSectionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /**
     * What this section lists: `bundles`, `themes`, a category slug, `other`, or a promotion slug.
     * Emitted with `seeAll`, which the page turns into the tab or the promotion list it names.
     */
    sectionId: {
      type: String,
      required: true
    },
    /** Mirrors `PluginTab.isCategory`, and decides how the heading resolves its label. */
    isCategory: {
      type: Boolean,
      default: false
    },
    /** Every plugin in the section; the row is cut to {@link visibleCards}, "See all" is not. */
    plugins: {
      type: Array,
      required: true
    },
    context: {
      type: Object,
      required: true
    }
  },
  components: {
    PluginGrid: PluginGrid
  },
  emits: ['openDetails', 'requestTrial', 'seeAll'],
  data() {
    return {
      visibleCards: SINGLE_ROW_MAX_CARDS,
      unobserve: null
    };
  },
  mounted() {
    this.visibleCards = visibleCardCount();
    this.unobserve = observeVisibleCardCount(count => {
      this.visibleCards = count;
    });
  },
  unmounted() {
    if (this.unobserve) {
      this.unobserve();
    }
  },
  computed: {
    heading() {
      return tabLabel({
        id: this.sectionId,
        isCategory: this.isCategory
      });
    },
    /**
     * Only when the row is leaving something out, measured against what this width shows rather
     * than a fixed threshold - the row runs from two to five cards depending on the breakpoint.
     */
    showSeeAll() {
      return this.plugins.length > this.visibleCards;
    },
    /** One row's worth; the rest is what "See all" opens. */
    maxCards() {
      return this.visibleCards;
    },
    seeAllLabel() {
      return Object(external_CoreHome_["translate"])('Marketplace_SeeAll');
    },
    seeAllAriaLabel() {
      return Object(external_CoreHome_["translate"])('Marketplace_SeeAllInCategory', this.heading);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"]
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginSection/PluginSection.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginSection/PluginSection.vue



PluginSectionvue_type_script_lang_ts.render = PluginSectionvue_type_template_id_34e2078e_render

/* harmony default export */ var PluginSection = (PluginSectionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginGrid/EmptyState.vue?vue&type=template&id=e9ec3158

const EmptyStatevue_type_template_id_e9ec3158_hoisted_1 = {
  class: "marketplaceEmptyState"
};
const EmptyStatevue_type_template_id_e9ec3158_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "marketplaceEmptyState__icon icon-search",
  "aria-hidden": "true"
}, null, -1);
const EmptyStatevue_type_template_id_e9ec3158_hoisted_3 = {
  class: "marketplaceEmptyState__message"
};
function EmptyStatevue_type_template_id_e9ec3158_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", EmptyStatevue_type_template_id_e9ec3158_hoisted_1, [EmptyStatevue_type_template_id_e9ec3158_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", EmptyStatevue_type_template_id_e9ec3158_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoPluginsFound')), 1), _ctx.canReset ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "marketplaceEmptyState__reset",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('reset'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.resetLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/EmptyState.vue?vue&type=template&id=e9ec3158

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginGrid/EmptyState.vue?vue&type=script&lang=ts


/* harmony default export */ var EmptyStatevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /** Whether anything was searched for. A category on its own is cleared, not "searched". */
    hasQuery: {
      type: Boolean,
      default: false
    },
    /**
     * Whether a filter is set at all. An empty catalogue renders this state with nothing filtered,
     * where a reset button would be offered for a state it cannot change.
     */
    canReset: {
      type: Boolean,
      default: true
    }
  },
  emits: ['reset'],
  computed: {
    resetLabel() {
      return this.hasQuery ? Object(external_CoreHome_["translate"])('Marketplace_ResetFilters') : Object(external_CoreHome_["translate"])('Marketplace_ShowAllPlugins');
    }
  },
  methods: {
    translate: external_CoreHome_["translate"]
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/EmptyState.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginGrid/EmptyState.vue



EmptyStatevue_type_script_lang_ts.render = EmptyStatevue_type_template_id_e9ec3158_render

/* harmony default export */ var EmptyState = (EmptyStatevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=template&id=24f4d644

const RequestTrialvue_type_template_id_24f4d644_hoisted_1 = {
  class: "ui-confirm",
  ref: "confirm"
};
const RequestTrialvue_type_template_id_24f4d644_hoisted_2 = ["value"];
const RequestTrialvue_type_template_id_24f4d644_hoisted_3 = ["value"];
function RequestTrialvue_type_template_id_24f4d644_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$plugin;
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RequestTrialvue_type_template_id_24f4d644_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrialConfirmTitle', (_ctx$plugin = _ctx.plugin) === null || _ctx$plugin === void 0 ? void 0 : _ctx$plugin.displayName)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrialConfirmEmailWarning')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, RequestTrialvue_type_template_id_24f4d644_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, RequestTrialvue_type_template_id_24f4d644_hoisted_3)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=template&id=24f4d644

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=script&lang=ts


/* harmony default export */ var RequestTrialvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:modelValue', 'trialRequested'],
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirm, {
        yes: () => {
          this.requestTrial(newValue);
        }
      }, {
        onCloseEnd: () => {
          this.$emit('update:modelValue', null);
        }
      });
    }
  },
  computed: {
    plugin() {
      return this.modelValue;
    }
  },
  methods: {
    requestTrial(plugin) {
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.requestTrial'
      }, {
        pluginName: plugin.name
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('Marketplace_RequestTrialSubmitted', plugin.displayName),
          context: 'success',
          id: 'requestTrialSuccess',
          placeat: '#notificationContainer',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
        this.$emit('trialRequested');
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue



RequestTrialvue_type_script_lang_ts.render = RequestTrialvue_type_template_id_24f4d644_render

/* harmony default export */ var RequestTrial = (RequestTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/PluginDetails.vue?vue&type=template&id=7c5c4acc

const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_1 = {
  ref: "root",
  class: "marketplacePluginDetails"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-left",
  "aria-hidden": "true"
}, null, -1);
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_3 = {
  key: 1,
  class: "marketplacePluginDetails__content"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_4 = {
  class: "alert alert-danger"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_5 = {
  key: 2,
  class: "marketplacePluginDetails__content"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_6 = {
  class: "marketplacePluginDetails__head"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_7 = {
  key: 0,
  class: "marketplacePluginDetails__cover"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_8 = ["src"];
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_9 = {
  class: "marketplacePluginDetails__info"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_10 = {
  key: 0,
  class: "marketplacePluginDetails__lede"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_11 = {
  class: "marketplacePluginDetails__facts"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_12 = {
  class: "marketplacePluginDetails__labels"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_13 = {
  key: 0,
  class: "marketplacePluginDetails__pill marketplacePluginDetails__pill--matomo"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_14 = {
  key: 1,
  class: "marketplacePluginDetails__pill"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_15 = {
  class: "marketplacePluginDetails__rating"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "marketplacePluginDetails__ratingStar",
  "aria-hidden": "true"
}, "★", -1);
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_17 = {
  key: 1,
  class: "marketplacePluginDetails__fact"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_18 = {
  key: 2,
  class: "marketplacePluginDetails__fact"
};
const PluginDetailsvue_type_template_id_7c5c4acc_hoisted_19 = {
  class: "marketplacePluginDetails__columns"
};
const _hoisted_20 = {
  class: "marketplacePluginDetails__main"
};
const _hoisted_21 = {
  class: "marketplacePluginDetails__alerts"
};
const _hoisted_22 = {
  key: 1,
  class: "alert alert-warning"
};
const _hoisted_23 = {
  key: 2,
  class: "alert alert-warning"
};
const _hoisted_24 = {
  key: 3,
  class: "alert alert-danger"
};
const _hoisted_25 = {
  key: 4,
  class: "alert alert-warning"
};
const _hoisted_26 = ["innerHTML"];
const _hoisted_27 = ["innerHTML"];
const _hoisted_28 = ["innerHTML"];
const _hoisted_29 = {
  key: 8,
  class: "alert alert-danger"
};
const _hoisted_30 = {
  class: "marketplacePluginDetails__cards"
};
const _hoisted_31 = {
  key: 0,
  class: "marketplacePluginDetails__card"
};
const _hoisted_32 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_33 = ["innerHTML"];
const _hoisted_34 = {
  key: 1,
  class: "marketplacePluginDetails__card"
};
const _hoisted_35 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_36 = {
  class: "marketplacePluginDetails__shots"
};
const _hoisted_37 = ["title", "onClick"];
const _hoisted_38 = ["src", "onError"];
const _hoisted_39 = {
  class: "marketplacePluginDetails__shotCaption"
};
const _hoisted_40 = {
  key: 2,
  class: "marketplacePluginDetails__card"
};
const _hoisted_41 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_42 = ["innerHTML"];
const _hoisted_43 = {
  key: 3,
  class: "marketplacePluginDetails__card"
};
const _hoisted_44 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_45 = ["innerHTML"];
const _hoisted_46 = {
  key: 4,
  class: "marketplacePluginDetails__card marketplacePluginDetails__reviews"
};
const _hoisted_47 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_48 = {
  class: "marketplacePluginDetails__reviewSummary"
};
const _hoisted_49 = {
  class: "marketplacePluginDetails__reviewScore"
};
const _hoisted_50 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "marketplacePluginDetails__ratingStar",
  "aria-hidden": "true"
}, "★", -1);
const _hoisted_51 = {
  key: 0,
  class: "marketplacePluginDetails__reviewCount"
};
const _hoisted_52 = ["title", "src"];
const _hoisted_53 = {
  class: "marketplacePluginDetails__aside"
};
const _hoisted_54 = {
  key: 0,
  class: "marketplacePluginDetails__freeSummary"
};
const _hoisted_55 = {
  class: "marketplacePluginDetails__free"
};
const _hoisted_56 = {
  key: 0,
  class: "marketplacePluginDetails__freeLicense"
};
const _hoisted_57 = {
  class: "marketplacePluginDetails__card"
};
const _hoisted_58 = {
  class: "marketplacePluginDetails__cardTitle"
};
const _hoisted_59 = {
  class: "marketplacePluginDetails__meta"
};
const _hoisted_60 = {
  key: 0,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_61 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_62 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_63 = {
  key: 1,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_64 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_65 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_66 = {
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_67 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_68 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_69 = ["href"];
const _hoisted_70 = ["href"];
const _hoisted_71 = {
  key: 2
};
const _hoisted_72 = {
  key: 3
};
const _hoisted_73 = {
  key: 0,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_74 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_75 = ["title"];
const _hoisted_76 = {
  key: 1,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_77 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_78 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_79 = ["href"];
const _hoisted_80 = ["href"];
const _hoisted_81 = ["href"];
const _hoisted_82 = {
  key: 2,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_83 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_84 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_85 = ["href"];
const _hoisted_86 = {
  key: 1
};
const _hoisted_87 = {
  key: 3,
  class: "marketplacePluginDetails__metaRow"
};
const _hoisted_88 = {
  class: "marketplacePluginDetails__metaLabel"
};
const _hoisted_89 = {
  class: "marketplacePluginDetails__metaValue"
};
const _hoisted_90 = {
  class: "marketplacePluginDetails__keywordList"
};
const _hoisted_91 = ["src", "alt"];
function PluginDetailsvue_type_template_id_7c5c4acc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginLatestVers, _ctx$pluginLatestVers2;
  const _component_PluginDetailsSkeleton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginDetailsSkeleton");
  const _component_MatomoGlyph = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoGlyph");
  const _component_MissingReqsNotice = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MissingReqsNotice");
  const _component_ShopPricing = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShopPricing");
  const _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");
  const _component_MatomoModal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoModal");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "marketplacePluginDetails__back",
    type: "button",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('back'))
  }, [PluginDetailsvue_type_template_id_7c5c4acc_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Mobile_NavigationBack')), 1)]), _ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginDetailsSkeleton, {
    key: 0
  })) : !_ctx.isKnownPlugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.fetchErrorMessage), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_6, [_ctx.plugin.coverImage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["marketplacePluginDetails__coverImage", {
      'marketplacePluginDetails__coverImage--placeholder': _ctx.isPlaceholderCover
    }]),
    src: `${_ctx.coverImage}?w=468&h=238`,
    alt: "",
    decoding: "async",
    onError: _cache[1] || (_cache[1] = $event => _ctx.coverImageFailed = true)
  }, null, 42, PluginDetailsvue_type_template_id_7c5c4acc_hoisted_8)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h1", {
    class: "marketplacePluginDetails__title",
    ref: "heading",
    tabindex: "-1"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.displayName || _ctx.plugin.name), 513), _ctx.plugin.description ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.description), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_12, [_ctx.isByMatomo ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoGlyph), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CategoryMatomo')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.categoryLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.categoryLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_15, [PluginDetailsvue_type_template_id_7c5c4acc_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginReviews.averageRating), 1)]), _ctx.reviewCountLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    class: "marketplacePluginDetails__reviewsLink",
    type: "button",
    onClick: _cache[2] || (_cache[2] = $event => _ctx.scrollElementIntoView('.marketplacePluginDetails__reviews'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.reviewCountLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx.plugin.numDownloads || 0) > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NumDownloads', String(_ctx.plugin.numDownloadsPretty))), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.lastUpdated && !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UpdatedOn', _ctx.plugin.lastUpdated)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsvue_type_template_id_7c5c4acc_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("main", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [_ctx.showMissingRequirementsNoticeIfApplicable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MissingReqsNotice, {
    key: 0,
    plugin: _ctx.plugin
  }, null, 8, ["plugin"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showDeploymentWarnings && _ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_MultiServerEnvironmentWarning')), 1)) : _ctx.showDeploymentWarnings && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AutoUpdateDisabledWarning', '\'[General]enable_auto_update=1\'', '\'config/config.ini.php\'')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showMissingLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseMissingDescription')), 1)) : _ctx.showExceededLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseExceededDescription')), 1)) : _ctx.plugin.licenseStatus === 'Pending' && !_ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 5,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getPendingLicenseHelpText(_ctx.plugin.displayName))
  }, null, 8, _hoisted_26)) : _ctx.plugin.licenseStatus === 'Cancelled' && !_ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 6,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getCancelledLicenseHelpText(_ctx.plugin.displayName))
  }, null, 8, _hoisted_27)) : !_ctx.plugin.hasDownloadLink && !_ctx.isMultiServerEnvironment && (_ctx.plugin.licenseStatus || !_ctx.plugin.isPaid) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 7,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getDownloadLinkMissingHelpText(_ctx.plugin.displayName))
  }, null, 8, _hoisted_28)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.fetchErrorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.fetchErrorMessage), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_30, [_ctx.pluginDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_32, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AboutThisPlugin')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "marketplacePluginDetails__readme",
    innerHTML: _ctx.$sanitize(_ctx.pluginDescription)
  }, null, 8, _hoisted_33)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginScreenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Screenshots')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_36, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginScreenshots, screenshot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("figure", {
      class: "marketplacePluginDetails__shot",
      key: `screenshot-${screenshot}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      class: "marketplacePluginDetails__shotButton",
      type: "button",
      title: _ctx.getScreenshotBaseName(screenshot),
      onClick: $event => _ctx.openLightbox(screenshot)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      class: "marketplacePluginDetails__shotImage",
      src: `${screenshot}?w=480`,
      alt: "",
      loading: "lazy",
      decoding: "async",
      onError: $event => _ctx.failedScreenshots.push(screenshot)
    }, null, 40, _hoisted_38)], 8, _hoisted_37), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("figcaption", _hoisted_39, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getScreenshotBaseName(screenshot)), 1)]);
  }), 128))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginDocumentation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_40, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_41, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Documentation')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "marketplacePluginDetails__readme",
    innerHTML: _ctx.$sanitize(_ctx.pluginDocumentation)
  }, null, 8, _hoisted_42)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginFaq ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_43, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_44, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "marketplacePluginDetails__readme",
    innerHTML: _ctx.$sanitize(_ctx.pluginFaq)
  }, null, 8, _hoisted_45)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_47, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_48, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", _hoisted_49, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginReviews.averageRating), 1), _hoisted_50, _ctx.reviewCountLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_51, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.reviewCountLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("iframe", {
    class: "marketplacePluginDetails__reviewFrame",
    title: _ctx.translate('Marketplace_Reviews'),
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.pluginReviews.height ? `height: ${_ctx.pluginReviews.height}px;` : ''),
    src: _ctx.pluginReviews.embedUrl
  }, null, 12, _hoisted_52)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("aside", _hoisted_53, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["marketplacePluginDetails__card marketplacePluginDetails__buy", {
      'marketplacePluginDetails__buy--highlighted': _ctx.showPricingCard
    }])
  }, [_ctx.showShopPricing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ShopPricing, {
    key: 0,
    plugin: _ctx.plugin,
    "num-users": _ctx.numUsers,
    "offers-free-trial": _ctx.plugin.isEligibleForFreeTrial || _ctx.plugin.isNewBundle,
    "use-period-tabs": _ctx.plugin.isNewBundle,
    stacked: true,
    prominent: _ctx.showPricingCard
  }, null, 8, ["plugin", "num-users", "offers-free-trial", "use-period-tabs", "prominent"])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.showFreeLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_54, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_55, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Free')), 1), _ctx.freeLicenseLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_56, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.freeLicenseLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
    "is-super-user": _ctx.isSuperUser,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "is-multi-server-environment": _ctx.isMultiServerEnvironment,
    "is-valid-consumer": _ctx.isValidConsumer,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "activate-nonce": _ctx.activateNonce,
    "deactivate-nonce": _ctx.deactivateNonce,
    "install-nonce": _ctx.installNonce,
    "update-nonce": _ctx.updateNonce,
    plugin: _ctx.plugin,
    "in-modal": true,
    "shop-variation-url": _ctx.selectedShopVariationUrl,
    onRequestTrial: _cache[3] || (_cache[3] = $event => _ctx.$emit('requestTrial', _ctx.plugin))
  }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "shop-variation-url"])], 64))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_57, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_58, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Details')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", _hoisted_59, [!_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_60, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_61, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_62, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.lastUpdated && !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_63, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_64, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LastUpdated')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_65, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.lastUpdated), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_66, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_67, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Developer')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_68, [_ctx.pluginAuthors.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginAuthors, (author, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: `author-${index}`
    }, [author.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      class: "marketplacePluginDetails__metaLink",
      target: "_blank",
      rel: "noreferrer noopener",
      href: author.homepage
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, _hoisted_69)) : author.email && _ctx.isValidEmail(author.email) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      class: "marketplacePluginDetails__metaLink",
      href: `mailto:${encodeURIComponent(author.email)}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, _hoisted_70)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_71, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 1)), index < _ctx.pluginAuthors.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_72, ", ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
  }), 128)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginOwner), 1)], 64))])]), _ctx.requiredMatomoVersion ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_73, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_74, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Requires')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", {
    class: "marketplacePluginDetails__metaValue",
    title: _ctx.requiredMatomoConstraint
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequiresMatomoVersion', _ctx.requiredMatomoVersion)), 9, _hoisted_75)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginHomepage || _ctx.pluginChangelogUrl || _ctx.pluginRepositoryUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_76, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_77, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Websites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_78, [_ctx.pluginHomepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "marketplacePluginDetails__metaLink",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.pluginHomepage
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginWebsite')), 9, _hoisted_79)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginChangelogUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.pluginHomepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" · ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "marketplacePluginDetails__metaLink",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.pluginChangelogUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Changelog')), 9, _hoisted_80)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginRepositoryUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [_ctx.pluginHomepage || _ctx.pluginChangelogUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" · ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "marketplacePluginDetails__metaLink",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.pluginRepositoryUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Source')), 9, _hoisted_81)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showLicenseName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_82, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_83, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_License')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_84, [_ctx.pluginLicenseUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "marketplacePluginDetails__metaLink",
    rel: "noreferrer noopener",
    href: _ctx.pluginLicenseUrl,
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers === void 0 ? void 0 : _ctx$pluginLatestVers.name), 9, _hoisted_85)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_86, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers2 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers2 === void 0 ? void 0 : _ctx$pluginLatestVers2.name), 1))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginKeywords.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_87, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", _hoisted_88, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginKeywords')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", _hoisted_89, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_90, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginKeywords, keyword => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      class: "marketplacePluginDetails__keywordItem",
      key: `keyword-${keyword}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(keyword), 1);
  }), 128))])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])])])])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoModal, {
    modelValue: _ctx.lightboxOpen,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.lightboxOpen = $event),
    classes: "marketplacePluginDetails__lightbox",
    "aria-label": _ctx.lightboxCaption
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.lightboxScreenshot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
      key: 0,
      class: "marketplacePluginDetails__lightboxImage",
      src: _ctx.lightboxScreenshot,
      alt: _ctx.lightboxCaption
    }, null, 8, _hoisted_91)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["modelValue", "aria-label"])], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetails.vue?vue&type=template&id=7c5c4acc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/PluginDetailsSkeleton.vue?vue&type=template&id=59e4cb40

const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_1 = {
  class: "pluginDetailsSkeleton",
  "aria-hidden": "true"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<div class=\"pluginDetailsSkeleton__head\"><div class=\"pluginDetailsSkeleton__cover\"></div><div class=\"pluginDetailsSkeleton__info\"><div class=\"pluginDetailsSkeleton__title\"></div><div class=\"pluginDetailsSkeleton__textBlock\"><span class=\"pluginDetailsSkeleton__textLine\"></span><span class=\"pluginDetailsSkeleton__textLine pluginDetailsSkeleton__textLine--short\"></span></div><div class=\"pluginDetailsSkeleton__facts\"><span class=\"pluginDetailsSkeleton__chipItem\"></span><span class=\"pluginDetailsSkeleton__chipItem pluginDetailsSkeleton__chipItem--short\"></span><span class=\"pluginDetailsSkeleton__fact\"></span><span class=\"pluginDetailsSkeleton__fact\"></span></div></div></div>", 1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_3 = {
  class: "pluginDetailsSkeleton__columns"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_4 = {
  class: "pluginDetailsSkeleton__main"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_5 = {
  class: "pluginDetailsSkeleton__card"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__cardTitle"
}, null, -1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_7 = {
  class: "pluginDetailsSkeleton__textBlock"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "pluginDetailsSkeleton__textLine pluginDetailsSkeleton__textLine--short"
}, null, -1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_9 = {
  class: "pluginDetailsSkeleton__card"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__cardTitle"
}, null, -1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_11 = {
  class: "pluginDetailsSkeleton__shots"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_12 = {
  class: "pluginDetailsSkeleton__aside"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__card pluginDetailsSkeleton__buy"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__price"
}), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__action"
})], -1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_14 = {
  class: "pluginDetailsSkeleton__card"
};
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "pluginDetailsSkeleton__cardTitle"
}, null, -1);
const PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_16 = {
  class: "pluginDetailsSkeleton__meta"
};
function PluginDetailsSkeletonvue_type_template_id_59e4cb40_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_1, [PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_5, [PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_7, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(6, line => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "pluginDetailsSkeleton__textLine",
      key: `line-${line}`
    });
  }), 64)), PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_8])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_9, [PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_11, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(3, shot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "pluginDetailsSkeleton__shot",
      key: `shot-${shot}`
    });
  }), 64))])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_12, [PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_14, [PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsSkeletonvue_type_template_id_59e4cb40_hoisted_16, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(5, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "pluginDetailsSkeleton__metaRow",
      key: `row-${row}`
    });
  }), 64))])])])])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetailsSkeleton.vue?vue&type=template&id=59e4cb40

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/PluginDetailsSkeleton.vue?vue&type=script&lang=ts

/**
 * Holds the plugin page's shape while its details are fetched. Its geometry has to match
 * PluginDetails, which is why both read the `@marketplace-details-*` variables rather than one
 * styling the other's DOM - the same arrangement PluginCard and PluginCardSkeleton use.
 */
/* harmony default export */ var PluginDetailsSkeletonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetailsSkeleton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetailsSkeleton.vue



PluginDetailsSkeletonvue_type_script_lang_ts.render = PluginDetailsSkeletonvue_type_template_id_59e4cb40_render

/* harmony default export */ var PluginDetailsSkeleton = (PluginDetailsSkeletonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/ShopPricing.vue?vue&type=template&id=4f26f544

const ShopPricingvue_type_template_id_4f26f544_hoisted_1 = ["aria-label"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_2 = ["name", "checked"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_3 = {
  class: "shopPricing__periodText"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_4 = {
  key: 0,
  class: "shopPricing__freeMonths"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_5 = ["name", "checked"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_6 = {
  class: "shopPricing__periodText"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_7 = {
  key: 1,
  class: "shopPricing__tierField"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_8 = {
  class: "shopPricing__tierLabel"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_9 = ["value"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_10 = ["value"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_11 = ["aria-label", "value"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_12 = ["value"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_13 = ["title"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_14 = ["innerHTML"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_15 = {
  key: 1,
  class: "shopPricing__amount"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_16 = {
  class: "shopPricing__amountValue"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_17 = {
  class: "shopPricing__amountPeriod"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_18 = ["innerHTML"];
const ShopPricingvue_type_template_id_4f26f544_hoisted_19 = {
  key: 3,
  class: "shopPricing__featureList"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_20 = {
  key: 0,
  class: "shopPricing__featureItem"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "shopPricing__featureCheck",
  "aria-hidden": "true"
}, "✓", -1);
const ShopPricingvue_type_template_id_4f26f544_hoisted_22 = {
  class: "shopPricing__featureItem"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "shopPricing__featureCheck",
  "aria-hidden": "true"
}, "✓", -1);
const ShopPricingvue_type_template_id_4f26f544_hoisted_24 = {
  class: "shopPricing__cta"
};
const ShopPricingvue_type_template_id_4f26f544_hoisted_25 = ["title", "href"];
function ShopPricingvue_type_template_id_4f26f544_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.selectedVariation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["shopPricing", {
      'shopPricing--stacked': _ctx.stacked,
      'shopPricing--prominent': _ctx.prominent
    }])
  }, [_ctx.hasBothPeriods ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "shopPricing__periods",
    role: "radiogroup",
    "aria-label": _ctx.translate('Marketplace_BillingPeriod')
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["shopPricing__period", {
      'shopPricing__period--selected': _ctx.selectedPeriod === _ctx.PERIOD_ANNUAL
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "shopPricing__periodInput",
    type: "radio",
    name: _ctx.periodGroupName,
    checked: _ctx.selectedPeriod === _ctx.PERIOD_ANNUAL,
    onChange: _cache[0] || (_cache[0] = $event => _ctx.selectPeriod(_ctx.PERIOD_ANNUAL))
  }, null, 40, ShopPricingvue_type_template_id_4f26f544_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PayAnnually')), 1), _ctx.freeMonthsLabel ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.freeMonthsLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["shopPricing__period", {
      'shopPricing__period--selected': _ctx.selectedPeriod === _ctx.PERIOD_MONTHLY
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "shopPricing__periodInput",
    type: "radio",
    name: _ctx.periodGroupName,
    checked: _ctx.selectedPeriod === _ctx.PERIOD_MONTHLY,
    onChange: _cache[1] || (_cache[1] = $event => _ctx.selectPeriod(_ctx.PERIOD_MONTHLY))
  }, null, 40, ShopPricingvue_type_template_id_4f26f544_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PayMonthly')), 1)], 2)], 8, ShopPricingvue_type_template_id_4f26f544_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.tiers.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", ShopPricingvue_type_template_id_4f26f544_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SelectUsers')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "shopPricing__tier",
    value: _ctx.selectedTier,
    onChange: _cache[2] || (_cache[2] = $event => _ctx.selectTier($event))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.tiers, tier => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: tier,
      value: tier
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(tier), 9, ShopPricingvue_type_template_id_4f26f544_hoisted_10);
  }), 128))], 40, ShopPricingvue_type_template_id_4f26f544_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currencies.length > 2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("select", {
    key: 2,
    class: "shopPricing__currency",
    "aria-label": _ctx.translate('SitesManager_Currency'),
    value: _ctx.selectedCurrency,
    onChange: _cache[3] || (_cache[3] = $event => _ctx.selectCurrency($event))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.currencies, currency => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: currency,
      value: currency
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(currency), 9, ShopPricingvue_type_template_id_4f26f544_hoisted_12);
  }), 128))], 40, ShopPricingvue_type_template_id_4f26f544_hoisted_11)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "shopPricing__price",
    title: _ctx.priceTitle
  }, [_ctx.prominent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "shopPricing__amount",
    innerHTML: _ctx.$sanitize(_ctx.amountLabel)
  }, null, 8, ShopPricingvue_type_template_id_4f26f544_hoisted_14)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ShopPricingvue_type_template_id_4f26f544_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.prettyAmount), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ShopPricingvue_type_template_id_4f26f544_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.amountPeriod), 1)])), _ctx.billingNote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 2,
    class: "shopPricing__billing",
    innerHTML: _ctx.$sanitize(_ctx.billingNote)
  }, null, 8, ShopPricingvue_type_template_id_4f26f544_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, ShopPricingvue_type_template_id_4f26f544_hoisted_13), _ctx.prominent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ShopPricingvue_type_template_id_4f26f544_hoisted_19, [_ctx.selectedTier ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", ShopPricingvue_type_template_id_4f26f544_hoisted_20, [ShopPricingvue_type_template_id_4f26f544_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.selectedTier), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", ShopPricingvue_type_template_id_4f26f544_hoisted_22, [ShopPricingvue_type_template_id_4f26f544_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UnlimitedWebsites')), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ShopPricingvue_type_template_id_4f26f544_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn shopPricing__addToCart addToCartLink",
    target: "_blank",
    rel: "noreferrer noopener",
    title: _ctx.translate('Marketplace_ClickToCompletePurchase'),
    href: _ctx.selectedVariation.addToCartUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.ctaLabel), 9, ShopPricingvue_type_template_id_4f26f544_hoisted_25), _ctx.alternativeCurrency ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    class: "shopPricing__currencySwitch",
    type: "button",
    onClick: _cache[4] || (_cache[4] = $event => _ctx.currentCurrency = _ctx.alternativeCurrency)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SwitchToCurrency', _ctx.alternativeCurrency)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/ShopPricing.vue?vue&type=template&id=4f26f544

// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/shopPricing.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const PERIOD_ANNUAL = 'year';
const PERIOD_MONTHLY = 'month';
const MONTHS_PER_YEAR = 12;
/**
 * A shop variation is one purchasable combination of tier, currency and billing period.
 * Periods are reported as either an annual or a monthly wording, so both spellings are
 * accepted; anything else is a period we cannot price and is dropped.
 */
function toShopPeriod(period) {
  const normalised = String(period || '').toLowerCase();
  if (normalised.startsWith('month')) {
    return PERIOD_MONTHLY;
  }
  if (normalised.startsWith('year') || normalised.startsWith('annual')) {
    return PERIOD_ANNUAL;
  }
  return null;
}
/**
 * The amount a variation costs, or null when the shop did not give a usable one.
 *
 * price is typed string | number | null, and null, undefined and '' all pass through Number()
 * as 0 rather than failing — which would advertise a paid product as free while still linking
 * to the cart. Anything that is not a finite, non-negative number is treated as absent.
 */
function variationPrice(variation) {
  const raw = variation === null || variation === void 0 ? void 0 : variation.price;
  if (raw === null || raw === undefined || raw === '') {
    return null;
  }
  const price = Number(raw);
  return Number.isFinite(price) && price >= 0 ? price : null;
}
/**
 * The shop variations that can actually be offered: a known billing period, a currency to
 * price them in, a price to show and somewhere to buy them. Order is kept, the marketplace
 * lists the variation it considers the default first.
 */
function usableVariations(plugin) {
  var _plugin$shop;
  const variations = (plugin === null || plugin === void 0 || (_plugin$shop = plugin.shop) === null || _plugin$shop === void 0 ? void 0 : _plugin$shop.variations) || [];
  return variations.filter(variation => !!toShopPeriod(variation.period) && !!variation.currency && !!variation.addToCartUrl && variationPrice(variation) !== null);
}
/**
 * Whether there is enough shop information to show a price for this plugin.
 */
function hasShopPricing(plugin) {
  return usableVariations(plugin).length > 0;
}
// Only matches the English wording the shop uses today; a localised variation name would
// fall through and keep its period suffix, splitting the tier again.
const PERIOD_WORDING = /[\s/-]+(per\s+)?(month|months|monthly|year|years|yearly|annual|annually)$/i;
/**
 * The key a variation is grouped under in the tier picker.
 *
 * When the billing period gets its own control, the shop's period wording has to come out of
 * the name first — "Up to 20 users" and "Up to 20 users monthly" are one tier billed two ways,
 * and the period field already says which is which. Without that control the wording is the
 * only thing telling the two apart, so it stays part of the tier.
 */
function tierKey(variation, stripPeriodWording) {
  const name = String(variation.name || '');
  return (stripPeriodWording ? name.replace(PERIOD_WORDING, '') : name).trim();
}
/**
 * Distinct tiers on offer, in the order the marketplace sent them.
 */
function distinctTiers(variations, stripPeriodWording) {
  return variations.map(variation => tierKey(variation, stripPeriodWording)).filter((value, index, all) => !!value && all.indexOf(value) === index);
}
/**
 * Distinct currencies on offer, in the order the marketplace sent them.
 */
function distinctCurrencies(variations) {
  return variations.map(variation => variation.currency).filter((value, index, all) => !!value && all.indexOf(value) === index);
}
/**
 * Price of a variation expressed per month. An annual variation is billed in one go, so
 * its price is spread over the twelve months it covers.
 */
function monthlyAmount(variation, period) {
  const price = variationPrice(variation);
  if (price === null) {
    return 0;
  }
  return period === PERIOD_ANNUAL ? price / MONTHS_PER_YEAR : price;
}
/**
 * How much is saved over a year by paying annually instead of monthly. Returns 0 when
 * there is nothing to compare against or nothing to save.
 */
function annualSavings(annual, monthly) {
  const annualPrice = variationPrice(annual);
  const monthlyPrice = variationPrice(monthly);
  if (annualPrice === null || monthlyPrice === null) {
    return 0;
  }
  return Math.max(0, monthlyPrice * MONTHS_PER_YEAR - annualPrice);
}
/**
 * How many months of the annual plan are effectively free compared to paying monthly.
 */
function freeMonths(annual, monthly) {
  const monthlyPrice = variationPrice(monthly);
  const savings = annualSavings(annual, monthly);
  if (monthlyPrice === null || monthlyPrice <= 0 || savings <= 0) {
    return 0;
  }
  return Math.floor(savings / monthlyPrice);
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/ShopPricing.vue?vue&type=script&lang=ts



let nextPeriodGroupId = 0;
/**
 * A price normalised to a month rarely divides evenly, so allow decimals without forcing
 * them onto amounts that are already whole. An amount with cents shows both digits, as money
 * does: 43.80 rather than 43.8.
 */
function formatAmount(amount) {
  const rounded = Math.round(amount * 100) / 100;
  return external_CoreHome_["NumberFormatter"].formatNumber(rounded, 2, Number.isInteger(rounded) ? 0 : 2);
}
/* harmony default export */ var ShopPricingvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    },
    // the cart link is also where a trial starts, so it is named for the trial when there is one
    offersFreeTrial: {
      type: Boolean,
      default: false
    },
    // only bundles sold without a free trial offer a choice of billing period; everywhere else
    // the period is part of the tier and must not be lifted into its own control
    usePeriodTabs: {
      type: Boolean,
      default: false
    },
    /**
     * Lays the controls out in a column rather than a row.
     *
     * The block's own stacking is keyed off the viewport, which says nothing about the width it
     * was actually given: in the details page's sidebar it is narrow on the widest screen there
     * is. The parent owns that, so it is a prop rather than another media query.
     */
    stacked: {
      type: Boolean,
      default: false
    },
    /**
     * Styles the stacked panel as the pricing card on plugins.matomo.org: the price leads at a
     * larger size with its currency and period beside it, and a checklist of what the tier
     * includes sits above a full-width cart button.
     */
    prominent: {
      type: Boolean,
      default: false
    }
  },
  data() {
    // radios only group when they share a name, so keep it unique per instance
    nextPeriodGroupId += 1;
    return {
      currentTier: '',
      currentCurrency: '',
      currentPeriod: '',
      periodGroupName: `shopPricingPeriod${nextPeriodGroupId}`
    };
  },
  computed: {
    PERIOD_ANNUAL() {
      return PERIOD_ANNUAL;
    },
    PERIOD_MONTHLY() {
      return PERIOD_MONTHLY;
    },
    variations() {
      return usableVariations(this.plugin);
    },
    tiers() {
      return distinctTiers(this.variations, this.usePeriodTabs);
    },
    selectedTier() {
      return this.tiers.includes(this.currentTier) ? this.currentTier : this.tiers[0] || '';
    },
    tierVariations() {
      // a plugin priced as a single offer has no tier name to group by
      return this.tiers.length ? this.variations.filter(variation => tierKey(variation, this.usePeriodTabs) === this.selectedTier) : this.variations;
    },
    currencies() {
      return distinctCurrencies(this.tierVariations);
    },
    selectedCurrency() {
      // the marketplace lists its preferred currency first; its cheapest flag marks a price
      // point rather than a currency, so it is not a default to pick up here
      return this.currencies.includes(this.currentCurrency) ? this.currentCurrency : this.currencies[0] || '';
    },
    /**
     * The other currency when there are exactly two, which is offered as a one-click switch
     * rather than a select. Three or more still need the select to choose between.
     */
    alternativeCurrency() {
      if (this.currencies.length !== 2) {
        return '';
      }
      return this.currencies.find(currency => currency !== this.selectedCurrency) || '';
    },
    currencyVariations() {
      return this.tierVariations.filter(variation => variation.currency === this.selectedCurrency);
    },
    annualVariation() {
      return this.currencyVariations.find(variation => toShopPeriod(variation.period) === PERIOD_ANNUAL);
    },
    monthlyVariation() {
      return this.currencyVariations.find(variation => toShopPeriod(variation.period) === PERIOD_MONTHLY);
    },
    hasBothPeriods() {
      return this.usePeriodTabs && !!(this.annualVariation && this.monthlyVariation);
    },
    selectedPeriod() {
      if (this.currentPeriod === PERIOD_MONTHLY && this.monthlyVariation) {
        return PERIOD_MONTHLY;
      }
      return this.annualVariation ? PERIOD_ANNUAL : PERIOD_MONTHLY;
    },
    selectedVariation() {
      const variation = this.selectedPeriod === PERIOD_ANNUAL ? this.annualVariation : this.monthlyVariation;
      return variation || null;
    },
    prettyAmount() {
      var _variationPrice;
      if (!this.selectedVariation) {
        return '';
      }
      // with both billing periods on offer the two are only comparable per month, on their
      // own a price is clearest over the period it is actually billed for
      return formatAmount(this.hasBothPeriods ? monthlyAmount(this.selectedVariation, this.selectedPeriod) : (_variationPrice = variationPrice(this.selectedVariation)) !== null && _variationPrice !== void 0 ? _variationPrice : 0);
    },
    amountPeriod() {
      const perMonth = this.hasBothPeriods || this.selectedPeriod === PERIOD_MONTHLY;
      return Object(external_CoreHome_["translate"])(perMonth ? 'Marketplace_PerMonthWithCurrency' : 'Marketplace_PerYearWithCurrency', this.selectedCurrency);
    },
    /**
     * The price with its currency and period, as one sentence so translators can order the three.
     * The amount and currency arrive as spans so the price can outweigh the words around it.
     */
    amountLabel() {
      const perMonth = this.hasBothPeriods || this.selectedPeriod === PERIOD_MONTHLY;
      return Object(external_CoreHome_["translate"])(perMonth ? 'Marketplace_PricePerMonth' : 'Marketplace_PricePerYear', `<span class="shopPricing__amountValue">${this.prettyAmount}</span>`, `<span class="shopPricing__amountCurrency">${this.selectedCurrency}</span>`);
    },
    numFreeMonths() {
      return freeMonths(this.annualVariation, this.monthlyVariation);
    },
    freeMonthsLabel() {
      if (this.numFreeMonths <= 0) {
        return '';
      }
      return this.numFreeMonths === 1 ? Object(external_CoreHome_["translate"])('Marketplace_OneMonthFree') : Object(external_CoreHome_["translate"])('Marketplace_XMonthsFree', this.numFreeMonths);
    },
    ctaLabel() {
      return this.offersFreeTrial ? Object(external_CoreHome_["translate"])('Marketplace_StartFree30DayTrial') : Object(external_CoreHome_["translate"])('Marketplace_AddToCart');
    },
    /**
     * How the price on screen is billed, with what paying annually saves. Both periods have a
     * note, so switching between them does not change the panel's height.
     */
    billingNote() {
      var _variationPrice2;
      if (!this.hasBothPeriods) {
        return '';
      }
      const savings = annualSavings(this.annualVariation, this.monthlyVariation);
      const prettySavings = `<strong>${formatAmount(savings)} ${this.selectedCurrency}</strong>`;
      if (this.selectedPeriod === PERIOD_MONTHLY) {
        return savings > 0 ? Object(external_CoreHome_["translate"])('Marketplace_BilledMonthlyWithSavings', prettySavings) : Object(external_CoreHome_["translate"])('Marketplace_BilledMonthly');
      }
      // formatted here rather than taken from the shop's prettyPrice, which puts the
      // currency in front of an unseparated amount and would not match the price above
      const total = `${formatAmount((_variationPrice2 = variationPrice(this.annualVariation)) !== null && _variationPrice2 !== void 0 ? _variationPrice2 : 0)} ` + `${this.selectedCurrency}`;
      if (savings <= 0) {
        return Object(external_CoreHome_["translate"])('Marketplace_BilledAnnually', `<strong>${total}</strong>`);
      }
      return Object(external_CoreHome_["translate"])('Marketplace_BilledAnnuallyWithSavings', `<strong>${total}</strong>`, prettySavings);
    },
    priceTitle() {
      return `${Object(external_CoreHome_["translate"])('Marketplace_ShownPriceIsExclTax')} ` + `${Object(external_CoreHome_["translate"])('Marketplace_CurrentNumPiwikUsers', this.numUsers)}`;
    }
  },
  methods: {
    selectTier(event) {
      this.currentTier = event.target.value;
    },
    selectCurrency(event) {
      this.currentCurrency = event.target.value;
    },
    selectPeriod(period) {
      this.currentPeriod = period;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/ShopPricing.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/ShopPricing.vue



ShopPricingvue_type_script_lang_ts.render = ShopPricingvue_type_template_id_4f26f544_render

/* harmony default export */ var ShopPricing = (ShopPricingvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=8508486a

function MissingReqsNoticevue_type_template_id_8508486a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugin.missingRequirements || [], (req, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index,
      class: "alert alert-danger"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_MissingRequirementsNotice', _ctx.requirement(req.requirement), req.actualVersion, req.requiredVersion)), 1);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=8508486a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts

/* harmony default export */ var MissingReqsNoticevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    }
  },
  methods: {
    requirement(req) {
      if (req === 'php') {
        return 'PHP';
      }
      return `${req[0].toUpperCase()}${req.substr(1)}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue



MissingReqsNoticevue_type_script_lang_ts.render = MissingReqsNoticevue_type_template_id_8508486a_render

/* harmony default export */ var MissingReqsNotice = (MissingReqsNoticevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetails/PluginDetails.vue?vue&type=script&lang=ts











/**
 * Licence names that are open source. The name is free text from the plugin's author, in more
 * than one spelling ("GPL v3+", "GPLv3+"), and a free plugin can still ship under a commercial
 * licence - so only these are called open source.
 */
const OPEN_SOURCE_LICENSE = /\b(?:[AL]?GPL|MIT|Apache|BSD|MPL)(?:v?\d|\b)/i;
/* harmony default export */ var PluginDetailsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    CTAContainer: CTAContainer,
    MatomoGlyph: MatomoGlyph,
    MatomoModal: external_CoreHome_["MatomoModal"],
    MissingReqsNotice: MissingReqsNotice,
    PluginDetailsSkeleton: PluginDetailsSkeleton,
    ShopPricing: ShopPricing
  },
  props: {
    /**
     * The card row for the plugin being shown. The details request fills in everything the
     * catalogue listing leaves out; see the `plugin` computed.
     */
    pluginCard: {
      type: Object,
      required: true
    },
    activateNonce: {
      type: String,
      required: true
    },
    deactivateNonce: {
      type: String,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    },
    updateNonce: {
      type: String,
      required: true
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true
    },
    isValidConsumer: {
      type: Boolean,
      required: true
    },
    isMultiServerEnvironment: {
      type: Boolean,
      required: true
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true
    },
    isSuperUser: {
      type: Boolean,
      required: true
    },
    hasSomeAdminAccess: {
      type: Boolean,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      isLoading: true,
      fetchedDetails: null,
      fetchAbortController: null,
      fetchTimeout: null,
      fetchErrorMessage: '',
      lightboxScreenshot: '',
      coverImageFailed: false,
      failedScreenshots: [],
      baseDocumentTitle: ''
    };
  },
  emits: ['back', 'requestTrial'],
  watch: {
    // the page is mounted per plugin, but the hash can name another one while it is open - a
    // second click from the plugin management table does exactly that
    'pluginCard.name': function onPluginChange() {
      this.coverImageFailed = false;
      this.failedScreenshots = [];
      this.fetchPluginDetails();
    },
    documentTitle(title) {
      document.title = title;
    },
    isLoading(newValue) {
      if (newValue === false) {
        this.focusHeading();
        this.applyExternalTarget();
        this.applyIframeResize();
      }
    }
  },
  mounted() {
    // Where the window ends up is the Marketplace page's business - it owns the scroll position on
    // both sides of the change between the two views. Focus follows once there is a heading to put
    // it on; see focusHeading(), since until the details arrive this is a skeleton.
    this.fetchPluginDetails();
    this.baseDocumentTitle = document.title;
  },
  unmounted() {
    // Left alone once something else has retitled the page: on the reporting page, the hash change
    // that closes this view has CoreHome title it for the category it returns to before we unmount.
    if (document.title === this.documentTitle) {
      document.title = this.baseDocumentTitle;
    }
    this.abortDetailsFetch();
    this.teardownIframeResize();
  },
  computed: {
    /**
     * Whether anything describes the plugin beyond its name. A deep link to a plugin the catalogue
     * does not carry, or one opened while the Marketplace API is unreachable, has only the name to
     * go on once its request fails, and the page is then the error alone: the buttons read
     * `missingRequirements`, which every row Plugins.php enriches carries and a bare name does not.
     */
    isKnownPlugin() {
      return !!this.fetchedDetails || Array.isArray(this.pluginCard.missingRequirements);
    },
    plugin() {
      // the plugin list only carries the fields its cards render, so everything else arrives from
      // getPluginDetails once the page opens
      return Object.assign(Object.assign({}, this.pluginCard), this.fetchedDetails || {});
    },
    pluginLatestVersion() {
      const versions = this.plugin.versions || [{}];
      return versions[versions.length - 1];
    },
    pluginReadmeHtml() {
      var _this$pluginLatestVer;
      return ((_this$pluginLatestVer = this.pluginLatestVersion) === null || _this$pluginLatestVer === void 0 ? void 0 : _this$pluginLatestVer.readmeHtml) || {};
    },
    pluginDescription() {
      var _this$pluginReadmeHtm;
      return ((_this$pluginReadmeHtm = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm === void 0 ? void 0 : _this$pluginReadmeHtm.description) || '';
    },
    pluginDocumentation() {
      var _this$pluginReadmeHtm2;
      return ((_this$pluginReadmeHtm2 = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm2 === void 0 ? void 0 : _this$pluginReadmeHtm2.documentation) || '';
    },
    pluginFaq() {
      var _this$pluginReadmeHtm3;
      return ((_this$pluginReadmeHtm3 = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm3 === void 0 ? void 0 : _this$pluginReadmeHtm3.faq) || '';
    },
    pluginShop() {
      return this.plugin.shop;
    },
    pluginShopVariations() {
      var _this$pluginShop;
      return ((_this$pluginShop = this.pluginShop) === null || _this$pluginShop === void 0 ? void 0 : _this$pluginShop.variations) || [];
    },
    pluginReviews() {
      var _this$pluginShop2;
      return ((_this$pluginShop2 = this.pluginShop) === null || _this$pluginShop2 === void 0 ? void 0 : _this$pluginShop2.reviews) || {};
    },
    pluginKeywords() {
      var _this$plugin;
      return ((_this$plugin = this.plugin) === null || _this$plugin === void 0 ? void 0 : _this$plugin.keywords) || [];
    },
    // the links below come from the plugin's own plugin.json, so only a safe scheme is linked
    pluginAuthors() {
      const authors = this.plugin.authors || [];
      return authors.filter(author => author.name).map(author => Object.assign(Object.assign({}, author), {}, {
        homepage: author.homepage ? this.$sanitizeUrl(author.homepage) : ''
      }));
    },
    pluginHomepage() {
      return this.plugin.homepage ? this.$sanitizeUrl(this.plugin.homepage) : '';
    },
    pluginChangelogUrl() {
      var _this$plugin$changelo;
      const url = ((_this$plugin$changelo = this.plugin.changelog) === null || _this$plugin$changelo === void 0 ? void 0 : _this$plugin$changelo.url) || '';
      return url ? Object(external_CoreHome_["externalRawLink"])(this.$sanitizeUrl(url)) : '';
    },
    pluginRepositoryUrl() {
      const url = this.plugin.repositoryUrl || '';
      return url ? Object(external_CoreHome_["externalRawLink"])(this.$sanitizeUrl(url)) : '';
    },
    pluginLicenseUrl() {
      var _this$pluginLatestVer2;
      const url = ((_this$pluginLatestVer2 = this.pluginLatestVersion) === null || _this$pluginLatestVer2 === void 0 || (_this$pluginLatestVer2 = _this$pluginLatestVer2.license) === null || _this$pluginLatestVer2 === void 0 ? void 0 : _this$pluginLatestVer2.url) || '';
      return url ? this.$sanitizeUrl(url) : '';
    },
    isByMatomo() {
      return isByMatomo(this.plugin);
    },
    pluginOwner() {
      return ownerLabel(this.plugin);
    },
    /**
     * The Matomo constraint the latest version declares, as written: `>=6.0.0-b1,<7.0.0-b1`.
     * Older plugins still name it `piwik`.
     */
    requiredMatomoConstraint() {
      var _this$pluginLatestVer3;
      const requires = ((_this$pluginLatestVer3 = this.pluginLatestVersion) === null || _this$pluginLatestVer3 === void 0 ? void 0 : _this$pluginLatestVer3.requires) || {};
      return requires.matomo || requires.piwik || '';
    },
    /**
     * The lowest Matomo the latest version runs on, cut to major.minor - "6.0" rather than
     * "6.0.0-b1", since the beta suffix only marks where the range opens. The upper bound is
     * left to the tooltip: it is always the next major, and "or newer" is what readers check.
     */
    requiredMatomoVersion() {
      const lowerBound = /(?:^|,)\s*>=\s*(\d+\.\d+)/.exec(this.requiredMatomoConstraint);
      return lowerBound ? lowerBound[1] : '';
    },
    showReviews() {
      return !!(this.pluginReviews && this.pluginReviews.embedUrl && this.pluginReviews.averageRating);
    },
    showMissingLicenseDescription() {
      return this.hasSomeAdminAccess && this.plugin.isMissingLicense;
    },
    showExceededLicenseDescription() {
      return this.hasSomeAdminAccess && this.plugin.hasExceededLicense;
    },
    showMissingRequirementsNoticeIfApplicable() {
      return this.isSuperUser && (this.plugin.isDownloadable || this.plugin.isInstalled);
    },
    showLicenseName() {
      var _this$pluginLatestVer4;
      const license = ((_this$pluginLatestVer4 = this.pluginLatestVersion) === null || _this$pluginLatestVer4 === void 0 ? void 0 : _this$pluginLatestVer4.license) || {};
      return !!license.name;
    },
    showDeploymentWarnings() {
      // both warnings tell you that you will have to download the plugin and deploy it yourself.
      // A bundle is a licence purchase with no download of its own — the plugins it covers are
      // installed individually afterwards — so neither warning is actionable for one.
      return !this.plugin.isBundle;
    },
    showShopPricing() {
      return this.isSuperUser && !this.plugin.isMissingLicense && !this.plugin.isInstalled && !this.plugin.hasExceededLicense && (this.plugin.isEligibleForFreeTrial || this.plugin.isNewBundle)
      // the variations come from the details request, so there are none to pick from when it
      // failed and the page is left with the card row alone
      && hasShopPricing(this.plugin);
    },
    /**
     * A plugin's price is shown as the pricing card on plugins.matomo.org. A bundle keeps the
     * panel it had, whose billing period toggle a plugin never shows.
     */
    showPricingCard() {
      return this.showShopPricing && !this.plugin.isNewBundle;
    },
    /** The same chip the plugin's card carries. */
    categoryLabel() {
      return chipLabel(this.plugin);
    },
    isPlaceholderCover() {
      return this.coverImage.endsWith(PLACEHOLDER_COVER);
    },
    /**
     * How many reviews the score is an average of, or nothing when the shop did not say.
     *
     * `reviewCount` is the written reviews the embed lists, which is what the reader is being
     * pointed at; `ratingCount` also counts scores left without one. Neither is guaranteed to be
     * in the response, so the label is dropped rather than shown as a zero.
     */
    reviewCountLabel() {
      var _this$pluginReviews$r, _this$pluginReviews;
      const count = Number((_this$pluginReviews$r = (_this$pluginReviews = this.pluginReviews) === null || _this$pluginReviews === void 0 ? void 0 : _this$pluginReviews.reviewCount) !== null && _this$pluginReviews$r !== void 0 ? _this$pluginReviews$r : 0);
      if (!Number.isFinite(count) || count < 1) {
        return '';
      }
      return count === 1 ? Object(external_CoreHome_["translate"])('Marketplace_OneReview') : Object(external_CoreHome_["translate"])('Marketplace_NumReviews', String(count));
    },
    /**
     * Whether the purchase panel says "Free" above its button. Only for a plugin that costs
     * nothing to begin with - one already paid for shows its status and its action alone.
     */
    showFreeLabel() {
      return !this.plugin.isPaid && !this.plugin.isInstalled && !this.plugin.isBundle;
    },
    /** The licence under "Free", called open source only when it is one. */
    freeLicenseLabel() {
      var _this$pluginLatestVer5;
      const name = ((_this$pluginLatestVer5 = this.pluginLatestVersion) === null || _this$pluginLatestVer5 === void 0 || (_this$pluginLatestVer5 = _this$pluginLatestVer5.license) === null || _this$pluginLatestVer5 === void 0 ? void 0 : _this$pluginLatestVer5.name) || '';
      if (!name || !OPEN_SOURCE_LICENSE.test(name)) {
        return name;
      }
      return Object(external_CoreHome_["translate"])('Marketplace_OpenSourceLicense', name);
    },
    lightboxOpen: {
      get() {
        return !!this.lightboxScreenshot;
      },
      set(open) {
        if (!open) {
          this.lightboxScreenshot = '';
        }
      }
    },
    lightboxCaption() {
      return this.lightboxScreenshot ? this.getScreenshotBaseName(this.lightboxScreenshot) : '';
    },
    /**
     * The plugin's name ahead of the page's own title, the way Matomo titles every page: most
     * specific first, each part joined with " - ".
     */
    documentTitle() {
      if (!this.baseDocumentTitle) {
        return '';
      }
      return `${this.plugin.displayName || this.plugin.name} - ${this.baseDocumentTitle}`;
    },
    /** The plugin's own cover, or the stand-in once that has failed to load. */
    coverImage() {
      return this.coverImageFailed ? PLACEHOLDER_COVER : this.plugin.coverImage || '';
    },
    /** A screenshot that fails to load is left out, rather than shown as an empty frame. */
    pluginScreenshots() {
      return (this.plugin.screenshots || []).filter(screenshot => !this.failedScreenshots.includes(screenshot));
    },
    pluginShopRecommendedVariation() {
      const recommendedVariations = this.pluginShopVariations.filter(v => v.recommended);
      const defaultVariation = this.pluginShopVariations.length ? this.pluginShopVariations[0] : null;
      return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
    },
    selectedShopVariationUrl() {
      var _this$pluginShopRecom;
      return ((_this$pluginShopRecom = this.pluginShopRecommendedVariation) === null || _this$pluginShopRecom === void 0 ? void 0 : _this$pluginShopRecom.addToCartUrl) || '';
    }
  },
  methods: {
    /**
     * Moves focus onto the plugin's name, off the card in the catalogue behind that no longer has
     * anything to do with what is on screen. Without `preventScroll` the browser would scroll the
     * heading into view itself and undo the jump to the top of the page.
     */
    focusHeading() {
      this.$nextTick(() => {
        const heading = this.$refs.heading;
        if (heading) {
          heading.focus({
            preventScroll: true
          });
        }
      });
    },
    /**
     * Opens the readme's own links in a new tab, as every other link off this page does.
     *
     * A readme is written for plugins.matomo.org, so a relative link in it resolves against this
     * Matomo instead and leads nowhere. Only absolute web and mail links, and anchors within the
     * readme, are kept; anything else is left as its text.
     */
    applyExternalTarget() {
      this.$nextTick(() => {
        var _root$querySelectorAl;
        const root = this.$refs.root;
        const links = (_root$querySelectorAl = root === null || root === void 0 ? void 0 : root.querySelectorAll('.marketplacePluginDetails__readme a[href]')) !== null && _root$querySelectorAl !== void 0 ? _root$querySelectorAl : [];
        links.forEach(link => {
          const href = (link.getAttribute('href') || '').trim();
          if (/^(https?:)?\/\//i.test(href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noreferrer noopener');
          } else if (!/^(mailto:|#)/i.test(href)) {
            link.replaceWith(...link.childNodes);
          }
        });
      });
    },
    scrollElementIntoView(selector) {
      this.$nextTick(() => {
        const root = this.$refs.root;
        const element = root === null || root === void 0 ? void 0 : root.querySelector(selector);
        if (element && typeof element.scrollIntoView === 'function') {
          element.scrollIntoView({
            block: 'nearest',
            behavior: 'smooth'
          });
        }
      });
    },
    isValidEmail(email) {
      // regex from https://stackoverflow.com/a/46181
      // eslint-disable-next-line max-len
      return email.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    getProtocolAndDomain(url) {
      const urlObj = new URL(url);
      return `${urlObj.protocol}//${urlObj.hostname}`;
    },
    reviewIframes() {
      var _root$querySelectorAl2;
      const root = this.$refs.root;
      return [...((_root$querySelectorAl2 = root === null || root === void 0 ? void 0 : root.querySelectorAll('.marketplacePluginDetails__reviewFrame')) !== null && _root$querySelectorAl2 !== void 0 ? _root$querySelectorAl2 : [])];
    },
    applyIframeResize() {
      this.$nextTick(() => {
        var _this$pluginReviews2;
        const {
          iFrameResize
        } = window;
        if (!((_this$pluginReviews2 = this.pluginReviews) !== null && _this$pluginReviews2 !== void 0 && _this$pluginReviews2.embedUrl) || !iFrameResize) {
          return;
        }
        const checkOrigin = [this.getProtocolAndDomain(this.pluginReviews.embedUrl)];
        this.reviewIframes().forEach(iframe => iFrameResize({
          checkOrigin
        }, iframe));
      });
    },
    /**
     * iframe-resizer attaches listeners to the window for each frame it is given. The page is
     * unmounted on every navigation back to the catalogue, so without this they accumulate.
     */
    teardownIframeResize() {
      this.reviewIframes().forEach(iframe => {
        const {
          iFrameResizer: resizer
        } = iframe;
        if (resizer) {
          resizer.close();
        }
      });
    },
    openLightbox(screenshot) {
      this.lightboxScreenshot = screenshot;
    },
    getScreenshotBaseName(screenshot) {
      const filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    abortDetailsFetch() {
      this.clearFetchTimeout();
      if (this.fetchAbortController) {
        this.fetchAbortController.abort();
        this.fetchAbortController = null;
      }
    },
    fetchPluginDetails() {
      var _this$pluginCard;
      const pluginName = (_this$pluginCard = this.pluginCard) === null || _this$pluginCard === void 0 ? void 0 : _this$pluginCard.name;
      if (!pluginName) {
        return;
      }
      this.abortDetailsFetch();
      this.isLoading = true;
      this.fetchErrorMessage = '';
      // details from the plugin opened before must not survive into this one, or a failed
      // request would show the new plugin's card data beside the old one's shop and versions
      this.fetchedDetails = null;
      const abortController = new AbortController();
      this.fetchAbortController = abortController;
      // A request that never reaches the server settles neither way in AjaxHelper, and the
      // skeleton would stay up for good - see FETCH_TIMEOUT_MS
      this.fetchTimeout = setTimeout(() => {
        this.fetchTimeout = null;
        if (this.fetchAbortController !== abortController) {
          return;
        }
        this.abortDetailsFetch();
        this.fetchErrorMessage = Object(external_CoreHome_["translate"])('Marketplace_PluginDetailsNotAvailable', pluginName);
        this.isLoading = false;
      }, FETCH_TIMEOUT_MS);
      external_CoreHome_["AjaxHelper"].post({
        module: 'Marketplace',
        action: 'getPluginDetails',
        format: 'JSON'
      }, {
        pluginName
      }, {
        withTokenInUrl: true,
        abortController,
        // the page renders the failure itself, in fetchErrorMessage
        createErrorNotification: false
      }).then(response => {
        if (this.fetchAbortController !== abortController) {
          return; // superseded, so this belongs to a plugin the user is no longer looking at
        }
        this.fetchedDetails = response;
      }).catch(response => {
        if (this.fetchAbortController !== abortController) {
          return;
        }
        this.fetchErrorMessage = (response === null || response === void 0 ? void 0 : response.message) || Object(external_CoreHome_["translate"])('General_ErrorRequest', '', '');
      }).finally(() => {
        if (this.fetchAbortController !== abortController) {
          return; // superseded or aborted, whoever replaced it owns the loading state
        }
        this.clearFetchTimeout();
        this.fetchAbortController = null;
        this.isLoading = false;
      });
    },
    clearFetchTimeout() {
      if (this.fetchTimeout) {
        clearTimeout(this.fetchTimeout);
        this.fetchTimeout = null;
      }
    },
    getPendingLicenseHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginLicenseStatusPending', pluginName, Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account/'), '</a>');
    },
    getCancelledLicenseHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginLicenseStatusCancelled', pluginName, Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account/'), '</a>');
    },
    getDownloadLinkMissingHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginDownloadLinkMissingDescription', pluginName, Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/plugins/faq_21/'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetails.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetails/PluginDetails.vue



PluginDetailsvue_type_script_lang_ts.render = PluginDetailsvue_type_template_id_7c5c4acc_render

/* harmony default export */ var PluginDetails = (PluginDetailsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts














/**
 * The hash parameter holding the open tab.
 *
 * Namespaced, and deliberately not `category`: the Marketplace is in the reporting menu as well as
 * the admin one, and on a reporting page `category` is CoreHome's own menu category. Writing that
 * one navigates the whole page away and unmounts this component; reading it hands back the
 * reporting category id, which matches no tab.
 */
const CATEGORY_PARAM = 'pluginCategory';
/**
 * The hash parameter holding the open promotion, if any. Its own parameter rather than a value of
 * {@link CATEGORY_PARAM}: a promotion is not a tab, and writing it there would leave the tab bar
 * looking for a tab that does not exist and highlighting none of them.
 */
const PROMOTION_PARAM = 'pluginPromotion';
/** How many cards a filtered view adds at a time. */
const PAGE_SIZE = 15;
/** Placeholder cards to hold the layout on a cold load. */
const INITIAL_SKELETONS = 10;
/**
 * How long each half of the change between the catalogue and a plugin's page takes.
 *
 * Kept in step with the transition on `.marketplacePage__views` in Marketplace.less: the fade is
 * CSS, but the swap in between has to wait for it, and nothing tells JavaScript when a class-driven
 * transition on a subtree has finished.
 */
const VIEW_FADE_MS = 220;
/** How long the fade between two categories' lists takes - see fadeInResults(). */
const LIST_FADE_MS = 280;
/** How long the search box waits after the last keystroke before writing the query to the hash. */
const QUERY_DEBOUNCE_MS = 250;
/**
 * Whether the page was opened from another Matomo page, such as the plugin management screen,
 * which leaving the details page should then return to. A bookmark, a new tab or an emailed link
 * has no referrer, and the Marketplace itself shares this page's query.
 */
function isOpenedFromAnotherMatomoPage() {
  if (!document.referrer || window.history.length < 2) {
    return false;
  }
  try {
    const referrer = new URL(document.referrer);
    return referrer.origin === window.location.origin && referrer.search !== window.location.search;
  } catch (e) {
    return false;
  }
}
/* harmony default export */ var Marketplacevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSort: {
      type: String,
      required: true
    },
    currentUserEmail: {
      type: String,
      default: ''
    },
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    hasSomeAdminAccess: Boolean,
    installNonce: {
      type: String,
      required: true
    },
    activateNonce: {
      type: String,
      required: true
    },
    deactivateNonce: {
      type: String,
      required: true
    },
    updateNonce: {
      type: String,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    },
    installAllPaidPluginsVisible: Boolean,
    installDisabled: Boolean
  },
  components: {
    InstallAllPaidPluginsButton: external_CorePluginsAdmin_["InstallAllPaidPluginsButton"],
    MarketplaceHero: MarketplaceHero,
    CategoryTabs: CategoryTabs,
    SortMenu: SortMenu,
    PluginGrid: PluginGrid,
    PluginSection: PluginSection,
    EmptyState: EmptyState,
    RequestTrial: RequestTrial,
    PluginDetails: PluginDetails
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  data() {
    return {
      loading: true,
      loadFailed: false,
      allPlugins: [],
      pluginSort: this.defaultSort || SORT_LAST_UPDATED,
      activeTab: TAB_ALL,
      activePromotion: '',
      searchQuery: '',
      pageSize: PAGE_SIZE,
      paginated: true,
      showRequestTrialForPlugin: null,
      previousScrollRestoration: null,
      viewPluginName: '',
      switching: false,
      viewSwitchTimeout: null,
      detailsEntryPushed: false,
      openedFromAnotherPage: false,
      returnToPlugin: '',
      returnScrollTop: 0,
      hasReturnScroll: false,
      observer: null,
      fetchAbortController: null,
      fetchTimeout: null,
      queryHashTimeout: null
    };
  },
  created() {
    // a page loaded on a plugin's URL starts there rather than fading into it from a catalogue
    // the reader never saw - set before the first render, or that render is the catalogue
    this.viewPluginName = this.selectedPluginName;
    this.openedFromAnotherPage = !!this.selectedPluginName && isOpenedFromAnotherMatomoPage();
  },
  mounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.mounted', {
      element: this.$refs.root
    });
    this.readStateFromHash();
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashParsed.value, () => this.readStateFromHash());
    this.takeOverScrollRestoration();
    this.fetchCatalogue();
    this.observeSentinel();
  },
  watch: {
    // what is on screen follows the name, but a step behind it - see switchView()
    selectedPluginName(name) {
      // only the plugin the page was opened on returns to the page that opened it
      this.openedFromAnotherPage = false;
      this.switchView(name);
    },
    // a change of category patches the list in place, then fades it in - see fadeInResults()
    listKey() {
      this.$nextTick(() => this.fadeInResults());
    },
    // where to come back to follows the row, which a cold deep link only gets once it lands
    selectedPlugin(plugin) {
      if (plugin) {
        this.prepareReturnToCard(plugin);
      }
    }
  },
  unmounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.unmounted', {
      element: this.$refs.root
    });
    if (this.observer) {
      this.observer.disconnect();
    }
    if (this.fetchAbortController) {
      this.fetchAbortController.abort();
    }
    if (this.fetchTimeout) {
      clearTimeout(this.fetchTimeout);
      this.fetchTimeout = null;
    }
    this.cancelQueryHashWrite();
    this.cancelViewSwitch();
    this.releaseScrollRestoration();
  },
  computed: {
    /**
     * The plugin the hash asks to show.
     *
     * The hash is the one source of truth for this and not a convenience over local state: the
     * plugin management screen opens a plugin here by writing `showPlugin` itself - see
     * CorePluginsAdmin's PluginName directive - so the parameter is set by callers this page does
     * not control. Keeping a second copy in `data` only creates two things that can disagree.
     *
     * Deliberately not resolved against the catalogue: what is shown must not wait on a listing
     * the details view does not need. A cold deep link would otherwise paint the grid, skeletons
     * and all, and replace it with the plugin a moment later.
     */
    selectedPluginName() {
      return external_CoreHome_["MatomoUrl"].hashParsed.value.showPlugin || '';
    },
    /**
     * The catalogue's row for that plugin, once there is one. Only the return trip needs it - see
     * prepareReturnToCard() - since the details request carries everything the view renders.
     */
    selectedPlugin() {
      var _this$allPlugins$find;
      if (!this.selectedPluginName) {
        return null;
      }
      return (_this$allPlugins$find = this.allPlugins.find(candidate => candidate.name === this.selectedPluginName)) !== null && _this$allPlugins$find !== void 0 ? _this$allPlugins$find : null;
    },
    /**
     * What the details view starts from. The card row where the catalogue has already been
     * fetched, so the name and description paint immediately; otherwise the name alone, which is
     * all its own request needs. Everything else arrives with that response.
     *
     * Keyed off the name being rendered rather than the one in the hash, so a page on its way out
     * keeps showing the plugin it was opened for until it has gone.
     */
    detailsCard() {
      const card = this.allPlugins.find(candidate => candidate.name === this.viewPluginName);
      return card !== null && card !== void 0 ? card : {
        name: this.viewPluginName
      };
    },
    tabs() {
      return buildTabs(this.allPlugins, tabLabel);
    },
    /**
     * The section stack, each row sorted the way the whole catalogue is, so that a row is the
     * first cards of the category it links to rather than a differently ordered sample. Bundles
     * are the exception on both counts - see sortTabPlugins().
     */
    sections() {
      // The promoted rows keep the order the Marketplace gave them - promoting a plugin is
      // pointless if the page's sort can move it to the end of the row - so only the stack below
      // them is re-sorted. Sorting is hidden on this view anyway; see showSort().
      return [...buildPromoSections(this.allPlugins), ...buildSections(this.allPlugins, tabLabel).map(section => Object.assign(Object.assign({}, section), {}, {
        plugins: sortTabPlugins(section.plugins, this.pluginSort, section.id)
      }))];
    },
    /**
     * Whether the page shows the section stack rather than one flat grid. Only on All plugins with
     * nothing searched for: a tab or a query asks for one list, and ten rows would bury it.
     *
     * Keyed off the catalogue rather than off `loading`: the first load has no plugins and so no
     * sections, and the flat grid holds the layout with its skeletons; but refresh() loads again
     * over a catalogue that is still there, and reading `loading` would collapse the stack to a
     * flat grid for the length of that request and then build it back.
     */
    showSections() {
      return this.activeTab === TAB_ALL && !this.activePromotion && !this.searchQuery.trim() && this.sections.length > 0;
    },
    /**
     * Whether the way out of a promotion's list is on screen. A search sets the promotion aside
     * rather than closing it - see filteredPlugins() - so there is nothing to go back from while
     * a query is typed.
     */
    showBackLink() {
      return !!this.activePromotion && !this.searchQuery.trim();
    },
    /** Which list is open, for the fade between categories - see fadeInResults(). */
    listKey() {
      return `${this.activeTab}|${this.activePromotion}`;
    },
    /** What every grid on the page needs to render a card, gathered once. */
    cardContext() {
      return {
        isSuperUser: this.isSuperUser,
        isPluginsAdminEnabled: this.isPluginsAdminEnabled,
        isMultiServerEnvironment: this.isMultiServerEnvironment,
        isValidConsumer: this.isValidConsumer,
        isAutoUpdatePossible: this.isAutoUpdatePossible,
        activateNonce: this.activateNonce,
        deactivateNonce: this.deactivateNonce,
        installNonce: this.installNonce,
        updateNonce: this.updateNonce
      };
    },
    /**
     * Whether resetFilters() has anything left to clear. An empty catalogue reaches the empty
     * state with nothing filtered, and a reset button there would do nothing when pressed.
     */
    hasActiveFilters() {
      return !!this.searchQuery.trim() || this.activeTab !== TAB_ALL;
    },
    filteredPlugins() {
      // A search spans the whole catalogue: the tab is dropped rather than intersected, so a
      // query typed while a category is open still finds everything. The tab itself is left set -
      // its row is hidden for the duration - so clearing the query returns to it. An open
      // promotion is set aside the same way, and comes back with the query cleared.
      if (this.activePromotion && !this.searchQuery.trim()) {
        // Sorted like any other list on this view, promotion order and all: the sort control is
        // on screen here, and a list that ignored it would look broken. The row on the overview
        // is the one that keeps the Marketplace's order - see buildPromoSections().
        return sortPlugins(promotedPlugins(this.allPlugins, this.activePromotion), this.pluginSort);
      }
      const tab = this.searchQuery.trim() ? TAB_ALL : this.activeTab;
      return sortTabPlugins(filterPlugins(this.allPlugins, tab, this.searchQuery), this.pluginSort, tab);
    },
    /**
     * A page at a time, but only while the sentinel can say the reader reached the bottom. Without
     * an IntersectionObserver nothing would ever ask for the next page, so the grid renders every
     * result instead of stopping at the first fifteen for good.
     */
    pagedPlugins() {
      if (!this.paginated) {
        return this.filteredPlugins;
      }
      return this.filteredPlugins.slice(0, this.pageSize);
    },
    /**
     * Skeletons stand in for a catalogue that is not there yet. A refresh() over one already on
     * screen shows the cards it has instead - see showSections().
     */
    skeletonCount() {
      return this.loading && this.allPlugins.length === 0 ? INITIAL_SKELETONS : 0;
    },
    /**
     * What the list below is: a search's result count, or the name of the open category. All
     * plugins names nothing, since its rows carry their own headings. A category names itself
     * while still loading - the label comes from the tab, so waiting would shift the skeletons.
     */
    resultsHeading() {
      if (this.searchQuery.trim()) {
        if (this.loading) {
          return '';
        }
        const found = this.filteredPlugins.length;
        return found === 1 ? Object(external_CoreHome_["translate"])('Marketplace_OneResultFoundFor', this.searchQuery) : Object(external_CoreHome_["translate"])('Marketplace_ResultsFoundFor', found, this.searchQuery);
      }
      if (this.activePromotion) {
        return tabLabel({
          id: this.activePromotion,
          isCategory: false
        });
      }
      if (this.activeTab === TAB_ALL) {
        return '';
      }
      const tab = this.tabs.find(candidate => candidate.id === this.activeTab);
      return tabLabel(tab !== null && tab !== void 0 ? tab : {
        id: this.activeTab,
        isCategory: !TYPE_TABS.includes(this.activeTab)
      });
    },
    /**
     * Sorting is offered over a single list only. The section stack is ten lists at once, each cut
     * to a row, so sorting there would change what the rows hold with no visible reordering.
     *
     * Bundles are left out for the same reason: they carry a seat order of their own - see
     * sortTabPlugins() - and a control that reordered nothing would read as a broken one. Only
     * while that tab is the list on screen, though: a search or a promotion sets the tab aside,
     * and what those show does sort.
     */
    showSort() {
      const showingBundles = this.activeTab === TAB_BUNDLES && !this.activePromotion && !this.searchQuery.trim();
      return !this.showSections && !showingBundles && this.filteredPlugins.length > 0;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    /**
     * The whole catalogue, in the two requests the Marketplace keeps warm.
     *
     * `Api\Client::getWarmedOverviewLists()` holds only ['plugins', ALL], ['plugins', PAID] and
     * ['themes', ALL] for 90 minutes. Anything varying query, sort or purchase type is a different
     * cache key and misses all three, so do not add a parameter here: nothing breaks loudly, every
     * tab click just becomes a cold catalogue download.
     */
    fetchCatalogue() {
      this.loading = true;
      this.loadFailed = false;
      if (this.fetchAbortController) {
        this.fetchAbortController.abort();
      }
      const abortController = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(new AbortController());
      this.fetchAbortController = abortController;
      const warmedRequest = themesOnly => external_CoreHome_["AjaxHelper"].post({
        module: 'Marketplace',
        action: 'searchPlugins',
        format: 'JSON'
      }, {
        query: '',
        sort: SORT_LAST_UPDATED,
        purchaseType: '',
        themesOnly
      }, {
        withTokenInUrl: true,
        abortController
      });
      // A request that never reaches the server settles neither way in AjaxHelper, so the pair is
      // raced against a timer rather than awaited on its own - see FETCH_TIMEOUT_MS.
      let timedOut = false;
      const givesUp = new Promise((resolve, reject) => {
        this.fetchTimeout = setTimeout(() => {
          timedOut = true;
          abortController.abort();
          reject(new Error('The Marketplace catalogue request timed out.'));
        }, FETCH_TIMEOUT_MS);
      });
      const timeoutHandle = this.fetchTimeout;
      return Promise.race([Promise.all([warmedRequest(false), warmedRequest(true)]), givesUp]).then(([plugins, themes]) => {
        // a fetch this component has already replaced must not write over the newer one's state
        if (this.fetchAbortController !== abortController) {
          return;
        }
        const merged = new Map();
        [].concat(plugins !== null && plugins !== void 0 ? plugins : [], themes !== null && themes !== void 0 ? themes : []).forEach(plugin => {
          if (plugin && plugin.name) {
            merged.set(plugin.name, plugin);
          }
        });
        this.allPlugins = Array.from(merged.values());
        this.loading = false;
      }).catch(() => {
        // a fetch this component itself replaced or abandoned leaves the page as it is - an
        // aborted request never settles in AjaxHelper, so a superseded fetch reaches here only
        // once its own timer fires, long after the fetch that replaced it has painted the page
        if (this.fetchAbortController !== abortController) {
          return;
        }
        // the timeout aborts too, and is the one abort that does mean the catalogue is gone
        if (abortController.signal.aborted && !timedOut) {
          return;
        }
        this.loading = false;
        this.loadFailed = true;
      }).finally(() => {
        if (timeoutHandle) {
          clearTimeout(timeoutHandle);
        }
        if (this.fetchTimeout === timeoutHandle) {
          this.fetchTimeout = null;
        }
        if (this.fetchAbortController === abortController) {
          this.fetchAbortController = null;
        }
      });
    },
    refresh() {
      this.fetchCatalogue().then(() => this.$emit('triggerUpdate'));
    },
    readStateFromHash() {
      const hash = external_CoreHome_["MatomoUrl"].hashParsed.value;
      const searchQuery = hash.query || '';
      const pluginSort = hash.sort || this.defaultSort || SORT_LAST_UPDATED;
      const category = hash[CATEGORY_PARAM] || '';
      const activeTab = category || tabFromLegacyPluginType(hash.pluginType || '') || TAB_ALL;
      const promotion = hash[PROMOTION_PARAM] || '';
      const activePromotion = isPromoSection(promotion) ? promotion : '';
      // Only a change to what is listed starts the list over. This runs on every hash write, and
      // some of them leave the list alone - opening and closing a plugin only moves `showPlugin` -
      // so resetting unconditionally would throw away however far the reader had scrolled.
      const listChanged = searchQuery !== this.searchQuery || pluginSort !== this.pluginSort || activeTab !== this.activeTab || activePromotion !== this.activePromotion;
      this.searchQuery = searchQuery;
      this.pluginSort = pluginSort;
      this.activeTab = activeTab;
      this.activePromotion = activePromotion;
      if (listChanged) {
        this.pageSize = PAGE_SIZE;
      }
    },
    updateHash(changes) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), changes));
    },
    /**
     * Writes the hash without leaving a history entry behind.
     *
     * MatomoUrl.updateHash() assigns window.location.hash, which pushes one. That is what we want
     * when opening a plugin - browser Back then closes it - but not when closing one the reader
     * never navigated to, which would otherwise need two Backs to escape. replaceState changes the
     * URL without notifying anyone, so the hashchange MatomoUrl listens for is raised by hand, the
     * way MatomoUrl itself does when the hash it is asked for is the one already set.
     */
    replaceHash(changes) {
      const oldURL = window.location.href;
      const params = Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), changes);
      window.history.replaceState(null, '', `#?${external_CoreHome_["MatomoUrl"].stringify(params)}`);
      window.dispatchEvent(new HashChangeEvent('hashchange', {
        newURL: window.location.href,
        oldURL
      }));
    },
    /**
     * Writes the query to the hash once the typing stops. Its own timer rather than CoreHome's
     * `debounce`, which hands back no way to call a pending write off - and resetFilters() has to,
     * or a write scheduled by the last keystroke lands after the reset and puts the query back.
     */
    pushQueryToHash(query) {
      this.cancelQueryHashWrite();
      this.queryHashTimeout = setTimeout(() => {
        this.queryHashTimeout = null;
        this.updateHash({
          query
        });
      }, QUERY_DEBOUNCE_MS);
    },
    cancelQueryHashWrite() {
      if (this.queryHashTimeout) {
        clearTimeout(this.queryHashTimeout);
        this.queryHashTimeout = null;
      }
    },
    updateQuery(query) {
      this.searchQuery = query;
      this.pageSize = PAGE_SIZE;
      this.pushQueryToHash(query);
    },
    /**
     * Sets the tab before writing it to the hash, the way updateQuery does. The hash round trip is
     * what normally feeds `activeTab` back, but `hashchange` only fires once the current task ends,
     * so anything reading the rendered tabs on the next tick - `seeAllInSection` below - would
     * otherwise still find the outgoing tab marked active and move focus onto it.
     */
    updateTab(tabId) {
      this.activeTab = tabId;
      // a tab and a promotion are two views of their own, so opening one closes the other
      this.activePromotion = '';
      // set here as well as in readStateFromHash(), which only resets what it sees change and is
      // handed a tab this has already applied
      this.pageSize = PAGE_SIZE;
      // Home is the default the hash is read back as, so it is dropped rather than written out
      this.updateHash({
        [CATEGORY_PARAM]: tabId === TAB_ALL ? null : tabId,
        [PROMOTION_PARAM]: null,
        pluginType: null
      });
    },
    /**
     * Sets the sort before writing it to the hash, for the reason given on updateTab(), and lets a
     * query the search box has typed but not yet written go first: updateHash() merges into the
     * hash as it stands, so writing during the debounce would put the outgoing query back and the
     * search box - bound to `searchQuery` - would revert until the pending write landed.
     */
    updateSort(sort) {
      this.cancelQueryHashWrite();
      this.pluginSort = sort;
      this.pageSize = PAGE_SIZE;
      this.updateHash({
        sort,
        query: this.searchQuery || null
      });
    },
    /**
     * Opens one section's category, as its tab would. Unlike a tab click this can fire most of a
     * screen down the page, so it also scrolls the new list into view and moves focus - the
     * button that had it is removed by the re-render, which would drop focus to <body>.
     */
    seeAllInSection(sectionId) {
      if (isPromoSection(sectionId)) {
        this.openPromotion(sectionId);
        return;
      }
      this.updateTab(sectionId);
      this.scrollIntoView(this.$refs.resultsBar);
      this.$nextTick(() => {
        const tabs = this.$refs.categoryTabs;
        if (tabs && tabs.focusActiveTab) {
          tabs.focusActiveTab();
        }
      });
    },
    /**
     * Opens a promotion's own list, the way seeAllInSection() opens a category's tab. Written to
     * the hash so the view survives a reload and the browser's Back leaves it, and focus moves to
     * the way out: the button that had it is removed by the re-render, and no tab is highlighted
     * for this view, so focus would otherwise drop to <body>.
     */
    openPromotion(sectionId) {
      this.activePromotion = sectionId;
      this.activeTab = TAB_ALL;
      this.pageSize = PAGE_SIZE;
      this.updateHash({
        [PROMOTION_PARAM]: sectionId,
        [CATEGORY_PARAM]: null,
        pluginType: null
      });
      this.scrollIntoView(this.$refs.resultsBar);
      this.$nextTick(() => {
        var _this$$refs$backLink;
        return (_this$$refs$backLink = this.$refs.backLink) === null || _this$$refs$backLink === void 0 ? void 0 : _this$$refs$backLink.focus();
      });
    },
    /** Leaves a promotion's list for the overview it was opened from. */
    closePromotion() {
      this.activePromotion = '';
      this.pageSize = PAGE_SIZE;
      this.updateHash({
        [PROMOTION_PARAM]: null
      });
    },
    /** Scrolls without animating for readers who have asked for less motion. */
    scrollIntoView(element) {
      if (element) {
        element.scrollIntoView({
          block: 'start',
          behavior: this.prefersReducedMotion() ? 'auto' : 'smooth'
        });
      }
    },
    /**
     * Sets the state before writing the hash, for the reason given on updateTab().
     *
     * `replace` is for the reset a deep link forces on the catalogue behind the details page: the
     * reader is not on the catalogue to see it happen, and an entry for it would sit between the
     * details page and wherever they actually came from.
     */
    resetFilters(replace = false) {
      this.cancelQueryHashWrite();
      this.searchQuery = '';
      this.activeTab = TAB_ALL;
      this.activePromotion = '';
      this.pageSize = PAGE_SIZE;
      const cleared = {
        query: null,
        [CATEGORY_PARAM]: null,
        [PROMOTION_PARAM]: null,
        pluginType: null
      };
      if (replace) {
        this.replaceHash(cleared);
        return;
      }
      this.updateHash(cleared);
    },
    /**
     * Stops the browser putting the scroll position back by itself.
     *
     * Leaving a plugin's page goes back through the history entry that opened it, and on a history
     * navigation the browser restores the offset it recorded for that entry - immediately, and
     * before the change between the two views has even begun. That is the jump this page spends
     * switchView() hiding, so it has to own the scroll position outright rather than share it.
     */
    takeOverScrollRestoration() {
      if (typeof window === 'undefined' || !('scrollRestoration' in window.history)) {
        return;
      }
      this.previousScrollRestoration = window.history.scrollRestoration;
      window.history.scrollRestoration = 'manual';
    },
    releaseScrollRestoration() {
      if (!this.previousScrollRestoration) {
        return;
      }
      window.history.scrollRestoration = this.previousScrollRestoration;
      this.previousScrollRestoration = null;
    },
    /**
     * Changes the view over: fade what is on screen out, swap the two and move the scroll position
     * while nothing is showing, then fade the new one in.
     *
     * Sequenced rather than cross-faded because the two views sit at different scroll offsets - a
     * plugin's page opens at the top, the catalogue comes back where the reader left it - so
     * dissolving one into the other showed the top of the grid against a page most of a screen
     * further down. Behind the blank, the jump is not there to be seen.
     */
    switchView(name) {
      this.cancelViewSwitch();
      if (this.prefersReducedMotion()) {
        this.applyView(name);
        return;
      }
      this.switching = true;
      this.viewSwitchTimeout = setTimeout(() => {
        this.viewSwitchTimeout = null;
        this.applyView(name);
        // the new view is mounted but still transparent; showing it in the same frame would
        // start its fade from wherever the outgoing one's had got to
        this.$nextTick(() => {
          this.switching = false;
        });
      }, VIEW_FADE_MS);
    },
    /** Renders the named plugin's page, or the catalogue, and puts the scroll position with it. */
    applyView(name) {
      this.viewPluginName = name;
      this.$nextTick(() => {
        if (name) {
          this.scrollDetailsIntoView();
          return;
        }
        this.restoreCataloguePosition();
      });
    },
    /**
     * Brings the top of a plugin's page into view, if it is not there already.
     *
     * Not a jump to the top every time: opening a card from near the top of the catalogue would
     * then move the page for no reason the reader can see. Hiding the catalogue collapses the
     * document, so the browser has usually clamped the offset down to something sensible by the
     * time this runs, and there is nothing left to do.
     */
    scrollDetailsIntoView() {
      const views = this.$refs.views;
      if (!views) {
        return;
      }
      const top = views.getBoundingClientRect().top + window.scrollY;
      if (window.scrollY <= top) {
        return;
      }
      // to the top of the document rather than to the page's own top: any other offset can stop
      // being reachable when the skeleton is replaced by content shorter than it, and the browser
      // pulling the reader back up at that point is a jump long after the change is over
      window.scrollTo({
        top: 0,
        behavior: 'auto'
      });
    },
    cancelViewSwitch() {
      if (this.viewSwitchTimeout) {
        clearTimeout(this.viewSwitchTimeout);
        this.viewSwitchTimeout = null;
      }
    },
    /**
     * Fades the results bar and the list in together, once the new category has been rendered.
     *
     * The bar is in it as well as the list: Home has no heading and no sort, so the bar collapses
     * there and opens up again on any other tab. Left out, it jumped the list by its own height at
     * the start of every change to or from Home, and only those. Search and sort leave the key
     * alone, so typing does not fade the list on every keystroke.
     *
     * Web Animations rather than a class, since restarting a CSS transition means taking the
     * class off and forcing a reflow. jsdom has no `animate`, hence the check.
     */
    fadeInResults() {
      const results = this.$refs.results;
      // behind a plugin's page the change is not on screen, and switchView() fades the way back
      if (!results || typeof results.animate !== 'function' || this.viewPluginName || this.prefersReducedMotion()) {
        return;
      }
      results.animate([{
        opacity: 0,
        transform: 'translateY(6px)'
      }, {
        opacity: 1,
        transform: 'none'
      }], {
        duration: LIST_FADE_MS,
        easing: 'ease-out'
      });
    },
    prefersReducedMotion() {
      return typeof window !== 'undefined' && typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    },
    /**
     * Opens a plugin's page. Pushes a history entry, so browser Back closes it again;
     * closeDetails() reuses that entry rather than adding a second one.
     */
    openDetails(plugin) {
      // a query typed but not yet written would otherwise land after this one and push a second
      // entry, this time with showPlugin already in it - see updateSort() for the same hazard
      this.cancelQueryHashWrite();
      this.returnScrollTop = window.scrollY;
      this.hasReturnScroll = true;
      this.returnToPlugin = plugin.name;
      this.detailsEntryPushed = true;
      this.updateHash({
        query: this.searchQuery || null,
        showPlugin: plugin.name
      });
    },
    /**
     * Leaves the details page. Goes back through the entry openDetails() pushed where there is one,
     * so the reader is not left with a forward entry pointing at the page they just dismissed. A
     * plugin opened from another Matomo page, such as the plugin management screen, goes back to
     * that page. One opened any other way - a bookmark, an emailed link - has nothing to go back
     * to, so its entry is replaced with the catalogue instead.
     */
    closeDetails() {
      if (this.detailsEntryPushed || this.openedFromAnotherPage) {
        this.detailsEntryPushed = false;
        this.openedFromAnotherPage = false;
        window.history.back();
        return;
      }
      this.replaceHash({
        showPlugin: null
      });
    },
    /**
     * Makes sure the catalogue behind the details page holds the card the reader will come back to.
     * Only has anything to do for a plugin opened by URL: one opened from a card was on screen by
     * definition, and openDetails() has already noted where.
     */
    prepareReturnToCard(plugin) {
      if (this.returnToPlugin === plugin.name) {
        return;
      }
      this.returnToPlugin = plugin.name;
      this.hasReturnScroll = false;
      if (!this.filteredPlugins.some(candidate => candidate.name === plugin.name)) {
        this.resetFilters(true);
      }
      // Being in the results is not enough to be on the page: the grid renders the first
      // `pageSize` of them and the rest wait on the sentinel, so a card ranked further down has no
      // element for restoreCataloguePosition() to find. Grow the page by whole pages until it
      // does. The section stack renders its own cards and pages nothing, so it is left alone.
      if (!this.showSections) {
        const position = this.filteredPlugins.findIndex(c => c.name === plugin.name) + 1;
        if (position > this.pageSize) {
          this.pageSize = Math.ceil(position / PAGE_SIZE) * PAGE_SIZE;
        }
      }
    },
    /**
     * Puts the reader back where they left the catalogue: the scroll position they opened the
     * plugin from, and focus on its card, which the details page took focus away from. A plugin
     * opened by URL was never scrolled to, so its card is brought into view instead.
     */
    restoreCataloguePosition() {
      const card = this.findCard(this.returnToPlugin);
      if (this.hasReturnScroll) {
        // straight there, behind the blank, and the catalogue fades in already in place
        window.scrollTo({
          top: this.returnScrollTop,
          behavior: 'auto'
        });
      } else {
        this.scrollIntoView(card);
      }
      const link = card === null || card === void 0 ? void 0 : card.querySelector('.pluginCard__titleLink');
      if (link) {
        link.focus({
          preventScroll: true
        });
      }
      this.returnToPlugin = '';
      this.hasReturnScroll = false;
    },
    /**
     * The visible card for a plugin. A section stack lists the same plugin in more than one row.
     */
    findCard(pluginName) {
      var _root$querySelectorAl, _ref, _cards$find;
      if (!pluginName) {
        return null;
      }
      const root = this.$refs.root;
      const cards = [...((_root$querySelectorAl = root === null || root === void 0 ? void 0 : root.querySelectorAll(`[data-plugin="${CSS.escape(pluginName)}"]`)) !== null && _root$querySelectorAl !== void 0 ? _root$querySelectorAl : [])];
      return (_ref = (_cards$find = cards.find(element => element.offsetParent !== null)) !== null && _cards$find !== void 0 ? _cards$find : cards[0]) !== null && _ref !== void 0 ? _ref : null;
    },
    observeSentinel() {
      const sentinel = this.$refs.sentinel;
      if (!sentinel || typeof IntersectionObserver === 'undefined') {
        this.paginated = false;
        return;
      }
      this.observer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(new IntersectionObserver(entries => {
        if (!entries.some(entry => entry.isIntersecting)) {
          return;
        }
        // the sentinel sits inside the hidden catalogue while a plugin page is open, so a browser
        // reports it as not intersecting; asserted rather than assumed, since paging in behind the
        // reader would move the cards out from under them on the way back
        if (this.viewPluginName) {
          return;
        }
        if (this.loading || this.showSections || this.pageSize >= this.filteredPlugins.length) {
          return;
        }
        this.pageSize += PAGE_SIZE;
      }, {
        rootMargin: '200px'
      }));
      this.observer.observe(sentinel);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue



Marketplacevue_type_script_lang_ts.render = render

/* harmony default export */ var Marketplace = (Marketplacevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=51987f7b

const ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_1 = ["innerHTML"];
const ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_2 = {
  class: "manage-license-key-input"
};
const ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_3 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
const ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_4 = ["value"];
const ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_5 = ["value"];
function ManageLicenseKeyvue_type_template_id_51987f7b_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_LicenseKey'),
    class: "manage-license-key"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "manage-license-key-intro",
      innerHTML: _ctx.$sanitize(_ctx.manageLicenseKeyIntro)
    }, null, 8, ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton, {
      disabled: _ctx.isUpdating
    }, null, 8, ["disabled"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "license_key",
      modelValue: _ctx.licenseKey,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.licenseKey = $event),
      placeholder: _ctx.licenseKeyPlaceholder,
      "full-width": true
    }, null, 8, ["modelValue", "placeholder"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[1] || (_cache[1] = $event => _ctx.updateLicense()),
      value: _ctx.saveButtonText,
      disabled: !_ctx.licenseKey || _ctx.isUpdating,
      id: "submit_license_key"
    }, null, 8, ["value", "disabled"]), _ctx.hasValidLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
      key: 0,
      id: "remove_license_key",
      onConfirm: _cache[2] || (_cache[2] = $event => _ctx.removeLicense()),
      disabled: _ctx.isUpdating,
      value: _ctx.translate('General_Remove')
    }, null, 8, ["disabled", "value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isUpdating
    }, null, 8, ["loading"])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, ManageLicenseKeyvue_type_template_id_51987f7b_hoisted_5)], 512)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=51987f7b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts



/* harmony default export */ var ManageLicenseKeyvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hasValidLicenseKey: Boolean
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    InstallAllPaidPluginsButton: external_CorePluginsAdmin_["InstallAllPaidPluginsButton"]
  },
  data() {
    return {
      licenseKey: '',
      hasValidLicense: this.hasValidLicenseKey,
      isUpdating: false
    };
  },
  methods: {
    updateLicenseKey(action, licenseKey, onSuccessMessage) {
      external_CoreHome_["NotificationsStore"].remove('ManageLicenseKeySuccess');
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: `Marketplace.${action}`,
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }, {
        withTokenInUrl: true
      }).then(response => {
        this.isUpdating = false;
        if (response && response.value) {
          external_CoreHome_["NotificationsStore"].show({
            id: 'ManageLicenseKeySuccess',
            message: onSuccessMessage,
            context: 'success',
            type: 'toast'
          });
          this.hasValidLicense = action !== 'deleteLicenseKey';
          this.licenseKey = '';
        }
      }, () => {
        this.isUpdating = false;
      });
    },
    removeLicense() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmRemoveLicense, {
        yes: () => {
          this.isUpdating = true;
          this.updateLicenseKey('deleteLicenseKey', '', Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
    updateLicense() {
      this.isUpdating = true;
      this.updateLicenseKey('saveLicenseKey', this.licenseKey, Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyActivatedSuccess'));
    }
  },
  computed: {
    manageLicenseKeyIntro() {
      const marketplaceLink = `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'overview'
      }))}`;
      return Object(external_CoreHome_["translate"])('Marketplace_ManageLicenseKeyIntro', `<a href="${marketplaceLink}">`, '</a>', Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account'), '</a>');
    },
    licenseKeyPlaceholder() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyIsValidShort') : Object(external_CoreHome_["translate"])('Marketplace_LicenseKey');
    },
    saveButtonText() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('CoreUpdater_UpdateTitle') : Object(external_CoreHome_["translate"])('Marketplace_ActivateLicenseKey');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue



ManageLicenseKeyvue_type_script_lang_ts.render = ManageLicenseKeyvue_type_template_id_51987f7b_render

/* harmony default export */ var ManageLicenseKey = (ManageLicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=fda5a7e0

const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_1 = {
  class: "getNewPlugins"
};
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_2 = {
  class: "row"
};
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_3 = {
  class: "pluginName"
};
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_5 = {
  key: 0
};
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_8 = [GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_6, GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_7];
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_9 = {
  class: "widgetBody"
};
const GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_10 = ["href"];
function GetNewPluginsvue_type_template_id_fda5a7e0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, (plugin, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)])), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)])), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])]), index < _ctx.plugins.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_5, GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsvue_type_template_id_fda5a7e0_hoisted_10)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=fda5a7e0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts



/* harmony default export */ var GetNewPluginsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  computed: {
    overviewLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'overview'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue



GetNewPluginsvue_type_script_lang_ts.render = GetNewPluginsvue_type_template_id_fda5a7e0_render

/* harmony default export */ var GetNewPlugins = (GetNewPluginsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=ee124f44

const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_1 = {
  class: "getNewPlugins isAdminPage",
  ref: "root"
};
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_2 = {
  class: "row"
};
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_3 = ["title"];
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_4 = ["title"];
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_5 = {
  key: 0
};
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_7 = ["src"];
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_8 = {
  class: "widgetBody"
};
const GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_9 = ["href"];
function GetNewPluginsAdminvue_type_template_id_ee124f44_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, plugin => {
    var _plugin$screenshots;
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
      class: "pluginName",
      title: plugin.description
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 8, GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_3)), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      class: "description",
      title: plugin.description
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 9, GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_4), (_plugin$screenshots = plugin.screenshots) !== null && _plugin$screenshots !== void 0 && _plugin$screenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_5, [GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      class: "screenshot",
      src: `${plugin.screenshots[0]}?w=600`,
      style: {
        "width": "100%"
      },
      alt: "",
      loading: "lazy",
      decoding: "async"
    }, null, 8, GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_7), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.marketplaceOverviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsAdminvue_type_template_id_ee124f44_hoisted_9)])], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=ee124f44

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts



/* harmony default export */ var GetNewPluginsAdminvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  computed: {
    marketplaceOverviewLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'overview'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue



GetNewPluginsAdminvue_type_script_lang_ts.render = GetNewPluginsAdminvue_type_template_id_ee124f44_render

/* harmony default export */ var GetNewPluginsAdmin = (GetNewPluginsAdminvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=4ea2b47a

const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_1 = {
  class: "getNewPlugins getPremiumFeatures widgetBody"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_2 = {
  key: 0,
  class: "col s12 m12"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_3 = ["innerHTML"];
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_4 = {
  style: {
    "margin-bottom": "28px",
    "color": "#5bb75b"
  }
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-heart red-text"
}, null, -1);
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_6 = {
  class: "pluginName"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_7 = {
  key: 0,
  class: "pluginSubtitle"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_8 = {
  class: "pluginBody"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_10 = {
  class: "pluginMoreDetails"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_11 = {
  class: "widgetBody"
};
const GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_12 = ["href"];
function GetPremiumFeaturesvue_type_template_id_4ea2b47a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginRows, (rowOfPlugins, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [index === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      style: {
        "font-weight": "bold",
        "color": "#5bb75b"
      },
      innerHTML: _ctx.$sanitize(_ctx.trialHintsText)
    }, null, 8, GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SupportMatomoThankYou')) + " ", 1), GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_5])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(rowOfPlugins, plugin => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "col s12 m4",
        key: plugin.name
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)])), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]]), plugin.specialOffer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SpecialOffer')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.specialOffer), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isBundle ? `${_ctx.translate('Marketplace_SpecialOffer')}: ` : '') + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)])), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]])])]);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetPremiumFeaturesvue_type_template_id_4ea2b47a_hoisted_12)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=4ea2b47a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts



/* harmony default export */ var GetPremiumFeaturesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  computed: {
    trialHintsText() {
      const link = Object(external_CoreHome_["externalRawLink"])('https://shop.matomo.org/free-trial/');
      const linkStyle = 'color:#5bb75b;text-decoration: underline;';
      return Object(external_CoreHome_["translate"])('Marketplace_TrialHints', `<a style="${linkStyle}" href="${link}" target="_blank" rel="noreferrer noopener">`, '</a>');
    },
    pluginRows() {
      // divide plugins array into rows of 3
      const result = [];
      this.plugins.forEach((plugin, index) => {
        const row = Math.floor(index / 3);
        result[row] = result[row] || [];
        result[row].push(plugin);
      });
      return result;
    },
    overviewLink() {
      const query = external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'overview'
      }));
      return `?${query}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue



GetPremiumFeaturesvue_type_script_lang_ts.render = GetPremiumFeaturesvue_type_template_id_4ea2b47a_render

/* harmony default export */ var GetPremiumFeatures = (GetPremiumFeaturesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=73252d1c

function OverviewIntrovue_type_template_id_73252d1c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Marketplace = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Marketplace");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Marketplace, {
    "default-sort": _ctx.defaultSort,
    "install-all-paid-plugins-visible": _ctx.installAllPaidPluginsVisible,
    "install-disabled": _ctx.installDisabled,
    "current-user-email": _ctx.currentUserEmail,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "is-super-user": _ctx.isSuperUser,
    "is-multi-server-environment": _ctx.isMultiServerEnvironment,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "is-valid-consumer": _ctx.getIsValidConsumer,
    "deactivate-nonce": _ctx.deactivateNonce,
    "activate-nonce": _ctx.activateNonce,
    "install-nonce": _ctx.installNonce,
    "update-nonce": _ctx.updateNonce,
    "has-some-admin-access": _ctx.hasSomeAdminAccess,
    "num-users": _ctx.numUsers,
    onTriggerUpdate: _cache[0] || (_cache[0] = $event => this.updateOverviewData()),
    onStartTrialStart: _cache[1] || (_cache[1] = $event => this.disableInstallAllPlugins(true)),
    onStartTrialStop: _cache[2] || (_cache[2] = $event => this.disableInstallAllPlugins(false))
  }, null, 8, ["default-sort", "install-all-paid-plugins-visible", "install-disabled", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "has-some-admin-access", "num-users"])])), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=73252d1c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts



/**
 * The reporting page's site, period and segment selectors. Nothing in the Marketplace is scoped to
 * a site, a period or a segment, so in the reporting menu they would offer choices that change
 * nothing. The update notice beside them stays.
 */
const REPORTING_SELECTORS = '.top_controls .top_bar_sites_selector, .top_controls #periodString, ' + '.top_controls .segmentEditorPanel';
/* harmony default export */ var OverviewIntrovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentUserEmail: String,
    inReportingMenu: Boolean,
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    hasSomeAdminAccess: Boolean,
    installNonce: {
      type: String,
      required: true
    },
    activateNonce: {
      type: String,
      required: true
    },
    deactivateNonce: {
      type: String,
      required: true
    },
    updateNonce: {
      type: String,
      required: true
    },
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    defaultSort: {
      type: String,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    }
  },
  components: {
    Marketplace: Marketplace
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  data() {
    return {
      updating: false,
      fetchRequest: null,
      fetchRequestAbortController: null,
      updateData: null,
      installDisabled: false,
      installLoading: false
    };
  },
  mounted() {
    this.setReportingSelectorsHidden(true);
  },
  unmounted() {
    // the reporting page is a single page: the next category shown keeps the same top controls
    this.setReportingSelectorsHidden(false);
  },
  computed: {
    getIsValidConsumer() {
      return this.updateData && typeof this.updateData.isValidConsumer !== 'undefined' ? this.updateData.isValidConsumer : this.isValidConsumer;
    },
    installAllPaidPluginsVisible() {
      return this.getIsValidConsumer && this.isSuperUser && this.isAutoUpdatePossible && this.isPluginsAdminEnabled || this.installDisabled && this.installLoading;
    }
  },
  methods: {
    setReportingSelectorsHidden(hidden) {
      if (!this.inReportingMenu) {
        return;
      }
      document.querySelectorAll(REPORTING_SELECTORS).forEach(element => {
        element.style.display = hidden ? 'none' : '';
      });
    },
    disableInstallAllPlugins(isLoading) {
      this.installDisabled = true;
      this.installLoading = isLoading;
    },
    enableInstallAllPlugins() {
      this.installDisabled = false;
      this.installLoading = false;
    },
    updateOverviewData() {
      this.updating = true;
      if (this.isSuperUser) {
        this.disableInstallAllPlugins(true);
      }
      if (this.fetchRequestAbortController) {
        this.fetchRequestAbortController.abort();
        this.fetchRequestAbortController = null;
      }
      this.fetchRequestAbortController = new AbortController();
      this.fetchRequest = external_CoreHome_["AjaxHelper"].post({
        module: 'Marketplace',
        action: 'updateOverview',
        format: 'JSON'
      }, {}, {
        withTokenInUrl: true,
        abortController: this.fetchRequestAbortController
      }).then(response => {
        this.updateData = response;
      }).finally(() => {
        this.updating = false;
        this.fetchRequestAbortController = null;
        this.enableInstallAllPlugins();
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue



OverviewIntrovue_type_script_lang_ts.render = OverviewIntrovue_type_template_id_73252d1c_render

/* harmony default export */ var OverviewIntro = (OverviewIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=a5f6c6ae

const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_1 = {
  key: 0
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_2 = ["href"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_5 = ["innerHTML"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_7 = {
  class: "subscriptionName"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_8 = ["href"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_9 = {
  key: 1
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_10 = {
  class: "subscriptionType"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_11 = ["title"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_12 = {
  key: 0,
  class: "icon-error"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_13 = {
  key: 1,
  class: "icon-warning"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_14 = {
  key: 2,
  class: "icon-error"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_15 = {
  key: 3,
  class: "icon-ok"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_16 = ["title"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_18 = {
  key: 0
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_19 = {
  colspan: "6"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_20 = {
  class: "tableActionBar"
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_21 = ["href"];
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-table"
}, null, -1);
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_23 = {
  key: 1
};
const SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_24 = ["innerHTML"];
function SubscriptionOverviewvue_type_template_id_a5f6c6ae_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_OverviewPluginSubscriptions'),
    class: "subscriptionOverview"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.hasLicenseKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginSubscriptionsList')) + " ", 1), _ctx.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.loginUrl
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsAllDetails')), 9, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsMissingInfo')) + " ", 1), SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoValidSubscriptionNoUpdates')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_CurrentNumPiwikUsers', `<strong>${_ctx.numUsers}</strong>`))
    }, null, 8, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_5)]), SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionType')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionStartDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionEndDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionNextPaymentDate')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.subscriptions || [], (subscription, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_7, [subscription.plugin.htmlUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        href: subscription.plugin.htmlUrl,
        rel: "noreferrer noopener",
        target: "_blank"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 9, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.productType), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
        class: "subscriptionStatus",
        title: _ctx.getSubscriptionStatusTitle(subscription)
      }, [!subscription.isValid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_12)) : subscription.isExpiredSoon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_13)) : subscription.status !== '' && subscription.status !== 'Active' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_14)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_15)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.status) + " ", 1), subscription.isExceeded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 4,
        class: "errorMessage",
        title: _ctx.translate('Marketplace_LicenseExceededPossibleCause')
      }, [SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Exceeded')), 1)], 8, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.start), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.isValid && subscription.nextPayment ? _ctx.translate('Marketplace_LicenseRenewsNextPaymentDate') : subscription.end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.nextPayment), 1)]);
    }), 128)), !_ctx.subscriptions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoSubscriptionsFound')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.marketplaceOverviewLink,
      class: ""
    }, [SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BrowseMarketplace')), 1)], 8, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_21)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
    }, null, 8, SubscriptionOverviewvue_type_template_id_a5f6c6ae_hoisted_24)]))]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=a5f6c6ae

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts


/* harmony default export */ var SubscriptionOverviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    loginUrl: {
      type: String,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    },
    hasLicenseKey: Boolean,
    subscriptions: {
      type: Array,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  methods: {
    getSubscriptionStatusTitle(sub) {
      if (!sub.isValid) {
        return Object(external_CoreHome_["translate"])('Marketplace_SubscriptionInvalid');
      }
      if (sub.isExpiredSoon) {
        return Object(external_CoreHome_["translate"])('Marketplace_SubscriptionExpiresSoon');
      }
      return undefined;
    }
  },
  computed: {
    marketplaceOverviewLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'overview'
      }))}`;
    },
    licenseKeyLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        idSite: external_CoreHome_["MatomoUrl"].parsed.value.idSite,
        module: 'Marketplace',
        action: 'manageLicenseKey'
      }))}`;
    },
    missingLicenseText() {
      return Object(external_CoreHome_["translate"])('Marketplace_OverviewPluginSubscriptionsMissingLicenseMessage', `<a href="${this.licenseKeyLink}">`, '</a>', `<a href="${this.marketplaceOverviewLink}">`, '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue



SubscriptionOverviewvue_type_script_lang_ts.render = SubscriptionOverviewvue_type_template_id_a5f6c6ae_render

/* harmony default export */ var SubscriptionOverview = (SubscriptionOverviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=1d333064

const RichMenuButtonvue_type_template_id_1d333064_hoisted_1 = {
  class: "richMarketplaceMenuButton"
};
const RichMenuButtonvue_type_template_id_1d333064_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const RichMenuButtonvue_type_template_id_1d333064_hoisted_3 = {
  class: "intro"
};
const RichMenuButtonvue_type_template_id_1d333064_hoisted_4 = {
  class: "cta"
};
const RichMenuButtonvue_type_template_id_1d333064_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-marketplace"
}, " ", -1);
function RichMenuButtonvue_type_template_id_1d333064_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RichMenuButtonvue_type_template_id_1d333064_hoisted_1, [RichMenuButtonvue_type_template_id_1d333064_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_1d333064_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RichMenuIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_1d333064_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn btn-outline",
    tabindex: "5",
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('action'), ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])($event => _ctx.$emit('action'), ["enter"]))
  }, [RichMenuButtonvue_type_template_id_1d333064_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)], 32)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=1d333064

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=script&lang=ts

/* harmony default export */ var RichMenuButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue



RichMenuButtonvue_type_script_lang_ts.render = RichMenuButtonvue_type_template_id_1d333064_render

/* harmony default export */ var RichMenuButton = (RichMenuButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/index.ts
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
//# sourceMappingURL=Marketplace.umd.js.map