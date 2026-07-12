(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Live = {}, global.Vue, global.CoreHome));
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

  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const DEFAULT_INTERVAL_MS = 3e3;
  const DEFAULT_MAX_INTERVAL_MS = 3e5;
  class AutoRefreshController {
    constructor(options) {
      __publicField(this, "options");
      __publicField(this, "currentInterval");
      __publicField(this, "updateInterval", null);
      __publicField(this, "visibilityListenerId", null);
      this.options = options;
      this.currentInterval = this.resolveBaseInterval();
      this.setupVisibilityHandling();
    }
    getUpdatedResult(result) {
      if (typeof result === "boolean") {
        return result;
      }
      return result.updated;
    }
    resolveBaseInterval() {
      if (this.options.getBaseInterval) {
        const interval = Number(this.options.getBaseInterval());
        if (Number.isFinite(interval) && interval > 0) {
          return interval;
        }
      }
      return DEFAULT_INTERVAL_MS;
    }
    resolveMaxInterval() {
      if (this.options.getMaxInterval) {
        const interval = Number(this.options.getMaxInterval());
        if (Number.isFinite(interval) && interval > 0) {
          return interval;
        }
      }
      return DEFAULT_MAX_INTERVAL_MS;
    }
    clearUpdate() {
      if (this.updateInterval) {
        window.clearTimeout(this.updateInterval);
        this.updateInterval = null;
      }
    }
    getVisibility() {
      const { Visibility: visibility } = window;
      if (!visibility || !visibility.isSupported || !visibility.isSupported()) {
        return null;
      }
      return visibility;
    }
    isTabHidden() {
      const visibility = this.getVisibility();
      return Boolean(visibility && visibility.hidden());
    }
    setupVisibilityHandling() {
      const visibility = this.getVisibility();
      if (!visibility) {
        return;
      }
      this.visibilityListenerId = visibility.change(() => {
        if (visibility.hidden()) {
          this.clearUpdate();
        } else if (this.options.shouldRun()) {
          this.update();
        }
      });
    }
    teardownVisibilityHandling() {
      const visibility = this.getVisibility();
      if (!visibility || typeof this.visibilityListenerId !== "number") {
        return;
      }
      visibility.unbind(this.visibilityListenerId);
      this.visibilityListenerId = null;
    }
    schedule(delayMs) {
      const nextDelay = Number.isFinite(delayMs) && delayMs > 0 ? delayMs : this.resolveBaseInterval();
      this.clearUpdate();
      if (!this.options.shouldRun()) {
        return;
      }
      this.updateInterval = window.setTimeout(() => {
        this.update();
      }, nextDelay);
    }
    update() {
      if (!this.options.shouldRun()) {
        return;
      }
      if (this.isTabHidden()) {
        return;
      }
      this.options.request().then((response) => Promise.resolve(this.options.handleResponse(response))).then((result) => {
        const baseInterval = this.resolveBaseInterval();
        const isUpdated = this.getUpdatedResult(result);
        if (isUpdated) {
          this.currentInterval = baseInterval;
        } else {
          this.currentInterval += baseInterval;
        }
        if (this.currentInterval > this.resolveMaxInterval()) {
          this.currentInterval = this.resolveMaxInterval();
        }
        this.schedule(this.currentInterval);
      }).catch((error) => {
        if (this.options.onError) {
          this.options.onError(error);
        }
        this.schedule(this.resolveBaseInterval());
      });
    }
    start() {
      this.currentInterval = 0;
      this.update();
    }
    stop() {
      this.clearUpdate();
    }
    destroy() {
      this.stop();
      this.teardownVisibilityHandling();
    }
  }
  const { $ } = window;
  const MAX_ROWS = 10;
  const _sfc_main$5 = vue.defineComponent({
    props: {
      liveRefreshAfterMs: Number,
      disableLink: Boolean
    },
    components: {
      MatomoLoader: CoreHome.MatomoLoader
    },
    data() {
      return {
        isStarted: true,
        isInitialLoading: true,
        refreshController: null
      };
    },
    computed: {
      visitorLogUrl() {
        return `#?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues({}, CoreHome.MatomoUrl.hashParsed.value), {
          category: "General_Visitors",
          subcategory: "Live_VisitorLog"
        }))}`;
      }
    },
    mounted() {
      const root = this.$refs.root;
      if (root && !root.closest(".widget")) {
        CoreHome.Matomo.postEvent("hidePeriodSelector");
      }
      this.initRefreshController();
      this.fetchInitialContent();
    },
    beforeUnmount() {
      this.clearUpdate();
      if (this.refreshController) {
        this.refreshController.destroy();
        this.refreshController = null;
      }
      this.teardownListInteractions();
    },
    methods: {
      initRefreshController() {
        this.refreshController = new AutoRefreshController({
          getBaseInterval: () => this.getBaseInterval(),
          shouldRun: () => {
            if (this.isInitialLoading || !this.isStarted) {
              return false;
            }
            const root = this.$refs.root;
            return Boolean(root && root.isConnected);
          },
          request: () => {
            const segment = CoreHome.MatomoUrl.parsed.value.segment;
            return CoreHome.AjaxHelper.fetch(
              {
                module: "Live",
                action: "getLastVisitsStart",
                segment
              },
              {
                format: "html"
              }
            );
          },
          handleResponse: (response) => {
            const segment = CoreHome.MatomoUrl.parsed.value.segment;
            const ensured = this.ensureVisitsList(response);
            const updated = ensured ? true : this.parseResponse(response);
            if (updated || !this.hasTotalVisitors()) {
              this.refreshTotalVisitors(segment);
            }
            return { updated };
          }
        });
      },
      getBaseInterval() {
        const interval = Number(this.liveRefreshAfterMs);
        return Number.isFinite(interval) ? interval : 0;
      },
      pause() {
        this.isStarted = false;
        this.clearUpdate();
      },
      play() {
        this.isStarted = true;
        if (this.refreshController) {
          this.refreshController.start();
        }
      },
      clearUpdate() {
        if (this.refreshController) {
          this.refreshController.stop();
        }
      },
      scheduleUpdate(delayMs) {
        if (this.refreshController) {
          this.refreshController.schedule(delayMs);
        }
      },
      update() {
        if (this.refreshController) {
          this.refreshController.update();
        }
      },
      ensureVisitsList(response) {
        const root = this.$refs.root;
        if (!root) {
          return false;
        }
        if (root.querySelector("#visitsLive")) {
          return false;
        }
        const parser = new DOMParser();
        const doc = parser.parseFromString(response, "text/html");
        const visitsList = doc.querySelector("#visitsLive");
        if (!visitsList) {
          return false;
        }
        root.appendChild(visitsList);
        CoreHome.Matomo.helper.compileVueEntryComponents(root);
        this.setupListInteractions();
        return true;
      },
      refreshTotalVisitors(segment) {
        const root = this.$refs.root;
        if (!root) {
          return;
        }
        CoreHome.AjaxHelper.fetch(
          {
            module: "Live",
            action: "ajaxTotalVisitors",
            segment
          },
          {
            format: "html"
          }
        ).then((response) => {
          const container = root.querySelector("#visitsTotal");
          const wrapper = document.createElement("div");
          wrapper.innerHTML = response;
          const newContent = wrapper.querySelector("#visitsTotal");
          if (!newContent) {
            return;
          }
          if (!container) {
            const list = root.querySelector("#visitsLive");
            if (list) {
              list.before(newContent);
            } else {
              root.prepend(newContent);
            }
            CoreHome.Matomo.helper.compileVueEntryComponents(root);
            return;
          }
          CoreHome.Matomo.helper.destroyVueComponent(container);
          container.replaceWith(newContent);
          CoreHome.Matomo.helper.compileVueEntryComponents(root);
        });
      },
      fetchInitialContent() {
        const segment = CoreHome.MatomoUrl.parsed.value.segment;
        const visitsPromise = CoreHome.AjaxHelper.fetch(
          {
            module: "Live",
            action: "getLastVisitsStart",
            segment
          },
          {
            format: "html"
          }
        );
        const totalPromise = CoreHome.AjaxHelper.fetch(
          {
            module: "Live",
            action: "ajaxTotalVisitors",
            segment
          },
          {
            format: "html"
          }
        );
        Promise.allSettled([visitsPromise, totalPromise]).then(([visitsResult, totalResult]) => {
          const visitsHtml = visitsResult.status === "fulfilled" ? visitsResult.value : "";
          const totalHtml = totalResult.status === "fulfilled" ? totalResult.value : "";
          const root = this.$refs.root;
          if (!root || !visitsHtml && !totalHtml) {
            return;
          }
          root.innerHTML = `${totalHtml || ""}${visitsHtml || ""}`;
          CoreHome.Matomo.helper.compileVueEntryComponents(root);
          if (visitsHtml) {
            this.setupListInteractions();
          }
        }).finally(() => {
          this.isInitialLoading = false;
          this.scheduleUpdate(this.getBaseInterval());
        });
      },
      parseResponse(response) {
        const root = this.$refs.root;
        if (!root) {
          return false;
        }
        const list = root.querySelector("#visitsLive");
        if (!list) {
          return false;
        }
        const parser = new DOMParser();
        const doc = parser.parseFromString(response, "text/html");
        const items = Array.from(doc.querySelectorAll("li.visit"));
        if (!items.length) {
          return false;
        }
        this.teardownListInteractions();
        let updated = false;
        for (let i = items.length - 1; i >= 0; i -= 1) {
          const item = items[i];
          const visitId = item.getAttribute("id");
          if (visitId) {
            const existing = list.querySelector(`#${visitId}`);
            if (existing) {
              if (existing.getAttribute("data-hash") !== item.getAttribute("data-hash")) {
                updated = true;
              }
              existing.remove();
              list.insertBefore(item, list.firstChild);
            } else {
              updated = true;
              item.style.display = "none";
              list.insertBefore(item, list.firstChild);
              this.fadeIn(item);
            }
          }
        }
        const visits = list.querySelectorAll("li.visit");
        for (let i = visits.length - 1; i >= MAX_ROWS; i -= 1) {
          visits[i].remove();
        }
        this.setupListInteractions();
        return updated;
      },
      fadeIn(item) {
        item.classList.add("live-widget-fade-in");
        item.style.display = "";
        item.addEventListener("animationend", () => {
          item.classList.remove("live-widget-fade-in");
        }, { once: true });
      },
      hasTotalVisitors() {
        const root = this.$refs.root;
        if (!root) {
          return false;
        }
        return Boolean(root.querySelector("#visitsTotal"));
      },
      getVisitsList() {
        if (!$) {
          return null;
        }
        const root = this.$refs.root;
        if (!root) {
          return null;
        }
        const list = root.querySelector("#visitsLive");
        if (!list) {
          return null;
        }
        return $(list);
      },
      setupListInteractions() {
        const $list = this.getVisitsList();
        if (!$list) {
          return;
        }
        this.teardownListInteractions();
        $list.on(
          "click.liveWidgetProfile",
          ".visits-live-launch-visitor-profile",
          function onClickLaunchProfile(e) {
            e.preventDefault();
            window.broadcast.propagateNewPopoverParameter(
              "visitorProfile",
              $(this).attr("data-visitor-id")
            );
            return false;
          }
        );
        const visits = $list.find("li.visit");
        visits.tooltip({
          items: ".visitorLogIconWithDetails",
          track: true,
          show: { delay: 100, duration: 0 },
          hide: false,
          content() {
            return $("<ul>").html($("ul", $(this)).html());
          },
          tooltipClass: "small"
        });
        $list.tooltip({
          track: true,
          content() {
            const title = $(this).attr("title") || "";
            return window.vueSanitize(title.replace(/\n/g, "<br />"));
          },
          show: { delay: 100, duration: 0 },
          hide: false
        });
      },
      teardownListInteractions() {
        const $list = this.getVisitsList();
        if (!$list) {
          return;
        }
        $list.off("click.liveWidgetProfile", ".visits-live-launch-visitor-profile");
        try {
          $("li.visit", $list).tooltip("destroy");
        } catch (e) {
        }
        try {
          $list.tooltip("destroy");
        } catch (e) {
        }
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
  const _hoisted_1$3 = {
    key: 0,
    class: "live-widget-loading"
  };
  const _hoisted_2$2 = { ref: "root" };
  const _hoisted_3$2 = { class: "visitsLiveFooter" };
  const _hoisted_4$2 = ["title"];
  const _hoisted_5$1 = {
    id: "pauseImage",
    border: "0",
    src: "plugins/Live/images/pause.svg",
    role: "presentation"
  };
  const _hoisted_6$1 = ["title"];
  const _hoisted_7$1 = {
    id: "playImage",
    border: "0",
    src: "plugins/Live/images/play.svg",
    role: "presentation"
  };
  const _hoisted_8$1 = { key: 0 };
  const _hoisted_9$1 = ["href"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      _ctx.isInitialLoading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
        vue.createVNode(_component_MatomoLoader)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_2$2, null, 512),
      vue.createElementVNode("div", _hoisted_3$2, [
        vue.createElementVNode("a", {
          title: _ctx.translate("Live_OnClickPause", _ctx.translate("Live_VisitorsInRealTime")),
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.pause(), ["prevent"]))
        }, [
          vue.withDirectives(vue.createElementVNode("img", _hoisted_5$1, null, 512), [
            [vue.vShow, _ctx.isStarted]
          ])
        ], 8, _hoisted_4$2),
        vue.createElementVNode("a", {
          title: _ctx.translate("Live_OnClickStart", _ctx.translate("Live_VisitorsInRealTime")),
          onClick: _cache[1] || (_cache[1] = ($event) => _ctx.play())
        }, [
          vue.withDirectives(vue.createElementVNode("img", _hoisted_7$1, null, 512), [
            [vue.vShow, !_ctx.isStarted]
          ])
        ], 8, _hoisted_6$1),
        !_ctx.disableLink ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8$1, [
          _cache[2] || (_cache[2] = vue.createTextVNode("   ", -1)),
          vue.createElementVNode("a", {
            class: "rightLink",
            href: _ctx.visitorLogUrl
          }, vue.toDisplayString(_ctx.translate("Live_LinkVisitorLog")), 9, _hoisted_9$1)
        ])) : vue.createCommentVNode("", true)
      ])
    ]);
  }
  const LiveWidget = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    props: {
      interval: Number,
      maxInterval: Number,
      dataUrlParams: {
        type: Object,
        required: true
      },
      fadeInSpeed: {
        type: [String, Number],
        default: 600
      }
    },
    data() {
      return {
        previousResponse: "",
        refreshController: null
      };
    },
    mounted() {
      const root = this.$refs.root;
      if (!root || !this.dataUrlParams) {
        return;
      }
      this.previousResponse = root.innerHTML;
      this.refreshController = new AutoRefreshController({
        getBaseInterval: () => this.getBaseInterval(),
        getMaxInterval: () => this.getMaxInterval(),
        shouldRun: () => {
          const element = this.$refs.root;
          return Boolean(element && element.isConnected);
        },
        request: () => CoreHome.AjaxHelper.fetch(this.dataUrlParams, {
          format: "html"
        }),
        handleResponse: (response) => this.replaceContent(response)
      });
      this.refreshController.schedule(this.getBaseInterval());
    },
    beforeUnmount() {
      if (this.refreshController) {
        this.refreshController.destroy();
        this.refreshController = null;
      }
    },
    methods: {
      getBaseInterval() {
        return Number(this.interval);
      },
      getMaxInterval() {
        return Number(this.maxInterval);
      },
      highlight(root) {
        const { fadeInSpeed } = this;
        if (!fadeInSpeed || !window.$ || !window.$.fn || !window.$.fn.effect) {
          return;
        }
        window.$(root).effect("highlight", {}, fadeInSpeed);
      },
      replaceContent(response) {
        const root = this.$refs.root;
        if (!root) {
          return false;
        }
        const updated = response !== this.previousResponse;
        if (!updated) {
          return false;
        }
        root.innerHTML = response;
        CoreHome.Matomo.helper.compileVueEntryComponents(root);
        this.highlight(root);
        this.previousResponse = response;
        return true;
      }
    }
  });
  const _hoisted_1$2 = { ref: "root" };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.renderSlot(_ctx.$slots, "default")
    ], 512);
  }
  const AutoRefreshWidget = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const DEFAULT_LAST_MINUTES = 3;
  const DEFAULT_REFRESH_AFTER_SECS = 3;
  const QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY = "Live_QueryMaxExecutionTimeExceeded";
  const _sfc_main$3 = vue.defineComponent({
    props: {
      lastMinutes: Number,
      refreshAfterXSecs: Number
    },
    data() {
      return {
        visitorsCount: null,
        visitsCount: null,
        actionsCount: null,
        error: "",
        refreshTimer: null,
        stopRefreshing: false
      };
    },
    computed: {
      refreshIntervalMs() {
        const seconds = Number(this.refreshAfterXSecs);
        const normalized = Number.isFinite(seconds) && seconds > 0 ? seconds : DEFAULT_REFRESH_AFTER_SECS;
        return normalized * 1e3;
      },
      normalizedLastMinutes() {
        const minutes = Number(this.lastMinutes);
        return Number.isFinite(minutes) && minutes > 0 ? minutes : DEFAULT_LAST_MINUTES;
      },
      visitorsCountText() {
        return this.formatCount(this.visitorsCount);
      },
      visitsCountText() {
        return this.formatCount(this.visitsCount);
      },
      actionsCountText() {
        return this.formatCount(this.actionsCount);
      },
      visitorsTooltip() {
        if (this.visitorsCount === 1) {
          return CoreHome.translate("Live_NbVisitor");
        }
        return CoreHome.translate("Live_NbVisitors", this.visitorsCountText);
      },
      visitsText() {
        if (this.visitsCount === 1) {
          return CoreHome.translate("General_OneVisit");
        }
        return CoreHome.translate("General_NVisits", this.visitsCountText);
      },
      actionsText() {
        if (this.actionsCount === 1) {
          return CoreHome.translate("General_OneAction");
        }
        return CoreHome.translate("VisitsSummary_NbActionsDescription", this.actionsCountText);
      },
      minutesText() {
        if (this.normalizedLastMinutes === 1) {
          return CoreHome.translate("Intl_OneMinute");
        }
        return CoreHome.translate("Intl_NMinutes", this.normalizedLastMinutes);
      },
      messageHtml() {
        const visitsMessage = `<span class="simple-realtime-metric" data-metric="visits">${this.visitsText}</span>`;
        const actionsMessage = `<span class="simple-realtime-metric" data-metric="actions">${this.actionsText}</span>`;
        const minutesMessage = `<span class="simple-realtime-metric" data-metric="minutes">${this.minutesText}</span>`;
        return CoreHome.translate(
          "Live_SimpleRealTimeWidget_Message",
          visitsMessage,
          actionsMessage,
          minutesMessage
        );
      }
    },
    mounted() {
      this.update();
    },
    beforeUnmount() {
      this.clearScheduledUpdate();
    },
    methods: {
      clearScheduledUpdate() {
        if (this.refreshTimer) {
          window.clearTimeout(this.refreshTimer);
          this.refreshTimer = null;
        }
      },
      scheduleUpdate() {
        this.clearScheduledUpdate();
        this.refreshTimer = window.setTimeout(() => {
          this.update();
        }, this.refreshIntervalMs);
      },
      parseCount(value) {
        const parsed = Number(value);
        if (!Number.isFinite(parsed) || parsed < 0) {
          return null;
        }
        return parsed;
      },
      formatCount(value) {
        if (value === null) {
          return "-";
        }
        return CoreHome.formatNumber(value, 0, 0);
      },
      resetCounters() {
        this.visitorsCount = null;
        this.visitsCount = null;
        this.actionsCount = null;
      },
      isTabHidden() {
        const visibility = window.Visibility;
        return Boolean(
          visibility && visibility.isSupported && visibility.isSupported() && visibility.hidden()
        );
      },
      getErrorMessage(error) {
        if (typeof error === "string") {
          return error;
        }
        if (error && typeof error === "object" && "message" in error) {
          const { message } = error;
          if (typeof message === "string") {
            return message;
          }
        }
        return "";
      },
      isMaxExecutionTimeError(error) {
        const message = this.getErrorMessage(error);
        const translatedMarker = CoreHome.translate(QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY);
        return message.startsWith(translatedMarker) || message.includes(QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY);
      },
      update() {
        const element = this.$el;
        if (!element || !element.isConnected) {
          return;
        }
        if (this.isTabHidden()) {
          this.scheduleUpdate();
          return;
        }
        CoreHome.AjaxHelper.fetch(
          {
            module: "API",
            method: "Live.getCounters",
            showColumns: "visits,visitors,actions",
            lastMinutes: this.normalizedLastMinutes
          },
          {
            format: "json"
          }
        ).then((response) => {
          const counters = Array.isArray(response) && response.length ? response[0] : {};
          this.visitorsCount = this.parseCount(counters.visitors);
          this.visitsCount = this.parseCount(counters.visits);
          this.actionsCount = this.parseCount(counters.actions);
          this.error = "";
          this.stopRefreshing = false;
        }).catch((error) => {
          this.error = this.getErrorMessage(error);
          this.stopRefreshing = this.isMaxExecutionTimeError(error);
          if (this.stopRefreshing) {
            this.resetCounters();
          }
        }).finally(() => {
          if (element.isConnected && !this.stopRefreshing) {
            this.scheduleUpdate();
          }
        });
      }
    }
  });
  const _hoisted_1$1 = { class: "simple-realtime-visitor-widget" };
  const _hoisted_2$1 = ["title"];
  const _hoisted_3$1 = {
    key: 0,
    class: "alert alert-danger"
  };
  const _hoisted_4$1 = ["innerHTML"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.createElementVNode("div", {
        class: "simple-realtime-visitor-counter",
        title: _ctx.visitorsTooltip
      }, [
        vue.createElementVNode("div", null, vue.toDisplayString(_ctx.visitorsCountText), 1)
      ], 8, _hoisted_2$1),
      _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
      _ctx.error ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, vue.toDisplayString(_ctx.error), 1)) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", {
        class: "simple-realtime-elaboration",
        innerHTML: _ctx.$sanitize(_ctx.messageHtml)
      }, null, 8, _hoisted_4$1)
    ]);
  }
  const SimpleRealtimeVisitorWidget = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      countErrorToday: Number,
      visitorsCountToday: Number,
      pisToday: Number,
      countErrorHalfHour: Number,
      visitorsCountHalfHour: Number,
      pisHalfhour: Number
    }
  });
  const _hoisted_1 = {
    class: "dataTable",
    cellspacing: "0"
  };
  const _hoisted_2 = {
    id: "label",
    class: "sortable label first",
    style: { "cursor": "auto" }
  };
  const _hoisted_3 = { class: "thDIV" };
  const _hoisted_4 = ["title"];
  const _hoisted_5 = { class: "thDIV" };
  const _hoisted_6 = ["title"];
  const _hoisted_7 = { class: "thDIV" };
  const _hoisted_8 = { class: "" };
  const _hoisted_9 = { class: "label column" };
  const _hoisted_10 = ["title"];
  const _hoisted_11 = ["title"];
  const _hoisted_12 = { class: "" };
  const _hoisted_13 = { class: "label column" };
  const _hoisted_14 = ["title"];
  const _hoisted_15 = ["title"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    var _a, _b, _c, _d;
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("table", _hoisted_1, [
        vue.createElementVNode("thead", null, [
          vue.createElementVNode("tr", null, [
            vue.createElementVNode("th", _hoisted_2, [
              vue.createElementVNode("div", _hoisted_3, vue.toDisplayString(_ctx.translate("General_Date")), 1)
            ]),
            vue.createElementVNode("th", {
              class: "sortable",
              style: { "cursor": "auto" },
              title: _ctx.translate("General_ColumnNbVisitsDocumentation")
            }, [
              vue.createElementVNode("div", _hoisted_5, vue.toDisplayString(_ctx.translate("General_ColumnNbVisits")), 1)
            ], 8, _hoisted_4),
            vue.createElementVNode("th", {
              class: "sortable",
              style: { "cursor": "auto" },
              title: _ctx.translate("General_ColumnNbActionsDocumentation")
            }, [
              vue.createElementVNode("div", _hoisted_7, vue.toDisplayString(_ctx.translate("General_Actions")), 1)
            ], 8, _hoisted_6)
          ])
        ]),
        vue.createElementVNode("tbody", null, [
          vue.createElementVNode("tr", _hoisted_8, [
            vue.createElementVNode("td", _hoisted_9, vue.toDisplayString(_ctx.translate("Live_LastHours", "24")), 1),
            vue.createElementVNode("td", {
              class: "column",
              title: (_a = _ctx.countErrorToday) == null ? void 0 : _a.toString()
            }, vue.toDisplayString(_ctx.visitorsCountToday || 0), 9, _hoisted_10),
            vue.createElementVNode("td", {
              class: "column",
              title: (_b = _ctx.countErrorToday) == null ? void 0 : _b.toString()
            }, vue.toDisplayString(_ctx.pisToday || 0), 9, _hoisted_11)
          ]),
          vue.createElementVNode("tr", _hoisted_12, [
            vue.createElementVNode("td", _hoisted_13, vue.toDisplayString(_ctx.translate("Live_LastMinutes", "30")), 1),
            vue.createElementVNode("td", {
              class: "column",
              title: (_c = _ctx.countErrorHalfHour) == null ? void 0 : _c.toString()
            }, vue.toDisplayString(_ctx.visitorsCountHalfHour || 0), 9, _hoisted_14),
            vue.createElementVNode("td", {
              class: "column",
              title: (_d = _ctx.countErrorHalfHour) == null ? void 0 : _d.toString()
            }, vue.toDisplayString(_ctx.pisHalfhour || 0), 9, _hoisted_15)
          ])
        ])
      ])
    ]);
  }
  const TotalVisitors = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      disableLink: Boolean,
      liveRefreshAfterMs: Number,
      isWidgetized: Boolean
    },
    components: {
      LiveWidget,
      ContentBlock: CoreHome.ContentBlock,
      Passthrough: CoreHome.Passthrough
    }
  });
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_LiveWidget = vue.resolveComponent("LiveWidget");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(!_ctx.isWidgetized ? "ContentBlock" : "Passthrough"), {
        "content-title": !_ctx.isWidgetized ? _ctx.translate("Live_VisitorsInRealTime") : void 0
      }, {
        default: vue.withCtx(() => [
          vue.createVNode(_component_LiveWidget, {
            "live-refresh-after-ms": _ctx.liveRefreshAfterMs,
            "disable-link": _ctx.disableLink
          }, null, 8, ["live-refresh-after-ms", "disable-link"])
        ]),
        _: 1
      }, 8, ["content-title"]))
    ]);
  }
  const LivePage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    }
  });
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("h2", null, [
        vue.createVNode(_component_EnrichedHeadline, null, {
          default: vue.withCtx(() => [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate("Live_VisitorLog")), 1)
          ]),
          _: 1
        })
      ])
    ])), [
      [_directive_content_intro]
    ]);
  }
  const IndexHeader = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AutoRefreshWidget = AutoRefreshWidget;
  exports2.IndexHeader = IndexHeader;
  exports2.LivePage = LivePage;
  exports2.LiveWidget = LiveWidget;
  exports2.SimpleRealtimeVisitorWidget = SimpleRealtimeVisitorWidget;
  exports2.TotalVisitors = TotalVisitors;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
