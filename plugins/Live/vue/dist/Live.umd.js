(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Live"] = factory(require("CoreHome"), require("vue"));
	else
		root["Live"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Live/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "LiveWidget", function() { return /* reexport */ LiveWidget; });
__webpack_require__.d(__webpack_exports__, "TotalVisitors", function() { return /* reexport */ TotalVisitors; });
__webpack_require__.d(__webpack_exports__, "LivePage", function() { return /* reexport */ LivePage; });
__webpack_require__.d(__webpack_exports__, "IndexHeader", function() { return /* reexport */ IndexHeader; });
__webpack_require__.d(__webpack_exports__, "LastVisits", function() { return /* reexport */ LastVisits; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LiveWidget/LiveWidget.vue?vue&type=template&id=d1748510

const _hoisted_1 = {
  key: 0,
  class: "live-widget-loading"
};
const _hoisted_2 = {
  ref: "root"
};
const _hoisted_3 = {
  class: "visitsLiveFooter"
};
const _hoisted_4 = ["title"];
const _hoisted_5 = {
  id: "pauseImage",
  border: "0",
  src: "plugins/Live/images/pause.png",
  role: "presentation"
};
const _hoisted_6 = ["title"];
const _hoisted_7 = {
  id: "playImage",
  border: "0",
  src: "plugins/Live/images/play.png",
  role: "presentation"
};
const _hoisted_8 = {
  key: 0
};
const _hoisted_9 = ["href"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoLoader = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoLoader");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [_ctx.isInitialLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoLoader)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, null, 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    title: _ctx.translate('Live_OnClickPause', _ctx.translate('Live_VisitorsInRealTime')),
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.pause(), ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", _hoisted_5, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.isStarted]])], 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    title: _ctx.translate('Live_OnClickStart', _ctx.translate('Live_VisitorsInRealTime')),
    onClick: _cache[1] || (_cache[1] = $event => _ctx.play())
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", _hoisted_7, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isStarted]])], 8, _hoisted_6), !_ctx.disableLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "rightLink",
    href: _ctx.visitorLogUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LinkVisitorLog')), 9, _hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/LiveWidget/LiveWidget.vue?vue&type=template&id=d1748510

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LiveWidget/LiveWidget.vue?vue&type=script&lang=ts


const {
  $
} = window;
const DEFAULT_INTERVAL_MS = 3000;
const MAX_INTERVAL_MS = 300000;
const MAX_ROWS = 10;
const TOOLTIP_DELAY_MS = 50;
/* harmony default export */ var LiveWidgetvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    liveRefreshAfterMs: Number,
    disableLink: Boolean
  },
  components: {
    MatomoLoader: external_CoreHome_["MatomoLoader"]
  },
  data() {
    return {
      isStarted: true,
      isStoppedByBlur: false,
      currentInterval: 0,
      updateInterval: null,
      isInitialLoading: true
    };
  },
  computed: {
    visitorLogUrl() {
      return `#?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        category: 'General_Visitors',
        subcategory: 'Live_VisitorLog'
      }))}`;
    }
  },
  mounted() {
    this.currentInterval = this.getBaseInterval();
    const root = this.$refs.root;
    if (root && !root.closest('.widget')) {
      external_CoreHome_["Matomo"].postEvent('hidePeriodSelector');
    }
    this.fetchInitialContent();
    this.setupVisibilityHandling();
  },
  beforeUnmount() {
    this.clearUpdate();
  },
  methods: {
    getBaseInterval() {
      const interval = Number(this.liveRefreshAfterMs);
      return Number.isFinite(interval) && interval > 0 ? interval : DEFAULT_INTERVAL_MS;
    },
    pause() {
      this.isStarted = false;
      this.clearUpdate();
    },
    play() {
      this.isStarted = true;
      this.currentInterval = 0;
      this.update();
    },
    clearUpdate() {
      if (this.updateInterval) {
        window.clearTimeout(this.updateInterval);
        this.updateInterval = null;
      }
    },
    scheduleUpdate(delayMs) {
      this.clearUpdate();
      if (!this.isStarted) {
        return;
      }
      this.updateInterval = window.setTimeout(() => {
        this.update();
      }, delayMs);
    },
    update() {
      if (this.isInitialLoading) {
        return;
      }
      if (!this.isStarted) {
        return;
      }
      const root = this.$refs.root;
      if (!root || !root.isConnected) {
        return;
      }
      const segment = external_CoreHome_["MatomoUrl"].parsed.value.segment;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'Live',
        action: 'getLastVisitsStart',
        segment
      }, {
        format: 'html'
      }).then(response => {
        const ensured = this.ensureVisitsList(response);
        const updated = ensured ? true : this.parseResponse(response);
        const baseInterval = this.getBaseInterval();
        if (!updated) {
          this.currentInterval += baseInterval;
        } else {
          this.currentInterval = baseInterval;
          this.refreshTotalVisitors(segment);
        }
        if (this.currentInterval > MAX_INTERVAL_MS) {
          this.currentInterval = MAX_INTERVAL_MS;
        }
        if (this.isStarted && root.isConnected) {
          this.scheduleUpdate(this.currentInterval);
        }
      }).catch(() => {
        if (this.isStarted && root.isConnected) {
          this.scheduleUpdate(this.getBaseInterval());
        }
      });
    },
    ensureVisitsList(response) {
      const root = this.$refs.root;
      if (!root) {
        return false;
      }
      if (root.querySelector('#visitsLive')) {
        return false;
      }
      const parser = new DOMParser();
      const doc = parser.parseFromString(response, 'text/html');
      const visitsList = doc.querySelector('#visitsLive');
      if (!visitsList) {
        return false;
      }
      root.appendChild(visitsList);
      external_CoreHome_["Matomo"].helper.compileVueEntryComponents(root);
      window.setTimeout(() => {
        this.initTooltips();
      }, TOOLTIP_DELAY_MS);
      return true;
    },
    refreshTotalVisitors(segment) {
      const root = this.$refs.root;
      if (!root) {
        return;
      }
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'Live',
        action: 'ajaxTotalVisitors',
        segment
      }, {
        format: 'html'
      }).then(response => {
        const container = root.querySelector('#visitsTotal');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = response;
        const newContent = wrapper.querySelector('#visitsTotal');
        if (!newContent) {
          return;
        }
        if (!container) {
          const list = root.querySelector('#visitsLive');
          if (list) {
            list.before(newContent);
          } else {
            root.prepend(newContent);
          }
          external_CoreHome_["Matomo"].helper.compileVueEntryComponents(root);
          return;
        }
        external_CoreHome_["Matomo"].helper.destroyVueComponent(container);
        container.replaceWith(newContent);
        external_CoreHome_["Matomo"].helper.compileVueEntryComponents(root);
      });
    },
    fetchInitialContent() {
      const segment = external_CoreHome_["MatomoUrl"].parsed.value.segment;
      const visitsPromise = external_CoreHome_["AjaxHelper"].fetch({
        module: 'Live',
        action: 'getLastVisitsStart',
        segment
      }, {
        format: 'html'
      });
      const totalPromise = external_CoreHome_["AjaxHelper"].fetch({
        module: 'Live',
        action: 'ajaxTotalVisitors',
        segment
      }, {
        format: 'html'
      });
      Promise.all([visitsPromise, totalPromise]).then(([visitsHtml, totalHtml]) => {
        const root = this.$refs.root;
        if (!root) {
          return;
        }
        root.innerHTML = `${totalHtml || ''}${visitsHtml || ''}`;
        external_CoreHome_["Matomo"].helper.compileVueEntryComponents(root);
        window.setTimeout(() => {
          this.initTooltips();
        }, TOOLTIP_DELAY_MS);
      }).catch(() => {
        // ignore initial errors, refresh loop will retry
      }).finally(() => {
        this.isInitialLoading = false;
        this.scheduleUpdate(this.currentInterval);
      });
    },
    parseResponse(response) {
      const root = this.$refs.root;
      if (!root) {
        return false;
      }
      const list = root.querySelector('#visitsLive');
      if (!list) {
        return false;
      }
      const parser = new DOMParser();
      const doc = parser.parseFromString(response, 'text/html');
      const items = Array.from(doc.querySelectorAll('li.visit'));
      if (!items.length) {
        return false;
      }
      this.clearTooltips();
      let updated = false;
      for (let i = items.length - 1; i >= 0; i -= 1) {
        const item = items[i];
        const visitId = item.getAttribute('id');
        if (visitId) {
          const existing = list.querySelector(`#${visitId}`);
          if (existing) {
            if (existing.innerHTML !== item.innerHTML) {
              updated = true;
            }
            existing.remove();
            list.insertBefore(item, list.firstChild);
          } else {
            updated = true;
            item.style.display = 'none';
            list.insertBefore(item, list.firstChild);
            this.fadeIn(item);
          }
        }
      }
      const visits = list.querySelectorAll('li.visit');
      for (let i = visits.length - 1; i >= MAX_ROWS; i -= 1) {
        visits[i].remove();
      }
      this.initTooltips();
      return updated;
    },
    fadeIn(item) {
      item.classList.add('live-widget-fade-in');
      item.style.display = '';
      item.addEventListener('animationend', () => {
        item.classList.remove('live-widget-fade-in');
      }, {
        once: true
      });
    },
    clearTooltips() {
      if (!$) {
        return;
      }
      const root = this.$refs.root;
      if (!root) {
        return;
      }
      const list = root.querySelector('#visitsLive');
      if (!list) {
        return;
      }
      const visits = $(list).find('li.visit .visitorLogIconWithDetails');
      visits.each(function clearExisting() {
        const visit = $(this);
        if (visit.data('ui-tooltip')) {
          visit.tooltip('destroy');
        }
      });
    },
    initTooltips() {
      if (!$) {
        return;
      }
      const root = this.$refs.root;
      if (!root) {
        return;
      }
      const list = root.querySelector('#visitsLive');
      if (!list) {
        return;
      }
      const visits = $(list).find('li.visit');
      visits.tooltip({
        items: '.visitorLogIconWithDetails',
        track: true,
        show: false,
        hide: false,
        content() {
          return $('<ul>').html($('ul', $(this)).html());
        },
        tooltipClass: 'small'
      });
    },
    setupVisibilityHandling() {
      const visibility = window.Visibility;
      if (!visibility || !visibility.isSupported || !visibility.isSupported()) {
        return;
      }
      visibility.change(() => {
        if (visibility.hidden()) {
          this.onTabBlur();
        } else {
          this.onTabFocus();
        }
      });
    },
    onTabBlur() {
      if (this.isStarted) {
        this.isStoppedByBlur = true;
        this.pause();
      }
    },
    onTabFocus() {
      if (this.isStoppedByBlur && !this.isStarted) {
        this.isStoppedByBlur = false;
        this.play();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/LiveWidget/LiveWidget.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/LiveWidget/LiveWidget.vue



LiveWidgetvue_type_script_lang_ts.render = render

/* harmony default export */ var LiveWidget = (LiveWidgetvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=template&id=c4046fce

const TotalVisitorsvue_type_template_id_c4046fce_hoisted_1 = {
  class: "dataTable",
  cellspacing: "0"
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_2 = {
  id: "label",
  class: "sortable label first",
  style: {
    "cursor": "auto"
  }
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_3 = {
  class: "thDIV"
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_4 = ["title"];
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_5 = {
  class: "thDIV"
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_6 = ["title"];
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_7 = {
  class: "thDIV"
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_8 = {
  class: ""
};
const TotalVisitorsvue_type_template_id_c4046fce_hoisted_9 = {
  class: "label column"
};
const _hoisted_10 = ["title"];
const _hoisted_11 = ["title"];
const _hoisted_12 = {
  class: ""
};
const _hoisted_13 = {
  class: "label column"
};
const _hoisted_14 = ["title"];
const _hoisted_15 = ["title"];
function TotalVisitorsvue_type_template_id_c4046fce_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", TotalVisitorsvue_type_template_id_c4046fce_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", TotalVisitorsvue_type_template_id_c4046fce_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TotalVisitorsvue_type_template_id_c4046fce_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Date')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    class: "sortable",
    style: {
      "cursor": "auto"
    },
    title: _ctx.translate('General_ColumnNbVisitsDocumentation')
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TotalVisitorsvue_type_template_id_c4046fce_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1)], 8, TotalVisitorsvue_type_template_id_c4046fce_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    class: "sortable",
    style: {
      "cursor": "auto"
    },
    title: _ctx.translate('General_ColumnNbActionsDocumentation')
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", TotalVisitorsvue_type_template_id_c4046fce_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Actions')), 1)], 8, TotalVisitorsvue_type_template_id_c4046fce_hoisted_6)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", TotalVisitorsvue_type_template_id_c4046fce_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", TotalVisitorsvue_type_template_id_c4046fce_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LastHours', 24)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorToday
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.visitorsCountToday || 0), 9, _hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorToday
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pisToday || 0), 9, _hoisted_11)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_LastMinutes', 30)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorHalfHour
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.visitorsCountHalfHour || 0), 9, _hoisted_14), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
    class: "column",
    title: _ctx.countErrorHalfHour
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pisHalfhour || 0), 9, _hoisted_15)])])])]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=template&id=c4046fce

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=script&lang=ts

/* harmony default export */ var TotalVisitorsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    countErrorToday: Number,
    visitorsCountToday: Number,
    pisToday: Number,
    countErrorHalfHour: Number,
    visitorsCountHalfHour: Number,
    pisHalfhour: Number
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/TotalVisitors/TotalVisitors.vue



TotalVisitorsvue_type_script_lang_ts.render = TotalVisitorsvue_type_template_id_c4046fce_render

/* harmony default export */ var TotalVisitors = (TotalVisitorsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=template&id=2ecbc076

function LivePagevue_type_template_id_2ecbc076_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_LiveWidget = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LiveWidget");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(!_ctx.isWidgetized ? 'ContentBlock' : 'Passthrough'), {
    "content-title": !_ctx.isWidgetized ? _ctx.translate('Live_VisitorsInRealTime') : undefined
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_LiveWidget, {
      "live-refresh-after-ms": _ctx.liveRefreshAfterMs,
      "disable-link": _ctx.disableLink
    }, null, 8, ["live-refresh-after-ms", "disable-link"])]),
    _: 1
  }, 8, ["content-title"]))]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=template&id=2ecbc076

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=script&lang=ts



/* harmony default export */ var LivePagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    disableLink: Boolean,
    liveRefreshAfterMs: Number,
    isWidgetized: Boolean
  },
  components: {
    LiveWidget: LiveWidget,
    ContentBlock: external_CoreHome_["ContentBlock"],
    Passthrough: external_CoreHome_["Passthrough"]
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/LivePage/LivePage.vue



LivePagevue_type_script_lang_ts.render = LivePagevue_type_template_id_2ecbc076_render

/* harmony default export */ var LivePage = (LivePagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=template&id=e270701e

function IndexHeadervue_type_template_id_e270701e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Live_VisitorLog')), 1)]),
    _: 1
  })])])), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=template&id=e270701e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=script&lang=ts


/* harmony default export */ var IndexHeadervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"]
  }
}));
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Live/vue/src/IndexHeader/IndexHeader.vue



IndexHeadervue_type_script_lang_ts.render = IndexHeadervue_type_template_id_e270701e_render

/* harmony default export */ var IndexHeader = (IndexHeadervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Live/vue/src/LastVisits/LastVisits.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $: LastVisits_$
} = window;
/* harmony default export */ var LastVisits = ({
  mounted(el) {
    LastVisits_$(el).off('click').on('click', '.visits-live-launch-visitor-profile', function onClickLaunchProfile(e) {
      e.preventDefault();
      window.broadcast.propagateNewPopoverParameter('visitorProfile', LastVisits_$(this).attr('data-visitor-id'));
      return false;
    }).tooltip({
      track: true,
      content() {
        const title = LastVisits_$(this).attr('title') || '';
        return window.vueSanitize(title.replace(/\n/g, '<br />'));
      },
      show: {
        delay: 100,
        duration: 0
      },
      hide: false
    });
  }
});
// CONCATENATED MODULE: ./plugins/Live/vue/src/index.ts
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
//# sourceMappingURL=Live.umd.js.map