<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="row sparklinesGrid">
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
    // True for date comparison (the only comparison mode the grid handles). Comparison cards are
    // wider — two value columns + a full-width sparkline — so they use a lower-density column tier.
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

    // Column density per card. No-comparison: widgets show two columns, reporting pages a
    // responsive 2/3/4/5-col grid (xl3 is widened to 5 cols above 1920px in the .less).
    // Comparison cards are wider: one per row in a widget, else 1 col ≤992px, 2 cols 993–1599px,
    // 3 cols 1600–1919px, 4 cols ≥1920px (xl6 is widened at 1600/1920 in the .less).
    const columnClasses = computed(() => {
      if (props.isComparing) {
        return props.isWidget ? 'col s12' : 'col s12 m12 l6 xl6';
      }
      return props.isWidget ? 'col s6' : 'col s6 m6 l4 xl3';
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
      columnClasses,
    };
  },
});
</script>
