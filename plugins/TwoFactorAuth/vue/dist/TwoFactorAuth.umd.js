(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["TwoFactorAuth"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["TwoFactorAuth"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "e62f":
/***/ (function(module, exports) {



/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "SetupTwoFactorAuth", function() { return /* reexport */ SetupTwoFactorAuth; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=ab37a51c

var _hoisted_1 = {
  class: "setupTwoFactorAuthentication"
};
var _hoisted_2 = {
  key: 0,
  class: "alert alert-warning"
};

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_5 = {
  class: "alert alert-warning"
};
var _hoisted_6 = {
  key: 1,
  class: "twoFactorRecoveryCodes browser-default"
};
var _hoisted_7 = {
  key: 2,
  class: "alert alert-danger"
};

var _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_9 = ["value"];
var _hoisted_10 = ["value"];
var _hoisted_11 = ["disabled"];

var _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep2",
  id: "twoFactorStep2",
  style: {
    "opacity": "0"
  }
}, null, -1);

var _hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  rel: "noreferrer noopener",
  href: "https://github.com/andOTP/andOTP#downloads"
}, "andOTP", -1);

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  rel: "noreferrer noopener",
  href: "https://authy.com/guides/github/"
}, "Authy", -1);

var _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  rel: "noreferrer noopener",
  href: "https://support.1password.com/one-time-passwords/"
}, "1Password", -1);

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  rel: "noreferrer noopener",
  href: "https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
}, "LastPass Authenticator", -1);

var _hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  rel: "noreferrer noopener",
  href: "https://support.google.com/accounts/answer/1066447"
}, "Google Authenticator", -1);

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ");

var _hoisted_22 = ["innerHTML"];

var _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_24 = {
  ref: "qrcode",
  title: ""
};

var _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep3",
  id: "twoFactorStep3",
  style: {
    "opacity": "0"
  }
}, null, -1);

var _hoisted_27 = {
  key: 0,
  class: "message_container"
};

var _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": ");

var _hoisted_29 = ["innerHTML"];

var _hoisted_30 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_31 = ["action"];
var _hoisted_32 = ["value"];
var _hoisted_33 = ["disabled", "value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$codes,
      _this = this;

  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_ctx.isAlreadyUsing2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_WarningChangingConfiguredDevice')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupIntroFollowSteps')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 1)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesExplanation')), 1), _hoisted_3, _hoisted_4]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesSecurity')), 1), (_ctx$codes = _ctx.codes) !== null && _ctx$codes !== void 0 && _ctx$codes.length ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", _hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.codes, function (code, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: index
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(code.toUpperCase().split('', 4).join('-')), 1);
  }), 128))], 512)), [[_directive_select_on_focus, {}]]) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesAllUsed')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      _ctx.downloadRecoveryCodes();

      _this.hasDownloadedRecoveryCode = true;
    }),
    value: _ctx.translate('General_Download')
  }, null, 8, _hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[1] || (_cache[1] = function ($event) {
      _ctx.print();

      _this.hasDownloadedRecoveryCode = true;
    }),
    value: _ctx.translate('General_Print')
  }, null, 8, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      _ctx.copyRecoveryCodesToClipboard();

      _this.hasDownloadedRecoveryCode = true;
    }),
    value: "translate('General_Copy')"
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "alert alert-info backupRecoveryCodesAlert"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupBackupRecoveryCodes')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn goToStep2",
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.nextStep();
    }),
    disabled: !_ctx.hasDownloadedRecoveryCode
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 9, _hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]])]), _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 2)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDevice')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1')) + " ", 1), _hoisted_13, _hoisted_14, _hoisted_15, _hoisted_16, _hoisted_17, _hoisted_18, _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Or')) + " ", 1), _hoisted_20, _hoisted_21]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2)
  }, null, 8, _hoisted_22), _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_24, null, 512), _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn goToStep3",
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.nextStep();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 2]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 2]]), _hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 3)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfirmSetup')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyAuthCodeIntro')), 1), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    noclear: true,
    context: "error"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
      }, null, 8, _hoisted_29), _hoisted_30];
    }),
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
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.authCode = $event;
    }),
    maxlength: 6,
    placeholder: 123456,
    "inline-help": _ctx.translate('TwoFactorAuth_VerifyAuthCodeHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "hidden",
    name: "authCodeNonce",
    value: _ctx.authCodeNonce
  }, null, 8, _hoisted_32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "submit",
    class: "btn confirmAuthCode",
    disabled: _ctx.authCode.length !== 6,
    value: _ctx.translate('General_Confirm')
  }, null, 8, _hoisted_33)], 8, _hoisted_31)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 3]])]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=ab37a51c

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts




var _window = window,
    QRCode = _window.QRCode,
    $ = _window.$;
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
    codes: Array
  },
  components: {
    Notification: external_CoreHome_["Notification"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  data: function data() {
    return {
      step: 1,
      hasDownloadedRecoveryCode: false,
      authCode: ''
    };
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      var qrcode = _this.$refs.qrcode; // eslint-disable-next-line no-new

      new QRCode(qrcode, {
        text: window.twoFaBarCodeSetupUrl
      });
      $(qrcode).attr('title', ''); // do not show secret on hover

      if (_this.accessErrorString) {
        // user entered something wrong
        _this.step = 3;

        _this.scrollToEnd();
      }

      $(_this.$refs.root).on('click', '.setupStep2Link', function () {
        external_CoreHome_["Matomo"].helper.modalConfirm('#setupTwoFAsecretConfirm');
      });
    });
  },
  methods: {
    scrollToEnd: function scrollToEnd() {
      var _this2 = this;

      setTimeout(function () {
        var id = '';

        if (_this2.step === 2) {
          id = '#twoFactorStep2';
        } else if (_this2.step === 3) {
          id = '#twoFactorStep3';
        }

        if (id) {
          external_CoreHome_["Matomo"].helper.lazyScrollTo(id, 50, true);
        }
      }, 50);
    },
    nextStep: function nextStep() {
      this.step += 1;
      this.scrollToEnd();
    },
    copyRecoveryCodesToClipboard: function copyRecoveryCodesToClipboard() {
      var textarea = document.createElement('textarea');
      textarea.value = this.codesJson;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    },
    downloadRecoveryCodes: function downloadRecoveryCodes() {
      external_CoreHome_["Matomo"].helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codesJson);
    },
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    },
    print: function print() {
      window.print();
    }
  },
  computed: {
    setupAuthenticatorOnDeviceStep2: function setupAuthenticatorOnDeviceStep2() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_SetupAuthenticatorOnDeviceStep2', '<a class="setupStep2Link">', '</a>');
    },
    codesJson: function codesJson() {
      return JSON.stringify((this.codes || []).join('\n'));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=custom&index=0&blockType=todo
var SetupTwoFactorAuthvue_type_custom_index_0_blockType_todo = __webpack_require__("e62f");
var SetupTwoFactorAuthvue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(SetupTwoFactorAuthvue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue



SetupTwoFactorAuthvue_type_script_lang_ts.render = render
/* custom blocks */

if (typeof SetupTwoFactorAuthvue_type_custom_index_0_blockType_todo_default.a === 'function') SetupTwoFactorAuthvue_type_custom_index_0_blockType_todo_default()(SetupTwoFactorAuthvue_type_script_lang_ts)


/* harmony default export */ var SetupTwoFactorAuth = (SetupTwoFactorAuthvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=TwoFactorAuth.umd.js.map