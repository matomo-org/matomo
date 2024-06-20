<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCard">
    <div class="kpiCardTitle">
      <span :class="`kpiCardIcon ${kpi.icon}`" />
      {{ translate(kpi.title) }}
    </div>

    <div class="kpiCardValue">{{ kpi.value }}</div>

    <div class="kpiCardEvolution">
      <span :class="`kpiCardEvolutionTrend ${evolutionTrendClass}`">
        <span :class="`kpiCardEvolutionIcon ${evolutionTrendIcon}`" />
        {{ kpi.evolutionValue }}
      </span>
      {{ translate(evolutionTrendFrom) }}
    </div>

    <div v-if="kpi.badge"
         v-html="$sanitize(kpi.badge)"
         class="kpiCardBadge"
    ></div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import { KPICardData } from '../types';

export default defineComponent({
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
  },
  computed: {
    evolutionTrendFrom(): string {
      switch (this.kpi.evolutionPeriod) {
        case 'day':
          return 'MultiSites_EvolutionFromPreviousDay';
        case 'week':
          return 'MultiSites_EvolutionFromPreviousWeek';
        case 'month':
          return 'MultiSites_EvolutionFromPreviousMonth';
        case 'year':
          return 'MultiSites_EvolutionFromPreviousYear';
        default:
          return 'MultiSites_EvolutionFromPreviousPeriod';
      }
    },
    evolutionTrendClass(): string {
      if (this.kpi.evolutionTrend === 1) {
        return 'kpiTrendPositive';
      }

      if (this.kpi.evolutionTrend === -1) {
        return 'kpiTrendNegative';
      }

      return 'kpiTrendNeutral';
    },
    evolutionTrendIcon(): string {
      if (this.kpi.evolutionTrend === 1) {
        return 'icon-chevron-up';
      }

      if (this.kpi.evolutionTrend === -1) {
        return 'icon-chevron-down';
      }

      return 'icon-circle';
    },
    kpi(): KPICardData {
      return this.modelValue as KPICardData;
    },
  },
});
</script>
