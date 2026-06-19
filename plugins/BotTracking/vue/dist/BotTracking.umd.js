(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.BotTracking = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
    }
  return a;
};
var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));

  const _sfc_main$1 = vue.defineComponent({
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      VueEntryContainer: CoreHome.VueEntryContainer
    },
    props: {
      backToMatomoLink: String
    },
    data() {
      return {
        loading: true,
        errorMessage: null,
        showMethodDetails: null,
        recommendedMethod: null,
        trackingMethods: []
      };
    },
    created() {
      vue.watch(() => CoreHome.MatomoUrl.hashParsed.value.activeTab, (activeTab) => {
        this.showMethodDetails = this.findTrackingMethod(activeTab);
      });
      this.fetchTrackingMethods();
    },
    methods: {
      fetchTrackingMethods() {
        const params = {
          module: "BotTracking",
          action: "getTrackingMethodsForSite",
          idSite: CoreHome.Matomo.idSite
        };
        this.loading = true;
        this.errorMessage = null;
        CoreHome.AjaxHelper.fetch(params).then((response) => {
          const trackingMethods = Array.isArray(response == null ? void 0 : response.trackingMethods) ? [...response.trackingMethods] : [];
          const recommendedIndex = trackingMethods.findIndex((method) => method.wasDetected);
          if (recommendedIndex !== -1) {
            this.recommendedMethod = trackingMethods[recommendedIndex];
            trackingMethods.splice(recommendedIndex, 1);
          } else {
            this.recommendedMethod = null;
          }
          this.trackingMethods = trackingMethods;
        }).catch(() => {
          this.errorMessage = CoreHome.translate("General_ErrorRequest", "", "");
          this.recommendedMethod = null;
          this.trackingMethods = [];
        }).finally(() => {
          this.loading = false;
          this.showMethodDetails = this.findTrackingMethod(
            CoreHome.MatomoUrl.hashParsed.value.activeTab
          );
        });
      },
      findTrackingMethod(methodId) {
        if (this.recommendedMethod && methodId && this.recommendedMethod.id.toLowerCase() === methodId.toLowerCase()) {
          return this.recommendedMethod;
        }
        let trackingMethod = null;
        Object.entries(this.trackingMethods).forEach(([, method]) => {
          if (methodId && method.id.toLowerCase() === methodId.toLowerCase() && method.content) {
            trackingMethod = method;
          }
        });
        return trackingMethod;
      },
      showMethod(methodId) {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), { activeTab: methodId.toLowerCase() }));
      },
      showOverview() {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), { activeTab: null }));
      }
    },
    computed: {
      headline() {
        if (this.showMethodDetails && this.showMethodDetails.name) {
          if (this.showMethodDetails.isOthers) {
            return this.showMethodDetails.name;
          }
          return CoreHome.translate("BotTracking_SiteWithoutDataInstallWithX", this.showMethodDetails.name);
        }
        return CoreHome.translate("BotTracking_SiteWithoutDataChooseTrackingMethod");
      }
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-chevron-left" }, null, -1);
  const _hoisted_2 = { key: 1 };
  const _hoisted_3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_4 = {
    key: 0,
    class: "row"
  };
  const _hoisted_5 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-warning" }, null, -1);
  const _hoisted_6 = {
    key: 1,
    class: "row tracking-method-detection"
  };
  const _hoisted_7 = ["src", "alt"];
  const _hoisted_8 = ["href"];
  const _hoisted_9 = { class: "row tracking-method-list" };
  const _hoisted_10 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-search" }, null, -1);
  const _hoisted_11 = ["href", "onClick"];
  const _hoisted_12 = ["src"];
  const _hoisted_13 = {
    key: 1,
    class: "list-entry-icon",
    "aria-hidden": "true"
  };
  const _hoisted_14 = { class: "list-entry-text" };
  const _hoisted_15 = ["href"];
  const _hoisted_16 = ["src"];
  const _hoisted_17 = {
    key: 1,
    class: "list-entry-icon",
    "aria-hidden": "true"
  };
  const _hoisted_18 = { class: "list-entry-text" };
  const _hoisted_19 = { class: "tracking-method-skip" };
  const _hoisted_20 = ["href"];
  const _hoisted_21 = ["data-method"];
  const _hoisted_22 = ["src", "alt"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_VueEntryContainer = vue.resolveComponent("VueEntryContainer");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      _ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 0,
        class: "tracking-method-back",
        onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => {
          _ctx.showOverview();
        }, ["prevent"]))
      }, [
        _hoisted_1$1,
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Mobile_NavigationBack")), 1)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("h1", null, vue.toDisplayString(_ctx.headline), 1),
      !_ctx.loading && !_ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataChooseTrackingMethodPreamble1")) + " ", 1),
        _hoisted_3,
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataChooseTrackingMethodPreamble2")), 1)
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_ActivityIndicator, {
        "loading-message": _ctx.translate("BotTracking_DetectingYourSite"),
        loading: _ctx.loading
      }, null, 8, ["loading-message", "loading"]),
      !_ctx.loading && !_ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 2 }, [
        _ctx.errorMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4, [
          _hoisted_5,
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.errorMessage), 1),
          vue.createElementVNode("a", {
            class: "btn",
            href: "#",
            onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.fetchTrackingMethods(), ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_Refresh")), 1)
        ])) : vue.createCommentVNode("", true),
        _ctx.recommendedMethod ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, [
          vue.createElementVNode("img", {
            src: _ctx.recommendedMethod.icon,
            alt: `${_ctx.recommendedMethod.name} logo`
          }, null, 8, _hoisted_7),
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate(
            "BotTracking_SiteWithoutDataInstallWithXRecommendation",
            _ctx.recommendedMethod.name
          )), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate(
            "BotTracking_SiteWithoutDataRecommendationText",
            _ctx.recommendedMethod.name
          )), 1),
          vue.createElementVNode("a", {
            href: `#${_ctx.recommendedMethod.id.toLowerCase()}`,
            class: "btn",
            onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => _ctx.showMethod(_ctx.recommendedMethod.id), ["prevent"]))
          }, vue.toDisplayString(_ctx.translate(
            "BotTracking_SiteWithoutDataInstallWithX",
            _ctx.recommendedMethod.name
          )), 9, _hoisted_8)
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_9, [
          _hoisted_10,
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataOtherInstallMethods")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataOtherInstallMethodsIntro")), 1),
          vue.createElementVNode("ul", null, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.trackingMethods, (method) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                class: "list-entry",
                key: method.id
              }, [
                method.content ? (vue.openBlock(), vue.createElementBlock("a", {
                  key: 0,
                  href: `#${method.id.toLowerCase()}`,
                  onClick: vue.withModifiers(($event) => _ctx.showMethod(method.id), ["prevent"])
                }, [
                  method.icon ? (vue.openBlock(), vue.createElementBlock("img", {
                    key: 0,
                    src: method.icon,
                    class: "list-entry-icon"
                  }, null, 8, _hoisted_12)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_13)),
                  vue.createElementVNode("span", _hoisted_14, vue.toDisplayString(method.name), 1)
                ], 8, _hoisted_11)) : method.link ? (vue.openBlock(), vue.createElementBlock("a", {
                  key: 1,
                  href: method.link,
                  target: "_blank",
                  rel: "noreferrer noopener"
                }, [
                  method.icon ? (vue.openBlock(), vue.createElementBlock("img", {
                    key: 0,
                    src: method.icon,
                    class: "list-entry-icon"
                  }, null, 8, _hoisted_16)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_17)),
                  vue.createElementVNode("span", _hoisted_18, vue.toDisplayString(method.name), 1)
                ], 8, _hoisted_15)) : vue.createCommentVNode("", true)
              ]);
            }), 128))
          ])
        ]),
        vue.createElementVNode("div", _hoisted_19, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataNotYetReady")), 1),
          vue.createElementVNode("a", { href: _ctx.backToMatomoLink }, vue.toDisplayString(_ctx.translate("BotTracking_SiteWithoutDataBackToMatomo")), 9, _hoisted_20)
        ])
      ], 64)) : vue.createCommentVNode("", true),
      _ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 3,
        class: "tracking-method-details",
        "data-method": _ctx.showMethodDetails.id
      }, [
        vue.createElementVNode("img", {
          src: _ctx.showMethodDetails.icon,
          alt: `${_ctx.showMethodDetails.name} logo`
        }, null, 8, _hoisted_22),
        vue.createVNode(_component_VueEntryContainer, {
          html: _ctx.showMethodDetails.content
        }, null, 8, ["html"])
      ], 8, _hoisted_21)) : vue.createCommentVNode("", true)
    ]);
  }
  const SiteWithoutData = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    computed: {
      noDataUrl() {
        var _a;
        const { period, date } = CoreHome.MatomoUrl.parsed.value;
        const query = CoreHome.MatomoUrl.stringify({
          module: "BotTracking",
          action: "siteWithoutData",
          idSite: (_a = CoreHome.Matomo.idSite) != null ? _a : CoreHome.MatomoUrl.parsed.value.idSite,
          period,
          date
        });
        return `index.php?${query}`;
      },
      messageHtml() {
        const linkOpen = `<a href="${this.noDataUrl}">`;
        return CoreHome.translate(
          "BotTracking_NoRecentAIBotRequests",
          "<strong>",
          "</strong>",
          linkOpen,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "alert alert-warning bot-tracking-no-recent-requests-message",
      innerHTML: _ctx.$sanitize(_ctx.messageHtml)
    }, null, 8, _hoisted_1);
  }
  const NoRecentRequestsWidget = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.NoRecentRequestsWidget = NoRecentRequestsWidget;
  exports2.SiteWithoutData = SiteWithoutData;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
