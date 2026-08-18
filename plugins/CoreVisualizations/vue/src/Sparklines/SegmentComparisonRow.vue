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
    <!-- The slot's measured size drives both the requested image and the size it is drawn at, so
         the image is never rescaled by CSS. Held back until measured. -->
    <!-- The tooltip goes on the slot, not the image: the slot is there even while the image is
         still loading. -->
    <div
      ref="sparklineSlot"
      class="sparklineSegmentComparisonRow__sparkline"
      :style="sparklineSizeVars"
      :title="segment.tooltip || undefined"
    >
      <Sparkline
        v-if="sparklineWidth > 0 && sparklineHeight > 0"
        :width="sparklineWidth"
        :height="sparklineHeight"
        :params="segment.url"
        :series-indices="segment.seriesIndices ?? undefined"
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

    // Displayed sparkline size, measured from the slot itself. The same numbers are handed to
    // Sparkline (which requests the image at 2x them) and published as custom properties the .less
    // draws the image at, so the two can't drift and CSS never has to rescale the image.
    const sparklineSlot = ref<HTMLElement | null>(null);
    const { width: sparklineWidth, height: sparklineHeight } = useSparklineSlotSize(sparklineSlot);
    const sparklineSizeVars = computed(() => ({
      '--sparklineSegmentComparisonRow-img-width': `${sparklineWidth.value}px`,
      '--sparklineSegmentComparisonRow-img-height': `${sparklineHeight.value}px`,
    }));

    return {
      segmentLabel,
      sparklineSlot,
      sparklineWidth,
      sparklineHeight,
      sparklineSizeVars,
    };
  },
});
</script>
