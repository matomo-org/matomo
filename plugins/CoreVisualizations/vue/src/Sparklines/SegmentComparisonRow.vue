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
    <PeriodColumns :entry="segment" />
    <!-- The slot's measured size is passed to Sparkline, which draws the image at exactly that
         size so CSS never has to rescale it. Shows the placeholder until it has been measured. -->
    <!-- The tooltip goes on the slot, not the image: the slot is there even while the image is
         still loading. -->
    <div
      ref="sparklineSlot"
      class="sparklineSegmentComparisonRow__sparkline"
      :class="{ 'sparklineSegmentComparisonRow__sparkline--loading': isSparklineLoading }"
      :title="segment.tooltip || undefined"
    >
      <Sparkline
        v-if="sparklineWidth > 0 && sparklineHeight > 0"
        class="sparklineImg--fluid"
        :class="{ 'sparklineImg--hidden': isSparklineLoading }"
        :width="sparklineWidth"
        :height="sparklineHeight"
        :params="segment.url"
        :series-indices="segment.seriesIndices ?? undefined"
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
import PeriodColumns from './PeriodColumns.vue';
import useSparklineSlotSize from './useSparklineSlotSize';
import { SparklineEntry } from './types';

/**
 * One compared segment inside a segment-comparison card: a presentational block with a segment-name
 * chip, one value column per compared date (a bare value for segment-only, or a labelled column
 * with an evolution badge per date for segment + date, rendered by the shared PeriodColumns), and
 * its own single- or multi-series sparkline. The row is not itself a link — the whole card is the
 * single `.sparkline` click-to-evolution unit (SegmentComparisonCard); every segment reloads the
 * same evolution graph.
 */
export default defineComponent({
  name: 'SegmentComparisonRow',
  components: {
    PeriodColumns,
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
      segmentLabel,
      sparklineSlot,
      sparklineWidth,
      sparklineHeight,
      isImageLoading,
      isSparklineLoading,
    };
  },
});
</script>
