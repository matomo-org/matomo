<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="dashboardHeader">
    <h1 class="card-title">
      <EnrichedHeadline
          :feature-name="translate('MultiSites_AllWebsitesDashboardTitle')"
      >
        {{ translate('MultiSites_AllWebsitesDashboardTitle') }}
      </EnrichedHeadline>
    </h1>

    <div v-if="!isWidgetized"
         id="periodString"
         class="borderedControl"
    >
      <PeriodSelector :periods="selectablePeriods" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { EnrichedHeadline, PeriodSelector } from 'CoreHome';

export default defineComponent({
  components: {
    EnrichedHeadline,
    PeriodSelector,
  },
  props: {
    isWidgetized: {
      type: Boolean,
      required: true,
    },
    selectablePeriods: {
      type: Array,
      required: true,
    },
  },
});
</script>
