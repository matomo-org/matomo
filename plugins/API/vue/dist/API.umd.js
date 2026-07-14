(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.API = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const { $ } = window;
  const _sfc_main = vue.defineComponent({
    props: {
      glossaryItems: {
        type: Object,
        required: true
      }
    },
    directives: {
      ContentIntro: CoreHome.ContentIntro
    },
    mounted() {
      const root = this.$refs.root;
      setTimeout(() => {
        $(".scrollspy", root).scrollSpy();
        $(".pushpin", root).pushpin({ top: $(".pushpin", root).offset().top });
        $(".tabs", root).tabs();
      });
    },
    methods: {
      entriesByLetter(entries) {
        const byLetter = {};
        entries.forEach((entry) => {
          byLetter[entry.letter] = byLetter[entry.letter] || [];
          byLetter[entry.letter].push(entry);
        });
        const byLetterArray = Object.entries(byLetter);
        byLetterArray.sort(([lhsLetter], [rhsLetter]) => {
          if (lhsLetter < rhsLetter) {
            return -1;
          }
          if (lhsLetter > rhsLetter) {
            return 1;
          }
          return 0;
        });
        return byLetterArray;
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
  const _hoisted_1 = {
    ref: "root",
    class: "glossaryPage"
  };
  const _hoisted_2 = { class: "row" };
  const _hoisted_3 = { class: "col s12" };
  const _hoisted_4 = { class: "row glossary" };
  const _hoisted_5 = { class: "col s12" };
  const _hoisted_6 = { class: "tabs" };
  const _hoisted_7 = ["href"];
  const _hoisted_8 = ["id"];
  const _hoisted_9 = { class: "card" };
  const _hoisted_10 = { class: "card-content" };
  const _hoisted_11 = {
    style: { "background": "#fff", "width": "100%" },
    class: "pushpin"
  };
  const _hoisted_12 = { class: "card-title" };
  const _hoisted_13 = { class: "pagination" };
  const _hoisted_14 = ["href"];
  const _hoisted_15 = ["id"];
  const _hoisted_16 = { style: { "color": "#4183C4", "font-weight": "bold" } };
  const _hoisted_17 = {
    key: 0,
    style: { "color": "#999", "text-transform": "uppercase", "font-weight": "normal", "margin-top": "-16px" }
  };
  const _hoisted_18 = ["innerHTML"];
  const _hoisted_19 = { key: 0 };
  const _hoisted_20 = {
    key: 1,
    style: { "color": "#bbb" }
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_content_intro = vue.resolveDirective("content-intro");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createElementVNode("div", _hoisted_2, [
        vue.createElementVNode("div", _hoisted_3, [
          vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", null, [
            vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate("API_Glossary")), 1),
            vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("API_LearnAboutCommonlyUsedTerms2")), 1)
          ])), [
            [_directive_content_intro]
          ])
        ])
      ]),
      vue.createElementVNode("div", _hoisted_4, [
        vue.createElementVNode("div", _hoisted_5, [
          vue.createElementVNode("ul", _hoisted_6, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.glossaryItems, (item, keyword, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: keyword,
                class: "tab col s3"
              }, [
                vue.createElementVNode("a", {
                  class: vue.normalizeClass(index === 0 ? "active" : ""),
                  href: `#${keyword}`
                }, vue.toDisplayString(item.title), 11, _hoisted_7)
              ]);
            }), 128))
          ])
        ]),
        (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.glossaryItems, (item, keyword) => {
          return vue.openBlock(), vue.createElementBlock("div", {
            key: keyword,
            id: keyword,
            class: "col s12"
          }, [
            vue.createElementVNode("div", _hoisted_9, [
              vue.createElementVNode("div", _hoisted_10, [
                vue.createElementVNode("div", _hoisted_11, [
                  vue.createElementVNode("h2", _hoisted_12, vue.toDisplayString(item.title), 1),
                  vue.createElementVNode("ul", _hoisted_13, [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(item.letters, (letter, index) => {
                      return vue.openBlock(), vue.createElementBlock("li", {
                        key: index,
                        class: "waves-effect",
                        style: { "margin-right": "3.5px" }
                      }, [
                        vue.createElementVNode("a", {
                          href: `#${keyword}${letter}`
                        }, vue.toDisplayString(letter), 9, _hoisted_14)
                      ]);
                    }), 128))
                  ])
                ]),
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.entriesByLetter(item.entries), ([letter, entries]) => {
                  return vue.openBlock(), vue.createElementBlock("div", {
                    key: letter,
                    class: "scrollspy",
                    id: `${keyword}${letter}`
                  }, [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(entries, (entry, index) => {
                      return vue.openBlock(), vue.createElementBlock("div", { key: index }, [
                        vue.createElementVNode("h3", _hoisted_16, vue.toDisplayString(entry.name), 1),
                        entry.subtitle ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_17, vue.toDisplayString(_ctx.translate(entry.subtitle)), 1)) : vue.createCommentVNode("", true),
                        vue.createElementVNode("p", null, [
                          vue.createElementVNode("span", {
                            innerHTML: _ctx.$sanitize(entry.documentation)
                          }, null, 8, _hoisted_18),
                          entry.id ? (vue.openBlock(), vue.createElementBlock("br", _hoisted_19)) : vue.createCommentVNode("", true),
                          entry.id ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_20, vue.toDisplayString(entry.id) + vue.toDisplayString(keyword === "metrics" || entry.is_metric ? " (API)" : ""), 1)) : vue.createCommentVNode("", true)
                        ])
                      ]);
                    }), 128))
                  ], 8, _hoisted_15);
                }), 128))
              ])
            ])
          ], 8, _hoisted_8);
        }), 128))
      ])
    ], 512);
  }
  const Glossary = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.Glossary = Glossary;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
