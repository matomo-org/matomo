<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div v-if="!showNextStep">
      <SystemCheckLegend
        :url="systemCheckLegendUrl"
      />

      <br style="clear:both;"/>
    </div>

    <h2>{{ translate('Installation_SystemCheck') }}</h2>

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

    <div v-if="!showNextStep">
      <p v-if="!showNextStep">
        <span class="icon-export"></span>
        <a target="_blank" rel="noreferrer noopener" href="{{ externalRawLink('https://matomo.org/docs/requirements/') }}">
          {{ translate('Installation_Requirements') }}
        </a>
      </p>

      <SystemCheckLegend
        :url="systemCheckLegendUrl"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SystemCheckLegend from './SystemCheckLegend.vue';
import SystemCheckSection from './SystemCheckSection.vue';

const { $ } = window;

export default defineComponent({
  props: {
    showNextStep: Boolean,
    systemCheckLegendUrl: {
      type: String,
      required: true,
    },
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
  },
  components: {
    SystemCheckSection,
    SystemCheckLegend,
  },
  mounted() {
    // client-side test for https to handle the case where the server is behind a reverse proxy
    if (document.location.protocol === 'https:') {
      const link = $('p.next-step a');
      link.attr('href', `${link.attr('href')}&clientProtocol=https`);
    }
  },
});
</script>
