<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sparklineSegmentComparisonRow">
    <span
      class="sparklineSegmentComparisonRow__chip"
      :title="segmentLabel"
    >{{ segmentLabel }}</span>
    <MetricValue
      class="metricValue--noTitle"
      :value="primaryValue"
      :secondary-value="secondaryValue"
      :secondary-label="secondaryLabel"
    />
    <div class="sparklineSegmentComparisonRow__sparkline">
      <Sparkline
        :width="380"
        :height="40"
        :params="segment.url"
        :series-indices="segment.seriesIndices"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { Sparkline } from 'CoreHome';
import MetricValue from '../MetricValue/MetricValue.vue';
import { SparklineEntry } from './types';

/**
 * One compared segment inside a segment-comparison card: a presentational block with a segment-name
 * chip, the metric readout (no title — the card shows the metric name once above the rows), and its
 * own single-series sparkline. The row is not a link — the whole card is the single `.sparkline`
 * click-to-evolution unit (SegmentComparisonCard). Segment-only comparison carries no evolution, so
 * no EvolutionBadge shows.
 */
export default defineComponent({
  name: 'SegmentComparisonRow',
  components: {
    MetricValue,
    Sparkline,
  },
  props: {
    segment: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
  },
  setup(props) {
    // Segment name (compareSegmentPretty); always populated in segment comparison.
    const segmentLabel = computed(() => props.segment.title || '');

    // Segment comparison groups the metric under a single period label; read that one group.
    // Values pass raw to MetricValue, which locale-formats numbers.
    const groupMetrics = computed<SparklineEntry['metrics'][string]>(() => {
      const metrics = props.segment.metrics || {};
      const label = (props.segment.metricsOrder || [])[0] ?? Object.keys(metrics)[0];
      return label !== undefined ? metrics[label] || [] : [];
    });
    const primaryValue = computed(() => groupMetrics.value[0]?.value ?? '');
    const secondaryValue = computed(() => groupMetrics.value[1]?.value);
    const secondaryLabel = computed(() => groupMetrics.value[1]?.description);

    return {
      segmentLabel,
      primaryValue,
      secondaryValue,
      secondaryLabel,
    };
  },
});
</script>
