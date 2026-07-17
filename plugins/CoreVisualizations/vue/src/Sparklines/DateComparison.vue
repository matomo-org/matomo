<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sparklineDateComparison">
    <div
      class="sparklineDateComparison__title"
      :title="metricTitle"
    >{{ metricTitle }}</div>
    <div class="sparklineDateComparison__periods">
      <template
        v-for="(period, index) in periods"
        :key="period.label"
      >
        <div
          v-if="index > 0"
          class="sparklineDateComparison__separator"
        />
        <div class="sparklineDateComparison__date">
          <DateAtom :label="period.label" />
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
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import MetricValue from '../MetricValue/MetricValue.vue';
import EvolutionBadge from '../EvolutionBadge/EvolutionBadge.vue';
import DateAtom from './DateAtom.vue';
import { SparklineEntry, SparklineEvolution } from './types';

interface PeriodColumn {
  label: string;
  primaryValue: string | number;
  evolution?: SparklineEvolution;
  secondaryValue?: string | number;
  secondaryLabel?: string;
}

/**
 * Date-comparison body for a sparkline card: metric name as title and one column per compared date
 * (the shell renders the shared sparkline below, which draws a coloured series per date). Metrics
 * arrive grouped by date label (one column each, in seriesIndices order). Only two-date comparison
 * reaches here.
 */
export default defineComponent({
  name: 'DateComparison',
  components: {
    DateAtom,
    MetricValue,
    EvolutionBadge,
  },
  props: {
    sparkline: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
  },
  setup(props) {
    // Read the metric name from the first column via metricsOrder (like periods below),
    // not Object.values, whose order JS shuffles for integer-like labels. The name is
    // the same across columns, so this is for consistency, not correctness.
    const metricTitle = computed(() => {
      const metrics = props.sparkline.metrics || {};
      const firstLabel = (props.sparkline.metricsOrder || [])[0] ?? Object.keys(metrics)[0];
      const primary = firstLabel !== undefined ? metrics[firstLabel]?.[0] : undefined;
      return primary?.title || primary?.description || '';
    });

    // One column per compared date, in backend order via `metricsOrder` (not Object.keys, which
    // JS re-sorts for integer-like year labels). Primary metric = big value + evolution, optional
    // second = the "unique" sub-line. Values pass raw to MetricValue, which formats numbers.
    const periods = computed<PeriodColumn[]>(() => {
      const metrics = props.sparkline.metrics || {};
      const order = props.sparkline.metricsOrder || [];
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

    return {
      metricTitle,
      periods,
    };
  },
});
</script>
