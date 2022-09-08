(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", "CorePluginsAdmin", ], factory);
	else if(typeof exports === 'object')
		exports["TwoFactorAuth"] = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else
		root["TwoFactorAuth"] = factory(root["CoreHome"], root["CorePluginsAdmin"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"setupTwoFactorAuthentication\",\n  ref: \"root\"\n};\nvar _hoisted_2 = {\n  key: 0,\n  class: \"alert alert-warning\"\n};\nvar _hoisted_3 = [\"disabled\"];\n\nvar _hoisted_4 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  name: \"twoFactorStep2\",\n  id: \"twoFactorStep2\",\n  style: {\n    \"opacity\": \"0\"\n  }\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_5 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"https://github.com/andOTP/andOTP#downloads\"\n}, \"andOTP\", -1\n/* HOISTED */\n);\n\nvar _hoisted_6 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\", \");\n\nvar _hoisted_7 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"https://authy.com/guides/github/\"\n}, \"Authy\", -1\n/* HOISTED */\n);\n\nvar _hoisted_8 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\", \");\n\nvar _hoisted_9 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"https://support.1password.com/one-time-passwords/\"\n}, \"1Password\", -1\n/* HOISTED */\n);\n\nvar _hoisted_10 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\", \");\n\nvar _hoisted_11 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/\"\n}, \"LastPass Authenticator\", -1\n/* HOISTED */\n);\n\nvar _hoisted_12 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"https://support.google.com/accounts/answer/1066447\"\n}, \"Google Authenticator\", -1\n/* HOISTED */\n);\n\nvar _hoisted_13 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\". \");\n\nvar _hoisted_14 = [\"innerHTML\"];\n\nvar _hoisted_15 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_16 = {\n  id: \"qrcode\",\n  ref: \"qrcode\",\n  title: \"\"\n};\n\nvar _hoisted_17 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_18 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  name: \"twoFactorStep3\",\n  id: \"twoFactorStep3\",\n  style: {\n    \"opacity\": \"0\"\n  }\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_19 = {\n  key: 0,\n  class: \"message_container\"\n};\n\nvar _hoisted_20 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\": \");\n\nvar _hoisted_21 = [\"innerHTML\"];\n\nvar _hoisted_22 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_23 = [\"action\"];\nvar _hoisted_24 = [\"value\"];\nvar _hoisted_25 = [\"disabled\", \"value\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _this = this;\n\n  var _component_ShowRecoveryCodes = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ShowRecoveryCodes\");\n\n  var _component_Notification = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Notification\");\n\n  var _component_Field = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Field\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [_ctx.isAlreadyUsing2fa ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_2, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_WarningChangingConfiguredDevice')), 1\n  /* TEXT */\n  )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_SetupIntroFollowSteps')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_StepX', 1)) + \" - \" + Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_RecoveryCodes')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ShowRecoveryCodes, {\n    codes: _ctx.codes,\n    onDownloaded: _cache[0] || (_cache[0] = function ($event) {\n      return _this.hasDownloadedRecoveryCode = true;\n    })\n  }, null, 8\n  /* PROPS */\n  , [\"codes\"]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", {\n    class: \"alert alert-info backupRecoveryCodesAlert\"\n  }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_SetupBackupRecoveryCodes')), 513\n  /* TEXT, NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.step === 1]]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"button\", {\n    class: \"btn goToStep2\",\n    onClick: _cache[1] || (_cache[1] = function ($event) {\n      return _ctx.nextStep();\n    }),\n    disabled: !_ctx.hasDownloadedRecoveryCode\n  }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Next')), 9\n  /* TEXT, PROPS */\n  , _hoisted_3), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.step === 1]])]), _hoisted_4, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_StepX', 2)) + \" - \" + Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDevice')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1')) + \" \", 1\n  /* TEXT */\n  ), _hoisted_5, _hoisted_6, _hoisted_7, _hoisted_8, _hoisted_9, _hoisted_10, _hoisted_11, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\", \" + Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Or')) + \" \", 1\n  /* TEXT */\n  ), _hoisted_12, _hoisted_13]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n    innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2)\n  }, null, 8\n  /* PROPS */\n  , _hoisted_14)]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [_hoisted_15, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", _hoisted_16, null, 512\n  /* NEED_PATCH */\n  )]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [_hoisted_17, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"button\", {\n    class: \"btn goToStep3\",\n    onClick: _cache[2] || (_cache[2] = function ($event) {\n      return _ctx.nextStep();\n    })\n  }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Next')), 513\n  /* TEXT, NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.step === 2]])])], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.step >= 2]]), _hoisted_18, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_StepX', 3)) + \" - \" + Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_ConfirmSetup')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_VerifyAuthCodeIntro')), 1\n  /* TEXT */\n  ), _ctx.accessErrorString ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_19, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Notification, {\n    noclear: true,\n    context: \"error\"\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"strong\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Error')), 1\n      /* TEXT */\n      ), _hoisted_20, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n        innerHTML: _ctx.$sanitize(_ctx.accessErrorString)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_21), _hoisted_22];\n    }),\n    _: 1\n    /* STABLE */\n\n  })])])) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"form\", {\n    method: \"post\",\n    class: \"setupConfirmAuthCodeForm\",\n    autocorrect: \"off\",\n    autocapitalize: \"none\",\n    autocomplete: \"off\",\n    action: _ctx.linkTo({\n      'module': 'TwoFactorAuth',\n      'action': _ctx.submitAction\n    })\n  }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"authCode\",\n    title: _ctx.translate('TwoFactorAuth_AuthenticationCode'),\n    modelValue: _ctx.authCode,\n    \"onUpdate:modelValue\": _cache[3] || (_cache[3] = function ($event) {\n      return _ctx.authCode = $event;\n    }),\n    maxlength: 6,\n    placeholder: '123456',\n    \"inline-help\": _ctx.translate('TwoFactorAuth_VerifyAuthCodeHelp')\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"hidden\",\n    name: \"authCodeNonce\",\n    value: _ctx.authCodeNonce\n  }, null, 8\n  /* PROPS */\n  , _hoisted_24), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"submit\",\n    class: \"btn confirmAuthCode\",\n    disabled: _ctx.authCode.length !== 6,\n    value: _ctx.translate('General_Confirm')\n  }, null, 8\n  /* PROPS */\n  , _hoisted_25)], 8\n  /* PROPS */\n  , _hoisted_23)], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.step >= 3]])], 512\n  /* NEED_PATCH */\n  );\n}\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\n\nvar _hoisted_1 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_2 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_3 = {\n  class: \"alert alert-warning\"\n};\nvar _hoisted_4 = {\n  key: 0,\n  class: \"twoFactorRecoveryCodes browser-default\"\n};\nvar _hoisted_5 = {\n  key: 1,\n  class: \"alert alert-danger\"\n};\n\nvar _hoisted_6 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_7 = [\"value\"];\nvar _hoisted_8 = [\"value\"];\nvar _hoisted_9 = [\"value\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _ctx$codes;\n\n  var _directive_select_on_focus = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"select-on-focus\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_RecoveryCodesExplanation')), 1\n  /* TEXT */\n  ), _hoisted_1, _hoisted_2]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_3, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_RecoveryCodesSecurity')), 1\n  /* TEXT */\n  ), (_ctx$codes = _ctx.codes) !== null && _ctx$codes !== void 0 && _ctx$codes.length ? Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])((Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"ul\", _hoisted_4, [(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.codes, function (code, index) {\n    return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"li\", {\n      key: index\n    }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(code.toUpperCase().match(/.{1,4}/g).join('-')), 1\n    /* TEXT */\n    );\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))], 512\n  /* NEED_PATCH */\n  )), [[_directive_select_on_focus, {}]]) : (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_5, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('TwoFactorAuth_RecoveryCodesAllUsed')), 1\n  /* TEXT */\n  )), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [_hoisted_6, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"button\",\n    class: \"btn backupRecoveryCode\",\n    onClick: _cache[0] || (_cache[0] = function ($event) {\n      _ctx.downloadRecoveryCodes();\n\n      _ctx.$emit('downloaded');\n    }),\n    value: _ctx.translate('General_Download'),\n    style: {\n      \"margin-right\": \"3.5px\"\n    }\n  }, null, 8\n  /* PROPS */\n  , _hoisted_7), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"button\",\n    class: \"btn backupRecoveryCode\",\n    onClick: _cache[1] || (_cache[1] = function ($event) {\n      _ctx.print();\n\n      _ctx.$emit('downloaded');\n    }),\n    value: _ctx.translate('General_Print'),\n    style: {\n      \"margin-right\": \"3.5px\"\n    }\n  }, null, 8\n  /* PROPS */\n  , _hoisted_8), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"button\",\n    class: \"btn backupRecoveryCode\",\n    onClick: _cache[2] || (_cache[2] = function ($event) {\n      _ctx.copyRecoveryCodesToClipboard();\n\n      _ctx.$emit('downloaded');\n    }),\n    value: _ctx.translate('General_Copy')\n  }, null, 8\n  /* PROPS */\n  , _hoisted_9)])]);\n}\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! CorePluginsAdmin */ \"CorePluginsAdmin\");\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var _types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../types */ \"./plugins/TwoFactorAuth/vue/src/types.ts\");\n/* harmony import */ var _ShowRecoveryCodes_ShowRecoveryCodes_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../ShowRecoveryCodes/ShowRecoveryCodes.vue */ \"./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue\");\n\n\n\n\n\nvar _window = window,\n    QRCode = _window.QRCode,\n    $ = _window.$;\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    isAlreadyUsing2fa: Boolean,\n    accessErrorString: String,\n    submitAction: {\n      type: String,\n      required: true\n    },\n    authCodeNonce: {\n      type: String,\n      required: true\n    },\n    codes: Array\n  },\n  components: {\n    ShowRecoveryCodes: _ShowRecoveryCodes_ShowRecoveryCodes_vue__WEBPACK_IMPORTED_MODULE_4__[\"default\"],\n    Notification: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Notification\"],\n    Field: CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__[\"Field\"]\n  },\n  directives: {\n    SelectOnFocus: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"SelectOnFocus\"]\n  },\n  data: function data() {\n    return {\n      step: 1,\n      hasDownloadedRecoveryCode: false,\n      authCode: ''\n    };\n  },\n  mounted: function mounted() {\n    var _this = this;\n\n    setTimeout(function () {\n      var qrcode = _this.$refs.qrcode; // eslint-disable-next-line no-new\n\n      new QRCode(qrcode, {\n        text: window.twoFaBarCodeSetupUrl\n      });\n      $(qrcode).attr('title', ''); // do not show secret on hover\n\n      if (_this.accessErrorString) {\n        // user entered something wrong\n        _this.step = 3;\n\n        _this.scrollToEnd();\n      }\n\n      $(_this.$refs.root).on('click', '.setupStep2Link', function (e) {\n        e.preventDefault();\n        CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].helper.modalConfirm('#setupTwoFAsecretConfirm');\n      });\n    });\n  },\n  methods: {\n    scrollToEnd: function scrollToEnd() {\n      var _this2 = this;\n\n      setTimeout(function () {\n        var id = '';\n\n        if (_this2.step === 2) {\n          id = '#twoFactorStep2';\n        } else if (_this2.step === 3) {\n          id = '#twoFactorStep3';\n        }\n\n        if (id) {\n          CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].helper.lazyScrollTo(id, 50, true);\n        }\n      }, 50);\n    },\n    nextStep: function nextStep() {\n      this.step += 1;\n      this.scrollToEnd();\n    },\n    linkTo: function linkTo(params) {\n      return \"?\".concat(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].stringify(Object.assign(Object.assign({}, CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].urlParsed.value), params)));\n    }\n  },\n  computed: {\n    setupAuthenticatorOnDeviceStep2: function setupAuthenticatorOnDeviceStep2() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('TwoFactorAuth_SetupAuthenticatorOnDeviceStep2', '<a class=\"setupStep2Link\">', '</a>');\n    }\n  }\n}));\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    codes: {\n      type: Array,\n      default: function _default() {\n        return [];\n      }\n    }\n  },\n  directives: {\n    SelectOnFocus: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"SelectOnFocus\"]\n  },\n  emits: ['downloaded'],\n  methods: {\n    copyRecoveryCodesToClipboard: function copyRecoveryCodesToClipboard() {\n      var textarea = document.createElement('textarea');\n      textarea.value = this.codes.join('\\n');\n      textarea.setAttribute('readonly', '');\n      textarea.style.position = 'absolute';\n      textarea.style.left = '-9999px';\n      document.body.appendChild(textarea);\n      textarea.select();\n      document.execCommand('copy');\n      document.body.removeChild(textarea);\n    },\n    downloadRecoveryCodes: function downloadRecoveryCodes() {\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codes.join('\\n'));\n    },\n    print: function print() {\n      window.print();\n    }\n  }\n}));\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: ShowRecoveryCodes, SetupTwoFactorAuth */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/TwoFactorAuth/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ShowRecoveryCodes\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"ShowRecoveryCodes\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"SetupTwoFactorAuth\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"SetupTwoFactorAuth\"]; });\n\n\n\n\n\n//# sourceURL=webpack://TwoFactorAuth/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://TwoFactorAuth/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue":
/*!*********************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue ***!
  \*********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _SetupTwoFactorAuth_vue_vue_type_template_id_4e852542__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SetupTwoFactorAuth.vue?vue&type=template&id=4e852542 */ \"./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542\");\n/* harmony import */ var _SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SetupTwoFactorAuth.vue?vue&type=script&lang=ts */ \"./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _SetupTwoFactorAuth_vue_vue_type_template_id_4e852542__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts":
/*!*********************************************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts ***!
  \*********************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./SetupTwoFactorAuth.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_SetupTwoFactorAuth_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542":
/*!***************************************************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542 ***!
  \***************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_SetupTwoFactorAuth_vue_vue_type_template_id_4e852542__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./SetupTwoFactorAuth.vue?vue&type=template&id=4e852542 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?vue&type=template&id=4e852542\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_SetupTwoFactorAuth_vue_vue_type_template_id_4e852542__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue":
/*!*******************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue ***!
  \*******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ShowRecoveryCodes_vue_vue_type_template_id_49c317ec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ShowRecoveryCodes.vue?vue&type=template&id=49c317ec */ \"./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec\");\n/* harmony import */ var _ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ShowRecoveryCodes.vue?vue&type=script&lang=ts */ \"./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _ShowRecoveryCodes_vue_vue_type_template_id_49c317ec__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts":
/*!*******************************************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts ***!
  \*******************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ShowRecoveryCodes.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ShowRecoveryCodes_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec":
/*!*************************************************************************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec ***!
  \*************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ShowRecoveryCodes_vue_vue_type_template_id_49c317ec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ShowRecoveryCodes.vue?vue&type=template&id=49c317ec */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?vue&type=template&id=49c317ec\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ShowRecoveryCodes_vue_vue_type_template_id_49c317ec__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/index.ts":
/*!************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/index.ts ***!
  \************************************************/
/*! exports provided: ShowRecoveryCodes, SetupTwoFactorAuth */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ShowRecoveryCodes_ShowRecoveryCodes_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ShowRecoveryCodes/ShowRecoveryCodes.vue */ \"./plugins/TwoFactorAuth/vue/src/ShowRecoveryCodes/ShowRecoveryCodes.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ShowRecoveryCodes\", function() { return _ShowRecoveryCodes_ShowRecoveryCodes_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* harmony import */ var _SetupTwoFactorAuth_SetupTwoFactorAuth_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SetupTwoFactorAuth/SetupTwoFactorAuth.vue */ \"./plugins/TwoFactorAuth/vue/src/SetupTwoFactorAuth/SetupTwoFactorAuth.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"SetupTwoFactorAuth\", function() { return _SetupTwoFactorAuth_SetupTwoFactorAuth_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/index.ts?");

/***/ }),

/***/ "./plugins/TwoFactorAuth/vue/src/types.ts":
/*!************************************************!*\
  !*** ./plugins/TwoFactorAuth/vue/src/types.ts ***!
  \************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n//# sourceURL=webpack://TwoFactorAuth/./plugins/TwoFactorAuth/vue/src/types.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://TwoFactorAuth/external_%22CoreHome%22?");

/***/ }),

/***/ "CorePluginsAdmin":
/*!***********************************!*\
  !*** external "CorePluginsAdmin" ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__;\n\n//# sourceURL=webpack://TwoFactorAuth/external_%22CorePluginsAdmin%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://TwoFactorAuth/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});