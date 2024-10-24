(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["UserCountry"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["UserCountry"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/UserCountry/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "LocationProviderSelection", function() { return /* reexport */ LocationProviderSelection; });
__webpack_require__.d(__webpack_exports__, "AdminPage", function() { return /* reexport */ AdminPage; });
__webpack_require__.d(__webpack_exports__, "GetDistinctCountries", function() { return /* reexport */ GetDistinctCountries; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=ad135132

const _hoisted_1 = {
  class: "locationProviderSelection"
};
const _hoisted_2 = ["innerHTML"];
const _hoisted_3 = {
  class: "row"
};
const _hoisted_4 = {
  class: "col s12 push-m9 m3"
};
const _hoisted_5 = {
  class: "col s12 m4 l2"
};
const _hoisted_6 = ["id", "disabled", "checked", "onChange"];
const _hoisted_7 = {
  class: "loc-provider-status"
};
const _hoisted_8 = {
  key: 0,
  class: "is-not-installed"
};
const _hoisted_9 = {
  key: 1,
  class: "is-installed"
};
const _hoisted_10 = {
  key: 2,
  class: "is-broken"
};
const _hoisted_11 = {
  class: "col s12 m4 l6"
};
const _hoisted_12 = ["innerHTML"];
const _hoisted_13 = ["innerHTML"];
const _hoisted_14 = {
  class: "col s12 m4 l4"
};
const _hoisted_15 = {
  key: 0,
  class: "form-help"
};
const _hoisted_16 = {
  key: 0
};
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = {
  style: {
    "position": "absolute"
  }
};
const _hoisted_19 = ["innerHTML"];
const _hoisted_20 = {
  class: "text-right"
};
const _hoisted_21 = ["onClick"];
const _hoisted_22 = {
  key: 1
};
const _hoisted_23 = {
  key: 1,
  class: "form-help"
};
const _hoisted_24 = {
  key: 0
};
const _hoisted_25 = ["innerHTML"];
const _hoisted_26 = ["innerHTML"];
const _hoisted_27 = {
  key: 1
};
const _hoisted_28 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [!_ctx.isThereWorkingProvider ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.setUpGuides || '')
  }, null, 8, _hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_InfoFor', _ctx.thisIp)), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.visibleLocationProviders, (provider, id) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: id,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`row form-group provider${id}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "location-provider",
      name: "location-provider",
      type: "radio",
      id: `provider_input_${id}`,
      disabled: provider.status !== 1,
      checked: _ctx.selectedProvider === id,
      onChange: $event => _ctx.selectedProvider = id
    }, null, 40, _hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translateOrDefault(provider.title)), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_7, [provider.status === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NotInstalled')), 1)) : provider.status === 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Installed')), 1)) : provider.status === 2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Broken')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.translateOrDefault(provider.description))
    }, null, 8, _hoisted_12), provider.status !== 1 && provider.install_docs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: 0,
      innerHTML: _ctx.$sanitize(provider.install_docs)
    }, null, 8, _hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [provider.status === 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [_ctx.thisIp !== '127.0.0.1' && _ctx.thisIp !== '::1' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountry_CurrentLocationIntro')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.updateLoading[id]
    }, null, 8, ["loading"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "location",
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
        visibility: _ctx.providerLocations[id] ? 'visible' : 'hidden'
      })
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", {
      innerHTML: _ctx.$sanitize(_ctx.providerLocations[id] || ' ')
    }, null, 8, _hoisted_19)], 4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.refreshProviderInfo(id), ["prevent"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Refresh')), 9, _hoisted_21)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountry_CannotLocalizeLocalIP', _ctx.thisIp)), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), provider.statusMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_23, [provider.status === 2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')) + ":", 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(provider.statusMessage)
    }, null, 8, _hoisted_25)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), provider.extra_message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: 2,
      class: "form-help",
      innerHTML: _ctx.$sanitize(provider.extra_message)
    }, null, 8, _hoisted_26)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2);
  }), 128)), !Object.keys(_ctx.locationProvidersNotDefaultOrDisabled).length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    noclear: true,
    context: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.noProvidersText)
    }, null, 8, _hoisted_28)]),
    _: 1
  })])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[0] || (_cache[0] = $event => _ctx.save()),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])]);
}
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=ad135132

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts



/* harmony default export */ var LocationProviderSelectionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentProviderId: {
      type: String,
      required: true
    },
    isThereWorkingProvider: Boolean,
    setUpGuides: String,
    thisIp: {
      type: String,
      required: true
    },
    locationProviders: {
      type: Object,
      required: true
    },
    defaultProviderId: {
      type: String,
      required: true
    },
    disabledProviderId: {
      type: String,
      required: true
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Notification: external_CoreHome_["Notification"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data() {
    return {
      isLoading: false,
      updateLoading: {},
      selectedProvider: this.currentProviderId,
      providerLocations: Object.fromEntries(Object.entries(this.locationProviders).map(([k, p]) => [k, p.location]))
    };
  },
  methods: {
    refreshProviderInfo(providerId) {
      // this should not be in a controller... ideally we fetch this data always from client side
      // and do not prefill it server side
      this.updateLoading[providerId] = true;
      delete this.providerLocations[providerId];
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'UserCountry',
        action: 'getLocationUsingProvider',
        id: providerId,
        format: 'html'
      }, {
        format: 'html'
      }).then(response => {
        this.providerLocations[providerId] = response;
      }).finally(() => {
        this.updateLoading[providerId] = false;
      });
    },
    save() {
      if (!this.selectedProvider) {
        return;
      }
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'UserCountry.setLocationProvider',
        providerId: this.selectedProvider
      }, {
        withTokenInUrl: true
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('General_Done'),
          context: 'success',
          noclear: true,
          type: 'toast',
          id: 'userCountryLocationProvider'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  },
  computed: {
    visibleLocationProviders() {
      return Object.fromEntries(Object.entries(this.locationProviders).filter(([, p]) => p.isVisible));
    },
    locationProvidersNotDefaultOrDisabled() {
      return Object.fromEntries(Object.entries(this.locationProviders).filter(([, p]) => p.id !== this.defaultProviderId && p.id !== this.disabledProviderId));
    },
    noProvidersText() {
      return Object(external_CoreHome_["translate"])('UserCountry_NoProviders', '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue



LocationProviderSelectionvue_type_script_lang_ts.render = render

/* harmony default export */ var LocationProviderSelection = (LocationProviderSelectionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/AdminPage/AdminPage.vue?vue&type=template&id=387617c4

function AdminPagevue_type_template_id_387617c4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_LocationProviderSelection = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LocationProviderSelection");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/geo-locate/'),
    id: "location-providers"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountry_Geolocation')), 1)]),
    _: 1
  }, 8, ["help-url"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UserCountry_GeolocationPageDesc')), 1)])), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UserCountry_LocationProvider')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_LocationProviderSelection, {
      "current-provider-id": _ctx.currentProviderId,
      "is-there-working-provider": _ctx.isThereWorkingProvider,
      "set-up-guides": _ctx.setUpGuides,
      "this-ip": _ctx.thisIp,
      "location-providers": _ctx.locationProviders,
      "default-provider-id": _ctx.defaultProviderId,
      "disabled-provider-id": _ctx.disabledProviderId
    }, null, 8, ["current-provider-id", "is-there-working-provider", "set-up-guides", "this-ip", "location-providers", "default-provider-id", "disabled-provider-id"])]),
    _: 1
  }, 8, ["content-title"])], 64);
}
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/AdminPage/AdminPage.vue?vue&type=template&id=387617c4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/AdminPage/AdminPage.vue?vue&type=script&lang=ts



/* harmony default export */ var AdminPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentProviderId: {
      type: String,
      required: true
    },
    isThereWorkingProvider: Boolean,
    setUpGuides: String,
    thisIp: {
      type: String,
      required: true
    },
    locationProviders: {
      type: Object,
      required: true
    },
    defaultProviderId: {
      type: String,
      required: true
    },
    disabledProviderId: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    LocationProviderSelection: LocationProviderSelection,
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    ContentBlock: external_CoreHome_["ContentBlock"]
  }
}));
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/AdminPage/AdminPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/AdminPage/AdminPage.vue



AdminPagevue_type_script_lang_ts.render = AdminPagevue_type_template_id_387617c4_render

/* harmony default export */ var AdminPage = (AdminPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/GetDistinctCountries/GetDistinctCountries.vue?vue&type=template&id=5d044f71

const GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_1 = {
  class: "sparkline"
};
const GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_2 = ["innerHTML"];
const GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", {
  style: {
    "clear": "left"
  }
}, null, -1);
function GetDistinctCountriesvue_type_template_id_5d044f71_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Sparkline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Sparkline");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Sparkline, {
      params: _ctx.urlSparklineCountries,
      width: 100,
      height: 25
    }, null, 8, ["params"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      innerHTML: _ctx.$sanitize(_ctx.distinctCountriesText)
    }, null, 8, GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_2)]), GetDistinctCountriesvue_type_template_id_5d044f71_hoisted_3]),
    _: 1
  });
}
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/GetDistinctCountries/GetDistinctCountries.vue?vue&type=template&id=5d044f71

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UserCountry/vue/src/GetDistinctCountries/GetDistinctCountries.vue?vue&type=script&lang=ts


/* harmony default export */ var GetDistinctCountriesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    numberDistinctCountries: {
      type: Number,
      required: true
    },
    urlSparklineCountries: {
      type: [Object, String],
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Sparkline: external_CoreHome_["Sparkline"]
  },
  computed: {
    distinctCountriesText() {
      return Object(external_CoreHome_["translate"])('UserCountry_DistinctCountries', `<strong>${this.numberDistinctCountries}</strong>`);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/GetDistinctCountries/GetDistinctCountries.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/GetDistinctCountries/GetDistinctCountries.vue



GetDistinctCountriesvue_type_script_lang_ts.render = GetDistinctCountriesvue_type_template_id_5d044f71_render

/* harmony default export */ var GetDistinctCountries = (GetDistinctCountriesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/UserCountry/vue/src/index.ts
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
//# sourceMappingURL=UserCountry.umd.js.map