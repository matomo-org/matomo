(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Morpheus = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main$1 = vue.defineComponent({
    props: {
      snippet: {
        type: Object,
        required: true
      }
    },
    computed: {
      vueEmbedComponent() {
        const snippet = this.snippet;
        const components = {};
        (snippet.components || []).forEach((info) => {
          components[info.component] = CoreHome.useExternalPluginComponent(info.plugin, info.component);
        });
        const directives = {};
        (snippet.directives || []).forEach((info) => {
          directives[info.directive] = window[info.plugin][info.directive];
        });
        const dataToUse = this.snippet.data || {};
        return vue.markRaw({
          template: this.snippet.vue_embed,
          components,
          directives,
          data() {
            return dataToUse;
          }
        });
      },
      processedSnippetCode() {
        const { snippet } = this;
        const vueEmbedIndex = snippet.code.indexOf("%vue_embed%");
        const lastNewline = snippet.code.lastIndexOf("\n", vueEmbedIndex);
        const spaces = snippet.code.substring(lastNewline + 1, vueEmbedIndex);
        return snippet.code.replaceAll("%vue_embed%", snippet.vue_embed.replaceAll("\n", `
${spaces}`));
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
  const _hoisted_1$1 = ["data-snippet"];
  const _hoisted_2$1 = { key: 0 };
  const _hoisted_3$1 = {
    key: 1,
    class: "demo"
  };
  const _hoisted_4$1 = {
    key: 2,
    class: "demo-code"
  };
  const _hoisted_5 = { key: 3 };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      style: vue.normalizeStyle({ "margin-top": _ctx.snippet.noMargin ? "-16px" : void 0 }),
      "data-snippet": _ctx.snippet.id
    }, [
      _ctx.snippet.title ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_2$1, vue.toDisplayString(_ctx.snippet.title), 1)) : vue.createCommentVNode("", true),
      _ctx.snippet.vue_embed ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, [
        (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.vueEmbedComponent)))
      ])) : vue.createCommentVNode("", true),
      _ctx.snippet.code ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$1, [
        vue.createElementVNode("pre", null, vue.toDisplayString(_ctx.processedSnippetCode), 1)
      ])) : vue.createCommentVNode("", true),
      _ctx.snippet.desc ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_5, vue.toDisplayString(_ctx.snippet.desc), 1)) : vue.createCommentVNode("", true)
    ], 12, _hoisted_1$1);
  }
  const DemoCodePair = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      demoSnippets: {
        type: Array,
        required: true
      },
      icons: {
        type: Object,
        required: true
      }
    },
    components: {
      DemoCodePair
    }
  });
  const _hoisted_1 = /* @__PURE__ */ vue.createElementVNode("h2", null, "Icons", -1);
  const _hoisted_2 = {
    id: "icons",
    class: "demo icons"
  };
  const _hoisted_3 = { class: "row" };
  const _hoisted_4 = /* @__PURE__ */ vue.createElementVNode("div", { class: "demo-code" }, [
    /* @__PURE__ */ vue.createElementVNode("pre", null, '<span class="icon-ok"></span>')
  ], -1);
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DemoCodePair = vue.resolveComponent("DemoCodePair");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.demoSnippets, (snippet) => {
        return vue.openBlock(), vue.createBlock(_component_DemoCodePair, {
          key: snippet.id,
          snippet
        }, null, 8, ["snippet"]);
      }), 128)),
      _hoisted_1,
      vue.createElementVNode("div", _hoisted_2, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.icons, (iconList, category) => {
          return vue.openBlock(), vue.createElementBlock("div", { key: category }, [
            vue.createElementVNode("h4", null, vue.toDisplayString(category), 1),
            vue.createElementVNode("div", _hoisted_3, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(iconList, (icon, index) => {
                return vue.openBlock(), vue.createElementBlock("div", {
                  class: "col s4 icon",
                  key: index
                }, [
                  vue.createElementVNode("span", {
                    class: vue.normalizeClass(`icon-${icon}`)
                  }, null, 2),
                  vue.createTextVNode(" " + vue.toDisplayString(icon), 1)
                ]);
              }), 128))
            ])
          ]);
        }), 128))
      ]),
      _hoisted_4
    ]);
  }
  const Demo = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.Demo = Demo;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
