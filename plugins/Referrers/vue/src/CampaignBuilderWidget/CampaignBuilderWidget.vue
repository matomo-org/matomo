<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="widgetBody">
    <p v-html="$sanitize(introHtml)" />
    <CampaignBuilder :has-extra-plugin="hasExtraPlugin" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from 'CoreHome';
import CampaignBuilder from '../CampaignBuilder/CampaignBuilder.vue';

const docsBuilderUrl = 'https://matomo.org/docs/'
  + 'tracking-campaigns-url-builder/';
const docsCampaignsUrl = 'https://matomo.org/docs/'
  + 'tracking-campaigns/';

export default defineComponent({
  components: {
    CampaignBuilder,
  },
  props: {
    hasExtraPlugin: Boolean,
  },
  computed: {
    introHtml(): string {
      /* eslint-disable prefer-template */
      const link1 = '<a target="_blank"'
        + ' rel="noreferrer noopener"'
        + ' href="' + docsBuilderUrl + '">';
      const link2 = '<a target="_blank"'
        + ' rel="noreferrer noopener"'
        + ' href="' + docsCampaignsUrl + '">';
      /* eslint-enable prefer-template */
      return translate(
        'Referrers_URLCampaignBuilderIntro',
        link1,
        '</a>',
        link2,
        '</a>',
      );
    },
  },
});
</script>
