(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Widgetize"] = factory(require("CoreHome"), require("vue"));
	else
		root["Widgetize"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
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
/******/ 	__webpack_require__.p = "plugins/Widgetize/vue/dist/";
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

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "WidgetPreviewIframe", function() { return /* reexport */ WidgetPreviewIframe; });
__webpack_require__.d(__webpack_exports__, "WidgetPreview", function() { return /* reexport */ WidgetPreview; });
__webpack_require__.d(__webpack_exports__, "ExportWidget", function() { return /* reexport */ ExportWidget; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=9ca6ab74

const _hoisted_1 = {
  id: "embedThisWidgetIframe"
};
const _hoisted_2 = ["innerHTML"];
const _hoisted_3 = {
  id: "embedThisWidgetIframeInput"
};
const _hoisted_4 = {
  readonly: "true",
  id: "iframeEmbed"
};
const _hoisted_5 = ["innerHTML"];
const _hoisted_6 = {
  id: "embedThisWidgetDirectLink"
};
const _hoisted_7 = {
  readonly: "true",
  id: "directLinkEmbed"
};
const _hoisted_8 = ["href"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: "embedThisWidgetIframeInput",
    innerHTML: _ctx.$sanitize(_ctx.translate('Widgetize_EmbedIframe'))
  }, null, 8, _hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("pre", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.widgetIframeHtml), 1)])), [[_directive_select_on_focus, {}]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: "embedThisWidgetDirectLink",
    innerHTML: _ctx.$sanitize(_ctx.translate('Widgetize_DirectLink'))
  }, null, 8, _hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("pre", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.urlIframe), 1)])), [[_directive_select_on_focus, {}]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.urlIframe,
    rel: "noreferrer noopener",
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Widgetize_OpenInNewWindow')), 9, _hoisted_8)])])], 64);
}
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=9ca6ab74

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts


/* harmony default export */ var WidgetPreviewIframevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    urlIframe: {
      type: String,
      required: true
    },
    widgetIframeHtml: {
      type: String,
      required: true
    }
  },
  inheritAttrs: false,
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  }
}));
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue



WidgetPreviewIframevue_type_script_lang_ts.render = render

/* harmony default export */ var WidgetPreviewIframe = (WidgetPreviewIframevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=1d314ae4

const WidgetPreviewvue_type_template_id_1d314ae4_hoisted_1 = {
  ref: "root"
};
function WidgetPreviewvue_type_template_id_1d314ae4_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetPreviewvue_type_template_id_1d314ae4_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=1d314ae4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }


const {
  $,
  widgetsHelper
} = window;
/* harmony default export */ var WidgetPreviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  mounted() {
    const element = this.$refs.root;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    $(element).widgetPreview({
      onPreviewLoaded: (widgetUniqueId, loadedWidgetElement) => {
        this.callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement);
      }
    });
  },
  methods: {
    callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement) {
      widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, widget => {
        const widgetParameters = widget.parameters;
        const exportButtonsElement = $('<div id="exportButtons">');
        const urlIframe = this.getEmbedUrl(widgetParameters, 'iframe');
        const widgetIframeHtml = '<div id="widgetIframe"><iframe width="100%" height="350" ' + `src="${urlIframe}" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0">` + '</iframe></div>';
        const previewIframe = $('<div>').attr('vue-entry', 'Widgetize.WidgetPreviewIframe').attr('widget-iframe-html', JSON.stringify(widgetIframeHtml)).attr('url-iframe', JSON.stringify(urlIframe));
        $(exportButtonsElement).append(previewIframe);
        $(loadedWidgetElement).parent().append(exportButtonsElement);
        external_CoreHome_["Matomo"].helper.compileVueEntryComponents(exportButtonsElement);
      });
    },
    getEmbedUrl(parameters, exportFormat) {
      const finalParams = _extends(_extends({}, parameters), {}, {
        moduleToWidgetize: parameters.module,
        actionToWidgetize: parameters.action,
        module: 'Widgetize',
        action: exportFormat,
        idSite: external_CoreHome_["Matomo"].idSite,
        period: external_CoreHome_["Matomo"].period,
        date: external_CoreHome_["MatomoUrl"].urlParsed.value.date,
        disableLink: 1,
        widget: 1
      });
      const {
        protocol,
        hostname
      } = window.location;
      const port = window.location.port === '' ? '' : `:${window.location.port}`;
      const path = window.location.pathname;
      const query = external_CoreHome_["MatomoUrl"].stringify(finalParams);
      return `${protocol}//${hostname}${port}${path}?${query}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue



WidgetPreviewvue_type_script_lang_ts.render = WidgetPreviewvue_type_template_id_1d314ae4_render

/* harmony default export */ var WidgetPreview = (WidgetPreviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=00de7386

const ExportWidgetvue_type_template_id_00de7386_hoisted_1 = {
  class: "widgetize"
};
const ExportWidgetvue_type_template_id_00de7386_hoisted_2 = ["innerHTML"];
const ExportWidgetvue_type_template_id_00de7386_hoisted_3 = ["innerHTML"];
const ExportWidgetvue_type_template_id_00de7386_hoisted_4 = ["innerHTML"];
const ExportWidgetvue_type_template_id_00de7386_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ExportWidgetvue_type_template_id_00de7386_hoisted_6 = ["textContent"];
const ExportWidgetvue_type_template_id_00de7386_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const ExportWidgetvue_type_template_id_00de7386_hoisted_8 = ["innerHTML"];
const _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_10 = ["textContent"];
const _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", {
  class: "clearfix"
}, null, -1);
function ExportWidgetvue_type_template_id_00de7386_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_WidgetPreview = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("WidgetPreview");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  const _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExportWidgetvue_type_template_id_00de7386_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1)]),
    _: 1
  })]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.intro)
  }, null, 8, ExportWidgetvue_type_template_id_00de7386_hoisted_2)])), [[_directive_content_intro]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": "Authentication"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.viewableAnonymously)
    }, null, 8, ExportWidgetvue_type_template_id_00de7386_hoisted_3)]),
    _: 1
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": "Widgetize dashboards"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.displayInIframe)
    }, null, 8, ExportWidgetvue_type_template_id_00de7386_hoisted_4), ExportWidgetvue_type_template_id_00de7386_hoisted_5]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.dashboardCode)
    }, null, 8, ExportWidgetvue_type_template_id_00de7386_hoisted_6), [[_directive_select_on_focus, {}]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [ExportWidgetvue_type_template_id_00de7386_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.displayInIframeAllSites)
    }, null, 8, ExportWidgetvue_type_template_id_00de7386_hoisted_8), _hoisted_9]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", {
      textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.allWebsitesDashboardCode)
    }, null, 8, _hoisted_10), [[_directive_select_on_focus, {}]])])]),
    _: 1
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Widgetize_Reports')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Widgetize_SelectAReport')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_WidgetPreview)]), _hoisted_11])]),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=00de7386

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts
function ExportWidgetvue_type_script_lang_ts_extends() { ExportWidgetvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return ExportWidgetvue_type_script_lang_ts_extends.apply(this, arguments); }



function getIframeCode(iframeUrl) {
  const url = iframeUrl.replace(/"/g, '&quot;');
  return `<iframe src="${url}" frameborder="0" marginheight="0" marginwidth="0" width="100%" ` + 'height="100%"></iframe>';
}
/* harmony default export */ var ExportWidgetvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    }
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    WidgetPreview: WidgetPreview
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  },
  data() {
    const port = window.location.port === '' ? '' : `:${window.location.port}`;
    const path = window.location.pathname;
    const urlPath = `${window.location.protocol}//${window.location.hostname}${port}${path}`;
    return {
      dashboardUrl: `${urlPath}?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'Widgetize',
        action: 'iframe',
        moduleToWidgetize: 'Dashboard',
        actionToWidgetize: 'index',
        idSite: external_CoreHome_["Matomo"].idSite,
        period: 'week',
        date: 'yesterday'
      })}`,
      allWebsitesDashboardUrl: `${urlPath}?${external_CoreHome_["MatomoUrl"].stringify({
        module: 'Widgetize',
        action: 'iframe',
        moduleToWidgetize: 'MultiSites',
        actionToWidgetize: 'standalone',
        idSite: external_CoreHome_["Matomo"].idSite,
        period: 'week',
        date: 'yesterday'
      })}`
    };
  },
  computed: {
    dashboardCode() {
      return getIframeCode(this.dashboardUrl);
    },
    allWebsitesDashboardCode() {
      return getIframeCode(this.allWebsitesDashboardUrl);
    },
    intro() {
      return Object(external_CoreHome_["translate"])('Widgetize_Intro', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/embed-piwik-report/'), '</a>');
    },
    viewableAnonymously() {
      return Object(external_CoreHome_["translate"])('Widgetize_ViewableAnonymously', `<a
          href="index.php?module=UsersManager"
          rel="noreferrer noopener"
          target="_blank"
        >`, '</a>', `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.linkTo({
        module: 'UsersManager',
        action: 'userSecurity'
      })}"
        >`, '</a>');
    },
    displayInIframe() {
      return Object(external_CoreHome_["translate"])('Widgetize_DisplayDashboardInIframe', `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.dashboardUrl}"
        >`, '</a>');
    },
    displayInIframeAllSites() {
      return Object(external_CoreHome_["translate"])('Widgetize_DisplayDashboardInIframeAllSites', `<a
          rel="noreferrer noopener"
          target="_blank"
          id="linkAllWebsitesDashboardUrl"
          href="${this.allWebsitesDashboardUrl}"
        >`, '</a>');
    }
  },
  methods: {
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(ExportWidgetvue_type_script_lang_ts_extends(ExportWidgetvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue



ExportWidgetvue_type_script_lang_ts.render = ExportWidgetvue_type_template_id_00de7386_render

/* harmony default export */ var ExportWidget = (ExportWidgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/index.ts
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
//# sourceMappingURL=Widgetize.umd.js.map