(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["CoreVisualizations"] = factory(require("CoreHome"), require("vue"));
	else
		root["CoreVisualizations"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/CoreVisualizations/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "SeriesPicker", function() { return /* reexport */ SeriesPicker; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=bd202f38

var _hoisted_1 = {
  key: 0,
  class: "jqplot-seriespicker-popover"
};
var _hoisted_2 = {
  class: "headline"
};
var _hoisted_3 = ["onClick"];
var _hoisted_4 = ["type", "checked"];
var _hoisted_5 = {
  key: 0,
  class: "headline recordsToPlot"
};
var _hoisted_6 = ["onClick"];
var _hoisted_7 = ["type", "checked"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["jqplot-seriespicker", {
      open: _ctx.isPopupVisible
    }]),
    onMouseenter: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.isPopupVisible = true;
    }),
    onMouseleave: _cache[2] || (_cache[2] = function ($event) {
      return _ctx.onLeavePopup();
    })
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function () {}, ["prevent", "stop"]))
  }, " + "), _ctx.isPopupVisible ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.multiselect ? 'General_MetricsToPlot' : 'General_MetricToPlot')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableColumns, function (columnConfig) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickColumn",
      onClick: function onClick($event) {
        return _ctx.optionSelected(columnConfig.column, _ctx.columnStates);
      },
      key: columnConfig.column
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.columnStates[columnConfig.column]
    }, null, 8, _hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(columnConfig.translation), 1)])], 8, _hoisted_3);
  }), 128)), _ctx.selectableRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_RecordsToPlot')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.selectableRows, function (rowConfig) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      class: "pickRow",
      onClick: function onClick($event) {
        return _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates);
      },
      key: rowConfig.matcher
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "select",
      type: _ctx.multiselect ? 'checkbox' : 'radio',
      checked: !!_ctx.rowStates[rowConfig.matcher]
    }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rowConfig.label), 1)])], 8, _hoisted_6);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 34);
}
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=template&id=bd202f38

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts



function getInitialOptionStates(allOptions, selectedOptions) {
  var states = {};
  allOptions.forEach(function (columnConfig) {
    var name = columnConfig.column || columnConfig.matcher;
    states[name] = false;
  });
  selectedOptions.forEach(function (column) {
    states[column] = true;
  });
  return states;
}

function arrayEqual(lhs, rhs) {
  if (lhs.length !== rhs.length) {
    return false;
  }

  return lhs.filter(function (element) {
    return rhs.indexOf(element) === -1;
  }).length === 0;
}

function unselectOptions(optionStates) {
  Object.keys(optionStates).forEach(function (optionName) {
    optionStates[optionName] = false;
  });
}

function getSelected(optionStates) {
  return Object.keys(optionStates).filter(function (optionName) {
    return !!optionStates[optionName];
  });
}

/* harmony default export */ var SeriesPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    multiselect: Boolean,
    selectableColumns: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectableRows: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectedColumns: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    selectedRows: {
      type: Array,
      default: function _default() {
        return [];
      }
    }
  },
  data: function data() {
    return {
      isPopupVisible: false,
      columnStates: getInitialOptionStates(this.selectableColumns, this.selectedColumns),
      rowStates: getInitialOptionStates(this.selectableRows, this.selectedRows)
    };
  },
  emits: ['select'],
  created: function created() {
    this.optionSelected = Object(external_CoreHome_["debounce"])(this.optionSelected, 0);
  },
  methods: {
    optionSelected: function optionSelected(optionValue, optionStates) {
      if (!this.multiselect) {
        unselectOptions(this.columnStates);
        unselectOptions(this.rowStates);
      }

      optionStates[optionValue] = !optionStates[optionValue];
      this.triggerOnSelectAndClose();
    },
    onLeavePopup: function onLeavePopup() {
      this.isPopupVisible = false;

      if (this.optionsChanged()) {
        this.triggerOnSelectAndClose();
      }
    },
    triggerOnSelectAndClose: function triggerOnSelectAndClose() {
      this.isPopupVisible = false;
      this.$emit('select', {
        columns: getSelected(this.columnStates),
        rows: getSelected(this.rowStates)
      });
    },
    optionsChanged: function optionsChanged() {
      return !arrayEqual(getSelected(this.columnStates), this.selectedColumns) || !arrayEqual(getSelected(this.rowStates), this.selectedRows);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.vue



SeriesPickervue_type_script_lang_ts.render = render

/* harmony default export */ var SeriesPicker = (SeriesPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/SeriesPicker/SeriesPicker.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var SeriesPicker_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SeriesPicker,
  scope: {
    multiselect: {
      angularJsBind: '<'
    },
    selectableColumns: {
      angularJsBind: '<'
    },
    selectableRows: {
      angularJsBind: '<'
    },
    selectedColumns: {
      angularJsBind: '<'
    },
    selectedRows: {
      angularJsBind: '<'
    },
    onSelect: {
      angularJsBind: '&',
      vue: 'select'
    }
  },
  directiveName: 'piwikSeriesPicker',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./plugins/CoreVisualizations/vue/src/index.ts
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
//# sourceMappingURL=CoreVisualizations.umd.js.map