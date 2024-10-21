(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["MobileMessaging"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["MobileMessaging"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/MobileMessaging/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "ReportParameters", function() { return /* reexport */ ReportParameters; });
__webpack_require__.d(__webpack_exports__, "ManageSmsProvider", function() { return /* reexport */ ManageSmsProvider; });
__webpack_require__.d(__webpack_exports__, "SmsProviderCredentials", function() { return /* reexport */ SmsProviderCredentials; });
__webpack_require__.d(__webpack_exports__, "DelegateMobileMessagingSettings", function() { return /* reexport */ DelegateMobileMessagingSettings; });
__webpack_require__.d(__webpack_exports__, "ManageMobilePhoneNumbers", function() { return /* reexport */ ManageMobilePhoneNumbers; });
__webpack_require__.d(__webpack_exports__, "SelectPhoneNumbers", function() { return /* reexport */ SelectPhoneNumbers; });
__webpack_require__.d(__webpack_exports__, "AdminPage", function() { return /* reexport */ AdminPage; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=a43e7e3c

const _hoisted_1 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SelectPhoneNumbers = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SelectPhoneNumbers");
  return _ctx.report && _ctx.report.type === 'mobile' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SelectPhoneNumbers, {
    "phone-numbers": _ctx.phoneNumbers,
    "with-introduction": true,
    "model-value": _ctx.report.phoneNumbers,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('change', 'phoneNumbers', $event))
  }, null, 8, ["phone-numbers", "model-value"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=a43e7e3c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=template&id=392447a0

const SelectPhoneNumbersvue_type_template_id_392447a0_hoisted_1 = {
  class: "mobile"
};
const _hoisted_2 = {
  id: "mobilePhoneNumbersHelp",
  class: "inline-help-node"
};
const _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);
const _hoisted_4 = {
  key: 0,
  style: {
    "margin-right": "3.5px"
  }
};
const _hoisted_5 = {
  key: 1,
  style: {
    "margin-right": "3.5px"
  }
};
const _hoisted_6 = ["href"];
function SelectPhoneNumbersvue_type_template_id_392447a0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SelectPhoneNumbersvue_type_template_id_392447a0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    "var-type": "array",
    name: "phoneNumbers",
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('update:modelValue', $event)),
    introduction: _ctx.withIntroduction ? _ctx.translate('ScheduledReports_SendReportTo') : undefined,
    title: _ctx.translate('MobileMessaging_PhoneNumbers'),
    disabled: _ctx.phoneNumbers.length === 0,
    options: _ctx.phoneNumbers
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [_hoisted_3, _ctx.phoneNumbers.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_NoPhoneNumbers')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_AdditionalPhoneNumbers')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.linkTo({
        module: 'MobileMessaging',
        action: 'index',
        updated: null
      })
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_MobileMessagingSettingsLink')), 9, _hoisted_6)])]),
    _: 1
  }, 8, ["model-value", "introduction", "title", "disabled", "options"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=template&id=392447a0

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



/* harmony default export */ var SelectPhoneNumbersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    phoneNumbers: {
      type: [Array, Object],
      required: true
    },
    withIntroduction: Boolean
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  methods: {
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue



SelectPhoneNumbersvue_type_script_lang_ts.render = SelectPhoneNumbersvue_type_template_id_392447a0_render

/* harmony default export */ var SelectPhoneNumbers = (SelectPhoneNumbersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts


const REPORT_TYPE = 'mobile';
/* harmony default export */ var ReportParametersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    phoneNumbers: {
      type: [Array, Object],
      required: true
    }
  },
  components: {
    SelectPhoneNumbers: SelectPhoneNumbers
  },
  emits: ['change'],
  created() {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions
    } = window;
    if (!resetReportParametersFunctions[REPORT_TYPE]) {
      resetReportParametersFunctions[REPORT_TYPE] = report => {
        report.phoneNumbers = [];
        report.formatmobile = 'sms';
      };
    }
    if (!updateReportParametersFunctions[REPORT_TYPE]) {
      updateReportParametersFunctions[REPORT_TYPE] = report => {
        if (!(report !== null && report !== void 0 && report.parameters)) {
          return;
        }
        if (report.parameters && report.parameters.phoneNumbers) {
          report.phoneNumbers = report.parameters.phoneNumbers;
        }
        report.formatmobile = 'sms';
      };
    }
    if (!getReportParametersFunctions[REPORT_TYPE]) {
      getReportParametersFunctions[REPORT_TYPE] = report => {
        // returning [''] when no phone numbers are selected avoids the "please provide a value
        // for 'parameters'" error message
        const phoneNumbers = report.phoneNumbers;
        return {
          phoneNumbers: phoneNumbers || ['']
        };
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue



ReportParametersvue_type_script_lang_ts.render = render

/* harmony default export */ var ReportParameters = (ReportParametersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=1a20acce

const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxErrorManageSmsProviderSettings"
}, null, -1);
const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_2 = {
  key: 0
};
const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_3 = {
  key: 0
};
const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_5 = {
  key: 1
};
const ManageSmsProvidervue_type_template_id_1a20acce_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = ["innerHTML"];
const _hoisted_8 = {
  key: 1
};
const _hoisted_9 = {
  id: "accountForm"
};
const _hoisted_10 = ["innerHTML"];
function ManageSmsProvidervue_type_template_id_1a20acce_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SmsProviderCredentials = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SmsProviderCredentials");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isDeletingAccount
  }, null, 8, ["loading"]), ManageSmsProvidervue_type_template_id_1a20acce_hoisted_1, _ctx.credentialSupplied ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageSmsProvidervue_type_template_id_1a20acce_hoisted_2, [_ctx.credentialError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSmsProvidervue_type_template_id_1a20acce_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialInvalid', _ctx.provider)), 1), ManageSmsProvidervue_type_template_id_1a20acce_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.credentialError), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSmsProvidervue_type_template_id_1a20acce_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialProvided', _ctx.provider)) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.creditLeft), 1)), ManageSmsProvidervue_type_template_id_1a20acce_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.updateOrDeleteAccountText),
    onClick: _cache[0] || (_cache[0] = $event => _ctx.onUpdateOrDeleteClick($event))
  }, null, 8, _hoisted_7)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PleaseSignUp')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "smsProviders",
    modelValue: _ctx.smsProvider,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.smsProvider = $event),
    title: _ctx.translate('MobileMessaging_Settings_SMSProvider'),
    options: _ctx.smsProviderOptions,
    value: _ctx.provider
  }, null, 8, ["modelValue", "title", "options", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SmsProviderCredentials, {
    provider: _ctx.smsProvider,
    modelValue: _ctx.credentials,
    "onUpdate:modelValue": [_cache[2] || (_cache[2] = $event => _ctx.credentials = $event), _cache[3] || (_cache[3] = $event => {
      _ctx.credentials = $event;
    })],
    "model-value": _ctx.credentials
  }, null, 8, ["provider", "modelValue", "model-value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    id: "apiAccountSubmit",
    disabled: !_ctx.isUpdateAccountPossible,
    saving: _ctx.isUpdatingAccount,
    onConfirm: _cache[4] || (_cache[4] = $event => _ctx.updateAccount())
  }, null, 8, ["disabled", "saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "providerDescription",
    innerHTML: _ctx.$sanitize(_ctx.currentProviderDescription)
  }, null, 8, _hoisted_10)])), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.credentialSupplied || _ctx.showAccountForm], [_directive_form]])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=1a20acce

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=d7bc9978
function SmsProviderCredentialsvue_type_template_id_d7bc9978_extends() { SmsProviderCredentialsvue_type_template_id_d7bc9978_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return SmsProviderCredentialsvue_type_template_id_d7bc9978_extends.apply(this, arguments); }

const SmsProviderCredentialsvue_type_template_id_d7bc9978_hoisted_1 = {
  key: 0
};
function SmsProviderCredentialsvue_type_template_id_d7bc9978_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return _ctx.fields ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SmsProviderCredentialsvue_type_template_id_d7bc9978_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.fields, field => {
    var _ctx$modelValue;
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: field.name,
      uicontrol: field.type,
      name: field.name,
      "model-value": (_ctx$modelValue = _ctx.modelValue) === null || _ctx$modelValue === void 0 ? void 0 : _ctx$modelValue[field.name],
      "onUpdate:modelValue": $event => _ctx.$emit('update:modelValue', SmsProviderCredentialsvue_type_template_id_d7bc9978_extends(SmsProviderCredentialsvue_type_template_id_d7bc9978_extends({}, _ctx.modelValue), {}, {
        [field.name]: $event
      })),
      title: _ctx.translate(field.title)
    }, null, 8, ["uicontrol", "name", "model-value", "onUpdate:modelValue", "title"]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=d7bc9978

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=script&lang=ts



const allFieldsByProvider = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({});
/* harmony default export */ var SmsProviderCredentialsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    provider: {
      type: String,
      required: true
    },
    modelValue: Object
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  watch: {
    provider() {
      // unset credentials when new provider is chosen
      this.$emit('update:modelValue', null);
      // fetch fields for provider
      this.getCredentialFields();
    }
  },
  created() {
    this.getCredentialFields();
  },
  methods: {
    getCredentialFields() {
      if (allFieldsByProvider[this.provider]) {
        this.$emit('update:modelValue', Object.fromEntries(allFieldsByProvider[this.provider].map(f => [f.name, null])));
        return;
      }
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'MobileMessaging',
        action: 'getCredentialFields',
        provider: this.provider
      }).then(fields => {
        this.$emit('update:modelValue', Object.fromEntries(fields.map(f => [f.name, null])));
        allFieldsByProvider[this.provider] = fields;
      });
    }
  },
  computed: {
    fields() {
      return allFieldsByProvider[this.provider];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue



SmsProviderCredentialsvue_type_script_lang_ts.render = SmsProviderCredentialsvue_type_template_id_d7bc9978_render

/* harmony default export */ var SmsProviderCredentials = (SmsProviderCredentialsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=script&lang=ts




/* harmony default export */ var ManageSmsProvidervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    credentialSupplied: Boolean,
    credentialError: String,
    provider: String,
    creditLeft: [Number, String],
    smsProviderOptions: {
      type: Object,
      required: true
    },
    smsProviders: {
      type: Object,
      required: true
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    SmsProviderCredentials: SmsProviderCredentials
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      isDeletingAccount: false,
      isUpdatingAccount: false,
      showAccountForm: false,
      credentials: null,
      smsProvider: this.provider
    };
  },
  methods: {
    deleteApiAccount() {
      this.isDeletingAccount = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'MobileMessaging.deleteSMSAPICredential'
      }, {
        errorElement: '#ajaxErrorManageSmsProviderSettings'
      }).then(() => {
        external_CoreHome_["Matomo"].helper.redirect();
      }).finally(() => {
        this.isDeletingAccount = false;
      });
    },
    showUpdateAccount() {
      this.showAccountForm = true;
    },
    updateAccount() {
      if (this.isUpdateAccountPossible) {
        this.isUpdatingAccount = true;
        external_CoreHome_["AjaxHelper"].post({
          method: 'MobileMessaging.setSMSAPICredential'
        }, {
          provider: this.smsProvider,
          credentials: this.credentials
        }, {
          errorElement: '#ajaxErrorManageSmsProviderSettings'
        }).then(() => {
          external_CoreHome_["Matomo"].helper.redirect();
        }).finally(() => {
          this.isUpdatingAccount = false;
        });
      }
    },
    deleteAccount() {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteAccount', {
        yes: () => {
          this.isDeletingAccount = true;
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'MobileMessaging.deleteSMSAPICredential'
          }, {
            errorElement: '#ajaxErrorManageSmsProviderSettings'
          }).then(() => {
            this.isDeletingAccount = false;
            external_CoreHome_["Matomo"].helper.redirect();
          }).finally(() => {
            this.isDeletingAccount = false;
          });
        }
      });
    },
    onUpdateOrDeleteClick(event) {
      const target = event.target;
      if (target.id === 'displayAccountForm') {
        this.showUpdateAccount();
      } else if (target.id === 'deleteAccount') {
        this.deleteAccount();
      }
    }
  },
  computed: {
    isUpdateAccountPossible() {
      // possible if smsProvider is set and all credential field values are set to something
      return !!this.smsProvider && this.credentials !== null && Object.values(this.credentials).every(v => !!v);
    },
    updateOrDeleteAccountText() {
      return Object(external_CoreHome_["translate"])('MobileMessaging_Settings_UpdateOrDeleteAccount', '<a id="displayAccountForm">', '</a>', '<a id="deleteAccount">', '</a>');
    },
    currentProviderDescription() {
      if (!this.smsProvider || !this.smsProviders) {
        return '';
      }
      return this.smsProviders[this.smsProvider];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue



ManageSmsProvidervue_type_script_lang_ts.render = ManageSmsProvidervue_type_template_id_1a20acce_render

/* harmony default export */ var ManageSmsProvider = (ManageSmsProvidervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=template&id=40b6024f

function DelegateMobileMessagingSettingsvue_type_template_id_40b6024f_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "delegatedManagement",
    title: _ctx.translate('MobileMessaging_Settings_LetUsersManageAPICredential'),
    modelValue: _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enabled = $event),
    "full-width": true,
    options: _ctx.delegateManagementOptions
  }, null, 8, ["title", "modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[1] || (_cache[1] = $event => _ctx.save()),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=template&id=40b6024f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var DelegateMobileMessagingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    delegateManagementOptions: {
      type: Array,
      required: true
    },
    delegatedManagement: [Number, Boolean]
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data() {
    return {
      isLoading: false,
      enabled: this.delegatedManagement ? 1 : 0
    };
  },
  methods: {
    save() {
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.setDelegatedManagement'
      }, {
        delegatedManagement: this.enabled && this.enabled !== '0' ? 'true' : 'false'
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          id: 'mobileMessagingSettings',
          type: 'transient',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
        external_CoreHome_["Matomo"].helper.redirect();
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue



DelegateMobileMessagingSettingsvue_type_script_lang_ts.render = DelegateMobileMessagingSettingsvue_type_template_id_40b6024f_render

/* harmony default export */ var DelegateMobileMessagingSettings = (DelegateMobileMessagingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=3fa85a3e

const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_1 = {
  key: 0
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_2 = {
  class: "row"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_3 = {
  class: "col s12"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_4 = {
  class: "form-group row"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_5 = {
  class: "col s12 m6"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_6 = {
  class: "col s12 m6 form-help"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_7 = {
  class: "form-group row addPhoneNumber"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_8 = {
  class: "col s12 m6"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_9 = {
  class: "countryCode left"
};
const ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "countryCodeSymbol"
}, "+", -1);
const _hoisted_11 = {
  class: "phoneNumber left"
};
const _hoisted_12 = {
  class: "addNumber left valign-wrapper"
};
const _hoisted_13 = {
  class: "col s12 m6 form-help"
};
const _hoisted_14 = {
  id: "ajaxErrorManagePhoneNumber",
  ref: "errorContainer"
};
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "notificationManagePhoneNumber"
}, null, -1);
const _hoisted_16 = {
  key: 1,
  class: "row"
};
const _hoisted_17 = {
  class: "col s12"
};
const _hoisted_18 = {
  class: "col s12 m6"
};
const _hoisted_19 = {
  class: "phoneNumber"
};
const _hoisted_20 = ["onUpdate:modelValue", "placeholder"];
const _hoisted_21 = {
  key: 0,
  class: "form-help col s12 m6"
};
const _hoisted_22 = ["onClick"];
const _hoisted_23 = {
  class: "ui-confirm",
  id: "confirmDeletePhoneNumber"
};
const _hoisted_24 = ["innerHTML"];
const _hoisted_25 = ["value"];
const _hoisted_26 = ["value"];
function ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Help')), 1), _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Add')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "countryCodeSelect",
    title: _ctx.translate('MobileMessaging_Settings_SelectCountry'),
    modelValue: _ctx.countryCallingCode,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.countryCallingCode = $event),
    "full-width": true,
    options: _ctx.countries
  }, null, 8, ["title", "modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_CountryCode_Help')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_9, [ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "countryCallingCode",
    title: _ctx.translate('MobileMessaging_Settings_CountryCode'),
    modelValue: _ctx.countryCallingCode,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.countryCallingCode = $event),
    "full-width": true,
    maxlength: 4
  }, null, 8, ["title", "modelValue"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "newPhoneNumber",
    modelValue: _ctx.newPhoneNumber,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.newPhoneNumber = $event),
    title: _ctx.translate('MobileMessaging_Settings_PhoneNumber'),
    "full-width": true,
    maxlength: 80
  }, null, 8, ["modelValue", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    disabled: !_ctx.canAddNumber || _ctx.isUpdatingPhoneNumbers,
    onConfirm: _cache[3] || (_cache[3] = $event => _ctx.addPhoneNumber()),
    value: _ctx.translate('General_Add')
  }, null, 8, ["disabled", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
    severity: "warning",
    id: "suspiciousPhoneNumber"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_SuspiciousPhoneNumber', '54184032')), 1)]),
    _: 1
  }, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSuspiciousPhoneNumber]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.strHelpAddPhone), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, null, 512), _hoisted_15, Object.keys(_ctx.phoneNumbers || {}).length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_ManagePhoneNumbers')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isUpdatingPhoneNumbers
  }, null, 8, ["loading"]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.phoneNumbers || [], (verificationData, phoneNumber, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "form-group row",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(phoneNumber), 1), !verificationData.verified ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
      key: 0,
      type: "text",
      class: "verificationCode",
      "onUpdate:modelValue": $event => _ctx.validationCode[index] = $event,
      placeholder: _ctx.translate('MobileMessaging_Settings_EnterActivationCode'),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, _hoisted_20)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.validationCode[index]]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !verificationData.verified ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
      key: 1,
      disabled: !_ctx.validationCode[index] || _ctx.isUpdatingPhoneNumbers,
      onConfirm: $event => _ctx.validateActivationCode(phoneNumber, index),
      value: _ctx.translate('MobileMessaging_Settings_ValidatePhoneNumber')
    }, null, 8, ["disabled", "onConfirm", "value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      disabled: _ctx.isUpdatingPhoneNumbers,
      onConfirm: $event => _ctx.removePhoneNumber(phoneNumber),
      value: _ctx.translate('General_Remove'),
      style: {
        "margin-left": "3.5px"
      }
    }, null, 8, ["disabled", "onConfirm", "value"])]), !verificationData.verified ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_VerificationCodeJustSent')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      onClick: $event => _ctx.resendVerificationCode(phoneNumber, index)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_ResendVerification')), 9, _hoisted_22)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   ")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
    innerHTML: _ctx.$sanitize(_ctx.removeNumberConfirmation)
  }, null, 8, _hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    role: "yes",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_25), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    role: "no",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_26)])], 64);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=3fa85a3e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=script&lang=ts



/* harmony default export */ var ManageMobilePhoneNumbersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isSuperUser: Boolean,
    defaultCallingCode: String,
    countries: {
      type: Array,
      required: true
    },
    strHelpAddPhone: {
      type: String,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Alert: external_CoreHome_["Alert"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data() {
    return {
      isUpdatingPhoneNumbers: false,
      phoneNumbers: {},
      countryCallingCode: this.defaultCallingCode || '',
      newPhoneNumber: '',
      validationCode: {},
      numberToRemove: ''
    };
  },
  mounted() {
    this.updatePhoneNumbers();
  },
  methods: {
    validateActivationCode(phoneNumber, index) {
      if (!this.validationCode[index]) {
        return;
      }
      const verificationCode = this.validationCode[index];
      this.isUpdatingPhoneNumbers = true;
      this.clearNotifcationsAndErrorsContainer();
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.validatePhoneNumber'
      }, {
        phoneNumber,
        verificationCode
      }, {
        errorElement: '#ajaxErrorManagePhoneNumber'
      }).then(response => {
        let notificationInstanceId;
        if (!response || !response.value) {
          const message = Object(external_CoreHome_["translate"])('MobileMessaging_Settings_InvalidActivationCode');
          notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
            message,
            placeat: '#notificationManagePhoneNumber',
            context: 'error',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient'
          });
        } else {
          const message = Object(external_CoreHome_["translate"])('MobileMessaging_Settings_PhoneActivated');
          notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
            message,
            placeat: '#notificationManagePhoneNumber',
            context: 'success',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient'
          });
          this.updatePhoneNumbers();
        }
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.validationCode[index] = '';
        this.isUpdatingPhoneNumbers = false;
      });
    },
    resendVerificationCode(phoneNumber) {
      this.isUpdatingPhoneNumbers = true;
      this.clearNotifcationsAndErrorsContainer();
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.resendVerificationCode'
      }, {
        phoneNumber
      }, {
        errorElement: '#ajaxErrorManagePhoneNumber'
      }).then(() => {
        const message = Object(external_CoreHome_["translate"])('MobileMessaging_Settings_NewVerificationCodeSent');
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message,
          placeat: '#notificationManagePhoneNumber',
          context: 'success',
          id: 'MobileMessaging_ValidatePhoneNumber',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
        this.updatePhoneNumbers();
      }).finally(() => {
        this.isUpdatingPhoneNumbers = false;
      });
    },
    updatePhoneNumbers() {
      this.isUpdatingPhoneNumbers = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.getPhoneNumbers'
      }, {}).then(phoneNumbers => {
        this.phoneNumbers = phoneNumbers;
        this.isUpdatingPhoneNumbers = false;
      });
    },
    removePhoneNumber(phoneNumber) {
      if (!phoneNumber) {
        return;
      }
      this.numberToRemove = phoneNumber;
      this.clearNotifcationsAndErrorsContainer();
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeletePhoneNumber', {
        yes: () => {
          this.isUpdatingPhoneNumbers = true;
          external_CoreHome_["AjaxHelper"].post({
            method: 'MobileMessaging.removePhoneNumber'
          }, {
            phoneNumber
          }, {
            errorElement: '#ajaxErrorManagePhoneNumber'
          }).then(() => {
            this.updatePhoneNumbers();
          }).finally(() => {
            this.isUpdatingPhoneNumbers = false;
            this.numberToRemove = '';
          });
        }
      });
    },
    addPhoneNumber() {
      const phoneNumber = `+${this.countryCallingCode}${this.newPhoneNumber}`;
      if (this.canAddNumber && phoneNumber.length > 1) {
        this.isUpdatingPhoneNumbers = true;
        this.clearNotifcationsAndErrorsContainer();
        external_CoreHome_["AjaxHelper"].post({
          method: 'MobileMessaging.addPhoneNumber'
        }, {
          phoneNumber
        }, {
          errorElement: '#ajaxErrorManagePhoneNumber'
        }).then(() => {
          this.updatePhoneNumbers();
          this.countryCallingCode = '';
          this.newPhoneNumber = '';
        }).finally(() => {
          this.isUpdatingPhoneNumbers = false;
        });
      }
    },
    clearNotifcationsAndErrorsContainer() {
      this.$refs.errorContainer.innerHTML = '';
      external_CoreHome_["NotificationsStore"].remove('MobileMessaging_ValidatePhoneNumber');
    }
  },
  computed: {
    showSuspiciousPhoneNumber() {
      return this.newPhoneNumber.trim().lastIndexOf('0', 0) === 0;
    },
    canAddNumber() {
      return !!this.newPhoneNumber && this.newPhoneNumber !== '';
    },
    removeNumberConfirmation() {
      return Object(external_CoreHome_["translate"])('MobileMessaging_ConfirmRemovePhoneNumber', this.numberToRemove);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue



ManageMobilePhoneNumbersvue_type_script_lang_ts.render = ManageMobilePhoneNumbersvue_type_template_id_3fa85a3e_render

/* harmony default export */ var ManageMobilePhoneNumbers = (ManageMobilePhoneNumbersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/AdminPage/AdminPage.vue?vue&type=template&id=39c941b4

const AdminPagevue_type_template_id_39c941b4_hoisted_1 = {
  class: "manageMobileMessagingSettings"
};
const AdminPagevue_type_template_id_39c941b4_hoisted_2 = {
  key: 0
};
const AdminPagevue_type_template_id_39c941b4_hoisted_3 = {
  key: 0
};
const AdminPagevue_type_template_id_39c941b4_hoisted_4 = {
  class: "ui-confirm",
  id: "confirmDeleteAccount"
};
const AdminPagevue_type_template_id_39c941b4_hoisted_5 = ["value"];
const AdminPagevue_type_template_id_39c941b4_hoisted_6 = ["value"];
function AdminPagevue_type_template_id_39c941b4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_DelegateMobileMessagingSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DelegateMobileMessagingSettings");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_ManageSmsProvider = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ManageSmsProvider");
  const _component_ManageMobilePhoneNumbers = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ManageMobilePhoneNumbers");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AdminPagevue_type_template_id_39c941b4_hoisted_1, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    "content-title": _ctx.translate('MobileMessaging_SettingsMenu')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DelegateMobileMessagingSettings, {
      "delegate-management-options": _ctx.delegateManagementOptions,
      "delegated-management": _ctx.delegatedManagement
    }, null, 8, ["delegate-management-options", "delegated-management"])]),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.accountManagedByCurrentUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 1,
    "content-title": _ctx.translate('MobileMessaging_Settings_SMSProvider'),
    feature: "true"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.isSuperUser && _ctx.delegatedManagement ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", AdminPagevue_type_template_id_39c941b4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_DelegatedSmsProviderOnlyAppliesToYou')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ManageSmsProvider, {
      "credential-supplied": _ctx.credentialSupplied,
      "credential-error": _ctx.credentialError,
      provider: _ctx.provider,
      "credit-left": _ctx.creditLeft,
      "sms-provider-options": _ctx.smsProviderOptions,
      "sms-providers": _ctx.smsProviders
    }, null, 8, ["credential-supplied", "credential-error", "provider", "credit-left", "sms-provider-options", "sms-providers"])]),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('MobileMessaging_PhoneNumbers')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [!_ctx.credentialSupplied ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", AdminPagevue_type_template_id_39c941b4_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.accountManagedByCurrentUser ? _ctx.translate('MobileMessaging_Settings_CredentialNotProvided') : _ctx.translate('MobileMessaging_Settings_CredentialNotProvidedByAdmin')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ManageMobilePhoneNumbers, {
      key: 1,
      "is-super-user": _ctx.isSuperUser,
      "default-calling-code": _ctx.defaultCallingCode,
      countries: _ctx.countries,
      "str-help-add-phone": _ctx.strHelpAddPhone,
      "phone-numbers": _ctx.phoneNumbers
    }, null, 8, ["is-super-user", "default-calling-code", "countries", "str-help-add-phone", "phone-numbers"]))]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AdminPagevue_type_template_id_39c941b4_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_DeleteAccountConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, AdminPagevue_type_template_id_39c941b4_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, AdminPagevue_type_template_id_39c941b4_hoisted_6)])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/AdminPage/AdminPage.vue?vue&type=template&id=39c941b4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MobileMessaging/vue/src/AdminPage/AdminPage.vue?vue&type=script&lang=ts





/* harmony default export */ var AdminPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    delegateManagementOptions: {
      type: Array,
      required: true
    },
    delegatedManagement: [Number, Boolean],
    isSuperUser: Boolean,
    defaultCallingCode: String,
    countries: {
      type: Array,
      required: true
    },
    strHelpAddPhone: {
      type: String,
      required: true
    },
    phoneNumbers: Object,
    accountManagedByCurrentUser: Boolean,
    credentialSupplied: Boolean,
    credentialError: String,
    provider: String,
    creditLeft: [Number, String],
    smsProviderOptions: {
      type: Object,
      required: true
    },
    smsProviders: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    DelegateMobileMessagingSettings: DelegateMobileMessagingSettings,
    ManageMobilePhoneNumbers: ManageMobilePhoneNumbers,
    ManageSmsProvider: ManageSmsProvider
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/AdminPage/AdminPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/AdminPage/AdminPage.vue



AdminPagevue_type_script_lang_ts.render = AdminPagevue_type_template_id_39c941b4_render

/* harmony default export */ var AdminPage = (AdminPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/index.ts
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
//# sourceMappingURL=MobileMessaging.umd.js.map