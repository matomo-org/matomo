(function(global, factory) {
  typeof exports === "object" && typeof module !== "undefined" ? factory(exports, require("vue"), require("CoreHome")) : typeof define === "function" && define.amd ? define(["exports", "vue", "CoreHome"], factory) : (global = typeof globalThis !== "undefined" ? globalThis : global || self, factory(global.Feedback = {}, global.Vue, global.CoreHome));
})(this, (function(exports2, vue, CoreHome) {
  "use strict";
  const _sfc_main$2 = vue.defineComponent({});
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _hoisted_1$2 = { class: "requestReview" };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createElementVNode("p", null, vue.toDisplayString(_ctx.translate("Feedback_PleaseLeaveExternalReviewForMatomo")), 1),
      _cache[0] || (_cache[0] = vue.createStaticVNode('<br><br><div class="review-links"><div class="review-link"><a href="https://www.softwarereviews.com/6g24l" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/softwarereviews.svg"></div><div class="link">Software Reviews</div></a></div><div class="review-link"><a href="https://www.capterra.com/p/182627/Matomo-Analytics/" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/capterra.svg"></div><div class="link">Capterra</div></a></div><div class="review-link"><a href="https://www.g2crowd.com/products/matomo-formerly-piwik/details" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/g2crowd.svg"></div><div class="link">G2 Crowd</div></a></div><div class="review-link"><a href="https://www.producthunt.com/posts/matomo-2" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/producthunt.svg"></div><div class="link">Product Hunt</div></a></div><div class="review-link"><a href="https://www.saasworthy.com/product/matomo" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/saasworthy.png"></div><div class="link">SaaSworthy</div></a></div><div class="review-link"><a href="https://www.trustradius.com/products/matomo/reviews" target="_blank"><div class="image"><img loading="lazy" src="plugins/Feedback/images/trustradius.svg"></div><div class="link">TrustRadius</div></a></div></div>', 3))
    ]);
  }
  const ReviewLinks = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = vue.defineComponent({
    props: {
      title: String
    },
    components: {
      MatomoDialog: CoreHome.MatomoDialog,
      ReviewLinks
    },
    data() {
      return {
        like: false,
        likeReason: null,
        dislikeReason: null,
        ratingDone: false,
        expanded: false,
        showFeedbackForm: false,
        feedbackMessage: "",
        errorMessage: null
      };
    },
    watch: {
      likeReason: "doFocusInput",
      dislikeReason: "doFocusInput"
    },
    methods: {
      dislikeFeature() {
        this.ratingDone = false;
        this.like = false;
        this.showFeedbackForm = true;
        this.errorMessage = null;
        this.likeReason = null;
        this.dislikeReason = null;
        this.feedbackMessage = "";
      },
      likeFeature() {
        this.ratingDone = false;
        this.like = true;
        this.showFeedbackForm = true;
        this.errorMessage = null;
        this.likeReason = null;
        this.dislikeReason = null;
        this.feedbackMessage = "";
      },
      doFocusInput() {
        this.$nextTick(() => {
          this.focusInput();
        });
      },
      focusInput() {
        if (this.$refs.feedbackText != null) {
          this.$refs.feedbackText.focus();
        }
      },
      sendFeedback() {
        this.errorMessage = null;
        CoreHome.AjaxHelper.fetch({
          method: "Feedback.sendFeedbackForFeature",
          featureName: this.title,
          like: this.like ? 1 : 0,
          choice: this.like ? this.likeReason : this.dislikeReason,
          message: this.feedbackMessage
        }).then((res) => {
          if (res.value === "success") {
            this.showFeedbackForm = false;
            this.ratingDone = true;
            this.feedbackMessage = "";
          } else {
            this.errorMessage = res.value;
          }
        });
      },
      htmlEntities(v) {
        return CoreHome.Matomo.helper.htmlEntities(v);
      }
    }
  });
  const _hoisted_1$1 = ["title"];
  const _hoisted_2$1 = { class: "ui-confirm ratefeatureDialog" };
  const _hoisted_3$1 = { key: 0 };
  const _hoisted_4$1 = { key: 0 };
  const _hoisted_5$1 = { key: 1 };
  const _hoisted_6$1 = { class: "row" };
  const _hoisted_7$1 = { style: { "text-align": "left", "margin-top": "16px" } };
  const _hoisted_8$1 = {
    for: "useful",
    class: "ratelabel"
  };
  const _hoisted_9$1 = {
    for: "easy",
    class: "ratelabel"
  };
  const _hoisted_10$1 = {
    for: "configurable",
    class: "ratelabel"
  };
  const _hoisted_11$1 = {
    for: "likeother",
    class: "ratelabel"
  };
  const _hoisted_12$1 = { key: 1 };
  const _hoisted_13 = { key: 0 };
  const _hoisted_14 = { key: 1 };
  const _hoisted_15 = { class: "row" };
  const _hoisted_16 = { style: { "text-align": "left" } };
  const _hoisted_17 = {
    for: "missingfeatures",
    class: "ratelabel"
  };
  const _hoisted_18 = {
    for: "makeeasier",
    class: "ratelabel"
  };
  const _hoisted_19 = {
    for: "speedup",
    class: "ratelabel"
  };
  const _hoisted_20 = {
    for: "fixbugs",
    class: "ratelabel"
  };
  const _hoisted_21 = {
    for: "dislikeother",
    class: "ratelabel"
  };
  const _hoisted_22 = {
    key: 2,
    class: "messageContainer",
    style: { "text-align": "left" }
  };
  const _hoisted_23 = { key: 0 };
  const _hoisted_24 = { key: 1 };
  const _hoisted_25 = { key: 2 };
  const _hoisted_26 = { key: 3 };
  const _hoisted_27 = { key: 4 };
  const _hoisted_28 = { key: 5 };
  const _hoisted_29 = { key: 6 };
  const _hoisted_30 = { key: 7 };
  const _hoisted_31 = { key: 8 };
  const _hoisted_32 = {
    key: 9,
    class: "error-text"
  };
  const _hoisted_33 = ["innerHTML"];
  const _hoisted_34 = ["title", "value"];
  const _hoisted_35 = ["value"];
  const _hoisted_36 = { class: "ui-confirm ratefeatureDialog" };
  const _hoisted_37 = ["innerHTML"];
  const _hoisted_38 = { key: 0 };
  const _hoisted_39 = { key: 1 };
  const _hoisted_40 = ["value"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoDialog = vue.resolveComponent("MatomoDialog");
    const _component_ReviewLinks = vue.resolveComponent("ReviewLinks");
    return vue.openBlock(), vue.createElementBlock("div", {
      title: _ctx.translate("Feedback_RateFeatureTitle", _ctx.htmlEntities(_ctx.title || "")),
      class: "ratefeature"
    }, [
      vue.createElementVNode("div", {
        class: "iconContainer",
        onMouseenter: _cache[2] || (_cache[2] = ($event) => _ctx.expanded = true),
        onMouseleave: _cache[3] || (_cache[3] = ($event) => _ctx.expanded = false)
      }, [
        vue.createElementVNode("img", {
          onClick: _cache[0] || (_cache[0] = ($event) => {
            _ctx.likeFeature();
          }),
          class: "like-icon",
          src: "plugins/Feedback/vue/src/RateFeature/thumbs-up.png"
        }),
        vue.createElementVNode("img", {
          onClick: _cache[1] || (_cache[1] = ($event) => {
            _ctx.dislikeFeature();
          }),
          class: "dislike-icon",
          src: "plugins/Feedback/vue/src/RateFeature/thumbs-down.png"
        })
      ], 32),
      vue.createVNode(_component_MatomoDialog, {
        modelValue: _ctx.showFeedbackForm,
        "onUpdate:modelValue": _cache[14] || (_cache[14] = ($event) => _ctx.showFeedbackForm = $event),
        onYes: _cache[15] || (_cache[15] = ($event) => _ctx.sendFeedback()),
        onValidation: _cache[16] || (_cache[16] = ($event) => _ctx.sendFeedback())
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_2$1, [
            _ctx.like ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, [
              _ctx.title ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_4$1, vue.toDisplayString(_ctx.translate(
                "Feedback_RateFeatureLeaveMessageLikeNamedFeature",
                _ctx.title
              )), 1)) : vue.createCommentVNode("", true),
              !_ctx.title ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_5$1, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageLike")), 1)) : vue.createCommentVNode("", true),
              _cache[21] || (_cache[21] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("div", _hoisted_6$1, [
                vue.createElementVNode("div", _hoisted_7$1, [
                  vue.createElementVNode("label", _hoisted_8$1, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "useful",
                      value: "useful",
                      "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.likeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.likeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureUsefulInfo")), 1)
                  ]),
                  _cache[18] || (_cache[18] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_9$1, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "easy",
                      value: "easy",
                      "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.likeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.likeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureEasyToUse")), 1)
                  ]),
                  _cache[19] || (_cache[19] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_10$1, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "configurable",
                      value: "configurable",
                      "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => _ctx.likeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.likeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureConfigurable")), 1)
                  ]),
                  _cache[20] || (_cache[20] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_11$1, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "likeother",
                      value: "likeother",
                      "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => _ctx.likeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.likeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureOtherReason")), 1)
                  ])
                ])
              ])
            ])) : vue.createCommentVNode("", true),
            !_ctx.like ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_12$1, [
              _ctx.title ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_13, vue.toDisplayString(_ctx.translate(
                "Feedback_RateFeatureLeaveMessageDislikeNamedFeature",
                _ctx.title
              )), 1)) : vue.createCommentVNode("", true),
              !_ctx.title ? (vue.openBlock(), vue.createElementBlock("h2", _hoisted_14, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislike")), 1)) : vue.createCommentVNode("", true),
              _cache[27] || (_cache[27] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("div", _hoisted_15, [
                vue.createElementVNode("div", _hoisted_16, [
                  vue.createElementVNode("label", _hoisted_17, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "missingfeatures",
                      value: "missingfeatures",
                      "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => _ctx.dislikeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.dislikeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureDislikeAddMissingFeatures")), 1)
                  ]),
                  _cache[22] || (_cache[22] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_18, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "makeeasier",
                      value: "makeeasier",
                      "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => _ctx.dislikeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.dislikeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureDislikeMakeEasier")), 1)
                  ]),
                  _cache[23] || (_cache[23] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_19, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "speedup",
                      value: "speedup",
                      "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => _ctx.dislikeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.dislikeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureDislikeSpeedUp")), 1)
                  ]),
                  _cache[24] || (_cache[24] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_20, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "fixbugs",
                      value: "fixbugs",
                      "onUpdate:modelValue": _cache[11] || (_cache[11] = ($event) => _ctx.dislikeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.dislikeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureDislikeFixBugs")), 1)
                  ]),
                  _cache[25] || (_cache[25] = vue.createElementVNode("br", null, null, -1)),
                  vue.createElementVNode("label", _hoisted_21, [
                    vue.withDirectives(vue.createElementVNode("input", {
                      type: "radio",
                      id: "dislikeother",
                      value: "dislikeother",
                      "onUpdate:modelValue": _cache[12] || (_cache[12] = ($event) => _ctx.dislikeReason = $event),
                      class: "rateradio"
                    }, null, 512), [
                      [vue.vModelRadio, _ctx.dislikeReason]
                    ]),
                    vue.createTextVNode(" " + vue.toDisplayString(_ctx.translate("Feedback_RateFeatureOtherReason")), 1)
                  ]),
                  _cache[26] || (_cache[26] = vue.createElementVNode("br", null, null, -1))
                ])
              ])
            ])) : vue.createCommentVNode("", true),
            _ctx.likeReason || _ctx.dislikeReason ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_22, [
              _ctx.likeReason && _ctx.likeReason === "useful" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_23, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageLikeExtraUseful")), 1)) : vue.createCommentVNode("", true),
              _ctx.likeReason && _ctx.likeReason === "easy" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_24, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageLikeExtraEasy")), 1)) : vue.createCommentVNode("", true),
              _ctx.likeReason && _ctx.likeReason === "configurable" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_25, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageLikeExtraConfigurable")), 1)) : vue.createCommentVNode("", true),
              _ctx.likeReason && _ctx.likeReason === "likeother" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_26, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageLikeExtra")), 1)) : vue.createCommentVNode("", true),
              _ctx.dislikeReason && _ctx.dislikeReason === "missingfeatures" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_27, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislikeExtraMissing")), 1)) : vue.createCommentVNode("", true),
              _ctx.dislikeReason && _ctx.dislikeReason === "makeeasier" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_28, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislikeExtraEasier")), 1)) : vue.createCommentVNode("", true),
              _ctx.dislikeReason && _ctx.dislikeReason === "fixbugs" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_29, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislikeExtraBugs")), 1)) : vue.createCommentVNode("", true),
              _ctx.dislikeReason && _ctx.dislikeReason === "speedup" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_30, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislikeExtraSpeed")), 1)) : vue.createCommentVNode("", true),
              _ctx.dislikeReason && _ctx.dislikeReason === "dislikeother" ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_31, vue.toDisplayString(_ctx.translate("Feedback_RateFeatureLeaveMessageDislikeExtra")), 1)) : vue.createCommentVNode("", true),
              _ctx.errorMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_32, vue.toDisplayString(_ctx.errorMessage), 1)) : vue.createCommentVNode("", true),
              vue.withDirectives(vue.createElementVNode("textarea", {
                ref: "feedbackText",
                class: vue.normalizeClass(["materialize-textarea", { "has-error": _ctx.errorMessage }]),
                id: "feedbacktext",
                "onUpdate:modelValue": _cache[13] || (_cache[13] = ($event) => _ctx.feedbackMessage = $event)
              }, null, 2), [
                [vue.vModelText, _ctx.feedbackMessage]
              ]),
              _ctx.likeReason || _ctx.dislikeReason ? (vue.openBlock(), vue.createElementBlock("p", {
                key: 10,
                innerHTML: _ctx.$sanitize(_ctx.translate("Feedback_Policy", _ctx.externalLink("https://matomo.org/privacy-policy/"), "</a>"))
              }, null, 8, _hoisted_33)) : vue.createCommentVNode("", true)
            ])) : vue.createCommentVNode("", true),
            vue.createElementVNode("input", {
              class: "btn",
              type: "button",
              role: "validation",
              title: _ctx.translate("Feedback_RateFeatureSendFeedbackInformation"),
              value: _ctx.translate("Feedback_SendFeedback")
            }, null, 8, _hoisted_34),
            vue.createElementVNode("input", {
              type: "button",
              role: "cancel",
              value: _ctx.translate("General_Cancel")
            }, null, 8, _hoisted_35)
          ])
        ]),
        _: 1
      }, 8, ["modelValue"]),
      vue.createVNode(_component_MatomoDialog, {
        modelValue: _ctx.ratingDone,
        "onUpdate:modelValue": _cache[17] || (_cache[17] = ($event) => _ctx.ratingDone = $event)
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_36, [
            vue.createElementVNode("h2", {
              innerHTML: _ctx.$sanitize(_ctx.translate(
                "Feedback_ThankYouHeart",
                `<i class='icon-heart red-text'></i>`
              ))
            }, null, 8, _hoisted_37),
            _ctx.like ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_38, [
              vue.createVNode(_component_ReviewLinks)
            ])) : vue.createCommentVNode("", true),
            !_ctx.like ? (vue.openBlock(), vue.createElementBlock("p", _hoisted_39, vue.toDisplayString(_ctx.translate("Feedback_AppreciateFeedback")), 1)) : vue.createCommentVNode("", true),
            vue.createElementVNode("input", {
              type: "button",
              value: _ctx.translate("General_Close"),
              role: "yes"
            }, null, 8, _hoisted_40)
          ])
        ]),
        _: 1
      }, 8, ["modelValue"])
    ], 8, _hoisted_1$1);
  }
  const RateFeature = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const { $ } = window;
  const cookieName = "feedback-question";
  const _sfc_main = vue.defineComponent({
    props: {
      showQuestionBanner: Boolean
    },
    components: {
      MatomoDialog: CoreHome.MatomoDialog
    },
    computed: {
      isHidden() {
        if (!this.showQuestionBanner) {
          return true;
        }
        return !!this.hide;
      },
      feedbackPolicy() {
        return CoreHome.translate(
          "Feedback_Policy",
          /* eslint-disable prefer-template */
          CoreHome.externalLink("https://matomo.org/privacy-policy/"),
          "</a>"
        );
      }
    },
    data() {
      return {
        questionText: "",
        question: 0,
        hide: null,
        feedbackDone: false,
        expanded: false,
        showFeedbackForm: false,
        feedbackMessage: null,
        errorMessage: null
      };
    },
    watch: {
      showFeedbackForm(val) {
        this.questionText = CoreHome.translate(`Feedback_Question${this.question}`);
        if (val) {
          setInterval(() => {
            $("#message").focus();
          }, 500);
        }
      }
    },
    created() {
      if (this.showQuestionBanner) {
        this.initQuestion();
      }
    },
    methods: {
      initQuestion() {
        if (!CoreHome.getCookie(cookieName)) {
          this.question = this.getRandomIntBetween(0, 4);
        } else {
          this.question = parseInt(CoreHome.getCookie(cookieName));
        }
        const nextQuestion = (this.question + 1) % 4;
        const sevenDays = 7 * 60 * 60 * 24 * 1e3;
        CoreHome.setCookie(cookieName, `${nextQuestion}`, sevenDays);
      },
      getRandomIntBetween(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1) + min);
      },
      showQuestion() {
        this.showFeedbackForm = true;
        this.errorMessage = null;
      },
      disableReminder() {
        CoreHome.AjaxHelper.fetch({
          method: "Feedback.updateFeedbackReminderDate"
        });
        this.hide = true;
      },
      sendFeedback() {
        this.errorMessage = null;
        CoreHome.AjaxHelper.fetch({
          method: "Feedback.sendFeedbackForSurvey",
          question: this.questionText,
          message: this.feedbackMessage
        }).then((res) => {
          if (res.value === "success") {
            this.showFeedbackForm = false;
            this.feedbackDone = true;
            this.hide = true;
          } else {
            this.errorMessage = res.value;
          }
        });
      }
    }
  });
  const _hoisted_1 = {
    key: 0,
    class: "bannerHeader"
  };
  const _hoisted_2 = { class: "ratefeature" };
  const _hoisted_3 = { class: "ui-confirm ratefeatureDialog" };
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = { class: "messageContainer" };
  const _hoisted_6 = {
    key: 0,
    class: "error-text"
  };
  const _hoisted_7 = ["innerHTML"];
  const _hoisted_8 = ["value"];
  const _hoisted_9 = ["value"];
  const _hoisted_10 = { class: "ui-confirm ratefeatureDialog" };
  const _hoisted_11 = ["innerHTML"];
  const _hoisted_12 = ["value"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_MatomoDialog = vue.resolveComponent("MatomoDialog");
    return vue.openBlock(), vue.createElementBlock("div", null, [
      !_ctx.isHidden ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
        vue.createElementVNode("span", null, [
          vue.createTextVNode(vue.toDisplayString(_ctx.translate(`Feedback_FeedbackTitle`)) + " ", 1),
          _cache[6] || (_cache[6] = vue.createElementVNode("i", { class: "icon-heart red-text" }, null, -1))
        ]),
        vue.createElementVNode("a", {
          onClick: _cache[0] || (_cache[0] = (...args) => _ctx.showQuestion && _ctx.showQuestion(...args)),
          class: "btn"
        }, vue.toDisplayString(_ctx.translate(`Feedback_Question${_ctx.question}`)), 1),
        vue.createElementVNode("a", {
          class: "close-btn",
          onClick: _cache[1] || (_cache[1] = (...args) => _ctx.disableReminder && _ctx.disableReminder(...args))
        }, [..._cache[7] || (_cache[7] = [
          vue.createElementVNode("i", { class: "icon-close white-text" }, null, -1)
        ])])
      ])) : vue.createCommentVNode("", true),
      vue.createElementVNode("div", _hoisted_2, [
        vue.createVNode(_component_MatomoDialog, {
          modelValue: _ctx.showFeedbackForm,
          "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => _ctx.showFeedbackForm = $event),
          onValidation: _cache[4] || (_cache[4] = ($event) => _ctx.sendFeedback())
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_3, [
              vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate(`Feedback_Question${_ctx.question}`)), 1),
              vue.createElementVNode("p", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "Feedback_FeedbackSubtitle",
                  `<i class='icon-heart red-text'></i>`
                ))
              }, null, 8, _hoisted_4),
              _cache[8] || (_cache[8] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("div", _hoisted_5, [
                _ctx.errorMessage ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, vue.toDisplayString(_ctx.errorMessage), 1)) : vue.createCommentVNode("", true),
                vue.withDirectives(vue.createElementVNode("textarea", {
                  id: "message",
                  class: vue.normalizeClass({ "has-error": _ctx.errorMessage }),
                  "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.feedbackMessage = $event)
                }, null, 2), [
                  [vue.vModelText, _ctx.feedbackMessage]
                ])
              ]),
              _cache[9] || (_cache[9] = vue.createElementVNode("br", null, null, -1)),
              vue.createElementVNode("p", {
                innerHTML: _ctx.$sanitize(_ctx.feedbackPolicy)
              }, null, 8, _hoisted_7),
              vue.createElementVNode("input", {
                type: "button",
                role: "validation",
                value: _ctx.translate("Feedback_SendFeedback")
              }, null, 8, _hoisted_8),
              vue.createElementVNode("input", {
                type: "button",
                role: "cancel",
                value: _ctx.translate("General_Cancel")
              }, null, 8, _hoisted_9)
            ])
          ]),
          _: 1
        }, 8, ["modelValue"]),
        vue.createVNode(_component_MatomoDialog, {
          modelValue: _ctx.feedbackDone,
          "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => _ctx.feedbackDone = $event)
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_10, [
              vue.createElementVNode("h2", null, vue.toDisplayString(_ctx.translate(`Feedback_ThankYou`)), 1),
              vue.createElementVNode("p", {
                innerHTML: _ctx.$sanitize(_ctx.translate(
                  "Feedback_ThankYourForFeedback",
                  `<i class='icon-heart red-text'></i>`
                ))
              }, null, 8, _hoisted_11),
              vue.createElementVNode("input", {
                type: "button",
                role: "cancel",
                value: _ctx.translate("General_Close")
              }, null, 8, _hoisted_12)
            ])
          ]),
          _: 1
        }, 8, ["modelValue"])
      ])
    ]);
  }
  const FeedbackQuestion = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  exports2.FeedbackQuestion = FeedbackQuestion;
  exports2.RateFeature = RateFeature;
  exports2.ReviewLinks = ReviewLinks;
  Object.defineProperty(exports2, Symbol.toStringTag, { value: "Module" });
}));
