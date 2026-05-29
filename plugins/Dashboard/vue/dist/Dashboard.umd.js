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
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class Dashboard_store_DashboardStore {
  constructor() {
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      dashboards: []
    }));
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    _defineProperty(this, "dashboards", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.dashboards));
    _defineProperty(this, "dashboardsPromise", null);
  }
  getDashboard(dashboardId) {
    return this.getAllDashboards().then(dashboards => dashboards.find(b => parseInt(`${b.id}`, 10) === parseInt(`${dashboardId}`, 10)));
  }
  getDashboardLayout(dashboardId) {
    return external_CoreHome_["AjaxHelper"].fetch({
      module: 'Dashboard',
      action: 'getDashboardLayout',
      idDashboard: dashboardId
    }, {
      withTokenInUrl: true
    });
  }
  reloadAllDashboards() {
    this.dashboardsPromise = null;
    return this.getAllDashboards();
  }
  getAllDashboards() {
    if (!this.dashboardsPromise) {
      this.dashboardsPromise = external_CoreHome_["AjaxHelper"].fetch({
        method: 'Dashboard.getDashboards',
        filter_limit: '-1'
      }).then(response => {
        if (response) {
          this.privateState.dashboards = response;
        }
        return this.dashboards.value;
      });
    }
    return this.dashboardsPromise;
  }
}
/* harmony default export */ var Dashboard_store = (new Dashboard_store_DashboardStore());
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/Dashboard/Dashboard.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const {
  $
} = window;
function renderDashboard(dashboardId, dashboard, layout) {
  const $settings = $('.dashboardSettings');
  $settings.show();
  window.initTopControls();
  // Embed dashboard / exported as widget
  if (!$('#topBars').length) {
    $settings.after($('#Dashboard'));
    $('#Dashboard ul li').removeClass('active');
    $(`#Dashboard_embeddedIndex_${dashboardId}`).addClass('active');
  }
  window.widgetsHelper.getAvailableWidgets();
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  $('#dashboardWidgetsArea').off('dashboardempty', window.showEmptyDashboardNotification).on('dashboardempty', window.showEmptyDashboardNotification).dashboard({
    idDashboard: dashboardId,
    layout,
    name: dashboard ? dashboard.name : ''
  });
  const divElements = $('#columnPreview').find('>div');
  divElements.each(function eachPreview() {
    const width = [];
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
  return new Promise(resolve => setTimeout(resolve)).then(() => Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall)).then(() => {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const dashboardElement = $('#dashboardWidgetsArea');
    dashboardElement.dashboard('destroyWidgets');
    dashboardElement.empty();
    return Promise.all([Dashboard_store.getDashboard(dashboardId), Dashboard_store.getDashboardLayout(dashboardId)]);
  }).then(([dashboard, layout]) => new Promise(resolve => {
    $(() => {
      renderDashboard(dashboardId, dashboard, layout);
      resolve();
    });
  }));
}
function clearDashboard() {
  $('.top_controls .dashboard-manager').hide();
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
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
  mounted(el, binding) {
    fetchDashboard(binding.value.idDashboard);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].parsed.value, parsed => {
      onLocationChange(parsed);
    });
    // ensure to not bind the event multiple times
    external_CoreHome_["Matomo"].off('Dashboard.loadDashboard', onLoadDashboard);
    external_CoreHome_["Matomo"].on('Dashboard.loadDashboard', onLoadDashboard);
  },
  unmounted() {
    onLocationChange(external_CoreHome_["MatomoUrl"].parsed.value);
    external_CoreHome_["Matomo"].off('Dashboard.loadDashboard', onLoadDashboard);
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=859f193a

const _hoisted_1 = ["title"];
const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-dashboard-customize"
}, null, -1);
const _hoisted_3 = {
  class: "dropdown positionInViewport"
};
const _hoisted_4 = {
  class: "submenu"
};
const _hoisted_5 = ["onClick", "disabled", "title", "data-action"];
const _hoisted_6 = ["onClick", "disabled", "title", "data-action"];
const _hoisted_7 = {
  class: "addWidget"
};
const _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-add1"
}, null, -1);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_AddWidgetModal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("AddWidgetModal");
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  const _directive_expand_on_click = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("expand-on-click");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    ref: "root",
    class: "dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings",
    onClick: _cache[2] || (_cache[2] = $event => _ctx.onOpen()),
    onFocusout: _cache[3] || (_cache[3] = (...args) => _ctx.onFocusOut && _ctx.onFocusOut(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    type: "button",
    class: "title",
    title: _ctx.translate('Dashboard_ManageDashboard'),
    tabindex: "4",
    ref: "expander"
  }, [_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_ManageDashboard')), 1)], 8, _hoisted_1)), [[_directive_tooltips]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.generalActions, (title, actionName) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: actionName
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      tabindex: "4",
      onClick: $event => _ctx.onClickAction($event, actionName),
      class: "generalAction",
      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,
      title: _ctx.actionTooltips[actionName] || undefined,
      "data-action": actionName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(title)), 9, _hoisted_5)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    tabindex: "4",
    class: "exportDashboard",
    "data-action": "exportDashboard",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.onClickExportDashboard())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_ExportThisDashboard')), 1)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dashboardActions, (title, actionName) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: actionName
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      tabindex: "4",
      onClick: $event => _ctx.onClickAction($event, actionName),
      disabled: _ctx.isActionDisabled[actionName] ? 'disabled' : undefined,
      title: _ctx.actionTooltips[actionName] || undefined,
      "data-action": actionName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(title)), 9, _hoisted_6)]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    tabindex: "4",
    class: "addWidget-button",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.openAddWidget())
  }, [_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_AddAWidget')), 1)])])])])), [[_directive_tooltips, {
    show: false
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_AddWidgetModal, {
    onSelect: _ctx.onWidgetSelected
  }, null, 8, ["onSelect"])], 32)), [[_directive_expand_on_click, {
    expander: 'expander',
    onExpand: _ctx.onExpand,
    onClosed: _ctx.onClosed
  }]]);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=template&id=859f193a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/AddWidgetModal.vue?vue&type=template&id=e54103e6

const AddWidgetModalvue_type_template_id_e54103e6_hoisted_1 = ["aria-label"];
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
}, null, -1);
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_3 = [AddWidgetModalvue_type_template_id_e54103e6_hoisted_2];
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_4 = {
  class: "add-widget-modal-title"
};
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_5 = {
  class: "add-widget-modal-body widgetpreview-base"
};
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_6 = {
  class: "add-widget-modal-categories"
};
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_7 = {
  class: "add-widget-modal-widgets"
};
const AddWidgetModalvue_type_template_id_e54103e6_hoisted_8 = {
  class: "add-widget-modal-preview"
};
function AddWidgetModalvue_type_template_id_e54103e6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_category_list = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("category-list");
  const _component_widgets_list = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("widgets-list");
  const _component_widget_preview = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("widget-preview");
  const _component_matomo_modal = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("matomo-modal");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_matomo_modal, {
    modelValue: _ctx.isOpen,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.isOpen = $event),
    classes: "add-widget-modal",
    "content-class": "add-widget-modal-content",
    "aria-label": _ctx.translate('Dashboard_AddAWidget'),
    onClosed: _ctx.onClosed
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "btn-close modal-close",
      "aria-label": _ctx.translate('General_Close'),
      onClick: _cache[0] || (_cache[0] = (...args) => _ctx.close && _ctx.close(...args))
    }, AddWidgetModalvue_type_template_id_e54103e6_hoisted_3, 8, AddWidgetModalvue_type_template_id_e54103e6_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", AddWidgetModalvue_type_template_id_e54103e6_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_AddAWidget')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddWidgetModalvue_type_template_id_e54103e6_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddWidgetModalvue_type_template_id_e54103e6_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_category_list, {
      categories: _ctx.categoryNames,
      "chosen-category": _ctx.chosenCategory,
      "onUpdate:chosenCategory": _ctx.onCategoryChosen,
      onConfirm: _ctx.focusWidgetList
    }, null, 8, ["categories", "chosen-category", "onUpdate:chosenCategory", "onConfirm"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddWidgetModalvue_type_template_id_e54103e6_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_widgets_list, {
      ref: "widgetsList",
      widgets: _ctx.widgetsInCategory,
      "chosen-widget-id": _ctx.hoveredWidgetId,
      "added-widgets": _ctx.addedWidgetIds,
      "existing-widget-ids": _ctx.existingWidgetIds,
      onHover: _ctx.onWidgetHover,
      onSelect: _ctx.onSelect
    }, null, 8, ["widgets", "chosen-widget-id", "added-widgets", "existing-widget-ids", "onHover", "onSelect"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", AddWidgetModalvue_type_template_id_e54103e6_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_widget_preview, {
      widget: _ctx.previewWidget,
      onSelect: _ctx.onSelect
    }, null, 8, ["widget", "onSelect"])])])]),
    _: 1
  }, 8, ["modelValue", "aria-label", "onClosed"]);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/AddWidgetModal.vue?vue&type=template&id=e54103e6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/CategoryList.vue?vue&type=template&id=790cc257

const CategoryListvue_type_template_id_790cc257_hoisted_1 = {
  class: "widgetpreview-base widgetpreview-categorylist"
};
const CategoryListvue_type_template_id_790cc257_hoisted_2 = ["onMouseover", "onClick", "onFocus", "onKeydown"];
function CategoryListvue_type_template_id_790cc257_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", CategoryListvue_type_template_id_790cc257_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.categories, category => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: category,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'widgetpreview-choosen': category === _ctx.chosenCategory
      }),
      role: "button",
      tabindex: "0",
      onMouseover: $event => _ctx.selectCategory(category),
      onClick: $event => _ctx.selectCategory(category),
      onFocus: $event => _ctx.selectCategory(category),
      onKeydown: [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.confirmCategory(category), ["prevent"]), ["enter"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.confirmCategory(category), ["prevent"]), ["space"])]
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(category), 43, CategoryListvue_type_template_id_790cc257_hoisted_2);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/CategoryList.vue?vue&type=template&id=790cc257

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/CategoryList.vue?vue&type=script&lang=ts

/* harmony default export */ var CategoryListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'CategoryList',
  props: {
    categories: {
      type: Array,
      required: true
    },
    chosenCategory: {
      type: String,
      default: null
    }
  },
  emits: ['update:chosenCategory', 'confirm'],
  methods: {
    selectCategory(category) {
      this.$emit('update:chosenCategory', category);
    },
    // Keyboard-only: selecting via Enter/Space also signals "advance focus to
    // the widgets list", so a keyboard user isn't stranded on the category
    // they just confirmed. Mouse/touch paths intentionally don't emit this.
    confirmCategory(category) {
      this.selectCategory(category);
      this.$emit('confirm');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/CategoryList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/CategoryList.vue



CategoryListvue_type_script_lang_ts.render = CategoryListvue_type_template_id_790cc257_render

/* harmony default export */ var CategoryList = (CategoryListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/WidgetsList.vue?vue&type=template&id=48f10d76

const WidgetsListvue_type_template_id_48f10d76_hoisted_1 = {
  ref: "list",
  class: "widgetpreview-base widgetpreview-widgetlist"
};
const WidgetsListvue_type_template_id_48f10d76_hoisted_2 = ["uniqueid", "onMouseenter", "onMouseleave", "onFocus", "onBlur", "onClick", "onKeydown"];
const WidgetsListvue_type_template_id_48f10d76_hoisted_3 = {
  class: "widgetpreview-widgetname"
};
const WidgetsListvue_type_template_id_48f10d76_hoisted_4 = {
  class: "widgetpreview-add-hint",
  "aria-hidden": "true"
};
function WidgetsListvue_type_template_id_48f10d76_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", WidgetsListvue_type_template_id_48f10d76_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.widgets, widget => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: widget.uniqueId,
      uniqueid: widget.uniqueId,
      role: "button",
      tabindex: "0",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'widgetpreview-choosen': widget.uniqueId === _ctx.chosenWidgetId,
        'widgetpreview-unavailable': _ctx.isUnavailable(widget)
      }),
      onMouseenter: $event => _ctx.onMouseEnter(widget),
      onMouseleave: $event => _ctx.onMouseLeave(widget),
      onFocus: $event => _ctx.onMouseEnter(widget),
      onBlur: $event => _ctx.onMouseLeave(widget),
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onRowClick(widget), ["prevent"]),
      onKeydown: [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onActivate(widget), ["prevent"]), ["enter"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onActivate(widget), ["prevent"]), ["space"])]
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", WidgetsListvue_type_template_id_48f10d76_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(widget.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", WidgetsListvue_type_template_id_48f10d76_hoisted_4, "+ " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Add')), 1)], 42, WidgetsListvue_type_template_id_48f10d76_hoisted_2);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetsList.vue?vue&type=template&id=48f10d76

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/WidgetsList.vue?vue&type=script&lang=ts


const HOVER_DELAY_MS = 400;
const KPI_METRIC_CATEGORY_ID = 'General_KpiMetric';
function hasHoverCapablePointer() {
  return typeof window !== 'undefined' && typeof window.matchMedia === 'function' && window.matchMedia('(any-hover: hover)').matches;
}
/* harmony default export */ var WidgetsListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'WidgetsList',
  props: {
    widgets: {
      type: Array,
      required: true
    },
    chosenWidgetId: {
      type: String,
      default: null
    },
    addedWidgets: {
      type: Object,
      default: () => new Set()
    },
    existingWidgetIds: {
      type: Object,
      default: () => new Set()
    }
  },
  emits: ['hover', 'select'],
  data() {
    return {
      hoverTimer: null,
      // Cached once: any hover-capable pointer gets desktop-like click-to-add
      // behaviour. Only pure no-hover environments use preview-first double-tap.
      supportsHover: hasHoverCapablePointer()
    };
  },
  methods: {
    translate: external_CoreHome_["translate"],
    isRepeatableWidget(widget) {
      var _widget$category;
      return ((_widget$category = widget.category) === null || _widget$category === void 0 ? void 0 : _widget$category.id) === KPI_METRIC_CATEGORY_ID;
    },
    isUnavailable(widget) {
      if (!widget.uniqueId) {
        return false;
      }
      if (this.addedWidgets.has(widget.uniqueId)) {
        return true;
      }
      if (this.isRepeatableWidget(widget)) {
        return false;
      }
      return this.existingWidgetIds.has(widget.uniqueId);
    },
    onMouseEnter(widget) {
      if (!widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
      const {
        uniqueId
      } = widget;
      this.hoverTimer = window.setTimeout(() => {
        this.hoverTimer = null;
        this.$emit('hover', uniqueId);
      }, HOVER_DELAY_MS);
    },
    onMouseLeave(widget) {
      // Matches the original jQuery widget menu: leaving an *unavailable* row keeps the
      // preview timer running so the user still gets a preview, while leaving any other
      // row cancels the pending preview.
      if (this.isUnavailable(widget)) {
        return;
      }
      this.clearHoverTimer();
    },
    onRowClick(widget) {
      if (!widget.uniqueId) {
        return;
      }
      // Rows flagged as `widgetpreview-unavailable` (already on the dashboard, or
      // added earlier in this modal session) stay clickable — the class is a
      // visual hint, not a hard block. Matches 5.x-dev's widgetMenu.js behaviour
      // where the click handler ignores the unavailable class.
      this.clearHoverTimer();
      // Touch / non-hover devices: first tap previews; second tap on the same row adds.
      if (!this.supportsHover && widget.uniqueId !== this.chosenWidgetId) {
        this.$emit('hover', widget.uniqueId);
        return;
      }
      this.$emit('select', widget.uniqueId);
    },
    // Keyboard activation (Enter / Space). Bypasses the touch double-tap branch in
    // onRowClick on purpose — a keypress is not a touch interaction, so a focused
    // row should add immediately even when supportsHover is false.
    onActivate(widget) {
      if (!widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
      this.$emit('select', widget.uniqueId);
    },
    focusFirst() {
      const list = this.$refs.list;
      const first = list === null || list === void 0 ? void 0 : list.querySelector('li');
      if (first instanceof HTMLElement) {
        first.focus();
      }
    },
    clearHoverTimer() {
      if (this.hoverTimer !== null) {
        window.clearTimeout(this.hoverTimer);
        this.hoverTimer = null;
      }
    }
  },
  beforeUnmount() {
    this.clearHoverTimer();
  }
}));
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetsList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetsList.vue



WidgetsListvue_type_script_lang_ts.render = WidgetsListvue_type_template_id_48f10d76_render

/* harmony default export */ var WidgetsList = (WidgetsListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/WidgetPreview.vue?vue&type=template&id=307f9336

const WidgetPreviewvue_type_template_id_307f9336_hoisted_1 = {
  class: "widgetpreview-preview"
};
const WidgetPreviewvue_type_template_id_307f9336_hoisted_2 = {
  key: 0,
  class: "widget"
};
const WidgetPreviewvue_type_template_id_307f9336_hoisted_3 = ["title"];
const WidgetPreviewvue_type_template_id_307f9336_hoisted_4 = {
  class: "widgetName"
};
const WidgetPreviewvue_type_template_id_307f9336_hoisted_5 = {
  class: "widgetContent"
};
function WidgetPreviewvue_type_template_id_307f9336_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Widget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Widget");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetPreviewvue_type_template_id_307f9336_hoisted_1, [_ctx.previewWidget ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", WidgetPreviewvue_type_template_id_307f9336_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "widgetTop",
    title: _ctx.translate('Dashboard_AddPreviewedWidget'),
    role: "button",
    tabindex: "0",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('select', _ctx.previewWidget.uniqueId)),
    onKeydown: [_cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('select', _ctx.previewWidget.uniqueId), ["prevent"]), ["enter"])), _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.$emit('select', _ctx.previewWidget.uniqueId), ["prevent"]), ["space"]))]
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", WidgetPreviewvue_type_template_id_307f9336_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Dashboard_WidgetPreview')), 1)], 40, WidgetPreviewvue_type_template_id_307f9336_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", WidgetPreviewvue_type_template_id_307f9336_hoisted_5, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Widget, {
    key: _ctx.previewWidget.uniqueId,
    widget: _ctx.previewWidget,
    widgetized: true,
    "suppress-notifications": true
  }, null, 8, ["widget"]))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetPreview.vue?vue&type=template&id=307f9336

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/WidgetPreview.vue?vue&type=script&lang=ts


/* harmony default export */ var WidgetPreviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'WidgetPreview',
  components: {
    Widget: external_CoreHome_["Widget"]
  },
  props: {
    widget: {
      type: Object,
      default: null
    }
  },
  emits: ['select'],
  computed: {
    previewWidget() {
      if (!this.widget) {
        return null;
      }
      const result = Object.assign(Object.assign({}, this.widget), {}, {
        parameters: this.getPreviewParameters(this.widget.parameters)
      });
      if (this.isContainerWidget(result)) {
        result.widgets = this.getPreviewChildren(result);
      }
      return result;
    }
  },
  mounted() {
    external_CoreHome_["Matomo"].on('widget:loaded', this.onWidgetLoaded);
  },
  unmounted() {
    external_CoreHome_["Matomo"].off('widget:loaded', this.onWidgetLoaded);
  },
  methods: {
    translate: external_CoreHome_["translate"],
    shouldDisableLink() {
      // disableLink is only forced for Widgetize embeds and the standalone body
      // matching widgetMenu.js.
      const urlFlag = external_CoreHome_["Matomo"].broadcast.getValueFromUrl('disableLink');
      if (urlFlag && urlFlag.length) {
        return true;
      }
      return !!document.querySelector('body#standalone');
    },
    getPreviewParameters(parameters = {}) {
      // Force widget=1 so previews render in widgetized layout, matching legacy widgetMenu.js.
      // showtitle=0 suppresses the server-rendered <h2>
      return Object.assign(Object.assign({}, parameters), {}, {
        widget: '1',
        showtitle: '0'
      }, this.shouldDisableLink() ? {
        disableLink: '1'
      } : {});
    },
    isContainerWidget(widget) {
      return !!widget.isContainer && Array.isArray(widget.widgets);
    },
    getPreviewChildren(widget) {
      var _widget$parameters;
      // Child widgets need the same widgetized parameters as the container preview.
      // Without this, nested widgets render as non-widgetized and may show titles again.
      const containerId = (_widget$parameters = widget.parameters) === null || _widget$parameters === void 0 ? void 0 : _widget$parameters.containerId;
      return widget.widgets.map(child => Object.assign(Object.assign({}, child), {}, {
        parameters: Object.assign(Object.assign({}, child.parameters), {}, {
          widget: '1'
        }, containerId ? {
          containerId
        } : {})
      }));
    },
    onWidgetLoaded(payload) {
      var _payload$parameters, _payload$element;
      // Only re-fire widget:create for the top-level preview;
      // nested container loads emit widget:loaded too.
      if (!this.widget || ((_payload$parameters = payload.parameters) === null || _payload$parameters === void 0 ? void 0 : _payload$parameters.uniqueId) !== this.widget.uniqueId) {
        return;
      }
      const root = this.$el;
      const loadedElement = payload === null || payload === void 0 || (_payload$element = payload.element) === null || _payload$element === void 0 ? void 0 : _payload$element[0];
      if (!root || !loadedElement || !root.contains(loadedElement)) {
        return;
      }
      const widget = root.querySelector('.widget');
      const widgetContent = widget === null || widget === void 0 ? void 0 : widget.querySelector('.widgetContent');
      if (!widget || !widgetContent) {
        return;
      }
      window.$(widgetContent).trigger('widget:create', [{
        element: window.$(widget)
      }]);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetPreview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/WidgetPreview.vue



WidgetPreviewvue_type_script_lang_ts.render = WidgetPreviewvue_type_template_id_307f9336_render

/* harmony default export */ var WidgetPreview = (WidgetPreviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/AddWidgetModal/AddWidgetModal.vue?vue&type=script&lang=ts





const OPEN_EVENT = 'Dashboard.AddWidget.open';
/* harmony default export */ var AddWidgetModalvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'AddWidgetModal',
  components: {
    MatomoModal: external_CoreHome_["MatomoModal"],
    CategoryList: CategoryList,
    WidgetsList: WidgetsList,
    WidgetPreview: WidgetPreview
  },
  emits: ['select'],
  data() {
    return {
      isOpen: false,
      chosenCategory: null,
      hoveredWidgetId: null,
      addedWidgetIds: new Set(),
      existingWidgetIds: new Set()
    };
  },
  computed: {
    widgets() {
      return external_CoreHome_["WidgetsStore"].widgets.value || {};
    },
    categoryNames() {
      return Object.keys(this.widgets);
    },
    widgetsInCategory() {
      if (!this.chosenCategory) {
        return [];
      }
      return this.widgets[this.chosenCategory] || [];
    },
    widgetsById() {
      // Rebuilds only when `WidgetsStore.widgets` mutates; `previewWidget` and
      // `onSelect` both consult this in O(1) instead of scanning every category.
      return new Map(Object.values(this.widgets).flat().filter(w => !!w.uniqueId).map(w => [w.uniqueId, w]));
    },
    previewWidget() {
      if (!this.hoveredWidgetId) {
        return null;
      }
      return this.widgetsById.get(this.hoveredWidgetId) || null;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    open() {
      // Snapshot the dashboard's currently-placed widget IDs once per open so
      // WidgetsList can flag them as unavailable without hitting the DOM on
      // every render. Refreshed each open; within-session additions are tracked
      // separately via `addedWidgetIds`.
      const ids = new Set();
      document.querySelectorAll('#dashboardWidgetsArea [widgetId]').forEach(el => {
        const id = el.getAttribute('widgetId');
        if (id) {
          ids.add(id);
        }
      });
      this.existingWidgetIds = ids;
      this.isOpen = true;
    },
    close() {
      this.isOpen = false;
    },
    onClosed() {
      this.chosenCategory = null;
      this.hoveredWidgetId = null;
      this.addedWidgetIds = new Set();
      this.existingWidgetIds = new Set();
    },
    onCategoryChosen(category) {
      if (this.chosenCategory === category) {
        return;
      }
      this.chosenCategory = category;
      this.hoveredWidgetId = null;
    },
    async focusWidgetList() {
      // Wait for the widgets list to re-render under the newly chosen category
      // before focusing — the prior li elements may have unmounted.
      await this.$nextTick();
      const widgetsList = this.$refs.widgetsList;
      if (widgetsList && typeof widgetsList.focusFirst === 'function') {
        widgetsList.focusFirst();
      }
    },
    onWidgetHover(uniqueId) {
      this.hoveredWidgetId = uniqueId;
    },
    onSelect(uniqueId) {
      const widget = this.widgetsById.get(uniqueId);
      if (widget) {
        // Keep the modal open so the user can add more widgets in one session;
        // the added row is greyed out via the `added-widgets` set on WidgetsList.
        this.addedWidgetIds.add(uniqueId);
        this.$emit('select', widget);
        return;
      }
      // WidgetsStore drives both the list and the lookup; a miss here means the cache
      // was unexpectedly cleared between render and click. Close anyway so the modal
      // cannot block follow-up interactions.
      console.warn(`Could not resolve dashboard widget "${uniqueId}" from cached metadata.`);
      this.close();
    }
  },
  mounted() {
    external_CoreHome_["Matomo"].on(OPEN_EVENT, this.open);
  },
  unmounted() {
    external_CoreHome_["Matomo"].off(OPEN_EVENT, this.open);
  }
}));
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/AddWidgetModal.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Dashboard/vue/src/AddWidgetModal/AddWidgetModal.vue



AddWidgetModalvue_type_script_lang_ts.render = AddWidgetModalvue_type_template_id_e54103e6_render

/* harmony default export */ var AddWidgetModal = (AddWidgetModalvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Dashboard/vue/src/DashboardSettings/DashboardSettings.vue?vue&type=script&lang=ts



const {
  $: DashboardSettingsvue_type_script_lang_ts_$
} = window;
const DASHBOARD_EXPORT_STORAGE_KEY = 'scheduledReports.dashboardExportId';
/* harmony default export */ var DashboardSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  name: 'DashboardSettings',
  components: {
    AddWidgetModal: AddWidgetModal
  },
  directives: {
    ExpandOnClick: external_CoreHome_["ExpandOnClick"],
    Tooltips: external_CoreHome_["Tooltips"]
  },
  data() {
    return {
      isActionDisabled: {},
      actionTooltips: {}
    };
  },
  setup() {
    const root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(() => {
      external_CoreHome_["Matomo"].postEvent('Dashboard.DashboardSettings.mounted', root.value);
      DashboardSettingsvue_type_script_lang_ts_$(root.value).hide(); // hide dashboard-manager initially (shown manually by Dashboard.ts)
    });
    return {
      root
    };
  },
  computed: {
    isUserNotAnonymous() {
      return !!external_CoreHome_["Matomo"].userLogin && external_CoreHome_["Matomo"].userLogin !== 'anonymous';
    },
    isSuperUser() {
      return this.isUserNotAnonymous && external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    isUserHasSomeAdminAccess() {
      return this.isUserNotAnonymous && external_CoreHome_["Matomo"].userHasSomeAdminAccess;
    },
    dashboardActions() {
      const result = {
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
    generalActions() {
      const result = {};
      if (this.isUserNotAnonymous) {
        result.createDashboard = 'Dashboard_CreateNewDashboard';
      }
      return result;
    }
  },
  methods: {
    onClickAction(event, action) {
      if (event.target.getAttribute('disabled')) {
        return;
      }
      window[action]();
    },
    onOpen() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      if (DashboardSettingsvue_type_script_lang_ts_$('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
        this.isActionDisabled.removeDashboard = true;
        this.actionTooltips.removeDashboard = Object(external_CoreHome_["translate"])('Dashboard_RemoveDefaultDashboardNotPossible');
      } else {
        this.isActionDisabled.removeDashboard = false;
        this.actionTooltips.removeDashboard = undefined;
      }
    },
    onExpand(event) {
      // Clicks triggered via keyboard (Enter/Space on the button) have detail === 0,
      // mouse clicks have detail >= 1. Only shift focus into the menu for keyboard opens.
      if (event.detail !== 0) {
        return;
      }
      this.$nextTick(() => {
        const firstAction = this.$refs.root.querySelector('.submenu button:not([disabled])');
        if (firstAction) {
          firstAction.focus();
        }
      });
    },
    onFocusOut(event) {
      const root = this.$refs.root;
      const newTarget = event.relatedTarget;
      if (newTarget && root.contains(newTarget)) {
        return;
      }
      root.classList.remove('expanded');
    },
    onClosed(event) {
      // Return focus to the trigger when the dropdown was dismissed via the Escape
      // key, so keyboard users keep their place. Enter/Space activation of buttons
      // produces a MouseEvent (synthetic click) and is handled by the browser
      // leaving focus on the activated element.
      if (!(event instanceof KeyboardEvent)) {
        return;
      }
      const expander = this.$refs.expander;
      if (expander) {
        expander.focus();
      }
    },
    openAddWidget() {
      // close the dashboard-manager dropdown when opening the modal
      this.$refs.root.classList.remove('expanded');
      external_CoreHome_["Matomo"].postEvent('Dashboard.AddWidget.open');
    },
    onWidgetSelected(widget) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      DashboardSettingsvue_type_script_lang_ts_$('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
    },
    redirectToCreateScheduledReports() {
      const query = Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value);
      delete query.category;
      delete query.subcategory;
      delete query.idDashboard;
      query.module = 'ScheduledReports';
      query.action = 'index';
      const hash = Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value);
      delete hash.category;
      delete hash.subcategory;
      delete hash.idDashboard;
      external_CoreHome_["MatomoUrl"].updateUrl(query, hash);
    },
    redirectToLoginPage() {
      const loginQuery = {
        module: external_CoreHome_["Matomo"].getLoginModule()
      };
      external_CoreHome_["MatomoUrl"].updateUrl(loginQuery);
    },
    onClickExportDashboard() {
      if (typeof sessionStorage !== 'undefined') {
        sessionStorage.removeItem(DASHBOARD_EXPORT_STORAGE_KEY);
      }
      if (this.isUserNotAnonymous) {
        const dashboardId = this.getCurrentDashboardId();
        if (dashboardId !== null && typeof sessionStorage !== 'undefined') {
          sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, String(dashboardId));
        }
        this.redirectToCreateScheduledReports();
        return;
      }
      // We do not persist dashboard id when user is anonymous
      this.redirectToLoginPage();
    },
    normalizeDashboardId(value) {
      const candidate = Array.isArray(value) ? value[0] : value;
      if (candidate === null || candidate === undefined) {
        return null;
      }
      const normalized = String(candidate).trim();
      if (!/^[1-9]\d*$/.test(normalized)) {
        return null;
      }
      return Number(normalized);
    },
    getCurrentDashboardId() {
      const fromSubcategory = this.normalizeDashboardId(external_CoreHome_["MatomoUrl"].getSearchParam('subcategory'));
      if (fromSubcategory !== null) {
        return fromSubcategory;
      }
      const fromQueryIdDashboard = this.normalizeDashboardId(external_CoreHome_["MatomoUrl"].urlParsed.value.idDashboard);
      if (fromQueryIdDashboard !== null) {
        return fromQueryIdDashboard;
      }
      return this.normalizeDashboardId(external_CoreHome_["MatomoUrl"].hashParsed.value.idDashboard);
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