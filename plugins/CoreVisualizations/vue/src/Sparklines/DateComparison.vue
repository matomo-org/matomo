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
    <PeriodColumns :periods="periods" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import PeriodColumns from './PeriodColumns.vue';
import { PeriodColumn, SparklineEntry } from './types';

/**
 * Date-comparison body for a sparkline card: metric name as title and one column per compared date
 * (the shell renders the shared sparkline below, which draws a coloured series per date). Metrics
 * arrive grouped by date label (one column each, in seriesIndices order). Only two-date comparison
 * reaches here. The columns themselves are rendered by the shared PeriodColumns component.
 */
export default defineComponent({
  name: 'DateComparison',
  components: {
    PeriodColumns,
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
