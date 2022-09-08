(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Dashboard"] = factory(require("CoreHome"), require("vue"));
	else
		root["Dashboard"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Dashboard/vue/dist/";
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
/*! exports provided: DashboardStore, Dashboard */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Dashboard/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardStore\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"DashboardStore\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Dashboard\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"Dashboard\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Dashboard/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://Dashboard/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/Dashboard/Dashboard.adapter.ts":
/*!******************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/Dashboard/Dashboard.adapter.ts ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return piwikDashboard; });\n/* harmony import */ var _Dashboard__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dashboard */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\nfunction piwikDashboard() {\n  return {\n    restrict: 'A',\n    scope: {\n      dashboardid: '=',\n      layout: '='\n    },\n    // eslint-disable-next-line @typescript-eslint/no-explicit-any\n    link: function expandOnClickLink(scope, element) {\n      var binding = {\n        instance: null,\n        value: {\n          idDashboard: scope.dashboardid,\n          layout: scope.layout\n        },\n        oldValue: null,\n        modifiers: {},\n        dir: {}\n      };\n      _Dashboard__WEBPACK_IMPORTED_MODULE_0__[\"default\"].mounted(element[0], binding); // using scope destroy instead of element destroy event, since piwik-dashboard elements\n      // are removed manually, outside of angularjs/vue workflow, so element destroy is not\n      // triggered\n\n      scope.$on('$destroy', function () {\n        _Dashboard__WEBPACK_IMPORTED_MODULE_0__[\"default\"].unmounted();\n      });\n    }\n  };\n}\nwindow.angular.module('piwikApp').directive('piwikDashboard', piwikDashboard);\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/Dashboard/Dashboard.adapter.ts?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.adapter.ts":
/*!************************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.adapter.ts ***!
  \************************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Dashboard_store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dashboard.store */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\nwindow.angular.module('piwikApp.service').factory('dashboardsModel', function () {\n  return _Dashboard_store__WEBPACK_IMPORTED_MODULE_0__[\"default\"];\n});\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.adapter.ts?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts":
/*!****************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts ***!
  \****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\nfunction _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }\n\nfunction _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }\n\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nvar DashboardStore = /*#__PURE__*/function () {\n  function DashboardStore() {\n    var _this = this;\n\n    _classCallCheck(this, DashboardStore);\n\n    _defineProperty(this, \"privateState\", Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"reactive\"])({\n      dashboards: []\n    }));\n\n    _defineProperty(this, \"state\", Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"computed\"])(function () {\n      return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"readonly\"])(_this.privateState);\n    }));\n\n    _defineProperty(this, \"dashboards\", Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"computed\"])(function () {\n      return _this.state.value.dashboards;\n    }));\n\n    _defineProperty(this, \"dashboardsPromise\", null);\n  }\n\n  _createClass(DashboardStore, [{\n    key: \"getDashboard\",\n    value: function getDashboard(dashboardId) {\n      return this.getAllDashboards().then(function (dashboards) {\n        return dashboards.find(function (b) {\n          return parseInt(\"\".concat(b.id), 10) === parseInt(\"\".concat(dashboardId), 10);\n        });\n      });\n    }\n  }, {\n    key: \"getDashboardLayout\",\n    value: function getDashboardLayout(dashboardId) {\n      return CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        module: 'Dashboard',\n        action: 'getDashboardLayout',\n        idDashboard: dashboardId\n      }, {\n        withTokenInUrl: true\n      });\n    }\n  }, {\n    key: \"reloadAllDashboards\",\n    value: function reloadAllDashboards() {\n      this.dashboardsPromise = null;\n      return this.getAllDashboards();\n    }\n  }, {\n    key: \"getAllDashboards\",\n    value: function getAllDashboards() {\n      var _this2 = this;\n\n      if (!this.dashboardsPromise) {\n        this.dashboardsPromise = CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n          method: 'Dashboard.getDashboards',\n          filter_limit: '-1'\n        }).then(function (response) {\n          if (response) {\n            _this2.privateState.dashboards = response;\n          }\n\n          return _this2.dashboards.value;\n        });\n      }\n\n      return this.dashboardsPromise;\n    }\n  }]);\n\n  return DashboardStore;\n}();\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (new DashboardStore());\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts":
/*!**********************************************************!*\
  !*** ./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _Dashboard_store__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Dashboard.store */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts\");\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== \"undefined\" && arr[Symbol.iterator] || arr[\"@@iterator\"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nvar _window = window,\n    $ = _window.$;\n\nfunction renderDashboard(dashboardId, dashboard, layout) {\n  var $settings = $('.dashboardSettings');\n  $settings.show();\n  window.initTopControls(); // Embed dashboard / exported as widget\n\n  if (!$('#topBars').length) {\n    $settings.after($('#Dashboard'));\n    $('#Dashboard ul li').removeClass('active');\n    $(\"#Dashboard_embeddedIndex_\".concat(dashboardId)).addClass('active');\n  }\n\n  window.widgetsHelper.getAvailableWidgets(); // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n  $('#dashboardWidgetsArea').off('dashboardempty', window.showEmptyDashboardNotification).on('dashboardempty', window.showEmptyDashboardNotification).dashboard({\n    idDashboard: dashboardId,\n    layout: layout,\n    name: dashboard ? dashboard.name : ''\n  });\n  var divElements = $('#columnPreview').find('>div');\n  divElements.each(function eachPreview() {\n    var width = [];\n    $('div', this).each(function eachDiv() {\n      width.push(this.className.replace(/width-/, ''));\n    });\n    $(this).attr('layout', width.join('-'));\n  });\n  divElements.off('click.renderDashboard');\n  divElements.on('click.renderDashboard', function onRenderDashboard() {\n    divElements.removeClass('choosen');\n    $(this).addClass('choosen');\n  });\n}\n\nfunction fetchDashboard(dashboardId) {\n  window.globalAjaxQueue.abort();\n  return new Promise(function (resolve) {\n    return setTimeout(resolve);\n  }).then(function () {\n    return Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall);\n  }).then(function () {\n    // eslint-disable-next-line @typescript-eslint/no-explicit-any\n    var dashboardElement = $('#dashboardWidgetsArea');\n    dashboardElement.dashboard('destroyWidgets');\n    dashboardElement.empty();\n    return Promise.all([_Dashboard_store__WEBPACK_IMPORTED_MODULE_2__[\"default\"].getDashboard(dashboardId), _Dashboard_store__WEBPACK_IMPORTED_MODULE_2__[\"default\"].getDashboardLayout(dashboardId)]);\n  }).then(function (_ref) {\n    var _ref2 = _slicedToArray(_ref, 2),\n        dashboard = _ref2[0],\n        layout = _ref2[1];\n\n    return new Promise(function (resolve) {\n      $(function () {\n        renderDashboard(dashboardId, dashboard, layout);\n        resolve();\n      });\n    });\n  });\n}\n\nfunction clearDashboard() {\n  $('.top_controls .dashboard-manager').hide(); // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n  $('#dashboardWidgetsArea').dashboard('destroy');\n}\n\nfunction onLocationChange(parsed) {\n  if (parsed.module !== 'Widgetize' && parsed.category !== 'Dashboard_Dashboard') {\n    // we remove the dashboard only if we no longer show a dashboard.\n    clearDashboard();\n  }\n}\n\nfunction onLoadPage(params) {\n  if (params.category === 'Dashboard_Dashboard' && $.isNumeric(params.subcategory)) {\n    params.promise = fetchDashboard(parseInt(params.subcategory, 10));\n  }\n}\n\nfunction onLoadDashboard(idDashboard) {\n  fetchDashboard(idDashboard);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  mounted: function mounted(el, binding) {\n    fetchDashboard(binding.value.idDashboard);\n    Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"watch\"])(function () {\n      return CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].parsed.value;\n    }, function (parsed) {\n      onLocationChange(parsed);\n    }); // load dashboard directly since it will be faster than going through reporting page API\n\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].on('ReportingPage.loadPage', onLoadPage);\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].on('Dashboard.loadDashboard', onLoadDashboard);\n  },\n  unmounted: function unmounted() {\n    onLocationChange(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].parsed.value);\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].off('ReportingPage.loadPage', onLoadPage);\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].off('Dashboard.loadDashboard', onLoadDashboard);\n  }\n});\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/index.ts":
/*!********************************************!*\
  !*** ./plugins/Dashboard/vue/src/index.ts ***!
  \********************************************/
/*! exports provided: DashboardStore, Dashboard */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Dashboard_Dashboard_store_adapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dashboard/Dashboard.store.adapter */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.adapter.ts\");\n/* harmony import */ var _Dashboard_Dashboard_adapter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dashboard/Dashboard.adapter */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.adapter.ts\");\n/* harmony import */ var _Dashboard_Dashboard_store__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Dashboard/Dashboard.store */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardStore\", function() { return _Dashboard_Dashboard_store__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/* harmony import */ var _Dashboard_Dashboard__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Dashboard/Dashboard */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Dashboard\", function() { return _Dashboard_Dashboard__WEBPACK_IMPORTED_MODULE_3__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://Dashboard/external_%22CoreHome%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://Dashboard/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});