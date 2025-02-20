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

    <div style="display: none;" ref="kpiCardTooltipTemplate">
      <div role="tooltip">
        <h3>{{ translate(kpi.title) }}</h3>
        {{ kpi.value }}
      </div>
    </div>

    <div class="kpiCardValue"
         :title="kpi.value"
         v-tooltips="{ duration: 200, delay: 200, content: tooltipContent }"
    >{{ kpi.valueCompact }}</div>

    <div class="kpiCardEvolution">
      <template v-if="kpi.evolutionValue !== ''">
        <span :class="`kpiCardEvolutionTrend ${evolutionTrendClass}`">
          <span :class="`kpiCardEvolutionIcon ${evolutionTrendIcon}`" />
          {{ kpi.evolutionValue }}&nbsp;
        </span>
        <span>{{ translate(evolutionTrendFrom) }}</span>
      </template>

      <template v-else>
        <div class="kpiCardEvolution">
          <span class="kpiCardEvolutionTrend">&nbsp;</span>
        </div>
      </template>
    </div>

    <div v-if="kpi.badge"
         class="kpiCardBadge"
         :title="kpi.badge.title"
         v-html="$sanitize(kpi.badge.label)"
         v-tooltips="{ duration: 200, delay: 200 }">
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import { Tooltips } from 'CoreHome';
import { KPICardData } from '../types';

export default defineComponent({
  directives: {
    Tooltips,
  },
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
  },
  computed: {
    tooltipContent(): () => string {
      return () => (this.$refs.kpiCardTooltipTemplate as HTMLElement)?.innerHTML || '';
    },
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
