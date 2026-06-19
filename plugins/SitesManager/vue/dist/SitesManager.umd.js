(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.SitesManager = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
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
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);

  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $ } = window;
  class SiteTypesStore {
    constructor() {
      __publicField(this, "state", vue.reactive({
        isLoading: false,
        typesById: {}
      }));
      __publicField(this, "typesById", vue.computed(() => vue.readonly(this.state).typesById));
      __publicField(this, "isLoading", vue.computed(() => vue.readonly(this.state).isLoading));
      __publicField(this, "types", vue.computed(() => Object.values(this.typesById.value)));
      __publicField(this, "response");
    }
    init() {
      return this.fetchAvailableTypes();
    }
    fetchAvailableTypes() {
      if (this.response) {
        return Promise.resolve(this.response);
      }
      this.state.isLoading = true;
      this.response = CoreHome.AjaxHelper.fetch({
        method: "API.getAvailableMeasurableTypes",
        filter_limit: "-1"
      }).then((types) => {
        types.forEach((type) => {
          this.state.typesById[type.id] = type;
        });
        return this.types.value;
      }).finally(() => {
        this.state.isLoading = false;
      });
      return this.response;
    }
    getEditSiteIdParameter() {
      const m = CoreHome.MatomoUrl.hashQuery.value.match(/editsiteid=([0-9]+)/);
      if (!m) {
        return void 0;
      }
      const isShowAddSite = CoreHome.MatomoUrl.urlParsed.value.showaddsite === "1" || CoreHome.MatomoUrl.urlParsed.value.showaddsite === "true";
      const editsiteid = m[1];
      if (editsiteid && $.isNumeric(editsiteid) && !isShowAddSite) {
        return editsiteid;
      }
      return void 0;
    }
    removeEditSiteIdParameterFromHash() {
      const params = __spreadValues({}, CoreHome.MatomoUrl.hashParsed.value);
      delete params.editsiteid;
      CoreHome.MatomoUrl.updateHash(params);
    }
  }
  const SiteTypesStore$1 = new SiteTypesStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class CurrencyStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isLoading: false,
        currencies: {}
      }));
      __publicField(this, "currencies", vue.computed(() => vue.readonly(this.privateState).currencies));
      __publicField(this, "isLoading", vue.computed(() => vue.readonly(this.privateState).isLoading));
      __publicField(this, "initializePromise", null);
    }
    init() {
      if (!this.initializePromise) {
        this.initializePromise = this.fetchCurrencies();
      }
      return this.initializePromise;
    }
    fetchCurrencies() {
      this.privateState.isLoading = true;
      return CoreHome.AjaxHelper.fetch({
        method: "SitesManager.getCurrencyList"
      }).then((currencies) => {
        this.privateState.currencies = currencies;
      }).finally(() => {
        this.privateState.isLoading = false;
      });
    }
  }
  const CurrencyStore$1 = new CurrencyStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class TimezoneStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isLoading: false,
        timezones: [],
        timezoneSupportEnabled: false
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "timezones", vue.computed(() => this.state.value.timezones));
      __publicField(this, "timezoneSupportEnabled", vue.computed(() => this.state.value.timezoneSupportEnabled));
      __publicField(this, "isLoading", vue.computed(() => this.state.value.isLoading));
      __publicField(this, "initializePromise", null);
    }
    init() {
      if (!this.initializePromise) {
        this.privateState.isLoading = true;
        this.initializePromise = Promise.all([
          this.checkTimezoneSupportEnabled(),
          this.fetchTimezones()
        ]).finally(() => {
          this.privateState.isLoading = false;
        });
      }
      return this.initializePromise;
    }
    fetchTimezones() {
      return CoreHome.AjaxHelper.fetch({
        method: "SitesManager.getTimezonesList"
      }).then((grouped) => {
        const flattened = [];
        Object.entries(grouped).forEach(([group, timezonesGroup]) => {
          Object.entries(timezonesGroup).forEach(([label, code]) => {
            flattened.push({
              group,
              label,
              code
            });
          });
        });
        this.privateState.timezones = flattened;
      });
    }
    checkTimezoneSupportEnabled() {
      return CoreHome.AjaxHelper.fetch({
        method: "SitesManager.isTimezoneSupportEnabled"
      }).then((response) => {
        this.privateState.timezoneSupportEnabled = response.value;
      });
    }
  }
  const TimezoneStore$1 = new TimezoneStore();
  const _sfc_main$5 = vue.defineComponent({
    props: {
      siteIsBeingEdited: {
        type: Boolean,
        required: true
      },
      hasPrev: {
        type: Boolean,
        required: true
      },
      hasNext: {
        type: Boolean,
        required: true
      },
      offsetStart: {
        type: Number,
        required: true
      },
      offsetEnd: {
        type: Number,
        required: true
      },
      totalNumberOfSites: {
        type: Number
      },
      isLoading: {
        type: Boolean,
        required: true
      },
      searchTerm: {
        type: String,
        required: true
      },
      isSearching: {
        type: Boolean,
        required: true
      }
    },
    emits: ["add", "search", "prev", "next", "update:searchTerm"],
    created() {
      SiteTypesStore$1.init();
      this.onKeydown = CoreHome.debounce(this.onKeydown, 50);
    },
    computed: {
      hasSuperUserAccess() {
        return CoreHome.Matomo.hasSuperUserAccess;
      },
      availableTypes() {
        return SiteTypesStore$1.types.value;
      },
      paginationText() {
        let text;
        if (this.isSearching) {
          text = CoreHome.translate(
            "General_PaginationWithoutTotal",
            `${this.offsetStart}`,
            `${this.offsetEnd}`
          );
        } else {
          text = CoreHome.translate(
            "General_Pagination",
            `${this.offsetStart}`,
            `${this.offsetEnd}`,
            this.totalNumberOfSites === null ? "?" : `${this.totalNumberOfSites}`
          );
        }
        return ` ${text} `;
      }
    },
    methods: {
      addNewEntity() {
        this.$emit("add");
      },
      searchSite() {
        if (this.siteIsBeingEdited) {
          return;
        }
        this.$emit("search");
      },
      previousPage() {
        this.$emit("prev");
      },
      nextPage() {
        this.$emit("next");
      },
      onKeydown(event) {
        setTimeout(() => {
          if (event.key === "Enter") {
            this.searchSiteOnEnter(event);
            return;
          }
          this.$emit("update:searchTerm", event.target.value);
        });
      },
      searchSiteOnEnter(event) {
        event.preventDefault();
        this.searchSite();
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
  const _hoisted_1$5 = { class: "sitesButtonBar clearfix" };
  const _hoisted_2$5 = { class: "search" };
  const _hoisted_3$5 = ["value", "placeholder", "disabled"];
  const _hoisted_4$5 = ["title"];
  const _hoisted_5$5 = { class: "paging" };
  const _hoisted_6$5 = ["disabled"];
  const _hoisted_7$5 = { style: { "cursor": "pointer" } };
  const _hoisted_8$5 = { class: "counter" };
  const _hoisted_9$5 = ["disabled"];
  const _hoisted_10$5 = {
    style: { "cursor": "pointer" },
    class: "pointer"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.withDirectives(vue.createElementVNode("a", {
        class: vue.normalizeClass(["btn addSite", { disabled: _ctx.siteIsBeingEdited }]),
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.addNewEntity()),
        tabindex: "1"
      }, vue.toDisplayString(_ctx.availableTypes.length > 1 ? _ctx.translate("SitesManager_AddMeasurable") : _ctx.translate("SitesManager_AddSite")), 3), [
        [vue.vShow, _ctx.hasSuperUserAccess && _ctx.availableTypes]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_2$5, [
        vue.createElementVNode("input", {
          value: _ctx.searchTerm,
          onKeydown: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydown($event)),
          placeholder: _ctx.translate("Actions_SubmenuSitesearch"),
          type: "text",
          disabled: _ctx.siteIsBeingEdited
        }, null, 40, _hoisted_3$5),
        vue.createElementVNode("div", {
          onClick: _cache[2] || (_cache[2] = ($event) => _ctx.searchSite()),
          title: _ctx.translate("General_ClickToSearch"),
          class: "search_ico icon-search"
        }, null, 8, _hoisted_4$5)
      ], 512), [
        [vue.vShow, _ctx.hasPrev || _ctx.hasNext || _ctx.isSearching]
      ]),
      vue.withDirectives(vue.createElementVNode("div", _hoisted_5$5, [
        vue.createElementVNode("a", {
          class: "btn prev",
          disabled: _ctx.hasPrev && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? void 0 : true,
          onClick: _cache[3] || (_cache[3] = ($event) => _ctx.previousPage())
        }, [
          vue.createElementVNode("span", _hoisted_7$5, "« " + vue.toDisplayString(_ctx.translate("General_Previous")), 1)
        ], 8, _hoisted_6$5),
        vue.withDirectives(vue.createElementVNode("span", _hoisted_8$5, [
          vue.createElementVNode("span", null, vue.toDisplayString(_ctx.paginationText), 1)
        ], 512), [
          [vue.vShow, _ctx.hasPrev || _ctx.hasNext]
        ]),
        vue.createElementVNode("a", {
          class: "btn next",
          disabled: _ctx.hasNext && !_ctx.isLoading && !_ctx.siteIsBeingEdited ? void 0 : true,
          onClick: _cache[4] || (_cache[4] = ($event) => _ctx.nextPage())
        }, [
          vue.createElementVNode("span", _hoisted_10$5, vue.toDisplayString(_ctx.translate("General_Next")) + " »", 1)
        ], 8, _hoisted_9$5)
      ], 512), [
        [vue.vShow, _ctx.hasPrev || _ctx.hasNext]
      ])
    ]);
  }
  const ButtonBar = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const timezoneOptions = vue.computed(
    () => TimezoneStore$1.timezones.value.map(({ group, label, code }) => ({
      group,
      key: label,
      value: code
    }))
  );
  function isSiteNew(site) {
    return typeof site.idsite === "undefined";
  }
  const _sfc_main$4 = vue.defineComponent({
    props: {
      site: {
        type: Object,
        required: true
      },
      timezoneSupportEnabled: {
        type: Boolean
      },
      utcTime: {
        type: Date,
        required: true
      },
      globalSettings: {
        type: Object,
        required: true
      },
      privacyManagerEnabled: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        isLoading: false,
        isLoadingPrivacy: false,
        isSaving: false,
        editMode: false,
        theSite: __spreadValues({}, this.site),
        measurableSettings: [],
        anonymisationSettings: [],
        settingValues: {},
        showRemoveDialog: false,
        deleteSiteExplanation: "",
        triggerSavePrivacySettings: ""
      };
    },
    components: {
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation,
      Field: CorePluginsAdmin.Field,
      GroupedSettings: CorePluginsAdmin.GroupedSettings,
      ActivityIndicator: CoreHome.ActivityIndicator
    },
    emits: ["delete", "editSite", "cancelEditSite", "save"],
    created() {
      CurrencyStore$1.init();
      TimezoneStore$1.init();
      SiteTypesStore$1.init();
      this.onSiteChanged();
    },
    watch: {
      site() {
        this.onSiteChanged();
      },
      measurableSettings(settings) {
        if (!settings.length) {
          return;
        }
        const settingValues = {};
        settings.forEach((settingsForPlugin) => {
          settingsForPlugin.settings.forEach((setting) => {
            settingValues[`${settingsForPlugin.pluginName}.${setting.name}`] = setting.value;
          });
        });
        this.settingValues = settingValues;
      }
    },
    methods: {
      onSiteChanged() {
        const site = this.site;
        this.theSite = __spreadValues({}, site);
        const isNew = isSiteNew(site);
        if (isNew) {
          const globalSettings = this.globalSettings;
          this.theSite.timezone = globalSettings.defaultTimezone;
          this.theSite.currency = globalSettings.defaultCurrency;
        }
        const forcedEditSiteId = SiteTypesStore$1.getEditSiteIdParameter();
        if (isNew || forcedEditSiteId && `${site.idsite}` === forcedEditSiteId) {
          this.editSite();
        }
      },
      editSite() {
        this.editMode = true;
        const idSite = this.theSite.idsite;
        this.$emit("editSite", { idSite });
        this.measurableSettings = [];
        this.anonymisationSettings = [];
        if (isSiteNew(this.theSite)) {
          if (!this.currentType) {
            return;
          }
          this.measurableSettings = this.currentType.settings || [];
          return;
        }
        this.isLoading = true;
        CoreHome.AjaxHelper.fetch({
          method: "SitesManager.getSiteSettings",
          idSite
        }).then((settings) => {
          this.measurableSettings = settings;
        }).finally(() => {
          this.isLoading = false;
        });
        if (this.privacyManagerEnabled && idSite) {
          this.isLoadingPrivacy = true;
          CoreHome.AjaxHelper.fetch({
            method: "PrivacyManager.getAnonymisationSettings",
            idSiteSpecific: idSite
          }).then((settings) => {
            this.anonymisationSettings = settings;
          }).finally(() => {
            this.isLoadingPrivacy = false;
          });
        }
      },
      onPrivacyUpdated() {
        this.triggerSavePrivacySettings = "done";
        this.anonymisationSettings = [];
      },
      onPrivacyAborted() {
        this.triggerSavePrivacySettings = "abort";
        this.isSaving = false;
      },
      saveSite() {
        var _a;
        if (this.isSaving) {
          return;
        }
        this.isSaving = true;
        const values = {
          siteName: this.theSite.name,
          description: (_a = this.theSite.description) != null ? _a : "",
          timezone: this.theSite.timezone,
          currency: this.theSite.currency,
          type: this.theSite.type,
          settingValues: {}
        };
        const isNew = isSiteNew(this.theSite);
        let apiMethod = "SitesManager.addSite";
        if (!isNew) {
          apiMethod = "SitesManager.updateSite";
          values.idSite = this.theSite.idsite;
        }
        Object.entries(this.settingValues).forEach(([fullName, fieldValue]) => {
          const [pluginName, name] = fullName.split(".");
          const settingValues = values.settingValues;
          if (!settingValues[pluginName]) {
            settingValues[pluginName] = [];
          }
          let value = fieldValue;
          if (fieldValue === false) {
            value = "0";
          } else if (fieldValue === true) {
            value = "1";
          } else if (Array.isArray(fieldValue)) {
            value = fieldValue.filter((x) => !!x);
          }
          settingValues[pluginName].push({
            name,
            value
          });
        });
        const showNotificationAndEmitSave = () => {
          const notificationId = CoreHome.NotificationsStore.show({
            message: isNew ? CoreHome.translate("SitesManager_WebsiteCreated") : CoreHome.translate("SitesManager_WebsiteUpdated"),
            context: "success",
            id: "websitecreated",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(notificationId);
          SiteTypesStore$1.removeEditSiteIdParameterFromHash();
          this.isSaving = false;
          this.editMode = false;
          this.$emit("save", {
            site: this.theSite,
            settingValues: values.settingValues,
            isNew
          });
        };
        const saveSitePromise = () => Promise.resolve(CoreHome.AjaxHelper.post(
          {
            method: apiMethod
          },
          values
        )).then((response) => {
          if (!this.theSite.idsite && response && response.value) {
            this.theSite.idsite = `${response.value}`;
          }
          const timezoneInfo = TimezoneStore$1.timezones.value.find(
            (t) => t.code === this.theSite.timezone
          );
          this.theSite.timezone_name = (timezoneInfo == null ? void 0 : timezoneInfo.label) || this.theSite.timezone;
          if (this.theSite.currency) {
            this.theSite.currency_name = CurrencyStore$1.currencies.value[this.theSite.currency];
          }
        });
        if (!isNew) {
          const savePrivacySettingsPromise = this.getTriggerPrivacySettingsSavePromise();
          savePrivacySettingsPromise.then(
            () => saveSitePromise().then(() => {
              showNotificationAndEmitSave();
            }).catch(() => {
              this.isSaving = false;
            })
          ).catch(() => {
            this.isSaving = false;
          });
        } else {
          saveSitePromise().then(() => {
            showNotificationAndEmitSave();
          });
        }
      },
      cancelEditSite(site) {
        this.editMode = false;
        SiteTypesStore$1.removeEditSiteIdParameterFromHash();
        this.$emit("cancelEditSite", { site, element: this.$refs.root });
      },
      deleteSite(password) {
        CoreHome.AjaxHelper.post({
          idSite: this.theSite.idsite,
          module: "API",
          format: "json",
          method: "SitesManager.deleteSite"
        }, {
          passwordConfirmation: password
        }).then(() => {
          this.$emit("delete", this.theSite);
        });
      },
      getMessagesToWarnOnSiteRemoval() {
        CoreHome.AjaxHelper.post({
          idSite: this.theSite.idsite,
          module: "API",
          format: "json",
          method: "SitesManager.getMessagesToWarnOnSiteRemoval"
        }).then((response) => {
          this.deleteSiteExplanation = "";
          if (response.length) {
            this.deleteSiteExplanation += response.join("<br>");
          }
          this.showRemoveDialog = true;
        });
      },
      getTriggerPrivacySettingsSavePromise() {
        return new Promise((resolve, reject) => {
          const unwatchTrigger = this.$watch(
            "triggerSavePrivacySettings",
            (val) => {
              if (val === "done") {
                unwatchTrigger();
                resolve(true);
              }
              if (val === "abort") {
                unwatchTrigger();
                reject();
              }
            },
            { immediate: false }
          );
          this.triggerSavePrivacySettings = "save";
        });
      }
    },
    computed: {
      availableTypes() {
        return SiteTypesStore$1.types.value;
      },
      setupUrl() {
        const site = this.theSite;
        let suffix = "";
        let connector = "";
        if (this.isInternalSetupUrl) {
          suffix = CoreHome.MatomoUrl.stringify({
            idSite: site.idsite,
            period: CoreHome.MatomoUrl.parsed.value.period,
            date: CoreHome.MatomoUrl.parsed.value.date,
            updated: "false"
          });
          connector = this.howToSetupUrl.indexOf("?") === -1 ? "?" : "&";
        }
        return `${this.howToSetupUrl}${connector}${suffix}`;
      },
      utcTimeIs() {
        const utcTime = this.utcTime;
        const formatTimePart = (n) => n.toString().padStart(2, "0");
        const hours = formatTimePart(utcTime.getHours());
        const minutes = formatTimePart(utcTime.getMinutes());
        const seconds = formatTimePart(utcTime.getSeconds());
        const date = `${CoreHome.format(this.utcTime)} ${hours}:${minutes}:${seconds}`;
        return CoreHome.translate("SitesManager_UTCTimeIs", date);
      },
      timezones() {
        return timezoneOptions.value;
      },
      currencies() {
        return CurrencyStore$1.currencies.value;
      },
      currentType() {
        const site = this.site;
        const type = SiteTypesStore$1.typesById.value[site.type];
        if (!type) {
          return { name: site.type };
        }
        return type;
      },
      howToSetupUrl() {
        const type = this.currentType;
        if (!type) {
          return void 0;
        }
        return type.howToSetupUrl;
      },
      isInternalSetupUrl() {
        const { howToSetupUrl } = this;
        if (!howToSetupUrl) {
          return false;
        }
        return `${howToSetupUrl}`.substring(0, 1) === "?";
      },
      removeDialogTitle() {
        return CoreHome.translate(
          "SitesManager_DeleteConfirm",
          `"${this.theSite.name}" (idSite = ${this.theSite.idsite})`
        );
      },
      anonymizeIpComponent() {
        if (this.privacyManagerEnabled) {
          return CoreHome.useExternalPluginComponent("PrivacyManager", "AnonymizeIp");
        }
        return "";
      }
    }
  });
  const _hoisted_1$4 = ["idsite", "type"];
  const _hoisted_2$4 = { class: "card-content" };
  const _hoisted_3$4 = {
    key: 0,
    class: "row"
  };
  const _hoisted_4$4 = { class: "col m3" };
  const _hoisted_5$4 = { class: "title" };
  const _hoisted_6$4 = { class: "title" };
  const _hoisted_7$4 = ["target", "title", "href"];
  const _hoisted_8$4 = { class: "col m4" };
  const _hoisted_9$4 = { class: "title" };
  const _hoisted_10$4 = { class: "title" };
  const _hoisted_11$4 = { class: "title" };
  const _hoisted_12$3 = { class: "title" };
  const _hoisted_13$3 = { class: "col m4" };
  const _hoisted_14$3 = { class: "title" };
  const _hoisted_15$2 = ["href"];
  const _hoisted_16$2 = { key: 0 };
  const _hoisted_17$1 = { class: "title" };
  const _hoisted_18$1 = { key: 1 };
  const _hoisted_19$1 = { class: "title" };
  const _hoisted_20$1 = { key: 2 };
  const _hoisted_21$1 = { class: "title" };
  const _hoisted_22$1 = { class: "col m1 right-align" };
  const _hoisted_23 = ["title"];
  const _hoisted_24 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-edit" }, null, -1);
  const _hoisted_25 = [
    _hoisted_24
  ];
  const _hoisted_26 = ["title"];
  const _hoisted_27 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-delete" }, null, -1);
  const _hoisted_28 = [
    _hoisted_27
  ];
  const _hoisted_29 = { key: 1 };
  const _hoisted_30 = ["id"];
  const _hoisted_31 = { key: 0 };
  const _hoisted_32 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_33 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_34 = { class: "" };
  const _hoisted_35 = { class: "editingSiteFooter" };
  const _hoisted_36 = ["disabled", "value"];
  const _hoisted_37 = ["disabled"];
  const _hoisted_38 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    var _a, _b, _c;
    const _component_Field = vue.resolveComponent("Field");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_GroupedSettings = vue.resolveComponent("GroupedSettings");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["site card hoverable", { "editingSite": _ctx.editMode }]),
      idsite: _ctx.theSite.idsite,
      type: _ctx.theSite.type,
      ref: "root"
    }, [
      vue.createElementVNode("div", _hoisted_2$4, [
        !_ctx.editMode ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$4, [
          vue.createElementVNode("div", _hoisted_4$4, [
            vue.createElementVNode("h4", null, vue.toDisplayString(_ctx.theSite.name), 1),
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_5$4, vue.toDisplayString(_ctx.translate("General_Id")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.idsite), 1)
              ]),
              vue.withDirectives(vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_6$4, vue.toDisplayString(_ctx.translate("SitesManager_Type")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.currentType.name), 1)
              ], 512), [
                [vue.vShow, _ctx.availableTypes.length > 1]
              ]),
              vue.withDirectives(vue.createElementVNode("li", null, [
                vue.createElementVNode("a", {
                  target: _ctx.isInternalSetupUrl ? "_self" : "_blank",
                  title: _ctx.translate("SitesManager_ShowTrackingTag"),
                  href: _ctx.setupUrl
                }, vue.toDisplayString(_ctx.translate("SitesManager_ShowTrackingTag")), 9, _hoisted_7$4)
              ], 512), [
                [vue.vShow, _ctx.theSite.idsite && _ctx.howToSetupUrl]
              ])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_8$4, [
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_9$4, vue.toDisplayString(_ctx.translate("SitesManager_Timezone")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.timezone_name), 1)
              ]),
              vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_10$4, vue.toDisplayString(_ctx.translate("SitesManager_Currency")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.currency_name), 1)
              ]),
              vue.withDirectives(vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_11$4, vue.toDisplayString(_ctx.translate("Goals_Ecommerce")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Yes")), 1)
              ], 512), [
                [vue.vShow, _ctx.theSite.ecommerce === 1 || _ctx.theSite.ecommerce === "1"]
              ]),
              vue.withDirectives(vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_12$3, vue.toDisplayString(_ctx.translate("Actions_SubmenuSitesearch")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_Yes")), 1)
              ], 512), [
                [vue.vShow, _ctx.theSite.sitesearch === 1 || _ctx.theSite.sitesearch === "1"]
              ])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_13$3, [
            vue.createElementVNode("ul", null, [
              vue.createElementVNode("li", null, [
                vue.createElementVNode("span", _hoisted_14$3, vue.toDisplayString(_ctx.translate("SitesManager_Urls")), 1),
                vue.createTextVNode(": "),
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.theSite.alias_urls, (url, index) => {
                  return vue.openBlock(), vue.createElementBlock("span", { key: url }, [
                    vue.createElementVNode("a", {
                      target: "_blank",
                      rel: "noreferrer noopener",
                      href: url
                    }, vue.toDisplayString(url) + vue.toDisplayString(index === _ctx.theSite.alias_urls.length - 1 ? "" : ", "), 9, _hoisted_15$2)
                  ]);
                }), 128))
              ]),
              ((_a = _ctx.theSite.excluded_ips) == null ? void 0 : _a.length) ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_16$2, [
                vue.createElementVNode("span", _hoisted_17$1, vue.toDisplayString(_ctx.translate("SitesManager_ExcludedIps")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.excluded_ips.split(/\s*,\s*/g).join(", ")), 1)
              ])) : vue.createCommentVNode("", true),
              ((_b = _ctx.theSite.excluded_parameters) == null ? void 0 : _b.length) ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_18$1, [
                vue.createElementVNode("span", _hoisted_19$1, vue.toDisplayString(_ctx.translate("SitesManager_ExcludedParameters")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.excluded_parameters.split(/\s*,\s*/g).join(", ")), 1)
              ])) : vue.createCommentVNode("", true),
              ((_c = _ctx.theSite.excluded_user_agents) == null ? void 0 : _c.length) ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_20$1, [
                vue.createElementVNode("span", _hoisted_21$1, vue.toDisplayString(_ctx.translate("SitesManager_ExcludedUserAgents")) + ":", 1),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.theSite.excluded_user_agents.split(/\s*,\s*/g).join(", ")), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_22$1, [
            vue.createElementVNode("button", {
              class: "table-action",
              onClick: _cache[0] || (_cache[0] = ($event) => _ctx.editSite()),
              title: _ctx.translate("General_Edit")
            }, _hoisted_25, 8, _hoisted_23),
            vue.withDirectives(vue.createElementVNode("button", {
              class: "table-action",
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.getMessagesToWarnOnSiteRemoval()),
              title: _ctx.translate("General_Delete")
            }, _hoisted_28, 8, _hoisted_26), [
              [vue.vShow, _ctx.theSite.idsite]
            ])
          ])
        ])) : vue.createCommentVNode("", true),
        _ctx.editMode ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_29, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "siteName",
            modelValue: _ctx.theSite.name,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.theSite.name = $event),
            maxlength: 90,
            title: _ctx.translate("General_Name"),
            placeholder: _ctx.translate("SitesManager_MeasurableNamePlaceholder"),
            "inline-help": _ctx.translate("SitesManager_MeasurableNameHelpText")
          }, null, 8, ["modelValue", "title", "placeholder", "inline-help"]),
          vue.createVNode(_component_Field, {
            uicontrol: "textarea",
            name: "siteDescription",
            modelValue: _ctx.theSite.description,
            "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.theSite.description = $event),
            maxlength: 255,
            autocomplete: "off",
            title: `${_ctx.translate("General_Description")} ${_ctx.translate("Goals_Optional")}`,
            placeholder: _ctx.translate("SitesManager_MeasurableDescriptionPlaceholder"),
            "inline-help": _ctx.translate("SitesManager_MeasurableDescriptionHelpText"),
            "ui-control-attributes": { class: "compact-textarea" }
          }, null, 8, ["modelValue", "title", "placeholder", "inline-help"]),
          vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoading }, null, 8, ["loading"]),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.measurableSettings, (settingsPerPlugin) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              key: settingsPerPlugin.pluginName
            }, [
              vue.createVNode(_component_GroupedSettings, {
                "group-name": settingsPerPlugin.pluginName,
                settings: settingsPerPlugin.settings,
                "all-setting-values": _ctx.settingValues,
                onChange: ($event) => _ctx.settingValues[`${settingsPerPlugin.pluginName}.${$event.name}`] = $event.value
              }, null, 8, ["group-name", "settings", "all-setting-values", "onChange"])
            ]);
          }), 128)),
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: "currency",
            modelValue: _ctx.theSite.currency,
            "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.theSite.currency = $event),
            title: _ctx.translate("SitesManager_Currency"),
            "inline-help": _ctx.translate("SitesManager_CurrencySymbolWillBeUsedForGoals"),
            options: _ctx.currencies
          }, null, 8, ["modelValue", "title", "inline-help", "options"]),
          vue.createVNode(_component_Field, {
            uicontrol: "select",
            name: "timezone",
            modelValue: _ctx.theSite.timezone,
            "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.theSite.timezone = $event),
            title: _ctx.translate("SitesManager_Timezone"),
            "inline-help": `#timezoneHelpText-${_ctx.theSite.idsite}`,
            options: _ctx.timezones
          }, null, 8, ["modelValue", "title", "inline-help", "options"]),
          vue.createElementVNode("div", {
            id: `timezoneHelpText-${_ctx.theSite.idsite}`,
            class: "inline-help-node"
          }, [
            vue.createElementVNode("div", null, [
              !_ctx.timezoneSupportEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_31, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_AdvancedTimezoneSupportNotFound")) + " ", 1),
                _hoisted_32
              ])) : vue.createCommentVNode("", true),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.utcTimeIs) + " ", 1),
              _hoisted_33,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward")), 1)
            ])
          ], 8, _hoisted_30),
          _ctx.privacyManagerEnabled && _ctx.theSite && _ctx.theSite.idsite ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
            vue.createElementVNode("h3", _hoisted_34, vue.toDisplayString(_ctx.translate("PrivacyManager_TrackingDataAnonymizationSettings")), 1),
            vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoadingPrivacy }, null, 8, ["loading"]),
            !_ctx.isLoadingPrivacy ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.anonymizeIpComponent), vue.mergeProps({
              key: 0,
              "id-site-specific": _ctx.theSite.idsite,
              "trigger-save": _ctx.triggerSavePrivacySettings == "save"
            }, _ctx.anonymisationSettings, {
              onUpdated: _ctx.onPrivacyUpdated,
              onAborted: _ctx.onPrivacyAborted,
              onCancel: _cache[6] || (_cache[6] = ($event) => _ctx.cancelEditSite(_ctx.site))
            }), null, 16, ["id-site-specific", "trigger-save", "onUpdated", "onAborted"])) : vue.createCommentVNode("", true)
          ], 64)) : vue.createCommentVNode("", true),
          vue.createElementVNode("div", _hoisted_35, [
            vue.withDirectives(vue.createElementVNode("input", {
              disabled: _ctx.isSaving,
              type: "submit",
              class: "btn",
              value: _ctx.translate("General_Save"),
              onClick: _cache[7] || (_cache[7] = ($event) => _ctx.saveSite())
            }, null, 8, _hoisted_36), [
              [vue.vShow, !_ctx.isLoading]
            ]),
            vue.createElementVNode("button", {
              class: "btn btn-link",
              disabled: _ctx.isSaving,
              onClick: _cache[8] || (_cache[8] = ($event) => _ctx.cancelEditSite(_ctx.site))
            }, vue.toDisplayString(_ctx.translate("General_Cancel", "", "")), 9, _hoisted_37)
          ])
        ])) : vue.createCommentVNode("", true)
      ]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showRemoveDialog,
        "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.showRemoveDialog = $event),
        onConfirmed: _ctx.deleteSite,
        "password-field-id": "currentUserPassword-" + _ctx.theSite.idsite
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.removeDialogTitle), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("SitesManager_DeleteSiteExplanation")), 1),
          _ctx.deleteSiteExplanation ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.deleteSiteExplanation)
          }, null, 8, _hoisted_38)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "password-field-id"])
    ], 10, _hoisted_1$4);
  }
  const SiteFields = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class GlobalSettingsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isLoading: false,
        globalSettings: {
          keepURLFragmentsGlobal: false,
          defaultCurrency: "",
          defaultTimezone: "",
          excludedIpsGlobal: "",
          excludedQueryParametersGlobal: "",
          excludedUserAgentsGlobal: "",
          excludedReferrersGlobal: "",
          searchKeywordParametersGlobal: "",
          searchCategoryParametersGlobal: "",
          exclusionTypeForQueryParams: ""
        }
      }));
      __publicField(this, "isLoading", vue.computed(() => vue.readonly(this.privateState).isLoading));
      __publicField(this, "globalSettings", vue.computed(() => vue.readonly(this.privateState).globalSettings));
    }
    init() {
      return this.fetchGlobalSettings();
    }
    saveGlobalSettings(settings) {
      this.privateState.isLoading = true;
      return CoreHome.AjaxHelper.post(
        {
          module: "SitesManager",
          format: "json",
          action: "setGlobalSettings"
        },
        settings,
        {
          withTokenInUrl: true
        }
      ).finally(() => {
        this.privateState.isLoading = false;
      });
    }
    fetchGlobalSettings() {
      this.privateState.isLoading = true;
      CoreHome.AjaxHelper.fetch({
        module: "SitesManager",
        action: "getGlobalSettings"
      }).then((response) => {
        this.privateState.globalSettings = __spreadProps(__spreadValues({}, response), {
          // the API can return false for these
          excludedIpsGlobal: response.excludedIpsGlobal || "",
          excludedQueryParametersGlobal: response.excludedQueryParametersGlobal || "",
          excludedUserAgentsGlobal: response.excludedUserAgentsGlobal || "",
          excludedReferrersGlobal: response.excludedReferrersGlobal || "",
          searchKeywordParametersGlobal: response.searchKeywordParametersGlobal || "",
          searchCategoryParametersGlobal: response.searchCategoryParametersGlobal || "",
          exclusionTypeForQueryParams: response.exclusionTypeForQueryParams || ""
        });
      }).finally(() => {
        this.privateState.isLoading = false;
      });
    }
  }
  const GlobalSettingsStore$1 = new GlobalSettingsStore();
  const _sfc_main$3 = vue.defineComponent({
    props: {
      rollUpEnabled: Boolean,
      privacyManagerEnabled: Boolean
    },
    components: {
      ButtonBar,
      ContentBlock: CoreHome.ContentBlock,
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      MatomoDialog: CoreHome.MatomoDialog,
      MatomoLoader: CoreHome.MatomoLoader,
      SiteFields
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    data() {
      const currentDate = /* @__PURE__ */ new Date();
      const utcTime = new Date(
        currentDate.getUTCFullYear(),
        currentDate.getUTCMonth(),
        currentDate.getUTCDate(),
        currentDate.getUTCHours(),
        currentDate.getUTCMinutes(),
        currentDate.getUTCSeconds()
      );
      return {
        pageSize: 10,
        currentPage: 0,
        showAddSiteDialog: false,
        searchTerm: "",
        activeSearchTerm: "",
        fetchedSites: [],
        isLoadingInitialEntities: false,
        utcTime,
        totalNumberOfSites: null,
        isSiteBeingEdited: false,
        fetchLimitedSitesAbortController: null
      };
    },
    created() {
      TimezoneStore$1.init();
      SiteTypesStore$1.init();
      GlobalSettingsStore$1.init();
      this.isLoadingInitialEntities = true;
      Promise.all([
        SiteTypesStore$1.fetchAvailableTypes(),
        this.fetchLimitedSitesWithAdminAccess(),
        this.getTotalNumberOfSites()
      ]).then(() => {
        this.triggerAddSiteIfRequested();
      }).finally(() => {
        this.isLoadingInitialEntities = false;
      });
      vue.watch(() => CoreHome.MatomoUrl.hashQuery.value, () => {
        this.checkGlobalSettingsHash();
      });
    },
    computed: {
      sites() {
        const emptyIdSiteRows = this.fetchedSites.filter((s) => !s.idsite).length;
        return this.fetchedSites.slice(0, this.pageSize + emptyIdSiteRows);
      },
      isLoading() {
        return !!this.fetchLimitedSitesAbortController || this.isLoadingInitialEntities || this.totalNumberOfSites === null || SiteTypesStore$1.isLoading.value || TimezoneStore$1.isLoading.value || GlobalSettingsStore$1.isLoading.value;
      },
      availableTypes() {
        return SiteTypesStore$1.types.value;
      },
      timezoneSupportEnabled() {
        return TimezoneStore$1.timezoneSupportEnabled.value;
      },
      globalSettings() {
        return GlobalSettingsStore$1.globalSettings.value;
      },
      headlineText() {
        return CoreHome.translate(
          "SitesManager_XManagement",
          this.availableTypes.length > 1 ? CoreHome.translate("General_Measurables") : CoreHome.translate("SitesManager_Sites")
        );
      },
      subheaderText() {
        const subheader = CoreHome.translate("SitesManager_ChooseMeasurableTypeSubheader");
        const rollup = this.rollUpEnabled ? CoreHome.translate("SitesManager_ChooseMeasurableTypeSubheaderRollUp") : "";
        return `${subheader} ${rollup}`.trim();
      },
      mainDescription() {
        return CoreHome.translate(
          "SitesManager_YouCurrentlyHaveAccessToNWebsites",
          `<strong>${this.totalNumberOfSites}</strong>`
        );
      },
      hasSuperUserAccess() {
        return CoreHome.Matomo.hasSuperUserAccess;
      },
      superUserAccessMessage() {
        return CoreHome.translate("SitesManager_SuperUserAccessCan", "<a href='#globalSettings'>", "</a>");
      },
      hasPrev() {
        return this.currentPage >= 1;
      },
      hasNext() {
        return this.fetchedSites.filter((s) => !!s.idsite).length >= this.pageSize + 1;
      },
      offsetStart() {
        return this.currentPage * this.pageSize + 1;
      },
      offsetEnd() {
        return this.offsetStart + this.sites.filter((s) => !!s.idsite).length - 1;
      }
    },
    methods: {
      checkGlobalSettingsHash() {
        const newHash = CoreHome.MatomoUrl.hashQuery.value;
        if (CoreHome.Matomo.hasSuperUserAccess && (newHash === "globalSettings" || newHash === "/globalSettings")) {
          CoreHome.MatomoUrl.updateLocation(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
            action: "globalSettings"
          }));
        }
      },
      addNewEntity() {
        if (this.availableTypes.length > 1) {
          this.showAddSiteDialog = true;
        } else if (this.availableTypes.length === 1) {
          this.addSite(this.availableTypes[0].id);
        }
      },
      addSite(typeId) {
        let type = typeId;
        const parameters = {
          isAllowed: true,
          measurableType: type
        };
        CoreHome.Matomo.postEvent("SitesManager.initAddSite", parameters);
        if (parameters && !parameters.isAllowed) {
          return;
        }
        if (!type) {
          type = "website";
        }
        this.fetchedSites.unshift({
          type
        });
        this.isSiteBeingEdited = true;
      },
      afterCancelEdit({ site, element }) {
        this.isSiteBeingEdited = false;
        if (!site.idsite) {
          this.fetchedSites = this.fetchedSites.filter((s) => !!s.idsite);
          return;
        }
        element.scrollIntoView();
      },
      fetchLimitedSitesWithAdminAccess(searchTerm = "") {
        if (this.fetchLimitedSitesAbortController) {
          this.fetchLimitedSitesAbortController.abort();
        }
        this.fetchLimitedSitesAbortController = new AbortController();
        const limit = this.pageSize + 1;
        const offset = this.currentPage * this.pageSize;
        const params = {
          method: "SitesManager.getSitesWithAdminAccess",
          fetchAliasUrls: 1,
          limit: limit + offset,
          // this is applied in SitesManager.getSitesWithAdminAccess API
          filter_offset: offset,
          // filter_offset and filter_limit is applied in response builder
          filter_limit: limit
        };
        if (searchTerm) {
          params.pattern = searchTerm;
        }
        return CoreHome.AjaxHelper.fetch(params).then((sites) => {
          this.fetchedSites = sites || [];
        }).then((sites) => {
          this.activeSearchTerm = searchTerm;
          return sites;
        }).finally(() => {
          this.fetchLimitedSitesAbortController = null;
        });
      },
      getTotalNumberOfSites() {
        return CoreHome.AjaxHelper.fetch({
          method: "SitesManager.getSitesIdWithAdminAccess",
          filter_limit: "-1"
        }).then((sites) => {
          this.totalNumberOfSites = sites.length;
        });
      },
      triggerAddSiteIfRequested() {
        const forcedEditSiteId = SiteTypesStore$1.getEditSiteIdParameter();
        const showaddsite = CoreHome.MatomoUrl.urlParsed.value.showaddsite;
        if (showaddsite === "1") {
          this.addNewEntity();
        } else if (forcedEditSiteId) {
          this.searchTerm = forcedEditSiteId;
          this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
        }
      },
      previousPage() {
        this.currentPage = Math.max(0, this.currentPage - 1);
        this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
      },
      nextPage() {
        this.currentPage = Math.max(0, this.currentPage + 1);
        this.fetchLimitedSitesWithAdminAccess(this.activeSearchTerm);
      },
      searchSites() {
        this.currentPage = 0;
        this.fetchLimitedSitesWithAdminAccess(this.searchTerm);
      },
      afterDelete(site) {
        let redirectParams = {
          showaddsite: 0
        };
        if (CoreHome.MatomoUrl.urlParsed.value.idSite === `${site.idsite}`) {
          const otherSite = this.sites.find((s) => s.idsite !== site.idsite);
          if (otherSite) {
            redirectParams = __spreadProps(__spreadValues({}, redirectParams), { idSite: otherSite.idsite });
          }
        }
        CoreHome.Matomo.helper.redirect(redirectParams);
      },
      afterSave(site, settingValues, index, isNew) {
        const texttareaArrayParams = [
          "excluded_ips",
          "excluded_parameters",
          "excluded_user_agents",
          "sitesearch_keyword_parameters",
          "sitesearch_category_parameters"
        ];
        const newSite = __spreadValues({}, site);
        Object.values(settingValues).forEach((settings) => {
          settings.forEach((setting) => {
            if (setting.name === "urls") {
              newSite.alias_urls = setting.value;
            } else if (texttareaArrayParams.indexOf(setting.name) !== -1) {
              newSite[setting.name] = setting.value.join(", ");
            } else {
              newSite[setting.name] = setting.value;
            }
          });
        });
        this.fetchedSites[index] = newSite;
        if (isNew && this.totalNumberOfSites !== null) {
          this.totalNumberOfSites += 1;
        }
        this.isSiteBeingEdited = false;
      }
    }
  });
  const _hoisted_1$3 = {
    class: "SitesManager",
    ref: "root"
  };
  const _hoisted_2$3 = { class: "sites-manager-header" };
  const _hoisted_3$3 = ["innerHTML"];
  const _hoisted_4$3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_5$3 = ["innerHTML"];
  const _hoisted_6$3 = { class: "loadingPiwik" };
  const _hoisted_7$3 = { class: "ui-confirm add-site-dialog" };
  const _hoisted_8$3 = { class: "center" };
  const _hoisted_9$3 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_10$3 = { class: "card-row" };
  const _hoisted_11$3 = { class: "center" };
  const _hoisted_12$2 = ["title", "onClick"];
  const _hoisted_13$2 = { class: "ui-button-text" };
  const _hoisted_14$2 = { class: "sitesManagerList" };
  const _hoisted_15$1 = { key: 0 };
  const _hoisted_16$1 = { class: "bottomButtonBar" };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_ButtonBar = vue.resolveComponent("ButtonBar");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_MatomoDialog = vue.resolveComponent("MatomoDialog");
    const _component_SiteFields = vue.resolveComponent("SiteFields");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      vue.createElementVNode("div", _hoisted_2$3, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
          vue.withDirectives(vue.createElementVNode("h2", null, [
            vue.createVNode(_component_EnrichedHeadline, {
              "help-url": _ctx.externalRawLink("https://matomo.org/docs/manage-websites/"),
              "feature-name": _ctx.translate("SitesManager_WebsitesManagement")
            }, {
              default: vue.withCtx(() => [
                vue.createTextVNode(vue.toDisplayString(_ctx.headlineText), 1)
              ]),
              _: 1
            }, 8, ["help-url", "feature-name"])
          ], 512), [
            [vue.vShow, _ctx.availableTypes.length]
          ]),
          vue.createElementVNode("p", null, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_MainDescription")) + " ", 1),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.mainDescription)
            }, null, 8, _hoisted_3$3),
            vue.withDirectives(vue.createElementVNode("span", null, [
              _hoisted_4$3,
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.superUserAccessMessage)
              }, null, 8, _hoisted_5$3)
            ], 512), [
              [vue.vShow, _ctx.hasSuperUserAccess]
            ])
          ])
        ])), [
          [_directive_content_intro]
        ])
      ]),
      vue.createElementVNode("div", null, [
        vue.createElementVNode("div", {
          class: vue.normalizeClass({ hide_only: !_ctx.isLoading })
        }, [
          vue.createElementVNode("div", _hoisted_6$3, [
            vue.createVNode(_component_MatomoLoader),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
          ])
        ], 2)
      ]),
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_ButtonBar, {
          "site-is-being-edited": _ctx.isSiteBeingEdited,
          "has-prev": _ctx.hasPrev,
          hasNext: _ctx.hasNext,
          "offset-start": _ctx.offsetStart,
          "offset-end": _ctx.offsetEnd,
          "total-number-of-sites": _ctx.totalNumberOfSites,
          "is-loading": _ctx.isLoading,
          "search-term": _ctx.searchTerm,
          "is-searching": !!_ctx.activeSearchTerm,
          "onUpdate:searchTerm": _cache[0] || (_cache[0] = ($event) => _ctx.searchTerm = $event),
          onAdd: _cache[1] || (_cache[1] = ($event) => _ctx.addNewEntity()),
          onSearch: _cache[2] || (_cache[2] = ($event) => _ctx.searchSites($event)),
          onPrev: _cache[3] || (_cache[3] = ($event) => _ctx.previousPage()),
          onNext: _cache[4] || (_cache[4] = ($event) => _ctx.nextPage())
        }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])
      ]),
      vue.createVNode(_component_MatomoDialog, {
        modelValue: _ctx.showAddSiteDialog,
        "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.showAddSiteDialog = $event),
        options: { classes: _ctx.availableTypes.length > 2 ? "sites-manager-modal" : "" }
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_7$3, [
            vue.createElementVNode("div", null, [
              vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("SitesManager_ChooseMeasurableTypeHeadline")), 1),
              vue.createElementVNode("div", _hoisted_8$3, [
                vue.createElementVNode("p", null, vue.toDisplayString(_ctx.subheaderText), 1),
                _hoisted_9$3
              ]),
              vue.createElementVNode("div", _hoisted_10$3, [
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.availableTypes, (type) => {
                  return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
                    key: type.id,
                    "content-title": type.name
                  }, {
                    default: vue.withCtx(() => [
                      vue.createElementVNode("p", _hoisted_11$3, vue.toDisplayString(type.longDescription), 1),
                      vue.createElementVNode("button", {
                        type: "button",
                        title: type.description,
                        class: "modal-close btn btn-block",
                        onClick: ($event) => _ctx.addSite(type.id),
                        "aria-disabled": "false"
                      }, [
                        vue.createElementVNode("span", _hoisted_13$2, vue.toDisplayString(type.name), 1)
                      ], 8, _hoisted_12$2)
                    ]),
                    _: 2
                  }, 1032, ["content-title"]);
                }), 128))
              ])
            ])
          ])
        ]),
        _: 1
      }, 8, ["modelValue", "options"]),
      vue.createElementVNode("div", _hoisted_14$2, [
        _ctx.activeSearchTerm && 0 === _ctx.sites.length && !_ctx.isLoading ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_15$1, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_NotFound")) + " ", 1),
          vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.activeSearchTerm), 1)
        ])) : vue.createCommentVNode("", true),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.sites, (site, index) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            key: site.idsite
          }, [
            vue.createVNode(_component_SiteFields, {
              site,
              "timezone-support-enabled": _ctx.timezoneSupportEnabled,
              "utc-time": _ctx.utcTime,
              "global-settings": _ctx.globalSettings,
              "privacy-manager-enabled": _ctx.privacyManagerEnabled,
              onEditSite: _cache[6] || (_cache[6] = ($event) => this.isSiteBeingEdited = true),
              onCancelEditSite: _cache[7] || (_cache[7] = ($event) => _ctx.afterCancelEdit($event)),
              onCancelEditPrivacy: _cache[8] || (_cache[8] = ($event) => _ctx.afterCancelEdit($event)),
              onDelete: _cache[9] || (_cache[9] = ($event) => _ctx.afterDelete($event)),
              onSave: ($event) => _ctx.afterSave($event.site, $event.settingValues, index, $event.isNew)
            }, null, 8, ["site", "timezone-support-enabled", "utc-time", "global-settings", "privacy-manager-enabled", "onSave"])
          ]);
        }), 128))
      ]),
      vue.createElementVNode("div", _hoisted_16$1, [
        vue.createVNode(_component_ButtonBar, {
          "site-is-being-edited": _ctx.isSiteBeingEdited,
          "has-prev": _ctx.hasPrev,
          hasNext: _ctx.hasNext,
          "offset-start": _ctx.offsetStart,
          "offset-end": _ctx.offsetEnd,
          "total-number-of-sites": _ctx.totalNumberOfSites,
          "is-loading": _ctx.isLoading,
          "search-term": _ctx.searchTerm,
          "is-searching": !!_ctx.activeSearchTerm,
          "onUpdate:searchTerm": _cache[10] || (_cache[10] = ($event) => _ctx.searchTerm = $event),
          onAdd: _cache[11] || (_cache[11] = ($event) => _ctx.addNewEntity()),
          onSearch: _cache[12] || (_cache[12] = ($event) => _ctx.searchSites($event)),
          onPrev: _cache[13] || (_cache[13] = ($event) => _ctx.previousPage()),
          onNext: _cache[14] || (_cache[14] = ($event) => _ctx.nextPage())
        }, null, 8, ["site-is-being-edited", "has-prev", "hasNext", "offset-start", "offset-end", "total-number-of-sites", "is-loading", "search-term", "is-searching"])
      ])
    ], 512);
  }
  const SitesManagement = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    components: {
      Field: CorePluginsAdmin.Field
    },
    props: {
      exclusionTypeForQueryParams: {
        type: String,
        default: "common_session_parameters"
      },
      excludedQueryParametersGlobal: {
        type: Array,
        default: () => []
      },
      commonSensitiveQueryParams: {
        type: Array,
        default: () => []
      }
    },
    data() {
      return {
        localExclusionTypeForQueryParams: this.exclusionTypeForQueryParams,
        localExcludedQueryParametersGlobal: this.excludedQueryParametersGlobal,
        exclusionTypeOptions: [
          {
            value: CoreHome.translate("SitesManager_ExclusionTypeOptionCommonSessionParameters"),
            key: "common_session_parameters"
          },
          {
            value: CoreHome.translate("SitesManager_ExclusionTypeOptionMatomoRecommendedPII"),
            key: "matomo_recommended_pii"
          },
          {
            value: CoreHome.translate("SitesManager_ExclusionTypeOptionCustom"),
            key: "custom"
          }
        ],
        showListOfCommonExclusions: false
      };
    },
    watch: {
      exclusionTypeForQueryParams: {
        handler(newExclusionType) {
          this.localExclusionTypeForQueryParams = newExclusionType;
        }
      },
      localExclusionTypeForQueryParams: {
        handler(newExclusionType) {
          this.updateExclusionType(newExclusionType);
        },
        immediate: true
      },
      excludedQueryParametersGlobal: {
        handler(excludedQueryParametersGlobal) {
          this.localExcludedQueryParametersGlobal = excludedQueryParametersGlobal;
        }
      }
    },
    methods: {
      updateExclusionType(value) {
        if (value !== "custom") {
          this.localExcludedQueryParametersGlobal = [];
          this.onInputExcludedQueryParametersGlobal("");
        }
        this.$emit("update:exclusionTypeForQueryParams", value);
      },
      onInputExcludedQueryParametersGlobal(value) {
        const valueArray = value.split("\n");
        this.$emit("update:excludedQueryParametersGlobal", valueArray);
      },
      addCommonPIIQueryParams() {
        let updatedParams = this.localExcludedQueryParametersGlobal.filter(
          (param) => !this.commonSensitiveQueryParams.includes(param)
        );
        updatedParams = updatedParams.concat(this.commonSensitiveQueryParams);
        this.localExcludedQueryParametersGlobal = updatedParams;
        this.$emit("update:excludedQueryParametersGlobal", updatedParams);
      }
    }
  });
  const _hoisted_1$2 = { class: "siteManagerGlobalExcludedUrlParameters" };
  const _hoisted_2$2 = {
    id: "excludedQueryParametersGlobalHelp",
    class: "inline-help-node"
  };
  const _hoisted_3$2 = {
    id: "excludedQueryParametersGlobalExclusionTypeHelp",
    class: "inline-help-node"
  };
  const _hoisted_4$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-chevron-down" }, null, -1);
  const _hoisted_5$2 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-chevron-up" }, null, -1);
  const _hoisted_6$2 = { key: 0 };
  const _hoisted_7$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_8$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_9$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_10$2 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_11$2 = ["value"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createElementVNode("div", _hoisted_2$2, [
        vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("SitesManager_ListOfQueryParametersToExclude", "/^sess.*|.*[dD]ate$/")), 1)
      ]),
      vue.createElementVNode("div", _hoisted_3$2, [
        vue.withDirectives(vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("SitesManager_ExclusionTypeDescriptionCommonSessionParameters")), 513), [
          [vue.vShow, _ctx.localExclusionTypeForQueryParams === "common_session_parameters"]
        ]),
        vue.withDirectives(vue.createElementVNode("div", null, [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("SitesManager_ExclusionTypeDescriptionMatomoRecommendedPII")), 1),
          vue.createElementVNode("div", null, [
            !_ctx.showListOfCommonExclusions ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 0,
              href: "javascript:;",
              onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.showListOfCommonExclusions = true, ["prevent"]))
            }, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_ExclusionViewListLink")) + " ", 1),
              _hoisted_4$2
            ])) : vue.createCommentVNode("", true),
            _ctx.showListOfCommonExclusions ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 1,
              href: "javascript:;",
              onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.showListOfCommonExclusions = false, ["prevent"]))
            }, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_ExclusionViewListLink")) + " ", 1),
              _hoisted_5$2
            ])) : vue.createCommentVNode("", true)
          ]),
          _ctx.showListOfCommonExclusions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$2, vue.toDisplayString(_ctx.commonSensitiveQueryParams.join(", ")), 1)) : vue.createCommentVNode("", true),
          _hoisted_7$2,
          _hoisted_8$2,
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(
            "SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition",
            "phpsessid, sessionid, ..."
          )), 1)
        ], 512), [
          [vue.vShow, _ctx.localExclusionTypeForQueryParams === "matomo_recommended_pii"]
        ]),
        vue.withDirectives(vue.createElementVNode("div", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_ExclusionTypeDescriptionCustom")) + " ", 1),
          _hoisted_9$2,
          _hoisted_10$2,
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(
            "SitesManager_MatomoWillAutomaticallyExcludeCommonSessionParametersInAddition",
            "phpsessid, sessionid, ..."
          )), 1)
        ], 512), [
          [vue.vShow, _ctx.localExclusionTypeForQueryParams === "custom"]
        ])
      ]),
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "radio",
          name: "exclusionType",
          introduction: _ctx.translate("SitesManager_GlobalListExcludedQueryParameters"),
          options: _ctx.exclusionTypeOptions,
          modelValue: _ctx.localExclusionTypeForQueryParams,
          "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.localExclusionTypeForQueryParams = $event),
          "inline-help": "#excludedQueryParametersGlobalExclusionTypeHelp"
        }, null, 8, ["introduction", "options", "modelValue"])
      ]),
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "textarea",
          name: "excludedQueryParametersGlobal",
          "var-type": "array",
          class: "limited-height-scrolling-textarea",
          modelValue: _ctx.localExcludedQueryParametersGlobal,
          "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.localExcludedQueryParametersGlobal = $event),
          "model-value": _ctx.localExcludedQueryParametersGlobal.join("\n"),
          onInput: _cache[4] || (_cache[4] = ($event) => _ctx.onInputExcludedQueryParametersGlobal($event.target.value)),
          title: _ctx.translate("SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites"),
          "inline-help": "#excludedQueryParametersGlobalHelp"
        }, null, 8, ["modelValue", "model-value", "title"]),
        vue.createElementVNode("input", {
          type: "button",
          onClick: _cache[5] || (_cache[5] = ($event) => _ctx.addCommonPIIQueryParams()),
          class: "btn",
          value: _ctx.translate("SitesManager_AddSensibleExclusionsToMyCustomListButtonText")
        }, null, 8, _hoisted_11$2)
      ], 512), [
        [vue.vShow, _ctx.localExclusionTypeForQueryParams === "custom"]
      ])
    ]);
  }
  const ExcludeQueryParameterSettings = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    components: {
      ExcludeQueryParameterSettings,
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    props: {
      commonSensitiveQueryParams: {
        type: Array,
        default: () => []
      }
    },
    data() {
      const currentDate = /* @__PURE__ */ new Date();
      const utcTime = new Date(
        currentDate.getUTCFullYear(),
        currentDate.getUTCMonth(),
        currentDate.getUTCDate(),
        currentDate.getUTCHours(),
        currentDate.getUTCMinutes(),
        currentDate.getUTCSeconds()
      );
      const settings = GlobalSettingsStore$1.globalSettings.value;
      return {
        currentIpAddress: null,
        utcTime,
        keepURLFragmentsGlobal: settings.keepURLFragmentsGlobal,
        defaultTimezone: settings.defaultTimezone,
        defaultCurrency: settings.defaultCurrency,
        excludedIpsGlobal: (settings.excludedIpsGlobal || "").split(","),
        excludedQueryParametersGlobal: (settings.excludedQueryParametersGlobal || "").split(","),
        excludedUserAgentsGlobal: (settings.excludedUserAgentsGlobal || "").split(","),
        excludedReferrersGlobal: (settings.excludedReferrersGlobal || "").split(","),
        searchKeywordParametersGlobal: (settings.searchKeywordParametersGlobal || "").split(","),
        searchCategoryParametersGlobal: (settings.searchCategoryParametersGlobal || "").split(","),
        isSaving: false,
        exclusionTypeForQueryParams: settings.exclusionTypeForQueryParams
      };
    },
    created() {
      CurrencyStore$1.init();
      TimezoneStore$1.init();
      GlobalSettingsStore$1.init();
      vue.watch(() => GlobalSettingsStore$1.globalSettings.value, (settings) => {
        this.keepURLFragmentsGlobal = settings.keepURLFragmentsGlobal;
        this.defaultTimezone = settings.defaultTimezone;
        this.defaultCurrency = settings.defaultCurrency;
        this.excludedIpsGlobal = (settings.excludedIpsGlobal || "").split(",");
        this.excludedQueryParametersGlobal = (settings.excludedQueryParametersGlobal || "").split(",");
        this.excludedUserAgentsGlobal = (settings.excludedUserAgentsGlobal || "").split(",");
        this.excludedReferrersGlobal = (settings.excludedReferrersGlobal || "").split(",");
        this.searchKeywordParametersGlobal = (settings.searchKeywordParametersGlobal || "").split(",");
        this.searchCategoryParametersGlobal = (settings.searchCategoryParametersGlobal || "").split(",");
        this.exclusionTypeForQueryParams = settings.exclusionTypeForQueryParams;
      });
      CoreHome.AjaxHelper.fetch({ method: "API.getIpFromHeader" }).then((response) => {
        this.currentIpAddress = response.value;
      });
    },
    methods: {
      saveGlobalSettings() {
        this.isSaving = true;
        GlobalSettingsStore$1.saveGlobalSettings({
          keepURLFragments: this.keepURLFragmentsGlobal,
          currency: this.defaultCurrency,
          timezone: this.defaultTimezone,
          excludedIps: this.excludedIpsGlobal.join(","),
          excludedQueryParameters: this.excludedQueryParametersGlobal.join(","),
          excludedUserAgents: this.excludedUserAgentsGlobal.join(","),
          excludedReferrers: this.excludedReferrersGlobal.join(","),
          searchKeywordParameters: this.searchKeywordParametersGlobal.join(","),
          searchCategoryParameters: this.searchCategoryParametersGlobal.join(","),
          exclusionTypeForQueryParams: this.exclusionTypeForQueryParams
        }).then(() => {
          CoreHome.Matomo.helper.redirect({ showaddsite: false });
        }).finally(() => {
          this.isSaving = false;
        });
      }
    },
    computed: {
      isLoading() {
        return GlobalSettingsStore$1.isLoading.value || TimezoneStore$1.isLoading.value || CurrencyStore$1.isLoading.value;
      },
      timezones() {
        return TimezoneStore$1.timezones.value;
      },
      timezoneOptions() {
        return this.timezones.map(({ group, label, code }) => ({ group, key: label, value: code }));
      },
      currencies() {
        return CurrencyStore$1.currencies.value;
      },
      hasSuperUserAccess() {
        return CoreHome.Matomo.hasSuperUserAccess;
      },
      yourCurrentIpAddressIs() {
        return CoreHome.translate("SitesManager_YourCurrentIpAddressIs", `<i>${this.currentIpAddress}</i>`);
      },
      timezoneSupportEnabled() {
        return TimezoneStore$1.timezoneSupportEnabled.value;
      },
      utcTimeDate() {
        const { utcTime } = this;
        const formatTimePart = (n) => n.toString().padStart(2, "0");
        const hours = formatTimePart(utcTime.getHours());
        const minutes = formatTimePart(utcTime.getMinutes());
        const seconds = formatTimePart(utcTime.getSeconds());
        return `${CoreHome.format(this.utcTime)} ${hours}:${minutes}:${seconds}`;
      },
      keepUrlFragmentHelp() {
        return CoreHome.translate(
          "SitesManager_KeepURLFragmentsHelp",
          "<em>#</em>",
          "<em>example.org/index.html#first_section</em>",
          "<em>example.org/index.html</em>"
        );
      },
      searchCategoryParamsInlineHelp() {
        const parts = [
          CoreHome.translate("Goals_Optional"),
          CoreHome.translate("SitesManager_SearchCategoryDesc"),
          CoreHome.translate("SitesManager_SearchCategoryParametersDesc")
        ];
        return parts.join(" ");
      }
    }
  });
  const _hoisted_1$1 = { class: "SitesManager" };
  const _hoisted_2$1 = /* @__PURE__ */ vue.createElementVNode("a", {
    name: "globalSettings",
    id: "globalSettings"
  }, null, -1);
  const _hoisted_3$1 = {
    id: "excludedIpsGlobalHelp",
    class: "inline-help-node"
  };
  const _hoisted_4$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_5$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_6$1 = ["innerHTML"];
  const _hoisted_7$1 = {
    id: "excludedUserAgentsGlobalHelp",
    class: "inline-help-node"
  };
  const _hoisted_8$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_9$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_10$1 = {
    id: "excludedReferrersGlobalHelp",
    class: "inline-help-node"
  };
  const _hoisted_11$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_12$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_13$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_14$1 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_15 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_16 = {
    id: "timezoneHelp",
    class: "inline-help-node"
  };
  const _hoisted_17 = { key: 0 };
  const _hoisted_18 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_19 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_20 = {
    id: "keepURLFragmentsHelp",
    class: "inline-help-node"
  };
  const _hoisted_21 = ["innerHTML"];
  const _hoisted_22 = { class: "alert alert-info" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ExcludeQueryParameterSettings = vue.resolveComponent("ExcludeQueryParameterSettings");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.withDirectives(vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("SitesManager_GlobalWebsitesSettings")
      }, {
        default: vue.withCtx(() => [
          _hoisted_2$1,
          vue.createElementVNode("div", _hoisted_3$1, [
            vue.createElementVNode("div", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate(
                "SitesManager_HelpExcludedIpAddresses",
                "1.2.3.4/24",
                "1.2.3.*",
                "1.2.*.*"
              )) + " ", 1),
              _hoisted_4$1,
              _hoisted_5$1,
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.yourCurrentIpAddressIs)
              }, null, 8, _hoisted_6$1)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_7$1, [
            vue.createElementVNode("div", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_GlobalExcludedUserAgentHelp1")) + " ", 1),
              _hoisted_8$1,
              _hoisted_9$1,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_GlobalListExcludedUserAgents_Desc")) + " " + vue.toDisplayString(_ctx.translate("SitesManager_GlobalExcludedUserAgentHelp2")) + " " + vue.toDisplayString(_ctx.translate(
                "SitesManager_GlobalExcludedUserAgentHelp3",
                "/bot|spider|crawl|scanner/i"
              )), 1)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_10$1, [
            vue.createElementVNode("div", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_ExcludedReferrersHelp")) + " ", 1),
              _hoisted_11$1,
              _hoisted_12$1,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_ExcludedReferrersHelpDetails")) + " ", 1),
              _hoisted_13$1,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(
                "SitesManager_ExcludedReferrersHelpExamples",
                "www.example.org",
                "http://example.org/mypath",
                "https://www.example.org/?param=1",
                "https://sub.example.org/"
              )) + " ", 1),
              _hoisted_14$1,
              _hoisted_15,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(
                "SitesManager_ExcludedReferrersHelpSubDomains",
                ".sub.example.org",
                "http://sub.example.org/mypath",
                "https://new.sub.example.org/"
              )), 1)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_16, [
            vue.createElementVNode("div", null, [
              !_ctx.timezoneSupportEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_17, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("SitesManager_AdvancedTimezoneSupportNotFound")) + " ", 1),
                _hoisted_18
              ])) : vue.createCommentVNode("", true),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_UTCTimeIs", _ctx.utcTimeDate)) + " ", 1),
              _hoisted_19,
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward")), 1)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_20, [
            vue.createElementVNode("div", {
              innerHTML: _ctx.$sanitize(_ctx.keepUrlFragmentHelp)
            }, null, 8, _hoisted_21),
            vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("SitesManager_KeepURLFragmentsHelp2")), 1)
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "textarea",
              name: "excludedIpsGlobal",
              "var-type": "array",
              modelValue: _ctx.excludedIpsGlobal,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.excludedIpsGlobal = $event),
              title: _ctx.translate("SitesManager_ListOfIpsToBeExcludedOnAllWebsites"),
              introduction: _ctx.translate("SitesManager_GlobalListExcludedIps"),
              "inline-help": "#excludedIpsGlobalHelp",
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "introduction", "disabled"])
          ]),
          vue.createVNode(_component_ExcludeQueryParameterSettings, {
            exclusionTypeForQueryParams: _ctx.exclusionTypeForQueryParams,
            "onUpdate:exclusionTypeForQueryParams": _cache[1] || (_cache[1] = ($event) => _ctx.exclusionTypeForQueryParams = $event),
            excludedQueryParametersGlobal: _ctx.excludedQueryParametersGlobal,
            "onUpdate:excludedQueryParametersGlobal": _cache[2] || (_cache[2] = ($event) => _ctx.excludedQueryParametersGlobal = $event),
            commonSensitiveQueryParams: _ctx.commonSensitiveQueryParams
          }, null, 8, ["exclusionTypeForQueryParams", "excludedQueryParametersGlobal", "commonSensitiveQueryParams"]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "textarea",
              name: "excludedUserAgentsGlobal",
              "var-type": "array",
              modelValue: _ctx.excludedUserAgentsGlobal,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.excludedUserAgentsGlobal = $event),
              title: _ctx.translate("SitesManager_GlobalListExcludedUserAgents_Desc"),
              introduction: _ctx.translate("SitesManager_GlobalListExcludedUserAgents"),
              "inline-help": "#excludedUserAgentsGlobalHelp",
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "introduction", "disabled"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "textarea",
              name: "excludedReferrersGlobal",
              "var-type": "array",
              modelValue: _ctx.excludedReferrersGlobal,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.excludedReferrersGlobal = $event),
              title: _ctx.translate("SitesManager_GlobalListExcludedReferrersDesc"),
              introduction: _ctx.translate("SitesManager_GlobalListExcludedReferrers"),
              "inline-help": "#excludedReferrersGlobalHelp",
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "introduction", "disabled"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "keepURLFragmentsGlobal",
              modelValue: _ctx.keepURLFragmentsGlobal,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.keepURLFragmentsGlobal = $event),
              title: _ctx.translate("SitesManager_KeepURLFragmentsLong"),
              introduction: _ctx.translate("SitesManager_KeepURLFragments"),
              "inline-help": "#keepURLFragmentsHelp",
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "introduction", "disabled"])
          ]),
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("SitesManager_TrackingSiteSearch")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("SitesManager_SiteSearchUse")), 1),
          vue.createElementVNode("div", _hoisted_22, vue.toDisplayString(_ctx.translate("SitesManager_SearchParametersNote")) + " " + vue.toDisplayString(_ctx.translate("SitesManager_SearchParametersNote2")), 1),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "searchKeywordParametersGlobal",
              "var-type": "array",
              modelValue: _ctx.searchKeywordParametersGlobal,
              "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.searchKeywordParametersGlobal = $event),
              title: _ctx.translate("SitesManager_SearchKeywordLabel"),
              "inline-help": _ctx.translate("SitesManager_SearchKeywordParametersDesc"),
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "inline-help", "disabled"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "searchCategoryParametersGlobal",
              "var-type": "array",
              modelValue: _ctx.searchCategoryParametersGlobal,
              "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.searchCategoryParametersGlobal = $event),
              title: _ctx.translate("SitesManager_SearchCategoryLabel"),
              "inline-help": _ctx.searchCategoryParamsInlineHelp,
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "title", "inline-help", "disabled"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "defaultTimezone",
              options: _ctx.timezoneOptions,
              title: _ctx.translate("SitesManager_SelectDefaultTimezone"),
              introduction: _ctx.translate("SitesManager_DefaultTimezoneForNewWebsites"),
              "inline-help": "#timezoneHelp",
              disabled: _ctx.isLoading,
              modelValue: _ctx.defaultTimezone,
              "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.defaultTimezone = $event)
            }, null, 8, ["options", "title", "introduction", "disabled", "modelValue"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "defaultCurrency",
              modelValue: _ctx.defaultCurrency,
              "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.defaultCurrency = $event),
              options: _ctx.currencies,
              title: _ctx.translate("SitesManager_SelectDefaultCurrency"),
              introduction: _ctx.translate("SitesManager_DefaultCurrencyForNewWebsites"),
              "inline-help": _ctx.translate("SitesManager_CurrencySymbolWillBeUsedForGoals"),
              disabled: _ctx.isLoading
            }, null, 8, ["modelValue", "options", "title", "introduction", "inline-help", "disabled"])
          ]),
          vue.createVNode(_component_SaveButton, {
            saving: _ctx.isSaving,
            onConfirm: _cache[10] || (_cache[10] = ($event) => _ctx.saveGlobalSettings())
          }, null, 8, ["saving"])
        ]),
        _: 1
      }, 8, ["content-title"]), [
        [vue.vShow, _ctx.hasSuperUserAccess]
      ])
    ]);
  }
  const ManageGlobalSettings = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      ctaContent: String
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      VueEntryContainer: CoreHome.VueEntryContainer
    },
    data() {
      return {
        loading: true,
        updateCheckInterval: 1e3,
        currentInterval: 1e3,
        maxInterval: 3e4,
        showMethodDetails: null,
        recommendedMethod: null,
        trackingMethods: []
      };
    },
    created() {
      const params = {
        module: "SitesManager",
        action: "getTrackingMethodsForSite"
      };
      CoreHome.AjaxHelper.fetch(params).then((response) => {
        this.trackingMethods = response.trackingMethods;
        this.recommendedMethod = response.recommendedMethod;
        this.loading = false;
        vue.watch(() => CoreHome.MatomoUrl.hashParsed.value.activeTab, (activeTab) => {
          this.showMethodDetails = this.findTrackingMethod(activeTab);
        });
        if (CoreHome.MatomoUrl.hashParsed.value.activeTab) {
          this.showMethodDetails = this.findTrackingMethod(
            CoreHome.MatomoUrl.hashParsed.value.activeTab
          );
        }
        this.checkIfSiteHasData();
      });
    },
    methods: {
      findTrackingMethod(methodId) {
        if (this.recommendedMethod && methodId && this.recommendedMethod.id.toLowerCase() === methodId.toLowerCase()) {
          return this.recommendedMethod;
        }
        let trackingMethod = null;
        Object.entries(this.trackingMethods).forEach(([, method]) => {
          if (methodId && method.id.toLowerCase() === methodId.toLowerCase()) {
            trackingMethod = method;
          }
        });
        return trackingMethod;
      },
      showMethod(methodId) {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), { activeTab: methodId.toLowerCase() }));
      },
      showOverview() {
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), { activeTab: null }));
      },
      checkIfSiteHasData() {
        const params = {
          method: "Live.getMostRecentVisitsDateTime",
          date: "today",
          period: "day",
          idSite: CoreHome.Matomo.idSite
        };
        const options = {
          // don't show error messages returned from API as notification
          createErrorNotification: false
        };
        CoreHome.AjaxHelper.fetch(params, options).then((response) => {
          if (response && response.value !== "") {
            window.broadcast.propagateNewPage("date=today");
            return;
          }
          window.setTimeout(this.checkIfSiteHasData, this.currentInterval);
          this.currentInterval = Math.min(
            this.currentInterval + this.updateCheckInterval,
            this.maxInterval
          );
        }).catch(() => {
        });
      }
    },
    computed: {
      ignoreSitesWithoutDataLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "SitesManager",
          action: "ignoreNoDataMessage"
        }))}`;
      },
      headline() {
        if (this.showMethodDetails && this.showMethodDetails.name) {
          if (this.showMethodDetails.type === 99) {
            return this.showMethodDetails.name;
          }
          return CoreHome.translate("SitesManager_SiteWithoutDataInstallWithX", this.showMethodDetails.name);
        }
        return CoreHome.translate("SitesManager_SiteWithoutDataChooseTrackingMethod");
      }
    }
  });
  const _hoisted_1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-chevron-left" }, null, -1);
  const _hoisted_2 = { id: "start-tracking-data-header" };
  const _hoisted_3 = {
    key: 0,
    class: "row",
    id: "start-tracking-detection"
  };
  const _hoisted_4 = ["src", "alt"];
  const _hoisted_5 = ["href"];
  const _hoisted_6 = {
    class: "row",
    id: "start-tracking-method-list"
  };
  const _hoisted_7 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-search" }, null, -1);
  const _hoisted_8 = ["href", "onClick"];
  const _hoisted_9 = ["src"];
  const _hoisted_10 = { class: "list-entry-text" };
  const _hoisted_11 = { id: "start-tracking-skip" };
  const _hoisted_12 = ["href"];
  const _hoisted_13 = ["data-method"];
  const _hoisted_14 = ["src", "alt"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_VueEntryContainer = vue.resolveComponent("VueEntryContainer");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      _ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 0,
        id: "start-tracking-back",
        onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => {
          _ctx.showOverview();
        }, ["prevent"]))
      }, [
        _hoisted_1,
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Mobile_NavigationBack")), 1)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("h1", _hoisted_2, vue.toDisplayString(_ctx.headline), 1),
      vue.createVNode(_component_VueEntryContainer, {
        id: "start-tracking-cta",
        html: _ctx.ctaContent
      }, null, 8, ["html"]),
      vue.createVNode(_component_ActivityIndicator, {
        "loading-message": `${_ctx.translate("SitesManager_DetectingYourSite")}…`,
        loading: _ctx.loading
      }, null, 8, ["loading-message", "loading"]),
      !_ctx.loading && !_ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
        _ctx.recommendedMethod ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
          vue.createElementVNode("img", {
            src: _ctx.recommendedMethod.icon,
            alt: `${_ctx.recommendedMethod.name} logo`
          }, null, 8, _hoisted_4),
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.recommendedMethod.recommendationTitle), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.recommendedMethod.recommendationText), 1),
          vue.createElementVNode("a", {
            href: `#${_ctx.recommendedMethod.id.toLowerCase()}`,
            class: "btn",
            id: "showMethod",
            onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.showMethod(_ctx.recommendedMethod.id), ["prevent"]))
          }, vue.toDisplayString(_ctx.recommendedMethod.recommendationButton), 9, _hoisted_5)
        ])) : vue.createCommentVNode("", true),
        vue.createElementVNode("div", _hoisted_6, [
          _hoisted_7,
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("SitesManager_SiteWithoutDataOtherInstallMethods")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("SitesManager_SiteWithoutDataOtherInstallMethodsIntro")), 1),
          vue.createElementVNode("ul", null, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.trackingMethods, (method) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                class: "list-entry",
                key: method.id
              }, [
                vue.createElementVNode("a", {
                  href: `#${method.id.toLowerCase()}`,
                  onClick: vue.withModifiers(($event) => _ctx.showMethod(method.id), ["prevent"])
                }, [
                  method.icon ? (vue.openBlock(), vue.createElementBlock("img", {
                    key: 0,
                    src: method.icon,
                    class: "list-entry-icon"
                  }, null, 8, _hoisted_9)) : vue.createCommentVNode("", true),
                  vue.createElementVNode("span", _hoisted_10, vue.toDisplayString(method.name), 1)
                ], 8, _hoisted_8)
              ]);
            }), 128))
          ])
        ]),
        vue.createElementVNode("div", _hoisted_11, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("SitesManager_SiteWithoutDataNotYetReady")), 1),
          vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("SitesManager_SiteWithoutDataTemporarilyHidePage")), 1),
          vue.createElementVNode("a", {
            href: _ctx.ignoreSitesWithoutDataLink,
            class: "ignoreSitesWithoutData"
          }, vue.toDisplayString(_ctx.translate("SitesManager_SiteWithoutDataHidePageForHour")), 9, _hoisted_12)
        ])
      ], 64)) : vue.createCommentVNode("", true),
      _ctx.showMethodDetails ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 2,
        id: "start-tracking-details",
        "data-method": _ctx.showMethodDetails.id
      }, [
        vue.createElementVNode("img", {
          src: _ctx.showMethodDetails.icon,
          alt: `${_ctx.showMethodDetails.name} logo`
        }, null, 8, _hoisted_14),
        vue.createVNode(_component_VueEntryContainer, {
          html: _ctx.showMethodDetails.content
        }, null, 8, ["html"])
      ], 8, _hoisted_13)) : vue.createCommentVNode("", true)
    ]);
  }
  const SiteWithoutData = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.CurrencyStore = CurrencyStore$1;
  exports2.ExcludeQueryParameterSettings = ExcludeQueryParameterSettings;
  exports2.ManageGlobalSettings = ManageGlobalSettings;
  exports2.SiteTypesStore = SiteTypesStore$1;
  exports2.SiteWithoutData = SiteWithoutData;
  exports2.SitesManagement = SitesManagement;
  exports2.TimezoneStore = TimezoneStore$1;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
