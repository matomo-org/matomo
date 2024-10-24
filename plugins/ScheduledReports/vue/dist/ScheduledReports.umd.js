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
__webpack_require__.d(__webpack_exports__, "ReportParameters", function() { return /* reexport */ ReportParameters; });
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

// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/types.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=6b4d0de2

const _hoisted_1 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return _ctx.report ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "report_email_me",
    introduction: _ctx.translate('ScheduledReports_SendReportTo'),
    "model-value": _ctx.report.emailMe,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('change', 'emailMe', $event)),
    title: `${_ctx.translate('ScheduledReports_SentToMe')} (${_ctx.currentUserEmail})`
  }, null, 8, ["introduction", "model-value", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email']])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "textarea",
    "var-type": "array",
    "model-value": _ctx.report.additionalEmails,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.$emit('change', 'additionalEmails', $event)),
    title: _ctx.translate('ScheduledReports_AlsoSendReportToTheseEmails')
  }, null, 8, ["model-value", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email']])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=6b4d0de2

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts


/* harmony default export */ var ReportParametersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    reportType: {
      type: String,
      required: true
    },
    defaultDisplayFormat: {
      type: Number,
      required: true
    },
    defaultEmailMe: {
      type: Boolean,
      required: true
    },
    defaultEvolutionGraph: {
      type: Boolean,
      required: true
    },
    currentUserEmail: {
      type: String,
      required: true
    }
  },
  emits: ['change'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  setup(props) {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions
    } = window;
    if (!resetReportParametersFunctions[props.reportType]) {
      resetReportParametersFunctions[props.reportType] = theReport => {
        theReport.displayFormat = props.defaultDisplayFormat;
        theReport.emailMe = props.defaultEmailMe;
        theReport.evolutionGraph = props.defaultEvolutionGraph;
        theReport.additionalEmails = [];
      };
    }
    if (!updateReportParametersFunctions[props.reportType]) {
      updateReportParametersFunctions[props.reportType] = theReport => {
        if (!(theReport !== null && theReport !== void 0 && theReport.parameters)) {
          return;
        }
        ['displayFormat', 'emailMe', 'evolutionGraph', 'additionalEmails'].forEach(field => {
          if (field in theReport.parameters) {
            theReport[field] = theReport.parameters[field];
          }
        });
      };
    }
    if (!getReportParametersFunctions[props.reportType]) {
      getReportParametersFunctions[props.reportType] = theReport => ({
        displayFormat: theReport.displayFormat,
        emailMe: theReport.emailMe,
        evolutionGraph: theReport.evolutionGraph,
        additionalEmails: theReport.additionalEmails || []
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue



ReportParametersvue_type_script_lang_ts.render = render

/* harmony default export */ var ReportParameters = (ReportParametersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=9949de62

const ManageScheduledReportvue_type_template_id_9949de62_hoisted_1 = {
  class: "emailReports",
  ref: "root"
};
const _hoisted_2 = {
  ref: "reportSentSuccess"
};
const _hoisted_3 = {
  ref: "reportUpdatedSuccess"
};
const _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxError",
  style: {
    "display": "none"
  }
}, null, -1);
const _hoisted_5 = {
  id: "ajaxLoadingDiv",
  style: {
    "display": "none"
  }
};
const _hoisted_6 = {
  class: "loadingPiwik"
};
const _hoisted_7 = ["alt"];
const _hoisted_8 = {
  class: "loadingSegment"
};
const _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  id: "bottom"
}, null, -1);
function ManageScheduledReportvue_type_template_id_9949de62_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ListReports = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ListReports");
  const _component_AddReport = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AddReport");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageScheduledReportvue_type_template_id_9949de62_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: "plugins/Morpheus/images/loading-blue.gif",
    alt: _ctx.translate('General_LoadingData')
  }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ListReports, {
    "content-title": _ctx.contentTitle,
    "user-login": _ctx.userLogin,
    "login-module": _ctx.loginModule,
    reports: _ctx.reports,
    "site-name": _ctx.decodedSiteName,
    "segment-editor-activated": _ctx.segmentEditorActivated,
    "saved-segments-by-id": _ctx.savedSegmentsById,
    periods: _ctx.periods,
    "report-types": _ctx.reportTypes,
    "download-output-type": _ctx.downloadOutputType,
    language: _ctx.language,
    "report-formats-by-report-type": _ctx.reportFormatsByReportType,
    onCreate: _cache[0] || (_cache[0] = $event => _ctx.createReport()),
    onEdit: _cache[1] || (_cache[1] = $event => _ctx.editReport($event)),
    onDelete: _cache[2] || (_cache[2] = $event => _ctx.deleteReport($event)),
    onSendnow: _cache[3] || (_cache[3] = $event => _ctx.sendReportNow($event))
  }, null, 8, ["content-title", "user-login", "login-module", "reports", "site-name", "segment-editor-activated", "saved-segments-by-id", "periods", "report-types", "download-output-type", "language", "report-formats-by-report-type"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showReportsList]]), _ctx.showReportForm ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_AddReport, {
    key: 0,
    report: _ctx.report,
    periods: _ctx.periods,
    "param-periods": _ctx.paramPeriods,
    "report-type-options": _ctx.reportTypeOptions,
    "report-formats-by-report-type-options": _ctx.reportFormatsByReportTypeOptions,
    "display-formats": _ctx.displayFormats,
    "reports-by-category-by-report-type": _ctx.reportsByCategoryByReportType,
    "allow-multiple-reports-by-report-type": _ctx.allowMultipleReportsByReportType,
    "count-websites": _ctx.countWebsites,
    "site-name": _ctx.decodedSiteName,
    "selected-reports": _ctx.selectedReports,
    "report-types": _ctx.reportTypes,
    "segment-editor-activated": _ctx.segmentEditorActivated,
    "saved-segments-by-id": _ctx.savedSegmentsById,
    onToggleSelectedReport: _cache[4] || (_cache[4] = $event => _ctx.toggleSelectedReport($event.reportType, $event.uniqueId)),
    onChange: _cache[5] || (_cache[5] = $event => _ctx.onChangeProperty($event.prop, $event.value)),
    onSubmit: _cache[6] || (_cache[6] = $event => _ctx.submitReport())
  }, {
    "report-parameters": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "report-parameters")]),
    _: 3
  }, 8, ["report", "periods", "param-periods", "report-type-options", "report-formats-by-report-type-options", "display-formats", "reports-by-category-by-report-type", "allow-multiple-reports-by-report-type", "count-websites", "site-name", "selected-reports", "report-types", "segment-editor-activated", "saved-segments-by-id"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_9])], 512);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=9949de62

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=1cbdcec1

const AddReportvue_type_template_id_1cbdcec1_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);
const AddReportvue_type_template_id_1cbdcec1_hoisted_2 = {
  key: 0
};
const AddReportvue_type_template_id_1cbdcec1_hoisted_3 = ["innerHTML"];
const AddReportvue_type_template_id_1cbdcec1_hoisted_4 = {
  id: "emailScheduleInlineHelp",
  class: "inline-help-node"
};
const AddReportvue_type_template_id_1cbdcec1_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AddReportvue_type_template_id_1cbdcec1_hoisted_6 = {
  id: "emailReportPeriodInlineHelp",
  class: "inline-help-node"
};
const AddReportvue_type_template_id_1cbdcec1_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AddReportvue_type_template_id_1cbdcec1_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AddReportvue_type_template_id_1cbdcec1_hoisted_9 = {
  key: 0,
  id: "reportHourHelpText",
  class: "inline-help-node"
};
const _hoisted_10 = ["textContent"];
const _hoisted_11 = {
  ref: "reportParameters"
};
const _hoisted_12 = {
  class: "email"
};
const _hoisted_13 = {
  class: "report_evolution_graph"
};
const _hoisted_14 = {
  class: "row evolution-graph-period"
};
const _hoisted_15 = {
  class: "col s12"
};
const _hoisted_16 = {
  for: "report_evolution_period_for_each"
};
const _hoisted_17 = ["checked"];
const _hoisted_18 = ["innerHTML"];
const _hoisted_19 = {
  class: "col s12"
};
const _hoisted_20 = {
  for: "report_evolution_period_for_prev"
};
const _hoisted_21 = ["checked"];
const _hoisted_22 = ["value"];
const _hoisted_23 = {
  class: "row"
};
const _hoisted_24 = {
  class: "col s12"
};
const _hoisted_25 = {
  class: "reportCategory"
};
const _hoisted_26 = {
  class: "listReports"
};
const _hoisted_27 = ["name", "type", "id", "checked", "onChange"];
const _hoisted_28 = {
  key: 0,
  class: "entityInlineHelp"
};
const _hoisted_29 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_30 = ["innerHTML"];
function AddReportvue_type_template_id_1cbdcec1_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "entityAddContainer",
    "content-title": _ctx.contentTitle
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [AddReportvue_type_template_id_1cbdcec1_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("form", {
      id: "addEditReport",
      onSubmit: _cache[13] || (_cache[13] = $event => _ctx.$emit('submit'))
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "website",
      title: _ctx.translate('General_Website'),
      disabled: true,
      "model-value": _ctx.siteName
    }, null, 8, ["title", "model-value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "report_description",
      title: _ctx.translate('General_Description'),
      "model-value": _ctx.report.description,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('change', {
        prop: 'description',
        value: $event
      })),
      "inline-help": _ctx.translate('ScheduledReports_DescriptionOnFirstPage')
    }, null, 8, ["title", "model-value", "inline-help"])]), _ctx.segmentEditorActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_1cbdcec1_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "report_segment",
      title: _ctx.translate('SegmentEditor_ChooseASegment'),
      "model-value": _ctx.report.idsegment,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.$emit('change', {
        prop: 'idsegment',
        value: $event
      })),
      options: _ctx.savedSegmentsById
    }, {
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.segmentEditorActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        key: 0,
        id: "reportSegmentInlineHelp",
        class: "inline-help-node",
        innerHTML: _ctx.$sanitize(_ctx.reportSegmentInlineHelp)
      }, null, 8, AddReportvue_type_template_id_1cbdcec1_hoisted_3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }, 8, ["title", "model-value", "options"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "report_schedule",
      "model-value": _ctx.report.period,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => {
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
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_1cbdcec1_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_WeeklyScheduleHelp')) + " ", 1), AddReportvue_type_template_id_1cbdcec1_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MonthlyScheduleHelp')), 1)])]),
      _: 1
    }, 8, ["model-value", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "report_period",
      "model-value": _ctx.report.periodParam,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.$emit('change', {
        prop: 'periodParam',
        value: $event
      })),
      options: _ctx.paramPeriods,
      title: _ctx.translate('ScheduledReports_ReportPeriod')
    }, {
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_1cbdcec1_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp')) + " ", 1), AddReportvue_type_template_id_1cbdcec1_hoisted_7, AddReportvue_type_template_id_1cbdcec1_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp2')), 1)])]),
      _: 1
    }, 8, ["model-value", "options", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "report_hour",
      "model-value": _ctx.report.hour,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.$emit('change', {
        prop: 'hour',
        value: $event
      })),
      title: _ctx.translate('ScheduledReports_ReportHour', 'X'),
      options: _ctx.reportHours
    }, {
      "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.timezoneOffset !== 0 && _ctx.timezoneOffset !== '0' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_1cbdcec1_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.reportHourUtc)
      }, null, 8, _hoisted_10)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }, 8, ["model-value", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "report_type",
      disabled: _ctx.reportTypes.length === 1,
      "model-value": _ctx.report.type,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.$emit('change', {
        prop: 'type',
        value: $event
      })),
      title: _ctx.translate('ScheduledReports_ReportType'),
      options: _ctx.reportTypeOptions
    }, null, 8, ["disabled", "model-value", "title", "options"])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportFormatsByReportTypeOptions, (reportFormats, reportType) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        key: reportType
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "report_format",
        title: _ctx.translate('ScheduledReports_ReportFormat'),
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(reportType),
        "model-value": _ctx.report[`format${reportType}`],
        "onUpdate:modelValue": $event => _ctx.$emit('change', {
          prop: `format${reportType}`,
          value: $event
        }),
        options: reportFormats
      }, null, 8, ["title", "class", "model-value", "onUpdate:modelValue", "options"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === reportType]])]);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "report-parameters")], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "display_format",
      "model-value": _ctx.report.displayFormat,
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.$emit('change', {
        prop: 'displayFormat',
        value: $event
      })),
      options: _ctx.displayFormats,
      introduction: _ctx.translate('ScheduledReports_AggregateReportsFormat')
    }, null, 8, ["model-value", "options", "introduction"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "report_evolution_graph",
      title: _ctx.translate('ScheduledReports_EvolutionGraph', 5),
      "model-value": _ctx.report.evolutionGraph,
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.$emit('change', {
        prop: 'evolutionGraph',
        value: $event
      }))
    }, null, 8, ["title", "model-value"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], [2, '2', 3, '3'].indexOf(_ctx.report.displayFormat) !== -1]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      id: "report_evolution_period_for_each",
      name: "report_evolution_period_for",
      type: "radio",
      value: "each",
      checked: _ctx.report.evolutionPeriodFor === 'each',
      onChange: _cache[8] || (_cache[8] = $event => _ctx.$emit('change', {
        prop: 'evolutionPeriodFor',
        value: $event.target.value
      }))
    }, null, 40, _hoisted_17), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.evolutionGraphsShowForEachInPeriod)
    }, null, 8, _hoisted_18)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      id: "report_evolution_period_for_prev",
      name: "report_evolution_period_for",
      type: "radio",
      value: "prev",
      checked: _ctx.report.evolutionPeriodFor === 'prev',
      onChange: _cache[9] || (_cache[9] = $event => _ctx.$emit('change', {
        prop: 'evolutionPeriodFor',
        value: $event.target.value
      }))
    }, null, 40, _hoisted_21), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_EvolutionGraphsShowForPreviousN', _ctx.frequencyPeriodPlural)) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "number",
      name: "report_evolution_period_n",
      value: _ctx.report.evolutionPeriodN,
      onKeydown: _cache[10] || (_cache[10] = $event => _ctx.onEvolutionPeriodN($event)),
      onChange: _cache[11] || (_cache[11] = $event => _ctx.onEvolutionPeriodN($event))
    }, null, 40, _hoisted_22)])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], [1, '1', 2, '2', 3, '3'].indexOf(_ctx.report.displayFormat) !== -1]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email' && _ctx.report.formatemail !== 'csv' && _ctx.report.formatemail !== 'tsv']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportsIncluded')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportsByCategoryByReportTypeInColumns, (reportColumns, reportType) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        name: "reportsList",
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`row ${reportType}`),
        key: reportType
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportColumns, (reportsByCategory, index) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          class: "col s12 m6",
          key: index
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportsByCategory, (reports, category) => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
            key: category
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_26, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reports, report => {
            var _ctx$selectedReports$;
            return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
              key: report.uniqueId
            }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
              name: `${reportType}Reports`,
              type: _ctx.allowMultipleReportsByReportType[reportType] ? 'checkbox' : 'radio',
              id: `${reportType}${report.uniqueId}`,
              checked: (_ctx$selectedReports$ = _ctx.selectedReports[reportType]) === null || _ctx$selectedReports$ === void 0 ? void 0 : _ctx$selectedReports$[report.uniqueId],
              onChange: $event => _ctx.$emit('toggleSelectedReport', {
                reportType,
                uniqueId: report.uniqueId
              })
            }, null, 40, _hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(report.name)), 1), report.uniqueId === 'MultiSites_getAll' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportIncludeNWebsites', _ctx.countWebsites)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
          }), 128))]), _hoisted_29]);
        }), 128))]);
      }), 128))], 2)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === reportType]]);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      value: _ctx.saveButtonTitle,
      onConfirm: _cache[12] || (_cache[12] = $event => _ctx.$emit('submit'))
    }, null, 8, ["value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "entityCancel",
      innerHTML: _ctx.$sanitize(_ctx.entityCancelText)
    }, null, 8, _hoisted_30)], 32)), [[_directive_form]])]),
    _: 3
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=1cbdcec1

// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/utilities.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function adjustHourToTimezone(hour, difference) {
  return `${(24 + parseFloat(hour) + difference) % 24}`;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=script&lang=ts




const {
  $: AddReportvue_type_script_lang_ts_$
} = window;
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
    },
    reportTypes: {
      type: Object,
      required: true
    },
    segmentEditorActivated: Boolean,
    savedSegmentsById: Object,
    periods: {
      type: Object,
      required: true
    }
  },
  emits: ['submit', 'change', 'toggleSelectedReport'],
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  created() {
    this.onEvolutionPeriodN = Object(external_CoreHome_["debounce"])(this.onEvolutionPeriodN, 50);
  },
  methods: {
    onEvolutionPeriodN(event) {
      this.$emit('change', {
        prop: 'evolutionPeriodN',
        value: event.target.value
      });
    },
    decode(s) {
      // report names can be encoded (mainly goals)
      return external_CoreHome_["Matomo"].helper.htmlDecode(s);
    }
  },
  setup(props, ctx) {
    const reportParameters = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => props.report, newValue => {
      const reportParametersElement = reportParameters.value;
      reportParametersElement.querySelectorAll('[vue-entry]').forEach(node => {
        // eslint-disable-next-line no-underscore-dangle
        AddReportvue_type_script_lang_ts_$(node).data('vueAppInstance').report_ = newValue;
      });
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      const reportParametersElement = reportParameters.value;
      external_CoreHome_["Matomo"].helper.compileVueEntryComponents(reportParametersElement, {
        report: props.report,
        onChange(prop, value) {
          ctx.emit('change', {
            prop,
            value
          });
        }
      });
    });
    return {
      reportParameters
    };
  },
  beforeUnmount() {
    const reportParameters = this.$refs.reportParameters;
    external_CoreHome_["Matomo"].helper.destroyVueComponent(reportParameters);
  },
  computed: {
    reportsByCategoryByReportTypeInColumns() {
      const reportsByCategoryByReportType = this.reportsByCategoryByReportType;
      const inColumns = Object.entries(reportsByCategoryByReportType).map(([key, reportsByCategory]) => {
        const newColumnAfter = Math.floor((Object.keys(reportsByCategory).length + 1) / 2);
        const column1 = {};
        const column2 = {};
        let currentColumn = column1;
        Object.entries(reportsByCategory).forEach(([category, reports]) => {
          currentColumn[category] = reports;
          if (Object.keys(currentColumn).length >= newColumnAfter) {
            currentColumn = column2;
          }
        });
        return [key, [column1, column2]];
      });
      return Object.fromEntries(inColumns);
    },
    entityCancelText() {
      return Object(external_CoreHome_["translate"])('General_OrCancel', '<a class="entityCancelLink">', '</a>');
    },
    frequencyPeriodSingle() {
      if (!this.report || !this.report.period) {
        return '';
      }
      const {
        ReportPlugin
      } = window;
      let translation = ReportPlugin.periodTranslations[this.report.period];
      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }
      return translation.single;
    },
    frequencyPeriodPlural() {
      if (!this.report || !this.report.period) {
        return '';
      }
      const {
        ReportPlugin
      } = window;
      let translation = ReportPlugin.periodTranslations[this.report.period];
      if (!translation) {
        translation = ReportPlugin.periodTranslations.day;
      }
      return translation.plural;
    },
    evolutionGraphsShowForEachInPeriod() {
      return Object(external_CoreHome_["translate"])('ScheduledReports_EvolutionGraphsShowForEachInPeriod', '<strong>', '</strong>', this.frequencyPeriodSingle);
    },
    reportSegmentInlineHelp() {
      return Object(external_CoreHome_["translate"])('ScheduledReports_Segment_Help', '<a href="./" rel="noreferrer noopener" target="_blank">', '</a>', Object(external_CoreHome_["translate"])('SegmentEditor_DefaultAllVisits'), Object(external_CoreHome_["translate"])('SegmentEditor_AddNewSegment'));
    },
    timezoneOffset() {
      return external_CoreHome_["Matomo"].timezoneOffset;
    },
    timeZoneDifferenceInHours() {
      return external_CoreHome_["Matomo"].timezoneOffset / 3600;
    },
    reportHours() {
      const hours = [];
      for (let i = 0; i < 24; i += 1) {
        if (this.timeZoneDifferenceInHours * 2 % 2 !== 0) {
          hours.push({
            key: `${i}.5`,
            value: `${i}:30`
          });
        } else {
          hours.push({
            key: `${i}`,
            value: `${i}`
          });
        }
      }
      return hours;
    },
    reportHourUtc() {
      const reportHour = adjustHourToTimezone(this.report.hour, -this.timeZoneDifferenceInHours);
      return Object(external_CoreHome_["translate"])('ScheduledReports_ReportHourWithUTC', [reportHour]);
    },
    saveButtonTitle() {
      const {
        ReportPlugin
      } = window;
      const isEditing = this.report.idreport > 0;
      return isEditing ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
    },
    contentTitle() {
      const {
        ReportPlugin
      } = window;
      const isEditing = this.report.idreport > 0;
      return isEditing ? ReportPlugin.updateReportString : Object(external_CoreHome_["translate"])('ScheduledReports_CreateAndScheduleReport');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue



AddReportvue_type_script_lang_ts.render = AddReportvue_type_template_id_1cbdcec1_render

/* harmony default export */ var AddReport = (AddReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=5e0e68b0

const ListReportsvue_type_template_id_5e0e68b0_hoisted_1 = {
  class: "first"
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_2 = {
  key: 0
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_3 = {
  colspan: "7"
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_6 = ["href"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_9 = {
  key: 1
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_10 = {
  colspan: "7"
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_14 = {
  class: "first"
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_15 = {
  key: 0,
  class: "entityInlineHelp",
  style: {
    "font-size": "9pt"
  }
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_16 = {
  key: 0
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_17 = {
  key: 1
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_18 = {
  key: 0
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_19 = {
  key: 0
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_21 = ["onClick"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_22 = ["src"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_23 = ["id", "action"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_24 = ["value"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);
const ListReportsvue_type_template_id_5e0e68b0_hoisted_26 = ["onClick", "id"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_27 = ["src"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_28 = {
  style: {
    "text-align": "center",
    "padding-top": "2px"
  }
};
const ListReportsvue_type_template_id_5e0e68b0_hoisted_29 = ["onClick", "title"];
const ListReportsvue_type_template_id_5e0e68b0_hoisted_30 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);
const _hoisted_31 = [ListReportsvue_type_template_id_5e0e68b0_hoisted_30];
const _hoisted_32 = {
  style: {
    "text-align": "center",
    "padding-top": "2px"
  }
};
const _hoisted_33 = ["onClick", "title"];
const _hoisted_34 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);
const _hoisted_35 = [_hoisted_34];
const _hoisted_36 = {
  class: "tableActionBar"
};
const _hoisted_37 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
function ListReportsvue_type_template_id_5e0e68b0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    id: "entityEditContainer",
    class: "entityTableContainer",
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/email-reports/'),
    feature: 'true',
    "content-title": _ctx.contentTitle
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => {
      var _ctx$reports;
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", ListReportsvue_type_template_id_5e0e68b0_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_EmailSchedule')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportFormat')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Edit')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.userLogin === 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_5e0e68b0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5e0e68b0_hoisted_3, [ListReportsvue_type_template_id_5e0e68b0_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MustBeLoggedIn')) + " ", 1), ListReportsvue_type_template_id_5e0e68b0_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("› "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: `index.php?module=${_ctx.loginModule}`
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Login_LogIn')), 9, ListReportsvue_type_template_id_5e0e68b0_hoisted_6), ListReportsvue_type_template_id_5e0e68b0_hoisted_7, ListReportsvue_type_template_id_5e0e68b0_hoisted_8])])) : !((_ctx$reports = _ctx.reports) !== null && _ctx$reports !== void 0 && _ctx$reports.length) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_5e0e68b0_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5e0e68b0_hoisted_10, [ListReportsvue_type_template_id_5e0e68b0_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ThereIsNoReportToManage', _ctx.siteName)) + ". ", 1), ListReportsvue_type_template_id_5e0e68b0_hoisted_12, ListReportsvue_type_template_id_5e0e68b0_hoisted_13])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.decodedReports, report => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: report.idreport
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5e0e68b0_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.description) + " ", 1), _ctx.segmentEditorActivated && report.idsegment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ListReportsvue_type_template_id_5e0e68b0_hoisted_15, [_ctx.savedSegmentsById[report.idsegment] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5e0e68b0_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.savedSegmentsById[report.idsegment]), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5e0e68b0_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SegmentDeleted')), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.periods[report.period]) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.format ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5e0e68b0_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.format.toUpperCase()), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.recipients.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5e0e68b0_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_NoRecipients')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(report.recipients, (recipient, index) => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
            key: index
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(recipient) + " ", 1), ListReportsvue_type_template_id_5e0e68b0_hoisted_20]);
        }), 128)), report.recipients.length !== 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 1,
          href: "#",
          name: "linkSendNow",
          class: "link_but withIcon",
          style: {
            "margin-top": "3px"
          },
          onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('sendnow', report.idreport), ["prevent"])
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
          border: "0",
          src: _ctx.reportTypes[report.type]
        }, null, 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_22), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportNow')), 1)], 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_21)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
          method: "POST",
          target: "_blank",
          id: `downloadReportForm_${report.idreport}`,
          action: _ctx.linkTo({
            module: 'API',
            segment: null,
            method: 'ScheduledReports.generateReport',
            idReport: report.idreport,
            outputType: _ctx.downloadOutputType,
            language: _ctx.language,
            format: ['html', 'csv', 'tsv'].indexOf(report.format) !== -1 ? report.format : 'original'
          })
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          type: "hidden",
          name: "token_auth",
          value: _ctx.token_auth
        }, null, 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_24), ListReportsvue_type_template_id_5e0e68b0_hoisted_25], 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
          href: "",
          rel: "noreferrer noopener",
          name: "linkDownloadReport",
          class: "link_but withIcon",
          onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.displayReport(report.idreport), ["prevent"]),
          id: report.idreport
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
          border: "0",
          width: 16,
          height: 16,
          src: _ctx.reportFormatsByReportType[report.type][report.format]
        }, null, 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1)], 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_26)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5e0e68b0_hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: $event => _ctx.$emit('edit', report.idreport),
          title: _ctx.translate('General_Edit')
        }, _hoisted_31, 8, ListReportsvue_type_template_id_5e0e68b0_hoisted_29)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: $event => _ctx.$emit('delete', report.idreport),
          title: _ctx.translate('General_Delete')
        }, _hoisted_35, 8, _hoisted_33)])]);
      }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_36, [_ctx.userLogin !== 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 0,
        id: "add-report",
        onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('create'))
      }, [_hoisted_37, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_CreateAndScheduleReport')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])];
    }),
    _: 1
  }, 8, ["help-url", "content-title"]);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=5e0e68b0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }


/* harmony default export */ var ListReportsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    contentTitle: {
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
      type: Number,
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
    reportTypes: {
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
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    },
    displayReport(reportId) {
      $(`#downloadReportForm_${reportId}`).submit();
    }
  },
  computed: {
    token_auth() {
      return external_CoreHome_["Matomo"].token_auth;
    },
    decodedReports() {
      return this.reports.map(r => _extends(_extends({}, r), {}, {
        description: external_CoreHome_["Matomo"].helper.htmlDecode(r.description)
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue



ListReportsvue_type_script_lang_ts.render = ListReportsvue_type_template_id_5e0e68b0_render

/* harmony default export */ var ListReports = (ListReportsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=script&lang=ts






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
window.resetReportParametersFunctions = window.resetReportParametersFunctions || {};
window.updateReportParametersFunctions = window.updateReportParametersFunctions || {};
window.getReportParametersFunctions = window.getReportParametersFunctions || {};
const {
  $: ManageScheduledReportvue_type_script_lang_ts_$
} = window;
const timeZoneDifferenceInHours = external_CoreHome_["Matomo"].timezoneOffset / 3600;
/* harmony default export */ var ManageScheduledReportvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    contentTitle: {
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
      type: Number,
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
    },
    reportTypes: {
      type: Object,
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
  mounted() {
    ManageScheduledReportvue_type_script_lang_ts_$(this.$refs.root).on('click', 'a.entityCancelLink', () => {
      this.showListOfReports();
    });
    external_CoreHome_["Matomo"].postEvent('ScheduledReports.ManageScheduledReport.mounted', {
      element: this.$refs.root
    });
  },
  unmounted() {
    external_CoreHome_["Matomo"].postEvent('ScheduledReports.ManageScheduledReport.unmounted', {
      element: this.$refs.root
    });
  },
  data() {
    return {
      showReportsList: true,
      report: {},
      selectedReports: {}
    };
  },
  methods: {
    sendReportNow(idReport) {
      scrollToTop();
      external_CoreHome_["AjaxHelper"].post({
        method: 'ScheduledReports.sendReport'
      }, {
        idReport,
        force: true
      }).then(() => {
        this.fadeInOutSuccessMessage(this.$refs.reportSentSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportSent'), false);
      });
    },
    formSetEditReport(idReport) {
      const {
        ReportPlugin
      } = window;
      let report = {
        idreport: idReport,
        type: ReportPlugin.defaultReportType,
        format: ReportPlugin.defaultReportFormat,
        description: '',
        period: ReportPlugin.defaultPeriod,
        hour: ReportPlugin.defaultHour,
        reports: [],
        idsegment: '',
        evolutionPeriodFor: 'prev',
        evolutionPeriodN: ReportPlugin.defaultEvolutionPeriodN,
        periodParam: ReportPlugin.defaultPeriod
      };
      if (idReport > 0) {
        report = ReportPlugin.reportList[idReport];
        updateParameters(report.type, report);
      } else {
        resetParameters(report.type, report);
      }
      report.hour = adjustHourToTimezone(report.hour, timeZoneDifferenceInHours);
      this.selectedReports = {};
      Object.values(report.reports).forEach(reportId => {
        this.selectedReports[report.type] = this.selectedReports[report.type] || {};
        this.selectedReports[report.type][reportId] = true;
      });
      report[`format${report.type}`] = report.format;
      if (!report.idsegment) {
        report.idsegment = '';
      }
      this.report = report;
      this.report.description = external_CoreHome_["Matomo"].helper.htmlDecode(report.description);
    },
    fadeInOutSuccessMessage(selector, message, reload = true) {
      external_CoreHome_["NotificationsStore"].show({
        message,
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
      if (reload) {
        external_CoreHome_["Matomo"].helper.refreshAfter(2);
      }
    },
    changedReportType() {
      resetParameters(this.report.type, this.report);
    },
    deleteReport(idReport) {
      external_CoreHome_["Matomo"].helper.modalConfirm('#confirm', {
        yes: () => {
          external_CoreHome_["AjaxHelper"].post({
            method: 'ScheduledReports.deleteReport'
          }, {
            idReport
          }, {
            redirectOnSuccess: true
          });
        }
      });
    },
    showListOfReports(shouldScrollToTop) {
      this.showReportsList = true;
      external_CoreHome_["Matomo"].helper.hideAjaxError();
      if (typeof shouldScrollToTop === 'undefined' || shouldScrollToTop) {
        scrollToTop();
      }
    },
    createReport() {
      this.showReportsList = false;
      // in nextTick so global report function records get manipulated before individual
      // entries are used
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
        this.formSetEditReport(0);
      });
    },
    editReport(reportId) {
      this.showReportsList = false;
      // in nextTick so global report function records get manipulated before individual
      // entries are used
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(() => {
        this.formSetEditReport(reportId);
      });
    },
    submitReport() {
      const apiParameters = {
        idReport: this.report.idreport,
        description: this.report.description,
        idSegment: this.report.idsegment,
        reportType: this.report.type,
        reportFormat: this.report[`format${this.report.type}`],
        periodParam: this.report.periodParam,
        evolutionPeriodFor: this.report.evolutionPeriodFor
      };
      if (apiParameters.evolutionPeriodFor !== 'each') {
        apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
      }
      const {
        period
      } = this.report;
      const hour = adjustHourToTimezone(this.report.hour, -timeZoneDifferenceInHours);
      const selectedReports = this.selectedReports[apiParameters.reportType] || {};
      const reports = Object.keys(selectedReports).filter(name => this.selectedReports[apiParameters.reportType][name]);
      if (reports.length > 0) {
        apiParameters.reports = reports;
      }
      const reportParams = window.getReportParametersFunctions[this.report.type](this.report);
      apiParameters.parameters = reportParams;
      const isCreate = this.report.idreport > 0;
      external_CoreHome_["AjaxHelper"].post({
        method: isCreate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
        period,
        hour
      }, apiParameters).then(() => {
        this.fadeInOutSuccessMessage(this.$refs.reportUpdatedSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportUpdated'));
      });
      return false;
    },
    onChangeProperty(propName, value) {
      this.report[propName] = value;
      if (propName === 'type') {
        this.changedReportType();
      }
    },
    toggleSelectedReport(reportType, uniqueId) {
      this.selectedReports[reportType] = this.selectedReports[reportType] || {};
      this.selectedReports[reportType][uniqueId] = !this.selectedReports[reportType][uniqueId];
    }
  },
  computed: {
    showReportForm() {
      return !this.showReportsList;
    },
    decodedSiteName() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(this.siteName);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue



ManageScheduledReportvue_type_script_lang_ts.render = ManageScheduledReportvue_type_template_id_9949de62_render

/* harmony default export */ var ManageScheduledReport = (ManageScheduledReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/index.ts
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
//# sourceMappingURL=ScheduledReports.umd.js.map