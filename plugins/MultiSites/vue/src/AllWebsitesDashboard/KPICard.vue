<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="kpiCard">
    <div
      v-if="kpi.badge"
      class="kpiCard__badge"
      :title="kpi.badge.title"
      v-html="$sanitize(kpi.badge.label)"
      v-tooltips="{ duration: 200, delay: 200 }" />
    <div v-else class="kpiCard__badge kpiCard__badge--empty" aria-hidden="true" />

    <div class="kpiCard__title">
      <span :class="`kpiCard__icon ${kpi.icon}`" />
      {{ translate(kpi.title) }}
    </div>

    <div style="display: none;" ref="kpiCardTooltipTemplate">
      <div role="tooltip">
        <h3>{{ translate(kpi.title) }}</h3>
        <template v-if="kpi.tooltipBody">
          {{ translate(kpi.tooltipBody) }}
        </template>
        <template v-else>
          {{ kpi.value }}
        </template>
      </div>
    </div>

    <div
      class="kpiCard__value"
      :title="kpi.value"
      v-tooltips="{ duration: 200, delay: 200, content: tooltipContent }"
    >{{ kpi.valueCompact }}</div>

    <div class="kpiCard__evolution">
      <template v-if="kpi.evolutionValue !== ''">
        <span :class="`kpiCard__trend ${evolutionTrendClass}`">
          <span :class="`kpiCard__trendIcon ${evolutionTrendIcon}`" />
          {{ kpi.evolutionValue }}
        </span>
        <span class="kpiCard__evolutionPeriod">{{ translate(evolutionTrendFrom) }}</span>
      </template>
      <template v-else>&nbsp;</template>
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
        return 'kpiCard__trend--positive';
      }

      if (this.kpi.evolutionTrend === -1) {
        return 'kpiCard__trend--negative';
      }

      return 'kpiCard__trend--neutral';
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
