(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["PrivacyManager"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["PrivacyManager"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/PrivacyManager/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "AnonymizeIp", function() { return /* reexport */ AnonymizeIp; });
__webpack_require__.d(__webpack_exports__, "AnonymizeLogData", function() { return /* reexport */ AnonymizeLogData; });
__webpack_require__.d(__webpack_exports__, "DoNotTrackPreference", function() { return /* reexport */ DoNotTrackPreference; });
__webpack_require__.d(__webpack_exports__, "ReportDeletionSettings", function() { return /* reexport */ ReportDeletionSettings_store; });
__webpack_require__.d(__webpack_exports__, "DeleteOldLogs", function() { return /* reexport */ DeleteOldLogs; });
__webpack_require__.d(__webpack_exports__, "DeleteOldReports", function() { return /* reexport */ DeleteOldReports; });
__webpack_require__.d(__webpack_exports__, "ScheduleReportDeletion", function() { return /* reexport */ ScheduleReportDeletion; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=536c794c


var _hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_5 = {
  key: 0
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_8 = {
  class: "alert-warning alert"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeIpSettings",
    title: _ctx.translate('PrivacyManager_UseAnonymizeIp'),
    modelValue: _ctx.actualEnabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.actualEnabled = $event;
    }),
    "inline-help": _ctx.anonymizeIpEnabledHelp
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "maskLength",
    title: _ctx.translate('PrivacyManager_AnonymizeIpMaskLengtDescription'),
    modelValue: _ctx.actualMaskLength,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.actualMaskLength = $event;
    }),
    options: _ctx.maskLengthOptions,
    "inline-help": _ctx.translate('PrivacyManager_GeolocationAnonymizeIpNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "useAnonymizedIpForVisitEnrichment",
    title: _ctx.translate('PrivacyManager_UseAnonymizedIpForVisitEnrichment'),
    modelValue: _ctx.actualUseAnonymizedIpForVisitEnrichment,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.actualUseAnonymizedIpForVisitEnrichment = $event;
    }),
    options: _ctx.useAnonymizedIpForVisitEnrichmentOptions,
    "inline-help": _ctx.translate('PrivacyManager_UseAnonymizedIpForVisitEnrichmentNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.actualEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeUserId",
    title: _ctx.translate('PrivacyManager_PseudonymizeUserId'),
    modelValue: _ctx.actualAnonymizeUserId,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.actualAnonymizeUserId = $event;
    })
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote')) + " ", 1), _hoisted_1, _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote2')), 1)];
    }),
    _: 1
  }, 8, ["title", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeOrderId",
    title: _ctx.translate('PrivacyManager_UseAnonymizeOrderId'),
    modelValue: _ctx.actualAnonymizeOrderId,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return _ctx.actualAnonymizeOrderId = $event;
    }),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeOrderIdNote')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "forceCookielessTracking",
    title: _ctx.translate('PrivacyManager_ForceCookielessTracking'),
    modelValue: _ctx.actualForceCookielessTracking,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.actualForceCookielessTracking = $event;
    })
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription', _ctx.trackerFileName)) + " ", 1), _hoisted_3, _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription2')), 1), !_ctx.trackerWritable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, [_hoisted_6, _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescriptionNotWritable', _ctx.trackerFileName)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 1
  }, 8, ["title", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "anonymizeReferrer",
    title: _ctx.translate('PrivacyManager_AnonymizeReferrer'),
    modelValue: _ctx.actualAnonymizeReferrer,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.actualAnonymizeReferrer = $event;
    }),
    options: _ctx.referrerAnonymizationOptions,
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeReferrerNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=536c794c

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=script&lang=ts



/* harmony default export */ var AnonymizeIpvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    anonymizeIpEnabled: Boolean,
    anonymizeUserId: Boolean,
    maskLength: {
      type: Number,
      required: true
    },
    useAnonymizedIpForVisitEnrichment: Boolean,
    anonymizeOrderId: Boolean,
    forceCookielessTracking: Boolean,
    anonymizeReferrer: String,
    maskLengthOptions: {
      type: Array,
      required: true
    },
    useAnonymizedIpForVisitEnrichmentOptions: {
      type: Array,
      required: true
    },
    trackerFileName: {
      type: String,
      required: true
    },
    trackerWritable: {
      type: Boolean,
      required: true
    },
    referrerAnonymizationOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      actualEnabled: !!this.anonymizeIpEnabled,
      actualMaskLength: this.maskLength,
      actualUseAnonymizedIpForVisitEnrichment: this.useAnonymizedIpForVisitEnrichment ? '1' : '0',
      actualAnonymizeUserId: !!this.anonymizeUserId,
      actualAnonymizeOrderId: !!this.anonymizeOrderId,
      actualForceCookielessTracking: !!this.forceCookielessTracking,
      actualAnonymizeReferrer: this.anonymizeReferrer
    };
  },
  methods: {
    save: function save() {
      var _this = this;

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'PrivacyManager.setAnonymizeIpSettings'
      }, {
        anonymizeIPEnable: this.actualEnabled ? '1' : '0',
        anonymizeUserId: this.actualAnonymizeUserId ? '1' : '0',
        anonymizeOrderId: this.actualAnonymizeOrderId ? '1' : '0',
        forceCookielessTracking: this.actualForceCookielessTracking ? '1' : '0',
        anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : '',
        maskLength: this.actualMaskLength,
        useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment ? '1' : '0'
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'toast'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  },
  computed: {
    anonymizeIpEnabledHelp: function anonymizeIpEnabledHelp() {
      var inlineHelp1 = Object(external_CoreHome_["translate"])('PrivacyManager_AnonymizeIpInlineHelp');
      var inlineHelp2 = Object(external_CoreHome_["translate"])('PrivacyManager_AnonymizeIpDescription');
      return "".concat(inlineHelp1, " ").concat(inlineHelp2);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue



AnonymizeIpvue_type_script_lang_ts.render = render

/* harmony default export */ var AnonymizeIp = (AnonymizeIpvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=bdf32a0e

var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_1 = {
  class: "anonymizeLogData"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_2 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_3 = {
  class: "col s12 input-field"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_4 = {
  for: "anonymizeSite",
  class: "siteSelectorLabel"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_5 = {
  class: "sites_autocomplete"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_6 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_7 = {
  class: "col s6 input-field"
};
var AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_8 = {
  for: "anonymizeStartDate",
  class: "active"
};
var _hoisted_9 = ["value"];
var _hoisted_10 = {
  class: "col s6 input-field"
};
var _hoisted_11 = {
  for: "anonymizeEndDate",
  class: "active"
};
var _hoisted_12 = ["value"];
var _hoisted_13 = {
  name: "anonymizeIp"
};
var _hoisted_14 = {
  name: "anonymizeLocation"
};
var _hoisted_15 = {
  name: "anonymizeTheUserId"
};
var _hoisted_16 = {
  class: "form-group row"
};
var _hoisted_17 = {
  class: "col s12 m6"
};
var _hoisted_18 = {
  for: "visit_columns"
};
var _hoisted_19 = {
  class: "innerFormField",
  name: "visit_columns"
};
var _hoisted_20 = ["onClick", "title"];
var _hoisted_21 = {
  class: "col s12 m6"
};
var _hoisted_22 = {
  class: "form-help"
};
var _hoisted_23 = {
  class: "inline-help"
};
var _hoisted_24 = {
  class: "form-group row"
};
var _hoisted_25 = {
  class: "col s12"
};
var _hoisted_26 = {
  class: "form-group row"
};
var _hoisted_27 = {
  class: "col s12 m6"
};
var _hoisted_28 = {
  for: "action_columns"
};
var _hoisted_29 = {
  class: "innerFormField",
  name: "action_columns"
};
var _hoisted_30 = ["onClick", "title"];
var _hoisted_31 = {
  class: "col s12 m6"
};
var _hoisted_32 = {
  class: "form-help"
};
var _hoisted_33 = {
  class: "inline-help"
};

var _hoisted_34 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);

var _hoisted_35 = {
  class: "ui-confirm",
  id: "confirmAnonymizeLogData",
  ref: "confirmAnonymizeLogData"
};
var _hoisted_36 = ["value"];
var _hoisted_37 = ["value"];
function AnonymizeLogDatavue_type_template_id_bdf32a0e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeSites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
    id: "anonymizeSite",
    modelValue: _ctx.site,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.site = $event;
    }),
    "show-all-sites-item": true,
    "switch-site-on-select": false,
    "show-selected-site": true
  }, null, 8, ["modelValue"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_bdf32a0e_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataFrom')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "anonymizeStartDate",
    class: "anonymizeStartDate",
    ref: "anonymizeStartDate",
    name: "anonymizeStartDate",
    value: _ctx.startDate,
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydownStartDate($event);
    }),
    onChange: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onKeydownStartDate($event);
    })
  }, null, 40, _hoisted_9)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "anonymizeEndDate",
    id: "anonymizeEndDate",
    ref: "anonymizeEndDate",
    name: "anonymizeEndDate",
    value: _ctx.endDate,
    onKeydown: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.onKeydownEndDate($event);
    }),
    onChange: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.onKeydownEndDate($event);
    })
  }, null, 40, _hoisted_12)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeIp",
    title: _ctx.translate('PrivacyManager_AnonymizeIp'),
    modelValue: _ctx.anonymizeIp,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.anonymizeIp = $event;
    }),
    introduction: _ctx.translate('General_Visit'),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeIpHelp')
  }, null, 8, ["title", "modelValue", "introduction", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeLocation",
    title: _ctx.translate('PrivacyManager_AnonymizeLocation'),
    modelValue: _ctx.anonymizeLocation,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.anonymizeLocation = $event;
    }),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeLocationHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeTheUserId",
    title: _ctx.translate('PrivacyManager_AnonymizeUserId'),
    modelValue: _ctx.anonymizeUserId,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.anonymizeUserId = $event;
    }),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeUserIdHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedVisitColumns, function (visitColumn, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("selectedVisitColumns selectedVisitColumns".concat(index, " multiple valign-wrapper")),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "visit_columns",
      "model-value": visitColumn.column,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        visitColumn.column = $event;

        _ctx.onVisitColumnChange();
      },
      "full-width": true,
      options: _ctx.availableVisitColumns
    }, null, 8, ["model-value", "onUpdate:modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-minus valign",
      onClick: function onClick($event) {
        return _ctx.removeVisitColumn(index);
      },
      title: _ctx.translate('General_Remove')
    }, null, 8, _hoisted_20), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedVisitColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedActionColumns, function (actionColumn, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("selectedActionColumns selectedActionColumns".concat(index, " multiple valign-wrapper")),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "action_columns",
      "model-value": actionColumn.column,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        actionColumn.column = $event;

        _ctx.onActionColumnChange();
      },
      "full-width": true,
      options: _ctx.availableActionColumns
    }, null, 8, ["model-value", "onUpdate:modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-minus valign",
      onClick: function onClick($event) {
        return _ctx.removeActionColumn(index);
      },
      title: _ctx.translate('General_Remove')
    }, null, 8, _hoisted_30), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedActionColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_34, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeProcessInfo')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "anonymizePastData",
    onConfirm: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.scheduleAnonymization();
    }),
    disabled: _ctx.isAnonymizePastDataDisabled,
    value: _ctx.translate('PrivacyManager_AnonymizeDataNow')
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_35, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeDataConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_36), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_37)], 512)]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=bdf32a0e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=script&lang=ts




function sub(value) {
  if (value < 10) {
    return "0".concat(value);
  }

  return value;
}

/* harmony default export */ var AnonymizeLogDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SiteSelector: external_CoreHome_["SiteSelector"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data: function data() {
    var now = new Date();
    var startDate = "".concat(now.getFullYear(), "-").concat(sub(now.getMonth() + 1), "-").concat(sub(now.getDay() + 1));
    return {
      isLoading: false,
      isDeleting: false,
      anonymizeIp: false,
      anonymizeLocation: false,
      anonymizeUserId: false,
      site: {
        id: 'all',
        name: 'All Websites'
      },
      availableVisitColumns: [],
      availableActionColumns: [],
      selectedVisitColumns: [{
        column: ''
      }],
      selectedActionColumns: [{
        column: ''
      }],
      startDate: startDate,
      endDate: startDate
    };
  },
  created: function created() {
    var _this = this;

    this.onKeydownStartDate = Object(external_CoreHome_["debounce"])(this.onKeydownStartDate, 50);
    this.onKeydownEndDate = Object(external_CoreHome_["debounce"])(this.onKeydownEndDate, 50);
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'PrivacyManager.getAvailableVisitColumnsToAnonymize'
    }).then(function (columns) {
      _this.availableVisitColumns = [];
      columns.forEach(function (column) {
        _this.availableVisitColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize'
    }).then(function (columns) {
      _this.availableActionColumns = [];
      columns.forEach(function (column) {
        _this.availableActionColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    setTimeout(function () {
      var options1 = external_CoreHome_["Matomo"].getBaseDatePickerOptions(null);
      var options2 = external_CoreHome_["Matomo"].getBaseDatePickerOptions(null);
      $(_this.$refs.anonymizeStartDate).datepicker(options1);
      $(_this.$refs.anonymizeEndDate).datepicker(options2);
    });
  },
  methods: {
    onVisitColumnChange: function onVisitColumnChange() {
      var hasAll = this.selectedVisitColumns.every(function (col) {
        return !!(col !== null && col !== void 0 && col.column);
      });

      if (hasAll) {
        this.addVisitColumn();
      }
    },
    addVisitColumn: function addVisitColumn() {
      this.selectedVisitColumns.push({
        column: ''
      });
    },
    removeVisitColumn: function removeVisitColumn(index) {
      if (index > -1) {
        var lastIndex = this.selectedVisitColumns.length - 1;

        if (lastIndex === index) {
          this.selectedVisitColumns[index] = {
            column: ''
          };
        } else {
          this.selectedVisitColumns.splice(index, 1);
        }
      }
    },
    onActionColumnChange: function onActionColumnChange() {
      var hasAll = this.selectedActionColumns.every(function (col) {
        return !!(col !== null && col !== void 0 && col.column);
      });

      if (hasAll) {
        this.addActionColumn();
      }
    },
    addActionColumn: function addActionColumn() {
      this.selectedActionColumns.push({
        column: ''
      });
    },
    removeActionColumn: function removeActionColumn(index) {
      if (index > -1) {
        var lastIndex = this.selectedActionColumns.length - 1;

        if (lastIndex === index) {
          this.selectedActionColumns[index] = {
            column: ''
          };
        } else {
          this.selectedActionColumns.splice(index, 1);
        }
      }
    },
    scheduleAnonymization: function scheduleAnonymization() {
      var date = "".concat(this.startDate, ",").concat(this.endDate);

      if (this.startDate === this.endDate) {
        date = this.startDate;
      }

      var params = {
        date: date
      };
      params.idSites = this.site.id;
      params.anonymizeIp = this.anonymizeIp ? '1' : '0';
      params.anonymizeLocation = this.anonymizeLocation ? '1' : '0';
      params.anonymizeUserId = this.anonymizeUserId ? '1' : '0';
      params.unsetVisitColumns = this.selectedVisitColumns.filter(function (c) {
        return !!(c !== null && c !== void 0 && c.column);
      }).map(function (c) {
        return c.column;
      });
      params.unsetLinkVisitActionColumns = this.selectedActionColumns.filter(function (c) {
        return !!(c !== null && c !== void 0 && c.column);
      }).map(function (c) {
        return c.column;
      });
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmAnonymizeLogData, {
        yes: function yes() {
          external_CoreHome_["AjaxHelper"].post({
            method: 'PrivacyManager.anonymizeSomeRawData'
          }, params).then(function () {
            window.location.reload(true);
          });
        }
      });
    },
    onKeydownStartDate: function onKeydownStartDate(event) {
      this.startDate = event.target.value;
    },
    onKeydownEndDate: function onKeydownEndDate(event) {
      this.endDate = event.target.value;
    }
  },
  computed: {
    isAnonymizePastDataDisabled: function isAnonymizePastDataDisabled() {
      return !this.anonymizeIp && !this.anonymizeLocation && !this.selectedVisitColumns && !this.selectedActionColumns;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue



AnonymizeLogDatavue_type_script_lang_ts.render = AnonymizeLogDatavue_type_template_id_bdf32a0e_render

/* harmony default export */ var AnonymizeLogData = (AnonymizeLogDatavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=0506d6be

function DoNotTrackPreferencevue_type_template_id_0506d6be_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "doNotTrack",
    modelValue: _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.enabled = $event;
    }),
    options: _ctx.doNotTrackOptions,
    "inline-help": _ctx.translate('PrivacyManager_DoNotTrack_Description')
  }, null, 8, ["modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=0506d6be

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts



/* harmony default export */ var DoNotTrackPreferencevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    dntSupport: Boolean,
    doNotTrackOptions: {
      type: Array,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: this.dntSupport ? 1 : 0
    };
  },
  methods: {
    save: function save() {
      var _this = this;

      this.isLoading = true;
      var action = 'deactivateDoNotTrack';

      if (this.enabled && this.enabled !== '0') {
        action = 'activateDoNotTrack';
      }

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "PrivacyManager.".concat(action)
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue



DoNotTrackPreferencevue_type_script_lang_ts.render = DoNotTrackPreferencevue_type_template_id_0506d6be_render

/* harmony default export */ var DoNotTrackPreference = (DoNotTrackPreferencevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ReportDeletionSettings/ReportDeletionSettings.store.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var ReportDeletionSettings_store_ReportDeletionSettingsStore = /*#__PURE__*/function () {
  function ReportDeletionSettingsStore() {
    var _this = this;

    _classCallCheck(this, ReportDeletionSettingsStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      settings: {},
      showEstimate: false,
      loadingEstimation: false,
      estimation: '',
      isModified: false
    }));

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "enableDeleteReports", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.settings.enableDeleteReports;
    }));

    _defineProperty(this, "enableDeleteLogs", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.settings.enableDeleteLogs;
    }));

    _defineProperty(this, "currentRequest", void 0);
  }

  _createClass(ReportDeletionSettingsStore, [{
    key: "updateSettings",
    value: function updateSettings(settings) {
      this.initSettings(settings);
      this.privateState.isModified = true;
    }
  }, {
    key: "initSettings",
    value: function initSettings(settings) {
      this.privateState.settings = Object.assign(Object.assign({}, this.privateState.settings), settings);
      this.reloadDbStats();
    }
  }, {
    key: "savePurgeDataSettings",
    value: function savePurgeDataSettings(apiMethod, settings) {
      this.privateState.isModified = false;
      return external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: apiMethod
      }, Object.assign(Object.assign({}, settings), {}, {
        enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
        enableDeleteReports: settings.enableDeleteReports ? '1' : '0'
      })).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'toast'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      });
    }
  }, {
    key: "isEitherDeleteSectionEnabled",
    value: function isEitherDeleteSectionEnabled() {
      return this.state.value.settings.enableDeleteLogs || this.state.value.settings.enableDeleteReports;
    }
  }, {
    key: "isManualEstimationLinkShowing",
    value: function isManualEstimationLinkShowing() {
      return window.$('#getPurgeEstimateLink').length > 0;
    }
  }, {
    key: "reloadDbStats",
    value: function reloadDbStats(forceEstimate) {
      var _this2 = this;

      if (this.currentRequest) {
        // if the manual estimate link is showing, abort unless forcing
        this.currentRequest.abort();
      }

      if (!forceEstimate && (!this.isEitherDeleteSectionEnabled() || this.isManualEstimationLinkShowing())) {
        return;
      }

      this.privateState.loadingEstimation = true;
      this.privateState.estimation = '';
      this.privateState.showEstimate = false;
      var settings = this.privateState.settings;
      var formData = {
        enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
        enableDeleteReports: settings.enableDeleteReports ? '1' : '0'
      };

      if (forceEstimate === true) {
        formData.forceEstimate = 1;
      }

      this.currentRequest = new AbortController();
      external_CoreHome_["AjaxHelper"].post({
        module: 'PrivacyManager',
        action: 'getDatabaseSize',
        format: 'html'
      }, formData, {
        abortController: this.currentRequest,
        format: 'html'
      }).then(function (data) {
        _this2.privateState.estimation = data;
        _this2.privateState.showEstimate = true;
        _this2.privateState.loadingEstimation = false;
      }).finally(function () {
        _this2.currentRequest = undefined;
        _this2.privateState.loadingEstimation = false;
      });
    }
  }]);

  return ReportDeletionSettingsStore;
}();

/* harmony default export */ var ReportDeletionSettings_store = (new ReportDeletionSettings_store_ReportDeletionSettingsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=2d91a21d

var DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_1 = {
  id: "formDeleteSettings"
};
var DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_2 = {
  id: "deleteLogSettingEnabled"
};
var DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_3 = {
  class: "alert alert-warning deleteOldLogsWarning",
  style: {
    "width": "50%"
  }
};
var DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_4 = {
  href: "https://matomo.org/faq/general/#faq_125",
  rel: "noreferrer noopener",
  target: "_blank"
};
var DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_5 = {
  id: "deleteLogSettings"
};
function DeleteOldLogsvue_type_template_id_2d91a21d_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.enabled = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteLog'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteRawDataInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ClickHere')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_2d91a21d_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "deleteOlderThan",
    "model-value": _ctx.deleteOlderThan,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      _ctx.deleteOlderThan = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteOlderThanTitle,
    "inline-help": _ctx.translate('PrivacyManager_LeastDaysInput', '1')
  }, null, 8, ["model-value", "title", "inline-help"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=2d91a21d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts




var _window = window,
    DeleteOldLogsvue_type_script_lang_ts_$ = _window.$;
/* harmony default export */ var DeleteOldLogsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: this.deleteData.config.delete_logs_enable === '1',
      deleteOlderThan: this.deleteData.config.delete_logs_older_than
    };
  },
  created: function created() {
    var _this = this;

    setTimeout(function () {
      ReportDeletionSettings_store.initSettings(_this.settings);
    });
  },
  methods: {
    saveSettings: function saveSettings() {
      var _this2 = this;

      var method = 'PrivacyManager.setDeleteLogsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings).finally(function () {
        _this2.isLoading = false;
      });
    },
    reloadDbStats: function reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
    },
    save: function save() {
      var _this3 = this;

      if (this.enabled) {
        var confirmId = 'deleteLogsConfirm';

        if (ReportDeletionSettings_store.enableDeleteReports.value) {
          confirmId = 'deleteBothConfirm';
        }

        DeleteOldLogsvue_type_script_lang_ts_$('#confirmDeleteSettings').find('>h2').hide();
        DeleteOldLogsvue_type_script_lang_ts_$("#".concat(confirmId)).show();
        external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteSettings', {
          yes: function yes() {
            _this3.saveSettings();
          }
        });
      } else {
        this.saveSettings();
      }
    }
  },
  computed: {
    settings: function settings() {
      return {
        enableDeleteLogs: !!this.enabled,
        deleteLogsOlderThan: this.deleteOlderThan
      };
    },
    deleteOlderThanTitle: function deleteOlderThanTitle() {
      return "".concat(Object(external_CoreHome_["translate"])('PrivacyManager_DeleteLogsOlderThan'), " (").concat(Object(external_CoreHome_["translate"])('Intl_PeriodDays'), ")");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue



DeleteOldLogsvue_type_script_lang_ts.render = DeleteOldLogsvue_type_template_id_2d91a21d_render

/* harmony default export */ var DeleteOldLogs = (DeleteOldLogsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=0a48298a

var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_1 = {
  id: "formDeleteSettings"
};
var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_2 = {
  id: "deleteReportsSettingEnabled"
};
var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_3 = {
  class: "alert alert-warning",
  style: {
    "width": "50%"
  }
};

var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var DeleteOldReportsvue_type_template_id_0a48298a_hoisted_6 = {
  id: "deleteReportsSettings"
};
function DeleteOldReportsvue_type_template_id_0a48298a_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldReportsvue_type_template_id_0a48298a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_0a48298a_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.enabled = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteReports'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteAggregateReportsDetailedInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_0a48298a_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo2', _ctx.deleteOldLogsText)), 1), DeleteOldReportsvue_type_template_id_0a48298a_hoisted_4, DeleteOldReportsvue_type_template_id_0a48298a_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo3', _ctx.deleteOldLogsText)), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_0a48298a_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "deleteReportsOlderThan",
    "model-value": _ctx.deleteOlderThan,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      _ctx.deleteOlderThan = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteReportsOlderThanTitle,
    "inline-help": _ctx.translate('PrivacyManager_LeastMonthsInput', '1')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepBasic",
    "model-value": _ctx.keepBasic,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      _ctx.keepBasic = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteReportsKeepBasicTitle,
    "inline-help": _ctx.translate('PrivacyManager_KeepBasicMetricsReportsDetailedInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_KeepDataFor')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepDay",
    "model-value": _ctx.keepDataForDay,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      _ctx.keepDataForDay = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_DailyReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepWeek",
    "model-value": _ctx.keepDataForWeek,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      _ctx.keepDataForWeek = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_WeeklyReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepMonth",
    "model-value": _ctx.keepDataForMonth,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      _ctx.keepDataForMonth = $event;

      _ctx.reloadDbStats();
    }),
    title: "".concat(_ctx.translate('General_MonthlyReports'), " (").concat(_ctx.translate('General_Recommended'), ")")
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepYear",
    "model-value": _ctx.keepDataForYear,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      _ctx.keepDataForYear = $event;

      _ctx.reloadDbStats();
    }),
    title: "".concat(_ctx.translate('General_YearlyReports'), " (").concat(_ctx.translate('General_Recommended'), ")")
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepRange",
    "model-value": _ctx.keepDataForRange,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      _ctx.keepDataForRange = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_RangeReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepSegments",
    "model-value": _ctx.keepDataForSegments,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
      _ctx.keepDataForSegments = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_KeepReportSegments')
  }, null, 8, ["model-value", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[9] || (_cache[9] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=0a48298a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts





function getInt(value) {
  return value ? '1' : '0';
}

var DeleteOldReportsvue_type_script_lang_ts_window = window,
    DeleteOldReportsvue_type_script_lang_ts_$ = DeleteOldReportsvue_type_script_lang_ts_window.$;
/* harmony default export */ var DeleteOldReportsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: parseInt(this.deleteData.config.delete_reports_enable, 10) === 1,
      deleteOlderThan: this.deleteData.config.delete_reports_older_than,
      keepBasic: parseInt(this.deleteData.config.delete_reports_keep_basic_metrics, 10) === 1,
      keepDataForDay: parseInt(this.deleteData.config.delete_reports_keep_day_reports, 10) === 1,
      keepDataForWeek: parseInt(this.deleteData.config.delete_reports_keep_week_reports, 10) === 1,
      keepDataForMonth: parseInt(this.deleteData.config.delete_reports_keep_month_reports, 10) === 1,
      keepDataForYear: parseInt(this.deleteData.config.delete_reports_keep_year_reports, 10) === 1,
      keepDataForRange: parseInt(this.deleteData.config.delete_reports_keep_range_reports, 10) === 1,
      keepDataForSegments: parseInt(this.deleteData.config.delete_reports_keep_segment_reports, 10) === 1
    };
  },
  created: function created() {
    var _this = this;

    setTimeout(function () {
      ReportDeletionSettings_store.initSettings(_this.settings);
    });
  },
  methods: {
    saveSettings: function saveSettings() {
      var _this2 = this;

      var method = 'PrivacyManager.setDeleteReportsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings).finally(function () {
        _this2.isLoading = false;
      });
    },
    reloadDbStats: function reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
    },
    save: function save() {
      var _this3 = this;

      if (this.enabled) {
        var confirmId = 'deleteReportsConfirm';

        if (ReportDeletionSettings_store.enableDeleteLogs.value) {
          confirmId = 'deleteBothConfirm';
        }

        DeleteOldReportsvue_type_script_lang_ts_$('#confirmDeleteSettings').find('>h2').hide();
        DeleteOldReportsvue_type_script_lang_ts_$("#".concat(confirmId)).show();
        external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteSettings', {
          yes: function yes() {
            _this3.saveSettings();
          }
        });
      } else {
        this.saveSettings();
      }
    }
  },
  computed: {
    settings: function settings() {
      return {
        enableDeleteReports: this.enabled,
        deleteReportsOlderThan: this.deleteOlderThan,
        keepBasic: getInt(this.keepBasic),
        keepDay: getInt(this.keepDataForDay),
        keepWeek: getInt(this.keepDataForWeek),
        keepMonth: getInt(this.keepDataForMonth),
        keepYear: getInt(this.keepDataForYear),
        keepRange: getInt(this.keepDataForRange),
        keepSegments: getInt(this.keepDataForSegments)
      };
    },
    deleteOldLogsText: function deleteOldLogsText() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_UseDeleteLog');
    },
    deleteReportsOlderThanTitle: function deleteReportsOlderThanTitle() {
      var first = Object(external_CoreHome_["translate"])('PrivacyManager_DeleteReportsOlderThan');
      return "".concat(first, " (").concat(Object(external_CoreHome_["translate"])('Intl_PeriodMonths'), ")");
    },
    deleteReportsKeepBasicTitle: function deleteReportsKeepBasicTitle() {
      var first = Object(external_CoreHome_["translate"])('PrivacyManager_KeepBasicMetrics');
      return "".concat(first, " (").concat(Object(external_CoreHome_["translate"])('General_Recommended'), ")");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue



DeleteOldReportsvue_type_script_lang_ts.render = DeleteOldReportsvue_type_template_id_0a48298a_render

/* harmony default export */ var DeleteOldReports = (DeleteOldReportsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=22bfc8d0

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_1 = {
  id: "formDeleteSettings"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_2 = {
  id: "deleteSchedulingSettings"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_3 = {
  id: "deleteSchedulingSettingsInlineHelp",
  class: "inline-help-node"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_4 = {
  key: 0
};

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_9 = {
  key: 0,
  id: "deleteDataEstimateSect",
  class: "form-group row"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_10 = {
  class: "col s12",
  id: "databaseSizeHeadline"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_11 = {
  class: "col s12 m6"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_12 = ["innerHTML"];

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" ");

var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_14 = {
  class: "col s12 m6"
};
var ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_15 = {
  key: 0,
  class: "form-help"
};
function ScheduleReportDeletionvue_type_template_id_22bfc8d0_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "scheduleSettingsHeadline",
    "content-title": _ctx.translate('PrivacyManager_DeleteSchedulingSettings')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "deleteLowestInterval",
        title: _ctx.translate('PrivacyManager_DeleteDataInterval'),
        modelValue: _ctx.deleteLowestInterval,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.deleteLowestInterval = $event;
        }),
        options: _ctx.scheduleDeletionOptions
      }, {
        "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_3, [_ctx.deleteData.lastRun ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_LastDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.lastRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_5, ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_6])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_NextDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.nextRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_7, ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
            id: "purgeDataNowLink",
            href: "#",
            onClick: _cache[0] || (_cache[0] = function ($event) {
              return _ctx.executeDataPurgeNow();
            })
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PurgeNow')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showPurgeNowLink]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
            "loading-message": _ctx.translate('PrivacyManager_PurgingData'),
            loading: _ctx.loadingDataPurge
          }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            id: "db-purged-message"
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DBPurged')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataWasPurged]])])];
        }),
        _: 1
      }, 8, ["title", "modelValue", "options"])])]), _ctx.deleteData.config.enable_database_size_estimate === '1' || _ctx.deleteData.config.enable_database_size_estimate === 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ReportsDataSavedEstimate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        id: "deleteDataEstimate",
        innerHTML: _ctx.$sanitize(_ctx.estimation)
      }, null, 8, ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEstimate]]), ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.loadingEstimation
      }, null, 8, ["loading"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_14, [_ctx.deleteData.config.enable_auto_database_size_estimate !== '1' && _ctx.deleteData.config.enable_auto_database_size_estimate !== 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_22bfc8d0_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        id: "getPurgeEstimateLink",
        href: "#",
        onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
          return _ctx.getPurgeEstimate();
        }, ["prevent"]))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GetPurgeEstimate')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[3] || (_cache[3] = function ($event) {
          return _ctx.save();
        }),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])];
    }),
    _: 1
  }, 8, ["content-title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isEitherDeleteSectionEnabled]])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=22bfc8d0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=script&lang=ts




/* harmony default export */ var ScheduleReportDeletionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      loadingDataPurge: false,
      dataWasPurged: false,
      showPurgeNowLink: true,
      deleteLowestInterval: this.deleteData.config.delete_logs_schedule_lowest_interval
    };
  },
  methods: {
    save: function save() {
      var method = 'PrivacyManager.setScheduleReportDeletionSettings';
      ReportDeletionSettings_store.savePurgeDataSettings(method, {
        deleteLowestInterval: this.deleteLowestInterval
      });
    },
    executeDataPurgeNow: function executeDataPurgeNow() {
      var _this = this;

      if (ReportDeletionSettings_store.state.value.isModified) {
        // ask user if they really want to delete their old data
        external_CoreHome_["Matomo"].helper.modalConfirm('#saveSettingsBeforePurge', {
          yes: function yes() {
            return null;
          }
        });
        return;
      }

      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmPurgeNow', {
        yes: function yes() {
          _this.loadingDataPurge = true;
          _this.showPurgeNowLink = false; // execute a data purge

          external_CoreHome_["AjaxHelper"].fetch({
            module: 'PrivacyManager',
            action: 'executeDataPurge',
            format: 'html'
          }, {
            withTokenInUrl: true
          }).then(function () {
            // force reload
            ReportDeletionSettings_store.reloadDbStats();
            _this.dataWasPurged = true;
            setTimeout(function () {
              _this.dataWasPurged = false;
              _this.showPurgeNowLink = true;
            }, 2000);
          }).finally(function () {
            _this.loadingDataPurge = false;
          });
        }
      });
    },
    getPurgeEstimate: function getPurgeEstimate() {
      return ReportDeletionSettings_store.reloadDbStats(true);
    }
  },
  computed: {
    showEstimate: function showEstimate() {
      return ReportDeletionSettings_store.state.value.showEstimate;
    },
    isEitherDeleteSectionEnabled: function isEitherDeleteSectionEnabled() {
      return ReportDeletionSettings_store.isEitherDeleteSectionEnabled();
    },
    estimation: function estimation() {
      return ReportDeletionSettings_store.state.value.estimation;
    },
    loadingEstimation: function loadingEstimation() {
      return ReportDeletionSettings_store.state.value.loadingEstimation;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue



ScheduleReportDeletionvue_type_script_lang_ts.render = ScheduleReportDeletionvue_type_template_id_22bfc8d0_render

/* harmony default export */ var ScheduleReportDeletion = (ScheduleReportDeletionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/index.ts
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
//# sourceMappingURL=PrivacyManager.umd.js.map