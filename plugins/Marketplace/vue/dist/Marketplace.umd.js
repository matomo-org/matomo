(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Marketplace"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Marketplace"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__) {
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
/******/ 	__webpack_require__.p = "plugins/Marketplace/vue/dist/";
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

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "Marketplace", function() { return /* reexport */ Marketplace; });
__webpack_require__.d(__webpack_exports__, "LicenseKey", function() { return /* reexport */ LicenseKey; });
__webpack_require__.d(__webpack_exports__, "ManageLicenseKey", function() { return /* reexport */ ManageLicenseKey; });
__webpack_require__.d(__webpack_exports__, "GetNewPlugins", function() { return /* reexport */ GetNewPlugins; });
__webpack_require__.d(__webpack_exports__, "GetNewPluginsAdmin", function() { return /* reexport */ GetNewPluginsAdmin; });
__webpack_require__.d(__webpack_exports__, "GetPremiumFeatures", function() { return /* reexport */ GetPremiumFeatures; });
__webpack_require__.d(__webpack_exports__, "MissingReqsNotice", function() { return /* reexport */ MissingReqsNotice; });
__webpack_require__.d(__webpack_exports__, "OverviewIntro", function() { return /* reexport */ OverviewIntro; });
__webpack_require__.d(__webpack_exports__, "SubscriptionOverview", function() { return /* reexport */ SubscriptionOverview; });
__webpack_require__.d(__webpack_exports__, "RichMenuButton", function() { return /* reexport */ RichMenuButton; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=5776cc38

var _hoisted_1 = {
  class: "row marketplaceActions",
  ref: "root"
};
var _hoisted_2 = {
  class: "col s12 m6 l4"
};
var _hoisted_3 = {
  class: "col s12 m6 l4"
};
var _hoisted_4 = {
  key: 0,
  class: "col s12 m12 l4 "
};
var _hoisted_5 = ["action"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$pluginsToShow;

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "plugin_type",
    "model-value": _ctx.pluginTypeFilter,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.pluginTypeFilter = $event;

      _ctx.changePluginType();
    }),
    title: _ctx.translate('Marketplace_Show'),
    "full-width": true,
    options: _ctx.pluginTypeOptions
  }, null, 8, ["model-value", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "plugin_sort",
    "model-value": _ctx.pluginSort,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      _ctx.pluginSort = $event;

      _ctx.changePluginSort();
    }),
    title: _ctx.translate('Marketplace_Sort'),
    "full-width": true,
    options: _ctx.pluginSortOptions
  }, null, 8, ["model-value", "title", "options"])]), ((_ctx$pluginsToShow = _ctx.pluginsToShow) === null || _ctx$pluginsToShow === void 0 ? void 0 : _ctx$pluginsToShow.length) > 20 || _ctx.query ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
    method: "post",
    class: "plugin-search",
    action: _ctx.pluginSearchFormAction,
    ref: "pluginSearchForm"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "query",
    title: _ctx.queryInputTitle,
    "full-width": true,
    modelValue: _ctx.searchQuery,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.searchQuery = $event;
    })
  }, null, 8, ["title", "modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "icon-search",
    onClick: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.$refs.pluginSearchForm.submit();
    })
  })], 8, _hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=5776cc38

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts




var lcfirst = function lcfirst(s) {
  return "".concat(s[0].toLowerCase()).concat(s.substring(1));
};

var _window = window,
    $ = _window.$;
/* harmony default export */ var Marketplacevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    pluginType: {
      type: String,
      required: true
    },
    pluginTypeOptions: {
      type: [Object, Array],
      required: true
    },
    sort: {
      type: String,
      required: true
    },
    pluginSortOptions: {
      type: [Object, Array],
      required: true
    },
    pluginsToShow: {
      type: Array,
      required: true
    },
    query: {
      type: String,
      default: ''
    },
    numAvailablePlugins: {
      type: Number,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  data: function data() {
    return {
      pluginSort: this.sort,
      pluginTypeFilter: this.pluginType,
      searchQuery: this.query
    };
  },
  mounted: function mounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.mounted', {
      element: this.$refs.root
    });
  },
  unmounted: function unmounted() {
    external_CoreHome_["Matomo"].postEvent('Marketplace.Marketplace.unmounted', {
      element: this.$refs.root
    });
  },
  created: function created() {
    var addCardClickHandler = function addCardClickHandler(selector) {
      var $nodes = $(selector);

      if (!$nodes || !$nodes.length) {
        return;
      }

      $nodes.each(function (index, node) {
        var $card = $(node);
        $card.off('click.cardClick');
        $card.on('click.cardClick', function (event) {
          // check if the target is a link or is a descendant of a link
          // to skip direct clicks on links within the card, we want those honoured
          if ($(event.target).closest('a').length) {
            return;
          }

          var $titleLink = $card.find('a.card-title-link');

          if ($titleLink) {
            event.stopPropagation();
            $titleLink.trigger('click');
          }
        });
      });
    };

    var shrinkDescriptionIfMultilineTitle = Object(external_CoreHome_["debounce"])(function (selector) {
      var $nodes = $(selector);

      if (!$nodes || !$nodes.length) {
        return;
      }

      $nodes.each(function (index, node) {
        var $card = $(node);
        var $titleText = $card.find('.card-title');
        var $alertText = $card.find('.card-content-bottom .alert');
        var hasDownloads = $card.hasClass('card-with-downloads');
        var titleLines = 1;

        if ($titleText.length) {
          var elHeight = +$titleText.height();
          var lineHeight = +$titleText.css('line-height').replace('px', '');

          if (lineHeight) {
            var _Math$ceil;

            titleLines = (_Math$ceil = Math.ceil(elHeight / lineHeight)) !== null && _Math$ceil !== void 0 ? _Math$ceil : 1;
          }
        }

        var alertLines = 0;

        if ($alertText.length) {
          var _elHeight = +$alertText.height();

          var _lineHeight = +$alertText.css('line-height').replace('px', '');

          if (_lineHeight) {
            var _Math$ceil2;

            alertLines = (_Math$ceil2 = Math.ceil(_elHeight / _lineHeight)) !== null && _Math$ceil2 !== void 0 ? _Math$ceil2 : 1;
          }
        }

        var $cardDescription = $card.find('.card-description');

        if ($cardDescription.length) {
          var cardDescription = $cardDescription[0];
          var clampedLines = 0; // a bit convoluted logic, but this is what's been arrived at with a designer
          // and via testing in browser
          //
          // a) visible downloads count
          //    -> clamp to 2 lines if title is 2 lines or more or alert is 2 lines or more
          //       or together are more than 3 lines
          //    -> clamp to 1 line if title is over 2 lines and alert is over 2 lines simultaneously
          // b) no downloads count (i.e. a premium plugin)
          //    -> clamp to 2 lines if sum of lines for title and notification is over 4

          if (hasDownloads) {
            if (titleLines >= 2 || alertLines > 2 || titleLines + alertLines >= 4) {
              clampedLines = 2;
            }

            if (titleLines + alertLines >= 5) {
              clampedLines = 1;
            }
          } else if (titleLines + alertLines >= 5) {
            clampedLines = 2;
          }

          if (clampedLines) {
            cardDescription.setAttribute('data-clamp', "".concat(clampedLines));
          } else {
            cardDescription.removeAttribute('data-clamp');
          }
        }
      });
    }, 100);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      var cardSelector = '.marketplace .card-holder';
      addCardClickHandler(cardSelector);
      shrinkDescriptionIfMultilineTitle(cardSelector);
      $(window).resize(function () {
        shrinkDescriptionIfMultilineTitle(cardSelector);
      });
    });
  },
  methods: {
    changePluginSort: function changePluginSort() {
      external_CoreHome_["MatomoUrl"].updateUrl(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        query: '',
        sort: this.pluginSort
      }), Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        query: '',
        sort: this.pluginSort
      }));
    },
    changePluginType: function changePluginType() {
      external_CoreHome_["MatomoUrl"].updateUrl(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        query: '',
        show: this.pluginTypeFilter
      }), Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        query: '',
        show: this.pluginTypeFilter
      }));
    }
  },
  computed: {
    pluginSearchFormAction: function pluginSearchFormAction() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        sort: '',
        embed: '0'
      })), "#?").concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        sort: '',
        embed: '0',
        query: this.searchQuery
      })));
    },
    queryInputTitle: function queryInputTitle() {
      var plugins = lcfirst(Object(external_CoreHome_["translate"])('General_Plugins'));
      return "".concat(Object(external_CoreHome_["translate"])('General_Search'), " ").concat(this.numAvailablePlugins, " ").concat(plugins, "...");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue



Marketplacevue_type_script_lang_ts.render = render

/* harmony default export */ var Marketplace = (Marketplacevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=39c51746

var LicenseKeyvue_type_template_id_39c51746_hoisted_1 = {
  class: "marketplace-max-width"
};
var LicenseKeyvue_type_template_id_39c51746_hoisted_2 = {
  class: "marketplace-paid-intro"
};
var LicenseKeyvue_type_template_id_39c51746_hoisted_3 = {
  key: 0
};
var LicenseKeyvue_type_template_id_39c51746_hoisted_4 = {
  key: 0
};

var LicenseKeyvue_type_template_id_39c51746_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_6 = {
  class: "licenseToolbar valign-wrapper"
};
var _hoisted_7 = ["href"];
var _hoisted_8 = {
  key: 0
};
var _hoisted_9 = {
  class: "ui-confirm",
  id: "installAllPaidPluginsAtOnce",
  ref: "installAllPaidPluginsAtOnce"
};

var _hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_12 = ["data-href", "value"];
var _hoisted_13 = ["value"];
var _hoisted_14 = {
  key: 1
};
var _hoisted_15 = {
  key: 0
};
var _hoisted_16 = ["innerHTML"];

var _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_18 = {
  class: "licenseToolbar valign-wrapper"
};
var _hoisted_19 = {
  key: 1
};
var _hoisted_20 = ["innerHTML"];
var _hoisted_21 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
var _hoisted_22 = ["value"];
var _hoisted_23 = ["value"];
function LicenseKeyvue_type_template_id_39c51746_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DefaultLicenseKeyFields = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DefaultLicenseKeyFields");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_39c51746_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_39c51746_hoisted_2, [_ctx.isValidConsumer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_39c51746_hoisted_3, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_39c51746_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PaidPluginsWithLicenseKeyIntro', '')) + " ", 1), LicenseKeyvue_type_template_id_39c51746_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DefaultLicenseKeyFields, {
    "model-value": _ctx.licenseKey,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.licenseKey = $event;

      _ctx.updatedLicenseKey();
    }),
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.updateLicense();
    }),
    "has-license-key": _ctx.hasLicenseKey,
    "is-valid-consumer": _ctx.isValidConsumer,
    "enable-update": _ctx.enableUpdate
  }, null, 8, ["model-value", "has-license-key", "is-valid-consumer", "enable-update"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    id: "remove_license_key",
    onConfirm: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.removeLicense();
    }),
    value: _ctx.translate('Marketplace_RemoveLicenseKey')
  }, null, 8, ["value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn valign",
    href: _ctx.subscriptionOverviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ViewSubscriptions')), 9, _hoisted_7), _ctx.showInstallAllPaidPlugins ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "btn installAllPaidPlugins valign",
    onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.onInstallAllPaidPlugins();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallAllPurchasedPlugins')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_InstallThesePlugins')) + " ", 1), _hoisted_10, _hoisted_11]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.paidPluginsToInstallAtOnce, function (pluginName) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: pluginName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(pluginName), 1);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "install",
    type: "button",
    "data-href": _ctx.installAllPaidPluginsLink,
    value: _ctx.translate('Marketplace_InstallAllPurchasedPluginsAction', _ctx.paidPluginsToInstallAtOnce.length)
  }, null, 8, _hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "cancel",
    type: "button",
    value: _ctx.translate('General_Cancel')
  }, null, 8, _hoisted_13)])], 512)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isUpdating
  }, null, 8, ["loading"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_14, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.noLicenseKeyIntroText)
  }, null, 8, _hoisted_16), _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DefaultLicenseKeyFields, {
    "model-value": _ctx.licenseKey,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      _ctx.licenseKey = $event;

      _ctx.updatedLicenseKey();
    }),
    onConfirm: _cache[5] || (_cache[5] = function ($event) {
      return _ctx.updateLicense();
    }),
    "has-license-key": _ctx.hasLicenseKey,
    "is-valid-consumer": _ctx.isValidConsumer,
    "enable-update": _ctx.enableUpdate
  }, null, 8, ["model-value", "has-license-key", "is-valid-consumer", "enable-update"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isUpdating
  }, null, 8, ["loading"])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.noLicenseKeyIntroNoSuperUserAccessText)
  }, null, 8, _hoisted_20)]))]))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_22), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_23)], 512)]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=39c51746

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=template&id=26188382

var DefaultLicenseKeyFieldsvue_type_template_id_26188382_hoisted_1 = {
  class: "valign licenseKeyText"
};
function DefaultLicenseKeyFieldsvue_type_template_id_26188382_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DefaultLicenseKeyFieldsvue_type_template_id_26188382_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "license_key",
    "full-width": true,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('update:modelValue', $event);
    }),
    placeholder: _ctx.licenseKeyPlaceholder
  }, null, 8, ["model-value", "placeholder"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.$emit('confirm');
    }),
    disabled: !_ctx.enableUpdate,
    value: _ctx.saveButtonText,
    id: "submit_license_key"
  }, null, 8, ["disabled", "value"])], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=template&id=26188382

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=script&lang=ts



/* harmony default export */ var DefaultLicenseKeyFieldsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: String,
    isValidConsumer: Boolean,
    hasLicenseKey: Boolean,
    enableUpdate: Boolean
  },
  emits: ['update:modelValue', 'confirm'],
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  computed: {
    licenseKeyPlaceholder: function licenseKeyPlaceholder() {
      return this.isValidConsumer ? Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyIsValidShort') : Object(external_CoreHome_["translate"])('Marketplace_LicenseKey');
    },
    saveButtonText: function saveButtonText() {
      return this.hasLicenseKey ? Object(external_CoreHome_["translate"])('CoreUpdater_UpdateTitle') : Object(external_CoreHome_["translate"])('Marketplace_ActivateLicenseKey');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/DefaultLicenseKeyFields.vue



DefaultLicenseKeyFieldsvue_type_script_lang_ts.render = DefaultLicenseKeyFieldsvue_type_template_id_26188382_render

/* harmony default export */ var DefaultLicenseKeyFields = (DefaultLicenseKeyFieldsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=script&lang=ts




/* harmony default export */ var LicenseKeyvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    hasLicenseKey: Boolean,
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    }
  },
  components: {
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    DefaultLicenseKeyFields: DefaultLicenseKeyFields
  },
  data: function data() {
    return {
      licenseKey: '',
      enableUpdate: false,
      isUpdating: false
    };
  },
  methods: {
    onInstallAllPaidPlugins: function onInstallAllPaidPlugins() {
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.installAllPaidPluginsAtOnce);
    },
    updateLicenseKey: function updateLicenseKey(action, licenseKey, onSuccessMessage) {
      var _this = this;

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "Marketplace.".concat(action),
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }, {
        withTokenInUrl: true
      }).then(function (response) {
        _this.isUpdating = false;

        if (response && response.value) {
          external_CoreHome_["NotificationsStore"].show({
            message: onSuccessMessage,
            context: 'success',
            type: 'transient'
          });
          external_CoreHome_["Matomo"].helper.redirect();
        }
      }, function () {
        _this.isUpdating = false;
      });
    },
    removeLicense: function removeLicense() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmRemoveLicense, {
        yes: function yes() {
          _this2.enableUpdate = false;
          _this2.isUpdating = true;

          _this2.updateLicenseKey('deleteLicenseKey', '', Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
    updatedLicenseKey: function updatedLicenseKey() {
      this.enableUpdate = !!this.licenseKey;
    },
    updateLicense: function updateLicense() {
      this.enableUpdate = false;
      this.isUpdating = true;
      this.updateLicenseKey('saveLicenseKey', this.licenseKey, Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyActivatedSuccess'));
    }
  },
  computed: {
    subscriptionOverviewLink: function subscriptionOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'subscriptionOverview'
      })));
    },
    noLicenseKeyIntroText: function noLicenseKeyIntroText() {
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntro', Object(external_CoreHome_["externalLink"])('https://matomo.org/recommends/premium-plugins/'), '</a>');
    },
    noLicenseKeyIntroNoSuperUserAccessText: function noLicenseKeyIntroNoSuperUserAccessText() {
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess', Object(external_CoreHome_["externalLink"])('https://matomo.org/recommends/premium-plugins/'), '</a>');
    },
    installAllPaidPluginsLink: function installAllPaidPluginsLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'installAllPaidPlugins',
        nonce: this.installNonce
      })));
    },
    showInstallAllPaidPlugins: function showInstallAllPaidPlugins() {
      return this.isAutoUpdatePossible && this.isPluginsAdminEnabled && this.paidPluginsToInstallAtOnce.length;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue



LicenseKeyvue_type_script_lang_ts.render = LicenseKeyvue_type_template_id_39c51746_render

/* harmony default export */ var LicenseKey = (LicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=50b87c40

var ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_1 = ["innerHTML"];
var ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_2 = {
  class: "manage-license-key-input"
};
var ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_3 = {
  class: "ui-confirm",
  id: "confirmRemoveLicense",
  ref: "confirmRemoveLicense"
};
var ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_4 = ["value"];
var ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_5 = ["value"];
function ManageLicenseKeyvue_type_template_id_50b87c40_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_LicenseKey'),
    class: "manage-license-key"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "manage-license-key-intro",
        innerHTML: _ctx.$sanitize(_ctx.manageLicenseKeyIntro)
      }, null, 8, ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "license_key",
        modelValue: _ctx.licenseKey,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
          return _ctx.licenseKey = $event;
        }),
        placeholder: _ctx.licenseKeyPlaceholder,
        "full-width": true
      }, null, 8, ["modelValue", "placeholder"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        onConfirm: _cache[1] || (_cache[1] = function ($event) {
          return _ctx.updateLicense();
        }),
        value: _ctx.saveButtonText,
        disabled: !_ctx.licenseKey || _ctx.isUpdating,
        id: "submit_license_key"
      }, null, 8, ["value", "disabled"]), _ctx.hasValidLicense ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
        key: 0,
        id: "remove_license_key",
        onConfirm: _cache[2] || (_cache[2] = function ($event) {
          return _ctx.removeLicense();
        }),
        disabled: _ctx.isUpdating,
        value: _ctx.translate('General_Remove')
      }, null, 8, ["disabled", "value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.isUpdating
      }, null, 8, ["loading"])];
    }),
    _: 1
  }, 8, ["content-title"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_ConfirmRemoveLicense')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, ManageLicenseKeyvue_type_template_id_50b87c40_hoisted_5)], 512)], 64);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=template&id=50b87c40

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts



/* harmony default export */ var ManageLicenseKeyvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hasValidLicenseKey: Boolean
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data: function data() {
    return {
      licenseKey: '',
      hasValidLicense: this.hasValidLicenseKey,
      isUpdating: false
    };
  },
  methods: {
    updateLicenseKey: function updateLicenseKey(action, licenseKey, onSuccessMessage) {
      var _this = this;

      external_CoreHome_["NotificationsStore"].remove('ManageLicenseKeySuccess');
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "Marketplace.".concat(action),
        format: 'JSON'
      }, {
        licenseKey: this.licenseKey
      }, {
        withTokenInUrl: true
      }).then(function (response) {
        _this.isUpdating = false;

        if (response && response.value) {
          external_CoreHome_["NotificationsStore"].show({
            id: 'ManageLicenseKeySuccess',
            message: onSuccessMessage,
            context: 'success',
            type: 'toast'
          });
          _this.hasValidLicense = action !== 'deleteLicenseKey';
          _this.licenseKey = '';
        }
      }, function () {
        _this.isUpdating = false;
      });
    },
    removeLicense: function removeLicense() {
      var _this2 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirmRemoveLicense, {
        yes: function yes() {
          _this2.isUpdating = true;

          _this2.updateLicenseKey('deleteLicenseKey', '', Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyDeletedSuccess'));
        }
      });
    },
    updateLicense: function updateLicense() {
      this.isUpdating = true;
      this.updateLicenseKey('saveLicenseKey', this.licenseKey, Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyActivatedSuccess'));
    }
  },
  computed: {
    manageLicenseKeyIntro: function manageLicenseKeyIntro() {
      var marketplaceLink = "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview'
      })));
      return Object(external_CoreHome_["translate"])('Marketplace_ManageLicenseKeyIntro', "<a href=\"".concat(marketplaceLink, "\">"), '</a>', Object(external_CoreHome_["externalLink"])('https://shop.matomo.org/my-account'), '</a>');
    },
    licenseKeyPlaceholder: function licenseKeyPlaceholder() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('Marketplace_LicenseKeyIsValidShort') : Object(external_CoreHome_["translate"])('Marketplace_LicenseKey');
    },
    saveButtonText: function saveButtonText() {
      return this.hasValidLicense ? Object(external_CoreHome_["translate"])('CoreUpdater_UpdateTitle') : Object(external_CoreHome_["translate"])('Marketplace_ActivateLicenseKey');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/ManageLicenseKey/ManageLicenseKey.vue



ManageLicenseKeyvue_type_script_lang_ts.render = ManageLicenseKeyvue_type_template_id_50b87c40_render

/* harmony default export */ var ManageLicenseKey = (ManageLicenseKeyvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=7f2da682

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_1 = {
  class: "getNewPlugins"
};
var GetNewPluginsvue_type_template_id_7f2da682_hoisted_2 = {
  class: "row"
};
var GetNewPluginsvue_type_template_id_7f2da682_hoisted_3 = {
  class: "pluginName"
};

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_5 = {
  key: 0
};

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsvue_type_template_id_7f2da682_hoisted_8 = [GetNewPluginsvue_type_template_id_7f2da682_hoisted_6, GetNewPluginsvue_type_template_id_7f2da682_hoisted_7];
var GetNewPluginsvue_type_template_id_7f2da682_hoisted_9 = {
  class: "widgetBody"
};
var GetNewPluginsvue_type_template_id_7f2da682_hoisted_10 = ["href"];
function GetNewPluginsvue_type_template_id_7f2da682_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsvue_type_template_id_7f2da682_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_7f2da682_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, function (plugin, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetNewPluginsvue_type_template_id_7f2da682_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 512), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetNewPluginsvue_type_template_id_7f2da682_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)], 512), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])]), index < _ctx.plugins.length - 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsvue_type_template_id_7f2da682_hoisted_5, GetNewPluginsvue_type_template_id_7f2da682_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsvue_type_template_id_7f2da682_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsvue_type_template_id_7f2da682_hoisted_10)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=template&id=7f2da682

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts



/* harmony default export */ var GetNewPluginsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  computed: {
    overviewLink: function overviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'Marketplace',
        action: 'overview'
      })));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPlugins/GetNewPlugins.vue



GetNewPluginsvue_type_script_lang_ts.render = GetNewPluginsvue_type_template_id_7f2da682_render

/* harmony default export */ var GetNewPlugins = (GetNewPluginsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=3ba8e55b

var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_1 = {
  class: "getNewPlugins isAdminPage",
  ref: "root"
};
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_2 = {
  class: "row"
};
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_3 = ["title"];
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_4 = ["title"];
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_5 = {
  key: 0
};

var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_7 = ["src"];
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_8 = {
  class: "widgetBody"
};
var GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_9 = ["href"];
function GetNewPluginsAdminvue_type_template_id_3ba8e55b_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugins, function (plugin) {
    var _plugin$screenshots;

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "col s12 m4",
      key: plugin.name
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      class: "pluginName",
      title: plugin.description
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 8, GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_3), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      class: "description",
      title: plugin.description
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 9, GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_4), (_plugin$screenshots = plugin.screenshots) !== null && _plugin$screenshots !== void 0 && _plugin$screenshots.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_5, [GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      class: "screenshot",
      src: "".concat(plugin.screenshots[0], "?w=600"),
      style: {
        "width": "100%"
      },
      alt: ""
    }, null, 8, GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_7), [[_directive_plugin_name, {
      pluginName: plugin.name
    }]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.marketplaceOverviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetNewPluginsAdminvue_type_template_id_3ba8e55b_hoisted_9)])], 512);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=template&id=3ba8e55b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts



var GetNewPluginsAdminvue_type_script_lang_ts_window = window,
    GetNewPluginsAdminvue_type_script_lang_ts_$ = GetNewPluginsAdminvue_type_script_lang_ts_window.$;

function applyDotdotdot(root) {
  GetNewPluginsAdminvue_type_script_lang_ts_$('.col .description', root).dotdotdot({
    watch: 'window'
  });
}

/* harmony default export */ var GetNewPluginsAdminvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  mounted: function mounted() {
    applyDotdotdot(this.$refs.root);
  },
  updated: function updated() {
    applyDotdotdot(this.$refs.root);
  },
  computed: {
    marketplaceOverviewLink: function marketplaceOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetNewPluginsAdmin/GetNewPluginsAdmin.vue



GetNewPluginsAdminvue_type_script_lang_ts.render = GetNewPluginsAdminvue_type_template_id_3ba8e55b_render

/* harmony default export */ var GetNewPluginsAdmin = (GetNewPluginsAdminvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=6ccd792d

var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_1 = {
  class: "getNewPlugins getPremiumFeatures widgetBody"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_2 = {
  key: 0,
  class: "col s12 m12"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_3 = ["innerHTML"];
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_4 = {
  style: {
    "margin-bottom": "28px",
    "color": "#5bb75b"
  }
};

var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-heart red-text"
}, null, -1);

var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_6 = {
  class: "pluginName"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_7 = {
  key: 0,
  class: "pluginSubtitle"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_8 = {
  class: "pluginBody"
};

var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_10 = {
  class: "pluginMoreDetails"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_11 = {
  class: "widgetBody"
};
var GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_12 = ["href"];
function GetPremiumFeaturesvue_type_template_id_6ccd792d_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_plugin_name = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("plugin-name");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pluginRows, function (rowOfPlugins, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "row",
      key: index
    }, [index === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
      style: {
        "font-weight": "bold",
        "color": "#5bb75b"
      },
      innerHTML: _ctx.$sanitize(_ctx.trialHintsText)
    }, null, 8, GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SupportMatomoThankYou')) + " ", 1), GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_5])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(rowOfPlugins, function (plugin) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: "col s12 m4",
        key: plugin.name
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.displayName), 1)], 512), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]]), plugin.specialOffer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SpecialOffer')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.specialOffer), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.isBundle ? "".concat(_ctx.translate('Marketplace_SpecialOffer'), ": ") : '') + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description) + " ", 1), GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_MoreDetails')), 1)], 512), [[_directive_plugin_name, {
        pluginName: plugin.name
      }]])])]);
    }), 128))]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.overviewLink
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ViewAllMarketplacePlugins')), 9, GetPremiumFeaturesvue_type_template_id_6ccd792d_hoisted_12)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=template&id=6ccd792d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts



/* harmony default export */ var GetPremiumFeaturesvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugins: {
      type: Array,
      required: true
    }
  },
  directives: {
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  computed: {
    trialHintsText: function trialHintsText() {
      var link = Object(external_CoreHome_["externalRawLink"])('https://shop.matomo.org/free-trial/');
      var linkStyle = 'color:#5bb75b;text-decoration: underline;';
      return Object(external_CoreHome_["translate"])('Marketplace_TrialHints', "<a style=\"".concat(linkStyle, "\" href=\"").concat(link, "\" target=\"_blank\" rel=\"noreferrer noopener\">"), '</a>');
    },
    pluginRows: function pluginRows() {
      // divide plugins array into rows of 3
      var result = [];
      this.plugins.forEach(function (plugin, index) {
        var row = Math.floor(index / 3);
        result[row] = result[row] || [];
        result[row].push(plugin);
      });
      return result;
    },
    overviewLink: function overviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview',
        show: 'premium'
      }));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/GetPremiumFeatures/GetPremiumFeatures.vue



GetPremiumFeaturesvue_type_script_lang_ts.render = GetPremiumFeaturesvue_type_template_id_6ccd792d_render

/* harmony default export */ var GetPremiumFeatures = (GetPremiumFeaturesvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=b0a2d858

function MissingReqsNoticevue_type_template_id_b0a2d858_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.plugin.missingRequirements || [], function (req, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: index,
      class: "alert alert-danger"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_MissingRequirementsNotice', _ctx.requirement(req.requirement), req.actualVersion, req.requiredVersion)), 1);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=template&id=b0a2d858

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts

/* harmony default export */ var MissingReqsNoticevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    plugin: {
      type: Object,
      required: true
    }
  },
  methods: {
    requirement: function requirement(req) {
      if (req === 'php') {
        return 'PHP';
      }

      return "".concat(req[0].toUpperCase()).concat(req.substr(1));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/MissingReqsNotice/MissingReqsNotice.vue



MissingReqsNoticevue_type_script_lang_ts.render = MissingReqsNoticevue_type_template_id_b0a2d858_render

/* harmony default export */ var MissingReqsNotice = (MissingReqsNoticevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=1c1fe542

var OverviewIntrovue_type_template_id_1c1fe542_hoisted_1 = {
  key: 0
};
var OverviewIntrovue_type_template_id_1c1fe542_hoisted_2 = {
  key: 1
};
var OverviewIntrovue_type_template_id_1c1fe542_hoisted_3 = ["innerHTML"];
var OverviewIntrovue_type_template_id_1c1fe542_hoisted_4 = {
  key: 2
};
var OverviewIntrovue_type_template_id_1c1fe542_hoisted_5 = ["innerHTML"];
var OverviewIntrovue_type_template_id_1c1fe542_hoisted_6 = ["innerHTML"];
function OverviewIntrovue_type_template_id_1c1fe542_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");

  var _component_LicenseKey = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("LicenseKey");

  var _component_UploadPluginDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("UploadPluginDialog");

  var _component_Marketplace = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Marketplace");

  var _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "feature-name": _ctx.translate('CorePluginsAdmin_Marketplace')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)];
    }),
    _: 1
  }, 8, ["feature-name"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [!_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_1c1fe542_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.showThemes ? _ctx.translate('Marketplace_NotAllowedToBrowseMarketplaceThemes') : _ctx.translate('Marketplace_NotAllowedToBrowseMarketplacePlugins')), 1)) : _ctx.showThemes ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_1c1fe542_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_ThemesDescription')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.installingNewThemeText)
  }, null, 8, OverviewIntrovue_type_template_id_1c1fe542_hoisted_3)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", OverviewIntrovue_type_template_id_1c1fe542_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_PluginsExtendPiwik')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.installingNewPluginText)
  }, null, 8, OverviewIntrovue_type_template_id_1c1fe542_hoisted_5)])), _ctx.isSuperUser && _ctx.inReportingMenu ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 3,
    ref: "noticeRemoveMarketplaceFromMenu",
    innerHTML: _ctx.$sanitize(_ctx.noticeRemoveMarketplaceFromMenuText)
  }, null, 8, OverviewIntrovue_type_template_id_1c1fe542_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_LicenseKey, {
    "is-valid-consumer": _ctx.isValidConsumer,
    "is-super-user": _ctx.isSuperUser,
    "is-auto-update-possible": _ctx.isAutoUpdatePossible,
    "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
    "has-license-key": _ctx.hasLicenseKey,
    "paid-plugins-to-install-at-once": _ctx.paidPluginsToInstallAtOnce,
    "install-nonce": _ctx.installNonce
  }, null, 8, ["is-valid-consumer", "is-super-user", "is-auto-update-possible", "is-plugins-admin-enabled", "has-license-key", "paid-plugins-to-install-at-once", "install-nonce"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_UploadPluginDialog, {
    "is-plugin-upload-enabled": _ctx.isPluginUploadEnabled,
    "upload-limit": _ctx.uploadLimit,
    "install-nonce": _ctx.installNonce
  }, null, 8, ["is-plugin-upload-enabled", "upload-limit", "install-nonce"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Marketplace, {
    "plugin-type": _ctx.pluginType,
    "plugin-type-options": _ctx.pluginTypeOptions,
    sort: _ctx.sort,
    "plugin-sort-options": _ctx.pluginSortOptions,
    "plugins-to-show": _ctx.pluginsToShow,
    query: _ctx.query,
    "num-available-plugins": _ctx.numAvailablePlugins
  }, null, 8, ["plugin-type", "plugin-type-options", "sort", "plugin-sort-options", "plugins-to-show", "query", "num-available-plugins"])], 512)), [[_directive_content_intro]]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=template&id=1c1fe542

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts





/* harmony default export */ var OverviewIntrovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    showThemes: Boolean,
    inReportingMenu: Boolean,
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    hasLicenseKey: Boolean,
    paidPluginsToInstallAtOnce: {
      type: Array,
      required: true
    },
    installNonce: {
      type: String,
      required: true
    },
    isPluginUploadEnabled: Boolean,
    uploadLimit: [String, Number],
    pluginType: {
      type: String,
      required: true
    },
    pluginTypeOptions: {
      type: [Object, Array],
      required: true
    },
    sort: {
      type: String,
      required: true
    },
    pluginSortOptions: {
      type: [Object, Array],
      required: true
    },
    pluginsToShow: {
      type: Array,
      required: true
    },
    query: {
      type: String,
      default: ''
    },
    numAvailablePlugins: {
      type: Number,
      required: true
    }
  },
  components: {
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    UploadPluginDialog: external_CorePluginsAdmin_["UploadPluginDialog"],
    LicenseKey: LicenseKey,
    Marketplace: Marketplace
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    PluginName: external_CorePluginsAdmin_["PluginName"]
  },
  mounted: function mounted() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      var pluginLink = this.$refs.noticeRemoveMarketplaceFromMenu.querySelector('[matomo-plugin-name]');
      external_CorePluginsAdmin_["PluginName"].mounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel'
        }
      });
    }
  },
  beforeUnmount: function beforeUnmount() {
    if (this.$refs.noticeRemoveMarketplaceFromMenu) {
      var pluginLink = this.$refs.noticeRemoveMarketplaceFromMenu.querySelector('[matomo-plugin-name]');
      external_CorePluginsAdmin_["PluginName"].unmounted(pluginLink, {
        dir: {},
        instance: null,
        modifiers: {},
        oldValue: null,
        value: {
          pluginName: 'WhiteLabel'
        }
      });
    }
  },
  computed: {
    installingNewThemeText: function installingNewThemeText() {
      return Object(external_CoreHome_["translate"])('Marketplace_InstallingNewThemesViaMarketplaceOrUpload', '<a href="#" class="uploadPlugin">', '</a>');
    },
    installingNewPluginText: function installingNewPluginText() {
      return Object(external_CoreHome_["translate"])('Marketplace_InstallingNewPluginsViaMarketplaceOrUpload', '<a href="#" class="uploadPlugin">', '</a>');
    },
    noticeRemoveMarketplaceFromMenuText: function noticeRemoveMarketplaceFromMenuText() {
      return Object(external_CoreHome_["translate"])('Marketplace_NoticeRemoveMarketplaceFromReportingMenu', '<a href="#" matomo-plugin-name="WhiteLabel">', '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/OverviewIntro/OverviewIntro.vue



OverviewIntrovue_type_script_lang_ts.render = OverviewIntrovue_type_template_id_1c1fe542_render

/* harmony default export */ var OverviewIntro = (OverviewIntrovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=09b01d8c

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_1 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_2 = ["href"];

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_5 = ["innerHTML"];

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_7 = {
  class: "subscriptionName"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_8 = ["href"];
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_9 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_10 = {
  class: "subscriptionType"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_11 = ["title"];
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_12 = {
  key: 0,
  class: "icon-error"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_13 = {
  key: 1,
  class: "icon-warning"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_14 = {
  key: 2,
  class: "icon-ok"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_15 = ["title"];

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-error"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_17 = {
  key: 0
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_18 = {
  colspan: "6"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_19 = {
  class: "tableActionBar"
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_20 = ["href"];

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-table"
}, null, -1);

var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_22 = {
  key: 1
};
var SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_23 = ["innerHTML"];
function SubscriptionOverviewvue_type_template_id_09b01d8c_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Marketplace_OverviewPluginSubscriptions'),
    class: "subscriptionOverview"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [_ctx.hasLicenseKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PluginSubscriptionsList')) + " ", 1), _ctx.loginUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: 0,
        target: "_blank",
        rel: "noreferrer noopener",
        href: _ctx.loginUrl
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsAllDetails')), 9, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_OverviewPluginSubscriptionsMissingInfo')) + " ", 1), SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoValidSubscriptionNoUpdates')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.translate('Marketplace_CurrentNumPiwikUsers', "<strong>".concat(_ctx.numUsers, "</strong>")))
      }, null, 8, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_5)]), SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionType')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CorePluginsAdmin_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionStartDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionEndDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_SubscriptionNextPaymentDate')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.subscriptions || [], function (subscription, index) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: index
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_7, [subscription.plugin.htmlUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
          key: 0,
          href: subscription.plugin.htmlUrl,
          rel: "noreferrer noopener",
          target: "_blank"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 9, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_8)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.plugin.displayName), 1))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.productType), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          class: "subscriptionStatus",
          title: _ctx.getSubscriptionStatusTitle(subscription)
        }, [!subscription.isValid ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_12)) : subscription.isExpiredSoon ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_13)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_14)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.status) + " ", 1), subscription.isExceeded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 3,
          class: "errorMessage",
          title: _ctx.translate('Marketplace_LicenseExceededPossibleCause')
        }, [SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Exceeded')), 1)], 8, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_11), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.start), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.isValid && subscription.nextPayment ? _ctx.translate('Marketplace_LicenseRenewsNextPaymentDate') : subscription.end), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(subscription.nextPayment), 1)]);
      }), 128)), !_ctx.subscriptions.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_NoSubscriptionsFound')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.marketplaceOverviewLink,
        class: ""
      }, [SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_BrowseMarketplace')), 1)], 8, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_20)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
        innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
      }, null, 8, SubscriptionOverviewvue_type_template_id_09b01d8c_hoisted_23)]))];
    }),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=template&id=09b01d8c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts


/* harmony default export */ var SubscriptionOverviewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    loginUrl: {
      type: String,
      required: true
    },
    numUsers: {
      type: Number,
      required: true
    },
    hasLicenseKey: Boolean,
    subscriptions: {
      type: Array,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  methods: {
    getSubscriptionStatusTitle: function getSubscriptionStatusTitle(sub) {
      if (!sub.isValid) {
        return Object(external_CoreHome_["translate"])('Marketplace_SubscriptionInvalid');
      }

      if (sub.isExpiredSoon) {
        return Object(external_CoreHome_["translate"])('Marketplace_SubscriptionExpiresSoon');
      }

      return undefined;
    }
  },
  computed: {
    marketplaceOverviewLink: function marketplaceOverviewLink() {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify({
        module: 'Marketplace',
        action: 'overview'
      }));
    },
    missingLicenseText: function missingLicenseText() {
      return Object(external_CoreHome_["translate"])('Marketplace_OverviewPluginSubscriptionsMissingLicense', "<a href=\"".concat(this.marketplaceOverviewLink, "\">"), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/SubscriptionOverview/SubscriptionOverview.vue



SubscriptionOverviewvue_type_script_lang_ts.render = SubscriptionOverviewvue_type_template_id_09b01d8c_render

/* harmony default export */ var SubscriptionOverview = (SubscriptionOverviewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=3cfb1147

var RichMenuButtonvue_type_template_id_3cfb1147_hoisted_1 = {
  class: "richMarketplaceMenuButton"
};

var RichMenuButtonvue_type_template_id_3cfb1147_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);

var RichMenuButtonvue_type_template_id_3cfb1147_hoisted_3 = {
  class: "intro"
};
var RichMenuButtonvue_type_template_id_3cfb1147_hoisted_4 = {
  class: "cta"
};

var RichMenuButtonvue_type_template_id_3cfb1147_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-marketplace"
}, " ", -1);

function RichMenuButtonvue_type_template_id_3cfb1147_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RichMenuButtonvue_type_template_id_3cfb1147_hoisted_1, [RichMenuButtonvue_type_template_id_3cfb1147_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_3cfb1147_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_RichMenuIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RichMenuButtonvue_type_template_id_3cfb1147_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn btn-outline",
    tabindex: "5",
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.$emit('action');
    }, ["prevent"])),
    onKeyup: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(function ($event) {
      return _ctx.$emit('action');
    }, ["enter"]))
  }, [RichMenuButtonvue_type_template_id_3cfb1147_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_Marketplace')), 1)], 32)])]);
}
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=template&id=3cfb1147

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=script&lang=ts

/* harmony default export */ var RichMenuButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/RichMenuButton/RichMenuButton.vue



RichMenuButtonvue_type_script_lang_ts.render = RichMenuButtonvue_type_template_id_3cfb1147_render

/* harmony default export */ var RichMenuButton = (RichMenuButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/index.ts
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
//# sourceMappingURL=Marketplace.umd.js.map