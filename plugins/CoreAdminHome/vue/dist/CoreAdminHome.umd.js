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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=df19edbe

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
        innerHTML: _ctx.archivingTriggerDesc,
        style: {
          "margin-left": "4px"
        }
      }, null, 8, _hoisted_6)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "form-help",
        innerHTML: _ctx.archivingInlineHelp
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
// CONCATENATED MODULE: ./plugins/CoreAdminHome/vue/src/ArchivingSettings/ArchivingSettings.vue?vue&type=template&id=df19edbe

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
        external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          type: 'transient',
          id: 'generalSettings',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification('generalSettings');
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
    }
  },
  directiveName: 'matomoArchivingSettings'
}));
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