(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Installation = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main$4 = vue.defineComponent({
    props: {
      errorType: {
        type: String,
        required: true
      },
      warningType: {
        type: String,
        required: true
      },
      informationalType: {
        type: String,
        required: true
      },
      results: {
        type: Array,
        required: true
      }
    },
    components: {
      Passthrough: CoreHome.Passthrough
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$4 = ["innerHTML"];
  const _hoisted_2$4 = { key: 0 };
  const _hoisted_3$4 = ["innerHTML"];
  const _hoisted_4$3 = { key: 1 };
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = { key: 2 };
  const _hoisted_7 = ["innerHTML"];
  const _hoisted_8 = { key: 3 };
  const _hoisted_9 = ["innerHTML"];
  const _hoisted_10 = { key: 0 };
  const _hoisted_11 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Passthrough = vue.resolveComponent("Passthrough");
    return vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.results, (result, index) => {
      return vue.openBlock(), vue.createBlock(_component_Passthrough, { key: index }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("tr", null, [
            vue.createElementVNode("td", {
              innerHTML: _ctx.$sanitize(result.label || "")
            }, null, 8, _hoisted_1$4),
            vue.createElementVNode("td", null, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(result.items, (item, index2) => {
                return vue.openBlock(), vue.createElementBlock("span", { key: index2 }, [
                  item.status === "error" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$4, [
                    _cache[0] || (_cache[0] = vue.createElementVNode("span", { class: "icon-error" }, null, -1)),
                    vue.createElementVNode("span", {
                      class: "err",
                      innerHTML: _ctx.$sanitize(typeof item.comment !== "string" ? "" : item.comment)
                    }, null, 8, _hoisted_3$4)
                  ])) : item.status === "warning" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$3, [
                    _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-warning" }, null, -1)),
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(typeof item.comment !== "string" ? "" : item.comment)
                    }, null, 8, _hoisted_5)
                  ])) : item.status === "informational" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_6, [
                    _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-info" }, null, -1)),
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(typeof item.comment !== "string" ? "" : item.comment)
                    }, null, 8, _hoisted_7)
                  ])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_8, [
                    _cache[3] || (_cache[3] = vue.createElementVNode("span", { class: "icon-ok" }, null, -1)),
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(typeof item.comment !== "string" ? "" : item.comment)
                    }, null, 8, _hoisted_9)
                  ])),
                  _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1))
                ]);
              }), 128))
            ])
          ]),
          result.longErrorMessage ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_10, [
            vue.createElementVNode("td", {
              colspan: "2",
              class: "error",
              style: { "font-size": "small" },
              innerHTML: _ctx.$sanitize(result.longErrorMessage)
            }, null, 8, _hoisted_11)
          ])) : vue.createCommentVNode("", true)
        ]),
        _: 2
      }, 1024);
    }), 128);
  }
  const DiagnosticTable = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const { $: $$1 } = window;
  const _sfc_main$3 = vue.defineComponent({
    props: {
      errorType: {
        type: String,
        required: true
      },
      warningType: {
        type: String,
        required: true
      },
      informationalType: {
        type: String,
        required: true
      },
      systemCheckInfo: {
        type: String,
        required: true
      },
      mandatoryResults: {
        type: Array,
        required: true
      },
      optionalResults: {
        type: Array,
        required: true
      },
      informationalResults: {
        type: Array,
        required: true
      },
      isInstallation: Boolean
    },
    components: {
      DiagnosticTable
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    methods: {
      copyInfo() {
        const textarea = this.$refs.systemCheckInfo;
        textarea.select();
        document.execCommand("copy");
        $$1(textarea).effect("highlight", {}, 600);
      },
      downloadInfo() {
        const textarea = this.$refs.systemCheckInfo;
        CoreHome.Matomo.helper.sendContentAsDownload("matomo_system_check.txt", textarea.innerHTML);
      }
    }
  });
  const _hoisted_1$3 = ["innerHTML"];
  const _hoisted_2$3 = {
    class: "entityTable system-check",
    id: "systemCheckRequired"
  };
  const _hoisted_3$3 = {
    class: "entityTable system-check",
    id: "systemCheckOptional"
  };
  const _hoisted_4$2 = {
    class: "entityTable system-check",
    id: "systemCheckInformational"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DiagnosticTable = vue.resolveComponent("DiagnosticTable");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("p", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Installation_CopyBelowInfoForSupport")) + " ", 1),
        _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
        _cache[3] || (_cache[3] = vue.createTextVNode()),
        _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
        vue.createElementVNode("a", {
          href: "",
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.copyInfo(), ["prevent"])),
          class: "btn",
          style: { "margin-right": "3.5px" }
        }, vue.toDisplayString(_ctx.translate("Installation_CopySystemCheck")), 1),
        vue.createElementVNode("a", {
          href: "",
          onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.downloadInfo(), ["prevent"])),
          class: "btn"
        }, vue.toDisplayString(_ctx.translate("Installation_DownloadSystemCheck")), 1)
      ]),
      vue.createElementVNode("div", null, [
        vue.createElementVNode("textarea", {
          style: { "width": "100%", "height": "200px" },
          readonly: "",
          id: "matomo_system_check_info",
          ref: "systemCheckInfo",
          innerHTML: _ctx.$sanitize(_ctx.systemCheckInfo)
        }, null, 8, _hoisted_1$3),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_2$3, [
          vue.createElementVNode("tbody", null, [
            vue.createVNode(_component_DiagnosticTable, {
              results: _ctx.mandatoryResults,
              "informational-type": _ctx.informationalType,
              "warning-type": _ctx.warningType,
              "error-type": _ctx.errorType
            }, null, 8, ["results", "informational-type", "warning-type", "error-type"])
          ])
        ])), [
          [_directive_content_table, { off: _ctx.isInstallation }]
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Installation_Optional")), 1),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_3$3, [
          vue.createElementVNode("tbody", null, [
            vue.createVNode(_component_DiagnosticTable, {
              results: _ctx.optionalResults,
              "informational-type": _ctx.informationalType,
              "warning-type": _ctx.warningType,
              "error-type": _ctx.errorType
            }, null, 8, ["results", "informational-type", "warning-type", "error-type"])
          ])
        ])), [
          [_directive_content_table, { off: _ctx.isInstallation }]
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Installation_InformationalResults")), 1),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_4$2, [
          vue.createElementVNode("tbody", null, [
            vue.createVNode(_component_DiagnosticTable, {
              results: _ctx.informationalResults,
              "informational-type": _ctx.informationalType,
              "warning-type": _ctx.warningType,
              "error-type": _ctx.errorType
            }, null, 8, ["results", "informational-type", "warning-type", "error-type"])
          ])
        ])), [
          [_directive_content_table, { off: _ctx.isInstallation }]
        ])
      ])
    ], 64);
  }
  const SystemCheckSection = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      errorType: {
        type: String,
        required: true
      },
      warningType: {
        type: String,
        required: true
      },
      informationalType: {
        type: String,
        required: true
      },
      systemCheckInfo: {
        type: String,
        required: true
      },
      mandatoryResults: {
        type: Array,
        required: true
      },
      optionalResults: {
        type: Array,
        required: true
      },
      informationalResults: {
        type: Array,
        required: true
      },
      isInstallation: Boolean,
      hasErrors: Boolean,
      hasWarnings: Boolean
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SystemCheckSection
    },
    computed: {
      thereWereErrorsText() {
        return CoreHome.translate(
          "Installation_SystemCheckSummaryThereWereErrors",
          "<strong>",
          "</strong>",
          "<strong>",
          "</strong>"
        );
      }
    }
  });
  const _hoisted_1$2 = {
    key: 0,
    class: "alert alert-danger"
  };
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = {
    key: 1,
    class: "alert alert-warning"
  };
  const _hoisted_4$1 = {
    key: 2,
    class: "alert alert-success"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SystemCheckSection = vue.resolveComponent("SystemCheckSection");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("Installation_SystemCheck"),
      feature: "true"
    }, {
      default: vue.withCtx(() => [
        _ctx.hasErrors ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.thereWereErrorsText)
          }, null, 8, _hoisted_2$2),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Installation_SeeBelowForMoreInfo")), 1)
        ])) : _ctx.hasWarnings ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$2, vue.toDisplayString(_ctx.translate("Installation_SystemCheckSummaryThereWereWarnings")) + " " + vue.toDisplayString(_ctx.translate("Installation_SeeBelowForMoreInfo")), 1)) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$1, vue.toDisplayString(_ctx.translate("Installation_SystemCheckSummaryNoProblems")), 1)),
        vue.createVNode(_component_SystemCheckSection, {
          "error-type": _ctx.errorType,
          "warning-type": _ctx.warningType,
          "informational-type": _ctx.informationalType,
          "system-check-info": _ctx.systemCheckInfo,
          "mandatory-results": _ctx.mandatoryResults,
          "optional-results": _ctx.optionalResults,
          "informational-results": _ctx.informationalResults,
          "is-installation": _ctx.isInstallation
        }, null, 8, ["error-type", "warning-type", "informational-type", "system-check-info", "mandatory-results", "optional-results", "informational-results", "is-installation"])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const SystemCheckPage = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      url: {
        type: String,
        required: true
      }
    }
  });
  const _hoisted_1$1 = { class: "system-check-legend" };
  const _hoisted_2$1 = { class: "next-step" };
  const _hoisted_3$1 = ["href"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1$1, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Installation_Legend")), 1),
        vue.createElementVNode("p", null, [
          _cache[0] || (_cache[0] = vue.createElementVNode("span", { class: "icon-ok" }, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Ok")), 1)
        ]),
        vue.createElementVNode("p", null, [
          _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-warning" }, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Warning")) + ": " + vue.toDisplayString(_ctx.translate("Installation_SystemCheckWarning")), 1)
        ]),
        vue.createElementVNode("p", null, [
          _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-error" }, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Error")) + ": " + vue.toDisplayString(_ctx.translate("Installation_SystemCheckError")), 1)
        ])
      ]),
      vue.createElementVNode("p", _hoisted_2$1, [
        vue.createElementVNode("a", { href: _ctx.url }, vue.toDisplayString(_ctx.translate("General_RefreshPage")) + " »", 9, _hoisted_3$1)
      ])
    ], 64);
  }
  const SystemCheckLegend = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const { $ } = window;
  const _sfc_main = vue.defineComponent({
    props: {
      showNextStep: Boolean,
      systemCheckLegendUrl: {
        type: String,
        required: true
      },
      errorType: {
        type: String,
        required: true
      },
      warningType: {
        type: String,
        required: true
      },
      informationalType: {
        type: String,
        required: true
      },
      systemCheckInfo: {
        type: String,
        required: true
      },
      mandatoryResults: {
        type: Array,
        required: true
      },
      optionalResults: {
        type: Array,
        required: true
      },
      informationalResults: {
        type: Array,
        required: true
      },
      isInstallation: Boolean
    },
    components: {
      SystemCheckSection,
      SystemCheckLegend
    },
    mounted() {
      if (document.location.protocol === "https:") {
        const link = $("p.next-step a");
        link.attr("href", `${link.attr("href")}&clientProtocol=https`);
      }
    }
  });
  const _hoisted_1 = { key: 0 };
  const _hoisted_2 = { key: 1 };
  const _hoisted_3 = { key: 0 };
  const _hoisted_4 = ["href"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SystemCheckLegend = vue.resolveComponent("SystemCheckLegend");
    const _component_SystemCheckSection = vue.resolveComponent("SystemCheckSection");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      !_ctx.showNextStep ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
        vue.createVNode(_component_SystemCheckLegend, { url: _ctx.systemCheckLegendUrl }, null, 8, ["url"]),
        _cache[0] || (_cache[0] = vue.createElementVNode("br", { style: { "clear": "both" } }, null, -1))
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Installation_SystemCheck")), 1),
      vue.createVNode(_component_SystemCheckSection, {
        "error-type": _ctx.errorType,
        "warning-type": _ctx.warningType,
        "informational-type": _ctx.informationalType,
        "system-check-info": _ctx.systemCheckInfo,
        "mandatory-results": _ctx.mandatoryResults,
        "optional-results": _ctx.optionalResults,
        "informational-results": _ctx.informationalResults,
        "is-installation": _ctx.isInstallation
      }, null, 8, ["error-type", "warning-type", "informational-type", "system-check-info", "mandatory-results", "optional-results", "informational-results", "is-installation"]),
      !_ctx.showNextStep ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
        !_ctx.showNextStep ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_3, [
          _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-export" }, null, -1)),
          vue.createElementVNode("a", {
            target: "_blank",
            rel: "noreferrer noopener",
            href: _ctx.externalRawLink("https://matomo.org/docs/requirements/")
          }, vue.toDisplayString(_ctx.translate("Installation_Requirements")), 9, _hoisted_4)
        ])) : vue.createCommentVNode("", true),
        vue.createVNode(_component_SystemCheckLegend, { url: _ctx.systemCheckLegendUrl }, null, 8, ["url"])
      ])) : vue.createCommentVNode("", true)
    ]);
  }
  const SystemCheck = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.SystemCheck = SystemCheck;
  exports2.SystemCheckPage = SystemCheckPage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
