<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="row sparklinesGrid">
    <!-- Segment comparison: one card per metric, each stacking its per-segment rows. -->
    <template v-if="comparisonMode === 'segment'">
      <div
        v-for="(segments, index) in segmentGroups"
        :key="index"
        :class="columnClasses"
      >
        <SegmentComparisonCard
          :segments="segments"
          :are-sparklines-linkable="areSparklinesLinkable"
          :all-metrics-documentation="allMetricsDocumentation"
        />
      </div>
    </template>
    <!-- No comparison and date comparison: one card per sparkline entry. -->
    <template v-else>
      <div
        v-for="(sparkline, index) in flatSparklines"
        :key="index"
        :class="columnClasses"
      >
        <SparklineCard
          :sparkline="sparkline"
          :are-sparklines-linkable="areSparklinesLinkable"
          :all-metrics-documentation="allMetricsDocumentation"
        />
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  nextTick,
  onMounted,
  PropType,
} from 'vue';
import SparklineCard from '../Sparklines/SparklineCard.vue';
import SegmentComparisonCard from '../Sparklines/SegmentComparisonCard.vue';
import { SparklineEntry } from '../Sparklines/types';

export default defineComponent({
  name: 'SparklinesGrid',
  components: {
    SparklineCard,
    SegmentComparisonCard,
  },
  props: {
    sparklines: {
      type: Object as PropType<Record<string, SparklineEntry[]>>,
      required: true,
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true,
    },
    // Backend map of metric column -> documentation string, forwarded to each card for the
    // metric-title tooltip.
    allMetricsDocumentation: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
    isWidget: {
      type: Boolean,
      default: false,
    },
    // Comparison layout from the backend: 'none', 'date' or 'segment'. Date cards are wider
    // (value columns + a full-width sparkline) so lower density; segment groups a metric's
    // per-segment entries into one taller card.
    comparisonMode: {
      type: String,
      default: 'none',
    },
  },
  setup(props) {
    // `order` is the backend's source of truth for display order: a total order across
    // all cards (even comparison metrics/segments). Flatten every group and sort by it.
    // Drop placeholders (Config::addPlaceholder()): no url, they only padded the legacy
    // 2-column layout and would render as empty cards here. Used by 'none' and 'date'.
    const flatSparklines = computed<SparklineEntry[]>(
      () => ([] as SparklineEntry[])
        .concat(...Object.values(props.sparklines || {}))
        .filter((sparkline) => !!sparkline.url)
        .sort((a, b) => a.order - b.order),
    );

    // Segment comparison emits one entry per (metric x segment), grouped by metric in `sparklines`.
    // One card per group (stacking per-segment rows); drop placeholders (no url) and order groups
    // by their lowest entry `order`.
    const segmentGroups = computed<SparklineEntry[][]>(
      () => Object.values(props.sparklines || {})
        .map((group) => group.filter((sparkline) => !!sparkline.url))
        .filter((group) => group.length > 0)
        .sort((a, b) => Math.min(...a.map((s) => s.order)) - Math.min(...b.map((s) => s.order))),
    );

    // Per-card column density: date-comparison cards are wider (fewer per row), the rest share the
    // standard width; widget mode uses one/two columns. See the .less for the per-tier widths
    // (xl3/xl6 widened above 1600/1920px).
    const columnClasses = computed(() => {
      if (props.comparisonMode === 'date') {
        return props.isWidget ? 'col s12' : 'col s12 m12 l6 xl6';
      }
      return props.isWidget ? 'col s6' : 'col s6 m6 l4 xl3';
    });

    onMounted(() => {
      // Wire each sparkline to its evolution graph once the cards are in the DOM (per-segment row
      // in segment mode, per card otherwise). Safe to re-run (it unbinds first); CoreHome's
      // sparkline.js is in the global JS bundle.
      nextTick(() => {
        window.initializeSparklines();
      });
    });

    return {
      flatSparklines,
      segmentGroups,
      columnClasses,
    };
  },
});
</script>
