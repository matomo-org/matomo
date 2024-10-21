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
__webpack_require__.d(__webpack_exports__, "UserEditForm", function() { return /* reexport */ UserEditForm; });
__webpack_require__.d(__webpack_exports__, "PagedUsersList", function() { return /* reexport */ PagedUsersList; });
__webpack_require__.d(__webpack_exports__, "UsersManager", function() { return /* reexport */ UsersManager; });
__webpack_require__.d(__webpack_exports__, "AnonymousSettings", function() { return /* reexport */ AnonymousSettings; });
__webpack_require__.d(__webpack_exports__, "NewsletterSettings", function() { return /* reexport */ NewsletterSettings; });
__webpack_require__.d(__webpack_exports__, "PersonalSettings", function() { return /* reexport */ PersonalSettings; });
__webpack_require__.d(__webpack_exports__, "AddNewToken", function() { return /* reexport */ AddNewToken; });
__webpack_require__.d(__webpack_exports__, "AddNewTokenSuccess", function() { return /* reexport */ AddNewTokenSuccess; });
__webpack_require__.d(__webpack_exports__, "UserSecurity", function() { return /* reexport */ UserSecurity; });
__webpack_require__.d(__webpack_exports__, "UserSettings", function() { return /* reexport */ UserSettings; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=c86c842a

const _hoisted_1 = ["title"];
const _hoisted_2 = ["onClick"];
const _hoisted_3 = {
  key: 0,
  class: "addCapability"
};
const _hoisted_4 = {
  class: "ui-confirm confirmCapabilityToggle modal",
  ref: "confirmCapabilityToggleModal"
};
const _hoisted_5 = {
  class: "modal-content"
};
const _hoisted_6 = ["innerHTML"];
const _hoisted_7 = ["innerHTML"];
const _hoisted_8 = {
  class: "modal-footer"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["capabilitiesEdit", {
      busy: _ctx.isBusy
    }])
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.actualCapabilities, capability => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: capability.id,
      class: "chip"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "capability-name",
      title: `${capability.description} ${_ctx.isIncludedInRole(capability) ? `<br/><br/>${_ctx.translate('UsersManager_IncludedInUsersRole')}` : ''}`
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.category) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(capability.name), 9, _hoisted_1), !_ctx.isIncludedInRole(capability) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: "icon-close",
      onClick: $event => {
        _ctx.capabilityToRemoveId = capability.id;
        _ctx.onToggleCapability(false);
      }
    }, null, 8, _hoisted_2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128)), _ctx.availableCapabilitiesGrouped.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [_ctx.userRole !== 'noaccess' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
    key: 0,
    "model-value": _ctx.capabilityToAddId,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => {
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
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.toggleCapability(), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      _ctx.capabilityToAddOrRemove = null;
      _ctx.capabilityToAddId = null;
      _ctx.capabilityToRemoveId = null;
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512)], 2);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=template&id=c86c842a

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesStore/CapabilitiesStore.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


class CapabilitiesStore_CapabilitiesStore {
  constructor() {
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({
      isLoading: false,
      capabilities: []
    }));
    _defineProperty(this, "state", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.privateState)));
    _defineProperty(this, "capabilities", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.capabilities));
    _defineProperty(this, "isLoading", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.state.value.isLoading));
    _defineProperty(this, "fetchPromise", void 0);
  }
  init() {
    return this.fetchCapabilities();
  }
  fetchCapabilities() {
    if (!this.fetchPromise) {
      this.privateState.isLoading = true;
      this.fetchPromise = external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getAvailableCapabilities'
      }).then(capabilities => {
        this.privateState.capabilities = capabilities;
        return this.capabilities.value;
      }).finally(() => {
        this.privateState.isLoading = false;
      });
    }
    return this.fetchPromise;
  }
}
/* harmony default export */ var src_CapabilitiesStore_CapabilitiesStore = (new CapabilitiesStore_CapabilitiesStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=script&lang=ts




const {
  $
} = window;
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
  data() {
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
    capabilities(newValue) {
      if (newValue) {
        this.theCapabilities = newValue;
      }
    }
  },
  created() {
    src_CapabilitiesStore_CapabilitiesStore.init();
    if (!this.capabilities) {
      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getUsersPlusRole',
        limit: '1',
        filter_search: this.userLogin
      }).then(user => {
        if (!user || !user.capabilities) {
          return [];
        }
        return user.capabilities;
      }).then(capabilities => {
        this.theCapabilities = capabilities;
      }).finally(() => {
        this.isBusy = false;
      });
    } else {
      this.theCapabilities = this.capabilities;
    }
  },
  methods: {
    onToggleCapability(isAdd) {
      this.isAddingCapability = isAdd;
      const capabilityToAddOrRemoveId = isAdd ? this.capabilityToAddId : this.capabilityToRemoveId;
      this.capabilityToAddOrRemove = null;
      this.availableCapabilities.forEach(capability => {
        if (capability.id === capabilityToAddOrRemoveId) {
          this.capabilityToAddOrRemove = capability;
        }
      });
      if (this.$refs.confirmCapabilityToggleModal) {
        $(this.$refs.confirmCapabilityToggleModal).modal({
          dismissible: false,
          yes: () => null
        }).modal('open');
      }
    },
    toggleCapability() {
      if (this.isAddingCapability) {
        this.addCapability(this.capabilityToAddOrRemove);
      } else {
        this.removeCapability(this.capabilityToAddOrRemove);
      }
    },
    isIncludedInRole(capability) {
      return (capability.includedInRoles || []).indexOf(this.userRole) !== -1;
    },
    getCapabilitiesList() {
      const result = [];
      this.availableCapabilities.forEach(capability => {
        if (this.isIncludedInRole(capability)) {
          return;
        }
        if (this.capabilitiesSet[capability.id]) {
          result.push(capability.id);
        }
      });
      return result;
    },
    addCapability(capability) {
      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.addCapabilities'
      }, {
        userLogin: this.userLogin,
        capabilities: capability.id,
        idSites: this.idsite
      }).then(() => {
        this.$emit('change', this.getCapabilitiesList());
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddId = null;
        this.capabilityToRemoveId = null;
      });
    },
    removeCapability(capability) {
      this.isBusy = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.removeCapabilities'
      }, {
        userLogin: this.userLogin,
        capabilities: capability.id,
        idSites: this.idsite
      }).then(() => {
        this.$emit('change', this.getCapabilitiesList());
      }).finally(() => {
        this.isBusy = false;
        this.capabilityToAddOrRemove = null;
        this.capabilityToAddId = null;
        this.capabilityToRemoveId = null;
      });
    }
  },
  computed: {
    availableCapabilities() {
      return src_CapabilitiesStore_CapabilitiesStore.capabilities.value;
    },
    confirmAddCapabilityToggleContent() {
      return Object(external_CoreHome_["translate"])('UsersManager_AreYouSureAddCapability', `<strong>${this.userLogin}</strong>`, `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ''}</strong>`, `<strong>${this.siteNameText}</strong>`);
    },
    confirmCapabilityToggleContent() {
      return Object(external_CoreHome_["translate"])('UsersManager_AreYouSureRemoveCapability', `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ''}</strong>`, `<strong>${this.userLogin}</strong>`, `<strong>${this.siteNameText}</strong>`);
    },
    siteNameText() {
      return external_CoreHome_["Matomo"].helper.htmlEntities(this.siteName);
    },
    availableCapabilitiesGrouped() {
      const availableCapabilitiesGrouped = this.availableCapabilities.filter(c => !this.capabilitiesSet[c.id]).map(c => ({
        group: c.category,
        key: c.id,
        value: c.name,
        tooltip: c.description
      }));
      availableCapabilitiesGrouped.sort((lhs, rhs) => {
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
    capabilitiesSet() {
      const capabilitiesSet = {};
      const capabilities = this.theCapabilities;
      (capabilities || []).forEach(capability => {
        capabilitiesSet[capability] = true;
      });
      (this.availableCapabilities || []).forEach(capability => {
        if (this.isIncludedInRole(capability)) {
          capabilitiesSet[capability.id] = true;
        }
      });
      return capabilitiesSet;
    },
    actualCapabilities() {
      const {
        capabilitiesSet
      } = this;
      return this.availableCapabilities.filter(c => !!capabilitiesSet[c.id]);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/CapabilitiesEdit/CapabilitiesEdit.vue



CapabilitiesEditvue_type_script_lang_ts.render = render

/* harmony default export */ var CapabilitiesEdit = (CapabilitiesEditvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=template&id=da62b99e

const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_1 = {
  key: 0,
  class: "row"
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_2 = {
  class: "row to-all-websites"
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_3 = {
  class: "col s12"
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_4 = {
  style: {
    "margin-right": "3.5px"
  }
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_5 = {
  id: "all-sites-access-select",
  style: {
    "margin-right": "3.5px"
  }
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_6 = {
  style: {
    "margin-top": "18px"
  }
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_7 = {
  class: "filters row"
};
const UserPermissionsEditvue_type_template_id_da62b99e_hoisted_8 = {
  class: "col s12 m12 l8"
};
const _hoisted_9 = {
  class: "input-field bulk-actions",
  style: {
    "margin-right": "3.5px"
  }
};
const _hoisted_10 = {
  id: "user-permissions-edit-bulk-actions",
  class: "dropdown-content"
};
const _hoisted_11 = {
  class: "dropdown-trigger",
  "data-target": "user-permissions-bulk-set-access"
};
const _hoisted_12 = {
  id: "user-permissions-bulk-set-access",
  class: "dropdown-content"
};
const _hoisted_13 = ["onClick"];
const _hoisted_14 = {
  class: "input-field site-filter",
  style: {
    "margin-right": "3.5px"
  }
};
const _hoisted_15 = ["value", "placeholder"];
const _hoisted_16 = {
  class: "input-field access-filter",
  style: {
    "margin-right": "3.5px"
  }
};
const _hoisted_17 = {
  key: 0,
  class: "col s12 m12 l4 sites-for-permission-pagination-container"
};
const _hoisted_18 = {
  class: "sites-for-permission-pagination"
};
const _hoisted_19 = {
  class: "counter"
};
const _hoisted_20 = ["textContent"];
const _hoisted_21 = {
  class: "roles-help-notification"
};
const _hoisted_22 = ["innerHTML"];
const _hoisted_23 = {
  class: "capabilities-help-notification"
};
const _hoisted_24 = {
  id: "sitesForPermission"
};
const _hoisted_25 = {
  class: "select-cell"
};
const _hoisted_26 = {
  class: "checkbox-container"
};
const _hoisted_27 = ["checked"];
const _hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const _hoisted_29 = {
  class: "role_header"
};
const _hoisted_30 = ["innerHTML"];
const _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const _hoisted_32 = [_hoisted_31];
const _hoisted_33 = {
  class: "capabilities_header"
};
const _hoisted_34 = ["innerHTML"];
const _hoisted_35 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const _hoisted_36 = [_hoisted_35];
const _hoisted_37 = {
  key: 0,
  class: "select-all-row"
};
const _hoisted_38 = {
  colspan: "4"
};
const _hoisted_39 = {
  key: 0
};
const _hoisted_40 = ["innerHTML"];
const _hoisted_41 = ["innerHTML"];
const _hoisted_42 = {
  key: 1
};
const _hoisted_43 = ["innerHTML"];
const _hoisted_44 = ["innerHTML"];
const _hoisted_45 = {
  class: "select-cell"
};
const _hoisted_46 = {
  class: "checkbox-container"
};
const _hoisted_47 = ["id", "onUpdate:modelValue"];
const _hoisted_48 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const _hoisted_49 = {
  class: "role-select"
};
const _hoisted_50 = {
  class: "delete-access-confirm-modal modal",
  ref: "deleteAccessConfirmModal"
};
const _hoisted_51 = {
  class: "modal-content"
};
const _hoisted_52 = ["innerHTML"];
const _hoisted_53 = ["innerHTML"];
const _hoisted_54 = {
  class: "modal-footer"
};
const _hoisted_55 = {
  class: "change-access-confirm-modal modal",
  ref: "changeAccessConfirmModal"
};
const _hoisted_56 = {
  class: "modal-content"
};
const _hoisted_57 = ["innerHTML"];
const _hoisted_58 = ["innerHTML"];
const _hoisted_59 = {
  class: "modal-footer"
};
const _hoisted_60 = {
  class: "confirm-give-access-all-sites modal",
  ref: "confirmGiveAccessAllSitesModal"
};
const _hoisted_61 = {
  class: "modal-content"
};
const _hoisted_62 = ["innerHTML"];
const _hoisted_63 = {
  class: "modal-footer"
};
function UserPermissionsEditvue_type_template_id_da62b99e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_CapabilitiesEdit = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CapabilitiesEdit");
  const _directive_dropdown_menu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("dropdown-menu");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["userPermissionsEdit", {
      loading: _ctx.isLoadingAccess
    }])
  }, [!_ctx.hasAccessToAtLeastOneSite ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    context: "warning",
    type: "transient",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Warning')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_NoAccessWarning')), 1)]),
    _: 1
  })])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_GiveAccessToAll')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.allWebsitesAccssLevelSet,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.allWebsitesAccssLevelSet = $event),
    uicontrol: "select",
    options: _ctx.filteredAccessLevels,
    "full-width": true
  }, null, 8, ["modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn", {
      disabled: _ctx.isGivingAccessToAllSites
    }]),
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showChangeAccessAllSitesModal(), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Apply')), 3)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_OrManageIndividually')) + ":", 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserPermissionsEditvue_type_template_id_da62b99e_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dropdown-trigger btn", {
      disabled: _ctx.isBulkActionsDisabled
    }]),
    href: "",
    "data-target": "user-permissions-edit-bulk-actions"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BulkActions')), 1)], 2)), [[_directive_dropdown_menu, {
    activates: '#user-permissions-edit-bulk-actions'
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SetPermission')), 1)])), [[_directive_dropdown_menu, {
    activates: '#user-permissions-bulk-set-access'
  }]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", _hoisted_12, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filteredAccessLevels, access => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: access.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
        _ctx.siteAccessToChange = null;
        _ctx.roleToChangeTo = access.key;
        _ctx.showChangeAccessConfirm();
      }, ["prevent"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(access.value), 9, _hoisted_13)]);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      _ctx.siteAccessToChange = null;
      _ctx.roleToChangeTo = 'noaccess';
      _ctx.showRemoveAccessConfirm();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_RemovePermissions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "text",
    value: _ctx.siteNameFilter,
    onKeydown: _cache[3] || (_cache[3] = $event => {
      _ctx.onChangeSiteFilter($event);
    }),
    onChange: _cache[4] || (_cache[4] = $event => {
      _ctx.onChangeSiteFilter($event);
    }),
    placeholder: _ctx.translate('UsersManager_FilterByWebsite')
  }, null, 40, _hoisted_15)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.accessLevelFilter,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.accessLevelFilter = $event),
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
    onClick: _cache[6] || (_cache[6] = $event => _ctx.gotoPreviousPage())
  }, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    textContent: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.paginationText)
  }, null, 8, _hoisted_20)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["next", {
      disabled: _ctx.offset + _ctx.limit >= _ctx.totalEntries
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "pointer",
    onClick: _cache[7] || (_cache[7] = $event => _ctx.gotoNextPage())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 2)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [_ctx.isRoleHelpToggled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
    key: 0,
    context: "info",
    type: "persistent",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.rolesHelpText)
    }, null, 8, _hoisted_22)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_23, [_ctx.isCapabilitiesHelpToggled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
    key: 0,
    context: "info",
    type: "persistent",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_CapabilitiesHelp')), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    type: "checkbox",
    id: "perm_edit_select_all",
    checked: _ctx.isAllCheckboxSelected,
    onChange: _cache[8] || (_cache[8] = $event => _ctx.onAllCheckboxChange($event))
  }, null, 40, _hoisted_27), _hoisted_28])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_29, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(`${_ctx.translate('UsersManager_Role')} `)
  }, null, 8, _hoisted_30), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      sticky: _ctx.isRoleHelpToggled
    }]),
    onClick: _cache[9] || (_cache[9] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.isRoleHelpToggled = !_ctx.isRoleHelpToggled, ["prevent"]))
  }, _hoisted_32, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(`${_ctx.translate('UsersManager_Capabilities')} `)
  }, null, 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
      sticky: _ctx.isCapabilitiesHelpToggled
    }]),
    onClick: _cache[10] || (_cache[10] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.isCapabilitiesHelpToggled = !_ctx.isCapabilitiesHelpToggled, ["prevent"]))
  }, _hoisted_36, 2)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.isAllCheckboxSelected && _ctx.siteAccess.length < _ctx.totalEntries ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_38, [!_ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.theDisplayedWebsitesAreSelectedText),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_40), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[11] || (_cache[11] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
    innerHTML: _ctx.$sanitize(_ctx.clickToSelectAllText)
  }, null, 8, _hoisted_41)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.allWebsitesAreSelectedText),
    style: {
      "margin-right": "3.5px"
    }
  }, null, 8, _hoisted_43), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "#",
    onClick: _cache[12] || (_cache[12] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
    innerHTML: _ctx.$sanitize(_ctx.clickToSelectDisplayedWebsitesText)
  }, null, 8, _hoisted_44)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.siteAccess, (entry, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: entry.idsite
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_45, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "checkbox",
      id: `perm_edit_select_row${index}`,
      "onUpdate:modelValue": $event => _ctx.selectedRows[index] = $event,
      onClick: _cache[13] || (_cache[13] = $event => _ctx.onRowSelected())
    }, null, 8, _hoisted_47), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.selectedRows[index]]]), _hoisted_48])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(entry.site_name), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_49, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      "model-value": entry.role,
      "onUpdate:modelValue": $event => {
        _ctx.onRoleChange(entry, $event);
      },
      "model-modifiers": {
        abortable: true
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
      onChange: _cache[14] || (_cache[14] = $event => _ctx.fetchAccess())
    }, null, 8, ["idsite", "site-name", "user-login", "user-role", "capabilities"])])])]);
  }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_50, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_51, [_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmSingleText)
  }, null, 8, _hoisted_52)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.siteAccessToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmMultipleText)
  }, null, 8, _hoisted_53)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_54, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[15] || (_cache[15] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.changeUserRole(), ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[16] || (_cache[16] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
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
    onClick: _cache[17] || (_cache[17] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.changeUserRole(), ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[18] || (_cache[18] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      _ctx.accessChangeEvent && _ctx.accessChangeEvent.abort();
      _ctx.siteAccessToChange = null;
      _ctx.roleToChangeTo = null;
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_60, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", {
    innerHTML: _ctx.$sanitize(_ctx.changePermToAllSitesConfirmText)
  }, null, 8, _hoisted_62), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ChangePermToAllSitesConfirm2')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_63, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[19] || (_cache[19] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.giveAccessToAllSites(), ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[20] || (_cache[20] = $event => $event.preventDefault())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512)], 2);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=template&id=da62b99e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=script&lang=ts




const {
  $: UserPermissionsEditvue_type_script_lang_ts_$
} = window;
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
  data() {
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
      accessChangeEvent: null,
      hasAccessToAtLeastOneSite: true,
      isRoleHelpToggled: false,
      isCapabilitiesHelpToggled: false,
      isGivingAccessToAllSites: false,
      roleToChangeTo: null,
      siteAccessToChange: null
    };
  },
  emits: ['userHasAccessDetected', 'accessChanged'],
  created() {
    this.onChangeSiteFilter = Object(external_CoreHome_["debounce"])(this.onChangeSiteFilter, 300);
    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["watch"])(() => this.allPropsWatch, () => {
      if (this.limit) {
        this.fetchAccess();
      }
    });
    this.fetchAccess();
  },
  watch: {
    accessLevelFilter() {
      this.offset = 0;
      this.fetchAccess();
    }
  },
  methods: {
    onAllCheckboxChange(event) {
      this.isAllCheckboxSelected = event.target.checked;
      if (!this.isAllCheckboxSelected) {
        this.clearSelection();
      } else {
        this.siteAccess.forEach((e, i) => {
          this.selectedRows[i] = true;
        });
        this.isBulkActionsDisabled = false;
      }
    },
    clearSelection() {
      this.selectedRows = {};
      this.areAllResultsSelected = false;
      this.isBulkActionsDisabled = true;
      this.isAllCheckboxSelected = false;
      this.siteAccessToChange = null;
    },
    onRowSelected() {
      setTimeout(() => {
        const selectedRowKeyCount = this.selectedRowsCount;
        this.isBulkActionsDisabled = selectedRowKeyCount === 0;
        this.isAllCheckboxSelected = selectedRowKeyCount === this.siteAccess.length;
      });
    },
    fetchAccess() {
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
      }).then(helper => {
        const result = helper.getRequestHandle();
        this.isLoadingAccess = false;
        this.siteAccess = result.responseJSON;
        this.totalEntries = parseInt(result.getResponseHeader('x-matomo-total-results'), 10) || 0;
        this.hasAccessToAtLeastOneSite = !!result.getResponseHeader('x-matomo-has-some');
        this.$emit('userHasAccessDetected', {
          hasAccess: this.hasAccessToAtLeastOneSite
        });
        this.clearSelection();
      }).catch(() => {
        this.isLoadingAccess = false;
        this.clearSelection();
      });
    },
    gotoPreviousPage() {
      this.offset = Math.max(0, this.offset - this.limit);
      this.fetchAccess();
    },
    gotoNextPage() {
      const newOffset = this.offset + this.limit;
      if (newOffset >= (this.totalEntries || 0)) {
        return;
      }
      this.offset = newOffset;
      this.fetchAccess();
    },
    showRemoveAccessConfirm() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.deleteAccessConfirmModal).modal({
        dismissible: false
      }).modal('open');
    },
    changeUserRole() {
      const getSelectedSites = () => {
        const result = [];
        Object.keys(this.selectedRows).forEach(index => {
          if (this.selectedRows[index] && this.siteAccess[index] // safety check
          ) {
            result.push(this.siteAccess[index].idsite);
          }
        });
        return result;
      };
      const getAllSitesInSearch = () => external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getSitesAccessForUser',
        filter_search: this.siteNameFilter,
        filter_access: this.accessLevelFilter,
        userLogin: this.userLogin,
        filter_limit: '-1'
      }).then(access => access.map(a => a.idsite));
      this.isLoadingAccess = true;
      return Promise.resolve().then(() => {
        if (this.siteAccessToChange) {
          return [this.siteAccessToChange.idsite];
        }
        if (this.areAllResultsSelected) {
          return getAllSitesInSearch();
        }
        return getSelectedSites();
      }).then(idSites => external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.setUserAccess'
      }, {
        userLogin: this.userLogin,
        access: this.roleToChangeTo,
        idSites
      })).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).then(() => {
        this.$emit('accessChanged');
        return this.fetchAccess();
      });
    },
    showChangeAccessConfirm() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.changeAccessConfirmModal).modal({
        dismissible: false,
        onCloseEnd: () => {
          this.accessChangeEvent = null;
        }
      }).modal('open');
    },
    getRoleDisplay(role) {
      let result = null;
      this.filteredAccessLevels.forEach(entry => {
        if (entry.key === role) {
          result = entry.value;
        }
      });
      return result;
    },
    giveAccessToAllSites() {
      this.isGivingAccessToAllSites = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'SitesManager.getSitesWithAdminAccess',
        filter_limit: -1
      }).then(allSites => {
        const idSites = allSites.map(s => s.idsite);
        return external_CoreHome_["AjaxHelper"].post({
          method: 'UsersManager.setUserAccess'
        }, {
          userLogin: this.userLogin,
          access: this.allWebsitesAccssLevelSet,
          idSites
        });
      }).then(() => this.fetchAccess()).finally(() => {
        this.isGivingAccessToAllSites = false;
      });
    },
    showChangeAccessAllSitesModal() {
      UserPermissionsEditvue_type_script_lang_ts_$(this.$refs.confirmGiveAccessAllSitesModal).modal({
        dismissible: false
      }).modal('open');
    },
    onChangeSiteFilter(event) {
      setTimeout(() => {
        const inputValue = event.target.value;
        if (this.siteNameFilter !== inputValue) {
          this.siteNameFilter = inputValue;
          this.offset = 0;
          this.fetchAccess();
        }
      });
    },
    onRoleChange(entry, event) {
      this.siteAccessToChange = entry;
      this.roleToChangeTo = event.value;
      this.accessChangeEvent = event;
      this.showChangeAccessConfirm();
    }
  },
  computed: {
    rolesHelpText() {
      return Object(external_CoreHome_["translate"])('UsersManager_RolesHelp', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_70/'), '</a>', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_69/'), '</a>');
    },
    theDisplayedWebsitesAreSelectedText() {
      const text = Object(external_CoreHome_["translate"])('UsersManager_TheDisplayedWebsitesAreSelected', `<strong>${this.siteAccess.length}</strong>`);
      return `${text} `;
    },
    clickToSelectAllText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ClickToSelectAll', `<strong>${this.totalEntries}</strong>`);
    },
    allWebsitesAreSelectedText() {
      return Object(external_CoreHome_["translate"])('UsersManager_AllWebsitesAreSelected', `<strong>${this.totalEntries}</strong>`);
    },
    clickToSelectDisplayedWebsitesText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ClickToSelectDisplayedWebsites', `<strong>${this.siteAccess.length}</strong>`);
    },
    deletePermConfirmSingleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_DeletePermConfirmSingle', `<strong>${this.userLogin}</strong>`, `<strong>${this.siteAccessToChangeName}</strong>`);
    },
    deletePermConfirmMultipleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_DeletePermConfirmMultiple', `<strong>${this.userLogin}</strong>`, `<strong>${this.affectedSitesCount}</strong>`);
    },
    changePermToSiteConfirmSingleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToSiteConfirmSingle', `<strong>${this.userLogin}</strong>`, `<strong>${this.siteAccessToChangeName}</strong>`, `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`);
    },
    changePermToSiteConfirmMultipleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToSiteConfirmMultiple', `<strong>${this.userLogin}</strong>`, `<strong>${this.affectedSitesCount}</strong>`, `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`);
    },
    changePermToAllSitesConfirmText() {
      return Object(external_CoreHome_["translate"])('UsersManager_ChangePermToAllSitesConfirm', `<strong>${this.userLogin}</strong>`, `<strong>${this.getRoleDisplay(this.allWebsitesAccssLevelSet)}</strong>`);
    },
    paginationLowerBound() {
      return this.offset + 1;
    },
    paginationUpperBound() {
      if (!this.totalEntries) {
        return '?';
      }
      return Math.min(this.offset + this.limit, this.totalEntries);
    },
    filteredAccessLevels() {
      return this.accessLevels.filter(entry => entry.key !== 'superuser' && entry.type === 'role');
    },
    filteredSelectAccessLevels() {
      return this.filterAccessLevels.filter(entry => entry.key !== 'superuser');
    },
    selectedRowsCount() {
      let selectedRowKeyCount = 0;
      Object.values(this.selectedRows).forEach(v => {
        if (v) {
          selectedRowKeyCount += 1;
        }
      });
      return selectedRowKeyCount;
    },
    affectedSitesCount() {
      if (this.areAllResultsSelected) {
        return this.totalEntries;
      }
      return this.selectedRowsCount;
    },
    allPropsWatch() {
      // see https://github.com/vuejs/vue/issues/844#issuecomment-390500758
      // eslint-disable-next-line no-sequences
      return this.userLogin, this.limit, this.accessLevels, this.filterAccessLevels, Date.now();
    },
    siteAccessToChangeName() {
      return this.siteAccessToChange ? external_CoreHome_["Matomo"].helper.htmlEntities(this.siteAccessToChange.site_name) : '';
    },
    paginationText() {
      const text = Object(external_CoreHome_["translate"])('General_Pagination', `${this.paginationLowerBound}`, `${this.paginationUpperBound}`, `${this.totalEntries}`);
      return ` ${text} `;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserPermissionsEdit/UserPermissionsEdit.vue



UserPermissionsEditvue_type_script_lang_ts.render = UserPermissionsEditvue_type_template_id_da62b99e_render

/* harmony default export */ var UserPermissionsEdit = (UserPermissionsEditvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserEditForm/UserEditForm.vue?vue&type=template&id=b96898cc

const UserEditFormvue_type_template_id_b96898cc_hoisted_1 = {
  class: "row"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_2 = {
  key: 0,
  class: "col s12 m6 invite-notes"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_3 = {
  class: "form-help"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_4 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_5 = {
  key: 1,
  class: "col m2 entityList"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_6 = {
  class: "listCircle"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_7 = {
  key: 0,
  class: "icon-warning"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "save-button-spacer hide-on-small-only"
}, null, -1);
const UserEditFormvue_type_template_id_b96898cc_hoisted_9 = {
  href: "",
  class: "entityCancelLink"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-arrow-left"
}, "  ", -1);
const UserEditFormvue_type_template_id_b96898cc_hoisted_11 = {
  class: "visibleTab col m10"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_12 = {
  key: 0,
  class: "basic-info-tab"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_13 = {
  class: "email-input"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_14 = {
  class: "form-group row",
  style: {
    "position": "relative"
  }
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_15 = {
  class: "col s12 m6 save-button"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_16 = {
  key: 0,
  class: "resend-notes"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_17 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_18 = {
  key: 0,
  class: "entityCancel"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon icon-arrow-left"
}, "  ", -1);
const UserEditFormvue_type_template_id_b96898cc_hoisted_20 = {
  key: 1,
  class: "user-permissions"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_21 = {
  key: 0
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_22 = {
  key: 1,
  class: "alert alert-info"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_23 = {
  key: 2,
  class: "superuser-access form-group"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_24 = {
  key: 0
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_25 = {
  key: 1
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_26 = {
  class: "browser-default"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_27 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_28 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_29 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_30 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_31 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_32 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_33 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_34 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_35 = {
  key: 0
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_36 = {
  key: 1
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_37 = {
  key: 3,
  class: "twofa-reset form-group"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_38 = {
  class: "resetTwoFa"
};
const UserEditFormvue_type_template_id_b96898cc_hoisted_39 = ["innerHTML"];
const UserEditFormvue_type_template_id_b96898cc_hoisted_40 = ["innerHTML"];
function UserEditFormvue_type_template_id_b96898cc_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _component_UserPermissionsEdit = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("UserPermissionsEdit");
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["userEditForm", {
      loading: _ctx.isSavingUserInfo
    }]),
    "content-title": `${_ctx.formTitle} ${!_ctx.isAdd ? `${_ctx.theUser.login}` : ''}`
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_1, [_ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_InviteSuccessNotification', [_ctx.inviteTokenExpiryDays]))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_4)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", UserEditFormvue_type_template_id_b96898cc_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
        active: _ctx.activeTab === 'basic'
      }, "menuBasicInfo"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.activeTab = 'basic', ["prevent"]))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BasicInformation')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
        active: _ctx.activeTab === 'permissions'
      }, "menuPermissions"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.activeTab = 'permissions', ["prevent"])),
      style: {
        "margin-right": "3.5px"
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_Permissions')), 1), !_ctx.userHasAccess && !_ctx.theUser.superuser_access ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UserEditFormvue_type_template_id_b96898cc_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 0,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
        active: _ctx.activeTab === 'superuser'
      }, "menuSuperuser"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: _cache[2] || (_cache[2] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.activeTab = 'superuser', ["prevent"]))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUserAccess')), 1)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' && _ctx.theUser.uses_2fa && !_ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 1,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([{
        active: _ctx.activeTab === '2fa'
      }, "menuUserTwoFa"])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.activeTab = '2fa', ["prevent"]))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_TwoFactorAuthentication')), 1)], 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), UserEditFormvue_type_template_id_b96898cc_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "entityCancel",
      onClick: _cache[4] || (_cache[4] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onDoneEditing(), ["prevent"]))
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", UserEditFormvue_type_template_id_b96898cc_hoisted_9, [UserEditFormvue_type_template_id_b96898cc_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BackToUser')), 1)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_11, [_ctx.activeTab === 'basic' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      modelValue: _ctx.theUser.login,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.theUser.login = $event),
      disabled: _ctx.isSavingUserInfo || !_ctx.isAdd || _ctx.isShowingPasswordConfirm,
      autocomplete: "off",
      uicontrol: "text",
      name: "user_login",
      maxlength: 100,
      title: _ctx.translate('General_Username')
    }, null, 8, ["modelValue", "disabled", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [!_ctx.isPending ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: 0,
      "model-value": _ctx.theUser.password,
      disabled: _ctx.isSavingUserInfo || _ctx.currentUserRole !== 'superuser' && !_ctx.isAdd || _ctx.isShowingPasswordConfirm,
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => {
        _ctx.theUser.password = $event;
        _ctx.isPasswordModified = true;
      }),
      uicontrol: "password",
      name: "user_password",
      autocomplete: "new-password",
      title: _ctx.translate('General_Password')
    }, null, 8, ["model-value", "disabled", "title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_13, [_ctx.currentUserRole === 'superuser' || _ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: 0,
      modelValue: _ctx.theUser.email,
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.theUser.email = $event),
      disabled: _ctx.isSavingUserInfo || _ctx.currentUserRole !== 'superuser' && !_ctx.isAdd || _ctx.isShowingPasswordConfirm,
      uicontrol: "text",
      name: "user_email",
      autocomplete: "off",
      maxlength: 100,
      title: _ctx.translate('UsersManager_Email')
    }, null, 8, ["modelValue", "disabled", "title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: 0,
      modelValue: _ctx.firstSiteAccess,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.firstSiteAccess = $event),
      disabled: _ctx.isSavingUserInfo,
      uicontrol: "site",
      name: "user_site",
      "ui-control-attributes": {
        onlySitesWithAdminAccess: true
      },
      title: _ctx.translate('UsersManager_FirstWebsitePermission'),
      "inline-help": _ctx.translate('UsersManager_FirstSiteInlineHelp')
    }, null, 8, ["modelValue", "disabled", "title", "inline-help"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_15, [_ctx.currentUserRole === 'superuser' || _ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveButton, {
      key: 0,
      value: _ctx.saveButtonLabel,
      disabled: _ctx.isAdd && (!_ctx.firstSiteAccess || !_ctx.firstSiteAccess.id),
      saving: _ctx.isSavingUserInfo,
      onConfirm: _ctx.saveUserInfo
    }, null, 8, ["value", "disabled", "saving", "onConfirm"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), _ctx.user && _ctx.isPending ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserEditFormvue_type_template_id_b96898cc_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_InvitationSent')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: "resend-link",
      onClick: _cache[9] || (_cache[9] = (...args) => _ctx.resendRequestedUser && _ctx.resendRequestedUser(...args)),
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_ResendInvite') + '/' + _ctx.translate('UsersManager_CopyLink'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_17)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmationForInviteUser,
      "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => _ctx.showPasswordConfirmationForInviteUser = $event),
      onConfirmed: _ctx.inviteUser
    }, null, 8, ["modelValue", "onConfirmed"])]), _ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      class: "entityCancelLink",
      onClick: _cache[11] || (_cache[11] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.onDoneEditing(), ["prevent"]))
    }, [UserEditFormvue_type_template_id_b96898cc_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BackToUser')), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isAdd ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_20, [!_ctx.theUser.superuser_access ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_UserPermissionsEdit, {
      "user-login": _ctx.theUser.login,
      onUserHasAccessDetected: _cache[12] || (_cache[12] = $event => _ctx.userHasAccess = $event.hasAccess),
      onAccessChanged: _cache[13] || (_cache[13] = $event => _ctx.isUserModified = true),
      "access-levels": _ctx.accessLevels,
      "filter-access-levels": _ctx.filterAccessLevels
    }, null, 8, ["user-login", "access-levels", "filter-access-levels"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.theUser.superuser_access ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUsersPermissionsNotice')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.activeTab === 'permissions']]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.activeTab === 'superuser' && _ctx.currentUserRole === 'superuser' && !_ctx.isAdd ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_23, [_ctx.isMarketplacePluginEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserEditFormvue_type_template_id_b96898cc_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUserIntro1')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserEditFormvue_type_template_id_b96898cc_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUserIntro1WithoutMarketplace')), 1)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUserIntro2')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SuperUserIntro3')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", UserEditFormvue_type_template_id_b96898cc_hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('Data'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_27), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('Security'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_28), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('Misconfiguration'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_29), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('UserManagement'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_30), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('ServiceDisruption'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_31), _ctx.isPluginsAdminEnabled && _ctx.isMarketplacePluginEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('Marketplace'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_32)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.accountabilityRisk)
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_33), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", {
      innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString('Compliance'))
    }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_34)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      modelValue: _ctx.superUserAccessChecked,
      "onUpdate:modelValue": _cache[14] || (_cache[14] = $event => _ctx.superUserAccessChecked = $event),
      onClick: _cache[15] || (_cache[15] = $event => _ctx.confirmSuperUserChange()),
      disabled: _ctx.isSavingUserInfo,
      uicontrol: "checkbox",
      name: "superuser_access",
      title: _ctx.translate('UsersManager_HasSuperUserAccess')
    }, null, 8, ["modelValue", "disabled", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmationForSuperUser,
      "onUpdate:modelValue": _cache[16] || (_cache[16] = $event => _ctx.showPasswordConfirmationForSuperUser = $event),
      onConfirmed: _ctx.toggleSuperuserAccess,
      onAborted: _cache[17] || (_cache[17] = $event => _ctx.setSuperUserAccessChecked())
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_AreYouSure')), 1), _ctx.theUser.superuser_access ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserEditFormvue_type_template_id_b96898cc_hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_RemoveSuperuserAccessConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.theUser.superuser_access ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UserEditFormvue_type_template_id_b96898cc_hoisted_36, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_AddSuperuserAccessConfirm')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }, 8, ["modelValue", "onConfirmed"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' && !_ctx.isAdd ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ResetTwoFactorAuthenticationInfo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserEditFormvue_type_template_id_b96898cc_hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.isResetting2FA,
      onConfirm: _cache[18] || (_cache[18] = $event => _ctx.confirmReset2FA()),
      value: _ctx.translate('UsersManager_ResetTwoFactorAuthentication')
    }, null, 8, ["saving", "value"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmationFor2FA,
      "onUpdate:modelValue": _cache[19] || (_cache[19] = $event => _ctx.showPasswordConfirmationFor2FA = $event),
      onConfirmed: _ctx.reset2FA
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_AreYouSure')), 1)]),
      _: 1
    }, 8, ["modelValue", "onConfirmed"])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.activeTab === '2fa']]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])), [[_directive_form]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.isShowingPasswordConfirm,
      "onUpdate:modelValue": _cache[20] || (_cache[20] = $event => _ctx.isShowingPasswordConfirm = $event),
      onConfirmed: _ctx.updateUser
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
        innerHTML: _ctx.$sanitize(_ctx.changePasswordTitle)
      }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_39), _ctx.user && _ctx.isPending ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Notification, {
        key: 0,
        context: "info",
        noclear: true
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", {
          innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_InviteEmailChange'))
        }, null, 8, UserEditFormvue_type_template_id_b96898cc_hoisted_40)]),
        _: 1
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 1
    }, 8, ["modelValue", "onConfirmed"])]),
    _: 1
  }, 8, ["class", "content-title"]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserEditForm/UserEditForm.vue?vue&type=template&id=b96898cc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserEditForm/UserEditForm.vue?vue&type=script&lang=ts
function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }




const DEFAULT_USER = {
  login: '',
  superuser_access: false,
  uses_2fa: false,
  password: '',
  email: '',
  invite_status: ''
};
/* harmony default export */ var UserEditFormvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    user: Object,
    currentUserRole: {
      type: String,
      required: true
    },
    accessLevels: {
      type: Array,
      required: true
    },
    filterAccessLevels: {
      type: Array,
      required: true
    },
    initialSiteId: {
      type: [String, Number],
      required: true
    },
    initialSiteName: {
      type: String,
      required: true
    },
    inviteTokenExpiryDays: {
      type: String,
      required: true
    },
    activatedPlugins: {
      type: Array,
      required: true
    }
  },
  components: {
    Notification: external_CoreHome_["Notification"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    UserPermissionsEdit: UserPermissionsEdit,
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      theUser: this.user || _extends({}, DEFAULT_USER),
      activeTab: 'basic',
      permissionsForIdSite: 1,
      isSavingUserInfo: false,
      userHasAccess: true,
      firstSiteAccess: {
        id: this.initialSiteId,
        name: this.initialSiteName
      },
      isUserModified: false,
      isPasswordModified: false,
      superUserAccessChecked: null,
      showPasswordConfirmationForSuperUser: false,
      showPasswordConfirmationFor2FA: false,
      showPasswordConfirmationForInviteUser: false,
      isResetting2FA: false,
      isShowingPasswordConfirm: false
    };
  },
  emits: ['done', 'updated', 'resendInvite'],
  watch: {
    user(newVal) {
      this.onUserChange(newVal);
    }
  },
  created() {
    this.onUserChange(this.user);
  },
  methods: {
    onUserChange(newVal) {
      this.theUser = newVal || _extends({}, DEFAULT_USER);
      if (!this.theUser.password) {
        this.resetPasswordVar();
      }
      this.setSuperUserAccessChecked();
    },
    confirmSuperUserChange() {
      this.showPasswordConfirmationForSuperUser = true;
    },
    confirmReset2FA() {
      this.showPasswordConfirmationFor2FA = true;
    },
    toggleSuperuserAccess(password) {
      this.isSavingUserInfo = true;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.setSuperUserAccess'
      }, {
        userLogin: this.theUser.login,
        hasSuperUserAccess: this.theUser.superuser_access ? '0' : '1',
        passwordConfirmation: password
      }).then(() => {
        this.theUser.superuser_access = !this.theUser.superuser_access;
      }).catch(() => {
        // ignore error (still displayed to user)
      }).then(() => {
        this.isSavingUserInfo = false;
        this.setSuperUserAccessChecked();
      });
    },
    saveUserInfo() {
      if (this.isAdd) {
        this.showPasswordConfirmationForInviteUser = true;
      } else {
        this.isShowingPasswordConfirm = true;
      }
    },
    resendRequestedUser() {
      this.$emit('resendInvite', {
        user: this.user
      });
    },
    inviteUser(password) {
      this.isSavingUserInfo = true;
      return external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.inviteUser'
      }, {
        userLogin: this.theUser.login,
        email: this.theUser.email,
        initialIdSite: this.firstSiteAccess ? this.firstSiteAccess.id : undefined,
        passwordConfirmation: password
      }).catch(e => {
        this.isSavingUserInfo = false;
        throw e;
      }).then(() => {
        this.firstSiteAccess = null;
        this.isSavingUserInfo = false;
        this.isUserModified = true;
        this.theUser.invite_status = 'pending';
        this.resetPasswordVar();
        this.showUserCreatedNotification();
        this.$emit('updated', {
          user: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.theUser)
        });
      });
    },
    resetPasswordVar() {
      if (!this.isAdd) {
        // make sure password is not stored in the client after update/save
        this.theUser.password = 'XXXXXXXX';
      }
    },
    showUserSavedNotification() {
      external_CoreHome_["NotificationsStore"].show({
        message: Object(external_CoreHome_["translate"])('General_YourChangesHaveBeenSaved'),
        context: 'success',
        type: 'toast'
      });
    },
    showUserCreatedNotification() {
      external_CoreHome_["NotificationsStore"].show({
        message: Object(external_CoreHome_["translate"])('UsersManager_InviteSuccess'),
        context: 'success',
        type: 'toast'
      });
    },
    reset2FA(password) {
      this.isResetting2FA = true;
      return external_CoreHome_["AjaxHelper"].post({
        method: 'TwoFactorAuth.resetTwoFactorAuth'
      }, {
        userLogin: this.theUser.login,
        passwordConfirmation: password
      }).catch(e => {
        this.isResetting2FA = false;
        throw e;
      }).then(() => {
        this.isResetting2FA = false;
        this.theUser.uses_2fa = false;
        this.activeTab = 'basic';
        this.showUserSavedNotification();
      });
    },
    updateUser(password) {
      this.isSavingUserInfo = true;
      return external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.updateUser'
      }, {
        userLogin: this.theUser.login,
        password: this.isPasswordModified && this.theUser.password ? this.theUser.password : undefined,
        passwordConfirmation: password,
        email: this.theUser.email
      }).then(() => {
        this.isSavingUserInfo = false;
        this.isUserModified = true;
        this.isPasswordModified = false;
        this.resetPasswordVar();
        this.showUserSavedNotification();
        this.$emit('updated', {
          user: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["readonly"])(this.theUser)
        });
      }).catch(() => {
        this.isSavingUserInfo = false;
      });
    },
    setSuperUserAccessChecked() {
      this.superUserAccessChecked = !!this.theUser.superuser_access;
    },
    onDoneEditing() {
      this.$emit('done', {
        isUserModified: this.isUserModified
      });
    },
    translateSuperUserRiskString(item) {
      return Object(external_CoreHome_["translate"])(`UsersManager_SuperUserRisk${item}`, '<strong>', '</strong>');
    }
  },
  computed: {
    formTitle() {
      return this.isAdd ? Object(external_CoreHome_["translate"])('UsersManager_InviteNewUser') : '';
    },
    saveButtonLabel() {
      return this.isAdd ? Object(external_CoreHome_["translate"])('UsersManager_InviteUser') : Object(external_CoreHome_["translate"])('UsersManager_SaveBasicInfo');
    },
    isPending() {
      if (!this.user) {
        return true;
      }
      if (this.user.invite_status === 'pending' || Number.isInteger(this.user.invite_status)) {
        return true;
      }
      return false;
    },
    isAdd() {
      return !this.user;
    },
    changePasswordTitle() {
      return Object(external_CoreHome_["translate"])('UsersManager_AreYouSureChangeDetails', `<strong>${this.theUser.login}</strong>`);
    },
    isPluginsAdminEnabled() {
      return external_CoreHome_["Matomo"].config.enable_plugins_admin;
    },
    isActivityLogPluginEnabled() {
      return this.activatedPlugins.includes('ActivityLog');
    },
    isMarketplacePluginEnabled() {
      return this.activatedPlugins.includes('Marketplace');
    },
    isProfessionalServicesPluginEnabled() {
      return this.activatedPlugins.includes('ProfessionalServices');
    },
    accountabilityRisk() {
      const riskInfo = this.translateSuperUserRiskString('Accountability');
      let pluginInfo = '';
      if (this.isPluginsAdminEnabled && this.isProfessionalServicesPluginEnabled) {
        if (this.isActivityLogPluginEnabled) {
          pluginInfo = Object(external_CoreHome_["translate"])('UsersManager_SuperUserRiskAccountabilityCheckActivityLog', '<a href="?module=ActivityLog&action=index" rel="noreferrer noopener" target="_blank">', '</a>');
        } else if (this.isMarketplacePluginEnabled) {
          pluginInfo = Object(external_CoreHome_["translate"])('UsersManager_SuperUserRiskAccountabilityGetActivityLogPlugin', Object(external_CoreHome_["externalLink"])('https://plugins.matomo.org/ActivityLog'), '</a>');
        }
      }
      return pluginInfo ? `${riskInfo} ${pluginInfo}` : riskInfo;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserEditForm/UserEditForm.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserEditForm/UserEditForm.vue



UserEditFormvue_type_script_lang_ts.render = UserEditFormvue_type_template_id_b96898cc_render

/* harmony default export */ var UserEditForm = (UserEditFormvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.vue?vue&type=template&id=3c24998f

const PagedUsersListvue_type_template_id_3c24998f_hoisted_1 = {
  class: "userListFilters row"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_2 = {
  class: "col s12 m12 l8"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_3 = {
  class: "input-field col s12 m3 l3"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_4 = {
  id: "user-list-bulk-actions",
  class: "dropdown-content"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_5 = {
  class: "dropdown-trigger",
  "data-target": "bulk-set-access"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_6 = {
  id: "bulk-set-access",
  class: "dropdown-content"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_7 = ["onClick"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_8 = {
  key: 0
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_9 = {
  class: "input-field col s12 m3 l3"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_10 = {
  class: "permissions-for-selector"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_11 = {
  class: "input-field col s12 m3 l3"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_12 = {
  class: "input-field col s12 m3 l3"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_13 = {
  key: 0,
  class: "input-field col s12 m12 l4 users-list-pagination-container"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_14 = {
  class: "usersListPagination"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_15 = {
  class: "pointer"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_16 = {
  class: "counter"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_17 = {
  class: "pointer"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_18 = {
  key: 0,
  class: "roles-help-notification"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_19 = ["innerHTML"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_20 = {
  class: "select-cell"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_21 = {
  class: "checkbox-container"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_23 = {
  class: "first"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_24 = {
  class: "role_header"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_25 = {
  style: {
    "margin-right": "3.5px"
  }
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help"
}, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_27 = [PagedUsersListvue_type_template_id_3c24998f_hoisted_26];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_28 = {
  key: 0
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_29 = ["title"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_30 = {
  key: 2
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_31 = {
  class: "actions-cell-header"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_32 = {
  key: 0,
  class: "select-all-row"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_33 = {
  colspan: "8"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_34 = {
  key: 0
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_35 = ["innerHTML"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_36 = ["innerHTML"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_37 = {
  key: 1
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_38 = ["innerHTML"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_39 = ["innerHTML"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_40 = ["id"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_41 = {
  class: "select-cell"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_42 = {
  class: "checkbox-container"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_43 = ["id", "onUpdate:modelValue"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_44 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_45 = {
  id: "userLogin"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_46 = {
  class: "access-cell"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_47 = {
  key: 0,
  id: "email"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_48 = {
  key: 1,
  id: "twofa"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_49 = {
  key: 0,
  class: "icon-ok"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_50 = {
  key: 1,
  class: "icon-close"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_51 = {
  key: 2,
  id: "last_seen"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_52 = {
  id: "status"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_53 = ["title"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_54 = {
  class: "center actions-cell"
};
const PagedUsersListvue_type_template_id_3c24998f_hoisted_55 = ["onClick"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_56 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-email"
}, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_57 = [PagedUsersListvue_type_template_id_3c24998f_hoisted_56];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_58 = ["onClick"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_59 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_60 = [PagedUsersListvue_type_template_id_3c24998f_hoisted_59];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_61 = ["onClick"];
const PagedUsersListvue_type_template_id_3c24998f_hoisted_62 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);
const PagedUsersListvue_type_template_id_3c24998f_hoisted_63 = [PagedUsersListvue_type_template_id_3c24998f_hoisted_62];
const _hoisted_64 = ["innerHTML"];
const _hoisted_65 = ["innerHTML"];
const _hoisted_66 = ["innerHTML"];
const _hoisted_67 = ["innerHTML"];
const _hoisted_68 = ["innerHTML"];
const _hoisted_69 = {
  class: "change-user-role-confirm-modal modal",
  ref: "changeUserRoleConfirmModal"
};
const _hoisted_70 = {
  class: "modal-content"
};
const _hoisted_71 = ["innerHTML"];
const _hoisted_72 = ["innerHTML"];
const _hoisted_73 = {
  class: "modal-footer"
};
function PagedUsersListvue_type_template_id_3c24998f_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Notification = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Notification");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _directive_dropdown_menu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("dropdown-menu");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["pagedUsersList", {
      loading: _ctx.isLoadingUsers
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dropdown-trigger btn bulk-actions", {
      disabled: _ctx.isBulkActionsDisabled
    }]),
    href: "",
    "data-target": "user-list-bulk-actions"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_BulkActions')), 1)], 2)), [[_directive_dropdown_menu]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", PagedUsersListvue_type_template_id_3c24998f_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", PagedUsersListvue_type_template_id_3c24998f_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SetPermission')), 1)])), [[_directive_dropdown_menu]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", PagedUsersListvue_type_template_id_3c24998f_hoisted_6, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.bulkActionAccessLevels, access => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: access.key
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      onClick: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
        _ctx.userToChange = null;
        _ctx.roleToChangeTo = access.key;
        _ctx.showAccessChangeConfirm();
      }, ["prevent"])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(access.value), 9, PagedUsersListvue_type_template_id_3c24998f_hoisted_7)]);
  }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[0] || (_cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => {
      _ctx.userToChange = null;
      _ctx.roleToChangeTo = 'noaccess';
      _ctx.showAccessChangeConfirm();
    }, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_RemovePermissions')), 1)]), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", PagedUsersListvue_type_template_id_3c24998f_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    onClick: _cache[1] || (_cache[1] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.showDeleteConfirm(), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_DeleteUsers')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    "model-value": _ctx.userTextFilter,
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.onUserTextFilterChange($event)),
    name: "user-text-filter",
    uicontrol: "text",
    "full-width": true,
    placeholder: _ctx.translate('UsersManager_UserSearch')
  }, null, 8, ["model-value", "placeholder"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    "model-value": _ctx.accessLevelFilter,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
      _ctx.accessLevelFilter = $event;
      _ctx.changeSearch({
        filter_access: _ctx.accessLevelFilter,
        offset: 0
      });
    }),
    name: "access-level-filter",
    uicontrol: "select",
    options: _ctx.filterAccessLevels,
    "full-width": true,
    placeholder: _ctx.translate('UsersManager_FilterByAccess')
  }, null, 8, ["model-value", "options", "placeholder"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    "model-value": _ctx.statusLevelFilter,
    "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => {
      _ctx.statusLevelFilter = $event;
      _ctx.changeSearch({
        filter_status: _ctx.statusLevelFilter,
        offset: 0
      });
    }),
    name: "status-level-filter",
    uicontrol: "select",
    options: _ctx.filterStatusLevels,
    "full-width": true,
    placeholder: _ctx.translate('UsersManager_FilterByStatus')
  }, null, 8, ["model-value", "options", "placeholder"])])])]), _ctx.totalEntries > _ctx.searchParams.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn prev", {
      disabled: _ctx.searchParams.offset <= 0
    }]),
    onClick: _cache[5] || (_cache[5] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.gotoPreviousPage(), ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_15, "« " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Previous')), 1)], 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      visibility: _ctx.isLoadingUsers ? 'hidden' : 'visible'
    })
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Pagination', _ctx.paginationLowerBound, _ctx.paginationUpperBound, _ctx.totalEntries)), 3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.isLoadingUsers
  }, null, 8, ["loading"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn next", {
      disabled: _ctx.searchParams.offset + _ctx.searchParams.limit >= _ctx.totalEntries
    }]),
    onClick: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.gotoNextPage(), ["prevent"]))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Next')) + " »", 1)], 2)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.isRoleHelpToggled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Notification, {
    context: "info",
    type: "persistent",
    noclear: true
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.rolesHelpText)
    }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_19)]),
    _: 1
  })])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", {
      id: "manageUsersTable",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        loading: _ctx.isLoadingUsers
      })
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "checkbox",
      id: "paged_users_select_all",
      checked: "checked",
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.isAllCheckboxSelected = $event),
      onChange: _cache[8] || (_cache[8] = $event => _ctx.onAllCheckboxChange())
    }, null, 544), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.isAllCheckboxSelected]]), PagedUsersListvue_type_template_id_3c24998f_hoisted_22])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_Username')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_RoleFor')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: "",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["helpIcon", {
        sticky: _ctx.isRoleHelpToggled
      }]),
      onClick: _cache[9] || (_cache[9] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.isRoleHelpToggled = !_ctx.isRoleHelpToggled, ["prevent"]))
    }, PagedUsersListvue_type_template_id_3c24998f_hoisted_27, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      class: "permissions-for-selector",
      "model-value": _ctx.permissionsForSite,
      "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => {
        _ctx.onPermissionsForUpdate($event);
      }),
      uicontrol: "site",
      "ui-control-attributes": {
        onlySitesWithAdminAccess: _ctx.currentUserRole !== 'superuser'
      }
    }, null, 8, ["model-value", "ui-control-attributes"])])]), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_Email')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
      key: 1,
      title: _ctx.translate('UsersManager_UsesTwoFactorAuthentication')
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_2FA')), 9, PagedUsersListvue_type_template_id_3c24998f_hoisted_29)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_30, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_LastSeen')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_Status')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PagedUsersListvue_type_template_id_3c24998f_hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Actions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.isAllCheckboxSelected && _ctx.users.length && _ctx.users.length < _ctx.totalEntries ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", PagedUsersListvue_type_template_id_3c24998f_hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_33, [!_ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_TheDisplayedUsersAreSelected', `<strong>${_ctx.users.length}</strong>`)),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_35), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "toggle-select-all-in-search",
      href: "#",
      onClick: _cache[11] || (_cache[11] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_ClickToSelectAll', `<strong>${_ctx.totalEntries}</strong>`))
    }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_36)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.areAllResultsSelected ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PagedUsersListvue_type_template_id_3c24998f_hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_AllUsersAreSelected', `<strong>${_ctx.totalEntries}</strong>`)),
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_38), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "toggle-select-all-in-search",
      href: "#",
      onClick: _cache[12] || (_cache[12] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_ClickToSelectDisplayedUsers', `<strong>${_ctx.users.length}</strong>`))
    }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_39)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.users, (user, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        id: `row${index}`,
        key: user.login
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: "checkbox",
        id: `paged_users_select_row${index}`,
        "onUpdate:modelValue": $event => _ctx.selectedRows[index] = $event,
        onClick: _cache[13] || (_cache[13] = $event => _ctx.onRowSelected())
      }, null, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_43), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], _ctx.selectedRows[index]]]), PagedUsersListvue_type_template_id_3c24998f_hoisted_44])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_45, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(user.login), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        "model-value": user.role,
        "onUpdate:modelValue": $event => {
          _ctx.userToChange = user;
          _ctx.roleToChangeTo = $event.value;
          _ctx.showAccessChangeConfirm();
          $event.abort();
        },
        "model-modifiers": {
          abortable: true
        },
        disabled: user.role === 'superuser',
        uicontrol: "select",
        options: user.login === 'anonymous' ? _ctx.anonymousAccessLevels : user.role === 'noaccess' ? _ctx.onlyRoleAccessLevels : _ctx.accessLevels
      }, null, 8, ["model-value", "onUpdate:modelValue", "disabled", "options"])])]), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_47, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(user.email), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_48, [user.uses_2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_49)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !user.uses_2fa ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PagedUsersListvue_type_template_id_3c24998f_hoisted_50)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_51, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(user.last_seen ? `${user.last_seen} ago` : '-'), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_52, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(Number.isInteger(user.invite_status) ? 'pending' : user.invite_status),
        title: user.invite_status === 'expired' ? _ctx.translate('UsersManager_ExpiredInviteAutomaticallyRemoved', '3') : ''
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.getInviteStatus(user.invite_status)), 11, PagedUsersListvue_type_template_id_3c24998f_hoisted_53)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PagedUsersListvue_type_template_id_3c24998f_hoisted_54, [(_ctx.currentUserRole === 'superuser' || _ctx.currentUserRole === 'admin' && user.invited_by === _ctx.currentUserLogin) && user.invite_status !== 'active' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 0,
        class: "resend table-action",
        title: "Resend/Copy Invite Link",
        onClick: $event => {
          _ctx.userToChange = user;
          _ctx.resendRequestedUser();
        }
      }, PagedUsersListvue_type_template_id_3c24998f_hoisted_57, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_55)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), user.login !== 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 1,
        class: "edituser table-action",
        title: "Edit",
        onClick: $event => _ctx.$emit('editUser', {
          user: user
        })
      }, PagedUsersListvue_type_template_id_3c24998f_hoisted_60, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_58)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (_ctx.currentUserRole === 'superuser' || _ctx.currentUserRole === 'admin' && user.invited_by === _ctx.currentUserLogin) && user.login !== 'anonymous' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 2,
        class: "deleteuser table-action",
        title: "Delete",
        onClick: $event => {
          _ctx.userToChange = user;
          _ctx.showDeleteConfirm();
        }
      }, PagedUsersListvue_type_template_id_3c24998f_hoisted_63, 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_61)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 8, PagedUsersListvue_type_template_id_3c24998f_hoisted_40);
    }), 128))])], 2)), [[_directive_content_table]])]),
    _: 1
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmationForUserRemoval,
    "onUpdate:modelValue": _cache[14] || (_cache[14] = $event => _ctx.showPasswordConfirmationForUserRemoval = $event),
    onConfirmed: _ctx.deleteRequestedUsers,
    onAborted: _ctx.resetUserAndRoleToChange
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_DeleteUserConfirmSingle', `<strong>${_ctx.userToChange.login}</strong>`))
    }, null, 8, _hoisted_64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", {
      key: 1,
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_DeleteUserConfirmMultiple', `<strong>${_ctx.affectedUsersCount}</strong>`))
    }, null, 8, _hoisted_65)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["modelValue", "onConfirmed", "onAborted"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmationForAnonymousAccess,
    "onUpdate:modelValue": _cache[15] || (_cache[15] = $event => _ctx.showPasswordConfirmationForAnonymousAccess = $event),
    onConfirmed: _ctx.changeUserRole,
    onAborted: _ctx.resetUserAndRoleToChange
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmSingleText)
    }, null, 8, _hoisted_66)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
      key: 1,
      innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmMultipleText)
    }, null, 8, _hoisted_67)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("em", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Note')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_AnonymousUserRoleChangeWarning', 'anonymous', _ctx.getRoleDisplay(_ctx.roleToChangeTo)))
    }, null, 8, _hoisted_68)])])]),
    _: 1
  }, 8, ["modelValue", "onConfirmed", "onAborted"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_69, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_70, [_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h3", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmSingleText)
  }, null, 8, _hoisted_71)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.userToChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 1,
    innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmMultipleText)
  }, null, 8, _hoisted_72)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_73, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[16] || (_cache[16] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.changeUserRole(), ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Yes')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[17] || (_cache[17] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.resetUserAndRoleToChange(), ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_No')), 1)])], 512)], 2);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.vue?vue&type=template&id=3c24998f

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.vue?vue&type=script&lang=ts
function PagedUsersListvue_type_script_lang_ts_extends() { PagedUsersListvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return PagedUsersListvue_type_script_lang_ts_extends.apply(this, arguments); }



const {
  $: PagedUsersListvue_type_script_lang_ts_$
} = window;
/* harmony default export */ var PagedUsersListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    initialSiteId: {
      type: [String, Number],
      required: true
    },
    initialSiteName: {
      type: String,
      required: true
    },
    currentUserRole: String,
    isLoadingUsers: Boolean,
    accessLevels: {
      type: Array,
      required: true
    },
    filterAccessLevels: {
      type: Array,
      required: true
    },
    filterStatusLevels: {
      type: Array,
      required: true
    },
    totalEntries: Number,
    users: {
      type: Array,
      required: true
    },
    searchParams: {
      type: Object,
      required: true
    }
  },
  components: {
    Field: external_CorePluginsAdmin_["Field"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Notification: external_CoreHome_["Notification"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    DropdownMenu: external_CoreHome_["DropdownMenu"],
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      areAllResultsSelected: false,
      selectedRows: {},
      isAllCheckboxSelected: false,
      isBulkActionsDisabled: true,
      userToChange: null,
      roleToChangeTo: null,
      accessLevelFilter: null,
      statusLevelFilter: null,
      isRoleHelpToggled: false,
      userTextFilter: '',
      permissionsForSite: {
        id: this.initialSiteId,
        name: this.initialSiteName
      },
      showPasswordConfirmationForUserRemoval: false,
      showPasswordConfirmationForAnonymousAccess: false
    };
  },
  emits: ['editUser', 'changeUserRole', 'deleteUser', 'searchChange', 'resendInvite'],
  created() {
    this.onUserTextFilterChange = Object(external_CoreHome_["debounce"])(this.onUserTextFilterChange, 300);
  },
  watch: {
    users() {
      this.clearSelection();
    }
  },
  methods: {
    getInviteStatus(inviteStatus) {
      if (Number.isInteger(inviteStatus)) {
        return Object(external_CoreHome_["translate"])('UsersManager_InviteDayLeft', inviteStatus);
      }
      if (inviteStatus === 'expired') {
        return Object(external_CoreHome_["translate"])('UsersManager_Expired');
      }
      return Object(external_CoreHome_["translate"])('UsersManager_Active');
    },
    onPermissionsForUpdate(site) {
      this.permissionsForSite = site;
      this.changeSearch({
        idSite: this.permissionsForSite.id
      });
    },
    clearSelection() {
      this.selectedRows = {};
      this.areAllResultsSelected = false;
      this.isBulkActionsDisabled = true;
      this.isAllCheckboxSelected = false;
      this.userToChange = null;
    },
    resetUserAndRoleToChange() {
      this.userToChange = null;
      this.roleToChangeTo = null;
    },
    onAllCheckboxChange() {
      if (!this.isAllCheckboxSelected) {
        this.clearSelection();
      } else {
        for (let i = 0; i !== this.users.length; i += 1) {
          this.selectedRows[i] = true;
        }
        this.isBulkActionsDisabled = false;
      }
    },
    changeUserRole(password) {
      this.$emit('changeUserRole', {
        users: this.userOperationSubject,
        role: this.roleToChangeTo,
        password
      });
    },
    onRowSelected() {
      const selectedRowKeyCount = this.selectedCount;
      this.isBulkActionsDisabled = selectedRowKeyCount === 0;
      this.isAllCheckboxSelected = selectedRowKeyCount === this.users.length;
    },
    deleteRequestedUsers(password) {
      this.$emit('deleteUser', {
        users: this.userOperationSubject,
        password
      });
    },
    resendRequestedUser() {
      this.$emit('resendInvite', {
        user: this.userToChange
      });
    },
    showDeleteConfirm() {
      this.showPasswordConfirmationForUserRemoval = true;
    },
    showAccessChangeConfirm() {
      const containsAnonymous = this.userOperationSubject === 'all' || Array.isArray(this.userOperationSubject) && this.userOperationSubject.filter(user => user.login === 'anonymous').length;
      if (containsAnonymous && this.roleToChangeTo === 'view') {
        this.showPasswordConfirmationForAnonymousAccess = true;
      } else {
        PagedUsersListvue_type_script_lang_ts_$(this.$refs.changeUserRoleConfirmModal).modal({
          dismissible: false
        }).modal('open');
      }
    },
    getRoleDisplay(role) {
      let result = null;
      this.accessLevels.forEach(entry => {
        if (entry.key === role) {
          result = entry.value;
        }
      });
      return result;
    },
    changeSearch(changes) {
      const params = PagedUsersListvue_type_script_lang_ts_extends(PagedUsersListvue_type_script_lang_ts_extends({}, this.searchParams), changes);
      this.$emit('searchChange', {
        params
      });
    },
    gotoPreviousPage() {
      this.changeSearch({
        offset: Math.max(0, this.searchParams.offset - this.searchParams.limit)
      });
    },
    gotoNextPage() {
      const newOffset = this.searchParams.offset + this.searchParams.limit;
      if (newOffset >= this.totalEntries) {
        return;
      }
      this.changeSearch({
        offset: newOffset
      });
    },
    onUserTextFilterChange(filter) {
      this.userTextFilter = filter;
      this.changeSearch({
        filter_search: filter,
        offset: 0
      });
    }
  },
  computed: {
    currentUserLogin() {
      return external_CoreHome_["Matomo"].userLogin;
    },
    paginationLowerBound() {
      return this.searchParams.offset + 1;
    },
    paginationUpperBound() {
      if (this.totalEntries === null) {
        return '?';
      }
      const searchParams = this.searchParams;
      return Math.min(searchParams.offset + searchParams.limit, this.totalEntries);
    },
    userOperationSubject() {
      if (this.userToChange) {
        return [this.userToChange];
      }
      if (this.areAllResultsSelected) {
        return 'all';
      }
      return this.selectedUsers;
    },
    selectedUsers() {
      const users = this.users;
      const result = [];
      Object.keys(this.selectedRows).forEach(index => {
        const indexN = parseInt(index, 10);
        if (this.selectedRows[index] && users[indexN] // sanity check
        ) {
          result.push(users[indexN]);
        }
      });
      return result;
    },
    rolesHelpText() {
      return Object(external_CoreHome_["translate"])('UsersManager_RolesHelp', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_70/'), '</a>', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/general/faq_69/'), '</a>');
    },
    affectedUsersCount() {
      if (this.areAllResultsSelected) {
        return this.totalEntries || 0;
      }
      return this.selectedCount;
    },
    selectedCount() {
      let selectedRowKeyCount = 0;
      Object.keys(this.selectedRows).forEach(key => {
        if (this.selectedRows[key]) {
          selectedRowKeyCount += 1;
        }
      });
      return selectedRowKeyCount;
    },
    deleteUserPermConfirmSingleText() {
      var _this$userToChange, _this$permissionsForS;
      return Object(external_CoreHome_["translate"])('UsersManager_DeleteUserPermConfirmSingle', `<strong>${((_this$userToChange = this.userToChange) === null || _this$userToChange === void 0 ? void 0 : _this$userToChange.login) || ''}</strong>`, `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`, `<strong>${external_CoreHome_["Matomo"].helper.htmlEntities(((_this$permissionsForS = this.permissionsForSite) === null || _this$permissionsForS === void 0 ? void 0 : _this$permissionsForS.name) || '')}</strong>`);
    },
    deleteUserPermConfirmMultipleText() {
      var _this$permissionsForS2;
      return Object(external_CoreHome_["translate"])('UsersManager_DeleteUserPermConfirmMultiple', `<strong>${this.affectedUsersCount}</strong>`, `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`, `<strong>${external_CoreHome_["Matomo"].helper.htmlEntities(((_this$permissionsForS2 = this.permissionsForSite) === null || _this$permissionsForS2 === void 0 ? void 0 : _this$permissionsForS2.name) || '')}</strong>`);
    },
    bulkActionAccessLevels() {
      return this.accessLevels.filter(e => e.key !== 'noaccess' && e.key !== 'superuser');
    },
    anonymousAccessLevels() {
      return this.accessLevels.filter(e => e.key === 'noaccess' || e.key === 'view');
    },
    onlyRoleAccessLevels() {
      return this.accessLevels.filter(e => e.type === 'role');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PagedUsersList/PagedUsersList.vue



PagedUsersListvue_type_script_lang_ts.render = PagedUsersListvue_type_template_id_3c24998f_render

/* harmony default export */ var PagedUsersList = (PagedUsersListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UsersManager/UsersManager.vue?vue&type=template&id=92c94f4a

const UsersManagervue_type_template_id_92c94f4a_hoisted_1 = {
  class: "usersManager"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_2 = {
  key: 0
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_3 = {
  key: 1
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_4 = {
  class: "row add-user-container"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_5 = {
  class: "col s12"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_6 = {
  class: "input-field",
  style: {
    "margin-right": "3.5px"
  }
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_7 = {
  key: 0,
  class: "input-field"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_8 = {
  key: 0
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_9 = {
  class: "resend-invite-confirm-modal modal",
  ref: "resendInviteConfirmModal"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
  class: "btn-close modal-close"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close"
})], -1);
const UsersManagervue_type_template_id_92c94f4a_hoisted_11 = {
  class: "modal-content"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_12 = {
  class: "modal-title"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_13 = ["innerHTML"];
const UsersManagervue_type_template_id_92c94f4a_hoisted_14 = {
  class: "modal-footer"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_15 = {
  key: 0,
  class: "success-copied"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-success"
}, null, -1);
const UsersManagervue_type_template_id_92c94f4a_hoisted_17 = {
  class: "add-existing-user-modal modal",
  ref: "addExistingUserModal"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_18 = {
  class: "modal-content"
};
const UsersManagervue_type_template_id_92c94f4a_hoisted_19 = {
  class: "modal-footer"
};
function UsersManagervue_type_template_id_92c94f4a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_EnrichedHeadline = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("EnrichedHeadline");
  const _component_PagedUsersList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PagedUsersList");
  const _component_UserEditForm = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("UserEditForm");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _directive_content_intro = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-intro");
  const _directive_tooltips = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("tooltips");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_EnrichedHeadline, {
    "help-url": _ctx.externalRawLink('https://matomo.org/docs/manage-users/'),
    "feature-name": "Users Management"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ManageUsers')), 1)]),
    _: 1
  }, 8, ["help-url"])]), _ctx.currentUserRole === 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UsersManagervue_type_template_id_92c94f4a_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ManageUsersDesc')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.currentUserRole === 'admin' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", UsersManagervue_type_template_id_92c94f4a_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ManageUsersAdminDesc')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn add-new-user",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.onAddNewUser())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_InviteNewUser')), 1)]), _ctx.currentUserRole !== 'superuser' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "btn add-existing-user",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.showAddExistingUserModal())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_AddExistingUser')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PagedUsersList, {
    onEditUser: _cache[2] || (_cache[2] = $event => _ctx.onEditUser($event.user)),
    onChangeUserRole: _cache[3] || (_cache[3] = $event => _ctx.onChangeUserRole($event.users, $event.role, $event.password)),
    onDeleteUser: _cache[4] || (_cache[4] = $event => _ctx.onDeleteUser($event.users, $event.password)),
    onSearchChange: _cache[5] || (_cache[5] = $event => {
      _ctx.searchParams = $event.params;
      _ctx.fetchUsers();
    }),
    onResendInvite: _cache[6] || (_cache[6] = $event => _ctx.showResendPopup($event.user)),
    "initial-site-id": _ctx.initialSiteId,
    "initial-site-name": _ctx.initialSiteName,
    "is-loading-users": _ctx.isLoadingUsers,
    "current-user-role": _ctx.currentUserRole,
    "access-levels": _ctx.accessLevels,
    "filter-access-levels": _ctx.filterAccessLevels,
    "filter-status-levels": _ctx.filterStatusLevels,
    "search-params": _ctx.searchParams,
    users: _ctx.users,
    "total-entries": _ctx.totalEntries
  }, null, 8, ["initial-site-id", "initial-site-name", "is-loading-users", "current-user-role", "access-levels", "filter-access-levels", "filter-status-levels", "search-params", "users", "total-entries"])])), [[_directive_content_intro]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.isEditing]]), _ctx.isEditing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_UserEditForm, {
    onDone: _cache[7] || (_cache[7] = $event => _ctx.onDoneEditing($event.isUserModified)),
    user: _ctx.userBeingEdited,
    "current-user-role": _ctx.currentUserRole,
    "invite-token-expiry-days": _ctx.inviteTokenExpiryDays,
    "access-levels": _ctx.accessLevels,
    "filter-access-levels": _ctx.filterAccessLevels,
    "initial-site-id": _ctx.initialSiteId,
    "initial-site-name": _ctx.initialSiteName,
    "activated-plugins": _ctx.activatedPlugins,
    onResendInvite: _cache[8] || (_cache[8] = $event => _ctx.showResendPopup($event.user)),
    onUpdated: _cache[9] || (_cache[9] = $event => _ctx.userBeingEdited = $event.user)
  }, null, 8, ["user", "current-user-role", "invite-token-expiry-days", "access-levels", "filter-access-levels", "initial-site-id", "initial-site-name", "activated-plugins"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_9, [UsersManagervue_type_template_id_92c94f4a_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", UsersManagervue_type_template_id_92c94f4a_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ResendInvite')), 1), _ctx.userBeingEdited ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
    key: 0,
    innerHTML: _ctx.$sanitize(_ctx.translate('UsersManager_InviteConfirmMessage', [`<strong>${_ctx.userBeingEdited.login}</strong>`, `<strong>${_ctx.userBeingEdited.email}</strong>`]))
  }, null, 8, UsersManagervue_type_template_id_92c94f4a_hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_InviteActionNotes', _ctx.inviteTokenExpiryDays)), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_14, [_ctx.copied ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UsersManagervue_type_template_id_92c94f4a_hoisted_15, [UsersManagervue_type_template_id_92c94f4a_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_LinkCopied')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    onClick: _cache[10] || (_cache[10] = $event => _ctx.showInviteActionPasswordConfirm('copy')),
    class: "btn btn-copy-link modal-action",
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_CopyLink')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    class: "btn btn-resend modal-action modal-no",
    onClick: _cache[11] || (_cache[11] = $event => _ctx.showInviteActionPasswordConfirm('send'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ResendInvite')), 1)])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_AddExistingUser')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_EnterUsernameOrEmail')) + ":", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    modelValue: _ctx.addNewUserLoginEmail,
    "onUpdate:modelValue": _cache[12] || (_cache[12] = $event => _ctx.addNewUserLoginEmail = $event),
    name: "add-existing-user-email",
    uicontrol: "text"
  }, null, 8, ["modelValue"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UsersManagervue_type_template_id_92c94f4a_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close btn",
    onClick: _cache[13] || (_cache[13] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.addExistingUser(), ["prevent"])),
    style: {
      "margin-right": "3.5px"
    }
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Add')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    href: "",
    class: "modal-action modal-close modal-no",
    onClick: _cache[14] || (_cache[14] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.addNewUserLoginEmail = null, ["prevent"]))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)])], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
    modelValue: _ctx.showPasswordConfirmationForInviteAction,
    "onUpdate:modelValue": _cache[15] || (_cache[15] = $event => _ctx.showPasswordConfirmationForInviteAction = $event),
    onConfirmed: _ctx.onInviteAction
  }, null, 8, ["modelValue", "onConfirmed"])])), [[_directive_tooltips]]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UsersManager/UsersManager.vue?vue&type=template&id=92c94f4a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UsersManager/UsersManager.vue?vue&type=script&lang=ts
function UsersManagervue_type_script_lang_ts_extends() { UsersManagervue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return UsersManagervue_type_script_lang_ts_extends.apply(this, arguments); }
/* eslint-disable newline-per-chained-call */





const NUM_USERS_PER_PAGE = 20;
const {
  $: UsersManagervue_type_script_lang_ts_$
} = window;
/* harmony default export */ var UsersManagervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    currentUserRole: {
      type: String,
      required: true
    },
    initialSiteName: {
      type: String,
      required: true
    },
    initialSiteId: {
      type: String,
      required: true
    },
    accessLevels: {
      type: Array,
      required: true
    },
    filterAccessLevels: {
      type: Array,
      required: true
    },
    filterStatusLevels: {
      type: Array,
      required: true
    },
    activatedPlugins: {
      type: Array,
      required: true
    },
    inviteTokenExpiryDays: {
      type: String,
      required: true
    }
  },
  components: {
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"],
    EnrichedHeadline: external_CoreHome_["EnrichedHeadline"],
    PagedUsersList: PagedUsersList,
    UserEditForm: UserEditForm,
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    ContentIntro: external_CoreHome_["ContentIntro"],
    Tooltips: external_CoreHome_["Tooltips"]
  },
  data() {
    return {
      isEditing: !!external_CoreHome_["MatomoUrl"].urlParsed.value.showadduser,
      isCurrentUserSuperUser: true,
      users: [],
      totalEntries: null,
      searchParams: {
        offset: 0,
        limit: NUM_USERS_PER_PAGE,
        filter_search: '',
        filter_access: '',
        filter_status: '',
        idSite: this.initialSiteId
      },
      isLoadingUsers: false,
      userBeingEdited: null,
      addNewUserLoginEmail: '',
      copied: false,
      loading: false,
      showPasswordConfirmationForInviteAction: false,
      inviteAction: ''
    };
  },
  created() {
    this.fetchUsers();
  },
  watch: {
    limit() {
      this.fetchUsers();
    }
  },
  methods: {
    showInviteActionPasswordConfirm(action) {
      if (this.loading) return;
      this.showPasswordConfirmationForInviteAction = true;
      this.inviteAction = action;
    },
    showResendPopup(user) {
      this.userBeingEdited = user;
      UsersManagervue_type_script_lang_ts_$(this.$refs.resendInviteConfirmModal).modal({
        dismissible: false
      }).modal('open');
      this.copied = false;
    },
    onInviteAction(password) {
      if (this.inviteAction === 'send') {
        this.onResendInvite(password);
      } else {
        this.generateInviteLink(password);
      }
    },
    onEditUser(user) {
      external_CoreHome_["Matomo"].helper.lazyScrollToContent();
      this.isEditing = true;
      this.userBeingEdited = user;
    },
    onDoneEditing(isUserModified) {
      this.isEditing = false;
      if (isUserModified) {
        // if a user was modified, we must reload the users list
        this.fetchUsers();
      }
    },
    showAddExistingUserModal() {
      UsersManagervue_type_script_lang_ts_$(this.$refs.addExistingUserModal).modal({
        dismissible: false
      }).modal('open');
    },
    onChangeUserRole(users, role, password) {
      this.isLoadingUsers = true;
      Promise.resolve().then(() => {
        if (users === 'all') {
          return this.getAllUsersInSearch();
        }
        return users;
      }).then(usersResolved => usersResolved.filter(u => u.role !== 'superuser').map(u => u.login)).then(userLogins => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const type = this.accessLevels.filter(a => a.key === role).map(a => a.type);
        let requests;
        if (type.length && type[0] === 'capability') {
          requests = userLogins.map(login => ({
            method: 'UsersManager.addCapabilities',
            userLogin: login,
            capabilities: role,
            idSites: this.searchParams.idSite,
            passwordConfirmation: password
          }));
        } else {
          requests = userLogins.map(login => ({
            method: 'UsersManager.setUserAccess',
            userLogin: login,
            access: role,
            idSites: this.searchParams.idSite,
            passwordConfirmation: password
          }));
        }
        return external_CoreHome_["AjaxHelper"].fetch(requests, {
          createErrorNotification: true
        });
      }).catch(() => {
        // ignore (errors will still be displayed to the user)
      }).finally(() => this.fetchUsers());
    },
    getAllUsersInSearch() {
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.getUsersPlusRole',
        filter_search: this.searchParams.filter_search,
        filter_access: this.searchParams.filter_access,
        filter_status: this.searchParams.filter_status,
        idSite: this.searchParams.idSite,
        filter_limit: '-1'
      });
    },
    onDeleteUser(users, password) {
      this.isLoadingUsers = true;
      Promise.resolve().then(() => {
        if (users === 'all') {
          return this.getAllUsersInSearch();
        }
        return users;
      }).then(usersResolved => usersResolved.map(u => u.login)).then(userLogins => {
        const requests = userLogins.map(login => ({
          method: 'UsersManager.deleteUser',
          userLogin: login,
          passwordConfirmation: password
        }));
        return external_CoreHome_["AjaxHelper"].fetch(requests, {
          createErrorNotification: true
        });
      }).then(() => {
        external_CoreHome_["NotificationsStore"].scrollToNotification(external_CoreHome_["NotificationsStore"].show({
          id: 'removeUserSuccess',
          message: Object(external_CoreHome_["translate"])('UsersManager_DeleteSuccess'),
          context: 'success',
          type: 'toast'
        }));
        this.fetchUsers();
      }, () => {
        if (users !== 'all' && users.length > 1) {
          // Show a notification that some users might not have been removed if an error occurs
          // and more than one users was tried to remove
          // Note: We do not scroll to this notification, as the error notification from AjaxHandler
          // will be created earlier, which will already be scrolled into view.
          external_CoreHome_["NotificationsStore"].show({
            id: 'removeUserSuccess',
            message: Object(external_CoreHome_["translate"])('UsersManager_DeleteNotSuccessful'),
            context: 'warning',
            type: 'toast'
          });
        }
        this.fetchUsers();
      });
    },
    async generateInviteLink(password) {
      if (this.loading) {
        return;
      }
      this.loading = true;
      try {
        const res = await external_CoreHome_["AjaxHelper"].post({
          method: 'UsersManager.generateInviteLink'
        }, {
          userLogin: this.userBeingEdited.login,
          passwordConfirmation: password
        });
        await this.copyToClipboard(res.value);
        // eslint-disable-next-line no-empty
      } catch (e) {}
      this.loading = false;
    },
    async copyToClipboard(value) {
      try {
        const tempInput = document.createElement('input');
        tempInput.style.top = '-100px';
        tempInput.style.left = '0';
        tempInput.style.position = 'fixed';
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        if (window.location.protocol !== 'https:') {
          document.execCommand('copy');
        } else {
          await navigator.clipboard.writeText(tempInput.value);
        }
        document.body.removeChild(tempInput);
        this.copied = true;
        // eslint-disable-next-line no-empty
      } catch (e) {
        const id = external_CoreHome_["NotificationsStore"].show({
          message: `<strong>${Object(external_CoreHome_["translate"])('UsersManager_CopyDenied')}</strong><br>
${Object(external_CoreHome_["translate"])('UsersManager_CopyDeniedHints', [`<br><span class="invite-link">${value}</span>`])}`,
          id: 'copyError',
          context: 'error',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
      }
    },
    onResendInvite(password) {
      if (password === '') return;
      external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.resendInvite',
        userLogin: this.userBeingEdited.login
      }, {
        passwordConfirmation: password
      }).then(() => {
        this.fetchUsers();
        UsersManagervue_type_script_lang_ts_$(this.$refs.resendInviteConfirmModal).modal('close');
        const id = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('UsersManager_InviteSuccess'),
          id: 'resendInvite',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
      });
    },
    fetchUsers() {
      this.isLoadingUsers = true;
      return external_CoreHome_["AjaxHelper"].fetch(UsersManagervue_type_script_lang_ts_extends(UsersManagervue_type_script_lang_ts_extends({}, this.searchParams), {}, {
        method: 'UsersManager.getUsersPlusRole'
      }), {
        returnResponseObject: true
      }).then(helper => {
        const result = helper.getRequestHandle();
        this.totalEntries = parseInt(result.getResponseHeader('x-matomo-total-results') || '0', 10);
        this.users = result.responseJSON;
        this.isLoadingUsers = false;
      }).catch(() => {
        this.isLoadingUsers = false;
      });
    },
    addExistingUser() {
      this.isLoadingUsers = true;
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'UsersManager.userExists',
        userLogin: this.addNewUserLoginEmail
      }).then(response => {
        if (response && response.value) {
          return this.addNewUserLoginEmail;
        }
        return external_CoreHome_["AjaxHelper"].fetch({
          method: 'UsersManager.getUserLoginFromUserEmail',
          userEmail: this.addNewUserLoginEmail
        }).then(r => r.value);
      }).then(login => external_CoreHome_["AjaxHelper"].post({
        method: 'UsersManager.setUserAccess'
      }, {
        userLogin: login,
        access: 'view',
        idSites: this.searchParams.idSite
      })).then(() => this.fetchUsers()).catch(() => {
        this.isLoadingUsers = false;
      });
    },
    onAddNewUser() {
      const parameters = {
        isAllowed: true
      };
      external_CoreHome_["Matomo"].postEvent('UsersManager.initAddUser', parameters);
      if (parameters && !parameters.isAllowed) {
        return;
      }
      this.isEditing = true;
      this.userBeingEdited = null;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UsersManager/UsersManager.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UsersManager/UsersManager.vue



UsersManagervue_type_script_lang_ts.render = UsersManagervue_type_template_id_92c94f4a_render

/* harmony default export */ var UsersManager = (UsersManagervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AnonymousSettings/AnonymousSettings.vue?vue&type=template&id=2293559a

const AnonymousSettingsvue_type_template_id_2293559a_hoisted_1 = {
  key: 0,
  class: "alert alert-info"
};
const AnonymousSettingsvue_type_template_id_2293559a_hoisted_2 = {
  key: 1
};
function AnonymousSettingsvue_type_template_id_2293559a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.title
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.anonymousSites.length === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AnonymousSettingsvue_type_template_id_2293559a_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_NoteNoAnonymousUserAccessSettingsWontBeUsed2')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.anonymousSites.length > 0 ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AnonymousSettingsvue_type_template_id_2293559a_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "anonymousDefaultReport",
      modelValue: _ctx.defaultReport,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.defaultReport = $event),
      introduction: _ctx.translate('UsersManager_WhenUsersAreNotLoggedInAndVisitPiwikTheyShouldAccess'),
      options: _ctx.defaultReportOptions
    }, null, 8, ["modelValue", "introduction", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "anonymousDefaultReportWebsite",
      modelValue: _ctx.defaultReportWebsite,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.defaultReportWebsite = $event),
      options: _ctx.anonymousSites
    }, null, 8, ["modelValue", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "anonymousDefaultDate",
      modelValue: _ctx.defaultDate,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.defaultDate = $event),
      introduction: _ctx.translate('UsersManager_ForAnonymousUsersReportDateToLoadByDefault'),
      options: _ctx.availableDefaultDates
    }, null, 8, ["modelValue", "introduction", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.loading,
      onConfirm: _cache[3] || (_cache[3] = $event => _ctx.save())
    }, null, 8, ["saving"])])), [[_directive_form]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AnonymousSettings/AnonymousSettings.vue?vue&type=template&id=2293559a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AnonymousSettings/AnonymousSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var AnonymousSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: {
      type: String,
      required: true
    },
    anonymousSites: {
      type: Array,
      required: true
    },
    anonymousDefaultReport: {
      type: [String, Number],
      required: true
    },
    anonymousDefaultSite: {
      type: String,
      required: true
    },
    anonymousDefaultDate: {
      type: String,
      required: true
    },
    availableDefaultDates: {
      type: Object,
      required: true
    },
    defaultReportOptions: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      loading: false,
      defaultReport: `${this.anonymousDefaultReport}`,
      defaultReportWebsite: this.anonymousDefaultSite,
      defaultDate: this.anonymousDefaultDate
    };
  },
  methods: {
    save() {
      const postParams = {
        anonymousDefaultReport: this.defaultReport === '1' ? this.defaultReportWebsite : this.defaultReport,
        anonymousDefaultDate: this.defaultDate
      };
      this.loading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'UsersManager',
        action: 'recordAnonymousUserSettings',
        format: 'json'
      }, postParams, {
        withTokenInUrl: true
      }).then(() => {
        const id = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          id: 'anonymousUserSettings',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
      }).finally(() => {
        this.loading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AnonymousSettings/AnonymousSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AnonymousSettings/AnonymousSettings.vue



AnonymousSettingsvue_type_script_lang_ts.render = AnonymousSettingsvue_type_template_id_2293559a_render

/* harmony default export */ var AnonymousSettings = (AnonymousSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/NewsletterSettings/NewsletterSettings.vue?vue&type=template&id=464433cd

const NewsletterSettingsvue_type_template_id_464433cd_hoisted_1 = {
  id: "newsletterSignup"
};
function NewsletterSettingsvue_type_template_id_464433cd_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", NewsletterSettingsvue_type_template_id_464433cd_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UsersManager_NewsletterSignupTitle')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "newsletterSignupCheckbox",
      id: "newsletterSignupCheckbox",
      modelValue: _ctx.newsletterSignupCheckbox,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.newsletterSignupCheckbox = $event),
      "full-width": true,
      title: _ctx.signupTitleText
    }, null, 8, ["modelValue", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      id: "newsletterSignupBtn",
      onConfirm: _cache[1] || (_cache[1] = $event => _ctx.signupForNewsletter()),
      disabled: !_ctx.newsletterSignupCheckbox,
      value: _ctx.newsletterSignupButtonTitle,
      saving: _ctx.isProcessingNewsletterSignup
    }, null, 8, ["disabled", "value", "saving"])]),
    _: 1
  }, 8, ["content-title"])], 512)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showNewsletterSignup]]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/NewsletterSettings/NewsletterSettings.vue?vue&type=template&id=464433cd

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/NewsletterSettings/NewsletterSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var NewsletterSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  data() {
    return {
      showNewsletterSignup: true,
      newsletterSignupCheckbox: false,
      isProcessingNewsletterSignup: false,
      newsletterSignupButtonTitle: Object(external_CoreHome_["translate"])('General_Save')
    };
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  computed: {
    signupTitleText() {
      return Object(external_CoreHome_["translate"])('UsersManager_NewsletterSignupMessage', Object(external_CoreHome_["externalLink"])('https://matomo.org/privacy-policy/'), '</a>');
    }
  },
  methods: {
    signupForNewsletter() {
      this.newsletterSignupButtonTitle = Object(external_CoreHome_["translate"])('General_Loading');
      this.isProcessingNewsletterSignup = true;
      external_CoreHome_["AjaxHelper"].fetch({
        module: 'API',
        method: 'UsersManager.newsletterSignup'
      }, {
        withTokenInUrl: true
      }).then(() => {
        this.isProcessingNewsletterSignup = false;
        this.showNewsletterSignup = false;
        const id = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('UsersManager_NewsletterSignupSuccessMessage'),
          id: 'newslettersignup',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
      }).catch(() => {
        this.isProcessingNewsletterSignup = false;
        const id = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('UsersManager_NewsletterSignupFailureMessage'),
          id: 'newslettersignup',
          context: 'error',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
        this.newsletterSignupButtonTitle = Object(external_CoreHome_["translate"])('General_PleaseTryAgain');
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/NewsletterSettings/NewsletterSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/NewsletterSettings/NewsletterSettings.vue



NewsletterSettingsvue_type_script_lang_ts.render = NewsletterSettingsvue_type_template_id_464433cd_render

/* harmony default export */ var NewsletterSettings = (NewsletterSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/PersonalSettings/PersonalSettings.vue?vue&type=template&id=f35048b0

const PersonalSettingsvue_type_template_id_f35048b0_hoisted_1 = {
  id: "userSettingsTable"
};
const PersonalSettingsvue_type_template_id_f35048b0_hoisted_2 = {
  key: 0
};
const PersonalSettingsvue_type_template_id_f35048b0_hoisted_3 = {
  id: "languageHelp",
  class: "inline-help-node"
};
const PersonalSettingsvue_type_template_id_f35048b0_hoisted_4 = ["href"];
const PersonalSettingsvue_type_template_id_f35048b0_hoisted_5 = {
  class: "sites_autocomplete"
};
function PersonalSettingsvue_type_template_id_f35048b0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_SiteSelector = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SiteSelector");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _component_PasswordConfirmation = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PasswordConfirmation");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.title,
    feature: 'true'
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("form", PersonalSettingsvue_type_template_id_f35048b0_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "username",
      title: _ctx.translate('General_Username'),
      disabled: true,
      modelValue: _ctx.username,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.username = $event),
      "inline-help": _ctx.translate('UsersManager_YourUsernameCannotBeChanged')
    }, null, 8, ["title", "modelValue", "inline-help"])]), _ctx.isUsersAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PersonalSettingsvue_type_template_id_f35048b0_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "email",
      "model-value": _ctx.email,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => {
        _ctx.email = $event;
        _ctx.doesRequirePasswordConfirmation = true;
      }),
      maxlength: 100,
      title: _ctx.translate('UsersManager_Email')
    }, null, 8, ["model-value", "title"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PersonalSettingsvue_type_template_id_f35048b0_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      target: "_blank",
      rel: "noreferrer noopener",
      href: _ctx.externalRawLink('https://matomo.org/translations/')
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('LanguagesManager_AboutPiwikTranslations')), 9, PersonalSettingsvue_type_template_id_f35048b0_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "language",
      modelValue: _ctx.language,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.language = $event),
      title: _ctx.translate('General_Language'),
      options: _ctx.languageOptions,
      "inline-help": "#languageHelp"
    }, null, 8, ["modelValue", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "timeformat",
      modelValue: _ctx.timeformat,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.timeformat = $event),
      title: _ctx.translate('General_TimeFormat'),
      options: _ctx.timeFormats
    }, null, 8, ["modelValue", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "defaultReport",
      modelValue: _ctx.theDefaultReport,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.theDefaultReport = $event),
      introduction: _ctx.translate('UsersManager_ReportToLoadByDefault'),
      title: _ctx.translate('General_AllWebsitesDashboard'),
      options: _ctx.defaultReportOptions
    }, null, 8, ["modelValue", "introduction", "title", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PersonalSettingsvue_type_template_id_f35048b0_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SiteSelector, {
      modelValue: _ctx.site,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.site = $event),
      "show-selected-site": true,
      "switch-site-on-select": false,
      "show-all-sites-item": false,
      showselectedsite: true,
      id: "defaultReportSiteSelector"
    }, null, 8, ["modelValue"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "defaultDate",
      modelValue: _ctx.theDefaultDate,
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.theDefaultDate = $event),
      introduction: _ctx.translate('UsersManager_ReportDateToLoadByDefault'),
      options: _ctx.availableDefaultDates
    }, null, 8, ["modelValue", "introduction", "options"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      onConfirm: _cache[7] || (_cache[7] = $event => _ctx.save()),
      saving: _ctx.loading
    }, null, 8, ["saving"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PasswordConfirmation, {
      modelValue: _ctx.showPasswordConfirmation,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.showPasswordConfirmation = $event),
      onConfirmed: _ctx.doSave
    }, null, 8, ["modelValue", "onConfirmed"])])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PersonalSettings/PersonalSettings.vue?vue&type=template&id=f35048b0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/PersonalSettings/PersonalSettings.vue?vue&type=script&lang=ts



/* harmony default export */ var PersonalSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isUsersAdminEnabled: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    userLogin: {
      type: String,
      required: true
    },
    userEmail: {
      type: String,
      required: true
    },
    currentLanguageCode: {
      type: String,
      required: true
    },
    languageOptions: {
      type: Object,
      required: true
    },
    currentTimeformat: {
      type: Number,
      required: true
    },
    timeFormats: {
      type: Object,
      required: true
    },
    defaultReport: {
      type: [String, Number],
      required: true
    },
    defaultReportOptions: {
      type: Object,
      required: true
    },
    defaultReportIdSite: {
      type: [String, Number],
      required: true
    },
    defaultReportSiteName: {
      type: String,
      required: true
    },
    defaultDate: {
      type: String,
      required: true
    },
    availableDefaultDates: {
      type: Object,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    Field: external_CorePluginsAdmin_["Field"],
    SiteSelector: external_CoreHome_["SiteSelector"],
    PasswordConfirmation: external_CorePluginsAdmin_["PasswordConfirmation"]
  },
  directives: {
    Form: external_CorePluginsAdmin_["Form"]
  },
  data() {
    return {
      doesRequirePasswordConfirmation: false,
      username: this.userLogin,
      email: this.userEmail,
      language: this.currentLanguageCode,
      timeformat: this.currentTimeformat,
      theDefaultReport: this.defaultReport,
      site: {
        id: this.defaultReportIdSite,
        name: external_CoreHome_["Matomo"].helper.htmlDecode(this.defaultReportSiteName)
      },
      theDefaultDate: this.defaultDate,
      loading: false,
      showPasswordConfirmation: false
    };
  },
  methods: {
    save() {
      if (this.doesRequirePasswordConfirmation) {
        this.showPasswordConfirmation = true;
        return;
      }
      this.doSave();
    },
    doSave(password) {
      const postParams = {
        email: this.email,
        defaultReport: this.theDefaultReport === 'MultiSites' ? this.theDefaultReport : this.site.id,
        defaultDate: this.theDefaultDate,
        language: this.language,
        timeformat: this.timeformat
      };
      if (password) {
        postParams.passwordConfirmation = password;
      }
      this.loading = true;
      external_CoreHome_["AjaxHelper"].post({
        module: 'UsersManager',
        action: 'recordUserSettings',
        format: 'json'
      }, postParams, {
        withTokenInUrl: true
      }).then(() => {
        const id = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('CoreAdminHome_SettingsSaveSuccess'),
          id: 'PersonalSettingsSuccess',
          context: 'success',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(id);
        this.doesRequirePasswordConfirmation = false;
        this.loading = false;
      }).catch(() => {
        this.loading = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PersonalSettings/PersonalSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/PersonalSettings/PersonalSettings.vue



PersonalSettingsvue_type_script_lang_ts.render = PersonalSettingsvue_type_template_id_f35048b0_render

/* harmony default export */ var PersonalSettings = (PersonalSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AddNewToken/AddNewToken.vue?vue&type=template&id=5852e320

const AddNewTokenvue_type_template_id_5852e320_hoisted_1 = {
  key: 0
};
const AddNewTokenvue_type_template_id_5852e320_hoisted_2 = {
  key: 1,
  class: "alert alert-danger"
};
const AddNewTokenvue_type_template_id_5852e320_hoisted_3 = ["action"];
const AddNewTokenvue_type_template_id_5852e320_hoisted_4 = ["value"];
const AddNewTokenvue_type_template_id_5852e320_hoisted_5 = ["value"];
const AddNewTokenvue_type_template_id_5852e320_hoisted_6 = ["innerHTML"];
function AddNewTokenvue_type_template_id_5852e320_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UsersManager_AuthTokens')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_TokenAuthIntro')), 1), _ctx.noDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("br", AddNewTokenvue_type_template_id_5852e320_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.noDescription ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", AddNewTokenvue_type_template_id_5852e320_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ValidatorErrorEmptyValue')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      action: _ctx.addNewTokenFormUrl,
      method: "post",
      class: "addTokenForm"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "description",
      title: _ctx.translate('General_Description'),
      maxlength: 100,
      required: true,
      "inline-help": _ctx.translate('UsersManager_AuthTokenPurpose'),
      modelValue: _ctx.tokenDescription,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.tokenDescription = $event)
    }, null, 8, ["title", "inline-help", "modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "secure_only",
      title: _ctx.translate('UsersManager_OnlyAllowSecureRequests'),
      required: false,
      "inline-help": _ctx.secureOnlyHelp,
      modelValue: _ctx.tokenSecureOnly,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.tokenSecureOnly = $event),
      disabled: _ctx.forceSecureOnlyCalc
    }, null, 8, ["title", "inline-help", "modelValue", "disabled"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      value: _ctx.formNonce,
      name: "nonce"
    }, null, 8, AddNewTokenvue_type_template_id_5852e320_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "submit",
      value: _ctx.translate('UsersManager_CreateNewToken'),
      class: "btn",
      style: {
        "margin-right": "3.5px"
      }
    }, null, 8, AddNewTokenvue_type_template_id_5852e320_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.cancelLink)
    }, null, 8, AddNewTokenvue_type_template_id_5852e320_hoisted_6)], 8, AddNewTokenvue_type_template_id_5852e320_hoisted_3)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewToken.vue?vue&type=template&id=5852e320

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AddNewToken/AddNewToken.vue?vue&type=script&lang=ts
function AddNewTokenvue_type_script_lang_ts_extends() { AddNewTokenvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return AddNewTokenvue_type_script_lang_ts_extends.apply(this, arguments); }



/* harmony default export */ var AddNewTokenvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    formNonce: String,
    noDescription: Boolean,
    forceSecureOnly: Boolean
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  data() {
    return {
      tokenDescription: '',
      tokenSecureOnly: true
    };
  },
  computed: {
    addNewTokenFormUrl() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(AddNewTokenvue_type_script_lang_ts_extends(AddNewTokenvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'addNewToken'
      }))}`;
    },
    cancelLink() {
      const backlink = `?${external_CoreHome_["MatomoUrl"].stringify(AddNewTokenvue_type_script_lang_ts_extends(AddNewTokenvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'userSecurity'
      }))}`;
      return Object(external_CoreHome_["translate"])('General_OrCancel', `<a class='entityCancelLink' href='${backlink}'>`, '</a>');
    },
    forceSecureOnlyCalc() {
      return this.forceSecureOnly;
    },
    secureOnlyHelp() {
      return this.forceSecureOnly ? Object(external_CoreHome_["translate"])('UsersManager_AuthTokenSecureOnlyHelpForced') : Object(external_CoreHome_["translate"])('UsersManager_AuthTokenSecureOnlyHelp');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewToken.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewToken.vue



AddNewTokenvue_type_script_lang_ts.render = AddNewTokenvue_type_template_id_5852e320_render

/* harmony default export */ var AddNewToken = (AddNewTokenvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AddNewToken/AddNewTokenSuccess.vue?vue&type=template&id=c60f0f6c

const AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_1 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_2 = {
  style: {
    "font-size": "40px"
  },
  class: "generatedTokenAuth"
};
const AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_3 = ["href"];
function AddNewTokenSuccessvue_type_template_id_c60f0f6c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_copy_to_clipboard = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("copy-to-clipboard");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UsersManager_TokenSuccessfullyGenerated')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_PleaseStoreToken')) + " ", 1), AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_DoNotStoreToken')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("pre", AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.generatedToken), 1)])), [[_directive_copy_to_clipboard, {}]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.userSecurityLink,
      class: "btn",
      style: {
        "height": "auto"
      }
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ConfirmTokenCopied')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_GoBackSecurityPage')), 9, AddNewTokenSuccessvue_type_template_id_c60f0f6c_hoisted_3)]),
    _: 1
  }, 8, ["content-title"]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewTokenSuccess.vue?vue&type=template&id=c60f0f6c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/AddNewToken/AddNewTokenSuccess.vue?vue&type=script&lang=ts
function AddNewTokenSuccessvue_type_script_lang_ts_extends() { AddNewTokenSuccessvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return AddNewTokenSuccessvue_type_script_lang_ts_extends.apply(this, arguments); }


/* harmony default export */ var AddNewTokenSuccessvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    generatedToken: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"]
  },
  directives: {
    CopyToClipboard: external_CoreHome_["CopyToClipboard"]
  },
  computed: {
    userSecurityLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(AddNewTokenSuccessvue_type_script_lang_ts_extends(AddNewTokenSuccessvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'userSecurity'
      }))}`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewTokenSuccess.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/AddNewToken/AddNewTokenSuccess.vue



AddNewTokenSuccessvue_type_script_lang_ts.render = AddNewTokenSuccessvue_type_template_id_c60f0f6c_render

/* harmony default export */ var AddNewTokenSuccess = (AddNewTokenSuccessvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserSecurity/UserSecurity.vue?vue&type=template&id=60a12226

const UserSecurityvue_type_template_id_60a12226_hoisted_1 = ["action"];
const UserSecurityvue_type_template_id_60a12226_hoisted_2 = ["value"];
const UserSecurityvue_type_template_id_60a12226_hoisted_3 = {
  key: 0
};
const UserSecurityvue_type_template_id_60a12226_hoisted_4 = {
  class: "alert alert-info"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_5 = ["value"];
const UserSecurityvue_type_template_id_60a12226_hoisted_6 = {
  key: 1
};
const UserSecurityvue_type_template_id_60a12226_hoisted_7 = {
  class: "alert alert-danger"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_8 = ["innerHTML"];
const UserSecurityvue_type_template_id_60a12226_hoisted_9 = {
  ref: "afterPassword"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  name: "authtokens",
  id: "authtokens"
}, null, -1);
const UserSecurityvue_type_template_id_60a12226_hoisted_11 = {
  key: 0
};
const UserSecurityvue_type_template_id_60a12226_hoisted_12 = {
  class: "listAuthTokens"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_13 = ["title"];
const UserSecurityvue_type_template_id_60a12226_hoisted_14 = {
  key: 0
};
const UserSecurityvue_type_template_id_60a12226_hoisted_15 = ["colspan", "innerHTML"];
const UserSecurityvue_type_template_id_60a12226_hoisted_16 = {
  class: "creationDate"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_17 = ["title"];
const UserSecurityvue_type_template_id_60a12226_hoisted_18 = ["action"];
const UserSecurityvue_type_template_id_60a12226_hoisted_19 = ["value"];
const UserSecurityvue_type_template_id_60a12226_hoisted_20 = ["value"];
const UserSecurityvue_type_template_id_60a12226_hoisted_21 = ["title"];
const UserSecurityvue_type_template_id_60a12226_hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);
const UserSecurityvue_type_template_id_60a12226_hoisted_23 = [UserSecurityvue_type_template_id_60a12226_hoisted_22];
const UserSecurityvue_type_template_id_60a12226_hoisted_24 = {
  class: "tableActionBar"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_25 = ["href"];
const UserSecurityvue_type_template_id_60a12226_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const UserSecurityvue_type_template_id_60a12226_hoisted_27 = ["action"];
const UserSecurityvue_type_template_id_60a12226_hoisted_28 = ["value"];
const UserSecurityvue_type_template_id_60a12226_hoisted_29 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  name: "idtokenauth",
  type: "hidden",
  value: "all"
}, null, -1);
const UserSecurityvue_type_template_id_60a12226_hoisted_30 = {
  type: "submit",
  class: "table-action"
};
const UserSecurityvue_type_template_id_60a12226_hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);
function UserSecurityvue_type_template_id_60a12226_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [_ctx.isUsersAdminEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ContentBlock, {
    key: 0,
    "content-title": _ctx.translate('General_ChangePassword'),
    feature: "true"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      id: "userSettingsTable",
      method: "post",
      action: _ctx.recordPasswordChangeAction
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "hidden",
      value: _ctx.changePasswordNonce,
      name: "nonce"
    }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_2), _ctx.isValidHost ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "password",
      name: "password",
      autocomplete: false,
      modelValue: _ctx.password,
      "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.password = $event),
      title: _ctx.translate('Login_NewPassword'),
      "inline-help": _ctx.translate('UsersManager_IfYouWouldLikeToChangeThePasswordTypeANewOne')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "password",
      name: "passwordBis",
      autocomplete: false,
      modelValue: _ctx.passwordBis,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.passwordBis = $event),
      title: _ctx.translate('Login_NewPasswordRepeat'),
      "inline-help": _ctx.translate('UsersManager_TypeYourPasswordAgain')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "password",
      name: "passwordConfirmation",
      autocomplete: false,
      modelValue: _ctx.passwordConfirmation,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.passwordConfirmation = $event),
      title: _ctx.translate('UsersManager_YourCurrentPassword'),
      "inline-help": _ctx.translate('UsersManager_TypeYourCurrentPassword')
    }, null, 8, ["modelValue", "title", "inline-help"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_PasswordChangeTerminatesOtherSessions')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "submit",
      value: _ctx.translate('General_Save'),
      class: "btn"
    }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isValidHost ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_InjectedHostCannotChangePwd', _ctx.invalidHost)) + " ", 1), !_ctx.isSuperUser ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      innerHTML: _ctx.$sanitize(_ctx.emailYourAdminText)
    }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, UserSecurityvue_type_template_id_60a12226_hoisted_1)]),
    _: 1
  }, 8, ["content-title"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_9, [_ctx.isUsersAdminEnabled && _ctx.afterPasswordComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.afterPasswordComponent), {
    key: 0
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), UserSecurityvue_type_template_id_60a12226_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UsersManager_AuthTokens')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => {
      var _ctx$tokens, _ctx$tokens2;
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_TokenAuthIntro')) + " ", 1), _ctx.hasTokensWithExpireDate ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", UserSecurityvue_type_template_id_60a12226_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ExpiredTokensDeleteAutomatically')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", UserSecurityvue_type_template_id_60a12226_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_CreationDate')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_LastUsed')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_SecureUseOnly')), 1), _ctx.hasTokensWithExpireDate ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: 0,
        title: _ctx.translate('UsersManager_TokensWithExpireDateCreationBySystem')
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_ExpireDate')), 9, UserSecurityvue_type_template_id_60a12226_hoisted_13)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Actions')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [!((_ctx$tokens = _ctx.tokens) !== null && _ctx$tokens !== void 0 && _ctx$tokens.length) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", UserSecurityvue_type_template_id_60a12226_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
        colspan: _ctx.hasTokensWithExpireDate ? 5 : 4,
        innerHTML: _ctx.$sanitize(_ctx.noTokenCreatedYetText)
      }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_15)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.tokens || [], theToken => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          key: theToken.idusertokenauth
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", UserSecurityvue_type_template_id_60a12226_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(theToken.date_created), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(theToken.description), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(theToken.last_used ? theToken.last_used : _ctx.translate('General_Never')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(parseInt(theToken.secure_only, 10) === 1 ? _ctx.translate('General_Yes') : _ctx.translate('General_No')), 1), _ctx.hasTokensWithExpireDate ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: 0,
          title: _ctx.translate('UsersManager_TokensWithExpireDateCreationBySystem')
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(theToken.date_expired ? theToken.date_expired : _ctx.translate('General_Never')), 9, UserSecurityvue_type_template_id_60a12226_hoisted_17)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
          method: "post",
          action: _ctx.deleteTokenAction,
          style: {
            "display": "inline"
          }
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          name: "nonce",
          type: "hidden",
          value: _ctx.deleteTokenNonce
        }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_19), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          name: "idtokenauth",
          type: "hidden",
          value: theToken.idusertokenauth
        }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_20), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          type: "submit",
          class: "table-action",
          title: _ctx.translate('General_Delete')
        }, UserSecurityvue_type_template_id_60a12226_hoisted_23, 8, UserSecurityvue_type_template_id_60a12226_hoisted_21)], 8, UserSecurityvue_type_template_id_60a12226_hoisted_18)])]);
      }), 128))])])), [[_directive_content_table]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", UserSecurityvue_type_template_id_60a12226_hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        href: _ctx.addNewTokenLink,
        class: "addNewToken"
      }, [UserSecurityvue_type_template_id_60a12226_hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_CreateNewToken')), 1)], 8, UserSecurityvue_type_template_id_60a12226_hoisted_25), (_ctx$tokens2 = _ctx.tokens) !== null && _ctx$tokens2 !== void 0 && _ctx$tokens2.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("form", {
        key: 0,
        method: "post",
        action: _ctx.deleteTokenAction,
        style: {
          "display": "inline"
        }
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        name: "nonce",
        type: "hidden",
        value: _ctx.deleteTokenNonce
      }, null, 8, UserSecurityvue_type_template_id_60a12226_hoisted_28), UserSecurityvue_type_template_id_60a12226_hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", UserSecurityvue_type_template_id_60a12226_hoisted_30, [UserSecurityvue_type_template_id_60a12226_hoisted_31, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('UsersManager_DeleteAllTokens')), 1)])], 8, UserSecurityvue_type_template_id_60a12226_hoisted_27)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])];
    }),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSecurity/UserSecurity.vue?vue&type=template&id=60a12226

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserSecurity/UserSecurity.vue?vue&type=script&lang=ts
function UserSecurityvue_type_script_lang_ts_extends() { UserSecurityvue_type_script_lang_ts_extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return UserSecurityvue_type_script_lang_ts_extends.apply(this, arguments); }



/* harmony default export */ var UserSecurityvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    deleteTokenNonce: String,
    tokens: Array,
    hasTokensWithExpireDate: Boolean,
    isUsersAdminEnabled: Boolean,
    changePasswordNonce: String,
    isValidHost: Boolean,
    isSuperUser: Boolean,
    invalidHost: String,
    afterPasswordEventContent: String,
    invalidHostMailLinkStart: String
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    Field: external_CorePluginsAdmin_["Field"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"]
  },
  data() {
    return {
      password: '',
      passwordBis: '',
      passwordConfirmation: ''
    };
  },
  mounted() {
    const afterPassword = this.$refs.afterPassword;
    external_CoreHome_["Matomo"].helper.compileVueEntryComponents(afterPassword);
  },
  computed: {
    recordPasswordChangeAction() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSecurityvue_type_script_lang_ts_extends(UserSecurityvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'recordPasswordChange'
      }))}`;
    },
    emailYourAdminText() {
      return Object(external_CoreHome_["translate"])('UsersManager_EmailYourAdministrator', this.invalidHostMailLinkStart || '', '</a>');
    },
    noTokenCreatedYetText() {
      const addNewTokenLink = `?${external_CoreHome_["MatomoUrl"].stringify(UserSecurityvue_type_script_lang_ts_extends(UserSecurityvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'addNewToken'
      }))}`;
      return Object(external_CoreHome_["translate"])('UsersManager_NoTokenCreatedYetCreateNow', `<a href="${addNewTokenLink}">`, '</a>');
    },
    deleteTokenAction() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSecurityvue_type_script_lang_ts_extends(UserSecurityvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'deleteToken'
      }))}`;
    },
    addNewTokenLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify(UserSecurityvue_type_script_lang_ts_extends(UserSecurityvue_type_script_lang_ts_extends({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'UsersManager',
        action: 'addNewToken'
      }))}`;
    },
    afterPasswordComponent() {
      if (!this.afterPasswordEventContent) {
        return null;
      }
      const afterPassword = this.$refs.afterPassword;
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])({
        template: this.afterPasswordEventContent,
        beforeUnmount() {
          external_CoreHome_["Matomo"].helper.destroyVueComponent(afterPassword);
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSecurity/UserSecurity.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSecurity/UserSecurity.vue



UserSecurityvue_type_script_lang_ts.render = UserSecurityvue_type_template_id_60a12226_render

/* harmony default export */ var UserSecurity = (UserSecurityvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=2115584c

const UserSettingsvue_type_template_id_2115584c_hoisted_1 = ["innerHTML"];
const UserSettingsvue_type_template_id_2115584c_hoisted_2 = {
  style: {
    "margin-left": "20px"
  }
};
const UserSettingsvue_type_template_id_2115584c_hoisted_3 = ["href"];
const UserSettingsvue_type_template_id_2115584c_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
function UserSettingsvue_type_template_id_2115584c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_PersonalSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PersonalSettings");
  const _component_NewsletterSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("NewsletterSettings");
  const _component_PluginSettings = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PluginSettings");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PersonalSettings, {
    "is-users-admin-enabled": _ctx.isUsersAdminEnabled,
    title: _ctx.translate('UsersManager_PersonalSettings'),
    "user-login": _ctx.userLogin,
    "user-email": _ctx.userEmail,
    "current-language-code": _ctx.currentLanguageCode,
    "language-options": _ctx.languageOptions,
    "current-timeformat": _ctx.currentTimeformat,
    "time-formats": _ctx.timeFormats,
    "default-report": _ctx.defaultReport,
    "default-report-options": _ctx.defaultReportOptions,
    "default-report-id-site": _ctx.defaultReportIdSite,
    "default-report-site-name": _ctx.defaultReportSiteName,
    "default-date": _ctx.defaultDate,
    "available-default-dates": _ctx.availableDefaultDates
  }, null, 8, ["is-users-admin-enabled", "title", "user-login", "user-email", "current-language-code", "language-options", "current-timeformat", "time-formats", "default-report", "default-report-options", "default-report-id-site", "default-report-site-name", "default-date", "available-default-dates"]), _ctx.showNewsletterSignup ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_NewsletterSettings, {
    key: 0
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PluginSettings, {
    mode: "user"
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('UsersManager_ExcludeVisitsViaCookie')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.yourVisitsAreText)
    }, null, 8, UserSettingsvue_type_template_id_2115584c_hoisted_1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", UserSettingsvue_type_template_id_2115584c_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      href: _ctx.setIgnoreCookieLink
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" › " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.ignoreCookieSet ? _ctx.translate('UsersManager_ClickHereToDeleteTheCookie') : _ctx.translate('UsersManager_ClickHereToSetTheCookieOnDomain', _ctx.piwikHost)) + " ", 1), UserSettingsvue_type_template_id_2115584c_hoisted_4], 8, UserSettingsvue_type_template_id_2115584c_hoisted_3)])]),
    _: 1
  }, 8, ["content-title"])]);
}
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSettings/UserSettings.vue?vue&type=template&id=2115584c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/UsersManager/vue/src/UserSettings/UserSettings.vue?vue&type=script&lang=ts





/* harmony default export */ var UserSettingsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    isUsersAdminEnabled: {
      type: Boolean,
      required: true
    },
    userLogin: {
      type: String,
      required: true
    },
    userEmail: {
      type: String,
      required: true
    },
    currentLanguageCode: {
      type: String,
      required: true
    },
    languageOptions: {
      type: Object,
      required: true
    },
    currentTimeformat: {
      type: Number,
      required: true
    },
    timeFormats: {
      type: Object,
      required: true
    },
    defaultReport: {
      type: [String, Number],
      required: true
    },
    defaultReportOptions: {
      type: Object,
      required: true
    },
    defaultReportIdSite: {
      type: [String, Number],
      required: true
    },
    defaultReportSiteName: {
      type: String,
      required: true
    },
    defaultDate: {
      type: String,
      required: true
    },
    availableDefaultDates: {
      type: Object,
      required: true
    },
    showNewsletterSignup: Boolean,
    ignoreCookieSet: Boolean,
    ignoreSalt: [String, Number, Boolean],
    piwikHost: {
      type: String,
      required: true
    }
  },
  components: {
    ContentBlock: external_CoreHome_["ContentBlock"],
    PersonalSettings: PersonalSettings,
    NewsletterSettings: NewsletterSettings,
    PluginSettings: external_CorePluginsAdmin_["PluginSettings"]
  },
  computed: {
    yourVisitsAreText() {
      if (this.ignoreCookieSet) {
        return Object(external_CoreHome_["translate"])('UsersManager_YourVisitsAreIgnoredOnDomain', '<strong>', this.piwikHost, '</strong>');
      }
      return Object(external_CoreHome_["translate"])('UsersManager_YourVisitsAreNotIgnored', '<strong>', '</strong>');
    },
    setIgnoreCookieLink() {
      return `?${external_CoreHome_["MatomoUrl"].stringify({
        ignoreSalt: this.ignoreSalt,
        module: 'UsersManager',
        action: 'setIgnoreCookie'
      })}#excludeCookie`;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSettings/UserSettings.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/UserSettings/UserSettings.vue



UserSettingsvue_type_script_lang_ts.render = UserSettingsvue_type_template_id_2115584c_render

/* harmony default export */ var UserSettings = (UserSettingsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/UsersManager/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */












// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=UsersManager.umd.js.map