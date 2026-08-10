<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="sparkline sparklineSegmentComparisonCard"
    :class="{ notLinkable: !areSparklinesLinkable }"
    :data-graph-params="graphParamsAttr"
    :data-series-indices="seriesIndicesAttr"
  >
    <div
      class="sparklineSegmentComparisonCard__title"
      :class="{ 'sparklineSegmentComparisonCard__title--documented': !!documentation }"
      :title="documentation || metricTitle"
      v-tooltips="{ duration: 200, delay: 200 }"
    >{{ metricTitle }}</div>
    <div class="sparklineSegmentComparisonCard__rows">
      <SegmentComparisonRow
        v-for="(segment, index) in segments"
        :key="index"
        :segment="segment"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, PropType } from 'vue';
import { Tooltips, ucfirst } from 'CoreHome';
import SegmentComparisonRow from './SegmentComparisonRow.vue';
import { sparklineGraphParamsAttr } from './sparklineDataAttrs';
import { SparklineEntry, SparklineMetric } from './types';

/**
 * Segment-comparison card: one card per metric, showing the metric name once and a stacked block
 * per compared segment (SegmentComparisonRow). The whole card is a single `.sparkline`
 * click-to-evolution link — every segment reloads the same evolution graph (same metric columns,
 * segments plotted as its series), so the card, not each row, is the clickable/hover unit. Rows are
 * presentational.
 */
export default defineComponent({
  name: 'SegmentComparisonCard',
  directives: {
    Tooltips,
  },
  components: {
    SegmentComparisonRow,
  },
  props: {
    // One entry per compared segment for this metric (same metric, different segment).
    segments: {
      type: Array as PropType<SparklineEntry[]>,
      required: true,
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true,
    },
    // Backend map of metric column -> documentation string, for the card-title tooltip.
    allMetricsDocumentation: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
  },
  setup(props) {
    // Same metric across segments; read its name + column from the first segment's first period
    // group. In segment-only comparison the column is populated so the doc tooltip resolves; in
    // segment + date it comes back empty (two periods, so Config::addSparkline can't map columns),
    // so no tooltip shows — correct parity with date comparison, not a regression.
    const primaryMetric = computed<SparklineMetric | undefined>(() => {
      const first = props.segments[0];
      const metrics = first?.metrics || {};
      const label = (first?.metricsOrder || [])[0] ?? Object.keys(metrics)[0];
      return label !== undefined ? metrics[label]?.[0] : undefined;
    });
    const metricTitle = computed(() => {
      const label = primaryMetric.value?.title || primaryMetric.value?.description;
      return ucfirst(label, document.documentElement.lang);
    });
    const documentation = computed(
      () => props.allMetricsDocumentation[primaryMetric.value?.column ?? ''] || undefined,
    );

    // The whole card is one click-to-evolution link (window.initializeSparklines reads these off
    // the `.sparkline` root). Segments share the metric's columns, so derive reload params from the
    // first segment; series indices are the union of the card's segments.
    const graphParamsAttr = computed(() => {
      const first = props.segments[0];
      return first ? sparklineGraphParamsAttr(first) : null;
    });
    const seriesIndicesAttr = computed(() => {
      const indices = props.segments.flatMap((segment) => segment.seriesIndices || []);
      return indices.length ? JSON.stringify(indices) : null;
    });

    return {
      metricTitle,
      documentation,
      graphParamsAttr,
      seriesIndicesAttr,
    };
  },
});
</script>
