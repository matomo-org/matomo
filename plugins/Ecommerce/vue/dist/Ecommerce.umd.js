(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Ecommerce = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main = vue.defineComponent({
    props: {
      idGoal: {
        type: [String, Number],
        required: true
      },
      visitorLogEnabled: Boolean,
      revenue: String,
      revenue_subtotal: String,
      revenue_tax: String,
      revenue_shipping: String,
      revenue_discount: String
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    methods: {
      showSegmentedVisitorLog() {
        window.SegmentedVisitorLog.show(
          "Goals.getMetrics",
          `visitConvertedGoalId==${this.idGoal}`,
          {}
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
  const _hoisted_1 = { class: "ulGoalTopElements" };
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = { key: 0 };
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = { key: 1 };
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = { key: 2 };
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = { key: 3 };
  const _hoisted_10 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("Goals_ConversionsOverview")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("ul", _hoisted_1, [
          vue.createElementVNode("li", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ColumnRevenue")) + ": ", 1),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.revenue || "")
            }, null, 8, _hoisted_2),
            _ctx.revenue_subtotal ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3, [
              vue.createTextVNode(", " + vue.toDisplayString(_ctx.translate("General_Subtotal")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.revenue_subtotal)
              }, null, 8, _hoisted_4)
            ])) : vue.createCommentVNode("", true),
            _ctx.revenue_tax ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5, [
              vue.createTextVNode(", " + vue.toDisplayString(_ctx.translate("General_Tax")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.revenue_tax)
              }, null, 8, _hoisted_6)
            ])) : vue.createCommentVNode("", true),
            _ctx.revenue_shipping ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7, [
              vue.createTextVNode(", " + vue.toDisplayString(_ctx.translate("General_Shipping")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.revenue_shipping)
              }, null, 8, _hoisted_8)
            ])) : vue.createCommentVNode("", true),
            _ctx.revenue_shipping ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_9, [
              vue.createTextVNode(", " + vue.toDisplayString(_ctx.translate("General_Discount")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.revenue_discount || "")
              }, null, 8, _hoisted_10)
            ])) : vue.createCommentVNode("", true)
          ])
        ]),
        _ctx.visitorLogEnabled ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          href: "",
          class: "segmentedlog",
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.showSegmentedVisitorLog(), ["prevent"]))
        }, [
          _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-visitor-profile rowActionIcon" }, "  ", -1)),
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("Live_RowActionTooltipWithDimension", _ctx.translate("General_Goal"))), 1)
        ])) : vue.createCommentVNode("", true),
        _cache[2] || (_cache[2] = vue.createElementVNode("br", { style: { "clear": "left" } }, null, -1))
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ConversionOverview = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.ConversionOverview = ConversionOverview;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
