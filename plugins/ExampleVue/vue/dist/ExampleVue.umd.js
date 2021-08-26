(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("vue"), require("VueClassComponent"), require("tslib"));
	else if(typeof define === 'function' && define.amd)
		define([, "VueClassComponent", "tslib"], factory);
	else if(typeof exports === 'object')
		exports["ExampleVue"] = factory(require("vue"), require("VueClassComponent"), require("tslib"));
	else
		root["ExampleVue"] = factory(root["Vue"], root["VueClassComponent"], root["tslib"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_c93a__, __WEBPACK_EXTERNAL_MODULE_d7bc__) {
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "c93a":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_c93a__;

/***/ }),

/***/ "d7bc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_d7bc__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ExampleComponent", function() { return /* reexport */ src_ExampleComponent; });
__webpack_require__.d(__webpack_exports__, "exampleComponentAdapter", function() { return /* reexport */ exampleVueComponentAdapter; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent.vue?vue&type=template&id=912c7966

function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.decrement && _ctx.decrement(...args))
  }, "-"), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.count) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.increment && _ctx.increment(...args))
  }, "+")]);
}
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent.vue?vue&type=template&id=912c7966

// EXTERNAL MODULE: external "tslib"
var external_tslib_ = __webpack_require__("d7bc");

// EXTERNAL MODULE: external "VueClassComponent"
var external_VueClassComponent_ = __webpack_require__("c93a");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/ExampleVue/vue/src/ExampleComponent.vue?vue&type=script&lang=ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }



let ExampleComponentvue_type_script_lang_ts_ExampleComponent = class ExampleComponent extends external_VueClassComponent_["Vue"] {
  constructor(...args) {
    super(...args);

    _defineProperty(this, "count", 0);
  }

  increment() {
    this.count += 1;
  }

  decrement() {
    this.count -= 1;
  }

};
ExampleComponentvue_type_script_lang_ts_ExampleComponent = Object(external_tslib_["__decorate"])([Object(external_VueClassComponent_["Options"])({})], ExampleComponentvue_type_script_lang_ts_ExampleComponent);
/* harmony default export */ var ExampleComponentvue_type_script_lang_ts = (ExampleComponentvue_type_script_lang_ts_ExampleComponent);
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent.vue



ExampleComponentvue_type_script_lang_ts.render = render

/* harmony default export */ var src_ExampleComponent = (ExampleComponentvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/ExampleComponent.adapter.ts


function exampleVueComponentAdapter() {
  return {
    restrict: 'A',
    scope: {},
    template: '<div>am i herE?</div>',
    link: function exampleVueComponentAdapterLink(scope, element) {
      const vueApp = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createApp"])(src_ExampleComponent);
      vueApp.mount(element[0]);
    }
  };
}
exampleVueComponentAdapter.$inject = [];
angular.module('piwikApp').directive('exampleVueComponent', exampleVueComponentAdapter);
// CONCATENATED MODULE: ./plugins/ExampleVue/vue/src/index.ts



// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=ExampleVue.umd.js.map