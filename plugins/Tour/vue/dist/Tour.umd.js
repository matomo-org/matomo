(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Tour = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";var __async = (__this, __arguments, generator) => {
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

  const PER_PAGE = 5;
  const _sfc_main = vue.defineComponent({
    components: {
      ActivityIndicator: CoreHome.ActivityIndicator
    },
    data() {
      return {
        loading: true,
        challenges: [],
        level: null,
        currentPage: 0
      };
    },
    computed: {
      isCompleted() {
        return this.challenges.every(
          (c) => c.isCompleted || c.isSkipped
        );
      },
      pagedChallenges() {
        const start = this.currentPage * PER_PAGE;
        return this.challenges.slice(start, start + PER_PAGE);
      },
      totalPages() {
        return Math.ceil(
          this.challenges.length / PER_PAGE
        );
      },
      hasPrevPage() {
        return this.currentPage > 0;
      },
      hasNextPage() {
        return this.currentPage < this.totalPages - 1;
      },
      statusLevelHtml() {
        if (!this.level) {
          return "";
        }
        return CoreHome.translate(
          "Tour_StatusLevel",
          `<strong>${this.level.currentLevelName}</strong>`,
          String(this.level.challengesNeededForNextLevel),
          `<strong>${this.level.nextLevelName}</strong>`
        );
      },
      youCanCallYourselfHtml() {
        return CoreHome.translate(
          "Tour_YouCanCallYourselfExpert",
          '<strong class="successStar">',
          "</strong>"
        );
      },
      shareHtml() {
        if (!this.level) {
          return "";
        }
        const shareText = encodeURIComponent(
          CoreHome.translate(
            "Tour_ShareAllChallengesCompleted",
            this.level.currentLevelName
          )
        );
        const url = encodeURIComponent("https://matomo.org");
        const shareUrl = `http://twitter.com/share?text=${shareText}&url=${url}`;
        return CoreHome.translate(
          "Tour_ShareYourAchievementOn",
          `<a target="_blank" rel="noreferrer noopener" href="${shareUrl}">Twitter</a>`
        );
      },
      superUserNoteHtml() {
        const faqUrl = "https://matomo.org/faq/general/faq_35/";
        return CoreHome.translate(
          "Tour_OnlyVisibleToSuperUser",
          CoreHome.externalLink(faqUrl),
          "</a>"
        );
      }
    },
    mounted() {
      this.fetchData();
      window.addEventListener("focus", this.onFocus);
    },
    beforeUnmount() {
      window.removeEventListener("focus", this.onFocus);
    },
    methods: {
      translate: CoreHome.translate,
      onFocus() {
        this.fetchData();
      },
      fetchData() {
        return __async(this, null, function* () {
          try {
            const [challenges, level] = yield Promise.all([
              CoreHome.AjaxHelper.fetch({
                method: "Tour.getChallenges"
              }),
              CoreHome.AjaxHelper.fetch({
                method: "Tour.getLevel"
              })
            ]);
            this.challenges = challenges;
            this.level = level;
            if (!this.loading) {
              return;
            }
            const firstIncomplete = challenges.findIndex(
              (c) => !c.isCompleted && !c.isSkipped
            );
            const done = firstIncomplete === -1 ? challenges.length : firstIncomplete;
            this.currentPage = Math.floor(done / PER_PAGE);
          } catch (e) {
          } finally {
            this.loading = false;
          }
        });
      },
      skipChallenge(id) {
        return __async(this, null, function* () {
          const challenge = this.challenges.find(
            (c) => c.id === id
          );
          if (challenge) {
            challenge.isSkipped = true;
          }
          try {
            yield CoreHome.AjaxHelper.post({
              method: "Tour.skipChallenge",
              id
            });
          } catch (e) {
            if (challenge) {
              challenge.isSkipped = false;
            }
          }
        });
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
  const _hoisted_1 = { class: "widgetBody tourEngagement" };
  const _hoisted_2 = { "aria-hidden": "true" };
  const _hoisted_3 = { key: 0 };
  const _hoisted_4 = { class: "completed" };
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = { key: 1 };
  const _hoisted_8 = { key: 0 };
  const _hoisted_9 = ["innerHTML"];
  const _hoisted_10 = ["title"];
  const _hoisted_11 = ["title"];
  const _hoisted_12 = ["title", "onClick"];
  const _hoisted_13 = ["href"];
  const _hoisted_14 = { style: { "text-align": "center", "padding-bottom": "0" } };
  const _hoisted_15 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityIndicator = vue.resolveComponent("ActivityIndicator");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      _ctx.loading ? (vue.openBlock(), vue.createBlock(_component_ActivityIndicator, {
        key: 0,
        loading: true
      })) : _ctx.level ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
        vue.createElementVNode("p", _hoisted_2, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.level.numLevelsTotal, (i) => {
            return vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: i }, [
              vue.createElementVNode("span", {
                class: vue.normalizeClass(["icon-star", _ctx.level.currentLevel >= i ? "successStar" : "upgradeStar"])
              }, null, 2),
              _cache[2] || (_cache[2] = vue.createTextVNode(" " + vue.toDisplayString(" "), -1))
            ], 64);
          }), 128))
        ]),
        _ctx.isCompleted ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
          vue.createElementVNode("p", null, [
            vue.createElementVNode("strong", _hoisted_4, vue.toDisplayString(_ctx.translate("Tour_CompletionTitle")), 1),
            vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Tour_CompletionMessage")) + " ", 1),
            _cache[3] || (_cache[3] = vue.createElementVNode("br", null, null, -1)),
            _cache[4] || (_cache[4] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.youCanCallYourselfHtml)
            }, null, 8, _hoisted_5),
            _cache[5] || (_cache[5] = vue.createElementVNode("br", null, null, -1)),
            _cache[6] || (_cache[6] = vue.createElementVNode("br", null, null, -1)),
            vue.createElementVNode("span", {
              innerHTML: _ctx.$sanitize(_ctx.shareHtml)
            }, null, 8, _hoisted_6)
          ])
        ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_7, [
          _ctx.level.description ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_8, vue.toDisplayString(_ctx.level.description), 1)) : vue.createCommentVNode("", true),
          vue.createElementVNode("p", {
            innerHTML: _ctx.$sanitize(_ctx.statusLevelHtml)
          }, null, 8, _hoisted_9),
          vue.createElementVNode("ul", null, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(_ctx.pagedChallenges, (challenge) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: challenge.id,
                class: vue.normalizeClass(["tourChallenge", challenge.id]),
                title: challenge.description
              }, [
                challenge.isCompleted || challenge.isSkipped ? (vue.openBlock(), vue.createElementBlock("span", {
                  key: 0,
                  class: "icon-ok",
                  title: _ctx.translate("Tour_ChallengeCompleted")
                }, null, 8, _hoisted_11)) : (vue.openBlock(), vue.createElementBlock("a", {
                  key: 1,
                  href: "javascript:void 0;",
                  class: "skip-challenge",
                  title: _ctx.translate("Tour_SkipThisChallenge"),
                  onClick: ($event) => _ctx.skipChallenge(challenge.id)
                }, [..._cache[7] || (_cache[7] = [
                  vue.createElementVNode("span", { class: "icon-hide" }, null, -1)
                ])], 8, _hoisted_12)),
                _cache[8] || (_cache[8] = vue.createTextVNode(" " + vue.toDisplayString(" ") + " ", -1)),
                _ctx.$sanitizeUrl(challenge.url) ? (vue.openBlock(), vue.createElementBlock("a", {
                  key: 2,
                  href: _ctx.$sanitizeUrl(challenge.url),
                  target: "_blank",
                  rel: "noreferrer noopener"
                }, vue.toDisplayString(challenge.name), 9, _hoisted_13)) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 3 }, [
                  vue.createTextVNode(vue.toDisplayString(challenge.name), 1)
                ], 64))
              ], 10, _hoisted_10);
            }), 128))
          ]),
          _cache[9] || (_cache[9] = vue.createElementVNode("hr", null, null, -1)),
          vue.createElementVNode("p", _hoisted_14, [
            _ctx.hasPrevPage ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 0,
              class: "previousChallenges",
              onClick: _cache[0] || (_cache[0] = ($event) => _ctx.currentPage -= 1)
            }, " ‹ " + vue.toDisplayString(_ctx.hasNextPage ? _ctx.translate("General_Previous") : _ctx.translate("Tour_PreviousChallenges")), 1)) : vue.createCommentVNode("", true),
            _ctx.hasPrevPage && _ctx.hasNextPage ? (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
              vue.createTextVNode(" | ")
            ], 64)) : vue.createCommentVNode("", true),
            _ctx.hasNextPage ? (vue.openBlock(), vue.createElementBlock("a", {
              key: 2,
              class: "nextChallenges",
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.currentPage += 1)
            }, vue.toDisplayString(_ctx.hasPrevPage ? _ctx.translate("General_Next") : _ctx.translate("Tour_NextChallenges")) + " › ", 1)) : vue.createCommentVNode("", true)
          ]),
          _cache[10] || (_cache[10] = vue.createElementVNode("hr", null, null, -1)),
          vue.createElementVNode("p", {
            class: "tourSuperUserNote",
            innerHTML: _ctx.$sanitize(_ctx.superUserNoteHtml)
          }, null, 8, _hoisted_15)
        ]))
      ], 64)) : vue.createCommentVNode("", true)
    ]);
  }
  const BecomeMatomoExpert = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.BecomeMatomoExpert = BecomeMatomoExpert;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
