(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Referrers = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";
  const { $ } = window;
  const _sfc_main$1 = vue.defineComponent({
    props: {
      hasExtraPlugin: {
        type: Boolean,
        default: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        websiteUrl: "",
        campaignName: "",
        campaignKeyword: "",
        campaignSource: "",
        campaignMedium: "",
        campaignId: "",
        campaignContent: "",
        campaignGroup: "",
        campaignPlacement: "",
        generatedUrl: ""
      };
    },
    created() {
      this.reset();
    },
    watch: {
      generatedUrl() {
        $("#urlCampaignBuilderResult").effect("highlight", {}, 1500);
      }
    },
    methods: {
      reset() {
        this.websiteUrl = "";
        this.campaignName = "";
        this.campaignKeyword = "";
        this.campaignSource = "";
        this.campaignMedium = "";
        this.campaignId = "";
        this.campaignContent = "";
        this.campaignGroup = "";
        this.campaignPlacement = "";
        this.generatedUrl = "";
      },
      generateUrl() {
        let generatedUrl = String(this.websiteUrl);
        if (generatedUrl.indexOf("http") !== 0) {
          generatedUrl = `https://${generatedUrl.trim()}`;
        }
        const urlHashPos = generatedUrl.indexOf("#");
        let urlHash = "";
        if (urlHashPos >= 0) {
          urlHash = generatedUrl.slice(urlHashPos);
          generatedUrl = generatedUrl.slice(0, urlHashPos);
        }
        if (generatedUrl.indexOf("/", 10) < 0 && generatedUrl.indexOf("?") < 0) {
          generatedUrl += "/";
        }
        const campaignName = encodeURIComponent(this.campaignName.trim());
        if (generatedUrl.indexOf("?") > 0 || generatedUrl.indexOf("#") > 0) {
          generatedUrl += "&";
        } else {
          generatedUrl += "?";
        }
        generatedUrl += `mtm_campaign=${campaignName}`;
        if (this.campaignKeyword) {
          generatedUrl += `&mtm_kwd=${encodeURIComponent(this.campaignKeyword.trim())}`;
        }
        if (this.campaignSource) {
          generatedUrl += `&mtm_source=${encodeURIComponent(this.campaignSource.trim())}`;
        }
        if (this.campaignMedium) {
          generatedUrl += `&mtm_medium=${encodeURIComponent(this.campaignMedium.trim())}`;
        }
        if (this.campaignContent) {
          generatedUrl += `&mtm_content=${encodeURIComponent(this.campaignContent.trim())}`;
        }
        if (this.campaignId) {
          generatedUrl += `&mtm_cid=${encodeURIComponent(this.campaignId.trim())}`;
        }
        if (this.campaignGroup) {
          generatedUrl += `&mtm_group=${encodeURIComponent(this.campaignGroup.trim())}`;
        }
        if (this.campaignPlacement) {
          generatedUrl += `&mtm_placement=${encodeURIComponent(this.campaignPlacement.trim())}`;
        }
        generatedUrl += urlHash;
        this.generatedUrl = generatedUrl;
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
  const _hoisted_1$1 = { class: "campaignUrlBuilder" };
  const _hoisted_2$1 = { id: "urlCampaignBuilderResult" };
  const _hoisted_3$1 = ["textContent"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.createElementVNode("form", null, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "websiteurl",
            title: `${_ctx.translate("Actions_ColumnPageURL")} (${_ctx.translate("General_Required2")})`,
            modelValue: _ctx.websiteUrl,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.websiteUrl = $event),
            "inline-help": _ctx.translate("Referrers_CampaignPageUrlHelp")
          }, null, 8, ["title", "modelValue", "inline-help"])
        ]),
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignname",
            title: `${_ctx.translate("CoreAdminHome_JSTracking_CampaignNameParam")} (${_ctx.translate("General_Required2")})`,
            modelValue: _ctx.campaignName,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.campaignName = $event),
            "inline-help": _ctx.translate("Referrers_CampaignNameHelp")
          }, null, 8, ["title", "modelValue", "inline-help"])
        ]),
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignkeyword",
            title: _ctx.translate("CoreAdminHome_JSTracking_CampaignKwdParam"),
            modelValue: _ctx.campaignKeyword,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.campaignKeyword = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignKeywordHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignsource",
            title: _ctx.translate("Referrers_CampaignSource"),
            modelValue: _ctx.campaignSource,
            "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.campaignSource = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignSourceHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignmedium",
            title: _ctx.translate("Referrers_CampaignMedium"),
            modelValue: _ctx.campaignMedium,
            "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.campaignMedium = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignMediumHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaigncontent",
            title: _ctx.translate("Referrers_CampaignContent"),
            modelValue: _ctx.campaignContent,
            "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.campaignContent = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignContentHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignid",
            title: _ctx.translate("Referrers_CampaignId"),
            modelValue: _ctx.campaignId,
            "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.campaignId = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignIdHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaigngroup",
            title: _ctx.translate("Referrers_CampaignGroup"),
            modelValue: _ctx.campaignGroup,
            "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.campaignGroup = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignGroupHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives(vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "campaignplacement",
            title: _ctx.translate("Referrers_CampaignPlacement"),
            modelValue: _ctx.campaignPlacement,
            "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.campaignPlacement = $event),
            "inline-help": `${_ctx.translate("Goals_Optional")} ${_ctx.translate("Referrers_CampaignPlacementHelp")}`
          }, null, 8, ["title", "modelValue", "inline-help"]), [
            [vue.vShow, _ctx.hasExtraPlugin]
          ])
        ]),
        vue.createVNode(_component_SaveButton, {
          class: "generateCampaignUrl",
          onConfirm: _cache[9] || (_cache[9] = ($event) => _ctx.generateUrl()),
          disabled: !_ctx.websiteUrl || !_ctx.campaignName,
          value: _ctx.translate("Referrers_GenerateUrl"),
          style: { "margin-right": "3.5px" }
        }, null, 8, ["disabled", "value"]),
        vue.createVNode(_component_SaveButton, {
          class: "resetCampaignUrl",
          onConfirm: _cache[10] || (_cache[10] = ($event) => _ctx.reset()),
          value: _ctx.translate("General_Clear")
        }, null, 8, ["value"]),
        vue.withDirectives(vue.createElementVNode("div", null, [
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Referrers_URLCampaignBuilderResult")), 1),
          vue.createElementVNode("div", null, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", _hoisted_2$1, [
              vue.createElementVNode("code", {
                textContent: vue.toDisplayString(_ctx.generatedUrl)
              }, null, 8, _hoisted_3$1)
            ])), [
              [_directive_copy_to_clipboard, {}]
            ])
          ])
        ], 512), [
          [vue.vShow, _ctx.generatedUrl]
        ])
      ])
    ]);
  }
  const CampaignBuilder = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const docsBuilderUrl = "https://matomo.org/docs/tracking-campaigns-url-builder/";
  const docsCampaignsUrl = "https://matomo.org/docs/tracking-campaigns/";
  const _sfc_main = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock,
      CampaignBuilder
    },
    props: {
      hasExtraPlugin: Boolean,
      isWidget: Boolean
    },
    computed: {
      introHtml() {
        return CoreHome.translate(
          "Referrers_URLCampaignBuilderIntro",
          CoreHome.externalLink(docsBuilderUrl),
          "</a>",
          CoreHome.externalLink(docsCampaignsUrl),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1 = { class: "widgetBody" };
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = {
    key: 1,
    class: "widgetBody"
  };
  const _hoisted_4 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CampaignBuilder = vue.resolveComponent("CampaignBuilder");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return !_ctx.isWidget ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      key: 0,
      "content-title": _ctx.translate("Referrers_URLCampaignBuilder")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", _hoisted_1, [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.introHtml)
          }, null, 8, _hoisted_2),
          vue.createVNode(_component_CampaignBuilder, { "has-extra-plugin": _ctx.hasExtraPlugin }, null, 8, ["has-extra-plugin"])
        ])
      ]),
      _: 1
    }, 8, ["content-title"])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
      vue.createElementVNode("p", {
        innerHTML: _ctx.$sanitize(_ctx.introHtml)
      }, null, 8, _hoisted_4),
      vue.createVNode(_component_CampaignBuilder, { "has-extra-plugin": _ctx.hasExtraPlugin }, null, 8, ["has-extra-plugin"])
    ]));
  }
  const CampaignBuilderWidget = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.CampaignBuilder = CampaignBuilder;
  exports2.CampaignBuilderWidget = CampaignBuilderWidget;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
