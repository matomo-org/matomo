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
__webpack_require__.d(__webpack_exports__, "RecommendGoals", function() { return /* reexport */ RecommendGoals; });

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
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const {
  $
} = window;
// usage v-goal-page-link="{ idGoal: 5 }"
const GoalPageLink = {
  mounted(el, binding) {
    if (!external_CoreHome_["Matomo"].helper.isReportingPage()) {
      return;
    }
    const title = $(el).text();
    const link = $('<a></a>');
    link.text(title);
    link.attr('title', Object(external_CoreHome_["translate"])('Goals_ClickToViewThisGoal'));
    link.click(e => {
      e.preventDefault();
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        category: 'Goals_Goals',
        subcategory: binding.value.idGoal
      }));
    });
    $(el).html(link[0]);
  }
};
/* harmony default export */ var GoalPageLink_GoalPageLink = (GoalPageLink);
// manually handle occurrence of goal-page-link on datatable html attributes since dataTable.js is
// not managed by vue.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
external_CoreHome_["Matomo"].on('Matomo.processDynamicHtml', $element => {
  $element.find('[goal-page-link]').each((i, e) => {
    if ($(e).attr('goal-page-link-handled')) {
      return;
    }
    const idGoal = $(e).attr('goal-page-link');
    if (idGoal) {
      GoalPageLink.mounted(e, {
        instance: null,
        value: {
          idGoal
        },
        oldValue: null,
        modifiers: {},
        dir: {}
      });
    }
    $(e).attr('goal-page-link-handled', '1');
  });
});
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=template&id=bcd8742c

const _hoisted_1 = {
  class: "manageGoals"
};
const _hoisted_2 = {
  id: "entityEditContainer",
  feature: "true",
  class: "managegoals"
};
const _hoisted_3 = {
  class: "contentHelp"
};
const _hoisted_4 = ["innerHTML"];
const _hoisted_5 = {
  key: 0
};
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_8 = ["innerHTML"];
const _hoisted_9 = {
  class: "first"
};
const _hoisted_10 = {
  class: "manageGoals-descriptionColumn"
};
const _hoisted_11 = {
  class: "manageGoals-triggerColumn"
};
const _hoisted_12 = {
  key: 1,
  class: "manageGoals-actionsColumn"
};
const _hoisted_13 = {
  key: 0
};
const _hoisted_14 = {
  colspan: "8"
};
const _hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_18 = ["id"];
const _hoisted_19 = {
  class: "first"
};
const _hoisted_20 = {
  class: "manageGoals-descriptionColumn"
};
const _hoisted_21 = {
  class: "manageGoals-triggerColumn"
};
const _hoisted_22 = {
  class: "matchAttribute"
};
const _hoisted_23 = {
  key: 0
};
const _hoisted_24 = {
  key: 1
};
const _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_26 = ["innerHTML"];
const _hoisted_27 = {
  key: 1,
  class: "entityTable_ActionCell entityTable_ActionCell-3 manageGoals-actionsColumn"
};
const _hoisted_28 = ["href", "title", "aria-label"];
const _hoisted_29 = ["onClick", "title"];
const _hoisted_30 = ["onClick", "title"];
const _hoisted_31 = {
  key: 0,
  class: "tableActionBar"
};
const _hoisted_32 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add"
}, null, -1);
const _hoisted_33 = {
  class: "ui-confirm",
  ref: "confirm"
};
const _hoisted_34 = ["value"];
const _hoisted_35 = ["value"];
const _hoisted_36 = {
  class: "addEditGoal"
};
const _hoisted_37 = ["innerHTML"];
const _hoisted_38 = {
  class: "row goalIsTriggeredWhen"
};
const _hoisted_39 = {
  class: "col s12"
};
const _hoisted_40 = {
  class: "row"
};
const _hoisted_41 = {
  class: "col s12 m6 goalTriggerType"
};
const _hoisted_42 = {
  class: "col s12 m6"
};
const _hoisted_43 = ["innerHTML"];
const _hoisted_44 = {
  class: "row whereTheMatchAttrbiute"
};
const _hoisted_45 = {
  class: "col s12"
};
const _hoisted_46 = {
  class: "row"
};
const _hoisted_47 = {
  class: "col s12 m6 l4"
};
const _hoisted_48 = {
  key: 0,
  class: "col s12 m6 l4"
};
const _hoisted_49 = {
  key: 1,
  class: "col s12 m6 l4"
};
const _hoisted_50 = {
  class: "col s12 m6 l4"
};
const _hoisted_51 = {
  id: "examples_pattern",
  class: "col s12"
};
const _hoisted_52 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_53 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_54 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_55 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_56 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_57 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_58 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_59 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_60 = {
  ref: "endedittable"
};
const _hoisted_61 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
  type: "hidden",
  name: "goalIdUpdate",
  value: ""
}, null, -1);
const _hoisted_62 = {
  key: 0
};
const _hoisted_63 = ["innerHTML"];
const _hoisted_64 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
  id: "bottom"
}, null, -1);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$goalToDelete;
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_ContentBlock = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ContentBlock");
  const _component_RecommendGoals = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RecommendGoals");
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_VueEntryContainer = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("VueEntryContainer");
  const _component_SaveButton = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveButton");
  const _directive_content_table = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("content-table");
  const _directive_form = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDirective"])("form");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.translate('Goals_ManageGoals')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
      loading: _ctx.isLoading
    }, null, 8, ["loading"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.learnMoreAboutGoalTracking)
    }, null, 8, _hoisted_4), !_ctx.ecommerceEnabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_5, [_hoisted_6, _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Optional')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Ecommerce')) + ": ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      innerHTML: _ctx.$sanitize(_ctx.youCanEnableEcommerceReports)
    }, null, 8, _hoisted_8)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("table", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Id')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalName')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Description')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalIsTriggeredWhen')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnRevenue')), 1), _ctx.beforeGoalListActionsHeadComponent ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.beforeGoalListActionsHeadComponent), {
      key: 0
    })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Actions')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [!Object.keys(_ctx.currentGoals || {}).length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_14, [_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_ThereIsNoGoalToManage', _ctx.siteName)) + " ", 1), _hoisted_16, _hoisted_17])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.currentGoals || [], goal => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        id: goal.idgoal,
        key: goal.idgoal
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.idgoal), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.description), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.goalMatchAttributeTranslations[goal.match_attribute] || goal.match_attribute), 1), goal.match_attribute === 'visit_duration' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.lcfirst(_ctx.translate('General_OperationGreaterThan'))) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Intl_NMinutes', goal.pattern)), 1)) : !!goal.pattern_type ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_24, [_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Pattern')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.pattern_type) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.pattern), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
        class: "center",
        innerHTML: _ctx.$sanitize(goal.revenue === 0 || goal.revenue === '0' ? '-' : goal.revenue_pretty)
      }, null, 8, _hoisted_26), _ctx.beforeGoalListActionsBodyComponent[goal.idgoal] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.beforeGoalListActionsBodyComponent[goal.idgoal]), {
        key: 0
      })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.userCanEditGoals ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", _hoisted_27, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
        class: "table-action icon-show",
        href: _ctx.getGoalReportUrl(goal.idgoal),
        title: _ctx.translate('Goals_ViewGoalReport'),
        "aria-label": _ctx.translate('Goals_ViewGoalReport')
      }, null, 8, _hoisted_28), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        onClick: $event => _ctx.editGoal(goal.idgoal),
        class: "table-action icon-edit",
        title: _ctx.translate('General_Edit')
      }, null, 8, _hoisted_29), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        onClick: $event => _ctx.deleteGoal(goal.idgoal),
        class: "table-action icon-delete",
        title: _ctx.translate('General_Delete')
      }, null, 8, _hoisted_30)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, _hoisted_18);
    }), 128))])])), [[_directive_content_table]]), _ctx.userCanEditGoals && !_ctx.onlyShowAddNewGoal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_31, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      id: "add-goal",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.createGoal())
    }, [_hoisted_32, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_AddNewGoal')), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  }, 8, ["content-title"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showGoalList]]), !_ctx.onlyShowAddNewGoal ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_RecommendGoals, {
    key: 0,
    goals: _ctx.currentGoals,
    "user-can-edit-goals": _ctx.userCanEditGoals,
    onCreated: _ctx.onRecommendedGoalsCreated,
    onPrefill: _ctx.prefillManualGoal
  }, null, 8, ["goals", "user-can-edit-goals", "onCreated", "onPrefill"])), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showGoalList]]) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_33, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_DeleteGoalConfirm', `"${(_ctx$goalToDelete = _ctx.goalToDelete) === null || _ctx$goalToDelete === void 0 ? void 0 : _ctx$goalToDelete.name}"`)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "yes",
    type: "button",
    value: _ctx.translate('General_Yes')
  }, null, 8, _hoisted_34), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    role: "no",
    type: "button",
    value: _ctx.translate('General_No')
  }, null, 8, _hoisted_35)], 512)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], !_ctx.onlyShowAddNewGoal]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.onlyShowAddNewGoal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_RecommendGoals, {
    key: 0,
    goals: _ctx.currentGoals,
    "user-can-edit-goals": _ctx.userCanEditGoals,
    onCreated: _ctx.onRecommendedGoalsCreated,
    onPrefill: _ctx.prefillManualGoal
  }, null, 8, ["goals", "user-can-edit-goals", "onCreated", "onPrefill"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_36, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ContentBlock, {
    "content-title": _ctx.goal.idgoal ? _ctx.translate('Goals_UpdateGoal') : _ctx.translate('Goals_AddNewGoal')
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      innerHTML: _ctx.$sanitize(_ctx.addNewGoalIntro)
    }, null, 8, _hoisted_37), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "goal_name",
      modelValue: _ctx.goal.name,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.goal.name = $event),
      maxlength: 50,
      autocomplete: "off",
      title: _ctx.translate('Goals_GoalName'),
      placeholder: _ctx.translate('Goals_GoalNamePlaceholder'),
      "inline-help": _ctx.translate('Goals_GoalNameHelpText'),
      onChange: _ctx.goalNameChanged
    }, null, 8, ["modelValue", "title", "placeholder", "inline-help", "onChange"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "textarea",
      name: "goal_description",
      modelValue: _ctx.goal.description,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.goal.description = $event),
      maxlength: 255,
      autocomplete: "off",
      title: `${_ctx.translate('General_Description')} ${_ctx.translate('Goals_Optional')}`,
      placeholder: _ctx.translate('Goals_GoalDescriptionPlaceholder'),
      "inline-help": _ctx.translate('Goals_GoalDescriptionHelpText'),
      "ui-control-attributes": {
        class: 'compact-textarea'
      }
    }, null, 8, ["modelValue", "title", "placeholder", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_38, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_39, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalIsTriggered')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_40, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_41, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "trigger_type",
      "model-value": _ctx.triggerType,
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => {
        _ctx.triggerType = $event;
        _ctx.changedTriggerType();
      }),
      "full-width": true,
      options: _ctx.goalTriggerTypeOptions
    }, null, 8, ["model-value", "options"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_42, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
      severity: "info"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        innerHTML: _ctx.$sanitize(_ctx.whereVisitedPageManuallyCallsJsTrackerText)
      }, null, 8, _hoisted_43)]),
      _: 1
    }, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType === 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "radio",
      name: "match_attribute",
      "full-width": true,
      "model-value": _ctx.goal.match_attribute,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => {
        _ctx.goal.match_attribute = $event;
        _ctx.initPatternType();
      }),
      options: _ctx.goalMatchAttributeOptions
    }, null, 8, ["model-value", "options"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']])])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_44, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_45, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_WhereThe')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_URL')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'url']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_PageTitle')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'title']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Filename')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'file']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_ExternalWebsiteUrl')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'external_website']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_VisitDuration')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'visit_duration']])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_47, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "event_type",
      modelValue: _ctx.eventType,
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.eventType = $event),
      "full-width": true,
      options: _ctx.eventTypeOptions
    }, null, 8, ["modelValue", "options"])])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']]), !_ctx.isMatchAttributeNumeric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_48, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "pattern_type",
      modelValue: _ctx.goal.pattern_type,
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.goal.pattern_type = $event),
      "full-width": true,
      options: _ctx.patternTypeOptions
    }, null, 8, ["modelValue", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.isMatchAttributeNumeric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_49, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "select",
      name: "pattern_type",
      modelValue: _ctx.goal.pattern_type,
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.goal.pattern_type = $event),
      "full-width": true,
      options: _ctx.numericComparisonTypeOptions
    }, null, 8, ["modelValue", "options"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_50, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "text",
      name: "pattern",
      modelValue: _ctx.goal.pattern,
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.goal.pattern = $event),
      maxlength: 255,
      autocomplete: "off",
      title: _ctx.patternFieldLabel,
      "full-width": true
    }, null, 8, ["modelValue", "title"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_51, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Alert, {
      severity: "info"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'checkout/confirmation'")) + " ", 1), _hoisted_52, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://example.com/thank-you.html'")) + " ", 1), _hoisted_53, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)\\\/demo\\\/(.*)'")), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'url']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'Order confirmation'")), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'title']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'files/brochure.pdf'")) + " ", 1), _hoisted_54, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://example.com/files/brochure.pdf'")) + " ", 1), _hoisted_55, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)\\\.zip'")), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'file']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'amazon.com'")) + " ", 1), _hoisted_56, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'http://mypartner.com/landing.html'")) + " ", 1), _hoisted_57, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.matchesExpressionExternal), 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'external_website']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Contains', "'video'")) + " ", 1), _hoisted_58, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_IsExactly', "'click'")) + " ", 1), _hoisted_59, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_MatchesExpression', "'(.*)_banner'")) + "\" ", 1)], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ForExampleShort')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_AtLeastMinutes', '5', '0.5')), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'visit_duration']])]),
      _: 1
    })])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually']]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "case_sensitive",
      modelValue: _ctx.goal.case_sensitive,
      "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.goal.case_sensitive = $event),
      title: _ctx.caseSensitiveTitle
    }, null, 8, ["modelValue", "title"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.triggerType !== 'manually' && !_ctx.isMatchAttributeNumeric]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [_ctx.goal.match_attribute !== 'visit_duration' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Field, {
      key: 0,
      uicontrol: "radio",
      name: "allow_multiple",
      "model-value": !!_ctx.goal.allow_multiple && _ctx.goal.allow_multiple !== '0' ? 1 : 0,
      "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => _ctx.goal.allow_multiple = $event),
      options: _ctx.allowMultipleOptions,
      introduction: _ctx.translate('Goals_AllowMultipleConversionsPerVisit'),
      "inline-help": _ctx.translate('Goals_HelpOneConversionPerVisit')
    }, null, 8, ["model-value", "options", "introduction", "inline-help"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_GoalRevenue')) + " " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Goals_Optional')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "number",
      name: "revenue",
      modelValue: _ctx.goal.revenue,
      "onUpdate:modelValue": _cache[11] || (_cache[11] = $event => _ctx.goal.revenue = $event),
      placeholder: _ctx.translate('Goals_DefaultRevenueLabel'),
      "inline-help": _ctx.translate('Goals_DefaultRevenueHelp')
    }, null, 8, ["modelValue", "placeholder", "inline-help"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
      uicontrol: "checkbox",
      name: "use_event_value",
      modelValue: _ctx.goal.event_value_as_revenue,
      "onUpdate:modelValue": _cache[12] || (_cache[12] = $event => _ctx.goal.event_value_as_revenue = $event),
      title: _ctx.translate('Goals_UseEventValueAsRevenue'),
      "inline-help": _ctx.useEventValueAsRevenueHelp
    }, null, 8, ["modelValue", "title", "inline-help"]), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.goal.match_attribute === 'event']])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_60, [_ctx.endEditTable ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_VueEntryContainer, {
      key: 0,
      html: _ctx.endEditTable
    }, null, 8, ["html"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), _hoisted_61, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SaveButton, {
      saving: _ctx.isLoading,
      onConfirm: _cache[13] || (_cache[13] = $event => _ctx.save()),
      value: _ctx.submitText
    }, null, 8, ["saving", "value"]), !_ctx.onlyShowAddNewGoal ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_62, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "entityCancel",
      onClick: _cache[14] || (_cache[14] = $event => _ctx.showListOfReports()),
      innerHTML: _ctx.$sanitize(_ctx.cancelText)
    }, null, 8, _hoisted_63), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEditGoal]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])), [[_directive_form]])]),
    _: 1
  }, 8, ["content-title"])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.showEditGoal]])], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.userCanEditGoals]]), _hoisted_64]);
}
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=template&id=bcd8742c

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.store.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class ManageGoals_store_ManageGoalsStore {
  constructor() {
    _defineProperty(this, "privateState", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["reactive"])({}));
    _defineProperty(this, "idGoal", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => this.privateState.idGoal));
  }
  setIdGoalShown(idGoal) {
    this.privateState.idGoal = idGoal;
  }
}
/* harmony default export */ var ManageGoals_store = (new ManageGoals_store_ManageGoalsStore());
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Goals/vue/src/RecommendGoals/RecommendGoalCard.vue?vue&type=script&setup=true&lang=ts


const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_1 = {
  class: "recommendGoals-cardMain"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_2 = {
  class: "recommendGoals-cardIcon"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_3 = {
  class: "recommendGoals-cardBody"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_4 = {
  class: "recommendGoals-cardTitle"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_5 = {
  class: "recommendGoals-cardName"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_6 = ["title"];
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_7 = {
  class: "recommendGoals-cardTrigger"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_8 = {
  class: "recommendGoals-pattern"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_9 = {
  class: "recommendGoals-cardActions"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_10 = {
  key: 0,
  class: "recommendGoals-accepted"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-ok"
}, null, -1);
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_12 = ["disabled"];
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_13 = ["title", "aria-label", "disabled"];
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-close"
}, null, -1);
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_15 = [RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_14];
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_16 = {
  key: 0,
  class: "recommendGoals-evidence"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-right"
}, null, -1);
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_18 = {
  class: "recommendGoals-evidenceBody"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_19 = {
  key: 0,
  class: "recommendGoals-cardReason"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_20 = {
  key: 1
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_21 = {
  key: 2,
  class: "recommendGoals-evidenceNote"
};
const RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_22 = {
  class: "recommendGoals-evidenceLabel"
};


/* harmony default export */ var RecommendGoalCardvue_type_script_setup_true_lang_ts = (/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  __name: 'RecommendGoalCard',
  props: {
    rec: null,
    accepted: {
      type: Boolean
    },
    creating: {
      type: Boolean
    },
    busy: {
      type: Boolean
    },
    primary: {
      type: Boolean
    }
  },
  emits: ["create", "dismiss"],
  setup(__props) {
    const props = __props;
    /* eslint-disable func-call-spacing, no-spaced-func */
    /* eslint-enable func-call-spacing, no-spaced-func */
    const needsSetup = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => (props.rec.matchAttribute || '').indexOf('event_') === 0);
    const hasEvidence = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => !!(props.rec.reason || props.rec.evidence && props.rec.evidence.length || needsSetup.value && props.rec.implementationNote));
    const goalIcon = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const matchAttribute = props.rec.matchAttribute || 'url';
      if (matchAttribute === 'file') {
        return 'icon-download';
      }
      if (matchAttribute === 'external_website') {
        return 'icon-outlink';
      }
      if (matchAttribute.indexOf('event_') === 0) {
        return 'icon-form';
      }
      if (matchAttribute.indexOf('visit_') === 0) {
        return 'icon-clock';
      }
      return 'icon-goal';
    });
    const displayPattern = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if ((props.rec.matchAttribute || '') === 'visit_duration') {
        return Object(external_CoreHome_["translate"])('Intl_NMinutes', props.rec.pattern);
      }
      return props.rec.pattern;
    });
    function matchAttributeLabel(matchAttribute) {
      const labels = {
        url: Object(external_CoreHome_["translate"])('Goals_VisitUrl'),
        title: Object(external_CoreHome_["translate"])('Goals_VisitPageTitle'),
        file: Object(external_CoreHome_["translate"])('Goals_Download'),
        external_website: Object(external_CoreHome_["translate"])('Goals_ClickOutlink'),
        event_action: Object(external_CoreHome_["translate"])('Goals_RecommendTriggerEventLabel', Object(external_CoreHome_["translate"])('Goals_SendEvent'), Object(external_CoreHome_["translate"])('Events_EventAction')),
        event_category: Object(external_CoreHome_["translate"])('Goals_RecommendTriggerEventLabel', Object(external_CoreHome_["translate"])('Goals_SendEvent'), Object(external_CoreHome_["translate"])('Events_EventCategory')),
        event_name: Object(external_CoreHome_["translate"])('Goals_RecommendTriggerEventLabel', Object(external_CoreHome_["translate"])('Goals_SendEvent'), Object(external_CoreHome_["translate"])('Events_EventName')),
        visit_duration: Object(external_CoreHome_["translate"])('Goals_VisitDurationMatchAttr'),
        visit_total_actions: Object(external_CoreHome_["translate"])('Goals_CategoryTextGeneral_Actions'),
        visit_total_pageviews: Object(external_CoreHome_["translate"])('General_ColumnPageviews')
      };
      return labels[matchAttribute] || matchAttribute;
    }
    const triggerDescription = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      const matchAttribute = props.rec.matchAttribute || 'url';
      const patternType = props.rec.patternType || 'contains';
      const matchLabel = matchAttributeLabel(matchAttribute);
      if (patternType === 'greater_than') {
        return Object(external_CoreHome_["translate"])('Goals_RecommendTriggerGreaterThan', matchLabel);
      }
      if (patternType === 'exact') {
        return Object(external_CoreHome_["translate"])('Goals_RecommendTriggerExact', matchLabel);
      }
      if (patternType === 'regex') {
        return Object(external_CoreHome_["translate"])('Goals_RecommendTriggerMatchesExpression', matchLabel);
      }
      return Object(external_CoreHome_["translate"])('Goals_RecommendTriggerContains', matchLabel);
    });
    return (_ctx, _cache) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["recommendGoals-card", {
          'recommendGoals-card--added': __props.accepted
        }])
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(goalIcon))
      }, null, 2)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.rec.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(needsSetup) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 0,
        class: "recommendGoals-chip recommendGoals-chip--setup",
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendNeedsSetupHelp')
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendNeedsSetup')), 9, RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(triggerDescription)) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("code", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(displayPattern)), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_9, [__props.accepted ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_10, [RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('General_Added')), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 1
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn", {
          'btn-outline': !__props.primary
        }]),
        onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('create')),
        disabled: __props.busy
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.creating ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendCreating') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendCreate')), 11, RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "recommendGoals-dismissBtn",
        title: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendDismissSuggestion'),
        "aria-label": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendDismissSuggestion'),
        onClick: _cache[1] || (_cache[1] = $event => _ctx.$emit('dismiss')),
        disabled: __props.busy
      }, RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_15, 8, RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_13)], 64))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(hasEvidence) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("details", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("summary", null, [RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendWhySuggested')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_18, [__props.rec.reason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.rec.reason), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), __props.rec.evidence && __props.rec.evidence.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_20, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(__props.rec.evidence, (item, index) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
          key: index
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item), 1);
      }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(needsSetup) && __props.rec.implementationNote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RecommendGoalCardvue_type_script_setup_true_lang_ts_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendManualHowTo')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(__props.rec.implementationNote), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
    };
  }
}));
// CONCATENATED MODULE: ./plugins/Goals/vue/src/RecommendGoals/RecommendGoalCard.vue?vue&type=script&setup=true&lang=ts
 
// CONCATENATED MODULE: ./plugins/Goals/vue/src/RecommendGoals/RecommendGoalCard.vue



/* harmony default export */ var RecommendGoalCard = (RecommendGoalCardvue_type_script_setup_true_lang_ts);
// CONCATENATED MODULE: ./plugins/Goals/vue/src/RecommendGoals/useScanProgress.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// fake, time-based progress: the backend reports no incremental scan status. The
// crawl phase eases to 60%, the (AI) ranking phase approaches but never reaches 100%.
const SCAN_CRAWL_PHASE_MS = 15000;
const SCAN_EXPECTED_TOTAL_MS = 30000;
const SCAN_CRAWL_PHASE_PROGRESS = 60;
const SCAN_RANKING_PHASE_PROGRESS = 93;
const SCAN_PROGRESS_TICK_MS = 250;
function useScanProgress() {
  const progress = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(0);
  const isInRankingPhase = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => progress.value >= SCAN_CRAWL_PHASE_PROGRESS);
  let startedAt = null;
  let timer = null;
  function computeProgress() {
    if (startedAt === null) {
      return 0;
    }
    const elapsed = Date.now() - startedAt;
    if (elapsed <= SCAN_CRAWL_PHASE_MS) {
      return elapsed / SCAN_CRAWL_PHASE_MS * SCAN_CRAWL_PHASE_PROGRESS;
    }
    const rankingElapsed = elapsed - SCAN_CRAWL_PHASE_MS;
    const rankingDuration = SCAN_EXPECTED_TOTAL_MS - SCAN_CRAWL_PHASE_MS;
    if (rankingElapsed <= rankingDuration) {
      return SCAN_CRAWL_PHASE_PROGRESS + rankingElapsed / rankingDuration * (SCAN_RANKING_PHASE_PROGRESS - SCAN_CRAWL_PHASE_PROGRESS);
    }
    // past the expected duration: creep slowly towards (but never reach) 99%
    const overtimeSeconds = (rankingElapsed - rankingDuration) / 1000;
    return Math.min(99, SCAN_RANKING_PHASE_PROGRESS + overtimeSeconds * 0.1);
  }
  function start() {
    startedAt = Date.now();
    progress.value = 0;
    timer = window.setInterval(() => {
      progress.value = computeProgress();
    }, SCAN_PROGRESS_TICK_MS);
  }
  function stop() {
    if (timer !== null) {
      window.clearInterval(timer);
      timer = null;
    }
    startedAt = null;
    progress.value = 0;
  }
  Object(external_commonjs_vue_commonjs2_vue_root_Vue_["onBeforeUnmount"])(stop);
  return {
    progress,
    isInRankingPhase,
    start,
    stop
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Goals/vue/src/RecommendGoals/RecommendGoals.vue?vue&type=script&setup=true&lang=ts


const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_1 = {
  class: "recommendGoals-intro"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_2 = {
  key: 0
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_3 = {
  key: 0
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_4 = {
  class: "recommendGoals-list"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_5 = {
  class: "recommendGoals-actions"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_6 = ["disabled"];
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_7 = ["disabled"];
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_8 = {
  key: 1,
  class: "recommendGoals-empty"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_9 = {
  key: 2,
  class: "recommendGoals-manual"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-right"
}, null, -1);
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_11 = {
  class: "recommendGoals-intro"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_12 = {
  class: "recommendGoals-manualList"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_13 = {
  class: "recommendGoals-manualBody"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_14 = {
  class: "recommendGoals-manualName"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_15 = {
  class: "recommendGoals-manualHowTo"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_16 = ["onClick", "disabled"];
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_17 = {
  key: 4,
  class: "recommendGoals-scanProgress"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_18 = {
  class: "recommendGoals-scanHint"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_19 = {
  key: 5,
  class: "recommendGoals-footer"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_20 = {
  key: 0,
  class: "recommendGoals-meta",
  "aria-live": "polite"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_21 = {
  key: 0
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_22 = {
  class: "recommendGoals-toolbar"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_23 = ["disabled"];
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-search"
}, null, -1);
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_25 = {
  class: "switch recommendGoals-aiSwitch"
};
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_26 = ["disabled"];
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "lever"
}, null, -1);
const RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_28 = ["aria-expanded"];




/* harmony default export */ var RecommendGoalsvue_type_script_setup_true_lang_ts = (/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  __name: 'RecommendGoals',
  props: {
    goals: {
      default: () => ({})
    },
    userCanEditGoals: {
      type: Boolean
    }
  },
  emits: ["created", "prefill"],
  setup(__props, {
    emit
  }) {
    const props = __props;
    /* eslint-disable func-call-spacing, no-spaced-func */
    /* eslint-enable func-call-spacing, no-spaced-func */
    const useAi = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const lastRunUsedAi = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const isLoading = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const isLoadingSaved = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const creatingId = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const isCreatingAll = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const isDismissing = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const dismissingId = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const hasRun = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const showPrivacyNote = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(false);
    const aiError = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const createError = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const recommendationMode = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const recommendations = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([]);
    const manualGoals = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([]);
    const generatedAt = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const remainingAiScans = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(null);
    const providerName = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])(Object(external_CoreHome_["translate"])('Goals_RecommendAiProviderFallback'));
    const createdRecommendationKeys = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["ref"])([]);
    const {
      progress: scanProgress,
      isInRankingPhase,
      start: startScanProgress,
      stop: stopScanProgress
    } = useScanProgress();
    const idSite = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => external_CoreHome_["Matomo"].idSite);
    const shouldShowRecommendations = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => props.userCanEditGoals);
    const isBusy = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => isLoading.value || isCreatingAll.value || isDismissing.value || creatingId.value !== null || dismissingId.value !== null);
    function recKey(rec) {
      return rec.id || rec.name;
    }
    function goalKey(matchAttribute, pattern) {
      let normalizedPattern = `${pattern || ''}`.trim().toLowerCase().replace(/\/+$/, '');
      if (matchAttribute === 'url') {
        normalizedPattern = normalizedPattern.replace(/^https?:\/\/[^/]+/i, '');
      }
      return `${matchAttribute}:${normalizedPattern.replace(/^\/|\/$/g, '')}`;
    }
    const existingGoalKeys = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object.values(props.goals || {}).filter(goal => goal.pattern).map(goal => goalKey(`${goal.match_attribute || 'url'}`, `${goal.pattern || ''}`)));
    function isAccepted(rec) {
      if (createdRecommendationKeys.value.indexOf(recKey(rec)) !== -1) {
        return true;
      }
      return existingGoalKeys.value.indexOf(goalKey(rec.matchAttribute || 'url', rec.pattern)) !== -1;
    }
    const pendingRecommendations = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => recommendations.value.filter(rec => !isAccepted(rec)));
    const scanButtonLabel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => hasRun.value ? Object(external_CoreHome_["translate"])('Goals_RecommendRescan') : Object(external_CoreHome_["translate"])('Goals_RecommendGoals'));
    const lastScannedAgo = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (!generatedAt.value) {
        return '';
      }
      const date = new Date(generatedAt.value * 1000);
      if (typeof Intl === 'undefined' || !Intl.RelativeTimeFormat) {
        return date.toLocaleString();
      }
      const formatter = new Intl.RelativeTimeFormat(external_CoreHome_["Matomo"].language, {
        numeric: 'auto'
      });
      const elapsedSeconds = Math.max(0, Math.round((Date.now() - date.getTime()) / 1000));
      if (elapsedSeconds < 60) {
        return formatter.format(-elapsedSeconds, 'second');
      }
      if (elapsedSeconds < 3600) {
        return formatter.format(-Math.round(elapsedSeconds / 60), 'minute');
      }
      if (elapsedSeconds < 86400) {
        return formatter.format(-Math.round(elapsedSeconds / 3600), 'hour');
      }
      return formatter.format(-Math.round(elapsedSeconds / 86400), 'day');
    });
    const scanProgressLabel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => lastRunUsedAi.value && isInRankingPhase.value ? Object(external_CoreHome_["translate"])('Goals_RecommendProgressAiRanking') : Object(external_CoreHome_["translate"])('Goals_RecommendProgressCrawling'));
    const fallbackModeMessage = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => {
      if (!hasRun.value || isLoading.value || !lastRunUsedAi.value) {
        return '';
      }
      if (recommendationMode.value === 'deterministic') {
        return Object(external_CoreHome_["translate"])('Goals_RecommendationFallbackUsed');
      }
      return '';
    });
    const privacyNote = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["computed"])(() => Object(external_CoreHome_["translate"])('Goals_RecommendAiToggleHelp', providerName.value));
    function loadSavedRecommendations() {
      isLoadingSaved.value = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.getSavedRecommendedGoals',
        idSite: idSite.value
      }, {
        createErrorNotification: false
      }).then(response => {
        if (!response || !response.generatedAt) {
          return;
        }
        remainingAiScans.value = typeof response.remainingAiScans === 'number' ? response.remainingAiScans : null;
        if (response.providerName) {
          providerName.value = response.providerName;
        }
        recommendations.value = response.goals || [];
        manualGoals.value = response.manualGoals || [];
        recommendationMode.value = response.mode || null;
        generatedAt.value = response.generatedAt;
        useAi.value = !!response.useAi;
        lastRunUsedAi.value = !!response.useAi;
        hasRun.value = true;
      }).catch(() => {
        // saved recommendations are optional; the user can still run a fresh scan
      }).finally(() => {
        isLoadingSaved.value = false;
      });
    }
    function recommend() {
      isLoading.value = true;
      aiError.value = null;
      createError.value = null;
      recommendationMode.value = null;
      const requestedAi = useAi.value;
      lastRunUsedAi.value = requestedAi;
      startScanProgress();
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.getRecommendedGoals',
        idSite: idSite.value,
        useAi: requestedAi ? 1 : 0
      }, {
        createErrorNotification: false
      }).then(response => {
        recommendations.value = response && response.goals || [];
        manualGoals.value = response && response.manualGoals || [];
        aiError.value = response && response.aiError || null;
        recommendationMode.value = response && response.mode || null;
        generatedAt.value = response && response.generatedAt || null;
        remainingAiScans.value = response && typeof response.remainingAiScans === 'number' ? response.remainingAiScans : null;
        if (response && response.providerName) {
          providerName.value = response.providerName;
        }
        hasRun.value = true;
      }).catch(() => {
        recommendations.value = [];
        manualGoals.value = [];
        aiError.value = Object(external_CoreHome_["translate"])('Goals_RecommendError');
        recommendationMode.value = null;
        hasRun.value = true;
      }).finally(() => {
        stopScanProgress();
        isLoading.value = false;
      });
    }
    function addGoalRequest(rec) {
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.addGoal',
        idSite: idSite.value,
        name: rec.name,
        matchAttribute: rec.matchAttribute || 'url',
        pattern: rec.pattern,
        patternType: rec.patternType || 'contains',
        caseSensitive: rec.caseSensitive ? 1 : 0,
        allowMultipleConversionsPerVisit: rec.allowMultipleConversionsPerVisit ? 1 : 0,
        revenue: rec.revenue || 0,
        description: rec.description || rec.reason || '',
        useEventValueAsRevenue: rec.useEventValueAsRevenue ? 1 : 0,
        createdFromRecommendedGoal: 1
      }, {
        createErrorNotification: false
      });
    }
    function createOne(rec) {
      creatingId.value = recKey(rec);
      createError.value = null;
      addGoalRequest(rec).then(response => {
        if (response && response.value) {
          createdRecommendationKeys.value.push(recKey(rec));
        }
        emit('created', response && response.value ? [response.value] : []);
      }).catch(() => {
        createError.value = Object(external_CoreHome_["translate"])('Goals_RecommendCreateError');
      }).finally(() => {
        creatingId.value = null;
      });
    }
    function createAll() {
      isCreatingAll.value = true;
      createError.value = null;
      const createdIds = [];
      pendingRecommendations.value.reduce((promise, rec) => promise.then(() => addGoalRequest(rec)).then(response => {
        if (response && response.value) {
          createdRecommendationKeys.value.push(recKey(rec));
          createdIds.push(response.value);
        }
      }), Promise.resolve()).catch(() => {
        createError.value = Object(external_CoreHome_["translate"])('Goals_RecommendCreateError');
      }).finally(() => {
        isCreatingAll.value = false;
        if (createdIds.length) {
          emit('created', createdIds);
        }
      });
    }
    function dismiss() {
      isDismissing.value = true;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.dismissRecommendedGoals',
        idSite: idSite.value
      }).then(() => {
        hasRun.value = false;
        recommendations.value = [];
        manualGoals.value = [];
        aiError.value = null;
        createError.value = null;
        recommendationMode.value = null;
        lastRunUsedAi.value = false;
        generatedAt.value = null;
      }).finally(() => {
        isDismissing.value = false;
      });
    }
    function dismissOne(rec) {
      dismissingId.value = recKey(rec);
      isDismissing.value = true;
      createError.value = null;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.dismissRecommendedGoal',
        idSite: idSite.value,
        recommendationId: rec.id || ''
      }, {
        createErrorNotification: false
      }).then(response => {
        if (!response || !response.success) {
          createError.value = Object(external_CoreHome_["translate"])('Goals_RecommendDismissError');
          return;
        }
        recommendations.value = recommendations.value.filter(other => other !== rec);
        if (!recommendations.value.length && !manualGoals.value.length) {
          hasRun.value = false;
          recommendationMode.value = null;
          generatedAt.value = null;
        }
      }).catch(() => {
        createError.value = Object(external_CoreHome_["translate"])('Goals_RecommendDismissError');
      }).finally(() => {
        dismissingId.value = null;
        isDismissing.value = false;
      });
    }
    if (shouldShowRecommendations.value) {
      loadSavedRecommendations();
    }
    return (_ctx, _cache) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(shouldShowRecommendations) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["ContentBlock"]), {
        key: 0,
        "content-title": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendedGoals'),
        feature: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendedGoals'),
        class: "recommendGoals"
      }, {
        default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendedGoalsIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["ActivityIndicator"]), {
          loading: isLoadingSaved.value
        }, null, 8, ["loading"]), hasRun.value && !isLoading.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_2, [recommendations.value.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_4, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(recommendations.value, rec => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(RecommendGoalCard, {
            key: recKey(rec),
            rec: rec,
            accepted: isAccepted(rec),
            creating: creatingId.value === recKey(rec),
            busy: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy),
            primary: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(pendingRecommendations).length === 1,
            onCreate: $event => createOne(rec),
            onDismiss: $event => dismissOne(rec)
          }, null, 8, ["rec", "accepted", "creating", "busy", "primary", "onCreate", "onDismiss"]);
        }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          type: "button",
          class: "btn-flat",
          onClick: _cache[0] || (_cache[0] = $event => dismiss()),
          disabled: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendDismiss')), 9, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_6), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(pendingRecommendations).length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
          key: 0,
          type: "button",
          class: "btn",
          onClick: _cache[1] || (_cache[1] = $event => createAll()),
          disabled: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(isCreatingAll.value ? Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendCreating') : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendCreateAll')), 9, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendNoneFound')), 1)), manualGoals.value.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("details", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("summary", null, [RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendManualTitle')) + " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(manualGoals.value.length) + ") ", 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendManualIntro')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_12, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(manualGoals.value, (rec, index) => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
            key: 'manual-' + index
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rec.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(rec.howTo), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
            type: "button",
            class: "btn-flat",
            onClick: $event => _ctx.$emit('prefill', rec),
            disabled: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy)
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendManualStartInForm')), 9, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_16)]);
        }), 128))])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), aiError.value && !isLoading.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Alert"]), {
          key: 1,
          severity: "warning"
        }, {
          default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(aiError.value), 1)]),
          _: 1
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(fallbackModeMessage) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Alert"]), {
          key: 2,
          severity: "warning"
        }, {
          default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(fallbackModeMessage)), 1)]),
          _: 1
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), createError.value && !isLoading.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Alert"]), {
          key: 3,
          severity: "danger"
        }, {
          default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(createError.value), 1)]),
          _: 1
        })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), isLoading.value ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["Progressbar"]), {
          progress: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(scanProgress),
          label: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(scanProgressLabel)
        }, null, 8, ["progress", "label"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendProgressHint')), 1)])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(lastScannedAgo) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendLastScanned', Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(lastScannedAgo))) + " ", 1), useAi.value && remainingAiScans.value !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_21, " (" + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendScansRemainingToday', `${remainingAiScans.value}`)) + ") ", 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          type: "button",
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["btn recommendGoals-run", {
            'btn-outline': Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(pendingRecommendations).length > 0
          }]),
          onClick: _cache[2] || (_cache[2] = $event => recommend()),
          disabled: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy)
        }, [RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(scanButtonLabel)), 1)], 10, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_25, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          type: "checkbox",
          "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => useAi.value = $event),
          disabled: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(isBusy)
        }, null, 8, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_26), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelCheckbox"], useAi.value]]), RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_27, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendUseAi')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          type: "button",
          class: "recommendGoals-privacyLink",
          "aria-expanded": showPrivacyNote.value ? 'true' : 'false',
          "aria-controls": "recommendGoalsPrivacyNote",
          onClick: _cache[4] || (_cache[4] = $event => showPrivacyNote.value = !showPrivacyNote.value)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(external_CoreHome_["translate"])('Goals_RecommendWhatDataIsShared')), 9, RecommendGoalsvue_type_script_setup_true_lang_ts_hoisted_28)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
          class: "recommendGoals-privacyNote",
          id: "recommendGoalsPrivacyNote"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["unref"])(privacyNote)), 513), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], showPrivacyNote.value]])]))]),
        _: 1
      }, 8, ["content-title", "feature"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
    };
  }
}));
// CONCATENATED MODULE: ./plugins/Goals/vue/src/RecommendGoals/RecommendGoals.vue?vue&type=script&setup=true&lang=ts
 
// CONCATENATED MODULE: ./plugins/Goals/vue/src/RecommendGoals/RecommendGoals.vue



/* harmony default export */ var RecommendGoals = (RecommendGoalsvue_type_script_setup_true_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=script&lang=ts





const notificationKey = 'Goals.ManageGoals.Notification';
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
    currencySymbol: {
      type: String,
      default: ''
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
  data() {
    return {
      showEditGoal: false,
      showGoalList: true,
      currentGoals: this.goals || {},
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
    Alert: external_CoreHome_["Alert"],
    VueEntryContainer: external_CoreHome_["VueEntryContainer"],
    RecommendGoals: RecommendGoals
  },
  directives: {
    ContentTable: external_CoreHome_["ContentTable"],
    Form: external_CorePluginsAdmin_["Form"]
  },
  created() {
    ManageGoals_store.setIdGoalShown(this.showGoal);
  },
  unmounted() {
    ManageGoals_store.setIdGoalShown(undefined);
  },
  mounted() {
    if (this.showAddGoal) {
      this.createGoal();
    } else if (this.showGoal) {
      this.editGoal(this.showGoal);
    } else {
      this.showListOfReports();
    }
    const storedNotifications = this.getStoredNotification();
    if (storedNotifications) {
      this.showNotificationMessage(storedNotifications.goal, storedNotifications.create);
    }
  },
  methods: {
    scrollToTop() {
      setTimeout(() => {
        external_CoreHome_["Matomo"].helper.lazyScrollTo('.pageWrap', 200);
      });
    },
    initGoalForm(goalMethodAPI, submitText, goalName, description, matchAttribute, pattern, patternType, caseSensitive, revenue, allowMultiple, useEventValueAsRevenue, goalId) {
      external_CoreHome_["Matomo"].postEvent('Goals.beforeInitGoalForm', goalMethodAPI, goalId, goalName);
      this.apiMethod = goalMethodAPI;
      this.goal = {};
      this.goal.name = goalName;
      this.goal.description = description;
      let actualMatchAttribute = matchAttribute;
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
    showListOfReports() {
      external_CoreHome_["Matomo"].postEvent('Goals.cancelForm');
      this.showGoalList = true;
      this.showEditGoal = false;
      this.scrollToTop();
    },
    showAddEditForm() {
      this.showGoalList = false;
      this.showEditGoal = true;
    },
    createGoal() {
      const parameters = {
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
    editGoal(goalId) {
      this.showAddEditForm();
      const goal = this.currentGoals[`${goalId}`];
      this.initGoalForm('Goals.updateGoal', Object(external_CoreHome_["translate"])('Goals_UpdateGoal'), goal.name, goal.description, goal.match_attribute, goal.pattern, goal.pattern_type, !!goal.case_sensitive && goal.case_sensitive !== '0', parseInt(`${goal.revenue}`, 10), !!goal.allow_multiple && goal.allow_multiple !== '0', !!goal.event_value_as_revenue && goal.event_value_as_revenue !== '0', goalId);
      this.scrollToTop();
    },
    deleteGoal(goalId) {
      this.goalToDelete = this.currentGoals[`${goalId}`];
      external_CoreHome_["Matomo"].helper.modalConfirm(this.$refs.confirm, {
        yes: () => {
          this.isLoading = true;
          external_CoreHome_["AjaxHelper"].fetch({
            idGoal: goalId,
            method: 'Goals.deleteGoal'
          }).then(() => {
            window.location.reload();
          }).finally(() => {
            this.isLoading = false;
          });
        }
      });
    },
    save() {
      const parameters = {};
      // TODO: test removal of encoding, should be handled by ajax request
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
          parameters.useEventValueAsRevenue = ambiguousBoolToInt(this.goal.event_value_as_revenue);
        }
        parameters.patternType = this.goal.pattern_type;
        parameters.pattern = this.goal.pattern;
        parameters.caseSensitive = ambiguousBoolToInt(this.goal.case_sensitive);
      }
      parameters.revenue = this.goal.revenue || 0;
      parameters.allowMultipleConversionsPerVisit = ambiguousBoolToInt(this.goal.allow_multiple);
      parameters.idGoal = this.goal.idgoal;
      parameters.method = this.apiMethod;
      const isCreate = parameters.method === 'Goals.addGoal';
      const isUpdate = parameters.method === 'Goals.updateGoal';
      const options = {};
      if (isUpdate) {
        external_CoreHome_["Matomo"].postEvent('Goals.beforeUpdateGoal', {
          parameters,
          options
        });
      } else if (isCreate) {
        external_CoreHome_["Matomo"].postEvent('Goals.beforeAddGoal', {
          parameters,
          options
        });
      }
      if (parameters !== null && parameters !== void 0 && parameters.cancelRequest) {
        return;
      }
      this.isLoading = true;
      external_CoreHome_["AjaxHelper"].fetch(parameters, options).then(async response => {
        let idToUse = parameters.idGoal;
        if (isCreate && response.value) {
          idToUse = response.value;
        }
        this.storeNotification(idToUse, isCreate);
        this.scrollToTop();
        const subcategory = external_CoreHome_["MatomoUrl"].parsed.value.subcategory;
        if (subcategory === 'Goals_AddNewGoal' && external_CoreHome_["Matomo"].helper.isReportingPage()) {
          // when adding a goal for the first time we need to load manage goals page afterwards
          await external_CoreHome_["ReportingMenuStore"].reloadMenuItems();
          external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
            subcategory: 'Goals_ManageGoals'
          }));
          this.isLoading = false;
        } else {
          window.location.reload();
        }
      }).catch(() => {
        this.scrollToTop();
        this.isLoading = false;
      });
    },
    storeNotification(goalId, isCreate) {
      try {
        sessionStorage.setItem(notificationKey, JSON.stringify({
          goal: goalId,
          create: isCreate
        }));
      } catch (e) {
        // Do nothing
      }
    },
    getStoredNotification() {
      const pendingNotification = sessionStorage.getItem(notificationKey);
      if (pendingNotification) {
        sessionStorage.removeItem(notificationKey);
        try {
          let {
            goal,
            create
          } = JSON.parse(pendingNotification);
          if (goal) {
            goal = parseInt(goal, 10); // we make sure this is an int
          }
          create = !!create; // we make sure this is a boolean
          return {
            goal,
            create
          };
        } catch (e) {
          return null;
        }
      }
      return null;
    },
    getGoalReportUrl(goalId) {
      const link = external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'CoreHome',
        action: 'index'
      }));
      const hash = external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        category: 'Goals_Goals',
        subcategory: goalId
      }));
      return `?${link}#?${hash}`;
    },
    refreshGoals() {
      return external_CoreHome_["AjaxHelper"].fetch({
        method: 'Goals.getGoals',
        filter_limit: '-1',
        orderByName: 1
      }).then(goals => {
        const refreshed = {};
        Object.values(goals || {}).forEach(goal => {
          refreshed[`${goal.idgoal}`] = Object.assign(Object.assign({}, goal), {}, {
            revenue_pretty: external_CoreHome_["NumberFormatter"].formatCurrency(goal.revenue, this.currencySymbol)
          });
        });
        this.currentGoals = refreshed;
      });
    },
    prefillManualGoal(manual) {
      this.createGoal();
      if (!this.showEditGoal) {
        return; // adding goals is currently not allowed (Goals.initAddGoal event)
      }
      this.goal.name = manual.name;
      if (manual.category === 'event') {
        this.goal.match_attribute = 'event';
        this.eventType = 'event_name';
      } else if (manual.category === 'outlink') {
        this.goal.match_attribute = 'external_website';
      } else if (manual.category === 'visit_duration') {
        // fully specified: the user only needs to review and save
        this.goal.match_attribute = 'visit_duration';
        this.goal.pattern_type = 'greater_than';
        this.goal.pattern = '3';
      } else if (manual.category === 'file') {
        this.goal.match_attribute = 'file';
      }
    },
    onRecommendedGoalsCreated(idGoals) {
      this.refreshGoals();
      if (idGoals.length === 1) {
        this.showNotificationMessage(idGoals[0], true);
      } else if (idGoals.length > 1) {
        external_CoreHome_["NotificationsStore"].show({
          id: 'ManageGoals.create',
          message: Object(external_CoreHome_["translate"])('Goals_RecommendGoalsCreated', `${idGoals.length}`),
          context: 'success',
          type: 'toast'
        });
      }
    },
    showNotificationMessage(goalId, isCreate) {
      let successMessage = Object(external_CoreHome_["translate"])(isCreate ? 'Goals_GoalCreated' : 'Goals_GoalUpdated');
      const reportLink = `<a href="${this.getGoalReportUrl(goalId)}">[${Object(external_CoreHome_["translate"])('Goals_ViewGoalReport')}]</a>`;
      successMessage = `${successMessage} ${reportLink}`;
      external_CoreHome_["NotificationsStore"].show({
        id: 'ManageGoals.create',
        message: successMessage,
        context: 'success',
        type: 'toast'
      });
    },
    changedTriggerType() {
      if (!this.isManuallyTriggered && !this.goal.pattern_type) {
        this.goal.pattern_type = 'contains';
      }
    },
    initPatternType() {
      if (this.isMatchAttributeNumeric) {
        this.goal.pattern_type = 'greater_than';
      } else {
        this.goal.pattern_type = 'contains';
      }
    },
    lcfirst(s) {
      return `${s.slice(0, 1).toLowerCase()}${s.slice(1)}`;
    },
    ucfirst(s) {
      return `${s.slice(0, 1).toUpperCase()}${s.slice(1)}`;
    },
    goalNameChanged() {
      external_CoreHome_["Matomo"].postEvent('Goals.goalNameChanged', this.goal.name);
    }
  },
  computed: {
    learnMoreAboutGoalTracking() {
      return Object(external_CoreHome_["translate"])('Goals_LearnMoreAboutGoalTrackingDocumentation', Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/tracking-goals-web-analytics/'), '</a>');
    },
    youCanEnableEcommerceReports() {
      const link = external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), {}, {
        module: 'SitesManager',
        action: 'index'
      }));
      /* eslint-disable prefer-template */
      const ecommerceReportsText = Object(external_CoreHome_["externalLink"])('https://matomo.org/docs/ecommerce-analytics/') + Object(external_CoreHome_["translate"])('Goals_EcommerceReports') + '</a>';
      const websiteManageText = `<a href='${link}'>${Object(external_CoreHome_["translate"])('SitesManager_WebsitesManagement')}</a>`;
      return Object(external_CoreHome_["translate"])('Goals_YouCanEnableEcommerceReports', ecommerceReportsText, websiteManageText);
    },
    siteName() {
      return external_CoreHome_["Matomo"].helper.htmlDecode(external_CoreHome_["Matomo"].siteName);
    },
    whereVisitedPageManuallyCallsJsTrackerText() {
      return Object(external_CoreHome_["translate"])('Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore', Object(external_CoreHome_["externalLink"])('https://developer.matomo.org/guides/tracking-javascript-guide#manually-trigger-goal-conversions'), '</a>');
    },
    caseSensitiveTitle() {
      return `${Object(external_CoreHome_["translate"])('Goals_CaseSensitive')} ${Object(external_CoreHome_["translate"])('Goals_Optional')}`;
    },
    useEventValueAsRevenueHelp() {
      return `${Object(external_CoreHome_["translate"])('Goals_EventValueAsRevenueHelp')} <br/><br/> ${Object(external_CoreHome_["translate"])('Goals_EventValueAsRevenueHelp2')}`;
    },
    cancelText() {
      return Object(external_CoreHome_["translate"])('General_OrCancel', '<a class=\'entityCancelLink\'>', '</a>');
    },
    isMatchAttributeNumeric() {
      return ['visit_duration'].indexOf(this.goal.match_attribute) > -1;
    },
    patternFieldLabel() {
      return this.goal.match_attribute === 'visit_duration' ? Object(external_CoreHome_["translate"])('Goals_TimeInMinutes') : Object(external_CoreHome_["translate"])('Goals_Pattern');
    },
    goalMatchAttributeTranslations() {
      return {
        manually: Object(external_CoreHome_["translate"])('Goals_ManuallyTriggeredUsingJavascriptFunction'),
        file: Object(external_CoreHome_["translate"])('Goals_Download'),
        url: Object(external_CoreHome_["translate"])('Goals_VisitUrl'),
        title: Object(external_CoreHome_["translate"])('Goals_VisitPageTitle'),
        external_website: Object(external_CoreHome_["translate"])('Goals_ClickOutlink'),
        event_action: `${Object(external_CoreHome_["translate"])('Goals_SendEvent')} (${Object(external_CoreHome_["translate"])('Events_EventAction')})`,
        event_category: `${Object(external_CoreHome_["translate"])('Goals_SendEvent')} (${Object(external_CoreHome_["translate"])('Events_EventCategory')})`,
        event_name: `${Object(external_CoreHome_["translate"])('Goals_SendEvent')} (${Object(external_CoreHome_["translate"])('Events_EventName')})`,
        visit_duration: `${this.ucfirst(Object(external_CoreHome_["translate"])('Goals_VisitDuration'))}`
      };
    },
    beforeGoalListActionsBodyComponent() {
      if (!this.beforeGoalListActionsBody) {
        return {};
      }
      const componentsByIdGoal = {};
      Object.values(this.currentGoals).forEach(g => {
        const template = this.beforeGoalListActionsBody[g.idgoal];
        if (!template) {
          return;
        }
        componentsByIdGoal[g.idgoal] = {
          template
        };
      });
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(componentsByIdGoal);
    },
    beforeGoalListActionsHeadComponent() {
      if (!this.beforeGoalListActionsHead) {
        return null;
      }
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])({
        template: this.beforeGoalListActionsHead
      });
    },
    isManuallyTriggered() {
      return this.triggerType === 'manually';
    },
    matchesExpressionExternal() {
      const url = "'http://www.amazon.com\\/(.*)\\/yourAffiliateId'";
      return Object(external_CoreHome_["translate"])('Goals_MatchesExpression', url);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Goals/vue/src/ManageGoals/ManageGoals.vue



ManageGoalsvue_type_script_lang_ts.render = render

/* harmony default export */ var ManageGoals = (ManageGoalsvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Goals/vue/src/index.ts
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
//# sourceMappingURL=Goals.umd.js.map