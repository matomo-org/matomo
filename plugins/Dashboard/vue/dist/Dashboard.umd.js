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

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3 ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = [\"title\"];\n\nvar _hoisted_2 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n  class: \"icon icon-chevron-down\"\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_3 = {\n  class: \"dropdown positionInViewport\"\n};\nvar _hoisted_4 = {\n  class: \"submenu\"\n};\nvar _hoisted_5 = {\n  class: \"addWidget\"\n};\n\nvar _hoisted_6 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"ul\", {\n  class: \"widgetpreview-categorylist\"\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_7 = {\n  class: \"manageDashboard\"\n};\nvar _hoisted_8 = [\"onClick\", \"disabled\", \"title\", \"data-action\"];\nvar _hoisted_9 = [\"onClick\", \"disabled\", \"title\", \"data-action\"];\n\nvar _hoisted_10 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"ul\", {\n  class: \"widgetpreview-widgetlist\"\n}, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_11 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", {\n  class: \"widgetpreview-preview\"\n}, null, -1\n/* HOISTED */\n);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _directive_expand_on_click = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"expand-on-click\");\n\n  var _directive_tooltips = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"tooltips\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])((Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n    ref: \"root\",\n    class: \"dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings\",\n    onClick: _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.onOpen();\n    })\n  }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n    class: \"title\",\n    title: _ctx.translate('Dashboard_ManageDashboard'),\n    tabindex: \"4\",\n    ref: \"expander\"\n  }, [_hoisted_2, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Dashboard_Dashboard')), 1\n  /* TEXT */\n  )], 8\n  /* PROPS */\n  , _hoisted_1), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_3, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"ul\", _hoisted_4, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"li\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_5, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Dashboard_AddAWidget')), 1\n  /* TEXT */\n  ), _hoisted_6]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"li\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_7, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Dashboard_ManageDashboard')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"ul\", null, [(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.dashboardActions, function (title, actionName) {\n    return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"li\", {\n      key: actionName,\n      onClick: function onClick($event) {\n        return _ctx.onClickAction($event, actionName);\n      },\n      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,\n      title: _ctx.actionTooltips[actionName] || undefined,\n      \"data-action\": actionName\n    }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate(title)), 9\n    /* TEXT, PROPS */\n    , _hoisted_8);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))])]), (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.generalActions, function (title, actionName) {\n    return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"li\", {\n      key: actionName,\n      onClick: function onClick($event) {\n        return _ctx.onClickAction($event, actionName);\n      },\n      class: \"generalAction\",\n      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,\n      title: _ctx.actionTooltips[actionName] || undefined,\n      \"data-action\": actionName\n    }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate(title)), 9\n    /* TEXT, PROPS */\n    , _hoisted_9);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))]), _hoisted_10, _hoisted_11])], 512\n  /* NEED_PATCH */\n  )), [[_directive_expand_on_click, {\n    expander: 'expander',\n    onClosed: _ctx.onClose\n  }], [_directive_tooltips, {\n    show: false\n  }]]);\n}\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n\nvar _window = window,\n    $ = _window.$;\n\nfunction isWidgetAvailable(widgetUniqueId) {\n  return !$('#dashboardWidgetsArea').find(\"[widgetId=\\\"\".concat(widgetUniqueId, \"\\\"]\")).length;\n}\n\nfunction widgetSelected(widget) {\n  // for UI tests (see DashboardManager_spec.js)\n  // eslint-disable-next-line @typescript-eslint/no-explicit-any\n  if (window.MATOMO_DASHBOARD_SETTINGS_WIDGET_SELECTED_NOOP) {\n    return;\n  } // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n\n  $('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  directives: {\n    ExpandOnClick: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ExpandOnClick\"],\n    Tooltips: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Tooltips\"]\n  },\n  data: function data() {\n    return {\n      isActionDisabled: {},\n      actionTooltips: {}\n    };\n  },\n  setup: function setup() {\n    // $.widgetMenu will modify the jquery object it's given, so we have to save it and reuse\n    // it to call functions.\n    // eslint-disable-next-line @typescript-eslint/no-explicit-any\n    var rootJQuery = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"ref\"])(null);\n    var root = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"ref\"])(null);\n    Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"onMounted\"])(function () {\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].postEvent('Dashboard.DashboardSettings.mounted', root.value);\n      rootJQuery.value = $(root.value);\n      rootJQuery.value.widgetPreview({\n        isWidgetAvailable: isWidgetAvailable,\n        onSelect: function onSelect(widgetUniqueId) {\n          window.widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, function (widget) {\n            root.value.click(); // close selector\n\n            widgetSelected(widget);\n          });\n        },\n        resetOnSelect: true\n      });\n      rootJQuery.value.hide(); // hide dashboard-manager initially (shown manually by Dashboard.ts)\n    });\n    return {\n      root: root,\n      rootJQuery: rootJQuery\n    };\n  },\n  computed: {\n    isUserNotAnonymous: function isUserNotAnonymous() {\n      return !!CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].userLogin && CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].userLogin !== 'anonymous';\n    },\n    isSuperUser: function isSuperUser() {\n      return this.isUserNotAnonymous && CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].hasSuperUserAccess;\n    },\n    isUserHasSomeAdminAccess: function isUserHasSomeAdminAccess() {\n      return this.isUserNotAnonymous && CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].userHasSomeAdminAccess;\n    },\n    dashboardActions: function dashboardActions() {\n      var result = {\n        resetDashboard: 'Dashboard_ResetDashboard',\n        showChangeDashboardLayoutDialog: 'Dashboard_ChangeDashboardLayout'\n      };\n\n      if (this.isUserNotAnonymous) {\n        result.renameDashboard = 'Dashboard_RenameDashboard';\n        result.removeDashboard = 'Dashboard_RemoveDashboard';\n      }\n\n      if (this.isSuperUser) {\n        result.setAsDefaultWidgets = 'Dashboard_SetAsDefaultWidgets';\n      }\n\n      if (this.isUserHasSomeAdminAccess) {\n        result.copyDashboardToUser = 'Dashboard_CopyDashboardToUser';\n      }\n\n      return result;\n    },\n    generalActions: function generalActions() {\n      var result = {};\n\n      if (this.isUserNotAnonymous) {\n        result.createDashboard = 'Dashboard_CreateNewDashboard';\n      }\n\n      return result;\n    }\n  },\n  methods: {\n    onClickAction: function onClickAction(event, action) {\n      if (event.target.getAttribute('disabled')) {\n        return;\n      }\n\n      window[action]();\n    },\n    onOpen: function onOpen() {\n      // eslint-disable-next-line @typescript-eslint/no-explicit-any\n      if ($('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {\n        this.isActionDisabled.removeDashboard = true;\n        this.actionTooltips.removeDashboard = Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('Dashboard_RemoveDefaultDashboardNotPossible');\n      } else {\n        this.isActionDisabled.removeDashboard = false;\n        this.actionTooltips.removeDashboard = undefined;\n      }\n    },\n    onClose: function onClose() {\n      this.rootJQuery.widgetPreview('reset');\n    }\n  }\n}));\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: DashboardStore, Dashboard, DashboardSettings */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Dashboard/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardStore\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"DashboardStore\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Dashboard\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"Dashboard\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardSettings\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"DashboardSettings\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Dashboard/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

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
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _Dashboard_store__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Dashboard.store */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts\");\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== \"undefined\" && arr[Symbol.iterator] || arr[\"@@iterator\"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\nvar _window = window,\n    $ = _window.$;\n\nfunction renderDashboard(dashboardId, dashboard, layout) {\n  var $settings = $('.dashboardSettings');\n  $settings.show();\n  window.initTopControls(); // Embed dashboard / exported as widget\n\n  if (!$('#topBars').length) {\n    $settings.after($('#Dashboard'));\n    $('#Dashboard ul li').removeClass('active');\n    $(\"#Dashboard_embeddedIndex_\".concat(dashboardId)).addClass('active');\n  }\n\n  window.widgetsHelper.getAvailableWidgets(); // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n  $('#dashboardWidgetsArea').off('dashboardempty', window.showEmptyDashboardNotification).on('dashboardempty', window.showEmptyDashboardNotification).dashboard({\n    idDashboard: dashboardId,\n    layout: layout,\n    name: dashboard ? dashboard.name : ''\n  });\n  var divElements = $('#columnPreview').find('>div');\n  divElements.each(function eachPreview() {\n    var width = [];\n    $('div', this).each(function eachDiv() {\n      width.push(this.className.replace(/width-/, ''));\n    });\n    $(this).attr('layout', width.join('-'));\n  });\n  divElements.off('click.renderDashboard');\n  divElements.on('click.renderDashboard', function onRenderDashboard() {\n    divElements.removeClass('choosen');\n    $(this).addClass('choosen');\n  });\n}\n\nfunction fetchDashboard(dashboardId) {\n  return new Promise(function (resolve) {\n    return setTimeout(resolve);\n  }).then(function () {\n    return Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall);\n  }).then(function () {\n    window.globalAjaxQueue.abort(); // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n    var dashboardElement = $('#dashboardWidgetsArea');\n    dashboardElement.dashboard('destroyWidgets');\n    dashboardElement.empty();\n    return Promise.all([_Dashboard_store__WEBPACK_IMPORTED_MODULE_2__[\"default\"].getDashboard(dashboardId), _Dashboard_store__WEBPACK_IMPORTED_MODULE_2__[\"default\"].getDashboardLayout(dashboardId)]);\n  }).then(function (_ref) {\n    var _ref2 = _slicedToArray(_ref, 2),\n        dashboard = _ref2[0],\n        layout = _ref2[1];\n\n    return new Promise(function (resolve) {\n      $(function () {\n        renderDashboard(dashboardId, dashboard, layout);\n        resolve();\n      });\n    });\n  });\n}\n\nfunction clearDashboard() {\n  $('.top_controls .dashboard-manager').hide(); // eslint-disable-next-line @typescript-eslint/no-explicit-any\n\n  $('#dashboardWidgetsArea').dashboard('destroy');\n}\n\nfunction onLocationChange(parsed) {\n  if (parsed.module !== 'Widgetize' && parsed.category !== 'Dashboard_Dashboard') {\n    // we remove the dashboard only if we no longer show a dashboard.\n    clearDashboard();\n  }\n}\n\nfunction onLoadDashboard(idDashboard) {\n  fetchDashboard(idDashboard);\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  mounted: function mounted(el, binding) {\n    fetchDashboard(binding.value.idDashboard);\n    Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"watch\"])(function () {\n      return CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].parsed.value;\n    }, function (parsed) {\n      onLocationChange(parsed);\n    });\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].on('Dashboard.loadDashboard', onLoadDashboard);\n  },\n  unmounted: function unmounted() {\n    onLocationChange(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoUrl\"].parsed.value);\n    CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Matomo\"].off('Dashboard.loadDashboard', onLoadDashboard);\n  }\n});\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue":
/*!***************************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _DashboardSettings_vue_vue_type_template_id_53ed8bc3__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./DashboardSettings.vue?vue&type=template&id=53ed8bc3 */ \"./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3\");\n/* harmony import */ var _DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./DashboardSettings.vue?vue&type=script&lang=ts */ \"./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _DashboardSettings_vue_vue_type_template_id_53ed8bc3__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts":
/*!***************************************************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts ***!
  \***************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./DashboardSettings.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_DashboardSettings_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3":
/*!*********************************************************************************************************!*\
  !*** ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3 ***!
  \*********************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_DashboardSettings_vue_vue_type_template_id_53ed8bc3__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./DashboardSettings.vue?vue&type=template&id=53ed8bc3 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=53ed8bc3\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_DashboardSettings_vue_vue_type_template_id_53ed8bc3__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?");

/***/ }),

/***/ "./plugins/Dashboard/vue/src/index.ts":
/*!********************************************!*\
  !*** ./plugins/Dashboard/vue/src/index.ts ***!
  \********************************************/
/*! exports provided: DashboardStore, Dashboard, DashboardSettings */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Dashboard_Dashboard_store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Dashboard/Dashboard.store */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardStore\", function() { return _Dashboard_Dashboard_store__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* harmony import */ var _Dashboard_Dashboard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Dashboard/Dashboard */ \"./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"Dashboard\", function() { return _Dashboard_Dashboard__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/* harmony import */ var _DashboardSettings_DashboardSettings_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./DashboardSettings/DashboardSettings.vue */ \"./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"DashboardSettings\", function() { return _DashboardSettings_DashboardSettings_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n//# sourceURL=webpack://Dashboard/./plugins/Dashboard/vue/src/index.ts?");

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
