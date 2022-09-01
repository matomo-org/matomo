(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Dashboard"] = factory(require("CoreHome"), require("vue"));
	else
		root["Dashboard"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Dashboard/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "DashboardStore", function() { return /* reexport */ Dashboard_store; });
__webpack_require__.d(__webpack_exports__, "Dashboard", function() { return /* reexport */ Dashboard; });

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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var Dashboard_store_DashboardStore = /*#__PURE__*/function () {
  function DashboardStore() {
    var _this = this;

    _classCallCheck(this, DashboardStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      dashboards: []
    }));

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "dashboards", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.dashboards;
    }));

    _defineProperty(this, "dashboardsPromise", null);
  }

  _createClass(DashboardStore, [{
    key: "getDashboard",
    value: function getDashboard(dashboardId) {
      return this.getAllDashboards().then(function (dashboards) {
        return dashboards.find(function (b) {
          return parseInt("".concat(b.id), 10) === parseInt("".concat(dashboardId), 10);
        });
      });
    }
  }, {
    key: "getDashboardLayout",
    value: function getDashboardLayout(dashboardId) {
      return external_CoreHome_["AjaxHelper"].fetch({
        module: 'Dashboard',
        action: 'getDashboardLayout',
        idDashboard: dashboardId
      }, {
        withTokenInUrl: true
      });
    }
  }, {
    key: "reloadAllDashboards",
    value: function reloadAllDashboards() {
      this.dashboardsPromise = null;
      return this.getAllDashboards();
    }
  }, {
    key: "getAllDashboards",
    value: function getAllDashboards() {
      var _this2 = this;

      if (!this.dashboardsPromise) {
        this.dashboardsPromise = external_CoreHome_["AjaxHelper"].fetch({
          method: 'Dashboard.getDashboards',
          filter_limit: '-1'
        }).then(function (response) {
          if (response) {
            _this2.privateState.dashboards = response;
          }

          return _this2.dashboards.value;
        });
      }

      return this.dashboardsPromise;
    }
  }]);

  return DashboardStore;
}();

/* harmony default export */ var Dashboard_store = (new Dashboard_store_DashboardStore());
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/Dashboard/Dashboard.store.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

window.angular.module('piwikApp.service').factory('dashboardsModel', function () {
  return Dashboard_store;
});
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var _window = window,
    $ = _window.$;

function renderDashboard(dashboardId, dashboard, layout) {
  var $settings = $('.dashboardSettings');
  $settings.show();
  window.initTopControls(); // Embed dashboard / exported as widget

  if (!$('#topBars').length) {
    $settings.after($('#Dashboard'));
    $('#Dashboard ul li').removeClass('active');
    $("#Dashboard_embeddedIndex_".concat(dashboardId)).addClass('active');
  }

  window.widgetsHelper.getAvailableWidgets(); // eslint-disable-next-line @typescript-eslint/no-explicit-any

  $('#dashboardWidgetsArea').off('dashboardempty', window.showEmptyDashboardNotification).on('dashboardempty', window.showEmptyDashboardNotification).dashboard({
    idDashboard: dashboardId,
    layout: layout,
    name: dashboard ? dashboard.name : ''
  });
  var divElements = $('#columnPreview').find('>div');
  divElements.each(function eachPreview() {
    var width = [];
    $('div', this).each(function eachDiv() {
      width.push(this.className.replace(/width-/, ''));
    });
    $(this).attr('layout', width.join('-'));
  });
  divElements.off('click.renderDashboard');
  divElements.on('click.renderDashboard', function onRenderDashboard() {
    divElements.removeClass('choosen');
    $(this).addClass('choosen');
  });
}

function fetchDashboard(dashboardId) {
  window.globalAjaxQueue.abort();
  return new Promise(function (resolve) {
    return setTimeout(resolve);
  }).then(function () {
    return Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall);
  }).then(function () {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    var dashboardElement = $('#dashboardWidgetsArea');
    dashboardElement.dashboard('destroyWidgets');
    dashboardElement.empty();
    return Promise.all([Dashboard_store.getDashboard(dashboardId), Dashboard_store.getDashboardLayout(dashboardId)]);
  }).then(function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        dashboard = _ref2[0],
        layout = _ref2[1];

    return new Promise(function (resolve) {
      $(function () {
        renderDashboard(dashboardId, dashboard, layout);
        resolve();
      });
    });
  });
}

function clearDashboard() {
  $('.top_controls .dashboard-manager').hide(); // eslint-disable-next-line @typescript-eslint/no-explicit-any

  $('#dashboardWidgetsArea').dashboard('destroy');
}

function onLocationChange(parsed) {
  if (parsed.module !== 'Widgetize' && parsed.category !== 'Dashboard_Dashboard') {
    // we remove the dashboard only if we no longer show a dashboard.
    clearDashboard();
  }
}

function onLoadPage(params) {
  if (params.category === 'Dashboard_Dashboard' && $.isNumeric(params.subcategory)) {
    params.promise = fetchDashboard(parseInt(params.subcategory, 10));
  }
}

function onLoadDashboard(idDashboard) {
  fetchDashboard(idDashboard);
}

/* harmony default export */ var Dashboard = ({
  mounted: function mounted(el, binding) {
    fetchDashboard(binding.value.idDashboard);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return external_CoreHome_["MatomoUrl"].parsed.value;
    }, function (parsed) {
      onLocationChange(parsed);
    }); // load dashboard directly since it will be faster than going through reporting page API

    external_CoreHome_["Matomo"].on('ReportingPage.loadPage', onLoadPage);
    external_CoreHome_["Matomo"].on('Dashboard.loadDashboard', onLoadDashboard);
  },
  unmounted: function unmounted() {
    onLocationChange(external_CoreHome_["MatomoUrl"].parsed.value);
    external_CoreHome_["Matomo"].off('ReportingPage.loadPage', onLoadPage);
    external_CoreHome_["Matomo"].off('Dashboard.loadDashboard', onLoadDashboard);
  }
});
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/Dashboard/Dashboard.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikDashboard() {
  return {
    restrict: 'A',
    scope: {
      dashboardid: '=',
      layout: '='
    },
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    link: function expandOnClickLink(scope, element) {
      var binding = {
        instance: null,
        value: {
          idDashboard: scope.dashboardid,
          layout: scope.layout
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      Dashboard.mounted(element[0], binding); // using scope destroy instead of element destroy event, since piwik-dashboard elements
      // are removed manually, outside of angularjs/vue workflow, so element destroy is not
      // triggered

      scope.$on('$destroy', function () {
        Dashboard.unmounted();
      });
    }
  };
}
window.angular.module('piwikApp').directive('piwikDashboard', piwikDashboard);
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/index.ts
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
//# sourceMappingURL=Dashboard.umd.js.map