(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome"), require("CorePluginsAdmin")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome", "CorePluginsAdmin"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.CustomDimensions = {}, global.Vue, global.CoreHome, global.CorePluginsAdmin));
})(this, (function(exports2, vue, CoreHome, CorePluginsAdmin) {
  "use strict";var __defProp = Object.defineProperty;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);

  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  class CustomDimensionsStore {
    constructor() {
      __publicField(this, "privateState", vue.reactive({
        customDimensions: [],
        availableScopes: [],
        extractionDimensions: [],
        isLoading: false,
        isUpdating: false
      }));
      __publicField(this, "state", vue.computed(() => vue.readonly(this.privateState)));
      __publicField(this, "isLoading", vue.computed(() => this.state.value.isLoading));
      __publicField(this, "isUpdating", vue.computed(() => this.state.value.isUpdating));
      __publicField(this, "extractionDimensions", vue.computed(() => this.state.value.extractionDimensions));
      __publicField(this, "extractionDimensionsOptions", vue.computed(
        () => this.extractionDimensions.value.map((e) => ({ key: e.value, value: e.name }))
      ));
      __publicField(this, "availableScopes", vue.computed(() => this.state.value.availableScopes));
      __publicField(this, "customDimensions", vue.computed(() => this.state.value.customDimensions));
      __publicField(this, "customDimensionsById", vue.computed(() => {
        const dimensionsById = {};
        this.customDimensions.value.forEach((c) => {
          dimensionsById[`${c.idcustomdimension}`] = c;
        });
        return dimensionsById;
      }));
      __publicField(this, "reloadPromise", null);
    }
    reload() {
      this.privateState.customDimensions = [];
      this.privateState.availableScopes = [];
      this.privateState.extractionDimensions = [];
      this.reloadPromise = null;
      return this.fetch();
    }
    fetch() {
      if (this.reloadPromise) {
        return this.reloadPromise;
      }
      this.privateState.isLoading = true;
      this.reloadPromise = Promise.all([
        this.fetchConfiguredCustomDimensions(),
        this.fetchAvailableExtractionDimensions(),
        this.fetchAvailableScopes()
      ]).finally(() => {
        this.privateState.isLoading = false;
      });
      return this.reloadPromise;
    }
    fetchConfiguredCustomDimensions() {
      return CoreHome.AjaxHelper.fetch({
        method: "CustomDimensions.getConfiguredCustomDimensions",
        filter_limit: "-1"
      }).then((r) => {
        this.privateState.customDimensions = r;
      });
    }
    fetchAvailableExtractionDimensions() {
      return CoreHome.AjaxHelper.fetch({
        method: "CustomDimensions.getAvailableExtractionDimensions",
        filter_limit: "-1"
      }).then((r) => {
        this.privateState.extractionDimensions = r;
      });
    }
    fetchAvailableScopes() {
      return CoreHome.AjaxHelper.fetch({
        method: "CustomDimensions.getAvailableScopes",
        filter_limit: "-1"
      }).then((r) => {
        this.privateState.availableScopes = r;
      });
    }
    createOrUpdateDimension(dimension, method) {
      this.privateState.isUpdating = true;
      return CoreHome.AjaxHelper.post(
        {
          method,
          scope: dimension.scope,
          idDimension: dimension.idcustomdimension,
          idSite: dimension.idsite,
          name: dimension.name,
          description: dimension.description,
          active: dimension.active ? "1" : "0",
          caseSensitive: dimension.case_sensitive ? "1" : "0"
        },
        {
          extractions: dimension.extractions
        }
      ).finally(() => {
        this.privateState.isUpdating = false;
      });
    }
  }
  const CustomDimensionsStore$1 = new CustomDimensionsStore();
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  function ucfirst(s) {
    return `${s[0].toUpperCase()}${s.slice(1)}`;
  }
  const notificationId = "customdimensions";
  const _sfc_main$2 = vue.defineComponent({
    props: {
      dimensionId: Number,
      dimensionScope: {
        type: String,
        required: true
      }
    },
    components: {
      ContentBlock: CoreHome.ContentBlock,
      Field: CorePluginsAdmin.Field,
      MatomoLoader: CoreHome.MatomoLoader
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        dimension: { extractions: [] },
        isUpdatingDim: false
      };
    },
    created() {
      this.init();
    },
    watch: {
      dimensionId() {
        this.init();
      }
    },
    methods: {
      removeAnyCustomDimensionNotification() {
        CoreHome.NotificationsStore.remove(notificationId);
      },
      showNotification(message, context) {
        CoreHome.NotificationsStore.show({
          message,
          context,
          id: notificationId,
          type: "transient"
        });
      },
      init() {
        if (this.dimensionId !== null) {
          this.removeAnyCustomDimensionNotification();
        }
        CustomDimensionsStore$1.fetch().then(() => {
          if (this.edit && this.dimensionId) {
            const dimensionInfo = CustomDimensionsStore$1.customDimensionsById.value[this.dimensionId];
            if (!dimensionInfo) {
              CoreHome.MatomoUrl.updateHashToUrl("/list");
              return;
            }
            this.dimension = CoreHome.clone(dimensionInfo);
            if (this.dimension && !this.dimension.extractions.length) {
              this.addExtraction();
            }
          } else if (this.create) {
            this.dimension = {
              idsite: CoreHome.Matomo.idSite,
              name: "",
              description: "",
              active: true,
              extractions: [],
              scope: this.dimensionScope,
              case_sensitive: true
            };
            this.addExtraction();
          }
        });
      },
      removeExtraction(index) {
        if (index > -1) {
          this.dimension.extractions.splice(index, 1);
        }
      },
      addExtraction() {
        if (this.doesScopeSupportExtraction) {
          this.dimension.extractions.push({
            dimension: "url",
            pattern: ""
          });
        }
      },
      createCustomDimension() {
        this.isUpdatingDim = true;
        CustomDimensionsStore$1.createOrUpdateDimension(
          this.dimension,
          "CustomDimensions.configureNewCustomDimension"
        ).then(() => {
          this.showNotification(CoreHome.translate("CustomDimensions_DimensionCreated"), "success");
          CustomDimensionsStore$1.reload();
          CoreHome.MatomoUrl.updateHashToUrl("/list");
        }).finally(() => {
          this.isUpdatingDim = false;
        });
      },
      updateCustomDimension() {
        this.isUpdatingDim = true;
        CustomDimensionsStore$1.createOrUpdateDimension(
          this.dimension,
          "CustomDimensions.configureExistingCustomDimension"
        ).then(() => {
          this.showNotification(CoreHome.translate("CustomDimensions_DimensionUpdated"), "success");
          CustomDimensionsStore$1.reload();
          CoreHome.MatomoUrl.updateHashToUrl("/list");
        }).finally(() => {
          this.isUpdatingDim = false;
        });
      },
      manuallyTrackCodeViaJs(dimension) {
        return `_paq.push(['setCustomDimension', ${dimension.idcustomdimension}, '${CoreHome.translate("CustomDimensions_ExampleValue")}']);`;
      },
      manuallyTrackCodeViaPhp(dimension) {
        return `$tracker->setCustomDimension('${dimension.idcustomdimension}', '${CoreHome.translate("CustomDimensions_ExampleValue")}');`;
      }
    },
    computed: {
      isLoading() {
        return CustomDimensionsStore$1.isLoading.value;
      },
      isUpdating() {
        return CustomDimensionsStore$1.isUpdating.value || this.isUpdatingDim;
      },
      nameInlineHelpText() {
        return [
          CoreHome.translate("CustomDimensions_NameHelpText"),
          CoreHome.translate("CustomDimensions_NameAllowedCharacters")
        ].join(" ");
      },
      create() {
        return this.dimensionId === 0;
      },
      edit() {
        return !this.create;
      },
      extractionDimensionsOptions() {
        return CustomDimensionsStore$1.extractionDimensionsOptions.value;
      },
      availableScopes() {
        return CustomDimensionsStore$1.availableScopes.value;
      },
      doesScopeSupportExtraction() {
        var _a;
        if (!((_a = this.dimension) == null ? void 0 : _a.scope) || !this.availableScopes) {
          return false;
        }
        const dimensionScope = this.availableScopes.find(
          (scope) => scope.value === this.dimension.scope
        );
        return dimensionScope == null ? void 0 : dimensionScope.supportsExtractions;
      },
      contentTitleText() {
        var _a;
        return CoreHome.translate(
          "CustomDimensions_ConfigureDimension",
          ucfirst(this.dimensionScope),
          `${((_a = this.dimension) == null ? void 0 : _a.index) || ""}`
        );
      },
      howToTrackManuallyText() {
        const link = "https://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions";
        return CoreHome.translate(
          "CustomDimensions_HowToTrackManuallyViaJsDetails",
          `<a target=_blank href="${link}" rel="noreferrer noopener">`,
          "</a>"
        );
      },
      manuallyTrackCode() {
        const exampleValue = CoreHome.translate("CustomDimensions_ExampleValue");
        return `&dimension${this.dimension.idcustomdimension}=${exampleValue}`;
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
  const _hoisted_1$2 = { class: "editCustomDimension" };
  const _hoisted_2$2 = { class: "loadingPiwik" };
  const _hoisted_3$2 = { class: "row form-group" };
  const _hoisted_4$2 = { class: "col s12" };
  const _hoisted_5$2 = { class: "col s12 m6" };
  const _hoisted_6$1 = { class: "row" };
  const _hoisted_7$1 = { class: "col s12 m6" };
  const _hoisted_8$1 = { class: "col s12 m6" };
  const _hoisted_9$1 = { class: "col s12" };
  const _hoisted_10$1 = ["onClick"];
  const _hoisted_11$1 = { class: "row" };
  const _hoisted_12$1 = { class: "col s12" };
  const _hoisted_13$1 = { class: "col s12 m6 form-help" };
  const _hoisted_14$1 = ["value", "disabled"];
  const _hoisted_15$1 = ["value", "disabled"];
  const _hoisted_16$1 = {
    class: "btn cancel",
    type: "button",
    href: "#list"
  };
  const _hoisted_17 = { class: "alert alert-info howToTrackInfo" };
  const _hoisted_18 = ["innerHTML"];
  const _hoisted_19 = ["innerHTML"];
  const _hoisted_20 = ["innerHTML"];
  const _hoisted_21 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_Field = vue.resolveComponent("Field");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createVNode(_component_ContentBlock, { "content-title": _ctx.contentTitleText }, {
        default: vue.withCtx(() => {
          var _a;
          return [
            vue.withDirectives(vue.createElementVNode("p", null, [
              vue.createElementVNode("span", _hoisted_2$2, [
                vue.createVNode(_component_MatomoLoader),
                vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
              ])
            ], 512), [
              [vue.vShow, _ctx.isLoading || _ctx.isUpdating]
            ]),
            vue.withDirectives(vue.createElementVNode("div", null, [
              vue.createElementVNode("form", {
                onSubmit: _cache[5] || (_cache[5] = vue.withModifiers(($event) => _ctx.edit ? _ctx.updateCustomDimension() : _ctx.createCustomDimension(), ["prevent"]))
              }, [
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "text",
                    name: "name",
                    modelValue: _ctx.dimension.name,
                    "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.dimension.name = $event),
                    maxlength: 255,
                    required: true,
                    title: _ctx.translate("General_Name"),
                    placeholder: _ctx.translate("CustomDimensions_NamePlaceholder"),
                    "inline-help": _ctx.nameInlineHelpText
                  }, null, 8, ["modelValue", "title", "placeholder", "inline-help"])
                ]),
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "textarea",
                    name: "description",
                    modelValue: _ctx.dimension.description,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.dimension.description = $event),
                    maxlength: 1e3,
                    title: `${_ctx.translate("General_Description")} ${_ctx.translate("Goals_Optional")}`,
                    placeholder: _ctx.translate("CustomDimensions_DescriptionPlaceholder"),
                    "inline-help": _ctx.translate("CustomDimensions_DescriptionHelpText"),
                    "ui-control-attributes": { class: "compact-textarea" }
                  }, null, 8, ["modelValue", "title", "placeholder", "inline-help"])
                ]),
                vue.createElementVNode("div", null, [
                  vue.createVNode(_component_Field, {
                    uicontrol: "checkbox",
                    name: "active",
                    modelValue: _ctx.dimension.active,
                    "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.dimension.active = $event),
                    title: _ctx.translate("CorePluginsAdmin_Active"),
                    "inline-help": _ctx.translate("CustomDimensions_CannotBeDeleted")
                  }, null, 8, ["modelValue", "title", "inline-help"])
                ]),
                vue.withDirectives(vue.createElementVNode("div", _hoisted_3$2, [
                  vue.createElementVNode("h3", _hoisted_4$2, vue.toDisplayString(_ctx.translate("CustomDimensions_ExtractValue")), 1),
                  vue.createElementVNode("div", _hoisted_5$2, [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.dimension.extractions, (extraction, index) => {
                      return vue.openBlock(), vue.createElementBlock("div", {
                        class: vue.normalizeClass(`extraction${index}`),
                        key: index
                      }, [
                        vue.createElementVNode("div", _hoisted_6$1, [
                          vue.createElementVNode("div", _hoisted_7$1, [
                            vue.createElementVNode("div", null, [
                              vue.createVNode(_component_Field, {
                                uicontrol: "select",
                                name: `dimension${index}`,
                                modelValue: extraction.dimension,
                                "onUpdate:modelValue": ($event) => extraction.dimension = $event,
                                "full-width": true,
                                options: _ctx.extractionDimensionsOptions
                              }, null, 8, ["name", "modelValue", "onUpdate:modelValue", "options"])
                            ])
                          ]),
                          vue.createElementVNode("div", _hoisted_8$1, [
                            vue.createElementVNode("div", null, [
                              vue.createVNode(_component_Field, {
                                uicontrol: "text",
                                name: `pattern${index}`,
                                modelValue: extraction.pattern,
                                "onUpdate:modelValue": ($event) => extraction.pattern = $event,
                                "full-width": true,
                                title: extraction.dimension === "urlparam" ? _ctx.translate("CustomDimensions_UrlQueryStringParameter") : "eg. /blog/(.*)/"
                              }, null, 8, ["name", "modelValue", "onUpdate:modelValue", "title"])
                            ])
                          ]),
                          vue.createElementVNode("div", _hoisted_9$1, [
                            vue.withDirectives(vue.createElementVNode("span", {
                              class: "icon-plus",
                              onClick: _cache[3] || (_cache[3] = ($event) => _ctx.addExtraction())
                            }, null, 512), [
                              [vue.vShow, extraction.pattern]
                            ]),
                            vue.withDirectives(vue.createElementVNode("span", {
                              class: "icon-minus",
                              onClick: ($event) => _ctx.removeExtraction(index)
                            }, null, 8, _hoisted_10$1), [
                              [vue.vShow, _ctx.dimension.extractions.length > 1]
                            ])
                          ])
                        ])
                      ], 2);
                    }), 128)),
                    vue.createElementVNode("div", _hoisted_11$1, [
                      vue.createElementVNode("div", _hoisted_12$1, [
                        vue.createElementVNode("div", null, [
                          vue.withDirectives(vue.createVNode(_component_Field, {
                            uicontrol: "checkbox",
                            name: "casesensitive",
                            modelValue: _ctx.dimension.case_sensitive,
                            "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.dimension.case_sensitive = $event),
                            title: _ctx.translate("Goals_CaseSensitive")
                          }, null, 8, ["modelValue", "title"]), [
                            [vue.vShow, (_a = _ctx.dimension.extractions[0]) == null ? void 0 : _a.pattern]
                          ])
                        ])
                      ])
                    ])
                  ]),
                  vue.createElementVNode("div", _hoisted_13$1, vue.toDisplayString(_ctx.translate("CustomDimensions_ExtractionsHelp")), 1)
                ], 512), [
                  [vue.vShow, _ctx.doesScopeSupportExtraction]
                ]),
                vue.withDirectives(vue.createElementVNode("input", {
                  class: "btn update",
                  type: "submit",
                  value: _ctx.translate("General_Update"),
                  disabled: _ctx.isUpdating,
                  style: { "margin-right": "3.5px" }
                }, null, 8, _hoisted_14$1), [
                  [vue.vShow, _ctx.edit]
                ]),
                vue.withDirectives(vue.createElementVNode("input", {
                  class: "btn create",
                  type: "submit",
                  value: _ctx.translate("General_Create"),
                  disabled: _ctx.isUpdating,
                  style: { "margin-right": "3.5px" }
                }, null, 8, _hoisted_15$1), [
                  [vue.vShow, _ctx.create]
                ]),
                vue.createElementVNode("a", _hoisted_16$1, vue.toDisplayString(_ctx.translate("General_Cancel")), 1)
              ], 32),
              vue.withDirectives(vue.createElementVNode("div", _hoisted_17, [
                vue.createElementVNode("strong", null, vue.toDisplayString(_ctx.translate("CustomDimensions_HowToTrackManuallyTitle")), 1),
                vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CustomDimensions_HowToTrackManuallyViaJs")), 1),
                vue.createElementVNode("div", null, [
                  vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                    vue.createElementVNode("code", {
                      innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCodeViaJs(_ctx.dimension))
                    }, null, 8, _hoisted_18)
                  ])), [
                    [_directive_copy_to_clipboard, {}]
                  ])
                ]),
                vue.createElementVNode("p", {
                  innerHTML: _ctx.$sanitize(_ctx.howToTrackManuallyText)
                }, null, 8, _hoisted_19),
                vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CustomDimensions_HowToTrackManuallyViaPhp")), 1),
                vue.createElementVNode("div", null, [
                  vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                    vue.createElementVNode("code", {
                      innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCodeViaPhp(_ctx.dimension))
                    }, null, 8, _hoisted_20)
                  ])), [
                    [_directive_copy_to_clipboard, {}]
                  ])
                ]),
                vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CustomDimensions_HowToTrackManuallyViaHttp")), 1),
                vue.createElementVNode("div", null, [
                  vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                    vue.createElementVNode("code", {
                      innerHTML: _ctx.$sanitize(_ctx.manuallyTrackCode)
                    }, null, 8, _hoisted_21)
                  ])), [
                    [_directive_copy_to_clipboard, {}]
                  ])
                ])
              ], 512), [
                [vue.vShow, _ctx.edit]
              ])
            ], 512), [
              [vue.vShow, !_ctx.isLoading]
            ])
          ];
        }),
        _: 1
      }, 8, ["content-title"])
    ]);
  }
  const CustomDimensionsEdit = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    name: "listcustomdimensions",
    components: {
      MatomoLoader: CoreHome.MatomoLoader,
      EnrichedHeadline: CoreHome.EnrichedHeadline,
      ContentBlock: CoreHome.ContentBlock
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro,
      ContentTable: CoreHome.ContentTable
    },
    created() {
      CustomDimensionsStore$1.fetch();
    },
    methods: {
      ucfirst(s) {
        return ucfirst(s);
      },
      addDimension(scope) {
        CoreHome.MatomoUrl.updateHashToUrl(`/?idDimension=0&scope=${scope}`);
      }
    },
    computed: {
      isLoading() {
        return CustomDimensionsStore$1.isLoading.value;
      },
      availableScopes() {
        return CustomDimensionsStore$1.availableScopes.value;
      },
      contentIntroText() {
        const firstPart = CoreHome.translate(
          "CustomDimensions_CustomDimensionsIntroNext",
          '<a target=_blank href="https://piwik.org/docs/custom-variables">',
          "</a>",
          '<a target=_blank href="https://piwik.org/faq/general/faq_21117">',
          "</a>"
        );
        const secondPart = CoreHome.translate(
          "CustomDimensions_CustomDimensionsIntro",
          '<a target=_blank href="https://piwik.org/docs/custom-dimensions">',
          "</a>",
          this.siteName
        );
        return `${firstPart}${secondPart}`;
      },
      customDimensions() {
        return CustomDimensionsStore$1.customDimensions.value;
      },
      sortedCustomDimensions() {
        const result = [...this.customDimensions];
        result.sort((lhs, rhs) => {
          const lhsId = parseInt(`${lhs.idcustomdimension}`, 10);
          const rhsId = parseInt(`${rhs.idcustomdimension}`, 10);
          return lhsId - rhsId;
        });
        return result;
      },
      sortedCustomDimensionsByScope() {
        const result = {};
        this.sortedCustomDimensions.reduce(
          (acc, dim) => {
            acc[dim.scope] = acc[dim.scope] || [];
            acc[dim.scope].push(dim);
            return acc;
          },
          result
        );
        return result;
      },
      siteName() {
        return CoreHome.Matomo.helper.htmlEntities(CoreHome.Matomo.helper.htmlDecode(CoreHome.Matomo.siteName));
      }
    }
  });
  const _hoisted_1$1 = ["innerHTML"];
  const _hoisted_2$1 = { class: "loadingPiwik" };
  const _hoisted_3$1 = { class: "index" };
  const _hoisted_4$1 = { class: "name" };
  const _hoisted_5$1 = { class: "active" };
  const _hoisted_6 = { class: "action" };
  const _hoisted_7 = { colspan: "5" };
  const _hoisted_8 = { class: "index" };
  const _hoisted_9 = { class: "name" };
  const _hoisted_10 = { class: "extractions" };
  const _hoisted_11 = { class: "active" };
  const _hoisted_12 = { class: "action" };
  const _hoisted_13 = ["href"];
  const _hoisted_14 = { class: "tableActionBar" };
  const _hoisted_15 = ["disabled", "onClick"];
  const _hoisted_16 = { class: "info" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_EnrichedHeadline = vue.resolveComponent("EnrichedHeadline");
    const _component_MatomoLoader = vue.resolveComponent("MatomoLoader");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _directive_content_intro = vue.resolveDirective("content-intro");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
        vue.createElementVNode("h2", null, [
          vue.createVNode(_component_EnrichedHeadline, null, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CustomDimensions_CustomDimensions")), 1)
            ]),
            _: 1
          })
        ]),
        vue.createElementVNode("p", {
          innerHTML: _ctx.$sanitize(_ctx.contentIntroText)
        }, null, 8, _hoisted_1$1),
        vue.withDirectives(vue.createElementVNode("p", null, [
          vue.createElementVNode("span", _hoisted_2$1, [
            vue.createVNode(_component_MatomoLoader),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("General_LoadingData")), 1)
          ])
        ], 512), [
          [vue.vShow, _ctx.isLoading]
        ])
      ])), [
        [_directive_content_intro]
      ]),
      (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.availableScopes, (scope) => {
        return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
          key: scope.value,
          class: vue.normalizeClass(`scope-${scope.value}`)
        }, [
          vue.createVNode(_component_ContentBlock, {
            "content-title": _ctx.translate(`CustomDimensions_ScopeTitle${_ctx.ucfirst(scope.value)}`)
          }, {
            default: vue.withCtx(() => [
              vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate(`CustomDimensions_ScopeDescription${_ctx.ucfirst(scope.value)}`)) + " " + vue.toDisplayString(_ctx.translate(`CustomDimensions_ScopeDescription${_ctx.ucfirst(scope.value)}MoreInfo`)), 1),
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", null, [
                vue.createElementVNode("thead", null, [
                  vue.createElementVNode("tr", null, [
                    vue.createElementVNode("th", _hoisted_3$1, vue.toDisplayString(_ctx.translate("General_Id")), 1),
                    vue.createElementVNode("th", _hoisted_4$1, vue.toDisplayString(_ctx.translate("General_Name")), 1),
                    vue.withDirectives(vue.createElementVNode("th", { class: "extractions" }, vue.toDisplayString(_ctx.translate("CustomDimensions_Extractions")), 513), [
                      [vue.vShow, scope.supportsExtractions]
                    ]),
                    vue.createElementVNode("th", _hoisted_5$1, vue.toDisplayString(_ctx.translate("CorePluginsAdmin_Active")), 1),
                    vue.createElementVNode("th", _hoisted_6, vue.toDisplayString(_ctx.translate("General_Action")), 1)
                  ])
                ]),
                vue.createElementVNode("tbody", null, [
                  vue.withDirectives(vue.createElementVNode("tr", null, [
                    vue.createElementVNode("td", _hoisted_7, vue.toDisplayString(_ctx.translate("CustomDimensions_NoCustomDimensionConfigured")), 1)
                  ], 512), [
                    [vue.vShow, scope.numSlotsUsed === 0 && !_ctx.isLoading]
                  ]),
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.sortedCustomDimensionsByScope[scope.value], (customDimension) => {
                    var _a;
                    return vue.openBlock(), vue.createElementBlock("tr", {
                      class: vue.normalizeClass(["customdimension", `customdimension-${customDimension.idcustomdimension}`]),
                      key: customDimension.idcustomdimension
                    }, [
                      vue.createElementVNode("td", _hoisted_8, vue.toDisplayString(customDimension.idcustomdimension), 1),
                      vue.createElementVNode("td", _hoisted_9, vue.toDisplayString(customDimension.name), 1),
                      vue.withDirectives(vue.createElementVNode("td", _hoisted_10, [
                        vue.createElementVNode("span", {
                          class: vue.normalizeClass({ "icon-ok": (_a = customDimension.extractions[0]) == null ? void 0 : _a.pattern })
                        }, null, 2)
                      ], 512), [
                        [vue.vShow, scope.supportsExtractions]
                      ]),
                      vue.createElementVNode("td", _hoisted_11, [
                        vue.createElementVNode("span", {
                          class: vue.normalizeClass({ "icon-ok": customDimension.active })
                        }, null, 2)
                      ]),
                      vue.createElementVNode("td", _hoisted_12, [
                        vue.createElementVNode("a", {
                          class: "table-action icon-edit",
                          href: `#?idDimension=${customDimension.idcustomdimension}&scope=${scope.value}`
                        }, null, 8, _hoisted_13)
                      ])
                    ], 2);
                  }), 128))
                ])
              ])), [
                [_directive_content_table]
              ]),
              vue.createElementVNode("div", _hoisted_14, [
                vue.withDirectives(vue.createElementVNode("button", {
                  class: "btn",
                  disabled: !scope.numSlotsLeft,
                  onClick: ($event) => _ctx.addDimension(scope.value)
                }, [
                  _cache[0] || (_cache[0] = vue.createElementVNode("span", { class: "icon-add" }, null, -1)),
                  vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("CustomDimensions_ConfigureNewDimension")) + " ", 1),
                  vue.createElementVNode("span", _hoisted_16, "(" + vue.toDisplayString(_ctx.translate(
                    "CustomDimensions_XofYLeft",
                    String(scope.numSlotsLeft),
                    String(scope.numSlotsAvailable)
                  )) + ")", 1)
                ], 8, _hoisted_15), [
                  [vue.vShow, !_ctx.isLoading]
                ])
              ])
            ]),
            _: 2
          }, 1032, ["content-title"])
        ], 2)), [
          [vue.vShow, !_ctx.isLoading]
        ]);
      }), 128))
    ]);
  }
  const CustomDimensionsList = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = vue.defineComponent({
    components: {
      CustomDimensionsList,
      ContentBlock: CoreHome.ContentBlock,
      CustomDimensionsEdit
    },
    directives: {
      CopyToClipboard: CoreHome.CopyToClipboard
    },
    data() {
      return {
        editMode: false,
        dimensionId: null,
        dimensionScope: ""
      };
    },
    created() {
      vue.watch(() => CoreHome.MatomoUrl.hashParsed.value, () => {
        this.initState();
      });
      this.initState();
    },
    methods: {
      getValidDimensionScope(scope) {
        if (["action", "visit"].indexOf(scope) !== -1) {
          return scope;
        }
        return "";
      },
      initState() {
        const idDimension = CoreHome.MatomoUrl.hashParsed.value.idDimension;
        if (idDimension) {
          const scope = this.getValidDimensionScope(CoreHome.MatomoUrl.hashParsed.value.scope);
          if (idDimension === "0") {
            const parameters = {
              isAllowed: true,
              scope
            };
            CoreHome.Matomo.postEvent("CustomDimensions.initAddDimension", parameters);
            if (parameters && !parameters.isAllowed) {
              this.editMode = false;
              this.dimensionId = null;
              this.dimensionScope = "";
              return;
            }
          }
          this.editMode = true;
          this.dimensionId = parseInt(idDimension, 10);
          this.dimensionScope = scope;
        } else {
          this.editMode = false;
          this.dimensionId = null;
          this.dimensionScope = "";
        }
        CoreHome.Matomo.helper.lazyScrollToContent();
      }
    },
    computed: {
      addCustomDimCode() {
        return "./console customdimensions:add-custom-dimension --scope=action\n./console customdimensions:add-custom-dimension --scope=visit";
      },
      addMultipleCustomDimCode() {
        return "./console customdimensions:add-custom-dimension --scope=action --count=5";
      }
    }
  });
  const _hoisted_1 = { class: "manageCustomDimensions" };
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = ["textContent"];
  const _hoisted_4 = ["textContent"];
  const _hoisted_5 = { key: 1 };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    var _a;
    const _component_CustomDimensionsList = vue.resolveComponent("CustomDimensionsList");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    const _component_CustomDimensionsEdit = vue.resolveComponent("CustomDimensionsEdit");
    const _directive_copy_to_clipboard = vue.resolveDirective("copy-to-clipboard");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      !_ctx.editMode ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_CustomDimensionsList)
        ]),
        vue.createVNode(_component_ContentBlock, {
          id: "customDimensionsCreateMoreDimensions",
          "content-title": _ctx.translate("CustomDimensions_IncreaseAvailableCustomDimensionsTitle")
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("p", null, [
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CustomDimensions_IncreaseAvailableCustomDimensionsTakesLong")) + " ", 1),
              _cache[0] || (_cache[0] = vue.createElementVNode("br", null, null, -1)),
              _cache[1] || (_cache[1] = vue.createElementVNode("br", null, null, -1)),
              vue.createTextVNode(vue.toDisplayString(_ctx.translate("CustomDimensions_HowToCreateCustomDimension")) + " ", 1),
              _cache[2] || (_cache[2] = vue.createElementVNode("br", null, null, -1)),
              _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1))
            ]),
            vue.createElementVNode("div", null, [
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                vue.createElementVNode("code", {
                  textContent: vue.toDisplayString(_ctx.addCustomDimCode)
                }, null, 8, _hoisted_3)
              ])), [
                [_directive_copy_to_clipboard, {}]
              ])
            ]),
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("CustomDimensions_HowToManyCreateCustomDimensions")) + " " + vue.toDisplayString(_ctx.translate("CustomDimensions_ExampleCreateCustomDimensions", "5")), 1),
            vue.createElementVNode("div", null, [
              vue.withDirectives((vue.openBlock(), vue.createElementBlock("pre", null, [
                vue.createElementVNode("code", {
                  textContent: vue.toDisplayString(_ctx.addMultipleCustomDimCode)
                }, null, 8, _hoisted_4)
              ])), [
                [_directive_copy_to_clipboard, {}]
              ])
            ])
          ]),
          _: 1
        }, 8, ["content-title"])
      ])) : vue.createCommentVNode("", true),
      _ctx.editMode ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5, [
        vue.createElementVNode("div", null, [
          vue.createVNode(_component_CustomDimensionsEdit, {
            "dimension-id": (_a = _ctx.dimensionId) != null ? _a : void 0,
            "dimension-scope": _ctx.dimensionScope
          }, null, 8, ["dimension-id", "dimension-scope"])
        ])
      ])) : vue.createCommentVNode("", true)
    ]);
  }
  const Manage = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.CustomDimensionsStore = CustomDimensionsStore$1;
  exports2.Edit = CustomDimensionsEdit;
  exports2.List = CustomDimensionsList;
  exports2.Manage = Manage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
