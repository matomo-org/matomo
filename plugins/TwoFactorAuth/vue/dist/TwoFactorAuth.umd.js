(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin"), require("Login")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin", "Login"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.TwoFactorAuth = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin, global.Login));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin, Login) {
  "use strict";var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
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
var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));

  const _sfc_main$6 = vue.defineComponent({
    props: {
      codes: {
        type: Array,
        default() {
          return [];
        }
      }
    },
    directives: {
      SelectOnFocus: CoreHome.SelectOnFocus
    },
    emits: ["downloaded"],
    methods: {
      copyRecoveryCodesToClipboard() {
        const textarea = document.createElement("textarea");
        textarea.value = this.codes.join("\n");
        textarea.setAttribute("readonly", "");
        textarea.style.position = "absolute";
        textarea.style.left = "-9999px";
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        document.body.removeChild(textarea);
      },
      downloadRecoveryCodes() {
        CoreHome.Matomo.helper.sendContentAsDownload("analytics_recovery_codes.txt", this.codes.join("\n"));
      },
      print() {
        window.print();
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
  const _hoisted_1$6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_2$6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_3$6 = { class: "alert alert-warning" };
  const _hoisted_4$5 = {
    key: 0,
    class: "twoFactorRecoveryCodes browser-default"
  };
  const _hoisted_5$5 = {
    key: 1,
    class: "alert alert-danger"
  };
  const _hoisted_6$4 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7$4 = ["value"];
  const _hoisted_8$3 = ["value"];
  const _hoisted_9$3 = ["value"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    const _directive_select_on_focus = vue.resolveDirective("select-on-focus");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("p", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("TwoFactorAuth_RecoveryCodesExplanation")), 1),
        _hoisted_1$6,
        _hoisted_2$6
      ]),
      vue.createElementVNode("div", _hoisted_3$6, vue.toDisplayString(_ctx.translate("TwoFactorAuth_RecoveryCodesSecurity")), 1),
      ((_a = _ctx.codes) == null ? void 0 : _a.length) ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("ul", _hoisted_4$5, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.codes, (code, index) => {
          return vue.openBlock(), vue.createElementBlock("li", { key: index }, vue.toDisplayString(code.toUpperCase().match(/.{1,4}/g).join("-")), 1);
        }), 128))
      ])), [
        [_directive_select_on_focus, {}]
      ]) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$5, vue.toDisplayString(_ctx.translate("TwoFactorAuth_RecoveryCodesAllUsed")), 1)),
      vue.createElementVNode("p", null, [
        _hoisted_6$4,
        vue.createElementVNode("input", {
          type: "button",
          class: "btn backupRecoveryCode",
          onClick: _cache[0] || (_cache[0] = ($event) => {
            _ctx.downloadRecoveryCodes();
            _ctx.$emit("downloaded");
          }),
          value: _ctx.translate("General_Download"),
          style: { "margin-right": "3.5px" }
        }, null, 8, _hoisted_7$4),
        vue.createElementVNode("input", {
          type: "button",
          class: "btn backupRecoveryCode",
          onClick: _cache[1] || (_cache[1] = ($event) => {
            _ctx.print();
            _ctx.$emit("downloaded");
          }),
          value: _ctx.translate("General_Print"),
          style: { "margin-right": "3.5px" }
        }, null, 8, _hoisted_8$3),
        vue.createElementVNode("input", {
          type: "button",
          class: "btn backupRecoveryCode",
          onClick: _cache[2] || (_cache[2] = ($event) => {
            _ctx.copyRecoveryCodesToClipboard();
            _ctx.$emit("downloaded");
          }),
          value: _ctx.translate("General_Copy")
        }, null, 8, _hoisted_9$3)
      ])
    ]);
  }
  const ShowRecoveryCodes = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = vue.defineComponent({
    props: {
      codes: Array,
      regenerateSuccess: Boolean,
      regenerateError: Boolean,
      regenerateNonce: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      ShowRecoveryCodes
    },
    computed: {
      contentTitle() {
        const part1 = CoreHome.translate("TwoFactorAuth_TwoFactorAuthentication");
        const part2 = CoreHome.translate("TwoFactorAuth_RecoveryCodes");
        return `${part1} - ${part2}`;
      },
      showRecoveryCodesLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "TwoFactorAuth",
          action: "showRecoveryCodes"
        }))}`;
      }
    }
  });
  const _hoisted_1$5 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_2$5 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_3$5 = {
    key: 0,
    class: "alert alert-success"
  };
  const _hoisted_4$4 = {
    key: 1,
    class: "alert alert-danger"
  };
  const _hoisted_5$4 = ["action"];
  const _hoisted_6$3 = ["value"];
  const _hoisted_7$3 = ["value"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ShowRecoveryCodes = vue.resolveComponent("ShowRecoveryCodes");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, { "content-title": _ctx.contentTitle }, {
      default: vue.withCtx(() => [
        vue.createVNode(_component_ShowRecoveryCodes, { codes: _ctx.codes }, null, 8, ["codes"]),
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_GenerateNewRecoveryCodes")), 1),
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("TwoFactorAuth_GenerateNewRecoveryCodesInfo")), 1),
          _hoisted_1$5,
          _hoisted_2$5
        ]),
        _ctx.regenerateSuccess ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$5, vue.toDisplayString(_ctx.translate("TwoFactorAuth_RecoveryCodesRegenerated")), 1)) : vue.createCommentVNode("", true),
        _ctx.regenerateError ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$4, vue.toDisplayString(_ctx.translate("General_ExceptionSecurityCheckFailed")), 1)) : vue.createCommentVNode("", true),
        vue.createElementVNode("form", {
          method: "post",
          action: _ctx.showRecoveryCodesLink
        }, [
          vue.createElementVNode("input", {
            type: "hidden",
            name: "regenerateNonce",
            value: _ctx.regenerateNonce
          }, null, 8, _hoisted_6$3),
          vue.createElementVNode("input", {
            type: "submit",
            class: "btn",
            value: _ctx.translate("TwoFactorAuth_GenerateNewRecoveryCodes")
          }, null, 8, _hoisted_7$3)
        ], 8, _hoisted_5$4)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ShowRecoveryCodesPage = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({});
  const _hoisted_1$4 = /* @__PURE__ */ vue.createElementVNode("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: "https://github.com/andOTP/andOTP#downloads"
  }, "andOTP", -1);
  const _hoisted_2$4 = /* @__PURE__ */ vue.createElementVNode("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: "https://authy.com/guides/github/"
  }, "Authy", -1);
  const _hoisted_3$4 = /* @__PURE__ */ vue.createElementVNode("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: "https://support.1password.com/one-time-passwords/"
  }, "1Password", -1);
  const _hoisted_4$3 = /* @__PURE__ */ vue.createElementVNode("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: "https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
  }, "LastPass Authenticator", -1);
  const _hoisted_5$3 = /* @__PURE__ */ vue.createElementVNode("a", {
    target: "_blank",
    rel: "noreferrer noopener",
    href: "https://support.google.com/accounts/answer/1066447"
  }, "Google Authenticator", -1);
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("p", null, [
      vue.createTextVNode(vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupAuthenticatorOnDeviceStep1")) + " ", 1),
      _hoisted_1$4,
      vue.createTextVNode(", "),
      _hoisted_2$4,
      vue.createTextVNode(", "),
      _hoisted_3$4,
      vue.createTextVNode(", "),
      _hoisted_4$3,
      vue.createTextVNode(", " + vue.toDisplayString(_ctx.translate("General_Or")) + " ", 1),
      _hoisted_5$3,
      vue.createTextVNode(". ")
    ]);
  }
  const InstallOTPApp = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const { QRCode, $ } = window;
  const _sfc_main$3 = vue.defineComponent({
    props: {
      isAlreadyUsing2fa: Boolean,
      accessErrorString: String,
      submitAction: {
        type: String,
        required: true
      },
      authCodeNonce: {
        type: String,
        required: true
      },
      newSecret: {
        type: String,
        required: true
      },
      codes: Array,
      twoFaBarCodeSetupUrl: {
        type: String,
        required: true
      },
      standalone: Boolean
    },
    components: {
      InstallOTPApp,
      MatomoDialog: CoreHome.MatomoDialog,
      ShowRecoveryCodes,
      Notification: CoreHome.Notification,
      Field: CorePluginsAdmin.Field,
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        step: 1,
        hasDownloadedRecoveryCode: false,
        authCode: "",
        qrCodeDialogVisible: false
      };
    },
    mounted() {
      setTimeout(() => {
        const qrcode = this.$refs.qrcode;
        new QRCode(qrcode, {
          text: this.twoFaBarCodeSetupUrl,
          width: 200,
          height: 200
        });
        $(qrcode).attr("title", "");
        if (this.accessErrorString) {
          this.step = 3;
          this.scrollToEnd();
        }
      });
    },
    methods: {
      scrollToEnd() {
        setTimeout(() => {
          let id = "";
          if (this.step === 2) {
            id = "#twoFactorStep2";
          } else if (this.step === 3) {
            id = "#twoFactorStep3";
          }
          if (id) {
            CoreHome.Matomo.helper.lazyScrollTo(id, 50, true);
          }
        }, 50);
      },
      showQrCodeModal() {
        this.qrCodeDialogVisible = true;
      },
      closeQrCodeModal() {
        this.qrCodeDialogVisible = false;
      },
      nextStep() {
        this.step += 1;
        if (this.step > 3) {
          this.step = 3;
        }
        this.scrollToEnd();
      },
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), params))}`;
      }
    },
    computed: {
      setupAuthenticatorOnDeviceStep2ShowCodes() {
        return CoreHome.translate(
          "TwoFactorAuth_SetupAuthenticatorOnDeviceStep2ShowCodes",
          CoreHome.translate("TwoFactorAuth_ShowCodes")
        );
      },
      showCodeModalInstructions3() {
        return CoreHome.translate(
          "TwoFactorAuth_ShowCodeModalInstructions3",
          CoreHome.translate("General_Continue")
        );
      }
    }
  });
  const _hoisted_1$3 = {
    class: "setupTwoFactorAuthentication",
    ref: "root"
  };
  const _hoisted_2$3 = {
    key: 0,
    class: "alert alert-warning"
  };
  const _hoisted_3$3 = ["disabled"];
  const _hoisted_4$2 = /* @__PURE__ */ vue.createElementVNode("a", {
    name: "twoFactorStep2",
    id: "twoFactorStep2",
    style: { "opacity": "0" }
  }, null, -1);
  const _hoisted_5$2 = ["innerHTML"];
  const _hoisted_6$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7$2 = /* @__PURE__ */ vue.createElementVNode("a", {
    name: "twoFactorStep3",
    id: "twoFactorStep3",
    style: { "opacity": "0" }
  }, null, -1);
  const _hoisted_8$2 = {
    key: 0,
    class: "message_container"
  };
  const _hoisted_9$2 = ["innerHTML"];
  const _hoisted_10$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_11$2 = ["action"];
  const _hoisted_12$2 = ["value"];
  const _hoisted_13$2 = ["disabled", "value"];
  const _hoisted_14$2 = { class: "ui-confirm two-fa-qr-code-dialog" };
  const _hoisted_15$2 = { class: "row" };
  const _hoisted_16$2 = { class: "col l8 offset-l2 m10 offset-m1 s12 center-align" };
  const _hoisted_17$2 = {
    id: "qrcode",
    ref: "qrcode",
    title: ""
  };
  const _hoisted_18$2 = { class: "text-code" };
  const _hoisted_19$2 = ["innerHTML"];
  const _hoisted_20$2 = { class: "row" };
  const _hoisted_21$1 = { class: "col l8 offset-l2 m10 offset-m1 s12" };
  const _hoisted_22 = ["value"];
  const _hoisted_23 = ["value"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ShowRecoveryCodes = vue.resolveComponent("ShowRecoveryCodes");
    const _component_InstallOTPApp = vue.resolveComponent("InstallOTPApp");
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_Field = vue.resolveComponent("Field");
    const _component_MatomoDialog = vue.resolveComponent("MatomoDialog");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.standalone ? _ctx.translate("TwoFactorAuth_RequiredToSetUpTwoFactorAuthentication") : _ctx.translate("TwoFactorAuth_SetUpTwoFactorAuthentication")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", _hoisted_1$3, [
          _ctx.isAlreadyUsing2fa ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, vue.toDisplayString(_ctx.translate("TwoFactorAuth_WarningChangingConfiguredDevice")), 1)) : vue.createCommentVNode("", true),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupIntroFollowSteps")), 1),
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_StepX", 1)) + " - " + vue.toDisplayString(_ctx.translate("TwoFactorAuth_RecoveryCodes")), 1),
          vue.createVNode(_component_ShowRecoveryCodes, {
            codes: _ctx.codes,
            onDownloaded: _cache[0] || (_cache[0] = ($event) => this.hasDownloadedRecoveryCode = true)
          }, null, 8, ["codes"]),
          vue.withDirectives(vue.createElementVNode("div", { class: "alert alert-info backupRecoveryCodesAlert" }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupBackupRecoveryCodes")), 513), [
            [vue.vShow, _ctx.step === 1]
          ]),
          vue.createElementVNode("p", null, [
            vue.withDirectives(vue.createElementVNode("button", {
              class: "btn goToStep2",
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.nextStep()),
              disabled: !_ctx.hasDownloadedRecoveryCode
            }, vue.toDisplayString(_ctx.translate("General_Next")), 9, _hoisted_3$3), [
              [vue.vShow, _ctx.step === 1]
            ])
          ]),
          _hoisted_4$2,
          vue.withDirectives(vue.createElementVNode("div", null, [
            vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_StepX", 2)) + " - " + vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupAuthenticatorOnDevice")), 1),
            vue.createVNode(_component_InstallOTPApp),
            vue.createElementVNode("p", {
              innerHTML: _ctx.$sanitize(_ctx.setupAuthenticatorOnDeviceStep2ShowCodes)
            }, null, 8, _hoisted_5$2),
            vue.createElementVNode("p", null, [
              _hoisted_6$2,
              vue.withDirectives(vue.createElementVNode("button", {
                class: "btn showOtpCodes",
                onClick: _cache[2] || (_cache[2] = ($event) => _ctx.showQrCodeModal())
              }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ShowCodes")), 513), [
                [vue.vShow, _ctx.step >= 2]
              ])
            ])
          ], 512), [
            [vue.vShow, _ctx.step >= 2]
          ]),
          _hoisted_7$2,
          vue.withDirectives(vue.createElementVNode("div", null, [
            vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_StepX", 3)) + " - " + vue.toDisplayString(_ctx.translate("TwoFactorAuth_ConfirmSetup")), 1),
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_VerifyAuthCodeIntro")), 1),
            _ctx.accessErrorString ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$2, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Notification, {
                  noclear: true,
                  context: "error"
                }, {
                  default: vue.withCtx(() => [
                    vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("General_Error")), 1),
                    vue.createTextVNode(": "),
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
                    }, null, 8, _hoisted_9$2),
                    _hoisted_10$2
                  ]),
                  _: 1
                })
              ])
            ])) : vue.createCommentVNode("", true),
            vue.createElementVNode("form", {
              method: "post",
              class: "setupConfirmAuthCodeForm",
              autocorrect: "off",
              autocapitalize: "none",
              autocomplete: "off",
              action: _ctx.linkTo({ "module": "TwoFactorAuth", "action": _ctx.submitAction })
            }, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  uicontrol: "text",
                  name: "authCode",
                  title: _ctx.translate("TwoFactorAuth_AuthenticationCode"),
                  modelValue: _ctx.authCode,
                  "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.authCode = $event),
                  maxlength: 6,
                  placeholder: "123456",
                  autocomplete: "one-time-code",
                  "inline-help": _ctx.translate("TwoFactorAuth_VerifyAuthCodeHelp")
                }, null, 8, ["title", "modelValue", "inline-help"])
              ]),
              vue.createElementVNode("input", {
                type: "hidden",
                name: "authCodeNonce",
                value: _ctx.authCodeNonce
              }, null, 8, _hoisted_12$2),
              vue.createElementVNode("input", {
                type: "submit",
                class: "btn confirmAuthCode",
                disabled: _ctx.authCode.length !== 6,
                value: _ctx.translate("General_Confirm")
              }, null, 8, _hoisted_13$2)
            ], 8, _hoisted_11$2)
          ], 512), [
            [vue.vShow, _ctx.step >= 3]
          ]),
          vue.createVNode(_component_MatomoDialog, {
            modelValue: _ctx.qrCodeDialogVisible,
            "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.qrCodeDialogVisible = $event),
            onValidation: _cache[5] || (_cache[5] = ($event) => {
              _ctx.closeQrCodeModal();
              _ctx.nextStep();
            }),
            options: { focusSelector: ".modal-action.btn" }
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("div", _hoisted_14$2, [
                vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_Your2FaAuthSecret")), 1),
                vue.createElementVNode("div", _hoisted_15$2, [
                  vue.createElementVNode("div", _hoisted_16$2, [
                    vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ShowCodeModalInstructions1")), 1),
                    vue.createElementVNode("p", null, [
                      vue.createElementVNode("span", _hoisted_17$2, null, 512)
                    ]),
                    vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ShowCodeModalInstructions2")), 1),
                    vue.createElementVNode("div", _hoisted_18$2, [
                      vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                        vue.createTextVNode(vue.toDisplayString(_ctx.newSecret), 1)
                      ])), [
                        [_directive_copy_to_clipboard, {}]
                      ])
                    ]),
                    vue.createElementVNode("p", {
                      innerHTML: _ctx.$sanitize(_ctx.showCodeModalInstructions3)
                    }, null, 8, _hoisted_19$2)
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_20$2, [
                  vue.createElementVNode("div", _hoisted_21$1, [
                    vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_DontHaveOTPApp")), 1),
                    vue.createVNode(_component_InstallOTPApp)
                  ])
                ]),
                vue.createElementVNode("input", {
                  role: "validation",
                  type: "button",
                  value: _ctx.translate("General_Continue")
                }, null, 8, _hoisted_22),
                vue.createElementVNode("input", {
                  role: "no",
                  type: "button",
                  value: _ctx.translate("General_Cancel")
                }, null, 8, _hoisted_23)
              ])
            ]),
            _: 1
          }, 8, ["modelValue", "options"])
        ], 512)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const SetupTwoFactorAuth = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      formData: {
        type: Object,
        required: true
      },
      accessErrorString: String,
      formNonce: {
        type: String,
        required: true
      },
      loginModule: {
        type: String,
        required: true
      },
      piwikUrl: String,
      userLogin: {
        type: String,
        required: true
      },
      contactEmail: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Notification: CoreHome.Notification,
      FormErrors: Login.FormErrors
    },
    computed: {
      learnMoreText() {
        return CoreHome.translate(
          "General_LearnMore",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_27245"),
          "</a>"
        );
      },
      mailToLink() {
        return `mailto:${this.contactEmail}?${CoreHome.MatomoUrl.stringify({
          subject: CoreHome.translate("TwoFactorAuth_NotPossibleToLogIn"),
          body: CoreHome.translate(
            "TwoFactorAuth_LostAuthenticationDevice",
            "\n\n",
            "\n\n",
            this.piwikUrl || "",
            "\n\n",
            this.userLogin,
            CoreHome.externalRawLink("https://matomo.org/faq/how-to/faq_27248")
          )
        })}`;
      },
      logoutLink() {
        return `?${CoreHome.MatomoUrl.stringify({
          module: this.loginModule,
          action: "logout"
        })}`;
      },
      formDataAttributes() {
        return Object.fromEntries(
          this.formData.attributes.split(/\s+/g).filter((s) => s).map((pair) => pair.split("=")).map(([name, value]) => [
            name,
            CoreHome.Matomo.helper.htmlDecode(value.substr(1, value.length - 2))
          ])
        );
      }
    }
  });
  const _hoisted_1$2 = { class: "message_container" };
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_4$1 = { class: "row" };
  const _hoisted_5$1 = { class: "col s12 input-field" };
  const _hoisted_6$1 = ["value"];
  const _hoisted_7$1 = /* @__PURE__ */ vue.createElementVNode("input", {
    type: "text",
    name: "form_authcode",
    placeholder: "",
    id: "form_authcode",
    class: "input",
    value: "",
    size: "20",
    autocorrect: "off",
    autocapitalize: "none",
    autocomplete: "one-time-code",
    tabindex: "10",
    autofocus: "autofocus"
  }, null, -1);
  const _hoisted_8$1 = { for: "form_authcode" };
  const _hoisted_9$1 = /* @__PURE__ */ vue.createElementVNode("i", { class: "icon-user icon" }, null, -1);
  const _hoisted_10$1 = { class: "row actions" };
  const _hoisted_11$1 = { class: "col s12" };
  const _hoisted_12$1 = ["value"];
  const _hoisted_13$1 = ["innerHTML"];
  const _hoisted_14$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_15$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_16$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_17$1 = ["href"];
  const _hoisted_18$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_19$1 = ["href"];
  const _hoisted_20$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_21 = ["href"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FormErrors = vue.resolveComponent("FormErrors");
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("TwoFactorAuth_TwoFactorAuthentication")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", _hoisted_1$2, [
          vue.createVNode(_component_FormErrors, {
            "form-errors": _ctx.formData.errors
          }, null, 8, ["form-errors"]),
          _ctx.accessErrorString ? (vue.openBlock(), vue.createBlock(_component_Notification, {
            key: 0,
            noclear: true,
            context: "error"
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("General_Error")), 1),
              vue.createTextVNode(": "),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.accessErrorString)
              }, null, 8, _hoisted_2$2),
              _hoisted_3$2
            ]),
            _: 1
          })) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("form", vue.mergeProps(_ctx.formDataAttributes, { class: "loginTwoFaForm" }), [
          vue.createElementVNode("div", _hoisted_4$1, [
            vue.createElementVNode("div", _hoisted_5$1, [
              vue.createElementVNode("input", {
                type: "hidden",
                name: "form_nonce",
                id: "login_form_nonce",
                value: _ctx.formNonce
              }, null, 8, _hoisted_6$1),
              _hoisted_7$1,
              vue.createElementVNode("label", _hoisted_8$1, [
                _hoisted_9$1,
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("TwoFactorAuth_AuthenticationCode")), 1)
              ])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_10$1, [
            vue.createElementVNode("div", _hoisted_11$1, [
              vue.createElementVNode("input", {
                class: "submit btn btn-block",
                id: "login_form_submit",
                type: "submit",
                value: _ctx.translate("TwoFactorAuth_Verify"),
                tabindex: "100"
              }, null, 8, _hoisted_12$1)
            ])
          ])
        ], 16),
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("TwoFactorAuth_VerifyIdentifyExplanation")) + " ", 1),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
          }, null, 8, _hoisted_13$1),
          _hoisted_14$1,
          _hoisted_15$1,
          vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_DontHaveYourMobileDevice")), 1),
          _hoisted_16$1,
          vue.createElementVNode("a", {
            href: _ctx.externalRawLink("https://matomo.org/faq/how-to/faq_27248"),
            rel: "noreferrer noopener",
            target: "_blank"
          }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_EnterRecoveryCodeInstead")), 9, _hoisted_17$1),
          _hoisted_18$1,
          vue.createElementVNode("a", {
            href: _ctx.mailToLink,
            rel: "noreferrer noopener"
          }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_AskSuperUserResetAuthenticationCode")), 9, _hoisted_19$1),
          _hoisted_20$1,
          vue.createElementVNode("a", {
            href: _ctx.logoutLink,
            rel: "noreferrer noopener"
          }, vue.toDisplayString(_ctx.translate("General_Logout")), 9, _hoisted_21)
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const LoginTwoFactorAuth = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    computed: {
      userSecurityLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "userSecurity"
        }))}`;
      }
    }
  });
  const _hoisted_1$1 = { class: "successMessage" };
  const _hoisted_2$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_3$1 = ["href"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, { class: "twoFactorSetupFinished" }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("h2", _hoisted_1$1, vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupFinishedTitle")), 1),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_SetupFinishedSubtitle")), 1),
        vue.createElementVNode("p", null, [
          _hoisted_2$1,
          vue.createElementVNode("a", {
            class: "btn",
            href: _ctx.userSecurityLink
          }, vue.toDisplayString(_ctx.translate("General_Continue")), 9, _hoisted_3$1)
        ])
      ]),
      _: 1
    });
  }
  const SetupFinished = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      isEnabled: Boolean,
      isForced: Boolean,
      disableNonce: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    computed: {
      contentTitle() {
        const part1 = CoreHome.translate("TwoFactorAuth_TwoFactorAuthentication");
        const part2 = CoreHome.translate("TwoFactorAuth_TwoFAShort");
        return `${part1} (${part2})`;
      },
      twoFactorAuthIntro() {
        return CoreHome.translate(
          "TwoFactorAuth_TwoFactorAuthenticationIntro",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_27245"),
          "</a>"
        );
      },
      setupTwoFactorAuthLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "TwoFactorAuth",
          action: "setupTwoFactorAuth"
        }))}`;
      },
      disableTwoFactorAuthLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "TwoFactorAuth",
          action: "disableTwoFactorAuth",
          disableNonce: this.disableNonce
        }))}`;
      },
      showRecoveryCodesLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "TwoFactorAuth",
          action: "showRecoveryCodes"
        }))}`;
      }
    },
    methods: {
      onDisable2FaLinkClick() {
        const nonce = this.disableNonce;
        CoreHome.Matomo.helper.modalConfirm(this.$refs.confirmDisable2FA, {
          yes() {
            CoreHome.MatomoUrl.updateUrl({
              module: "TwoFactorAuth",
              action: "disableTwoFactorAuth",
              disableNonce: nonce
            });
          }
        });
      }
    }
  });
  const _hoisted_1 = ["innerHTML"];
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = { class: "twoFaStatusEnabled" };
  const _hoisted_4 = { key: 1 };
  const _hoisted_5 = { key: 0 };
  const _hoisted_6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_8 = ["href"];
  const _hoisted_9 = { key: 1 };
  const _hoisted_10 = ["href"];
  const _hoisted_11 = ["href"];
  const _hoisted_12 = ["value"];
  const _hoisted_13 = ["href"];
  const _hoisted_14 = { key: 2 };
  const _hoisted_15 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_16 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_17 = ["href"];
  const _hoisted_18 = {
    id: "confirmDisable2FA",
    class: "ui-confirm",
    ref: "confirmDisable2FA"
  };
  const _hoisted_19 = ["value"];
  const _hoisted_20 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.contentTitle,
      class: "userSettings2FA"
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.twoFactorAuthIntro)
        }, null, 8, _hoisted_1),
        _ctx.isEnabled ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2, [
          vue.createElementVNode("strong", _hoisted_3, vue.toDisplayString(_ctx.translate("TwoFactorAuth_TwoFactorAuthenticationIsEnabled")), 1)
        ])) : vue.createCommentVNode("", true),
        _ctx.isEnabled ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_4, [
          _ctx.isForced ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("TwoFactorAuth_TwoFactorAuthenticationRequired")) + " ", 1),
            _hoisted_6,
            _hoisted_7,
            vue.createElementVNode("a", {
              class: "btn btn-link enable2FaLink",
              href: _ctx.setupTwoFactorAuthLink,
              style: { "margin-right": "3.5px" }
            }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ConfigureDifferentDevice")), 9, _hoisted_8)
          ])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_9, [
            vue.createElementVNode("a", {
              class: "btn btn-link enable2FaLink",
              href: _ctx.setupTwoFactorAuthLink,
              style: { "margin-right": "3.5px" }
            }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ConfigureDifferentDevice")), 9, _hoisted_10),
            vue.createElementVNode("a", {
              href: _ctx.disableTwoFactorAuthLink,
              style: { "display": "none" },
              id: "disable2fa"
            }, "disable2fa", 8, _hoisted_11),
            vue.createElementVNode("input", {
              type: "button",
              class: "btn btn-link disable2FaLink",
              onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onDisable2FaLinkClick()),
              value: _ctx.translate("TwoFactorAuth_DisableTwoFA"),
              style: { "margin-right": "3.5px" }
            }, null, 8, _hoisted_12)
          ])),
          vue.createElementVNode("a", {
            class: "btn btn-link showRecoveryCodesLink",
            href: _ctx.showRecoveryCodesLink
          }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ShowRecoveryCodes")), 9, _hoisted_13)
        ])) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_14, [
          vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_TwoFactorAuthenticationIsDisabled")), 1),
          _hoisted_15,
          _hoisted_16,
          vue.createElementVNode("a", {
            class: "btn btn-link enable2FaLink",
            href: _ctx.setupTwoFactorAuthLink
          }, vue.toDisplayString(_ctx.translate("TwoFactorAuth_EnableTwoFA")), 9, _hoisted_17)
        ])),
        vue.createElementVNode("div", _hoisted_18, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("TwoFactorAuth_ConfirmDisableTwoFA")), 1),
          vue.createElementVNode("input", {
            role: "yes",
            type: "button",
            value: _ctx.translate("General_Yes")
          }, null, 8, _hoisted_19),
          vue.createElementVNode("input", {
            role: "no",
            type: "button",
            value: _ctx.translate("General_No")
          }, null, 8, _hoisted_20)
        ], 512)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const UserSettings = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.LoginTwoFactorAuth = LoginTwoFactorAuth;
  exports2.SetupFinished = SetupFinished;
  exports2.SetupTwoFactorAuth = SetupTwoFactorAuth;
  exports2.ShowRecoveryCodes = ShowRecoveryCodes;
  exports2.ShowRecoveryCodesPage = ShowRecoveryCodesPage;
  exports2.UserSettings = UserSettings;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
