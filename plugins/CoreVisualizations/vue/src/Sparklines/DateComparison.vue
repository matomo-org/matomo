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
    <PeriodColumns :entry="sparkline" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { ucfirst } from 'CoreHome';
import PeriodColumns from './PeriodColumns.vue';
import { SparklineEntry } from './types';

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
    // Read the metric name from the first column via metricsOrder, not Object.values, whose order
    // JS shuffles for integer-like labels. The name is the same across columns, so this is for
    // consistency, not correctness.
    const metricTitle = computed(() => {
      const metrics = props.sparkline.metrics || {};
      const firstLabel = (props.sparkline.metricsOrder || [])[0] ?? Object.keys(metrics)[0];
      const primary = firstLabel !== undefined ? metrics[firstLabel]?.[0] : undefined;
      return ucfirst(primary?.title || primary?.description, document.documentElement.lang);
    });

    return {
      metricTitle,
    };
  },
});
</script>
