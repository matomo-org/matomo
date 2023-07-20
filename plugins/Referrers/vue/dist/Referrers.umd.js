(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Referrers"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Referrers"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/Referrers/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "CampaignBuilder", function() { return /* reexport */ CampaignBuilder; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=a9b70d82

var _hoisted_1 = {
  class: "campaignUrlBuilder"
};
var _hoisted_2 = {
  id: "urlCampaignBuilderResult"
};
var _hoisted_3 = ["textContent"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "websiteurl",
    title: "".concat(_ctx.translate('Actions_ColumnPageURL'), " (").concat(_ctx.translate('General_Required2'), ")"),
    modelValue: _ctx.websiteUrl,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.websiteUrl = $event;
    }),
    "inline-help": _ctx.translate('Referrers_CampaignPageUrlHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignname",
    title: "".concat(_ctx.translate('CoreAdminHome_JSTracking_CampaignNameParam'), " (").concat(_ctx.translate('General_Required2'), ")"),
    modelValue: _ctx.campaignName,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.campaignName = $event;
    }),
    "inline-help": _ctx.translate('Referrers_CampaignNameHelp')
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignkeyword",
    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignKwdParam'),
    modelValue: _ctx.campaignKeyword,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.campaignKeyword = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignKeywordHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignsource",
    title: _ctx.translate('Referrers_CampaignSource'),
    modelValue: _ctx.campaignSource,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
      return _ctx.campaignSource = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignSourceHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignmedium",
    title: _ctx.translate('Referrers_CampaignMedium'),
    modelValue: _ctx.campaignMedium,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
      return _ctx.campaignMedium = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignMediumHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaigncontent",
    title: _ctx.translate('Referrers_CampaignContent'),
    modelValue: _ctx.campaignContent,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.campaignContent = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignContentHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignid",
    title: _ctx.translate('Referrers_CampaignId'),
    modelValue: _ctx.campaignId,
    "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
      return _ctx.campaignId = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignIdHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaigngroup",
    title: _ctx.translate('Referrers_CampaignGroup'),
    modelValue: _ctx.campaignGroup,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
      return _ctx.campaignGroup = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignGroupHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "campaignplacement",
    title: _ctx.translate('Referrers_CampaignPlacement'),
    modelValue: _ctx.campaignPlacement,
    "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
      return _ctx.campaignPlacement = $event;
    }),
    "inline-help": "".concat(_ctx.translate('Goals_Optional'), " ").concat(_ctx.translate('Referrers_CampaignPlacementHelp'))
  }, null, 8, ["title", "modelValue", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.hasExtraPlugin]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "generateCampaignUrl",
    onConfirm: _cache[9] || (_cache[9] = function ($event) {
      return _ctx.generateUrl();
    }),
    disabled: !_ctx.websiteUrl || !_ctx.campaignName,
    value: _ctx.translate('Referrers_GenerateUrl'),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, ["disabled", "value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "resetCampaignUrl",
    onConfirm: _cache[10] || (_cache[10] = function ($event) {
      return _ctx.reset();
    }),
    value: _ctx.translate('General_Clear')
  }, null, 8, ["value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Referrers_URLCampaignBuilderResult')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("pre", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", {
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.generatedUrl)
  }, null, 8, _hoisted_3)], 512), [[_directive_copy_to_clipboard, {}]])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.generatedUrl]])])]);
}
// CONCATENATED MODULE: ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=a9b70d82

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts



var _window = window,
    $ = _window.$;
/* harmony default export */ var CampaignBuildervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    hasExtraPlugin: {
      type: Boolean,
      default: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  data: function data() {
    return {
      websiteUrl: '',
      campaignName: '',
      campaignKeyword: '',
      campaignSource: '',
      campaignMedium: '',
      campaignId: '',
      campaignContent: '',
      campaignGroup: '',
      campaignPlacement: '',
      generatedUrl: ''
    };
  },
  created: function created() {
    this.reset();
  },
  watch: {
    generatedUrl: function generatedUrl() {
      $('#urlCampaignBuilderResult').effect('highlight', {}, 1500);
    }
  },
  methods: {
    reset: function reset() {
      this.websiteUrl = '';
      this.campaignName = '';
      this.campaignKeyword = '';
      this.campaignSource = '';
      this.campaignMedium = '';
      this.campaignId = '';
      this.campaignContent = '';
      this.campaignGroup = '';
      this.campaignPlacement = '';
      this.generatedUrl = '';
    },
    generateUrl: function generateUrl() {
      var generatedUrl = String(this.websiteUrl);

      if (generatedUrl.indexOf('http') !== 0) {
        generatedUrl = "https://".concat(generatedUrl.trim());
      }

      var urlHashPos = generatedUrl.indexOf('#');
      var urlHash = '';

      if (urlHashPos >= 0) {
        urlHash = generatedUrl.slice(urlHashPos);
        generatedUrl = generatedUrl.slice(0, urlHashPos);
      }

      if (generatedUrl.indexOf('/', 10) < 0 && generatedUrl.indexOf('?') < 0) {
        generatedUrl += '/';
      }

      var campaignName = encodeURIComponent(this.campaignName.trim());

      if (generatedUrl.indexOf('?') > 0 || generatedUrl.indexOf('#') > 0) {
        generatedUrl += '&';
      } else {
        generatedUrl += '?';
      }

      generatedUrl += "mtm_campaign=".concat(campaignName);

      if (this.campaignKeyword) {
        generatedUrl += "&mtm_kwd=".concat(encodeURIComponent(this.campaignKeyword.trim()));
      }

      if (this.campaignSource) {
        generatedUrl += "&mtm_source=".concat(encodeURIComponent(this.campaignSource.trim()));
      }

      if (this.campaignMedium) {
        generatedUrl += "&mtm_medium=".concat(encodeURIComponent(this.campaignMedium.trim()));
      }

      if (this.campaignContent) {
        generatedUrl += "&mtm_content=".concat(encodeURIComponent(this.campaignContent.trim()));
      }

      if (this.campaignId) {
        generatedUrl += "&mtm_cid=".concat(encodeURIComponent(this.campaignId.trim()));
      }

      if (this.campaignGroup) {
        generatedUrl += "&mtm_group=".concat(encodeURIComponent(this.campaignGroup.trim()));
      }

      if (this.campaignPlacement) {
        generatedUrl += "&mtm_placement=".concat(encodeURIComponent(this.campaignPlacement.trim()));
      }

      generatedUrl += urlHash;
      this.generatedUrl = generatedUrl;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue



CampaignBuildervue_type_script_lang_ts.render = render

/* harmony default export */ var CampaignBuilder = (CampaignBuildervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var CampaignBuilder_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: CampaignBuilder,
  scope: {
    hasExtraPlugin: {
      angularJsBind: '<'
    }
  },
  directiveName: 'matomoCampaignBuilder'
}));
// CONCATENATED MODULE: ./plugins/Referrers/vue/src/index.ts
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
//# sourceMappingURL=Referrers.umd.js.map