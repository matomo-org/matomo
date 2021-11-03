<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div >
    <div v-if="!hide" class="trialHeader">
      <span>{{ translate(`Feedback_FeedbackTitle`) }} <i class="icon-heart red-text"></i></span>
      <a @click="showFeedbackForm=true" class="btn">{{ translate(`Feedback_Question${ question }`) }}</a>
      <a class="btn" @click="setCookieValue('hide')">Close</a>
    </div>
    <div
      class="ratefeature"
    >
      <MatomoDialog
        v-model="showFeedbackForm"
        @yes="sendFeedback()"
      >
        <div
          class="ui-confirm ratefeatureDialog"
        >
          <h2>{{ translate(`Feedback_Question${question}`) }}</h2>
          <p
            v-html="translate('Feedback_FeedbackSubtitle',`<i class='icon-heart red-text'></i>`)"></p>
          <br/>
          <div class="messageContainer">
            <textarea v-model="feedbackMessage"/>
          </div>
          <br/>
          <p
            v-html="translate('Feedback_Policy',`<a rel='nofollow' href='https://matomo.org/privacy-policy/' target='_blank'>`,'</a>')"></p>
          <input
            type="button"
            :value="translate('Feedback_SendFeedback')"
            role="yes"
          />
          <input
            type="button"
            role="cancel"
            :value="translate('General_Cancel')"
          />
        </div>
      </MatomoDialog>
      <MatomoDialog  v-model="feedbackDone">
        <div
          class="ui-confirm ratefeatureDialog"
        >
        <h2>{{ translate(`Feedback_ThankYou`) }}</h2>
        <p v-html="translate('Feedback_ThankYourForFeedback',`<i class='icon-heart red-text'></i>`)"></p>
        </div>
      </MatomoDialog>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoDialog, AjaxHelper } from 'CoreHome';
import ReviewLinks from '../ReviewLinks/ReviewLinks.vue';
const cookieName = "feedback-question";
export default defineComponent({

  components: {
    MatomoDialog,
    ReviewLinks,
  },
  data() {
    return {
      questionText: '',
      question: 0,
      hide: null,
      feedbackDone: false,
      expanded: false,
      showFeedbackForm: false,
      feedbackMessage: '',
    };
  },
  watch: {
    showFeedbackForm() {
      // eslint-disable-next-line no-underscore-dangle
      this.questionText = window._pk_translate(`Feedback_Question${this.question}`)
    },
  },
  created() {

    if (this.getCookieValue(cookieName) === 'hide') {
      this.hide = true;
    } else if (this.getCookieValue(cookieName)) {
      // eslint-disable-next-line radix
      this.question = parseInt(this.getCookieValue(cookieName));
      const nextQuestion = (this.question + 1 > 4) ? 0 : this.question + 1;
      this.setCookieValue(nextQuestion);
      this.hide = false;
    } else {
      this.setCookieValue(0);
      this.hide = false;
    }
  },
  methods: {
    getCookieValue() {
      const currentCookie = document.cookie.match(`(^|;)\\s*${cookieName}\\s*=\\s*([^;]+)`);
      return currentCookie ? currentCookie.pop() : null;
    },
    setCookieValue(value) {
      const now = new Date();
      const time = now.getTime();
      const expireTime = time + 1000 * 36000;
      now.setTime(expireTime);
      document.cookie = `${cookieName}=${value};expires=${now.toUTCString()};path=/`;
      this.hide = true;
    },
    sendFeedback() {
      AjaxHelper.fetch({
        method: 'Feedback.sendFeedbackForSurvey',
        question: this.questionText,
        message: this.feedbackMessage,
      });
      this.feedbackDone = true;
      this.setCookieValue('hide');
    },
  },
});
</script>
<style scoped>

.trialHeader {
  min-height: 48px;
  background-color: #263238;
  text-align: center;
  color: #fff;
}

.trialHeader span {
  vertical-align: sub;
  font-size: 14px;
}
.trialHeader a{
  margin-left: 16px;
  margin-top: 6px;
}
</style>
