(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["PrivacyManager"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["PrivacyManager"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/PrivacyManager/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "1752":
/***/ (function(module, exports) {



/***/ }),

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
__webpack_require__.d(__webpack_exports__, "DoNotTrackPreference", function() { return /* reexport */ DoNotTrackPreference; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=61762408

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "doNotTrack",
    modelValue: _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.enabled = $event;
    }),
    options: _ctx.doNotTrackOptions,
    "inline-help": _ctx.translate('PrivacyManager_DoNotTrack_Description')
  }, null, 8, ["modelValue", "options", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])], 512)), [[_directive_form]]);
}
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=template&id=61762408

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts



/* harmony default export */ var DoNotTrackPreferencevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isSuperUser: Boolean,
    dntSupport: Boolean,
    doNotTrackOptions: {
      type: Array,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: this.dntSupport ? 1 : 0
    };
  },
  methods: {
    save: function save() {
      var _this = this;

      this.isLoading = true;
      var action = 'deactivateDoNotTrack';

      if (this.enabled === 1) {
        action = 'activateDoNotTrack';
      }

      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: "PrivacyManager.".concat(action)
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=script&lang=ts
 
// EXTERNAL MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue?vue&type=custom&index=0&blockType=todo
var DoNotTrackPreferencevue_type_custom_index_0_blockType_todo = __webpack_require__("1752");
var DoNotTrackPreferencevue_type_custom_index_0_blockType_todo_default = /*#__PURE__*/__webpack_require__.n(DoNotTrackPreferencevue_type_custom_index_0_blockType_todo);

// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/DoNotTrackPreference/DoNotTrackPreference.vue



DoNotTrackPreferencevue_type_script_lang_ts.render = render
/* custom blocks */

if (typeof DoNotTrackPreferencevue_type_custom_index_0_blockType_todo_default.a === 'function') DoNotTrackPreferencevue_type_custom_index_0_blockType_todo_default()(DoNotTrackPreferencevue_type_script_lang_ts)


/* harmony default export */ var DoNotTrackPreference = (DoNotTrackPreferencevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/PrivacyManager/vue/src/index.ts
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
//# sourceMappingURL=PrivacyManager.umd.js.map