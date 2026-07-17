(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.UserCountry = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";
  const _sfc_main$2 = vue.defineComponent({
    props: {
      currentProviderId: {
        type: String,
        required: true
      },
      isThereWorkingProvider: Boolean,
      setUpGuides: String,
      thisIp: {
        type: String,
        required: true
      },
      locationProviders: {
        type: Object,
        required: true
      },
      defaultProviderId: {
        type: String,
        required: true
      },
      disabledProviderId: {
        type: String,
        required: true
      }
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      Notification: CoreHome.Notification,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    data() {
      return {
        isLoading: false,
        updateLoading: {},
        selectedProvider: this.currentProviderId,
        providerLocations: Object.fromEntries(
          Object.entries(this.locationProviders).map(([k, p]) => [k, p.location])
        )
      };
    },
    methods: {
      refreshProviderInfo(providerId) {
        this.updateLoading[providerId] = true;
        delete this.providerLocations[providerId];
        CoreHome.AjaxHelper.fetch(
          {
            module: "UserCountry",
            action: "getLocationUsingProvider",
            id: providerId,
            format: "html"
          },
          {
            format: "html"
          }
        ).then((response) => {
          this.providerLocations[providerId] = response;
        }).finally(() => {
          this.updateLoading[providerId] = false;
        });
      },
      save() {
        if (!this.selectedProvider) {
          return;
        }
        this.isLoading = true;
        CoreHome.AjaxHelper.fetch(
          {
            method: "UserCountry.setLocationProvider",
            providerId: this.selectedProvider
          },
          {
            withTokenInUrl: true
          }
        ).then(() => {
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("General_Done"),
            context: "success",
            noclear: true,
            type: "toast",
            id: "userCountryLocationProvider"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).finally(() => {
          this.isLoading = false;
        });
      }
    },
    computed: {
      visibleLocationProviders() {
        return Object.fromEntries(
          Object.entries(this.locationProviders).filter(([, p]) => p.isVisible)
        );
      },
      locationProvidersNotDefaultOrDisabled() {
        return Object.fromEntries(
          Object.entries(this.locationProviders).filter(
            ([, p]) => p.id !== this.defaultProviderId && p.id !== this.disabledProviderId
          )
        );
      },
      noProvidersText() {
        return CoreHome.translate(
          "UserCountry_NoProviders",
          '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">',
          "</a>"
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
  const _hoisted_1$1 = { class: "locationProviderSelection" };
  const _hoisted_2$1 = ["innerHTML"];
  const _hoisted_3 = { class: "row" };
  const _hoisted_4 = { class: "col s12 push-m9 m3" };
  const _hoisted_5 = { class: "col s12 m4 l2" };
  const _hoisted_6 = ["id", "disabled", "checked", "onChange"];
  const _hoisted_7 = { class: "loc-provider-status" };
  const _hoisted_8 = {
    key: 0,
    class: "is-not-installed"
  };
  const _hoisted_9 = {
    key: 1,
    class: "is-installed"
  };
  const _hoisted_10 = {
    key: 2,
    class: "is-broken"
  };
  const _hoisted_11 = { class: "col s12 m4 l6" };
  const _hoisted_12 = ["innerHTML"];
  const _hoisted_13 = ["innerHTML"];
  const _hoisted_14 = { class: "col s12 m4 l4" };
  const _hoisted_15 = {
    key: 0,
    class: "form-help"
  };
  const _hoisted_16 = { key: 0 };
  const _hoisted_17 = { style: { "position": "absolute" } };
  const _hoisted_18 = ["innerHTML"];
  const _hoisted_19 = { class: "text-right" };
  const _hoisted_20 = ["onClick"];
  const _hoisted_21 = { key: 1 };
  const _hoisted_22 = {
    key: 1,
    class: "form-help"
  };
  const _hoisted_23 = { key: 0 };
  const _hoisted_24 = ["innerHTML"];
  const _hoisted_25 = ["innerHTML"];
  const _hoisted_26 = { key: 1 };
  const _hoisted_27 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      !_ctx.isThereWorkingProvider ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        innerHTML: _ctx.$sanitize(_ctx.setUpGuides || "")
      }, null, 8, _hoisted_2$1)) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_3, [
        vue.createElementVNode("div", _hoisted_4, vue.toDisplayString(_ctx.translate("General_InfoFor", _ctx.thisIp)), 1)
      ]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.visibleLocationProviders, (provider, id) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          key: id,
          class: vue.normalizeClass(`row form-group provider${id}`)
        }, [
          vue.createElementVNode("div", _hoisted_5, [
            vue.createElementVNode("p", null, [
              vue.createElementVNode("label", null, [
                vue.createElementVNode("input", {
                  class: "location-provider",
                  name: "location-provider",
                  type: "radio",
                  id: `provider_input_${id}`,
                  disabled: provider.status !== 1,
                  checked: _ctx.selectedProvider === id,
                  onChange: ($event) => _ctx.selectedProvider = String(id)
                }, null, 40, _hoisted_6),
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translateOrDefault(provider.title || "")), 1)
              ])
            ]),
            vue.createElementVNode("p", _hoisted_7, [
              provider.status === 0 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8, vue.toDisplayString(_ctx.translate("General_NotInstalled")), 1)) : provider.status === 1 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_9, vue.toDisplayString(_ctx.translate("General_Installed")), 1)) : provider.status === 2 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_10, vue.toDisplayString(_ctx.translate("General_Broken")), 1)) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_11, [
            vue.createElementVNode("p", {
              innerHTML: _ctx.$sanitize(_ctx.translateOrDefault(provider.description))
            }, null, 8, _hoisted_12),
            provider.status !== 1 && provider.install_docs ? (vue.openBlock(), vue.createElementBlock("p", {
              key: 0,
              innerHTML: _ctx.$sanitize(provider.install_docs)
            }, null, 8, _hoisted_13)) : vue.createCommentVNode("", true)
          ]),
          vue.createElementVNode("div", _hoisted_14, [
            provider.status === 1 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15, [
              _ctx.thisIp !== "127.0.0.1" && _ctx.thisIp !== "::1" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_16, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("UserCountry_CurrentLocationIntro")) + ": ", 1),
                vue.createElementVNode("div", null, [
                  _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("div", _hoisted_17, [
                    vue.createVNode(_component_ActivityIndicator, {
                      loading: _ctx.updateLoading[id]
                    }, null, 8, ["loading"])
                  ]),
                  vue.createElementVNode("span", {
                    class: "location",
                    style: vue.normalizeStyle({ visibility: _ctx.providerLocations[id] ? "visible" : "hidden" })
                  }, [
                    vue.createElementVNode("strong", {
                      innerHTML: _ctx.$sanitize(_ctx.providerLocations[id] || " ")
                    }, null, 8, _hoisted_18)
                  ], 4)
                ]),
                vue.createElementVNode("div", _hoisted_19, [
                  vue.createElementVNode("a", {
                    onClick: vue.withModifiers(($event) => _ctx.refreshProviderInfo(String(id)), ["prevent"])
                  }, vue.toDisplayString(_ctx.translate("General_Refresh")), 9, _hoisted_20)
                ])
              ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_21, vue.toDisplayString(_ctx.translate("UserCountry_CannotLocalizeLocalIP", _ctx.thisIp)), 1))
            ])) : vue.createCommentVNode("", true),
            provider.statusMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_22, [
              provider.status === 2 ? (vue.openBlock(), vue.createElementBlock("strong", _hoisted_23, vue.toDisplayString(_ctx.translate("General_Error")) + ":", 1)) : vue.createCommentVNode("", true),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(provider.statusMessage)
              }, null, 8, _hoisted_24)
            ])) : vue.createCommentVNode("", true),
            provider.extra_message ? (vue.openBlock(), vue.createElementBlock("div", {
              key: 2,
              class: "form-help",
              innerHTML: _ctx.$sanitize(provider.extra_message)
            }, null, 8, _hoisted_25)) : vue.createCommentVNode("", true)
          ])
        ], 2);
      }), 128)),
      !Object.keys(_ctx.locationProvidersNotDefaultOrDisabled).length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_26, [
        vue.createVNode(_component_Notification, {
          noclear: true,
          context: "warning"
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.noProvidersText)
            }, null, 8, _hoisted_27)
          ]),
          _: 1
        })
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_SaveButton, {
        onConfirm: _cache[0] || (_cache[0] = ($event) => _ctx.save()),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])
    ]);
  }
  const LocationProviderSelection = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      currentProviderId: {
        type: String,
        required: true
      },
      isThereWorkingProvider: Boolean,
      setUpGuides: String,
      thisIp: {
        type: String,
        required: true
      },
      locationProviders: {
        type: Object,
        required: true
      },
      defaultProviderId: {
        type: String,
        required: true
      },
      disabledProviderId: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      LocationProviderSelection,
      EnrichedHeadline: CoreHome.EnrichedHeadline
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro,
      ContentBlock: CoreHome.ContentBlock
    }
  });
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_LocationProviderSelection = vue.resolveComponent("LocationProviderSelection");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, [
          vue.createVNode(_component_EnrichedHeadline, {
            "help-url": _ctx.externalRawLink("https://matomo.org/docs/geo-locate/"),
            id: "location-providers"
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("UserCountry_Geolocation")), 1)
            ]),
            _: 1
          }, 8, ["help-url"])
        ]),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UserCountry_GeolocationPageDesc")), 1)
      ])), [
        [_directive_content_intro]
      ]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("UserCountry_LocationProvider")
      }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_LocationProviderSelection, {
            "current-provider-id": _ctx.currentProviderId,
            "is-there-working-provider": _ctx.isThereWorkingProvider,
            "set-up-guides": _ctx.setUpGuides,
            "this-ip": _ctx.thisIp,
            "location-providers": _ctx.locationProviders,
            "default-provider-id": _ctx.defaultProviderId,
            "disabled-provider-id": _ctx.disabledProviderId
          }, null, 8, ["current-provider-id", "is-there-working-provider", "set-up-guides", "this-ip", "location-providers", "default-provider-id", "disabled-provider-id"])
        ]),
        _: 1
      }, 8, ["content-title"])
    ], 64);
  }
  const AdminPage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      numberDistinctCountries: {
        type: Number,
        required: true
      },
      urlSparklineCountries: {
        type: [Object, String],
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Sparkline: CoreHome.Sparkline
    },
    computed: {
      distinctCountriesText() {
        return CoreHome.translate(
          "UserCountry_DistinctCountries",
          `<strong>${this.numberDistinctCountries}</strong>`
        );
      }
    }
  });
  const _hoisted_1 = { class: "sparkline" };
  const _hoisted_2 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Sparkline = vue.resolveComponent("Sparkline");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, null, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", _hoisted_1, [
          vue.createVNode(_component_Sparkline, {
            params: _ctx.urlSparklineCountries,
            width: 100,
            height: 25
          }, null, 8, ["params"]),
          vue.createElementVNode("div", {
            innerHTML: _ctx.$sanitize(_ctx.distinctCountriesText)
          }, null, 8, _hoisted_2)
        ]),
        _cache[0] || (_cache[0] = vue.createElementVNode("br", { style: { "clear": "left" } }, null, -1))
      ]),
      _: 1
    });
  }
  const GetDistinctCountries = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AdminPage = AdminPage;
  exports2.GetDistinctCountries = GetDistinctCountries;
  exports2.LocationProviderSelection = LocationProviderSelection;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
