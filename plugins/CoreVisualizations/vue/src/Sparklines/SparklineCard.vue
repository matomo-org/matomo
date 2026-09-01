<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="sparkline sparklineCard"
    :class="{ notLinkable: !areSparklinesLinkable }"
    :data-graph-params="graphParamsAttr"
    :data-series-indices="seriesIndicesAttr"
  >
    <!-- Segment header, set only when comparing more than one segment (sparkline.title is null
         otherwise). Dormant in the supported modes: no-comparison and single-segment date
         comparison both leave it null; the date-comparison body renders its own metric title. -->
    <div
      v-if="sparkline.title"
      class="sparklineCard__title"
    >
      {{ sparkline.title }}
    </div>
    <DateComparison
      v-if="isComparison"
      :sparkline="sparkline"
    />
    <NoComparison
      v-else
      :sparkline="sparkline"
      :all-metrics-documentation="allMetricsDocumentation"
    />
    <!-- Both card bodies end with the sparkline, so the shell owns the single slot. Its measured
         size is passed to Sparkline, which draws the image at exactly that size so CSS never has
         to rescale it. The sparkline waits until the slot has been measured; a card in a hidden
         tab measures 0 at first and shows the placeholder until it is visible. -->
    <!-- The tooltip goes on the slot, not the image: the slot is there even while the image is
         still loading. -->
    <div
      ref="sparklineSlot"
      class="sparklineCard__sparkline"
      :class="{ 'sparklineCard__sparkline--loading': isSparklineLoading }"
      :title="sparkline.tooltip || undefined"
    >
      <Sparkline
        v-if="sparklineWidth > 0 && sparklineHeight > 0"
        class="sparklineImg--fluid"
        :class="{ 'sparklineImg--hidden': isSparklineLoading }"
        :width="sparklineWidth"
        :height="sparklineHeight"
        :params="sparkline.url"
        :series-indices="sparkline.seriesIndices ?? undefined"
        @loading-change="isImageLoading = $event"
      />
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  PropType,
  ref,
} from 'vue';
import { Sparkline } from 'CoreHome';
import NoComparison from './NoComparison.vue';
import DateComparison from './DateComparison.vue';
import { sparklineGraphParamsAttr, sparklineSeriesIndicesAttr } from './sparklineDataAttrs';
import useSparklineSlotSize from './useSparklineSlotSize';
import { SparklineEntry } from './types';

/**
 * Card shell: the frame around a sparkline body. Owns the legacy `.sparkline` wrapper and
 * its evolution-graph data attributes; delegates the content to a body component. Comparison
 * entries carry seriesIndices (one per compared date), so they get the DateComparison body;
 * everything else gets the no-comparison body.
 */
export default defineComponent({
  name: 'SparklineCard',
  components: {
    NoComparison,
    DateComparison,
    Sparkline,
  },
  props: {
    sparkline: {
      type: Object as PropType<SparklineEntry>,
      required: true,
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true,
    },
    // Backend map of metric column -> documentation string, forwarded to the body component.
    allMetricsDocumentation: {
      type: Object as PropType<Record<string, string>>,
      default: () => ({}),
    },
  },
  setup(props) {
    // Comparison entries set seriesIndices (one series per compared date); no-comparison entries
    // leave it null. This picks the card body — only two-date comparison reaches the Vue grid.
    const isComparison = computed(() => !!props.sparkline.seriesIndices?.length);

    // The legacy click-to-evolution wiring (window.initializeSparklines) reads these attributes off
    // the .sparkline wrapper. Shared with SegmentComparisonCard, which puts them on the card root.
    const graphParamsAttr = computed(() => sparklineGraphParamsAttr(props.sparkline));
    const seriesIndicesAttr = computed(() => sparklineSeriesIndicesAttr(props.sparkline));

    // Sparkline size, measured from the slot it will be drawn in.
    const sparklineSlot = ref<HTMLElement | null>(null);
    const {
      width: sparklineWidth,
      height: sparklineHeight,
      isResizePending,
    } = useSparklineSlotSize(sparklineSlot);

    // Starts true so the placeholder also covers the time before the slot has been measured, when
    // there is no image yet. Sparkline tells us about every change after that.
    const isImageLoading = ref(true);

    // A pending resize counts as loading too: the image on screen is about to be replaced, and the
    // UI screenshot runner waits on this state before capturing.
    const isSparklineLoading = computed(() => isResizePending.value || isImageLoading.value);

    return {
      isComparison,
      graphParamsAttr,
      seriesIndicesAttr,
      sparklineSlot,
      sparklineWidth,
      sparklineHeight,
      isImageLoading,
      isSparklineLoading,
    };
  },
});
</script>
