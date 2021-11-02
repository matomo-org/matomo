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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=65bd066a

const _hoisted_1 = ["title"];
const _hoisted_2 = {
  class: "ui-confirm ratefeatureDialog"
};
const _hoisted_3 = {
  key: 0
};
const _hoisted_4 = {
  key: 1
};

const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);

const _hoisted_6 = {
  class: "messageContainer"
};
const _hoisted_7 = ["title", "value"];
const _hoisted_8 = ["value"];
const _hoisted_9 = {
  class: "ui-confirm ratefeatureDialog"
};
const _hoisted_10 = {
  key: 0
};
const _hoisted_11 = ["value"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_MatomoDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MatomoDialog");

  const _component_ReviewLinks = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ReviewLinks");

  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    title: _ctx.translate('Feedback_RateFeatureTitle', _ctx.$sanitize(_ctx.title)),
    class: "ratefeature"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "iconContainer",
    onMouseenter: _cache[2] || (_cache[2] = $event => _ctx.expanded = true),
    onMouseleave: _cache[3] || (_cache[3] = $event => _ctx.expanded = false)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[0] || (_cache[0] = $event => {
      _ctx.likeFeature();

      _ctx.showFeedbackForm = true;
    }),
    class: "like-icon",
    src: "plugins/Feedback/vue/src/RateFeature/thumbs-up.png"
  }), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
    onClick: _cache[1] || (_cache[1] = $event => {
      _ctx.dislikeFeature();

      _ctx.showFeedbackForm = true;
    }),
    class: "dislike-icon",
    src: "plugins/Feedback/vue/src/RateFeature/thumbs-down.png"
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], _ctx.expanded]])], 32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.showFeedbackForm,
    "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.showFeedbackForm = $event),
    onYes: _cache[6] || (_cache[6] = $event => _ctx.sendFeedback())
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureThankYouTitle', _ctx.title)), 1), _ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLike')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislike')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("textarea", {
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.feedbackMessage = $event)
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.feedbackMessage]])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      title: _ctx.translate('Feedback_RateFeatureSendFeedbackInformation'),
      value: _ctx.translate('Feedback_SendFeedback'),
      role: "yes"
    }, null, 8, _hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      role: "cancel",
      value: _ctx.translate('General_Cancel')
    }, null, 8, _hoisted_8)])]),
    _: 1
  }, 8, ["modelValue"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MatomoDialog, {
    modelValue: _ctx.ratingDone,
    "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.ratingDone = $event)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_ThankYou', _ctx.title)), 1), _ctx.like ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ReviewLinks)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      type: "button",
      value: _ctx.translate('General_Ok'),
      role: "yes"
    }, null, 8, _hoisted_11)])]),
    _: 1
  }, 8, ["modelValue"])], 8, _hoisted_1);
}
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=65bd066a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=4471fc41

const ReviewLinksvue_type_template_id_4471fc41_hoisted_1 = {
  class: "requestReview"
};

const ReviewLinksvue_type_template_id_4471fc41_hoisted_2 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createStaticVNode"])("<br><br><div class=\"review-links\"><div class=\"review-link\"><a href=\"https://www.capterra.com/p/182627/Matomo-Analytics/\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/capterra.svg\"></div><div class=\"link\">Capterra</div></a></div><div class=\"review-link\"><a href=\"https://www.g2crowd.com/products/matomo-formerly-piwik/details\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/g2crowd.svg\"></div><div class=\"link\">G2 Crowd</div></a></div><div class=\"review-link\"><a href=\"https://www.producthunt.com/posts/matomo-2\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/producthunt.svg\"></div><div class=\"link\">Product Hunt</div></a></div><div class=\"review-link\"><a href=\"https://www.saasworthy.com/product/matomo\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/saasworthy.png\"></div><div class=\"link\">SaaSworthy</div></a></div><div class=\"review-link\"><a href=\"https://www.trustradius.com/products/matomo/reviews\" target=\"_blank\"><div class=\"image\"><img loading=\"lazy\" src=\"plugins/Feedback/images/trustradius.svg\"></div><div class=\"link\">TrustRadius</div></a></div></div>", 3);

function ReviewLinksvue_type_template_id_4471fc41_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ReviewLinksvue_type_template_id_4471fc41_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Feedback_PleaseLeaveExternalReviewForMatomo')), 1), ReviewLinksvue_type_template_id_4471fc41_hoisted_2]);
}
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=4471fc41

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts

/* harmony default export */ var ReviewLinksvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue



ReviewLinksvue_type_script_lang_ts.render = ReviewLinksvue_type_template_id_4471fc41_render

/* harmony default export */ var ReviewLinks = (ReviewLinksvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/@vue/cli-plugin-typescript/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-3!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts



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
      ratingDone: false,
      expanded: false,
      showFeedbackForm: false,
      feedbackMessage: ''
    };
  },

  methods: {
    dislikeFeature() {
      this.like = false;
    },

    likeFeature() {
      this.like = true;
    },

    sendFeedback() {
      external_CoreHome_["AjaxHelper"].fetch({
        method: 'Feedback.sendFeedbackForFeature',
        featureName: this.title,
        like: this.like ? '1' : '0',
        message: this.feedbackMessage
      });
      this.ratingDone = true;
    }

  }
}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue



RateFeaturevue_type_script_lang_ts.render = render

/* harmony default export */ var RateFeature = (RateFeaturevue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/RateFeature/RateFeature.adapter.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/* harmony default export */ var RateFeature_adapter = (Object(external_CoreHome_["createAngularJsAdapter"])({
  component: RateFeature,
  scope: {
    title: {
      angularJsBind: '@'
    }
  },
  directiveName: 'piwikRateFeature'
}));
// CONCATENATED MODULE: ./plugins/Feedback/vue/src/index.ts
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
//# sourceMappingURL=Feedback.umd.js.map