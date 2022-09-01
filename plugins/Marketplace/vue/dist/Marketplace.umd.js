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
__webpack_require__.d(__webpack_exports__, "PluginName", function() { return /* reexport */ PluginName; });

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

// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginName/PluginName.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
window.broadcast.addPopoverHandler('browsePluginDetail', function (value) {
  var pluginName = value;
  var activeTab = null;

  if (value.indexOf('!') !== -1) {
    activeTab = value.slice(value.indexOf('!') + 1);
    pluginName = value.slice(0, value.indexOf('!'));
  }

  var url = "module=Marketplace&action=pluginDetails&pluginName=".concat(encodeURIComponent(pluginName));

  if (activeTab) {
    url += "&activeTab=".concat(encodeURIComponent(activeTab));
  }

  window.Piwik_Popover.createPopupAndLoadUrl(url, 'details');
});

function onClickPluginNameLink(binding, event) {
  var pluginName = binding.value.pluginName;
  var activePluginTab = binding.value.activePluginTab;
  event.preventDefault();

  if (activePluginTab) {
    pluginName += "!".concat(activePluginTab);
  }

  window.broadcast.propagateNewPopoverParameter('browsePluginDetail', pluginName);
}

var _window = window,
    $ = _window.$;
/* harmony default export */ var PluginName = ({
  mounted: function mounted(element, binding) {
    var pluginName = binding.value.pluginName;

    if (!pluginName) {
      return;
    }

    binding.value.onClickHandler = onClickPluginNameLink.bind(null, binding);
    $(element).on('click', binding.value.onClickHandler) // attribute added for AnonymousPiwikUsageMeasurement
    .attr('matomo-plugin-name', pluginName);
  },
  unmounted: function unmounted(element, binding) {
    $(element).off('click', binding.value.onClickHandler);
  }
});
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/PluginName/PluginName.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function piwikPluginName() {
  return {
    restrict: 'A',
    link: function link(scope, element, attrs) {
      var binding = {
        instance: null,
        value: {
          pluginName: attrs.piwikPluginName,
          activePluginTab: attrs.activeplugintab
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      PluginName.mounted(element[0], binding);
      element.on('$destroy', function () {
        PluginName.unmounted(element[0], binding);
      });
    }
  };
}

window.angular.module('piwikApp').directive('piwikPluginName', piwikPluginName);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=1547a42f

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
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=template&id=1547a42f

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/Marketplace/Marketplace.vue?vue&type=script&lang=ts




var lcfirst = function lcfirst(s) {
  return "".concat(s[0].toLowerCase()).concat(s.substring(1));
};

var Marketplacevue_type_script_lang_ts_window = window,
    Marketplacevue_type_script_lang_ts_$ = Marketplacevue_type_script_lang_ts_window.$;
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
    function syncMaxHeight2(selector) {
      if (!selector) {
        return;
      }

      var $nodes = Marketplacevue_type_script_lang_ts_$(selector);

      if (!$nodes || !$nodes.length) {
        return;
      }

      var maxh3 = undefined;
      var maxMeta = undefined;
      var maxFooter = undefined;
      var nodesToUpdate = [];
      var lastTop = 0;
      $nodes.each(function (index, node) {
        var $node = Marketplacevue_type_script_lang_ts_$(node);

        var _$node$offset = $node.offset(),
            top = _$node$offset.top;

        if (lastTop !== top) {
          nodesToUpdate = [];
          lastTop = top;
          maxh3 = undefined;
          maxMeta = undefined;
          maxFooter = undefined;
        }

        nodesToUpdate.push($node);
        var heightH3 = $node.find('h3').height();
        var heightMeta = $node.find('.metadata').height();
        var heightFooter = $node.find('.footer').height();

        if (!maxh3) {
          maxh3 = heightH3;
        } else if (maxh3 < heightH3) {
          maxh3 = heightH3;
        }

        if (!maxMeta) {
          maxMeta = heightMeta;
        } else if (maxMeta < heightMeta) {
          maxMeta = heightMeta;
        }

        if (!maxFooter) {
          maxFooter = heightFooter;
        } else if (maxFooter < heightFooter) {
          maxFooter = heightFooter;
        }

        Marketplacevue_type_script_lang_ts_$.each(nodesToUpdate, function (i, $nodeToUpdate) {
          if (maxh3) {
            $nodeToUpdate.find('h3').height("".concat(maxh3, "px"));
          }

          if (maxMeta) {
            $nodeToUpdate.find('.metadata').height("".concat(maxMeta, "px"));
          }

          if (maxFooter) {
            $nodeToUpdate.find('.footer').height("".concat(maxFooter, "px"));
          }
        });
      });
    }

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      // Keeps the plugin descriptions the same height
      var descriptions = Marketplacevue_type_script_lang_ts_$('.marketplace .plugin .description');
      descriptions.dotdotdot({
        after: 'a.more',
        watch: 'window'
      });
      external_CoreHome_["Matomo"].helper.compileVueDirectives(descriptions); // have to recompile any vue directives

      syncMaxHeight2('.marketplace .plugin');
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=6a23f4d2

var LicenseKeyvue_type_template_id_6a23f4d2_hoisted_1 = {
  class: "marketplace-max-width"
};
var LicenseKeyvue_type_template_id_6a23f4d2_hoisted_2 = {
  class: "marketplace-paid-intro"
};
var LicenseKeyvue_type_template_id_6a23f4d2_hoisted_3 = {
  key: 0
};
var LicenseKeyvue_type_template_id_6a23f4d2_hoisted_4 = {
  key: 0
};

var LicenseKeyvue_type_template_id_6a23f4d2_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

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
function LicenseKeyvue_type_template_id_6a23f4d2_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_DefaultLicenseKeyFields = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DefaultLicenseKeyFields");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_6a23f4d2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", LicenseKeyvue_type_template_id_6a23f4d2_hoisted_2, [_ctx.isValidConsumer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_6a23f4d2_hoisted_3, [_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LicenseKeyvue_type_template_id_6a23f4d2_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Marketplace_PaidPluginsWithLicenseKeyIntro', '')) + " ", 1), LicenseKeyvue_type_template_id_6a23f4d2_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DefaultLicenseKeyFields, {
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
// CONCATENATED MODULE: ./plugins/Marketplace/vue/src/LicenseKey/LicenseKey.vue?vue&type=template&id=6a23f4d2

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
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntro', '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/recommends/premium-plugins/">', '</a>');
    },
    noLicenseKeyIntroNoSuperUserAccessText: function noLicenseKeyIntroNoSuperUserAccessText() {
      return Object(external_CoreHome_["translate"])('Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess', '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/recommends/premium-plugins/">', '</a>');
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



LicenseKeyvue_type_script_lang_ts.render = LicenseKeyvue_type_template_id_6a23f4d2_render

/* harmony default export */ var LicenseKey = (LicenseKeyvue_type_script_lang_ts);
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