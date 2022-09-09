(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Goals"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Goals"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
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
/******/ 	__webpack_require__.p = "plugins/Goals/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "GoalPageLink", function() { return /* reexport */ GoalPageLink_GoalPageLink; });
__webpack_require__.d(__webpack_exports__, "ManageGoals", function() { return /* reexport */ ManageGoals; });
__webpack_require__.d(__webpack_exports__, "ManageGoalsStore", function() { return /* reexport */ ManageGoals_store; });
__webpack_require__.d(__webpack_exports__, "PiwikApiMock", function() { return /* reexport */ PiwikApiMock; });

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

// CONCATENATED MODULE: ./plugins/Goals/vue/src/GoalPageLink/GoalPageLink.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var _window = window,
    GoalPageLink_$ = _window.$; // usage v-goal-page-link="{ idGoal: 5 }"

var GoalPageLink = {
  mounted: function mounted(el, binding) {
    if (!external_CoreHome_["Matomo"].helper.isAngularRenderingThePage()) {
      return;
    }

    var title = GoalPageLink_$(el).text();
    var link = GoalPageLink_$('<a></a>');
    link.text(title);
    link.attr('title', Object(external_CoreHome_["translate"])('Goals_ClickToViewThisGoal'));
    link.click(function (e) {
      e.preventDefault();
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        category: 'Goals_Goals',
        subcategory: binding.value.idGoal
      }));
    });
    GoalPageLink_$(el).html(link[0]);
  }
};
/* harmony default export */ var GoalPageLink_GoalPageLink = (GoalPageLink); // manually handle occurrence of goal-page-link on datatable html attributes since dataTable.js is
// not managed by vue.
// eslint-disable-next-line @typescript-eslint/no-explicit-any

external_CoreHome_["Matomo"].on('Matomo.processDynamicHtml', function ($element) {
  $element.find('[goal-page-link]').each(function (i, e) {
    if (GoalPageLink_$(e).attr('goal-page-link-handled')) {
      return;
    }

    var idGoal = GoalPageLink_$(e).attr('goal-page-link');

    if (idGoal) {
      GoalPageLink.mounted(e, {
        instance: null,
        value: {
          idGoal: idGoal
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      });
    }

    GoalPageLink_$(e).attr('goal-page-link-handled', '1');
  });
});
// CONCATENATED MODULE: ./plugins/Goals/vue/src/GoalPageLink/GoalPageLink.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function piwikGoalPageLink() {
  return {
    restrict: 'A',
    link: function piwikGoalPageLinkLink(scope, element, attrs) {
      var binding = {
        instance: null,
        value: {
          idGoal: attrs.piwikGoalPageLink
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      };
      GoalPageLink_GoalPageLink.mounted(element[0], binding);
    }
  };
}
window.angular.module('piwikApp').directive('piwikGoalPageLink', piwikGoalPageLink);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=template&id=1317ed06

var _hoisted_1 = {
  class: "manageGoals"
};
var _hoisted_2 = {
  id: "entityEditContainer",
  feature: "true",
  class: "managegoals"
};
var _hoisted_3 = {
  class: "contentHelp"
};
var _hoisted_4 = ["innerHTML"];
var _hoisted_5 = {
  key: 0
};

var _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_8 = ["innerHTML"];
var _hoisted_9 = {
  class: "first"
};
var _hoisted_10 = {
  key: 1
};
var _hoisted_11 = {
  key: 2
};
var _hoisted_12 = {
  key: 0
};
var _hoisted_13 = {
  colspan: "8"
};

var _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_17 = ["id"];
var _hoisted_18 = {
  class: "first"
};
var _hoisted_19 = {
  class: "matchAttribute"
};
var _hoisted_20 = {
  key: 0
};
var _hoisted_21 = {
  key: 1
};

var _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_23 = ["innerHTML"];
var _hoisted_24 = {
  key: 1,
  style: {
    "padding-top": "2px"
  }
};
var _hoisted_25 = ["onClick", "title"];

var _hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-edit"
}, null, -1);

var _hoisted_27 = [_hoisted_26];
var _hoisted_28 = {
  key: 2,
  style: {
    "padding-top": "2px"
  }
};
var _hoisted_29 = ["onClick", "title"];

var _hoisted_30 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-delete"
}, null, -1);

var _hoisted_31 = [_hoisted_30];
var _hoisted_32 = {
  key: 0,
  class: "tableActionBar"
};

var _hoisted_33 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);

var _hoisted_34 = {
  class: "ui-confirm",
  ref: "confirm"
};
var _hoisted_35 = ["value"];
var _hoisted_36 = ["value"];
var _hoisted_37 = {
  class: "addEditGoal"
};
var _hoisted_38 = ["innerHTML"];
var _hoisted_39 = {
  class: "row goalIsTriggeredWhen"
};
var _hoisted_40 = {
  class: "col s12"
};
var _hoisted_41 = {
  class: "row"
};
var _hoisted_42 = {
  class: "col s12 m6 goalTriggerType"
};
var _hoisted_43 = {
  class: "col s12 m6"
};
var _hoisted_44 = ["innerHTML"];
var _hoisted_45 = {
  class: "row whereTheMatchAttrbiute"
};
var _hoisted_46 = {
  class: "col s12"
};
var _hoisted_47 = {
  class: "row"
};
var _hoisted_48 = {
  class: "col s12 m6 l4"
};
var _hoisted_49 = {
  key: 0,
  class: "col s12 m6 l4"
};
var _hoisted_50 = {
  key: 1,
  class: "col s12 m6 l4"
};
var _hoisted_51 = {
  class: "col s12 m6 l4"
};
var _hoisted_52 = {
  id: "examples_pattern",
  class: "col s12"
};

var _hoisted_53 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_54 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_55 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_56 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_57 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_58 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_59 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_60 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

var _hoisted_61 = {
  ref: "endedittable"
};

var _hoisted_62 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "goalIdUpdate",
  value: ""
}, null, -1);

var _hoisted_63 = {
  key: 0
};
var _hoisted_64 = ["innerHTML"];

var _hoisted_65 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  id: "bottom"
}, null, -1);

function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$goalToDelete;

  var _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");

  var _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");

  var _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");

  var _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");

  var _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");

  var _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");

  var _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Goals_ManageGoals')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
        loading: _ctx.isLoading
      }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.learnMoreAboutGoalTracking)
      }, null, 8, _hoisted_4), !_ctx.ecommerceEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, [_hoisted_6, _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Optional')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Ecommerce')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.youCanEnableEcommerceReports)
      }, null, 8, _hoisted_8)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalName')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalIsTriggeredWhen')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1), _ctx.beforeGoalListActionsHeadComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.beforeGoalListActionsHeadComponent), {
        key: 0
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Edit')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [!Object.keys(_ctx.goals || {}).length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_13, [_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_ThereIsNoGoalToManage', _ctx.siteName)) + " ", 1), _hoisted_15, _hoisted_16])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.goals || [], function (goal) {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
          id: goal.idgoal,
          key: goal.idgoal
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.idgoal), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.description), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.goalMatchAttributeTranslations[goal.match_attribute] || goal.match_attribute), 1), goal.match_attribute === 'visit_duration' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.lcfirst(_ctx.translate('General_OperationGreaterThan'))) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Intl_NMinutes', goal.pattern)), 1)) : !!goal.pattern_type ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_21, [_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Pattern')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.pattern_type) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.pattern), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
          class: "center",
          innerHTML: _ctx.$sanitize(goal.revenue === 0 || goal.revenue === '0' ? '-' : goal.revenue_pretty)
        }, null, 8, _hoisted_23), _ctx.beforeGoalListActionsBodyComponent[goal.idgoal] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.beforeGoalListActionsBodyComponent[goal.idgoal]), {
          key: 0
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          onClick: function onClick($event) {
            return _ctx.editGoal(goal.idgoal);
          },
          class: "table-action",
          title: _ctx.translate('General_Edit')
        }, _hoisted_27, 8, _hoisted_25)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          onClick: function onClick($event) {
            return _ctx.deleteGoal(goal.idgoal);
          },
          class: "table-action",
          title: _ctx.translate('General_Delete')
        }, _hoisted_31, 8, _hoisted_29)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, _hoisted_17);
      }), 128))])], 512), [[_directive_content_table]]), _ctx.userCanEditGoals && !_ctx.onlyShowAddNewGoal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_32, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        id: "add-goal",
        onClick: _cache[0] || (_cache[0] = function ($event) {
          return _ctx.createGoal();
        })
      }, [_hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_AddNewGoal')), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)];
    }),
    _: 1
  }, 8, ["content-title"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showGoalList]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_34, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_DeleteGoalConfirm', "\"".concat((_ctx$goalToDelete = _ctx.goalToDelete) === null || _ctx$goalToDelete === void 0 ? void 0 : _ctx$goalToDelete.name, "\""))), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_35), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_36)], 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.onlyShowAddNewGoal]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.goal.idgoal ? _ctx.translate('Goals_UpdateGoal') : _ctx.translate('Goals_AddNewGoal')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
      return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        innerHTML: _ctx.$sanitize(_ctx.addNewGoalIntro)
      }, null, 8, _hoisted_38), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "goal_name",
        modelValue: _ctx.goal.name,
        "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
          return _ctx.goal.name = $event;
        }),
        maxlength: 50,
        title: _ctx.translate('Goals_GoalName')
      }, null, 8, ["modelValue", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "goal_description",
        modelValue: _ctx.goal.description,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = function ($event) {
          return _ctx.goal.description = $event;
        }),
        maxlength: 255,
        title: _ctx.translate('General_Description')
      }, null, 8, ["modelValue", "title"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_40, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalIsTriggered')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "trigger_type",
        "model-value": _ctx.triggerType,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = function ($event) {
          _ctx.triggerType = $event;

          _ctx.changedTriggerType();
        }),
        "full-width": true,
        options: _ctx.goalTriggerTypeOptions
      }, null, 8, ["model-value", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_43, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
        severity: "info"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
            innerHTML: _ctx.$sanitize(_ctx.whereVisitedPageManuallyCallsJsTrackerText)
          }, null, 8, _hoisted_44)];
        }),
        _: 1
      }, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType === 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "radio",
        name: "match_attribute",
        "full-width": true,
        "model-value": _ctx.goal.match_attribute,
        "onUpdate:modelValue": _cache[4] || (_cache[4] = function ($event) {
          _ctx.goal.match_attribute = $event;

          _ctx.initPatternType();
        }),
        options: _ctx.goalMatchAttributeOptions
      }, null, 8, ["model-value", "options"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_45, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_WhereThe')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_URL')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'url']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_PageTitle')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'title']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Filename')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'file']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_ExternalWebsiteUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'external_website']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_VisitDuration')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'visit_duration']])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_47, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_48, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "event_type",
        modelValue: _ctx.eventType,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = function ($event) {
          return _ctx.eventType = $event;
        }),
        "full-width": true,
        options: _ctx.eventTypeOptions
      }, null, 8, ["modelValue", "options"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']]), !_ctx.isMatchAttributeNumeric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_49, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "pattern_type",
        modelValue: _ctx.goal.pattern_type,
        "onUpdate:modelValue": _cache[6] || (_cache[6] = function ($event) {
          return _ctx.goal.pattern_type = $event;
        }),
        "full-width": true,
        options: _ctx.patternTypeOptions
      }, null, 8, ["modelValue", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isMatchAttributeNumeric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_50, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "select",
        name: "pattern_type",
        modelValue: _ctx.goal.pattern_type,
        "onUpdate:modelValue": _cache[7] || (_cache[7] = function ($event) {
          return _ctx.goal.pattern_type = $event;
        }),
        "full-width": true,
        options: _ctx.numericComparisonTypeOptions
      }, null, 8, ["modelValue", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_51, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "text",
        name: "pattern",
        modelValue: _ctx.goal.pattern,
        "onUpdate:modelValue": _cache[8] || (_cache[8] = function ($event) {
          return _ctx.goal.pattern = $event;
        }),
        maxlength: 255,
        title: _ctx.patternFieldLabel,
        "full-width": true
      }, null, 8, ["modelValue", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_52, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
        severity: "info"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(function () {
          return [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'checkout/confirmation'")) + " ", 1), _hoisted_53, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://example.com/thank-you.html'")) + " ", 1), _hoisted_54, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)\\\/demo\\\/(.*)'")), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'url']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'Order confirmation'")), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'title']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'files/brochure.pdf'")) + " ", 1), _hoisted_55, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://example.com/files/brochure.pdf'")) + " ", 1), _hoisted_56, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)\\\.zip'")), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'file']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'amazon.com'")) + " ", 1), _hoisted_57, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://mypartner.com/landing.html'")) + " ", 1), _hoisted_58, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.matchesExpressionExternal), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'external_website']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'video'")) + " ", 1), _hoisted_59, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'click'")) + " ", 1), _hoisted_60, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)_banner'")) + "\" ", 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_AtLeastMinutes', '5', '0.5')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'visit_duration']])];
        }),
        _: 1
      })])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "case_sensitive",
        modelValue: _ctx.goal.case_sensitive,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = function ($event) {
          return _ctx.goal.case_sensitive = $event;
        }),
        title: _ctx.caseSensitiveTitle
      }, null, 8, ["modelValue", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually' && !_ctx.isMatchAttributeNumeric]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.goal.match_attribute !== 'visit_duration' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
        key: 0,
        uicontrol: "radio",
        name: "allow_multiple",
        "model-value": !!_ctx.goal.allow_multiple && _ctx.goal.allow_multiple !== '0' ? 1 : 0,
        "onUpdate:modelValue": _cache[10] || (_cache[10] = function ($event) {
          return _ctx.goal.allow_multiple = $event;
        }),
        options: _ctx.allowMultipleOptions,
        introduction: _ctx.translate('Goals_AllowMultipleConversionsPerVisit'),
        "inline-help": _ctx.translate('Goals_HelpOneConversionPerVisit')
      }, null, 8, ["model-value", "options", "introduction", "inline-help"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalRevenue')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Optional')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "number",
        name: "revenue",
        modelValue: _ctx.goal.revenue,
        "onUpdate:modelValue": _cache[11] || (_cache[11] = function ($event) {
          return _ctx.goal.revenue = $event;
        }),
        placeholder: _ctx.translate('Goals_DefaultRevenueLabel'),
        "inline-help": _ctx.translate('Goals_DefaultRevenueHelp')
      }, null, 8, ["modelValue", "placeholder", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
        uicontrol: "checkbox",
        name: "use_event_value",
        modelValue: _ctx.goal.event_value_as_revenue,
        "onUpdate:modelValue": _cache[12] || (_cache[12] = function ($event) {
          return _ctx.goal.event_value_as_revenue = $event;
        }),
        title: _ctx.translate('Goals_UseEventValueAsRevenue'),
        "inline-help": _ctx.useEventValueAsRevenueHelp
      }, null, 8, ["modelValue", "title", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_61, [_ctx.endEditTableComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.endEditTableComponent), {
        key: 0
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), _hoisted_62, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
        saving: _ctx.isLoading,
        onConfirm: _cache[13] || (_cache[13] = function ($event) {
          return _ctx.save();
        }),
        value: _ctx.submitText
      }, null, 8, ["saving", "value"]), !_ctx.onlyShowAddNewGoal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_63, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
        class: "entityCancel",
        onClick: _cache[14] || (_cache[14] = function ($event) {
          return _ctx.showListOfReports();
        }),
        innerHTML: _ctx.$sanitize(_ctx.cancelText)
      }, null, 8, _hoisted_64), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEditGoal]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), [[_directive_form]])];
    }),
    _: 1
  }, 8, ["content-title"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEditGoal]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.userCanEditGoals]]), _hoisted_65]);
}
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=template&id=1317ed06

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/PiwikApiMock.ts
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
// the piwikApi angularjs service is passed in some frontend events to allow plugins to modify
// a request before it is sent. for the time being in Vue we use this mock, which has the same
// API as the piwikApi service, to modify the input used with AjaxHelper. this provides BC
// with for plugins that haven't been converted.
//
// should be removed in Matomo 5.
var PiwikApiMock = /*#__PURE__*/function () {
  function PiwikApiMock(parameters, options) {
    _classCallCheck(this, PiwikApiMock);

    _defineProperty(this, "parameters", void 0);

    _defineProperty(this, "options", void 0);

    this.parameters = parameters;
    this.options = options;
  }

  _createClass(PiwikApiMock, [{
    key: "addParams",
    value: function addParams(params) {
      Object.assign(this.parameters, params);
    }
  }, {
    key: "withTokenInUrl",
    value: function withTokenInUrl() {
      this.options.withTokenInUrl = true;
    }
  }, {
    key: "reset",
    value: function reset() {
      var _this = this;

      Object.keys(this.parameters).forEach(function (name) {
        delete _this.parameters[name];
      });
      delete this.options.postParams;
    }
  }, {
    key: "addPostParams",
    value: function addPostParams(params) {
      this.options.postParams = Object.assign(Object.assign({}, this.options.postParams), params);
    }
  }]);

  return PiwikApiMock;
}();


// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.store.ts
function ManageGoals_store_classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function ManageGoals_store_defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function ManageGoals_store_createClass(Constructor, protoProps, staticProps) { if (protoProps) ManageGoals_store_defineProperties(Constructor.prototype, protoProps); if (staticProps) ManageGoals_store_defineProperties(Constructor, staticProps); return Constructor; }

function ManageGoals_store_defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


var ManageGoals_store_ManageGoalsStore = /*#__PURE__*/function () {
  function ManageGoalsStore() {
    var _this = this;

    ManageGoals_store_classCallCheck(this, ManageGoalsStore);

    ManageGoals_store_defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({}));

    ManageGoals_store_defineProperty(this, "idGoal", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(function () {
      return _this.privateState.idGoal;
    }));
  }

  ManageGoals_store_createClass(ManageGoalsStore, [{
    key: "setIdGoalShown",
    value: function setIdGoalShown(idGoal) {
      this.privateState.idGoal = idGoal;
    }
  }]);

  return ManageGoalsStore;
}();

/* harmony default export */ var ManageGoals_store = (new ManageGoals_store_ManageGoalsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=script&lang=ts






function ambiguousBoolToInt(n) {
  return !!n && n !== '0' ? 1 : 0;
}

/* harmony default export */ var ManageGoalsvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  inheritAttrs: false,
  props: {
    onlyShowAddNewGoal: Boolean,
    userCanEditGoals: Boolean,
    ecommerceEnabled: Boolean,
    goals: {
      type: Object,
      required: true
    },
    addNewGoalIntro: String,
    goalTriggerTypeOptions: Object,
    goalMatchAttributeOptions: Array,
    eventTypeOptions: Array,
    patternTypeOptions: Array,
    numericComparisonTypeOptions: Array,
    allowMultipleOptions: Array,
    showAddGoal: Boolean,
    showGoal: Number,
    beforeGoalListActionsBody: Object,
    endEditTable: String,
    beforeGoalListActionsHead: String
  },
  data: function data() {
    return {
      showEditGoal: false,
      showGoalList: true,
      goal: {},
      isLoading: false,
      eventType: 'event_category',
      triggerType: 'visitors',
      apiMethod: '',
      submitText: '',
      goalToDelete: null,
      addEditTableComponent: false
    };
  },
  components: {
    SaveButton: external_CorePluginsAdmin_["SaveButton"],
    ContentBlock: external_CoreHome_["ContentBlock"],
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Field: external_CorePluginsAdmin_["Field"],
    Alert: external_CoreHome_["Alert"]
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"],
    Form: external_CorePluginsAdmin_["Form"]
  },
  created: function created() {
    ManageGoals_store.setIdGoalShown(this.showGoal);
  },
  unmounted: function unmounted() {
    ManageGoals_store.setIdGoalShown(undefined);
  },
  mounted: function mounted() {
    var _this = this;

    if (this.showAddGoal) {
      this.createGoal();
    } else if (this.showGoal) {
      this.editGoal(this.showGoal);
    } else {
      this.showListOfReports();
    } // this component can be used in multiple places, one where
    // Matomo.helper.compileAngularComponents() is already called, one where it's not.
    // to make sure this function is only applied once to the slot data, we explicitly do not
    // add it to vue, then on the next update, add it and call compileAngularComponents()


    Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
      _this.addEditTableComponent = true;
      Object(external_commonjs_vue_commonjs2_vue_root_Vue_["nextTick"])(function () {
        var el = _this.$refs.endedittable;
        var scope = external_CoreHome_["Matomo"].helper.getAngularDependency('$rootScope').$new(true);
        $(el).data('scope', scope);
        external_CoreHome_["Matomo"].helper.compileAngularComponents(el, {
          scope: scope
        });
      });
    });
  },
  beforeUnmount: function beforeUnmount() {
    var el = this.$refs.endedittable;
    $(el).data('scope').$destroy();
  },
  methods: {
    scrollToTop: function scrollToTop() {
      setTimeout(function () {
        external_CoreHome_["Matomo"].helper.lazyScrollTo('.pageWrap', 200);
      });
    },
    initGoalForm: function initGoalForm(goalMethodAPI, submitText, goalName, description, matchAttribute, pattern, patternType, caseSensitive, revenue, allowMultiple, useEventValueAsRevenue, goalId) {
      external_CoreHome_["Matomo"].postEvent('Goals.beforeInitGoalForm', goalMethodAPI, goalId);
      this.apiMethod = goalMethodAPI;
      this.goal = {};
      this.goal.name = goalName;
      this.goal.description = description;
      var actualMatchAttribute = matchAttribute;

      if (actualMatchAttribute === 'manually') {
        this.triggerType = 'manually';
        actualMatchAttribute = 'url';
      } else {
        this.triggerType = 'visitors';
      }

      if (actualMatchAttribute.indexOf('event') === 0) {
        this.eventType = actualMatchAttribute;
        actualMatchAttribute = 'event';
      } else {
        this.eventType = 'event_category';
      }

      this.goal.match_attribute = actualMatchAttribute;
      this.goal.allow_multiple = allowMultiple;
      this.goal.pattern_type = patternType;
      this.goal.pattern = pattern;
      this.goal.case_sensitive = caseSensitive;
      this.goal.revenue = revenue;
      this.goal.event_value_as_revenue = useEventValueAsRevenue;
      this.submitText = submitText;
      this.goal.idgoal = goalId;
    },
    showListOfReports: function showListOfReports() {
      external_CoreHome_["Matomo"].postEvent('Goals.cancelForm');
      this.showGoalList = true;
      this.showEditGoal = false;
      this.scrollToTop();
    },
    showAddEditForm: function showAddEditForm() {
      this.showGoalList = false;
      this.showEditGoal = true;
    },
    createGoal: function createGoal() {
      var parameters = {
        isAllowed: true
      };
      external_CoreHome_["Matomo"].postEvent('Goals.initAddGoal', parameters);

      if (parameters && !parameters.isAllowed) {
        return;
      }

      this.showAddEditForm();
      this.initGoalForm('Goals.addGoal', Object(external_CoreHome_["translate"])('Goals_AddGoal'), '', '', 'url', '', 'contains', false, 0, false, false, 0);
      this.scrollToTop();
    },
    editGoal: function editGoal(goalId) {
      this.showAddEditForm();
      var goal = this.goals["".concat(goalId)];
      this.initGoalForm('Goals.updateGoal', Object(external_CoreHome_["translate"])('Goals_UpdateGoal'), goal.name, goal.description, goal.match_attribute, goal.pattern, goal.pattern_type, !!goal.case_sensitive && goal.case_sensitive !== '0', parseInt("".concat(goal.revenue), 10), !!goal.allow_multiple && goal.allow_multiple !== '0', !!goal.event_value_as_revenue && goal.event_value_as_revenue !== '0', goalId);
      this.scrollToTop();
    },
    deleteGoal: function deleteGoal(goalId) {
      var _this2 = this;

      this.goalToDelete = this.goals["".concat(goalId)];
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirm, {
        yes: function yes() {
          _this2.isLoading = true;
          external_CoreHome_["AjaxHelper"].fetch({
            idGoal: goalId,
            method: 'Goals.deleteGoal'
          }).then(function () {
            window.location.reload();
          }).finally(function () {
            _this2.isLoading = false;
          });
        }
      });
    },
    save: function save() {
      var _this3 = this;

      var parameters = {}; // TODO: test removal of encoding, should be handled by ajax request

      parameters.name = this.goal.name;
      parameters.description = this.goal.description;

      if (this.isManuallyTriggered) {
        parameters.matchAttribute = 'manually';
        parameters.patternType = 'regex';
        parameters.pattern = '.*';
        parameters.caseSensitive = 0;
      } else {
        parameters.matchAttribute = this.goal.match_attribute;

        if (parameters.matchAttribute === 'event') {
          parameters.matchAttribute = this.eventType;
        }

        parameters.patternType = this.goal.pattern_type;
        parameters.pattern = this.goal.pattern;
        parameters.caseSensitive = ambiguousBoolToInt(this.goal.case_sensitive);
      }

      parameters.revenue = this.goal.revenue || 0;
      parameters.allowMultipleConversionsPerVisit = ambiguousBoolToInt(this.goal.allow_multiple);
      parameters.useEventValueAsRevenue = ambiguousBoolToInt(this.goal.event_value_as_revenue);
      parameters.idGoal = this.goal.idgoal;
      parameters.method = this.apiMethod;
      var isCreate = parameters.method === 'Goals.addGoal';
      var isUpdate = parameters.method === 'Goals.updateGoal';
      var options = {};
      var piwikApiMock = new PiwikApiMock(parameters, options);

      if (isUpdate) {
        external_CoreHome_["Matomo"].postEvent('Goals.beforeUpdateGoal', parameters, piwikApiMock);
      } else if (isCreate) {
        external_CoreHome_["Matomo"].postEvent('Goals.beforeAddGoal', parameters, piwikApiMock);
      }

      if (parameters !== null && parameters !== void 0 && parameters.cancelRequest) {
        return;
      }

      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch(parameters, options).then(function () {
        var subcategory = external_CoreHome_["MatomoUrl"].parsed.value.subcategory;

        if (subcategory === 'Goals_AddNewGoal' && external_CoreHome_["Matomo"].helper.isAngularRenderingThePage()) {
          // when adding a goal for the first time we need to load manage goals page afterwards
          external_CoreHome_["ReportingMenuStore"].reloadMenuItems().then(function () {
            external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
              subcategory: 'Goals_ManageGoals'
            }));
            _this3.isLoading = false;
          });
        } else {
          window.location.reload();
        }
      }).catch(function () {
        _this3.scrollToTop();

        _this3.isLoading = false;
      });
    },
    changedTriggerType: function changedTriggerType() {
      if (!this.isManuallyTriggered && !this.goal.pattern_type) {
        this.goal.pattern_type = 'contains';
      }
    },
    initPatternType: function initPatternType() {
      if (this.isMatchAttributeNumeric) {
        this.goal.pattern_type = 'greater_than';
      } else {
        this.goal.pattern_type = 'contains';
      }
    },
    lcfirst: function lcfirst(s) {
      return "".concat(s.slice(0, 1).toLowerCase()).concat(s.slice(1));
    },
    ucfirst: function ucfirst(s) {
      return "".concat(s.slice(0, 1).toUpperCase()).concat(s.slice(1));
    }
  },
  computed: {
    learnMoreAboutGoalTracking: function learnMoreAboutGoalTracking() {
      return Object(external_CoreHome_["translate"])('Goals_LearnMoreAboutGoalTrackingDocumentation', '<a target="_blank" rel="noreferrer noopener" ' + 'href="https://matomo.org/docs/tracking-goals-web-analytics/">', '</a>');
    },
    youCanEnableEcommerceReports: function youCanEnableEcommerceReports() {
      var link = external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'SitesManager',
        action: 'index'
      }));
      var ecommerceReportsText = '<a href="https://matomo.org/docs/ecommerce-analytics/" ' + "rel=\"noreferrer noopener\" target=\"_blank\">".concat(Object(external_CoreHome_["translate"])('Goals_EcommerceReports'), "</a>");
      var websiteManageText = "<a href='".concat(link, "'>").concat(Object(external_CoreHome_["translate"])('SitesManager_WebsitesManagement'), "</a>");
      return Object(external_CoreHome_["translate"])('Goals_YouCanEnableEcommerceReports', ecommerceReportsText, websiteManageText);
    },
    siteName: function siteName() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(external_CoreHome_["Matomo"].siteName);
    },
    whereVisitedPageManuallyCallsJsTrackerText: function whereVisitedPageManuallyCallsJsTrackerText() {
      var link = 'https://developer.matomo.org/guides/tracking-javascript-guide#manually-trigger-goal-conversions';
      return Object(external_CoreHome_["translate"])('Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore', "<a target=\"_blank\" rel=\"noreferrer noopener\" href=\"".concat(link, "\">"), '</a>');
    },
    caseSensitiveTitle: function caseSensitiveTitle() {
      return "".concat(Object(external_CoreHome_["translate"])('Goals_CaseSensitive'), " ").concat(Object(external_CoreHome_["translate"])('Goals_Optional'));
    },
    useEventValueAsRevenueHelp: function useEventValueAsRevenueHelp() {
      return "".concat(Object(external_CoreHome_["translate"])('Goals_EventValueAsRevenueHelp'), " <br/><br/> ").concat(Object(external_CoreHome_["translate"])('Goals_EventValueAsRevenueHelp2'));
    },
    cancelText: function cancelText() {
      return Object(external_CoreHome_["translate"])('General_OrCancel', '<a class=\'entityCancelLink\'>', '</a>');
    },
    isMatchAttributeNumeric: function isMatchAttributeNumeric() {
      return ['visit_duration'].indexOf(this.goal.match_attribute) > -1;
    },
    patternFieldLabel: function patternFieldLabel() {
      return this.goal.match_attribute === 'visit_duration' ? Object(external_CoreHome_["translate"])('Goals_TimeInMinutes') : Object(external_CoreHome_["translate"])('Goals_Pattern');
    },
    goalMatchAttributeTranslations: function goalMatchAttributeTranslations() {
      return {
        manually: Object(external_CoreHome_["translate"])('Goals_ManuallyTriggeredUsingJavascriptFunction'),
        file: Object(external_CoreHome_["translate"])('Goals_Download'),
        url: Object(external_CoreHome_["translate"])('Goals_VisitUrl'),
        title: Object(external_CoreHome_["translate"])('Goals_VisitPageTitle'),
        external_website: Object(external_CoreHome_["translate"])('Goals_ClickOutlink'),
        event_action: "".concat(Object(external_CoreHome_["translate"])('Goals_SendEvent'), " (").concat(Object(external_CoreHome_["translate"])('Events_EventAction'), ")"),
        event_category: "".concat(Object(external_CoreHome_["translate"])('Goals_SendEvent'), " (").concat(Object(external_CoreHome_["translate"])('Events_EventCategory'), ")"),
        event_name: "".concat(Object(external_CoreHome_["translate"])('Goals_SendEvent'), " (").concat(Object(external_CoreHome_["translate"])('Events_EventName'), ")"),
        visit_duration: "".concat(this.ucfirst(Object(external_CoreHome_["translate"])('Goals_VisitDuration')))
      };
    },
    beforeGoalListActionsBodyComponent: function beforeGoalListActionsBodyComponent() {
      var _this4 = this;

      if (!this.beforeGoalListActionsBody) {
        return {};
      }

      var componentsByIdGoal = {};
      Object.values(this.goals).forEach(function (g) {
        var template = _this4.beforeGoalListActionsBody[g.idgoal];

        if (!template) {
          return;
        }

        componentsByIdGoal[g.idgoal] = {
          template: template
        };
      });
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(componentsByIdGoal);
    },
    endEditTableComponent: function endEditTableComponent() {
      if (!this.endEditTable || !this.addEditTableComponent) {
        return null;
      }

      var endedittable = this.$refs.endedittable;
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])({
        template: this.endEditTable,
        mounted: function mounted() {
          external_CoreHome_["Matomo"].helper.compileVueEntryComponents(endedittable);
        },
        beforeUnmount: function beforeUnmount() {
          external_CoreHome_["Matomo"].helper.destroyVueComponent(endedittable);
        }
      });
    },
    beforeGoalListActionsHeadComponent: function beforeGoalListActionsHeadComponent() {
      if (!this.beforeGoalListActionsHead) {
        return null;
      }

      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])({
        template: this.beforeGoalListActionsHead
      });
    },
    isManuallyTriggered: function isManuallyTriggered() {
      return this.triggerType === 'manually';
    },
    matchesExpressionExternal: function matchesExpressionExternal() {
      var url = "'http://www.amazon.com\\/(.*)\\/yourAffiliateId'";
      return Object(external_CoreHome_["translate"])('Goals_MatchesExpression', url);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue



ManageGoalsvue_type_script_lang_ts.render = render

/* harmony default export */ var ManageGoals = (ManageGoalsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var ManageGoals_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: ManageGoals,
  directiveName: 'piwikManageGoals',
  scope: {
    userCanEditGoals: {
      angularJsBind: '<'
    },
    onlyShowAddNewGoal: {
      angularJsBind: '<'
    },
    ecommerceEnabled: {
      angularJsBind: '<'
    },
    goals: {
      angularJsBind: '<'
    },
    showGoal: {
      angularJsBind: '<'
    },
    showAddGoal: {
      angularJsBind: '<'
    },
    addNewGoalIntro: {
      angularJsBind: '<'
    },
    goalTriggerTypeOptions: {
      angularJsBind: '<'
    },
    goalMatchAttributeOptions: {
      angularJsBind: '<'
    },
    eventTypeOptions: {
      angularJsBind: '<'
    },
    patternTypeOptions: {
      angularJsBind: '<'
    },
    numericComparisonTypeOptions: {
      angularJsBind: '<'
    },
    allowMultipleOptions: {
      angularJsBind: '<'
    },
    beforeGoalListActionsBody: {
      angularJsBind: '<'
    },
    endEditTable: {
      angularJsBind: '<'
    },
    beforeGoalListActionsHead: {
      angularJsBind: '<'
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Goals/vue/src/index.ts
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
//# sourceMappingURL=Goals.umd.js.map