(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("CoreHome"), require("vue"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "CoreHome", "vue", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Transitions = {}, global.CoreHome, global.Vue, global.CorePluginsAdmin));
})(this, (function(exports2, CoreHome, vue, CorePluginsAdmin) {
  "use strict";
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const actionType = vue.ref("");
  const actionName = vue.ref("");
  const onDataChanged = (params) => {
    actionType.value = params.actionType;
    actionName.value = params.actionName;
  };
  CoreHome.Matomo.on("Transitions.dataChanged", onDataChanged);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      exportFormatOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field
    },
    data() {
      return {
        exportFormat: "JSON"
      };
    },
    computed: {
      exportLink() {
        const exportUrlParams = {
          module: "API"
        };
        exportUrlParams.method = "Transitions.getTransitionsForAction";
        exportUrlParams.actionType = actionType.value;
        exportUrlParams.actionName = actionName.value;
        exportUrlParams.idSite = CoreHome.Matomo.idSite;
        exportUrlParams.period = CoreHome.Matomo.period;
        exportUrlParams.date = CoreHome.Matomo.currentDateString;
        exportUrlParams.format = this.exportFormat;
        exportUrlParams.token_auth = CoreHome.Matomo.token_auth;
        exportUrlParams.force_api_session = 1;
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split("/");
        urlParts.pop();
        const url = urlParts.join("/");
        return `${url}/index.php?${CoreHome.MatomoUrl.stringify(exportUrlParams)}`;
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
  const _hoisted_1$2 = { class: "transition-export-popover row" };
  const _hoisted_2$1 = { class: "col l6" };
  const _hoisted_3$1 = { class: "input-field" };
  const _hoisted_4$1 = { class: "matomo-field" };
  const _hoisted_5$1 = { class: "col l12" };
  const _hoisted_6$1 = ["href"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createElementVNode("div", _hoisted_2$1, [
        vue.createElementVNode("div", _hoisted_3$1, [
          vue.createElementVNode("div", _hoisted_4$1, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: "exportFormat",
              title: _ctx.translate("CoreHome_ExportFormat"),
              "model-value": _ctx.exportFormat,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.exportFormat = $event),
              "full-width": true,
              options: _ctx.exportFormatOptions
            }, null, 8, ["title", "model-value", "options"])
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_5$1, [
        vue.createElementVNode("a", {
          class: "btn",
          href: _ctx.exportLink,
          target: "_new",
          title: "translate('CoreHome_ExportTooltip')"
        }, vue.toDisplayString(_ctx.translate("General_Export")), 9, _hoisted_6$1)
      ])
    ]);
  }
  const TransitionExporterPopover = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { Piwik_Popover } = window;
  const TransitionExporter = {
    mounted(element) {
      element.addEventListener("click", (e) => {
        e.preventDefault();
        const props = {
          exportFormat: "JSON",
          exportFormatOptions: [
            { key: "JSON", value: "JSON" },
            { key: "XML", value: "XML" }
          ]
        };
        const app = CoreHome.createVueApp({
          template: '<popover v-bind="bind"/>',
          data() {
            return {
              bind: props
            };
          }
        });
        app.component("popover", TransitionExporterPopover);
        const mountPoint = document.createElement("div");
        app.mount(mountPoint);
        Piwik_Popover.showLoading("");
        Piwik_Popover.setTitle(
          `${CoreHome.Matomo.helper.htmlEntities(actionName.value)} ${CoreHome.translate("Transitions_Transitions")}`
        );
        Piwik_Popover.setContent(mountPoint);
        Piwik_Popover.onClose(() => {
          app.unmount();
        });
      });
    }
  };
  const _sfc_main$2 = vue.defineComponent({
    props: {
      isWidget: Boolean
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      Field: CorePluginsAdmin.Field,
      MatomoLoader: CoreHome.MatomoLoader
    },
    directives: {
      TransitionExporter
    },
    data() {
      return {
        actionType: "Actions.getPageUrls",
        actionNameOptions: [],
        actionTypeOptions: [
          {
            key: "Actions.getPageUrls",
            value: CoreHome.translate("Actions_PageUrls")
          },
          {
            key: "Actions.getPageTitles",
            value: CoreHome.translate("Actions_WidgetPageTitles")
          }
        ],
        isLoading: false,
        actionName: null,
        isEnabled: true,
        noDataKey: "_____ignore_____"
      };
    },
    setup() {
      let transitionsInstance = null;
      const transitionsUrl = vue.ref();
      const onSwitchTransitionsUrl = (params) => {
        if (params == null ? void 0 : params.url) {
          transitionsUrl.value = params.url;
        }
      };
      CoreHome.Matomo.on("Transitions.switchTransitionsUrl", onSwitchTransitionsUrl);
      vue.onBeforeUnmount(() => {
        CoreHome.Matomo.off("Transitions.switchTransitionsUrl", onSwitchTransitionsUrl);
      });
      const createTransitionsInstance = (type, actionName2) => {
        if (!transitionsInstance) {
          transitionsInstance = new window.Piwik_Transitions(type, actionName2, null, "");
        } else {
          transitionsInstance.reset(type, actionName2, "");
        }
      };
      const getTransitionsInstance = () => transitionsInstance;
      return {
        transitionsUrl,
        createTransitionsInstance,
        getTransitionsInstance
      };
    },
    watch: {
      transitionsUrl(newValue) {
        let url = newValue;
        if (this.isUrlReport) {
          url = url.replace("https://", "").replace("http://", "");
        }
        const found = this.actionNameOptions.find((option) => {
          let optionUrl = option.url;
          if (optionUrl && this.isUrlReport) {
            optionUrl = String(optionUrl).replace("https://", "").replace("http://", "");
          } else {
            optionUrl = void 0;
          }
          return option.key === url || url === optionUrl && optionUrl;
        });
        if (found) {
          this.actionName = found.key;
        } else {
          this.actionNameOptions = [
            ...this.actionNameOptions,
            { key: url, value: url }
          ];
          this.actionName = url;
        }
      },
      actionName(newValue) {
        if (newValue === null || newValue === this.noDataKey) {
          return;
        }
        const type = this.isUrlReport ? "url" : "title";
        this.createTransitionsInstance(type, newValue);
        this.getTransitionsInstance().showPopover(true);
      },
      actionType(newValue) {
        this.fetch(newValue);
      }
    },
    created() {
      this.fetch(this.actionType);
    },
    methods: {
      detectActionName(reports) {
        const othersLabel = CoreHome.translate("General_Others");
        reports.forEach((report) => {
          if (!report) {
            return;
          }
          if (report.label === othersLabel) {
            return;
          }
          const key = this.isUrlReport ? report.url : report.label;
          if (key) {
            const pageviews = CoreHome.translate("Transitions_NumPageviews", report.nb_hits);
            const label = `${report.label} (${pageviews})`;
            this.actionNameOptions.push({
              key,
              value: label,
              url: report.url
            });
            if (!this.actionName) {
              this.actionName = key;
            }
          }
        });
      },
      fetch(type) {
        this.isLoading = true;
        this.actionNameOptions = [];
        this.actionName = null;
        CoreHome.AjaxHelper.fetch({
          method: type,
          flat: 1,
          filter_limit: 100,
          filter_sort_order: "desc",
          filter_sort_column: "nb_hits",
          showColumns: "label,nb_hits,url"
        }).then((report) => {
          this.isLoading = false;
          this.actionNameOptions = [];
          this.actionName = null;
          if (report == null ? void 0 : report.length) {
            this.isEnabled = true;
            this.detectActionName(report);
          }
          if (this.actionName === null || this.actionNameOptions.length === 0) {
            this.isEnabled = false;
            this.actionName = this.noDataKey;
            this.actionNameOptions.push({
              key: this.noDataKey,
              value: CoreHome.translate("CoreHome_ThereIsNoDataForThisReport")
            });
          }
        }).catch(() => {
          this.isLoading = false;
          this.isEnabled = false;
        });
      }
    },
    computed: {
      isUrlReport() {
        return this.actionType === "Actions.getPageUrls";
      },
      availableInOtherReports2() {
        return CoreHome.translate("Transitions_AvailableInOtherReports2", '<span class="icon-transition"></span>');
      }
    }
  });
  const _hoisted_1$1 = { class: "row" };
  const _hoisted_2 = { class: "col s12 m3" };
  const _hoisted_3 = { name: "actionType" };
  const _hoisted_4 = { class: "col s12 m9" };
  const _hoisted_5 = { name: "actionName" };
  const _hoisted_6 = {
    class: "loadingPiwik",
    style: { "display": "none" },
    id: "transitions_inline_loading"
  };
  const _hoisted_7 = { class: "popoverContainer" };
  const _hoisted_8 = { id: "Transitions_Error_Container" };
  const _hoisted_9 = { class: "dataTableWrapper" };
  const _hoisted_10 = { class: "dataTableFeatures" };
  const _hoisted_11 = { class: "dataTableFooterNavigation" };
  const _hoisted_12 = { class: "dataTableControls" };
  const _hoisted_13 = { class: "row" };
  const _hoisted_14 = { class: "dataTableAction" };
  const _hoisted_15 = { class: "alert alert-info" };
  const _hoisted_16 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _directive_transition_exporter = vue.resolveDirective("transition-exporter");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass({ widgetBody: _ctx.isWidget }),
      id: "transitions_report"
    }, [
      vue.createElementVNode("div", _hoisted_1$1, [
        vue.createElementVNode("div", _hoisted_2, [
          vue.createElementVNode("div", _hoisted_3, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "actionType",
              modelValue: _ctx.actionType,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.actionType = $event),
              title: _ctx.translate("Actions_ActionType"),
              "full-width": true,
              options: _ctx.actionTypeOptions
            }, null, 8, ["modelValue", "title", "options"])
          ])
        ]),
        vue.createElementVNode("div", _hoisted_4, [
          vue.createElementVNode("div", _hoisted_5, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "actionName",
              modelValue: _ctx.actionName,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.actionName = $event),
              title: _ctx.translate("Transitions_TopX", "100"),
              "full-width": true,
              disabled: !_ctx.isEnabled,
              options: _ctx.actionNameOptions
            }, null, 8, ["modelValue", "title", "disabled", "options"])
          ])
        ])
      ]),
      vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoading }, null, 8, ["loading"]),
      vue.createElementVNode("div", _hoisted_6, [
        vue.createVNode(_component_MatomoLoader),
        vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_7, null, 512), [
        [vue.vShow, !_ctx.isLoading && _ctx.isEnabled]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_8, null, 512), [
        [vue.vShow, !_ctx.isLoading]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_9, [
        vue.createElementVNode("div", _hoisted_10, [
          vue.createElementVNode("div", _hoisted_11, [
            vue.createElementVNode("div", _hoisted_12, [
              vue.createElementVNode("div", _hoisted_13, [
                vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_14, [..._cache[2] || (_cache[2] = [
                  vue.createElementVNode("span", { class: "icon-export" }, null, -1)
                ])])), [
                  [_directive_transition_exporter]
                ])
              ])
            ])
          ])
        ])
      ], 512), [
        [vue.vShow, _ctx.isEnabled]
      ]),
      vue.createElementVNode("div", _hoisted_15, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Transitions_AvailableInOtherReports")) + " " + vue.toDisplayString(_ctx.translate("Actions_PageUrls")) + ", " + vue.toDisplayString(_ctx.translate("Actions_SubmenuPageTitles")) + ", " + vue.toDisplayString(_ctx.translate("Actions_SubmenuPagesEntry")) + " " + vue.toDisplayString(_ctx.translate("General_And")) + " " + vue.toDisplayString(_ctx.translate("Actions_SubmenuPagesExit")) + ". ", 1),
        vue.createElementVNode("span", {
          innerHTML: _ctx.$sanitize(_ctx.availableInOtherReports2)
        }, null, 8, _hoisted_16)
      ])
    ], 2);
  }
  const TransitionSwitcher = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      isWidget: Boolean
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      TransitionSwitcher
    }
  });
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TransitionSwitcher = vue.resolveComponent("TransitionSwitcher");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return !_ctx.isWidget ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      key: 0,
      "help-text": _ctx.translate("Transitions_FeatureDescription"),
      "help-url": _ctx.externalRawLink("https://matomo.org/docs/transitions/"),
      "content-title": _ctx.translate("Transitions_Transitions")
    }, {
      default: vue.withCtx(() => [
        vue.createVNode(_component_TransitionSwitcher, { "is-widget": _ctx.isWidget }, null, 8, ["is-widget"])
      ]),
      _: 1
    }, 8, ["help-text", "help-url", "content-title"])) : (vue.openBlock(), vue.createBlock(_component_TransitionSwitcher, {
      key: 1,
      "is-widget": _ctx.isWidget
    }, null, 8, ["is-widget"]));
  }
  const TransitionsPage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    directives: {
      TransitionExporter
    }
  });
  const _hoisted_1 = { class: "dataTableAction" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_transition_exporter = vue.resolveDirective("transition-exporter");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_1, [..._cache[0] || (_cache[0] = [
      vue.createElementVNode("span", { class: "icon-export" }, null, -1)
    ])])), [
      [_directive_transition_exporter]
    ]);
  }
  const TransitionExporterLink = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.TransitionExporter = TransitionExporter;
  exports2.TransitionExporterLink = TransitionExporterLink;
  exports2.TransitionSwitcher = TransitionSwitcher;
  exports2.TransitionsPage = TransitionsPage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
