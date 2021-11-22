(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["CorePluginsAdmin"] = factory(require("CoreHome"), require("vue"));
	else
		root["CorePluginsAdmin"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/CorePluginsAdmin/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "FormField", function() { return /* reexport */ FormField; });
__webpack_require__.d(__webpack_exports__, "Field", function() { return /* reexport */ Field; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=92496620
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


var _hoisted_1 = {
  class: "form-group row"
};
var _hoisted_2 = {
  key: 0,
  class: "col s12"
};
var _hoisted_3 = {
  key: 0,
  class: "form-help"
};
var _hoisted_4 = {
  key: 0,
  class: "inline-help",
  ref: "inlineHelp"
};

var _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_ctx.formField.introduction ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", _hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.introduction), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'input-field': _ctx.formField.uiControl !== 'checkbox' && _ctx.formField.uiControl !== 'radio',
      'file-field': _ctx.formField.uiControl === 'file',
      'm6': !_ctx.formField.fullWidth
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.childComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(_objectSpread(_objectSpread({}, _ctx.formField), {}, {
    modelValue: _ctx.processedModelValue,
    availableOptions: _ctx.availableOptions
  }, _ctx.extraChildComponentParams), {
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }), null, 16))], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["col s12", {
      'm6': !_ctx.formField.fullWidth
    }])
  }, [_ctx.showFormHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "form-description"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.formField.description]]), _ctx.formField.inlineHelp ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_4, null, 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.defaultValuePrettyTruncated), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showDefaultValue]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showField]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=92496620

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=c034113c

var FieldCheckboxvue_type_template_id_c034113c_hoisted_1 = {
  class: "checkbox"
};
var FieldCheckboxvue_type_template_id_c034113c_hoisted_2 = ["checked", "id", "name"];
var FieldCheckboxvue_type_template_id_c034113c_hoisted_3 = ["innerHTML"];
function FieldCheckboxvue_type_template_id_c034113c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxvue_type_template_id_c034113c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes, {
    value: 1,
    checked: !!_ctx.modelValue,
    type: "checkbox",
    id: _ctx.name,
    name: _ctx.name
  }), null, 16, FieldCheckboxvue_type_template_id_c034113c_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldCheckboxvue_type_template_id_c034113c_hoisted_3)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=c034113c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldCheckboxvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    uiControlAttributes: Object,
    name: String,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      if (this.modelValue !== event.target.checked) {
        this.$emit('update:modelValue', event.target.checked);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue



FieldCheckboxvue_type_script_lang_ts.render = FieldCheckboxvue_type_template_id_c034113c_render

/* harmony default export */ var FieldCheckbox = (FieldCheckboxvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=5c43edab

var FieldCheckboxArrayvue_type_template_id_5c43edab_hoisted_1 = ["value", "checked", "onChange", "id", "name"];
function FieldCheckboxArrayvue_type_template_id_5c43edab_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (checkboxModel, $index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: $index,
      class: "checkbox"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: checkboxModel.key,
      checked: !!_ctx.checkboxStates[$index],
      onChange: function onChange($event) {
        return _ctx.onChange($index);
      }
    }, _ctx.uiControlAttributes, {
      type: "checkbox",
      id: "".concat(_ctx.name).concat(checkboxModel.key),
      name: checkboxModel.name
    }), null, 16, FieldCheckboxArrayvue_type_template_id_5c43edab_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], checkboxModel.description]])])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=5c43edab

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

 // TODO: how often is this called

function getCheckboxStates(availableOptions, modelValue) {
  return Object.values(availableOptions).map(function (o) {
    return modelValue && modelValue.indexOf(o.key) !== -1;
  });
}

/* harmony default export */ var FieldCheckboxArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Object,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    type: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    checkboxStates: function checkboxStates() {
      return getCheckboxStates(this.availableOptions, this.modelValue);
    }
  },
  mounted: function mounted() {
    window.Materialize.updateTextFields();
  },
  methods: {
    onChange: function onChange(changedIndex) {
      var checkboxStates = _toConsumableArray(this.checkboxStates);

      checkboxStates[changedIndex] = !checkboxStates[changedIndex];
      var newValue = [];
      Object.values(this.availableOptions).forEach(function (option, index) {
        if (checkboxStates[index]) {
          newValue.push(option.key);
        }
      });
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue



FieldCheckboxArrayvue_type_script_lang_ts.render = FieldCheckboxArrayvue_type_template_id_5c43edab_render

/* harmony default export */ var FieldCheckboxArray = (FieldCheckboxArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=1d7fd05e

var FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_1 = {
  class: "expandableSelector"
};

var FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("svg", {
  class: "caret",
  height: "24",
  viewBox: "0 0 24 24",
  width: "24",
  xmlns: "http://www.w3.org/2000/svg"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M7 10l5 5 5-5z"
}), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
  d: "M0 0h24v24H0z",
  fill: "none"
})], -1);

var FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_3 = ["value"];
var FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_4 = {
  class: "expandableList z-depth-2"
};
var FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_5 = {
  class: "searchContainer"
};
var _hoisted_6 = {
  class: "collection firstLevel"
};
var _hoisted_7 = ["onClick"];
var _hoisted_8 = {
  class: "collection secondLevel"
};
var _hoisted_9 = ["onClick"];
var _hoisted_10 = {
  class: "primary-content"
};
var _hoisted_11 = ["title"];
function FieldExpandableSelectvue_type_template_id_1d7fd05e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showSelect = !_ctx.showSelect;
    }),
    class: "select-wrapper"
  }, [FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "select-dropdown",
    readonly: "readonly",
    value: _ctx.modelValueText
  }, null, 8, FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_1d7fd05e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    placeholder: "Search",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    class: "expandableSearch browser-default"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, _ctx.showSelect]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (options, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      class: "collection-item",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h4", {
      class: "expandableListCategory",
      onClick: function onClick($event) {
        return _ctx.onCategoryClicked(options);
      }
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(options.group) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["secondary-content", {
        "icon-arrow-right": _ctx.showCategory !== options.group,
        "icon-arrow-bottom": _ctx.showCategory === options.group
      }])
    }, null, 2)], 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options.values.filter(function (x) {
      return x.value.indexOf(_ctx.searchTerm) !== -1;
    }), function (children) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        class: "expandableListItem collection-item valign-wrapper",
        key: children.key,
        onClick: function onClick($event) {
          return _ctx.onValueClicked(children);
        }
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(children.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        title: children.tooltip,
        class: "secondary-content icon-help"
      }, null, 8, _hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], children.tooltip]])], 8, _hoisted_9);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showCategory === options.group || _ctx.searchTerm]])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], options.values.filter(function (x) {
      return x.value.indexOf(_ctx.searchTerm) !== -1;
    }).length]]);
  }), 128))])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSelect]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=1d7fd05e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts


function getAvailableOptions(availableValues) {
  var flatValues = [];
  var groups = {};
  Object.values(availableValues).forEach(function (uncastedValue) {
    var value = uncastedValue;
    var group = value.group || '';

    if (!(group in groups) || !groups[group]) {
      groups[group] = {
        values: [],
        group: group
      };
    }

    var formatted = {
      key: value.key,
      value: value.value
    };

    if ('tooltip' in value && value.tooltip) {
      formatted.tooltip = value.tooltip;
    }

    groups[group].values.push(formatted);
  });
  Object.values(groups).forEach(function (group) {
    if (group.values.length) {
      flatValues.push(group);
    }
  });
  return flatValues;
}
/* harmony default export */ var FieldExpandableSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Number, String],
    availableOptions: Array
  },
  directives: {
    FocusAnywhereButHere: external_CoreHome_["FocusAnywhereButHere"],
    FocusIf: external_CoreHome_["FocusIf"]
  },
  inheritAttrs: false,
  // emits modelValue update, but doesn't sync to input value. same as angularjs directive.
  emits: ['update:modelValue'],
  data: function data() {
    return {
      showSelect: false,
      searchTerm: '',
      showCategory: ''
    };
  },
  computed: {
    modelValueText: function modelValueText() {
      var key = this.modelValue;
      var keyItem;
      this.availableOptions.some(function (option) {
        keyItem = option.values.find(function (item) {
          return item.key === key;
        });
        return keyItem; // stop iterating if found
      });

      if (keyItem) {
        return keyItem.value.toString();
      }

      return key;
    }
  },
  methods: {
    onBlur: function onBlur() {
      this.showSelect = false;
    },
    onCategoryClicked: function onCategoryClicked(options) {
      if (this.showCategory === options.group) {
        this.showCategory = '';
      } else {
        this.showCategory = options.group;
      }
    },
    onValueClicked: function onValueClicked(selectedValue) {
      this.$emit('update:modelValue', selectedValue.key);
      this.showSelect = false;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue



FieldExpandableSelectvue_type_script_lang_ts.render = FieldExpandableSelectvue_type_template_id_1d7fd05e_render

/* harmony default export */ var FieldExpandableSelect = (FieldExpandableSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=de4fbe4e

var FieldFieldArrayvue_type_template_id_de4fbe4e_hoisted_1 = ["for", "innerHTML"];
function FieldFieldArrayvue_type_template_id_de4fbe4e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FieldArray = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FieldArray");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFieldArrayvue_type_template_id_de4fbe4e_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FieldArray, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onValueUpdate($event);
    }),
    field: _ctx.uiControlAttributes.field
  }, null, 8, ["name", "model-value", "field"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=de4fbe4e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldFieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FieldArray: external_CoreHome_["FieldArray"]
  },
  props: {
    name: String,
    title: String,
    modelValue: null,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onValueUpdate: function onValueUpdate(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue



FieldFieldArrayvue_type_script_lang_ts.render = FieldFieldArrayvue_type_template_id_de4fbe4e_render

/* harmony default export */ var FieldFieldArray = (FieldFieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=19b62409

var FieldFilevue_type_template_id_19b62409_hoisted_1 = {
  class: "btn"
};
var FieldFilevue_type_template_id_19b62409_hoisted_2 = ["for", "innerHTML"];
var FieldFilevue_type_template_id_19b62409_hoisted_3 = ["name", "id"];
var FieldFilevue_type_template_id_19b62409_hoisted_4 = {
  class: "file-path-wrapper"
};
var FieldFilevue_type_template_id_19b62409_hoisted_5 = ["value"];
function FieldFilevue_type_template_id_19b62409_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_19b62409_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFilevue_type_template_id_19b62409_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    ref: "fileInput",
    name: _ctx.name,
    type: "file",
    id: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldFilevue_type_template_id_19b62409_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_19b62409_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "file-path validate",
    value: _ctx.modelValue,
    type: "text"
  }, null, 8, FieldFilevue_type_template_id_19b62409_hoisted_5)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=19b62409

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldFilevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  setup: function setup(props) {
    var fileInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.value;
    }, function (v) {
      if (v === '') {
        var fileInputElement = fileInput.value;
        fileInputElement.value = '';
      }
    });
    return {
      fileInput: fileInput
    };
  },
  methods: {
    onChange: function onChange(event) {
      var file = event.target.files.item(0);
      this.$emit('update:modelValue', file);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue



FieldFilevue_type_script_lang_ts.render = FieldFilevue_type_template_id_19b62409_render

/* harmony default export */ var FieldFile = (FieldFilevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=19d94b14

var FieldHiddenvue_type_template_id_19d94b14_hoisted_1 = ["type", "name", "value"];
function FieldHiddenvue_type_template_id_19d94b14_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: _ctx.uiControl,
    name: _ctx.name,
    value: _ctx.modelValue,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldHiddenvue_type_template_id_19d94b14_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=19d94b14

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldHiddenvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    uiControl: String,
    name: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue



FieldHiddenvue_type_script_lang_ts.render = FieldHiddenvue_type_template_id_19d94b14_render

/* harmony default export */ var FieldHidden = (FieldHiddenvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=2770b1bc

var FieldMultituplevue_type_template_id_2770b1bc_hoisted_1 = {
  class: "fieldMultiTuple"
};
var FieldMultituplevue_type_template_id_2770b1bc_hoisted_2 = ["for", "innerHTML"];
function FieldMultituplevue_type_template_id_2770b1bc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MultiPairField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultiPairField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldMultituplevue_type_template_id_2770b1bc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldMultituplevue_type_template_id_2770b1bc_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MultiPairField, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _ctx.onUpdateValue,
    field1: _ctx.uiControlAttributes.field1,
    field2: _ctx.uiControlAttributes.field2,
    field3: _ctx.uiControlAttributes.field3,
    field4: _ctx.uiControlAttributes.field4
  }, null, 8, ["name", "model-value", "onUpdate:modelValue", "field1", "field2", "field3", "field4"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=2770b1bc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldMultituplevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: null,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    MultiPairField: external_CoreHome_["MultiPairField"]
  },
  emits: ['update:modelValue'],
  methods: {
    onUpdateValue: function onUpdateValue(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue



FieldMultituplevue_type_script_lang_ts.render = FieldMultituplevue_type_template_id_2770b1bc_render

/* harmony default export */ var FieldMultituple = (FieldMultituplevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=b9cb2162

var FieldNumbervue_type_template_id_b9cb2162_hoisted_1 = ["type", "id", "name", "value"];
var FieldNumbervue_type_template_id_b9cb2162_hoisted_2 = ["for", "innerHTML"];
function FieldNumbervue_type_template_id_b9cb2162_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: (_ctx.modelValue || '').toString(),
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldNumbervue_type_template_id_b9cb2162_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldNumbervue_type_template_id_b9cb2162_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=b9cb2162

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldNumbervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    uiControl: String,
    name: String,
    title: String,
    modelValue: [Number, String],
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onChange = Object(external_CoreHome_["debounce"])(this.onChange.bind(this), 50);
  },
  methods: {
    onChange: function onChange(event) {
      var value = parseFloat(event.target.value);
      this.$emit('update:modelValue', value);
    }
  },
  mounted: function mounted() {
    window.Materialize.updateTextFields();
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        // TODO: removed $timeout
        setTimeout(function () {
          window.Materialize.updateTextFields();
        });
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue



FieldNumbervue_type_script_lang_ts.render = FieldNumbervue_type_template_id_b9cb2162_render

/* harmony default export */ var FieldNumber = (FieldNumbervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=cfccf0ac

var FieldRadiovue_type_template_id_cfccf0ac_hoisted_1 = ["value", "id", "name", "disabled", "checked"];
function FieldRadiovue_type_template_id_cfccf0ac_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (radioModel) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: radioModel.key,
      class: "radio"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
      value: radioModel.key,
      onChange: _cache[0] || (_cache[0] = function ($event) {
        return _ctx.onChange($event);
      }),
      type: "radio",
      id: "".concat(_ctx.name).concat(radioModel.key),
      name: _ctx.name,
      disabled: radioModel.disabled || _ctx.disabled
    }, _ctx.uiControlAttributes, {
      checked: _ctx.modelValue === radioModel.key
    }), null, 16, FieldRadiovue_type_template_id_cfccf0ac_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.value) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], radioModel.description]])])])]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=cfccf0ac

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldRadiovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: null
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue



FieldRadiovue_type_script_lang_ts.render = FieldRadiovue_type_template_id_cfccf0ac_render

/* harmony default export */ var FieldRadio = (FieldRadiovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32ed2642
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || FieldSelectvue_type_template_id_32ed2642_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_template_id_32ed2642_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_template_id_32ed2642_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_template_id_32ed2642_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_template_id_32ed2642_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }


var FieldSelectvue_type_template_id_32ed2642_hoisted_1 = {
  key: 0
};
var FieldSelectvue_type_template_id_32ed2642_hoisted_2 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_3 = ["label"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_4 = ["value", "selected"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_5 = ["for", "innerHTML"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_6 = {
  key: 1
};
var FieldSelectvue_type_template_id_32ed2642_hoisted_7 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_8 = ["value", "selected"];
var FieldSelectvue_type_template_id_32ed2642_hoisted_9 = ["for", "innerHTML"];
function FieldSelectvue_type_template_id_32ed2642_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.groupedOptions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32ed2642_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "select",
    class: "grouped",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groupedOptions, function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        group = _ref2[0],
        options = _ref2[1];

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
      key: group,
      label: group
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(options, function (option) {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: option.key,
        value: "string:".concat(option.key),
        selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32ed2642_hoisted_4);
    }), 128))], 8, FieldSelectvue_type_template_id_32ed2642_hoisted_3);
  }), 128))], 16, FieldSelectvue_type_template_id_32ed2642_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.title
  }, null, 8, FieldSelectvue_type_template_id_32ed2642_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.groupedOptions && _ctx.options ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32ed2642_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "ungrouped",
    ref: "select",
    multiple: _ctx.multiple,
    name: _ctx.name,
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, function (option) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option.key,
      value: "string:".concat(option.key),
      selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32ed2642_hoisted_8);
  }), 128))], 16, FieldSelectvue_type_template_id_32ed2642_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.title
  }, null, 8, FieldSelectvue_type_template_id_32ed2642_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32ed2642

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
function FieldSelectvue_type_script_lang_ts_toConsumableArray(arr) { return FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArray(arr) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr) || FieldSelectvue_type_script_lang_ts_nonIterableSpread(); }

function FieldSelectvue_type_script_lang_ts_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function FieldSelectvue_type_script_lang_ts_arrayWithoutHoles(arr) { if (Array.isArray(arr)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr); }

function FieldSelectvue_type_script_lang_ts_slicedToArray(arr, i) { return FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) || FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || FieldSelectvue_type_script_lang_ts_nonIterableRest(); }

function FieldSelectvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function FieldSelectvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function FieldSelectvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }



function initMaterialSelect(select, placeholder) {
  var uiControlOptions = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var $select = window.$(select);
  $select.formSelect(uiControlOptions); // add placeholder to input

  if (placeholder) {
    var $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (availableValues instanceof Array || !(_typeof(availableValues) === 'object')) {
    return false;
  }

  return Object.values(availableValues).some(function (v) {
    return _typeof(v) === 'object';
  });
}

function hasOption(flatValues, key) {
  return flatValues.some(function (f) {
    return f.key === key;
  });
}

function FieldSelectvue_type_script_lang_ts_getAvailableOptions(givenAvailableValues, type, uiControlAttributes) {
  if (!givenAvailableValues) {
    return [];
  }

  var availableValues = givenAvailableValues;

  if (!hasGroupedValues(availableValues)) {
    availableValues = {
      '': givenAvailableValues
    };
  }

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref, 2),
        group = _ref2[0],
        values = _ref2[1];

    Object.entries(values).forEach(function (_ref3) {
      var _ref4 = FieldSelectvue_type_script_lang_ts_slicedToArray(_ref3, 2),
          valueObjKey = _ref4[0],
          value = _ref4[1];

      if (_typeof(value) === 'object' && typeof value.key !== 'undefined') {
        flatValues.push(value);
        return;
      }

      var key = valueObjKey;

      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({
        group: group,
        key: key,
        value: value
      });
    });
  }); // for selects w/ a placeholder, add an option to unset the select

  if (uiControlAttributes.placeholder && !hasOption(flatValues, '')) {
    return [{
      key: '',
      value: ''
    }].concat(flatValues);
  }

  return flatValues;
}

function handleOldAngularJsValues(value) {
  if (typeof value === 'string') {
    return value.replace(/^string:/, '');
  }

  return value;
}

/* harmony default export */ var FieldSelectvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    multiple: Boolean,
    name: String,
    title: String,
    availableOptions: Array,
    uiControlAttributes: Object,
    uiControlOptions: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    options: function options() {
      // if modelValue is empty, but there is no empty value allowed in availableOptions,
      // add one temporarily until something is set
      if (this.availableOptions && !hasOption(this.availableOptions, '') && (typeof this.modelValue === 'undefined' || this.modelValue === null || this.modelValue === '')) {
        return [{
          key: '',
          value: this.modelValue,
          group: ''
        }].concat(FieldSelectvue_type_script_lang_ts_toConsumableArray(this.availableOptions));
      }

      return this.availableOptions;
    },
    groupedOptions: function groupedOptions() {
      var options = this.options;

      if (!options || !options[0] || typeof options[0].group === 'undefined') {
        return null;
      }

      var groups = {};
      options.forEach(function (entry) {
        groups[entry.group] = groups[entry.group] || [];
        groups[entry.group].push(entry);
      });
      var result = Object.entries(groups);
      result.sort(function (lhs, rhs) {
        if (lhs[0] < rhs[0]) {
          return -1;
        }

        if (lhs[0] > rhs[0]) {
          return 1;
        }

        return 0;
      });
      return result;
    }
  },
  methods: {
    onChange: function onChange(event) {
      var element = event.target;
      var newValue;

      if (this.multiple) {
        // TODO: check Array.from compatibility
        newValue = Array.from(element.options).filter(function (e) {
          return e.selected;
        }).map(function (e) {
          return e.value;
        });
        newValue = newValue.map(handleOldAngularJsValues);
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      this.$emit('update:modelValue', newValue);
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this = this;

      if (newVal !== oldVal) {
        window.$(this.$refs.select).val(newVal);
        setTimeout(function () {
          initMaterialSelect(_this.$refs.select, _this.uiControlAttributes.placeholder, _this.uiControlOptions);
        });
      }
    },
    'uiControlAttributes.disabled': {
      handler: function handler(newVal, oldVal) {
        var _this2 = this;

        setTimeout(function () {
          if (newVal !== oldVal) {
            initMaterialSelect(_this2.$refs.select, _this2.uiControlAttributes.placeholder, _this2.uiControlOptions);
          }
        });
      }
    },
    availableOptions: function availableOptions(newVal, oldVal) {
      var _this3 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          initMaterialSelect(_this3.$refs.select, _this3.uiControlAttributes.placeholder, _this3.uiControlOptions);
        });
      }
    }
  },
  mounted: function mounted() {
    var _this4 = this;

    setTimeout(function () {
      initMaterialSelect(_this4.$refs.select, _this4.uiControlAttributes.placeholder, _this4.uiControlOptions);
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue



FieldSelectvue_type_script_lang_ts.render = FieldSelectvue_type_template_id_32ed2642_render

/* harmony default export */ var FieldSelect = (FieldSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=e71fa3e8

var FieldSitevue_type_template_id_e71fa3e8_hoisted_1 = ["for", "innerHTML"];
var FieldSitevue_type_template_id_e71fa3e8_hoisted_2 = {
  class: "sites_autocomplete"
};
function FieldSitevue_type_template_id_e71fa3e8_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    class: "siteSelectorLabel",
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSitevue_type_template_id_e71fa3e8_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldSitevue_type_template_id_e71fa3e8_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    id: _ctx.name,
    "show-all-sites-item": _ctx.uiControlAttributes.showAllSitesItem || false,
    "switch-site-on-select": false,
    "show-selected-site": true,
    "only-sites-with-admin-access": _ctx.uiControlAttributes.onlySitesWithAdminAccess || false
  }, _ctx.uiControlAttributes), null, 16, ["model-value", "id", "show-all-sites-item", "only-sites-with-admin-access"])])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=e71fa3e8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    SiteSelector: external_CoreHome_["SiteSelector"]
  },
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue



FieldSitevue_type_script_lang_ts.render = FieldSitevue_type_template_id_e71fa3e8_render

/* harmony default export */ var FieldSite = (FieldSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=34bb8ec2

var FieldTextvue_type_template_id_34bb8ec2_hoisted_1 = ["type", "id", "name", "value"];
var FieldTextvue_type_template_id_34bb8ec2_hoisted_2 = ["for", "innerHTML"];
function FieldTextvue_type_template_id_34bb8ec2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: (_ctx.modelValue || '').toString(),
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldTextvue_type_template_id_34bb8ec2_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextvue_type_template_id_34bb8ec2_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=34bb8ec2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    name: String,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    uiControl: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      if (newVal !== oldVal) {
        // TODO: removed $timeout
        setTimeout(function () {
          window.Materialize.updateTextFields();
        });
      }
    }
  },
  methods: {
    onKeydown: function onKeydown(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue



FieldTextvue_type_script_lang_ts.render = FieldTextvue_type_template_id_34bb8ec2_render

/* harmony default export */ var FieldText = (FieldTextvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=5951fa2a

var FieldTextareavue_type_template_id_5951fa2a_hoisted_1 = ["name", "id", "value"];
var FieldTextareavue_type_template_id_5951fa2a_hoisted_2 = ["for", "innerHTML"];
function FieldTextareavue_type_template_id_5951fa2a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    id: _ctx.name,
    value: _ctx.modelValue,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea",
    ref: "textarea"
  }), null, 16, FieldTextareavue_type_template_id_5951fa2a_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareavue_type_template_id_5951fa2a_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=5951fa2a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextareavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      this.$emit('update:modelValue', event.target.value);
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          window.Materialize.textareaAutoResize(_this.$refs.textarea);
          window.Materialize.updateTextFields();
        });
      }
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    setTimeout(function () {
      window.Materialize.textareaAutoResize(_this2.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue



FieldTextareavue_type_script_lang_ts.render = FieldTextareavue_type_template_id_5951fa2a_render

/* harmony default export */ var FieldTextarea = (FieldTextareavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=36165a41

var FieldTextareaArrayvue_type_template_id_36165a41_hoisted_1 = ["for", "innerHTML"];
var FieldTextareaArrayvue_type_template_id_36165a41_hoisted_2 = ["name", "value"];
function FieldTextareaArrayvue_type_template_id_36165a41_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareaArrayvue_type_template_id_36165a41_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "textarea",
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    value: _ctx.concattedValue,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea"
  }), null, 16, FieldTextareaArrayvue_type_template_id_36165a41_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=36165a41

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts


var SEPARATOR = '\n';
/* harmony default export */ var FieldTextareaArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: Array
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue: function concattedValue() {
      return (this.modelValue || []).join(SEPARATOR);
    }
  },
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var value = event.target.value.split(SEPARATOR).map(function (v) {
        return v.trim();
      });
      this.$emit('update:modelValue', value);
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          window.Materialize.textareaAutoResize(_this.$refs.textarea);
          window.Materialize.updateTextFields();
        });
      }
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    setTimeout(function () {
      window.Materialize.textareaAutoResize(_this2.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue



FieldTextareaArrayvue_type_script_lang_ts.render = FieldTextareaArrayvue_type_template_id_36165a41_render

/* harmony default export */ var FieldTextareaArray = (FieldTextareaArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/utilities.ts
function utilities_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { utilities_typeof = function _typeof(obj) { return typeof obj; }; } else { utilities_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return utilities_typeof(obj); }

function utilities_slicedToArray(arr, i) { return utilities_arrayWithHoles(arr) || utilities_iterableToArrayLimit(arr, i) || utilities_unsupportedIterableToArray(arr, i) || utilities_nonIterableRest(); }

function utilities_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function utilities_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return utilities_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return utilities_arrayLikeToArray(o, minLen); }

function utilities_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function utilities_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function utilities_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function processCheckboxAndRadioAvailableValues(availableValues, type) {
  if (!availableValues) {
    return [];
  } // TODO: check Object.entries([...]) on old browsers


  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = utilities_slicedToArray(_ref, 2),
        valueObjKey = _ref2[0],
        value = _ref2[1];

    if (utilities_typeof(value) === 'object' && typeof value.key !== 'undefined') {
      flatValues.push(value);
      return;
    }

    var key = valueObjKey;

    if (type === 'integer' && typeof valueObjKey === 'string') {
      key = parseInt(key, 10);
    }

    flatValues.push({
      key: key,
      value: value
    });
  });
  return flatValues;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
function FormFieldvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FormFieldvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FormFieldvue_type_script_lang_ts_typeof(obj); }

















var TEXT_CONTROLS = ['password', 'url', 'search', 'email'];
var CONTROLS_SUPPORTING_ARRAY = ['textarea', 'checkbox', 'text'];
var CONTROL_TO_COMPONENT_MAP = {
  checkbox: 'FieldCheckbox',
  'expandable-select': 'FieldExpandableSelect',
  'field-array': 'FieldFieldArray',
  file: 'FieldFile',
  hidden: 'FieldHidden',
  multiselect: 'FieldSelect',
  multituple: 'FieldMultituple',
  number: 'FieldNumber',
  radio: 'FieldRadio',
  select: 'FieldSelect',
  site: 'FieldSite',
  text: 'FieldText',
  textarea: 'FieldTextarea'
};
var CONTROL_TO_AVAILABLE_OPTION_PROCESSOR = {
  FieldSelect: FieldSelectvue_type_script_lang_ts_getAvailableOptions,
  FieldCheckboxArray: processCheckboxAndRadioAvailableValues,
  FieldRadio: processCheckboxAndRadioAvailableValues,
  FieldExpandableSelect: getAvailableOptions
};
/* harmony default export */ var FormFieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    formField: {
      type: Object,
      required: true
    },
    allSettings: [Object, Array]
  },
  emits: ['update:modelValue'],
  components: {
    FieldCheckbox: FieldCheckbox,
    FieldCheckboxArray: FieldCheckboxArray,
    FieldExpandableSelect: FieldExpandableSelect,
    FieldFieldArray: FieldFieldArray,
    FieldFile: FieldFile,
    FieldHidden: FieldHidden,
    FieldMultituple: FieldMultituple,
    FieldNumber: FieldNumber,
    FieldRadio: FieldRadio,
    FieldSelect: FieldSelect,
    FieldSite: FieldSite,
    FieldText: FieldText,
    FieldTextarea: FieldTextarea,
    FieldTextareaArray: FieldTextareaArray
  },
  setup: function setup(props) {
    var inlineHelpNode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    var setInlineHelp = function setInlineHelp(newVal) {
      var toAppend;

      if (!newVal) {
        return;
      }

      if (typeof newVal === 'string' && newVal && newVal.indexOf('#') === 0) {
        toAppend = window.$(newVal);
      } else {
        toAppend = window.vueSanitize(newVal);
      }

      window.$(inlineHelpNode.value).html('').append(toAppend); // TODO: used to have $timeout here
    }; // TODO: test the watch changes


    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.formField.inlineHelp;
    }, setInlineHelp);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      if (props.formField.inlineHelp) {
        setInlineHelp(props.formField.inlineHelp);
      }
    });
    return {
      inlineHelp: inlineHelpNode
    };
  },
  computed: {
    childComponent: function childComponent() {
      var uiControl = this.formField.uiControl;
      var control = CONTROL_TO_COMPONENT_MAP[uiControl];

      if (TEXT_CONTROLS.indexOf(uiControl) !== -1) {
        control = 'FieldText'; // we use same template for text and password both
      }

      if (this.formField.type === 'array' && CONTROLS_SUPPORTING_ARRAY.indexOf(uiControl) !== -1) {
        control = "".concat(control, "Array");
      }

      return control;
    },
    extraChildComponentParams: function extraChildComponentParams() {
      if (this.formField.uiControl === 'multiselect') {
        return {
          multiple: true
        };
      }

      return {};
    },
    showFormHelp: function showFormHelp() {
      return this.formField.description || this.formField.inlineHelp || this.showDefaultValue;
    },
    showDefaultValue: function showDefaultValue() {
      return this.defaultValuePretty && this.formField.uiControl !== 'checkbox' && this.formField.uiControl !== 'radio';
    },
    showField: function showField() {
      if (!this.formField.condition || !this.allSettings || !Object.values(this.allSettings).length) {
        return true;
      }

      var values = {};
      Object.values(this.allSettings).forEach(function (setting) {
        if (setting.value === '0') {
          values[setting.name] = 0;
        } else {
          values[setting.name] = setting.value;
        }
      });
      return this.formField.condition(values);
    },
    processedModelValue: function processedModelValue() {
      var field = this.formField; // convert boolean values since angular 1.6 uses strict equals when determining if a model
      // value matches the ng-value of an input.

      if (field.type === 'boolean') {
        var valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0'; // for checkboxes, the value MUST be either true or faluse

        if (field.uiControl === 'checkbox') {
          return valueIsTruthy;
        }

        if (field.uiControl === 'radio') {
          return valueIsTruthy ? '1' : '0';
        }
      }

      return this.modelValue;
    },
    defaultValue: function defaultValue() {
      var defaultValue = this.formField.defaultValue;

      if (Array.isArray(defaultValue)) {
        defaultValue = defaultValue.join(',');
      }

      return defaultValue;
    },
    // TODO: availableOptions can be Arry/Object anywhere
    availableOptions: function availableOptions() {
      var childComponent = this.childComponent,
          formField = this.formField;

      if (!formField.availableValues || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]) {
        return null;
      }

      return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](formField.availableValues, formField.type, formField.uiControlAttributes);
    },
    defaultValuePretty: function defaultValuePretty() {
      var defaultValue = this.defaultValue;
      var availableOptions = this.availableOptions;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        var defaultParsed = null;

        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {// invalid JSON
        } // TODO: additional check for null + typeof !== object'


        if (defaultParsed !== null && FormFieldvue_type_script_lang_ts_typeof(defaultParsed) === 'object') {
          return '';
        }
      } // TODO: change all instanceof Array to Array.isArray


      if (!Array.isArray(availableOptions)) {
        if (Array.isArray(defaultValue)) {
          return '';
        }

        return defaultValue ? defaultValue.toString() : '';
      }

      var prettyValues = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      Object.values(availableOptions).forEach(function (value) {
        if (defaultValue.indexOf(value.key) !== -1 && typeof value.value !== 'undefined') {
          prettyValues.push(value.value);
        }
      });
      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated: function defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue



FormFieldvue_type_script_lang_ts.render = render

/* harmony default export */ var FormField = (FormFieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.adapter.ts
function FormField_adapter_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function FormField_adapter_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { FormField_adapter_ownKeys(Object(source), true).forEach(function (key) { FormField_adapter_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { FormField_adapter_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function FormField_adapter_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var FormField_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: FormField,
  scope: {
    modelValue: {
      default: function _default(scope) {
        var field = scope.piwikFormField; // vue components expect object data as input, so we parse JSON data
        // for angularjs directives that use JSON.

        if (typeof field.value === 'string' && field.value && (field.type === 'array' || field.uiControl === 'multituple' || field.uiControl === 'field-array' || field.uiControl === 'multiselect' || field.uiControl === 'site')) {
          field.value = JSON.parse(field.value);
        }

        return field.value;
      }
    },
    piwikFormField: {
      vue: 'formField',
      angularJsBind: '=',
      transform: function transform(value, vm, scope) {
        return FormField_adapter_objectSpread(FormField_adapter_objectSpread({}, value), {}, {
          condition: value.condition ? function (values) {
            return scope.$eval(value.condition, values);
          } : value.condition,
          disabled: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.disabled),
          autocomplete: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.autocomplete),
          autofocus: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.autofocus),
          tabindex: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.tabindex),
          fullWidth: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.fullWidth),
          maxlength: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.maxlength),
          required: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.required),
          rows: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.rows),
          min: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.min),
          max: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.max)
        });
      }
    },
    allSettings: {
      angularJsBind: '='
    }
  },
  directiveName: 'piwikFormField',
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, controller, $timeout) {
      if (newValue !== scope.piwikFormField.value) {
        $timeout(function () {
          scope.piwikFormField.value = newValue;
        });
      }
    }
  },
  $inject: ['$timeout'],
  postCreate: function postCreate(vm, scope) {
    scope.$watch('piwikFormField.value', function (newVal, oldVal) {
      if (newVal !== oldVal) {
        vm.modelValue = newVal;
      }
    });
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=3d71025b

function Fieldvue_type_template_id_3d71025b_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FormField, {
    "form-field": _ctx.field,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 8, ["form-field", "model-value"]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=3d71025b

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts
function Fieldvue_type_script_lang_ts_ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }

function Fieldvue_type_script_lang_ts_objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { Fieldvue_type_script_lang_ts_ownKeys(Object(source), true).forEach(function (key) { Fieldvue_type_script_lang_ts_defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { Fieldvue_type_script_lang_ts_ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function Fieldvue_type_script_lang_ts_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }



var UI_CONTROLS_TO_TYPE = {
  multiselect: 'array',
  checkbox: 'boolean',
  site: 'object',
  number: 'integer'
};
/* harmony default export */ var Fieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    uicontrol: String,
    name: String,
    defaultValue: null,
    options: [Object, Array],
    description: String,
    introduction: String,
    title: String,
    inlineHelp: String,
    disabled: Boolean,
    uiControlAttributes: {
      type: Object,
      default: function _default() {
        return {};
      }
    },
    uiControlOptions: {
      type: Object,
      default: function _default() {
        return {};
      }
    },
    autocomplete: Boolean,
    condition: Function,
    varType: String,
    autofocus: Boolean,
    tabindex: Number,
    fullWidth: Boolean,
    maxlength: Number,
    required: Boolean,
    placeholder: String,
    rows: Number,
    min: Number,
    max: Number
  },
  emits: ['update:modelValue'],
  components: {
    FormField: FormField
  },
  computed: {
    type: function type() {
      if (this.varType) {
        return this.varType;
      }

      if (UI_CONTROLS_TO_TYPE[this.uicontrol]) {
        return UI_CONTROLS_TO_TYPE[this.uicontrol];
      }

      return 'string';
    },
    field: function field() {
      return {
        uiControl: this.uicontrol,
        type: this.type,
        name: this.name,
        defaultValue: this.defaultValue,
        availableValues: this.options,
        description: this.description,
        introduction: this.introduction,
        inlineHelp: this.inlineHelp,
        title: this.title,
        uiControlAttributes: Fieldvue_type_script_lang_ts_objectSpread(Fieldvue_type_script_lang_ts_objectSpread({}, this.uiControlAttributes), {}, {
          disabled: this.disabled,
          autocomplete: this.autocomplete,
          tabindex: this.tabindex,
          autofocus: this.autofocus,
          rows: this.rows,
          required: this.required,
          maxlength: this.maxlength,
          placeholder: this.placeholder,
          min: this.min,
          max: this.max
        }),
        fullWidth: this.fullWidth,
        uiControlOptions: this.uiControlOptions
      };
    }
  },
  methods: {
    onChange: function onChange(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue



Fieldvue_type_script_lang_ts.render = Fieldvue_type_template_id_3d71025b_render

/* harmony default export */ var Field = (Fieldvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




function handleJsonValue(value, varType, uiControl) {
  if (typeof value === 'string' && value && (varType === 'array' || uiControl === 'multituple' || uiControl === 'field-array' || uiControl === 'multiselect' || uiControl === 'site')) {
    return JSON.parse(value);
  }

  return value;
}

/* harmony default export */ var Field_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: Field,
  require: '?ngModel',
  scope: {
    uicontrol: {
      angularJsBind: '@'
    },
    name: {
      angularJsBind: '@'
    },
    value: {
      vue: 'modelValue',
      angularJsBind: '@',
      transform: function transform(value, vm, scope) {
        // vue components expect object data as input, so we parse JSON data
        // for angularjs directives that use JSON.
        return handleJsonValue(value, scope.varType, scope.uicontrol);
      }
    },
    default: {
      vue: 'defaultValue',
      angularJsBind: '@'
    },
    options: {
      angularJsBind: '='
    },
    description: {
      angularJsBind: '@'
    },
    introduction: {
      angularJsBind: '@'
    },
    title: {
      angularJsBind: '@'
    },
    inlineHelp: {
      angularJsBind: '@'
    },
    disabled: {
      angularJsBind: '=',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    uiControlAttributes: {
      angularJsBind: '='
    },
    uiControlOptions: {
      angularJsBind: '='
    },
    autocomplete: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    condition: {
      angularJsBind: '@',
      transform: function transform(value, vm, scope) {
        var transformed = value;

        if (value) {
          transformed = function transformed(values) {
            return scope.$eval(value, values);
          };
        }

        return transformed;
      }
    },
    varType: {
      angularJsBind: '@'
    },
    autofocus: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    tabindex: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    fullWidth: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    maxlength: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    required: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsBoolAttr"]
    },
    placeholder: {
      angularJsBind: '@'
    },
    rows: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    min: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    },
    max: {
      angularJsBind: '@',
      transform: external_CoreHome_["transformAngularJsIntAttr"]
    }
  },
  directiveName: 'piwikField',
  $inject: ['$timeout'],
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel) {
      if (newValue !== scope.value) {
        scope.value = newValue;

        if (ngModel) {
          ngModel.$setViewValue(scope.value);
        }
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller;
    scope.$watch('value', function (newVal, oldVal) {
      if (newVal !== vm.modelValue) {
        var transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
        vm.modelValue = transformed;

        if (newVal === oldVal && ngModel) {
          // first update
          ngModel.$setViewValue(transformed);
        }
      }
    });

    if (typeof scope.value !== 'undefined') {
      var transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
      vm.modelValue = JSON.parse(JSON.stringify(transformed));
    }

    if (ngModel) {
      ngModel.$render = function () {
        Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
          vm.modelValue = Object(external_CoreHome_["processScopeProperty"])(ngModel.$viewValue);
        });
      };

      ngModel.$setViewValue(vm.modelValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/index.ts
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
//# sourceMappingURL=CorePluginsAdmin.umd.js.map