(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Feedback"] = factory(require("CoreHome"), require("vue"));
	else
		root["Feedback"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE_CoreHome__, __WEBPACK_EXTERNAL_MODULE_vue__) {
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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8 ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = [\"title\"];\nconst _hoisted_2 = {\n  class: \"ui-confirm ratefeatureDialog\"\n};\nconst _hoisted_3 = {\n  key: 0\n};\nconst _hoisted_4 = {\n  key: 1\n};\n\nconst _hoisted_5 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"br\", null, null, -1\n/* HOISTED */\n);\n\nconst _hoisted_6 = {\n  class: \"messageContainer\"\n};\nconst _hoisted_7 = [\"title\", \"value\"];\nconst _hoisted_8 = [\"value\"];\nconst _hoisted_9 = {\n  class: \"ui-confirm ratefeatureDialog\"\n};\nconst _hoisted_10 = {\n  key: 0\n};\nconst _hoisted_11 = [\"value\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_MatomoDialog = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"MatomoDialog\");\n\n  const _component_ReviewLinks = Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"resolveComponent\"])(\"ReviewLinks\");\n\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", {\n    title: _ctx.translate('Feedback_RateFeatureTitle', _ctx.$sanitize(_ctx.title)),\n    class: \"ratefeature\"\n  }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", {\n    class: \"iconContainer\",\n    onMouseenter: _cache[2] || (_cache[2] = $event => _ctx.expanded = true),\n    onMouseleave: _cache[3] || (_cache[3] = $event => _ctx.expanded = false)\n  }, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"img\", {\n    onClick: _cache[0] || (_cache[0] = $event => {\n      _ctx.likeFeature();\n\n      _ctx.showFeedbackForm = true;\n    }),\n    class: \"like-icon\",\n    src: \"plugins/Feedback/vue/src/RateFeature/thumbs-up.png\"\n  }), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"img\", {\n    onClick: _cache[1] || (_cache[1] = $event => {\n      _ctx.dislikeFeature();\n\n      _ctx.showFeedbackForm = true;\n    }),\n    class: \"dislike-icon\",\n    src: \"plugins/Feedback/vue/src/RateFeature/thumbs-down.png\"\n  }, null, 512\n  /* NEED_PATCH */\n  ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vShow\"], _ctx.expanded]])], 32\n  /* HYDRATE_EVENTS */\n  ), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_MatomoDialog, {\n    modelValue: _ctx.showFeedbackForm,\n    \"onUpdate:modelValue\": _cache[5] || (_cache[5] = $event => _ctx.showFeedbackForm = $event),\n    onYes: _cache[6] || (_cache[6] = $event => _ctx.sendFeedback())\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(() => [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_2, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Feedback_RateFeatureThankYouTitle', _ctx.title)), 1\n    /* TEXT */\n    ), _ctx.like ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"p\", _hoisted_3, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Feedback_RateFeatureLeaveMessageLike')), 1\n    /* TEXT */\n    )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), !_ctx.like ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"p\", _hoisted_4, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Feedback_RateFeatureLeaveMessageDislike')), 1\n    /* TEXT */\n    )) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), _hoisted_5, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_6, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withDirectives\"])(Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"textarea\", {\n      \"onUpdate:modelValue\": _cache[4] || (_cache[4] = $event => _ctx.feedbackMessage = $event)\n    }, null, 512\n    /* NEED_PATCH */\n    ), [[vue__WEBPACK_IMPORTED_MODULE_0__[\"vModelText\"], _ctx.feedbackMessage]])]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n      type: \"button\",\n      title: _ctx.translate('Feedback_RateFeatureSendFeedbackInformation'),\n      value: _ctx.translate('Feedback_SendFeedback'),\n      role: \"yes\"\n    }, null, 8\n    /* PROPS */\n    , _hoisted_7), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n      type: \"button\",\n      role: \"cancel\",\n      value: _ctx.translate('General_Cancel')\n    }, null, 8\n    /* PROPS */\n    , _hoisted_8)])]),\n    _: 1\n    /* STABLE */\n\n  }, 8\n  /* PROPS */\n  , [\"modelValue\"]), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_MatomoDialog, {\n    modelValue: _ctx.ratingDone,\n    \"onUpdate:modelValue\": _cache[7] || (_cache[7] = $event => _ctx.ratingDone = $event)\n  }, {\n    default: Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"withCtx\"])(() => [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"div\", _hoisted_9, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"h2\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Feedback_ThankYou', _ctx.title)), 1\n    /* TEXT */\n    ), _ctx.like ? (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_10, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createVNode\"])(_component_ReviewLinks)])) : Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createCommentVNode\"])(\"v-if\", true), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"input\", {\n      type: \"button\",\n      value: _ctx.translate('General_Ok'),\n      role: \"yes\"\n    }, null, 8\n    /* PROPS */\n    , _hoisted_11)])]),\n    _: 1\n    /* STABLE */\n\n  }, 8\n  /* PROPS */\n  , [\"modelValue\"])], 8\n  /* PROPS */\n  , _hoisted_1);\n}\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2 ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"requestReview\"\n};\n\nconst _hoisted_2 = /*#__PURE__*/Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createStaticVNode\"])(\"<br><br><div class=\\\"review-links\\\"><div class=\\\"review-link\\\"><a href=\\\"https://www.capterra.com/p/182627/Matomo-Analytics/\\\" target=\\\"_blank\\\"><div class=\\\"image\\\"><img loading=\\\"lazy\\\" src=\\\"plugins/Feedback/images/capterra.svg\\\"></div><div class=\\\"link\\\">Capterra</div></a></div><div class=\\\"review-link\\\"><a href=\\\"https://www.g2crowd.com/products/matomo-formerly-piwik/details\\\" target=\\\"_blank\\\"><div class=\\\"image\\\"><img loading=\\\"lazy\\\" src=\\\"plugins/Feedback/images/g2crowd.svg\\\"></div><div class=\\\"link\\\">G2 Crowd</div></a></div><div class=\\\"review-link\\\"><a href=\\\"https://www.producthunt.com/posts/matomo-2\\\" target=\\\"_blank\\\"><div class=\\\"image\\\"><img loading=\\\"lazy\\\" src=\\\"plugins/Feedback/images/producthunt.svg\\\"></div><div class=\\\"link\\\">Product Hunt</div></a></div><div class=\\\"review-link\\\"><a href=\\\"https://www.saasworthy.com/product/matomo\\\" target=\\\"_blank\\\"><div class=\\\"image\\\"><img loading=\\\"lazy\\\" src=\\\"plugins/Feedback/images/saasworthy.png\\\"></div><div class=\\\"link\\\">SaaSworthy</div></a></div><div class=\\\"review-link\\\"><a href=\\\"https://www.trustradius.com/products/matomo/reviews\\\" target=\\\"_blank\\\"><div class=\\\"image\\\"><img loading=\\\"lazy\\\" src=\\\"plugins/Feedback/images/trustradius.svg\\\"></div><div class=\\\"link\\\">TrustRadius</div></a></div></div>\", 3);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"openBlock\"])(), Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementBlock\"])(\"div\", _hoisted_1, [Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"createElementVNode\"])(\"p\", null, Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"toDisplayString\"])(_ctx.translate('Feedback_PleaseLeaveExternalReviewForMatomo')), 1\n  /* TEXT */\n  ), _hoisted_2]);\n}\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _ReviewLinks_ReviewLinks_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../ReviewLinks/ReviewLinks.vue */ \"./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue\");\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({\n  props: {\n    title: String\n  },\n  components: {\n    MatomoDialog: CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"MatomoDialog\"],\n    ReviewLinks: _ReviewLinks_ReviewLinks_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n  },\n\n  data() {\n    return {\n      like: false,\n      ratingDone: false,\n      expanded: false,\n      showFeedbackForm: false,\n      feedbackMessage: ''\n    };\n  },\n\n  methods: {\n    dislikeFeature() {\n      this.like = false;\n    },\n\n    likeFeature() {\n      this.like = true;\n    },\n\n    sendFeedback() {\n      CoreHome__WEBPACK_IMPORTED_MODULE_1__[\"AjaxHelper\"].fetch({\n        method: 'Feedback.sendFeedbackForFeature',\n        featureName: this.title,\n        like: this.like ? '1' : '0',\n        message: this.feedbackMessage\n      });\n      this.ratingDone = true;\n    }\n\n  }\n}));\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(vue__WEBPACK_IMPORTED_MODULE_0__[\"defineComponent\"])({}));\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js":
/*!**********************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js ***!
  \**********************************************************************************/
/*! exports provided: ReviewLinks, RateFeature */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _setPublicPath__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPublicPath */ \"./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js\");\n/* harmony import */ var _entry__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ~entry */ \"./plugins/Feedback/vue/src/index.ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ReviewLinks\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"ReviewLinks\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"RateFeature\", function() { return _entry__WEBPACK_IMPORTED_MODULE_1__[\"RateFeature\"]; });\n\n\n\n\n\n//# sourceURL=webpack://Feedback/./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js?");

/***/ }),

/***/ "./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js":
/*!***************************************************************************!*\
  !*** ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// This file is imported into lib/wc client bundles.\n\nif (typeof window !== 'undefined') {\n  var currentScript = window.document.currentScript\n  if (false) { var getCurrentScript; }\n\n  var src = currentScript && currentScript.src.match(/(.+\\/)[^/]+\\.js(\\?.*)?$/)\n  if (src) {\n    __webpack_require__.p = src[1] // eslint-disable-line\n  }\n}\n\n// Indicate to webpack that this file can be concatenated\n/* harmony default export */ __webpack_exports__[\"default\"] = (null);\n\n\n//# sourceURL=webpack://Feedback/./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/RateFeature/RateFeature.adapter.ts":
/*!*********************************************************************!*\
  !*** ./plugins/Feedback/vue/src/RateFeature/RateFeature.adapter.ts ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! CoreHome */ \"CoreHome\");\n/* harmony import */ var CoreHome__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(CoreHome__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _RateFeature_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RateFeature.vue */ \"./plugins/Feedback/vue/src/RateFeature/RateFeature.vue\");\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (Object(CoreHome__WEBPACK_IMPORTED_MODULE_0__[\"createAngularJsAdapter\"])({\n  component: _RateFeature_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  scope: {\n    title: {\n      vue: 'title',\n      angularJsBind: '@'\n    }\n  },\n  directiveName: 'piwikRateFeature' // TODO: default to piwik + component name\n\n}));\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.adapter.ts?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/RateFeature/RateFeature.vue":
/*!**************************************************************!*\
  !*** ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _RateFeature_vue_vue_type_template_id_62286ac8__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RateFeature.vue?vue&type=template&id=62286ac8 */ \"./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8\");\n/* harmony import */ var _RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RateFeature.vue?vue&type=script&lang=ts */ \"./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _RateFeature_vue_vue_type_template_id_62286ac8__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Feedback/vue/src/RateFeature/RateFeature.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts":
/*!**************************************************************************************!*\
  !*** ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts ***!
  \**************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./RateFeature.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_RateFeature_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8":
/*!********************************************************************************************!*\
  !*** ./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8 ***!
  \********************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_RateFeature_vue_vue_type_template_id_62286ac8__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./RateFeature.vue?vue&type=template&id=62286ac8 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?vue&type=template&id=62286ac8\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_RateFeature_vue_vue_type_template_id_62286ac8__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/RateFeature/RateFeature.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue":
/*!**************************************************************!*\
  !*** ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ReviewLinks_vue_vue_type_template_id_47a1a1b2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ReviewLinks.vue?vue&type=template&id=47a1a1b2 */ \"./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2\");\n/* harmony import */ var _ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ReviewLinks.vue?vue&type=script&lang=ts */ \"./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts\");\n/* empty/unused harmony star reexport */\n\n\n_ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].render = _ReviewLinks_vue_vue_type_template_id_47a1a1b2__WEBPACK_IMPORTED_MODULE_0__[\"render\"]\n/* hot reload */\nif (false) {}\n\n_ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"].__file = \"plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue\"\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (_ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_1__[\"default\"]);\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts":
/*!**************************************************************************************!*\
  !*** ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts ***!
  \**************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--14-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--14-2!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ReviewLinks.vue?vue&type=script&lang=ts */ \"./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader/index.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=script&lang=ts\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return _node_modules_vue_cli_plugin_typescript_node_modules_cache_loader_dist_cjs_js_ref_14_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_plugin_typescript_node_modules_ts_loader_index_js_ref_14_2_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ReviewLinks_vue_vue_type_script_lang_ts__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; });\n\n/* empty/unused harmony star reexport */ \n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2":
/*!********************************************************************************************!*\
  !*** ./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2 ***!
  \********************************************************************************************/
/*! exports provided: render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ReviewLinks_vue_vue_type_template_id_47a1a1b2__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--12-0!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!../../../../../node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--0-0!../../../../../node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--0-1!./ReviewLinks.vue?vue&type=template&id=47a1a1b2 */ \"./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js?!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js?!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/index.js?!./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?vue&type=template&id=47a1a1b2\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_cli_plugin_babel_node_modules_cache_loader_dist_cjs_js_ref_12_0_node_modules_babel_loader_lib_index_js_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_templateLoader_js_ref_6_node_modules_vue_cli_service_node_modules_cache_loader_dist_cjs_js_ref_0_0_node_modules_vue_cli_service_node_modules_vue_loader_v16_dist_index_js_ref_0_1_ReviewLinks_vue_vue_type_template_id_47a1a1b2__WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue?");

/***/ }),

/***/ "./plugins/Feedback/vue/src/index.ts":
/*!*******************************************!*\
  !*** ./plugins/Feedback/vue/src/index.ts ***!
  \*******************************************/
/*! exports provided: ReviewLinks, RateFeature */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _RateFeature_RateFeature_adapter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RateFeature/RateFeature.adapter */ \"./plugins/Feedback/vue/src/RateFeature/RateFeature.adapter.ts\");\n/* harmony import */ var _ReviewLinks_ReviewLinks_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ReviewLinks/ReviewLinks.vue */ \"./plugins/Feedback/vue/src/ReviewLinks/ReviewLinks.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"ReviewLinks\", function() { return _ReviewLinks_ReviewLinks_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]; });\n\n/* harmony import */ var _RateFeature_RateFeature_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./RateFeature/RateFeature.vue */ \"./plugins/Feedback/vue/src/RateFeature/RateFeature.vue\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"RateFeature\", function() { return _RateFeature_RateFeature_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]; });\n\n/*!\n * Matomo - free/libre analytics platform\n *\n * @link https://matomo.org\n * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later\n */\n\n\n\n\n//# sourceURL=webpack://Feedback/./plugins/Feedback/vue/src/index.ts?");

/***/ }),

/***/ "CoreHome":
/*!***************************!*\
  !*** external "CoreHome" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_CoreHome__;\n\n//# sourceURL=webpack://Feedback/external_%22CoreHome%22?");

/***/ }),

/***/ "vue":
/*!******************************************************************!*\
  !*** external {"commonjs":"vue","commonjs2":"vue","root":"Vue"} ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("module.exports = __WEBPACK_EXTERNAL_MODULE_vue__;\n\n//# sourceURL=webpack://Feedback/external_%7B%22commonjs%22:%22vue%22,%22commonjs2%22:%22vue%22,%22root%22:%22Vue%22%7D?");

/***/ })

/******/ });
});