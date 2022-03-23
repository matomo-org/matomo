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
__webpack_require__.d(__webpack_exports__, "ManageSmsProvider", function() { return /* reexport */ ManageSmsProvider; });
__webpack_require__.d(__webpack_exports__, "SmsProviderCredentials", function() { return /* reexport */ SmsProviderCredentials; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=71256e6a
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
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/SmsProviderCredentials/SmsProviderCredentials.vue?vue&type=template&id=71256e6a

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
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  watch: {
    provider: function provider() {
      // unset credentials when new provider is chosen
      this.$emit('update:modelValue', {}); // fetch fields for provider

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
        return;
      }

      external_CoreHome_["AjaxHelper"].fetch({
        module: 'MobileMessaging',
        action: 'getCredentialFields',
        provider: this.provider
      }).then(function (fields) {
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=e42e6e40


var ManageSmsProvidervue_type_template_id_e42e6e40_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  id: "ajaxErrorManageSmsProviderSettings"
}, null, -1);

var _hoisted_2 = {
  key: 0
};
var _hoisted_3 = {
  key: 0
};

var _hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_5 = {
  key: 1
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = ["innerHTML"];
var _hoisted_8 = {
  key: 1
};
var _hoisted_9 = {
  id: "accountForm"
};
var _hoisted_10 = ["innerHTML"];
function ManageSmsProvidervue_type_template_id_e42e6e40_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_SmsProviderCredentials = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SmsProviderCredentials");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isDeletingAccount
  }, null, 8, ["loading"]), ManageSmsProvidervue_type_template_id_e42e6e40_hoisted_1, _ctx.credentialSupplied ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_2, [_ctx.credentialError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialInvalid', _ctx.provider)), 1), _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.credentialError), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('MobileMessaging_Settings_CredentialProvided', _ctx.provider)) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.creditLeft), 1)), _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.updateOrDeleteAccountText,
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
// CONCATENATED MODULE: ./plugins/MobileMessaging/vue/src/ManageSmsProvider/ManageSmsProvider.vue?vue&type=template&id=e42e6e40

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
      credentials: {},
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
      return !!this.smsProvider && Object.keys(this.credentials).length > 0 && Object.values(this.credentials).every(function (v) {
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



ManageSmsProvidervue_type_script_lang_ts.render = ManageSmsProvidervue_type_template_id_e42e6e40_render

/* harmony default export */ var ManageSmsProvider = (ManageSmsProvidervue_type_script_lang_ts);
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