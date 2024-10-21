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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=554c5c7e

const _hoisted_1 = {
  class: "form-group row"
};
const _hoisted_2 = {
  class: "col s12"
};
const _hoisted_3 = {
  class: "col s12 m6"
};
const _hoisted_4 = {
  class: "form-description",
  style: {
    "margin-left": "4px"
  }
};
const _hoisted_5 = {
  for: "enableBrowserTriggerArchiving2"
};
const _hoisted_6 = ["innerHTML"];
const _hoisted_7 = {
  class: "col s12 m6"
};
const _hoisted_8 = ["innerHTML"];
const _hoisted_9 = {
  class: "form-group row"
};
const _hoisted_10 = {
  class: "col s12"
};
const _hoisted_11 = {
  class: "input-field col s12 m6"
};
const _hoisted_12 = ["disabled"];
const _hoisted_13 = {
  class: "form-description"
};
const _hoisted_14 = {
  class: "col s12 m6"
};
const _hoisted_15 = {
  key: 0,
  class: "form-help"
};
const _hoisted_16 = {
  key: 0
};
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ArchivingSettings'),
    anchor: "archivingSettings",
    class: "matomo-archiving-settings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_AllowPiwikArchivingToTriggerBrowser')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "enableBrowserTriggerArchiving1",
      name: "enableBrowserTriggerArchiving",
      value: "1",
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enableBrowserTriggerArchivingValue = $event)
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.enableBrowserTriggerArchivingValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "enableBrowserTriggerArchiving2",
      name: "enableBrowserTriggerArchiving",
      value: "0",
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.enableBrowserTriggerArchivingValue = $event)
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
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.todayArchiveTimeToLiveValue = $event),
      id: "todayArchiveTimeToLive",
      disabled: !_ctx.isGeneralSettingsAdminEnabled
    }, null, 8, _hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.todayArchiveTimeToLiveValue]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RearchiveTimeIntervalOnlyForTodayReports')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [_ctx.isGeneralSettingsAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [_ctx.showWarningCron ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("strong", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NewReportsWillBeProcessedByCron')), 1), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ReportsWillBeProcessedAtMostEveryHour')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_IfArchivingIsFastYouCanSetupCronRunMoreOften')), 1), _hoisted_18])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_SmallTrafficYouCanLeaveDefault', _ctx.todayArchiveTimeToLiveDefault)) + " ", 1), _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MediumToHighTrafficItIsRecommendedTo', 1800, 3600)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.isLoading,
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.save())
    }, null, 8, ["saving"])])])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=554c5c7e

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts



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
  data() {
    return {
      isLoading: false,
      enableBrowserTriggerArchivingValue: this.enableBrowserTriggerArchiving ? 1 : 0,
      todayArchiveTimeToLiveValue: this.todayArchiveTimeToLive
    };
  },
  watch: {
    enableBrowserTriggerArchiving(newValue) {
      this.enableBrowserTriggerArchivingValue = newValue ? 1 : 0;
    },
    todayArchiveTimeToLive(newValue) {
      this.todayArchiveTimeToLiveValue = newValue;
    }
  },
  computed: {
    archivingTriggerDesc() {
      let result = '';
      result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerDescription', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/setup-auto-archiving/'), '</a>');
      if (this.showSegmentArchiveTriggerInfo) {
        result += Object(external_CoreHome_["translate"])('General_ArchivingTriggerSegment');
      }
      return result;
    },
    archivingInlineHelp() {
      let result = Object(external_CoreHome_["translate"])('General_ArchivingInlineHelp');
      result += '<br/>';
      result += Object(external_CoreHome_["translate"])('General_SeeTheOfficialDocumentationForMoreInformation', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/setup-auto-archiving/'), '</a>');
      return result;
    }
  },
  methods: {
    save() {
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setArchiveSettings'
      }, {
        enableBrowserTriggerArchiving: this.enableBrowserTriggerArchivingValue,
        todayArchiveTimeToLive: this.todayArchiveTimeToLiveValue
      }).then(() => {
        this.isLoading = false;
        const notificationId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue



ArchivingSettingsvue_type_script_lang_ts.render = render

/* harmony default export */ var ArchivingSettings = (ArchivingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=83e81732

const BrandingSettingsvue_type_template_id_83e81732_hoisted_1 = {
  id: "logoSettings"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_2 = {
  id: "logoUploadForm",
  ref: "logoUploadForm",
  method: "post",
  enctype: "multipart/form-data",
  action: "index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_3 = {
  key: 0
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_4 = ["value"];
const BrandingSettingsvue_type_template_id_83e81732_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);
const BrandingSettingsvue_type_template_id_83e81732_hoisted_6 = {
  key: 0
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_7 = {
  key: 0,
  class: "alert alert-warning uploaderror"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_8 = {
  class: "row"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_9 = {
  class: "col s12"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_10 = ["src"];
const BrandingSettingsvue_type_template_id_83e81732_hoisted_11 = {
  class: "row"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_12 = {
  class: "col s12"
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_13 = ["src"];
const BrandingSettingsvue_type_template_id_83e81732_hoisted_14 = {
  key: 1
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_15 = ["innerHTML"];
const BrandingSettingsvue_type_template_id_83e81732_hoisted_16 = {
  key: 1
};
const BrandingSettingsvue_type_template_id_83e81732_hoisted_17 = {
  class: "alert alert-warning"
};
function BrandingSettingsvue_type_template_id_83e81732_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_BrandingSettings'),
    anchor: "brandingSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_CustomLogoHelpText')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      name: "useCustomLogo",
      uicontrol: "checkbox",
      "model-value": _ctx.enabled,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.onUseCustomLogoChange($event)),
      title: _ctx.translate('CoreAdminHome_UseCustomLogo'),
      "inline-help": _ctx.help
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", BrandingSettingsvue_type_template_id_83e81732_hoisted_2, [_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "token_auth",
      value: _ctx.tokenAuth
    }, null, 8, BrandingSettingsvue_type_template_id_83e81732_hoisted_4), BrandingSettingsvue_type_template_id_83e81732_hoisted_5, _ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
      name: "fade-out"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.showUploadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LogoUploadFailed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "file",
      name: "customLogo",
      "model-value": _ctx.customLogo,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.onCustomLogoChange($event)),
      title: _ctx.translate('CoreAdminHome_LogoUpload'),
      "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '110')
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: _ctx.pathUserLogoWithBuster,
      id: "currentLogo",
      style: {
        "max-height": "150px"
      },
      ref: "currentLogo"
    }, null, 8, BrandingSettingsvue_type_template_id_83e81732_hoisted_10)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "file",
      name: "customFavicon",
      "model-value": _ctx.customFavicon,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.onFaviconChange($event)),
      title: _ctx.translate('CoreAdminHome_FaviconUpload'),
      "inline-help": _ctx.translate('CoreAdminHome_LogoUploadHelp', 'JPG / PNG / GIF', '16')
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: _ctx.pathUserFaviconWithBuster,
      id: "currentFavicon",
      width: "16",
      height: "16",
      ref: "currentFavicon"
    }, null, 8, BrandingSettingsvue_type_template_id_83e81732_hoisted_13)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.logosWriteable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "alert alert-warning",
      innerHTML: _ctx.$sanitize(_ctx.logosNotWriteableWarning)
    }, null, 8, BrandingSettingsvue_type_template_id_83e81732_hoisted_15)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.fileUploadEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", BrandingSettingsvue_type_template_id_83e81732_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_FileUploadDisabled', "file_uploads=1")), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.save()),
      saving: _ctx.isLoading
    }, null, 8, ["saving"])])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=template&id=83e81732

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts



const {
  $: BrandingSettingsvue_type_script_lang_ts_$
} = window;
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
  data() {
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
    tokenAuth() {
      return external_CoreHome_["Matomo"].token_auth;
    },
    logosNotWriteableWarning() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_LogoNotWriteableInstruction', `<code>${this.pathUserLogoDirectory}</code><br/>`, `${this.pathUserLogo}, ${this.pathUserLogoSmall}, ${this.pathUserLogoSvg}`);
    },
    help() {
      if (!this.isPluginsAdminEnabled) {
        return undefined;
      }
      const giveUsFeedbackText = `"${Object(external_CoreHome_["translate"])('General_GiveUsYourFeedback')}"`;
      const linkStart = '<a href="?module=CorePluginsAdmin&action=plugins" ' + 'rel="noreferrer noopener" target="_blank">';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_CustomLogoFeedbackInfo', giveUsFeedbackText, linkStart, '</a>');
    },
    pathUserLogoWithBuster() {
      if (this.currentLogoSrcExists && this.pathUserLogo) {
        return `${this.pathUserLogo}?${this.currentLogoCacheBuster}`;
      }
      return '';
    },
    pathUserFaviconWithBuster() {
      if (this.currentFaviconSrcExists && this.pathUserFavicon) {
        return `${this.pathUserFavicon}?${this.currentFaviconCacheBuster}`;
      }
      return '';
    }
  },
  methods: {
    onUseCustomLogoChange(newValue) {
      this.enabled = newValue;
    },
    onCustomLogoChange(newValue) {
      this.customLogo = newValue;
      this.updateLogo();
    },
    onFaviconChange(newValue) {
      this.customFavicon = newValue;
      this.updateLogo();
    },
    save() {
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'CoreAdminHome.setBrandingSettings'
      }, {
        useCustomLogo: this.enabled ? '1' : '0'
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
    updateLogo() {
      const isSubmittingLogo = !!this.customLogo;
      const isSubmittingFavicon = !!this.customFavicon;
      if (!isSubmittingLogo && !isSubmittingFavicon) {
        return;
      }
      this.showUploadError = false;
      const frameName = `upload${new Date().getTime()}`;
      const uploadFrame = BrandingSettingsvue_type_script_lang_ts_$(`<iframe name="${frameName}" />`);
      uploadFrame.css('display', 'none');
      uploadFrame.on('load', () => {
        setTimeout(() => {
          const frameContent = (BrandingSettingsvue_type_script_lang_ts_$(uploadFrame.contents()).find('body').html() || '').trim();
          if (frameContent === '0') {
            this.showUploadError = true;
          } else {
            // Upload succeed, so we update the images availability
            // according to what have been uploaded
            if (isSubmittingLogo) {
              this.currentLogoSrcExists = true;
              this.currentLogoCacheBuster = new Date().getTime(); // force re-fetch
            }
            if (isSubmittingFavicon) {
              this.currentFaviconSrcExists = true;
              this.currentFaviconCacheBuster = new Date().getTime(); // force re-fetch
            }
          }
          if (frameContent === '1' || frameContent === '0') {
            uploadFrame.remove();
          }
        }, 1000);
      });
      BrandingSettingsvue_type_script_lang_ts_$('body:first').append(uploadFrame);
      const submittingForm = BrandingSettingsvue_type_script_lang_ts_$(this.$refs.logoUploadForm);
      submittingForm.attr('target', frameName);
      submittingForm.submit();
      this.customLogo = '';
      this.customFavicon = '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.vue



BrandingSettingsvue_type_script_lang_ts.render = BrandingSettingsvue_type_template_id_83e81732_render

/* harmony default export */ var BrandingSettings = (BrandingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=6d8f555e

const SmtpSettingsvue_type_template_id_6d8f555e_hoisted_1 = {
  id: "smtpSettings"
};
function SmtpSettingsvue_type_template_id_6d8f555e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_EmailServerSettings'),
    anchor: "mailSettings"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "mailUseSmtp",
      modelValue: _ctx.enabled,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enabled = $event),
      title: _ctx.translate('General_UseSMTPServerForEmail'),
      "inline-help": _ctx.translate('General_SelectYesIfYouWantToSendEmailsViaServer')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SmtpSettingsvue_type_template_id_6d8f555e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailHost",
      "model-value": _ctx.mailHost,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.onUpdateMailHost($event)),
      title: _ctx.translate('General_SmtpServerAddress')
    }, null, 8, ["model-value", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailPort",
      modelValue: _ctx.mailPort,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.mailPort = $event),
      title: _ctx.translate('General_SmtpPort'),
      "inline-help": _ctx.translate('General_OptionalSmtpPort')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "mailType",
      modelValue: _ctx.mailType,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.mailType = $event),
      title: _ctx.translate('General_AuthenticationMethodSmtp'),
      options: _ctx.mailTypes,
      "inline-help": _ctx.translate('General_OnlyUsedIfUserPwdIsSet')
    }, null, 8, ["modelValue", "title", "options", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailUsername",
      modelValue: _ctx.mailUsername,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.mailUsername = $event),
      title: _ctx.translate('General_SmtpUsername'),
      "inline-help": _ctx.translate('General_OnlyEnterIfRequired'),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "password",
      name: "mailPassword",
      "model-value": _ctx.mailPassword,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.onMailPasswordChange($event)),
      onClick: _cache[6] || (_cache[6] = $event => {
        !_ctx.passwordChanged && $event.target.select();
      }),
      title: _ctx.translate('General_SmtpPassword'),
      "inline-help": _ctx.passwordHelp,
      autocomplete: 'off'
    }, null, 8, ["model-value", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailFromAddress",
      modelValue: _ctx.mailFromAddress,
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.mailFromAddress = $event),
      title: _ctx.translate('General_SmtpFromAddress'),
      "inline-help": _ctx.translate('General_SmtpFromEmailHelp', _ctx.mailHost),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "mailFromName",
      modelValue: _ctx.mailFromName,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.mailFromName = $event),
      title: _ctx.translate('General_SmtpFromName'),
      "inline-help": _ctx.translate('General_NameShownInTheSenderColumn'),
      autocomplete: 'off'
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "mailEncryption",
      modelValue: _ctx.mailEncryption,
      "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.mailEncryption = $event),
      title: _ctx.translate('General_SmtpEncryption'),
      options: _ctx.mailEncryptions,
      "inline-help": _ctx.translate('General_EncryptedSmtpTransport')
    }, null, 8, ["modelValue", "title", "options", "inline-help"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[10] || (_cache[10] = $event => _ctx.save()),
      saving: _ctx.isLoading
    }, null, 8, ["saving"])])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=template&id=6d8f555e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts



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
  data() {
    const mail = this.mail;
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
    passwordHelp() {
      const part1 = `${Object(external_CoreHome_["translate"])('General_OnlyEnterIfRequiredPassword')}<br/>`;
      const part2 = `${Object(external_CoreHome_["translate"])('General_WarningPasswordStored', '<strong>', '</strong>')}<br/>`;
      return `${part1}\n${part2}`;
    }
  },
  methods: {
    onUpdateMailHost(newValue) {
      this.mailHost = newValue;
      if (this.passwordChanged) {
        return;
      }
      this.mailPassword = '';
      this.passwordChanged = true;
    },
    onMailPasswordChange(newValue) {
      this.mailPassword = newValue;
      this.passwordChanged = true;
    },
    save() {
      this.isLoading = true;
      const mailSettings = {
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
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.vue



SmtpSettingsvue_type_script_lang_ts.render = SmtpSettingsvue_type_template_id_6d8f555e_render

/* harmony default export */ var SmtpSettings = (SmtpSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=template&id=259cef04

const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_1 = {
  id: "js-code-options"
};
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_4 = ["innerHTML"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_5 = ["innerHTML"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_8 = ["innerHTML"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_11 = ["innerHTML"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_14 = ["href"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_15 = ["href"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_16 = ["href"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_17 = ["href"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_18 = ["href"];
const JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_19 = ["href"];
const _hoisted_20 = ["href"];
const _hoisted_21 = {
  id: "javascript-output-section"
};
const _hoisted_22 = {
  class: "valign-wrapper trackingHelpHeader matchWidth"
};
const _hoisted_23 = {
  id: "javascript-email-button"
};
const _hoisted_24 = {
  id: "javascript-text"
};
const _hoisted_25 = ["textContent"];
function JsTrackingCodeGeneratorvue_type_template_id_259cef04_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_JsTrackingCodeAdvancedOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackingCodeAdvancedOptions");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    anchor: "javaScriptTracking",
    "content-title": _ctx.translate('CoreAdminHome_JavaScriptTracking')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTrackingIntro1')) + " ", 1), JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_2, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTrackingIntro2')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro3a)
    }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(' ' + _ctx.jsTrackingIntro3b)
    }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_5), JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_6, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro4a)
    }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_8), JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_9, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro5)
    }, null, 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_11), JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_12, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_InstallationGuides')) + " : ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/'),
      target: "_blank",
      rel: "noopener"
    }, "WordPress", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-integrate-matomo-with-squarespace-website/'),
      target: "_blank",
      rel: "noopener"
    }, "Squarespace", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_15), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-wix/'),
      target: "_blank",
      rel: "noopener"
    }, "Wix", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/how-to-install/faq_19424/'),
      target: "_blank",
      rel: "noopener"
    }, "SharePoint", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_17), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-joomla/'),
      target: "_blank",
      rel: "noopener"
    }, "Joomla", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_18), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-shopify-store/'),
      target: "_blank",
      rel: "noopener"
    }, "Shopify", 8, JsTrackingCodeGeneratorvue_type_template_id_259cef04_hoisted_19), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager/'),
      target: "_blank",
      rel: "noopener"
    }, "Google Tag Manager", 8, _hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "site",
      name: "js-tracker-website",
      class: "jsTrackingCodeWebsite",
      modelValue: _ctx.site,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.site = $event),
      ref: "site",
      introduction: _ctx.translate('General_Website')
    }, null, 8, ["modelValue", "introduction"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_JsTrackingTag')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', "</head>")), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      class: "btn",
      id: "emailJsBtn",
      onClick: _cache[1] || (_cache[1] = $event => _ctx.sendEmail())
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_EmailInstructionsButton')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
      class: "codeblock",
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
      ref: "trackingCode"
    }, null, 8, _hoisted_25), [[_directive_copy_to_clipboard, {}]])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_JsTrackingCodeAdvancedOptions, {
      site: _ctx.site,
      "max-custom-variables": _ctx.maxCustomVariables,
      "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
      onUpdateTrackingCode: _ctx.updateTrackingCode
    }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=template&id=259cef04

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=template&id=f7fad886

const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_1 = {
  class: "trackingCodeAdvancedOptions"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_2 = {
  class: "advance-option"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down"
}, null, -1);
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-up"
}, null, -1);
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_5 = {
  id: "javascript-advanced-options"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_6 = ["innerHTML"];
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_7 = {
  id: "optional-js-tracking-options"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_8 = {
  id: "jsTrackAllSubdomainsInlineHelp",
  class: "inline-help-node"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_9 = ["innerHTML"];
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_10 = ["innerHTML"];
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_11 = {
  id: "jsTrackGroupByDomainInlineHelp",
  class: "inline-help-node"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_12 = {
  id: "jsTrackAllAliasesInlineHelp",
  class: "inline-help-node"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_13 = {
  id: "javascript-tracking-visitor-cv"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_14 = {
  class: "row"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_15 = {
  class: "col s12 m3"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_16 = {
  class: "col s12 m3"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_17 = {
  class: "col s12 m6 l3"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_18 = ["onKeydown"];
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_19 = {
  class: "col s12 m6 l3"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_20 = ["onKeydown"];
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_21 = {
  class: "row"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_22 = {
  class: "col s12"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_24 = {
  id: "jsCrossDomain",
  class: "inline-help-node"
};
const JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_26 = {
  id: "jsDoNotTrackInlineHelp",
  class: "inline-help-node"
};
const _hoisted_27 = {
  key: 0
};
const _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_29 = ["innerHTML"];
const _hoisted_30 = {
  id: "js-campaign-query-param-extra"
};
const _hoisted_31 = {
  class: "row"
};
const _hoisted_32 = {
  class: "col s12"
};
const _hoisted_33 = {
  class: "row"
};
const _hoisted_34 = {
  class: "col s12"
};
function JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [!_ctx.showAdvanced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    href: "javascript:;",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showAdvanced = true, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ShowAdvancedOptions')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_3])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showAdvanced ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    href: "javascript:;",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showAdvanced = false, ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_HideAdvancedOptions')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.trackingDocumentationHelp)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.mergeSubdomainsDesc)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
  }, null, 8, JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_10)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-all-subdomains",
    "model-value": _ctx.trackAllSubdomains,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => {
      _ctx.trackAllSubdomains = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: `${_ctx.translate('CoreAdminHome_JSTracking_MergeSubdomains')} ${_ctx.currentSiteName}`,
    "inline-help": "#jsTrackAllSubdomainsInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomainDesc1', _ctx.currentSiteHost)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-group-by-domain",
    "model-value": _ctx.groupByDomain,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
      _ctx.groupByDomain = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_GroupPageTitlesByDomain'),
    "inline-help": "#jsTrackGroupByDomainInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_MergeAliasesDesc', _ctx.currentSiteAlias)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-all-aliases",
    "model-value": _ctx.trackAllAliases,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => {
      _ctx.trackAllAliases = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: `${_ctx.translate('CoreAdminHome_JSTracking_MergeAliases')} ${_ctx.currentSiteName}`,
    "inline-help": "#jsTrackAllAliasesInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-noscript",
    "model-value": _ctx.trackNoScript,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => {
      _ctx.trackNoScript = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_TrackNoScript')
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-visitor-cv-check",
    "model-value": _ctx.trackCustomVars,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => {
      _ctx.trackCustomVars = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_VisitorCustomVars'),
    "inline-help": _ctx.translate('CoreAdminHome_JSTracking_VisitorCustomVarsDesc')
  }, null, 8, ["model-value", "disabled", "title", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.maxCustomVariables > 0]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Value')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.customVars, (customVar, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "text",
      class: "custom-variable-name",
      onKeydown: $event => _ctx.onCustomVarNameKeydown($event, index),
      placeholder: "e.g. Type"
    }, null, 40, JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_18)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "text",
      class: "custom-variable-value",
      onKeydown: $event => _ctx.onCustomVarValueKeydown($event, index),
      placeholder: "e.g. Customer"
    }, null, 40, JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_20)])]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "javascript:;",
    onClick: _cache[7] || (_cache[7] = $event => _ctx.addCustomVar()),
    class: "add-custom-variable"
  }, [JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Add')), 1)])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.canAddMoreCustomVariables]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.trackCustomVars]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CrossDomain')) + " ", 1), JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_CrossDomain_NeedsMultipleDomains')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-cross-domain",
    "model-value": _ctx.crossDomain,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => {
      _ctx.crossDomain = $event;
      _ctx.updateTrackingCode();
      _ctx.onCrossDomainToggle();
    }),
    disabled: _ctx.isLoading || !_ctx.hasManySiteUrls,
    title: _ctx.translate('CoreAdminHome_JSTracking_EnableCrossDomainLinking'),
    "inline-help": "#jsCrossDomain"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_EnableDoNotTrackDesc')) + " ", 1), _ctx.serverSideDoNotTrackEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_27, [_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JSTracking_EnableDoNotTrack_AlreadyEnabled')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "javascript-tracking-do-not-track",
    "model-value": _ctx.doNotTrack,
    "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => {
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
    "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => {
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
  }, null, 8, _hoisted_29), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "custom-campaign-query-params-check",
    "model-value": _ctx.useCustomCampaignParams,
    "onUpdate:modelValue": _cache[11] || (_cache[11] = $event => {
      _ctx.useCustomCampaignParams = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CustomCampaignQueryParam'),
    "inline-help": "#jsTrackCampaignParamsInlineHelp"
  }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "custom-campaign-name-query-param",
    "model-value": _ctx.customCampaignName,
    "onUpdate:modelValue": _cache[12] || (_cache[12] = $event => {
      _ctx.customCampaignName = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignNameParam')
  }, null, 8, ["model-value", "disabled", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "custom-campaign-keyword-query-param",
    "model-value": _ctx.customCampaignKeyword,
    "onUpdate:modelValue": _cache[13] || (_cache[13] = $event => {
      _ctx.customCampaignKeyword = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignKwdParam')
  }, null, 8, ["model-value", "disabled", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.useCustomCampaignParams]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "require-consent-for-campaign-tracking",
    "model-value": _ctx.disableCampaignParameters,
    "onUpdate:modelValue": _cache[14] || (_cache[14] = $event => {
      _ctx.disableCampaignParameters = $event;
      _ctx.updateTrackingCode();
    }),
    disabled: _ctx.isLoading,
    title: _ctx.translate('CoreAdminHome_JSTracking_DisableCampaignParameters'),
    "inline-help": _ctx.translate('CoreAdminHome_JSTracking_DisableCampaignParametersDesc')
  }, null, 8, ["model-value", "disabled", "title", "inline-help"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showAdvanced]])]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=template&id=f7fad886

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=script&lang=ts



function getHostNameFromUrl(url) {
  const urlObj = new URL(url);
  return urlObj.hostname;
}
function getCustomVarArray(cvars) {
  return cvars.filter(cv => !!cv.name).map(cv => [cv.name, cv.value]);
}
const piwikHost = window.location.host;
const piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
/* harmony default export */ var JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    site: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean
  },
  data() {
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
      trackingCodeAbortController: null,
      disableCampaignParameters: false
    };
  },
  emits: ['updateTrackingCode'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  created() {
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
    this.onCustomVarNameKeydown = Object(external_CoreHome_["debounce"])(this.onCustomVarNameKeydown, 100);
    this.onCustomVarValueKeydown = Object(external_CoreHome_["debounce"])(this.onCustomVarValueKeydown, 100);
    this.addCustomVar();
  },
  watch: {
    site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    onSiteChanged(newValue) {
      const idSite = newValue.id;
      const promises = [];
      if (!this.siteUrls[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getSiteUrlsFromId',
          idSite,
          filter_limit: '-1'
        }).then(data => {
          this.siteUrls[idSite] = data || [];
        }));
      }
      if (!this.siteExcludedQueryParams[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getExcludedQueryParameters',
          idSite,
          filter_limit: '-1'
        }).then(data => {
          this.siteExcludedQueryParams[idSite] = data || [];
        }));
      }
      if (!this.siteExcludedReferrers[idSite]) {
        this.isLoading = true;
        promises.push(external_CoreHome_["AjaxHelper"].fetch({
          module: 'API',
          method: 'SitesManager.getExcludedReferrers',
          idSite,
          filter_limit: '-1'
        }).then(data => {
          this.siteExcludedReferrers[idSite] = [];
          Object.values(data || []).forEach(referrer => {
            this.siteExcludedReferrers[idSite].push(referrer.replace(/^https?:\/\//, ''));
          });
        }));
      }
      Promise.all(promises).then(() => {
        this.isLoading = false;
        this.updateCurrentSiteInfo();
        this.updateTrackingCode();
      });
    },
    updateCurrentSiteInfo() {
      if (!this.hasManySiteUrls) {
        // we make sure to disable cross domain if it has only one url or less
        this.crossDomain = false;
      }
    },
    onCrossDomainToggle() {
      if (this.crossDomain) {
        this.trackAllAliases = true;
      }
    },
    updateTrackingCode() {
      // get params used to generate JS code
      const params = {
        piwikUrl: `${piwikHost}${piwikPath}`,
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
        forceMatomoEndpoint: 1,
        disableCampaignParameters: this.disableCampaignParameters ? 1 : 0
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
      }).then(response => {
        this.trackingCodeAbortController = null;
        this.$emit('updateTrackingCode', response.value);
      });
    },
    addCustomVar() {
      if (this.canAddMoreCustomVariables) {
        this.customVars.push({
          name: '',
          value: ''
        });
      }
      this.canAddMoreCustomVariables = !!this.maxCustomVariables && this.maxCustomVariables > this.customVars.length;
    },
    onCustomVarNameKeydown(event, index) {
      setTimeout(() => {
        this.customVars[index].name = event.target.value;
        this.updateTrackingCode();
      });
    },
    onCustomVarValueKeydown(event, index) {
      setTimeout(() => {
        this.customVars[index].value = event.target.value;
        this.updateTrackingCode();
      });
    }
  },
  computed: {
    hasManySiteUrls() {
      const {
        site
      } = this;
      return this.siteUrls[site.id] && this.siteUrls[site.id].length > 1;
    },
    currentSiteHost() {
      var _this$siteUrls$this$s;
      const siteUrl = (_this$siteUrls$this$s = this.siteUrls[this.site.id]) === null || _this$siteUrls$this$s === void 0 ? void 0 : _this$siteUrls$this$s[0];
      if (!siteUrl) {
        return '';
      }
      return getHostNameFromUrl(siteUrl);
    },
    currentSiteAlias() {
      var _this$siteUrls$this$s2;
      const defaultAliasUrl = `x.${this.currentSiteHost}`;
      const alias = (_this$siteUrls$this$s2 = this.siteUrls[this.site.id]) === null || _this$siteUrls$this$s2 === void 0 ? void 0 : _this$siteUrls$this$s2[1];
      return alias || defaultAliasUrl;
    },
    currentSiteName() {
      return external_CoreHome_["Matomo"].helper.htmlEntities(this.site.name);
    },
    mergeSubdomainsDesc() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_MergeSubdomainsDesc', `x.${this.currentSiteHost}`, `y.${this.currentSiteHost}`);
    },
    learnMoreText() {
      /* eslint-disable prefer-template */
      const subdomainsLink = Object(external_CoreHome_["externalRawLink"])('https://developer.matomo.org/guides/tracking-javascript-guide') + '#measuring-domains-andor-sub-domains';
      return Object(external_CoreHome_["translate"])('General_LearnMore', ` (<a href="${subdomainsLink}" rel="noreferrer noopener" target="_blank">`, '</a>)');
    },
    jsTrackCampaignParamsInlineHelp() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CustomCampaignQueryParamDesc', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_119'), '</a>');
    },
    trackingDocumentationHelp() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingDocumentationHelp', Object(external_CoreHome_["externalLink"])('https://developer.matomo.org/guides/tracking-javascript-guide'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeAdvancedOptions.vue



JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts.render = JsTrackingCodeAdvancedOptionsvue_type_template_id_f7fad886_render

/* harmony default export */ var JsTrackingCodeAdvancedOptions = (JsTrackingCodeAdvancedOptionsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=script&lang=ts




/* harmony default export */ var JsTrackingCodeGeneratorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean
  },
  data() {
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
  created() {
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    updateTrackingCode(code) {
      this.trackingCode = code;
      const jsCodeTextarea = $(this.$refs.trackingCode);
      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: () => {
            this.isHighlighting = false;
          }
        }, 1500);
      }
    },
    onSiteChanged(newValue) {
      const idSite = newValue.id;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'API',
        format: 'json',
        method: 'SitesManager.detectConsentManager',
        idSite,
        filter_limit: '-1'
      }).then(response => {
        if (Object.prototype.hasOwnProperty.call(response, 'name')) {
          this.consentManagerName = response.name;
        }
        if (Object.prototype.hasOwnProperty.call(response, 'url')) {
          this.consentManagerUrl = response.url;
        }
        this.consentManagerIsConnected = response.isConnected;
      });
    },
    sendEmail() {
      let subjectLine = Object(external_CoreHome_["translate"])('SitesManager_EmailInstructionsSubject');
      subjectLine = encodeURIComponent(subjectLine);
      let {
        trackingCode
      } = this;
      trackingCode = trackingCode.replace(/<[^>]+>/g, '');
      let bodyText = `${Object(external_CoreHome_["translate"])('SitesManager_JsTrackingTagHelp')}. ${Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail', '\'head')}\n${trackingCode}`;
      if (this.consentManagerName !== '' && this.consentManagerUrl !== '') {
        bodyText += Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_ConsentManagerDetected', this.consentManagerName, this.consentManagerUrl);
        if (this.consentManagerIsConnected) {
          bodyText += `\n${Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_ConsentManagerConnected', this.consentManagerName)}`;
        }
      }
      bodyText = encodeURIComponent(bodyText);
      const linkText = `mailto:?subject=${subjectLine}&body=${bodyText}`;
      window.location.href = linkText;
    }
  },
  computed: {
    jsTrackingIntro3a() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro3a', Object(external_CoreHome_["externalLink"])('https://matomo.org/integrate/'), '</a>');
    },
    jsTrackingIntro3b() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro3b');
    },
    jsTrackingIntro4a() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro4', '<a href="#image-tracking-link">', '</a>');
    },
    jsTrackingIntro5() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTrackingIntro5', Object(external_CoreHome_["externalLink"])('https://developer.matomo.org/guides/tracking-javascript-guide'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.vue



JsTrackingCodeGeneratorvue_type_script_lang_ts.render = JsTrackingCodeGeneratorvue_type_template_id_259cef04_render

/* harmony default export */ var JsTrackingCodeGenerator = (JsTrackingCodeGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=06b9935e

const JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_1 = {
  class: "list-style-decimal"
};
const JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_2 = {
  id: "javascript-text"
};
const JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_3 = ["textContent"];
const JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_4 = {
  key: 0
};
function JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_JsTrackingCodeAdvancedOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackingCodeAdvancedOptions");
  const _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ol", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_JsTrackingCodeAdvancedOptionsStep')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_JsTrackingCodeAdvancedOptions, {
    site: _ctx.site,
    "max-custom-variables": _ctx.maxCustomVariables,
    "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
    onUpdateTrackingCode: _ctx.updateTrackingCode
  }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getCopyCodeStep), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
    class: "codeblock",
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
    ref: "trackingCode"
  }, null, 8, JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_3), [[_directive_copy_to_clipboard, {}]])])])]), _ctx.isJsTrackerInstallCheckAvailable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.testComponent), {
    site: _ctx.site
  }, null, 8, ["site"]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=06b9935e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts



/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
    jsTag: String,
    isJsTrackerInstallCheckAvailable: Boolean
  },
  components: {
    JsTrackingCodeAdvancedOptions: JsTrackingCodeAdvancedOptions
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data() {
    return {
      site: this.defaultSite,
      trackingCode: '',
      isHighlighting: false
    };
  },
  created() {
    if (this.jsTag) {
      this.trackingCode = this.jsTag;
    }
  },
  methods: {
    updateTrackingCode(code) {
      this.trackingCode = code;
      const jsCodeTextarea = $(this.$refs.trackingCode);
      if (jsCodeTextarea && !this.isHighlighting) {
        this.isHighlighting = true;
        jsCodeTextarea.effect('highlight', {
          complete: () => {
            this.isHighlighting = false;
          }
        }, 1500);
      }
    }
  },
  computed: {
    getCopyCodeStep() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead', '</head>');
    },
    testComponent() {
      if (this.isJsTrackerInstallCheckAvailable) {
        return Object(external_CoreHome_["useExternalPluginComponent"])('JsTrackerInstallCheck', 'JsTrackerInstallCheck');
      }
      return '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue



JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts.render = JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_06b9935e_render

/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutData = (JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=6095b7b0

const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "image-tracking-link"
}, null, -1);
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_2 = {
  id: "image-tracking-code-options"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_3 = ["innerHTML"];
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_4 = ["innerHTML"];
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_5 = {
  id: "image-tracking-goal-sub"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_6 = {
  class: "row"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_7 = {
  class: "col s12 m6"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_8 = {
  class: "col s12 m6"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_9 = {
  id: "image-link-output-section"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_10 = {
  id: "image-tracking-text"
};
const ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_11 = ["textContent"];
function ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ImageTracking'),
    anchor: "imageTracking"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro)
    }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro3)
    }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "site",
      name: "image-tracker-website",
      modelValue: _ctx.site,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.site = $event),
      introduction: _ctx.translate('General_Website')
    }, null, 8, ["modelValue", "introduction"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "image-tracker-action-name",
      "model-value": _ctx.pageName,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => {
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
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => {
        _ctx.trackGoal = $event;
        _ctx.updateTrackingCode();
      }),
      disabled: _ctx.isLoading,
      title: _ctx.translate('CoreAdminHome_TrackAGoal')
    }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "image-tracker-goal",
      options: _ctx.siteGoals,
      disabled: _ctx.isLoading,
      "model-value": _ctx.trackIdGoal,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
        _ctx.trackIdGoal = $event;
        _ctx.updateTrackingCode();
      })
    }, null, 8, ["options", "disabled", "model-value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "image-revenue",
      "model-value": _ctx.revenue,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => {
        _ctx.revenue = $event;
        _ctx.updateTrackingCode();
      }),
      disabled: _ctx.isLoading,
      "full-width": true,
      title: `${_ctx.translate('CoreAdminHome_WithOptionalRevenue')} ${_ctx.currentSiteCurrency}`
    }, null, 8, ["model-value", "disabled", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.trackGoal]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ImageTrackingLink')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
      ref: "trackingCode"
    }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_hoisted_11), [[_directive_copy_to_clipboard, {}]])])])])])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=6095b7b0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=script&lang=ts



let currencySymbols = null;
const {
  $: ImageTrackingCodeGeneratorvue_type_script_lang_ts_$
} = window;
const ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikHost = window.location.host;
const ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
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
  data() {
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
  created() {
    this.updateTrackingCode = Object(external_CoreHome_["debounce"])(this.updateTrackingCode);
    if (this.site && this.site.id) {
      this.onSiteChanged(this.site);
    }
  },
  watch: {
    site(newValue) {
      this.onSiteChanged(newValue);
    }
  },
  methods: {
    onSiteChanged(newValue) {
      this.trackIdGoal = null;
      let currencyPromise;
      if (currencySymbols) {
        currencyPromise = Promise.resolve(currencySymbols);
      } else {
        this.isLoading = true;
        currencyPromise = external_CoreHome_["AjaxHelper"].fetch({
          method: 'SitesManager.getCurrencySymbols',
          filter_limit: '-1'
        });
      }
      let sitePromise;
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
      let goalPromise;
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
      return Promise.all([currencyPromise, sitePromise, goalPromise]).then(([currencyResponse, site, goalsResponse]) => {
        this.isLoading = false;
        currencySymbols = currencyResponse;
        this.sites[newValue.id] = site;
        this.goals[newValue.id] = goalsResponse;
        this.updateTrackingCode();
      });
    },
    updateTrackingCode() {
      // get data used to generate the link
      const postParams = {
        piwikUrl: `${ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikHost}${ImageTrackingCodeGeneratorvue_type_script_lang_ts_piwikPath}`,
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
      }).then(response => {
        this.trackingCodeAbortController = null;
        this.trackingCode = response.value;
        const imageCodeTextarea = ImageTrackingCodeGeneratorvue_type_script_lang_ts_$(this.$refs.trackingCode);
        if (imageCodeTextarea && !this.isHighlighting) {
          this.isHighlighting = true;
          imageCodeTextarea.effect('highlight', {
            complete: () => {
              this.isHighlighting = false;
            }
          }, 1500);
        }
      });
    }
  },
  computed: {
    currentSiteCurrency() {
      if (!currencySymbols) {
        return '';
      }
      return currencySymbols[(this.sites[this.site.id].currency || '').toUpperCase()];
    },
    siteGoals() {
      const goalsResponse = this.goals[this.site.id];
      return [{
        key: '',
        value: Object(external_CoreHome_["translate"])('UserCountryMap_None')
      }].concat(Object.values(goalsResponse || []).map(g => ({
        key: `${g.idgoal}`,
        value: g.name
      })));
    },
    imageTrackingIntro() {
      const first = Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro1');
      const second = Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro2', '<code>&lt;noscript&gt;&lt;/noscript&gt;</code>');
      return `${first} ${second}`;
    },
    imageTrackingIntro3() {
      const link = Object(external_CoreHome_["externalRawLink"])('https://matomo.org/docs/tracking-api/reference/');
      return Object(external_CoreHome_["translate"])('CoreAdminHome_ImageTrackingIntro3', `<a href="${link}" rel="noreferrer noopener" target="_blank">`, '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue



ImageTrackingCodeGeneratorvue_type_script_lang_ts.render = ImageTrackingCodeGeneratorvue_type_template_id_6095b7b0_render

/* harmony default export */ var ImageTrackingCodeGenerator = (ImageTrackingCodeGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=a3500cc4

const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_3 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_4 = {
  class: "action"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_5 = {
  colspan: "7"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_7 = {
  class: "ui-confirm",
  id: "confirmDeleteAllTrackingFailures"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_8 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_9 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_10 = {
  class: "ui-confirm",
  id: "confirmDeleteThisTrackingFailure"
};
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_11 = ["value"];
const TrackingFailuresvue_type_template_id_a3500cc4_hoisted_12 = ["value"];
function TrackingFailuresvue_type_template_id_a3500cc4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_FailureRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FailureRow");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "matomoTrackingFailures",
    "content-title": _ctx.translate('CoreAdminHome_TrackingFailures')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingFailuresIntroduction', '2')) + " ", 1), TrackingFailuresvue_type_template_id_a3500cc4_hoisted_1, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "btn deleteAllFailures",
      type: "button",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.deleteAll()),
      value: _ctx.translate('CoreAdminHome_DeleteAllFailures')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length > 0]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading
    }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[1] || (_cache[1] = $event => _ctx.changeSortOrder('idsite'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Measurable')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[2] || (_cache[2] = $event => _ctx.changeSortOrder('problem'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Problem')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[3] || (_cache[3] = $event => _ctx.changeSortOrder('solution'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_Solution')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[4] || (_cache[4] = $event => _ctx.changeSortOrder('date_first_occurred'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Date')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[5] || (_cache[5] = $event => _ctx.changeSortOrder('url'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_ColumnPageURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
      onClick: _cache[6] || (_cache[6] = $event => _ctx.changeSortOrder('request_url'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_TrackingURL')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_NoKnownFailures')) + " ", 1), TrackingFailuresvue_type_template_id_a3500cc4_hoisted_6], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.failures.length === 0]])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedFailures, (failure, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FailureRow, {
        failure: failure,
        onDelete: _cache[7] || (_cache[7] = $event => _ctx.deleteFailure($event.idSite, $event.idFailure))
      }, null, 8, ["failure"])]);
    }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteAllTrackingFailures')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "yes",
      value: _ctx.translate('General_Yes')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "no",
      value: _ctx.translate('General_No')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_9)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TrackingFailuresvue_type_template_id_a3500cc4_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ConfirmDeleteThisTrackingFailure')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "yes",
      value: _ctx.translate('General_Yes')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "no",
      value: _ctx.translate('General_No')
    }, null, 8, TrackingFailuresvue_type_template_id_a3500cc4_hoisted_12)])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=template&id=a3500cc4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

const FailureRowvue_type_template_id_62acb18a_hoisted_1 = ["href"];
const FailureRowvue_type_template_id_62acb18a_hoisted_2 = {
  class: "datetime"
};
const FailureRowvue_type_template_id_62acb18a_hoisted_3 = ["title"];
const FailureRowvue_type_template_id_62acb18a_hoisted_4 = ["title"];
function FailureRowvue_type_template_id_62acb18a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.site_name) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.idsite) + ")", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.problem), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.solution) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noopener noreferrer",
    href: _ctx.failure.solution_url
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_LearnMore')), 9, FailureRowvue_type_template_id_62acb18a_hoisted_1), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.solution_url]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FailureRowvue_type_template_id_62acb18a_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.pretty_date_first_occurred), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.url), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    onClick: _cache[0] || (_cache[0] = $event => _ctx.showFullRequestUrl = true),
    title: _ctx.translate('CoreHome_ClickToSeeFullInformation')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.limtedRequestUrl) + "...", 9, FailureRowvue_type_template_id_62acb18a_hoisted_3), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.showFullRequestUrl]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.failure.request_url), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.failure.showFullRequestUrl]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "table-action icon-delete",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.deleteFailure(_ctx.failure.idsite, _ctx.failure.idfailure)),
    title: _ctx.translate('General_Delete')
  }, null, 8, FailureRowvue_type_template_id_62acb18a_hoisted_4)])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=template&id=62acb18a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts

/* harmony default export */ var FailureRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    failure: {
      type: Object,
      required: true
    }
  },
  emits: ['delete'],
  data() {
    return {
      showFullRequestUrl: false
    };
  },
  computed: {
    limtedRequestUrl() {
      return this.failure.request_url.substring(0, 100);
    }
  },
  methods: {
    deleteFailure(idSite, idFailure) {
      this.$emit('delete', {
        idSite,
        idFailure
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/FailureRow.vue



FailureRowvue_type_script_lang_ts.render = FailureRowvue_type_template_id_62acb18a_render

/* harmony default export */ var FailureRow = (FailureRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.vue?vue&type=script&lang=ts



/* harmony default export */ var TrackingFailuresvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    FailureRow: FailureRow
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      failures: [],
      sortColumn: 'idsite',
      sortReverse: false,
      isLoading: false
    };
  },
  created() {
    this.fetchAll();
  },
  methods: {
    changeSortOrder(columnToSort) {
      if (this.sortColumn === columnToSort) {
        this.sortReverse = !this.sortReverse;
      } else {
        this.sortColumn = columnToSort;
      }
    },
    fetchAll() {
      this.failures = [];
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'CoreAdminHome.getTrackingFailures',
        filter_limit: '-1'
      }).then(failures => {
        this.failures = failures;
        this.isLoading = false;
      }).finally(() => {
        this.isLoading = false;
      });
    },
    deleteAll() {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteAllTrackingFailures', {
        yes: () => {
          this.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteAllTrackingFailures'
          }).then(() => {
            this.fetchAll();
          });
        }
      });
    },
    deleteFailure(idSite, idFailure) {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteThisTrackingFailure', {
        yes: () => {
          this.failures = [];
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'CoreAdminHome.deleteTrackingFailure',
            idSite,
            idFailure
          }).then(() => {
            this.fetchAll();
          });
        }
      });
    }
  },
  computed: {
    sortedFailures() {
      const {
        sortColumn
      } = this;
      const sorted = [...this.failures];
      if (this.sortReverse) {
        sorted.sort((lhs, rhs) => {
          if (lhs[sortColumn] > rhs[sortColumn]) {
            return -1;
          }
          if (lhs[sortColumn] < rhs[sortColumn]) {
            return 1;
          }
          return 0;
        });
      } else {
        sorted.sort((lhs, rhs) => {
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



TrackingFailuresvue_type_script_lang_ts.render = TrackingFailuresvue_type_template_id_a3500cc4_render

/* harmony default export */ var TrackingFailures = (TrackingFailuresvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/index.ts
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
//# sourceMappingURL=CoreAdminHome.umd.js.map