<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock>
    <div class="form-group row">
      <div class="col s12 m6">
        <p>{{ translate('JsTrackerInstallCheck_EnterSiteUrl') }}</p>
        <Field
          uicontrol="url"
          title="Site URL"
          v-model="inputUrl"
          placeholder="https://example.org"
        >
        </Field>
        <button type="button" id="runTestBtn" class="btn btn-small" v-on:click="openTestWindow">
          {{ translate('JsTrackerInstallCheck_StartTestBtnText') }}</button>
      </div>
      <div class="col s12 m6">
        <div class="form-help">
          <p>{{ translate('JsTrackerInstallCheck_TestHelpText') }}</p>
        </div>
      </div>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper, NotificationsStore, translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import ContentBlock from '../../../CoreHome/vue/src/ContentBlock/ContentBlock.vue';

export default defineComponent({
  components: { ContentBlock, Field },
  data() {
    return {
      inputUrl: '',
      numberOfChecks: 0,
    };
  },
  props: {
    checkNonce: {
      type: String,
      required: true,
    },
  },
  methods: {
    openTestWindow() {
      const url = `${this.inputUrl}?tracker_install_check=${this.checkNonce}`;
      window.open(url);
      this.setCheckInTime();
    },
    setCheckInTime() {
      setTimeout(this.checkWhetherSuccessWasRecorded, 5000);
    },
    checkWhetherSuccessWasRecorded() {
      AjaxHelper.post(
        {
          module: 'API',
          method: 'JsTrackerInstallCheck.checkForJsTrackerInstallTestSuccess',
        },
        { nonce: this.checkNonce },
      ).then((response) => {
        let notificationMessage = translate('JsTrackerInstallCheck_TestFailureMessage');
        const isSuccess = response && response.isSuccess;
        if (isSuccess) {
          notificationMessage = translate('JsTrackerInstallCheck_TestSuccessMessage');
        }

        NotificationsStore.show({
          context: isSuccess ? 'success' : 'warning',
          id: 'JsTrackerInstallCheckResult',
          type: 'transient',
          message: notificationMessage,
        });
      });
    },
  },
});
</script>
