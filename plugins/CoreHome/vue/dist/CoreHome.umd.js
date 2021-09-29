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
/*! exports provided: activityIndicatorAdapter, ActivityIndicator, translate, alertAdapter */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/CoreHome/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"activityIndicatorAdapter\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"activityIndicatorAdapter\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ActivityIndicator\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"ActivityIndicator\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"translate\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"translate\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"alertAdapter\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"alertAdapter\"]; });\n\n\n\n\n\n//# sourceURL=webpack://CoreHome/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

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
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return activityIndicatorAdapter; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityIndicator.vue */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue\");\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n\n\n\nfunction activityIndicatorAdapter() {\n  return {\n    restrict: 'A',\n    scope: {\n      loading: '<',\n      loadingMessage: '<'\n    },\n    template: '',\n    link: function activityIndicatorAdapterLink(scope, element) {\n      const app = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createApp\"])({\n        template: '<activity-indicator :loading=\"loading\" :loadingMessage=\"loadingMessage\"/>',\n\n        data() {\n          return {\n            loading: scope.loading,\n            loadingMessage: scope.loadingMessage\n          };\n        }\n\n      });\n      app.component('activity-indicator', _ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n      const vm = app.mount(element[0]);\n      scope.$watch('loading', newValue => {\n        vm.loading = newValue;\n      });\n      scope.$watch('loadingMessage', newValue => {\n        vm.loadingMessage = newValue || Object(_translate__WEBPACK_IMPORTED_MODULE_2__[\"default\"])('General_LoadingData');\n      });\n    }\n  };\n}\nactivityIndicatorAdapter.$inject = [];\nangular.module('piwikApp').directive('piwikActivityIndicator', activityIndicatorAdapter);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts?");

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

/***/ "./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts":
/*!*********************************************************!*\
  !*** ./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return alertAdapter; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _Alert_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Alert.vue */ \"./plugins/CoreHome/vue/src/Alert/Alert.vue\");\n\n\nfunction alertAdapter() {\n  return {\n    restrict: 'A',\n    transclude: true,\n    scope: {\n      severity: '@piwikAlert'\n    },\n    template: '<div ng-transclude/>',\n    compile: function alertAdapterCompile() {\n      return {\n        post: function alertAdapterPostLink(scope, element) {\n          const clone = element.find('[ng-transclude]');\n          const app = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createApp\"])({\n            template: '<alert :severity=\"severity\"><div ref=\"transcludeTarget\"/></alert>',\n\n            data() {\n              return {\n                severity: scope.severity\n              };\n            },\n\n            setup() {\n              const transcludeTarget = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"ref\"])(null);\n              return {\n                transcludeTarget\n              };\n            }\n\n          });\n          app.component('alert', _Alert_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n          const vm = app.mount(element[0]);\n          scope.$watch('severity', newValue => {\n            vm.severity = newValue;\n          });\n          $(vm.transcludeTarget).append(clone);\n        }\n      };\n    }\n  };\n}\nalertAdapter.$inject = [];\nangular.module('piwikApp').directive('piwikAlert', alertAdapter);\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts?");

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

/***/ "./plugins/CoreHome/vue/src/index.ts":
/*!*******************************************!*\
  !*** ./plugins/CoreHome/vue/src/index.ts ***!
  \*******************************************/
/*! exports provided: activityIndicatorAdapter, ActivityIndicator, translate, alertAdapter */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ActivityIndicator_ActivityIndicator_adapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActivityIndicator/ActivityIndicator.adapter */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.adapter.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"activityIndicatorAdapter\", function() { return _ActivityIndicator_ActivityIndicator_adapter__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* harmony import */ var _ActivityIndicator_ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityIndicator/ActivityIndicator.vue */ \"./plugins/CoreHome/vue/src/ActivityIndicator/ActivityIndicator.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ActivityIndicator\", function() { return _ActivityIndicator_ActivityIndicator_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/* harmony import */ var _translate__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./translate */ \"./plugins/CoreHome/vue/src/translate.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"translate\", function() { return _translate__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/* harmony import */ var _Alert_Alert_adapter__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Alert/Alert.adapter */ \"./plugins/CoreHome/vue/src/Alert/Alert.adapter.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"alertAdapter\", function() { return _Alert_Alert_adapter__WEBPACK_IMPORTED_MODULE_3__[\"default\"]; });\n\n\n\n\n\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/index.ts?");

/***/ }),

/***/ "./plugins/CoreHome/vue/src/translate.ts":
/*!***********************************************!*\
  !*** ./plugins/CoreHome/vue/src/translate.ts ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return translate; });\nfunction translate(translationStringId, values = []) {\n  return window._pk_translate(translationStringId, values); // eslint-disable-line\n}\n\n//# sourceURL=webpack://CoreHome/./plugins/CoreHome/vue/src/translate.ts?");

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