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

/***/ "d004":
/***/ (function(module, exports) {



/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "AnonymizeIp", function() { return /* reexport */ AnonymizeIp; });
__webpack_require__.d(__webpack_exports__, "OptOutCustomizer", function() { return /* reexport */ OptOutCustomizer; });

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=6dd537f0

var OptOutCustomizervue_type_template_id_6dd537f0_hoisted_1 = {
  class: "optOutCustomizer"
};
var OptOutCustomizervue_type_template_id_6dd537f0_hoisted_2 = ["innerHTML"];
var OptOutCustomizervue_type_template_id_6dd537f0_hoisted_3 = ["value"];
var OptOutCustomizervue_type_template_id_6dd537f0_hoisted_4 = ["value"];

var OptOutCustomizervue_type_template_id_6dd537f0_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<option value=\"px\">px</option><option value=\"pt\">pt</option><option value=\"em\">em</option><option value=\"rem\">rem</option><option value=\"%\">%</option>", 5);

var _hoisted_10 = [OptOutCustomizervue_type_template_id_6dd537f0_hoisted_5];
var _hoisted_11 = {
  ref: "pre"
};
var _hoisted_12 = ["innerHTML"];
var _hoisted_13 = ["src"];
function OptOutCustomizervue_type_template_id_6dd537f0_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OptOutCustomizervue_type_template_id_6dd537f0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_OptOutExplanation')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.readThisToLearnMore)
  }, null, 8, OptOutCustomizervue_type_template_id_6dd537f0_hoisted_2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutCustomize')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.fontColor,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onFontColorChange($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onFontColorChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_6dd537f0_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_BackgroundColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.backgroundColor,
    onKeydown: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onBgColorChange($event);
    }),
    onChange: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.onBgColorChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_6dd537f0_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontSize')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontSizeInput",
    type: "number",
    min: "1",
    max: "100",
    onKeydown: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.onFontSizeChange($event);
    }),
    onChange: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.onFontSizeChange($event);
    })
  }, null, 32)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default",
    onKeydown: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.onFontSizeUnitChange($event);
    }),
    onChange: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.onFontSizeUnitChange($event);
    })
  }, _hoisted_10, 32)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontFamily')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontFamilyInput",
    type: "text",
    onKeydown: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.onFontFamilyChange($event);
    }),
    onChange: _cache[9] || (_cache[9] = function ($event) {
      return _ctx.onFontFamilyChange($event);
    })
  }, null, 32)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutHtmlCode')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("<iframe\n      style=\"border: 0; height: 200px; width: 600px;\"\n      src=\"" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.iframeUrl) + "\"\n      ></iframe>", 1)], 512), [[_directive_select_on_focus, {}]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.optOutExplanationIntro)
  }, null, 8, _hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutPreview')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("iframe", {
    id: "previewIframe",
    style: {
      "border": "1px solid #333",
      "height": "200px",
      "width": "600px"
    },
    src: _ctx.iframeUrl,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      withBg: _ctx.withBg
    })
  }, null, 10, _hoisted_13)]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=6dd537f0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=script&lang=ts
/* eslint-disable no-mixed-operators */

/* eslint-disable no-bitwise */



function nearlyWhite(hex) {
  var bigint = parseInt(hex, 16);
  var r = bigint >> 16 & 255;
  var g = bigint >> 8 & 255;
  var b = bigint & 255;
  return r >= 225 && g >= 225 && b >= 225;
}

var _window = window,
    $ = _window.$;
/* harmony default export */ var OptOutCustomizervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    language: {
      type: String,
      required: true
    },
    piwikurl: String
  },
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  data: function data() {
    return {
      fontSizeUnit: 'px',
      backgroundColor: '',
      fontColor: '',
      fontSize: '',
      fontFamily: ''
    };
  },
  created: function created() {
    this.onFontColorChange = Object(external_CoreHome_["debounce"])(this.onFontColorChange, 50);
    this.onBgColorChange = Object(external_CoreHome_["debounce"])(this.onBgColorChange, 50);
    this.onFontSizeChange = Object(external_CoreHome_["debounce"])(this.onFontSizeChange, 50);
    this.onFontSizeUnitChange = Object(external_CoreHome_["debounce"])(this.onFontSizeUnitChange, 50);
    this.onFontFamilyChange = Object(external_CoreHome_["debounce"])(this.onFontFamilyChange, 50);
  },
  methods: {
    onFontColorChange: function onFontColorChange(event) {
      this.fontColor = event.target.value;
    },
    onBgColorChange: function onBgColorChange(event) {
      this.backgroundColor = event.target.value;
    },
    onFontSizeChange: function onFontSizeChange(event) {
      this.fontSize = event.target.value;
    },
    onFontSizeUnitChange: function onFontSizeUnitChange(event) {
      this.fontSizeUnit = event.target.value;
    },
    onFontFamilyChange: function onFontFamilyChange(event) {
      this.fontFamily = event.target.value;
    }
  },
  watch: {
    iframeUrl: function iframeUrl() {
      var pre = this.$refs.pre;
      var isAnimationAlreadyRunning = $(pre).queue('fx').length > 0;

      if (!isAnimationAlreadyRunning) {
        $(pre).effect('highlight', {}, 1500);
      }
    }
  },
  computed: {
    fontSizeWithUnit: function fontSizeWithUnit() {
      if (this.fontSize) {
        return "".concat(this.fontSize).concat(this.fontSizeUnit);
      }

      return '';
    },
    withBg: function withBg() {
      return !!this.piwikurl && this.backgroundColor === '' && this.fontColor !== '' && nearlyWhite(this.fontColor.substr(1));
    },
    iframeUrl: function iframeUrl() {
      if (this.piwikurl) {
        var query = external_CoreHome_["MatomoUrl"].stringify({
          module: 'CoreAdminHome',
          action: 'optOut',
          language: this.language,
          backgroundColor: this.backgroundColor.substr(1),
          fontColor: this.fontColor.substr(1),
          fontSize: this.fontSizeWithUnit,
          fontFamily: this.fontFamily
        });
        return "".concat(this.piwikurl, "index.php?").concat(query);
      }

      return '';
    },
    readThisToLearnMore: function readThisToLearnMore() {
      var link = 'https://matomo.org/faq/how-to/faq_25918/';
      return Object(external_CoreHome_["translate"])('General_ReadThisToLearnMore', "<a rel='noreferrer noopener' target='_blank' href='".concat(link, "'>"), '</a>');
    },
    optOutExplanationIntro: function optOutExplanationIntro() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_OptOutExplanationIntro', "<a href=\"".concat(this.iframeUrl, "\" rel=\"noreferrer noopener\" target=\"_blank\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=custom&index=0&blockType=todo
var OptOutCustomizervue_type_custom_index_0_blockType_todo = __webpack_require__("d004");
var OptOutCustomizervue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(OptOutCustomizervue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue



OptOutCustomizervue_type_script_lang_ts.render = OptOutCustomizervue_type_template_id_6dd537f0_render
/* custom blocks */

if (typeof OptOutCustomizervue_type_custom_index_0_blockType_todo_default.a === 'function') OptOutCustomizervue_type_custom_index_0_blockType_todo_default()(OptOutCustomizervue_type_script_lang_ts)


/* harmony default export */ var OptOutCustomizer = (OptOutCustomizervue_type_script_lang_ts);
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