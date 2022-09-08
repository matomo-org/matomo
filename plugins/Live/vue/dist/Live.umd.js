(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome"], factory);
	else if(typeof exports === 'object')
		exports["Live"] = factory(require("CoreHome"));
	else
		root["Live"] = factory(root["CoreHome"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__) {
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
/******/ 	__webpack_require__.p = "plugins/Live/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: LiveWidgetRefresh */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Live/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LiveWidgetRefresh\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"LiveWidgetRefresh\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Live/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://Live/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.adapter.ts":
/*!**********************************************************************!*\
  !*** ./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.adapter.ts ***!
  \**********************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LiveWidgetRefresh__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LiveWidgetRefresh */ \"./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\nfunction piwikLiveWidgetRefresh() {\n  return {\n    restrict: 'A',\n    scope: {\n      liveRefreshAfterMs: '@'\n    },\n    // eslint-disable-next-line @typescript-eslint/no-explicit-any\n    link: function link(scope, element) {\n      _LiveWidgetRefresh__WEBPACK_IMPORTED_MODULE_0__[\"default\"].mounted(element[0], {\n        instance: null,\n        value: {\n          liveRefreshAfterMs: parseInt(scope.liveRefreshAfterMs, 10)\n        },\n        oldValue: null,\n        modifiers: {},\n        dir: {}\n      });\n    }\n  };\n}\n\npiwikLiveWidgetRefresh.$inject = ['piwik', '$timeout'];\nwindow.angular.module('piwikApp').directive('piwikLiveWidgetRefresh', piwikLiveWidgetRefresh);\n\n//# sourceURL=webpack://Live/./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.adapter.ts?");

/***/ }),

/***/ "./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts":
/*!**************************************************************!*\
  !*** ./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_0__);\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\nvar _window = window,\n    $ = _window.$;\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  mounted: function mounted(el, binding) {\n    setTimeout(function () {\n      var segment = CoreHome__WEBPACK_IMPORTED_MODULE_0__[\"MatomoUrl\"].parsed.value.segment; // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n      $(el).find('#visitsLive').liveWidget({\n        interval: binding.value.liveRefreshAfterMs,\n        onUpdate: function onUpdate() {\n          // updates the numbers of total visits in startbox\n          CoreHome__WEBPACK_IMPORTED_MODULE_0__[\"AjaxHelper\"].fetch({\n            module: 'Live',\n            action: 'ajaxTotalVisitors',\n            segment: segment\n          }, {\n            format: 'html'\n          }).then(function (r) {\n            $(el).find('#visitsTotal').replaceWith(r);\n          });\n        },\n        maxRows: 10,\n        fadeInSpeed: 600,\n        dataUrlParams: {\n          module: 'Live',\n          action: 'getLastVisitsStart',\n          segment: segment\n        }\n      });\n    });\n  }\n});\n\n//# sourceURL=webpack://Live/./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts?");

/***/ }),

/***/ "./plugins/Live/vue/src/index.ts":
/*!***************************************!*\
  !*** ./plugins/Live/vue/src/index.ts ***!
  \***************************************/
/*! exports provided: LiveWidgetRefresh */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LiveWidget_LiveWidgetRefresh_adapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LiveWidget/LiveWidgetRefresh.adapter */ \"./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.adapter.ts\");\n/* harmony import */ var _LiveWidget_LiveWidgetRefresh__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LiveWidget/LiveWidgetRefresh */ \"./plugins/Live/vue/src/LiveWidget/LiveWidgetRefresh.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LiveWidgetRefresh\", function() { return _LiveWidget_LiveWidgetRefresh__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n//# sourceURL=webpack://Live/./plugins/Live/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://Live/external_%22CoreHome%22?");

/***/ })

/******/ });
});