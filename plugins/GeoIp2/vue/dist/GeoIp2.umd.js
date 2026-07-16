(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.GeoIp2 = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";var __defProp = Object.defineProperty;
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

  const { $ } = window;
  const _sfc_main = vue.defineComponent({
    props: {
      geoipDatabaseStartedInstalled: Boolean,
      showGeoipUpdateSection: {
        type: Boolean,
        required: true
      },
      dbipLiteUrl: {
        type: String,
        required: true
      },
      dbipLiteFilename: {
        type: String,
        required: true
      },
      geoipLocUrl: String,
      isProviderPluginActive: Boolean,
      geoipIspUrl: String,
      lastTimeUpdaterRun: String,
      geoipUpdatePeriod: String,
      updatePeriodOptions: {
        type: Object,
        required: true
      },
      nextRunTime: Number,
      nextRunTimePretty: String
    },
    components: {
      Progressbar: CoreHome.Progressbar,
      Field: CorePluginsAdmin.Field,
      ContentBlock: CoreHome.ContentBlock
    },
    data() {
      return {
        geoipDatabaseInstalled: !!this.geoipDatabaseStartedInstalled,
        showFreeDownload: false,
        showPiwikNotManagingInfo: true,
        progressFreeDownload: 0,
        progressUpdateDownload: 0,
        buttonUpdateSaveText: CoreHome.translate("General_Save"),
        progressUpdateLabel: "",
        locationDbUrl: this.geoipLocUrl || "",
        ispDbUrl: this.geoipIspUrl || "",
        orgDbUrl: "",
        updatePeriod: this.geoipUpdatePeriod || "month",
        isUpdatingGeoIpDatabase: false,
        downloadErrorMessage: null,
        nextRunTimePrettyUpdated: void 0
      };
    },
    methods: {
      startDownloadFreeGeoIp() {
        this.showFreeDownload = true;
        this.showPiwikNotManagingInfo = false;
        this.progressFreeDownload = 0;
        this.downloadNextChunk(
          "downloadFreeDBIPLiteDB",
          (v) => {
            this.progressFreeDownload = v;
          },
          false,
          {}
        ).then(() => {
          window.location.reload();
        }).catch((e) => {
          this.geoipDatabaseInstalled = true;
          this.downloadErrorMessage = e.message;
        });
      },
      startAutomaticUpdateGeoIp() {
        this.buttonUpdateSaveText = CoreHome.translate("General_Continue");
        this.showGeoIpUpdateInfo();
      },
      showGeoIpUpdateInfo() {
        this.geoipDatabaseInstalled = true;
      },
      saveGeoIpLinks() {
        return CoreHome.AjaxHelper.post(
          {
            period: this.updatePeriod,
            module: "GeoIp2",
            action: "updateGeoIPLinks"
          },
          {
            loc_db: this.locationDbUrl,
            isp_db: this.ispDbUrl,
            org_db: this.orgDbUrl
          },
          {
            withTokenInUrl: true
          }
        ).then(
          (response) => this.downloadNextFileIfNeeded(response, null)
        ).then((response) => {
          this.progressUpdateLabel = "";
          this.isUpdatingGeoIpDatabase = false;
          CoreHome.NotificationsStore.show({
            message: CoreHome.translate("General_Done"),
            placeat: "#done-updating-updater",
            context: "success",
            noclear: true,
            type: "toast",
            style: {
              display: "inline-block"
            },
            id: "userCountryGeoIpUpdate"
          });
          this.nextRunTimePrettyUpdated = response.nextRunTime;
          $(this.$refs.inlineHelpNode).effect("highlight", {
            color: "#FFFFCB"
          }, 2e3);
          return void 0;
        }).catch((e) => {
          this.isUpdatingGeoIpDatabase = false;
          CoreHome.NotificationsStore.show({
            message: e.message,
            placeat: "#geoipdb-update-info-error",
            context: "error",
            style: {
              display: "inline-block"
            },
            id: "userCountryGeoIpUpdate",
            type: "transient"
          });
        });
      },
      downloadNextFileIfNeeded(response, currentDownloading) {
        if (response == null ? void 0 : response.to_download) {
          const continuing = currentDownloading === response.to_download;
          this.progressUpdateDownload = 0;
          this.progressUpdateLabel = response.to_download_label;
          this.isUpdatingGeoIpDatabase = true;
          return this.downloadNextChunk(
            "downloadMissingGeoIpDb",
            (v) => {
              this.progressUpdateDownload = v;
            },
            continuing,
            {
              key: response.to_download
            }
          ).then((r) => this.downloadNextFileIfNeeded(r, response.to_download));
        }
        return Promise.resolve(response);
      },
      downloadNextChunk(action, progressBarSet, cont, extraData) {
        const data = __spreadValues({}, extraData);
        return CoreHome.AjaxHelper.post(
          {
            module: "GeoIp2",
            action,
            continue: cont ? 1 : 0
          },
          data,
          { withTokenInUrl: true }
        ).catch(() => {
          throw new Error(CoreHome.translate("GeoIp2_FatalErrorDuringDownload"));
        }).then((response) => {
          if (response.error) {
            throw new Error(response.error);
          }
          const newProgressVal = Math.floor(
            response.current_size / response.expected_file_size * 100
          );
          progressBarSet(Math.min(newProgressVal, 100));
          if (newProgressVal < 100) {
            return this.downloadNextChunk(action, progressBarSet, true, extraData);
          }
          return response;
        });
      }
    },
    computed: {
      nextRunTimeText() {
        if (this.nextRunTimePrettyUpdated) {
          return this.nextRunTimePrettyUpdated;
        }
        if (!this.nextRunTime) {
          return CoreHome.translate("GeoIp2_UpdaterIsNotScheduledToRun");
        }
        if (this.nextRunTime * 1e3 < Date.now()) {
          return CoreHome.translate("GeoIp2_UpdaterScheduledForNextRun");
        }
        return CoreHome.translate(
          "GeoIp2_UpdaterWillRunNext",
          `<strong>${this.nextRunTimePretty}</strong>`
        );
      },
      providerPluginHelp() {
        if (this.isProviderPluginActive) {
          return void 0;
        }
        const text = CoreHome.translate("GeoIp2_ISPRequiresProviderPlugin");
        return `<div style="margin:0" class='alert alert-warning'>${text}</div>`;
      },
      contentTitle() {
        return CoreHome.translate(
          this.geoipDatabaseInstalled ? "GeoIp2_SetupAutomaticUpdatesOfGeoIP" : "GeoIp2_GeoIPDatabases"
        );
      },
      accuracyNote() {
        return CoreHome.translate(
          "UserCountry_GeoIpDbIpAccuracyNote",
          '<a href="https://dev.maxmind.com/geoip/geoip2/geolite2/?rId=piwik" rel="noreferrer noopener" target="_blank">',
          "</a>"
        );
      },
      purchasedGeoIpText() {
        const maxMindLink = "http://www.maxmind.com/en/geolocation_landing?rId=piwik";
        return CoreHome.translate(
          "GeoIp2_IPurchasedGeoIPDBs",
          `<a rel="noreferrer noopener" href="${maxMindLink}" target="_blank">`,
          "</a>",
          '<a rel="noreferrer noopener" href="https://db-ip.com/db/?refid=mtm" target="_blank">',
          "</a>"
        );
      },
      geoIPUpdaterInstructions() {
        return CoreHome.translate(
          "GeoIp2_GeoIPUpdaterInstructions",
          '<a href="http://www.maxmind.com/?rId=piwik" rel="noreferrer noopener" target="_blank">',
          "</a>",
          '<a rel="noreferrer noopener" href="https://db-ip.com/?refid=mtm" target="_blank">',
          "</a>"
        );
      },
      geoliteCityLink() {
        const translation = CoreHome.translate(
          "GeoIp2_GeoLiteCityLink",
          `<a rel="noreferrer noopener" href="${this.dbipLiteUrl}" target="_blank">`,
          this.dbipLiteUrl,
          "</a>"
        );
        return `${translation}<br /><br />`;
      },
      maxMindLinkExplanation() {
        return CoreHome.translate(
          "UserCountry_MaxMindLinkExplanation",
          CoreHome.externalLink("https://matomo.org/faq/how-to/how-do-i-get-the-geolocation-download-url-for-the-free-maxmind-db/"),
          "</a>"
        );
      },
      freeProgressbarLabel() {
        return CoreHome.translate(
          "GeoIp2_DownloadingDb",
          `<a href="${this.dbipLiteUrl}">${this.dbipLiteFilename}</a>...`
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
  const _hoisted_1 = { key: 0 };
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = { id: "manage-geoip-dbs" };
  const _hoisted_4 = {
    class: "row",
    id: "geoipdb-screen1"
  };
  const _hoisted_5 = { class: "geoipdb-column-1 col s6" };
  const _hoisted_6 = { class: "geoipdb-column-2 col s6" };
  const _hoisted_7 = ["innerHTML"];
  const _hoisted_8 = { class: "geoipdb-column-1 col s6" };
  const _hoisted_9 = ["value"];
  const _hoisted_10 = { class: "geoipdb-column-2 col s6" };
  const _hoisted_11 = ["value"];
  const _hoisted_12 = { class: "row" };
  const _hoisted_13 = ["innerHTML"];
  const _hoisted_14 = { id: "geoipdb-screen2-download" };
  const _hoisted_15 = {
    key: 1,
    id: "geoipdb-update-info"
  };
  const _hoisted_16 = ["innerHTML"];
  const _hoisted_17 = ["innerHTML"];
  const _hoisted_18 = ["innerHTML"];
  const _hoisted_19 = {
    id: "locationProviderUpdatePeriodInlineHelp",
    class: "inline-help-node",
    ref: "inlineHelpNode"
  };
  const _hoisted_20 = ["innerHTML"];
  const _hoisted_21 = { key: 1 };
  const _hoisted_22 = ["innerHTML"];
  const _hoisted_23 = ["value"];
  const _hoisted_24 = ["innerHTML"];
  const _hoisted_25 = { key: 1 };
  const _hoisted_26 = { class: "form-description" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Progressbar = vue.resolveComponent("Progressbar");
    const _component_Field = vue.resolveComponent("Field");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.contentTitle,
      id: "geoip-db-mangement"
    }, {
      default: vue.withCtx(() => [
        _ctx.showGeoipUpdateSection ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
          !_ctx.geoipDatabaseInstalled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
            vue.withDirectives(vue.createElementVNode("div", null, [
              vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("GeoIp2_NotManagingGeoIPDBs")), 1),
              vue.createElementVNode("div", _hoisted_3, [
                vue.createElementVNode("div", _hoisted_4, [
                  vue.createElementVNode("div", _hoisted_5, [
                    vue.createElementVNode("p", null, [
                      vue.createTextVNode(vue.toDisplayString(_ctx.translate("GeoIp2_IWantToDownloadFreeGeoIP")), 1),
                      _cache[6] || (_cache[6] = vue.createElementVNode("sup", null, [
                        vue.createElementVNode("small", null, "*")
                      ], -1))
                    ])
                  ]),
                  vue.createElementVNode("div", _hoisted_6, [
                    vue.createElementVNode("p", {
                      innerHTML: _ctx.$sanitize(_ctx.purchasedGeoIpText)
                    }, null, 8, _hoisted_7)
                  ]),
                  vue.createElementVNode("div", _hoisted_8, [
                    vue.createElementVNode("input", {
                      type: "button",
                      class: "btn",
                      onClick: _cache[0] || (_cache[0] = ($event) => _ctx.startDownloadFreeGeoIp()),
                      value: `${_ctx.translate("General_GetStarted")}...`
                    }, null, 8, _hoisted_9)
                  ]),
                  vue.createElementVNode("div", _hoisted_10, [
                    vue.createElementVNode("input", {
                      type: "button",
                      class: "btn",
                      id: "start-automatic-update-geoip",
                      onClick: _cache[1] || (_cache[1] = ($event) => _ctx.startAutomaticUpdateGeoIp()),
                      value: `${_ctx.translate("General_GetStarted")}...`
                    }, null, 8, _hoisted_11)
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_12, [
                  vue.createElementVNode("p", null, [
                    vue.createElementVNode("sup", null, [
                      _cache[7] || (_cache[7] = vue.createTextVNode("* ", -1)),
                      vue.createElementVNode("small", {
                        innerHTML: _ctx.$sanitize(_ctx.accuracyNote)
                      }, null, 8, _hoisted_13)
                    ])
                  ])
                ])
              ])
            ], 512), [
              [vue.vShow, _ctx.showPiwikNotManagingInfo]
            ]),
            vue.withDirectives(vue.createElementVNode("div", _hoisted_14, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Progressbar, {
                  label: _ctx.freeProgressbarLabel,
                  progress: _ctx.progressFreeDownload
                }, null, 8, ["label", "progress"])
              ])
            ], 512), [
              [vue.vShow, _ctx.showFreeDownload]
            ])
          ])) : vue.createCommentVNode("", true),
          _ctx.geoipDatabaseInstalled && !_ctx.downloadErrorMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15, [
            vue.createElementVNode("p", null, [
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.geoIPUpdaterInstructions)
              }, null, 8, _hoisted_16),
              _cache[10] || (_cache[10] = vue.createElementVNode("br", null, null, -1)),
              _cache[11] || (_cache[11] = vue.createElementVNode("br", null, null, -1)),
              !!_ctx.dbipLiteUrl ? (vue.openBlock(), vue.createElementBlock("span", {
                key: 0,
                innerHTML: _ctx.$sanitize(_ctx.geoliteCityLink)
              }, null, 8, _hoisted_17)) : vue.createCommentVNode("", true),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.maxMindLinkExplanation)
              }, null, 8, _hoisted_18),
              vue.withDirectives(vue.createElementVNode("span", null, [
                _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
                _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1)),
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("GeoIp2_GeoIPUpdaterIntro")) + ": ", 1)
              ], 512), [
                [vue.vShow, _ctx.geoipDatabaseInstalled]
              ])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "geoip-location-db",
                introduction: _ctx.translate("GeoIp2_LocationDatabase"),
                title: _ctx.translate("Actions_ColumnDownloadURL"),
                "inline-help": _ctx.translate("GeoIp2_LocationDatabaseHint"),
                modelValue: _ctx.locationDbUrl,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.locationDbUrl = $event)
              }, null, 8, ["introduction", "title", "inline-help", "modelValue"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "geoip-isp-db",
                introduction: _ctx.translate("GeoIp2_ISPDatabase"),
                title: _ctx.translate("Actions_ColumnDownloadURL"),
                "inline-help": _ctx.providerPluginHelp,
                modelValue: _ctx.ispDbUrl,
                "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.ispDbUrl = $event),
                disabled: !_ctx.isProviderPluginActive
              }, null, 8, ["introduction", "title", "inline-help", "modelValue", "disabled"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "radio",
                name: "geoip-update-period",
                introduction: _ctx.translate("GeoIp2_DownloadNewDatabasesEvery"),
                modelValue: _ctx.updatePeriod,
                "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.updatePeriod = $event),
                options: _ctx.updatePeriodOptions
              }, {
                "inline-help": vue.withCtx(() => [
                  vue.createElementVNode("div", _hoisted_19, [
                    _ctx.lastTimeUpdaterRun ? (vue.openBlock(), vue.createElementBlock("span", {
                      key: 0,
                      innerHTML: _ctx.$sanitize(
                        _ctx.translate("GeoIp2_UpdaterWasLastRun", _ctx.lastTimeUpdaterRun)
                      )
                    }, null, 8, _hoisted_20)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_21, vue.toDisplayString(_ctx.translate("GeoIp2_UpdaterHasNotBeenRun")), 1)),
                    _cache[12] || (_cache[12] = vue.createElementVNode("br", null, null, -1)),
                    _cache[13] || (_cache[13] = vue.createElementVNode("br", null, null, -1)),
                    vue.createElementVNode("div", {
                      id: "geoip-updater-next-run-time",
                      innerHTML: _ctx.$sanitize(_ctx.nextRunTimeText)
                    }, null, 8, _hoisted_22)
                  ], 512)
                ]),
                _: 1
              }, 8, ["introduction", "modelValue", "options"])
            ]),
            vue.createElementVNode("input", {
              type: "button",
              class: "btn",
              onClick: _cache[5] || (_cache[5] = ($event) => _ctx.saveGeoIpLinks()),
              value: _ctx.buttonUpdateSaveText
            }, null, 8, _hoisted_23),
            vue.createElementVNode("div", null, [
              _cache[14] || (_cache[14] = vue.createElementVNode("div", { id: "done-updating-updater" }, null, -1)),
              _cache[15] || (_cache[15] = vue.createElementVNode("div", { id: "geoipdb-update-info-error" }, null, -1)),
              vue.createElementVNode("div", null, [
                vue.withDirectives(vue.createVNode(_component_Progressbar, {
                  progress: _ctx.progressUpdateDownload,
                  label: _ctx.progressUpdateLabel
                }, null, 8, ["progress", "label"]), [
                  [vue.vShow, _ctx.isUpdatingGeoIpDatabase]
                ])
              ])
            ])
          ])) : vue.createCommentVNode("", true),
          _ctx.downloadErrorMessage ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 2,
            innerHTML: _ctx.$sanitize(_ctx.downloadErrorMessage)
          }, null, 8, _hoisted_24)) : vue.createCommentVNode("", true)
        ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_25, [
          vue.createElementVNode("p", _hoisted_26, vue.toDisplayString(_ctx.translate("GeoIp2_CannotSetupGeoIPAutoUpdating")), 1)
        ]))
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const GeoIp2Updater = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.GeoIp2Updater = GeoIp2Updater;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
