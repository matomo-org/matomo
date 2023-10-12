(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Widgetize"] = factory(require("CoreHome"), require("vue"));
	else
		root["Widgetize"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2 ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"widgetize\"\n};\nvar _hoisted_2 = [\"innerHTML\"];\nvar _hoisted_3 = [\"innerHTML\"];\nvar _hoisted_4 = [\"innerHTML\"];\n\nvar _hoisted_5 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_6 = [\"textContent\"];\n\nvar _hoisted_7 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_8 = [\"innerHTML\"];\n\nvar _hoisted_9 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_10 = [\"textContent\"];\n\nvar _hoisted_11 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", {\n  class: \"clearfix\"\n}, null, -1\n/* HOISTED */\n);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_EnrichedHeadline = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"EnrichedHeadline\");\n\n  var _component_ContentBlock = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ContentBlock\");\n\n  var _component_WidgetPreview = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"WidgetPreview\");\n\n  var _directive_content_intro = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"content-intro\");\n\n  var _directive_select_on_focus = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"select-on-focus\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_EnrichedHeadline, null, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.title), 1\n      /* TEXT */\n      )];\n    }),\n    _: 1\n    /* STABLE */\n\n  })]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", {\n    innerHTML: _ctx.$sanitize(_ctx.intro)\n  }, null, 8\n  /* PROPS */\n  , _hoisted_2)], 512\n  /* NEED_PATCH */\n  ), [[_directive_content_intro]]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ContentBlock, {\n    \"content-title\": \"Authentication\"\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", {\n        innerHTML: _ctx.$sanitize(_ctx.viewableAnonymously)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_3)];\n    }),\n    _: 1\n    /* STABLE */\n\n  }), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ContentBlock, {\n    \"content-title\": \"Widgetize dashboards\"\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n        innerHTML: _ctx.$sanitize(_ctx.displayInIframe)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_4), _hoisted_5]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"pre\", {\n        textContent: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.dashboardCode)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_6), [[_directive_select_on_focus, {}]]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [_hoisted_7, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n        innerHTML: _ctx.$sanitize(_ctx.displayInIframeAllSites)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_8), _hoisted_9]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"pre\", {\n        textContent: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.allWebsitesDashboardCode)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_10), [[_directive_select_on_focus, {}]])])];\n    }),\n    _: 1\n    /* STABLE */\n\n  }), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ContentBlock, {\n    \"content-title\": _ctx.translate('Widgetize_Reports')\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Widgetize_SelectAReport')), 1\n      /* TEXT */\n      ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_WidgetPreview)]), _hoisted_11])];\n    }),\n    _: 1\n    /* STABLE */\n\n  }, 8\n  /* PROPS */\n  , [\"content-title\"])]);\n}\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  ref: \"root\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, null, 512\n  /* NEED_PATCH */\n  );\n}\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  id: \"embedThisWidgetIframe\"\n};\nvar _hoisted_2 = [\"innerHTML\"];\nvar _hoisted_3 = {\n  id: \"embedThisWidgetIframeInput\"\n};\nvar _hoisted_4 = {\n  readonly: \"true\",\n  id: \"iframeEmbed\"\n};\nvar _hoisted_5 = [\"innerHTML\"];\nvar _hoisted_6 = {\n  id: \"embedThisWidgetDirectLink\"\n};\nvar _hoisted_7 = {\n  readonly: \"true\",\n  id: \"directLinkEmbed\"\n};\n\nvar _hoisted_8 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\" - \");\n\nvar _hoisted_9 = [\"href\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _directive_select_on_focus = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"select-on-focus\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"label\", {\n    for: \"embedThisWidgetIframeInput\",\n    innerHTML: _ctx.$sanitize(_ctx.translate('Widgetize_EmbedIframe'))\n  }, null, 8\n  /* PROPS */\n  , _hoisted_2), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_3, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"pre\", _hoisted_4, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.widgetIframeHtml), 1\n  /* TEXT */\n  )], 512\n  /* NEED_PATCH */\n  ), [[_directive_select_on_focus, {}]])])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"label\", {\n    for: \"embedThisWidgetDirectLink\",\n    innerHTML: _ctx.$sanitize(_ctx.translate('Widgetize_DirectLink'))\n  }, null, 8\n  /* PROPS */\n  , _hoisted_5), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_6, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"pre\", _hoisted_7, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.urlIframe), 1\n  /* TEXT */\n  )], 512\n  /* NEED_PATCH */\n  ), [[_directive_select_on_focus, {}]]), _hoisted_8, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n    href: _ctx.urlIframe,\n    rel: \"noreferrer noopener\",\n    target: \"_blank\"\n  }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Widgetize_OpenInNewWindow')), 9\n  /* TEXT, PROPS */\n  , _hoisted_9)])])], 64\n  /* STABLE_FRAGMENT */\n  );\n}\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _WidgetPreview_WidgetPreview_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../WidgetPreview/WidgetPreview.vue */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue\");\n\n\n\n\nfunction getIframeCode(iframeUrl) {\n  var url = iframeUrl.replace(/\"/g, '&quot;');\n  return \"<iframe src=\\\"\".concat(url, \"\\\" frameborder=\\\"0\\\" marginheight=\\\"0\\\" marginwidth=\\\"0\\\" width=\\\"100%\\\" \") + 'height=\"100%\"></iframe>';\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    title: {\n      type: String,\n      required: true\n    }\n  },\n  components: {\n    EnrichedHeadline: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"EnrichedHeadline\"],\n    ContentBlock: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ContentBlock\"],\n    WidgetPreview: _WidgetPreview_WidgetPreview_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n  },\n  directives: {\n    ContentIntro: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ContentIntro\"],\n    SelectOnFocus: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"SelectOnFocus\"]\n  },\n  data: function data() {\n    var port = window.location.port === '' ? '' : \":\".concat(window.location.port);\n    var path = window.location.pathname;\n    var urlPath = \"\".concat(window.location.protocol, \"//\").concat(window.location.hostname).concat(port).concat(path);\n    return {\n      dashboardUrl: \"\".concat(urlPath, \"?\").concat(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].stringify({\n        module: 'Widgetize',\n        action: 'iframe',\n        moduleToWidgetize: 'Dashboard',\n        actionToWidgetize: 'index',\n        idSite: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].idSite,\n        period: 'week',\n        date: 'yesterday'\n      })),\n      allWebsitesDashboardUrl: \"\".concat(urlPath, \"?\").concat(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].stringify({\n        module: 'Widgetize',\n        action: 'iframe',\n        moduleToWidgetize: 'MultiSites',\n        actionToWidgetize: 'standalone',\n        idSite: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].idSite,\n        period: 'week',\n        date: 'yesterday'\n      }))\n    };\n  },\n  computed: {\n    dashboardCode: function dashboardCode() {\n      return getIframeCode(this.dashboardUrl);\n    },\n    allWebsitesDashboardCode: function allWebsitesDashboardCode() {\n      return getIframeCode(this.allWebsitesDashboardUrl);\n    },\n    intro: function intro() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('Widgetize_Intro', Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"externalLink\"])('https://matomo.org/docs/embed-piwik-report/'), '</a>');\n    },\n    viewableAnonymously: function viewableAnonymously() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('Widgetize_ViewableAnonymously', \"<a\\n          href=\\\"index.php?module=UsersManager\\\"\\n          rel=\\\"noreferrer noopener\\\"\\n          target=\\\"_blank\\\"\\n        >\", '</a>', \"<a\\n          rel=\\\"noreferrer noopener\\\"\\n          target=\\\"_blank\\\"\\n          href=\\\"\".concat(this.linkTo({\n        module: 'UsersManager',\n        action: 'userSecurity'\n      }), \"\\\"\\n        >\"), '</a>');\n    },\n    displayInIframe: function displayInIframe() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('Widgetize_DisplayDashboardInIframe', \"<a\\n          rel=\\\"noreferrer noopener\\\"\\n          target=\\\"_blank\\\"\\n          href=\\\"\".concat(this.dashboardUrl, \"\\\"\\n        >\"), '</a>');\n    },\n    displayInIframeAllSites: function displayInIframeAllSites() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('Widgetize_DisplayDashboardInIframeAllSites', \"<a\\n          rel=\\\"noreferrer noopener\\\"\\n          target=\\\"_blank\\\"\\n          id=\\\"linkAllWebsitesDashboardUrl\\\"\\n          href=\\\"\".concat(this.allWebsitesDashboardUrl, \"\\\"\\n        >\"), '</a>');\n    }\n  },\n  methods: {\n    linkTo: function linkTo(params) {\n      return \"?\".concat(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].stringify(Object.assign(Object.assign({}, CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].urlParsed.value), params)));\n    }\n  }\n}));\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n\nvar _window = window,\n    $ = _window.$,\n    widgetsHelper = _window.widgetsHelper;\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  mounted: function mounted() {\n    var _this = this;\n\n    var element = this.$refs.root; // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n    $(element).widgetPreview({\n      onPreviewLoaded: function onPreviewLoaded(widgetUniqueId, loadedWidgetElement) {\n        _this.callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement);\n      }\n    });\n  },\n  methods: {\n    callbackAddExportButtonsUnderWidget: function callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement) {\n      var _this2 = this;\n\n      widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, function (widget) {\n        var widgetParameters = widget.parameters;\n        var exportButtonsElement = $('<div id=\"exportButtons\">');\n\n        var urlIframe = _this2.getEmbedUrl(widgetParameters, 'iframe');\n\n        var widgetIframeHtml = '<div id=\"widgetIframe\"><iframe width=\"100%\" height=\"350\" ' + \"src=\\\"\".concat(urlIframe, \"\\\" scrolling=\\\"yes\\\" frameborder=\\\"0\\\" marginheight=\\\"0\\\" marginwidth=\\\"0\\\">\") + '</iframe></div>';\n        var previewIframe = $('<div>').attr('vue-entry', 'Widgetize.WidgetPreviewIframe').attr('widget-iframe-html', JSON.stringify(widgetIframeHtml)).attr('url-iframe', JSON.stringify(urlIframe));\n        $(exportButtonsElement).append(previewIframe);\n        $(loadedWidgetElement).parent().append(exportButtonsElement);\n        CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].helper.compileVueEntryComponents(exportButtonsElement);\n      });\n    },\n    getEmbedUrl: function getEmbedUrl(parameters, exportFormat) {\n      var finalParams = Object.assign(Object.assign({}, parameters), {}, {\n        moduleToWidgetize: parameters.module,\n        actionToWidgetize: parameters.action,\n        module: 'Widgetize',\n        action: exportFormat,\n        idSite: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].idSite,\n        period: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].period,\n        date: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].urlParsed.value.date,\n        disableLink: 1,\n        widget: 1\n      });\n      var _window$location = window.location,\n          protocol = _window$location.protocol,\n          hostname = _window$location.hostname;\n      var port = window.location.port === '' ? '' : \":\".concat(window.location.port);\n      var path = window.location.pathname;\n      var query = CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].stringify(finalParams);\n      return \"\".concat(protocol, \"//\").concat(hostname).concat(port).concat(path, \"?\").concat(query);\n    }\n  }\n}));\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    urlIframe: {\n      type: String,\n      required: true\n    },\n    widgetIframeHtml: {\n      type: String,\n      required: true\n    }\n  },\n  inheritAttrs: false,\n  directives: {\n    SelectOnFocus: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"SelectOnFocus\"]\n  }\n}));\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: WidgetPreviewIframe, WidgetPreview, ExportWidget */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Widgetize/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"WidgetPreviewIframe\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"WidgetPreviewIframe\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"WidgetPreview\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"WidgetPreview\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ExportWidget\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"ExportWidget\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Widgetize/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://Widgetize/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue":
/*!*****************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ExportWidget_vue_vue_type_template_id_cb6561e2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ExportWidget.vue?vue&type=template&id=cb6561e2 */ \"./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2\");\n/* harmony import */ var _ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ExportWidget.vue?vue&type=script&lang=ts */ \"./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _ExportWidget_vue_vue_type_template_id_cb6561e2__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts":
/*!*****************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts ***!
  \*****************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ExportWidget.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExportWidget_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2":
/*!***********************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2 ***!
  \***********************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExportWidget_vue_vue_type_template_id_cb6561e2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ExportWidget.vue?vue&type=template&id=cb6561e2 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?vue&type=template&id=cb6561e2\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ExportWidget_vue_vue_type_template_id_cb6561e2__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue":
/*!*******************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _WidgetPreview_vue_vue_type_template_id_98919bce__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./WidgetPreview.vue?vue&type=template&id=98919bce */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce\");\n/* harmony import */ var _WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WidgetPreview.vue?vue&type=script&lang=ts */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _WidgetPreview_vue_vue_type_template_id_98919bce__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts":
/*!*******************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts ***!
  \*******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./WidgetPreview.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreview_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce":
/*!*************************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce ***!
  \*************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreview_vue_vue_type_template_id_98919bce__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./WidgetPreview.vue?vue&type=template&id=98919bce */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?vue&type=template&id=98919bce\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreview_vue_vue_type_template_id_98919bce__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue":
/*!*************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _WidgetPreviewIframe_vue_vue_type_template_id_24673a1d__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./WidgetPreviewIframe.vue?vue&type=template&id=24673a1d */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d\");\n/* harmony import */ var _WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WidgetPreviewIframe.vue?vue&type=script&lang=ts */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _WidgetPreviewIframe_vue_vue_type_template_id_24673a1d__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts":
/*!*************************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts ***!
  \*************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./WidgetPreviewIframe.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreviewIframe_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d":
/*!*******************************************************************************************************!*\
  !*** ./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d ***!
  \*******************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreviewIframe_vue_vue_type_template_id_24673a1d__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./WidgetPreviewIframe.vue?vue&type=template&id=24673a1d */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?vue&type=template&id=24673a1d\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_WidgetPreviewIframe_vue_vue_type_template_id_24673a1d__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue?");

/***/ }),

/***/ "./plugins/Widgetize/vue/src/index.ts":
/*!********************************************!*\
  !*** ./plugins/Widgetize/vue/src/index.ts ***!
  \********************************************/
/*! exports provided: WidgetPreviewIframe, WidgetPreview, ExportWidget */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _WidgetPreview_WidgetPreviewIframe_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./WidgetPreview/WidgetPreviewIframe.vue */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreviewIframe.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"WidgetPreviewIframe\", function() { return _WidgetPreview_WidgetPreviewIframe_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* harmony import */ var _WidgetPreview_WidgetPreview_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./WidgetPreview/WidgetPreview.vue */ \"./plugins/Widgetize/vue/src/WidgetPreview/WidgetPreview.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"WidgetPreview\", function() { return _WidgetPreview_WidgetPreview_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/* harmony import */ var _ExportWidget_ExportWidget_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ExportWidget/ExportWidget.vue */ \"./plugins/Widgetize/vue/src/ExportWidget/ExportWidget.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ExportWidget\", function() { return _ExportWidget_ExportWidget_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n//# sourceURL=webpack://Widgetize/./plugins/Widgetize/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://Widgetize/external_%22CoreHome%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://Widgetize/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});