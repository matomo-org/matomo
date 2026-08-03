(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["AIProviders"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["AIProviders"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/AIProviders/vue/dist/";
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

/***/ "2a82":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ManageAIProviders_vue_vue_type_style_index_0_id_464239d2_lang_less__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("4d9d");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ManageAIProviders_vue_vue_type_style_index_0_id_464239d2_lang_less__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ManageAIProviders_vue_vue_type_style_index_0_id_464239d2_lang_less__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "4d9d":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "9c8b":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ProviderCard_vue_vue_type_style_index_0_id_30b60d9a_lang_less__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("e225");
/* harmony import */ var _node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ProviderCard_vue_vue_type_style_index_0_id_30b60d9a_lang_less__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_cli_service_node_modules_mini_css_extract_plugin_dist_loader_js_ref_11_oneOf_1_0_node_modules_vue_cli_service_node_modules_css_loader_dist_cjs_js_ref_11_oneOf_1_1_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_11_oneOf_1_2_node_modules_less_loader_dist_cjs_js_ref_11_oneOf_1_3_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_1_1_ProviderCard_vue_vue_type_style_index_0_id_30b60d9a_lang_less__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "e225":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ManageAIProviders", function() { return /* reexport */ ManageAIProviders; });

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

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/AIProviders/vue/src/components/ProviderCard.vue?vue&type=script&setup=true&lang=ts


const _hoisted_1 = ["aria-checked", "aria-disabled", "tabindex"];
const _hoisted_2 = {
  class: "ai-providers-card-inner"
};
const _hoisted_3 = {
  class: "ai-providers-card-heading"
};
const _hoisted_4 = {
  class: "ai-providers-card-header"
};
const _hoisted_5 = {
  class: "ai-providers-card-name"
};
const _hoisted_6 = {
  key: 0,
  class: "ai-providers-card-default"
};
const _hoisted_7 = {
  class: "ai-providers-card-description"
};
const _hoisted_8 = {
  key: 2,
  class: "ai-providers-card-model"
};
const _hoisted_9 = {
  key: 1,
  class: "ai-providers-card-model-help"
};
const _hoisted_10 = ["disabled", "title"];
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true",
  class: "icon icon-reload"
}, null, -1);
const _hoisted_12 = {
  class: "ai-providers-card-actions"
};
const _hoisted_13 = ["disabled"];
const _hoisted_14 = ["disabled"];



/* harmony default export */ var ProviderCardvue_type_script_setup_true_lang_ts = (/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  __name: 'ProviderCard',
  props: {
    provider: null,
    configuration: null,
    availableModels: null,
    selected: {
      type: Boolean
    },
    usableAsDefault: {
      type: Boolean
    },
    canEdit: {
      type: Boolean
    },
    isTesting: {
      type: Boolean
    },
    isDisconnecting: {
      type: Boolean
    }
  },
  emits: ["select", "update:apiKey", "update:endpointUrl", "update:useFipsEndpoint", "update:model", "test", "disconnect"],
  setup(__props, {
    emit
  }) {
    const props = __props;
    /* eslint-disable func-call-spacing, no-spaced-func */
    /* eslint-enable func-call-spacing, no-spaced-func */
    const hasPendingKey = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$configuration$, _props$configuration;
      return ((_props$configuration$ = (_props$configuration = props.configuration) === null || _props$configuration === void 0 ? void 0 : _props$configuration.apiKey) !== null && _props$configuration$ !== void 0 ? _props$configuration$ : '') !== '';
    });
    const hasEndpointUrl = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _props$configuration$2, _props$configuration2;
      return ((_props$configuration$2 = (_props$configuration2 = props.configuration) === null || _props$configuration2 === void 0 ? void 0 : _props$configuration2.endpointUrl) !== null && _props$configuration$2 !== void 0 ? _props$configuration$2 : '') !== '';
    });
    const hasKey = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => hasPendingKey.value || props.provider.configuration.hasApiKey);
    // Providers with a default endpoint (e.g. AWS Bedrock) only need the key;
    // fully custom servers need the URL and commonly run without authentication.
    const canTest = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (!props.provider.supportsCustomEndpoint || props.provider.defaultEndpointUrl) {
        return hasKey.value;
      }
      return hasEndpointUrl.value;
    });
    const modelOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const options = {};
      props.availableModels.forEach(model => {
        options[model] = model;
      });
      return options;
    });
    function selectProvider() {
      if (props.usableAsDefault) {
        emit('select');
      }
    }
    return (_ctx, _cache) => {
      var _props$configuration3, _props$configuration4, _props$configuration5, _props$configuration6;
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        "aria-checked": __props.selected,
        "aria-disabled": !__props.usableAsDefault,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
          'is-selected': __props.selected,
          'is-not-usable': !__props.usableAsDefault
        }, "ai-providers-card"]),
        role: "radio",
        tabindex: __props.usableAsDefault ? 0 : -1,
        onClick: _cache[7] || (_cache[7] = $event => selectProvider()),
        onKeydown: [_cache[8] || (_cache[8] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => selectProvider(), ["prevent"]), ["enter"])), _cache[9] || (_cache[9] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => selectProvider(), ["prevent"]), ["space"]))]
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.provider.name), 1), __props.selected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultBadge')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])(__props.provider.description)), 1)]), __props.canEdit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 0
      }, [__props.provider.supportsCustomEndpoint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["Field"]), {
        key: 0,
        class: "ai-providers-endpoint-field",
        "model-value": (_props$configuration3 = __props.configuration) === null || _props$configuration3 === void 0 ? void 0 : _props$configuration3.endpointUrl,
        name: `endpointUrl-${__props.provider.id}`,
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])(__props.provider.endpointFieldTitle),
        placeholder: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])(__props.provider.endpointFieldPlaceholder),
        autocomplete: "off",
        "full-width": "",
        uicontrol: "text",
        "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => emit('update:endpointUrl', `${$event}`))
      }, null, 8, ["model-value", "name", "title", "placeholder"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), __props.provider.supportsFipsEndpoint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["Field"]), {
        key: 1,
        class: "ai-providers-fips-field",
        "model-value": (_props$configuration4 = __props.configuration) === null || _props$configuration4 === void 0 ? void 0 : _props$configuration4.useFipsEndpoint,
        name: `useFipsEndpoint-${__props.provider.id}`,
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_BedrockUseFipsEndpoint'),
        "full-width": "",
        uicontrol: "checkbox",
        "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => emit('update:useFipsEndpoint', !!$event))
      }, null, 8, ["model-value", "name", "title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["Field"]), {
        "model-value": (_props$configuration5 = __props.configuration) === null || _props$configuration5 === void 0 ? void 0 : _props$configuration5.apiKey,
        name: `apiKey-${__props.provider.id}`,
        placeholder: __props.provider.configuration.hasApiKey ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ApiKeyAlreadyConfiguredPlaceholder') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ApiKeyPlaceholder'),
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ApiKey'),
        autocomplete: "new-password",
        "full-width": "",
        uicontrol: "password",
        "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => emit('update:apiKey', `${$event}`))
      }, null, 8, ["model-value", "name", "placeholder", "title"]), [[Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["AutoClearPassword"])]]), __props.provider.supportsCustomEndpoint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [__props.availableModels.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["Field"]), {
        key: 0,
        "model-value": (_props$configuration6 = __props.configuration) === null || _props$configuration6 === void 0 ? void 0 : _props$configuration6.model,
        name: `model-${__props.provider.id}`,
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_Model'),
        options: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(modelOptions),
        "full-width": "",
        uicontrol: "select",
        "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => emit('update:model', `${$event}`))
      }, null, 8, ["model-value", "name", "title", "options"])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ClickTestConnectionToShowAvailableModels')), 1)), __props.availableModels.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 2,
        class: "btn-flat ai-providers-refresh-models",
        type: "button",
        disabled: __props.isTesting || !Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(canTest),
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_RefreshModels'),
        onClick: _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => emit('test'), ["prevent", "stop"]))
      }, [_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_RefreshModels')), 1)], 8, _hoisted_10)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
          'is-connected': __props.provider.configuration.isUsable
        }, "ai-providers-card-status"])
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        "aria-hidden": "true",
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["icon ai-providers-status-icon", __props.provider.configuration.isUsable ? 'icon-ok' : 'icon-minus'])
      }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.provider.configuration.isUsable ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_StatusConnected') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_StatusNotConnected')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        class: "btn btn-outline btn-small",
        type: "button",
        disabled: __props.isTesting || !Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(canTest),
        onClick: _cache[5] || (_cache[5] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => emit('test'), ["prevent", "stop"]))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.isTesting ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_TestingConnection') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_TestConnection')), 9, _hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        class: "btn-flat",
        type: "button",
        disabled: __props.isDisconnecting || !__props.provider.configuration.hasApiKey,
        onClick: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => emit('disconnect'), ["prevent", "stop"]))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.isDisconnecting ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_Disconnecting') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_Disconnect')), 9, _hoisted_14)])], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 42, _hoisted_1);
    };
  }
}));
// CONCATENATED MODULE: ./plugins/AIProviders/vue/src/components/ProviderCard.vue?vue&type=script&setup=true&lang=ts
 
// EXTERNAL MODULE: ./plugins/AIProviders/vue/src/components/ProviderCard.vue?vue&type=style&index=0&id=30b60d9a&lang=less
var ProviderCardvue_type_style_index_0_id_30b60d9a_lang_less = __webpack_require__("9c8b");

// CONCATENATED MODULE: ./plugins/AIProviders/vue/src/components/ProviderCard.vue





/* harmony default export */ var ProviderCard = (ProviderCardvue_type_script_setup_true_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/AIProviders/vue/src/ManageAIProviders.vue?vue&type=script&setup=true&lang=ts


const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_1 = {
  class: "ai-providers-page"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_2 = {
  class: "ai-providers-page-header"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_3 = {
  class: "ai-providers-page-title"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_4 = {
  class: "ai-providers-page-subtitle"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_5 = {
  class: "ai-providers"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_6 = {
  class: "ai-providers-defaults-title"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_7 = {
  class: "ai-providers-section"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_8 = {
  class: "ai-providers-subsection-title"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_9 = {
  class: "ai-providers-section-help"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_10 = ["aria-label"];
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_11 = {
  key: 1,
  class: "ai-providers-section"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_12 = {
  class: "ai-providers-subsection-title"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_13 = {
  class: "ai-providers-section-help"
};
const ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_14 = ["aria-label"];
const _hoisted_15 = {
  class: "ai-providers-capability-header"
};
const _hoisted_16 = ["value"];
const _hoisted_17 = {
  class: "ai-providers-capability-label"
};
const _hoisted_18 = {
  key: 0,
  class: "ai-providers-capability-description"
};
const _hoisted_19 = {
  key: 2,
  class: "ai-providers-footer"
};
const _hoisted_20 = ["disabled"];




/* harmony default export */ var ManageAIProvidersvue_type_script_setup_true_lang_ts = (/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  __name: 'ManageAIProviders',
  setup(__props) {
    const settings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const isLoading = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const isSaving = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const defaultProviderId = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
    const defaultCapabilityLevel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
    const providerConfigurations = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])({});
    // Models discovered per provider from the last "test connection"/refresh, used
    // to populate the model picker.
    const availableModels = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])({});
    const testingProviders = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])({});
    const disconnectingProviders = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])({});
    const providers = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _settings$value;
      return ((_settings$value = settings.value) === null || _settings$value === void 0 ? void 0 : _settings$value.providers) || [];
    });
    const hasUsableProvider = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => providers.value.some(provider => provider.configuration.isUsable));
    const canEditCapabilityLevel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _settings$value2;
      return !!((_settings$value2 = settings.value) !== null && _settings$value2 !== void 0 && _settings$value2.canEditCapabilityLevel);
    });
    const canEditProviderConfiguration = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _settings$value3;
      return !!((_settings$value3 = settings.value) !== null && _settings$value3 !== void 0 && _settings$value3.canEditProviderConfiguration);
    });
    // Snapshot of the saved state, used to detect unsaved changes.
    const savedSnapshot = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
    const capabilityLevelOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      var _settings$value4;
      const capabilityLevels = ((_settings$value4 = settings.value) === null || _settings$value4 === void 0 ? void 0 : _settings$value4.capabilityLevels) || {};
      return Object.entries(capabilityLevels).map(([id, keys]) => ({
        id,
        label: Object(external_CoreHome_["translate"])(keys.label),
        description: keys.description ? Object(external_CoreHome_["translate"])(keys.description) : ''
      }));
    });
    // Serializes everything the user can edit, so it can be compared to the saved snapshot.
    function serializeEditableState() {
      return JSON.stringify({
        defaultProviderId: defaultProviderId.value,
        defaultCapabilityLevel: defaultCapabilityLevel.value,
        providerConfigurations: providerConfigurations.value
      });
    }
    const hasUnsavedChanges = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => !!settings.value && serializeEditableState() !== savedSnapshot.value);
    /**
     * Applies the given settings to the component state.
     * @param nextSettings
     */
    function applySettings(nextSettings) {
      settings.value = nextSettings;
      defaultProviderId.value = nextSettings.defaultProviderId;
      defaultCapabilityLevel.value = nextSettings.defaultCapabilityLevel;
      const nextProviderConfigurations = {};
      nextSettings.providers.forEach(provider => {
        const savedModel = provider.configuration.model || '';
        nextProviderConfigurations[provider.id] = {
          apiKey: '',
          endpointUrl: provider.configuration.endpointUrl || '',
          model: savedModel,
          useFipsEndpoint: provider.configuration.useFipsEndpoint || false
        };
      });
      providerConfigurations.value = nextProviderConfigurations;
      availableModels.value = {};
      // Capture the freshly applied state as the baseline for unsaved-change detection.
      savedSnapshot.value = serializeEditableState();
    }
    function markProviderUsable(providerId) {
      if (!settings.value) {
        return;
      }
      const provider = settings.value.providers.find(p => p.id === providerId);
      if (provider) {
        provider.configuration = Object.assign(Object.assign({}, provider.configuration), {}, {
          hasApiKey: true,
          isUsable: true
        });
      }
      if (!defaultProviderId.value) {
        defaultProviderId.value = providerId;
      }
    }
    function getCleanErrorMessage(error) {
      let message = '';
      if (error && typeof error === 'object' && 'message' in error) {
        message = `${error.message}`;
      } else {
        message = `${error}`;
      }
      return message.replace(/\s*#\d+\s+[\s\S]*$/, '').replace(/\s+/g, ' ').trim();
    }
    // Notification IDs may only contain word characters (alphanumerics + underscore),
    // see core/Notification/Manager.php::checkId(). Provider IDs can contain other
    // characters (e.g. hyphens), so sanitize before using them in a notification ID.
    function notificationId(id) {
      return id.replace(/[^\w]/g, '_');
    }
    function showErrorNotification(error, id) {
      const cleaned = getCleanErrorMessage(error);
      const isUseful = cleaned && cleaned !== 'Something went wrong';
      const message = isUseful ? Object(external_CoreHome_["translate"])('AIProviders_RequestFailed', cleaned) : Object(external_CoreHome_["translate"])('AIProviders_UnexpectedError');
      return external_CoreHome_["NotificationsStore"].show({
        message,
        type: 'transient',
        id,
        context: 'error'
      });
    }
    function updateModel(providerId, model) {
      providerConfigurations.value[providerId] = Object.assign(Object.assign({}, providerConfigurations.value[providerId]), {}, {
        model
      });
    }
    async function fetchAvailableModels(providerId, showNotifications) {
      testingProviders.value[providerId] = true;
      try {
        const response = await external_CoreHome_["AjaxHelper"].post({
          method: 'AIProviders.testConnection'
        }, {
          providerId,
          providerConfiguration: JSON.stringify(providerConfigurations.value[providerId] || {})
        }, {
          withTokenInUrl: true,
          createErrorNotification: false
        });
        markProviderUsable(providerId);
        if (response.models && response.models.length) {
          var _providerConfiguratio;
          availableModels.value[providerId] = response.models;
          // Default to the first discovered model when none is selected yet.
          if (!((_providerConfiguratio = providerConfigurations.value[providerId]) !== null && _providerConfiguratio !== void 0 && _providerConfiguratio.model)) {
            updateModel(providerId, response.models[0]);
          }
        }
        if (showNotifications) {
          external_CoreHome_["NotificationsStore"].show({
            message: Object(external_CoreHome_["translate"])('AIProviders_TestConnectionSuccess', response.providerName),
            type: 'transient',
            id: notificationId(`aiProvidersTest-${providerId}`),
            context: 'success'
          });
        }
      } catch (error) {
        if (showNotifications) {
          showErrorNotification(error, notificationId(`aiProvidersTestError-${providerId}`));
        }
      } finally {
        testingProviders.value[providerId] = false;
      }
    }
    async function loadSettings() {
      isLoading.value = true;
      try {
        const response = await external_CoreHome_["AjaxHelper"].fetch({
          method: 'AIProviders.getSettings'
        }, {
          createErrorNotification: false
        });
        applySettings(response);
        response.providers.filter(provider => provider.supportsCustomEndpoint && provider.configuration.isUsable).forEach(provider => {
          fetchAvailableModels(provider.id, false);
        });
      } catch (error) {
        showErrorNotification(error, 'aiProvidersLoadError');
      } finally {
        isLoading.value = false;
      }
    }
    function updateApiKey(providerId, apiKey) {
      providerConfigurations.value[providerId] = Object.assign(Object.assign({}, providerConfigurations.value[providerId]), {}, {
        apiKey
      });
    }
    function updateEndpointUrl(providerId, endpointUrl) {
      providerConfigurations.value[providerId] = Object.assign(Object.assign({}, providerConfigurations.value[providerId]), {}, {
        endpointUrl
      });
      availableModels.value[providerId] = [];
    }
    function updateUseFipsEndpoint(providerId, useFipsEndpoint) {
      providerConfigurations.value[providerId] = Object.assign(Object.assign({}, providerConfigurations.value[providerId]), {}, {
        useFipsEndpoint
      });
      availableModels.value[providerId] = [];
    }
    async function disconnectProvider(providerId) {
      disconnectingProviders.value[providerId] = true;
      try {
        const response = await external_CoreHome_["AjaxHelper"].post({
          method: 'AIProviders.disconnectProvider'
        }, {
          providerId
        }, {
          withTokenInUrl: true,
          createErrorNotification: false
        });
        applySettings(response);
        external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('AIProviders_DisconnectSuccess'),
          type: 'transient',
          id: notificationId(`aiProvidersDisconnect-${providerId}`),
          context: 'success'
        });
      } catch (error) {
        showErrorNotification(error, notificationId(`aiProvidersDisconnectError-${providerId}`));
      } finally {
        disconnectingProviders.value[providerId] = false;
      }
    }
    async function testConnection(providerId) {
      await fetchAvailableModels(providerId, true);
    }
    function cancelChanges() {
      if (settings.value) {
        applySettings(settings.value);
      }
    }
    /**
     * Saves the current settings to the server.
     */
    async function saveSettings() {
      isSaving.value = true;
      try {
        const response = await external_CoreHome_["AjaxHelper"].post({
          method: 'AIProviders.saveSettings'
        }, {
          defaultProviderId: defaultProviderId.value,
          defaultCapabilityLevel: defaultCapabilityLevel.value,
          providerConfigurations: JSON.stringify(providerConfigurations.value)
        }, {
          withTokenInUrl: true,
          createErrorNotification: false
        });
        applySettings(response);
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('AIProviders_SettingsSaveSuccess'),
          type: 'transient',
          id: 'aiProvidersSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      } catch (error) {
        const notificationInstanceId = showErrorNotification(error, 'aiProvidersSettingsError');
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      } finally {
        isSaving.value = false;
      }
    }
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(loadSettings);
    return (_ctx, _cache) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["EnrichedHeadline"]), null, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_MenuTitle')), 1)]),
        _: 1
      })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ConfigurationIntro')), 1)]), isLoading.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["ActivityIndicator"]), {
        key: 0,
        loading: isLoading.value
      }, null, 8, ["loading"])) : settings.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["ContentBlock"]), {
        key: 1,
        class: "ai-providers-content"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["ai-providers-unsaved-changes", {
            'is-visible': Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(hasUnsavedChanges)
          }])
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_UnsavedChanges')), 3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_5, [!Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(canEditProviderConfiguration) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Alert"]), {
          key: 0,
          severity: "info"
        }, {
          default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_ManagedConfigurationHelp')), 1)]),
          _: 1
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultsTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultProvider')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultProviderHelp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
          "aria-label": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultProvider'),
          class: "ai-providers-cards",
          role: "radiogroup"
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(providers), provider => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(ProviderCard, {
            key: provider.id,
            "available-models": availableModels.value[provider.id] || [],
            "can-edit": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(canEditProviderConfiguration),
            configuration: providerConfigurations.value[provider.id],
            "is-disconnecting": !!disconnectingProviders.value[provider.id],
            "is-testing": !!testingProviders.value[provider.id],
            provider: provider,
            selected: defaultProviderId.value === provider.id,
            "usable-as-default": provider.configuration.isUsable,
            onDisconnect: $event => disconnectProvider(provider.id),
            onSelect: $event => provider.configuration.isUsable ? defaultProviderId.value = provider.id : null,
            onTest: $event => testConnection(provider.id),
            "onUpdate:apiKey": $event => updateApiKey(provider.id, $event),
            "onUpdate:endpointUrl": $event => updateEndpointUrl(provider.id, $event),
            "onUpdate:model": $event => updateModel(provider.id, $event),
            "onUpdate:useFipsEndpoint": $event => updateUseFipsEndpoint(provider.id, $event)
          }, null, 8, ["available-models", "can-edit", "configuration", "is-disconnecting", "is-testing", "provider", "selected", "usable-as-default", "onDisconnect", "onSelect", "onTest", "onUpdate:apiKey", "onUpdate:endpointUrl", "onUpdate:model", "onUpdate:useFipsEndpoint"]);
        }), 128))], 8, ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_10), !Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(hasUsableProvider) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Alert"]), {
          key: 0,
          class: "ai-providers-default-warning",
          severity: "warning"
        }, {
          default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_NoDefaultProviderWarning')), 1)]),
          _: 1
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(canEditCapabilityLevel) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultCapabilityLevel')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultCapabilityLevelHelp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
          "aria-label": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('AIProviders_DefaultCapabilityLevel'),
          class: "ai-providers-capability-cards",
          role: "radiogroup"
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(capabilityLevelOptions), capability => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", {
            key: capability.id,
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
              'is-selected': defaultCapabilityLevel.value === capability.id
            }, "ai-providers-capability-card"])
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
            "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => defaultCapabilityLevel.value = $event),
            value: capability.id,
            name: "defaultCapabilityLevel",
            type: "radio"
          }, null, 8, _hoisted_16), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], defaultCapabilityLevel.value]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.label), 1)]), capability.description ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.description), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
        }), 128))], 8, ManageAIProvidersvue_type_script_setup_true_lang_ts_hoisted_14)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])), [[Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["Form"])]])]),
        _: 1
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), settings.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        disabled: isSaving.value || !Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(hasUnsavedChanges),
        class: "btn btn-outline",
        type: "button",
        onClick: _cache[1] || (_cache[1] = $event => cancelChanges())
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('General_Cancel')), 9, _hoisted_20), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CorePluginsAdmin_["SaveButton"]), {
        disabled: !Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(hasUnsavedChanges),
        saving: isSaving.value,
        onConfirm: _cache[2] || (_cache[2] = $event => saveSettings())
      }, null, 8, ["disabled", "saving"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    };
  }
}));
// CONCATENATED MODULE: ./plugins/AIProviders/vue/src/ManageAIProviders.vue?vue&type=script&setup=true&lang=ts
 
// EXTERNAL MODULE: ./plugins/AIProviders/vue/src/ManageAIProviders.vue?vue&type=style&index=0&id=464239d2&lang=less
var ManageAIProvidersvue_type_style_index_0_id_464239d2_lang_less = __webpack_require__("2a82");

// CONCATENATED MODULE: ./plugins/AIProviders/vue/src/ManageAIProviders.vue





/* harmony default export */ var ManageAIProviders = (ManageAIProvidersvue_type_script_setup_true_lang_ts);
// CONCATENATED MODULE: ./plugins/AIProviders/vue/src/index.ts
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
//# sourceMappingURL=AIProviders.umd.js.map