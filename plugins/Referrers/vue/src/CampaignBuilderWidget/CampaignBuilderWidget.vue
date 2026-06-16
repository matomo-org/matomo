<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    v-if="!isWidget"
    :content-title="translate('Referrers_URLCampaignBuilder')"
  >
    <div class="widgetBody">
      <p v-html="$sanitize(introHtml)" />
      <CampaignBuilder :has-extra-plugin="hasExtraPlugin" />
    </div>
  </ContentBlock>

  <div
    v-else
    class="widgetBody"
  >
    <p v-html="$sanitize(introHtml)" />
    <CampaignBuilder :has-extra-plugin="hasExtraPlugin" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, externalLink, ContentBlock } from 'CoreHome';
import CampaignBuilder from '../CampaignBuilder/CampaignBuilder.vue';

const docsBuilderUrl = 'https://matomo.org/docs/'
  + 'tracking-campaigns-url-builder/';
const docsCampaignsUrl = 'https://matomo.org/docs/'
  + 'tracking-campaigns/';

export default defineComponent({
  components: {
    ContentBlock,
    CampaignBuilder,
  },
  props: {
    hasExtraPlugin: Boolean,
    isWidget: Boolean,
  },
  computed: {
    introHtml(): string {
      return translate(
        'Referrers_URLCampaignBuilderIntro',
        externalLink(docsBuilderUrl),
        '</a>',
        externalLink(docsCampaignsUrl),
        '</a>',
      );
    },
  },
});
</script>
