(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["Tour"] = factory(require("CoreHome"), require("vue"));
	else
		root["Tour"] = factory(root["CoreHome"], root["Vue"]);
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
/******/ 	__webpack_require__.p = "plugins/Tour/vue/dist/";
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
__webpack_require__.d(__webpack_exports__, "BecomeMatomoExpert", function() { return /* reexport */ BecomeMatomoExpert; });

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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Tour/vue/src/BecomeMatomoExpert/BecomeMatomoExpert.vue?vue&type=template&id=78131424

const _hoisted_1 = {
  class: "widgetBody tourEngagement"
};
const _hoisted_2 = {
  "aria-hidden": "true"
};
const _hoisted_3 = {
  key: 0
};
const _hoisted_4 = {
  class: "completed"
};
const _hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_7 = ["innerHTML"];
const _hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("br", null, null, -1);
const _hoisted_10 = ["innerHTML"];
const _hoisted_11 = {
  key: 1
};
const _hoisted_12 = {
  key: 0
};
const _hoisted_13 = ["innerHTML"];
const _hoisted_14 = ["title"];
const _hoisted_15 = ["title"];
const _hoisted_16 = ["title", "onClick"];
const _hoisted_17 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-hide"
}, null, -1);
const _hoisted_18 = [_hoisted_17];
const _hoisted_19 = ["href"];
const _hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_21 = {
  style: {
    "text-align": "center",
    "padding-bottom": "0"
  }
};
const _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("hr", null, null, -1);
const _hoisted_23 = ["innerHTML"];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [_ctx.loading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ActivityIndicator, {
    key: 0,
    loading: true
  })) : _ctx.level ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.level.numLevelsTotal, i => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: i
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["icon-star", _ctx.level.currentLevel >= i ? 'successStar' : 'upgradeStar'])
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' '))], 64);
  }), 128))]), _ctx.isCompleted ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", _hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Tour_CompletionTitle')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('Tour_CompletionMessage')) + " ", 1), _hoisted_5, _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.youCanCallYourselfHtml)
  }, null, 8, _hoisted_7), _hoisted_8, _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    innerHTML: _ctx.$sanitize(_ctx.shareHtml)
  }, null, 8, _hoisted_10)])])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_11, [_ctx.level.description ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.level.description), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    innerHTML: _ctx.$sanitize(_ctx.statusLevelHtml)
  }, null, 8, _hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.pagedChallenges, challenge => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: challenge.id,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["tourChallenge", challenge.id]),
      title: challenge.description
    }, [challenge.isCompleted || challenge.isSkipped ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      class: "icon-ok",
      title: _ctx.translate('Tour_ChallengeCompleted')
    }, null, 8, _hoisted_15)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 1,
      href: "javascript:void 0;",
      class: "skip-challenge",
      title: _ctx.translate('Tour_SkipThisChallenge'),
      onClick: $event => _ctx.skipChallenge(challenge.id)
    }, _hoisted_18, 8, _hoisted_16)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(' ') + " "), _ctx.$sanitizeUrl(challenge.url) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
      key: 2,
      href: _ctx.$sanitizeUrl(challenge.url),
      target: "_blank",
      rel: "noreferrer noopener"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(challenge.name), 9, _hoisted_19)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 3
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(challenge.name), 1)], 64))], 10, _hoisted_14);
  }), 128))]), _hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_21, [_ctx.hasPrevPage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "previousChallenges",
    onClick: _cache[0] || (_cache[0] = $event => _ctx.currentPage -= 1)
  }, " ‹ " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.hasNextPage ? _ctx.translate('General_Previous') : _ctx.translate('Tour_PreviousChallenges')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.hasPrevPage && _ctx.hasNextPage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" | ")], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.hasNextPage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    class: "nextChallenges",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.currentPage += 1)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.hasPrevPage ? _ctx.translate('General_Next') : _ctx.translate('Tour_NextChallenges')) + " › ", 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", {
    class: "tourSuperUserNote",
    innerHTML: _ctx.$sanitize(_ctx.superUserNoteHtml)
  }, null, 8, _hoisted_23)]))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/Tour/vue/src/BecomeMatomoExpert/BecomeMatomoExpert.vue?vue&type=template&id=78131424

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Tour/vue/src/BecomeMatomoExpert/BecomeMatomoExpert.vue?vue&type=script&lang=ts


const PER_PAGE = 5;
/* harmony default export */ var BecomeMatomoExpertvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"]
  },
  data() {
    return {
      loading: true,
      challenges: [],
      level: null,
      currentPage: 0
    };
  },
  computed: {
    isCompleted() {
      return this.challenges.every(c => c.isCompleted || c.isSkipped);
    },
    pagedChallenges() {
      const start = this.currentPage * PER_PAGE;
      return this.challenges.slice(start, start + PER_PAGE);
    },
    totalPages() {
      return Math.ceil(this.challenges.length / PER_PAGE);
    },
    hasPrevPage() {
      return this.currentPage > 0;
    },
    hasNextPage() {
      return this.currentPage < this.totalPages - 1;
    },
    statusLevelHtml() {
      if (!this.level) {
        return '';
      }
      return Object(external_CoreHome_["translate"])('Tour_StatusLevel', `<strong>${this.level.currentLevelName}</strong>`, String(this.level.challengesNeededForNextLevel), `<strong>${this.level.nextLevelName}</strong>`);
    },
    youCanCallYourselfHtml() {
      return Object(external_CoreHome_["translate"])('Tour_YouCanCallYourselfExpert', '<strong class="successStar">', '</strong>');
    },
    shareHtml() {
      if (!this.level) {
        return '';
      }
      const shareText = encodeURIComponent(Object(external_CoreHome_["translate"])('Tour_ShareAllChallengesCompleted', this.level.currentLevelName));
      const url = encodeURIComponent('https://matomo.org');
      const shareUrl = `http://twitter.com/share?text=${shareText}&url=${url}`;
      return Object(external_CoreHome_["translate"])('Tour_ShareYourAchievementOn', `<a target="_blank" rel="noreferrer noopener" href="${shareUrl}">Twitter</a>`);
    },
    superUserNoteHtml() {
      const faqUrl = 'https://matomo.org/faq/' + 'general/faq_35/';
      return Object(external_CoreHome_["translate"])('Tour_OnlyVisibleToSuperUser', Object(external_CoreHome_["externalLink"])(faqUrl), '</a>');
    }
  },
  mounted() {
    this.fetchData();
    window.addEventListener('focus', this.onFocus);
  },
  beforeUnmount() {
    window.removeEventListener('focus', this.onFocus);
  },
  methods: {
    translate: external_CoreHome_["translate"],
    onFocus() {
      this.fetchData();
    },
    async fetchData() {
      try {
        const [challenges, level] = await Promise.all([external_CoreHome_["AjaxHelper"].fetch({
          method: 'Tour.getChallenges'
        }), external_CoreHome_["AjaxHelper"].fetch({
          method: 'Tour.getLevel'
        })]);
        this.challenges = challenges;
        this.level = level;
        if (!this.loading) {
          return;
        }
        // set initial page to first uncompleted
        const firstIncomplete = challenges.findIndex(c => !c.isCompleted && !c.isSkipped);
        const done = firstIncomplete === -1 ? challenges.length : firstIncomplete;
        this.currentPage = Math.floor(done / PER_PAGE);
      } catch (_unused) {
        // silently keep current state on error
      } finally {
        this.loading = false;
      }
    },
    async skipChallenge(id) {
      const challenge = this.challenges.find(c => c.id === id);
      if (challenge) {
        challenge.isSkipped = true;
      }
      try {
        await external_CoreHome_["AjaxHelper"].post({
          method: 'Tour.skipChallenge',
          id
        });
      } catch (_unused2) {
        if (challenge) {
          challenge.isSkipped = false;
        }
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Tour/vue/src/BecomeMatomoExpert/BecomeMatomoExpert.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Tour/vue/src/BecomeMatomoExpert/BecomeMatomoExpert.vue



BecomeMatomoExpertvue_type_script_lang_ts.render = render

/* harmony default export */ var BecomeMatomoExpert = (BecomeMatomoExpertvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Tour/vue/src/index.ts
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=Tour.umd.js.map