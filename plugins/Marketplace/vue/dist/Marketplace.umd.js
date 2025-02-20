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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=19bbd7e6

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
    "num-users": _ctx.numUsers,
    onTriggerUpdate: _cache[0] || (_cache[0] = function ($event) {
      return _this.updateMarketplace();
    }),
    onStartTrialStart: _cache[1] || (_cache[1] = function ($event) {
      return _this.$emit('startTrialStart');
    }),
    onStartTrialStop: _cache[2] || (_cache[2] = function ($event) {
      return _this.$emit('startTrialStop');
    })
  }, null, 8, ["plugins-to-show", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "has-some-admin-access", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.loading && _ctx.pluginsToShow.length == 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
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
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=19bbd7e6

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=9a1e2784

var PluginListvue_type_template_id_9a1e2784_hoisted_1 = {
  key: 0,
  class: "pluginListContainer row"
};
var PluginListvue_type_template_id_9a1e2784_hoisted_2 = ["onClick"];
var PluginListvue_type_template_id_9a1e2784_hoisted_3 = {
  class: "card"
};
var PluginListvue_type_template_id_9a1e2784_hoisted_4 = {
  class: "card-content"
};
var PluginListvue_type_template_id_9a1e2784_hoisted_5 = ["src"];
var PluginListvue_type_template_id_9a1e2784_hoisted_6 = {
  class: "content-container"
};
var PluginListvue_type_template_id_9a1e2784_hoisted_7 = {
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
  class: "owner"
};
var _hoisted_18 = {
  key: 0
};
var _hoisted_19 = {
  key: 1
};
var _hoisted_20 = {
  class: "cta-container"
};
var _hoisted_21 = {
  key: 1,
  class: "matomo-badge matomo-badge-bottom",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginListvue_type_template_id_9a1e2784_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_RequestTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RequestTrial");

  var _component_StartFreeTrial = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("StartFreeTrial");

  var _component_PluginDetailsModal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginDetailsModal");

  var _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RequestTrial, {
    modelValue: _ctx.showRequestTrialForPlugin,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showRequestTrialForPlugin = $event;
    }),
    onTrialRequested: _cache[1] || (_cache[1] = function ($event) {
      return _this.$emit('triggerUpdate');
    })
  }, null, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_StartFreeTrial, {
    "current-user-email": _ctx.currentUserEmail,
    "is-valid-consumer": _ctx.isValidConsumer,
    modelValue: _ctx.showStartFreeTrialForPlugin,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.showStartFreeTrialForPlugin = $event;
    }),
    onTrialStarted: _cache[3] || (_cache[3] = function ($event) {
      _this.$emit('triggerUpdate');
    }),
    onStartTrialStart: _cache[4] || (_cache[4] = function ($event) {
      _this.$emit('startTrialStart');
    }),
    onStartTrialStop: _cache[5] || (_cache[5] = function ($event) {
      _this.$emit('startTrialStop');
    })
  }, null, 8, ["current-user-email", "is-valid-consumer", "modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginDetailsModal, {
    modelValue: _ctx.showPluginDetailsForPlugin,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
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
    "num-users": _ctx.numUsers,
    onRequestTrial: _cache[7] || (_cache[7] = function ($event) {
      return _this.requestTrial($event);
    }),
    onStartFreeTrial: _cache[8] || (_cache[8] = function ($event) {
      return _this.startFreeTrial($event);
    })
  }, null, 8, ["modelValue", "is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "has-some-admin-access", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"]), _ctx.pluginsToShow.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginsToShow, function (plugin) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m6 l4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("card-holder ".concat(plugin.numDownloads > 0 ? 'card-with-downloads' : '')),
      onClick: function onClick($event) {
        return _ctx.clickCard($event, plugin);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: "".concat(plugin.coverImage, "?w=880&h=480"),
      alt: "",
      class: "cover-image"
    }, null, 8, PluginListvue_type_template_id_9a1e2784_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginListvue_type_template_id_9a1e2784_hoisted_7, ['piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [plugin.priceFrom ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
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
      onOpenDetailsModal: function onOpenDetailsModal($event) {
        return _this.openDetailsModal(plugin);
      },
      onRequestTrial: function onRequestTrial($event) {
        return _this.requestTrial(plugin);
      },
      onStartFreeTrial: function onStartFreeTrial($event) {
        return _this.startFreeTrial(plugin);
      }
    }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "onOpenDetailsModal", "onRequestTrial", "onStartFreeTrial"])]), 'piwik' == plugin.owner || 'matomo-org' == plugin.owner ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_21)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])])])], 10, PluginListvue_type_template_id_9a1e2784_hoisted_2)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=template&id=9a1e2784

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=c75c86ba

var CTAContainervue_type_template_id_c75c86ba_hoisted_1 = {
  key: 0,
  class: "alert alert-danger alert-no-background"
};
var CTAContainervue_type_template_id_c75c86ba_hoisted_2 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_c75c86ba_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_c75c86ba_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_c75c86ba_hoisted_5 = ["href"];
var CTAContainervue_type_template_id_c75c86ba_hoisted_6 = {
  key: 2,
  class: "alert alert-danger alert-no-background"
};
var CTAContainervue_type_template_id_c75c86ba_hoisted_7 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_c75c86ba_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_c75c86ba_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_c75c86ba_hoisted_10 = ["href"];
var CTAContainervue_type_template_id_c75c86ba_hoisted_11 = {
  key: 1,
  class: "alert alert-warning alert-no-background"
};
var CTAContainervue_type_template_id_c75c86ba_hoisted_12 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var CTAContainervue_type_template_id_c75c86ba_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var CTAContainervue_type_template_id_c75c86ba_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var CTAContainervue_type_template_id_c75c86ba_hoisted_15 = {
  key: 4,
  class: "alert alert-success alert-no-background"
};

var CTAContainervue_type_template_id_c75c86ba_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" (");

var CTAContainervue_type_template_id_c75c86ba_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ");

var CTAContainervue_type_template_id_c75c86ba_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" (");

var CTAContainervue_type_template_id_c75c86ba_hoisted_19 = ["href"];

var CTAContainervue_type_template_id_c75c86ba_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ");

var CTAContainervue_type_template_id_c75c86ba_hoisted_21 = ["href"];

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(") ");

var _hoisted_23 = ["title"];
var _hoisted_24 = ["title", "href"];
var _hoisted_25 = {
  key: 8,
  class: "alert alert-warning alert-no-background"
};
var _hoisted_26 = {
  key: 0,
  style: {
    "white-space": "nowrap"
  }
};

var _hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("(");

var _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(")");

var _hoisted_29 = ["href"];
var _hoisted_30 = ["title"];
var _hoisted_31 = ["title"];
function CTAContainervue_type_template_id_c75c86ba_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_MoreDetailsAction = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MoreDetailsAction");

  var _component_DownloadButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DownloadButton");

  return _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseMissing')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_2, [CTAContainervue_type_template_id_c75c86ba_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }), CTAContainervue_type_template_id_c75c86ba_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.inModal && _ctx.plugin.hasExceededLicense && _ctx.plugin.consumer.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "btn btn-block",
    tabindex: "7",
    target: "_blank",
    rel: "noreferrer noopener",
    href: _ctx.externalRawLink(_ctx.plugin.consumer.loginUrl)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_UpgradeSubscription')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_5)) : _ctx.plugin.hasExceededLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_LicenseExceeded')) + " ", 1), !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_7, [CTAContainervue_type_template_id_c75c86ba_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MoreDetailsAction, {
    onAction: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }), CTAContainervue_type_template_id_c75c86ba_hoisted_9])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.canBeUpdated && 0 == _ctx.plugin.missingRequirements.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [_ctx.isAutoUpdatePossible && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    class: "btn btn-block",
    href: _ctx.linkToUpdate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreUpdater_UpdateTitle')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_10)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotUpdate')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CTAContainervue_type_template_id_c75c86ba_hoisted_12, [CTAContainervue_type_template_id_c75c86ba_hoisted_13, !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), CTAContainervue_type_template_id_c75c86ba_hoisted_14])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]))], 64)) : _ctx.plugin.isInstalled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CTAContainervue_type_template_id_c75c86ba_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Installed')) + " ", 1), _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [CTAContainervue_type_template_id_c75c86ba_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": false,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "is-auto-update-possible"]), CTAContainervue_type_template_id_c75c86ba_hoisted_17], 64)) : !_ctx.plugin.isInvalid && !_ctx.isMultiServerEnvironment && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [CTAContainervue_type_template_id_c75c86ba_hoisted_18, _ctx.plugin.isActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    tabindex: "7",
    href: _ctx.linkToDeactivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Deactivate')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_19)) : _ctx.plugin.missingRequirements.length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [CTAContainervue_type_template_id_c75c86ba_hoisted_20], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    tabindex: "7",
    href: _ctx.linkToActivate(_ctx.plugin.name)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Activate')), 9, CTAContainervue_type_template_id_c75c86ba_hoisted_21)), _hoisted_22], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.plugin.isEligibleForFreeTrial && !_ctx.inModal && _ctx.isPluginsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 5,
    class: "btn btn-block purchaseable",
    title: _ctx.translate('Marketplace_StartFreeTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_StartFreeTrial')), 9, _hoisted_23)) : _ctx.plugin.isEligibleForFreeTrial && _ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 6,
    class: "btn btn-block addToCartLink",
    target: "_blank",
    title: _ctx.translate('Marketplace_ClickToCompletePurchase'),
    rel: "noreferrer noopener",
    href: _ctx.shopVariationUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_AddToCart')), 9, _hoisted_24)) : !_ctx.inModal && !_ctx.plugin.isDownloadable && (_ctx.plugin.isPaid || _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 7,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }, null, 8, ["label"])) : _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_CannotInstall')) + " ", 1), !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_26, [_hoisted_27, !_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    onAction: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DownloadButton, {
    plugin: _ctx.plugin,
    "show-or": !_ctx.inModal,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible
  }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]), _hoisted_28])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : _ctx.isPluginsAdminEnabled && _ctx.plugin.hasDownloadLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 9,
    tabindex: "7",
    href: _ctx.linkToInstall(_ctx.plugin.name),
    class: "btn btn-block"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ActionInstall')), 9, _hoisted_29)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 10
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64))], 64)) : _ctx.plugin.isTrialRequested ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    tabindex: "7",
    class: "btn btn-block purchaseable disabled",
    href: "",
    title: _ctx.translate('Marketplace_TrialRequested')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialRequested')), 9, _hoisted_30)) : _ctx.plugin.canTrialBeRequested && !_ctx.plugin.isMissingLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    tabindex: "7",
    class: "btn btn-block purchaseable",
    href: "",
    onClick: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _this.$emit('requestTrial');
    }, ["prevent"])),
    title: _ctx.translate('Marketplace_RequestTrial')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RequestTrial')), 9, _hoisted_31)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 3
  }, [!_ctx.inModal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MoreDetailsAction, {
    key: 0,
    "show-as-button": true,
    label: _ctx.translate('General_MoreDetails'),
    onAction: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.$emit('openDetailsModal');
    })
  }, null, 8, ["label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64));
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/CTAContainer.vue?vue&type=template&id=c75c86ba

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



CTAContainervue_type_script_lang_ts.render = CTAContainervue_type_template_id_c75c86ba_render

/* harmony default export */ var CTAContainer = (CTAContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=template&id=a8ef37ce

var RequestTrialvue_type_template_id_a8ef37ce_hoisted_1 = {
  class: "ui-confirm",
  ref: "confirm"
};
var RequestTrialvue_type_template_id_a8ef37ce_hoisted_2 = ["value"];
var RequestTrialvue_type_template_id_a8ef37ce_hoisted_3 = ["value"];
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=script&lang=ts


/* harmony default export */ var RequestTrialvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: {
      type: Object,
      default: function _default() {
        return {};
      }
    }
  },
  emits: ['update:modelValue', 'trialRequested'],
  watch: {
    modelValue: function modelValue(newValue) {
      var _this = this;

      if (!newValue) {
        return;
      }

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirm, {
        yes: function yes() {
          _this.requestTrial(newValue);
        }
      }, {
        onCloseEnd: function onCloseEnd() {
          _this.$emit('update:modelValue', null);
        }
      });
    }
  },
  computed: {
    plugin: function plugin() {
      return this.modelValue;
    }
  },
  methods: {
    requestTrial: function requestTrial(plugin) {
      var _this2 = this;

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.requestTrial'
      }, {
        pluginName: plugin.name
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('Marketplace_RequestTrialSubmitted', plugin.displayName),
          context: 'success',
          id: 'requestTrialSuccess',
          placeat: '#notificationContainer',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);

        _this2.$emit('trialRequested');
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RequestTrial/RequestTrial.vue



RequestTrialvue_type_script_lang_ts.render = RequestTrialvue_type_template_id_a8ef37ce_render

/* harmony default export */ var RequestTrial = (RequestTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=5f45b39e

var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_1 = {
  class: "modal",
  id: "startFreeTrial"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_2 = {
  key: 0,
  class: "btn-close modal-close"
};

var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
}, null, -1);

var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_4 = [StartFreeTrialvue_type_template_id_5f45b39e_hoisted_3];
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_5 = {
  key: 1,
  class: "modal-content trial-start-in-progress"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_6 = {
  class: "Piwik_Popover_Loading"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_7 = {
  class: "Piwik_Popover_Loading_Name"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_8 = {
  key: 2,
  class: "modal-content trial-start-error"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_9 = {
  class: "modal-text"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_10 = {
  key: 3,
  class: "modal-content trial-start-no-license"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_11 = {
  class: "modal-text"
};
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_12 = ["innerHTML"];
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_13 = ["innerHTML"];
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_14 = ["disabled"];
var StartFreeTrialvue_type_template_id_5f45b39e_hoisted_15 = ["innerHTML"];
function StartFreeTrialvue_type_template_id_5f45b39e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_1, [!_ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_2, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.trialStartInProgress ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartInProgressText')), 1)])])])) : _ctx.trialStartError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trialStartError), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartErrorSupport')), 1)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StartFreeTrialvue_type_template_id_5f45b39e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_12)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "trial-start-legal-hint",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseLegalHintText)
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn",
    disabled: !_ctx.createAccountEmail,
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.createAccountAndStartFreeTrial();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TrialStartNoLicenseCreateAccount')), 9, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_14)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "add-existing-license",
    innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseAddHereText)
  }, null, 8, StartFreeTrialvue_type_template_id_5f45b39e_hoisted_15)])]))]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=template&id=5f45b39e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/StartFreeTrial/StartFreeTrial.vue?vue&type=script&lang=ts



var _window = window,
    $ = _window.$;
/* harmony default export */ var StartFreeTrialvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  props: {
    modelValue: {
      type: Object,
      default: function _default() {
        return {};
      }
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
  emits: ['update:modelValue', 'trialStarted', 'startTrialStart', 'startTrialStop'],
  watch: {
    modelValue: function modelValue(newValue) {
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
    plugin: function plugin() {
      return this.modelValue;
    },
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
      $('#startFreeTrial').modal('close');
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

          _this.$emit('update:modelValue', null);
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
          $(emailField).focus();
          $(emailField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: function onCloseEnd() {
          _this2.createAccountError = null;

          if (_this2.trialStartInProgress) {
            return;
          }

          _this2.$emit('update:modelValue', null);
        }
      };

      if (immediateTransition) {
        modalOptions.inDuration = 0;
      }

      $('#startFreeTrial').modal(modalOptions).modal('open');
    },
    showErrorModal: function showErrorModal(error) {
      var _this3 = this;

      if (this.trialStartError) {
        return;
      }

      this.trialStartError = error;
      $('#startFreeTrial').modal({
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
      $('#startFreeTrial').modal({
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
      this.$emit('startTrialStart');
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.startFreeTrial'
      }, {
        pluginName: this.plugin.name
      }, {
        createErrorNotification: false
      }).then(function () {
        _this5.loadingModalCloseCallback = _this5.startFreeTrialSuccess;

        _this5.closeModal();
      }).catch(function (error) {
        _this5.showErrorModal(external_CoreHome_["Matomo"].helper.htmlDecode(error.message));

        _this5.trialStartInProgress = false;

        _this5.$emit('startTrialStop');
      }).finally(function () {
        _this5.$emit('update:modelValue', null);
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



StartFreeTrialvue_type_script_lang_ts.render = StartFreeTrialvue_type_template_id_5f45b39e_render

/* harmony default export */ var StartFreeTrial = (StartFreeTrialvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2b416103

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_1 = {
  ref: "root",
  class: "modal",
  id: "pluginDetailsModal"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_2 = {
  class: "modal-content__header"
};

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "btn-close modal-close"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
})], -1);

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_4 = {
  key: 0,
  class: "plugin-metadata-part1"
};

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 1", -1);

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_6 = {
  key: 0,
  class: "pair"
};

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "star-icon reviews-icon",
  src: "plugins/Marketplace/images/star.svg",
  alt: ""
}, null, -1);

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_8 = {
  key: 1,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_9 = {
  key: 2,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_10 = {
  key: 3,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_11 = {
  key: 4,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_12 = {
  class: "plugin-description"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_13 = {
  key: 1,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_14 = {
  key: 2,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_15 = {
  key: 3,
  class: "alert alert-danger"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_16 = {
  key: 4,
  class: "alert alert-warning"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_17 = ["innerHTML"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_18 = ["innerHTML"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_19 = ["innerHTML"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_20 = ["innerHTML"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_21 = {
  class: "plugin-metadata-part2"
};

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
  class: "sr-only"
}, "Plugin details — part 2", -1);

var PluginDetailsModalvue_type_template_id_2b416103_hoisted_24 = {
  key: 0,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_25 = {
  key: 1,
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_26 = {
  class: "pair"
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_27 = ["href"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_28 = ["href"];
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_29 = {
  key: 2
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_30 = {
  key: 3
};
var PluginDetailsModalvue_type_template_id_2b416103_hoisted_31 = {
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
  key: 0,
  class: "matomo-badge matomo-badge-modal",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
var _hoisted_61 = {
  class: "cta-container cta-container-modal"
};
var _hoisted_62 = {
  key: 0,
  class: "free-trial"
};
var _hoisted_63 = {
  class: "free-trial-lead-in"
};
var _hoisted_64 = ["title"];
var _hoisted_65 = ["value", "title"];
var _hoisted_66 = {
  key: 1,
  class: "matomo-badge matomo-badge-modal",
  src: "plugins/Marketplace/images/matomo-badge.png",
  "aria-label": "Matomo plugin",
  alt: ""
};
function PluginDetailsModalvue_type_template_id_2b416103_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginActivity,
      _ctx$pluginActivity2,
      _ctx$pluginLatestVers,
      _ctx$pluginLatestVers2,
      _ctx$pluginLatestVers3,
      _ctx$pluginLatestVers4,
      _this = this;

  var _component_MissingReqsNotice = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MissingReqsNotice");

  var _component_CTAContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CTAContainer");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_1, [!_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["modal-content", {
      'modal-content--simple-header': !_ctx.hasHeaderMetadata
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_2, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin && _ctx.plugin.displayName ? _ctx.plugin.displayName : 'Plugin details'), 1), _ctx.hasHeaderMetadata ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_4, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [_ctx.showReviews ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Reviews')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.scrollElementIntoView('#reviews');
    })
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
  }, null, 8, PluginDetailsModalvue_type_template_id_2b416103_hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_21, [PluginDetailsModalvue_type_template_id_2b416103_hoisted_22, PluginDetailsModalvue_type_template_id_2b416103_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dl", null, [!_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Version')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.plugin.latestVersion), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pluginKeywords ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginKeywords')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pluginKeywords.join(', ')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.plugin.isBundle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Authors')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginAuthors, function (author, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: "author-".concat(index)
    }, [author.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 0,
      target: "_blank",
      rel: "noreferrer noopener",
      href: author.homepage
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, PluginDetailsModalvue_type_template_id_2b416103_hoisted_27)) : author.email && _ctx.isValidEmail(author.email) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      href: "mailto:".concat(encodeURIComponent(author.email))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 9, PluginDetailsModalvue_type_template_id_2b416103_hoisted_28)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsModalvue_type_template_id_2b416103_hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(author.name), 1)), index < _ctx.pluginAuthors.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PluginDetailsModalvue_type_template_id_2b416103_hoisted_30, ", ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginDetailsModalvue_type_template_id_2b416103_hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dt", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Websites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("dd", null, [_ctx.plugin.homepage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
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
  }, null, 12, _hoisted_59)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["modal-content__footer", {
      'modal-content__footer--with-free-trial': _ctx.showFreeTrialDropdown
    }])
  }, [_ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_60)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, [_ctx.showFreeTrialDropdown ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_62, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_63, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_TryFreeTrialTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "free-trial-dropdown",
    title: "".concat(_ctx.translate('Marketplace_ShownPriceIsExclTax'), " ").concat(_ctx.translate('Marketplace_CurrentNumPiwikUsers', _ctx.numUsers)),
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.selectedPluginShopVariationUrl = $event;
    }),
    onChange: _cache[2] || (_cache[2] = function () {
      return _ctx.changeSelectedPluginShopVariationUrl && _ctx.changeSelectedPluginShopVariationUrl.apply(_ctx, arguments);
    })
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugin.shop.variations, function (variation, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: "var-".concat(index),
      value: variation.addToCartUrl,
      title: "".concat(_ctx.translate('Marketplace_PriceExclTax', variation.price, variation.currency), " ").concat(_ctx.translate('Marketplace_CurrentNumPiwikUsers', _ctx.numUsers))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.name) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.prettyPrice) + " / " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(variation.period), 9, _hoisted_65);
  }), 128))], 40, _hoisted_64), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelSelect"], _ctx.selectedPluginShopVariationUrl]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CTAContainer, {
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
    onRequestTrial: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.emitTrialEvent('requestTrial');
    }),
    onStartFreeTrial: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.emitTrialEvent('startFreeTrial');
    })
  }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "shop-variation-url"])]), !_ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("img", _hoisted_66)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=template&id=2b416103

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
    },
    numUsers: {
      type: Number,
      required: true
    }
  },
  data: function data() {
    return {
      isLoading: true,
      currentPluginShopVariationUrl: ''
    };
  },
  emits: ['requestTrial', 'startFreeTrial', 'update:modelValue'],
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
    showFreeTrialDropdown: function showFreeTrialDropdown() {
      return this.isSuperUser && !this.plugin.isMissingLicense && !this.plugin.isInstalled && !this.plugin.hasExceededLicense && this.plugin.isEligibleForFreeTrial;
    },
    pluginScreenshots: function pluginScreenshots() {
      return this.plugin.screenshots || [];
    },
    hasHeaderMetadata: function hasHeaderMetadata() {
      return this.showReviews || !this.plugin.isBundle || (this.plugin.numDownloads || 0) > 0 || this.plugin.lastUpdated && !this.plugin.isBundle;
    },
    pluginShopVariationsPretty: function pluginShopVariationsPretty() {
      return this.pluginShopVariations.map(function (variation) {
        return "".concat(variation.name, " - ").concat(variation.prettyPrice, " / ").concat(variation.period);
      });
    },
    pluginShopRecommendedVariation: function pluginShopRecommendedVariation() {
      var recommendedVariations = this.pluginShopVariations.filter(function (v) {
        return v.recommended;
      });
      var defaultVariation = this.pluginShopVariations.length ? this.pluginShopVariations[0] : null;
      return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
    },
    selectedPluginShopVariationUrl: function selectedPluginShopVariationUrl() {
      var _this$pluginShopRecom;

      return this.currentPluginShopVariationUrl ? this.currentPluginShopVariationUrl : ((_this$pluginShopRecom = this.pluginShopRecommendedVariation) === null || _this$pluginShopRecom === void 0 ? void 0 : _this$pluginShopRecom.addToCartUrl) || '';
    },
    selectedShopVariationUrl: function selectedShopVariationUrl() {
      return this.selectedPluginShopVariationUrl || '';
    }
  },
  methods: {
    changeSelectedPluginShopVariationUrl: function changeSelectedPluginShopVariationUrl(event) {
      if (event) {
        this.currentPluginShopVariationUrl = event.target.value;
      }
    },
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
    emitTrialEvent: function emitTrialEvent(eventName) {
      var _this4 = this;

      var plugin = this.plugin;
      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal('close');
      setTimeout(function () {
        _this4.$emit(eventName, plugin);
      }, 250);
    },
    showPluginDetailsDialog: function showPluginDetailsDialog() {
      var _this5 = this;

      PluginDetailsModalvue_type_script_lang_ts_$('#pluginDetailsModal').modal({
        dismissible: true,
        onCloseEnd: function onCloseEnd() {
          external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
            showPlugin: null
          }));

          _this5.$emit('update:modelValue', null);

          _this5.isLoading = true;
        }
      }).modal('open');
      setTimeout(function () {
        _this5.isLoading = false;
      }, 10); // just to prevent showing the modal when the plugin data are not yet passed in
    },
    getPendingLicenseHelpText: function getPendingLicenseHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginLicenseStatusPending', pluginName, Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account/'), '</a>');
    },
    getCancelledLicenseHelpText: function getCancelledLicenseHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginLicenseStatusCancelled', pluginName, Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account/'), '</a>');
    },
    getDownloadLinkMissingHelpText: function getDownloadLinkMissingHelpText(pluginName) {
      return Object(external_CoreHome_["translate"])('Marketplace_PluginDownloadLinkMissingDescription', pluginName, Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/plugins/faq_21/'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginDetailsModal/PluginDetailsModal.vue



PluginDetailsModalvue_type_script_lang_ts.render = PluginDetailsModalvue_type_template_id_2b416103_render

/* harmony default export */ var PluginDetailsModal = (PluginDetailsModalvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }







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
    },
    numUsers: {
      type: Number,
      required: true
    }
  },
  data: function data() {
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
    pluginsToShow: function pluginsToShow(newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        this.shrinkDescriptionIfMultilineTitle();
        this.parseShowPluginParameter();
      }
    }
  },
  mounted: function mounted() {
    var _this = this;

    PluginListvue_type_script_lang_ts_$(window).resize(function () {
      _this.shrinkDescriptionIfMultilineTitle();
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return external_CoreHome_["MatomoUrl"].hashParsed.value.showPlugin;
    }, function (newValue, oldValue) {
      if (newValue && newValue !== oldValue) {
        _this.parseShowPluginParameter();
      }
    });
    this.parseShowPluginParameter();
  },
  methods: {
    parseShowPluginParameter: function parseShowPluginParameter() {
      var _MatomoUrl$hashParsed = external_CoreHome_["MatomoUrl"].hashParsed.value,
          showPlugin = _MatomoUrl$hashParsed.showPlugin,
          pluginType = _MatomoUrl$hashParsed.pluginType,
          query = _MatomoUrl$hashParsed.query;

      if (!showPlugin) {
        return;
      }

      var pluginToShow = this.pluginsToShow.filter( // eslint-disable-next-line @typescript-eslint/no-explicit-any
      function (plugin) {
        return plugin.name === showPlugin;
      });

      if (pluginToShow.length === 1) {
        var _pluginToShow = _slicedToArray(pluginToShow, 1),
            plugin = _pluginToShow[0];

        this.openDetailsModal(plugin);
        this.scrollPluginCardIntoView(plugin);
      } else if (pluginType !== '' || query !== '') {
        // plugin was not found in current list, so unset filters to retry
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          pluginType: 'plugins',
          query: null
        }));
      }
    },
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
    scrollPluginCardIntoView: function scrollPluginCardIntoView(plugin) {
      var $titles = PluginListvue_type_script_lang_ts_$(".pluginListContainer .card-title:contains(\"".concat(plugin.displayName, "\")"));

      if ($titles.length !== 1) {
        return;
      }

      var $cards = $titles.parents('.card');

      if ($cards.length !== 1 || !$cards[0].scrollIntoView) {
        return;
      }

      $cards[0].scrollIntoView({
        block: 'start',
        behavior: 'smooth'
      });
    },
    requestTrial: function requestTrial(plugin) {
      this.showRequestTrialForPlugin = plugin;
    },
    startFreeTrial: function startFreeTrial(plugin) {
      this.showStartFreeTrialForPlugin = plugin;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginList/PluginList.vue



PluginListvue_type_script_lang_ts.render = PluginListvue_type_template_id_9a1e2784_render

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
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
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
    updateMarketplace: function updateMarketplace() {
      var _this2 = this;

      this.fetchPlugins(function () {
        return _this2.$emit('triggerUpdate');
      });
    },
    fetchPlugins: function fetchPlugins(cb) {
      var _this3 = this;

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
        _this3.pluginsToShow = response;

        if (typeof cb === 'function') {
          cb();
        }
      }).finally(function () {
        _this3.loading = false;
        _this3.fetchRequestAbortController = null;
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=79698da6

var ManageLicenseKeyvue_type_template_id_79698da6_hoisted_1 = ["innerHTML"];
var ManageLicenseKeyvue_type_template_id_79698da6_hoisted_2 = {
  class: "manage-license-key-input"
};
var ManageLicenseKeyvue_type_template_id_79698da6_hoisted_3 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
var ManageLicenseKeyvue_type_template_id_79698da6_hoisted_4 = ["value"];
var ManageLicenseKeyvue_type_template_id_79698da6_hoisted_5 = ["value"];
function ManageLicenseKeyvue_type_template_id_79698da6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");

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
      }, null, 8, ManageLicenseKeyvue_type_template_id_79698da6_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton, {
        disabled: _ctx.isUpdating
      }, null, 8, ["disabled"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_79698da6_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_79698da6_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, ManageLicenseKeyvue_type_template_id_79698da6_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, ManageLicenseKeyvue_type_template_id_79698da6_hoisted_5)], 512)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=79698da6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts



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



ManageLicenseKeyvue_type_script_lang_ts.render = ManageLicenseKeyvue_type_template_id_79698da6_render

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=8d2d1142

var OverviewIntrovue_type_template_id_8d2d1142_hoisted_1 = {
  class: "marketplaceIntro"
};
var OverviewIntrovue_type_template_id_8d2d1142_hoisted_2 = {
  key: 0
};
var OverviewIntrovue_type_template_id_8d2d1142_hoisted_3 = {
  key: 1
};
var OverviewIntrovue_type_template_id_8d2d1142_hoisted_4 = {
  key: 0,
  class: "installAllPaidPlugins"
};
function OverviewIntrovue_type_template_id_8d2d1142_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_InstallAllPaidPluginsButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallAllPaidPluginsButton");

  var _component_Marketplace = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Marketplace");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.translate('CorePluginsAdmin_Marketplace')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)];
    }),
    _: 1
  }, 8, ["feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OverviewIntrovue_type_template_id_8d2d1142_hoisted_1, [!_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", OverviewIntrovue_type_template_id_8d2d1142_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Intro')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", OverviewIntrovue_type_template_id_8d2d1142_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_IntroSuperUser')), 1))]), _ctx.installAllPaidPluginsVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OverviewIntrovue_type_template_id_8d2d1142_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallAllPaidPluginsButton, {
    disabled: _ctx.installDisabled
  }, null, 8, ["disabled"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Marketplace, {
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
    onTriggerUpdate: _cache[0] || (_cache[0] = function ($event) {
      return _this.updateOverviewData();
    }),
    onStartTrialStart: _cache[1] || (_cache[1] = function ($event) {
      return _this.disableInstallAllPlugins(true);
    }),
    onStartTrialStop: _cache[2] || (_cache[2] = function ($event) {
      return _this.disableInstallAllPlugins(false);
    })
  }, null, 8, ["plugin-type-options", "default-sort", "plugin-sort-options", "num-available-plugins-by-type", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "has-some-admin-access", "num-users"])], 512)), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=8d2d1142

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
    InstallAllPaidPluginsButton: external_CorePluginsAdmin_["InstallAllPaidPluginsButton"],
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    Marketplace: Marketplace
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  data: function data() {
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
    getIsValidConsumer: function getIsValidConsumer() {
      return this.updateData && typeof this.updateData.isValidConsumer !== 'undefined' ? this.updateData.isValidConsumer : this.isValidConsumer;
    },
    installAllPaidPluginsVisible: function installAllPaidPluginsVisible() {
      return this.getIsValidConsumer && this.isSuperUser && this.isAutoUpdatePossible && this.isPluginsAdminEnabled || this.installDisabled && this.installLoading;
    },
    showThemes: function showThemes() {
      return external_CoreHome_["MatomoUrl"].hashParsed.value.pluginType === 'themes';
    }
  },
  methods: {
    disableInstallAllPlugins: function disableInstallAllPlugins(isLoading) {
      this.installDisabled = true;
      this.installLoading = isLoading;
    },
    enableInstallAllPlugins: function enableInstallAllPlugins() {
      this.installDisabled = false;
      this.installLoading = false;
    },
    updateOverviewData: function updateOverviewData() {
      var _this = this;

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
      }).then(function (response) {
        _this.updateData = response;
      }).finally(function () {
        _this.updating = false;
        _this.fetchRequestAbortController = null;

        _this.enableInstallAllPlugins();
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue



OverviewIntrovue_type_script_lang_ts.render = OverviewIntrovue_type_template_id_8d2d1142_render

/* harmony default export */ var OverviewIntro = (OverviewIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=cc78be12

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_1 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_2 = ["href"];

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_5 = ["innerHTML"];

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_7 = {
  class: "subscriptionName"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_8 = ["href"];
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_9 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_10 = {
  class: "subscriptionType"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_11 = ["title"];
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_12 = {
  key: 0,
  class: "icon-error"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_13 = {
  key: 1,
  class: "icon-warning"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_14 = {
  key: 2,
  class: "icon-error"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_15 = {
  key: 3,
  class: "icon-ok"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_16 = ["title"];

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_18 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_19 = {
  colspan: "6"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_20 = {
  class: "tableActionBar"
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_21 = ["href"];

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-table"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_23 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_24 = ["innerHTML"];
function SubscriptionOverviewvue_type_template_id_cc78be12_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_OverviewPluginSubscriptions'),
    class: "subscriptionOverview"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.hasLicenseKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginSubscriptionsList')) + " ", 1), _ctx.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        target: "_blank",
        rel: "noreferrer noopener",
        href: _ctx.loginUrl
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsAllDetails')), 9, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsMissingInfo')) + " ", 1), SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoValidSubscriptionNoUpdates')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_CurrentNumPiwikUsers', "<strong>".concat(_ctx.numUsers, "</strong>")))
      }, null, 8, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_5)]), SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionType')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionStartDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionEndDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionNextPaymentDate')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.subscriptions || [], function (subscription, index) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: index
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_7, [subscription.plugin.htmlUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          href: subscription.plugin.htmlUrl,
          rel: "noreferrer noopener",
          target: "_blank"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 9, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.productType), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          class: "subscriptionStatus",
          title: _ctx.getSubscriptionStatusTitle(subscription)
        }, [!subscription.isValid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_12)) : subscription.isExpiredSoon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_13)) : subscription.status !== '' && subscription.status !== 'Active' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_14)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_15)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.status) + " ", 1), subscription.isExceeded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 4,
          class: "errorMessage",
          title: _ctx.translate('Marketplace_LicenseExceededPossibleCause')
        }, [SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Exceeded')), 1)], 8, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.start), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.isValid && subscription.nextPayment ? _ctx.translate('Marketplace_LicenseRenewsNextPaymentDate') : subscription.end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.nextPayment), 1)]);
      }), 128)), !_ctx.subscriptions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoSubscriptionsFound')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.marketplaceOverviewLink,
        class: ""
      }, [SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BrowseMarketplace')), 1)], 8, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_21)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
      }, null, 8, SubscriptionOverviewvue_type_template_id_cc78be12_hoisted_24)]))];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=cc78be12

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
    licenseKeyLink: function licenseKeyLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey'
      }));
    },
    missingLicenseText: function missingLicenseText() {
      return Object(external_CoreHome_["translate"])('Marketplace_OverviewPluginSubscriptionsMissingLicenseMessage', "<a href=\"".concat(this.licenseKeyLink, "\">"), '</a>', "<a href=\"".concat(this.marketplaceOverviewLink, "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue



SubscriptionOverviewvue_type_script_lang_ts.render = SubscriptionOverviewvue_type_template_id_cc78be12_render

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