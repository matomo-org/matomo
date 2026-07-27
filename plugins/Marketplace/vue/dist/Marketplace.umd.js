(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Marketplace = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
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

  const _sfc_main$f = vue.defineComponent({
    props: {
      plugin: {
        type: Object,
        required: true
      },
      showOr: {
        type: Boolean,
        default: false
      },
      isAutoUpdatePossible: {
        type: Boolean,
        required: true
      }
    },
    methods: {
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite
        }), params))}`;
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
  const _hoisted_1$e = {
    key: 0,
    onclick: "$(this).css('display', 'none')"
  };
  const _hoisted_2$d = ["href"];
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    return _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$e, [
      _ctx.showOr ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Or")) + " ", 1)
      ], 64)) : vue.createCommentVNode("", true),
      vue.createElementVNode("a", {
        tabindex: "7",
        class: "plugin-details download",
        href: _ctx.linkTo({
          module: "Marketplace",
          action: "download",
          pluginName: _ctx.plugin.name,
          nonce: _ctx.plugin.downloadNonce
        })
      }, vue.toDisplayString(_ctx.translate("General_Download")), 9, _hoisted_2$d)
    ])) : vue.createCommentVNode("", true);
  }
  const DownloadButton = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  const _sfc_main$e = vue.defineComponent({
    props: {
      showAsButton: {
        type: Boolean,
        required: false,
        default: false
      },
      label: {
        type: String,
        required: false
      }
    },
    emits: ["action"]
  });
  const _hoisted_1$d = ["title"];
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("a", {
      tabindex: "7",
      class: vue.normalizeClass({ "btn btn-block": _ctx.showAsButton }),
      href: "",
      title: _ctx.translate("General_MoreDetails"),
      onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.$emit("action"), ["prevent"])),
      onKeyup: _cache[1] || (_cache[1] = vue.withKeys(($event) => _ctx.$emit("action"), ["enter"]))
    }, vue.toDisplayString(_ctx.label ? _ctx.label : _ctx.translate("General_Help")), 43, _hoisted_1$d);
  }
  const MoreDetailsAction = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
  const _sfc_main$d = vue.defineComponent({
    props: {
      plugin: {
        type: Object,
        required: true
      },
      activateNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      installNonce: {
        type: String,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      isAutoUpdatePossible: {
        type: Boolean,
        required: true
      },
      isValidConsumer: {
        type: Boolean,
        required: true
      },
      isMultiServerEnvironment: {
        type: Boolean,
        required: true
      },
      isPluginsAdminEnabled: {
        type: Boolean,
        required: true
      },
      isSuperUser: {
        type: Boolean,
        required: true
      },
      inModal: {
        type: Boolean,
        required: true
      },
      shopVariationUrl: {
        type: String,
        required: false,
        default: ""
      }
    },
    emits: [
      "openDetailsModal",
      "requestTrial",
      "startFreeTrial"
    ],
    components: {
      MoreDetailsAction,
      DownloadButton
    },
    methods: {
      linkToActivate(pluginName) {
        return this.linkTo({
          module: "CorePluginsAdmin",
          action: "activate",
          redirectTo: "referrer",
          nonce: this.activateNonce,
          pluginName
        });
      },
      linkToDeactivate(pluginName) {
        return this.linkTo({
          module: "CorePluginsAdmin",
          action: "deactivate",
          redirectTo: "referrer",
          nonce: this.deactivateNonce,
          pluginName
        });
      },
      linkToInstall(pluginName) {
        return this.linkTo({
          module: "Marketplace",
          action: "installPlugin",
          nonce: this.installNonce,
          pluginName
        });
      },
      linkToUpdate(pluginName) {
        return this.linkTo({
          module: "Marketplace",
          action: "updatePlugin",
          nonce: this.updateNonce,
          pluginName
        });
      },
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite
        }), params))}`;
      }
    }
  });
  const _hoisted_1$c = {
    key: 0,
    class: "alert alert-danger alert-no-background"
  };
  const _hoisted_2$c = {
    key: 0,
    style: { "white-space": "nowrap" }
  };
  const _hoisted_3$c = ["href"];
  const _hoisted_4$a = {
    key: 2,
    class: "alert alert-danger alert-no-background"
  };
  const _hoisted_5$9 = {
    key: 0,
    style: { "white-space": "nowrap" }
  };
  const _hoisted_6$7 = ["href"];
  const _hoisted_7$6 = {
    key: 1,
    class: "alert alert-warning alert-no-background"
  };
  const _hoisted_8$6 = {
    key: 0,
    style: { "white-space": "nowrap" }
  };
  const _hoisted_9$5 = {
    key: 4,
    class: "alert alert-success alert-no-background"
  };
  const _hoisted_10$5 = ["href"];
  const _hoisted_11$4 = ["href"];
  const _hoisted_12$4 = ["title"];
  const _hoisted_13$4 = ["title", "href"];
  const _hoisted_14$3 = {
    key: 8,
    class: "alert alert-warning alert-no-background"
  };
  const _hoisted_15$3 = {
    key: 0,
    style: { "white-space": "nowrap" }
  };
  const _hoisted_16$3 = ["href"];
  const _hoisted_17$3 = ["title"];
  const _hoisted_18$3 = ["title"];
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MoreDetailsAction = vue.resolveComponent("MoreDetailsAction");
    const _component_DownloadButton = vue.resolveComponent("DownloadButton");
    return _ctx.isSuperUser ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
      _ctx.plugin.isMissingLicense ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$c, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_LicenseMissing")) + " ", 1),
        !_ctx.inModal ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$c, [
          _cache[8] || (_cache[8] = vue.createTextVNode("(", -1)),
          vue.createVNode(_component_MoreDetailsAction, {
            onAction: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("openDetailsModal"))
          }),
          _cache[9] || (_cache[9] = vue.createTextVNode(")", -1))
        ])) : vue.createCommentVNode("", true)
      ])) : _ctx.inModal && _ctx.plugin.hasExceededLicense && _ctx.plugin.consumer.loginUrl ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 1,
        class: "btn btn-block",
        tabindex: "7",
        target: "_blank",
        rel: "noreferrer noopener",
        href: _ctx.externalRawLink(_ctx.plugin.consumer.loginUrl)
      }, vue.toDisplayString(_ctx.translate("Marketplace_UpgradeSubscription")), 9, _hoisted_3$c)) : _ctx.plugin.hasExceededLicense ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$a, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_LicenseExceeded")) + " ", 1),
        !_ctx.inModal ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$9, [
          _cache[10] || (_cache[10] = vue.createTextVNode("(", -1)),
          vue.createVNode(_component_MoreDetailsAction, {
            onAction: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("openDetailsModal"))
          }),
          _cache[11] || (_cache[11] = vue.createTextVNode(")", -1))
        ])) : vue.createCommentVNode("", true)
      ])) : _ctx.plugin.canBeUpdated && 0 == _ctx.plugin.missingRequirements.length ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 3 }, [
        _ctx.isAutoUpdatePossible && _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          tabindex: "7",
          class: "btn btn-block",
          href: _ctx.linkToUpdate(_ctx.plugin.name)
        }, vue.toDisplayString(_ctx.translate("CoreUpdater_UpdateTitle")), 9, _hoisted_6$7)) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$6, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_CannotUpdate")) + " ", 1),
          !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8$6, [
            _cache[12] || (_cache[12] = vue.createTextVNode("(", -1)),
            !_ctx.inModal ? (vue.openBlock(), vue.createBlock(_component_MoreDetailsAction, {
              key: 0,
              onAction: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("openDetailsModal"))
            })) : vue.createCommentVNode("", true),
            vue.createVNode(_component_DownloadButton, {
              plugin: _ctx.plugin,
              "show-or": !_ctx.inModal,
              "is-auto-update-possible": _ctx.isAutoUpdatePossible
            }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]),
            _cache[13] || (_cache[13] = vue.createTextVNode(")", -1))
          ])) : vue.createCommentVNode("", true)
        ]))
      ], 64)) : _ctx.plugin.isInstalled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9$5, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Installed")) + " ", 1),
        _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          _cache[14] || (_cache[14] = vue.createTextVNode(" (", -1)),
          vue.createVNode(_component_DownloadButton, {
            plugin: _ctx.plugin,
            "show-or": false,
            "is-auto-update-possible": _ctx.isAutoUpdatePossible
          }, null, 8, ["plugin", "is-auto-update-possible"]),
          _cache[15] || (_cache[15] = vue.createTextVNode(") ", -1))
        ], 64)) : !_ctx.plugin.isInvalid && !_ctx.isMultiServerEnvironment && _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
          _cache[16] || (_cache[16] = vue.createTextVNode(" (", -1)),
          _ctx.plugin.isActivated ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            tabindex: "7",
            href: _ctx.linkToDeactivate(_ctx.plugin.name)
          }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Deactivate")), 9, _hoisted_10$5)) : _ctx.plugin.missingRequirements.length > 0 ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
            vue.createTextVNode(" - ")
          ], 64)) : (vue.openBlock(), vue.createElementBlock("a", {
            key: 2,
            tabindex: "7",
            href: _ctx.linkToActivate(_ctx.plugin.name)
          }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Activate")), 9, _hoisted_11$4)),
          _cache[17] || (_cache[17] = vue.createTextVNode(") ", -1))
        ], 64)) : vue.createCommentVNode("", true)
      ])) : _ctx.plugin.isEligibleForFreeTrial && !_ctx.inModal && _ctx.isPluginsAdminEnabled ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 5,
        class: "btn btn-block purchaseable",
        title: _ctx.translate("Marketplace_StartFreeTrial")
      }, vue.toDisplayString(_ctx.translate("Marketplace_StartFreeTrial")), 9, _hoisted_12$4)) : _ctx.plugin.isEligibleForFreeTrial && _ctx.inModal ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 6,
        class: "btn btn-block addToCartLink",
        target: "_blank",
        title: _ctx.translate("Marketplace_ClickToCompletePurchase"),
        rel: "noreferrer noopener",
        href: _ctx.shopVariationUrl
      }, vue.toDisplayString(_ctx.translate("Marketplace_AddToCart")), 9, _hoisted_13$4)) : !_ctx.inModal && !_ctx.plugin.isDownloadable && (_ctx.plugin.isPaid || _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible) ? (vue.openBlock(), vue.createBlock(_component_MoreDetailsAction, {
        key: 7,
        "show-as-button": true,
        label: _ctx.translate("General_MoreDetails"),
        onAction: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("openDetailsModal"))
      }, null, 8, ["label"])) : _ctx.plugin.missingRequirements.length > 0 || !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14$3, [
        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_CannotInstall")) + " ", 1),
        !_ctx.inModal || _ctx.plugin.missingRequirements.length === 0 && _ctx.plugin.isDownloadable && !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_15$3, [
          _cache[18] || (_cache[18] = vue.createTextVNode("(", -1)),
          !_ctx.inModal ? (vue.openBlock(), vue.createBlock(_component_MoreDetailsAction, {
            key: 0,
            onAction: _cache[4] || (_cache[4] = ($event) => _ctx.$emit("openDetailsModal"))
          })) : vue.createCommentVNode("", true),
          vue.createVNode(_component_DownloadButton, {
            plugin: _ctx.plugin,
            "show-or": !_ctx.inModal,
            "is-auto-update-possible": _ctx.isAutoUpdatePossible
          }, null, 8, ["plugin", "show-or", "is-auto-update-possible"]),
          _cache[19] || (_cache[19] = vue.createTextVNode(")", -1))
        ])) : vue.createCommentVNode("", true)
      ])) : _ctx.isPluginsAdminEnabled && _ctx.plugin.hasDownloadLink ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 9,
        tabindex: "7",
        href: _ctx.linkToInstall(_ctx.plugin.name),
        class: "btn btn-block"
      }, vue.toDisplayString(_ctx.translate("Marketplace_ActionInstall")), 9, _hoisted_16$3)) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 10 }, [
        !_ctx.inModal ? (vue.openBlock(), vue.createBlock(_component_MoreDetailsAction, {
          key: 0,
          "show-as-button": true,
          label: _ctx.translate("General_MoreDetails"),
          onAction: _cache[5] || (_cache[5] = ($event) => _ctx.$emit("openDetailsModal"))
        }, null, 8, ["label"])) : vue.createCommentVNode("", true)
      ], 64))
    ], 64)) : _ctx.plugin.isTrialRequested ? (vue.openBlock(), vue.createElementBlock("a", {
      key: 1,
      tabindex: "7",
      class: "btn btn-block purchaseable disabled",
      href: "",
      title: _ctx.translate("Marketplace_TrialRequested")
    }, vue.toDisplayString(_ctx.translate("Marketplace_TrialRequested")), 9, _hoisted_17$3)) : _ctx.plugin.canTrialBeRequested && !_ctx.plugin.isMissingLicense ? (vue.openBlock(), vue.createElementBlock("a", {
      key: 2,
      tabindex: "7",
      class: "btn btn-block purchaseable",
      href: "",
      onClick: _cache[6] || (_cache[6] = vue.withModifiers(($event) => {
        _ctx.$emit("requestTrial");
      }, ["prevent"])),
      title: _ctx.translate("Marketplace_RequestTrial")
    }, vue.toDisplayString(_ctx.translate("Marketplace_RequestTrial")), 9, _hoisted_18$3)) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 3 }, [
      !_ctx.inModal ? (vue.openBlock(), vue.createBlock(_component_MoreDetailsAction, {
        key: 0,
        "show-as-button": true,
        label: _ctx.translate("General_MoreDetails"),
        onAction: _cache[7] || (_cache[7] = ($event) => _ctx.$emit("openDetailsModal"))
      }, null, 8, ["label"])) : vue.createCommentVNode("", true)
    ], 64));
  }
  const CTAContainer = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const _sfc_main$c = vue.defineComponent({
    props: {
      modelValue: {
        type: Object,
        default: () => null
      }
    },
    emits: ["update:modelValue", "trialRequested"],
    watch: {
      modelValue(newValue) {
        if (!newValue) {
          return;
        }
        CoreHome.Matomo.helper.modalConfirm(
          this.$refs.confirm,
          {
            yes: () => {
              this.requestTrial(newValue);
            }
          },
          {
            onCloseEnd: () => {
              this.$emit("update:modelValue", null);
            }
          }
        );
      }
    },
    computed: {
      plugin() {
        return this.modelValue;
      }
    },
    methods: {
      requestTrial(plugin) {
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "Marketplace.requestTrial"
          },
          { pluginName: plugin.name }
        ).then(() => {
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate(
              "Marketplace_RequestTrialSubmitted",
              plugin.displayName
            ),
            context: "success",
            id: "requestTrialSuccess",
            placeat: "#notificationContainer",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
          this.$emit("trialRequested");
        });
      }
    }
  });
  const _hoisted_1$b = {
    class: "ui-confirm",
    ref: "confirm"
  };
  const _hoisted_2$b = ["value"];
  const _hoisted_3$b = ["value"];
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$b, [
      vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_RequestTrialConfirmTitle", (_a = _ctx.plugin) == null ? void 0 : _a.displayName)), 1),
      vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Marketplace_RequestTrialConfirmEmailWarning")), 1),
      vue.createElementVNode("input", {
        role: "yes",
        type: "button",
        value: _ctx.translate("General_Yes")
      }, null, 8, _hoisted_2$b),
      vue.createElementVNode("input", {
        role: "no",
        type: "button",
        value: _ctx.translate("General_No")
      }, null, 8, _hoisted_3$b)
    ], 512);
  }
  const RequestTrial = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  const { $: $$2 } = window;
  const _sfc_main$b = vue.defineComponent({
    components: { Field: CorePluginsAdmin.Field },
    props: {
      modelValue: {
        type: Object,
        default: () => null
      },
      currentUserEmail: String,
      isValidConsumer: Boolean
    },
    data() {
      return {
        createAccountEmail: this.currentUserEmail || "",
        createAccountError: null,
        trialStartError: null,
        loadingModalCloseCallback: void 0,
        trialStartInProgress: false,
        trialStartSuccessNotificationMessage: "",
        trialStartSuccessNotificationTitle: ""
      };
    },
    emits: ["update:modelValue", "trialStarted", "startTrialStart", "startTrialStop"],
    watch: {
      modelValue(newValue) {
        if (!newValue) {
          return;
        }
        if (this.isValidConsumer) {
          this.trialStartSuccessNotificationMessage = CoreHome.translate(
            "CorePluginsAdmin_PluginFreeTrialStarted",
            "<strong>",
            "</strong>",
            this.plugin.displayName
          );
          this.startFreeTrial();
        } else {
          this.trialStartSuccessNotificationTitle = CoreHome.translate(
            "CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle"
          );
          this.trialStartSuccessNotificationMessage = CoreHome.translate(
            "CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage",
            this.plugin.displayName
          );
          this.showLicenseDialog(false);
        }
      }
    },
    computed: {
      plugin() {
        return this.modelValue;
      },
      trialStartNoLicenseAddHereText() {
        const link = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "manageLicenseKey"
        }))}`;
        return CoreHome.translate(
          "Marketplace_TrialStartNoLicenseAddHere",
          `<a href="${link}">`,
          "</a>"
        );
      },
      trialStartNoLicenseLegalHintText() {
        return CoreHome.translate(
          "Marketplace_TrialStartNoLicenseLegalHint",
          CoreHome.externalLink("https://shop.matomo.org/terms-conditions/"),
          "</a>",
          CoreHome.externalLink("https://matomo.org/privacy-policy/"),
          "</a>"
        );
      }
    },
    methods: {
      closeModal() {
        $$2("#startFreeTrial").modal("close");
      },
      createAccountAndStartFreeTrial() {
        if (!this.createAccountEmail) {
          return;
        }
        this.showLoadingModal(true);
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "Marketplace.createAccount"
          },
          {
            email: this.createAccountEmail
          },
          {
            createErrorNotification: false
          }
        ).then(() => {
          this.startFreeTrial();
        }).catch((error) => {
          if (error.message.startsWith("Marketplace_CreateAccountError")) {
            this.showErrorModal(CoreHome.translate(error.message));
            this.trialStartInProgress = false;
            this.$emit("update:modelValue", null);
          } else {
            this.createAccountError = error.message;
            this.trialStartInProgress = false;
            this.showLicenseDialog(true);
          }
        });
      },
      showLicenseDialog(immediateTransition) {
        const onEnter = (event) => {
          const keycode = event.keyCode ? event.keyCode : event.which;
          if (keycode === 13) {
            this.closeModal();
            this.createAccountAndStartFreeTrial();
          }
        };
        const modalOptions = {
          dismissible: true,
          onOpenEnd: () => {
            const emailField = ".modal.open #email";
            $$2(emailField).focus();
            $$2(emailField).off("keypress").keypress(onEnter);
          },
          onCloseEnd: () => {
            this.createAccountError = null;
            if (this.trialStartInProgress) {
              return;
            }
            this.$emit("update:modelValue", null);
          }
        };
        if (immediateTransition) {
          modalOptions.inDuration = 0;
        }
        $$2("#startFreeTrial").modal(modalOptions).modal("open");
      },
      showErrorModal(error) {
        if (this.trialStartError) {
          return;
        }
        this.trialStartError = error;
        $$2("#startFreeTrial").modal({
          dismissible: true,
          inDuration: 0,
          onCloseEnd: () => {
            this.trialStartError = null;
          }
        }).modal("open");
      },
      showLoadingModal(immediateTransition) {
        if (this.trialStartInProgress) {
          return;
        }
        this.trialStartInProgress = true;
        this.loadingModalCloseCallback = void 0;
        $$2("#startFreeTrial").modal({
          dismissible: false,
          inDuration: immediateTransition ? 0 : void 0,
          onCloseEnd: () => {
            if (!this.loadingModalCloseCallback) {
              return;
            }
            this.loadingModalCloseCallback();
            this.loadingModalCloseCallback = void 0;
          }
        }).modal("open");
      },
      startFreeTrial() {
        this.showLoadingModal(false);
        this.$emit("startTrialStart");
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "Marketplace.startFreeTrial"
          },
          {
            pluginName: this.plugin.name
          },
          {
            createErrorNotification: false
          }
        ).then(() => {
          this.loadingModalCloseCallback = this.startFreeTrialSuccess;
          this.closeModal();
        }).catch((error) => {
          this.showErrorModal(CoreHome.Matomo.helper.htmlDecode(error.message));
          this.trialStartInProgress = false;
          this.$emit("startTrialStop");
        }).finally(() => {
          this.$emit("update:modelValue", null);
        });
      },
      startFreeTrialSuccess() {
        const notificationInstanceId = CoreHome.NotificationsStore.show({
          message: this.trialStartSuccessNotificationMessage,
          title: this.trialStartSuccessNotificationTitle,
          context: "success",
          id: "startTrialSuccess",
          placeat: "#notificationContainer",
          type: "transient"
        });
        CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        this.trialStartInProgress = false;
        this.$emit("trialStarted");
      }
    }
  });
  const _hoisted_1$a = {
    class: "modal",
    id: "startFreeTrial"
  };
  const _hoisted_2$a = {
    key: 0,
    class: "btn-close modal-close"
  };
  const _hoisted_3$a = {
    key: 1,
    class: "modal-content trial-start-in-progress"
  };
  const _hoisted_4$9 = { class: "Piwik_Popover_Loading" };
  const _hoisted_5$8 = { class: "Piwik_Popover_Loading_Name" };
  const _hoisted_6$6 = {
    key: 2,
    class: "modal-content trial-start-error"
  };
  const _hoisted_7$5 = { class: "modal-text" };
  const _hoisted_8$5 = {
    key: 3,
    class: "modal-content trial-start-no-license"
  };
  const _hoisted_9$4 = { class: "modal-text" };
  const _hoisted_10$4 = ["innerHTML"];
  const _hoisted_11$3 = ["innerHTML"];
  const _hoisted_12$3 = ["disabled"];
  const _hoisted_13$3 = ["innerHTML"];
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$a, [
      !_ctx.trialStartInProgress ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2$a, [..._cache[2] || (_cache[2] = [
        vue.createElementVNode("i", { class: "icon-close" }, null, -1)
      ])])) : vue.createCommentVNode("", true),
      _ctx.trialStartInProgress ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$a, [
        vue.createElementVNode("div", _hoisted_4$9, [
          vue.createElementVNode("div", _hoisted_5$8, [
            vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartInProgressTitle")), 1),
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartInProgressText")), 1)
          ])
        ])
      ])) : _ctx.trialStartError ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$6, [
        vue.createElementVNode("div", _hoisted_7$5, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartErrorTitle")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.trialStartError), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartErrorSupport")), 1)
        ])
      ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$5, [
        vue.createElementVNode("div", _hoisted_9$4, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartNoLicenseTitle")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartNoLicenseText")), 1),
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "email",
            modelValue: _ctx.createAccountEmail,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.createAccountEmail = $event),
            "full-width": true,
            title: _ctx.translate("UsersManager_Email")
          }, null, 8, ["modelValue", "title"]),
          _ctx.createAccountError ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 0,
            class: "alert alert-danger",
            innerHTML: _ctx.$sanitize(_ctx.createAccountError)
          }, null, 8, _hoisted_10$4)) : vue.createCommentVNode("", true),
          vue.createElementVNode("p", {
            class: "trial-start-legal-hint",
            innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseLegalHintText)
          }, null, 8, _hoisted_11$3),
          vue.createElementVNode("p", null, [
            vue.createElementVNode("button", {
              class: "btn",
              disabled: !_ctx.createAccountEmail,
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.createAccountAndStartFreeTrial())
            }, vue.toDisplayString(_ctx.translate("Marketplace_TrialStartNoLicenseCreateAccount")), 9, _hoisted_12$3)
          ]),
          vue.createElementVNode("p", {
            class: "add-existing-license",
            innerHTML: _ctx.$sanitize(_ctx.trialStartNoLicenseAddHereText)
          }, null, 8, _hoisted_13$3)
        ])
      ]))
    ]);
  }
  const StartFreeTrial = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  const _sfc_main$a = vue.defineComponent({
    props: {
      plugin: {
        type: Object,
        required: true
      }
    },
    methods: {
      requirement(req) {
        if (req === "php") {
          return "PHP";
        }
        return `${req[0].toUpperCase()}${req.substr(1)}`;
      }
    }
  });
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.plugin.missingRequirements || [], (req, index) => {
      return vue.openBlock(), vue.createElementBlock("div", {
        key: index,
        class: "alert alert-danger"
      }, vue.toDisplayString(_ctx.translate(
        "CorePluginsAdmin_MissingRequirementsNotice",
        _ctx.requirement(req.requirement),
        req.actualVersion,
        req.requiredVersion
      )), 1);
    }), 128);
  }
  const MissingReqsNotice = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const { $: $$1 } = window;
  const _sfc_main$9 = vue.defineComponent({
    components: { MissingReqsNotice, CTAContainer },
    props: {
      modelValue: {
        type: Object,
        default: () => null
      },
      activateNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      installNonce: {
        type: String,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      isAutoUpdatePossible: {
        type: Boolean,
        required: true
      },
      isValidConsumer: {
        type: Boolean,
        required: true
      },
      isMultiServerEnvironment: {
        type: Boolean,
        required: true
      },
      isPluginsAdminEnabled: {
        type: Boolean,
        required: true
      },
      isSuperUser: {
        type: Boolean,
        required: true
      },
      hasSomeAdminAccess: {
        type: Boolean,
        required: true
      },
      numUsers: {
        type: Number,
        required: true
      }
    },
    data() {
      return {
        isLoading: true,
        currentPluginShopVariationUrl: ""
      };
    },
    emits: [
      "requestTrial",
      "startFreeTrial",
      "update:modelValue"
    ],
    watch: {
      modelValue(newValue) {
        if (newValue) {
          this.showPluginDetailsDialog();
        }
      },
      isLoading(newValue) {
        if (newValue === false) {
          this.applyExternalTarget();
          this.applyIframeResize();
        }
      }
    },
    computed: {
      plugin() {
        return this.modelValue;
      },
      pluginLatestVersion() {
        const versions = this.plugin.versions || [{}];
        return versions[versions.length - 1];
      },
      pluginReadmeHtml() {
        var _a;
        return ((_a = this.pluginLatestVersion) == null ? void 0 : _a.readmeHtml) || {};
      },
      pluginDescription() {
        var _a;
        return ((_a = this.pluginReadmeHtml) == null ? void 0 : _a.description) || "";
      },
      pluginDocumentation() {
        var _a;
        return ((_a = this.pluginReadmeHtml) == null ? void 0 : _a.documentation) || "";
      },
      pluginFaq() {
        var _a;
        return ((_a = this.pluginReadmeHtml) == null ? void 0 : _a.faq) || "";
      },
      pluginShop() {
        return this.plugin.shop;
      },
      pluginShopVariations() {
        var _a;
        return ((_a = this.pluginShop) == null ? void 0 : _a.variations) || [];
      },
      pluginReviews() {
        var _a;
        return ((_a = this.pluginShop) == null ? void 0 : _a.reviews) || {};
      },
      pluginKeywords() {
        var _a;
        return ((_a = this.plugin) == null ? void 0 : _a.keywords) || [];
      },
      pluginAuthors() {
        const authors = this.plugin.authors || [];
        return authors.filter((author) => author.name);
      },
      pluginActivity() {
        return this.plugin.activity || {};
      },
      pluginChangelogUrl() {
        return this.plugin.changelog.url || "";
      },
      pluginSupport() {
        return this.plugin.support || [];
      },
      isMatomoPlugin() {
        return ["piwik", "matomo-org"].includes(this.plugin.owner);
      },
      pluginOwner() {
        return this.isMatomoPlugin ? "Matomo" : this.plugin.owner;
      },
      showReviews() {
        return !!(this.pluginReviews && this.pluginReviews.embedUrl && this.pluginReviews.averageRating);
      },
      showMissingLicenseDescription() {
        return this.hasSomeAdminAccess && this.plugin.isMissingLicense;
      },
      showExceededLicenseDescription() {
        return this.hasSomeAdminAccess && this.plugin.hasExceededLicense;
      },
      showMissingRequirementsNoticeIfApplicable() {
        return this.isSuperUser && (this.plugin.isDownloadable || this.plugin.isInstalled);
      },
      showLicenseName() {
        var _a;
        const license = ((_a = this.pluginLatestVersion) == null ? void 0 : _a.license) || {};
        return !!license.name;
      },
      showFreeTrialDropdown() {
        return this.isSuperUser && !this.plugin.isMissingLicense && !this.plugin.isInstalled && !this.plugin.hasExceededLicense && this.plugin.isEligibleForFreeTrial;
      },
      pluginScreenshots() {
        return this.plugin.screenshots || [];
      },
      hasHeaderMetadata() {
        return this.showReviews || !this.plugin.isBundle || (this.plugin.numDownloads || 0) > 0 || this.plugin.lastUpdated && !this.plugin.isBundle;
      },
      pluginShopVariationsPretty() {
        return this.pluginShopVariations.map(
          (variation) => `${variation.name} - ${variation.prettyPrice} / ${variation.period}`
        );
      },
      pluginShopRecommendedVariation() {
        const recommendedVariations = this.pluginShopVariations.filter((v) => v.recommended);
        const defaultVariation = this.pluginShopVariations.length ? this.pluginShopVariations[0] : null;
        return recommendedVariations.length ? recommendedVariations[0] : defaultVariation;
      },
      selectedPluginShopVariationUrl() {
        var _a;
        return this.currentPluginShopVariationUrl ? this.currentPluginShopVariationUrl : ((_a = this.pluginShopRecommendedVariation) == null ? void 0 : _a.addToCartUrl) || "";
      },
      selectedShopVariationUrl() {
        return this.selectedPluginShopVariationUrl || "";
      }
    },
    methods: {
      changeSelectedPluginShopVariationUrl(event) {
        if (event) {
          this.currentPluginShopVariationUrl = event.target.value;
        }
      },
      applyExternalTarget() {
        setTimeout(() => {
          const root = this.$refs.root;
          $$1(".modal-content__main a", root).each((index, a) => {
            const link = $$1(a).attr("href");
            if (link && link.indexOf("http") === 0) {
              $$1(a).attr("target", "_blank");
            }
          });
        });
      },
      scrollElementIntoView(selector) {
        setTimeout(() => {
          const root = this.$refs.root;
          const elements = $$1(selector, root);
          if (elements.length && elements[0] && typeof elements[0].scrollIntoView === "function") {
            elements[0].scrollIntoView({ block: "nearest", behavior: "smooth" });
          }
        });
      },
      isValidEmail(email) {
        return email.match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
      },
      isValidHttpUrl(input) {
        try {
          const url = new URL(input);
          return url.protocol === "http:" || url.protocol === "https:";
        } catch (err) {
          return false;
        }
      },
      getProtocolAndDomain(url) {
        const urlObj = new URL(url);
        return `${urlObj.protocol}//${urlObj.hostname}`;
      },
      applyIframeResize() {
        setTimeout(() => {
          const { iFrameResize } = window;
          if (this.pluginReviews) {
            $$1(() => {
              const $iFrames = $$1("#pluginDetailsModal iframe.reviewIframe");
              for (let i = 0; i < $iFrames.length; i += 1) {
                iFrameResize({ checkOrigin: [this.getProtocolAndDomain(this.pluginReviews.embedUrl)] }, $iFrames[i]);
              }
            });
          }
        });
      },
      getScreenshotBaseName(screenshot) {
        const filename = screenshot.split("/").pop() || "";
        return filename.substring(0, filename.lastIndexOf(".")).split("_").join(" ");
      },
      emitTrialEvent(eventName) {
        const { plugin } = this;
        $$1("#pluginDetailsModal").modal("close");
        setTimeout(() => {
          this.$emit(eventName, plugin);
        }, 250);
      },
      showPluginDetailsDialog() {
        $$1("#pluginDetailsModal").modal({
          dismissible: true,
          onCloseEnd: () => {
            CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
              showPlugin: null
            }));
            this.$emit("update:modelValue", null);
            this.isLoading = true;
          }
        }).modal("open");
        setTimeout(() => {
          this.isLoading = false;
        }, 10);
      },
      getPendingLicenseHelpText(pluginName) {
        return CoreHome.translate(
          "Marketplace_PluginLicenseStatusPending",
          pluginName,
          CoreHome.externalLink("https://shop.matomo.org/my-account/"),
          "</a>"
        );
      },
      getCancelledLicenseHelpText(pluginName) {
        return CoreHome.translate(
          "Marketplace_PluginLicenseStatusCancelled",
          pluginName,
          CoreHome.externalLink("https://shop.matomo.org/my-account/"),
          "</a>"
        );
      },
      getDownloadLinkMissingHelpText(pluginName) {
        return CoreHome.translate(
          "Marketplace_PluginDownloadLinkMissingDescription",
          pluginName,
          CoreHome.externalLink("https://matomo.org/faq/plugins/faq_21/"),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$9 = {
    ref: "root",
    class: "modal",
    id: "pluginDetailsModal"
  };
  const _hoisted_2$9 = { class: "modal-content__header" };
  const _hoisted_3$9 = {
    key: 0,
    class: "plugin-metadata-part1"
  };
  const _hoisted_4$8 = {
    key: 0,
    class: "pair"
  };
  const _hoisted_5$7 = {
    key: 1,
    class: "pair"
  };
  const _hoisted_6$5 = {
    key: 2,
    class: "pair"
  };
  const _hoisted_7$4 = {
    key: 3,
    class: "pair"
  };
  const _hoisted_8$4 = {
    key: 4,
    class: "pair"
  };
  const _hoisted_9$3 = { class: "plugin-description" };
  const _hoisted_10$3 = {
    key: 1,
    class: "alert alert-warning"
  };
  const _hoisted_11$2 = {
    key: 2,
    class: "alert alert-warning"
  };
  const _hoisted_12$2 = {
    key: 3,
    class: "alert alert-danger"
  };
  const _hoisted_13$2 = {
    key: 4,
    class: "alert alert-warning"
  };
  const _hoisted_14$2 = ["innerHTML"];
  const _hoisted_15$2 = ["innerHTML"];
  const _hoisted_16$2 = ["innerHTML"];
  const _hoisted_17$2 = ["innerHTML"];
  const _hoisted_18$2 = { class: "plugin-metadata-part2" };
  const _hoisted_19$2 = {
    key: 0,
    class: "pair"
  };
  const _hoisted_20 = {
    key: 1,
    class: "pair"
  };
  const _hoisted_21 = { class: "pair" };
  const _hoisted_22 = ["href"];
  const _hoisted_23 = ["href"];
  const _hoisted_24 = { key: 2 };
  const _hoisted_25 = { key: 3 };
  const _hoisted_26 = { class: "pair" };
  const _hoisted_27 = ["href"];
  const _hoisted_28 = ["href"];
  const _hoisted_29 = ["href"];
  const _hoisted_30 = {
    key: 0,
    class: "pair"
  };
  const _hoisted_31 = {
    key: 1,
    class: "pair"
  };
  const _hoisted_32 = ["href"];
  const _hoisted_33 = { key: 1 };
  const _hoisted_34 = ["innerHTML"];
  const _hoisted_35 = { key: 0 };
  const _hoisted_36 = ["href"];
  const _hoisted_37 = { key: 1 };
  const _hoisted_38 = ["href"];
  const _hoisted_39 = ["innerHTML"];
  const _hoisted_40 = {
    key: 0,
    class: "plugin-screenshots"
  };
  const _hoisted_41 = { class: "thumbnails" };
  const _hoisted_42 = ["src"];
  const _hoisted_43 = {
    key: 1,
    class: "plugin-documentation"
  };
  const _hoisted_44 = ["innerHTML"];
  const _hoisted_45 = {
    key: 2,
    class: "plugin-faq"
  };
  const _hoisted_46 = ["innerHTML"];
  const _hoisted_47 = {
    key: 3,
    class: "plugin-reviews",
    id: "reviews"
  };
  const _hoisted_48 = ["id", "src"];
  const _hoisted_49 = {
    key: 0,
    class: "matomo-badge matomo-badge-modal",
    src: "plugins/Marketplace/images/matomo-badge.png",
    "aria-label": "Matomo plugin",
    alt: ""
  };
  const _hoisted_50 = { class: "cta-container cta-container-modal" };
  const _hoisted_51 = {
    key: 0,
    class: "free-trial"
  };
  const _hoisted_52 = { class: "free-trial-lead-in" };
  const _hoisted_53 = ["title"];
  const _hoisted_54 = ["value", "title"];
  const _hoisted_55 = {
    key: 1,
    class: "matomo-badge matomo-badge-modal",
    src: "plugins/Marketplace/images/matomo-badge.png",
    "aria-label": "Matomo plugin",
    alt: ""
  };
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    var _a, _b, _c, _d, _e, _f;
    const _component_MissingReqsNotice = vue.resolveComponent("MissingReqsNotice");
    const _component_CTAContainer = vue.resolveComponent("CTAContainer");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$9, [
      !_ctx.isLoading ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        class: vue.normalizeClass(["modal-content", { "modal-content--simple-header": !_ctx.hasHeaderMetadata }])
      }, [
        vue.createElementVNode("div", _hoisted_2$9, [
          _cache[7] || (_cache[7] = vue.createElementVNode("span", { class: "btn-close modal-close" }, [
            vue.createElementVNode("i", { class: "icon-close" })
          ], -1)),
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.plugin && _ctx.plugin.displayName ? _ctx.plugin.displayName : "Plugin details"), 1),
          _ctx.hasHeaderMetadata ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$9, [
            _cache[6] || (_cache[6] = vue.createElementVNode("h3", { class: "sr-only" }, "Plugin details — part 1", -1)),
            vue.createElementVNode("dl", null, [
              _ctx.showReviews ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$8, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_Reviews")), 1),
                vue.createElementVNode("dd", null, [
                  _cache[5] || (_cache[5] = vue.createElementVNode("img", {
                    class: "star-icon reviews-icon",
                    src: "plugins/Marketplace/images/star.svg",
                    alt: ""
                  }, null, -1)),
                  vue.createElementVNode("a", {
                    onClick: _cache[0] || (_cache[0] = ($event) => _ctx.scrollElementIntoView("#reviews"))
                  }, vue.toDisplayString(_ctx.pluginReviews.averageRating), 1)
                ])
              ])) : vue.createCommentVNode("", true),
              !_ctx.plugin.isBundle ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$7, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Version")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.plugin.latestVersion), 1)
              ])) : vue.createCommentVNode("", true),
              (_ctx.plugin.numDownloads || 0) > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$5, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("General_Downloads")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.plugin.numDownloadsPretty), 1)
              ])) : vue.createCommentVNode("", true),
              _ctx.plugin.lastUpdated && !_ctx.plugin.isBundle ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$4, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_LastUpdated")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.plugin.lastUpdated), 1)
              ])) : vue.createCommentVNode("", true),
              !_ctx.plugin.isBundle ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$4, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_Developer")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.pluginOwner), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ])) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("div", {
          class: vue.normalizeClass(["modal-content__main", { "modal-content__main--with-free-trial": _ctx.showFreeTrialDropdown }])
        }, [
          vue.createElementVNode("div", _hoisted_9$3, [
            _ctx.showMissingRequirementsNoticeIfApplicable ? (vue.openBlock(), vue.createBlock(_component_MissingReqsNotice, {
              key: 0,
              plugin: _ctx.plugin
            }, null, 8, ["plugin"])) : vue.createCommentVNode("", true),
            _ctx.isMultiServerEnvironment ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_10$3, vue.toDisplayString(_ctx.translate("Marketplace_MultiServerEnvironmentWarning")), 1)) : !_ctx.isAutoUpdatePossible ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_11$2, vue.toDisplayString(_ctx.translate(
              "Marketplace_AutoUpdateDisabledWarning",
              "'[General]enable_auto_update=1'",
              "'config/config.ini.php'"
            )), 1)) : vue.createCommentVNode("", true),
            _ctx.showMissingLicenseDescription ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_12$2, vue.toDisplayString(_ctx.translate("Marketplace_PluginLicenseMissingDescription")), 1)) : _ctx.showExceededLicenseDescription ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_13$2, vue.toDisplayString(_ctx.translate("Marketplace_PluginLicenseExceededDescription")), 1)) : _ctx.plugin.licenseStatus === "Pending" && !_ctx.isMultiServerEnvironment ? (vue.openBlock(), vue.createElementBlock("div", {
              key: 5,
              class: "alert alert-warning",
              innerHTML: _ctx.$sanitize(_ctx.getPendingLicenseHelpText(_ctx.plugin.displayName))
            }, null, 8, _hoisted_14$2)) : _ctx.plugin.licenseStatus === "Cancelled" && !_ctx.isMultiServerEnvironment ? (vue.openBlock(), vue.createElementBlock("div", {
              key: 6,
              class: "alert alert-warning",
              innerHTML: _ctx.$sanitize(_ctx.getCancelledLicenseHelpText(_ctx.plugin.displayName))
            }, null, 8, _hoisted_15$2)) : !_ctx.plugin.hasDownloadLink && !_ctx.isMultiServerEnvironment && (_ctx.plugin.licenseStatus || !_ctx.plugin.isPaid) ? (vue.openBlock(), vue.createElementBlock("div", {
              key: 7,
              class: "alert alert-warning",
              innerHTML: _ctx.$sanitize(_ctx.getDownloadLinkMissingHelpText(_ctx.plugin.displayName))
            }, null, 8, _hoisted_16$2)) : vue.createCommentVNode("", true),
            vue.createElementVNode("div", {
              innerHTML: _ctx.$sanitize(_ctx.pluginDescription)
            }, null, 8, _hoisted_17$2)
          ]),
          vue.createElementVNode("div", _hoisted_18$2, [
            _cache[8] || (_cache[8] = vue.createElementVNode("hr", null, null, -1)),
            _cache[9] || (_cache[9] = vue.createElementVNode("h3", { class: "sr-only" }, "Plugin details — part 2", -1)),
            vue.createElementVNode("dl", null, [
              !_ctx.plugin.isBundle ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_19$2, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Version")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.plugin.latestVersion), 1)
              ])) : vue.createCommentVNode("", true),
              _ctx.pluginKeywords ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_20, [
                vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_PluginKeywords")), 1),
                vue.createElementVNode("dd", null, vue.toDisplayString(_ctx.pluginKeywords.join(", ")), 1)
              ])) : vue.createCommentVNode("", true),
              !_ctx.plugin.isBundle ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 2 }, [
                vue.createElementVNode("div", _hoisted_21, [
                  vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_Authors")), 1),
                  vue.createElementVNode("dd", null, [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginAuthors, (author, index) => {
                      return vue.openBlock(), vue.createElementBlock(vue.Fragment, {
                        key: `author-${index}`
                      }, [
                        author.homepage ? (vue.openBlock(), vue.createElementBlock("a", {
                          key: 0,
                          target: "_blank",
                          rel: "noreferrer noopener",
                          href: author.homepage
                        }, vue.toDisplayString(author.name), 9, _hoisted_22)) : author.email && _ctx.isValidEmail(author.email) ? (vue.openBlock(), vue.createElementBlock("a", {
                          key: 1,
                          href: `mailto:${encodeURIComponent(author.email)}`
                        }, vue.toDisplayString(author.name), 9, _hoisted_23)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_24, vue.toDisplayString(author.name), 1)),
                        index < _ctx.pluginAuthors.length - 1 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_25, ", ")) : vue.createCommentVNode("", true)
                      ], 64);
                    }), 128))
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_26, [
                  vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Websites")), 1),
                  vue.createElementVNode("dd", null, [
                    _ctx.plugin.homepage ? (vue.openBlock(), vue.createElementBlock("a", {
                      key: 0,
                      target: "_blank",
                      rel: "noreferrer noopener",
                      href: _ctx.plugin.homepage
                    }, vue.toDisplayString(_ctx.translate("Marketplace_PluginWebsite")), 9, _hoisted_27)) : vue.createCommentVNode("", true),
                    _ctx.pluginChangelogUrl ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
                      _ctx.plugin.homepage ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                        vue.createTextVNode(", ")
                      ], 64)) : vue.createCommentVNode("", true),
                      vue.createElementVNode("a", {
                        target: "_blank",
                        rel: "noreferrer noopener",
                        href: _ctx.externalRawLink(_ctx.pluginChangelogUrl)
                      }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Changelog")), 9, _hoisted_28)
                    ], 64)) : vue.createCommentVNode("", true),
                    _ctx.plugin.repositoryUrl ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 2 }, [
                      _ctx.plugin.homepage || _ctx.pluginChangelogUrl ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                        vue.createTextVNode(", ")
                      ], 64)) : vue.createCommentVNode("", true),
                      vue.createElementVNode("a", {
                        target: "_blank",
                        rel: "noreferrer noopener",
                        href: _ctx.externalRawLink(_ctx.plugin.repositoryUrl)
                      }, "GitHub", 8, _hoisted_29)
                    ], 64)) : vue.createCommentVNode("", true)
                  ])
                ]),
                _ctx.pluginActivity && _ctx.pluginActivity.numCommits ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_30, [
                  vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Activity")), 1),
                  vue.createElementVNode("dd", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.plugin.activity.numCommits) + " commits ", 1),
                    Number((_a = _ctx.pluginActivity) == null ? void 0 : _a.numContributors) > 1 ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                      vue.createTextVNode(vue.toDisplayString(" " + _ctx.translate("Marketplace_ByXDevelopers", String(_ctx.pluginActivity.numContributors))), 1)
                    ], 64)) : vue.createCommentVNode("", true),
                    ((_b = _ctx.pluginActivity) == null ? void 0 : _b.lastCommitDate) ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
                      vue.createTextVNode(vue.toDisplayString(" " + _ctx.translate("Marketplace_LastCommitTime", _ctx.pluginActivity.lastCommitDate)), 1)
                    ], 64)) : vue.createCommentVNode("", true)
                  ])
                ])) : vue.createCommentVNode("", true),
                _ctx.showLicenseName ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_31, [
                  vue.createElementVNode("dt", null, vue.toDisplayString(_ctx.translate("Marketplace_License")), 1),
                  vue.createElementVNode("dd", null, [
                    ((_c = _ctx.pluginLatestVersion.license) == null ? void 0 : _c.url) ? (vue.openBlock(), vue.createElementBlock("a", {
                      key: 0,
                      rel: "noreferrer noopener",
                      href: (_d = _ctx.pluginLatestVersion.license) == null ? void 0 : _d.url,
                      target: "_blank"
                    }, vue.toDisplayString((_e = _ctx.pluginLatestVersion.license) == null ? void 0 : _e.name), 9, _hoisted_32)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_33, vue.toDisplayString((_f = _ctx.pluginLatestVersion.license) == null ? void 0 : _f.name), 1))
                  ])
                ])) : vue.createCommentVNode("", true),
                _ctx.pluginSupport.length ? (vue.openBlock(true), vue.createElementBlock(vue.Fragment, { key: 2 }, vue.renderList(_ctx.pluginSupport, (support, index) => {
                  return vue.openBlock(), vue.createElementBlock("div", {
                    class: "pair",
                    key: `support-${index}`
                  }, [
                    support.name && support.value ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                      vue.createElementVNode("dt", {
                        innerHTML: _ctx.$sanitize(support.name)
                      }, null, 8, _hoisted_34),
                      _ctx.isValidHttpUrl(support.value) ? (vue.openBlock(), vue.createElementBlock("dd", _hoisted_35, [
                        vue.createElementVNode("a", {
                          target: "_blank",
                          rel: "noreferrer noopener",
                          href: _ctx.externalRawLink(_ctx.$sanitize(support.value))
                        }, vue.toDisplayString(_ctx.$sanitize(support.value)), 9, _hoisted_36)
                      ])) : _ctx.isValidEmail(support.value) ? (vue.openBlock(), vue.createElementBlock("dd", _hoisted_37, [
                        vue.createElementVNode("a", {
                          href: `mailto:${encodeURIComponent(support.value)}`
                        }, vue.toDisplayString(_ctx.$sanitize(support.value)), 9, _hoisted_38)
                      ])) : (vue.openBlock(), vue.createElementBlock("dd", {
                        key: 2,
                        innerHTML: _ctx.$sanitize(support.value)
                      }, null, 8, _hoisted_39))
                    ], 64)) : vue.createCommentVNode("", true)
                  ]);
                }), 128)) : vue.createCommentVNode("", true)
              ], 64)) : vue.createCommentVNode("", true)
            ])
          ]),
          _ctx.pluginScreenshots.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_40, [
            _cache[10] || (_cache[10] = vue.createElementVNode("hr", null, null, -1)),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Marketplace_Screenshots")), 1),
            vue.createElementVNode("div", _hoisted_41, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginScreenshots, (screenshot) => {
                return vue.openBlock(), vue.createElementBlock("figure", {
                  key: `screenshot-${screenshot}`
                }, [
                  vue.createElementVNode("img", {
                    src: `${screenshot}?w=800`,
                    width: "800",
                    alt: ""
                  }, null, 8, _hoisted_42),
                  vue.createElementVNode("figcaption", null, vue.toDisplayString(_ctx.getScreenshotBaseName(screenshot)), 1)
                ]);
              }), 128))
            ])
          ])) : vue.createCommentVNode("", true),
          _ctx.pluginDocumentation ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_43, [
            _cache[11] || (_cache[11] = vue.createElementVNode("hr", null, null, -1)),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("General_Documentation")), 1),
            vue.createElementVNode("div", {
              innerHTML: _ctx.$sanitize(_ctx.pluginDocumentation)
            }, null, 8, _hoisted_44)
          ])) : vue.createCommentVNode("", true),
          _ctx.pluginFaq ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_45, [
            _cache[12] || (_cache[12] = vue.createElementVNode("hr", null, null, -1)),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("General_Faq")), 1),
            vue.createElementVNode("div", {
              innerHTML: _ctx.$sanitize(_ctx.pluginFaq)
            }, null, 8, _hoisted_46)
          ])) : vue.createCommentVNode("", true),
          _ctx.showReviews ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_47, [
            _cache[13] || (_cache[13] = vue.createElementVNode("hr", null, null, -1)),
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Marketplace_Reviews")), 1),
            vue.createElementVNode("iframe", {
              class: "reviewIframe",
              style: vue.normalizeStyle(_ctx.pluginReviews.height ? `height: ${_ctx.pluginReviews.height}px;` : ""),
              id: _ctx.pluginReviews.embedUrl.replace(/[\W_]+/g, " "),
              src: _ctx.pluginReviews.embedUrl
            }, null, 12, _hoisted_48)
          ])) : vue.createCommentVNode("", true)
        ], 2),
        vue.createElementVNode("div", {
          class: vue.normalizeClass(["modal-content__footer", { "modal-content__footer--with-free-trial": _ctx.showFreeTrialDropdown }])
        }, [
          _ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (vue.openBlock(), vue.createElementBlock("img", _hoisted_49)) : vue.createCommentVNode("", true),
          vue.createElementVNode("div", _hoisted_50, [
            _ctx.showFreeTrialDropdown ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_51, [
              vue.createElementVNode("div", _hoisted_52, vue.toDisplayString(_ctx.translate("Marketplace_TryFreeTrialTitle")), 1),
              vue.withDirectives(vue.createElementVNode("select", {
                class: "free-trial-dropdown",
                title: `${_ctx.translate("Marketplace_ShownPriceIsExclTax")} ${_ctx.translate(
                  "Marketplace_CurrentNumPiwikUsers",
                  _ctx.numUsers
                )}`,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.selectedPluginShopVariationUrl = $event),
                onChange: _cache[2] || (_cache[2] = (...args) => _ctx.changeSelectedPluginShopVariationUrl && _ctx.changeSelectedPluginShopVariationUrl(...args))
              }, [
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.plugin.shop.variations, (variation, index) => {
                  return vue.openBlock(), vue.createElementBlock("option", {
                    key: `var-${index}`,
                    value: variation.addToCartUrl,
                    title: `${_ctx.translate(
                      "Marketplace_PriceExclTax",
                      String(variation.price),
                      variation.currency
                    )} ${_ctx.translate("Marketplace_CurrentNumPiwikUsers", _ctx.numUsers)}`
                  }, vue.toDisplayString(variation.name) + " - " + vue.toDisplayString(variation.prettyPrice) + " / " + vue.toDisplayString(variation.period), 9, _hoisted_54);
                }), 128))
              ], 40, _hoisted_53), [
                [vue.vModelSelect, _ctx.selectedPluginShopVariationUrl]
              ])
            ])) : vue.createCommentVNode("", true),
            vue.createVNode(_component_CTAContainer, {
              "is-super-user": _ctx.isSuperUser,
              "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
              "is-multi-server-environment": _ctx.isMultiServerEnvironment,
              "is-valid-consumer": _ctx.isValidConsumer,
              "is-auto-update-possible": _ctx.isAutoUpdatePossible,
              "activate-nonce": _ctx.activateNonce,
              "deactivate-nonce": _ctx.deactivateNonce,
              "install-nonce": _ctx.installNonce,
              "update-nonce": _ctx.updateNonce,
              plugin: _ctx.plugin,
              "in-modal": true,
              "shop-variation-url": _ctx.selectedShopVariationUrl,
              onRequestTrial: _cache[3] || (_cache[3] = ($event) => _ctx.emitTrialEvent("requestTrial")),
              onStartFreeTrial: _cache[4] || (_cache[4] = ($event) => _ctx.emitTrialEvent("startFreeTrial"))
            }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "shop-variation-url"])
          ]),
          !_ctx.showFreeTrialDropdown && _ctx.isMatomoPlugin ? (vue.openBlock(), vue.createElementBlock("img", _hoisted_55)) : vue.createCommentVNode("", true)
        ], 2)
      ], 2)) : vue.createCommentVNode("", true)
    ], 512);
  }
  const PluginDetailsModal = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const { $ } = window;
  const _sfc_main$8 = vue.defineComponent({
    props: {
      currentUserEmail: String,
      pluginsToShow: {
        type: Array,
        required: true
      },
      isAutoUpdatePossible: {
        type: Boolean,
        required: true
      },
      isSuperUser: {
        type: Boolean,
        required: true
      },
      isValidConsumer: {
        type: Boolean,
        required: true
      },
      isMultiServerEnvironment: {
        type: Boolean,
        required: true
      },
      isPluginsAdminEnabled: {
        type: Boolean,
        required: true
      },
      hasSomeAdminAccess: {
        type: Boolean,
        required: true
      },
      activateNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      installNonce: {
        type: String,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      numUsers: {
        type: Number,
        required: true
      }
    },
    data() {
      return {
        showRequestTrialForPlugin: null,
        showStartFreeTrialForPlugin: null,
        showPluginDetailsForPlugin: null
      };
    },
    components: {
      PluginDetailsModal,
      CTAContainer,
      RequestTrial,
      StartFreeTrial
    },
    emits: ["triggerUpdate", "startTrialStart", "startTrialStop"],
    watch: {
      pluginsToShow(newValue, oldValue) {
        if (newValue && newValue !== oldValue) {
          this.shrinkDescriptionIfMultilineTitle();
          this.parseShowPluginParameter();
        }
      }
    },
    mounted() {
      $(window).resize(() => {
        this.shrinkDescriptionIfMultilineTitle();
      });
      vue.watch(() => CoreHome.MatomoUrl.hashParsed.value.showPlugin, (newValue, oldValue) => {
        if (newValue && newValue !== oldValue) {
          this.parseShowPluginParameter();
        }
      });
      this.parseShowPluginParameter();
    },
    methods: {
      parseShowPluginParameter() {
        const { showPlugin, pluginType, query } = CoreHome.MatomoUrl.hashParsed.value;
        if (!showPlugin) {
          return;
        }
        const pluginToShow = this.pluginsToShow.filter(
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          (plugin) => plugin.name === showPlugin
        );
        if (pluginToShow.length === 1) {
          const [plugin] = pluginToShow;
          this.openDetailsModal(plugin);
          this.scrollPluginCardIntoView(plugin);
        } else if (pluginType !== "" || query !== "") {
          CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
            pluginType: "plugins",
            query: null
          }));
        }
      },
      shrinkDescriptionIfMultilineTitle() {
        const $nodes = $(".marketplace .card-holder");
        if (!$nodes || !$nodes.length) {
          return;
        }
        $nodes.each((index, node) => {
          var _a, _b;
          const $card = $(node);
          const $titleText = $card.find(".card-title");
          const $alertText = $card.find(".card-content-bottom .alert");
          const hasDownloads = $card.hasClass("card-with-downloads");
          let titleLines = 1;
          if ($titleText.length) {
            const elHeight = +$titleText.height();
            const lineHeight = +$titleText.css("line-height").replace("px", "");
            if (lineHeight) {
              titleLines = (_a = Math.ceil(elHeight / lineHeight)) != null ? _a : 1;
            }
          }
          let alertLines = 0;
          if ($alertText.length) {
            const elHeight = +$alertText.height();
            const lineHeight = +$alertText.css("line-height").replace("px", "");
            if (lineHeight) {
              alertLines = (_b = Math.ceil(elHeight / lineHeight)) != null ? _b : 1;
            }
          }
          const $cardDescription = $card.find(".card-description");
          if ($cardDescription.length) {
            const cardDescription = $cardDescription[0];
            let clampedLines = 0;
            if (hasDownloads) {
              if (titleLines >= 2 || alertLines > 2 || titleLines + alertLines >= 4) {
                clampedLines = 2;
              }
              if (titleLines + alertLines >= 5) {
                clampedLines = 1;
              }
            } else if (titleLines + alertLines >= 5) {
              clampedLines = 2;
            }
            if (clampedLines) {
              cardDescription.setAttribute("data-clamp", `${clampedLines}`);
            } else {
              cardDescription.removeAttribute("data-clamp");
            }
          }
        });
      },
      clickCard(event, plugin) {
        if ($(event.target).closest("a:not(.card-title-link)").length) {
          return;
        }
        event.stopPropagation();
        this.openDetailsModal(plugin);
      },
      openDetailsModal(plugin) {
        this.showPluginDetailsForPlugin = plugin;
      },
      scrollPluginCardIntoView(plugin) {
        const $titles = $(`.pluginListContainer .card-title:contains("${plugin.displayName}")`);
        if ($titles.length !== 1) {
          return;
        }
        const $cards = $titles.parents(".card");
        if ($cards.length !== 1 || !$cards[0].scrollIntoView) {
          return;
        }
        $cards[0].scrollIntoView({ block: "start", behavior: "smooth" });
      },
      requestTrial(plugin) {
        this.showRequestTrialForPlugin = plugin;
      },
      startFreeTrial(plugin) {
        this.showStartFreeTrialForPlugin = plugin;
      }
    }
  });
  const _hoisted_1$8 = {
    key: 0,
    class: "pluginListContainer row"
  };
  const _hoisted_2$8 = ["onClick"];
  const _hoisted_3$8 = { class: "card" };
  const _hoisted_4$7 = { class: "card-content" };
  const _hoisted_5$6 = ["src"];
  const _hoisted_6$4 = { class: "content-container" };
  const _hoisted_7$3 = { class: "card-content-top" };
  const _hoisted_8$3 = {
    key: 0,
    class: "matomo-badge matomo-badge-top",
    src: "plugins/Marketplace/images/matomo-badge.png",
    "aria-label": "Matomo plugin",
    alt: ""
  };
  const _hoisted_9$2 = { class: "price" };
  const _hoisted_10$2 = ["onClick"];
  const _hoisted_11$1 = { class: "card-title" };
  const _hoisted_12$1 = { class: "card-description" };
  const _hoisted_13$1 = { class: "card-content-bottom" };
  const _hoisted_14$1 = {
    key: 0,
    class: "downloads"
  };
  const _hoisted_15$1 = { class: "owner" };
  const _hoisted_16$1 = { key: 0 };
  const _hoisted_17$1 = { key: 1 };
  const _hoisted_18$1 = { class: "cta-container" };
  const _hoisted_19$1 = {
    key: 1,
    class: "matomo-badge matomo-badge-bottom",
    src: "plugins/Marketplace/images/matomo-badge.png",
    "aria-label": "Matomo plugin",
    alt: ""
  };
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_RequestTrial = vue.resolveComponent("RequestTrial");
    const _component_StartFreeTrial = vue.resolveComponent("StartFreeTrial");
    const _component_PluginDetailsModal = vue.resolveComponent("PluginDetailsModal");
    const _component_CTAContainer = vue.resolveComponent("CTAContainer");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createVNode(_component_RequestTrial, {
        modelValue: _ctx.showRequestTrialForPlugin,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.showRequestTrialForPlugin = $event),
        onTrialRequested: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("triggerUpdate"))
      }, null, 8, ["modelValue"]),
      vue.createVNode(_component_StartFreeTrial, {
        "current-user-email": _ctx.currentUserEmail,
        "is-valid-consumer": _ctx.isValidConsumer,
        modelValue: _ctx.showStartFreeTrialForPlugin,
        "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.showStartFreeTrialForPlugin = $event),
        onTrialStarted: _cache[3] || (_cache[3] = ($event) => {
          _ctx.$emit("triggerUpdate");
        }),
        onStartTrialStart: _cache[4] || (_cache[4] = ($event) => {
          _ctx.$emit("startTrialStart");
        }),
        onStartTrialStop: _cache[5] || (_cache[5] = ($event) => {
          _ctx.$emit("startTrialStop");
        })
      }, null, 8, ["current-user-email", "is-valid-consumer", "modelValue"]),
      vue.createVNode(_component_PluginDetailsModal, {
        modelValue: _ctx.showPluginDetailsForPlugin,
        "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.showPluginDetailsForPlugin = $event),
        "is-super-user": _ctx.isSuperUser,
        "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
        "is-multi-server-environment": _ctx.isMultiServerEnvironment,
        "is-valid-consumer": _ctx.isValidConsumer,
        "is-auto-update-possible": _ctx.isAutoUpdatePossible,
        "has-some-admin-access": _ctx.hasSomeAdminAccess,
        "deactivate-nonce": _ctx.deactivateNonce,
        "activate-nonce": _ctx.activateNonce,
        "install-nonce": _ctx.installNonce,
        "update-nonce": _ctx.updateNonce,
        "num-users": _ctx.numUsers,
        onRequestTrial: _cache[7] || (_cache[7] = ($event) => _ctx.requestTrial($event)),
        onStartFreeTrial: _cache[8] || (_cache[8] = ($event) => _ctx.startFreeTrial($event))
      }, null, 8, ["modelValue", "is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "has-some-admin-access", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"]),
      _ctx.pluginsToShow.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginsToShow, (plugin) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "col s12 m6 l4",
            key: plugin.name
          }, [
            vue.createElementVNode("div", {
              class: vue.normalizeClass(`card-holder ${(plugin.numDownloads || 0) > 0 ? "card-with-downloads" : ""}`),
              onClick: ($event) => _ctx.clickCard($event, plugin)
            }, [
              vue.createElementVNode("div", _hoisted_3$8, [
                vue.createElementVNode("div", _hoisted_4$7, [
                  vue.createElementVNode("img", {
                    src: `${plugin.coverImage}?w=880&h=480`,
                    alt: "",
                    class: "cover-image"
                  }, null, 8, _hoisted_5$6),
                  vue.createElementVNode("div", _hoisted_6$4, [
                    vue.createElementVNode("div", _hoisted_7$3, [
                      "piwik" == plugin.owner || "matomo-org" == plugin.owner ? (vue.openBlock(), vue.createElementBlock("img", _hoisted_8$3)) : vue.createCommentVNode("", true),
                      vue.createElementVNode("div", _hoisted_9$2, [
                        plugin.priceFrom ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate(
                            "Marketplace_PriceFromPerPeriod",
                            plugin.priceFrom.prettyPrice,
                            plugin.priceFrom.period
                          )), 1)
                        ], 64)) : plugin.isFree ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_Free")), 1)
                        ], 64)) : vue.createCommentVNode("", true)
                      ]),
                      vue.createElementVNode("a", {
                        onClick: vue.withModifiers(($event) => _ctx.clickCard($event, plugin), ["prevent"]),
                        class: "card-title-link",
                        href: "#",
                        tabindex: "7"
                      }, [
                        _cache[10] || (_cache[10] = vue.createElementVNode("div", { class: "card-focus" }, null, -1)),
                        vue.createElementVNode("h2", _hoisted_11$1, [
                          vue.createTextVNode(vue.toDisplayString(plugin.displayName), 1),
                          _cache[9] || (_cache[9] = vue.createElementVNode("span", { class: "card-title-chevron" }, " ›", -1))
                        ])
                      ], 8, _hoisted_10$2),
                      vue.createElementVNode("div", _hoisted_12$1, vue.toDisplayString(plugin.description), 1)
                    ]),
                    vue.createElementVNode("div", _hoisted_13$1, [
                      (plugin.numDownloads || 0) > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14$1, vue.toDisplayString(plugin.numDownloadsPretty) + " " + vue.toDisplayString(_ctx.translate("General_Downloads").toLowerCase()), 1)) : vue.createCommentVNode("", true),
                      vue.createElementVNode("div", _hoisted_15$1, [
                        vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_CreatedBy")) + " ", 1),
                        plugin.owner === "piwik" || plugin.owner === "matomo-org" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_16$1, " Matomo")) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_17$1, vue.toDisplayString(plugin.owner), 1))
                      ]),
                      vue.createElementVNode("div", _hoisted_18$1, [
                        vue.createVNode(_component_CTAContainer, {
                          "is-super-user": _ctx.isSuperUser,
                          "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
                          "is-multi-server-environment": _ctx.isMultiServerEnvironment,
                          "is-valid-consumer": _ctx.isValidConsumer,
                          "is-auto-update-possible": _ctx.isAutoUpdatePossible,
                          "activate-nonce": _ctx.activateNonce,
                          "deactivate-nonce": _ctx.deactivateNonce,
                          "install-nonce": _ctx.installNonce,
                          "update-nonce": _ctx.updateNonce,
                          plugin,
                          "in-modal": false,
                          onOpenDetailsModal: ($event) => _ctx.openDetailsModal(plugin),
                          onRequestTrial: ($event) => _ctx.requestTrial(plugin),
                          onStartFreeTrial: ($event) => _ctx.startFreeTrial(plugin)
                        }, null, 8, ["is-super-user", "is-plugins-admin-enabled", "is-multi-server-environment", "is-valid-consumer", "is-auto-update-possible", "activate-nonce", "deactivate-nonce", "install-nonce", "update-nonce", "plugin", "onOpenDetailsModal", "onRequestTrial", "onStartFreeTrial"])
                      ]),
                      "piwik" == plugin.owner || "matomo-org" == plugin.owner ? (vue.openBlock(), vue.createElementBlock("img", _hoisted_19$1)) : vue.createCommentVNode("", true)
                    ])
                  ])
                ])
              ])
            ], 10, _hoisted_2$8)
          ]);
        }), 128))
      ])) : vue.createCommentVNode("", true)
    ], 64);
  }
  const PluginList = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const lcfirst = (s) => `${s[0].toLowerCase()}${s.substring(1)}`;
  const _sfc_main$7 = vue.defineComponent({
    props: {
      pluginTypeOptions: {
        type: Object,
        required: true
      },
      defaultSort: {
        type: String,
        required: true
      },
      pluginSortOptions: {
        type: Object,
        required: true
      },
      currentUserEmail: String,
      isValidConsumer: Boolean,
      isSuperUser: Boolean,
      isAutoUpdatePossible: Boolean,
      isPluginsAdminEnabled: Boolean,
      isMultiServerEnvironment: Boolean,
      hasSomeAdminAccess: Boolean,
      installNonce: {
        type: String,
        required: true
      },
      activateNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      numUsers: {
        type: Number,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field,
      MatomoLoader: CoreHome.MatomoLoader,
      PluginList
    },
    data() {
      return {
        loading: false,
        fetchRequest: null,
        fetchRequestAbortController: null,
        pluginSort: this.defaultSort,
        pluginTypeFilter: "plugins",
        searchQuery: "",
        pluginsToShow: []
      };
    },
    emits: ["triggerUpdate", "startTrialStart", "startTrialStop"],
    mounted() {
      CoreHome.Matomo.postEvent("Marketplace.Marketplace.mounted", { element: this.$refs.root });
      vue.watch(() => CoreHome.MatomoUrl.hashParsed.value, () => {
        this.updateValuesFromHash(false);
      });
      this.updateValuesFromHash(true);
    },
    unmounted() {
      CoreHome.Matomo.postEvent("Marketplace.Marketplace.unmounted", { element: this.$refs.root });
    },
    methods: {
      updateValuesFromHash(forceFetch) {
        let doFetch = forceFetch;
        const newSearchQuery = CoreHome.MatomoUrl.hashParsed.value.query || "";
        const newPluginSort = CoreHome.MatomoUrl.hashParsed.value.sort || "";
        const newPluginTypeFilter = CoreHome.MatomoUrl.hashParsed.value.pluginType || "";
        if (newSearchQuery || this.searchQuery) {
          doFetch = doFetch || newSearchQuery !== this.searchQuery;
          this.searchQuery = newSearchQuery;
        }
        if (newPluginSort) {
          doFetch = doFetch || newPluginSort !== this.pluginSort;
          this.pluginSort = newPluginSort;
        }
        if (newPluginTypeFilter) {
          doFetch = doFetch || newPluginTypeFilter !== this.pluginTypeFilter;
          this.pluginTypeFilter = newPluginTypeFilter;
        }
        if (!doFetch) {
          return;
        }
        this.fetchPlugins();
      },
      updateQuery(event) {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          query: event
        }));
      },
      updateType(event) {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          pluginType: event
        }));
      },
      updateSort(event) {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          sort: event
        }));
      },
      updateMarketplace() {
        this.fetchPlugins(() => this.$emit("triggerUpdate"));
      },
      fetchPlugins(cb) {
        this.loading = true;
        this.pluginsToShow = [];
        if (this.fetchRequestAbortController) {
          this.fetchRequestAbortController.abort();
          this.fetchRequestAbortController = null;
        }
        this.fetchRequestAbortController = new AbortController();
        this.fetchRequest = CoreHome.AjaxHelper.post(
          {
            module: "Marketplace",
            action: "searchPlugins",
            format: "JSON"
          },
          {
            query: this.searchQuery,
            sort: this.pluginSort,
            themesOnly: this.showThemes,
            purchaseType: this.pluginTypeFilter === "premium" ? "paid" : ""
          },
          {
            withTokenInUrl: true,
            abortController: this.fetchRequestAbortController
          }
        ).then((response) => {
          this.pluginsToShow = response;
          if (typeof cb === "function") {
            cb();
          }
        }).finally(() => {
          this.loading = false;
          this.fetchRequestAbortController = null;
        });
      }
    },
    computed: {
      queryInputTitle() {
        const plugins = lcfirst(CoreHome.translate("General_Plugins"));
        return `${CoreHome.translate("General_Search")} ${plugins}...`;
      },
      loadingMessage() {
        return CoreHome.translate(
          "Mobile_LoadingReport",
          CoreHome.translate(this.showThemes ? "CorePluginsAdmin_Themes" : "General_Plugins")
        );
      },
      showThemes() {
        return this.pluginTypeFilter === "themes";
      }
    }
  });
  const _hoisted_1$7 = {
    class: "row marketplaceActions",
    ref: "root"
  };
  const _hoisted_2$7 = { class: "col s12 m6 l4" };
  const _hoisted_3$7 = { class: "col s12 m6 l4" };
  const _hoisted_4$6 = {
    key: 0,
    class: "col s12 m12 l4"
  };
  const _hoisted_5$5 = { class: "plugin-search" };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    const _component_Field = vue.resolveComponent("Field");
    const _component_PluginList = vue.resolveComponent("PluginList");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1$7, [
        vue.createElementVNode("div", _hoisted_2$7, [
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: "plugin_type",
            "model-value": _ctx.pluginTypeFilter,
            "onUpdate:modelValue": _ctx.updateType,
            title: _ctx.translate("Marketplace_Show"),
            "full-width": true,
            options: _ctx.pluginTypeOptions
          }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])
        ]),
        vue.createElementVNode("div", _hoisted_3$7, [
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: "plugin_sort",
            "model-value": _ctx.pluginSort,
            "onUpdate:modelValue": _ctx.updateSort,
            title: _ctx.translate("Marketplace_Sort"),
            "full-width": true,
            options: _ctx.pluginSortOptions
          }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])
        ]),
        ((_a = _ctx.pluginsToShow) == null ? void 0 : _a.length) > 20 || _ctx.searchQuery ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$6, [
          vue.createElementVNode("div", _hoisted_5$5, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "query",
                title: _ctx.queryInputTitle,
                "full-width": true,
                "model-value": _ctx.searchQuery,
                "onUpdate:modelValue": _ctx.updateQuery
              }, null, 8, ["title", "model-value", "onUpdate:modelValue"])
            ]),
            _cache[3] || (_cache[3] = vue.createElementVNode("span", { class: "icon-search" }, null, -1))
          ])
        ])) : vue.createCommentVNode("", true)
      ], 512),
      !_ctx.loading && _ctx.pluginsToShow.length > 0 ? (vue.openBlock(), vue.createBlock(_component_PluginList, {
        key: 0,
        "plugins-to-show": _ctx.pluginsToShow,
        "current-user-email": _ctx.currentUserEmail,
        "is-auto-update-possible": _ctx.isAutoUpdatePossible,
        "is-super-user": _ctx.isSuperUser,
        "is-multi-server-environment": _ctx.isMultiServerEnvironment,
        "has-some-admin-access": _ctx.hasSomeAdminAccess,
        "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
        "is-valid-consumer": _ctx.isValidConsumer,
        "deactivate-nonce": _ctx.deactivateNonce,
        "activate-nonce": _ctx.activateNonce,
        "install-nonce": _ctx.installNonce,
        "update-nonce": _ctx.updateNonce,
        "num-users": _ctx.numUsers,
        onTriggerUpdate: _cache[0] || (_cache[0] = ($event) => _ctx.updateMarketplace()),
        onStartTrialStart: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("startTrialStart")),
        onStartTrialStop: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("startTrialStop"))
      }, null, 8, ["plugins-to-show", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "has-some-admin-access", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "num-users"])) : vue.createCommentVNode("", true),
      !_ctx.loading && _ctx.pluginsToShow.length == 0 ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, { key: 1 }, {
        default: vue.withCtx(() => [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate(_ctx.showThemes ? "Marketplace_NoThemesFound" : "Marketplace_NoPluginsFound")), 1)
        ]),
        _: 1
      })) : vue.createCommentVNode("", true),
      _ctx.loading ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, { key: 2 }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_MatomoLoader),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.loadingMessage), 1)
        ]),
        _: 1
      })) : vue.createCommentVNode("", true)
    ], 64);
  }
  const Marketplace = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = vue.defineComponent({
    props: {
      hasValidLicenseKey: Boolean
    },
    components: {
      Field: CorePluginsAdmin.Field,
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton,
      ActivityIndicator: CoreHome.ActivityIndicator,
      InstallAllPaidPluginsButton: CorePluginsAdmin.InstallAllPaidPluginsButton
    },
    data() {
      return {
        licenseKey: "",
        hasValidLicense: this.hasValidLicenseKey,
        isUpdating: false
      };
    },
    methods: {
      updateLicenseKey(action, licenseKey, onSuccessMessage) {
        CoreHome.NotificationsStore.remove("ManageLicenseKeySuccess");
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: `Marketplace.${action}`,
            format: "JSON"
          },
          {
            licenseKey: this.licenseKey
          },
          { withTokenInUrl: true }
        ).then((response) => {
          this.isUpdating = false;
          if (response && response.value) {
            CoreHome.NotificationsStore.show({
              id: "ManageLicenseKeySuccess",
              message: onSuccessMessage,
              context: "success",
              type: "toast"
            });
            this.hasValidLicense = action !== "deleteLicenseKey";
            this.licenseKey = "";
          }
        }, () => {
          this.isUpdating = false;
        });
      },
      removeLicense() {
        CoreHome.Matomo.helper.modalConfirm(this.$refs.confirmRemoveLicense, {
          yes: () => {
            this.isUpdating = true;
            this.updateLicenseKey(
              "deleteLicenseKey",
              "",
              CoreHome.translate("Marketplace_LicenseKeyDeletedSuccess")
            );
          }
        });
      },
      updateLicense() {
        this.isUpdating = true;
        this.updateLicenseKey(
          "saveLicenseKey",
          this.licenseKey,
          CoreHome.translate("Marketplace_LicenseKeyActivatedSuccess")
        );
      }
    },
    computed: {
      manageLicenseKeyIntro() {
        const marketplaceLink = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "overview"
        }))}`;
        return CoreHome.translate(
          "Marketplace_ManageLicenseKeyIntro",
          `<a href="${marketplaceLink}">`,
          "</a>",
          CoreHome.externalLink("https://shop.matomo.org/my-account"),
          "</a>"
        );
      },
      licenseKeyPlaceholder() {
        return this.hasValidLicense ? CoreHome.translate("Marketplace_LicenseKeyIsValidShort") : CoreHome.translate("Marketplace_LicenseKey");
      },
      saveButtonText() {
        return this.hasValidLicense ? CoreHome.translate("CoreUpdater_UpdateTitle") : CoreHome.translate("Marketplace_ActivateLicenseKey");
      }
    }
  });
  const _hoisted_1$6 = ["innerHTML"];
  const _hoisted_2$6 = { class: "manage-license-key-input" };
  const _hoisted_3$6 = {
    class: "ui-confirm",
    id: "confirmRemoveLicense",
    ref: "confirmRemoveLicense"
  };
  const _hoisted_4$5 = ["value"];
  const _hoisted_5$4 = ["value"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_InstallAllPaidPluginsButton = vue.resolveComponent("InstallAllPaidPluginsButton");
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("Marketplace_LicenseKey"),
        class: "manage-license-key"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", {
            class: "manage-license-key-intro",
            innerHTML: _ctx.$sanitize(_ctx.manageLicenseKeyIntro)
          }, null, 8, _hoisted_1$6),
          vue.createVNode(_component_InstallAllPaidPluginsButton, { disabled: _ctx.isUpdating }, null, 8, ["disabled"]),
          vue.createElementVNode("div", _hoisted_2$6, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "license_key",
              modelValue: _ctx.licenseKey,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.licenseKey = $event),
              placeholder: _ctx.licenseKeyPlaceholder,
              "full-width": true
            }, null, 8, ["modelValue", "placeholder"])
          ]),
          vue.createVNode(_component_SaveButton, {
            onConfirm: _cache[1] || (_cache[1] = ($event) => _ctx.updateLicense()),
            value: _ctx.saveButtonText,
            disabled: !_ctx.licenseKey || _ctx.isUpdating,
            id: "submit_license_key"
          }, null, 8, ["value", "disabled"]),
          _ctx.hasValidLicense ? (vue.openBlock(), vue.createBlock(_component_SaveButton, {
            key: 0,
            id: "remove_license_key",
            onConfirm: _cache[2] || (_cache[2] = ($event) => _ctx.removeLicense()),
            disabled: _ctx.isUpdating,
            value: _ctx.translate("General_Remove")
          }, null, 8, ["disabled", "value"])) : vue.createCommentVNode("", true),
          vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isUpdating }, null, 8, ["loading"])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createElementVNode("div", _hoisted_3$6, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Marketplace_ConfirmRemoveLicense")), 1),
        vue.createElementVNode("input", {
          role: "yes",
          type: "button",
          value: _ctx.translate("General_Yes")
        }, null, 8, _hoisted_4$5),
        vue.createElementVNode("input", {
          role: "no",
          type: "button",
          value: _ctx.translate("General_No")
        }, null, 8, _hoisted_5$4)
      ], 512)
    ], 64);
  }
  const ManageLicenseKey = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = vue.defineComponent({
    props: {
      plugins: {
        type: Array,
        required: true
      }
    },
    directives: {
      PluginName: CorePluginsAdmin.PluginName
    },
    computed: {
      overviewLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "overview"
        }))}`;
      }
    }
  });
  const _hoisted_1$5 = { class: "getNewPlugins" };
  const _hoisted_2$5 = { class: "row" };
  const _hoisted_3$5 = { class: "pluginName" };
  const _hoisted_4$4 = { key: 0 };
  const _hoisted_5$3 = { class: "widgetBody" };
  const _hoisted_6$3 = ["href"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_plugin_name = vue.resolveDirective("plugin-name");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createElementVNode("div", _hoisted_2$5, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.plugins, (plugin, index) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "col s12",
            key: plugin.name
          }, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("h3", _hoisted_3$5, [
              vue.createTextVNode(vue.toDisplayString(plugin.displayName), 1)
            ])), [
              [_directive_plugin_name, { pluginName: plugin.name }]
            ]),
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(plugin.description) + " ", 1),
              _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", null, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_MoreDetails")), 1)
              ])), [
                [_directive_plugin_name, { pluginName: plugin.name }]
              ])
            ]),
            index < _ctx.plugins.length - 1 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$4, [..._cache[1] || (_cache[1] = [
              vue.createElementVNode("br", null, null, -1),
              vue.createElementVNode("br", null, null, -1)
            ])])) : vue.createCommentVNode("", true)
          ]);
        }), 128))
      ]),
      vue.createElementVNode("div", _hoisted_5$3, [
        vue.createElementVNode("a", { href: _ctx.overviewLink }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ViewAllMarketplacePlugins")), 9, _hoisted_6$3)
      ])
    ]);
  }
  const GetNewPlugins = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    props: {
      plugins: {
        type: Array,
        required: true
      }
    },
    directives: {
      PluginName: CorePluginsAdmin.PluginName
    },
    computed: {
      marketplaceOverviewLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "overview"
        }))}`;
      }
    }
  });
  const _hoisted_1$4 = {
    class: "getNewPlugins isAdminPage",
    ref: "root"
  };
  const _hoisted_2$4 = { class: "row" };
  const _hoisted_3$4 = ["title"];
  const _hoisted_4$3 = ["title"];
  const _hoisted_5$2 = { key: 0 };
  const _hoisted_6$2 = ["src"];
  const _hoisted_7$2 = { class: "widgetBody" };
  const _hoisted_8$2 = ["href"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_plugin_name = vue.resolveDirective("plugin-name");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
      vue.createElementVNode("div", _hoisted_2$4, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.plugins, (plugin) => {
          var _a;
          return vue.openBlock(), vue.createElementBlock("div", {
            class: "col s12 m4",
            key: plugin.name
          }, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("h3", {
              class: "pluginName",
              title: plugin.description
            }, [
              vue.createTextVNode(vue.toDisplayString(plugin.displayName), 1)
            ], 8, _hoisted_3$4)), [
              [_directive_plugin_name, { pluginName: plugin.name }]
            ]),
            vue.createElementVNode("p", {
              class: "description",
              title: plugin.description
            }, vue.toDisplayString(plugin.description), 9, _hoisted_4$3),
            ((_a = plugin.screenshots) == null ? void 0 : _a.length) ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$2, [
              _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
              vue.withDirectives(vue.createElementVNode("img", {
                class: "screenshot",
                src: `${plugin.screenshots[0]}?w=600`,
                style: { "width": "100%" },
                alt: ""
              }, null, 8, _hoisted_6$2), [
                [_directive_plugin_name, { pluginName: plugin.name }]
              ])
            ])) : vue.createCommentVNode("", true)
          ]);
        }), 128))
      ]),
      vue.createElementVNode("div", _hoisted_7$2, [
        vue.createElementVNode("a", { href: _ctx.marketplaceOverviewLink }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ViewAllMarketplacePlugins")), 9, _hoisted_8$2)
      ])
    ], 512);
  }
  const GetNewPluginsAdmin = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      plugins: {
        type: Array,
        required: true
      }
    },
    directives: {
      PluginName: CorePluginsAdmin.PluginName
    },
    computed: {
      trialHintsText() {
        const link = CoreHome.externalRawLink("https://shop.matomo.org/free-trial/");
        const linkStyle = "color:#5bb75b;text-decoration: underline;";
        return CoreHome.translate(
          "Marketplace_TrialHints",
          `<a style="${linkStyle}" href="${link}" target="_blank" rel="noreferrer noopener">`,
          "</a>"
        );
      },
      pluginRows() {
        const result = [];
        this.plugins.forEach((plugin, index) => {
          const row = Math.floor(index / 3);
          result[row] = result[row] || [];
          result[row].push(plugin);
        });
        return result;
      },
      overviewLink() {
        const query = CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "overview"
        }));
        const hash = CoreHome.MatomoUrl.stringify({ pluginType: "premium" });
        return `?${query}#?${hash}`;
      }
    }
  });
  const _hoisted_1$3 = { class: "getNewPlugins getPremiumFeatures widgetBody" };
  const _hoisted_2$3 = {
    key: 0,
    class: "col s12 m12"
  };
  const _hoisted_3$3 = ["innerHTML"];
  const _hoisted_4$2 = { style: { "margin-bottom": "28px", "color": "#5bb75b" } };
  const _hoisted_5$1 = { class: "pluginName" };
  const _hoisted_6$1 = {
    key: 0,
    class: "pluginSubtitle"
  };
  const _hoisted_7$1 = { class: "pluginBody" };
  const _hoisted_8$1 = { class: "pluginMoreDetails" };
  const _hoisted_9$1 = { class: "widgetBody" };
  const _hoisted_10$1 = ["href"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_plugin_name = vue.resolveDirective("plugin-name");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pluginRows, (rowOfPlugins, index) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          class: "row",
          key: index
        }, [
          index === 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, [
            vue.createElementVNode("h3", {
              style: { "font-weight": "bold", "color": "#5bb75b" },
              innerHTML: _ctx.$sanitize(_ctx.trialHintsText)
            }, null, 8, _hoisted_3$3),
            vue.createElementVNode("h3", _hoisted_4$2, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_SupportMatomoThankYou")) + " ", 1),
              _cache[0] || (_cache[0] = vue.createElementVNode("i", { class: "icon-heart red-text" }, null, -1))
            ])
          ])) : vue.createCommentVNode("", true),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(rowOfPlugins, (plugin) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              class: "col s12 m4",
              key: plugin.name
            }, [
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("h3", _hoisted_5$1, [
                vue.createTextVNode(vue.toDisplayString(plugin.displayName), 1)
              ])), [
                [_directive_plugin_name, { pluginName: plugin.name }]
              ]),
              plugin.specialOffer ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_6$1, [
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Marketplace_SpecialOffer")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(plugin.specialOffer), 1)
              ])) : vue.createCommentVNode("", true),
              vue.createElementVNode("span", _hoisted_7$1, [
                vue.createTextVNode(vue.toDisplayString(plugin.isBundle ? `${_ctx.translate("Marketplace_SpecialOffer")}: ` : "") + vue.toDisplayString(plugin.description) + " ", 1),
                _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
                vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_8$1, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_MoreDetails")), 1)
                ])), [
                  [_directive_plugin_name, { pluginName: plugin.name }]
                ])
              ])
            ]);
          }), 128))
        ]);
      }), 128)),
      vue.createElementVNode("div", _hoisted_9$1, [
        vue.createElementVNode("a", { href: _ctx.overviewLink }, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_ViewAllMarketplacePlugins")), 9, _hoisted_10$1)
      ])
    ]);
  }
  const GetPremiumFeatures = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      currentUserEmail: String,
      inReportingMenu: Boolean,
      isValidConsumer: Boolean,
      isSuperUser: Boolean,
      isAutoUpdatePossible: Boolean,
      isPluginsAdminEnabled: Boolean,
      isMultiServerEnvironment: Boolean,
      hasSomeAdminAccess: Boolean,
      installNonce: {
        type: String,
        required: true
      },
      activateNonce: {
        type: String,
        required: true
      },
      deactivateNonce: {
        type: String,
        required: true
      },
      updateNonce: {
        type: String,
        required: true
      },
      isPluginUploadEnabled: Boolean,
      uploadLimit: [String, Number],
      pluginTypeOptions: {
        type: Object,
        required: true
      },
      defaultSort: {
        type: String,
        required: true
      },
      pluginSortOptions: {
        type: Object,
        required: true
      },
      numUsers: {
        type: Number,
        required: true
      }
    },
    components: {
      InstallAllPaidPluginsButton: CorePluginsAdmin.InstallAllPaidPluginsButton,
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      Marketplace
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    data() {
      return {
        updating: false,
        fetchRequest: null,
        fetchRequestAbortController: null,
        updateData: null,
        installDisabled: false,
        installLoading: false
      };
    },
    computed: {
      getIsValidConsumer() {
        return this.updateData && typeof this.updateData.isValidConsumer !== "undefined" ? this.updateData.isValidConsumer : this.isValidConsumer;
      },
      installAllPaidPluginsVisible() {
        return this.getIsValidConsumer && this.isSuperUser && this.isAutoUpdatePossible && this.isPluginsAdminEnabled || this.installDisabled && this.installLoading;
      },
      showThemes() {
        return CoreHome.MatomoUrl.hashParsed.value.pluginType === "themes";
      }
    },
    methods: {
      disableInstallAllPlugins(isLoading) {
        this.installDisabled = true;
        this.installLoading = isLoading;
      },
      enableInstallAllPlugins() {
        this.installDisabled = false;
        this.installLoading = false;
      },
      updateOverviewData() {
        this.updating = true;
        if (this.isSuperUser) {
          this.disableInstallAllPlugins(true);
        }
        if (this.fetchRequestAbortController) {
          this.fetchRequestAbortController.abort();
          this.fetchRequestAbortController = null;
        }
        this.fetchRequestAbortController = new AbortController();
        this.fetchRequest = CoreHome.AjaxHelper.post(
          {
            module: "Marketplace",
            action: "updateOverview",
            format: "JSON"
          },
          {},
          {
            withTokenInUrl: true,
            abortController: this.fetchRequestAbortController
          }
        ).then((response) => {
          this.updateData = response;
        }).finally(() => {
          this.updating = false;
          this.fetchRequestAbortController = null;
          this.enableInstallAllPlugins();
        });
      }
    }
  });
  const _hoisted_1$2 = { class: "marketplaceIntro" };
  const _hoisted_2$2 = { key: 0 };
  const _hoisted_3$2 = { key: 1 };
  const _hoisted_4$1 = {
    key: 0,
    class: "installAllPaidPlugins"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_InstallAllPaidPluginsButton = vue.resolveComponent("InstallAllPaidPluginsButton");
    const _component_Marketplace = vue.resolveComponent("Marketplace");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("h2", null, [
        vue.createVNode(_component_EnrichedHeadline, {
          "feature-name": _ctx.translate("CorePluginsAdmin_Marketplace")
        }, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_Marketplace")), 1)
          ]),
          _: 1
        }, 8, ["feature-name"])
      ]),
      vue.createElementVNode("div", _hoisted_1$2, [
        !_ctx.isSuperUser ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2$2, vue.toDisplayString(_ctx.translate("Marketplace_Intro")), 1)) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_3$2, vue.toDisplayString(_ctx.translate("Marketplace_IntroSuperUser")), 1))
      ]),
      _ctx.installAllPaidPluginsVisible ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$1, [
        vue.createVNode(_component_InstallAllPaidPluginsButton, { disabled: _ctx.installDisabled }, null, 8, ["disabled"])
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_Marketplace, {
        "plugin-type-options": _ctx.pluginTypeOptions,
        "default-sort": _ctx.defaultSort,
        "plugin-sort-options": _ctx.pluginSortOptions,
        "current-user-email": _ctx.currentUserEmail,
        "is-auto-update-possible": _ctx.isAutoUpdatePossible,
        "is-super-user": _ctx.isSuperUser,
        "is-multi-server-environment": _ctx.isMultiServerEnvironment,
        "is-plugins-admin-enabled": _ctx.isPluginsAdminEnabled,
        "is-valid-consumer": _ctx.getIsValidConsumer,
        "deactivate-nonce": _ctx.deactivateNonce,
        "activate-nonce": _ctx.activateNonce,
        "install-nonce": _ctx.installNonce,
        "update-nonce": _ctx.updateNonce,
        "has-some-admin-access": _ctx.hasSomeAdminAccess,
        "num-users": _ctx.numUsers,
        onTriggerUpdate: _cache[0] || (_cache[0] = ($event) => _ctx.updateOverviewData()),
        onStartTrialStart: _cache[1] || (_cache[1] = ($event) => _ctx.disableInstallAllPlugins(true)),
        onStartTrialStop: _cache[2] || (_cache[2] = ($event) => _ctx.disableInstallAllPlugins(false))
      }, null, 8, ["plugin-type-options", "default-sort", "plugin-sort-options", "current-user-email", "is-auto-update-possible", "is-super-user", "is-multi-server-environment", "is-plugins-admin-enabled", "is-valid-consumer", "deactivate-nonce", "activate-nonce", "install-nonce", "update-nonce", "has-some-admin-access", "num-users"])
    ])), [
      [_directive_content_intro]
    ]);
  }
  const OverviewIntro = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      loginUrl: {
        type: String,
        required: true
      },
      numUsers: {
        type: Number,
        required: true
      },
      hasLicenseKey: Boolean,
      subscriptions: {
        type: Array,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    methods: {
      getSubscriptionStatusTitle(sub) {
        if (!sub.isValid) {
          return CoreHome.translate("Marketplace_SubscriptionInvalid");
        }
        if (sub.isExpiredSoon) {
          return CoreHome.translate("Marketplace_SubscriptionExpiresSoon");
        }
        return void 0;
      }
    },
    computed: {
      marketplaceOverviewLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "overview"
        }))}`;
      },
      licenseKeyLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          idSite: CoreHome.MatomoUrl.parsed.value.idSite,
          module: "Marketplace",
          action: "manageLicenseKey"
        }))}`;
      },
      missingLicenseText() {
        return CoreHome.translate(
          "Marketplace_OverviewPluginSubscriptionsMissingLicenseMessage",
          `<a href="${this.licenseKeyLink}">`,
          "</a>",
          `<a href="${this.marketplaceOverviewLink}">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = ["href"];
  const _hoisted_3$1 = ["innerHTML"];
  const _hoisted_4 = { class: "subscriptionName" };
  const _hoisted_5 = ["href"];
  const _hoisted_6 = { key: 1 };
  const _hoisted_7 = { class: "subscriptionType" };
  const _hoisted_8 = ["title"];
  const _hoisted_9 = {
    key: 0,
    class: "icon-error"
  };
  const _hoisted_10 = {
    key: 1,
    class: "icon-warning"
  };
  const _hoisted_11 = {
    key: 2,
    class: "icon-error"
  };
  const _hoisted_12 = {
    key: 3,
    class: "icon-ok"
  };
  const _hoisted_13 = ["title"];
  const _hoisted_14 = { key: 0 };
  const _hoisted_15 = { colspan: "6" };
  const _hoisted_16 = { class: "tableActionBar" };
  const _hoisted_17 = ["href"];
  const _hoisted_18 = { key: 1 };
  const _hoisted_19 = ["innerHTML"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("Marketplace_OverviewPluginSubscriptions"),
      class: "subscriptionOverview"
    }, {
      default: vue.withCtx(() => [
        _ctx.hasLicenseKey ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("Marketplace_PluginSubscriptionsList")) + " ", 1),
            _ctx.loginUrl ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 0,
              target: "_blank",
              rel: "noreferrer noopener",
              href: _ctx.loginUrl
            }, vue.toDisplayString(_ctx.translate("Marketplace_OverviewPluginSubscriptionsAllDetails")), 9, _hoisted_2$1)) : vue.createCommentVNode("", true),
            _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_OverviewPluginSubscriptionsMissingInfo")) + " ", 1),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_NoValidSubscriptionNoUpdates")) + " ", 1),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.translate(
                "Marketplace_CurrentNumPiwikUsers",
                `<strong>${_ctx.numUsers}</strong>`
              ))
            }, null, 8, _hoisted_3$1)
          ]),
          _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
            vue.createElementVNode("thead", null, [
              vue.createElementVNode("tr", null, [
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Name")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("Marketplace_SubscriptionType")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Status")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("Marketplace_SubscriptionStartDate")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("Marketplace_SubscriptionEndDate")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("Marketplace_SubscriptionNextPaymentDate")), 1)
              ])
            ]),
            vue.createElementVNode("tbody", null, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.subscriptions || [], (subscription, index) => {
                var _a, _b, _c, _d;
                return vue.openBlock(), vue.createElementBlock("tr", { key: index }, [
                  vue.createElementVNode("td", _hoisted_4, [
                    ((_a = subscription.plugin) == null ? void 0 : _a.htmlUrl) ? (vue.openBlock(), vue.createElementBlock("a", {
                      key: 0,
                      href: (_b = subscription.plugin) == null ? void 0 : _b.htmlUrl,
                      rel: "noreferrer noopener",
                      target: "_blank"
                    }, vue.toDisplayString((_c = subscription.plugin) == null ? void 0 : _c.displayName), 9, _hoisted_5)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_6, vue.toDisplayString((_d = subscription.plugin) == null ? void 0 : _d.displayName), 1))
                  ]),
                  vue.createElementVNode("td", _hoisted_7, vue.toDisplayString(subscription.productType), 1),
                  vue.createElementVNode("td", {
                    class: "subscriptionStatus",
                    title: _ctx.getSubscriptionStatusTitle(subscription)
                  }, [
                    !subscription.isValid ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_9)) : subscription.isExpiredSoon ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_10)) : subscription.status !== "" && subscription.status !== "Active" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_11)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_12)),
                    vue.createTextVNode(" " + vue.toDisplayString(subscription.status) + " ", 1),
                    subscription.isExceeded ? (vue.openBlock(), vue.createElementBlock("span", {
                      key: 4,
                      class: "errorMessage",
                      title: _ctx.translate("Marketplace_LicenseExceededPossibleCause")
                    }, [
                      _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-error" }, null, -1)),
                      vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_Exceeded")), 1)
                    ], 8, _hoisted_13)) : vue.createCommentVNode("", true)
                  ], 8, _hoisted_8),
                  vue.createElementVNode("td", null, vue.toDisplayString(subscription.start), 1),
                  vue.createElementVNode("td", null, vue.toDisplayString(subscription.isValid && subscription.nextPayment ? _ctx.translate("Marketplace_LicenseRenewsNextPaymentDate") : subscription.end), 1),
                  vue.createElementVNode("td", null, vue.toDisplayString(subscription.nextPayment), 1)
                ]);
              }), 128)),
              !_ctx.subscriptions.length ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_14, [
                vue.createElementVNode("td", _hoisted_15, vue.toDisplayString(_ctx.translate("Marketplace_NoSubscriptionsFound")), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ])), [
            [_directive_content_table]
          ]),
          vue.createElementVNode("div", _hoisted_16, [
            vue.createElementVNode("a", {
              href: _ctx.marketplaceOverviewLink,
              class: ""
            }, [
              _cache[3] || (_cache[3] = vue.createElementVNode("span", { class: "icon-table" }, null, -1)),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_BrowseMarketplace")), 1)
            ], 8, _hoisted_17)
          ])
        ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_18, [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.missingLicenseText)
          }, null, 8, _hoisted_19)
        ]))
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const SubscriptionOverview = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({});
  const _hoisted_1 = { class: "richMarketplaceMenuButton" };
  const _hoisted_2 = { class: "intro" };
  const _hoisted_3 = { class: "cta" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      _cache[3] || (_cache[3] = vue.createElementVNode("hr", null, null, -1)),
      vue.createElementVNode("p", _hoisted_2, vue.toDisplayString(_ctx.translate("Marketplace_RichMenuIntro")), 1),
      vue.createElementVNode("p", _hoisted_3, [
        vue.createElementVNode("a", {
          class: "btn btn-outline",
          tabindex: "5",
          href: "",
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.$emit("action"), ["prevent"])),
          onKeyup: _cache[1] || (_cache[1] = vue.withKeys(($event) => _ctx.$emit("action"), ["enter"]))
        }, [
          _cache[2] || (_cache[2] = vue.createElementVNode("span", { class: "icon-marketplace" }, " ", -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Marketplace_Marketplace")), 1)
        ], 32)
      ])
    ]);
  }
  const RichMenuButton = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.GetNewPlugins = GetNewPlugins;
  exports2.GetNewPluginsAdmin = GetNewPluginsAdmin;
  exports2.GetPremiumFeatures = GetPremiumFeatures;
  exports2.ManageLicenseKey = ManageLicenseKey;
  exports2.Marketplace = Marketplace;
  exports2.MissingReqsNotice = MissingReqsNotice;
  exports2.OverviewIntro = OverviewIntro;
  exports2.PluginList = PluginList;
  exports2.RichMenuButton = RichMenuButton;
  exports2.SubscriptionOverview = SubscriptionOverview;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
