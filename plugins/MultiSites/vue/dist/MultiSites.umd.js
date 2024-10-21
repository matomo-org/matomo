(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["MultiSites"] = factory(require("CoreHome"), require("vue"));
	else
		root["MultiSites"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/MultiSites/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "AllWebsitesDashboard", function() { return /* reexport */ AllWebsitesDashboard; });
__webpack_require__.d(__webpack_exports__, "MultisitesSite", function() { return /* reexport */ MultisitesSite; });
__webpack_require__.d(__webpack_exports__, "DashboadStore", function() { return /* reexport */ Dashboard_store; });
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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.vue?vue&type=template&id=75bcc548

const _hoisted_1 = {
  class: "dashboardHeader"
};
const _hoisted_2 = {
  class: "card-title"
};
const _hoisted_3 = {
  key: 0,
  id: "periodString",
  class: "borderedControl"
};
const _hoisted_4 = {
  class: "dashboardControls"
};
const _hoisted_5 = {
  class: "siteSearch"
};
const _hoisted_6 = ["placeholder"];
const _hoisted_7 = ["title"];
const _hoisted_8 = ["href"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_PeriodSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PeriodSelector");
  const _component_KPICardContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("KPICardContainer");
  const _component_SitesTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SitesTable");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h1", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.translate('MultiSites_AllWebsitesDashboardTitle')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_AllWebsitesDashboardTitle')), 1)]),
    _: 1
  }, 8, ["feature-name"])]), !_ctx.isWidgetized ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PeriodSelector, {
    periods: _ctx.selectablePeriods
  }, null, 8, ["periods"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_KPICardContainer, {
    "is-loading": _ctx.isLoadingKPIs,
    "model-value": _ctx.kpis
  }, null, 8, ["is-loading", "model-value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    onKeydown: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])($event => _ctx.searchSite(_ctx.searchTerm), ["enter"])),
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.searchTerm = $event),
    placeholder: _ctx.translate('Actions_SubmenuSitesearch')
  }, null, 40, _hoisted_6), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search",
    onClick: _cache[2] || (_cache[2] = $event => _ctx.searchSite(_ctx.searchTerm)),
    title: _ctx.translate('General_ClickToSearch')
  }, null, 8, _hoisted_7)]), !_ctx.isWidgetized && _ctx.isUserAllowedToAddSite ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "btn",
    href: _ctx.addSiteUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AddSite')), 9, _hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SitesTable, {
    "display-revenue": _ctx.displayRevenue,
    "display-sparklines": _ctx.displaySparklines
  }, null, 8, ["display-revenue", "display-sparklines"])], 64);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.vue?vue&type=template&id=75bcc548

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.store.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const DEFAULT_SORT_ORDER = 'desc';
const DEFAULT_SORT_COLUMN = 'nb_visits';
class AllWebsitesDashboard_store_DashboardStore {
  constructor() {
    _defineProperty(this, "fetchAbort", null);
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      dashboardKPIs: {
        evolutionPeriod: 'day',
        hits: '?',
        hitsEvolution: '',
        hitsTrend: 0,
        pageviews: '?',
        pageviewsEvolution: '',
        pageviewsTrend: 0,
        revenue: '?',
        revenueEvolution: '',
        revenueTrend: 0,
        visits: '?',
        visitsEvolution: '',
        visitsTrend: 0
      },
      dashboardSites: [],
      errorLoading: false,
      isLoadingKPIs: false,
      isLoadingSites: false,
      numSites: 0,
      paginationCurrentPage: 0,
      sparklineDate: '',
      sortColumn: DEFAULT_SORT_COLUMN,
      sortOrder: DEFAULT_SORT_ORDER
    }));
    _defineProperty(this, "autoRefreshInterval", 0);
    _defineProperty(this, "autoRefreshTimeout", null);
    _defineProperty(this, "pageSize", 25);
    _defineProperty(this, "searchTerm", '');
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    _defineProperty(this, "numberOfPages", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Math.ceil(this.state.value.numSites / this.pageSize - 1)));
    _defineProperty(this, "currentPagingOffset", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Math.ceil(this.state.value.paginationCurrentPage * this.pageSize)));
    _defineProperty(this, "paginationLowerBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (this.state.value.numSites === 0) {
        return 0;
      }
      return 1 + this.currentPagingOffset.value;
    }));
    _defineProperty(this, "paginationUpperBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (this.state.value.numSites === 0) {
        return 0;
      }
      const end = this.pageSize + this.currentPagingOffset.value;
      const max = this.state.value.numSites;
      if (end < max) {
        return end;
      }
      return max;
    }));
  }
  reloadDashboard() {
    this.privateState.sortColumn = DEFAULT_SORT_COLUMN;
    this.privateState.sortOrder = DEFAULT_SORT_ORDER;
    this.privateState.paginationCurrentPage = 0;
    this.refreshData();
  }
  navigateNextPage() {
    if (this.privateState.paginationCurrentPage === this.numberOfPages.value) {
      return;
    }
    this.privateState.paginationCurrentPage += 1;
    this.refreshData(true);
  }
  navigatePreviousPage() {
    if (this.privateState.paginationCurrentPage === 0) {
      return;
    }
    this.privateState.paginationCurrentPage -= 1;
    this.refreshData(true);
  }
  searchSite(term) {
    this.searchTerm = term;
    this.privateState.paginationCurrentPage = 0;
    this.refreshData(true);
  }
  setAutoRefreshInterval(interval) {
    this.autoRefreshInterval = interval;
  }
  setPageSize(size) {
    this.pageSize = size;
  }
  sortBy(column) {
    if (this.privateState.sortColumn === column) {
      this.privateState.sortOrder = this.privateState.sortOrder === 'desc' ? 'asc' : 'desc';
    } else {
      this.privateState.sortOrder = column === 'label' ? 'asc' : 'desc';
    }
    this.privateState.sortColumn = column;
    this.refreshData(true);
  }
  cancelAutoRefresh() {
    if (!this.autoRefreshTimeout) {
      return;
    }
    clearTimeout(this.autoRefreshTimeout);
    this.autoRefreshTimeout = null;
  }
  refreshData(onlySites = false) {
    if (this.fetchAbort) {
      this.fetchAbort.abort();
      this.fetchAbort = null;
      this.cancelAutoRefresh();
    }
    this.fetchAbort = new AbortController();
    this.privateState.errorLoading = false;
    this.privateState.isLoadingKPIs = !onlySites;
    this.privateState.isLoadingSites = true;
    const params = {
      method: 'MultiSites.mockDashboardData',
      filter_limit: this.pageSize,
      filter_offset: this.currentPagingOffset.value,
      filter_sort_column: this.privateState.sortColumn,
      filter_sort_order: this.privateState.sortOrder,
      showColumns: ['hits_evolution', 'hits_evolution_trend', 'label', 'nb_hits', 'nb_pageviews', 'nb_visits', 'pageviews_evolution', 'pageviews_evolution_trend', 'revenue', 'revenue_evolution', 'revenue_evolution_trend', 'visits_evolution', 'visits_evolution_trend'].join(',')
    };
    if (this.searchTerm) {
      params.pattern = this.searchTerm;
    }
    return external_CoreHome_["AjaxHelper"].fetch(params, {
      abortController: this.fetchAbort
    }).then(response => {
      if (!onlySites) {
        this.updateDashboardKPIs(response);
      }
      this.updateDashboardSites(response);
    }).catch(() => {
      this.privateState.dashboardSites = [];
      this.privateState.errorLoading = true;
    }).finally(() => {
      this.privateState.isLoadingKPIs = false;
      this.privateState.isLoadingSites = false;
      this.fetchAbort = null;
      this.startAutoRefresh();
    });
  }
  startAutoRefresh() {
    this.cancelAutoRefresh();
    if (this.autoRefreshInterval <= 0) {
      return;
    }
    let currentPeriod;
    try {
      currentPeriod = external_CoreHome_["Periods"].parse(external_CoreHome_["Matomo"].period, external_CoreHome_["Matomo"].currentDateString);
    } catch (e) {
      // gracefully ignore period parsing errors
    }
    if (!currentPeriod || !currentPeriod.containsToday()) {
      return;
    }
    this.autoRefreshTimeout = setTimeout(() => {
      this.autoRefreshTimeout = null;
      this.refreshData();
    }, this.autoRefreshInterval * 1000);
  }
  updateDashboardKPIs(response) {
    this.privateState.dashboardKPIs = {
      evolutionPeriod: external_CoreHome_["Matomo"].period,
      hits: response.totals.nb_hits,
      hitsEvolution: response.totals.hits_evolution,
      hitsTrend: response.totals.hits_evolution_trend,
      pageviews: response.totals.nb_pageviews,
      pageviewsEvolution: response.totals.pageviews_evolution,
      pageviewsTrend: response.totals.pageviews_evolution_trend,
      revenue: response.totals.revenue,
      revenueEvolution: response.totals.revenue_evolution,
      revenueTrend: response.totals.revenue_evolution_trend,
      visits: response.totals.nb_visits,
      visitsEvolution: response.totals.visits_evolution,
      visitsTrend: response.totals.visits_evolution_trend
    };
  }
  updateDashboardSites(response) {
    this.privateState.dashboardSites = response.sites;
    this.privateState.numSites = response.numSites;
    this.privateState.sparklineDate = response.sparklineDate;
  }
}
/* harmony default export */ var AllWebsitesDashboard_store = (new AllWebsitesDashboard_store_DashboardStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICardContainer.vue?vue&type=template&id=87c62b90

const KPICardContainervue_type_template_id_87c62b90_hoisted_1 = {
  class: "kpiCardContainer"
};
const KPICardContainervue_type_template_id_87c62b90_hoisted_2 = {
  key: 0,
  class: "kpiCard kpiCardLoading"
};
const KPICardContainervue_type_template_id_87c62b90_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "kpiCardTitle"
}, " ", -1);
const KPICardContainervue_type_template_id_87c62b90_hoisted_4 = {
  class: "kpiCardValue"
};
const KPICardContainervue_type_template_id_87c62b90_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "kpiCardEvolution"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "kpiCardEvolutionTrend"
}, " ")], -1);
const KPICardContainervue_type_template_id_87c62b90_hoisted_6 = {
  key: 0,
  class: "kpiCardBadge"
};
function KPICardContainervue_type_template_id_87c62b90_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  const _component_KPICard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("KPICard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KPICardContainervue_type_template_id_87c62b90_hoisted_1, [_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KPICardContainervue_type_template_id_87c62b90_hoisted_2, [KPICardContainervue_type_template_id_87c62b90_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", KPICardContainervue_type_template_id_87c62b90_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader)]), KPICardContainervue_type_template_id_87c62b90_hoisted_5, _ctx.hasKpiBadge ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KPICardContainervue_type_template_id_87c62b90_hoisted_6, " ")) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.kpis, (kpi, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: `kpi-card-${index}`
    }, [index > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        kpiCardDivider: true,
        kpiCardDividerBadge: _ctx.hasKpiBadge
      })
    }, " ", 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_KPICard, {
      "model-value": kpi
    }, null, 8, ["model-value"])], 64);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICardContainer.vue?vue&type=template&id=87c62b90

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICard.vue?vue&type=template&id=3c2758fa

const KPICardvue_type_template_id_3c2758fa_hoisted_1 = {
  class: "kpiCard"
};
const KPICardvue_type_template_id_3c2758fa_hoisted_2 = {
  class: "kpiCardTitle"
};
const KPICardvue_type_template_id_3c2758fa_hoisted_3 = {
  class: "kpiCardValue"
};
const KPICardvue_type_template_id_3c2758fa_hoisted_4 = {
  class: "kpiCardEvolution"
};
const KPICardvue_type_template_id_3c2758fa_hoisted_5 = {
  key: 1,
  class: "kpiCardEvolution"
};
const KPICardvue_type_template_id_3c2758fa_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "kpiCardEvolutionTrend"
}, " ", -1);
const KPICardvue_type_template_id_3c2758fa_hoisted_7 = [KPICardvue_type_template_id_3c2758fa_hoisted_6];
const KPICardvue_type_template_id_3c2758fa_hoisted_8 = ["innerHTML"];
function KPICardvue_type_template_id_3c2758fa_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KPICardvue_type_template_id_3c2758fa_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", KPICardvue_type_template_id_3c2758fa_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`kpiCardIcon ${_ctx.kpi.icon}`)
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.kpi.title)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", KPICardvue_type_template_id_3c2758fa_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.kpi.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", KPICardvue_type_template_id_3c2758fa_hoisted_4, [_ctx.kpi.evolutionValue !== '' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`kpiCardEvolutionTrend ${_ctx.evolutionTrendClass}`)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`kpiCardEvolutionIcon ${_ctx.evolutionTrendIcon}`)
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.kpi.evolutionValue), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.evolutionTrendFrom)), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KPICardvue_type_template_id_3c2758fa_hoisted_5, KPICardvue_type_template_id_3c2758fa_hoisted_7))]), _ctx.kpi.badge ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.kpi.badge),
    class: "kpiCardBadge"
  }, null, 8, KPICardvue_type_template_id_3c2758fa_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICard.vue?vue&type=template&id=3c2758fa

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICard.vue?vue&type=script&lang=ts

/* harmony default export */ var KPICardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  computed: {
    evolutionTrendFrom() {
      switch (this.kpi.evolutionPeriod) {
        case 'day':
          return 'MultiSites_EvolutionFromPreviousDay';
        case 'week':
          return 'MultiSites_EvolutionFromPreviousWeek';
        case 'month':
          return 'MultiSites_EvolutionFromPreviousMonth';
        case 'year':
          return 'MultiSites_EvolutionFromPreviousYear';
        default:
          return 'MultiSites_EvolutionFromPreviousPeriod';
      }
    },
    evolutionTrendClass() {
      if (this.kpi.evolutionTrend === 1) {
        return 'kpiTrendPositive';
      }
      if (this.kpi.evolutionTrend === -1) {
        return 'kpiTrendNegative';
      }
      return 'kpiTrendNeutral';
    },
    evolutionTrendIcon() {
      if (this.kpi.evolutionTrend === 1) {
        return 'icon-chevron-up';
      }
      if (this.kpi.evolutionTrend === -1) {
        return 'icon-chevron-down';
      }
      return 'icon-circle';
    },
    kpi() {
      return this.modelValue;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICard.vue



KPICardvue_type_script_lang_ts.render = KPICardvue_type_template_id_3c2758fa_render

/* harmony default export */ var KPICard = (KPICardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICardContainer.vue?vue&type=script&lang=ts



/* harmony default export */ var KPICardContainervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoLoader: external_CoreHome_["MatomoLoader"],
    KPICard: KPICard
  },
  props: {
    isLoading: Boolean,
    modelValue: {
      type: Array,
      required: true
    }
  },
  computed: {
    hasKpiBadge() {
      return this.kpis.some(kpi => !!kpi.badge);
    },
    kpis() {
      return this.modelValue;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICardContainer.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/KPICardContainer.vue



KPICardContainervue_type_script_lang_ts.render = KPICardContainervue_type_template_id_87c62b90_render

/* harmony default export */ var KPICardContainer = (KPICardContainervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTable.vue?vue&type=template&id=0d846a88

const SitesTablevue_type_template_id_0d846a88_hoisted_1 = {
  class: "sitesTableContainer"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_2 = {
  class: "card-table dataTable sitesTable"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_3 = {
  class: "sitesTableEvolutionSelector"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_4 = ["value"];
const SitesTablevue_type_template_id_0d846a88_hoisted_5 = {
  value: "hits_evolution"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_6 = {
  value: "visits_evolution"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_7 = {
  value: "pageviews_evolution"
};
const SitesTablevue_type_template_id_0d846a88_hoisted_8 = {
  key: 0,
  value: "revenue_evolution"
};
const _hoisted_9 = {
  key: 0
};
const _hoisted_10 = {
  class: "sitesTableLoading",
  colspan: "7"
};
const _hoisted_11 = {
  key: 1
};
const _hoisted_12 = {
  colspan: "7"
};
const _hoisted_13 = {
  class: "notification system notification-error"
};
const _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_16 = ["href"];
const _hoisted_17 = ["href"];
const _hoisted_18 = ["href"];
const _hoisted_19 = {
  key: 0,
  class: "sitesTablePagination"
};
const _hoisted_20 = {
  class: "dataTablePages"
};
function SitesTablevue_type_template_id_0d846a88_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  const _component_SitesTableSite = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SitesTableSite");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SitesTablevue_type_template_id_0d846a88_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", SitesTablevue_type_template_id_0d846a88_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    onClick: _cache[0] || (_cache[0] = $event => _ctx.sortBy('label')),
    class: "label"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Website')) + " ", 1), _ctx.sortColumn === 'label' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    onClick: _cache[1] || (_cache[1] = $event => _ctx.sortBy('nb_visits'))
  }, [_ctx.sortColumn === 'nb_visits' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    onClick: _cache[2] || (_cache[2] = $event => _ctx.sortBy('nb_pageviews'))
  }, [_ctx.sortColumn === 'nb_pageviews' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    onClick: _cache[3] || (_cache[3] = $event => _ctx.sortBy('nb_hits'))
  }, [_ctx.sortColumn === 'nb_hits' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnHits')), 1)]), _ctx.displayRevenue ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
    key: 0,
    onClick: _cache[4] || (_cache[4] = $event => _ctx.sortBy('revenue'))
  }, [_ctx.sortColumn === 'revenue' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    onClick: _cache[5] || (_cache[5] = $event => _ctx.sortBy(_ctx.evolutionSelector))
  }, [_ctx.sortColumn === _ctx.evolutionSelector ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.sortColumnClass)
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_Evolution')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", SitesTablevue_type_template_id_0d846a88_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default",
    value: _ctx.evolutionSelector,
    onChange: _cache[6] || (_cache[6] = $event => _ctx.changeEvolutionSelector($event.target.value))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SitesTablevue_type_template_id_0d846a88_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnHits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SitesTablevue_type_template_id_0d846a88_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SitesTablevue_type_template_id_0d846a88_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1), _ctx.displayRevenue ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", SitesTablevue_type_template_id_0d846a88_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, SitesTablevue_type_template_id_0d846a88_hoisted_4)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader)])])) : _ctx.errorLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), _hoisted_14, _hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NeedMoreHelp')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://matomo.org/faq/troubleshooting/faq_19489/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 9, _hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" – "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://forum.matomo.org/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_CommunityHelp')), 9, _hoisted_17), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, " – ", 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.errorShowProfessionalHelp]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://matomo.org/support-plans/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_ProfessionalHelp')), 9, _hoisted_18), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.errorShowProfessionalHelp]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ")])])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 2
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, site => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SitesTableSite, {
      "display-revenue": _ctx.displayRevenue,
      "evolution-metric": _ctx.evolutionMetric,
      key: `site-${site.idsite}`,
      "model-value": site,
      "sparkline-date": _ctx.sparklineDate,
      "sparkline-metric": _ctx.sparklineMetric
    }, null, 8, ["display-revenue", "evolution-metric", "model-value", "sparkline-date", "sparkline-metric"]);
  }), 128))])])]), !_ctx.isLoading || _ctx.paginationUpperBound > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "dataTablePrevious",
    onClick: _cache[7] || (_cache[7] = $event => _ctx.navigatePreviousPage())
  }, " « " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.paginationCurrentPage !== 0]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Pagination', _ctx.paginationLowerBound, _ctx.paginationUpperBound, _ctx.numberOfFilteredSites)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "dataTableNext",
    onClick: _cache[8] || (_cache[8] = $event => _ctx.navigateNextPage())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " » ", 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.paginationCurrentPage < _ctx.paginationMaxPage]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTable.vue?vue&type=template&id=0d846a88

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTableSite.vue?vue&type=template&id=47f7cfe0

const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_1 = {
  class: "label"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_2 = ["href", "title"];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-outlink"
}, null, -1);
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_4 = [SitesTableSitevue_type_template_id_47f7cfe0_hoisted_3];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_5 = ["href"];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_6 = {
  key: 1,
  class: "value"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_7 = {
  class: "value"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_8 = {
  class: "value"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_9 = {
  class: "value"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_10 = {
  key: 0
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_11 = {
  class: "value"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_12 = ["colspan"];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_13 = ["src"];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_14 = {
  key: 1,
  class: "sitesTableSparkline"
};
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_15 = ["href", "title"];
const SitesTableSitevue_type_template_id_47f7cfe0_hoisted_16 = ["src"];
function SitesTableSitevue_type_template_id_47f7cfe0_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      sitesTableGroup: !!_ctx.site.isGroup,
      sitesTableGroupSite: !_ctx.site.isGroup && !!_ctx.site.group,
      sitesTableSite: !_ctx.site.isGroup && !_ctx.site.group
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_1, [!_ctx.site.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.site.main_url,
    title: _ctx.translate('General_GoTo', _ctx.site.main_url)
  }, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_4, 8, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    title: "View reports",
    class: "value",
    href: _ctx.dashboardUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.siteLabel), 9, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_5)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.siteLabel), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.site.nb_visits), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.site.nb_pageviews), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.site.nb_hits), 1)]), _ctx.displayRevenue ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.site.revenue), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    colspan: _ctx.displaySparkline ? 1 : 2
  }, [!_ctx.site.isGroup && !!_ctx.site[_ctx.evolutionMetric] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    src: _ctx.evolutionIconSrc,
    alt: ""
  }, null, 8, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.evolutionTrendClass)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.site[_ctx.evolutionMetric]), 3)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_12), _ctx.displaySparkline ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", SitesTableSitevue_type_template_id_47f7cfe0_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.dashboardUrl,
    title: _ctx.translate('General_GoTo', _ctx.translate('Dashboard_DashboardOf', _ctx.siteLabel))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    alt: "",
    width: "100",
    height: "25",
    src: _ctx.evolutionSparklineSrc
  }, null, 8, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_16)], 8, SitesTableSitevue_type_template_id_47f7cfe0_hoisted_15)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTableSite.vue?vue&type=template&id=47f7cfe0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTableSite.vue?vue&type=script&lang=ts


/* harmony default export */ var SitesTableSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    displayRevenue: {
      type: Boolean,
      required: true
    },
    evolutionMetric: {
      type: String,
      required: true
    },
    modelValue: {
      type: Object,
      required: true
    },
    sparklineDate: String,
    sparklineMetric: String
  },
  computed: {
    dashboardUrl() {
      const dashboardParams = external_CoreHome_["MatomoUrl"].stringify({
        module: 'CoreHome',
        action: 'index',
        date: external_CoreHome_["Matomo"].currentDateString,
        period: external_CoreHome_["Matomo"].period,
        idSite: this.site.idsite
      });
      return `?${dashboardParams}${this.tokenParam}`;
    },
    displaySparkline() {
      return !this.site.isGroup && this.sparklineDate && this.sparklineMetric;
    },
    evolutionIconSrc() {
      if (this.evolutionTrend === 1) {
        return 'plugins/MultiSites/images/arrow_up.png';
      }
      if (this.evolutionTrend === -1) {
        return 'plugins/MultiSites/images/arrow_down.png';
      }
      return 'plugins/MultiSites/images/stop.png';
    },
    evolutionSparklineSrc() {
      const sparklineParams = external_CoreHome_["MatomoUrl"].stringify({
        module: 'MultiSites',
        action: 'getEvolutionGraph',
        date: this.sparklineDate,
        period: external_CoreHome_["Matomo"].period,
        idSite: this.site.idsite,
        columns: this.sparklineMetric,
        evolutionBy: this.sparklineMetric,
        colors: JSON.stringify(external_CoreHome_["Matomo"].getSparklineColors()),
        viewDataTable: 'sparkline'
      });
      return `?${sparklineParams}${this.tokenParam}`;
    },
    evolutionTrend() {
      const property = `${this.evolutionMetric}_trend`;
      return this.site[property];
    },
    evolutionTrendClass() {
      if (this.evolutionTrend === 1) {
        return 'evolutionTrendPositive';
      }
      if (this.evolutionTrend === -1) {
        return 'evolutionTrendNegative';
      }
      return '';
    },
    site() {
      return this.modelValue;
    },
    siteLabel() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(this.site.label);
    },
    tokenParam() {
      const token_auth = external_CoreHome_["MatomoUrl"].urlParsed.value.token_auth;
      return token_auth ? `&token_auth=${token_auth}` : '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTableSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTableSite.vue



SitesTableSitevue_type_script_lang_ts.render = SitesTableSitevue_type_template_id_47f7cfe0_render

/* harmony default export */ var SitesTableSite = (SitesTableSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTable.vue?vue&type=script&lang=ts




/* harmony default export */ var SitesTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    MatomoLoader: external_CoreHome_["MatomoLoader"],
    SitesTableSite: SitesTableSite
  },
  props: {
    displayRevenue: {
      type: Boolean,
      required: true
    },
    displaySparklines: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      evolutionSelector: 'visits_evolution'
    };
  },
  computed: {
    errorLoading() {
      return AllWebsitesDashboard_store.state.value.errorLoading;
    },
    errorShowProfessionalHelp() {
      return external_CoreHome_["Matomo"].config && external_CoreHome_["Matomo"].config.are_ads_enabled;
    },
    evolutionMetric() {
      return this.evolutionSelector;
    },
    isLoading() {
      return AllWebsitesDashboard_store.state.value.isLoadingSites;
    },
    numberOfFilteredSites() {
      return AllWebsitesDashboard_store.state.value.numSites;
    },
    paginationCurrentPage() {
      return AllWebsitesDashboard_store.state.value.paginationCurrentPage;
    },
    paginationLowerBound() {
      return AllWebsitesDashboard_store.paginationLowerBound.value;
    },
    paginationUpperBound() {
      return AllWebsitesDashboard_store.paginationUpperBound.value;
    },
    paginationMaxPage() {
      return AllWebsitesDashboard_store.numberOfPages.value;
    },
    sites() {
      return AllWebsitesDashboard_store.state.value.dashboardSites;
    },
    sortColumn() {
      return AllWebsitesDashboard_store.state.value.sortColumn;
    },
    sortColumnClass() {
      return {
        sitesTableSort: true,
        sitesTableSortAsc: this.sortOrder === 'asc',
        sitesTableSortDesc: this.sortOrder === 'desc'
      };
    },
    sortOrder() {
      return AllWebsitesDashboard_store.state.value.sortOrder;
    },
    sparklineMetric() {
      switch (this.evolutionMetric) {
        case 'hits_evolution':
          return 'nb_hits';
        case 'pageviews_evolution':
          return 'nb_pageviews';
        case 'revenue_evolution':
          return 'revenue';
        case 'visits_evolution':
          return 'nb_visits';
        default:
          return '';
      }
    },
    sparklineDate() {
      return this.displaySparklines ? AllWebsitesDashboard_store.state.value.sparklineDate : null;
    }
  },
  methods: {
    changeEvolutionSelector(metric) {
      this.evolutionSelector = metric;
      this.sortBy(metric);
    },
    navigateNextPage() {
      AllWebsitesDashboard_store.navigateNextPage();
    },
    navigatePreviousPage() {
      AllWebsitesDashboard_store.navigatePreviousPage();
    },
    sortBy(column) {
      AllWebsitesDashboard_store.sortBy(column);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/SitesTable.vue



SitesTablevue_type_script_lang_ts.render = SitesTablevue_type_template_id_0d846a88_render

/* harmony default export */ var SitesTable = (SitesTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }





/* harmony default export */ var AllWebsitesDashboardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    KPICardContainer: KPICardContainer,
    PeriodSelector: external_CoreHome_["PeriodSelector"],
    SitesTable: SitesTable
  },
  props: {
    autoRefreshInterval: {
      type: Number,
      required: true
    },
    displayRevenue: {
      type: Boolean,
      required: true
    },
    displaySparklines: {
      type: Boolean,
      required: true
    },
    isWidgetized: {
      type: Boolean,
      required: true
    },
    kpiBadgeHits: {
      type: String,
      required: true
    },
    pageSize: {
      type: Number,
      required: true
    },
    selectablePeriods: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      searchTerm: ''
    };
  },
  mounted() {
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => external_CoreHome_["MatomoUrl"].hashParsed.value, () => AllWebsitesDashboard_store.reloadDashboard());
    AllWebsitesDashboard_store.setAutoRefreshInterval(this.autoRefreshInterval);
    AllWebsitesDashboard_store.setPageSize(this.pageSize);
    AllWebsitesDashboard_store.reloadDashboard();
  },
  computed: {
    addSiteUrl() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(_extends(_extends(_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        module: 'SitesManager',
        action: 'index',
        showaddsite: '1'
      }))}`;
    },
    isLoadingKPIs() {
      return AllWebsitesDashboard_store.state.value.isLoadingKPIs;
    },
    kpis() {
      const {
        dashboardKPIs
      } = AllWebsitesDashboard_store.state.value;
      const kpis = [{
        icon: 'icon-user',
        title: 'MultiSites_TotalVisits',
        value: dashboardKPIs.visits,
        evolutionPeriod: dashboardKPIs.evolutionPeriod,
        evolutionTrend: dashboardKPIs.visitsTrend,
        evolutionValue: dashboardKPIs.visitsEvolution
      }, {
        icon: 'icon-show',
        title: 'MultiSites_TotalPageviews',
        value: dashboardKPIs.pageviews,
        evolutionPeriod: dashboardKPIs.evolutionPeriod,
        evolutionTrend: dashboardKPIs.pageviewsTrend,
        evolutionValue: dashboardKPIs.pageviewsEvolution
      }, {
        badge: this.kpiBadgeHits,
        icon: 'icon-hits',
        title: 'MultiSites_TotalHits',
        value: dashboardKPIs.hits,
        evolutionPeriod: dashboardKPIs.evolutionPeriod,
        evolutionTrend: dashboardKPIs.hitsTrend,
        evolutionValue: dashboardKPIs.hitsEvolution
      }];
      if (this.displayRevenue) {
        kpis.push({
          icon: 'icon-dollar-sign',
          title: 'General_TotalRevenue',
          value: dashboardKPIs.revenue,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.revenueTrend,
          evolutionValue: dashboardKPIs.revenueEvolution
        });
      }
      return kpis;
    },
    isUserAllowedToAddSite() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    }
  },
  methods: {
    searchSite(term) {
      AllWebsitesDashboard_store.searchSite(term);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.vue



AllWebsitesDashboardvue_type_script_lang_ts.render = render

/* harmony default export */ var AllWebsitesDashboard = (AllWebsitesDashboardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=template&id=052ab191

const MultisitesSitevue_type_template_id_052ab191_hoisted_1 = {
  key: 0,
  class: "multisites-label label"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_2 = ["href"];
const MultisitesSitevue_type_template_id_052ab191_hoisted_3 = {
  key: 0
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_4 = ["href", "title"];
const MultisitesSitevue_type_template_id_052ab191_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-outlink"
}, null, -1);
const MultisitesSitevue_type_template_id_052ab191_hoisted_6 = [MultisitesSitevue_type_template_id_052ab191_hoisted_5];
const MultisitesSitevue_type_template_id_052ab191_hoisted_7 = {
  key: 1,
  class: "multisites-label label"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_8 = {
  class: "value"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_9 = {
  class: "multisites-column"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_10 = {
  class: "value"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_11 = {
  class: "multisites-column"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_12 = {
  class: "value"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_13 = {
  key: 2,
  class: "multisites-column"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_14 = {
  class: "value"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_15 = ["title"];
const MultisitesSitevue_type_template_id_052ab191_hoisted_16 = {
  key: 0,
  class: "visits value"
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/arrow_up.png",
  alt: ""
}, null, -1);
const MultisitesSitevue_type_template_id_052ab191_hoisted_18 = {
  style: {
    "color": "green"
  }
};
const MultisitesSitevue_type_template_id_052ab191_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/stop.png",
  alt: ""
}, null, -1);
const MultisitesSitevue_type_template_id_052ab191_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/arrow_down.png",
  alt: ""
}, null, -1);
const _hoisted_21 = {
  style: {
    "color": "red"
  }
};
const _hoisted_22 = {
  key: 4,
  style: {
    "width": "180px"
  }
};
const _hoisted_23 = {
  key: 0,
  class: "sparkline",
  style: {
    "width": "100px",
    "margin": "auto"
  }
};
const _hoisted_24 = ["href", "title"];
const _hoisted_25 = ["src"];
function MultisitesSitevue_type_template_id_052ab191_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'groupedWebsite': _ctx.website.group,
      'website': !_ctx.website.group,
      'group': _ctx.website.isGroup
    }),
    ref: "root"
  }, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", MultisitesSitevue_type_template_id_052ab191_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    title: "View reports",
    class: "value truncated-text-line",
    href: _ctx.dashboardUrl(_ctx.website)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.websiteLabel), 9, MultisitesSitevue_type_template_id_052ab191_hoisted_2), _ctx.website.main_url ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.website.main_url,
    title: _ctx.translate('General_GoTo', _ctx.website.main_url)
  }, MultisitesSitevue_type_template_id_052ab191_hoisted_6, 8, MultisitesSitevue_type_template_id_052ab191_hoisted_4)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", MultisitesSitevue_type_template_id_052ab191_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.websiteLabel), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", MultisitesSitevue_type_template_id_052ab191_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.nb_visits), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", MultisitesSitevue_type_template_id_052ab191_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.nb_pageviews), 1)]), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", MultisitesSitevue_type_template_id_052ab191_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.revenue), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.period !== 'range' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
    key: 3,
    class: "multisites-evolution",
    title: _ctx.website.tooltip
  }, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MultisitesSitevue_type_template_id_052ab191_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [MultisitesSitevue_type_template_id_052ab191_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", MultisitesSitevue_type_template_id_052ab191_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website[`${_ctx.evolutionMetric}_trend`] === 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [MultisitesSitevue_type_template_id_052ab191_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website[`${_ctx.evolutionMetric}_trend`] === 0]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [MultisitesSitevue_type_template_id_052ab191_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website[`${_ctx.evolutionMetric}_trend`] === -1]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, MultisitesSitevue_type_template_id_052ab191_hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showSparklines ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_22, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_23, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.dashboardUrl(_ctx.website),
    title: _ctx.translate('General_GoTo', _ctx.translate('Dashboard_DashboardOf', _ctx.websiteLabel))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    alt: "",
    width: "100",
    height: "25",
    src: _ctx.sparklineImage(_ctx.website)
  }, null, 8, _hoisted_25)], 8, _hoisted_24)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=template&id=052ab191

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=script&lang=ts


/* harmony default export */ var MultisitesSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    website: {
      type: Object,
      required: true
    },
    evolutionMetric: {
      type: String,
      required: true
    },
    showSparklines: Boolean,
    dateSparkline: String,
    displayRevenueColumn: Boolean,
    metric: String
  },
  mounted() {
    external_CoreHome_["Matomo"].postEvent('MultiSites.MultiSitesSite.mounted', {
      element: this.$refs.root
    });
  },
  unmounted() {
    external_CoreHome_["Matomo"].postEvent('MultiSites.MultiSitesSite.unmounted', {
      element: this.$refs.root
    });
  },
  methods: {
    dashboardUrl(website) {
      return `index.php?module=CoreHome&action=index&date=${this.date}&period=${this.period}` + `&idSite=${website.idsite}${this.tokenParam}`;
    },
    sparklineImage(website) {
      let {
        metric
      } = this;
      switch (this.evolutionMetric) {
        case 'visits_evolution':
          metric = 'nb_visits';
          break;
        case 'pageviews_evolution':
          metric = 'nb_pageviews';
          break;
        case 'revenue_evolution':
          metric = 'revenue';
          break;
        default:
          break;
      }
      return `index.php?module=MultiSites&action=getEvolutionGraph&period=${this.period}&date=` + `${this.dateSparkline}&evolutionBy=${metric}&columns=${metric}&idSite=${website.idsite}` + `&idsite=${website.idsite}&viewDataTable=sparkline${this.tokenParam}&colors=` + `${encodeURIComponent(JSON.stringify(external_CoreHome_["Matomo"].getSparklineColors()))}`;
    }
  },
  computed: {
    tokenParam() {
      const token_auth = external_CoreHome_["MatomoUrl"].urlParsed.value.token_auth;
      return token_auth ? `&token_auth=${token_auth}` : '';
    },
    period() {
      return external_CoreHome_["Matomo"].period;
    },
    date() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.date;
    },
    websiteLabel() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(this.website.label);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue



MultisitesSitevue_type_script_lang_ts.render = MultisitesSitevue_type_template_id_052ab191_render

/* harmony default export */ var MultisitesSite = (MultisitesSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.store.ts
function Dashboard_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


const {
  NumberFormatter
} = window;
class Dashboard_store_DashboardStore {
  constructor() {
    Dashboard_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      sites: [],
      isLoading: false,
      pageSize: 25,
      currentPage: 0,
      totalVisits: '?',
      totalPageviews: '?',
      totalActions: '?',
      totalRevenue: '?',
      searchTerm: '',
      lastVisits: '?',
      lastVisitsDate: '?',
      numberOfSites: 0,
      loadingMessage: Object(external_CoreHome_["translate"])('MultiSites_LoadingWebsites'),
      reverse: true,
      sortColumn: 'nb_visits',
      refreshInterval: 0,
      errorLoadingSites: false
    }));
    Dashboard_store_defineProperty(this, "refreshTimeout", null);
    Dashboard_store_defineProperty(this, "fetchAbort", null);
    Dashboard_store_defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    Dashboard_store_defineProperty(this, "numberOfFilteredSites", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.numberOfSites));
    Dashboard_store_defineProperty(this, "numberOfPages", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Math.ceil(this.numberOfFilteredSites.value / this.state.value.pageSize - 1)));
    Dashboard_store_defineProperty(this, "currentPagingOffset", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Math.ceil(this.state.value.currentPage * this.state.value.pageSize)));
    Dashboard_store_defineProperty(this, "paginationLowerBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.currentPagingOffset.value + 1));
    Dashboard_store_defineProperty(this, "paginationUpperBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      let end = this.currentPagingOffset.value + this.state.value.pageSize;
      const max = this.numberOfFilteredSites.value;
      if (end > max) {
        end = max;
      }
      return end;
    }));
  }
  cancelRefereshInterval() {
    if (this.refreshTimeout) {
      clearTimeout(this.refreshTimeout);
      this.refreshTimeout = null;
    }
  }
  updateWebsitesList(report) {
    if (!report) {
      this.onError();
      return;
    }
    const allSites = report.sites;
    allSites.forEach(site => {
      if (site.ratio !== 1 && site.ratio !== '1') {
        const percent = NumberFormatter.formatPercent(Math.round(parseInt(site.ratio, 10) * 100));
        let metricName = null;
        let previousTotal = '0';
        let currentTotal = '0';
        let evolution = '0';
        let previousTotalAdjusted = '0';
        if (this.state.value.sortColumn === 'nb_visits' || this.state.value.sortColumn === 'visits_evolution') {
          previousTotal = NumberFormatter.formatNumber(site.previous_nb_visits);
          currentTotal = NumberFormatter.formatNumber(site.nb_visits);
          evolution = NumberFormatter.formatPercent(site.visits_evolution);
          metricName = Object(external_CoreHome_["translate"])('General_ColumnNbVisits');
          previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(parseInt(site.previous_nb_visits, 10) * parseInt(site.ratio, 10)));
        }
        if (this.state.value.sortColumn === 'pageviews_evolution') {
          previousTotal = `${site.previous_Actions_nb_pageviews}`;
          currentTotal = `${site.nb_pageviews}`;
          evolution = NumberFormatter.formatPercent(site.pageviews_evolution);
          metricName = Object(external_CoreHome_["translate"])('General_ColumnPageviews');
          previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(parseInt(site.previous_Actions_nb_pageviews, 10) * parseInt(site.ratio, 10)));
        }
        if (this.state.value.sortColumn === 'revenue_evolution') {
          previousTotal = NumberFormatter.formatCurrency(site.previous_Goal_revenue, site.currencySymbol);
          currentTotal = NumberFormatter.formatCurrency(site.revenue, site.currencySymbol);
          evolution = NumberFormatter.formatPercent(site.revenue_evolution);
          metricName = Object(external_CoreHome_["translate"])('General_ColumnRevenue');
          previousTotalAdjusted = NumberFormatter.formatCurrency(Math.round(parseInt(site.previous_Goal_revenue, 10) * parseInt(site.ratio, 10)), site.currencySymbol);
        }
        if (metricName) {
          site.tooltip = `${Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonIncomplete', [percent])}\n`;
          site.tooltip += `${Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonProportional', [percent, `${previousTotalAdjusted}`, metricName, `${previousTotal}`])}\n`;
          switch (site.periodName) {
            case 'day':
              site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonDay', [`${currentTotal}`, metricName, `${previousTotalAdjusted}`, site.previousRange, `${evolution}`]);
              break;
            case 'week':
              site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonWeek', [`${currentTotal}`, metricName, `${previousTotalAdjusted}`, site.previousRange, `${evolution}`]);
              break;
            case 'month':
              site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonMonth', [`${currentTotal}`, metricName, `${previousTotalAdjusted}`, site.previousRange, `${evolution}`]);
              break;
            case 'year':
              site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonYear', [`${currentTotal}`, metricName, `${previousTotalAdjusted}`, site.previousRange, `${evolution}`]);
              break;
            default:
              break;
          }
        }
      }
    });
    this.privateState.totalVisits = report.totals.nb_visits;
    this.privateState.totalPageviews = report.totals.nb_pageviews;
    this.privateState.totalActions = report.totals.nb_actions;
    this.privateState.totalRevenue = report.totals.revenue;
    this.privateState.lastVisits = report.totals.nb_visits_lastdate;
    this.privateState.sites = allSites;
    this.privateState.numberOfSites = report.numSites;
    this.privateState.lastVisitsDate = report.lastDate;
  }
  sortBy(metric) {
    if (this.state.value.sortColumn === metric) {
      this.privateState.reverse = !this.state.value.reverse;
    }
    this.privateState.sortColumn = metric;
    this.fetchAllSites();
  }
  previousPage() {
    this.privateState.currentPage = this.state.value.currentPage - 1;
    this.fetchAllSites();
  }
  nextPage() {
    this.privateState.currentPage = this.state.value.currentPage + 1;
    this.fetchAllSites();
  }
  searchSite(term) {
    this.privateState.searchTerm = term;
    this.privateState.currentPage = 0;
    this.fetchAllSites();
  }
  fetchAllSites() {
    if (this.fetchAbort) {
      this.fetchAbort.abort();
      this.fetchAbort = null;
      this.cancelRefereshInterval();
    }
    this.privateState.isLoading = true;
    this.privateState.errorLoadingSites = false;
    const params = {
      method: 'MultiSites.getAllWithGroups',
      hideMetricsDoc: '1',
      filter_sort_order: 'asc',
      filter_limit: this.state.value.pageSize,
      filter_offset: this.currentPagingOffset.value,
      showColumns: ['label', 'nb_visits', 'nb_pageviews', 'visits_evolution', 'visits_evolution_trend', 'pageviews_evolution', 'pageviews_evolution_trend', 'revenue_evolution', 'revenue_evolution_trend', 'nb_actions,revenue'].join(',')
    };
    if (this.privateState.searchTerm) {
      params.pattern = this.privateState.searchTerm;
    }
    if (this.privateState.sortColumn) {
      params.filter_sort_column = this.privateState.sortColumn;
    }
    if (this.privateState.reverse) {
      params.filter_sort_order = 'desc';
    }
    this.fetchAbort = new AbortController();
    return external_CoreHome_["AjaxHelper"].fetch(params, {
      abortController: this.fetchAbort
    }).then(response => {
      this.updateWebsitesList(response);
    }).catch(() => {
      this.onError();
    }).finally(() => {
      this.privateState.isLoading = false;
      this.fetchAbort = null;
      if (this.state.value.refreshInterval && this.state.value.refreshInterval > 0) {
        this.cancelRefereshInterval();
        this.refreshTimeout = setTimeout(() => {
          this.refreshTimeout = null;
          this.fetchAllSites();
        }, this.state.value.refreshInterval * 1000);
      }
    });
  }
  onError() {
    this.privateState.errorLoadingSites = true;
    this.privateState.sites = [];
  }
  setRefreshInterval(interval) {
    this.privateState.refreshInterval = interval;
  }
  setPageSize(pageSize) {
    this.privateState.pageSize = pageSize;
  }
}
/* harmony default export */ var Dashboard_store = (new Dashboard_store_DashboardStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=template&id=40e2a52d

const Dashboardvue_type_template_id_40e2a52d_hoisted_1 = {
  ref: "root"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_2 = {
  class: "card-title"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_3 = ["innerHTML", "title"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_4 = {
  id: "mt",
  class: "dataTable card-table",
  cellspacing: "0"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_5 = {
  class: "heading"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_6 = {
  class: "heading"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_7 = {
  class: "heading"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_8 = {
  class: "heading"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_9 = ["colspan"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_10 = ["value"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_11 = {
  value: "visits_evolution"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_12 = {
  value: "pageviews_evolution"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_13 = {
  key: 0,
  value: "revenue_evolution"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_14 = {
  key: 0
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_15 = {
  colspan: "7",
  class: "allWebsitesLoading"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_16 = {
  key: 1
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_17 = {
  key: 0
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_18 = {
  colspan: "7"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_19 = {
  class: "notification system notification-error"
};
const Dashboardvue_type_template_id_40e2a52d_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const Dashboardvue_type_template_id_40e2a52d_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const Dashboardvue_type_template_id_40e2a52d_hoisted_22 = ["href"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_23 = ["href"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_24 = ["href"];
const Dashboardvue_type_template_id_40e2a52d_hoisted_25 = {
  colspan: "8",
  class: "paging"
};
const _hoisted_26 = {
  class: "row"
};
const _hoisted_27 = {
  class: "col s3 add_new_site"
};
const _hoisted_28 = ["href"];
const _hoisted_29 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const _hoisted_30 = {
  class: "col s6"
};
const _hoisted_31 = {
  style: {
    "cursor": "pointer"
  }
};
const _hoisted_32 = {
  class: "dataTablePages"
};
const _hoisted_33 = {
  id: "counter"
};
const _hoisted_34 = {
  style: {
    "cursor": "pointer"
  },
  class: "pointer"
};
const _hoisted_35 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "col s3"
}, " ", -1);
const _hoisted_36 = {
  row_id: "last"
};
const _hoisted_37 = {
  colspan: "8",
  class: "site_search"
};
const _hoisted_38 = {
  class: "row"
};
const _hoisted_39 = {
  class: "input-field col s12"
};
const _hoisted_40 = ["placeholder"];
const _hoisted_41 = ["title"];
function Dashboardvue_type_template_id_40e2a52d_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_MultisitesSite = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultisitesSite");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Dashboardvue_type_template_id_40e2a52d_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", Dashboardvue_type_template_id_40e2a52d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/faq/new-to-piwik/all-websites-dashboard/'),
    "feature-name": _ctx.translate('General_AllWebsitesDashboard')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_AllWebsitesDashboard')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "smallTitle",
      innerHTML: _ctx.$sanitize(this.smallTitleContent),
      title: _ctx.smallTitleTooltip
    }, null, 8, Dashboardvue_type_template_id_40e2a52d_hoisted_3)]),
    _: 1
  }, 8, ["help-url", "feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", Dashboardvue_type_template_id_40e2a52d_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "names",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["label", {
      columnSorted: 'label' === _ctx.sortColumn
    }]),
    onClick: _cache[0] || (_cache[0] = $event => _ctx.sortBy('label'))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_40e2a52d_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Website')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'label' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'label' === _ctx.sortColumn
    }]),
    style: {
      "margin-left": "3.5px"
    }
  }, null, 2)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "visits",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multisites-column", {
      columnSorted: 'nb_visits' === _ctx.sortColumn
    }]),
    onClick: _cache[1] || (_cache[1] = $event => _ctx.sortBy('nb_visits'))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'nb_visits' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'nb_visits' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_40e2a52d_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "pageviews",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multisites-column", {
      columnSorted: 'nb_pageviews' === _ctx.sortColumn
    }]),
    onClick: _cache[2] || (_cache[2] = $event => _ctx.sortBy('nb_pageviews'))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'nb_pageviews' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'nb_pageviews' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_40e2a52d_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1)], 2), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
    key: 0,
    id: "revenue",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multisites-column", {
      columnSorted: 'revenue' === _ctx.sortColumn
    }]),
    onClick: _cache[3] || (_cache[3] = $event => _ctx.sortBy('revenue'))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'revenue' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'revenue' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_40e2a52d_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "evolution",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      columnSorted: _ctx.evolutionSelector === _ctx.sortColumn
    }),
    colspan: _ctx.showSparklines ? 2 : 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && _ctx.evolutionSelector === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && _ctx.evolutionSelector === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "evolution",
    onClick: _cache[4] || (_cache[4] = $event => _ctx.sortBy(_ctx.evolutionSelector)),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_Evolution')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "selector browser-default",
    id: "evolution_selector",
    value: _ctx.evolutionSelector,
    onChange: _cache[5] || (_cache[5] = $event => {
      _ctx.evolutionSelector = $event.target.value;
      _ctx.sortBy(_ctx.evolutionSelector);
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", Dashboardvue_type_template_id_40e2a52d_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", Dashboardvue_type_template_id_40e2a52d_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", Dashboardvue_type_template_id_40e2a52d_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, Dashboardvue_type_template_id_40e2a52d_hoisted_10)], 10, Dashboardvue_type_template_id_40e2a52d_hoisted_9)])]), _ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tbody", Dashboardvue_type_template_id_40e2a52d_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_40e2a52d_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": _ctx.loadingMessage,
    loading: _ctx.isLoading
  }, null, 8, ["loading-message", "loading"])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tbody", Dashboardvue_type_template_id_40e2a52d_hoisted_16, [_ctx.errorLoadingSites ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", Dashboardvue_type_template_id_40e2a52d_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_40e2a52d_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Dashboardvue_type_template_id_40e2a52d_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), Dashboardvue_type_template_id_40e2a52d_hoisted_20, Dashboardvue_type_template_id_40e2a52d_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NeedMoreHelp')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://matomo.org/faq/troubleshooting/faq_19489/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 9, Dashboardvue_type_template_id_40e2a52d_hoisted_22), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" – "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.externalRawLink('https://forum.matomo.org/')
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_CommunityHelp')), 9, Dashboardvue_type_template_id_40e2a52d_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, " – ", 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.areAdsForProfessionalServicesEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.professionalHelpUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_ProfessionalHelp')), 9, Dashboardvue_type_template_id_40e2a52d_hoisted_24), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.areAdsForProfessionalServicesEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ")])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, website => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MultisitesSite, {
      key: website.idsite,
      website: website,
      "evolution-metric": _ctx.evolutionSelector,
      "date-sparkline": _ctx.dateSparkline,
      "show-sparklines": _ctx.showSparklines,
      metric: _ctx.sortColumn,
      "display-revenue-column": _ctx.displayRevenueColumn
    }, null, 8, ["website", "evolution-metric", "date-sparkline", "show-sparklines", "metric", "display-revenue-column"]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tfoot", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_40e2a52d_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_27, [_ctx.hasSuperUserAccess ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    href: _ctx.addSiteUrl
  }, [_hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AddSite')), 1)], 8, _hoisted_28)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    id: "prev",
    class: "previous dataTablePrevious",
    onClick: _cache[6] || (_cache[6] = $event => _ctx.previousPage())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_31, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.currentPage === 0)]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Pagination', _ctx.paginationLowerBound, _ctx.paginationUpperBound, _ctx.numberOfFilteredSites)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    id: "next",
    class: "next dataTableNext",
    onClick: _cache[7] || (_cache[7] = $event => _ctx.nextPage())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_34, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.currentPage >= _ctx.numberOfPages)]])]), _hoisted_35])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", _hoisted_36, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    onKeydown: _cache[8] || (_cache[8] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])($event => _ctx.searchSite(_ctx.searchTerm), ["enter"])),
    "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.searchTerm = $event),
    placeholder: _ctx.translate('Actions_SubmenuSitesearch')
  }, null, 40, _hoisted_40), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search search_ico",
    onClick: _cache[10] || (_cache[10] = $event => _ctx.searchSite(_ctx.searchTerm)),
    title: _ctx.translate('General_ClickToSearch')
  }, null, 8, _hoisted_41)])])])])])])], 512);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=template&id=40e2a52d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=script&lang=ts




/* harmony default export */ var Dashboardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    displayRevenueColumn: Boolean,
    showSparklines: Boolean,
    dateSparkline: String,
    pageSize: Number,
    autoRefreshTodayReport: Number
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    MultisitesSite: MultisitesSite
  },
  data() {
    return {
      evolutionSelector: 'visits_evolution',
      searchTerm: ''
    };
  },
  created() {
    if (this.pageSize) {
      Dashboard_store.setPageSize(this.pageSize);
    }
    this.refresh(this.autoRefreshTodayReport);
  },
  methods: {
    refresh(interval) {
      Dashboard_store.setRefreshInterval(interval);
      Dashboard_store.fetchAllSites();
    },
    sortBy(column) {
      Dashboard_store.sortBy(column);
    },
    previousPage() {
      Dashboard_store.previousPage();
    },
    nextPage() {
      Dashboard_store.nextPage();
    },
    searchSite() {
      Dashboard_store.searchSite(this.searchTerm);
    }
  },
  computed: {
    hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    date() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.date;
    },
    idSite() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.idSite;
    },
    url() {
      return external_CoreHome_["Matomo"].piwik_url;
    },
    period() {
      return external_CoreHome_["Matomo"].period;
    },
    areAdsForProfessionalServicesEnabled() {
      return external_CoreHome_["Matomo"].config && external_CoreHome_["Matomo"].config.are_ads_enabled;
    },
    sortColumn() {
      return Dashboard_store.state.value.sortColumn;
    },
    reverse() {
      return Dashboard_store.state.value.reverse;
    },
    smallTitleContent() {
      const state = Dashboard_store.state.value;
      return Object(external_CoreHome_["translate"])('General_TotalVisitsPageviewsActionsRevenue', `<strong>${state.totalVisits}</strong>`, `<strong>${state.totalPageviews}</strong>`, `<strong>${state.totalActions}</strong>`, `<strong>${state.totalRevenue}</strong>`);
    },
    smallTitleTooltip() {
      const state = Dashboard_store.state.value;
      return Object(external_CoreHome_["translate"])('General_EvolutionSummaryGeneric', Object(external_CoreHome_["translate"])('General_NVisits', `${state.totalVisits}`), this.date, `${state.lastVisits}`, state.lastVisitsDate, Object(external_CoreHome_["getFormattedEvolution"])(state.totalVisits, state.lastVisits));
    },
    loadingMessage() {
      return Dashboard_store.state.value.loadingMessage;
    },
    isLoading() {
      return Dashboard_store.state.value.isLoading;
    },
    errorLoadingSites() {
      return Dashboard_store.state.value.errorLoadingSites;
    },
    sites() {
      return Dashboard_store.state.value.sites;
    },
    numberOfPages() {
      return Dashboard_store.numberOfPages.value;
    },
    currentPage() {
      return Dashboard_store.state.value.currentPage;
    },
    paginationLowerBound() {
      return Dashboard_store.paginationLowerBound.value;
    },
    paginationUpperBound() {
      return Dashboard_store.paginationUpperBound.value;
    },
    numberOfFilteredSites() {
      return Dashboard_store.numberOfFilteredSites.value;
    },
    professionalHelpUrl() {
      return Object(external_CoreHome_["externalRawLink"])('https://matomo.org/support-plans/');
    },
    addSiteUrl() {
      return `index.php?module=SitesManager&action=index&showaddsite=1&period=${this.period}&` + `date=${this.date}&idSite=${this.idSite}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue



Dashboardvue_type_script_lang_ts.render = Dashboardvue_type_template_id_40e2a52d_render

/* harmony default export */ var Dashboard = (Dashboardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/index.ts
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
//# sourceMappingURL=MultiSites.umd.js.map