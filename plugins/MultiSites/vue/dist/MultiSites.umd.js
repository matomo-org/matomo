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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=template&id=72fd34d8

var _hoisted_1 = {
  key: 0,
  class: "multisites-label label"
};
var _hoisted_2 = ["href"];
var _hoisted_3 = {
  key: 0
};
var _hoisted_4 = ["href", "title"];

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-outlink"
}, null, -1);

var _hoisted_6 = [_hoisted_5];
var _hoisted_7 = {
  key: 1,
  class: "multisites-label label"
};
var _hoisted_8 = {
  class: "value"
};
var _hoisted_9 = {
  class: "multisites-column"
};
var _hoisted_10 = {
  class: "value"
};
var _hoisted_11 = {
  class: "multisites-column"
};
var _hoisted_12 = {
  class: "value"
};
var _hoisted_13 = {
  key: 2,
  class: "multisites-column"
};
var _hoisted_14 = {
  class: "value"
};
var _hoisted_15 = ["title"];
var _hoisted_16 = {
  key: 0,
  class: "visits value"
};

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/arrow_up.png",
  alt: ""
}, null, -1);

var _hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])();

var _hoisted_19 = {
  style: {
    "color": "green"
  }
};

var _hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/stop.png",
  alt: ""
}, null, -1);

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])();

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
  class: "multisites_icon",
  src: "plugins/MultiSites/images/arrow_down.png",
  alt: ""
}, null, -1);

var _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])();

var _hoisted_24 = {
  style: {
    "color": "red"
  }
};
var _hoisted_25 = {
  key: 4,
  style: {
    "width": "180px"
  }
};
var _hoisted_26 = {
  key: 0,
  class: "sparkline",
  style: {
    "width": "100px",
    "margin": "auto"
  }
};
var _hoisted_27 = ["href", "title"];
var _hoisted_28 = ["src"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'groupedWebsite': _ctx.website.group,
      'website': !_ctx.website.group,
      'group': _ctx.website.isGroup
    }),
    ref: "root"
  }, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    title: "View reports",
    class: "value truncated-text-line",
    href: _ctx.dashboardUrl(_ctx.website)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.websiteLabel), 9, _hoisted_2), _ctx.website.main_url ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.website.main_url,
    title: _ctx.translate('General_GoTo', _ctx.website.main_url)
  }, _hoisted_6, 8, _hoisted_4)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.websiteLabel), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.nb_visits), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.nb_pageviews), 1)]), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website.revenue), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.period !== 'range' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
    key: 3,
    class: "multisites-evolution",
    title: _ctx.website.tooltip
  }, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_17, _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website["".concat(_ctx.evolutionMetric, "_trend")] === 1]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_20, _hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website["".concat(_ctx.evolutionMetric, "_trend")] === 0]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_22, _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.website[_ctx.evolutionMetric]), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.website["".concat(_ctx.evolutionMetric, "_trend")] === -1]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, _hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showSparklines ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_25, [!_ctx.website.isGroup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.dashboardUrl(_ctx.website),
    title: _ctx.translate('General_GoTo', _ctx.translate('Dashboard_DashboardOf', _ctx.websiteLabel))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    alt: "",
    width: "100",
    height: "25",
    src: _ctx.sparklineImage(_ctx.website)
  }, null, 8, _hoisted_28)], 8, _hoisted_27)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=template&id=72fd34d8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=script&lang=ts


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
  mounted: function mounted() {
    external_CoreHome_["Matomo"].postEvent('MultiSites.MultiSitesSite.mounted', {
      element: this.$refs.root
    });
  },
  unmounted: function unmounted() {
    external_CoreHome_["Matomo"].postEvent('MultiSites.MultiSitesSite.unmounted', {
      element: this.$refs.root
    });
  },
  methods: {
    dashboardUrl: function dashboardUrl(website) {
      return "index.php?module=CoreHome&action=index&date=".concat(this.date, "&period=").concat(this.period) + "&idSite=".concat(website.idsite).concat(this.tokenParam);
    },
    sparklineImage: function sparklineImage(website) {
      var metric = this.metric;

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

      return "index.php?module=MultiSites&action=getEvolutionGraph&period=".concat(this.period, "&date=") + "".concat(this.dateSparkline, "&evolutionBy=").concat(metric, "&columns=").concat(metric, "&idSite=").concat(website.idsite) + "&idsite=".concat(website.idsite, "&viewDataTable=sparkline").concat(this.tokenParam, "&colors=") + "".concat(encodeURIComponent(JSON.stringify(external_CoreHome_["Matomo"].getSparklineColors())));
    }
  },
  computed: {
    tokenParam: function tokenParam() {
      var token_auth = external_CoreHome_["MatomoUrl"].urlParsed.value.token_auth;
      return token_auth ? "&token_auth=".concat(token_auth) : '';
    },
    period: function period() {
      return external_CoreHome_["Matomo"].period;
    },
    date: function date() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.date;
    },
    websiteLabel: function websiteLabel() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(this.website.label);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.vue



MultisitesSitevue_type_script_lang_ts.render = render

/* harmony default export */ var MultisitesSite = (MultisitesSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/MultisitesSite/MultisitesSite.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var MultisitesSite_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: MultisitesSite,
  scope: {
    website: {
      angularJsBind: '='
    },
    evolutionMetric: {
      angularJsBind: '='
    },
    showSparklines: {
      angularJsBind: '='
    },
    dateSparkline: {
      angularJsBind: '='
    },
    displayRevenueColumn: {
      angularJsBind: '='
    },
    metric: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikMultisitesSite'
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=template&id=31690b13

var Dashboardvue_type_template_id_31690b13_hoisted_1 = {
  ref: "root"
};
var Dashboardvue_type_template_id_31690b13_hoisted_2 = {
  class: "card-title"
};
var Dashboardvue_type_template_id_31690b13_hoisted_3 = ["innerHTML", "title"];
var Dashboardvue_type_template_id_31690b13_hoisted_4 = {
  id: "mt",
  class: "dataTable card-table",
  cellspacing: "0"
};
var Dashboardvue_type_template_id_31690b13_hoisted_5 = {
  class: "heading"
};
var Dashboardvue_type_template_id_31690b13_hoisted_6 = {
  class: "heading"
};
var Dashboardvue_type_template_id_31690b13_hoisted_7 = {
  class: "heading"
};
var Dashboardvue_type_template_id_31690b13_hoisted_8 = {
  class: "heading"
};
var Dashboardvue_type_template_id_31690b13_hoisted_9 = ["colspan"];
var Dashboardvue_type_template_id_31690b13_hoisted_10 = ["value"];
var Dashboardvue_type_template_id_31690b13_hoisted_11 = {
  value: "visits_evolution"
};
var Dashboardvue_type_template_id_31690b13_hoisted_12 = {
  value: "pageviews_evolution"
};
var Dashboardvue_type_template_id_31690b13_hoisted_13 = {
  key: 0,
  value: "revenue_evolution"
};
var Dashboardvue_type_template_id_31690b13_hoisted_14 = {
  key: 0
};
var Dashboardvue_type_template_id_31690b13_hoisted_15 = {
  colspan: "7",
  class: "allWebsitesLoading"
};
var Dashboardvue_type_template_id_31690b13_hoisted_16 = {
  key: 1
};
var Dashboardvue_type_template_id_31690b13_hoisted_17 = {
  key: 0
};
var Dashboardvue_type_template_id_31690b13_hoisted_18 = {
  colspan: "7"
};
var Dashboardvue_type_template_id_31690b13_hoisted_19 = {
  class: "notification system notification-error"
};

var Dashboardvue_type_template_id_31690b13_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Dashboardvue_type_template_id_31690b13_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var Dashboardvue_type_template_id_31690b13_hoisted_22 = {
  rel: "noreferrer noopener",
  target: "_blank",
  href: "https://matomo.org/faq/troubleshooting/faq_19489/"
};

var Dashboardvue_type_template_id_31690b13_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" – ");

var Dashboardvue_type_template_id_31690b13_hoisted_24 = {
  rel: "noreferrer noopener",
  target: "_blank",
  href: "https://forum.matomo.org/"
};
var Dashboardvue_type_template_id_31690b13_hoisted_25 = ["href"];

var Dashboardvue_type_template_id_31690b13_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". ");

var Dashboardvue_type_template_id_31690b13_hoisted_27 = {
  colspan: "8",
  class: "paging"
};
var Dashboardvue_type_template_id_31690b13_hoisted_28 = {
  class: "row"
};
var _hoisted_29 = {
  class: "col s3 add_new_site"
};
var _hoisted_30 = ["href"];

var _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var _hoisted_32 = {
  class: "col s6"
};
var _hoisted_33 = {
  style: {
    "cursor": "pointer"
  }
};
var _hoisted_34 = {
  class: "dataTablePages"
};
var _hoisted_35 = {
  id: "counter"
};
var _hoisted_36 = {
  style: {
    "cursor": "pointer"
  },
  class: "pointer"
};

var _hoisted_37 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "col s3"
}, " ", -1);

var _hoisted_38 = {
  row_id: "last"
};
var _hoisted_39 = {
  colspan: "8",
  class: "site_search"
};
var _hoisted_40 = {
  class: "row"
};
var _hoisted_41 = {
  class: "input-field col s12"
};
var _hoisted_42 = ["placeholder"];
var _hoisted_43 = ["title"];
function Dashboardvue_type_template_id_31690b13_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _this = this;

  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_MultisitesSite = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultisitesSite");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", Dashboardvue_type_template_id_31690b13_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", Dashboardvue_type_template_id_31690b13_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": "https://matomo.org/faq/new-to-piwik/all-websites-dashboard/",
    "feature-name": _ctx.translate('General_AllWebsitesDashboard')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_AllWebsitesDashboard')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: "smallTitle",
        innerHTML: _ctx.$sanitize(_this.smallTitleContent),
        title: _ctx.smallTitleTooltip
      }, null, 8, Dashboardvue_type_template_id_31690b13_hoisted_3)];
    }),
    _: 1
  }, 8, ["feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", Dashboardvue_type_template_id_31690b13_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "names",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["label", {
      columnSorted: 'label' === _ctx.sortColumn
    }]),
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.sortBy('label');
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_31690b13_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Website')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
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
    onClick: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.sortBy('nb_visits');
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'nb_visits' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'nb_visits' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_31690b13_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
    id: "pageviews",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multisites-column", {
      columnSorted: 'nb_pageviews' === _ctx.sortColumn
    }]),
    onClick: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.sortBy('nb_pageviews');
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'nb_pageviews' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'nb_pageviews' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_31690b13_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1)], 2), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
    key: 0,
    id: "revenue",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["multisites-column", {
      columnSorted: 'revenue' === _ctx.sortColumn
    }]),
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.sortBy('revenue');
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["arrow", {
      multisites_asc: !_ctx.reverse && 'revenue' === _ctx.sortColumn,
      multisites_desc: _ctx.reverse && 'revenue' === _ctx.sortColumn
    }]),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", Dashboardvue_type_template_id_31690b13_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
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
    onClick: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.sortBy(_ctx.evolutionSelector);
    }),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MultiSites_Evolution')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "selector browser-default",
    id: "evolution_selector",
    value: _ctx.evolutionSelector,
    onChange: _cache[5] || (_cache[5] = function ($event) {
      _ctx.evolutionSelector = $event.target.value;

      _ctx.sortBy(_ctx.evolutionSelector);
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", Dashboardvue_type_template_id_31690b13_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", Dashboardvue_type_template_id_31690b13_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnPageviews')), 1), _ctx.displayRevenueColumn ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", Dashboardvue_type_template_id_31690b13_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, Dashboardvue_type_template_id_31690b13_hoisted_10)], 10, Dashboardvue_type_template_id_31690b13_hoisted_9)])]), _ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tbody", Dashboardvue_type_template_id_31690b13_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_31690b13_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    "loading-message": _ctx.loadingMessage,
    loading: _ctx.isLoading
  }, null, 8, ["loading-message", "loading"])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tbody", Dashboardvue_type_template_id_31690b13_hoisted_16, [_ctx.errorLoadingSites ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", Dashboardvue_type_template_id_31690b13_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_31690b13_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Dashboardvue_type_template_id_31690b13_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ErrorRequest', '', '')) + " ", 1), Dashboardvue_type_template_id_31690b13_hoisted_20, Dashboardvue_type_template_id_31690b13_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_NeedMoreHelp')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", Dashboardvue_type_template_id_31690b13_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Faq')), 1), Dashboardvue_type_template_id_31690b13_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", Dashboardvue_type_template_id_31690b13_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_CommunityHelp')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, " – ", 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.areAdsForProfessionalServicesEnabled]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    rel: "noreferrer noopener",
    target: "_blank",
    href: _ctx.professionalHelpUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_ProfessionalHelp')), 9, Dashboardvue_type_template_id_31690b13_hoisted_25), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.areAdsForProfessionalServicesEnabled]]), Dashboardvue_type_template_id_31690b13_hoisted_26])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sites, function (website) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_MultisitesSite, {
      key: website.idsite,
      website: website,
      "evolution-metric": _ctx.evolutionSelector,
      "date-sparkline": _ctx.dateSparkline,
      "show-sparklines": _ctx.showSparklines,
      metric: _ctx.sortColumn,
      "display-revenue-column": _ctx.displayRevenueColumn
    }, null, 8, ["website", "evolution-metric", "date-sparkline", "show-sparklines", "metric", "display-revenue-column"]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tfoot", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", Dashboardvue_type_template_id_31690b13_hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Dashboardvue_type_template_id_31690b13_hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_29, [_ctx.hasSuperUserAccess ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    href: _ctx.addSiteUrl
  }, [_hoisted_31, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('SitesManager_AddSite')), 1)], 8, _hoisted_30)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    id: "prev",
    class: "previous dataTablePrevious",
    onClick: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.previousPage();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_33, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.currentPage === 0)]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Pagination', _ctx.paginationLowerBound, _ctx.paginationUpperBound, _ctx.numberOfFilteredSites)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    id: "next",
    class: "next dataTableNext",
    onClick: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.nextPage();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_36, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !(_ctx.currentPage >= _ctx.numberOfPages)]])]), _hoisted_37])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_40, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    onKeydown: _cache[8] || (_cache[8] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(function ($event) {
      return _ctx.searchSite(_ctx.searchTerm);
    }, ["enter"])),
    "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    placeholder: _ctx.translate('Actions_SubmenuSitesearch')
  }, null, 40, _hoisted_42), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search search_ico",
    onClick: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.searchSite(_ctx.searchTerm);
    }),
    title: _ctx.translate('General_ClickToSearch')
  }, null, 8, _hoisted_43)])])])])])])], 512);
}
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=template&id=31690b13

// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.store.ts
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


var _window = window,
    NumberFormatter = _window.NumberFormatter;

var Dashboard_store_DashboardStore = /*#__PURE__*/function () {
  function DashboardStore() {
    var _this = this;

    _classCallCheck(this, DashboardStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
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

    _defineProperty(this, "refreshTimeout", null);

    _defineProperty(this, "fetchAbort", null);

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "numberOfFilteredSites", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.numberOfSites;
    }));

    _defineProperty(this, "numberOfPages", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Math.ceil(_this.numberOfFilteredSites.value / _this.state.value.pageSize - 1);
    }));

    _defineProperty(this, "currentPagingOffset", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Math.ceil(_this.state.value.currentPage * _this.state.value.pageSize);
    }));

    _defineProperty(this, "paginationLowerBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.currentPagingOffset.value + 1;
    }));

    _defineProperty(this, "paginationUpperBound", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      var end = _this.currentPagingOffset.value + _this.state.value.pageSize;
      var max = _this.numberOfFilteredSites.value;

      if (end > max) {
        end = max;
      }

      return end;
    }));
  }

  _createClass(DashboardStore, [{
    key: "cancelRefereshInterval",
    value: function cancelRefereshInterval() {
      if (this.refreshTimeout) {
        clearTimeout(this.refreshTimeout);
        this.refreshTimeout = null;
      }
    }
  }, {
    key: "updateWebsitesList",
    value: function updateWebsitesList(report) {
      var _this2 = this;

      if (!report) {
        this.onError();
        return;
      }

      var allSites = report.sites;
      allSites.forEach(function (site) {
        if (site.ratio !== 1 && site.ratio !== '1') {
          var percent = NumberFormatter.formatPercent(Math.round(parseInt(site.ratio, 10) * 100));
          var metricName = null;
          var previousTotal = '0';
          var currentTotal = '0';
          var evolution = '0';
          var previousTotalAdjusted = '0';

          if (_this2.state.value.sortColumn === 'nb_visits' || _this2.state.value.sortColumn === 'visits_evolution') {
            previousTotal = NumberFormatter.formatNumber(site.previous_nb_visits);
            currentTotal = NumberFormatter.formatNumber(site.nb_visits);
            evolution = NumberFormatter.formatPercent(site.visits_evolution);
            metricName = Object(external_CoreHome_["translate"])('General_ColumnNbVisits');
            previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(parseInt(site.previous_nb_visits, 10) * parseInt(site.ratio, 10)));
          }

          if (_this2.state.value.sortColumn === 'pageviews_evolution') {
            previousTotal = "".concat(site.previous_Actions_nb_pageviews);
            currentTotal = "".concat(site.nb_pageviews);
            evolution = NumberFormatter.formatPercent(site.pageviews_evolution);
            metricName = Object(external_CoreHome_["translate"])('General_ColumnPageviews');
            previousTotalAdjusted = NumberFormatter.formatNumber(Math.round(parseInt(site.previous_Actions_nb_pageviews, 10) * parseInt(site.ratio, 10)));
          }

          if (_this2.state.value.sortColumn === 'revenue_evolution') {
            previousTotal = NumberFormatter.formatCurrency(site.previous_Goal_revenue, site.currencySymbol);
            currentTotal = NumberFormatter.formatCurrency(site.revenue, site.currencySymbol);
            evolution = NumberFormatter.formatPercent(site.revenue_evolution);
            metricName = Object(external_CoreHome_["translate"])('General_ColumnRevenue');
            previousTotalAdjusted = NumberFormatter.formatCurrency(Math.round(parseInt(site.previous_Goal_revenue, 10) * parseInt(site.ratio, 10)), site.currencySymbol);
          }

          if (metricName) {
            site.tooltip = "".concat(Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonIncomplete', [percent]), "\n");
            site.tooltip += "".concat(Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonProportional', [percent, "".concat(previousTotalAdjusted), metricName, "".concat(previousTotal)]), "\n");

            switch (site.periodName) {
              case 'day':
                site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonDay', ["".concat(currentTotal), metricName, "".concat(previousTotalAdjusted), site.previousRange, "".concat(evolution)]);
                break;

              case 'week':
                site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonWeek', ["".concat(currentTotal), metricName, "".concat(previousTotalAdjusted), site.previousRange, "".concat(evolution)]);
                break;

              case 'month':
                site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonMonth', ["".concat(currentTotal), metricName, "".concat(previousTotalAdjusted), site.previousRange, "".concat(evolution)]);
                break;

              case 'year':
                site.tooltip += Object(external_CoreHome_["translate"])('MultiSites_EvolutionComparisonYear', ["".concat(currentTotal), metricName, "".concat(previousTotalAdjusted), site.previousRange, "".concat(evolution)]);
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
  }, {
    key: "sortBy",
    value: function sortBy(metric) {
      if (this.state.value.sortColumn === metric) {
        this.privateState.reverse = !this.state.value.reverse;
      }

      this.privateState.sortColumn = metric;
      this.fetchAllSites();
    }
  }, {
    key: "previousPage",
    value: function previousPage() {
      this.privateState.currentPage = this.state.value.currentPage - 1;
      this.fetchAllSites();
    }
  }, {
    key: "nextPage",
    value: function nextPage() {
      this.privateState.currentPage = this.state.value.currentPage + 1;
      this.fetchAllSites();
    }
  }, {
    key: "searchSite",
    value: function searchSite(term) {
      this.privateState.searchTerm = term;
      this.privateState.currentPage = 0;
      this.fetchAllSites();
    }
  }, {
    key: "fetchAllSites",
    value: function fetchAllSites() {
      var _this3 = this;

      if (this.fetchAbort) {
        this.fetchAbort.abort();
        this.fetchAbort = null;
        this.cancelRefereshInterval();
      }

      this.privateState.isLoading = true;
      this.privateState.errorLoadingSites = false;
      var params = {
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
      }).then(function (response) {
        _this3.updateWebsitesList(response);
      }).catch(function () {
        _this3.onError();
      }).finally(function () {
        _this3.privateState.isLoading = false;
        _this3.fetchAbort = null;

        if (_this3.state.value.refreshInterval && _this3.state.value.refreshInterval > 0) {
          _this3.cancelRefereshInterval();

          _this3.refreshTimeout = setTimeout(function () {
            _this3.refreshTimeout = null;

            _this3.fetchAllSites();
          }, _this3.state.value.refreshInterval * 1000);
        }
      });
    }
  }, {
    key: "onError",
    value: function onError() {
      this.privateState.errorLoadingSites = true;
      this.privateState.sites = [];
    }
  }, {
    key: "setRefreshInterval",
    value: function setRefreshInterval(interval) {
      this.privateState.refreshInterval = interval;
    }
  }, {
    key: "setPageSize",
    value: function setPageSize(pageSize) {
      this.privateState.pageSize = pageSize;
    }
  }]);

  return DashboardStore;
}();

/* harmony default export */ var Dashboard_store = (new Dashboard_store_DashboardStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=script&lang=ts




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
  data: function data() {
    return {
      evolutionSelector: 'visits_evolution',
      searchTerm: ''
    };
  },
  created: function created() {
    if (this.pageSize) {
      Dashboard_store.setPageSize(this.pageSize);
    }

    this.refresh(this.autoRefreshTodayReport);
  },
  methods: {
    refresh: function refresh(interval) {
      Dashboard_store.setRefreshInterval(interval);
      Dashboard_store.fetchAllSites();
    },
    sortBy: function sortBy(column) {
      Dashboard_store.sortBy(column);
    },
    previousPage: function previousPage() {
      Dashboard_store.previousPage();
    },
    nextPage: function nextPage() {
      Dashboard_store.nextPage();
    },
    searchSite: function searchSite() {
      Dashboard_store.searchSite(this.searchTerm);
    }
  },
  computed: {
    hasSuperUserAccess: function hasSuperUserAccess() {
      return external_CoreHome_["Matomo"].hasSuperUserAccess;
    },
    date: function date() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.date;
    },
    idSite: function idSite() {
      return external_CoreHome_["MatomoUrl"].urlParsed.value.idSite;
    },
    url: function url() {
      return external_CoreHome_["Matomo"].piwik_url;
    },
    period: function period() {
      return external_CoreHome_["Matomo"].period;
    },
    areAdsForProfessionalServicesEnabled: function areAdsForProfessionalServicesEnabled() {
      return external_CoreHome_["Matomo"].config && external_CoreHome_["Matomo"].config.are_ads_enabled;
    },
    sortColumn: function sortColumn() {
      return Dashboard_store.state.value.sortColumn;
    },
    reverse: function reverse() {
      return Dashboard_store.state.value.reverse;
    },
    smallTitleContent: function smallTitleContent() {
      var state = Dashboard_store.state.value;
      return Object(external_CoreHome_["translate"])('General_TotalVisitsPageviewsActionsRevenue', "<strong>".concat(state.totalVisits, "</strong>"), "<strong>".concat(state.totalPageviews, "</strong>"), "<strong>".concat(state.totalActions, "</strong>"), "<strong>".concat(state.totalRevenue, "</strong>"));
    },
    smallTitleTooltip: function smallTitleTooltip() {
      var state = Dashboard_store.state.value;
      return Object(external_CoreHome_["translate"])('General_EvolutionSummaryGeneric', Object(external_CoreHome_["translate"])('General_NVisits', "".concat(state.totalVisits)), this.date, "".concat(state.lastVisits), state.lastVisitsDate, Object(external_CoreHome_["getFormattedEvolution"])(state.totalVisits, state.lastVisits));
    },
    loadingMessage: function loadingMessage() {
      return Dashboard_store.state.value.loadingMessage;
    },
    isLoading: function isLoading() {
      return Dashboard_store.state.value.isLoading;
    },
    errorLoadingSites: function errorLoadingSites() {
      return Dashboard_store.state.value.errorLoadingSites;
    },
    sites: function sites() {
      return Dashboard_store.state.value.sites;
    },
    numberOfPages: function numberOfPages() {
      return Dashboard_store.numberOfPages.value;
    },
    currentPage: function currentPage() {
      return Dashboard_store.state.value.currentPage;
    },
    paginationLowerBound: function paginationLowerBound() {
      return Dashboard_store.paginationLowerBound.value;
    },
    paginationUpperBound: function paginationUpperBound() {
      return Dashboard_store.paginationUpperBound.value;
    },
    numberOfFilteredSites: function numberOfFilteredSites() {
      return Dashboard_store.numberOfFilteredSites.value;
    },
    professionalHelpUrl: function professionalHelpUrl() {
      return 'https://matomo.org/support-plans/?pk_campaign=Help&pk_medium=AjaxError&pk_content=' + 'MultiSites&pk_source=Matomo_App';
    },
    addSiteUrl: function addSiteUrl() {
      return "index.php?module=SitesManager&action=index&showaddsite=1&period=".concat(this.period, "&") + "date=".concat(this.date, "&idSite=").concat(this.idSite);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.vue



Dashboardvue_type_script_lang_ts.render = Dashboardvue_type_template_id_31690b13_render

/* harmony default export */ var Dashboard = (Dashboardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/Dashboard/Dashboard.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var Dashboard_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: Dashboard,
  scope: {
    displayRevenueColumn: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    showSparklines: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    dateSparkline: {
      angularJsBind: '@'
    },
    pageSize: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    autoRefreshTodayReport: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    }
  },
  directiveName: 'piwikMultisitesDashboard'
}));
// CONCATENATED MODULE: ./plugins/MultiSites/vue/src/index.ts
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
//# sourceMappingURL=MultiSites.umd.js.map