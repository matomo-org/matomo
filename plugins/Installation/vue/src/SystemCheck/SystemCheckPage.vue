<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="translate('Installation_SystemCheck')" feature="true">
    <div class="alert alert-danger" v-if="hasErrors">
      <span v-html="$sanitize(thereWereErrorsText)"></span>
      {{ translate('Installation_SeeBelowForMoreInfo') }}
    </div>
    <div class="alert alert-warning" v-else-if="hasWarnings">
      {{ translate('Installation_SystemCheckSummaryThereWereWarnings') }}
      {{ translate('Installation_SeeBelowForMoreInfo') }}
    </div>
    <div class="alert alert-success" v-else>
      {{ translate('Installation_SystemCheckSummaryNoProblems') }}
    </div>

    <SystemCheckSection
      :error-type="errorType"
      :warning-type="warningType"
      :informational-type="informationalType"
      :system-check-info="systemCheckInfo"
      :mandatory-results="mandatoryResults"
      :optional-results="optionalResults"
      :informational-results="informationalResults"
      :is-installation="isInstallation"
    />
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, translate } from 'CoreHome';
import SystemCheckSection from './SystemCheckSection.vue';

export default defineComponent({
  props: {
    errorType: {
      type: String,
      required: true,
    },
    warningType: {
      type: String,
      required: true,
    },
    informationalType: {
      type: String,
      required: true,
    },
    systemCheckInfo: {
      type: String,
      required: true,
    },
    mandatoryResults: {
      type: Array,
      required: true,
    },
    optionalResults: {
      type: Array,
      required: true,
    },
    informationalResults: {
      type: Array,
      required: true,
    },
    isInstallation: Boolean,
    hasErrors: Boolean,
    hasWarnings: Boolean,
  },
  components: {
    ContentBlock,
    SystemCheckSection,
  },
  computed: {
    thereWereErrorsText() {
      return translate(
        'Installation_SystemCheckSummaryThereWereErrors',
        '<strong>',
        '</strong>',
        '<strong>',
        '</strong>',
      );
    },
  },
});
</script>
