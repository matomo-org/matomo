<template>
    <li>{{ translate('CoreHome_TestInstallationDescription') }}</li>
    <div class="jsTrackerInstallCheck">
      <span
          class="btn testInstallBtn"
          v-show="!isTesting && !isTestSuccess"
          @click="initiateTrackerTest">
        {{ translate('CoreHome_TestInstallationBtnText') }}
      </span>
      <ActivityIndicator :loading="isTesting" :loadingMessage="translate('General_Testing')"/>
      <div class="system-success success-message"
           v-show="isTestSuccess">
        <span class="icon-ok"></span>
        {{ translate('CoreHome_JsTrackingCodeInstallCheckSuccessMessage') }}
      </div>
      <div class="system-errors test-error" v-show="isTestComplete && !isTestSuccess">
        <span class="icon-warning"></span>
        {{ translate('CoreHome_JsTrackingCodeInstallCheckFailureMessage') }}
      </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import SiteRef from '../SiteSelector/SiteRef';

export default defineComponent({
  components: {
    ActivityIndicator,
  },
  data() {
    return {
      checkNonce: '',
      isTesting: false,
      isTestComplete: false,
      isTestSuccess: false,
      testTimeoutCount: 0,
    };
  },
  props: {
    site: {
      type: Object,
      required: true,
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
      AjaxHelper.post(
        {
          module: 'API',
          method: 'JsTrackerInstallCheck.initiateJsTrackerInstallTest',
        },
        { idSite: siteRef.id },
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
              this.testTimeoutCount = 10;
            }
          }, 10000);
        }
      });
    },
    setCheckInTime() {
      setTimeout(this.checkWhetherSuccessWasRecorded, 1000);
    },
    checkWhetherSuccessWasRecorded() {
      const siteRef = this.site as SiteRef;
      const methodName = this.checkNonce ? 'checkForJsTrackerInstallTestSuccess' : 'getJsTrackerInstallTestResult';
      const postParams = { idSite: siteRef.id, nonce: this.checkNonce };
      if (this.checkNonce) {
        postParams.nonce = this.checkNonce;
      }
      AjaxHelper.post(
        {
          module: 'API',
          method: `JsTrackerInstallCheck.${methodName}`,
        },
        postParams,
      ).then((response) => {
        this.isTestSuccess = response && response.isSuccess;
        // If the test isn't successful but hasn't exceeded the timeout count, wait and check again
        if (this.checkNonce && !this.isTestSuccess && this.testTimeoutCount < 10) {
          this.testTimeoutCount += 1;
          this.setCheckInTime();
          return;
        }
        this.isTestComplete = !!this.checkNonce;
        this.isTesting = false;
      });
    },
  },
});
</script>
