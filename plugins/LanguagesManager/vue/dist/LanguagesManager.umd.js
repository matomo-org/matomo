(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["LanguagesManager"] = factory(require("CoreHome"), require("vue"));
	else
		root["LanguagesManager"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/LanguagesManager/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "TranslationSearch", function() { return /* reexport */ TranslationSearch; });
__webpack_require__.d(__webpack_exports__, "TranslationSearchPage", function() { return /* reexport */ TranslationSearchPage; });
__webpack_require__.d(__webpack_exports__, "LanguageSelector", function() { return /* reexport */ LanguageSelector; });
__webpack_require__.d(__webpack_exports__, "LanguagesDropdown", function() { return /* reexport */ LanguagesDropdown; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=35ed731d

const _hoisted_1 = ["href"];
const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_4 = {
  style: {
    "word-break": "break-all"
  }
};
const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
  style: {
    "width": "250px"
  }
}, "Key", -1);
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, "English translation", -1);
const _hoisted_7 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" This page helps you to find existing translations that you can reuse in your Plugin. If you want to know more about translations have a look at our "), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: _ctx.externalRawLink('https://developer.matomo.org/guides/internationalization'),
    rel: "noreferrer noopener",
    target: "_blank"
  }, "Internationalization guide", 8, _hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". Enter a search term to find translations and their corresponding keys: ")]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "alias",
    "inline-help": "Search for English translation. Max 1000 results will be shown.",
    placeholder: "Search for English translation",
    modelValue: _ctx.searchTerm,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.searchTerm = $event)
  }, null, 8, ["modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "translationSearch.compareLanguage",
    "inline-help": "Optionally select a language to compare the English language with.",
    "model-value": _ctx.compareLanguage,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => {
      _ctx.compareLanguage = $event;
      _ctx.doCompareLanguage();
    }),
    options: _ctx.languages
  }, null, 8, ["model-value", "options"])]), _hoisted_2, _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [_hoisted_5, _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, "Compare translation", 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.compareLanguage && _ctx.compareTranslations]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredTranslations, translation => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: translation.label
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(translation.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(translation.value), 1), _ctx.compareLanguage && _ctx.compareTranslations ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.compareTranslations[translation.label]), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))])])), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm], [_directive_content_table]])]);
}
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=35ed731d

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts


// loading a component this way since during Installation we don't want to load CorePluginsAdmin
// just for the language selector directive
const Field = Object(external_CoreHome_["useExternalPluginComponent"])('CorePluginsAdmin', 'Field');
/* harmony default export */ var TranslationSearchvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      compareTranslations: null,
      existingTranslations: [],
      languages: [],
      compareLanguage: '',
      searchTerm: ''
    };
  },
  created() {
    this.fetchTranslations('en');
    this.fetchLanguages();
  },
  methods: {
    fetchTranslations(languageCode) {
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'LanguagesManager.getTranslationsForLanguage',
        filter_limit: -1,
        languageCode
      }).then(response => {
        if (!response) {
          return;
        }
        if (languageCode === 'en') {
          this.existingTranslations = response;
        } else {
          this.compareTranslations = {};
          response.forEach(translation => {
            this.compareTranslations[translation.label] = translation.value;
          });
        }
      });
    },
    fetchLanguages() {
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'LanguagesManager.getAvailableLanguagesInfo',
        filter_limit: -1
      }).then(languages => {
        this.languages = [{
          key: '',
          value: 'None'
        }];
        if (languages) {
          languages.forEach(language => {
            if (language.code === 'en') {
              return;
            }
            this.languages.push({
              key: language.code,
              value: language.name
            });
          });
        }
      });
    },
    doCompareLanguage() {
      if (this.compareLanguage) {
        this.compareTranslations = null;
        this.fetchTranslations(this.compareLanguage);
      }
    }
  },
  computed: {
    filteredTranslations() {
      let filtered = this.existingTranslations.filter(t => t.label.includes(this.searchTerm) || t.value.includes(this.searchTerm));
      filtered = filtered.slice(0, 1000);
      return filtered;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue



TranslationSearchvue_type_script_lang_ts.render = render

/* harmony default export */ var TranslationSearch = (TranslationSearchvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=75ade4ac

function TranslationSearchPagevue_type_template_id_75ade4ac_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_TranslationSearch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TranslationSearch");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('LanguagesManager_TranslationSearch'),
    feature: "true"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TranslationSearch)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=template&id=75ade4ac

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts



/* harmony default export */ var TranslationSearchPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    TranslationSearch: TranslationSearch
  }
}));
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearchPage.vue



TranslationSearchPagevue_type_script_lang_ts.render = TranslationSearchPagevue_type_template_id_75ade4ac_render

/* harmony default export */ var TranslationSearchPage = (TranslationSearchPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const {
  $
} = window;
function postLanguageChange(element, event) {
  const value = $(event.target).attr('value');
  if (value) {
    $(element).find('#language').val(value).parents('form').submit();
  }
}
/* harmony default export */ var LanguageSelector = ({
  mounted(el, binding) {
    binding.value.onClick = postLanguageChange.bind(null, el);
    $(el).on('click', 'a[value]', binding.value.onClick);
  },
  unmounted(el, binding) {
    $(el).off('click', 'a[value]', binding.value.onClick);
  }
});
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=040297e6

const LanguagesDropdownvue_type_template_id_040297e6_hoisted_1 = {
  class: "languageSelection"
};
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_2 = ["href"];
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_3 = ["value", "title"];
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_4 = {
  action: "index.php?module=LanguagesManager&action=saveLanguage",
  method: "post",
  ref: "form"
};
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_5 = ["value"];
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_6 = ["value"];
const LanguagesDropdownvue_type_template_id_040297e6_hoisted_7 = ["value"];
function LanguagesDropdownvue_type_template_id_040297e6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MenuItemsDropdown = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MenuItemsDropdown");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", LanguagesDropdownvue_type_template_id_040297e6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MenuItemsDropdown, {
    "menu-title": _ctx.currentLanguageName,
    onAfterSelect: _cache[0] || (_cache[0] = $event => _ctx.onSelect($event))
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "item",
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.externalRawLink('https://matomo.org/translations/')
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('LanguagesManager_AboutPiwikTranslations')), 9, LanguagesDropdownvue_type_template_id_040297e6_hoisted_2), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.languages, language => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
        key: language.code,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`item ${language.code === _ctx.currentLanguageCode ? 'active' : ''}`),
        value: language.code,
        title: `${language.name} (${language.english_name})`
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(language.name), 11, LanguagesDropdownvue_type_template_id_040297e6_hoisted_3);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", LanguagesDropdownvue_type_template_id_040297e6_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "language",
      id: "language",
      value: _ctx.selectedLanguage
    }, null, 8, LanguagesDropdownvue_type_template_id_040297e6_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      name: "nonce",
      id: "nonce",
      value: _ctx.formNonce
    }, null, 8, LanguagesDropdownvue_type_template_id_040297e6_hoisted_6), _ctx.tokenAuth ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
      key: 0,
      type: "hidden",
      name: "token_auth",
      value: _ctx.tokenAuth
    }, null, 8, LanguagesDropdownvue_type_template_id_040297e6_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)]),
    _: 1
  }, 8, ["menu-title"])]);
}
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=template&id=040297e6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts


/* harmony default export */ var LanguagesDropdownvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    tokenAuth: String,
    formNonce: {
      type: String,
      required: true
    },
    languages: {
      type: Array,
      required: true
    },
    currentLanguageCode: {
      type: String,
      required: true
    },
    currentLanguageName: {
      type: String,
      required: true
    }
  },
  components: {
    MenuItemsDropdown: external_CoreHome_["MenuItemsDropdown"]
  },
  data() {
    return {
      selectedLanguage: this.currentLanguageCode
    };
  },
  methods: {
    onSelect(selected) {
      this.selectedLanguage = selected.getAttribute('value');
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])().then(() => {
        this.$refs.form.submit();
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguagesDropdown/LanguagesDropdown.vue



LanguagesDropdownvue_type_script_lang_ts.render = LanguagesDropdownvue_type_template_id_040297e6_render

/* harmony default export */ var LanguagesDropdown = (LanguagesDropdownvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/index.ts
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
//# sourceMappingURL=LanguagesManager.umd.js.map