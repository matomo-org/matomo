<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline>{{ translate('DBStats_DatabaseUsage') }}</EnrichedHeadline>
    </h2>
    <p>
      {{ translate('DBStats_MainDescription', totalSpaceUsed) }}<br/>
      <span v-html="$sanitize(learnMoreText)"></span>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentIntro,
  EnrichedHeadline,
  translate,
  externalRawLink,
} from 'CoreHome';

export default defineComponent({
  props: {
    totalSpaceUsed: {
      type: [String, Number],
      required: true,
    },
  },
  components: {
    EnrichedHeadline,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    learnMoreText() {
      const link = externalRawLink('https://matomo.org/docs/setup-auto-archiving/');
      return translate(
        'DBStats_LearnMore',
        `<a target="_blank" rel="noreferrer noopener" href="${link}">Matomo Auto Archiving</a>`,
      );
    },
  },
});
</script>
