(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("CoreHome"), require("vue"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "CoreHome", "vue", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Goals = {}, global.CoreHome, global.Vue, global.CorePluginsAdmin));
})(this, (function(exports2, CoreHome, vue, CorePluginsAdmin) {
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
  const { $ } = window;
  const GoalPageLink = {
    mounted(el, binding) {
      if (!CoreHome.Matomo.helper.isReportingPage()) {
        return;
      }
      const title = $(el).text();
      const link = $("<a></a>");
      link.text(title);
      link.attr("title", CoreHome.translate("Goals_ClickToViewThisGoal"));
      link.click((e) => {
        e.preventDefault();
        CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          category: "Goals_Goals",
          subcategory: binding.value.idGoal
        }));
      });
      $(el).html(link[0]);
    }
  };
  CoreHome.Matomo.on("Matomo.processDynamicHtml", ($element) => {
    $element.find("[goal-page-link]").each((i, e) => {
      if ($(e).attr("goal-page-link-handled")) {
        return;
      }
      const idGoal = $(e).attr("goal-page-link");
      if (idGoal) {
        GoalPageLink.mounted(e, {
          instance: null,
          value: {
            idGoal
          },
          oldValue: null,
          modifiers: {},
          dir: {}
        });
      }
      $(e).attr("goal-page-link-handled", "1");
    });
  });
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class ManageGoalsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({}));
      __publicField(this, "idGoal", vue.computed(() => this.privateState.idGoal));
    }
    setIdGoalShown(idGoal) {
      this.privateState.idGoal = idGoal;
    }
  }
  const ManageGoalsStore$1 = new ManageGoalsStore();
  const notificationKey = "Goals.ManageGoals.Notification";
  function ambiguousBoolToInt(n) {
    return !!n && n !== "0" ? 1 : 0;
  }
  const _sfc_main = vue.defineComponent({
    inheritAttrs: false,
    props: {
      onlyShowAddNewGoal: Boolean,
      userCanEditGoals: Boolean,
      ecommerceEnabled: Boolean,
      goals: {
        type: Object,
        required: true
      },
      addNewGoalIntro: String,
      goalTriggerTypeOptions: Object,
      goalMatchAttributeOptions: Array,
      eventTypeOptions: Array,
      patternTypeOptions: Array,
      numericComparisonTypeOptions: Array,
      allowMultipleOptions: Array,
      showAddGoal: Boolean,
      showGoal: Number,
      beforeGoalListActionsBody: Object,
      endEditTable: String,
      beforeGoalListActionsHead: String
    },
    data() {
      return {
        showEditGoal: false,
        showGoalList: true,
        goal: {},
        isLoading: false,
        eventType: "event_category",
        triggerType: "visitors",
        apiMethod: "",
        submitText: "",
        goalToDelete: null,
        addEditTableComponent: false,
        patternMissing: false
      };
    },
    components: {
      SaveButton: CorePluginsAdmin.SaveButton,
      ContentBlock: CoreHome.ContentBlock,
      ActivityIndicator: CoreHome.ActivityIndicator,
      Field: CorePluginsAdmin.Field,
      Alert: CoreHome.Alert,
      VueEntryContainer: CoreHome.VueEntryContainer
    },
    watch: {
      "goal.pattern": function goalPatternChanged(pattern) {
        if (this.patternMissing && pattern !== void 0 && pattern !== null && `${pattern}` !== "") {
          this.patternMissing = false;
        }
      }
    },
    directives: {
      ContentTable: CoreHome.ContentTable,
      Form: CorePluginsAdmin.Form
    },
    created() {
      ManageGoalsStore$1.setIdGoalShown(this.showGoal);
    },
    unmounted() {
      ManageGoalsStore$1.setIdGoalShown(void 0);
    },
    mounted() {
      if (this.showAddGoal) {
        this.createGoal();
      } else if (this.showGoal) {
        this.editGoal(this.showGoal);
      } else {
        this.showListOfReports();
      }
      const storedNotifications = this.getStoredNotification();
      if (storedNotifications) {
        this.showNotificationMessage(storedNotifications.goal, storedNotifications.create);
      }
    },
    methods: {
      scrollToTop() {
        setTimeout(() => {
          CoreHome.Matomo.helper.lazyScrollTo(".pageWrap", 200);
        });
      },
      initGoalForm(goalMethodAPI, submitText, goalName, description, matchAttribute, pattern, patternType, caseSensitive, revenue, allowMultiple, useEventValueAsRevenue, goalId) {
        CoreHome.Matomo.postEvent("Goals.beforeInitGoalForm", goalMethodAPI, goalId, goalName);
        this.apiMethod = goalMethodAPI;
        this.patternMissing = false;
        this.goal = {};
        this.goal.name = goalName;
        this.goal.description = description;
        let actualMatchAttribute = matchAttribute;
        if (actualMatchAttribute === "manually") {
          this.triggerType = "manually";
          actualMatchAttribute = "url";
        } else {
          this.triggerType = "visitors";
        }
        if (actualMatchAttribute.indexOf("event") === 0) {
          this.eventType = actualMatchAttribute;
          actualMatchAttribute = "event";
        } else {
          this.eventType = "event_category";
        }
        this.goal.match_attribute = actualMatchAttribute;
        this.goal.allow_multiple = allowMultiple;
        this.goal.pattern_type = patternType;
        this.goal.pattern = pattern;
        this.goal.case_sensitive = caseSensitive;
        this.goal.revenue = revenue;
        this.goal.event_value_as_revenue = useEventValueAsRevenue;
        this.submitText = submitText;
        this.goal.idgoal = goalId;
      },
      showListOfReports() {
        CoreHome.Matomo.postEvent("Goals.cancelForm");
        this.showGoalList = true;
        this.showEditGoal = false;
        this.scrollToTop();
      },
      showAddEditForm() {
        this.showGoalList = false;
        this.showEditGoal = true;
      },
      createGoal() {
        const parameters = {
          isAllowed: true
        };
        CoreHome.Matomo.postEvent("Goals.initAddGoal", parameters);
        if (parameters && !parameters.isAllowed) {
          return;
        }
        this.showAddEditForm();
        this.initGoalForm(
          "Goals.addGoal",
          CoreHome.translate("Goals_AddGoal"),
          "",
          "",
          "url",
          "",
          "contains",
          false,
          0,
          false,
          false,
          0
        );
        this.scrollToTop();
      },
      editGoal(goalId) {
        this.showAddEditForm();
        const goal = this.goals[`${goalId}`];
        this.initGoalForm(
          "Goals.updateGoal",
          CoreHome.translate("Goals_UpdateGoal"),
          goal.name,
          goal.description,
          goal.match_attribute,
          goal.pattern,
          goal.pattern_type,
          !!goal.case_sensitive && goal.case_sensitive !== "0",
          parseInt(`${goal.revenue}`, 10),
          !!goal.allow_multiple && goal.allow_multiple !== "0",
          !!goal.event_value_as_revenue && goal.event_value_as_revenue !== "0",
          goalId
        );
        this.scrollToTop();
      },
      deleteGoal(goalId) {
        this.goalToDelete = this.goals[`${goalId}`];
        CoreHome.Matomo.helper.modalConfirm(this.$refs.confirm, {
          yes: () => {
            this.isLoading = true;
            CoreHome.AjaxHelper.fetch({
              idGoal: goalId,
              method: "Goals.deleteGoal"
            }).then(() => {
              window.location.reload();
            }).finally(() => {
              this.isLoading = false;
            });
          }
        });
      },
      save() {
        const parameters = {};
        parameters.name = this.goal.name;
        parameters.description = this.goal.description;
        if (this.isManuallyTriggered) {
          parameters.matchAttribute = "manually";
          parameters.patternType = "regex";
          parameters.pattern = ".*";
          parameters.caseSensitive = 0;
        } else {
          parameters.matchAttribute = this.goal.match_attribute;
          if (parameters.matchAttribute === "event") {
            parameters.matchAttribute = this.eventType;
            parameters.useEventValueAsRevenue = ambiguousBoolToInt(this.goal.event_value_as_revenue);
          }
          parameters.patternType = this.goal.pattern_type;
          parameters.pattern = this.goal.pattern;
          parameters.caseSensitive = ambiguousBoolToInt(this.goal.case_sensitive);
        }
        parameters.revenue = this.goal.revenue || 0;
        parameters.allowMultipleConversionsPerVisit = ambiguousBoolToInt(this.goal.allow_multiple);
        parameters.idGoal = this.goal.idgoal;
        parameters.method = this.apiMethod;
        const isCreate = parameters.method === "Goals.addGoal";
        const isUpdate = parameters.method === "Goals.updateGoal";
        const options = {};
        if (isUpdate) {
          CoreHome.Matomo.postEvent("Goals.beforeUpdateGoal", { parameters, options });
        } else if (isCreate) {
          CoreHome.Matomo.postEvent("Goals.beforeAddGoal", { parameters, options });
        }
        if (parameters == null ? void 0 : parameters.cancelRequest) {
          return;
        }
        if (parameters.matchAttribute !== "manually" && (parameters.pattern === void 0 || parameters.pattern === null || `${parameters.pattern}` === "")) {
          this.patternMissing = true;
          this.scrollToTop();
          return;
        }
        this.patternMissing = false;
        this.isLoading = true;
        CoreHome.AjaxHelper.fetch(parameters, options).then((response) => __async(this, null, function* () {
          let idToUse = parameters.idGoal;
          if (isCreate && response.value) {
            idToUse = response.value;
          }
          this.storeNotification(idToUse, isCreate);
          this.scrollToTop();
          const subcategory = CoreHome.MatomoUrl.parsed.value.subcategory;
          if (subcategory === "Goals_AddNewGoal" && CoreHome.Matomo.helper.isReportingPage()) {
            yield CoreHome.ReportingMenuStore.reloadMenuItems();
            CoreHome.MatomoUrl.updateHash(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
              subcategory: "Goals_ManageGoals"
            }));
            this.isLoading = false;
          } else {
            window.location.reload();
          }
        })).catch(() => {
          this.scrollToTop();
          this.isLoading = false;
        });
      },
      storeNotification(goalId, isCreate) {
        try {
          sessionStorage.setItem(notificationKey, JSON.stringify({ goal: goalId, create: isCreate }));
        } catch (e) {
        }
      },
      getStoredNotification() {
        const pendingNotification = sessionStorage.getItem(notificationKey);
        if (pendingNotification) {
          sessionStorage.removeItem(notificationKey);
          try {
            let { goal, create } = JSON.parse(pendingNotification);
            if (goal) {
              goal = parseInt(goal, 10);
            }
            create = !!create;
            return { goal, create };
          } catch (e) {
            return null;
          }
        }
        return null;
      },
      getGoalReportUrl(goalId) {
        const link = CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "CoreHome",
          action: "index"
        }));
        const hash = CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          category: "Goals_Goals",
          subcategory: goalId
        }));
        return `?${link}#?${hash}`;
      },
      showNotificationMessage(goalId, isCreate) {
        let successMessage = CoreHome.translate(isCreate ? "Goals_GoalCreated" : "Goals_GoalUpdated");
        const reportLink = `<a href="${this.getGoalReportUrl(goalId)}">[${CoreHome.translate("Goals_ViewGoalReport")}]</a>`;
        successMessage = `${successMessage} ${reportLink}`;
        CoreHome.NotificationsStore.show({
          id: "ManageGoals.create",
          message: successMessage,
          context: "success",
          type: "toast"
        });
      },
      changedTriggerType() {
        if (!this.isManuallyTriggered && !this.goal.pattern_type) {
          this.goal.pattern_type = "contains";
        }
      },
      initPatternType() {
        if (this.isMatchAttributeNumeric) {
          this.goal.pattern_type = "greater_than";
        } else {
          this.goal.pattern_type = "contains";
        }
      },
      lcfirst(s) {
        return `${s.slice(0, 1).toLowerCase()}${s.slice(1)}`;
      },
      ucfirst(s) {
        return `${s.slice(0, 1).toUpperCase()}${s.slice(1)}`;
      },
      goalNameChanged() {
        CoreHome.Matomo.postEvent("Goals.goalNameChanged", this.goal.name);
      }
    },
    computed: {
      learnMoreAboutGoalTracking() {
        return CoreHome.translate(
          "Goals_LearnMoreAboutGoalTrackingDocumentation",
          CoreHome.externalLink("https://matomo.org/docs/tracking-goals-web-analytics/"),
          "</a>"
        );
      },
      youCanEnableEcommerceReports() {
        const link = CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "SitesManager",
          action: "index"
        }));
        const ecommerceReportsText = CoreHome.externalLink("https://matomo.org/docs/ecommerce-analytics/") + CoreHome.translate("Goals_EcommerceReports") + "</a>";
        const websiteManageText = `<a href='${link}'>${CoreHome.translate("SitesManager_WebsitesManagement")}</a>`;
        return CoreHome.translate(
          "Goals_YouCanEnableEcommerceReports",
          ecommerceReportsText,
          websiteManageText
        );
      },
      siteName() {
        return CoreHome.Matomo.helper.htmlDecode(CoreHome.Matomo.siteName);
      },
      whereVisitedPageManuallyCallsJsTrackerText() {
        return CoreHome.translate(
          "Goals_WhereVisitedPageManuallyCallsJavascriptTrackerLearnMore",
          CoreHome.externalLink("https://developer.matomo.org/guides/tracking-javascript-guide#manually-trigger-goal-conversions"),
          "</a>"
        );
      },
      caseSensitiveTitle() {
        return `${CoreHome.translate("Goals_CaseSensitive")} ${CoreHome.translate("Goals_Optional")}`;
      },
      useEventValueAsRevenueHelp() {
        return `${CoreHome.translate("Goals_EventValueAsRevenueHelp")} <br/><br/> ${CoreHome.translate("Goals_EventValueAsRevenueHelp2")}`;
      },
      cancelText() {
        return CoreHome.translate(
          "General_OrCancel",
          "<a class='entityCancelLink'>",
          "</a>"
        );
      },
      isMatchAttributeNumeric() {
        return ["visit_duration"].indexOf(this.goal.match_attribute) > -1;
      },
      patternFieldLabel() {
        return this.goal.match_attribute === "visit_duration" ? CoreHome.translate("Goals_TimeInMinutes") : CoreHome.translate("Goals_Pattern");
      },
      goalMatchAttributeTranslations() {
        return {
          manually: CoreHome.translate("Goals_ManuallyTriggeredUsingJavascriptFunction"),
          file: CoreHome.translate("Goals_Download"),
          url: CoreHome.translate("Goals_VisitUrl"),
          title: CoreHome.translate("Goals_VisitPageTitle"),
          external_website: CoreHome.translate("Goals_ClickOutlink"),
          event_action: `${CoreHome.translate("Goals_SendEvent")} (${CoreHome.translate("Events_EventAction")})`,
          event_category: `${CoreHome.translate("Goals_SendEvent")} (${CoreHome.translate("Events_EventCategory")})`,
          event_name: `${CoreHome.translate("Goals_SendEvent")} (${CoreHome.translate("Events_EventName")})`,
          visit_duration: `${this.ucfirst(CoreHome.translate("Goals_VisitDuration"))}`
        };
      },
      beforeGoalListActionsBodyComponent() {
        if (!this.beforeGoalListActionsBody) {
          return {};
        }
        const componentsByIdGoal = {};
        Object.values(this.goals).forEach((g) => {
          const template = this.beforeGoalListActionsBody[g.idgoal];
          if (!template) {
            return;
          }
          componentsByIdGoal[g.idgoal] = {
            template
          };
        });
        return vue.markRaw(componentsByIdGoal);
      },
      beforeGoalListActionsHeadComponent() {
        if (!this.beforeGoalListActionsHead) {
          return null;
        }
        return vue.markRaw({
          template: this.beforeGoalListActionsHead
        });
      },
      isManuallyTriggered() {
        return this.triggerType === "manually";
      },
      matchesExpressionExternal() {
        const url = "'http://www.amazon.com\\/(.*)\\/yourAffiliateId'";
        return CoreHome.translate("Goals_MatchesExpression", url);
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
  const _hoisted_1 = { class: "manageGoals" };
  const _hoisted_2 = {
    id: "entityEditContainer",
    feature: "true",
    class: "managegoals"
  };
  const _hoisted_3 = { class: "contentHelp" };
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = { key: 0 };
  const _hoisted_6 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_7 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = { class: "first" };
  const _hoisted_10 = { class: "manageGoals-descriptionColumn" };
  const _hoisted_11 = { class: "manageGoals-triggerColumn" };
  const _hoisted_12 = {
    key: 1,
    class: "manageGoals-actionsColumn"
  };
  const _hoisted_13 = { key: 0 };
  const _hoisted_14 = { colspan: "8" };
  const _hoisted_15 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_16 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_17 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_18 = ["id"];
  const _hoisted_19 = { class: "first" };
  const _hoisted_20 = { class: "manageGoals-descriptionColumn" };
  const _hoisted_21 = { class: "manageGoals-triggerColumn" };
  const _hoisted_22 = { class: "matchAttribute" };
  const _hoisted_23 = { key: 0 };
  const _hoisted_24 = { key: 1 };
  const _hoisted_25 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_26 = ["innerHTML"];
  const _hoisted_27 = {
    key: 1,
    class: "entityTable_ActionCell entityTable_ActionCell-3 manageGoals-actionsColumn"
  };
  const _hoisted_28 = ["href", "title", "aria-label"];
  const _hoisted_29 = ["onClick", "title"];
  const _hoisted_30 = ["onClick", "title"];
  const _hoisted_31 = {
    key: 0,
    class: "tableActionBar"
  };
  const _hoisted_32 = /* @__PURE__ */ vue.createElementVNode("span", { class: "icon-add" }, null, -1);
  const _hoisted_33 = {
    class: "ui-confirm",
    ref: "confirm"
  };
  const _hoisted_34 = ["value"];
  const _hoisted_35 = ["value"];
  const _hoisted_36 = { class: "addEditGoal" };
  const _hoisted_37 = ["innerHTML"];
  const _hoisted_38 = { class: "row goalIsTriggeredWhen" };
  const _hoisted_39 = { class: "col s12" };
  const _hoisted_40 = { class: "row" };
  const _hoisted_41 = { class: "col s12 m6 goalTriggerType" };
  const _hoisted_42 = { class: "col s12 m6" };
  const _hoisted_43 = ["innerHTML"];
  const _hoisted_44 = { class: "row whereTheMatchAttrbiute" };
  const _hoisted_45 = { class: "col s12" };
  const _hoisted_46 = { class: "row" };
  const _hoisted_47 = { class: "col s12 m6 l4" };
  const _hoisted_48 = {
    key: 0,
    class: "col s12 m6 l4"
  };
  const _hoisted_49 = {
    key: 1,
    class: "col s12 m6 l4"
  };
  const _hoisted_50 = { class: "col s12 m6 l4" };
  const _hoisted_51 = {
    id: "examples_pattern",
    class: "col s12"
  };
  const _hoisted_52 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_53 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_54 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_55 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_56 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_57 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_58 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_59 = /* @__PURE__ */ vue.createElementVNode("br", null, null, -1);
  const _hoisted_60 = { ref: "endedittable" };
  const _hoisted_61 = /* @__PURE__ */ vue.createElementVNode("input", {
    type: "hidden",
    name: "goalIdUpdate",
    value: ""
  }, null, -1);
  const _hoisted_62 = { key: 0 };
  const _hoisted_63 = ["innerHTML"];
  const _hoisted_64 = /* @__PURE__ */ vue.createElementVNode("a", { id: "bottom" }, null, -1);
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_Field = vue.resolveComponent("Field");
    const _component_Alert = vue.resolveComponent("Alert");
    const _component_VueEntryContainer = vue.resolveComponent("VueEntryContainer");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _directive_content_table = vue.resolveDirective("content-table");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.withDirectives(vue.createElementVNode("div", _hoisted_2, [
          vue.createVNode(_component_ContentBlock, {
            "content-title": _ctx.translate("Goals_ManageGoals")
          }, {
            default: vue.withCtx(() => [
              vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoading }, null, 8, ["loading"]),
              vue.createElementVNode("div", _hoisted_3, [
                vue.createElementVNode("span", {
                  innerHTML: _ctx.$sanitize(_ctx.learnMoreAboutGoalTracking)
                }, null, 8, _hoisted_4),
                !_ctx.ecommerceEnabled ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5, [
                  _hoisted_6,
                  _hoisted_7,
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Goals_Optional")) + " " + vue.toDisplayString(_ctx.translate("Goals_Ecommerce")) + ": ", 1),
                  vue.createElementVNode("span", {
                    innerHTML: _ctx.$sanitize(_ctx.youCanEnableEcommerceReports)
                  }, null, 8, _hoisted_8)
                ])) : vue.createCommentVNode("", true)
              ]),
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
                vue.createElementVNode("thead", null, [
                  vue.createElementVNode("tr", null, [
                    vue.createElementVNode("th", _hoisted_9, vue.toDisplayString(_ctx.translate("General_Id")), 1),
                    vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("Goals_GoalName")), 1),
                    vue.createElementVNode("th", _hoisted_10, vue.toDisplayString(_ctx.translate("General_Description")), 1),
                    vue.createElementVNode("th", _hoisted_11, vue.toDisplayString(_ctx.translate("Goals_GoalIsTriggeredWhen")), 1),
                    vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_ColumnRevenue")), 1),
                    _ctx.beforeGoalListActionsHeadComponent ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.beforeGoalListActionsHeadComponent), { key: 0 })) : vue.createCommentVNode("", true),
                    _ctx.userCanEditGoals ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_12, vue.toDisplayString(_ctx.translate("General_Actions")), 1)) : vue.createCommentVNode("", true)
                  ])
                ]),
                vue.createElementVNode("tbody", null, [
                  !Object.keys(_ctx.goals || {}).length ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_13, [
                    vue.createElementVNode("td", _hoisted_14, [
                      _hoisted_15,
                      vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Goals_ThereIsNoGoalToManage", _ctx.siteName)) + " ", 1),
                      _hoisted_16,
                      _hoisted_17
                    ])
                  ])) : vue.createCommentVNode("", true),
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.goals || [], (goal) => {
                    return vue.openBlock(), vue.createElementBlock("tr", {
                      id: goal.idgoal,
                      key: goal.idgoal
                    }, [
                      vue.createElementVNode("td", _hoisted_19, vue.toDisplayString(goal.idgoal), 1),
                      vue.createElementVNode("td", null, vue.toDisplayString(goal.name), 1),
                      vue.createElementVNode("td", _hoisted_20, vue.toDisplayString(goal.description), 1),
                      vue.createElementVNode("td", _hoisted_21, [
                        vue.createElementVNode("span", _hoisted_22, vue.toDisplayString(_ctx.goalMatchAttributeTranslations[goal.match_attribute] || goal.match_attribute), 1),
                        goal.match_attribute === "visit_duration" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_23, vue.toDisplayString(_ctx.lcfirst(_ctx.translate("General_OperationGreaterThan"))) + " " + vue.toDisplayString(_ctx.translate("Intl_NMinutes", goal.pattern)), 1)) : !!goal.pattern_type ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_24, [
                          _hoisted_25,
                          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Goals_Pattern")) + " " + vue.toDisplayString(goal.pattern_type) + ": " + vue.toDisplayString(goal.pattern), 1)
                        ])) : vue.createCommentVNode("", true)
                      ]),
                      vue.createElementVNode("td", {
                        class: "center",
                        innerHTML: _ctx.$sanitize(
                          goal.revenue === 0 || goal.revenue === "0" ? "-" : goal.revenue_pretty
                        )
                      }, null, 8, _hoisted_26),
                      _ctx.beforeGoalListActionsBodyComponent[goal.idgoal] ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(_ctx.beforeGoalListActionsBodyComponent[goal.idgoal]), { key: 0 })) : vue.createCommentVNode("", true),
                      _ctx.userCanEditGoals ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_27, [
                        vue.createElementVNode("a", {
                          class: "table-action icon-show",
                          href: _ctx.getGoalReportUrl(goal.idgoal),
                          title: _ctx.translate("Goals_ViewGoalReport"),
                          "aria-label": _ctx.translate("Goals_ViewGoalReport")
                        }, null, 8, _hoisted_28),
                        vue.createElementVNode("button", {
                          onClick: ($event) => _ctx.editGoal(goal.idgoal),
                          class: "table-action icon-edit",
                          title: _ctx.translate("General_Edit")
                        }, null, 8, _hoisted_29),
                        vue.createElementVNode("button", {
                          onClick: ($event) => _ctx.deleteGoal(goal.idgoal),
                          class: "table-action icon-delete",
                          title: _ctx.translate("General_Delete")
                        }, null, 8, _hoisted_30)
                      ])) : vue.createCommentVNode("", true)
                    ], 8, _hoisted_18);
                  }), 128))
                ])
              ])), [
                [_directive_content_table]
              ]),
              _ctx.userCanEditGoals && !_ctx.onlyShowAddNewGoal ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_31, [
                vue.createElementVNode("button", {
                  id: "add-goal",
                  onClick: _cache[0] || (_cache[0] = ($event) => _ctx.createGoal())
                }, [
                  _hoisted_32,
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Goals_AddNewGoal")), 1)
                ])
              ])) : vue.createCommentVNode("", true)
            ]),
            _: 1
          }, 8, ["content-title"])
        ], 512), [
          [vue.vShow, _ctx.showGoalList]
        ]),
        vue.createElementVNode("div", _hoisted_33, [
          vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("Goals_DeleteGoalConfirm", `"${(_a = _ctx.goalToDelete) == null ? void 0 : _a.name}"`)), 1),
          vue.createElementVNode("input", {
            role: "yes",
            type: "button",
            value: _ctx.translate("General_Yes")
          }, null, 8, _hoisted_34),
          vue.createElementVNode("input", {
            role: "no",
            type: "button",
            value: _ctx.translate("General_No")
          }, null, 8, _hoisted_35)
        ], 512)
      ], 512), [
        [vue.vShow, !_ctx.onlyShowAddNewGoal]
      ]),
      vue.withDirectives(vue.createElementVNode("div", null, [
        vue.withDirectives(vue.createElementVNode("div", _hoisted_36, [
          vue.createVNode(_component_ContentBlock, {
            "content-title": _ctx.goal.idgoal ? _ctx.translate("Goals_UpdateGoal") : _ctx.translate("Goals_AddNewGoal")
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("div", {
                innerHTML: _ctx.$sanitize(_ctx.addNewGoalIntro)
              }, null, 8, _hoisted_37),
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "text",
                    name: "goal_name",
                    modelValue: _ctx.goal.name,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.goal.name = $event),
                    maxlength: 50,
                    autocomplete: "off",
                    title: _ctx.translate("Goals_GoalName"),
                    placeholder: _ctx.translate("Goals_GoalNamePlaceholder"),
                    "inline-help": _ctx.translate("Goals_GoalNameHelpText"),
                    onChange: _ctx.goalNameChanged
                  }, null, 8, ["modelValue", "title", "placeholder", "inline-help", "onChange"])
                ]),
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "textarea",
                    name: "goal_description",
                    modelValue: _ctx.goal.description,
                    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.goal.description = $event),
                    maxlength: 255,
                    autocomplete: "off",
                    title: `${_ctx.translate("General_Description")} ${_ctx.translate("Goals_Optional")}`,
                    placeholder: _ctx.translate("Goals_GoalDescriptionPlaceholder"),
                    "inline-help": _ctx.translate("Goals_GoalDescriptionHelpText"),
                    "ui-control-attributes": { class: "compact-textarea" }
                  }, null, 8, ["modelValue", "title", "placeholder", "inline-help"])
                ]),
                vue.createElementVNode("div", _hoisted_38, [
                  vue.createElementVNode("div", _hoisted_39, [
                    vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Goals_GoalIsTriggered")), 1)
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_40, [
                  vue.createElementVNode("div", _hoisted_41, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        uicontrol: "select",
                        name: "trigger_type",
                        "model-value": _ctx.triggerType,
                        "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
                          _ctx.triggerType = $event;
                          _ctx.changedTriggerType();
                        }),
                        "full-width": true,
                        options: _ctx.goalTriggerTypeOptions
                      }, null, 8, ["model-value", "options"])
                    ])
                  ]),
                  vue.createElementVNode("div", _hoisted_42, [
                    vue.withDirectives(vue.createVNode(_component_Alert, { severity: "info" }, {
                      default: vue.withCtx(() => [
                        vue.createElementVNode("span", {
                          innerHTML: _ctx.$sanitize(_ctx.whereVisitedPageManuallyCallsJsTrackerText)
                        }, null, 8, _hoisted_43)
                      ]),
                      _: 1
                    }, 512), [
                      [vue.vShow, _ctx.triggerType === "manually"]
                    ]),
                    vue.createElementVNode("div", null, [
                      vue.withDirectives(vue.createVNode(_component_Field, {
                        uicontrol: "radio",
                        name: "match_attribute",
                        "full-width": true,
                        "model-value": _ctx.goal.match_attribute,
                        "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => {
                          _ctx.goal.match_attribute = $event;
                          _ctx.initPatternType();
                        }),
                        options: _ctx.goalMatchAttributeOptions
                      }, null, 8, ["model-value", "options"]), [
                        [vue.vShow, _ctx.triggerType !== "manually"]
                      ])
                    ])
                  ])
                ]),
                vue.withDirectives(vue.createElementVNode("div", _hoisted_44, [
                  vue.createElementVNode("h3", _hoisted_45, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate("Goals_WhereThe")) + " ", 1),
                    vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Goals_URL")), 513), [
                      [vue.vShow, _ctx.goal.match_attribute === "url"]
                    ]),
                    vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Goals_PageTitle")), 513), [
                      [vue.vShow, _ctx.goal.match_attribute === "title"]
                    ]),
                    vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Goals_Filename")), 513), [
                      [vue.vShow, _ctx.goal.match_attribute === "file"]
                    ]),
                    vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Goals_ExternalWebsiteUrl")), 513), [
                      [vue.vShow, _ctx.goal.match_attribute === "external_website"]
                    ]),
                    vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("Goals_VisitDuration")), 513), [
                      [vue.vShow, _ctx.goal.match_attribute === "visit_duration"]
                    ])
                  ])
                ], 512), [
                  [vue.vShow, _ctx.triggerType !== "manually"]
                ]),
                vue.withDirectives(vue.createElementVNode("div", _hoisted_46, [
                  vue.withDirectives(vue.createElementVNode("div", _hoisted_47, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        uicontrol: "select",
                        name: "event_type",
                        modelValue: _ctx.eventType,
                        "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.eventType = $event),
                        "full-width": true,
                        options: _ctx.eventTypeOptions
                      }, null, 8, ["modelValue", "options"])
                    ])
                  ], 512), [
                    [vue.vShow, _ctx.goal.match_attribute === "event"]
                  ]),
                  !_ctx.isMatchAttributeNumeric ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_48, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        uicontrol: "select",
                        name: "pattern_type",
                        modelValue: _ctx.goal.pattern_type,
                        "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.goal.pattern_type = $event),
                        "full-width": true,
                        options: _ctx.patternTypeOptions
                      }, null, 8, ["modelValue", "options"])
                    ])
                  ])) : vue.createCommentVNode("", true),
                  _ctx.isMatchAttributeNumeric ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_49, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        uicontrol: "select",
                        name: "pattern_type",
                        modelValue: _ctx.goal.pattern_type,
                        "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.goal.pattern_type = $event),
                        "full-width": true,
                        options: _ctx.numericComparisonTypeOptions
                      }, null, 8, ["modelValue", "options"])
                    ])
                  ])) : vue.createCommentVNode("", true),
                  vue.createElementVNode("div", _hoisted_50, [
                    vue.createElementVNode("div", null, [
                      vue.createVNode(_component_Field, {
                        uicontrol: "text",
                        name: "pattern",
                        modelValue: _ctx.goal.pattern,
                        "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.goal.pattern = $event),
                        maxlength: 255,
                        autocomplete: "off",
                        title: _ctx.patternFieldLabel,
                        "full-width": true,
                        "error-message": _ctx.patternMissing ? _ctx.translate("General_PleaseSpecifyValue", "pattern") : ""
                      }, null, 8, ["modelValue", "title", "error-message"])
                    ])
                  ]),
                  vue.createElementVNode("div", _hoisted_51, [
                    vue.createVNode(_component_Alert, { severity: "info" }, {
                      default: vue.withCtx(() => [
                        vue.withDirectives(vue.createElementVNode("span", null, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_Contains", "'checkout/confirmation'")) + " ", 1),
                          _hoisted_52,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_IsExactly", "'http://example.com/thank-you.html'")) + " ", 1),
                          _hoisted_53,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_MatchesExpression", "'(.*)\\/demo\\/(.*)'")), 1)
                        ], 512), [
                          [vue.vShow, _ctx.goal.match_attribute === "url"]
                        ]),
                        vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_Contains", "'Order confirmation'")), 513), [
                          [vue.vShow, _ctx.goal.match_attribute === "title"]
                        ]),
                        vue.withDirectives(vue.createElementVNode("span", null, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_Contains", "'files/brochure.pdf'")) + " ", 1),
                          _hoisted_54,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_IsExactly", "'http://example.com/files/brochure.pdf'")) + " ", 1),
                          _hoisted_55,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_MatchesExpression", "'(.*)\\.zip'")), 1)
                        ], 512), [
                          [vue.vShow, _ctx.goal.match_attribute === "file"]
                        ]),
                        vue.withDirectives(vue.createElementVNode("span", null, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_Contains", "'amazon.com'")) + " ", 1),
                          _hoisted_56,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_IsExactly", "'http://mypartner.com/landing.html'")) + " ", 1),
                          _hoisted_57,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.matchesExpressionExternal), 1)
                        ], 512), [
                          [vue.vShow, _ctx.goal.match_attribute === "external_website"]
                        ]),
                        vue.withDirectives(vue.createElementVNode("span", null, [
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_Contains", "'video'")) + " ", 1),
                          _hoisted_58,
                          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_IsExactly", "'click'")) + " ", 1),
                          _hoisted_59,
                          vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_MatchesExpression", "'(.*)_banner'")) + '" ', 1)
                        ], 512), [
                          [vue.vShow, _ctx.goal.match_attribute === "event"]
                        ]),
                        vue.withDirectives(vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate("General_ForExampleShort")) + " " + vue.toDisplayString(_ctx.translate("Goals_AtLeastMinutes", "5", "0.5")), 513), [
                          [vue.vShow, _ctx.goal.match_attribute === "visit_duration"]
                        ])
                      ]),
                      _: 1
                    })
                  ])
                ], 512), [
                  [vue.vShow, _ctx.triggerType !== "manually"]
                ]),
                vue.createElementVNode("div", null, [
                  vue.withDirectives(vue.createVNode(_component_Field, {
                    uicontrol: "checkbox",
                    name: "case_sensitive",
                    modelValue: _ctx.goal.case_sensitive,
                    "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.goal.case_sensitive = $event),
                    title: _ctx.caseSensitiveTitle
                  }, null, 8, ["modelValue", "title"]), [
                    [vue.vShow, _ctx.triggerType !== "manually" && !_ctx.isMatchAttributeNumeric]
                  ])
                ]),
                vue.createElementVNode("div", null, [
                  _ctx.goal.match_attribute !== "visit_duration" ? (vue.openBlock(), vue.createBlock(_component_Field, {
                    key: 0,
                    uicontrol: "radio",
                    name: "allow_multiple",
                    "model-value": !!_ctx.goal.allow_multiple && _ctx.goal.allow_multiple !== "0" ? 1 : 0,
                    "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => _ctx.goal.allow_multiple = $event),
                    options: _ctx.allowMultipleOptions,
                    introduction: _ctx.translate("Goals_AllowMultipleConversionsPerVisit"),
                    "inline-help": _ctx.translate("Goals_HelpOneConversionPerVisit")
                  }, null, 8, ["model-value", "options", "introduction", "inline-help"])) : vue.createCommentVNode("", true)
                ]),
                vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("Goals_GoalRevenue")) + " " + vue.toDisplayString(_ctx.translate("Goals_Optional")), 1),
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "number",
                    name: "revenue",
                    modelValue: _ctx.goal.revenue,
                    "onUpdate:modelValue": _cache[11] || (_cache[11] = ($event) => _ctx.goal.revenue = $event),
                    placeholder: _ctx.translate("Goals_DefaultRevenueLabel"),
                    "inline-help": _ctx.translate("Goals_DefaultRevenueHelp")
                  }, null, 8, ["modelValue", "placeholder", "inline-help"])
                ]),
                vue.createElementVNode("div", null, [
                  vue.withDirectives(vue.createVNode(_component_Field, {
                    uicontrol: "checkbox",
                    name: "use_event_value",
                    modelValue: _ctx.goal.event_value_as_revenue,
                    "onUpdate:modelValue": _cache[12] || (_cache[12] = ($event) => _ctx.goal.event_value_as_revenue = $event),
                    title: _ctx.translate("Goals_UseEventValueAsRevenue"),
                    "inline-help": _ctx.useEventValueAsRevenueHelp
                  }, null, 8, ["modelValue", "title", "inline-help"]), [
                    [vue.vShow, _ctx.goal.match_attribute === "event"]
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_60, [
                  _ctx.endEditTable ? (vue.openBlock(), vue.createBlock(_component_VueEntryContainer, {
                    key: 0,
                    html: _ctx.endEditTable
                  }, null, 8, ["html"])) : vue.createCommentVNode("", true)
                ], 512),
                _hoisted_61,
                vue.createVNode(_component_SaveButton, {
                  saving: _ctx.isLoading,
                  onConfirm: _cache[13] || (_cache[13] = ($event) => _ctx.save()),
                  value: _ctx.submitText
                }, null, 8, ["saving", "value"]),
                !_ctx.onlyShowAddNewGoal ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_62, [
                  vue.withDirectives(vue.createElementVNode("div", {
                    class: "entityCancel",
                    onClick: _cache[14] || (_cache[14] = ($event) => _ctx.showListOfReports()),
                    innerHTML: _ctx.$sanitize(_ctx.cancelText)
                  }, null, 8, _hoisted_63), [
                    [vue.vShow, _ctx.showEditGoal]
                  ])
                ])) : vue.createCommentVNode("", true)
              ])), [
                [_directive_form]
              ])
            ]),
            _: 1
          }, 8, ["content-title"])
        ], 512), [
          [vue.vShow, _ctx.showEditGoal]
        ])
      ], 512), [
        [vue.vShow, _ctx.userCanEditGoals]
      ]),
      _hoisted_64
    ]);
  }
  const ManageGoals = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.GoalPageLink = GoalPageLink;
  exports2.ManageGoals = ManageGoals;
  exports2.ManageGoalsStore = ManageGoalsStore$1;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
