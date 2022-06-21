<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock>
    <div class="sparkline">
      <Sparkline :params="urlSparklineCountries"/>
      <div>
        {{ 'UserCountry_DistinctCountries'|translate("<strong>"~numberDistinctCountries|number~"</strong>")|raw }}
      </div>
    </div>
    <br style="clear:left"/>
  </ContentBlock>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, Sparkline } from 'CoreHome';

export default defineComponent({
  props: {
    // TODO
  },
  components: {
    ContentBlock,
    Sparkline,
  },
});
</script>
