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
__webpack_require__.d(__webpack_exports__, "PluginSettings", function() { return /* reexport */ PluginSettings; });
__webpack_require__.d(__webpack_exports__, "PluginFilter", function() { return /* reexport */ PluginFilter; });
__webpack_require__.d(__webpack_exports__, "PluginManagement", function() { return /* reexport */ PluginManagement; });
__webpack_require__.d(__webpack_exports__, "PluginUpload", function() { return /* reexport */ PluginUpload; });
__webpack_require__.d(__webpack_exports__, "SaveButton", function() { return /* reexport */ SaveButton; });
__webpack_require__.d(__webpack_exports__, "Form", function() { return /* reexport */ Form; });
__webpack_require__.d(__webpack_exports__, "GroupedSettings", function() { return /* reexport */ GroupedSettings; });
__webpack_require__.d(__webpack_exports__, "PasswordConfirmation", function() { return /* reexport */ PasswordConfirmation; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=d6991e4e

var _hoisted_1 = {
  class: "form-group row matomo-form-field"
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
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.childComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])(Object.assign(Object.assign({
    formField: _ctx.formField
  }, _ctx.formField), {}, {
    modelValue: _ctx.processedModelValue,
    modelModifiers: _ctx.modelModifiers,
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
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formField.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.formField.description]]), _ctx.formField.inlineHelp || _ctx.hasInlineHelpSlot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_4, [_ctx.inlineHelpComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.inlineHelpComponent), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeProps"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    key: 0
  }, _ctx.inlineHelpBind)), null, 16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Default')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.defaultValuePrettyTruncated), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showDefaultValue]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showField]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=template&id=d6991e4e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=2988a0eb

var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_1 = {
  class: "checkbox"
};
var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_2 = ["checked", "id", "name"];
var FieldCheckboxvue_type_template_id_2988a0eb_hoisted_3 = ["innerHTML"];
function FieldCheckboxvue_type_template_id_2988a0eb_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxvue_type_template_id_2988a0eb_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes, {
    value: 1,
    checked: _ctx.isChecked,
    type: "checkbox",
    id: _ctx.name,
    name: _ctx.name
  }), null, 16, FieldCheckboxvue_type_template_id_2988a0eb_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldCheckboxvue_type_template_id_2988a0eb_hoisted_3)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=template&id=2988a0eb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldCheckboxvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: [Boolean, Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object,
    name: String,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var newValue = event.target.checked;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            event.target.checked = !newValue;
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    isChecked: function isChecked() {
      return !!this.modelValue && this.modelValue !== '0';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckbox.vue



FieldCheckboxvue_type_script_lang_ts.render = FieldCheckboxvue_type_template_id_2988a0eb_render

/* harmony default export */ var FieldCheckbox = (FieldCheckboxvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=23eb5e5a

var FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_1 = {
  ref: "root"
};
var FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_2 = ["value", "checked", "onChange", "id", "name"];
function FieldCheckboxArrayvue_type_template_id_23eb5e5a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
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
    }), null, 16, FieldCheckboxArrayvue_type_template_id_23eb5e5a_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(checkboxModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], checkboxModel.description]])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=template&id=23eb5e5a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }



function getCheckboxStates(availableOptions, modelValue) {
  return (availableOptions || []).map(function (o) {
    return modelValue && modelValue.indexOf(o.key) !== -1;
  });
}

/* harmony default export */ var FieldCheckboxArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    modelModifiers: Object,
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
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  methods: {
    onChange: function onChange(changedIndex) {
      var _this$modelModifiers,
          _this = this;

      var checkboxStates = _toConsumableArray(this.checkboxStates);

      checkboxStates[changedIndex] = !checkboxStates[changedIndex];
      var availableOptions = this.availableOptions || {};
      var newValue = [];
      Object.values(availableOptions).forEach(function (option, index) {
        if (checkboxStates[index]) {
          newValue.push(option.key);
        }
      });

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          // undo checked changes since we want the parent component to decide if it should go
          // through
          var item = _this.$refs.root.querySelectorAll('input').item(changedIndex);

          item.checked = !item.checked;
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldCheckboxArray.vue



FieldCheckboxArrayvue_type_script_lang_ts.render = FieldCheckboxArrayvue_type_template_id_23eb5e5a_render

/* harmony default export */ var FieldCheckboxArray = (FieldCheckboxArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=788961ef

var FieldExpandableSelectvue_type_template_id_788961ef_hoisted_1 = {
  class: "expandableSelector"
};

var FieldExpandableSelectvue_type_template_id_788961ef_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("svg", {
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

var FieldExpandableSelectvue_type_template_id_788961ef_hoisted_3 = ["value"];
var FieldExpandableSelectvue_type_template_id_788961ef_hoisted_4 = {
  class: "expandableList z-depth-2"
};
var FieldExpandableSelectvue_type_template_id_788961ef_hoisted_5 = {
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
function FieldExpandableSelectvue_type_template_id_788961ef_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _directive_focus_if = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-if");

  var _directive_focus_anywhere_but_here = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("focus-anywhere-but-here");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldExpandableSelectvue_type_template_id_788961ef_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showSelect = !_ctx.showSelect;
    }),
    class: "select-wrapper"
  }, [FieldExpandableSelectvue_type_template_id_788961ef_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    class: "select-dropdown",
    readonly: "readonly",
    value: _ctx.modelValueText
  }, null, 8, FieldExpandableSelectvue_type_template_id_788961ef_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_788961ef_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldExpandableSelectvue_type_template_id_788961ef_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    placeholder: "Search",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.searchTerm = $event;
    }),
    class: "expandableSearch browser-default"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.searchTerm], [_directive_focus_if, {
    focused: _ctx.showSelect
  }]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions, function (options, index) {
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
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
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
      return x.value.toLowerCase().indexOf(_ctx.searchTerm.toLowerCase()) !== -1;
    }).length]]);
  }), 128))])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSelect]])], 512)), [[_directive_focus_anywhere_but_here, {
    blur: _ctx.onBlur
  }]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=template&id=788961ef

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts


function getAvailableOptions(availableValues) {
  var flatValues = [];

  if (!availableValues) {
    return flatValues;
  }

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
    modelModifiers: Object,
    availableOptions: Array,
    title: String
  },
  directives: {
    FocusAnywhereButHere: external_CoreHome_["FocusAnywhereButHere"],
    FocusIf: external_CoreHome_["FocusIf"]
  },
  inheritAttrs: false,
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
      if (this.title) {
        return this.title;
      }

      var key = this.modelValue;
      var availableOptions = this.availableOptions || [];
      var keyItem;
      availableOptions.some(function (option) {
        keyItem = option.values.find(function (item) {
          return item.key === key;
        });
        return keyItem; // stop iterating if found
      });

      if (keyItem) {
        return keyItem.value ? "".concat(keyItem.value) : '';
      }

      return key ? "".concat(key) : '';
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
      var _this$modelModifiers;

      this.showSelect = false;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', selectedValue.key);
        return;
      }

      var emitEventData = {
        value: selectedValue.key,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.vue



FieldExpandableSelectvue_type_script_lang_ts.render = FieldExpandableSelectvue_type_template_id_788961ef_render

/* harmony default export */ var FieldExpandableSelect = (FieldExpandableSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=58341ca6

var FieldFieldArrayvue_type_template_id_58341ca6_hoisted_1 = ["for", "innerHTML"];
function FieldFieldArrayvue_type_template_id_58341ca6_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FieldArray = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FieldArray");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFieldArrayvue_type_template_id_58341ca6_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FieldArray, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onValueUpdate($event);
    }),
    "model-modifiers": _ctx.modelModifiers,
    field: _ctx.uiControlAttributes.field
  }, null, 8, ["name", "model-value", "model-modifiers", "field"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=template&id=58341ca6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFieldArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldFieldArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FieldArray: external_CoreHome_["FieldArray"]
  },
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
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



FieldFieldArrayvue_type_script_lang_ts.render = FieldFieldArrayvue_type_template_id_58341ca6_render

/* harmony default export */ var FieldFieldArray = (FieldFieldArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2903f7cf

var FieldFilevue_type_template_id_2903f7cf_hoisted_1 = {
  class: "btn"
};
var FieldFilevue_type_template_id_2903f7cf_hoisted_2 = ["for", "innerHTML"];
var FieldFilevue_type_template_id_2903f7cf_hoisted_3 = ["name", "id"];
var FieldFilevue_type_template_id_2903f7cf_hoisted_4 = {
  class: "file-path-wrapper"
};
var FieldFilevue_type_template_id_2903f7cf_hoisted_5 = ["value"];
function FieldFilevue_type_template_id_2903f7cf_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2903f7cf_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldFilevue_type_template_id_2903f7cf_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    ref: "fileInput",
    name: _ctx.name,
    type: "file",
    id: _ctx.name,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldFilevue_type_template_id_2903f7cf_hoisted_3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldFilevue_type_template_id_2903f7cf_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    class: "file-path validate",
    value: _ctx.filePath,
    type: "text"
  }, null, 8, FieldFilevue_type_template_id_2903f7cf_hoisted_5)])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=template&id=2903f7cf

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldFilevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: [String, File],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  watch: {
    modelValue: function modelValue(v) {
      if (!v || v === '') {
        var fileInputElement = this.$refs.fileInput;
        fileInputElement.value = '';
      }
    }
  },
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers;

      var files = event.target.files;

      if (!files) {
        return;
      }

      var file = files.item(0);

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', file);
        return;
      }

      var emitEventData = {
        value: file,
        abort: function abort() {// not supported
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  },
  computed: {
    filePath: function filePath() {
      if (this.modelValue instanceof File) {
        return this.$refs.fileInput.value;
      }

      return undefined;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldFile.vue



FieldFilevue_type_script_lang_ts.render = FieldFilevue_type_template_id_2903f7cf_render

/* harmony default export */ var FieldFile = (FieldFilevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=1cc21994

var FieldHiddenvue_type_template_id_1cc21994_hoisted_1 = ["type", "name", "value"];
function FieldHiddenvue_type_template_id_1cc21994_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: _ctx.uiControl,
    name: _ctx.name,
    value: _ctx.modelValue,
    onChange: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    })
  }, null, 40, FieldHiddenvue_type_template_id_1cc21994_hoisted_1)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=template&id=1cc21994

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldHidden.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldHiddenvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
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



FieldHiddenvue_type_script_lang_ts.render = FieldHiddenvue_type_template_id_1cc21994_render

/* harmony default export */ var FieldHidden = (FieldHiddenvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=6abda7e4

var FieldMultituplevue_type_template_id_6abda7e4_hoisted_1 = {
  class: "fieldMultiTuple"
};
var FieldMultituplevue_type_template_id_6abda7e4_hoisted_2 = ["for", "innerHTML"];
function FieldMultituplevue_type_template_id_6abda7e4_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_MultiPairField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MultiPairField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldMultituplevue_type_template_id_6abda7e4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldMultituplevue_type_template_id_6abda7e4_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MultiPairField, {
    name: _ctx.name,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _ctx.onUpdateValue,
    "model-modifiers": _ctx.modelModifiers,
    field1: _ctx.uiControlAttributes.field1,
    field2: _ctx.uiControlAttributes.field2,
    field3: _ctx.uiControlAttributes.field3,
    field4: _ctx.uiControlAttributes.field4
  }, null, 8, ["name", "model-value", "onUpdate:modelValue", "model-modifiers", "field1", "field2", "field3", "field4"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=template&id=6abda7e4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldMultituplevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: null,
    modelModifiers: Object,
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



FieldMultituplevue_type_script_lang_ts.render = FieldMultituplevue_type_template_id_6abda7e4_render

/* harmony default export */ var FieldMultituple = (FieldMultituplevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=91fe4a1c

var FieldNumbervue_type_template_id_91fe4a1c_hoisted_1 = ["type", "id", "name", "value"];
var FieldNumbervue_type_template_id_91fe4a1c_hoisted_2 = ["for", "innerHTML"];
function FieldNumbervue_type_template_id_91fe4a1c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueFormatted,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onChange($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldNumbervue_type_template_id_91fe4a1c_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldNumbervue_type_template_id_91fe4a1c_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=template&id=91fe4a1c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldNumbervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    uiControl: String,
    name: String,
    title: String,
    modelValue: [Number, String],
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onChange = Object(external_CoreHome_["debounce"])(this.onChange.bind(this), 50);
  },
  methods: {
    onChange: function onChange(event) {
      var _this = this;

      var value = parseFloat(event.target.value);

      if (value !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.modelValueFormatted) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.modelValueFormatted;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  mounted: function mounted() {
    setTimeout(function () {
      window.Materialize.updateTextFields();
    });
  },
  watch: {
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  computed: {
    modelValueFormatted: function modelValueFormatted() {
      return (this.modelValue || '').toString();
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldNumber.vue



FieldNumbervue_type_script_lang_ts.render = FieldNumbervue_type_template_id_91fe4a1c_render

/* harmony default export */ var FieldNumber = (FieldNumbervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=5ab171cb

var FieldRadiovue_type_template_id_5ab171cb_hoisted_1 = {
  ref: "root"
};
var FieldRadiovue_type_template_id_5ab171cb_hoisted_2 = ["value", "id", "name", "disabled", "checked"];
function FieldRadiovue_type_template_id_5ab171cb_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldRadiovue_type_template_id_5ab171cb_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fieldRadioTitle"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.title]]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.availableOptions || [], function (radioModel) {
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
      checked: _ctx.modelValue === radioModel.key || "".concat(_ctx.modelValue) === radioModel.key
    }), null, 16, FieldRadiovue_type_template_id_5ab171cb_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.value) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "form-description"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(radioModel.description), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], radioModel.description]])])])]);
  }), 128))], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=template&id=5ab171cb

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts

/* harmony default export */ var FieldRadiovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    availableOptions: Array,
    name: String,
    disabled: Boolean,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(event) {
      var _this$modelModifiers,
          _this = this;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', event.target.value);
        return;
      }

      var reset = function reset() {
        // change to previous value so the parent component can determine if this change should
        // go through
        _this.$refs.root.querySelectorAll('input').forEach(function (inp, i) {
          var _this$availableOption;

          if (!((_this$availableOption = _this.availableOptions) !== null && _this$availableOption !== void 0 && _this$availableOption[i])) {
            return;
          }

          var key = _this.availableOptions[i].key;
          inp.checked = _this.modelValue === key || "".concat(_this.modelValue) === key;
        });
      };

      var emitEventData = {
        value: event.target.value,
        abort: function abort() {
          reset();
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldRadio.vue



FieldRadiovue_type_script_lang_ts.render = FieldRadiovue_type_template_id_5ab171cb_render

/* harmony default export */ var FieldRadio = (FieldRadiovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32fc626c
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || FieldSelectvue_type_template_id_32fc626c_unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function FieldSelectvue_type_template_id_32fc626c_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(o, minLen); }

function FieldSelectvue_type_template_id_32fc626c_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }


var FieldSelectvue_type_template_id_32fc626c_hoisted_1 = {
  key: 0,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_32fc626c_hoisted_2 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_3 = ["label"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_4 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_5 = ["for", "innerHTML"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_6 = {
  key: 1,
  class: "matomo-field-select"
};
var FieldSelectvue_type_template_id_32fc626c_hoisted_7 = ["multiple", "name"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_8 = ["value", "selected", "disabled"];
var FieldSelectvue_type_template_id_32fc626c_hoisted_9 = ["for", "innerHTML"];
function FieldSelectvue_type_template_id_32fc626c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [_ctx.groupedOptions ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32fc626c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
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
        selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
        disabled: option.disabled
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32fc626c_hoisted_4);
    }), 128))], 8, FieldSelectvue_type_template_id_32fc626c_hoisted_3);
  }), 128))], 16, FieldSelectvue_type_template_id_32fc626c_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_32fc626c_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.groupedOptions && _ctx.options ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldSelectvue_type_template_id_32fc626c_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
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
      selected: _ctx.multiple ? _ctx.modelValue && _ctx.modelValue.indexOf(option.key) !== -1 : _ctx.modelValue === option.key,
      disabled: option.disabled
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.value), 9, FieldSelectvue_type_template_id_32fc626c_hoisted_8);
  }), 128))], 16, FieldSelectvue_type_template_id_32fc626c_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSelectvue_type_template_id_32fc626c_hoisted_9)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=template&id=32fc626c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
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



function initMaterialSelect(select, modelValue, placeholder) {
  var uiControlOptions = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
  var multiple = arguments.length > 4 ? arguments[4] : undefined;

  if (!select) {
    return;
  }

  var $select = window.$(select); // reset selected since materialize removes them

  Array.from(select.options).forEach(function (opt) {
    if (multiple) {
      opt.selected = !!modelValue && modelValue.indexOf(opt.value.replace(/^string:/, '')) !== -1;
    } else {
      opt.selected = "string:".concat(modelValue) === opt.value;
    }
  });
  $select.formSelect(uiControlOptions); // add placeholder to input

  if (placeholder) {
    var $materialInput = $select.closest('.select-wrapper').find('input');
    $materialInput.attr('placeholder', placeholder);
  }
}

function hasGroupedValues(availableValues) {
  if (Array.isArray(availableValues) || !(_typeof(availableValues) === 'object')) {
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

  var hasGroups = true;
  var availableValues = givenAvailableValues;

  if (!hasGroupedValues(availableValues)) {
    availableValues = {
      '': givenAvailableValues
    };
    hasGroups = false;
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

      if (value && _typeof(value) === 'object' && typeof value.key !== 'undefined') {
        flatValues.push(value);
        return;
      }

      var key = valueObjKey;

      if (type === 'integer' && typeof valueObjKey === 'string') {
        key = parseInt(valueObjKey, 10);
      }

      flatValues.push({
        group: hasGroups ? group : undefined,
        key: key,
        value: value
      });
    });
  }); // for selects w/ a placeholder, add an option to unset the select

  if (uiControlAttributes !== null && uiControlAttributes !== void 0 && uiControlAttributes.placeholder && !hasOption(flatValues, '')) {
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
    modelModifiers: Object,
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
      var availableOptions = this.availableOptions;

      if (availableOptions && !hasOption(availableOptions, '') && (typeof this.modelValue === 'undefined' || this.modelValue === null || this.modelValue === '')) {
        return [{
          key: '',
          value: this.modelValue,
          group: this.hasGroups ? '' : undefined
        }].concat(FieldSelectvue_type_script_lang_ts_toConsumableArray(availableOptions));
      }

      return availableOptions;
    },
    hasGroups: function hasGroups() {
      var availableOptions = this.availableOptions;
      return availableOptions && availableOptions[0] && typeof availableOptions[0].group !== 'undefined';
    },
    groupedOptions: function groupedOptions() {
      var options = this.options;

      if (!this.hasGroups || !options) {
        return null;
      }

      var groups = {};
      options.forEach(function (entry) {
        var group = entry.group;
        groups[group] = groups[group] || [];
        groups[group].push(entry);
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
      var _this$modelModifiers,
          _this = this;

      var element = event.target;
      var newValue;

      if (this.multiple) {
        newValue = Array.from(element.options).filter(function (e) {
          return e.selected;
        }).map(function (e) {
          return e.value;
        });
        newValue = newValue.map(function (x) {
          return handleOldAngularJsValues(x);
        });
      } else {
        newValue = element.value;
        newValue = handleOldAngularJsValues(newValue);
      }

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {
          _this.onModelValueChange(_this.modelValue);
        }
      };
      this.$emit('update:modelValue', emitEventData);
    },
    onModelValueChange: function onModelValueChange(newVal) {
      var _this2 = this;

      window.$(this.$refs.select).val(newVal);
      setTimeout(function () {
        var _this2$uiControlAttri;

        initMaterialSelect(_this2.$refs.select, newVal, (_this2$uiControlAttri = _this2.uiControlAttributes) === null || _this2$uiControlAttri === void 0 ? void 0 : _this2$uiControlAttri.placeholder, _this2.uiControlOptions, _this2.multiple);
      });
    }
  },
  watch: {
    modelValue: function modelValue(newVal) {
      this.onModelValueChange(newVal);
    },
    'uiControlAttributes.disabled': {
      handler: function handler(newVal, oldVal) {
        var _this3 = this;

        setTimeout(function () {
          if (newVal !== oldVal) {
            var _this3$uiControlAttri;

            initMaterialSelect(_this3.$refs.select, _this3.modelValue, (_this3$uiControlAttri = _this3.uiControlAttributes) === null || _this3$uiControlAttri === void 0 ? void 0 : _this3$uiControlAttri.placeholder, _this3.uiControlOptions, _this3.multiple);
          }
        });
      }
    },
    availableOptions: function availableOptions(newVal, oldVal) {
      var _this4 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          var _this4$uiControlAttri;

          initMaterialSelect(_this4.$refs.select, _this4.modelValue, (_this4$uiControlAttri = _this4.uiControlAttributes) === null || _this4$uiControlAttri === void 0 ? void 0 : _this4$uiControlAttri.placeholder, _this4.uiControlOptions, _this4.multiple);
        });
      }
    }
  },
  mounted: function mounted() {
    var _this5 = this;

    setTimeout(function () {
      var _this5$uiControlAttri;

      initMaterialSelect(_this5.$refs.select, _this5.modelValue, (_this5$uiControlAttri = _this5.uiControlAttributes) === null || _this5$uiControlAttri === void 0 ? void 0 : _this5$uiControlAttri.placeholder, _this5.uiControlOptions, _this5.multiple);
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.vue



FieldSelectvue_type_script_lang_ts.render = FieldSelectvue_type_template_id_32fc626c_render

/* harmony default export */ var FieldSelect = (FieldSelectvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=4680911e

var FieldSitevue_type_template_id_4680911e_hoisted_1 = ["for", "innerHTML"];
var FieldSitevue_type_template_id_4680911e_hoisted_2 = {
  class: "sites_autocomplete"
};
function FieldSitevue_type_template_id_4680911e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    class: "siteSelectorLabel",
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldSitevue_type_template_id_4680911e_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FieldSitevue_type_template_id_4680911e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
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
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=template&id=4680911e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldSitevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    modelValue: Object,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  components: {
    SiteSelector: external_CoreHome_["SiteSelector"]
  },
  emits: ['update:modelValue'],
  methods: {
    onChange: function onChange(newValue) {
      var _this$modelModifiers;

      if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
        this.$emit('update:modelValue', newValue);
        return;
      }

      var emitEventData = {
        value: newValue,
        abort: function abort() {// empty (not necessary to reset anything since the DOM will not change for this UI
          // element until modelValue does)
        }
      };
      this.$emit('update:modelValue', emitEventData);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldSite.vue



FieldSitevue_type_script_lang_ts.render = FieldSitevue_type_template_id_4680911e_render

/* harmony default export */ var FieldSite = (FieldSitevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=518c87fe

var FieldTextvue_type_template_id_518c87fe_hoisted_1 = ["type", "id", "name", "value"];
var FieldTextvue_type_template_id_518c87fe_hoisted_2 = ["for", "innerHTML"];
function FieldTextvue_type_template_id_518c87fe_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    id: _ctx.name,
    name: _ctx.name,
    value: _ctx.modelValueText,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    })
  }, _ctx.uiControlAttributes), null, 16, FieldTextvue_type_template_id_518c87fe_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextvue_type_template_id_518c87fe_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=template&id=518c87fe

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String,
    name: String,
    uiControlAttributes: Object,
    modelValue: [String, Number],
    modelModifiers: Object,
    uiControl: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    modelValueText: function modelValueText() {
      if (typeof this.modelValue === 'undefined' || this.modelValue === null) {
        return '';
      }

      return this.modelValue.toString();
    }
  },
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
    modelValue: function modelValue() {
      setTimeout(function () {
        window.Materialize.updateTextFields();
      });
    }
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (this.modelValue !== newValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            // change to previous value if the parent component did not update the model value
            // (done manually because Vue will not notice if a value does NOT change)
            if (event.target.value !== _this.modelValueText) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldText.vue



FieldTextvue_type_script_lang_ts.render = FieldTextvue_type_template_id_518c87fe_render

/* harmony default export */ var FieldText = (FieldTextvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=72853163

var FieldTextArrayvue_type_template_id_72853163_hoisted_1 = ["for", "innerHTML"];
var FieldTextArrayvue_type_template_id_72853163_hoisted_2 = ["type", "name", "value"];
function FieldTextArrayvue_type_template_id_72853163_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextArrayvue_type_template_id_72853163_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "control_".concat(_ctx.uiControl),
    type: _ctx.uiControl,
    name: _ctx.name,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    value: _ctx.concattedValues
  }, _ctx.uiControlAttributes), null, 16, FieldTextArrayvue_type_template_id_72853163_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=template&id=72853163

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControl: String,
    modelValue: Array,
    modelModifiers: Object,
    uiControlAttributes: Object
  },
  inheritAttrs: false,
  computed: {
    concattedValues: function concattedValues() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      }

      return (this.modelValue || []).join(', ');
    }
  },
  emits: ['update:modelValue'],
  created: function created() {
    // debounce because puppeteer types reeaally fast
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var values = event.target.value.split(',').map(function (v) {
        return v.trim();
      });

      if (values.join(', ') !== this.concattedValues) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', values);
          return;
        }

        var emitEventData = {
          value: values,
          abort: function abort() {
            if (event.target.value !== _this.concattedValues) {
              event.target.value = _this.concattedValues;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextArray.vue



FieldTextArrayvue_type_script_lang_ts.render = FieldTextArrayvue_type_template_id_72853163_render

/* harmony default export */ var FieldTextArray = (FieldTextArrayvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=f0327bcc

var FieldTextareavue_type_template_id_f0327bcc_hoisted_1 = ["name", "id", "value"];
var FieldTextareavue_type_template_id_f0327bcc_hoisted_2 = ["for", "innerHTML"];
function FieldTextareavue_type_template_id_f0327bcc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    id: _ctx.name,
    value: _ctx.modelValueText,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea",
    ref: "textarea"
  }), null, 16, FieldTextareavue_type_template_id_f0327bcc_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareavue_type_template_id_f0327bcc_hoisted_2)], 64);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=template&id=f0327bcc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts


/* harmony default export */ var FieldTextareavue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    uiControlAttributes: Object,
    modelValue: String,
    modelModifiers: Object,
    title: String
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var newValue = event.target.value;

      if (newValue !== this.modelValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', newValue);
          return;
        }

        var emitEventData = {
          value: newValue,
          abort: function abort() {
            if (event.target.value !== _this.modelValue) {
              event.target.value = _this.modelValueText;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  computed: {
    modelValueText: function modelValueText() {
      return this.modelValue || '';
    }
  },
  watch: {
    modelValue: function modelValue() {
      var _this2 = this;

      setTimeout(function () {
        window.Materialize.textareaAutoResize(_this2.$refs.textarea);
        window.Materialize.updateTextFields();
      });
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextarea.vue



FieldTextareavue_type_script_lang_ts.render = FieldTextareavue_type_template_id_f0327bcc_render

/* harmony default export */ var FieldTextarea = (FieldTextareavue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=77717d95

var FieldTextareaArrayvue_type_template_id_77717d95_hoisted_1 = ["for", "innerHTML"];
var FieldTextareaArrayvue_type_template_id_77717d95_hoisted_2 = ["name", "value"];
function FieldTextareaArrayvue_type_template_id_77717d95_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    for: _ctx.name,
    innerHTML: _ctx.$sanitize(_ctx.title)
  }, null, 8, FieldTextareaArrayvue_type_template_id_77717d95_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    ref: "textarea",
    name: _ctx.name
  }, _ctx.uiControlAttributes, {
    value: _ctx.concattedValue,
    onKeydown: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    onChange: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.onKeydown($event);
    }),
    class: "materialize-textarea"
  }), null, 16, FieldTextareaArrayvue_type_template_id_77717d95_hoisted_2)]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=template&id=77717d95

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
function FieldTextareaArrayvue_type_script_lang_ts_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return typeof obj; }; } else { FieldTextareaArrayvue_type_script_lang_ts_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return FieldTextareaArrayvue_type_script_lang_ts_typeof(obj); }



var SEPARATOR = '\n';
/* harmony default export */ var FieldTextareaArrayvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    name: String,
    title: String,
    uiControlAttributes: Object,
    modelValue: [Array, String],
    modelModifiers: Object
  },
  inheritAttrs: false,
  emits: ['update:modelValue'],
  computed: {
    concattedValue: function concattedValue() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      } // Handle case when modelValues is like: {"0": "value0", "2": "value1"}


      if (FieldTextareaArrayvue_type_script_lang_ts_typeof(this.modelValue) === 'object') {
        return Object.values(this.modelValue).join(SEPARATOR);
      }

      try {
        return (this.modelValue || []).join(SEPARATOR);
      } catch (e) {
        // Prevent page breaking on unexpected modelValue type
        console.error(e);
        return '';
      }
    }
  },
  created: function created() {
    this.onKeydown = Object(external_CoreHome_["debounce"])(this.onKeydown.bind(this), 50);
  },
  methods: {
    onKeydown: function onKeydown(event) {
      var _this = this;

      var value = event.target.value.split(SEPARATOR);

      if (value.join(SEPARATOR) !== this.concattedValue) {
        var _this$modelModifiers;

        if (!((_this$modelModifiers = this.modelModifiers) !== null && _this$modelModifiers !== void 0 && _this$modelModifiers.abortable)) {
          this.$emit('update:modelValue', value);
          return;
        }

        var emitEventData = {
          value: value,
          abort: function abort() {
            if (event.target.value !== _this.concattedValue) {
              // change to previous value if the parent component did not update the model value
              // (done manually because Vue will not notice if a value does NOT change)
              event.target.value = _this.concattedValue;
            }
          }
        };
        this.$emit('update:modelValue', emitEventData);
      }
    }
  },
  watch: {
    modelValue: function modelValue(newVal, oldVal) {
      var _this2 = this;

      if (newVal !== oldVal) {
        setTimeout(function () {
          if (_this2.$refs.textarea) {
            window.Materialize.textareaAutoResize(_this2.$refs.textarea);
          }

          window.Materialize.updateTextFields();
        });
      }
    }
  },
  mounted: function mounted() {
    var _this3 = this;

    setTimeout(function () {
      if (_this3.$refs.textarea) {
        window.Materialize.textareaAutoResize(_this3.$refs.textarea);
      }

      window.Materialize.updateTextFields();
    });
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldTextareaArray.vue



FieldTextareaArrayvue_type_script_lang_ts.render = FieldTextareaArrayvue_type_template_id_77717d95_render

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
  }

  var flatValues = [];
  Object.entries(availableValues).forEach(function (_ref) {
    var _ref2 = utilities_slicedToArray(_ref, 2),
        valueObjKey = _ref2[0],
        value = _ref2[1];

    if (value && utilities_typeof(value) === 'object' && typeof value.key !== 'undefined') {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldAngularJsTemplate.vue?vue&type=template&id=3f63d972

var FieldAngularJsTemplatevue_type_template_id_3f63d972_hoisted_1 = {
  ref: "root"
};
function FieldAngularJsTemplatevue_type_template_id_3f63d972_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FieldAngularJsTemplatevue_type_template_id_3f63d972_hoisted_1, null, 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldAngularJsTemplate.vue?vue&type=template&id=3f63d972

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FieldAngularJsTemplate.vue?vue&type=script&lang=ts



function clone(obj) {
  if (typeof obj === 'undefined') {
    return undefined;
  }

  return JSON.parse(JSON.stringify(obj));
}

/* harmony default export */ var FieldAngularJsTemplatevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    formField: {
      type: null,
      required: true
    },
    templateFile: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue'],
  inheritAttrs: false,
  setup: function setup(props, context) {
    var root = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    var $element = window.$("<div ng-include=\"'".concat(props.templateFile, "?cb=").concat(external_CoreHome_["Matomo"].cacheBuster, "'\"></div>"));
    var $timeout = external_CoreHome_["Matomo"].helper.getAngularDependency('$timeout');
    var $rootScope = external_CoreHome_["Matomo"].helper.getAngularDependency('$rootScope');
    var scope = $rootScope.$new();
    scope.formField = Object.assign(Object.assign({}, clone(props.formField)), {}, {
      value: clone(props.modelValue)
    });
    scope.$watch('formField.value', function (newValue, oldValue) {
      if (newValue !== oldValue && JSON.stringify(newValue) !== JSON.stringify(props.modelValue)) {
        context.emit('update:modelValue', clone(newValue));
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.modelValue;
    }, function (newValue) {
      if (JSON.stringify(newValue) !== JSON.stringify(scope.formField.value)) {
        $timeout(function () {
          scope.formField.value = newValue;
        });
      }
    });
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.formField;
    }, function (newValue) {
      $timeout(function () {
        var currentValue = scope.formField.value;
        scope.formField = Object.assign(Object.assign({}, clone(newValue)), {}, {
          value: currentValue
        });
      });
    }, {
      deep: true
    }); // append on mount

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      window.$(root.value).append($element);
      external_CoreHome_["Matomo"].helper.compileAngularComponents($element, {
        scope: scope,
        params: {
          formField: Object.assign(Object.assign({}, clone(props.formField)), {}, {
            value: props.modelValue
          })
        }
      });
    });
    return {
      root: root
    };
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldAngularJsTemplate.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/FormField/FieldAngularJsTemplate.vue



FieldAngularJsTemplatevue_type_script_lang_ts.render = FieldAngularJsTemplatevue_type_template_id_3f63d972_render

/* harmony default export */ var FieldAngularJsTemplate = (FieldAngularJsTemplatevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/FormField/FormField.vue?vue&type=script&lang=ts
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
    modelModifiers: Object,
    formField: {
      type: Object,
      required: true
    }
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
    FieldTextArray: FieldTextArray,
    FieldTextarea: FieldTextarea,
    FieldTextareaArray: FieldTextareaArray
  },
  setup: function setup(props) {
    var inlineHelpNode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);

    var setInlineHelp = function setInlineHelp(newVal) {
      var toAppend;

      if (!newVal || !inlineHelpNode.value || typeof newVal.render === 'function') {
        return;
      }

      if (typeof newVal === 'string') {
        if (newVal.indexOf('#') === 0) {
          toAppend = window.$(newVal);
        } else {
          toAppend = window.vueSanitize(newVal);
        }
      } else {
        toAppend = newVal;
      }

      window.$(inlineHelpNode.value).html('').append(toAppend);
    };

    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return props.formField.inlineHelp;
    }, setInlineHelp);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onMounted"])(function () {
      setInlineHelp(props.formField.inlineHelp);
    });
    return {
      inlineHelp: inlineHelpNode
    };
  },
  computed: {
    inlineHelpComponent: function inlineHelpComponent() {
      var formField = this.formField;
      var inlineHelpRecord = formField.inlineHelp;

      if (inlineHelpRecord && typeof inlineHelpRecord.render === 'function') {
        return formField.inlineHelp;
      }

      return undefined;
    },
    inlineHelpBind: function inlineHelpBind() {
      return this.inlineHelpComponent ? this.formField.inlineHelpBind : undefined;
    },
    childComponent: function childComponent() {
      var formField = this.formField;

      if (formField.component) {
        var component = formField.component;

        if (formField.component.plugin) {
          var _formField$component = formField.component,
              plugin = _formField$component.plugin,
              name = _formField$component.name;

          if (!plugin || !name) {
            throw new Error('Invalid component property given to piwik-field directive, must be ' + '{plugin: \'...\',name: \'...\'}');
          }

          component = Object(external_CoreHome_["useExternalPluginComponent"])(plugin, name);
        }

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(component);
      } // backwards compatibility w/ settings that use templateFile property


      if (formField.templateFile) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(FieldAngularJsTemplate);
      }

      var uiControl = formField.uiControl;
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
      return this.formField.description || this.formField.inlineHelp || this.showDefaultValue || this.hasInlineHelpSlot;
    },
    showDefaultValue: function showDefaultValue() {
      return this.defaultValuePretty && this.formField.uiControl !== 'checkbox' && this.formField.uiControl !== 'radio';
    },

    /**
     * @deprecated here for angularjs BC support. shouldn't be used directly, instead use
     *             GroupedSetting.vue.
     */
    showField: function showField() {
      if (!this.formField || !this.formField.condition || !(this.formField.condition instanceof Function)) {
        return true;
      }

      return this.formField.condition();
    },
    processedModelValue: function processedModelValue() {
      var field = this.formField; // convert boolean values since angular 1.6 uses strict equals when determining if a model
      // value matches the ng-value of an input.

      if (field.type === 'boolean') {
        var valueIsTruthy = this.modelValue && this.modelValue > 0 && this.modelValue !== '0'; // for checkboxes, the value MUST be either true or false

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
        return defaultValue.join(',');
      }

      return defaultValue;
    },
    availableOptions: function availableOptions() {
      var childComponent = this.childComponent;

      if (typeof childComponent !== 'string') {
        return null;
      }

      var formField = this.formField;

      if (!formField.availableValues || !CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent]) {
        return null;
      }

      return CONTROL_TO_AVAILABLE_OPTION_PROCESSOR[childComponent](formField.availableValues, formField.type, formField.uiControlAttributes);
    },
    defaultValuePretty: function defaultValuePretty() {
      var formField = this.formField;
      var defaultValue = formField.defaultValue;
      var availableOptions = this.availableOptions;

      if (typeof defaultValue === 'string' && defaultValue) {
        // eg default value for multi tuple
        var defaultParsed = null;

        try {
          defaultParsed = JSON.parse(defaultValue);
        } catch (e) {// invalid JSON
        }

        if (defaultParsed !== null && FormFieldvue_type_script_lang_ts_typeof(defaultParsed) === 'object') {
          return '';
        }
      }

      if (!Array.isArray(availableOptions)) {
        if (Array.isArray(defaultValue)) {
          return '';
        }

        return defaultValue ? "".concat(defaultValue) : '';
      }

      var prettyValues = [];

      if (!Array.isArray(defaultValue)) {
        defaultValue = [defaultValue];
      }

      (availableOptions || []).forEach(function (value) {
        if (typeof value.value !== 'undefined' && defaultValue.indexOf(value.key) !== -1) {
          prettyValues.push(value.value);
        }
      });
      return prettyValues.join(', ');
    },
    defaultValuePrettyTruncated: function defaultValuePrettyTruncated() {
      return this.defaultValuePretty.substring(0, 50);
    },
    hasInlineHelpSlot: function hasInlineHelpSlot() {
      var _inlineHelpSlot$, _inlineHelpSlot$$chil;

      if (!this.$slots['inline-help']) {
        return false;
      }

      var inlineHelpSlot = this.$slots['inline-help']();
      return !!(inlineHelpSlot !== null && inlineHelpSlot !== void 0 && (_inlineHelpSlot$ = inlineHelpSlot[0]) !== null && _inlineHelpSlot$ !== void 0 && (_inlineHelpSlot$$chil = _inlineHelpSlot$.children) !== null && _inlineHelpSlot$$chil !== void 0 && _inlineHelpSlot$$chil.length);
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
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





function transformVueComponentRef(value) {
  if (!value) {
    return undefined;
  }

  var plugin = value.plugin,
      name = value.name;

  if (!plugin || !name) {
    throw new Error('Invalid component property given to piwik-field directive, must be ' + '{plugin: \'...\',name: \'...\'}');
  }

  return Object(external_CoreHome_["useExternalPluginComponent"])(plugin, name);
} // eslint-disable-next-line @typescript-eslint/no-explicit-any


function conditionFn(scope, condition) {
  var values = {};
  Object.values(scope.allSettings || {}).forEach(function (setting) {
    if (setting.value === '0') {
      values[setting.name] = 0;
    } else {
      values[setting.name] = setting.value;
    }
  });
  return scope.$eval(condition, values);
}

/* harmony default export */ var FormField_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: FormField,
  scope: {
    modelValue: {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      default: function _default(scope) {
        var field = scope.piwikFormField; // vue components expect object data as input, so we parse JSON data
        // for angularjs directives that use JSON.

        if (typeof field.value === 'string' && field.value && (field.type === 'array' || field.uiControl === 'multituple' || field.uiControl === 'field-array' || field.uiControl === 'multiselect' || field.uiControl === 'site')) {
          field.value = JSON.parse(field.value);
        }

        if (field.uiControl === 'checkbox') {
          return Object(external_CoreHome_["transformAngularJsBoolAttr"])(field.value);
        }

        return field.value;
      }
    },
    piwikFormField: {
      vue: 'formField',
      angularJsBind: '=',
      transform: function transform(v, vm, scope) {
        var value = v;

        function getComponent() {
          if (value.templateFile) {
            return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(FieldAngularJsTemplate);
          }

          var comp = transformVueComponentRef(value.component);

          if (!comp) {
            return undefined;
          }

          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(comp);
        }

        return Object.assign(Object.assign({}, value), {}, {
          condition: value.condition ? conditionFn.bind(null, scope, value.condition) : value.condition,
          disabled: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.disabled),
          autocomplete: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.autocomplete),
          autofocus: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.autofocus),
          tabindex: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.tabindex),
          fullWidth: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.fullWidth),
          maxlength: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.maxlength),
          required: Object(external_CoreHome_["transformAngularJsBoolAttr"])(value.required),
          rows: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.rows),
          min: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.min),
          max: Object(external_CoreHome_["transformAngularJsIntAttr"])(value.max),
          component: getComponent()
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
    }); // deep watch for all settings, on change trigger change in formfield property
    // so condition is re-applied

    scope.$watch('allSettings', function () {
      vm.formField = Object.assign(Object.assign({}, vm.formField), {}, {
        condition: scope.piwikFormField.condition ? conditionFn.bind(null, scope, scope.piwikFormField.condition) : scope.piwikFormField.condition
      });
    }, true);
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=4b1ab6ed

function Fieldvue_type_template_id_4b1ab6ed_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FormField, {
    "form-field": _ctx.field,
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onChange($event);
    }),
    "model-modifiers": _ctx.modelModifiers
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "inline-help")];
    }),
    _: 3
  }, 8, ["form-field", "model-value", "model-modifiers"]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=template&id=4b1ab6ed

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/Field/Field.vue?vue&type=script&lang=ts


var UI_CONTROLS_TO_TYPE = {
  multiselect: 'array',
  checkbox: 'boolean',
  site: 'object',
  number: 'integer'
};
/* harmony default export */ var Fieldvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: null,
    modelModifiers: Object,
    uicontrol: String,
    name: String,
    defaultValue: null,
    options: [Object, Array],
    description: String,
    introduction: String,
    title: String,
    inlineHelp: [String, Object],
    inlineHelpBind: Object,
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
    autocomplete: String,
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
    max: Number,
    component: null,
    templateFile: String
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

      var uicontrol = this.uicontrol;

      if (uicontrol && UI_CONTROLS_TO_TYPE[uicontrol]) {
        return UI_CONTROLS_TO_TYPE[uicontrol];
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
        inlineHelpBind: this.inlineHelpBind,
        title: this.title,
        component: this.component,
        templateFile: this.templateFile,
        uiControlAttributes: Object.assign(Object.assign({}, this.uiControlAttributes), {}, {
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



Fieldvue_type_script_lang_ts.render = Fieldvue_type_template_id_4b1ab6ed_render

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
    var result = JSON.parse(value); // the angularjs site field supplied siteid/sitename properties which initializes the
    // siteselector value. the sitename is assumed to be encoded, and is decoded once.
    // so the value for 'site' Field's in angularjs is assumed to be encoded.

    if (uiControl === 'site') {
      result.name = external_CoreHome_["Matomo"].helper.htmlDecode(result.name);
    }

    return result;
  }

  if (uiControl === 'checkbox' && varType !== 'array') {
    return Object(external_CoreHome_["transformAngularJsBoolAttr"])(value);
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
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
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
      angularJsBind: '@'
    },
    condition: {
      angularJsBind: '@',
      transform: function transform(value, vm, scope) {
        if (!value) {
          return undefined;
        }

        return function (values) {
          return scope.$eval(value, values);
        };
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
    },
    component: {
      angularJsBind: '<',
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      transform: function transform(value, vm, scope) {
        if (!value) {
          return undefined;
        }

        if (scope.templateFile) {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["shallowRef"])(FieldAngularJsTemplate);
        }

        var plugin = value.plugin,
            name = value.name;

        if (!plugin || !name) {
          throw new Error('Invalid component property given to piwik-field directive, must ' + 'be {plugin: \'...\',name: \'...\'}');
        }

        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["shallowRef"])(Object(external_CoreHome_["useExternalPluginComponent"])(plugin, name));
      }
    }
  },
  directiveName: 'piwikField',
  $inject: ['$timeout'],
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel, $timeout) {
      var currentValue = ngModel ? ngModel.$viewValue : scope.value;

      if (newValue !== currentValue) {
        $timeout(function () {
          if (!ngModel) {
            scope.value = newValue;
            return;
          } // ngModel being used


          ngModel.$setViewValue(newValue);
          ngModel.$render(); // not detected by the watch for some reason
        });
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller;

    if (!ngModel) {
      scope.$watch('value', function (newVal) {
        if (newVal !== vm.modelValue) {
          var transformed = handleJsonValue(newVal, scope.varType, scope.uicontrol);
          Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
            vm.modelValue = transformed;
          });
        }
      });
      return;
    } // ngModel being used


    ngModel.$render = function () {
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        vm.modelValue = Object(external_CoreHome_["removeAngularJsSpecificProperties"])(ngModel.$viewValue);
      });
    };

    if (typeof scope.value !== 'undefined') {
      var transformed = handleJsonValue(scope.value, scope.varType, scope.uicontrol);
      ngModel.$setViewValue(transformed);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    } // to provide same behavior in angularjs/<4.6.0, we trigger a model update to the same
    // value, but only for 'site' uicontrols. this only happened for site selectors, no others.


    if (scope.uicontrol === 'site' && ngModel.$viewValue) {
      setTimeout(function () {
        ngModel.$setViewValue(Object.assign({}, ngModel.$viewValue));
      });
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=template&id=4f2694bf

var PluginSettingsvue_type_template_id_4f2694bf_hoisted_1 = {
  class: "pluginSettings",
  ref: "root"
};
var PluginSettingsvue_type_template_id_4f2694bf_hoisted_2 = ["id"];
var PluginSettingsvue_type_template_id_4f2694bf_hoisted_3 = {
  class: "card-content"
};
var PluginSettingsvue_type_template_id_4f2694bf_hoisted_4 = ["id"];
var PluginSettingsvue_type_template_id_4f2694bf_hoisted_5 = ["onClick", "disabled", "value"];
function PluginSettingsvue_type_template_id_4f2694bf_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_GroupedSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSettings");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PluginSettingsvue_type_template_id_4f2694bf_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.settingsPerPlugin, function (settings) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "card",
      id: "".concat(settings.pluginName, "PluginSettings"),
      key: "".concat(settings.pluginName, "PluginSettings")
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PluginSettingsvue_type_template_id_4f2694bf_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
      class: "card-title",
      id: settings.pluginName
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(settings.title), 9, PluginSettingsvue_type_template_id_4f2694bf_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSettings, {
      "group-name": settings.pluginName,
      settings: settings.settings,
      "all-setting-values": _ctx.settingValues,
      onChange: function onChange($event) {
        return _ctx.settingValues["".concat(settings.pluginName, ".").concat($event.name)] = $event.value;
      }
    }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      onClick: function onClick($event) {
        return _ctx.saveSetting(settings.pluginName);
      },
      disabled: _ctx.isLoading,
      class: "pluginsSettingsSubmit btn",
      value: _ctx.translate('General_Save')
    }, null, 8, PluginSettingsvue_type_template_id_4f2694bf_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading || _ctx.isSaving[settings.pluginName]
    }, null, 8, ["loading"])])], 8, PluginSettingsvue_type_template_id_4f2694bf_hoisted_2);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmModal,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.showPasswordConfirmModal = $event;
    }),
    onConfirmed: _ctx.confirmPassword
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmWithPassword')), 1)];
    }),
    _: 1
  }, 8, ["modelValue", "onConfirmed"])], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=template&id=4f2694bf

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=template&id=566a93cc

function GroupedSettingsvue_type_template_id_566a93cc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_GroupedSetting = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("GroupedSetting");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.settings, function (setting) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: "".concat(_ctx.groupPrefix).concat(setting.name)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_GroupedSetting, {
      "model-value": _ctx.allSettingValues["".concat(_ctx.groupPrefix).concat(setting.name)],
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.$emit('change', {
          name: setting.name,
          value: $event
        });
      },
      setting: setting,
      "condition-values": _ctx.settingValues
    }, null, 8, ["model-value", "onUpdate:modelValue", "setting", "condition-values"])]);
  }), 128);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=template&id=566a93cc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=template&id=17c12cc8

function GroupedSettingvue_type_template_id_17c12cc8_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_FormField = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FormField");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FormField, {
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.changeValue($event);
    }),
    "form-field": _ctx.settingWithComponent
  }, null, 8, ["model-value", "form-field"])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showField]]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=template&id=17c12cc8

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=script&lang=ts



 // TODO: have to use angularjs here until there's an expression evaluating alternative

var conditionScope;
/* harmony default export */ var GroupedSettingvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    setting: {
      type: Object,
      required: true
    },
    modelValue: null,
    conditionValues: {
      type: Object,
      required: true
    }
  },
  components: {
    FormField: FormField
  },
  emits: ['update:modelValue'],
  computed: {
    // bc for angularjs field that uses templateFile
    settingWithComponent: function settingWithComponent() {
      if (this.setting.templateFile) {
        return Object.assign(Object.assign({}, this.setting), {}, {
          component: FieldAngularJsTemplate
        });
      }

      return this.setting;
    },
    showField: function showField() {
      var condition = this.setting.condition;

      if (!condition) {
        return true;
      }

      if (!conditionScope) {
        var $rootScope = external_CoreHome_["Matomo"].helper.getAngularDependency('$rootScope');
        conditionScope = $rootScope.$new(true);
      }

      return conditionScope.$eval(condition, this.conditionValues);
    }
  },
  methods: {
    changeValue: function changeValue(newValue) {
      this.$emit('update:modelValue', newValue);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSetting.vue



GroupedSettingvue_type_script_lang_ts.render = GroupedSettingvue_type_template_id_17c12cc8_render

/* harmony default export */ var GroupedSetting = (GroupedSettingvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=script&lang=ts
function GroupedSettingsvue_type_script_lang_ts_slicedToArray(arr, i) { return GroupedSettingsvue_type_script_lang_ts_arrayWithHoles(arr) || GroupedSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || GroupedSettingsvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || GroupedSettingsvue_type_script_lang_ts_nonIterableRest(); }

function GroupedSettingsvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function GroupedSettingsvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function GroupedSettingsvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function GroupedSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function GroupedSettingsvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }



/* harmony default export */ var GroupedSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    groupName: String,
    settings: {
      type: Array,
      required: true
    },
    allSettingValues: {
      type: Object,
      required: true
    }
  },
  emits: ['change'],
  components: {
    GroupedSetting: GroupedSetting
  },
  computed: {
    settingValues: function settingValues() {
      var _this = this;

      var entries = Object.entries(this.allSettingValues).filter(function (_ref) {
        var _ref2 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_ref, 1),
            key = _ref2[0];

        if (_this.groupName) {
          var _key$split = key.split('.'),
              _key$split2 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_key$split, 1),
              groupName = _key$split2[0];

          if (groupName !== _this.groupName) {
            return false;
          }
        }

        return true;
      }).map(function (_ref3) {
        var _ref4 = GroupedSettingsvue_type_script_lang_ts_slicedToArray(_ref3, 2),
            key = _ref4[0],
            value = _ref4[1];

        return _this.groupName ? [key.split('.')[1], value] : [key, value];
      });
      return Object.fromEntries(entries);
    },
    groupPrefix: function groupPrefix() {
      if (!this.groupName) {
        return '';
      }

      return "".concat(this.groupName, ".");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/GroupedSettings/GroupedSettings.vue



GroupedSettingsvue_type_script_lang_ts.render = GroupedSettingsvue_type_template_id_566a93cc_render

/* harmony default export */ var GroupedSettings = (GroupedSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=template&id=0ff066dc

var PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_1 = {
  class: "confirm-password-modal modal",
  ref: "root"
};
var PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_2 = {
  class: "modal-content"
};
var PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_3 = {
  class: "modal-text"
};
var PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_4 = {
  class: "modal-footer"
};
var PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_5 = ["disabled"];
function PasswordConfirmationvue_type_template_id_0ff066dc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.passwordConfirmation,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.passwordConfirmation = $event;
    }),
    uicontrol: 'password',
    disabled: !_ctx.requiresPasswordConfirmation ? 'disabled' : undefined,
    name: 'currentUserPassword',
    autocomplete: 'off',
    "full-width": true,
    title: _ctx.translate('UsersManager_YourCurrentPassword')
  }, null, 8, ["modelValue", "disabled", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    disabled: _ctx.requiresPasswordConfirmation && !_ctx.passwordConfirmation ? 'disabled' : undefined,
    onClick: _cache[1] || (_cache[1] = function ($event) {
      $event.preventDefault();

      _ctx.$emit('confirmed', _ctx.passwordConfirmation);

      _ctx.passwordConfirmation = '';
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 9, PasswordConfirmationvue_type_template_id_0ff066dc_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no btn-flat",
    onClick: _cache[2] || (_cache[2] = function ($event) {
      $event.preventDefault();

      _ctx.$emit('aborted');
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=template&id=0ff066dc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=script&lang=ts



var _window = window,
    $ = _window.$;
/* harmony default export */ var PasswordConfirmationvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    /**
     * Whether the confirmation is displayed or not;
     */
    modelValue: {
      type: Boolean,
      required: true
    }
  },
  data: function data() {
    return {
      passwordConfirmation: ''
    };
  },
  emits: ['confirmed', 'aborted', 'update:modelValue'],
  components: {
    Field: Field
  },
  activated: function activated() {
    this.$emit('update:modelValue', false);
  },
  methods: {
    showPasswordConfirmModal: function showPasswordConfirmModal() {
      var _this = this;

      var root = this.$refs.root;
      var $root = $(root);

      var onEnter = function onEnter(event) {
        var keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode === 13) {
          $root.modal('close');

          _this.$emit('confirmed', _this.passwordConfirmation);

          _this.passwordConfirmation = '';
        }
      };

      $root.modal({
        dismissible: false,
        onOpenEnd: function onOpenEnd() {
          var passwordField = '.modal.open #currentUserPassword';
          $(passwordField).focus();
          $(passwordField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: function onCloseEnd() {
          _this.$emit('update:modelValue', false);
        }
      }).modal('open');
    }
  },
  computed: {
    requiresPasswordConfirmation: function requiresPasswordConfirmation() {
      return !!external_CoreHome_["Matomo"].requiresPasswordConfirmation;
    }
  },
  watch: {
    modelValue: function modelValue(newValue) {
      if (newValue) {
        this.showPasswordConfirmModal();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.vue



PasswordConfirmationvue_type_script_lang_ts.render = PasswordConfirmationvue_type_template_id_0ff066dc_render

/* harmony default export */ var PasswordConfirmation = (PasswordConfirmationvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=script&lang=ts
function PluginSettingsvue_type_script_lang_ts_slicedToArray(arr, i) { return PluginSettingsvue_type_script_lang_ts_arrayWithHoles(arr) || PluginSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) || PluginSettingsvue_type_script_lang_ts_unsupportedIterableToArray(arr, i) || PluginSettingsvue_type_script_lang_ts_nonIterableRest(); }

function PluginSettingsvue_type_script_lang_ts_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function PluginSettingsvue_type_script_lang_ts_unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(o, minLen); }

function PluginSettingsvue_type_script_lang_ts_arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function PluginSettingsvue_type_script_lang_ts_iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function PluginSettingsvue_type_script_lang_ts_arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }





var PluginSettingsvue_type_script_lang_ts_window = window,
    PluginSettingsvue_type_script_lang_ts_$ = PluginSettingsvue_type_script_lang_ts_window.$;
/* harmony default export */ var PluginSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    mode: String
  },
  components: {
    PasswordConfirmation: PasswordConfirmation,
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    GroupedSettings: GroupedSettings
  },
  data: function data() {
    return {
      isLoading: true,
      isSaving: {},
      showPasswordConfirmModal: false,
      settingsToSave: null,
      settingsPerPlugin: [],
      settingValues: {}
    };
  },
  created: function created() {
    var _this = this;

    external_CoreHome_["AjaxHelper"].fetch({
      method: this.apiMethod
    }).then(function (settingsPerPlugin) {
      _this.isLoading = false;
      _this.settingsPerPlugin = settingsPerPlugin;
      settingsPerPlugin.forEach(function (settings) {
        settings.settings.forEach(function (setting) {
          _this.settingValues["".concat(settings.pluginName, ".").concat(setting.name)] = setting.value;
        });
      });
      window.anchorLinkFix.scrollToAnchorInUrl();

      _this.addSectionsToTableOfContents();
    }).catch(function () {
      _this.isLoading = false;
    });
  },
  computed: {
    apiMethod: function apiMethod() {
      return this.mode === 'admin' ? 'CorePluginsAdmin.getSystemSettings' : 'CorePluginsAdmin.getUserSettings';
    },
    saveApiMethod: function saveApiMethod() {
      return this.mode === 'admin' ? 'CorePluginsAdmin.setSystemSettings' : 'CorePluginsAdmin.setUserSettings';
    }
  },
  methods: {
    addSectionsToTableOfContents: function addSectionsToTableOfContents() {
      var $toc = PluginSettingsvue_type_script_lang_ts_$('#generalSettingsTOC');

      if (!$toc.length) {
        return;
      }

      var settingsPerPlugin = this.settingsPerPlugin;
      settingsPerPlugin.forEach(function (settingsForPlugin) {
        var pluginName = settingsForPlugin.pluginName,
            settings = settingsForPlugin.settings;

        if (!pluginName) {
          return;
        }

        if (pluginName === 'CoreAdminHome' && settings) {
          settings.filter(function (s) {
            return s.introduction;
          }).forEach(function (s) {
            $toc.append("<a href=\"#/".concat(pluginName, "PluginSettings\">").concat(s.introduction, "</a> "));
          });
        } else {
          $toc.append("<a href=\"#/".concat(pluginName, "\">").concat(pluginName.replace(/([A-Z])/g, ' $1').trim(), "</a> "));
        }
      });
    },
    confirmPassword: function confirmPassword(password) {
      this.showPasswordConfirmModal = false;
      this.save(this.settingsToSave, password);
    },
    saveSetting: function saveSetting(requestedPlugin) {
      if (this.mode === 'admin') {
        this.settingsToSave = requestedPlugin;
        this.showPasswordConfirmModal = true;
      } else {
        this.save(requestedPlugin);
      }
    },
    save: function save(requestedPlugin, password) {
      var _this2 = this;

      var saveApiMethod = this.saveApiMethod;
      this.isSaving[requestedPlugin] = true;
      var settingValuesPayload = this.getValuesForPlugin(requestedPlugin);
      external_CoreHome_["AjaxHelper"].post({
        method: saveApiMethod
      }, {
        settingValues: settingValuesPayload,
        passwordConfirmation: password
      }).then(function () {
        _this2.isSaving[requestedPlugin] = false;
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_PluginSettingsSaveSuccess'),
          id: 'generalSettings',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).catch(function () {
        _this2.isSaving[requestedPlugin] = false;
      });
      this.settingsToSave = null;
    },
    getValuesForPlugin: function getValuesForPlugin(requestedPlugin) {
      var values = {};

      if (!values[requestedPlugin]) {
        values[requestedPlugin] = [];
      }

      Object.entries(this.settingValues).forEach(function (_ref) {
        var _ref2 = PluginSettingsvue_type_script_lang_ts_slicedToArray(_ref, 2),
            key = _ref2[0],
            value = _ref2[1];

        var _key$split = key.split('.'),
            _key$split2 = PluginSettingsvue_type_script_lang_ts_slicedToArray(_key$split, 2),
            pluginName = _key$split2[0],
            settingName = _key$split2[1];

        if (pluginName !== requestedPlugin) {
          return;
        }

        var postValue = value;

        if (postValue === false) {
          postValue = '0';
        } else if (postValue === true) {
          postValue = '1';
        }

        values[pluginName].push({
          name: settingName,
          value: postValue
        });
      });
      return values;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.vue



PluginSettingsvue_type_script_lang_ts.render = PluginSettingsvue_type_template_id_4f2694bf_render

/* harmony default export */ var PluginSettings = (PluginSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var PluginSettings_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: PluginSettings,
  scope: {
    mode: {
      angularJsBind: '@'
    }
  },
  directiveName: 'piwikPluginSettings'
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginManagement.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var PluginManagement_window = window,
    PluginManagement_$ = PluginManagement_window.$;

function onClickUninstall(binding, event) {
  event.preventDefault();
  var link = PluginManagement_$(event.target).attr('href');
  var pluginName = PluginManagement_$(event.target).attr('data-plugin-name');

  if (!link || !pluginName) {
    return;
  }

  if (!binding.value.uninstallConfirmMessage) {
    binding.value.uninstallConfirmMessage = PluginManagement_$('#uninstallPluginConfirm').text();
  }

  var messageToDisplay = (binding.value.uninstallConfirmMessage || '').replace('%s', pluginName);
  PluginManagement_$('#uninstallPluginConfirm').text(messageToDisplay);
  external_CoreHome_["Matomo"].helper.modalConfirm('#confirmUninstallPlugin', {
    yes: function yes() {
      window.location.href = link;
    }
  });
}

function onDonateLinkClick(event) {
  event.preventDefault();
  var overlayId = PluginManagement_$(event.target).data('overlay-id');
  external_CoreHome_["Matomo"].helper.modalConfirm("#".concat(overlayId), {});
}

/* harmony default export */ var PluginManagement = ({
  mounted: function mounted(el, binding) {
    setTimeout(function () {
      binding.value.uninstallConfirmMessage = '';
      PluginManagement_$(el).find('.uninstall').click(onClickUninstall.bind(null, binding));
      PluginManagement_$(el).find('.plugin-donation-link').click(onDonateLinkClick);
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginManagement.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikPluginManagement() {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope, element) {
      var binding = {
        instance: null,
        value: {},
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      PluginManagement.mounted(element[0], binding);
    }
  };
}
window.angular.module('piwikApp').directive('piwikPluginManagement', piwikPluginManagement);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginUpload.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var PluginUpload_window = window,
    PluginUpload_$ = PluginUpload_window.$;

function onUploadPlugin(event) {
  event.preventDefault();
  external_CoreHome_["Matomo"].helper.modalConfirm('#installPluginByUpload', {});
}

function onSubmitPlugin(event) {
  var $zipFile = PluginUpload_$('[name=pluginZip]');
  var fileName = $zipFile.val();

  if (!fileName || fileName.slice(-4) !== '.zip') {
    event.preventDefault(); // eslint-disable-next-line no-alert

    alert(Object(external_CoreHome_["translate"])('CorePluginsAdmin_NoZipFileSelected'));
  } else if ($zipFile.data('maxSize') > 0 && $zipFile[0].files[0].size > $zipFile.data('maxSize') * 1048576) {
    event.preventDefault(); // eslint-disable-next-line no-alert

    alert(Object(external_CoreHome_["translate"])('CorePluginsAdmin_FileExceedsUploadLimit'));
  }
}

/* harmony default export */ var PluginUpload = ({
  mounted: function mounted() {
    setTimeout(function () {
      PluginUpload_$('.uploadPlugin').click(onUploadPlugin);
      PluginUpload_$('#uploadPluginForm').submit(onSubmitPlugin);
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginUpload.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikPluginUpload() {
  return {
    restrict: 'A',
    link: function expandOnClickLink() {
      PluginUpload.mounted();
    }
  };
}
window.angular.module('piwikApp').directive('piwikPluginUpload', piwikPluginUpload);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginFilter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var PluginFilter_window = window,
    PluginFilter_$ = PluginFilter_window.$;

function getCurrentFilterOrigin(element) {
  return element.find('.origin a.active').data('filter-origin');
}

function getCurrentFilterStatus(element) {
  return element.find('.status a.active').data('filter-status');
}

function getMatchingNodes(filterOrigin, filterStatus) {
  var query = '#plugins tr';

  if (filterOrigin === 'all') {
    query += '[data-filter-origin]';
  } else {
    query += "[data-filter-origin=".concat(filterOrigin, "]");
  }

  if (filterStatus === 'all') {
    query += '[data-filter-status]';
  } else {
    query += "[data-filter-status=".concat(filterStatus, "]");
  }

  return PluginFilter_$(query);
}

function updateNumberOfMatchingPluginsInFilter(element, selectorFilterToUpdate, filterOrigin, filterStatus) {
  var numMatchingNodes = getMatchingNodes(filterOrigin, filterStatus).length;
  var updatedCounterText = " (".concat(numMatchingNodes, ")");
  element.find("".concat(selectorFilterToUpdate, " .counter")).text(updatedCounterText);
}

function updateAllNumbersOfMatchingPluginsInFilter(element) {
  var filterOrigin = getCurrentFilterOrigin(element);
  var filterStatus = getCurrentFilterStatus(element);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="all"]', filterOrigin, 'all');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="active"]', filterOrigin, 'active');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-status="inactive"]', filterOrigin, 'inactive');
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="all"]', 'all', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="core"]', 'core', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="official"]', 'official', filterStatus);
  updateNumberOfMatchingPluginsInFilter(element, '[data-filter-origin="thirdparty"]', 'thirdparty', filterStatus);
}

function filterPlugins(element) {
  var filterOrigin = getCurrentFilterOrigin(element);
  var filterStatus = getCurrentFilterStatus(element);
  var $nodesToEnable = getMatchingNodes(filterOrigin, filterStatus);
  PluginFilter_$('#plugins tr[data-filter-origin][data-filter-status]').css('display', 'none');
  $nodesToEnable.css('display', 'table-row');
  updateAllNumbersOfMatchingPluginsInFilter(element);
}

function onClickStatus(element, event) {
  event.preventDefault();
  PluginFilter_$(event.target).siblings().removeClass('active');
  PluginFilter_$(event.target).addClass('active');
  filterPlugins(element);
}

function onClickOrigin(element, event) {
  event.preventDefault();
  PluginFilter_$(event.target).siblings().removeClass('active');
  PluginFilter_$(event.target).addClass('active');
  filterPlugins(element);
}

/* harmony default export */ var PluginFilter = ({
  mounted: function mounted(el) {
    setTimeout(function () {
      updateAllNumbersOfMatchingPluginsInFilter(PluginFilter_$(el));
      PluginFilter_$(el).find('.status').on('click', 'a', onClickStatus.bind(null, PluginFilter_$(el)));
      PluginFilter_$(el).find('.origin').on('click', 'a', onClickOrigin.bind(null, PluginFilter_$(el)));
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Plugins/PluginFilter.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikPluginFilter() {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope, element) {
      PluginFilter.mounted(element[0]);
    }
  };
}
window.angular.module('piwikApp').directive('piwikPluginFilter', piwikPluginFilter);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=template&id=1e391ba1

var SaveButtonvue_type_template_id_1e391ba1_hoisted_1 = {
  class: "matomo-save-button",
  style: {
    "display": "inline-block"
  }
};
var SaveButtonvue_type_template_id_1e391ba1_hoisted_2 = ["disabled", "value"];
function SaveButtonvue_type_template_id_1e391ba1_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveButtonvue_type_template_id_1e391ba1_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "button",
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onConfirm($event);
    }),
    disabled: _ctx.saving || _ctx.disabled,
    class: "btn",
    value: _ctx.value ? _ctx.value : _ctx.translate('General_Save')
  }, null, 8, SaveButtonvue_type_template_id_1e391ba1_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.saving
  }, null, 8, ["loading"])]);
}
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=template&id=1e391ba1

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=script&lang=ts


/* harmony default export */ var SaveButtonvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    saving: Boolean,
    value: String,
    disabled: Boolean
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  emits: ['confirm'],
  methods: {
    onConfirm: function onConfirm(event) {
      this.$emit('confirm', event);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.vue



SaveButtonvue_type_script_lang_ts.render = SaveButtonvue_type_template_id_1e391ba1_render

/* harmony default export */ var SaveButton = (SaveButtonvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/SaveButton/SaveButton.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var SaveButton_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SaveButton,
  scope: {
    saving: {
      angularJsBind: '=?'
    },
    value: {
      angularJsBind: '@?'
    },
    disabled: {
      angularJsBind: '=?'
    },
    onconfirm: {
      angularJsBind: '&?',
      vue: 'confirm'
    }
  },
  events: {
    confirm: function confirm($event, vm, scope, element, attrs, controller, $timeout) {
      element[0].click(); // this directive can be used on input's with ng-click specified

      $timeout();
    }
  },
  replace: true,
  directiveName: 'piwikSaveButton',
  $inject: ['$timeout']
}));
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Form/Form.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var Form_window = window,
    Form_$ = Form_window.$;
/* harmony default export */ var Form = ({
  mounted: function mounted(el) {
    setTimeout(function () {
      Form_$(el).find('input[type=text]').keypress(function (e) {
        var key = e.keyCode || e.which;

        if (key === 13) {
          Form_$(el).find('.matomo-save-button input').triggerHandler('click');
        }
      });
    });
  }
});
// CONCATENATED MODULE: ./plugins/CorePluginsAdmin/vue/src/Form/Form.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikForm() {
  return {
    restrict: 'A',
    link: function expandOnClickLink(scope, element) {
      Form.mounted(element[0]);
    }
  };
}
window.angular.module('piwikApp').directive('piwikForm', piwikForm);
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