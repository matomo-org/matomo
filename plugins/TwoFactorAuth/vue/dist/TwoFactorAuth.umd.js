(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("Login"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", "Login", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["TwoFactorAuth"] = factory(require("CoreHome"), require("Login"), require("vue"), require("CorePluginsAdmin"));
	else
		root["TwoFactorAuth"] = factory(root["CoreHome"], root["Login"], root["Vue"], root["CorePluginsAdmin"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__5b81__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__) {
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
/******/ 	__webpack_require__.p = "plugins/TwoFactorAuth/vue/dist/";
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

/***/ "5b81":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__5b81__;

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
__webpack_require__.d(__webpack_exports__, "ShowRecoveryCodes", function() { return /* reexport */ ShowRecoveryCodes; });
__webpack_require__.d(__webpack_exports__, "ShowRecoveryCodesPage", function() { return /* reexport */ ShowRecoveryCodesPage; });
__webpack_require__.d(__webpack_exports__, "SetupTwoFactorAuth", function() { return /* reexport */ SetupTwoFactorAuth; });
__webpack_require__.d(__webpack_exports__, "LoginTwoFactorAuth", function() { return /* reexport */ LoginTwoFactorAuth; });
__webpack_require__.d(__webpack_exports__, "SetupFinished", function() { return /* reexport */ SetupFinished; });
__webpack_require__.d(__webpack_exports__, "UserSettings", function() { return /* reexport */ UserSettings; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=43bd6978

const _hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_3 = {
  class: "alert alert-warning"
};
const _hoisted_4 = {
  key: 0,
  class: "twoFactorRecoveryCodes browser-default"
};
const _hoisted_5 = {
  key: 1,
  class: "alert alert-danger"
};
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = ["value"];
const _hoisted_8 = ["value"];
const _hoisted_9 = ["value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$codes;
  const _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesExplanation')), 1), _hoisted_1, _hoisted_2]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesSecurity')), 1), (_ctx$codes = _ctx.codes) !== null && _ctx$codes !== void 0 && _ctx$codes.length ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", _hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.codes, (code, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: index
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(code.toUpperCase().match(/.{1,4}/g).join('-')), 1);
  }), 128))])), [[_directive_select_on_focus, {}]]) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesAllUsed')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[0] || (_cache[0] = $event => {
      _ctx.downloadRecoveryCodes();
      _ctx.$emit('downloaded');
    }),
    value: _ctx.translate('General_Download'),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[1] || (_cache[1] = $event => {
      _ctx.print();
      _ctx.$emit('downloaded');
    }),
    value: _ctx.translate('General_Print'),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[2] || (_cache[2] = $event => {
      _ctx.copyRecoveryCodesToClipboard();
      _ctx.$emit('downloaded');
    }),
    value: _ctx.translate('General_Copy')
  }, null, 8, _hoisted_9)])]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=43bd6978

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts


/* harmony default export */ var ShowRecoveryCodesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    codes: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  emits: ['downloaded'],
  methods: {
    copyRecoveryCodesToClipboard() {
      const textarea = document.createElement('textarea');
      textarea.value = this.codes.join('\n');
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    },
    downloadRecoveryCodes() {
      external_CoreHome_["Matomo"].helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codes.join('\n'));
    },
    print() {
      window.print();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue



ShowRecoveryCodesvue_type_script_lang_ts.render = render

/* harmony default export */ var ShowRecoveryCodes = (ShowRecoveryCodesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=template&id=13e8e57f

const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_3 = {
  key: 0,
  class: "alert alert-success"
};
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_4 = {
  key: 1,
  class: "alert alert-danger"
};
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_5 = ["action"];
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_6 = ["value"];
const ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_7 = ["value"];
function ShowRecoveryCodesPagevue_type_template_id_13e8e57f_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ShowRecoveryCodes = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShowRecoveryCodes");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitle
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ShowRecoveryCodes, {
      codes: _ctx.codes
    }, null, 8, ["codes"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_GenerateNewRecoveryCodes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_GenerateNewRecoveryCodesInfo')), 1), ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_1, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_2]), _ctx.regenerateSuccess ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesRegenerated')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.regenerateError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ExceptionSecurityCheckFailed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      method: "post",
      action: _ctx.showRecoveryCodesLink
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "regenerateNonce",
      value: _ctx.regenerateNonce
    }, null, 8, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "submit",
      class: "btn",
      value: _ctx.translate('TwoFactorAuth_GenerateNewRecoveryCodes')
    }, null, 8, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_7)], 8, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_5)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=template&id=13e8e57f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



/* harmony default export */ var ShowRecoveryCodesPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    codes: Array,
    regenerateSuccess: Boolean,
    regenerateError: Boolean,
    regenerateNonce: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    ShowRecoveryCodes: ShowRecoveryCodes
  },
  computed: {
    contentTitle() {
      const part1 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthentication');
      const part2 = Object(external_CoreHome_["translate"])('TwoFactorAuth_RecoveryCodes');
      return `${part1} - ${part2}`;
    },
    showRecoveryCodesLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue



ShowRecoveryCodesPagevue_type_script_lang_ts.render = ShowRecoveryCodesPagevue_type_template_id_13e8e57f_render

/* harmony default export */ var ShowRecoveryCodesPage = (ShowRecoveryCodesPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4ecbaa44

const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_1 = {
  class: "setupTwoFactorAuthentication",
  ref: "root"
};
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_2 = {
  key: 0,
  class: "alert alert-warning"
};
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_3 = ["disabled"];
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep2",
  id: "twoFactorStep2",
  style: {
    "opacity": "0"
  }
}, null, -1);
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://github.com/andOTP/andOTP#downloads"
}, "andOTP", -1);
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://authy.com/guides/github/"
}, "Authy", -1);
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.1password.com/one-time-passwords/"
}, "1Password", -1);
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
}, "LastPass Authenticator", -1);
const SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.google.com/accounts/answer/1066447"
}, "Google Authenticator", -1);
const _hoisted_10 = ["innerHTML"];
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_12 = {
  id: "qrcode",
  ref: "qrcode",
  title: ""
};
const _hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep3",
  id: "twoFactorStep3",
  style: {
    "opacity": "0"
  }
}, null, -1);
const _hoisted_15 = {
  key: 0,
  class: "message_container"
};
const _hoisted_16 = ["innerHTML"];
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = ["action"];
const _hoisted_19 = ["value"];
const _hoisted_20 = ["disabled", "value"];
const _hoisted_21 = {
  id: "setupTwoFAsecretConfirm",
  class: "ui-confirm"
};
const _hoisted_22 = {
  style: {
    "text-align": "center"
  }
};
const _hoisted_23 = {
  style: {
    "font-size": "30px"
  }
};
const _hoisted_24 = ["value"];
function SetupTwoFactorAuthvue_type_template_id_4ecbaa44_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ShowRecoveryCodes = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShowRecoveryCodes");
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.standalone ? _ctx.translate('TwoFactorAuth_RequiredToSetUpTwoFactorAuthentication') : _ctx.translate('TwoFactorAuth_SetUpTwoFactorAuthentication')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_1, [_ctx.isAlreadyUsing2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_WarningChangingConfiguredDevice')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupIntroFollowSteps')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 1)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ShowRecoveryCodes, {
      codes: _ctx.codes,
      onDownloaded: _cache[0] || (_cache[0] = $event => this.hasDownloadedRecoveryCode = true)
    }, null, 8, ["codes"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "alert alert-info backupRecoveryCodesAlert"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupBackupRecoveryCodes')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      class: "btn goToStep2",
      onClick: _cache[1] || (_cache[1] = $event => _ctx.nextStep()),
      disabled: !_ctx.hasDownloadedRecoveryCode
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 9, SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]])]), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 2)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDevice')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1')) + " ", 1), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", "), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", "), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", "), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Or')) + " ", 1), SetupTwoFactorAuthvue_type_template_id_4ecbaa44_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ")]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2)
    }, null, 8, _hoisted_10)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_12, null, 512)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      class: "btn goToStep3",
      onClick: _cache[2] || (_cache[2] = $event => _ctx.nextStep())
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 2]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 2]]), _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 3)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfirmSetup')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyAuthCodeIntro')), 1), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
      noclear: true,
      context: "error"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
      }, null, 8, _hoisted_16), _hoisted_17]),
      _: 1
    })])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      method: "post",
      class: "setupConfirmAuthCodeForm",
      autocorrect: "off",
      autocapitalize: "none",
      autocomplete: "off",
      action: _ctx.linkTo({
        'module': 'TwoFactorAuth',
        'action': _ctx.submitAction
      })
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "authCode",
      title: _ctx.translate('TwoFactorAuth_AuthenticationCode'),
      modelValue: _ctx.authCode,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.authCode = $event),
      maxlength: 6,
      placeholder: '123456',
      "inline-help": _ctx.translate('TwoFactorAuth_VerifyAuthCodeHelp')
    }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "authCodeNonce",
      value: _ctx.authCodeNonce
    }, null, 8, _hoisted_19), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "submit",
      class: "btn confirmAuthCode",
      disabled: _ctx.authCode.length !== 6,
      value: _ctx.translate('General_Confirm')
    }, null, 8, _hoisted_20)], 8, _hoisted_18)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 3]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_Your2FaAuthSecret')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("code", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.newSecret), 1)])), [[_directive_select_on_focus, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      role: "ok",
      type: "button",
      value: _ctx.translate('General_Ok')
    }, null, 8, _hoisted_24)])], 512)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4ecbaa44

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts
function SetupTwoFactorAuthvue_type_script_lang_ts_extends() { SetupTwoFactorAuthvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SetupTwoFactorAuthvue_type_script_lang_ts_extends.apply(this, arguments); }





const {
  QRCode,
  $
} = window;
/* harmony default export */ var SetupTwoFactorAuthvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isAlreadyUsing2fa: Boolean,
    accessErrorString: String,
    submitAction: {
      type: String,
      required: true
    },
    authCodeNonce: {
      type: String,
      required: true
    },
    newSecret: {
      type: String,
      required: true
    },
    codes: Array,
    twoFaBarCodeSetupUrl: {
      type: String,
      required: true
    },
    standalone: Boolean
  },
  components: {
    ShowRecoveryCodes: ShowRecoveryCodes,
    Notification: external_CoreHome_["Notification"],
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  data() {
    return {
      step: 1,
      hasDownloadedRecoveryCode: false,
      authCode: ''
    };
  },
  mounted() {
    setTimeout(() => {
      const qrcode = this.$refs.qrcode;
      // eslint-disable-next-line no-new
      new QRCode(qrcode, {
        text: this.twoFaBarCodeSetupUrl
      });
      $(qrcode).attr('title', ''); // do not show secret on hover
      if (this.accessErrorString) {
        // user entered something wrong
        this.step = 3;
        this.scrollToEnd();
      }
      $(this.$refs.root).on('click', '.setupStep2Link', e => {
        e.preventDefault();
        external_CoreHome_["Matomo"].helper.modalConfirm('#setupTwoFAsecretConfirm');
      });
    });
  },
  methods: {
    scrollToEnd() {
      setTimeout(() => {
        let id = '';
        if (this.step === 2) {
          id = '#twoFactorStep2';
        } else if (this.step === 3) {
          id = '#twoFactorStep3';
        }
        if (id) {
          external_CoreHome_["Matomo"].helper.lazyScrollTo(id, 50, true);
        }
      }, 50);
    },
    nextStep() {
      this.step += 1;
      this.scrollToEnd();
    },
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(SetupTwoFactorAuthvue_type_script_lang_ts_extends(SetupTwoFactorAuthvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  },
  computed: {
    setupAuthenticatorOnDeviceStep2() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_SetupAuthenticatorOnDeviceStep2', '<a class="setupStep2Link">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue



SetupTwoFactorAuthvue_type_script_lang_ts.render = SetupTwoFactorAuthvue_type_template_id_4ecbaa44_render

/* harmony default export */ var SetupTwoFactorAuth = (SetupTwoFactorAuthvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=template&id=6b8b0945

const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_1 = {
  class: "message_container"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_2 = ["innerHTML"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_4 = {
  class: "row"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_5 = {
  class: "col s12 input-field"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_6 = ["value"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "text",
  name: "form_authcode",
  placeholder: "",
  id: "login_form_authcode",
  class: "input",
  value: "",
  size: "20",
  autocorrect: "off",
  autocapitalize: "none",
  autocomplete: "off",
  tabindex: "10",
  autofocus: "autofocus"
}, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_8 = {
  for: "login_form_authcode"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-user icon"
}, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_10 = {
  class: "row actions"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_11 = {
  class: "col s12"
};
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_12 = ["value"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_13 = ["innerHTML"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_17 = ["href"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_19 = ["href"];
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_21 = ["href"];
function LoginTwoFactorAuthvue_type_template_id_6b8b0945_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FormErrors = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormErrors");
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('TwoFactorAuth_TwoFactorAuthentication')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FormErrors, {
      "form-errors": _ctx.formData.errors
    }, null, 8, ["form-errors"]), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
      key: 0,
      noclear: true,
      context: "error"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
      }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_2), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_3]),
      _: 1
    })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(_ctx.formDataAttributes, {
      class: "loginTwoFaForm"
    }), [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "form_nonce",
      id: "login_form_nonce",
      value: _ctx.formNonce
    }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_6), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_8, [LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_AuthenticationCode')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "submit btn",
      id: "login_form_submit",
      type: "submit",
      value: _ctx.translate('TwoFactorAuth_Verify'),
      tabindex: "100"
    }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_12)])])], 16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyIdentifyExplanation')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
    }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_13), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_14, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_DontHaveYourMobileDevice')), 1), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/how-to/faq_27248'),
      rel: "noreferrer noopener",
      target: "_blank"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_EnterRecoveryCodeInstead')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_17), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.mailToLink,
      rel: "noreferrer noopener"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_AskSuperUserResetAuthenticationCode')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_19), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.logoutLink,
      rel: "noreferrer noopener"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Logout')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_21)])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=template&id=6b8b0945

// EXTERNAL MODULE: external "Login"
var external_Login_ = __webpack_require__("5b81");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=script&lang=ts



/* harmony default export */ var LoginTwoFactorAuthvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    formData: {
      type: Object,
      required: true
    },
    accessErrorString: String,
    formNonce: {
      type: String,
      required: true
    },
    loginModule: {
      type: String,
      required: true
    },
    piwikUrl: String,
    userLogin: {
      type: String,
      required: true
    },
    contactEmail: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Notification: external_CoreHome_["Notification"],
    FormErrors: external_Login_["FormErrors"]
  },
  computed: {
    learnMoreText() {
      return Object(external_CoreHome_["translate"])('General_LearnMore', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_27245'), '</a>');
    },
    mailToLink() {
      return `mailto:${this.contactEmail}?${external_CoreHome_["MatomoUrl"].stringify({
        subject: Object(external_CoreHome_["translate"])('TwoFactorAuth_NotPossibleToLogIn'),
        body: Object(external_CoreHome_["translate"])('TwoFactorAuth_LostAuthenticationDevice', '\n\n', '\n\n', this.piwikUrl || '', '\n\n', this.userLogin, Object(external_CoreHome_["externalRawLink"])('https://matomo.org/faq/how-to/faq_27248'))
      })}`;
    },
    logoutLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify({
        module: this.loginModule,
        action: 'logout'
      })}`;
    },
    formDataAttributes() {
      // convert html attribute string (ie 'a="b" d="f"') to JS object {a: "b", d: "f"}
      return Object.fromEntries(this.formData.attributes.split(/\s+/g).filter(s => s).map(pair => pair.split('=')).map(([name, value]) => [name, external_CoreHome_["Matomo"].helper.htmlDecode(value.substr(1, value.length - 2))]));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue



LoginTwoFactorAuthvue_type_script_lang_ts.render = LoginTwoFactorAuthvue_type_template_id_6b8b0945_render

/* harmony default export */ var LoginTwoFactorAuth = (LoginTwoFactorAuthvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=template&id=631d8678

const SetupFinishedvue_type_template_id_631d8678_hoisted_1 = {
  class: "successMessage"
};
const SetupFinishedvue_type_template_id_631d8678_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const SetupFinishedvue_type_template_id_631d8678_hoisted_3 = ["href"];
function SetupFinishedvue_type_template_id_631d8678_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "twoFactorSetupFinished"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", SetupFinishedvue_type_template_id_631d8678_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupFinishedTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupFinishedSubtitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SetupFinishedvue_type_template_id_631d8678_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "btn",
      href: _ctx.userSecurityLink
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Continue')), 9, SetupFinishedvue_type_template_id_631d8678_hoisted_3)])]),
    _: 1
  });
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=template&id=631d8678

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=script&lang=ts
function SetupFinishedvue_type_script_lang_ts_extends() { SetupFinishedvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SetupFinishedvue_type_script_lang_ts_extends.apply(this, arguments); }


/* harmony default export */ var SetupFinishedvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  computed: {
    userSecurityLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(SetupFinishedvue_type_script_lang_ts_extends(SetupFinishedvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'userSecurity'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue



SetupFinishedvue_type_script_lang_ts.render = SetupFinishedvue_type_template_id_631d8678_render

/* harmony default export */ var SetupFinished = (SetupFinishedvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=790c613d

const UserSettingsvue_type_template_id_790c613d_hoisted_1 = ["innerHTML"];
const UserSettingsvue_type_template_id_790c613d_hoisted_2 = {
  key: 0
};
const UserSettingsvue_type_template_id_790c613d_hoisted_3 = {
  class: "twoFaStatusEnabled"
};
const UserSettingsvue_type_template_id_790c613d_hoisted_4 = {
  key: 1
};
const UserSettingsvue_type_template_id_790c613d_hoisted_5 = {
  key: 0
};
const UserSettingsvue_type_template_id_790c613d_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const UserSettingsvue_type_template_id_790c613d_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const UserSettingsvue_type_template_id_790c613d_hoisted_8 = ["href"];
const UserSettingsvue_type_template_id_790c613d_hoisted_9 = {
  key: 1
};
const UserSettingsvue_type_template_id_790c613d_hoisted_10 = ["href"];
const UserSettingsvue_type_template_id_790c613d_hoisted_11 = ["href"];
const UserSettingsvue_type_template_id_790c613d_hoisted_12 = ["value"];
const UserSettingsvue_type_template_id_790c613d_hoisted_13 = ["href"];
const UserSettingsvue_type_template_id_790c613d_hoisted_14 = {
  key: 2
};
const UserSettingsvue_type_template_id_790c613d_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const UserSettingsvue_type_template_id_790c613d_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const UserSettingsvue_type_template_id_790c613d_hoisted_17 = ["href"];
const UserSettingsvue_type_template_id_790c613d_hoisted_18 = {
  id: "confirmDisable2FA",
  class: "ui-confirm",
  ref: "confirmDisable2FA"
};
const UserSettingsvue_type_template_id_790c613d_hoisted_19 = ["value"];
const UserSettingsvue_type_template_id_790c613d_hoisted_20 = ["value"];
function UserSettingsvue_type_template_id_790c613d_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitle,
    class: "userSettings2FA"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.twoFactorAuthIntro)
    }, null, 8, UserSettingsvue_type_template_id_790c613d_hoisted_1), _ctx.isEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserSettingsvue_type_template_id_790c613d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", UserSettingsvue_type_template_id_790c613d_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_TwoFactorAuthenticationIsEnabled')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserSettingsvue_type_template_id_790c613d_hoisted_4, [_ctx.isForced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UserSettingsvue_type_template_id_790c613d_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_TwoFactorAuthenticationRequired')) + " ", 1), UserSettingsvue_type_template_id_790c613d_hoisted_6, UserSettingsvue_type_template_id_790c613d_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "btn btn-link enable2FaLink",
      href: _ctx.setupTwoFactorAuthLink,
      style: {
        "margin-right": "3.5px"
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfigureDifferentDevice')), 9, UserSettingsvue_type_template_id_790c613d_hoisted_8)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UserSettingsvue_type_template_id_790c613d_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "btn btn-link enable2FaLink",
      href: _ctx.setupTwoFactorAuthLink,
      style: {
        "margin-right": "3.5px"
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfigureDifferentDevice')), 9, UserSettingsvue_type_template_id_790c613d_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.disableTwoFactorAuthLink,
      style: {
        "display": "none"
      },
      id: "disable2fa"
    }, "disable2fa", 8, UserSettingsvue_type_template_id_790c613d_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      class: "btn btn-link disable2FaLink",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.onDisable2FaLinkClick()),
      value: _ctx.translate('TwoFactorAuth_DisableTwoFA'),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, UserSettingsvue_type_template_id_790c613d_hoisted_12)])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "btn btn-link showRecoveryCodesLink",
      href: _ctx.showRecoveryCodesLink
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ShowRecoveryCodes')), 9, UserSettingsvue_type_template_id_790c613d_hoisted_13)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserSettingsvue_type_template_id_790c613d_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_TwoFactorAuthenticationIsDisabled')), 1), UserSettingsvue_type_template_id_790c613d_hoisted_15, UserSettingsvue_type_template_id_790c613d_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "btn btn-link enable2FaLink",
      href: _ctx.setupTwoFactorAuthLink
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_EnableTwoFA')), 9, UserSettingsvue_type_template_id_790c613d_hoisted_17)])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserSettingsvue_type_template_id_790c613d_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfirmDisableTwoFA')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      role: "yes",
      type: "button",
      value: _ctx.translate('General_Yes')
    }, null, 8, UserSettingsvue_type_template_id_790c613d_hoisted_19), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      role: "no",
      type: "button",
      value: _ctx.translate('General_No')
    }, null, 8, UserSettingsvue_type_template_id_790c613d_hoisted_20)], 512)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=790c613d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=script&lang=ts
function UserSettingsvue_type_script_lang_ts_extends() { UserSettingsvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return UserSettingsvue_type_script_lang_ts_extends.apply(this, arguments); }


/* harmony default export */ var UserSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isEnabled: Boolean,
    isForced: Boolean,
    disableNonce: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  computed: {
    contentTitle() {
      const part1 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthentication');
      const part2 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFAShort');
      return `${part1} (${part2})`;
    },
    twoFactorAuthIntro() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthenticationIntro', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_27245'), '</a>');
    },
    setupTwoFactorAuthLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSettingsvue_type_script_lang_ts_extends(UserSettingsvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'setupTwoFactorAuth'
      }))}`;
    },
    disableTwoFactorAuthLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSettingsvue_type_script_lang_ts_extends(UserSettingsvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'disableTwoFactorAuth',
        disableNonce: this.disableNonce
      }))}`;
    },
    showRecoveryCodesLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSettingsvue_type_script_lang_ts_extends(UserSettingsvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes'
      }))}`;
    }
  },
  methods: {
    onDisable2FaLinkClick() {
      const nonce = this.disableNonce;
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmDisable2FA, {
        yes() {
          external_CoreHome_["MatomoUrl"].updateUrl({
            module: 'TwoFactorAuth',
            action: 'disableTwoFactorAuth',
            disableNonce: nonce
          });
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue



UserSettingsvue_type_script_lang_ts.render = UserSettingsvue_type_template_id_790c613d_render

/* harmony default export */ var UserSettings = (UserSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/index.ts
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
//# sourceMappingURL=TwoFactorAuth.umd.js.map