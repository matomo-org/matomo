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

/***/ "5108":
/***/ (function(module, exports) {



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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=07090b3b

var _hoisted_1 = {
  id: "embedThisWidgetIframe"
};

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
  for: "embedThisWidgetIframeInput"
}, "› Embed Iframe", -1);

var _hoisted_3 = {
  id: "embedThisWidgetIframeInput"
};
var _hoisted_4 = {
  readonly: "true",
  id: "iframeEmbed"
};

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
  for: "embedThisWidgetDirectLink"
}, "› Direct Link", -1);

var _hoisted_6 = {
  id: "embedThisWidgetDirectLink"
};
var _hoisted_7 = {
  readonly: "true",
  id: "directLinkEmbed"
};

var _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" - ");

var _hoisted_9 = ["href"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_select_on_focus = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("select-on-focus");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.widgetIframeHtml), 1)], 512), [[_directive_select_on_focus, {}]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.urlIframe), 1)], 512), [[_directive_select_on_focus, {}]]), _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.urlIframe,
    rel: "noreferrer noopener",
    target: "_blank"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Widgetize_OpenInNewWindow')), 9, _hoisted_9)])])], 64);
}
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=07090b3b

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts


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
  directives: {
    SelectOnFocus: external_CoreHome_["SelectOnFocus"]
  }
}));
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue



WidgetPreviewIframevue_type_script_lang_ts.render = render

/* harmony default export */ var WidgetPreviewIframe = (WidgetPreviewIframevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=53a08bfe

var WidgetPreviewvue_type_template_id_53a08bfe_hoisted_1 = {
  ref: "root"
};
function WidgetPreviewvue_type_template_id_53a08bfe_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetPreviewvue_type_template_id_53a08bfe_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=53a08bfe

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts


var _window = window,
    $ = _window.$,
    widgetsHelper = _window.widgetsHelper;
/* harmony default export */ var WidgetPreviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  created: function created() {
    var _this = this;

    var element = this.$refs.root; // eslint-disable-next-line @typescript-eslint/no-explicit-any

    $(element).widgetPreview({
      onPreviewLoaded: function onPreviewLoaded(widgetUniqueId, loadedWidgetElement) {
        _this.callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement);
      }
    });
  },
  methods: {
    callbackAddExportButtonsUnderWidget: function callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement) {
      var _this2 = this;

      widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, function (widget) {
        var widgetParameters = widget.parameters;
        var exportButtonsElement = $('<div id="exportButtons">');

        var urlIframe = _this2.getEmbedUrl(widgetParameters, 'iframe');

        var widgetIframeHtml = '<div id="widgetIframe"><iframe width="100%" height="350" ' + "src=\"".concat(urlIframe, "\" scrolling=\"yes\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\">") + '</iframe></div>';
        var previewIframe = $('<div>').attr('vue-entry', 'Widgetize.WidgetPreviewIframe').attr('widget-iframe', JSON.stringify(widgetIframeHtml)).attr('url-iframe', JSON.stringify(urlIframe));
        $(exportButtonsElement).append(previewIframe);
        $(loadedWidgetElement).parent().append(exportButtonsElement);
        external_CoreHome_["Matomo"].helper.compileVueEntryComponents(exportButtonsElement);
      });
    },
    getEmbedUrl: function getEmbedUrl(parameters, exportFormat) {
      var finalParams = Object.assign(Object.assign({}, parameters), {}, {
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
      delete finalParams.action;
      delete finalParams.module;
      var _window$location = window.location,
          protocol = _window$location.protocol,
          hostname = _window$location.hostname;
      var port = window.location.port === '' ? '' : ":".concat(window.location.port);
      var path = window.location.pathname;
      var query = external_CoreHome_["MatomoUrl"].stringify(finalParams);
      return "".concat(protocol, "//").concat(hostname).concat(port).concat(path, "?").concat(query);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=custom&index=0&blockType=todo
var WidgetPreviewvue_type_custom_index_0_blockType_todo = __webpack_require__("5108");
var WidgetPreviewvue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(WidgetPreviewvue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue



WidgetPreviewvue_type_script_lang_ts.render = WidgetPreviewvue_type_template_id_53a08bfe_render
/* custom blocks */

if (typeof WidgetPreviewvue_type_custom_index_0_blockType_todo_default.a === 'function') WidgetPreviewvue_type_custom_index_0_blockType_todo_default()(WidgetPreviewvue_type_script_lang_ts)


/* harmony default export */ var WidgetPreview = (WidgetPreviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Widgetize/vue/src/index.ts
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
//# sourceMappingURL=Widgetize.umd.js.map