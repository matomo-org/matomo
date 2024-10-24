(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"), require("SegmentEditor"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin", "SegmentEditor"], factory);
	else if(typeof exports === 'object')
		exports["PrivacyManager"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"), require("SegmentEditor"));
	else
		root["PrivacyManager"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"], root["SegmentEditor"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__, __WEBPACK_EXTERNAL_MODULE_f06f__) {
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

/***/ "f06f":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_f06f__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ManageGdpr", function() { return /* reexport */ ManageGdpr; });
__webpack_require__.d(__webpack_exports__, "AnonymizeIp", function() { return /* reexport */ AnonymizeIp; });
__webpack_require__.d(__webpack_exports__, "OptOutCustomizer", function() { return /* reexport */ OptOutCustomizer; });
__webpack_require__.d(__webpack_exports__, "AnonymizeLogData", function() { return /* reexport */ AnonymizeLogData; });
__webpack_require__.d(__webpack_exports__, "DoNotTrackPreference", function() { return /* reexport */ DoNotTrackPreference; });
__webpack_require__.d(__webpack_exports__, "ReportDeletionSettings", function() { return /* reexport */ ReportDeletionSettings_store; });
__webpack_require__.d(__webpack_exports__, "DeleteOldLogs", function() { return /* reexport */ DeleteOldLogs; });
__webpack_require__.d(__webpack_exports__, "DeleteOldReports", function() { return /* reexport */ DeleteOldReports; });
__webpack_require__.d(__webpack_exports__, "ScheduleReportDeletion", function() { return /* reexport */ ScheduleReportDeletion; });
__webpack_require__.d(__webpack_exports__, "AskingForConsent", function() { return /* reexport */ AskingForConsent; });
__webpack_require__.d(__webpack_exports__, "GdprOverview", function() { return /* reexport */ GdprOverview; });
__webpack_require__.d(__webpack_exports__, "PreviousAnonymizations", function() { return /* reexport */ PreviousAnonymizations; });
__webpack_require__.d(__webpack_exports__, "PrivacySettings", function() { return /* reexport */ PrivacySettings; });
__webpack_require__.d(__webpack_exports__, "UsersOptOut", function() { return /* reexport */ UsersOptOut; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=f72f2428

const _hoisted_1 = {
  class: "manageGdpr"
};
const _hoisted_2 = {
  class: "intro"
};
const _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = ["innerHTML"];
const _hoisted_8 = {
  class: "form-group row"
};
const _hoisted_9 = {
  class: "col s12 input-field"
};
const _hoisted_10 = {
  for: "gdprsite",
  class: "siteSelectorLabel"
};
const _hoisted_11 = {
  class: "sites_autocomplete"
};
const _hoisted_12 = {
  class: "form-group row segmentFilterGroup"
};
const _hoisted_13 = {
  class: "col s12"
};
const _hoisted_14 = {
  style: {
    "margin": "8px 0",
    "display": "inline-block"
  }
};
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_19 = {
  class: "checkInclude"
};
const _hoisted_20 = {
  colspan: "8"
};
const _hoisted_21 = ["title"];
const _hoisted_22 = {
  class: "checkInclude"
};
const _hoisted_23 = ["title"];
const _hoisted_24 = {
  class: "visitId"
};
const _hoisted_25 = {
  class: "visitorId"
};
const _hoisted_26 = ["title", "onClick"];
const _hoisted_27 = {
  class: "visitorIp"
};
const _hoisted_28 = ["title", "onClick"];
const _hoisted_29 = {
  class: "userId"
};
const _hoisted_30 = ["title", "onClick"];
const _hoisted_31 = ["title"];
const _hoisted_32 = ["src"];
const _hoisted_33 = ["title"];
const _hoisted_34 = ["src"];
const _hoisted_35 = ["title"];
const _hoisted_36 = ["src"];
const _hoisted_37 = ["title"];
const _hoisted_38 = ["src"];
const _hoisted_39 = ["onClick"];
const _hoisted_40 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Live/images/visitorProfileLaunch.png",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);
const _hoisted_41 = {
  class: "ui-confirm",
  id: "confirmDeleteDataSubject",
  ref: "confirmDeleteDataSubject"
};
const _hoisted_42 = ["value"];
const _hoisted_43 = ["value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");
  const _component_SegmentGenerator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentGenerator");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_GdprTools')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntro1')) + " ", 1), _hoisted_3, _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntro2')) + " ", 1), _hoisted_5]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntroAccessRight')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntroEraseRight')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.overviewHintText)
    }, null, 8, _hoisted_7)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SearchForDataSubject')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SelectWebsite')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
      id: "gdprsite",
      modelValue: _ctx.site,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.site = $event),
      "show-all-sites-item": true,
      "switch-site-on-select": false,
      "show-selected-site": true
    }, null, 8, ["modelValue"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FindDataSubjectsBy')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentGenerator, {
      modelValue: _ctx.segment_filter,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.segment_filter = $event),
      "visit-segments-only": true,
      idsite: _ctx.site.id
    }, null, 8, ["modelValue", "idsite"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      class: "findDataSubjects",
      value: _ctx.translate('PrivacyManager_FindMatchingDataSubjects'),
      onConfirm: _cache[2] || (_cache[2] = $event => _ctx.findDataSubjects()),
      disabled: !_ctx.segment_filter,
      saving: _ctx.isLoading
    }, null, 8, ["value", "disabled", "saving"])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_NoDataSubjectsFound')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.dataSubjects.length && _ctx.hasSearched]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_MatchingDataSubjects')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_VisitsMatchedCriteria')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ExportingNote')) + " ", 1), _hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(), _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeletionFromMatomoOnly')) + " ", 1), _hoisted_17, _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ResultIncludesAllVisits')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "activateAll",
    "model-value": _ctx.toggleAll,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
      _ctx.toggleAll = $event;
      _ctx.toggleActivateAll();
    }),
    "full-width": true
  }, null, 8, ["model-value"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Website')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitId')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitorID')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitorIP')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_UserId')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Details')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.profileEnabled]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ResultTruncated', '400')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataSubjects.length > 400]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dataSubjects, (dataSubject, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      title: `${_ctx.translate('PrivacyManager_LastAction')}: ${dataSubject.lastActionDateTime}`,
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: `subject${dataSubject.idVisit}`,
      modelValue: _ctx.dataSubjectsActive[index],
      "onUpdate:modelValue": $event => _ctx.dataSubjectsActive[index] = $event,
      "full-width": true
    }, null, 8, ["name", "modelValue", "onUpdate:modelValue"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      class: "site",
      title: `(${_ctx.translate('General_Id')} ${dataSubject.idSite})`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.siteName), 9, _hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.idVisit), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddVisitorIdToSearch'),
      onClick: $event => _ctx.addFilter('visitorId', dataSubject.visitorId)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.visitorId), 9, _hoisted_26)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddVisitorIPToSearch'),
      onClick: $event => _ctx.addFilter('visitIp', dataSubject.visitIp)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.visitIp), 9, _hoisted_28)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddUserIdToSearch'),
      onClick: $event => _ctx.addFilter('userId', dataSubject.userId)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.userId), 9, _hoisted_30)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: `${dataSubject.deviceType} ${dataSubject.deviceModel}`,
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.deviceTypeIcon
    }, null, 8, _hoisted_32)], 8, _hoisted_31), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: dataSubject.operatingSystem,
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.operatingSystemIcon
    }, null, 8, _hoisted_34)], 8, _hoisted_33), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: `${dataSubject.browser} ${dataSubject.browserFamilyDescription}`,
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.browserIcon
    }, null, 8, _hoisted_36)], 8, _hoisted_35), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: `${dataSubject.country} ${dataSubject.region || ''}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.countryFlag
    }, null, 8, _hoisted_38)], 8, _hoisted_37)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "visitorLogTooltip",
      title: "View visitor profile",
      onClick: $event => _ctx.showProfile(dataSubject.visitorId, dataSubject.idSite)
    }, [_hoisted_40, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_ViewVisitorProfile')), 1)], 8, _hoisted_39)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.profileEnabled]])], 8, _hoisted_21);
  }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "exportDataSubjects",
    style: {
      "margin-right": "3.5px"
    },
    onConfirm: _cache[4] || (_cache[4] = $event => _ctx.exportDataSubject()),
    disabled: !_ctx.hasActiveDataSubjects,
    value: _ctx.translate('PrivacyManager_ExportSelectedVisits')
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "deleteDataSubjects",
    onConfirm: _cache[5] || (_cache[5] = $event => _ctx.deleteDataSubject()),
    disabled: !_ctx.hasActiveDataSubjects || _ctx.isDeleting,
    value: _ctx.translate('PrivacyManager_DeleteSelectedVisits')
  }, null, 8, ["disabled", "value"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataSubjects.length]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteVisitsConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_42), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_43)], 512)]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=f72f2428

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "SegmentEditor"
var external_SegmentEditor_ = __webpack_require__("f06f");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }




/* harmony default export */ var ManageGdprvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SiteSelector: external_CoreHome_["SiteSelector"],
    SegmentGenerator: external_SegmentEditor_["SegmentGenerator"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      isLoading: false,
      isDeleting: false,
      site: {
        id: 'all',
        name: Object(external_CoreHome_["translate"])('UsersManager_AllWebsites')
      },
      segment_filter: 'userId==',
      dataSubjects: [],
      toggleAll: true,
      hasSearched: false,
      profileEnabled: external_CoreHome_["Matomo"].visitorProfileEnabled,
      dataSubjectsActive: []
    };
  },
  setup() {
    const sitesPromise = external_CoreHome_["AjaxHelper"].fetch({
      method: 'SitesManager.getSitesIdWithAdminAccess',
      filter_limit: '-1'
    });
    return {
      getSites() {
        return sitesPromise;
      }
    };
  },
  methods: {
    showSuccessNotification(message) {
      const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
        message,
        context: 'success',
        id: 'manageGdpr',
        type: 'transient'
      });
      setTimeout(() => {
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }, 200);
    },
    linkTo(action) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'PrivacyManager',
        action
      }))}`;
    },
    toggleActivateAll() {
      this.dataSubjectsActive.fill(this.toggleAll);
    },
    showProfile(visitorId, idSite) {
      external_CoreHome_["Matomo"].helper.showVisitorProfilePopup(visitorId, idSite);
    },
    exportDataSubject() {
      const visitsToDelete = this.activatedDataSubjects;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'PrivacyManager.exportDataSubjects',
        format: 'json',
        filter_limit: -1
      }, {
        visits: visitsToDelete
      }).then(visits => {
        this.showSuccessNotification(Object(external_CoreHome_["translate"])('PrivacyManager_VisitsSuccessfullyExported'));
        external_CoreHome_["Matomo"].helper.sendContentAsDownload('exported_data_subjects.json', JSON.stringify(visits));
      });
    },
    deleteDataSubject() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmDeleteDataSubject, {
        yes: () => {
          this.isDeleting = true;
          const visitsToDelete = this.activatedDataSubjects;
          external_CoreHome_["AjaxHelper"].post({
            module: 'API',
            method: 'PrivacyManager.deleteDataSubjects',
            filter_limit: -1
          }, {
            visits: visitsToDelete
          }).then(() => {
            this.dataSubjects = [];
            this.showSuccessNotification(Object(external_CoreHome_["translate"])('PrivacyManager_VisitsSuccessfullyDeleted'));
            this.findDataSubjects();
          }).finally(() => {
            this.isDeleting = false;
          });
        }
      });
    },
    addFilter(segment, value) {
      this.segment_filter += `,${segment}==${value}`;
      this.findDataSubjects();
    },
    findDataSubjects() {
      this.dataSubjects = [];
      this.dataSubjectsActive = [];
      this.isLoading = true;
      this.toggleAll = true;
      this.hasSearched = false;
      this.getSites().then(idsites => {
        let siteIds = this.site.id;
        if (siteIds === 'all' && !external_CoreHome_["Matomo"].hasSuperUserAccess) {
          // when superuser, we speed the request up a little and simply use 'all'
          siteIds = idsites;
          if (Array.isArray(idsites)) {
            siteIds = idsites.join(',');
          }
        }
        external_CoreHome_["AjaxHelper"].fetch({
          idSite: siteIds,
          module: 'API',
          method: 'PrivacyManager.findDataSubjects',
          segment: this.segment_filter
        }).then(visits => {
          this.hasSearched = true;
          this.dataSubjectsActive = visits.map(() => true);
          this.dataSubjects = visits;
        }).finally(() => {
          this.isLoading = false;
        });
      });
    }
  },
  computed: {
    hasActiveDataSubjects() {
      return !!this.activatedDataSubjects.length;
    },
    activatedDataSubjects() {
      return this.dataSubjects.filter((v, i) => this.dataSubjectsActive[i]).map(v => ({
        idsite: v.idSite,
        idvisit: v.idVisit
      }));
    },
    overviewHintText() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_GdprToolsOverviewHint', `<a href="${this.linkTo('gdprOverview')}">`, '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue



ManageGdprvue_type_script_lang_ts.render = render

/* harmony default export */ var ManageGdpr = (ManageGdprvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=3156f43b

const AnonymizeIpvue_type_template_id_3156f43b_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_5 = {
  key: 0
};
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AnonymizeIpvue_type_template_id_3156f43b_hoisted_8 = {
  class: "alert-warning alert"
};
function AnonymizeIpvue_type_template_id_3156f43b_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeIpSettings",
    title: _ctx.translate('PrivacyManager_UseAnonymizeIp'),
    modelValue: _ctx.actualEnabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.actualEnabled = $event),
    "inline-help": _ctx.anonymizeIpEnabledHelp
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "maskLength",
    title: _ctx.translate('PrivacyManager_AnonymizeIpMaskLengtDescription'),
    modelValue: _ctx.actualMaskLength,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.actualMaskLength = $event),
    options: _ctx.maskLengthOptions,
    "inline-help": _ctx.translate('PrivacyManager_GeolocationAnonymizeIpNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "useAnonymizedIpForVisitEnrichment",
    title: _ctx.translate('PrivacyManager_UseAnonymizedIpForVisitEnrichment'),
    modelValue: _ctx.actualUseAnonymizedIpForVisitEnrichment,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.actualUseAnonymizedIpForVisitEnrichment = $event),
    options: _ctx.useAnonymizedIpForVisitEnrichmentOptions,
    "inline-help": _ctx.translate('PrivacyManager_UseAnonymizedIpForVisitEnrichmentNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.actualEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeUserId",
    title: _ctx.translate('PrivacyManager_PseudonymizeUserId'),
    modelValue: _ctx.actualAnonymizeUserId,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.actualAnonymizeUserId = $event)
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote')) + " ", 1), AnonymizeIpvue_type_template_id_3156f43b_hoisted_1, AnonymizeIpvue_type_template_id_3156f43b_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote2')), 1)]),
    _: 1
  }, 8, ["title", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeOrderId",
    title: _ctx.translate('PrivacyManager_UseAnonymizeOrderId'),
    modelValue: _ctx.actualAnonymizeOrderId,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.actualAnonymizeOrderId = $event),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeOrderIdNote')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "forceCookielessTracking",
    title: _ctx.translate('PrivacyManager_ForceCookielessTracking'),
    modelValue: _ctx.actualForceCookielessTracking,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.actualForceCookielessTracking = $event)
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription', _ctx.trackerFileName)) + " ", 1), AnonymizeIpvue_type_template_id_3156f43b_hoisted_3, AnonymizeIpvue_type_template_id_3156f43b_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription2')), 1), !_ctx.trackerWritable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", AnonymizeIpvue_type_template_id_3156f43b_hoisted_5, [AnonymizeIpvue_type_template_id_3156f43b_hoisted_6, AnonymizeIpvue_type_template_id_3156f43b_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", AnonymizeIpvue_type_template_id_3156f43b_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescriptionNotWritable', _ctx.trackerFileName)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["title", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "anonymizeReferrer",
    title: _ctx.translate('PrivacyManager_AnonymizeReferrer'),
    modelValue: _ctx.actualAnonymizeReferrer,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.actualAnonymizeReferrer = $event),
    options: _ctx.referrerAnonymizationOptions,
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeReferrerNote')
  }, null, 8, ["title", "modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[7] || (_cache[7] = $event => _ctx.save()),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])])), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=3156f43b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=script&lang=ts



function configBoolToInt(value) {
  return value === true || value === 1 || value === '1' ? 1 : 0;
}
/* harmony default export */ var AnonymizeIpvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    anonymizeIpEnabled: Boolean,
    anonymizeUserId: Boolean,
    maskLength: {
      type: Number,
      required: true
    },
    useAnonymizedIpForVisitEnrichment: [Boolean, String, Number],
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
  data() {
    return {
      isLoading: false,
      actualEnabled: this.anonymizeIpEnabled,
      actualMaskLength: this.maskLength,
      actualUseAnonymizedIpForVisitEnrichment: configBoolToInt(this.useAnonymizedIpForVisitEnrichment),
      actualAnonymizeUserId: !!this.anonymizeUserId,
      actualAnonymizeOrderId: !!this.anonymizeOrderId,
      actualForceCookielessTracking: !!this.forceCookielessTracking,
      actualAnonymizeReferrer: this.anonymizeReferrer
    };
  },
  methods: {
    save() {
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
        useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'toast'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  },
  computed: {
    anonymizeIpEnabledHelp() {
      const inlineHelp1 = Object(external_CoreHome_["translate"])('PrivacyManager_AnonymizeIpInlineHelp');
      const inlineHelp2 = Object(external_CoreHome_["translate"])('PrivacyManager_AnonymizeIpDescription');
      return `${inlineHelp1} ${inlineHelp2}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue



AnonymizeIpvue_type_script_lang_ts.render = AnonymizeIpvue_type_template_id_3156f43b_render

/* harmony default export */ var AnonymizeIp = (AnonymizeIpvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=6cbe5d69

const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_1 = {
  class: "optOutCustomizer"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_2 = ["innerHTML"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_3 = {
  key: 0,
  id: "opt-out-styling"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_4 = ["value"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_5 = ["value"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_6 = ["value"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_7 = ["value"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<option value=\"px\">px</option><option value=\"pt\">pt</option><option value=\"em\">em</option><option value=\"rem\">rem</option><option value=\"%\">%</option>", 5);
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_13 = [OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_8];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_14 = ["value"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_15 = ["src"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_16 = {
  class: "form-group row"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_17 = {
  class: "col s12 m6"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_18 = {
  for: "codeType1"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_19 = {
  for: "codeType2"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_20 = {
  key: 0
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_21 = {
  class: "col s12 m6"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_22 = ["innerHTML"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_23 = {
  ref: "pre"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_24 = ["innerHTML"];
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_25 = {
  class: "system notification notification-info optOutTestReminder"
};
const OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_26 = ["innerHTML"];
function OptOutCustomizervue_type_template_id_6cbe5d69_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_OptOutExplanation')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.readThisToLearnMore)
  }, null, 8, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutAppearance')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "applyStyling",
    type: "checkbox",
    name: "applyStyling",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.applyStyling = $event),
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.updateCode()),
    onChange: _cache[2] || (_cache[2] = $event => _ctx.updateCode())
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.applyStyling]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ApplyStyling')), 1)])])]), _ctx.applyStyling ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.fontColor,
    onKeydown: _cache[3] || (_cache[3] = $event => _ctx.onFontColorChange($event)),
    onChange: _cache[4] || (_cache[4] = $event => _ctx.onFontColorChange($event))
  }, null, 40, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_BackgroundColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.backgroundColor,
    onKeydown: _cache[5] || (_cache[5] = $event => _ctx.onBgColorChange($event)),
    onChange: _cache[6] || (_cache[6] = $event => _ctx.onBgColorChange($event))
  }, null, 40, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_5)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontSize')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontSizeInput",
    type: "number",
    min: "1",
    max: "100",
    value: _ctx.fontSize,
    onKeydown: _cache[7] || (_cache[7] = $event => _ctx.onFontSizeChange($event)),
    onChange: _cache[8] || (_cache[8] = $event => _ctx.onFontSizeChange($event))
  }, null, 40, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_6)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default",
    value: _ctx.fontSizeUnit,
    onKeydown: _cache[9] || (_cache[9] = $event => _ctx.onFontSizeUnitChange($event)),
    onChange: _cache[10] || (_cache[10] = $event => _ctx.onFontSizeUnitChange($event))
  }, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_13, 40, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_7)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontFamily')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontFamilyInput",
    type: "text",
    value: _ctx.fontFamily,
    onKeydown: _cache[11] || (_cache[11] = $event => _ctx.onFontFamilyChange($event)),
    onChange: _cache[12] || (_cache[12] = $event => _ctx.onFontFamilyChange($event))
  }, null, 40, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_14)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "showIntro",
    type: "checkbox",
    name: "showIntro",
    "onUpdate:modelValue": _cache[13] || (_cache[13] = $event => _ctx.showIntro = $event),
    onKeydown: _cache[14] || (_cache[14] = $event => _ctx.updateCode()),
    onChange: _cache[15] || (_cache[15] = $event => _ctx.updateCode())
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.showIntro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ShowIntro')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutPreview')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("iframe", {
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
  }, null, 10, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_15)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutHtmlCode')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "radio",
    id: "codeType1",
    name: "codeType",
    value: "tracker",
    "onUpdate:modelValue": _cache[16] || (_cache[16] = $event => _ctx.codeType = $event),
    onKeydown: _cache[17] || (_cache[17] = $event => _ctx.updateCode()),
    onChange: _cache[18] || (_cache[18] = $event => _ctx.updateCode())
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.codeType]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutUseTracker')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "radio",
    id: "codeType2",
    name: "codeType",
    value: "selfContained",
    "onUpdate:modelValue": _cache[19] || (_cache[19] = $event => _ctx.codeType = $event),
    onKeydown: _cache[20] || (_cache[20] = $event => _ctx.updateCode()),
    onChange: _cache[21] || (_cache[21] = $event => _ctx.updateCode())
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.codeType]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutUseStandalone')), 1)])]), _ctx.codeType === 'selfContained' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "language",
    modelValue: _ctx.language,
    "onUpdate:modelValue": _cache[22] || (_cache[22] = $event => _ctx.language = $event),
    title: _ctx.translate('General_Language'),
    options: _ctx.languageOptions,
    onKeydown: _cache[23] || (_cache[23] = $event => _ctx.updateCode()),
    onChange: _cache[24] || (_cache[24] = $event => _ctx.updateCode())
  }, null, 8, ["modelValue", "title", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "form-help",
    innerHTML: _ctx.$sanitize(_ctx.codeTypeHelp)
  }, null, 8, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_22)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("pre", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.codeBox) + "\n      ", 1)])), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.optOutExplanationIntro)
  }, null, 8, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTest')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestBody')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep1')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep3')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep4')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_BuildYourOwn')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.optOutCustomOptOutLink)
  }, null, 8, OptOutCustomizervue_type_template_id_6cbe5d69_hoisted_26)])], 64);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=6cbe5d69

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=script&lang=ts
/* eslint-disable no-mixed-operators */
/* eslint-disable no-bitwise */



function nearlyWhite(hex) {
  const bigint = parseInt(hex, 16);
  const r = bigint >> 16 & 255;
  const g = bigint >> 8 & 255;
  const b = bigint & 255;
  return r >= 225 && g >= 225 && b >= 225;
}
const {
  $: OptOutCustomizervue_type_script_lang_ts_$
} = window;
/* harmony default export */ var OptOutCustomizervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentLanguageCode: {
      type: String,
      required: true
    },
    languageOptions: {
      type: Object,
      required: true
    },
    matomoUrl: String
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data() {
    return {
      fontSizeUnit: 'px',
      backgroundColor: '#FFFFFF',
      fontColor: '#000000',
      fontSize: '12',
      fontFamily: 'Arial',
      showIntro: true,
      applyStyling: false,
      codeType: 'tracker',
      code: '',
      language: this.currentLanguageCode
    };
  },
  created() {
    this.onFontColorChange = Object(external_CoreHome_["debounce"])(this.onFontColorChange, 50);
    this.onBgColorChange = Object(external_CoreHome_["debounce"])(this.onBgColorChange, 50);
    this.onFontSizeChange = Object(external_CoreHome_["debounce"])(this.onFontSizeChange, 50);
    this.onFontSizeUnitChange = Object(external_CoreHome_["debounce"])(this.onFontSizeUnitChange, 50);
    this.onFontFamilyChange = Object(external_CoreHome_["debounce"])(this.onFontFamilyChange, 50);
    if (this.matomoUrl) {
      this.updateCode();
    }
  },
  methods: {
    onFontColorChange(event) {
      this.fontColor = event.target.value;
      this.updateCode();
    },
    onBgColorChange(event) {
      this.backgroundColor = event.target.value;
      this.updateCode();
    },
    onFontSizeChange(event) {
      this.fontSize = event.target.value;
      this.updateCode();
    },
    onFontSizeUnitChange(event) {
      this.fontSizeUnit = event.target.value;
      this.updateCode();
    },
    onFontFamilyChange(event) {
      this.fontFamily = event.target.value;
      this.updateCode();
    },
    updateCode() {
      let methodName = 'CoreAdminHome.getOptOutJSEmbedCode';
      if (this.codeType === 'selfContained') {
        methodName = 'CoreAdminHome.getOptOutSelfContainedEmbedCode';
      }
      external_CoreHome_["AjaxHelper"].fetch({
        method: methodName,
        backgroundColor: this.backgroundColor.substr(1),
        fontColor: this.fontColor.substr(1),
        fontSize: this.fontSizeWithUnit,
        fontFamily: this.fontFamily,
        showIntro: this.showIntro === true ? 1 : 0,
        applyStyling: this.applyStyling === true ? 1 : 0,
        matomoUrl: this.matomoUrl,
        language: this.codeType === 'selfContained' ? this.language : 'auto'
      }).then(data => {
        this.code = data.value || '';
      });
    }
  },
  watch: {
    codeBox() {
      const pre = this.$refs.pre;
      const isAnimationAlreadyRunning = OptOutCustomizervue_type_script_lang_ts_$(pre).queue('fx').length > 0;
      if (!isAnimationAlreadyRunning) {
        OptOutCustomizervue_type_script_lang_ts_$(pre).effect('highlight', {}, 1500);
      }
    }
  },
  computed: {
    fontSizeWithUnit() {
      if (this.fontSize) {
        return `${this.fontSize}${this.fontSizeUnit}`;
      }
      return '';
    },
    withBg() {
      return !!this.matomoUrl && this.backgroundColor === '' && this.fontColor !== '' && nearlyWhite(this.fontColor.slice(1));
    },
    codeBox() {
      if (this.matomoUrl) {
        return this.code;
      }
      return '';
    },
    iframeUrl() {
      const query = external_CoreHome_["MatomoUrl"].stringify({
        module: 'CoreAdminHome',
        action: 'optOut',
        language: this.language,
        backgroundColor: this.backgroundColor.substr(1),
        fontColor: this.fontColor.substr(1),
        fontSize: this.fontSizeWithUnit,
        fontFamily: this.fontFamily,
        applyStyling: this.applyStyling === true ? 1 : 0,
        showIntro: this.showIntro === true ? 1 : 0
      });
      return `${this.matomoUrl}index.php?${query}`;
    },
    readThisToLearnMore() {
      return Object(external_CoreHome_["translate"])('General_ReadThisToLearnMore', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/how-to/faq_25918/'), '</a>');
    },
    optOutExplanationIntro() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_OptOutExplanationIntro', `<a href="${this.iframeUrl}" rel="noreferrer noopener" target="_blank">`, '</a>');
    },
    optOutCustomOptOutLink() {
      const link = 'https://developer.matomo.org/guides/tracking-javascript-guide#optional-creating-a-custom-opt-out-form';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_OptOutCustomOptOutLink', Object(external_CoreHome_["externalLink"])(link), '</a>');
    },
    codeTypeHelp() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_OptOutCodeTypeExplanation');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue



OptOutCustomizervue_type_script_lang_ts.render = OptOutCustomizervue_type_template_id_6cbe5d69_render

/* harmony default export */ var OptOutCustomizer = (OptOutCustomizervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=4d965cd4

const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_1 = {
  class: "anonymizeLogData"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_2 = {
  class: "form-group row"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_3 = {
  class: "col s12 input-field"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_4 = {
  for: "anonymizeSite",
  class: "siteSelectorLabel"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_5 = {
  class: "sites_autocomplete"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_6 = {
  class: "form-group row"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_7 = {
  class: "col s6 input-field"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_8 = {
  for: "anonymizeStartDate",
  class: "active"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_9 = ["value"];
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_10 = {
  class: "col s6 input-field"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_11 = {
  for: "anonymizeEndDate",
  class: "active"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_12 = ["value"];
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_13 = {
  name: "anonymizeIp"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_14 = {
  name: "anonymizeLocation"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_15 = {
  name: "anonymizeTheUserId"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_16 = {
  class: "form-group row"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_17 = {
  class: "col s12 m6"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_18 = {
  for: "visit_columns"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_19 = {
  class: "innerFormField",
  name: "visit_columns"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_20 = ["onClick", "title"];
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_21 = {
  class: "col s12 m6"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_22 = {
  class: "form-help"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_23 = {
  class: "inline-help"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_24 = {
  class: "form-group row"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_25 = {
  class: "col s12"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_26 = {
  class: "form-group row"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_27 = {
  class: "col s12 m6"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_28 = {
  for: "action_columns"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_29 = {
  class: "innerFormField",
  name: "action_columns"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_30 = ["onClick", "title"];
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_31 = {
  class: "col s12 m6"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_32 = {
  class: "form-help"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_33 = {
  class: "inline-help"
};
const AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_34 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);
function AnonymizeLogDatavue_type_template_id_4d965cd4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeSites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
    id: "anonymizeSite",
    modelValue: _ctx.site,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.site = $event),
    "show-all-sites-item": true,
    "switch-site-on-select": false,
    "show-selected-site": true
  }, null, 8, ["modelValue"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataFrom')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    id: "anonymizeStartDate",
    class: "anonymizeStartDate",
    ref: "anonymizeStartDate",
    name: "anonymizeStartDate",
    value: _ctx.startDate,
    onKeydown: _cache[1] || (_cache[1] = $event => _ctx.onKeydownStartDate($event)),
    onChange: _cache[2] || (_cache[2] = $event => _ctx.onKeydownStartDate($event))
  }, null, 40, AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_9)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "anonymizeEndDate",
    id: "anonymizeEndDate",
    ref: "anonymizeEndDate",
    name: "anonymizeEndDate",
    value: _ctx.endDate,
    onKeydown: _cache[3] || (_cache[3] = $event => _ctx.onKeydownEndDate($event)),
    onChange: _cache[4] || (_cache[4] = $event => _ctx.onKeydownEndDate($event))
  }, null, 40, AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_12)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeIp",
    title: _ctx.translate('PrivacyManager_AnonymizeIp'),
    modelValue: _ctx.anonymizeIp,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.anonymizeIp = $event),
    introduction: _ctx.translate('General_Visit'),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeIpHelp')
  }, null, 8, ["title", "modelValue", "introduction", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeLocation",
    title: _ctx.translate('PrivacyManager_AnonymizeLocation'),
    modelValue: _ctx.anonymizeLocation,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.anonymizeLocation = $event),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeLocationHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeTheUserId",
    title: _ctx.translate('PrivacyManager_AnonymizeUserId'),
    modelValue: _ctx.anonymizeUserId,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.anonymizeUserId = $event),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeUserIdHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedVisitColumns, (visitColumn, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`selectedVisitColumns selectedVisitColumns${index} multiple valign-wrapper`),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "visit_columns",
      "model-value": visitColumn.column,
      "onUpdate:modelValue": $event => {
        visitColumn.column = $event;
        _ctx.onVisitColumnChange();
      },
      "full-width": true,
      options: _ctx.availableVisitColumns
    }, null, 8, ["model-value", "onUpdate:modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-minus valign",
      onClick: $event => _ctx.removeVisitColumn(index),
      title: _ctx.translate('General_Remove')
    }, null, 8, AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_20), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedVisitColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedActionColumns, (actionColumn, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`selectedActionColumns selectedActionColumns${index} multiple valign-wrapper`),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "action_columns",
      "model-value": actionColumn.column,
      "onUpdate:modelValue": $event => {
        actionColumn.column = $event;
        _ctx.onActionColumnChange();
      },
      "full-width": true,
      options: _ctx.availableActionColumns
    }, null, 8, ["model-value", "onUpdate:modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-minus valign",
      onClick: $event => _ctx.removeActionColumn(index),
      title: _ctx.translate('General_Remove')
    }, null, 8, AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_30), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedActionColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [AnonymizeLogDatavue_type_template_id_4d965cd4_hoisted_34, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeProcessInfo')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "anonymizePastData",
    onConfirm: _cache[8] || (_cache[8] = $event => _ctx.showPasswordConfirmModal = true),
    disabled: _ctx.isAnonymizePastDataDisabled,
    value: _ctx.translate('PrivacyManager_AnonymizeDataNow')
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.showPasswordConfirmModal = $event),
    onConfirmed: _ctx.scheduleAnonymization
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeDataConfirm')), 1)]),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=4d965cd4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=script&lang=ts



function sub(value) {
  if (value < 10) {
    return `0${value}`;
  }
  return value;
}
/* harmony default export */ var AnonymizeLogDatavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"],
    SiteSelector: external_CoreHome_["SiteSelector"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data() {
    const now = new Date();
    const startDate = `${now.getFullYear()}-${sub(now.getMonth() + 1)}-${sub(now.getDay() + 1)}`;
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
      startDate,
      endDate: startDate,
      showPasswordConfirmModal: false
    };
  },
  created() {
    this.onKeydownStartDate = Object(external_CoreHome_["debounce"])(this.onKeydownStartDate, 50);
    this.onKeydownEndDate = Object(external_CoreHome_["debounce"])(this.onKeydownEndDate, 50);
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'PrivacyManager.getAvailableVisitColumnsToAnonymize'
    }).then(columns => {
      this.availableVisitColumns = [];
      columns.forEach(column => {
        this.availableVisitColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    external_CoreHome_["AjaxHelper"].fetch({
      method: 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize'
    }).then(columns => {
      this.availableActionColumns = [];
      columns.forEach(column => {
        this.availableActionColumns.push({
          key: column.column_name,
          value: column.column_name
        });
      });
    });
    setTimeout(() => {
      const options1 = external_CoreHome_["Matomo"].getBaseDatePickerOptions(null);
      const options2 = external_CoreHome_["Matomo"].getBaseDatePickerOptions(null);
      $(this.$refs.anonymizeStartDate).datepicker(options1);
      $(this.$refs.anonymizeEndDate).datepicker(options2);
    });
  },
  methods: {
    onVisitColumnChange() {
      const hasAll = this.selectedVisitColumns.every(col => !!(col !== null && col !== void 0 && col.column));
      if (hasAll) {
        this.addVisitColumn();
      }
    },
    addVisitColumn() {
      this.selectedVisitColumns.push({
        column: ''
      });
    },
    removeVisitColumn(index) {
      if (index > -1) {
        const lastIndex = this.selectedVisitColumns.length - 1;
        if (lastIndex === index) {
          this.selectedVisitColumns[index] = {
            column: ''
          };
        } else {
          this.selectedVisitColumns.splice(index, 1);
        }
      }
    },
    onActionColumnChange() {
      const hasAll = this.selectedActionColumns.every(col => !!(col !== null && col !== void 0 && col.column));
      if (hasAll) {
        this.addActionColumn();
      }
    },
    addActionColumn() {
      this.selectedActionColumns.push({
        column: ''
      });
    },
    removeActionColumn(index) {
      if (index > -1) {
        const lastIndex = this.selectedActionColumns.length - 1;
        if (lastIndex === index) {
          this.selectedActionColumns[index] = {
            column: ''
          };
        } else {
          this.selectedActionColumns.splice(index, 1);
        }
      }
    },
    scheduleAnonymization(password) {
      let date = `${this.startDate},${this.endDate}`;
      if (this.startDate === this.endDate) {
        date = this.startDate;
      }
      const params = {
        date
      };
      params.idSites = this.site.id;
      params.anonymizeIp = this.anonymizeIp ? '1' : '0';
      params.anonymizeLocation = this.anonymizeLocation ? '1' : '0';
      params.anonymizeUserId = this.anonymizeUserId ? '1' : '0';
      params.unsetVisitColumns = this.selectedVisitColumns.filter(c => !!(c !== null && c !== void 0 && c.column)).map(c => c.column);
      params.unsetLinkVisitActionColumns = this.selectedActionColumns.filter(c => !!(c !== null && c !== void 0 && c.column)).map(c => c.column);
      params.passwordConfirmation = password;
      external_CoreHome_["AjaxHelper"].post({
        method: 'PrivacyManager.anonymizeSomeRawData'
      }, params).then(() => {
        window.location.reload(true);
      });
    },
    onKeydownStartDate(event) {
      this.startDate = event.target.value;
    },
    onKeydownEndDate(event) {
      this.endDate = event.target.value;
    }
  },
  computed: {
    isAnonymizePastDataDisabled() {
      return !this.anonymizeIp && !this.anonymizeLocation && !this.selectedVisitColumns && !this.selectedActionColumns;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue



AnonymizeLogDatavue_type_script_lang_ts.render = AnonymizeLogDatavue_type_template_id_4d965cd4_render

/* harmony default export */ var AnonymizeLogData = (AnonymizeLogDatavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=4ca6f286

function DoNotTrackPreferencevue_type_template_id_4ca6f286_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "doNotTrack",
    modelValue: _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.enabled = $event),
    options: _ctx.doNotTrackOptions,
    "inline-help": _ctx.translate('PrivacyManager_DoNotTrack_Description')
  }, null, 8, ["modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[1] || (_cache[1] = $event => _ctx.save()),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])])), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=4ca6f286

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts



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
  data() {
    return {
      isLoading: false,
      enabled: this.dntSupport ? 1 : 0
    };
  },
  methods: {
    save() {
      this.isLoading = true;
      let action = 'deactivateDoNotTrack';
      if (this.enabled && this.enabled !== '0') {
        action = 'activateDoNotTrack';
      }
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: `PrivacyManager.${action}`
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue



DoNotTrackPreferencevue_type_script_lang_ts.render = DoNotTrackPreferencevue_type_template_id_4ca6f286_render

/* harmony default export */ var DoNotTrackPreference = (DoNotTrackPreferencevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ReportDeletionSettings/ReportDeletionSettings.store.ts
function ReportDeletionSettings_store_extends() { ReportDeletionSettings_store_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ReportDeletionSettings_store_extends.apply(this, arguments); }
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class ReportDeletionSettings_store_ReportDeletionSettingsStore {
  constructor() {
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      settings: {},
      showEstimate: false,
      loadingEstimation: false,
      estimation: '',
      isModified: false
    }));
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    _defineProperty(this, "enableDeleteReports", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.settings.enableDeleteReports));
    _defineProperty(this, "enableDeleteLogs", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.settings.enableDeleteLogs));
    _defineProperty(this, "currentRequest", void 0);
  }
  updateSettings(settings) {
    this.initSettings(settings);
    this.privateState.isModified = true;
  }
  initSettings(settings) {
    this.privateState.settings = ReportDeletionSettings_store_extends(ReportDeletionSettings_store_extends({}, this.privateState.settings), settings);
    this.reloadDbStats();
  }
  savePurgeDataSettings(apiMethod, settings, password) {
    this.privateState.isModified = false;
    return external_CoreHome_["AjaxHelper"].post({
      module: 'API',
      method: apiMethod
    }, ReportDeletionSettings_store_extends(ReportDeletionSettings_store_extends({}, settings), {}, {
      enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
      enableDeleteReports: settings.enableDeleteReports ? '1' : '0',
      passwordConfirmation: password
    })).then(() => {
      const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
        message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
        context: 'success',
        id: 'privacyManagerSettings',
        type: 'toast'
      });
      external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
    });
  }
  isEitherDeleteSectionEnabled() {
    return this.state.value.settings.enableDeleteLogs || this.state.value.settings.enableDeleteReports;
  }
  isManualEstimationLinkShowing() {
    return window.$('#getPurgeEstimateLink').length > 0;
  }
  reloadDbStats(forceEstimate) {
    if (this.currentRequest) {
      // if the manual estimate link is showing, abort unless forcing
      this.currentRequest.abort();
      this.currentRequest = undefined;
    }
    if (!forceEstimate && (!this.isEitherDeleteSectionEnabled() || this.isManualEstimationLinkShowing())) {
      return;
    }
    this.privateState.loadingEstimation = true;
    this.privateState.estimation = '';
    this.privateState.showEstimate = false;
    const {
      settings
    } = this.privateState;
    const formData = ReportDeletionSettings_store_extends(ReportDeletionSettings_store_extends({}, settings), {}, {
      enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
      enableDeleteReports: settings.enableDeleteReports ? '1' : '0'
    });
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
    }).then(data => {
      this.privateState.estimation = data;
      this.privateState.showEstimate = true;
      this.privateState.loadingEstimation = false;
    }).finally(() => {
      this.currentRequest = undefined;
      this.privateState.loadingEstimation = false;
    });
  }
}
/* harmony default export */ var ReportDeletionSettings_store = (new ReportDeletionSettings_store_ReportDeletionSettingsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=cb5c6300

const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_1 = {
  id: "formDeleteSettings"
};
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_2 = {
  id: "deleteLogSettingEnabled"
};
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_3 = {
  class: "alert alert-warning deleteOldLogsWarning",
  style: {
    "width": "50%"
  }
};
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_4 = ["href"];
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_5 = {
  id: "deleteLogSettings"
};
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_6 = {
  key: 0
};
const DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_7 = {
  key: 1
};
function DeleteOldLogsvue_type_template_id_cb5c6300_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => {
      _ctx.enabled = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteLog'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteRawDataInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.externalRawLink('https://matomo.org/faq/general/faq_125'),
    rel: "noreferrer noopener",
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ClickHere')), 9, DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_4)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "deleteOlderThan",
    "model-value": _ctx.deleteOlderThan,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => {
      _ctx.deleteOlderThan = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteOlderThanTitle,
    "inline-help": _ctx.translate('PrivacyManager_LeastDaysInput', '1')
  }, null, 8, ["model-value", "title", "inline-help"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[2] || (_cache[2] = $event => this.showPasswordConfirmModal = true),
    saving: _ctx.isLoading
  }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.showPasswordConfirmModal = $event),
    onConfirmed: _ctx.saveSettings
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.enabled && !_ctx.enableDeleteReports ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteLogsConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled && _ctx.enableDeleteReports ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldLogsvue_type_template_id_cb5c6300_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteBothConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])])), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=cb5c6300

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts




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
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      isLoading: false,
      enabled: parseInt(this.deleteData.config.delete_logs_enable, 10) === 1,
      deleteOlderThan: this.deleteData.config.delete_logs_older_than,
      showPasswordConfirmModal: false
    };
  },
  created() {
    setTimeout(() => {
      ReportDeletionSettings_store.initSettings(this.settings);
    });
  },
  methods: {
    saveSettings(password) {
      const method = 'PrivacyManager.setDeleteLogsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings, password).finally(() => {
        this.isLoading = false;
      });
    },
    reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
    }
  },
  computed: {
    settings() {
      return {
        enableDeleteLogs: !!this.enabled,
        deleteLogsOlderThan: this.deleteOlderThan
      };
    },
    deleteOlderThanTitle() {
      return `${Object(external_CoreHome_["translate"])('PrivacyManager_DeleteLogsOlderThan')} (${Object(external_CoreHome_["translate"])('Intl_PeriodDays')})`;
    },
    enableDeleteReports() {
      return !!ReportDeletionSettings_store.enableDeleteReports.value;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue



DeleteOldLogsvue_type_script_lang_ts.render = DeleteOldLogsvue_type_template_id_cb5c6300_render

/* harmony default export */ var DeleteOldLogs = (DeleteOldLogsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=e02c43aa

const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_1 = {
  id: "formDeleteSettings"
};
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_2 = {
  id: "deleteReportsSettingEnabled"
};
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_3 = {
  class: "alert alert-warning",
  style: {
    "width": "50%"
  }
};
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_6 = {
  id: "deleteReportsSettings"
};
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_7 = {
  key: 0
};
const DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_8 = {
  key: 1
};
function DeleteOldReportsvue_type_template_id_e02c43aa_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => {
      _ctx.enabled = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteReports'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteAggregateReportsDetailedInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo2', _ctx.deleteOldLogsText)), 1), DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_4, DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo3', _ctx.deleteOldLogsText)), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "deleteReportsOlderThan",
    "model-value": _ctx.deleteOlderThan,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => {
      _ctx.deleteOlderThan = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteReportsOlderThanTitle,
    "inline-help": _ctx.translate('PrivacyManager_LeastMonthsInput', '1')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepBasic",
    "model-value": _ctx.keepBasic,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => {
      _ctx.keepBasic = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.deleteReportsKeepBasicTitle,
    "inline-help": _ctx.translate('PrivacyManager_KeepBasicMetricsReportsDetailedInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_KeepDataFor')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepDay",
    "model-value": _ctx.keepDataForDay,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
      _ctx.keepDataForDay = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_DailyReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepWeek",
    "model-value": _ctx.keepDataForWeek,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => {
      _ctx.keepDataForWeek = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_WeeklyReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepMonth",
    "model-value": _ctx.keepDataForMonth,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => {
      _ctx.keepDataForMonth = $event;
      _ctx.reloadDbStats();
    }),
    title: `${_ctx.translate('General_MonthlyReports')} (${_ctx.translate('General_Recommended')})`
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepYear",
    "model-value": _ctx.keepDataForYear,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => {
      _ctx.keepDataForYear = $event;
      _ctx.reloadDbStats();
    }),
    title: `${_ctx.translate('General_YearlyReports')} (${_ctx.translate('General_Recommended')})`
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepRange",
    "model-value": _ctx.keepDataForRange,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => {
      _ctx.keepDataForRange = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('General_RangeReports')
  }, null, 8, ["model-value", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsKeepSegments",
    "model-value": _ctx.keepDataForSegments,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => {
      _ctx.keepDataForSegments = $event;
      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_KeepReportSegments')
  }, null, 8, ["model-value", "title"])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[9] || (_cache[9] = $event => this.showPasswordConfirmModal = true),
    saving: _ctx.isLoading
  }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => _ctx.showPasswordConfirmModal = $event),
    onConfirmed: _ctx.saveSettings
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.enabled && !_ctx.enableDeleteLogs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled && _ctx.enableDeleteLogs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldReportsvue_type_template_id_e02c43aa_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteBothConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])])), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=e02c43aa

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts




function getInt(value) {
  return value ? '1' : '0';
}
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
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
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
      keepDataForSegments: parseInt(this.deleteData.config.delete_reports_keep_segment_reports, 10) === 1,
      showPasswordConfirmModal: false
    };
  },
  created() {
    setTimeout(() => {
      ReportDeletionSettings_store.initSettings(this.settings);
    });
  },
  methods: {
    saveSettings(password) {
      const method = 'PrivacyManager.setDeleteReportsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings, password).finally(() => {
        this.isLoading = false;
      });
    },
    reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
    }
  },
  computed: {
    settings() {
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
    deleteOldLogsText() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_UseDeleteLog');
    },
    deleteReportsOlderThanTitle() {
      const first = Object(external_CoreHome_["translate"])('PrivacyManager_DeleteReportsOlderThan');
      return `${first} (${Object(external_CoreHome_["translate"])('Intl_PeriodMonths')})`;
    },
    deleteReportsKeepBasicTitle() {
      const first = Object(external_CoreHome_["translate"])('PrivacyManager_KeepBasicMetrics');
      return `${first} (${Object(external_CoreHome_["translate"])('General_Recommended')})`;
    },
    enableDeleteLogs() {
      return !!ReportDeletionSettings_store.enableDeleteLogs.value;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue



DeleteOldReportsvue_type_script_lang_ts.render = DeleteOldReportsvue_type_template_id_e02c43aa_render

/* harmony default export */ var DeleteOldReports = (DeleteOldReportsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=e8afc692

const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_1 = {
  id: "formDeleteSettings"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_2 = {
  id: "deleteSchedulingSettings"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_3 = {
  id: "deleteSchedulingSettingsInlineHelp",
  class: "inline-help-node"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_4 = {
  key: 0
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_9 = {
  key: 0,
  id: "deleteDataEstimateSect",
  class: "form-group row"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_10 = {
  class: "col s12",
  id: "databaseSizeHeadline"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_11 = {
  class: "col s12 m6"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_12 = ["innerHTML"];
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_13 = {
  class: "col s12 m6"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_14 = {
  key: 0,
  class: "form-help"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_15 = {
  class: "ui-confirm",
  id: "saveSettingsBeforePurge"
};
const ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  role: "yes",
  type: "button",
  value: "{{ translate('General_Ok') }}"
}, null, -1);
function ScheduleReportDeletionvue_type_template_id_e8afc692_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "scheduleSettingsHeadline",
    "content-title": _ctx.translate('PrivacyManager_DeleteSchedulingSettings')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "deleteLowestInterval",
      title: _ctx.translate('PrivacyManager_DeleteDataInterval'),
      modelValue: _ctx.deleteLowestInterval,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.deleteLowestInterval = $event),
      options: _ctx.scheduleDeletionOptions
    }, {
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_3, [_ctx.deleteData.lastRun ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_LastDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.lastRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_5, ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_6])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_NextDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.nextRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_7, ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        id: "purgeDataNowLink",
        href: "#",
        onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.executeDataPurge(), ["prevent"]))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PurgeNow')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showPurgeNowLink]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        "loading-message": _ctx.translate('PrivacyManager_PurgingData'),
        loading: _ctx.loadingDataPurge
      }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        id: "db-purged-message"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DBPurged')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataWasPurged]])])]),
      _: 1
    }, 8, ["title", "modelValue", "options"])])]), _ctx.deleteData.config.enable_database_size_estimate === '1' || _ctx.deleteData.config.enable_database_size_estimate === 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ReportsDataSavedEstimate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      id: "deleteDataEstimate",
      innerHTML: _ctx.$sanitize(_ctx.estimation)
    }, null, 8, ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEstimate]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.loadingEstimation
    }, null, 8, ["loading"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_13, [_ctx.deleteData.config.enable_auto_database_size_estimate !== '1' && _ctx.deleteData.config.enable_auto_database_size_estimate !== 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      id: "getPurgeEstimateLink",
      href: "#",
      onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.getPurgeEstimate(), ["prevent"]))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GetPurgeEstimate')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.showPasswordConfirmModal = true),
      saving: _ctx.isLoading
    }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmModal,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.showPasswordConfirmModal = $event),
      onConfirmed: _ctx.save
    }, null, 8, ["modelValue", "onConfirmed"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmModalForPurge,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.showPasswordConfirmModalForPurge = $event),
      onConfirmed: _ctx.executePurgeNow
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PurgeNowConfirm')), 1)]),
      _: 1
    }, 8, ["modelValue", "onConfirmed"])]),
    _: 1
  }, 8, ["content-title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isEitherDeleteSectionEnabled]])])), [[_directive_form]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SaveSettingsBeforePurge')), 1), ScheduleReportDeletionvue_type_template_id_e8afc692_hoisted_16])], 64);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=e8afc692

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=script&lang=ts




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
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      isLoading: false,
      loadingDataPurge: false,
      dataWasPurged: false,
      showPurgeNowLink: true,
      deleteLowestInterval: this.deleteData.config.delete_logs_schedule_lowest_interval,
      showPasswordConfirmModal: false,
      showPasswordConfirmModalForPurge: false
    };
  },
  methods: {
    save(password) {
      const method = 'PrivacyManager.setScheduleReportDeletionSettings';
      ReportDeletionSettings_store.savePurgeDataSettings(method, {
        deleteLowestInterval: this.deleteLowestInterval
      }, password);
    },
    executeDataPurge() {
      if (ReportDeletionSettings_store.state.value.isModified) {
        // ask user if they really want to delete their old data
        external_CoreHome_["Matomo"].helper.modalConfirm('#saveSettingsBeforePurge', {
          yes: () => null
        });
        return;
      }
      this.showPasswordConfirmModalForPurge = true;
    },
    getPurgeEstimate() {
      return ReportDeletionSettings_store.reloadDbStats(true);
    },
    executePurgeNow(password) {
      this.loadingDataPurge = true;
      this.showPurgeNowLink = false; // execute a data purge
      return external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'PrivacyManager.executeDataPurge'
      }, {
        passwordConfirmation: password
      }).then(() => {
        // force reload
        ReportDeletionSettings_store.reloadDbStats();
        this.dataWasPurged = true;
        setTimeout(() => {
          this.dataWasPurged = false;
          this.showPurgeNowLink = true;
        }, 2000);
      }).catch(() => {
        this.showPurgeNowLink = true;
      }).finally(() => {
        this.loadingDataPurge = false;
      });
    }
  },
  computed: {
    showEstimate() {
      return ReportDeletionSettings_store.state.value.showEstimate;
    },
    isEitherDeleteSectionEnabled() {
      return ReportDeletionSettings_store.isEitherDeleteSectionEnabled();
    },
    estimation() {
      return ReportDeletionSettings_store.state.value.estimation;
    },
    loadingEstimation() {
      return ReportDeletionSettings_store.state.value.loadingEstimation;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue



ScheduleReportDeletionvue_type_script_lang_ts.render = ScheduleReportDeletionvue_type_template_id_e8afc692_render

/* harmony default export */ var ScheduleReportDeletion = (ScheduleReportDeletionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AskingForConsent/AskingForConsent.vue?vue&type=template&id=1c45dbd0

const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_1 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_2 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_3 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_6 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_9 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_10 = ["innerHTML"];
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, null, -1);
const AskingForConsentvue_type_template_id_1c45dbd0_hoisted_12 = ["innerHTML"];
function AskingForConsentvue_type_template_id_1c45dbd0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AskingForConsent')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ConsentExplanation')), 1)])), [[_directive_content_intro]]), _ctx.consentManagerName ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    "content-title": _ctx.translate('PrivacyManager_ConsentManager'),
    class: "privacyAskingForConsent"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.consentManagerDetectedText)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_1), _ctx.consentManagerIsConnected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.translate('PrivacyManager_ConsentManagerConnected', _ctx.consentManagerName))
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_WhenDoINeedConsent'),
    class: "privacyAskingForConsent"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.whenConsentIsNeeded1)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_3), AskingForConsentvue_type_template_id_1c45dbd0_hoisted_4, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.whenConsentIsNeeded2)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_6), AskingForConsentvue_type_template_id_1c45dbd0_hoisted_7, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.whenConsentIsNeeded3)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_9)])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_HowDoIAskForConsent'),
    class: "privacyAskingForConsent"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_HowDoIAskForConsentIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
      innerHTML: _ctx.$sanitize(_ctx.consentManagersList)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_10), AskingForConsentvue_type_template_id_1c45dbd0_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.howDoIAskForConsentOthers)
    }, null, 8, AskingForConsentvue_type_template_id_1c45dbd0_hoisted_12)]),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AskingForConsent/AskingForConsent.vue?vue&type=template&id=1c45dbd0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AskingForConsent/AskingForConsent.vue?vue&type=script&lang=ts


/* harmony default export */ var AskingForConsentvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    consentManagerName: {
      type: String,
      required: true
    },
    consentManagerUrl: {
      type: String,
      required: true
    },
    consentManagerIsConnected: {
      type: Boolean,
      required: true
    },
    consentManagers: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  computed: {
    whenConsentIsNeeded1() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_WhenConsentIsNeededPart1', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/new-to-piwik/what-is-gdpr/'), '</a>');
    },
    whenConsentIsNeeded2() {
      const blogLink = 'https://matomo.org/blog/2018/04/lawful-basis-for-processing-personal-data-under-gdpr-with-matomo/';
      return Object(external_CoreHome_["translate"])('PrivacyManager_WhenConsentIsNeededPart2', Object(external_CoreHome_["externalLink"])(blogLink), '</a>');
    },
    whenConsentIsNeeded3() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_WhenConsentIsNeededPart3', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/how-to/faq_35661/'), '</a>');
    },
    howDoIAskForConsentOthers() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_HowDoIAskForConsentOutro', Object(external_CoreHome_["externalLink"])('https://developer.matomo.org/guides/tracking-consent'), '</a>');
    },
    consentManagersList() {
      let list = '';
      Object.entries(this.consentManagers).forEach(([name, url]) => {
        const u = Object(external_CoreHome_["externalRawLink"])(url);
        list += '<li>' + `  <a href="${u}"` + '     target="_blank" rel="noreferrer noopener">' + `    ${name} ${Object(external_CoreHome_["translate"])('PrivacyManager_ConsentManager')}` + '  </a>' + '</li>';
      });
      return list;
    },
    consentManagerDetectedText() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_ConsentManagerDetected', this.consentManagerName, `<a href="${this.consentManagerUrl}" target="_blank" rel="noreferrer noopener">`, '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AskingForConsent/AskingForConsent.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AskingForConsent/AskingForConsent.vue



AskingForConsentvue_type_script_lang_ts.render = AskingForConsentvue_type_template_id_1c45dbd0_render

/* harmony default export */ var AskingForConsent = (AskingForConsentvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/GdprOverview/GdprOverview.vue?vue&type=template&id=eba81e86

const GdprOverviewvue_type_template_id_eba81e86_hoisted_1 = {
  class: "gdprOverview"
};
const GdprOverviewvue_type_template_id_eba81e86_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GdprOverviewvue_type_template_id_eba81e86_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GdprOverviewvue_type_template_id_eba81e86_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GdprOverviewvue_type_template_id_eba81e86_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const GdprOverviewvue_type_template_id_eba81e86_hoisted_6 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_7 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_8 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_9 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_10 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_11 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_12 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_13 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_14 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_15 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_16 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_17 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_18 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_19 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_20 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_21 = ["innerHTML"];
const GdprOverviewvue_type_template_id_eba81e86_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function GdprOverviewvue_type_template_id_eba81e86_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_VueEntryContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("VueEntryContainer");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GdprOverviewvue_type_template_id_eba81e86_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprOverview')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprOverviewIntro1')) + " ", 1), GdprOverviewvue_type_template_id_eba81e86_hoisted_2, GdprOverviewvue_type_template_id_eba81e86_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprOverviewIntro2')), 1)])])), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_VueEntryContainer, {
    html: _ctx.afterGDPROverviewIntroContent
  }, null, 8, ["html"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_GdprChecklists')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprChecklistDesc1')) + " ", 1), GdprOverviewvue_type_template_id_eba81e86_hoisted_4, GdprOverviewvue_type_template_id_eba81e86_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.gdprChecklistDesc2)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_6)])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_IndividualsRights')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_IndividualsRightsIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_IndividualsRightsInform')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.rightsLinkText('IndividualsRightsAccess'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.rightsLinkText('IndividualsRightsErasure'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.rightsLinkText('IndividualsRightsRectification'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.rightsLinkText('IndividualsRightsPortability'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.rightsLinkText('IndividualsRightsObject', 'usersOptOut'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_IndividualsRightsChildren')), 1)])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_AwarenessDocumentation')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AwarenessDocumentationIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AwarenessDocumentationDesc1')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AwarenessDocumentationDesc2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.awarenessDocumentationDesc3)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.awarenessDocumentationDesc4)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_13)])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_SecurityProcedures')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SecurityProceduresIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc1)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc2)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_15), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc3)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc4)
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_17)])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_DataRetention')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DataRetentionInMatomo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [_ctx.deleteLogsEnable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.translate('PrivacyManager_RawDataRemovedAfter', `<strong>${_ctx.rawDataRetention}</strong>`))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_18)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 1,
      innerHTML: _ctx.$sanitize(_ctx.translate('PrivacyManager_RawDataNeverRemoved'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_19)), _ctx.deleteReportsEnable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 2,
      innerHTML: _ctx.$sanitize(_ctx.translate('PrivacyManager_ReportsRemovedAfter', `<strong>${_ctx.reportRetention}</strong>`))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_20)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 3,
      innerHTML: _ctx.$sanitize(_ctx.translate('PrivacyManager_ReportsNeverRemoved'))
    }, null, 8, GdprOverviewvue_type_template_id_eba81e86_hoisted_21))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [GdprOverviewvue_type_template_id_eba81e86_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DataRetentionOverall')), 1)])]),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/GdprOverview/GdprOverview.vue?vue&type=template&id=eba81e86

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/GdprOverview/GdprOverview.vue?vue&type=script&lang=ts


function externalLinkTranslate(tokenSuffix, url) {
  return Object(external_CoreHome_["translate"])(`PrivacyManager_${tokenSuffix}`, Object(external_CoreHome_["externalLink"])(url), '</a>');
}
/* harmony default export */ var GdprOverviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    afterGDPROverviewIntroContent: String,
    deleteLogsEnable: Boolean,
    deleteReportsEnable: Boolean,
    rawDataRetention: null,
    reportRetention: null
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    VueEntryContainer: external_CoreHome_["VueEntryContainer"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  methods: {
    rightsLinkText(tokenSuffix, action = 'gdprTools') {
      const link = `?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'PrivacyManager',
        action
      })}`;
      return Object(external_CoreHome_["translate"])(`PrivacyManager_${tokenSuffix}`, `<a target="_blank" rel="noreferrer noopener" href="${link}">`, '</a>');
    }
  },
  computed: {
    gdprChecklistDesc2() {
      return externalLinkTranslate('GdprChecklistDesc2', 'https://matomo.org/docs/gdpr');
    },
    awarenessDocumentationDesc3() {
      return externalLinkTranslate('AwarenessDocumentationDesc3', 'https://matomo.org/faq/general/faq_18254/');
    },
    awarenessDocumentationDesc4() {
      return externalLinkTranslate('AwarenessDocumentationDesc4', 'https://matomo.org/blog/2018/04/gdpr-how-to-fill-in-the-information-asset-register-when-using-matomo/');
    },
    securityProceduresDesc1() {
      return externalLinkTranslate('SecurityProceduresDesc1', 'https://matomo.org/docs/security/');
    },
    securityProceduresDesc2() {
      return externalLinkTranslate('SecurityProceduresDesc2', 'https://ico.org.uk/for-organisations/guide-to-the-general-data-protection-regulation-gdpr/international-transfers/');
    },
    securityProceduresDesc3() {
      return externalLinkTranslate('SecurityProceduresDesc3', 'https://ico.org.uk/for-organisations/guide-to-the-general-data-protection-regulation-gdpr/personal-data-breaches/');
    },
    securityProceduresDesc4() {
      return externalLinkTranslate('SecurityProceduresDesc4', 'https://www.cnil.fr/en/guidelines-dpia');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/GdprOverview/GdprOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/GdprOverview/GdprOverview.vue



GdprOverviewvue_type_script_lang_ts.render = GdprOverviewvue_type_template_id_eba81e86_render

/* harmony default export */ var GdprOverview = (GdprOverviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/PreviousAnonymizations.vue?vue&type=template&id=2b9a8f00

const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_1 = {
  key: 0
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_3 = {
  key: 1
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_5 = {
  key: 2
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_6 = {
  key: 3
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_7 = {
  key: 0
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_8 = ["title"];
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_9 = {
  key: 1
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_10 = ["title"];
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_11 = {
  key: 2
};
const PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_12 = ["title"];
function PreviousAnonymizationsvue_type_template_id_2b9a8f00_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PreviousRawDataAnonymizations')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_Requester')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AffectedIDSites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AffectedDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_Anonymize')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_VisitColumns')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_LinkVisitActionColumns')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.anonymizations, (entry, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.requester), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.sites.join(', ')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.date_start) + " - " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.date_end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [entry.anonymize_ip ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_IPAddress')), 1), PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_2])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.anonymize_location ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Overlay_Location')), 1), PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_4])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), entry.anonymize_userid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_UserId')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !entry.anonymize_ip && !entry.anonymize_location && !entry.anonymize_userid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_6, "-")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.unset_visit_columns.join(', ')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.unset_link_visit_action_columns.join(', ')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [!entry.job_start_date ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-info",
      style: {
        "cursor": "help"
      },
      title: `${_ctx.translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '')}`
    }, null, 8, PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_8), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_Scheduled')), 1)])) : entry.job_start_date && !entry.job_finish_date ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-info",
      style: {
        "cursor": "help"
      },
      title: `${_ctx.translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '')}.
${_ctx.translate('PrivacyManager_JobStartDate', entry.job_start_date)}.
${_ctx.translate('PrivacyManager_CurrentOutput', entry.output)}`
    }, null, 8, PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_InProgress')), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "icon-info",
      style: {
        "cursor": "help"
      },
      title: `${_ctx.translate('PrivacyManager_ScheduledDate', entry.scheduled_date || '')}.
${_ctx.translate('PrivacyManager_JobStartDate', entry.job_start_date)}.
${_ctx.translate('PrivacyManager_JobFinishDate', entry.job_finish_date)}.
${_ctx.translate('PrivacyManager_Output', entry.output)}`
    }, null, 8, PreviousAnonymizationsvue_type_template_id_2b9a8f00_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Done')), 1)]))])]);
  }), 128))])])), [[_directive_content_table]])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/PreviousAnonymizations.vue?vue&type=template&id=2b9a8f00

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/PreviousAnonymizations.vue?vue&type=script&lang=ts


/* harmony default export */ var PreviousAnonymizationsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    anonymizations: {
      type: Array,
      required: true
    }
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/PreviousAnonymizations.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/PreviousAnonymizations.vue



PreviousAnonymizationsvue_type_script_lang_ts.render = PreviousAnonymizationsvue_type_template_id_2b9a8f00_render

/* harmony default export */ var PreviousAnonymizations = (PreviousAnonymizationsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/PrivacySettings/PrivacySettings.vue?vue&type=template&id=f0e353ec

const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_1 = ["innerHTML"];
const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_2 = ["innerHTML"];
const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_3 = {
  key: 0
};
const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "anonymizeHistoricalData",
  id: "anonymizeHistoricalData"
}, null, -1);
const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_5 = {
  key: 1
};
const PrivacySettingsvue_type_template_id_f0e353ec_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function PrivacySettingsvue_type_template_id_f0e353ec_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_AnonymizeIp = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AnonymizeIp");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_DeleteOldLogs = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DeleteOldLogs");
  const _component_DeleteOldReports = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DeleteOldReports");
  const _component_ScheduleReportDeletion = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ScheduleReportDeletion");
  const _component_AnonymizeLogData = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AnonymizeLogData");
  const _component_PreviousAnonymizations = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PreviousAnonymizations");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/privacy/')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeData')), 1)]),
    _: 1
  }, 8, ["help-url"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.teaserHeader),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, PrivacySettingsvue_type_template_id_f0e353ec_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.seeAlsoOurOfficialGuide)
  }, null, 8, PrivacySettingsvue_type_template_id_f0e353ec_hoisted_2)])])), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "anonymizeIPAnchor",
    "content-title": _ctx.translate('PrivacyManager_UseAnonymizeTrackingData')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AnonymizeIp, {
      "anonymize-ip-enabled": _ctx.anonymizeIpEnabled,
      "anonymize-user-id": _ctx.anonymizeUserId,
      "mask-length": _ctx.maskLength,
      "use-anonymized-ip-for-visit-enrichment": _ctx.useAnonymizedIpForVisitEnrichment,
      "anonymize-order-id": _ctx.anonymizeOrderId,
      "force-cookieless-tracking": _ctx.forceCookielessTracking,
      "anonymize-referrer": _ctx.anonymizeReferrer,
      "mask-length-options": _ctx.maskLengthOptions,
      "use-anonymized-ip-for-visit-enrichment-options": _ctx.useAnonymizedIpForVisitEnrichmentOptions,
      "tracker-file-name": _ctx.trackerFileName,
      "tracker-writable": _ctx.trackerWritable,
      "referrer-anonymization-options": _ctx.referrerAnonymizationOptions
    }, null, 8, ["anonymize-ip-enabled", "anonymize-user-id", "mask-length", "use-anonymized-ip-for-visit-enrichment", "anonymize-order-id", "force-cookieless-tracking", "anonymize-referrer", "mask-length-options", "use-anonymized-ip-for-visit-enrichment-options", "tracker-file-name", "tracker-writable", "referrer-anonymization-options"])]),
    _: 1
  }, 8, ["content-title"]), _ctx.isDataPurgeSettingsEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PrivacySettingsvue_type_template_id_f0e353ec_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "deleteLogsAnchor",
    "content-title": _ctx.translate('PrivacyManager_DeleteOldRawData')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteDataDescription')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DeleteOldLogs, {
      "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
      "delete-data": _ctx.deleteData,
      "schedule-deletion-options": _ctx.scheduleDeletionOptions
    }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "deleteReportsAnchor",
    "content-title": _ctx.translate('PrivacyManager_DeleteOldAggregatedReports')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DeleteOldReports, {
      "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
      "delete-data": _ctx.deleteData,
      "schedule-deletion-options": _ctx.scheduleDeletionOptions
    }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])]),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ScheduleReportDeletion, {
    "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
    "delete-data": _ctx.deleteData,
    "schedule-deletion-options": _ctx.scheduleDeletionOptions
  }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), PrivacySettingsvue_type_template_id_f0e353ec_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_AnonymizePreviousData'),
    class: "logDataAnonymizer"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizePreviousDataDescription')), 1), _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_AnonymizeLogData, {
      key: 0
    })) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PrivacySettingsvue_type_template_id_f0e353ec_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizePreviousDataOnlySuperUser')), 1)), PrivacySettingsvue_type_template_id_f0e353ec_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PreviousAnonymizations, {
      anonymizations: _ctx.anonymizations
    }, null, 8, ["anonymizations"])]),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/PrivacySettings/PrivacySettings.vue?vue&type=template&id=f0e353ec

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/PrivacySettings/PrivacySettings.vue?vue&type=script&lang=ts








/* harmony default export */ var PrivacySettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    anonymizeIpEnabled: Boolean,
    anonymizeUserId: Boolean,
    maskLength: {
      type: Number,
      required: true
    },
    useAnonymizedIpForVisitEnrichment: [Boolean, String, Number],
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
    },
    isDataPurgeSettingsEnabled: Boolean,
    deleteData: {
      type: Object,
      required: true
    },
    scheduleDeletionOptions: {
      type: Object,
      required: true
    },
    anonymizations: {
      type: Array,
      required: true
    },
    isSuperUser: Boolean
  },
  components: {
    AnonymizeIp: AnonymizeIp,
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    DeleteOldLogs: DeleteOldLogs,
    DeleteOldReports: DeleteOldReports,
    ScheduleReportDeletion: ScheduleReportDeletion,
    AnonymizeLogData: AnonymizeLogData,
    PreviousAnonymizations: PreviousAnonymizations
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  },
  computed: {
    teaserHeader() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_TeaserHeader', '<a href="#anonymizeIPAnchor">', '</a>', '<a href="#deleteLogsAnchor">', '</a>', '<a href="#anonymizeHistoricalData">', '</a>');
    },
    seeAlsoOurOfficialGuide() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_SeeAlsoOurOfficialGuidePrivacy', Object(external_CoreHome_["externalLink"])('https://matomo.org/privacy/'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/PrivacySettings/PrivacySettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/PrivacySettings/PrivacySettings.vue



PrivacySettingsvue_type_script_lang_ts.render = PrivacySettingsvue_type_template_id_f0e353ec_render

/* harmony default export */ var PrivacySettings = (PrivacySettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/UsersOptOut/UsersOptOut.vue?vue&type=template&id=bdf073f4

const UsersOptOutvue_type_template_id_bdf073f4_hoisted_1 = {
  key: 0
};
const UsersOptOutvue_type_template_id_bdf073f4_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const UsersOptOutvue_type_template_id_bdf073f4_hoisted_3 = {
  key: 1
};
function UsersOptOutvue_type_template_id_bdf073f4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_OptOutCustomizer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("OptOutCustomizer");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_DoNotTrackPreference = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DoNotTrackPreference");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_TrackingOptOut')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.prefaceComponentsResolved, (preface, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(preface), {
        key: index
      });
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_OptOutCustomizer, {
      "matomo-url": _ctx.matomoUrl,
      language: _ctx.language,
      "language-options": _ctx.languageOptions
    }, null, 8, ["matomo-url", "language", "language-options"])]),
    _: 1
  }, 8, ["content-title"]), _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    id: "DNT",
    "content-title": _ctx.translate('PrivacyManager_DoNotTrack_SupportDNTPreference')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
      severity: "warning"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DoNotTrack_Deprecated')), 1)]),
      _: 1
    }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_ctx.dntSupport ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UsersOptOutvue_type_template_id_bdf073f4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DoNotTrack_Enabled')), 1), UsersOptOutvue_type_template_id_bdf073f4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DoNotTrack_EnabledMoreInfo')), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UsersOptOutvue_type_template_id_bdf073f4_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DoNotTrack_Disabled')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DoNotTrack_DisabledMoreInfo')), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DoNotTrackPreference, {
      "dnt-support": _ctx.dntSupport,
      "do-not-track-options": _ctx.doNotTrackOptions
    }, null, 8, ["dnt-support", "do-not-track-options"])]),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/UsersOptOut/UsersOptOut.vue?vue&type=template&id=bdf073f4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/PrivacyManager/vue/src/UsersOptOut/UsersOptOut.vue?vue&type=script&lang=ts




/* harmony default export */ var UsersOptOutvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    language: {
      type: String,
      required: true
    },
    matomoUrl: String,
    isSuperUser: Boolean,
    dntSupport: Boolean,
    doNotTrackOptions: {
      type: Array,
      required: true
    },
    languageOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    Alert: external_CoreHome_["Alert"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    DoNotTrackPreference: DoNotTrackPreference,
    OptOutCustomizer: OptOutCustomizer
  },
  data() {
    return {
      prefaceComponents: []
    };
  },
  computed: {
    prefaceComponentsResolved() {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(this.prefaceComponents.map(c => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(Object(external_CoreHome_["useExternalPluginComponent"])(c.plugin, c.component))));
    }
  },
  created() {
    const components = [];
    external_CoreHome_["Matomo"].postEvent('PrivacyManager.UsersOptOut.preface', components);
    this.prefaceComponents = components;
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/UsersOptOut/UsersOptOut.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/UsersOptOut/UsersOptOut.vue



UsersOptOutvue_type_script_lang_ts.render = UsersOptOutvue_type_template_id_bdf073f4_render

/* harmony default export */ var UsersOptOut = (UsersOptOutvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/index.ts
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
//# sourceMappingURL=PrivacyManager.umd.js.map