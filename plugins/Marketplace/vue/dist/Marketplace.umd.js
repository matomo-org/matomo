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
__webpack_require__.d(__webpack_exports__, "InstallAllPaidPluginsButton", function() { return /* reexport */ InstallAllPaidPluginsButton; });
__webpack_require__.d(__webpack_exports__, "ManageLicenseKey", function() { return /* reexport */ ManageLicenseKey; });
__webpack_require__.d(__webpack_exports__, "GetNewPlugins", function() { return /* reexport */ GetNewPlugins; });
__webpack_require__.d(__webpack_exports__, "GetNewPluginsAdmin", function() { return /* reexport */ GetNewPluginsAdmin; });
__webpack_require__.d(__webpack_exports__, "GetPremiumFeatures", function() { return /* reexport */ GetPremiumFeatures; });
__webpack_require__.d(__webpack_exports__, "MissingReqsNotice", function() { return /* reexport */ MissingReqsNotice; });
__webpack_require__.d(__webpack_exports__, "OverviewIntro", function() { return /* reexport */ OverviewIntro; });
__webpack_require__.d(__webpack_exports__, "SubscriptionOverview", function() { return /* reexport */ SubscriptionOverview; });
__webpack_require__.d(__webpack_exports__, "RichMenuButton", function() { return /* reexport */ RichMenuButton; });
__webpack_require__.d(__webpack_exports__, "PluginList", function() { return /* reexport */ PluginList; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=19bbd7e6

const _hoisted_1 = {
  class: "row marketplaceActions",
  ref: "root"
};
const _hoisted_2 = {
  class: "col s12 m6 l4"
};
const _hoisted_3 = {
  class: "col s12 m6 l4"
};
const _hoisted_4 = {
  key: 0,
  class: "col s12 m12 l4"
};
const _hoisted_5 = {
  class: "plugin-search"
};
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search"
}, null, -1);
const _hoisted_7 = ["alt"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginsToShow;
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_PluginList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginList");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "plugin_type",
    "model-value": _ctx.pluginTypeFilter,
    "onUpdate:modelValue": _ctx.updateType,
    title: _ctx.translate('Marketplace_Show'),
    "full-width": true,
    options: _ctx.pluginTypeOptions
  }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "plugin_sort",
    "model-value": _ctx.pluginSort,
    "onUpdate:modelValue": _ctx.updateSort,
    title: _ctx.translate('Marketplace_Sort'),
    "full-width": true,
    options: _ctx.pluginSortOptions
  }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])]), ((_ctx$pluginsToShow = _ctx.pluginsToShow) === null || _ctx$pluginsToShow === void 0 ? void 0 : _ctx$pluginsToShow.length) > 20 || _ctx.searchQuery ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "query",
    title: _ctx.queryInputTitle,
    "full-width": true,
    "model-value": _ctx.searchQuery,
    "onUpdate:modelValue": _ctx.updateQuery
  }, null, 8, ["title", "model-value", "onUpdate:modelValue"])]), _hoisted_6])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), !_ctx.loading && _ctx.pluginsToShow.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PluginList, {
    key: 0,
    "plugins-to-show": _ctx.pluginsToShow,
    "current-user-email": _ctx.currentUserEmail,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "is-super-user": _ctx.isSuperUser,
    "is-multi-server-environment": _ctx.isMultiServerEnvironment,
    "has-some-admin-access": _ctx.hasSomeAdminAccess,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "is-valid-consumer": _ctx.isValidConsumer,
    "deactivate-nonce": _ctx.deactivateNonce,
    "activate-nonce": _ctx.activateNonce,
    "install-nonce": _ctx.installNonce,
    "update-nonce": _ctx.updateNonce,
    "num-users": _ctx.numUsers,
    onTriggerUpdate: _cache[0] || (_cache[0] = $event => this.updateMarketplace()),
    onStartTrialStart: _cache[1] || (_cache[1] = $event => this.$emit('startTrialStart')),
    onStartTrialStop: _cache[2] || (_cache[2] = $event => this.$emit('startTrialStop'))
  }, null, 8, ["plugins-to-show", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "has-some-admin-access", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loading && _ctx.pluginsToShow.length == 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 1
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.showThemes ? 'Marketplace_NoThemesFound' : 'Marketplace_NoPluginsFound')), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 2
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: "plugins/Morpheus/images/loading-blue.gif",
      alt: _ctx.translate('General_LoadingData')
    }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=19bbd7e6

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=9a1e2784

const PluginListvue_type_template_id_9a1e2784_hoisted_1 = {
  key: 0,
  class: "pluginListContainer row"
};
const PluginListvue_type_template_id_9a1e2784_hoisted_2 = ["onClick"];
const PluginListvue_type_template_id_9a1e2784_hoisted_3 = {
  class: "card"
};
const PluginListvue_type_template_id_9a1e2784_hoisted_4 = {
  class: "card-content"
};
const PluginListvue_type_template_id_9a1e2784_hoisted_5 = ["src"];
const PluginListvue_type_template_id_9a1e2784_hoisted_6 = {
  class: "content-container"
};
const PluginListvue_type_template_id_9a1e2784_hoisted_7 = {
  class: "card-content-top"
};
const _hoisted_8 = {
  key: 0,
  class: "matomo-badge matomo-badge-top",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
const _hoisted_9 = {
  class: "price"
};
const _hoisted_10 = ["onClick"];
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "card-focus"
}, null, -1);
const _hoisted_12 = {
  class: "card-title"
};
const _hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "card-title-chevron"
}, " ›", -1);
const _hoisted_14 = {
  class: "card-description"
};
const _hoisted_15 = {
  class: "card-content-bottom"
};
const _hoisted_16 = {
  key: 0,
  class: "downloads"
};
const _hoisted_17 = {
  class: "owner"
};
const _hoisted_18 = {
  key: 0
};
const _hoisted_19 = {
  key: 1
};
const _hoisted_20 = {
  class: "cta-container"
};
const _hoisted_21 = {
  key: 1,
  class: "matomo-badge matomo-badge-bottom",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginListvue_type_template_id_9a1e2784_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RequestTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RequestTrial");
  const _component_StartFreeTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("StartFreeTrial");
  const _component_PluginDetailsModal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginDetailsModal");
  const _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RequestTrial, {
    modelValue: _ctx.showRequestTrialForPlugin,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.showRequestTrialForPlugin = $event),
    onTrialRequested: _cache[1] || (_cache[1] = $event => this.$emit('triggerUpdate'))
  }, null, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_StartFreeTrial, {
    "current-user-email": _ctx.currentUserEmail,
    "is-valid-consumer": _ctx.isValidConsumer,
    modelValue: _ctx.showStartFreeTrialForPlugin,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.showStartFreeTrialForPlugin = $event),
    onTrialStarted: _cache[3] || (_cache[3] = $event => {
      this.$emit('triggerUpdate');
    }),
    onStartTrialStart: _cache[4] || (_cache[4] = $event => {
      this.$emit('startTrialStart');
    }),
    onStartTrialStop: _cache[5] || (_cache[5] = $event => {
      this.$emit('startTrialStop');
    })
  }, null, 8, ["current-user-email", "is-valid-consumer", "modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginDetailsModal, {
    modelValue: _ctx.showPluginDetailsForPlugin,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.showPluginDetailsForPlugin = $event),
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
    onRequestTrial: _cache[7] || (_cache[7] = $event => this.requestTrial($event)),
    onStartFreeTrial: _cache[8] || (_cache[8] = $event => this.startFreeTrial($event))
  }, null, 8, ["modelValue", "is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "has-some-admin-access", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"]), _ctx.pluginsToShow.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginsToShow, plugin => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m6 l4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`card-holder ${plugin.numDownloads > 0 ? 'card-with-downloads' : ''}`),
      onClick: $event => _ctx.clickCard($event, plugin)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: `${plugin.coverImage}?w=880&h=480`,
      alt: "",
      class: "cover-image"
    }, null, 8, PluginListvue_type_template_id_9a1e2784_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_7, ['piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [plugin.priceFrom ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PriceFromPerPeriod', plugin.priceFrom.prettyPrice, plugin.priceFrom.period)), 1)], 64)) : plugin.isFree ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Free')), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.clickCard($event, plugin), ["prevent"]),
      class: "card-title-link",
      href: "#",
      tabindex: "7"
    }, [_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1), _hoisted_13])], 8, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [plugin.numDownloads > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.numDownloadsPretty) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Downloads').toLowerCase()), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CreatedBy')) + " ", 1), plugin.owner === 'piwik' || plugin.owner === 'matomo-org' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_18, " Matomo")) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.owner), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
      "is-super-user": _ctx.isSuperUser,
      "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
      "is-multi-server-environment": _ctx.isMultiServerEnvironment,
      "is-valid-consumer": _ctx.isValidConsumer,
      "is-auto-update-possible": _ctx.isAutoUpdatePossible,
      "activate-nonce": _ctx.activateNonce,
      "deactivate-nonce": _ctx.deactivateNonce,
      "install-nonce": _ctx.installNonce,
      "update-nonce": _ctx.updateNonce,
      plugin: plugin,
      "in-modal": false,
      onOpenDetailsModal: $event => this.openDetailsModal(plugin),
      onRequestTrial: $event => this.requestTrial(plugin),
      onStartFreeTrial: $event => this.startFreeTrial(plugin)
    }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "onOpenDetailsModal", "onRequestTrial", "onStartFreeTrial"])]), 'piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_21)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])])])], 10, PluginListvue_type_template_id_9a1e2784_hoisted_2)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=9a1e2784

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=c75c86ba

const CTAContainervue_type_template_id_c75c86ba_hoisted_1 = {
  key: 0,
  class: "alert alert-danger alert-no-background"
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_2 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_3 = ["href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_4 = {
  key: 2,
  class: "alert alert-danger alert-no-background"
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_5 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_6 = ["href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_7 = {
  key: 1,
  class: "alert alert-warning alert-no-background"
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_8 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_9 = {
  key: 4,
  class: "alert alert-success alert-no-background"
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_10 = ["href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_11 = ["href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_12 = ["title"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_13 = ["title", "href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_14 = {
  key: 8,
  class: "alert alert-warning alert-no-background"
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_15 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};
const CTAContainervue_type_template_id_c75c86ba_hoisted_16 = ["href"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_17 = ["title"];
const CTAContainervue_type_template_id_c75c86ba_hoisted_18 = ["title"];
function CTAContainervue_type_template_id_c75c86ba_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MoreDetailsAction = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MoreDetailsAction");
  const _component_DownloadButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DownloadButton");
  return _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseMissing')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("("), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[0] || (_cache[0] = $event => _ctx.$emit('openDetailsModal'))
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.inModal && _ctx.plugin.hasExceededLicense && _ctx.plugin.consumer.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "btn btn-block",
    tabindex: "7",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.plugin.consumer.loginUrl)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UpgradeSubscription')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_3)) : _ctx.plugin.hasExceededLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseExceeded')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("("), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[1] || (_cache[1] = $event => _ctx.$emit('openDetailsModal'))
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.canBeUpdated && 0 == _ctx.plugin.missingRequirements.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [_ctx.isAutoUpdatePossible && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    class: "btn btn-block",
    href: _ctx.linkToUpdate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreUpdater_UpdateTitle')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_6)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotUpdate')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("("), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[2] || (_cache[2] = $event => _ctx.$emit('openDetailsModal'))
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]))], 64)) : _ctx.plugin.isInstalled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Installed')) + " ", 1), _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" ("), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": false,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "is-auto-update-possible"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ")], 64)) : !_ctx.plugin.isInvalid && !_ctx.isMultiServerEnvironment && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" ("), _ctx.plugin.isActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    href: _ctx.linkToDeactivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Deactivate')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_10)) : _ctx.plugin.missingRequirements.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ")], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    tabindex: "7",
    href: _ctx.linkToActivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activate')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_11)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.isEligibleForFreeTrial && !_ctx.inModal && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 5,
    class: "btn btn-block purchaseable",
    title: _ctx.translate('Marketplace_StartFreeTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_StartFreeTrial')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_12)) : _ctx.plugin.isEligibleForFreeTrial && _ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 6,
    class: "btn btn-block addToCartLink",
    target: "_blank",
    title: _ctx.translate('Marketplace_ClickToCompletePurchase'),
    rel: "noreferrer noopener",
    href: _ctx.shopVariationUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AddToCart')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_13)) : !_ctx.inModal && !_ctx.plugin.isDownloadable && (_ctx.plugin.isPaid || _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 7,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[3] || (_cache[3] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotInstall')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("("), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[4] || (_cache[4] = $event => _ctx.$emit('openDetailsModal'))
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.isPluginsAdminEnabled && _ctx.plugin.hasDownloadLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 9,
    tabindex: "7",
    href: _ctx.linkToInstall(_ctx.plugin.name),
    class: "btn btn-block"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ActionInstall')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_16)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 10
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[5] || (_cache[5] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64))], 64)) : _ctx.plugin.isTrialRequested ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    tabindex: "7",
    class: "btn btn-block purchaseable disabled",
    href: "",
    title: _ctx.translate('Marketplace_TrialRequested')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialRequested')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_17)) : _ctx.plugin.canTrialBeRequested && !_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    tabindex: "7",
    class: "btn btn-block purchaseable",
    href: "",
    onClick: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      this.$emit('requestTrial');
    }, ["prevent"])),
    title: _ctx.translate('Marketplace_RequestTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrial')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_18)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[7] || (_cache[7] = $event => _ctx.$emit('openDetailsModal'))
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64));
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=c75c86ba

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=template&id=201a6490

const DownloadButtonvue_type_template_id_201a6490_hoisted_1 = {
  key: 0,
  onclick: "$(this).css('display', 'none')"
};
const DownloadButtonvue_type_template_id_201a6490_hoisted_2 = ["href"];
function DownloadButtonvue_type_template_id_201a6490_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", DownloadButtonvue_type_template_id_201a6490_hoisted_1, [_ctx.showOr ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Or')) + " ", 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    tabindex: "7",
    class: "plugin-details download",
    href: _ctx.linkTo({
      module: 'Marketplace',
      action: 'download',
      pluginName: _ctx.plugin.name,
      nonce: _ctx.plugin.downloadNonce
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 9, DownloadButtonvue_type_template_id_201a6490_hoisted_2)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=template&id=201a6490

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }


/* harmony default export */ var DownloadButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    },
    showOr: {
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
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue



DownloadButtonvue_type_script_lang_ts.render = DownloadButtonvue_type_template_id_201a6490_render

/* harmony default export */ var DownloadButton = (DownloadButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=3596d57c

const MoreDetailsActionvue_type_template_id_3596d57c_hoisted_1 = ["title"];
function MoreDetailsActionvue_type_template_id_3596d57c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    tabindex: "7",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'btn btn-block': _ctx.showAsButton
    }),
    href: "",
    title: _ctx.translate('General_MoreDetails'),
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('action'), ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])($event => _ctx.$emit('action'), ["enter"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label ? _ctx.label : _ctx.translate('General_Help')), 43, MoreDetailsActionvue_type_template_id_3596d57c_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=3596d57c

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



MoreDetailsActionvue_type_script_lang_ts.render = MoreDetailsActionvue_type_template_id_3596d57c_render

/* harmony default export */ var MoreDetailsAction = (MoreDetailsActionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts
function CTAContainervue_type_script_lang_ts_extends() { CTAContainervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return CTAContainervue_type_script_lang_ts_extends.apply(this, arguments); }




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
  emits: ['openDetailsModal', 'requestTrial', 'startFreeTrial'],
  components: {
    MoreDetailsAction: MoreDetailsAction,
    DownloadButton: DownloadButton
  },
  methods: {
    linkToActivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'activate',
        redirectTo: 'referrer',
        nonce: this.activateNonce,
        pluginName
      });
    },
    linkToDeactivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        redirectTo: 'referrer',
        nonce: this.deactivateNonce,
        pluginName
      });
    },
    linkToInstall(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'installPlugin',
        nonce: this.installNonce,
        pluginName
      });
    },
    linkToUpdate(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'updatePlugin',
        nonce: this.updateNonce,
        pluginName
      });
    },
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(CTAContainervue_type_script_lang_ts_extends(CTAContainervue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue



CTAContainervue_type_script_lang_ts.render = CTAContainervue_type_template_id_c75c86ba_render

/* harmony default export */ var CTAContainer = (CTAContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=template&id=a8ef37ce

const RequestTrialvue_type_template_id_a8ef37ce_hoisted_1 = {
  class: "ui-confirm",
  ref: "confirm"
};
const RequestTrialvue_type_template_id_a8ef37ce_hoisted_2 = ["value"];
const RequestTrialvue_type_template_id_a8ef37ce_hoisted_3 = ["value"];
function RequestTrialvue_type_template_id_a8ef37ce_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$plugin;
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RequestTrialvue_type_template_id_a8ef37ce_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrialConfirmTitle', (_ctx$plugin = _ctx.plugin) === null || _ctx$plugin === void 0 ? void 0 : _ctx$plugin.displayName)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrialConfirmEmailWarning')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, RequestTrialvue_type_template_id_a8ef37ce_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, RequestTrialvue_type_template_id_a8ef37ce_hoisted_3)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=template&id=a8ef37ce

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



RequestTrialvue_type_script_lang_ts.render = RequestTrialvue_type_template_id_a8ef37ce_render

/* harmony default export */ var RequestTrial = (RequestTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=5f45b39e

const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_1 = {
  class: "modal",
  id: "startFreeTrial"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_2 = {
  key: 0,
  class: "btn-close modal-close"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
}, null, -1);
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_4 = [StartFreeTrialvue_type_template_id_5f45b39e_hoisted_3];
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_5 = {
  key: 1,
  class: "modal-content trial-start-in-progress"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_6 = {
  class: "Piwik_Popover_Loading"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_7 = {
  class: "Piwik_Popover_Loading_Name"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_8 = {
  key: 2,
  class: "modal-content trial-start-error"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_9 = {
  class: "modal-text"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_10 = {
  key: 3,
  class: "modal-content trial-start-no-license"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_11 = {
  class: "modal-text"
};
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_12 = ["innerHTML"];
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_13 = ["innerHTML"];
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_14 = ["disabled"];
const StartFreeTrialvue_type_template_id_5f45b39e_hoisted_15 = ["innerHTML"];
function StartFreeTrialvue_type_template_id_5f45b39e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_1, [!_ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_2, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressText')), 1)])])])) : _ctx.trialStartError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trialStartError), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorSupport')), 1)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "email",
    modelValue: _ctx.createAccountEmail,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.createAccountEmail = $event),
    "full-width": true,
    title: _ctx.translate('UsersManager_Email')
  }, null, 8, ["modelValue", "title"]), _ctx.createAccountError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "alert alert-danger",
    innerHTML: _ctx.$sanitize(_ctx.createAccountError)
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_12)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "trial-start-legal-hint",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseLegalHintText)
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn",
    disabled: !_ctx.createAccountEmail,
    onClick: _cache[1] || (_cache[1] = $event => _ctx.createAccountAndStartFreeTrial())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseCreateAccount')), 9, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_14)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "add-existing-license",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseAddHereText)
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_15)])]))]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=5f45b39e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=script&lang=ts



const {
  $
} = window;
/* harmony default export */ var StartFreeTrialvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    },
    currentUserEmail: String,
    isValidConsumer: Boolean
  },
  data() {
    return {
      createAccountEmail: this.currentUserEmail || '',
      createAccountError: null,
      trialStartError: null,
      loadingModalCloseCallback: undefined,
      trialStartInProgress: false,
      trialStartSuccessNotificationMessage: '',
      trialStartSuccessNotificationTitle: ''
    };
  },
  emits: ['update:modelValue', 'trialStarted', 'startTrialStart', 'startTrialStop'],
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }
      if (this.isValidConsumer) {
        this.trialStartSuccessNotificationMessage = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStarted', '<strong>', '</strong>', this.plugin.displayName);
        this.startFreeTrial();
      } else {
        this.trialStartSuccessNotificationTitle = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle');
        this.trialStartSuccessNotificationMessage = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage', this.plugin.displayName);
        this.showLicenseDialog(false);
      }
    }
  },
  computed: {
    plugin() {
      return this.modelValue;
    },
    trialStartNoLicenseAddHereText() {
      const link = `?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey'
      })}`;
      return Object(external_CoreHome_["translate"])('Marketplace_TrialStartNoLicenseAddHere', `<a href="${link}">`, '</a>');
    },
    trialStartNoLicenseLegalHintText() {
      return Object(external_CoreHome_["translate"])('Marketplace_TrialStartNoLicenseLegalHint', Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/terms-conditions/'), '</a>', Object(external_CoreHome_["externalLink"])('https://matomo.org/privacy-policy/'), '</a>');
    }
  },
  methods: {
    closeModal() {
      $('#startFreeTrial').modal('close');
    },
    createAccountAndStartFreeTrial() {
      if (!this.createAccountEmail) {
        return;
      }
      this.showLoadingModal(true);
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.createAccount'
      }, {
        email: this.createAccountEmail
      }, {
        createErrorNotification: false
      }).then(() => {
        this.startFreeTrial();
      }).catch(error => {
        if (error.message.startsWith('Marketplace_CreateAccountError')) {
          this.showErrorModal(Object(external_CoreHome_["translate"])(error.message));
          this.trialStartInProgress = false;
          this.$emit('update:modelValue', null);
        } else {
          this.createAccountError = error.message;
          this.trialStartInProgress = false;
          this.showLicenseDialog(true);
        }
      });
    },
    showLicenseDialog(immediateTransition) {
      const onEnter = event => {
        const keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode === 13) {
          this.closeModal();
          this.createAccountAndStartFreeTrial();
        }
      };
      const modalOptions = {
        dismissible: true,
        onOpenEnd: () => {
          const emailField = '.modal.open #email';
          $(emailField).focus();
          $(emailField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: () => {
          this.createAccountError = null;
          if (this.trialStartInProgress) {
            return;
          }
          this.$emit('update:modelValue', null);
        }
      };
      if (immediateTransition) {
        modalOptions.inDuration = 0;
      }
      $('#startFreeTrial').modal(modalOptions).modal('open');
    },
    showErrorModal(error) {
      if (this.trialStartError) {
        return;
      }
      this.trialStartError = error;
      $('#startFreeTrial').modal({
        dismissible: true,
        inDuration: 0,
        onCloseEnd: () => {
          this.trialStartError = null;
        }
      }).modal('open');
    },
    showLoadingModal(immediateTransition) {
      if (this.trialStartInProgress) {
        return;
      }
      this.trialStartInProgress = true;
      this.loadingModalCloseCallback = undefined;
      $('#startFreeTrial').modal({
        dismissible: false,
        inDuration: immediateTransition ? 0 : undefined,
        onCloseEnd: () => {
          if (!this.loadingModalCloseCallback) {
            return;
          }
          this.loadingModalCloseCallback();
          this.loadingModalCloseCallback = undefined;
        }
      }).modal('open');
    },
    startFreeTrial() {
      this.showLoadingModal(false);
      this.$emit('startTrialStart');
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.startFreeTrial'
      }, {
        pluginName: this.plugin.name
      }, {
        createErrorNotification: false
      }).then(() => {
        this.loadingModalCloseCallback = this.startFreeTrialSuccess;
        this.closeModal();
      }).catch(error => {
        this.showErrorModal(external_CoreHome_["Matomo"].helper.htmlDecode(error.message));
        this.trialStartInProgress = false;
        this.$emit('startTrialStop');
      }).finally(() => {
        this.$emit('update:modelValue', null);
      });
    },
    startFreeTrialSuccess() {
      const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
        message: this.trialStartSuccessNotificationMessage,
        title: this.trialStartSuccessNotificationTitle,
        context: 'success',
        id: 'startTrialSuccess',
        placeat: '#notificationContainer',
        type: 'transient'
      });
      external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      this.trialStartInProgress = false;
      this.$emit('trialStarted');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue



StartFreeTrialvue_type_script_lang_ts.render = StartFreeTrialvue_type_template_id_5f45b39e_render

/* harmony default export */ var StartFreeTrial = (StartFreeTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2b416103

const PluginDetailsModalvue_type_template_id_2b416103_hoisted_1 = {
  ref: "root",
  class: "modal",
  id: "pluginDetailsModal"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_2 = {
  class: "modal-content__header"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "btn-close modal-close"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
})], -1);
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_4 = {
  key: 0,
  class: "plugin-metadata-part1"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 1", -1);
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_6 = {
  key: 0,
  class: "pair"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "star-icon reviews-icon",
  src: "plugins/Marketplace/images/star.svg",
  alt: ""
}, null, -1);
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_8 = {
  key: 1,
  class: "pair"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_9 = {
  key: 2,
  class: "pair"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_10 = {
  key: 3,
  class: "pair"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_11 = {
  key: 4,
  class: "pair"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_12 = {
  class: "plugin-description"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_13 = {
  key: 1,
  class: "alert alert-warning"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_14 = {
  key: 2,
  class: "alert alert-warning"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_15 = {
  key: 3,
  class: "alert alert-danger"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_16 = {
  key: 4,
  class: "alert alert-warning"
};
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_17 = ["innerHTML"];
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_18 = ["innerHTML"];
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_19 = ["innerHTML"];
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_20 = ["innerHTML"];
const PluginDetailsModalvue_type_template_id_2b416103_hoisted_21 = {
  class: "plugin-metadata-part2"
};
const _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 2", -1);
const _hoisted_24 = {
  key: 0,
  class: "pair"
};
const _hoisted_25 = {
  key: 1,
  class: "pair"
};
const _hoisted_26 = {
  class: "pair"
};
const _hoisted_27 = ["href"];
const _hoisted_28 = ["href"];
const _hoisted_29 = {
  key: 2
};
const _hoisted_30 = {
  key: 3
};
const _hoisted_31 = {
  class: "pair"
};
const _hoisted_32 = ["href"];
const _hoisted_33 = ["href"];
const _hoisted_34 = ["href"];
const _hoisted_35 = {
  key: 0,
  class: "pair"
};
const _hoisted_36 = {
  key: 1,
  class: "pair"
};
const _hoisted_37 = ["href"];
const _hoisted_38 = {
  key: 1
};
const _hoisted_39 = ["innerHTML"];
const _hoisted_40 = {
  key: 0
};
const _hoisted_41 = ["href"];
const _hoisted_42 = {
  key: 1
};
const _hoisted_43 = ["href"];
const _hoisted_44 = ["innerHTML"];
const _hoisted_45 = {
  key: 0,
  class: "plugin-screenshots"
};
const _hoisted_46 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_47 = {
  class: "thumbnails"
};
const _hoisted_48 = ["src"];
const _hoisted_49 = {
  key: 1,
  class: "plugin-documentation"
};
const _hoisted_50 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_51 = ["innerHTML"];
const _hoisted_52 = {
  key: 2,
  class: "plugin-faq"
};
const _hoisted_53 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_54 = ["innerHTML"];
const _hoisted_55 = {
  key: 3,
  class: "plugin-reviews",
  id: "reviews"
};
const _hoisted_56 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_57 = ["id", "src"];
const _hoisted_58 = {
  key: 0,
  class: "matomo-badge matomo-badge-modal",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
const _hoisted_59 = {
  class: "cta-container cta-container-modal"
};
const _hoisted_60 = {
  key: 0,
  class: "free-trial"
};
const _hoisted_61 = {
  class: "free-trial-lead-in"
};
const _hoisted_62 = ["title"];
const _hoisted_63 = ["value", "title"];
const _hoisted_64 = {
  key: 1,
  class: "matomo-badge matomo-badge-modal",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginDetailsModalvue_type_template_id_2b416103_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginActivity, _ctx$pluginActivity2, _ctx$pluginLatestVers, _ctx$pluginLatestVers2, _ctx$pluginLatestVers3, _ctx$pluginLatestVers4;
  const _component_MissingReqsNotice = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MissingReqsNotice");
  const _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_1, [!_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["modal-content", {
      'modal-content--simple-header': !_ctx.hasHeaderMetadata
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_2, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin && _ctx.plugin.displayName ? _ctx.plugin.displayName : 'Plugin details'), 1), _ctx.hasHeaderMetadata ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_4, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [_ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = $event => _ctx.scrollElementIntoView('#reviews'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginReviews.averageRating), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.numDownloads > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Downloads')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.numDownloadsPretty), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.lastUpdated && !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LastUpdated')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.lastUpdated), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Developer')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginOwner), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["modal-content__main", {
      'modal-content__main--with-free-trial': _ctx.showFreeTrialDropdown
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_12, [_ctx.showMissingRequirementsNoticeIfApplicable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MissingReqsNotice, {
    key: 0,
    plugin: _ctx.plugin
  }, null, 8, ["plugin"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_MultiServerEnvironmentWarning')), 1)) : !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AutoUpdateDisabledWarning', '\'[General]enable_auto_update=1\'', '\'config/config.ini.php\'')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showMissingLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseMissingDescription')), 1)) : _ctx.showExceededLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseExceededDescription')), 1)) : _ctx.plugin.licenseStatus === 'Pending' && !_ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 5,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getPendingLicenseHelpText(_ctx.plugin.displayName))
  }, null, 8, PluginDetailsModalvue_type_template_id_2b416103_hoisted_17)) : _ctx.plugin.licenseStatus === 'Cancelled' && !_ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 6,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getCancelledLicenseHelpText(_ctx.plugin.displayName))
  }, null, 8, PluginDetailsModalvue_type_template_id_2b416103_hoisted_18)) : !_ctx.plugin.hasDownloadLink && !_ctx.isMultiServerEnvironment && (_ctx.plugin.licenseStatus || !_ctx.plugin.isPaid) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 7,
    class: "alert alert-warning",
    innerHTML: _ctx.$sanitize(_ctx.getDownloadLinkMissingHelpText(_ctx.plugin.displayName))
  }, null, 8, PluginDetailsModalvue_type_template_id_2b416103_hoisted_19)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginDescription)
  }, null, 8, PluginDetailsModalvue_type_template_id_2b416103_hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_21, [_hoisted_22, _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [!_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginKeywords ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginKeywords')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginKeywords.join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Authors')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginAuthors, (author, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: `author-${index}`
    }, [author.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      target: "_blank",
      rel: "noreferrer noopener",
      href: author.homepage
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, _hoisted_27)) : author.email && _ctx.isValidEmail(author.email) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      href: `mailto:${encodeURIComponent(author.email)}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, _hoisted_28)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 1)), index < _ctx.pluginAuthors.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_30, ", ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Websites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [_ctx.plugin.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.plugin.homepage
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginWebsite')), 9, _hoisted_32)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginChangelogUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_ctx.plugin.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.pluginChangelogUrl)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Changelog')), 9, _hoisted_33)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.repositoryUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [_ctx.plugin.homepage || _ctx.pluginChangelogUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.plugin.repositoryUrl)
  }, "GitHub", 8, _hoisted_34)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.pluginActivity && _ctx.pluginActivity.numCommits ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activity')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.activity.numCommits) + " commits ", 1), ((_ctx$pluginActivity = _ctx.pluginActivity) === null || _ctx$pluginActivity === void 0 ? void 0 : _ctx$pluginActivity.numContributors) > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' ' + _ctx.translate('Marketplace_ByXDevelopers', _ctx.pluginActivity.numContributors)), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$pluginActivity2 = _ctx.pluginActivity) !== null && _ctx$pluginActivity2 !== void 0 && _ctx$pluginActivity2.lastCommitDate ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' ' + _ctx.translate('Marketplace_LastCommitTime', _ctx.pluginActivity.lastCommitDate)), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showLicenseName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_36, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_License')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [(_ctx$pluginLatestVers = _ctx.pluginLatestVersion.license) !== null && _ctx$pluginLatestVers !== void 0 && _ctx$pluginLatestVers.url ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    href: (_ctx$pluginLatestVers2 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers2 === void 0 ? void 0 : _ctx$pluginLatestVers2.url,
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers3 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers3 === void 0 ? void 0 : _ctx$pluginLatestVers3.name), 9, _hoisted_37)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_38, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers4 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers4 === void 0 ? void 0 : _ctx$pluginLatestVers4.name), 1))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginSupport.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginSupport, (support, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "pair",
      key: `support-${index}`
    }, [support.name && support.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", {
      innerHTML: _ctx.$sanitize(support.name)
    }, null, 8, _hoisted_39), this.isValidHttpUrl(support.value) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", _hoisted_40, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.externalRawLink(_ctx.$sanitize(support.value))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.$sanitize(support.value)), 9, _hoisted_41)])) : this.isValidEmail(support.value) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: `mailto:${encodeURIComponent(support.value)}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.$sanitize(support.value)), 9, _hoisted_43)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", {
      key: 2,
      innerHTML: _ctx.$sanitize(support.value)
    }, null, 8, _hoisted_44))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.pluginScreenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_45, [_hoisted_46, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Screenshots')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_47, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginScreenshots, screenshot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("figure", {
      key: `screenshot-${screenshot}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: `${screenshot}?w=800`,
      width: "800",
      alt: ""
    }, null, 8, _hoisted_48), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("figcaption", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(this.getScreenshotBaseName(screenshot)), 1)]);
  }), 128))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginDocumentation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_49, [_hoisted_50, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Documentation')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginDocumentation)
  }, null, 8, _hoisted_51)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginFaq ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_52, [_hoisted_53, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginFaq)
  }, null, 8, _hoisted_54)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_55, [_hoisted_56, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("iframe", {
    class: "reviewIframe",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.pluginReviews.height ? `height: ${_ctx.pluginReviews.height}px;` : ''),
    id: _ctx.pluginReviews.embedUrl.replace(/[\W_]+/g, ' '),
    src: _ctx.pluginReviews.embedUrl
  }, null, 12, _hoisted_57)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["modal-content__footer", {
      'modal-content__footer--with-free-trial': _ctx.showFreeTrialDropdown
    }])
  }, [_ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_58)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_59, [_ctx.showFreeTrialDropdown ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_60, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TryFreeTrialTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "free-trial-dropdown",
    title: `${_ctx.translate('Marketplace_ShownPriceIsExclTax')} ${_ctx.translate('Marketplace_CurrentNumPiwikUsers', _ctx.numUsers)}`,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.selectedPluginShopVariationUrl = $event),
    onChange: _cache[2] || (_cache[2] = (...args) => _ctx.changeSelectedPluginShopVariationUrl && _ctx.changeSelectedPluginShopVariationUrl(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugin.shop.variations, (variation, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: `var-${index}`,
      value: variation.addToCartUrl,
      title: `${_ctx.translate('Marketplace_PriceExclTax', variation.price, variation.currency)} ${_ctx.translate('Marketplace_CurrentNumPiwikUsers', _ctx.numUsers)}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.name) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.prettyPrice) + " / " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.period), 9, _hoisted_63);
  }), 128))], 40, _hoisted_62), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelSelect"], _ctx.selectedPluginShopVariationUrl]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
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
    onRequestTrial: _cache[3] || (_cache[3] = $event => _ctx.emitTrialEvent('requestTrial')),
    onStartFreeTrial: _cache[4] || (_cache[4] = $event => _ctx.emitTrialEvent('startFreeTrial'))
  }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "shop-variation-url"])]), !_ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2b416103

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=script&lang=ts
function PluginDetailsModalvue_type_script_lang_ts_extends() { PluginDetailsModalvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return PluginDetailsModalvue_type_script_lang_ts_extends.apply(this, arguments); }




const {
  $: PluginDetailsModalvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var PluginDetailsModalvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MissingReqsNotice: MissingReqsNotice,
    CTAContainer: CTAContainer
  },
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
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
      currentPluginShopVariationUrl: ''
    };
  },
  emits: ['requestTrial', 'startFreeTrial', 'update:modelValue'],
  watch: {
    modelValue(newValue) {
      if (newValue) {
        this.showPluginDetailsDialog();
      }
    },
    isLoading(newValue) {
      if (newValue === false) {
        this.applyExternalTarget();
        this.applyIframeResize();
      }
    }
  },
  computed: {
    plugin() {
      return this.modelValue;
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
    pluginAuthors() {
      const authors = this.plugin.authors || [];
      return authors.filter(author => author.name);
    },
    pluginActivity() {
      return this.plugin.activity || {};
    },
    pluginChangelogUrl() {
      return this.plugin.changelog.url || '';
    },
    pluginSupport() {
      return this.plugin.support || [];
    },
    isMatomoPlugin() {
      return ['piwik', 'matomo-org'].includes(this.plugin.owner);
    },
    pluginOwner() {
      return this.isMatomoPlugin ? 'Matomo' : this.plugin.owner;
    },
    showReviews() {
      return this.pluginReviews && this.pluginReviews.embedUrl && this.pluginReviews.averageRating;
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
      var _this$pluginLatestVer2;
      const license = ((_this$pluginLatestVer2 = this.pluginLatestVersion) === null || _this$pluginLatestVer2 === void 0 ? void 0 : _this$pluginLatestVer2.license) || {};
      return !!license.name;
    },
    showFreeTrialDropdown() {
      return this.isSuperUser && !this.plugin.isMissingLicense && !this.plugin.isInstalled && !this.plugin.hasExceededLicense && this.plugin.isEligibleForFreeTrial;
    },
    pluginScreenshots() {
      return this.plugin.screenshots || [];
    },
    hasHeaderMetadata() {
      return this.showReviews || !this.plugin.isBundle || (this.plugin.numDownloads || 0) > 0 || this.plugin.lastUpdated && !this.plugin.isBundle;
    },
    pluginShopVariationsPretty() {
      return this.pluginShopVariations.map(variation => `${variation.name} - ${variation.prettyPrice} / ${variation.period}`);
    },
    pluginShopRecommendedVariation() {
      const recommendedVariations = this.pluginShopVariations.filter(v => v.recommended);
      const defaultVariation = this.pluginShopVariations.length ? this.pluginShopVariations[0] : null;
      return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
    },
    selectedPluginShopVariationUrl() {
      var _this$pluginShopRecom;
      return this.currentPluginShopVariationUrl ? this.currentPluginShopVariationUrl : ((_this$pluginShopRecom = this.pluginShopRecommendedVariation) === null || _this$pluginShopRecom === void 0 ? void 0 : _this$pluginShopRecom.addToCartUrl) || '';
    },
    selectedShopVariationUrl() {
      return this.selectedPluginShopVariationUrl || '';
    }
  },
  methods: {
    changeSelectedPluginShopVariationUrl(event) {
      if (event) {
        this.currentPluginShopVariationUrl = event.target.value;
      }
    },
    applyExternalTarget() {
      setTimeout(() => {
        const root = this.$refs.root;
        PluginDetailsModalvue_type_script_lang_ts_$('.modal-content__main a', root).each((index, a) => {
          const link = PluginDetailsModalvue_type_script_lang_ts_$(a).attr('href');
          if (link && link.indexOf('http') === 0) {
            PluginDetailsModalvue_type_script_lang_ts_$(a).attr('target', '_blank');
          }
        });
      });
    },
    scrollElementIntoView(selector) {
      setTimeout(() => {
        const root = this.$refs.root;
        const elements = PluginDetailsModalvue_type_script_lang_ts_$(selector, root);
        if (elements.length && elements[0] && elements[0].scrollIntoView) {
          elements[0].scrollIntoView({
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
    isValidHttpUrl(input) {
      try {
        const url = new URL(input);
        return url.protocol === 'http:' || url.protocol === 'https:';
      } catch (err) {
        return false;
      }
    },
    getProtocolAndDomain(url) {
      const urlObj = new URL(url);
      return `${urlObj.protocol}//${urlObj.hostname}`;
    },
    applyIframeResize() {
      setTimeout(() => {
        const {
          iFrameResize
        } = window;
        if (this.pluginReviews) {
          PluginDetailsModalvue_type_script_lang_ts_$(() => {
            const $iFrames = PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal iframe.reviewIframe');
            for (let i = 0; i < $iFrames.length; i += 1) {
              // eslint-disable-next-line max-len
              iFrameResize({
                checkOrigin: [this.getProtocolAndDomain(this.pluginReviews.embedUrl)]
              }, $iFrames[i]);
            }
          });
        }
      });
    },
    getScreenshotBaseName(screenshot) {
      const filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    emitTrialEvent(eventName) {
      const {
        plugin
      } = this;
      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal('close');
      setTimeout(() => {
        this.$emit(eventName, plugin);
      }, 250);
    },
    showPluginDetailsDialog() {
      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal({
        dismissible: true,
        onCloseEnd: () => {
          external_CoreHome_["MatomoUrl"].updateHash(PluginDetailsModalvue_type_script_lang_ts_extends(PluginDetailsModalvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
            showPlugin: null
          }));
          this.$emit('update:modelValue', null);
          this.isLoading = true;
        }
      }).modal('open');
      setTimeout(() => {
        this.isLoading = false;
      }, 10); // just to prevent showing the modal when the plugin data are not yet passed in
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
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue



PluginDetailsModalvue_type_script_lang_ts.render = PluginDetailsModalvue_type_template_id_2b416103_render

/* harmony default export */ var PluginDetailsModal = (PluginDetailsModalvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts
function PluginListvue_type_script_lang_ts_extends() { PluginListvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return PluginListvue_type_script_lang_ts_extends.apply(this, arguments); }






const {
  $: PluginListvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var PluginListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentUserEmail: String,
    pluginsToShow: {
      type: Array,
      required: true
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true
    },
    isSuperUser: {
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
    hasSomeAdminAccess: {
      type: Boolean,
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
    numUsers: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      showRequestTrialForPlugin: null,
      showStartFreeTrialForPlugin: null,
      showPluginDetailsForPlugin: null
    };
  },
  components: {
    PluginDetailsModal: PluginDetailsModal,
    CTAContainer: CTAContainer,
    RequestTrial: RequestTrial,
    StartFreeTrial: StartFreeTrial
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  watch: {
    pluginsToShow(newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        this.shrinkDescriptionIfMultilineTitle();
        this.parseShowPluginParameter();
      }
    }
  },
  mounted() {
    PluginListvue_type_script_lang_ts_$(window).resize(() => {
      this.shrinkDescriptionIfMultilineTitle();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashParsed.value.showPlugin, (newValue, oldValue) => {
      if (newValue && newValue !== oldValue) {
        this.parseShowPluginParameter();
      }
    });
    this.parseShowPluginParameter();
  },
  methods: {
    parseShowPluginParameter() {
      const {
        showPlugin,
        pluginType,
        query
      } = external_CoreHome_["MatomoUrl"].hashParsed.value;
      if (!showPlugin) {
        return;
      }
      const pluginToShow = this.pluginsToShow.filter(
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      plugin => plugin.name === showPlugin);
      if (pluginToShow.length === 1) {
        const [plugin] = pluginToShow;
        this.openDetailsModal(plugin);
        this.scrollPluginCardIntoView(plugin);
      } else if (pluginType !== '' || query !== '') {
        // plugin was not found in current list, so unset filters to retry
        external_CoreHome_["MatomoUrl"].updateHash(PluginListvue_type_script_lang_ts_extends(PluginListvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          pluginType: 'plugins',
          query: null
        }));
      }
    },
    shrinkDescriptionIfMultilineTitle() {
      const $nodes = PluginListvue_type_script_lang_ts_$('.marketplace .card-holder');
      if (!$nodes || !$nodes.length) {
        return;
      }
      $nodes.each((index, node) => {
        const $card = PluginListvue_type_script_lang_ts_$(node);
        const $titleText = $card.find('.card-title');
        const $alertText = $card.find('.card-content-bottom .alert');
        const hasDownloads = $card.hasClass('card-with-downloads');
        let titleLines = 1;
        if ($titleText.length) {
          const elHeight = +$titleText.height();
          const lineHeight = +$titleText.css('line-height').replace('px', '');
          if (lineHeight) {
            var _Math$ceil;
            titleLines = (_Math$ceil = Math.ceil(elHeight / lineHeight)) !== null && _Math$ceil !== void 0 ? _Math$ceil : 1;
          }
        }
        let alertLines = 0;
        if ($alertText.length) {
          const elHeight = +$alertText.height();
          const lineHeight = +$alertText.css('line-height').replace('px', '');
          if (lineHeight) {
            var _Math$ceil2;
            alertLines = (_Math$ceil2 = Math.ceil(elHeight / lineHeight)) !== null && _Math$ceil2 !== void 0 ? _Math$ceil2 : 1;
          }
        }
        const $cardDescription = $card.find('.card-description');
        if ($cardDescription.length) {
          const cardDescription = $cardDescription[0];
          let clampedLines = 0;
          // a bit convoluted logic, but this is what's been arrived at with a designer
          // and via testing in browser
          //
          // a) visible downloads count
          //    -> clamp to 2 lines if title is 2 lines or more or alert is 2 lines or more
          //       or together are more than 3 lines
          //    -> clamp to 1 line if title is over 2 lines and alert is over 2 lines simultaneously
          // b) no downloads count (i.e. a premium plugin)
          //    -> clamp to 2 lines if sum of lines for title and notification is over 4
          if (hasDownloads) {
            if (titleLines >= 2 || alertLines > 2 || titleLines + alertLines >= 4) {
              clampedLines = 2;
            }
            if (titleLines + alertLines >= 5) {
              clampedLines = 1;
            }
          } else if (titleLines + alertLines >= 5) {
            clampedLines = 2;
          }
          if (clampedLines) {
            cardDescription.setAttribute('data-clamp', `${clampedLines}`);
          } else {
            cardDescription.removeAttribute('data-clamp');
          }
        }
      });
    },
    clickCard(event, plugin) {
      // check if the target is a link or is a descendant of a link
      // to skip direct clicks on links within the card, we want those honoured
      if (PluginListvue_type_script_lang_ts_$(event.target).closest('a:not(.card-title-link)').length) {
        return;
      }
      event.stopPropagation();
      this.openDetailsModal(plugin);
    },
    openDetailsModal(plugin) {
      this.showPluginDetailsForPlugin = plugin;
    },
    scrollPluginCardIntoView(plugin) {
      const $titles = PluginListvue_type_script_lang_ts_$(`.pluginListContainer .card-title:contains("${plugin.displayName}")`);
      if ($titles.length !== 1) {
        return;
      }
      const $cards = $titles.parents('.card');
      if ($cards.length !== 1 || !$cards[0].scrollIntoView) {
        return;
      }
      $cards[0].scrollIntoView({
        block: 'start',
        behavior: 'smooth'
      });
    },
    requestTrial(plugin) {
      this.showRequestTrialForPlugin = plugin;
    },
    startFreeTrial(plugin) {
      this.showStartFreeTrialForPlugin = plugin;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue



PluginListvue_type_script_lang_ts.render = PluginListvue_type_template_id_9a1e2784_render

/* harmony default export */ var PluginList = (PluginListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts
function Marketplacevue_type_script_lang_ts_extends() { Marketplacevue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return Marketplacevue_type_script_lang_ts_extends.apply(this, arguments); }




const lcfirst = s => `${s[0].toLowerCase()}${s.substring(1)}`;
/* harmony default export */ var Marketplacevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    pluginTypeOptions: {
      type: Object,
      required: true
    },
    defaultSort: {
      type: String,
      required: true
    },
    pluginSortOptions: {
      type: Object,
      required: true
    },
    numAvailablePluginsByType: {
      type: Object,
      required: true
    },
    currentUserEmail: String,
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
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    PluginList: PluginList,
    Field: external_CorePluginsAdmin_["Field"]
  },
  data() {
    return {
      loading: false,
      fetchRequest: null,
      fetchRequestAbortController: null,
      pluginSort: this.defaultSort,
      pluginTypeFilter: 'plugins',
      searchQuery: '',
      pluginsToShow: []
    };
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  mounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.mounted', {
      element: this.$refs.root
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashParsed.value, () => {
      this.updateValuesFromHash(false);
    });
    this.updateValuesFromHash(true);
  },
  unmounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.unmounted', {
      element: this.$refs.root
    });
  },
  methods: {
    updateValuesFromHash(forceFetch) {
      let doFetch = forceFetch;
      const newSearchQuery = external_CoreHome_["MatomoUrl"].hashParsed.value.query || '';
      const newPluginSort = external_CoreHome_["MatomoUrl"].hashParsed.value.sort || '';
      const newPluginTypeFilter = external_CoreHome_["MatomoUrl"].hashParsed.value.pluginType || '';
      if (newSearchQuery || this.searchQuery) {
        doFetch = doFetch || newSearchQuery !== this.searchQuery;
        this.searchQuery = newSearchQuery;
      }
      if (newPluginSort) {
        doFetch = doFetch || newPluginSort !== this.pluginSort;
        this.pluginSort = newPluginSort;
      }
      if (newPluginTypeFilter) {
        doFetch = doFetch || newPluginTypeFilter !== this.pluginTypeFilter;
        this.pluginTypeFilter = newPluginTypeFilter;
      }
      if (!doFetch) {
        return;
      }
      this.fetchPlugins();
    },
    updateQuery(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Marketplacevue_type_script_lang_ts_extends(Marketplacevue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        query: event
      }));
    },
    updateType(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Marketplacevue_type_script_lang_ts_extends(Marketplacevue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        pluginType: event
      }));
    },
    updateSort(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Marketplacevue_type_script_lang_ts_extends(Marketplacevue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        sort: event
      }));
    },
    updateMarketplace() {
      this.fetchPlugins(() => this.$emit('triggerUpdate'));
    },
    fetchPlugins(cb) {
      this.loading = true;
      this.pluginsToShow = [];
      if (this.fetchRequestAbortController) {
        this.fetchRequestAbortController.abort();
        this.fetchRequestAbortController = null;
      }
      this.fetchRequestAbortController = new AbortController();
      this.fetchRequest = external_CoreHome_["AjaxHelper"].post({
        module: 'Marketplace',
        action: 'searchPlugins',
        format: 'JSON'
      }, {
        query: this.searchQuery,
        sort: this.pluginSort,
        themesOnly: this.showThemes,
        purchaseType: this.pluginTypeFilter === 'premium' ? 'paid' : ''
      }, {
        withTokenInUrl: true,
        abortController: this.fetchRequestAbortController
      }).then(response => {
        this.pluginsToShow = response;
        if (typeof cb === 'function') {
          cb();
        }
      }).finally(() => {
        this.loading = false;
        this.fetchRequestAbortController = null;
      });
    }
  },
  computed: {
    queryInputTitle() {
      const plugins = lcfirst(Object(external_CoreHome_["translate"])('General_Plugins'));
      const pluginCount = this.numAvailablePluginsByType[this.pluginTypeFilter] || 0;
      return `${Object(external_CoreHome_["translate"])('General_Search')} ${pluginCount} ${plugins}...`;
    },
    loadingMessage() {
      return Object(external_CoreHome_["translate"])('Mobile_LoadingReport', Object(external_CoreHome_["translate"])(this.showThemes ? 'CorePluginsAdmin_Themes' : 'General_Plugins'));
    },
    showThemes() {
      return this.pluginTypeFilter === 'themes';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue



Marketplacevue_type_script_lang_ts.render = render

/* harmony default export */ var Marketplace = (Marketplacevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=template&id=1d0cceb8

const InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_1 = ["disabled"];
const InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_2 = {
  class: "ui-confirm",
  id: "installAllPaidPluginsAtOnce",
  ref: "installAllPaidPluginsAtOnce"
};
const InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_3 = ["data-href", "value"];
const InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_4 = ["value"];
function InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onInstallAllPaidPlugins(), ["prevent"])),
    disabled: _ctx.disabled
  }, [_ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MatomoLoader, {
    key: 0
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallPurchasedPlugins')), 1)], 8, InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallAllPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallThesePlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.paidPluginsToInstallAtOnce, pluginDisplayName => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: pluginDisplayName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(pluginDisplayName), 1);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "install",
    type: "button",
    "data-href": _ctx.installAllPaidPluginsLink,
    value: _ctx.translate('Marketplace_InstallAllPurchasedPluginsAction', _ctx.paidPluginsToInstallAtOnce.length)
  }, null, 8, InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "cancel",
    type: "button",
    value: _ctx.translate('General_Cancel')
  }, null, 8, InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_hoisted_4)])], 512)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=template&id=1d0cceb8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=script&lang=ts
function InstallAllPaidPluginsButtonvue_type_script_lang_ts_extends() { InstallAllPaidPluginsButtonvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return InstallAllPaidPluginsButtonvue_type_script_lang_ts_extends.apply(this, arguments); }


/* harmony default export */ var InstallAllPaidPluginsButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoLoader: external_CoreHome_["MatomoLoader"]
  },
  props: {
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  methods: {
    onInstallAllPaidPlugins() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce);
    }
  },
  computed: {
    installAllPaidPluginsLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(InstallAllPaidPluginsButtonvue_type_script_lang_ts_extends(InstallAllPaidPluginsButtonvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'installAllPaidPlugins',
        nonce: this.installNonce
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue



InstallAllPaidPluginsButtonvue_type_script_lang_ts.render = InstallAllPaidPluginsButtonvue_type_template_id_1d0cceb8_render

/* harmony default export */ var InstallAllPaidPluginsButton = (InstallAllPaidPluginsButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=8dd04b4e

const ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_1 = ["innerHTML"];
const ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_2 = {
  class: "manage-license-key-input"
};
const ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_3 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
const ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_4 = ["value"];
const ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_5 = ["value"];
function ManageLicenseKeyvue_type_template_id_8dd04b4e_render(_ctx, _cache, $props, $setup, $data, $options) {
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
    }, null, 8, ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_5)], 512)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=8dd04b4e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts
function ManageLicenseKeyvue_type_script_lang_ts_extends() { ManageLicenseKeyvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ManageLicenseKeyvue_type_script_lang_ts_extends.apply(this, arguments); }



/* harmony default export */ var ManageLicenseKeyvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hasValidLicenseKey: Boolean
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
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
      const marketplaceLink = `?${external_CoreHome_["MatomoUrl"].stringify(ManageLicenseKeyvue_type_script_lang_ts_extends(ManageLicenseKeyvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
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



ManageLicenseKeyvue_type_script_lang_ts.render = ManageLicenseKeyvue_type_template_id_8dd04b4e_render

/* harmony default export */ var ManageLicenseKey = (ManageLicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=f1842798

const GetNewPluginsvue_type_template_id_f1842798_hoisted_1 = {
  class: "getNewPlugins"
};
const GetNewPluginsvue_type_template_id_f1842798_hoisted_2 = {
  class: "row"
};
const GetNewPluginsvue_type_template_id_f1842798_hoisted_3 = {
  class: "pluginName"
};
const GetNewPluginsvue_type_template_id_f1842798_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_f1842798_hoisted_5 = {
  key: 0
};
const GetNewPluginsvue_type_template_id_f1842798_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_f1842798_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsvue_type_template_id_f1842798_hoisted_8 = [GetNewPluginsvue_type_template_id_f1842798_hoisted_6, GetNewPluginsvue_type_template_id_f1842798_hoisted_7];
const GetNewPluginsvue_type_template_id_f1842798_hoisted_9 = {
  class: "widgetBody"
};
const GetNewPluginsvue_type_template_id_f1842798_hoisted_10 = ["href"];
function GetNewPluginsvue_type_template_id_f1842798_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, (plugin, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", GetNewPluginsvue_type_template_id_f1842798_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)])), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetNewPluginsvue_type_template_id_f1842798_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)])), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])]), index < _ctx.plugins.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsvue_type_template_id_f1842798_hoisted_5, GetNewPluginsvue_type_template_id_f1842798_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsvue_type_template_id_f1842798_hoisted_10)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=f1842798

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts
function GetNewPluginsvue_type_script_lang_ts_extends() { GetNewPluginsvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return GetNewPluginsvue_type_script_lang_ts_extends.apply(this, arguments); }



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
      return `?${external_CoreHome_["MatomoUrl"].stringify(GetNewPluginsvue_type_script_lang_ts_extends(GetNewPluginsvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue



GetNewPluginsvue_type_script_lang_ts.render = GetNewPluginsvue_type_template_id_f1842798_render

/* harmony default export */ var GetNewPlugins = (GetNewPluginsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=b01ab65c

const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_1 = {
  class: "getNewPlugins isAdminPage",
  ref: "root"
};
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_2 = {
  class: "row"
};
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_3 = ["title"];
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_4 = ["title"];
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_5 = {
  key: 0
};
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_7 = ["src"];
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_8 = {
  class: "widgetBody"
};
const GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_9 = ["href"];
function GetNewPluginsAdminvue_type_template_id_b01ab65c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, plugin => {
    var _plugin$screenshots;
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
      class: "pluginName",
      title: plugin.description
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 8, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_3)), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      class: "description",
      title: plugin.description
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 9, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_4), (_plugin$screenshots = plugin.screenshots) !== null && _plugin$screenshots !== void 0 && _plugin$screenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_5, [GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      class: "screenshot",
      src: `${plugin.screenshots[0]}?w=600`,
      style: {
        "width": "100%"
      },
      alt: ""
    }, null, 8, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_7), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.marketplaceOverviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_9)])], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=b01ab65c

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
      return `?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      })}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue



GetNewPluginsAdminvue_type_script_lang_ts.render = GetNewPluginsAdminvue_type_template_id_b01ab65c_render

/* harmony default export */ var GetNewPluginsAdmin = (GetNewPluginsAdminvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=0ec62128

const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_1 = {
  class: "getNewPlugins getPremiumFeatures widgetBody"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_2 = {
  key: 0,
  class: "col s12 m12"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_3 = ["innerHTML"];
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_4 = {
  style: {
    "margin-bottom": "28px",
    "color": "#5bb75b"
  }
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-heart red-text"
}, null, -1);
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_6 = {
  class: "pluginName"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_7 = {
  key: 0,
  class: "pluginSubtitle"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_8 = {
  class: "pluginBody"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_10 = {
  class: "pluginMoreDetails"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_11 = {
  class: "widgetBody"
};
const GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_12 = ["href"];
function GetPremiumFeaturesvue_type_template_id_0ec62128_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginRows, (rowOfPlugins, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [index === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      style: {
        "font-weight": "bold",
        "color": "#5bb75b"
      },
      innerHTML: _ctx.$sanitize(_ctx.trialHintsText)
    }, null, 8, GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SupportMatomoThankYou')) + " ", 1), GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_5])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(rowOfPlugins, plugin => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "col s12 m4",
        key: plugin.name
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)])), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]]), plugin.specialOffer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SpecialOffer')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.specialOffer), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isBundle ? `${_ctx.translate('Marketplace_SpecialOffer')}: ` : '') + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)])), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]])])]);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_12)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=0ec62128

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
      const query = external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      });
      const hash = external_CoreHome_["MatomoUrl"].stringify({
        pluginType: 'premium'
      });
      return `?${query}#?${hash}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue



GetPremiumFeaturesvue_type_script_lang_ts.render = GetPremiumFeaturesvue_type_template_id_0ec62128_render

/* harmony default export */ var GetPremiumFeatures = (GetPremiumFeaturesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=2f3f2403

const OverviewIntrovue_type_template_id_2f3f2403_hoisted_1 = {
  class: "marketplaceIntro"
};
const OverviewIntrovue_type_template_id_2f3f2403_hoisted_2 = {
  key: 0
};
const OverviewIntrovue_type_template_id_2f3f2403_hoisted_3 = {
  key: 1
};
const OverviewIntrovue_type_template_id_2f3f2403_hoisted_4 = {
  key: 0,
  class: "installAllPaidPlugins"
};
function OverviewIntrovue_type_template_id_2f3f2403_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");
  const _component_Marketplace = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Marketplace");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.translate('CorePluginsAdmin_Marketplace')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)]),
    _: 1
  }, 8, ["feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OverviewIntrovue_type_template_id_2f3f2403_hoisted_1, [!_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", OverviewIntrovue_type_template_id_2f3f2403_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Intro')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", OverviewIntrovue_type_template_id_2f3f2403_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_IntroSuperUser')), 1))]), _ctx.installAllPaidPluginsVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OverviewIntrovue_type_template_id_2f3f2403_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton, {
    "paid-plugins-to-install-at-once": _ctx.getPaidPluginsToInstallAtOnce,
    "install-nonce": _ctx.installNonce,
    disabled: _ctx.installDisabled,
    loading: _ctx.installLoading
  }, null, 8, ["paid-plugins-to-install-at-once", "install-nonce", "disabled", "loading"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Marketplace, {
    "plugin-type-options": _ctx.pluginTypeOptions,
    "default-sort": _ctx.defaultSort,
    "plugin-sort-options": _ctx.pluginSortOptions,
    "num-available-plugins-by-type": _ctx.numAvailablePluginsByType,
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
  }, null, 8, ["plugin-type-options", "default-sort", "plugin-sort-options", "num-available-plugins-by-type", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "has-some-admin-access", "num-users"])])), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=2f3f2403

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts




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
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true
    },
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
    pluginTypeOptions: {
      type: Object,
      required: true
    },
    defaultSort: {
      type: String,
      required: true
    },
    pluginSortOptions: {
      type: Object,
      required: true
    },
    numAvailablePluginsByType: {
      type: Object,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    }
  },
  components: {
    InstallAllPaidPluginsButton: InstallAllPaidPluginsButton,
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
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
  computed: {
    getIsValidConsumer() {
      return this.updateData && typeof this.updateData.isValidConsumer !== 'undefined' ? this.updateData.isValidConsumer : this.isValidConsumer;
    },
    getPaidPluginsToInstallAtOnce() {
      return this.updateData && typeof this.updateData.paidPluginsToInstallAtOnce !== 'undefined' ? this.updateData.paidPluginsToInstallAtOnce : this.paidPluginsToInstallAtOnce;
    },
    installAllPaidPluginsVisible() {
      var _this$getPaidPluginsT;
      return this.getIsValidConsumer && this.isSuperUser && this.isAutoUpdatePossible && this.isPluginsAdminEnabled && ((_this$getPaidPluginsT = this.getPaidPluginsToInstallAtOnce) === null || _this$getPaidPluginsT === void 0 ? void 0 : _this$getPaidPluginsT.length) || this.installDisabled && this.installLoading;
    },
    showThemes() {
      return external_CoreHome_["MatomoUrl"].hashParsed.value.pluginType === 'themes';
    }
  },
  methods: {
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



OverviewIntrovue_type_script_lang_ts.render = OverviewIntrovue_type_template_id_2f3f2403_render

/* harmony default export */ var OverviewIntro = (OverviewIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=0308ba50

const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_1 = {
  key: 0
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_2 = ["href"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_5 = ["innerHTML"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_7 = {
  class: "subscriptionName"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_8 = ["href"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_9 = {
  key: 1
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_10 = {
  class: "subscriptionType"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_11 = ["title"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_12 = {
  key: 0,
  class: "icon-error"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_13 = {
  key: 1,
  class: "icon-warning"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_14 = {
  key: 2,
  class: "icon-error"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_15 = {
  key: 3,
  class: "icon-ok"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_16 = ["title"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_18 = {
  key: 0
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_19 = {
  colspan: "6"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_20 = {
  class: "tableActionBar"
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_21 = ["href"];
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-table"
}, null, -1);
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_23 = {
  key: 1
};
const SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_24 = ["innerHTML"];
function SubscriptionOverviewvue_type_template_id_0308ba50_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_OverviewPluginSubscriptions'),
    class: "subscriptionOverview"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.hasLicenseKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginSubscriptionsList')) + " ", 1), _ctx.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.loginUrl
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsAllDetails')), 9, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsMissingInfo')) + " ", 1), SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoValidSubscriptionNoUpdates')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_CurrentNumPiwikUsers', `<strong>${_ctx.numUsers}</strong>`))
    }, null, 8, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_5)]), SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionType')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionStartDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionEndDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionNextPaymentDate')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.subscriptions || [], (subscription, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_7, [subscription.plugin.htmlUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        href: subscription.plugin.htmlUrl,
        rel: "noreferrer noopener",
        target: "_blank"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 9, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.productType), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
        class: "subscriptionStatus",
        title: _ctx.getSubscriptionStatusTitle(subscription)
      }, [!subscription.isValid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_12)) : subscription.isExpiredSoon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_13)) : subscription.status !== '' && subscription.status !== 'Active' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_14)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_15)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.status) + " ", 1), subscription.isExceeded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 4,
        class: "errorMessage",
        title: _ctx.translate('Marketplace_LicenseExceededPossibleCause')
      }, [SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Exceeded')), 1)], 8, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.start), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.isValid && subscription.nextPayment ? _ctx.translate('Marketplace_LicenseRenewsNextPaymentDate') : subscription.end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.nextPayment), 1)]);
    }), 128)), !_ctx.subscriptions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoSubscriptionsFound')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.marketplaceOverviewLink,
      class: ""
    }, [SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BrowseMarketplace')), 1)], 8, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_21)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
    }, null, 8, SubscriptionOverviewvue_type_template_id_0308ba50_hoisted_24)]))]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=0308ba50

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
      return `?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      })}`;
    },
    missingLicenseText() {
      return Object(external_CoreHome_["translate"])('Marketplace_OverviewPluginSubscriptionsMissingLicense', `<a href="${this.marketplaceOverviewLink}">`, '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue



SubscriptionOverviewvue_type_script_lang_ts.render = SubscriptionOverviewvue_type_template_id_0308ba50_render

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