<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="sparklinesGrid"
    :style="{ '--sparklines-card-min-width': cardMinWidth }"
  >
    <SparklineCard
      v-for="(sparkline, index) in flatSparklines"
      :key="index"
      :sparkline="sparkline"
      :are-sparklines-linkable="areSparklinesLinkable"
      :all-metrics-documentation="allMetricsDocumentation"
    />
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
import { SparklineEntry } from '../Sparklines/types';

export default defineComponent({
  name: 'SparklinesGrid',
  components: {
    SparklineCard,
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
    // True for date comparison. Comparison cards are wider — one value column per compared date
    // plus a full-width sparkline — so they get a larger minimum width (see cardMinWidth).
    isComparing: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    // `order` is the backend's source of truth for display order: a total order across
    // all cards (even comparison metrics/segments). Flatten every group and sort by it.
    // Drop placeholders (Config::addPlaceholder()): no url, they only padded the legacy
    // 2-column layout and would render as empty cards here.
    const flatSparklines = computed<SparklineEntry[]>(
      () => ([] as SparklineEntry[])
        .concat(...Object.values(props.sparklines || {}))
        .filter((sparkline) => !!sparkline.url)
        .sort((a, b) => a.order - b.order),
    );

    // Minimum card width fed to the CSS Grid `minmax()` track, so `auto-fill` derives the column
    // count from the available width rather than fixed breakpoints. Comparison cards lay each
    // compared date out as its own column, so their minimum scales with the number of dates —
    // wider cards then pack fewer per row. Widgets are narrow, so no-comparison cards use a
    // smaller minimum to still fit two across.
    const cardMinWidth = computed(() => {
      if (!props.isComparing) {
        return props.isWidget ? '160px' : '260px';
      }

      const first = flatSparklines.value[0];
      const dateCount = first ? Object.keys(first.metrics).length : 2;
      return `${64 + (150 * dateCount)}px`;
    });

    onMounted(() => {
      // Re-wire each sparkline to its evolution graph once the cards are in the DOM.
      // Safe to re-run (it unbinds first); CoreHome ships sparkline.js in the global JS bundle.
      nextTick(() => {
        window.initializeSparklines();
      });
    });

    return {
      flatSparklines,
      cardMinWidth,
    };
  },
});
</script>
