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


const actionType = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
const actionName = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])('');
const onDataChanged = params => {
  actionType.value = params.actionType;
  actionName.value = params.actionName;
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
      exportUrlParams.actionType = actionType.value;
      exportUrlParams.actionName = actionName.value;
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
      Piwik_Popover.setTitle(`${actionName.value} ${Object(external_CoreHome_["translate"])('Transitions_Transitions')}`);
      Piwik_Popover.setContent(mountPoint);
      Piwik_Popover.onClose(() => {
        app.unmount();
      });
    });
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=template&id=5af29651

const TransitionSwitchervue_type_template_id_5af29651_hoisted_1 = {
  class: "row"
};
const TransitionSwitchervue_type_template_id_5af29651_hoisted_2 = {
  class: "col s12 m3"
};
const TransitionSwitchervue_type_template_id_5af29651_hoisted_3 = {
  name: "actionType"
};
const TransitionSwitchervue_type_template_id_5af29651_hoisted_4 = {
  class: "col s12 m9"
};
const TransitionSwitchervue_type_template_id_5af29651_hoisted_5 = {
  name: "actionName"
};
const TransitionSwitchervue_type_template_id_5af29651_hoisted_6 = {
  class: "loadingPiwik",
  style: {
    "display": "none"
  },
  id: "transitions_inline_loading"
};
const _hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  src: "plugins/Morpheus/images/loading-blue.gif",
  alt: ""
}, null, -1);
const _hoisted_8 = {
  class: "popoverContainer"
};
const _hoisted_9 = {
  id: "Transitions_Error_Container"
};
const _hoisted_10 = {
  class: "dataTableWrapper"
};
const _hoisted_11 = {
  class: "dataTableFeatures"
};
const _hoisted_12 = {
  class: "dataTableFooterNavigation"
};
const _hoisted_13 = {
  class: "dataTableControls"
};
const _hoisted_14 = {
  class: "row"
};
const _hoisted_15 = {
  class: "dataTableAction"
};
const _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export"
}, null, -1);
const _hoisted_17 = [_hoisted_16];
const _hoisted_18 = {
  class: "alert alert-info"
};
const _hoisted_19 = ["innerHTML"];
function TransitionSwitchervue_type_template_id_5af29651_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _directive_transition_exporter = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("transition-exporter");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      widgetBody: _ctx.isWidget
    }),
    id: "transitions_report"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "actionType",
    modelValue: _ctx.actionType,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.actionType = $event),
    title: _ctx.translate('Actions_ActionType'),
    "full-width": true,
    options: _ctx.actionTypeOptions
  }, null, 8, ["modelValue", "title", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
  }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TransitionSwitchervue_type_template_id_5af29651_hoisted_6, [_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading && _ctx.isEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isLoading]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", _hoisted_15, _hoisted_17)), [[_directive_transition_exporter]])])])])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Transitions_AvailableInOtherReports')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_PageUrls')) + ", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPageTitles')) + ", " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPagesEntry')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_And')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Actions_SubmenuPagesExit')) + ". ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.availableInOtherReports2)
  }, null, 8, _hoisted_19)])], 2);
}
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=template&id=5af29651

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=script&lang=ts




/* harmony default export */ var TransitionSwitchervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isWidget: Boolean
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
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
    let transitionsInstance = null;
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
    const createTransitionsInstance = (type, actionName) => {
      if (!transitionsInstance) {
        transitionsInstance = new window.Piwik_Transitions(type, actionName, null, '');
      } else {
        transitionsInstance.reset(type, actionName, '');
      }
    };
    const getTransitionsInstance = () => transitionsInstance;
    return {
      transitionsUrl,
      createTransitionsInstance,
      getTransitionsInstance
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
    actionName(newValue) {
      if (newValue === null || newValue === this.noDataKey) {
        return;
      }
      const type = this.isUrlReport ? 'url' : 'title';
      this.createTransitionsInstance(type, newValue);
      this.getTransitionsInstance().showPopover(true);
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
    availableInOtherReports2() {
      return Object(external_CoreHome_["translate"])('Transitions_AvailableInOtherReports2', '<span class="icon-transition"></span>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Transitions/vue/src/TransitionSwitcher/TransitionSwitcher.vue



TransitionSwitchervue_type_script_lang_ts.render = TransitionSwitchervue_type_template_id_5af29651_render

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