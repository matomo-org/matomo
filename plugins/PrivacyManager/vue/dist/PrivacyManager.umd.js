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

/***/ "0e9f":
/***/ (function(module, exports) {



/***/ }),

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=118fbffa

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
  src: "plugins/Live/images/visitorProfileLaunch.png"
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
        "visit-segments-only": 1,
        idsite: _ctx.site.id
      }, null, 8, ["modelValue", "idsite"])])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        class: "findDataSubjects",
        value: "Find matching data subjects",
        onConfirm: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.findDataSubjects();
        }),
        disabled: !_ctx.segment_filter,
        saving: _ctx.isLoading
      }, null, 8, ["disabled", "saving"])];
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
      title: "(ID ".concat(dataSubject.idSite, ")")
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
      title: "".concat(dataSubject.deviceType, " ").concat(dataSubject.deviceModel)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.deviceTypeIcon
    }, null, 8, _hoisted_33)], 8, _hoisted_32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: dataSubject.operatingSystem
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.operatingSystemIcon
    }, null, 8, _hoisted_35)], 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: "".concat(dataSubject.browser, " ").concat(dataSubject.browserFamilyDescription)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      height: "16",
      src: dataSubject.browserIcon
    }, null, 8, _hoisted_37)], 8, _hoisted_36), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      title: "".concat(dataSubject.country, " ").concat(dataSubject.region)
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
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=template&id=118fbffa

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
 
// EXTERNAL MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue?vue&type=custom&index=0&blockType=todo
var ManageGdprvue_type_custom_index_0_blockType_todo = __webpack_require__("0e9f");
var ManageGdprvue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(ManageGdprvue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/ManageGdpr/ManageGdpr.vue



ManageGdprvue_type_script_lang_ts.render = render
/* custom blocks */

if (typeof ManageGdprvue_type_custom_index_0_blockType_todo_default.a === 'function') ManageGdprvue_type_custom_index_0_blockType_todo_default()(ManageGdprvue_type_script_lang_ts)


/* harmony default export */ var ManageGdpr = (ManageGdprvue_type_script_lang_ts);
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