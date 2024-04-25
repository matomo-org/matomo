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
__webpack_require__.d(__webpack_exports__, "LicenseKey", function() { return /* reexport */ LicenseKey; });
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=16f1c1c3

var _hoisted_1 = {
  class: "row marketplaceActions",
  ref: "root"
};
var _hoisted_2 = {
  class: "col s12 m6 l4"
};
var _hoisted_3 = {
  class: "col s12 m6 l4"
};
var _hoisted_4 = {
  key: 0,
  class: "col s12 m12 l4 "
};
var _hoisted_5 = {
  class: "plugin-search"
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search"
}, null, -1);

var _hoisted_7 = ["alt"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginsToShow,
      _this = this;

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_PluginList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginList");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

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
    onTriggerUpdate: _cache[0] || (_cache[0] = function ($event) {
      return _this.fetchPlugins();
    })
  }, null, 8, ["plugins-to-show", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "has-some-admin-access", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loading && _ctx.pluginsToShow.length == 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 1
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.showThemes ? 'Marketplace_NoThemesFound' : 'Marketplace_NoPluginsFound')), 1)];
    }),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 2
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
        src: "plugins/Morpheus/images/loading-blue.gif",
        alt: _ctx.translate('General_LoadingData')
      }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadingMessage), 1)];
    }),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=16f1c1c3

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=3404bfd4

var PluginListvue_type_template_id_3404bfd4_hoisted_1 = {
  key: 0,
  class: "pluginListContainer row"
};
var PluginListvue_type_template_id_3404bfd4_hoisted_2 = ["onClick"];
var PluginListvue_type_template_id_3404bfd4_hoisted_3 = {
  class: "card"
};
var PluginListvue_type_template_id_3404bfd4_hoisted_4 = {
  class: "card-content"
};
var PluginListvue_type_template_id_3404bfd4_hoisted_5 = ["src"];
var PluginListvue_type_template_id_3404bfd4_hoisted_6 = {
  class: "content-container"
};
var PluginListvue_type_template_id_3404bfd4_hoisted_7 = {
  class: "card-content-top"
};
var _hoisted_8 = {
  key: 0,
  class: "matomo-badge matomo-badge-top",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
var _hoisted_9 = {
  class: "price"
};
var _hoisted_10 = ["onClick"];

var _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "card-focus"
}, null, -1);

var _hoisted_12 = {
  class: "card-title"
};

var _hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "card-title-chevron"
}, " ›", -1);

var _hoisted_14 = {
  class: "card-description"
};
var _hoisted_15 = {
  class: "card-content-bottom"
};
var _hoisted_16 = {
  key: 0,
  class: "downloads"
};
var _hoisted_17 = {
  class: "cta-container"
};
var _hoisted_18 = {
  key: 1,
  class: "matomo-badge matomo-badge-bottom",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginListvue_type_template_id_3404bfd4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_StartFreeTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("StartFreeTrial");

  var _component_PluginDetailsModal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginDetailsModal");

  var _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_StartFreeTrial, {
    "current-user-email": _ctx.currentUserEmail,
    "is-valid-consumer": _ctx.isValidConsumer,
    modelValue: _ctx.showStartFreeTrialForPlugin,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showStartFreeTrialForPlugin = $event;
    }),
    onTrialStarted: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('triggerUpdate');
    })
  }, null, 8, ["current-user-email", "is-valid-consumer", "modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginDetailsModal, {
    modelValue: _ctx.showPluginDetailsForPlugin,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.showPluginDetailsForPlugin = $event;
    }),
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
    onStartFreeTrial: _cache[3] || (_cache[3] = function ($event) {
      return _this.showStartFreeTrialForPlugin = $event;
    })
  }, null, 8, ["modelValue", "is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "has-some-admin-access", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce"]), _ctx.pluginsToShow.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginListvue_type_template_id_3404bfd4_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginsToShow, function (plugin) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m6 l4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("card-holder ".concat(plugin.numDownloads > 0 ? 'card-with-downloads' : '')),
      onClick: function onClick($event) {
        return _ctx.clickCard($event, plugin);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_3404bfd4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_3404bfd4_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: "".concat(plugin.coverImage, "?w=880&h=480"),
      alt: "",
      class: "cover-image"
    }, null, 8, PluginListvue_type_template_id_3404bfd4_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_3404bfd4_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_3404bfd4_hoisted_7, ['piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [plugin.priceFrom ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PriceFromPerPeriod', plugin.priceFrom.prettyPrice, plugin.priceFrom.period)), 1)], 64)) : plugin.isFree ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Free')), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
        return _ctx.clickCard($event, plugin);
      }, ["prevent"]),
      class: "card-title-link",
      href: "#",
      tabindex: "7"
    }, [_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1), _hoisted_13])], 8, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [plugin.numDownloads > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.numDownloadsPretty) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Downloads').toLowerCase()), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
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
      onStartFreeTrial: function onStartFreeTrial($event) {
        return _ctx.showStartFreeTrialForPlugin = plugin.name;
      },
      onOpenDetailsModal: function onOpenDetailsModal($event) {
        return _this.openDetailsModal(plugin);
      }
    }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "onStartFreeTrial", "onOpenDetailsModal"])]), 'piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])])])], 10, PluginListvue_type_template_id_3404bfd4_hoisted_2)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=3404bfd4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=ec62eb30

var CTAContainervue_type_template_id_ec62eb30_hoisted_1 = {
  key: 0,
  class: "alert alert-danger alert-no-background"
};
var CTAContainervue_type_template_id_ec62eb30_hoisted_2 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_ec62eb30_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_ec62eb30_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_ec62eb30_hoisted_5 = {
  key: 1,
  class: "alert alert-danger alert-no-background"
};
var CTAContainervue_type_template_id_ec62eb30_hoisted_6 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_ec62eb30_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_ec62eb30_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_ec62eb30_hoisted_9 = ["href"];
var CTAContainervue_type_template_id_ec62eb30_hoisted_10 = {
  key: 1,
  class: "alert alert-warning alert-no-background"
};
var CTAContainervue_type_template_id_ec62eb30_hoisted_11 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_ec62eb30_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_ec62eb30_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_ec62eb30_hoisted_14 = {
  key: 3,
  class: "alert alert-success alert-no-background"
};

var CTAContainervue_type_template_id_ec62eb30_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" (");

var CTAContainervue_type_template_id_ec62eb30_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ");

var CTAContainervue_type_template_id_ec62eb30_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" (");

var CTAContainervue_type_template_id_ec62eb30_hoisted_18 = ["href"];

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ");

var _hoisted_20 = ["href"];

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ");

var _hoisted_22 = ["title"];
var _hoisted_23 = {
  key: 6,
  class: "alert alert-warning alert-no-background"
};
var _hoisted_24 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var _hoisted_27 = ["href"];
function CTAContainervue_type_template_id_ec62eb30_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MoreDetailsAction = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MoreDetailsAction");

  var _component_DownloadButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DownloadButton");

  return _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_ec62eb30_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseMissing')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_ec62eb30_hoisted_2, [CTAContainervue_type_template_id_ec62eb30_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }), CTAContainervue_type_template_id_ec62eb30_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.hasExceededLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_ec62eb30_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseExceeded')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_ec62eb30_hoisted_6, [CTAContainervue_type_template_id_ec62eb30_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }), CTAContainervue_type_template_id_ec62eb30_hoisted_8])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.canBeUpdated && 0 == _ctx.plugin.missingRequirements.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    class: "btn btn-block",
    href: _ctx.linkToUpdate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreUpdater_UpdateTitle')), 9, CTAContainervue_type_template_id_ec62eb30_hoisted_9)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_ec62eb30_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotUpdate')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_ec62eb30_hoisted_11, [CTAContainervue_type_template_id_ec62eb30_hoisted_12, !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), CTAContainervue_type_template_id_ec62eb30_hoisted_13])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]))], 64)) : _ctx.plugin.isInstalled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_ec62eb30_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Installed')) + " ", 1), _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [CTAContainervue_type_template_id_ec62eb30_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": false,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "is-auto-update-possible"]), CTAContainervue_type_template_id_ec62eb30_hoisted_16], 64)) : !_ctx.plugin.isInvalid && !_ctx.isMultiServerEnvironment && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [CTAContainervue_type_template_id_ec62eb30_hoisted_17, _ctx.plugin.isActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    href: _ctx.linkToDeactivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Deactivate')), 9, CTAContainervue_type_template_id_ec62eb30_hoisted_18)) : _ctx.plugin.missingRequirements.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [_hoisted_19], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    tabindex: "7",
    href: _ctx.linkToActivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activate')), 9, _hoisted_20)), _hoisted_21], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.isEligibleForFreeTrial ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 4,
    tabindex: "7",
    class: "btn btn-block purchaseable",
    href: "",
    onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.$emit('startFreeTrial');
    }, ["prevent"])),
    onKeyup: _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(function ($event) {
      return _ctx.$emit('startFreeTrial');
    }, ["enter"])),
    title: _ctx.translate('Marketplace_StartFreeTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_StartFreeTrial')), 41, _hoisted_22)) : !_ctx.inModal && !_ctx.plugin.isDownloadable && (_ctx.plugin.isPaid || _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 5,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }, null, 8, ["label"])) : _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotInstall')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_24, [_hoisted_25, !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), _hoisted_26])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 7,
    tabindex: "7",
    href: _ctx.linkToInstall(_ctx.plugin.name),
    class: "btn btn-block"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ActionInstall')), 9, _hoisted_27))], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64));
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=ec62eb30

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=template&id=201a6490

var DownloadButtonvue_type_template_id_201a6490_hoisted_1 = {
  key: 0,
  onclick: "$(this).css('display', 'none')"
};
var DownloadButtonvue_type_template_id_201a6490_hoisted_2 = ["href"];
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts


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
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/DownloadButton.vue



DownloadButtonvue_type_script_lang_ts.render = DownloadButtonvue_type_template_id_201a6490_render

/* harmony default export */ var DownloadButton = (DownloadButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=3596d57c

var MoreDetailsActionvue_type_template_id_3596d57c_hoisted_1 = ["title"];
function MoreDetailsActionvue_type_template_id_3596d57c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    tabindex: "7",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'btn btn-block': _ctx.showAsButton
    }),
    href: "",
    title: _ctx.translate('General_MoreDetails'),
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.$emit('action');
    }, ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(function ($event) {
      return _ctx.$emit('action');
    }, ["enter"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label ? _ctx.label : _ctx.translate('General_Help')), 43, MoreDetailsActionvue_type_template_id_3596d57c_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=template&id=3596d57c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/MoreDetailsAction.vue?vue&type=script&lang=ts

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts





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
    }
  },
  emits: ['startFreeTrial', 'openDetailsModal'],
  components: {
    MoreDetailsAction: MoreDetailsAction,
    DownloadButton: DownloadButton
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  methods: {
    linkToActivate: function linkToActivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'activate',
        redirectTo: 'referrer',
        nonce: this.activateNonce,
        pluginName: pluginName
      });
    },
    linkToDeactivate: function linkToDeactivate(pluginName) {
      return this.linkTo({
        module: 'CorePluginsAdmin',
        action: 'deactivate',
        redirectTo: 'referrer',
        nonce: this.deactivateNonce,
        pluginName: pluginName
      });
    },
    linkToInstall: function linkToInstall(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'installPlugin',
        nonce: this.installNonce,
        pluginName: pluginName
      });
    },
    linkToUpdate: function linkToUpdate(pluginName) {
      return this.linkTo({
        module: 'Marketplace',
        action: 'updatePlugin',
        nonce: this.updateNonce,
        pluginName: pluginName
      });
    },
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue



CTAContainervue_type_script_lang_ts.render = CTAContainervue_type_template_id_ec62eb30_render

/* harmony default export */ var CTAContainer = (CTAContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=74682556

var StartFreeTrialvue_type_template_id_74682556_hoisted_1 = {
  class: "modal",
  id: "startFreeTrial"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_2 = {
  key: 0,
  class: "btn-close modal-close"
};

var StartFreeTrialvue_type_template_id_74682556_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
}, null, -1);

var StartFreeTrialvue_type_template_id_74682556_hoisted_4 = [StartFreeTrialvue_type_template_id_74682556_hoisted_3];
var StartFreeTrialvue_type_template_id_74682556_hoisted_5 = {
  key: 1,
  class: "modal-content trial-start-in-progress"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_6 = {
  class: "Piwik_Popover_Loading"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_7 = {
  class: "Piwik_Popover_Loading_Name"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_8 = {
  key: 2,
  class: "modal-content trial-start-error"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_9 = {
  class: "modal-text"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_10 = {
  key: 3,
  class: "modal-content trial-start-no-license"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_11 = {
  class: "modal-text"
};
var StartFreeTrialvue_type_template_id_74682556_hoisted_12 = ["innerHTML"];
var StartFreeTrialvue_type_template_id_74682556_hoisted_13 = ["innerHTML"];
var StartFreeTrialvue_type_template_id_74682556_hoisted_14 = ["disabled"];
var StartFreeTrialvue_type_template_id_74682556_hoisted_15 = ["innerHTML"];
function StartFreeTrialvue_type_template_id_74682556_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_1, [!_ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", StartFreeTrialvue_type_template_id_74682556_hoisted_2, StartFreeTrialvue_type_template_id_74682556_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressText')), 1)])])])) : _ctx.trialStartError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trialStartError), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorSupport')), 1)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_74682556_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "email",
    modelValue: _ctx.createAccountEmail,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.createAccountEmail = $event;
    }),
    "full-width": true,
    title: _ctx.translate('UsersManager_Email')
  }, null, 8, ["modelValue", "title"]), _ctx.createAccountError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "alert alert-danger",
    innerHTML: _ctx.$sanitize(_ctx.createAccountError)
  }, null, 8, StartFreeTrialvue_type_template_id_74682556_hoisted_12)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "trial-start-legal-hint",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseLegalHintText)
  }, null, 8, StartFreeTrialvue_type_template_id_74682556_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn",
    disabled: !_ctx.createAccountEmail,
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.createAccountAndStartFreeTrial();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseCreateAccount')), 9, StartFreeTrialvue_type_template_id_74682556_hoisted_14)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "add-existing-license",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseAddHereText)
  }, null, 8, StartFreeTrialvue_type_template_id_74682556_hoisted_15)])]))]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=74682556

// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Periods/Periods.ts
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

/**
 * Matomo period management service for the frontend.
 *
 * Usage:
 *
 *     var DayPeriod = matomoPeriods.get('day');
 *     var day = new DayPeriod(new Date());
 *
 * or
 *
 *     var day = matomoPeriods.parse('day', '2013-04-05');
 *
 * Adding custom periods:
 *
 * To add your own period to the frontend, create a period class for it
 * w/ the following methods:
 *
 * - **getPrettyString()**: returns a human readable display string for the period.
 * - **getDateRange()**: returns an array w/ two elements, the first being the start
 *                       Date of the period, the second being the end Date. The dates
 *                       must be Date objects, not strings, and are inclusive.
 * - **containsToday()**: returns true if the date period contains today. False if not.
 * - (_static_) **parse(strDate)**: creates a new instance of this period from the
 *                                  value of the 'date' query parameter.
 * - (_static_) **getDisplayText**: returns translated text for the period, eg, 'month',
 *                                  'week', etc.
 *
 * Then call Periods.addCustomPeriod w/ your period class:
 *
 *     Periods.addCustomPeriod('mycustomperiod', MyCustomPeriod);
 *
 * NOTE: currently only single date periods like day, week, month year can
 *       be extended. Other types of periods that require a special UI to
 *       view/edit aren't, since there is currently no way to use a
 *       custom UI for a custom period.
 */
var Periods = /*#__PURE__*/function () {
  function Periods() {
    _classCallCheck(this, Periods);

    _defineProperty(this, "periods", {});

    _defineProperty(this, "periodOrder", []);
  }

  _createClass(Periods, [{
    key: "addCustomPeriod",
    value: function addCustomPeriod(name, periodClass) {
      if (this.periods[name]) {
        throw new Error("The \"".concat(name, "\" period already exists! It cannot be overridden."));
      }

      this.periods[name] = periodClass;
      this.periodOrder.push(name);
    }
  }, {
    key: "getAllLabels",
    value: function getAllLabels() {
      return Array().concat(this.periodOrder);
    }
  }, {
    key: "get",
    value: function get(strPeriod) {
      var periodClass = this.periods[strPeriod];

      if (!periodClass) {
        throw new Error("Invalid period label: ".concat(strPeriod));
      }

      return periodClass;
    }
  }, {
    key: "parse",
    value: function parse(strPeriod, strDate) {
      return this.get(strPeriod).parse(strDate);
    }
  }, {
    key: "isRecognizedPeriod",
    value: function isRecognizedPeriod(strPeriod) {
      return !!this.periods[strPeriod];
    }
  }]);

  return Periods;
}();

/* harmony default export */ var Periods_Periods = (new Periods());
// CONCATENATED MODULE: ./plugins/CoreHome/vue/src/Matomo/Matomo.ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var originalTitle;
var _window = window,
    piwik = _window.piwik,
    broadcast = _window.broadcast,
    piwikHelper = _window.piwikHelper;
piwik.helper = piwikHelper;
piwik.broadcast = broadcast;

piwik.updateDateInTitle = function updateDateInTitle(date, period) {
  if (!$('.top_controls #periodString').length) {
    return;
  } // Cache server-rendered page title


  originalTitle = originalTitle || document.title;

  if (originalTitle.indexOf(piwik.siteName) === 0) {
    var dateString = " - ".concat(Periods_Periods.parse(period, date).getPrettyString(), " ");
    document.title = "".concat(piwik.siteName).concat(dateString).concat(originalTitle.slice(piwik.siteName.length));
  }
};

piwik.hasUserCapability = function hasUserCapability(capability) {
  return Array.isArray(piwik.userCapabilities) && piwik.userCapabilities.indexOf(capability) !== -1;
};

piwik.on = function addMatomoEventListener(eventName, listener) {
  function listenerWrapper(evt) {
    listener.apply(void 0, _toConsumableArray(evt.detail)); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;
  window.addEventListener(eventName, listenerWrapper);
};

piwik.off = function removeMatomoEventListener(eventName, listener) {
  if (listener.wrapper) {
    window.removeEventListener(eventName, listener.wrapper);
  }
};

piwik.postEvent = function postMatomoEvent(eventName) {
  for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    args[_key - 1] = arguments[_key];
  }

  var event = new CustomEvent(eventName, {
    detail: args
  });
  window.dispatchEvent(event);
};

var Matomo = piwik;
/* harmony default export */ var Matomo_Matomo = (Matomo);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=script&lang=ts




var StartFreeTrialvue_type_script_lang_ts_window = window,
    StartFreeTrialvue_type_script_lang_ts_$ = StartFreeTrialvue_type_script_lang_ts_window.$;
/* harmony default export */ var StartFreeTrialvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  props: {
    modelValue: {
      type: String,
      required: true
    },
    currentUserEmail: String,
    isValidConsumer: Boolean
  },
  data: function data() {
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
  emits: ['update:modelValue', 'trialStarted'],
  watch: {
    modelValue: function modelValue(newValue) {
      if (!newValue) {
        return;
      }

      if (this.isValidConsumer) {
        this.trialStartSuccessNotificationMessage = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStarted', '<strong>', '</strong>', newValue);
        this.startFreeTrial();
      } else {
        this.trialStartSuccessNotificationTitle = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle');
        this.trialStartSuccessNotificationMessage = Object(external_CoreHome_["translate"])('CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage', newValue);
        this.showLicenseDialog(false);
      }
    }
  },
  computed: {
    trialStartNoLicenseAddHereText: function trialStartNoLicenseAddHereText() {
      var link = "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey'
      }));
      return Object(external_CoreHome_["translate"])('Marketplace_TrialStartNoLicenseAddHere', "<a href=\"".concat(link, "\">"), '</a>');
    },
    trialStartNoLicenseLegalHintText: function trialStartNoLicenseLegalHintText() {
      return Object(external_CoreHome_["translate"])('Marketplace_TrialStartNoLicenseLegalHint', Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/terms-conditions/'), '</a>', Object(external_CoreHome_["externalLink"])('https://matomo.org/privacy-policy/'), '</a>');
    }
  },
  methods: {
    closeModal: function closeModal() {
      StartFreeTrialvue_type_script_lang_ts_$('#startFreeTrial').modal('close');
    },
    createAccountAndStartFreeTrial: function createAccountAndStartFreeTrial() {
      var _this = this;

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
      }).then(function () {
        _this.startFreeTrial();
      }).catch(function (error) {
        if (error.message.startsWith('Marketplace_CreateAccountError')) {
          _this.showErrorModal(Object(external_CoreHome_["translate"])(error.message));

          _this.trialStartInProgress = false;

          _this.$emit('update:modelValue', '');
        } else {
          _this.createAccountError = error.message;
          _this.trialStartInProgress = false;

          _this.showLicenseDialog(true);
        }
      });
    },
    showLicenseDialog: function showLicenseDialog(immediateTransition) {
      var _this2 = this;

      var onEnter = function onEnter(event) {
        var keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode === 13) {
          _this2.closeModal();

          _this2.createAccountAndStartFreeTrial();
        }
      };

      var modalOptions = {
        dismissible: true,
        onOpenEnd: function onOpenEnd() {
          var emailField = '.modal.open #email';
          StartFreeTrialvue_type_script_lang_ts_$(emailField).focus();
          StartFreeTrialvue_type_script_lang_ts_$(emailField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: function onCloseEnd() {
          _this2.createAccountError = null;

          if (_this2.trialStartInProgress) {
            return;
          }

          _this2.$emit('update:modelValue', '');
        }
      };

      if (immediateTransition) {
        modalOptions.inDuration = 0;
      }

      StartFreeTrialvue_type_script_lang_ts_$('#startFreeTrial').modal(modalOptions).modal('open');
    },
    showErrorModal: function showErrorModal(error) {
      var _this3 = this;

      if (this.trialStartError) {
        return;
      }

      this.trialStartError = error;
      StartFreeTrialvue_type_script_lang_ts_$('#startFreeTrial').modal({
        dismissible: true,
        inDuration: 0,
        onCloseEnd: function onCloseEnd() {
          _this3.trialStartError = null;
        }
      }).modal('open');
    },
    showLoadingModal: function showLoadingModal(immediateTransition) {
      var _this4 = this;

      if (this.trialStartInProgress) {
        return;
      }

      this.trialStartInProgress = true;
      this.loadingModalCloseCallback = undefined;
      StartFreeTrialvue_type_script_lang_ts_$('#startFreeTrial').modal({
        dismissible: false,
        inDuration: immediateTransition ? 0 : undefined,
        onCloseEnd: function onCloseEnd() {
          if (!_this4.loadingModalCloseCallback) {
            return;
          }

          _this4.loadingModalCloseCallback();

          _this4.loadingModalCloseCallback = undefined;
        }
      }).modal('open');
    },
    startFreeTrial: function startFreeTrial() {
      var _this5 = this;

      this.showLoadingModal(false);
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.startFreeTrial'
      }, {
        pluginName: this.modelValue
      }, {
        createErrorNotification: false
      }).then(function () {
        _this5.loadingModalCloseCallback = _this5.startFreeTrialSuccess;

        _this5.closeModal();
      }).catch(function (error) {
        _this5.showErrorModal(Matomo_Matomo.helper.htmlDecode(error.message));

        _this5.trialStartInProgress = false;
      }).finally(function () {
        _this5.$emit('update:modelValue', '');
      });
    },
    startFreeTrialSuccess: function startFreeTrialSuccess() {
      var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
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



StartFreeTrialvue_type_script_lang_ts.render = StartFreeTrialvue_type_template_id_74682556_render

/* harmony default export */ var StartFreeTrial = (StartFreeTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2a5d1a74

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_1 = {
  ref: "root",
  class: "modal",
  id: "pluginDetailsModal"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_2 = {
  key: 0,
  class: "modal-content"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_3 = {
  class: "modal-content__header"
};

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "btn-close modal-close"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
})], -1);

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_5 = ["title"];
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_6 = {
  class: "plugin-metadata-part1"
};

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 1", -1);

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_8 = {
  key: 0,
  class: "pair"
};

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "star-icon reviews-icon",
  src: "plugins/Marketplace/images/star.svg",
  alt: ""
}, null, -1);

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_10 = {
  key: 1,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_11 = {
  key: 2,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_12 = {
  key: 3,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_13 = {
  key: 4,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_14 = {
  class: "modal-content__main"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_15 = {
  class: "plugin-description"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_16 = {
  key: 1,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_17 = {
  key: 2,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_18 = {
  key: 3,
  class: "alert alert-danger"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_19 = {
  key: 4,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_20 = ["innerHTML"];
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_21 = {
  class: "plugin-metadata-part2"
};

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 2", -1);

var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_24 = {
  key: 0,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_25 = {
  key: 1,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_26 = {
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_27 = ["href"];
var _hoisted_28 = ["href"];
var _hoisted_29 = {
  key: 2
};
var _hoisted_30 = {
  key: 3
};
var _hoisted_31 = {
  class: "pair"
};
var _hoisted_32 = ["href"];

var _hoisted_33 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_34 = ["href"];

var _hoisted_35 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_36 = ["href"];
var _hoisted_37 = {
  key: 0,
  class: "pair"
};
var _hoisted_38 = {
  key: 1,
  class: "pair"
};
var _hoisted_39 = ["href"];
var _hoisted_40 = {
  key: 1
};
var _hoisted_41 = ["innerHTML"];
var _hoisted_42 = {
  key: 0
};
var _hoisted_43 = ["href"];
var _hoisted_44 = {
  key: 1
};
var _hoisted_45 = ["href"];
var _hoisted_46 = ["innerHTML"];
var _hoisted_47 = {
  key: 0,
  class: "plugin-screenshots"
};

var _hoisted_48 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var _hoisted_49 = {
  class: "thumbnails"
};
var _hoisted_50 = ["src"];
var _hoisted_51 = {
  key: 1,
  class: "plugin-documentation"
};

var _hoisted_52 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var _hoisted_53 = ["innerHTML"];
var _hoisted_54 = {
  key: 2,
  class: "plugin-faq"
};

var _hoisted_55 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var _hoisted_56 = ["innerHTML"];
var _hoisted_57 = {
  key: 3,
  class: "plugin-reviews",
  id: "reviews"
};

var _hoisted_58 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var _hoisted_59 = ["id", "src"];
var _hoisted_60 = {
  class: "modal-content__footer"
};
var _hoisted_61 = {
  class: "cta-container"
};
var _hoisted_62 = {
  key: 0,
  class: "matomo-badge matomo-badge-modal",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginDetailsModalvue_type_template_id_2a5d1a74_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginActivity,
      _ctx$pluginActivity2,
      _ctx$pluginLatestVers,
      _ctx$pluginLatestVers2,
      _ctx$pluginLatestVers3,
      _ctx$pluginLatestVers4,
      _this = this;

  var _component_MissingReqsNotice = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MissingReqsNotice");

  var _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_1, [!_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_3, [PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [_ctx.plugin.featured ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", {
    key: 0,
    class: "star-icon featured-icon",
    title: _ctx.translate('Marketplace_FeaturedPlugin'),
    src: "plugins/Marketplace/images/star.svg",
    alt: ""
  }, null, 8, PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin && _ctx.plugin.displayName ? _ctx.plugin.displayName : 'Plugin details'), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_6, [PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [_ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.scrollElementIntoView('#reviews');
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginReviews.averageRating), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.numDownloads > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Downloads')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.numDownloadsPretty), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.lastUpdated && !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LastUpdated')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.lastUpdated), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Developer')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginOwner), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_15, [_ctx.showMissingRequirementsNoticeIfApplicable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MissingReqsNotice, {
    key: 0,
    plugin: _ctx.plugin
  }, null, 8, ["plugin"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isMultiServerEnvironment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_MultiServerEnvironmentWarning')), 1)) : !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AutoUpdateDisabledWarning', '\'[General]enable_auto_update=1\'', '\'config/config.ini.php\'')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showMissingLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseMissingDescription')), 1)) : _ctx.showExceededLicenseDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginLicenseExceededDescription')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginDescription)
  }, null, 8, PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_21, [PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_22, PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [!_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginKeywords ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginKeywords')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginKeywords.join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Authors')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginAuthors, function (author, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: "author-".concat(index)
    }, [author.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      target: "_blank",
      rel: "noreferrer noopener",
      href: author.homepage
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, PluginDetailsModalvue_type_template_id_2a5d1a74_hoisted_27)) : author.email && _ctx.isValidEmail(author.email) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      href: "mailto:".concat(encodeURIComponent(author.email))
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
  }, [_hoisted_33], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.pluginChangelogUrl)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Changelog')), 9, _hoisted_34)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.plugin.repositoryUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [_ctx.plugin.homepage || _ctx.pluginChangelogUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_hoisted_35], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.plugin.repositoryUrl)
  }, "GitHub", 8, _hoisted_36)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.pluginActivity && _ctx.pluginActivity.numCommits ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activity')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.activity.numCommits) + " commits ", 1), ((_ctx$pluginActivity = _ctx.pluginActivity) === null || _ctx$pluginActivity === void 0 ? void 0 : _ctx$pluginActivity.numContributors) > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' ' + _ctx.translate('Marketplace_ByXDevelopers', _ctx.pluginActivity.numContributors)), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx$pluginActivity2 = _ctx.pluginActivity) !== null && _ctx$pluginActivity2 !== void 0 && _ctx$pluginActivity2.lastCommitDate ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' ' + _ctx.translate('Marketplace_LastCommitTime', _ctx.pluginActivity.lastCommitDate)), 1)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showLicenseName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_License')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [(_ctx$pluginLatestVers = _ctx.pluginLatestVersion.license) !== null && _ctx$pluginLatestVers !== void 0 && _ctx$pluginLatestVers.url ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    rel: "noreferrer noopener",
    href: (_ctx$pluginLatestVers2 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers2 === void 0 ? void 0 : _ctx$pluginLatestVers2.url,
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers3 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers3 === void 0 ? void 0 : _ctx$pluginLatestVers3.name), 9, _hoisted_39)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_40, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])((_ctx$pluginLatestVers4 = _ctx.pluginLatestVersion.license) === null || _ctx$pluginLatestVers4 === void 0 ? void 0 : _ctx$pluginLatestVers4.name), 1))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginSupport.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginSupport, function (support, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "pair",
      key: "support-".concat(index)
    }, [support.name && support.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", {
      innerHTML: _ctx.$sanitize(support.name)
    }, null, 8, _hoisted_41), _this.isValidHttpUrl(support.value) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.externalRawLink(_ctx.$sanitize(support.value))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.$sanitize(support.value)), 9, _hoisted_43)])) : _this.isValidEmail(support.value) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", _hoisted_44, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "mailto:".concat(encodeURIComponent(support.value))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.$sanitize(support.value)), 9, _hoisted_45)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("dd", {
      key: 2,
      innerHTML: _ctx.$sanitize(support.value)
    }, null, 8, _hoisted_46))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.pluginScreenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_47, [_hoisted_48, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Screenshots')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_49, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginScreenshots, function (screenshot) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("figure", {
      key: "screenshot-".concat(screenshot)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: "".concat(screenshot, "?w=800"),
      width: "800",
      alt: ""
    }, null, 8, _hoisted_50), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("figcaption", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_this.getScreenshotBaseName(screenshot)), 1)]);
  }), 128))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginDocumentation ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_51, [_hoisted_52, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Documentation')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginDocumentation)
  }, null, 8, _hoisted_53)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginFaq ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_54, [_hoisted_55, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    innerHTML: _ctx.$sanitize(_ctx.pluginFaq)
  }, null, 8, _hoisted_56)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_57, [_hoisted_58, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("iframe", {
    class: "reviewIframe",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.pluginReviews.height ? "height: ".concat(_ctx.pluginReviews.height, "px;") : ''),
    id: _ctx.pluginReviews.embedUrl.replace(/[\W_]+/g, ' '),
    src: _ctx.pluginReviews.embedUrl
  }, null, 12, _hoisted_59)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_60, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
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
    onStartFreeTrial: _ctx.startFreeTrial
  }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "onStartFreeTrial"])]), 'piwik' == _ctx.plugin.owner || 'matomo-org' == _ctx.plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_62)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2a5d1a74

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=8508486a

function MissingReqsNoticevue_type_template_id_8508486a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugin.missingRequirements || [], function (req, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index,
      class: "alert alert-danger"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_MissingRequirementsNotice', _ctx.requirement(req.requirement), req.actualVersion, req.requiredVersion)), 1);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=8508486a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts

/* harmony default export */ var MissingReqsNoticevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    }
  },
  methods: {
    requirement: function requirement(req) {
      if (req === 'php') {
        return 'PHP';
      }

      return "".concat(req[0].toUpperCase()).concat(req.substr(1));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue



MissingReqsNoticevue_type_script_lang_ts.render = MissingReqsNoticevue_type_template_id_8508486a_render

/* harmony default export */ var MissingReqsNotice = (MissingReqsNoticevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=script&lang=ts



var PluginDetailsModalvue_type_script_lang_ts_window = window,
    PluginDetailsModalvue_type_script_lang_ts_$ = PluginDetailsModalvue_type_script_lang_ts_window.$;
/* harmony default export */ var PluginDetailsModalvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MissingReqsNotice: MissingReqsNotice,
    CTAContainer: CTAContainer
  },
  props: {
    modelValue: {
      type: Object,
      default: function _default() {
        return {};
      }
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
    }
  },
  data: function data() {
    return {
      isLoading: true,
      pluginDetails: '',
      fetchRequest: null,
      fetchRequestAbortController: null
    };
  },
  emits: ['update:modelValue', 'startFreeTrial'],
  watch: {
    modelValue: function modelValue(newValue) {
      if (newValue) {
        this.showPluginDetailsDialog();
      }
    },
    isLoading: function isLoading(newValue) {
      if (newValue === false) {
        this.applyExternalTarget();
        this.applyIframeResize();
      }
    }
  },
  computed: {
    plugin: function plugin() {
      return this.modelValue;
    },
    pluginLatestVersion: function pluginLatestVersion() {
      var versions = this.plugin.versions || [{}];
      return versions[versions.length - 1];
    },
    pluginReadmeHtml: function pluginReadmeHtml() {
      var _this$pluginLatestVer;

      return ((_this$pluginLatestVer = this.pluginLatestVersion) === null || _this$pluginLatestVer === void 0 ? void 0 : _this$pluginLatestVer.readmeHtml) || {};
    },
    pluginDescription: function pluginDescription() {
      var _this$pluginReadmeHtm;

      return ((_this$pluginReadmeHtm = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm === void 0 ? void 0 : _this$pluginReadmeHtm.description) || '';
    },
    pluginDocumentation: function pluginDocumentation() {
      var _this$pluginReadmeHtm2;

      return ((_this$pluginReadmeHtm2 = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm2 === void 0 ? void 0 : _this$pluginReadmeHtm2.documentation) || '';
    },
    pluginFaq: function pluginFaq() {
      var _this$pluginReadmeHtm3;

      return ((_this$pluginReadmeHtm3 = this.pluginReadmeHtml) === null || _this$pluginReadmeHtm3 === void 0 ? void 0 : _this$pluginReadmeHtm3.faq) || '';
    },
    pluginShop: function pluginShop() {
      return this.plugin.shop;
    },
    pluginShopVariations: function pluginShopVariations() {
      var _this$pluginShop;

      return ((_this$pluginShop = this.pluginShop) === null || _this$pluginShop === void 0 ? void 0 : _this$pluginShop.variations) || [];
    },
    pluginReviews: function pluginReviews() {
      var _this$pluginShop2;

      return ((_this$pluginShop2 = this.pluginShop) === null || _this$pluginShop2 === void 0 ? void 0 : _this$pluginShop2.reviews) || {};
    },
    pluginKeywords: function pluginKeywords() {
      var _this$plugin;

      return ((_this$plugin = this.plugin) === null || _this$plugin === void 0 ? void 0 : _this$plugin.keywords) || [];
    },
    pluginAuthors: function pluginAuthors() {
      var authors = this.plugin.authors || [];
      return authors.filter(function (author) {
        return author.name;
      });
    },
    pluginActivity: function pluginActivity() {
      return this.plugin.activity || {};
    },
    pluginChangelogUrl: function pluginChangelogUrl() {
      return this.plugin.changelog.url || '';
    },
    pluginSupport: function pluginSupport() {
      return this.plugin.support || [];
    },
    isMatomoPlugin: function isMatomoPlugin() {
      return ['piwik', 'matomo-org'].includes(this.plugin.owner);
    },
    pluginOwner: function pluginOwner() {
      return this.isMatomoPlugin ? 'Matomo' : this.plugin.owner;
    },
    showReviews: function showReviews() {
      return this.pluginReviews && this.pluginReviews.embedUrl && this.pluginReviews.averageRating;
    },
    showMissingLicenseDescription: function showMissingLicenseDescription() {
      return this.hasSomeAdminAccess && this.plugin.isMissingLicense;
    },
    showExceededLicenseDescription: function showExceededLicenseDescription() {
      return this.hasSomeAdminAccess && this.plugin.hasExceededLicense;
    },
    showMissingRequirementsNoticeIfApplicable: function showMissingRequirementsNoticeIfApplicable() {
      return this.isSuperUser && (this.plugin.isDownloadable || this.plugin.isInstalled);
    },
    showLicenseName: function showLicenseName() {
      var _this$pluginLatestVer2;

      var license = ((_this$pluginLatestVer2 = this.pluginLatestVersion) === null || _this$pluginLatestVer2 === void 0 ? void 0 : _this$pluginLatestVer2.license) || {};
      return !!license.name;
    },
    pluginScreenshots: function pluginScreenshots() {
      return this.plugin.screenshots || [];
    }
  },
  methods: {
    applyExternalTarget: function applyExternalTarget() {
      var _this = this;

      setTimeout(function () {
        var root = _this.$refs.root;
        PluginDetailsModalvue_type_script_lang_ts_$('.modal-content__main a', root).each(function (index, a) {
          var link = PluginDetailsModalvue_type_script_lang_ts_$(a).attr('href');

          if (link && link.indexOf('http') === 0) {
            PluginDetailsModalvue_type_script_lang_ts_$(a).attr('target', '_blank');
          }
        });
      });
    },
    scrollElementIntoView: function scrollElementIntoView(selector) {
      var _this2 = this;

      setTimeout(function () {
        var root = _this2.$refs.root;
        var elements = PluginDetailsModalvue_type_script_lang_ts_$(selector, root);

        if (elements.length && elements[0] && elements[0].scrollIntoView) {
          elements[0].scrollIntoView({
            block: 'nearest',
            behavior: 'smooth'
          });
        }
      });
    },
    isValidEmail: function isValidEmail(email) {
      // regex from https://stackoverflow.com/a/46181
      // eslint-disable-next-line max-len
      return email.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    isValidHttpUrl: function isValidHttpUrl(input) {
      try {
        var url = new URL(input);
        return url.protocol === 'http:' || url.protocol === 'https:';
      } catch (err) {
        return false;
      }
    },
    getProtocolAndDomain: function getProtocolAndDomain(url) {
      var urlObj = new URL(url);
      return "".concat(urlObj.protocol, "//").concat(urlObj.hostname);
    },
    applyIframeResize: function applyIframeResize() {
      var _this3 = this;

      setTimeout(function () {
        var _window2 = window,
            iFrameResize = _window2.iFrameResize;

        if (_this3.pluginReviews) {
          PluginDetailsModalvue_type_script_lang_ts_$(function () {
            var $iFrames = PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal iframe.reviewIframe');

            for (var i = 0; i < $iFrames.length; i += 1) {
              // eslint-disable-next-line max-len
              iFrameResize({
                checkOrigin: [_this3.getProtocolAndDomain(_this3.pluginReviews.embedUrl)]
              }, $iFrames[i]);
            }
          });
        }
      });
    },
    getScreenshotBaseName: function getScreenshotBaseName(screenshot) {
      var filename = screenshot.split('/').pop() || '';
      return filename.substring(0, filename.lastIndexOf('.')).split('_').join(' ');
    },
    showPluginDetailsDialog: function showPluginDetailsDialog() {
      var _this4 = this;

      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal({
        dismissible: true,
        onCloseEnd: function onCloseEnd() {
          _this4.$emit('update:modelValue', null);

          _this4.isLoading = true;
        }
      }).modal('open');
      setTimeout(function () {
        _this4.isLoading = false;
      }, 10); // just to prevent showing the modal when the plugin data are not yet passed in
    },
    startFreeTrial: function startFreeTrial() {
      var _this5 = this;

      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal('close');
      setTimeout(function () {
        _this5.$emit('startFreeTrial', _this5.plugin.name);
      }, 250);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue



PluginDetailsModalvue_type_script_lang_ts.render = PluginDetailsModalvue_type_template_id_2a5d1a74_render

/* harmony default export */ var PluginDetailsModal = (PluginDetailsModalvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts





var PluginListvue_type_script_lang_ts_window = window,
    PluginListvue_type_script_lang_ts_$ = PluginListvue_type_script_lang_ts_window.$;
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
    }
  },
  data: function data() {
    return {
      showStartFreeTrialForPlugin: '',
      showPluginDetailsForPlugin: null
    };
  },
  components: {
    PluginDetailsModal: PluginDetailsModal,
    CTAContainer: CTAContainer,
    StartFreeTrial: StartFreeTrial
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  emits: ['triggerUpdate'],
  watch: {
    pluginsToShow: function pluginsToShow(newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        this.shrinkDescriptionIfMultilineTitle();
      }
    }
  },
  mounted: function mounted() {
    var _this = this;

    PluginListvue_type_script_lang_ts_$(window).resize(function () {
      _this.shrinkDescriptionIfMultilineTitle();
    });
  },
  methods: {
    shrinkDescriptionIfMultilineTitle: function shrinkDescriptionIfMultilineTitle() {
      var $nodes = PluginListvue_type_script_lang_ts_$('.marketplace .card-holder');

      if (!$nodes || !$nodes.length) {
        return;
      }

      $nodes.each(function (index, node) {
        var $card = PluginListvue_type_script_lang_ts_$(node);
        var $titleText = $card.find('.card-title');
        var $alertText = $card.find('.card-content-bottom .alert');
        var hasDownloads = $card.hasClass('card-with-downloads');
        var titleLines = 1;

        if ($titleText.length) {
          var elHeight = +$titleText.height();
          var lineHeight = +$titleText.css('line-height').replace('px', '');

          if (lineHeight) {
            var _Math$ceil;

            titleLines = (_Math$ceil = Math.ceil(elHeight / lineHeight)) !== null && _Math$ceil !== void 0 ? _Math$ceil : 1;
          }
        }

        var alertLines = 0;

        if ($alertText.length) {
          var _elHeight = +$alertText.height();

          var _lineHeight = +$alertText.css('line-height').replace('px', '');

          if (_lineHeight) {
            var _Math$ceil2;

            alertLines = (_Math$ceil2 = Math.ceil(_elHeight / _lineHeight)) !== null && _Math$ceil2 !== void 0 ? _Math$ceil2 : 1;
          }
        }

        var $cardDescription = $card.find('.card-description');

        if ($cardDescription.length) {
          var cardDescription = $cardDescription[0];
          var clampedLines = 0; // a bit convoluted logic, but this is what's been arrived at with a designer
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
            cardDescription.setAttribute('data-clamp', "".concat(clampedLines));
          } else {
            cardDescription.removeAttribute('data-clamp');
          }
        }
      });
    },
    clickCard: function clickCard(event, plugin) {
      // check if the target is a link or is a descendant of a link
      // to skip direct clicks on links within the card, we want those honoured
      if (PluginListvue_type_script_lang_ts_$(event.target).closest('a:not(.card-title-link)').length) {
        return;
      }

      event.stopPropagation();
      this.openDetailsModal(plugin);
    },
    openDetailsModal: function openDetailsModal(plugin) {
      this.showPluginDetailsForPlugin = plugin;
    },
    startTrialFromDetailsModal: function startTrialFromDetailsModal(pluginName) {
      this.showStartFreeTrialForPlugin = pluginName;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue



PluginListvue_type_script_lang_ts.render = PluginListvue_type_template_id_3404bfd4_render

/* harmony default export */ var PluginList = (PluginListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts





var lcfirst = function lcfirst(s) {
  return "".concat(s[0].toLowerCase()).concat(s.substring(1));
};

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
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    PluginList: PluginList,
    Field: external_CorePluginsAdmin_["Field"]
  },
  data: function data() {
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
  mounted: function mounted() {
    var _this = this;

    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.mounted', {
      element: this.$refs.root
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return external_CoreHome_["MatomoUrl"].hashParsed.value;
    }, function () {
      _this.updateValuesFromHash(false);
    });
    this.updateValuesFromHash(true);
  },
  unmounted: function unmounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.unmounted', {
      element: this.$refs.root
    });
  },
  methods: {
    updateValuesFromHash: function updateValuesFromHash(forceFetch) {
      var doFetch = forceFetch;
      var newSearchQuery = external_CoreHome_["MatomoUrl"].hashParsed.value.query || '';
      var newPluginSort = external_CoreHome_["MatomoUrl"].hashParsed.value.sort || '';
      var newPluginTypeFilter = external_CoreHome_["MatomoUrl"].hashParsed.value.pluginType || '';

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
    updateQuery: function updateQuery(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        query: event
      }));
    },
    updateType: function updateType(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        pluginType: event
      }));
    },
    updateSort: function updateSort(event) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        sort: event
      }));
    },
    fetchPlugins: function fetchPlugins() {
      var _this2 = this;

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
      }).then(function (response) {
        _this2.pluginsToShow = response;
      }).finally(function () {
        _this2.loading = false;
        _this2.fetchRequestAbortController = null;
      });
    }
  },
  computed: {
    queryInputTitle: function queryInputTitle() {
      var plugins = lcfirst(Object(external_CoreHome_["translate"])('General_Plugins'));
      var pluginCount = this.numAvailablePluginsByType[this.pluginTypeFilter] || 0;
      return "".concat(Object(external_CoreHome_["translate"])('General_Search'), " ").concat(pluginCount, " ").concat(plugins, "...");
    },
    loadingMessage: function loadingMessage() {
      return Object(external_CoreHome_["translate"])('Mobile_LoadingReport', Object(external_CoreHome_["translate"])(this.showThemes ? 'CorePluginsAdmin_Themes' : 'General_Plugins'));
    },
    showThemes: function showThemes() {
      return this.pluginTypeFilter === 'themes';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue



Marketplacevue_type_script_lang_ts.render = render

/* harmony default export */ var Marketplace = (Marketplacevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=b5754d94

var LicenseKeyvue_type_template_id_b5754d94_hoisted_1 = {
  class: "marketplace-max-width"
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_2 = {
  class: "marketplace-paid-intro"
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_3 = {
  key: 0
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_4 = {
  key: 0
};

var LicenseKeyvue_type_template_id_b5754d94_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LicenseKeyvue_type_template_id_b5754d94_hoisted_6 = {
  class: "licenseToolbar valign-wrapper"
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_7 = ["href"];
var LicenseKeyvue_type_template_id_b5754d94_hoisted_8 = {
  key: 0
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_9 = {
  class: "ui-confirm",
  id: "installAllPaidPluginsAtOnce",
  ref: "installAllPaidPluginsAtOnce"
};

var LicenseKeyvue_type_template_id_b5754d94_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LicenseKeyvue_type_template_id_b5754d94_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LicenseKeyvue_type_template_id_b5754d94_hoisted_12 = ["data-href", "value"];
var LicenseKeyvue_type_template_id_b5754d94_hoisted_13 = ["value"];
var LicenseKeyvue_type_template_id_b5754d94_hoisted_14 = {
  key: 1
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_15 = {
  key: 0
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_16 = ["innerHTML"];

var LicenseKeyvue_type_template_id_b5754d94_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LicenseKeyvue_type_template_id_b5754d94_hoisted_18 = {
  class: "licenseToolbar valign-wrapper"
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_19 = {
  key: 1
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_20 = ["innerHTML"];
var LicenseKeyvue_type_template_id_b5754d94_hoisted_21 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
var LicenseKeyvue_type_template_id_b5754d94_hoisted_22 = ["value"];
var LicenseKeyvue_type_template_id_b5754d94_hoisted_23 = ["value"];
function LicenseKeyvue_type_template_id_b5754d94_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DefaultLicenseKeyFields = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DefaultLicenseKeyFields");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_2, [_ctx.isValidConsumer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_3, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PaidPluginsWithLicenseKeyIntro', '')) + " ", 1), LicenseKeyvue_type_template_id_b5754d94_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DefaultLicenseKeyFields, {
    "model-value": _ctx.licenseKey,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.licenseKey = $event;

      _ctx.updatedLicenseKey();
    }),
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.updateLicense();
    }),
    "has-license-key": _ctx.hasLicenseKey,
    "is-valid-consumer": _ctx.isValidConsumer,
    "enable-update": _ctx.enableUpdate
  }, null, 8, ["model-value", "has-license-key", "is-valid-consumer", "enable-update"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    id: "remove_license_key",
    onConfirm: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.removeLicense();
    }),
    value: _ctx.translate('Marketplace_RemoveLicenseKey')
  }, null, 8, ["value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn valign",
    href: _ctx.subscriptionOverviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ViewSubscriptions')), 9, LicenseKeyvue_type_template_id_b5754d94_hoisted_7), _ctx.showInstallAllPaidPlugins ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "btn installAllPaidPlugins valign",
    onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.onInstallAllPaidPlugins();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallAllPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallThesePlugins')) + " ", 1), LicenseKeyvue_type_template_id_b5754d94_hoisted_10, LicenseKeyvue_type_template_id_b5754d94_hoisted_11]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.paidPluginsToInstallAtOnce, function (pluginName) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: pluginName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(pluginName), 1);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "install",
    type: "button",
    "data-href": _ctx.installAllPaidPluginsLink,
    value: _ctx.translate('Marketplace_InstallAllPurchasedPluginsAction', _ctx.paidPluginsToInstallAtOnce.length)
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "cancel",
    type: "button",
    value: _ctx.translate('General_Cancel')
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_13)])], 512)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isUpdating
  }, null, 8, ["loading"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_14, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.noLicenseKeyIntroText)
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_16), LicenseKeyvue_type_template_id_b5754d94_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DefaultLicenseKeyFields, {
    "model-value": _ctx.licenseKey,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      _ctx.licenseKey = $event;

      _ctx.updatedLicenseKey();
    }),
    onConfirm: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.updateLicense();
    }),
    "has-license-key": _ctx.hasLicenseKey,
    "is-valid-consumer": _ctx.isValidConsumer,
    "enable-update": _ctx.enableUpdate
  }, null, 8, ["model-value", "has-license-key", "is-valid-consumer", "enable-update"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isUpdating
  }, null, 8, ["loading"])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.noLicenseKeyIntroNoSuperUserAccessText)
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_20)]))]))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_b5754d94_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_22), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, LicenseKeyvue_type_template_id_b5754d94_hoisted_23)], 512)]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=b5754d94

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=template&id=e9fa1036

var DefaultLicenseKeyFieldsvue_type_template_id_e9fa1036_hoisted_1 = {
  class: "valign licenseKeyText"
};
function DefaultLicenseKeyFieldsvue_type_template_id_e9fa1036_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DefaultLicenseKeyFieldsvue_type_template_id_e9fa1036_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "license_key",
    "full-width": true,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('update:modelValue', $event);
    }),
    placeholder: _ctx.licenseKeyPlaceholder
  }, null, 8, ["model-value", "placeholder"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('confirm');
    }),
    disabled: !_ctx.enableUpdate,
    value: _ctx.saveButtonText,
    id: "submit_license_key"
  }, null, 8, ["disabled", "value"])], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=template&id=e9fa1036

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=script&lang=ts



/* harmony default export */ var DefaultLicenseKeyFieldsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: String,
    isValidConsumer: Boolean,
    hasLicenseKey: Boolean,
    enableUpdate: Boolean
  },
  emits: ['update:modelValue', 'confirm'],
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  computed: {
    licenseKeyPlaceholder: function licenseKeyPlaceholder() {
      return this.isValidConsumer ? Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyIsValidShort') : Object(external_CoreHome_["translate"])('Marketplace_LicenseKey');
    },
    saveButtonText: function saveButtonText() {
      return this.hasLicenseKey ? Object(external_CoreHome_["translate"])('CoreUpdater_UpdateTitle') : Object(external_CoreHome_["translate"])('Marketplace_ActivateLicenseKey');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue



DefaultLicenseKeyFieldsvue_type_script_lang_ts.render = DefaultLicenseKeyFieldsvue_type_template_id_e9fa1036_render

/* harmony default export */ var DefaultLicenseKeyFields = (DefaultLicenseKeyFieldsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=script&lang=ts




/* harmony default export */ var LicenseKeyvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    hasLicenseKey: Boolean,
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    }
  },
  components: {
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    DefaultLicenseKeyFields: DefaultLicenseKeyFields
  },
  data: function data() {
    return {
      licenseKey: '',
      enableUpdate: false,
      isUpdating: false
    };
  },
  methods: {
    onInstallAllPaidPlugins: function onInstallAllPaidPlugins() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce);
    },
    updateLicenseKey: function updateLicenseKey(action, licenseKey, onSuccessMessage) {
      var _this = this;

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "Marketplace.".concat(action),
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }, {
        withTokenInUrl: true
      }).then(function (response) {
        _this.isUpdating = false;

        if (response && response.value) {
          external_CoreHome_["NotificationsStore"].show({
            message: onSuccessMessage,
            context: 'success',
            type: 'transient'
          });
          external_CoreHome_["Matomo"].helper.redirect();
        }
      }, function () {
        _this.isUpdating = false;
      });
    },
    removeLicense: function removeLicense() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmRemoveLicense, {
        yes: function yes() {
          _this2.enableUpdate = false;
          _this2.isUpdating = true;

          _this2.updateLicenseKey('deleteLicenseKey', '', Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
    updatedLicenseKey: function updatedLicenseKey() {
      this.enableUpdate = !!this.licenseKey;
    },
    updateLicense: function updateLicense() {
      this.enableUpdate = false;
      this.isUpdating = true;
      this.updateLicenseKey('saveLicenseKey', this.licenseKey, Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyActivatedSuccess'));
    }
  },
  computed: {
    subscriptionOverviewLink: function subscriptionOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'subscriptionOverview'
      })));
    },
    noLicenseKeyIntroText: function noLicenseKeyIntroText() {
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntro', Object(external_CoreHome_["externalLink"])('https://matomo.org/recommends/premium-plugins/'), '</a>');
    },
    noLicenseKeyIntroNoSuperUserAccessText: function noLicenseKeyIntroNoSuperUserAccessText() {
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess', Object(external_CoreHome_["externalLink"])('https://matomo.org/recommends/premium-plugins/'), '</a>');
    },
    installAllPaidPluginsLink: function installAllPaidPluginsLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'installAllPaidPlugins',
        nonce: this.installNonce
      })));
    },
    showInstallAllPaidPlugins: function showInstallAllPaidPlugins() {
      return this.isAutoUpdatePossible && this.isPluginsAdminEnabled && this.paidPluginsToInstallAtOnce.length;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue



LicenseKeyvue_type_script_lang_ts.render = LicenseKeyvue_type_template_id_b5754d94_render

/* harmony default export */ var LicenseKey = (LicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=8dd04b4e

var ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_1 = ["innerHTML"];
var ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_2 = {
  class: "manage-license-key-input"
};
var ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_3 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
var ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_4 = ["value"];
var ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_5 = ["value"];
function ManageLicenseKeyvue_type_template_id_8dd04b4e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_LicenseKey'),
    class: "manage-license-key"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "manage-license-key-intro",
        innerHTML: _ctx.$sanitize(_ctx.manageLicenseKeyIntro)
      }, null, 8, ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_8dd04b4e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "license_key",
        modelValue: _ctx.licenseKey,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.licenseKey = $event;
        }),
        placeholder: _ctx.licenseKeyPlaceholder,
        "full-width": true
      }, null, 8, ["modelValue", "placeholder"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.updateLicense();
        }),
        value: _ctx.saveButtonText,
        disabled: !_ctx.licenseKey || _ctx.isUpdating,
        id: "submit_license_key"
      }, null, 8, ["value", "disabled"]), _ctx.hasValidLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
        key: 0,
        id: "remove_license_key",
        onConfirm: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.removeLicense();
        }),
        disabled: _ctx.isUpdating,
        value: _ctx.translate('General_Remove')
      }, null, 8, ["disabled", "value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.isUpdating
      }, null, 8, ["loading"])];
    }),
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts



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
  data: function data() {
    return {
      licenseKey: '',
      hasValidLicense: this.hasValidLicenseKey,
      isUpdating: false
    };
  },
  methods: {
    updateLicenseKey: function updateLicenseKey(action, licenseKey, onSuccessMessage) {
      var _this = this;

      external_CoreHome_["NotificationsStore"].remove('ManageLicenseKeySuccess');
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "Marketplace.".concat(action),
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }, {
        withTokenInUrl: true
      }).then(function (response) {
        _this.isUpdating = false;

        if (response && response.value) {
          external_CoreHome_["NotificationsStore"].show({
            id: 'ManageLicenseKeySuccess',
            message: onSuccessMessage,
            context: 'success',
            type: 'toast'
          });
          _this.hasValidLicense = action !== 'deleteLicenseKey';
          _this.licenseKey = '';
        }
      }, function () {
        _this.isUpdating = false;
      });
    },
    removeLicense: function removeLicense() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmRemoveLicense, {
        yes: function yes() {
          _this2.isUpdating = true;

          _this2.updateLicenseKey('deleteLicenseKey', '', Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
    updateLicense: function updateLicense() {
      this.isUpdating = true;
      this.updateLicenseKey('saveLicenseKey', this.licenseKey, Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyActivatedSuccess'));
    }
  },
  computed: {
    manageLicenseKeyIntro: function manageLicenseKeyIntro() {
      var marketplaceLink = "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview'
      })));
      return Object(external_CoreHome_["translate"])('Marketplace_ManageLicenseKeyIntro', "<a href=\"".concat(marketplaceLink, "\">"), '</a>', Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account'), '</a>');
    },
    licenseKeyPlaceholder: function licenseKeyPlaceholder() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyIsValidShort') : Object(external_CoreHome_["translate"])('Marketplace_LicenseKey');
    },
    saveButtonText: function saveButtonText() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('CoreUpdater_UpdateTitle') : Object(external_CoreHome_["translate"])('Marketplace_ActivateLicenseKey');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue



ManageLicenseKeyvue_type_script_lang_ts.render = ManageLicenseKeyvue_type_template_id_8dd04b4e_render

/* harmony default export */ var ManageLicenseKey = (ManageLicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=f1842798

var GetNewPluginsvue_type_template_id_f1842798_hoisted_1 = {
  class: "getNewPlugins"
};
var GetNewPluginsvue_type_template_id_f1842798_hoisted_2 = {
  class: "row"
};
var GetNewPluginsvue_type_template_id_f1842798_hoisted_3 = {
  class: "pluginName"
};

var GetNewPluginsvue_type_template_id_f1842798_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_f1842798_hoisted_5 = {
  key: 0
};

var GetNewPluginsvue_type_template_id_f1842798_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_f1842798_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_f1842798_hoisted_8 = [GetNewPluginsvue_type_template_id_f1842798_hoisted_6, GetNewPluginsvue_type_template_id_f1842798_hoisted_7];
var GetNewPluginsvue_type_template_id_f1842798_hoisted_9 = {
  class: "widgetBody"
};
var GetNewPluginsvue_type_template_id_f1842798_hoisted_10 = ["href"];
function GetNewPluginsvue_type_template_id_f1842798_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, function (plugin, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetNewPluginsvue_type_template_id_f1842798_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 512), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetNewPluginsvue_type_template_id_f1842798_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)], 512), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])]), index < _ctx.plugins.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsvue_type_template_id_f1842798_hoisted_5, GetNewPluginsvue_type_template_id_f1842798_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_f1842798_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsvue_type_template_id_f1842798_hoisted_10)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=f1842798

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts



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
    overviewLink: function overviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview'
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue



GetNewPluginsvue_type_script_lang_ts.render = GetNewPluginsvue_type_template_id_f1842798_render

/* harmony default export */ var GetNewPlugins = (GetNewPluginsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=b01ab65c

var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_1 = {
  class: "getNewPlugins isAdminPage",
  ref: "root"
};
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_2 = {
  class: "row"
};
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_3 = ["title"];
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_4 = ["title"];
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_5 = {
  key: 0
};

var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_7 = ["src"];
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_8 = {
  class: "widgetBody"
};
var GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_9 = ["href"];
function GetNewPluginsAdminvue_type_template_id_b01ab65c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, function (plugin) {
    var _plugin$screenshots;

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      class: "pluginName",
      title: plugin.description
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 8, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_3), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      class: "description",
      title: plugin.description
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 9, GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_4), (_plugin$screenshots = plugin.screenshots) !== null && _plugin$screenshots !== void 0 && _plugin$screenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_5, [GetNewPluginsAdminvue_type_template_id_b01ab65c_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      class: "screenshot",
      src: "".concat(plugin.screenshots[0], "?w=600"),
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts



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
    marketplaceOverviewLink: function marketplaceOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue



GetNewPluginsAdminvue_type_script_lang_ts.render = GetNewPluginsAdminvue_type_template_id_b01ab65c_render

/* harmony default export */ var GetNewPluginsAdmin = (GetNewPluginsAdminvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=0ec62128

var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_1 = {
  class: "getNewPlugins getPremiumFeatures widgetBody"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_2 = {
  key: 0,
  class: "col s12 m12"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_3 = ["innerHTML"];
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_4 = {
  style: {
    "margin-bottom": "28px",
    "color": "#5bb75b"
  }
};

var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-heart red-text"
}, null, -1);

var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_6 = {
  class: "pluginName"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_7 = {
  key: 0,
  class: "pluginSubtitle"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_8 = {
  class: "pluginBody"
};

var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_10 = {
  class: "pluginMoreDetails"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_11 = {
  class: "widgetBody"
};
var GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_12 = ["href"];
function GetPremiumFeaturesvue_type_template_id_0ec62128_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginRows, function (rowOfPlugins, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [index === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      style: {
        "font-weight": "bold",
        "color": "#5bb75b"
      },
      innerHTML: _ctx.$sanitize(_ctx.trialHintsText)
    }, null, 8, GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SupportMatomoThankYou')) + " ", 1), GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_5])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(rowOfPlugins, function (plugin) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "col s12 m4",
        key: plugin.name
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 512), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]]), plugin.specialOffer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SpecialOffer')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.specialOffer), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isBundle ? "".concat(_ctx.translate('Marketplace_SpecialOffer'), ": ") : '') + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)], 512), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]])])]);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetPremiumFeaturesvue_type_template_id_0ec62128_hoisted_12)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=0ec62128

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts



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
    trialHintsText: function trialHintsText() {
      var link = Object(external_CoreHome_["externalRawLink"])('https://shop.matomo.org/free-trial/');
      var linkStyle = 'color:#5bb75b;text-decoration: underline;';
      return Object(external_CoreHome_["translate"])('Marketplace_TrialHints', "<a style=\"".concat(linkStyle, "\" href=\"").concat(link, "\" target=\"_blank\" rel=\"noreferrer noopener\">"), '</a>');
    },
    pluginRows: function pluginRows() {
      // divide plugins array into rows of 3
      var result = [];
      this.plugins.forEach(function (plugin, index) {
        var row = Math.floor(index / 3);
        result[row] = result[row] || [];
        result[row].push(plugin);
      });
      return result;
    },
    overviewLink: function overviewLink() {
      var query = external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      });
      var hash = external_CoreHome_["MatomoUrl"].stringify({
        pluginType: 'premium'
      });
      return "?".concat(query, "#?").concat(hash);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue



GetPremiumFeaturesvue_type_script_lang_ts.render = GetPremiumFeaturesvue_type_template_id_0ec62128_render

/* harmony default export */ var GetPremiumFeatures = (GetPremiumFeaturesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=b739a192

var OverviewIntrovue_type_template_id_b739a192_hoisted_1 = {
  key: 0
};
var OverviewIntrovue_type_template_id_b739a192_hoisted_2 = {
  key: 1
};
var OverviewIntrovue_type_template_id_b739a192_hoisted_3 = ["innerHTML"];
var OverviewIntrovue_type_template_id_b739a192_hoisted_4 = {
  key: 2
};
var OverviewIntrovue_type_template_id_b739a192_hoisted_5 = ["innerHTML"];
var OverviewIntrovue_type_template_id_b739a192_hoisted_6 = ["innerHTML"];
function OverviewIntrovue_type_template_id_b739a192_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_LicenseKey = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LicenseKey");

  var _component_UploadPluginDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("UploadPluginDialog");

  var _component_Marketplace = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Marketplace");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.translate('CorePluginsAdmin_Marketplace')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)];
    }),
    _: 1
  }, 8, ["feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [!_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_b739a192_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.restrictedMessage), 1)) : _ctx.showThemes ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_b739a192_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ThemesDescription')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.installingNewThemeText)
  }, null, 8, OverviewIntrovue_type_template_id_b739a192_hoisted_3)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_b739a192_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_PluginsExtendPiwik')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.installingNewPluginText)
  }, null, 8, OverviewIntrovue_type_template_id_b739a192_hoisted_5)])), _ctx.isSuperUser && _ctx.inReportingMenu ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 3,
    ref: "noticeRemoveMarketplaceFromMenu",
    innerHTML: _ctx.$sanitize(_ctx.noticeRemoveMarketplaceFromMenuText)
  }, null, 8, OverviewIntrovue_type_template_id_b739a192_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_LicenseKey, {
    "is-valid-consumer": _ctx.isValidConsumer,
    "is-super-user": _ctx.isSuperUser,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "has-license-key": _ctx.hasLicenseKey,
    "paid-plugins-to-install-at-once": _ctx.paidPluginsToInstallAtOnce,
    "install-nonce": _ctx.installNonce
  }, null, 8, ["is-valid-consumer", "is-super-user", "is-auto-update-possible", "is-plugins-admin-enabled", "has-license-key", "paid-plugins-to-install-at-once", "install-nonce"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_UploadPluginDialog, {
    "is-plugin-upload-enabled": _ctx.isPluginUploadEnabled,
    "upload-limit": _ctx.uploadLimit,
    "install-nonce": _ctx.installNonce
  }, null, 8, ["is-plugin-upload-enabled", "upload-limit", "install-nonce"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Marketplace, {
    "plugin-type-options": _ctx.pluginTypeOptions,
    "default-sort": _ctx.defaultSort,
    "plugin-sort-options": _ctx.pluginSortOptions,
    "num-available-plugins-by-type": _ctx.numAvailablePluginsByType,
    "current-user-email": _ctx.currentUserEmail,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "is-super-user": _ctx.isSuperUser,
    "is-multi-server-environment": _ctx.isMultiServerEnvironment,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "is-valid-consumer": _ctx.isValidConsumer,
    "deactivate-nonce": _ctx.deactivateNonce,
    "activate-nonce": _ctx.activateNonce,
    "install-nonce": _ctx.installNonce,
    "update-nonce": _ctx.updateNonce,
    "has-some-admin-access": _ctx.hasSomeAdminAccess
  }, null, 8, ["plugin-type-options", "default-sort", "plugin-sort-options", "num-available-plugins-by-type", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "has-some-admin-access"])], 512)), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=b739a192

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts





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
    hasLicenseKey: Boolean,
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
    }
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    UploadPluginDialog: external_CorePluginsAdmin_["UploadPluginDialog"],
    LicenseKey: LicenseKey,
    Marketplace: Marketplace
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  mounted: function mounted() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      var pluginLink = this.$refs.noticeRemoveMarketplaceFromMenu.querySelector('[matomo-plugin-name]');
      external_CorePluginsAdmin_["PluginName"].mounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel'
        }
      });
    }
  },
  beforeUnmount: function beforeUnmount() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      var pluginLink = this.$refs.noticeRemoveMarketplaceFromMenu.querySelector('[matomo-plugin-name]');
      external_CorePluginsAdmin_["PluginName"].unmounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel'
        }
      });
    }
  },
  computed: {
    installingNewThemeText: function installingNewThemeText() {
      return Object(external_CoreHome_["translate"])('Marketplace_InstallingNewThemesViaMarketplaceOrUpload', '<a href="#" class="uploadPlugin">', '</a>');
    },
    installingNewPluginText: function installingNewPluginText() {
      return Object(external_CoreHome_["translate"])('Marketplace_InstallingNewPluginsViaMarketplaceOrUpload', '<a href="#" class="uploadPlugin">', '</a>');
    },
    noticeRemoveMarketplaceFromMenuText: function noticeRemoveMarketplaceFromMenuText() {
      return Object(external_CoreHome_["translate"])('Marketplace_NoticeRemoveMarketplaceFromReportingMenu', '<a href="#" matomo-plugin-name="WhiteLabel">', '</a>');
    },
    showThemes: function showThemes() {
      return external_CoreHome_["MatomoUrl"].hashParsed.value.pluginType === 'themes';
    },
    restrictedMessage: function restrictedMessage() {
      return this.showThemes ? Object(external_CoreHome_["translate"])('Marketplace_NotAllowedToBrowseMarketplaceThemes') : Object(external_CoreHome_["translate"])('Marketplace_NotAllowedToBrowseMarketplacePlugins');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue



OverviewIntrovue_type_script_lang_ts.render = OverviewIntrovue_type_template_id_b739a192_render

/* harmony default export */ var OverviewIntro = (OverviewIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=21b40b13

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_1 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_2 = ["href"];

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_5 = ["innerHTML"];

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_7 = {
  class: "subscriptionName"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_8 = ["href"];
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_9 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_10 = {
  class: "subscriptionType"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_11 = ["title"];
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_12 = {
  key: 0,
  class: "icon-error"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_13 = {
  key: 1,
  class: "icon-warning"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_14 = {
  key: 2,
  class: "icon-ok"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_15 = ["title"];

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_17 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_18 = {
  colspan: "6"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_19 = {
  class: "tableActionBar"
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_20 = ["href"];

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-table"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_22 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_23 = ["innerHTML"];
function SubscriptionOverviewvue_type_template_id_21b40b13_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_OverviewPluginSubscriptions'),
    class: "subscriptionOverview"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.hasLicenseKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginSubscriptionsList')) + " ", 1), _ctx.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        target: "_blank",
        rel: "noreferrer noopener",
        href: _ctx.loginUrl
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsAllDetails')), 9, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsMissingInfo')) + " ", 1), SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoValidSubscriptionNoUpdates')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_CurrentNumPiwikUsers', "<strong>".concat(_ctx.numUsers, "</strong>")))
      }, null, 8, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_5)]), SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionType')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionStartDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionEndDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionNextPaymentDate')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.subscriptions || [], function (subscription, index) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: index
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_7, [subscription.plugin.htmlUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          href: subscription.plugin.htmlUrl,
          rel: "noreferrer noopener",
          target: "_blank"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 9, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.productType), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          class: "subscriptionStatus",
          title: _ctx.getSubscriptionStatusTitle(subscription)
        }, [!subscription.isValid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_12)) : subscription.isExpiredSoon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_13)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_14)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.status) + " ", 1), subscription.isExceeded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 3,
          class: "errorMessage",
          title: _ctx.translate('Marketplace_LicenseExceededPossibleCause')
        }, [SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Exceeded')), 1)], 8, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.start), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.isValid && subscription.nextPayment ? _ctx.translate('Marketplace_LicenseRenewsNextPaymentDate') : subscription.end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.nextPayment), 1)]);
      }), 128)), !_ctx.subscriptions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoSubscriptionsFound')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.marketplaceOverviewLink,
        class: ""
      }, [SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BrowseMarketplace')), 1)], 8, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_20)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
      }, null, 8, SubscriptionOverviewvue_type_template_id_21b40b13_hoisted_23)]))];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=21b40b13

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts


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
    getSubscriptionStatusTitle: function getSubscriptionStatusTitle(sub) {
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
    marketplaceOverviewLink: function marketplaceOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      }));
    },
    missingLicenseText: function missingLicenseText() {
      return Object(external_CoreHome_["translate"])('Marketplace_OverviewPluginSubscriptionsMissingLicense', "<a href=\"".concat(this.marketplaceOverviewLink, "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue



SubscriptionOverviewvue_type_script_lang_ts.render = SubscriptionOverviewvue_type_template_id_21b40b13_render

/* harmony default export */ var SubscriptionOverview = (SubscriptionOverviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=1d333064

var RichMenuButtonvue_type_template_id_1d333064_hoisted_1 = {
  class: "richMarketplaceMenuButton"
};

var RichMenuButtonvue_type_template_id_1d333064_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var RichMenuButtonvue_type_template_id_1d333064_hoisted_3 = {
  class: "intro"
};
var RichMenuButtonvue_type_template_id_1d333064_hoisted_4 = {
  class: "cta"
};

var RichMenuButtonvue_type_template_id_1d333064_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-marketplace"
}, " ", -1);

function RichMenuButtonvue_type_template_id_1d333064_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RichMenuButtonvue_type_template_id_1d333064_hoisted_1, [RichMenuButtonvue_type_template_id_1d333064_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_1d333064_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RichMenuIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_1d333064_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn btn-outline",
    tabindex: "5",
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.$emit('action');
    }, ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(function ($event) {
      return _ctx.$emit('action');
    }, ["enter"]))
  }, [RichMenuButtonvue_type_template_id_1d333064_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)], 32)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=1d333064

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=script&lang=ts

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