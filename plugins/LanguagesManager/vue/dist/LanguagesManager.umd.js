(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["LanguagesManager"] = factory(require("CoreHome"), require("vue"));
	else
		root["LanguagesManager"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	__webpack_require__.p = "plugins/LanguagesManager/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6 ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"languageSelection\"\n};\nvar _hoisted_2 = {\n  class: \"item\",\n  target: \"_blank\",\n  rel: \"noreferrer noopener\",\n  href: \"{{ externalRawLink('https://matomo.org/translations/') }}\"\n};\nvar _hoisted_3 = [\"value\", \"title\"];\nvar _hoisted_4 = {\n  action: \"index.php?module=LanguagesManager&action=saveLanguage\",\n  method: \"post\",\n  ref: \"form\"\n};\nvar _hoisted_5 = [\"value\"];\nvar _hoisted_6 = [\"value\"];\nvar _hoisted_7 = [\"value\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_MenuItemsDropdown = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"MenuItemsDropdown\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_MenuItemsDropdown, {\n    \"menu-title\": _ctx.currentLanguageName,\n    onAfterSelect: _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.onSelect($event);\n    })\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", _hoisted_2, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('LanguagesManager_AboutPiwikTranslations')), 1\n      /* TEXT */\n      ), (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.languages, function (language) {\n        return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"a\", {\n          key: language.code,\n          class: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"normalizeClass\"])(\"item \".concat(language.code === _ctx.currentLanguageCode ? 'active' : '')),\n          value: language.code,\n          title: \"\".concat(language.name, \" (\").concat(language.english_name, \")\")\n        }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(language.name), 11\n        /* TEXT, CLASS, PROPS */\n        , _hoisted_3);\n      }), 128\n      /* KEYED_FRAGMENT */\n      )), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"form\", _hoisted_4, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n        type: \"hidden\",\n        name: \"language\",\n        id: \"language\",\n        value: _ctx.selectedLanguage\n      }, null, 8\n      /* PROPS */\n      , _hoisted_5), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n        type: \"hidden\",\n        name: \"nonce\",\n        id: \"nonce\",\n        value: _ctx.formNonce\n      }, null, 8\n      /* PROPS */\n      , _hoisted_6), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\" During installation token_auth is not set \"), _ctx.tokenAuth ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"input\", {\n        key: 0,\n        type: \"hidden\",\n        name: \"token_auth\",\n        value: _ctx.tokenAuth\n      }, null, 8\n      /* PROPS */\n      , _hoisted_7)) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true)], 512\n      /* NEED_PATCH */\n      )];\n    }),\n    _: 1\n    /* STABLE */\n\n  }, 8\n  /* PROPS */\n  , [\"menu-title\"])]);\n}\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\n\nvar _hoisted_1 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [/*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\" This page helps you to find existing translations that you can reuse in your Plugin. If you want to know more about translations have a look at our \"), /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n  href: \"{{ externalRawLink('https://developer.matomo.org/guides/internationalization') }}\",\n  rel: \"noreferrer noopener\",\n  target: \"_blank\"\n}, \"Internationalization guide\"), /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(\". Enter a search term to find translations and their corresponding keys: \")], -1\n/* HOISTED */\n);\n\nvar _hoisted_2 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_3 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_4 = {\n  style: {\n    \"word-break\": \"break-all\"\n  }\n};\n\nvar _hoisted_5 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"th\", {\n  style: {\n    \"width\": \"250px\"\n  }\n}, \"Key\", -1\n/* HOISTED */\n);\n\nvar _hoisted_6 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"th\", null, \"English translation\", -1\n/* HOISTED */\n);\n\nvar _hoisted_7 = {\n  key: 0\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_Field = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Field\");\n\n  var _directive_content_table = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveDirective\"])(\"content-table\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", null, [_hoisted_1, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"text\",\n    name: \"alias\",\n    \"inline-help\": \"Search for English translation. Max 1000 results will be shown.\",\n    placeholder: \"Search for English translation\",\n    modelValue: _ctx.searchTerm,\n    \"onUpdate:modelValue\": _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.searchTerm = $event;\n    })\n  }, null, 8\n  /* PROPS */\n  , [\"modelValue\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Field, {\n    uicontrol: \"select\",\n    name: \"translationSearch.compareLanguage\",\n    \"inline-help\": \"Optionally select a language to compare the English language with.\",\n    \"model-value\": _ctx.compareLanguage,\n    \"onUpdate:modelValue\": _cache[1] || (_cache[1] = function ($event) {\n      _ctx.compareLanguage = $event;\n\n      _ctx.doCompareLanguage();\n    }),\n    options: _ctx.languages\n  }, null, 8\n  /* PROPS */\n  , [\"model-value\", \"options\"])]), _hoisted_2, _hoisted_3, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"table\", _hoisted_4, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"thead\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"tr\", null, [_hoisted_5, _hoisted_6, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"th\", null, \"Compare translation\", 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.compareLanguage && _ctx.compareTranslations]])])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"tbody\", null, [(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.filteredTranslations, function (translation) {\n    return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"tr\", {\n      key: translation.label\n    }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"td\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(translation.label), 1\n    /* TEXT */\n    ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"td\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(translation.value), 1\n    /* TEXT */\n    ), _ctx.compareLanguage && _ctx.compareTranslations ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"td\", _hoisted_7, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.compareTranslations[translation.label]), 1\n    /* TEXT */\n    )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true)]);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))])], 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.searchTerm], [_directive_content_table]])]);\n}\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644 ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_TranslationSearch = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"TranslationSearch\");\n\n  var _component_ContentBlock = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ContentBlock\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createBlock\"])(_component_ContentBlock, {\n    \"content-title\": _ctx.translate('LanguagesManager_TranslationSearch'),\n    feature: \"true\"\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_TranslationSearch)];\n    }),\n    _: 1\n    /* STABLE */\n\n  }, 8\n  /* PROPS */\n  , [\"content-title\"]);\n}\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    tokenAuth: String,\n    formNonce: {\n      type: String,\n      required: true\n    },\n    languages: {\n      type: Array,\n      required: true\n    },\n    currentLanguageCode: {\n      type: String,\n      required: true\n    },\n    currentLanguageName: {\n      type: String,\n      required: true\n    }\n  },\n  components: {\n    MenuItemsDropdown: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MenuItemsDropdown\"]\n  },\n  data: function data() {\n    return {\n      selectedLanguage: this.currentLanguageCode\n    };\n  },\n  methods: {\n    onSelect: function onSelect(selected) {\n      var _this = this;\n\n      this.selectedLanguage = selected.getAttribute('value');\n      Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"nextTick\"])().then(function () {\n        _this.$refs.form.submit();\n      });\n    }\n  }\n}));\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n\n // loading a component this way since during Installation we don't want to load CorePluginsAdmin\n// just for the language selector directive\n\nvar Field = Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"useExternalPluginComponent\"])('CorePluginsAdmin', 'Field');\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  components: {\n    Field: Field\n  },\n  directives: {\n    ContentTable: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ContentTable\"]\n  },\n  data: function data() {\n    return {\n      compareTranslations: null,\n      existingTranslations: [],\n      languages: [],\n      compareLanguage: '',\n      searchTerm: ''\n    };\n  },\n  created: function created() {\n    this.fetchTranslations('en');\n    this.fetchLanguages();\n  },\n  methods: {\n    fetchTranslations: function fetchTranslations(languageCode) {\n      var _this = this;\n\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        method: 'LanguagesManager.getTranslationsForLanguage',\n        filter_limit: -1,\n        languageCode: languageCode\n      }).then(function (response) {\n        if (!response) {\n          return;\n        }\n\n        if (languageCode === 'en') {\n          _this.existingTranslations = response;\n        } else {\n          _this.compareTranslations = {};\n          response.forEach(function (translation) {\n            _this.compareTranslations[translation.label] = translation.value;\n          });\n        }\n      });\n    },\n    fetchLanguages: function fetchLanguages() {\n      var _this2 = this;\n\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        method: 'LanguagesManager.getAvailableLanguagesInfo',\n        filter_limit: -1\n      }).then(function (languages) {\n        _this2.languages = [{\n          key: '',\n          value: 'None'\n        }];\n\n        if (languages) {\n          languages.forEach(function (language) {\n            if (language.code === 'en') {\n              return;\n            }\n\n            _this2.languages.push({\n              key: language.code,\n              value: language.name\n            });\n          });\n        }\n      });\n    },\n    doCompareLanguage: function doCompareLanguage() {\n      if (this.compareLanguage) {\n        this.compareTranslations = null;\n        this.fetchTranslations(this.compareLanguage);\n      }\n    }\n  },\n  computed: {\n    filteredTranslations: function filteredTranslations() {\n      var _this3 = this;\n\n      var filtered = this.existingTranslations.filter(function (t) {\n        return t.label.includes(_this3.searchTerm) || t.value.includes(_this3.searchTerm);\n      });\n      filtered = filtered.slice(0, 1000);\n      return filtered;\n    }\n  }\n}));\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _TranslationSearch_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TranslationSearch.vue */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue\");\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  components: {\n    ContentBlock: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ContentBlock\"],\n    TranslationSearch: _TranslationSearch_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n  }\n}));\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: TranslationSearch, TranslationSearchPage, LanguageSelector, LanguagesDropdown */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/LanguagesManager/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"TranslationSearch\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"TranslationSearch\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"TranslationSearchPage\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"TranslationSearchPage\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LanguageSelector\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"LanguageSelector\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LanguagesDropdown\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"LanguagesDropdown\"]; });\n\n\n\n\n\n//# sourceURL=webpack://LanguagesManager/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://LanguagesManager/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts":
/*!*******************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts ***!
  \*******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\nvar _window = window,\n    $ = _window.$;\n\nfunction postLanguageChange(element, event) {\n  var value = $(event.target).attr('value');\n\n  if (value) {\n    $(element).find('#language').val(value).parents('form').submit();\n  }\n}\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  mounted: function mounted(el, binding) {\n    binding.value.onClick = postLanguageChange.bind(null, el);\n    $(el).on('click', 'a[value]', binding.value.onClick);\n  },\n  unmounted: function unmounted(el, binding) {\n    $(el).off('click', 'a[value]', binding.value.onClick);\n  }\n});\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue":
/*!**********************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue ***!
  \**********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LanguagesDropdown_vue_vue_type_template_id_70bd4ad6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LanguagesDropdown.vue?vue&type=template&id=70bd4ad6 */ \"./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6\");\n/* harmony import */ var _LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LanguagesDropdown.vue?vue&type=script&lang=ts */ \"./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _LanguagesDropdown_vue_vue_type_template_id_70bd4ad6__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts":
/*!**********************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts ***!
  \**********************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./LanguagesDropdown.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LanguagesDropdown_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6":
/*!****************************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6 ***!
  \****************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LanguagesDropdown_vue_vue_type_template_id_70bd4ad6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./LanguagesDropdown.vue?vue&type=template&id=70bd4ad6 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=70bd4ad6\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LanguagesDropdown_vue_vue_type_template_id_70bd4ad6__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue":
/*!**********************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue ***!
  \**********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TranslationSearch_vue_vue_type_template_id_3b47292f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TranslationSearch.vue?vue&type=template&id=3b47292f */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f\");\n/* harmony import */ var _TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TranslationSearch.vue?vue&type=script&lang=ts */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _TranslationSearch_vue_vue_type_template_id_3b47292f__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts":
/*!**********************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts ***!
  \**********************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./TranslationSearch.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearch_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f":
/*!****************************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f ***!
  \****************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearch_vue_vue_type_template_id_3b47292f__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./TranslationSearch.vue?vue&type=template&id=3b47292f */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3b47292f\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearch_vue_vue_type_template_id_3b47292f__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue":
/*!**************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue ***!
  \**************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TranslationSearchPage_vue_vue_type_template_id_3d4e2644__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TranslationSearchPage.vue?vue&type=template&id=3d4e2644 */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644\");\n/* harmony import */ var _TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TranslationSearchPage.vue?vue&type=script&lang=ts */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _TranslationSearchPage_vue_vue_type_template_id_3d4e2644__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts":
/*!**************************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts ***!
  \**************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./TranslationSearchPage.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearchPage_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644":
/*!********************************************************************************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644 ***!
  \********************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearchPage_vue_vue_type_template_id_3d4e2644__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./TranslationSearchPage.vue?vue&type=template&id=3d4e2644 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=3d4e2644\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_TranslationSearchPage_vue_vue_type_template_id_3d4e2644__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?");

/***/ }),

/***/ "./plugins/LanguagesManager/vue/src/index.ts":
/*!***************************************************!*\
  !*** ./plugins/LanguagesManager/vue/src/index.ts ***!
  \***************************************************/
/*! exports provided: TranslationSearch, TranslationSearchPage, LanguageSelector, LanguagesDropdown */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TranslationSearch_TranslationSearch_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TranslationSearch/TranslationSearch.vue */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"TranslationSearch\", function() { return _TranslationSearch_TranslationSearch_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* harmony import */ var _TranslationSearch_TranslationSearchPage_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TranslationSearch/TranslationSearchPage.vue */ \"./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"TranslationSearchPage\", function() { return _TranslationSearch_TranslationSearchPage_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/* harmony import */ var _LanguageSelector_LanguageSelector_ts__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./LanguageSelector/LanguageSelector.ts */ \"./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LanguageSelector\", function() { return _LanguageSelector_LanguageSelector_ts__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/* harmony import */ var _LanguagesDropdown_LanguagesDropdown_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./LanguagesDropdown/LanguagesDropdown.vue */ \"./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LanguagesDropdown\", function() { return _LanguagesDropdown_LanguagesDropdown_vue__WEBPACK_IMPORTED_MODULE_3__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n\n//# sourceURL=webpack://LanguagesManager/./plugins/LanguagesManager/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://LanguagesManager/external_%22CoreHome%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://LanguagesManager/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});