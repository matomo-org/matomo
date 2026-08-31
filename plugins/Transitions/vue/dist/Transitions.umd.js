(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Transitions"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Transitions"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/Transitions/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "TransitionExporter", function() { return /* reexport */ TransitionExporter; });
__webpack_require__.d(__webpack_exports__, "TransitionSwitcher", function() { return /* reexport */ TransitionSwitcher; });
__webpack_require__.d(__webpack_exports__, "TransitionsPage", function() { return /* reexport */ TransitionsPage; });
__webpack_require__.d(__webpack_exports__, "TransitionExporterLink", function() { return /* reexport */ TransitionExporterLink; });
__webpack_require__.d(__webpack_exports__, "TransitionsReport", function() { return /* reexport */ TransitionsReport; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.vue?vue&type=template&id=2826c363

const _hoisted_1 = {
  class: "transition-export-popover row"
};
const _hoisted_2 = {
  class: "col l6"
};
const _hoisted_3 = {
  class: "input-field"
};
const _hoisted_4 = {
  class: "matomo-field"
};
const _hoisted_5 = {
  class: "col l12"
};
const _hoisted_6 = ["href"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "exportFormat",
    title: _ctx.translate('CoreHome_ExportFormat'),
    "model-value": _ctx.exportFormat,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.exportFormat = $event),
    "full-width": true,
    options: _ctx.exportFormatOptions
  }, null, 8, ["title", "model-value", "options"])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn",
    href: _ctx.exportLink,
    target: "_new",
    title: "translate('CoreHome_ExportTooltip')"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Export')), 9, _hoisted_6)])]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.vue?vue&type=template&id=2826c363

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/transitionParams.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const transitionParams_actionType = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
const transitionParams_actionName = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
const onDataChanged = params => {
  transitionParams_actionType.value = params.actionType;
  transitionParams_actionName.value = params.actionName;
};
external_CoreHome_["Matomo"].on('Transitions.dataChanged', onDataChanged);

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.vue?vue&type=script&lang=ts




/* harmony default export */ var TransitionExporterPopovervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    exportFormatOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  data() {
    return {
      exportFormat: 'JSON'
    };
  },
  computed: {
    exportLink() {
      const exportUrlParams = {
        module: 'API'
      };
      exportUrlParams.method = 'Transitions.getTransitionsForAction';
      exportUrlParams.actionType = transitionParams_actionType.value;
      exportUrlParams.actionName = transitionParams_actionName.value;
      exportUrlParams.idSite = external_CoreHome_["Matomo"].idSite;
      exportUrlParams.period = external_CoreHome_["Matomo"].period;
      exportUrlParams.date = external_CoreHome_["Matomo"].currentDateString;
      exportUrlParams.format = this.exportFormat;
      exportUrlParams.token_auth = external_CoreHome_["Matomo"].token_auth;
      exportUrlParams.force_api_session = 1;
      const currentUrl = window.location.href;
      const urlParts = currentUrl.split('/');
      urlParts.pop();
      const url = urlParts.join('/');
      return `${url}/index.php?${external_CoreHome_["MatomoUrl"].stringify(exportUrlParams)}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterPopover.vue



TransitionExporterPopovervue_type_script_lang_ts.render = render

/* harmony default export */ var TransitionExporterPopover = (TransitionExporterPopovervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const {
  Piwik_Popover
} = window;
/* harmony default export */ var TransitionExporter = ({
  mounted(element) {
    element.addEventListener('click', e => {
      e.preventDefault();
      const props = {
        exportFormat: 'JSON',
        exportFormatOptions: [{
          key: 'JSON',
          value: 'JSON'
        }, {
          key: 'XML',
          value: 'XML'
        }]
      };
      const app = Object(external_CoreHome_["createVueApp"])({
        template: '<popover v-bind="bind"/>',
        data() {
          return {
            bind: props
          };
        }
      });
      app.component('popover', TransitionExporterPopover);
      const mountPoint = document.createElement('div');
      app.mount(mountPoint);
      Piwik_Popover.showLoading('');
      Piwik_Popover.setTitle(`${external_CoreHome_["Matomo"].helper.htmlEntities(transitionParams_actionName.value)} ${Object(external_CoreHome_["translate"])('Transitions_Transitions')}`);
      Piwik_Popover.setContent(mountPoint);
      Piwik_Popover.onClose(() => {
        app.unmount();
      });
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=template&id=7d7f6af2

const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_1 = {
  class: "row"
};
const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_2 = {
  class: "col s12 m3"
};
const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_3 = {
  name: "actionType"
};
const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_4 = {
  class: "col s12 m9"
};
const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_5 = {
  name: "actionName"
};
const TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_6 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  },
  id: "transitions_inline_loading"
};
const _hoisted_7 = {
  class: "popoverContainer"
};
const _hoisted_8 = {
  id: "Transitions_Error_Container"
};
const _hoisted_9 = {
  class: "dataTableWrapper"
};
const _hoisted_10 = {
  class: "dataTableFeatures"
};
const _hoisted_11 = {
  class: "dataTableFooterNavigation"
};
const _hoisted_12 = {
  class: "dataTableControls"
};
const _hoisted_13 = {
  class: "row"
};
const _hoisted_14 = {
  class: "dataTableAction"
};
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export"
}, null, -1);
const _hoisted_16 = [_hoisted_15];
const _hoisted_17 = {
  class: "alert alert-info"
};
const _hoisted_18 = ["innerHTML"];
function TransitionSwitchervue_type_template_id_7d7f6af2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  const _component_TransitionsReport = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsReport");
  const _directive_transition_exporter = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("transition-exporter");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      widgetBody: _ctx.isWidget
    }),
    id: "transitions_report"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "actionType",
    modelValue: _ctx.actionType,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.actionType = $event),
    title: _ctx.translate('Actions_ActionType'),
    "full-width": true,
    options: _ctx.actionTypeOptions
  }, null, 8, ["modelValue", "title", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "actionName",
    modelValue: _ctx.actionName,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.actionName = $event),
    title: _ctx.translate('Transitions_TopX', 100),
    "full-width": true,
    disabled: !_ctx.isEnabled,
    options: _ctx.actionNameOptions
  }, null, 8, ["modelValue", "title", "disabled", "options"])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading
  }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_7d7f6af2_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [_ctx.hasAction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TransitionsReport, {
    key: 0,
    "action-type": _ctx.transitionsActionType,
    "action-name": _ctx.selectedActionName,
    context: "embedded"
  }, null, 8, ["action-type", "action-name"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.isEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", _hoisted_14, _hoisted_16)), [[_directive_transition_exporter]])])])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Transitions_AvailableInOtherReports')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_PageUrls')) + ", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPageTitles')) + ", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPagesEntry')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_And')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPagesExit')) + ". ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.availableInOtherReports2)
  }, null, 8, _hoisted_18)])], 2);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=template&id=7d7f6af2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.vue?vue&type=template&id=a527546c

const TransitionsReportvue_type_template_id_a527546c_hoisted_1 = {
  key: 0,
  class: "transitionsReport__error"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_2 = ["innerHTML"];
const TransitionsReportvue_type_template_id_a527546c_hoisted_3 = {
  key: 0,
  class: "transitionsReport__errorMessage"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_4 = {
  key: 1,
  class: "transitionsReport__grid"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_5 = {
  class: "transitionsReport__column",
  ref: "incomingColumn"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_6 = {
  class: "transitionsReport__ribbons"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_7 = {
  class: "transitionsReport__center"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_8 = {
  class: "transitionsReport__ribbons"
};
const TransitionsReportvue_type_template_id_a527546c_hoisted_9 = {
  class: "transitionsReport__column",
  ref: "outgoingColumn"
};
function TransitionsReportvue_type_template_id_a527546c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_TransitionsColumn = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsColumn");
  const _component_TransitionsRibbons = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsRibbons");
  const _component_TransitionsCenterCard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsCenterCard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsReport", {
      'transitionsReport--narrow': _ctx.isInDashboardWidget
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsReport__loader", {
      'transitionsReport__loader--prominent': _ctx.context === 'popover'
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoading,
    "loading-message": _ctx.loadingMessage
  }, null, 8, ["loading", "loading-message"])], 2), _ctx.error && !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "transitionsReport__errorTitle",
    innerHTML: _ctx.$sanitize(_ctx.error.title)
  }, null, 8, TransitionsReportvue_type_template_id_a527546c_hoisted_2), _ctx.error.message ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", TransitionsReportvue_type_template_id_a527546c_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error.message), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.context === 'popover' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "transitionsReport__errorBack",
    href: "#",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.goBack && _ctx.goBack(...args), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error.backLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.report && !_ctx.isLoading && !_ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsColumn, {
    sections: _ctx.incomingSections,
    "highlighted-keys": _ctx.highlightedKeys,
    onOpen: _cache[1] || (_cache[1] = $event => _ctx.onOpen('incoming', $event)),
    onHighlight: _ctx.onRowHighlight,
    onUnhighlight: _cache[2] || (_cache[2] = $event => _ctx.highlightedGroup = ''),
    onNavigate: _ctx.onNavigate
  }, null, 8, ["sections", "highlighted-keys", "onHighlight", "onNavigate"])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsRibbons, {
    side: "incoming",
    rows: _ctx.incomingRibbonRows,
    column: _ctx.incomingColumnElement,
    center: _ctx.centerCardElement,
    "highlighted-keys": _ctx.highlightedKeys
  }, null, 8, ["rows", "column", "center", "highlighted-keys"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsCenterCard, {
    ref: "centerCard",
    report: _ctx.report,
    "highlighted-group": _ctx.highlightedGroup,
    onOpen: _ctx.onOpenFromCard,
    onHighlight: _cache[3] || (_cache[3] = $event => _ctx.highlightedGroup = $event),
    onUnhighlight: _cache[4] || (_cache[4] = $event => _ctx.highlightedGroup = '')
  }, null, 8, ["report", "highlighted-group", "onOpen"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsRibbons, {
    side: "outgoing",
    rows: _ctx.outgoingRibbonRows,
    column: _ctx.outgoingColumnElement,
    center: _ctx.centerCardElement,
    "highlighted-keys": _ctx.highlightedKeys
  }, null, 8, ["rows", "column", "center", "highlighted-keys"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsReportvue_type_template_id_a527546c_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsColumn, {
    sections: _ctx.outgoingSections,
    "highlighted-keys": _ctx.highlightedKeys,
    onOpen: _cache[5] || (_cache[5] = $event => _ctx.onOpen('outgoing', $event)),
    onHighlight: _ctx.onRowHighlight,
    onUnhighlight: _cache[6] || (_cache[6] = $event => _ctx.highlightedGroup = ''),
    onNavigate: _ctx.onNavigate
  }, null, 8, ["sections", "highlighted-keys", "onHighlight", "onNavigate"])], 512)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.vue?vue&type=template&id=a527546c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.vue?vue&type=template&id=d6e2b71c

const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_1 = {
  class: "transitionsCenterCard"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_2 = {
  class: "transitionsCenterCard__header"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_3 = ["href", "title"];
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "transitionsCenterCard__glyph icon-outlink"
}, null, -1);
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_5 = [TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_4];
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_6 = {
  key: 1,
  class: "transitionsCenterCard__icon"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "transitionsCenterCard__glyph icon-document"
}, null, -1);
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_8 = [TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_7];
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_9 = ["title"];
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_10 = ["title"];
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_11 = {
  class: "transitionsCenterCard__metricHeading"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_12 = {
  class: "transitionsCenterCard__metricTotal"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_13 = {
  class: "transitionsCenterCard__metricList"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_14 = {
  class: "transitionsCenterCard__metricLabel"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_15 = {
  class: "transitionsCenterCard__metricValue"
};
const TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_16 = ["title"];
function TransitionsCenterCardvue_type_template_id_d6e2b71c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_2, [_ctx.safeTitleUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "transitionsCenterCard__icon",
    href: _ctx.safeTitleUrl,
    rel: "noreferrer noopener",
    target: "_blank",
    title: _ctx.report.actionName
  }, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_5, 8, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_3)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_6, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_8)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "transitionsCenterCard__title",
    title: _ctx.titleTooltip
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.report.title), 9, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_9), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "transitionsCenterCard__pageviews",
    title: _ctx.report.pageviewsTooltip
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.report.pageviewsLabel), 9, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_10)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sides, side => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsCenterCard__metricGroup", {
        'transitionsCenterCard__metricGroup--divided': side === 'outgoing'
      }]),
      key: side
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.headingFor(side)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.totalFor(side)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_13, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricsFor(side), metric => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.isActionable(metric) ? 'a' : 'div'), {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsCenterCard__metric", _ctx.metricClasses(metric)]),
        key: metric.key,
        href: _ctx.isActionable(metric) ? '#' : null,
        title: metric.tooltip,
        onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onMetricClick(metric), ["prevent"]),
        onMouseenter: $event => _ctx.onMetricHighlight(metric),
        onMouseleave: _cache[0] || (_cache[0] = $event => _ctx.$emit('unhighlight'))
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsCenterCard__dot", _ctx.dotClass(metric)])
        }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.labelBefore), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.valueLabel), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.labelAfter), 1)])]),
        _: 2
      }, 1064, ["class", "href", "title", "onClick", "onMouseenter"]);
    }), 128))])], 2);
  }), 128)), _ctx.report.loops > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 0,
    class: "transitionsCenterCard__loops",
    title: _ctx.report.loopsTooltip
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.report.loopsLabel), 9, TransitionsCenterCardvue_type_template_id_d6e2b71c_hoisted_16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.vue?vue&type=template&id=d6e2b71c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.vue?vue&type=script&lang=ts


/* harmony default export */ var TransitionsCenterCardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    highlightedGroup: {
      type: String,
      default: ''
    }
  },
  emits: ['open', 'highlight', 'unhighlight'],
  computed: {
    sides() {
      return ['incoming', 'outgoing'];
    },
    /**
     * An action name is tracked data, so it can be any string. Only a value DOMPurify accepts as
     * an href reaches the link; anything else falls through to the plain icon.
     */
    safeTitleUrl() {
      return this.report.titleUrl ? this.$sanitizeUrl(this.report.titleUrl) : '';
    },
    /**
     * Only worth a tooltip when the title was shortened; page titles are shown in full. Undefined
     * rather than null, because that is what Vue's typing for a DOM attribute accepts.
     */
    titleTooltip() {
      return this.report.title === this.report.actionName ? undefined : this.report.actionName;
    }
  },
  methods: {
    headingFor(side) {
      return side === 'incoming' ? Object(external_CoreHome_["translate"])('Transitions_IncomingTraffic') : Object(external_CoreHome_["translate"])('Transitions_OutgoingTraffic');
    },
    totalFor(side) {
      return external_CoreHome_["NumberFormatter"].formatNumber(side === 'incoming' ? this.report.incomingTotal : this.report.outgoingTotal);
    },
    /** Every metric is listed, including the ones at zero, so the breakdown is always complete. */
    metricsFor(side) {
      return this.report.metrics.filter(metric => metric.side === side);
    },
    /** A group with no transitions has nothing to open, however expandable it is in principle. */
    isActionable(metric) {
      return metric.canExpand && metric.value > 0;
    },
    metricClasses(metric) {
      return {
        'transitionsCenterCard__metric--actionable': this.isActionable(metric),
        'transitionsCenterCard__metric--highlighted': metric.groupName === this.highlightedGroup
      };
    },
    dotClass(metric) {
      if (metric.value <= 0) {
        return 'transitionsCenterCard__dot--empty';
      }
      return metric.side === 'incoming' ? 'transitionsCenterCard__dot--incoming' : 'transitionsCenterCard__dot--outgoing';
    },
    onMetricClick(metric) {
      if (this.isActionable(metric)) {
        this.$emit('open', metric.groupName);
      }
    },
    /**
     * A metric at zero has no ribbons to emphasise, so it stays inert on hover. The value decides
     * that, not expandability: direct entries cannot be opened but do highlight.
     */
    onMetricHighlight(metric) {
      if (metric.value > 0) {
        this.$emit('highlight', metric.groupName);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsCenterCard.vue



TransitionsCenterCardvue_type_script_lang_ts.render = TransitionsCenterCardvue_type_template_id_d6e2b71c_render

/* harmony default export */ var TransitionsCenterCard = (TransitionsCenterCardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.vue?vue&type=template&id=29da2b1e

const TransitionsColumnvue_type_template_id_29da2b1e_hoisted_1 = {
  class: "transitionsColumn"
};
function TransitionsColumnvue_type_template_id_29da2b1e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_TransitionsSection = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsSection");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", TransitionsColumnvue_type_template_id_29da2b1e_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sections, section => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "transitionsColumn__sectionItem",
      key: section.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsSection, {
      section: section,
      "highlighted-keys": _ctx.highlightedKeys,
      onHighlight: _cache[0] || (_cache[0] = $event => _ctx.$emit('highlight', $event)),
      onUnhighlight: _cache[1] || (_cache[1] = $event => _ctx.$emit('unhighlight')),
      onNavigate: _cache[2] || (_cache[2] = $event => _ctx.$emit('navigate', $event)),
      onOpen: _cache[3] || (_cache[3] = $event => _ctx.$emit('open', $event))
    }, null, 8, ["section", "highlighted-keys"])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.vue?vue&type=template&id=29da2b1e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.vue?vue&type=template&id=0e73ce4f

const TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_1 = {
  class: "transitionsSection"
};
const TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_2 = {
  class: "transitionsSection__header"
};
const TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_3 = ["title"];
const TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_4 = {
  key: 0,
  class: "transitionsSection__badge"
};
const TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_5 = {
  class: "transitionsSection__rowList"
};
function TransitionsSectionvue_type_template_id_0e73ce4f_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_TransitionsRow = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionsRow");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "transitionsSection__title",
    title: _ctx.section.title
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.section.title), 9, TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_3), _ctx.section.badge ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.section.badge), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionsSectionvue_type_template_id_0e73ce4f_hoisted_5, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.section.rows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "transitionsSection__rowItem",
      key: row.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionsRow, {
      row: row,
      side: _ctx.section.side,
      highlighted: _ctx.highlightedKeys.includes(row.key),
      onHighlight: $event => _ctx.$emit('highlight', row),
      onUnhighlight: _cache[0] || (_cache[0] = $event => _ctx.$emit('unhighlight')),
      onNavigate: _cache[1] || (_cache[1] = $event => _ctx.$emit('navigate', $event)),
      onOpen: _cache[2] || (_cache[2] = $event => _ctx.$emit('open', $event))
    }, null, 8, ["row", "side", "highlighted", "onHighlight"])]);
  }), 128))])]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.vue?vue&type=template&id=0e73ce4f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.vue?vue&type=template&id=474c9fa8

const TransitionsRowvue_type_template_id_474c9fa8_hoisted_1 = {
  class: "transitionsRow__icon",
  "aria-hidden": "true"
};
const TransitionsRowvue_type_template_id_474c9fa8_hoisted_2 = {
  class: "transitionsRow__body"
};
const TransitionsRowvue_type_template_id_474c9fa8_hoisted_3 = ["title"];
const TransitionsRowvue_type_template_id_474c9fa8_hoisted_4 = {
  key: 0,
  class: "transitionsRow__count"
};
const TransitionsRowvue_type_template_id_474c9fa8_hoisted_5 = {
  class: "transitionsRow__figures"
};
const TransitionsRowvue_type_template_id_474c9fa8_hoisted_6 = {
  key: 0,
  class: "transitionsRow__total"
};
function TransitionsRowvue_type_template_id_474c9fa8_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.isActionable ? 'a' : 'div'), {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRow", _ctx.rowClasses]),
    "data-ribbon-key": _ctx.row.key,
    href: _ctx.href,
    target: _ctx.safeExternalUrl ? '_blank' : null,
    rel: _ctx.safeExternalUrl ? 'noreferrer noopener' : null,
    onClick: _ctx.onClick,
    onMouseenter: _cache[0] || (_cache[0] = $event => _ctx.$emit('highlight')),
    onMouseleave: _cache[1] || (_cache[1] = $event => _ctx.$emit('unhighlight'))
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsRowvue_type_template_id_474c9fa8_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRow__glyph", [_ctx.row.icon, _ctx.glyphClass]])
    }, null, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsRowvue_type_template_id_474c9fa8_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "transitionsRow__label",
      title: _ctx.row.fullLabel || _ctx.row.label
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.row.label), 9, TransitionsRowvue_type_template_id_474c9fa8_hoisted_3), _ctx.isAction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", TransitionsRowvue_type_template_id_474c9fa8_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.row.countLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", TransitionsRowvue_type_template_id_474c9fa8_hoisted_5, [!_ctx.isAction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", TransitionsRowvue_type_template_id_474c9fa8_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.row.countLabel), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRow__pill", {
        'transitionsRow__pill--muted': !_ctx.isAction
      }])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.isAction ? _ctx.row.percentage : `(${_ctx.row.percentage})`), 3)])]),
    _: 1
  }, 40, ["class", "data-ribbon-key", "href", "target", "rel", "onClick"]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.vue?vue&type=template&id=474c9fa8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.vue?vue&type=script&lang=ts

/* harmony default export */ var TransitionsRowvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    row: {
      type: Object,
      required: true
    },
    side: {
      type: String,
      required: true
    },
    highlighted: Boolean
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open'],
  computed: {
    isAction() {
      return this.row.kind === 'action';
    },
    /**
     * The icon carries its side's accent, so a row reads as belonging to its half of the report.
     */
    glyphClass() {
      return this.side === 'outgoing' ? 'transitionsRow__glyph--outgoing' : 'transitionsRow__glyph--incoming';
    },
    /**
     * Row labels are tracked URLs, so they can be any string. Only a value DOMPurify accepts as an
     * href reaches the link; anything else renders as a plain row.
     */
    safeExternalUrl() {
      return this.row.externalUrl ? this.$sanitizeUrl(this.row.externalUrl) : '';
    },
    isActionable() {
      return !!(this.safeExternalUrl || this.row.transitionUrl || this.row.opensGroup);
    },
    /**
     * Every actionable row is an anchor, so it sits in the tab order and Enter activates it. Only
     * an outlink has a real destination; the rest carry `#` because an anchor without an href is
     * not focusable, and onClick keeps that `#` from ever reaching the address bar.
     */
    href() {
      if (!this.isActionable) {
        return null;
      }
      return this.safeExternalUrl || '#';
    },
    rowClasses() {
      return {
        'transitionsRow--outgoing': this.side === 'outgoing',
        'transitionsRow--actionable': this.isActionable,
        'transitionsRow--highlighted': this.highlighted,
        'transitionsRow--others': this.row.isOthers,
        'transitionsRow--summary': !this.isAction
      };
    }
  },
  methods: {
    onClick(event) {
      // An outlink is a real link, so let the browser follow it.
      if (this.safeExternalUrl) {
        return;
      }
      // The others are anchors only to be focusable, so their `#` must not be followed: on a
      // reporting page the hash is the route, and following it would navigate away.
      event.preventDefault();
      if (this.row.transitionUrl) {
        this.$emit('navigate', this.row.transitionUrl);
        return;
      }
      if (this.row.opensGroup) {
        this.$emit('open', this.row.opensGroup);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRow.vue



TransitionsRowvue_type_script_lang_ts.render = TransitionsRowvue_type_template_id_474c9fa8_render

/* harmony default export */ var TransitionsRow = (TransitionsRowvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.vue?vue&type=script&lang=ts


/* harmony default export */ var TransitionsSectionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    section: {
      type: Object,
      required: true
    },
    highlightedKeys: {
      type: Array,
      default: () => []
    }
  },
  components: {
    TransitionsRow: TransitionsRow
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open']
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsSection.vue



TransitionsSectionvue_type_script_lang_ts.render = TransitionsSectionvue_type_template_id_0e73ce4f_render

/* harmony default export */ var TransitionsSection = (TransitionsSectionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.vue?vue&type=script&lang=ts


/* harmony default export */ var TransitionsColumnvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    sections: {
      type: Array,
      required: true
    },
    highlightedKeys: {
      type: Array,
      default: () => []
    }
  },
  components: {
    TransitionsSection: TransitionsSection
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open']
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsColumn.vue



TransitionsColumnvue_type_script_lang_ts.render = TransitionsColumnvue_type_template_id_29da2b1e_render

/* harmony default export */ var TransitionsColumn = (TransitionsColumnvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.vue?vue&type=template&id=55a322c3

const TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_1 = {
  class: "transitionsRibbons",
  ref: "layer",
  "aria-hidden": "true",
  focusable: "false"
};
const TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_2 = ["id", "x1", "x2"];
const TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_3 = ["d", "fill"];
function TransitionsRibbonsvue_type_template_id_55a322c3_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("defs", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("linearGradient", {
    id: _ctx.gradientId,
    x1: _ctx.gradientX1,
    y1: "0",
    x2: _ctx.gradientX2,
    y2: "0"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("stop", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRibbons__stopOuter", _ctx.outerStopClass]),
    offset: "0"
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("stop", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRibbons__stopInner", _ctx.innerStopClass]),
    offset: "1"
  }, null, 2)], 8, TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_2)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.paths, path => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["transitionsRibbons__band", _ctx.bandClasses(path.key)]),
      key: path.key,
      d: path.d,
      fill: `url(#${_ctx.gradientId})`
    }, null, 10, TransitionsRibbonsvue_type_template_id_55a322c3_hoisted_3);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.vue?vue&type=template&id=55a322c3

// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/ribbonGeometry.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/** Smallest ribbon a row may get, so a row with a tiny share still reads as connected. */
const MIN_RIBBON_THICKNESS = 3;
/** Absolute floor applied after the overflow rescale, so no ribbon collapses to nothing. */
const HAIRLINE_THICKNESS = 1;
function sanitiseShare(share) {
  return Number.isFinite(share) && share > 0 ? share : 0;
}
/**
 * Lays out one side's ribbons, left to right for incoming and right to left for outgoing.
 *
 * Each row takes a proportional slice of the center band, clamped up to MIN_RIBBON_THICKNESS.
 * Clamped rows keep that minimum and the rest share what is left, so the minimum is not scaled
 * away again; only when the minimums alone overflow does everything scale down.
 *
 * @returns one path per row, in input order. Empty when there is nothing to draw.
 */
function computeRibbonPaths(rows, layout) {
  var _layout$rowOverlap, _layout$rowStraight;
  const {
    side,
    width,
    centerTop,
    centerHeight
  } = layout;
  if (!rows.length || width <= 0 || centerHeight <= 0) {
    return [];
  }
  const total = rows.reduce((sum, row) => sum + sanitiseShare(row.share), 0);
  if (total <= 0) {
    return [];
  }
  const raw = rows.map(row => sanitiseShare(row.share) / total * centerHeight);
  const isClamped = raw.map(thickness => thickness < MIN_RIBBON_THICKNESS);
  const clampedTotal = isClamped.filter(Boolean).length * MIN_RIBBON_THICKNESS;
  const unclampedTotal = raw.reduce((sum, thickness, index) => isClamped[index] ? sum : sum + thickness, 0);
  const budget = centerHeight - clampedTotal;
  let thicknesses;
  if (budget <= 0 || unclampedTotal <= 0) {
    // Too many rows to give each one the minimum; give up on it and scale the stack to fit.
    const scale = centerHeight / (clampedTotal + unclampedTotal);
    thicknesses = raw.map((thickness, index) => Math.max(HAIRLINE_THICKNESS, (isClamped[index] ? MIN_RIBBON_THICKNESS : thickness) * scale));
  } else {
    // Clamped rows keep their minimum; the rest share what is left, in proportion.
    const scale = budget / unclampedTotal;
    thicknesses = raw.map((thickness, index) => isClamped[index] ? MIN_RIBBON_THICKNESS : thickness * scale);
  }
  const rowOverlap = (_layout$rowOverlap = layout.rowOverlap) !== null && _layout$rowOverlap !== void 0 ? _layout$rowOverlap : 0;
  const rowStraight = (_layout$rowStraight = layout.rowStraight) !== null && _layout$rowStraight !== void 0 ? _layout$rowStraight : 0;
  const rowX = side === 'incoming' ? -rowOverlap : width + rowOverlap;
  const centerX = side === 'incoming' ? width : 0;
  // Where the band stops running straight and starts curving towards the center.
  const curveX = side === 'incoming' ? Math.min(rowX + rowStraight, centerX) : Math.max(rowX - rowStraight, centerX);
  const controlX = curveX + (centerX - curveX) / 2;
  let bandTop = centerTop;
  return rows.map((row, index) => {
    const thickness = thicknesses[index];
    const rowTop = row.top;
    const rowBottom = row.top + row.height;
    const bandBottom = bandTop + thickness;
    const d = `M${rowX},${rowTop}` + ` L${curveX},${rowTop}` + ` C${controlX},${rowTop} ${controlX},${bandTop} ${centerX},${bandTop}` + ` L${centerX},${bandBottom}` + ` C${controlX},${bandBottom} ${controlX},${rowBottom} ${curveX},${rowBottom}` + ` L${rowX},${rowBottom}` + ' Z';
    bandTop = bandBottom;
    return {
      key: row.key,
      d,
      thickness
    };
  });
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/useRibbonGeometry.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/** Keeps the band clear of the card's rounded corners at each end. */
const CENTER_INSET = 50;
/** Rows paint over the layer, so this much of the band hides under the row it leaves. */
const ROW_OVERLAP = 10;
/** The overlap plus a few pixels, so the band leaves the row at the row's exact height. */
const ROW_STRAIGHT = ROW_OVERLAP + 6;
/** Caps CENTER_INSET on a short card, which would otherwise leave no edge to draw into. */
const MAX_INSET_SHARE = 0.25;
/** Escaped for a double-quoted attribute value, not CSS.escape()'s identifier position. */
function escapeKey(key) {
  return key.replace(/["\\]/g, '\\$&');
}
/** Specs stub getBoundingClientRect, which jsdom otherwise reports as 0x0. */
function measure(element) {
  const rect = element.getBoundingClientRect();
  return {
    top: rect.top,
    height: rect.height,
    width: rect.width
  };
}
/**
 * Measures one column's rows and lays out the ribbons connecting them to the center card.
 * Re-measures on resize, coalescing bursts into a single animation frame.
 */
function useRibbonGeometry(options) {
  const paths = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([]);
  let frame = null;
  let scheduled = false;
  let observer = null;
  let observed = [];
  function layout(layer, column, center) {
    const layerRect = measure(layer);
    const centerRect = measure(center);
    const measuredRows = [];
    options.rows.value.forEach(source => {
      const element = column.querySelector(`[data-ribbon-key="${escapeKey(source.key)}"]`);
      if (!element) {
        return;
      }
      const rect = measure(element);
      measuredRows.push({
        key: source.key,
        share: source.share,
        top: rect.top - layerRect.top,
        height: rect.height
      });
    });
    const inset = Math.min(CENTER_INSET, centerRect.height * MAX_INSET_SHARE);
    paths.value = computeRibbonPaths(measuredRows, {
      side: options.side,
      width: layerRect.width,
      centerTop: centerRect.top - layerRect.top + inset,
      centerHeight: centerRect.height - inset * 2,
      rowOverlap: ROW_OVERLAP,
      rowStraight: ROW_STRAIGHT
    });
  }
  /** Rebinds the observer only when the element set actually changed. */
  function observe(elements) {
    if (typeof ResizeObserver !== 'function') {
      return;
    }
    const unchanged = elements.length === observed.length && elements.every((element, index) => element === observed[index]);
    if (unchanged) {
      return;
    }
    if (!observer) {
      observer = new ResizeObserver(schedule); // eslint-disable-line no-use-before-define
    }
    observer.disconnect();
    elements.forEach(element => observer.observe(element));
    observed = elements;
  }
  function recompute() {
    const layer = options.layer.value;
    const column = options.column();
    const center = options.center();
    if (!layer || !column || !center) {
      paths.value = [];
      return;
    }
    observe([layer, column, center]);
    layout(layer, column, center);
  }
  function schedule() {
    if (scheduled) {
      return;
    }
    scheduled = true;
    if (typeof requestAnimationFrame !== 'function') {
      scheduled = false;
      recompute();
      return;
    }
    const handle = requestAnimationFrame(() => {
      scheduled = false;
      frame = null;
      recompute();
    });
    // A frame callback can run before rAF() returns, so only keep a handle that is still pending.
    if (scheduled) {
      frame = handle;
    }
  }
  function teardown() {
    if (observer) {
      observer.disconnect();
      observer = null;
      observed = [];
    }
    if (frame !== null) {
      if (typeof cancelAnimationFrame === 'function') {
        cancelAnimationFrame(frame);
      }
      frame = null;
    }
    scheduled = false;
  }
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(schedule);
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(teardown);
  // Post-flush, because the rows only exist in the DOM after the update is applied. Not deep: the
  // producer returns a fresh array each time, so identity alone fires this.
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(options.rows, schedule, {
    flush: 'post'
  });
  return {
    paths
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.vue?vue&type=script&lang=ts


// Gradient ids must be unique per mounted layer; two layers share every page, and a page may hold
// more than one report.
let gradientSequence = 0;
/* harmony default export */ var TransitionsRibbonsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    side: {
      type: String,
      required: true
    },
    rows: {
      type: Array,
      required: true
    },
    /** Resolves the column holding the rows these ribbons connect to. */
    column: {
      type: Function,
      required: true
    },
    /** Resolves the center card the ribbons converge on. */
    center: {
      type: Function,
      required: true
    },
    highlightedKeys: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    const layer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    gradientSequence += 1;
    const gradientId = `transitionsRibbonsGradient-${props.side}-${gradientSequence}`;
    const {
      paths
    } = useRibbonGeometry({
      side: props.side,
      layer,
      column: () => props.column(),
      center: () => props.center(),
      rows: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toRef"])(props, 'rows')
    });
    return {
      layer,
      paths,
      gradientId
    };
  },
  computed: {
    isOutgoing() {
      return this.side === 'outgoing';
    },
    /** The deep end of the gradient sits on the column side, which flips with the side. */
    gradientX1() {
      return this.isOutgoing ? 1 : 0;
    },
    gradientX2() {
      return this.isOutgoing ? 0 : 1;
    },
    outerStopClass() {
      return this.isOutgoing ? 'transitionsRibbons__stopOuter--outgoing' : '';
    },
    innerStopClass() {
      return this.isOutgoing ? 'transitionsRibbons__stopInner--outgoing' : '';
    }
  },
  methods: {
    bandClasses(key) {
      return {
        'transitionsRibbons__band--highlighted': this.highlightedKeys.includes(key)
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsRibbons.vue



TransitionsRibbonsvue_type_script_lang_ts.render = TransitionsRibbonsvue_type_template_id_55a322c3_render

/* harmony default export */ var TransitionsRibbons = (TransitionsRibbonsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/transitionsReportData.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Turns a loaded Piwik_Transitions_Model into the render-ready shape the report's components
 * consume, and resolves the API's exception names to displayable text.
 *
 * Pure: nothing here touches the request, the loading state or any ref, which is why it sits
 * beside useTransitionsData rather than inside it -- the shaping can then be exercised directly
 * instead of only through a mounted component.
 */

/** Value placeholder marker, so a metric label can be split around its value without markup. */
const VALUE_MARKER = '\u0001';
/**
 * The groups of each side, in render order. Mirrors Piwik_Transitions' leftGroups/rightGroups plus
 * the two terminal metrics (direct entries, exits) that have no detail rows.
 */
const TRANSITIONS_GROUPS = [{
  name: 'previousPages',
  side: 'incoming',
  titleKey: 'Transitions_FromPreviousPages',
  inlineKey: 'Transitions_FromPreviousPagesInline',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-document',
  canExpand: true
}, {
  name: 'previousSiteSearches',
  side: 'incoming',
  titleKey: 'Transitions_FromPreviousSiteSearches',
  inlineKey: 'Transitions_FromPreviousSiteSearchesInline',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-search',
  canExpand: true
}, {
  name: 'searchEngines',
  side: 'incoming',
  titleKey: 'Transitions_FromSearchEngines',
  inlineKey: 'Referrers_TypeSearchEngines',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-search',
  canExpand: true
}, {
  name: 'socialNetworks',
  side: 'incoming',
  titleKey: 'Transitions_FromSocialNetworks',
  inlineKey: 'Referrers_TypeSocialNetworks',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-users',
  canExpand: true
}, {
  name: 'aiAssistants',
  side: 'incoming',
  titleKey: 'Transitions_FromAIAssistants',
  inlineKey: 'Referrers_TypeAIAssistants',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-ai-assistants',
  canExpand: true
}, {
  name: 'websites',
  side: 'incoming',
  titleKey: 'Transitions_FromWebsites',
  inlineKey: 'Referrers_TypeWebsites',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-outlink',
  canExpand: true
}, {
  name: 'campaigns',
  side: 'incoming',
  titleKey: 'Transitions_FromCampaigns',
  inlineKey: 'Referrers_TypeCampaigns',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-reporting-referer',
  canExpand: true
}, {
  name: 'directEntries',
  side: 'incoming',
  titleKey: 'Transitions_DirectEntries',
  inlineKey: 'Referrers_TypeDirectEntries',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-sign-in',
  canExpand: false
}, {
  name: 'followingPages',
  side: 'outgoing',
  titleKey: 'Transitions_ToFollowingPages',
  inlineKey: 'Transitions_ToFollowingPagesInline',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-document',
  canExpand: true
}, {
  name: 'followingSiteSearches',
  side: 'outgoing',
  titleKey: 'Transitions_ToFollowingSiteSearches',
  inlineKey: 'Transitions_ToFollowingSiteSearchesInline',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-search',
  canExpand: true
}, {
  name: 'downloads',
  side: 'outgoing',
  titleKey: 'General_Downloads',
  inlineKey: 'Transitions_NumDownloads',
  countKey: 'Transitions_NumDownloads',
  icon: 'icon-download',
  canExpand: true
}, {
  name: 'outlinks',
  side: 'outgoing',
  titleKey: 'General_Outlinks',
  inlineKey: 'Transitions_NumOutlinks',
  countKey: 'Transitions_NumOutlinks',
  icon: 'icon-outlink',
  canExpand: true
}, {
  name: 'exits',
  side: 'outgoing',
  titleKey: 'General_ColumnExits',
  inlineKey: 'Transitions_ExitsInline',
  countKey: 'Transitions_NumPageviews',
  icon: 'icon-sign-out',
  canExpand: false
}];
/** Exception names Transitions.getTransitionsForAction throws, mapped to their translation keys. */
const ERROR_TRANSLATIONS = {
  NoDataForAction: {
    title: 'Transitions_NoDataForAction',
    details: 'Transitions_NoDataForActionDetails'
  },
  PeriodNotAllowed: {
    title: 'Transitions_PeriodNotAllowed',
    details: 'Transitions_PeriodNotAllowedDetails'
  }
};
/**
 * Removes protocol, www and trailing slashes from a URL; with `removeDomain` the domain goes too.
 * Ported verbatim from Piwik_Transitions_Util.shortenUrl.
 */
function shortenUrl(url, removeDomain = false) {
  if (url === 'Others') {
    return url;
  }
  const urlBackup = url;
  let shortened = url.replace(/http(s)?:\/\/(www\.)?/, '');
  if (urlBackup === shortened) {
    return shortened;
  }
  if (removeDomain) {
    shortened = shortened.replace(/[^/]*/, '');
    if (shortened === '/') {
      shortened = urlBackup;
    }
  }
  return shortened.replace(/\/$/, '');
}
/** The model exposes group totals as `<group>NbTransitions`, but direct entries/exits as-is. */
function metricName(group) {
  return group.canExpand ? `${group.name}NbTransitions` : group.name;
}
function resolveError(errorName, actionName) {
  var _ERROR_TRANSLATIONS$e;
  // In development mode the API appends a stack trace to the exception message, so match on the
  // leading exception name rather than on the whole message.
  const [name] = errorName.split(/\s/, 1);
  const keys = (_ERROR_TRANSLATIONS$e = ERROR_TRANSLATIONS[errorName]) !== null && _ERROR_TRANSLATIONS$e !== void 0 ? _ERROR_TRANSLATIONS$e : ERROR_TRANSLATIONS[name];
  if (!keys) {
    // An exception we have no translation for, so the name is all there is to show. The back link
    // still has to read as a link: every translated error uses Transitions_ErrorBack for it.
    return {
      title: errorName,
      message: '',
      backLabel: Object(external_CoreHome_["translate"])('Transitions_ErrorBack')
    };
  }
  const subject = `<span>${external_CoreHome_["Matomo"].helper.addBreakpointsToUrl(actionName)}</span>`;
  return {
    title: Object(external_CoreHome_["translate"])(keys.title, subject),
    message: Object(external_CoreHome_["translate"])(keys.details),
    backLabel: Object(external_CoreHome_["translate"])('Transitions_ErrorBack')
  };
}
/**
 * The share-of-all-pageviews tooltip. Empty until the site total is known, since the share is the
 * whole point of it.
 */
function buildPageviewsTooltip(model, totalNbPageviews) {
  if (!totalNbPageviews) {
    return '';
  }
  const shareOfAll = external_CoreHome_["NumberFormatter"].formatPercent(Math.round(model.pageviews / totalNbPageviews * 1000) / 10);
  return `${Object(external_CoreHome_["translate"])('Transitions_ShareOfAllPageviews', external_CoreHome_["NumberFormatter"].formatNumber(model.pageviews), shareOfAll)}\n${Object(external_CoreHome_["translate"])('General_DateRange')} ${model.date}`;
}
/** Splits an inline label around its value, so the value can be emphasised without markup. */
function splitInlineLabel(inlineKey) {
  var _parts$, _parts$2;
  const parts = Object(external_CoreHome_["translate"])(inlineKey, VALUE_MARKER).split(VALUE_MARKER);
  return {
    before: (_parts$ = parts[0]) !== null && _parts$ !== void 0 ? _parts$ : '',
    after: (_parts$2 = parts[1]) !== null && _parts$2 !== void 0 ? _parts$2 : ''
  };
}
/** The detail rows of a group, shown while it is the open group on its side. */
function buildRows(model, group, actionType, groupShare) {
  if (!group.canExpand) {
    return [];
  }
  const details = model.getDetailsForGroup(group.name) || [];
  return details.map((detail, index) => {
    var _ref;
    const rawLabel = (_ref = typeof detail.url !== 'undefined' ? detail.url : detail.label) !== null && _ref !== void 0 ? _ref : '';
    const isOthers = rawLabel === 'Others';
    const isInternalPage = group.name === 'previousPages' || group.name === 'followingPages';
    const isDownload = group.name === 'downloads';
    const isOutlink = group.name === 'outlinks' || group.name === 'websites';
    // How much of the URL the label keeps, and whether the row links out, are two separate
    // questions: a download keeps only its path but still opens in a new tab.
    const shortenWithDomain = actionType === 'url' && isInternalPage || isDownload;
    const shortenKeepingDomain = isOutlink;
    const linksOut = isOutlink || isDownload;
    let label = rawLabel;
    if (shortenWithDomain) {
      label = shortenUrl(rawLabel, true);
    } else if (shortenKeepingDomain) {
      label = shortenUrl(rawLabel);
    }
    return {
      key: `${group.name}-${index}`,
      kind: 'action',
      icon: group.icon,
      label,
      fullLabel: label === rawLabel || isOthers ? '' : rawLabel,
      countLabel: Object(external_CoreHome_["translate"])(group.countKey, external_CoreHome_["NumberFormatter"].formatNumber(detail.referrals)),
      percentage: external_CoreHome_["NumberFormatter"].formatPercent(detail.percentage),
      share: detail.percentage / 100 * groupShare,
      externalUrl: !isOthers && linksOut ? rawLabel : undefined,
      transitionUrl: !isOthers && isInternalPage ? rawLabel : undefined,
      isOthers
    };
  });
}
/** The single row that stands for a whole group while some other group is the open one. */
function buildSummaryRow(group, title, value, share, shareLabel) {
  return {
    key: group.name,
    kind: 'summary',
    icon: group.icon,
    label: title,
    fullLabel: '',
    countLabel: Object(external_CoreHome_["translate"])(group.countKey, external_CoreHome_["NumberFormatter"].formatNumber(value)),
    percentage: shareLabel,
    share,
    opensGroup: group.canExpand ? group.name : undefined,
    isOthers: false
  };
}
function buildReport(model, actionType, actionName) {
  const groups = [];
  const metrics = [];
  let incomingTotal = 0;
  let outgoingTotal = 0;
  TRANSITIONS_GROUPS.forEach(group => {
    const value = model[metricName(group)] || 0;
    const share = model.getPercentage(metricName(group));
    // Formatted once, so the summary row's pill and the card's tooltip cannot round the same
    // share to two different numbers. The model rounds to whole percent above 10% and to one
    // decimal below it, which is also what the API's own detail-row percentages use.
    const shareLabel = model.getPercentage(metricName(group), true);
    const title = model.getGroupTitle(group.name);
    if (group.side === 'incoming') {
      incomingTotal += value;
    } else {
      outgoingTotal += value;
    }
    groups.push({
      name: group.name,
      side: group.side,
      title,
      nbTransitions: value,
      canExpand: group.canExpand,
      countLabel: Object(external_CoreHome_["translate"])(group.countKey, external_CoreHome_["NumberFormatter"].formatNumber(value)),
      rows: buildRows(model, group, actionType, share),
      summaryRow: buildSummaryRow(group, title, value, share, shareLabel)
    });
    const {
      before,
      after
    } = splitInlineLabel(group.inlineKey);
    metrics.push({
      key: group.name,
      groupName: group.name,
      side: group.side,
      labelBefore: before,
      labelAfter: after,
      value,
      valueLabel: external_CoreHome_["NumberFormatter"].formatNumber(value),
      // A metric at zero has no share to explain, so it gets no tooltip -- the legacy renderer
      // left one off there too.
      tooltip: value > 0 ? Object(external_CoreHome_["translate"])('Transitions_XOfAllPageviews', shareLabel) : '',
      canExpand: group.canExpand
    });
  });
  return {
    actionName,
    title: actionType === 'url' ? shortenUrl(actionName, true) : actionName,
    titleUrl: actionType === 'url' ? actionName : undefined,
    loops: model.loops,
    loopsLabel: Object(external_CoreHome_["translate"])('Transitions_LoopsInline', external_CoreHome_["NumberFormatter"].formatNumber(model.loops)),
    loopsTooltip: Object(external_CoreHome_["translate"])('Transitions_XOfAllPageviews', model.getPercentage('loops', true)),
    pageviewsLabel: Object(external_CoreHome_["translate"])('Transitions_NumPageviews', external_CoreHome_["NumberFormatter"].formatNumber(model.pageviews)),
    pageviewsTooltip: buildPageviewsTooltip(model, model.getTotalNbPageviews()),
    incomingTotal,
    outgoingTotal,
    groups,
    metrics
  };
}
/**
 * Seeds the group titles the API response does not carry, so getGroupTitle() can resolve them.
 */
function seedGroupTitles(model) {
  TRANSITIONS_GROUPS.forEach(group => {
    model.groupTitles[group.name] = Object(external_CoreHome_["translate"])(group.titleKey);
  });
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/useTransitionsData.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/** The one request whose failure is the report's own failure. */
const REPORT_API_METHOD = 'Transitions.getTransitionsForAction';
/**
 * Loads one action and owns the loading/error state. The request and parsing stay in the legacy
 * Piwik_Transitions_Model/Ajax pair, so the wire contract is unchanged.
 *
 * Last-request-wins: a response is dropped once a newer load has started or the component has
 * unmounted, so fast switching cannot paint stale data.
 */
function useTransitionsData() {
  const isLoading = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
  const error = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
  const report = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
  let requestId = 0;
  let disposed = false;
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(() => {
    disposed = true;
  });
  const isCurrent = id => !disposed && id === requestId;
  function load(actionType, actionName, overrideParams) {
    requestId += 1;
    const id = requestId;
    isLoading.value = true;
    error.value = null;
    const ajax = new window.Piwik_Transitions_Ajax();
    const model = new window.Piwik_Transitions_Model(ajax);
    ajax.setErrorCallback((errorName, params) => {
      // The site total shares this ajax instance, so its failures arrive here too. Only the
      // report's own failure should replace the report.
      if (params.method !== REPORT_API_METHOD) {
        // The total's own callback never runs on failure and the request is fired once per page,
        // so release its waiters here rather than leaving them queued for good.
        model.notifyTotalNbPageviewsLoaded(false);
        return;
      }
      if (!isCurrent(id)) {
        return;
      }
      isLoading.value = false;
      report.value = null;
      error.value = resolveError(errorName, actionName);
    });
    seedGroupTitles(model);
    model.loadData(actionType, actionName, overrideParams, () => {
      if (!isCurrent(id)) {
        return;
      }
      isLoading.value = false;
      error.value = null;
      report.value = buildReport(model, actionType, actionName);
      // Still in flight on the first report, so fill the tooltip in when it lands.
      model.whenTotalNbPageviewsLoaded(totalNbPageviews => {
        if (!isCurrent(id) || !report.value) {
          return;
        }
        // In place, not a new object: replacing it would re-lay-out both ribbon layers for the
        // sake of one tooltip string.
        report.value.pageviewsTooltip = buildPageviewsTooltip(model, totalNbPageviews);
      });
      external_CoreHome_["Matomo"].postEvent('Transitions.dataChanged', {
        actionType,
        actionName
      });
    });
  }
  return {
    isLoading,
    error,
    report,
    load
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.vue?vue&type=script&lang=ts






/**
 * The rows a side's ribbon layer connects, in the order they are rendered. A free function rather
 * than a method, so it reads the sections computed instead of rebuilding them: methods are not
 * cached, so sectionsFor() would otherwise run twice per side on every invalidation.
 */
function ribbonRows(sections) {
  return sections.flatMap(section => section.rows.map(row => ({
    key: row.key,
    share: row.share
  })));
}
/* harmony default export */ var TransitionsReportvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    actionType: {
      type: String,
      required: true
    },
    actionName: {
      type: String,
      required: true
    },
    /**
     * segment/date/period/idSite the report should be fetched for, as parsed from the row action
     * link. Without these an Overlay or deep-linked popover would fetch the wrong data.
     */
    overrideParams: {
      type: Object,
      default: () => ({})
    },
    context: {
      type: String,
      default: 'embedded'
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    TransitionsCenterCard: TransitionsCenterCard,
    TransitionsColumn: TransitionsColumn,
    TransitionsRibbons: TransitionsRibbons
  },
  setup: () => useTransitionsData(),
  data() {
    return {
      openGroups: {
        incoming: 'previousPages',
        outgoing: 'followingPages'
      },
      highlightedGroup: '',
      isInDashboardWidget: false
    };
  },
  created() {
    this.reload();
  },
  mounted() {
    // A dashboard column is far narrower than the viewport a media query would see.
    this.isInDashboardWidget = !!this.$el.closest('[widgetId]');
  },
  watch: {
    actionType() {
      this.reload();
    },
    actionName() {
      this.reload();
    },
    overrideParams: {
      deep: true,
      handler() {
        this.reload();
      }
    }
  },
  computed: {
    incomingSections() {
      return this.sectionsFor('incoming');
    },
    outgoingSections() {
      return this.sectionsFor('outgoing');
    },
    incomingRibbonRows() {
      return ribbonRows(this.incomingSections);
    },
    outgoingRibbonRows() {
      return ribbonRows(this.outgoingSections);
    },
    /**
     * The popover names what it is fetching, the way the legacy renderer's own loading state did.
     * The embedded report keeps the generic message, which is what its inline loader showed.
     *
     * One key holding the whole sentence rather than a fragment with the name appended: locales
     * put the name elsewhere in the sentence, and an appended right-to-left name would need its
     * own isolation.
     */
    loadingMessage() {
      if (this.context !== 'popover') {
        return Object(external_CoreHome_["translate"])('General_LoadingData');
      }
      return Object(external_CoreHome_["translate"])('Transitions_LoadingTransitionsFor', this.actionName);
    },
    /** Ribbon keys belonging to the highlighted group, so its bands can be emphasised. */
    highlightedKeys() {
      var _this$report;
      if (!this.highlightedGroup) {
        return [];
      }
      const group = (((_this$report = this.report) === null || _this$report === void 0 ? void 0 : _this$report.groups) || []).find(candidate => candidate.name === this.highlightedGroup);
      if (!group) {
        return [];
      }
      return this.openGroups[group.side] === group.name && group.canExpand ? group.rows.map(row => row.key) : [group.name];
    }
  },
  methods: {
    reload() {
      this.openGroups = {
        incoming: 'previousPages',
        outgoing: 'followingPages'
      };
      this.highlightedGroup = '';
      this.load(this.actionType, this.actionName, this.overrideParams);
    },
    groupsFor(side) {
      var _this$report2;
      return (((_this$report2 = this.report) === null || _this$report2 === void 0 ? void 0 : _this$report2.groups) || []).filter(group => group.side === side && group.nbTransitions > 0);
    },
    /**
     * A column holds at most two blocks: the open group, listed row by row under its own title,
     * and everything else on that side, one summary row per group. A catch-all is titled "Other
     * sources"/"Other destinations" while a group is open, and plain "Incoming traffic"/"Outgoing
     * traffic" when none is -- on an entry or exit page there is nothing for it to be other than.
     */
    sectionsFor(side) {
      const groups = this.groupsFor(side);
      const openGroup = groups.find(group => group.name === this.openGroups[side] && group.canExpand);
      const sections = [];
      if (openGroup) {
        sections.push({
          key: openGroup.name,
          side,
          title: openGroup.title,
          badge: openGroup.countLabel,
          rows: openGroup.rows
        });
      }
      const rest = groups.filter(group => group !== openGroup);
      if (rest.length) {
        const total = rest.reduce((sum, group) => sum + group.nbTransitions, 0);
        // The block is only "other" while something else is open; with nothing open it holds all
        // of that side's traffic and says so.
        const otherKey = side === 'incoming' ? 'Transitions_OtherSources' : 'Transitions_OtherDestinations';
        const allKey = side === 'incoming' ? 'Transitions_IncomingTraffic' : 'Transitions_OutgoingTraffic';
        sections.push({
          key: `${side}-other`,
          side,
          title: Object(external_CoreHome_["translate"])(openGroup ? otherKey : allKey),
          // Only the incoming block gets a badge: on the outgoing side this would phrase summed
          // downloads and outlinks as pageviews.
          badge: side === 'incoming' ? Object(external_CoreHome_["translate"])('Transitions_NumPageviews', external_CoreHome_["NumberFormatter"].formatNumber(total)) : '',
          rows: rest.map(group => group.summaryRow)
        });
      }
      return sections;
    },
    incomingColumnElement() {
      var _this$$refs$incomingC;
      return (_this$$refs$incomingC = this.$refs.incomingColumn) !== null && _this$$refs$incomingC !== void 0 ? _this$$refs$incomingC : null;
    },
    outgoingColumnElement() {
      var _this$$refs$outgoingC;
      return (_this$$refs$outgoingC = this.$refs.outgoingColumn) !== null && _this$$refs$outgoingC !== void 0 ? _this$$refs$outgoingC : null;
    },
    /**
     * The ribbons meet the card itself, not the grid cell around it, so the band lines up with
     * what the reader sees rather than with the cell's padding.
     */
    centerCardElement() {
      var _card$$el;
      const card = this.$refs.centerCard;
      return (_card$$el = card === null || card === void 0 ? void 0 : card.$el) !== null && _card$$el !== void 0 ? _card$$el : null;
    },
    onOpen(side, groupName) {
      this.openGroups = Object.assign(Object.assign({}, this.openGroups), {}, {
        [side]: groupName
      });
      this.highlightedGroup = '';
    },
    onOpenFromCard(groupName) {
      var _this$report3;
      const group = (((_this$report3 = this.report) === null || _this$report3 === void 0 ? void 0 : _this$report3.groups) || []).find(candidate => candidate.name === groupName);
      if (group !== null && group !== void 0 && group.canExpand) {
        this.onOpen(group.side, groupName);
      }
    },
    /** A row highlights the group it stands for, or the group its detail rows belong to. */
    onRowHighlight(row) {
      if (row.kind === 'summary') {
        this.highlightedGroup = row.key;
        return;
      }
      const [groupName] = row.key.split('-');
      this.highlightedGroup = groupName;
    },
    onNavigate(url) {
      if (this.context === 'popover') {
        // Mounted through a vue-entry, so the row action cannot pass a handler in; it listens for
        // this instead and re-opens the popover, which keeps the popover URL in sync.
        external_CoreHome_["Matomo"].postEvent('Transitions.reloadPopover', {
          url
        });
        return;
      }
      external_CoreHome_["Matomo"].postEvent('Transitions.switchTransitionsUrl', {
        url
      });
    },
    /**
     * Offered in the popover only, where a history step closes the popover and lands back on the
     * report behind it. On the Transitions page the same step would navigate away from the page
     * altogether, which is why the legacy renderer left the link out of its inline error too.
     */
    goBack() {
      window.history.back();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsReport/TransitionsReport.vue



TransitionsReportvue_type_script_lang_ts.render = TransitionsReportvue_type_template_id_a527546c_render

/* harmony default export */ var TransitionsReport = (TransitionsReportvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=script&lang=ts





/* harmony default export */ var TransitionSwitchervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isWidget: Boolean
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    MatomoLoader: external_CoreHome_["MatomoLoader"],
    TransitionsReport: TransitionsReport
  },
  directives: {
    TransitionExporter: TransitionExporter
  },
  data() {
    return {
      actionType: 'Actions.getPageUrls',
      actionNameOptions: [],
      actionTypeOptions: [{
        key: 'Actions.getPageUrls',
        value: Object(external_CoreHome_["translate"])('Actions_PageUrls')
      }, {
        key: 'Actions.getPageTitles',
        value: Object(external_CoreHome_["translate"])('Actions_WidgetPageTitles')
      }],
      isLoading: false,
      actionName: null,
      isEnabled: true,
      noDataKey: '_____ignore_____'
    };
  },
  setup() {
    const transitionsUrl = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])();
    const onSwitchTransitionsUrl = params => {
      if (params !== null && params !== void 0 && params.url) {
        transitionsUrl.value = params.url;
      }
    };
    external_CoreHome_["Matomo"].on('Transitions.switchTransitionsUrl', onSwitchTransitionsUrl);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(() => {
      external_CoreHome_["Matomo"].off('Transitions.switchTransitionsUrl', onSwitchTransitionsUrl);
    });
    return {
      transitionsUrl
    };
  },
  watch: {
    transitionsUrl(newValue) {
      let url = newValue;
      if (this.isUrlReport) {
        url = url.replace('https://', '').replace('http://', '');
      }
      const found = this.actionNameOptions.find(option => {
        let optionUrl = option.url;
        if (optionUrl && this.isUrlReport) {
          optionUrl = String(optionUrl).replace('https://', '').replace('http://', '');
        } else {
          optionUrl = undefined;
        }
        return option.key === url || url === optionUrl && optionUrl;
      });
      if (found) {
        this.actionName = found.key;
      } else {
        // we only fetch top 100 in the report... so the entry the user clicked on, might not
        // be in the top 100
        this.actionNameOptions = [...this.actionNameOptions, {
          key: url,
          value: url
        }];
        this.actionName = url;
      }
    },
    actionType(newValue) {
      this.fetch(newValue);
    }
  },
  created() {
    this.fetch(this.actionType);
  },
  methods: {
    detectActionName(reports) {
      const othersLabel = Object(external_CoreHome_["translate"])('General_Others');
      reports.forEach(report => {
        if (!report) {
          return;
        }
        if (report.label === othersLabel) {
          return;
        }
        const key = this.isUrlReport ? report.url : report.label;
        if (key) {
          const pageviews = Object(external_CoreHome_["translate"])('Transitions_NumPageviews', report.nb_hits);
          const label = `${report.label} (${pageviews})`;
          this.actionNameOptions.push({
            key,
            value: label,
            url: report.url
          });
          if (!this.actionName) {
            this.actionName = key;
          }
        }
      });
    },
    fetch(type) {
      this.isLoading = true;
      this.actionNameOptions = [];
      this.actionName = null;
      external_CoreHome_["AjaxHelper"].fetch({
        method: type,
        flat: 1,
        filter_limit: 100,
        filter_sort_order: 'desc',
        filter_sort_column: 'nb_hits',
        showColumns: 'label,nb_hits,url'
      }).then(report => {
        this.isLoading = false;
        this.actionNameOptions = [];
        this.actionName = null;
        if (report !== null && report !== void 0 && report.length) {
          this.isEnabled = true;
          this.detectActionName(report);
        }
        if (this.actionName === null || this.actionNameOptions.length === 0) {
          this.isEnabled = false;
          this.actionName = this.noDataKey;
          this.actionNameOptions.push({
            key: this.noDataKey,
            value: Object(external_CoreHome_["translate"])('CoreHome_ThereIsNoDataForThisReport')
          });
        }
      }).catch(() => {
        this.isLoading = false;
        this.isEnabled = false;
      });
    }
  },
  computed: {
    isUrlReport() {
      return this.actionType === 'Actions.getPageUrls';
    },
    /** The report identifies actions as 'url' or 'title', not by the report method name. */
    transitionsActionType() {
      return this.isUrlReport ? 'url' : 'title';
    },
    hasAction() {
      return !!this.actionName && this.actionName !== this.noDataKey;
    },
    /** Narrowed for the report, which is only rendered once an action is actually selected. */
    selectedActionName() {
      var _this$actionName;
      return (_this$actionName = this.actionName) !== null && _this$actionName !== void 0 ? _this$actionName : '';
    },
    availableInOtherReports2() {
      return Object(external_CoreHome_["translate"])('Transitions_AvailableInOtherReports2', '<span class="icon-transition"></span>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue



TransitionSwitchervue_type_script_lang_ts.render = TransitionSwitchervue_type_template_id_7d7f6af2_render

/* harmony default export */ var TransitionSwitcher = (TransitionSwitchervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsPage/TransitionsPage.vue?vue&type=template&id=7f0eaf9e

function TransitionsPagevue_type_template_id_7f0eaf9e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_TransitionSwitcher = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TransitionSwitcher");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return !_ctx.isWidget ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    "help-text": _ctx.translate('Transitions_FeatureDescription'),
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/transitions/'),
    "content-title": _ctx.translate('Transitions_Transitions')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TransitionSwitcher, {
      "is-widget": _ctx.isWidget
    }, null, 8, ["is-widget"])]),
    _: 1
  }, 8, ["help-text", "help-url", "content-title"])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_TransitionSwitcher, {
    key: 1,
    "is-widget": _ctx.isWidget
  }, null, 8, ["is-widget"]));
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsPage/TransitionsPage.vue?vue&type=template&id=7f0eaf9e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionsPage/TransitionsPage.vue?vue&type=script&lang=ts



/* harmony default export */ var TransitionsPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isWidget: Boolean
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    TransitionSwitcher: TransitionSwitcher
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsPage/TransitionsPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionsPage/TransitionsPage.vue



TransitionsPagevue_type_script_lang_ts.render = TransitionsPagevue_type_template_id_7f0eaf9e_render

/* harmony default export */ var TransitionsPage = (TransitionsPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterLink.vue?vue&type=template&id=e5b0991c

const TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_1 = {
  class: "dataTableAction"
};
const TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export"
}, null, -1);
const TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_3 = [TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_2];
function TransitionExporterLinkvue_type_template_id_e5b0991c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_transition_exporter = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("transition-exporter");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_1, TransitionExporterLinkvue_type_template_id_e5b0991c_hoisted_3)), [[_directive_transition_exporter]]);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterLink.vue?vue&type=template&id=e5b0991c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterLink.vue?vue&type=script&lang=ts


/* harmony default export */ var TransitionExporterLinkvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    TransitionExporter: TransitionExporter
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterLink.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionExporter/TransitionExporterLink.vue



TransitionExporterLinkvue_type_script_lang_ts.render = TransitionExporterLinkvue_type_template_id_e5b0991c_render

/* harmony default export */ var TransitionExporterLink = (TransitionExporterLinkvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/index.ts
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
//# sourceMappingURL=Transitions.umd.js.map