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
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=40badd5d

var _hoisted_1 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return _ctx.report ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    name: "report_email_me",
    introduction: _ctx.translate('ScheduledReports_SendReportTo'),
    "model-value": _ctx.report.emailMe,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('change', 'emailMe', $event);
    }),
    title: "".concat(_ctx.translate('ScheduledReports_SentToMe'), " (").concat(_ctx.currentUserEmail, ")")
  }, null, 8, ["introduction", "model-value", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email']])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "textarea",
    "var-type": "array",
    "model-value": _ctx.report.additionalEmails,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('change', 'additionalEmails', $event);
    }),
    title: _ctx.translate('ScheduledReports_AlsoSendReportToTheseEmails')
  }, null, 8, ["model-value", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email']])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=40badd5d

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts


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
  setup: function setup(props) {
    var _window = window,
        resetReportParametersFunctions = _window.resetReportParametersFunctions,
        updateReportParametersFunctions = _window.updateReportParametersFunctions,
        getReportParametersFunctions = _window.getReportParametersFunctions;

    if (!resetReportParametersFunctions[props.reportType]) {
      resetReportParametersFunctions[props.reportType] = function (theReport) {
        theReport.displayFormat = props.defaultDisplayFormat;
        theReport.emailMe = props.defaultEmailMe;
        theReport.evolutionGraph = props.defaultEvolutionGraph;
        theReport.additionalEmails = [];
      };
    }

    if (!updateReportParametersFunctions[props.reportType]) {
      updateReportParametersFunctions[props.reportType] = function (theReport) {
        if (!(theReport !== null && theReport !== void 0 && theReport.parameters)) {
          return;
        }

        ['displayFormat', 'emailMe', 'evolutionGraph', 'additionalEmails'].forEach(function (field) {
          if (field in theReport.parameters) {
            theReport[field] = theReport.parameters[field];
          }
        });
      };
    }

    if (!getReportParametersFunctions[props.reportType]) {
      getReportParametersFunctions[props.reportType] = function (theReport) {
        return {
          displayFormat: theReport.displayFormat,
          emailMe: theReport.emailMe,
          evolutionGraph: theReport.evolutionGraph,
          additionalEmails: theReport.additionalEmails || []
        };
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ReportParameters/ReportParameters.vue



ReportParametersvue_type_script_lang_ts.render = render

/* harmony default export */ var ReportParameters = (ReportParametersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=243c7804

var ManageScheduledReportvue_type_template_id_243c7804_hoisted_1 = {
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

function ManageScheduledReportvue_type_template_id_243c7804_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ListReports = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ListReports");

  var _component_AddReport = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AddReport");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageScheduledReportvue_type_template_id_243c7804_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
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
    onToggleSelectedReport: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.toggleSelectedReport($event.reportType, $event.uniqueId);
    }),
    onChange: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.onChangeProperty($event.prop, $event.value);
    }),
    onSubmit: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.submitReport();
    })
  }, {
    "report-parameters": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "report-parameters")];
    }),
    _: 3
  }, 8, ["report", "periods", "param-periods", "report-type-options", "report-formats-by-report-type-options", "display-formats", "reports-by-category-by-report-type", "allow-multiple-reports-by-report-type", "count-websites", "site-name", "selected-reports", "report-types", "segment-editor-activated", "saved-segments-by-id"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_9])], 512);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=template&id=243c7804

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=3ae95bea


var AddReportvue_type_template_id_3ae95bea_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "clear"
}, null, -1);

var AddReportvue_type_template_id_3ae95bea_hoisted_2 = {
  key: 0
};
var AddReportvue_type_template_id_3ae95bea_hoisted_3 = ["innerHTML"];
var AddReportvue_type_template_id_3ae95bea_hoisted_4 = {
  id: "emailScheduleInlineHelp",
  class: "inline-help-node"
};

var AddReportvue_type_template_id_3ae95bea_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_3ae95bea_hoisted_6 = {
  id: "emailReportPeriodInlineHelp",
  class: "inline-help-node"
};

var AddReportvue_type_template_id_3ae95bea_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_3ae95bea_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var AddReportvue_type_template_id_3ae95bea_hoisted_9 = {
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
function AddReportvue_type_template_id_3ae95bea_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: "entityAddContainer",
    "content-title": _ctx.translate('ScheduledReports_CreateAndScheduleReport')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [AddReportvue_type_template_id_3ae95bea_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
        id: "addEditReport",
        onSubmit: _cache[13] || (_cache[13] = function ($event) {
          return _ctx.$emit('submit');
        })
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
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'description',
            value: $event
          });
        }),
        "inline-help": _ctx.translate('ScheduledReports_DescriptionOnFirstPage')
      }, null, 8, ["title", "model-value", "inline-help"])]), _ctx.segmentEditorActivated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_3ae95bea_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
            innerHTML: _ctx.$sanitize(_ctx.reportSegmentInlineHelp)
          }, null, 8, AddReportvue_type_template_id_3ae95bea_hoisted_3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
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
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_3ae95bea_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_WeeklyScheduleHelp')) + " ", 1), AddReportvue_type_template_id_3ae95bea_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MonthlyScheduleHelp')), 1)])];
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
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddReportvue_type_template_id_3ae95bea_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp')) + " ", 1), AddReportvue_type_template_id_3ae95bea_hoisted_7, AddReportvue_type_template_id_3ae95bea_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportPeriodHelp2')), 1)])];
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
          return [_ctx.timezoneOffset !== 0 && _ctx.timezoneOffset !== '0' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddReportvue_type_template_id_3ae95bea_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
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
      }, null, 8, ["disabled", "model-value", "title", "options"])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportFormatsByReportTypeOptions, function (reportFormats, reportType) {
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
      }, null, 8, ["title", "model-value"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], [2, '2', 3, '3'].indexOf(_ctx.report.displayFormat) !== -1]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        id: "report_evolution_period_for_each",
        name: "report_evolution_period_for",
        type: "radio",
        value: "each",
        checked: _ctx.report.evolutionPeriodFor === 'each',
        onChange: _cache[8] || (_cache[8] = function ($event) {
          return _ctx.$emit('change', {
            prop: 'evolutionPeriodFor',
            value: $event.target.value
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
            value: $event.target.value
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
      }, null, 40, _hoisted_22)])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], [1, '1', 2, '2', 3, '3'].indexOf(_ctx.report.displayFormat) !== -1]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.report.type === 'email' && _ctx.report.formatemail !== 'csv' && _ctx.report.formatemail !== 'tsv']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportsIncluded')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.reportsByCategoryByReportTypeInColumns, function (reportColumns, reportType) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          name: "reportsList",
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])("row ".concat(reportType)),
          key: reportType
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportColumns, function (reportsByCategory, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
            class: "col s12 m6",
            key: index
          }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(reportsByCategory, function (reports, category) {
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
              }, null, 40, _hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.decode(report.name)), 1), report.uniqueId === 'MultiSites_getAll' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportIncludeNWebsites', _ctx.countWebsites)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
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
        innerHTML: _ctx.$sanitize(_ctx.entityCancelText)
      }, null, 8, _hoisted_30)], 544), [[_directive_form]])];
    }),
    _: 3
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=template&id=3ae95bea

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
  created: function created() {
    this.onEvolutionPeriodN = Object(external_CoreHome_["debounce"])(this.onEvolutionPeriodN, 50);
  },
  methods: {
    onEvolutionPeriodN: function onEvolutionPeriodN(event) {
      this.$emit('change', {
        prop: 'evolutionPeriodN',
        value: event.target.value
      });
    },
    decode: function decode(s) {
      // report names can be encoded (mainly goals)
      return external_CoreHome_["Matomo"].helper.htmlDecode(s);
    }
  },
  setup: function setup(props, ctx) {
    var reportParameters = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    var angularControllerProxy = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      report: Object.assign({}, props.report)
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return angularControllerProxy.report;
    }, function (newValue) {
      Object.keys(newValue).forEach(function (key) {
        if (newValue[key] !== props.report[key]) {
          ctx.emit('change', {
            prop: key,
            value: newValue[key]
          });
        }
      });
    }, {
      deep: true
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.report;
    }, function (newValue) {
      Object.assign(angularControllerProxy.report, newValue);
      external_CoreHome_["Matomo"].helper.getAngularDependency('$timeout')();
    }, {
      deep: true
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      var reportParametersElement = reportParameters.value;
      external_CoreHome_["Matomo"].helper.compileAngularComponents(reportParametersElement, {
        params: {
          manageScheduledReport: angularControllerProxy
        }
      });
      external_CoreHome_["Matomo"].helper.compileVueEntryComponents(reportParametersElement, {
        report: angularControllerProxy.report,
        onChange: function onChange(prop, value) {
          ctx.emit('change', {
            prop: prop,
            value: value
          });
        }
      });
    });
    return {
      reportParameters: reportParameters
    };
  },
  beforeUnmount: function beforeUnmount() {
    var reportParameters = this.$refs.reportParameters;
    external_CoreHome_["Matomo"].helper.destroyVueComponent(reportParameters);
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

          if (Object.keys(currentColumn).length >= newColumnAfter) {
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

      var _window = window,
          ReportPlugin = _window.ReportPlugin;
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

      var _window2 = window,
          ReportPlugin = _window2.ReportPlugin;
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
      var _window3 = window,
          ReportPlugin = _window3.ReportPlugin;
      var isCreate = this.report.idreport > 0;
      return isCreate ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/AddReport/AddReport.vue



AddReportvue_type_script_lang_ts.render = AddReportvue_type_template_id_3ae95bea_render

/* harmony default export */ var AddReport = (AddReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=5753851b

var ListReportsvue_type_template_id_5753851b_hoisted_1 = {
  class: "first"
};
var ListReportsvue_type_template_id_5753851b_hoisted_2 = {
  key: 0
};
var ListReportsvue_type_template_id_5753851b_hoisted_3 = {
  colspan: "7"
};

var ListReportsvue_type_template_id_5753851b_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("› ");

var ListReportsvue_type_template_id_5753851b_hoisted_7 = ["href"];

var ListReportsvue_type_template_id_5753851b_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_10 = {
  key: 1
};
var ListReportsvue_type_template_id_5753851b_hoisted_11 = {
  colspan: "7"
};

var ListReportsvue_type_template_id_5753851b_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_13 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_15 = {
  class: "first"
};
var ListReportsvue_type_template_id_5753851b_hoisted_16 = {
  key: 0,
  class: "entityInlineHelp",
  style: {
    "font-size": "9pt"
  }
};
var ListReportsvue_type_template_id_5753851b_hoisted_17 = {
  key: 0
};
var ListReportsvue_type_template_id_5753851b_hoisted_18 = {
  key: 1
};
var ListReportsvue_type_template_id_5753851b_hoisted_19 = {
  key: 0
};
var ListReportsvue_type_template_id_5753851b_hoisted_20 = {
  key: 0
};

var ListReportsvue_type_template_id_5753851b_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_22 = ["onClick"];
var ListReportsvue_type_template_id_5753851b_hoisted_23 = ["src"];
var ListReportsvue_type_template_id_5753851b_hoisted_24 = ["id", "action"];
var ListReportsvue_type_template_id_5753851b_hoisted_25 = ["value"];

var ListReportsvue_type_template_id_5753851b_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "force_api_session",
  value: "1"
}, null, -1);

var ListReportsvue_type_template_id_5753851b_hoisted_27 = ["onClick", "id"];
var ListReportsvue_type_template_id_5753851b_hoisted_28 = ["src"];
var ListReportsvue_type_template_id_5753851b_hoisted_29 = {
  style: {
    "text-align": "center",
    "padding-top": "2px"
  }
};
var ListReportsvue_type_template_id_5753851b_hoisted_30 = ["onClick", "title"];

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

function ListReportsvue_type_template_id_5753851b_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    id: "entityEditContainer",
    class: "entityTableContainer",
    "help-url": "https://matomo.org/docs/email-reports/",
    feature: 'true',
    "content-title": _ctx.contentTitle
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      var _ctx$reports;

      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", ListReportsvue_type_template_id_5753851b_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_EmailSchedule')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ReportFormat')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Edit')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.userLogin === 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_5753851b_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5753851b_hoisted_3, [ListReportsvue_type_template_id_5753851b_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_MustBeLoggedIn')) + " ", 1), ListReportsvue_type_template_id_5753851b_hoisted_5, ListReportsvue_type_template_id_5753851b_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: "index.php?module=".concat(_ctx.loginModule)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Login_LogIn')), 9, ListReportsvue_type_template_id_5753851b_hoisted_7), ListReportsvue_type_template_id_5753851b_hoisted_8, ListReportsvue_type_template_id_5753851b_hoisted_9])])) : !((_ctx$reports = _ctx.reports) !== null && _ctx$reports !== void 0 && _ctx$reports.length) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", ListReportsvue_type_template_id_5753851b_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5753851b_hoisted_11, [ListReportsvue_type_template_id_5753851b_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_ThereIsNoReportToManage', _ctx.siteName)) + ". ", 1), ListReportsvue_type_template_id_5753851b_hoisted_13, ListReportsvue_type_template_id_5753851b_hoisted_14])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.decodedReports, function (report) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: report.idreport
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5753851b_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.description) + " ", 1), _ctx.segmentEditorActivated && report.idsegment ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ListReportsvue_type_template_id_5753851b_hoisted_16, [_ctx.savedSegmentsById[report.idsegment] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5753851b_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.savedSegmentsById[report.idsegment]), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5753851b_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SegmentDeleted')), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.periods[report.period]), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.format ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5753851b_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(report.format.toUpperCase()), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [report.recipients.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ListReportsvue_type_template_id_5753851b_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_NoRecipients')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(report.recipients, function (recipient, index) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
            key: index
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(recipient) + " ", 1), ListReportsvue_type_template_id_5753851b_hoisted_21]);
        }), 128)), report.recipients.length !== 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 1,
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
          src: _ctx.reportTypes[report.type]
        }, null, 8, ListReportsvue_type_template_id_5753851b_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('ScheduledReports_SendReportNow')), 1)], 8, ListReportsvue_type_template_id_5753851b_hoisted_22)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
          method: "POST",
          target: "_blank",
          id: "downloadReportForm_".concat(report.idreport),
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
        }, null, 8, ListReportsvue_type_template_id_5753851b_hoisted_25), ListReportsvue_type_template_id_5753851b_hoisted_26], 8, ListReportsvue_type_template_id_5753851b_hoisted_24), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
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
          width: 16,
          height: 16,
          src: _ctx.reportFormatsByReportType[report.type][report.format]
        }, null, 8, ListReportsvue_type_template_id_5753851b_hoisted_28), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Download')), 1)], 8, ListReportsvue_type_template_id_5753851b_hoisted_27)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ListReportsvue_type_template_id_5753851b_hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: function onClick($event) {
            return _ctx.$emit('edit', report.idreport);
          },
          title: _ctx.translate('General_Edit')
        }, _hoisted_32, 8, ListReportsvue_type_template_id_5753851b_hoisted_30)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          class: "table-action",
          onClick: function onClick($event) {
            return _ctx.$emit('delete', report.idreport);
          },
          title: _ctx.translate('General_Delete')
        }, _hoisted_36, 8, _hoisted_34)])]);
      }), 128))])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_37, [_ctx.userLogin !== 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
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
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=template&id=5753851b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts


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
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    },
    displayReport: function displayReport(reportId) {
      $("#downloadReportForm_".concat(reportId)).submit();
    }
  },
  computed: {
    token_auth: function token_auth() {
      return external_CoreHome_["Matomo"].token_auth;
    },
    decodedReports: function decodedReports() {
      return this.reports.map(function (r) {
        return Object.assign(Object.assign({}, r), {}, {
          description: external_CoreHome_["Matomo"].helper.htmlDecode(r.description)
        });
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ListReports/ListReports.vue



ListReportsvue_type_script_lang_ts.render = ListReportsvue_type_template_id_5753851b_render

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

window.resetReportParametersFunctions = window.resetReportParametersFunctions || {};
window.updateReportParametersFunctions = window.updateReportParametersFunctions || {};
window.getReportParametersFunctions = window.getReportParametersFunctions || {};
var _window = window,
    ManageScheduledReportvue_type_script_lang_ts_$ = _window.$;
var ManageScheduledReportvue_type_script_lang_ts_timeZoneDifferenceInHours = external_CoreHome_["Matomo"].timezoneOffset / 3600;
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
  mounted: function mounted() {
    var _this = this;

    ManageScheduledReportvue_type_script_lang_ts_$(this.$refs.root).on('click', 'a.entityCancelLink', function () {
      _this.showListOfReports();
    });
    external_CoreHome_["Matomo"].postEvent('ScheduledReports.ManageScheduledReport.mounted', {
      element: this.$refs.root
    });
  },
  unmounted: function unmounted() {
    external_CoreHome_["Matomo"].postEvent('ScheduledReports.ManageScheduledReport.unmounted', {
      element: this.$refs.root
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
        _this2.fadeInOutSuccessMessage(_this2.$refs.reportSentSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportSent'), false);
      });
    },
    formSetEditReport: function formSetEditReport(idReport) {
      var _this3 = this;

      var _window2 = window,
          ReportPlugin = _window2.ReportPlugin;
      var report = {
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

      report.hour = adjustHourToTimezone(report.hour, ManageScheduledReportvue_type_script_lang_ts_timeZoneDifferenceInHours);
      this.selectedReports = {};
      Object.values(report.reports).forEach(function (reportId) {
        _this3.selectedReports[report.type] = _this3.selectedReports[report.type] || {};
        _this3.selectedReports[report.type][reportId] = true;
      });
      report["format".concat(report.type)] = report.format;

      if (!report.idsegment) {
        report.idsegment = '';
      }

      this.report = report;
      this.report.description = external_CoreHome_["Matomo"].helper.htmlDecode(report.description);
    },
    fadeInOutSuccessMessage: function fadeInOutSuccessMessage(selector, message) {
      var reload = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
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

      if (reload) {
        external_CoreHome_["Matomo"].helper.refreshAfter(2);
      }
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
            redirectOnSuccess: true
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
      var _this4 = this;

      this.showReportsList = false; // in nextTick so global report function records get manipulated before individual
      // entries are used

      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        _this4.formSetEditReport(0);
      });
    },
    editReport: function editReport(reportId) {
      var _this5 = this;

      this.showReportsList = false; // in nextTick so global report function records get manipulated before individual
      // entries are used

      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        _this5.formSetEditReport(reportId);
      });
    },
    submitReport: function submitReport() {
      var _this6 = this;

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
      var selectedReports = this.selectedReports[apiParameters.reportType] || {};
      var reports = Object.keys(selectedReports).filter(function (name) {
        return _this6.selectedReports[apiParameters.reportType][name];
      });

      if (reports.length > 0) {
        apiParameters.reports = reports;
      }

      var reportParams = window.getReportParametersFunctions[this.report.type](this.report);
      apiParameters.parameters = reportParams;
      var isCreate = this.report.idreport > 0;
      external_CoreHome_["AjaxHelper"].post({
        method: isCreate ? 'ScheduledReports.updateReport' : 'ScheduledReports.addReport',
        period: period,
        hour: hour
      }, apiParameters).then(function () {
        _this6.fadeInOutSuccessMessage(_this6.$refs.reportUpdatedSuccess, Object(external_CoreHome_["translate"])('ScheduledReports_ReportUpdated'));
      });
      return false;
    },
    onChangeProperty: function onChangeProperty(propName, value) {
      this.report[propName] = value;

      if (propName === 'type') {
        this.changedReportType();
      }
    },
    toggleSelectedReport: function toggleSelectedReport(reportType, uniqueId) {
      this.selectedReports[reportType] = this.selectedReports[reportType] || {};
      this.selectedReports[reportType][uniqueId] = !this.selectedReports[reportType][uniqueId];
    }
  },
  computed: {
    showReportForm: function showReportForm() {
      return !this.showReportsList;
    },
    decodedSiteName: function decodedSiteName() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(this.siteName);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ScheduledReports/vue/src/ManageScheduledReport/ManageScheduledReport.vue



ManageScheduledReportvue_type_script_lang_ts.render = ManageScheduledReportvue_type_template_id_243c7804_render

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