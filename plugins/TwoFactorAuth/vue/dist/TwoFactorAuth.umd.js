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

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ShowRecoveryCodes", function() { return /* reexport */ ShowRecoveryCodes; });
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=e6e67cfe


var _hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_3 = {
  class: "alert alert-warning"
};
var _hoisted_4 = {
  key: 0,
  class: "twoFactorRecoveryCodes browser-default"
};
var _hoisted_5 = {
  key: 1,
  class: "alert alert-danger"
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = ["value"];
var _hoisted_8 = ["value"];
var _hoisted_9 = ["value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$codes;

  var _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesExplanation')), 1), _hoisted_1, _hoisted_2]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesSecurity')), 1), (_ctx$codes = _ctx.codes) !== null && _ctx$codes !== void 0 && _ctx$codes.length ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", _hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.codes, function (code, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: index
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(code.toUpperCase().match(/.{1,4}/g).join('-')), 1);
  }), 128))], 512)), [[_directive_select_on_focus, {}]]) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodesAllUsed')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn backupRecoveryCode",
    onClick: _cache[0] || (_cache[0] = function ($event) {
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
    onClick: _cache[1] || (_cache[1] = function ($event) {
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
    onClick: _cache[2] || (_cache[2] = function ($event) {
      _ctx.copyRecoveryCodesToClipboard();

      _ctx.$emit('downloaded');
    }),
    value: _ctx.translate('General_Copy')
  }, null, 8, _hoisted_9)])]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=e6e67cfe

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts


/* harmony default export */ var ShowRecoveryCodesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    codes: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  emits: ['downloaded'],
  methods: {
    copyRecoveryCodesToClipboard: function copyRecoveryCodesToClipboard() {
      var textarea = document.createElement('textarea');
      textarea.value = this.codes.join('\n');
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    },
    downloadRecoveryCodes: function downloadRecoveryCodes() {
      external_CoreHome_["Matomo"].helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codes.join('\n'));
    },
    print: function print() {
      window.print();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue



ShowRecoveryCodesvue_type_script_lang_ts.render = render

/* harmony default export */ var ShowRecoveryCodes = (ShowRecoveryCodesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=ba50e374

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_1 = {
  class: "setupTwoFactorAuthentication",
  ref: "root"
};
var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_2 = {
  key: 0,
  class: "alert alert-warning"
};
var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_3 = ["disabled"];

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep2",
  id: "twoFactorStep2",
  style: {
    "opacity": "0"
  }
}, null, -1);

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://github.com/andOTP/andOTP#downloads"
}, "andOTP", -1);

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://authy.com/guides/github/"
}, "Authy", -1);

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.1password.com/one-time-passwords/"
}, "1Password", -1);

var _hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
}, "LastPass Authenticator", -1);

var _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.google.com/accounts/answer/1066447"
}, "Google Authenticator", -1);

var _hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ");

var _hoisted_14 = ["innerHTML"];

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_16 = {
  id: "qrcode",
  ref: "qrcode",
  title: ""
};

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep3",
  id: "twoFactorStep3",
  style: {
    "opacity": "0"
  }
}, null, -1);

var _hoisted_19 = {
  key: 0,
  class: "message_container"
};

var _hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": ");

var _hoisted_21 = ["innerHTML"];

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_23 = ["action"];
var _hoisted_24 = ["value"];
var _hoisted_25 = ["disabled", "value"];
function SetupTwoFactorAuthvue_type_template_id_ba50e374_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_ShowRecoveryCodes = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShowRecoveryCodes");

  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_1, [_ctx.isAlreadyUsing2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_WarningChangingConfiguredDevice')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupIntroFollowSteps')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 1)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ShowRecoveryCodes, {
    codes: _ctx.codes,
    onDownloaded: _cache[0] || (_cache[0] = function ($event) {
      return _this.hasDownloadedRecoveryCode = true;
    })
  }, null, 8, ["codes"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "alert alert-info backupRecoveryCodesAlert"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupBackupRecoveryCodes')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn goToStep2",
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.nextStep();
    }),
    disabled: !_ctx.hasDownloadedRecoveryCode
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 9, SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]])]), SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 2)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDevice')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1')) + " ", 1), SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_5, SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_6, SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_7, SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_8, SetupTwoFactorAuthvue_type_template_id_ba50e374_hoisted_9, _hoisted_10, _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Or')) + " ", 1), _hoisted_12, _hoisted_13]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2)
  }, null, 8, _hoisted_14)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_16, null, 512)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn goToStep3",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.nextStep();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 2]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 2]]), _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 3)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfirmSetup')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyAuthCodeIntro')), 1), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    noclear: true,
    context: "error"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
      }, null, 8, _hoisted_21), _hoisted_22];
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
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.authCode = $event;
    }),
    maxlength: 6,
    placeholder: '123456',
    "inline-help": _ctx.translate('TwoFactorAuth_VerifyAuthCodeHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "hidden",
    name: "authCodeNonce",
    value: _ctx.authCodeNonce
  }, null, 8, _hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "submit",
    class: "btn confirmAuthCode",
    disabled: _ctx.authCode.length !== 6,
    value: _ctx.translate('General_Confirm')
  }, null, 8, _hoisted_25)], 8, _hoisted_23)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 3]])], 512);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=ba50e374

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
    ShowRecoveryCodes: ShowRecoveryCodes,
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

      $(_this.$refs.root).on('click', '.setupStep2Link', function (e) {
        e.preventDefault();
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
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    }
  },
  computed: {
    setupAuthenticatorOnDeviceStep2: function setupAuthenticatorOnDeviceStep2() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_SetupAuthenticatorOnDeviceStep2', '<a class="setupStep2Link">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue



SetupTwoFactorAuthvue_type_script_lang_ts.render = SetupTwoFactorAuthvue_type_template_id_ba50e374_render

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