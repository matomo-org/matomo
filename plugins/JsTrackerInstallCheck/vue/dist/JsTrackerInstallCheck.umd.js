(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.JsTrackerInstallCheck = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";
  const MAX_NUM_API_CALLS = 10;
  const TIME_BETWEEN_API_CALLS = 1e3;
  const _sfc_main = vue.defineComponent({
    components: {
      Field: CorePluginsAdmin.Field,
      ActivityIndicator: CoreHome.ActivityIndicator
    },
    data() {
      return {
        checkNonce: "",
        isTesting: false,
        isTestComplete: false,
        isTestSuccess: false,
        testTimeoutCount: 0,
        baseUrl: ""
      };
    },
    props: {
      site: {
        type: Object,
        required: true
      },
      isWordpress: {
        type: Boolean,
        required: false,
        default: false
      }
    },
    created() {
      this.checkWhetherSuccessWasRecorded();
    },
    watch: {
      site() {
        this.onSiteChange();
      }
    },
    methods: {
      onSiteChange() {
        this.checkNonce = "";
        this.isTesting = false;
        this.isTestComplete = false;
        this.isTestSuccess = false;
        this.testTimeoutCount = 0;
        this.checkWhetherSuccessWasRecorded();
      },
      initiateTrackerTest() {
        this.isTesting = true;
        this.isTestComplete = false;
        this.isTestSuccess = false;
        this.testTimeoutCount = 0;
        const siteRef = this.site;
        const postParams = { idSite: siteRef.id, url: "" };
        if (this.baseUrl) {
          postParams.url = this.baseUrl;
        }
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "JsTrackerInstallCheck.initiateJsTrackerInstallTest"
          },
          postParams
        ).then((response) => {
          const isSuccess = response && response.url && response.nonce;
          if (isSuccess) {
            this.checkNonce = response.nonce;
            const windowRef = window.open(response.url);
            this.setCheckInTime();
            setTimeout(() => {
              if (windowRef && !windowRef.closed) {
                windowRef.close();
                this.testTimeoutCount = MAX_NUM_API_CALLS;
              }
            }, MAX_NUM_API_CALLS * TIME_BETWEEN_API_CALLS);
          }
        }).catch(() => {
          this.isTesting = false;
        });
      },
      setCheckInTime() {
        setTimeout(this.checkWhetherSuccessWasRecorded, TIME_BETWEEN_API_CALLS);
      },
      checkWhetherSuccessWasRecorded() {
        const siteRef = this.site;
        const postParams = { idSite: siteRef.id, nonce: "" };
        if (this.checkNonce) {
          postParams.nonce = this.checkNonce;
        }
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "JsTrackerInstallCheck.wasJsTrackerInstallTestSuccessful"
          },
          postParams
        ).then((response) => {
          if (response && response.mainUrl && !this.baseUrl) {
            this.baseUrl = response.mainUrl;
          }
          this.isTestSuccess = response && response.isSuccess;
          if (this.checkNonce && !this.isTestSuccess && this.testTimeoutCount < MAX_NUM_API_CALLS) {
            this.testTimeoutCount += 1;
            this.setCheckInTime();
            return;
          }
          this.isTestComplete = !!this.checkNonce;
          this.isTesting = false;
        }).catch(() => {
          this.isTesting = false;
        });
      }
    },
    computed: {
      getTestFailureMessage() {
        const learnMoreLink = CoreHome.externalLink("https://matomo.org/faq/troubleshooting/faq_58/");
        const closingTag = "</a>";
        if (!this.isWordpress) {
          return CoreHome.translate("JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage", learnMoreLink, closingTag);
        }
        return CoreHome.translate(
          "JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress",
          '<a target="_blank" rel="noreferrer noopener" href="https://wordpress.org/plugins/wp-piwik/">WP-Matomo Integration (WP-Piwik)</a>',
          learnMoreLink,
          closingTag
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
  const _hoisted_1 = { class: "jsTrackerInstallCheck" };
  const _hoisted_2 = { class: "row testInstallFields" };
  const _hoisted_3 = { class: "col s2" };
  const _hoisted_4 = { class: "col s10" };
  const _hoisted_5 = ["disabled", "value"];
  const _hoisted_6 = { class: "system-success success-message" };
  const _hoisted_7 = { class: "system-errors test-error" };
  const _hoisted_8 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("JsTrackerInstallCheck_OptionalTestInstallationDescription")), 1),
      vue.createElementVNode("div", _hoisted_1, [
        vue.createElementVNode("div", _hoisted_2, [
          vue.createElementVNode("div", _hoisted_3, [
            vue.createVNode(_component_Field, {
              uicontrol: "url",
              name: "baseUrl",
              placeholder: "https://example.com",
              modelValue: _ctx.baseUrl,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.baseUrl = $event),
              "full-width": true,
              disabled: _ctx.isTesting
            }, null, 8, ["modelValue", "disabled"])
          ]),
          vue.createElementVNode("div", _hoisted_4, [
            vue.createElementVNode("input", {
              type: "button",
              class: "btn testInstallBtn",
              onClick: _cache[1] || (_cache[1] = (...args) => _ctx.initiateTrackerTest && _ctx.initiateTrackerTest(...args)),
              disabled: !_ctx.baseUrl || _ctx.isTesting,
              value: _ctx.translate("JsTrackerInstallCheck_TestInstallationBtnText")
            }, null, 8, _hoisted_5)
          ])
        ]),
        vue.createVNode(_component_ActivityIndicator, {
          loading: _ctx.isTesting,
          loadingMessage: _ctx.translate("General_Testing")
        }, null, 8, ["loading", "loadingMessage"]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_6, [
          _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-ok" }, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage")), 1)
        ], 512), [
          [vue.vShow, _ctx.isTestSuccess]
        ]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_7, [
          _cache[3] || (_cache[3] = vue.createElementVNode("span", { class: "icon-warning" }, null, -1)),
          _cache[4] || (_cache[4] = vue.createTextVNode("  ", -1)),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.getTestFailureMessage)
          }, null, 8, _hoisted_8)
        ], 512), [
          [vue.vShow, _ctx.isTestComplete && !_ctx.isTestSuccess]
        ])
      ])
    ], 64);
  }
  const JsTrackerInstallCheck = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.JsTrackerInstallCheck = JsTrackerInstallCheck;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
