(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.SegmentEditor = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
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
  class SegmentGeneratorStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        isLoading: false,
        segments: []
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
    }
    loadSegments(siteId, visitSegmentsOnly) {
      this.privateState.isLoading = true;
      let idSites = void 0;
      let idSite = void 0;
      if (siteId === "all" || !siteId) {
        idSites = "all";
        idSite = "all";
      } else if (siteId) {
        idSites = siteId;
        idSite = siteId;
      }
      return CoreHome.AjaxHelper.fetch({
        method: "API.getSegmentsMetadata",
        filter_limit: "-1",
        _hideImplementationData: 0,
        idSites,
        idSite
      }, {
        // Stay out of globalAjaxQueue so a navigation-triggered
        // globalAjaxQueue.abort() (e.g. when the panel close re-renders
        // hashchange listeners) cannot kill the metadata fetch.
        // AjaxHelper silently swallows aborts, which would leave the
        // promise pending forever and the segment editor form rendered
        // without dimension labels or condition rows.
        abortable: false
      }).then((response) => {
        this.privateState.isLoading = false;
        if (response) {
          if (visitSegmentsOnly) {
            this.privateState.segments = response.filter(
              (s) => s.sqlSegment && s.sqlSegment.match(/log_visit\./)
            );
          } else {
            this.privateState.segments = response;
          }
        }
        return this.state.value.segments;
      }).finally(() => {
        this.privateState.isLoading = false;
      });
    }
  }
  const SegmentGeneratorStore$1 = new SegmentGeneratorStore();
  const _sfc_main$7 = vue.defineComponent({
    props: {
      value: null
    },
    created() {
      this.onKeydownOrConditionValue = CoreHome.debounce(this.onKeydownOrConditionValue, 50);
    },
    emits: ["update"],
    methods: {
      onKeydownOrConditionValue(event) {
        this.$emit("update", event.target.value);
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
  const _hoisted_1$7 = ["placeholder", "title", "value"];
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("input", {
      placeholder: _ctx.translate("General_Value"),
      type: "text",
      class: "autocomplete",
      title: _ctx.translate("General_Value"),
      autocomplete: "off",
      value: _ctx.value,
      onKeydown: _cache[0] || (_cache[0] = ($event) => _ctx.onKeydownOrConditionValue($event)),
      onChange: _cache[1] || (_cache[1] = ($event) => _ctx.onKeydownOrConditionValue($event))
    }, null, 40, _hoisted_1$7);
  }
  const ValueInput = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  function initialMatches() {
    return {
      metric: [
        {
          key: "==",
          value: CoreHome.translate("General_OperationEquals")
        },
        {
          key: "!=",
          value: CoreHome.translate("General_OperationNotEquals")
        },
        {
          key: "<=",
          value: CoreHome.translate("General_OperationAtMost")
        },
        {
          key: ">=",
          value: CoreHome.translate("General_OperationAtLeast")
        },
        {
          key: "<",
          value: CoreHome.translate("General_OperationLessThan")
        },
        {
          key: ">",
          value: CoreHome.translate("General_OperationGreaterThan")
        }
      ],
      dimension: [
        {
          key: "==",
          value: CoreHome.translate("General_OperationIs")
        },
        {
          key: "!=",
          value: CoreHome.translate("General_OperationIsNot")
        },
        {
          key: "=@",
          value: CoreHome.translate("General_OperationContains")
        },
        {
          key: "!@",
          value: CoreHome.translate("General_OperationDoesNotContain")
        },
        {
          key: "=^",
          value: CoreHome.translate("General_OperationStartsWith")
        },
        {
          key: "=$",
          value: CoreHome.translate("General_OperationEndsWith")
        }
      ]
    };
  }
  function generateUniqueId() {
    let id = "";
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    for (let i = 1; i <= 10; i += 1) {
      id += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return id;
  }
  function findAndExplodeByMatch(metric) {
    const matches = ["==", "!=", "<=", ">=", "=@", "!@", "<", ">", "=^", "=$"];
    const newMetric = {};
    let minPos = metric.length;
    let match;
    let index;
    let singleChar = false;
    for (let key = 0; key < matches.length; key += 1) {
      match = matches[key];
      index = metric.indexOf(match);
      if (index !== -1) {
        if (index < minPos) {
          minPos = index;
          if (match.length === 1) {
            singleChar = true;
          }
        }
      }
    }
    if (minPos < metric.length) {
      if (singleChar === true) {
        newMetric.segment = metric.slice(0, minPos);
        newMetric.matches = metric.slice(minPos, minPos + 1);
        newMetric.value = decodeURIComponent(metric.slice(minPos + 1));
      } else {
        newMetric.segment = metric.slice(0, minPos);
        newMetric.matches = metric.slice(minPos, minPos + 2);
        newMetric.value = decodeURIComponent(metric.slice(minPos + 2));
      }
      if (newMetric.value === '""') {
        newMetric.value = "";
      }
    }
    try {
      newMetric.value = decodeURIComponent(newMetric.value);
    } catch (e) {
    }
    return newMetric;
  }
  function stripTags(text) {
    return text ? `${text}`.replace(/(<([^>]+)>)/ig, "") : text;
  }
  const { $ } = window;
  const _sfc_main$6 = vue.defineComponent({
    props: {
      addInitialCondition: Boolean,
      visitSegmentsOnly: Boolean,
      idsite: {
        type: [String, Number],
        default: () => CoreHome.Matomo.idSite
      },
      modelValue: {
        type: String,
        default: ""
      }
    },
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator,
      Field: CorePluginsAdmin.Field,
      MatomoLoader: CoreHome.MatomoLoader,
      ValueInput
    },
    data() {
      return {
        conditions: [],
        queriedSegments: [],
        matches: initialMatches(),
        conditionValuesLoading: {},
        segmentDefinition: ""
      };
    },
    emits: ["update:modelValue"],
    watch: {
      modelValue(newVal) {
        if ((newVal || "") !== (this.segmentDefinition || "")) {
          this.setSegmentString(newVal);
        }
      },
      conditions: {
        deep: true,
        handler() {
          this.computeSegmentDefinition();
        }
      },
      segmentDefinition(newVal) {
        if ((newVal || "") !== (this.modelValue || "")) {
          this.$emit("update:modelValue", newVal);
        }
      },
      idsite(newVal) {
        this.reloadSegments(newVal, this.visitSegmentsOnly);
      }
    },
    created() {
      this.matches[""] = this.matches.dimension;
      this.setSegmentString(this.modelValue);
      this.segmentDefinition = this.modelValue;
      this.reloadSegments(this.idsite, this.visitSegmentsOnly);
    },
    methods: {
      reloadSegments(idsite, visitSegmentsOnly) {
        SegmentGeneratorStore$1.loadSegments(idsite, visitSegmentsOnly).then((segments) => {
          this.queriedSegments = segments.map((s) => __spreadProps(__spreadValues({}, s), {
            category: s.category || "Others"
          }));
          if (this.addInitialCondition && this.conditions.length === 0) {
            this.addNewAndCondition();
          }
        });
      },
      addAndCondition(condition) {
        this.conditions.push(condition);
      },
      addNewOrCondition(condition) {
        if (!this.firstSegment) {
          return;
        }
        const orCondition = {
          segment: this.firstSegment,
          matches: this.firstMatch,
          value: ""
        };
        this.addOrCondition(condition, orCondition);
      },
      addOrCondition(condition, orCondition) {
        this.conditionValuesLoading[orCondition.id] = false;
        orCondition.id = generateUniqueId();
        condition.orConditions.push(orCondition);
        vue.nextTick(() => {
          this.updateAutocomplete(orCondition);
        });
      },
      onSegmentSelection(event, orCondition) {
        orCondition.segment = event;
        this.updateAutocomplete(orCondition);
        this.computeSegmentDefinition();
        this.focusValueInput(orCondition);
      },
      updateAutocomplete(orCondition) {
        this.conditionValuesLoading[orCondition.id] = true;
        $(`.orCondId${orCondition.id} .metricValueBlock input`, this.$refs.root).autocomplete({
          source: [],
          minLength: 0
        });
        const abortController = new AbortController();
        let resolved = false;
        CoreHome.AjaxHelper.fetch(
          {
            module: "API",
            format: "json",
            method: "API.getSuggestedValuesForSegment",
            segmentName: orCondition.segment,
            segment: null,
            idSite: this.idsite
          },
          {
            createErrorNotification: false
            // don't show errors returned from the API in UI
          }
        ).then((response) => {
          this.conditionValuesLoading[orCondition.id] = false;
          resolved = true;
          let autocompleteValues = response;
          if (Array.isArray(autocompleteValues)) {
            autocompleteValues = autocompleteValues.map((v) => `${v}`);
          }
          const inputElement = $(`.orCondId${orCondition.id} .metricValueBlock input`).autocomplete({
            source: autocompleteValues,
            minLength: 0,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            select: (event, ui) => {
              event.preventDefault();
              orCondition.value = ui.item.value;
              this.computeSegmentDefinition();
              this.$forceUpdate();
            }
          }).off("click").click(() => {
            $(inputElement).autocomplete("search", orCondition.value);
          });
        }).catch(() => {
          resolved = true;
          this.conditionValuesLoading[orCondition.id] = false;
          $(`.orCondId${orCondition.id} .metricValueBlock input`).autocomplete({
            source: [],
            minLength: 0
          }).autocomplete("search", orCondition.value);
        });
        setTimeout(() => {
          if (!resolved) {
            abortController.abort();
          }
        }, 2e4);
      },
      removeOrCondition(condition, orCondition) {
        const index = condition.orConditions.indexOf(orCondition);
        if (index > -1) {
          condition.orConditions.splice(index, 1);
        }
        if (condition.orConditions.length === 0) {
          const andCondIndex = this.conditions.indexOf(condition);
          if (index > -1) {
            this.conditions.splice(andCondIndex, 1);
          }
        }
      },
      setSegmentString(segmentStr) {
        this.conditions = [];
        if (!segmentStr) {
          return;
        }
        const blocks = segmentStr.split(";").map((b) => b.split(","));
        this.conditions = blocks.map((block) => {
          const condition = { orConditions: [] };
          block.forEach((innerBlock) => {
            const orCondition = findAndExplodeByMatch(innerBlock);
            this.addOrCondition(condition, orCondition);
          });
          return condition;
        });
      },
      addNewAndCondition() {
        const condition = { orConditions: [] };
        if (!this.firstSegment) {
          return;
        }
        this.addAndCondition(condition);
        this.addNewOrCondition(condition);
      },
      // NOTE: can't use a computed property since we need to recompute on changes inside the
      //       structure. don't have to if we don't do in-place changes, but with nested structures,
      //       that's complicated.
      computeSegmentDefinition() {
        let segmentStr = "";
        this.conditions.forEach((condition) => {
          if (!condition.orConditions.length) {
            return;
          }
          let subSegmentStr = "";
          condition.orConditions.forEach((orCondition) => {
            if (!orCondition.value && !orCondition.segment && !orCondition.matches) {
              return;
            }
            if (subSegmentStr !== "") {
              subSegmentStr += ",";
            }
            const value = encodeURIComponent(encodeURIComponent(orCondition.value));
            subSegmentStr += `${orCondition.segment}${orCondition.matches}${value}`;
          });
          if (segmentStr !== "") {
            segmentStr += ";";
          }
          segmentStr += subSegmentStr;
        });
        this.segmentDefinition = segmentStr;
      },
      focusValueInput(orCondition) {
        const $input = $(`.orCondId${orCondition.id} .metricValueBlock input`);
        $input.focus();
        if ($input.val()) {
          $input.select();
        }
      }
    },
    computed: {
      firstSegment() {
        var _a;
        return ((_a = this.queriedSegments[0]) == null ? void 0 : _a.segment) || null;
      },
      firstMatch() {
        const segment = this.queriedSegments[0];
        if (!segment) {
          return null;
        }
        if (segment.type && this.matches[segment.type]) {
          return this.matches[segment.type][0].key;
        }
        return this.matches[""][0].key;
      },
      segments() {
        const result = {};
        this.queriedSegments.forEach((s) => {
          result[s.segment] = s;
        });
        return result;
      },
      segmentList() {
        return this.queriedSegments.map((s) => ({
          group: s.category,
          key: s.segment,
          value: s.name,
          tooltip: s.acceptedValues ? stripTags(s.acceptedValues) : void 0
        }));
      },
      addNewOrConditionLinkText() {
        return `+ ${CoreHome.translate(
          "SegmentEditor_AddANDorORCondition",
          `<span>${CoreHome.translate("SegmentEditor_OperatorOR")}</span>`
        )}`;
      },
      andConditionLabel() {
        return this.conditions.length ? CoreHome.translate("SegmentEditor_OperatorAND") : "";
      },
      addNewAndConditionLinkText() {
        return `+ ${CoreHome.translate("SegmentEditor_AddANDorORCondition", `<span>${this.andConditionLabel}</span>`)}`;
      },
      isLoading() {
        return SegmentGeneratorStore$1.state.value.isLoading;
      }
    }
  });
  const _hoisted_1$6 = {
    class: "segment-generator",
    ref: "root"
  };
  const _hoisted_2$3 = { class: "segment-rows" };
  const _hoisted_3$2 = { class: "segment-row" };
  const _hoisted_4$1 = ["onClick"];
  const _hoisted_5$1 = { class: "segment-loading" };
  const _hoisted_6$1 = { class: "segment-row-inputs valign-wrapper" };
  const _hoisted_7$1 = { class: "segment-input metricListBlock valign-wrapper" };
  const _hoisted_8$1 = { style: { "width": "100%" } };
  const _hoisted_9$1 = { class: "segment-input metricMatchBlock valign-wrapper" };
  const _hoisted_10$1 = { style: { "display": "inline-block" } };
  const _hoisted_11$1 = { class: "segment-input metricValueBlock valign-wrapper" };
  const _hoisted_12$1 = {
    class: "form-group row",
    style: { "width": "100%" }
  };
  const _hoisted_13$1 = { class: "input-field col s12" };
  const _hoisted_14$1 = { class: "segment-or" };
  const _hoisted_15$1 = ["onClick"];
  const _hoisted_16$1 = ["innerHTML"];
  const _hoisted_17 = { class: "segment-and" };
  const _hoisted_18 = ["innerHTML"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_Field = vue.resolveComponent("Field");
    const _component_ValueInput = vue.resolveComponent("ValueInput");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, [
      vue.createVNode(_component_ActivityIndicator, { loading: _ctx.isLoading }, null, 8, ["loading"]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.conditions, (condition, conditionIndex) => {
        return vue.openBlock(), vue.createElementBlock("div", {
          class: vue.normalizeClass(`segmentRow${conditionIndex}`),
          key: conditionIndex
        }, [
          vue.createElementVNode("div", _hoisted_2$3, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(condition.orConditions, (orCondition, orConditionIndex) => {
              var _a, _b;
              return vue.openBlock(), vue.createElementBlock("div", {
                class: vue.normalizeClass(`orCondId${orCondition.id}`),
                key: orConditionIndex
              }, [
                vue.createElementVNode("div", _hoisted_3$2, [
                  vue.createElementVNode("a", {
                    class: "segment-close",
                    onClick: ($event) => _ctx.removeOrCondition(condition, orCondition)
                  }, null, 8, _hoisted_4$1),
                  vue.createElementVNode("div", _hoisted_5$1, [
                    vue.withDirectives(vue.createVNode(_component_MatomoLoader, null, null, 512), [
                      [vue.vShow, _ctx.conditionValuesLoading[orCondition.id || ""]]
                    ])
                  ]),
                  vue.createElementVNode("div", _hoisted_6$1, [
                    vue.createElementVNode("div", _hoisted_7$1, [
                      vue.createElementVNode("div", _hoisted_8$1, [
                        vue.createVNode(_component_Field, {
                          uicontrol: "expandable-select",
                          name: "segments",
                          "model-value": orCondition.segment,
                          "onUpdate:modelValue": ($event) => _ctx.onSegmentSelection($event, orCondition),
                          title: (_a = _ctx.segments[orCondition.segment]) == null ? void 0 : _a.name,
                          "full-width": true,
                          options: _ctx.segmentList
                        }, null, 8, ["model-value", "onUpdate:modelValue", "title", "options"])
                      ])
                    ]),
                    vue.createElementVNode("div", _hoisted_9$1, [
                      vue.createElementVNode("div", _hoisted_10$1, [
                        vue.createVNode(_component_Field, {
                          uicontrol: "select",
                          name: "matchType",
                          "model-value": orCondition.matches,
                          "onUpdate:modelValue": ($event) => {
                            orCondition.matches = $event;
                            _ctx.computeSegmentDefinition();
                          },
                          "full-width": true,
                          options: _ctx.matches[(_b = _ctx.segments[orCondition.segment]) == null ? void 0 : _b.type]
                        }, null, 8, ["model-value", "onUpdate:modelValue", "options"])
                      ])
                    ]),
                    vue.createElementVNode("div", _hoisted_11$1, [
                      vue.createElementVNode("div", _hoisted_12$1, [
                        vue.createElementVNode("div", _hoisted_13$1, [
                          _cache[1] || (_cache[1] = vue.createElementVNode("span", {
                            role: "status",
                            "aria-live": "polite",
                            class: "ui-helper-hidden-accessible"
                          }, null, -1)),
                          vue.createVNode(_component_ValueInput, {
                            value: orCondition.value,
                            onUpdate: ($event) => {
                              orCondition.value = $event;
                              _ctx.computeSegmentDefinition();
                            }
                          }, null, 8, ["value", "onUpdate"])
                        ])
                      ])
                    ]),
                    _cache[2] || (_cache[2] = vue.createElementVNode("div", { class: "clear" }, null, -1))
                  ])
                ]),
                vue.createElementVNode("div", _hoisted_14$1, vue.toDisplayString(_ctx.translate("SegmentEditor_OperatorOR")), 1)
              ], 2);
            }), 128)),
            vue.createElementVNode("div", {
              class: "segment-add-or",
              onClick: ($event) => _ctx.addNewOrCondition(condition)
            }, [
              vue.createElementVNode("div", null, [
                vue.createElementVNode("a", {
                  innerHTML: _ctx.$sanitize(_ctx.addNewOrConditionLinkText)
                }, null, 8, _hoisted_16$1)
              ])
            ], 8, _hoisted_15$1)
          ]),
          vue.createElementVNode("div", _hoisted_17, vue.toDisplayString(_ctx.translate("SegmentEditor_OperatorAND")), 1)
        ], 2);
      }), 128)),
      vue.createElementVNode("div", {
        class: "segment-add-row initial",
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.addNewAndCondition())
      }, [
        vue.createElementVNode("div", null, [
          vue.createElementVNode("a", {
            innerHTML: _ctx.$sanitize(_ctx.addNewAndConditionLinkText)
          }, null, 8, _hoisted_18)
        ])
      ])
    ], 512);
  }
  const SegmentGenerator = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function getStarredByTitlePart(segment, userContext, translations) {
    const login = segment.starred_by || "";
    if (login === userContext.login) {
      return ` (${translations.General_StarredByYou})`;
    }
    return ` (${translations.General_StarredBy} ${login})`;
  }
  function getCanUserEditSegment(segment, segmentAccess, userContext) {
    if (!segment || userContext.isAnonymous) {
      return false;
    }
    if (segmentAccess !== "write") {
      return false;
    }
    if (userContext.hasSuperUserAccess) {
      return true;
    }
    return segment.login === userContext.login;
  }
  function getDeleteSegmentTitle(segment, canEdit, translations) {
    if (segment.enable_only_idsite) {
      return canEdit ? translations.General_CanDeleteSiteSegment : translations.General_CanNotDeleteSiteSegment;
    }
    return canEdit ? translations.General_CanDeleteGlobalSegment : translations.General_CanNotDeleteGlobalSegment;
  }
  function getEditSegmentTitle(segment, canEdit, translations) {
    if (segment.enable_only_idsite) {
      return canEdit ? translations.General_CanEditSiteSegment : translations.General_CanNotEditSiteSegment;
    }
    return canEdit ? translations.General_CanEditGlobalSegment : translations.General_CanNotEditGlobalSegment;
  }
  function getStarSegmentTitle(segment, canEdit, translations, userContext) {
    if (userContext.isAnonymous) {
      return "";
    }
    if (segment.enable_only_idsite) {
      if (canEdit) {
        if (segment.starred) {
          return `${translations.General_CanUnstarSiteSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
        }
        return translations.General_CanStarSiteSegment;
      }
      if (segment.starred) {
        return translations.General_CanNotUnstarSiteSegment;
      }
      return translations.General_CanNotStarSiteSegment;
    }
    if (canEdit) {
      if (segment.starred) {
        return `${translations.General_CanUnstarGlobalSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
      }
      return translations.General_CanStarGlobalSegment;
    }
    if (segment.starred) {
      return translations.General_CanNotUnstarGlobalSegment;
    }
    return translations.General_CanNotStarGlobalSegment;
  }
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class SegmentSelectorStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        availableSegments: [],
        currentSegment: "",
        isUserAnonymous: false,
        isInitialized: false,
        loginUrl: "",
        manageSegmentsUrl: "",
        panelExpanded: false,
        renderVersion: 0,
        segmentAccess: "read",
        translations: {},
        userContext: {
          isAnonymous: false,
          hasSuperUserAccess: false,
          login: ""
        }
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "starChangeCallbacks", []);
    }
    normalizeAvailableSegments(segments) {
      return segments.map(
        (segment) => __spreadProps(__spreadValues({}, segment), {
          starred: this.normalizeStarredState(segment.starred)
        })
      );
    }
    init(config) {
      this.privateState.availableSegments = this.normalizeAvailableSegments(config.availableSegments);
      this.privateState.currentSegment = config.currentSegment || "";
      this.privateState.isUserAnonymous = config.isUserAnonymous;
      this.privateState.isInitialized = true;
      this.privateState.loginUrl = config.loginUrl;
      this.privateState.manageSegmentsUrl = config.manageSegmentsUrl;
      this.privateState.segmentAccess = config.segmentAccess;
      this.privateState.translations = config.translations;
      this.privateState.userContext = config.userContext;
      this.privateState.renderVersion += 1;
    }
    onStarChange(callback) {
      this.starChangeCallbacks.push(callback);
      let isUnsubscribed = false;
      return () => {
        if (isUnsubscribed) {
          return;
        }
        isUnsubscribed = true;
        const index = this.starChangeCallbacks.indexOf(callback);
        if (index !== -1) {
          this.starChangeCallbacks.splice(index, 1);
        }
      };
    }
    notifyChange() {
      this.privateState.renderVersion += 1;
    }
    setAvailableSegments(segments) {
      this.privateState.availableSegments = this.normalizeAvailableSegments(segments);
      this.notifyChange();
    }
    setCurrentSegment(segment) {
      this.privateState.currentSegment = segment || "";
      this.notifyChange();
    }
    getCurrentSegment() {
      return this.privateState.currentSegment;
    }
    setPanelExpanded(isExpanded) {
      this.privateState.panelExpanded = isExpanded;
      this.notifyChange();
    }
    getPanelExpanded() {
      return this.privateState.panelExpanded;
    }
    getSegmentAccess() {
      return this.privateState.segmentAccess;
    }
    getTranslations() {
      return this.privateState.translations;
    }
    getUserContext() {
      return this.privateState.userContext;
    }
    normalizeStarredState(starred) {
      if (typeof starred === "boolean") {
        return starred;
      }
      if (typeof starred === "number") {
        return starred !== 0;
      }
      if (typeof starred === "string") {
        return starred === "1" || starred.toLowerCase() === "true";
      }
      return false;
    }
    getSegmentFromId(idSegment) {
      if (typeof idSegment === "undefined" || idSegment === null || idSegment === "") {
        return null;
      }
      return this.privateState.availableSegments.find((segment) => `${segment.idsegment}` === `${idSegment}`) || null;
    }
    decodeDefinition(definition) {
      const candidates = [definition];
      try {
        candidates.push(window.piwikHelper.htmlDecode(definition));
      } catch (e) {
      }
      try {
        candidates.push(window.piwikHelper.htmlDecode(decodeURIComponent(definition)));
      } catch (e) {
      }
      return candidates.filter((candidate, index, values) => typeof candidate !== "undefined" && values.indexOf(candidate) === index);
    }
    getSegmentByDefinition(definition) {
      const candidates = this.decodeDefinition(definition);
      return this.privateState.availableSegments.find((segment) => candidates.indexOf(segment.definition) !== -1) || null;
    }
    getPlainSegmentName(segment) {
      return window.piwikHelper.htmlDecode(segment.name);
    }
    getSegmentTooltipText(segment) {
      let segmentName = window.piwikHelper.htmlDecode(segment.name);
      const { userContext } = this.privateState;
      if (userContext.hasSuperUserAccess && segment.login !== userContext.login) {
        segmentName += " (";
        segmentName += CoreHome.translate("General_CreatedByUser", [segment.login || ""]);
        if (Number(segment.enable_all_users) === 0) {
          segmentName += `, ${CoreHome.translate("SegmentEditor_VisibleToSuperUser")}`;
        }
        segmentName += ")";
      }
      return segmentName;
    }
    isSegmentVisibleToSuperUserOnly(segment) {
      const { userContext } = this.privateState;
      return userContext.hasSuperUserAccess && segment.login !== userContext.login && Number(segment.enable_all_users) === 0;
    }
    isSegmentSharedWithMeBySuperUser(segment) {
      const { userContext } = this.privateState;
      if (userContext.hasSuperUserAccess) {
        return false;
      }
      return segment.login !== userContext.login && Number(segment.enable_all_users) === 1;
    }
    getCurrentSegmentTitle() {
      const current = this.getCurrentSegment();
      if (current !== "") {
        const segment = this.getSegmentByDefinition(current);
        if (segment) {
          return this.getPlainSegmentName(segment);
        }
        return CoreHome.translate("SegmentEditor_CustomSegment");
      }
      return this.privateState.translations.SegmentEditor_DefaultAllVisits;
    }
    getCurrentSegmentTooltip() {
      let title = `${CoreHome.translate("SegmentEditor_ChooseASegment")}.`;
      title += ` ${CoreHome.translate("SegmentEditor_CurrentlySelectedSegment", [this.getCurrentSegmentTitle()])}`;
      return title;
    }
    getComparedSegmentDefinitions() {
      return CoreHome.ComparisonsStoreInstance.getSegmentComparisons().map(
        (comparison) => comparison.params.segment
      );
    }
    getComparisonLimit() {
      return Number(window.piwik.config.data_comparison_segment_limit) + 1;
    }
    isComparisonAvailable() {
      const comparisonService = CoreHome.ComparisonsStoreInstance;
      const isEnabled = comparisonService.isComparisonEnabled();
      return isEnabled || isEnabled === null;
    }
    isSegmentSelected(definition) {
      return definition === this.privateState.currentSegment || definition === decodeURIComponent(this.privateState.currentSegment);
    }
    isSegmentCompared(definition, comparedSegments) {
      return comparedSegments.indexOf(definition) !== -1 || comparedSegments.indexOf(decodeURIComponent(definition)) !== -1;
    }
    buildCompareState(definition, comparedSegments) {
      if (this.isSegmentCompared(definition, comparedSegments)) {
        return {
          state: "active",
          title: CoreHome.translate("SegmentEditor_CompareThisSegment")
        };
      }
      if (comparedSegments.length >= this.getComparisonLimit()) {
        return {
          state: "disabled",
          title: CoreHome.translate("General_MaximumNumberOfSegmentsComparedIs", [this.getComparisonLimit()])
        };
      }
      return {
        state: "",
        title: CoreHome.translate("SegmentEditor_CompareThisSegment")
      };
    }
    getCanUserEditSegment(segment) {
      return getCanUserEditSegment(
        segment,
        this.privateState.segmentAccess,
        this.privateState.userContext
      );
    }
    getEditSegmentTitle(segment, canEdit) {
      return getEditSegmentTitle(segment, canEdit, this.privateState.translations);
    }
    getDeleteSegmentTitle(segment, canEdit) {
      return getDeleteSegmentTitle(segment, canEdit, this.privateState.translations);
    }
    getStarSegmentTitle(segment, canEdit) {
      return getStarSegmentTitle(
        segment,
        canEdit,
        this.privateState.translations,
        this.privateState.userContext
      );
    }
    toggleStarredSegment(segment, idSegment) {
      segment.starred = !this.normalizeStarredState(segment.starred);
      const method = segment.starred ? "star" : "unstar";
      this.notifyStarredSegment(segment);
      const LegacyAjaxHelper = window.ajaxHelper;
      const ajaxHandler = new LegacyAjaxHelper();
      ajaxHandler.addParams({
        module: "API",
        format: "json",
        method: `SegmentEditor.${method}`,
        userLogin: this.privateState.userContext.login,
        idSegment: idSegment || ""
      }, "POST");
      ajaxHandler.useCallbackInCaseOfError();
      ajaxHandler.setCallback((response) => {
        if (!response || response.result === "error") {
          segment.starred = !this.normalizeStarredState(segment.starred);
          this.notifyStarredSegment(segment, true);
          return;
        }
        segment.starred = this.normalizeStarredState(response.starred);
        segment.starred_by = response.starred_by;
        this.notifyStarredSegment(segment);
      });
      ajaxHandler.send();
    }
    toggleStarredSegmentById(idSegment) {
      const segment = this.getSegmentFromId(idSegment);
      if (!segment) {
        return;
      }
      this.toggleStarredSegment(segment, idSegment);
    }
    notifyStarredSegment(segment, isError = false) {
      this.notifyChange();
      this.starChangeCallbacks.forEach((callback) => {
        callback(segment, isError);
      });
    }
    buildSearchContext(searchValue) {
      const rawSearch = searchValue || "";
      const hasSearch = rawSearch.length >= 2;
      return {
        hasSearch,
        lowerSearch: rawSearch.toLowerCase(),
        normalizedSearch: hasSearch ? window.piwikHelper.normalize(rawSearch) : ""
      };
    }
    matchesSearch(text, search) {
      if (!search.hasSearch) {
        return true;
      }
      const normalizedText = window.piwikHelper.normalize(text);
      const lowerText = text.toLowerCase();
      return normalizedText.indexOf(search.normalizedSearch) !== -1 || lowerText.indexOf(search.lowerSearch) !== -1;
    }
    buildHeaderEntry(type) {
      if (type === "shared") {
        return {
          key: "header-shared-with-you",
          type: "header",
          className: "segmentsSharedWithMeBySuperUser",
          label: CoreHome.translate("SegmentEditor_SharedWithYou"),
          tooltip: ""
        };
      }
      return {
        key: "header-visible-to-super-user",
        type: "header",
        className: "segmentsVisibleToSuperUser",
        label: CoreHome.translate("SegmentEditor_VisibleToSuperUser"),
        tooltip: ""
      };
    }
    buildAllVisitsEntry(context) {
      const allVisitsCompareState = this.buildCompareState("", context.comparedSegments);
      const label = [
        this.privateState.translations.SegmentEditor_DefaultAllVisits,
        this.privateState.translations.General_DefaultAppended
      ].join(" ");
      return {
        key: "segment-all-visits",
        type: "segment",
        classes: [
          this.privateState.currentSegment === "" ? "segmentSelected" : "",
          this.isSegmentCompared("", context.comparedSegments) ? "comparedSegment" : ""
        ].join(" ").trim(),
        idsegment: "",
        definition: "",
        label,
        tooltip: label,
        showStarButton: false,
        showStarPlaceholder: !this.privateState.isUserAnonymous,
        showEditButton: false,
        showEditPlaceholder: this.privateState.segmentAccess === "write",
        showCompareButton: context.comparisonAvailable,
        compareButtonClass: "segmentAction compareSegment allVisitsCompareSegment",
        compareTitle: allVisitsCompareState.title,
        compareState: allVisitsCompareState.state
      };
    }
    buildSegmentEntry(segment, tooltipText, labelText, context) {
      const canEdit = this.getCanUserEditSegment(segment);
      const compareState = this.buildCompareState(segment.definition, context.comparedSegments);
      const classes = [];
      if (this.isSegmentSelected(segment.definition)) {
        classes.push("segmentSelected");
      }
      if (segment.starred) {
        classes.push("segmentStarred");
      }
      if (this.isSegmentCompared(segment.definition, context.comparedSegments)) {
        classes.push("comparedSegment");
      }
      return {
        key: `segment-${segment.idsegment}`,
        type: "segment",
        classes: classes.join(" "),
        idsegment: `${segment.idsegment || ""}`,
        definition: segment.definition,
        label: labelText,
        tooltip: tooltipText,
        // Intentionally hide the star control for anonymous users rather than
        // showing a disabled state; this is the agreed product behavior.
        showStarButton: !this.privateState.isUserAnonymous,
        isStarred: this.normalizeStarredState(segment.starred),
        starTitle: this.getStarSegmentTitle(segment, canEdit),
        starState: canEdit ? "" : "disabled",
        showEditButton: this.privateState.segmentAccess === "write",
        editTitle: this.getEditSegmentTitle(segment, canEdit),
        editState: canEdit ? "" : "disabled",
        showCompareButton: context.comparisonAvailable,
        compareButtonClass: "segmentAction compareSegment",
        compareTitle: compareState.title,
        compareState: compareState.state
      };
    }
    buildSegmentEntries(context) {
      const entries = [];
      let hasSharedHeader = false;
      let hasSuperUserHeader = false;
      this.privateState.availableSegments.forEach((segment) => {
        const isStarred = this.normalizeStarredState(segment.starred);
        const labelText = this.getPlainSegmentName(segment);
        const tooltipText = this.getSegmentTooltipText(segment);
        if (!this.matchesSearch(tooltipText, context.search)) {
          return;
        }
        if (this.isSegmentSharedWithMeBySuperUser(segment) && !hasSharedHeader) {
          hasSharedHeader = true;
          entries.push(this.buildHeaderEntry("shared"));
        }
        if (this.isSegmentVisibleToSuperUserOnly(segment) && !hasSuperUserHeader) {
          hasSuperUserHeader = true;
          entries.push(this.buildHeaderEntry("superuser"));
        }
        entries.push(this.buildSegmentEntry(__spreadProps(__spreadValues({}, segment), {
          starred: isStarred
        }), tooltipText, labelText, context));
      });
      return entries;
    }
    buildNoResultsEntry() {
      return {
        key: "no-results",
        type: "no-results",
        classes: "filterNoResults grayed",
        idsegment: "",
        definition: "",
        label: this.privateState.translations.General_SearchNoResults,
        tooltip: this.privateState.translations.General_SearchNoResults,
        showStarButton: false,
        showEditButton: false,
        showCompareButton: false
      };
    }
    buildSelectorEntries(context) {
      const entries = [];
      const allVisitsEntry = this.buildAllVisitsEntry(context);
      if (this.matchesSearch(allVisitsEntry.label, context.search)) {
        entries.push(allVisitsEntry);
      }
      entries.push(...this.buildSegmentEntries(context));
      if (context.search.hasSearch && entries.filter((entry) => entry.type === "segment").length === 0) {
        entries.push(this.buildNoResultsEntry());
      }
      return entries;
    }
    buildViewModel(entries) {
      return {
        authorizedToCreateSegments: this.privateState.segmentAccess === "write",
        currentSegmentTitle: this.getCurrentSegmentTitle(),
        currentSegmentTooltip: this.getCurrentSegmentTooltip(),
        currentSegmentValue: this.privateState.currentSegment,
        entries,
        isExpanded: this.privateState.panelExpanded,
        isUserAnonymous: !!this.privateState.isUserAnonymous,
        loginUrl: this.privateState.loginUrl,
        manageSegmentsUrl: this.privateState.manageSegmentsUrl
      };
    }
    getSelectorViewModel(searchValue) {
      const { renderVersion } = this.privateState;
      if (renderVersion < 0) {
        throw new Error("Segment selector render version must not be negative");
      }
      const context = {
        comparedSegments: this.getComparedSegmentDefinitions(),
        comparisonAvailable: this.isComparisonAvailable(),
        search: this.buildSearchContext(searchValue)
      };
      const entries = this.buildSelectorEntries(context);
      return this.buildViewModel(entries);
    }
  }
  const SegmentSelectorStore$1 = new SegmentSelectorStore();
  const starPath = "M8.64315 1.72117C8.67601 1.65477 8.72679 1.59887 8.78974 1.55979C8.85268 1.52071 8.9253 1.5 8.9994 1.5C9.07349 1.5 9.14611 1.52071 9.20906 1.55979C9.27201 1.59887 9.32278 1.65477 9.35565 1.72117L11.0881 5.23042C11.2023 5.4614 11.3708 5.66123 11.5791 5.81276C11.7875 5.96429 12.0295 6.063 12.2844 6.10042L16.1589 6.66742C16.2323 6.67806 16.3013 6.70902 16.358 6.75682C16.4147 6.80461 16.457 6.86733 16.4799 6.93787C16.5029 7.00842 16.5056 7.08397 16.4878 7.156C16.4701 7.22802 16.4325 7.29363 16.3794 7.34542L13.5774 10.0739C13.3926 10.254 13.2544 10.4763 13.1745 10.7216C13.0947 10.967 13.0757 11.2281 13.1191 11.4824L13.7806 15.3374C13.7936 15.4108 13.7857 15.4863 13.7578 15.5554C13.7299 15.6245 13.6831 15.6844 13.6228 15.7282C13.5625 15.772 13.4911 15.7979 13.4168 15.8031C13.3425 15.8083 13.2682 15.7924 13.2024 15.7574L9.7389 13.9364C9.51068 13.8166 9.25678 13.754 8.99902 13.754C8.74126 13.754 8.48736 13.8166 8.25915 13.9364L4.7964 15.7574C4.73064 15.7922 4.65644 15.8079 4.58223 15.8026C4.50803 15.7973 4.43679 15.7713 4.37662 15.7276C4.31645 15.6838 4.26977 15.6241 4.24189 15.5551C4.21401 15.4861 4.20604 15.4107 4.2189 15.3374L4.87965 11.4832C4.92329 11.2287 4.90438 10.9675 4.82455 10.722C4.74472 10.4764 4.60635 10.254 4.4214 10.0739L1.6194 7.34617C1.56584 7.29444 1.52789 7.22872 1.50987 7.15648C1.49185 7.08423 1.49447 7.00838 1.51746 6.93756C1.54044 6.86674 1.58285 6.8038 1.63985 6.75591C1.69686 6.70801 1.76617 6.67709 1.8399 6.66667L5.71365 6.10042C5.96884 6.06329 6.21119 5.96471 6.41983 5.81316C6.62848 5.66161 6.79717 5.46162 6.9114 5.23042L8.64315 1.72117Z";
  const _sfc_main$5 = vue.defineComponent({
    name: "StarIcon",
    props: {
      filled: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      iconFill() {
        return this.filled ? "currentColor" : "none";
      },
      starPath() {
        return starPath;
      }
    }
  });
  const _hoisted_1$5 = {
    xmlns: "http://www.w3.org/2000/svg",
    width: "18",
    height: "18",
    viewBox: "0 0 18 18",
    "aria-hidden": "true"
  };
  const _hoisted_2$2 = ["fill", "d"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("svg", _hoisted_1$5, [
      vue.createElementVNode("path", {
        "stroke-width": "1.5",
        "stroke-linecap": "round",
        "stroke-linejoin": "round",
        stroke: "currentColor",
        fill: _ctx.iconFill,
        d: _ctx.starPath
      }, null, 8, _hoisted_2$2)
    ]);
  }
  const StarIcon = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = vue.defineComponent({
    name: "StarButton",
    components: {
      StarIcon
    },
    props: {
      segment: {
        type: Object,
        required: true
      }
    },
    methods: {
      toggleStar() {
        if (this.segment.starState === "disabled" || !this.segment.idsegment) {
          return;
        }
        SegmentSelectorStore$1.toggleStarredSegmentById(this.segment.idsegment);
      }
    }
  });
  const _hoisted_1$4 = ["data-star", "title", "data-state"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_StarIcon = vue.resolveComponent("StarIcon");
    return vue.openBlock(), vue.createElementBlock("button", {
      "data-star": _ctx.segment.idsegment,
      class: "segmentAction starSegment",
      title: _ctx.segment.starTitle,
      "data-state": _ctx.segment.starState,
      onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.toggleStar(), ["stop", "prevent"]))
    }, [
      vue.createVNode(_component_StarIcon, {
        filled: !!_ctx.segment.isStarred
      }, null, 8, ["filled"])
    ], 8, _hoisted_1$4);
  }
  const StarButton = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = vue.defineComponent({
    name: "EditButton",
    props: {
      segment: {
        type: Object,
        required: true
      }
    },
    emits: ["openEditButton"],
    methods: {
      // This should be replaced with a direct call to store to open edit modal once we have migrated
      // it to its own vue component. For now we need this to dispatch the event to Segmentation.js
      dispatchOpenEvent() {
        if (this.segment.editState === "disabled" || !this.segment.idsegment) {
          return;
        }
        this.$emit("openEditButton", this.segment.idsegment);
      }
    }
  });
  const _hoisted_1$3 = ["title", "data-state"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return _ctx.segment.showEditButton ? (vue.openBlock(), vue.createElementBlock("button", {
      key: 0,
      class: "segmentAction editSegment",
      title: _ctx.segment.editTitle,
      "data-state": _ctx.segment.editState,
      onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.dispatchOpenEvent(), ["stop", "prevent"]))
    }, null, 8, _hoisted_1$3)) : vue.createCommentVNode("", true);
  }
  const EditButton = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = vue.defineComponent({
    name: "CompareIcon",
    props: {
      state: {
        type: String,
        required: true
      }
    },
    computed: {
      iconFill() {
        return this.state === "active" ? "currentColor" : "transparent";
      }
    }
  });
  const _hoisted_1$2 = {
    xmlns: "http://www.w3.org/2000/svg",
    width: "18",
    height: "18",
    viewBox: "0 0 18 18",
    "aria-hidden": "true"
  };
  const _hoisted_2$1 = ["fill"];
  const _hoisted_3$1 = ["fill"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("svg", _hoisted_1$2, [
      vue.createElementVNode("path", {
        d: "M0.78125 2H7.78125V16H0.78125L3.00852 9L0.78125 2Z",
        fill: _ctx.iconFill,
        stroke: "currentColor",
        "stroke-width": "1.5",
        "stroke-linecap": "round",
        "stroke-linejoin": "round"
      }, null, 8, _hoisted_2$1),
      vue.createElementVNode("path", {
        d: "M10.2188 2H17.2188L14.9915 9L17.2188 16H10.2188V2Z",
        fill: _ctx.iconFill,
        stroke: "currentColor",
        "stroke-width": "1.5",
        "stroke-linecap": "round",
        "stroke-linejoin": "round"
      }, null, 8, _hoisted_3$1)
    ]);
  }
  const CompareIcon = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    name: "CompareButton",
    components: {
      CompareIcon
    },
    props: {
      segment: {
        type: Object,
        required: true
      },
      isAnonymous: {
        type: Boolean,
        default: false
      }
    },
    emits: ["toggleCompareButton"],
    methods: {
      // This should be replaced with a direct call to the store to toggle the comparison once the
      // add/edit segment modal is migrated to its own vue component, since that migration will
      // introduce the store-driven panel close mechanism this action depends on. For now we need
      // this to dispatch the event to Segmentation.js.
      dispatchToggleEvent() {
        if (this.segment.compareState === "disabled" || typeof this.segment.definition === "undefined") {
          return;
        }
        this.$emit("toggleCompareButton", this.segment.definition);
      }
    }
  });
  const _hoisted_1$1 = ["title", "data-state"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CompareIcon = vue.resolveComponent("CompareIcon");
    return _ctx.segment.showCompareButton ? (vue.openBlock(), vue.createElementBlock("button", {
      key: 0,
      class: vue.normalizeClass([_ctx.segment.compareButtonClass, { isAnonymous: _ctx.isAnonymous }]),
      title: _ctx.segment.compareTitle,
      "data-state": _ctx.segment.compareState,
      onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => _ctx.dispatchToggleEvent(), ["stop", "prevent"]))
    }, [
      vue.createVNode(_component_CompareIcon, {
        state: _ctx.segment.compareState || ""
      }, null, 8, ["state"])
    ], 10, _hoisted_1$1)) : vue.createCommentVNode("", true);
  }
  const CompareButton = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    name: "SegmentSelector",
    components: {
      CompareButton,
      EditButton,
      SearchInput: CoreHome.SearchInput,
      StarButton
    },
    data() {
      return {
        filterTimer: null,
        panelContainer: null,
        searchInput: "",
        debouncedSearchInput: "",
        starAnimationClasses: {},
        unsubscribeStarChange: null
      };
    },
    computed: {
      viewModel() {
        if (!SegmentSelectorStore$1.state.value.isInitialized) {
          return null;
        }
        const filterValue = this.debouncedSearchInput.length >= 2 ? this.debouncedSearchInput : "";
        return SegmentSelectorStore$1.getSelectorViewModel(filterValue);
      }
    },
    mounted() {
      const root = this.$refs.root;
      this.panelContainer = root.closest(".segmentListContainer");
      if (this.panelContainer) {
        this.panelContainer.addEventListener("SegmentEditor.resetFilter", this.clearSearch);
      }
      this.unsubscribeStarChange = SegmentSelectorStore$1.onStarChange((segment, isError) => {
        const segmentId = `${segment.idsegment || ""}`;
        if (!segmentId) {
          return;
        }
        this.starAnimationClasses = __spreadProps(__spreadValues({}, this.starAnimationClasses), {
          [segmentId]: isError ? "segmentStarErrorAnimation" : "segmentStarAnimation"
        });
      });
    },
    beforeUnmount() {
      if (this.panelContainer) {
        this.panelContainer.removeEventListener("SegmentEditor.resetFilter", this.clearSearch);
      }
      if (this.unsubscribeStarChange) {
        this.unsubscribeStarChange();
        this.unsubscribeStarChange = null;
      }
      if (this.filterTimer) {
        window.clearTimeout(this.filterTimer);
        this.filterTimer = null;
      }
    },
    watch: {
      searchInput(newValue) {
        this.onSearchInput(newValue);
      }
    },
    methods: {
      translate: CoreHome.translate,
      dispatchPanelEvent(eventName, detail) {
        if (!this.panelContainer) {
          return;
        }
        this.panelContainer.dispatchEvent(new CustomEvent(eventName, {
          bubbles: true,
          detail
        }));
      },
      togglePanel() {
        this.dispatchPanelEvent("SegmentEditor:toggle-panel");
      },
      selectSegment(entry) {
        if (entry.type !== "segment") {
          return;
        }
        if (!entry.definition && entry.definition !== "") {
          return;
        }
        this.dispatchPanelEvent("SegmentEditor:select-segment", { definition: entry.definition });
      },
      toggleComparison(definition) {
        this.dispatchPanelEvent("SegmentEditor:toggle-comparison", { definition });
      },
      openEditSegment(id) {
        this.dispatchPanelEvent("SegmentEditor:open-edit-segment", { idSegment: id });
      },
      openAddSegment() {
        this.dispatchPanelEvent("SegmentEditor:open-add-segment");
      },
      getEntryClasses(entry) {
        const baseClasses = Array.isArray(entry.classes) ? entry.classes.join(" ") : entry.classes || "";
        const animationClass = entry.idsegment ? this.starAnimationClasses[`${entry.idsegment}`] || "" : "";
        return [baseClasses, animationClass].filter(Boolean).join(" ");
      },
      clearStarAnimationClass(entry) {
        if (!entry.idsegment) {
          return;
        }
        const segmentId = `${entry.idsegment}`;
        if (!this.starAnimationClasses[segmentId]) {
          return;
        }
        const classes = __spreadValues({}, this.starAnimationClasses);
        delete classes[segmentId];
        this.starAnimationClasses = classes;
      },
      onSearchInputUpdate(value) {
        if (!value) {
          this.clearSearch();
        }
      },
      onSearchInput(value) {
        this.onSearchInputUpdate(value);
        if (this.filterTimer) {
          window.clearTimeout(this.filterTimer);
        }
        this.filterTimer = window.setTimeout(() => {
          this.debouncedSearchInput = value;
          SegmentSelectorStore$1.notifyChange();
        }, 500);
      },
      clearSearch() {
        this.searchInput = "";
        this.debouncedSearchInput = "";
        if (this.filterTimer) {
          window.clearTimeout(this.filterTimer);
          this.filterTimer = null;
        }
        SegmentSelectorStore$1.notifyChange();
      }
    }
  });
  const _hoisted_1 = { ref: "root" };
  const _hoisted_2 = {
    key: 0,
    class: "segmentationContainer listHtml"
  };
  const _hoisted_3 = ["title"];
  const _hoisted_4 = { class: "dropdown dropdown-body" };
  const _hoisted_5 = { class: "segmentFilterContainer" };
  const _hoisted_6 = { class: "submenu" };
  const _hoisted_7 = { class: "segment-visits-label" };
  const _hoisted_8 = { class: "segmentList" };
  const _hoisted_9 = ["data-idsegment", "data-definition", "onClick", "onAnimationend"];
  const _hoisted_10 = ["title", "onKeyup"];
  const _hoisted_11 = {
    key: 1,
    class: "segmentAction starSegment segmentAction--placeholder",
    "aria-hidden": "true"
  };
  const _hoisted_12 = {
    key: 4,
    class: "segmentAction editSegment segmentAction--placeholder",
    "aria-hidden": "true"
  };
  const _hoisted_13 = ["href"];
  const _hoisted_14 = { key: 1 };
  const _hoisted_15 = { class: "youMustBeLoggedIn" };
  const _hoisted_16 = ["href"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SearchInput = vue.resolveComponent("SearchInput");
    const _component_star_button = vue.resolveComponent("star-button");
    const _component_compare_button = vue.resolveComponent("compare-button");
    const _component_edit_button = vue.resolveComponent("edit-button");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      _ctx.viewModel ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
        vue.createElementVNode("a", {
          class: "title",
          tabindex: "4",
          title: _ctx.viewModel.currentSegmentTooltip,
          onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => _ctx.togglePanel && _ctx.togglePanel(...args), ["prevent"]))
        }, [
          _cache[3] || (_cache[3] = vue.createElementVNode("span", { class: "icon icon-segment" }, null, -1)),
          vue.createElementVNode("span", {
            class: vue.normalizeClass(["segmentationTitle", { "segment-clicked": !!_ctx.viewModel.currentSegmentValue }])
          }, vue.toDisplayString(_ctx.viewModel.currentSegmentTitle), 3)
        ], 8, _hoisted_3),
        vue.createElementVNode("div", _hoisted_4, [
          vue.createElementVNode("div", _hoisted_5, [
            vue.createVNode(_component_SearchInput, {
              tabindex: "4",
              modelValue: _ctx.searchInput,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.searchInput = $event),
              "show-clear": true
            }, null, 8, ["modelValue"])
          ]),
          vue.createElementVNode("ul", _hoisted_6, [
            vue.createElementVNode("li", null, [
              vue.createElementVNode("span", _hoisted_7, vue.toDisplayString(_ctx.translate("SegmentEditor_SelectSegmentOfVisits")), 1),
              vue.createElementVNode("div", _hoisted_8, [
                vue.createElementVNode("ul", null, [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.viewModel.entries, (entry) => {
                    return vue.openBlock(), vue.createElementBlock(vue.Fragment, {
                      key: entry.key
                    }, [
                      entry.type === "header" ? (vue.openBlock(), vue.createElementBlock("span", {
                        key: 0,
                        class: vue.normalizeClass(entry.className)
                      }, [
                        _cache[4] || (_cache[4] = vue.createElementVNode("hr", null, null, -1)),
                        vue.createTextVNode(" " + vue.toDisplayString(entry.label) + ": ", 1),
                        _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1))
                      ], 2)) : entry.type === "no-results" ? (vue.openBlock(), vue.createElementBlock("li", {
                        key: 1,
                        class: vue.normalizeClass(_ctx.getEntryClasses(entry))
                      }, vue.toDisplayString(entry.label), 3)) : (vue.openBlock(), vue.createElementBlock("li", {
                        key: 2,
                        class: vue.normalizeClass(_ctx.getEntryClasses(entry)),
                        "data-idsegment": entry.idsegment,
                        "data-definition": entry.definition,
                        onClick: vue.withModifiers(($event) => _ctx.selectSegment(entry), ["prevent"]),
                        onAnimationend: ($event) => _ctx.clearStarAnimationClass(entry)
                      }, [
                        vue.createElementVNode("span", {
                          class: "segname",
                          tabindex: "4",
                          title: entry.tooltip,
                          onKeyup: vue.withKeys(vue.withModifiers(($event) => _ctx.selectSegment(entry), ["prevent"]), ["enter"])
                        }, vue.toDisplayString(entry.label), 41, _hoisted_10),
                        entry.type === "segment" ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
                          entry.showStarButton ? (vue.openBlock(), vue.createBlock(_component_star_button, {
                            key: 0,
                            segment: entry
                          }, null, 8, ["segment"])) : entry.showStarPlaceholder ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_11)) : vue.createCommentVNode("", true),
                          entry.showCompareButton ? (vue.openBlock(), vue.createBlock(_component_compare_button, {
                            key: 2,
                            segment: entry,
                            "is-anonymous": _ctx.viewModel.isUserAnonymous,
                            onToggleCompareButton: _ctx.toggleComparison
                          }, null, 8, ["segment", "is-anonymous", "onToggleCompareButton"])) : vue.createCommentVNode("", true),
                          entry.showEditButton ? (vue.openBlock(), vue.createBlock(_component_edit_button, {
                            key: 3,
                            segment: entry,
                            onOpenEditButton: _ctx.openEditSegment
                          }, null, 8, ["segment", "onOpenEditButton"])) : entry.showEditPlaceholder ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_12)) : vue.createCommentVNode("", true)
                        ], 64)) : vue.createCommentVNode("", true)
                      ], 42, _hoisted_9))
                    ], 64);
                  }), 128))
                ])
              ])
            ])
          ]),
          _ctx.viewModel.authorizedToCreateSegments ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 0 }, [
            vue.createElementVNode("button", {
              tabindex: "4",
              class: "add_new_segment btn",
              onClick: _cache[2] || (_cache[2] = vue.withModifiers((...args) => _ctx.openAddSegment && _ctx.openAddSegment(...args), ["stop", "prevent"]))
            }, [
              _cache[6] || (_cache[6] = vue.createElementVNode("span", { class: "icon-add" }, null, -1)),
              vue.createTextVNode("   " + vue.toDisplayString(_ctx.translate("SegmentEditor_AddNewSegment")), 1)
            ]),
            vue.createElementVNode("a", {
              href: _ctx.viewModel.manageSegmentsUrl,
              tabindex: "4",
              class: "btn btn-block btn-outline manage_segment_btn"
            }, vue.toDisplayString(_ctx.translate("SegmentEditor_ManageSegments")), 9, _hoisted_13)
          ], 64)) : _ctx.viewModel.isUserAnonymous ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_14, [
            vue.createElementVNode("span", _hoisted_15, vue.toDisplayString(_ctx.translate("SegmentEditor_YouMustBeLoggedInToCreateSegments")), 1),
            vue.createElementVNode("a", {
              href: _ctx.viewModel.loginUrl,
              tabindex: "4",
              class: "sign_in_segment_btn btn"
            }, vue.toDisplayString(_ctx.translate("Login_LogIn")), 9, _hoisted_16)
          ])) : vue.createCommentVNode("", true)
        ])
      ])) : vue.createCommentVNode("", true)
    ], 512);
  }
  const SegmentSelector = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.CompareIcon = CompareIcon;
  exports2.SegmentGenerator = SegmentGenerator;
  exports2.SegmentGeneratorStore = SegmentGeneratorStore$1;
  exports2.SegmentSelector = SegmentSelector;
  exports2.SegmentSelectorStore = SegmentSelectorStore$1;
  exports2.StarIcon = StarIcon;
  exports2.getCanUserEditSegment = getCanUserEditSegment;
  exports2.getDeleteSegmentTitle = getDeleteSegmentTitle;
  exports2.getEditSegmentTitle = getEditSegmentTitle;
  exports2.getStarSegmentTitle = getStarSegmentTitle;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
