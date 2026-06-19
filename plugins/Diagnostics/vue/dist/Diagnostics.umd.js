(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Diagnostics = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main = vue.defineComponent({
    props: {
      allConfigValues: {
        type: Object,
        required: true
      },
      configFilePath: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Passthrough: CoreHome.Passthrough
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        hideGlobalConfigValues: false
      };
    },
    methods: {
      humanReadableValue(value) {
        if (value === false) {
          return "false";
        }
        if (value === true) {
          return "true";
        }
        if (value === null) {
          return "";
        }
        if (value === "") {
          return "''";
        }
        if (typeof value === "object" && Object.keys(value).length === 0) {
          return "[]";
        }
        if (typeof value === "object" && Object.keys(value).length > 0) {
          return `<div class="pre">${JSON.stringify(value, null, 4)}</div>`;
        }
        return CoreHome.Matomo.helper.htmlEntities(`${value}`);
      },
      onHideUnchanged(event) {
        if (event.target.tagName !== "A") {
          return;
        }
        this.hideGlobalConfigValues = !this.hideGlobalConfigValues;
      }
    },
    computed: {
      configFileIntro() {
        return CoreHome.translate(
          "Diagnostics_ConfigFileIntroduction",
          `<code>"${CoreHome.Matomo.helper.htmlEntities(this.configFilePath)}"</code>`
        );
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
  const _hoisted_1 = ["innerHTML"];
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = ["href"];
  const _hoisted_4 = { class: "diagnostics configfile" };
  const _hoisted_5 = { colspan: "3" };
  const _hoisted_6 = ["name"];
  const _hoisted_7 = { class: "name" };
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = { class: "description" };
  const _hoisted_10 = ["innerHTML"];
  const _hoisted_11 = { key: 0 };
  const _hoisted_12 = { key: 0 };
  const _hoisted_13 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Passthrough = vue.resolveComponent("Passthrough");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("Diagnostics_ConfigFileTitle"),
      feature: "true"
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, [
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.configFileIntro),
            style: { "margin-right": "3.5px" }
          }, null, 8, _hoisted_1),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.translate("Diagnostics_HideUnchanged", "<a>", "</a>")),
            onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onHideUnchanged($event))
          }, null, 8, _hoisted_2)
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Diagnostics_Sections")), 1),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.allConfigValues, (values, category) => {
          return vue.openBlock(), vue.createBlock(_component_Passthrough, { key: category }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("a", {
                href: `#${category}`
              }, vue.toDisplayString(category), 9, _hoisted_3),
              _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1))
            ]),
            _: 2
          }, 1024);
        }), 128)),
        _cache[2] || (_cache[2] = vue.createElementVNode("p", null, null, -1)),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_4, [
          vue.createElementVNode("tbody", null, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.allConfigValues, (configValues, category) => {
              return vue.openBlock(), vue.createBlock(_component_Passthrough, { key: category }, {
                default: vue.withCtx(() => [
                  vue.createElementVNode("tr", null, [
                    vue.createElementVNode("td", _hoisted_5, [
                      vue.createElementVNode("a", { name: category }, null, 8, _hoisted_6),
                      vue.createElementVNode("h3", null, vue.toDisplayString(category), 1)
                    ])
                  ]),
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(configValues, (configEntry, key) => {
                    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("tr", {
                      key,
                      class: vue.normalizeClass({ "custom-value": configEntry.isCustomValue })
                    }, [
                      vue.createElementVNode("td", _hoisted_7, vue.toDisplayString(`${key}${configEntry.value !== null && (configEntry.value instanceof Array || typeof configEntry.value === "object") ? "[]" : ""}`), 1),
                      vue.createElementVNode("td", {
                        class: "value",
                        innerHTML: _ctx.$sanitize(_ctx.humanReadableValue(configEntry.value))
                      }, null, 8, _hoisted_8),
                      vue.createElementVNode("td", _hoisted_9, [
                        vue.createElementVNode("span", {
                          innerHTML: _ctx.$sanitize(configEntry.description)
                        }, null, 8, _hoisted_10),
                        (configEntry.isCustomValue || configEntry.value === null) && configEntry.defaultValue !== null ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_11, [
                          configEntry.description ? (vue.openBlock(), vue.createElementBlock("br", _hoisted_12)) : vue.createCommentVNode("", true),
                          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Default")) + ": ", 1),
                          vue.createElementVNode("span", {
                            class: "defaultValue",
                            innerHTML: _ctx.$sanitize(_ctx.humanReadableValue(configEntry.defaultValue))
                          }, null, 8, _hoisted_13)
                        ])) : vue.createCommentVNode("", true)
                      ])
                    ], 2)), [
                      [vue.vShow, configEntry.isCustomValue || !_ctx.hideGlobalConfigValues]
                    ]);
                  }), 128))
                ]),
                _: 2
              }, 1024);
            }), 128))
          ])
        ])), [
          [_directive_content_table]
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ConfigFile = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.ConfigFile = ConfigFile;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
