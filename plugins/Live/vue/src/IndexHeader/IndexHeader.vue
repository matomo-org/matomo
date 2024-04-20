<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline>{{ translate('Live_VisitorLog') }}</EnrichedHeadline>
    </h2>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentIntro, EnrichedHeadline } from 'CoreHome';

export default defineComponent({
  components: {
    EnrichedHeadline,
  },
  directives: {
    ContentIntro,
  },
});
</script>
