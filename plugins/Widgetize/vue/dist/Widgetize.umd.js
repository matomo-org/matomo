(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Widgetize = {}, global.Vue, global.CoreHome));
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

  const _sfc_main$2 = vue.defineComponent({
    props: {
      urlIframe: {
        type: String,
        required: true
      },
      widgetIframeHtml: {
        type: String,
        required: true
      }
    },
    inheritAttrs: false,
    directives: {
      SelectOnFocus: CoreHome.SelectOnFocus
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$2 = { id: "embedThisWidgetIframe" };
  const _hoisted_2$1 = ["innerHTML"];
  const _hoisted_3$1 = { id: "embedThisWidgetIframeInput" };
  const _hoisted_4$1 = {
    readonly: "true",
    id: "iframeEmbed"
  };
  const _hoisted_5$1 = ["innerHTML"];
  const _hoisted_6$1 = { id: "embedThisWidgetDirectLink" };
  const _hoisted_7$1 = {
    readonly: "true",
    id: "directLinkEmbed"
  };
  const _hoisted_8$1 = ["href"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_select_on_focus = vue.resolveDirective("select-on-focus");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1$2, [
        vue.createElementVNode("label", {
          for: "embedThisWidgetIframeInput",
          innerHTML: _ctx.$sanitize(_ctx.translate("Widgetize_EmbedIframe"))
        }, null, 8, _hoisted_2$1),
        vue.createElementVNode("div", _hoisted_3$1, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", _hoisted_4$1, [
            vue.createTextVNode(vue.toDisplayString(_ctx.widgetIframeHtml), 1)
          ])), [
            [_directive_select_on_focus, {}]
          ])
        ])
      ]),
      vue.createElementVNode("div", null, [
        vue.createElementVNode("label", {
          for: "embedThisWidgetDirectLink",
          innerHTML: _ctx.$sanitize(_ctx.translate("Widgetize_DirectLink"))
        }, null, 8, _hoisted_5$1),
        vue.createElementVNode("div", _hoisted_6$1, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", _hoisted_7$1, [
            vue.createTextVNode(vue.toDisplayString(_ctx.urlIframe), 1)
          ])), [
            [_directive_select_on_focus, {}]
          ]),
          vue.createTextVNode(" - "),
          vue.createElementVNode("a", {
            href: _ctx.urlIframe,
            rel: "noreferrer noopener",
            target: "_blank"
          }, vue.toDisplayString(_ctx.translate("Widgetize_OpenInNewWindow")), 9, _hoisted_8$1)
        ])
      ])
    ], 64);
  }
  const WidgetPreviewIframe = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const { $, widgetsHelper } = window;
  const _sfc_main$1 = vue.defineComponent({
    mounted() {
      const element = this.$refs.root;
      $(element).widgetPreview({
        onPreviewLoaded: (widgetUniqueId, loadedWidgetElement) => {
          this.callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement);
        }
      });
    },
    methods: {
      callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement) {
        widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, (widget) => {
          const widgetParameters = widget.parameters;
          const exportButtonsElement = $('<div id="exportButtons">');
          const urlIframe = this.getEmbedUrl(widgetParameters, "iframe");
          const widgetIframeHtml = `<div id="widgetIframe"><iframe width="100%" height="350" src="${urlIframe}" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>`;
          const previewIframe = $("<div>").attr("vue-entry", "Widgetize.WidgetPreviewIframe").attr("widget-iframe-html", JSON.stringify(widgetIframeHtml)).attr("url-iframe", JSON.stringify(urlIframe));
          $(exportButtonsElement).append(previewIframe);
          $(loadedWidgetElement).parent().append(exportButtonsElement);
          CoreHome.Matomo.helper.compileVueEntryComponents(exportButtonsElement);
        });
      },
      getEmbedUrl(parameters, exportFormat) {
        const finalParams = __spreadProps(__spreadValues({}, parameters), {
          moduleToWidgetize: parameters.module,
          actionToWidgetize: parameters.action,
          module: "Widgetize",
          action: exportFormat,
          idSite: CoreHome.Matomo.idSite,
          period: CoreHome.Matomo.period,
          date: CoreHome.MatomoUrl.urlParsed.value.date,
          disableLink: 1,
          widget: 1
        });
        const { protocol, hostname } = window.location;
        const port = window.location.port === "" ? "" : `:${window.location.port}`;
        const path = window.location.pathname;
        const query = CoreHome.MatomoUrl.stringify(finalParams);
        return `${protocol}//${hostname}${port}${path}?${query}`;
      }
    }
  });
  const _hoisted_1$1 = { ref: "root" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, null, 512);
  }
  const WidgetPreview = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  function getIframeCode(iframeUrl) {
    const url = iframeUrl.replace(/"/g, "&quot;");
    return `<iframe src="${url}" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>`;
  }
  const _sfc_main = vue.defineComponent({
    props: {
      title: {
        type: String,
        required: true
      }
    },
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      ContentBlock: CoreHome.ContentBlock,
      WidgetPreview
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro,
      SelectOnFocus: CoreHome.SelectOnFocus
    },
    data() {
      const port = window.location.port === "" ? "" : `:${window.location.port}`;
      const path = window.location.pathname;
      const urlPath = `${window.location.protocol}//${window.location.hostname}${port}${path}`;
      return {
        dashboardUrl: `${urlPath}?${CoreHome.MatomoUrl.stringify({
          module: "Widgetize",
          action: "iframe",
          moduleToWidgetize: "Dashboard",
          actionToWidgetize: "index",
          idSite: CoreHome.Matomo.idSite,
          period: "week",
          date: "yesterday"
        })}`,
        allWebsitesDashboardUrl: `${urlPath}?${CoreHome.MatomoUrl.stringify({
          module: "Widgetize",
          action: "iframe",
          moduleToWidgetize: "MultiSites",
          actionToWidgetize: "standalone",
          idSite: CoreHome.Matomo.idSite,
          period: "week",
          date: "yesterday"
        })}`
      };
    },
    computed: {
      dashboardCode() {
        return getIframeCode(this.dashboardUrl);
      },
      allWebsitesDashboardCode() {
        return getIframeCode(this.allWebsitesDashboardUrl);
      },
      intro() {
        return CoreHome.translate(
          "Widgetize_Intro",
          CoreHome.externalLink("https://matomo.org/docs/embed-piwik-report/"),
          "</a>"
        );
      },
      viewableAnonymously() {
        return CoreHome.translate(
          "Widgetize_ViewableAnonymously",
          `<a
          href="index.php?module=UsersManager"
          rel="noreferrer noopener"
          target="_blank"
        >`,
          "</a>",
          `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.linkTo({ module: "UsersManager", action: "userSecurity" })}"
        >`,
          "</a>"
        );
      },
      displayInIframe() {
        return CoreHome.translate(
          "Widgetize_DisplayDashboardInIframe",
          `<a
          rel="noreferrer noopener"
          target="_blank"
          href="${this.dashboardUrl}"
        >`,
          "</a>"
        );
      },
      displayInIframeAllSites() {
        return CoreHome.translate(
          "Widgetize_DisplayDashboardInIframeAllSites",
          `<a
          rel="noreferrer noopener"
          target="_blank"
          id="linkAllWebsitesDashboardUrl"
          href="${this.allWebsitesDashboardUrl}"
        >`,
          "</a>"
        );
      }
    },
    methods: {
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), params))}`;
      }
    }
  });
  const _hoisted_1 = { class: "widgetize" };
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = ["innerHTML"];
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_6 = ["textContent"];
  const _hoisted_7 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_10 = ["textContent"];
  const _hoisted_11 = /* @__PURE__ */ vue.createElementVNode("br", { class: "clearfix" }, null, -1);
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_WidgetPreview = vue.resolveComponent("WidgetPreview");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    const _directive_select_on_focus = vue.resolveDirective("select-on-focus");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, [
          vue.createVNode(_component_EnrichedHeadline, null, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.title), 1)
            ]),
            _: 1
          })
        ]),
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.intro)
        }, null, 8, _hoisted_2)
      ])), [
        [_directive_content_intro]
      ]),
      vue.createVNode(_component_ContentBlock, { "content-title": "Authentication" }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.viewableAnonymously)
          }, null, 8, _hoisted_3)
        ]),
        _: 1
      }),
      vue.createVNode(_component_ContentBlock, { "content-title": "Widgetize dashboards" }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("p", null, [
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.displayInIframe)
              }, null, 8, _hoisted_4),
              _hoisted_5
            ]),
            vue.withDirectives(vue.createElementVNode("pre", {
              textContent: vue.toDisplayString(_ctx.dashboardCode)
            }, null, 8, _hoisted_6), [
              [_directive_select_on_focus, {}]
            ]),
            vue.createElementVNode("p", null, [
              _hoisted_7,
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.displayInIframeAllSites)
              }, null, 8, _hoisted_8),
              _hoisted_9
            ]),
            vue.withDirectives(vue.createElementVNode("pre", {
              textContent: vue.toDisplayString(_ctx.allWebsitesDashboardCode)
            }, null, 8, _hoisted_10), [
              [_directive_select_on_focus, {}]
            ])
          ])
        ]),
        _: 1
      }),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("Widgetize_Reports")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Widgetize_SelectAReport")), 1),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_WidgetPreview)
            ]),
            _hoisted_11
          ])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const ExportWidget = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.ExportWidget = ExportWidget;
  exports2.WidgetPreview = WidgetPreview;
  exports2.WidgetPreviewIframe = WidgetPreviewIframe;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
