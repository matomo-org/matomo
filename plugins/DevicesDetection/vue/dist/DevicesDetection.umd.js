(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.DevicesDetection = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";var __defProp = Object.defineProperty;
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

  function isClientHintsSupported() {
    const nav = navigator;
    return nav.userAgentData && typeof nav.userAgentData.getHighEntropyValues === "function";
  }
  let clientHints = null;
  function getDefaultClientHints() {
    const nav = navigator;
    if (!isClientHintsSupported()) {
      return Promise.resolve(null);
    }
    if (clientHints) {
      return Promise.resolve(clientHints);
    }
    clientHints = {
      brands: nav.userAgentData.brands,
      platform: nav.userAgentData.platform
    };
    return nav.userAgentData.getHighEntropyValues(
      ["brands", "model", "platform", "platformVersion", "uaFullVersion", "fullVersionList"]
    ).then((ua) => {
      clientHints = __spreadValues({}, ua);
      if (clientHints.fullVersionList) {
        delete clientHints.brands;
        delete clientHints.uaFullVersion;
      }
      return clientHints;
    });
  }
  const _sfc_main = vue.defineComponent({
    props: {
      userAgent: {
        type: String,
        required: true
      },
      bot_info: Object,
      os_logo: String,
      os_name: String,
      os_version: String,
      os_family_logo: String,
      os_family: String,
      browser_logo: String,
      browser_name: String,
      browser_version: String,
      browser_family: String,
      browser_family_logo: String,
      device_type_logo: String,
      device_type: String,
      device_brand_logo: String,
      device_brand: String,
      device_model: String,
      clientHintsChecked: Boolean
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    created() {
      getDefaultClientHints().then((hints) => {
        this.defaultClientHints = hints;
        this.toggleClientHints();
      });
    },
    data() {
      return {
        itemListHtml: "",
        considerClientHints: !!this.clientHintsChecked,
        clientHintsText: "",
        userAgentText: this.userAgent,
        defaultClientHints: null
      };
    },
    methods: {
      showList(type) {
        CoreHome.AjaxHelper.fetch(
          {
            module: "DevicesDetection",
            action: "showList",
            type
          },
          {
            format: "html"
          }
        ).then((response) => {
          this.itemListHtml = response;
          CoreHome.Matomo.helper.modalConfirm(
            this.$refs.deviceDetectionItemList,
            void 0,
            { fixedFooter: true }
          );
        });
      },
      toggleClientHints() {
        if (this.considerClientHints && this.defaultClientHints !== null) {
          this.clientHintsText = this.clientHintsText || JSON.stringify(this.defaultClientHints);
        } else {
          this.clientHintsText = "";
        }
      }
    },
    computed: {
      isClientHintsSupported() {
        return isClientHintsSupported();
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
  const _hoisted_1 = { class: "detectionPage" };
  const _hoisted_2 = {
    action: "",
    method: "POST"
  };
  const _hoisted_3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_4 = {
    key: 0,
    class: "checkbox-container usech"
  };
  const _hoisted_5 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7 = ["value"];
  const _hoisted_8 = { key: 0 };
  const _hoisted_9 = { key: 1 };
  const _hoisted_10 = { class: "detection" };
  const _hoisted_11 = ["src"];
  const _hoisted_12 = ["src"];
  const _hoisted_13 = { class: "detection" };
  const _hoisted_14 = ["src"];
  const _hoisted_15 = ["src"];
  const _hoisted_16 = { class: "detection" };
  const _hoisted_17 = ["src"];
  const _hoisted_18 = ["src"];
  const _hoisted_19 = {
    class: "ui-confirm",
    id: "deviceDetectionItemList",
    ref: "deviceDetectionItemList"
  };
  const _hoisted_20 = ["innerHTML"];
  const _hoisted_21 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("DevicesDetection_DeviceDetection")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("form", _hoisted_2, [
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("DevicesDetection_UserAgent")), 1),
            vue.withDirectives(vue.createElementVNode("textarea", {
              name: "ua",
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.userAgentText = $event)
            }, null, 512), [
              [vue.vModelText, _ctx.userAgentText]
            ]),
            _hoisted_3,
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("DevicesDetection_ClientHints")), 1),
            _ctx.isClientHintsSupported ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4, [
              vue.createElementVNode("label", null, [
                vue.withDirectives(vue.createElementVNode("input", {
                  type: "checkbox",
                  id: "usech",
                  "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.considerClientHints = $event),
                  onChange: _cache[2] || (_cache[2] = ($event) => _ctx.toggleClientHints())
                }, null, 544), [
                  [vue.vModelCheckbox, _ctx.considerClientHints]
                ]),
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("DevicesDetection_ConsiderClientHints")), 1)
              ])
            ])) : vue.createCommentVNode("", true),
            _ctx.isClientHintsSupported && _ctx.considerClientHints ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("textarea", {
              key: 1,
              name: "clienthints",
              style: { "margin-top": "2em" },
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.clientHintsText = $event)
            }, null, 512)), [
              [vue.vModelText, _ctx.clientHintsText]
            ]) : vue.createCommentVNode("", true),
            vue.withDirectives(vue.createElementVNode("span", {
              id: "noclienthints",
              class: "alert alert-warning"
            }, vue.toDisplayString(_ctx.translate("DevicesDetection_ClientHintsNotSupported")), 513), [
              [vue.vShow, !_ctx.isClientHintsSupported]
            ]),
            _hoisted_5,
            _hoisted_6,
            vue.createElementVNode("input", {
              type: "submit",
              value: _ctx.translate("General_Refresh"),
              class: "btn"
            }, null, 8, _hoisted_7)
          ]),
          _ctx.bot_info ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_8, vue.toDisplayString(_ctx.translate("DevicesDetection_BotDetected", _ctx.bot_info.name)), 1)) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_9, [
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("DevicesDetection_ColumnOperatingSystem")), 1),
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_10, [
              vue.createElementVNode("tbody", null, [
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Name")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[4] || (_cache[4] = vue.withModifiers(($event) => _ctx.showList("os"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.os_logo
                    }, null, 8, _hoisted_11),
                    vue.createTextVNode(vue.toDisplayString(_ctx.os_name), 1)
                  ])
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Version")), 1),
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.os_version), 1)
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("DevicesDetection_OperatingSystemFamily")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[5] || (_cache[5] = vue.withModifiers(($event) => _ctx.showList("osfamilies"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.os_family_logo
                    }, null, 8, _hoisted_12),
                    vue.createTextVNode(vue.toDisplayString(_ctx.os_family), 1)
                  ])
                ])
              ])
            ])), [
              [_directive_content_table]
            ]),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("DevicesDetection_ColumnBrowser")), 1),
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_13, [
              vue.createElementVNode("tbody", null, [
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Name")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[6] || (_cache[6] = vue.withModifiers(($event) => _ctx.showList("browsers"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.browser_logo
                    }, null, 8, _hoisted_14),
                    vue.createTextVNode(vue.toDisplayString(_ctx.browser_name), 1)
                  ])
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Version")), 1),
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.browser_version), 1)
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("DevicesDetection_BrowserFamily")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[7] || (_cache[7] = vue.withModifiers(($event) => _ctx.showList("browserfamilies"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.browser_family_logo
                    }, null, 8, _hoisted_15),
                    vue.createTextVNode(vue.toDisplayString(_ctx.browser_family), 1)
                  ])
                ])
              ])
            ])), [
              [_directive_content_table]
            ]),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("DevicesDetection_Device")), 1),
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_16, [
              vue.createElementVNode("tbody", null, [
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("DevicesDetection_dataTableLabelTypes")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[8] || (_cache[8] = vue.withModifiers(($event) => _ctx.showList("devicetypes"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.device_type_logo
                    }, null, 8, _hoisted_17),
                    vue.createTextVNode(vue.toDisplayString(_ctx.device_type), 1)
                  ])
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("DevicesDetection_dataTableLabelBrands")) + " ", 1),
                    vue.createElementVNode("small", null, [
                      vue.createTextVNode(" ("),
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: _cache[9] || (_cache[9] = vue.withModifiers(($event) => _ctx.showList("brands"), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("Mobile_ShowAll")), 1),
                      vue.createTextVNode(") ")
                    ])
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("img", {
                      height: 16,
                      width: 16,
                      src: _ctx.device_brand_logo
                    }, null, 8, _hoisted_18),
                    vue.createTextVNode(vue.toDisplayString(_ctx.device_brand), 1)
                  ])
                ]),
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.translate("DevicesDetection_dataTableLabelModels")), 1),
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.device_model), 1)
                ])
              ])
            ])), [
              [_directive_content_table]
            ])
          ]))
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createElementVNode("div", _hoisted_19, [
        vue.createElementVNode("div", {
          class: "itemList",
          innerHTML: _ctx.$sanitize(_ctx.itemListHtml)
        }, null, 8, _hoisted_20),
        vue.createElementVNode("input", {
          role: "close",
          type: "button",
          value: _ctx.translate("General_Close")
        }, null, 8, _hoisted_21)
      ], 512)
    ]);
  }
  const DetectionPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.DetectionPage = DetectionPage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
