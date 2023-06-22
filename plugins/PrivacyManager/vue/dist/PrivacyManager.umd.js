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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=ef410c26

var _hoisted_1 = {
  class: "manageGdpr"
};
var _hoisted_2 = {
  class: "intro"
};

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = ["innerHTML"];
var _hoisted_8 = {
  class: "form-group row"
};
var _hoisted_9 = {
  class: "col s12 input-field"
};
var _hoisted_10 = {
  for: "gdprsite",
  class: "siteSelectorLabel"
};
var _hoisted_11 = {
  class: "sites_autocomplete"
};
var _hoisted_12 = {
  class: "form-group row segmentFilterGroup"
};
var _hoisted_13 = {
  class: "col s12"
};
var _hoisted_14 = {
  style: {
    "margin": "8px 0",
    "display": "inline-block"
  }
};

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])();

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_20 = {
  class: "checkInclude"
};
var _hoisted_21 = {
  colspan: "8"
};
var _hoisted_22 = ["title"];
var _hoisted_23 = {
  class: "checkInclude"
};
var _hoisted_24 = ["title"];
var _hoisted_25 = {
  class: "visitId"
};
var _hoisted_26 = {
  class: "visitorId"
};
var _hoisted_27 = ["title", "onClick"];
var _hoisted_28 = {
  class: "visitorIp"
};
var _hoisted_29 = ["title", "onClick"];
var _hoisted_30 = {
  class: "userId"
};
var _hoisted_31 = ["title", "onClick"];
var _hoisted_32 = ["title"];
var _hoisted_33 = ["src"];
var _hoisted_34 = ["title"];
var _hoisted_35 = ["src"];
var _hoisted_36 = ["title"];
var _hoisted_37 = ["src"];
var _hoisted_38 = ["title"];
var _hoisted_39 = ["src"];
var _hoisted_40 = ["onClick"];

var _hoisted_41 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Live/images/visitorProfileLaunch.png",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);

var _hoisted_42 = {
  class: "ui-confirm",
  id: "confirmDeleteDataSubject",
  ref: "confirmDeleteDataSubject"
};
var _hoisted_43 = ["value"];
var _hoisted_44 = ["value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  var _component_SegmentGenerator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentGenerator");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('PrivacyManager_GdprTools')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntro1')) + " ", 1), _hoisted_3, _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntro2')) + " ", 1), _hoisted_5]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntroAccessRight')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GdprToolsPageIntroEraseRight')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.overviewHintText)
      }, null, 8, _hoisted_7)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SearchForDataSubject')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SelectWebsite')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
        id: "gdprsite",
        modelValue: _ctx.site,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.site = $event;
        }),
        "show-all-sites-item": true,
        "switch-site-on-select": false,
        "show-selected-site": true
      }, null, 8, ["modelValue"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FindDataSubjectsBy')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentGenerator, {
        modelValue: _ctx.segment_filter,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.segment_filter = $event;
        }),
        "visit-segments-only": true,
        idsite: _ctx.site.id
      }, null, 8, ["modelValue", "idsite"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        class: "findDataSubjects",
        value: _ctx.translate('PrivacyManager_FindMatchingDataSubjects'),
        onConfirm: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.findDataSubjects();
        }),
        disabled: !_ctx.segment_filter,
        saving: _ctx.isLoading
      }, null, 8, ["value", "disabled", "saving"])];
    }),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_NoDataSubjectsFound')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.dataSubjects.length && _ctx.hasSearched]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_MatchingDataSubjects')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_VisitsMatchedCriteria')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ExportingNote')) + " ", 1), _hoisted_15, _hoisted_16, _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeletionFromMatomoOnly')) + " ", 1), _hoisted_18, _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ResultIncludesAllVisits')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "activateAll",
    "model-value": _ctx.toggleAll,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      _ctx.toggleAll = $event;

      _ctx.toggleActivateAll();
    }),
    "full-width": true
  }, null, 8, ["model-value"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Website')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitId')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitorID')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_VisitorIP')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_UserId')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Details')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.profileEnabled]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ResultTruncated', '400')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataSubjects.length > 400]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dataSubjects, function (dataSubject, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      title: "".concat(_ctx.translate('PrivacyManager_LastAction'), ": ").concat(dataSubject.lastActionDateTime),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "subject".concat(dataSubject.idVisit),
      modelValue: _ctx.dataSubjectsActive[index],
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.dataSubjectsActive[index] = $event;
      },
      "full-width": true
    }, null, 8, ["name", "modelValue", "onUpdate:modelValue"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
      class: "site",
      title: "(".concat(_ctx.translate('General_Id'), " ").concat(dataSubject.idSite, ")")
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.siteName), 9, _hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.idVisit), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddVisitorIdToSearch'),
      onClick: function onClick($event) {
        return _ctx.addFilter('visitorId', dataSubject.visitorId);
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.visitorId), 9, _hoisted_27)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddVisitorIPToSearch'),
      onClick: function onClick($event) {
        return _ctx.addFilter('visitIp', dataSubject.visitIp);
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.visitIp), 9, _hoisted_29)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      title: _ctx.translate('PrivacyManager_AddUserIdToSearch'),
      onClick: function onClick($event) {
        return _ctx.addFilter('userId', dataSubject.userId);
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dataSubject.userId), 9, _hoisted_31)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: "".concat(dataSubject.deviceType, " ").concat(dataSubject.deviceModel),
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.deviceTypeIcon
    }, null, 8, _hoisted_33)], 8, _hoisted_32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: dataSubject.operatingSystem,
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.operatingSystemIcon
    }, null, 8, _hoisted_35)], 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: "".concat(dataSubject.browser, " ").concat(dataSubject.browserFamilyDescription),
      style: {
        "margin-right": "3.5px"
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.browserIcon
    }, null, 8, _hoisted_37)], 8, _hoisted_36), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: "".concat(dataSubject.country, " ").concat(dataSubject.region || '')
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.countryFlag
    }, null, 8, _hoisted_39)], 8, _hoisted_38)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "visitorLogTooltip",
      title: "View visitor profile",
      onClick: function onClick($event) {
        return _ctx.showProfile(dataSubject.visitorId, dataSubject.idSite);
      }
    }, [_hoisted_41, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_ViewVisitorProfile')), 1)], 8, _hoisted_40)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.profileEnabled]])], 8, _hoisted_22);
  }), 128))])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "exportDataSubjects",
    style: {
      "margin-right": "3.5px"
    },
    onConfirm: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.exportDataSubject();
    }),
    disabled: !_ctx.hasActiveDataSubjects,
    value: _ctx.translate('PrivacyManager_ExportSelectedVisits')
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "deleteDataSubjects",
    onConfirm: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.deleteDataSubject();
    }),
    disabled: !_ctx.hasActiveDataSubjects || _ctx.isDeleting,
    value: _ctx.translate('PrivacyManager_DeleteSelectedVisits')
  }, null, 8, ["disabled", "value"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataSubjects.length]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteVisitsConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_43), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_44)], 512)]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=ef410c26

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "SegmentEditor"
var external_SegmentEditor_ = __webpack_require__("f06f");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=script&lang=ts




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
  data: function data() {
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
  setup: function setup() {
    var sitesPromise = external_CoreHome_["AjaxHelper"].fetch({
      method: 'SitesManager.getSitesIdWithAdminAccess',
      filter_limit: '-1'
    });
    return {
      getSites: function getSites() {
        return sitesPromise;
      }
    };
  },
  methods: {
    showSuccessNotification: function showSuccessNotification(message) {
      var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
        message: message,
        context: 'success',
        id: 'manageGdpr',
        type: 'transient'
      });
      setTimeout(function () {
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }, 200);
    },
    linkTo: function linkTo(action) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'PrivacyManager',
        action: action
      })));
    },
    toggleActivateAll: function toggleActivateAll() {
      this.dataSubjectsActive.fill(this.toggleAll);
    },
    showProfile: function showProfile(visitorId, idSite) {
      external_CoreHome_["Matomo"].helper.showVisitorProfilePopup(visitorId, idSite);
    },
    exportDataSubject: function exportDataSubject() {
      var _this = this;

      var visitsToDelete = this.activatedDataSubjects;
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'PrivacyManager.exportDataSubjects',
        format: 'json',
        filter_limit: -1
      }, {
        visits: visitsToDelete
      }).then(function (visits) {
        _this.showSuccessNotification(Object(external_CoreHome_["translate"])('PrivacyManager_VisitsSuccessfullyExported'));

        external_CoreHome_["Matomo"].helper.sendContentAsDownload('exported_data_subjects.json', JSON.stringify(visits));
      });
    },
    deleteDataSubject: function deleteDataSubject() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmDeleteDataSubject, {
        yes: function yes() {
          _this2.isDeleting = true;
          var visitsToDelete = _this2.activatedDataSubjects;
          external_CoreHome_["AjaxHelper"].post({
            module: 'API',
            method: 'PrivacyManager.deleteDataSubjects',
            filter_limit: -1
          }, {
            visits: visitsToDelete
          }).then(function () {
            _this2.dataSubjects = [];

            _this2.showSuccessNotification(Object(external_CoreHome_["translate"])('PrivacyManager_VisitsSuccessfullyDeleted'));

            _this2.findDataSubjects();
          }).finally(function () {
            _this2.isDeleting = false;
          });
        }
      });
    },
    addFilter: function addFilter(segment, value) {
      this.segment_filter += ",".concat(segment, "==").concat(value);
      this.findDataSubjects();
    },
    findDataSubjects: function findDataSubjects() {
      var _this3 = this;

      this.dataSubjects = [];
      this.dataSubjectsActive = [];
      this.isLoading = true;
      this.toggleAll = true;
      this.hasSearched = false;
      this.getSites().then(function (idsites) {
        var siteIds = _this3.site.id;

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
          segment: _this3.segment_filter
        }).then(function (visits) {
          _this3.hasSearched = true;
          _this3.dataSubjectsActive = visits.map(function () {
            return true;
          });
          _this3.dataSubjects = visits;
        }).finally(function () {
          _this3.isLoading = false;
        });
      });
    }
  },
  computed: {
    hasActiveDataSubjects: function hasActiveDataSubjects() {
      return !!this.activatedDataSubjects.length;
    },
    activatedDataSubjects: function activatedDataSubjects() {
      var _this4 = this;

      return this.dataSubjects.filter(function (v, i) {
        return _this4.dataSubjectsActive[i];
      }).map(function (v) {
        return {
          idsite: v.idSite,
          idvisit: v.idVisit
        };
      });
    },
    overviewHintText: function overviewHintText() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_GdprToolsOverviewHint', "<a href=\"".concat(this.linkTo('gdprOverview'), "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue



ManageGdprvue_type_script_lang_ts.render = render

/* harmony default export */ var ManageGdpr = (ManageGdprvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=3a6e17ea


var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_5 = {
  key: 0
};

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_8 = {
  class: "alert-warning alert"
};
function AnonymizeIpvue_type_template_id_3a6e17ea_render(_ctx, _cache, $props, $setup, $data, $options) {
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
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote')) + " ", 1), AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_1, AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PseudonymizeUserIdNote2')), 1)];
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
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription', _ctx.trackerFileName)) + " ", 1), AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_3, AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescription2')), 1), !_ctx.trackerWritable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_5, [AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_6, AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", AnonymizeIpvue_type_template_id_3a6e17ea_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ForceCookielessTrackingDescriptionNotWritable', _ctx.trackerFileName)), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
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
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=template&id=3a6e17ea

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeIp/AnonymizeIp.vue?vue&type=script&lang=ts




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
  data: function data() {
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
        useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment
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



AnonymizeIpvue_type_script_lang_ts.render = AnonymizeIpvue_type_template_id_3a6e17ea_render

/* harmony default export */ var AnonymizeIp = (AnonymizeIpvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=bb076364

var OptOutCustomizervue_type_template_id_bb076364_hoisted_1 = {
  class: "optOutCustomizer"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_2 = ["innerHTML"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_3 = {
  key: 0,
  id: "opt-out-styling"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_4 = ["value"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_5 = ["value"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_6 = ["value"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_7 = ["value"];

var OptOutCustomizervue_type_template_id_bb076364_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<option value=\"px\">px</option><option value=\"pt\">pt</option><option value=\"em\">em</option><option value=\"rem\">rem</option><option value=\"%\">%</option>", 5);

var OptOutCustomizervue_type_template_id_bb076364_hoisted_13 = [OptOutCustomizervue_type_template_id_bb076364_hoisted_8];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_14 = ["value"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_15 = ["src"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_16 = {
  class: "form-group row"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_17 = {
  class: "col s12 m6"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_18 = {
  for: "codeType1"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_19 = {
  for: "codeType2"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_20 = {
  key: 0
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_21 = {
  class: "col s12 m6"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_22 = ["innerHTML"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_23 = {
  ref: "pre"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_24 = ["innerHTML"];
var OptOutCustomizervue_type_template_id_bb076364_hoisted_25 = {
  class: "system notification notification-info optOutTestReminder"
};
var OptOutCustomizervue_type_template_id_bb076364_hoisted_26 = ["innerHTML"];
function OptOutCustomizervue_type_template_id_bb076364_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreAdminHome_OptOutExplanation')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.readThisToLearnMore)
  }, null, 8, OptOutCustomizervue_type_template_id_bb076364_hoisted_2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutAppearance')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "applyStyling",
    type: "checkbox",
    name: "applyStyling",
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.applyStyling = $event;
    }),
    onKeydown: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.updateCode();
    }),
    onChange: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.updateCode();
    })
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.applyStyling]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ApplyStyling')), 1)])])]), _ctx.applyStyling ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.fontColor,
    onKeydown: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.onFontColorChange($event);
    }),
    onChange: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.onFontColorChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_bb076364_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_BackgroundColor')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "color",
    value: _ctx.backgroundColor,
    onKeydown: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.onBgColorChange($event);
    }),
    onChange: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.onBgColorChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_bb076364_hoisted_5)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontSize')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontSizeInput",
    type: "number",
    min: "1",
    max: "100",
    value: _ctx.fontSize,
    onKeydown: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.onFontSizeChange($event);
    }),
    onChange: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.onFontSizeChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_bb076364_hoisted_6)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default",
    value: _ctx.fontSizeUnit,
    onKeydown: _cache[9] || (_cache[9] = function ($event) {
      return _ctx.onFontSizeUnitChange($event);
    }),
    onChange: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.onFontSizeUnitChange($event);
    })
  }, OptOutCustomizervue_type_template_id_bb076364_hoisted_13, 40, OptOutCustomizervue_type_template_id_bb076364_hoisted_7)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_FontFamily')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "FontFamilyInput",
    type: "text",
    value: _ctx.fontFamily,
    onKeydown: _cache[11] || (_cache[11] = function ($event) {
      return _ctx.onFontFamilyChange($event);
    }),
    onChange: _cache[12] || (_cache[12] = function ($event) {
      return _ctx.onFontFamilyChange($event);
    })
  }, null, 40, OptOutCustomizervue_type_template_id_bb076364_hoisted_14)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "showIntro",
    type: "checkbox",
    name: "showIntro",
    "onUpdate:modelValue": _cache[13] || (_cache[13] = function ($event) {
      return _ctx.showIntro = $event;
    }),
    onKeydown: _cache[14] || (_cache[14] = function ($event) {
      return _ctx.updateCode();
    }),
    onChange: _cache[15] || (_cache[15] = function ($event) {
      return _ctx.updateCode();
    })
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
  }, null, 10, OptOutCustomizervue_type_template_id_bb076364_hoisted_15)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutHtmlCode')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", OptOutCustomizervue_type_template_id_bb076364_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "radio",
    id: "codeType1",
    name: "codeType",
    value: "tracker",
    "onUpdate:modelValue": _cache[16] || (_cache[16] = function ($event) {
      return _ctx.codeType = $event;
    }),
    onKeydown: _cache[17] || (_cache[17] = function ($event) {
      return _ctx.updateCode();
    }),
    onChange: _cache[18] || (_cache[18] = function ($event) {
      return _ctx.updateCode();
    })
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.codeType]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutUseTracker')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", OptOutCustomizervue_type_template_id_bb076364_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "radio",
    id: "codeType2",
    name: "codeType",
    value: "selfContained",
    "onUpdate:modelValue": _cache[19] || (_cache[19] = function ($event) {
      return _ctx.codeType = $event;
    }),
    onKeydown: _cache[20] || (_cache[20] = function ($event) {
      return _ctx.updateCode();
    }),
    onChange: _cache[21] || (_cache[21] = function ($event) {
      return _ctx.updateCode();
    })
  }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.codeType]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutUseStandalone')), 1)])]), _ctx.codeType === 'selfContained' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "language",
    modelValue: _ctx.language,
    "onUpdate:modelValue": _cache[22] || (_cache[22] = function ($event) {
      return _ctx.language = $event;
    }),
    title: _ctx.translate('General_Language'),
    options: _ctx.languageOptions,
    onKeydown: _cache[23] || (_cache[23] = function ($event) {
      return _ctx.updateCode();
    }),
    onChange: _cache[24] || (_cache[24] = function ($event) {
      return _ctx.updateCode();
    })
  }, null, 8, ["modelValue", "title", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "form-help",
    innerHTML: _ctx.$sanitize(_ctx.codeTypeHelp)
  }, null, 8, OptOutCustomizervue_type_template_id_bb076364_hoisted_22)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", OptOutCustomizervue_type_template_id_bb076364_hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.codeBox) + "\n      ", 1)], 512), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.optOutExplanationIntro)
  }, null, 8, OptOutCustomizervue_type_template_id_bb076364_hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", OptOutCustomizervue_type_template_id_bb076364_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTest')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestBody')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep1')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep2')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep3')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_OptOutRememberToTestStep4')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_BuildYourOwn')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.optOutCustomOptOutLink)
  }, null, 8, OptOutCustomizervue_type_template_id_bb076364_hoisted_26)])], 64);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=template&id=bb076364

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
    OptOutCustomizervue_type_script_lang_ts_$ = _window.$;
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
  data: function data() {
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
  created: function created() {
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
    onFontColorChange: function onFontColorChange(event) {
      this.fontColor = event.target.value;
      this.updateCode();
    },
    onBgColorChange: function onBgColorChange(event) {
      this.backgroundColor = event.target.value;
      this.updateCode();
    },
    onFontSizeChange: function onFontSizeChange(event) {
      this.fontSize = event.target.value;
      this.updateCode();
    },
    onFontSizeUnitChange: function onFontSizeUnitChange(event) {
      this.fontSizeUnit = event.target.value;
      this.updateCode();
    },
    onFontFamilyChange: function onFontFamilyChange(event) {
      this.fontFamily = event.target.value;
      this.updateCode();
    },
    updateCode: function updateCode() {
      var _this = this;

      var methodName = 'CoreAdminHome.getOptOutJSEmbedCode';

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
      }).then(function (data) {
        _this.code = data.value || '';
      });
    }
  },
  watch: {
    codeBox: function codeBox() {
      var pre = this.$refs.pre;
      var isAnimationAlreadyRunning = OptOutCustomizervue_type_script_lang_ts_$(pre).queue('fx').length > 0;

      if (!isAnimationAlreadyRunning) {
        OptOutCustomizervue_type_script_lang_ts_$(pre).effect('highlight', {}, 1500);
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
      return !!this.matomoUrl && this.backgroundColor === '' && this.fontColor !== '' && nearlyWhite(this.fontColor.slice(1));
    },
    codeBox: function codeBox() {
      if (this.matomoUrl) {
        return this.code;
      }

      return '';
    },
    iframeUrl: function iframeUrl() {
      var query = external_CoreHome_["MatomoUrl"].stringify({
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
      return "".concat(this.matomoUrl, "index.php?").concat(query);
    },
    readThisToLearnMore: function readThisToLearnMore() {
      var link = 'https://matomo.org/faq/how-to/faq_25918/';
      return Object(external_CoreHome_["translate"])('General_ReadThisToLearnMore', "<a rel='noreferrer noopener' target='_blank' href='".concat(link, "'>"), '</a>');
    },
    optOutExplanationIntro: function optOutExplanationIntro() {
      return Object(external_CoreHome_["translate"])('CoreAdminHome_OptOutExplanationIntro', "<a href=\"".concat(this.iframeUrl, "\" rel=\"noreferrer noopener\" target=\"_blank\">"), '</a>');
    },
    optOutCustomOptOutLink: function optOutCustomOptOutLink() {
      var link = 'https://developer.matomo.org/guides/tracking-javascript-guide#optional-creating-a-custom-opt-out-form';
      return Object(external_CoreHome_["translate"])('CoreAdminHome_OptOutCustomOptOutLink', "<a href=\"".concat(link, "\" rel=\"noreferrer noopener\" target=\"_blank\">"), '</a>');
    },
    codeTypeHelp: function codeTypeHelp() {
      return Object(external_CoreHome_["translate"])('PrivacyManager_OptOutCodeTypeExplanation');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/OptOutCustomizer/OptOutCustomizer.vue



OptOutCustomizervue_type_script_lang_ts.render = OptOutCustomizervue_type_template_id_bb076364_render

/* harmony default export */ var OptOutCustomizer = (OptOutCustomizervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=cccc64d4

var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_1 = {
  class: "anonymizeLogData"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_2 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_3 = {
  class: "col s12 input-field"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_4 = {
  for: "anonymizeSite",
  class: "siteSelectorLabel"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_5 = {
  class: "sites_autocomplete"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_6 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_7 = {
  class: "col s6 input-field"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_8 = {
  for: "anonymizeStartDate",
  class: "active"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_9 = ["value"];
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_10 = {
  class: "col s6 input-field"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_11 = {
  for: "anonymizeEndDate",
  class: "active"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_12 = ["value"];
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_13 = {
  name: "anonymizeIp"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_14 = {
  name: "anonymizeLocation"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_15 = {
  name: "anonymizeTheUserId"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_16 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_17 = {
  class: "col s12 m6"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_18 = {
  for: "visit_columns"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_19 = {
  class: "innerFormField",
  name: "visit_columns"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_20 = ["onClick", "title"];
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_21 = {
  class: "col s12 m6"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_22 = {
  class: "form-help"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_23 = {
  class: "inline-help"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_24 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_25 = {
  class: "col s12"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_26 = {
  class: "form-group row"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_27 = {
  class: "col s12 m6"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_28 = {
  for: "action_columns"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_29 = {
  class: "innerFormField",
  name: "action_columns"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_30 = ["onClick", "title"];
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_31 = {
  class: "col s12 m6"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_32 = {
  class: "form-help"
};
var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_33 = {
  class: "inline-help"
};

var AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_34 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info"
}, null, -1);

function AnonymizeLogDatavue_type_template_id_cccc64d4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeSites')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
    id: "anonymizeSite",
    modelValue: _ctx.site,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.site = $event;
    }),
    "show-all-sites-item": true,
    "switch-site-on-select": false,
    "show-selected-site": true
  }, null, 8, ["modelValue"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataFrom')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
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
  }, null, 40, AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_9)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeRowDataTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
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
  }, null, 40, AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_12)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeIp",
    title: _ctx.translate('PrivacyManager_AnonymizeIp'),
    modelValue: _ctx.anonymizeIp,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.anonymizeIp = $event;
    }),
    introduction: _ctx.translate('General_Visit'),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeIpHelp')
  }, null, 8, ["title", "modelValue", "introduction", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeLocation",
    title: _ctx.translate('PrivacyManager_AnonymizeLocation'),
    modelValue: _ctx.anonymizeLocation,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.anonymizeLocation = $event;
    }),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeLocationHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "anonymizeTheUserId",
    title: _ctx.translate('PrivacyManager_AnonymizeUserId'),
    modelValue: _ctx.anonymizeUserId,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.anonymizeUserId = $event;
    }),
    "inline-help": _ctx.translate('PrivacyManager_AnonymizeUserIdHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedVisitColumns, function (visitColumn, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("selectedVisitColumns selectedVisitColumns".concat(index, " multiple valign-wrapper")),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
    }, null, 8, AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_20), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedVisitColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetVisitColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Action')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumns')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectedActionColumns, function (actionColumn, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("selectedActionColumns selectedActionColumns".concat(index, " multiple valign-wrapper")),
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
    }, null, 8, AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_30), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index + 1 !== _ctx.selectedActionColumns.length]])], 2);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_UnsetActionColumnsHelp')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [AnonymizeLogDatavue_type_template_id_cccc64d4_hoisted_34, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeProcessInfo')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "anonymizePastData",
    onConfirm: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.showPasswordConfirmModal = true;
    }),
    disabled: _ctx.isAnonymizePastDataDisabled,
    value: _ctx.translate('PrivacyManager_AnonymizeDataNow')
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
      return _ctx.showPasswordConfirmModal = $event;
    }),
    onConfirmed: _ctx.scheduleAnonymization
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_AnonymizeDataConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)];
    }),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=template&id=cccc64d4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/AnonymizeLogData/AnonymizeLogData.vue?vue&type=script&lang=ts




function sub(value) {
  if (value < 10) {
    return "0".concat(value);
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
      endDate: startDate,
      showPasswordConfirmModal: false
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
    scheduleAnonymization: function scheduleAnonymization(password) {
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
      params.passwordConfirmation = password;
      external_CoreHome_["AjaxHelper"].post({
        method: 'PrivacyManager.anonymizeSomeRawData'
      }, params).then(function () {
        window.location.reload(true);
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



AnonymizeLogDatavue_type_script_lang_ts.render = AnonymizeLogDatavue_type_template_id_cccc64d4_render

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
    value: function savePurgeDataSettings(apiMethod, settings, password) {
      this.privateState.isModified = false;
      return external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: apiMethod
      }, Object.assign(Object.assign({}, settings), {}, {
        enableDeleteLogs: settings.enableDeleteLogs ? '1' : '0',
        enableDeleteReports: settings.enableDeleteReports ? '1' : '0',
        passwordConfirmation: password
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
        this.currentRequest = undefined;
      }

      if (!forceEstimate && (!this.isEitherDeleteSectionEnabled() || this.isManualEstimationLinkShowing())) {
        return;
      }

      this.privateState.loadingEstimation = true;
      this.privateState.estimation = '';
      this.privateState.showEstimate = false;
      var settings = this.privateState.settings;
      var formData = Object.assign(Object.assign({}, settings), {}, {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=56e5fdb4

var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_1 = {
  id: "formDeleteSettings"
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_2 = {
  id: "deleteLogSettingEnabled"
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_3 = {
  class: "alert alert-warning deleteOldLogsWarning",
  style: {
    "width": "50%"
  }
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_4 = {
  href: "https://matomo.org/faq/general/faq_125",
  rel: "noreferrer noopener",
  target: "_blank"
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_5 = {
  id: "deleteLogSettings"
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_6 = {
  key: 0
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_7 = {
  key: 1
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_8 = {
  key: 2
};
var DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_9 = {
  key: 3
};
function DeleteOldLogsvue_type_template_id_56e5fdb4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.enabled = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteLog'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteRawDataInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ClickHere')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
      return _this.showPasswordConfirmModal = true;
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.showPasswordConfirmModal = $event;
    }),
    onConfirmed: _ctx.saveSettings
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.enabled && !_ctx.enableDeleteReports ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteLogsConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled && _ctx.enableDeleteReports ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteBothConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldLogsvue_type_template_id_56e5fdb4_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=template&id=56e5fdb4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts




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
  data: function data() {
    return {
      isLoading: false,
      enabled: parseInt(this.deleteData.config.delete_logs_enable, 10) === 1,
      deleteOlderThan: this.deleteData.config.delete_logs_older_than,
      showPasswordConfirmModal: false
    };
  },
  created: function created() {
    var _this = this;

    setTimeout(function () {
      ReportDeletionSettings_store.initSettings(_this.settings);
    });
  },
  methods: {
    saveSettings: function saveSettings(password) {
      var _this2 = this;

      var method = 'PrivacyManager.setDeleteLogsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings, password).finally(function () {
        _this2.isLoading = false;
      });
    },
    reloadDbStats: function reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
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
    },
    enableDeleteReports: function enableDeleteReports() {
      return !!ReportDeletionSettings_store.enableDeleteReports.value;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldLogs/DeleteOldLogs.vue



DeleteOldLogsvue_type_script_lang_ts.render = DeleteOldLogsvue_type_template_id_56e5fdb4_render

/* harmony default export */ var DeleteOldLogs = (DeleteOldLogsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=55144633

var DeleteOldReportsvue_type_template_id_55144633_hoisted_1 = {
  id: "formDeleteSettings"
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_2 = {
  id: "deleteReportsSettingEnabled"
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_3 = {
  class: "alert alert-warning",
  style: {
    "width": "50%"
  }
};

var DeleteOldReportsvue_type_template_id_55144633_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var DeleteOldReportsvue_type_template_id_55144633_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var DeleteOldReportsvue_type_template_id_55144633_hoisted_6 = {
  id: "deleteReportsSettings"
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_7 = {
  key: 0
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_8 = {
  key: 1
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_9 = {
  key: 2
};
var DeleteOldReportsvue_type_template_id_55144633_hoisted_10 = {
  key: 3
};
function DeleteOldReportsvue_type_template_id_55144633_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldReportsvue_type_template_id_55144633_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_55144633_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "deleteReportsEnable",
    "model-value": _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.enabled = $event;

      _ctx.reloadDbStats();
    }),
    title: _ctx.translate('PrivacyManager_UseDeleteReports'),
    "inline-help": _ctx.translate('PrivacyManager_DeleteAggregateReportsDetailedInfo')
  }, null, 8, ["model-value", "title", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_55144633_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo2', _ctx.deleteOldLogsText)), 1), DeleteOldReportsvue_type_template_id_55144633_hoisted_4, DeleteOldReportsvue_type_template_id_55144633_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsInfo3', _ctx.deleteOldLogsText)), 1)])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.enabled]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DeleteOldReportsvue_type_template_id_55144633_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
      return _this.showPasswordConfirmModal = true;
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[10] || (_cache[10] = function ($event) {
      return _ctx.showPasswordConfirmModal = $event;
    }),
    onConfirmed: _ctx.saveSettings
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.enabled && !_ctx.enableDeleteLogs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldReportsvue_type_template_id_55144633_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteReportsConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled && _ctx.enableDeleteLogs ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldReportsvue_type_template_id_55144633_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DeleteBothConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DeleteOldReportsvue_type_template_id_55144633_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", DeleteOldReportsvue_type_template_id_55144633_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=template&id=55144633

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts





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
      keepDataForSegments: parseInt(this.deleteData.config.delete_reports_keep_segment_reports, 10) === 1,
      showPasswordConfirmModal: false
    };
  },
  created: function created() {
    var _this = this;

    setTimeout(function () {
      ReportDeletionSettings_store.initSettings(_this.settings);
    });
  },
  methods: {
    saveSettings: function saveSettings(password) {
      var _this2 = this;

      var method = 'PrivacyManager.setDeleteReportsSettings';
      this.isLoading = true;
      ReportDeletionSettings_store.savePurgeDataSettings(method, this.settings, password).finally(function () {
        _this2.isLoading = false;
      });
    },
    reloadDbStats: function reloadDbStats() {
      ReportDeletionSettings_store.updateSettings(this.settings);
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
    },
    enableDeleteLogs: function enableDeleteLogs() {
      return !!ReportDeletionSettings_store.enableDeleteLogs.value;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DeleteOldReports/DeleteOldReports.vue



DeleteOldReportsvue_type_script_lang_ts.render = DeleteOldReportsvue_type_template_id_55144633_render

/* harmony default export */ var DeleteOldReports = (DeleteOldReportsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=7dfdd21e

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_1 = {
  id: "formDeleteSettings"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_2 = {
  id: "deleteSchedulingSettings"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_3 = {
  id: "deleteSchedulingSettingsInlineHelp",
  class: "inline-help-node"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_4 = {
  key: 0
};

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_9 = {
  key: 0,
  id: "deleteDataEstimateSect",
  class: "form-group row"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_10 = {
  class: "col s12",
  id: "databaseSizeHeadline"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_11 = {
  class: "col s12 m6"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_12 = ["innerHTML"];

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" ");

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_14 = {
  class: "col s12 m6"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_15 = {
  key: 0,
  class: "form-help"
};
var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_16 = {
  class: "ui-confirm",
  id: "saveSettingsBeforePurge"
};

var ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  role: "yes",
  type: "button",
  value: "{{ translate('General_Ok') }}"
}, null, -1);

function ScheduleReportDeletionvue_type_template_id_7dfdd21e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    id: "scheduleSettingsHeadline",
    "content-title": _ctx.translate('PrivacyManager_DeleteSchedulingSettings')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_3, [_ctx.deleteData.lastRun ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_LastDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.lastRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_5, ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_6])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_NextDelete')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.deleteData.nextRunPretty) + " ", 1), ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_7, ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
            id: "purgeDataNowLink",
            href: "#",
            onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
              return _ctx.executeDataPurge();
            }, ["prevent"]))
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PurgeNow')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showPurgeNowLink]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
            "loading-message": _ctx.translate('PrivacyManager_PurgingData'),
            loading: _ctx.loadingDataPurge
          }, null, 8, ["loading-message", "loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            id: "db-purged-message"
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_DBPurged')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.dataWasPurged]])])];
        }),
        _: 1
      }, 8, ["title", "modelValue", "options"])])]), _ctx.deleteData.config.enable_database_size_estimate === '1' || _ctx.deleteData.config.enable_database_size_estimate === 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_ReportsDataSavedEstimate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        id: "deleteDataEstimate",
        innerHTML: _ctx.$sanitize(_ctx.estimation)
      }, null, 8, ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_12), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEstimate]]), ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.loadingEstimation
      }, null, 8, ["loading"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_14, [_ctx.deleteData.config.enable_auto_database_size_estimate !== '1' && _ctx.deleteData.config.enable_auto_database_size_estimate !== 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        id: "getPurgeEstimateLink",
        href: "#",
        onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
          return _ctx.getPurgeEstimate();
        }, ["prevent"]))
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_GetPurgeEstimate')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[3] || (_cache[3] = function ($event) {
          return _ctx.showPasswordConfirmModal = true;
        }),
        saving: _ctx.isLoading
      }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModal,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          return _ctx.showPasswordConfirmModal = $event;
        }),
        onConfirmed: _ctx.save
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)];
        }),
        _: 1
      }, 8, ["modelValue", "onConfirmed"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModalForPurge,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
          return _ctx.showPasswordConfirmModalForPurge = $event;
        }),
        onConfirmed: _ctx.executePurgeNow
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_PurgeNowConfirm')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)];
        }),
        _: 1
      }, 8, ["modelValue", "onConfirmed"])];
    }),
    _: 1
  }, 8, ["content-title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isEitherDeleteSectionEnabled]])], 512), [[_directive_form]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('PrivacyManager_SaveSettingsBeforePurge')), 1), ScheduleReportDeletionvue_type_template_id_7dfdd21e_hoisted_17])], 64);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ScheduleReportDeletion/ScheduleReportDeletion.vue?vue&type=template&id=7dfdd21e

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
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
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
      deleteLowestInterval: this.deleteData.config.delete_logs_schedule_lowest_interval,
      showPasswordConfirmModal: false,
      showPasswordConfirmModalForPurge: false
    };
  },
  methods: {
    save: function save(password) {
      var method = 'PrivacyManager.setScheduleReportDeletionSettings';
      ReportDeletionSettings_store.savePurgeDataSettings(method, {
        deleteLowestInterval: this.deleteLowestInterval
      }, password);
    },
    executeDataPurge: function executeDataPurge() {
      if (ReportDeletionSettings_store.state.value.isModified) {
        // ask user if they really want to delete their old data
        external_CoreHome_["Matomo"].helper.modalConfirm('#saveSettingsBeforePurge', {
          yes: function yes() {
            return null;
          }
        });
        return;
      }

      this.showPasswordConfirmModalForPurge = true;
    },
    getPurgeEstimate: function getPurgeEstimate() {
      return ReportDeletionSettings_store.reloadDbStats(true);
    },
    executePurgeNow: function executePurgeNow(password) {
      var _this = this;

      this.loadingDataPurge = true;
      this.showPurgeNowLink = false; // execute a data purge

      return external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'PrivacyManager.executeDataPurge'
      }, {
        passwordConfirmation: password
      }).then(function () {
        // force reload
        ReportDeletionSettings_store.reloadDbStats();
        _this.dataWasPurged = true;
        setTimeout(function () {
          _this.dataWasPurged = false;
          _this.showPurgeNowLink = true;
        }, 2000);
      }).catch(function () {
        _this.showPurgeNowLink = true;
      }).finally(function () {
        _this.loadingDataPurge = false;
      });
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



ScheduleReportDeletionvue_type_script_lang_ts.render = ScheduleReportDeletionvue_type_template_id_7dfdd21e_render

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