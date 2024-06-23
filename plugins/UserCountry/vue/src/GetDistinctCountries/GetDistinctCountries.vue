<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock>
    <div class="sparkline">
      <Sparkline :params="urlSparklineCountries" :width="100" :height="25"/>
      <div v-html="$sanitize(distinctCountriesText)"></div>
    </div>
    <br style="clear:left"/>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate, ContentBlock, Sparkline } from 'CoreHome';

export default defineComponent({
  props: {
    numberDistinctCountries: {
      type: Number,
      required: true,
    },
    urlSparklineCountries: {
      type: [Object, String],
      required: true,
    },
  },
  components: {
    ContentBlock,
    Sparkline,
  },
  computed: {
    distinctCountriesText() {
      return translate(
        'UserCountry_DistinctCountries',
        `<strong>${this.numberDistinctCountries}</strong>`,
      );
    },
  },
});
</script>
