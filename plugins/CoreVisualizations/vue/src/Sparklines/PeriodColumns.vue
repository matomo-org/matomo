<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="periodColumns">
    <template
      v-for="(period, index) in periods"
      :key="period.label"
    >
      <div
        v-if="index > 0"
        class="periodColumns__separator"
      />
      <div class="periodColumns__column">
        <!-- A single column (segment-only) needs no date label; label only when >1 period. -->
        <DateAtom
          v-if="showLabels"
          :label="period.label"
        />
        <MetricValue
          class="metricValue--noTitle"
          :value="period.primaryValue"
          :secondary-value="period.secondaryValue"
          :secondary-label="period.secondaryLabel"
        >
          <template
            v-if="period.evolution"
            #evolution
          >
            <EvolutionBadge
              :percent="period.evolution.percent"
              :trend="period.evolution.trend"
              :is-lower-value-better="period.evolution.isLowerValueBetter"
              :tooltip="period.evolution.tooltip || ''"
            />
          </template>
        </MetricValue>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import MetricValue from '../MetricValue/MetricValue.vue';
import EvolutionBadge from '../EvolutionBadge/EvolutionBadge.vue';
import DateAtom from './DateAtom.vue';
import { PeriodColumn } from './types';

/**
 * Shared compared-period columns for comparison cards: one column per period (a DateAtom label, the
 * MetricValue readout with no title, and an EvolutionBadge when the period has evolution), split by
 * dividers. Rendered by DateComparison (date comparison) and SegmentComparisonRow (segment + date).
 * The host owns the outer spacing; this only lays the columns out. The date label shows only when
 * comparing more than one period — a single column (segment-only) needs no label.
 */
export default defineComponent({
  name: 'PeriodColumns',
  components: {
    DateAtom,
    MetricValue,
    EvolutionBadge,
  },
  props: {
    periods: {
      type: Array as PropType<PeriodColumn[]>,
      required: true,
    },
  },
  setup(props) {
    const showLabels = computed(() => props.periods.length > 1);

    return {
      showLabels,
    };
  },
});
</script>
