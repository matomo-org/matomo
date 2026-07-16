(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.CoreAdminHome = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";
  const _sfc_main$8 = vue.defineComponent({
    props: {
      enableBrowserTriggerArchiving: Boolean,
      showSegmentArchiveTriggerInfo: Boolean,
      isGeneralSettingsAdminEnabled: Boolean,
      showWarningCron: Boolean,
      todayArchiveTimeToLive: Number,
      todayArchiveTimeToLiveDefault: Number
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    data() {
      return {
        isLoading: false,
        enableBrowserTriggerArchivingValue: this.enableBrowserTriggerArchiving ? 1 : 0,
        todayArchiveTimeToLiveValue: this.todayArchiveTimeToLive
      };
    },
    watch: {
      enableBrowserTriggerArchiving(newValue) {
        this.enableBrowserTriggerArchivingValue = newValue ? 1 : 0;
      },
      todayArchiveTimeToLive(newValue) {
        this.todayArchiveTimeToLiveValue = newValue;
      }
    },
    computed: {
      archivingTriggerDesc() {
        let result = "";
        result += CoreHome.translate(
          "General_ArchivingTriggerDescription",
          CoreHome.externalLink("https://matomo.org/docs/setup-auto-archiving/"),
          "</a>"
        );
        if (this.showSegmentArchiveTriggerInfo) {
          result += CoreHome.translate("General_ArchivingTriggerSegment");
        }
        return result;
      },
      archivingInlineHelp() {
        let result = CoreHome.translate("General_ArchivingInlineHelp");
        result += "<br/>";
        result += CoreHome.translate(
          "General_SeeTheOfficialDocumentationForMoreInformation",
          CoreHome.externalLink("https://matomo.org/docs/setup-auto-archiving/"),
          "</a>"
        );
        return result;
      }
    },
    methods: {
      save() {
        this.isLoading = true;
        CoreHome.AjaxHelper.post({ module: "API", method: "CoreAdminHome.setArchiveSettings" }, {
          enableBrowserTriggerArchiving: this.enableBrowserTriggerArchivingValue,
          todayArchiveTimeToLive: this.todayArchiveTimeToLiveValue
        }).then(() => {
          this.isLoading = false;
          const notificationId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            type: "transient",
            id: "generalSettings",
            context: "success"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationId);
        }).finally(() => {
          this.isLoading = false;
        });
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
  const _hoisted_1$8 = { class: "form-group row" };
  const _hoisted_2$7 = { class: "col s12" };
  const _hoisted_3$7 = { class: "col s12 m6" };
  const _hoisted_4$7 = {
    class: "form-description",
    style: { "margin-left": "4px" }
  };
  const _hoisted_5$5 = { for: "enableBrowserTriggerArchiving2" };
  const _hoisted_6$5 = ["innerHTML"];
  const _hoisted_7$5 = { class: "col s12 m6" };
  const _hoisted_8$5 = ["innerHTML"];
  const _hoisted_9$5 = { class: "form-group row" };
  const _hoisted_10$4 = { class: "col s12" };
  const _hoisted_11$3 = { class: "input-field col s12 m6" };
  const _hoisted_12$3 = ["disabled"];
  const _hoisted_13$3 = { class: "form-description" };
  const _hoisted_14$3 = { class: "col s12 m6" };
  const _hoisted_15$3 = {
    key: 0,
    class: "form-help"
  };
  const _hoisted_16$3 = { key: 0 };
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("CoreAdminHome_ArchivingSettings"),
      anchor: "archivingSettings",
      class: "matomo-archiving-settings"
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_1$8, [
            vue.createElementVNode("h3", _hoisted_2$7, vue.toDisplayString(_ctx.translate("General_AllowPiwikArchivingToTriggerBrowser")), 1),
            vue.createElementVNode("div", _hoisted_3$7, [
              vue.createElementVNode("p", null, [
                vue.createElementVNode("label", null, [
                  vue.withDirectives(vue.createElementVNode("input", {
                    type: "radio",
                    id: "enableBrowserTriggerArchiving1",
                    name: "enableBrowserTriggerArchiving",
                    value: "1",
                    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.enableBrowserTriggerArchivingValue = $event)
                  }, null, 512), [
                    [vue.vModelRadio, _ctx.enableBrowserTriggerArchivingValue]
                  ]),
                  vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
                  vue.createElementVNode("span", _hoisted_4$7, vue.toDisplayString(_ctx.translate("General_Default")), 1)
                ])
              ]),
              vue.createElementVNode("p", null, [
                vue.createElementVNode("label", _hoisted_5$5, [
                  vue.withDirectives(vue.createElementVNode("input", {
                    type: "radio",
                    id: "enableBrowserTriggerArchiving2",
                    name: "enableBrowserTriggerArchiving",
                    value: "0",
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.enableBrowserTriggerArchivingValue = $event)
                  }, null, 512), [
                    [vue.vModelRadio, _ctx.enableBrowserTriggerArchivingValue]
                  ]),
                  vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("General_No")), 1),
                  vue.createElementVNode("span", {
                    class: "form-description",
                    innerHTML: _ctx.$sanitize(_ctx.archivingTriggerDesc),
                    style: { "margin-left": "4px" }
                  }, null, 8, _hoisted_6$5)
                ])
              ])
            ]),
            vue.createElementVNode("div", _hoisted_7$5, [
              vue.createElementVNode("div", {
                class: "form-help",
                innerHTML: _ctx.$sanitize(_ctx.archivingInlineHelp)
              }, null, 8, _hoisted_8$5)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_9$5, [
            vue.createElementVNode("h3", _hoisted_10$4, vue.toDisplayString(_ctx.translate("General_ReportsContainingTodayWillBeProcessedAtMostEvery")), 1),
            vue.createElementVNode("div", _hoisted_11$3, [
              vue.withDirectives(vue.createElementVNode("input", {
                type: "text",
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.todayArchiveTimeToLiveValue = $event),
                id: "todayArchiveTimeToLive",
                disabled: !_ctx.isGeneralSettingsAdminEnabled
              }, null, 8, _hoisted_12$3), [
                [vue.vModelText, _ctx.todayArchiveTimeToLiveValue]
              ]),
              vue.createElementVNode("span", _hoisted_13$3, vue.toDisplayString(_ctx.translate("General_RearchiveTimeIntervalOnlyForTodayReports")), 1)
            ]),
            vue.createElementVNode("div", _hoisted_14$3, [
              _ctx.isGeneralSettingsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15$3, [
                _ctx.showWarningCron ? (vue.openBlock(), vue.createElementBlock("strong", _hoisted_16$3, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_NewReportsWillBeProcessedByCron")), 1),
                  _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ReportsWillBeProcessedAtMostEveryHour")) + " " + vue.toDisplayString(_ctx.translate("General_IfArchivingIsFastYouCanSetupCronRunMoreOften")), 1),
                  _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1))
                ])) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_SmallTrafficYouCanLeaveDefault", _ctx.todayArchiveTimeToLiveDefault)) + " ", 1),
                _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1)),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_MediumToHighTrafficItIsRecommendedTo", 1800, 3600)), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_SaveButton, {
              saving: _ctx.isLoading,
              onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.save())
            }, null, 8, ["saving"])
          ])
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ArchivingSettings = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const { $: $$2 } = window;
  const _sfc_main$7 = vue.defineComponent({
    props: {
      fileUploadEnabled: {
        type: Boolean,
        required: true
      },
      logosWriteable: {
        type: Boolean,
        required: true
      },
      useCustomLogo: {
        type: Boolean,
        required: true
      },
      pathUserLogoDirectory: {
        type: String,
        required: true
      },
      pathUserLogo: {
        type: String,
        required: true
      },
      pathUserLogoSmall: {
        type: String,
        required: true
      },
      pathUserLogoSvg: {
        type: String,
        required: true
      },
      hasUserLogo: {
        type: Boolean,
        required: true
      },
      pathUserFavicon: {
        type: String,
        required: true
      },
      hasUserFavicon: {
        type: Boolean,
        required: true
      },
      isPluginsAdminEnabled: {
        type: Boolean,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isLoading: false,
        enabled: this.useCustomLogo,
        customLogo: this.pathUserLogo,
        customFavicon: this.pathUserFavicon,
        showUploadError: false,
        currentLogoSrcExists: this.hasUserLogo,
        currentFaviconSrcExists: this.hasUserFavicon,
        newLogoBase64Src: "",
        newFaviconBase64Src: ""
      };
    },
    computed: {
      tokenAuth() {
        return CoreHome.Matomo.token_auth;
      },
      logosNotWriteableWarning() {
        return CoreHome.translate(
          "CoreAdminHome_LogoNotWriteableInstruction",
          `<code>${this.pathUserLogoDirectory}</code><br/>`,
          `${this.pathUserLogo}, ${this.pathUserLogoSmall}, ${this.pathUserLogoSvg}`
        );
      },
      help() {
        if (!this.isPluginsAdminEnabled) {
          return void 0;
        }
        const giveUsFeedbackText = `"${CoreHome.translate("General_GiveUsYourFeedback")}"`;
        const linkStart = '<a href="?module=CorePluginsAdmin&action=plugins" rel="noreferrer noopener" target="_blank">';
        return CoreHome.translate(
          "CoreAdminHome_CustomLogoFeedbackInfo",
          giveUsFeedbackText,
          linkStart,
          "</a>"
        );
      },
      pathUserLogoSrc() {
        if (this.newLogoBase64Src) {
          return `data:image/png;base64, ${this.newLogoBase64Src}`;
        }
        if (this.currentLogoSrcExists && this.pathUserLogo) {
          return this.pathUserLogo;
        }
        return "";
      },
      pathUserFaviconSrc() {
        if (this.newFaviconBase64Src) {
          return `data:image/png;base64, ${this.newFaviconBase64Src}`;
        }
        if (this.currentFaviconSrcExists && this.pathUserFavicon) {
          return this.pathUserFavicon;
        }
        return "";
      }
    },
    methods: {
      onUseCustomLogoChange(newValue) {
        this.enabled = newValue;
      },
      onCustomLogoChange(newValue) {
        this.customLogo = newValue;
        this.updateLogo();
      },
      onFaviconChange(newValue) {
        this.customFavicon = newValue;
        this.updateLogo();
      },
      save() {
        this.isLoading = true;
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "CoreAdminHome.setBrandingSettings"
          },
          {
            useCustomLogo: this.enabled ? "1" : "0",
            hasCustomLogo: this.newLogoBase64Src.length > 0 || this.customLogo ? "1" : "0",
            hasCustomFavicon: this.newFaviconBase64Src.length > 0 || this.customFavicon ? "1" : "0"
          }
        ).then((response) => {
          this.enabled = !!response.useCustomLogo;
          if (response.customLogoPath) {
            this.customLogo = response.customLogoPath;
          }
          if (response.customFaviconPath) {
            this.customFavicon = response.customFaviconPath;
          }
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            type: "transient",
            id: "generalSettings",
            context: "success"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).finally(() => {
          if (!this.enabled) {
            this.currentLogoSrcExists = false;
            this.currentFaviconSrcExists = false;
            this.customLogo = "";
            this.customFavicon = "";
            this.newFaviconBase64Src = "";
            this.newLogoBase64Src = "";
          }
          this.isLoading = false;
        });
      },
      updateLogo() {
        const isSubmittingLogo = !!this.customLogo;
        const isSubmittingFavicon = !!this.customFavicon;
        if (!isSubmittingLogo && !isSubmittingFavicon) {
          return;
        }
        this.showUploadError = false;
        const frameName = `upload${(/* @__PURE__ */ new Date()).getTime()}`;
        const uploadFrame = $$2(`<iframe name="${frameName}" />`);
        uploadFrame.css("display", "none");
        uploadFrame.on("load", () => {
          setTimeout(() => {
            let frameContent = "";
            let frameContentJSON = {};
            try {
              frameContent = ($$2(uploadFrame.contents()).find("body").text() || "").trim();
              frameContentJSON = JSON.parse(frameContent);
            } catch (e) {
            }
            if (frameContent && Object.keys(frameContentJSON).length === 0) {
              this.showUploadError = true;
            } else {
              if (isSubmittingLogo && frameContentJSON.logo) {
                this.newLogoBase64Src = frameContentJSON.logo;
              }
              if (isSubmittingFavicon && frameContentJSON.favicon) {
                this.newFaviconBase64Src = frameContentJSON.favicon;
              }
            }
            if (frameContent) {
              uploadFrame.remove();
            }
          }, 1e3);
        });
        $$2("body:first").append(uploadFrame);
        const submittingForm = $$2(this.$refs.logoUploadForm);
        submittingForm.attr("target", frameName);
        submittingForm.submit();
        this.customLogo = "";
        this.customFavicon = "";
      }
    }
  });
  const _hoisted_1$7 = { id: "logoSettings" };
  const _hoisted_2$6 = {
    id: "logoUploadForm",
    ref: "logoUploadForm",
    method: "post",
    enctype: "multipart/form-data",
    action: "index.php?module=CoreAdminHome&format=json&action=uploadCustomLogo"
  };
  const _hoisted_3$6 = { key: 0 };
  const _hoisted_4$6 = ["value"];
  const _hoisted_5$4 = { key: 0 };
  const _hoisted_6$4 = {
    key: 0,
    class: "alert alert-warning uploaderror"
  };
  const _hoisted_7$4 = { class: "row" };
  const _hoisted_8$4 = { class: "col s12" };
  const _hoisted_9$4 = ["src"];
  const _hoisted_10$3 = { class: "row" };
  const _hoisted_11$2 = { class: "col s12" };
  const _hoisted_12$2 = ["src"];
  const _hoisted_13$2 = { key: 1 };
  const _hoisted_14$2 = ["innerHTML"];
  const _hoisted_15$2 = { key: 1 };
  const _hoisted_16$2 = { class: "alert alert-warning" };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("CoreAdminHome_BrandingSettings"),
      anchor: "brandingSettings"
    }, {
      default: vue.withCtx(() => [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CoreAdminHome_CustomLogoHelpText")), 1),
          vue.createVNode(_component_Field, {
            name: "useCustomLogo",
            uicontrol: "checkbox",
            "model-value": _ctx.enabled,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.onUseCustomLogoChange($event)),
            title: _ctx.translate("CoreAdminHome_UseCustomLogo"),
            "inline-help": _ctx.help
          }, null, 8, ["model-value", "title", "inline-help"]),
          vue.withDirectives(vue.createElementVNode("div", _hoisted_1$7, [
            vue.createElementVNode("form", _hoisted_2$6, [
              _ctx.fileUploadEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$6, [
                vue.createElementVNode("input", {
                  type: "hidden",
                  name: "token_auth",
                  value: _ctx.tokenAuth
                }, null, 8, _hoisted_4$6),
                _cache[4] || (_cache[4] = vue.createElementVNode("input", {
                  type: "hidden",
                  name: "force_api_session",
                  value: "1"
                }, null, -1)),
                _ctx.logosWriteable ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$4, [
                  vue.createVNode(vue.Transition, { name: "fade-out" }, {
                    default: vue.withCtx(() => [
                      _ctx.showUploadError ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$4, vue.toDisplayString(_ctx.translate("CoreAdminHome_LogoUploadFailed")), 1)) : vue.createCommentVNode("", true)
                    ]),
                    _: 1
                  }),
                  vue.createVNode(_component_Field, {
                    uicontrol: "file",
                    name: "customLogo",
                    "model-value": _ctx.customLogo,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.onCustomLogoChange($event)),
                    title: _ctx.translate("CoreAdminHome_LogoUpload"),
                    "inline-help": _ctx.translate("CoreAdminHome_LogoUploadHelp", "JPG / PNG / GIF", "110")
                  }, null, 8, ["model-value", "title", "inline-help"]),
                  vue.createElementVNode("div", _hoisted_7$4, [
                    vue.createElementVNode("div", _hoisted_8$4, [
                      vue.createElementVNode("img", {
                        src: _ctx.pathUserLogoSrc,
                        id: "currentLogo",
                        style: { "max-height": "150px" },
                        ref: "currentLogo"
                      }, null, 8, _hoisted_9$4)
                    ])
                  ]),
                  vue.createVNode(_component_Field, {
                    uicontrol: "file",
                    name: "customFavicon",
                    "model-value": _ctx.customFavicon,
                    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.onFaviconChange($event)),
                    title: _ctx.translate("CoreAdminHome_FaviconUpload"),
                    "inline-help": _ctx.translate("CoreAdminHome_LogoUploadHelp", "JPG / PNG / GIF", "16")
                  }, null, 8, ["model-value", "title", "inline-help"]),
                  vue.createElementVNode("div", _hoisted_10$3, [
                    vue.createElementVNode("div", _hoisted_11$2, [
                      vue.createElementVNode("img", {
                        src: _ctx.pathUserFaviconSrc,
                        id: "currentFavicon",
                        width: "16",
                        height: "16",
                        ref: "currentFavicon"
                      }, null, 8, _hoisted_12$2)
                    ])
                  ])
                ])) : vue.createCommentVNode("", true),
                !_ctx.logosWriteable ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_13$2, [
                  vue.createElementVNode("div", {
                    class: "alert alert-warning",
                    innerHTML: _ctx.$sanitize(_ctx.logosNotWriteableWarning)
                  }, null, 8, _hoisted_14$2)
                ])) : vue.createCommentVNode("", true)
              ])) : vue.createCommentVNode("", true),
              !_ctx.fileUploadEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15$2, [
                vue.createElementVNode("div", _hoisted_16$2, vue.toDisplayString(_ctx.translate("CoreAdminHome_FileUploadDisabled", "file_uploads=1")), 1)
              ])) : vue.createCommentVNode("", true)
            ], 512)
          ], 512), [
            [vue.vShow, _ctx.enabled]
          ]),
          vue.createVNode(_component_SaveButton, {
            onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.save()),
            saving: _ctx.isLoading
          }, null, 8, ["saving"])
        ])), [
          [_directive_form]
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const BrandingSettings = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = vue.defineComponent({
    props: {
      mail: {
        type: Object,
        required: true
      },
      mailTypes: {
        type: Object,
        required: true
      },
      mailEncryptions: {
        type: Object,
        required: true
      }
    },
    data() {
      const mail = this.mail;
      return {
        isLoading: false,
        showPasswordConfirmation: false,
        enabled: mail.transport === "smtp",
        mailHost: mail.host,
        passwordChanged: false,
        mailPort: mail.port,
        mailType: mail.type,
        mailUsername: mail.username,
        mailPassword: mail.password ? "******" : "",
        mailFromAddress: mail.noreply_email_address,
        mailFromName: mail.noreply_email_name,
        mailEncryption: mail.encryption
      };
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      Form: CorePluginsAdmin.Form,
      AutoClearPassword: CoreHome.AutoClearPassword
    },
    computed: {
      passwordHelp() {
        const part1 = `${CoreHome.translate("General_OnlyEnterIfRequiredPassword")}<br/>`;
        const part2 = `${CoreHome.translate("General_WarningPasswordStored", "<strong>", "</strong>")}<br/>`;
        return `${part1}
${part2}`;
      }
    },
    methods: {
      onUpdateMailHost(newValue) {
        this.mailHost = newValue;
        if (this.passwordChanged) {
          return;
        }
        this.mailPassword = "";
        this.passwordChanged = true;
      },
      onMailPasswordChange(newValue) {
        this.mailPassword = newValue;
        this.passwordChanged = true;
      },
      save(password) {
        this.isLoading = true;
        const mailSettings = {
          mailUseSmtp: this.enabled ? "1" : "0",
          mailPort: this.mailPort,
          mailHost: this.mailHost,
          mailType: this.mailType,
          mailUsername: this.mailUsername,
          mailFromAddress: this.mailFromAddress,
          mailFromName: this.mailFromName,
          mailEncryption: this.mailEncryption,
          passwordConfirmation: password
        };
        if (this.passwordChanged) {
          mailSettings.mailPassword = this.mailPassword;
        }
        CoreHome.AjaxHelper.post(
          { module: "CoreAdminHome", action: "setMailSettings" },
          mailSettings,
          { withTokenInUrl: true }
        ).then(() => {
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            type: "transient",
            id: "generalSettings",
            context: "success"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).finally(() => {
          this.isLoading = false;
        });
      }
    }
  });
  const _hoisted_1$6 = { id: "smtpSettings" };
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_auto_clear_password = vue.resolveDirective("auto-clear-password");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("CoreAdminHome_EmailServerSettings"),
      anchor: "mailSettings"
    }, {
      default: vue.withCtx(() => [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "mailUseSmtp",
            modelValue: _ctx.enabled,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.enabled = $event),
            title: _ctx.translate("General_UseSMTPServerForEmail"),
            "inline-help": _ctx.translate("General_SelectYesIfYouWantToSendEmailsViaServer")
          }, null, 8, ["modelValue", "title", "inline-help"]),
          vue.withDirectives(vue.createElementVNode("div", _hoisted_1$6, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "mailHost",
              "model-value": _ctx.mailHost,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.onUpdateMailHost($event)),
              title: _ctx.translate("General_SmtpServerAddress")
            }, null, 8, ["model-value", "title"]),
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "mailPort",
              modelValue: _ctx.mailPort,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.mailPort = $event),
              title: _ctx.translate("General_SmtpPort"),
              "inline-help": _ctx.translate("General_OptionalSmtpPort")
            }, null, 8, ["modelValue", "title", "inline-help"]),
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "mailType",
              modelValue: _ctx.mailType,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.mailType = $event),
              title: _ctx.translate("General_AuthenticationMethodSmtp"),
              options: _ctx.mailTypes,
              "inline-help": _ctx.translate("General_OnlyUsedIfUserPwdIsSet")
            }, null, 8, ["modelValue", "title", "options", "inline-help"]),
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "mailUsername",
              modelValue: _ctx.mailUsername,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.mailUsername = $event),
              title: _ctx.translate("General_SmtpUsername"),
              "inline-help": _ctx.translate("General_OnlyEnterIfRequired"),
              autocomplete: "off"
            }, null, 8, ["modelValue", "title", "inline-help"]),
            vue.withDirectives(vue.createVNode(_component_Field, {
              uicontrol: "password",
              name: "mailPassword",
              "model-value": _ctx.mailPassword,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.onMailPasswordChange($event)),
              onClick: _cache[6] || (_cache[6] = ($event) => {
                !_ctx.passwordChanged && $event.target.select();
              }),
              title: _ctx.translate("General_SmtpPassword"),
              "inline-help": _ctx.passwordHelp,
              autocomplete: "off"
            }, null, 8, ["model-value", "title", "inline-help"]), [
              [_directive_auto_clear_password]
            ]),
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "mailFromAddress",
              modelValue: _ctx.mailFromAddress,
              "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.mailFromAddress = $event),
              title: _ctx.translate("General_SmtpFromAddress"),
              "inline-help": _ctx.translate("General_SmtpFromEmailHelp", _ctx.mailHost),
              autocomplete: "off"
            }, null, 8, ["modelValue", "title", "inline-help"]),
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "mailFromName",
              modelValue: _ctx.mailFromName,
              "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.mailFromName = $event),
              title: _ctx.translate("General_SmtpFromName"),
              "inline-help": _ctx.translate("General_NameShownInTheSenderColumn"),
              autocomplete: "off"
            }, null, 8, ["modelValue", "title", "inline-help"]),
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "mailEncryption",
              modelValue: _ctx.mailEncryption,
              "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.mailEncryption = $event),
              title: _ctx.translate("General_SmtpEncryption"),
              options: _ctx.mailEncryptions,
              "inline-help": _ctx.translate("General_EncryptedSmtpTransport")
            }, null, 8, ["modelValue", "title", "options", "inline-help"])
          ], 512), [
            [vue.vShow, _ctx.enabled]
          ]),
          vue.createVNode(_component_SaveButton, {
            onConfirm: _cache[10] || (_cache[10] = ($event) => _ctx.showPasswordConfirmation = true),
            saving: _ctx.isLoading
          }, null, 8, ["saving"]),
          vue.createVNode(_component_PasswordConfirmation, {
            modelValue: _ctx.showPasswordConfirmation,
            "onUpdate:modelValue": _cache[11] || (_cache[11] = ($event) => _ctx.showPasswordConfirmation = $event),
            onConfirmed: _cache[12] || (_cache[12] = ($event) => _ctx.save($event))
          }, null, 8, ["modelValue"])
        ])), [
          [_directive_form]
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const SmtpSettings = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  function getHostNameFromUrl(url) {
    const urlObj = new URL(url);
    return urlObj.hostname;
  }
  function getCustomVarArray(cvars) {
    return cvars.filter((cv) => !!cv.name).map((cv) => [cv.name, cv.value]);
  }
  const piwikHost$1 = window.location.host;
  const piwikPath$1 = window.location.pathname.substring(0, window.location.pathname.lastIndexOf("/"));
  const _sfc_main$5 = vue.defineComponent({
    props: {
      site: {
        type: Object,
        required: true
      },
      maxCustomVariables: Number,
      serverSideDoNotTrackEnabled: Boolean
    },
    data() {
      return {
        showAdvanced: false,
        trackAllSubdomains: false,
        isLoading: false,
        siteUrls: {},
        siteExcludedQueryParams: {},
        siteExcludedReferrers: {},
        crossDomain: false,
        groupByDomain: false,
        trackAllAliases: false,
        trackNoScript: false,
        trackCustomVars: false,
        customVars: [],
        canAddMoreCustomVariables: !!this.maxCustomVariables && this.maxCustomVariables > 0,
        doNotTrack: false,
        disableCookies: false,
        useCustomCampaignParams: false,
        customCampaignName: "",
        customCampaignKeyword: "",
        trackingCodeAbortController: null,
        disableCampaignParameters: false
      };
    },
    emits: ["updateTrackingCode"],
    components: {
      Field: CorePluginsAdmin.Field
    },
    created() {
      if (this.site && this.site.id) {
        this.onSiteChanged(this.site);
      }
      this.onCustomVarNameKeydown = CoreHome.debounce(this.onCustomVarNameKeydown, 100);
      this.onCustomVarValueKeydown = CoreHome.debounce(this.onCustomVarValueKeydown, 100);
      this.addCustomVar();
    },
    watch: {
      site(newValue) {
        this.onSiteChanged(newValue);
      }
    },
    methods: {
      onSiteChanged(newValue) {
        const idSite = newValue.id;
        const promises = [];
        if (!this.siteUrls[idSite]) {
          this.isLoading = true;
          promises.push(
            CoreHome.AjaxHelper.fetch({
              module: "API",
              method: "SitesManager.getSiteUrlsFromId",
              idSite,
              filter_limit: "-1"
            }).then((data) => {
              this.siteUrls[idSite] = data || [];
            })
          );
        }
        if (!this.siteExcludedQueryParams[idSite]) {
          this.isLoading = true;
          promises.push(
            CoreHome.AjaxHelper.fetch({
              module: "API",
              method: "SitesManager.getExcludedQueryParameters",
              idSite,
              filter_limit: "-1"
            }).then((data) => {
              this.siteExcludedQueryParams[idSite] = data || [];
            })
          );
        }
        if (!this.siteExcludedReferrers[idSite]) {
          this.isLoading = true;
          promises.push(
            CoreHome.AjaxHelper.fetch({
              module: "API",
              method: "SitesManager.getExcludedReferrers",
              idSite,
              filter_limit: "-1"
            }).then((data) => {
              this.siteExcludedReferrers[idSite] = [];
              Object.values(data || []).forEach((referrer) => {
                this.siteExcludedReferrers[idSite].push(referrer.replace(/^https?:\/\//, ""));
              });
            })
          );
        }
        Promise.all(promises).then(() => {
          this.isLoading = false;
          this.updateCurrentSiteInfo();
          this.updateTrackingCode();
        });
      },
      updateCurrentSiteInfo() {
        if (!this.hasManySiteUrls) {
          this.crossDomain = false;
        }
      },
      onCrossDomainToggle() {
        if (this.crossDomain) {
          this.trackAllAliases = true;
        }
      },
      updateTrackingCode() {
        const params = {
          piwikUrl: `${piwikHost$1}${piwikPath$1}`,
          groupPageTitlesByDomain: this.groupByDomain ? 1 : 0,
          mergeSubdomains: this.trackAllSubdomains ? 1 : 0,
          mergeAliasUrls: this.trackAllAliases ? 1 : 0,
          visitorCustomVariables: this.trackCustomVars ? getCustomVarArray(this.customVars) : 0,
          customCampaignNameQueryParam: null,
          customCampaignKeywordParam: null,
          doNotTrack: this.doNotTrack ? 1 : 0,
          disableCookies: this.disableCookies ? 1 : 0,
          crossDomain: this.crossDomain ? 1 : 0,
          trackNoScript: this.trackNoScript ? 1 : 0,
          forceMatomoEndpoint: 1,
          disableCampaignParameters: this.disableCampaignParameters ? 1 : 0
        };
        if (this.siteExcludedQueryParams[this.site.id]) {
          params.excludedQueryParams = this.siteExcludedQueryParams[this.site.id];
        }
        if (this.siteExcludedReferrers[this.site.id]) {
          params.excludedReferrers = this.siteExcludedReferrers[this.site.id];
        }
        if (this.useCustomCampaignParams) {
          params.customCampaignNameQueryParam = this.customCampaignName;
          params.customCampaignKeywordParam = this.customCampaignKeyword;
        }
        if (this.trackingCodeAbortController) {
          this.trackingCodeAbortController.abort();
          this.trackingCodeAbortController = null;
        }
        this.trackingCodeAbortController = new AbortController();
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            format: "json",
            method: "SitesManager.getJavascriptTag",
            idSite: this.site.id
          },
          params,
          {
            abortController: this.trackingCodeAbortController
          }
        ).then((response) => {
          this.trackingCodeAbortController = null;
          this.$emit("updateTrackingCode", response.value);
        });
      },
      addCustomVar() {
        if (this.canAddMoreCustomVariables) {
          this.customVars.push({ name: "", value: "" });
        }
        this.canAddMoreCustomVariables = !!this.maxCustomVariables && this.maxCustomVariables > this.customVars.length;
      },
      onCustomVarNameKeydown(event, index) {
        setTimeout(() => {
          this.customVars[index].name = event.target.value;
          this.updateTrackingCode();
        });
      },
      onCustomVarValueKeydown(event, index) {
        setTimeout(() => {
          this.customVars[index].value = event.target.value;
          this.updateTrackingCode();
        });
      }
    },
    computed: {
      hasManySiteUrls() {
        const { site } = this;
        return this.siteUrls[site.id] && this.siteUrls[site.id].length > 1;
      },
      currentSiteHost() {
        var _a;
        const siteUrl = (_a = this.siteUrls[this.site.id]) == null ? void 0 : _a[0];
        if (!siteUrl) {
          return "";
        }
        return getHostNameFromUrl(siteUrl);
      },
      currentSiteAlias() {
        var _a;
        const defaultAliasUrl = `x.${this.currentSiteHost}`;
        const alias = (_a = this.siteUrls[this.site.id]) == null ? void 0 : _a[1];
        return alias || defaultAliasUrl;
      },
      currentSiteName() {
        return CoreHome.Matomo.helper.htmlEntities(this.site.name);
      },
      mergeSubdomainsDesc() {
        return CoreHome.translate(
          "CoreAdminHome_JSTracking_MergeSubdomainsDesc",
          `x.${this.currentSiteHost}`,
          `y.${this.currentSiteHost}`
        );
      },
      learnMoreText() {
        const subdomainsLink = CoreHome.externalRawLink("https://developer.matomo.org/guides/tracking-javascript-guide") + "#measuring-domains-andor-sub-domains";
        return CoreHome.translate(
          "General_LearnMore",
          ` (<a href="${subdomainsLink}" rel="noreferrer noopener" target="_blank">`,
          "</a>)"
        );
      },
      jsTrackCampaignParamsInlineHelp() {
        return CoreHome.translate(
          "CoreAdminHome_JSTracking_CustomCampaignQueryParamDesc",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_119"),
          "</a>"
        );
      },
      trackingDocumentationHelp() {
        return CoreHome.translate(
          "CoreAdminHome_JSTrackingDocumentationHelp",
          CoreHome.externalLink("https://developer.matomo.org/guides/tracking-javascript-guide"),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$5 = { class: "trackingCodeAdvancedOptions" };
  const _hoisted_2$5 = { class: "advance-option" };
  const _hoisted_3$5 = { id: "javascript-advanced-options" };
  const _hoisted_4$5 = ["innerHTML"];
  const _hoisted_5$3 = { id: "optional-js-tracking-options" };
  const _hoisted_6$3 = {
    id: "jsTrackAllSubdomainsInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_7$3 = ["innerHTML"];
  const _hoisted_8$3 = ["innerHTML"];
  const _hoisted_9$3 = {
    id: "jsTrackGroupByDomainInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_10$2 = {
    id: "jsTrackAllAliasesInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_11$1 = { id: "javascript-tracking-visitor-cv" };
  const _hoisted_12$1 = { class: "row" };
  const _hoisted_13$1 = { class: "col s12 m3" };
  const _hoisted_14$1 = { class: "col s12 m3" };
  const _hoisted_15$1 = { class: "col s12 m6 l3" };
  const _hoisted_16$1 = ["onKeydown"];
  const _hoisted_17$1 = { class: "col s12 m6 l3" };
  const _hoisted_18 = ["onKeydown"];
  const _hoisted_19 = { class: "row" };
  const _hoisted_20 = { class: "col s12" };
  const _hoisted_21 = {
    id: "jsCrossDomain",
    class: "inline-help-node"
  };
  const _hoisted_22 = {
    id: "jsDoNotTrackInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_23 = { key: 0 };
  const _hoisted_24 = ["innerHTML"];
  const _hoisted_25 = { id: "js-campaign-query-param-extra" };
  const _hoisted_26 = { class: "row" };
  const _hoisted_27 = { class: "col s12" };
  const _hoisted_28 = { class: "row" };
  const _hoisted_29 = { class: "col s12" };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createElementVNode("div", _hoisted_2$5, [
        vue.createElementVNode("span", null, [
          !_ctx.showAdvanced ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            href: "javascript:;",
            onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.showAdvanced = true, ["prevent"]))
          }, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_ShowAdvancedOptions")) + " ", 1),
            _cache[15] || (_cache[15] = vue.createElementVNode("span", { class: "icon-chevron-down" }, null, -1))
          ])) : vue.createCommentVNode("", true),
          _ctx.showAdvanced ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 1,
            href: "javascript:;",
            onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.showAdvanced = false, ["prevent"]))
          }, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_HideAdvancedOptions")) + " ", 1),
            _cache[16] || (_cache[16] = vue.createElementVNode("span", { class: "icon-chevron-up" }, null, -1))
          ])) : vue.createCommentVNode("", true)
        ])
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_3$5, [
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.trackingDocumentationHelp)
        }, null, 8, _hoisted_4$5),
        vue.createElementVNode("div", _hoisted_5$3, [
          vue.createElementVNode("div", _hoisted_6$3, [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.mergeSubdomainsDesc)
            }, null, 8, _hoisted_7$3),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.learnMoreText)
            }, null, 8, _hoisted_8$3)
          ]),
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "javascript-tracking-all-subdomains",
            "model-value": _ctx.trackAllSubdomains,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => {
              _ctx.trackAllSubdomains = $event;
              _ctx.updateTrackingCode();
            }),
            disabled: _ctx.isLoading,
            title: `${_ctx.translate(
              "CoreAdminHome_JSTracking_MergeSubdomains"
            )} ${_ctx.currentSiteName}`,
            "inline-help": "#jsTrackAllSubdomainsInlineHelp"
          }, null, 8, ["model-value", "disabled", "title"])
        ]),
        vue.createElementVNode("div", _hoisted_9$3, vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_GroupPageTitlesByDomainDesc1", _ctx.currentSiteHost)), 1),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-group-by-domain",
          "model-value": _ctx.groupByDomain,
          "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
            _ctx.groupByDomain = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_GroupPageTitlesByDomain"),
          "inline-help": "#jsTrackGroupByDomainInlineHelp"
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.createElementVNode("div", _hoisted_10$2, vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_MergeAliasesDesc", _ctx.currentSiteAlias)), 1),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-all-aliases",
          "model-value": _ctx.trackAllAliases,
          "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
            _ctx.trackAllAliases = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: `${_ctx.translate("CoreAdminHome_JSTracking_MergeAliases")} ${_ctx.currentSiteName}`,
          "inline-help": "#jsTrackAllAliasesInlineHelp"
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-noscript",
          "model-value": _ctx.trackNoScript,
          "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => {
            _ctx.trackNoScript = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_TrackNoScript")
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.withDirectives(vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-visitor-cv-check",
          "model-value": _ctx.trackCustomVars,
          "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => {
            _ctx.trackCustomVars = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_VisitorCustomVars"),
          "inline-help": _ctx.translate("CoreAdminHome_JSTracking_VisitorCustomVarsDesc")
        }, null, 8, ["model-value", "disabled", "title", "inline-help"]), [
          [vue.vShow, _ctx.maxCustomVariables > 0]
        ]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_11$1, [
          vue.createElementVNode("div", _hoisted_12$1, [
            vue.createElementVNode("div", _hoisted_13$1, vue.toDisplayString(_ctx.translate("General_Name")), 1),
            vue.createElementVNode("div", _hoisted_14$1, vue.toDisplayString(_ctx.translate("General_Value")), 1)
          ]),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.customVars, (customVar, index) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              class: "row",
              key: index
            }, [
              vue.createElementVNode("div", _hoisted_15$1, [
                vue.createElementVNode("input", {
                  type: "text",
                  class: "custom-variable-name",
                  onKeydown: ($event) => _ctx.onCustomVarNameKeydown($event, index),
                  placeholder: "e.g. Type"
                }, null, 40, _hoisted_16$1)
              ]),
              vue.createElementVNode("div", _hoisted_17$1, [
                vue.createElementVNode("input", {
                  type: "text",
                  class: "custom-variable-value",
                  onKeydown: ($event) => _ctx.onCustomVarValueKeydown($event, index),
                  placeholder: "e.g. Customer"
                }, null, 40, _hoisted_18)
              ])
            ]);
          }), 128)),
          vue.withDirectives(vue.createElementVNode("div", _hoisted_19, [
            vue.createElementVNode("div", _hoisted_20, [
              vue.createElementVNode("a", {
                href: "javascript:;",
                onClick: _cache[7] || (_cache[7] = ($event) => _ctx.addCustomVar()),
                class: "add-custom-variable"
              }, [
                _cache[17] || (_cache[17] = vue.createElementVNode("span", { class: "icon-add" }, null, -1)),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Add")), 1)
              ])
            ])
          ], 512), [
            [vue.vShow, _ctx.canAddMoreCustomVariables]
          ])
        ], 512), [
          [vue.vShow, _ctx.trackCustomVars]
        ]),
        vue.createElementVNode("div", _hoisted_21, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_CrossDomain")) + " ", 1),
          _cache[18] || (_cache[18] = vue.createElementVNode("br", null, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_CrossDomain_NeedsMultipleDomains")), 1)
        ]),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-cross-domain",
          "model-value": _ctx.crossDomain,
          "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => {
            _ctx.crossDomain = $event;
            _ctx.updateTrackingCode();
            _ctx.onCrossDomainToggle();
          }),
          disabled: _ctx.isLoading || !_ctx.hasManySiteUrls,
          title: _ctx.translate("CoreAdminHome_JSTracking_EnableCrossDomainLinking"),
          "inline-help": "#jsCrossDomain"
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.createElementVNode("div", _hoisted_22, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_EnableDoNotTrackDesc")) + " ", 1),
          _ctx.serverSideDoNotTrackEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_23, [
            _cache[19] || (_cache[19] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_EnableDoNotTrack_AlreadyEnabled")), 1)
          ])) : vue.createCommentVNode("", true)
        ]),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-do-not-track",
          "model-value": _ctx.doNotTrack,
          "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => {
            _ctx.doNotTrack = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_EnableDoNotTrack"),
          "inline-help": "#jsDoNotTrackInlineHelp"
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "javascript-tracking-disable-cookies",
          "model-value": _ctx.disableCookies,
          "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => {
            _ctx.disableCookies = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_DisableCookies"),
          "inline-help": _ctx.translate("CoreAdminHome_JSTracking_DisableCookiesDesc")
        }, null, 8, ["model-value", "disabled", "title", "inline-help"]),
        vue.createElementVNode("div", {
          id: "jsTrackCampaignParamsInlineHelp",
          class: "inline-help-node",
          innerHTML: _ctx.$sanitize(_ctx.jsTrackCampaignParamsInlineHelp)
        }, null, 8, _hoisted_24),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "custom-campaign-query-params-check",
          "model-value": _ctx.useCustomCampaignParams,
          "onUpdate:modelValue": _cache[11] || (_cache[11] = ($event) => {
            _ctx.useCustomCampaignParams = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_CustomCampaignQueryParam"),
          "inline-help": "#jsTrackCampaignParamsInlineHelp"
        }, null, 8, ["model-value", "disabled", "title"]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_25, [
          vue.createElementVNode("div", _hoisted_26, [
            vue.createElementVNode("div", _hoisted_27, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "custom-campaign-name-query-param",
                "model-value": _ctx.customCampaignName,
                "onUpdate:modelValue": _cache[12] || (_cache[12] = ($event) => {
                  _ctx.customCampaignName = $event;
                  _ctx.updateTrackingCode();
                }),
                disabled: _ctx.isLoading,
                title: _ctx.translate("CoreAdminHome_JSTracking_CampaignNameParam")
              }, null, 8, ["model-value", "disabled", "title"])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_28, [
            vue.createElementVNode("div", _hoisted_29, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "custom-campaign-keyword-query-param",
                "model-value": _ctx.customCampaignKeyword,
                "onUpdate:modelValue": _cache[13] || (_cache[13] = ($event) => {
                  _ctx.customCampaignKeyword = $event;
                  _ctx.updateTrackingCode();
                }),
                disabled: _ctx.isLoading,
                title: _ctx.translate("CoreAdminHome_JSTracking_CampaignKwdParam")
              }, null, 8, ["model-value", "disabled", "title"])
            ])
          ])
        ], 512), [
          [vue.vShow, _ctx.useCustomCampaignParams]
        ]),
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "require-consent-for-campaign-tracking",
          "model-value": _ctx.disableCampaignParameters,
          "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => {
            _ctx.disableCampaignParameters = $event;
            _ctx.updateTrackingCode();
          }),
          disabled: _ctx.isLoading,
          title: _ctx.translate("CoreAdminHome_JSTracking_DisableCampaignParameters"),
          "inline-help": _ctx.translate("CoreAdminHome_JSTracking_DisableCampaignParametersDesc")
        }, null, 8, ["model-value", "disabled", "title", "inline-help"])
      ], 512), [
        [vue.vShow, _ctx.showAdvanced]
      ])
    ]);
  }
  const JsTrackingCodeAdvancedOptions = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    props: {
      defaultSite: {
        type: Object,
        required: true
      },
      maxCustomVariables: Number,
      serverSideDoNotTrackEnabled: Boolean
    },
    data() {
      return {
        site: this.defaultSite,
        trackingCode: "",
        isHighlighting: false,
        consentManagerName: "",
        consentManagerUrl: "",
        consentManagerIsConnected: false
      };
    },
    components: {
      JsTrackingCodeAdvancedOptions,
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    created() {
      if (this.site && this.site.id) {
        this.onSiteChanged(this.site);
      }
    },
    watch: {
      site(newValue) {
        this.onSiteChanged(newValue);
      }
    },
    methods: {
      updateTrackingCode(code) {
        this.trackingCode = code;
        const jsCodeTextarea = $(this.$refs.trackingCode);
        if (jsCodeTextarea && !this.isHighlighting) {
          this.isHighlighting = true;
          jsCodeTextarea.effect("highlight", {
            complete: () => {
              this.isHighlighting = false;
            }
          }, 1500);
        }
      },
      onSiteChanged(newValue) {
        const idSite = newValue.id;
        CoreHome.AjaxHelper.fetch(
          {
            module: "API",
            format: "json",
            method: "SitesManager.detectConsentManager",
            idSite,
            filter_limit: "-1"
          }
        ).then((response) => {
          if (Object.prototype.hasOwnProperty.call(response, "name")) {
            this.consentManagerName = response.name;
          }
          if (Object.prototype.hasOwnProperty.call(response, "url")) {
            this.consentManagerUrl = response.url;
          }
          this.consentManagerIsConnected = response.isConnected;
        });
      },
      sendEmail() {
        let subjectLine = CoreHome.translate("SitesManager_EmailInstructionsSubject");
        subjectLine = encodeURIComponent(subjectLine);
        let { trackingCode } = this;
        trackingCode = trackingCode.replace(/<[^>]+>/g, "");
        let bodyText = `${CoreHome.translate("SitesManager_JsTrackingTagHelp")}. ${CoreHome.translate(
          "CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail",
          "'head"
        )}
${trackingCode}`;
        if (this.consentManagerName !== "" && this.consentManagerUrl !== "") {
          bodyText += CoreHome.translate(
            "CoreAdminHome_JSTracking_ConsentManagerDetected",
            this.consentManagerName,
            this.consentManagerUrl
          );
          if (this.consentManagerIsConnected) {
            bodyText += `
${CoreHome.translate("CoreAdminHome_JSTracking_ConsentManagerConnected", this.consentManagerName)}`;
          }
        }
        bodyText = encodeURIComponent(bodyText);
        const linkText = `mailto:?subject=${subjectLine}&body=${bodyText}`;
        window.location.href = linkText;
      }
    },
    computed: {
      jsTrackingIntro3a() {
        return CoreHome.translate(
          "CoreAdminHome_JSTrackingIntro3a",
          CoreHome.externalLink("https://matomo.org/integrate/"),
          "</a>"
        );
      },
      jsTrackingIntro3b() {
        return CoreHome.translate("CoreAdminHome_JSTrackingIntro3b");
      },
      jsTrackingIntro4a() {
        return CoreHome.translate(
          "CoreAdminHome_JSTrackingIntro4",
          '<a href="#image-tracking-link">',
          "</a>"
        );
      },
      jsTrackingIntro5() {
        return CoreHome.translate(
          "CoreAdminHome_JSTrackingIntro5",
          CoreHome.externalLink("https://developer.matomo.org/guides/tracking-javascript-guide"),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$4 = { id: "js-code-options" };
  const _hoisted_2$4 = ["innerHTML"];
  const _hoisted_3$4 = ["innerHTML"];
  const _hoisted_4$4 = ["innerHTML"];
  const _hoisted_5$2 = ["innerHTML"];
  const _hoisted_6$2 = ["href"];
  const _hoisted_7$2 = ["href"];
  const _hoisted_8$2 = ["href"];
  const _hoisted_9$2 = ["href"];
  const _hoisted_10$1 = ["href"];
  const _hoisted_11 = ["href"];
  const _hoisted_12 = ["href"];
  const _hoisted_13 = { id: "javascript-output-section" };
  const _hoisted_14 = { class: "valign-wrapper trackingHelpHeader matchWidth" };
  const _hoisted_15 = { id: "javascript-email-button" };
  const _hoisted_16 = { id: "javascript-text" };
  const _hoisted_17 = ["textContent"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_JsTrackingCodeAdvancedOptions = vue.resolveComponent("JsTrackingCodeAdvancedOptions");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      anchor: "javaScriptTracking",
      "content-title": _ctx.translate("CoreAdminHome_JavaScriptTracking")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", _hoisted_1$4, [
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTrackingIntro1")) + " ", 1),
            _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
            _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTrackingIntro2")) + " ", 1),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro3a)
            }, null, 8, _hoisted_2$4),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(" " + _ctx.jsTrackingIntro3b)
            }, null, 8, _hoisted_3$4),
            _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
            _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro4a)
            }, null, 8, _hoisted_4$4),
            _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1)),
            _cache[7] || (_cache[7] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.jsTrackingIntro5)
            }, null, 8, _hoisted_5$2),
            _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
            _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_InstallationGuides")) + " : ", 1),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-wordpress/"),
              target: "_blank",
              rel: "noopener"
            }, "WordPress", 8, _hoisted_6$2),
            _cache[10] || (_cache[10] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-integrate-matomo-with-squarespace-website/"),
              target: "_blank",
              rel: "noopener"
            }, "Squarespace", 8, _hoisted_7$2),
            _cache[11] || (_cache[11] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-wix/"),
              target: "_blank",
              rel: "noopener"
            }, "Wix", 8, _hoisted_8$2),
            _cache[12] || (_cache[12] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/how-to-install/faq_19424/"),
              target: "_blank",
              rel: "noopener"
            }, "SharePoint", 8, _hoisted_9$2),
            _cache[13] || (_cache[13] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-joomla/"),
              target: "_blank",
              rel: "noopener"
            }, "Joomla", 8, _hoisted_10$1),
            _cache[14] || (_cache[14] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-shopify-store/"),
              target: "_blank",
              rel: "noopener"
            }, "Shopify", 8, _hoisted_11),
            _cache[15] || (_cache[15] = vue.createTextVNode(" | ", -1)),
            vue.createElementVNode("a", {
              href: _ctx.externalRawLink("https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager/"),
              target: "_blank",
              rel: "noopener"
            }, "Google Tag Manager", 8, _hoisted_12)
          ]),
          vue.createVNode(_component_Field, {
            uicontrol: "site",
            name: "js-tracker-website",
            class: "jsTrackingCodeWebsite",
            modelValue: _ctx.site,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.site = $event),
            ref: "site",
            introduction: _ctx.translate("General_Website")
          }, null, 8, ["modelValue", "introduction"]),
          vue.createElementVNode("div", _hoisted_13, [
            vue.createElementVNode("div", _hoisted_14, [
              vue.createElementVNode("div", null, [
                vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("General_JsTrackingTag")), 1),
                vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead", "</head>")), 1)
              ]),
              vue.createElementVNode("div", _hoisted_15, [
                vue.createElementVNode("button", {
                  class: "btn",
                  id: "emailJsBtn",
                  onClick: _cache[1] || (_cache[1] = ($event) => _ctx.sendEmail())
                }, vue.toDisplayString(_ctx.translate("SitesManager_EmailInstructionsButton")), 1)
              ])
            ]),
            vue.createElementVNode("div", _hoisted_16, [
              vue.createElementVNode("div", null, [
                vue.withDirectives(vue.createElementVNode("pre", {
                  class: "codeblock",
                  textContent: vue.toDisplayString(_ctx.trackingCode),
                  ref: "trackingCode"
                }, null, 8, _hoisted_17), [
                  [_directive_copy_to_clipboard, {}]
                ])
              ])
            ])
          ])
        ]),
        vue.createVNode(_component_JsTrackingCodeAdvancedOptions, {
          site: _ctx.site,
          "max-custom-variables": _ctx.maxCustomVariables,
          "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
          onUpdateTrackingCode: _ctx.updateTrackingCode
        }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const JsTrackingCodeGenerator = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      defaultSite: {
        type: Object,
        required: true
      },
      maxCustomVariables: Number,
      serverSideDoNotTrackEnabled: Boolean,
      jsTag: String,
      isJsTrackerInstallCheckAvailable: Boolean
    },
    components: {
      JsTrackingCodeAdvancedOptions
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        site: this.defaultSite,
        trackingCode: "",
        isHighlighting: false
      };
    },
    created() {
      if (this.jsTag) {
        this.trackingCode = this.jsTag;
      }
    },
    methods: {
      updateTrackingCode(code) {
        this.trackingCode = code;
        const jsCodeTextarea = $(this.$refs.trackingCode);
        if (jsCodeTextarea && !this.isHighlighting) {
          this.isHighlighting = true;
          jsCodeTextarea.effect("highlight", {
            complete: () => {
              this.isHighlighting = false;
            }
          }, 1500);
        }
      }
    },
    computed: {
      getCopyCodeStep() {
        return CoreHome.translate("CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead", "</head>");
      },
      testComponent() {
        if (this.isJsTrackerInstallCheckAvailable) {
          return CoreHome.useExternalPluginComponent("JsTrackerInstallCheck", "JsTrackerInstallCheck");
        }
        return "";
      }
    }
  });
  const _hoisted_1$3 = { class: "list-style-decimal" };
  const _hoisted_2$3 = { id: "javascript-text" };
  const _hoisted_3$3 = ["textContent"];
  const _hoisted_4$3 = { key: 0 };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_JsTrackingCodeAdvancedOptions = vue.resolveComponent("JsTrackingCodeAdvancedOptions");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createElementBlock("ol", _hoisted_1$3, [
      vue.createElementVNode("li", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_JsTrackingCodeAdvancedOptionsStep")) + " ", 1),
        vue.createVNode(_component_JsTrackingCodeAdvancedOptions, {
          site: _ctx.site,
          "max-custom-variables": _ctx.maxCustomVariables,
          "server-side-do-not-track-enabled": _ctx.serverSideDoNotTrackEnabled,
          onUpdateTrackingCode: _ctx.updateTrackingCode
        }, null, 8, ["site", "max-custom-variables", "server-side-do-not-track-enabled", "onUpdateTrackingCode"])
      ]),
      vue.createElementVNode("li", null, [
        vue.createElementVNode("span", null, vue.toDisplayString(_ctx.getCopyCodeStep), 1),
        vue.createElementVNode("div", _hoisted_2$3, [
          vue.createElementVNode("div", null, [
            vue.withDirectives(vue.createElementVNode("pre", {
              class: "codeblock",
              textContent: vue.toDisplayString(_ctx.trackingCode),
              ref: "trackingCode"
            }, null, 8, _hoisted_3$3), [
              [_directive_copy_to_clipboard, {}]
            ])
          ])
        ])
      ]),
      _ctx.isJsTrackerInstallCheckAvailable ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_4$3, [
        (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.testComponent), { site: _ctx.site }, null, 8, ["site"]))
      ])) : vue.createCommentVNode("", true)
    ]);
  }
  const JsTrackingCodeGeneratorSitesWithoutData = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  let currencySymbols = null;
  const { $: $$1 } = window;
  const piwikHost = window.location.host;
  const piwikPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf("/"));
  const _sfc_main$2 = vue.defineComponent({
    props: {
      defaultSite: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        isLoading: false,
        site: this.defaultSite,
        pageName: "",
        trackGoal: false,
        trackIdGoal: null,
        revenue: "",
        trackingCode: "",
        sites: {},
        goals: {},
        trackingCodeAbortController: null,
        isHighlighting: false
      };
    },
    created() {
      this.updateTrackingCode = CoreHome.debounce(this.updateTrackingCode);
      if (this.site && this.site.id) {
        this.onSiteChanged(this.site);
      }
    },
    watch: {
      site(newValue) {
        this.onSiteChanged(newValue);
      }
    },
    methods: {
      onSiteChanged(newValue) {
        this.trackIdGoal = null;
        let currencyPromise;
        if (currencySymbols) {
          currencyPromise = Promise.resolve(currencySymbols);
        } else {
          this.isLoading = true;
          currencyPromise = CoreHome.AjaxHelper.fetch({
            method: "SitesManager.getCurrencySymbols",
            filter_limit: "-1"
          });
        }
        let sitePromise;
        if (this.sites[newValue.id]) {
          sitePromise = Promise.resolve(this.sites[newValue.id]);
        } else {
          this.isLoading = true;
          sitePromise = CoreHome.AjaxHelper.fetch({
            module: "API",
            method: "SitesManager.getSiteFromId",
            idSite: newValue.id
          });
        }
        let goalPromise;
        if (this.goals[newValue.id]) {
          goalPromise = Promise.resolve(this.goals[newValue.id]);
        } else {
          this.isLoading = true;
          goalPromise = CoreHome.AjaxHelper.fetch({
            module: "API",
            method: "Goals.getGoals",
            filter_limit: "-1",
            idSite: newValue.id
          });
        }
        return Promise.all([
          currencyPromise,
          sitePromise,
          goalPromise
        ]).then(([currencyResponse, site, goalsResponse]) => {
          this.isLoading = false;
          currencySymbols = currencyResponse;
          this.sites[newValue.id] = site;
          this.goals[newValue.id] = goalsResponse;
          this.updateTrackingCode();
        });
      },
      updateTrackingCode() {
        const postParams = {
          piwikUrl: `${piwikHost}${piwikPath}`,
          actionName: this.pageName,
          forceMatomoEndpoint: 1
        };
        if (this.trackGoal && this.trackIdGoal) {
          postParams.idGoal = this.trackIdGoal;
          postParams.revenue = this.revenue;
        }
        if (this.trackingCodeAbortController) {
          this.trackingCodeAbortController.abort();
          this.trackingCodeAbortController = null;
        }
        this.trackingCodeAbortController = new AbortController();
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            format: "json",
            method: "SitesManager.getImageTrackingCode",
            idSite: this.site.id
          },
          postParams,
          { abortController: this.trackingCodeAbortController }
        ).then((response) => {
          this.trackingCodeAbortController = null;
          this.trackingCode = response.value;
          const imageCodeTextarea = $$1(this.$refs.trackingCode);
          if (imageCodeTextarea && !this.isHighlighting) {
            this.isHighlighting = true;
            imageCodeTextarea.effect("highlight", {
              complete: () => {
                this.isHighlighting = false;
              }
            }, 1500);
          }
        });
      }
    },
    computed: {
      currentSiteCurrency() {
        if (!currencySymbols) {
          return "";
        }
        return currencySymbols[(this.sites[this.site.id].currency || "").toUpperCase()];
      },
      siteGoals() {
        const goalsResponse = this.goals[this.site.id];
        return [
          { key: "", value: CoreHome.translate("UserCountryMap_None") }
        ].concat(
          Object.values(goalsResponse || []).map((g) => ({ key: `${g.idgoal}`, value: g.name }))
        );
      },
      imageTrackingIntro() {
        const first = CoreHome.translate("CoreAdminHome_ImageTrackingIntro1");
        const second = CoreHome.translate(
          "CoreAdminHome_ImageTrackingIntro2",
          "<code>&lt;noscript&gt;&lt;/noscript&gt;</code>"
        );
        return `${first} ${second}`;
      },
      imageTrackingIntro3() {
        const link = CoreHome.externalRawLink("https://matomo.org/docs/tracking-api/reference/");
        return CoreHome.translate(
          "CoreAdminHome_ImageTrackingIntro3",
          `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$2 = { id: "image-tracking-code-options" };
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = ["innerHTML"];
  const _hoisted_4$2 = { id: "image-tracking-goal-sub" };
  const _hoisted_5$1 = { class: "row" };
  const _hoisted_6$1 = { class: "col s12 m6" };
  const _hoisted_7$1 = { class: "col s12 m6" };
  const _hoisted_8$1 = { id: "image-link-output-section" };
  const _hoisted_9$1 = { id: "image-tracking-text" };
  const _hoisted_10 = ["textContent"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("CoreAdminHome_ImageTracking"),
      anchor: "imageTracking"
    }, {
      default: vue.withCtx(() => [
        _cache[5] || (_cache[5] = vue.createElementVNode("a", { name: "image-tracking-link" }, null, -1)),
        vue.createElementVNode("div", _hoisted_1$2, [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro)
          }, null, 8, _hoisted_2$2),
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.imageTrackingIntro3)
          }, null, 8, _hoisted_3$2),
          vue.createVNode(_component_Field, {
            uicontrol: "site",
            name: "image-tracker-website",
            modelValue: _ctx.site,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.site = $event),
            introduction: _ctx.translate("General_Website")
          }, null, 8, ["modelValue", "introduction"]),
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "image-tracker-action-name",
            "model-value": _ctx.pageName,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => {
              _ctx.pageName = $event;
              _ctx.updateTrackingCode();
            }),
            disabled: _ctx.isLoading,
            introduction: _ctx.translate("General_Options"),
            title: _ctx.translate("Actions_ColumnPageName")
          }, null, 8, ["model-value", "disabled", "introduction", "title"]),
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "image-tracking-goal-check",
            "model-value": _ctx.trackGoal,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => {
              _ctx.trackGoal = $event;
              _ctx.updateTrackingCode();
            }),
            disabled: _ctx.isLoading,
            title: _ctx.translate("CoreAdminHome_TrackAGoal")
          }, null, 8, ["model-value", "disabled", "title"]),
          vue.withDirectives(vue.createElementVNode("div", _hoisted_4$2, [
            vue.createElementVNode("div", _hoisted_5$1, [
              vue.createElementVNode("div", _hoisted_6$1, [
                vue.createVNode(_component_Field, {
                  uicontrol: "select",
                  name: "image-tracker-goal",
                  options: _ctx.siteGoals,
                  disabled: _ctx.isLoading,
                  "model-value": _ctx.trackIdGoal,
                  "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
                    _ctx.trackIdGoal = $event;
                    _ctx.updateTrackingCode();
                  })
                }, null, 8, ["options", "disabled", "model-value"])
              ]),
              vue.createElementVNode("div", _hoisted_7$1, [
                vue.createVNode(_component_Field, {
                  uicontrol: "text",
                  name: "image-revenue",
                  "model-value": _ctx.revenue,
                  "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
                    _ctx.revenue = $event;
                    _ctx.updateTrackingCode();
                  }),
                  disabled: _ctx.isLoading,
                  "full-width": true,
                  title: `${_ctx.translate("CoreAdminHome_WithOptionalRevenue")} ${_ctx.currentSiteCurrency}`
                }, null, 8, ["model-value", "disabled", "title"])
              ])
            ])
          ], 512), [
            [vue.vShow, _ctx.trackGoal]
          ]),
          vue.createElementVNode("div", _hoisted_8$1, [
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("CoreAdminHome_ImageTrackingLink")), 1),
            vue.createElementVNode("div", _hoisted_9$1, [
              vue.createElementVNode("div", null, [
                vue.withDirectives(vue.createElementVNode("pre", {
                  textContent: vue.toDisplayString(_ctx.trackingCode),
                  ref: "trackingCode"
                }, null, 8, _hoisted_10), [
                  [_directive_copy_to_clipboard, {}]
                ])
              ])
            ])
          ])
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ImageTrackingCodeGenerator = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      failure: {
        type: Object,
        required: true
      }
    },
    emits: ["delete"],
    data() {
      return {
        showFullRequestUrl: false
      };
    },
    computed: {
      limtedRequestUrl() {
        return this.failure.request_url.substring(0, 100);
      }
    },
    methods: {
      deleteFailure(idSite, idFailure) {
        this.$emit("delete", { idSite, idFailure });
      }
    }
  });
  const _hoisted_1$1 = ["href"];
  const _hoisted_2$1 = { class: "datetime" };
  const _hoisted_3$1 = ["title"];
  const _hoisted_4$1 = ["title"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("td", null, vue.toDisplayString(_ctx.failure.site_name) + " (" + vue.toDisplayString(_ctx.translate("General_Id")) + " " + vue.toDisplayString(_ctx.failure.idsite) + ")", 1),
      vue.createElementVNode("td", null, vue.toDisplayString(_ctx.failure.problem), 1),
      vue.createElementVNode("td", null, [
        vue.createTextVNode(vue.toDisplayString(_ctx.failure.solution) + " ", 1),
        vue.withDirectives(vue.createElementVNode("a", {
          rel: "noopener noreferrer",
          href: _ctx.failure.solution_url
        }, vue.toDisplayString(_ctx.translate("CoreAdminHome_LearnMore")), 9, _hoisted_1$1), [
          [vue.vShow, _ctx.failure.solution_url]
        ])
      ]),
      vue.createElementVNode("td", _hoisted_2$1, vue.toDisplayString(_ctx.failure.pretty_date_first_occurred), 1),
      vue.createElementVNode("td", null, vue.toDisplayString(_ctx.failure.url), 1),
      vue.createElementVNode("td", null, [
        vue.withDirectives(vue.createElementVNode("span", {
          onClick: _cache[0] || (_cache[0] = ($event) => _ctx.showFullRequestUrl = true),
          title: _ctx.translate("CoreHome_ClickToSeeFullInformation")
        }, vue.toDisplayString(_ctx.limtedRequestUrl) + "...", 9, _hoisted_3$1), [
          [vue.vShow, !_ctx.showFullRequestUrl]
        ]),
        vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.failure.request_url), 513), [
          [vue.vShow, _ctx.failure.showFullRequestUrl]
        ])
      ]),
      vue.createElementVNode("td", null, [
        vue.createElementVNode("span", {
          class: "table-action icon-delete",
          onClick: _cache[1] || (_cache[1] = ($event) => _ctx.deleteFailure(_ctx.failure.idsite, _ctx.failure.idfailure)),
          title: _ctx.translate("General_Delete")
        }, null, 8, _hoisted_4$1)
      ])
    ], 64);
  }
  const FailureRow = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock,
      ActivityIndicator: CoreHome.ActivityIndicator,
      FailureRow
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        failures: [],
        sortColumn: "idsite",
        sortReverse: false,
        isLoading: false
      };
    },
    created() {
      this.fetchAll();
    },
    methods: {
      changeSortOrder(columnToSort) {
        if (this.sortColumn === columnToSort) {
          this.sortReverse = !this.sortReverse;
        } else {
          this.sortColumn = columnToSort;
        }
      },
      fetchAll() {
        this.failures = [];
        this.isLoading = true;
        CoreHome.AjaxHelper.fetch({
          method: "CoreAdminHome.getTrackingFailures",
          filter_limit: "-1"
        }).then((failures) => {
          this.failures = failures;
          this.isLoading = false;
        }).finally(() => {
          this.isLoading = false;
        });
      },
      deleteAll() {
        CoreHome.Matomo.helper.modalConfirm(
          "#confirmDeleteAllTrackingFailures",
          {
            yes: () => {
              this.failures = [];
              CoreHome.AjaxHelper.fetch({
                method: "CoreAdminHome.deleteAllTrackingFailures"
              }).then(() => {
                this.fetchAll();
              });
            }
          }
        );
      },
      deleteFailure(idSite, idFailure) {
        CoreHome.Matomo.helper.modalConfirm(
          "#confirmDeleteThisTrackingFailure",
          {
            yes: () => {
              this.failures = [];
              CoreHome.AjaxHelper.fetch({
                method: "CoreAdminHome.deleteTrackingFailure",
                idSite,
                idFailure
              }).then(() => {
                this.fetchAll();
              });
            }
          }
        );
      }
    },
    computed: {
      sortedFailures() {
        const { sortColumn } = this;
        const sorted = [...this.failures];
        if (this.sortReverse) {
          sorted.sort((lhs, rhs) => {
            if (lhs[sortColumn] > rhs[sortColumn]) {
              return -1;
            }
            if (lhs[sortColumn] < rhs[sortColumn]) {
              return 1;
            }
            return 0;
          });
        } else {
          sorted.sort((lhs, rhs) => {
            if (lhs[sortColumn] < rhs[sortColumn]) {
              return -1;
            }
            if (lhs[sortColumn] > rhs[sortColumn]) {
              return 1;
            }
            return 0;
          });
        }
        return sorted;
      }
    }
  });
  const _hoisted_1 = ["value"];
  const _hoisted_2 = { class: "action" };
  const _hoisted_3 = { colspan: "7" };
  const _hoisted_4 = {
    class: "ui-confirm",
    id: "confirmDeleteAllTrackingFailures"
  };
  const _hoisted_5 = ["value"];
  const _hoisted_6 = ["value"];
  const _hoisted_7 = {
    class: "ui-confirm",
    id: "confirmDeleteThisTrackingFailure"
  };
  const _hoisted_8 = ["value"];
  const _hoisted_9 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_FailureRow = vue.resolveComponent("FailureRow");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      class: "matomoTrackingFailures",
      "content-title": _ctx.translate("CoreAdminHome_TrackingFailures")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_TrackingFailuresIntroduction", "2")) + " ", 1),
          _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
          _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1)),
          vue.withDirectives(vue.createElementVNode("input", {
            class: "btn deleteAllFailures",
            type: "button",
            onClick: _cache[0] || (_cache[0] = ($event) => _ctx.deleteAll()),
            value: _ctx.translate("CoreAdminHome_DeleteAllFailures")
          }, null, 8, _hoisted_1), [
            [vue.vShow, !_ctx.isLoading && _ctx.failures.length > 0]
          ])
        ]),
        vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoading }, null, 8, ["loading"]),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
          vue.createElementVNode("thead", null, [
            vue.createElementVNode("tr", null, [
              vue.createElementVNode("th", {
                onClick: _cache[1] || (_cache[1] = ($event) => _ctx.changeSortOrder("idsite"))
              }, vue.toDisplayString(_ctx.translate("General_Measurable")), 1),
              vue.createElementVNode("th", {
                onClick: _cache[2] || (_cache[2] = ($event) => _ctx.changeSortOrder("problem"))
              }, vue.toDisplayString(_ctx.translate("CoreAdminHome_Problem")), 1),
              vue.createElementVNode("th", {
                onClick: _cache[3] || (_cache[3] = ($event) => _ctx.changeSortOrder("solution"))
              }, vue.toDisplayString(_ctx.translate("CoreAdminHome_Solution")), 1),
              vue.createElementVNode("th", {
                onClick: _cache[4] || (_cache[4] = ($event) => _ctx.changeSortOrder("date_first_occurred"))
              }, vue.toDisplayString(_ctx.translate("General_Date")), 1),
              vue.createElementVNode("th", {
                onClick: _cache[5] || (_cache[5] = ($event) => _ctx.changeSortOrder("url"))
              }, vue.toDisplayString(_ctx.translate("Actions_ColumnPageURL")), 1),
              vue.createElementVNode("th", {
                onClick: _cache[6] || (_cache[6] = ($event) => _ctx.changeSortOrder("request_url"))
              }, vue.toDisplayString(_ctx.translate("CoreAdminHome_TrackingURL")), 1),
              vue.createElementVNode("th", _hoisted_2, vue.toDisplayString(_ctx.translate("General_Action")), 1)
            ])
          ]),
          vue.createElementVNode("tbody", null, [
            vue.createElementVNode("tr", null, [
              vue.withDirectives(vue.createElementVNode("td", _hoisted_3, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("CoreAdminHome_NoKnownFailures")) + " ", 1),
                _cache[10] || (_cache[10] = vue.createElementVNode("span", { class: "icon-ok" }, null, -1))
              ], 512), [
                [vue.vShow, !_ctx.isLoading && _ctx.failures.length === 0]
              ])
            ]),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.sortedFailures, (failure, index) => {
              return vue.openBlock(), vue.createElementBlock("tr", { key: index }, [
                vue.createVNode(_component_FailureRow, {
                  failure,
                  onDelete: _cache[7] || (_cache[7] = ($event) => _ctx.deleteFailure($event.idSite, $event.idFailure))
                }, null, 8, ["failure"])
              ]);
            }), 128))
          ])
        ])), [
          [_directive_content_table]
        ]),
        vue.createElementVNode("div", _hoisted_4, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("CoreAdminHome_ConfirmDeleteAllTrackingFailures")), 1),
          vue.createElementVNode("input", {
            type: "button",
            role: "yes",
            value: _ctx.translate("General_Yes")
          }, null, 8, _hoisted_5),
          vue.createElementVNode("input", {
            type: "button",
            role: "no",
            value: _ctx.translate("General_No")
          }, null, 8, _hoisted_6)
        ]),
        vue.createElementVNode("div", _hoisted_7, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("CoreAdminHome_ConfirmDeleteThisTrackingFailure")), 1),
          vue.createElementVNode("input", {
            type: "button",
            role: "yes",
            value: _ctx.translate("General_Yes")
          }, null, 8, _hoisted_8),
          vue.createElementVNode("input", {
            type: "button",
            role: "no",
            value: _ctx.translate("General_No")
          }, null, 8, _hoisted_9)
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const TrackingFailures = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.ArchivingSettings = ArchivingSettings;
  exports2.BrandingSettings = BrandingSettings;
  exports2.ImageTrackingCodeGenerator = ImageTrackingCodeGenerator;
  exports2.JsTrackingCodeGenerator = JsTrackingCodeGenerator;
  exports2.JsTrackingCodeGeneratorSitesWithoutData = JsTrackingCodeGeneratorSitesWithoutData;
  exports2.SmtpSettings = SmtpSettings;
  exports2.TrackingFailures = TrackingFailures;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
