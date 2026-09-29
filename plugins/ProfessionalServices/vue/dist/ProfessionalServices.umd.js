(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome"], factory);
	else if(typeof exports === 'object')
		exports["ProfessionalServices"] = factory(require("CoreHome"));
	else
		root["ProfessionalServices"] = factory(root["CoreHome"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__) {
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
/******/ 	__webpack_require__.p = "plugins/ProfessionalServices/vue/dist/";
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

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "DismissPromoWidget", function() { return /* reexport */ DismissPromoWidget; });
__webpack_require__.d(__webpack_exports__, "ProductPromotion", function() { return /* reexport */ ProductPromotion; });

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

// CONCATENATED MODULE: ./plugins/ProfessionalServices/vue/src/DismissPromoWidget/DismissPromoWidget.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function onClickDismissPromoWidgetLink(binding, event) {
  const {
    widgetName
  } = binding.value;
  const currentCategory = external_CoreHome_["ReportingMenuStore"].activeCategory.value;
  event.preventDefault();
  external_CoreHome_["Matomo"].helper.showAjaxLoading();
  return external_CoreHome_["AjaxHelper"].post({
    method: 'ProfessionalServices.dismissWidget'
  }, {
    widgetName
  }).catch(e => {
    external_CoreHome_["Matomo"].helper.hideAjaxLoading();
    throw e;
  }).then(() => {
    external_CoreHome_["ReportingMenuStore"].reloadMenuItems().then(() => {
      external_CoreHome_["Matomo"].helper.hideAjaxLoading();
      external_CoreHome_["MatomoUrl"].updateHash('category=Dashboard_Dashboard&subcategory=1');
      external_CoreHome_["NotificationsStore"].show({
        id: 'ProfessionalServices_PromoWidgetDismissed',
        animate: false,
        context: 'info',
        noclear: true,
        message: Object(external_CoreHome_["translate"])('ProfessionalServices_DismissedNotification', Object(external_CoreHome_["translate"])(currentCategory)),
        type: 'toast'
      });
    });
  });
}
/* harmony default export */ var DismissPromoWidget = ({
  mounted(element, binding) {
    const {
      widgetName
    } = binding.value;
    if (!widgetName) {
      return;
    }
    binding.value.onClickHandler = onClickDismissPromoWidgetLink.bind(null, binding);
    element.addEventListener('click', binding.value.onClickHandler);
  },
  unmounted(element, binding) {
    element.removeEventListener('click', binding.value.onClickHandler);
  }
});
// CONCATENATED MODULE: ./plugins/ProfessionalServices/vue/src/ProductPromotion/ProductPromotion.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function onDismiss(element, binding, event) {
  event.preventDefault();
  external_CoreHome_["Matomo"].helper.showAjaxLoading();
  return external_CoreHome_["AjaxHelper"].post({
    method: 'ProfessionalServices.dismissDashboardPromotion'
  }, {
    pluginName: binding.value.pluginName,
    triggerName: binding.value.triggerName
  }).then(() => {
    external_CoreHome_["Matomo"].helper.hideAjaxLoading();
    element.remove();
  }).catch(() => {
    // Handled rather than re-thrown: re-throwing here ends the chain in an unhandled
    // rejection, which tells the user nothing and shows up only in the console. The
    // banner stays, since it was not dismissed.
    external_CoreHome_["Matomo"].helper.hideAjaxLoading();
    external_CoreHome_["NotificationsStore"].show({
      message: Object(external_CoreHome_["translate"])('ProfessionalServices_PromotionDismissFailed'),
      context: 'error',
      id: 'productPromotionDismissFailed',
      placeat: '#notificationContainer',
      type: 'transient'
    });
  });
}
function onRequestTrial(element, binding, event) {
  event.preventDefault();
  // Already asked for. The button is disabled once the request succeeds, which stops a
  // real click, but not a programmatic one - and asking twice would send the super users
  // a second email for the same plugin.
  const requestTrial = element.querySelector('[data-role=requestTrial]');
  if (requestTrial && requestTrial.disabled) {
    return;
  }
  // Held from when the directive mounted rather than looked up now. modalConfirm() moves
  // this node out of the banner and into a modal on the body, and never puts it back, so
  // a fresh query finds nothing the second time and the link would quietly do nothing
  // after the user declines once.
  const confirm = binding.value.confirmElement;
  if (!confirm) {
    return;
  }
  external_CoreHome_["Matomo"].helper.modalConfirm(confirm, {
    yes: () => {
      external_CoreHome_["AjaxHelper"].post({
        module: 'API',
        method: 'Marketplace.requestTrial'
      }, {
        pluginName: binding.value.pluginName
      }).then(() => {
        const notificationInstanceId = external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('Marketplace_RequestTrialSubmitted', binding.value.productName),
          context: 'success',
          id: 'productPromotionTrialRequested',
          placeat: '#notificationContainer',
          type: 'transient'
        });
        external_CoreHome_["NotificationsStore"].scrollToNotification(notificationInstanceId);
        // The banner stays put and its button becomes the same disabled "Trial requested"
        // state the Marketplace uses, so the click is confirmed where the user made it.
        // It does not need to survive a reload: a pending trial request makes the promotion
        // ineligible, so the next dashboard load leaves it out altogether.
        if (requestTrial) {
          requestTrial.disabled = true;
          requestTrial.classList.add('productPromotion__ctaButton--requested');
          requestTrial.textContent = Object(external_CoreHome_["translate"])('Marketplace_TrialRequested');
        }
      }).catch(() => {
        // The request failed, so no trial is pending and the banner has to stay: removing
        // it would hide the only way back to this offer. Without this the rejection is an
        // unhandled promise and the user is told nothing at all.
        external_CoreHome_["NotificationsStore"].show({
          message: Object(external_CoreHome_["translate"])('ProfessionalServices_PromotionTrialRequestFailed'),
          context: 'error',
          id: 'productPromotionTrialRequestFailed',
          placeat: '#notificationContainer',
          type: 'transient'
        });
      });
    }
  });
}
/**
 * Drops the artwork from the layout when it cannot be loaded, so that a missing image costs
 * the reader nothing: the copy, the call to action and the dismiss control stay where they
 * are and take the space back. Without this the figure keeps its column and the banner
 * carries an empty panel, or a broken-image icon, for the rest of the page's life.
 */
function hideFigureIfImageFails(element) {
  const image = element.querySelector('.productPromotion__image');
  if (!image) {
    return;
  }
  const hideFigure = () => element.classList.add('productPromotion--noFigure');
  // The failure may already have happened - a cached 404, or an image that finished while
  // the directive was still being mounted - in which case no event is coming.
  if (image.complete && image.naturalWidth === 0) {
    hideFigure();
    return;
  }
  image.addEventListener('error', hideFigure, {
    once: true
  });
}
/* harmony default export */ var ProductPromotion = ({
  mounted(element, binding) {
    var _binding$value;
    if (!((_binding$value = binding.value) !== null && _binding$value !== void 0 && _binding$value.pluginName)) {
      return;
    }
    hideFigureIfImageFails(element);
    const dismiss = element.querySelector('[data-role=dismiss]');
    if (dismiss) {
      binding.value.onDismissHandler = onDismiss.bind(null, element, binding);
      dismiss.addEventListener('click', binding.value.onDismissHandler);
    }
    const requestTrial = element.querySelector('[data-role=requestTrial]');
    if (requestTrial) {
      binding.value.confirmElement = element.querySelector('[data-role=requestTrialConfirm]');
      binding.value.onRequestTrialHandler = onRequestTrial.bind(null, element, binding);
      requestTrial.addEventListener('click', binding.value.onRequestTrialHandler);
    }
  },
  unmounted(element, binding) {
    var _binding$value2, _binding$value3;
    // Written as an explicit lookup and guard rather than an optional-chained call: as a
    // statement, `a?.b()` is an expression this branch's lint rules reject outright.
    const dismiss = element.querySelector('[data-role=dismiss]');
    if (dismiss && (_binding$value2 = binding.value) !== null && _binding$value2 !== void 0 && _binding$value2.onDismissHandler) {
      dismiss.removeEventListener('click', binding.value.onDismissHandler);
    }
    const requestTrial = element.querySelector('[data-role=requestTrial]');
    if (requestTrial && (_binding$value3 = binding.value) !== null && _binding$value3 !== void 0 && _binding$value3.onRequestTrialHandler) {
      requestTrial.removeEventListener('click', binding.value.onRequestTrialHandler);
    }
  }
});
// CONCATENATED MODULE: ./plugins/ProfessionalServices/vue/src/index.ts
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
//# sourceMappingURL=ProfessionalServices.umd.js.map