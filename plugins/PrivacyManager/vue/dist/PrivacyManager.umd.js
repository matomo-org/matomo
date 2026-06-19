(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("SegmentEditor"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "SegmentEditor", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.PrivacyManager = {}, global.Vue, global.CoreHome, global.SegmentEditor, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, SegmentEditor, CorePluginsAdmin) {
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
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
var __async = (__this, __arguments, generator) => {
  return new Promise((resolve, reject) => {
    var fulfilled = (value) => {
      try {
        step(generator.next(value));
      } catch (e) {
        reject(e);
      }
    };
    var rejected = (value) => {
      try {
        step(generator.throw(value));
      } catch (e) {
        reject(e);
      }
    };
    var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
    step((generator = generator.apply(__this, __arguments)).next());
  });
};

  const _sfc_main$h = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SiteSelector: CoreHome.SiteSelector,
      SegmentGenerator: SegmentEditor.SegmentGenerator,
      SaveButton: CorePluginsAdmin.SaveButton,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        isLoading: false,
        isDeleting: false,
        site: {
          id: "all",
          name: CoreHome.translate("UsersManager_AllWebsites")
        },
        segment_filter: "visitId==",
        dataSubjects: [],
        toggleAll: true,
        hasSearched: false,
        profileEnabled: CoreHome.Matomo.visitorProfileEnabled,
        dataSubjectsActive: [],
        isVisitorLogAndProfileEnabled: true,
        allWebsitesContainsDisabledSite: false
      };
    },
    created() {
      this.changeSite(this.site);
    },
    watch: {
      site(newSite) {
        if (newSite.id === "all") {
          this.isVisitorLogAndProfileEnabled = true;
          return;
        }
        this.allWebsitesContainsDisabledSite = false;
        this.isLoading = true;
        this.dataSubjects = [];
        this.hasSearched = false;
        CoreHome.AjaxHelper.fetch({
          method: "Live.isVisitorProfileEnabled",
          idSite: newSite.id
        }).then((isEnabled) => {
          this.isVisitorLogAndProfileEnabled = isEnabled.value;
        }).finally(() => {
          this.isLoading = false;
        });
      }
    },
    setup() {
      const sitesPromise = CoreHome.AjaxHelper.fetch({
        method: "SitesManager.getSitesIdWithAdminAccess",
        filter_limit: "-1"
      });
      return {
        getSites() {
          return sitesPromise;
        }
      };
    },
    methods: {
      changeSite(newValue) {
        CoreHome.AjaxHelper.fetch(
          {
            module: "API",
            method: "Live.isVisitorProfileEnabled",
            filter_limit: -1,
            idSite: newValue.id
          },
          {
            createErrorNotification: false
            // don't show errors from this API in UI
          }
        ).then((response) => {
          if (!response.value && this.segment_filter === "userId==") {
            this.segment_filter = "visitId==";
          } else if (response.value && this.segment_filter === "visitId==") {
            this.segment_filter = "userId==";
          }
        }).catch(() => {
          this.segment_filter = "visitId==";
        });
      },
      showSuccessNotification(message) {
        const notificationInstanceId = CoreHome.NotificationsStore.show({
          message,
          context: "success",
          id: "manageGdpr",
          type: "transient"
        });
        setTimeout(() => {
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }, 200);
      },
      linkTo(action, module2 = "PrivacyManager") {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: module2,
          action
        }))}`;
      },
      toggleActivateAll() {
        this.dataSubjectsActive.fill(this.toggleAll);
      },
      showProfile(visitorId, idSite) {
        CoreHome.Matomo.helper.showVisitorProfilePopup(visitorId, idSite);
      },
      exportDataSubject() {
        const visitsToDelete = this.activatedDataSubjects;
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "PrivacyManager.exportDataSubjects",
            format: "json",
            filter_limit: -1
          },
          {
            visits: visitsToDelete
          }
        ).then((visits) => {
          this.showSuccessNotification(CoreHome.translate("PrivacyManager_VisitsSuccessfullyExported"));
          CoreHome.Matomo.helper.sendContentAsDownload("exported_data_subjects.json", JSON.stringify(visits));
        });
      },
      deleteDataSubject() {
        CoreHome.Matomo.helper.modalConfirm(this.$refs.confirmDeleteDataSubject, {
          yes: () => {
            this.isDeleting = true;
            const visitsToDelete = this.activatedDataSubjects;
            CoreHome.AjaxHelper.post(
              {
                module: "API",
                method: "PrivacyManager.deleteDataSubjects",
                filter_limit: -1
              },
              {
                visits: visitsToDelete
              }
            ).then(() => {
              this.dataSubjects = [];
              this.showSuccessNotification(CoreHome.translate("PrivacyManager_VisitsSuccessfullyDeleted"));
              this.findDataSubjects();
            }).finally(() => {
              this.isDeleting = false;
            });
          }
        });
      },
      addFilter(segment, value) {
        this.segment_filter += `,${segment}==${value}`;
        this.findDataSubjects();
      },
      findDataSubjects() {
        this.dataSubjects = [];
        this.dataSubjectsActive = [];
        this.isLoading = true;
        this.toggleAll = true;
        this.hasSearched = false;
        this.getSites().then((idsites) => {
          let siteIds = this.site.id;
          if (siteIds === "all" && !CoreHome.Matomo.hasSuperUserAccess) {
            siteIds = idsites;
            if (Array.isArray(idsites)) {
              siteIds = idsites.join(",");
            }
          }
          CoreHome.AjaxHelper.fetch({
            method: "Live.isVisitorProfileEnabled",
            idSite: siteIds
          }).then((isEnabled) => {
            this.allWebsitesContainsDisabledSite = !isEnabled.value;
          });
          CoreHome.AjaxHelper.fetch({
            idSite: siteIds,
            module: "API",
            method: "PrivacyManager.findDataSubjects",
            segment: this.segment_filter
          }).then((visits) => {
            this.hasSearched = true;
            this.dataSubjectsActive = visits.map(() => true);
            this.dataSubjects = visits;
          }).finally(() => {
            this.isLoading = false;
          });
        });
      }
    },
    computed: {
      hasActiveDataSubjects() {
        return !!this.activatedDataSubjects.length;
      },
      activatedDataSubjects() {
        return this.dataSubjects.filter((v, i) => this.dataSubjectsActive[i]).map((v) => ({
          idsite: v.idSite,
          idvisit: v.idVisit
        }));
      },
      overviewHintText() {
        return CoreHome.translate(
          "PrivacyManager_GdprToolsOverviewHint",
          `<a href="${this.linkTo("gdprOverview")}">`,
          "</a>"
        );
      },
      siteSettingsText() {
        return CoreHome.translate(
          "PrivacyManager_PleaseEnableVisitorLogsProfilesSites",
          `<a href="${this.linkTo("index", "SitesManager")}">`,
          "</a>"
        );
      },
      siteSettingsTextSingle() {
        return CoreHome.translate(
          "PrivacyManager_PleaseEnableVisitorLogsProfiles",
          `<a href="${this.linkTo("index", "SitesManager")}">`,
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
  const _hoisted_1$g = { class: "manageGdpr" };
  const _hoisted_2$f = { class: "intro" };
  const _hoisted_3$e = ["innerHTML"];
  const _hoisted_4$d = { class: "form-group row" };
  const _hoisted_5$b = { class: "col s12 input-field" };
  const _hoisted_6$9 = {
    for: "gdprsite",
    class: "siteSelectorLabel"
  };
  const _hoisted_7$8 = { class: "sites_autocomplete" };
  const _hoisted_8$6 = {
    key: 0,
    class: "form-group row segmentFilterGroup"
  };
  const _hoisted_9$6 = { class: "col s12" };
  const _hoisted_10$6 = { style: { "margin": "8px 0", "display": "inline-block" } };
  const _hoisted_11$5 = {
    key: 2,
    class: "dataUnavailable system notification notification-icon notification-info"
  };
  const _hoisted_12$5 = ["innerHTML"];
  const _hoisted_13$3 = {
    key: 0,
    class: "system notification notification-icon notification-info"
  };
  const _hoisted_14$3 = { class: "notification-body" };
  const _hoisted_15$3 = ["innerHTML"];
  const _hoisted_16$3 = { class: "checkInclude" };
  const _hoisted_17$3 = { colspan: "8" };
  const _hoisted_18$3 = ["title"];
  const _hoisted_19$2 = { class: "checkInclude" };
  const _hoisted_20$2 = ["title"];
  const _hoisted_21$1 = { class: "visitId" };
  const _hoisted_22$1 = { class: "visitorId" };
  const _hoisted_23$1 = ["title", "onClick"];
  const _hoisted_24$1 = { class: "visitorIp" };
  const _hoisted_25$1 = ["title", "onClick"];
  const _hoisted_26$1 = { class: "userId" };
  const _hoisted_27$1 = ["title", "onClick"];
  const _hoisted_28$1 = ["title"];
  const _hoisted_29$1 = ["src"];
  const _hoisted_30$1 = ["title"];
  const _hoisted_31$1 = ["src"];
  const _hoisted_32$1 = ["title"];
  const _hoisted_33$1 = ["src"];
  const _hoisted_34 = ["title"];
  const _hoisted_35 = ["src"];
  const _hoisted_36 = ["onClick"];
  const _hoisted_37 = {
    class: "ui-confirm",
    id: "confirmDeleteDataSubject",
    ref: "confirmDeleteDataSubject"
  };
  const _hoisted_38 = ["value"];
  const _hoisted_39 = ["value"];
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SiteSelector = vue.resolveComponent("SiteSelector");
    const _component_SegmentGenerator = vue.resolveComponent("SegmentGenerator");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_Field = vue.resolveComponent("Field");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$g, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_GdprTools")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_2$f, [
            vue.createElementVNode("p", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_GdprToolsPageIntro1")) + " ", 1),
              _cache[7] || (_cache[7] = vue.createElementVNode("br", null, null, -1)),
              _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_GdprToolsPageIntro2")) + " ", 1),
              _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1))
            ]),
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprToolsPageIntroAccessRight")), 1),
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprToolsPageIntroEraseRight")), 1)
            ]),
            vue.createElementVNode("p", null, [
              _cache[10] || (_cache[10] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.overviewHintText)
              }, null, 8, _hoisted_3$e)
            ])
          ]),
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_SearchForDataSubject")), 1),
          vue.createElementVNode("div", _hoisted_4$d, [
            vue.createElementVNode("div", _hoisted_5$b, [
              vue.createElementVNode("div", null, [
                vue.createElementVNode("label", _hoisted_6$9, vue.toDisplayString(_ctx.translate("PrivacyManager_SelectWebsite")), 1),
                vue.createElementVNode("div", _hoisted_7$8, [
                  vue.createVNode(_component_SiteSelector, {
                    id: "gdprsite",
                    modelValue: _ctx.site,
                    "onUpdate:modelValue": [
                      _cache[0] || (_cache[0] = ($event) => _ctx.site = $event),
                      _cache[1] || (_cache[1] = ($event) => _ctx.changeSite($event))
                    ],
                    "show-all-sites-item": true,
                    "switch-site-on-select": false,
                    "show-selected-site": true
                  }, null, 8, ["modelValue"])
                ])
              ])
            ])
          ]),
          _ctx.isVisitorLogAndProfileEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$6, [
            vue.createElementVNode("div", _hoisted_9$6, [
              vue.createElementVNode("div", null, [
                vue.createElementVNode("label", _hoisted_10$6, vue.toDisplayString(_ctx.translate("PrivacyManager_FindDataSubjectsBy")), 1),
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_SegmentGenerator, {
                    modelValue: _ctx.segment_filter,
                    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.segment_filter = $event),
                    "visit-segments-only": true,
                    idsite: _ctx.site.id,
                    "show-segment-editor": true
                  }, null, 8, ["modelValue", "idsite"])
                ])
              ])
            ])
          ])) : vue.createCommentVNode("", true),
          _ctx.isVisitorLogAndProfileEnabled ? (vue.openBlock(), vue.createBlock(_component_SaveButton, {
            key: 1,
            class: "findDataSubjects",
            value: _ctx.translate("PrivacyManager_FindMatchingDataSubjects"),
            onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.findDataSubjects()),
            disabled: !_ctx.segment_filter,
            saving: _ctx.isLoading
          }, null, 8, ["value", "disabled", "saving"])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_11$5, [
            vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_SiteDataNotAvailable")), 1),
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_VisitorLogsProfilesDisabledMessage")), 1),
            vue.createElementVNode("p", {
              innerHTML: _ctx.$sanitize(_ctx.siteSettingsTextSingle)
            }, null, 8, _hoisted_12$5)
          ]))
        ]),
        _: 1
      }, 8, ["content-title"]),
      _ctx.allWebsitesContainsDisabledSite ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_13$3, [
        vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_SiteDataNotAvailableCertainSites")), 1),
        vue.createElementVNode("div", _hoisted_14$3, [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_VisitorLogsProfilesSiteNamesDisabledMessage")), 1),
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.siteSettingsText)
          }, null, 8, _hoisted_15$3)
        ])
      ])) : vue.createCommentVNode("", true),
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_NoDataSubjectsFound")), 1)
      ], 512), [
        [vue.vShow, !_ctx.dataSubjects.length && _ctx.hasSearched]
      ]),
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_MatchingDataSubjects")), 1),
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_VisitsMatchedCriteria")) + " " + vue.toDisplayString(_ctx.translate("PrivacyManager_ExportingNote")) + " ", 1),
          _cache[11] || (_cache[11] = vue.createElementVNode("br", null, null, -1)),
          _cache[12] || (_cache[12] = vue.createTextVNode()),
          _cache[13] || (_cache[13] = vue.createElementVNode("br", null, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_DeletionFromMatomoOnly")) + " ", 1),
          _cache[14] || (_cache[14] = vue.createElementVNode("br", null, null, -1)),
          _cache[15] || (_cache[15] = vue.createElementVNode("br", null, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_ResultIncludesAllVisits")), 1)
        ]),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
          vue.createElementVNode("thead", null, [
            vue.createElementVNode("tr", null, [
              vue.createElementVNode("th", _hoisted_16$3, [
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "checkbox",
                    name: "activateAll",
                    "model-value": _ctx.toggleAll,
                    "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
                      _ctx.toggleAll = $event;
                      _ctx.toggleActivateAll();
                    }),
                    "full-width": true
                  }, null, 8, ["model-value"])
                ])
              ]),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Website")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_VisitId")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_VisitorID")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_VisitorIP")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_UserId")), 1),
              vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Details")), 1),
              vue.withDirectives(vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Action")), 513), [
                [vue.vShow, _ctx.profileEnabled]
              ])
            ])
          ]),
          vue.createElementVNode("tbody", null, [
            vue.withDirectives(vue.createElementVNode("tr", null, [
              vue.createElementVNode("td", _hoisted_17$3, vue.toDisplayString(_ctx.translate("PrivacyManager_ResultTruncated", "400")), 1)
            ], 512), [
              [vue.vShow, _ctx.dataSubjects.length > 400]
            ]),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.dataSubjects, (dataSubject, index) => {
              return vue.openBlock(), vue.createElementBlock("tr", {
                title: `${_ctx.translate("PrivacyManager_LastAction")}: ${dataSubject.lastActionDateTime}`,
                key: index
              }, [
                vue.createElementVNode("td", _hoisted_19$2, [
                  vue.createElementVNode("div", null, [
                    vue.createVNode(_component_Field, {
                      uicontrol: "checkbox",
                      name: `subject${dataSubject.idVisit}`,
                      modelValue: _ctx.dataSubjectsActive[index],
                      "onUpdate:modelValue": ($event) => _ctx.dataSubjectsActive[index] = $event,
                      "full-width": true
                    }, null, 8, ["name", "modelValue", "onUpdate:modelValue"])
                  ])
                ]),
                vue.createElementVNode("td", {
                  class: "site",
                  title: `(${_ctx.translate("General_Id")} ${dataSubject.idSite})`
                }, vue.toDisplayString(dataSubject.siteName), 9, _hoisted_20$2),
                vue.createElementVNode("td", _hoisted_21$1, vue.toDisplayString(dataSubject.idVisit), 1),
                vue.createElementVNode("td", _hoisted_22$1, [
                  vue.createElementVNode("a", {
                    title: _ctx.translate("PrivacyManager_AddVisitorIdToSearch"),
                    onClick: ($event) => _ctx.addFilter("visitorId", dataSubject.visitorId)
                  }, vue.toDisplayString(dataSubject.visitorId), 9, _hoisted_23$1)
                ]),
                vue.createElementVNode("td", _hoisted_24$1, [
                  vue.createElementVNode("a", {
                    title: _ctx.translate("PrivacyManager_AddVisitorIPToSearch"),
                    onClick: ($event) => _ctx.addFilter("visitIp", dataSubject.visitIp)
                  }, vue.toDisplayString(dataSubject.visitIp), 9, _hoisted_25$1)
                ]),
                vue.createElementVNode("td", _hoisted_26$1, [
                  vue.createElementVNode("a", {
                    title: _ctx.translate("PrivacyManager_AddUserIdToSearch"),
                    onClick: ($event) => _ctx.addFilter("userId", dataSubject.userId)
                  }, vue.toDisplayString(dataSubject.userId), 9, _hoisted_27$1)
                ]),
                vue.createElementVNode("td", null, [
                  vue.createElementVNode("span", {
                    title: `${dataSubject.deviceType} ${dataSubject.deviceModel}`,
                    style: { "margin-right": "3.5px" }
                  }, [
                    vue.createElementVNode("img", {
                      height: "16",
                      src: dataSubject.deviceTypeIcon
                    }, null, 8, _hoisted_29$1)
                  ], 8, _hoisted_28$1),
                  vue.createElementVNode("span", {
                    title: dataSubject.operatingSystem,
                    style: { "margin-right": "3.5px" }
                  }, [
                    vue.createElementVNode("img", {
                      height: "16",
                      src: dataSubject.operatingSystemIcon
                    }, null, 8, _hoisted_31$1)
                  ], 8, _hoisted_30$1),
                  vue.createElementVNode("span", {
                    title: `${dataSubject.browser} ${dataSubject.browserFamilyDescription}`,
                    style: { "margin-right": "3.5px" }
                  }, [
                    vue.createElementVNode("img", {
                      height: "16",
                      src: dataSubject.browserIcon
                    }, null, 8, _hoisted_33$1)
                  ], 8, _hoisted_32$1),
                  vue.createElementVNode("span", {
                    title: `${dataSubject.country} ${dataSubject.region || ""}`
                  }, [
                    vue.createElementVNode("img", {
                      height: "16",
                      src: dataSubject.countryFlag
                    }, null, 8, _hoisted_35)
                  ], 8, _hoisted_34)
                ]),
                vue.withDirectives(vue.createElementVNode("td", null, [
                  vue.createElementVNode("a", {
                    class: "visitorLogTooltip",
                    title: "View visitor profile",
                    onClick: ($event) => _ctx.showProfile(dataSubject.visitorId, dataSubject.idSite)
                  }, [
                    _cache[16] || (_cache[16] = vue.createElementVNode("img", {
                      src: "plugins/Live/images/visitorProfileLaunch.png",
                      style: { "margin-right": "3.5px" }
                    }, null, -1)),
                    vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Live_ViewVisitorProfile")), 1)
                  ], 8, _hoisted_36)
                ], 512), [
                  [vue.vShow, _ctx.profileEnabled]
                ])
              ], 8, _hoisted_18$3);
            }), 128))
          ])
        ])), [
          [_directive_content_table]
        ]),
        vue.createVNode(_component_SaveButton, {
          class: "exportDataSubjects",
          style: { "margin-right": "3.5px" },
          onConfirm: _cache[5] || (_cache[5] = ($event) => _ctx.exportDataSubject()),
          disabled: !_ctx.hasActiveDataSubjects,
          value: _ctx.translate("PrivacyManager_ExportSelectedVisits")
        }, null, 8, ["disabled", "value"]),
        vue.createVNode(_component_SaveButton, {
          class: "deleteDataSubjects",
          onConfirm: _cache[6] || (_cache[6] = ($event) => _ctx.deleteDataSubject()),
          disabled: !_ctx.hasActiveDataSubjects || _ctx.isDeleting,
          value: _ctx.translate("PrivacyManager_DeleteSelectedVisits")
        }, null, 8, ["disabled", "value"])
      ], 512), [
        [vue.vShow, _ctx.dataSubjects.length]
      ]),
      vue.createElementVNode("div", _hoisted_37, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteVisitsConfirm")), 1),
        vue.createElementVNode("input", {
          role: "yes",
          type: "button",
          value: _ctx.translate("General_Yes")
        }, null, 8, _hoisted_38),
        vue.createElementVNode("input", {
          role: "no",
          type: "button",
          value: _ctx.translate("General_No")
        }, null, 8, _hoisted_39)
      ], 512)
    ]);
  }
  const ManageGdpr = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$h]]);
  function boolToInt(value) {
    return value === true || value === 1 || value === "1" ? 1 : 0;
  }
  const SYSTEM_SETTINGS = "system";
  const SITE_SPECIFIC_SETTINGS = "site-specific";
  const _sfc_main$g = vue.defineComponent({
    props: {
      ipAnonymizerEnabled: Boolean,
      anonymizeUserId: Boolean,
      ipAddressMaskLength: {
        type: [Number, String],
        required: true
      },
      useAnonymizedIpForVisitEnrichment: {
        type: [Boolean, String, Number],
        default: 0
      },
      anonymizeOrderId: Boolean,
      forceCookielessTracking: Boolean,
      anonymizeReferrer: String,
      maskLengthOptions: {
        type: Array,
        required: true
      },
      useAnonymizedIpForVisitEnrichmentOptions: {
        type: Array,
        required: true
      },
      trackerFileName: {
        type: String,
        required: true
      },
      trackerWritable: {
        type: Boolean,
        required: true
      },
      referrerAnonymizationOptions: {
        type: Object,
        required: true
      },
      randomizeConfigId: Boolean,
      idSiteSpecific: {
        type: [String, Number]
      },
      useSiteSpecificSettings: {
        type: Boolean,
        default: false
      },
      triggerSave: {
        type: Boolean,
        default: false
      },
      extraMetadata: {
        type: Object,
        default: () => ({})
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    emits: ["updated", "aborted"],
    data() {
      return {
        isLoading: false,
        actualEnabled: this.ipAnonymizerEnabled,
        actualUseSiteSpecificSettings: this.getActualUseSiteSpecificSettings(),
        actualMaskLength: +this.ipAddressMaskLength,
        actualUseAnonymizedIpForVisitEnrichment: boolToInt(
          this.useAnonymizedIpForVisitEnrichment
        ),
        actualAnonymizeUserId: !!this.anonymizeUserId,
        actualAnonymizeOrderId: !!this.anonymizeOrderId,
        actualForceCookielessTracking: !!this.forceCookielessTracking,
        actualAnonymizeReferrer: this.anonymizeReferrer,
        actualRandomizeConfigId: !!this.randomizeConfigId,
        showPasswordConfirmation: false
      };
    },
    methods: {
      shouldSave() {
        if (this.showSettings && this.actualRandomizeConfigId) {
          this.showPasswordConfirmation = true;
        } else {
          this.save();
        }
      },
      abortPasswordConfirmation() {
        this.$emit("aborted");
      },
      save(password) {
        this.isLoading = true;
        const postParams = {
          anonymizeIPEnable: boolToInt(this.actualEnabled),
          anonymizeUserId: boolToInt(this.actualAnonymizeUserId),
          anonymizeOrderId: boolToInt(this.actualAnonymizeOrderId),
          forceCookielessTracking: this.idSiteSpecific ? void 0 : boolToInt(this.actualForceCookielessTracking),
          anonymizeReferrer: this.actualAnonymizeReferrer ? this.actualAnonymizeReferrer : "",
          ipAddressMaskLength: this.actualMaskLength,
          useAnonymizedIpForVisitEnrichment: this.actualUseAnonymizedIpForVisitEnrichment,
          randomizeConfigId: boolToInt(this.actualRandomizeConfigId),
          idSiteSpecific: this.idSiteSpecific ? this.idSiteSpecific : void 0,
          useSiteSpecificSettings: this.idSiteSpecific ? boolToInt(this.isSiteSpecificSettingsEnabled) : void 0
        };
        if (password) {
          postParams.passwordConfirmation = password;
        }
        CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "PrivacyManager.setAnonymizeIpSettings"
          },
          postParams
        ).then(() => {
          if (!this.idSiteSpecific) {
            const notificationInstanceId = CoreHome.NotificationsStore.show({
              message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
              context: "success",
              id: "privacyManagerSettings",
              type: "toast"
            });
            CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
          }
          this.$emit("updated");
        }).catch(() => {
          this.$emit("aborted");
        }).finally(() => {
          this.isLoading = false;
        });
      },
      getActualUseSiteSpecificSettings() {
        return this.idSiteSpecific && this.useSiteSpecificSettings ? SITE_SPECIFIC_SETTINGS : SYSTEM_SETTINGS;
      },
      randomiseConfigIdHelpText() {
        const helpText = CoreHome.translate("PrivacyManager_RandomizeConfigIdNote");
        const helpTextWarning = CoreHome.translate(
          "PrivacyManager_RandomizeConfigIdNoteWarning",
          "<strong>",
          "</strong>"
        );
        return `${helpText}<br><br>${helpTextWarning}`;
      },
      getExtraMetadataForField(fieldName) {
        var _a;
        return (_a = this.extraMetadata) == null ? void 0 : _a[fieldName];
      }
    },
    computed: {
      anonymizeIpEnabledHelp() {
        const inlineHelp1 = CoreHome.translate("PrivacyManager_AnonymizeIpInlineHelp");
        const inlineHelp2 = CoreHome.translate("PrivacyManager_AnonymizeIpDescription");
        return `${inlineHelp1} ${inlineHelp2}`;
      },
      passwordConfirmationTitle() {
        if (this.idSiteSpecific) {
          return CoreHome.translate("PrivacyManager_ConfirmConfigRandomisationEnabledPerSite");
        }
        return CoreHome.translate("PrivacyManager_ConfirmConfigRandomisationEnabled");
      },
      useSiteSpecificSettingsHelpText() {
        const link = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "PrivacyManager",
          action: "privacySettings"
        }))}`;
        return CoreHome.translate(
          "PrivacyManager_UseSiteSpecificSettingsHelpText",
          `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
          "</a>",
          CoreHome.translate("PrivacyManager_UseSiteSpecificSettings")
        );
      },
      useAnonymizedIpForVisitEnrichmentHelpText() {
        const description = CoreHome.translate("PrivacyManager_UseAnonymizedIpForVisitEnrichmentDesc");
        const readMore = CoreHome.translate(
          "PrivacyManager_UseAnonymizedIpForVisitEnrichmentReadMore",
          CoreHome.externalLink("https://matomo.org/faq/how-to/setting-up-accurate-visitors-geolocation"),
          "</a>"
        );
        return `${description}<br/><br/>${readMore}`;
      },
      showSettings() {
        return !this.idSiteSpecific || this.isSiteSpecificSettingsEnabled;
      },
      isSiteSpecificSettingsEnabled() {
        return this.idSiteSpecific && this.actualUseSiteSpecificSettings === SITE_SPECIFIC_SETTINGS;
      },
      useSiteSpecificSettingsOptions() {
        return [
          {
            value: CoreHome.translate("PrivacyManager_UseSystemSettings"),
            key: SYSTEM_SETTINGS
          },
          {
            value: CoreHome.translate("PrivacyManager_UseSiteSpecificSettings"),
            key: SITE_SPECIFIC_SETTINGS
          }
        ];
      }
    },
    watch: {
      triggerSave(newValue) {
        if (newValue) {
          this.shouldSave();
        }
      }
    }
  });
  const _hoisted_1$f = { class: "anonymizeSettings" };
  const _hoisted_2$e = { class: "anonymizeIpSettingsField" };
  const _hoisted_3$d = { class: "maskLengthField" };
  const _hoisted_4$c = { class: "useAnonymizedIpForVisitEnrichmentField" };
  const _hoisted_5$a = { class: "anonymizeUserIdField" };
  const _hoisted_6$8 = { class: "anonymizeOrderIdField" };
  const _hoisted_7$7 = {
    key: 0,
    class: "forceCookielessTrackingField"
  };
  const _hoisted_8$5 = { key: 0 };
  const _hoisted_9$5 = { class: "alert-warning alert" };
  const _hoisted_10$5 = { class: "anonymizeReferrerField" };
  const _hoisted_11$4 = { class: "randomizeConfigIdField" };
  const _hoisted_12$4 = {
    key: 2,
    class: "footer-buttons"
  };
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _directive_form = vue.resolveDirective("form");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$f, [
      _ctx.idSiteSpecific ? (vue.openBlock(), vue.createBlock(_component_Field, {
        key: 0,
        uicontrol: "radio",
        name: `useSiteSpecificSettings${_ctx.idSiteSpecific}`,
        title: _ctx.translate("PrivacyManager_SiteAnonymizationConfig"),
        modelValue: _ctx.actualUseSiteSpecificSettings,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.actualUseSiteSpecificSettings = $event),
        options: _ctx.useSiteSpecificSettingsOptions,
        "inline-help": _ctx.useSiteSpecificSettingsHelpText
      }, null, 8, ["name", "title", "modelValue", "options", "inline-help"])) : vue.createCommentVNode("", true),
      _ctx.showSettings ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
        vue.createElementVNode("div", _hoisted_2$e, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: `anonymizeIpSettings${_ctx.idSiteSpecific}`,
            title: _ctx.translate("PrivacyManager_UseAnonymizeIp"),
            modelValue: _ctx.actualEnabled,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.actualEnabled = $event),
            "inline-help": _ctx.anonymizeIpEnabledHelp,
            "extra-metadata": _ctx.getExtraMetadataForField("ipAnonymizerEnabled")
          }, null, 8, ["name", "title", "modelValue", "inline-help", "extra-metadata"])
        ]),
        vue.withDirectives(vue.createElementVNode("div", null, [
          vue.createElementVNode("div", _hoisted_3$d, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: `maskLength${_ctx.idSiteSpecific}`,
              title: _ctx.translate("PrivacyManager_AnonymizeIpMaskLengtDescription"),
              modelValue: _ctx.actualMaskLength,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.actualMaskLength = $event),
              options: _ctx.maskLengthOptions,
              "inline-help": _ctx.translate("PrivacyManager_GeolocationAnonymizeIpNote"),
              "extra-metadata": _ctx.getExtraMetadataForField("ipAddressMaskLength")
            }, null, 8, ["name", "title", "modelValue", "options", "inline-help", "extra-metadata"])
          ]),
          vue.createElementVNode("div", _hoisted_4$c, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: `useAnonymizedIpForVisitEnrichment${_ctx.idSiteSpecific}`,
              title: _ctx.translate("PrivacyManager_UseAnonymizedIpForVisitEnrichment"),
              modelValue: _ctx.actualUseAnonymizedIpForVisitEnrichment,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.actualUseAnonymizedIpForVisitEnrichment = $event),
              options: _ctx.useAnonymizedIpForVisitEnrichmentOptions,
              "inline-help": _ctx.useAnonymizedIpForVisitEnrichmentHelpText,
              "extra-metadata": _ctx.getExtraMetadataForField("useAnonymizedIpForVisitEnrichment")
            }, null, 8, ["name", "title", "modelValue", "options", "inline-help", "extra-metadata"])
          ])
        ], 512), [
          [vue.vShow, _ctx.actualEnabled]
        ]),
        vue.createElementVNode("div", _hoisted_5$a, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: `anonymizeUserId${_ctx.idSiteSpecific}`,
            title: _ctx.translate("PrivacyManager_PseudonymizeUserId"),
            modelValue: _ctx.actualAnonymizeUserId,
            "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.actualAnonymizeUserId = $event),
            "extra-metadata": _ctx.getExtraMetadataForField("anonymizeUserId")
          }, {
            "inline-help": vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_PseudonymizeUserIdNote")) + " ", 1),
              _cache[11] || (_cache[11] = vue.createElementVNode("br", null, null, -1)),
              _cache[12] || (_cache[12] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("em", null, vue.toDisplayString(_ctx.translate("PrivacyManager_PseudonymizeUserIdNote2")), 1)
            ]),
            _: 1
          }, 8, ["name", "title", "modelValue", "extra-metadata"])
        ]),
        vue.createElementVNode("div", _hoisted_6$8, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: `anonymizeOrderId${_ctx.idSiteSpecific}`,
            title: _ctx.translate("Ecommerce_UseAnonymizeOrderId"),
            modelValue: _ctx.actualAnonymizeOrderId,
            "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.actualAnonymizeOrderId = $event),
            "inline-help": _ctx.translate("Ecommerce_AnonymizeOrderIdNote"),
            "extra-metadata": _ctx.getExtraMetadataForField("anonymizeOrderId")
          }, null, 8, ["name", "title", "modelValue", "inline-help", "extra-metadata"])
        ]),
        !_ctx.idSiteSpecific ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$7, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "forceCookielessTracking",
            title: _ctx.translate("PrivacyManager_ForceCookielessTracking"),
            modelValue: _ctx.actualForceCookielessTracking,
            "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.actualForceCookielessTracking = $event),
            "extra-metadata": _ctx.getExtraMetadataForField("forceCookielessTracking")
          }, {
            "inline-help": vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_ForceCookielessTrackingDescription", _ctx.trackerFileName)) + " ", 1),
              _cache[15] || (_cache[15] = vue.createElementVNode("br", null, null, -1)),
              _cache[16] || (_cache[16] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("em", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ForceCookielessTrackingDescription2")), 1),
              !_ctx.trackerWritable ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8$5, [
                _cache[13] || (_cache[13] = vue.createElementVNode("br", null, null, -1)),
                _cache[14] || (_cache[14] = vue.createElementVNode("br", null, null, -1)),
                vue.createElementVNode("p", _hoisted_9$5, vue.toDisplayString(_ctx.translate(
                  "PrivacyManager_ForceCookielessTrackingDescriptionNotWritable",
                  _ctx.trackerFileName
                )), 1)
              ])) : vue.createCommentVNode("", true)
            ]),
            _: 1
          }, 8, ["title", "modelValue", "extra-metadata"])
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_10$5, [
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: `anonymizeReferrer${_ctx.idSiteSpecific}`,
            title: _ctx.translate("PrivacyManager_AnonymizeReferrer"),
            modelValue: _ctx.actualAnonymizeReferrer,
            "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.actualAnonymizeReferrer = $event),
            options: _ctx.referrerAnonymizationOptions,
            "inline-help": _ctx.translate("PrivacyManager_AnonymizeReferrerNote"),
            "extra-metadata": _ctx.getExtraMetadataForField("anonymizeReferrer")
          }, null, 8, ["name", "title", "modelValue", "options", "inline-help", "extra-metadata"])
        ]),
        vue.createElementVNode("div", _hoisted_11$4, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: `randomizeConfigId${_ctx.idSiteSpecific}`,
            title: _ctx.translate("PrivacyManager_UseRandomizeConfigId"),
            modelValue: _ctx.actualRandomizeConfigId,
            "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.actualRandomizeConfigId = $event),
            "inline-help": _ctx.randomiseConfigIdHelpText,
            "extra-metadata": _ctx.getExtraMetadataForField("randomizeConfigId")
          }, null, 8, ["name", "title", "modelValue", "inline-help", "extra-metadata"])
        ])
      ], 64)) : vue.createCommentVNode("", true),
      !_ctx.idSiteSpecific ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_12$4, [
        vue.createVNode(_component_SaveButton, {
          onConfirm: _cache[9] || (_cache[9] = ($event) => _ctx.shouldSave()),
          saving: _ctx.isLoading
        }, null, 8, ["saving"])
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmation,
        "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => _ctx.showPasswordConfirmation = $event),
        onConfirmed: _ctx.save,
        onAborted: _ctx.abortPasswordConfirmation
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.passwordConfirmationTitle), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConfirmConfigRandomisationExplanation")), 1)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "onAborted"])
    ])), [
      [_directive_form]
    ]);
  }
  const AnonymizeIp = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$g]]);
  function nearlyWhite(hex) {
    const bigint = parseInt(hex, 16);
    const r = bigint >> 16 & 255;
    const g = bigint >> 8 & 255;
    const b = bigint & 255;
    return r >= 225 && g >= 225 && b >= 225;
  }
  const { $: $$1 } = window;
  const _sfc_main$f = vue.defineComponent({
    props: {
      currentLanguageCode: {
        type: String,
        required: true
      },
      languageOptions: {
        type: Object,
        required: true
      },
      matomoUrl: String
    },
    components: {
      Field: CorePluginsAdmin.Field
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        fontSizeUnit: "px",
        backgroundColor: "#FFFFFF",
        fontColor: "#000000",
        fontSize: "12",
        fontFamily: "Arial",
        showIntro: true,
        applyStyling: false,
        codeType: "tracker",
        code: "",
        language: this.currentLanguageCode
      };
    },
    created() {
      this.onFontColorChange = CoreHome.debounce(this.onFontColorChange, 50);
      this.onBgColorChange = CoreHome.debounce(this.onBgColorChange, 50);
      this.onFontSizeChange = CoreHome.debounce(this.onFontSizeChange, 50);
      this.onFontSizeUnitChange = CoreHome.debounce(this.onFontSizeUnitChange, 50);
      this.onFontFamilyChange = CoreHome.debounce(this.onFontFamilyChange, 50);
      if (this.matomoUrl) {
        this.updateCode();
      }
    },
    methods: {
      onFontColorChange(event) {
        this.fontColor = event.target.value;
        this.updateCode();
      },
      onBgColorChange(event) {
        this.backgroundColor = event.target.value;
        this.updateCode();
      },
      onFontSizeChange(event) {
        this.fontSize = event.target.value;
        this.updateCode();
      },
      onFontSizeUnitChange(event) {
        this.fontSizeUnit = event.target.value;
        this.updateCode();
      },
      onFontFamilyChange(event) {
        this.fontFamily = event.target.value;
        this.updateCode();
      },
      updateCode() {
        let methodName = "CoreAdminHome.getOptOutJSEmbedCode";
        if (this.codeType === "selfContained") {
          methodName = "CoreAdminHome.getOptOutSelfContainedEmbedCode";
        }
        CoreHome.AjaxHelper.fetch({
          method: methodName,
          backgroundColor: this.backgroundColor.substr(1),
          fontColor: this.fontColor.substr(1),
          fontSize: this.fontSizeWithUnit,
          fontFamily: this.fontFamily,
          showIntro: this.showIntro === true ? 1 : 0,
          applyStyling: this.applyStyling === true ? 1 : 0,
          matomoUrl: this.matomoUrl,
          language: this.codeType === "selfContained" ? this.language : "auto"
        }).then((data) => {
          this.code = data.value || "";
        });
      }
    },
    watch: {
      codeBox() {
        const pre = this.$refs.pre;
        const isAnimationAlreadyRunning = $$1(pre).queue("fx").length > 0;
        if (!isAnimationAlreadyRunning) {
          $$1(pre).effect("highlight", {}, 1500);
        }
      }
    },
    computed: {
      fontSizeWithUnit() {
        if (this.fontSize) {
          return `${this.fontSize}${this.fontSizeUnit}`;
        }
        return "";
      },
      withBg() {
        return !!this.matomoUrl && this.backgroundColor === "" && this.fontColor !== "" && nearlyWhite(this.fontColor.slice(1));
      },
      codeBox() {
        if (this.matomoUrl) {
          return this.code;
        }
        return "";
      },
      iframeUrl() {
        const query = CoreHome.MatomoUrl.stringify({
          module: "CoreAdminHome",
          action: "optOut",
          language: this.language,
          backgroundColor: this.backgroundColor.substr(1),
          fontColor: this.fontColor.substr(1),
          fontSize: this.fontSizeWithUnit,
          fontFamily: this.fontFamily,
          applyStyling: this.applyStyling === true ? 1 : 0,
          showIntro: this.showIntro === true ? 1 : 0
        });
        return `${this.matomoUrl}index.php?${query}`;
      },
      usersOptOutIntro() {
        return CoreHome.translate(
          "PrivacyManager_UsersOptOutIntro",
          CoreHome.externalLink("https://matomo.org/faq/how-to/faq_25918/"),
          "</a>"
        );
      },
      optOutExplanationIntro() {
        return CoreHome.translate(
          "PrivacyManager_OptOutExplanationIntro",
          `<a href="${this.iframeUrl}" rel="noreferrer noopener" target="_blank">`,
          "</a>"
        );
      },
      optOutCustomOptOutLink() {
        const link = "https://developer.matomo.org/guides/tracking-javascript-guide#optional-creating-a-custom-opt-out-form";
        return CoreHome.translate(
          "CoreAdminHome_OptOutCustomOptOutLink",
          CoreHome.externalLink(link),
          "</a>"
        );
      },
      codeTypeHelp() {
        return CoreHome.translate("PrivacyManager_OptOutCodeTypeExplanation");
      }
    }
  });
  const _hoisted_1$e = { class: "optOutCustomizer" };
  const _hoisted_2$d = ["innerHTML"];
  const _hoisted_3$c = {
    key: 0,
    id: "opt-out-styling"
  };
  const _hoisted_4$b = ["value"];
  const _hoisted_5$9 = ["value"];
  const _hoisted_6$7 = ["value"];
  const _hoisted_7$6 = ["value"];
  const _hoisted_8$4 = ["value"];
  const _hoisted_9$4 = ["src"];
  const _hoisted_10$4 = { class: "form-group row" };
  const _hoisted_11$3 = { class: "col s12 m6" };
  const _hoisted_12$3 = { for: "codeType1" };
  const _hoisted_13$2 = { for: "codeType2" };
  const _hoisted_14$2 = { key: 0 };
  const _hoisted_15$2 = { class: "col s12 m6" };
  const _hoisted_16$2 = ["innerHTML"];
  const _hoisted_17$2 = { ref: "pre" };
  const _hoisted_18$2 = ["innerHTML"];
  const _hoisted_19$1 = { class: "system notification notification-info optOutTestReminder" };
  const _hoisted_20$1 = ["innerHTML"];
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1$e, [
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.usersOptOutIntro)
        }, null, 8, _hoisted_2$d),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutAppearance")), 1),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("span", null, [
            vue.createElementVNode("label", null, [
              vue.withDirectives(vue.createElementVNode("input", {
                id: "applyStyling",
                type: "checkbox",
                name: "applyStyling",
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.applyStyling = $event),
                onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.updateCode()),
                onChange: _cache[2] || (_cache[2] = ($event) => _ctx.updateCode())
              }, null, 544), [
                [vue.vModelCheckbox, _ctx.applyStyling]
              ]),
              vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ApplyStyling")), 1)
            ])
          ])
        ]),
        _ctx.applyStyling ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$c, [
          vue.createElementVNode("p", null, [
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_FontColor")) + ": ", 1),
              vue.createElementVNode("input", {
                type: "color",
                value: _ctx.fontColor,
                onKeydown: _cache[3] || (_cache[3] = ($event) => _ctx.onFontColorChange($event)),
                onChange: _cache[4] || (_cache[4] = ($event) => _ctx.onFontColorChange($event))
              }, null, 40, _hoisted_4$b)
            ]),
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_BackgroundColor")) + ": ", 1),
              vue.createElementVNode("input", {
                type: "color",
                value: _ctx.backgroundColor,
                onKeydown: _cache[5] || (_cache[5] = ($event) => _ctx.onBgColorChange($event)),
                onChange: _cache[6] || (_cache[6] = ($event) => _ctx.onBgColorChange($event))
              }, null, 40, _hoisted_5$9)
            ]),
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_FontSize")) + ": ", 1),
              vue.createElementVNode("input", {
                id: "FontSizeInput",
                type: "number",
                min: "1",
                max: "100",
                value: _ctx.fontSize,
                onKeydown: _cache[7] || (_cache[7] = ($event) => _ctx.onFontSizeChange($event)),
                onChange: _cache[8] || (_cache[8] = ($event) => _ctx.onFontSizeChange($event))
              }, null, 40, _hoisted_6$7)
            ]),
            vue.createElementVNode("span", null, [
              vue.createElementVNode("select", {
                class: "browser-default",
                value: _ctx.fontSizeUnit,
                onKeydown: _cache[9] || (_cache[9] = ($event) => _ctx.onFontSizeUnitChange($event)),
                onChange: _cache[10] || (_cache[10] = ($event) => _ctx.onFontSizeUnitChange($event))
              }, [..._cache[25] || (_cache[25] = [
                vue.createStaticVNode('<option value="px">px</option><option value="pt">pt</option><option value="em">em</option><option value="rem">rem</option><option value="%">%</option>', 5)
              ])], 40, _hoisted_7$6)
            ]),
            vue.createElementVNode("span", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_FontFamily")) + ": ", 1),
              vue.createElementVNode("input", {
                id: "FontFamilyInput",
                type: "text",
                value: _ctx.fontFamily,
                onKeydown: _cache[11] || (_cache[11] = ($event) => _ctx.onFontFamilyChange($event)),
                onChange: _cache[12] || (_cache[12] = ($event) => _ctx.onFontFamilyChange($event))
              }, null, 40, _hoisted_8$4)
            ])
          ])
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("span", null, [
            vue.createElementVNode("label", null, [
              vue.withDirectives(vue.createElementVNode("input", {
                id: "showIntro",
                type: "checkbox",
                name: "showIntro",
                "onUpdate:modelValue": _cache[13] || (_cache[13] = ($event) => _ctx.showIntro = $event),
                onKeydown: _cache[14] || (_cache[14] = ($event) => _ctx.updateCode()),
                onChange: _cache[15] || (_cache[15] = ($event) => _ctx.updateCode())
              }, null, 544), [
                [vue.vModelCheckbox, _ctx.showIntro]
              ]),
              vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ShowIntro")), 1)
            ])
          ])
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutPreview")), 1),
        vue.createElementVNode("iframe", {
          id: "previewIframe",
          style: { "border": "1px solid #333", "height": "200px", "width": "600px" },
          src: _ctx.iframeUrl,
          class: vue.normalizeClass({ withBg: _ctx.withBg })
        }, null, 10, _hoisted_9$4)
      ]),
      vue.createElementVNode("div", null, [
        vue.createElementVNode("div", _hoisted_10$4, [
          vue.createElementVNode("div", _hoisted_11$3, [
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutHtmlCode")), 1),
            vue.createElementVNode("p", null, [
              vue.createElementVNode("label", _hoisted_12$3, [
                vue.withDirectives(vue.createElementVNode("input", {
                  type: "radio",
                  id: "codeType1",
                  name: "codeType",
                  value: "tracker",
                  "onUpdate:modelValue": _cache[16] || (_cache[16] = ($event) => _ctx.codeType = $event),
                  onKeydown: _cache[17] || (_cache[17] = ($event) => _ctx.updateCode()),
                  onChange: _cache[18] || (_cache[18] = ($event) => _ctx.updateCode())
                }, null, 544), [
                  [vue.vModelRadio, _ctx.codeType]
                ]),
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutUseTracker")), 1)
              ])
            ]),
            vue.createElementVNode("p", null, [
              vue.createElementVNode("label", _hoisted_13$2, [
                vue.withDirectives(vue.createElementVNode("input", {
                  type: "radio",
                  id: "codeType2",
                  name: "codeType",
                  value: "selfContained",
                  "onUpdate:modelValue": _cache[19] || (_cache[19] = ($event) => _ctx.codeType = $event),
                  onKeydown: _cache[20] || (_cache[20] = ($event) => _ctx.updateCode()),
                  onChange: _cache[21] || (_cache[21] = ($event) => _ctx.updateCode())
                }, null, 544), [
                  [vue.vModelRadio, _ctx.codeType]
                ]),
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutUseStandalone")), 1)
              ])
            ]),
            _ctx.codeType === "selfContained" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14$2, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  uicontrol: "select",
                  name: "language",
                  modelValue: _ctx.language,
                  "onUpdate:modelValue": _cache[22] || (_cache[22] = ($event) => _ctx.language = $event),
                  title: _ctx.translate("General_Language"),
                  options: _ctx.languageOptions,
                  onKeydown: _cache[23] || (_cache[23] = ($event) => _ctx.updateCode()),
                  onChange: _cache[24] || (_cache[24] = ($event) => _ctx.updateCode())
                }, null, 8, ["modelValue", "title", "options"])
              ])
            ])) : vue.createCommentVNode("", true)
          ]),
          vue.createElementVNode("div", _hoisted_15$2, [
            vue.createElementVNode("div", {
              class: "form-help",
              innerHTML: _ctx.$sanitize(_ctx.codeTypeHelp)
            }, null, 8, _hoisted_16$2)
          ])
        ])
      ]),
      vue.createElementVNode("div", null, [
        vue.createElementVNode("div", null, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", _hoisted_17$2, [
            vue.createTextVNode("" + vue.toDisplayString(_ctx.codeBox) + "\n      ", 1)
          ])), [
            [_directive_copy_to_clipboard, {}]
          ])
        ]),
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.optOutExplanationIntro)
        }, null, 8, _hoisted_18$2),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutExplanationCookieDeletion")), 1),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutExplanationCookieDeletionCheck")), 1),
        vue.createElementVNode("div", _hoisted_19$1, [
          vue.createElementVNode("p", null, [
            vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTest")), 1)
          ]),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTestBody")), 1),
          vue.createElementVNode("p", null, [
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTestStep1")), 1),
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTestStep2")), 1),
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTestStep3")), 1),
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_OptOutRememberToTestStep4")), 1)
            ])
          ])
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_BuildYourOwn")), 1),
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.optOutCustomOptOutLink)
        }, null, 8, _hoisted_20$1)
      ])
    ], 64);
  }
  const OptOutCustomizer = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  function sub(value) {
    if (value < 10) {
      return `0${value}`;
    }
    return value;
  }
  const _sfc_main$e = vue.defineComponent({
    components: {
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation,
      SiteSelector: CoreHome.SiteSelector,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    data() {
      const now = /* @__PURE__ */ new Date();
      const startDate = `${now.getFullYear()}-${sub(now.getMonth() + 1)}-${sub(now.getDay() + 1)}`;
      return {
        isLoading: false,
        isDeleting: false,
        anonymizeIp: false,
        anonymizeLocation: false,
        anonymizeUserId: false,
        site: {
          id: "all",
          name: "All Websites"
        },
        availableVisitColumns: [],
        availableActionColumns: [],
        selectedVisitColumns: [{
          column: ""
        }],
        selectedActionColumns: [{
          column: ""
        }],
        startDate,
        endDate: startDate,
        showPasswordConfirmModal: false
      };
    },
    created() {
      this.onKeydownStartDate = CoreHome.debounce(this.onKeydownStartDate, 50);
      this.onKeydownEndDate = CoreHome.debounce(this.onKeydownEndDate, 50);
      CoreHome.AjaxHelper.fetch({
        method: "PrivacyManager.getAvailableVisitColumnsToAnonymize"
      }).then((columns) => {
        this.availableVisitColumns = [];
        columns.forEach((column) => {
          this.availableVisitColumns.push({
            key: column.column_name,
            value: column.column_name
          });
        });
      });
      CoreHome.AjaxHelper.fetch({
        method: "PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize"
      }).then((columns) => {
        this.availableActionColumns = [];
        columns.forEach((column) => {
          this.availableActionColumns.push({
            key: column.column_name,
            value: column.column_name
          });
        });
      });
      setTimeout(() => {
        const options1 = CoreHome.Matomo.getBaseDatePickerOptions(null);
        const options2 = CoreHome.Matomo.getBaseDatePickerOptions(null);
        $(this.$refs.anonymizeStartDate).datepicker(options1);
        $(this.$refs.anonymizeEndDate).datepicker(options2);
      });
    },
    methods: {
      onVisitColumnChange() {
        const hasAll = this.selectedVisitColumns.every((col) => !!(col == null ? void 0 : col.column));
        if (hasAll) {
          this.addVisitColumn();
        }
      },
      addVisitColumn() {
        this.selectedVisitColumns.push({ column: "" });
      },
      removeVisitColumn(index) {
        if (index > -1) {
          const lastIndex = this.selectedVisitColumns.length - 1;
          if (lastIndex === index) {
            this.selectedVisitColumns[index] = { column: "" };
          } else {
            this.selectedVisitColumns.splice(index, 1);
          }
        }
      },
      onActionColumnChange() {
        const hasAll = this.selectedActionColumns.every((col) => !!(col == null ? void 0 : col.column));
        if (hasAll) {
          this.addActionColumn();
        }
      },
      addActionColumn() {
        this.selectedActionColumns.push({ column: "" });
      },
      removeActionColumn(index) {
        if (index > -1) {
          const lastIndex = this.selectedActionColumns.length - 1;
          if (lastIndex === index) {
            this.selectedActionColumns[index] = {
              column: ""
            };
          } else {
            this.selectedActionColumns.splice(index, 1);
          }
        }
      },
      scheduleAnonymization(password) {
        let date = `${this.startDate},${this.endDate}`;
        if (this.startDate === this.endDate) {
          date = this.startDate;
        }
        const params = { date };
        params.idSites = this.site.id;
        params.anonymizeIp = this.anonymizeIp ? "1" : "0";
        params.anonymizeLocation = this.anonymizeLocation ? "1" : "0";
        params.anonymizeUserId = this.anonymizeUserId ? "1" : "0";
        params.unsetVisitColumns = this.selectedVisitColumns.filter(
          (c) => !!(c == null ? void 0 : c.column)
        ).map((c) => c.column);
        params.unsetLinkVisitActionColumns = this.selectedActionColumns.filter(
          (c) => !!(c == null ? void 0 : c.column)
        ).map((c) => c.column);
        params.passwordConfirmation = password;
        CoreHome.AjaxHelper.post({
          method: "PrivacyManager.anonymizeSomeRawData"
        }, params).then(() => {
          window.location.reload(true);
        });
      },
      onKeydownStartDate(event) {
        this.startDate = event.target.value;
      },
      onKeydownEndDate(event) {
        this.endDate = event.target.value;
      }
    },
    computed: {
      isAnonymizePastDataDisabled() {
        return !this.anonymizeIp && !this.anonymizeLocation && !this.selectedVisitColumns && !this.selectedActionColumns;
      }
    }
  });
  const _hoisted_1$d = { class: "anonymizeLogData" };
  const _hoisted_2$c = { class: "form-group row" };
  const _hoisted_3$b = { class: "col s12 input-field" };
  const _hoisted_4$a = {
    for: "anonymizeSite",
    class: "siteSelectorLabel"
  };
  const _hoisted_5$8 = { class: "sites_autocomplete" };
  const _hoisted_6$6 = { class: "form-group row" };
  const _hoisted_7$5 = { class: "col s6 input-field" };
  const _hoisted_8$3 = {
    for: "anonymizeStartDate",
    class: "active"
  };
  const _hoisted_9$3 = ["value"];
  const _hoisted_10$3 = { class: "col s6 input-field" };
  const _hoisted_11$2 = {
    for: "anonymizeEndDate",
    class: "active"
  };
  const _hoisted_12$2 = ["value"];
  const _hoisted_13$1 = { name: "anonymizeIp" };
  const _hoisted_14$1 = { name: "anonymizeLocation" };
  const _hoisted_15$1 = { name: "anonymizeTheUserId" };
  const _hoisted_16$1 = { class: "form-group row" };
  const _hoisted_17$1 = { class: "col s12 m6" };
  const _hoisted_18$1 = { for: "visit_columns" };
  const _hoisted_19 = {
    class: "innerFormField",
    name: "visit_columns"
  };
  const _hoisted_20 = ["onClick", "title"];
  const _hoisted_21 = { class: "col s12 m6" };
  const _hoisted_22 = { class: "form-help" };
  const _hoisted_23 = { class: "inline-help" };
  const _hoisted_24 = { class: "form-group row" };
  const _hoisted_25 = { class: "col s12" };
  const _hoisted_26 = { class: "form-group row" };
  const _hoisted_27 = { class: "col s12 m6" };
  const _hoisted_28 = { for: "action_columns" };
  const _hoisted_29 = {
    class: "innerFormField",
    name: "action_columns"
  };
  const _hoisted_30 = ["onClick", "title"];
  const _hoisted_31 = { class: "col s12 m6" };
  const _hoisted_32 = { class: "form-help" };
  const _hoisted_33 = { class: "inline-help" };
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SiteSelector = vue.resolveComponent("SiteSelector");
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$d, [
      vue.createElementVNode("div", _hoisted_2$c, [
        vue.createElementVNode("div", _hoisted_3$b, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("label", _hoisted_4$a, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeSites")), 1),
            vue.createElementVNode("div", _hoisted_5$8, [
              vue.createVNode(_component_SiteSelector, {
                id: "anonymizeSite",
                modelValue: _ctx.site,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.site = $event),
                "show-all-sites-item": true,
                "switch-site-on-select": false,
                "show-selected-site": true
              }, null, 8, ["modelValue"])
            ])
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_6$6, [
        vue.createElementVNode("div", _hoisted_7$5, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("label", _hoisted_8$3, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeRowDataFrom")), 1),
            vue.createElementVNode("input", {
              type: "text",
              id: "anonymizeStartDate",
              class: "anonymizeStartDate",
              ref: "anonymizeStartDate",
              name: "anonymizeStartDate",
              value: _ctx.startDate,
              onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydownStartDate($event)),
              onChange: _cache[2] || (_cache[2] = ($event) => _ctx.onKeydownStartDate($event))
            }, null, 40, _hoisted_9$3)
          ])
        ]),
        vue.createElementVNode("div", _hoisted_10$3, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("label", _hoisted_11$2, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeRowDataTo")), 1),
            vue.createElementVNode("input", {
              type: "text",
              class: "anonymizeEndDate",
              id: "anonymizeEndDate",
              ref: "anonymizeEndDate",
              name: "anonymizeEndDate",
              value: _ctx.endDate,
              onKeydown: _cache[3] || (_cache[3] = ($event) => _ctx.onKeydownEndDate($event)),
              onChange: _cache[4] || (_cache[4] = ($event) => _ctx.onKeydownEndDate($event))
            }, null, 40, _hoisted_12$2)
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_13$1, [
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "anonymizeIp",
          title: _ctx.translate("PrivacyManager_AnonymizeIp"),
          modelValue: _ctx.anonymizeIp,
          "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.anonymizeIp = $event),
          introduction: _ctx.translate("General_Visit"),
          "inline-help": _ctx.translate("PrivacyManager_AnonymizeIpHelp")
        }, null, 8, ["title", "modelValue", "introduction", "inline-help"])
      ]),
      vue.createElementVNode("div", _hoisted_14$1, [
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "anonymizeLocation",
          title: _ctx.translate("PrivacyManager_AnonymizeLocation"),
          modelValue: _ctx.anonymizeLocation,
          "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.anonymizeLocation = $event),
          "inline-help": _ctx.translate("PrivacyManager_AnonymizeLocationHelp")
        }, null, 8, ["title", "modelValue", "inline-help"])
      ]),
      vue.createElementVNode("div", _hoisted_15$1, [
        vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "anonymizeTheUserId",
          title: _ctx.translate("PrivacyManager_AnonymizeUserId"),
          modelValue: _ctx.anonymizeUserId,
          "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.anonymizeUserId = $event),
          "inline-help": _ctx.translate("PrivacyManager_AnonymizeUserIdHelp")
        }, null, 8, ["title", "modelValue", "inline-help"])
      ]),
      vue.createElementVNode("div", _hoisted_16$1, [
        vue.createElementVNode("div", _hoisted_17$1, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("label", _hoisted_18$1, vue.toDisplayString(_ctx.translate("PrivacyManager_UnsetVisitColumns")), 1),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectedVisitColumns, (visitColumn, index) => {
              return vue.openBlock(), vue.createElementBlock("div", {
                class: vue.normalizeClass(`selectedVisitColumns selectedVisitColumns${index} multiple valign-wrapper`),
                key: index
              }, [
                vue.createElementVNode("div", _hoisted_19, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "select",
                    name: "visit_columns",
                    "model-value": visitColumn.column,
                    "onUpdate:modelValue": ($event) => {
                      visitColumn.column = $event;
                      _ctx.onVisitColumnChange();
                    },
                    "full-width": true,
                    options: _ctx.availableVisitColumns
                  }, null, 8, ["model-value", "onUpdate:modelValue", "options"])
                ]),
                vue.withDirectives(vue.createElementVNode("span", {
                  class: "icon-minus valign",
                  onClick: ($event) => _ctx.removeVisitColumn(index),
                  title: _ctx.translate("General_Remove")
                }, null, 8, _hoisted_20), [
                  [vue.vShow, index + 1 !== _ctx.selectedVisitColumns.length]
                ])
              ], 2);
            }), 128))
          ])
        ]),
        vue.createElementVNode("div", _hoisted_21, [
          vue.createElementVNode("div", _hoisted_22, [
            vue.createElementVNode("span", _hoisted_23, vue.toDisplayString(_ctx.translate("PrivacyManager_UnsetVisitColumnsHelp")), 1)
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_24, [
        vue.createElementVNode("div", _hoisted_25, [
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("General_Action")), 1)
        ])
      ]),
      vue.createElementVNode("div", _hoisted_26, [
        vue.createElementVNode("div", _hoisted_27, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("label", _hoisted_28, vue.toDisplayString(_ctx.translate("PrivacyManager_UnsetActionColumns")), 1),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectedActionColumns, (actionColumn, index) => {
              return vue.openBlock(), vue.createElementBlock("div", {
                class: vue.normalizeClass(`selectedActionColumns selectedActionColumns${index} multiple valign-wrapper`),
                key: index
              }, [
                vue.createElementVNode("div", _hoisted_29, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "select",
                    name: "action_columns",
                    "model-value": actionColumn.column,
                    "onUpdate:modelValue": ($event) => {
                      actionColumn.column = $event;
                      _ctx.onActionColumnChange();
                    },
                    "full-width": true,
                    options: _ctx.availableActionColumns
                  }, null, 8, ["model-value", "onUpdate:modelValue", "options"])
                ]),
                vue.withDirectives(vue.createElementVNode("span", {
                  class: "icon-minus valign",
                  onClick: ($event) => _ctx.removeActionColumn(index),
                  title: _ctx.translate("General_Remove")
                }, null, 8, _hoisted_30), [
                  [vue.vShow, index + 1 !== _ctx.selectedActionColumns.length]
                ])
              ], 2);
            }), 128))
          ])
        ]),
        vue.createElementVNode("div", _hoisted_31, [
          vue.createElementVNode("div", _hoisted_32, [
            vue.createElementVNode("span", _hoisted_33, vue.toDisplayString(_ctx.translate("PrivacyManager_UnsetActionColumnsHelp")), 1)
          ])
        ])
      ]),
      vue.createElementVNode("p", null, [
        _cache[10] || (_cache[10] = vue.createElementVNode("span", { class: "icon-info" }, null, -1)),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeProcessInfo")), 1)
      ]),
      vue.createVNode(_component_SaveButton, {
        class: "anonymizePastData",
        onConfirm: _cache[8] || (_cache[8] = ($event) => _ctx.showPasswordConfirmModal = true),
        disabled: _ctx.isAnonymizePastDataDisabled,
        value: _ctx.translate("PrivacyManager_AnonymizeDataNow")
      }, null, 8, ["disabled", "value"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModal,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.showPasswordConfirmModal = $event),
        onConfirmed: _ctx.scheduleAnonymization
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeDataConfirm")), 1)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed"])
    ]);
  }
  const AnonymizeLogData = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
  const _sfc_main$d = vue.defineComponent({
    props: {
      dntSupport: Boolean,
      doNotTrackOptions: {
        type: Array,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isLoading: false,
        enabled: this.dntSupport ? 1 : 0
      };
    },
    methods: {
      save() {
        this.isLoading = true;
        let action = "deactivateDoNotTrack";
        if (this.enabled && this.enabled !== "0") {
          action = "activateDoNotTrack";
        }
        CoreHome.AjaxHelper.post({
          module: "API",
          method: `PrivacyManager.${action}`
        }).then(() => {
          const notificationInstanceId = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            context: "success",
            id: "privacyManagerSettings",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
        }).finally(() => {
          this.isLoading = false;
        });
      }
    }
  });
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _directive_form = vue.resolveDirective("form");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "radio",
          name: "doNotTrack",
          modelValue: _ctx.enabled,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.enabled = $event),
          options: _ctx.doNotTrackOptions,
          "inline-help": _ctx.translate("PrivacyManager_DoNotTrack_Description")
        }, null, 8, ["modelValue", "options", "inline-help"])
      ]),
      vue.createVNode(_component_SaveButton, {
        onConfirm: _cache[1] || (_cache[1] = ($event) => _ctx.save()),
        saving: _ctx.isLoading
      }, null, 8, ["saving"])
    ])), [
      [_directive_form]
    ]);
  }
  const DoNotTrackPreference = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class ReportDeletionSettingsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        settings: {},
        showEstimate: false,
        loadingEstimation: false,
        estimation: "",
        isModified: false
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "enableDeleteReports", vue.computed(() => this.state.value.settings.enableDeleteReports));
      __publicField(this, "enableDeleteLogs", vue.computed(() => this.state.value.settings.enableDeleteLogs));
      __publicField(this, "currentRequest");
    }
    updateSettings(settings) {
      this.initSettings(settings);
      this.privateState.isModified = true;
    }
    initSettings(settings) {
      this.privateState.settings = __spreadValues(__spreadValues({}, this.privateState.settings), settings);
      this.reloadDbStats();
    }
    savePurgeDataSettings(apiMethod, settings, password) {
      this.privateState.isModified = false;
      return CoreHome.AjaxHelper.post(
        {
          module: "API",
          method: apiMethod
        },
        __spreadProps(__spreadValues({}, settings), {
          enableDeleteLogs: settings.enableDeleteLogs ? "1" : "0",
          enableDeleteReports: settings.enableDeleteReports ? "1" : "0",
          passwordConfirmation: password
        })
      ).then(() => {
        const notificationInstanceId = CoreHome.NotificationsStore.show({
          message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
          context: "success",
          id: "privacyManagerSettings",
          type: "toast"
        });
        CoreHome.NotificationsStore.scrollToNotification(notificationInstanceId);
      });
    }
    isEitherDeleteSectionEnabled() {
      return this.state.value.settings.enableDeleteLogs || this.state.value.settings.enableDeleteReports;
    }
    isManualEstimationLinkShowing() {
      return window.$("#getPurgeEstimateLink").length > 0;
    }
    reloadDbStats(forceEstimate) {
      if (this.currentRequest) {
        this.currentRequest.abort();
        this.currentRequest = void 0;
      }
      if (!forceEstimate && (!this.isEitherDeleteSectionEnabled() || this.isManualEstimationLinkShowing())) {
        return;
      }
      this.privateState.loadingEstimation = true;
      this.privateState.estimation = "";
      this.privateState.showEstimate = false;
      const { settings } = this.privateState;
      const formData = __spreadProps(__spreadValues({}, settings), {
        enableDeleteLogs: settings.enableDeleteLogs ? "1" : "0",
        enableDeleteReports: settings.enableDeleteReports ? "1" : "0"
      });
      if (forceEstimate === true) {
        formData.forceEstimate = 1;
      }
      this.currentRequest = new AbortController();
      CoreHome.AjaxHelper.post(
        {
          module: "PrivacyManager",
          action: "getDatabaseSize",
          format: "html"
        },
        formData,
        { abortController: this.currentRequest, format: "html" }
      ).then((data) => {
        this.privateState.estimation = data;
        this.privateState.showEstimate = true;
        this.privateState.loadingEstimation = false;
      }).finally(() => {
        this.currentRequest = void 0;
        this.privateState.loadingEstimation = false;
      });
    }
  }
  const ReportDeletionSettingsStore$1 = new ReportDeletionSettingsStore();
  const _sfc_main$c = vue.defineComponent({
    props: {
      isDataPurgeSettingsEnabled: Boolean,
      deleteData: {
        type: Object,
        required: true
      },
      scheduleDeletionOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isLoading: false,
        enabled: parseInt(this.deleteData.config.delete_logs_enable, 10) === 1,
        deleteOlderThan: this.deleteData.config.delete_logs_older_than,
        showPasswordConfirmModal: false
      };
    },
    created() {
      setTimeout(() => {
        ReportDeletionSettingsStore$1.initSettings(this.settings);
      });
    },
    methods: {
      saveSettings(password) {
        const method = "PrivacyManager.setDeleteLogsSettings";
        this.isLoading = true;
        ReportDeletionSettingsStore$1.savePurgeDataSettings(method, this.settings, password).finally(() => {
          this.isLoading = false;
        });
      },
      reloadDbStats() {
        ReportDeletionSettingsStore$1.updateSettings(this.settings);
      }
    },
    computed: {
      settings() {
        return {
          enableDeleteLogs: !!this.enabled,
          deleteLogsOlderThan: this.deleteOlderThan
        };
      },
      deleteOlderThanTitle() {
        return `${CoreHome.translate("PrivacyManager_DeleteLogsOlderThan")} (${CoreHome.translate("Intl_PeriodDays")})`;
      },
      enableDeleteReports() {
        return !!ReportDeletionSettingsStore$1.enableDeleteReports.value;
      }
    }
  });
  const _hoisted_1$c = { id: "formDeleteSettings" };
  const _hoisted_2$b = { id: "deleteLogSettingEnabled" };
  const _hoisted_3$a = {
    class: "alert alert-warning deleteOldLogsWarning",
    style: { "width": "50%" }
  };
  const _hoisted_4$9 = ["href"];
  const _hoisted_5$7 = { id: "deleteLogSettings" };
  const _hoisted_6$5 = { key: 0 };
  const _hoisted_7$4 = { key: 1 };
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _directive_form = vue.resolveDirective("form");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$c, [
      vue.createElementVNode("div", _hoisted_2$b, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "deleteEnable",
            "model-value": _ctx.enabled,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => {
              _ctx.enabled = $event;
              _ctx.reloadDbStats();
            }),
            title: _ctx.translate("PrivacyManager_UseDeleteLog"),
            "inline-help": _ctx.translate("PrivacyManager_DeleteRawDataInfo")
          }, null, 8, ["model-value", "title", "inline-help"])
        ]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_3$a, [
          vue.createElementVNode("a", {
            href: _ctx.externalRawLink("https://matomo.org/faq/general/faq_125"),
            rel: "noreferrer noopener",
            target: "_blank"
          }, vue.toDisplayString(_ctx.translate("General_ClickHere")), 9, _hoisted_4$9)
        ], 512), [
          [vue.vShow, _ctx.enabled]
        ])
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_5$7, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "deleteOlderThan",
            "model-value": _ctx.deleteOlderThan,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => {
              _ctx.deleteOlderThan = $event;
              _ctx.reloadDbStats();
            }),
            title: _ctx.deleteOlderThanTitle,
            "inline-help": _ctx.translate("PrivacyManager_LeastDaysInput", "1")
          }, null, 8, ["model-value", "title", "inline-help"])
        ])
      ], 512), [
        [vue.vShow, _ctx.enabled]
      ]),
      vue.createVNode(_component_SaveButton, {
        onConfirm: _cache[2] || (_cache[2] = ($event) => this.showPasswordConfirmModal = true),
        saving: _ctx.isLoading
      }, null, 8, ["saving"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModal,
        "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.showPasswordConfirmModal = $event),
        onConfirmed: _ctx.saveSettings
      }, {
        default: vue.withCtx(() => [
          _ctx.enabled && !_ctx.enableDeleteReports ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_6$5, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteLogsConfirm")), 1)) : vue.createCommentVNode("", true),
          _ctx.enabled && _ctx.enableDeleteReports ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_7$4, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteBothConfirm")), 1)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed"])
    ])), [
      [_directive_form]
    ]);
  }
  const DeleteOldLogs = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  function getInt(value) {
    return value ? "1" : "0";
  }
  const _sfc_main$b = vue.defineComponent({
    props: {
      isDataPurgeSettingsEnabled: Boolean,
      deleteData: {
        type: Object,
        required: true
      },
      scheduleDeletionOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isLoading: false,
        enabled: parseInt(this.deleteData.config.delete_reports_enable, 10) === 1,
        deleteOlderThan: this.deleteData.config.delete_reports_older_than,
        keepBasic: parseInt(this.deleteData.config.delete_reports_keep_basic_metrics, 10) === 1,
        keepDataForDay: parseInt(this.deleteData.config.delete_reports_keep_day_reports, 10) === 1,
        keepDataForWeek: parseInt(this.deleteData.config.delete_reports_keep_week_reports, 10) === 1,
        keepDataForMonth: parseInt(
          this.deleteData.config.delete_reports_keep_month_reports,
          10
        ) === 1,
        keepDataForYear: parseInt(this.deleteData.config.delete_reports_keep_year_reports, 10) === 1,
        keepDataForRange: parseInt(
          this.deleteData.config.delete_reports_keep_range_reports,
          10
        ) === 1,
        keepDataForSegments: parseInt(
          this.deleteData.config.delete_reports_keep_segment_reports,
          10
        ) === 1,
        showPasswordConfirmModal: false
      };
    },
    created() {
      setTimeout(() => {
        ReportDeletionSettingsStore$1.initSettings(this.settings);
      });
    },
    methods: {
      saveSettings(password) {
        const method = "PrivacyManager.setDeleteReportsSettings";
        this.isLoading = true;
        ReportDeletionSettingsStore$1.savePurgeDataSettings(method, this.settings, password).finally(() => {
          this.isLoading = false;
        });
      },
      reloadDbStats() {
        ReportDeletionSettingsStore$1.updateSettings(this.settings);
      }
    },
    computed: {
      settings() {
        return {
          enableDeleteReports: this.enabled,
          deleteReportsOlderThan: this.deleteOlderThan,
          keepBasic: getInt(this.keepBasic),
          keepDay: getInt(this.keepDataForDay),
          keepWeek: getInt(this.keepDataForWeek),
          keepMonth: getInt(this.keepDataForMonth),
          keepYear: getInt(this.keepDataForYear),
          keepRange: getInt(this.keepDataForRange),
          keepSegments: getInt(this.keepDataForSegments)
        };
      },
      deleteOldLogsText() {
        return CoreHome.translate("PrivacyManager_UseDeleteLog");
      },
      deleteReportsOlderThanTitle() {
        const first = CoreHome.translate("PrivacyManager_DeleteReportsOlderThan");
        return `${first} (${CoreHome.translate("Intl_PeriodMonths")})`;
      },
      deleteReportsKeepBasicTitle() {
        const first = CoreHome.translate("PrivacyManager_KeepBasicMetrics");
        return `${first} (${CoreHome.translate("General_Recommended")})`;
      },
      enableDeleteLogs() {
        return !!ReportDeletionSettingsStore$1.enableDeleteLogs.value;
      }
    }
  });
  const _hoisted_1$b = { id: "formDeleteSettings" };
  const _hoisted_2$a = { id: "deleteReportsSettingEnabled" };
  const _hoisted_3$9 = {
    class: "alert alert-warning",
    style: { "width": "50%" }
  };
  const _hoisted_4$8 = { id: "deleteReportsSettings" };
  const _hoisted_5$6 = { key: 0 };
  const _hoisted_6$4 = { key: 1 };
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _directive_form = vue.resolveDirective("form");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$b, [
      vue.createElementVNode("div", _hoisted_2$a, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "deleteReportsEnable",
            "model-value": _ctx.enabled,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => {
              _ctx.enabled = $event;
              _ctx.reloadDbStats();
            }),
            title: _ctx.translate("PrivacyManager_UseDeleteReports"),
            "inline-help": _ctx.translate("PrivacyManager_DeleteAggregateReportsDetailedInfo")
          }, null, 8, ["model-value", "title", "inline-help"])
        ]),
        vue.withDirectives(vue.createElementVNode("div", _hoisted_3$9, [
          vue.createElementVNode("span", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteReportsInfo2", _ctx.deleteOldLogsText)), 1),
            _cache[11] || (_cache[11] = vue.createElementVNode("br", null, null, -1)),
            _cache[12] || (_cache[12] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteReportsInfo3", _ctx.deleteOldLogsText)), 1)
          ])
        ], 512), [
          [vue.vShow, _ctx.enabled]
        ])
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_4$8, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "deleteReportsOlderThan",
            "model-value": _ctx.deleteOlderThan,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => {
              _ctx.deleteOlderThan = $event;
              _ctx.reloadDbStats();
            }),
            title: _ctx.deleteReportsOlderThanTitle,
            "inline-help": _ctx.translate("PrivacyManager_LeastMonthsInput", "1")
          }, null, 8, ["model-value", "title", "inline-help"])
        ]),
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "deleteReportsKeepBasic",
            "model-value": _ctx.keepBasic,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => {
              _ctx.keepBasic = $event;
              _ctx.reloadDbStats();
            }),
            title: _ctx.deleteReportsKeepBasicTitle,
            "inline-help": _ctx.translate("PrivacyManager_KeepBasicMetricsReportsDetailedInfo")
          }, null, 8, ["model-value", "title", "inline-help"])
        ]),
        vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_KeepDataFor")), 1),
        vue.createElementVNode("div", null, [
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepDay",
              "model-value": _ctx.keepDataForDay,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
                _ctx.keepDataForDay = $event;
                _ctx.reloadDbStats();
              }),
              title: _ctx.translate("General_DailyReports")
            }, null, 8, ["model-value", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepWeek",
              "model-value": _ctx.keepDataForWeek,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
                _ctx.keepDataForWeek = $event;
                _ctx.reloadDbStats();
              }),
              title: _ctx.translate("General_WeeklyReports")
            }, null, 8, ["model-value", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepMonth",
              "model-value": _ctx.keepDataForMonth,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => {
                _ctx.keepDataForMonth = $event;
                _ctx.reloadDbStats();
              }),
              title: `${_ctx.translate("General_MonthlyReports")} (${_ctx.translate("General_Recommended")})`
            }, null, 8, ["model-value", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepYear",
              "model-value": _ctx.keepDataForYear,
              "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => {
                _ctx.keepDataForYear = $event;
                _ctx.reloadDbStats();
              }),
              title: `${_ctx.translate("General_YearlyReports")} (${_ctx.translate("General_Recommended")})`
            }, null, 8, ["model-value", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepRange",
              "model-value": _ctx.keepDataForRange,
              "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => {
                _ctx.keepDataForRange = $event;
                _ctx.reloadDbStats();
              }),
              title: _ctx.translate("General_RangeReports")
            }, null, 8, ["model-value", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "deleteReportsKeepSegments",
              "model-value": _ctx.keepDataForSegments,
              "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => {
                _ctx.keepDataForSegments = $event;
                _ctx.reloadDbStats();
              }),
              title: _ctx.translate("PrivacyManager_KeepReportSegments")
            }, null, 8, ["model-value", "title"])
          ])
        ])
      ], 512), [
        [vue.vShow, _ctx.enabled]
      ]),
      vue.createVNode(_component_SaveButton, {
        onConfirm: _cache[9] || (_cache[9] = ($event) => this.showPasswordConfirmModal = true),
        saving: _ctx.isLoading
      }, null, 8, ["saving"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmModal,
        "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => _ctx.showPasswordConfirmModal = $event),
        onConfirmed: _ctx.saveSettings
      }, {
        default: vue.withCtx(() => [
          _ctx.enabled && !_ctx.enableDeleteLogs ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_5$6, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteReportsConfirm")), 1)) : vue.createCommentVNode("", true),
          _ctx.enabled && _ctx.enableDeleteLogs ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_6$4, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteBothConfirm")), 1)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed"])
    ])), [
      [_directive_form]
    ]);
  }
  const DeleteOldReports = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  const _sfc_main$a = vue.defineComponent({
    props: {
      isDataPurgeSettingsEnabled: Boolean,
      deleteData: {
        type: Object,
        required: true
      },
      scheduleDeletionOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      ActivityIndicator: CoreHome.ActivityIndicator,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        isLoading: false,
        loadingDataPurge: false,
        dataWasPurged: false,
        showPurgeNowLink: true,
        deleteLowestInterval: this.deleteData.config.delete_logs_schedule_lowest_interval,
        showPasswordConfirmModal: false,
        showPasswordConfirmModalForPurge: false
      };
    },
    methods: {
      save(password) {
        const method = "PrivacyManager.setScheduleReportDeletionSettings";
        ReportDeletionSettingsStore$1.savePurgeDataSettings(method, {
          deleteLowestInterval: this.deleteLowestInterval
        }, password);
      },
      executeDataPurge() {
        if (ReportDeletionSettingsStore$1.state.value.isModified) {
          CoreHome.Matomo.helper.modalConfirm("#saveSettingsBeforePurge", {
            yes: () => null
          });
          return;
        }
        this.showPasswordConfirmModalForPurge = true;
      },
      getPurgeEstimate() {
        return ReportDeletionSettingsStore$1.reloadDbStats(true);
      },
      executePurgeNow(password) {
        this.loadingDataPurge = true;
        this.showPurgeNowLink = false;
        return CoreHome.AjaxHelper.post(
          {
            module: "API",
            method: "PrivacyManager.executeDataPurge"
          },
          {
            passwordConfirmation: password
          }
        ).then(() => {
          ReportDeletionSettingsStore$1.reloadDbStats();
          this.dataWasPurged = true;
          setTimeout(() => {
            this.dataWasPurged = false;
            this.showPurgeNowLink = true;
          }, 2e3);
        }).catch(() => {
          this.showPurgeNowLink = true;
        }).finally(() => {
          this.loadingDataPurge = false;
        });
      }
    },
    computed: {
      showEstimate() {
        return ReportDeletionSettingsStore$1.state.value.showEstimate;
      },
      isEitherDeleteSectionEnabled() {
        return ReportDeletionSettingsStore$1.isEitherDeleteSectionEnabled();
      },
      estimation() {
        return ReportDeletionSettingsStore$1.state.value.estimation;
      },
      loadingEstimation() {
        return ReportDeletionSettingsStore$1.state.value.loadingEstimation;
      }
    }
  });
  const _hoisted_1$a = { id: "formDeleteSettings" };
  const _hoisted_2$9 = { id: "deleteSchedulingSettings" };
  const _hoisted_3$8 = {
    id: "deleteSchedulingSettingsInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_4$7 = { key: 0 };
  const _hoisted_5$5 = {
    key: 0,
    id: "deleteDataEstimateSect",
    class: "form-group row"
  };
  const _hoisted_6$3 = {
    class: "col s12",
    id: "databaseSizeHeadline"
  };
  const _hoisted_7$3 = { class: "col s12 m6" };
  const _hoisted_8$2 = ["innerHTML"];
  const _hoisted_9$2 = { class: "col s12 m6" };
  const _hoisted_10$2 = {
    key: 0,
    class: "form-help"
  };
  const _hoisted_11$1 = {
    class: "ui-confirm",
    id: "saveSettingsBeforePurge"
  };
  const _hoisted_12$1 = ["value"];
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$a, [
        vue.withDirectives(vue.createVNode(_component_ContentBlock, {
          id: "scheduleSettingsHeadline",
          "content-title": _ctx.translate("PrivacyManager_DeleteSchedulingSettings")
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_2$9, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  uicontrol: "select",
                  name: "deleteLowestInterval",
                  title: _ctx.translate("PrivacyManager_DeleteDataInterval"),
                  modelValue: _ctx.deleteLowestInterval,
                  "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.deleteLowestInterval = $event),
                  options: _ctx.scheduleDeletionOptions
                }, {
                  "inline-help": vue.withCtx(() => [
                    vue.createElementVNode("div", _hoisted_3$8, [
                      _ctx.deleteData.lastRun ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$7, [
                        vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_LastDelete")) + ":", 1),
                        vue.createTextVNode(" " + vue.toDisplayString(_ctx.deleteData.lastRunPretty) + " ", 1),
                        _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1)),
                        _cache[7] || (_cache[7] = vue.createElementVNode("br", null, null, -1))
                      ])) : vue.createCommentVNode("", true),
                      vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_NextDelete")) + ":", 1),
                      vue.createTextVNode(" " + vue.toDisplayString(_ctx.deleteData.nextRunPretty) + " ", 1),
                      _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
                      _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1)),
                      vue.withDirectives(vue.createElementVNode("a", {
                        id: "purgeDataNowLink",
                        href: "#",
                        onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.executeDataPurge(), ["prevent"]))
                      }, vue.toDisplayString(_ctx.translate("PrivacyManager_PurgeNow")), 513), [
                        [vue.vShow, _ctx.showPurgeNowLink]
                      ]),
                      vue.createVNode(_component_ActivityIndicator, {
                        "loading-message": _ctx.translate("PrivacyManager_PurgingData"),
                        loading: _ctx.loadingDataPurge
                      }, null, 8, ["loading-message", "loading"]),
                      vue.withDirectives(vue.createElementVNode("span", { id: "db-purged-message" }, vue.toDisplayString(_ctx.translate("PrivacyManager_DBPurged")), 513), [
                        [vue.vShow, _ctx.dataWasPurged]
                      ])
                    ])
                  ]),
                  _: 1
                }, 8, ["title", "modelValue", "options"])
              ])
            ]),
            _ctx.deleteData.config.enable_database_size_estimate === "1" || _ctx.deleteData.config.enable_database_size_estimate === 1 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$5, [
              vue.createElementVNode("h3", _hoisted_6$3, vue.toDisplayString(_ctx.translate("PrivacyManager_ReportsDataSavedEstimate")), 1),
              vue.createElementVNode("div", _hoisted_7$3, [
                vue.withDirectives(vue.createElementVNode("div", {
                  id: "deleteDataEstimate",
                  innerHTML: _ctx.$sanitize(_ctx.estimation)
                }, null, 8, _hoisted_8$2), [
                  [vue.vShow, _ctx.showEstimate]
                ]),
                _cache[10] || (_cache[10] = vue.createTextVNode(" ", -1)),
                vue.createVNode(_component_ActivityIndicator, { loading: _ctx.loadingEstimation }, null, 8, ["loading"])
              ]),
              vue.createElementVNode("div", _hoisted_9$2, [
                _ctx.deleteData.config.enable_auto_database_size_estimate !== "1" && _ctx.deleteData.config.enable_auto_database_size_estimate !== 1 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_10$2, [
                  vue.createElementVNode("a", {
                    id: "getPurgeEstimateLink",
                    href: "#",
                    onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => _ctx.getPurgeEstimate(), ["prevent"]))
                  }, vue.toDisplayString(_ctx.translate("PrivacyManager_GetPurgeEstimate")), 1)
                ])) : vue.createCommentVNode("", true)
              ])
            ])) : vue.createCommentVNode("", true),
            vue.createVNode(_component_SaveButton, {
              onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.showPasswordConfirmModal = true),
              saving: _ctx.isLoading
            }, null, 8, ["saving"]),
            vue.createVNode(_component_PasswordConfirmation, {
              modelValue: _ctx.showPasswordConfirmModal,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.showPasswordConfirmModal = $event),
              onConfirmed: _ctx.save
            }, null, 8, ["modelValue", "onConfirmed"]),
            vue.createVNode(_component_PasswordConfirmation, {
              modelValue: _ctx.showPasswordConfirmModalForPurge,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.showPasswordConfirmModalForPurge = $event),
              onConfirmed: _ctx.executePurgeNow
            }, {
              default: vue.withCtx(() => [
                vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_PurgeNowConfirm")), 1)
              ]),
              _: 1
            }, 8, ["modelValue", "onConfirmed"])
          ]),
          _: 1
        }, 8, ["content-title"]), [
          [vue.vShow, _ctx.isEitherDeleteSectionEnabled]
        ])
      ])), [
        [_directive_form]
      ]),
      vue.createElementVNode("div", _hoisted_11$1, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_SaveSettingsBeforePurge")), 1),
        vue.createElementVNode("input", {
          role: "yes",
          type: "button",
          value: _ctx.translate("General_Ok")
        }, null, 8, _hoisted_12$1)
      ])
    ], 64);
  }
  const ScheduleReportDeletion = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const _sfc_main$9 = vue.defineComponent({
    props: {
      consentManagerName: {
        type: String,
        required: true
      },
      consentManagerUrl: {
        type: String,
        required: true
      },
      consentManagerIsConnected: {
        type: Boolean,
        required: true
      },
      consentManagers: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      consentManagementPlatformsOutro() {
        return CoreHome.translate(
          "PrivacyManager_ConsentManagementPlatformsOutro",
          CoreHome.externalLink("https://developer.matomo.org/guides/tracking-consent"),
          "</a>"
        );
      },
      consentManagersList() {
        let list = "";
        Object.entries(this.consentManagers).forEach(([name, url]) => {
          const u = CoreHome.externalRawLink(url);
          list += `<li>  <a href="${u}"     target="_blank" rel="noreferrer noopener">    ${name} ${CoreHome.translate("PrivacyManager_ConsentManager")}  </a></li>`;
        });
        return list;
      },
      consentManagerDetectedText() {
        return CoreHome.translate(
          "PrivacyManager_ConsentManagerDetected",
          this.consentManagerName,
          `<a href="${this.consentManagerUrl}" target="_blank" rel="noreferrer noopener">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$9 = ["innerHTML"];
  const _hoisted_2$8 = ["innerHTML"];
  const _hoisted_3$7 = ["innerHTML"];
  const _hoisted_4$6 = ["innerHTML"];
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AskingForConsent")), 1)
      ])), [
        [_directive_content_intro]
      ]),
      _ctx.consentManagerName ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
        key: 0,
        "content-title": _ctx.translate("PrivacyManager_ConsentManager"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.consentManagerDetectedText)
          }, null, 8, _hoisted_1$9),
          _ctx.consentManagerIsConnected ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.translate("PrivacyManager_ConsentManagerConnected", _ctx.consentManagerName))
          }, null, 8, _hoisted_2$8)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["content-title"])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_ConsentRequirements"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentRequirementsIntro")), 1),
          vue.createElementVNode("ol", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentRequirementsReasonPersonalData")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentRequirementsReasonStorage")), 1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_WhenDoINeedConsent"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DetermineConsentNeedIntro")), 1),
          vue.createElementVNode("ul", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DetermineConsentNeedAction1")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DetermineConsentNeedAction2")), 1)
          ]),
          _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentNotRequiredIntro")), 1),
          vue.createElementVNode("ul", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentNotRequiredCondition1")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentNotRequiredCondition2")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentNotRequiredCondition3")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentNotRequiredCondition4")), 1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_HandlingPreviouslyCollectedData"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_HandlingPreviouslyCollectedDataIntro")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_HandlingPreviouslyCollectedDataDetails")), 1)
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_HowToObtainValidConsent"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("ol", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement1")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement2")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement3")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement4")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement5")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement6")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement7")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement8")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement9")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ValidConsentRequirement10")), 1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_ConsentManagementPlatforms"),
        class: "privacyAskingForConsent"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_ConsentManagementPlatformsIntro")), 1),
          vue.createElementVNode("ul", {
            innerHTML: _ctx.$sanitize(_ctx.consentManagersList)
          }, null, 8, _hoisted_3$7),
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.consentManagementPlatformsOutro)
          }, null, 8, _hoisted_4$6)
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const AskingForConsent = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  function externalLinkTranslate(tokenSuffix, url) {
    return CoreHome.translate(
      `PrivacyManager_${tokenSuffix}`,
      CoreHome.externalLink(url),
      "</a>"
    );
  }
  const _sfc_main$8 = vue.defineComponent({
    props: {
      afterGDPROverviewIntroContent: String,
      deleteLogsEnable: Boolean,
      deleteReportsEnable: Boolean,
      rawDataRetention: null,
      reportRetention: null
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      VueEntryContainer: CoreHome.VueEntryContainer
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      dataProcessingAgreementIntro1() {
        return CoreHome.translate(
          "PrivacyManager_DataProcessingAgreementIntro1Linked",
          CoreHome.externalLink("https://matomo.org/matomo-cloud-dpa/"),
          "</a>"
        );
      },
      gdprChecklistDesc2() {
        return externalLinkTranslate(
          "GdprChecklistDesc2",
          "https://matomo.org/guide/manage-matomo/privacy/"
        );
      },
      awarenessDocumentationDesc3() {
        return externalLinkTranslate(
          "AwarenessDocumentationDesc3",
          "https://matomo.org/faq/general/faq_18254/"
        );
      },
      awarenessDocumentationDesc4() {
        return externalLinkTranslate(
          "AwarenessDocumentationDesc4",
          "https://matomo.org/blog/2018/04/gdpr-how-to-fill-in-the-information-asset-register-when-using-matomo/"
        );
      },
      securityProceduresDesc1() {
        return externalLinkTranslate(
          "SecurityProceduresDesc1",
          "https://matomo.org/docs/security/"
        );
      },
      securityProceduresDesc2() {
        return externalLinkTranslate(
          "SecurityProceduresDesc2",
          "https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/international-transfers/a-guide-to-international-transfers/"
        );
      },
      securityProceduresDesc3() {
        return externalLinkTranslate(
          "SecurityProceduresDesc3",
          "https://ico.org.uk/for-organisations/report-a-breach/personal-data-breach/personal-data-breaches-a-guide/"
        );
      },
      securityProceduresDesc4() {
        return externalLinkTranslate(
          "SecurityProceduresDesc4",
          "https://www.cnil.fr/en/guidelines-dpia"
        );
      }
    },
    methods: {
      rightsLinkText(tokenSuffix, action = "gdprTools") {
        const link = `?${CoreHome.MatomoUrl.stringify({
          module: "PrivacyManager",
          action
        })}`;
        return CoreHome.translate(
          `PrivacyManager_${tokenSuffix}`,
          `<a target="_blank" rel="noreferrer noopener" href="${link}">`,
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$8 = { class: "gdprOverview" };
  const _hoisted_2$7 = ["innerHTML"];
  const _hoisted_3$6 = ["innerHTML"];
  const _hoisted_4$5 = ["innerHTML"];
  const _hoisted_5$4 = ["innerHTML"];
  const _hoisted_6$2 = ["innerHTML"];
  const _hoisted_7$2 = ["innerHTML"];
  const _hoisted_8$1 = ["innerHTML"];
  const _hoisted_9$1 = ["innerHTML"];
  const _hoisted_10$1 = ["innerHTML"];
  const _hoisted_11 = ["innerHTML"];
  const _hoisted_12 = ["innerHTML"];
  const _hoisted_13 = ["innerHTML"];
  const _hoisted_14 = ["innerHTML"];
  const _hoisted_15 = ["innerHTML"];
  const _hoisted_16 = ["innerHTML"];
  const _hoisted_17 = ["innerHTML"];
  const _hoisted_18 = ["innerHTML"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_VueEntryContainer = vue.resolveComponent("VueEntryContainer");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverview")), 1),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewIntro1")), 1),
        vue.createElementVNode("ul", null, [
          vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewKeyPoint1")), 1),
          vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewIntro3")), 1),
          vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewIntro4")), 1)
        ]),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewMatomoPersonalData")), 1),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewApplicabilityIntro")), 1),
        vue.createElementVNode("ul", null, [
          vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewApplicabilityCondition1")), 1),
          vue.createElementVNode("li", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewApplicabilityCondition2")) + " ", 1),
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewApplicabilityCondition2Detail1")), 1),
              vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewApplicabilityCondition2Detail2")), 1)
            ])
          ])
        ]),
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_GdprOverviewIntro2")), 1)
      ])), [
        [_directive_content_intro]
      ]),
      vue.createVNode(_component_VueEntryContainer, { html: _ctx.afterGDPROverviewIntroContent }, null, 8, ["html"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_DataProcessingAgreement")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.dataProcessingAgreementIntro1)
            }, null, 8, _hoisted_2$7)
          ]),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DataProcessingAgreementIntro2")), 1)
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_GdprChecklists")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_GdprChecklistDesc1")) + " ", 1),
            _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.gdprChecklistDesc2)
            }, null, 8, _hoisted_3$6)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_IndividualsRights")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_IndividualsRightsIntro")), 1),
          vue.createElementVNode("ol", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_IndividualsRightsInform")), 1),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.rightsLinkText("IndividualsRightsAccess"))
            }, null, 8, _hoisted_4$5),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.rightsLinkText("IndividualsRightsErasure"))
            }, null, 8, _hoisted_5$4),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.rightsLinkText("IndividualsRightsRectification"))
            }, null, 8, _hoisted_6$2),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.rightsLinkText("IndividualsRightsPortability"))
            }, null, 8, _hoisted_7$2),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.rightsLinkText("IndividualsRightsObject", "usersOptOut"))
            }, null, 8, _hoisted_8$1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_IndividualsRightsChildren")), 1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_AwarenessDocumentation")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AwarenessDocumentationIntro")), 1),
          vue.createElementVNode("ol", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AwarenessDocumentationDesc1")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AwarenessDocumentationDesc2")), 1),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.awarenessDocumentationDesc3)
            }, null, 8, _hoisted_9$1),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.awarenessDocumentationDesc4)
            }, null, 8, _hoisted_10$1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_SecurityProcedures")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_SecurityProceduresIntro")), 1),
          vue.createElementVNode("ol", null, [
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc1)
            }, null, 8, _hoisted_11),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc2)
            }, null, 8, _hoisted_12),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc3)
            }, null, 8, _hoisted_13),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.securityProceduresDesc4)
            }, null, 8, _hoisted_14)
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_DataRetention")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DataRetentionInMatomo")), 1),
          vue.createElementVNode("ul", null, [
            _ctx.deleteLogsEnable ? (vue.openBlock(), vue.createElementBlock("li", {
              key: 0,
              innerHTML: _ctx.$sanitize(_ctx.translate(
                "PrivacyManager_RawDataRemovedAfter",
                `<strong>${_ctx.rawDataRetention}</strong>`
              ))
            }, null, 8, _hoisted_15)) : (vue.openBlock(), vue.createElementBlock("li", {
              key: 1,
              innerHTML: _ctx.$sanitize(_ctx.translate("PrivacyManager_RawDataNeverRemoved"))
            }, null, 8, _hoisted_16)),
            _ctx.deleteReportsEnable ? (vue.openBlock(), vue.createElementBlock("li", {
              key: 2,
              innerHTML: _ctx.$sanitize(_ctx.translate(
                "PrivacyManager_ReportsRemovedAfter",
                `<strong>${_ctx.reportRetention}</strong>`
              ))
            }, null, 8, _hoisted_17)) : (vue.openBlock(), vue.createElementBlock("li", {
              key: 3,
              innerHTML: _ctx.$sanitize(_ctx.translate("PrivacyManager_ReportsNeverRemoved"))
            }, null, 8, _hoisted_18))
          ]),
          vue.createElementVNode("p", null, [
            _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_DataRetentionOverall")), 1)
          ])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const GdprOverview = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const _sfc_main$7 = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock
    }
  });
  const _hoisted_1$7 = { class: "eprivacyLaws" };
  const _hoisted_2$6 = {
    href: "https://matomo.org/faq/general/eprivacy-directive-national-implementations-and-website-analytics/",
    target: "_blank",
    rel: "noreferrer noopener"
  };
  const _hoisted_3$5 = {
    href: "https://matomo.org/faq/how-to/how-do-i-configure-matomo-without-tracking-consent-for-french-visitors-cnil-exemption/",
    target: "_blank",
    rel: "noreferrer noopener"
  };
  const _hoisted_4$4 = {
    href: "https://matomo.org/faq/new-to-piwik/configure-matomo-analytics-for-tdddg-ttdsg-compliance/",
    target: "_blank",
    rel: "noreferrer noopener"
  };
  const _hoisted_5$3 = {
    href: "https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-without-consent-or-cookie-banner/",
    target: "_blank",
    rel: "noreferrer noopener"
  };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_EPrivacyLaws")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyIntro")) + " ", 1),
            _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyDirectiveArticle53Intro")), 1)
          ]),
          vue.createElementVNode("ul", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyConsentRequired")), 1),
            vue.createElementVNode("li", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExceptionsExist")) + " ", 1),
              vue.createElementVNode("ul", null, [
                vue.createElementVNode("li", null, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyStrictlyNecessaryExamplesTitle")) + " ", 1),
                  vue.createElementVNode("ul", null, [
                    vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExampleConsentStatus")), 1),
                    vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExampleAuthenticationSecurity")), 1),
                    vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExampleCartBilling")), 1),
                    vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExamplePersonalisation")), 1),
                    vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyExampleLoadBalancing")), 1)
                  ])
                ]),
                vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyTransmissionException")), 1)
              ])
            ])
          ]),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyAnonymousTrackingConsent")), 1)
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_EPrivacyNationalImplementationsTitle")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("ul", null, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyNationalImplementationAnalyticsExempt")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyNationalImplementationPriorConsent")), 1)
          ]),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyCheckLocalRules")), 1)
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_EPrivacyFurtherInformationTitle")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("ul", null, [
            vue.createElementVNode("li", null, [
              vue.createElementVNode("a", _hoisted_2$6, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyFurtherInfoDirectiveAndAnalytics")), 1)
            ]),
            vue.createElementVNode("li", null, [
              vue.createElementVNode("a", _hoisted_3$5, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyFurtherInfoFrenchVisitors")), 1)
            ]),
            vue.createElementVNode("li", null, [
              vue.createElementVNode("a", _hoisted_4$4, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyFurtherInfoTdddgCompliance")), 1)
            ]),
            vue.createElementVNode("li", null, [
              vue.createElementVNode("a", _hoisted_5$3, vue.toDisplayString(_ctx.translate("PrivacyManager_EPrivacyFurtherInfoWithoutConsent")), 1)
            ])
          ])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const EPrivacyLaws = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = vue.defineComponent({
    props: {
      anonymizations: {
        type: Array,
        required: true
      }
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    }
  });
  const _hoisted_1$6 = { key: 0 };
  const _hoisted_2$5 = { key: 1 };
  const _hoisted_3$4 = { key: 2 };
  const _hoisted_4$3 = { key: 3 };
  const _hoisted_5$2 = { key: 0 };
  const _hoisted_6$1 = ["title"];
  const _hoisted_7$1 = { key: 1 };
  const _hoisted_8 = ["title"];
  const _hoisted_9 = { key: 2 };
  const _hoisted_10 = ["title"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("PrivacyManager_PreviousRawDataAnonymizations")), 1),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
        vue.createElementVNode("thead", null, [
          vue.createElementVNode("tr", null, [
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_Requester")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AffectedIDSites")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AffectedDate")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_Anonymize")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_VisitColumns")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("PrivacyManager_LinkVisitActionColumns")), 1),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Status")), 1)
          ])
        ]),
        vue.createElementVNode("tbody", null, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.anonymizations, (entry, index) => {
            return vue.openBlock(), vue.createElementBlock("tr", { key: index }, [
              vue.createElementVNode("td", null, vue.toDisplayString(entry.requester), 1),
              vue.createElementVNode("td", null, vue.toDisplayString(entry.sites.join(", ")), 1),
              vue.createElementVNode("td", null, vue.toDisplayString(entry.date_start) + " - " + vue.toDisplayString(entry.date_end), 1),
              vue.createElementVNode("td", null, [
                entry.anonymize_ip ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$6, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_IPAddress")), 1),
                  _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1))
                ])) : vue.createCommentVNode("", true),
                entry.anonymize_location ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$5, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("Overlay_Location")), 1),
                  _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1))
                ])) : vue.createCommentVNode("", true),
                entry.anonymize_userid ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3$4, vue.toDisplayString(_ctx.translate("General_UserId")), 1)) : vue.createCommentVNode("", true),
                !entry.anonymize_ip && !entry.anonymize_location && !entry.anonymize_userid ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$3, "-")) : vue.createCommentVNode("", true)
              ]),
              vue.createElementVNode("td", null, vue.toDisplayString(entry.unset_visit_columns.join(", ")), 1),
              vue.createElementVNode("td", null, vue.toDisplayString(entry.unset_link_visit_action_columns.join(", ")), 1),
              vue.createElementVNode("td", null, [
                !entry.job_start_date ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$2, [
                  vue.createElementVNode("span", {
                    class: "icon-info",
                    style: { "cursor": "help" },
                    title: `${_ctx.translate("PrivacyManager_ScheduledDate", entry.scheduled_date || "")}`
                  }, null, 8, _hoisted_6$1),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_Scheduled")), 1)
                ])) : entry.job_start_date && !entry.job_finish_date ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$1, [
                  vue.createElementVNode("span", {
                    class: "icon-info",
                    style: { "cursor": "help" },
                    title: `${_ctx.translate("PrivacyManager_ScheduledDate", entry.scheduled_date || "")}.
${_ctx.translate("PrivacyManager_JobStartDate", entry.job_start_date)}.
${_ctx.translate("PrivacyManager_CurrentOutput", entry.output)}`
                  }, null, 8, _hoisted_8),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_InProgress")), 1)
                ])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_9, [
                  vue.createElementVNode("span", {
                    class: "icon-info",
                    style: { "cursor": "help" },
                    title: `${_ctx.translate("PrivacyManager_ScheduledDate", entry.scheduled_date || "")}.
${_ctx.translate("PrivacyManager_JobStartDate", entry.job_start_date)}.
${_ctx.translate("PrivacyManager_JobFinishDate", entry.job_finish_date)}.
${_ctx.translate("PrivacyManager_Output", entry.output)}`
                  }, null, 8, _hoisted_10),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Done")), 1)
                ]))
              ])
            ]);
          }), 128))
        ])
      ])), [
        [_directive_content_table]
      ])
    ]);
  }
  const PreviousAnonymizations = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = vue.defineComponent({
    props: {
      ipAnonymizerEnabled: Boolean,
      anonymizeUserId: Boolean,
      ipAddressMaskLength: {
        type: Number,
        required: true
      },
      useAnonymizedIpForVisitEnrichment: [Boolean, String, Number],
      anonymizeOrderId: Boolean,
      forceCookielessTracking: Boolean,
      anonymizeReferrer: String,
      maskLengthOptions: {
        type: Array,
        required: true
      },
      useAnonymizedIpForVisitEnrichmentOptions: {
        type: Array,
        required: true
      },
      trackerFileName: {
        type: String,
        required: true
      },
      trackerWritable: {
        type: Boolean,
        required: true
      },
      referrerAnonymizationOptions: {
        type: Object,
        required: true
      },
      isDataPurgeSettingsEnabled: Boolean,
      deleteData: {
        type: Object,
        required: true
      },
      scheduleDeletionOptions: {
        type: Object,
        required: true
      },
      anonymizations: {
        type: Array,
        required: true
      },
      isSuperUser: Boolean,
      randomizeConfigId: Boolean,
      extraMetadata: {
        type: Object,
        default: () => ({})
      }
    },
    components: {
      AnonymizeIp,
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      ContentBlock: CoreHome.ContentBlock,
      DeleteOldLogs,
      DeleteOldReports,
      ScheduleReportDeletion,
      AnonymizeLogData,
      PreviousAnonymizations
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    computed: {
      teaserHeader() {
        return CoreHome.translate(
          "PrivacyManager_TeaserHeader",
          '<a href="#anonymizeIPAnchor">',
          "</a>",
          '<a href="#deleteLogsAnchor">',
          "</a>",
          '<a href="#anonymizeHistoricalData">',
          "</a>"
        );
      },
      seeAlsoOurOfficialGuide() {
        return CoreHome.translate(
          "PrivacyManager_SeeAlsoOurOfficialGuidePrivacy",
          CoreHome.externalLink("https://matomo.org/privacy/"),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$5 = ["innerHTML"];
  const _hoisted_2$4 = ["innerHTML"];
  const _hoisted_3$3 = { key: 0 };
  const _hoisted_4$2 = { key: 1 };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_AnonymizeIp = vue.resolveComponent("AnonymizeIp");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_DeleteOldLogs = vue.resolveComponent("DeleteOldLogs");
    const _component_DeleteOldReports = vue.resolveComponent("DeleteOldReports");
    const _component_ScheduleReportDeletion = vue.resolveComponent("ScheduleReportDeletion");
    const _component_AnonymizeLogData = vue.resolveComponent("AnonymizeLogData");
    const _component_PreviousAnonymizations = vue.resolveComponent("PreviousAnonymizations");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, [
          vue.createVNode(_component_EnrichedHeadline, {
            "help-url": _ctx.externalRawLink("https://matomo.org/docs/privacy/")
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizeData")), 1)
            ]),
            _: 1
          }, 8, ["help-url"])
        ]),
        vue.createElementVNode("p", null, [
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.teaserHeader),
            style: { "margin-right": "3.5px" }
          }, null, 8, _hoisted_1$5),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.seeAlsoOurOfficialGuide)
          }, null, 8, _hoisted_2$4)
        ])
      ])), [
        [_directive_content_intro]
      ]),
      vue.createVNode(_component_ContentBlock, {
        id: "anonymizeIPAnchor",
        "content-title": _ctx.translate("PrivacyManager_UseAnonymizeTrackingData")
      }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_AnonymizeIp, {
            "ip-anonymizer-enabled": _ctx.ipAnonymizerEnabled,
            "anonymize-user-id": _ctx.anonymizeUserId,
            "ip-address-mask-length": _ctx.ipAddressMaskLength,
            "use-anonymized-ip-for-visit-enrichment": _ctx.useAnonymizedIpForVisitEnrichment,
            "anonymize-order-id": _ctx.anonymizeOrderId,
            "force-cookieless-tracking": _ctx.forceCookielessTracking,
            "anonymize-referrer": _ctx.anonymizeReferrer,
            "mask-length-options": _ctx.maskLengthOptions,
            "use-anonymized-ip-for-visit-enrichment-options": _ctx.useAnonymizedIpForVisitEnrichmentOptions,
            "tracker-file-name": _ctx.trackerFileName,
            "tracker-writable": _ctx.trackerWritable,
            "referrer-anonymization-options": _ctx.referrerAnonymizationOptions,
            "randomize-config-id": _ctx.randomizeConfigId,
            "extra-metadata": _ctx.extraMetadata
          }, null, 8, ["ip-anonymizer-enabled", "anonymize-user-id", "ip-address-mask-length", "use-anonymized-ip-for-visit-enrichment", "anonymize-order-id", "force-cookieless-tracking", "anonymize-referrer", "mask-length-options", "use-anonymized-ip-for-visit-enrichment-options", "tracker-file-name", "tracker-writable", "referrer-anonymization-options", "randomize-config-id", "extra-metadata"])
        ]),
        _: 1
      }, 8, ["content-title"]),
      _ctx.isDataPurgeSettingsEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$3, [
        vue.createVNode(_component_ContentBlock, {
          id: "deleteLogsAnchor",
          "content-title": _ctx.translate("PrivacyManager_DeleteOldRawData")
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DeleteDataDescription")), 1),
            vue.createVNode(_component_DeleteOldLogs, {
              "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
              "delete-data": _ctx.deleteData,
              "schedule-deletion-options": _ctx.scheduleDeletionOptions
            }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])
          ]),
          _: 1
        }, 8, ["content-title"]),
        vue.createVNode(_component_ContentBlock, {
          id: "deleteReportsAnchor",
          "content-title": _ctx.translate("PrivacyManager_DeleteOldAggregatedReports")
        }, {
          default: vue.withCtx(() => [
            vue.createVNode(_component_DeleteOldReports, {
              "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
              "delete-data": _ctx.deleteData,
              "schedule-deletion-options": _ctx.scheduleDeletionOptions
            }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])
          ]),
          _: 1
        }, 8, ["content-title"]),
        vue.createVNode(_component_ScheduleReportDeletion, {
          "is-data-purge-settings-enabled": _ctx.isDataPurgeSettingsEnabled,
          "delete-data": _ctx.deleteData,
          "schedule-deletion-options": _ctx.scheduleDeletionOptions
        }, null, 8, ["is-data-purge-settings-enabled", "delete-data", "schedule-deletion-options"])
      ])) : vue.createCommentVNode("", true),
      _cache[1] || (_cache[1] = vue.createElementVNode("a", {
        name: "anonymizeHistoricalData",
        id: "anonymizeHistoricalData"
      }, null, -1)),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_AnonymizePreviousData"),
        class: "logDataAnonymizer"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizePreviousDataDescription")), 1),
          _ctx.isSuperUser ? (vue.openBlock(), vue.createBlock(_component_AnonymizeLogData, { key: 0 })) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_4$2, vue.toDisplayString(_ctx.translate("PrivacyManager_AnonymizePreviousDataOnlySuperUser")), 1)),
          _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
          vue.createVNode(_component_PreviousAnonymizations, { anonymizations: _ctx.anonymizations }, null, 8, ["anonymizations"])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const PrivacySettings = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  function fetchCompliancePolicies() {
    return __async(this, null, function* () {
      return CoreHome.AjaxHelper.fetch(
        {
          method: "PrivacyManager.getCompliancePolicies"
        },
        {
          createErrorNotification: false
        }
      );
    });
  }
  function createComplianceStore(initialType) {
    const state = vue.reactive({
      idSite: null,
      loading: false,
      complianceType: initialType,
      complianceModeEnforced: false,
      complianceConfigControlled: false,
      complianceRequirements: [],
      fetchComplianceError: null,
      saveComplianceError: null
    });
    function fetchComplianceStatus() {
      return CoreHome.AjaxHelper.fetch(
        {
          idSite: state.idSite,
          complianceType: state.complianceType,
          method: "PrivacyManager.getComplianceStatus"
        },
        {
          createErrorNotification: false
        }
      );
    }
    function storeComplianceStatus(complianceData) {
      state.complianceModeEnforced = complianceData.complianceModeEnforced;
      state.complianceConfigControlled = complianceData.complianceConfigControlled;
      state.complianceRequirements = complianceData.complianceRequirements;
    }
    function fetchCompliance() {
      if (!state.idSite || !state.complianceType) return;
      state.loading = true;
      state.fetchComplianceError = null;
      fetchComplianceStatus().then((complianceData) => {
        storeComplianceStatus(complianceData);
      }).catch((error) => {
        state.fetchComplianceError = error.message || error;
      }).finally(() => {
        state.loading = false;
      });
    }
    function setIdSite(idSite) {
      state.idSite = idSite;
      fetchCompliance();
    }
    function saveComplianceStatus(enforce, password) {
      state.loading = true;
      state.saveComplianceError = null;
      CoreHome.AjaxHelper.post(
        {
          idSite: state.idSite,
          complianceType: state.complianceType,
          enforce,
          method: "PrivacyManager.setComplianceStatus"
        },
        {
          createErrorNotification: false,
          passwordConfirmation: password
        }
      ).then(() => {
        fetchCompliance();
      }).catch((error) => {
        state.saveComplianceError = error.message || error;
      }).finally(() => {
        state.loading = false;
      });
    }
    const publicState = vue.readonly(state);
    return {
      state: publicState,
      setIdSite,
      saveComplianceStatus
    };
  }
  const statusClassMap = {
    compliant: "compliant",
    non_compliant: "non-compliant",
    unknown: "unknown"
  };
  const iconClassMap = {
    compliant: "icon-ok",
    non_compliant: "icon-close",
    unknown: "icon-circle"
  };
  const statusTextMap = {
    compliant: "PrivacyManager_ComplianceCompliant",
    non_compliant: "PrivacyManager_ComplianceNonCompliant",
    unknown: "PrivacyManager_ComplianceComplianceUnknown"
  };
  const _sfc_main$4 = vue.defineComponent({
    props: {
      results: {
        type: Array,
        required: true
      }
    },
    methods: {
      getStatusClass(value) {
        return statusClassMap[value] || statusClassMap.unknown;
      },
      getIconClass(value) {
        return iconClassMap[value] || iconClassMap.unknown;
      },
      getStatusText(value) {
        return statusTextMap[value] || statusTextMap.unknown;
      }
    }
  });
  const _hoisted_1$4 = { class: "card-table dataTable compliance" };
  const _hoisted_2$3 = { class: "label" };
  const _hoisted_3$2 = { class: "label" };
  const _hoisted_4$1 = { class: "label" };
  const _hoisted_5$1 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("table", _hoisted_1$4, [
      vue.createElementVNode("thead", null, [
        vue.createElementVNode("tr", null, [
          vue.createElementVNode("th", _hoisted_2$3, vue.toDisplayString(_ctx.translate("PrivacyManager_ComplianceTableSettingName")), 1),
          vue.createElementVNode("th", _hoisted_3$2, vue.toDisplayString(_ctx.translate("PrivacyManager_ComplianceTableSettingStatus")), 1),
          vue.createElementVNode("th", _hoisted_4$1, vue.toDisplayString(_ctx.translate("PrivacyManager_ComplianceTableSettingNotes")), 1)
        ])
      ]),
      vue.createElementVNode("tbody", null, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.results, (item, index) => {
          return vue.openBlock(), vue.createElementBlock("tr", { key: index }, [
            vue.createElementVNode("td", null, vue.toDisplayString(item.name), 1),
            vue.createElementVNode("td", {
              class: vue.normalizeClass(["status", _ctx.getStatusClass(item.value)])
            }, [
              vue.createElementVNode("span", {
                class: vue.normalizeClass(["icon", _ctx.getIconClass(item.value)])
              }, null, 2),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(_ctx.getStatusText(item.value))), 1)
            ], 2),
            vue.createElementVNode("td", {
              innerHTML: _ctx.$sanitize(item.notes)
            }, null, 8, _hoisted_5$1)
          ]);
        }), 128))
      ])
    ]);
  }
  const ComplianceTable = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      idSite: {
        type: String,
        required: true
      },
      complianceType: {
        type: String,
        required: true
      },
      title: {
        type: String,
        required: true
      },
      description: {
        type: String,
        required: true
      }
    },
    components: {
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation,
      SaveButton: CorePluginsAdmin.SaveButton,
      Field: CorePluginsAdmin.Field,
      ActivityIndicator: CoreHome.ActivityIndicator,
      ComplianceTable,
      ContentBlock: CoreHome.ContentBlock
    },
    methods: {
      saveSettings(password) {
        this.saveComplianceStatus(this.shouldEnforceComplianceMode, password);
        this.showPasswordConfirmation = false;
      },
      resetSave() {
        this.showPasswordConfirmation = false;
      }
    },
    setup(props) {
      const store = createComplianceStore(props.complianceType);
      store.setIdSite(props.idSite);
      const shouldEnforceComplianceMode = vue.ref(false);
      vue.watch(
        () => store.state.complianceModeEnforced,
        (val) => {
          shouldEnforceComplianceMode.value = val;
        },
        { immediate: true }
      );
      vue.watch(
        () => props.idSite,
        (newSite) => {
          if (newSite) {
            store.setIdSite(newSite);
          }
        },
        { immediate: true }
      );
      return {
        state: store.state,
        saveComplianceStatus: store.saveComplianceStatus,
        shouldEnforceComplianceMode,
        showPasswordConfirmation: vue.ref(false)
      };
    }
  });
  const _hoisted_1$3 = ["innerHTML"];
  const _hoisted_2$2 = {
    key: 0,
    class: "notification system notification-error"
  };
  const _hoisted_3$1 = {
    key: 0,
    class: "notification system notification-error"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_ComplianceTable = vue.resolveComponent("ComplianceTable");
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, { "content-title": _ctx.title }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.description)
        }, null, 8, _hoisted_1$3),
        vue.createVNode(_component_ActivityIndicator, {
          loading: _ctx.state.loading
        }, null, 8, ["loading"]),
        !_ctx.state.loading ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          _ctx.state.fetchComplianceError ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$2, vue.toDisplayString(_ctx.translate("General_ErrorTryAgain")) + " " + vue.toDisplayString(_ctx.translate("General_ExceptionContactSupportGeneric", ["", ""])), 1)) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
            vue.createVNode(_component_ComplianceTable, {
              results: _ctx.state.complianceRequirements
            }, null, 8, ["results"]),
            !_ctx.state.complianceConfigControlled ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
              vue.createVNode(_component_Field, {
                uicontrol: "checkbox",
                name: "site-" + _ctx.idSite + "-" + _ctx.complianceType + "-enableFeature",
                title: _ctx.translate("PrivacyManager_ComplianceEnforceCheckboxIntro"),
                introduction: _ctx.translate("PrivacyManager_ComplianceEnforceCheckboxTitle"),
                "inline-help": _ctx.translate("PrivacyManager_ComplianceEnforceCheckboxHelp"),
                modelValue: _ctx.shouldEnforceComplianceMode,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.shouldEnforceComplianceMode = $event)
              }, null, 8, ["name", "title", "introduction", "inline-help", "modelValue"]),
              _ctx.state.saveComplianceError ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, vue.toDisplayString(_ctx.translate("General_ErrorTryAgain")) + " " + vue.toDisplayString(_ctx.translate("General_ExceptionContactSupportGeneric", ["", ""])), 1)) : vue.createCommentVNode("", true),
              vue.createVNode(_component_SaveButton, {
                class: vue.normalizeClass("site-" + _ctx.idSite + "-" + _ctx.complianceType + "-save"),
                onConfirm: _cache[1] || (_cache[1] = ($event) => this.showPasswordConfirmation = true),
                value: _ctx.translate("General_Save")
              }, null, 8, ["class", "value"]),
              vue.createVNode(_component_PasswordConfirmation, {
                "model-value": this.showPasswordConfirmation,
                passwordFieldId: "password" + _ctx.complianceType,
                onConfirmed: _ctx.saveSettings,
                onAborted: _ctx.resetSave
              }, null, 8, ["model-value", "passwordFieldId", "onConfirmed", "onAborted"])
            ], 64)) : vue.createCommentVNode("", true)
          ], 64))
        ], 64)) : vue.createCommentVNode("", true)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const ComplianceOverview = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      ComplianceOverview,
      SiteSelector: CoreHome.SiteSelector
    },
    setup() {
      var _a, _b;
      const site = vue.ref({
        id: (_a = CoreHome.Matomo.idSite) != null ? _a : CoreHome.MatomoUrl.urlParsed.value.idSite,
        name: CoreHome.Matomo.siteName ? CoreHome.Matomo.helper.htmlDecode(CoreHome.Matomo.siteName) : CoreHome.translate("General_MultiSitesSummary")
      });
      const siteId = vue.ref(String((_b = CoreHome.Matomo.idSite) != null ? _b : CoreHome.MatomoUrl.urlParsed.value.idSite));
      vue.watch(site, (newSite) => {
        siteId.value = (newSite == null ? void 0 : newSite.id) != null ? String(newSite.id) : "";
      });
      const complianceTypes = vue.ref([]);
      vue.onMounted(() => __async(null, null, function* () {
        complianceTypes.value = yield fetchCompliancePolicies();
      }));
      return {
        site,
        siteId,
        complianceTypes
      };
    }
  });
  const _hoisted_1$2 = { for: "complianceSite" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_SiteSelector = vue.resolveComponent("SiteSelector");
    const _component_ComplianceOverview = vue.resolveComponent("ComplianceOverview");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("h2", null, [
        vue.createVNode(_component_EnrichedHeadline, null, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_Compliance")), 1)
          ]),
          _: 1
        })
      ]),
      vue.createElementVNode("label", _hoisted_1$2, vue.toDisplayString(_ctx.translate("PrivacyManager_ComplianceSelectSite")), 1),
      vue.createVNode(_component_SiteSelector, {
        id: "complianceSite",
        "switch-site-on-select": false,
        "show-selected-site": true,
        modelValue: _ctx.site,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.site = $event)
      }, null, 8, ["modelValue"]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.complianceTypes, (type) => {
        return vue.openBlock(), vue.createBlock(_component_ComplianceOverview, {
          key: type.id,
          "id-site": _ctx.siteId,
          "compliance-type": type.id,
          title: type.title,
          description: type.description
        }, null, 8, ["id-site", "compliance-type", "title", "description"]);
      }), 128))
    ], 64);
  }
  const Compliance = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      language: {
        type: String,
        required: true
      },
      matomoUrl: String,
      isSuperUser: Boolean,
      dntSupport: Boolean,
      doNotTrackOptions: {
        type: Array,
        required: true
      },
      languageOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      Alert: CoreHome.Alert,
      ContentBlock: CoreHome.ContentBlock,
      DoNotTrackPreference,
      OptOutCustomizer
    },
    data() {
      return {
        prefaceComponents: []
      };
    },
    computed: {
      prefaceComponentsResolved() {
        return vue.markRaw(this.prefaceComponents.map(
          (c) => vue.markRaw(CoreHome.useExternalPluginComponent(c.plugin, c.component))
        ));
      }
    },
    created() {
      const components = [];
      CoreHome.Matomo.postEvent("PrivacyManager.UsersOptOut.preface", components);
      this.prefaceComponents = components;
    }
  });
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = { key: 1 };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_OptOutCustomizer = vue.resolveComponent("OptOutCustomizer");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_Alert = vue.resolveComponent("Alert");
    const _component_DoNotTrackPreference = vue.resolveComponent("DoNotTrackPreference");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_TrackingOptOut")
      }, {
        default: vue.withCtx(() => [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.prefaceComponentsResolved, (preface, index) => {
            return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(preface), { key: index });
          }), 128)),
          vue.createVNode(_component_OptOutCustomizer, {
            "matomo-url": _ctx.matomoUrl,
            language: _ctx.language,
            "language-options": _ctx.languageOptions
          }, null, 8, ["matomo-url", "language", "language-options"])
        ]),
        _: 1
      }, 8, ["content-title"]),
      _ctx.isSuperUser ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
        key: 0,
        id: "DNT",
        "content-title": _ctx.translate("PrivacyManager_DoNotTrack_SupportDNTPreference")
      }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_Alert, { severity: "warning" }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_DoNotTrack_Deprecated")), 1)
            ]),
            _: 1
          }),
          vue.createElementVNode("p", null, [
            _ctx.dntSupport ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$1, [
              vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("PrivacyManager_DoNotTrack_Enabled")), 1),
              _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_DoNotTrack_EnabledMoreInfo")), 1)
            ])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$1, vue.toDisplayString(_ctx.translate("PrivacyManager_DoNotTrack_Disabled")) + " " + vue.toDisplayString(_ctx.translate("PrivacyManager_DoNotTrack_DisabledMoreInfo")), 1))
          ]),
          vue.createVNode(_component_DoNotTrackPreference, {
            "dnt-support": _ctx.dntSupport,
            "do-not-track-options": _ctx.doNotTrackOptions
          }, null, 8, ["dnt-support", "do-not-track-options"])
        ]),
        _: 1
      }, 8, ["content-title"])) : vue.createCommentVNode("", true)
    ]);
  }
  const UsersOptOut = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    computed: {
      exemptionsBullet() {
        return CoreHome.translate(
          "PrivacyManager_UnderstandingYourLegalObligationsBulletExemptions",
          CoreHome.externalLink(
            "https://matomo.org/faq/how-to/how-do-i-configure-matomo-without-tracking-consent-for-french-visitors-cnil-exemption/"
          ),
          "</a>"
        );
      }
    },
    methods: {
      translate: CoreHome.translate
    }
  });
  const _hoisted_1 = { class: "understandingYourLegalObligations" };
  const _hoisted_2 = { class: "browser-default" };
  const _hoisted_3 = ["innerHTML"];
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("PrivacyManager_UnderstandingYourLegalObligations")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsIntro1")) + " ", 1),
            _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsIntro2")) + " ", 1),
            _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
            _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsIntro3")) + " ", 1),
            _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
            _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsIntro4")), 1)
          ]),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsIntro5")), 1),
          vue.createElementVNode("ul", _hoisted_2, [
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(
                _ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsBulletStrictConsent")
              )
            }, null, 8, _hoisted_3),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(_ctx.exemptionsBullet)
            }, null, 8, _hoisted_4),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(
                _ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsBulletPersonalDataOnly")
              )
            }, null, 8, _hoisted_5),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(
                _ctx.translate("PrivacyManager_UnderstandingYourLegalObligationsBulletTransparencyOptOut")
              )
            }, null, 8, _hoisted_6),
            vue.createElementVNode("li", {
              innerHTML: _ctx.$sanitize(
                _ctx.translate(
                  "PrivacyManager_UnderstandingYourLegalObligationsBulletNoComplianceRequirements"
                )
              )
            }, null, 8, _hoisted_7)
          ])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const UnderstandingYourLegalObligations = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AnonymizeIp = AnonymizeIp;
  exports2.AnonymizeLogData = AnonymizeLogData;
  exports2.AskingForConsent = AskingForConsent;
  exports2.Compliance = Compliance;
  exports2.DeleteOldLogs = DeleteOldLogs;
  exports2.DeleteOldReports = DeleteOldReports;
  exports2.DoNotTrackPreference = DoNotTrackPreference;
  exports2.EPrivacyLaws = EPrivacyLaws;
  exports2.GdprOverview = GdprOverview;
  exports2.ManageGdpr = ManageGdpr;
  exports2.OptOutCustomizer = OptOutCustomizer;
  exports2.PreviousAnonymizations = PreviousAnonymizations;
  exports2.PrivacySettings = PrivacySettings;
  exports2.ReportDeletionSettings = ReportDeletionSettingsStore$1;
  exports2.ScheduleReportDeletion = ScheduleReportDeletion;
  exports2.UnderstandingYourLegalObligations = UnderstandingYourLegalObligations;
  exports2.UsersOptOut = UsersOptOut;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
