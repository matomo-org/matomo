(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["ScheduledReports"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["ScheduledReports"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/ScheduledReports/vue/dist/";
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

/***/ "52e8":
/***/ (function(module, exports) {



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
__webpack_require__.d(__webpack_exports__, "ManageScheduledReport", function() { return /* reexport */ ManageScheduledReport; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=bde43de2

var _hoisted_1 = {
  class: "emailReports",
  ref: "root"
};
var _hoisted_2 = {
  ref: "reportSentSuccess"
};
var _hoisted_3 = {
  ref: "reportUpdatedSuccess"
};

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxError",
  style: {
    "display": "none"
  }
}, null, -1);

var _hoisted_5 = {
  id: "ajaxLoadingDiv",
  style: {
    "display": "none"
  }
};
var _hoisted_6 = {
  class: "loadingPiwik"
};
var _hoisted_7 = ["alt"];
var _hoisted_8 = {
  class: "loadingSegment"
};

var _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  id: "bottom"
}, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ListReports = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ListReports");

  var _component_AddReport = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AddReport");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ListReports, {
    title: _ctx.title,
    "user-login": _ctx.userLogin,
    "login-module": _ctx.loginModule,
    reports: _ctx.reports,
    "site-name": _ctx.siteName,
    "segment-editor-activated": _ctx.segmentEditorActivated,
    "saved-segments-by-id": _ctx.savedSegmentsById,
    periods: _ctx.periods,
    recipient: _ctx.recipient,
    "report-types": _ctx.reportTypes,
    "download-output-type": _ctx.downloadOutputType,
    language: _ctx.language,
    "report-formats-by-report-type": _ctx.reportFormatsByReportType,
    onCreate: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.createReport();
    }),
    onEdit: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.editReport($event);
    }),
    onDelete: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.deleteReport($event);
    }),
    onSendnow: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.sendReportNow($event);
    })
  }, null, 8, ["title", "user-login", "login-module", "reports", "site-name", "segment-editor-activated", "saved-segments-by-id", "periods", "recipient", "report-types", "download-output-type", "language", "report-formats-by-report-type"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showReportsList]]), _ctx.showReportForm ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_AddReport, {
    key: 0,
    report: _ctx.report,
    "param-periods": _ctx.paramPeriods,
    "report-type-options": _ctx.reportTypeOptions,
    "report-formats-by-report-type-options": _ctx.reportFormatsByReportTypeOptions,
    "report-formats": _ctx.reportFormats,
    "display-formats": _ctx.displayFormats,
    "reports-by-category-by-report-type": _ctx.reportsByCategoryByReportType,
    "allow-multiple-reports-by-report-type": _ctx.allowMultipleReportsByReportType,
    "reports-by-category": _ctx.reportsByCategory,
    "count-websites": _ctx.countWebsites,
    "site-name": _ctx.siteName,
    "selected-reports": _ctx.selectedReports,
    onToggleSelectedReport: _ctx.selectedReports[_ctx.$event.reportType][_ctx.$event.uniqueId],
    onChange: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.onChangeProperty($event.prop, $event.value);
    }),
    onSubmit: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.submitReport();
    })
  }, {
    "report-parameters": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "report-parameters")];
    }),
    _: 3
  }, 8, ["report", "param-periods", "report-type-options", "report-formats-by-report-type-options", "report-formats", "display-formats", "reports-by-category-by-report-type", "allow-multiple-reports-by-report-type", "reports-by-category", "count-websites", "site-name", "selected-reports", "onToggleSelectedReport"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_9])], 512);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=bde43de2

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=47b1ff78


var AddReportvue_type_template_id_47b1ff78_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);

var AddReportvue_type_template_id_47b1ff78_hoisted_2 = {
  key: 0
};
var AddReportvue_type_template_id_47b1ff78_hoisted_3 = ["innerHTML"];
var AddReportvue_type_template_id_47b1ff78_hoisted_4 = {
  id: "emailScheduleInlineHelp",
  class: "inline-help-node"
};

var AddReportvue_type_template_id_47b1ff78_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_47b1ff78_hoisted_6 = {
  id: "emailReportPeriodInlineHelp",
  class: "inline-help-node"
};

var AddReportvue_type_template_id_47b1ff78_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_47b1ff78_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_47b1ff78_hoisted_9 = {
  key: 0,
  id: "reportHourHelpText",
  class: "inline-help-node"
};
var _hoisted_10 = ["textContent"];
var _hoisted_11 = {
  ref: "reportParameters"
};
var _hoisted_12 = {
  class: "email"
};
var _hoisted_13 = {
  class: "report_evolution_graph"
};
var _hoisted_14 = {
  class: "row evolution-graph-period"
};
var _hoisted_15 = {
  class: "col s12"
};
var _hoisted_16 = {
  for: "report_evolution_period_for_each"
};
var _hoisted_17 = ["checked"];
var _hoisted_18 = ["innerHTML"];
var _hoisted_19 = {
  class: "col s12"
};
var _hoisted_20 = {
  for: "report_evolution_period_for_prev"
};
var _hoisted_21 = ["checked"];
var _hoisted_22 = ["value"];
var _hoisted_23 = {
  class: "row"
};
var _hoisted_24 = {
  class: "col s12"
};
var _hoisted_25 = {
  class: "reportCategory"
};
var _hoisted_26 = {
  class: "listReports"
};
var _hoisted_27 = ["name", "type", "id", "checked", "onChange"];
var _hoisted_28 = {
  key: 0,
  class: "entityInlineHelp"
};

var _hoisted_29 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_30 = ["innerHTML"];
function AddReportvue_type_template_id_47b1ff78_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "entityAddContainer",
    "content-title": _ctx.translate('ScheduledReports_CreateAndScheduleReport')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [AddReportvue_type_template_id_47b1ff78_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
        id: "addEditReport",
        onSubmit: _cache[13] || (_cache[13] = function ($event) {
          return _ctx.$emit('submit');
        })
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "website",
        title: _ctx.translate('General_Website'),
        disabled: true,
        value: _ctx.siteName
      }, null, 8, ["title", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "textarea",
        name: "report_description",
        title: _ctx.translate('General_Description'),
        "model-value": _ctx.report.description,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'description',
            value: $event
          });
        }),
        "inline-help": _ctx.translate('ScheduledReports_DescriptionOnFirstPage')
      }, null, 8, ["title", "model-value", "inline-help"])]), _ctx.segmentEditorActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_47b1ff78_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_segment",
        title: _ctx.translate('SegmentEditor_ChooseASegment'),
        "model-value": _ctx.report.idsegment,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'idsegment',
            value: $event
          });
        }),
        options: _ctx.savedSegmentsById
      }, {
        "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [_ctx.segmentEditorActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
            key: 0,
            id: "reportSegmentInlineHelp",
            class: "inline-help-node",
            innerHTML: _ctx.reportSegmentInlineHelp
          }, null, 8, AddReportvue_type_template_id_47b1ff78_hoisted_3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
        }),
        _: 1
      }, 8, ["title", "model-value", "options"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_schedule",
        "model-value": _ctx.report.period,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          _ctx.$emit('change', {
            prop: 'period',
            value: $event
          });

          _ctx.$emit('change', {
            prop: 'periodParam',
            value: _ctx.report.period === 'never' ? null : _ctx.report.period
          });
        }),
        title: _ctx.translate('ScheduledReports_EmailSchedule'),
        options: _ctx.periods
      }, {
        "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_47b1ff78_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_WeeklyScheduleHelp')) + " ", 1), AddReportvue_type_template_id_47b1ff78_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MonthlyScheduleHelp')), 1)])];
        }),
        _: 1
      }, 8, ["model-value", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_period",
        "model-value": _ctx.report.periodParam,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'periodParam',
            value: $event
          });
        }),
        options: _ctx.paramPeriods,
        title: _ctx.translate('ScheduledReports_ReportPeriod')
      }, {
        "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_47b1ff78_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp')) + " ", 1), AddReportvue_type_template_id_47b1ff78_hoisted_7, AddReportvue_type_template_id_47b1ff78_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp2')), 1)])];
        }),
        _: 1
      }, 8, ["model-value", "options", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_hour",
        "model-value": _ctx.report.hour,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'hour',
            value: $event
          });
        }),
        title: _ctx.translate('ScheduledReports_ReportHour', 'X'),
        options: _ctx.reportHours
      }, {
        "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [_ctx.timezoneOffset !== 0 && _ctx.timezoneOffset !== '0' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_47b1ff78_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.reportHourUtc)
          }, null, 8, _hoisted_10)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
        }),
        _: 1
      }, 8, ["model-value", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_type",
        disabled: _ctx.reportTypes.length === 1,
        "model-value": _ctx.report.type,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'type',
            value: $event
          });
        }),
        title: _ctx.translate('ScheduledReports_ReportType'),
        options: _ctx.reportTypeOptions
      }, null, 8, ["disabled", "model-value", "title", "options"])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportFormatsByReportTypeOptions, function (reportType, reportFormats) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          key: reportType
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
          uicontrol: "select",
          name: "report_format",
          title: _ctx.translate('ScheduledReports_ReportFormat'),
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(reportType),
          "model-value": _ctx.report["format".concat(reportType)],
          "onUpdate:modelValue": function onUpdateModelValue($event) {
            return _ctx.$emit('change', {
              prop: "format".concat(reportType),
              value: $event
            });
          },
          options: reportFormats
        }, null, 8, ["title", "class", "model-value", "onUpdate:modelValue", "options"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === reportType]])]);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "report-parameters")], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "display_format",
        "model-value": _ctx.report.displayFormat,
        "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'displayFormat',
            value: $event
          });
        }),
        options: _ctx.displayFormats,
        introduction: _ctx.translate('ScheduledReports_AggregateReportsFormat')
      }, null, 8, ["model-value", "options", "introduction"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "report_evolution_graph",
        title: _ctx.translate('ScheduledReports_EvolutionGraph', 5),
        "model-value": _ctx.report.evolutionGraph,
        "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'evolutionGraph',
            value: $event
          });
        })
      }, null, 8, ["title", "model-value"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.displayFormat in [2, '2', 3, '3']]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        id: "report_evolution_period_for_each",
        name: "report_evolution_period_for",
        type: "radio",
        value: "each",
        checked: _ctx.report.evolutionPeriodFor === 'each',
        onChange: _cache[8] || (_cache[8] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'evolutionPeriodFor',
            value: $event
          });
        })
      }, null, 40, _hoisted_17), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.evolutionGraphsShowForEachInPeriod)
      }, null, 8, _hoisted_18)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        id: "report_evolution_period_for_prev",
        name: "report_evolution_period_for",
        type: "radio",
        value: "prev",
        checked: _ctx.report.evolutionPeriodFor === 'prev',
        onChange: _cache[9] || (_cache[9] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'evolutionPeriodFor',
            value: $event
          });
        })
      }, null, 40, _hoisted_21), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_EvolutionGraphsShowForPreviousN', _ctx.frequencyPeriodPlural)) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "number",
        name: "report_evolution_period_n",
        value: _ctx.report.evolutionPeriodN,
        onKeydown: _cache[10] || (_cache[10] = function ($event) {
          return _ctx.onEvolutionPeriodN($event);
        }),
        onChange: _cache[11] || (_cache[11] = function ($event) {
          return _ctx.onEvolutionPeriodN($event);
        })
      }, null, 40, _hoisted_22)])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.displayFormat in [1, '1', 2, '2', 3, '3']]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email' && _ctx.report.formatemail !== 'csv' && _ctx.report.formatemail !== 'tsv']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportsIncluded')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportsByCategoryByReportTypeInColumns, function (reportType, reportColumns) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          name: "reportsList",
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("row ".concat(reportType)),
          key: reportType
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportColumns, function (reportsByCategory, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
            class: "col s12 m6",
            key: index
          }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportsByCategory, function (category, reports) {
            return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
              key: category
            }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_26, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reports, function (report) {
              var _ctx$selectedReports$;

              return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
                key: report.uniqueId
              }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
                name: "".concat(reportType, "Reports"),
                type: _ctx.allowMultipleReportsByReportType[reportType] ? 'checkbox' : 'radio',
                id: "".concat(reportType).concat(report.uniqueId),
                checked: (_ctx$selectedReports$ = _ctx.selectedReports[reportType]) === null || _ctx$selectedReports$ === void 0 ? void 0 : _ctx$selectedReports$[report.uniqueId],
                onChange: function onChange($event) {
                  return _ctx.$emit('toggleSelectedReport', {
                    reportType: reportType,
                    uniqueId: report.uniqueId
                  });
                }
              }, null, 40, _hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.name), 1), report.uniqueId === 'MultiSites_getAll' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportIncludeNWebsites', _ctx.countWebsites)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
            }), 128))]), _hoisted_29]);
          }), 128))]);
        }), 128))], 2)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === reportType]]);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        value: _ctx.saveButtonTitle,
        onConfirm: _cache[12] || (_cache[12] = function ($event) {
          return _ctx.$emit('submit');
        })
      }, null, 8, ["value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "entityCancel",
        innerHTML: _ctx.entityCancelText
      }, null, 8, _hoisted_30)], 544), [[_directive_form]])];
    }),
    _: 3
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=47b1ff78

// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/utilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function adjustHourToTimezone(hour, difference) {
  return "".concat((24 + parseFloat(hour) + difference) % 24);
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=script&lang=ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





var _window = window,
    ReportPlugin = _window.ReportPlugin;
/* harmony default export */ var AddReportvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    selectedReports: Object,
    paramPeriods: {
      type: Object,
      required: true
    },
    reportTypeOptions: {
      type: Object,
      required: true
    },
    reportFormatsByReportTypeOptions: {
      type: Object,
      required: true
    },
    displayFormats: {
      type: Object,
      required: true
    },
    reportsByCategoryByReportType: {
      type: Object,
      required: true
    },
    allowMultipleReportsByReportType: {
      type: Object,
      required: true
    },
    countWebsites: {
      type: Number,
      required: true
    },
    siteName: {
      type: String,
      required: true
    }
  },
  emits: ['submit', 'change'],
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  created: function created() {
    this.onEvolutionPeriodN = Object(external_CoreHome_["debounce"])(this.onEvolutionPeriodN, 50);
  },
  methods: {
    onEvolutionPeriodN: function onEvolutionPeriodN(value) {
      this.$emit('change', 'evolutionPeriodN', value);
    }
  },
  mounted: function mounted() {
    external_CoreHome_["Matomo"].helper.compileAngularComponents($(this.$refs.reportParameters));
  },
  computed: {
    reportsByCategoryByReportTypeInColumns: function reportsByCategoryByReportTypeInColumns() {
      var reportsByCategoryByReportType = this.reportsByCategoryByReportType;
      var inColumns = Object.entries(reportsByCategoryByReportType).map(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
            key = _ref2[0],
            reportsByCategory = _ref2[1];

        var newColumnAfter = Math.floor((Object.keys(reportsByCategory).length + 1) / 2);
        var column1 = {};
        var column2 = {};
        var currentColumn = column1;
        Object.entries(reportsByCategory).forEach(function (_ref3) {
          var _ref4 = _slicedToArray(_ref3, 2),
              category = _ref4[0],
              reports = _ref4[1];

          currentColumn[category] = reports;

          if (Object.keys(currentColumn).length > newColumnAfter) {
            currentColumn = column2;
          }
        });
        return [key, [column1, column2]];
      });
      return Object.fromEntries(inColumns);
    },
    entityCancelText: function entityCancelText() {
      return Object(external_CoreHome_["translate"])('General_OrCancel', '<a class="entityCancelLink">', '</a>');
    },
    frequencyPeriodSingle: function frequencyPeriodSingle() {
      if (!this.report || !this.report.period) {
        return '';
      }

      var translation = ReportPlugin.periodTranslations[this.report.period];

      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }

      return translation.single;
    },
    frequencyPeriodPlural: function frequencyPeriodPlural() {
      if (!this.report || !this.report.period) {
        return '';
      }

      var translation = ReportPlugin.periodTranslations[this.report.period];

      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }

      return translation.plural;
    },
    evolutionGraphsShowForEachInPeriod: function evolutionGraphsShowForEachInPeriod() {
      return Object(external_CoreHome_["translate"])('ScheduledReports_EvolutionGraphsShowForEachInPeriod', '<strong>', '</strong>', this.frequencyPeriodSingle);
    },
    reportSegmentInlineHelp: function reportSegmentInlineHelp() {
      return Object(external_CoreHome_["translate"])('ScheduledReports_Segment_Help', '<a href="./" rel="noreferrer noopener" target="_blank">', '</a>', Object(external_CoreHome_["translate"])('SegmentEditor_DefaultAllVisits'), Object(external_CoreHome_["translate"])('SegmentEditor_AddNewSegment'));
    },
    timezoneOffset: function timezoneOffset() {
      return external_CoreHome_["Matomo"].timezoneOffset;
    },
    timeZoneDifferenceInHours: function timeZoneDifferenceInHours() {
      return external_CoreHome_["Matomo"].timezoneOffset / 3600;
    },
    reportHours: function reportHours() {
      var hours = [];

      for (var i = 0; i < 24; i += 1) {
        if (this.timeZoneDifferenceInHours * 2 % 2 !== 0) {
          hours.push({
            key: "".concat(i, ".5"),
            value: "".concat(i, ":30")
          });
        } else {
          hours.push({
            key: "".concat(i),
            value: "".concat(i)
          });
        }
      }

      return hours;
    },
    reportHourUtc: function reportHourUtc() {
      var reportHour = adjustHourToTimezone(this.report.hour, -this.timeZoneDifferenceInHours);
      return Object(external_CoreHome_["translate"])('ScheduledReports_ReportHourWithUTC', [reportHour]);
    },
    saveButtonTitle: function saveButtonTitle() {
      var isCreate = this.report.idreport > 0;
      return isCreate ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue



AddReportvue_type_script_lang_ts.render = AddReportvue_type_template_id_47b1ff78_render

/* harmony default export */ var AddReport = (AddReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=4db01cfb

var ListReportsvue_type_template_id_4db01cfb_hoisted_1 = {
  class: "first"
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_2 = {
  key: 0
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_3 = {
  colspan: "7"
};

var ListReportsvue_type_template_id_4db01cfb_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("› ");

var ListReportsvue_type_template_id_4db01cfb_hoisted_7 = ["href"];

var ListReportsvue_type_template_id_4db01cfb_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_10 = {
  key: 1
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_11 = {
  colspan: "7"
};

var ListReportsvue_type_template_id_4db01cfb_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_15 = {
  class: "first"
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_16 = {
  key: 0,
  class: "entityInlineHelp",
  style: {
    "font-size": "9pt"
  }
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_17 = {
  key: 0
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_18 = {
  key: 1
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_19 = {
  key: 0
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_20 = {
  key: 0
};

var ListReportsvue_type_template_id_4db01cfb_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_22 = ["onClick"];
var ListReportsvue_type_template_id_4db01cfb_hoisted_23 = ["src"];
var ListReportsvue_type_template_id_4db01cfb_hoisted_24 = ["id", "action"];
var ListReportsvue_type_template_id_4db01cfb_hoisted_25 = ["value"];

var ListReportsvue_type_template_id_4db01cfb_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);

var ListReportsvue_type_template_id_4db01cfb_hoisted_27 = ["onClick", "id"];
var ListReportsvue_type_template_id_4db01cfb_hoisted_28 = ["src"];
var ListReportsvue_type_template_id_4db01cfb_hoisted_29 = {
  style: {
    "text-align": "center",
    "padding-top": "2px"
  }
};
var ListReportsvue_type_template_id_4db01cfb_hoisted_30 = ["onClick", "title"];

var _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);

var _hoisted_32 = [_hoisted_31];
var _hoisted_33 = {
  style: {
    "text-align": "center",
    "padding-top": "2px"
  }
};
var _hoisted_34 = ["onClick", "title"];

var _hoisted_35 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);

var _hoisted_36 = [_hoisted_35];
var _hoisted_37 = {
  class: "tableActionBar"
};

var _hoisted_38 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

function ListReportsvue_type_template_id_4db01cfb_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    id: "entityEditContainer",
    class: "entityTableContainer",
    "help-url": "https://matomo.org/docs/email-reports/",
    feature: true,
    "content-title": _ctx.title
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      var _ctx$reports;

      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", ListReportsvue_type_template_id_4db01cfb_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_EmailSchedule')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportFormat')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Edit')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 1)])]), _ctx.userLogin === 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_4db01cfb_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_4db01cfb_hoisted_3, [ListReportsvue_type_template_id_4db01cfb_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MustBeLoggedIn')) + " ", 1), ListReportsvue_type_template_id_4db01cfb_hoisted_5, ListReportsvue_type_template_id_4db01cfb_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: "index.php?module=".concat(_ctx.loginModule)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Login_LogIn')), 9, ListReportsvue_type_template_id_4db01cfb_hoisted_7), ListReportsvue_type_template_id_4db01cfb_hoisted_8, ListReportsvue_type_template_id_4db01cfb_hoisted_9])])) : !((_ctx$reports = _ctx.reports) !== null && _ctx$reports !== void 0 && _ctx$reports.length) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_4db01cfb_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_4db01cfb_hoisted_11, [ListReportsvue_type_template_id_4db01cfb_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ThereIsNoReportToManage', _ctx.siteName)) + ". ", 1), ListReportsvue_type_template_id_4db01cfb_hoisted_13, ListReportsvue_type_template_id_4db01cfb_hoisted_14])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reports, function (report) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: report.idreport
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_4db01cfb_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.description) + " ", 1), _ctx.segmentEditorActivated && report.idsegment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ListReportsvue_type_template_id_4db01cfb_hoisted_16, [_ctx.savedSegmentsById[report.idsegment] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_4db01cfb_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.savedSegmentsById.report.idsegment), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_4db01cfb_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SegmentDeleted')), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.periods.report.period), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.format ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_4db01cfb_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.format.toUpperCase()), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.recipients.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_4db01cfb_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_NoRecipients')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(report.recipients, function (recipient, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
            key: index
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(recipient) + " ", 1), ListReportsvue_type_template_id_4db01cfb_hoisted_21]);
        }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          href: "#",
          name: "linkSendNow",
          class: "link_but withIcon",
          style: {
            "margin-top": "3px"
          },
          onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
            return _ctx.$emit('sendnow', report.idreport);
          }, ["prevent"])
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
          border: "0",
          src: _ctx.reportTypes.report.type
        }, null, 8, ListReportsvue_type_template_id_4db01cfb_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportNow')), 1)], 8, ListReportsvue_type_template_id_4db01cfb_hoisted_22)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
          method: "POST",
          target: "_blank",
          id: "downloadReportForm_".concat(report.idreport),
          action: _ctx.linkTo({
            'module': 'API',
            'segment': null,
            'method': 'ScheduledReports.generateReport',
            'idReport': report.idreport,
            'outputType': _ctx.downloadOutputType,
            'language': _ctx.language,
            'format': report.format in ['html', 'csv', 'tsv'] ? report.format : false
          })
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          type: "hidden",
          name: "token_auth",
          value: _ctx.token_auth
        }, null, 8, ListReportsvue_type_template_id_4db01cfb_hoisted_25), ListReportsvue_type_template_id_4db01cfb_hoisted_26], 8, ListReportsvue_type_template_id_4db01cfb_hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          href: "",
          rel: "noreferrer noopener",
          name: "linkDownloadReport",
          class: "link_but withIcon",
          onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
            return _ctx.displayReport(report.idreport);
          }, ["prevent"]),
          id: report.idreport
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
          border: "0",
          width: "16px",
          height: "16px",
          src: _ctx.reportFormatsByReportType[report.type][report.format]
        }, null, 8, ListReportsvue_type_template_id_4db01cfb_hoisted_28), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1)], 8, ListReportsvue_type_template_id_4db01cfb_hoisted_27)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_4db01cfb_hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: function onClick($event) {
            return _ctx.$emit('edit', parseInt(report.idreport, 10));
          },
          title: _ctx.translate('General_Edit')
        }, _hoisted_32, 8, ListReportsvue_type_template_id_4db01cfb_hoisted_30)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: function onClick($event) {
            return _ctx.$emit('delete', report.idreport);
          },
          title: _ctx.translate('General_Delete')
        }, _hoisted_36, 8, _hoisted_34)])]);
      }), 128))], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_37, [_ctx.userLogin !== 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 0,
        id: "add-report",
        onClick: _cache[0] || (_cache[0] = function ($event) {
          return _ctx.$emit('create');
        })
      }, [_hoisted_38, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_CreateAndScheduleReport')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=4db01cfb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts


/* harmony default export */ var ListReportsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    },
    userLogin: {
      type: String,
      required: true
    },
    loginModule: {
      type: String,
      required: true
    },
    reports: {
      type: Array,
      required: true
    },
    siteName: {
      type: String,
      required: true
    },
    segmentEditorActivated: Boolean,
    savedSegmentsById: Object,
    periods: {
      type: Object,
      required: true
    },
    downloadOutputType: {
      type: String,
      required: true
    },
    language: {
      type: String,
      required: true
    },
    reportFormatsByReportType: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  emits: ['create', 'edit', 'delete', 'sendnow'],
  methods: {
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(params));
    },
    displayReport: function displayReport(reportId) {
      $("#downloadReportForm_".concat(reportId)).submit();
    }
  },
  computed: {
    token_auth: function token_auth() {
      return external_CoreHome_["Matomo"].token_auth;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue



ListReportsvue_type_script_lang_ts.render = ListReportsvue_type_template_id_4db01cfb_render

/* harmony default export */ var ListReports = (ListReportsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=script&lang=ts







function scrollToTop() {
  external_CoreHome_["Matomo"].helper.lazyScrollTo('.emailReports', 200);
}

function updateParameters(reportType, report) {
  var _window$updateReportP;

  if ((_window$updateReportP = window.updateReportParametersFunctions) !== null && _window$updateReportP !== void 0 && _window$updateReportP[reportType]) {
    window.updateReportParametersFunctions[reportType](report);
  }
}

function resetParameters(reportType, report) {
  var _window$resetReportPa;

  if ((_window$resetReportPa = window.resetReportParametersFunctions) !== null && _window$resetReportPa !== void 0 && _window$resetReportPa[reportType]) {
    window.resetReportParametersFunctions[reportType](report);
  }
}

var ManageScheduledReportvue_type_script_lang_ts_window = window,
    ManageScheduledReportvue_type_script_lang_ts_$ = ManageScheduledReportvue_type_script_lang_ts_window.$,
    ManageScheduledReportvue_type_script_lang_ts_ReportPlugin = ManageScheduledReportvue_type_script_lang_ts_window.ReportPlugin;
var ManageScheduledReportvue_type_script_lang_ts_timeZoneDifferenceInHours = external_CoreHome_["Matomo"].timezoneOffset / 3600;
/* harmony default export */ var ManageScheduledReportvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    },
    userLogin: {
      type: String,
      required: true
    },
    loginModule: {
      type: String,
      required: true
    },
    reports: {
      type: Array,
      required: true
    },
    siteName: {
      type: String,
      required: true
    },
    segmentEditorActivated: Boolean,
    savedSegmentsById: Object,
    periods: {
      type: Object,
      required: true
    },
    downloadOutputType: {
      type: String,
      required: true
    },
    language: {
      type: String,
      required: true
    },
    reportFormatsByReportType: {
      type: Object,
      required: true
    },
    paramPeriods: {
      type: Object,
      required: true
    },
    reportTypeOptions: {
      type: Object,
      required: true
    },
    reportFormatsByReportTypeOptions: {
      type: Object,
      required: true
    },
    displayFormats: {
      type: Object,
      required: true
    },
    reportsByCategoryByReportType: {
      type: Object,
      required: true
    },
    allowMultipleReportsByReportType: {
      type: Object,
      required: true
    },
    countWebsites: {
      type: Number,
      required: true
    }
  },
  components: {
    AddReport: AddReport,
    ListReports: ListReports
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"],
    Form: external_CorePluginsAdmin_["Form"]
  },
  mounted: function mounted() {
    var _this = this;

    ManageScheduledReportvue_type_script_lang_ts_$(this.$refs.root).on('click', 'a.entityCancelLink', function () {
      _this.showListOfReports();
    });
  },
  data: function data() {
    return {
      showReportsList: true,
      report: {},
      selectedReports: {}
    };
  },
  methods: {
    sendReportNow: function sendReportNow(idReport) {
      var _this2 = this;

      scrollToTop();
      external_CoreHome_["AjaxHelper"].post({
        method: 'ScheduledReports.sendReport'
      }, {
        idReport: idReport,
        force: true
      }).then(function () {
        _this2.fadeInOutSuccessMessage(_this2.$refs.reportSentSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportSent'));
      });
    },
    formSetEditReport: function formSetEditReport(idReport) {
      var _this3 = this;

      var report = {
        idreport: idReport,
        type: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultReportType,
        format: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultReportFormat,
        description: '',
        period: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultPeriod,
        hour: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultHour,
        reports: [],
        idsegment: '',
        evolutionPeriodFor: 'prev',
        evolutionPeriodN: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultEvolutionPeriodN,
        periodParam: ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.defaultPeriod
      };

      if (idReport > 0) {
        this.report = ManageScheduledReportvue_type_script_lang_ts_ReportPlugin.reportList[idReport];
        updateParameters(report.type, this.report);
      } else {
        resetParameters(report.type, this.report);
      }

      report.hour = adjustHourToTimezone(report.hour, ManageScheduledReportvue_type_script_lang_ts_timeZoneDifferenceInHours);
      this.selectedReports = {};
      Object.keys(report.reports).forEach(function (key) {
        _this3.selectedReports[report.type] = _this3.selectedReports[report.type] || {};
        _this3.selectedReports[report.type][key] = true;
      });
      report["format".concat(report.type)] = report.format;

      if (!report.idsegment) {
        report.idsegment = '';
      }

      this.report = report;
      this.report.description = external_CoreHome_["Matomo"].helper.htmlDecode(report.description);
    },
    fadeInOutSuccessMessage: function fadeInOutSuccessMessage(selector, message) {
      external_CoreHome_["NotificationsStore"].show({
        message: message,
        placeat: selector,
        context: 'success',
        noclear: true,
        type: 'toast',
        style: {
          display: 'inline-block',
          marginTop: '10px'
        },
        id: 'scheduledReportSuccess'
      });
      external_CoreHome_["Matomo"].helper.refreshAfter(2);
    },
    changedReportType: function changedReportType() {
      resetParameters(this.report.type, this.report);
    },
    deleteReport: function deleteReport(idReport) {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirm', {
        yes: function yes() {
          external_CoreHome_["AjaxHelper"].post({
            method: 'ScheduledReports.deleteReport'
          }, {
            idReport: idReport
          }, {
            redirectOnSuccess: {}
          });
        }
      });
    },
    showListOfReports: function showListOfReports(shouldScrollToTop) {
      this.showReportsList = true;
      external_CoreHome_["Matomo"].helper.hideAjaxError();

      if (typeof shouldScrollToTop === 'undefined' || shouldScrollToTop) {
        scrollToTop();
      }
    },
    createReport: function createReport() {
      this.showReportsList = false;
      this.formSetEditReport(0);
    },
    editReport: function editReport(reportId) {
      this.showReportsList = false;
      this.formSetEditReport(reportId);
    },
    submitReport: function submitReport() {
      var _this4 = this;

      var apiParameters = {
        idReport: this.report.idreport,
        description: this.report.description,
        idSegment: this.report.idsegment,
        reportType: this.report.type,
        reportFormat: this.report["format".concat(this.report.type)],
        periodParam: this.report.periodParam,
        evolutionPeriodFor: this.report.evolutionPeriodFor
      };

      if (apiParameters.evolutionPeriodFor !== 'each') {
        apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
      }

      var period = this.report.period;
      var hour = adjustHourToTimezone(this.report.hour, -ManageScheduledReportvue_type_script_lang_ts_timeZoneDifferenceInHours);
      var reports = Object.keys(this.selectedReports[apiParameters.reportType]).filter(function (name) {
        return _this4.selectedReports[apiParameters.reportType][name];
      });

      if (reports.length > 0) {
        apiParameters.reports = reports;
      }

      apiParameters.parameters = window.getReportParametersFunctions[this.report.type](this.report);
      var isCreate = this.report.idreport > 0;
      external_CoreHome_["AjaxHelper"].post({
        method: isCreate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
        period: period,
        hour: hour
      }, apiParameters, {
        redirectOnSuccess: true
      }).then(function () {
        _this4.fadeInOutSuccessMessage(_this4.$refs.reportUpdatedSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportUpdated'));
      });
      return false;
    },
    onChangeProperty: function onChangeProperty(propName, value) {
      this.report[propName] = value;

      if (propName === 'type') {
        this.changedReportType();
      }
    }
  },
  computed: {
    showReportForm: function showReportForm() {
      return !this.showListOfReports;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=custom&index=0&blockType=todo
var ManageScheduledReportvue_type_custom_index_0_blockType_todo = __webpack_require__("52e8");
var ManageScheduledReportvue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(ManageScheduledReportvue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue



ManageScheduledReportvue_type_script_lang_ts.render = render
/* custom blocks */

if (typeof ManageScheduledReportvue_type_custom_index_0_blockType_todo_default.a === 'function') ManageScheduledReportvue_type_custom_index_0_blockType_todo_default()(ManageScheduledReportvue_type_script_lang_ts)


/* harmony default export */ var ManageScheduledReport = (ManageScheduledReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/index.ts
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
//# sourceMappingURL=ScheduledReports.umd.js.map