(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["MobileMessaging"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["MobileMessaging"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/MobileMessaging/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "ManageMobilePhoneNumbers", function() { return /* reexport */ ManageMobilePhoneNumbers; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=58601729

var _hoisted_1 = {
  key: 0
};
var _hoisted_2 = {
  class: "row"
};
var _hoisted_3 = {
  class: "col s12"
};
var _hoisted_4 = {
  class: "form-group row"
};
var _hoisted_5 = {
  class: "col s12 m6"
};
var _hoisted_6 = {
  class: "col s12 m6 form-help"
};
var _hoisted_7 = {
  class: "form-group row addPhoneNumber"
};
var _hoisted_8 = {
  class: "col s12 m6"
};
var _hoisted_9 = {
  class: "countryCode left"
};

var _hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "countryCodeSymbol"
}, "+", -1);

var _hoisted_11 = {
  class: "phoneNumber left"
};
var _hoisted_12 = {
  class: "addNumber left valign-wrapper"
};
var _hoisted_13 = {
  class: "col s12 m6 form-help"
};

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxErrorAddPhoneNumber"
}, null, -1);

var _hoisted_15 = {
  key: 1,
  class: "row"
};
var _hoisted_16 = {
  class: "col s12"
};
var _hoisted_17 = {
  class: "col s12 m6"
};
var _hoisted_18 = {
  class: "phoneNumber"
};
var _hoisted_19 = ["onUpdate:modelValue", "placeholder"];
var _hoisted_20 = {
  key: 0,
  class: "form-help col s12 m6"
};

var _hoisted_21 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("   ");

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "invalidVerificationCodeAjaxError",
  style: {
    "display": "none"
  }
}, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Help')), 1), _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Add')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "countryCodeSelect",
    title: _ctx.translate('MobileMessaging_Settings_SelectCountry'),
    modelValue: _ctx.countryCallingCode,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.countryCallingCode = $event;
    }),
    "full-width": true,
    options: _ctx.countries
  }, null, 8, ["title", "modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_CountryCode_Help')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "countryCallingCode",
    title: _ctx.translate('MobileMessaging_Settings_CountryCode'),
    modelValue: _ctx.countryCallingCode,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.countryCallingCode = $event;
    }),
    "full-width": true,
    maxlength: 4
  }, null, 8, ["title", "modelValue"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "newPhoneNumber",
    modelValue: _ctx.newPhoneNumber,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
      return _ctx.newPhoneNumber = $event;
    }),
    title: _ctx.translate('MobileMessaging_Settings_PhoneNumber'),
    "full-width": true,
    maxlength: 80
  }, null, 8, ["modelValue", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    class: "valign",
    disabled: !_ctx.canAddNumber || _ctx.isAddingPhonenumber,
    onConfirm: _cache[3] || (_cache[3] = function ($event) {
      return _ctx.addPhoneNumber();
    }),
    value: _ctx.translate('General_Add')
  }, null, 8, ["disabled", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
    severity: "warning",
    id: "suspiciousPhoneNumber"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_SuspiciousPhoneNumber', '54184032')), 1)];
    }),
    _: 1
  }, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showSuspiciousPhoneNumber]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.strHelpAddPhone), 1)]), _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isAddingPhonenumber
  }, null, 8, ["loading"]), Object.keys(_ctx.phoneNumbers || {}).length > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_ManagePhoneNumbers')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.phoneNumbers || [], function (validated, phoneNumber, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      class: "form-group row",
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(phoneNumber), 1), !validated ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
      key: 0,
      type: "text",
      class: "verificationCode",
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.validationCode[index] = $event;
      },
      placeholder: _ctx.translate('MobileMessaging_Settings_EnterActivationCode'),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, _hoisted_19)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.validationCode[index]], [external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isActivated[index]]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !validated ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
      key: 1,
      disabled: !_ctx.validationCode[index] || _ctx.isChangingPhoneNumber,
      onConfirm: function onConfirm($event) {
        return _ctx.validateActivationCode(phoneNumber, index);
      },
      value: _ctx.translate('MobileMessaging_Settings_ValidatePhoneNumber'),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, ["disabled", "onConfirm", "value"])), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], "!(isActivated[index])"]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      disabled: _ctx.isChangingPhoneNumber,
      onConfirm: function onConfirm($event) {
        return _ctx.removePhoneNumber(phoneNumber);
      },
      value: _ctx.translate('General_Remove')
    }, null, 8, ["disabled", "onConfirm", "value"])]), !validated ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_VerificationCodeJustSent')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isActivated[index]]]), _hoisted_21])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isChangingPhoneNumber
  }, null, 8, ["loading"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=58601729

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=script&lang=ts



/* harmony default export */ var ManageMobilePhoneNumbersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isSuperUser: Boolean,
    defaultCallingCode: String,
    countries: {
      type: Array,
      required: true
    },
    strHelpAddPhone: {
      type: String,
      required: true
    },
    phoneNumbers: Object
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Alert: external_CoreHome_["Alert"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data: function data() {
    return {
      isAddingPhonenumber: false,
      isChangingPhoneNumber: false,
      isActivated: {},
      countryCallingCode: this.defaultCallingCode || '',
      newPhoneNumber: '',
      validationCode: {}
    };
  },
  methods: {
    validateActivationCode: function validateActivationCode(phoneNumber, index) {
      var _this = this;

      if (!this.validationCode[index]) {
        return;
      }

      var verificationCode = this.validationCode[index];
      this.isChangingPhoneNumber = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.validatePhoneNumber'
      }, {
        phoneNumber: phoneNumber,
        verificationCode: verificationCode
      }, {
        errorElement: '#invalidVerificationCodeAjaxError'
      }).then(function (response) {
        _this.isChangingPhoneNumber = false;
        var notificationInstanceId;

        if (!response || !response.value) {
          var message = Object(external_CoreHome_["translate"])('MobileMessaging_Settings_InvalidActivationCode');
          notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
            message: message,
            context: 'error',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient'
          });
        } else {
          var _message = Object(external_CoreHome_["translate"])('MobileMessaging_Settings_PhoneActivated');

          notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
            message: _message,
            context: 'success',
            id: 'MobileMessaging_ValidatePhoneNumber',
            type: 'transient'
          });
          _this.isActivated[index] = true;
        }

        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
      }).finally(function () {
        _this.isChangingPhoneNumber = false;
      });
    },
    removePhoneNumber: function removePhoneNumber(phoneNumber) {
      var _this2 = this;

      if (!phoneNumber) {
        return;
      }

      this.isChangingPhoneNumber = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.removePhoneNumber'
      }, {
        phoneNumber: phoneNumber
      }, {
        errorElement: '#invalidVerificationCodeAjaxError'
      }).then(function () {
        _this2.isChangingPhoneNumber = false;
        external_CoreHome_["Matomo"].helper.redirect();
      }).finally(function () {
        _this2.isChangingPhoneNumber = false;
      });
    },
    addPhoneNumber: function addPhoneNumber() {
      var _this3 = this;

      var phoneNumber = "+".concat(this.countryCallingCode).concat(this.newPhoneNumber);

      if (this.canAddNumber && phoneNumber.length > 1) {
        this.isAddingPhonenumber = true;
        external_CoreHome_["AjaxHelper"].post({
          method: 'MobileMessaging.addPhoneNumber'
        }, {
          phoneNumber: phoneNumber
        }, {
          errorElement: '#ajaxErrorAddPhoneNumber'
        }).then(function () {
          _this3.isAddingPhonenumber = false;
          external_CoreHome_["Matomo"].helper.redirect();
        }).finally(function () {
          _this3.isAddingPhonenumber = false;
        });
      }
    }
  },
  computed: {
    showSuspiciousPhoneNumber: function showSuspiciousPhoneNumber() {
      return this.newPhoneNumber.trim().lastIndexOf('0', 0) === 0;
    },
    canAddNumber: function canAddNumber() {
      return !!this.newPhoneNumber && this.newPhoneNumber !== '';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue



ManageMobilePhoneNumbersvue_type_script_lang_ts.render = render

/* harmony default export */ var ManageMobilePhoneNumbers = (ManageMobilePhoneNumbersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/index.ts
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
//# sourceMappingURL=MobileMessaging.umd.js.map