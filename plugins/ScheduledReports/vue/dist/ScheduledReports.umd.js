(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CorePluginsAdmin"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CorePluginsAdmin", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.ScheduledReports = {}, global.Vue, global.CorePluginsAdmin, global.CoreHome));
})(this, (function(exports2, vue, CorePluginsAdmin, CoreHome) {
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

  const _sfc_main$3 = vue.defineComponent({
    props: {
      report: {
        type: Object,
        required: true
      },
      reportType: {
        type: String,
        required: true
      },
      defaultDisplayFormat: {
        type: Number,
        required: true
      },
      defaultEmailMe: {
        type: Boolean,
        required: true
      },
      defaultEvolutionGraph: {
        type: Boolean,
        required: true
      },
      currentUserEmail: {
        type: String,
        required: true
      }
    },
    emits: ["change"],
    components: {
      Field: CorePluginsAdmin.Field
    },
    setup(props) {
      const {
        resetReportParametersFunctions,
        updateReportParametersFunctions,
        getReportParametersFunctions
      } = window;
      if (!resetReportParametersFunctions[props.reportType]) {
        resetReportParametersFunctions[props.reportType] = (theReport) => {
          theReport.displayFormat = props.defaultDisplayFormat;
          theReport.emailMe = props.defaultEmailMe;
          theReport.evolutionGraph = props.defaultEvolutionGraph;
          theReport.additionalEmails = [];
        };
      }
      if (!updateReportParametersFunctions[props.reportType]) {
        updateReportParametersFunctions[props.reportType] = (theReport) => {
          if (!(theReport == null ? void 0 : theReport.parameters)) {
            return;
          }
          ["displayFormat", "emailMe", "evolutionGraph", "additionalEmails"].forEach((field) => {
            if (field in theReport.parameters) {
              theReport[field] = theReport.parameters[field];
            }
          });
        };
      }
      if (!getReportParametersFunctions[props.reportType]) {
        getReportParametersFunctions[props.reportType] = (theReport) => ({
          displayFormat: theReport.displayFormat,
          emailMe: theReport.emailMe,
          evolutionGraph: theReport.evolutionGraph,
          additionalEmails: theReport.additionalEmails || [],
          enforceOrder: true
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
  const _hoisted_1$3 = { key: 0 };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    return _ctx.report ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      vue.createElementVNode("div", null, [
        vue.withDirectives(vue.createVNode(_component_Field, {
          uicontrol: "checkbox",
          name: "report_email_me",
          introduction: _ctx.translate("ScheduledReports_SendReportTo"),
          "model-value": _ctx.report.emailMe,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("change", "emailMe", $event)),
          title: `${_ctx.translate("ScheduledReports_SentToMe")} (${_ctx.currentUserEmail})`
        }, null, 8, ["introduction", "model-value", "title"]), [
          [vue.vShow, _ctx.report.type === "email"]
        ])
      ]),
      vue.createElementVNode("div", null, [
        vue.withDirectives(vue.createVNode(_component_Field, {
          uicontrol: "textarea",
          "var-type": "array",
          "model-value": _ctx.report.additionalEmails,
          "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.$emit("change", "additionalEmails", $event)),
          title: _ctx.translate("ScheduledReports_AlsoSendReportToTheseEmails")
        }, null, 8, ["model-value", "title"]), [
          [vue.vShow, _ctx.report.type === "email"]
        ])
      ])
    ])) : vue.createCommentVNode("", true);
  }
  const ReportParameters = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function adjustHourToTimezone(hour, difference) {
    return `${(24 + parseFloat(hour) + difference) % 24}`;
  }
  const _sfc_main$2 = vue.defineComponent({
    props: {
      report: {
        type: Object,
        required: true
      },
      selectedReports: Object,
      selectedReportsOrder: {
        type: Object,
        default: () => ({})
      },
      paramPeriods: {
        type: Object,
        required: true
      },
      reportTypeOptions: {
        type: Object,
        required: true
      },
      reportFormatsByReportTypeOptions: {
        type: Object,
        required: true
      },
      displayFormats: {
        type: Object,
        required: true
      },
      reportsByCategoryByReportType: {
        type: Object,
        required: true
      },
      allowMultipleReportsByReportType: {
        type: Object,
        required: true
      },
      countWebsites: {
        type: Number,
        required: true
      },
      siteName: {
        type: String,
        required: true
      },
      reportTypes: {
        type: Object,
        required: true
      },
      segmentEditorActivated: Boolean,
      savedSegmentsById: Object,
      periods: {
        type: Object,
        required: true
      },
      validationErrors: {
        type: Object,
        default: () => ({
          name: false,
          reports: false
        })
      }
    },
    emits: ["submit", "change", "toggleSelectedReport", "reorderSelectedReports"],
    components: {
      ContentBlock: CoreHome.ContentBlock,
      DraggableList: CoreHome.DraggableList,
      Field: CorePluginsAdmin.Field,
      SaveButton: CorePluginsAdmin.SaveButton
    },
    directives: {
      Form: CorePluginsAdmin.Form
    },
    created() {
      this.onEvolutionPeriodN = CoreHome.debounce(this.onEvolutionPeriodN, 50);
    },
    methods: {
      onEvolutionPeriodN(event) {
        this.$emit("change", {
          prop: "evolutionPeriodN",
          value: event.target.value
        });
      },
      decode(s) {
        return CoreHome.Matomo.helper.htmlDecode(s);
      },
      onSelectedReportsReorder(order) {
        if (!this.report || !this.report.type) {
          return;
        }
        this.$emit("reorderSelectedReports", {
          reportType: this.report.type,
          order
        });
      }
    },
    setup(props, ctx) {
      const reportParameters = vue.ref(null);
      vue.watch(() => props.report, (newValue) => {
        const reportParametersElement = reportParameters.value;
        reportParametersElement.querySelectorAll("[vue-entry]").forEach((node) => {
          $(node).data("vueAppInstance").report_ = newValue;
        });
      });
      vue.onMounted(() => {
        const reportParametersElement = reportParameters.value;
        CoreHome.Matomo.helper.compileVueEntryComponents(reportParametersElement, {
          report: props.report,
          onChange(prop, value) {
            ctx.emit("change", { prop, value });
          }
        });
      });
      return {
        reportParameters
      };
    },
    beforeUnmount() {
      const reportParameters = this.$refs.reportParameters;
      CoreHome.Matomo.helper.destroyVueComponent(reportParameters);
    },
    computed: {
      enforceSelectedReportOrder() {
        var _a2;
        const parameters = ((_a2 = this.report) == null ? void 0 : _a2.parameters) || {};
        if (typeof parameters.enforceOrder !== "undefined") {
          return !!parameters.enforceOrder;
        }
        return false;
      },
      /**
       * Ensures each report type has a flattened order array where every selected report
       * appears exactly once (ordered first, then any remaining selections).
       */
      selectedReportsOrderNormalized() {
        const normalized = {};
        const allSelectedReports = this.selectedReports || {};
        Object.keys(allSelectedReports).forEach((reportType) => {
          const selectedForType = allSelectedReports[reportType] || {};
          const ordered = ((this.selectedReportsOrder || {})[reportType] || []).filter((uniqueId) => selectedForType[uniqueId]);
          const remaining = Object.keys(selectedForType).filter(
            (uniqueId) => selectedForType[uniqueId] && ordered.indexOf(uniqueId) === -1
          );
          normalized[reportType] = ordered.concat(remaining);
        });
        return normalized;
      },
      /**
       * Flattens the nested report metadata into a two-level lookup so we can access any report
       * by its type and unique id without re-iterating the category structure.
       */
      reportsLookup() {
        const reportsByType = this.reportsByCategoryByReportType;
        const lookup = {};
        Object.entries(reportsByType).forEach(([reportType, reportsByCategory]) => {
          lookup[reportType] = lookup[reportType] || {};
          Object.values(reportsByCategory).forEach((reports) => {
            reports.forEach((report) => {
              lookup[reportType][report.uniqueId] = report;
            });
          });
        });
        return lookup;
      },
      selectedReportsForCurrentType() {
        var _a2;
        const type = (_a2 = this.report) == null ? void 0 : _a2.type;
        if (!type) {
          return [];
        }
        const selectedForType = (this.selectedReports || {})[type] || {};
        let order = [];
        if (this.enforceSelectedReportOrder) {
          order = this.selectedReportsOrderNormalized[type] || [];
        } else {
          const reportsByCategory = this.reportsByCategoryByReportType[type] || {};
          const ordered = [];
          Object.values(reportsByCategory).forEach((reports) => {
            reports.forEach((report) => {
              if (selectedForType[report.uniqueId]) {
                ordered.push(report.uniqueId);
              }
            });
          });
          order = ordered;
        }
        if (!order.length) {
          order = Object.keys(selectedForType).filter((uniqueId) => selectedForType[uniqueId]);
        }
        if (!order.length) {
          return [];
        }
        const lookup = this.reportsLookup[type] || {};
        return order.map((uniqueId) => lookup[uniqueId]).filter((report) => !!report);
      },
      reportsByCategoryByReportTypeInColumns() {
        const reportsByCategoryByReportType = this.reportsByCategoryByReportType;
        const inColumns = Object.entries(reportsByCategoryByReportType).map(
          ([key, reportsByCategory]) => {
            const newColumnAfter = Math.floor((Object.keys(reportsByCategory).length + 1) / 2);
            const column1 = {};
            const column2 = {};
            let currentColumn = column1;
            Object.entries(reportsByCategory).forEach(([category, reports]) => {
              currentColumn[category] = reports;
              if (Object.keys(currentColumn).length >= newColumnAfter) {
                currentColumn = column2;
              }
            });
            return [key, [column1, column2]];
          }
        );
        return Object.fromEntries(inColumns);
      },
      entityCancelText() {
        return CoreHome.translate(
          "General_OrCancel",
          '<a class="entityCancelLink">',
          "</a>"
        );
      },
      frequencyPeriodSingle() {
        if (!this.report || !this.report.period) {
          return "";
        }
        const { ReportPlugin } = window;
        let translation = ReportPlugin.periodTranslations[this.report.period];
        if (!translation) {
          translation = ReportPlugin.periodTranslations.day;
        }
        return translation.single;
      },
      frequencyPeriodPlural() {
        if (!this.report || !this.report.period) {
          return "";
        }
        const { ReportPlugin } = window;
        let translation = ReportPlugin.periodTranslations[this.report.period];
        if (!translation) {
          translation = ReportPlugin.periodTranslations.day;
        }
        return translation.plural;
      },
      evolutionGraphsShowForEachInPeriod() {
        return CoreHome.translate(
          "ScheduledReports_EvolutionGraphsShowForEachInPeriod",
          "<strong>",
          "</strong>",
          this.frequencyPeriodSingle
        );
      },
      reportSegmentInlineHelp() {
        const segmentManagementPageUrl = `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), {
          module: "CoreHome",
          action: "index",
          category: "General_Visitors",
          subcategory: "CoreHome_Segments"
        }))}`;
        return CoreHome.translate(
          "ScheduledReports_HelpSegmentManagement",
          `<a href="${segmentManagementPageUrl}" rel="noreferrer noopener" target="_blank">`,
          "</a>"
        );
      },
      timezoneOffset() {
        return CoreHome.Matomo.timezoneOffset;
      },
      timeZoneDifferenceInHours() {
        return CoreHome.Matomo.timezoneOffset / 3600;
      },
      reportHours() {
        const hours = [];
        const fractionalOffset = (this.timeZoneDifferenceInHours % 1 + 1) % 1;
        const minutePart = Math.round(fractionalOffset * 60);
        const minuteLabel = `${minutePart}`.padStart(2, "0");
        for (let i = 0; i < 24; i += 1) {
          const paddedHour = `${i}`.padStart(2, "0");
          const key = fractionalOffset === 0 ? `${i}` : `${i + fractionalOffset}`;
          const value = fractionalOffset === 0 ? `${paddedHour}:00` : `${paddedHour}:${minuteLabel}`;
          hours.push({
            key,
            value
          });
        }
        return hours;
      },
      reportHourUtc() {
        const reportHour = adjustHourToTimezone(
          this.report.hour,
          -this.timeZoneDifferenceInHours
        );
        const normalized = (parseFloat(reportHour) % 24 + 24) % 24;
        const roundedHour = Math.round(normalized) % 24;
        return `${roundedHour}`.padStart(2, "0");
      },
      reportHourUtcLabel() {
        return CoreHome.translate("ScheduledReports_ReportHourWithUtcOnly", [`${this.reportHourUtc}:00`]);
      },
      reportHourUtcHelpText() {
        return `${CoreHome.translate("ScheduledReports_ReportWillBeSentAt")} ${CoreHome.translate("ScheduledReports_ReportHourEqualsUtc", [this.reportHourUtcLabel])} ${CoreHome.translate("ScheduledReports_NoteDeliveryTime")}`;
      },
      saveButtonTitle() {
        const { ReportPlugin } = window;
        const isEditing = this.report.idreport > 0;
        return isEditing ? ReportPlugin.updateReportString : ReportPlugin.createReportString;
      },
      contentTitle() {
        const { ReportPlugin } = window;
        const isEditing = this.report.idreport > 0;
        return isEditing ? ReportPlugin.updateReportString : CoreHome.translate("ScheduledReports_CreateAndScheduleReport");
      },
      getDeliveryMediumInlineTooltip() {
        const link = CoreHome.translate(
          "CoreHome_LearnMoreFullStop",
          CoreHome.externalLink("https://matomo.org/faq/general/create-and-schedule-a-report/"),
          "</a>"
        );
        return `${CoreHome.translate("ScheduledReports_CreateTooltip")} ${link}`;
      }
    }
  });
  const _hoisted_1$2 = { key: 0 };
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = {
    id: "emailScheduleInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_4$2 = {
    id: "emailReportPeriodInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_5$2 = {
    key: 0,
    id: "reportHourHelpText",
    class: "inline-help-node"
  };
  const _hoisted_6$2 = ["textContent"];
  const _hoisted_7$1 = {
    id: "deliveryMediumnInlineHelp",
    class: "inline-help-node"
  };
  const _hoisted_8$1 = ["innerHTML"];
  const _hoisted_9$1 = { ref: "reportParameters" };
  const _hoisted_10$1 = { class: "report_evolution_graph" };
  const _hoisted_11$1 = { class: "row evolution-graph-period" };
  const _hoisted_12$1 = { class: "col s12" };
  const _hoisted_13$1 = { for: "report_evolution_period_for_each" };
  const _hoisted_14$1 = ["checked"];
  const _hoisted_15$1 = ["innerHTML"];
  const _hoisted_16$1 = { class: "col s12" };
  const _hoisted_17$1 = { for: "report_evolution_period_for_prev" };
  const _hoisted_18$1 = ["checked"];
  const _hoisted_19$1 = ["value"];
  const _hoisted_20$1 = { class: "row" };
  const _hoisted_21$1 = {
    id: "scheduled-reports-selection-heading",
    class: "col s12"
  };
  const _hoisted_22$1 = { class: "reportCategory" };
  const _hoisted_23$1 = { class: "listReports" };
  const _hoisted_24$1 = ["name", "type", "id", "checked", "onChange"];
  const _hoisted_25$1 = {
    key: 0,
    class: "entityInlineHelp"
  };
  const _hoisted_26$1 = {
    key: 1,
    class: "draggableListPanel selectedReportsWrapper"
  };
  const _hoisted_27$1 = { class: "draggableListHeading selectedReportsHeading" };
  const _hoisted_28$1 = { class: "draggableListHelp selectedReportsHelp" };
  const _hoisted_29$1 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _component_DraggableList = vue.resolveComponent("DraggableList");
    const _component_SaveButton = vue.resolveComponent("SaveButton");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_form = vue.resolveDirective("form");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      class: "entityAddContainer",
      "content-title": _ctx.contentTitle
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("ScheduledReports_CreateTooltip")), 1),
        _cache[20] || (_cache[20] = vue.createElementVNode("div", { class: "clear" }, null, -1)),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("form", {
          id: "addEditReport",
          onSubmit: _cache[14] || (_cache[14] = ($event) => _ctx.$emit("submit"))
        }, [
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "website",
              title: _ctx.translate("General_Website"),
              disabled: true,
              "model-value": _ctx.siteName
            }, null, 8, ["title", "model-value"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "text",
              name: "report_description",
              title: _ctx.translate("General_Name"),
              "model-value": _ctx.report.description,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("change", { prop: "description", value: $event })),
              placeholder: _ctx.translate("ScheduledReports_ReportNamePlaceholder"),
              "inline-help": _ctx.translate("ScheduledReports_ReportNameHelpText"),
              "error-message": _ctx.validationErrors.name ? _ctx.translate("ScheduledReports_ReportMissingName", "", "") : ""
            }, null, 8, ["title", "model-value", "placeholder", "inline-help", "error-message"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "textarea",
              name: "report_custom_description",
              title: `${_ctx.translate("General_Description")} ${_ctx.translate("Goals_Optional")}`,
              "model-value": _ctx.report.reportDescription,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.$emit("change", { prop: "reportDescription", value: $event })),
              placeholder: _ctx.translate("ScheduledReports_ReportDescriptionPlaceholder"),
              "inline-help": _ctx.translate("ScheduledReports_ReportDescriptionHelpText"),
              "ui-control-attributes": { class: "compact-textarea" }
            }, null, 8, ["title", "model-value", "placeholder", "inline-help"])
          ]),
          _ctx.segmentEditorActivated ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "report_segment",
              title: _ctx.translate("SegmentEditor_ChooseASegment"),
              "model-value": _ctx.report.idsegment,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.$emit("change", { prop: "idsegment", value: $event })),
              options: _ctx.savedSegmentsById
            }, {
              "inline-help": vue.withCtx(() => [
                _ctx.segmentEditorActivated ? (vue.openBlock(), vue.createElementBlock("div", {
                  key: 0,
                  id: "reportSegmentInlineHelp",
                  class: "inline-help-node",
                  innerHTML: _ctx.$sanitize(_ctx.reportSegmentInlineHelp)
                }, null, 8, _hoisted_2$2)) : vue.createCommentVNode("", true)
              ]),
              _: 1
            }, 8, ["title", "model-value", "options"])
          ])) : vue.createCommentVNode("", true),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "report_schedule",
              "model-value": _ctx.report.period,
              "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => {
                _ctx.$emit("change", { prop: "period", value: $event });
                _ctx.$emit("change", {
                  prop: "periodParam",
                  value: _ctx.report.period === "never" ? null : _ctx.report.period
                });
              }),
              title: _ctx.translate("ScheduledReports_ReportSchedule"),
              options: _ctx.periods
            }, {
              "inline-help": vue.withCtx(() => [
                vue.createElementVNode("div", _hoisted_3$2, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("ScheduledReports_WeeklyScheduleHelp")) + " ", 1),
                  _cache[15] || (_cache[15] = vue.createElementVNode("br", null, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_MonthlyScheduleHelp")), 1)
                ])
              ]),
              _: 1
            }, 8, ["model-value", "title", "options"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "report_period",
              "model-value": _ctx.report.periodParam,
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.$emit("change", { prop: "periodParam", value: $event })),
              options: _ctx.paramPeriods,
              title: _ctx.translate("ScheduledReports_ReportPeriod")
            }, {
              "inline-help": vue.withCtx(() => [
                vue.createElementVNode("div", _hoisted_4$2, [
                  vue.createTextVNode(vue.toDisplayString(_ctx.translate("ScheduledReports_ScheduleReportPeriodHelp")) + " ", 1),
                  _cache[16] || (_cache[16] = vue.createElementVNode("br", null, null, -1)),
                  _cache[17] || (_cache[17] = vue.createElementVNode("br", null, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_ScheduleReportPeriodHelp2")), 1)
                ])
              ]),
              _: 1
            }, 8, ["model-value", "options", "title"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "report_hour",
              "model-value": _ctx.report.hour,
              "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.$emit("change", { prop: "hour", value: $event })),
              title: _ctx.translate("ScheduledReports_ReportHourLocal"),
              options: _ctx.reportHours
            }, {
              "inline-help": vue.withCtx(() => [
                String(_ctx.timezoneOffset) !== "0" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$2, [
                  vue.createElementVNode("span", {
                    textContent: vue.toDisplayString(_ctx.reportHourUtcHelpText)
                  }, null, 8, _hoisted_6$2)
                ])) : vue.createCommentVNode("", true)
              ]),
              _: 1
            }, 8, ["model-value", "title", "options"])
          ]),
          vue.createElementVNode("div", null, [
            vue.createVNode(_component_Field, {
              uicontrol: "select",
              name: "report_type",
              disabled: _ctx.reportTypes.length === 1,
              "model-value": _ctx.report.type,
              "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.$emit("change", { prop: "type", value: $event })),
              title: _ctx.translate("ScheduledReports_ReportType"),
              options: _ctx.reportTypeOptions
            }, {
              "inline-help": vue.withCtx(() => [
                vue.createElementVNode("div", _hoisted_7$1, [
                  vue.createElementVNode("span", {
                    innerHTML: _ctx.$sanitize(_ctx.getDeliveryMediumInlineTooltip)
                  }, null, 8, _hoisted_8$1)
                ])
              ]),
              _: 1
            }, 8, ["disabled", "model-value", "title", "options"])
          ]),
          vue.createElementVNode("div", _hoisted_9$1, [
            vue.renderSlot(_ctx.$slots, "report-parameters")
          ], 512),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.reportFormatsByReportTypeOptions, (reportFormats, reportType) => {
            return vue.openBlock(), vue.createElementBlock("div", { key: reportType }, [
              vue.withDirectives(vue.createVNode(_component_Field, {
                uicontrol: "select",
                name: "report_format",
                title: _ctx.translate("ScheduledReports_ReportFormat"),
                class: vue.normalizeClass(reportType),
                "model-value": _ctx.report[`format${reportType}`],
                "onUpdate:modelValue": ($event) => _ctx.$emit("change", { prop: `format${reportType}`, value: $event }),
                options: reportFormats
              }, null, 8, ["title", "class", "model-value", "onUpdate:modelValue", "options"]), [
                [vue.vShow, _ctx.report.type === reportType]
              ])
            ]);
          }), 128)),
          vue.withDirectives(vue.createElementVNode("div", null, [
            vue.createElementVNode("div", {
              class: vue.normalizeClass(_ctx.report.type)
            }, [
              vue.createVNode(_component_Field, {
                uicontrol: "select",
                name: "display_format",
                "model-value": _ctx.report.displayFormat,
                "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.$emit("change", { prop: "displayFormat", value: $event })),
                options: _ctx.displayFormats,
                introduction: _ctx.translate("ScheduledReports_AggregateReportsFormat")
              }, null, 8, ["model-value", "options", "introduction"])
            ], 2),
            vue.createElementVNode("div", _hoisted_10$1, [
              vue.withDirectives(vue.createVNode(_component_Field, {
                uicontrol: "checkbox",
                name: "report_evolution_graph",
                title: _ctx.translate("ScheduledReports_EvolutionGraph", "5"),
                "model-value": _ctx.report.evolutionGraph,
                "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.$emit("change", { prop: "evolutionGraph", value: $event }))
              }, null, 8, ["title", "model-value"]), [
                [vue.vShow, [2, "2", 3, "3"].indexOf(_ctx.report.displayFormat) !== -1]
              ])
            ]),
            vue.withDirectives(vue.createElementVNode("div", _hoisted_11$1, [
              vue.createElementVNode("div", _hoisted_12$1, [
                vue.createElementVNode("label", _hoisted_13$1, [
                  vue.createElementVNode("input", {
                    id: "report_evolution_period_for_each",
                    name: "report_evolution_period_for",
                    type: "radio",
                    value: "each",
                    checked: _ctx.report.evolutionPeriodFor === "each",
                    onChange: _cache[9] || (_cache[9] = ($event) => _ctx.$emit(
                      "change",
                      { prop: "evolutionPeriodFor", value: $event.target.value }
                    ))
                  }, null, 40, _hoisted_14$1),
                  vue.createElementVNode("span", {
                    innerHTML: _ctx.$sanitize(_ctx.evolutionGraphsShowForEachInPeriod)
                  }, null, 8, _hoisted_15$1)
                ])
              ]),
              vue.createElementVNode("div", _hoisted_16$1, [
                vue.createElementVNode("label", _hoisted_17$1, [
                  vue.createElementVNode("input", {
                    id: "report_evolution_period_for_prev",
                    name: "report_evolution_period_for",
                    type: "radio",
                    value: "prev",
                    checked: _ctx.report.evolutionPeriodFor === "prev",
                    onChange: _cache[10] || (_cache[10] = ($event) => _ctx.$emit(
                      "change",
                      { prop: "evolutionPeriodFor", value: $event.target.value }
                    ))
                  }, null, 40, _hoisted_18$1),
                  vue.createElementVNode("span", null, [
                    vue.createTextVNode(vue.toDisplayString(_ctx.translate(
                      "ScheduledReports_EvolutionGraphsShowForPreviousN",
                      _ctx.frequencyPeriodPlural
                    )) + ": ", 1),
                    vue.createElementVNode("input", {
                      type: "number",
                      name: "report_evolution_period_n",
                      value: _ctx.report.evolutionPeriodN,
                      onKeydown: _cache[11] || (_cache[11] = ($event) => _ctx.onEvolutionPeriodN($event)),
                      onChange: _cache[12] || (_cache[12] = ($event) => _ctx.onEvolutionPeriodN($event))
                    }, null, 40, _hoisted_19$1)
                  ])
                ])
              ])
            ], 512), [
              [vue.vShow, [1, "1", 2, "2", 3, "3"].indexOf(_ctx.report.displayFormat) !== -1]
            ])
          ], 512), [
            [
              vue.vShow,
              _ctx.report[`format${_ctx.report.type}`] === "pdf" || _ctx.report[`format${_ctx.report.type}`] === "html"
            ]
          ]),
          vue.createElementVNode("div", _hoisted_20$1, [
            vue.createElementVNode("h3", _hoisted_21$1, vue.toDisplayString(_ctx.translate("ScheduledReports_ReportsIncluded")), 1),
            vue.createElementVNode("div", {
              class: vue.normalizeClass({
                "col s12 scheduled-reports-field-help": true,
                "form-group__error-message": _ctx.validationErrors.reports
              })
            }, vue.toDisplayString(_ctx.translate("ScheduledReports_ReportsIncludedHelp")), 3)
          ]),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.reportsByCategoryByReportTypeInColumns, (reportColumns, reportType) => {
            return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
              name: "reportsList",
              class: vue.normalizeClass(`row ${reportType}`),
              key: reportType
            }, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(reportColumns, (reportsByCategory, index) => {
                return vue.openBlock(), vue.createElementBlock("div", {
                  class: "col s12 m6",
                  key: index
                }, [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(reportsByCategory, (reports, category) => {
                    return vue.openBlock(), vue.createElementBlock("div", { key: category }, [
                      vue.createElementVNode("h3", _hoisted_22$1, vue.toDisplayString(category), 1),
                      vue.createElementVNode("ul", _hoisted_23$1, [
                        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(reports, (report) => {
                          var _a2, _b;
                          return vue.openBlock(), vue.createElementBlock("li", {
                            key: report.uniqueId
                          }, [
                            vue.createElementVNode("label", null, [
                              vue.createElementVNode("input", {
                                name: `${reportType}Reports`,
                                type: _ctx.allowMultipleReportsByReportType[reportType] ? "checkbox" : "radio",
                                id: `${reportType}${report.uniqueId}`,
                                checked: (_b = (_a2 = _ctx.selectedReports) == null ? void 0 : _a2[reportType]) == null ? void 0 : _b[report.uniqueId],
                                onChange: ($event) => _ctx.$emit("toggleSelectedReport", {
                                  reportType,
                                  uniqueId: report.uniqueId
                                })
                              }, null, 40, _hoisted_24$1),
                              vue.createElementVNode("span", null, vue.toDisplayString(_ctx.decode(report.name)), 1),
                              report.uniqueId === "MultiSites_getAll" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_25$1, vue.toDisplayString(_ctx.translate("ScheduledReports_ReportIncludeNWebsites", String(_ctx.countWebsites))), 1)) : vue.createCommentVNode("", true)
                            ])
                          ]);
                        }), 128))
                      ]),
                      _cache[18] || (_cache[18] = vue.createElementVNode("br", null, null, -1))
                    ]);
                  }), 128))
                ]);
              }), 128))
            ], 2)), [
              [vue.vShow, _ctx.report.type === reportType]
            ]);
          }), 128)),
          _ctx.allowMultipleReportsByReportType[_ctx.report.type] && _ctx.selectedReportsForCurrentType.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_26$1, [
            vue.createElementVNode("div", _hoisted_27$1, [
              vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate("ScheduledReports_SelectedReports")), 1)
            ]),
            vue.createElementVNode("p", _hoisted_28$1, vue.toDisplayString(_ctx.translate("ScheduledReports_SelectedReportsHelp")), 1),
            vue.createVNode(_component_DraggableList, {
              class: "selectedReportsList",
              items: _ctx.selectedReportsForCurrentType,
              "item-key": "uniqueId",
              onReorder: _ctx.onSelectedReportsReorder
            }, {
              default: vue.withCtx(({ item: reportItem }) => [
                _cache[19] || (_cache[19] = vue.createElementVNode("span", { class: "icon-menu-hamburger drag-icon" }, null, -1)),
                vue.createElementVNode("span", null, vue.toDisplayString(_ctx.decode(reportItem.name)), 1)
              ]),
              _: 1
            }, 8, ["items", "onReorder"])
          ])) : vue.createCommentVNode("", true),
          vue.createVNode(_component_SaveButton, {
            value: _ctx.saveButtonTitle,
            onConfirm: _cache[13] || (_cache[13] = ($event) => _ctx.$emit("submit"))
          }, null, 8, ["value"]),
          vue.createElementVNode("div", {
            class: "entityCancel",
            innerHTML: _ctx.$sanitize(_ctx.entityCancelText)
          }, null, 8, _hoisted_29$1)
        ], 32)), [
          [_directive_form]
        ])
      ]),
      _: 3
    }, 8, ["content-title"]);
  }
  const AddReport = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      contentTitle: {
        type: String,
        required: true
      },
      userLogin: {
        type: String,
        required: true
      },
      loginModule: {
        type: String,
        required: true
      },
      reports: {
        type: Array,
        required: true
      },
      siteName: {
        type: String,
        required: true
      },
      segmentEditorActivated: Boolean,
      savedSegmentsById: Object,
      periods: {
        type: Object,
        required: true
      },
      downloadOutputType: {
        type: Number,
        required: true
      },
      language: {
        type: String,
        required: true
      },
      reportFormatsByReportType: {
        type: Object,
        required: true
      },
      reportTypes: {
        type: Object,
        required: true
      },
      sendingReports: {
        type: Array,
        required: false
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      MatomoLoader: CoreHome.MatomoLoader
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    emits: ["create", "edit", "delete", "sendnow"],
    methods: {
      linkTo(params) {
        return `?${CoreHome.MatomoUrl.stringify(__spreadValues(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), params))}`;
      },
      displayReport(reportId) {
        $(`#downloadReportForm_${reportId}`).submit();
      }
    },
    computed: {
      token_auth() {
        return CoreHome.Matomo.token_auth;
      },
      decodedReports() {
        return this.reports.map(
          (r) => __spreadProps(__spreadValues({}, r), { description: CoreHome.Matomo.helper.htmlDecode(r.description) })
        );
      },
      learnMoreComputed() {
        return CoreHome.translate(
          "ScheduledReports_LearnMoreTooltip",
          CoreHome.externalLink("https://matomo.org/faq/general/create-and-schedule-a-report/"),
          "</a>"
        );
      }
    }
  });
  const _hoisted_1$1 = { class: "browser-default periodTooltipList" };
  const _hoisted_2$1 = ["innerHTML"];
  const _hoisted_3$1 = ["innerHTML"];
  const _hoisted_4$1 = { class: "first" };
  const _hoisted_5$1 = { key: 0 };
  const _hoisted_6$1 = { colspan: "7" };
  const _hoisted_7 = ["href"];
  const _hoisted_8 = { key: 1 };
  const _hoisted_9 = { colspan: "7" };
  const _hoisted_10 = { class: "first" };
  const _hoisted_11 = {
    key: 0,
    class: "entityInlineHelp",
    style: { "font-size": "9pt" }
  };
  const _hoisted_12 = { key: 0 };
  const _hoisted_13 = { key: 1 };
  const _hoisted_14 = { key: 0 };
  const _hoisted_15 = { key: 0 };
  const _hoisted_16 = ["onClick"];
  const _hoisted_17 = ["src"];
  const _hoisted_18 = {
    href: "#",
    name: "linkSendNow",
    class: "link_but move-left"
  };
  const _hoisted_19 = {
    key: 2,
    class: "loadingPiwik"
  };
  const _hoisted_20 = ["id", "action"];
  const _hoisted_21 = ["value"];
  const _hoisted_22 = ["onClick"];
  const _hoisted_23 = ["src"];
  const _hoisted_24 = ["id"];
  const _hoisted_25 = { style: { "text-align": "center", "padding-top": "2px" } };
  const _hoisted_26 = ["onClick", "title"];
  const _hoisted_27 = { style: { "text-align": "center", "padding-top": "2px" } };
  const _hoisted_28 = ["onClick", "title"];
  const _hoisted_29 = { class: "tableActionBar" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      id: "entityEditContainer",
      class: "entityTableContainer",
      "help-url": _ctx.externalRawLink("https://matomo.org/docs/email-reports/"),
      feature: "true",
      "content-title": _ctx.contentTitle
    }, {
      default: vue.withCtx(() => {
        var _a2;
        return [
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("ScheduledReports_ManageTooltip1")), 1),
          vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("ScheduledReports_ManageTooltip2")), 1),
          vue.createElementVNode("ul", _hoisted_1$1, [
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("ScheduledReports_PeriodTooltip1")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("ScheduledReports_PeriodTooltip2")), 1),
            vue.createElementVNode("li", null, vue.toDisplayString(_ctx.translate("ScheduledReports_PeriodTooltip3")), 1)
          ]),
          vue.createElementVNode("p", null, [
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.translate("ScheduledReports_ManageTooltip3"))
            }, null, 8, _hoisted_2$1),
            _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.learnMoreComputed)
            }, null, 8, _hoisted_3$1)
          ]),
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
            vue.createElementVNode("thead", null, [
              vue.createElementVNode("tr", null, [
                vue.createElementVNode("th", _hoisted_4$1, vue.toDisplayString(_ctx.translate("General_Name")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("ScheduledReports_ReportSchedule")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("ScheduledReports_ReportFormat")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("ScheduledReports_SendReportTo")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Download")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Edit")), 1),
                vue.createElementVNode("th", null, vue.toDisplayString(_ctx.translate("General_Delete")), 1)
              ])
            ]),
            vue.createElementVNode("tbody", null, [
              _ctx.userLogin === "anonymous" ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_5$1, [
                vue.createElementVNode("td", _hoisted_6$1, [
                  _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_MustBeLoggedIn")) + " ", 1),
                  _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1)),
                  _cache[4] || (_cache[4] = vue.createTextVNode("› ", -1)),
                  vue.createElementVNode("a", {
                    href: `index.php?module=${_ctx.loginModule}`
                  }, vue.toDisplayString(_ctx.translate("Login_LogIn")), 9, _hoisted_7),
                  _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1)),
                  _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1))
                ])
              ])) : !((_a2 = _ctx.reports) == null ? void 0 : _a2.length) ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_8, [
                vue.createElementVNode("td", _hoisted_9, [
                  _cache[7] || (_cache[7] = vue.createElementVNode("br", null, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_ThereIsNoReportToManage", _ctx.siteName)) + ". ", 1),
                  _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
                  _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1))
                ])
              ])) : vue.createCommentVNode("", true),
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.decodedReports, (report) => {
                return vue.openBlock(), vue.createElementBlock("tr", {
                  key: report.idreport
                }, [
                  vue.createElementVNode("td", _hoisted_10, [
                    vue.createTextVNode(vue.toDisplayString(report.description) + " ", 1),
                    _ctx.segmentEditorActivated && report.idsegment ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_11, [
                      (_ctx.savedSegmentsById || {})[report.idsegment] ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_12, vue.toDisplayString((_ctx.savedSegmentsById || {})[report.idsegment]), 1)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_13, vue.toDisplayString(_ctx.translate("ScheduledReports_SegmentDeleted")), 1))
                    ])) : vue.createCommentVNode("", true)
                  ]),
                  vue.createElementVNode("td", null, vue.toDisplayString(_ctx.periods[report.period]), 1),
                  vue.createElementVNode("td", null, [
                    report.format ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_14, vue.toDisplayString(report.format.toUpperCase()), 1)) : vue.createCommentVNode("", true)
                  ]),
                  vue.createElementVNode("td", null, [
                    report.recipients.length === 0 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_15, vue.toDisplayString(_ctx.translate("ScheduledReports_NoRecipients")), 1)) : vue.createCommentVNode("", true),
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(report.recipients, (recipient, index) => {
                      return vue.openBlock(), vue.createElementBlock("span", { key: index }, [
                        vue.createTextVNode(vue.toDisplayString(recipient) + " ", 1),
                        _cache[10] || (_cache[10] = vue.createElementVNode("br", null, null, -1))
                      ]);
                    }), 128)),
                    report.recipients.length !== 0 && !(_ctx.sendingReports || []).includes(report.idreport) ? (vue.openBlock(), vue.createElementBlock("span", {
                      key: 1,
                      class: "clickable",
                      onClick: vue.withModifiers(($event) => _ctx.$emit("sendnow", report.idreport), ["prevent"])
                    }, [
                      vue.createElementVNode("img", {
                        border: "0",
                        src: _ctx.reportTypes[report.type]
                      }, null, 8, _hoisted_17),
                      vue.createElementVNode("a", _hoisted_18, vue.toDisplayString(_ctx.translate("ScheduledReports_SendPreviewNow")) + " " + vue.toDisplayString(_ctx.translate("ScheduledReports_CurrentPeriod")), 1)
                    ], 8, _hoisted_16)) : vue.createCommentVNode("", true),
                    (_ctx.sendingReports || []).includes(report.idreport) ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_19, [
                      vue.createVNode(_component_MatomoLoader),
                      vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_SendingReport")), 1)
                    ])) : vue.createCommentVNode("", true)
                  ]),
                  vue.createElementVNode("td", null, [
                    vue.createElementVNode("form", {
                      method: "POST",
                      target: "_blank",
                      id: `downloadReportForm_${report.idreport}`,
                      action: _ctx.linkTo({
                        module: "API",
                        segment: null,
                        method: "ScheduledReports.generateReport",
                        idReport: report.idreport,
                        outputType: _ctx.downloadOutputType,
                        language: _ctx.language,
                        format: ["html", "csv", "tsv"].indexOf(report.format) !== -1 ? report.format : "original"
                      })
                    }, [
                      vue.createElementVNode("input", {
                        type: "hidden",
                        name: "token_auth",
                        value: _ctx.token_auth
                      }, null, 8, _hoisted_21),
                      _cache[11] || (_cache[11] = vue.createElementVNode("input", {
                        type: "hidden",
                        name: "force_api_session",
                        value: "1"
                      }, null, -1))
                    ], 8, _hoisted_20),
                    vue.createElementVNode("span", {
                      class: "clickable",
                      onClick: vue.withModifiers(($event) => _ctx.displayReport(report.idreport), ["prevent"])
                    }, [
                      vue.createElementVNode("img", {
                        border: "0",
                        width: 16,
                        height: 16,
                        src: _ctx.reportFormatsByReportType[report.type][report.format]
                      }, null, 8, _hoisted_23),
                      vue.createElementVNode("a", {
                        href: "#",
                        rel: "noreferrer noopener",
                        name: "linkDownloadReport",
                        class: "link_but move-left",
                        id: String(report.idreport)
                      }, vue.toDisplayString(_ctx.translate("ScheduledReports_DownloadPreview")) + " " + vue.toDisplayString(_ctx.translate("ScheduledReports_CurrentPeriod")), 9, _hoisted_24)
                    ], 8, _hoisted_22)
                  ]),
                  vue.createElementVNode("td", _hoisted_25, [
                    vue.createElementVNode("button", {
                      class: "table-action",
                      onClick: ($event) => _ctx.$emit("edit", report.idreport),
                      title: _ctx.translate("General_Edit")
                    }, [..._cache[12] || (_cache[12] = [
                      vue.createElementVNode("span", { class: "icon-edit" }, null, -1)
                    ])], 8, _hoisted_26)
                  ]),
                  vue.createElementVNode("td", _hoisted_27, [
                    vue.createElementVNode("button", {
                      class: "table-action",
                      onClick: ($event) => _ctx.$emit("delete", report.idreport),
                      title: _ctx.translate("General_Delete")
                    }, [..._cache[13] || (_cache[13] = [
                      vue.createElementVNode("span", { class: "icon-delete" }, null, -1)
                    ])], 8, _hoisted_28)
                  ])
                ]);
              }), 128))
            ])
          ])), [
            [_directive_content_table]
          ]),
          vue.createElementVNode("div", _hoisted_29, [
            _ctx.userLogin !== "anonymous" ? (vue.openBlock(), vue.createElementBlock("button", {
              key: 0,
              id: "add-report",
              onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("create"))
            }, [
              _cache[14] || (_cache[14] = vue.createElementVNode("span", { class: "icon-add" }, null, -1)),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("ScheduledReports_CreateAndScheduleReport")), 1)
            ])) : vue.createCommentVNode("", true)
          ])
        ];
      }),
      _: 1
    }, 8, ["help-url", "content-title"]);
  }
  const ListReports = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  function getStorage() {
    return typeof sessionStorage === "undefined" ? null : sessionStorage;
  }
  function getStoredValue(key) {
    var _a2, _b;
    return (_b = (_a2 = getStorage()) == null ? void 0 : _a2.getItem(key)) != null ? _b : null;
  }
  function setStoredValue(key, value) {
    const storage = getStorage();
    if (storage) {
      storage.setItem(key, value);
    }
  }
  function removeStoredValue(key) {
    const storage = getStorage();
    if (storage) {
      storage.removeItem(key);
    }
  }
  function consumeStoredValue(key) {
    var _a2;
    const storage = getStorage();
    const value = (_a2 = storage == null ? void 0 : storage.getItem(key)) != null ? _a2 : null;
    if (value !== null && storage) {
      storage.removeItem(key);
    }
    return value;
  }
  function scrollToTop() {
    CoreHome.Matomo.helper.lazyScrollTo(".emailReports", 200, true);
  }
  function updateParameters(reportType, report) {
    var _a2;
    if ((_a2 = window.updateReportParametersFunctions) == null ? void 0 : _a2[reportType]) {
      window.updateReportParametersFunctions[reportType](report);
    }
  }
  function resetParameters(reportType, report) {
    var _a2;
    if ((_a2 = window.resetReportParametersFunctions) == null ? void 0 : _a2[reportType]) {
      window.resetReportParametersFunctions[reportType](report);
    }
  }
  window.resetReportParametersFunctions = window.resetReportParametersFunctions || {};
  window.updateReportParametersFunctions = window.updateReportParametersFunctions || {};
  window.getReportParametersFunctions = window.getReportParametersFunctions || {};
  const { $: $$1 } = window;
  const PENDING_NOTIFICATION_KEY = "scheduledReports.pendingNotification";
  const DASHBOARD_EXPORT_STORAGE_KEY = "scheduledReports.dashboardExportId";
  const VALIDATION_NOTIFICATION_ID = "scheduledReportValidationError";
  const timeZoneDifferenceInHours = CoreHome.Matomo.timezoneOffset / 3600;
  const _sfc_main = vue.defineComponent({
    name: "ManageScheduledReport",
    props: {
      contentTitle: {
        type: String,
        required: true
      },
      userLogin: {
        type: String,
        required: true
      },
      loginModule: {
        type: String,
        required: true
      },
      reports: {
        type: Array,
        required: true
      },
      siteName: {
        type: String,
        required: true
      },
      segmentEditorActivated: Boolean,
      savedSegmentsById: Object,
      periods: {
        type: Object,
        required: true
      },
      downloadOutputType: {
        type: Number,
        required: true
      },
      language: {
        type: String,
        required: true
      },
      reportFormatsByReportType: {
        type: Object,
        required: true
      },
      paramPeriods: {
        type: Object,
        required: true
      },
      reportTypeOptions: {
        type: Object,
        required: true
      },
      reportFormatsByReportTypeOptions: {
        type: Object,
        required: true
      },
      displayFormats: {
        type: Object,
        required: true
      },
      reportsByCategoryByReportType: {
        type: Object,
        required: true
      },
      allowMultipleReportsByReportType: {
        type: Object,
        required: true
      },
      countWebsites: {
        type: Number,
        required: true
      },
      reportTypes: {
        type: Object,
        required: true
      }
    },
    components: {
      MatomoLoader: CoreHome.MatomoLoader,
      AddReport,
      ListReports
    },
    directives: {
      ContentTable: CoreHome.ContentTable,
      Form: CorePluginsAdmin.Form
    },
    mounted() {
      $$1(this.$refs.root).on("click", "a.entityCancelLink", () => {
        this.showListOfReports();
      });
      this.handleDashboardExportFromSession();
      CoreHome.Matomo.postEvent("ScheduledReports.ManageScheduledReport.mounted", {
        element: this.$refs.root
      });
      const pendingMessage = getStoredValue(PENDING_NOTIFICATION_KEY);
      if (pendingMessage && this.$refs.reportUpdatedSuccess) {
        removeStoredValue(PENDING_NOTIFICATION_KEY);
        scrollToTop();
        this.fadeInOutSuccessMessage(
          this.$refs.reportUpdatedSuccess,
          pendingMessage,
          false
        );
      }
    },
    unmounted() {
      CoreHome.Matomo.postEvent("ScheduledReports.ManageScheduledReport.unmounted", {
        element: this.$refs.root
      });
    },
    data() {
      return {
        showReportsList: true,
        report: {},
        selectedReports: {},
        selectedReportsOrder: {},
        sendingReports: [],
        isDashboardExportInfoVisible: false,
        validationErrors: {
          name: false,
          reports: false
        }
      };
    },
    methods: {
      sendReportNow(idReport) {
        if (this.sendingReports.includes(idReport)) {
          return;
        }
        scrollToTop();
        this.sendingReports.push(idReport);
        CoreHome.AjaxHelper.post(
          {
            method: "ScheduledReports.sendReport"
          },
          {
            idReport,
            force: true
          }
        ).then(() => {
          this.fadeInOutSuccessMessage(
            this.$refs.reportSentSuccess,
            CoreHome.translate("ScheduledReports_ReportSent"),
            false
          );
        }).finally(() => {
          this.sendingReports = this.sendingReports.filter(
            (report) => report !== idReport
          );
        });
      },
      formSetEditReport(idReport) {
        const { ReportPlugin } = window;
        let report = {
          idreport: idReport,
          type: ReportPlugin.defaultReportType,
          format: ReportPlugin.defaultReportFormat,
          description: "",
          reportDescription: "",
          period: ReportPlugin.defaultPeriod,
          hour: ReportPlugin.defaultHour,
          reports: [],
          idsegment: "",
          evolutionPeriodFor: "prev",
          evolutionPeriodN: ReportPlugin.defaultEvolutionPeriodN,
          periodParam: ReportPlugin.defaultPeriod
        };
        if (idReport > 0) {
          report = ReportPlugin.reportList[idReport];
          updateParameters(report.type, report);
        } else {
          resetParameters(report.type, report);
        }
        report.hour = adjustHourToTimezone(report.hour, timeZoneDifferenceInHours);
        this.selectedReports = {};
        this.selectedReportsOrder = {};
        Object.values(report.reports).forEach((reportId) => {
          this.selectedReports[report.type] = this.selectedReports[report.type] || {};
          this.selectedReports[report.type][reportId] = true;
        });
        this.selectedReportsOrder[report.type] = Object.values(report.reports).map(
          (reportId) => reportId
        );
        report[`format${report.type}`] = report.format;
        if (!report.idsegment) {
          report.idsegment = "";
        }
        this.report = report;
        this.report.description = CoreHome.Matomo.helper.htmlDecode(report.description);
        this.report.reportDescription = CoreHome.Matomo.helper.htmlDecode(
          report.parameters && Object.prototype.hasOwnProperty.call(
            report.parameters,
            "reportDescription"
          ) ? `${report.parameters.reportDescription || ""}` : ""
        );
      },
      showNotificationMessage(selector, message, context = "success", type = "toast") {
        CoreHome.NotificationsStore.show({
          message,
          placeat: selector,
          context,
          noclear: true,
          type,
          style: {
            display: "inline-block",
            marginTop: "10px",
            width: "100%"
          },
          id: "scheduledReportSuccess"
        });
      },
      fadeInOutSuccessMessage(selector, message, reload = true) {
        this.showNotificationMessage(selector, message);
        if (reload) {
          CoreHome.Matomo.helper.refreshAfter(2);
        }
      },
      queueSaveNotificationAndRefresh(isUpdate) {
        setStoredValue(
          PENDING_NOTIFICATION_KEY,
          isUpdate ? CoreHome.translate("ScheduledReports_ReportUpdated") : CoreHome.translate("ScheduledReports_ReportAdded")
        );
        CoreHome.Matomo.helper.refreshAfter(0);
      },
      showDashboardExportInfo(selector, message, dashboardName, reload = true) {
        let dashboardInfoMessage = `${CoreHome.translate("ScheduledReports_ExportDashboardTitle")}
        <br/><br/>${CoreHome.translate("ScheduledReports_ExportDashboardPrepare", CoreHome.Matomo.helper.htmlEntities(dashboardName))}
        <br/><br/>${CoreHome.translate("ScheduledReports_ExportDashboardWidgetsConvertedAutomatically")}
        <br/><br/>${CoreHome.translate("ScheduledReports_ExportDashboardEmailEnabledByDefault", CoreHome.translate("ScheduledReports_ReportSchedule"), CoreHome.translate("General_Never"))}
        <br/><br/>${CoreHome.translate("ScheduledReports_ExportDashboardDownload")}`;
        if (message !== "") {
          dashboardInfoMessage += `<br/><br/>${message}`;
        }
        this.isDashboardExportInfoVisible = true;
        this.showNotificationMessage(selector, dashboardInfoMessage, "info", "persistent");
        if (reload) {
          setStoredValue(PENDING_NOTIFICATION_KEY, message);
          CoreHome.Matomo.helper.refreshAfter(2);
        }
      },
      changedReportType() {
        resetParameters(this.report.type, this.report);
      },
      deleteReport(idReport) {
        CoreHome.Matomo.helper.modalConfirm("#confirm", {
          yes: () => {
            CoreHome.AjaxHelper.post(
              {
                method: "ScheduledReports.deleteReport"
              },
              {
                idReport
              },
              {
                redirectOnSuccess: true
              }
            );
          }
        });
      },
      showListOfReports(shouldScrollToTop) {
        this.showReportsList = true;
        this.resetValidationErrors();
        if (this.isDashboardExportInfoVisible) {
          CoreHome.NotificationsStore.remove("scheduledReportSuccess");
          this.isDashboardExportInfoVisible = false;
        }
        CoreHome.Matomo.helper.hideAjaxError();
        if (typeof shouldScrollToTop === "undefined" || shouldScrollToTop) {
          scrollToTop();
        }
      },
      createReport(afterInit) {
        this.showReportsList = false;
        vue.nextTick(() => {
          this.formSetEditReport(0);
          this.resetValidationErrors();
          if (afterInit) {
            afterInit();
          }
        });
      },
      editReport(reportId) {
        this.showReportsList = false;
        vue.nextTick(() => {
          this.formSetEditReport(reportId);
          this.resetValidationErrors();
        });
      },
      submitReport() {
        const apiParameters = {
          idReport: this.report.idreport,
          description: this.report.description,
          idSegment: this.report.idsegment,
          reportType: this.report.type,
          reportFormat: this.report[`format${this.report.type}`],
          periodParam: this.report.periodParam,
          evolutionPeriodFor: this.report.evolutionPeriodFor
        };
        if (apiParameters.evolutionPeriodFor !== "each") {
          apiParameters.evolutionPeriodN = this.report.evolutionPeriodN;
        }
        const { period } = this.report;
        const hour = adjustHourToTimezone(this.report.hour, -timeZoneDifferenceInHours);
        const reportType = apiParameters.reportType;
        const selectedReports = this.selectedReports[reportType] || {};
        let reports = (this.selectedReportsOrder[reportType] || []).filter(
          (name) => selectedReports[name]
        );
        if (!reports.length) {
          reports = Object.keys(selectedReports).filter(
            (name) => selectedReports[name]
          );
        }
        if (reports.length > 0) {
          apiParameters.reports = reports;
        }
        const validationErrors = this.getValidationErrors(reports);
        if (validationErrors.name || validationErrors.reports) {
          this.validationErrors = validationErrors;
          scrollToTop();
          this.showValidationErrors(validationErrors);
          return false;
        }
        const reportParams = window.getReportParametersFunctions[this.report.type](this.report);
        reportParams.reportDescription = this.report.reportDescription || "";
        apiParameters.parameters = reportParams;
        const isUpdate = Number(this.report.idreport) > 0;
        CoreHome.AjaxHelper.post(
          {
            method: isUpdate ? "ScheduledReports.updateReport" : "ScheduledReports.addReport",
            period,
            hour
          },
          apiParameters
        ).then(() => {
          this.queueSaveNotificationAndRefresh(isUpdate);
        });
        return false;
      },
      onChangeProperty(propName, value) {
        this.report[propName] = value;
        if (propName === "type") {
          this.changedReportType();
        }
        this.refreshValidationErrors();
      },
      toggleSelectedReport(reportType, uniqueId) {
        this.selectedReports[reportType] = this.selectedReports[reportType] || {};
        const newValue = !this.selectedReports[reportType][uniqueId];
        this.selectedReports[reportType][uniqueId] = newValue;
        this.selectedReportsOrder[reportType] = this.selectedReportsOrder[reportType] || [];
        if (newValue) {
          if (this.selectedReportsOrder[reportType].indexOf(uniqueId) === -1) {
            this.selectedReportsOrder[reportType].push(uniqueId);
          }
        } else {
          this.selectedReportsOrder[reportType] = this.selectedReportsOrder[reportType].filter(
            (reportId) => reportId !== uniqueId
          );
        }
        this.refreshValidationErrors();
      },
      onReorderSelectedReports(reportType, order) {
        this.selectedReportsOrder[reportType] = order.filter(
          (uniqueId) => {
            var _a2;
            return (_a2 = this.selectedReports[reportType]) == null ? void 0 : _a2[uniqueId];
          }
        );
      },
      getSelectedReportIds(reportType) {
        const selectedReports = this.selectedReports[reportType] || {};
        let reports = (this.selectedReportsOrder[reportType] || []).filter(
          (name) => selectedReports[name]
        );
        if (!reports.length) {
          reports = Object.keys(selectedReports).filter(
            (name) => selectedReports[name]
          );
        }
        return reports;
      },
      getValidationErrors(selectedReportIds) {
        const reportType = this.report.type;
        const selectedReports = selectedReportIds || this.getSelectedReportIds(reportType);
        return {
          name: !String(this.report.description || "").trim(),
          reports: selectedReports.length === 0
        };
      },
      showValidationErrors(validationErrors) {
        const messages = [];
        if (validationErrors.name) {
          messages.push(CoreHome.translate(
            "ScheduledReports_ReportMissingName",
            '<a href="#report_description">',
            "</a>"
          ));
        }
        if (validationErrors.reports) {
          messages.push(CoreHome.translate(
            "ScheduledReports_ReportMissingReports",
            '<a href="#scheduled-reports-selection-heading">',
            "</a>"
          ));
        }
        const message = messages.length > 1 ? `<ul><li>${messages.join("</li><li>")}</li></ul>` : messages[0];
        CoreHome.NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
        CoreHome.NotificationsStore.show({
          message,
          placeat: this.$refs.reportUpdatedSuccess,
          context: "error",
          noclear: true,
          type: "persistent",
          style: {
            display: "inline-block",
            marginTop: "10px",
            width: "100%"
          },
          id: VALIDATION_NOTIFICATION_ID
        });
      },
      refreshValidationErrors() {
        if (!this.validationErrors.name && !this.validationErrors.reports) {
          return;
        }
        const validationErrors = this.getValidationErrors();
        this.validationErrors = validationErrors;
        if (validationErrors.name || validationErrors.reports) {
          this.showValidationErrors(validationErrors);
        } else {
          CoreHome.NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
        }
      },
      resetValidationErrors() {
        this.validationErrors = {
          name: false,
          reports: false
        };
        CoreHome.NotificationsStore.remove(VALIDATION_NOTIFICATION_ID);
      },
      handleDashboardExportFromSession() {
        return __async(this, null, function* () {
          const storedDashboardId = this.consumeDashboardExportIdFromSession();
          if (storedDashboardId === null) {
            return;
          }
          const dashboardId = this.parsePositiveDashboardIdParam(storedDashboardId);
          if (dashboardId === "") {
            scrollToTop();
            this.showNotificationMessage(
              this.$refs.reportUpdatedSuccess,
              CoreHome.translate("ScheduledReports_ExportDashboardInvalidDashboard"),
              "error",
              "persistent"
            );
            return;
          }
          this.getWidgetReportMapping(dashboardId).then((mapping) => {
            if (!this.isValidDashboardExportMapping(mapping)) {
              scrollToTop();
              this.showNotificationMessage(
                this.$refs.reportUpdatedSuccess,
                CoreHome.translate("ScheduledReports_ExportDashboardInvalidDashboard"),
                "error",
                "persistent"
              );
              return;
            }
            this.createReport(() => {
              this.applyDashboardExportMapping(mapping);
            });
          }).catch(() => {
            scrollToTop();
            this.showNotificationMessage(
              this.$refs.reportUpdatedSuccess,
              CoreHome.translate("General_ErrorTryAgain"),
              "error"
            );
          });
        });
      },
      consumeDashboardExportIdFromSession() {
        return consumeStoredValue(DASHBOARD_EXPORT_STORAGE_KEY);
      },
      getWidgetReportMapping(dashboardId) {
        return __async(this, null, function* () {
          return CoreHome.AjaxHelper.fetch(
            {
              method: "ScheduledReports.getWidgetReportMap",
              dashId: dashboardId,
              idSite: CoreHome.Matomo.idSite,
              segment: this.getExportSegmentFromUrl()
            }
          ).then((e) => e);
        });
      },
      getExportSegmentFromUrl() {
        const { segment } = CoreHome.MatomoUrl.parsed.value;
        return typeof segment === "string" ? segment : "";
      },
      parsePositiveDashboardIdParam(value) {
        if (typeof value !== "string") {
          return "";
        }
        return /^[1-9]\d*$/.test(value.trim()) ? value.trim() : "";
      },
      isValidDashboardExportMapping(mapping) {
        if (!(mapping == null ? void 0 : mapping.dashboardName)) {
          return false;
        }
        return Object.keys(mapping.email || {}).length > 0;
      },
      applyDashboardExportMapping(mapping) {
        if (!this.isValidDashboardExportMapping(mapping)) {
          return;
        }
        const dashName = CoreHome.Matomo.helper.htmlDecode(mapping.dashboardName);
        this.selectedReports = { email: __spreadValues({}, mapping.email) };
        this.selectedReportsOrder = { email: Object.keys(mapping.email || {}) };
        if (mapping.idSegment) {
          this.report.idsegment = mapping.idSegment;
        }
        const dateTodayString = CoreHome.format(CoreHome.getToday());
        this.report.description = CoreHome.translate(
          "ScheduledReports_ExportDashboardReportDescription",
          dashName,
          dateTodayString
        );
        let unmappedWidgetsForDisplay = "";
        if (mapping.unmappedWidgets && mapping.unmappedWidgets.length) {
          const escapedWidgets = mapping.unmappedWidgets.map(
            (widgetName) => CoreHome.Matomo.helper.escape(widgetName)
          );
          unmappedWidgetsForDisplay = CoreHome.translate(
            "ScheduledReports_WidgetsNotMappedToReports",
            escapedWidgets.join(", ")
          );
        }
        this.showDashboardExportInfo(
          this.$refs.reportUpdatedSuccess,
          unmappedWidgetsForDisplay,
          dashName,
          false
        );
      }
    },
    computed: {
      showReportForm() {
        return !this.showReportsList;
      },
      decodedSiteName() {
        return CoreHome.Matomo.helper.htmlDecode(this.siteName);
      }
    }
  });
  const _hoisted_1 = {
    class: "emailReports",
    ref: "root"
  };
  const _hoisted_2 = { ref: "reportSentSuccess" };
  const _hoisted_3 = { ref: "reportUpdatedSuccess" };
  const _hoisted_4 = {
    id: "ajaxLoadingDiv",
    ref: "ajaxLoadingDiv",
    style: { "display": "none" }
  };
  const _hoisted_5 = { class: "loadingPiwik" };
  const _hoisted_6 = { class: "loadingSegment" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_ListReports = vue.resolveComponent("ListReports");
    const _component_AddReport = vue.resolveComponent("AddReport");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createElementVNode("div", _hoisted_2, null, 512),
      vue.createElementVNode("div", _hoisted_3, null, 512),
      vue.createElementVNode("div", null, [
        _cache[8] || (_cache[8] = vue.createElementVNode("div", {
          id: "ajaxError",
          style: { "display": "none" }
        }, null, -1)),
        vue.createElementVNode("div", _hoisted_4, [
          vue.createElementVNode("div", _hoisted_5, [
            vue.createVNode(_component_MatomoLoader),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
          ]),
          vue.createElementVNode("div", _hoisted_6, vue.toDisplayString(_ctx.translate("SegmentEditor_LoadingSegmentedDataMayTakeSomeTime")), 1)
        ], 512),
        vue.withDirectives(vue.createVNode(_component_ListReports, {
          "content-title": _ctx.contentTitle,
          "user-login": _ctx.userLogin,
          "login-module": _ctx.loginModule,
          reports: _ctx.reports,
          "site-name": _ctx.decodedSiteName,
          "segment-editor-activated": _ctx.segmentEditorActivated,
          "saved-segments-by-id": _ctx.savedSegmentsById,
          periods: _ctx.periods,
          "report-types": _ctx.reportTypes,
          "download-output-type": _ctx.downloadOutputType,
          language: _ctx.language,
          "report-formats-by-report-type": _ctx.reportFormatsByReportType,
          "sending-reports": _ctx.sendingReports,
          onCreate: _cache[0] || (_cache[0] = ($event) => _ctx.createReport()),
          onEdit: _cache[1] || (_cache[1] = ($event) => _ctx.editReport($event)),
          onDelete: _cache[2] || (_cache[2] = ($event) => _ctx.deleteReport($event)),
          onSendnow: _cache[3] || (_cache[3] = ($event) => _ctx.sendReportNow($event))
        }, null, 8, ["content-title", "user-login", "login-module", "reports", "site-name", "segment-editor-activated", "saved-segments-by-id", "periods", "report-types", "download-output-type", "language", "report-formats-by-report-type", "sending-reports"]), [
          [vue.vShow, _ctx.showReportsList]
        ]),
        _ctx.showReportForm ? (vue.openBlock(), vue.createBlock(_component_AddReport, {
          key: 0,
          report: _ctx.report,
          "validation-errors": _ctx.validationErrors,
          periods: _ctx.periods,
          "param-periods": _ctx.paramPeriods,
          "report-type-options": _ctx.reportTypeOptions,
          "report-formats-by-report-type-options": _ctx.reportFormatsByReportTypeOptions,
          "display-formats": _ctx.displayFormats,
          "reports-by-category-by-report-type": _ctx.reportsByCategoryByReportType,
          "allow-multiple-reports-by-report-type": _ctx.allowMultipleReportsByReportType,
          "count-websites": _ctx.countWebsites,
          "site-name": _ctx.decodedSiteName,
          "selected-reports": _ctx.selectedReports,
          "selected-reports-order": _ctx.selectedReportsOrder,
          "report-types": _ctx.reportTypes,
          "segment-editor-activated": _ctx.segmentEditorActivated,
          "saved-segments-by-id": _ctx.savedSegmentsById,
          onToggleSelectedReport: _cache[4] || (_cache[4] = ($event) => _ctx.toggleSelectedReport($event.reportType, $event.uniqueId)),
          onReorderSelectedReports: _cache[5] || (_cache[5] = ($event) => _ctx.onReorderSelectedReports($event.reportType, $event.order)),
          onChange: _cache[6] || (_cache[6] = ($event) => _ctx.onChangeProperty($event.prop, $event.value)),
          onSubmit: _cache[7] || (_cache[7] = ($event) => _ctx.submitReport())
        }, {
          "report-parameters": vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "report-parameters")
          ]),
          _: 3
        }, 8, ["report", "validation-errors", "periods", "param-periods", "report-type-options", "report-formats-by-report-type-options", "display-formats", "reports-by-category-by-report-type", "allow-multiple-reports-by-report-type", "count-websites", "site-name", "selected-reports", "selected-reports-order", "report-types", "segment-editor-activated", "saved-segments-by-id"])) : vue.createCommentVNode("", true),
        _cache[9] || (_cache[9] = vue.createElementVNode("a", { id: "bottom" }, null, -1))
      ])
    ], 512);
  }
  const ManageScheduledReport = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.ManageScheduledReport = ManageScheduledReport;
  exports2.ReportParameters = ReportParameters;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
