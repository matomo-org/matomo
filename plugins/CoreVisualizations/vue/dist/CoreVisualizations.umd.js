(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.CoreVisualizations = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
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

  function getInitialOptionStates(allOptions, selectedOptions) {
    const states = {};
    allOptions.forEach((columnConfig) => {
      const name = columnConfig.column || columnConfig.matcher;
      states[name] = false;
    });
    selectedOptions.forEach((column) => {
      states[column] = true;
    });
    return states;
  }
  function arrayEqual(lhs, rhs) {
    if (lhs.length !== rhs.length) {
      return false;
    }
    return lhs.filter((element) => rhs.indexOf(element) === -1).length === 0;
  }
  function unselectOptions(optionStates) {
    Object.keys(optionStates).forEach((optionName) => {
      optionStates[optionName] = false;
    });
  }
  function getSelected(optionStates) {
    return Object.keys(optionStates).filter((optionName) => !!optionStates[optionName]);
  }
  const _sfc_main$1 = vue.defineComponent({
    props: {
      multiselect: Boolean,
      selectableColumns: {
        type: Array,
        default: () => []
      },
      selectableRows: {
        type: Array,
        default: () => []
      },
      selectedColumns: {
        type: Array,
        default: () => []
      },
      selectedRows: {
        type: Array,
        default: () => []
      }
    },
    data() {
      return {
        isPopupVisible: false,
        columnStates: getInitialOptionStates(
          this.selectableColumns,
          this.selectedColumns
        ),
        rowStates: getInitialOptionStates(
          this.selectableRows,
          this.selectedRows
        )
      };
    },
    emits: ["select"],
    created() {
      this.optionSelected = CoreHome.debounce(this.optionSelected, 0);
    },
    methods: {
      optionSelected(optionValue, optionStates) {
        if (!this.multiselect) {
          unselectOptions(this.columnStates);
          unselectOptions(this.rowStates);
        }
        optionStates[optionValue] = !optionStates[optionValue];
        this.triggerOnSelectAndClose();
      },
      onLeavePopup() {
        this.isPopupVisible = false;
        if (this.optionsChanged()) {
          this.triggerOnSelectAndClose();
        }
      },
      triggerOnSelectAndClose() {
        this.isPopupVisible = false;
        this.$emit("select", {
          columns: getSelected(this.columnStates),
          rows: getSelected(this.rowStates)
        });
      },
      optionsChanged() {
        return !arrayEqual(
          getSelected(this.columnStates),
          this.selectedColumns
        ) || !arrayEqual(
          getSelected(this.rowStates),
          this.selectedRows
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
  const _hoisted_1$1 = {
    key: 0,
    class: "jqplot-seriespicker-popover"
  };
  const _hoisted_2$1 = { class: "headline" };
  const _hoisted_3$1 = ["onClick"];
  const _hoisted_4$1 = ["type", "checked"];
  const _hoisted_5 = {
    key: 0,
    class: "headline recordsToPlot"
  };
  const _hoisted_6 = ["onClick"];
  const _hoisted_7 = ["type", "checked"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["jqplot-seriespicker", { open: _ctx.isPopupVisible }]),
      onMouseenter: _cache[1] || (_cache[1] = ($event) => _ctx.isPopupVisible = true),
      onMouseleave: _cache[2] || (_cache[2] = ($event) => _ctx.onLeavePopup())
    }, [
      vue.createElementVNode("a", {
        href: "#",
        onClick: _cache[0] || (_cache[0] = vue.withModifiers(() => {
        }, ["prevent", "stop"]))
      }, " + "),
      _ctx.isPopupVisible ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
        vue.createElementVNode("p", _hoisted_2$1, vue.toDisplayString(_ctx.translate(_ctx.multiselect ? "General_MetricsToPlot" : "General_MetricToPlot")), 1),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectableColumns, (columnConfig) => {
          return vue.openBlock(), vue.createElementBlock("p", {
            class: "pickColumn",
            onClick: ($event) => _ctx.optionSelected(columnConfig.column, _ctx.columnStates),
            key: columnConfig.column
          }, [
            vue.createElementVNode("label", null, [
              vue.createElementVNode("input", {
                class: "select",
                type: _ctx.multiselect ? "checkbox" : "radio",
                checked: !!_ctx.columnStates[columnConfig.column]
              }, null, 8, _hoisted_4$1),
              vue.createElementVNode("span", null, vue.toDisplayString(columnConfig.translation), 1)
            ])
          ], 8, _hoisted_3$1);
        }), 128)),
        _ctx.selectableRows.length ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_5, vue.toDisplayString(_ctx.translate("General_RecordsToPlot")), 1)) : vue.createCommentVNode("", true),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectableRows, (rowConfig) => {
          return vue.openBlock(), vue.createElementBlock("p", {
            class: "pickRow",
            onClick: ($event) => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates),
            key: rowConfig.matcher
          }, [
            vue.createElementVNode("label", null, [
              vue.createElementVNode("input", {
                class: "select",
                type: _ctx.multiselect ? "checkbox" : "radio",
                checked: !!_ctx.rowStates[rowConfig.matcher]
              }, null, 8, _hoisted_7),
              vue.createElementVNode("span", null, vue.toDisplayString(rowConfig.label), 1)
            ])
          ], 8, _hoisted_6);
        }), 128))
      ])) : vue.createCommentVNode("", true)
    ], 34);
  }
  const SeriesPicker = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  function getPastPeriodStr() {
    const { startDate } = CoreHome.Range.getLastNRange(CoreHome.Matomo.period, 2, CoreHome.Matomo.currentDateString);
    const dateRange = CoreHome.Periods.get(CoreHome.Matomo.period).parse(startDate).getDateRange();
    return `${CoreHome.format(dateRange[0])},${CoreHome.format(dateRange[1])}`;
  }
  const { $ } = window;
  const _sfc_main = vue.defineComponent({
    props: {
      metric: {
        type: String,
        required: true
      },
      idGoal: [String, Number],
      metricTranslations: {
        type: Object,
        required: true
      },
      metricDocumentations: Object,
      goals: {
        type: Object,
        required: true
      },
      goalMetrics: Array,
      lowerIsBetterMetrics: {
        type: Array,
        default: () => []
      }
    },
    components: {
      Sparkline: CoreHome.Sparkline
    },
    setup(props) {
      const root = vue.ref(null);
      const isLoading = vue.ref(false);
      const responses = vue.ref(null);
      const actualMetric = vue.ref(props.metric);
      const actualIdGoal = vue.ref(props.idGoal);
      const selectedColumns = vue.computed(() => [
        actualIdGoal.value ? `goal${actualIdGoal.value}_${actualMetric.value}` : actualMetric.value
      ]);
      const metricValueUnformatted = vue.computed(() => {
        var _a;
        if (!((_a = responses.value) == null ? void 0 : _a[1])) {
          return null;
        }
        return responses.value[1][actualMetric.value] || 0;
      });
      const pastValueUnformatted = vue.computed(() => {
        var _a;
        if (!((_a = responses.value) == null ? void 0 : _a[2])) {
          return null;
        }
        return responses.value[2][actualMetric.value] || 0;
      });
      const isLowerValueBetter = vue.computed(
        () => props.lowerIsBetterMetrics.indexOf(actualMetric.value) !== -1
      );
      const evolutionClass = vue.computed(() => {
        if (metricValueUnformatted.value === null || pastValueUnformatted.value === null || metricValueUnformatted.value === pastValueUnformatted.value) {
          return [];
        }
        const increased = metricValueUnformatted.value > pastValueUnformatted.value;
        const isPositive = isLowerValueBetter.value ? !increased : increased;
        return [
          increased ? "evolution-up" : "evolution-down",
          isPositive ? "positive-evolution" : "negative-evolution"
        ];
      });
      const metricChangePercent = vue.computed(() => {
        if (metricValueUnformatted.value === null || metricValueUnformatted.value === void 0 || pastValueUnformatted.value === null || pastValueUnformatted.value === void 0) {
          return null;
        }
        const currentValue = typeof metricValueUnformatted.value === "string" ? parseFloat(metricValueUnformatted.value) : metricValueUnformatted.value;
        const pastValue2 = typeof pastValueUnformatted.value === "string" ? parseFloat(pastValueUnformatted.value) : pastValueUnformatted.value;
        const evolution = CoreHome.Matomo.helper.calculateEvolution(currentValue, pastValue2);
        return `${(evolution * 100).toFixed(2)} %`;
      });
      const pastValue = vue.computed(() => {
        var _a;
        if (!((_a = responses.value) == null ? void 0 : _a[3])) {
          return null;
        }
        const pastDataFormatted = responses.value[3];
        return pastDataFormatted[actualMetric.value] || 0;
      });
      const metricValue = vue.computed(() => {
        var _a;
        if (!((_a = responses.value) == null ? void 0 : _a[0])) {
          return null;
        }
        const currentData = responses.value[0];
        return currentData[actualMetric.value] || 0;
      });
      const metricTranslation = vue.computed(() => {
        var _a;
        if (!((_a = props.metricTranslations) == null ? void 0 : _a[actualMetric.value])) {
          return "";
        }
        return props.metricTranslations[actualMetric.value];
      });
      const metricDocumentation = vue.computed(() => {
        var _a;
        if (!((_a = props.metricDocumentations) == null ? void 0 : _a[actualMetric.value])) {
          return "";
        }
        return props.metricDocumentations[actualMetric.value];
      });
      const currentPeriod = vue.computed(() => {
        if (CoreHome.Matomo.startDateString === CoreHome.Matomo.endDateString) {
          return CoreHome.Matomo.endDateString;
        }
        return `${CoreHome.Matomo.startDateString}, ${CoreHome.Matomo.endDateString}`;
      });
      function isIdGoalSet() {
        return actualIdGoal.value || actualIdGoal.value === 0;
      }
      const sparklineParams = vue.computed(() => {
        const params = {
          module: "API",
          action: "get",
          columns: actualMetric.value
        };
        if (isIdGoalSet()) {
          params.idGoal = actualIdGoal.value;
          params.module = "Goals";
        }
        return params;
      });
      const pastPeriod = vue.computed(() => {
        if (CoreHome.Matomo.period === "range") {
          return void 0;
        }
        return getPastPeriodStr();
      });
      const selectableColumns = vue.computed(() => {
        const result = [];
        Object.keys(props.metricTranslations).forEach((column) => {
          result.push({
            column,
            translation: props.metricTranslations[column]
          });
        });
        Object.values(props.goals || {}).forEach((goal) => {
          props.goalMetrics.forEach((column) => {
            result.push({
              column: `goal${goal.idgoal}_${column}`,
              translation: `${goal.name} - ${props.metricTranslations[column]}`
            });
          });
        });
        return result;
      });
      function setWidgetTitle() {
        var _a;
        let title = metricTranslation.value;
        if (isIdGoalSet()) {
          const goalName = ((_a = props.goals[actualIdGoal.value]) == null ? void 0 : _a.name) || CoreHome.translate("General_Unknown");
          title = `${goalName} - ${title}`;
        }
        $(root.value).closest("div.widget").find(".widgetTop > .widgetName > span").text(title);
      }
      function getLastPeriodDate() {
        const range = CoreHome.Range.getLastNRange(CoreHome.Matomo.period, 2, CoreHome.Matomo.currentDateString);
        return CoreHome.format(range.startDate);
      }
      function fetchData() {
        isLoading.value = true;
        const promises = [];
        let apiModule = "API";
        let apiAction = "get";
        const extraParams = {};
        if (isIdGoalSet()) {
          extraParams.idGoal = actualIdGoal.value;
          extraParams.filter_add_columns_when_show_all_columns = 0;
          apiModule = "Goals";
          apiAction = "get";
        }
        const method = `${apiModule}.${apiAction}`;
        promises.push(CoreHome.AjaxHelper.fetch(__spreadValues({
          method,
          format_metrics: "all"
        }, extraParams)));
        if (CoreHome.Matomo.period !== "range") {
          promises.push(CoreHome.AjaxHelper.fetch(__spreadValues({
            method,
            format_metrics: "0"
          }, extraParams)));
          promises.push(CoreHome.AjaxHelper.fetch(__spreadValues({
            method,
            date: getLastPeriodDate(),
            format_metrics: "0"
          }, extraParams)));
          promises.push(CoreHome.AjaxHelper.fetch(__spreadValues({
            method,
            date: getLastPeriodDate(),
            format_metrics: "all"
          }, extraParams)));
        }
        return Promise.all(promises).then((r) => {
          responses.value = r;
          isLoading.value = false;
        });
      }
      function onMetricChanged(newMetric) {
        actualMetric.value = newMetric;
        fetchData().then(setWidgetTitle);
        $(root.value).closest("[widgetId]").trigger("setParameters", {
          column: actualMetric.value,
          idGoal: actualIdGoal.value
        });
      }
      function setMetric(newColumn) {
        let idGoal = void 0;
        let actualColumn = newColumn;
        const m = newColumn.match(/^goal([0-9]+)_(.*)/);
        if (m) {
          idGoal = +m[1];
          [, , actualColumn] = m;
        }
        if (actualMetric.value !== actualColumn || idGoal !== actualIdGoal.value) {
          actualMetric.value = actualColumn;
          actualIdGoal.value = idGoal;
          onMetricChanged(actualColumn);
        }
      }
      function createSeriesPicker() {
        const element = $(root.value);
        const $widgetName = element.closest("div.widget").find(".widgetTop > .widgetName");
        const $seriesPickerElem = $('<div class="single-metric-view-picker"><div></div></div>');
        const app = CoreHome.createVueApp({
          render: () => vue.createVNode(SeriesPicker, {
            multiselect: false,
            selectableColumns: selectableColumns.value,
            selectableRows: [],
            selectedColumns: selectedColumns.value,
            selectedRows: [],
            onSelect: ({ columns }) => {
              setMetric(columns[0]);
            }
          })
        });
        $widgetName.append($seriesPickerElem);
        app.mount($seriesPickerElem.children()[0]);
        return app;
      }
      let seriesPickerApp;
      vue.onMounted(() => {
        seriesPickerApp = createSeriesPicker();
      });
      vue.onBeforeUnmount(() => {
        $(root.value).closest(".widgetContent").off("widget:destroy").off("widget:reload");
        $(root.value).closest("div.widget").find(".single-metric-view-picker").remove();
        seriesPickerApp.unmount();
      });
      vue.watch(() => props.metric, () => {
        onMetricChanged(props.metric);
      });
      onMetricChanged(props.metric);
      return {
        root,
        metricValue,
        isLoading,
        selectedColumns,
        responses,
        metricValueUnformatted,
        pastValueUnformatted,
        evolutionClass,
        metricChangePercent,
        pastValue,
        metricTranslation,
        metricDocumentation,
        sparklineParams,
        pastPeriod,
        selectableColumns,
        currentPeriod
      };
    }
  });
  const _hoisted_1 = { class: "metric-sparkline" };
  const _hoisted_2 = { class: "metric-value" };
  const _hoisted_3 = ["title"];
  const _hoisted_4 = ["title"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Sparkline = vue.resolveComponent("Sparkline");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["singleMetricView", { "loading": _ctx.isLoading }]),
      ref: "root"
    }, [
      vue.createElementVNode("div", _hoisted_1, [
        vue.createVNode(_component_Sparkline, { params: _ctx.sparklineParams }, null, 8, ["params"])
      ]),
      vue.createElementVNode("div", _hoisted_2, [
        vue.createElementVNode("span", { title: _ctx.metricDocumentation }, [
          vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.metricValue), 1),
          vue.createTextVNode(" " + vue.toDisplayString((_ctx.metricTranslation || "").toLowerCase()), 1)
        ], 8, _hoisted_3),
        _ctx.pastValue !== null ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "metricEvolution",
          title: _ctx.translate(
            "General_EvolutionSummaryGeneric",
            _ctx.metricValue,
            _ctx.currentPeriod,
            _ctx.pastValue,
            _ctx.pastPeriod,
            _ctx.metricChangePercent
          )
        }, [
          vue.createElementVNode("span", {
            class: vue.normalizeClass(_ctx.evolutionClass)
          }, vue.toDisplayString(_ctx.metricChangePercent), 3)
        ], 8, _hoisted_4)) : vue.createCommentVNode("", true)
      ])
    ], 2);
  }
  const SingleMetricView = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.SeriesPicker = SeriesPicker;
  exports2.SingleMetricView = SingleMetricView;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
