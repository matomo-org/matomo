(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", "CorePluginsAdmin", ], factory);
	else if(typeof exports === 'object')
		exports["UserCountry"] = factory(require("CoreHome"), require("CorePluginsAdmin"), require("vue"));
	else
		root["UserCountry"] = factory(root["CoreHome"], root["CorePluginsAdmin"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/UserCountry/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nvar _hoisted_1 = {\n  class: \"locationProviderSelection\"\n};\nvar _hoisted_2 = [\"innerHTML\"];\nvar _hoisted_3 = {\n  class: \"row\"\n};\nvar _hoisted_4 = {\n  class: \"col s12 push-m9 m3\"\n};\nvar _hoisted_5 = {\n  class: \"col s12 m4 l2\"\n};\nvar _hoisted_6 = [\"id\", \"disabled\", \"checked\", \"onChange\"];\nvar _hoisted_7 = {\n  class: \"loc-provider-status\"\n};\nvar _hoisted_8 = {\n  key: 0,\n  class: \"is-not-installed\"\n};\nvar _hoisted_9 = {\n  key: 1,\n  class: \"is-installed\"\n};\nvar _hoisted_10 = {\n  key: 2,\n  class: \"is-broken\"\n};\nvar _hoisted_11 = {\n  class: \"col s12 m4 l6\"\n};\nvar _hoisted_12 = [\"innerHTML\"];\nvar _hoisted_13 = [\"innerHTML\"];\nvar _hoisted_14 = {\n  class: \"col s12 m4 l4\"\n};\nvar _hoisted_15 = {\n  key: 0,\n  class: \"form-help\"\n};\nvar _hoisted_16 = {\n  key: 0\n};\n\nvar _hoisted_17 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nvar _hoisted_18 = {\n  style: {\n    \"position\": \"absolute\"\n  }\n};\nvar _hoisted_19 = [\"innerHTML\"];\nvar _hoisted_20 = {\n  class: \"text-right\"\n};\nvar _hoisted_21 = [\"onClick\"];\nvar _hoisted_22 = {\n  key: 1\n};\nvar _hoisted_23 = {\n  key: 1,\n  class: \"form-help\"\n};\nvar _hoisted_24 = {\n  key: 0\n};\nvar _hoisted_25 = [\"innerHTML\"];\nvar _hoisted_26 = [\"innerHTML\"];\nvar _hoisted_27 = {\n  key: 1\n};\nvar _hoisted_28 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  var _component_ActivityIndicator = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ActivityIndicator\");\n\n  var _component_Notification = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"Notification\");\n\n  var _component_SaveButton = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"SaveButton\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [!_ctx.isThereWorkingProvider ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n    key: 0,\n    innerHTML: _ctx.$sanitize(_ctx.setUpGuides || '')\n  }, null, 8\n  /* PROPS */\n  , _hoisted_2)) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_3, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_4, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_InfoFor', _ctx.thisIp)), 1\n  /* TEXT */\n  )]), (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(vue__WEBPACK_IMPORTED_MODULE_0__[\"Fragment\"], null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"renderList\"])(_ctx.visibleLocationProviders, function (provider, id) {\n    return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n      key: id,\n      class: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"normalizeClass\"])(\"row form-group provider\".concat(id))\n    }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_5, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"label\", null, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n      class: \"location-provider\",\n      name: \"location-provider\",\n      type: \"radio\",\n      id: \"provider_input_\".concat(id),\n      disabled: provider.status !== 1,\n      checked: _ctx.selectedProvider === id,\n      onChange: function onChange($event) {\n        return _ctx.selectedProvider = id;\n      }\n    }, null, 40\n    /* PROPS, HYDRATE_EVENTS */\n    , _hoisted_6), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translateOrDefault(provider.title)), 1\n    /* TEXT */\n    )])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", _hoisted_7, [provider.status === 0 ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"span\", _hoisted_8, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_NotInstalled')), 1\n    /* TEXT */\n    )) : provider.status === 1 ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"span\", _hoisted_9, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Installed')), 1\n    /* TEXT */\n    )) : provider.status === 2 ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"span\", _hoisted_10, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Broken')), 1\n    /* TEXT */\n    )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true)])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_11, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", {\n      innerHTML: _ctx.$sanitize(_ctx.translateOrDefault(provider.description))\n    }, null, 8\n    /* PROPS */\n    , _hoisted_12), provider.status !== 1 && provider.install_docs ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"p\", {\n      key: 0,\n      innerHTML: _ctx.$sanitize(provider.install_docs)\n    }, null, 8\n    /* PROPS */\n    , _hoisted_13)) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true)]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_14, [provider.status === 1 ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_15, [_ctx.thisIp !== '127.0.0.1' ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_16, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createTextVNode\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('UserCountry_CurrentLocationIntro')) + \": \", 1\n    /* TEXT */\n    ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", null, [_hoisted_17, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_18, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ActivityIndicator, {\n      loading: _ctx.updateLoading[id]\n    }, null, 8\n    /* PROPS */\n    , [\"loading\"])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n      class: \"location\",\n      style: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"normalizeStyle\"])({\n        visibility: _ctx.providerLocations[id] ? 'visible' : 'hidden'\n      })\n    }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"strong\", {\n      innerHTML: _ctx.$sanitize(_ctx.providerLocations[id] || ' ')\n    }, null, 8\n    /* PROPS */\n    , _hoisted_19)], 4\n    /* STYLE */\n    )]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_20, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"a\", {\n      onClick: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withModifiers\"])(function ($event) {\n        return _ctx.refreshProviderInfo(id);\n      }, [\"prevent\"])\n    }, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Refresh')), 9\n    /* TEXT, PROPS */\n    , _hoisted_21)])])) : (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_22, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('UserCountry_CannotLocalizeLocalIP', _ctx.thisIp)), 1\n    /* TEXT */\n    ))])) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), provider.statusMessage ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_23, [provider.status === 2 ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"strong\", _hoisted_24, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('General_Error')) + \":\", 1\n    /* TEXT */\n    )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n      innerHTML: _ctx.$sanitize(provider.statusMessage)\n    }, null, 8\n    /* PROPS */\n    , _hoisted_25)])) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), provider.extra_message ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n      key: 2,\n      class: \"form-help\",\n      innerHTML: _ctx.$sanitize(provider.extra_message)\n    }, null, 8\n    /* PROPS */\n    , _hoisted_26)) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true)])], 2\n    /* CLASS */\n    );\n  }), 128\n  /* KEYED_FRAGMENT */\n  )), !Object.keys(_ctx.locationProvidersNotDefaultOrDisabled).length ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_27, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_Notification, {\n    noclear: true,\n    context: \"warning\"\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(function () {\n      return [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"span\", {\n        innerHTML: _ctx.$sanitize(_ctx.noProvidersText)\n      }, null, 8\n      /* PROPS */\n      , _hoisted_28)];\n    }),\n    _: 1\n    /* STABLE */\n\n  })])) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_SaveButton, {\n    onConfirm: _cache[0] || (_cache[0] = function ($event) {\n      return _ctx.save();\n    }),\n    saving: _ctx.isLoading\n  }, null, 8\n  /* PROPS */\n  , [\"saving\"])]);\n}\n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! CorePluginsAdmin */ \"CorePluginsAdmin\");\n/* harmony import */ var CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__);\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== \"undefined\" && arr[Symbol.iterator] || arr[\"@@iterator\"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    currentProviderId: {\n      type: String,\n      required: true\n    },\n    isThereWorkingProvider: Boolean,\n    setUpGuides: String,\n    thisIp: {\n      type: String,\n      required: true\n    },\n    locationProviders: {\n      type: Object,\n      required: true\n    },\n    defaultProviderId: {\n      type: String,\n      required: true\n    },\n    disabledProviderId: {\n      type: String,\n      required: true\n    }\n  },\n  components: {\n    ActivityIndicator: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"ActivityIndicator\"],\n    Notification: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"Notification\"],\n    SaveButton: CorePluginsAdmin__WEBPACK_IMPORTED_MODULE_2__[\"SaveButton\"]\n  },\n  data: function data() {\n    return {\n      isLoading: false,\n      updateLoading: {},\n      selectedProvider: this.currentProviderId,\n      providerLocations: Object.fromEntries(Object.entries(this.locationProviders).map(function (_ref) {\n        var _ref2 = _slicedToArray(_ref, 2),\n            k = _ref2[0],\n            p = _ref2[1];\n\n        return [k, p.location];\n      }))\n    };\n  },\n  methods: {\n    refreshProviderInfo: function refreshProviderInfo(providerId) {\n      var _this = this;\n\n      // this should not be in a controller... ideally we fetch this data always from client side\n      // and do not prefill it server side\n      this.updateLoading[providerId] = true;\n      delete this.providerLocations[providerId];\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        module: 'UserCountry',\n        action: 'getLocationUsingProvider',\n        id: providerId,\n        format: 'html'\n      }, {\n        format: 'html'\n      }).then(function (response) {\n        _this.providerLocations[providerId] = response;\n      }).finally(function () {\n        _this.updateLoading[providerId] = false;\n      });\n    },\n    save: function save() {\n      var _this2 = this;\n\n      if (!this.selectedProvider) {\n        return;\n      }\n\n      this.isLoading = true;\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        method: 'UserCountry.setLocationProvider',\n        providerId: this.selectedProvider\n      }, {\n        withTokenInUrl: true\n      }).then(function () {\n        var notificationInstanceId = CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"NotificationsStore\"].show({\n          message: Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('General_Done'),\n          context: 'success',\n          noclear: true,\n          type: 'toast',\n          id: 'userCountryLocationProvider'\n        });\n        CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"NotificationsStore\"].scrollToNotification(notificationInstanceId);\n      }).finally(function () {\n        _this2.isLoading = false;\n      });\n    }\n  },\n  computed: {\n    visibleLocationProviders: function visibleLocationProviders() {\n      return Object.fromEntries(Object.entries(this.locationProviders).filter(function (_ref3) {\n        var _ref4 = _slicedToArray(_ref3, 2),\n            p = _ref4[1];\n\n        return p.isVisible;\n      }));\n    },\n    locationProvidersNotDefaultOrDisabled: function locationProvidersNotDefaultOrDisabled() {\n      var _this3 = this;\n\n      return Object.fromEntries(Object.entries(this.locationProviders).filter(function (_ref5) {\n        var _ref6 = _slicedToArray(_ref5, 2),\n            p = _ref6[1];\n\n        return p.id !== _this3.defaultProviderId && p.id !== _this3.disabledProviderId;\n      }));\n    },\n    noProvidersText: function noProvidersText() {\n      return Object(CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"translate\"])('UserCountry_NoProviders', '<a rel=\"noreferrer noopener\" href=\"https://db-ip.com/?refid=mtm\" target=\"_blank\">', '</a>');\n    }\n  }\n}));\n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: LocationProviderSelection */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/UserCountry/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LocationProviderSelection\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"LocationProviderSelection\"]; });\n\n\n\n\n\n//# sourceURL=webpack://UserCountry/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://UserCountry/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue":
/*!*********************************************************************************************!*\
  !*** ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue ***!
  \*********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LocationProviderSelection_vue_vue_type_template_id_696b15d0__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LocationProviderSelection.vue?vue&type=template&id=696b15d0 */ \"./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0\");\n/* harmony import */ var _LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LocationProviderSelection.vue?vue&type=script&lang=ts */ \"./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _LocationProviderSelection_vue_vue_type_template_id_696b15d0__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?");

/***/ }),

/***/ "./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts":
/*!*********************************************************************************************************************!*\
  !*** ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts ***!
  \*********************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./LocationProviderSelection.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LocationProviderSelection_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?");

/***/ }),

/***/ "./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0":
/*!***************************************************************************************************************************!*\
  !*** ./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0 ***!
  \***************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LocationProviderSelection_vue_vue_type_template_id_696b15d0__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./LocationProviderSelection.vue?vue&type=template&id=696b15d0 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?vue&type=template&id=696b15d0\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_LocationProviderSelection_vue_vue_type_template_id_696b15d0__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue?");

/***/ }),

/***/ "./plugins/UserCountry/vue/src/index.ts":
/*!**********************************************!*\
  !*** ./plugins/UserCountry/vue/src/index.ts ***!
  \**********************************************/
/*! exports provided: LocationProviderSelection */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LocationProviderSelection_LocationProviderSelection_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LocationProviderSelection/LocationProviderSelection.vue */ \"./plugins/UserCountry/vue/src/LocationProviderSelection/LocationProviderSelection.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"LocationProviderSelection\", function() { return _LocationProviderSelection_LocationProviderSelection_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n//# sourceURL=webpack://UserCountry/./plugins/UserCountry/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://UserCountry/external_%22CoreHome%22?");

/***/ }),

/***/ "CorePluginsAdmin":
/*!***********************************!*\
  !*** external "CorePluginsAdmin" ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CorePluginsAdmin__;\n\n//# sourceURL=webpack://UserCountry/external_%22CorePluginsAdmin%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://UserCountry/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});