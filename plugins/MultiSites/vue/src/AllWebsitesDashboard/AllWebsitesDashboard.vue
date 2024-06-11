<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <h1 class="card-title">
    <EnrichedHeadline
        :feature-name="translate('MultiSites_AllWebsitesDashboardTitle')"
    >
      {{ translate('MultiSites_AllWebsitesDashboardTitle') }}
    </EnrichedHeadline>
  </h1>
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
