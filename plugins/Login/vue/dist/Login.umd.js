(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Login = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main$1 = vue.defineComponent({
    props: {
      formErrors: [Array, Object]
    },
    components: {
      Notification: CoreHome.Notification
    }
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$1 = ["innerHTML"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Notification = vue.resolveComponent("Notification");
    return Object.keys(_ctx.formErrors || {}).length ? (vue.openBlock(), vue.createBlock(_component_Notification, {
      key: 0,
      noclear: true,
      context: "error"
    }, {
      default: vue.withCtx(() => [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.formErrors, (data, key) => {
          return vue.openBlock(), vue.createElementBlock("span", { key }, [
            vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("General_Error")), 1),
            _cache[0] || (_cache[0] = vue.createTextVNode(": ", -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(data)
            }, null, 8, _hoisted_1$1),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1))
          ]);
        }), 128))
      ]),
      _: 1
    })) : vue.createCommentVNode("", true);
  }
  const FormErrors = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      blockedIps: {
        type: Array,
        required: true
      },
      disallowedIps: {
        type: Array,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    methods: {
      unblockAllIps() {
        window.bruteForceLog.unblockAllIps();
      }
    }
  });
  const _hoisted_1 = { key: 0 };
  const _hoisted_2 = {
    key: 1,
    style: { "margin-left": "20px" }
  };
  const _hoisted_3 = { key: 2 };
  const _hoisted_4 = ["value"];
  const _hoisted_5 = {
    id: "confirmUnblockAllIps",
    class: "ui-confirm"
  };
  const _hoisted_6 = ["value"];
  const _hoisted_7 = ["value"];
  const _hoisted_8 = { key: 3 };
  const _hoisted_9 = { style: { "margin-left": "20px" } };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("Login_CurrentlyBlockedIPs")
    }, {
      default: vue.withCtx(() => [
        !_ctx.blockedIps.length ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_1, vue.toDisplayString(_ctx.translate("UserCountryMap_None")), 1)) : (vue.openBlock(), vue.createElementBlock("ul", _hoisted_2, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.blockedIps, (blockedIp, index) => {
            return vue.openBlock(), vue.createElementBlock("li", {
              style: { "list-style": "disc" },
              key: index
            }, vue.toDisplayString(blockedIp), 1);
          }), 128))
        ])),
        _ctx.blockedIps.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
          vue.createElementVNode("p", null, [
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("Login_CurrentlyBlockedIPsUnblockInfo")), 1)
          ]),
          vue.createElementVNode("div", null, [
            vue.createElementVNode("input", {
              type: "button",
              class: "btn",
              value: _ctx.translate("Login_UnblockAllIPs"),
              onClick: _cache[0] || (_cache[0] = ($event) => _ctx.unblockAllIps())
            }, null, 8, _hoisted_4)
          ]),
          vue.createElementVNode("div", _hoisted_5, [
            vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Login_CurrentlyBlockedIPsUnblockConfirm")), 1),
            vue.createElementVNode("input", {
              role: "yes",
              type: "button",
              value: _ctx.translate("General_Yes")
            }, null, 8, _hoisted_6),
            vue.createElementVNode("input", {
              role: "no",
              type: "button",
              value: _ctx.translate("General_No")
            }, null, 8, _hoisted_7)
          ])
        ])) : vue.createCommentVNode("", true),
        _ctx.disallowedIps.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8, [
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Login_IPsAlwaysBlocked")), 1),
          vue.createElementVNode("ul", _hoisted_9, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.disallowedIps, (ip, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                style: { "list-style": "disc" },
                key: index
              }, vue.toDisplayString(ip), 1);
            }), 128))
          ])
        ])) : vue.createCommentVNode("", true)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const BruteForceLog = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.BruteForceLog = BruteForceLog;
  exports2.FormErrors = FormErrors;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
