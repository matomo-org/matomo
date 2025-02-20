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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=43bd6978


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
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=43bd6978

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=template&id=13e8e57f


var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_3 = {
  key: 0,
  class: "alert alert-success"
};
var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_4 = {
  key: 1,
  class: "alert alert-danger"
};
var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_5 = ["action"];
var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_6 = ["value"];
var ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_7 = ["value"];
function ShowRecoveryCodesPagevue_type_template_id_13e8e57f_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ShowRecoveryCodes = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShowRecoveryCodes");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitle
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ShowRecoveryCodes, {
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
      }, null, 8, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_7)], 8, ShowRecoveryCodesPagevue_type_template_id_13e8e57f_hoisted_5)];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=template&id=13e8e57f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=script&lang=ts



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
    contentTitle: function contentTitle() {
      var part1 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthentication');
      var part2 = Object(external_CoreHome_["translate"])('TwoFactorAuth_RecoveryCodes');
      return "".concat(part1, " - ").concat(part2);
    },
    showRecoveryCodesLink: function showRecoveryCodesLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes'
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodesPage.vue



ShowRecoveryCodesPagevue_type_script_lang_ts.render = ShowRecoveryCodesPagevue_type_template_id_13e8e57f_render

/* harmony default export */ var ShowRecoveryCodesPage = (ShowRecoveryCodesPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=8d641b1e

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_1 = {
  class: "setupTwoFactorAuthentication",
  ref: "root"
};
var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_2 = {
  key: 0,
  class: "alert alert-warning"
};
var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_3 = ["disabled"];

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep2",
  id: "twoFactorStep2",
  style: {
    "opacity": "0"
  }
}, null, -1);

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_5 = ["innerHTML"];

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "twoFactorStep3",
  id: "twoFactorStep3",
  style: {
    "opacity": "0"
  }
}, null, -1);

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_8 = {
  key: 0,
  class: "message_container"
};

var SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": ");

var _hoisted_10 = ["innerHTML"];

var _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_12 = ["action"];
var _hoisted_13 = ["value"];
var _hoisted_14 = ["disabled", "value"];
var _hoisted_15 = {
  class: "ui-confirm two-fa-qr-code-dialog"
};
var _hoisted_16 = {
  class: "row"
};
var _hoisted_17 = {
  class: "col l8 offset-l2 m10 offset-m1 s12 center-align"
};
var _hoisted_18 = {
  id: "qrcode",
  ref: "qrcode",
  title: ""
};
var _hoisted_19 = {
  class: "text-code"
};
var _hoisted_20 = ["innerHTML"];
var _hoisted_21 = {
  class: "row"
};
var _hoisted_22 = {
  class: "col l8 offset-l2 m10 offset-m1 s12"
};
var _hoisted_23 = ["value"];
var _hoisted_24 = ["value"];
function SetupTwoFactorAuthvue_type_template_id_8d641b1e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_ShowRecoveryCodes = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ShowRecoveryCodes");

  var _component_InstallOTPApp = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("InstallOTPApp");

  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.standalone ? _ctx.translate('TwoFactorAuth_RequiredToSetUpTwoFactorAuthentication') : _ctx.translate('TwoFactorAuth_SetUpTwoFactorAuthentication')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_1, [_ctx.isAlreadyUsing2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_WarningChangingConfiguredDevice')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupIntroFollowSteps')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 1)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_RecoveryCodes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ShowRecoveryCodes, {
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
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')), 9, SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step === 1]])]), SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 2)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDevice')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallOTPApp), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2ShowCodes)
      }, null, 8, SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        class: "btn showOtpCodes",
        onClick: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.showQrCodeModal();
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ShowCodes')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 2]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 2]]), SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_StepX', 3)) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ConfirmSetup')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyAuthCodeIntro')), 1), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
        noclear: true,
        context: "error"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), SetupTwoFactorAuthvue_type_template_id_8d641b1e_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
          }, null, 8, _hoisted_10), _hoisted_11];
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
      }, null, 8, _hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "submit",
        class: "btn confirmAuthCode",
        disabled: _ctx.authCode.length !== 6,
        value: _ctx.translate('General_Confirm')
      }, null, 8, _hoisted_14)], 8, _hoisted_12)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.step >= 3]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
        modelValue: _ctx.qrCodeDialogVisible,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          return _ctx.qrCodeDialogVisible = $event;
        }),
        onValidation: _cache[5] || (_cache[5] = function ($event) {
          _ctx.closeQrCodeModal();

          _ctx.nextStep();
        }),
        options: {
          focusSelector: '.modal-action.btn'
        }
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_Your2FaAuthSecret')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ShowCodeModalInstructions1')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_18, null, 512)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_ShowCodeModalInstructions2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.newSecret), 1)], 512), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
            innerHTML: _ctx.$sanitize(_ctx.showCodeModalInstructions3)
          }, null, 8, _hoisted_20)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_DontHaveOTPApp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_InstallOTPApp)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
            role: "validation",
            type: "button",
            value: _ctx.translate('General_Continue')
          }, null, 8, _hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
            role: "no",
            type: "button",
            value: _ctx.translate('General_Cancel')
          }, null, 8, _hoisted_24)])];
        }),
        _: 1
      }, 8, ["modelValue", "options"])], 512)];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=8d641b1e

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/InstallOTPApp.vue?vue&type=template&id=a1258308


var InstallOTPAppvue_type_template_id_a1258308_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://github.com/andOTP/andOTP#downloads"
}, "andOTP", -1);

var InstallOTPAppvue_type_template_id_a1258308_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var InstallOTPAppvue_type_template_id_a1258308_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://authy.com/guides/github/"
}, "Authy", -1);

var InstallOTPAppvue_type_template_id_a1258308_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var InstallOTPAppvue_type_template_id_a1258308_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.1password.com/one-time-passwords/"
}, "1Password", -1);

var InstallOTPAppvue_type_template_id_a1258308_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", ");

var InstallOTPAppvue_type_template_id_a1258308_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
}, "LastPass Authenticator", -1);

var InstallOTPAppvue_type_template_id_a1258308_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  target: "_blank",
  rel: "noreferrer noopener",
  href: "https://support.google.com/accounts/answer/1066447"
}, "Google Authenticator", -1);

var InstallOTPAppvue_type_template_id_a1258308_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ");

function InstallOTPAppvue_type_template_id_a1258308_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1')) + " ", 1), InstallOTPAppvue_type_template_id_a1258308_hoisted_1, InstallOTPAppvue_type_template_id_a1258308_hoisted_2, InstallOTPAppvue_type_template_id_a1258308_hoisted_3, InstallOTPAppvue_type_template_id_a1258308_hoisted_4, InstallOTPAppvue_type_template_id_a1258308_hoisted_5, InstallOTPAppvue_type_template_id_a1258308_hoisted_6, InstallOTPAppvue_type_template_id_a1258308_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Or')) + " ", 1), InstallOTPAppvue_type_template_id_a1258308_hoisted_8, InstallOTPAppvue_type_template_id_a1258308_hoisted_9]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/InstallOTPApp.vue?vue&type=template&id=a1258308

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/InstallOTPApp.vue?vue&type=script&lang=ts

/* harmony default export */ var InstallOTPAppvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/InstallOTPApp.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/InstallOTPApp.vue



InstallOTPAppvue_type_script_lang_ts.render = InstallOTPAppvue_type_template_id_a1258308_render

/* harmony default export */ var InstallOTPApp = (InstallOTPAppvue_type_script_lang_ts);
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
    InstallOTPApp: InstallOTPApp,
    MatomoDialog: external_CoreHome_["MatomoDialog"],
    ShowRecoveryCodes: ShowRecoveryCodes,
    Notification: external_CoreHome_["Notification"],
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      step: 1,
      hasDownloadedRecoveryCode: false,
      authCode: '',
      qrCodeDialogVisible: false
    };
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      var qrcode = _this.$refs.qrcode; // eslint-disable-next-line no-new

      new QRCode(qrcode, {
        text: _this.twoFaBarCodeSetupUrl,
        width: 200,
        height: 200
      });
      $(qrcode).attr('title', ''); // do not show secret on hover

      if (_this.accessErrorString) {
        // user entered something wrong
        _this.step = 3;

        _this.scrollToEnd();
      }
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
    showQrCodeModal: function showQrCodeModal() {
      this.qrCodeDialogVisible = true;
    },
    closeQrCodeModal: function closeQrCodeModal() {
      this.qrCodeDialogVisible = false;
    },
    nextStep: function nextStep() {
      this.step += 1;

      if (this.step > 3) {
        this.step = 3;
      }

      this.scrollToEnd();
    },
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    }
  },
  computed: {
    setupAuthenticatorOnDeviceStep2ShowCodes: function setupAuthenticatorOnDeviceStep2ShowCodes() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_SetupAuthenticatorOnDeviceStep2ShowCodes', Object(external_CoreHome_["translate"])('TwoFactorAuth_ShowCodes'));
    },
    showCodeModalInstructions3: function showCodeModalInstructions3() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_ShowCodeModalInstructions3', Object(external_CoreHome_["translate"])('General_Continue'));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue



SetupTwoFactorAuthvue_type_script_lang_ts.render = SetupTwoFactorAuthvue_type_template_id_8d641b1e_render

/* harmony default export */ var SetupTwoFactorAuth = (SetupTwoFactorAuthvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=template&id=6b8b0945

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_1 = {
  class: "message_container"
};

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(": ");

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_3 = ["innerHTML"];

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_5 = {
  class: "row"
};
var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_6 = {
  class: "col s12 input-field"
};
var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_7 = ["value"];

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
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

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_9 = {
  for: "login_form_authcode"
};

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-user icon"
}, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_11 = {
  class: "row actions"
};
var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_12 = {
  class: "col s12"
};
var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_13 = ["value"];
var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_14 = ["innerHTML"];

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_18 = ["href"];

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_20 = ["href"];

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_22 = ["href"];
function LoginTwoFactorAuthvue_type_template_id_6b8b0945_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormErrors = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormErrors");

  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('TwoFactorAuth_TwoFactorAuthentication')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FormErrors, {
        "form-errors": _ctx.formData.errors
      }, null, 8, ["form-errors"]), _ctx.accessErrorString ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
        key: 0,
        noclear: true,
        context: "error"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Error')), 1), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
          }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_3), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_4];
        }),
        _: 1
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(_ctx.formDataAttributes, {
        class: "loginTwoFaForm"
      }), [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "hidden",
        name: "form_nonce",
        id: "login_form_nonce",
        value: _ctx.formNonce
      }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_7), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_9, [LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_AuthenticationCode')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        class: "submit btn",
        id: "login_form_submit",
        type: "submit",
        value: _ctx.translate('TwoFactorAuth_Verify'),
        tabindex: "100"
      }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_13)])])], 16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_VerifyIdentifyExplanation')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
      }, null, 8, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_14), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_15, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_DontHaveYourMobileDevice')), 1), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.externalRawLink('https://matomo.org/faq/how-to/faq_27248'),
        rel: "noreferrer noopener",
        target: "_blank"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_EnterRecoveryCodeInstead')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_18), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.mailToLink,
        rel: "noreferrer noopener"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_AskSuperUserResetAuthenticationCode')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_20), LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.logoutLink,
        rel: "noreferrer noopener"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Logout')), 9, LoginTwoFactorAuthvue_type_template_id_6b8b0945_hoisted_22)])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=template&id=6b8b0945

// EXTERNAL MODULE: external "Login"
var external_Login_ = __webpack_require__("5b81");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=script&lang=ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }




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
    learnMoreText: function learnMoreText() {
      return Object(external_CoreHome_["translate"])('General_LearnMore', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_27245'), '</a>');
    },
    mailToLink: function mailToLink() {
      return "mailto:".concat(this.contactEmail, "?").concat(external_CoreHome_["MatomoUrl"].stringify({
        subject: Object(external_CoreHome_["translate"])('TwoFactorAuth_NotPossibleToLogIn'),
        body: Object(external_CoreHome_["translate"])('TwoFactorAuth_LostAuthenticationDevice', '\n\n', '\n\n', this.piwikUrl || '', '\n\n', this.userLogin, Object(external_CoreHome_["externalRawLink"])('https://matomo.org/faq/how-to/faq_27248'))
      }));
    },
    logoutLink: function logoutLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: this.loginModule,
        action: 'logout'
      }));
    },
    formDataAttributes: function formDataAttributes() {
      // convert html attribute string (ie 'a="b" d="f"') to JS object {a: "b", d: "f"}
      return Object.fromEntries(this.formData.attributes.split(/\s+/g).filter(function (s) {
        return s;
      }).map(function (pair) {
        return pair.split('=');
      }).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
            name = _ref2[0],
            value = _ref2[1];

        return [name, external_CoreHome_["Matomo"].helper.htmlDecode(value.substr(1, value.length - 2))];
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/LoginTwoFactorAuth/LoginTwoFactorAuth.vue



LoginTwoFactorAuthvue_type_script_lang_ts.render = LoginTwoFactorAuthvue_type_template_id_6b8b0945_render

/* harmony default export */ var LoginTwoFactorAuth = (LoginTwoFactorAuthvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=template&id=631d8678

var SetupFinishedvue_type_template_id_631d8678_hoisted_1 = {
  class: "successMessage"
};

var SetupFinishedvue_type_template_id_631d8678_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SetupFinishedvue_type_template_id_631d8678_hoisted_3 = ["href"];
function SetupFinishedvue_type_template_id_631d8678_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "twoFactorSetupFinished"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", SetupFinishedvue_type_template_id_631d8678_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupFinishedTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('TwoFactorAuth_SetupFinishedSubtitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [SetupFinishedvue_type_template_id_631d8678_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        class: "btn",
        href: _ctx.userSecurityLink
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Continue')), 9, SetupFinishedvue_type_template_id_631d8678_hoisted_3)])];
    }),
    _: 1
  });
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=template&id=631d8678

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=script&lang=ts


/* harmony default export */ var SetupFinishedvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  computed: {
    userSecurityLink: function userSecurityLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'userSecurity'
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/SetupFinished/SetupFinished.vue



SetupFinishedvue_type_script_lang_ts.render = SetupFinishedvue_type_template_id_631d8678_render

/* harmony default export */ var SetupFinished = (SetupFinishedvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=790c613d

var UserSettingsvue_type_template_id_790c613d_hoisted_1 = ["innerHTML"];
var UserSettingsvue_type_template_id_790c613d_hoisted_2 = {
  key: 0
};
var UserSettingsvue_type_template_id_790c613d_hoisted_3 = {
  class: "twoFaStatusEnabled"
};
var UserSettingsvue_type_template_id_790c613d_hoisted_4 = {
  key: 1
};
var UserSettingsvue_type_template_id_790c613d_hoisted_5 = {
  key: 0
};

var UserSettingsvue_type_template_id_790c613d_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var UserSettingsvue_type_template_id_790c613d_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var UserSettingsvue_type_template_id_790c613d_hoisted_8 = ["href"];
var UserSettingsvue_type_template_id_790c613d_hoisted_9 = {
  key: 1
};
var UserSettingsvue_type_template_id_790c613d_hoisted_10 = ["href"];
var UserSettingsvue_type_template_id_790c613d_hoisted_11 = ["href"];
var UserSettingsvue_type_template_id_790c613d_hoisted_12 = ["value"];
var UserSettingsvue_type_template_id_790c613d_hoisted_13 = ["href"];
var UserSettingsvue_type_template_id_790c613d_hoisted_14 = {
  key: 2
};

var UserSettingsvue_type_template_id_790c613d_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var UserSettingsvue_type_template_id_790c613d_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var UserSettingsvue_type_template_id_790c613d_hoisted_17 = ["href"];
var UserSettingsvue_type_template_id_790c613d_hoisted_18 = {
  id: "confirmDisable2FA",
  class: "ui-confirm",
  ref: "confirmDisable2FA"
};
var UserSettingsvue_type_template_id_790c613d_hoisted_19 = ["value"];
var UserSettingsvue_type_template_id_790c613d_hoisted_20 = ["value"];
function UserSettingsvue_type_template_id_790c613d_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitle,
    class: "userSettings2FA"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
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
        onClick: _cache[0] || (_cache[0] = function ($event) {
          return _ctx.onDisable2FaLinkClick();
        }),
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
      }, null, 8, UserSettingsvue_type_template_id_790c613d_hoisted_20)], 512)];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=790c613d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/UserSettings/UserSettings.vue?vue&type=script&lang=ts


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
    contentTitle: function contentTitle() {
      var part1 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthentication');
      var part2 = Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFAShort');
      return "".concat(part1, " (").concat(part2, ")");
    },
    twoFactorAuthIntro: function twoFactorAuthIntro() {
      return Object(external_CoreHome_["translate"])('TwoFactorAuth_TwoFactorAuthenticationIntro', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_27245'), '</a>');
    },
    setupTwoFactorAuthLink: function setupTwoFactorAuthLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'setupTwoFactorAuth'
      })));
    },
    disableTwoFactorAuthLink: function disableTwoFactorAuthLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'disableTwoFactorAuth',
        disableNonce: this.disableNonce
      })));
    },
    showRecoveryCodesLink: function showRecoveryCodesLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes'
      })));
    }
  },
  methods: {
    onDisable2FaLinkClick: function onDisable2FaLinkClick() {
      var nonce = this.disableNonce;
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmDisable2FA, {
        yes: function yes() {
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