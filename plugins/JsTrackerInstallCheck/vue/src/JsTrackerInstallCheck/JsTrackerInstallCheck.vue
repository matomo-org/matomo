<template>
    <li>{{ translate('JsTrackerInstallCheck_TestInstallationDescription') }}</li>
    <div class="jsTrackerInstallCheck">
      <div class="row testInstallFields">
        <div class="col s2">
          <Field
              uicontrol="url"
              name="baseUrl"
              placeholder="https://example.com"
              v-model="baseUrl"
              :full-width="true"
              :disabled="isTesting"
          />
        </div>
        <div class="col s10">
          <input type="button" class="btn testInstallBtn"
                 @click="initiateTrackerTest"
                 :disabled="!baseUrl || isTesting"
                 :value="translate('JsTrackerInstallCheck_TestInstallationBtnText')">
        </div>
      </div>
      <ActivityIndicator :loading="isTesting" :loadingMessage="translate('General_Testing')"/>
      <div class="system-success success-message"
           v-show="isTestSuccess">
        <span class="icon-ok"></span>
        {{ translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckSuccessMessage') }}
      </div>
      <div class="system-errors test-error" v-show="isTestComplete && !isTestSuccess">
        <span class="icon-warning"></span>&nbsp;
        <span v-html="$sanitize(getTestFailureMessage)"></span>
      </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  SiteRef, translate,
} from 'CoreHome';
import Field from '../../../../CorePluginsAdmin/vue/src/Field/Field.vue';

const MAX_NUM_API_CALLS = 10;
const TIME_BETWEEN_API_CALLS = 1000;

export default defineComponent({
  components: {
    Field,
    ActivityIndicator,
  },
  data() {
    return {
      checkNonce: '',
      isTesting: false,
      isTestComplete: false,
      isTestSuccess: false,
      testTimeoutCount: 0,
      baseUrl: '',
    };
  },
  props: {
    site: {
      type: Object,
      required: true,
    },
    isWordpress: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  created() {
    this.checkWhetherSuccessWasRecorded();
  },
  watch: {
    site() {
      this.onSiteChange();
    },
  },
  methods: {
    onSiteChange() {
      this.checkNonce = '';
      this.isTesting = false;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      this.checkWhetherSuccessWasRecorded();
    },
    initiateTrackerTest() {
      this.isTesting = true;
      this.isTestComplete = false;
      this.isTestSuccess = false;
      this.testTimeoutCount = 0;
      const siteRef = this.site as SiteRef;
      const postParams = { idSite: siteRef.id, url: '' };
      if (this.baseUrl) {
        postParams.url = this.baseUrl;
      }
      AjaxHelper.post(
        {
          module: 'API',
          method: 'JsTrackerInstallCheck.initiateJsTrackerInstallTest',
        },
        postParams,
      ).then((response) => {
        const isSuccess = response && response.url && response.nonce;
        if (isSuccess) {
          this.checkNonce = response.nonce;
          const windowRef = window.open(response.url);
          this.setCheckInTime();
          setTimeout(() => {
            if (windowRef && !windowRef.closed) {
              windowRef.close();
              // Set the timeout to the max since we've already waited too long
              this.testTimeoutCount = MAX_NUM_API_CALLS;
            }
          }, MAX_NUM_API_CALLS * TIME_BETWEEN_API_CALLS);
        }
      });
    },
    setCheckInTime() {
      setTimeout(this.checkWhetherSuccessWasRecorded, TIME_BETWEEN_API_CALLS);
    },
    checkWhetherSuccessWasRecorded() {
      const siteRef = this.site as SiteRef;
      const postParams = { idSite: siteRef.id, nonce: '' };
      if (this.checkNonce) {
        postParams.nonce = this.checkNonce;
      }
      AjaxHelper.post(
        {
          module: 'API',
          method: 'JsTrackerInstallCheck.wasJsTrackerInstallTestSuccessful',
        },
        postParams,
      ).then((response) => {
        if (response && response.mainUrl && !this.baseUrl) {
          this.baseUrl = response.mainUrl;
        }
        this.isTestSuccess = response && response.isSuccess;
        // If the test isn't successful but hasn't exceeded the timeout count, wait and check again
        if (this.checkNonce && !this.isTestSuccess && this.testTimeoutCount < MAX_NUM_API_CALLS) {
          this.testTimeoutCount += 1;
          this.setCheckInTime();
          return;
        }
        this.isTestComplete = !!this.checkNonce;
        this.isTesting = false;
      });
    },
  },
  computed: {
    getTestFailureMessage() {
      if (!this.isWordpress) {
        return translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessage');
      }

      return translate('JsTrackerInstallCheck_JsTrackingCodeInstallCheckFailureMessageWordpress',
        '<a target="_blank" rel="noreferrer noopener" href="https://wordpress.org/plugins/wp-piwik/">WP-Matomo Integration (WP-Piwik)</a>');
    },
  },
});
</script>
