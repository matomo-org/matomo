<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <h2>
    <EnrichedHeadline>{{ translate('PrivacyManager_Compliance') }}</EnrichedHeadline>
  </h2>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { EnrichedHeadline } from 'CoreHome';

export default defineComponent({
  components: {
    EnrichedHeadline,
  },
});
</script>
