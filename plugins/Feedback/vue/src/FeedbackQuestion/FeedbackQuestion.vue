<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-if="!isHidden" class="bannerHeader">
      <span>{{ translate(`Feedback_FeedbackTitle`) }} <i class="icon-heart red-text"></i></span>
      <a @click="showQuestion" class="btn">
        {{ translate(`Feedback_Question${question}`) }}
      </a>
      <a class="close-btn" @click="disableReminder">
        <i class="icon-close white-text"></i></a>
    </div>
    <div class="ratefeature">
      <MatomoDialog
        v-model="showFeedbackForm"
        @validation="sendFeedback()"
      >
        <div
          class="ui-confirm ratefeatureDialog"
        >
          <h2>{{ translate(`Feedback_Question${question}`) }}</h2>
          <p
            v-html="$sanitize(translate('Feedback_FeedbackSubtitle',
            `<i class='icon-heart red-text'></i>`))"></p>
          <br/>
          <div class="messageContainer">
            <div class="error-text" v-if="errorMessage">{{ errorMessage }}</div>
            <textarea id="message" :class="{'has-error':errorMessage}" v-model="feedbackMessage"/>
          </div>
          <br/>
          <p
            v-html="$sanitize(feedbackPolicy)"></p>
          <input
            type="button"
            role="validation"
            :value="translate('Feedback_SendFeedback')"
          />
          <input
            type="button"
            role="cancel"
            :value="translate('General_Cancel')"
          />
        </div>
      </MatomoDialog>
      <MatomoDialog v-model="feedbackDone">
        <div
          class="ui-confirm ratefeatureDialog"
        >
          <h2>{{ translate(`Feedback_ThankYou`) }}</h2>
          <p v-html="$sanitize(translate('Feedback_ThankYourForFeedback',
        `<i class='icon-heart red-text'></i>`))">
          </p>
          <input
            type="button"
            role="cancel"
            :value="translate('General_Close')"
          />
        </div>
      </MatomoDialog>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  MatomoDialog, AjaxHelper, setCookie, getCookie, translate,
} from 'CoreHome';

const { $ } = window;

interface FeedbackQuestionState {
  questionText: string;
  question: number;
  hide: null|boolean;
  feedbackDone: boolean;
  expanded: boolean;
  showFeedbackForm: boolean;
  feedbackMessage: string|null;
  errorMessage: string|null;
}

interface SendFeedbackForSurveyResponse {
  value: string;
}

const cookieName = 'feedback-question';
export default defineComponent({
  props: {
    showQuestionBanner: String,
  },
  components: {
    MatomoDialog,
  },
  computed: {
    isHidden() {
      if (this.showQuestionBanner === '0') {
        return true;
      }
      return !!this.hide;
    },
    feedbackPolicy() {
      return translate(
        'Feedback_Policy',
        '<a rel="nofollow" href="https://matomo.org/privacy-policy/" target="_blank">',
        '</a>',
      );
    },
  },
  data(): FeedbackQuestionState {
    return {
      questionText: '',
      question: 0,
      hide: null,
      feedbackDone: false,
      expanded: false,
      showFeedbackForm: false,
      feedbackMessage: null,
      errorMessage: null,
    };
  },
  watch: {
    showFeedbackForm(val) {
      // eslint-disable-next-line no-underscore-dangle
      this.questionText = translate(`Feedback_Question${this.question}`);
      if (val) {
        setInterval(() => {
          $('#message').focus();
        }, 500);
      }
    },
  },
  created() {
    if (this.showQuestionBanner !== '0') {
      this.initQuestion();
    }
  },
  methods: {
    initQuestion() {
      if (!getCookie(cookieName)) {
        this.question = this.getRandomIntBetween(0, 4);
      } else {
        // eslint-disable-next-line radix
        this.question = parseInt(getCookie(cookieName)!);
      }

      const nextQuestion = (this.question + 1) % 4;
      const sevenDays = 7 * 60 * 60 * 24 * 1000;
      setCookie(cookieName, `${nextQuestion}`, sevenDays);
    },
    getRandomIntBetween(min: number, max: number) {
      // eslint-disable-next-line no-param-reassign
      min = Math.ceil(min);
      // eslint-disable-next-line no-param-reassign
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min + 1) + min);
    },
    showQuestion() {
      this.showFeedbackForm = true;
      this.errorMessage = null;
    },
    disableReminder() {
      AjaxHelper.fetch({
        method: 'Feedback.updateFeedbackReminderDate',
      });
      this.hide = true;
    },
    sendFeedback() {
      this.errorMessage = null;
      AjaxHelper.fetch({
        method: 'Feedback.sendFeedbackForSurvey',
        question: this.questionText,
        message: this.feedbackMessage,
      }).then((res: SendFeedbackForSurveyResponse) => {
        if (res.value === 'success') {
          this.showFeedbackForm = false;
          this.feedbackDone = true;
          this.hide = true;
        } else {
          this.errorMessage = res.value;
        }
      });
    },
  },
});
</script>
