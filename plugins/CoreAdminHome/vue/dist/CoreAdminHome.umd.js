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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ArchivingSettings_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: ArchivingSettings,
  scope: {
    enableBrowserTriggerArchiving: {
      angularJsBind: '<'
    },
    showSegmentArchiveTriggerInfo: {
      angularJsBind: '<'
    },
    isGeneralSettingsAdminEnabled: {
      angularJsBind: '<'
    },
    showWarningCron: {
      angularJsBind: '<'
    },
    todayArchiveTimeToLive: {
      angularJsBind: '<'
    },
    todayArchiveTimeToLiveDefault: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoArchivingSettings'
}));
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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/BrandingSettings/BrandingSettings.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var BrandingSettings_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: BrandingSettings,
  scope: {
    fileUploadEnabled: {
      angularJsBind: '<'
    },
    logosWriteable: {
      angularJsBind: '<'
    },
    useCustomLogo: {
      angularJsBind: '<'
    },
    pathUserLogoDirectory: {
      angularJsBind: '<'
    },
    pathUserLogo: {
      angularJsBind: '<'
    },
    pathUserLogoSmall: {
      angularJsBind: '<'
    },
    pathUserLogoSvg: {
      angularJsBind: '<'
    },
    hasUserLogo: {
      angularJsBind: '<'
    },
    pathUserFavicon: {
      angularJsBind: '<'
    },
    hasUserFavicon: {
      angularJsBind: '<'
    },
    isPluginsAdminEnabled: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoBrandingSettings'
}));
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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/SmtpSettings/SmtpSettings.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var SmtpSettings_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SmtpSettings,
  scope: {
    mail: {
      angularJsBind: '<'
    },
    mailTypes: {
      angularJsBind: '<'
    },
    mailEncryptions: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoSmtpSettings'
}));
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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGenerator.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var JsTrackingCodeGenerator_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: JsTrackingCodeGenerator,
  scope: {
    defaultSite: {
      angularJsBind: '<'
    },
    maxCustomVariables: {
      angularJsBind: '<'
    },
    serverSideDoNotTrackEnabled: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoJsTrackingCodeGenerator'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=0330e404


var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "image-tracking-link"
}, null, -1);

var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_2 = {
  id: "image-tracking-code-options"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_3 = ["innerHTML"];
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_4 = ["innerHTML"];
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_5 = {
  id: "image-tracking-goal-sub"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_6 = {
  class: "row"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_7 = {
  class: "col s12 m6"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_8 = {
  class: "col s12 m6"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_9 = {
  id: "image-link-output-section"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_10 = {
  id: "image-tracking-text"
};
var ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_11 = ["textContent"];
function ImageTrackingCodeGeneratorvue_type_template_id_0330e404_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('CoreAdminHome_ImageTracking'),
    anchor: "imageTracking"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro)
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro3)
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
      }, null, 8, ["model-value", "disabled", "title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "image-tracker-goal",
        options: _ctx.siteGoals,
        disabled: _ctx.isLoading,
        "model-value": _ctx.trackIdGoal,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          _ctx.trackIdGoal = $event;

          _ctx.updateTrackingCode();
        })
      }, null, 8, ["options", "disabled", "model-value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
      }, null, 8, ["model-value", "disabled", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.trackGoal]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_ImageTrackingLink')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
        ref: "trackingCode"
      }, null, 8, ImageTrackingCodeGeneratorvue_type_template_id_0330e404_hoisted_11), [[_directive_copy_to_clipboard, {}]])])])])])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=template&id=0330e404

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.vue?vue&type=script&lang=ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }




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
        var _ref2 = _slicedToArray(_ref, 3),
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



ImageTrackingCodeGeneratorvue_type_script_lang_ts.render = ImageTrackingCodeGeneratorvue_type_template_id_0330e404_render

/* harmony default export */ var ImageTrackingCodeGenerator = (ImageTrackingCodeGeneratorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ImageTrackingCodeGenerator/ImageTrackingCodeGenerator.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ImageTrackingCodeGenerator_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: ImageTrackingCodeGenerator,
  scope: {
    defaultSite: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoImageTrackingCodeGenerator'
}));
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
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || TrackingFailuresvue_type_script_lang_ts_unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function TrackingFailuresvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return TrackingFailuresvue_type_script_lang_ts_arrayLikeToArray(arr); }

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

      var sorted = _toConsumableArray(this.failures);

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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var TrackingFailures_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: TrackingFailures,
  directiveName: 'matomoTrackingFailures'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=62369881

var JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_hoisted_1 = {
  id: "javascript-text"
};
var JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_hoisted_2 = ["textContent"];
function JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_JsTrackingCodeAdvancedOptions = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("JsTrackingCodeAdvancedOptions");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
    class: "codeblock",
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trackingCode),
    ref: "trackingCode"
  }, null, 8, JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_hoisted_2), [[_directive_copy_to_clipboard, {}]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_JsTrackingCodeAdvancedOptions, {
    site: _ctx.site,
    "max-custom-variables": _ctx.maxCustomVariables,
    "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
    onUpdateTrackingCode: _ctx.updateTrackingCode,
    ref: "jsTrackingCodeAdvanceOption"
  }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])], 64);
}
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=template&id=62369881

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts



/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    defaultSite: {
      type: Object,
      required: true
    },
    maxCustomVariables: Number,
    serverSideDoNotTrackEnabled: Boolean,
    jsTag: String
  },
  components: {
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
  }
}));
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/JsTrackingCodeGenerator/JsTrackingCodeGeneratorSitesWithoutData.vue



JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts.render = JsTrackingCodeGeneratorSitesWithoutDatavue_type_template_id_62369881_render

/* harmony default export */ var JsTrackingCodeGeneratorSitesWithoutData = (JsTrackingCodeGeneratorSitesWithoutDatavue_type_script_lang_ts);
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