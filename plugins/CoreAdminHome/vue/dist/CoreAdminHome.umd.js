(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["CoreAdminHome"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["CoreAdminHome"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/CoreAdminHome/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "ArchivingSettings", function() { return /* reexport */ ArchivingSettings; });
__webpack_require__.d(__webpack_exports__, "BrandingSettings", function() { return /* reexport */ BrandingSettings; });
__webpack_require__.d(__webpack_exports__, "SmtpSettings", function() { return /* reexport */ SmtpSettings; });
__webpack_require__.d(__webpack_exports__, "JsTrackingCodeGenerator", function() { return /* reexport */ JsTrackingCodeGenerator; });
__webpack_require__.d(__webpack_exports__, "JsTrackingCodeGeneratorSitesWithoutData", function() { return /* reexport */ JsTrackingCodeGeneratorSitesWithoutData; });
__webpack_require__.d(__webpack_exports__, "ImageTrackingCodeGenerator", function() { return /* reexport */ ImageTrackingCodeGenerator; });
__webpack_require__.d(__webpack_exports__, "TrackingFailures", function() { return /* reexport */ TrackingFailures; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=ed65b600

var _hoisted_1 = {
  class: "form-group row"
};
var _hoisted_2 = {
  class: "col s12"
};
var _hoisted_3 = {
  class: "col s12 m6"
};
var _hoisted_4 = {
  class: "form-description",
  style: {
    "margin-left": "4px"
  }
};
var _hoisted_5 = {
  for: "enableBrowserTriggerArchiving2"
};
var _hoisted_6 = ["innerHTML"];
var _hoisted_7 = {
  class: "col s12 m6"
};
var _hoisted_8 = ["innerHTML"];
var _hoisted_9 = {
  class: "form-group row"
};
var _hoisted_10 = {
  class: "col s12"
};
var _hoisted_11 = {
  class: "input-field col s12 m6"
};
var _hoisted_12 = ["disabled"];
var _hoisted_13 = {
  class: "form-description"
};
var _hoisted_14 = {
  class: "col s12 m6"
};
var _hoisted_15 = {
  key: 0,
  class: "form-help"
};
var _hoisted_16 = {
  key: 0
};

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ArchivingSettings'),
    anchor: "archivingSettings",
    class: "matomo-archiving-settings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_AllowPiwikArchivingToTriggerBrowser')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "radio",
        id: "enableBrowserTriggerArchiving1",
        name: "enableBrowserTriggerArchiving",
        value: "1",
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.enableBrowserTriggerArchivingValue = $event;
        })
      }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.enableBrowserTriggerArchivingValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "radio",
        id: "enableBrowserTriggerArchiving2",
        name: "enableBrowserTriggerArchiving",
        value: "0",
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.enableBrowserTriggerArchivingValue = $event;
        })
      }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.enableBrowserTriggerArchivingValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "form-description",
        innerHTML: _ctx.$sanitize(_ctx.archivingTriggerDesc),
        style: {
          "margin-left": "4px"
        }
      }, null, 8, _hoisted_6)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "form-help",
        innerHTML: _ctx.$sanitize(_ctx.archivingInlineHelp)
      }, null, 8, _hoisted_8)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ReportsContainingTodayWillBeProcessedAtMostEvery')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "text",
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          return _ctx.todayArchiveTimeToLiveValue = $event;
        }),
        id: "todayArchiveTimeToLive",
        disabled: !_ctx.isGeneralSettingsAdminEnabled
      }, null, 8, _hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.todayArchiveTimeToLiveValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RearchiveTimeIntervalOnlyForTodayReports')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [_ctx.isGeneralSettingsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [_ctx.showWarningCron ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NewReportsWillBeProcessedByCron')), 1), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ReportsWillBeProcessedAtMostEveryHour')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_IfArchivingIsFastYouCanSetupCronRunMoreOften')), 1), _hoisted_18])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_SmallTrafficYouCanLeaveDefault', _ctx.todayArchiveTimeToLiveDefault)) + " ", 1), _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MediumToHighTrafficItIsRecommendedTo', 1800, 3600)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        saving: _ctx.isLoading,
        onConfirm: _cache[3] || (_cache[3] = function ($event) {
          return _ctx.save();
        })
      }, null, 8, ["saving"])])])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=ed65b600

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var ArchivingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    enableBrowserTriggerArchiving: Boolean,
    showSegmentArchiveTriggerInfo: Boolean,
    isGeneralSettingsAdminEnabled: Boolean,
    showWarningCron: Boolean,
    todayArchiveTimeToLive: Number,
    todayArchiveTimeToLiveDefault: Number
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data: function data() {
    return {
      isLoading: false,
      enableBrowserTriggerArchivingValue: this.enableBrowserTriggerArchiving ? 1 : 0,
      todayArchiveTimeToLiveValue: this.todayArchiveTimeToLive
    };
  },
  watch: {
    enableBrowserTriggerArchiving: function enableBrowserTriggerArchiving(newValue) {
      this.enableBrowserTriggerArchivingValue = newValue ? 1 : 0;
    },
    todayArchiveTimeToLive: function todayArchiveTimeToLive(newValue) {
      this.todayArchiveTimeToLiveValue = newValue;
    }
  },
  computed: {
    archivingTriggerDesc: function archivingTriggerDesc() {
      var result = '';
      result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerDescription', '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/docs/setup-auto-archiving/">', '</a>');

      if (this.showSegmentArchiveTriggerInfo) {
        result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerSegment');
      }

      return result;
    },
    archivingInlineHelp: function archivingInlineHelp() {
      var result = Object(external_CoreHome_["translate"])('General_ArchivingInlineHelp');
      result += '<br/>';
      result += Object(external_CoreHome_["translate"])('General_SeeTheOfficialDocumentationForMoreInformation', '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/docs/setup-auto-archiving/">', '</a>');
      return result;
    }
  },
  methods: {
    save: function save() {
      var _this = this;

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setArchiveSettings'
      }, {
        enableBrowserTriggerArchiving: this.enableBrowserTriggerArchivingValue,
        todayArchiveTimeToLive: this.todayArchiveTimeToLiveValue
      }).then(function () {
        _this.isLoading = false;
        var notificationId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationId);
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue



ArchivingSettingsvue_type_script_lang_ts.render = render

/* harmony default export */ var ArchivingSettings = (ArchivingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=1802cbc6

var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_1 = {
  id: "logoSettings"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_2 = {
  id: "logoUploadForm",
  ref: "logoUploadForm",
  method: "post",
  enctype: "multipart/form-data",
  action: "index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_3 = {
  key: 0
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_4 = ["value"];

var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);

var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_6 = {
  key: 0
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_7 = {
  key: 0,
  class: "alert alert-warning uploaderror"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_8 = {
  class: "row"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_9 = {
  class: "col s12"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_10 = ["src"];
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_11 = {
  class: "row"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_12 = {
  class: "col s12"
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_13 = ["src"];
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_14 = {
  key: 1
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_15 = ["innerHTML"];
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_16 = {
  key: 1
};
var BrandingSettingsvue_type_template_id_1802cbc6_hoisted_17 = {
  class: "alert alert-warning"
};
function BrandingSettingsvue_type_template_id_1802cbc6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_BrandingSettings'),
    anchor: "brandingSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_CustomLogoHelpText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        name: "useCustomLogo",
        uicontrol: "checkbox",
        "model-value": _ctx.enabled,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.onUseCustomLogoChange($event);
        }),
        title: _ctx.translate('CoreAdminHome_UseCustomLogo'),
        "inline-help": _ctx.help
      }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_2, [_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "hidden",
        name: "token_auth",
        value: _ctx.tokenAuth
      }, null, 8, BrandingSettingsvue_type_template_id_1802cbc6_hoisted_4), BrandingSettingsvue_type_template_id_1802cbc6_hoisted_5, _ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
        name: "fade-out"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [_ctx.showUploadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LogoUploadFailed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
        }),
        _: 1
      }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "file",
        name: "customLogo",
        "model-value": _ctx.customLogo,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.onCustomLogoChange($event);
        }),
        title: _ctx.translate('CoreAdminHome_LogoUpload'),
        "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '110')
      }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
        src: _ctx.pathUserLogoWithBuster,
        id: "currentLogo",
        style: {
          "max-height": "150px"
        },
        ref: "currentLogo"
      }, null, 8, BrandingSettingsvue_type_template_id_1802cbc6_hoisted_10)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "file",
        name: "customFavicon",
        "model-value": _ctx.customFavicon,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          return _ctx.onFaviconChange($event);
        }),
        title: _ctx.translate('CoreAdminHome_FaviconUpload'),
        "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '16')
      }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
        src: _ctx.pathUserFaviconWithBuster,
        id: "currentFavicon",
        width: "16",
        height: "16",
        ref: "currentFavicon"
      }, null, 8, BrandingSettingsvue_type_template_id_1802cbc6_hoisted_13)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "alert alert-warning",
        innerHTML: _ctx.$sanitize(_ctx.logosNotWriteableWarning)
      }, null, 8, BrandingSettingsvue_type_template_id_1802cbc6_hoisted_15)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_1802cbc6_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_FileUploadDisabled', "file_uploads=1")), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[3] || (_cache[3] = function ($event) {
          return _ctx.save();
        }),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])], 512), [[_directive_form]])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=1802cbc6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts



var _window = window,
    BrandingSettingsvue_type_script_lang_ts_$ = _window.$;
/* harmony default export */ var BrandingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    fileUploadEnabled: {
      type: Boolean,
      required: true
    },
    logosWriteable: {
      type: Boolean,
      required: true
    },
    useCustomLogo: {
      type: Boolean,
      required: true
    },
    pathUserLogoDirectory: {
      type: String,
      required: true
    },
    pathUserLogo: {
      type: String,
      required: true
    },
    pathUserLogoSmall: {
      type: String,
      required: true
    },
    pathUserLogoSvg: {
      type: String,
      required: true
    },
    hasUserLogo: {
      type: Boolean,
      required: true
    },
    pathUserFavicon: {
      type: String,
      required: true
    },
    hasUserFavicon: {
      type: Boolean,
      required: true
    },
    isPluginsAdminEnabled: {
      type: Boolean,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: this.useCustomLogo,
      customLogo: this.pathUserLogo,
      customFavicon: this.pathUserFavicon,
      showUploadError: false,
      currentLogoSrcExists: this.hasUserLogo,
      currentFaviconSrcExists: this.hasUserFavicon,
      currentLogoCacheBuster: new Date().getTime(),
      currentFaviconCacheBuster: new Date().getTime()
    };
  },
  computed: {
    tokenAuth: function tokenAuth() {
      return external_CoreHome_["Matomo"].token_auth;
    },
    logosNotWriteableWarning: function logosNotWriteableWarning() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_LogoNotWriteableInstruction', "<code>".concat(this.pathUserLogoDirectory, "</code><br/>"), "".concat(this.pathUserLogo, ", ").concat(this.pathUserLogoSmall, ", ").concat(this.pathUserLogoSvg));
    },
    help: function help() {
      if (!this.isPluginsAdminEnabled) {
        return undefined;
      }

      var giveUsFeedbackText = "\"".concat(Object(external_CoreHome_["translate"])('General_GiveUsYourFeedback'), "\"");
      var linkStart = '<a href="?module=CorePluginsAdmin&action=plugins" ' + 'rel="noreferrer noopener" target="_blank">';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_CustomLogoFeedbackInfo', giveUsFeedbackText, linkStart, '</a>');
    },
    pathUserLogoWithBuster: function pathUserLogoWithBuster() {
      if (this.currentLogoSrcExists && this.pathUserLogo) {
        return "".concat(this.pathUserLogo, "?").concat(this.currentLogoCacheBuster);
      }

      return '';
    },
    pathUserFaviconWithBuster: function pathUserFaviconWithBuster() {
      if (this.currentFaviconSrcExists && this.pathUserFavicon) {
        return "".concat(this.pathUserFavicon, "?").concat(this.currentFaviconCacheBuster);
      }

      return '';
    }
  },
  methods: {
    onUseCustomLogoChange: function onUseCustomLogoChange(newValue) {
      this.enabled = newValue;
    },
    onCustomLogoChange: function onCustomLogoChange(newValue) {
      this.customLogo = newValue;
      this.updateLogo();
    },
    onFaviconChange: function onFaviconChange(newValue) {
      this.customFavicon = newValue;
      this.updateLogo();
    },
    save: function save() {
      var _this = this;

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setBrandingSettings'
      }, {
        useCustomLogo: this.enabled ? '1' : '0'
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isLoading = false;
      });
    },
    updateLogo: function updateLogo() {
      var _this2 = this;

      var isSubmittingLogo = !!this.customLogo;
      var isSubmittingFavicon = !!this.customFavicon;

      if (!isSubmittingLogo && !isSubmittingFavicon) {
        return;
      }

      this.showUploadError = false;
      var frameName = "upload".concat(new Date().getTime());
      var uploadFrame = BrandingSettingsvue_type_script_lang_ts_$("<iframe name=\"".concat(frameName, "\" />"));
      uploadFrame.css('display', 'none');
      uploadFrame.on('load', function () {
        setTimeout(function () {
          var frameContent = (BrandingSettingsvue_type_script_lang_ts_$(uploadFrame.contents()).find('body').html() || '').trim();

          if (frameContent === '0') {
            _this2.showUploadError = true;
          } else {
            // Upload succeed, so we update the images availability
            // according to what have been uploaded
            if (isSubmittingLogo) {
              _this2.currentLogoSrcExists = true;
              _this2.currentLogoCacheBuster = new Date().getTime(); // force re-fetch
            }

            if (isSubmittingFavicon) {
              _this2.currentFaviconSrcExists = true;
              _this2.currentFaviconCacheBuster = new Date().getTime(); // force re-fetch
            }
          }

          if (frameContent === '1' || frameContent === '0') {
            uploadFrame.remove();
          }
        }, 1000);
      });
      BrandingSettingsvue_type_script_lang_ts_$('body:first').append(uploadFrame);
      var submittingForm = BrandingSettingsvue_type_script_lang_ts_$(this.$refs.logoUploadForm);
      submittingForm.attr('target', frameName);
      submittingForm.submit();
      this.customLogo = '';
      this.customFavicon = '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue



BrandingSettingsvue_type_script_lang_ts.render = BrandingSettingsvue_type_template_id_1802cbc6_render

/* harmony default export */ var BrandingSettings = (BrandingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=14e9a186

var SmtpSettingsvue_type_template_id_14e9a186_hoisted_1 = {
  id: "smtpSettings"
};
function SmtpSettingsvue_type_template_id_14e9a186_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_EmailServerSettings'),
    anchor: "mailSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "mailUseSmtp",
        modelValue: _ctx.enabled,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.enabled = $event;
        }),
        title: _ctx.translate('General_UseSMTPServerForEmail'),
        "inline-help": _ctx.translate('General_SelectYesIfYouWantToSendEmailsViaServer')
      }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SmtpSettingsvue_type_template_id_14e9a186_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "mailHost",
        "model-value": _ctx.mailHost,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.onUpdateMailHost($event);
        }),
        title: _ctx.translate('General_SmtpServerAddress')
      }, null, 8, ["model-value", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "mailPort",
        modelValue: _ctx.mailPort,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          return _ctx.mailPort = $event;
        }),
        title: _ctx.translate('General_SmtpPort'),
        "inline-help": _ctx.translate('General_OptionalSmtpPort')
      }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "mailType",
        modelValue: _ctx.mailType,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          return _ctx.mailType = $event;
        }),
        title: _ctx.translate('General_AuthenticationMethodSmtp'),
        options: _ctx.mailTypes,
        "inline-help": _ctx.translate('General_OnlyUsedIfUserPwdIsSet')
      }, null, 8, ["modelValue", "title", "options", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "mailUsername",
        modelValue: _ctx.mailUsername,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          return _ctx.mailUsername = $event;
        }),
        title: _ctx.translate('General_SmtpUsername'),
        "inline-help": _ctx.translate('General_OnlyEnterIfRequired'),
        autocomplete: 'off'
      }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "password",
        name: "mailPassword",
        "model-value": _ctx.mailPassword,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
          return _ctx.onMailPasswordChange($event);
        }),
        onClick: _cache[6] || (_cache[6] = function ($event) {
          !_ctx.passwordChanged && $event.target.select();
        }),
        title: _ctx.translate('General_SmtpPassword'),
        "inline-help": _ctx.passwordHelp,
        autocomplete: 'off'
      }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "mailFromAddress",
        modelValue: _ctx.mailFromAddress,
        "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
          return _ctx.mailFromAddress = $event;
        }),
        title: _ctx.translate('General_SmtpFromAddress'),
        "inline-help": _ctx.translate('General_SmtpFromEmailHelp', _ctx.mailHost),
        autocomplete: 'off'
      }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "mailFromName",
        modelValue: _ctx.mailFromName,
        "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
          return _ctx.mailFromName = $event;
        }),
        title: _ctx.translate('General_SmtpFromName'),
        "inline-help": _ctx.translate('General_NameShownInTheSenderColumn'),
        autocomplete: 'off'
      }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "mailEncryption",
        modelValue: _ctx.mailEncryption,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
          return _ctx.mailEncryption = $event;
        }),
        title: _ctx.translate('General_SmtpEncryption'),
        options: _ctx.mailEncryptions,
        "inline-help": _ctx.translate('General_EncryptedSmtpTransport')
      }, null, 8, ["modelValue", "title", "options", "inline-help"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[10] || (_cache[10] = function ($event) {
          return _ctx.save();
        }),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])], 512), [[_directive_form]])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=14e9a186

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var SmtpSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    mail: {
      type: Object,
      required: true
    },
    mailTypes: {
      type: Object,
      required: true
    },
    mailEncryptions: {
      type: Object,
      required: true
    }
  },
  data: function data() {
    var mail = this.mail;
    return {
      isLoading: false,
      enabled: mail.transport === 'smtp',
      mailHost: mail.host,
      passwordChanged: false,
      mailPort: mail.port,
      mailType: mail.type,
      mailUsername: mail.username,
      mailPassword: mail.password ? '******' : '',
      mailFromAddress: mail.noreply_email_address,
      mailFromName: mail.noreply_email_name,
      mailEncryption: mail.encryption
    };
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  computed: {
    passwordHelp: function passwordHelp() {
      var part1 = "".concat(Object(external_CoreHome_["translate"])('General_OnlyEnterIfRequiredPassword'), "<br/>");
      var part2 = "".concat(Object(external_CoreHome_["translate"])('General_WarningPasswordStored', '<strong>', '</strong>'), "<br/>");
      return "".concat(part1, "\n").concat(part2);
    }
  },
  methods: {
    onUpdateMailHost: function onUpdateMailHost(newValue) {
      this.mailHost = newValue;

      if (this.passwordChanged) {
        return;
      }

      this.mailPassword = '';
      this.passwordChanged = true;
    },
    onMailPasswordChange: function onMailPasswordChange(newValue) {
      this.mailPassword = newValue;
      this.passwordChanged = true;
    },
    save: function save() {
      var _this = this;

      this.isLoading = true;
      var mailSettings = {
        mailUseSmtp: this.enabled ? '1' : '0',
        mailPort: this.mailPort,
        mailHost: this.mailHost,
        mailType: this.mailType,
        mailUsername: this.mailUsername,
        mailFromAddress: this.mailFromAddress,
        mailFromName: this.mailFromName,
        mailEncryption: this.mailEncryption
      };

      if (this.passwordChanged) {
        mailSettings.mailPassword = this.mailPassword;
      }

      external_CoreHome_["AjaxHelper"].post({
        module: 'CoreAdminHome',
        action: 'setMailSettings'
      }, mailSettings, {
        withTokenInUrl: true
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue



SmtpSettingsvue_type_script_lang_ts.render = SmtpSettingsvue_type_template_id_14e9a186_render

/* harmony default export */ var SmtpSettings = (SmtpSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=template&id=a13a8c0c

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_1 = {
  id: "js-code-options"
};

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_4 = ["innerHTML"];
var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_5 = ["innerHTML"];

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_8 = ["innerHTML"];

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_11 = ["innerHTML"];

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/",
  target: "_blank",
  rel: "noopener"
}, "WordPress", -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-integrate-matomo-with-squarespace-website/",
  target: "_blank",
  rel: "noopener"
}, "Squarespace", -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-wix/",
  target: "_blank",
  rel: "noopener"
}, "Wix", -1);

var JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/how-to-install/faq_19424/",
  target: "_blank",
  rel: "noopener"
}, "SharePoint", -1);

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-joomla/",
  target: "_blank",
  rel: "noopener"
}, "Joomla", -1);

var _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-shopify-store/",
  target: "_blank",
  rel: "noopener"
}, "Shopify", -1);

var _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ");

var _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager/",
  target: "_blank",
  rel: "noopener"
}, "Google Tag Manager", -1);

var _hoisted_27 = {
  id: "javascript-output-section"
};
var _hoisted_28 = {
  class: "valign-wrapper trackingHelpHeader matchWidth"
};
var _hoisted_29 = {
  id: "javascript-email-button"
};
var _hoisted_30 = {
  id: "javascript-text"
};
var _hoisted_31 = ["textContent"];
function JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_JsTrackingCodeAdvancedOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackingCodeAdvancedOptions");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    anchor: "javaScriptTracking",
    "content-title": _ctx.translate('CoreAdminHome_JavaScriptTracking')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTrackingIntro1')) + " ", 1), JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_2, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTrackingIntro2')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro3a)
      }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(' ' + _ctx.jsTrackingIntro3b)
      }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_5), JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_6, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro4a)
      }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_8), JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_9, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro5)
      }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_11), JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_12, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_InstallationGuides')) + " : ", 1), JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_14, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_15, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_16, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_17, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_18, JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_hoisted_19, _hoisted_20, _hoisted_21, _hoisted_22, _hoisted_23, _hoisted_24, _hoisted_25, _hoisted_26]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "site",
        name: "js-tracker-website",
        class: "jsTrackingCodeWebsite",
        modelValue: _ctx.site,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.site = $event;
        }),
        ref: "site",
        introduction: _ctx.translate('General_Website')
      }, null, 8, ["modelValue", "introduction"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_JsTrackingTag')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', "</head>")), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        class: "btn",
        id: "emailJsBtn",
        onClick: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.sendEmail();
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_EmailInstructionsButton')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
        class: "codeblock",
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
        ref: "trackingCode"
      }, null, 8, _hoisted_31), [[_directive_copy_to_clipboard, {}]])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_JsTrackingCodeAdvancedOptions, {
        site: _ctx.site,
        "max-custom-variables": _ctx.maxCustomVariables,
        "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
        onUpdateTrackingCode: _ctx.updateTrackingCode,
        ref: "jsTrackingCodeAdvanceOption"
      }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=template&id=a13a8c0c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=template&id=b2286754

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_1 = {
  class: "trackingCodeAdvancedOptions"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_2 = {
  class: "advance-option"
};

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down"
}, null, -1);

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-up"
}, null, -1);

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_5 = {
  id: "javascript-advanced-options"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_6 = ["innerHTML"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_7 = {
  id: "optional-js-tracking-options"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_8 = {
  id: "jsTrackAllSubdomainsInlineHelp",
  class: "inline-help-node"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_9 = ["innerHTML"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_10 = ["innerHTML"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_11 = {
  id: "jsTrackGroupByDomainInlineHelp",
  class: "inline-help-node"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_12 = {
  id: "jsTrackAllAliasesInlineHelp",
  class: "inline-help-node"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_13 = {
  id: "javascript-tracking-visitor-cv"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_14 = {
  class: "row"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_15 = {
  class: "col s12 m3"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_16 = {
  class: "col s12 m3"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_17 = {
  class: "col s12 m6 l3"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_18 = ["onKeydown"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_19 = {
  class: "col s12 m6 l3"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_20 = ["onKeydown"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_21 = {
  class: "row"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_22 = {
  class: "col s12"
};

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_24 = {
  id: "jsCrossDomain",
  class: "inline-help-node"
};

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_26 = {
  id: "jsDoNotTrackInlineHelp",
  class: "inline-help-node"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_27 = {
  key: 0
};

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_29 = ["innerHTML"];
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_30 = {
  id: "js-campaign-query-param-extra"
};
var JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_31 = {
  class: "row"
};
var _hoisted_32 = {
  class: "col s12"
};
var _hoisted_33 = {
  class: "row"
};
var _hoisted_34 = {
  class: "col s12"
};
function JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [!_ctx.showAdvanced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    href: "javascript:;",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showAdvanced = true;
    }, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ShowAdvancedOptions')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_3])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showAdvanced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    href: "javascript:;",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showAdvanced = false;
    }, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_HideAdvancedOptions')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.trackingDocumentationHelp)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.mergeSubdomainsDesc)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_10)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-all-subdomains",
    "model-value": _ctx.trackAllSubdomains,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      _ctx.trackAllSubdomains = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: "".concat(_ctx.translate('CoreAdminHome_JSTracking_MergeSubdomains'), " ").concat(_ctx.currentSiteName),
    "inline-help": "#jsTrackAllSubdomainsInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomainDesc1', _ctx.currentSiteHost)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-group-by-domain",
    "model-value": _ctx.groupByDomain,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      _ctx.groupByDomain = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomain'),
    "inline-help": "#jsTrackGroupByDomainInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_MergeAliasesDesc', _ctx.currentSiteAlias)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-all-aliases",
    "model-value": _ctx.trackAllAliases,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      _ctx.trackAllAliases = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: "".concat(_ctx.translate('CoreAdminHome_JSTracking_MergeAliases'), " ").concat(_ctx.currentSiteName),
    "inline-help": "#jsTrackAllAliasesInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-noscript",
    "model-value": _ctx.trackNoScript,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      _ctx.trackNoScript = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_TrackNoScript')
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-visitor-cv-check",
    "model-value": _ctx.trackCustomVars,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      _ctx.trackCustomVars = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_VisitorCustomVars'),
    "inline-help": _ctx.translate('CoreAdminHome_JSTracking_VisitorCustomVarsDesc')
  }, null, 8, ["model-value", "disabled", "title", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.maxCustomVariables > 0]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Value')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.customVars, function (customVar, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "text",
      class: "custom-variable-name",
      onKeydown: function onKeydown($event) {
        return _ctx.onCustomVarNameKeydown($event, index);
      },
      placeholder: "e.g. Type"
    }, null, 40, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_18)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "text",
      class: "custom-variable-value",
      onKeydown: function onKeydown($event) {
        return _ctx.onCustomVarValueKeydown($event, index);
      },
      placeholder: "e.g. Customer"
    }, null, 40, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_20)])]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "javascript:;",
    onClick: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.addCustomVar();
    }),
    class: "add-custom-variable"
  }, [JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Add')), 1)])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.canAddMoreCustomVariables]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.trackCustomVars]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CrossDomain')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CrossDomain_NeedsMultipleDomains')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-cross-domain",
    "model-value": _ctx.crossDomain,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
      _ctx.crossDomain = $event;

      _ctx.updateTrackingCode();

      _ctx.onCrossDomainToggle();
    }),
    disabled: _ctx.isLoading || !_ctx.hasManySiteUrls,
    title: _ctx.translate('CoreAdminHome_JSTracking_EnableCrossDomainLinking'),
    "inline-help": "#jsCrossDomain"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_EnableDoNotTrackDesc')) + " ", 1), _ctx.serverSideDoNotTrackEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_27, [JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_EnableDoNotTrack_AlreadyEnabled')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-do-not-track",
    "model-value": _ctx.doNotTrack,
    "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
      _ctx.doNotTrack = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_EnableDoNotTrack'),
    "inline-help": "#jsDoNotTrackInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-disable-cookies",
    "model-value": _ctx.disableCookies,
    "onUpdate:modelValue": _cache[10] || (_cache[10] = function ($event) {
      _ctx.disableCookies = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_DisableCookies'),
    "inline-help": _ctx.translate('CoreAdminHome_JSTracking_DisableCookiesDesc')
  }, null, 8, ["model-value", "disabled", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    id: "jsTrackCampaignParamsInlineHelp",
    class: "inline-help-node",
    innerHTML: _ctx.$sanitize(_ctx.jsTrackCampaignParamsInlineHelp)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_29), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "custom-campaign-query-params-check",
    "model-value": _ctx.useCustomCampaignParams,
    "onUpdate:modelValue": _cache[11] || (_cache[11] = function ($event) {
      _ctx.useCustomCampaignParams = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CustomCampaignQueryParam'),
    "inline-help": "#jsTrackCampaignParamsInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "custom-campaign-name-query-param",
    "model-value": _ctx.customCampaignName,
    "onUpdate:modelValue": _cache[12] || (_cache[12] = function ($event) {
      _ctx.customCampaignName = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignNameParam')
  }, null, 8, ["model-value", "disabled", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "custom-campaign-keyword-query-param",
    "model-value": _ctx.customCampaignKeyword,
    "onUpdate:modelValue": _cache[13] || (_cache[13] = function ($event) {
      _ctx.customCampaignKeyword = $event;

      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignKwdParam')
  }, null, 8, ["model-value", "disabled", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.useCustomCampaignParams]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showAdvanced]])]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=template&id=b2286754

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=script&lang=ts




function getHostNameFromUrl(url) {
  var urlObj = new URL(url);
  return urlObj.hostname;
}

function getCustomVarArray(cvars) {
  return cvars.filter(function (cv) {
    return !!cv.name;
  }).map(function (cv) {
    return [cv.name, cv.value];
  });
}

var piwikHost = window.location.host;
var piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
/* harmony default export */ var JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    site: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean
  },
  data: function data() {
    return {
      showAdvanced: false,
      trackAllSubdomains: false,
      isLoading: false,
      siteUrls: {},
      siteExcludedQueryParams: {},
      siteExcludedReferrers: {},
      crossDomain: false,
      groupByDomain: false,
      trackAllAliases: false,
      trackNoScript: false,
      trackCustomVars: false,
      customVars: [],
      canAddMoreCustomVariables: !!this.maxCustomVariables && this.maxCustomVariables > 0,
      doNotTrack: false,
      disableCookies: false,
      useCustomCampaignParams: false,
      customCampaignName: '',
      customCampaignKeyword: '',
      trackingCodeAbortController: null
    };
  },
  emits: ['updateTrackingCode'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  created: function created() {
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }

    this.onCustomVarNameKeydown = Object(external_CoreHome_["debounce"])(this.onCustomVarNameKeydown, 100);
    this.onCustomVarValueKeydown = Object(external_CoreHome_["debounce"])(this.onCustomVarValueKeydown, 100);
    this.addCustomVar();
  },
  watch: {
    site: function site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    onSiteChanged: function onSiteChanged(newValue) {
      var _this = this;

      var idSite = newValue.id;
      var promises = [];

      if (!this.siteUrls[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getSiteUrlsFromId',
          idSite: idSite,
          filter_limit: '-1'
        }).then(function (data) {
          _this.siteUrls[idSite] = data || [];
        }));
      }

      if (!this.siteExcludedQueryParams[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'Overlay.getExcludedQueryParameters',
          idSite: idSite,
          filter_limit: '-1'
        }).then(function (data) {
          _this.siteExcludedQueryParams[idSite] = data || [];
        }));
      }

      if (!this.siteExcludedReferrers[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getExcludedReferrers',
          idSite: idSite,
          filter_limit: '-1'
        }).then(function (data) {
          _this.siteExcludedReferrers[idSite] = [];
          Object.values(data || []).forEach(function (referrer) {
            _this.siteExcludedReferrers[idSite].push(referrer.replace(/^https?:\/\//, ''));
          });
        }));
      }

      Promise.all(promises).then(function () {
        // eslint-disable-next-line
        var refs = _this.$refs.jsTrackingCodeAdvanceOption;
        _this.isLoading = false;

        _this.updateCurrentSiteInfo();

        _this.updateTrackingCode();
      });
    },
    updateCurrentSiteInfo: function updateCurrentSiteInfo() {
      if (!this.hasManySiteUrls) {
        // we make sure to disable cross domain if it has only one url or less
        this.crossDomain = false;
      }
    },
    onCrossDomainToggle: function onCrossDomainToggle() {
      if (this.crossDomain) {
        this.trackAllAliases = true;
      }
    },
    updateTrackingCode: function updateTrackingCode() {
      var _this2 = this;

      // get params used to generate JS code
      var params = {
        piwikUrl: "".concat(piwikHost).concat(piwikPath),
        groupPageTitlesByDomain: this.groupByDomain ? 1 : 0,
        mergeSubdomains: this.trackAllSubdomains ? 1 : 0,
        mergeAliasUrls: this.trackAllAliases ? 1 : 0,
        visitorCustomVariables: this.trackCustomVars ? getCustomVarArray(this.customVars) : 0,
        customCampaignNameQueryParam: null,
        customCampaignKeywordParam: null,
        doNotTrack: this.doNotTrack ? 1 : 0,
        disableCookies: this.disableCookies ? 1 : 0,
        crossDomain: this.crossDomain ? 1 : 0,
        trackNoScript: this.trackNoScript ? 1 : 0,
        forceMatomoEndpoint: 1
      };

      if (this.siteExcludedQueryParams[this.site.id]) {
        params.excludedQueryParams = this.siteExcludedQueryParams[this.site.id];
      }

      if (this.siteExcludedReferrers[this.site.id]) {
        params.excludedReferrers = this.siteExcludedReferrers[this.site.id];
      }

      if (this.useCustomCampaignParams) {
        params.customCampaignNameQueryParam = this.customCampaignName;
        params.customCampaignKeywordParam = this.customCampaignKeyword;
      }

      if (this.trackingCodeAbortController) {
        this.trackingCodeAbortController.abort();
        this.trackingCodeAbortController = null;
      }

      this.trackingCodeAbortController = new AbortController();
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        format: 'json',
        method: 'SitesManager.getJavascriptTag',
        idSite: this.site.id
      }, params, {
        abortController: this.trackingCodeAbortController
      }).then(function (response) {
        _this2.trackingCodeAbortController = null;

        _this2.$emit('updateTrackingCode', response.value);
      });
    },
    addCustomVar: function addCustomVar() {
      if (this.canAddMoreCustomVariables) {
        this.customVars.push({
          name: '',
          value: ''
        });
      }

      this.canAddMoreCustomVariables = !!this.maxCustomVariables && this.maxCustomVariables > this.customVars.length;
    },
    onCustomVarNameKeydown: function onCustomVarNameKeydown(event, index) {
      var _this3 = this;

      setTimeout(function () {
        _this3.customVars[index].name = event.target.value;

        _this3.updateTrackingCode();
      });
    },
    onCustomVarValueKeydown: function onCustomVarValueKeydown(event, index) {
      var _this4 = this;

      setTimeout(function () {
        _this4.customVars[index].value = event.target.value;

        _this4.updateTrackingCode();
      });
    }
  },
  computed: {
    hasManySiteUrls: function hasManySiteUrls() {
      var site = this.site;
      return this.siteUrls[site.id] && this.siteUrls[site.id].length > 1;
    },
    currentSiteHost: function currentSiteHost() {
      var _this$siteUrls$this$s;

      var siteUrl = (_this$siteUrls$this$s = this.siteUrls[this.site.id]) === null || _this$siteUrls$this$s === void 0 ? void 0 : _this$siteUrls$this$s[0];

      if (!siteUrl) {
        return '';
      }

      return getHostNameFromUrl(siteUrl);
    },
    currentSiteAlias: function currentSiteAlias() {
      var _this$siteUrls$this$s2;

      var defaultAliasUrl = "x.".concat(this.currentSiteHost);
      var alias = (_this$siteUrls$this$s2 = this.siteUrls[this.site.id]) === null || _this$siteUrls$this$s2 === void 0 ? void 0 : _this$siteUrls$this$s2[1];
      return alias || defaultAliasUrl;
    },
    currentSiteName: function currentSiteName() {
      return external_CoreHome_["Matomo"].helper.htmlEntities(this.site.name);
    },
    mergeSubdomainsDesc: function mergeSubdomainsDesc() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_MergeSubdomainsDesc', "x.".concat(this.currentSiteHost), "y.".concat(this.currentSiteHost));
    },
    learnMoreText: function learnMoreText() {
      var subdomainsLink = 'https://developer.matomo.org/guides/tracking-javascript-guide' + '#measuring-domains-andor-sub-domains';
      return Object(external_CoreHome_["translate"])('General_LearnMore', " (<a href=\"".concat(subdomainsLink, "\" rel=\"noreferrer noopener\" target=\"_blank\">"), '</a>)');
    },
    jsTrackCampaignParamsInlineHelp: function jsTrackCampaignParamsInlineHelp() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CustomCampaignQueryParamDesc', '<a href="https://matomo.org/faq/general/faq_119" rel="noreferrer noopener" target="_blank">', '</a>');
    },
    trackingDocumentationHelp: function trackingDocumentationHelp() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingDocumentationHelp', '<a rel="noreferrer noopener" target="_blank" href="https://developer.matomo.org/guides/tracking-javascript-guide">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue



JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts.render = JsTrackingCodeAdvancedOptionsvue_type_template_id_b2286754_render

/* harmony default export */ var JsTrackingCodeAdvancedOptions = (JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=script&lang=ts




/* harmony default export */ var JsTrackingCodeGeneratorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean
  },
  data: function data() {
    return {
      site: this.defaultSite,
      trackingCode: '',
      isHighlighting: false,
      consentManagerName: '',
      consentManagerUrl: '',
      consentManagerIsConnected: false
    };
  },
  components: {
    JsTrackingCodeAdvancedOptions: JsTrackingCodeAdvancedOptions,
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  created: function created() {
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site: function site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    updateTrackingCode: function updateTrackingCode(code) {
      var _this = this;

      this.trackingCode = code;
      var jsCodeTextarea = $(this.$refs.trackingCode);

      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: function complete() {
            _this.isHighlighting = false;
          }
        }, 1500);
      }
    },
    onSiteChanged: function onSiteChanged(newValue) {
      var _this2 = this;

      var idSite = newValue.id;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'API',
        format: 'json',
        method: 'Tour.detectConsentManager',
        idSite: idSite,
        filter_limit: '-1'
      }).then(function (response) {
        if (Object.prototype.hasOwnProperty.call(response, 'name')) {
          _this2.consentManagerName = response.name;
        }

        if (Object.prototype.hasOwnProperty.call(response, 'url')) {
          _this2.consentManagerUrl = response.url;
        }

        _this2.consentManagerIsConnected = response.isConnected;
      });
    },
    sendEmail: function sendEmail() {
      var subjectLine = Object(external_CoreHome_["translate"])('SitesManager_EmailInstructionsSubject');
      subjectLine = encodeURIComponent(subjectLine);
      var trackingCode = this.trackingCode;
      trackingCode = trackingCode.replace(/<[^>]+>/g, '');
      var bodyText = "".concat(Object(external_CoreHome_["translate"])('SitesManager_JsTrackingTagHelp'), ". ").concat(Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail', '\'head'), "\n").concat(trackingCode);

      if (this.consentManagerName !== '' && this.consentManagerUrl !== '') {
        bodyText += Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_ConsentManagerDetected', this.consentManagerName, this.consentManagerUrl);

        if (this.consentManagerIsConnected) {
          bodyText += "\n".concat(Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_ConsentManagerConnected', this.consentManagerName));
        }
      }

      bodyText = encodeURIComponent(bodyText);
      var linkText = "mailto:?subject=".concat(subjectLine, "&body=").concat(bodyText);
      window.location.href = linkText;
    }
  },
  computed: {
    jsTrackingIntro3a: function jsTrackingIntro3a() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro3a', '<a href="https://matomo.org/integrate/" rel="noreferrer noopener" target="_blank">', '</a>');
    },
    jsTrackingIntro3b: function jsTrackingIntro3b() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro3b');
    },
    jsTrackingIntro4a: function jsTrackingIntro4a() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro4', '<a href="#image-tracking-link">', '</a>');
    },
    jsTrackingIntro5: function jsTrackingIntro5() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro5', '<a rel="noreferrer noopener" target="_blank" ' + 'href="https://developer.matomo.org/guides/tracking-javascript-guide">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue



JsTrackingCodeGeneratorvue_type_script_lang_ts.render = JsTrackingCodeGeneratorvue_type_template_id_a13a8c0c_render

/* harmony default export */ var JsTrackingCodeGenerator = (JsTrackingCodeGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=b75712a8

var JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_1 = {
  key: 0
};
var JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_2 = {
  id: "javascript-text"
};
var JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_3 = ["textContent"];
function JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_JsTrackingCodeAdvancedOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackingCodeAdvancedOptions");

  var _component_JsTrackerInstallCheck = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackerInstallCheck");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.showTestSection ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JsTrackingCodeAdvancedOptionsStep')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_JsTrackingCodeAdvancedOptions, {
    site: _ctx.site,
    "max-custom-variables": _ctx.maxCustomVariables,
    "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
    onUpdateTrackingCode: _ctx.updateTrackingCode,
    ref: "jsTrackingCodeAdvanceOption"
  }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getCopyCodeStep), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
    class: "codeblock",
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
    ref: "trackingCode"
  }, null, 8, JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_hoisted_3), [[_directive_copy_to_clipboard, {}]])])]), !_ctx.showTestSection ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_JsTrackingCodeAdvancedOptions, {
    key: 1,
    site: _ctx.site,
    "max-custom-variables": _ctx.maxCustomVariables,
    "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
    onUpdateTrackingCode: _ctx.updateTrackingCode,
    ref: "jsTrackingCodeAdvanceOption"
  }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showTestSection ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_JsTrackerInstallCheck, {
    key: 2,
    site: _ctx.site
  }, null, 8, ["site"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=b75712a8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=0ca140a7

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_1 = {
  class: "jsTrackerInstallCheck"
};
var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_2 = {
  class: "row testInstallFields"
};
var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_3 = {
  class: "col s2"
};
var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_4 = {
  class: "col s10"
};
var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_5 = ["disabled", "value"];
var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_6 = {
  class: "system-success success-message"
};

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_8 = {
  class: "system-errors test-error"
};

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-warning"
}, null, -1);

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("  ");

var JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_11 = ["innerHTML"];
function JsTrackerInstallCheckvue_type_template_id_0ca140a7_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('JsTrackerInstallCheck_TestInstallationDescription')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "url",
    name: "baseUrl",
    placeholder: "https://example.com",
    modelValue: _ctx.baseUrl,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.baseUrl = $event;
    }),
    "full-width": true,
    disabled: _ctx.isTesting
  }, null, 8, ["modelValue", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    class: "btn testInstallBtn",
    onClick: _cache[1] || (_cache[1] = function () {
      return _ctx.initiateTrackerTest && _ctx.initiateTrackerTest.apply(_ctx, arguments);
    }),
    disabled: !_ctx.baseUrl || _ctx.isTesting,
    value: _ctx.translate('JsTrackerInstallCheck_TestInstallationBtnText')
  }, null, 8, JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_5)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isTesting,
    loadingMessage: _ctx.translate('General_Testing')
  }, null, 8, ["loading", "loadingMessage"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_6, [JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isTestSuccess]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_8, [JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_9, JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.getTestFailureMessage)
  }, null, 8, JsTrackerInstallCheckvue_type_template_id_0ca140a7_hoisted_11)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isTestComplete && !_ctx.isTestSuccess]])])], 64);
}
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=0ca140a7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=72138b1f

function Fieldvue_type_template_id_72138b1f_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FormField, {
    "form-field": _ctx.field,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    "model-modifiers": _ctx.modelModifiers
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")];
    }),
    _: 3
  }, 8, ["form-field", "model-value", "model-modifiers"]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=72138b1f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=404ea360

var FormFieldvue_type_template_id_404ea360_hoisted_1 = {
  class: "form-group row matomo-form-field"
};
var FormFieldvue_type_template_id_404ea360_hoisted_2 = {
  key: 0,
  class: "col s12"
};
var FormFieldvue_type_template_id_404ea360_hoisted_3 = {
  key: 0,
  class: "form-help"
};
var FormFieldvue_type_template_id_404ea360_hoisted_4 = {
  key: 0,
  class: "inline-help",
  ref: "inlineHelp"
};

var FormFieldvue_type_template_id_404ea360_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

function FormFieldvue_type_template_id_404ea360_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FormFieldvue_type_template_id_404ea360_hoisted_1, [_ctx.formField.introduction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", FormFieldvue_type_template_id_404ea360_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.introduction), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'input-field': _ctx.formField.uiControl !== 'checkbox' && _ctx.formField.uiControl !== 'radio',
      'file-field': _ctx.formField.uiControl === 'file',
      'm6': !_ctx.formField.fullWidth
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.childComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(Object.assign(Object.assign({
    formField: _ctx.formField
  }, _ctx.formField), {}, {
    modelValue: _ctx.processedModelValue,
    modelModifiers: _ctx.modelModifiers,
    availableOptions: _ctx.availableOptions
  }, _ctx.extraChildComponentParams), {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }), null, 16))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'm6': !_ctx.formField.fullWidth
    }])
  }, [_ctx.showFormHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FormFieldvue_type_template_id_404ea360_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "form-description"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.formField.description]]), _ctx.formField.inlineHelp || _ctx.hasInlineHelpSlot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", FormFieldvue_type_template_id_404ea360_hoisted_4, [_ctx.inlineHelpComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.inlineHelpComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeProps"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    key: 0
  }, _ctx.inlineHelpBind)), null, 16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [FormFieldvue_type_template_id_404ea360_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.defaultValuePrettyTruncated), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showDefaultValue]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=404ea360

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=2988a0eb

var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_1 = {
  class: "checkbox"
};
var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_2 = ["checked", "id", "name"];
var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_3 = ["innerHTML"];
function FieldCheckboxvue_type_template_id_2988a0eb_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxvue_type_template_id_2988a0eb_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes, {
    value: 1,
    checked: _ctx.isChecked,
    type: "checkbox",
    id: _ctx.name,
    name: _ctx.name
  }), null, 16, FieldCheckboxvue_type_template_id_2988a0eb_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldCheckboxvue_type_template_id_2988a0eb_hoisted_3)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=2988a0eb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldCheckboxvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Boolean, Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object,
    name: String,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var newValue = event.target.checked;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            event.target.checked = !newValue;
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    isChecked: function isChecked() {
      return !!this.modelValue && this.modelValue !== '0';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue



FieldCheckboxvue_type_script_lang_ts.render = FieldCheckboxvue_type_template_id_2988a0eb_render

/* harmony default export */ var FieldCheckbox = (FieldCheckboxvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=23eb5e5a

var FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_1 = {
  ref: "root"
};
var FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_2 = ["value", "checked", "onChange", "id", "name"];
function FieldCheckboxArrayvue_type_template_id_23eb5e5a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (checkboxModel, $index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: $index,
      class: "checkbox"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: checkboxModel.key,
      checked: !!_ctx.checkboxStates[$index],
      onChange: function onChange($event) {
        return _ctx.onChange($index);
      }
    }, _ctx.uiControlAttributes, {
      type: "checkbox",
      id: "".concat(_ctx.name).concat(checkboxModel.key),
      name: checkboxModel.name
    }), null, 16, FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], checkboxModel.description]])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=23eb5e5a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



function getCheckboxStates(availableOptions, modelValue) {
  return (availableOptions || []).map(function (o) {
    return modelValue && modelValue.indexOf(o.key) !== -1;
  });
}

/* harmony default export */ var FieldCheckboxArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    modelModifiers: Object,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    type: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    checkboxStates: function checkboxStates() {
      return getCheckboxStates(this.availableOptions, this.modelValue);
    }
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  methods: {
    onChange: function onChange(changedIndex) {
      var _this$modelModifiers,
          _this = this;

      var checkboxStates = _toConsumableArray(this.checkboxStates);

      checkboxStates[changedIndex] = !checkboxStates[changedIndex];
      var availableOptions = this.availableOptions || {};
      var newValue = [];
      Object.values(availableOptions).forEach(function (option, index) {
        if (checkboxStates[index]) {
          newValue.push(option.key);
        }
      });

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          // undo checked changes since we want the parent component to decide if it should go
          // through
          var item = _this.$refs.root.querySelectorAll('input').item(changedIndex);

          item.checked = !item.checked;
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue



FieldCheckboxArrayvue_type_script_lang_ts.render = FieldCheckboxArrayvue_type_template_id_23eb5e5a_render

/* harmony default export */ var FieldCheckboxArray = (FieldCheckboxArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=22a119c2

var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_1 = {
  class: "expandableSelector"
};

var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("svg", {
  class: "caret",
  height: "24",
  viewBox: "0 0 24 24",
  width: "24",
  xmlns: "http://www.w3.org/2000/svg"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M7 10l5 5 5-5z"
}), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M0 0h24v24H0z",
  fill: "none"
})], -1);

var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_3 = ["value"];
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_4 = {
  class: "expandableList z-depth-2"
};
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_5 = {
  class: "searchContainer"
};
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_6 = {
  class: "collection firstLevel"
};
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_7 = ["onClick"];
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_8 = {
  class: "collection secondLevel"
};
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_9 = ["onClick"];
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_10 = {
  class: "primary-content"
};
var FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_11 = ["title"];
function FieldExpandableSelectvue_type_template_id_22a119c2_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showSelect = !_ctx.showSelect;
    }),
    class: "select-wrapper"
  }, [FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "select-dropdown",
    readonly: "readonly",
    value: _ctx.modelValueText
  }, null, 8, FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    placeholder: "Search",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    class: "expandableSearch browser-default"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.showSelect
  }]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (options, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "collection-item",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", {
      class: "expandableListCategory",
      onClick: function onClick($event) {
        return _ctx.onCategoryClicked(options);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(options.group) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["secondary-content", {
        "icon-chevron-right": _ctx.showCategory !== options.group,
        "icon-chevron-down": _ctx.showCategory === options.group
      }])
    }, null, 2)], 8, FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options.values.filter(function (x) {
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
    }), function (children) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: "expandableListItem collection-item valign-wrapper",
        key: children.key,
        onClick: function onClick($event) {
          return _ctx.onValueClicked(children);
        }
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(children.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        title: children.tooltip,
        class: "secondary-content icon-help"
      }, null, 8, FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], children.tooltip]])], 8, FieldExpandableSelectvue_type_template_id_22a119c2_hoisted_9);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showCategory === options.group || _ctx.searchTerm]])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], options.values.filter(function (x) {
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
    }).length]]);
  }), 128))])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSelect]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=22a119c2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts


function getAvailableOptions(availableValues) {
  var flatValues = [];

  if (!availableValues) {
    return flatValues;
  }

  var groups = {};
  Object.values(availableValues).forEach(function (uncastedValue) {
    var value = uncastedValue;
    var group = value.group || '';

    if (!(group in groups) || !groups[group]) {
      groups[group] = {
        values: [],
        group: group
      };
    }

    var formatted = {
      key: value.key,
      value: value.value
    };

    if ('tooltip' in value && value.tooltip) {
      formatted.tooltip = value.tooltip;
    }

    groups[group].values.push(formatted);
  });
  Object.values(groups).forEach(function (group) {
    if (group.values.length) {
      flatValues.push(group);
    }
  });
  return flatValues;
}
/* harmony default export */ var FieldExpandableSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Number, String],
    modelModifiers: Object,
    availableOptions: Array,
    title: String
  },
  directives: {
    FocusAnywhereButHere: external_CoreHome_["FocusAnywhereButHere"],
    FocusIf: external_CoreHome_["FocusIf"]
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  data: function data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: ''
    };
  },
  computed: {
    modelValueText: function modelValueText() {
      if (this.title) {
        return this.title;
      }

      var key = this.modelValue;
      var availableOptions = this.availableOptions || [];
      var keyItem;
      availableOptions.some(function (option) {
        keyItem = option.values.find(function (item) {
          return item.key === key;
        });
        return keyItem; // stop iterating if found
      });

      if (keyItem) {
        return keyItem.value ? "".concat(keyItem.value) : '';
      }

      return key ? "".concat(key) : '';
    }
  },
  methods: {
    onBlur: function onBlur() {
      this.showSelect = false;
    },
    onCategoryClicked: function onCategoryClicked(options) {
      if (this.showCategory === options.group) {
        this.showCategory = '';
      } else {
        this.showCategory = options.group;
      }
    },
    onValueClicked: function onValueClicked(selectedValue) {
      var _this$modelModifiers;

      this.showSelect = false;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', selectedValue.key);
        return;
      }

      var emitEventData = {
        value: selectedValue.key,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue



FieldExpandableSelectvue_type_script_lang_ts.render = FieldExpandableSelectvue_type_template_id_22a119c2_render

/* harmony default export */ var FieldExpandableSelect = (FieldExpandableSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=077f56d4

var FieldFieldArrayvue_type_template_id_077f56d4_hoisted_1 = ["for", "innerHTML"];
function FieldFieldArrayvue_type_template_id_077f56d4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FieldArray = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FieldArray");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFieldArrayvue_type_template_id_077f56d4_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FieldArray, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onValueUpdate($event);
    }),
    "model-modifiers": _ctx.modelModifiers,
    field: _ctx.uiControlAttributes.field,
    rows: _ctx.uiControlAttributes.rows
  }, null, 8, ["name", "model-value", "model-modifiers", "field", "rows"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=077f56d4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldFieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FieldArray: external_CoreHome_["FieldArray"]
  },
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onValueUpdate: function onValueUpdate(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue



FieldFieldArrayvue_type_script_lang_ts.render = FieldFieldArrayvue_type_template_id_077f56d4_render

/* harmony default export */ var FieldFieldArray = (FieldFieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2903f7cf

var FieldFilevue_type_template_id_2903f7cf_hoisted_1 = {
  class: "btn"
};
var FieldFilevue_type_template_id_2903f7cf_hoisted_2 = ["for", "innerHTML"];
var FieldFilevue_type_template_id_2903f7cf_hoisted_3 = ["name", "id"];
var FieldFilevue_type_template_id_2903f7cf_hoisted_4 = {
  class: "file-path-wrapper"
};
var FieldFilevue_type_template_id_2903f7cf_hoisted_5 = ["value"];
function FieldFilevue_type_template_id_2903f7cf_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2903f7cf_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFilevue_type_template_id_2903f7cf_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    ref: "fileInput",
    name: _ctx.name,
    type: "file",
    id: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldFilevue_type_template_id_2903f7cf_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2903f7cf_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "file-path validate",
    value: _ctx.filePath,
    type: "text"
  }, null, 8, FieldFilevue_type_template_id_2903f7cf_hoisted_5)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2903f7cf

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldFilevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: [String, File],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  watch: {
    modelValue: function modelValue(v) {
      if (!v || v === '') {
        var fileInputElement = this.$refs.fileInput;
        fileInputElement.value = '';
      }
    }
  },
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers;

      var files = event.target.files;

      if (!files) {
        return;
      }

      var file = files.item(0);

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', file);
        return;
      }

      var emitEventData = {
        value: file,
        abort: function abort() {// not supported
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  },
  computed: {
    filePath: function filePath() {
      if (this.modelValue instanceof File) {
        return this.$refs.fileInput.value;
      }

      return undefined;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue



FieldFilevue_type_script_lang_ts.render = FieldFilevue_type_template_id_2903f7cf_render

/* harmony default export */ var FieldFile = (FieldFilevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=1cc21994

var FieldHiddenvue_type_template_id_1cc21994_hoisted_1 = ["type", "name", "value"];
function FieldHiddenvue_type_template_id_1cc21994_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: _ctx.uiControl,
    name: _ctx.name,
    value: _ctx.modelValue,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldHiddenvue_type_template_id_1cc21994_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=1cc21994

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldHiddenvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uiControl: String,
    name: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue



FieldHiddenvue_type_script_lang_ts.render = FieldHiddenvue_type_template_id_1cc21994_render

/* harmony default export */ var FieldHidden = (FieldHiddenvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=11865b3d

var FieldMultituplevue_type_template_id_11865b3d_hoisted_1 = {
  class: "fieldMultiTuple"
};
var FieldMultituplevue_type_template_id_11865b3d_hoisted_2 = ["for", "innerHTML"];
function FieldMultituplevue_type_template_id_11865b3d_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MultiPairField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultiPairField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldMultituplevue_type_template_id_11865b3d_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldMultituplevue_type_template_id_11865b3d_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MultiPairField, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _ctx.onUpdateValue,
    "model-modifiers": _ctx.modelModifiers,
    field1: _ctx.uiControlAttributes.field1,
    field2: _ctx.uiControlAttributes.field2,
    field3: _ctx.uiControlAttributes.field3,
    field4: _ctx.uiControlAttributes.field4,
    rows: _ctx.uiControlAttributes.rows
  }, null, 8, ["name", "model-value", "onUpdate:modelValue", "model-modifiers", "field1", "field2", "field3", "field4", "rows"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=11865b3d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldMultituplevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    MultiPairField: external_CoreHome_["MultiPairField"]
  },
  emits: ['update:modelValue'],
  methods: {
    onUpdateValue: function onUpdateValue(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue



FieldMultituplevue_type_script_lang_ts.render = FieldMultituplevue_type_template_id_11865b3d_render

/* harmony default export */ var FieldMultituple = (FieldMultituplevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=91fe4a1c

var FieldNumbervue_type_template_id_91fe4a1c_hoisted_1 = ["type", "id", "name", "value"];
var FieldNumbervue_type_template_id_91fe4a1c_hoisted_2 = ["for", "innerHTML"];
function FieldNumbervue_type_template_id_91fe4a1c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueFormatted,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldNumbervue_type_template_id_91fe4a1c_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldNumbervue_type_template_id_91fe4a1c_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=91fe4a1c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldNumbervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    uiControl: String,
    name: String,
    title: String,
    modelValue: [Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onChange = Object(external_CoreHome_["debounce"])(this.onChange.bind(this), 50);
  },
  methods: {
    onChange: function onChange(event) {
      var _this = this;

      var value = parseFloat(event.target.value);

      if (value !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.modelValueFormatted) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.modelValueFormatted;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  computed: {
    modelValueFormatted: function modelValueFormatted() {
      return (this.modelValue || '').toString();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue



FieldNumbervue_type_script_lang_ts.render = FieldNumbervue_type_template_id_91fe4a1c_render

/* harmony default export */ var FieldNumber = (FieldNumbervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=5ab171cb

var FieldRadiovue_type_template_id_5ab171cb_hoisted_1 = {
  ref: "root"
};
var FieldRadiovue_type_template_id_5ab171cb_hoisted_2 = ["value", "id", "name", "disabled", "checked"];
function FieldRadiovue_type_template_id_5ab171cb_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldRadiovue_type_template_id_5ab171cb_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions || [], function (radioModel) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: radioModel.key,
      class: "radio"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: radioModel.key,
      onChange: _cache[0] || (_cache[0] = function ($event) {
        return _ctx.onChange($event);
      }),
      type: "radio",
      id: "".concat(_ctx.name).concat(radioModel.key),
      name: _ctx.name,
      disabled: radioModel.disabled || _ctx.disabled
    }, _ctx.uiControlAttributes, {
      checked: _ctx.modelValue === radioModel.key || "".concat(_ctx.modelValue) === radioModel.key
    }), null, 16, FieldRadiovue_type_template_id_5ab171cb_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.value) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], radioModel.description]])])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=5ab171cb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldRadiovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers,
          _this = this;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', event.target.value);
        return;
      }

      var reset = function reset() {
        // change to previous value so the parent component can determine if this change should
        // go through
        _this.$refs.root.querySelectorAll('input').forEach(function (inp, i) {
          var _this$availableOption;

          if (!((_this$availableOption = _this.availableOptions) !== null && _this$availableOption !== void 0 && _this$availableOption[i])) {
            return;
          }

          var key = _this.availableOptions[i].key;
          inp.checked = _this.modelValue === key || "".concat(_this.modelValue) === key;
        });
      };

      var emitEventData = {
        value: event.target.value,
        abort: function abort() {
          reset();
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue



FieldRadiovue_type_script_lang_ts.render = FieldRadiovue_type_template_id_5ab171cb_render

/* harmony default export */ var FieldRadio = (FieldRadiovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32fc626c
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || FieldSelectvue_type_template_id_32fc626c_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_template_id_32fc626c_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }


var FieldSelectvue_type_template_id_32fc626c_hoisted_1 = {
  key: 0,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_32fc626c_hoisted_2 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_3 = ["label"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_4 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_5 = ["for", "innerHTML"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_6 = {
  key: 1,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_32fc626c_hoisted_7 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_8 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_9 = ["for", "innerHTML"];
function FieldSelectvue_type_template_id_32fc626c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.groupedOptions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32fc626c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "select",
    class: "grouped",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groupedOptions, function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        group = _ref2[0],
        options = _ref2[1];

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
      key: group,
      label: group
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options, function (option) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: option.key,
        value: "string:".concat(option.key),
        selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
        disabled: option.disabled
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32fc626c_hoisted_4);
    }), 128))], 8, FieldSelectvue_type_template_id_32fc626c_hoisted_3);
  }), 128))], 16, FieldSelectvue_type_template_id_32fc626c_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_32fc626c_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.groupedOptions && _ctx.options ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32fc626c_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "ungrouped",
    ref: "select",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, function (option) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option.key,
      value: "string:".concat(option.key),
      selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
      disabled: option.disabled
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32fc626c_hoisted_8);
  }), 128))], 16, FieldSelectvue_type_template_id_32fc626c_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_32fc626c_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32fc626c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
function FieldSelectvue_type_script_lang_ts_toConsumableArray(arr) { return FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArray(arr) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr) || FieldSelectvue_type_script_lang_ts_nonIterableSpread(); }

function FieldSelectvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr); }

function FieldSelectvue_type_script_lang_ts_slicedToArray(arr, i) { return FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || FieldSelectvue_type_script_lang_ts_nonIterableRest(); }

function FieldSelectvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }



function initMaterialSelect(select, modelValue, placeholder) {
  var uiControlOptions = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
  var multiple = arguments.length > 4 ? arguments[4] : undefined;

  if (!select) {
    return;
  }

  var $select = window.$(select); // reset selected since materialize removes them

  Array.from(select.options).forEach(function (opt) {
    if (multiple) {
      opt.selected = !!modelValue && modelValue.indexOf(opt.value.replace(/^string:/, '')) !== -1;
    } else {
      opt.selected = "string:".concat(modelValue) === opt.value;
    }
  });
  $select.formSelect(uiControlOptions); // add placeholder to input

  if (placeholder) {
    var $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (Array.isArray(availableValues) || !(_typeof(availableValues) === 'object')) {
    return false;
  }

  return Object.values(availableValues).some(function (v) {
    return _typeof(v) === 'object';
  });
}

function hasOption(flatValues, key) {
  return flatValues.some(function (f) {
    return f.key === key;
  });
}

function FieldSelectvue_type_script_lang_ts_getAvailableOptions(givenAvailableValues, type, uiControlAttributes) {
  if (!givenAvailableValues) {
    return [];
  }

  var hasGroups = true;
  var availableValues = givenAvailableValues;

  if (!hasGroupedValues(availableValues)) {
    availableValues = {
      '': givenAvailableValues
    };
    hasGroups = false;
  }

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref, 2),
        group = _ref2[0],
        values = _ref2[1];

    Object.entries(values).forEach(function (_ref3) {
      var _ref4 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref3, 2),
          valueObjKey = _ref4[0],
          value = _ref4[1];

      if (value && _typeof(value) === 'object' && typeof value.key !== 'undefined') {
        flatValues.push(value);
        return;
      }

      var key = valueObjKey;

      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({
        group: hasGroups ? group : undefined,
        key: key,
        value: value
      });
    });
  }); // for selects w/ a placeholder, add an option to unset the select

  if (uiControlAttributes !== null && uiControlAttributes !== void 0 && uiControlAttributes.placeholder && !hasOption(flatValues, '')) {
    return [{
      key: '',
      value: ''
    }].concat(flatValues);
  }

  return flatValues;
}

function handleOldAngularJsValues(value) {
  if (typeof value === 'string') {
    return value.replace(/^string:/, '');
  }

  return value;
}

/* harmony default export */ var FieldSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    uiControlOptions: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    options: function options() {
      // if modelValue is empty, but there is no empty value allowed in availableOptions,
      // add one temporarily until something is set
      var availableOptions = this.availableOptions;

      if (availableOptions && !hasOption(availableOptions, '') && (typeof this.modelValue === 'undefined' || this.modelValue === null || this.modelValue === '')) {
        return [{
          key: '',
          value: this.modelValue,
          group: this.hasGroups ? '' : undefined
        }].concat(FieldSelectvue_type_script_lang_ts_toConsumableArray(availableOptions));
      }

      return availableOptions;
    },
    hasGroups: function hasGroups() {
      var availableOptions = this.availableOptions;
      return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== 'undefined';
    },
    groupedOptions: function groupedOptions() {
      var options = this.options;

      if (!this.hasGroups || !options) {
        return null;
      }

      var groups = {};
      options.forEach(function (entry) {
        var group = entry.group;
        groups[group] = groups[group] || [];
        groups[group].push(entry);
      });
      var result = Object.entries(groups);
      result.sort(function (lhs, rhs) {
        if (lhs[0] < rhs[0]) {
          return -1;
        }

        if (lhs[0] > rhs[0]) {
          return 1;
        }

        return 0;
      });
      return result;
    }
  },
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers,
          _this = this;

      var element = event.target;
      var newValue;

      if (this.multiple) {
        newValue = Array.from(element.options).filter(function (e) {
          return e.selected;
        }).map(function (e) {
          return e.value;
        });
        newValue = newValue.map(function (x) {
          return handleOldAngularJsValues(x);
        });
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          _this.onModelValueChange(_this.modelValue);
        }
      };
      this.$emit('update:modelValue', emitEventData);
    },
    onModelValueChange: function onModelValueChange(newVal) {
      var _this2 = this;

      window.$(this.$refs.select).val(newVal);
      setTimeout(function () {
        var _this2$uiControlAttri;

        initMaterialSelect(_this2.$refs.select, newVal, (_this2$uiControlAttri = _this2.uiControlAttributes) === null || _this2$uiControlAttri === void 0 ? void 0 : _this2$uiControlAttri.placeholder, _this2.uiControlOptions, _this2.multiple);
      });
    }
  },
  watch: {
    modelValue: function modelValue(newVal) {
      this.onModelValueChange(newVal);
    },
    'uiControlAttributes.disabled': {
      handler: function handler(newVal, oldVal) {
        var _this3 = this;

        setTimeout(function () {
          if (newVal !== oldVal) {
            var _this3$uiControlAttri;

            initMaterialSelect(_this3.$refs.select, _this3.modelValue, (_this3$uiControlAttri = _this3.uiControlAttributes) === null || _this3$uiControlAttri === void 0 ? void 0 : _this3$uiControlAttri.placeholder, _this3.uiControlOptions, _this3.multiple);
          }
        });
      }
    },
    availableOptions: function availableOptions(newVal, oldVal) {
      var _this4 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          var _this4$uiControlAttri;

          initMaterialSelect(_this4.$refs.select, _this4.modelValue, (_this4$uiControlAttri = _this4.uiControlAttributes) === null || _this4$uiControlAttri === void 0 ? void 0 : _this4$uiControlAttri.placeholder, _this4.uiControlOptions, _this4.multiple);
        });
      }
    }
  },
  mounted: function mounted() {
    var _this5 = this;

    setTimeout(function () {
      var _this5$uiControlAttri;

      initMaterialSelect(_this5.$refs.select, _this5.modelValue, (_this5$uiControlAttri = _this5.uiControlAttributes) === null || _this5$uiControlAttri === void 0 ? void 0 : _this5$uiControlAttri.placeholder, _this5.uiControlOptions, _this5.multiple);
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue



FieldSelectvue_type_script_lang_ts.render = FieldSelectvue_type_template_id_32fc626c_render

/* harmony default export */ var FieldSelect = (FieldSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=4680911e

var FieldSitevue_type_template_id_4680911e_hoisted_1 = ["for", "innerHTML"];
var FieldSitevue_type_template_id_4680911e_hoisted_2 = {
  class: "sites_autocomplete"
};
function FieldSitevue_type_template_id_4680911e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    class: "siteSelectorLabel",
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSitevue_type_template_id_4680911e_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldSitevue_type_template_id_4680911e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    id: _ctx.name,
    "show-all-sites-item": _ctx.uiControlAttributes.showAllSitesItem || false,
    "switch-site-on-select": false,
    "show-selected-site": true,
    "only-sites-with-admin-access": _ctx.uiControlAttributes.onlySitesWithAdminAccess || false
  }, _ctx.uiControlAttributes), null, 16, ["model-value", "id", "show-all-sites-item", "only-sites-with-admin-access"])])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=4680911e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    SiteSelector: external_CoreHome_["SiteSelector"]
  },
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(newValue) {
      var _this$modelModifiers;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue



FieldSitevue_type_script_lang_ts.render = FieldSitevue_type_template_id_4680911e_render

/* harmony default export */ var FieldSite = (FieldSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=90e4f7d4

var FieldTextvue_type_template_id_90e4f7d4_hoisted_1 = ["type", "id", "name", "value", "spellcheck"];
var FieldTextvue_type_template_id_90e4f7d4_hoisted_2 = ["for", "innerHTML"];
function FieldTextvue_type_template_id_90e4f7d4_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueText,
    spellcheck: _ctx.uiControl === 'password' ? false : null,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldTextvue_type_template_id_90e4f7d4_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextvue_type_template_id_90e4f7d4_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=90e4f7d4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    name: String,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object,
    uiControl: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    modelValueText: function modelValueText() {
      if (typeof this.modelValue === 'undefined' || this.modelValue === null) {
        return '';
      }

      return this.modelValue.toString();
    }
  },
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            // change to previous value if the parent component did not update the model value
            // (done manually because Vue will not notice if a value does NOT change)
            if (event.target.value !== _this.modelValueText) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue



FieldTextvue_type_script_lang_ts.render = FieldTextvue_type_template_id_90e4f7d4_render

/* harmony default export */ var FieldText = (FieldTextvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=72853163

var FieldTextArrayvue_type_template_id_72853163_hoisted_1 = ["for", "innerHTML"];
var FieldTextArrayvue_type_template_id_72853163_hoisted_2 = ["type", "name", "value"];
function FieldTextArrayvue_type_template_id_72853163_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextArrayvue_type_template_id_72853163_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    name: _ctx.name,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    value: _ctx.concattedValues
  }, _ctx.uiControlAttributes), null, 16, FieldTextArrayvue_type_template_id_72853163_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=72853163

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControl: String,
    modelValue: Array,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  computed: {
    concattedValues: function concattedValues() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      }

      return (this.modelValue || []).join(', ');
    }
  },
  emits: ['update:modelValue'],
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var values = event.target.value.split(',').map(function (v) {
        return v.trim();
      });

      if (values.join(', ') !== this.concattedValues) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', values);
          return;
        }

        var emitEventData = {
          value: values,
          abort: function abort() {
            if (event.target.value !== _this.concattedValues) {
              event.target.value = _this.concattedValues;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue



FieldTextArrayvue_type_script_lang_ts.render = FieldTextArrayvue_type_template_id_72853163_render

/* harmony default export */ var FieldTextArray = (FieldTextArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=f0327bcc

var FieldTextareavue_type_template_id_f0327bcc_hoisted_1 = ["name", "id", "value"];
var FieldTextareavue_type_template_id_f0327bcc_hoisted_2 = ["for", "innerHTML"];
function FieldTextareavue_type_template_id_f0327bcc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    id: _ctx.name,
    value: _ctx.modelValueText,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea",
    ref: "textarea"
  }), null, 16, FieldTextareavue_type_template_id_f0327bcc_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareavue_type_template_id_f0327bcc_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=f0327bcc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextareavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    modelModifiers: Object,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (newValue !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            if (event.target.value !== _this.modelValue) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    modelValueText: function modelValueText() {
      return this.modelValue || '';
    }
  },
  watch: {
    modelValue: function modelValue() {
      var _this2 = this;

      setTimeout(function () {
        window.Materialize.textareaAutoResize(_this2.$refs.textarea);
        window.Materialize.updateTextFields();
      });
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue



FieldTextareavue_type_script_lang_ts.render = FieldTextareavue_type_template_id_f0327bcc_render

/* harmony default export */ var FieldTextarea = (FieldTextareavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=77717d95

var FieldTextareaArrayvue_type_template_id_77717d95_hoisted_1 = ["for", "innerHTML"];
var FieldTextareaArrayvue_type_template_id_77717d95_hoisted_2 = ["name", "value"];
function FieldTextareaArrayvue_type_template_id_77717d95_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareaArrayvue_type_template_id_77717d95_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "textarea",
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    value: _ctx.concattedValue,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea"
  }), null, 16, FieldTextareaArrayvue_type_template_id_77717d95_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=77717d95

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
function FieldTextareaArrayvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FieldTextareaArrayvue_type_script_lang_ts_typeof(obj); }



var SEPARATOR = '\n';
/* harmony default export */ var FieldTextareaArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: [Array, String],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue: function concattedValue() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      } // Handle case when modelValues is like: {"0": "value0", "2": "value1"}


      if (FieldTextareaArrayvue_type_script_lang_ts_typeof(this.modelValue) === 'object') {
        return Object.values(this.modelValue).join(SEPARATOR);
      }

      try {
        return (this.modelValue || []).join(SEPARATOR);
      } catch (e) {
        // Prevent page breaking on unexpected modelValue type
        console.error(e);
        return '';
      }
    }
  },
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var value = event.target.value.split(SEPARATOR);

      if (value.join(SEPARATOR) !== this.concattedValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.concattedValue) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.concattedValue;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this2 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          if (_this2.$refs.textarea) {
            window.Materialize.textareaAutoResize(_this2.$refs.textarea);
          }

          window.Materialize.updateTextFields();
        });
      }
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      if (_this3.$refs.textarea) {
        window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      }

      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue



FieldTextareaArrayvue_type_script_lang_ts.render = FieldTextareaArrayvue_type_template_id_77717d95_render

/* harmony default export */ var FieldTextareaArray = (FieldTextareaArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/utilities.ts
function utilities_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { utilities_typeof = function _typeof(obj) { return typeof obj; }; } else { utilities_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return utilities_typeof(obj); }

function utilities_slicedToArray(arr, i) { return utilities_arrayWithHoles(arr) || utilities_iterableToArrayLimit(arr, i) || utilities_unsupportedIterableToArray(arr, i) || utilities_nonIterableRest(); }

function utilities_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function utilities_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return utilities_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return utilities_arrayLikeToArray(o, minLen); }

function utilities_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function utilities_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function utilities_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function processCheckboxAndRadioAvailableValues(availableValues, type) {
  if (!availableValues) {
    return [];
  }

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = utilities_slicedToArray(_ref, 2),
        valueObjKey = _ref2[0],
        value = _ref2[1];

    if (value && utilities_typeof(value) === 'object' && typeof value.key !== 'undefined') {
      flatValues.push(value);
      return;
    }

    var key = valueObjKey;

    if (type === 'integer' && typeof valueObjKey === 'string') {
      key = parseInt(key, 10);
    }

    flatValues.push({
      key: key,
      value: value
    });
  });
  return flatValues;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
function FormFieldvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FormFieldvue_type_script_lang_ts_typeof(obj); }



















var TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
var CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
var CONTROL_TO_COMPONENT_MAP = {
  checkbox: 'FieldCheckbox',
  'expandable-select': 'FieldExpandableSelect',
  'field-array': 'FieldFieldArray',
  file: 'FieldFile',
  hidden: 'FieldHidden',
  multiselect: 'FieldSelect',
  multituple: 'FieldMultituple',
  number: 'FieldNumber',
  radio: 'FieldRadio',
  select: 'FieldSelect',
  site: 'FieldSite',
  text: 'FieldText',
  textarea: 'FieldTextarea'
};
var CONTROL_TO_AVAILABLE_OPTION_PROCESSOR = {
  FieldSelect: FieldSelectvue_type_script_lang_ts_getAvailableOptions,
  FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
  FieldRadio: processCheckboxAndRadioAvailableValues,
  FieldExpandableSelect: getAvailableOptions
};
/* harmony default export */ var FormFieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    formField: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  components: {
    FieldCheckbox: FieldCheckbox,
    FieldCheckboxArray: FieldCheckboxArray,
    FieldExpandableSelect: FieldExpandableSelect,
    FieldFieldArray: FieldFieldArray,
    FieldFile: FieldFile,
    FieldHidden: FieldHidden,
    FieldMultituple: FieldMultituple,
    FieldNumber: FieldNumber,
    FieldRadio: FieldRadio,
    FieldSelect: FieldSelect,
    FieldSite: FieldSite,
    FieldText: FieldText,
    FieldTextArray: FieldTextArray,
    FieldTextarea: FieldTextarea,
    FieldTextareaArray: FieldTextareaArray
  },
  setup: function setup(props) {
    var inlineHelpNode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    var setInlineHelp = function setInlineHelp(newVal) {
      var toAppend;

      if (!newVal || !inlineHelpNode.value || typeof newVal.render === 'function') {
        return;
      }

      if (typeof newVal === 'string') {
        if (newVal.indexOf('#') === 0) {
          toAppend = window.$(newVal);
        } else {
          toAppend = window.vueSanitize(newVal);
        }
      } else {
        toAppend = newVal;
      }

      window.$(inlineHelpNode.value).html('').append(toAppend);
    };

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.formField.inlineHelp;
    }, setInlineHelp);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      setInlineHelp(props.formField.inlineHelp);
    });
    return {
      inlineHelp: inlineHelpNode
    };
  },
  computed: {
    inlineHelpComponent: function inlineHelpComponent() {
      var formField = this.formField;
      var inlineHelpRecord = formField.inlineHelp;

      if (inlineHelpRecord && typeof inlineHelpRecord.render === 'function') {
        return formField.inlineHelp;
      }

      return undefined;
    },
    inlineHelpBind: function inlineHelpBind() {
      return this.inlineHelpComponent ? this.formField.inlineHelpBind : undefined;
    },
    childComponent: function childComponent() {
      var formField = this.formField;

      if (formField.component) {
        var component = formField.component;

        if (formField.component.plugin) {
          var _formField$component = formField.component,
              plugin = _formField$component.plugin,
              name = _formField$component.name;

          if (!plugin || !name) {
            throw new Error('Invalid component property given to FormField directive, must be ' + '{plugin: \'...\',name: \'...\'}');
          }

          component = Object(external_CoreHome_["useExternalPluginComponent"])(plugin, name);
        }

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(component);
      }

      var uiControl = formField.uiControl;
      var control = CONTROL_TO_COMPONENT_MAP[uiControl];

      if (TEXT_CONTROLS.indexOf(uiControl) !== -1) {
        control = 'FieldText'; // we use same template for text and password both
      }

      if (this.formField.type === 'array' && CONTROLS_SUPPORTING_ARRAY.indexOf(uiControl) !== -1) {
        control = "".concat(control, "Array");
      }

      return control;
    },
    extraChildComponentParams: function extraChildComponentParams() {
      if (this.formField.uiControl === 'multiselect') {
        return {
          multiple: true
        };
      }

      return {};
    },
    showFormHelp: function showFormHelp() {
      return this.formField.description || this.formField.inlineHelp || this.showDefaultValue || this.hasInlineHelpSlot;
    },
    showDefaultValue: function showDefaultValue() {
      return this.defaultValuePretty && this.formField.uiControl !== 'checkbox' && this.formField.uiControl !== 'radio';
    },
    processedModelValue: function processedModelValue() {
      var field = this.formField; // handle boolean field types

      if (field.type === 'boolean') {
        var valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0'; // for checkboxes, the value MUST be either true or false

        if (field.uiControl === 'checkbox') {
          return valueIsTruthy;
        }

        if (field.uiControl === 'radio') {
          return valueIsTruthy ? '1' : '0';
        }
      }

      return this.modelValue;
    },
    defaultValue: function defaultValue() {
      var defaultValue = this.formField.defaultValue;

      if (Array.isArray(defaultValue)) {
        return defaultValue.join(',');
      }

      return defaultValue;
    },
    availableOptions: function availableOptions() {
      var childComponent = this.childComponent;

      if (typeof childComponent !== 'string') {
        return null;
      }

      var formField = this.formField;

      if (!formField.availableValues || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]) {
        return null;
      }

      return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](formField.availableValues, formField.type, formField.uiControlAttributes);
    },
    defaultValuePretty: function defaultValuePretty() {
      var formField = this.formField;
      var defaultValue = formField.defaultValue;
      var availableOptions = this.availableOptions;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        var defaultParsed = null;

        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {// invalid JSON
        }

        if (defaultParsed !== null && FormFieldvue_type_script_lang_ts_typeof(defaultParsed) === 'object') {
          return '';
        }
      }

      if (!Array.isArray(availableOptions)) {
        if (Array.isArray(defaultValue)) {
          return '';
        }

        return defaultValue ? "".concat(defaultValue) : '';
      }

      var prettyValues = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      (availableOptions || []).forEach(function (value) {
        if (typeof value.value !== 'undefined' && defaultValue.indexOf(value.key) !== -1) {
          prettyValues.push(value.value);
        }
      });
      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated: function defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    },
    hasInlineHelpSlot: function hasInlineHelpSlot() {
      var _inlineHelpSlot$, _inlineHelpSlot$$chil;

      if (!this.$slots['inline-help']) {
        return false;
      }

      var inlineHelpSlot = this.$slots['inline-help']();
      return !!(inlineHelpSlot !== null && inlineHelpSlot !== void 0 && (_inlineHelpSlot$ = inlineHelpSlot[0]) !== null && _inlineHelpSlot$ !== void 0 && (_inlineHelpSlot$$chil = _inlineHelpSlot$.children) !== null && _inlineHelpSlot$$chil !== void 0 && _inlineHelpSlot$$chil.length);
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue



FormFieldvue_type_script_lang_ts.render = FormFieldvue_type_template_id_404ea360_render

/* harmony default export */ var FormField = (FormFieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts


var UI_CONTROLS_TO_TYPE = {
  multiselect: 'array',
  checkbox: 'boolean',
  site: 'object',
  number: 'integer'
};
/* harmony default export */ var Fieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uicontrol: String,
    name: String,
    defaultValue: null,
    options: [Object, Array],
    description: String,
    introduction: String,
    title: String,
    inlineHelp: [String, Object],
    inlineHelpBind: Object,
    disabled: Boolean,
    uiControlAttributes: {
      type: Object,
      default: function _default() {
        return {};
      }
    },
    uiControlOptions: {
      type: Object,
      default: function _default() {
        return {};
      }
    },
    autocomplete: String,
    varType: String,
    autofocus: Boolean,
    tabindex: Number,
    fullWidth: Boolean,
    maxlength: Number,
    required: Boolean,
    placeholder: String,
    rows: Number,
    min: Number,
    max: Number,
    component: null
  },
  emits: ['update:modelValue'],
  components: {
    FormField: FormField
  },
  computed: {
    type: function type() {
      if (this.varType) {
        return this.varType;
      }

      var uicontrol = this.uicontrol;

      if (uicontrol && UI_CONTROLS_TO_TYPE[uicontrol]) {
        return UI_CONTROLS_TO_TYPE[uicontrol];
      }

      return 'string';
    },
    field: function field() {
      return {
        uiControl: this.uicontrol,
        type: this.type,
        name: this.name,
        defaultValue: this.defaultValue,
        availableValues: this.options,
        description: this.description,
        introduction: this.introduction,
        inlineHelp: this.inlineHelp,
        inlineHelpBind: this.inlineHelpBind,
        title: this.title,
        component: this.component,
        uiControlAttributes: Object.assign(Object.assign({}, this.uiControlAttributes), {}, {
          disabled: this.disabled,
          autocomplete: this.autocomplete,
          tabindex: this.tabindex,
          autofocus: this.autofocus,
          rows: this.rows,
          required: this.required,
          maxlength: this.maxlength,
          placeholder: this.placeholder,
          min: this.min,
          max: this.max
        }),
        fullWidth: this.fullWidth,
        uiControlOptions: this.uiControlOptions
      };
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue



Fieldvue_type_script_lang_ts.render = Fieldvue_type_template_id_72138b1f_render

/* harmony default export */ var Field = (Fieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts



var MAX_NUM_API_CALLS = 10;
var TIME_BETWEEN_API_CALLS = 1000;
/* harmony default export */ var JsTrackerInstallCheckvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: Field,
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data: function data() {
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
  created: function created() {
    this.checkWhetherSuccessWasRecorded();
  },
  watch: {
    site: function site() {
      this.onSiteChange();
    }
  },
  methods: {
    onSiteChange: function onSiteChange() {
      this.checkNonce = '';
      this.isTesting = false;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      this.checkWhetherSuccessWasRecorded();
    },
    initiateTrackerTest: function initiateTrackerTest() {
      var _this = this;

      this.isTesting = true;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      var siteRef = this.site;
      var postParams = {
        idSite: siteRef.id,
        url: ''
      };

      if (this.baseUrl) {
        postParams.url = this.baseUrl;
      }

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'JsTrackerInstallCheck.initiateJsTrackerInstallTest'
      }, postParams).then(function (response) {
        var isSuccess = response && response.url && response.nonce;

        if (isSuccess) {
          _this.checkNonce = response.nonce;
          var windowRef = window.open(response.url);

          _this.setCheckInTime();

          setTimeout(function () {
            if (windowRef && !windowRef.closed) {
              windowRef.close(); // Set the timeout to the max since we've already waited too long

              _this.testTimeoutCount = MAX_NUM_API_CALLS;
            }
          }, MAX_NUM_API_CALLS * TIME_BETWEEN_API_CALLS);
        }
      });
    },
    setCheckInTime: function setCheckInTime() {
      setTimeout(this.checkWhetherSuccessWasRecorded, TIME_BETWEEN_API_CALLS);
    },
    checkWhetherSuccessWasRecorded: function checkWhetherSuccessWasRecorded() {
      var _this2 = this;

      var siteRef = this.site;
      var postParams = {
        idSite: siteRef.id,
        nonce: ''
      };

      if (this.checkNonce) {
        postParams.nonce = this.checkNonce;
      }

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'JsTrackerInstallCheck.wasJsTrackerInstallTestSuccessful'
      }, postParams).then(function (response) {
        if (response && response.mainUrl && !_this2.baseUrl) {
          _this2.baseUrl = response.mainUrl;
        }

        _this2.isTestSuccess = response && response.isSuccess; // If the test isn't successful but hasn't exceeded the timeout count, wait and check again

        if (_this2.checkNonce && !_this2.isTestSuccess && _this2.testTimeoutCount < MAX_NUM_API_CALLS) {
          _this2.testTimeoutCount += 1;

          _this2.setCheckInTime();

          return;
        }

        _this2.isTestComplete = !!_this2.checkNonce;
        _this2.isTesting = false;
      });
    }
  },
  computed: {
    getTestFailureMessage: function getTestFailureMessage() {
      if (!this.isWordpress) {
        return Object(external_CoreHome_["translate"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage');
      }

      return Object(external_CoreHome_["translate"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress', '<a target="_blank" rel="noreferrer noopener" href="https://wordpress.org/plugins/wp-piwik/">WP-Matomo Integration (WP-Piwik)</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue



JsTrackerInstallCheckvue_type_script_lang_ts.render = JsTrackerInstallCheckvue_type_template_id_0ca140a7_render

/* harmony default export */ var JsTrackerInstallCheck = (JsTrackerInstallCheckvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts




/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
    jsTag: String,
    showTestSection: {
      type: Boolean,
      required: false,
      default: false
    }
  },
  components: {
    JsTrackerInstallCheck: JsTrackerInstallCheck,
    JsTrackingCodeAdvancedOptions: JsTrackingCodeAdvancedOptions
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      site: this.defaultSite,
      trackingCode: '',
      isHighlighting: false
    };
  },
  created: function created() {
    if (this.jsTag) {
      this.trackingCode = this.jsTag;
    }
  },
  methods: {
    updateTrackingCode: function updateTrackingCode(code) {
      var _this = this;

      this.trackingCode = code;
      var jsCodeTextarea = $(this.$refs.trackingCode);

      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: function complete() {
            _this.isHighlighting = false;
          }
        }, 1500);
      }
    }
  },
  computed: {
    getCopyCodeStep: function getCopyCodeStep() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', '</head>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue



JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts.render = JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_b75712a8_render

/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutData = (JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=ccc6b740


var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "image-tracking-link"
}, null, -1);

var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_2 = {
  id: "image-tracking-code-options"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_3 = ["innerHTML"];
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_4 = ["innerHTML"];
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_5 = {
  id: "image-tracking-goal-sub"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_6 = {
  class: "row"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_7 = {
  class: "col s12 m6"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_8 = {
  class: "col s12 m6"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_9 = {
  id: "image-link-output-section"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_10 = {
  id: "image-tracking-text"
};
var ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_11 = ["textContent"];
function ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ImageTracking'),
    anchor: "imageTracking"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro)
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro3)
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "site",
        name: "image-tracker-website",
        modelValue: _ctx.site,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.site = $event;
        }),
        introduction: _ctx.translate('General_Website')
      }, null, 8, ["modelValue", "introduction"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "image-tracker-action-name",
        "model-value": _ctx.pageName,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          _ctx.pageName = $event;

          _ctx.updateTrackingCode();
        }),
        disabled: _ctx.isLoading,
        introduction: _ctx.translate('General_Options'),
        title: _ctx.translate('Actions_ColumnPageName')
      }, null, 8, ["model-value", "disabled", "introduction", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "image-tracking-goal-check",
        "model-value": _ctx.trackGoal,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          _ctx.trackGoal = $event;

          _ctx.updateTrackingCode();
        }),
        disabled: _ctx.isLoading,
        title: _ctx.translate('CoreAdminHome_TrackAGoal')
      }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "image-tracker-goal",
        options: _ctx.siteGoals,
        disabled: _ctx.isLoading,
        "model-value": _ctx.trackIdGoal,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          _ctx.trackIdGoal = $event;

          _ctx.updateTrackingCode();
        })
      }, null, 8, ["options", "disabled", "model-value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "image-revenue",
        "model-value": _ctx.revenue,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          _ctx.revenue = $event;

          _ctx.updateTrackingCode();
        }),
        disabled: _ctx.isLoading,
        "full-width": true,
        title: "".concat(_ctx.translate('CoreAdminHome_WithOptionalRevenue'), " ").concat(_ctx.currentSiteCurrency)
      }, null, 8, ["model-value", "disabled", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.trackGoal]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ImageTrackingLink')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
        ref: "trackingCode"
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_hoisted_11), [[_directive_copy_to_clipboard, {}]])])])])])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=ccc6b740

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=script&lang=ts
function ImageTrackingCodeGeneratorvue_type_script_lang_ts_slicedToArray(arr, i) { return ImageTrackingCodeGeneratorvue_type_script_lang_ts_arrayWithHoles(arr) || ImageTrackingCodeGeneratorvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || ImageTrackingCodeGeneratorvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || ImageTrackingCodeGeneratorvue_type_script_lang_ts_nonIterableRest(); }

function ImageTrackingCodeGeneratorvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function ImageTrackingCodeGeneratorvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return ImageTrackingCodeGeneratorvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return ImageTrackingCodeGeneratorvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function ImageTrackingCodeGeneratorvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function ImageTrackingCodeGeneratorvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function ImageTrackingCodeGeneratorvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }




var currencySymbols = null;
var ImageTrackingCodeGeneratorvue_type_script_lang_ts_window = window,
    ImageTrackingCodeGeneratorvue_type_script_lang_ts_$ = ImageTrackingCodeGeneratorvue_type_script_lang_ts_window.$;
var ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikHost = window.location.host;
var ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
/* harmony default export */ var ImageTrackingCodeGeneratorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      isLoading: false,
      site: this.defaultSite,
      pageName: '',
      trackGoal: false,
      trackIdGoal: null,
      revenue: '',
      trackingCode: '',
      sites: {},
      goals: {},
      trackingCodeAbortController: null,
      isHighlighting: false
    };
  },
  created: function created() {
    this.updateTrackingCode = Object(external_CoreHome_["debounce"])(this.updateTrackingCode);

    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site: function site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    onSiteChanged: function onSiteChanged(newValue) {
      var _this = this;

      this.trackIdGoal = null;
      var currencyPromise;

      if (currencySymbols) {
        currencyPromise = Promise.resolve(currencySymbols);
      } else {
        this.isLoading = true;
        currencyPromise = external_CoreHome_["AjaxHelper"].fetch({
          method: 'SitesManager.getCurrencySymbols',
          filter_limit: '-1'
        });
      }

      var sitePromise;

      if (this.sites[newValue.id]) {
        sitePromise = Promise.resolve(this.sites[newValue.id]);
      } else {
        this.isLoading = true;
        sitePromise = external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getSiteFromId',
          idSite: newValue.id
        });
      }

      var goalPromise;

      if (this.goals[newValue.id]) {
        goalPromise = Promise.resolve(this.goals[newValue.id]);
      } else {
        this.isLoading = true;
        goalPromise = external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'Goals.getGoals',
          filter_limit: '-1',
          idSite: newValue.id
        });
      }

      return Promise.all([currencyPromise, sitePromise, goalPromise]).then(function (_ref) {
        var _ref2 = ImageTrackingCodeGeneratorvue_type_script_lang_ts_slicedToArray(_ref, 3),
            currencyResponse = _ref2[0],
            site = _ref2[1],
            goalsResponse = _ref2[2];

        _this.isLoading = false;
        currencySymbols = currencyResponse;
        _this.sites[newValue.id] = site;
        _this.goals[newValue.id] = goalsResponse;

        _this.updateTrackingCode();
      });
    },
    updateTrackingCode: function updateTrackingCode() {
      var _this2 = this;

      // get data used to generate the link
      var postParams = {
        piwikUrl: "".concat(ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikHost).concat(ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikPath),
        actionName: this.pageName,
        forceMatomoEndpoint: 1
      };

      if (this.trackGoal && this.trackIdGoal) {
        postParams.idGoal = this.trackIdGoal;
        postParams.revenue = this.revenue;
      }

      if (this.trackingCodeAbortController) {
        this.trackingCodeAbortController.abort();
        this.trackingCodeAbortController = null;
      }

      this.trackingCodeAbortController = new AbortController();
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        format: 'json',
        method: 'SitesManager.getImageTrackingCode',
        idSite: this.site.id
      }, postParams, {
        abortController: this.trackingCodeAbortController
      }).then(function (response) {
        _this2.trackingCodeAbortController = null;
        _this2.trackingCode = response.value;
        var imageCodeTextarea = ImageTrackingCodeGeneratorvue_type_script_lang_ts_$(_this2.$refs.trackingCode);

        if (imageCodeTextarea && !_this2.isHighlighting) {
          _this2.isHighlighting = true;
          imageCodeTextarea.effect('highlight', {
            complete: function complete() {
              _this2.isHighlighting = false;
            }
          }, 1500);
        }
      });
    }
  },
  computed: {
    currentSiteCurrency: function currentSiteCurrency() {
      if (!currencySymbols) {
        return '';
      }

      return currencySymbols[(this.sites[this.site.id].currency || '').toUpperCase()];
    },
    siteGoals: function siteGoals() {
      var goalsResponse = this.goals[this.site.id];
      return [{
        key: '',
        value: Object(external_CoreHome_["translate"])('UserCountryMap_None')
      }].concat(Object.values(goalsResponse || []).map(function (g) {
        return {
          key: "".concat(g.idgoal),
          value: g.name
        };
      }));
    },
    imageTrackingIntro: function imageTrackingIntro() {
      var first = Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro1');
      var second = Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro2', '<code>&lt;noscript&gt;&lt;/noscript&gt;</code>');
      return "".concat(first, " ").concat(second);
    },
    imageTrackingIntro3: function imageTrackingIntro3() {
      var link = 'https://matomo.org/docs/tracking-api/reference/';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro3', "<a href=\"".concat(link, "\" rel=\"noreferrer noopener\" target=\"_blank\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue



ImageTrackingCodeGeneratorvue_type_script_lang_ts.render = ImageTrackingCodeGeneratorvue_type_template_id_ccc6b740_render

/* harmony default export */ var ImageTrackingCodeGenerator = (ImageTrackingCodeGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=209e7186


var TrackingFailuresvue_type_template_id_209e7186_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var TrackingFailuresvue_type_template_id_209e7186_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var TrackingFailuresvue_type_template_id_209e7186_hoisted_3 = ["value"];
var TrackingFailuresvue_type_template_id_209e7186_hoisted_4 = {
  class: "action"
};
var TrackingFailuresvue_type_template_id_209e7186_hoisted_5 = {
  colspan: "7"
};

var TrackingFailuresvue_type_template_id_209e7186_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);

var TrackingFailuresvue_type_template_id_209e7186_hoisted_7 = {
  class: "ui-confirm",
  id: "confirmDeleteAllTrackingFailures"
};
var TrackingFailuresvue_type_template_id_209e7186_hoisted_8 = ["value"];
var TrackingFailuresvue_type_template_id_209e7186_hoisted_9 = ["value"];
var TrackingFailuresvue_type_template_id_209e7186_hoisted_10 = {
  class: "ui-confirm",
  id: "confirmDeleteThisTrackingFailure"
};
var TrackingFailuresvue_type_template_id_209e7186_hoisted_11 = ["value"];
var TrackingFailuresvue_type_template_id_209e7186_hoisted_12 = ["value"];
function TrackingFailuresvue_type_template_id_209e7186_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_FailureRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FailureRow");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "matomoTrackingFailures",
    "content-title": _ctx.translate('CoreAdminHome_TrackingFailures')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingFailuresIntroduction', '2')) + " ", 1), TrackingFailuresvue_type_template_id_209e7186_hoisted_1, TrackingFailuresvue_type_template_id_209e7186_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        class: "btn deleteAllFailures",
        type: "button",
        onClick: _cache[0] || (_cache[0] = function ($event) {
          return _ctx.deleteAll();
        }),
        value: _ctx.translate('CoreAdminHome_DeleteAllFailures')
      }, null, 8, TrackingFailuresvue_type_template_id_209e7186_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length > 0]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.isLoading
      }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.changeSortOrder('idsite');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Measurable')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.changeSortOrder('problem');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Problem')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[3] || (_cache[3] = function ($event) {
          return _ctx.changeSortOrder('solution');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Solution')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[4] || (_cache[4] = function ($event) {
          return _ctx.changeSortOrder('date_first_occurred');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Date')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[5] || (_cache[5] = function ($event) {
          return _ctx.changeSortOrder('url');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_ColumnPageURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
        onClick: _cache[6] || (_cache[6] = function ($event) {
          return _ctx.changeSortOrder('request_url');
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", TrackingFailuresvue_type_template_id_209e7186_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", TrackingFailuresvue_type_template_id_209e7186_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_NoKnownFailures')) + " ", 1), TrackingFailuresvue_type_template_id_209e7186_hoisted_6], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length === 0]])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedFailures, function (failure, index) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: index
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FailureRow, {
          failure: failure,
          onDelete: _cache[7] || (_cache[7] = function ($event) {
            return _ctx.deleteFailure($event.idSite, $event.idFailure);
          })
        }, null, 8, ["failure"])]);
      }), 128))])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_209e7186_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteAllTrackingFailures')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "button",
        role: "yes",
        value: _ctx.translate('General_Yes')
      }, null, 8, TrackingFailuresvue_type_template_id_209e7186_hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "button",
        role: "no",
        value: _ctx.translate('General_No')
      }, null, 8, TrackingFailuresvue_type_template_id_209e7186_hoisted_9)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_209e7186_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteThisTrackingFailure')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "button",
        role: "yes",
        value: _ctx.translate('General_Yes')
      }, null, 8, TrackingFailuresvue_type_template_id_209e7186_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "button",
        role: "no",
        value: _ctx.translate('General_No')
      }, null, 8, TrackingFailuresvue_type_template_id_209e7186_hoisted_12)])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=209e7186

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

var FailureRowvue_type_template_id_62acb18a_hoisted_1 = ["href"];
var FailureRowvue_type_template_id_62acb18a_hoisted_2 = {
  class: "datetime"
};
var FailureRowvue_type_template_id_62acb18a_hoisted_3 = ["title"];
var FailureRowvue_type_template_id_62acb18a_hoisted_4 = ["title"];
function FailureRowvue_type_template_id_62acb18a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.site_name) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.idsite) + ")", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.problem), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.solution) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noopener noreferrer",
    href: _ctx.failure.solution_url
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LearnMore')), 9, FailureRowvue_type_template_id_62acb18a_hoisted_1), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.solution_url]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FailureRowvue_type_template_id_62acb18a_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.pretty_date_first_occurred), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.url), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showFullRequestUrl = true;
    }),
    title: _ctx.translate('CoreHome_ClickToSeeFullInformation')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.limtedRequestUrl) + "...", 9, FailureRowvue_type_template_id_62acb18a_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.showFullRequestUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.request_url), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.showFullRequestUrl]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "table-action icon-delete",
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.deleteFailure(_ctx.failure.idsite, _ctx.failure.idfailure);
    }),
    title: _ctx.translate('General_Delete')
  }, null, 8, FailureRowvue_type_template_id_62acb18a_hoisted_4)])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts

/* harmony default export */ var FailureRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    failure: {
      type: Object,
      required: true
    }
  },
  emits: ['delete'],
  data: function data() {
    return {
      showFullRequestUrl: false
    };
  },
  computed: {
    limtedRequestUrl: function limtedRequestUrl() {
      return this.failure.request_url.substring(0, 100);
    }
  },
  methods: {
    deleteFailure: function deleteFailure(idSite, idFailure) {
      this.$emit('delete', {
        idSite: idSite,
        idFailure: idFailure
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue



FailureRowvue_type_script_lang_ts.render = FailureRowvue_type_template_id_62acb18a_render

/* harmony default export */ var FailureRow = (FailureRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=script&lang=ts
function TrackingFailuresvue_type_script_lang_ts_toConsumableArray(arr) { return TrackingFailuresvue_type_script_lang_ts_arrayWithoutHoles(arr) || TrackingFailuresvue_type_script_lang_ts_iterableToArray(arr) || TrackingFailuresvue_type_script_lang_ts_unsupportedIterableToArray(arr) || TrackingFailuresvue_type_script_lang_ts_nonIterableSpread(); }

function TrackingFailuresvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function TrackingFailuresvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function TrackingFailuresvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function TrackingFailuresvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(arr); }

function TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }




/* harmony default export */ var TrackingFailuresvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    FailureRow: FailureRow
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data: function data() {
    return {
      failures: [],
      sortColumn: 'idsite',
      sortReverse: false,
      isLoading: false
    };
  },
  created: function created() {
    this.fetchAll();
  },
  methods: {
    changeSortOrder: function changeSortOrder(columnToSort) {
      if (this.sortColumn === columnToSort) {
        this.sortReverse = !this.sortReverse;
      } else {
        this.sortColumn = columnToSort;
      }
    },
    fetchAll: function fetchAll() {
      var _this = this;

      this.failures = [];
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'CoreAdminHome.getTrackingFailures',
        filter_limit: '-1'
      }).then(function (failures) {
        _this.failures = failures;
        _this.isLoading = false;
      }).finally(function () {
        _this.isLoading = false;
      });
    },
    deleteAll: function deleteAll() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteAllTrackingFailures', {
        yes: function yes() {
          _this2.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteAllTrackingFailures'
          }).then(function () {
            _this2.fetchAll();
          });
        }
      });
    },
    deleteFailure: function deleteFailure(idSite, idFailure) {
      var _this3 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteThisTrackingFailure', {
        yes: function yes() {
          _this3.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteTrackingFailure',
            idSite: idSite,
            idFailure: idFailure
          }).then(function () {
            _this3.fetchAll();
          });
        }
      });
    }
  },
  computed: {
    sortedFailures: function sortedFailures() {
      var sortColumn = this.sortColumn;

      var sorted = TrackingFailuresvue_type_script_lang_ts_toConsumableArray(this.failures);

      if (this.sortReverse) {
        sorted.sort(function (lhs, rhs) {
          if (lhs[sortColumn] > rhs[sortColumn]) {
            return -1;
          }

          if (lhs[sortColumn] < rhs[sortColumn]) {
            return 1;
          }

          return 0;
        });
      } else {
        sorted.sort(function (lhs, rhs) {
          if (lhs[sortColumn] < rhs[sortColumn]) {
            return -1;
          }

          if (lhs[sortColumn] > rhs[sortColumn]) {
            return 1;
          }

          return 0;
        });
      }

      return sorted;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue



TrackingFailuresvue_type_script_lang_ts.render = TrackingFailuresvue_type_template_id_209e7186_render

/* harmony default export */ var TrackingFailures = (TrackingFailuresvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/index.ts
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
//# sourceMappingURL=CoreAdminHome.umd.js.map