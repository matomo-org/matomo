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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=39929da3

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
  }), 128)), _ctx.availableCapabilitiesGrouped.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [_ctx.userRole !== 'noaccess' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
    key: 0,
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
  }, null, 8, ["model-value", "disabled", "options"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_5, [_ctx.isAddingCapability ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", {
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
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=39929da3

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