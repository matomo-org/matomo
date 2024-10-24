(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Feedback"] = factory(require("CoreHome"), require("vue"));
	else
		root["Feedback"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Feedback/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "ReviewLinks", function() { return /* reexport */ ReviewLinks; });
__webpack_require__.d(__webpack_exports__, "RateFeature", function() { return /* reexport */ RateFeature; });
__webpack_require__.d(__webpack_exports__, "FeedbackQuestion", function() { return /* reexport */ FeedbackQuestion; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=7efbd721

const _hoisted_1 = {
  class: "requestReview"
};
const _hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<br><br><div class=\"review-links\"><div class=\"review-link\"><a href=\"https://www.softwarereviews.com/6g24l\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/softwarereviews.svg\"></div><div class=\"link\">Software Reviews</div></a></div><div class=\"review-link\"><a href=\"https://www.capterra.com/p/182627/Matomo-Analytics/\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/capterra.svg\"></div><div class=\"link\">Capterra</div></a></div><div class=\"review-link\"><a href=\"https://www.g2crowd.com/products/matomo-formerly-piwik/details\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/g2crowd.svg\"></div><div class=\"link\">G2 Crowd</div></a></div><div class=\"review-link\"><a href=\"https://www.producthunt.com/posts/matomo-2\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/producthunt.svg\"></div><div class=\"link\">Product Hunt</div></a></div><div class=\"review-link\"><a href=\"https://www.saasworthy.com/product/matomo\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/saasworthy.png\"></div><div class=\"link\">SaaSworthy</div></a></div><div class=\"review-link\"><a href=\"https://www.trustradius.com/products/matomo/reviews\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/trustradius.svg\"></div><div class=\"link\">TrustRadius</div></a></div></div>", 3);
function render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_PleaseLeaveExternalReviewForMatomo')), 1), _hoisted_2]);
}
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=7efbd721

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts

/* harmony default export */ var ReviewLinksvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue



ReviewLinksvue_type_script_lang_ts.render = render

/* harmony default export */ var ReviewLinks = (ReviewLinksvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=36ef4b69

const RateFeaturevue_type_template_id_36ef4b69_hoisted_1 = ["title"];
const RateFeaturevue_type_template_id_36ef4b69_hoisted_2 = {
  class: "ui-confirm ratefeatureDialog"
};
const _hoisted_3 = {
  key: 0
};
const _hoisted_4 = {
  key: 0
};
const _hoisted_5 = {
  key: 1
};
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = {
  class: "row"
};
const _hoisted_8 = {
  style: {
    "text-align": "left",
    "margin-top": "16px"
  }
};
const _hoisted_9 = {
  for: "useful",
  class: "ratelabel"
};
const _hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_11 = {
  for: "easy",
  class: "ratelabel"
};
const _hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_13 = {
  for: "configurable",
  class: "ratelabel"
};
const _hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_15 = {
  for: "likeother",
  class: "ratelabel"
};
const _hoisted_16 = {
  key: 1
};
const _hoisted_17 = {
  key: 0
};
const _hoisted_18 = {
  key: 1
};
const _hoisted_19 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_20 = {
  class: "row"
};
const _hoisted_21 = {
  style: {
    "text-align": "left"
  }
};
const _hoisted_22 = {
  for: "missingfeatures",
  class: "ratelabel"
};
const _hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_24 = {
  for: "makeeasier",
  class: "ratelabel"
};
const _hoisted_25 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_26 = {
  for: "speedup",
  class: "ratelabel"
};
const _hoisted_27 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_28 = {
  for: "fixbugs",
  class: "ratelabel"
};
const _hoisted_29 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_30 = {
  for: "dislikeother",
  class: "ratelabel"
};
const _hoisted_31 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_32 = {
  key: 2,
  class: "messageContainer",
  style: {
    "text-align": "left"
  }
};
const _hoisted_33 = {
  key: 0
};
const _hoisted_34 = {
  key: 1
};
const _hoisted_35 = {
  key: 2
};
const _hoisted_36 = {
  key: 3
};
const _hoisted_37 = {
  key: 4
};
const _hoisted_38 = {
  key: 5
};
const _hoisted_39 = {
  key: 6
};
const _hoisted_40 = {
  key: 7
};
const _hoisted_41 = {
  key: 8
};
const _hoisted_42 = {
  key: 9,
  class: "error-text"
};
const _hoisted_43 = ["innerHTML"];
const _hoisted_44 = ["title", "value"];
const _hoisted_45 = ["value"];
const _hoisted_46 = {
  class: "ui-confirm ratefeatureDialog"
};
const _hoisted_47 = ["innerHTML"];
const _hoisted_48 = {
  key: 0
};
const _hoisted_49 = {
  key: 1
};
const _hoisted_50 = ["value"];
function RateFeaturevue_type_template_id_36ef4b69_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");
  const _component_ReviewLinks = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ReviewLinks");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    title: _ctx.translate('Feedback_RateFeatureTitle', _ctx.htmlEntities(_ctx.title)),
    class: "ratefeature"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "iconContainer",
    onMouseenter: _cache[2] || (_cache[2] = $event => _ctx.expanded = true),
    onMouseleave: _cache[3] || (_cache[3] = $event => _ctx.expanded = false)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[0] || (_cache[0] = $event => {
      _ctx.likeFeature();
    }),
    class: "like-icon",
    src: "plugins/Feedback/vue/src/RateFeature/thumbs-up.png"
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[1] || (_cache[1] = $event => {
      _ctx.dislikeFeature();
    }),
    class: "dislike-icon",
    src: "plugins/Feedback/vue/src/RateFeature/thumbs-down.png"
  })], 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showFeedbackForm,
    "onUpdate:modelValue": _cache[14] || (_cache[14] = $event => _ctx.showFeedbackForm = $event),
    onYes: _cache[15] || (_cache[15] = $event => _ctx.sendFeedback()),
    onValidation: _cache[16] || (_cache[16] = $event => _ctx.sendFeedback())
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", RateFeaturevue_type_template_id_36ef4b69_hoisted_2, [_ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [_ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLikeNamedFeature', _ctx.title)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLike')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "useful",
      value: "useful",
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.likeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.likeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureUsefulInfo')), 1)]), _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "easy",
      value: "easy",
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.likeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.likeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureEasyToUse')), 1)]), _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "configurable",
      value: "configurable",
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.likeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.likeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureConfigurable')), 1)]), _hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "likeother",
      value: "likeother",
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.likeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.likeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureOtherReason')), 1)])])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_16, [_ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeNamedFeature', _ctx.title)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.title ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("h2", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislike')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_22, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "missingfeatures",
      value: "missingfeatures",
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.dislikeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.dislikeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureDislikeAddMissingFeatures')), 1)]), _hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_24, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "makeeasier",
      value: "makeeasier",
      "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.dislikeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.dislikeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureDislikeMakeEasier')), 1)]), _hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_26, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "speedup",
      value: "speedup",
      "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => _ctx.dislikeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.dislikeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureDislikeSpeedUp')), 1)]), _hoisted_27, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "fixbugs",
      value: "fixbugs",
      "onUpdate:modelValue": _cache[11] || (_cache[11] = $event => _ctx.dislikeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.dislikeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureDislikeFixBugs')), 1)]), _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", _hoisted_30, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "radio",
      id: "dislikeother",
      value: "dislikeother",
      "onUpdate:modelValue": _cache[12] || (_cache[12] = $event => _ctx.dislikeReason = $event),
      class: "rateradio"
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.dislikeReason]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureOtherReason')), 1)]), _hoisted_31])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.likeReason || _ctx.dislikeReason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_32, [_ctx.likeReason && _ctx.likeReason === 'useful' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_33, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLikeExtraUseful')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.likeReason && _ctx.likeReason === 'easy' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_34, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLikeExtraEasy')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.likeReason && _ctx.likeReason === 'configurable' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_35, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLikeExtraConfigurable')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.likeReason && _ctx.likeReason === 'likeother' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_36, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLikeExtra')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.dislikeReason && _ctx.dislikeReason === 'missingfeatures' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_37, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeExtraMissing')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.dislikeReason && _ctx.dislikeReason === 'makeeasier' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_38, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeExtraEasier')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.dislikeReason && _ctx.dislikeReason === 'fixbugs' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_39, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeExtraBugs')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.dislikeReason && _ctx.dislikeReason === 'speedup' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_40, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeExtraSpeed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.dislikeReason && _ctx.dislikeReason === 'dislikeother' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_41, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislikeExtra')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.errorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_42, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.errorMessage), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", {
      ref: "feedbackText",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["materialize-textarea", {
        'has-error': _ctx.errorMessage
      }]),
      id: "feedbacktext",
      "onUpdate:modelValue": _cache[13] || (_cache[13] = $event => _ctx.feedbackMessage = $event)
    }, null, 2), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.feedbackMessage]]), _ctx.likeReason || _ctx.dislikeReason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
      key: 10,
      innerHTML: _ctx.$sanitize(_ctx.translate('Feedback_Policy', _ctx.externalLink('https://matomo.org/privacy-policy/'), '</a>'))
    }, null, 8, _hoisted_43)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "btn",
      type: "button",
      role: "validation",
      title: _ctx.translate('Feedback_RateFeatureSendFeedbackInformation'),
      value: _ctx.translate('Feedback_SendFeedback')
    }, null, 8, _hoisted_44), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "cancel",
      value: _ctx.translate('General_Cancel')
    }, null, 8, _hoisted_45)])]),
    _: 1
  }, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.ratingDone,
    "onUpdate:modelValue": _cache[17] || (_cache[17] = $event => _ctx.ratingDone = $event)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_46, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
      innerHTML: _ctx.$sanitize(_ctx.translate('Feedback_ThankYouHeart', `<i class='icon-heart red-text'></i>`))
    }, null, 8, _hoisted_47), _ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_48, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ReviewLinks)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_49, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_AppreciateFeedback')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      value: _ctx.translate('General_Close'),
      role: "yes"
    }, null, 8, _hoisted_50)])]),
    _: 1
  }, 8, ["modelValue"])], 8, RateFeaturevue_type_template_id_36ef4b69_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=36ef4b69

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts



/* harmony default export */ var RateFeaturevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    title: String
  },
  components: {
    MatomoDialog: external_CoreHome_["MatomoDialog"],
    ReviewLinks: ReviewLinks
  },
  data() {
    return {
      like: false,
      likeReason: null,
      dislikeReason: null,
      ratingDone: false,
      expanded: false,
      showFeedbackForm: false,
      feedbackMessage: '',
      errorMessage: null
    };
  },
  watch: {
    likeReason: 'doFocusInput',
    dislikeReason: 'doFocusInput'
  },
  methods: {
    dislikeFeature() {
      this.ratingDone = false;
      this.like = false;
      this.showFeedbackForm = true;
      this.errorMessage = null;
      this.likeReason = null;
      this.dislikeReason = null;
      this.feedbackMessage = '';
    },
    likeFeature() {
      this.ratingDone = false;
      this.like = true;
      this.showFeedbackForm = true;
      this.errorMessage = null;
      this.likeReason = null;
      this.dislikeReason = null;
      this.feedbackMessage = '';
    },
    doFocusInput() {
      this.$nextTick(() => {
        this.focusInput();
      });
    },
    focusInput() {
      if (this.$refs.feedbackText != null) {
        this.$refs.feedbackText.focus();
      }
    },
    sendFeedback() {
      this.errorMessage = null;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Feedback.sendFeedbackForFeature',
        featureName: this.title,
        like: this.like ? 1 : 0,
        choice: this.like ? this.likeReason : this.dislikeReason,
        message: this.feedbackMessage
      }).then(res => {
        if (res.value === 'success') {
          this.showFeedbackForm = false;
          this.ratingDone = true;
          this.feedbackMessage = '';
        } else {
          this.errorMessage = res.value;
        }
      });
    },
    htmlEntities(v) {
      return external_CoreHome_["Matomo"].helper.htmlEntities(v);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue



RateFeaturevue_type_script_lang_ts.render = RateFeaturevue_type_template_id_36ef4b69_render

/* harmony default export */ var RateFeature = (RateFeaturevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.vue?vue&type=template&id=ce1a48d2

const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_1 = {
  key: 0,
  class: "bannerHeader"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-heart red-text"
}, null, -1);
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("i", {
  class: "icon-close white-text"
}, null, -1);
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_4 = [FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_3];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_5 = {
  class: "ratefeature"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_6 = {
  class: "ui-confirm ratefeatureDialog"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_7 = ["innerHTML"];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_9 = {
  class: "messageContainer"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_10 = {
  key: 0,
  class: "error-text"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_12 = ["innerHTML"];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_13 = ["value"];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_14 = ["value"];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_15 = {
  class: "ui-confirm ratefeatureDialog"
};
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_16 = ["innerHTML"];
const FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_17 = ["value"];
function FeedbackQuestionvue_type_template_id_ce1a48d2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", null, [!_ctx.isHidden ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(`Feedback_FeedbackTitle`)) + " ", 1), FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_2]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.showQuestion && _ctx.showQuestion(...args)),
    class: "btn"
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(`Feedback_Question${_ctx.question}`)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "close-btn",
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.disableReminder && _ctx.disableReminder(...args))
  }, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_4)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showFeedbackForm,
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.showFeedbackForm = $event),
    onValidation: _cache[4] || (_cache[4] = $event => _ctx.sendFeedback())
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(`Feedback_Question${_ctx.question}`)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.translate('Feedback_FeedbackSubtitle', `<i class='icon-heart red-text'></i>`))
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_7), FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_9, [_ctx.errorMessage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.errorMessage), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", {
      id: "message",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'has-error': _ctx.errorMessage
      }),
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.feedbackMessage = $event)
    }, null, 2), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.feedbackMessage]])]), FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.feedbackPolicy)
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "validation",
      value: _ctx.translate('Feedback_SendFeedback')
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "cancel",
      value: _ctx.translate('General_Cancel')
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_14)])]),
    _: 1
  }, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.feedbackDone,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.feedbackDone = $event)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(`Feedback_ThankYou`)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
      innerHTML: _ctx.$sanitize(_ctx.translate('Feedback_ThankYourForFeedback', `<i class='icon-heart red-text'></i>`))
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "cancel",
      value: _ctx.translate('General_Close')
    }, null, 8, FeedbackQuestionvue_type_template_id_ce1a48d2_hoisted_17)])]),
    _: 1
  }, 8, ["modelValue"])])]);
}
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.vue?vue&type=template&id=ce1a48d2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.vue?vue&type=script&lang=ts


const {
  $
} = window;
const cookieName = 'feedback-question';
/* harmony default export */ var FeedbackQuestionvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    showQuestionBanner: Boolean
  },
  components: {
    MatomoDialog: external_CoreHome_["MatomoDialog"]
  },
  computed: {
    isHidden() {
      if (!this.showQuestionBanner) {
        return true;
      }
      return !!this.hide;
    },
    feedbackPolicy() {
      return Object(external_CoreHome_["translate"])('Feedback_Policy', /* eslint-disable prefer-template */
      Object(external_CoreHome_["externalLink"])('https://matomo.org/privacy-policy/'), '</a>');
    }
  },
  data() {
    return {
      questionText: '',
      question: 0,
      hide: null,
      feedbackDone: false,
      expanded: false,
      showFeedbackForm: false,
      feedbackMessage: null,
      errorMessage: null
    };
  },
  watch: {
    showFeedbackForm(val) {
      // eslint-disable-next-line no-underscore-dangle
      this.questionText = Object(external_CoreHome_["translate"])(`Feedback_Question${this.question}`);
      if (val) {
        setInterval(() => {
          $('#message').focus();
        }, 500);
      }
    }
  },
  created() {
    if (this.showQuestionBanner) {
      this.initQuestion();
    }
  },
  methods: {
    initQuestion() {
      if (!Object(external_CoreHome_["getCookie"])(cookieName)) {
        this.question = this.getRandomIntBetween(0, 4);
      } else {
        // eslint-disable-next-line radix
        this.question = parseInt(Object(external_CoreHome_["getCookie"])(cookieName));
      }
      const nextQuestion = (this.question + 1) % 4;
      const sevenDays = 7 * 60 * 60 * 24 * 1000;
      Object(external_CoreHome_["setCookie"])(cookieName, `${nextQuestion}`, sevenDays);
    },
    getRandomIntBetween(min, max) {
      // eslint-disable-next-line no-param-reassign
      min = Math.ceil(min);
      // eslint-disable-next-line no-param-reassign
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min + 1) + min);
    },
    showQuestion() {
      this.showFeedbackForm = true;
      this.errorMessage = null;
    },
    disableReminder() {
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Feedback.updateFeedbackReminderDate'
      });
      this.hide = true;
    },
    sendFeedback() {
      this.errorMessage = null;
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Feedback.sendFeedbackForSurvey',
        question: this.questionText,
        message: this.feedbackMessage
      }).then(res => {
        if (res.value === 'success') {
          this.showFeedbackForm = false;
          this.feedbackDone = true;
          this.hide = true;
        } else {
          this.errorMessage = res.value;
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/FeedbackQuestion/FeedbackQuestion.vue



FeedbackQuestionvue_type_script_lang_ts.render = FeedbackQuestionvue_type_template_id_ce1a48d2_render

/* harmony default export */ var FeedbackQuestion = (FeedbackQuestionvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/index.ts
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
//# sourceMappingURL=Feedback.umd.js.map