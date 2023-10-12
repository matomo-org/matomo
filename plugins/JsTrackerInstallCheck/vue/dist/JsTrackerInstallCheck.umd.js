(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", "CorePluginsAdmin", ], factory);
	else if(typeof exports === 'object')
		exports["JsTrackerInstallCheck"] = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else
		root["JsTrackerInstallCheck"] = factory(root["CoreHome"], root["CorePluginsAdmin"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	__webpack_require__.p = "plugins/JsTrackerInstallCheck/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5 ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"jsTrackerInstallCheck\"\n};\nvar _hoisted_2 = {\n  class: \"row testInstallFields\"\n};\nvar _hoisted_3 = {\n  class: \"col s2\"\n};\nvar _hoisted_4 = {\n  class: \"col s10\"\n};\nvar _hoisted_5 = [\"disabled\", \"value\"];\nvar _hoisted_6 = {\n  class: \"system-success success-message\"\n};\n\nvar _hoisted_7 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n  class: \"icon-ok\"\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_8 = {\n  class: \"system-errors test-error\"\n};\n\nvar _hoisted_9 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n  class: \"icon-warning\"\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_10 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\"  \");\n\nvar _hoisted_11 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_Field = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Field\");\n\n  var _component_ActivityIndicator = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ActivityIndicator\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('JsTrackerInstallCheck_TestInstallationDescription')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_2, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_3, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"url\",\n    name: \"baseUrl\",\n    placeholder: \"https://example.com\",\n    modelValue: _ctx.baseUrl,\n    \"onUpdate:modelValue\": _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.baseUrl = $event;\n    }),\n    \"full-width\": true,\n    disabled: _ctx.isTesting\n  }, null, 8\n  /* PROPS */\n  , [\"modelValue\", \"disabled\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_4, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n    type: \"button\",\n    class: \"btn testInstallBtn\",\n    onClick: _cache[1] || (_cache[1] = function () {\n      return _ctx.initiateTrackerTest && _ctx.initiateTrackerTest.apply(_ctx, arguments);\n    }),\n    disabled: !_ctx.baseUrl || _ctx.isTesting,\n    value: _ctx.translate('JsTrackerInstallCheck_TestInstallationBtnText')\n  }, null, 8\n  /* PROPS */\n  , _hoisted_5)])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ActivityIndicator, {\n    loading: _ctx.isTesting,\n    loadingMessage: _ctx.translate('General_Testing')\n  }, null, 8\n  /* PROPS */\n  , [\"loading\", \"loadingMessage\"]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_6, [_hoisted_7, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\" \" + Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage')), 1\n  /* TEXT */\n  )], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.isTestSuccess]]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_8, [_hoisted_9, _hoisted_10, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n    innerHTML: _ctx.$sanitize(_ctx.getTestFailureMessage)\n  }, null, 8\n  /* PROPS */\n  , _hoisted_11)], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.isTestComplete && !_ctx.isTestSuccess]])])], 64\n  /* STABLE_FRAGMENT */\n  );\n}\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! CorePluginsAdmin */ \"CorePluginsAdmin\");\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\nvar MAX_NUM_API_CALLS = 10;\nvar TIME_BETWEEN_API_CALLS = 1000;\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  components: {\n    Field: CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__[\"Field\"],\n    ActivityIndicator: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ActivityIndicator\"]\n  },\n  data: function data() {\n    return {\n      checkNonce: '',\n      isTesting: false,\n      isTestComplete: false,\n      isTestSuccess: false,\n      testTimeoutCount: 0,\n      baseUrl: ''\n    };\n  },\n  props: {\n    site: {\n      type: Object,\n      required: true\n    },\n    isWordpress: {\n      type: Boolean,\n      required: false,\n      default: false\n    }\n  },\n  created: function created() {\n    this.checkWhetherSuccessWasRecorded();\n  },\n  watch: {\n    site: function site() {\n      this.onSiteChange();\n    }\n  },\n  methods: {\n    onSiteChange: function onSiteChange() {\n      this.checkNonce = '';\n      this.isTesting = false;\n      this.isTestComplete = false;\n      this.isTestSuccess = false;\n      this.testTimeoutCount = 0;\n      this.checkWhetherSuccessWasRecorded();\n    },\n    initiateTrackerTest: function initiateTrackerTest() {\n      var _this = this;\n\n      this.isTesting = true;\n      this.isTestComplete = false;\n      this.isTestSuccess = false;\n      this.testTimeoutCount = 0;\n      var siteRef = this.site;\n      var postParams = {\n        idSite: siteRef.id,\n        url: ''\n      };\n\n      if (this.baseUrl) {\n        postParams.url = this.baseUrl;\n      }\n\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].post({\n        module: 'API',\n        method: 'JsTrackerInstallCheck.initiateJsTrackerInstallTest'\n      }, postParams).then(function (response) {\n        var isSuccess = response && response.url && response.nonce;\n\n        if (isSuccess) {\n          _this.checkNonce = response.nonce;\n          var windowRef = window.open(response.url);\n\n          _this.setCheckInTime();\n\n          setTimeout(function () {\n            if (windowRef && !windowRef.closed) {\n              windowRef.close(); // Set the timeout to the max since we've already waited too long\n\n              _this.testTimeoutCount = MAX_NUM_API_CALLS;\n            }\n          }, MAX_NUM_API_CALLS * TIME_BETWEEN_API_CALLS);\n        }\n      }).catch(function () {\n        _this.isTesting = false;\n      });\n    },\n    setCheckInTime: function setCheckInTime() {\n      setTimeout(this.checkWhetherSuccessWasRecorded, TIME_BETWEEN_API_CALLS);\n    },\n    checkWhetherSuccessWasRecorded: function checkWhetherSuccessWasRecorded() {\n      var _this2 = this;\n\n      var siteRef = this.site;\n      var postParams = {\n        idSite: siteRef.id,\n        nonce: ''\n      };\n\n      if (this.checkNonce) {\n        postParams.nonce = this.checkNonce;\n      }\n\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].post({\n        module: 'API',\n        method: 'JsTrackerInstallCheck.wasJsTrackerInstallTestSuccessful'\n      }, postParams).then(function (response) {\n        if (response && response.mainUrl && !_this2.baseUrl) {\n          _this2.baseUrl = response.mainUrl;\n        }\n\n        _this2.isTestSuccess = response && response.isSuccess; // If the test isn't successful but hasn't exceeded the timeout count, wait and check again\n\n        if (_this2.checkNonce && !_this2.isTestSuccess && _this2.testTimeoutCount < MAX_NUM_API_CALLS) {\n          _this2.testTimeoutCount += 1;\n\n          _this2.setCheckInTime();\n\n          return;\n        }\n\n        _this2.isTestComplete = !!_this2.checkNonce;\n        _this2.isTesting = false;\n      }).catch(function () {\n        _this2.isTesting = false;\n      });\n    }\n  },\n  computed: {\n    getTestFailureMessage: function getTestFailureMessage() {\n      var learnMoreLink = Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"externalLink\"])('https://matomo.org/faq/troubleshooting/faq_58/');\n      var closingTag = '</a>';\n\n      if (!this.isWordpress) {\n        return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage', learnMoreLink, closingTag);\n      }\n\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress', '<a target=\"_blank\" rel=\"noreferrer noopener\" href=\"https://wordpress.org/plugins/wp-piwik/\">WP-Matomo Integration (WP-Piwik)</a>', learnMoreLink, closingTag);\n    }\n  }\n}));\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: JsTrackerInstallCheck */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/JsTrackerInstallCheck/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"JsTrackerInstallCheck\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"JsTrackerInstallCheck\"]; });\n\n\n\n\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue":
/*!***********************************************************************************************!*\
  !*** ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue ***!
  \***********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _JsTrackerInstallCheck_vue_vue_type_template_id_1e5f6eb5__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5 */ \"./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5\");\n/* harmony import */ var _JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./JsTrackerInstallCheck.vue?vue&type=script&lang=ts */ \"./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _JsTrackerInstallCheck_vue_vue_type_template_id_1e5f6eb5__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?");

/***/ }),

/***/ "./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts":
/*!***********************************************************************************************************************!*\
  !*** ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts ***!
  \***********************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./JsTrackerInstallCheck.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_JsTrackerInstallCheck_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?");

/***/ }),

/***/ "./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5":
/*!*****************************************************************************************************************************!*\
  !*** ./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5 ***!
  \*****************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_JsTrackerInstallCheck_vue_vue_type_template_id_1e5f6eb5__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?vue&type=template&id=1e5f6eb5\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_JsTrackerInstallCheck_vue_vue_type_template_id_1e5f6eb5__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue?");

/***/ }),

/***/ "./plugins/JsTrackerInstallCheck/vue/src/index.ts":
/*!********************************************************!*\
  !*** ./plugins/JsTrackerInstallCheck/vue/src/index.ts ***!
  \********************************************************/
/*! exports provided: JsTrackerInstallCheck */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _JsTrackerInstallCheck_JsTrackerInstallCheck_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./JsTrackerInstallCheck/JsTrackerInstallCheck.vue */ \"./plugins/JsTrackerInstallCheck/vue/src/JsTrackerInstallCheck/JsTrackerInstallCheck.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"JsTrackerInstallCheck\", function() { return _JsTrackerInstallCheck_JsTrackerInstallCheck_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n//# sourceURL=webpack://JsTrackerInstallCheck/./plugins/JsTrackerInstallCheck/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://JsTrackerInstallCheck/external_%22CoreHome%22?");

/***/ }),

/***/ "CorePluginsAdmin":
/*!***********************************!*\
  !*** external "CorePluginsAdmin" ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__;\n\n//# sourceURL=webpack://JsTrackerInstallCheck/external_%22CorePluginsAdmin%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://JsTrackerInstallCheck/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});