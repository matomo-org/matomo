(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.MultiSites = {}, global.Vue, global.CoreHome));
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
  const DEFAULT_SORT_ORDER = "desc";
  const DEFAULT_SORT_COLUMN = "nb_visits";
  class DashboardStore {
    constructor() {
      __publicField(this, "fetchAbort", null);
      __publicField(this, "privateState", vue.reactive({
        dashboardKPIs: {
          badges: {},
          evolutionPeriod: "day",
          hits: "?",
          hitsCompact: "?",
          hitsEvolution: "",
          hitsTrend: 0,
          aiChatbotsRequests: "?",
          aiChatbotsRequestsCompact: "?",
          aiChatbotsRequestsEvolution: "",
          aiChatbotsRequestsTrend: 0,
          pageviews: "?",
          pageviewsCompact: "?",
          pageviewsEvolution: "",
          pageviewsTrend: 0,
          revenue: "?",
          revenueCompact: "?",
          revenueEvolution: "",
          revenueTrend: 0,
          visits: "?",
          visitsCompact: "?",
          visitsEvolution: "",
          visitsTrend: 0
        },
        dashboardSites: [],
        errorLoading: false,
        isLoadingKPIs: false,
        isLoadingSites: false,
        numSites: 0,
        paginationCurrentPage: 0,
        sortColumn: DEFAULT_SORT_COLUMN,
        sortOrder: DEFAULT_SORT_ORDER
      }));
      __publicField(this, "autoRefreshInterval", 0);
      __publicField(this, "autoRefreshTimeout", null);
      __publicField(this, "pageSize", 25);
      __publicField(this, "searchTerm", "");
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "numberOfPages", vue.computed(
        () => Math.ceil(this.state.value.numSites / this.pageSize - 1)
      ));
      __publicField(this, "currentPagingOffset", vue.computed(
        () => Math.ceil(this.state.value.paginationCurrentPage * this.pageSize)
      ));
      __publicField(this, "paginationLowerBound", vue.computed(() => {
        if (this.state.value.numSites === 0) {
          return 0;
        }
        return 1 + this.currentPagingOffset.value;
      }));
      __publicField(this, "paginationUpperBound", vue.computed(() => {
        if (this.state.value.numSites === 0) {
          return 0;
        }
        const end = this.pageSize + this.currentPagingOffset.value;
        const max = this.state.value.numSites;
        if (end < max) {
          return end;
        }
        return max;
      }));
    }
    reloadDashboard() {
      this.privateState.sortColumn = DEFAULT_SORT_COLUMN;
      this.privateState.sortOrder = DEFAULT_SORT_ORDER;
      this.privateState.paginationCurrentPage = 0;
      this.refreshData();
    }
    navigateNextPage() {
      if (this.privateState.paginationCurrentPage === this.numberOfPages.value) {
        return;
      }
      this.privateState.paginationCurrentPage += 1;
      this.refreshData(true);
    }
    navigatePreviousPage() {
      if (this.privateState.paginationCurrentPage === 0) {
        return;
      }
      this.privateState.paginationCurrentPage -= 1;
      this.refreshData(true);
    }
    searchSite(term) {
      this.searchTerm = term;
      this.privateState.paginationCurrentPage = 0;
      this.refreshData(true);
    }
    setAutoRefreshInterval(interval) {
      this.autoRefreshInterval = interval;
    }
    setPageSize(size) {
      this.pageSize = size;
    }
    sortBy(column) {
      if (this.privateState.sortColumn === column) {
        this.privateState.sortOrder = this.privateState.sortOrder === "desc" ? "asc" : "desc";
      } else {
        this.privateState.sortOrder = column === "label" ? "asc" : "desc";
      }
      this.privateState.sortColumn = column;
      this.refreshData(true);
    }
    cancelAutoRefresh() {
      if (!this.autoRefreshTimeout) {
        return;
      }
      clearTimeout(this.autoRefreshTimeout);
      this.autoRefreshTimeout = null;
    }
    refreshData(onlySites = false) {
      if (this.fetchAbort) {
        this.fetchAbort.abort();
        this.fetchAbort = null;
        this.cancelAutoRefresh();
      }
      this.fetchAbort = new AbortController();
      this.privateState.errorLoading = false;
      this.privateState.isLoadingKPIs = !onlySites;
      this.privateState.isLoadingSites = true;
      const params = {
        method: "MultiSites.getAllWithGroups",
        filter_limit: this.pageSize,
        filter_offset: this.currentPagingOffset.value,
        filter_sort_column: this.privateState.sortColumn,
        filter_sort_order: this.privateState.sortOrder,
        format_metrics: 0,
        showColumns: [
          "hits_evolution",
          "hits_evolution_trend",
          "label",
          "hits",
          "ai_chatbots_requests",
          "nb_pageviews",
          "nb_visits",
          "pageviews_evolution",
          "pageviews_evolution_trend",
          "revenue",
          "revenue_evolution",
          "revenue_evolution_trend",
          "visits_evolution",
          "visits_evolution_trend"
        ].join(",")
      };
      if (this.searchTerm) {
        params.pattern = this.searchTerm;
      }
      return CoreHome.AjaxHelper.fetch(
        params,
        {
          abortController: this.fetchAbort,
          createErrorNotification: false
        }
      ).then((response) => {
        if (!onlySites) {
          this.updateDashboardKPIs(response);
          CoreHome.Matomo.postEvent("MultiSites.DashboardKPIs.updated", {
            parameters: new CoreHome.AjaxHelper().mixinDefaultGetParams({
              filter_limit: this.pageSize,
              filter_offset: this.currentPagingOffset.value,
              filter_sort_column: this.privateState.sortColumn,
              filter_sort_order: this.privateState.sortOrder,
              pattern: this.searchTerm
            }),
            kpis: this.privateState.dashboardKPIs
          });
        }
        this.updateDashboardSites(response);
      }).catch(() => {
        this.privateState.dashboardSites = [];
        this.privateState.errorLoading = true;
      }).finally(() => {
        this.privateState.isLoadingKPIs = false;
        this.privateState.isLoadingSites = false;
        this.fetchAbort = null;
        this.startAutoRefresh();
      });
    }
    startAutoRefresh() {
      this.cancelAutoRefresh();
      if (this.autoRefreshInterval <= 0) {
        return;
      }
      let currentPeriod;
      try {
        currentPeriod = CoreHome.Periods.parse(
          CoreHome.Matomo.period,
          CoreHome.Matomo.currentDateString
        );
      } catch (e) {
      }
      if (!currentPeriod || !currentPeriod.containsToday()) {
        return;
      }
      this.autoRefreshTimeout = setTimeout(() => {
        this.autoRefreshTimeout = null;
        this.refreshData();
      }, this.autoRefreshInterval * 1e3);
    }
    updateDashboardKPIs(response) {
      const isSegmented = !!CoreHome.MatomoUrl.parsed.value.segment;
      const aiRequests = response.totals.ai_chatbots_requests || 0;
      const previousAiRequests = response.totals.previous_ai_chatbots_requests || 0;
      this.privateState.dashboardKPIs = {
        badges: {
          hits: null,
          pageviews: null,
          revenue: null,
          visits: null
        },
        evolutionPeriod: CoreHome.Matomo.period,
        hits: CoreHome.NumberFormatter.formatNumber(response.totals.hits),
        hitsCompact: CoreHome.NumberFormatter.formatNumberCompact(response.totals.hits),
        hitsEvolution: CoreHome.NumberFormatter.calculateAndFormatEvolution(
          response.totals.hits,
          response.totals.previous_hits,
          true
        ),
        hitsTrend: Math.sign(
          response.totals.hits - response.totals.previous_hits
        ),
        aiChatbotsRequests: isSegmented ? "-" : CoreHome.NumberFormatter.formatNumber(aiRequests),
        aiChatbotsRequestsCompact: isSegmented ? "-" : CoreHome.NumberFormatter.formatNumberCompact(aiRequests),
        aiChatbotsRequestsEvolution: isSegmented ? "" : CoreHome.NumberFormatter.calculateAndFormatEvolution(
          aiRequests,
          previousAiRequests,
          true
        ),
        aiChatbotsRequestsTrend: isSegmented ? 0 : Math.sign(aiRequests - previousAiRequests),
        pageviews: CoreHome.NumberFormatter.formatNumber(response.totals.nb_pageviews),
        pageviewsCompact: CoreHome.NumberFormatter.formatNumberCompact(response.totals.nb_pageviews),
        pageviewsEvolution: CoreHome.NumberFormatter.calculateAndFormatEvolution(
          response.totals.nb_pageviews,
          response.totals.previous_nb_pageviews,
          true
        ),
        pageviewsTrend: Math.sign(
          response.totals.nb_pageviews - response.totals.previous_nb_pageviews
        ),
        revenue: CoreHome.NumberFormatter.formatCurrency(response.totals.revenue, ""),
        revenueCompact: CoreHome.NumberFormatter.formatCurrencyCompact(response.totals.revenue, ""),
        revenueEvolution: CoreHome.NumberFormatter.calculateAndFormatEvolution(
          response.totals.revenue,
          response.totals.previous_revenue,
          true
        ),
        revenueTrend: Math.sign(
          response.totals.revenue - response.totals.previous_revenue
        ),
        visits: CoreHome.NumberFormatter.formatNumber(response.totals.nb_visits),
        visitsCompact: CoreHome.NumberFormatter.formatNumberCompact(response.totals.nb_visits),
        visitsEvolution: CoreHome.NumberFormatter.calculateAndFormatEvolution(
          response.totals.nb_visits,
          response.totals.previous_nb_visits,
          true
        ),
        visitsTrend: Math.sign(
          response.totals.nb_visits - response.totals.previous_nb_visits
        )
      };
    }
    updateDashboardSites(response) {
      this.privateState.dashboardSites = response.sites;
      this.privateState.numSites = response.numSites;
    }
  }
  const DashboardStore$1 = new DashboardStore();
  const _sfc_main$4 = vue.defineComponent({
    directives: {
      Tooltips: CoreHome.Tooltips
    },
    props: {
      modelValue: {
        type: Object,
        required: true
      }
    },
    computed: {
      tooltipContent() {
        return () => {
          var _a;
          return ((_a = this.$refs.kpiCardTooltipTemplate) == null ? void 0 : _a.innerHTML) || "";
        };
      },
      evolutionTrendFrom() {
        switch (this.kpi.evolutionPeriod) {
          case "day":
            return "MultiSites_EvolutionFromPreviousDay";
          case "week":
            return "MultiSites_EvolutionFromPreviousWeek";
          case "month":
            return "MultiSites_EvolutionFromPreviousMonth";
          case "year":
            return "MultiSites_EvolutionFromPreviousYear";
          default:
            return "MultiSites_EvolutionFromPreviousPeriod";
        }
      },
      evolutionTrendClass() {
        if (this.kpi.evolutionTrend === 1) {
          return "kpiTrendPositive";
        }
        if (this.kpi.evolutionTrend === -1) {
          return "kpiTrendNegative";
        }
        return "kpiTrendNeutral";
      },
      evolutionTrendIcon() {
        if (this.kpi.evolutionTrend === 1) {
          return "icon-chevron-up";
        }
        if (this.kpi.evolutionTrend === -1) {
          return "icon-chevron-down";
        }
        return "icon-circle";
      },
      kpi() {
        return this.modelValue;
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
  const _hoisted_1$4 = { class: "kpiCard" };
  const _hoisted_2$4 = { class: "kpiCardTitle" };
  const _hoisted_3$4 = {
    style: { "display": "none" },
    ref: "kpiCardTooltipTemplate"
  };
  const _hoisted_4$4 = { role: "tooltip" };
  const _hoisted_5$3 = ["title"];
  const _hoisted_6$3 = { class: "kpiCardEvolution" };
  const _hoisted_7$3 = {
    key: 1,
    class: "kpiCardEvolution"
  };
  const _hoisted_8$3 = ["title", "innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
      vue.createElementVNode("div", _hoisted_2$4, [
        vue.createElementVNode("span", {
          class: vue.normalizeClass(`kpiCardIcon ${_ctx.kpi.icon}`)
        }, null, 2),
        vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate(_ctx.kpi.title)), 1)
      ]),
      vue.createElementVNode("div", _hoisted_3$4, [
        vue.createElementVNode("div", _hoisted_4$4, [
          vue.createElementVNode("h3", null, vue.toDisplayString(_ctx.translate(_ctx.kpi.title)), 1),
          _ctx.kpi.tooltipBody ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
            vue.createTextVNode(vue.toDisplayString(_ctx.translate(_ctx.kpi.tooltipBody)), 1)
          ], 64)) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
            vue.createTextVNode(vue.toDisplayString(_ctx.kpi.value), 1)
          ], 64))
        ])
      ], 512),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
        class: "kpiCardValue",
        title: _ctx.kpi.value
      }, [
        vue.createTextVNode(vue.toDisplayString(_ctx.kpi.valueCompact), 1)
      ], 8, _hoisted_5$3)), [
        [_directive_tooltips, { duration: 200, delay: 200, content: _ctx.tooltipContent }]
      ]),
      vue.createElementVNode("div", _hoisted_6$3, [
        _ctx.kpi.evolutionValue !== "" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          vue.createElementVNode("span", {
            class: vue.normalizeClass(`kpiCardEvolutionTrend ${_ctx.evolutionTrendClass}`)
          }, [
            vue.createElementVNode("span", {
              class: vue.normalizeClass(`kpiCardEvolutionIcon ${_ctx.evolutionTrendIcon}`)
            }, null, 2),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.kpi.evolutionValue) + "  ", 1)
          ], 2),
          vue.createElementVNode("span", null, vue.toDisplayString(_ctx.translate(_ctx.evolutionTrendFrom)), 1)
        ], 64)) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$3, [..._cache[0] || (_cache[0] = [
          vue.createElementVNode("span", { class: "kpiCardEvolutionTrend" }, " ", -1)
        ])]))
      ]),
      _ctx.kpi.badge ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        class: "kpiCardBadge",
        title: _ctx.kpi.badge.title,
        innerHTML: _ctx.$sanitize(_ctx.kpi.badge.label)
      }, null, 8, _hoisted_8$3)), [
        [_directive_tooltips, { duration: 200, delay: 200 }]
      ]) : vue.createCommentVNode("", true)
    ]);
  }
  const KPICard = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    components: {
      MatomoLoader: CoreHome.MatomoLoader,
      KPICard
    },
    props: {
      isLoading: Boolean,
      modelValue: {
        type: Array,
        required: true
      }
    },
    computed: {
      hasKpiBadge() {
        return this.kpis.some((kpi) => !!kpi.badge);
      },
      kpis() {
        return this.modelValue;
      }
    }
  });
  const _hoisted_1$3 = { class: "kpiCardContainer" };
  const _hoisted_2$3 = {
    key: 0,
    class: "kpiCard kpiCardLoading"
  };
  const _hoisted_3$3 = { class: "kpiCardValue" };
  const _hoisted_4$3 = {
    key: 0,
    class: "kpiCardBadge"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_KPICard = vue.resolveComponent("KPICard");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      _ctx.isLoading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, [
        _cache[0] || (_cache[0] = vue.createElementVNode("div", { class: "kpiCardTitle" }, " ", -1)),
        vue.createElementVNode("div", _hoisted_3$3, [
          vue.createVNode(_component_MatomoLoader)
        ]),
        _cache[1] || (_cache[1] = vue.createElementVNode("div", { class: "kpiCardEvolution" }, [
          vue.createElementVNode("span", { class: "kpiCardEvolutionTrend" }, " ")
        ], -1)),
        _ctx.hasKpiBadge ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$3, " ")) : vue.createCommentVNode("", true)
      ])) : (vue.openBlock(true), vue.createElementBlock(vue.Fragment, { key: 1 }, vue.renderList(_ctx.kpis, (kpi, index) => {
        return vue.openBlock(), vue.createElementBlock(vue.Fragment, {
          key: `kpi-card-${index}`
        }, [
          index > 0 ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 0,
            class: vue.normalizeClass({ kpiCardDivider: true, kpiCardDividerBadge: _ctx.hasKpiBadge })
          }, " ", 2)) : vue.createCommentVNode("", true),
          vue.createVNode(_component_KPICard, { "model-value": kpi }, null, 8, ["model-value"])
        ], 64);
      }), 128))
    ]);
  }
  const KPICardContainer = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    props: {
      displayRevenue: {
        type: Boolean,
        required: true
      },
      evolutionMetric: {
        type: String,
        required: true
      },
      modelValue: {
        type: Object,
        required: true
      },
      sparklineMetric: String,
      displaySparkline: Boolean,
      showAiChatbotsRequests: {
        type: Boolean,
        required: true
      }
    },
    computed: {
      dashboardUrl() {
        const dashboardParams = CoreHome.MatomoUrl.stringify({
          module: "CoreHome",
          action: "index",
          date: CoreHome.Matomo.currentDateString,
          period: CoreHome.Matomo.period,
          idSite: this.site.idsite
        });
        return `?${dashboardParams}${this.tokenParam}`;
      },
      evolutionIconSrc() {
        if (this.evolutionTrend === 1) {
          return "plugins/MultiSites/images/arrow_up.svg";
        }
        if (this.evolutionTrend === -1) {
          return "plugins/MultiSites/images/arrow_down.svg";
        }
        return "plugins/MultiSites/images/stop.svg";
      },
      evolutionSparklineSrc() {
        let sparklineDate = CoreHome.Matomo.currentDateString;
        if (CoreHome.Matomo.period !== "range") {
          const { startDate, endDate } = CoreHome.Range.getLastNRange(
            CoreHome.Matomo.period,
            "30",
            CoreHome.Matomo.currentDateString
          );
          sparklineDate = `${CoreHome.format(startDate)},${CoreHome.format(endDate)}`;
        }
        const redesignEnabled = document.body.classList.contains("sparklines-redesign-enabled");
        const sizeParams = redesignEnabled ? { width: 200, height: 50 } : {};
        const sparklineParams = CoreHome.MatomoUrl.stringify(__spreadValues({
          module: "MultiSites",
          action: "getEvolutionGraph",
          date: sparklineDate,
          period: CoreHome.Matomo.period,
          idSite: this.site.idsite,
          columns: this.sparklineMetric,
          evolutionBy: this.sparklineMetric,
          colors: JSON.stringify(CoreHome.Matomo.getSparklineColors()),
          viewDataTable: "sparkline"
        }, sizeParams));
        return `?${sparklineParams}${this.tokenParam}`;
      },
      evolutionTrend() {
        const property = `${this.evolutionMetric}_trend`;
        return this.site[property];
      },
      evolutionTrendClass() {
        if (this.evolutionTrend === 1) {
          return "evolutionTrendPositive";
        }
        if (this.evolutionTrend === -1) {
          return "evolutionTrendNegative";
        }
        return "";
      },
      site() {
        return this.modelValue;
      },
      siteLabel() {
        return CoreHome.Matomo.helper.htmlDecode(this.site.label);
      },
      tokenParam() {
        const token_auth = CoreHome.MatomoUrl.urlParsed.value.token_auth;
        return token_auth ? `&token_auth=${token_auth}` : "";
      }
    }
  });
  const _hoisted_1$2 = { class: "label" };
  const _hoisted_2$2 = ["href", "title"];
  const _hoisted_3$2 = ["href"];
  const _hoisted_4$2 = {
    key: 1,
    class: "value"
  };
  const _hoisted_5$2 = { class: "value" };
  const _hoisted_6$2 = { class: "value" };
  const _hoisted_7$2 = { key: 0 };
  const _hoisted_8$2 = { class: "value" };
  const _hoisted_9$2 = { class: "value" };
  const _hoisted_10$2 = { key: 1 };
  const _hoisted_11$2 = { class: "value" };
  const _hoisted_12$2 = ["colspan"];
  const _hoisted_13$1 = ["src"];
  const _hoisted_14$1 = {
    key: 2,
    class: "sitesTableSparkline"
  };
  const _hoisted_15$1 = ["href", "title"];
  const _hoisted_16$1 = ["src"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("tr", {
      class: vue.normalizeClass({
        sitesTableGroup: !!_ctx.site.isGroup,
        sitesTableGroupSite: !_ctx.site.isGroup && !!_ctx.site.group,
        sitesTableSite: !_ctx.site.isGroup && !_ctx.site.group
      })
    }, [
      vue.createElementVNode("td", _hoisted_1$2, [
        !_ctx.site.isGroup ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          vue.createElementVNode("a", {
            rel: "noreferrer noopener",
            target: "_blank",
            href: _ctx.site.main_url,
            title: _ctx.translate("General_GoTo", _ctx.site.main_url)
          }, [..._cache[0] || (_cache[0] = [
            vue.createElementVNode("span", { class: "icon icon-outlink" }, null, -1)
          ])], 8, _hoisted_2$2),
          vue.createElementVNode("a", {
            title: "View reports",
            class: "value",
            href: _ctx.dashboardUrl
          }, vue.toDisplayString(_ctx.siteLabel), 9, _hoisted_3$2)
        ], 64)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$2, vue.toDisplayString(_ctx.siteLabel), 1))
      ]),
      vue.createElementVNode("td", null, [
        vue.createElementVNode("span", _hoisted_5$2, vue.toDisplayString(_ctx.formatNumber(_ctx.site.nb_visits)), 1)
      ]),
      vue.createElementVNode("td", null, [
        vue.createElementVNode("span", _hoisted_6$2, vue.toDisplayString(_ctx.formatNumber(_ctx.site.nb_pageviews)), 1)
      ]),
      _ctx.showAiChatbotsRequests ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_7$2, [
        vue.createElementVNode("span", _hoisted_8$2, vue.toDisplayString(_ctx.formatNumber(_ctx.site.ai_chatbots_requests)), 1)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("td", null, [
        vue.createElementVNode("span", _hoisted_9$2, vue.toDisplayString(_ctx.formatNumber(_ctx.site.hits)), 1)
      ]),
      _ctx.displayRevenue ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_10$2, [
        vue.createElementVNode("span", _hoisted_11$2, vue.toDisplayString(_ctx.formatCurrency(_ctx.site.revenue, _ctx.site.currencySymbol || "")), 1)
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("td", {
        colspan: _ctx.displaySparkline ? 1 : 2
      }, [
        !_ctx.site.isGroup && (_ctx.sparklineMetric || "") in _ctx.site ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          vue.createElementVNode("img", {
            src: _ctx.evolutionIconSrc,
            alt: ""
          }, null, 8, _hoisted_13$1),
          vue.createElementVNode("span", {
            class: vue.normalizeClass(_ctx.evolutionTrendClass)
          }, vue.toDisplayString(_ctx.calculateAndFormatEvolution(
            _ctx.site[_ctx.sparklineMetric || ""],
            _ctx.site[`previous_${_ctx.sparklineMetric}`] * _ctx.site.ratio,
            true
          )), 3)
        ], 64)) : vue.createCommentVNode("", true)
      ], 8, _hoisted_12$2),
      _ctx.displaySparkline ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_14$1, [
        !_ctx.site.isGroup ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          rel: "noreferrer noopener",
          target: "_blank",
          href: _ctx.dashboardUrl,
          title: _ctx.translate("General_GoTo", _ctx.translate("Dashboard_DashboardOf", _ctx.siteLabel))
        }, [
          vue.createElementVNode("img", {
            alt: "",
            width: "100",
            height: "25",
            src: _ctx.evolutionSparklineSrc
          }, null, 8, _hoisted_16$1)
        ], 8, _hoisted_15$1)) : vue.createCommentVNode("", true)
      ])) : vue.createCommentVNode("", true)
    ], 2);
  }
  const SitesTableSite = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    directives: {
      Tooltips: CoreHome.Tooltips
    },
    components: {
      MatomoLoader: CoreHome.MatomoLoader,
      SitesTableSite
    },
    props: {
      displayRevenue: {
        type: Boolean,
        required: true
      },
      displaySparklines: {
        type: Boolean,
        required: true
      },
      showAiChatbotsRequests: {
        type: Boolean,
        required: true
      },
      isSegmented: {
        type: Boolean,
        required: true
      }
    },
    data() {
      return {
        evolutionSelector: "visits_evolution"
      };
    },
    watch: {
      isSegmented() {
        this.ensureEvolutionSelectorIsValid();
      }
    },
    computed: {
      errorLoading() {
        return DashboardStore$1.state.value.errorLoading;
      },
      errorShowProfessionalHelp() {
        return CoreHome.Matomo.config && CoreHome.Matomo.config.are_ads_enabled;
      },
      evolutionMetric() {
        return this.evolutionSelector;
      },
      isLoading() {
        return DashboardStore$1.state.value.isLoadingSites;
      },
      numberOfFilteredSites() {
        return DashboardStore$1.state.value.numSites;
      },
      paginationCurrentPage() {
        return DashboardStore$1.state.value.paginationCurrentPage;
      },
      paginationLowerBound() {
        return DashboardStore$1.paginationLowerBound.value;
      },
      paginationUpperBound() {
        return DashboardStore$1.paginationUpperBound.value;
      },
      paginationMaxPage() {
        return DashboardStore$1.numberOfPages.value;
      },
      sites() {
        return DashboardStore$1.state.value.dashboardSites;
      },
      sortColumn() {
        return DashboardStore$1.state.value.sortColumn;
      },
      sortColumnClass() {
        return {
          sitesTableSort: true,
          sitesTableSortAsc: this.sortOrder === "asc",
          sitesTableSortDesc: this.sortOrder === "desc"
        };
      },
      sortOrder() {
        return DashboardStore$1.state.value.sortOrder;
      },
      sparklineMetric() {
        switch (this.evolutionMetric) {
          case "hits_evolution":
            return "hits";
          case "pageviews_evolution":
            return "nb_pageviews";
          case "ai_chatbots_requests_evolution":
            return "ai_chatbots_requests";
          case "revenue_evolution":
            return "revenue";
          case "visits_evolution":
            return "nb_visits";
          default:
            return "";
        }
      },
      loadingColspan() {
        let columns = 6;
        if (this.showAiChatbotsRequests && !this.isSegmented) {
          columns += 1;
        }
        if (this.displayRevenue) {
          columns += 1;
        }
        if (this.displaySparklines) {
          columns += 1;
        }
        return columns;
      }
    },
    methods: {
      changeEvolutionSelector(metric) {
        this.evolutionSelector = metric;
        this.sortBy(metric);
      },
      ensureEvolutionSelectorIsValid() {
        if (this.evolutionSelector === "ai_chatbots_requests_evolution" && (this.isSegmented || !this.showAiChatbotsRequests)) {
          this.evolutionSelector = "visits_evolution";
          this.sortBy(this.evolutionSelector);
        }
      },
      navigateNextPage() {
        DashboardStore$1.navigateNextPage();
      },
      navigatePreviousPage() {
        DashboardStore$1.navigatePreviousPage();
      },
      sortBy(column) {
        DashboardStore$1.sortBy(column);
      }
    }
  });
  const _hoisted_1$1 = { class: "sitesTableContainer" };
  const _hoisted_2$1 = { class: "card-table dataTable sitesTable" };
  const _hoisted_3$1 = ["title"];
  const _hoisted_4$1 = ["title"];
  const _hoisted_5$1 = ["title"];
  const _hoisted_6$1 = ["title"];
  const _hoisted_7$1 = ["title"];
  const _hoisted_8$1 = ["title"];
  const _hoisted_9$1 = ["title"];
  const _hoisted_10$1 = { class: "sitesTableEvolutionSelector" };
  const _hoisted_11$1 = ["value"];
  const _hoisted_12$1 = { value: "hits_evolution" };
  const _hoisted_13 = { value: "visits_evolution" };
  const _hoisted_14 = { value: "pageviews_evolution" };
  const _hoisted_15 = {
    key: 0,
    value: "ai_chatbots_requests_evolution"
  };
  const _hoisted_16 = {
    key: 1,
    value: "revenue_evolution"
  };
  const _hoisted_17 = { key: 0 };
  const _hoisted_18 = ["colspan"];
  const _hoisted_19 = {
    key: 0,
    class: "sitesTablePagination"
  };
  const _hoisted_20 = { class: "dataTablePages" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_SitesTableSite = vue.resolveComponent("SitesTableSite");
    const _directive_tooltips = vue.resolveDirective("tooltips");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1$1, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_2$1, [
          vue.createElementVNode("thead", null, [
            vue.createElementVNode("tr", null, [
              vue.createElementVNode("th", {
                onClick: _cache[0] || (_cache[0] = ($event) => _ctx.sortBy("label")),
                class: "label",
                title: _ctx.translate("MultiSites_MetricDocumentationWebsite")
              }, [
                vue.createTextVNode(vue.toDisplayString(_ctx.translate("General_Website")) + " ", 1),
                _ctx.sortColumn === "label" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true)
              ], 8, _hoisted_3$1),
              vue.createElementVNode("th", {
                onClick: _cache[1] || (_cache[1] = ($event) => _ctx.sortBy("nb_visits")),
                title: _ctx.translate("MultiSites_MetricDocumentationVisits")
              }, [
                _ctx.sortColumn === "nb_visits" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ColumnNbVisits")), 1)
              ], 8, _hoisted_4$1),
              vue.createElementVNode("th", {
                onClick: _cache[2] || (_cache[2] = ($event) => _ctx.sortBy("nb_pageviews")),
                title: _ctx.translate("MultiSites_MetricDocumentationPageviews")
              }, [
                _ctx.sortColumn === "nb_pageviews" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ColumnPageviews")), 1)
              ], 8, _hoisted_5$1),
              _ctx.showAiChatbotsRequests && !_ctx.isSegmented ? (vue.openBlock(), vue.createElementBlock("th", {
                key: 0,
                onClick: _cache[3] || (_cache[3] = ($event) => _ctx.sortBy("ai_chatbots_requests")),
                title: _ctx.translate("MultiSites_MetricDocumentationAiChatbotsRequests")
              }, [
                _ctx.sortColumn === "ai_chatbots_requests" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("MultiSites_AiChatbotsRequests")), 1)
              ], 8, _hoisted_6$1)) : vue.createCommentVNode("", true),
              vue.createElementVNode("th", {
                onClick: _cache[4] || (_cache[4] = ($event) => _ctx.sortBy("hits")),
                title: _ctx.translate(_ctx.showAiChatbotsRequests && !_ctx.isSegmented ? "MultiSites_MetricDocumentationHitsIncludingAi" : "MultiSites_MetricDocumentationHits")
              }, [
                _ctx.sortColumn === "hits" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ColumnHits")), 1)
              ], 8, _hoisted_7$1),
              _ctx.displayRevenue ? (vue.openBlock(), vue.createElementBlock("th", {
                key: 1,
                onClick: _cache[5] || (_cache[5] = ($event) => _ctx.sortBy("revenue")),
                title: _ctx.translate("MultiSites_MetricDocumentationRevenue")
              }, [
                _ctx.sortColumn === "revenue" ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_ColumnRevenue")), 1)
              ], 8, _hoisted_8$1)) : vue.createCommentVNode("", true),
              vue.createElementVNode("th", {
                onClick: _cache[6] || (_cache[6] = ($event) => _ctx.sortBy(_ctx.evolutionSelector)),
                title: _ctx.translate("MultiSites_MetricDocumentationEvolution")
              }, [
                _ctx.sortColumn === _ctx.evolutionSelector ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: vue.normalizeClass(_ctx.sortColumnClass)
                }, null, 2)) : vue.createCommentVNode("", true),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("MultiSites_Evolution")), 1)
              ], 8, _hoisted_9$1),
              vue.createElementVNode("th", _hoisted_10$1, [
                vue.createElementVNode("select", {
                  class: "browser-default",
                  value: _ctx.evolutionSelector,
                  onChange: _cache[7] || (_cache[7] = ($event) => _ctx.changeEvolutionSelector($event.target.value))
                }, [
                  vue.createElementVNode("option", _hoisted_12$1, vue.toDisplayString(_ctx.translate("General_ColumnHits")), 1),
                  vue.createElementVNode("option", _hoisted_13, vue.toDisplayString(_ctx.translate("General_ColumnNbVisits")), 1),
                  vue.createElementVNode("option", _hoisted_14, vue.toDisplayString(_ctx.translate("General_ColumnPageviews")), 1),
                  _ctx.showAiChatbotsRequests && !_ctx.isSegmented ? (vue.openBlock(), vue.createElementBlock("option", _hoisted_15, vue.toDisplayString(_ctx.translate("MultiSites_AiChatbotsRequests")), 1)) : vue.createCommentVNode("", true),
                  _ctx.displayRevenue ? (vue.openBlock(), vue.createElementBlock("option", _hoisted_16, vue.toDisplayString(_ctx.translate("General_ColumnRevenue")), 1)) : vue.createCommentVNode("", true)
                ], 40, _hoisted_11$1)
              ])
            ])
          ]),
          vue.createElementVNode("tbody", null, [
            _ctx.isLoading ? (vue.openBlock(), vue.createElementBlock("tr", _hoisted_17, [
              vue.createElementVNode("td", {
                class: "sitesTableLoading",
                colspan: _ctx.loadingColspan
              }, [
                vue.createVNode(_component_MatomoLoader)
              ], 8, _hoisted_18)
            ])) : (vue.openBlock(true), vue.createElementBlock(vue.Fragment, { key: 1 }, vue.renderList(_ctx.sites, (site) => {
              return vue.openBlock(), vue.createBlock(_component_SitesTableSite, {
                "display-revenue": _ctx.displayRevenue,
                "evolution-metric": _ctx.evolutionMetric,
                key: `site-${site.idsite}`,
                "model-value": site,
                "display-sparkline": _ctx.displaySparklines,
                "sparkline-metric": _ctx.sparklineMetric,
                "show-ai-chatbots-requests": _ctx.showAiChatbotsRequests && !_ctx.isSegmented
              }, null, 8, ["display-revenue", "evolution-metric", "model-value", "display-sparkline", "sparkline-metric", "show-ai-chatbots-requests"]);
            }), 128))
          ])
        ])), [
          [_directive_tooltips]
        ])
      ]),
      !_ctx.isLoading || _ctx.paginationUpperBound > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_19, [
        vue.withDirectives(vue.createElementVNode("span", {
          class: "dataTablePrevious",
          onClick: _cache[8] || (_cache[8] = ($event) => _ctx.navigatePreviousPage())
        }, " « " + vue.toDisplayString(_ctx.translate("General_Previous")), 513), [
          [vue.vShow, _ctx.paginationCurrentPage !== 0]
        ]),
        vue.createElementVNode("span", _hoisted_20, vue.toDisplayString(_ctx.translate(
          "General_Pagination",
          String(_ctx.paginationLowerBound),
          String(_ctx.paginationUpperBound),
          String(_ctx.numberOfFilteredSites)
        )), 1),
        vue.withDirectives(vue.createElementVNode("span", {
          class: "dataTableNext",
          onClick: _cache[9] || (_cache[9] = ($event) => _ctx.navigateNextPage())
        }, vue.toDisplayString(_ctx.translate("General_Next")) + " » ", 513), [
          [vue.vShow, _ctx.paginationCurrentPage < _ctx.paginationMaxPage]
        ])
      ])) : vue.createCommentVNode("", true)
    ], 64);
  }
  const SitesTable = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    components: {
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      KPICardContainer,
      SitesTable
    },
    props: {
      autoRefreshInterval: {
        type: Number,
        required: true
      },
      displayRevenue: {
        type: Boolean,
        required: true
      },
      displaySparklines: {
        type: Boolean,
        required: true
      },
      hasBotTrackingEnabled: {
        type: Boolean,
        required: true
      },
      isWidgetized: {
        type: Boolean,
        required: true
      },
      pageSize: {
        type: Number,
        required: true
      }
    },
    data() {
      return {
        searchTerm: ""
      };
    },
    mounted() {
      vue.watch(() => CoreHome.MatomoUrl.hashParsed.value, () => DashboardStore$1.reloadDashboard());
      DashboardStore$1.setAutoRefreshInterval(this.autoRefreshInterval);
      DashboardStore$1.setPageSize(this.pageSize);
      DashboardStore$1.reloadDashboard();
    },
    computed: {
      addSiteUrl() {
        return `?${CoreHome.MatomoUrl.stringify(__spreadProps(__spreadValues(__spreadValues({}, CoreHome.MatomoUrl.urlParsed.value), CoreHome.MatomoUrl.hashParsed.value), {
          module: "SitesManager",
          action: "index",
          showaddsite: "1"
        }))}`;
      },
      isLoadingKPIs() {
        return DashboardStore$1.state.value.isLoadingKPIs;
      },
      errorLoading() {
        return DashboardStore$1.state.value.errorLoading;
      },
      isSegmented() {
        return !!CoreHome.MatomoUrl.parsed.value.segment;
      },
      kpis() {
        var _a, _b, _c, _d, _e;
        const { dashboardKPIs } = DashboardStore$1.state.value;
        const { hasBotTrackingEnabled, isSegmented } = this;
        const kpis = [
          {
            badge: ((_a = dashboardKPIs.badges) == null ? void 0 : _a.visits) || null,
            icon: "icon-user",
            title: "MultiSites_TotalVisits",
            value: dashboardKPIs.visits,
            valueCompact: dashboardKPIs.visitsCompact,
            evolutionPeriod: dashboardKPIs.evolutionPeriod,
            evolutionTrend: dashboardKPIs.visitsTrend,
            evolutionValue: dashboardKPIs.visitsEvolution
          },
          {
            badge: ((_b = dashboardKPIs.badges) == null ? void 0 : _b.pageviews) || null,
            icon: "icon-show",
            title: "MultiSites_TotalPageviews",
            value: dashboardKPIs.pageviews,
            valueCompact: dashboardKPIs.pageviewsCompact,
            evolutionPeriod: dashboardKPIs.evolutionPeriod,
            evolutionTrend: dashboardKPIs.pageviewsTrend,
            evolutionValue: dashboardKPIs.pageviewsEvolution
          }
        ];
        if (hasBotTrackingEnabled) {
          kpis.push({
            badge: isSegmented ? {
              label: CoreHome.translate("MultiSites_SegmentationNotSupported"),
              title: CoreHome.translate("MultiSites_AiChatbotsSegmentationTooltip")
            } : (_c = dashboardKPIs.badges) == null ? void 0 : _c.aiChatbotsRequests,
            icon: "icon-admin-platform",
            title: "MultiSites_TotalAiChatbotsRequests",
            tooltipBody: isSegmented ? "MultiSites_AiChatbotsSegmentationTooltip" : void 0,
            value: dashboardKPIs.aiChatbotsRequests,
            valueCompact: dashboardKPIs.aiChatbotsRequestsCompact,
            evolutionPeriod: dashboardKPIs.evolutionPeriod,
            evolutionTrend: dashboardKPIs.aiChatbotsRequestsTrend,
            evolutionValue: dashboardKPIs.aiChatbotsRequestsEvolution
          });
        }
        kpis.push({
          badge: ((_d = dashboardKPIs.badges) == null ? void 0 : _d.hits) || null,
          icon: "icon-hits",
          title: "MultiSites_TotalHits",
          tooltipBody: !isSegmented && hasBotTrackingEnabled ? "MultiSites_TotalHitsIncludingAiTooltip" : void 0,
          value: dashboardKPIs.hits,
          valueCompact: dashboardKPIs.hitsCompact,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.hitsTrend,
          evolutionValue: dashboardKPIs.hitsEvolution
        });
        if (this.displayRevenue) {
          kpis.push({
            badge: ((_e = dashboardKPIs.badges) == null ? void 0 : _e.revenue) || null,
            icon: "icon-dollar-sign",
            title: "General_TotalRevenue",
            value: dashboardKPIs.revenue,
            valueCompact: dashboardKPIs.revenueCompact,
            evolutionPeriod: dashboardKPIs.evolutionPeriod,
            evolutionTrend: dashboardKPIs.revenueTrend,
            evolutionValue: dashboardKPIs.revenueEvolution
          });
        }
        return kpis;
      },
      isUserAllowedToAddSite() {
        return CoreHome.Matomo.hasSuperUserAccess;
      }
    },
    methods: {
      searchSite(term) {
        DashboardStore$1.searchSite(term);
      }
    }
  });
  const _hoisted_1 = { class: "dashboardHeader" };
  const _hoisted_2 = { class: "card-title" };
  const _hoisted_3 = { key: 0 };
  const _hoisted_4 = { class: "notification system notification-error" };
  const _hoisted_5 = ["href"];
  const _hoisted_6 = ["href"];
  const _hoisted_7 = ["href"];
  const _hoisted_8 = { class: "dashboardControls" };
  const _hoisted_9 = { class: "siteSearch" };
  const _hoisted_10 = ["placeholder"];
  const _hoisted_11 = ["title"];
  const _hoisted_12 = ["href"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_KPICardContainer = vue.resolveComponent("KPICardContainer");
    const _component_SitesTable = vue.resolveComponent("SitesTable");
    return vue.openBlock(), vue.createElementBlock(vue.Fragment, null, [
      vue.createElementVNode("div", _hoisted_1, [
        vue.createElementVNode("h1", _hoisted_2, [
          vue.createVNode(_component_EnrichedHeadline, {
            "feature-name": _ctx.translate("MultiSites_AllWebsitesDashboardTitle")
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("MultiSites_AllWebsitesDashboardTitle")), 1)
            ]),
            _: 1
          }, 8, ["feature-name"])
        ])
      ]),
      _ctx.errorLoading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
        vue.createElementVNode("div", _hoisted_4, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate("MultiSites_AllWebsitesDashboardErrorMessage")) + " ", 1),
          _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1)),
          _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
          vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_NeedMoreHelp", "", "")) + " ", 1),
          vue.createElementVNode("a", {
            rel: "noreferrer noopener",
            target: "_blank",
            href: _ctx.externalRawLink("https://matomo.org/faq/troubleshooting/faq_19489/")
          }, vue.toDisplayString(_ctx.translate("General_Faq")), 9, _hoisted_5),
          _cache[5] || (_cache[5] = vue.createTextVNode(" – ", -1)),
          vue.createElementVNode("a", {
            rel: "noreferrer noopener",
            target: "_blank",
            href: _ctx.externalRawLink("https://forum.matomo.org/")
          }, vue.toDisplayString(_ctx.translate("Feedback_CommunityHelp")), 9, _hoisted_6),
          _cache[6] || (_cache[6] = vue.createTextVNode(" – ", -1)),
          vue.createElementVNode("a", {
            rel: "noreferrer noopener",
            target: "_blank",
            href: _ctx.externalRawLink("https://matomo.org/support-plans/")
          }, vue.toDisplayString(_ctx.translate("Feedback_ProfessionalHelp")), 9, _hoisted_7),
          _cache[7] || (_cache[7] = vue.createTextVNode(". ", -1))
        ])
      ])) : vue.createCommentVNode("", true),
      vue.createVNode(_component_KPICardContainer, {
        "is-loading": _ctx.isLoadingKPIs,
        "model-value": _ctx.kpis
      }, null, 8, ["is-loading", "model-value"]),
      vue.createElementVNode("div", _hoisted_8, [
        vue.createElementVNode("div", _hoisted_9, [
          vue.withDirectives(vue.createElementVNode("input", {
            type: "text",
            onKeydown: _cache[0] || (_cache[0] = vue.withKeys(($event) => _ctx.searchSite(_ctx.searchTerm), ["enter"])),
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.searchTerm = $event),
            placeholder: _ctx.translate("Actions_SubmenuSitesearch")
          }, null, 40, _hoisted_10), [
            [vue.vModelText, _ctx.searchTerm]
          ]),
          vue.createElementVNode("span", {
            class: "icon-search",
            onClick: _cache[2] || (_cache[2] = ($event) => _ctx.searchSite(_ctx.searchTerm)),
            title: _ctx.translate("General_ClickToSearch")
          }, null, 8, _hoisted_11)
        ]),
        !_ctx.isWidgetized && _ctx.isUserAllowedToAddSite ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          class: "btn",
          href: _ctx.addSiteUrl
        }, vue.toDisplayString(_ctx.translate("SitesManager_AddSite")), 9, _hoisted_12)) : vue.createCommentVNode("", true)
      ]),
      vue.createVNode(_component_SitesTable, {
        "display-revenue": _ctx.displayRevenue,
        "display-sparklines": _ctx.displaySparklines,
        "show-ai-chatbots-requests": _ctx.hasBotTrackingEnabled,
        "is-segmented": _ctx.isSegmented
      }, null, 8, ["display-revenue", "display-sparklines", "show-ai-chatbots-requests", "is-segmented"])
    ], 64);
  }
  const AllWebsitesDashboard = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.AllWebsitesDashboard = AllWebsitesDashboard;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
