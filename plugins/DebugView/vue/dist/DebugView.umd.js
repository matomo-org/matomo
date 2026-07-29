(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.DebugView = {}, global.Vue, global.CoreHome));
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

  const _sfc_main$5 = vue.defineComponent({
    props: {
      buckets: {
        type: Array,
        required: true
      },
      selectedMinute: {
        type: Number,
        default: null
      },
      pendingCount: {
        type: Number,
        default: 0
      },
      paused: Boolean
    },
    emits: ["selectMinute"],
    methods: {
      getDotLabel(bucket, index) {
        const parts = [];
        if (bucket.count > 1) {
          parts.push(CoreHome.translate("DebugView_HitsInMinute", `${bucket.count}`, bucket.label));
        } else if (bucket.count === 1) {
          parts.push(CoreHome.translate("DebugView_OneHitInMinute", bucket.label));
        } else {
          parts.push(CoreHome.translate("DebugView_NoHitsInMinute", bucket.label));
        }
        if (index === 0) {
          parts.push(CoreHome.translate("DebugView_CurrentMinute"));
        }
        if (bucket.minuteStart === this.selectedMinute) {
          parts.push(CoreHome.translate("DebugView_SelectedMinute"));
        }
        if (bucket.count > 0) {
          parts.push(CoreHome.translate("DebugView_JumpToMinute", bucket.label));
        }
        return parts.join(". ");
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
  const _hoisted_1$5 = { class: "debugViewMinutesRail" };
  const _hoisted_2$5 = ["aria-label"];
  const _hoisted_3$5 = ["disabled", "aria-label", "onClick"];
  const _hoisted_4$4 = {
    key: 0,
    class: "debugViewMinuteCount"
  };
  const _hoisted_5$4 = {
    key: 0,
    class: "debugViewMinuteLabel",
    "aria-hidden": "true"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
      vue.createElementVNode("div", {
        class: vue.normalizeClass(["debugViewPendingBadge", { "debugViewPendingBadge--paused": _ctx.paused }]),
        "aria-live": "polite"
      }, vue.toDisplayString(_ctx.translate("DebugView_NewHitsSincePaused", `${_ctx.pendingCount}`)), 3),
      vue.createElementVNode("ol", {
        class: "debugViewMinutesList",
        "aria-label": _ctx.translate("DebugView_MinutesTimeline")
      }, [
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.buckets, (bucket, index) => {
          return vue.openBlock(), vue.createElementBlock("li", {
            key: bucket.minuteStart,
            class: vue.normalizeClass(["debugViewMinuteItem", { "debugViewMinuteItem--has-hits": bucket.count > 0 }])
          }, [
            vue.createElementVNode("button", {
              type: "button",
              class: vue.normalizeClass(["debugViewMinuteDot", {
                "debugViewMinuteDot--has-hits": bucket.count > 0,
                "debugViewMinuteDot--current": index === 0,
                "debugViewMinuteDot--selected": bucket.minuteStart === _ctx.selectedMinute
              }]),
              disabled: bucket.count === 0,
              "aria-label": _ctx.getDotLabel(bucket, index),
              onClick: ($event) => _ctx.$emit("selectMinute", bucket.minuteStart)
            }, [
              bucket.count > 0 ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$4, vue.toDisplayString(bucket.count), 1)) : vue.createCommentVNode("", true)
            ], 10, _hoisted_3$5),
            bucket.showLabel ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$4, vue.toDisplayString(bucket.label), 1)) : vue.createCommentVNode("", true)
          ], 2);
        }), 128))
      ], 8, _hoisted_2$5)
    ]);
  }
  const MinutesRail = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const HIT_TYPES = {
    pageview: { iconSvg: "plugins/Morpheus/images/action.svg", labelKey: "DebugView_TypePageview" },
    event: { iconSvg: "plugins/Morpheus/images/event.svg", labelKey: "DebugView_TypeEvent" },
    goal: { iconSvg: "plugins/Morpheus/images/goal.svg", labelKey: "DebugView_TypeGoal" },
    download: {
      iconSvg: "plugins/Morpheus/images/download.svg",
      labelKey: "DebugView_TypeDownload"
    },
    outlink: { iconSvg: "plugins/Morpheus/images/link.svg", labelKey: "DebugView_TypeOutlink" },
    search: { iconSvg: "plugins/Morpheus/images/search.svg", labelKey: "DebugView_TypeSearch" },
    ecommerceOrder: {
      iconSvg: "plugins/Morpheus/images/ecommerceOrder.svg",
      labelKey: "DebugView_TypeEcommerceOrder"
    },
    ecommerceAbandonedCart: {
      iconSvg: "plugins/Morpheus/images/ecommerceAbandonedCart.svg",
      labelKey: "DebugView_TypeEcommerceAbandonedCart"
    },
    content: {
      iconSvg: "plugins/Morpheus/images/contentinteraction.svg",
      labelKey: "DebugView_TypeContent"
    },
    ping: { icon: "icon-heart", labelKey: "DebugView_TypePing" },
    media: { iconSvg: "plugins/MediaAnalytics/images/video.png", labelKey: "DebugView_TypeMedia" },
    form: { iconSvg: "plugins/FormAnalytics/images/form.png", labelKey: "DebugView_TypeForm" },
    crash: { iconSvg: "plugins/CrashAnalytics/images/crash.png", labelKey: "DebugView_TypeCrash" },
    // the visits log links session recordings with the play icon
    sessionRecording: { icon: "icon-play", labelKey: "DebugView_TypeSessionRecording" },
    other: { icon: "icon-help", labelKey: "DebugView_TypeVendor" }
  };
  function getHitTypeInfo(type, trackingParams) {
    const knownType = Object.prototype.hasOwnProperty.call(HIT_TYPES, type) ? type : "other";
    const entry = HIT_TYPES[knownType];
    let iconSvg = entry.iconSvg || null;
    if (knownType === "media" && trackingParams && trackingParams.ma_mt === "audio") {
      iconSvg = "plugins/MediaAnalytics/images/audio.png";
    }
    if (knownType === "content" && trackingParams && !trackingParams.c_i) {
      iconSvg = "plugins/Morpheus/images/contentimpression.svg";
    }
    return {
      icon: entry.icon || "",
      iconSvg,
      labelKey: entry.labelKey,
      cssClass: `debugViewHitIconCircle--${knownType}`
    };
  }
  const _sfc_main$4 = vue.defineComponent({
    props: {
      hit: {
        type: Object,
        required: true
      },
      isSelected: Boolean
    },
    emits: ["open"],
    computed: {
      typeInfo() {
        return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
      },
      ariaLabel() {
        let typeLabel = CoreHome.translate(this.typeInfo.labelKey);
        if (this.hit.isBot) {
          typeLabel = `${typeLabel} (${this.hit.botName || CoreHome.translate("DebugView_BotBadge")})`;
        }
        return `${typeLabel}: ${this.hit.title}, ${this.hit.timePretty}`;
      }
    }
  });
  const _hoisted_1$4 = ["data-hit-id", "aria-label", "title"];
  const _hoisted_2$4 = ["src"];
  const _hoisted_3$4 = { class: "debugViewHitTitle" };
  const _hoisted_4$3 = ["title"];
  const _hoisted_5$3 = { class: "debugViewHitTime" };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("button", {
      type: "button",
      class: vue.normalizeClass(["debugViewHitRow", { "debugViewHitRow--selected": _ctx.isSelected }]),
      "data-hit-id": _ctx.hit.idRawRequest,
      "aria-label": _ctx.ariaLabel,
      "aria-haspopup": "dialog",
      title: _ctx.hit.subtitle || void 0,
      onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("open"))
    }, [
      vue.createElementVNode("span", {
        class: vue.normalizeClass(["debugViewHitIconCircle", _ctx.typeInfo.cssClass]),
        "aria-hidden": "true"
      }, [
        _ctx.typeInfo.iconSvg ? (vue.openBlock(), vue.createElementBlock("img", {
          key: 0,
          src: _ctx.typeInfo.iconSvg,
          alt: ""
        }, null, 8, _hoisted_2$4)) : (vue.openBlock(), vue.createElementBlock("span", {
          key: 1,
          class: vue.normalizeClass(_ctx.typeInfo.icon)
        }, null, 2))
      ], 2),
      vue.createElementVNode("span", _hoisted_3$4, vue.toDisplayString(_ctx.hit.title), 1),
      _ctx.hit.isBot ? (vue.openBlock(), vue.createElementBlock("span", {
        key: 0,
        class: "debugViewBotBadge",
        title: _ctx.hit.botName || void 0
      }, vue.toDisplayString(_ctx.translate("DebugView_BotBadge")), 9, _hoisted_4$3)) : vue.createCommentVNode("", true),
      vue.createElementVNode("span", _hoisted_5$3, vue.toDisplayString(_ctx.hit.timePretty), 1)
    ], 10, _hoisted_1$4);
  }
  const HitRow = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    props: {
      hits: {
        type: Array,
        required: true
      },
      selectedHitId: {
        type: String,
        default: null
      }
    },
    components: {
      HitRow
    },
    emits: ["openHit"],
    computed: {
      // hits are ordered newest first; each item carries the elapsed-time gap to
      // the next newer hit shown directly above it
      items() {
        return this.hits.map((hit, index) => {
          let gapLabel = null;
          if (index > 0) {
            const gap = this.hits[index - 1].timestamp - hit.timestamp;
            if (gap > 0 && gap < 60) {
              gapLabel = CoreHome.translate("DebugView_SecondsAgoShort", `${gap}`);
            } else if (gap >= 60) {
              gapLabel = CoreHome.translate("DebugView_MinutesAgoShort", `${Math.floor(gap / 60)}`);
            }
          }
          return { hit, gapLabel };
        });
      }
    },
    methods: {
      findRowElement(hitId) {
        const root = this.$refs.root;
        if (!root) {
          return null;
        }
        const rows = root.querySelectorAll(".debugViewHitRow");
        for (let i = 0; i < rows.length; i += 1) {
          if (rows[i].getAttribute("data-hit-id") === hitId) {
            return rows[i];
          }
        }
        return null;
      },
      focusHit(hitId) {
        const row = this.findRowElement(hitId);
        if (row) {
          row.focus();
        }
      },
      scrollToMinute(minuteStart) {
        const hit = this.hits.find(
          (candidate) => candidate.timestamp >= minuteStart && candidate.timestamp < minuteStart + 60
        );
        if (!hit) {
          return;
        }
        const row = this.findRowElement(hit.idRawRequest);
        if (row && typeof row.scrollIntoView === "function") {
          const reducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
          row.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
        }
      }
    }
  });
  const _hoisted_1$3 = {
    class: "debugViewStream",
    ref: "root"
  };
  const _hoisted_2$3 = {
    key: 0,
    class: "debugViewHitGap",
    "aria-hidden": "true"
  };
  const _hoisted_3$3 = { class: "debugViewHitGapLabel" };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_HitRow = vue.resolveComponent("HitRow");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
      vue.createVNode(vue.TransitionGroup, {
        tag: "ol",
        name: "debugViewHitAnim",
        class: "debugViewStreamList",
        "aria-label": _ctx.translate("DebugView_SecondsStream")
      }, {
        default: vue.withCtx(() => [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.items, (item) => {
            return vue.openBlock(), vue.createElementBlock("li", {
              key: item.hit.idRawRequest,
              class: "debugViewStreamItem"
            }, [
              item.gapLabel ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, [
                vue.createElementVNode("span", _hoisted_3$3, vue.toDisplayString(item.gapLabel), 1)
              ])) : vue.createCommentVNode("", true),
              vue.createVNode(_component_HitRow, {
                hit: item.hit,
                "is-selected": item.hit.idRawRequest === _ctx.selectedHitId,
                onOpen: ($event) => _ctx.$emit("openHit", item.hit)
              }, null, 8, ["hit", "is-selected", "onOpen"])
            ]);
          }), 128))
        ]),
        _: 1
      }, 8, ["aria-label"])
    ], 512);
  }
  const HitsStream = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const MAX_DEPTH = 4;
  const REDACTED_SENTINEL = "__redacted__";
  function isSkippedKey(key) {
    const lowerKey = key.toLowerCase();
    return lowerKey.endsWith("icon") || lowerKey.endsWith("iconsvg");
  }
  function isSkippedValue(value) {
    return value === null || value === void 0 || value === "";
  }
  function toText(value) {
    if (value === REDACTED_SENTINEL) {
      return CoreHome.translate("DebugView_Redacted");
    }
    if (value !== null && typeof value === "object") {
      try {
        return JSON.stringify(value);
      } catch (e) {
        return String(value);
      }
    }
    return String(value);
  }
  const _sfc_main$2 = vue.defineComponent({
    name: "DetailRows",
    props: {
      entries: {
        type: Object,
        required: true
      },
      depth: {
        type: Number,
        default: 0
      }
    },
    data() {
      return {
        expandedKeys: {}
      };
    },
    watch: {
      entries() {
        this.expandedKeys = {};
      }
    },
    computed: {
      visibleEntries() {
        const result = [];
        Object.keys(this.entries).forEach((key) => {
          const value = this.entries[key];
          if (isSkippedValue(value) || isSkippedKey(key)) {
            return;
          }
          if (value && typeof value === "object" && this.depth < MAX_DEPTH) {
            const children = value;
            if (Object.keys(children).length) {
              result.push({ key, text: "", children });
            }
            return;
          }
          result.push({ key, text: toText(value), children: null });
        });
        return result;
      }
    },
    methods: {
      toggle(key) {
        this.expandedKeys[key] = !this.expandedKeys[key];
      },
      isExpanded(key) {
        return !!this.expandedKeys[key];
      }
    }
  });
  const _hoisted_1$2 = {
    key: 0,
    class: "debugViewDetailNested"
  };
  const _hoisted_2$2 = { class: "debugViewDetailKey" };
  const _hoisted_3$2 = ["aria-expanded", "title", "onClick"];
  const _hoisted_4$2 = { class: "debugViewDetailKey" };
  const _hoisted_5$2 = { class: "debugViewDetailValue" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DetailRows = vue.resolveComponent("DetailRows", true);
    return vue.openBlock(), vue.createElementBlock("ul", {
      class: vue.normalizeClass(["debugViewDetailRows", { "debugViewDetailRows--nested": _ctx.depth > 0 }])
    }, [
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.visibleEntries, (entry) => {
        return vue.openBlock(), vue.createElementBlock("li", {
          key: entry.key,
          class: "debugViewDetailRow"
        }, [
          entry.children ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
            vue.createElementVNode("span", _hoisted_2$2, vue.toDisplayString(entry.key), 1),
            vue.createVNode(_component_DetailRows, {
              entries: entry.children,
              depth: _ctx.depth + 1
            }, null, 8, ["entries", "depth"])
          ])) : (vue.openBlock(), vue.createElementBlock("button", {
            key: 1,
            type: "button",
            class: vue.normalizeClass(["debugViewDetailValueRow", { "debugViewDetailValueRow--expanded": _ctx.isExpanded(entry.key) }]),
            "aria-expanded": _ctx.isExpanded(entry.key) ? "true" : "false",
            title: entry.text,
            onClick: ($event) => _ctx.toggle(entry.key)
          }, [
            vue.createElementVNode("span", _hoisted_4$2, vue.toDisplayString(entry.key), 1),
            vue.createElementVNode("span", _hoisted_5$2, vue.toDisplayString(entry.text), 1)
          ], 10, _hoisted_3$2))
        ]);
      }), 128))
    ], 2);
  }
  const DetailRows = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      hit: {
        type: Object,
        required: true
      },
      idSite: {
        type: Number,
        required: true
      }
    },
    components: {
      DetailRows,
      MatomoLoader: CoreHome.MatomoLoader
    },
    emits: ["close"],
    data() {
      return {
        activeTab: "params",
        visitDetails: null,
        visitLoadState: "loading"
      };
    },
    computed: {
      typeInfo() {
        return getHitTypeInfo(this.hit.type, this.hit.trackingParams);
      },
      paneLabel() {
        return this.hit.title || CoreHome.translate("DebugView_HitDetails");
      },
      trackingParamsEntries() {
        return this.hit.trackingParams || {};
      },
      hasTrackingParams() {
        return !!this.hit.trackingParams && Object.keys(this.hit.trackingParams).length > 0;
      },
      defaultParamsEntries() {
        return this.hit.trackingParamsDefaults || {};
      },
      hasDefaultParams() {
        return !!this.hit.trackingParamsDefaults && Object.keys(this.hit.trackingParamsDefaults).length > 0;
      },
      otherParamsEntries() {
        return this.hit.trackingParamsOther || {};
      },
      hasOtherParams() {
        return !!this.hit.trackingParamsOther && Object.keys(this.hit.trackingParamsOther).length > 0;
      },
      // the processed Live actions belonging to this raw request. Core actions
      // are matched via the log_link_visit_action id (a pageview and the goal it
      // triggered share it); media, form and crash actions have no such row, so
      // they are matched via their own identifiers. Heatmap & Session Recording
      // requests are deliberately not matched (nothing sensible to show).
      matchedActions() {
        if (!this.visitDetails || this.hit.type === "sessionRecording") {
          return [];
        }
        const rawActions = this.visitDetails.actionDetails;
        if (!Array.isArray(rawActions)) {
          return [];
        }
        const actions = rawActions;
        const params = this.hit.trackingParams || {};
        switch (this.hit.type) {
          case "media": {
            const resource = params.ma_re;
            if (!resource) {
              return [];
            }
            return actions.filter(
              (action) => action.type === "media" && String(action.url) === String(resource)
            );
          }
          case "crash": {
            const cra = typeof params.cra === "string" ? params.cra : "";
            if (!cra) {
              return [];
            }
            const prefix = cra.replace(/\.\.\.$/, "");
            return actions.filter(
              (action) => action.type === "crash" && typeof action.message === "string" && (action.message === cra || action.message.indexOf(prefix) === 0)
            );
          }
          case "form": {
            const pvId = params.pv_id;
            const faId = params.fa_id;
            if (pvId) {
              return actions.filter(
                (action) => action.type === "form" && String(action.idpageview) === String(pvId)
              );
            }
            if (faId) {
              return actions.filter(
                (action) => action.type === "form" && String(action.formId) === String(faId)
              );
            }
            return [];
          }
          default: {
            if (!this.hit.idLinkVa) {
              return [];
            }
            const idLinkVa = Number(this.hit.idLinkVa);
            return actions.filter(
              (action) => Number(action.pageId) === idLinkVa || Number(action.goalPageId) === idLinkVa
            );
          }
        }
      },
      visitEntries() {
        if (!this.visitDetails) {
          return {};
        }
        const entries = __spreadValues({}, this.visitDetails);
        delete entries.actionDetails;
        return entries;
      }
    },
    watch: {
      "hit.idRawRequest": function onHitChange() {
        this.activeTab = "params";
        this.loadVisit();
        this.$nextTick(() => this.focusCloseButton());
      }
    },
    mounted() {
      this.focusCloseButton();
      this.loadVisit();
    },
    methods: {
      // lazily loads the visit this hit belongs to, directly from the browser via
      // the visitId segment; the raw parameters render without waiting for it
      loadVisit() {
        this.visitDetails = null;
        if (this.hit.isBot) {
          this.visitLoadState = "bot";
          return;
        }
        if (!this.hit.idVisit) {
          this.visitLoadState = "unavailable";
          return;
        }
        this.visitLoadState = "loading";
        const requestedHitId = this.hit.idRawRequest;
        CoreHome.AjaxHelper.fetch(
          {
            method: "Live.getLastVisitsDetails",
            idSite: this.idSite,
            segment: `visitId==${this.hit.idVisit}`,
            filter_limit: 1,
            // pin the date window explicitly: AjaxHelper would otherwise inject
            // the page URL's current period/date (e.g. a stale date=yesterday),
            // scoping the lookup so the just-tracked visit is never found.
            // last2 (yesterday + today) always covers the <= 60 min stream
            // window, including around the site-timezone midnight
            period: "range",
            date: "last2"
          },
          { createErrorNotification: false }
        ).then((visits) => {
          if (requestedHitId !== this.hit.idRawRequest) {
            return;
          }
          const visit = Array.isArray(visits) && visits.length ? visits[0] : null;
          if (visit) {
            this.visitDetails = visit;
            this.visitLoadState = "loaded";
          } else {
            this.visitLoadState = "unavailable";
          }
        }).catch(() => {
          if (requestedHitId === this.hit.idRawRequest) {
            this.visitLoadState = "unavailable";
          }
        });
      },
      focusCloseButton() {
        const closeButton = this.$refs.closeButton;
        if (closeButton) {
          closeButton.focus();
        }
      },
      toggleTab() {
        this.activeTab = this.activeTab === "params" ? "processed" : "params";
        this.$nextTick(() => {
          const pane = this.$refs.pane;
          const active = pane && pane.querySelector('[role="tab"][aria-selected="true"]');
          if (active) {
            active.focus();
          }
        });
      },
      // below the responsive breakpoint the pane overlays the stream, so keep
      // focus inside it while it is open (the content behind it is obscured)
      onTabKey(event) {
        if (!window.matchMedia || !window.matchMedia("(max-width: 960px)").matches) {
          return;
        }
        const pane = this.$refs.pane;
        if (!pane) {
          return;
        }
        const focusable = Array.from(pane.querySelectorAll(
          'button:not([tabindex="-1"]), [href], input, select, textarea, [tabindex="0"]'
        )).filter((el) => el.offsetParent !== null);
        if (!focusable.length) {
          return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    }
  });
  const _hoisted_1$1 = ["aria-label"];
  const _hoisted_2$1 = { class: "debugViewDetailsHeader" };
  const _hoisted_3$1 = ["src"];
  const _hoisted_4$1 = ["title"];
  const _hoisted_5$1 = ["title"];
  const _hoisted_6$1 = ["aria-label"];
  const _hoisted_7$1 = {
    class: "debugViewDetailsTabs",
    role: "tablist"
  };
  const _hoisted_8$1 = ["aria-selected", "tabindex"];
  const _hoisted_9$1 = ["aria-selected", "tabindex"];
  const _hoisted_10$1 = ["aria-labelledby"];
  const _hoisted_11$1 = { class: "debugViewDetailsSection" };
  const _hoisted_12 = { class: "debugViewDetailsSection" };
  const _hoisted_13 = { class: "debugViewDetailsSection" };
  const _hoisted_14 = { class: "debugViewDetailsSection" };
  const _hoisted_15 = { class: "debugViewVisitUnavailable" };
  const _hoisted_16 = {
    key: 1,
    class: "debugViewLazyLoading"
  };
  const _hoisted_17 = { class: "debugViewDetailsSection" };
  const _hoisted_18 = { class: "debugViewVisitUnavailable" };
  const _hoisted_19 = { class: "debugViewDetailsSection" };
  const _hoisted_20 = {
    key: 0,
    class: "debugViewProcessedHint"
  };
  const _hoisted_21 = { class: "debugViewDetailsSection" };
  const _hoisted_22 = {
    key: 3,
    class: "debugViewVisitUnavailable"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DetailRows = vue.resolveComponent("DetailRows");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    return vue.openBlock(), vue.createElementBlock("div", {
      ref: "pane",
      class: "debugViewDetailsPane",
      role: "dialog",
      "aria-label": _ctx.paneLabel,
      onKeydown: [
        _cache[7] || (_cache[7] = vue.withKeys(vue.withModifiers(($event) => _ctx.$emit("close"), ["prevent"]), ["esc"])),
        _cache[8] || (_cache[8] = vue.withKeys((...args) => _ctx.onTabKey && _ctx.onTabKey(...args), ["tab"]))
      ]
    }, [
      vue.createElementVNode("div", _hoisted_2$1, [
        vue.createElementVNode("span", {
          class: vue.normalizeClass(["debugViewHitIconCircle", _ctx.typeInfo.cssClass]),
          "aria-hidden": "true"
        }, [
          _ctx.typeInfo.iconSvg ? (vue.openBlock(), vue.createElementBlock("img", {
            key: 0,
            src: _ctx.typeInfo.iconSvg,
            alt: ""
          }, null, 8, _hoisted_3$1)) : (vue.openBlock(), vue.createElementBlock("span", {
            key: 1,
            class: vue.normalizeClass(_ctx.typeInfo.icon)
          }, null, 2))
        ], 2),
        vue.createElementVNode("h3", {
          class: "debugViewDetailsTitle",
          title: _ctx.hit.title
        }, vue.toDisplayString(_ctx.hit.title), 9, _hoisted_4$1),
        _ctx.hit.isBot ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "debugViewBotBadge",
          title: _ctx.hit.botName || void 0
        }, vue.toDisplayString(_ctx.translate("DebugView_BotBadge")), 9, _hoisted_5$1)) : vue.createCommentVNode("", true),
        vue.createElementVNode("button", {
          ref: "closeButton",
          type: "button",
          class: "debugViewDetailsClose",
          "aria-label": _ctx.translate("DebugView_CloseDetails"),
          onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("close"))
        }, [..._cache[9] || (_cache[9] = [
          vue.createElementVNode("span", {
            class: "icon-close",
            "aria-hidden": "true"
          }, null, -1)
        ])], 8, _hoisted_6$1)
      ]),
      vue.createElementVNode("div", _hoisted_7$1, [
        vue.createElementVNode("button", {
          type: "button",
          role: "tab",
          id: "debugViewTabParams",
          "aria-selected": _ctx.activeTab === "params" ? "true" : "false",
          "aria-controls": "debugViewTabPanel",
          tabindex: _ctx.activeTab === "params" ? 0 : -1,
          class: vue.normalizeClass(["debugViewDetailsTab", { "debugViewDetailsTab--active": _ctx.activeTab === "params" }]),
          onClick: _cache[1] || (_cache[1] = ($event) => _ctx.activeTab = "params"),
          onKeydown: [
            _cache[2] || (_cache[2] = vue.withKeys(vue.withModifiers(($event) => _ctx.toggleTab(), ["prevent"]), ["left"])),
            _cache[3] || (_cache[3] = vue.withKeys(vue.withModifiers(($event) => _ctx.toggleTab(), ["prevent"]), ["right"]))
          ]
        }, vue.toDisplayString(_ctx.translate("DebugView_ParametersTab")), 43, _hoisted_8$1),
        vue.createElementVNode("button", {
          type: "button",
          role: "tab",
          id: "debugViewTabProcessed",
          "aria-selected": _ctx.activeTab === "processed" ? "true" : "false",
          "aria-controls": "debugViewTabPanel",
          tabindex: _ctx.activeTab === "processed" ? 0 : -1,
          class: vue.normalizeClass(["debugViewDetailsTab", { "debugViewDetailsTab--active": _ctx.activeTab === "processed" }]),
          onClick: _cache[4] || (_cache[4] = ($event) => _ctx.activeTab = "processed"),
          onKeydown: [
            _cache[5] || (_cache[5] = vue.withKeys(vue.withModifiers(($event) => _ctx.toggleTab(), ["prevent"]), ["left"])),
            _cache[6] || (_cache[6] = vue.withKeys(vue.withModifiers(($event) => _ctx.toggleTab(), ["prevent"]), ["right"]))
          ]
        }, vue.toDisplayString(_ctx.translate("DebugView_ProcessedTab")), 43, _hoisted_9$1)
      ]),
      vue.createElementVNode("div", {
        class: "debugViewDetailsBody",
        role: "tabpanel",
        id: "debugViewTabPanel",
        "aria-labelledby": _ctx.activeTab === "params" ? "debugViewTabParams" : "debugViewTabProcessed"
      }, [
        _ctx.activeTab === "params" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
          _ctx.hasTrackingParams ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
            vue.createElementVNode("h4", _hoisted_11$1, vue.toDisplayString(_ctx.translate("DebugView_TrackingParameters")), 1),
            vue.createVNode(_component_DetailRows, {
              entries: _ctx.trackingParamsEntries,
              depth: 0
            }, null, 8, ["entries"])
          ], 64)) : vue.createCommentVNode("", true),
          _ctx.hasDefaultParams ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
            vue.createElementVNode("h4", _hoisted_12, vue.toDisplayString(_ctx.translate("DebugView_DefaultParameters")), 1),
            vue.createVNode(_component_DetailRows, {
              entries: _ctx.defaultParamsEntries,
              depth: 0
            }, null, 8, ["entries"])
          ], 64)) : vue.createCommentVNode("", true),
          _ctx.hasOtherParams ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 2 }, [
            vue.createElementVNode("h4", _hoisted_13, vue.toDisplayString(_ctx.translate("DebugView_OtherParameters")), 1),
            vue.createVNode(_component_DetailRows, {
              entries: _ctx.otherParamsEntries,
              depth: 0
            }, null, 8, ["entries"])
          ], 64)) : vue.createCommentVNode("", true)
        ], 64)) : vue.createCommentVNode("", true),
        _ctx.activeTab === "processed" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
          _ctx.visitLoadState === "bot" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
            vue.createElementVNode("h4", _hoisted_14, vue.toDisplayString(_ctx.translate("DebugView_ProcessedDetails")), 1),
            vue.createElementVNode("p", _hoisted_15, vue.toDisplayString(_ctx.translate("DebugView_ProcessedNotAvailableBot")), 1)
          ], 64)) : _ctx.visitLoadState === "loading" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_16, [
            vue.createVNode(_component_MatomoLoader)
          ])) : _ctx.visitLoadState === "loaded" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 2 }, [
            _ctx.hit.type === "sessionRecording" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
              vue.createElementVNode("h4", _hoisted_17, vue.toDisplayString(_ctx.translate("DebugView_ProcessedDetails")), 1),
              vue.createElementVNode("p", _hoisted_18, vue.toDisplayString(_ctx.translate("DebugView_ProcessedCannotBeShown")), 1)
            ], 64)) : _ctx.matchedActions.length ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
              vue.createElementVNode("h4", _hoisted_19, vue.toDisplayString(_ctx.translate("DebugView_ProcessedDetails")), 1),
              _ctx.hit.type === "media" || _ctx.hit.type === "form" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_20, vue.toDisplayString(_ctx.translate("DebugView_ProcessedAggregatedHint")), 1)) : vue.createCommentVNode("", true),
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.matchedActions, (action, index) => {
                return vue.openBlock(), vue.createBlock(_component_DetailRows, {
                  key: index,
                  entries: action,
                  depth: 0
                }, null, 8, ["entries"]);
              }), 128))
            ], 64)) : vue.createCommentVNode("", true),
            vue.createElementVNode("h4", _hoisted_21, vue.toDisplayString(_ctx.translate("DebugView_ProcessedVisitDetails")), 1),
            vue.createVNode(_component_DetailRows, {
              entries: _ctx.visitEntries,
              depth: 0
            }, null, 8, ["entries"])
          ], 64)) : (vue.openBlock(), vue.createElementBlock("p", _hoisted_22, vue.toDisplayString(_ctx.translate("DebugView_VisitNotAvailable")), 1))
        ], 64)) : vue.createCommentVNode("", true)
      ], 8, _hoisted_10$1)
    ], 40, _hoisted_1$1);
  }
  const HitDetailsPane = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
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
  const MAX_RENDERED_HITS = 500;
  const RAIL_TICK_MS = 15e3;
  function compareDecimalIds(a, b) {
    const left = /^\d+$/.test(a) ? a.replace(/^0+(?=\d)/, "") : "0";
    const right = /^\d+$/.test(b) ? b.replace(/^0+(?=\d)/, "") : "0";
    if (left.length !== right.length) {
      return left.length - right.length;
    }
    if (left === right) {
      return 0;
    }
    return left < right ? -1 : 1;
  }
  const _sfc_main = vue.defineComponent({
    props: {
      idSite: {
        type: Number,
        required: true
      },
      refreshInterval: {
        type: Number,
        default: 5
      },
      lastMinutes: {
        type: Number,
        default: 30
      }
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      Alert: CoreHome.Alert,
      ContentBlock: CoreHome.ContentBlock,
      HitDetailsPane,
      HitsStream,
      MinutesRail
    },
    data() {
      return {
        hits: [],
        buffer: [],
        paused: false,
        selectedHit: null,
        selectedMinute: null,
        isInitialLoading: true,
        pollingError: null,
        timezone: "",
        serverTimeOffset: 0,
        nowTick: Math.floor(Date.now() / 1e3),
        // incremental polling cursor; a decimal string, never a Number (see
        // compareDecimalIds)
        lastId: "0",
        seenHits: /* @__PURE__ */ new Map(),
        // minute-rail counts, tracked separately from `hits` so the rail stays
        // accurate even when the rendered stream is capped at MAX_RENDERED_HITS
        minuteCounts: /* @__PURE__ */ new Map(),
        streamGeneration: 0,
        refreshController: null,
        railTimer: null,
        isUnmounted: false
      };
    },
    computed: {
      liveStatusText() {
        return this.paused ? CoreHome.translate("DebugView_StreamPaused") : CoreHome.translate("DebugView_StreamLive");
      },
      lastMinutesCount() {
        const minutes = Number(this.lastMinutes);
        return Number.isFinite(minutes) && minutes > 0 ? Math.floor(minutes) : 30;
      },
      // "now" in server time: browser now corrected by the clock offset tracked
      // from every API response
      serverNow() {
        return this.nowTick + this.serverTimeOffset;
      },
      minuteBuckets() {
        const formatter = this.createMinuteFormatter();
        const counts = this.minuteCounts;
        const nowMinuteStart = Math.floor(this.serverNow / 60) * 60;
        const buckets = [];
        for (let i = 0; i < this.lastMinutesCount; i += 1) {
          const minuteStart = nowMinuteStart - i * 60;
          const count = counts.get(minuteStart) || 0;
          buckets.push({
            minuteStart,
            count,
            label: formatter.format(new Date(minuteStart * 1e3)),
            showLabel: i % 5 === 0 || count > 0
          });
        }
        return buckets;
      }
    },
    mounted() {
      this.initRefreshController();
      if (this.refreshController) {
        this.refreshController.start();
      }
      this.railTimer = window.setInterval(() => {
        this.nowTick = Math.floor(Date.now() / 1e3);
        this.pruneOldHits();
      }, RAIL_TICK_MS);
    },
    beforeUnmount() {
      this.isUnmounted = true;
      if (this.railTimer) {
        window.clearInterval(this.railTimer);
        this.railTimer = null;
      }
      if (this.refreshController) {
        this.refreshController.destroy();
        this.refreshController = null;
      }
    },
    methods: {
      createMinuteFormatter() {
        const options = { hour: "numeric", minute: "2-digit" };
        try {
          return new Intl.DateTimeFormat(
            void 0,
            __spreadProps(__spreadValues({}, options), { timeZone: this.timezone || void 0 })
          );
        } catch (e) {
          return new Intl.DateTimeFormat(void 0, options);
        }
      },
      initRefreshController() {
        this.refreshController = new AutoRefreshController({
          getBaseInterval: () => {
            const seconds = Number(this.refreshInterval);
            return Number.isFinite(seconds) && seconds > 0 ? seconds * 1e3 : 5e3;
          },
          shouldRun: () => {
            if (this.isUnmounted) {
              return false;
            }
            const root = this.$refs.root;
            return Boolean(root && root.isConnected);
          },
          request: () => {
            const generation = this.streamGeneration;
            return this.fetchHits().then(
              (response) => ({ generation, response }),
              (error) => {
                const tagged = new Error("DebugView poll failed");
                tagged.generation = generation;
                tagged.error = error;
                throw tagged;
              }
            );
          },
          handleResponse: (tagged) => this.processResponse(tagged),
          onError: (error) => this.handlePollingError(error)
        });
      },
      fetchHits() {
        return CoreHome.AjaxHelper.fetch(
          {
            method: "DebugView.getRecentHits",
            idSite: this.idSite,
            lastMinutes: this.lastMinutesCount,
            // ids are monotonic, so a strict id cursor can never lose late or
            // same-second hits — no overlap window needed
            minId: this.lastId
          },
          { createErrorNotification: false }
        );
      },
      processResponse(tagged) {
        if (tagged.generation !== this.streamGeneration) {
          return { updated: false };
        }
        const { response } = tagged;
        this.isInitialLoading = false;
        this.pollingError = null;
        this.nowTick = Math.floor(Date.now() / 1e3);
        if (response && Number.isFinite(response.serverTime)) {
          this.serverTimeOffset = response.serverTime - this.nowTick;
        }
        if (response && response.timezone) {
          this.timezone = response.timezone;
        }
        const incoming = (response && response.hits || []).filter(
          (hit) => !!hit && !!hit.idRawRequest && !this.seenHits.has(hit.idRawRequest)
        );
        incoming.forEach((hit) => {
          this.seenHits.set(hit.idRawRequest, hit.timestamp);
          if (compareDecimalIds(hit.idRawRequest, this.lastId) > 0) {
            this.lastId = hit.idRawRequest;
          }
        });
        if (incoming.length) {
          if (this.paused) {
            this.buffer = this.buffer.concat(incoming);
          } else {
            this.hits = this.sortNewestFirst(this.hits.concat(incoming));
            this.addToRailCounts(incoming);
          }
        }
        this.pruneOldHits();
        return { updated: incoming.length > 0 };
      },
      handlePollingError(taggedError) {
        let inner = taggedError;
        if (taggedError && typeof taggedError === "object" && "generation" in taggedError) {
          const tagged = taggedError;
          if (tagged.generation !== this.streamGeneration) {
            return;
          }
          inner = tagged.error;
        }
        this.isInitialLoading = false;
        let message = "";
        if (inner && typeof inner === "object" && "message" in inner) {
          const errorMessage = inner.message;
          if (typeof errorMessage === "string") {
            message = errorMessage;
          }
        }
        this.pollingError = message;
      },
      addToRailCounts(hits) {
        hits.forEach((hit) => {
          const minuteStart = Math.floor(hit.timestamp / 60) * 60;
          this.minuteCounts.set(minuteStart, (this.minuteCounts.get(minuteStart) || 0) + 1);
        });
      },
      sortNewestFirst(hits) {
        return hits.slice().sort((lhs, rhs) => {
          if (rhs.timestamp !== lhs.timestamp) {
            return rhs.timestamp - lhs.timestamp;
          }
          return compareDecimalIds(rhs.idRawRequest, lhs.idRawRequest);
        });
      },
      pruneOldHits() {
        const windowStart = this.serverNow - this.lastMinutesCount * 60;
        this.hits = this.hits.filter((hit) => hit.timestamp >= windowStart).slice(0, MAX_RENDERED_HITS);
        if (this.buffer.length) {
          this.buffer = this.buffer.filter((hit) => hit.timestamp >= windowStart);
        }
        this.seenHits.forEach((timestamp, id) => {
          if (timestamp < windowStart) {
            this.seenHits.delete(id);
          }
        });
        this.minuteCounts.forEach((count, minuteStart) => {
          if (minuteStart + 60 <= windowStart) {
            this.minuteCounts.delete(minuteStart);
          }
        });
      },
      togglePaused() {
        if (this.paused) {
          this.resumeStream();
        } else {
          this.paused = true;
        }
      },
      resumeStream() {
        if (this.buffer.length) {
          this.hits = this.sortNewestFirst(this.hits.concat(this.buffer));
          this.addToRailCounts(this.buffer);
          this.buffer = [];
          this.pruneOldHits();
        }
        this.paused = false;
      },
      onPageEscape() {
        if (this.selectedHit) {
          this.onCloseDetails();
        }
      },
      onSelectMinute(minuteStart) {
        this.selectedMinute = minuteStart;
        const stream = this.$refs.stream;
        if (stream) {
          stream.scrollToMinute(minuteStart);
        }
      },
      onOpenHit(hit) {
        this.selectedHit = hit;
      },
      onCloseDetails() {
        const closedHit = this.selectedHit;
        this.selectedHit = null;
        if (!closedHit) {
          return;
        }
        this.$nextTick(() => {
          const stream = this.$refs.stream;
          if (stream) {
            stream.focusHit(closedHit.idRawRequest);
          }
        });
      }
    }
  });
  const _hoisted_1 = { class: "debugViewIntro" };
  const _hoisted_2 = { class: "debugViewTopBar" };
  const _hoisted_3 = { class: "debugViewLiveStatus" };
  const _hoisted_4 = { class: "debugViewLiveText" };
  const _hoisted_5 = ["aria-label"];
  const _hoisted_6 = {
    key: 0,
    class: "debugViewErrorDetails"
  };
  const _hoisted_7 = {
    key: 1,
    class: "debugViewLayout"
  };
  const _hoisted_8 = { class: "debugViewStreamColumn" };
  const _hoisted_9 = {
    key: 0,
    class: "debugViewEmptyState"
  };
  const _hoisted_10 = { class: "debugViewEmptyHeadline" };
  const _hoisted_11 = { class: "debugViewEmptyHint" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Alert = vue.resolveComponent("Alert");
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_MinutesRail = vue.resolveComponent("MinutesRail");
    const _component_HitsStream = vue.resolveComponent("HitsStream");
    const _component_HitDetailsPane = vue.resolveComponent("HitDetailsPane");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "debugViewPage",
      ref: "root",
      onKeydown: _cache[1] || (_cache[1] = vue.withKeys((...args) => _ctx.onPageEscape && _ctx.onPageEscape(...args), ["esc"]))
    }, [
      vue.createVNode(_component_ContentBlock, {
        "content-title": _ctx.translate("DebugView_DebugView")
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("p", _hoisted_1, vue.toDisplayString(_ctx.translate("DebugView_PageDescription")), 1),
          vue.createElementVNode("div", _hoisted_2, [
            vue.createElementVNode("div", _hoisted_3, [
              vue.createElementVNode("span", {
                class: vue.normalizeClass(["debugViewLiveDot", { "debugViewLiveDot--paused": _ctx.paused }]),
                "aria-hidden": "true"
              }, null, 2),
              vue.createElementVNode("span", _hoisted_4, vue.toDisplayString(_ctx.liveStatusText), 1),
              vue.createElementVNode("button", {
                type: "button",
                class: "debugViewPauseButton",
                "aria-label": _ctx.paused ? _ctx.translate("DebugView_Resume") : _ctx.translate("DebugView_Pause"),
                onClick: _cache[0] || (_cache[0] = ($event) => _ctx.togglePaused())
              }, [
                vue.createElementVNode("span", {
                  class: vue.normalizeClass(_ctx.paused ? "icon-play" : "icon-pause"),
                  "aria-hidden": "true"
                }, null, 2)
              ], 8, _hoisted_5)
            ])
          ]),
          _ctx.pollingError !== null ? (vue.openBlock(), vue.createBlock(_component_Alert, {
            key: 0,
            severity: "warning"
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("DebugView_PollingErrorTitle")), 1),
              _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
              vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("DebugView_PollingErrorMessage")) + " ", 1),
              _ctx.pollingError ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_6, vue.toDisplayString(_ctx.pollingError), 1)) : vue.createCommentVNode("", true)
            ]),
            _: 1
          })) : vue.createCommentVNode("", true),
          vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isInitialLoading }, null, 8, ["loading"]),
          !_ctx.isInitialLoading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7, [
            vue.createVNode(_component_MinutesRail, {
              buckets: _ctx.minuteBuckets,
              "selected-minute": _ctx.selectedMinute,
              "pending-count": _ctx.buffer.length,
              paused: _ctx.paused,
              onSelectMinute: _ctx.onSelectMinute
            }, null, 8, ["buckets", "selected-minute", "pending-count", "paused", "onSelectMinute"]),
            vue.createElementVNode("div", _hoisted_8, [
              !_ctx.hits.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9, [
                _cache[3] || (_cache[3] = vue.createElementVNode("span", {
                  class: "icon-search debugViewEmptyIcon",
                  "aria-hidden": "true"
                }, null, -1)),
                vue.createElementVNode("p", _hoisted_10, [
                  vue.createElementVNode("span", {
                    class: vue.normalizeClass(["debugViewLiveDot", { "debugViewLiveDot--paused": _ctx.paused }]),
                    "aria-hidden": "true"
                  }, null, 2),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("DebugView_WaitingForRequests")), 1)
                ]),
                vue.createElementVNode("p", _hoisted_11, vue.toDisplayString(_ctx.translate("DebugView_WaitingForRequestsHint")), 1)
              ])) : (vue.openBlock(), vue.createBlock(_component_HitsStream, {
                key: 1,
                ref: "stream",
                hits: _ctx.hits,
                "selected-hit-id": _ctx.selectedHit ? _ctx.selectedHit.idRawRequest : null,
                onOpenHit: _ctx.onOpenHit
              }, null, 8, ["hits", "selected-hit-id", "onOpenHit"]))
            ]),
            _ctx.selectedHit ? (vue.openBlock(), vue.createBlock(_component_HitDetailsPane, {
              key: 0,
              hit: _ctx.selectedHit,
              "id-site": _ctx.idSite,
              onClose: _ctx.onCloseDetails
            }, null, 8, ["hit", "id-site", "onClose"])) : vue.createCommentVNode("", true)
          ])) : vue.createCommentVNode("", true)
        ]),
        _: 1
      }, 8, ["content-title"])
    ], 544);
  }
  const DebugViewPage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.DebugViewPage = DebugViewPage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
