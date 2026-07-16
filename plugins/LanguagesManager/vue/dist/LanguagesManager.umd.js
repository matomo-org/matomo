(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.LanguagesManager = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const Field = CoreHome.useExternalPluginComponent("CorePluginsAdmin", "Field");
  const _sfc_main$2 = vue.defineComponent({
    components: {
      Field
    },
    directives: {
      ContentTable: CoreHome.ContentTable
    },
    data() {
      return {
        compareTranslations: null,
        existingTranslations: [],
        languages: [],
        compareLanguage: "",
        searchTerm: ""
      };
    },
    created() {
      this.fetchTranslations("en");
      this.fetchLanguages();
    },
    methods: {
      fetchTranslations(languageCode) {
        CoreHome.AjaxHelper.fetch({
          method: "LanguagesManager.getTranslationsForLanguage",
          filter_limit: -1,
          languageCode
        }).then((response) => {
          if (!response) {
            return;
          }
          if (languageCode === "en") {
            this.existingTranslations = response;
          } else {
            this.compareTranslations = {};
            response.forEach((translation) => {
              this.compareTranslations[translation.label] = translation.value;
            });
          }
        });
      },
      fetchLanguages() {
        CoreHome.AjaxHelper.fetch({
          method: "LanguagesManager.getAvailableLanguagesInfo",
          filter_limit: -1
        }).then((languages) => {
          this.languages = [{
            key: "",
            value: "None"
          }];
          if (languages) {
            languages.forEach((language) => {
              if (language.code === "en") {
                return;
              }
              this.languages.push({
                key: language.code,
                value: language.name
              });
            });
          }
        });
      },
      doCompareLanguage() {
        if (this.compareLanguage) {
          this.compareTranslations = null;
          this.fetchTranslations(this.compareLanguage);
        }
      }
    },
    computed: {
      filteredTranslations() {
        let filtered = this.existingTranslations.filter(
          (t) => t.label.includes(this.searchTerm) || t.value.includes(this.searchTerm)
        );
        filtered = filtered.slice(0, 1e3);
        return filtered;
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
  const _hoisted_1$1 = ["href"];
  const _hoisted_2$1 = { style: { "word-break": "break-all" } };
  const _hoisted_3$1 = { key: 0 };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_Field = vue.resolveComponent("Field");
    const _directive_content_table = vue.resolveDirective("content-table");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      vue.createElementVNode("p", null, [
        _cache[2] || (_cache[2] = vue.createTextVNode(" This page helps you to find existing translations that you can reuse in your Plugin. If you want to know more about translations have a look at our ", -1)),
        vue.createElementVNode("a", {
          href: _ctx.externalRawLink("https://developer.matomo.org/guides/internationalization"),
          rel: "noreferrer noopener",
          target: "_blank"
        }, "Internationalization guide", 8, _hoisted_1$1),
        _cache[3] || (_cache[3] = vue.createTextVNode(". Enter a search term to find translations and their corresponding keys: ", -1))
      ]),
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "text",
          name: "alias",
          "inline-help": "Search for English translation. Max 1000 results will be shown.",
          placeholder: "Search for English translation",
          modelValue: _ctx.searchTerm,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.searchTerm = $event)
        }, null, 8, ["modelValue"])
      ]),
      vue.createElementVNode("div", null, [
        vue.createVNode(_component_Field, {
          uicontrol: "select",
          name: "translationSearch.compareLanguage",
          "inline-help": "Optionally select a language to compare the English language with.",
          "model-value": _ctx.compareLanguage,
          "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => {
            _ctx.compareLanguage = $event;
            _ctx.doCompareLanguage();
          }),
          options: _ctx.languages
        }, null, 8, ["model-value", "options"])
      ]),
      _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1)),
      _cache[7] || (_cache[7] = vue.createElementVNode("br", null, null, -1)),
      vue.withDirectives((vue.openBlock(), vue.createElementBlock("table", _hoisted_2$1, [
        vue.createElementVNode("thead", null, [
          vue.createElementVNode("tr", null, [
            _cache[4] || (_cache[4] = vue.createElementVNode("th", { style: { "width": "250px" } }, "Key", -1)),
            _cache[5] || (_cache[5] = vue.createElementVNode("th", null, "English translation", -1)),
            vue.withDirectives(vue.createElementVNode("th", null, "Compare translation", 512), [
              [vue.vShow, _ctx.compareLanguage && _ctx.compareTranslations]
            ])
          ])
        ]),
        vue.createElementVNode("tbody", null, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.filteredTranslations, (translation) => {
            return vue.openBlock(), vue.createElementBlock("tr", {
              key: translation.label
            }, [
              vue.createElementVNode("td", null, vue.toDisplayString(translation.label), 1),
              vue.createElementVNode("td", null, vue.toDisplayString(translation.value), 1),
              _ctx.compareLanguage && _ctx.compareTranslations ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_3$1, vue.toDisplayString(_ctx.compareTranslations[translation.label]), 1)) : vue.createCommentVNode("", true)
            ]);
          }), 128))
        ])
      ])), [
        [vue.vShow, _ctx.searchTerm],
        [_directive_content_table]
      ])
    ]);
  }
  const TranslationSearch = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    components: {
      ContentBlock: CoreHome.ContentBlock,
      TranslationSearch
    }
  });
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TranslationSearch = vue.resolveComponent("TranslationSearch");
    const _component_ContentBlock = vue.resolveComponent("ContentBlock");
    return vue.openBlock(), vue.createBlock(_component_ContentBlock, {
      "content-title": _ctx.translate("LanguagesManager_TranslationSearch"),
      feature: "true"
    }, {
      default: vue.withCtx(() => [
        vue.createVNode(_component_TranslationSearch)
      ]),
      _: 1
    }, 8, ["content-title"]);
  }
  const TranslationSearchPage = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  /*!
   * Matomo - free/libre analytics platform
   *
   * @link    https://matomo.org
   * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
   */
  const { $ } = window;
  function postLanguageChange(element, event) {
    const value = $(event.target).attr("value");
    if (value) {
      $(element).find("#language").val(value).parents("form").submit();
    }
  }
  const LanguageSelector = {
    mounted(el, binding) {
      binding.value.onClick = postLanguageChange.bind(null, el);
      $(el).on("click", "a[value]", binding.value.onClick);
    },
    unmounted(el, binding) {
      $(el).off("click", "a[value]", binding.value.onClick);
    }
  };
  const _sfc_main = vue.defineComponent({
    props: {
      tokenAuth: String,
      formNonce: {
        type: String,
        required: true
      },
      languages: {
        type: Array,
        required: true
      },
      currentLanguageCode: {
        type: String,
        required: true
      },
      currentLanguageName: {
        type: String,
        required: true
      }
    },
    components: {
      MenuItemsDropdown: CoreHome.MenuItemsDropdown
    },
    data() {
      return {
        selectedLanguage: this.currentLanguageCode
      };
    },
    methods: {
      onSelect(selected) {
        this.selectedLanguage = selected.getAttribute("value");
        vue.nextTick().then(() => {
          this.$refs.form.submit();
        });
      }
    }
  });
  const _hoisted_1 = { class: "languageSelection" };
  const _hoisted_2 = ["href"];
  const _hoisted_3 = ["value", "title"];
  const _hoisted_4 = {
    action: "index.php?module=LanguagesManager&action=saveLanguage",
    method: "post",
    ref: "form"
  };
  const _hoisted_5 = ["value"];
  const _hoisted_6 = ["value"];
  const _hoisted_7 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MenuItemsDropdown = vue.resolveComponent("MenuItemsDropdown");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createVNode(_component_MenuItemsDropdown, {
        "menu-title": _ctx.currentLanguageName,
        onAfterSelect: _cache[0] || (_cache[0] = ($event) => _ctx.onSelect($event))
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("a", {
            class: "item",
            target: "_blank",
            rel: "noreferrer noopener",
            href: _ctx.externalRawLink("https://matomo.org/translations/")
          }, vue.toDisplayString(_ctx.translate("LanguagesManager_AboutPiwikTranslations")), 9, _hoisted_2),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.languages, (language) => {
            return vue.openBlock(), vue.createElementBlock("a", {
              key: language.code,
              class: vue.normalizeClass(`item ${language.code === _ctx.currentLanguageCode ? "active" : ""}`),
              value: language.code,
              title: `${language.name} (${language.english_name})`
            }, vue.toDisplayString(language.name), 11, _hoisted_3);
          }), 128)),
          vue.createElementVNode("form", _hoisted_4, [
            vue.createElementVNode("input", {
              type: "hidden",
              name: "language",
              id: "language",
              value: _ctx.selectedLanguage
            }, null, 8, _hoisted_5),
            vue.createElementVNode("input", {
              type: "hidden",
              name: "nonce",
              id: "nonce",
              value: _ctx.formNonce
            }, null, 8, _hoisted_6),
            _ctx.tokenAuth ? (vue.openBlock(), vue.createElementBlock("input", {
              key: 0,
              type: "hidden",
              name: "token_auth",
              value: _ctx.tokenAuth
            }, null, 8, _hoisted_7)) : vue.createCommentVNode("", true)
          ], 512)
        ]),
        _: 1
      }, 8, ["menu-title"])
    ]);
  }
  const LanguagesDropdown = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.LanguageSelector = LanguageSelector;
  exports2.LanguagesDropdown = LanguagesDropdown;
  exports2.TranslationSearch = TranslationSearch;
  exports2.TranslationSearchPage = TranslationSearchPage;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
