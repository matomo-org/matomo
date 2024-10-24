(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["JsTrackerInstallCheck"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["JsTrackerInstallCheck"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/JsTrackerInstallCheck/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "JsTrackerInstallCheck", function() { return /* reexport */ JsTrackerInstallCheck; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=dd44ee20

const _hoisted_1 = {
  class: "jsTrackerInstallCheck"
};
const _hoisted_2 = {
  class: "row testInstallFields"
};
const _hoisted_3 = {
  class: "col s2"
};
const _hoisted_4 = {
  class: "col s10"
};
const _hoisted_5 = ["disabled", "value"];
const _hoisted_6 = {
  class: "system-success success-message"
};
const _hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);
const _hoisted_8 = {
  class: "system-errors test-error"
};
const _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);
const _hoisted_10 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('JsTrackerInstallCheck_OptionalTestInstallationDescription')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "url",
    name: "baseUrl",
    placeholder: "https://example.com",
    modelValue: _ctx.baseUrl,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.baseUrl = $event),
    "full-width": true,
    disabled: _ctx.isTesting
  }, null, 8, ["modelValue", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn testInstallBtn",
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.initiateTrackerTest && _ctx.initiateTrackerTest(...args)),
    disabled: !_ctx.baseUrl || _ctx.isTesting,
    value: _ctx.translate('JsTrackerInstallCheck_TestInstallationBtnText')
  }, null, 8, _hoisted_5)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isTesting,
    loadingMessage: _ctx.translate('General_Testing')
  }, null, 8, ["loading", "loadingMessage"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isTestSuccess]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("  "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.getTestFailureMessage)
  }, null, 8, _hoisted_10)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isTestComplete && !_ctx.isTestSuccess]])])], 64);
}
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=dd44ee20

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts



const MAX_NUM_API_CALLS = 10;
const TIME_BETWEEN_API_CALLS = 1000;
/* harmony default export */ var JsTrackerInstallCheckvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data() {
    return {
      checkNonce: '',
      isTesting: false,
      isTestComplete: false,
      isTestSuccess: false,
      testTimeoutCount: 0,
      baseUrl: ''
    };
  },
  props: {
    site: {
      type: Object,
      required: true
    },
    isWordpress: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  created() {
    this.checkWhetherSuccessWasRecorded();
  },
  watch: {
    site() {
      this.onSiteChange();
    }
  },
  methods: {
    onSiteChange() {
      this.checkNonce = '';
      this.isTesting = false;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      this.checkWhetherSuccessWasRecorded();
    },
    initiateTrackerTest() {
      this.isTesting = true;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      const siteRef = this.site;
      const postParams = {
        idSite: siteRef.id,
        url: ''
      };
      if (this.baseUrl) {
        postParams.url = this.baseUrl;
      }
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'JsTrackerInstallCheck.initiateJsTrackerInstallTest'
      }, postParams).then(response => {
        const isSuccess = response && response.url && response.nonce;
        if (isSuccess) {
          this.checkNonce = response.nonce;
          const windowRef = window.open(response.url);
          this.setCheckInTime();
          setTimeout(() => {
            if (windowRef && !windowRef.closed) {
              windowRef.close();
              // Set the timeout to the max since we've already waited too long
              this.testTimeoutCount = MAX_NUM_API_CALLS;
            }
          }, MAX_NUM_API_CALLS * TIME_BETWEEN_API_CALLS);
        }
      }).catch(() => {
        this.isTesting = false;
      });
    },
    setCheckInTime() {
      setTimeout(this.checkWhetherSuccessWasRecorded, TIME_BETWEEN_API_CALLS);
    },
    checkWhetherSuccessWasRecorded() {
      const siteRef = this.site;
      const postParams = {
        idSite: siteRef.id,
        nonce: ''
      };
      if (this.checkNonce) {
        postParams.nonce = this.checkNonce;
      }
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'JsTrackerInstallCheck.wasJsTrackerInstallTestSuccessful'
      }, postParams).then(response => {
        if (response && response.mainUrl && !this.baseUrl) {
          this.baseUrl = response.mainUrl;
        }
        this.isTestSuccess = response && response.isSuccess;
        // If the test isn't successful but hasn't exceeded the timeout count, wait and check again
        if (this.checkNonce && !this.isTestSuccess && this.testTimeoutCount < MAX_NUM_API_CALLS) {
          this.testTimeoutCount += 1;
          this.setCheckInTime();
          return;
        }
        this.isTestComplete = !!this.checkNonce;
        this.isTesting = false;
      }).catch(() => {
        this.isTesting = false;
      });
    }
  },
  computed: {
    getTestFailureMessage() {
      const learnMoreLink = Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/troubleshooting/faq_58/');
      const closingTag = '</a>';
      if (!this.isWordpress) {
        return Object(external_CoreHome_["translate"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage', learnMoreLink, closingTag);
      }
      return Object(external_CoreHome_["translate"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress', '<a target="_blank" rel="noreferrer noopener" href="https://wordpress.org/plugins/wp-piwik/">WP-Matomo Integration (WP-Piwik)</a>', learnMoreLink, closingTag);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue



JsTrackerInstallCheckvue_type_script_lang_ts.render = render

/* harmony default export */ var JsTrackerInstallCheck = (JsTrackerInstallCheckvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/index.ts
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
//# sourceMappingURL=JsTrackerInstallCheck.umd.js.map