(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.ExampleVue = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main = vue.defineComponent({
    components: {
      MatomoDialog: CoreHome.MatomoDialog
    },
    data() {
      return {
        count: 12,
        showDialog: false
      };
    },
    methods: {
      increment() {
        this.count += 1;
        this.showDialog = this.count > 15;
      },
      decrement() {
        this.count -= 1;
        this.showDialog = this.count > 15;
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
  const _hoisted_1 = { class: "example-component" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoDialog = vue.resolveComponent("MatomoDialog");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createElementVNode("button", {
        onClick: _cache[0] || (_cache[0] = (...args) => _ctx.decrement && _ctx.decrement(...args))
      }, "-"),
      vue.createTextVNode(" " + vue.toDisplayString(_ctx.count) + " ", 1),
      vue.createElementVNode("button", {
        onClick: _cache[1] || (_cache[1] = (...args) => _ctx.increment && _ctx.increment(...args))
      }, "+"),
      vue.createVNode(_component_MatomoDialog, {
        modelValue: _ctx.showDialog,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.showDialog = $event)
      }, {
        default: vue.withCtx(() => [..._cache[3] || (_cache[3] = [
          vue.createElementVNode("div", { class: "ui-confirm exampleDialog" }, [
            vue.createElementVNode("h2", null, "Alert"),
            vue.createElementVNode("p", null, " The count is greater than 15 right now! "),
            vue.createElementVNode("input", {
              type: "button",
              value: "OK",
              role: "yes"
            })
          ], -1)
        ])]),
        _: 1
      }, 8, ["modelValue"])
    ]);
  }
  const ExampleComponent = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-88b1496f"]]);
  exports2.ExampleComponent = ExampleComponent;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
