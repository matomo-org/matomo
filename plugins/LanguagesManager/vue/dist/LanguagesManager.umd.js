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
__webpack_require__.d(__webpack_exports__, "LanguageSelector", function() { return /* reexport */ LanguageSelector; });

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

// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var _window = window,
    $ = _window.$;

function postLanguageChange(element, event) {
  var value = $(event.target).attr('value');

  if (value) {
    $(element).find('#language').val(value).parents('form').submit();
  }
}

/* harmony default export */ var LanguageSelector = ({
  mounted: function mounted(el, binding) {
    binding.value.onClick = postLanguageChange.bind(null, el);
    $(el).on('click', 'a[value]', binding.value.onClick);
  },
  unmounted: function unmounted(el, binding) {
    $(el).off('click', 'a[value]', binding.value.onClick);
  }
});
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/LanguageSelector/LanguageSelector.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


function languageSelection() {
  return {
    restrict: 'C',
    link: function languageSelectionLink(scope, element) {
      var binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      LanguageSelector.mounted(element[0], binding);
      element.on('$destroy', function () {
        LanguageSelector.unmounted(element[0], binding);
      });
    }
  };
}

window.angular.module('piwikApp').directive('languageSelection', languageSelection);
// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3d477584


var _hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" This page helps you to find existing translations that you can reuse in your Plugin. If you want to know more about translations have a look at our "), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  href: "https://developer.matomo.org/guides/internationalization",
  rel: "noreferrer noopener",
  target: "_blank"
}, "Internationalization guide"), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(". Enter a search term to find translations and their corresponding keys: ")], -1);

var _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_4 = {
  style: {
    "word-break": "break-all"
  }
};

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", {
  style: {
    "width": "250px"
  }
}, "Key", -1);

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, "English translation", -1);

var _hoisted_7 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "alias",
    "inline-help": "Search for English translation. Max 1000 results will be shown.",
    placeholder: "Search for English translation",
    modelValue: _ctx.searchTerm,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.searchTerm = $event;
    })
  }, null, 8, ["modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "translationSearch.compareLanguage",
    "inline-help": "Optionally select a language to compare the English language with.",
    "model-value": _ctx.compareLanguage,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      _ctx.compareLanguage = $event;

      _ctx.doCompareLanguage();
    }),
    options: _ctx.languages
  }, null, 8, ["model-value", "options"])]), _hoisted_2, _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [_hoisted_5, _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, "Compare translation", 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.compareLanguage && _ctx.compareTranslations]])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredTranslations, function (translation) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: translation.label
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(translation.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(translation.value), 1), _ctx.compareLanguage && _ctx.compareTranslations ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.compareTranslations[translation.label]), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.searchTerm], [_directive_content_table]])]);
}
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=template&id=3d477584

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts

 // loading a component this way since during Installation we don't want to load CorePluginsAdmin
// just for the language selector directive

var Field = Object(external_CoreHome_["useExternalPluginComponent"])('CorePluginsAdmin', 'Field');
/* harmony default export */ var TranslationSearchvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    Field: Field
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data: function data() {
    return {
      compareTranslations: null,
      existingTranslations: [],
      languages: [],
      compareLanguage: '',
      searchTerm: ''
    };
  },
  created: function created() {
    this.fetchTranslations('en');
    this.fetchLanguages();
  },
  methods: {
    fetchTranslations: function fetchTranslations(languageCode) {
      var _this = this;

      external_CoreHome_["AjaxHelper"].fetch({
        method: 'LanguagesManager.getTranslationsForLanguage',
        filter_limit: -1,
        languageCode: languageCode
      }).then(function (response) {
        if (!response) {
          return;
        }

        if (languageCode === 'en') {
          _this.existingTranslations = response;
        } else {
          _this.compareTranslations = {};
          response.forEach(function (translation) {
            _this.compareTranslations[translation.label] = translation.value;
          });
        }
      });
    },
    fetchLanguages: function fetchLanguages() {
      var _this2 = this;

      external_CoreHome_["AjaxHelper"].fetch({
        method: 'LanguagesManager.getAvailableLanguagesInfo',
        filter_limit: -1
      }).then(function (languages) {
        _this2.languages = [{
          key: '',
          value: 'None'
        }];

        if (languages) {
          languages.forEach(function (language) {
            if (language.code === 'en') {
              return;
            }

            _this2.languages.push({
              key: language.code,
              value: language.name
            });
          });
        }
      });
    },
    doCompareLanguage: function doCompareLanguage() {
      if (this.compareLanguage) {
        this.compareTranslations = null;
        this.fetchTranslations(this.compareLanguage);
      }
    }
  },
  computed: {
    filteredTranslations: function filteredTranslations() {
      var _this3 = this;

      var filtered = this.existingTranslations.filter(function (t) {
        return t.label.includes(_this3.searchTerm) || t.value.includes(_this3.searchTerm);
      });
      filtered = filtered.slice(0, 1000);
      return filtered;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.vue



TranslationSearchvue_type_script_lang_ts.render = render

/* harmony default export */ var TranslationSearch = (TranslationSearchvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/TranslationSearch/TranslationSearch.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var TranslationSearch_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: TranslationSearch,
  directiveName: 'piwikTranslationSearch'
}));
// CONCATENATED MODULE: ./plugins/LanguagesManager/vue/src/index.ts
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
//# sourceMappingURL=LanguagesManager.umd.js.map