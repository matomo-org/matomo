(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Dashboard = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
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
  class DashboardStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        dashboards: []
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "dashboards", vue.computed(() => this.state.value.dashboards));
      __publicField(this, "dashboardsPromise", null);
    }
    getDashboard(dashboardId) {
      return this.getAllDashboards().then(
        (dashboards) => dashboards.find(
          (b) => parseInt(`${b.id}`, 10) === parseInt(`${dashboardId}`, 10)
        )
      );
    }
    getDashboardLayout(dashboardId) {
      return CoreHome.AjaxHelper.fetch(
        {
          module: "Dashboard",
          action: "getDashboardLayout",
          idDashboard: dashboardId
        },
        {
          withTokenInUrl: true
        }
      );
    }
    reloadAllDashboards() {
      this.dashboardsPromise = null;
      return this.getAllDashboards();
    }
    getAllDashboards() {
      if (!this.dashboardsPromise) {
        this.dashboardsPromise = CoreHome.AjaxHelper.fetch({
          method: "Dashboard.getDashboards",
          filter_limit: "-1"
        }).then((response) => {
          if (response) {
            this.privateState.dashboards = response;
          }
          return this.dashboards.value;
        });
      }
      return this.dashboardsPromise;
    }
  }
  const DashboardStore$1 = new DashboardStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $: $$1 } = window;
  function renderDashboard(dashboardId, dashboard, layout) {
    const $settings = $$1(".dashboardSettings");
    $settings.show();
    window.initTopControls();
    if (!$$1("#topBars").length) {
      $settings.after($$1("#Dashboard"));
      $$1("#Dashboard ul li").removeClass("active");
      $$1(`#Dashboard_embeddedIndex_${dashboardId}`).addClass("active");
    }
    window.widgetsHelper.getAvailableWidgets();
    $$1("#dashboardWidgetsArea").off("dashboardempty", window.showEmptyDashboardNotification).on("dashboardempty", window.showEmptyDashboardNotification).dashboard({
      idDashboard: dashboardId,
      layout,
      name: dashboard ? dashboard.name : ""
    });
    const divElements = $$1("#columnPreview").find(">div");
    divElements.each(function eachPreview() {
      const width = [];
      $$1("div", this).each(function eachDiv() {
        width.push(this.className.replace(/width-/, ""));
      });
      $$1(this).attr("layout", width.join("-"));
    });
    divElements.off("click.renderDashboard");
    divElements.on("click.renderDashboard", function onRenderDashboard() {
      divElements.removeClass("choosen");
      $$1(this).addClass("choosen");
    });
  }
  function fetchDashboard(dashboardId) {
    return new Promise((resolve) => setTimeout(resolve)).then(
      () => Promise.resolve(window.widgetsHelper.firstGetAvailableWidgetsCall)
    ).then(() => {
      const dashboardElement = $$1("#dashboardWidgetsArea");
      dashboardElement.dashboard("destroyWidgets");
      dashboardElement.empty();
      return Promise.all([
        DashboardStore$1.getDashboard(dashboardId),
        DashboardStore$1.getDashboardLayout(dashboardId)
      ]);
    }).then(([dashboard, layout]) => new Promise((resolve) => {
      $$1(() => {
        renderDashboard(dashboardId, dashboard, layout);
        resolve();
      });
    }));
  }
  function clearDashboard() {
    $$1(".top_controls .dashboard-manager").hide();
    $$1("#dashboardWidgetsArea").dashboard("destroy");
  }
  function onLocationChange(parsed) {
    if (parsed.module !== "Widgetize" && parsed.category !== "Dashboard_Dashboard") {
      clearDashboard();
    }
  }
  function onLoadDashboard(idDashboard) {
    fetchDashboard(idDashboard);
  }
  const Dashboard = {
    mounted(el, binding) {
      fetchDashboard(binding.value.idDashboard);
      vue.watch(() => CoreHome.MatomoUrl.parsed.value, (parsed) => {
        onLocationChange(parsed);
      });
      CoreHome.Matomo.off("Dashboard.loadDashboard", onLoadDashboard);
      CoreHome.Matomo.on("Dashboard.loadDashboard", onLoadDashboard);
    },
    unmounted() {
      onLocationChange(CoreHome.MatomoUrl.parsed.value);
      CoreHome.Matomo.off("Dashboard.loadDashboard", onLoadDashboard);
    }
  };
  const _sfc_main$4 = vue.defineComponent({
    name: "CategoryList",
    props: {
      categories: {
        type: Array,
        required: true
      },
      chosenCategory: {
        type: String,
        default: null
      }
    },
    emits: ["update:chosenCategory", "confirm"],
    methods: {
      selectCategory(category) {
        this.$emit("update:chosenCategory", category);
      },
      // Keyboard-only: selecting via Enter/Space also signals "advance focus to
      // the widgets list", so a keyboard user isn't stranded on the category
      // they just confirmed. Mouse/touch paths intentionally don't emit this.
      confirmCategory(category) {
        this.selectCategory(category);
        this.$emit("confirm");
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
  const _hoisted_1$4 = { class: "widgetpreview-base widgetpreview-categorylist" };
  const _hoisted_2$4 = ["onMouseover", "onClick", "onFocus", "onKeydown"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$4, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.categories, (category) => {
        return vue.openBlock(), vue.createElementBlock("li", {
          key: category,
          class: vue.normalizeClass([{ "widgetpreview-choosen": category === _ctx.chosenCategory }, "category-list-item"])
        }, [
          vue.createElementVNode("button", {
            type: "button",
            class: "category-button-item",
            onMouseover: ($event) => _ctx.selectCategory(category),
            onClick: ($event) => _ctx.selectCategory(category),
            onFocus: ($event) => _ctx.selectCategory(category),
            onKeydown: [
              vue.withKeys(vue.withModifiers(($event) => _ctx.confirmCategory(category), ["prevent"]), ["enter"]),
              vue.withKeys(vue.withModifiers(($event) => _ctx.confirmCategory(category), ["prevent"]), ["space"])
            ]
          }, vue.toDisplayString(category), 41, _hoisted_2$4)
        ], 2);
      }), 128))
    ]);
  }
  const CategoryList = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const HOVER_DELAY_MS = 400;
  const KPI_METRIC_CATEGORY_ID = "General_KpiMetric";
  function hasHoverCapablePointer() {
    return typeof window !== "undefined" && typeof window.matchMedia === "function" && window.matchMedia("(any-hover: hover)").matches;
  }
  const _sfc_main$3 = vue.defineComponent({
    name: "WidgetsList",
    props: {
      widgets: {
        type: Array,
        required: true
      },
      chosenWidgetId: {
        type: String,
        default: null
      },
      addedWidgets: {
        type: Object,
        default: () => /* @__PURE__ */ new Set()
      },
      existingWidgetIds: {
        type: Object,
        default: () => /* @__PURE__ */ new Set()
      }
    },
    emits: ["hover", "select"],
    data() {
      return {
        hoverTimer: null,
        // Cached once: any hover-capable pointer gets desktop-like click-to-add
        // behaviour. Only pure no-hover environments use preview-first double-tap.
        supportsHover: hasHoverCapablePointer(),
        // The row most recently added in this session. Drives the transient green
        // check in the add hint; cleared as soon as the hover moves elsewhere (see
        // the chosenWidgetId watcher) so re-hovering an added row shows "+" again.
        justAddedId: null
      };
    },
    watch: {
      chosenWidgetId(newId) {
        if (newId !== this.justAddedId) {
          this.justAddedId = null;
        }
      }
    },
    methods: {
      translate: CoreHome.translate,
      isRepeatableWidget(widget) {
        var _a2;
        return ((_a2 = widget.category) == null ? void 0 : _a2.id) === KPI_METRIC_CATEGORY_ID;
      },
      isJustAdded(widget) {
        return !!widget.uniqueId && widget.uniqueId === this.justAddedId;
      },
      isUnavailable(widget) {
        if (!widget.uniqueId) {
          return false;
        }
        if (this.addedWidgets.has(widget.uniqueId)) {
          return true;
        }
        if (this.isRepeatableWidget(widget)) {
          return false;
        }
        return this.existingWidgetIds.has(widget.uniqueId);
      },
      onMouseEnter(widget) {
        if (!widget.uniqueId) {
          return;
        }
        this.clearHoverTimer();
        const { uniqueId } = widget;
        this.hoverTimer = window.setTimeout(() => {
          this.hoverTimer = null;
          this.$emit("hover", uniqueId);
        }, HOVER_DELAY_MS);
      },
      onMouseLeave(widget) {
        if (this.isUnavailable(widget)) {
          return;
        }
        this.clearHoverTimer();
      },
      onRowClick(widget) {
        if (!widget.uniqueId) {
          return;
        }
        this.clearHoverTimer();
        if (!this.supportsHover && widget.uniqueId !== this.chosenWidgetId) {
          this.$emit("hover", widget.uniqueId);
          return;
        }
        this.justAddedId = widget.uniqueId;
        this.$emit("select", widget.uniqueId);
      },
      // Keyboard activation (Enter / Space). Bypasses the touch double-tap branch in
      // onRowClick on purpose — a keypress is not a touch interaction, so a focused
      // row should add immediately even when supportsHover is false.
      onActivate(widget) {
        if (!widget.uniqueId) {
          return;
        }
        this.clearHoverTimer();
        this.justAddedId = widget.uniqueId;
        this.$emit("select", widget.uniqueId);
      },
      focusFirst() {
        const list = this.$refs.list;
        const first = list == null ? void 0 : list.querySelector("li button");
        if (first instanceof HTMLElement) {
          first.focus();
        }
      },
      clearHoverTimer() {
        if (this.hoverTimer !== null) {
          window.clearTimeout(this.hoverTimer);
          this.hoverTimer = null;
        }
      }
    },
    beforeUnmount() {
      this.clearHoverTimer();
    }
  });
  const _hoisted_1$3 = {
    ref: "list",
    class: "widgetpreview-base widgetpreview-widgetlist"
  };
  const _hoisted_2$3 = ["uniqueid"];
  const _hoisted_3$3 = ["onMouseenter", "onMouseleave", "onFocus", "onBlur", "onClick", "onKeydown"];
  const _hoisted_4$2 = { class: "widgetpreview-widgetname" };
  const _hoisted_5$2 = {
    class: "widgetpreview-add-hint",
    "aria-hidden": "true"
  };
  const _hoisted_6$2 = {
    key: 0,
    class: "icon-ok widgetpreview-add-check"
  };
  const _hoisted_7$1 = {
    key: 1,
    class: "widgetpreview-add-plus"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$3, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.widgets, (widget) => {
        return vue.openBlock(), vue.createElementBlock("li", {
          key: widget.uniqueId,
          uniqueid: widget.uniqueId,
          class: vue.normalizeClass([{
            "widgetpreview-choosen": widget.uniqueId === _ctx.chosenWidgetId,
            "widgetpreview-unavailable": _ctx.isUnavailable(widget)
          }, "widget-list-item"])
        }, [
          vue.createElementVNode("button", {
            type: "button",
            class: "widget-button-item",
            onMouseenter: ($event) => _ctx.onMouseEnter(widget),
            onMouseleave: ($event) => _ctx.onMouseLeave(widget),
            onFocus: ($event) => _ctx.onMouseEnter(widget),
            onBlur: ($event) => _ctx.onMouseLeave(widget),
            onClick: vue.withModifiers(($event) => _ctx.onRowClick(widget), ["prevent"]),
            onKeydown: [
              vue.withKeys(vue.withModifiers(($event) => _ctx.onActivate(widget), ["prevent"]), ["enter"]),
              vue.withKeys(vue.withModifiers(($event) => _ctx.onActivate(widget), ["prevent"]), ["space"])
            ]
          }, [
            vue.createElementVNode("span", _hoisted_4$2, vue.toDisplayString(widget.name), 1),
            vue.createElementVNode("span", _hoisted_5$2, [
              _ctx.isJustAdded(widget) ? (vue.openBlock(), vue.createElementBlock("i", _hoisted_6$2)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$1, "+")),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(_ctx.isJustAdded(widget) ? "General_Added" : "General_Add")), 1)
            ])
          ], 40, _hoisted_3$3)
        ], 10, _hoisted_2$3);
      }), 128))
    ], 512);
  }
  const WidgetsList = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    name: "WidgetPreview",
    components: {
      ReportHeader: CoreHome.ReportHeader,
      Widget: CoreHome.Widget
    },
    props: {
      widget: {
        type: Object,
        default: null
      }
    },
    emits: ["select"],
    computed: {
      previewWidget() {
        if (!this.widget) {
          return null;
        }
        const result = __spreadProps(__spreadValues({}, this.widget), {
          parameters: this.getPreviewParameters(this.widget.parameters)
        });
        if (this.isContainerWidget(result)) {
          result.widgets = this.getPreviewChildren(result);
        }
        return result;
      }
    },
    mounted() {
      CoreHome.Matomo.on("widget:loaded", this.onWidgetLoaded);
    },
    unmounted() {
      CoreHome.Matomo.off("widget:loaded", this.onWidgetLoaded);
    },
    methods: {
      translate: CoreHome.translate,
      shouldDisableLink() {
        const urlFlag = CoreHome.Matomo.broadcast.getValueFromUrl("disableLink");
        if (urlFlag && urlFlag.length) {
          return true;
        }
        return !!document.querySelector("body#standalone");
      },
      getPreviewParameters(parameters = {}) {
        return __spreadValues(__spreadProps(__spreadValues({}, parameters), {
          widget: "1",
          showtitle: "0"
        }), this.shouldDisableLink() ? { disableLink: "1" } : {});
      },
      isContainerWidget(widget) {
        return !!widget.isContainer && Array.isArray(widget.widgets);
      },
      getPreviewChildren(widget) {
        var _a2;
        const containerId = (_a2 = widget.parameters) == null ? void 0 : _a2.containerId;
        return widget.widgets.map((child) => __spreadProps(__spreadValues({}, child), {
          parameters: __spreadValues(__spreadProps(__spreadValues({}, child.parameters), {
            widget: "1"
          }), containerId ? { containerId } : {})
        }));
      },
      onWidgetLoaded(payload) {
        var _a2, _b;
        if (!this.widget || ((_a2 = payload.parameters) == null ? void 0 : _a2.uniqueId) !== this.widget.uniqueId) {
          return;
        }
        const root = this.$el;
        const loadedElement = (_b = payload == null ? void 0 : payload.element) == null ? void 0 : _b[0];
        if (!root || !loadedElement || !root.contains(loadedElement)) {
          return;
        }
        const widget = root.querySelector(".widget");
        const widgetContent = widget == null ? void 0 : widget.querySelector(".widgetContent");
        if (!widget || !widgetContent) {
          return;
        }
        window.$(widgetContent).trigger("widget:create", [{ element: window.$(widget) }]);
      }
    }
  });
  const _hoisted_1$2 = { class: "widgetpreview-preview" };
  const _hoisted_2$2 = {
    key: 0,
    class: "widget"
  };
  const _hoisted_3$2 = { class: "widgetContent" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ReportHeader = vue.resolveComponent("ReportHeader");
    const _component_Widget = vue.resolveComponent("Widget");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      _ctx.previewWidget ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$2, [
        vue.createVNode(_component_ReportHeader, {
          context: "preview",
          title: _ctx.translate("Dashboard_WidgetPreview"),
          "title-clickable": "",
          "title-click-hint": _ctx.translate("Dashboard_AddPreviewedWidget"),
          onTitleClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("select", _ctx.previewWidget.uniqueId))
        }, null, 8, ["title", "title-click-hint"]),
        vue.createElementVNode("div", _hoisted_3$2, [
          (vue.openBlock(), vue.createBlock(_component_Widget, {
            key: _ctx.previewWidget.uniqueId,
            widget: _ctx.previewWidget,
            widgetized: true,
            "suppress-notifications": true
          }, null, 8, ["widget"]))
        ])
      ])) : vue.createCommentVNode("", true)
    ]);
  }
  const WidgetPreview = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const OPEN_EVENT = "Dashboard.AddWidget.open";
  const _sfc_main$1 = vue.defineComponent({
    name: "AddWidgetModal",
    components: {
      MatomoModal: CoreHome.MatomoModal,
      CategoryList,
      WidgetsList,
      WidgetPreview
    },
    emits: ["select"],
    data() {
      return {
        isOpen: false,
        chosenCategory: null,
        hoveredWidgetId: null,
        addedWidgetIds: /* @__PURE__ */ new Set(),
        existingWidgetIds: /* @__PURE__ */ new Set()
      };
    },
    computed: {
      widgets() {
        return CoreHome.WidgetsStore.widgets.value || {};
      },
      categoryNames() {
        return Object.keys(this.widgets);
      },
      widgetsInCategory() {
        if (!this.chosenCategory) {
          return [];
        }
        return this.widgets[this.chosenCategory] || [];
      },
      widgetsById() {
        return new Map(
          Object.values(this.widgets).flat().filter((w) => !!w.uniqueId).map((w) => [w.uniqueId, w])
        );
      },
      previewWidget() {
        if (!this.hoveredWidgetId) {
          return null;
        }
        return this.widgetsById.get(this.hoveredWidgetId) || null;
      }
    },
    methods: {
      translate: CoreHome.translate,
      open() {
        const ids = /* @__PURE__ */ new Set();
        document.querySelectorAll("#dashboardWidgetsArea [widgetId]").forEach((el) => {
          const id = el.getAttribute("widgetId");
          if (id) {
            ids.add(id);
          }
        });
        this.existingWidgetIds = ids;
        this.isOpen = true;
      },
      close() {
        this.isOpen = false;
      },
      onClosed() {
        this.chosenCategory = null;
        this.hoveredWidgetId = null;
        this.addedWidgetIds = /* @__PURE__ */ new Set();
        this.existingWidgetIds = /* @__PURE__ */ new Set();
      },
      onCategoryChosen(category) {
        if (this.chosenCategory === category) {
          return;
        }
        this.chosenCategory = category;
        this.hoveredWidgetId = null;
      },
      focusWidgetList() {
        return __async(this, null, function* () {
          yield this.$nextTick();
          const widgetsList = this.$refs.widgetsList;
          if (widgetsList && typeof widgetsList.focusFirst === "function") {
            widgetsList.focusFirst();
          }
        });
      },
      onWidgetHover(uniqueId) {
        this.hoveredWidgetId = uniqueId;
      },
      onSelect(uniqueId) {
        const widget = this.widgetsById.get(uniqueId);
        if (widget) {
          this.addedWidgetIds.add(uniqueId);
          this.$emit("select", widget);
          return;
        }
        console.warn(`Could not resolve dashboard widget "${uniqueId}" from cached metadata.`);
        this.close();
      }
    },
    mounted() {
      CoreHome.Matomo.on(OPEN_EVENT, this.open);
    },
    unmounted() {
      CoreHome.Matomo.off(OPEN_EVENT, this.open);
    }
  });
  const _hoisted_1$1 = ["aria-label"];
  const _hoisted_2$1 = { class: "add-widget-modal-title" };
  const _hoisted_3$1 = { class: "add-widget-modal-body widgetpreview-base" };
  const _hoisted_4$1 = { class: "add-widget-modal-categories" };
  const _hoisted_5$1 = { class: "add-widget-modal-widgets" };
  const _hoisted_6$1 = { class: "add-widget-modal-preview" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_category_list = vue.resolveComponent("category-list");
    const _component_widgets_list = vue.resolveComponent("widgets-list");
    const _component_widget_preview = vue.resolveComponent("widget-preview");
    const _component_matomo_modal = vue.resolveComponent("matomo-modal");
    return vue.openBlock(), vue.createBlock(_component_matomo_modal, {
      modelValue: _ctx.isOpen,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.isOpen = $event),
      classes: "add-widget-modal",
      "content-class": "add-widget-modal-content",
      "aria-label": _ctx.translate("Dashboard_AddAWidget"),
      onClosed: _ctx.onClosed
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("button", {
          type: "button",
          class: "btn-close modal-close",
          "aria-label": _ctx.translate("General_Close"),
          onClick: _cache[0] || (_cache[0] = (...args) => _ctx.close && _ctx.close(...args))
        }, [..._cache[2] || (_cache[2] = [
          vue.createElementVNode("i", { class: "icon-close" }, null, -1)
        ])], 8, _hoisted_1$1),
        vue.createElementVNode("h3", _hoisted_2$1, vue.toDisplayString(_ctx.translate("Dashboard_AddAWidget")), 1),
        vue.createElementVNode("div", _hoisted_3$1, [
          vue.createElementVNode("div", _hoisted_4$1, [
            vue.createVNode(_component_category_list, {
              categories: _ctx.categoryNames,
              "chosen-category": _ctx.chosenCategory,
              "onUpdate:chosenCategory": _ctx.onCategoryChosen,
              onConfirm: _ctx.focusWidgetList
            }, null, 8, ["categories", "chosen-category", "onUpdate:chosenCategory", "onConfirm"])
          ]),
          vue.createElementVNode("div", _hoisted_5$1, [
            vue.createVNode(_component_widgets_list, {
              ref: "widgetsList",
              widgets: _ctx.widgetsInCategory,
              "chosen-widget-id": _ctx.hoveredWidgetId,
              "added-widgets": _ctx.addedWidgetIds,
              "existing-widget-ids": _ctx.existingWidgetIds,
              onHover: _ctx.onWidgetHover,
              onSelect: _ctx.onSelect
            }, null, 8, ["widgets", "chosen-widget-id", "added-widgets", "existing-widget-ids", "onHover", "onSelect"])
          ]),
          vue.createElementVNode("div", _hoisted_6$1, [
            vue.createVNode(_component_widget_preview, {
              widget: _ctx.previewWidget,
              onSelect: _ctx.onSelect
            }, null, 8, ["widget", "onSelect"])
          ])
        ])
      ]),
      _: 1
    }, 8, ["modelValue", "aria-label", "onClosed"]);
  }
  const AddWidgetModal = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const { $ } = window;
  const DASHBOARD_EXPORT_STORAGE_KEY = "scheduledReports.dashboardExportId";
  const _sfc_main = vue.defineComponent({
    name: "DashboardSettings",
    components: {
      AddWidgetModal
    },
    directives: {
      ExpandOnClick: CoreHome.ExpandOnClick,
      Tooltips: CoreHome.Tooltips
    },
    data() {
      return {
        isActionDisabled: {},
        actionTooltips: {}
      };
    },
    setup() {
      const root = vue.ref(null);
      vue.onMounted(() => {
        CoreHome.Matomo.postEvent("Dashboard.DashboardSettings.mounted", root.value);
        $(root.value).hide();
      });
      return {
        root
      };
    },
    computed: {
      isUserNotAnonymous() {
        return !!CoreHome.Matomo.userLogin && CoreHome.Matomo.userLogin !== "anonymous";
      },
      isSuperUser() {
        return this.isUserNotAnonymous && CoreHome.Matomo.hasSuperUserAccess;
      },
      isUserHasSomeAdminAccess() {
        return this.isUserNotAnonymous && CoreHome.Matomo.userHasSomeAdminAccess;
      },
      dashboardActions() {
        const result = {
          resetDashboard: "Dashboard_ResetDashboard",
          showChangeDashboardLayoutDialog: "Dashboard_ChangeDashboardLayout"
        };
        if (this.isUserNotAnonymous) {
          result.renameDashboard = "Dashboard_RenameDashboard";
          result.removeDashboard = "Dashboard_RemoveDashboard";
        }
        if (this.isSuperUser) {
          result.setAsDefaultWidgets = "Dashboard_SetAsDefaultWidgets";
        }
        if (this.isUserHasSomeAdminAccess) {
          result.copyDashboardToUser = "Dashboard_CopyDashboardToUser";
        }
        return result;
      },
      generalActions() {
        const result = {};
        if (this.isUserNotAnonymous) {
          result.createDashboard = "Dashboard_CreateNewDashboard";
        }
        return result;
      }
    },
    methods: {
      onClickAction(event, action) {
        if (event.target.getAttribute("disabled")) {
          return;
        }
        window[action]();
      },
      onOpen() {
        if ($("#dashboardWidgetsArea").dashboard("isDefaultDashboard")) {
          this.isActionDisabled.removeDashboard = true;
          this.actionTooltips.removeDashboard = CoreHome.translate("Dashboard_RemoveDefaultDashboardNotPossible");
        } else {
          this.isActionDisabled.removeDashboard = false;
          this.actionTooltips.removeDashboard = void 0;
        }
      },
      onExpand(event) {
        if (event.detail !== 0) {
          return;
        }
        this.$nextTick(() => {
          const firstAction = this.$refs.root.querySelector(".mtm-dropdownPanel__menu button:not([disabled])");
          if (firstAction) {
            firstAction.focus();
          }
        });
      },
      onFocusOut(event) {
        const root = this.$refs.root;
        const newTarget = event.relatedTarget;
        if (newTarget && root.contains(newTarget)) {
          return;
        }
        root.classList.remove("expanded");
      },
      onClosed(event) {
        if (!(event instanceof KeyboardEvent)) {
          return;
        }
        const expander = this.$refs.expander;
        if (expander) {
          expander.focus();
        }
      },
      openAddWidget() {
        this.$refs.root.classList.remove("expanded");
        CoreHome.Matomo.postEvent("Dashboard.AddWidget.open");
      },
      onWidgetSelected(widget) {
        $("#dashboardWidgetsArea").dashboard("addWidget", widget.uniqueId, 1, widget.parameters, true, false);
      },
      redirectToCreateScheduledReports() {
        const query = __spreadValues({}, CoreHome.MatomoUrl.urlParsed.value);
        delete query.category;
        delete query.subcategory;
        delete query.idDashboard;
        query.module = "ScheduledReports";
        query.action = "index";
        const hash = __spreadValues({}, CoreHome.MatomoUrl.hashParsed.value);
        delete hash.category;
        delete hash.subcategory;
        delete hash.idDashboard;
        CoreHome.MatomoUrl.updateUrl(query, hash);
      },
      redirectToLoginPage() {
        const loginQuery = {
          module: CoreHome.Matomo.getLoginModule()
        };
        CoreHome.MatomoUrl.updateUrl(loginQuery);
      },
      onClickExportDashboard() {
        if (typeof sessionStorage !== "undefined") {
          sessionStorage.removeItem(DASHBOARD_EXPORT_STORAGE_KEY);
        }
        if (this.isUserNotAnonymous) {
          const dashboardId = this.getCurrentDashboardId();
          if (dashboardId !== null && typeof sessionStorage !== "undefined") {
            sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, String(dashboardId));
          }
          this.redirectToCreateScheduledReports();
          return;
        }
        this.redirectToLoginPage();
      },
      normalizeDashboardId(value) {
        const candidate = Array.isArray(value) ? value[0] : value;
        if (candidate === null || candidate === void 0) {
          return null;
        }
        const normalized = String(candidate).trim();
        if (!/^[1-9]\d*$/.test(normalized)) {
          return null;
        }
        return Number(normalized);
      },
      getCurrentDashboardId() {
        const fromSubcategory = this.normalizeDashboardId(CoreHome.MatomoUrl.getSearchParam("subcategory"));
        if (fromSubcategory !== null) {
          return fromSubcategory;
        }
        const fromQueryIdDashboard = this.normalizeDashboardId(CoreHome.MatomoUrl.urlParsed.value.idDashboard);
        if (fromQueryIdDashboard !== null) {
          return fromQueryIdDashboard;
        }
        return this.normalizeDashboardId(CoreHome.MatomoUrl.hashParsed.value.idDashboard);
      }
    }
  });
  const _hoisted_1 = ["title"];
  const _hoisted_2 = { class: "piwikSelector__dropdown positionInViewport" };
  const _hoisted_3 = { class: "mtm-dropdownPanel mtm-dropdownPanel--wide" };
  const _hoisted_4 = { class: "mtm-dropdownPanel__menu" };
  const _hoisted_5 = ["onClick", "disabled", "title", "data-action"];
  const _hoisted_6 = { class: "mtm-dropdownPanel__menuLabel" };
  const _hoisted_7 = { class: "mtm-dropdownPanel__menuItem" };
  const _hoisted_8 = { class: "mtm-dropdownPanel__menuLabel" };
  const _hoisted_9 = ["onClick", "disabled", "title", "data-action"];
  const _hoisted_10 = { class: "mtm-dropdownPanel__menuLabel" };
  const _hoisted_11 = { class: "mtm-dropdownPanel__menuItem mtm-dropdownPanel__menuItem--addWidget" };
  const _hoisted_12 = { class: "mtm-dropdownPanel__menuLabel" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_AddWidgetModal = vue.resolveComponent("AddWidgetModal");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    const _directive_expand_on_click = vue.resolveDirective("expand-on-click");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      ref: "root",
      class: "dashboard-manager piwikSelector borderedControl piwikTopControl dashboardSettings",
      onClick: _cache[2] || (_cache[2] = ($event) => _ctx.onOpen()),
      onFocusout: _cache[3] || (_cache[3] = (...args) => _ctx.onFocusOut && _ctx.onFocusOut(...args))
    }, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("button", {
        type: "button",
        class: "title",
        title: _ctx.translate("Dashboard_ManageDashboard"),
        tabindex: "4",
        ref: "expander"
      }, [
        _cache[4] || (_cache[4] = vue.createElementVNode("span", { class: "icon icon-dashboard-customize" }, null, -1)),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Dashboard_ManageDashboard")), 1)
      ], 8, _hoisted_1)), [
        [_directive_tooltips]
      ]),
      vue.createElementVNode("div", _hoisted_2, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
          vue.createElementVNode("ul", _hoisted_4, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.generalActions, (title, actionName) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: actionName,
                class: "mtm-dropdownPanel__menuItem"
              }, [
                vue.createElementVNode("button", {
                  type: "button",
                  tabindex: "4",
                  onClick: ($event) => _ctx.onClickAction($event, actionName),
                  class: vue.normalizeClass(["mtm-dropdownPanel__menuLink mtm-dropdownPanel__menuLink--generalAction", { "mtm-dropdownPanel__menuLink--disabled": _ctx.isActionDisabled[actionName] }]),
                  disabled: _ctx.isActionDisabled[actionName] ? true : void 0,
                  title: _ctx.actionTooltips[actionName] || void 0,
                  "data-action": actionName
                }, [
                  vue.createElementVNode("span", _hoisted_6, vue.toDisplayString(_ctx.translate(title)), 1)
                ], 10, _hoisted_5)
              ]);
            }), 128)),
            vue.createElementVNode("li", _hoisted_7, [
              vue.createElementVNode("button", {
                type: "button",
                tabindex: "4",
                class: "mtm-dropdownPanel__menuLink",
                "data-action": "exportDashboard",
                onClick: _cache[0] || (_cache[0] = ($event) => _ctx.onClickExportDashboard())
              }, [
                vue.createElementVNode("span", _hoisted_8, vue.toDisplayString(_ctx.translate("Dashboard_ExportThisDashboard")), 1)
              ])
            ]),
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.dashboardActions, (title, actionName) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: actionName,
                class: "mtm-dropdownPanel__menuItem"
              }, [
                vue.createElementVNode("button", {
                  type: "button",
                  tabindex: "4",
                  onClick: ($event) => _ctx.onClickAction($event, actionName),
                  class: vue.normalizeClass(["mtm-dropdownPanel__menuLink", { "mtm-dropdownPanel__menuLink--disabled": _ctx.isActionDisabled[actionName] }]),
                  disabled: _ctx.isActionDisabled[actionName] ? true : void 0,
                  title: _ctx.actionTooltips[actionName] || void 0,
                  "data-action": actionName
                }, [
                  vue.createElementVNode("span", _hoisted_10, vue.toDisplayString(_ctx.translate(title)), 1)
                ], 10, _hoisted_9)
              ]);
            }), 128)),
            vue.createElementVNode("li", _hoisted_11, [
              vue.createElementVNode("button", {
                type: "button",
                tabindex: "4",
                class: "mtm-dropdownPanel__menuLink",
                "data-action": "addWidget",
                onClick: _cache[1] || (_cache[1] = ($event) => _ctx.openAddWidget())
              }, [
                _cache[5] || (_cache[5] = vue.createElementVNode("span", { class: "icon icon-add1 mtm-dropdownPanel__menuIcon" }, null, -1)),
                vue.createElementVNode("span", _hoisted_12, vue.toDisplayString(_ctx.translate("Dashboard_AddAWidget")), 1)
              ])
            ])
          ])
        ])), [
          [_directive_tooltips, { show: false }]
        ])
      ]),
      vue.createVNode(_component_AddWidgetModal, { onSelect: _ctx.onWidgetSelected }, null, 8, ["onSelect"])
    ], 32)), [
      [_directive_expand_on_click, { expander: "expander", onExpand: _ctx.onExpand, onClosed: _ctx.onClosed }]
    ]);
  }
  const DashboardSettings = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.Dashboard = Dashboard;
  exports2.DashboardSettings = DashboardSettings;
  exports2.DashboardStore = DashboardStore$1;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
