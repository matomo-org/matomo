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
__webpack_require__.d(__webpack_exports__, "ReportParameters", function() { return /* reexport */ ReportParameters; });
__webpack_require__.d(__webpack_exports__, "ManageSmsProvider", function() { return /* reexport */ ManageSmsProvider; });
__webpack_require__.d(__webpack_exports__, "SmsProviderCredentials", function() { return /* reexport */ SmsProviderCredentials; });
__webpack_require__.d(__webpack_exports__, "DelegateMobileMessagingSettings", function() { return /* reexport */ DelegateMobileMessagingSettings; });
__webpack_require__.d(__webpack_exports__, "ManageMobilePhoneNumbers", function() { return /* reexport */ ManageMobilePhoneNumbers; });
__webpack_require__.d(__webpack_exports__, "SelectPhoneNumbers", function() { return /* reexport */ SelectPhoneNumbers; });

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

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=e5a63bc6
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }


var _hoisted_1 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return _ctx.fields ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.fields, function (field) {
    var _ctx$modelValue;

    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: field.name,
      uicontrol: field.type,
      name: field.name,
      "model-value": (_ctx$modelValue = _ctx.modelValue) === null || _ctx$modelValue === void 0 ? void 0 : _ctx$modelValue[field.name],
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.$emit('update:modelValue', Object.assign(Object.assign({}, _ctx.modelValue), {}, _defineProperty({}, field.name, $event)));
      },
      title: _ctx.translate(field.title)
    }, null, 8, ["uicontrol", "name", "model-value", "onUpdate:modelValue", "title"]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=e5a63bc6

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=script&lang=ts



var allFieldsByProvider = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({});
/* harmony default export */ var SmsProviderCredentialsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    provider: {
      type: String,
      required: true
    },
    modelValue: Object
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  watch: {
    provider: function provider() {
      // unset credentials when new provider is chosen
      this.$emit('update:modelValue', null); // fetch fields for provider

      this.getCredentialFields();
    }
  },
  created: function created() {
    this.getCredentialFields();
  },
  methods: {
    getCredentialFields: function getCredentialFields() {
      var _this = this;

      if (allFieldsByProvider[this.provider]) {
        this.$emit('update:modelValue', Object.fromEntries(allFieldsByProvider[this.provider].map(function (f) {
          return [f.name, null];
        })));
        return;
      }

      external_CoreHome_["AjaxHelper"].fetch({
        module: 'MobileMessaging',
        action: 'getCredentialFields',
        provider: this.provider
      }).then(function (fields) {
        _this.$emit('update:modelValue', Object.fromEntries(fields.map(function (f) {
          return [f.name, null];
        })));

        allFieldsByProvider[_this.provider] = fields;
      });
    }
  },
  computed: {
    fields: function fields() {
      return allFieldsByProvider[this.provider];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue



SmsProviderCredentialsvue_type_script_lang_ts.render = render

/* harmony default export */ var SmsProviderCredentials = (SmsProviderCredentialsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/* harmony default export */ var SmsProviderCredentials_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SmsProviderCredentials,
  scope: {
    provider: {
      angularJsBind: '='
    },
    credentials: {
      angularJsBind: '=value'
    }
  },
  directiveName: 'smsProviderCredentials',
  transclude: true,
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
          Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
            vm.modelValue = newVal;
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
      ngModel.$setViewValue(scope.value);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=template&id=12d83df9

var SelectPhoneNumbersvue_type_template_id_12d83df9_hoisted_1 = {
  class: "mobile"
};
var _hoisted_2 = {
  id: "mobilePhoneNumbersHelp",
  class: "inline-help-node"
};

var _hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-info",
  style: {
    "margin-right": "3.5px"
  }
}, null, -1);

var _hoisted_4 = {
  key: 0,
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_5 = {
  key: 1,
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_6 = ["href"];
function SelectPhoneNumbersvue_type_template_id_12d83df9_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SelectPhoneNumbersvue_type_template_id_12d83df9_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "checkbox",
    "var-type": "array",
    name: "phoneNumbers",
    "model-value": _ctx.modelValue,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('update:modelValue', $event);
    }),
    introduction: _ctx.withIntroduction ? _ctx.translate('ScheduledReports_SendReportTo') : undefined,
    title: _ctx.translate('MobileMessaging_PhoneNumbers'),
    disabled: _ctx.phoneNumbers.length === 0,
    options: _ctx.phoneNumbers
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [_hoisted_3, _ctx.phoneNumbers.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_NoPhoneNumbers')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_AdditionalPhoneNumbers')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.linkTo({
          module: 'MobileMessaging',
          action: 'index',
          updated: null
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_MobileReport_MobileMessagingSettingsLink')), 9, _hoisted_6)])];
    }),
    _: 1
  }, 8, ["model-value", "introduction", "title", "disabled", "options"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=template&id=12d83df9

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=script&lang=ts



/* harmony default export */ var SelectPhoneNumbersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: Array,
    phoneNumbers: {
      type: [Array, Object],
      required: true
    },
    withIntroduction: Boolean
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  methods: {
    linkTo: function linkTo(params) {
      return "?".concat(external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params)));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.vue



SelectPhoneNumbersvue_type_script_lang_ts.render = SelectPhoneNumbersvue_type_template_id_12d83df9_render

/* harmony default export */ var SelectPhoneNumbers = (SelectPhoneNumbersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SelectPhoneNumbers/SelectPhoneNumbers.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/* harmony default export */ var SelectPhoneNumbers_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: SelectPhoneNumbers,
  require: '?ngModel',
  scope: {
    phoneNumbers: {
      angularJsBind: '<'
    },
    withIntroduction: {
      angularJsBind: '<'
    },
    value: {
      angularJsBind: '<',
      vue: 'modelValue'
    }
  },
  $inject: ['$timeout'],
  directiveName: 'matomoSelectPhoneNumbers',
  events: {
    'update:modelValue': function updateModelValue(newValue, vm, scope, element, attrs, ngModel, $timeout) {
      if (!ngModel) {
        return;
      }

      if (newValue !== ngModel.$viewValue) {
        $timeout(function () {
          ngModel.$setViewValue(newValue);
          ngModel.$render(); // not detected by the watch for some reason
        });
      }
    }
  },
  postCreate: function postCreate(vm, scope, element, attrs, controller) {
    var ngModel = controller; // ngModel being used

    ngModel.$render = function () {
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        vm.modelValue = Object(external_CoreHome_["removeAngularJsSpecificProperties"])(ngModel.$viewValue);
      });
    };

    if (typeof scope.value !== 'undefined') {
      ngModel.$setViewValue(scope.value);
    } else {
      ngModel.$setViewValue(vm.modelValue);
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=5f520e2e

var ReportParametersvue_type_template_id_5f520e2e_hoisted_1 = {
  key: 0
};
function ReportParametersvue_type_template_id_5f520e2e_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SelectPhoneNumbers = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SelectPhoneNumbers");

  return _ctx.report && _ctx.report.type === 'mobile' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReportParametersvue_type_template_id_5f520e2e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SelectPhoneNumbers, {
    "phone-numbers": _ctx.phoneNumbers,
    "with-introduction": true,
    "model-value": _ctx.report.phoneNumbers,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.$emit('change', 'phoneNumbers', $event);
    })
  }, null, 8, ["phone-numbers", "model-value"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=5f520e2e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts


var REPORT_TYPE = 'mobile';
/* harmony default export */ var ReportParametersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    phoneNumbers: {
      type: [Array, Object],
      required: true
    }
  },
  components: {
    SelectPhoneNumbers: SelectPhoneNumbers
  },
  emits: ['change'],
  created: function created() {
    var _window = window,
        resetReportParametersFunctions = _window.resetReportParametersFunctions,
        updateReportParametersFunctions = _window.updateReportParametersFunctions,
        getReportParametersFunctions = _window.getReportParametersFunctions;

    if (!resetReportParametersFunctions[REPORT_TYPE]) {
      resetReportParametersFunctions[REPORT_TYPE] = function (report) {
        report.phoneNumbers = [];
        report.formatmobile = 'sms';
      };
    }

    if (!updateReportParametersFunctions[REPORT_TYPE]) {
      updateReportParametersFunctions[REPORT_TYPE] = function (report) {
        if (!(report !== null && report !== void 0 && report.parameters)) {
          return;
        }

        if (report.parameters && report.parameters.phoneNumbers) {
          report.phoneNumbers = report.parameters.phoneNumbers;
        }

        report.formatmobile = 'sms';
      };
    }

    if (!getReportParametersFunctions[REPORT_TYPE]) {
      getReportParametersFunctions[REPORT_TYPE] = function (report) {
        // returning [''] when no phone numbers are selected avoids the "please provide a value
        // for 'parameters'" error message
        var phoneNumbers = report.phoneNumbers;
        return {
          phoneNumbers: phoneNumbers || ['']
        };
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ReportParameters/ReportParameters.vue



ReportParametersvue_type_script_lang_ts.render = ReportParametersvue_type_template_id_5f520e2e_render

/* harmony default export */ var ReportParameters = (ReportParametersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=6fdded90


var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxErrorManageSmsProviderSettings"
}, null, -1);

var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_2 = {
  key: 0
};
var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_3 = {
  key: 0
};

var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_5 = {
  key: 1
};

var ManageSmsProvidervue_type_template_id_6fdded90_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = ["innerHTML"];
var _hoisted_8 = {
  key: 1
};
var _hoisted_9 = {
  id: "accountForm"
};
var _hoisted_10 = ["innerHTML"];
function ManageSmsProvidervue_type_template_id_6fdded90_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SmsProviderCredentials = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SmsProviderCredentials");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isDeletingAccount
  }, null, 8, ["loading"]), ManageSmsProvidervue_type_template_id_6fdded90_hoisted_1, _ctx.credentialSupplied ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageSmsProvidervue_type_template_id_6fdded90_hoisted_2, [_ctx.credentialError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSmsProvidervue_type_template_id_6fdded90_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialInvalid', _ctx.provider)), 1), ManageSmsProvidervue_type_template_id_6fdded90_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.credentialError), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSmsProvidervue_type_template_id_6fdded90_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialProvided', _ctx.provider)) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.creditLeft), 1)), ManageSmsProvidervue_type_template_id_6fdded90_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.updateOrDeleteAccountText),
    onClick: _cache[0] || (_cache[0] = function ($event) {
      return _ctx.onUpdateOrDeleteClick($event);
    })
  }, null, 8, _hoisted_7)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PleaseSignUp')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "smsProviders",
    modelValue: _ctx.smsProvider,
    "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
      return _ctx.smsProvider = $event;
    }),
    title: _ctx.translate('MobileMessaging_Settings_SMSProvider'),
    options: _ctx.smsProviderOptions,
    value: _ctx.provider
  }, null, 8, ["modelValue", "title", "options", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SmsProviderCredentials, {
    provider: _ctx.smsProvider,
    modelValue: _ctx.credentials,
    "onUpdate:modelValue": [_cache[2] || (_cache[2] = function ($event) {
      return _ctx.credentials = $event;
    }), _cache[3] || (_cache[3] = function ($event) {
      _ctx.credentials = $event;
    })],
    "model-value": _ctx.credentials
  }, null, 8, ["provider", "modelValue", "model-value"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    id: "apiAccountSubmit",
    disabled: !_ctx.isUpdateAccountPossible,
    saving: _ctx.isUpdatingAccount,
    onConfirm: _cache[4] || (_cache[4] = function ($event) {
      return _ctx.updateAccount();
    })
  }, null, 8, ["disabled", "saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "providerDescription",
    innerHTML: _ctx.$sanitize(_ctx.currentProviderDescription)
  }, null, 8, _hoisted_10)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.credentialSupplied || _ctx.showAccountForm], [_directive_form]])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=6fdded90

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=script&lang=ts




/* harmony default export */ var ManageSmsProvidervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    credentialSupplied: Boolean,
    credentialError: String,
    provider: String,
    creditLeft: [Number, String],
    smsProviderOptions: {
      type: Object,
      required: true
    },
    smsProviders: {
      type: Object,
      required: true
    }
  },
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    SmsProviderCredentials: SmsProviderCredentials
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data: function data() {
    return {
      isDeletingAccount: false,
      isUpdatingAccount: false,
      showAccountForm: false,
      credentials: null,
      smsProvider: this.provider
    };
  },
  methods: {
    deleteApiAccount: function deleteApiAccount() {
      var _this = this;

      this.isDeletingAccount = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'MobileMessaging.deleteSMSAPICredential'
      }, {
        errorElement: '#ajaxErrorManageSmsProviderSettings'
      }).then(function () {
        external_CoreHome_["Matomo"].helper.redirect();
      }).finally(function () {
        _this.isDeletingAccount = false;
      });
    },
    showUpdateAccount: function showUpdateAccount() {
      this.showAccountForm = true;
    },
    updateAccount: function updateAccount() {
      var _this2 = this;

      if (this.isUpdateAccountPossible) {
        this.isUpdatingAccount = true;
        external_CoreHome_["AjaxHelper"].post({
          method: 'MobileMessaging.setSMSAPICredential'
        }, {
          provider: this.smsProvider,
          credentials: this.credentials
        }, {
          errorElement: '#ajaxErrorManageSmsProviderSettings'
        }).then(function () {
          external_CoreHome_["Matomo"].helper.redirect();
        }).finally(function () {
          _this2.isUpdatingAccount = false;
        });
      }
    },
    deleteAccount: function deleteAccount() {
      var _this3 = this;

      external_CoreHome_["Matomo"].helper.modalConfirm('#confirmDeleteAccount', {
        yes: function yes() {
          _this3.isDeletingAccount = true;
          external_CoreHome_["AjaxHelper"].fetch({
            method: 'MobileMessaging.deleteSMSAPICredential'
          }, {
            errorElement: '#ajaxErrorManageSmsProviderSettings'
          }).then(function () {
            _this3.isDeletingAccount = false;
            external_CoreHome_["Matomo"].helper.redirect();
          }).finally(function () {
            _this3.isDeletingAccount = false;
          });
        }
      });
    },
    onUpdateOrDeleteClick: function onUpdateOrDeleteClick(event) {
      var target = event.target;

      if (target.id === 'displayAccountForm') {
        this.showUpdateAccount();
      } else if (target.id === 'deleteAccount') {
        this.deleteAccount();
      }
    }
  },
  computed: {
    isUpdateAccountPossible: function isUpdateAccountPossible() {
      // possible if smsProvider is set and all credential field values are set to something
      return !!this.smsProvider && this.credentials !== null && Object.values(this.credentials).every(function (v) {
        return !!v;
      });
    },
    updateOrDeleteAccountText: function updateOrDeleteAccountText() {
      return Object(external_CoreHome_["translate"])('MobileMessaging_Settings_UpdateOrDeleteAccount', '<a id="displayAccountForm">', '</a>', '<a id="deleteAccount">', '</a>');
    },
    currentProviderDescription: function currentProviderDescription() {
      if (!this.smsProvider || !this.smsProviders) {
        return '';
      }

      return this.smsProviders[this.smsProvider];
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue



ManageSmsProvidervue_type_script_lang_ts.render = ManageSmsProvidervue_type_template_id_6fdded90_render

/* harmony default export */ var ManageSmsProvider = (ManageSmsProvidervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=template&id=8e4eafcc

function DelegateMobileMessagingSettingsvue_type_template_id_8e4eafcc_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "radio",
    name: "delegatedManagement",
    title: _ctx.translate('MobileMessaging_Settings_LetUsersManageAPICredential'),
    modelValue: _ctx.enabled,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.enabled = $event;
    }),
    "full-width": true,
    options: _ctx.delegateManagementOptions
  }, null, 8, ["title", "modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
    onConfirm: _cache[1] || (_cache[1] = function ($event) {
      return _ctx.save();
    }),
    saving: _ctx.isLoading
  }, null, 8, ["saving"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=template&id=8e4eafcc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var DelegateMobileMessagingSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    delegateManagementOptions: {
      type: Array,
      required: true
    },
    delegatedManagement: [Number, Boolean]
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"]
  },
  data: function data() {
    return {
      isLoading: false,
      enabled: this.delegatedManagement ? 1 : 0
    };
  },
  methods: {
    save: function save() {
      var _this = this;

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'MobileMessaging.setDelegatedManagement'
      }, {
        delegatedManagement: this.enabled && this.enabled !== '0' ? 'true' : 'false'
      }).then(function () {
        var notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          id: 'mobileMessagingSettings',
          type: 'transient',
          context: 'success'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
        external_CoreHome_["Matomo"].helper.redirect();
      }).finally(function () {
        _this.isLoading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue



DelegateMobileMessagingSettingsvue_type_script_lang_ts.render = DelegateMobileMessagingSettingsvue_type_template_id_8e4eafcc_render

/* harmony default export */ var DelegateMobileMessagingSettings = (DelegateMobileMessagingSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=373eda9d

var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_1 = {
  key: 0
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_2 = {
  class: "row"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_3 = {
  class: "col s12"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_4 = {
  class: "form-group row"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_5 = {
  class: "col s12 m6"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_6 = {
  class: "col s12 m6 form-help"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_7 = {
  class: "form-group row addPhoneNumber"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_8 = {
  class: "col s12 m6"
};
var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_9 = {
  class: "countryCode left"
};

var ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
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

function ManageMobilePhoneNumbersvue_type_template_id_373eda9d_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Help')), 1), _ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_Add')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "select",
    name: "countryCodeSelect",
    title: _ctx.translate('MobileMessaging_Settings_SelectCountry'),
    modelValue: _ctx.countryCallingCode,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.countryCallingCode = $event;
    }),
    "full-width": true,
    options: _ctx.countries
  }, null, 8, ["title", "modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_PhoneNumbers_CountryCode_Help')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_9, [ManageMobilePhoneNumbersvue_type_template_id_373eda9d_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
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
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(phoneNumber), 1), !validated && !_ctx.isActivated[index] ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
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
    }, null, 8, _hoisted_19)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.validationCode[index]]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !validated && !_ctx.isActivated[index] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
      key: 1,
      disabled: !_ctx.validationCode[index] || _ctx.isChangingPhoneNumber,
      onConfirm: function onConfirm($event) {
        return _ctx.validateActivationCode(phoneNumber, index);
      },
      value: _ctx.translate('MobileMessaging_Settings_ValidatePhoneNumber')
    }, null, 8, ["disabled", "onConfirm", "value"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      disabled: _ctx.isChangingPhoneNumber,
      onConfirm: function onConfirm($event) {
        return _ctx.removePhoneNumber(phoneNumber);
      },
      value: _ctx.translate('General_Remove'),
      style: {
        "margin-left": "3.5px"
      }
    }, null, 8, ["disabled", "onConfirm", "value"])]), !validated && !_ctx.isActivated[index] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_VerificationCodeJustSent')), 1), _hoisted_21])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isChangingPhoneNumber
  }, null, 8, ["loading"])]);
}
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue?vue&type=template&id=373eda9d

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



ManageMobilePhoneNumbersvue_type_script_lang_ts.render = ManageMobilePhoneNumbersvue_type_template_id_373eda9d_render

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