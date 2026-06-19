(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.ProfessionalServices = {}, global.CoreHome));
})(this, (function(exports2, CoreHome) {
  "use strict";
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function onClickDismissPromoWidgetLink(binding, event) {
    const { widgetName } = binding.value;
    const currentCategory = CoreHome.ReportingMenuStore.activeCategory.value;
    event.preventDefault();
    CoreHome.Matomo.helper.showAjaxLoading();
    return CoreHome.AjaxHelper.post({
      method: "ProfessionalServices.dismissWidget"
    }, {
      widgetName
    }).catch((e) => {
      CoreHome.Matomo.helper.hideAjaxLoading();
      throw e;
    }).then(() => {
      CoreHome.ReportingMenuStore.reloadMenuItems().then(() => {
        CoreHome.Matomo.helper.hideAjaxLoading();
        CoreHome.MatomoUrl.updateHash("category=Dashboard_Dashboard&subcategory=1");
        CoreHome.NotificationsStore.show({
          id: "ProfessionalServices_PromoWidgetDismissed",
          animate: false,
          context: "info",
          noclear: true,
          message: CoreHome.translate("ProfessionalServices_DismissedNotification", CoreHome.translate(currentCategory)),
          type: "toast"
        });
      });
    });
  }
  const DismissPromoWidget = {
    mounted(element, binding) {
      const { widgetName } = binding.value;
      if (!widgetName) {
        return;
      }
      binding.value.onClickHandler = onClickDismissPromoWidgetLink.bind(null, binding);
      element.addEventListener("click", binding.value.onClickHandler);
    },
    unmounted(element, binding) {
      element.removeEventListener("click", binding.value.onClickHandler);
    }
  };
  exports2.DismissPromoWidget = DismissPromoWidget;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
