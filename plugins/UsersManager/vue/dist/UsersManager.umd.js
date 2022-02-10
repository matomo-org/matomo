(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["UsersManager"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["UsersManager"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/UsersManager/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "CapabilitiesEdit", function() { return /* reexport */ CapabilitiesEdit; });
__webpack_require__.d(__webpack_exports__, "UserPermissionsEdit", function() { return /* reexport */ UserPermissionsEdit; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=259d8ccf

var _hoisted_1 = ["title"];
var _hoisted_2 = ["onClick"];
var _hoisted_3 = {
  key: 0,
  class: "addCapability"
};
var _hoisted_4 = {
  class: "ui-confirm confirmCapabilityToggle modal",
  ref: "confirmCapabilityToggleModal"
};
var _hoisted_5 = {
  class: "modal-content"
};
var _hoisted_6 = ["innerHTML"];
var _hoisted_7 = ["innerHTML"];
var _hoisted_8 = {
  class: "modal-footer"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["capabilitiesEdit", {
      busy: _ctx.isBusy
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.actualCapabilities, function (capability) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: capability.id,
      class: "chip"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "capability-name",
      title: "".concat(capability.description, " ").concat(_ctx.isIncludedInRole(capability) ? "<br/><br/>".concat(_ctx.translate('UsersManager_IncludedInUsersRole')) : '')
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.category) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.name), 9, _hoisted_1), !_ctx.isIncludedInRole(capability) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: "icon-close",
      onClick: function onClick($event) {
        _ctx.capabilityToRemoveId = capability.id;

        _ctx.onToggleCapability(false);
      }
    }, null, 8, _hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), _ctx.availableCapabilitiesGrouped.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    "model-value": _ctx.capabilityToAddId,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      _ctx.capabilityToAddId = $event;

      _ctx.onToggleCapability(true);
    }),
    disabled: _ctx.isBusy,
    uicontrol: "expandable-select",
    name: "add_capability",
    "full-width": true,
    options: _ctx.availableCapabilitiesGrouped
  }, null, 8, ["model-value", "disabled", "options"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [_ctx.isAddingCapability ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.confirmAddCapabilityToggleContent)
  }, null, 8, _hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isAddingCapability ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.confirmCapabilityToggleContent)
  }, null, 8, _hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.toggleCapability();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.capabilityToAddOrRemove = null;
      _ctx.capabilityToAddId = null;
      _ctx.capabilityToRemoveId = null;
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512)], 2);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=259d8ccf

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesStore/CapabilitiesStore.ts
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



var CapabilitiesStore_CapabilitiesStore = /*#__PURE__*/function () {
  function CapabilitiesStore() {
    var _this = this;

    _classCallCheck(this, CapabilitiesStore);

    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      capabilities: []
    }));

    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(_this.privateState);
    }));

    _defineProperty(this, "capabilities", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.capabilities;
    }));

    _defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.state.value.isLoading;
    }));

    _defineProperty(this, "fetchPromise", void 0);

    this.fetchCapabilities();
  }

  _createClass(CapabilitiesStore, [{
    key: "fetchCapabilities",
    value: function fetchCapabilities() {
      var _this2 = this;

      if (!this.fetchPromise) {
        this.privateState.isLoading = true;
        this.fetchPromise = external_CoreHome_["AjaxHelper"].fetch({
          method: 'UsersManager.getAvailableCapabilities'
        }).then(function (capabilities) {
          _this2.privateState.capabilities = capabilities;
          return _this2.capabilities.value;
        }).finally(function () {
          _this2.privateState.isLoading = false;
        });
      }

      return this.fetchPromise;
    }
  }]);

  return CapabilitiesStore;
}();

/* harmony default export */ var src_CapabilitiesStore_CapabilitiesStore = (Object(external_CoreHome_["lazyInitSingleton"])(CapabilitiesStore_CapabilitiesStore));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=script&lang=ts




var _window = window,
    $ = _window.$;
/* harmony default export */ var CapabilitiesEditvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    idsite: [String, Number],
    siteName: {
      type: String,
      required: true
    },
    userLogin: {
      type: String,
      required: true
    },
    userRole: {
      type: String,
      required: true
    },
    capabilities: Array
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  data: function data() {
    return {
      theCapabilities: this.capabilities || [],
      isBusy: false,
      isAddingCapability: false,
      capabilityToAddId: null,
      capabilityToRemoveId: null,
      capabilityToAddOrRemove: null
    };
  },
  emits: ['change'],
  watch: {
    capabilities: function capabilities(newValue) {
      if (newValue) {
        this.theCapabilities = newValue;
      }
    }
  },
  created: function created() {
    var _this = this;

    if (!this.capabilities) {
      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getUsersPlusRole',
        limit: '1',
        filter_search: this.userLogin
      }).then(function (user) {
        if (!user || !user.capabilities) {
          return [];
        }

        return user.capabilities;
      }).then(function (capabilities) {
        _this.theCapabilities = capabilities;
      }).finally(function () {
        _this.isBusy = false;
      });
    } else {
      this.theCapabilities = this.capabilities;
    }
  },
  methods: {
    onToggleCapability: function onToggleCapability(isAdd) {
      var _this2 = this;

      this.isAddingCapability = isAdd;
      var capabilityToAddOrRemoveId = isAdd ? this.capabilityToAddId : this.capabilityToRemoveId;
      this.capabilityToAddOrRemove = null;
      this.availableCapabilities.forEach(function (capability) {
        if (capability.id === capabilityToAddOrRemoveId) {
          _this2.capabilityToAddOrRemove = capability;
        }
      });

      if (this.$refs.confirmCapabilityToggleModal) {
        $(this.$refs.confirmCapabilityToggleModal).modal({
          dismissible: false,
          yes: function yes() {
            return null;
          }
        }).modal('open');
      }
    },
    toggleCapability: function toggleCapability() {
      if (this.isAddingCapability) {
        this.addCapability(this.capabilityToAddOrRemove);
      } else {
        this.removeCapability(this.capabilityToAddOrRemove);
      }
    },
    isIncludedInRole: function isIncludedInRole(capability) {
      return (capability.includedInRoles || []).indexOf(this.userRole) !== -1;
    },
    getCapabilitiesList: function getCapabilitiesList() {
      var _this3 = this;

      var result = [];
      this.availableCapabilities.forEach(function (capability) {
        if (_this3.isIncludedInRole(capability)) {
          return;
        }

        if (_this3.capabilitiesSet[capability.id]) {
          result.push(capability.id);
        }
      });
      return result;
    },
    addCapability: function addCapability(capability) {
      var _this4 = this;

      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.addCapabilities'
      }, {
        userLogin: this.userLogin,
        capabilities: capability.id,
        idSites: this.idsite
      }).then(function () {
        _this4.$emit('change', _this4.getCapabilitiesList());
      }).finally(function () {
        _this4.isBusy = false;
        _this4.capabilityToAddOrRemove = null;
        _this4.capabilityToAddId = null;
        _this4.capabilityToRemoveId = null;
      });
    },
    removeCapability: function removeCapability(capability) {
      var _this5 = this;

      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.removeCapabilities'
      }, {
        userLogin: this.userLogin,
        capabilities: capability.id,
        idSites: this.idsite
      }).then(function () {
        _this5.$emit('change', _this5.getCapabilitiesList());
      }).finally(function () {
        _this5.isBusy = false;
        _this5.capabilityToAddOrRemove = null;
        _this5.capabilityToAddId = null;
        _this5.capabilityToRemoveId = null;
      });
    }
  },
  computed: {
    availableCapabilities: function availableCapabilities() {
      return src_CapabilitiesStore_CapabilitiesStore.capabilities.value;
    },
    confirmAddCapabilityToggleContent: function confirmAddCapabilityToggleContent() {
      return Object(external_CoreHome_["translate"])('UsersManager_AreYouSureAddCapability', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : '', "</strong>"), "<strong>".concat(this.siteNameText, "</strong>"));
    },
    confirmCapabilityToggleContent: function confirmCapabilityToggleContent() {
      return Object(external_CoreHome_["translate"])('UsersManager_AreYouSureRemoveCapability', "<strong>".concat(this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : '', "</strong>"), "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.siteNameText, "</strong>"));
    },
    siteNameText: function siteNameText() {
      return external_CoreHome_["Matomo"].helper.htmlEntities(this.siteName);
    },
    availableCapabilitiesGrouped: function availableCapabilitiesGrouped() {
      var _this6 = this;

      var availableCapabilitiesGrouped = this.availableCapabilities.filter(function (c) {
        return !_this6.capabilitiesSet[c.id];
      }).map(function (c) {
        return {
          group: c.category,
          key: c.id,
          value: c.name,
          tooltip: c.description
        };
      });
      availableCapabilitiesGrouped.sort(function (lhs, rhs) {
        if (lhs.group === rhs.group) {
          if (lhs.value === rhs.value) {
            return 0;
          }

          return lhs.value < rhs.value ? -1 : 1;
        }

        return lhs.group < rhs.group ? -1 : 1;
      });
      return availableCapabilitiesGrouped;
    },
    capabilitiesSet: function capabilitiesSet() {
      var _this7 = this;

      var capabilitiesSet = {};
      var capabilities = this.theCapabilities;
      (capabilities || []).forEach(function (capability) {
        capabilitiesSet[capability] = true;
      });
      (this.availableCapabilities || []).forEach(function (capability) {
        if (_this7.isIncludedInRole(capability)) {
          capabilitiesSet[capability.id] = true;
        }
      });
      return capabilitiesSet;
    },
    actualCapabilities: function actualCapabilities() {
      var capabilitiesSet = this.capabilitiesSet;
      return this.availableCapabilities.filter(function (c) {
        return !!capabilitiesSet[c.id];
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue



CapabilitiesEditvue_type_script_lang_ts.render = render

/* harmony default export */ var CapabilitiesEdit = (CapabilitiesEditvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var CapabilitiesEdit_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: CapabilitiesEdit,
  scope: {
    idsite: {
      angularJsBind: '<'
    },
    siteName: {
      angularJsBind: '<'
    },
    userLogin: {
      angularJsBind: '<'
    },
    userRole: {
      angularJsBind: '<'
    },
    capabilities: {
      angularJsBind: '<'
    },
    onCapabilitiesChange: {
      angularJsBind: '&',
      vue: 'change'
    }
  },
  directiveName: 'piwikCapabilitiesEdit',
  restrict: 'E',
  $inject: ['$timeout'],
  events: {
    change: function change(caps, vm, scope, element, attrs, controller, $timeout) {
      $timeout(function () {
        if (scope.onCapabilitiesChange) {
          scope.onCapabilitiesChange.call({
            capabilities: caps
          });
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=template&id=43b9eb34

var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_1 = {
  key: 0,
  class: "row"
};
var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_2 = {
  class: "row to-all-websites"
};
var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_3 = {
  class: "col s12"
};
var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_4 = {
  style: {
    "margin-right": "3.5px"
  }
};
var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_5 = {
  id: "all-sites-access-select",
  style: {
    "margin-right": "3.5px"
  }
};

var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, null, -1);

var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_7 = {
  class: "filters row"
};
var UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_8 = {
  class: "col s12 m12 l8"
};
var _hoisted_9 = {
  class: "input-field bulk-actions",
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_10 = {
  id: "user-permissions-edit-bulk-actions",
  class: "dropdown-content"
};
var _hoisted_11 = {
  class: "dropdown-trigger",
  "data-target": "user-permissions-bulk-set-access"
};
var _hoisted_12 = {
  id: "user-permissions-bulk-set-access",
  class: "dropdown-content"
};
var _hoisted_13 = ["onClick"];
var _hoisted_14 = {
  class: "input-field site-filter",
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_15 = ["value", "placeholder"];
var _hoisted_16 = {
  class: "input-field access-filter",
  style: {
    "margin-right": "3.5px"
  }
};
var _hoisted_17 = {
  key: 0,
  class: "col s12 m12 l4 sites-for-permission-pagination-container"
};
var _hoisted_18 = {
  class: "sites-for-permission-pagination"
};
var _hoisted_19 = {
  class: "counter"
};
var _hoisted_20 = ["textContent"];
var _hoisted_21 = {
  class: "roles-help-notification"
};
var _hoisted_22 = ["innerHTML"];
var _hoisted_23 = {
  class: "capabilities-help-notification"
};
var _hoisted_24 = {
  id: "sitesForPermission"
};
var _hoisted_25 = {
  class: "select-cell"
};
var _hoisted_26 = {
  class: "checkbox-container"
};
var _hoisted_27 = ["checked"];

var _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);

var _hoisted_29 = {
  class: "role_header"
};
var _hoisted_30 = ["innerHTML"];

var _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

var _hoisted_32 = [_hoisted_31];
var _hoisted_33 = {
  class: "capabilities_header"
};
var _hoisted_34 = ["innerHTML"];

var _hoisted_35 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);

var _hoisted_36 = [_hoisted_35];
var _hoisted_37 = {
  key: 0,
  class: "select-all-row"
};
var _hoisted_38 = {
  colspan: "4"
};
var _hoisted_39 = {
  key: 0
};
var _hoisted_40 = ["innerHTML"];
var _hoisted_41 = ["innerHTML"];
var _hoisted_42 = {
  key: 1
};
var _hoisted_43 = ["innerHTML"];
var _hoisted_44 = ["innerHTML"];
var _hoisted_45 = {
  class: "select-cell"
};
var _hoisted_46 = {
  class: "checkbox-container"
};
var _hoisted_47 = ["id", "onUpdate:modelValue"];

var _hoisted_48 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);

var _hoisted_49 = {
  class: "role-select"
};
var _hoisted_50 = {
  class: "delete-access-confirm-modal modal",
  ref: "deleteAccessConfirmModal"
};
var _hoisted_51 = {
  class: "modal-content"
};
var _hoisted_52 = ["innerHTML"];
var _hoisted_53 = ["innerHTML"];
var _hoisted_54 = {
  class: "modal-footer"
};
var _hoisted_55 = {
  class: "change-access-confirm-modal modal",
  ref: "changeAccessConfirmModal"
};
var _hoisted_56 = {
  class: "modal-content"
};
var _hoisted_57 = ["innerHTML"];
var _hoisted_58 = ["innerHTML"];
var _hoisted_59 = {
  class: "modal-footer"
};
var _hoisted_60 = {
  class: "confirm-give-access-all-sites modal",
  ref: "confirmGiveAccessAllSitesModal"
};
var _hoisted_61 = {
  class: "modal-content"
};
var _hoisted_62 = ["innerHTML"];
var _hoisted_63 = {
  class: "modal-footer"
};
function UserPermissionsEditvue_type_template_id_43b9eb34_render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_CapabilitiesEdit = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CapabilitiesEdit");

  var _directive_dropdown_menu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("dropdown-menu");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["userPermissionsEdit", {
      loading: _ctx.isLoadingAccess
    }])
  }, [!_ctx.hasAccessToAtLeastOneSite ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    context: "warning",
    type: "transient",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Warning')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_NoAccessWarning')), 1)];
    }),
    _: 1
  })])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_GiveAccessToAll')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.allWebsitesAccssLevelSet,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
      return _ctx.allWebsitesAccssLevelSet = $event;
    }),
    uicontrol: "select",
    options: _ctx.filteredAccessLevels,
    "full-width": true
  }, null, 8, ["modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn", {
      disabled: _ctx.isGivingAccessToAllSites
    }]),
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.showChangeAccessAllSitesModal();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Apply')), 3)]), UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_OrManageIndividually')) + ":", 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_43b9eb34_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dropdown-trigger btn", {
      disabled: _ctx.isBulkActionsDisabled
    }]),
    href: "",
    "data-target": "user-permissions-edit-bulk-actions"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BulkActions')), 1)], 2), [[_directive_dropdown_menu, {
    activates: '#user-permissions-edit-bulk-actions'
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SetPermission')), 1)], 512), [[_directive_dropdown_menu, {
    activates: '#user-permissions-bulk-set-access'
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_12, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredAccessLevels, function (access) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: access.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
        _ctx.siteAccessToChange = null;
        _ctx.roleToChangeTo = access.key;

        _ctx.showChangeAccessConfirm();
      }, ["prevent"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(access.value), 9, _hoisted_13)]);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.siteAccessToChange = null;
      _ctx.roleToChangeTo = 'noaccess';

      _ctx.showRemoveAccessConfirm();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_RemovePermissions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    value: _ctx.siteNameFilter,
    onKeydown: _cache[3] || (_cache[3] = function ($event) {
      _ctx.onChangeSiteFilter($event);
    }),
    onChange: _cache[4] || (_cache[4] = function ($event) {
      _ctx.onChangeSiteFilter($event);
    }),
    placeholder: _ctx.translate('UsersManager_FilterByWebsite')
  }, null, 40, _hoisted_15)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.accessLevelFilter,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
      return _ctx.accessLevelFilter = $event;
    }),
    uicontrol: "select",
    options: _ctx.filteredSelectAccessLevels,
    "full-width": true,
    placeholder: _ctx.translate('UsersManager_FilterByAccess')
  }, null, 8, ["modelValue", "options", "placeholder"])])])]), _ctx.totalEntries > _ctx.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["prev", {
      disabled: _ctx.offset <= 0
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "pointer",
    onClick: _cache[6] || (_cache[6] = function ($event) {
      return _ctx.gotoPreviousPage();
    })
  }, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.paginationText)
  }, null, 8, _hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["next", {
      disabled: _ctx.offset + _ctx.limit >= _ctx.totalEntries
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "pointer",
    onClick: _cache[7] || (_cache[7] = function ($event) {
      return _ctx.gotoNextPage();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 2)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [_ctx.isRoleHelpToggled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
    key: 0,
    context: "info",
    type: "persistent",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.rolesHelpText)
      }, null, 8, _hoisted_22)];
    }),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [_ctx.isCapabilitiesHelpToggled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
    key: 0,
    context: "info",
    type: "persistent",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_CapabilitiesHelp')), 1)];
    }),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "checkbox",
    id: "perm_edit_select_all",
    checked: _ctx.isAllCheckboxSelected,
    onChange: _cache[8] || (_cache[8] = function ($event) {
      return _ctx.onAllCheckboxChange($event);
    })
  }, null, 40, _hoisted_27), _hoisted_28])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: "".concat(_ctx.translate('UsersManager_Role'), " ")
  }, null, 8, _hoisted_30), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      sticky: _ctx.isRoleHelpToggled
    }]),
    onClick: _cache[9] || (_cache[9] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.isRoleHelpToggled = !_ctx.isRoleHelpToggled;
    }, ["prevent"]))
  }, _hoisted_32, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: "".concat(_ctx.translate('UsersManager_Capabilities'), " ")
  }, null, 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      sticky: _ctx.isCapabilitiesHelpToggled
    }]),
    onClick: _cache[10] || (_cache[10] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.isCapabilitiesHelpToggled = !_ctx.isCapabilitiesHelpToggled;
    }, ["prevent"]))
  }, _hoisted_36, 2)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.isAllCheckboxSelected && _ctx.siteAccess.length < _ctx.totalEntries ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_38, [!_ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.theDisplayedWebsitesAreSelectedText),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_40), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[11] || (_cache[11] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected;
    }, ["prevent"])),
    innerHTML: _ctx.$sanitize(_ctx.clickToSelectAllText)
  }, null, 8, _hoisted_41)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.allWebsitesAreSelectedText),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_43), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[12] || (_cache[12] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected;
    }, ["prevent"])),
    innerHTML: _ctx.$sanitize(_ctx.clickToSelectDisplayedWebsitesText)
  }, null, 8, _hoisted_44)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.siteAccess, function (entry, index) {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: entry.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_45, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "checkbox",
      id: "perm_edit_select_row".concat(index),
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        return _ctx.selectedRows[index] = $event;
      },
      onClick: _cache[13] || (_cache[13] = function ($event) {
        return _ctx.onRowSelected();
      })
    }, null, 8, _hoisted_47), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.selectedRows[index]]]), _hoisted_48])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.site_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_49, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "model-value": entry.role,
      "onUpdate:modelValue": function onUpdateModelValue($event) {
        _ctx.onRoleChange(entry, $event);
      },
      uicontrol: "select",
      options: _ctx.filteredAccessLevels,
      "full-width": true
    }, null, 8, ["model-value", "onUpdate:modelValue", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_CapabilitiesEdit, {
      idsite: entry.idsite,
      "site-name": entry.site_name,
      "user-login": _ctx.userLogin,
      "user-role": entry.role,
      capabilities: entry.capabilities,
      onChange: _cache[14] || (_cache[14] = function ($event) {
        return _ctx.fetchAccess();
      })
    }, null, 8, ["idsite", "site-name", "user-login", "user-role", "capabilities"])])])]);
  }), 128))])], 512), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_50, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_51, [_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmSingleText)
  }, null, 8, _hoisted_52)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmMultipleText)
  }, null, 8, _hoisted_53)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_54, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[15] || (_cache[15] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.changeUserRole();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[16] || (_cache[16] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.siteAccessToChange = null;
      _ctx.roleToChangeTo = null;
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_55, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_56, [_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmSingleText)
  }, null, 8, _hoisted_57)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmMultipleText)
  }, null, 8, _hoisted_58)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_59, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[17] || (_cache[17] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.changeUserRole();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[18] || (_cache[18] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      _ctx.siteAccessToChange.role = _ctx.previousRole;
      _ctx.siteAccessToChange = null;
      _ctx.roleToChangeTo = null;
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_60, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
    innerHTML: _ctx.$sanitize(_ctx.changePermToAllSitesConfirmText)
  }, null, 8, _hoisted_62), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ChangePermToAllSitesConfirm2')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_63, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[19] || (_cache[19] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])(function ($event) {
      return _ctx.giveAccessToAllSites();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[20] || (_cache[20] = function ($event) {
      return $event.preventDefault();
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512)], 2);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=template&id=43b9eb34

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=script&lang=ts




var UserPermissionsEditvue_type_script_lang_ts_window = window,
    UserPermissionsEditvue_type_script_lang_ts_$ = UserPermissionsEditvue_type_script_lang_ts_window.$;
/* harmony default export */ var UserPermissionsEditvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    userLogin: {
      type: String,
      required: true
    },
    limit: {
      type: Number,
      default: 10
    },
    accessLevels: {
      type: Array,
      required: true
    },
    filterAccessLevels: {
      type: Array,
      required: true
    }
  },
  components: {
    Notification: external_CoreHome_["Notification"],
    Field: external_CorePluginsAdmin_["Field"],
    CapabilitiesEdit: CapabilitiesEdit
  },
  directives: {
    DropdownMenu: external_CoreHome_["DropdownMenu"],
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data: function data() {
    return {
      siteAccess: [],
      offset: 0,
      totalEntries: null,
      accessLevelFilter: '',
      siteNameFilter: '',
      isLoadingAccess: false,
      allWebsitesAccssLevelSet: 'view',
      isAllCheckboxSelected: false,
      selectedRows: {},
      isBulkActionsDisabled: true,
      areAllResultsSelected: false,
      previousRole: null,
      hasAccessToAtLeastOneSite: true,
      isRoleHelpToggled: false,
      isCapabilitiesHelpToggled: false,
      isGivingAccessToAllSites: false,
      roleToChangeTo: null,
      siteAccessToChange: null
    };
  },
  emits: ['userHasAccessDetected', 'accessChanged'],
  created: function created() {
    var _this = this;

    this.onChangeSiteFilter = Object(external_CoreHome_["debounce"])(this.onChangeSiteFilter, 300);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(function () {
      return _this.allPropsWatch;
    }, function () {
      if (_this.limit) {
        _this.fetchAccess();
      }
    });
    this.fetchAccess();
  },
  watch: {
    accessLevelFilter: function accessLevelFilter() {
      this.offset = 0;
      this.fetchAccess();
    }
  },
  methods: {
    onAllCheckboxChange: function onAllCheckboxChange(event) {
      var _this2 = this;

      this.isAllCheckboxSelected = event.target.checked;

      if (!this.isAllCheckboxSelected) {
        this.clearSelection();
      } else {
        this.siteAccess.forEach(function (e, i) {
          _this2.selectedRows[i] = true;
        });
        this.isBulkActionsDisabled = false;
      }
    },
    clearSelection: function clearSelection() {
      this.selectedRows = {};
      this.areAllResultsSelected = false;
      this.isBulkActionsDisabled = true;
      this.isAllCheckboxSelected = false;
      this.siteAccessToChange = null;
    },
    onRowSelected: function onRowSelected() {
      var _this3 = this;

      setTimeout(function () {
        var selectedRowKeyCount = _this3.selectedRowsCount;
        _this3.isBulkActionsDisabled = selectedRowKeyCount === 0;
        _this3.isAllCheckboxSelected = selectedRowKeyCount === _this3.siteAccess.length;
      });
    },
    fetchAccess: function fetchAccess() {
      var _this4 = this;

      this.isLoadingAccess = true;
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getSitesAccessForUser',
        limit: this.limit,
        offset: this.offset,
        filter_search: this.siteNameFilter,
        filter_access: this.accessLevelFilter,
        userLogin: this.userLogin
      }, {
        returnResponseObject: true
      }).then(function (helper) {
        var result = helper.getRequestHandle();
        _this4.isLoadingAccess = false;
        _this4.siteAccess = result.responseJSON;
        _this4.totalEntries = parseInt(result.getResponseHeader('x-matomo-total-results'), 10) || 0;
        _this4.hasAccessToAtLeastOneSite = !!result.getResponseHeader('x-matomo-has-some');

        _this4.$emit('userHasAccessDetected', {
          hasAccess: _this4.hasAccessToAtLeastOneSite
        });

        _this4.clearSelection();
      }).catch(function () {
        _this4.isLoadingAccess = false;

        _this4.clearSelection();
      });
    },
    gotoPreviousPage: function gotoPreviousPage() {
      this.offset = Math.max(0, this.offset - this.limit);
      this.fetchAccess();
    },
    gotoNextPage: function gotoNextPage() {
      var newOffset = this.offset + this.limit;

      if (newOffset >= (this.totalEntries || 0)) {
        return;
      }

      this.offset = newOffset;
      this.fetchAccess();
    },
    showRemoveAccessConfirm: function showRemoveAccessConfirm() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.deleteAccessConfirmModal).modal({
        dismissible: false
      }).modal('open');
    },
    changeUserRole: function changeUserRole() {
      var _this5 = this;

      var getSelectedSites = function getSelectedSites() {
        var result = [];
        Object.keys(_this5.selectedRows).forEach(function (index) {
          if (_this5.selectedRows[index] && _this5.siteAccess[index] // safety check
          ) {
            result.push(_this5.siteAccess[index].idsite);
          }
        });
        return result;
      };

      var getAllSitesInSearch = function getAllSitesInSearch() {
        return external_CoreHome_["AjaxHelper"].fetch({
          method: 'UsersManager.getSitesAccessForUser',
          filter_search: _this5.siteNameFilter,
          filter_access: _this5.accessLevelFilter,
          userLogin: _this5.userLogin,
          filter_limit: '-1'
        }).then(function (access) {
          return access.map(function (a) {
            return a.idsite;
          });
        });
      };

      this.isLoadingAccess = true;
      return Promise.resolve().then(function () {
        if (_this5.siteAccessToChange) {
          return [_this5.siteAccessToChange.idsite];
        }

        if (_this5.areAllResultsSelected) {
          return getAllSitesInSearch();
        }

        return getSelectedSites();
      }).then(function (idSites) {
        return external_CoreHome_["AjaxHelper"].post({
          method: 'UsersManager.setUserAccess'
        }, {
          userLogin: _this5.userLogin,
          access: _this5.roleToChangeTo,
          idSites: idSites
        });
      }).catch(function () {// ignore (errors will still be displayed to the user)
      }).then(function () {
        _this5.$emit('accessChanged');

        return _this5.fetchAccess();
      });
    },
    showChangeAccessConfirm: function showChangeAccessConfirm() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.changeAccessConfirmModal).modal({
        dismissible: false
      }).modal('open');
    },
    getRoleDisplay: function getRoleDisplay(role) {
      var result = null;
      this.filteredAccessLevels.forEach(function (entry) {
        if (entry.key === role) {
          result = entry.value;
        }
      });
      return result;
    },
    giveAccessToAllSites: function giveAccessToAllSites() {
      var _this6 = this;

      this.isGivingAccessToAllSites = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getSitesWithAdminAccess'
      }).then(function (allSites) {
        var idSites = allSites.map(function (s) {
          return s.idsite;
        });
        return external_CoreHome_["AjaxHelper"].post({
          method: 'UsersManager.setUserAccess'
        }, {
          userLogin: _this6.userLogin,
          access: _this6.allWebsitesAccssLevelSet,
          idSites: idSites
        });
      }).then(function () {
        return _this6.fetchAccess();
      }).finally(function () {
        _this6.isGivingAccessToAllSites = false;
      });
    },
    showChangeAccessAllSitesModal: function showChangeAccessAllSitesModal() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.confirmGiveAccessAllSitesModal).modal({
        dismissible: false
      }).modal('open');
    },
    onChangeSiteFilter: function onChangeSiteFilter(event) {
      var _this7 = this;

      setTimeout(function () {
        var inputValue = event.target.value;

        if (_this7.siteNameFilter !== inputValue) {
          _this7.siteNameFilter = inputValue;
          _this7.offset = 0;

          _this7.fetchAccess();
        }
      });
    },
    onRoleChange: function onRoleChange(entry, newRole) {
      this.previousRole = entry.role;
      this.roleToChangeTo = newRole;
      this.siteAccessToChange = entry;
      this.showChangeAccessConfirm();
    }
  },
  computed: {
    rolesHelpText: function rolesHelpText() {
      return Object(external_CoreHome_["translate"])('UsersManager_RolesHelp', '<a href="https://matomo.org/faq/general/faq_70/" target="_blank" rel="noreferrer noopener">', '</a>', '<a href="https://matomo.org/faq/general/faq_69/" target="_blank" rel="noreferrer noopener">', '</a>');
    },
    theDisplayedWebsitesAreSelectedText: function theDisplayedWebsitesAreSelectedText() {
      var text = Object(external_CoreHome_["translate"])('UsersManager_TheDisplayedWebsitesAreSelected', "<strong>".concat(this.siteAccess.length, "</strong>"));
      return "".concat(text, " ");
    },
    clickToSelectAllText: function clickToSelectAllText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ClickToSelectAll', "<strong>".concat(this.totalEntries, "</strong>"));
    },
    allWebsitesAreSelectedText: function allWebsitesAreSelectedText() {
      return Object(external_CoreHome_["translate"])('UsersManager_AllWebsitesAreSelected', "<strong>".concat(this.totalEntries, "</strong>"));
    },
    clickToSelectDisplayedWebsitesText: function clickToSelectDisplayedWebsitesText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ClickToSelectDisplayedWebsites', "<strong>".concat(this.siteAccess.length, "</strong>"));
    },
    deletePermConfirmSingleText: function deletePermConfirmSingleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_DeletePermConfirmSingle', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.siteAccessToChangeName, "</strong>"));
    },
    deletePermConfirmMultipleText: function deletePermConfirmMultipleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_DeletePermConfirmMultiple', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.affectedSitesCount, "</strong>"));
    },
    changePermToSiteConfirmSingleText: function changePermToSiteConfirmSingleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToSiteConfirmSingle', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.siteAccessToChangeName, "</strong>"), "<strong>".concat(this.getRoleDisplay(this.roleToChangeTo), "</strong>"));
    },
    changePermToSiteConfirmMultipleText: function changePermToSiteConfirmMultipleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToSiteConfirmMultiple', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.affectedSitesCount, "</strong>"), "<strong>".concat(this.getRoleDisplay(this.roleToChangeTo), "</strong>"));
    },
    changePermToAllSitesConfirmText: function changePermToAllSitesConfirmText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToAllSitesConfirm', "<strong>".concat(this.userLogin, "</strong>"), "<strong>".concat(this.getRoleDisplay(this.allWebsitesAccssLevelSet), "</strong>"));
    },
    paginationLowerBound: function paginationLowerBound() {
      return this.offset + 1;
    },
    paginationUpperBound: function paginationUpperBound() {
      if (!this.totalEntries) {
        return '?';
      }

      return Math.min(this.offset + this.limit, this.totalEntries);
    },
    filteredAccessLevels: function filteredAccessLevels() {
      return this.accessLevels.filter(function (entry) {
        return entry.key !== 'superuser';
      });
    },
    filteredSelectAccessLevels: function filteredSelectAccessLevels() {
      return this.filterAccessLevels.filter(function (entry) {
        return entry.key !== 'superuser';
      });
    },
    selectedRowsCount: function selectedRowsCount() {
      var selectedRowKeyCount = 0;
      Object.values(this.selectedRows).forEach(function (v) {
        if (v) {
          selectedRowKeyCount += 1;
        }
      });
      return selectedRowKeyCount;
    },
    affectedSitesCount: function affectedSitesCount() {
      if (this.areAllResultsSelected) {
        return this.totalEntries;
      }

      return this.selectedRowsCount;
    },
    allPropsWatch: function allPropsWatch() {
      // see https://github.com/vuejs/vue/issues/844#issuecomment-390500758
      // eslint-disable-next-line no-sequences
      return this.userLogin, this.limit, this.accessLevels, this.filterAccessLevels, Date.now();
    },
    siteAccessToChangeName: function siteAccessToChangeName() {
      return this.siteAccessToChange ? external_CoreHome_["Matomo"].helper.htmlEntities(this.siteAccessToChange.site_name) : '';
    },
    paginationText: function paginationText() {
      var text = Object(external_CoreHome_["translate"])('General_Pagination', "".concat(this.paginationLowerBound), "".concat(this.paginationUpperBound), "".concat(this.totalEntries));
      return " ".concat(text, " ");
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue



UserPermissionsEditvue_type_script_lang_ts.render = UserPermissionsEditvue_type_template_id_43b9eb34_render

/* harmony default export */ var UserPermissionsEdit = (UserPermissionsEditvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var UserPermissionsEdit_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: UserPermissionsEdit,
  scope: {
    userLogin: {
      angularJsBind: '<'
    },
    limit: {
      angularJsBind: '<'
    },
    onUserHasAccessDetected: {
      angularJsBind: '&',
      vue: 'userHasAccessDetected'
    },
    onAccessChange: {
      angularJsBind: '&',
      vue: 'accessChanged'
    },
    accessLevels: {
      angularJsBind: '<'
    },
    filterAccessLevels: {
      angularJsBind: '<'
    }
  },
  directiveName: 'piwikUserPermissionsEdit',
  restrict: 'E'
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/index.ts
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
//# sourceMappingURL=UsersManager.umd.js.map