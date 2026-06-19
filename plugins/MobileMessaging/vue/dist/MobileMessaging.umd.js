(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.MobileMessaging = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
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
      modelValue: Array,
      phoneNumbers: {
        type: [Array, Object],
        required: true
      },
      withIntroduction: Boolean
    },
    emits: ["update:modelValue"],
    components: {
      Field: CorePluginsAdmin.Field
    },
    methods: {
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), params))}`;
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
  const _hoisted_1$5 = { class: "mobile" };
  const _hoisted_2$3 = {
    id: "mobilePhoneNumbersHelp",
    class: "inline-help-node"
  };
  const _hoisted_3$3 = /* @__PURE__ */ vue.createElementVNode("span", {
    class: "icon-info",
    style: { "margin-right": "3.5px" }
  }, null, -1);
  const _hoisted_4$3 = {
    key: 0,
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_5$3 = {
    key: 1,
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_6$3 = ["href"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createVNode(_component_Field, {
        uicontrol: "checkbox",
        "var-type": "array",
        name: "phoneNumbers",
        "model-value": _ctx.modelValue,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event)),
        introduction: _ctx.withIntroduction ? _ctx.translate("ScheduledReports_SendReportTo") : void 0,
        title: _ctx.translate("MobileMessaging_PhoneNumbers"),
        disabled: _ctx.phoneNumbers.length === 0,
        options: _ctx.phoneNumbers
      }, {
        "inline-help": vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_2$3, [
            _hoisted_3$3,
            _ctx.phoneNumbers.length === 0 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$3, vue.toDisplayString(_ctx.translate("MobileMessaging_MobileReport_NoPhoneNumbers")), 1)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$3, vue.toDisplayString(_ctx.translate("MobileMessaging_MobileReport_AdditionalPhoneNumbers")), 1)),
            vue.createElementVNode("a", {
              href: _ctx.linkTo({ module: "MobileMessaging", action: "index", updated: null })
            }, vue.toDisplayString(_ctx.translate("MobileMessaging_MobileReport_MobileMessagingSettingsLink")), 9, _hoisted_6$3)
          ])
        ]),
        _: 1
      }, 8, ["model-value", "introduction", "title", "disabled", "options"])
    ]);
  }
  const SelectPhoneNumbers = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const REPORT_TYPE = "mobile";
  const _sfc_main$5 = vue.defineComponent({
    props: {
      report: {
        type: Object,
        required: true
      },
      phoneNumbers: {
        type: [Array, Object],
        required: true
      }
    },
    components: {
      SelectPhoneNumbers
    },
    emits: ["change"],
    created() {
      const {
        resetReportParametersFunctions,
        updateReportParametersFunctions,
        getReportParametersFunctions
      } = window;
      if (!resetReportParametersFunctions[REPORT_TYPE]) {
        resetReportParametersFunctions[REPORT_TYPE] = (report) => {
          report.phoneNumbers = [];
          report.formatmobile = "sms";
        };
      }
      if (!updateReportParametersFunctions[REPORT_TYPE]) {
        updateReportParametersFunctions[REPORT_TYPE] = (report) => {
          if (!(report == null ? void 0 : report.parameters)) {
            return;
          }
          if (report.parameters && report.parameters.phoneNumbers) {
            report.phoneNumbers = report.parameters.phoneNumbers;
          }
          report.formatmobile = "sms";
        };
      }
      if (!getReportParametersFunctions[REPORT_TYPE]) {
        getReportParametersFunctions[REPORT_TYPE] = (report) => {
          const phoneNumbers = report.phoneNumbers;
          return {
            phoneNumbers: phoneNumbers || [""]
          };
        };
      }
    }
  });
  const _hoisted_1$4 = { key: 0 };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SelectPhoneNumbers = vue.resolveComponent("SelectPhoneNumbers");
    return _ctx.report && _ctx.report.type === "mobile" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
      vue.createVNode(_component_SelectPhoneNumbers, {
        "phone-numbers": _ctx.phoneNumbers,
        "with-introduction": true,
        "model-value": _ctx.report.phoneNumbers,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("change", "phoneNumbers", $event))
      }, null, 8, ["phone-numbers", "model-value"])
    ])) : vue.createCommentVNode("", true);
  }
  const ReportParameters = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const allFieldsByProvider = vue.reactive({});
  const _sfc_main$4 = vue.defineComponent({
    props: {
      provider: {
        type: String,
        required: true
      },
      modelValue: Object
    },
    emits: ["update:modelValue"],
    components: {
      Field: CorePluginsAdmin.Field
    },
    watch: {
      provider() {
        this.$emit("update:modelValue", null);
        this.getCredentialFields();
      }
    },
    created() {
      this.getCredentialFields();
    },
    methods: {
      getCredentialFields() {
        if (allFieldsByProvider[this.provider]) {
          this.$emit(
            "update:modelValue",
            Object.fromEntries(
              allFieldsByProvider[this.provider].map((f) => [f.name, null])
            )
          );
          return;
        }
        CoreHome.AjaxHelper.fetch({
          module: "MobileMessaging",
          action: "getCredentialFields",
          provider: this.provider
        }).then((fields) => {
          this.$emit("update:modelValue", Object.fromEntries(fields.map((f) => [f.name, null])));
          allFieldsByProvider[this.provider] = fields;
        });
      }
    },
    computed: {
      fields() {
        return allFieldsByProvider[this.provider];
      }
    }
  });
  const _hoisted_1$3 = { key: 0 };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return _ctx.fields ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.fields, (field) => {
        var _a;
        return vue.openBlock(), vue.createBlock(_component_Field, {
          key: field.name,
          uicontrol: field.type,
          name: field.name,
          "model-value": (_a = _ctx.modelValue) == null ? void 0 : _a[field.name],
          "onUpdate:modelValue": ($event) => _ctx.$emit("update:modelValue", __spreadProps(__spreadValues({}, _ctx.modelValue), { [field.name]: $event })),
          title: _ctx.translate(field.title)
        }, null, 8, ["uicontrol", "name", "model-value", "onUpdate:modelValue", "title"]);
      }), 128))
    ])) : vue.createCommentVNode("", true);
  }
  const SmsProviderCredentials = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      credentialSupplied: Boolean,
      credentialError: String,
      provider: String,
      creditLeft: [Number, String],
      smsProviderOptions: {
        type: Object,
        required: true
      },
      smsProviders: {
        type: Object,
        required: true
      }
    },
    components: {
      Alert: CoreHome.Alert,
      ActivityIndicator: CoreHome.ActivityIndicator,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      SmsProviderCredentials
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isDeletingAccount: false,
        isUpdatingAccount: false,
        showAccountForm: false,
        credentials: null,
        smsProvider: this.provider
      };
    },
    methods: {
      deleteApiAccount() {
        this.isDeletingAccount = true;
        CoreHome.AjaxHelper.fetch(
          {
            method: "MobileMessaging.deleteSMSAPICredential"
          },
          {
            errorElement: "#ajaxErrorManageSmsProviderSettings"
          }
        ).then(() => {
          CoreHome.Matomo.helper.redirect();
        }).finally(() => {
          this.isDeletingAccount = false;
        });
      },
      showUpdateAccount() {
        this.showAccountForm = true;
      },
      updateAccount() {
        if (this.isUpdateAccountPossible) {
          this.isUpdatingAccount = true;
          CoreHome.AjaxHelper.post(
            {
              method: "MobileMessaging.setSMSAPICredential"
            },
            {
              provider: this.smsProvider,
              credentials: this.credentials
            },
            {
              errorElement: "#ajaxErrorManageSmsProviderSettings"
            }
          ).then(() => {
            CoreHome.Matomo.helper.redirect();
          }).finally(() => {
            this.isUpdatingAccount = false;
          });
        }
      },
      deleteAccount() {
        CoreHome.Matomo.helper.modalConfirm("#confirmDeleteAccount", {
          yes: () => {
            this.isDeletingAccount = true;
            CoreHome.AjaxHelper.fetch(
              {
                method: "MobileMessaging.deleteSMSAPICredential"
              },
              {
                errorElement: "#ajaxErrorManageSmsProviderSettings"
              }
            ).then(() => {
              this.isDeletingAccount = false;
              CoreHome.Matomo.helper.redirect();
            }).finally(() => {
              this.isDeletingAccount = false;
            });
          }
        });
      },
      onUpdateOrDeleteClick(event) {
        const target = event.target;
        if (target.id === "displayAccountForm") {
          this.showUpdateAccount();
        } else if (target.id === "deleteAccount") {
          this.deleteAccount();
        }
      }
    },
    computed: {
      isUpdateAccountPossible() {
        return !!this.smsProvider && this.credentials !== null && Object.values(this.credentials).every((v) => !!v);
      },
      updateOrDeleteAccountText() {
        return CoreHome.translate(
          "MobileMessaging_Settings_UpdateOrDeleteAccount",
          '<a id="displayAccountForm">',
          "</a>",
          '<a id="deleteAccount">',
          "</a>"
        );
      },
      currentProviderDescription() {
        if (!this.smsProvider || !this.smsProviders) {
          return "";
        }
        return this.smsProviders[this.smsProvider];
      }
    }
  });
  const _hoisted_1$2 = /* @__PURE__ */ vue.createElementVNode("div", { id: "ajaxErrorManageSmsProviderSettings" }, null, -1);
  const _hoisted_2$2 = { key: 0 };
  const _hoisted_3$2 = { key: 0 };
  const _hoisted_4$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_5$2 = { key: 1 };
  const _hoisted_6$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7$1 = ["innerHTML"];
  const _hoisted_8$1 = { key: 1 };
  const _hoisted_9$1 = { id: "accountForm" };
  const _hoisted_10$1 = ["innerHTML"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Alert = vue.resolveComponent("Alert");
    const _component_Field = vue.resolveComponent("Field");
    const _component_SmsProviderCredentials = vue.resolveComponent("SmsProviderCredentials");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isDeletingAccount }, null, 8, ["loading"]),
      _hoisted_1$2,
      _ctx.credentialSupplied ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2$2, [
        _ctx.credentialError ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3$2, [
          vue.createVNode(_component_Alert, { severity: "danger" }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_CredentialInvalid", _ctx.provider)), 1),
              _hoisted_4$2,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.credentialError), 1)
            ]),
            _: 1
          })
        ])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$2, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_CredentialProvided", _ctx.provider)) + " " + vue.toDisplayString(_ctx.creditLeft), 1)),
        _hoisted_6$2,
        vue.createElementVNode("span", {
          innerHTML: _ctx.$sanitize(_ctx.updateOrDeleteAccountText),
          onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onUpdateOrDeleteClick($event))
        }, null, 8, _hoisted_7$1)
      ])) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_8$1, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_PleaseSignUp")), 1)),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_9$1, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: "smsProviders",
            modelValue: _ctx.smsProvider,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.smsProvider = $event),
            title: _ctx.translate("MobileMessaging_Settings_SMSProvider"),
            options: _ctx.smsProviderOptions,
            value: _ctx.provider
          }, null, 8, ["modelValue", "title", "options", "value"])
        ]),
        vue.createVNode(_component_SmsProviderCredentials, {
          provider: _ctx.smsProvider,
          modelValue: _ctx.credentials,
          "onUpdate:modelValue": [
            _cache[2] || (_cache[2] = ($event) => _ctx.credentials = $event),
            _cache[3] || (_cache[3] = ($event) => {
              _ctx.credentials = $event;
            })
          ],
          "model-value": _ctx.credentials
        }, null, 8, ["provider", "modelValue", "model-value"]),
        vue.createVNode(_component_SaveButton, {
          id: "apiAccountSubmit",
          disabled: !_ctx.isUpdateAccountPossible,
          saving: _ctx.isUpdatingAccount,
          onConfirm: _cache[4] || (_cache[4] = ($event) => _ctx.updateAccount())
        }, null, 8, ["disabled", "saving"]),
        vue.createElementVNode("div", {
          class: "providerDescription",
          innerHTML: _ctx.$sanitize(_ctx.currentProviderDescription)
        }, null, 8, _hoisted_10$1)
      ])), [
        [vue.vShow, !_ctx.credentialSupplied || _ctx.showAccountForm],
        [_directive_form]
      ])
    ]);
  }
  const ManageSmsProvider = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      delegateManagementOptions: {
        type: Array,
        required: true
      },
      delegatedManagement: [Number, Boolean]
    },
    components: {
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    data() {
      return {
        isLoading: false,
        enabled: this.delegatedManagement ? 1 : 0
      };
    },
    methods: {
      save() {
        this.isLoading = true;
        CoreHome.AjaxHelper.post(
          {
            method: "MobileMessaging.setDelegatedManagement"
          },
          {
            delegatedManagement: this.enabled && this.enabled !== "0" ? "true" : "false"
          }
        ).then(() => {
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            id: "mobileMessagingSettings",
            type: "transient",
            context: "success"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
          CoreHome.Matomo.helper.redirect();
        }).finally(() => {
          this.isLoading = false;
        });
      }
    }
  });
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "radio",
          name: "delegatedManagement",
          title: _ctx.translate("MobileMessaging_Settings_LetUsersManageAPICredential"),
          modelValue: _ctx.enabled,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.enabled = $event),
          "full-width": true,
          options: _ctx.delegateManagementOptions
        }, null, 8, ["title", "modelValue", "options"])
      ]),
      vue.createVNode(_component_SaveButton, {
        onConfirm: _cache[1] || (_cache[1] = ($event) => _ctx.save()),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])
    ]);
  }
  const DelegateMobileMessagingSettings = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      isSuperUser: Boolean,
      defaultCallingCode: String,
      countries: {
        type: Array,
        required: true
      },
      strHelpAddPhone: {
        type: String,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      Alert: CoreHome.Alert,
      ActivityIndicator: CoreHome.ActivityIndicator
    },
    data() {
      return {
        isUpdatingPhoneNumbers: false,
        phoneNumbers: {},
        countryCallingCode: this.defaultCallingCode || "",
        newPhoneNumber: "",
        validationCode: {},
        numberToRemove: ""
      };
    },
    mounted() {
      this.updatePhoneNumbers();
    },
    methods: {
      validateActivationCode(phoneNumber, index) {
        if (!this.validationCode[index]) {
          return;
        }
        const verificationCode = this.validationCode[index];
        this.isUpdatingPhoneNumbers = true;
        this.clearNotifcationsAndErrorsContainer();
        CoreHome.AjaxHelper.post(
          {
            method: "MobileMessaging.validatePhoneNumber"
          },
          {
            phoneNumber,
            verificationCode
          },
          {
            errorElement: "#ajaxErrorManagePhoneNumber"
          }
        ).then((response) => {
          let notificationInstanceId;
          if (!response || !response.value) {
            const message = CoreHome.translate("MobileMessaging_Settings_InvalidActivationCode");
            notificationInstanceId = CoreHome.NotificationsStore.show({
              message,
              placeat: "#notificationManagePhoneNumber",
              context: "error",
              id: "MobileMessaging_ValidatePhoneNumber",
              type: "transient"
            });
          } else {
            const message = CoreHome.translate("MobileMessaging_Settings_PhoneActivated");
            notificationInstanceId = CoreHome.NotificationsStore.show({
              message,
              placeat: "#notificationManagePhoneNumber",
              context: "success",
              id: "MobileMessaging_ValidatePhoneNumber",
              type: "transient"
            });
            this.updatePhoneNumbers();
          }
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).finally(() => {
          this.validationCode[index] = "";
          this.isUpdatingPhoneNumbers = false;
        });
      },
      resendVerificationCode(phoneNumber) {
        this.isUpdatingPhoneNumbers = true;
        this.clearNotifcationsAndErrorsContainer();
        CoreHome.AjaxHelper.post(
          {
            method: "MobileMessaging.resendVerificationCode"
          },
          {
            phoneNumber
          },
          {
            errorElement: "#ajaxErrorManagePhoneNumber"
          }
        ).then(() => {
          const message = CoreHome.translate("MobileMessaging_Settings_NewVerificationCodeSent");
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message,
            placeat: "#notificationManagePhoneNumber",
            context: "success",
            id: "MobileMessaging_ValidatePhoneNumber",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
          this.updatePhoneNumbers();
        }).finally(() => {
          this.isUpdatingPhoneNumbers = false;
        });
      },
      updatePhoneNumbers() {
        this.isUpdatingPhoneNumbers = true;
        CoreHome.AjaxHelper.post(
          {
            method: "MobileMessaging.getPhoneNumbers"
          },
          {}
        ).then((phoneNumbers) => {
          this.phoneNumbers = phoneNumbers;
          this.isUpdatingPhoneNumbers = false;
        });
      },
      removePhoneNumber(phoneNumber) {
        if (!phoneNumber) {
          return;
        }
        this.numberToRemove = phoneNumber;
        this.clearNotifcationsAndErrorsContainer();
        CoreHome.Matomo.helper.modalConfirm(
          "#confirmDeletePhoneNumber",
          {
            yes: () => {
              this.isUpdatingPhoneNumbers = true;
              CoreHome.AjaxHelper.post(
                {
                  method: "MobileMessaging.removePhoneNumber"
                },
                {
                  phoneNumber
                },
                {
                  errorElement: "#ajaxErrorManagePhoneNumber"
                }
              ).then(() => {
                this.updatePhoneNumbers();
              }).finally(() => {
                this.isUpdatingPhoneNumbers = false;
                this.numberToRemove = "";
              });
            }
          }
        );
      },
      addPhoneNumber() {
        const phoneNumber = `+${this.countryCallingCode}${this.newPhoneNumber}`;
        if (this.canAddNumber && phoneNumber.length > 1) {
          this.isUpdatingPhoneNumbers = true;
          this.clearNotifcationsAndErrorsContainer();
          CoreHome.AjaxHelper.post(
            {
              method: "MobileMessaging.addPhoneNumber"
            },
            {
              phoneNumber
            },
            {
              errorElement: "#ajaxErrorManagePhoneNumber"
            }
          ).then(() => {
            this.updatePhoneNumbers();
            this.countryCallingCode = "";
            this.newPhoneNumber = "";
          }).finally(() => {
            this.isUpdatingPhoneNumbers = false;
          });
        }
      },
      clearNotifcationsAndErrorsContainer() {
        this.$refs.errorContainer.innerHTML = "";
        CoreHome.NotificationsStore.remove("MobileMessaging_ValidatePhoneNumber");
      }
    },
    computed: {
      showSuspiciousPhoneNumber() {
        return this.newPhoneNumber.trim().lastIndexOf("0", 0) === 0;
      },
      canAddNumber() {
        return !!this.newPhoneNumber && this.newPhoneNumber !== "";
      },
      removeNumberConfirmation() {
        return CoreHome.translate("MobileMessaging_ConfirmRemovePhoneNumber", this.numberToRemove);
      }
    }
  });
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = { class: "row" };
  const _hoisted_3$1 = { class: "col s12" };
  const _hoisted_4$1 = { class: "form-group row" };
  const _hoisted_5$1 = { class: "col s12 m6" };
  const _hoisted_6$1 = { class: "col s12 m6 form-help" };
  const _hoisted_7 = { class: "form-group row addPhoneNumber" };
  const _hoisted_8 = { class: "col s12 m6" };
  const _hoisted_9 = { class: "countryCode left" };
  const _hoisted_10 = /* @__PURE__ */ vue.createElementVNode("span", { class: "countryCodeSymbol" }, "+", -1);
  const _hoisted_11 = { class: "phoneNumber left" };
  const _hoisted_12 = { class: "addNumber left valign-wrapper" };
  const _hoisted_13 = { class: "col s12 m6 form-help" };
  const _hoisted_14 = {
    id: "ajaxErrorManagePhoneNumber",
    ref: "errorContainer"
  };
  const _hoisted_15 = /* @__PURE__ */ vue.createElementVNode("div", { id: "notificationManagePhoneNumber" }, null, -1);
  const _hoisted_16 = {
    key: 1,
    class: "row"
  };
  const _hoisted_17 = { class: "col s12" };
  const _hoisted_18 = { class: "col s12 m6" };
  const _hoisted_19 = { class: "phoneNumber" };
  const _hoisted_20 = ["onUpdate:modelValue", "placeholder"];
  const _hoisted_21 = {
    key: 0,
    class: "form-help col s12 m6"
  };
  const _hoisted_22 = ["onClick"];
  const _hoisted_23 = {
    class: "ui-confirm",
    id: "confirmDeletePhoneNumber"
  };
  const _hoisted_24 = ["innerHTML"];
  const _hoisted_25 = ["value"];
  const _hoisted_26 = ["value"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_Alert = vue.resolveComponent("Alert");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", null, [
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_PhoneNumbers_Help")), 1),
        _ctx.isSuperUser ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_1$1, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou")), 1)) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_2$1, [
          vue.createElementVNode("h3", _hoisted_3$1, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_PhoneNumbers_Add")), 1)
        ]),
        vue.createElementVNode("div", _hoisted_4$1, [
          vue.createElementVNode("div", _hoisted_5$1, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "select",
                name: "countryCodeSelect",
                title: _ctx.translate("MobileMessaging_Settings_SelectCountry"),
                modelValue: _ctx.countryCallingCode,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.countryCallingCode = $event),
                "full-width": true,
                options: _ctx.countries
              }, null, 8, ["title", "modelValue", "options"])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_6$1, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_PhoneNumbers_CountryCode_Help")), 1)
        ]),
        vue.createElementVNode("div", _hoisted_7, [
          vue.createElementVNode("div", _hoisted_8, [
            vue.createElementVNode("div", _hoisted_9, [
              _hoisted_10,
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  uicontrol: "text",
                  name: "countryCallingCode",
                  title: _ctx.translate("MobileMessaging_Settings_CountryCode"),
                  modelValue: _ctx.countryCallingCode,
                  "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.countryCallingCode = $event),
                  "full-width": true,
                  maxlength: 4
                }, null, 8, ["title", "modelValue"])
              ])
            ]),
            vue.createElementVNode("div", _hoisted_11, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  uicontrol: "text",
                  name: "newPhoneNumber",
                  modelValue: _ctx.newPhoneNumber,
                  "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.newPhoneNumber = $event),
                  title: _ctx.translate("MobileMessaging_Settings_PhoneNumber"),
                  "full-width": true,
                  maxlength: 80
                }, null, 8, ["modelValue", "title"])
              ])
            ]),
            vue.createElementVNode("div", _hoisted_12, [
              vue.createVNode(_component_SaveButton, {
                class: "valign",
                disabled: !_ctx.canAddNumber || _ctx.isUpdatingPhoneNumbers,
                onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.addPhoneNumber()),
                value: _ctx.translate("General_Add")
              }, null, 8, ["disabled", "value"])
            ]),
            vue.withDirectives(vue.createVNode(_component_Alert, {
              severity: "warning",
              id: "suspiciousPhoneNumber"
            }, {
              default: vue.withCtx(() => [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_SuspiciousPhoneNumber", "54184032")), 1)
              ]),
              _: 1
            }, 512), [
              [vue.vShow, _ctx.showSuspiciousPhoneNumber]
            ])
          ]),
          vue.createElementVNode("div", _hoisted_13, vue.toDisplayString(_ctx.strHelpAddPhone), 1)
        ]),
        vue.createElementVNode("div", _hoisted_14, null, 512),
        _hoisted_15,
        Object.keys(_ctx.phoneNumbers || {}).length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_16, [
          vue.createElementVNode("h3", _hoisted_17, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_ManagePhoneNumbers")), 1)
        ])) : vue.createCommentVNode("", true),
        vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isUpdatingPhoneNumbers }, null, 8, ["loading"]),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.phoneNumbers || [], (verificationData, phoneNumber, index) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "form-group row",
            key: index
          }, [
            vue.createElementVNode("div", _hoisted_18, [
              vue.createElementVNode("span", _hoisted_19, vue.toDisplayString(phoneNumber), 1),
              !verificationData.verified ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("input", {
                key: 0,
                type: "text",
                class: "verificationCode",
                "onUpdate:modelValue": ($event) => _ctx.validationCode[index] = $event,
                placeholder: _ctx.translate("MobileMessaging_Settings_EnterActivationCode"),
                style: { "margin-right": "3.5px" }
              }, null, 8, _hoisted_20)), [
                [vue.vModelText, _ctx.validationCode[index]]
              ]) : vue.createCommentVNode("", true),
              !verificationData.verified ? (vue.openBlock(), vue.createBlock(_component_SaveButton, {
                key: 1,
                disabled: !_ctx.validationCode[index] || _ctx.isUpdatingPhoneNumbers,
                onConfirm: ($event) => _ctx.validateActivationCode(phoneNumber, index),
                value: _ctx.translate("MobileMessaging_Settings_ValidatePhoneNumber")
              }, null, 8, ["disabled", "onConfirm", "value"])) : vue.createCommentVNode("", true),
              vue.createVNode(_component_SaveButton, {
                disabled: _ctx.isUpdatingPhoneNumbers,
                onConfirm: ($event) => _ctx.removePhoneNumber(phoneNumber),
                value: _ctx.translate("General_Remove"),
                style: { "margin-left": "3.5px" }
              }, null, 8, ["disabled", "onConfirm", "value"])
            ]),
            !verificationData.verified ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_21, [
              vue.createElementVNode("div", null, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_VerificationCodeJustSent")) + " ", 1),
                vue.createElementVNode("a", {
                  onClick: ($event) => _ctx.resendVerificationCode(phoneNumber, index)
                }, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_ResendVerification")), 9, _hoisted_22)
              ]),
              vue.createTextVNode("   ")
            ])) : vue.createCommentVNode("", true)
          ]);
        }), 128))
      ]),
      vue.createElementVNode("div", _hoisted_23, [
        vue.createElementVNode("h2", {
          innerHTML: _ctx.$sanitize(_ctx.removeNumberConfirmation)
        }, null, 8, _hoisted_24),
        vue.createElementVNode("input", {
          type: "button",
          role: "yes",
          value: _ctx.translate("General_Yes")
        }, null, 8, _hoisted_25),
        vue.createElementVNode("input", {
          type: "button",
          role: "no",
          value: _ctx.translate("General_No")
        }, null, 8, _hoisted_26)
      ])
    ], 64);
  }
  const ManageMobilePhoneNumbers = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      delegateManagementOptions: {
        type: Array,
        required: true
      },
      delegatedManagement: [Number, Boolean],
      isSuperUser: Boolean,
      defaultCallingCode: String,
      countries: {
        type: Array,
        required: true
      },
      strHelpAddPhone: {
        type: String,
        required: true
      },
      phoneNumbers: Object,
      accountManagedByCurrentUser: Boolean,
      credentialSupplied: Boolean,
      credentialError: String,
      provider: String,
      creditLeft: [Number, String],
      smsProviderOptions: {
        type: Object,
        required: true
      },
      smsProviders: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      DelegateMobileMessagingSettings,
      ManageMobilePhoneNumbers,
      ManageSmsProvider
    }
  });
  const _hoisted_1 = { class: "manageMobileMessagingSettings" };
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = { key: 0 };
  const _hoisted_4 = {
    class: "ui-confirm",
    id: "confirmDeleteAccount"
  };
  const _hoisted_5 = ["value"];
  const _hoisted_6 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DelegateMobileMessagingSettings = vue.resolveComponent("DelegateMobileMessagingSettings");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_ManageSmsProvider = vue.resolveComponent("ManageSmsProvider");
    const _component_ManageMobilePhoneNumbers = vue.resolveComponent("ManageMobilePhoneNumbers");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      _ctx.isSuperUser ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
        key: 0,
        "content-title": _ctx.translate("MobileMessaging_SettingsMenu")
      }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_DelegateMobileMessagingSettings, {
            "delegate-management-options": _ctx.delegateManagementOptions,
            "delegated-management": _ctx.delegatedManagement
          }, null, 8, ["delegate-management-options", "delegated-management"])
        ]),
        _: 1
      }, 8, ["content-title"])) : vue.createCommentVNode("", true),
      _ctx.accountManagedByCurrentUser ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
        key: 1,
        "content-title": _ctx.translate("MobileMessaging_Settings_SMSProvider"),
        feature: "true"
      }, {
        default: vue.withCtx(() => [
          _ctx.isSuperUser && _ctx.delegatedManagement ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_DelegatedSmsProviderOnlyAppliesToYou")), 1)) : vue.createCommentVNode("", true),
          vue.createVNode(_component_ManageSmsProvider, {
            "credential-supplied": _ctx.credentialSupplied,
            "credential-error": _ctx.credentialError,
            provider: _ctx.provider,
            "credit-left": _ctx.creditLeft,
            "sms-provider-options": _ctx.smsProviderOptions,
            "sms-providers": _ctx.smsProviders
          }, null, 8, ["credential-supplied", "credential-error", "provider", "credit-left", "sms-provider-options", "sms-providers"])
        ]),
        _: 1
      }, 8, ["content-title"])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("MobileMessaging_PhoneNumbers")
      }, {
        default: vue.withCtx(() => [
          !_ctx.credentialSupplied ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_3, vue.toDisplayString(_ctx.accountManagedByCurrentUser ? _ctx.translate("MobileMessaging_Settings_CredentialNotProvided") : _ctx.translate("MobileMessaging_Settings_CredentialNotProvidedByAdmin")), 1)) : (vue.openBlock(), vue.createBlock(_component_ManageMobilePhoneNumbers, {
            key: 1,
            "is-super-user": _ctx.isSuperUser,
            "default-calling-code": _ctx.defaultCallingCode,
            countries: _ctx.countries,
            "str-help-add-phone": _ctx.strHelpAddPhone,
            "phone-numbers": _ctx.phoneNumbers
          }, null, 8, ["is-super-user", "default-calling-code", "countries", "str-help-add-phone", "phone-numbers"]))
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createElementVNode("div", _hoisted_4, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("MobileMessaging_Settings_DeleteAccountConfirm")), 1),
        vue.createElementVNode("input", {
          role: "yes",
          type: "button",
          value: _ctx.translate("General_Yes")
        }, null, 8, _hoisted_5),
        vue.createElementVNode("input", {
          role: "no",
          type: "button",
          value: _ctx.translate("General_No")
        }, null, 8, _hoisted_6)
      ])
    ]);
  }
  const AdminPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AdminPage = AdminPage;
  exports2.DelegateMobileMessagingSettings = DelegateMobileMessagingSettings;
  exports2.ManageMobilePhoneNumbers = ManageMobilePhoneNumbers;
  exports2.ManageSmsProvider = ManageSmsProvider;
  exports2.ReportParameters = ReportParameters;
  exports2.SelectPhoneNumbers = SelectPhoneNumbers;
  exports2.SmsProviderCredentials = SmsProviderCredentials;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
