(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.DBStats = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main = vue.defineComponent({
    props: {
      totalSpaceUsed: {
        type: [String, Number],
        required: true
      }
    },
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      learnMoreText() {
        const link = CoreHome.externalRawLink("https://matomo.org/docs/setup-auto-archiving/");
        return CoreHome.translate(
          "DBStats_LearnMore",
          `<a target="_blank" rel="noreferrer noopener" href="${link}">Matomo Auto Archiving</a>`
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
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("h2", null, [
        vue.createVNode(_component_EnrichedHeadline, null, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("DBStats_DatabaseUsage")), 1)
          ]),
          _: 1
        })
      ]),
      vue.createElementVNode("p", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("DBStats_MainDescription", _ctx.totalSpaceUsed)), 1),
        _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
        vue.createElementVNode("span", {
          innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
        }, null, 8, _hoisted_1)
      ])
    ])), [
      [_directive_content_intro]
    ]);
  }
  const DBStatsIntro = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.DBStatsIntro = DBStatsIntro;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
