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

  const _sfc_main$9 = vue.defineComponent({
    name: "EvolutionTrendIcon",
    props: {
      direction: {
        type: String,
        required: true,
        validator: (value) => ["up", "down", "neutral"].indexOf(value) !== -1
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
  const _hoisted_1$9 = {
    key: 0,
    viewBox: "0 0 16 16"
  };
  const _hoisted_2$8 = {
    key: 1,
    viewBox: "0 0 16 16"
  };
  const _hoisted_3$6 = {
    key: 2,
    viewBox: "0 0 16 16"
  };
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    return _ctx.direction === "up" ? (vue.openBlock(), vue.createElementBlock("svg", _hoisted_1$9, [..._cache[0] || (_cache[0] = [
      vue.createElementVNode("path", {
        d: "M3.77344 11L8.27344 5L12.7734 11H3.77344Z",
        fill: "currentColor"
      }, null, -1)
    ])])) : _ctx.direction === "down" ? (vue.openBlock(), vue.createElementBlock("svg", _hoisted_2$8, [..._cache[1] || (_cache[1] = [
      vue.createElementVNode("path", {
        d: "M3.77344 6L8.27344 12L12.7734 6H3.77344Z",
        fill: "currentColor"
      }, null, -1)
    ])])) : (vue.openBlock(), vue.createElementBlock("svg", _hoisted_3$6, [..._cache[2] || (_cache[2] = [
      vue.createElementVNode("rect", {
        x: "3",
        y: "7",
        width: "10",
        height: "2",
        fill: "currentColor"
      }, null, -1)
    ])]));
  }
  const EvolutionTrendIcon = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const _sfc_main$8 = vue.defineComponent({
    name: "EvolutionBadge",
    components: {
      EvolutionTrendIcon
    },
    props: {
      // the change to display, either a number (eg 4, -4) or a pre-formatted
      // string as emitted by Sparklines/Config.php (eg "4%", "-4%")
      percent: {
        type: [Number, String],
        required: true
      },
      // when true the colour is inverted, so a decrease reads as positive (eg bounce rate)
      isLowerValueBetter: {
        type: Boolean,
        default: false
      },
      // raw value difference (currentValue - pastValue); the authoritative source of the
      // arrow direction when available, falling back to the sign of percent otherwise
      trend: {
        type: Number,
        default: void 0
      },
      tooltip: {
        type: String,
        default: ""
      }
    },
    setup(props) {
      const changeValue = vue.computed(() => {
        if (typeof props.trend === "number" && !Number.isNaN(props.trend)) {
          return props.trend;
        }
        const numeric = parseFloat(
          String(props.percent).replace("−", "-").replace(",", ".").replace(/[^0-9.+-]/g, "")
        );
        return Number.isNaN(numeric) ? 0 : numeric;
      });
      const direction = vue.computed(() => {
        if (changeValue.value > 0) {
          return "up";
        }
        if (changeValue.value < 0) {
          return "down";
        }
        return "neutral";
      });
      const directionClass = vue.computed(() => {
        if (direction.value === "neutral") {
          return "evolutionBadge--neutral";
        }
        const increased = direction.value === "up";
        const isPositive = props.isLowerValueBetter ? !increased : increased;
        return isPositive ? "evolutionBadge--positive" : "evolutionBadge--negative";
      });
      const formattedPercent = vue.computed(() => {
        const label = typeof props.percent === "number" ? `${props.percent}%` : String(props.percent).trim();
        const sign = label.charAt(0);
        if (changeValue.value > 0 && sign !== "+" && sign !== "-") {
          return `+${label}`;
        }
        return label;
      });
      return {
        direction,
        directionClass,
        formattedPercent
      };
    }
  });
  const _hoisted_1$8 = ["title"];
  const _hoisted_2$7 = {
    class: "evolutionBadge__icon",
    "aria-hidden": "true"
  };
  const _hoisted_3$5 = { class: "evolutionBadge__value" };
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EvolutionTrendIcon = vue.resolveComponent("EvolutionTrendIcon");
    return vue.openBlock(), vue.createElementBlock("span", {
      class: vue.normalizeClass(["evolutionBadge", _ctx.directionClass]),
      title: _ctx.tooltip || void 0
    }, [
      vue.createElementVNode("span", _hoisted_2$7, [
        vue.createVNode(_component_EvolutionTrendIcon, {
          class: "evolutionTrendIcon",
          direction: _ctx.direction
        }, null, 8, ["direction"])
      ]),
      vue.createElementVNode("span", _hoisted_3$5, vue.toDisplayString(_ctx.formattedPercent), 1)
    ], 10, _hoisted_1$8);
  }
  const EvolutionBadge = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const _sfc_main$7 = vue.defineComponent({
    name: "MetricValue",
    directives: {
      Tooltips: CoreHome.Tooltips
    },
    props: {
      title: {
        type: String,
        required: true
      },
      // Pre-formatted value (e.g. "9,527" or "4min 22s"); rendered verbatim, no formatting here.
      value: {
        type: [String, Number],
        required: true
      },
      // Optional secondary line. Value and label are kept separate so they can be
      // styled independently (e.g. "9,527" darker, "unique visitors" grey). Matomo
      // hands these out separately as metric.value + metric.description.
      secondaryValue: [String, Number],
      secondaryLabel: String,
      // Optional metric documentation; when set it is shown as the title tooltip (otherwise the
      // tooltip falls back to the full title so a clipped title stays recoverable on hover).
      documentation: String
    },
    computed: {
      hasSecondary() {
        return this.secondaryValue !== void 0 && this.secondaryValue !== null && this.secondaryValue !== "";
      }
    }
  });
  const _hoisted_1$7 = { class: "metricValue" };
  const _hoisted_2$6 = ["title"];
  const _hoisted_3$4 = { class: "metricValue__primary" };
  const _hoisted_4$4 = { class: "metricValue__number" };
  const _hoisted_5$2 = {
    key: 0,
    class: "metricValue__secondary"
  };
  const _hoisted_6$2 = { class: "metricValue__secondaryValue" };
  const _hoisted_7$1 = {
    key: 0,
    class: "metricValue__secondaryLabel"
  };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
        class: vue.normalizeClass(["metricValue__title", { "metricValue__title--documented": !!_ctx.documentation }]),
        title: _ctx.documentation || _ctx.title
      }, [
        vue.createTextVNode(vue.toDisplayString(_ctx.title), 1)
      ], 10, _hoisted_2$6)), [
        [_directive_tooltips, { duration: 200, delay: 200 }]
      ]),
      vue.createElementVNode("div", _hoisted_3$4, [
        vue.createElementVNode("span", _hoisted_4$4, vue.toDisplayString(_ctx.value), 1),
        vue.renderSlot(_ctx.$slots, "evolution")
      ]),
      _ctx.hasSecondary ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$2, [
        vue.createElementVNode("span", _hoisted_6$2, vue.toDisplayString(_ctx.secondaryValue), 1),
        _ctx.secondaryLabel ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$1, vue.toDisplayString(_ctx.secondaryLabel), 1)) : vue.createCommentVNode("", true)
      ])) : vue.createCommentVNode("", true)
    ]);
  }
  const MetricValue = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  function getInitialOptionStates$1(allOptions, selectedOptions) {
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
  const _sfc_main$6 = vue.defineComponent({
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
        columnStates: getInitialOptionStates$1(
          this.selectableColumns,
          this.selectedColumns
        ),
        rowStates: getInitialOptionStates$1(
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
  const _hoisted_1$6 = {
    key: 0,
    class: "jqplot-seriespicker-popover"
  };
  const _hoisted_2$5 = { class: "headline" };
  const _hoisted_3$3 = ["onClick"];
  const _hoisted_4$3 = ["type", "checked"];
  const _hoisted_5$1 = {
    key: 0,
    class: "headline recordsToPlot"
  };
  const _hoisted_6$1 = ["onClick"];
  const _hoisted_7 = ["type", "checked"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
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
      _ctx.isPopupVisible ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, [
        vue.createElementVNode("p", _hoisted_2$5, vue.toDisplayString(_ctx.translate(_ctx.multiselect ? "General_MetricsToPlot" : "General_MetricToPlot")), 1),
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
              }, null, 8, _hoisted_4$3),
              vue.createElementVNode("span", null, vue.toDisplayString(columnConfig.translation), 1)
            ])
          ], 8, _hoisted_3$3);
        }), 128)),
        _ctx.selectableRows.length ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_5$1, vue.toDisplayString(_ctx.translate("General_RecordsToPlot")), 1)) : vue.createCommentVNode("", true),
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
          ], 8, _hoisted_6$1);
        }), 128))
      ])) : vue.createCommentVNode("", true)
    ], 34);
  }
  const SeriesPicker = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
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
  const _sfc_main$5 = vue.defineComponent({
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
    methods: {
      unselectOptions(optionStates) {
        Object.keys(optionStates).forEach((optionName) => {
          optionStates[optionName] = false;
        });
      },
      getSelected(optionStates) {
        return Object.keys(optionStates).filter((optionName) => !!optionStates[optionName]);
      },
      optionSelected(optionValue, optionStates) {
        if (!this.multiselect) {
          this.unselectOptions(this.columnStates);
          this.unselectOptions(this.rowStates);
        }
        optionStates[optionValue] = !optionStates[optionValue];
        this.$emit("select", {
          columns: this.getSelected(this.columnStates),
          rows: this.getSelected(this.rowStates)
        });
      }
    }
  });
  const _hoisted_1$5 = ["role", "aria-label"];
  const _hoisted_2$4 = ["type", "checked", "onChange", "onKeydown"];
  const _hoisted_3$2 = { class: "metrics-picker__title" };
  const _hoisted_4$2 = {
    key: 0,
    class: "metrics-picker__headline"
  };
  const _hoisted_5 = ["type", "checked", "onChange", "onKeydown"];
  const _hoisted_6 = { class: "metrics-picker__title" };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "metrics-picker__options",
      role: _ctx.multiselect ? "group" : "radiogroup",
      "aria-label": _ctx.translate("General_ChooseMetrics")
    }, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectableColumns, (columnConfig) => {
        return vue.openBlock(), vue.createElementBlock("label", {
          class: "metrics-picker__column metrics-picker__label",
          key: columnConfig.column
        }, [
          vue.createElementVNode("input", {
            class: "filled-in",
            type: _ctx.multiselect ? "checkbox" : "radio",
            checked: !!_ctx.columnStates[columnConfig.column],
            onChange: ($event) => _ctx.optionSelected(columnConfig.column, _ctx.columnStates),
            onKeydown: vue.withKeys(vue.withModifiers(($event) => _ctx.optionSelected(columnConfig.column, _ctx.columnStates), ["prevent"]), ["enter"])
          }, null, 40, _hoisted_2$4),
          _cache[0] || (_cache[0] = vue.createElementVNode("span", { "aria-hidden": "true" }, null, -1)),
          vue.createElementVNode("span", _hoisted_3$2, vue.toDisplayString(columnConfig.translation), 1)
        ]);
      }), 128)),
      _ctx.selectableRows.length ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_4$2, vue.toDisplayString(_ctx.translate("General_RecordsToPlot")), 1)) : vue.createCommentVNode("", true),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.selectableRows, (rowConfig) => {
        return vue.openBlock(), vue.createElementBlock("label", {
          class: "metrics-picker__row metrics-picker__label",
          key: rowConfig.matcher
        }, [
          vue.createElementVNode("input", {
            class: "filled-in",
            type: _ctx.multiselect ? "checkbox" : "radio",
            checked: !!_ctx.rowStates[rowConfig.matcher],
            onChange: ($event) => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates),
            onKeydown: vue.withKeys(vue.withModifiers(($event) => _ctx.optionSelected(rowConfig.matcher, _ctx.rowStates), ["prevent"]), ["enter"])
          }, null, 40, _hoisted_5),
          _cache[1] || (_cache[1] = vue.createElementVNode("span", { "aria-hidden": "true" }, null, -1)),
          vue.createElementVNode("span", _hoisted_6, vue.toDisplayString(rowConfig.label), 1)
        ]);
      }), 128))
    ], 8, _hoisted_1$5);
  }
  const MetricsPickerOptions = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
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
    components: {
      MetricsPickerOptions
    },
    directives: {
      ExpandOnClick: CoreHome.ExpandOnClick
    },
    emits: ["select"],
    methods: {
      onSelect(selected) {
        this.$emit("select", selected);
        this.$refs.root.classList.remove("expanded");
      }
    }
  });
  const _hoisted_1$4 = {
    ref: "root",
    class: "metrics-picker"
  };
  const _hoisted_2$3 = {
    ref: "expander",
    type: "button",
    class: "metrics-picker__toggle"
  };
  const _hoisted_3$1 = { class: "metrics-picker__toggle-label" };
  const _hoisted_4$1 = { class: "metrics-picker__dropdown" };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MetricsPickerOptions = vue.resolveComponent("MetricsPickerOptions");
    const _directive_expand_on_click = vue.resolveDirective("expand-on-click");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
      vue.createElementVNode("button", _hoisted_2$3, [
        vue.createElementVNode("span", _hoisted_3$1, vue.toDisplayString(_ctx.translate("General_ChooseMetrics")), 1),
        _cache[1] || (_cache[1] = vue.createElementVNode("span", { class: "icon-chevron-down metrics-picker__chevron" }, null, -1))
      ], 512),
      vue.createElementVNode("div", _hoisted_4$1, [
        vue.createVNode(_component_MetricsPickerOptions, {
          multiselect: _ctx.multiselect,
          "selectable-columns": _ctx.selectableColumns,
          "selectable-rows": _ctx.selectableRows,
          "selected-columns": _ctx.selectedColumns,
          "selected-rows": _ctx.selectedRows,
          onSelect: _cache[0] || (_cache[0] = ($event) => _ctx.onSelect($event))
        }, null, 8, ["multiselect", "selectable-columns", "selectable-rows", "selected-columns", "selected-rows"])
      ])
    ])), [
      [_directive_expand_on_click, { expander: "expander" }]
    ]);
  }
  const MetricsPicker = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  function getPastPeriodStr() {
    const { startDate } = CoreHome.Range.getLastNRange(CoreHome.Matomo.period, 2, CoreHome.Matomo.currentDateString);
    const dateRange = CoreHome.Periods.get(CoreHome.Matomo.period).parse(startDate).getDateRange();
    return `${CoreHome.format(dateRange[0])},${CoreHome.format(dateRange[1])}`;
  }
  const { $ } = window;
  const _sfc_main$3 = vue.defineComponent({
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
  const _hoisted_1$3 = { class: "metric-sparkline" };
  const _hoisted_2$2 = { class: "metric-value" };
  const _hoisted_3 = ["title"];
  const _hoisted_4 = ["title"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    var _a, _b, _c, _d, _e;
    const _component_Sparkline = vue.resolveComponent("Sparkline");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["singleMetricView", { "loading": _ctx.isLoading }]),
      ref: "root"
    }, [
      vue.createElementVNode("div", _hoisted_1$3, [
        vue.createVNode(_component_Sparkline, { params: _ctx.sparklineParams }, null, 8, ["params"])
      ]),
      vue.createElementVNode("div", _hoisted_2$2, [
        vue.createElementVNode("span", { title: _ctx.metricDocumentation }, [
          vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.metricValue), 1),
          vue.createTextVNode(" " + vue.toDisplayString((_ctx.metricTranslation || "").toLowerCase()), 1)
        ], 8, _hoisted_3),
        _ctx.pastValue !== null ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "metricEvolution",
          title: _ctx.translate(
            "General_EvolutionSummaryGeneric",
            String((_a = _ctx.metricValue) != null ? _a : ""),
            String((_b = _ctx.currentPeriod) != null ? _b : ""),
            String((_c = _ctx.pastValue) != null ? _c : ""),
            String((_d = _ctx.pastPeriod) != null ? _d : ""),
            String((_e = _ctx.metricChangePercent) != null ? _e : "")
          )
        }, [
          vue.createElementVNode("span", {
            class: vue.normalizeClass(_ctx.evolutionClass)
          }, vue.toDisplayString(_ctx.metricChangePercent), 3)
        ], 8, _hoisted_4)) : vue.createCommentVNode("", true)
      ])
    ], 2);
  }
  const SingleMetricView = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    name: "NoComparison",
    components: {
      MetricValue,
      EvolutionBadge,
      Sparkline: CoreHome.Sparkline
    },
    props: {
      sparkline: {
        type: Object,
        required: true
      },
      // Backend map of metric column -> documentation string (from Sparklines.php).
      allMetricsDocumentation: {
        type: Object,
        default: () => ({})
      }
    },
    setup(props) {
      const primaryMetric = vue.computed(
        () => {
          var _a, _b;
          return (_b = (_a = props.sparkline.metrics) == null ? void 0 : _a[""]) == null ? void 0 : _b[0];
        }
      );
      const secondaryMetric = vue.computed(
        () => {
          var _a, _b;
          return (_b = (_a = props.sparkline.metrics) == null ? void 0 : _a[""]) == null ? void 0 : _b[1];
        }
      );
      const title = vue.computed(
        () => {
          var _a, _b;
          return ((_a = primaryMetric.value) == null ? void 0 : _a.title) || ((_b = primaryMetric.value) == null ? void 0 : _b.description) || "";
        }
      );
      const documentation = vue.computed(
        () => {
          var _a, _b;
          return props.allMetricsDocumentation[(_b = (_a = primaryMetric.value) == null ? void 0 : _a.column) != null ? _b : ""] || void 0;
        }
      );
      const formatValue = (value) => typeof value === "number" ? CoreHome.NumberFormatter.formatNumber(value, 2) : value;
      const primaryValue = vue.computed(() => {
        var _a, _b;
        return (_b = formatValue((_a = primaryMetric.value) == null ? void 0 : _a.value)) != null ? _b : "";
      });
      const secondaryValue = vue.computed(() => {
        var _a;
        return formatValue((_a = secondaryMetric.value) == null ? void 0 : _a.value);
      });
      const secondaryLabel = vue.computed(() => {
        var _a;
        return (_a = secondaryMetric.value) == null ? void 0 : _a.description;
      });
      return {
        title,
        documentation,
        primaryValue,
        secondaryValue,
        secondaryLabel
      };
    }
  });
  const _hoisted_1$2 = { class: "noComparison" };
  const _hoisted_2$1 = { class: "sparklineSlot" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    const _component_EvolutionBadge = vue.resolveComponent("EvolutionBadge");
    const _component_MetricValue = vue.resolveComponent("MetricValue");
    const _component_Sparkline = vue.resolveComponent("Sparkline");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createVNode(_component_MetricValue, {
        title: _ctx.title,
        value: _ctx.primaryValue,
        "secondary-value": _ctx.secondaryValue,
        "secondary-label": _ctx.secondaryLabel,
        documentation: _ctx.documentation
      }, vue.createSlots({ _: 2 }, [
        _ctx.sparkline.evolution ? {
          name: "evolution",
          fn: vue.withCtx(() => [
            vue.createVNode(_component_EvolutionBadge, {
              percent: _ctx.sparkline.evolution.percent,
              trend: _ctx.sparkline.evolution.trend,
              "is-lower-value-better": _ctx.sparkline.evolution.isLowerValueBetter,
              tooltip: _ctx.sparkline.evolution.tooltip || ""
            }, null, 8, ["percent", "trend", "is-lower-value-better", "tooltip"])
          ]),
          key: "0"
        } : void 0
      ]), 1032, ["title", "value", "secondary-value", "secondary-label", "documentation"]),
      vue.createElementVNode("div", _hoisted_2$1, [
        vue.createVNode(_component_Sparkline, {
          width: 380,
          height: 40,
          params: _ctx.sparkline.url,
          "series-indices": (_a = _ctx.sparkline.seriesIndices) != null ? _a : void 0
        }, null, 8, ["params", "series-indices"])
      ])
    ]);
  }
  const NoComparison = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    name: "SparklineCard",
    components: {
      NoComparison
    },
    props: {
      sparkline: {
        type: Object,
        required: true
      },
      areSparklinesLinkable: {
        type: Boolean,
        default: true
      },
      // Backend map of metric column -> documentation string, forwarded to the body component.
      allMetricsDocumentation: {
        type: Object,
        default: () => ({})
      }
    },
    setup(props) {
      const graphParamsAttr = vue.computed(() => {
        const { graphParams, url } = props.sparkline;
        if (graphParams && Object.keys(graphParams).length) {
          return JSON.stringify(graphParams);
        }
        if (url) {
          const parsed = CoreHome.MatomoUrl.parse(url.substring(url.indexOf("?") + 1));
          const derived = {};
          ["columns", "rows", "idGoal"].forEach((key) => {
            if (parsed[key]) {
              derived[key] = parsed[key];
            }
          });
          if (Object.keys(derived).length) {
            return JSON.stringify(derived);
          }
        }
        return null;
      });
      const seriesIndicesAttr = vue.computed(() => {
        const { seriesIndices } = props.sparkline;
        return seriesIndices && seriesIndices.length ? JSON.stringify(seriesIndices) : null;
      });
      return {
        graphParamsAttr,
        seriesIndicesAttr
      };
    }
  });
  const _hoisted_1$1 = ["data-graph-params", "data-series-indices"];
  const _hoisted_2 = {
    key: 0,
    class: "sparklineCard__title"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_NoComparison = vue.resolveComponent("NoComparison");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["sparkline sparklineCard", { notLinkable: !_ctx.areSparklinesLinkable }]),
      "data-graph-params": _ctx.graphParamsAttr,
      "data-series-indices": _ctx.seriesIndicesAttr
    }, [
      _ctx.sparkline.title ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, vue.toDisplayString(_ctx.sparkline.title), 1)) : vue.createCommentVNode("", true),
      vue.createVNode(_component_NoComparison, {
        sparkline: _ctx.sparkline,
        "all-metrics-documentation": _ctx.allMetricsDocumentation
      }, null, 8, ["sparkline", "all-metrics-documentation"])
    ], 10, _hoisted_1$1);
  }
  const SparklineCard = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    name: "SparklinesGrid",
    components: {
      SparklineCard
    },
    props: {
      sparklines: {
        type: Object,
        required: true
      },
      areSparklinesLinkable: {
        type: Boolean,
        default: true
      },
      // Backend map of metric column -> documentation string, forwarded to each card for the
      // metric-title tooltip.
      allMetricsDocumentation: {
        type: Object,
        default: () => ({})
      },
      isWidget: {
        type: Boolean,
        default: false
      }
    },
    setup(props) {
      const flatSparklines = vue.computed(
        () => [].concat(...Object.values(props.sparklines || {})).filter((sparkline) => !!sparkline.url).sort((a, b) => a.order - b.order)
      );
      const columnClasses = vue.computed(() => props.isWidget ? "col s6" : "col s6 m6 l4 xl3");
      vue.onMounted(() => {
        vue.nextTick(() => {
          window.initializeSparklines();
        });
      });
      return {
        flatSparklines,
        columnClasses
      };
    }
  });
  const _hoisted_1 = { class: "row sparklinesGrid" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SparklineCard = vue.resolveComponent("SparklineCard");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.flatSparklines, (sparkline, index) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          key: index,
          class: vue.normalizeClass(_ctx.columnClasses)
        }, [
          vue.createVNode(_component_SparklineCard, {
            sparkline,
            "are-sparklines-linkable": _ctx.areSparklinesLinkable,
            "all-metrics-documentation": _ctx.allMetricsDocumentation
          }, null, 8, ["sparkline", "are-sparklines-linkable", "all-metrics-documentation"])
        ], 2);
      }), 128))
    ]);
  }
  const SparklinesGrid = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.EvolutionBadge = EvolutionBadge;
  exports2.MetricValue = MetricValue;
  exports2.MetricsPicker = MetricsPicker;
  exports2.SeriesPicker = SeriesPicker;
  exports2.SingleMetricView = SingleMetricView;
  exports2.SparklinesGrid = SparklinesGrid;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
