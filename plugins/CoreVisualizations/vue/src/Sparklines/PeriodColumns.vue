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
import { PeriodColumn, SparklineEntry } from './types';

/**
 * Shared compared-period columns for comparison cards: takes a SparklineEntry and renders one
 * column per compared period (a DateAtom label, the MetricValue readout with no title, and an
 * EvolutionBadge when the period has evolution), split by dividers. Used by DateComparison (date
 * comparison) and SegmentComparisonRow (segment + date). The host owns the outer spacing; this
 * derives the columns from the entry's per-period metric groups (metricsOrder order) and lays
 * them out. The date label shows only when comparing >1 period — a single column needs no label.
 */
export default defineComponent({
  name: 'PeriodColumns',
  components: {
    DateAtom,
    MetricValue,
    EvolutionBadge,
  },
  props: {
    entry: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
  },
  setup(props) {
    // One column per period, in backend order via `metricsOrder` (not Object.keys, which JS
    // re-sorts integer-like year labels). Primary = big value + evolution; optional second = the
    // "unique" sub-line. Values pass raw to MetricValue, which locale-formats numbers.
    const periods = computed<PeriodColumn[]>(() => {
      const metrics = props.entry.metrics || {};
      const order = props.entry.metricsOrder || [];
      return order.map((label) => {
        const groupMetrics = metrics[label] || [];
        const primary = groupMetrics[0];
        const secondary = groupMetrics[1];
        return {
          label,
          primaryValue: primary?.value ?? '',
          evolution: primary?.evolution,
          secondaryValue: secondary?.value,
          secondaryLabel: secondary?.description,
        };
      });
    });

    // Label the columns only when comparing more than one period (segment-only has one column).
    const showLabels = computed(() => periods.value.length > 1);

    return {
      periods,
      showLabels,
    };
  },
});
</script>
