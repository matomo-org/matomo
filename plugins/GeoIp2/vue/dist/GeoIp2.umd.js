(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["GeoIp2"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["GeoIp2"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/GeoIp2/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "GeoIp2Updater", function() { return /* reexport */ GeoIp2Updater; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/GeoIp2/vue/src/GeoIp2Updater/GeoIp2Updater.vue?vue&type=template&id=df77adbe

const _hoisted_1 = {
  key: 0
};
const _hoisted_2 = {
  key: 0
};
const _hoisted_3 = {
  id: "manage-geoip-dbs"
};
const _hoisted_4 = {
  class: "row",
  id: "geoipdb-screen1"
};
const _hoisted_5 = {
  class: "geoipdb-column-1 col s6"
};
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("sup", null, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("small", null, "*")], -1);
const _hoisted_7 = {
  class: "geoipdb-column-2 col s6"
};
const _hoisted_8 = ["innerHTML"];
const _hoisted_9 = {
  class: "geoipdb-column-1 col s6"
};
const _hoisted_10 = ["value"];
const _hoisted_11 = {
  class: "geoipdb-column-2 col s6"
};
const _hoisted_12 = ["value"];
const _hoisted_13 = {
  class: "row"
};
const _hoisted_14 = ["innerHTML"];
const _hoisted_15 = {
  id: "geoipdb-screen2-download"
};
const _hoisted_16 = {
  key: 1,
  id: "geoipdb-update-info"
};
const _hoisted_17 = ["innerHTML"];
const _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_20 = ["innerHTML"];
const _hoisted_21 = ["innerHTML"];
const _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_24 = {
  id: "locationProviderUpdatePeriodInlineHelp",
  class: "inline-help-node",
  ref: "inlineHelpNode"
};
const _hoisted_25 = ["innerHTML"];
const _hoisted_26 = {
  key: 1
};
const _hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_29 = ["innerHTML"];
const _hoisted_30 = ["value"];
const _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "done-updating-updater"
}, null, -1);
const _hoisted_32 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "geoipdb-update-info-error"
}, null, -1);
const _hoisted_33 = ["innerHTML"];
const _hoisted_34 = {
  key: 1
};
const _hoisted_35 = {
  class: "form-description"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Progressbar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Progressbar");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.contentTitle,
    id: "geoip-db-mangement"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.showGeoipUpdateSection ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [!_ctx.geoipDatabaseInstalled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('GeoIp2_NotManagingGeoIPDBs')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('GeoIp2_IWantToDownloadFreeGeoIP')), 1), _hoisted_6])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.purchasedGeoIpText)
    }, null, 8, _hoisted_8)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      class: "btn",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.startDownloadFreeGeoIp()),
      value: `${_ctx.translate('General_GetStarted')}...`
    }, null, 8, _hoisted_10)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      class: "btn",
      id: "start-automatic-update-geoip",
      onClick: _cache[1] || (_cache[1] = $event => _ctx.startAutomaticUpdateGeoIp()),
      value: `${_ctx.translate('General_GetStarted')}...`
    }, null, 8, _hoisted_12)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("sup", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("* "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("small", {
      innerHTML: _ctx.$sanitize(_ctx.accuracyNote)
    }, null, 8, _hoisted_14)])])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showPiwikNotManagingInfo]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Progressbar, {
      label: _ctx.freeProgressbarLabel,
      progress: _ctx.progressFreeDownload
    }, null, 8, ["label", "progress"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showFreeDownload]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.geoipDatabaseInstalled && !_ctx.downloadErrorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.geoIPUpdaterInstructions)
    }, null, 8, _hoisted_17), _hoisted_18, _hoisted_19, !!_ctx.dbipLiteUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.geoliteCityLink)
    }, null, 8, _hoisted_20)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.maxMindLinkExplanation)
    }, null, 8, _hoisted_21), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_22, _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('GeoIp2_GeoIPUpdaterIntro')) + ": ", 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.geoipDatabaseInstalled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "geoip-location-db",
      introduction: _ctx.translate('GeoIp2_LocationDatabase'),
      title: _ctx.translate('Actions_ColumnDownloadURL'),
      "inline-help": _ctx.translate('GeoIp2_LocationDatabaseHint'),
      modelValue: _ctx.locationDbUrl,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.locationDbUrl = $event)
    }, null, 8, ["introduction", "title", "inline-help", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "geoip-isp-db",
      introduction: _ctx.translate('GeoIp2_ISPDatabase'),
      title: _ctx.translate('Actions_ColumnDownloadURL'),
      "inline-help": _ctx.providerPluginHelp,
      modelValue: _ctx.ispDbUrl,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.ispDbUrl = $event),
      disabled: !_ctx.isProviderPluginActive
    }, null, 8, ["introduction", "title", "inline-help", "modelValue", "disabled"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "geoip-update-period",
      introduction: _ctx.translate('GeoIp2_DownloadNewDatabasesEvery'),
      modelValue: _ctx.updatePeriod,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.updatePeriod = $event),
      options: _ctx.updatePeriodOptions
    }, {
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_24, [_ctx.lastTimeUpdaterRun ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 0,
        innerHTML: _ctx.$sanitize(_ctx.translate('GeoIp2_UpdaterWasLastRun', _ctx.lastTimeUpdaterRun))
      }, null, 8, _hoisted_25)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('GeoIp2_UpdaterHasNotBeenRun')), 1)), _hoisted_27, _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        id: "geoip-updater-next-run-time",
        innerHTML: _ctx.$sanitize(_ctx.nextRunTimeText)
      }, null, 8, _hoisted_29)], 512)]),
      _: 1
    }, 8, ["introduction", "modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      class: "btn",
      onClick: _cache[5] || (_cache[5] = $event => _ctx.saveGeoIpLinks()),
      value: _ctx.buttonUpdateSaveText
    }, null, 8, _hoisted_30), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_31, _hoisted_32, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Progressbar, {
      progress: _ctx.progressUpdateDownload,
      label: _ctx.progressUpdateLabel
    }, null, 8, ["progress", "label"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isUpdatingGeoIpDatabase]])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.downloadErrorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: 2,
      innerHTML: _ctx.$sanitize(_ctx.downloadErrorMessage)
    }, null, 8, _hoisted_33)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('GeoIp2_CannotSetupGeoIPAutoUpdating')), 1)]))]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/GeoIp2/vue/src/GeoIp2Updater/GeoIp2Updater.vue?vue&type=template&id=df77adbe

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/GeoIp2/vue/src/GeoIp2Updater/GeoIp2Updater.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



const {
  $
} = window;
/* harmony default export */ var GeoIp2Updatervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    geoipDatabaseStartedInstalled: Boolean,
    showGeoipUpdateSection: {
      type: Boolean,
      required: true
    },
    dbipLiteUrl: {
      type: String,
      required: true
    },
    dbipLiteFilename: {
      type: String,
      required: true
    },
    geoipLocUrl: String,
    isProviderPluginActive: Boolean,
    geoipIspUrl: String,
    lastTimeUpdaterRun: String,
    geoipUpdatePeriod: String,
    updatePeriodOptions: {
      type: Object,
      required: true
    },
    nextRunTime: Number,
    nextRunTimePretty: String
  },
  components: {
    Progressbar: external_CoreHome_["Progressbar"],
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  data() {
    return {
      geoipDatabaseInstalled: !!this.geoipDatabaseStartedInstalled,
      showFreeDownload: false,
      showPiwikNotManagingInfo: true,
      progressFreeDownload: 0,
      progressUpdateDownload: 0,
      buttonUpdateSaveText: Object(external_CoreHome_["translate"])('General_Save'),
      progressUpdateLabel: '',
      locationDbUrl: this.geoipLocUrl || '',
      ispDbUrl: this.geoipIspUrl || '',
      orgDbUrl: '',
      updatePeriod: this.geoipUpdatePeriod || 'month',
      isUpdatingGeoIpDatabase: false,
      downloadErrorMessage: null,
      nextRunTimePrettyUpdated: undefined
    };
  },
  methods: {
    startDownloadFreeGeoIp() {
      this.showFreeDownload = true;
      this.showPiwikNotManagingInfo = false;
      this.progressFreeDownload = 0; // start download of free dbs
      this.downloadNextChunk('downloadFreeDBIPLiteDB', v => {
        this.progressFreeDownload = v;
      }, false, {}).then(() => {
        window.location.reload();
      }).catch(e => {
        this.geoipDatabaseInstalled = true;
        this.downloadErrorMessage = e.message;
      });
    },
    startAutomaticUpdateGeoIp() {
      this.buttonUpdateSaveText = Object(external_CoreHome_["translate"])('General_Continue');
      this.showGeoIpUpdateInfo();
    },
    showGeoIpUpdateInfo() {
      this.geoipDatabaseInstalled = true; // todo we need to replace this the proper way eventually
    },
    saveGeoIpLinks() {
      return external_CoreHome_["AjaxHelper"].post({
        period: this.updatePeriod,
        module: 'GeoIp2',
        action: 'updateGeoIPLinks'
      }, {
        loc_db: this.locationDbUrl,
        isp_db: this.ispDbUrl,
        org_db: this.orgDbUrl
      }, {
        withTokenInUrl: true
      }).then(response => this.downloadNextFileIfNeeded(response, null)).then(response => {
        this.progressUpdateLabel = '';
        this.isUpdatingGeoIpDatabase = false;
        external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('General_Done'),
          placeat: '#done-updating-updater',
          context: 'success',
          noclear: true,
          type: 'toast',
          style: {
            display: 'inline-block'
          },
          id: 'userCountryGeoIpUpdate'
        });
        this.nextRunTimePrettyUpdated = response.nextRunTime;
        $(this.$refs.inlineHelpNode).effect('highlight', {
          color: '#FFFFCB'
        }, 2000);
        return undefined;
      }).catch(e => {
        this.isUpdatingGeoIpDatabase = false;
        external_CoreHome_["NotificationsStore"].show({
          message: e.message,
          placeat: '#geoipdb-update-info-error',
          context: 'error',
          style: {
            display: 'inline-block'
          },
          id: 'userCountryGeoIpUpdate',
          type: 'transient'
        });
      });
    },
    downloadNextFileIfNeeded(response, currentDownloading) {
      if (response !== null && response !== void 0 && response.to_download) {
        const continuing = currentDownloading === response.to_download;
        this.progressUpdateDownload = 0;
        this.progressUpdateLabel = response.to_download_label;
        this.isUpdatingGeoIpDatabase = true; // start/continue download
        return this.downloadNextChunk('downloadMissingGeoIpDb', v => {
          this.progressUpdateDownload = v;
        }, continuing, {
          key: response.to_download
        }).then(r => this.downloadNextFileIfNeeded(r, response.to_download));
      }
      return Promise.resolve(response);
    },
    downloadNextChunk(action, progressBarSet, cont, extraData) {
      const data = _extends({}, extraData);
      return external_CoreHome_["AjaxHelper"].post({
        module: 'GeoIp2',
        action,
        continue: cont ? 1 : 0
      }, data, {
        withTokenInUrl: true
      }).catch(() => {
        throw new Error(Object(external_CoreHome_["translate"])('GeoIp2_FatalErrorDuringDownload'));
      }).then(response => {
        if (response.error) {
          throw new Error(response.error);
        }
        // update progress bar
        const newProgressVal = Math.floor(response.current_size / response.expected_file_size * 100);
        // if incomplete, download next chunk, otherwise, show updater manager
        progressBarSet(Math.min(newProgressVal, 100));
        if (newProgressVal < 100) {
          return this.downloadNextChunk(action, progressBarSet, true, extraData);
        }
        return response;
      });
    }
  },
  computed: {
    nextRunTimeText() {
      if (this.nextRunTimePrettyUpdated) {
        return this.nextRunTimePrettyUpdated;
      }
      if (!this.nextRunTime) {
        return Object(external_CoreHome_["translate"])('GeoIp2_UpdaterIsNotScheduledToRun');
      }
      if (this.nextRunTime * 1000 < Date.now()) {
        return Object(external_CoreHome_["translate"])('GeoIp2_UpdaterScheduledForNextRun');
      }
      return Object(external_CoreHome_["translate"])('GeoIp2_UpdaterWillRunNext', `<strong>${this.nextRunTimePretty}</strong>`);
    },
    providerPluginHelp() {
      if (this.isProviderPluginActive) {
        return undefined;
      }
      const text = Object(external_CoreHome_["translate"])('GeoIp2_ISPRequiresProviderPlugin');
      return `<div style="margin:0" class='alert alert-warning'>${text}</div>`;
    },
    contentTitle() {
      return Object(external_CoreHome_["translate"])(this.geoipDatabaseInstalled ? 'GeoIp2_SetupAutomaticUpdatesOfGeoIP' : 'GeoIp2_GeoIPDatabases');
    },
    accuracyNote() {
      return Object(external_CoreHome_["translate"])('UserCountry_GeoIpDbIpAccuracyNote', '<a href="https://dev.maxmind.com/geoip/geoip2/geolite2/?rId=piwik" rel="noreferrer noopener" target="_blank">', '</a>');
    },
    purchasedGeoIpText() {
      const maxMindLink = 'http://www.maxmind.com/en/geolocation_landing?rId=piwik';
      return Object(external_CoreHome_["translate"])('GeoIp2_IPurchasedGeoIPDBs', `<a rel="noreferrer noopener" href="${maxMindLink}" target="_blank">`, '</a>', '<a rel="noreferrer noopener" href="https://db-ip.com/db/?refid=mtm" target="_blank">', '</a>');
    },
    geoIPUpdaterInstructions() {
      return Object(external_CoreHome_["translate"])('GeoIp2_GeoIPUpdaterInstructions', '<a href="http://www.maxmind.com/?rId=piwik" rel="noreferrer noopener" target="_blank">', '</a>', '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">', '</a>');
    },
    geoliteCityLink() {
      const translation = Object(external_CoreHome_["translate"])('GeoIp2_GeoLiteCityLink', `<a rel="noreferrer noopener" href="${this.dbipLiteUrl}" target="_blank">`, this.dbipLiteUrl, '</a>');
      return `${translation}<br /><br />`;
    },
    maxMindLinkExplanation() {
      return Object(external_CoreHome_["translate"])('UserCountry_MaxMindLinkExplanation', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/how-to/' + 'how-do-i-get-the-geolocation-download-url-for-the-free-maxmind-db/'), '</a>');
    },
    freeProgressbarLabel() {
      return Object(external_CoreHome_["translate"])('GeoIp2_DownloadingDb', `<a href="${this.dbipLiteUrl}">${this.dbipLiteFilename}</a>...`);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/GeoIp2/vue/src/GeoIp2Updater/GeoIp2Updater.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/GeoIp2/vue/src/GeoIp2Updater/GeoIp2Updater.vue



GeoIp2Updatervue_type_script_lang_ts.render = render

/* harmony default export */ var GeoIp2Updater = (GeoIp2Updatervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/GeoIp2/vue/src/index.ts
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
//# sourceMappingURL=GeoIp2.umd.js.map