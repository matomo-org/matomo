<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div :class="gridClasses">
    <!-- Segment and segment + date comparison: one card per metric, each stacking its per-segment
         rows (each row shows one value in 'segment' mode, or its compared-date columns in
         'segmentDate' mode). -->
    <template v-if="isSegmentMode">
      <div
        v-for="(segments, index) in segmentGroups"
        :key="index"
        class="sparklinesGrid__item"
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
        class="sparklinesGrid__item"
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
    // Comparison layout from the backend: 'none', 'date', 'segment' or 'segmentDate'. Date and
    // segment+date cards are wider (value columns + a full-width sparkline) so use a lower density;
    // segment / segment+date group a metric's per-segment entries into one taller card.
    comparisonMode: {
      type: String,
      default: 'none',
    },
  },
  setup(props) {
    // Both segment modes render one SegmentComparisonCard per metric group (a row per segment);
    // they differ only in how many date columns each row shows and in card width (isWideLayout).
    const isSegmentMode = computed(
      () => props.comparisonMode === 'segment' || props.comparisonMode === 'segmentDate',
    );

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

    // Segment (and segment + date) comparison emits one entry per (metric x segment), grouped by
    // metric in `sparklines`. One card per group (stacking per-segment rows); drop placeholders
    // (no url) and order groups by their lowest entry `order`.
    const segmentGroups = computed<SparklineEntry[][]>(
      () => Object.values(props.sparklines || {})
        .map((group) => group.filter((sparkline) => !!sparkline.url))
        .filter((group) => group.length > 0)
        .sort((a, b) => Math.min(...a.map((s) => s.order)) - Math.min(...b.map((s) => s.order))),
    );

    // date / segment+date cards are wider (value columns + a full-width sparkline), so the grid
    // gives them a lower density. (isSegmentMode = segment || segmentDate is a different split.)
    const isWideLayout = computed(
      () => props.comparisonMode === 'date' || props.comparisonMode === 'segmentDate',
    );

    // Container classes drive the CSS grid (see the .less). Every layout reflows via auto-fill.
    // Standard cards use --compact (a denser 200px minimum) in a widget; the wide comparison
    // layouts (date / segment+date) use --wide (350px) on reporting pages and widgets alike.
    // --framed layers the widget frame (tighter gutter, even padding) over the column modifier.
    const gridClasses = computed(() => ({
      sparklinesGrid: true,
      'sparklinesGrid--wide': isWideLayout.value,
      'sparklinesGrid--framed': props.isWidget,
      'sparklinesGrid--compact': props.isWidget && !isWideLayout.value,
    }));

    onMounted(() => {
      // Wire each sparkline to its evolution graph once the cards are in the DOM (per-segment row
      // in segment mode, per card otherwise). Safe to re-run (it unbinds first); CoreHome's
      // sparkline.js is in the global JS bundle.
      nextTick(() => {
        window.initializeSparklines();
      });
    });

    return {
      isSegmentMode,
      flatSparklines,
      segmentGroups,
      gridClasses,
    };
  },
});
</script>
