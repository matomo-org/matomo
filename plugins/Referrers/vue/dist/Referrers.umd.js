(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", "CorePluginsAdmin", ], factory);
	else if(typeof exports === 'object')
		exports["Referrers"] = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else
		root["Referrers"] = factory(root["CoreHome"], root["CorePluginsAdmin"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"campaignUrlBuilder\"\n};\nvar _hoisted_2 = {\n  id: \"urlCampaignBuilderResult\"\n};\nvar _hoisted_3 = [\"textContent\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_Field = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Field\");\n\n  var _component_SaveButton = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"SaveButton\");\n\n  var _directive_copy_to_clipboard = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"copy-to-clipboard\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"form\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"websiteurl\",\n    title: \"\".concat(_ctx.translate('Actions_ColumnPageURL'), \" (\").concat(_ctx.translate('General_Required2'), \")\"),\n    modelValue: _ctx.websiteUrl,\n    \"onUpdate:modelValue\": _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.websiteUrl = $event;\n    }),\n    \"inline-help\": _ctx.translate('Referrers_CampaignPageUrlHelp')\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignname\",\n    title: \"\".concat(_ctx.translate('CoreAdminHome_JSTracking_CampaignNameParam'), \" (\").concat(_ctx.translate('General_Required2'), \")\"),\n    modelValue: _ctx.campaignName,\n    \"onUpdate:modelValue\": _cache[1] || (_cache[1] = function ($event) {\n      return _ctx.campaignName = $event;\n    }),\n    \"inline-help\": _ctx.translate('Referrers_CampaignNameHelp')\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignkeyword\",\n    title: _ctx.translate('CoreAdminHome_JSTracking_CampaignKwdParam'),\n    modelValue: _ctx.campaignKeyword,\n    \"onUpdate:modelValue\": _cache[2] || (_cache[2] = function ($event) {\n      return _ctx.campaignKeyword = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignKeywordHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignsource\",\n    title: _ctx.translate('Referrers_CampaignSource'),\n    modelValue: _ctx.campaignSource,\n    \"onUpdate:modelValue\": _cache[3] || (_cache[3] = function ($event) {\n      return _ctx.campaignSource = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignSourceHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignmedium\",\n    title: _ctx.translate('Referrers_CampaignMedium'),\n    modelValue: _ctx.campaignMedium,\n    \"onUpdate:modelValue\": _cache[4] || (_cache[4] = function ($event) {\n      return _ctx.campaignMedium = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignMediumHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaigncontent\",\n    title: _ctx.translate('Referrers_CampaignContent'),\n    modelValue: _ctx.campaignContent,\n    \"onUpdate:modelValue\": _cache[5] || (_cache[5] = function ($event) {\n      return _ctx.campaignContent = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignContentHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignid\",\n    title: _ctx.translate('Referrers_CampaignId'),\n    modelValue: _ctx.campaignId,\n    \"onUpdate:modelValue\": _cache[6] || (_cache[6] = function ($event) {\n      return _ctx.campaignId = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignIdHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaigngroup\",\n    title: _ctx.translate('Referrers_CampaignGroup'),\n    modelValue: _ctx.campaignGroup,\n    \"onUpdate:modelValue\": _cache[7] || (_cache[7] = function ($event) {\n      return _ctx.campaignGroup = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignGroupHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"campaignplacement\",\n    title: _ctx.translate('Referrers_CampaignPlacement'),\n    modelValue: _ctx.campaignPlacement,\n    \"onUpdate:modelValue\": _cache[8] || (_cache[8] = function ($event) {\n      return _ctx.campaignPlacement = $event;\n    }),\n    \"inline-help\": \"\".concat(_ctx.translate('Goals_Optional'), \" \").concat(_ctx.translate('Referrers_CampaignPlacementHelp'))\n  }, null, 8\n  /* PROPS */\n  , [\"title\", \"modelValue\", \"inline-help\"]), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.hasExtraPlugin]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_SaveButton, {\n    class: \"generateCampaignUrl\",\n    onConfirm: _cache[9] || (_cache[9] = function ($event) {\n      return _ctx.generateUrl();\n    }),\n    disabled: !_ctx.websiteUrl || !_ctx.campaignName,\n    value: _ctx.translate('Referrers_GenerateUrl'),\n    style: {\n      \"margin-right\": \"3.5px\"\n    }\n  }, null, 8\n  /* PROPS */\n  , [\"disabled\", \"value\"]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_SaveButton, {\n    class: \"resetCampaignUrl\",\n    onConfirm: _cache[10] || (_cache[10] = function ($event) {\n      return _ctx.reset();\n    }),\n    value: _ctx.translate('General_Clear')\n  }, null, 8\n  /* PROPS */\n  , [\"value\"]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h3\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Referrers_URLCampaignBuilderResult')), 1\n  /* TEXT */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"pre\", _hoisted_2, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"code\", {\n    textContent: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.generatedUrl)\n  }, null, 8\n  /* PROPS */\n  , _hoisted_3)], 512\n  /* NEED_PATCH */\n  ), [[_directive_copy_to_clipboard, {}]])])], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.generatedUrl]])])]);\n}\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! CorePluginsAdmin */ \"CorePluginsAdmin\");\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\nvar _window = window,\n    $ = _window.$;\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    hasExtraPlugin: {\n      type: Boolean,\n      default: true\n    }\n  },\n  components: {\n    Field: CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__[\"Field\"],\n    SaveButton: CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__[\"SaveButton\"]\n  },\n  directives: {\n    CopyToClipboard: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"CopyToClipboard\"]\n  },\n  data: function data() {\n    return {\n      websiteUrl: '',\n      campaignName: '',\n      campaignKeyword: '',\n      campaignSource: '',\n      campaignMedium: '',\n      campaignId: '',\n      campaignContent: '',\n      campaignGroup: '',\n      campaignPlacement: '',\n      generatedUrl: ''\n    };\n  },\n  created: function created() {\n    this.reset();\n  },\n  watch: {\n    generatedUrl: function generatedUrl() {\n      $('#urlCampaignBuilderResult').effect('highlight', {}, 1500);\n    }\n  },\n  methods: {\n    reset: function reset() {\n      this.websiteUrl = '';\n      this.campaignName = '';\n      this.campaignKeyword = '';\n      this.campaignSource = '';\n      this.campaignMedium = '';\n      this.campaignId = '';\n      this.campaignContent = '';\n      this.campaignGroup = '';\n      this.campaignPlacement = '';\n      this.generatedUrl = '';\n    },\n    generateUrl: function generateUrl() {\n      var generatedUrl = String(this.websiteUrl);\n\n      if (generatedUrl.indexOf('http') !== 0) {\n        generatedUrl = \"https://\".concat(generatedUrl.trim());\n      }\n\n      var urlHashPos = generatedUrl.indexOf('#');\n      var urlHash = '';\n\n      if (urlHashPos >= 0) {\n        urlHash = generatedUrl.slice(urlHashPos);\n        generatedUrl = generatedUrl.slice(0, urlHashPos);\n      }\n\n      if (generatedUrl.indexOf('/', 10) < 0 && generatedUrl.indexOf('?') < 0) {\n        generatedUrl += '/';\n      }\n\n      var campaignName = encodeURIComponent(this.campaignName.trim());\n\n      if (generatedUrl.indexOf('?') > 0 || generatedUrl.indexOf('#') > 0) {\n        generatedUrl += '&';\n      } else {\n        generatedUrl += '?';\n      }\n\n      generatedUrl += \"mtm_campaign=\".concat(campaignName);\n\n      if (this.campaignKeyword) {\n        generatedUrl += \"&mtm_kwd=\".concat(encodeURIComponent(this.campaignKeyword.trim()));\n      }\n\n      if (this.campaignSource) {\n        generatedUrl += \"&mtm_source=\".concat(encodeURIComponent(this.campaignSource.trim()));\n      }\n\n      if (this.campaignMedium) {\n        generatedUrl += \"&mtm_medium=\".concat(encodeURIComponent(this.campaignMedium.trim()));\n      }\n\n      if (this.campaignContent) {\n        generatedUrl += \"&mtm_content=\".concat(encodeURIComponent(this.campaignContent.trim()));\n      }\n\n      if (this.campaignId) {\n        generatedUrl += \"&mtm_cid=\".concat(encodeURIComponent(this.campaignId.trim()));\n      }\n\n      if (this.campaignGroup) {\n        generatedUrl += \"&mtm_group=\".concat(encodeURIComponent(this.campaignGroup.trim()));\n      }\n\n      if (this.campaignPlacement) {\n        generatedUrl += \"&mtm_placement=\".concat(encodeURIComponent(this.campaignPlacement.trim()));\n      }\n\n      generatedUrl += urlHash;\n      this.generatedUrl = generatedUrl;\n    }\n  }\n}));\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: CampaignBuilder */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Referrers/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"CampaignBuilder\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"CampaignBuilder\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Referrers/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://Referrers/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.adapter.ts":
/*!******************************************************************************!*\
  !*** ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.adapter.ts ***!
  \******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _CampaignBuilder_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CampaignBuilder.vue */ \"./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(CoreHome__WEBPACK_IMPORTED_MODULE_0__[\"createAngularJsAdapter\"])({\n  component: _CampaignBuilder_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  scope: {\n    hasExtraPlugin: {\n      angularJsBind: '<'\n    }\n  },\n  directiveName: 'matomoCampaignBuilder'\n}));\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.adapter.ts?");

/***/ }),

/***/ "./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue":
/*!***********************************************************************!*\
  !*** ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CampaignBuilder_vue_vue_type_template_id_3d06388b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CampaignBuilder.vue?vue&type=template&id=3d06388b */ \"./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b\");\n/* harmony import */ var _CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CampaignBuilder.vue?vue&type=script&lang=ts */ \"./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _CampaignBuilder_vue_vue_type_template_id_3d06388b__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?");

/***/ }),

/***/ "./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts":
/*!***********************************************************************************************!*\
  !*** ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts ***!
  \***********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./CampaignBuilder.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_CampaignBuilder_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?");

/***/ }),

/***/ "./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b":
/*!*****************************************************************************************************!*\
  !*** ./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b ***!
  \*****************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_CampaignBuilder_vue_vue_type_template_id_3d06388b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./CampaignBuilder.vue?vue&type=template&id=3d06388b */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?vue&type=template&id=3d06388b\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_CampaignBuilder_vue_vue_type_template_id_3d06388b__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue?");

/***/ }),

/***/ "./plugins/Referrers/vue/src/index.ts":
/*!********************************************!*\
  !*** ./plugins/Referrers/vue/src/index.ts ***!
  \********************************************/
/*! exports provided: CampaignBuilder */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CampaignBuilder_CampaignBuilder_adapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CampaignBuilder/CampaignBuilder.adapter */ \"./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.adapter.ts\");\n/* harmony import */ var _CampaignBuilder_CampaignBuilder_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CampaignBuilder/CampaignBuilder.vue */ \"./plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"CampaignBuilder\", function() { return _CampaignBuilder_CampaignBuilder_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n//# sourceURL=webpack://Referrers/./plugins/Referrers/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://Referrers/external_%22CoreHome%22?");

/***/ }),

/***/ "CorePluginsAdmin":
/*!***********************************!*\
  !*** external "CorePluginsAdmin" ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__;\n\n//# sourceURL=webpack://Referrers/external_%22CorePluginsAdmin%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://Referrers/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});