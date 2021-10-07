(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("vue"));
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CoreHome"] = factory(require("vue"));
	else
		root["CoreHome"] = factory(root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189 ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"loadingPiwik\"\n};\n\nconst _hoisted_2 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"img\", {\n  src: \"plugins/Morpheus/images/loading-blue.gif\",\n  alt: \"\"\n}, null, -1\n/* HOISTED */\n);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])((Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [_hoisted_2, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.loadingMessage), 1\n  /* TEXT */\n  )], 512\n  /* NEED_PATCH */\n  )), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.loading]]);\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41 ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n    class: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"normalizeClass\"])([\"alert\", {\n      [`alert-${_ctx.severity}`]: true\n    }])\n  }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderSlot\"])(_ctx.$slots, \"default\")], 2\n  /* CLASS */\n  );\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    loading: {\n      type: Boolean,\n      required: true,\n      default: false\n    },\n    loadingMessage: {\n      type: String,\n      required: false,\n      default: Object(_translate__WEBPACK_IMPORTED_MODULE_1__[\"default\"])('General_LoadingData')\n    }\n  }\n}));\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    severity: {\n      type: String,\n      required: true\n    }\n  }\n}));\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: activityIndicatorAdapter, ActivityIndicator, translate, alertAdapter, Periods, AjaxHelper, PiwikUrl */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/CoreHome/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"activityIndicatorAdapter\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"activityIndicatorAdapter\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ActivityIndicator\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"ActivityIndicator\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"translate\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"translate\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"alertAdapter\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"alertAdapter\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Periods\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"Periods\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"AjaxHelper\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"PiwikUrl\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"PiwikUrl\"]; });\n\n\n\n\n\n//# sourceURL=webpack://CoreHome/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://CoreHome/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts":
/*!*********************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts ***!
  \*********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return activityIndicatorAdapter; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityIndicator.vue */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue\");\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nfunction activityIndicatorAdapter() {\n  return {\n    restrict: 'A',\n    scope: {\n      loading: '<',\n      loadingMessage: '<'\n    },\n    template: '',\n    link: function activityIndicatorAdapterLink(scope, element) {\n      const app = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createApp\"])({\n        template: '<activity-indicator :loading=\"loading\" :loadingMessage=\"loadingMessage\"/>',\n\n        data() {\n          return {\n            loading: scope.loading,\n            loadingMessage: scope.loadingMessage\n          };\n        }\n\n      });\n      app.component('activity-indicator', _ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n      const vm = app.mount(element[0]);\n      scope.$watch('loading', newValue => {\n        vm.loading = newValue;\n      });\n      scope.$watch('loadingMessage', newValue => {\n        vm.loadingMessage = newValue || Object(_translate__WEBPACK_IMPORTED_MODULE_2__[\"default\"])('General_LoadingData');\n      });\n    }\n  };\n}\nactivityIndicatorAdapter.$inject = [];\nangular.module('piwikApp').directive('piwikActivityIndicator', activityIndicatorAdapter);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue":
/*!**************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ActivityIndicator_vue_vue_type_template_id_44c7b189__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActivityIndicator.vue?vue&type=template&id=44c7b189 */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189\");\n/* harmony import */ var _ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityIndicator.vue?vue&type=script&lang=ts */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _ActivityIndicator_vue_vue_type_template_id_44c7b189__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts":
/*!**************************************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts ***!
  \**************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ActivityIndicator.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ActivityIndicator_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189":
/*!********************************************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189 ***!
  \********************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ActivityIndicator_vue_vue_type_template_id_44c7b189__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ActivityIndicator.vue?vue&type=template&id=44c7b189 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?vue&type=template&id=44c7b189\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ActivityIndicator_vue_vue_type_template_id_44c7b189__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts":
/*!*******************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts ***!
  \*******************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AjaxHelper__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AjaxHelper */ \"./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts\");\n\nwindow.ajaxHelper = _AjaxHelper__WEBPACK_IMPORTED_MODULE_0__[\"default\"];\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts":
/*!***********************************************************!*\
  !*** ./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return AjaxHelper; });\n/* harmony import */ var _PiwikUrl_PiwikUrl__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../PiwikUrl/PiwikUrl */ \"./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\nwindow.globalAjaxQueue = [];\nwindow.globalAjaxQueue.active = 0;\n\nwindow.globalAjaxQueue.clean = function globalAjaxQueueClean() {\n  const filtered = this.filter(x => !x || x.readyState === 4);\n  this.splice(0, this.length);\n  Array.prototype.push(...filtered);\n};\n\nwindow.globalAjaxQueue.push = function globalAjaxQueuePush(...args) {\n  this.active += args.length; // cleanup ajax queue\n\n  this.clean(); // call original array push\n\n  return Array.prototype.push.call(this, ...args);\n};\n\nwindow.globalAjaxQueue.abort = function globalAjaxQueueAbort() {\n  // abort all queued requests if possible\n  this.forEach(x => x && x.abort && x.abort()); // remove all elements from array\n\n  this.splice(0, this.length);\n  this.active = 0;\n};\n/**\n * Global ajax helper to handle requests within piwik\n */\n\n\nclass AjaxHelper {\n  /**\n   * Format of response\n   */\n\n  /**\n   * A timeout for the request which will override any global timeout\n   */\n\n  /**\n   * Callback function to be executed on success\n   */\n\n  /**\n   * Use this.callback if an error is returned\n   */\n\n  /**\n   * Callback function to be executed on error\n   */\n\n  /**\n   * Callback function to be executed on complete (after error or success)\n   */\n\n  /**\n   * Params to be passed as GET params\n   * @see ajaxHelper.mixinDefaultGetParams\n   */\n\n  /**\n   * Base URL used in the AJAX request. Can be set by setUrl.\n   *\n   * It is set to '?' rather than 'index.php?' to increase chances that it works\n   * including for users who have an automatic 301 redirection from index.php? to ?\n   * POST values are missing when there is such 301 redirection. So by by-passing\n   * this 301 redirection, we avoid this issue.\n   *\n   * @see ajaxHelper.setUrl\n   */\n\n  /**\n   * Params to be passed as GET params\n   * @see ajaxHelper.mixinDefaultPostParams\n   */\n\n  /**\n   * Element to be displayed while loading\n   */\n\n  /**\n   * Element to be displayed on error\n   */\n\n  /**\n   * Handle for current request\n   */\n  constructor() {\n    _defineProperty(this, \"format\", 'json');\n\n    _defineProperty(this, \"timeout\", null);\n\n    _defineProperty(this, \"callback\", null);\n\n    _defineProperty(this, \"useRegularCallbackInCaseOfError\", false);\n\n    _defineProperty(this, \"errorCallback\", void 0);\n\n    _defineProperty(this, \"withToken\", false);\n\n    _defineProperty(this, \"completeCallback\", void 0);\n\n    _defineProperty(this, \"getParams\", {});\n\n    _defineProperty(this, \"getUrl\", '?');\n\n    _defineProperty(this, \"postParams\", {});\n\n    _defineProperty(this, \"loadingElement\", null);\n\n    _defineProperty(this, \"errorElement\", '#ajaxError');\n\n    _defineProperty(this, \"requestHandle\", null);\n\n    _defineProperty(this, \"defaultParams\", ['idSite', 'period', 'date', 'segment']);\n\n    this.errorCallback = this.defaultErrorCallback.bind(this);\n  }\n  /**\n   * Adds params to the request.\n   * If params are given more then once, the latest given value is used for the request\n   *\n   * @param  params\n   * @param  type  type of given parameters (POST or GET)\n   * @return {void}\n   */\n\n\n  addParams(params, type) {\n    if (typeof params === 'string') {\n      // TODO: add global types for broadcast (multiple uses below)\n      params = window['broadcast'].getValuesFromUrl(params); // eslint-disable-line\n    }\n\n    const arrayParams = ['compareSegments', 'comparePeriods', 'compareDates'];\n    Object.keys(params).forEach(key => {\n      const value = params[key];\n\n      if (arrayParams.indexOf(key) !== -1 && !value) {\n        return;\n      }\n\n      if (type.toLowerCase() === 'get') {\n        this.getParams[key] = value;\n      } else if (type.toLowerCase() === 'post') {\n        this.postParams[key] = value;\n      }\n    });\n  }\n\n  withTokenInUrl() {\n    this.withToken = true;\n  }\n  /**\n   * Sets the base URL to use in the AJAX request.\n   */\n\n\n  setUrl(url) {\n    this.addParams(broadcast.getValuesFromUrl(url), 'GET');\n  }\n  /**\n   * Gets this helper instance ready to send a bulk request. Each argument to this\n   * function is a single request to use.\n   */\n\n\n  setBulkRequests(...urls) {\n    const urlsProcessed = urls.map(u => $.param(u));\n    this.addParams({\n      module: 'API',\n      method: 'API.getBulkRequest',\n      urls: urlsProcessed,\n      format: 'json'\n    }, 'post');\n  }\n  /**\n   * Set a timeout (in milliseconds) for the request. This will override any global timeout.\n   *\n   * @param timeout  Timeout in milliseconds\n   */\n\n\n  setTimeout(timeout) {\n    this.timeout = timeout;\n  }\n  /**\n   * Sets the callback called after the request finishes\n   *\n   * @param callback  Callback function\n   */\n\n\n  setCallback(callback) {\n    this.callback = callback;\n  }\n  /**\n   * Set that the callback passed to setCallback() should be used if an application error (i.e. an\n   * Exception in PHP) is returned.\n   */\n\n\n  useCallbackInCaseOfError() {\n    this.useRegularCallbackInCaseOfError = true;\n  }\n  /**\n   * Set callback to redirect on success handler\n   * &update=1(+x) will be appended to the current url\n   *\n   * @param [params] to modify in redirect url\n   * @return {void}\n   */\n\n\n  redirectOnSuccess(params) {\n    this.setCallback(() => {\n      // TODO: piwik helper\n      window['piwikHelper'].redirect(params); // eslint-disable-line\n    });\n  }\n  /**\n   * Sets the callback called in case of an error within the request\n   */\n\n\n  setErrorCallback(callback) {\n    this.errorCallback = callback;\n  }\n  /**\n   * Sets the complete callback which is called after an error or success callback.\n   */\n\n\n  setCompleteCallback(callback) {\n    this.completeCallback = callback;\n  }\n  /**\n   * error callback to use by default\n   */\n\n\n  defaultErrorCallback(deferred, status) {\n    // do not display error message if request was aborted\n    if (status === 'abort') {\n      return;\n    }\n\n    const loadingError = $('#loadingError');\n\n    if (Piwik_Popover.isOpen() && deferred && deferred.status === 500) {\n      if (deferred && deferred.status === 500) {\n        $(document.body).html(piwikHelper.escape(deferred.responseText));\n      }\n    } else {\n      loadingError.show();\n    }\n  }\n  /**\n   * Sets the response format for the request\n   *\n   * @param format  response format (e.g. json, html, ...)\n   */\n\n\n  setFormat(format) {\n    this.format = format;\n  }\n  /**\n   * Set the div element to show while request is loading\n   *\n   * @param [element]  selector for the loading element\n   */\n\n\n  setLoadingElement(element) {\n    this.loadingElement = element || '#ajaxLoadingDiv';\n  }\n  /**\n   * Set the div element to show on error\n   *\n   * @param element  selector for the error element\n   */\n\n\n  setErrorElement(element) {\n    if (!element) {\n      return;\n    }\n\n    this.errorElement = element;\n  }\n  /**\n   * Detect whether are allowed to use the given default parameter or not\n   */\n\n\n  useGETDefaultParameter(parameter) {\n    if (parameter && this.defaultParams) {\n      for (let i = 0; i < this.defaultParams.length; i += 1) {\n        if (this.defaultParams[i] === parameter) {\n          return true;\n        }\n      }\n    }\n\n    return false;\n  }\n  /**\n   * Removes a default parameter that is usually send automatically along the request.\n   *\n   * @param parameter  A name such as \"period\", \"date\", \"segment\".\n   */\n\n\n  removeDefaultParameter(parameter) {\n    if (parameter && this.defaultParams) {\n      for (let i = 0; i < this.defaultParams.length; i += 1) {\n        if (this.defaultParams[i] === parameter) {\n          this.defaultParams.splice(i, 1);\n        }\n      }\n    }\n  }\n  /**\n   * Send the request\n   */\n\n\n  send() {\n    if ($(this.errorElement).length) {\n      $(this.errorElement).hide();\n    }\n\n    if (this.loadingElement) {\n      $(this.loadingElement).fadeIn();\n    }\n\n    this.requestHandle = this.buildAjaxCall();\n    globalAjaxQueue.push(this.requestHandle);\n  }\n  /**\n   * Aborts the current request if it is (still) running\n   */\n\n\n  abort() {\n    if (this.requestHandle && typeof this.requestHandle.abort === 'function') {\n      this.requestHandle.abort();\n      this.requestHandle = null;\n    }\n  }\n  /**\n   * Builds and sends the ajax requests\n   */\n\n\n  buildAjaxCall() {\n    const self = this;\n    const parameters = this.mixinDefaultGetParams(this.getParams);\n    let url = this.getUrl;\n\n    if (url[url.length - 1] !== '?') {\n      url += '&';\n    } // we took care of encoding &segment properly already, so we don't use $.param for it ($.param\n    // URL encodes the values)\n\n\n    if (parameters.segment) {\n      url = `${url}segment=${parameters.segment}&`;\n      delete parameters.segment;\n    }\n\n    if (parameters.date) {\n      url = `${url}date=${decodeURIComponent(parameters.date.toString())}&`;\n      delete parameters.date;\n    }\n\n    url += $.param(parameters);\n    const ajaxCall = {\n      type: 'POST',\n      async: true,\n      url,\n      dataType: this.format || 'json',\n      complete: this.completeCallback,\n      error: function errorCallback(...params) {\n        globalAjaxQueue.active -= 1;\n\n        if (self.errorCallback) {\n          self.errorCallback.apply(this, params);\n        }\n      },\n      success: (response, status, request) => {\n        if (this.loadingElement) {\n          $(this.loadingElement).hide();\n        }\n\n        if (response && response.result === 'error' && !this.useRegularCallbackInCaseOfError) {\n          let placeAt = null;\n          let type = 'toast';\n\n          if ($(this.errorElement).length && response.message) {\n            $(this.errorElement).show();\n            placeAt = this.errorElement;\n            type = null;\n          }\n\n          if (response.message) {\n            const UI = window['require']('piwik/UI'); // eslint-disable-line\n\n            const notification = new UI.Notification();\n            notification.show(response.message, {\n              placeat: placeAt,\n              context: 'error',\n              type,\n              id: 'ajaxHelper'\n            });\n            notification.scrollToNotification();\n          }\n        } else if (this.callback) {\n          this.callback(response, status, request);\n        }\n\n        globalAjaxQueue.active -= 1;\n        const {\n          piwik\n        } = window;\n\n        if (piwik && piwik.ajaxRequestFinished) {\n          piwik.ajaxRequestFinished();\n        }\n      },\n      data: this.mixinDefaultPostParams(this.postParams),\n      timeout: this.timeout !== null ? this.timeout : undefined\n    };\n    return $.ajax(ajaxCall);\n  }\n\n  isRequestToApiMethod() {\n    return this.getParams && this.getParams.module === 'API' && this.getParams.method || this.postParams && this.postParams.module === 'API' && this.postParams.method;\n  }\n\n  isWidgetizedRequest() {\n    return broadcast.getValueFromUrl('module') === 'Widgetize';\n  }\n\n  getDefaultPostParams() {\n    if (this.withToken || this.isRequestToApiMethod() || piwik.shouldPropagateTokenAuth) {\n      return {\n        token_auth: piwik.token_auth,\n        // When viewing a widgetized report there won't be any session that can be used, so don't\n        // force session usage\n        force_api_session: broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1\n      };\n    }\n\n    return {};\n  }\n  /**\n   * Mixin the default parameters to send as POST\n   *\n   * @param params   parameter object\n   */\n\n\n  mixinDefaultPostParams(params) {\n    const defaultParams = this.getDefaultPostParams();\n    const mergedParams = { ...defaultParams,\n      ...params\n    };\n    return mergedParams;\n  }\n  /**\n   * Mixin the default parameters to send as GET\n   *\n   * @param   params   parameter object\n   */\n\n\n  mixinDefaultGetParams(originalParams) {\n    const segment = _PiwikUrl_PiwikUrl__WEBPACK_IMPORTED_MODULE_0__[\"default\"].getSearchParam('segment');\n    const defaultParams = {\n      idSite: piwik.idSite || broadcast.getValueFromUrl('idSite'),\n      period: piwik.period || broadcast.getValueFromUrl('period'),\n      segment\n    };\n    const params = originalParams; // never append token_auth to url\n\n    if (params.token_auth) {\n      params.token_auth = null;\n      delete params.token_auth;\n    }\n\n    Object.keys(defaultParams).forEach(key => {\n      if (this.useGETDefaultParameter(key) && !params[key] && !this.postParams[key] && defaultParams[key]) {\n        params[key] = defaultParams[key];\n      }\n    }); // handle default date & period if not already set\n\n    if (this.useGETDefaultParameter('date') && !params.date && !this.postParams.date) {\n      params.date = piwik.currentDateString;\n    }\n\n    return params;\n  }\n\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts":
/*!*********************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return alertAdapter; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _Alert_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Alert.vue */ \"./plugins/CoreHome/vue/src/Alert/Alert.vue\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\nfunction alertAdapter() {\n  return {\n    restrict: 'A',\n    transclude: true,\n    scope: {\n      severity: '@piwikAlert'\n    },\n    template: '<div ng-transclude/>',\n    compile: function alertAdapterCompile() {\n      return {\n        post: function alertAdapterPostLink(scope, element) {\n          const clone = element.find('[ng-transclude]');\n          const app = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createApp\"])({\n            template: '<alert :severity=\"severity\"><div ref=\"transcludeTarget\"/></alert>',\n\n            data() {\n              return {\n                severity: scope.severity\n              };\n            },\n\n            setup() {\n              const transcludeTarget = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"ref\"])(null);\n              return {\n                transcludeTarget\n              };\n            }\n\n          });\n          app.component('alert', _Alert_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n          const vm = app.mount(element[0]);\n          scope.$watch('severity', newValue => {\n            vm.severity = newValue;\n          });\n          $(vm.transcludeTarget).append(clone);\n        }\n      };\n    }\n  };\n}\nalertAdapter.$inject = [];\nangular.module('piwikApp').directive('piwikAlert', alertAdapter);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Alert/Alert.vue":
/*!**************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Alert/Alert.vue ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Alert_vue_vue_type_template_id_52d77d41__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Alert.vue?vue&type=template&id=52d77d41 */ \"./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41\");\n/* harmony import */ var _Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Alert.vue?vue&type=script&lang=ts */ \"./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _Alert_vue_vue_type_template_id_52d77d41__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/CoreHome/vue/src/Alert/Alert.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts":
/*!**************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./Alert.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_Alert_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41":
/*!********************************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41 ***!
  \********************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_Alert_vue_vue_type_template_id_52d77d41__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./Alert.vue?vue&type=template&id=52d77d41 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/CoreHome/vue/src/Alert/Alert.vue?vue&type=template&id=52d77d41\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_Alert_vue_vue_type_template_id_52d77d41__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.vue?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Day.ts":
/*!*************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Day.ts ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return DayPeriod; });\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nclass DayPeriod {\n  constructor(dateInPeriod) {\n    _defineProperty(this, \"dateInPeriod\", void 0);\n\n    this.dateInPeriod = dateInPeriod;\n  }\n\n  static parse(strDate) {\n    return new DayPeriod(Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(strDate));\n  }\n\n  static getDisplayText() {\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('Intl_PeriodDay');\n  }\n\n  getPrettyString() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"])(this.dateInPeriod);\n  }\n\n  getDateRange() {\n    return [new Date(this.dateInPeriod.getTime()), new Date(this.dateInPeriod.getTime())];\n  }\n\n  containsToday() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"])(this.getDateRange());\n  }\n\n}\n_Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].addCustomPeriod('day', DayPeriod);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Day.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Month.ts":
/*!***************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Month.ts ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return MonthPeriod; });\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nclass MonthPeriod {\n  constructor(dateInPeriod) {\n    _defineProperty(this, \"dateInPeriod\", void 0);\n\n    this.dateInPeriod = dateInPeriod;\n  }\n\n  static parse(strDate) {\n    return new MonthPeriod(Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(strDate));\n  }\n\n  static getDisplayText() {\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('Intl_PeriodMonth');\n  }\n\n  getPrettyString() {\n    const month = Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(`Intl_Month_Long_StandAlone_${this.dateInPeriod.getMonth() + 1}`);\n    return `${month} ${this.dateInPeriod.getFullYear()}`;\n  }\n\n  getDateRange() {\n    const startMonth = new Date(this.dateInPeriod.getTime());\n    startMonth.setDate(1);\n    const endMonth = new Date(this.dateInPeriod.getTime());\n    endMonth.setDate(1);\n    endMonth.setMonth(endMonth.getMonth() + 1);\n    endMonth.setDate(0);\n    return [startMonth, endMonth];\n  }\n\n  containsToday() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"])(this.getDateRange());\n  }\n\n}\n_Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].addCustomPeriod('month', MonthPeriod);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Month.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Periods.adapter.ts":
/*!*************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Periods.adapter.ts ***!
  \*************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _Range__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Range */ \"./plugins/CoreHome/vue/src/Periods/Range.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\npiwik.addCustomPeriod = _Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"].addCustomPeriod.bind(_Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"]);\n\nfunction piwikPeriods() {\n  return {\n    getAllLabels: _Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"].getAllLabels.bind(_Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"]),\n    isRecognizedPeriod: _Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"].isRecognizedPeriod.bind(_Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"]),\n    get: _Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"].get.bind(_Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"]),\n    parse: _Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"].parse.bind(_Periods__WEBPACK_IMPORTED_MODULE_0__[\"default\"]),\n    parseDate: _utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"],\n    format: _utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"],\n    RangePeriod: _Range__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n    todayIsInRange: _utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"]\n  };\n}\n\nangular.module('piwikApp.service').factory('piwikPeriods', piwikPeriods);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Periods.adapter.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Periods.ts":
/*!*****************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Periods.ts ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n/**\n * Piwik period management service for the frontend.\n *\n * Usage:\n *\n *     var DayPeriod = piwikPeriods.get('day');\n *     var day = new DayPeriod(new Date());\n *\n * or\n *\n *     var day = piwikPeriods.parse('day', '2013-04-05');\n *\n * Adding custom periods:\n *\n * To add your own period to the frontend, create a period class for it\n * w/ the following methods:\n *\n * - **getPrettyString()**: returns a human readable display string for the period.\n * - **getDateRange()**: returns an array w/ two elements, the first being the start\n *                       Date of the period, the second being the end Date. The dates\n *                       must be Date objects, not strings, and are inclusive.\n * - **containsToday()**: returns true if the date period contains today. False if not.\n * - (_static_) **parse(strDate)**: creates a new instance of this period from the\n *                                  value of the 'date' query parameter.\n * - (_static_) **getDisplayText**: returns translated text for the period, eg, 'month',\n *                                  'week', etc.\n *\n * Then call piwik.addCustomPeriod w/ your period class:\n *\n *     piwik.addCustomPeriod('mycustomperiod', MyCustomPeriod);\n *\n * NOTE: currently only single date periods like day, week, month year can\n *       be extended. Other types of periods that require a special UI to\n *       view/edit aren't, since there is currently no way to use a\n *       custom UI for a custom period.\n */\nclass Periods {\n  constructor() {\n    _defineProperty(this, \"periods\", {});\n\n    _defineProperty(this, \"periodOrder\", []);\n  }\n\n  addCustomPeriod(name, periodClass) {\n    if (this.periods[name]) {\n      throw new Error(`The \"${name}\" period already exists! It cannot be overridden.`);\n    }\n\n    this.periods[name] = periodClass;\n    this.periodOrder.push(name);\n  }\n\n  getAllLabels() {\n    return Array().concat(this.periodOrder);\n  }\n\n  get(strPeriod) {\n    const periodClass = this.periods[strPeriod];\n\n    if (!periodClass) {\n      throw new Error(`Invalid period label: ${strPeriod}`);\n    }\n\n    return periodClass;\n  }\n\n  parse(strPeriod, strDate) {\n    return this.get(strPeriod).parse(strDate);\n  }\n\n  isRecognizedPeriod(strPeriod) {\n    return !!this.periods[strPeriod];\n  }\n\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (new Periods());\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Periods.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Range.ts":
/*!***************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Range.ts ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return RangePeriod; });\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nclass RangePeriod {\n  constructor(startDate, endDate, childPeriodType) {\n    _defineProperty(this, \"startDate\", void 0);\n\n    _defineProperty(this, \"endDate\", void 0);\n\n    _defineProperty(this, \"childPeriodType\", void 0);\n\n    this.startDate = startDate;\n    this.endDate = endDate;\n    this.childPeriodType = childPeriodType;\n  }\n  /**\n   * Returns a range representing the last N childPeriodType periods, including the current one.\n   */\n\n\n  static getLastNRange(childPeriodType, strAmount, strEndDate) {\n    const nAmount = Math.max(parseInt(strAmount.toString(), 10) - 1, 0);\n\n    if (Number.isNaN(nAmount)) {\n      throw new Error('Invalid range strAmount');\n    }\n\n    let endDate = strEndDate ? Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(strEndDate) : Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"getToday\"])();\n    let startDate = new Date(endDate.getTime());\n\n    if (childPeriodType === 'day') {\n      startDate.setDate(startDate.getDate() - nAmount);\n    } else if (childPeriodType === 'week') {\n      startDate.setDate(startDate.getDate() - nAmount * 7);\n    } else if (childPeriodType === 'month') {\n      startDate.setDate(1);\n      startDate.setMonth(startDate.getMonth() - nAmount);\n    } else if (childPeriodType === 'year') {\n      startDate.setFullYear(startDate.getFullYear() - nAmount);\n    } else {\n      throw new Error(`Unknown period type '${childPeriodType}'.`);\n    }\n\n    if (childPeriodType !== 'day') {\n      const startPeriod = _Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].periods[childPeriodType].parse(startDate);\n      const endPeriod = _Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].periods[childPeriodType].parse(endDate);\n      [startDate] = startPeriod.getDateRange();\n      [, endDate] = endPeriod.getDateRange();\n    }\n\n    const firstWebsiteDate = new Date(1991, 7, 6);\n\n    if (startDate.getTime() - firstWebsiteDate.getTime() < 0) {\n      switch (childPeriodType) {\n        case 'year':\n          startDate = new Date(1992, 0, 1);\n          break;\n\n        case 'month':\n          startDate = new Date(1991, 8, 1);\n          break;\n\n        case 'week':\n          startDate = new Date(1991, 8, 12);\n          break;\n\n        case 'day':\n        default:\n          startDate = firstWebsiteDate;\n          break;\n      }\n    }\n\n    return new RangePeriod(startDate, endDate, childPeriodType);\n  }\n\n  static parse(strDate, childPeriodType = 'day') {\n    if (/^previous/.test(strDate)) {\n      const endDate = RangePeriod.getLastNRange(childPeriodType, '2').startDate;\n      return RangePeriod.getLastNRange(childPeriodType, strDate.substring(8), endDate);\n    }\n\n    if (/^last/.test(strDate)) {\n      return RangePeriod.getLastNRange(childPeriodType, strDate.substring(4));\n    }\n\n    const parts = decodeURIComponent(strDate).split(',');\n    return new RangePeriod(Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(parts[0]), Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(parts[1]), childPeriodType);\n  }\n\n  static getDisplayText() {\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('General_DateRangeInPeriodList');\n  }\n\n  getPrettyString() {\n    const start = Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"])(this.startDate);\n    const end = Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"])(this.endDate);\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('General_DateRangeFromTo', [start, end]);\n  }\n\n  getDateRange() {\n    return [this.startDate, this.endDate];\n  }\n\n  containsToday() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"])(this.getDateRange());\n  }\n\n}\n_Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].addCustomPeriod('range', RangePeriod);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Range.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Week.ts":
/*!**************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Week.ts ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return WeekPeriod; });\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nclass WeekPeriod {\n  constructor(dateInPeriod) {\n    _defineProperty(this, \"dateInPeriod\", void 0);\n\n    this.dateInPeriod = dateInPeriod;\n  }\n\n  static parse(strDate) {\n    return new WeekPeriod(Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(strDate));\n  }\n\n  static getDisplayText() {\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('Intl_PeriodWeek');\n  }\n\n  getPrettyString() {\n    const weekDates = this.getDateRange();\n    const startWeek = Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"])(weekDates[0]);\n    const endWeek = Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"format\"])(weekDates[1]);\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('General_DateRangeFromTo', [startWeek, endWeek]);\n  }\n\n  getDateRange() {\n    const daysToMonday = (this.dateInPeriod.getDay() + 6) % 7;\n    const startWeek = new Date(this.dateInPeriod.getTime());\n    startWeek.setDate(this.dateInPeriod.getDate() - daysToMonday);\n    const endWeek = new Date(startWeek.getTime());\n    endWeek.setDate(startWeek.getDate() + 6);\n    return [startWeek, endWeek];\n  }\n\n  containsToday() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"])(this.getDateRange());\n  }\n\n}\n_Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].addCustomPeriod('week', WeekPeriod);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Week.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/Year.ts":
/*!**************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/Year.ts ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return YearPeriod; });\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony import */ var _Periods__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony import */ var _utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utilities */ \"./plugins/CoreHome/vue/src/Periods/utilities.ts\");\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nclass YearPeriod {\n  constructor(dateInPeriod) {\n    _defineProperty(this, \"dateInPeriod\", void 0);\n\n    this.dateInPeriod = dateInPeriod;\n  }\n\n  static parse(strDate) {\n    return new YearPeriod(Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"parseDate\"])(strDate));\n  }\n\n  static getDisplayText() {\n    return Object(_translate__WEBPACK_IMPORTED_MODULE_0__[\"default\"])('Intl_PeriodYear');\n  }\n\n  getPrettyString() {\n    return this.dateInPeriod.getFullYear().toString();\n  }\n\n  getDateRange() {\n    const startYear = new Date(this.dateInPeriod.getTime());\n    startYear.setMonth(0);\n    startYear.setDate(1);\n    const endYear = new Date(this.dateInPeriod.getTime());\n    endYear.setMonth(12);\n    endYear.setDate(0);\n    return [startYear, endYear];\n  }\n\n  containsToday() {\n    return Object(_utilities__WEBPACK_IMPORTED_MODULE_2__[\"todayIsInRange\"])(this.getDateRange());\n  }\n\n}\n_Periods__WEBPACK_IMPORTED_MODULE_1__[\"default\"].addCustomPeriod('year', YearPeriod);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/Year.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/Periods/utilities.ts":
/*!*******************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Periods/utilities.ts ***!
  \*******************************************************/
/*! exports provided: format, getToday, parseDate, todayIsInRange */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"format\", function() { return format; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"getToday\", function() { return getToday; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"parseDate\", function() { return parseDate; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"todayIsInRange\", function() { return todayIsInRange; });\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\nfunction format(date) {\n  return $.datepicker.formatDate('yy-mm-dd', date);\n}\nfunction getToday() {\n  const date = new Date(Date.now()); // undo browser timezone\n\n  date.setTime(date.getTime() + date.getTimezoneOffset() * 60 * 1000); // apply piwik site timezone (if it exists)\n\n  date.setHours(date.getHours() + (piwik.timezoneOffset || 0) / 3600); // get rid of hours/minutes/seconds/etc.\n\n  date.setHours(0);\n  date.setMinutes(0);\n  date.setSeconds(0);\n  date.setMilliseconds(0);\n  return date;\n}\nfunction parseDate(date) {\n  if (date instanceof Date) {\n    return date;\n  }\n\n  const strDate = decodeURIComponent(date);\n\n  if (strDate === 'today' || strDate === 'now') {\n    return getToday();\n  }\n\n  if (strDate === 'yesterday' // note: ignoring the 'same time' part since the frontend doesn't care about the time\n  || strDate === 'yesterdaySameTime') {\n    const yesterday = getToday();\n    yesterday.setDate(yesterday.getDate() - 1);\n    return yesterday;\n  }\n\n  if (strDate.match(/last[ -]?week/i)) {\n    const lastWeek = getToday();\n    lastWeek.setDate(lastWeek.getDate() - 7);\n    return lastWeek;\n  }\n\n  if (strDate.match(/last[ -]?month/i)) {\n    const lastMonth = getToday();\n    lastMonth.setDate(1);\n    lastMonth.setMonth(lastMonth.getMonth() - 1);\n    return lastMonth;\n  }\n\n  if (strDate.match(/last[ -]?year/i)) {\n    const lastYear = getToday();\n    lastYear.setFullYear(lastYear.getFullYear() - 1);\n    return lastYear;\n  }\n\n  try {\n    return $.datepicker.parseDate('yy-mm-dd', strDate);\n  } catch (err) {\n    // angular swallows this error, so manual console log here\n    console.error(err.message || err);\n    throw err;\n  }\n}\nfunction todayIsInRange(dateRange) {\n  if (dateRange.length !== 2) {\n    return false;\n  }\n\n  if (getToday() >= dateRange[0] && getToday() <= dateRange[1]) {\n    return true;\n  }\n\n  return false;\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Periods/utilities.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.adapter.ts":
/*!***************************************************************!*\
  !*** ./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.adapter.ts ***!
  \***************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _PiwikUrl__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./PiwikUrl */ \"./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\nfunction piwikUrl() {\n  const model = {\n    getSearchParam: _PiwikUrl__WEBPACK_IMPORTED_MODULE_0__[\"default\"].getSearchParam.bind(_PiwikUrl__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n  };\n  return model;\n}\n\npiwikUrl.$inject = [];\nangular.module('piwikApp.service').service('piwikUrl', piwikUrl);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.adapter.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts":
/*!*******************************************************!*\
  !*** ./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n/**\n * Similar to angulars $location but works around some limitation. Use it if you need to access\n * search params\n */\nconst PiwikUrl = {\n  getSearchParam(paramName) {\n    const hash = window.location.href.split('#');\n    const regex = new RegExp(`${paramName}(\\\\[]|=)`);\n\n    if (hash && hash[1] && regex.test(decodeURIComponent(hash[1]))) {\n      const valueFromHash = broadcast.getValueFromHash(paramName, window.location.href); // for date, period and idsite fall back to parameter from url, if non in hash was provided\n\n      if (valueFromHash || paramName !== 'date' && paramName !== 'period' && paramName !== 'idSite') {\n        return valueFromHash;\n      }\n    }\n\n    return broadcast.getValueFromUrl(paramName, window.location.search);\n  }\n\n};\n/* harmony default export */ __webpack_exports__[\"default\"] = (PiwikUrl);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/index.ts":
/*!*******************************************!*\
  !*** ./plugins/CoreHome/vue/src/index.ts ***!
  \*******************************************/
/*! exports provided: activityIndicatorAdapter, ActivityIndicator, translate, alertAdapter, Periods, AjaxHelper, PiwikUrl */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Periods_Day__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Periods/Day */ \"./plugins/CoreHome/vue/src/Periods/Day.ts\");\n/* harmony import */ var _Periods_Week__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Periods/Week */ \"./plugins/CoreHome/vue/src/Periods/Week.ts\");\n/* harmony import */ var _Periods_Month__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Periods/Month */ \"./plugins/CoreHome/vue/src/Periods/Month.ts\");\n/* harmony import */ var _Periods_Year__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Periods/Year */ \"./plugins/CoreHome/vue/src/Periods/Year.ts\");\n/* harmony import */ var _Periods_Range__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Periods/Range */ \"./plugins/CoreHome/vue/src/Periods/Range.ts\");\n/* harmony import */ var _Periods_Periods_adapter__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Periods/Periods.adapter */ \"./plugins/CoreHome/vue/src/Periods/Periods.adapter.ts\");\n/* harmony import */ var _AjaxHelper_AjaxHelper_adapter__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./AjaxHelper/AjaxHelper.adapter */ \"./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.adapter.ts\");\n/* harmony import */ var _PiwikUrl_PiwikUrl_adapter__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./PiwikUrl/PiwikUrl.adapter */ \"./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.adapter.ts\");\n/* harmony import */ var _ActivityIndicator_ActivityIndicator_adapter__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./ActivityIndicator/ActivityIndicator.adapter */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"activityIndicatorAdapter\", function() { return _ActivityIndicator_ActivityIndicator_adapter__WEBPACK_IMPORTED_MODULE_8__[\"default\"]; });\n\n/* harmony import */ var _ActivityIndicator_ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./ActivityIndicator/ActivityIndicator.vue */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ActivityIndicator\", function() { return _ActivityIndicator_ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_9__[\"default\"]; });\n\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"translate\", function() { return _translate__WEBPACK_IMPORTED_MODULE_10__[\"default\"]; });\n\n/* harmony import */ var _Alert_Alert_adapter__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./Alert/Alert.adapter */ \"./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"alertAdapter\", function() { return _Alert_Alert_adapter__WEBPACK_IMPORTED_MODULE_11__[\"default\"]; });\n\n/* harmony import */ var _Periods_Periods__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./Periods/Periods */ \"./plugins/CoreHome/vue/src/Periods/Periods.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Periods\", function() { return _Periods_Periods__WEBPACK_IMPORTED_MODULE_12__[\"default\"]; });\n\n/* harmony import */ var _AjaxHelper_AjaxHelper__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./AjaxHelper/AjaxHelper */ \"./plugins/CoreHome/vue/src/AjaxHelper/AjaxHelper.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"AjaxHelper\", function() { return _AjaxHelper_AjaxHelper__WEBPACK_IMPORTED_MODULE_13__[\"default\"]; });\n\n/* harmony import */ var _PiwikUrl_PiwikUrl__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./PiwikUrl/PiwikUrl */ \"./plugins/CoreHome/vue/src/PiwikUrl/PiwikUrl.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"PiwikUrl\", function() { return _PiwikUrl_PiwikUrl__WEBPACK_IMPORTED_MODULE_14__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/index.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/translate.ts":
/*!***********************************************!*\
  !*** ./plugins/CoreHome/vue/src/translate.ts ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return translate; });\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\nfunction translate(translationStringId, values = []) {\n  return _pk_translate(translationStringId, values);\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/translate.ts?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://CoreHome/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});