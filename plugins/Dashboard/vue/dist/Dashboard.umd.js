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
__webpack_require__.d(__webpack_exports__, "DashboardSettings", function() { return /* reexport */ DashboardSettings; });

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
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    });
    external_CoreHome_["Matomo"].on('Dashboard.loadDashboard', onLoadDashboard);
  },
  unmounted: function unmounted() {
    onLocationChange(external_CoreHome_["MatomoUrl"].parsed.value);
    external_CoreHome_["Matomo"].off('Dashboard.loadDashboard', onLoadDashboard);
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=7aeb38d7

var _hoisted_1 = ["title"];

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-chevron-down"
}, null, -1);

var _hoisted_3 = {
  class: "dropdown positionInViewport"
};
var _hoisted_4 = {
  class: "submenu"
};
var _hoisted_5 = {
  class: "addWidget"
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
  class: "widgetpreview-categorylist"
}, null, -1);

var _hoisted_7 = {
  class: "manageDashboard"
};
var _hoisted_8 = ["onClick", "disabled", "title", "data-action"];
var _hoisted_9 = ["onClick", "disabled", "title", "data-action"];

var _hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", {
  class: "widgetpreview-widgetlist"
}, null, -1);

var _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "widgetpreview-preview"
}, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_expand_on_click = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-click");

  var _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    ref: "root",
    class: "dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onOpen();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "title",
    title: _ctx.translate('Dashboard_ManageDashboard'),
    tabindex: "4",
    ref: "expander"
  }, [_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_Dashboard')), 1)], 8, _hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_AddAWidget')), 1), _hoisted_6]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_ManageDashboard')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dashboardActions, function (title, actionName) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: actionName,
      onClick: function onClick($event) {
        return _ctx.onClickAction($event, actionName);
      },
      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,
      title: _ctx.actionTooltips[actionName] || undefined,
      "data-action": actionName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(title)), 9, _hoisted_8);
  }), 128))])]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.generalActions, function (title, actionName) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: actionName,
      onClick: function onClick($event) {
        return _ctx.onClickAction($event, actionName);
      },
      class: "generalAction",
      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,
      title: _ctx.actionTooltips[actionName] || undefined,
      "data-action": actionName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(title)), 9, _hoisted_9);
  }), 128))]), _hoisted_10, _hoisted_11])], 512)), [[_directive_expand_on_click, {
    expander: 'expander',
    onClosed: _ctx.onClose
  }], [_directive_tooltips, {
    show: false
  }]]);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=7aeb38d7

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts


var DashboardSettingsvue_type_script_lang_ts_window = window,
    DashboardSettingsvue_type_script_lang_ts_$ = DashboardSettingsvue_type_script_lang_ts_window.$;

function isWidgetAvailable(widgetUniqueId) {
  return !DashboardSettingsvue_type_script_lang_ts_$('#dashboardWidgetsArea').find("[widgetId=\"".concat(widgetUniqueId, "\"]")).length;
}

function widgetSelected(widget) {
  // for UI tests (see DashboardManager_spec.js)
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  if (window.MATOMO_DASHBOARD_SETTINGS_WIDGET_SELECTED_NOOP) {
    return;
  } // eslint-disable-next-line @typescript-eslint/no-explicit-any


  DashboardSettingsvue_type_script_lang_ts_$('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
}

/* harmony default export */ var DashboardSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  directives: {
    ExpandOnClick: external_CoreHome_["ExpandOnClick"],
    Tooltips: external_CoreHome_["Tooltips"]
  },
  data: function data() {
    return {
      isActionDisabled: {},
      actionTooltips: {}
    };
  },
  setup: function setup() {
    // $.widgetMenu will modify the jquery object it's given, so we have to save it and reuse
    // it to call functions.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    var rootJQuery = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    var root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    var createWidgetPreview = function createWidgetPreview() {
      rootJQuery.value.widgetPreview({
        isWidgetAvailable: isWidgetAvailable,
        onSelect: function onSelect(widgetUniqueId) {
          window.widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, function (widget) {
            root.value.click(); // close selector

            widgetSelected(widget);
          });
        },
        resetOnSelect: true
      });
    };

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      external_CoreHome_["Matomo"].postEvent('Dashboard.DashboardSettings.mounted', root.value);
      rootJQuery.value = DashboardSettingsvue_type_script_lang_ts_$(root.value);
      createWidgetPreview(); // When the available widgets list is reloaded, re-create the widget preview to include update

      external_CoreHome_["Matomo"].on('WidgetsStore.reloaded', function () {
        createWidgetPreview();
      });
      rootJQuery.value.hide(); // hide dashboard-manager initially (shown manually by Dashboard.ts)
    });
    return {
      root: root,
      rootJQuery: rootJQuery
    };
  },
  computed: {
    isUserNotAnonymous: function isUserNotAnonymous() {
      return !!external_CoreHome_["Matomo"].userLogin && external_CoreHome_["Matomo"].userLogin !== 'anonymous';
    },
    isSuperUser: function isSuperUser() {
      return this.isUserNotAnonymous && external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    isUserHasSomeAdminAccess: function isUserHasSomeAdminAccess() {
      return this.isUserNotAnonymous && external_CoreHome_["Matomo"].userHasSomeAdminAccess;
    },
    dashboardActions: function dashboardActions() {
      var result = {
        resetDashboard: 'Dashboard_ResetDashboard',
        showChangeDashboardLayoutDialog: 'Dashboard_ChangeDashboardLayout'
      };

      if (this.isUserNotAnonymous) {
        result.renameDashboard = 'Dashboard_RenameDashboard';
        result.removeDashboard = 'Dashboard_RemoveDashboard';
      }

      if (this.isSuperUser) {
        result.setAsDefaultWidgets = 'Dashboard_SetAsDefaultWidgets';
      }

      if (this.isUserHasSomeAdminAccess) {
        result.copyDashboardToUser = 'Dashboard_CopyDashboardToUser';
      }

      return result;
    },
    generalActions: function generalActions() {
      var result = {};

      if (this.isUserNotAnonymous) {
        result.createDashboard = 'Dashboard_CreateNewDashboard';
      }

      return result;
    }
  },
  methods: {
    onClickAction: function onClickAction(event, action) {
      if (event.target.getAttribute('disabled')) {
        return;
      }

      window[action]();
    },
    onOpen: function onOpen() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      if (DashboardSettingsvue_type_script_lang_ts_$('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
        this.isActionDisabled.removeDashboard = true;
        this.actionTooltips.removeDashboard = Object(external_CoreHome_["translate"])('Dashboard_RemoveDefaultDashboardNotPossible');
      } else {
        this.isActionDisabled.removeDashboard = false;
        this.actionTooltips.removeDashboard = undefined;
      }
    },
    onClose: function onClose() {
      this.rootJQuery.widgetPreview('reset');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue



DashboardSettingsvue_type_script_lang_ts.render = render

/* harmony default export */ var DashboardSettings = (DashboardSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/index.ts
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
//# sourceMappingURL=Dashboard.umd.js.map