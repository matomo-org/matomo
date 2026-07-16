(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.UsersManager = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
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

  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class CapabilitiesStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isLoading: false,
        capabilities: []
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "capabilities", vue.computed(() => this.state.value.capabilities));
      __publicField(this, "isLoading", vue.computed(() => this.state.value.isLoading));
      __publicField(this, "fetchPromise");
    }
    init() {
      return this.fetchCapabilities();
    }
    fetchCapabilities() {
      if (!this.fetchPromise) {
        this.privateState.isLoading = true;
        this.fetchPromise = CoreHome.AjaxHelper.fetch({
          method: "UsersManager.getAvailableCapabilities"
        }).then((capabilities) => {
          this.privateState.capabilities = capabilities;
          return this.capabilities.value;
        }).finally(() => {
          this.privateState.isLoading = false;
        });
      }
      return this.fetchPromise;
    }
  }
  const CapabilitiesStore$1 = new CapabilitiesStore();
  const { $: $$5 } = window;
  const _sfc_main$d = vue.defineComponent({
    props: {
      idsite: [String, Number],
      siteName: {
        type: String,
        required: true
      },
      userLogin: {
        type: String,
        required: true
      },
      userRole: {
        type: String,
        required: true
      },
      capabilities: Array
    },
    components: {
      Field: CorePluginsAdmin.Field
    },
    data() {
      return {
        theCapabilities: this.capabilities || [],
        isBusy: false,
        isAddingCapability: false,
        capabilityToAddId: null,
        capabilityToRemoveId: null,
        capabilityToAddOrRemove: null
      };
    },
    emits: ["change"],
    watch: {
      capabilities(newValue) {
        if (newValue) {
          this.theCapabilities = newValue;
        }
      }
    },
    created() {
      CapabilitiesStore$1.init();
      if (!this.capabilities) {
        this.isBusy = true;
        CoreHome.AjaxHelper.fetch({
          method: "UsersManager.getUsersPlusRole",
          limit: "1",
          filter_search: this.userLogin
        }).then((user) => {
          if (!user || !user.capabilities) {
            return [];
          }
          return user.capabilities;
        }).then((capabilities) => {
          this.theCapabilities = capabilities;
        }).finally(() => {
          this.isBusy = false;
        });
      } else {
        this.theCapabilities = this.capabilities;
      }
    },
    methods: {
      onToggleCapability(isAdd) {
        this.isAddingCapability = isAdd;
        const capabilityToAddOrRemoveId = isAdd ? this.capabilityToAddId : this.capabilityToRemoveId;
        this.capabilityToAddOrRemove = null;
        this.availableCapabilities.forEach((capability) => {
          if (capability.id === capabilityToAddOrRemoveId) {
            this.capabilityToAddOrRemove = capability;
          }
        });
        if (this.$refs.confirmCapabilityToggleModal) {
          $$5(this.$refs.confirmCapabilityToggleModal).modal({
            dismissible: false,
            yes: () => null
          }).modal("open");
        }
      },
      toggleCapability() {
        if (this.isAddingCapability) {
          this.addCapability(this.capabilityToAddOrRemove);
        } else {
          this.removeCapability(this.capabilityToAddOrRemove);
        }
      },
      isIncludedInRole(capability) {
        return (capability.includedInRoles || []).indexOf(this.userRole) !== -1;
      },
      getCapabilitiesList() {
        const result = [];
        this.availableCapabilities.forEach((capability) => {
          if (this.isIncludedInRole(capability)) {
            return;
          }
          if (this.capabilitiesSet[capability.id]) {
            result.push(capability.id);
          }
        });
        return result;
      },
      addCapability(capability) {
        this.isBusy = true;
        CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.addCapabilities"
          },
          {
            userLogin: this.userLogin,
            capabilities: capability.id,
            idSites: this.idsite
          }
        ).then(() => {
          this.$emit("change", this.getCapabilitiesList());
        }).finally(() => {
          this.isBusy = false;
          this.capabilityToAddOrRemove = null;
          this.capabilityToAddId = null;
          this.capabilityToRemoveId = null;
        });
      },
      removeCapability(capability) {
        this.isBusy = true;
        CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.removeCapabilities"
          },
          {
            userLogin: this.userLogin,
            capabilities: capability.id,
            idSites: this.idsite
          }
        ).then(() => {
          this.$emit("change", this.getCapabilitiesList());
        }).finally(() => {
          this.isBusy = false;
          this.capabilityToAddOrRemove = null;
          this.capabilityToAddId = null;
          this.capabilityToRemoveId = null;
        });
      }
    },
    computed: {
      availableCapabilities() {
        return CapabilitiesStore$1.capabilities.value;
      },
      confirmAddCapabilityToggleContent() {
        return CoreHome.translate(
          "UsersManager_AreYouSureAddCapability",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ""}</strong>`,
          `<strong>${this.siteNameText}</strong>`
        );
      },
      confirmCapabilityToggleContent() {
        return CoreHome.translate(
          "UsersManager_AreYouSureRemoveCapability",
          `<strong>${this.capabilityToAddOrRemove ? this.capabilityToAddOrRemove.name : ""}</strong>`,
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.siteNameText}</strong>`
        );
      },
      siteNameText() {
        return CoreHome.Matomo.helper.htmlEntities(this.siteName);
      },
      availableCapabilitiesGrouped() {
        const availableCapabilitiesGrouped = this.availableCapabilities.filter(
          (c) => !this.capabilitiesSet[c.id]
        ).map((c) => ({
          group: c.category,
          key: c.id,
          value: c.name,
          tooltip: c.description
        }));
        availableCapabilitiesGrouped.sort((lhs, rhs) => {
          if (lhs.group === rhs.group) {
            if (lhs.value === rhs.value) {
              return 0;
            }
            return lhs.value < rhs.value ? -1 : 1;
          }
          return lhs.group < rhs.group ? -1 : 1;
        });
        return availableCapabilitiesGrouped;
      },
      capabilitiesSet() {
        const capabilitiesSet = {};
        const capabilities = this.theCapabilities;
        (capabilities || []).forEach((capability) => {
          capabilitiesSet[capability] = true;
        });
        (this.availableCapabilities || []).forEach((capability) => {
          if (this.isIncludedInRole(capability)) {
            capabilitiesSet[capability.id] = true;
          }
        });
        return capabilitiesSet;
      },
      actualCapabilities() {
        const { capabilitiesSet } = this;
        return this.availableCapabilities.filter((c) => !!capabilitiesSet[c.id]);
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
  const _hoisted_1$d = ["title"];
  const _hoisted_2$c = ["onClick"];
  const _hoisted_3$a = {
    key: 0,
    class: "addCapability"
  };
  const _hoisted_4$9 = {
    class: "ui-confirm confirmCapabilityToggle modal",
    ref: "confirmCapabilityToggleModal"
  };
  const _hoisted_5$9 = { class: "modal-content" };
  const _hoisted_6$9 = ["innerHTML"];
  const _hoisted_7$8 = ["innerHTML"];
  const _hoisted_8$7 = { class: "modal-footer" };
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["capabilitiesEdit", { busy: _ctx.isBusy }])
    }, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.actualCapabilities, (capability) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          key: capability.id,
          class: "chip"
        }, [
          vue.createElementVNode("span", {
            class: "capability-name",
            title: `${capability.description} ${_ctx.isIncludedInRole(capability) ? `<br/><br/>${_ctx.translate("UsersManager_IncludedInUsersRole")}` : ""}`
          }, vue.toDisplayString(capability.category) + ": " + vue.toDisplayString(capability.name), 9, _hoisted_1$d),
          !_ctx.isIncludedInRole(capability) ? (vue.openBlock(), vue.createElementBlock("span", {
            key: 0,
            class: "icon-close",
            onClick: ($event) => {
              _ctx.capabilityToRemoveId = capability.id;
              _ctx.onToggleCapability(false);
            }
          }, null, 8, _hoisted_2$c)) : vue.createCommentVNode("", true)
        ]);
      }), 128)),
      _ctx.availableCapabilitiesGrouped.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$a, [
        _ctx.userRole !== "noaccess" ? (vue.openBlock(), vue.createBlock(_component_Field, {
          key: 0,
          "model-value": _ctx.capabilityToAddId,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => {
            _ctx.capabilityToAddId = $event;
            _ctx.onToggleCapability(true);
          }),
          disabled: _ctx.isBusy,
          uicontrol: "expandable-select",
          name: "add_capability",
          "full-width": true,
          options: _ctx.availableCapabilitiesGrouped
        }, null, 8, ["model-value", "disabled", "options"])) : vue.createCommentVNode("", true)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_4$9, [
        vue.createElementVNode("div", _hoisted_5$9, [
          _ctx.isAddingCapability ? (vue.openBlock(), vue.createElementBlock("h2", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.confirmAddCapabilityToggleContent)
          }, null, 8, _hoisted_6$9)) : vue.createCommentVNode("", true),
          !_ctx.isAddingCapability ? (vue.openBlock(), vue.createElementBlock("h2", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.confirmCapabilityToggleContent)
          }, null, 8, _hoisted_7$8)) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("div", _hoisted_8$7, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.toggleCapability(), ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => {
              _ctx.capabilityToAddOrRemove = null;
              _ctx.capabilityToAddId = null;
              _ctx.capabilityToRemoveId = null;
            }, ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_No")), 1)
        ])
      ], 512)
    ], 2);
  }
  const CapabilitiesEdit = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const { $: $$4 } = window;
  const _sfc_main$c = vue.defineComponent({
    props: {
      userLogin: {
        type: String,
        required: true
      },
      limit: {
        type: Number,
        default: 10
      },
      accessLevels: {
        type: Array,
        required: true
      },
      filterAccessLevels: {
        type: Array,
        required: true
      }
    },
    components: {
      Notification: CoreHome.Notification,
      Field: CorePluginsAdmin.Field,
      CapabilitiesEdit,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      DropdownMenu: CoreHome.DropdownMenu,
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        siteAccess: [],
        offset: 0,
        totalEntries: null,
        accessLevelFilter: "",
        siteNameFilter: "",
        isLoadingAccess: false,
        allWebsitesAccssLevelSet: "view",
        isAllCheckboxSelected: false,
        selectedRows: {},
        isBulkActionsDisabled: true,
        areAllResultsSelected: false,
        accessChangeEvent: null,
        hasAccessToAtLeastOneSite: true,
        isRoleHelpToggled: false,
        isCapabilitiesHelpToggled: false,
        isGivingAccessToAllSites: false,
        roleToChangeTo: null,
        siteAccessToChange: null,
        showPasswordConfirmationForAccessChange: false,
        showPasswordConfirmationForAllSitesAccess: false
      };
    },
    emits: ["userHasAccessDetected", "accessChanged"],
    created() {
      this.onChangeSiteFilter = CoreHome.debounce(this.onChangeSiteFilter, 300);
      vue.watch(
        () => this.allPropsWatch,
        () => {
          if (this.limit) {
            this.fetchAccess();
          }
        }
      );
      this.fetchAccess();
    },
    watch: {
      accessLevelFilter() {
        this.offset = 0;
        this.fetchAccess();
      }
    },
    methods: {
      onAllCheckboxChange(event) {
        this.isAllCheckboxSelected = event.target.checked;
        if (!this.isAllCheckboxSelected) {
          this.clearSelection();
        } else {
          this.siteAccess.forEach((e, i) => {
            this.selectedRows[i] = true;
          });
          this.isBulkActionsDisabled = false;
        }
      },
      clearSelection() {
        this.selectedRows = {};
        this.areAllResultsSelected = false;
        this.isBulkActionsDisabled = true;
        this.isAllCheckboxSelected = false;
        this.siteAccessToChange = null;
      },
      onRowSelected() {
        setTimeout(() => {
          const selectedRowKeyCount = this.selectedRowsCount;
          this.isBulkActionsDisabled = selectedRowKeyCount === 0;
          this.isAllCheckboxSelected = selectedRowKeyCount === this.siteAccess.length;
        });
      },
      fetchAccess() {
        this.isLoadingAccess = true;
        return CoreHome.AjaxHelper.fetch(
          {
            method: "UsersManager.getSitesAccessForUser",
            limit: this.limit,
            offset: this.offset,
            filter_search: this.siteNameFilter,
            filter_access: this.accessLevelFilter,
            userLogin: this.userLogin
          },
          { returnResponseObject: true }
        ).then((helper) => {
          const result = helper.getRequestHandle();
          this.isLoadingAccess = false;
          this.siteAccess = result.responseJSON;
          this.totalEntries = parseInt(result.getResponseHeader("x-matomo-total-results"), 10) || 0;
          this.hasAccessToAtLeastOneSite = !!result.getResponseHeader("x-matomo-has-some");
          this.$emit("userHasAccessDetected", { hasAccess: this.hasAccessToAtLeastOneSite });
          this.clearSelection();
        }).catch(() => {
          this.isLoadingAccess = false;
          this.clearSelection();
        });
      },
      gotoPreviousPage() {
        this.offset = Math.max(0, this.offset - this.limit);
        this.fetchAccess();
      },
      gotoNextPage() {
        const newOffset = this.offset + this.limit;
        if (newOffset >= (this.totalEntries || 0)) {
          return;
        }
        this.offset = newOffset;
        this.fetchAccess();
      },
      showRemoveAccessConfirm() {
        $$4(this.$refs.deleteAccessConfirmModal).modal({
          dismissible: false
        }).modal("open");
      },
      changeUserRole(password = "") {
        const getSelectedSites = () => {
          const result = [];
          Object.keys(this.selectedRows).forEach((index) => {
            if (this.selectedRows[index] && this.siteAccess[index]) {
              result.push(this.siteAccess[index].idsite);
            }
          });
          return result;
        };
        const getAllSitesInSearch = () => CoreHome.AjaxHelper.fetch(
          {
            method: "UsersManager.getSitesAccessForUser",
            filter_search: this.siteNameFilter,
            filter_access: this.accessLevelFilter,
            userLogin: this.userLogin,
            filter_limit: "-1"
          }
        ).then((access) => access.map((a) => a.idsite));
        this.isLoadingAccess = true;
        return Promise.resolve().then(() => {
          if (this.siteAccessToChange) {
            return [this.siteAccessToChange.idsite];
          }
          if (this.areAllResultsSelected) {
            return getAllSitesInSearch();
          }
          return getSelectedSites();
        }).then((idSites) => CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.setUserAccess"
          },
          {
            userLogin: this.userLogin,
            access: this.roleToChangeTo,
            idSites,
            passwordConfirmation: password
          }
        )).catch(() => {
        }).then(() => {
          this.accessChangeEvent = null;
          this.$emit("accessChanged");
          return this.fetchAccess();
        });
      },
      showChangeAccessConfirm() {
        if (this.roleToChangeTo === "admin") {
          this.showPasswordConfirmationForAccessChange = true;
          return;
        }
        $$4(this.$refs.changeAccessConfirmModal).modal({
          dismissible: false,
          onCloseEnd: () => {
            this.accessChangeEvent = null;
          }
        }).modal("open");
      },
      onAccessChangeAborted() {
        if (this.accessChangeEvent) {
          this.accessChangeEvent.abort();
        }
        this.accessChangeEvent = null;
        this.siteAccessToChange = null;
        this.roleToChangeTo = null;
      },
      getRoleDisplay(role) {
        let result = role;
        this.filteredAccessLevels.forEach((entry) => {
          if (entry.key === role) {
            result = entry.value;
          }
        });
        return result;
      },
      giveAccessToAllSites(password = "") {
        this.isGivingAccessToAllSites = true;
        CoreHome.AjaxHelper.fetch({
          method: "SitesManager.getSitesWithAdminAccess",
          filter_limit: -1
        }).then((allSites) => {
          const idSites = allSites.map((s) => s.idsite);
          return CoreHome.AjaxHelper.post(
            {
              method: "UsersManager.setUserAccess"
            },
            {
              userLogin: this.userLogin,
              access: this.allWebsitesAccssLevelSet,
              idSites,
              passwordConfirmation: password
            }
          );
        }).then(() => this.fetchAccess()).finally(() => {
          this.isGivingAccessToAllSites = false;
        });
      },
      showChangeAccessAllSitesModal() {
        if (this.allWebsitesAccssLevelSet === "admin") {
          this.showPasswordConfirmationForAllSitesAccess = true;
          return;
        }
        $$4(this.$refs.confirmGiveAccessAllSitesModal).modal({
          dismissible: false
        }).modal("open");
      },
      onChangeSiteFilter(event) {
        setTimeout(() => {
          const inputValue = event.target.value;
          if (this.siteNameFilter !== inputValue) {
            this.siteNameFilter = inputValue;
            this.offset = 0;
            this.fetchAccess();
          }
        });
      },
      onRoleChange(entry, event) {
        this.siteAccessToChange = entry;
        this.roleToChangeTo = event.value;
        this.accessChangeEvent = event;
        this.showChangeAccessConfirm();
      }
    },
    computed: {
      rolesHelpText() {
        return CoreHome.translate(
          "UsersManager_RolesHelp",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_70/"),
          "</a>",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_69/"),
          "</a>"
        );
      },
      theDisplayedWebsitesAreSelectedText() {
        const text = CoreHome.translate(
          "UsersManager_TheDisplayedWebsitesAreSelected",
          `<strong>${this.siteAccess.length}</strong>`
        );
        return `${text} `;
      },
      clickToSelectAllText() {
        return CoreHome.translate("UsersManager_ClickToSelectAll", `<strong>${this.totalEntries}</strong>`);
      },
      allWebsitesAreSelectedText() {
        return CoreHome.translate(
          "UsersManager_AllWebsitesAreSelected",
          `<strong>${this.totalEntries}</strong>`
        );
      },
      clickToSelectDisplayedWebsitesText() {
        return CoreHome.translate(
          "UsersManager_ClickToSelectDisplayedWebsites",
          `<strong>${this.siteAccess.length}</strong>`
        );
      },
      deletePermConfirmSingleText() {
        return CoreHome.translate(
          "UsersManager_DeletePermConfirmSingle",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.siteAccessToChangeName}</strong>`
        );
      },
      deletePermConfirmMultipleText() {
        return CoreHome.translate(
          "UsersManager_DeletePermConfirmMultiple",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.affectedSitesCount}</strong>`
        );
      },
      changePermToSiteConfirmSingleText() {
        return CoreHome.translate(
          "UsersManager_ChangePermToSiteConfirmSingle",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.siteAccessToChangeName}</strong>`,
          `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`
        );
      },
      changePermToSiteConfirmMultipleText() {
        return CoreHome.translate(
          "UsersManager_ChangePermToSiteConfirmMultiple",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.affectedSitesCount}</strong>`,
          `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`
        );
      },
      changePermToAllSitesConfirmText() {
        return CoreHome.translate(
          "UsersManager_ChangePermToAllSitesConfirm",
          `<strong>${this.userLogin}</strong>`,
          `<strong>${this.getRoleDisplay(this.allWebsitesAccssLevelSet)}</strong>`
        );
      },
      paginationLowerBound() {
        return this.offset + 1;
      },
      paginationUpperBound() {
        if (!this.totalEntries) {
          return "?";
        }
        return Math.min(this.offset + this.limit, this.totalEntries);
      },
      filteredAccessLevels() {
        return this.accessLevels.filter((entry) => entry.key !== "superuser" && entry.type === "role");
      },
      filteredSelectAccessLevels() {
        return this.filterAccessLevels.filter(
          (entry) => entry.key !== "superuser"
        );
      },
      selectedRowsCount() {
        let selectedRowKeyCount = 0;
        Object.values(this.selectedRows).forEach((v) => {
          if (v) {
            selectedRowKeyCount += 1;
          }
        });
        return selectedRowKeyCount;
      },
      affectedSitesCount() {
        if (this.areAllResultsSelected) {
          return this.totalEntries;
        }
        return this.selectedRowsCount;
      },
      allPropsWatch() {
        return this.userLogin, this.limit, this.accessLevels, this.filterAccessLevels, Date.now();
      },
      siteAccessToChangeName() {
        return this.siteAccessToChange ? CoreHome.Matomo.helper.htmlEntities(this.siteAccessToChange.site_name) : "";
      },
      paginationText() {
        const text = CoreHome.translate(
          "General_Pagination",
          `${this.paginationLowerBound}`,
          `${this.paginationUpperBound}`,
          `${this.totalEntries}`
        );
        return ` ${text} `;
      }
    }
  });
  const _hoisted_1$c = {
    key: 0,
    class: "row"
  };
  const _hoisted_2$b = { class: "row to-all-websites" };
  const _hoisted_3$9 = { class: "col s12" };
  const _hoisted_4$8 = { style: { "margin-right": "3.5px" } };
  const _hoisted_5$8 = {
    id: "all-sites-access-select",
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_6$8 = { style: { "margin-top": "18px" } };
  const _hoisted_7$7 = { class: "filters row" };
  const _hoisted_8$6 = { class: "col s12 m12 l8" };
  const _hoisted_9$6 = {
    class: "input-field bulk-actions",
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_10$5 = {
    id: "user-permissions-edit-bulk-actions",
    class: "dropdown-content"
  };
  const _hoisted_11$5 = {
    class: "dropdown-trigger",
    "data-target": "user-permissions-bulk-set-access"
  };
  const _hoisted_12$4 = {
    id: "user-permissions-bulk-set-access",
    class: "dropdown-content"
  };
  const _hoisted_13$4 = ["onClick"];
  const _hoisted_14$4 = {
    class: "input-field site-filter",
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_15$4 = ["value", "placeholder"];
  const _hoisted_16$4 = {
    class: "input-field access-filter",
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_17$3 = {
    key: 0,
    class: "col s12 m12 l4 sites-for-permission-pagination-container"
  };
  const _hoisted_18$3 = { class: "sites-for-permission-pagination" };
  const _hoisted_19$3 = { class: "counter" };
  const _hoisted_20$3 = ["textContent"];
  const _hoisted_21$3 = { class: "roles-help-notification" };
  const _hoisted_22$3 = ["innerHTML"];
  const _hoisted_23$2 = { class: "capabilities-help-notification" };
  const _hoisted_24$2 = { id: "sitesForPermission" };
  const _hoisted_25$2 = { class: "select-cell" };
  const _hoisted_26$2 = { class: "checkbox-container" };
  const _hoisted_27$2 = ["checked"];
  const _hoisted_28$2 = { class: "role_header" };
  const _hoisted_29$2 = ["innerHTML"];
  const _hoisted_30$2 = { class: "capabilities_header" };
  const _hoisted_31$2 = ["innerHTML"];
  const _hoisted_32$2 = {
    key: 0,
    class: "select-all-row"
  };
  const _hoisted_33$2 = { colspan: "4" };
  const _hoisted_34$2 = { key: 0 };
  const _hoisted_35$1 = ["innerHTML"];
  const _hoisted_36$1 = ["innerHTML"];
  const _hoisted_37$1 = { key: 1 };
  const _hoisted_38$1 = ["innerHTML"];
  const _hoisted_39$1 = ["innerHTML"];
  const _hoisted_40$1 = { class: "select-cell" };
  const _hoisted_41$1 = { class: "checkbox-container" };
  const _hoisted_42$1 = ["id", "onUpdate:modelValue"];
  const _hoisted_43$1 = { class: "role-select" };
  const _hoisted_44$1 = {
    class: "delete-access-confirm-modal modal",
    ref: "deleteAccessConfirmModal"
  };
  const _hoisted_45$1 = { class: "modal-content" };
  const _hoisted_46$1 = ["innerHTML"];
  const _hoisted_47$1 = ["innerHTML"];
  const _hoisted_48$1 = { class: "modal-footer" };
  const _hoisted_49$1 = {
    class: "change-access-confirm-modal modal",
    ref: "changeAccessConfirmModal"
  };
  const _hoisted_50$1 = { class: "modal-content" };
  const _hoisted_51$1 = ["innerHTML"];
  const _hoisted_52$1 = ["innerHTML"];
  const _hoisted_53$1 = { class: "modal-footer" };
  const _hoisted_54$1 = {
    class: "confirm-give-access-all-sites modal",
    ref: "confirmGiveAccessAllSitesModal"
  };
  const _hoisted_55$1 = { class: "modal-content" };
  const _hoisted_56$1 = ["innerHTML"];
  const _hoisted_57$1 = { class: "modal-footer" };
  const _hoisted_58$1 = ["innerHTML"];
  const _hoisted_59$1 = ["innerHTML"];
  const _hoisted_60$1 = { key: 2 };
  const _hoisted_61$1 = ["innerHTML"];
  const _hoisted_62$1 = ["innerHTML"];
  const _hoisted_63$1 = { key: 0 };
  const _hoisted_64$1 = ["innerHTML"];
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_Field = vue.resolveComponent("Field");
    const _component_CapabilitiesEdit = vue.resolveComponent("CapabilitiesEdit");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _directive_dropdown_menu = vue.resolveDirective("dropdown-menu");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["userPermissionsEdit", { loading: _ctx.isLoadingAccess }])
    }, [
      !_ctx.hasAccessToAtLeastOneSite ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$c, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_Notification, {
            context: "warning",
            type: "transient",
            noclear: true
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("General_Warning")) + ":", 1),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_NoAccessWarning")), 1)
            ]),
            _: 1
          })
        ])
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_2$b, [
        vue.createElementVNode("div", _hoisted_3$9, [
          vue.createElementVNode("div", null, [
            vue.createElementVNode("span", _hoisted_4$8, vue.toDisplayString(_ctx.translate("UsersManager_GiveAccessToAll")) + ":", 1),
            vue.createElementVNode("div", _hoisted_5$8, [
              vue.createVNode(_component_Field, {
                modelValue: _ctx.allWebsitesAccssLevelSet,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.allWebsitesAccssLevelSet = $event),
                uicontrol: "select",
                options: _ctx.filteredAccessLevels,
                "full-width": true
              }, null, 8, ["modelValue", "options"])
            ]),
            vue.createElementVNode("a", {
              href: "",
              class: vue.normalizeClass(["btn", { disabled: _ctx.isGivingAccessToAllSites }]),
              onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.showChangeAccessAllSitesModal(), ["prevent"]))
            }, vue.toDisplayString(_ctx.translate("General_Apply")), 3)
          ]),
          vue.createElementVNode("p", _hoisted_6$8, vue.toDisplayString(_ctx.translate("UsersManager_OrManageIndividually")) + ":", 1)
        ])
      ]),
      vue.createElementVNode("div", _hoisted_7$7, [
        vue.createElementVNode("div", _hoisted_8$6, [
          vue.createElementVNode("div", _hoisted_9$6, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
              class: vue.normalizeClass(["dropdown-trigger btn", { disabled: _ctx.isBulkActionsDisabled }]),
              href: "",
              "data-target": "user-permissions-edit-bulk-actions"
            }, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_BulkActions")), 1)
            ], 2)), [
              [_directive_dropdown_menu, { activates: "#user-permissions-edit-bulk-actions" }]
            ]),
            vue.createElementVNode("ul", _hoisted_10$5, [
              vue.createElementVNode("li", null, [
                vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_11$5, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_SetPermission")), 1)
                ])), [
                  [_directive_dropdown_menu, { activates: "#user-permissions-bulk-set-access" }]
                ]),
                vue.createElementVNode("ul", _hoisted_12$4, [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.filteredAccessLevels, (access) => {
                    return vue.openBlock(), vue.createElementBlock("li", {
                      key: access.key
                    }, [
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: vue.withModifiers(($event) => {
                          _ctx.siteAccessToChange = null;
                          _ctx.accessChangeEvent = null;
                          _ctx.roleToChangeTo = String(access.key);
                          _ctx.showChangeAccessConfirm();
                        }, ["prevent"])
                      }, vue.toDisplayString(access.value), 9, _hoisted_13$4)
                    ]);
                  }), 128))
                ])
              ]),
              vue.createElementVNode("li", null, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => {
                    _ctx.siteAccessToChange = null;
                    _ctx.roleToChangeTo = "noaccess";
                    _ctx.showRemoveAccessConfirm();
                  }, ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_RemovePermissions")), 1)
              ])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_14$4, [
            vue.createElementVNode("input", {
              type: "text",
              value: _ctx.siteNameFilter,
              onKeydown: _cache[3] || (_cache[3] = ($event) => {
                _ctx.onChangeSiteFilter($event);
              }),
              onChange: _cache[4] || (_cache[4] = ($event) => {
                _ctx.onChangeSiteFilter($event);
              }),
              placeholder: _ctx.translate("UsersManager_FilterByWebsite")
            }, null, 40, _hoisted_15$4)
          ]),
          vue.createElementVNode("div", _hoisted_16$4, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                modelValue: _ctx.accessLevelFilter,
                "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.accessLevelFilter = $event),
                uicontrol: "select",
                options: _ctx.filteredSelectAccessLevels,
                "full-width": true,
                placeholder: _ctx.translate("UsersManager_FilterByAccess")
              }, null, 8, ["modelValue", "options", "placeholder"])
            ])
          ])
        ]),
        _ctx.totalEntries > _ctx.limit ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_17$3, [
          vue.createElementVNode("div", _hoisted_18$3, [
            vue.createElementVNode("a", {
              class: vue.normalizeClass(["prev", { disabled: _ctx.offset <= 0 }])
            }, [
              vue.createElementVNode("span", {
                class: "pointer",
                onClick: _cache[6] || (_cache[6] = ($event) => _ctx.gotoPreviousPage())
              }, "« " + vue.toDisplayString(_ctx.translate("General_Previous")), 1)
            ], 2),
            vue.createElementVNode("span", _hoisted_19$3, [
              vue.createElementVNode("span", {
                textContent: vue.toDisplayString(_ctx.paginationText)
              }, null, 8, _hoisted_20$3)
            ]),
            vue.createElementVNode("a", {
              class: vue.normalizeClass(["next", { disabled: _ctx.offset + _ctx.limit >= (_ctx.totalEntries || 0) }])
            }, [
              vue.createElementVNode("span", {
                class: "pointer",
                onClick: _cache[7] || (_cache[7] = ($event) => _ctx.gotoNextPage())
              }, vue.toDisplayString(_ctx.translate("General_Next")) + " »", 1)
            ], 2)
          ])
        ])) : vue.createCommentVNode("", true)
      ]),
      vue.createElementVNode("div", _hoisted_21$3, [
        _ctx.isRoleHelpToggled ? (vue.openBlock(), vue.createBlock(_component_Notification, {
          key: 0,
          context: "info",
          type: "persistent",
          noclear: true
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.rolesHelpText)
            }, null, 8, _hoisted_22$3)
          ]),
          _: 1
        })) : vue.createCommentVNode("", true)
      ]),
      vue.createElementVNode("div", _hoisted_23$2, [
        _ctx.isCapabilitiesHelpToggled ? (vue.openBlock(), vue.createBlock(_component_Notification, {
          key: 0,
          context: "info",
          type: "persistent",
          noclear: true
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("UsersManager_CapabilitiesHelp")), 1)
          ]),
          _: 1
        })) : vue.createCommentVNode("", true)
      ]),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_24$2, [
        vue.createElementVNode("thead", null, [
          vue.createElementVNode("tr", null, [
            vue.createElementVNode("th", _hoisted_25$2, [
              vue.createElementVNode("span", _hoisted_26$2, [
                vue.createElementVNode("label", null, [
                  vue.createElementVNode("input", {
                    type: "checkbox",
                    id: "perm_edit_select_all",
                    checked: _ctx.isAllCheckboxSelected,
                    onChange: _cache[8] || (_cache[8] = ($event) => _ctx.onAllCheckboxChange($event))
                  }, null, 40, _hoisted_27$2),
                  _cache[23] || (_cache[23] = vue.createElementVNode("span", null, null, -1))
                ])
              ])
            ]),
            vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Name")), 1),
            vue.createElementVNode("th", _hoisted_28$2, [
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(`${_ctx.translate("UsersManager_Role")} `)
              }, null, 8, _hoisted_29$2),
              vue.createElementVNode("a", {
                href: "",
                class: vue.normalizeClass(["helpIcon", { sticky: _ctx.isRoleHelpToggled }]),
                onClick: _cache[9] || (_cache[9] = vue.withModifiers(($event) => _ctx.isRoleHelpToggled = !_ctx.isRoleHelpToggled, ["prevent"]))
              }, [..._cache[24] || (_cache[24] = [
                vue.createElementVNode("span", { class: "icon-help" }, null, -1)
              ])], 2)
            ]),
            vue.createElementVNode("th", _hoisted_30$2, [
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(`${_ctx.translate("UsersManager_Capabilities")} `)
              }, null, 8, _hoisted_31$2),
              vue.createElementVNode("a", {
                href: "",
                class: vue.normalizeClass(["helpIcon", { sticky: _ctx.isCapabilitiesHelpToggled }]),
                onClick: _cache[10] || (_cache[10] = vue.withModifiers(($event) => _ctx.isCapabilitiesHelpToggled = !_ctx.isCapabilitiesHelpToggled, ["prevent"]))
              }, [..._cache[25] || (_cache[25] = [
                vue.createElementVNode("span", { class: "icon-help" }, null, -1)
              ])], 2)
            ])
          ])
        ]),
        vue.createElementVNode("tbody", null, [
          _ctx.isAllCheckboxSelected && _ctx.siteAccess.length < (_ctx.totalEntries || 0) ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_32$2, [
            vue.createElementVNode("td", _hoisted_33$2, [
              !_ctx.areAllResultsSelected ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_34$2, [
                vue.createElementVNode("span", {
                  innerHTML: _ctx.$sanitize(_ctx.theDisplayedWebsitesAreSelectedText),
                  style: { "margin-right": "3.5px" }
                }, null, 8, _hoisted_35$1),
                vue.createElementVNode("a", {
                  href: "#",
                  onClick: _cache[11] || (_cache[11] = vue.withModifiers(($event) => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
                  innerHTML: _ctx.$sanitize(_ctx.clickToSelectAllText)
                }, null, 8, _hoisted_36$1)
              ])) : vue.createCommentVNode("", true),
              _ctx.areAllResultsSelected ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_37$1, [
                vue.createElementVNode("span", {
                  innerHTML: _ctx.$sanitize(_ctx.allWebsitesAreSelectedText),
                  style: { "margin-right": "3.5px" }
                }, null, 8, _hoisted_38$1),
                vue.createElementVNode("a", {
                  href: "#",
                  onClick: _cache[12] || (_cache[12] = vue.withModifiers(($event) => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
                  innerHTML: _ctx.$sanitize(_ctx.clickToSelectDisplayedWebsitesText)
                }, null, 8, _hoisted_39$1)
              ])) : vue.createCommentVNode("", true)
            ])
          ])) : vue.createCommentVNode("", true),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.siteAccess, (entry, index) => {
            return vue.openBlock(), vue.createElementBlock("tr", {
              key: entry.idsite
            }, [
              vue.createElementVNode("td", _hoisted_40$1, [
                vue.createElementVNode("span", _hoisted_41$1, [
                  vue.createElementVNode("label", null, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "checkbox",
                      id: `perm_edit_select_row${index}`,
                      "onUpdate:modelValue": ($event) => _ctx.selectedRows[index] = $event,
                      onClick: _cache[13] || (_cache[13] = ($event) => _ctx.onRowSelected())
                    }, null, 8, _hoisted_42$1), [
                      [vue.vModelCheckbox, _ctx.selectedRows[index]]
                    ]),
                    _cache[26] || (_cache[26] = vue.createElementVNode("span", null, null, -1))
                  ])
                ])
              ]),
              vue.createElementVNode("td", null, [
                vue.createElementVNode("span", null, vue.toDisplayString(entry.site_name), 1)
              ]),
              vue.createElementVNode("td", null, [
                vue.createElementVNode("div", _hoisted_43$1, [
                  vue.createVNode(_component_Field, {
                    "model-value": entry.role,
                    "onUpdate:modelValue": ($event) => {
                      _ctx.onRoleChange(entry, $event);
                    },
                    "model-modifiers": { abortable: true },
                    uicontrol: "select",
                    options: _ctx.filteredAccessLevels,
                    "full-width": true
                  }, null, 8, ["model-value", "onUpdate:modelValue", "options"])
                ])
              ]),
              vue.createElementVNode("td", null, [
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_CapabilitiesEdit, {
                    idsite: entry.idsite,
                    "site-name": entry.site_name,
                    "user-login": _ctx.userLogin,
                    "user-role": entry.role,
                    capabilities: entry.capabilities,
                    onChange: _cache[14] || (_cache[14] = ($event) => _ctx.fetchAccess())
                  }, null, 8, ["idsite", "site-name", "user-login", "user-role", "capabilities"])
                ])
              ])
            ]);
          }), 128))
        ])
      ])), [
        [_directive_content_table]
      ]),
      vue.createElementVNode("div", _hoisted_44$1, [
        vue.createElementVNode("div", _hoisted_45$1, [
          _ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmSingleText)
          }, null, 8, _hoisted_46$1)) : vue.createCommentVNode("", true),
          !_ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.deletePermConfirmMultipleText)
          }, null, 8, _hoisted_47$1)) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("div", _hoisted_48$1, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[15] || (_cache[15] = vue.withModifiers(($event) => _ctx.changeUserRole(), ["prevent"])),
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[16] || (_cache[16] = vue.withModifiers(($event) => {
              _ctx.siteAccessToChange = null;
              _ctx.roleToChangeTo = null;
            }, ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_No")), 1)
        ])
      ], 512),
      vue.createElementVNode("div", _hoisted_49$1, [
        vue.createElementVNode("div", _hoisted_50$1, [
          _ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmSingleText)
          }, null, 8, _hoisted_51$1)) : vue.createCommentVNode("", true),
          !_ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmMultipleText)
          }, null, 8, _hoisted_52$1)) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("div", _hoisted_53$1, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[17] || (_cache[17] = vue.withModifiers(($event) => _ctx.changeUserRole(), ["prevent"])),
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[18] || (_cache[18] = vue.withModifiers(($event) => {
              _ctx.accessChangeEvent && _ctx.accessChangeEvent.abort();
              _ctx.siteAccessToChange = null;
              _ctx.roleToChangeTo = null;
            }, ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_No")), 1)
        ])
      ], 512),
      vue.createElementVNode("div", _hoisted_54$1, [
        vue.createElementVNode("div", _hoisted_55$1, [
          vue.createElementVNode("h3", {
            innerHTML: _ctx.$sanitize(_ctx.changePermToAllSitesConfirmText)
          }, null, 8, _hoisted_56$1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_ChangePermToAllSitesConfirm2")), 1)
        ]),
        vue.createElementVNode("div", _hoisted_57$1, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[19] || (_cache[19] = vue.withModifiers(($event) => _ctx.giveAccessToAllSites(), ["prevent"])),
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[20] || (_cache[20] = ($event) => $event.preventDefault())
          }, vue.toDisplayString(_ctx.translate("General_No")), 1)
        ])
      ], 512),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForAccessChange,
        "onUpdate:modelValue": _cache[21] || (_cache[21] = ($event) => _ctx.showPasswordConfirmationForAccessChange = $event),
        onConfirmed: _ctx.changeUserRole,
        onAborted: _ctx.onAccessChangeAborted
      }, {
        default: vue.withCtx(() => [
          _ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmSingleText)
          }, null, 8, _hoisted_58$1)) : vue.createCommentVNode("", true),
          !_ctx.siteAccessToChange ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.changePermToSiteConfirmMultipleText)
          }, null, 8, _hoisted_59$1)) : vue.createCommentVNode("", true),
          _ctx.roleToChangeTo === "admin" ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_60$1, [
            vue.createElementVNode("em", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Note")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "UsersManager_AdminUserRoleChangeWarning",
                  String(_ctx.getRoleDisplay(_ctx.roleToChangeTo))
                ))
              }, null, 8, _hoisted_61$1)
            ])
          ])) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "onAborted"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForAllSitesAccess,
        "onUpdate:modelValue": _cache[22] || (_cache[22] = ($event) => _ctx.showPasswordConfirmationForAllSitesAccess = $event),
        onConfirmed: _ctx.giveAccessToAllSites
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("h3", {
            innerHTML: _ctx.$sanitize(_ctx.changePermToAllSitesConfirmText)
          }, null, 8, _hoisted_62$1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_ChangePermToAllSitesConfirm2")), 1),
          _ctx.allWebsitesAccssLevelSet === "admin" ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_63$1, [
            vue.createElementVNode("em", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Note")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "UsersManager_AdminUserRoleChangeWarning",
                  String(_ctx.getRoleDisplay(_ctx.allWebsitesAccssLevelSet))
                ))
              }, null, 8, _hoisted_64$1)
            ])
          ])) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed"])
    ], 2);
  }
  const UserPermissionsEdit = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  const DEFAULT_USER$1 = {
    login: "",
    superuser_access: false,
    uses_2fa: false,
    password: "",
    email: "",
    invite_status: ""
  };
  const _sfc_main$b = vue.defineComponent({
    props: {
      user: Object,
      currentUserRole: {
        type: String,
        required: true
      },
      accessLevels: {
        type: Array,
        required: true
      },
      filterAccessLevels: {
        type: Array,
        required: true
      },
      activatedPlugins: {
        type: Array,
        required: true
      },
      passwordStrengthValidationRules: {
        type: Array,
        default: () => []
      }
    },
    components: {
      Notification: CoreHome.Notification,
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton,
      UserPermissionsEdit,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      Form: CorePluginsAdmin.Form,
      AutoClearPassword: CoreHome.AutoClearPassword
    },
    data() {
      return {
        theUser: this.user || __spreadValues({}, DEFAULT_USER$1),
        activeTab: "basic",
        permissionsForIdSite: 1,
        isSavingUserInfo: false,
        userHasAccess: true,
        isUserModified: false,
        isPasswordModified: false,
        superUserAccessChecked: null,
        showPasswordConfirmationForSuperUser: false,
        showPasswordConfirmationFor2FA: false,
        isResetting2FA: false,
        isShowingPasswordConfirm: false
      };
    },
    emits: ["done", "updated", "resendInvite"],
    watch: {
      user(newVal) {
        this.onUserChange(newVal);
      }
    },
    created() {
      this.onUserChange(__spreadValues({}, this.user));
    },
    methods: {
      onUserChange(newVal) {
        this.theUser = newVal || __spreadValues({}, DEFAULT_USER$1);
        if (!this.theUser.password) {
          this.resetPasswordVar();
        }
        this.setSuperUserAccessChecked();
      },
      confirmSuperUserChange() {
        this.showPasswordConfirmationForSuperUser = true;
      },
      confirmReset2FA() {
        this.showPasswordConfirmationFor2FA = true;
      },
      toggleSuperuserAccess(password) {
        this.isSavingUserInfo = true;
        CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.setSuperUserAccess"
          },
          {
            userLogin: this.theUser.login,
            hasSuperUserAccess: this.theUser.superuser_access ? "0" : "1",
            passwordConfirmation: password
          }
        ).then(() => {
          this.theUser = __spreadProps(__spreadValues({}, this.theUser), { superuser_access: !this.theUser.superuser_access });
        }).catch(() => {
        }).finally(() => {
          this.isSavingUserInfo = false;
          this.setSuperUserAccessChecked();
        });
      },
      resendRequestedUser() {
        this.$emit("resendInvite", {
          user: this.user
        });
      },
      resetPasswordVar() {
        this.theUser.password = "XXXXXXXX";
      },
      showUserSavedNotification() {
        CoreHome.NotificationsStore.show({
          message: CoreHome.translate("General_YourChangesHaveBeenSaved"),
          context: "success",
          type: "toast"
        });
      },
      reset2FA(password) {
        this.isResetting2FA = true;
        return CoreHome.AjaxHelper.post({
          method: "TwoFactorAuth.resetTwoFactorAuth"
        }, {
          userLogin: this.theUser.login,
          passwordConfirmation: password
        }).catch((e) => {
          this.isResetting2FA = false;
          throw e;
        }).then(() => {
          this.isResetting2FA = false;
          this.theUser.uses_2fa = false;
          this.activeTab = "basic";
          this.showUserSavedNotification();
        });
      },
      updateUser(password) {
        this.isSavingUserInfo = true;
        return CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.updateUser"
          },
          {
            userLogin: this.theUser.login,
            password: this.isPasswordModified && this.theUser.password ? this.theUser.password : void 0,
            passwordConfirmation: password,
            email: this.theUser.email
          }
        ).then(() => {
          this.isSavingUserInfo = false;
          this.isUserModified = true;
          this.isPasswordModified = false;
          this.resetPasswordVar();
          this.showUserSavedNotification();
          this.$emit("updated", { user: this.theUser });
        }).catch(() => {
          this.isSavingUserInfo = false;
        });
      },
      setSuperUserAccessChecked() {
        this.superUserAccessChecked = !!this.theUser.superuser_access;
      },
      onDoneEditing() {
        this.$emit("done", { isUserModified: this.isUserModified });
      },
      translateSuperUserRiskString(item) {
        return CoreHome.translate(
          `UsersManager_SuperUserRisk${item}`,
          "<strong>",
          "</strong>"
        );
      }
    },
    computed: {
      isPending() {
        if (!this.user) {
          return true;
        }
        if (this.user.invite_status === "pending" || Number.isInteger(this.user.invite_status)) {
          return true;
        }
        return false;
      },
      changePasswordTitle() {
        return CoreHome.translate(
          "UsersManager_AreYouSureChangeDetails",
          `<strong>${this.theUser.login}</strong>`
        );
      },
      isPluginsAdminEnabled() {
        return CoreHome.Matomo.config.enable_plugins_admin;
      },
      isActivityLogPluginEnabled() {
        return this.activatedPlugins.includes("ActivityLog");
      },
      isMarketplacePluginEnabled() {
        return this.activatedPlugins.includes("Marketplace");
      },
      isProfessionalServicesPluginEnabled() {
        return this.activatedPlugins.includes("ProfessionalServices");
      },
      accountabilityRisk() {
        const riskInfo = this.translateSuperUserRiskString("Accountability");
        let pluginInfo = "";
        if (this.isPluginsAdminEnabled && this.isProfessionalServicesPluginEnabled) {
          if (this.isActivityLogPluginEnabled) {
            pluginInfo = CoreHome.translate(
              "UsersManager_SuperUserRiskAccountabilityCheckActivityLog",
              '<a href="?module=ActivityLog&action=index" rel="noreferrer noopener" target="_blank">',
              "</a>"
            );
          } else if (this.isMarketplacePluginEnabled) {
            pluginInfo = CoreHome.translate(
              "UsersManager_SuperUserRiskAccountabilityGetActivityLogPlugin",
              CoreHome.externalLink("https://plugins.matomo.org/ActivityLog"),
              "</a>"
            );
          }
        }
        return pluginInfo ? `${riskInfo} ${pluginInfo}` : riskInfo;
      },
      isCurrentUser() {
        return this.theUser.login === CoreHome.Matomo.userLogin;
      },
      superUserAccessTooltipText() {
        if (this.isCurrentUser) {
          return CoreHome.translate("UsersManager_CannotRevokeOwnSuperuserAccess");
        }
        return "";
      }
    }
  });
  const _hoisted_1$b = { class: "row" };
  const _hoisted_2$a = { class: "col m2 entityList" };
  const _hoisted_3$8 = { class: "listCircle" };
  const _hoisted_4$7 = {
    key: 0,
    class: "icon-warning"
  };
  const _hoisted_5$7 = {
    href: "",
    class: "entityCancelLink"
  };
  const _hoisted_6$7 = { class: "visibleTab col m10" };
  const _hoisted_7$6 = {
    key: 0,
    class: "basic-info-tab"
  };
  const _hoisted_8$5 = { class: "email-input" };
  const _hoisted_9$5 = {
    class: "form-group row",
    style: { "position": "relative" }
  };
  const _hoisted_10$4 = { class: "col s12 m6 save-button" };
  const _hoisted_11$4 = {
    key: 0,
    class: "resend-notes"
  };
  const _hoisted_12$3 = ["innerHTML"];
  const _hoisted_13$3 = { class: "user-permissions" };
  const _hoisted_14$3 = { key: 0 };
  const _hoisted_15$3 = {
    key: 1,
    class: "alert alert-info"
  };
  const _hoisted_16$3 = {
    key: 1,
    class: "superuser-access form-group"
  };
  const _hoisted_17$2 = { key: 0 };
  const _hoisted_18$2 = { key: 1 };
  const _hoisted_19$2 = { class: "browser-default" };
  const _hoisted_20$2 = ["innerHTML"];
  const _hoisted_21$2 = ["innerHTML"];
  const _hoisted_22$2 = ["innerHTML"];
  const _hoisted_23$1 = ["innerHTML"];
  const _hoisted_24$1 = ["innerHTML"];
  const _hoisted_25$1 = ["innerHTML"];
  const _hoisted_26$1 = ["innerHTML"];
  const _hoisted_27$1 = ["innerHTML"];
  const _hoisted_28$1 = ["title"];
  const _hoisted_29$1 = { key: 0 };
  const _hoisted_30$1 = { key: 1 };
  const _hoisted_31$1 = {
    key: 2,
    class: "twofa-reset form-group"
  };
  const _hoisted_32$1 = { class: "resetTwoFa" };
  const _hoisted_33$1 = ["innerHTML"];
  const _hoisted_34$1 = ["innerHTML"];
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_UserPermissionsEdit = vue.resolveComponent("UserPermissionsEdit");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_auto_clear_password = vue.resolveDirective("auto-clear-password");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      class: vue.normalizeClass(["userEditForm", { loading: _ctx.isSavingUserInfo }]),
      "content-title": _ctx.theUser.login
    }, {
      default: vue.withCtx(() => [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$b, [
          vue.createElementVNode("div", _hoisted_2$a, [
            vue.createElementVNode("ul", _hoisted_3$8, [
              vue.createElementVNode("li", {
                class: vue.normalizeClass([{ active: _ctx.activeTab === "basic" }, "menuBasicInfo"])
              }, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.activeTab = "basic", ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_BasicInformation")), 1)
              ], 2),
              vue.createElementVNode("li", {
                class: vue.normalizeClass([{ active: _ctx.activeTab === "permissions" }, "menuPermissions"])
              }, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.activeTab = "permissions", ["prevent"])),
                  style: { "margin-right": "3.5px" }
                }, vue.toDisplayString(_ctx.translate("UsersManager_Permissions")), 1),
                !_ctx.userHasAccess && !_ctx.theUser.superuser_access ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$7)) : vue.createCommentVNode("", true)
              ], 2),
              _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("li", {
                key: 0,
                class: vue.normalizeClass([{ active: _ctx.activeTab === "superuser" }, "menuSuperuser"])
              }, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => _ctx.activeTab = "superuser", ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_SuperUserAccess")), 1)
              ], 2)) : vue.createCommentVNode("", true),
              _ctx.currentUserRole === "superuser" && _ctx.theUser.uses_2fa ? (vue.openBlock(), vue.createElementBlock("li", {
                key: 1,
                class: vue.normalizeClass([{ active: _ctx.activeTab === "2fa" }, "menuUserTwoFa"])
              }, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[3] || (_cache[3] = vue.withModifiers(($event) => _ctx.activeTab = "2fa", ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_TwoFactorAuthentication")), 1)
              ], 2)) : vue.createCommentVNode("", true)
            ]),
            _cache[20] || (_cache[20] = vue.createElementVNode("div", { class: "save-button-spacer hide-on-small-only" }, null, -1)),
            vue.createElementVNode("div", {
              class: "entityCancel",
              onClick: _cache[4] || (_cache[4] = vue.withModifiers(($event) => _ctx.onDoneEditing(), ["prevent"]))
            }, [
              vue.createElementVNode("a", _hoisted_5$7, [
                _cache[19] || (_cache[19] = vue.createElementVNode("span", { class: "icon-arrow-left" }, "  ", -1)),
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_BackToUser")), 1)
              ])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_6$7, [
            _ctx.activeTab === "basic" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$6, [
              vue.createElementVNode("div", null, [
                vue.createVNode(_component_Field, {
                  modelValue: _ctx.theUser.login,
                  "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.theUser.login = $event),
                  disabled: true,
                  autocomplete: "off",
                  uicontrol: "text",
                  name: "user_login",
                  maxlength: 100,
                  title: _ctx.translate("General_Username")
                }, null, 8, ["modelValue", "title"])
              ]),
              vue.createElementVNode("div", null, [
                !_ctx.isPending ? vue.withDirectives((vue.openBlock(), vue.createBlock(_component_Field, {
                  key: 0,
                  "model-value": _ctx.theUser.password,
                  disabled: _ctx.isSavingUserInfo || _ctx.currentUserRole !== "superuser" || _ctx.isShowingPasswordConfirm,
                  "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => {
                    _ctx.theUser.password = $event;
                    _ctx.isPasswordModified = true;
                  }),
                  uicontrol: "password",
                  name: "user_password",
                  autocomplete: "new-password",
                  title: _ctx.translate("General_Password"),
                  "ui-control-attributes": {
                    passwordStrengthValidationRules: _ctx.passwordStrengthValidationRules
                  }
                }, null, 8, ["model-value", "disabled", "title", "ui-control-attributes"])), [
                  [_directive_auto_clear_password]
                ]) : vue.createCommentVNode("", true)
              ]),
              vue.createElementVNode("div", _hoisted_8$5, [
                _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createBlock(_component_Field, {
                  key: 0,
                  modelValue: _ctx.theUser.email,
                  "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.theUser.email = $event),
                  disabled: _ctx.isSavingUserInfo || _ctx.currentUserRole !== "superuser" || _ctx.isShowingPasswordConfirm,
                  uicontrol: "text",
                  name: "user_email",
                  autocomplete: "off",
                  maxlength: 100,
                  title: _ctx.translate("UsersManager_Email")
                }, null, 8, ["modelValue", "disabled", "title"])) : vue.createCommentVNode("", true)
              ]),
              vue.createElementVNode("div", null, [
                vue.createElementVNode("div", _hoisted_9$5, [
                  vue.createElementVNode("div", _hoisted_10$4, [
                    _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createBlock(_component_SaveButton, {
                      key: 0,
                      value: _ctx.translate("UsersManager_SaveBasicInfo"),
                      saving: _ctx.isSavingUserInfo,
                      onConfirm: _cache[8] || (_cache[8] = ($event) => _ctx.isShowingPasswordConfirm = true)
                    }, null, 8, ["value", "saving"])) : vue.createCommentVNode("", true)
                  ])
                ]),
                _ctx.user && _ctx.isPending ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_11$4, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_InvitationSent")) + " ", 1),
                  vue.createElementVNode("span", {
                    class: "resend-link",
                    onClick: _cache[9] || (_cache[9] = (...args) => _ctx.resendRequestedUser && _ctx.resendRequestedUser(...args)),
                    innerHTML: _ctx.$sanitize(_ctx.translate("UsersManager_ResendInvite") + "/" + _ctx.translate("UsersManager_CopyLink"))
                  }, null, 8, _hoisted_12$3)
                ])) : vue.createCommentVNode("", true)
              ])
            ])) : vue.createCommentVNode("", true),
            vue.withDirectives(vue.createElementVNode("div", _hoisted_13$3, [
              !_ctx.theUser.superuser_access ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14$3, [
                vue.createVNode(_component_UserPermissionsEdit, {
                  "user-login": _ctx.theUser.login,
                  onUserHasAccessDetected: _cache[10] || (_cache[10] = ($event) => _ctx.userHasAccess = $event.hasAccess),
                  onAccessChanged: _cache[11] || (_cache[11] = ($event) => _ctx.isUserModified = true),
                  "access-levels": _ctx.accessLevels,
                  "filter-access-levels": _ctx.filterAccessLevels
                }, null, 8, ["user-login", "access-levels", "filter-access-levels"])
              ])) : vue.createCommentVNode("", true),
              _ctx.theUser.superuser_access ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15$3, vue.toDisplayString(_ctx.translate("UsersManager_SuperUsersPermissionsNotice")), 1)) : vue.createCommentVNode("", true)
            ], 512), [
              [vue.vShow, _ctx.activeTab === "permissions"]
            ]),
            _ctx.activeTab === "superuser" && _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_16$3, [
              _ctx.isMarketplacePluginEnabled ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_17$2, vue.toDisplayString(_ctx.translate("UsersManager_SuperUserIntro1")), 1)) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_18$2, vue.toDisplayString(_ctx.translate("UsersManager_SuperUserIntro1WithoutMarketplace")), 1)),
              vue.createElementVNode("p", null, [
                vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("UsersManager_SuperUserIntro2")), 1)
              ]),
              vue.createElementVNode("p", null, [
                vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("UsersManager_SuperUserIntro3")), 1)
              ]),
              vue.createElementVNode("ul", _hoisted_19$2, [
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("Data"))
                }, null, 8, _hoisted_20$2),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("Security"))
                }, null, 8, _hoisted_21$2),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("Misconfiguration"))
                }, null, 8, _hoisted_22$2),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("UserManagement"))
                }, null, 8, _hoisted_23$1),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("ServiceDisruption"))
                }, null, 8, _hoisted_24$1),
                _ctx.isPluginsAdminEnabled && _ctx.isMarketplacePluginEnabled ? (vue.openBlock(), vue.createElementBlock("li", {
                  key: 0,
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("Marketplace"))
                }, null, 8, _hoisted_25$1)) : vue.createCommentVNode("", true),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.accountabilityRisk)
                }, null, 8, _hoisted_26$1),
                vue.createElementVNode("li", {
                  innerHTML: _ctx.$sanitize(_ctx.translateSuperUserRiskString("Compliance"))
                }, null, 8, _hoisted_27$1)
              ]),
              vue.createElementVNode("div", {
                class: vue.normalizeClass({ "disabled": _ctx.isCurrentUser }),
                title: _ctx.superUserAccessTooltipText
              }, [
                vue.createVNode(_component_Field, {
                  modelValue: _ctx.superUserAccessChecked,
                  "onUpdate:modelValue": [
                    _cache[12] || (_cache[12] = ($event) => _ctx.superUserAccessChecked = $event),
                    _cache[13] || (_cache[13] = ($event) => _ctx.confirmSuperUserChange())
                  ],
                  disabled: _ctx.isCurrentUser,
                  uicontrol: "checkbox",
                  name: "superuser_access",
                  title: _ctx.translate("UsersManager_HasSuperUserAccess")
                }, null, 8, ["modelValue", "disabled", "title"])
              ], 10, _hoisted_28$1),
              vue.createVNode(_component_PasswordConfirmation, {
                modelValue: _ctx.showPasswordConfirmationForSuperUser,
                "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => _ctx.showPasswordConfirmationForSuperUser = $event),
                onConfirmed: _ctx.toggleSuperuserAccess,
                onAborted: _cache[15] || (_cache[15] = ($event) => _ctx.setSuperUserAccessChecked())
              }, {
                default: vue.withCtx(() => [
                  vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("UsersManager_AreYouSure")), 1),
                  _ctx.theUser.superuser_access ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_29$1, vue.toDisplayString(_ctx.translate("UsersManager_RemoveSuperuserAccessConfirm")), 1)) : vue.createCommentVNode("", true),
                  !_ctx.theUser.superuser_access ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_30$1, vue.toDisplayString(_ctx.translate("UsersManager_AddSuperuserAccessConfirm")), 1)) : vue.createCommentVNode("", true)
                ]),
                _: 1
              }, 8, ["modelValue", "onConfirmed"])
            ])) : vue.createCommentVNode("", true),
            _ctx.currentUserRole === "superuser" ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_31$1, [
              vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_ResetTwoFactorAuthenticationInfo")), 1),
              vue.createElementVNode("div", _hoisted_32$1, [
                vue.createVNode(_component_SaveButton, {
                  saving: _ctx.isResetting2FA,
                  onConfirm: _cache[16] || (_cache[16] = ($event) => _ctx.confirmReset2FA()),
                  value: _ctx.translate("UsersManager_ResetTwoFactorAuthentication")
                }, null, 8, ["saving", "value"])
              ]),
              vue.createVNode(_component_PasswordConfirmation, {
                modelValue: _ctx.showPasswordConfirmationFor2FA,
                "onUpdate:modelValue": _cache[17] || (_cache[17] = ($event) => _ctx.showPasswordConfirmationFor2FA = $event),
                onConfirmed: _ctx.reset2FA
              }, {
                default: vue.withCtx(() => [
                  vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("UsersManager_AreYouSure")), 1)
                ]),
                _: 1
              }, 8, ["modelValue", "onConfirmed"])
            ], 512)), [
              [vue.vShow, _ctx.activeTab === "2fa"]
            ]) : vue.createCommentVNode("", true)
          ])
        ])), [
          [_directive_form]
        ]),
        vue.createVNode(_component_PasswordConfirmation, {
          modelValue: _ctx.isShowingPasswordConfirm,
          "onUpdate:modelValue": _cache[18] || (_cache[18] = ($event) => _ctx.isShowingPasswordConfirm = $event),
          onConfirmed: _ctx.updateUser
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("h2", {
              innerHTML: _ctx.$sanitize(_ctx.changePasswordTitle)
            }, null, 8, _hoisted_33$1),
            _ctx.user && _ctx.isPending ? (vue.openBlock(), vue.createBlock(_component_Notification, {
              key: 0,
              context: "info",
              noclear: true
            }, {
              default: vue.withCtx(() => [
                vue.createElementVNode("strong", {
                  innerHTML: _ctx.$sanitize(_ctx.translate("UsersManager_InviteEmailChange"))
                }, null, 8, _hoisted_34$1)
              ]),
              _: 1
            })) : vue.createCommentVNode("", true)
          ]),
          _: 1
        }, 8, ["modelValue", "onConfirmed"])
      ]),
      _: 1
    }, 8, ["class", "content-title"]);
  }
  const UserEditForm = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  const DEFAULT_USER = {
    login: "",
    superuser_access: false,
    uses_2fa: false,
    password: "",
    email: "",
    invite_status: ""
  };
  const _sfc_main$a = vue.defineComponent({
    props: {
      initialSiteId: {
        type: [String, Number],
        required: true
      },
      initialSiteName: {
        type: String,
        required: true
      },
      inviteTokenExpiryDays: {
        type: String,
        required: true
      }
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
    data() {
      return {
        theUser: __spreadValues({}, DEFAULT_USER),
        isInvitingUser: false,
        firstSiteAccess: {
          id: this.initialSiteId,
          name: this.initialSiteName
        },
        showPasswordConfirmation: false
      };
    },
    emits: ["aborted", "invited"],
    methods: {
      inviteUser(password) {
        this.isInvitingUser = true;
        return CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.inviteUser"
          },
          {
            userLogin: this.theUser.login,
            email: this.theUser.email,
            initialIdSite: this.firstSiteAccess ? this.firstSiteAccess.id : void 0,
            passwordConfirmation: password
          }
        ).then(() => {
          this.firstSiteAccess = {
            id: this.initialSiteId,
            name: this.initialSiteName
          };
          this.theUser.invite_status = "pending";
          this.showUserInvitedNotification();
          this.$emit("invited", { user: this.theUser });
          this.theUser = DEFAULT_USER;
        }).finally(() => {
          this.isInvitingUser = false;
        });
      },
      showUserInvitedNotification() {
        CoreHome.NotificationsStore.show({
          message: CoreHome.translate("UsersManager_InviteSuccess"),
          context: "success",
          type: "toast"
        });
      },
      abort() {
        this.theUser = DEFAULT_USER;
        this.firstSiteAccess = null;
        this.$emit("aborted");
      }
    }
  });
  const _hoisted_1$a = { class: "row" };
  const _hoisted_2$9 = { class: "col s12 m6 invite-notes" };
  const _hoisted_3$7 = { class: "form-help" };
  const _hoisted_4$6 = ["innerHTML"];
  const _hoisted_5$6 = { class: "col m10" };
  const _hoisted_6$6 = { class: "email-input" };
  const _hoisted_7$5 = {
    class: "form-group row",
    style: { "position": "relative" }
  };
  const _hoisted_8$4 = { class: "col s12 m6 save-button" };
  const _hoisted_9$4 = { class: "entityCancel" };
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      class: "userInviteForm",
      "content-title": _ctx.translate("UsersManager_InviteNewUser")
    }, {
      default: vue.withCtx(() => [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$a, [
          vue.createElementVNode("div", _hoisted_2$9, [
            vue.createElementVNode("div", _hoisted_3$7, [
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(
                  _ctx.translate(
                    "UsersManager_InviteSuccessNotification",
                    [_ctx.inviteTokenExpiryDays]
                  )
                )
              }, null, 8, _hoisted_4$6)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_5$6, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                modelValue: _ctx.theUser.login,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.theUser.login = $event),
                disabled: _ctx.isInvitingUser,
                autocomplete: "off",
                uicontrol: "text",
                name: "user_login",
                maxlength: 100,
                title: _ctx.translate("General_Username")
              }, null, 8, ["modelValue", "disabled", "title"])
            ]),
            vue.createElementVNode("div", _hoisted_6$6, [
              vue.createVNode(_component_Field, {
                modelValue: _ctx.theUser.email,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.theUser.email = $event),
                disabled: _ctx.isInvitingUser,
                uicontrol: "text",
                name: "user_email",
                autocomplete: "off",
                maxlength: 100,
                title: _ctx.translate("UsersManager_Email")
              }, null, 8, ["modelValue", "disabled", "title"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                modelValue: _ctx.firstSiteAccess,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.firstSiteAccess = $event),
                disabled: _ctx.isInvitingUser,
                uicontrol: "site",
                name: "user_site",
                "ui-control-attributes": { onlySitesWithAdminAccess: true },
                title: _ctx.translate("UsersManager_FirstWebsitePermission"),
                "inline-help": _ctx.translate("UsersManager_FirstSiteInlineHelp")
              }, null, 8, ["modelValue", "disabled", "title", "inline-help"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createElementVNode("div", _hoisted_7$5, [
                vue.createElementVNode("div", _hoisted_8$4, [
                  vue.createVNode(_component_SaveButton, {
                    value: _ctx.translate("UsersManager_InviteUser"),
                    disabled: !_ctx.firstSiteAccess || !_ctx.firstSiteAccess.id || !_ctx.theUser.login || !_ctx.theUser.email,
                    saving: _ctx.isInvitingUser,
                    onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.showPasswordConfirmation = true)
                  }, null, 8, ["value", "disabled", "saving"])
                ])
              ]),
              vue.createVNode(_component_PasswordConfirmation, {
                modelValue: _ctx.showPasswordConfirmation,
                "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.showPasswordConfirmation = $event),
                onConfirmed: _ctx.inviteUser
              }, null, 8, ["modelValue", "onConfirmed"])
            ]),
            vue.createElementVNode("div", _hoisted_9$4, [
              vue.createElementVNode("a", {
                href: "",
                class: "entityCancelLink",
                onClick: _cache[5] || (_cache[5] = vue.withModifiers(($event) => _ctx.abort(), ["prevent"]))
              }, [
                _cache[6] || (_cache[6] = vue.createElementVNode("span", { class: "icon icon-arrow-left" }, "  ", -1)),
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_BackToUser")), 1)
              ])
            ])
          ])
        ])), [
          [_directive_form]
        ])
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const UserInvite = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const _sfc_main$9 = vue.defineComponent({
    props: {
      user: {
        type: Object,
        required: false
      },
      inviteTokenExpiryDays: {
        type: String,
        required: true
      }
    },
    components: {
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    data() {
      return {
        copied: false,
        showPasswordConfirmationForInviteAction: false,
        inviteAction: "",
        loading: false
      };
    },
    emits: ["close"],
    watch: {
      user(newUser) {
        if (!newUser) {
          return;
        }
        $(this.$refs.resendInviteConfirmModal).modal({
          dismissible: false,
          onCloseEnd: () => this.$emit("close")
        }).modal("open");
        this.copied = false;
      }
    },
    methods: {
      showInviteActionPasswordConfirm(action) {
        if (this.loading) {
          return;
        }
        this.showPasswordConfirmationForInviteAction = true;
        this.inviteAction = action;
      },
      onInviteAction(password) {
        if (this.inviteAction === "send") {
          this.onResendInvite(password);
        } else {
          this.generateInviteLink(password);
        }
      },
      onResendInvite(password) {
        if (password === "") return;
        CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.resendInvite",
            userLogin: this.user.login
          },
          {
            passwordConfirmation: password
          }
        ).then(() => {
          $(this.$refs.resendInviteConfirmModal).modal("close");
          const id = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("UsersManager_InviteSuccess"),
            id: "resendInvite",
            context: "success",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(id);
        });
      },
      generateInviteLink(password) {
        return __async(this, null, function* () {
          if (this.loading) {
            return;
          }
          this.loading = true;
          try {
            const res = yield CoreHome.AjaxHelper.post(
              {
                method: "UsersManager.generateInviteLink"
              },
              {
                userLogin: this.user.login,
                passwordConfirmation: password
              }
            );
            yield this.copyToClipboard(res.value);
          } catch (e) {
          }
          this.loading = false;
        });
      },
      copyToClipboard(value) {
        return __async(this, null, function* () {
          try {
            const tempInput = document.createElement("input");
            tempInput.style.top = "-100px";
            tempInput.style.left = "0";
            tempInput.style.position = "fixed";
            tempInput.value = value;
            document.body.appendChild(tempInput);
            tempInput.select();
            if (window.location.protocol !== "https:") {
              document.execCommand("copy");
            } else {
              yield navigator.clipboard.writeText(tempInput.value);
            }
            document.body.removeChild(tempInput);
            this.copied = true;
          } catch (e) {
            const id = CoreHome.NotificationsStore.show({
              message: `<strong>${CoreHome.translate("UsersManager_CopyDenied")}</strong><br>
${CoreHome.translate("UsersManager_CopyDeniedHints", [`<br><span class="invite-link">${value}</span>`])}`,
              id: "copyError",
              context: "error",
              type: "transient"
            });
            CoreHome.NotificationsStore.scrollToNotification(id);
          }
        });
      }
    }
  });
  const _hoisted_1$9 = {
    class: "resend-invite-confirm-modal modal",
    ref: "resendInviteConfirmModal"
  };
  const _hoisted_2$8 = { class: "modal-content" };
  const _hoisted_3$6 = { class: "modal-title" };
  const _hoisted_4$5 = ["innerHTML"];
  const _hoisted_5$5 = { class: "modal-footer" };
  const _hoisted_6$5 = {
    key: 0,
    class: "success-copied"
  };
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2, _b;
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForInviteAction,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.showPasswordConfirmationForInviteAction = $event),
        onConfirmed: _ctx.onInviteAction
      }, null, 8, ["modelValue", "onConfirmed"]),
      vue.createElementVNode("div", _hoisted_1$9, [
        _cache[4] || (_cache[4] = vue.createElementVNode("div", { class: "btn-close modal-close" }, [
          vue.createElementVNode("i", { class: "icon-close" })
        ], -1)),
        vue.createElementVNode("div", _hoisted_2$8, [
          vue.createElementVNode("h2", _hoisted_3$6, vue.toDisplayString(_ctx.translate("UsersManager_ResendInvite")), 1),
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.translate(
              "UsersManager_InviteConfirmMessage",
              [
                `<strong>${(_a2 = _ctx.user) == null ? void 0 : _a2.login}</strong>`,
                `<strong>${(_b = _ctx.user) == null ? void 0 : _b.email}</strong>`
              ]
            ))
          }, null, 8, _hoisted_4$5),
          vue.createElementVNode("p", null, [
            vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("UsersManager_InviteActionNotes", _ctx.inviteTokenExpiryDays)), 1)
          ])
        ]),
        vue.createElementVNode("div", _hoisted_5$5, [
          _ctx.copied ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_6$5, [
            _cache[3] || (_cache[3] = vue.createElementVNode("i", { class: "icon-success" }, null, -1)),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_LinkCopied")), 1)
          ])) : vue.createCommentVNode("", true),
          vue.createElementVNode("button", {
            onClick: _cache[1] || (_cache[1] = ($event) => _ctx.showInviteActionPasswordConfirm("copy")),
            class: "btn btn-copy-link modal-action",
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("UsersManager_CopyLink")), 1),
          vue.createElementVNode("button", {
            class: "btn btn-resend modal-action modal-no",
            onClick: _cache[2] || (_cache[2] = ($event) => _ctx.showInviteActionPasswordConfirm("send"))
          }, vue.toDisplayString(_ctx.translate("UsersManager_ResendInvite")), 1)
        ])
      ], 512)
    ], 64);
  }
  const ResendInviteModal = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const { $: $$3 } = window;
  const _sfc_main$8 = vue.defineComponent({
    props: {
      initialSiteId: {
        type: [String, Number],
        required: true
      },
      initialSiteName: {
        type: String,
        required: true
      },
      currentUserRole: String,
      isLoadingUsers: Boolean,
      accessLevels: {
        type: Array,
        required: true
      },
      filterAccessLevels: {
        type: Array,
        required: true
      },
      filterStatusLevels: {
        type: Array,
        required: true
      },
      totalEntries: Number,
      users: {
        type: Array,
        required: true
      },
      searchParams: {
        type: Object,
        required: true
      }
    },
    components: {
      Field: CorePluginsAdmin.Field,
      ActivityIndicator: CoreHome.ActivityIndicator,
      Notification: CoreHome.Notification,
      ContentBlock: CoreHome.ContentBlock,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      DropdownMenu: CoreHome.DropdownMenu,
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        areAllResultsSelected: false,
        selectedRows: {},
        isAllCheckboxSelected: false,
        isBulkActionsDisabled: true,
        userToChange: null,
        roleToChangeTo: null,
        accessLevelFilter: null,
        statusLevelFilter: null,
        isRoleHelpToggled: false,
        userTextFilter: "",
        permissionsForSite: {
          id: this.initialSiteId,
          name: this.initialSiteName
        },
        showPasswordConfirmationForUserRemoval: false,
        showPasswordConfirmationForAccessChange: false,
        showPasswordConfirmationForUserSignOut: false
      };
    },
    emits: ["editUser", "changeUserRole", "deleteUser", "searchChange", "resendInvite", "signOutUser"],
    created() {
      this.onUserTextFilterChange = CoreHome.debounce(this.onUserTextFilterChange, 300);
    },
    watch: {
      users() {
        this.clearSelection();
      }
    },
    methods: {
      getInviteStatus(inviteStatus) {
        if (Number.isInteger(inviteStatus)) {
          return CoreHome.translate("UsersManager_InviteDayLeft", inviteStatus);
        }
        if (inviteStatus === "expired") {
          return CoreHome.translate("UsersManager_Expired");
        }
        return CoreHome.translate("UsersManager_Active");
      },
      onPermissionsForUpdate(site) {
        this.permissionsForSite = site;
        this.changeSearch({ idSite: this.permissionsForSite.id });
      },
      clearSelection() {
        this.selectedRows = {};
        this.areAllResultsSelected = false;
        this.isBulkActionsDisabled = true;
        this.isAllCheckboxSelected = false;
        this.userToChange = null;
      },
      resetUserAndRoleToChange() {
        this.userToChange = null;
        this.roleToChangeTo = null;
      },
      onAllCheckboxChange() {
        if (!this.isAllCheckboxSelected) {
          this.clearSelection();
        } else {
          for (let i = 0; i !== this.users.length; i += 1) {
            this.selectedRows[i] = true;
          }
          this.isBulkActionsDisabled = false;
        }
      },
      changeUserRole(password = "") {
        this.$emit("changeUserRole", {
          users: this.userOperationSubject,
          role: this.roleToChangeTo,
          password
        });
      },
      onRowSelected() {
        const selectedRowKeyCount = this.selectedCount;
        this.isBulkActionsDisabled = selectedRowKeyCount === 0;
        this.isAllCheckboxSelected = selectedRowKeyCount === this.users.length;
      },
      deleteRequestedUsers(password) {
        this.$emit("deleteUser", {
          users: this.userOperationSubject,
          password
        });
      },
      resendRequestedUser() {
        this.$emit("resendInvite", {
          user: this.userToChange
        });
      },
      showDeleteConfirm() {
        this.showPasswordConfirmationForUserRemoval = true;
      },
      showSignOutConfirm() {
        this.showPasswordConfirmationForUserSignOut = true;
      },
      signOutRequestedUser(password) {
        var _a2;
        this.$emit("signOutUser", {
          userLogin: ((_a2 = this.userToChange) == null ? void 0 : _a2.login) || null,
          password
        });
      },
      showAccessChangeConfirm() {
        const grantsAnonymousView = this.changeAffectsAnonymous && this.roleToChangeTo === "view";
        const grantsAdminRole = this.roleToChangeTo === "admin";
        if (grantsAnonymousView || grantsAdminRole) {
          this.showPasswordConfirmationForAccessChange = true;
        } else {
          $$3(this.$refs.changeUserRoleConfirmModal).modal({
            dismissible: false
          }).modal("open");
        }
      },
      getRoleDisplay(role) {
        let result = role;
        this.accessLevels.forEach((entry) => {
          if (entry.key === role) {
            result = entry.value;
          }
        });
        return result;
      },
      changeSearch(changes) {
        const params = __spreadValues(__spreadValues({}, this.searchParams), changes);
        this.$emit("searchChange", { params });
      },
      gotoPreviousPage() {
        this.changeSearch({
          offset: Math.max(0, this.searchParams.offset - this.searchParams.limit)
        });
      },
      gotoNextPage() {
        const newOffset = this.searchParams.offset + this.searchParams.limit;
        if (newOffset >= this.totalEntries) {
          return;
        }
        this.changeSearch({
          offset: newOffset
        });
      },
      onUserTextFilterChange(filter) {
        this.userTextFilter = filter;
        this.changeSearch({
          filter_search: filter,
          offset: 0
        });
      }
    },
    computed: {
      currentUserLogin() {
        return CoreHome.Matomo.userLogin;
      },
      paginationLowerBound() {
        return this.searchParams.offset + 1;
      },
      paginationUpperBound() {
        if (this.totalEntries === null) {
          return "?";
        }
        const searchParams = this.searchParams;
        return Math.min(searchParams.offset + searchParams.limit, this.totalEntries);
      },
      userOperationSubject() {
        if (this.userToChange) {
          return [this.userToChange];
        }
        if (this.areAllResultsSelected) {
          return "all";
        }
        return this.selectedUsers;
      },
      changeAffectsAnonymous() {
        return this.userOperationSubject === "all" || Array.isArray(this.userOperationSubject) && this.userOperationSubject.some((user) => user.login === "anonymous");
      },
      selectedUsers() {
        const users = this.users;
        const result = [];
        Object.keys(this.selectedRows).forEach((index) => {
          const indexN = parseInt(index, 10);
          if (this.selectedRows[index] && users[indexN]) {
            result.push(users[indexN]);
          }
        });
        return result;
      },
      rolesHelpText() {
        return CoreHome.translate(
          "UsersManager_RolesHelp",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_70/"),
          "</a>",
          CoreHome.externalLink("https://matomo.org/faq/general/faq_69/"),
          "</a>"
        );
      },
      affectedUsersCount() {
        if (this.areAllResultsSelected) {
          return this.totalEntries || 0;
        }
        return this.selectedCount;
      },
      selectedCount() {
        let selectedRowKeyCount = 0;
        Object.keys(this.selectedRows).forEach((key) => {
          if (this.selectedRows[key]) {
            selectedRowKeyCount += 1;
          }
        });
        return selectedRowKeyCount;
      },
      deleteUserPermConfirmSingleText() {
        var _a2, _b;
        return CoreHome.translate(
          "UsersManager_DeleteUserPermConfirmSingle",
          `<strong>${((_a2 = this.userToChange) == null ? void 0 : _a2.login) || ""}</strong>`,
          `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
          `<strong>${CoreHome.Matomo.helper.htmlEntities(((_b = this.permissionsForSite) == null ? void 0 : _b.name) || "")}</strong>`
        );
      },
      deleteUserPermConfirmMultipleText() {
        var _a2;
        return CoreHome.translate(
          "UsersManager_DeleteUserPermConfirmMultiple",
          `<strong>${this.affectedUsersCount}</strong>`,
          `<strong>${this.getRoleDisplay(this.roleToChangeTo)}</strong>`,
          `<strong>${CoreHome.Matomo.helper.htmlEntities(((_a2 = this.permissionsForSite) == null ? void 0 : _a2.name) || "")}</strong>`
        );
      },
      bulkActionAccessLevels() {
        return this.accessLevels.filter(
          (e) => e.key !== "noaccess" && e.key !== "superuser"
        );
      },
      anonymousAccessLevels() {
        return this.accessLevels.filter(
          (e) => e.key === "noaccess" || e.key === "view"
        );
      },
      onlyRoleAccessLevels() {
        return this.accessLevels.filter(
          (e) => e.type === "role"
        );
      }
    }
  });
  const _hoisted_1$8 = { class: "userListFilters row" };
  const _hoisted_2$7 = { class: "col s12 m12 l8" };
  const _hoisted_3$5 = { class: "input-field col s12 m3 l3" };
  const _hoisted_4$4 = {
    id: "user-list-bulk-actions",
    class: "dropdown-content"
  };
  const _hoisted_5$4 = {
    class: "dropdown-trigger",
    "data-target": "bulk-set-access"
  };
  const _hoisted_6$4 = {
    id: "bulk-set-access",
    class: "dropdown-content"
  };
  const _hoisted_7$4 = ["onClick"];
  const _hoisted_8$3 = { key: 0 };
  const _hoisted_9$3 = { class: "input-field col s12 m3 l3" };
  const _hoisted_10$3 = { class: "permissions-for-selector" };
  const _hoisted_11$3 = { class: "input-field col s12 m3 l3" };
  const _hoisted_12$2 = { class: "input-field col s12 m3 l3" };
  const _hoisted_13$2 = {
    key: 0,
    class: "input-field col s12 m12 l4 users-list-pagination-container"
  };
  const _hoisted_14$2 = { class: "usersListPagination" };
  const _hoisted_15$2 = { class: "pointer" };
  const _hoisted_16$2 = { class: "counter" };
  const _hoisted_17$1 = { class: "pointer" };
  const _hoisted_18$1 = {
    key: 0,
    class: "roles-help-notification"
  };
  const _hoisted_19$1 = ["innerHTML"];
  const _hoisted_20$1 = { class: "select-cell" };
  const _hoisted_21$1 = { class: "checkbox-container" };
  const _hoisted_22$1 = { class: "first" };
  const _hoisted_23 = { class: "role_header" };
  const _hoisted_24 = { style: { "margin-right": "3.5px" } };
  const _hoisted_25 = { key: 0 };
  const _hoisted_26 = ["title"];
  const _hoisted_27 = { key: 2 };
  const _hoisted_28 = { class: "actions-cell-header" };
  const _hoisted_29 = {
    key: 0,
    class: "select-all-row"
  };
  const _hoisted_30 = { colspan: "8" };
  const _hoisted_31 = { key: 0 };
  const _hoisted_32 = ["innerHTML"];
  const _hoisted_33 = ["innerHTML"];
  const _hoisted_34 = { key: 1 };
  const _hoisted_35 = ["innerHTML"];
  const _hoisted_36 = ["innerHTML"];
  const _hoisted_37 = ["id"];
  const _hoisted_38 = { class: "select-cell" };
  const _hoisted_39 = { class: "checkbox-container" };
  const _hoisted_40 = ["id", "onUpdate:modelValue"];
  const _hoisted_41 = { id: "userLogin" };
  const _hoisted_42 = { class: "access-cell" };
  const _hoisted_43 = {
    key: 0,
    id: "email"
  };
  const _hoisted_44 = {
    key: 1,
    id: "twofa"
  };
  const _hoisted_45 = {
    key: 0,
    class: "icon-ok"
  };
  const _hoisted_46 = {
    key: 1,
    class: "icon-close"
  };
  const _hoisted_47 = {
    key: 2,
    id: "last_seen"
  };
  const _hoisted_48 = { id: "status" };
  const _hoisted_49 = ["title"];
  const _hoisted_50 = { class: "center actions-cell" };
  const _hoisted_51 = ["onClick"];
  const _hoisted_52 = ["onClick"];
  const _hoisted_53 = ["title", "onClick"];
  const _hoisted_54 = ["onClick"];
  const _hoisted_55 = ["innerHTML"];
  const _hoisted_56 = ["innerHTML"];
  const _hoisted_57 = ["innerHTML"];
  const _hoisted_58 = ["innerHTML"];
  const _hoisted_59 = { key: 2 };
  const _hoisted_60 = ["innerHTML"];
  const _hoisted_61 = { key: 3 };
  const _hoisted_62 = ["innerHTML"];
  const _hoisted_63 = ["innerHTML"];
  const _hoisted_64 = {
    class: "change-user-role-confirm-modal modal",
    ref: "changeUserRoleConfirmModal"
  };
  const _hoisted_65 = { class: "modal-content" };
  const _hoisted_66 = ["innerHTML"];
  const _hoisted_67 = ["innerHTML"];
  const _hoisted_68 = { class: "modal-footer" };
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_Notification = vue.resolveComponent("Notification");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _directive_dropdown_menu = vue.resolveDirective("dropdown-menu");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["pagedUsersList", { loading: _ctx.isLoadingUsers }])
    }, [
      vue.createElementVNode("div", _hoisted_1$8, [
        vue.createElementVNode("div", _hoisted_2$7, [
          vue.createElementVNode("div", _hoisted_3$5, [
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", {
              class: vue.normalizeClass(["dropdown-trigger btn bulk-actions", { disabled: _ctx.isBulkActionsDisabled }]),
              href: "",
              "data-target": "user-list-bulk-actions"
            }, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_BulkActions")), 1)
            ], 2)), [
              [_directive_dropdown_menu]
            ]),
            vue.createElementVNode("ul", _hoisted_4$4, [
              vue.createElementVNode("li", null, [
                vue.withDirectives((vue.openBlock(), vue.createElementBlock("a", _hoisted_5$4, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_SetPermission")), 1)
                ])), [
                  [_directive_dropdown_menu]
                ]),
                vue.createElementVNode("ul", _hoisted_6$4, [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.bulkActionAccessLevels, (access) => {
                    return vue.openBlock(), vue.createElementBlock("li", {
                      key: access.key
                    }, [
                      vue.createElementVNode("a", {
                        href: "",
                        onClick: vue.withModifiers(($event) => {
                          _ctx.userToChange = null;
                          _ctx.roleToChangeTo = access.key;
                          _ctx.showAccessChangeConfirm();
                        }, ["prevent"])
                      }, vue.toDisplayString(access.value), 9, _hoisted_7$4)
                    ]);
                  }), 128))
                ])
              ]),
              vue.createElementVNode("li", null, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => {
                    _ctx.userToChange = null;
                    _ctx.roleToChangeTo = "noaccess";
                    _ctx.showAccessChangeConfirm();
                  }, ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_RemovePermissions")), 1)
              ]),
              _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_8$3, [
                vue.createElementVNode("a", {
                  href: "",
                  onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => _ctx.showDeleteConfirm(), ["prevent"]))
                }, vue.toDisplayString(_ctx.translate("UsersManager_DeleteUsers")), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_9$3, [
            vue.createElementVNode("div", _hoisted_10$3, [
              vue.createVNode(_component_Field, {
                "model-value": _ctx.userTextFilter,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.onUserTextFilterChange($event)),
                name: "user-text-filter",
                uicontrol: "text",
                "full-width": true,
                placeholder: _ctx.translate("UsersManager_UserSearch")
              }, null, 8, ["model-value", "placeholder"])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_11$3, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                "model-value": _ctx.accessLevelFilter,
                "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
                  var _a2;
                  _ctx.accessLevelFilter = $event;
                  _ctx.changeSearch({
                    filter_access: (_a2 = _ctx.accessLevelFilter) != null ? _a2 : void 0,
                    offset: 0
                  });
                }),
                name: "access-level-filter",
                uicontrol: "select",
                options: _ctx.filterAccessLevels,
                "full-width": true,
                placeholder: _ctx.translate("UsersManager_FilterByAccess")
              }, null, 8, ["model-value", "options", "placeholder"])
            ])
          ]),
          vue.createElementVNode("div", _hoisted_12$2, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                "model-value": _ctx.statusLevelFilter,
                "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
                  var _a2;
                  _ctx.statusLevelFilter = $event;
                  _ctx.changeSearch({
                    filter_status: (_a2 = _ctx.statusLevelFilter) != null ? _a2 : void 0,
                    offset: 0
                  });
                }),
                name: "status-level-filter",
                uicontrol: "select",
                options: _ctx.filterStatusLevels,
                "full-width": true,
                placeholder: _ctx.translate("UsersManager_FilterByStatus")
              }, null, 8, ["model-value", "options", "placeholder"])
            ])
          ])
        ]),
        _ctx.totalEntries > _ctx.searchParams.limit ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_13$2, [
          vue.createElementVNode("div", _hoisted_14$2, [
            vue.createElementVNode("a", {
              class: vue.normalizeClass(["btn prev", { disabled: _ctx.searchParams.offset <= 0 }]),
              onClick: _cache[5] || (_cache[5] = vue.withModifiers(($event) => _ctx.gotoPreviousPage(), ["prevent"]))
            }, [
              vue.createElementVNode("span", _hoisted_15$2, "« " + vue.toDisplayString(_ctx.translate("General_Previous")), 1)
            ], 2),
            vue.createElementVNode("div", _hoisted_16$2, [
              vue.createElementVNode("span", {
                class: vue.normalizeClass({ visibility: _ctx.isLoadingUsers ? "hidden" : "visible" })
              }, vue.toDisplayString(_ctx.translate(
                "General_Pagination",
                _ctx.paginationLowerBound,
                _ctx.paginationUpperBound,
                _ctx.totalEntries || 0
              )), 3),
              vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoadingUsers }, null, 8, ["loading"])
            ]),
            vue.createElementVNode("a", {
              class: vue.normalizeClass(["btn next", { disabled: _ctx.searchParams.offset + _ctx.searchParams.limit >= (_ctx.totalEntries || 0) }]),
              onClick: _cache[6] || (_cache[6] = vue.withModifiers(($event) => _ctx.gotoNextPage(), ["prevent"]))
            }, [
              vue.createElementVNode("span", _hoisted_17$1, vue.toDisplayString(_ctx.translate("General_Next")) + " »", 1)
            ], 2)
          ])
        ])) : vue.createCommentVNode("", true)
      ]),
      _ctx.isRoleHelpToggled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_18$1, [
        vue.createVNode(_component_Notification, {
          context: "info",
          type: "persistent",
          noclear: true
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.rolesHelpText)
            }, null, 8, _hoisted_19$1)
          ]),
          _: 1
        })
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_ContentBlock, null, {
        default: vue.withCtx(() => [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", {
            id: "manageUsersTable",
            class: vue.normalizeClass({ loading: _ctx.isLoadingUsers })
          }, [
            vue.createElementVNode("thead", null, [
              vue.createElementVNode("tr", null, [
                vue.createElementVNode("th", _hoisted_20$1, [
                  vue.createElementVNode("span", _hoisted_21$1, [
                    vue.createElementVNode("label", null, [
                      vue.withDirectives(vue.createElementVNode("input", {
                        type: "checkbox",
                        id: "paged_users_select_all",
                        checked: "",
                        "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.isAllCheckboxSelected = $event),
                        onChange: _cache[8] || (_cache[8] = ($event) => _ctx.onAllCheckboxChange())
                      }, null, 544), [
                        [vue.vModelCheckbox, _ctx.isAllCheckboxSelected]
                      ]),
                      _cache[19] || (_cache[19] = vue.createElementVNode("span", null, null, -1))
                    ])
                  ])
                ]),
                vue.createElementVNode("th", _hoisted_22$1, vue.toDisplayString(_ctx.translate("UsersManager_Username")), 1),
                vue.createElementVNode("th", _hoisted_23, [
                  vue.createElementVNode("span", _hoisted_24, vue.toDisplayString(_ctx.translate("UsersManager_RoleFor")), 1),
                  vue.createElementVNode("a", {
                    href: "",
                    class: vue.normalizeClass(["helpIcon", { sticky: _ctx.isRoleHelpToggled }]),
                    onClick: _cache[9] || (_cache[9] = vue.withModifiers(($event) => _ctx.isRoleHelpToggled = !_ctx.isRoleHelpToggled, ["prevent"]))
                  }, [..._cache[20] || (_cache[20] = [
                    vue.createElementVNode("span", { class: "icon-help" }, null, -1)
                  ])], 2),
                  vue.createElementVNode("div", null, [
                    vue.createVNode(_component_Field, {
                      class: "permissions-for-selector",
                      "model-value": _ctx.permissionsForSite,
                      "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => {
                        _ctx.onPermissionsForUpdate($event);
                      }),
                      uicontrol: "site",
                      "ui-control-attributes": {
                        onlySitesWithAdminAccess: _ctx.currentUserRole !== "superuser"
                      }
                    }, null, 8, ["model-value", "ui-control-attributes"])
                  ])
                ]),
                _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_25, vue.toDisplayString(_ctx.translate("UsersManager_Email")), 1)) : vue.createCommentVNode("", true),
                _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("th", {
                  key: 1,
                  title: _ctx.translate("UsersManager_UsesTwoFactorAuthentication")
                }, vue.toDisplayString(_ctx.translate("UsersManager_2FA")), 9, _hoisted_26)) : vue.createCommentVNode("", true),
                _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_27, vue.toDisplayString(_ctx.translate("UsersManager_LastSeen")), 1)) : vue.createCommentVNode("", true),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("UsersManager_Status")), 1),
                vue.createElementVNode("th", _hoisted_28, [
                  vue.createElementVNode("div", null, vue.toDisplayString(_ctx.translate("General_Actions")), 1)
                ])
              ])
            ]),
            vue.createElementVNode("tbody", null, [
              _ctx.isAllCheckboxSelected && _ctx.users.length && _ctx.users.length < (_ctx.totalEntries || 0) ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_29, [
                vue.createElementVNode("td", _hoisted_30, [
                  !_ctx.areAllResultsSelected ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_31, [
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(_ctx.translate(
                        "UsersManager_TheDisplayedUsersAreSelected",
                        `<strong>${_ctx.users.length}</strong>`
                      )),
                      style: { "margin-right": "3.5px" }
                    }, null, 8, _hoisted_32),
                    vue.createElementVNode("a", {
                      class: "toggle-select-all-in-search",
                      href: "#",
                      onClick: _cache[11] || (_cache[11] = vue.withModifiers(($event) => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
                      innerHTML: _ctx.$sanitize(_ctx.translate(
                        "UsersManager_ClickToSelectAll",
                        `<strong>${_ctx.totalEntries}</strong>`
                      ))
                    }, null, 8, _hoisted_33)
                  ])) : vue.createCommentVNode("", true),
                  _ctx.areAllResultsSelected ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_34, [
                    vue.createElementVNode("span", {
                      innerHTML: _ctx.$sanitize(_ctx.translate(
                        "UsersManager_AllUsersAreSelected",
                        `<strong>${_ctx.totalEntries}</strong>`
                      )),
                      style: { "margin-right": "3.5px" }
                    }, null, 8, _hoisted_35),
                    vue.createElementVNode("a", {
                      class: "toggle-select-all-in-search",
                      href: "#",
                      onClick: _cache[12] || (_cache[12] = vue.withModifiers(($event) => _ctx.areAllResultsSelected = !_ctx.areAllResultsSelected, ["prevent"])),
                      innerHTML: _ctx.$sanitize(_ctx.translate(
                        "UsersManager_ClickToSelectDisplayedUsers",
                        `<strong>${_ctx.users.length}</strong>`
                      ))
                    }, null, 8, _hoisted_36)
                  ])) : vue.createCommentVNode("", true)
                ])
              ])) : vue.createCommentVNode("", true),
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.users, (user, index) => {
                var _a2;
                return vue.openBlock(), vue.createElementBlock("tr", {
                  id: `row${index}`,
                  key: user.login
                }, [
                  vue.createElementVNode("td", _hoisted_38, [
                    vue.createElementVNode("span", _hoisted_39, [
                      vue.createElementVNode("label", null, [
                        vue.withDirectives(vue.createElementVNode("input", {
                          type: "checkbox",
                          id: `paged_users_select_row${index}`,
                          "onUpdate:modelValue": ($event) => _ctx.selectedRows[index] = $event,
                          onClick: _cache[13] || (_cache[13] = ($event) => _ctx.onRowSelected())
                        }, null, 8, _hoisted_40), [
                          [vue.vModelCheckbox, _ctx.selectedRows[index]]
                        ]),
                        _cache[21] || (_cache[21] = vue.createElementVNode("span", null, null, -1))
                      ])
                    ])
                  ]),
                  vue.createElementVNode("td", _hoisted_41, vue.toDisplayString(user.login), 1),
                  vue.createElementVNode("td", _hoisted_42, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        "model-value": user.role,
                        "onUpdate:modelValue": ($event) => {
                          _ctx.userToChange = user;
                          _ctx.roleToChangeTo = $event.value;
                          _ctx.showAccessChangeConfirm();
                          $event.abort();
                        },
                        "model-modifiers": { abortable: true },
                        disabled: user.role === "superuser",
                        uicontrol: "select",
                        options: user.login === "anonymous" ? _ctx.anonymousAccessLevels : user.role === "noaccess" ? _ctx.onlyRoleAccessLevels : _ctx.accessLevels
                      }, null, 8, ["model-value", "onUpdate:modelValue", "disabled", "options"])
                    ])
                  ]),
                  _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_43, vue.toDisplayString(user.email), 1)) : vue.createCommentVNode("", true),
                  _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_44, [
                    user.uses_2fa ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_45)) : vue.createCommentVNode("", true),
                    !user.uses_2fa ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_46)) : vue.createCommentVNode("", true)
                  ])) : vue.createCommentVNode("", true),
                  _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_47, vue.toDisplayString(user.last_seen_ago ? _ctx.translate("UsersManager_XAgo", user.last_seen_ago) : "-"), 1)) : vue.createCommentVNode("", true),
                  vue.createElementVNode("td", _hoisted_48, [
                    vue.createElementVNode("span", {
                      class: vue.normalizeClass(Number.isInteger(user.invite_status) ? "pending" : user.invite_status),
                      title: user.invite_status === "expired" ? _ctx.translate("UsersManager_ExpiredInviteAutomaticallyRemoved", "3") : ""
                    }, vue.toDisplayString(_ctx.getInviteStatus((_a2 = user.invite_status) != null ? _a2 : "")), 11, _hoisted_49)
                  ]),
                  vue.createElementVNode("td", _hoisted_50, [
                    (_ctx.currentUserRole === "superuser" || _ctx.currentUserRole === "admin" && user.invited_by === _ctx.currentUserLogin) && user.invite_status !== "active" ? (vue.openBlock(), vue.createElementBlock("button", {
                      key: 0,
                      class: "resend table-action",
                      title: "Resend/Copy Invite Link",
                      onClick: ($event) => {
                        _ctx.userToChange = user;
                        _ctx.resendRequestedUser();
                      }
                    }, [..._cache[22] || (_cache[22] = [
                      vue.createElementVNode("span", { class: "icon-email" }, null, -1)
                    ])], 8, _hoisted_51)) : vue.createCommentVNode("", true),
                    user.login !== "anonymous" ? (vue.openBlock(), vue.createElementBlock("button", {
                      key: 1,
                      class: "edituser table-action",
                      title: "Edit",
                      onClick: ($event) => _ctx.$emit("editUser", { user })
                    }, [..._cache[23] || (_cache[23] = [
                      vue.createElementVNode("span", { class: "icon-edit" }, null, -1)
                    ])], 8, _hoisted_52)) : vue.createCommentVNode("", true),
                    _ctx.currentUserRole === "superuser" && user.login !== "anonymous" && user.invite_status === "active" ? (vue.openBlock(), vue.createElementBlock("button", {
                      key: 2,
                      class: "signoutuser table-action",
                      title: _ctx.translate("UsersManager_SignOutUser"),
                      onClick: ($event) => {
                        _ctx.userToChange = user;
                        _ctx.showSignOutConfirm();
                      }
                    }, [..._cache[24] || (_cache[24] = [
                      vue.createElementVNode("span", { class: "icon-sign-out" }, null, -1)
                    ])], 8, _hoisted_53)) : vue.createCommentVNode("", true),
                    (_ctx.currentUserRole === "superuser" || _ctx.currentUserRole === "admin" && user.invited_by === _ctx.currentUserLogin && user.invite_status !== "active") && user.login !== "anonymous" ? (vue.openBlock(), vue.createElementBlock("button", {
                      key: 3,
                      class: "deleteuser table-action",
                      title: "Delete",
                      onClick: ($event) => {
                        _ctx.userToChange = user;
                        _ctx.showDeleteConfirm();
                      }
                    }, [..._cache[25] || (_cache[25] = [
                      vue.createElementVNode("span", { class: "icon-delete" }, null, -1)
                    ])], 8, _hoisted_54)) : vue.createCommentVNode("", true)
                  ])
                ], 8, _hoisted_37);
              }), 128))
            ])
          ], 2)), [
            [_directive_content_table]
          ])
        ]),
        _: 1
      }),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForUserRemoval,
        "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => _ctx.showPasswordConfirmationForUserRemoval = $event),
        onConfirmed: _ctx.deleteRequestedUsers,
        onAborted: _ctx.resetUserAndRoleToChange
      }, {
        default: vue.withCtx(() => [
          _ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h2", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.translate(
              "UsersManager_DeleteUserConfirmSingle",
              `<strong>${_ctx.userToChange.login}</strong>`
            ))
          }, null, 8, _hoisted_55)) : vue.createCommentVNode("", true),
          !_ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h2", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.translate(
              "UsersManager_DeleteUserConfirmMultiple",
              `<strong>${_ctx.affectedUsersCount}</strong>`
            ))
          }, null, 8, _hoisted_56)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "onAborted"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForAccessChange,
        "onUpdate:modelValue": _cache[15] || (_cache[15] = ($event) => _ctx.showPasswordConfirmationForAccessChange = $event),
        onConfirmed: _ctx.changeUserRole,
        onAborted: _ctx.resetUserAndRoleToChange
      }, {
        default: vue.withCtx(() => [
          _ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmSingleText)
          }, null, 8, _hoisted_57)) : vue.createCommentVNode("", true),
          !_ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmMultipleText)
          }, null, 8, _hoisted_58)) : vue.createCommentVNode("", true),
          _ctx.changeAffectsAnonymous && _ctx.roleToChangeTo === "view" ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_59, [
            vue.createElementVNode("em", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Note")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "UsersManager_AnonymousUserRoleChangeWarning",
                  "anonymous",
                  String(_ctx.getRoleDisplay(_ctx.roleToChangeTo))
                ))
              }, null, 8, _hoisted_60)
            ])
          ])) : vue.createCommentVNode("", true),
          _ctx.roleToChangeTo === "admin" ? (vue.openBlock(), vue.createElementBlock("h3", _hoisted_61, [
            vue.createElementVNode("em", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Note")) + ": ", 1),
              vue.createElementVNode("span", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "UsersManager_AdminUserRoleChangeWarning",
                  String(_ctx.getRoleDisplay(_ctx.roleToChangeTo))
                ))
              }, null, 8, _hoisted_62)
            ])
          ])) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "onAborted"]),
      vue.createVNode(_component_PasswordConfirmation, {
        modelValue: _ctx.showPasswordConfirmationForUserSignOut,
        "onUpdate:modelValue": _cache[16] || (_cache[16] = ($event) => _ctx.showPasswordConfirmationForUserSignOut = $event),
        onConfirmed: _ctx.signOutRequestedUser,
        onAborted: _ctx.resetUserAndRoleToChange
      }, {
        default: vue.withCtx(() => [
          _ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.translate(
              "UsersManager_SignOutUserConfirm",
              `<strong>${_ctx.userToChange.login}</strong>`
            ))
          }, null, 8, _hoisted_63)) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["modelValue", "onConfirmed", "onAborted"]),
      vue.createElementVNode("div", _hoisted_64, [
        vue.createElementVNode("div", _hoisted_65, [
          _ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("h3", {
            key: 0,
            innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmSingleText)
          }, null, 8, _hoisted_66)) : vue.createCommentVNode("", true),
          !_ctx.userToChange ? (vue.openBlock(), vue.createElementBlock("p", {
            key: 1,
            innerHTML: _ctx.$sanitize(_ctx.deleteUserPermConfirmMultipleText)
          }, null, 8, _hoisted_67)) : vue.createCommentVNode("", true)
        ]),
        vue.createElementVNode("div", _hoisted_68, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[17] || (_cache[17] = vue.withModifiers(($event) => _ctx.changeUserRole(), ["prevent"])),
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("General_Yes")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[18] || (_cache[18] = vue.withModifiers(($event) => _ctx.resetUserAndRoleToChange(), ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_No")), 1)
        ])
      ], 512)
    ], 2);
  }
  const PagedUsersList = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const NUM_USERS_PER_PAGE = 20;
  const { $: $$2 } = window;
  const _sfc_main$7 = vue.defineComponent({
    props: {
      currentUserRole: {
        type: String,
        required: true
      },
      initialSiteName: {
        type: String,
        required: true
      },
      initialSiteId: {
        type: String,
        required: true
      },
      accessLevels: {
        type: Array,
        required: true
      },
      filterAccessLevels: {
        type: Array,
        required: true
      },
      filterStatusLevels: {
        type: Array,
        required: true
      },
      activatedPlugins: {
        type: Array,
        required: true
      },
      inviteTokenExpiryDays: {
        type: String,
        required: true
      },
      passwordStrengthValidationRules: {
        type: Array,
        default: () => []
      },
      inviteComponent: {
        type: Object,
        required: false,
        default: null
      },
      resendInviteComponent: {
        type: Object,
        required: false,
        default: null
      }
    },
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      PagedUsersList,
      UserEditForm,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro,
      Tooltips: CoreHome.Tooltips
    },
    data() {
      return {
        isEditing: !!CoreHome.MatomoUrl.urlParsed.value.showadduser,
        isInviting: false,
        isCurrentUserSuperUser: true,
        users: [],
        totalEntries: null,
        searchParams: {
          offset: 0,
          limit: NUM_USERS_PER_PAGE,
          filter_search: "",
          filter_access: "",
          filter_status: "",
          idSite: this.initialSiteId
        },
        isLoadingUsers: false,
        userBeingEdited: null,
        addNewUserLoginEmail: "",
        loading: false,
        triggerResendInviteForUser: null
      };
    },
    created() {
      this.fetchUsers();
    },
    watch: {
      limit() {
        this.fetchUsers();
      }
    },
    methods: {
      onInvite(user) {
        this.isInviting = false;
        this.userBeingEdited = user;
        this.isEditing = true;
        this.fetchUsers();
      },
      onEditUser(user) {
        CoreHome.Matomo.helper.lazyScrollToContent();
        this.isEditing = true;
        this.userBeingEdited = user;
      },
      onDoneEditing(isUserModified) {
        this.isEditing = false;
        if (isUserModified) {
          this.fetchUsers();
        }
      },
      showAddExistingUserModal() {
        $$2(this.$refs.addExistingUserModal).modal({ dismissible: false }).modal("open");
      },
      onChangeUserRole(users, role, password) {
        this.isLoadingUsers = true;
        Promise.resolve().then(() => {
          if (users === "all") {
            return this.getAllUsersInSearch();
          }
          return users;
        }).then((usersResolved) => usersResolved.filter((u) => u.role !== "superuser").map((u) => u.login)).then((userLogins) => {
          const type = this.accessLevels.filter((a) => a.key === role).map((a) => a.type);
          let requests;
          if (type.length && type[0] === "capability") {
            requests = userLogins.map((login) => ({
              method: "UsersManager.addCapabilities",
              userLogin: login,
              capabilities: role,
              idSites: this.searchParams.idSite,
              passwordConfirmation: password
            }));
          } else {
            requests = userLogins.map((login) => ({
              method: "UsersManager.setUserAccess",
              userLogin: login,
              access: role,
              idSites: this.searchParams.idSite,
              passwordConfirmation: password
            }));
          }
          return CoreHome.AjaxHelper.fetch(requests, { createErrorNotification: true });
        }).catch(() => {
        }).finally(
          () => this.fetchUsers()
        );
      },
      getAllUsersInSearch() {
        return CoreHome.AjaxHelper.fetch({
          method: "UsersManager.getUsersPlusRole",
          filter_search: this.searchParams.filter_search,
          filter_access: this.searchParams.filter_access,
          filter_status: this.searchParams.filter_status,
          idSite: this.searchParams.idSite,
          filter_limit: "-1"
        });
      },
      onDeleteUser(users, password) {
        this.isLoadingUsers = true;
        Promise.resolve().then(() => {
          if (users === "all") {
            return this.getAllUsersInSearch();
          }
          return users;
        }).then((usersResolved) => usersResolved.map((u) => u.login)).then((userLogins) => {
          const requests = userLogins.map((login) => ({
            method: "UsersManager.deleteUser",
            userLogin: login,
            passwordConfirmation: password
          }));
          return CoreHome.AjaxHelper.fetch(requests, { createErrorNotification: true });
        }).then(() => {
          CoreHome.NotificationsStore.scrollToNotification(CoreHome.NotificationsStore.show({
            id: "removeUserSuccess",
            message: CoreHome.translate("UsersManager_DeleteSuccess"),
            context: "success",
            type: "toast"
          }));
          this.fetchUsers();
        }, () => {
          if (users !== "all" && users.length > 1) {
            CoreHome.NotificationsStore.show({
              id: "removeUserSuccess",
              message: CoreHome.translate("UsersManager_DeleteNotSuccessful"),
              context: "warning",
              type: "toast"
            });
          }
          this.fetchUsers();
        });
      },
      fetchUsers() {
        this.isLoadingUsers = true;
        return CoreHome.AjaxHelper.fetch(
          __spreadProps(__spreadValues({}, this.searchParams), {
            method: "UsersManager.getUsersPlusRole"
          }),
          { returnResponseObject: true }
        ).then((helper) => {
          const result = helper.getRequestHandle();
          this.totalEntries = parseInt(
            result.getResponseHeader("x-matomo-total-results") || "0",
            10
          );
          this.users = result.responseJSON;
          this.isLoadingUsers = false;
        }).catch(() => {
          this.isLoadingUsers = false;
        });
      },
      addExistingUser() {
        this.isLoadingUsers = true;
        return CoreHome.AjaxHelper.fetch({
          method: "UsersManager.userExists",
          userLogin: this.addNewUserLoginEmail
        }).then((response) => {
          if (response && response.value) {
            return this.addNewUserLoginEmail;
          }
          return CoreHome.AjaxHelper.fetch({
            method: "UsersManager.getUserLoginFromUserEmail",
            userEmail: this.addNewUserLoginEmail
          }).then((r) => r.value);
        }).then((login) => CoreHome.AjaxHelper.post(
          {
            method: "UsersManager.setUserAccess"
          },
          {
            userLogin: login,
            access: "view",
            idSites: this.searchParams.idSite
          }
        )).then(
          () => this.fetchUsers()
        ).catch(() => {
          this.isLoadingUsers = false;
        });
      },
      onSignOutUser(userLogin, password) {
        CoreHome.AjaxHelper.post({
          method: "UsersManager.logoutUser",
          userLogin
        }, {
          passwordConfirmation: password
        }, {
          createErrorNotification: true
        }).then(() => {
          CoreHome.NotificationsStore.scrollToNotification(CoreHome.NotificationsStore.show({
            id: "signOutUserSuccess",
            message: CoreHome.translate("UsersManager_SignOutUserSuccess", userLogin),
            context: "success",
            type: "toast"
          }));
        });
      },
      onAddNewUser() {
        const parameters = { isAllowed: true };
        CoreHome.Matomo.postEvent("UsersManager.initAddUser", parameters);
        if (parameters && !parameters.isAllowed) {
          return;
        }
        this.isInviting = true;
        this.userBeingEdited = null;
      }
    },
    computed: {
      usedInviteComponent() {
        if (this.inviteComponent) {
          const [plugin, component] = this.inviteComponent.split(".");
          return CoreHome.useExternalPluginComponent(plugin, component);
        }
        return CoreHome.useExternalPluginComponent("UsersManager", "UserInvite");
      },
      usedResendInviteComponent() {
        if (this.resendInviteComponent) {
          const [plugin, component] = this.resendInviteComponent.split(".");
          return CoreHome.useExternalPluginComponent(plugin, component);
        }
        return CoreHome.useExternalPluginComponent("UsersManager", "ResendInviteModal");
      }
    }
  });
  const _hoisted_1$7 = { class: "usersManager" };
  const _hoisted_2$6 = { key: 0 };
  const _hoisted_3$4 = { key: 1 };
  const _hoisted_4$3 = { class: "row add-user-container" };
  const _hoisted_5$3 = { class: "col s12" };
  const _hoisted_6$3 = {
    class: "input-field",
    style: { "margin-right": "3.5px" }
  };
  const _hoisted_7$3 = {
    key: 0,
    class: "input-field"
  };
  const _hoisted_8$2 = { key: 0 };
  const _hoisted_9$2 = {
    class: "add-existing-user-modal modal",
    ref: "addExistingUserModal"
  };
  const _hoisted_10$2 = { class: "modal-content" };
  const _hoisted_11$2 = { class: "modal-footer" };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    var _a2;
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_PagedUsersList = vue.resolveComponent("PagedUsersList");
    const _component_UserEditForm = vue.resolveComponent("UserEditForm");
    const _component_Field = vue.resolveComponent("Field");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createElementVNode("h2", null, [
            vue.createVNode(_component_EnrichedHeadline, {
              "help-url": _ctx.externalRawLink("https://matomo.org/docs/manage-users/"),
              "feature-name": "Users Management"
            }, {
              default: vue.withCtx(() => [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_ManageUsers")), 1)
              ]),
              _: 1
            }, 8, ["help-url"])
          ]),
          _ctx.currentUserRole === "superuser" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_2$6, vue.toDisplayString(_ctx.translate("UsersManager_ManageUsersDesc")), 1)) : vue.createCommentVNode("", true),
          _ctx.currentUserRole === "admin" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_3$4, vue.toDisplayString(_ctx.translate("UsersManager_ManageUsersAdminDesc")), 1)) : vue.createCommentVNode("", true),
          vue.createElementVNode("div", _hoisted_4$3, [
            vue.createElementVNode("div", _hoisted_5$3, [
              vue.createElementVNode("div", _hoisted_6$3, [
                vue.createElementVNode("a", {
                  class: "btn add-new-user",
                  onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onAddNewUser())
                }, vue.toDisplayString(_ctx.translate("UsersManager_InviteNewUser")), 1)
              ]),
              _ctx.currentUserRole !== "superuser" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$3, [
                vue.createElementVNode("a", {
                  class: "btn add-existing-user",
                  onClick: _cache[1] || (_cache[1] = ($event) => _ctx.showAddExistingUserModal())
                }, vue.toDisplayString(_ctx.translate("UsersManager_AddExistingUser")), 1)
              ])) : vue.createCommentVNode("", true)
            ])
          ]),
          vue.createVNode(_component_PagedUsersList, {
            onEditUser: _cache[2] || (_cache[2] = ($event) => _ctx.onEditUser($event.user)),
            onChangeUserRole: _cache[3] || (_cache[3] = ($event) => _ctx.onChangeUserRole($event.users, $event.role, $event.password)),
            onDeleteUser: _cache[4] || (_cache[4] = ($event) => _ctx.onDeleteUser($event.users, $event.password)),
            onSearchChange: _cache[5] || (_cache[5] = ($event) => {
              _ctx.searchParams = $event.params;
              _ctx.fetchUsers();
            }),
            onResendInvite: _cache[6] || (_cache[6] = ($event) => _ctx.triggerResendInviteForUser = $event.user),
            onSignOutUser: _cache[7] || (_cache[7] = ($event) => _ctx.onSignOutUser($event.userLogin, $event.password)),
            "initial-site-id": _ctx.initialSiteId,
            "initial-site-name": _ctx.initialSiteName,
            "is-loading-users": _ctx.isLoadingUsers,
            "current-user-role": _ctx.currentUserRole,
            "access-levels": _ctx.accessLevels,
            "filter-access-levels": _ctx.filterAccessLevels,
            "filter-status-levels": _ctx.filterStatusLevels,
            "search-params": _ctx.searchParams,
            users: _ctx.users,
            "total-entries": (_a2 = _ctx.totalEntries) != null ? _a2 : void 0
          }, null, 8, ["initial-site-id", "initial-site-name", "is-loading-users", "current-user-role", "access-levels", "filter-access-levels", "filter-status-levels", "search-params", "users", "total-entries"])
        ])), [
          [_directive_content_intro]
        ])
      ], 512), [
        [vue.vShow, !_ctx.isEditing && !_ctx.isInviting]
      ]),
      vue.withDirectives(vue.createElementVNode("div", null, [
        (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.usedInviteComponent), {
          "invite-token-expiry-days": _ctx.inviteTokenExpiryDays,
          "initial-site-id": _ctx.initialSiteId,
          "initial-site-name": _ctx.initialSiteName,
          onAborted: _cache[8] || (_cache[8] = ($event) => _ctx.isInviting = false),
          onInvited: _cache[9] || (_cache[9] = ($event) => _ctx.onInvite($event.user))
        }, null, 40, ["invite-token-expiry-days", "initial-site-id", "initial-site-name"]))
      ], 512), [
        [vue.vShow, _ctx.isInviting]
      ]),
      (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.usedResendInviteComponent), {
        user: _ctx.triggerResendInviteForUser,
        "invite-token-expiry-days": _ctx.inviteTokenExpiryDays,
        onClose: _cache[10] || (_cache[10] = ($event) => _ctx.triggerResendInviteForUser = null)
      }, null, 40, ["user", "invite-token-expiry-days"])),
      _ctx.isEditing ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$2, [
        vue.createVNode(_component_UserEditForm, {
          onDone: _cache[11] || (_cache[11] = ($event) => _ctx.onDoneEditing($event.isUserModified)),
          user: _ctx.userBeingEdited || void 0,
          "current-user-role": _ctx.currentUserRole,
          "access-levels": _ctx.accessLevels,
          "filter-access-levels": _ctx.filterAccessLevels,
          "activated-plugins": _ctx.activatedPlugins,
          "password-strength-validation-rules": _ctx.passwordStrengthValidationRules,
          onResendInvite: _cache[12] || (_cache[12] = ($event) => _ctx.triggerResendInviteForUser = $event.user),
          onUpdated: _cache[13] || (_cache[13] = ($event) => _ctx.userBeingEdited = $event.user)
        }, null, 8, ["user", "current-user-role", "access-levels", "filter-access-levels", "activated-plugins", "password-strength-validation-rules"])
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_9$2, [
        vue.createElementVNode("div", _hoisted_10$2, [
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("UsersManager_AddExistingUser")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_EnterUsernameOrEmail")) + ":", 1),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              modelValue: _ctx.addNewUserLoginEmail,
              "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => _ctx.addNewUserLoginEmail = $event),
              name: "add-existing-user-email",
              uicontrol: "text"
            }, null, 8, ["modelValue"])
          ])
        ]),
        vue.createElementVNode("div", _hoisted_11$2, [
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close btn",
            onClick: _cache[15] || (_cache[15] = vue.withModifiers(($event) => _ctx.addExistingUser(), ["prevent"])),
            style: { "margin-right": "3.5px" }
          }, vue.toDisplayString(_ctx.translate("General_Add")), 1),
          vue.createElementVNode("a", {
            href: "",
            class: "modal-action modal-close modal-no",
            onClick: _cache[16] || (_cache[16] = vue.withModifiers(($event) => _ctx.addNewUserLoginEmail = null, ["prevent"]))
          }, vue.toDisplayString(_ctx.translate("General_Cancel")), 1)
        ])
      ], 512)
    ])), [
      [_directive_tooltips]
    ]);
  }
  const UsersManager = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = vue.defineComponent({
    props: {
      title: {
        type: String,
        required: true
      },
      anonymousSites: {
        type: Array,
        required: true
      },
      anonymousDefaultReport: {
        type: [String, Number],
        required: true
      },
      anonymousDefaultSite: {
        type: String,
        required: true
      },
      anonymousDefaultDate: {
        type: String,
        required: true
      },
      availableDefaultDates: {
        type: Object,
        required: true
      },
      defaultReportOptions: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        loading: false,
        defaultReport: `${this.anonymousDefaultReport}`,
        defaultReportWebsite: this.anonymousDefaultSite,
        defaultDate: this.anonymousDefaultDate
      };
    },
    methods: {
      save() {
        const postParams = {
          anonymousDefaultReport: this.defaultReport === "1" ? this.defaultReportWebsite : this.defaultReport,
          anonymousDefaultDate: this.defaultDate
        };
        this.loading = true;
        CoreHome.AjaxHelper.post(
          {
            module: "UsersManager",
            action: "recordAnonymousUserSettings",
            format: "json"
          },
          postParams,
          { withTokenInUrl: true }
        ).then(() => {
          const id = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            id: "anonymousUserSettings",
            context: "success",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(id);
        }).finally(() => {
          this.loading = false;
        });
      }
    }
  });
  const _hoisted_1$6 = {
    key: 0,
    class: "alert alert-info"
  };
  const _hoisted_2$5 = { key: 1 };
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, { "content-title": _ctx.title }, {
      default: vue.withCtx(() => [
        _ctx.anonymousSites.length === 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, vue.toDisplayString(_ctx.translate("UsersManager_NoteNoAnonymousUserAccessSettingsWontBeUsed2")), 1)) : vue.createCommentVNode("", true),
        _ctx.anonymousSites.length > 0 ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_2$5, [
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: "anonymousDefaultReport",
              modelValue: _ctx.defaultReport,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.defaultReport = $event),
              introduction: _ctx.translate(
                "UsersManager_WhenUsersAreNotLoggedInAndVisitPiwikTheyShouldAccess"
              ),
              options: _ctx.defaultReportOptions
            }, null, 8, ["modelValue", "introduction", "options"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "anonymousDefaultReportWebsite",
              modelValue: _ctx.defaultReportWebsite,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.defaultReportWebsite = $event),
              options: _ctx.anonymousSites
            }, null, 8, ["modelValue", "options"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "radio",
              name: "anonymousDefaultDate",
              modelValue: _ctx.defaultDate,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.defaultDate = $event),
              introduction: _ctx.translate("UsersManager_ForAnonymousUsersReportDateToLoadByDefault"),
              options: _ctx.availableDefaultDates
            }, null, 8, ["modelValue", "introduction", "options"])
          ]),
          vue.createVNode(_component_SaveButton, {
            saving: _ctx.loading,
            onConfirm: _cache[3] || (_cache[3] = ($event) => _ctx.save())
          }, null, 8, ["saving"])
        ])), [
          [_directive_form]
        ]) : vue.createCommentVNode("", true)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const AnonymousSettings = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = vue.defineComponent({
    data() {
      return {
        showNewsletterSignup: true,
        newsletterSignupCheckbox: false,
        isProcessingNewsletterSignup: false,
        newsletterSignupButtonTitle: CoreHome.translate("General_Save")
      };
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton,
      Field: CorePluginsAdmin.Field
    },
    computed: {
      signupTitleText() {
        return CoreHome.translate(
          "UsersManager_NewsletterSignupMessage",
          CoreHome.externalLink("https://matomo.org/privacy-policy/"),
          "</a>"
        );
      }
    },
    methods: {
      signupForNewsletter() {
        this.newsletterSignupButtonTitle = CoreHome.translate("General_Loading");
        this.isProcessingNewsletterSignup = true;
        CoreHome.AjaxHelper.fetch(
          {
            module: "API",
            method: "UsersManager.newsletterSignup"
          },
          { withTokenInUrl: true }
        ).then(() => {
          this.isProcessingNewsletterSignup = false;
          this.showNewsletterSignup = false;
          const id = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("UsersManager_NewsletterSignupSuccessMessage"),
            id: "newslettersignup",
            context: "success",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(id);
        }).catch(() => {
          this.isProcessingNewsletterSignup = false;
          const id = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("UsersManager_NewsletterSignupFailureMessage"),
            id: "newslettersignup",
            context: "error",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(id);
          this.newsletterSignupButtonTitle = CoreHome.translate("General_PleaseTryAgain");
        });
      }
    }
  });
  const _hoisted_1$5 = { id: "newsletterSignup" };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("UsersManager_NewsletterSignupTitle")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "newsletterSignupCheckbox",
              id: "newsletterSignupCheckbox",
              modelValue: _ctx.newsletterSignupCheckbox,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.newsletterSignupCheckbox = $event),
              "full-width": true,
              title: _ctx.signupTitleText
            }, null, 8, ["modelValue", "title"])
          ]),
          vue.createVNode(_component_SaveButton, {
            id: "newsletterSignupBtn",
            onConfirm: _cache[1] || (_cache[1] = ($event) => _ctx.signupForNewsletter()),
            disabled: !_ctx.newsletterSignupCheckbox,
            value: _ctx.newsletterSignupButtonTitle,
            saving: _ctx.isProcessingNewsletterSignup
          }, null, 8, ["disabled", "value", "saving"])
        ]),
        _: 1
      }, 8, ["content-title"])
    ], 512)), [
      [vue.vShow, _ctx.showNewsletterSignup]
    ]);
  }
  const NewsletterSettings = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    name: "PersonalSettings",
    props: {
      isUsersAdminEnabled: {
        type: Boolean,
        required: true
      },
      title: {
        type: String,
        required: true
      },
      userLogin: {
        type: String,
        required: true
      },
      userEmail: {
        type: String,
        required: true
      },
      currentLanguageCode: {
        type: String,
        required: true
      },
      languageOptions: {
        type: Object,
        required: true
      },
      currentTimeformat: {
        type: Number,
        required: true
      },
      timeFormats: {
        type: Object,
        required: true
      },
      themeMode: {
        type: String,
        required: true
      },
      themeModeOptions: {
        type: Object,
        required: true
      },
      defaultReport: {
        type: [String, Number],
        required: true
      },
      defaultReportOptions: {
        type: Object,
        required: true
      },
      defaultReportIdSite: {
        type: [String, Number],
        required: true
      },
      defaultReportSiteName: {
        type: String,
        required: true
      },
      defaultDate: {
        type: String,
        required: true
      },
      availableDefaultDates: {
        type: Object,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      SaveButton: CorePluginsAdmin.SaveButton,
      Field: CorePluginsAdmin.Field,
      SiteSelector: CoreHome.SiteSelector,
      PasswordConfirmation: CorePluginsAdmin.PasswordConfirmation
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    data() {
      return {
        doesRequirePasswordConfirmation: false,
        username: this.userLogin,
        email: this.userEmail,
        language: this.currentLanguageCode,
        timeformat: this.currentTimeformat,
        theThemeMode: this.themeMode,
        theDefaultReport: this.defaultReport,
        site: {
          id: this.defaultReportIdSite,
          name: CoreHome.Matomo.helper.htmlDecode(this.defaultReportSiteName)
        },
        theDefaultDate: this.defaultDate,
        loading: false,
        showPasswordConfirmation: false
      };
    },
    methods: {
      save() {
        if (this.doesRequirePasswordConfirmation) {
          this.showPasswordConfirmation = true;
          return;
        }
        this.doSave();
      },
      doSave(password) {
        const postParams = {
          email: this.email,
          themeMode: this.theThemeMode,
          defaultReport: this.theDefaultReport === "MultiSites" ? this.theDefaultReport : this.site.id,
          defaultDate: this.theDefaultDate,
          language: this.language,
          timeformat: this.timeformat
        };
        if (password) {
          postParams.passwordConfirmation = password;
        }
        this.loading = true;
        CoreHome.AjaxHelper.post(
          {
            module: "UsersManager",
            action: "recordUserSettings",
            format: "json"
          },
          postParams,
          {
            withTokenInUrl: true
          }
        ).then(() => {
          CoreHome.Matomo.setThemeMode(this.theThemeMode);
          const id = CoreHome.NotificationsStore.show({
            message: CoreHome.translate("CoreAdminHome_SettingsSaveSuccess"),
            id: "PersonalSettingsSuccess",
            context: "success",
            type: "transient"
          });
          CoreHome.NotificationsStore.scrollToNotification(id);
          this.doesRequirePasswordConfirmation = false;
          this.loading = false;
        }).catch(() => {
          this.loading = false;
        });
      }
    }
  });
  const _hoisted_1$4 = { id: "userSettingsTable" };
  const _hoisted_2$4 = { key: 0 };
  const _hoisted_3$3 = {
    id: "languageHelp",
    class: "inline-help-node"
  };
  const _hoisted_4$2 = ["href"];
  const _hoisted_5$2 = { class: "sites_autocomplete" };
  const _hoisted_6$2 = {
    id: "themeModeHelp",
    class: "inline-help-node"
  };
  const _hoisted_7$2 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_SiteSelector = vue.resolveComponent("SiteSelector");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_PasswordConfirmation = vue.resolveComponent("PasswordConfirmation");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.title,
        feature: "true"
      }, {
        default: vue.withCtx(() => [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("form", _hoisted_1$4, [
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "username",
                title: _ctx.translate("General_Username"),
                disabled: true,
                modelValue: _ctx.username,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.username = $event),
                "inline-help": _ctx.translate("UsersManager_YourUsernameCannotBeChanged")
              }, null, 8, ["title", "modelValue", "inline-help"])
            ]),
            _ctx.isUsersAdminEnabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$4, [
              vue.createVNode(_component_Field, {
                uicontrol: "text",
                name: "email",
                "model-value": _ctx.email,
                "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => {
                  _ctx.email = $event;
                  _ctx.doesRequirePasswordConfirmation = true;
                }),
                maxlength: 100,
                title: _ctx.translate("UsersManager_Email")
              }, null, 8, ["model-value", "title"])
            ])) : vue.createCommentVNode("", true),
            vue.createElementVNode("div", _hoisted_3$3, [
              vue.createElementVNode("a", {
                target: "_blank",
                rel: "noreferrer noopener",
                href: _ctx.externalRawLink("https://matomo.org/translations/")
              }, vue.toDisplayString(_ctx.translate("LanguagesManager_AboutPiwikTranslations")), 9, _hoisted_4$2)
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "select",
                name: "language",
                modelValue: _ctx.language,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.language = $event),
                title: _ctx.translate("General_Language"),
                options: _ctx.languageOptions,
                "inline-help": "#languageHelp"
              }, null, 8, ["modelValue", "title", "options"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "select",
                name: "timeformat",
                modelValue: _ctx.timeformat,
                "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.timeformat = $event),
                title: _ctx.translate("General_TimeFormat"),
                options: _ctx.timeFormats
              }, null, 8, ["modelValue", "title", "options"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "radio",
                name: "themeMode",
                modelValue: _ctx.theThemeMode,
                "onUpdate:modelValue": [
                  _cache[4] || (_cache[4] = ($event) => _ctx.theThemeMode = $event),
                  _cache[5] || (_cache[5] = ($event) => {
                    _ctx.theThemeMode = $event;
                  })
                ],
                inlineHelp: "#themeModeHelp",
                title: "",
                introduction: _ctx.translate("CorePluginsAdmin_Theme"),
                options: _ctx.themeModeOptions
              }, null, 8, ["modelValue", "introduction", "options"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "radio",
                name: "defaultReport",
                modelValue: _ctx.theDefaultReport,
                "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.theDefaultReport = $event),
                introduction: _ctx.translate("UsersManager_ReportToLoadByDefault"),
                title: _ctx.translate("General_AllWebsitesDashboard"),
                options: _ctx.defaultReportOptions
              }, null, 8, ["modelValue", "introduction", "title", "options"])
            ]),
            vue.createElementVNode("div", _hoisted_5$2, [
              vue.createVNode(_component_SiteSelector, {
                modelValue: _ctx.site,
                "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.site = $event),
                "show-selected-site": true,
                "switch-site-on-select": false,
                "show-all-sites-item": false,
                showselectedsite: true,
                id: "defaultReportSiteSelector"
              }, null, 8, ["modelValue"])
            ]),
            vue.createElementVNode("div", null, [
              vue.createVNode(_component_Field, {
                uicontrol: "radio",
                name: "defaultDate",
                modelValue: _ctx.theDefaultDate,
                "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.theDefaultDate = $event),
                introduction: _ctx.translate("UsersManager_ReportDateToLoadByDefault"),
                options: _ctx.availableDefaultDates
              }, null, 8, ["modelValue", "introduction", "options"])
            ]),
            vue.createVNode(_component_SaveButton, {
              onConfirm: _cache[9] || (_cache[9] = ($event) => _ctx.save()),
              saving: _ctx.loading
            }, null, 8, ["saving"]),
            vue.createVNode(_component_PasswordConfirmation, {
              modelValue: _ctx.showPasswordConfirmation,
              "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => _ctx.showPasswordConfirmation = $event),
              onConfirmed: _ctx.doSave
            }, null, 8, ["modelValue", "onConfirmed"])
          ])), [
            [_directive_form]
          ])
        ]),
        _: 1
      }, 8, ["content-title"]),
      vue.createElementVNode("div", _hoisted_6$2, [
        vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("UsersManager_ThemeModeHelp1")), 1),
        _cache[11] || (_cache[11] = vue.createElementVNode("br", null, null, -1)),
        vue.createElementVNode("span", {
          innerHTML: _ctx.$sanitize(_ctx.translate(
            "UsersManager_ThemeModeHelp2",
            `<em>${_ctx.translate("UsersManager_ThemeModeMatchBrowser")}</em>`
          ))
        }, null, 8, _hoisted_7$2),
        _cache[12] || (_cache[12] = vue.createElementVNode("br", null, null, -1)),
        _cache[13] || (_cache[13] = vue.createElementVNode("br", null, null, -1)),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_ThemeModeHelp3")), 1)
      ])
    ], 64);
  }
  const PersonalSettings = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const { $: $$1 } = window;
  const _sfc_main$3 = vue.defineComponent({
    props: {
      formNonce: String,
      noDescription: Boolean,
      invalidExpireDate: Boolean,
      forceSecureOnly: Boolean,
      defaultExpirationDays: Number,
      expirationReminderDays: Number,
      initialExpireDate: String
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field
    },
    data() {
      return {
        tokenDescription: "",
        tokenSecureOnly: true,
        tokenHasExpiration: true,
        tokenExpireDate: null,
        isSaving: false
      };
    },
    mounted() {
      this.setInitialTokenExpirationDate();
    },
    computed: {
      addNewTokenFormUrl() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "addNewToken"
        }))}`;
      },
      cancelLink() {
        const backlink = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "userSecurity"
        }))}`;
        return CoreHome.translate(
          "General_OrCancel",
          `<a class='entityCancelLink' href='${backlink}'>`,
          "</a>"
        );
      },
      forceSecureOnlyCalc() {
        return this.forceSecureOnly;
      },
      secureOnlyHelp() {
        return this.forceSecureOnly ? CoreHome.translate("UsersManager_AuthTokenSecureOnlyHelpForced") : CoreHome.translate("UsersManager_AuthTokenSecureOnlyHelp");
      },
      tokenExpireDateHelpText() {
        return CoreHome.translate(
          "UsersManager_TokenExpireDateHelpText",
          this.defaultExpirationDays,
          this.expirationReminderDays
        );
      },
      tokenExpireDateCheckboxHelpText() {
        return CoreHome.translate(
          "UsersManager_TokenExpireDateCheckboxHelp",
          this.expirationReminderDays
        );
      }
    },
    methods: {
      setInitialTokenExpirationDate() {
        const initialDate = new Date(this.initialExpireDate);
        const tokenExpireDateOptions = CoreHome.Matomo.getBaseDatePickerOptions(initialDate);
        const dtInput = $$1('[name="token_expire_date"]', this.$refs.root);
        setTimeout(() => {
          this.tokenExpireDate = this.initialExpireDate;
          dtInput.datepicker(tokenExpireDateOptions);
          dtInput.datepicker("setDate", initialDate);
        });
      },
      onKeydownTokenExpireDate(event) {
        setTimeout(() => {
          this.tokenExpireDate = event.target.value;
        });
      }
    }
  });
  const _hoisted_1$3 = { key: 0 };
  const _hoisted_2$3 = {
    key: 1,
    class: "alert alert-danger"
  };
  const _hoisted_3$2 = {
    key: 2,
    class: "alert alert-danger"
  };
  const _hoisted_4$1 = ["action"];
  const _hoisted_5$1 = { style: { "margin-bottom": "2rem" } };
  const _hoisted_6$1 = { class: "form-group row tokenExpireDateTime" };
  const _hoisted_7$1 = { class: "col s12 m6" };
  const _hoisted_8$1 = {
    for: "token_expire_date",
    class: "active"
  };
  const _hoisted_9$1 = ["value", "required"];
  const _hoisted_10$1 = { class: "col s12 m6" };
  const _hoisted_11$1 = { class: "form-help" };
  const _hoisted_12$1 = { class: "inline-help" };
  const _hoisted_13$1 = ["innerHTML"];
  const _hoisted_14$1 = ["value"];
  const _hoisted_15$1 = ["value"];
  const _hoisted_16$1 = ["innerHTML"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("UsersManager_AuthTokens")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_TokenAuthIntro")), 1),
        _ctx.noDescription || _ctx.invalidExpireDate ? (vue.openBlock(), vue.createElementBlock("br", _hoisted_1$3)) : vue.createCommentVNode("", true),
        _ctx.noDescription ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, vue.toDisplayString(_ctx.translate("General_Description")) + ": " + vue.toDisplayString(_ctx.translate("General_ValidatorErrorEmptyValue")), 1)) : vue.createCommentVNode("", true),
        _ctx.invalidExpireDate ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$2, vue.toDisplayString(_ctx.translate("UsersManager_TokenExpireDate")) + ": " + vue.toDisplayString(_ctx.translate("UsersManager_InvalidTokenExpireDateFormat")), 1)) : vue.createCommentVNode("", true),
        vue.createElementVNode("form", {
          action: _ctx.addNewTokenFormUrl,
          method: "post",
          class: "addTokenForm"
        }, [
          vue.createVNode(_component_Field, {
            uicontrol: "text",
            name: "description",
            title: _ctx.translate("General_Description"),
            maxlength: 100,
            required: true,
            "inline-help": _ctx.translate("UsersManager_AuthTokenPurpose"),
            modelValue: _ctx.tokenDescription,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.tokenDescription = $event),
            autofocus: ""
          }, null, 8, ["title", "inline-help", "modelValue"]),
          vue.createVNode(_component_Field, {
            uicontrol: "checkbox",
            name: "secure_only",
            title: _ctx.translate("UsersManager_OnlyAllowSecureRequests"),
            required: false,
            "inline-help": _ctx.secureOnlyHelp,
            modelValue: _ctx.tokenSecureOnly,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.tokenSecureOnly = $event),
            disabled: _ctx.forceSecureOnlyCalc
          }, null, 8, ["title", "inline-help", "modelValue", "disabled"]),
          vue.createElementVNode("section", _hoisted_5$1, [
            vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("UsersManager_ExpireDate")), 1),
            vue.createVNode(_component_Field, {
              uicontrol: "checkbox",
              name: "has_expiration",
              title: _ctx.translate("UsersManager_TokenExpireDateCheckboxLabel"),
              required: false,
              "inline-help": _ctx.tokenExpireDateCheckboxHelpText,
              modelValue: _ctx.tokenHasExpiration,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.tokenHasExpiration = $event)
            }, null, 8, ["title", "inline-help", "modelValue"]),
            vue.withDirectives(vue.createElementVNode("div", _hoisted_6$1, [
              vue.createElementVNode("div", _hoisted_7$1, [
                vue.createElementVNode("label", _hoisted_8$1, vue.toDisplayString(_ctx.translate("UsersManager_TokenExpireDate")), 1),
                vue.createElementVNode("input", {
                  type: "text",
                  id: "token_expire_date",
                  name: "token_expire_date",
                  value: _ctx.tokenExpireDate,
                  required: _ctx.tokenHasExpiration,
                  onChange: _cache[3] || (_cache[3] = ($event) => _ctx.onKeydownTokenExpireDate($event)),
                  onKeydown: _cache[4] || (_cache[4] = ($event) => _ctx.onKeydownTokenExpireDate($event))
                }, null, 40, _hoisted_9$1)
              ]),
              vue.createElementVNode("div", _hoisted_10$1, [
                vue.createElementVNode("div", _hoisted_11$1, [
                  vue.createElementVNode("span", _hoisted_12$1, [
                    vue.createElementVNode("span", null, [
                      vue.createElementVNode("span", {
                        innerHTML: _ctx.$sanitize(_ctx.tokenExpireDateHelpText)
                      }, null, 8, _hoisted_13$1)
                    ])
                  ])
                ])
              ])
            ], 512), [
              [vue.vShow, _ctx.tokenHasExpiration]
            ])
          ]),
          vue.createElementVNode("input", {
            type: "hidden",
            value: _ctx.formNonce,
            name: "nonce"
          }, null, 8, _hoisted_14$1),
          vue.createElementVNode("input", {
            type: "submit",
            value: _ctx.translate("UsersManager_CreateNewToken"),
            class: "btn",
            style: { "margin-right": "4px" }
          }, null, 8, _hoisted_15$1),
          vue.createElementVNode("span", {
            innerHTML: _ctx.$sanitize(_ctx.cancelLink)
          }, null, 8, _hoisted_16$1)
        ], 8, _hoisted_4$1)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const AddNewToken = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      generatedToken: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
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
  const _hoisted_1$2 = {
    style: { "font-size": "40px" },
    class: "generatedTokenAuth"
  };
  const _hoisted_2$2 = ["href"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("UsersManager_TokenSuccessfullyGenerated")
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_PleaseStoreToken")) + " ", 1),
          _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_DoNotStoreToken")), 1)
        ]),
        vue.createElementVNode("div", null, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", _hoisted_1$2, [
            vue.createElementVNode("code", null, vue.toDisplayString(_ctx.generatedToken), 1)
          ])), [
            [_directive_copy_to_clipboard, {}]
          ])
        ]),
        vue.createElementVNode("a", {
          href: _ctx.userSecurityLink,
          class: "btn",
          style: { "height": "auto" }
        }, vue.toDisplayString(_ctx.translate("UsersManager_ConfirmTokenCopied")) + " " + vue.toDisplayString(_ctx.translate("UsersManager_GoBackSecurityPage")), 9, _hoisted_2$2)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const AddNewTokenSuccess = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      deleteTokenNonce: String,
      tokens: Array,
      isUsersAdminEnabled: Boolean,
      changePasswordNonce: String,
      isValidHost: Boolean,
      isSuperUser: Boolean,
      invalidHost: String,
      afterPasswordEventContent: String,
      invalidHostMailLinkStart: String,
      passwordStrengthValidationRules: Array
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field
    },
    directives: {
      ContentTable: CoreHome.ContentTable,
      AutoClearPassword: CoreHome.AutoClearPassword
    },
    data() {
      return {
        password: "",
        passwordBis: "",
        passwordConfirmation: "",
        passwordStrengthMet: false,
        passwordBisStrengthMet: false
      };
    },
    mounted() {
      const afterPassword = this.$refs.afterPassword;
      CoreHome.Matomo.helper.compileVueEntryComponents(afterPassword);
    },
    methods: {
      setPasswordStrengthValidation(event, field) {
        if (field === "passwordStrengthMet") {
          this.passwordStrengthMet = event;
        }
        if (field === "passwordBisStrengthMet") {
          this.passwordBisStrengthMet = event;
        }
      }
    },
    computed: {
      recordPasswordChangeAction() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "recordPasswordChange"
        }))}`;
      },
      emailYourAdminText() {
        return CoreHome.translate(
          "UsersManager_EmailYourAdministrator",
          this.invalidHostMailLinkStart || "",
          "</a>"
        );
      },
      noTokenCreatedYetText() {
        const addNewTokenLink = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "addNewToken"
        }))}`;
        return CoreHome.translate(
          "UsersManager_NoTokenCreatedYetCreateNow",
          `<a href="${addNewTokenLink}">`,
          "</a>"
        );
      },
      changePasswordInfoNotification() {
        var _a2;
        const sessionsLoggedOut = CoreHome.translate("UsersManager_PasswordChangeTerminatesOtherSessions");
        let tokensNotRevoked = "";
        if ((_a2 = this.tokens) == null ? void 0 : _a2.length) {
          tokensNotRevoked = CoreHome.translate(
            "UsersManager_PasswordChangeDoesNotRevokeAuthTokens",
            `<a href="#authtokens">${CoreHome.translate("UsersManager_AuthTokens")}</a>`
          );
        }
        return [sessionsLoggedOut, tokensNotRevoked].filter((item) => item).join("<br><br>");
      },
      deleteTokenAction() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "deleteToken"
        }))}`;
      },
      addNewTokenLink() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "UsersManager",
          action: "addNewToken"
        }))}`;
      },
      afterPasswordComponent() {
        if (!this.afterPasswordEventContent) {
          return null;
        }
        const afterPassword = this.$refs.afterPassword;
        return vue.markRaw({
          template: this.afterPasswordEventContent,
          beforeUnmount() {
            CoreHome.Matomo.helper.destroyVueComponent(afterPassword);
          }
        });
      },
      isPasswordChangeFormSubmitEnabled() {
        var _a2, _b;
        return this.passwordConfirmation && (!((_a2 = this.passwordStrengthValidationRules) == null ? void 0 : _a2.length) || ((_b = this.passwordStrengthValidationRules) == null ? void 0 : _b.length) && this.passwordStrengthMet && this.passwordBisStrengthMet);
      }
    }
  });
  const _hoisted_1$1 = ["action"];
  const _hoisted_2$1 = ["value"];
  const _hoisted_3$1 = { key: 0 };
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = ["value", "disabled"];
  const _hoisted_6 = { key: 1 };
  const _hoisted_7 = { class: "alert alert-danger" };
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = { ref: "afterPassword" };
  const _hoisted_10 = { class: "listAuthTokens" };
  const _hoisted_11 = { key: 0 };
  const _hoisted_12 = ["innerHTML"];
  const _hoisted_13 = { class: "creationDate" };
  const _hoisted_14 = ["action"];
  const _hoisted_15 = ["value"];
  const _hoisted_16 = ["value"];
  const _hoisted_17 = ["title"];
  const _hoisted_18 = { class: "tableActionBar" };
  const _hoisted_19 = ["href"];
  const _hoisted_20 = ["action"];
  const _hoisted_21 = ["value"];
  const _hoisted_22 = {
    type: "submit",
    class: "table-action delete-all-tokens"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_auto_clear_password = vue.resolveDirective("auto-clear-password");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      _ctx.isUsersAdminEnabled ? (vue.openBlock(), vue.createBlock(_component_ContentBlock, {
        key: 0,
        "content-title": _ctx.translate("General_ChangePassword"),
        feature: "true"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("form", {
            id: "userSettingsTable",
            method: "post",
            action: _ctx.recordPasswordChangeAction
          }, [
            vue.createElementVNode("input", {
              type: "hidden",
              value: _ctx.changePasswordNonce,
              name: "nonce"
            }, null, 8, _hoisted_2$1),
            _ctx.isValidHost ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, [
              vue.withDirectives(vue.createVNode(_component_Field, {
                uicontrol: "password",
                name: "password",
                autocomplete: "off",
                modelValue: _ctx.password,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.password = $event),
                title: _ctx.translate("Login_NewPassword"),
                "inline-help": _ctx.translate("UsersManager_IfYouWouldLikeToChangeThePasswordTypeANewOne"),
                "ui-control-attributes": {
                  passwordStrengthValidationRules: _ctx.passwordStrengthValidationRules
                },
                "onCheck:isValid": _cache[1] || (_cache[1] = ($event) => _ctx.setPasswordStrengthValidation($event, "passwordStrengthMet"))
              }, null, 8, ["modelValue", "title", "inline-help", "ui-control-attributes"]), [
                [_directive_auto_clear_password]
              ]),
              vue.withDirectives(vue.createVNode(_component_Field, {
                uicontrol: "password",
                name: "passwordBis",
                autocomplete: "off",
                modelValue: _ctx.passwordBis,
                "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.passwordBis = $event),
                title: _ctx.translate("Login_NewPasswordRepeat"),
                "inline-help": _ctx.translate("UsersManager_TypeYourPasswordAgain"),
                "ui-control-attributes": {
                  passwordStrengthValidationRules: _ctx.passwordStrengthValidationRules
                },
                "onCheck:isValid": _cache[3] || (_cache[3] = ($event) => _ctx.setPasswordStrengthValidation($event, "passwordBisStrengthMet"))
              }, null, 8, ["modelValue", "title", "inline-help", "ui-control-attributes"]), [
                [_directive_auto_clear_password]
              ]),
              vue.withDirectives(vue.createVNode(_component_Field, {
                uicontrol: "password",
                name: "passwordConfirmation",
                autocomplete: "off",
                modelValue: _ctx.passwordConfirmation,
                "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.passwordConfirmation = $event),
                title: _ctx.translate("UsersManager_YourCurrentPassword"),
                "inline-help": _ctx.translate("UsersManager_TypeYourCurrentPassword")
              }, null, 8, ["modelValue", "title", "inline-help"]), [
                [_directive_auto_clear_password]
              ]),
              vue.createElementVNode("div", {
                class: "alert alert-info",
                innerHTML: _ctx.$sanitize(_ctx.changePasswordInfoNotification)
              }, null, 8, _hoisted_4),
              vue.createElementVNode("input", {
                type: "submit",
                value: _ctx.translate("General_Save"),
                class: "btn",
                disabled: !_ctx.isPasswordChangeFormSubmitEnabled
              }, null, 8, _hoisted_5)
            ])) : vue.createCommentVNode("", true),
            !_ctx.isValidHost ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, [
              vue.createElementVNode("div", _hoisted_7, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("UsersManager_InjectedHostCannotChangePwd", _ctx.invalidHost || "")) + " ", 1),
                !_ctx.isSuperUser ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  innerHTML: _ctx.$sanitize(_ctx.emailYourAdminText)
                }, null, 8, _hoisted_8)) : vue.createCommentVNode("", true)
              ])
            ])) : vue.createCommentVNode("", true)
          ], 8, _hoisted_1$1)
        ]),
        _: 1
      }, 8, ["content-title"])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_9, [
        _ctx.isUsersAdminEnabled && _ctx.afterPasswordComponent ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.afterPasswordComponent), { key: 0 })) : vue.createCommentVNode("", true)
      ], 512),
      _cache[9] || (_cache[9] = vue.createElementVNode("a", {
        name: "authtokens",
        id: "authtokens"
      }, null, -1)),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("UsersManager_AuthTokens")
      }, {
        default: vue.withCtx(() => {
          var _a2, _b;
          return [
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("UsersManager_TokenAuthIntro")) + " " + vue.toDisplayString(_ctx.translate("UsersManager_ExpiredTokensDeleteAutomatically")), 1),
            vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_10, [
              vue.createElementVNode("thead", null, [
                vue.createElementVNode("tr", null, [
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_CreationDate")), 1),
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Description")), 1),
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("UsersManager_LastUsed")), 1),
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("UsersManager_SecureUseOnly")), 1),
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("UsersManager_ExpireDate")), 1),
                  vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Actions")), 1)
                ])
              ]),
              vue.createElementVNode("tbody", null, [
                !((_a2 = _ctx.tokens) == null ? void 0 : _a2.length) ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_11, [
                  vue.createElementVNode("td", {
                    colspan: 5,
                    innerHTML: _ctx.$sanitize(_ctx.noTokenCreatedYetText)
                  }, null, 8, _hoisted_12)
                ])) : vue.createCommentVNode("", true),
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.tokens || [], (theToken) => {
                  return vue.openBlock(), vue.createElementBlock("tr", {
                    key: theToken.idusertokenauth
                  }, [
                    vue.createElementVNode("td", null, [
                      vue.createElementVNode("span", _hoisted_13, vue.toDisplayString(theToken.date_created), 1)
                    ]),
                    vue.createElementVNode("td", null, vue.toDisplayString(theToken.description), 1),
                    vue.createElementVNode("td", null, vue.toDisplayString(theToken.last_used ? theToken.last_used : _ctx.translate("General_Never")), 1),
                    vue.createElementVNode("td", null, vue.toDisplayString(parseInt(`${theToken.secure_only}`, 10) === 1 ? _ctx.translate("General_Yes") : _ctx.translate("General_No")), 1),
                    vue.createElementVNode("td", null, vue.toDisplayString(theToken.date_expired ? theToken.date_expired : _ctx.translate("General_Never")), 1),
                    vue.createElementVNode("td", null, [
                      vue.createElementVNode("form", {
                        method: "post",
                        action: _ctx.deleteTokenAction,
                        style: { "display": "inline" }
                      }, [
                        vue.createElementVNode("input", {
                          name: "nonce",
                          type: "hidden",
                          value: _ctx.deleteTokenNonce
                        }, null, 8, _hoisted_15),
                        vue.createElementVNode("input", {
                          name: "idtokenauth",
                          type: "hidden",
                          value: theToken.idusertokenauth
                        }, null, 8, _hoisted_16),
                        vue.createElementVNode("button", {
                          type: "submit",
                          class: "table-action",
                          title: _ctx.translate("General_Delete")
                        }, [..._cache[5] || (_cache[5] = [
                          vue.createElementVNode("span", { class: "icon-delete" }, null, -1)
                        ])], 8, _hoisted_17)
                      ], 8, _hoisted_14)
                    ])
                  ]);
                }), 128))
              ])
            ])), [
              [_directive_content_table]
            ]),
            vue.createElementVNode("div", _hoisted_18, [
              vue.createElementVNode("a", {
                href: _ctx.addNewTokenLink,
                class: "addNewToken"
              }, [
                _cache[6] || (_cache[6] = vue.createElementVNode("span", { class: "icon-add" }, null, -1)),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_CreateNewToken")), 1)
              ], 8, _hoisted_19),
              ((_b = _ctx.tokens) == null ? void 0 : _b.length) ? (vue.openBlock(), vue.createElementBlock("form", {
                key: 0,
                method: "post",
                action: _ctx.deleteTokenAction,
                style: { "display": "inline" }
              }, [
                vue.createElementVNode("input", {
                  name: "nonce",
                  type: "hidden",
                  value: _ctx.deleteTokenNonce
                }, null, 8, _hoisted_21),
                _cache[8] || (_cache[8] = vue.createElementVNode("input", {
                  name: "idtokenauth",
                  type: "hidden",
                  value: "all"
                }, null, -1)),
                vue.createElementVNode("button", _hoisted_22, [
                  _cache[7] || (_cache[7] = vue.createElementVNode("span", { class: "icon-delete" }, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("UsersManager_DeleteAllTokens")), 1)
                ])
              ], 8, _hoisted_20)) : vue.createCommentVNode("", true)
            ])
          ];
        }),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const UserSecurity = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    props: {
      isUsersAdminEnabled: {
        type: Boolean,
        required: true
      },
      userLogin: {
        type: String,
        required: true
      },
      userEmail: {
        type: String,
        required: true
      },
      currentLanguageCode: {
        type: String,
        required: true
      },
      languageOptions: {
        type: Object,
        required: true
      },
      currentTimeformat: {
        type: Number,
        required: true
      },
      timeFormats: {
        type: Object,
        required: true
      },
      themeMode: {
        type: String,
        required: true
      },
      themeModeOptions: {
        type: Object,
        required: true
      },
      defaultReport: {
        type: [String, Number],
        required: true
      },
      defaultReportOptions: {
        type: Object,
        required: true
      },
      defaultReportIdSite: {
        type: [String, Number],
        required: true
      },
      defaultReportSiteName: {
        type: String,
        required: true
      },
      defaultDate: {
        type: String,
        required: true
      },
      availableDefaultDates: {
        type: Object,
        required: true
      },
      showNewsletterSignup: Boolean,
      ignoreCookieSet: Boolean,
      setIgnoreCookieNonce: String,
      piwikHost: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      PersonalSettings,
      NewsletterSettings,
      PluginSettings: CorePluginsAdmin.PluginSettings
    },
    computed: {
      yourVisitsAreText() {
        if (this.ignoreCookieSet) {
          return CoreHome.translate(
            "UsersManager_YourVisitsAreIgnoredOnDomain",
            "<strong>",
            this.piwikHost,
            "</strong>"
          );
        }
        return CoreHome.translate(
          "UsersManager_YourVisitsAreNotIgnored",
          "<strong>",
          "</strong>"
        );
      },
      setIgnoreCookieLink() {
        return `?${CoreHome.MatomoUrl.stringify({
          module: "UsersManager",
          action: "setIgnoreCookie",
          nonce: this.setIgnoreCookieNonce
        })}#excludeCookie`;
      }
    }
  });
  const _hoisted_1 = ["innerHTML"];
  const _hoisted_2 = { style: { "margin-left": "20px" } };
  const _hoisted_3 = ["href"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PersonalSettings = vue.resolveComponent("PersonalSettings");
    const _component_NewsletterSettings = vue.resolveComponent("NewsletterSettings");
    const _component_PluginSettings = vue.resolveComponent("PluginSettings");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createVNode(_component_PersonalSettings, {
        "is-users-admin-enabled": _ctx.isUsersAdminEnabled,
        title: _ctx.translate("UsersManager_PersonalSettings"),
        "user-login": _ctx.userLogin,
        "user-email": _ctx.userEmail,
        "current-language-code": _ctx.currentLanguageCode,
        "language-options": _ctx.languageOptions,
        "current-timeformat": _ctx.currentTimeformat,
        "time-formats": _ctx.timeFormats,
        "theme-mode": _ctx.themeMode,
        "theme-mode-options": _ctx.themeModeOptions,
        "default-report": _ctx.defaultReport,
        "default-report-options": _ctx.defaultReportOptions,
        "default-report-id-site": _ctx.defaultReportIdSite,
        "default-report-site-name": _ctx.defaultReportSiteName,
        "default-date": _ctx.defaultDate,
        "available-default-dates": _ctx.availableDefaultDates
      }, null, 8, ["is-users-admin-enabled", "title", "user-login", "user-email", "current-language-code", "language-options", "current-timeformat", "time-formats", "theme-mode", "theme-mode-options", "default-report", "default-report-options", "default-report-id-site", "default-report-site-name", "default-date", "available-default-dates"]),
      _ctx.showNewsletterSignup ? (vue.openBlock(), vue.createBlock(_component_NewsletterSettings, { key: 0 })) : vue.createCommentVNode("", true),
      vue.createVNode(_component_PluginSettings, { mode: "user" }),
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("UsersManager_ExcludeVisitsViaCookie"),
        class: "ignoreCookieSettings"
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.yourVisitsAreText)
          }, null, 8, _hoisted_1),
          vue.createElementVNode("span", _hoisted_2, [
            vue.createElementVNode("a", { href: _ctx.setIgnoreCookieLink }, [
              vue.createTextVNode(" › " + vue.toDisplayString(_ctx.ignoreCookieSet ? _ctx.translate("UsersManager_ClickHereToDeleteTheCookie") : _ctx.translate("UsersManager_ClickHereToSetTheCookieOnDomain", _ctx.piwikHost)) + " ", 1),
              _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1))
            ], 8, _hoisted_3)
          ])
        ]),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const UserSettings = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AddNewToken = AddNewToken;
  exports2.AddNewTokenSuccess = AddNewTokenSuccess;
  exports2.AnonymousSettings = AnonymousSettings;
  exports2.CapabilitiesEdit = CapabilitiesEdit;
  exports2.NewsletterSettings = NewsletterSettings;
  exports2.PagedUsersList = PagedUsersList;
  exports2.PersonalSettings = PersonalSettings;
  exports2.ResendInviteModal = ResendInviteModal;
  exports2.UserEditForm = UserEditForm;
  exports2.UserInvite = UserInvite;
  exports2.UserPermissionsEdit = UserPermissionsEdit;
  exports2.UserSecurity = UserSecurity;
  exports2.UserSettings = UserSettings;
  exports2.UsersManager = UsersManager;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
